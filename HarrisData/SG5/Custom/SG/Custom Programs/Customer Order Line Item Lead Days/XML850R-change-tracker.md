# XML850R Change Tracker

Each change tracked separately, applied separately. Nothing applied yet.

- Opened 2026-08-26
- Members: `XML850R_AR`, `XML850R_R1`, `XML850R_WM` in `SGSRC/QRPGLESRC`
- Source backups: `W:\Claude-Extracts\XML850R-backup-20260826-1457\` (6 files, MD5 verified)

Status key: OPEN (not started) / PENDING (waiting on a decision) / READY (decided, not applied) / APPLIED / HELD

---

## Data changes - PROITRG.UDCDETAIL

| ID | Change | Scope | Status |
|---|---|---|---|
| D1 | EMAIL recipients: `bbusch@screen-graphics.com` to `sgit@screen-graphics.com` | Bill is making this change directly | BILL |
| D2 | `UDCKEY` person labels on those rows | Bill is making this change directly | BILL |

No UDCDETAIL changes from this session. Recipient lists recorded under "Notes carried forward"
were read 2026-08-26 and will be stale once D1 lands.

---

## Code changes - all three members unless noted

| ID | Change | Reason | Status |
|---|---|---|---|
| C1 | Remove hardcoded product-class branches `DCCU` / `DCSG` / `KITS` from the short-stock `Select` | No product classes hardcoded. UDC file governs | READY |
| C2 | Remove the stock-name test (`9x-` or `3M0`). The `XMLAvail` alert lives inside it and goes with it | Same. Supersedes v2 spec Change 5 for this one alert | READY |
| C3 | AR only: remove the second stock-name test at lines 1244-1249 that sets `StockItem` | `StockItem` is never read downstream | READY |
| C4 | New short-stock cascade, in this order: item `REQUIREDAY`, prefix `PREFIXRQDD` (scoped to `CustClass` or `SCREEN GRAPHICS`), class `PCREQUIRED`, `IPTLT` if above 1, else default 10 | Replaces the removed hardcoding. Prefix ahead of class, confirmed 2026-08-26 after reviewing the full impact | READY |
| C5 | In-stock branch: `Avail >= O1QOrd` returns 1 business day, unconditional, no lookup | Any item with exact or more available is 1 day | READY |
| C6 | Literal fix `REQUIREDDAY` to `REQUIREDAY` (AR 1333, R1 1513, WM 1329) | Table holds one D. Nine override rows have never matched since 2025-10-19 | READY |
| C7 | Guard the `HDIMST` chain, capture `WkClass` under `%Found`, blank otherwise | Failed CHAIN leaks the prior line's `IMPCLS` | READY |
| C8 | Guard the `HDIWHS` chain, zero `IWOHQT` / `IWQOO` / `IWRESQ` in the not-found branch | Failed CHAIN leaks the prior line's quantities into `Avail` | READY |
| C9 | Capture `CustClass` from the existing `Chain HDPCust# HDCUSTR`, `%Found`-guarded | Needed for prefix scoping. No extra file access | READY |
| C10 | Field definitions: `AddDays` to `5s 0`; add `LeadDays 5s 1`, `ReqDays 5s 0`, `WkClass Like(IMPCLS)`, `CustClass Like(CMCCLS)` | `AddDays` is `2s 0` in AR and R1, `7s 0` in WM. `IPTLT` is DECIMAL(5,1) | READY |
| C11 | Literal fix `Alert = 'XMLORDERS'` to `'XMLORDERR'` (AR 651, R1 833, WM 663) | No `XMLORDERS` EMAIL group exists. Alert currently sends to nobody | READY |
| C12 | AR only: add `Alert = 'XMLConfig';` before the `Exsr Notify` at line 1282 | AR never sets it. Send runs on blanks or a stale code | READY |
| C13 | `XMLNODTL` no-detail alert, built as real code with a maintained counter. Marked with banner comments in all three members | Cannot be applied as an uncomment: AR counter never increments, R1 has no block, WM field undeclared | READY |

### C13 detail

Three insertion points per member, each marked so it is obvious in a diff.

**Field.** AR already declares `ProcessedItems` as `3s 0` at line 204. R1 and WM need the
same declaration added. Detail loop caps at 500, so `3s 0` is sufficient.

**Reset**, immediately before the detail `For lineCounter = 1 to 500;`

```
           // C13 -- count detail lines actually written for this order
           ProcessedItems = 0;
```

**Increment**, immediately after `Write OEDTWKR;`

```
           Write OEDTWKR;
           ProcessedItems += 1;                                    // C13
```

**Test**, immediately after the detail `EndFor;` and ahead of the `H1RqDt` fallback

```
         //===================================================================
         //  C13  Order arrived with no detail lines.  Alert only.  The order
         //       IS still created: processing falls straight through to the
         //       H1RqDt fallback, Write OEHDWKR, the tax call, the data queue
         //       and the XMLOEHD write, exactly as it does today.
         //       Do NOT add LeaveSR here.
         //===================================================================
         If ProcessedItems = 0;

           Alert     = 'XMLNODTL';
           AlertSubj = *Blanks;
           If xmlMode = 'T';
             AlertSubj = '*** TEST ***';
           EndIf;
           AlertSubj = %Trim(AlertSubj) + ' ' +
                       'Imported Order Alert:  Order has no detail';
           AlertMsg  = 'Customer: ' + %Trim(xmlRetailer) + CrLf;
           AlertMsg  = %Trim(AlertMsg) + 'Imported File: ' +
                       %Trim(ifsFile) + CrLf;
           AlertMsg  = %Trim(AlertMsg) + 'Imported Order: ' +
                       %Trim(HWPO#) + CrLf;
           AlertMsg  = %Trim(AlertMsg) + 'Issue: No Items Ordered' + CrLf;
           Exsr Notify;

         EndIf;
         //===================================================================
```

**The order must still be created.** Confirmed by Bill 2026-08-26. The commented original
carried a `LeaveSR` that would have skipped `Write OEHDWKR`, the tax call, the data queue
entry and the `XMLOEHD` write. That is dropped. C13 alerts and nothing more.

Verified against the current code path: with zero lines the detail `For` exits immediately,
`If H1RqDt = 0` sets the header required date to the next business day from `LastDtlDate`,
and execution continues unconditionally to `Write OEHDWKR`. Nothing between the `EndFor` and
the header write depends on line count.

**No-line orders are routine, not rare.** Counting `SG.XMLOEHD` headers with no matching
`SG.XMLOEDT` rows:

| Partner | No-line orders | Total | Rate |
|---|---:|---:|---:|
| wastemgmt | 1,189 | 22,951 | 5.2% |
| republic | 695 | 15,921 | 4.4% |
| amerigas | 0 | 5,525 | 0% |

So C13's alert will fire on roughly one order in twenty for WM and Republic, and effectively
never for Amerigas. Alert volume needs a decision, see O6.

An earlier check against `OEORDH` returned zero and was reported here as "never occurred".
That was wrong: `OEORDH` holds only orders that cleared HOEOEM and survived purging, so it
cannot see this at all. `SG.XMLOEHD` / `SG.XMLOEDT` are the files that retain it.

Two corrections folded in against the commented original:

- It wrote `AlertMsg = 'Imported File: '...` on the second line, overwriting the Customer
  line instead of appending. Now uses `%Trim(AlertMsg) +`. Present in both the AR and WM
  commented blocks.
- `AlertSubj` is blanked before use, so a stale subject from an earlier alert in the same job
  cannot prefix the message.

---

## Pre-existing defects found in the full sweep

Found while sweeping all three members rather than only the lead-days path. None are caused
by C1-C13. All confirmed present in the compiled source: the QSYS members and the IFS files
are byte-identical apart from a trailing newline, and the `SGPGM` objects were created
2026-08-18 11:31:22, twenty-one seconds after the members were written at 11:31:01.

| ID | Defect | Sites | Status |
|---|---|---|---|
| C14 | Missing statement terminator merges two assignments into one. RPG reads the second `=` as a comparison and assigns its result to the target, destroying the intended text | AR 1756-1759, R1 835-836, R1 1917-1920, WM 1766-1769 | READY |
| C15 | `Chain HDPCust# HDCUSTR` is unguarded, then `CMSVSV`, `CMSV`, `CMWH#`, `CMLOC#` are used. A failed chain leaks the prior record | AR 1202, R1 1393, WM 1216 | READY |
| C16 | `Chain H1BLTO HDCUSTR` is unguarded, then `CMLOC#`, `CMSLSM`, `CMCTXC`, `CMCNTC`, `CMCTTC`, `CMLOC1-3`, `CMORTY`, `CMCTRM` are used on the header | AR 1501, R1 1662, WM 1503 | READY |
| C17 | `O1SlPr = %Dec(O1SlPrXlt:13:5)` sits outside any `Monitor`, while the quantity conversion right above it is monitored. A malformed price halts the program instead of defaulting | AR 1177, R1 1368, WM equivalent | READY |
| C18 | Leftover debug code: `If HWPO# = '4100022893'; DebugFlag = 'Y'; EndIf;`. `DebugFlag` is never read anywhere | AR 780-782, R1 977-979 | READY |
| C19 | XML entity constants are wrong: `apos` = `'andapos;'`, `quot` = `'andquot;'`, `amper` = `'andamp;'`, `lt` = `'andlt;'`, `gt` = `'andgt;'`. The ampersand has been replaced by the word "and", so the scan/replace loops never match anything. Additionally `XML-INTO` already decodes entities, so the whole block may be redundant | AR 544-548 and the loops at 1119-1156, same in R1 and WM | READY |
| C20 | `AlertMsg` and `AlertSubj` are built with `%Trim(AlertMsg) + ...` without being cleared first, so text from an earlier alert in the same job prefixes later ones | Throughout all three | READY |

### C14 detail

The four sites all have the same shape. Example, AR 1756-1759:

```
           AlertMsg = %Trim(AlertMsg) + 'Price: ' +
                      %Char(CustSlPr) + CrLf +
           AlertMsg = %Trim(AlertMsg) + 'Extended Price: ' +
                      %Char(CustQOrd * CustSlPr);
```

Line 1757 ends in `+` with no `;`, so lines 1756-1759 are a single statement. Fix is to
terminate the first assignment after `%Char(CustSlPr) + CrLf`.

R1 835-836 is the same defect in the "no download path in ARPSFTP" branch:

```
           AlertSubj = %Trim(AlertSubj) + ' ' +
                      'Republic OSN Order Alert: ' + %Trim(xmlRetailer) +
           AlertMsg = fileName;
```

That alert is doubly broken: C14 corrupts the subject, and C11 means it looks up an EMAIL
code that does not exist, so it reaches nobody.

---

## Build and verify

| ID | Step | Status |
|---|---|---|
| B1 | `CPYFRMSTMF` each edited IFS file into `SGSRC/QRPGLESRC` | OPEN |
| B2 | Save current `SGPGM` program objects before replace | OPEN |
| B3 | `CRTSQLRPGI` per member | OPEN |
| B4 | Replay an archived inbound order, check `O1RQDT` and `H1RQDT` | OPEN |

`SGPGM` is sequence 50 in both Test and Live library lists. There is no SG5 copy, so B3 is a direct-to-Live change.

---

## Confirmed by testing

All traced against live data, processed date as noted, Labor Day 2026-09-07 observed.

| Item | Qty | Processed | Tier that answers | Days | Due date | Confirmed |
|---|---:|---|---|---:|---|---|
| 93-9640 | short | 2026-08-27 | item `REQUIREDAY` = 5 | 5 | 2026-09-03 | yes |
| 96-20023 | short | 2026-08-27 | prefix or class, both 10 | 10 | 2026-09-11 | yes |
| 97-0001 | short | 2026-08-27 | prefix `97-` = 5 | 5 | 2026-09-03 | yes |
| 96-CLAY | 5 | 2026-08-27 | prefix `96-` = 10 | 10 | 2026-09-11 | yes |
| 96-20096-H | 100 | 2026-08-27 | prefix `96-` = 10 | 10 | 2026-09-11 | not ruled on |
| 42540-0074 | 100 | 2026-08-27 | `IPTLT` = 9 | 9 | 2026-09-10 | yes |
| 93-0007 | 2500 | 2026-08-31 | prefix `93-` = 10 | 10 | 2026-09-15 | yes |
| 99-0100 | 45 | 2026-08-27 | prefix `99-` = 5 (beats class `KITS` = 10) | 5 | 2026-09-03 | yes, via the O1 swap |

The 7 items where prefix and class disagree, all `KITS`, all now resolving to 5 days via prefix:
`97-0094-KIT`, `99-0100`, `99-96090-C-KIT`, `99-96090-E-KIT`, `99-96090-N-KIT`,
`99-96090-R-KIT`, `99-96090-T-KIT`. They get 10 days today, so these 7 are the only
items in the catalog whose promise shortens as a result of the tier order.

---

## Open questions

| ID | Question | Status |
|---|---|---|
| O1 | Tier order between class and prefix | CLOSED 2026-08-26. **Prefix first.** Observable on exactly 7 items, all `KITS` on `97-`/`99-`, which move from 10 days to 5. Every other item matches one tier, neither tier, or two that agree, so nothing else shifts. Briefly set to class-first earlier the same day, then swapped after reviewing the full impact |
| O2 | D1 scope | CLOSED. Bill is making the UDCDETAIL changes directly |
| O3 | D2 `UDCKEY` labels | CLOSED. Same |
| O4 | C13 `XMLNODTL`: build a real counter, or defer | CLOSED 2026-08-26. Build it, with the insertion points marked in source. Order must still be created, no `LeaveSR` |
| O6 | C13 alert volume | CLOSED 2026-08-26. Every occurrence, no throttling |
| O5 | Which product classes get `PCREQUIRED` rows | CLOSED. No further values being set. `KITS` = 10 and `KITC` = 5, `KITC` items pending setup |

`PREFIXRQDD` confirmed correct as it stands, five rows: `93-` WM, `94-` AG, `96-` RP, `97-` and `99-` SCREEN GRAPHICS.

---

## Notes carried forward

- No cross-customer orders occur, so prefix scoping never misfires in practice. Keeping the `UDCKEY` check anyway as a fail-safe.
- Floor on `IPTLT`: when short, use it only above 1. 5,491 plant-1 items sit at `IPTLT` = 1 with no UDC coverage and would otherwise get a next-day promise while short.
- `CalcReqD` counts from `%Date()`, the run date, not the customer PO date.
- Alerts staying live: `XMLConfig`, `XMLDrpShp`, `ORDUPDATE`, `XMLNOCUST`, and `XMLORDERR` once C11 lands.
