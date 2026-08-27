# XML850R Lead Days Review

Evidence record for the proposed lead-days change to `XML850R_AR`, `XML850R_R1`,
`XML850R_WM` (source `SGSRC/QRPGLESRC`).

- Prepared 2026-08-26 14:57 EDT, system 192.168.120.40, spec reviewed: v2

## BOTTOM LINE

1. The v2 spec is mostly correct. Two things in it are not: Change 4 would stop
   Amerigas order intake, and Change 3 deletes two customer-service alerts that
   Change 5 says to keep.
2. `REQUIREDAY` is the correct spelling. The production code's `REQUIREDDAY` is
   the bug. The nine override rows have never worked since 2025-10-19.
3. With IPTLT evaluated first, five of those nine still will not work: they ask
   for 5 days, their IPTLT says 10.
4. Everything else checked out against the live system.

## STATE: nothing has been changed on the IBM i

- No source member edited, server or local. Nothing compiled.
- Every database call was a SELECT. No INSERT / UPDATE / DELETE.
- Only write to `W:` was an additive backup folder plus this file.
- The `UDCDETAIL` change (`WASTE MGT` to `WM`) was made by Bill, not this session.

---

## 1. Re-running any of this

Read-only, over the installed IBM i Access ODBC Driver. Use 192.168.120.40, not
10.10.0.5 (IBM i internal loopback, unreachable from a workstation).

```powershell
$cs = "DRIVER={IBM i Access ODBC Driver};SYSTEM=192.168.120.40;UID=bill;PWD=<password>;CMT=0;"
$conn = New-Object System.Data.Odbc.OdbcConnection $cs
$conn.Open()
$cmd = New-Object System.Data.Odbc.OdbcCommand "<paste a query here>", $conn
$dt = New-Object System.Data.DataTable
$dt.Load($cmd.ExecuteReader())
$dt | Format-Table -AutoSize
$conn.Close()
```

---

## 2. Findings against the live system

### 2.1 IPTLT exists on HDIPLT, as DECIMAL(5,1) - CONFIRMED

The spec makes `LeadDays = IPTLT` load-bearing. IPTLT appears in zero of the three
source members and nowhere in the QRPGLESRC tree, so it had to be checked against
the file itself. It is real and present in every environment schema. It is not an
integer: one decimal place, so assigning it to a zoned `5s 0` would truncate
silently under `TruncNbr(*Yes)`.

```sql
SELECT table_schema, column_name, data_type, length, numeric_scale
  FROM qsys2.syscolumns
 WHERE table_name = 'HDIPLT'
   AND (column_name LIKE '%LT%' OR column_name LIKE '%LEAD%')
 ORDER BY table_schema, column_name
```

27 rows. SGHDSDATA extract:

| Schema | Column | Type | Len | Scale |
|---|---|---|---:|---:|
| SGHDSDATA | IPALTS | DECIMAL | 11 | 0 |
| SGHDSDATA | IPLTC | CHAR | 1 | |
| SGHDSDATA | IPPLT | NUMERIC | 3 | 0 |
| SGHDSDATA | IPSLT | DECIMAL | 3 | 0 |
| SGHDSDATA | IPTLT | DECIMAL | 5 | 1 |

### 2.2 IPTLT populated for 23,462 of 23,466 plant-1 items - CONFIRMED

Values cluster on 1, 5 and 10 days, accounting for 23,268 of 23,466 items. Those
are exactly the numbers the current code hardcodes, so IPTLT appears to have been
maintained to mirror the existing rules, with about 200 genuine exceptions. No
fractional values exist today, so the truncation risk in 2.1 is latent.

This is also why the UDC cascade is close to unreachable: with IPTLT first, the
item / class / prefix tiers can only fire for the 4 plant-1 items where IPTLT is 0.

```sql
SELECT IPTLT, COUNT(*) AS ITEMS
  FROM SGHDSDATA.HDIPLT
 WHERE IPPLT = 1
 GROUP BY IPTLT
 ORDER BY IPTLT
```

| IPTLT | Items | IPTLT | Items | IPTLT | Items |
|---:|---:|---:|---:|---:|---:|
| 0 | 4 | 8 | 6 | 16 | 2 |
| 1 | 6,943 | 9 | 2 | 18 | 1 |
| 2 | 16 | 10 | 12,919 | 19 | 4 |
| 3 | 10 | 11 | 2 | 20 | 2 |
| 4 | 23 | 12 | 3 | 21 | 5 |
| 5 | 3,406 | 13 | 2 | 22 | 2 |
| 6 | 27 | 14 | 3 | 23 | 1 |
| 7 | 62 | 15 | 18 | 24 | 1 |
| | | | | 42 | 2 |

### 2.3 REQUIREDAY is correct, the running code is wrong - REVERSES AN EARLIER CLAIM

This review first flagged the spec's `UDCCODED='REQUIREDAY'` as a one-letter typo
for the code's `REQUIREDDAY`. That was backwards. The table holds REQUIREDAY with
one D. The production code's REQUIREDDAY with two D matches nothing, which means
the nine override rows have never once been honored since 2025-10-19. The spec
fixes a live bug rather than introducing one.

```sql
SELECT UDCSYSTEMD, UDCCODED, COUNT(*) AS N
  FROM PROITRG.UDCDETAIL
 WHERE UDCSYSTEMD = 'ORDERLINES'
 GROUP BY UDCSYSTEMD, UDCCODED
 ORDER BY UDCCODED
```

| UDCSYSTEMD | UDCCODED | Rows |
|---|---|---:|
| ORDERLINES | PCREQUIRED | 2 |
| ORDERLINES | PREFIXRQDD | 3 |
| ORDERLINES | REQUIREDAY | 9 |

PCREQUIRED and PREFIXRQDD already exist, so the three-tier cascade in the spec
was planned against real data.

Where the wrong spelling sits in source:

```
XML850R_AR  line 1333    UDCCODED = 'REQUIREDDAY'
XML850R_R1  line 1513    UDCCODED = 'REQUIREDDAY'
XML850R_WM  line 1329    UDCCODED = 'REQUIREDDAY'
```

### 2.4 Five of the nine item overrides conflict with IPTLT - CONFIRMED

The five 93- rows ask for 5 days while their plant lead time says 10. With IPTLT
first, as directed, those five resolve to 10 days and their override stays inert.
The other four agree with IPTLT and are no-ops either way. Their product classes
are SPCU and DPCU, which match none of the classes in the current hardcoded rules
and none of the PCREQUIRED rows, so they fall to the default branch today as well.

```sql
SELECT u.UDCKEY, u.UDCDESC1 AS UDC_DAYS, p.IPTLT AS PLANT_IPTLT, i.IMPCLS
  FROM PROITRG.UDCDETAIL u
  LEFT JOIN SGHDSDATA.HDIPLT p ON p.IPITEM = u.UDCKEY AND p.IPPLT = 1
  LEFT JOIN SGHDSDATA.HDIMST i ON i.IMITEM = u.UDCKEY
 WHERE u.UDCSYSTEMD = 'ORDERLINES' AND u.UDCCODED = 'REQUIREDAY'
 ORDER BY u.UDCKEY
```

| UDCKEY | UDC days | IPTLT | IMPCLS | Effect under IPTLT-first |
|---|---:|---:|---|---|
| 93-0015 | 5 | 10.0 | SPCU | override inert |
| 93-0016 | 5 | 10.0 | SPCU | override inert |
| 93-0405-DR | 5 | 10.0 | DPCU | override inert |
| 93-5119 | 5 | 10.0 | SPCU | override inert |
| 93-9640 | 5 | 10.0 | SPCU | override inert |
| 94-ATD | 10 | 10.0 | DPCU | no-op, agrees |
| 96-31000 | 10 | 10.0 | SPCU | no-op, agrees |
| 96-32000 | 10 | 10.0 | SPCU | no-op, agrees |
| 96-33000 | 10 | 10.0 | SPCU | no-op, agrees |

### 2.5 The prefix rows are customer-scoped, and now key correctly - RESOLVED IN DATA

An earlier note said UDCKEY values were too long for a 2-character class code and
must match a description on HDCCLS. Superseded: the WASTE MGT row was re-keyed to
WM, which is the class code and joins cleanly. SCREEN GRAPHICS matches nothing in
HDCCLS by design and acts as the all-customers default.

```sql
SELECT u.UDCCODED, u.UDCKEY, u.UDCINDEXED, u.UDCDESC1, l.CCCCDS AS CLASS_DESC
  FROM PROITRG.UDCDETAIL u
  LEFT JOIN SGHDSDATA.HDCCLS l ON l.CCCCLS = u.UDCKEY
 WHERE u.UDCSYSTEMD = 'ORDERLINES' AND u.UDCCODED = 'PREFIXRQDD'
 ORDER BY u.UDCKEY, u.UDCINDEXED
```

| UDCKEY | UDCINDEXED | Days | Resolves to | Applies to |
|---|---|---:|---|---|
| SCREEN GRAPHICS | 97- | 5 | no class row | every customer |
| SCREEN GRAPHICS | 99- | 5 | no class row | every customer |
| WM | 93- | 10 | Waste Management | class WM only |

### 2.6 The customer class chain resolves for all three retailers - CONFIRMED

`HDCUST.CMCCLS` is CHAR(2) and joins to `HDCCLS.CCCCLS`. All three XML retailers
carry a class, and all three are the only classes flagged CCORTY = 'A'. The value
is already in scope where it is needed: the detail loop performs
`Chain HDPCust# HDCUSTR` just above the lead-day block, at XML850R_AR line 1202,
XML850R_R1 line 1393, XML850R_WM line 1216. No extra file access required. It does
need guarding with `%Found`, same stale-record reason as the item master chain.

```sql
SELECT c.CMCUST, c.CMBLTO, c.CMCCLS, l.CCCCDS, l.CCALPH, l.CCORTY
  FROM SGHDSDATA.HDCUST c
  LEFT JOIN SGHDSDATA.HDCCLS l ON l.CCCCLS = c.CMCCLS
 WHERE c.CMCUST IN (1590000, 1000, 5000)
 ORDER BY c.CMCUST
```

| CMCUST | Retailer | CMCCLS | Class description | CCORTY |
|---:|---|---|---|---|
| 1,000 | republic | RP | Republic Services | A |
| 5,000 | wastemgmt | WM | Waste Management | A |
| 1,590,000 | amerigas | AG | Amerigas | A |

### 2.7 The availability fields belong to HDIWHS, not HDIPLT - CONFIRMED

Decides where the spec's Change 2 zeroing sits. IWOHQT, IWQOO and IWRESQ are all
on HDIWHS, so zeroing them belongs in the HDIWHS not-found branch as the spec has
it. IPCMTO and IPTLT are on HDIPLT and stay with that chain.

| File | Column | Type | Len | Scale |
|---|---|---|---:|---:|
| HDCUST | CMCCLS | CHAR | 2 | |
| HDIMST | IMPCLS | CHAR | 4 | |
| HDIPLT | IPCMTO | DECIMAL | 13 | 4 |
| HDIPLT | IPTLT | DECIMAL | 5 | 1 |
| HDIWHS | IWOHQT | DECIMAL | 13 | 4 |
| HDIWHS | IWQOO | DECIMAL | 13 | 4 |
| HDIWHS | IWRESQ | DECIMAL | 13 | 4 |

### 2.8 IPLTL does not exist anywhere - CONFIRMED, resolved

A mid-review instruction named IPLTL as the lead-days field. Searching both
business schemas returns 63 rows, every one IPTLT and none IPLTL. Confirmed since
as a transposition, so IPTLT stands.

```sql
SELECT table_schema, table_name, column_name, data_type, length, numeric_scale
  FROM qsys2.syscolumns
 WHERE column_name IN ('IPLTL','IPTLT')
   AND table_schema IN ('SGHDSDATA','S5HDSDATA')
 ORDER BY column_name, table_schema, table_name
```

---

## 3. Findings in the source, not the database

### 3.1 Change 4 cannot be applied as an uncomment in any of the three - BLOCKED

The spec asks to uncomment the XMLNODTL no-detail alert. Each member fails
differently, and the AR case would stop order intake outright.

- XML850R_AR: `ProcessedItems` is declared at line 204 and never incremented
  anywhere in the program. Uncommenting makes `If ProcessedItems = 0` always true,
  so the alert fires on every order and the `LeaveSR` at line 1489 skips the
  `Write OEHDWKR`. No Amerigas order would be written again.
- XML850R_R1: there is no XMLNODTL block at all. Nothing to uncomment.
- XML850R_WM: the block at lines 1464-1488 tests `ProcessedItem`, singular, at
  line 1467. That field is not declared in WM. Compile error.

Making it real needs a counter that is actually maintained: reset per order,
incremented after each successful `Write OEDTWKR`, tested after the detail
`EndFor`. That is new code, not an uncomment. Currently held.

### 3.2 Change 3 deletes two alerts that Change 5 says to keep - CONFLICT

The replacement block in Change 3 collapses the HDIPLT not-found branch to
`Avail = 0; LeadDays = 0;`. That discards the XMLConfig stock-item-not-set-up
alert, and the block also drops the XMLAvail short-stock alert to customer
service. Change 5 says leave all alerts alone. Both are preserved in the settled
design below, with original conditions and message text.

```
XMLConfig / stock item not set up
  XML850R_AR  1259-1286      XML850R_R1  1443-1466      XML850R_WM  1265-1293

XMLAvail / short stock to customer service
  fires inside the stock-named branch of the current day-count rules
```

### 3.3 The compile reads a different copy of the source than the one being edited

The editable copies are IFS stream files. The compile reads
`SRCFILE(SGSRC/QRPGLESRC)`, a QSYS source physical file. Separate objects. The
timestamps show the working order: edit the IFS copy, push it into QRPGLESRC, then
compile. Editing the IFS copy alone compiles stale source, so a `CPYFRMSTMF` step
has to sit between the edit and `CRTSQLRPGI`. The spec omits it.

| Member | Copy | Last written | Bytes | Lines |
|---|---|---|---:|---:|
| XML850R_AR | IFS | 2026-08-18 11:27:13 | 71,547 | 1,975 |
| XML850R_AR | QSYS | 2026-08-18 11:31:01 | 201,450 | |
| XML850R_R1 | IFS | 2026-08-18 11:27:15 | 78,781 | 2,137 |
| XML850R_R1 | QSYS | 2026-08-18 11:31:01 | 217,974 | |
| XML850R_WM | IFS | 2026-08-18 11:27:18 | 71,391 | 1,982 |
| XML850R_WM | QSYS | 2026-08-18 11:31:01 | 202,164 | |

All three IFS files are CRLF on every line, so spliced content must be CRLF.

### 3.4 Compiling into SGPGM is a direct-to-Live change - NEEDS SEPARATE APPROVAL

SGPGM sits at sequence 50 in both the Test and Live library lists, so there is no
SG5 copy of these programs to prove the change against first.
`CRTSQLRPGI OBJ(SGPGM/...) REPLACE(*YES)` replaces the program object serving live
order intake in a single step. Decide how to cover that before the compile, for
example saving the current *PGM objects so the previous version can be restored.

---

## 4. The settled design

IPTLT evaluated first, in-stock at one business day, prefix tier scoped by
customer class with SCREEN GRAPHICS as the all-customers default.

```
AddDays = 0;

Chain (O1PLT:O1ITEM) HDIPLT;
If %Found(HDIPLT);
   Avail    = (IWOHQT + IWQOO) - (IWRESQ + IPCMTO);
   LeadDays = IPTLT;                          // DECIMAL(5,1)
Else;
   Avail = 0;  LeadDays = 0;
   <XMLConfig alert, preserved verbatim>
EndIf;

<XMLAvail short-stock alert, original condition and text, preserved>

If Avail >= O1QOrd;
   AddDays = 1;                               // in stock, next business day

ElseIf LeadDays > 0;
   AddDays = %Int(LeadDays);                  // plant lead time
   If LeadDays > AddDays;                     // round a partial day up
      AddDays += 1;
   EndIf;

Else;
   ReqDays = 0;                               // zeroed first, so SQLCODE 100
                                              // cannot carry a prior line

   1. REQUIREDAY  on O1BItem                  // one D, matches the data
   2. PCREQUIRED  on WkClass                  // guarded, never a stale IMPCLS
   3. PREFIXRQDD  on UDCINDEXED, scoped:
        UDCKEY = CustClass  (2-char CMCCLS)
        or UDCKEY = 'SCREEN GRAPHICS'
        customer-specific wins; longest prefix breaks ties

   If ReqDays > 0;  AddDays = ReqDays;  Else;  AddDays = 10;  EndIf;
EndIf;

Exsr CalcReqD;        // already skips Sat, Sun and HOLIDAY rows, so every
                      // value above is a business-day count
```

### Field changes

| Field | Change |
|---|---|
| AddDays | normalized to `5s 0`. Currently `2s 0` in AR and R1, `7s 0` in WM. |
| LeadDays | new, `5s 1` to hold IPTLT faithfully, with the round-up above so a future fractional value cannot truncate to 0 and fall through to the default. |
| ReqDays | new, `5s 0`. |
| WkClass | new, `Like(IMPCLS)`. Captured under `%Found(HDIMST)`, blanked otherwise. |
| CustClass | new, `Like(CMCCLS)`. Captured under `%Found(HDCUST)`, blanked otherwise. |
| UDCDays, NullInd | become unused. Left in place to keep the diff minimal. |

Three things carried in beyond the v2 spec, each following from a decision rather
than adding scope: the two customer-service alerts stay, CustClass is taken from
the chain already in the loop, and LeadDays rounds a partial day up.

---

## 5. Still open

- Change 4, XMLNODTL. Held. Needs either a real counter or a decision to defer.
- The inert five. IPTLT-first is decided; consequence is five 93- overrides stay
  ineffective. Reversible later either by moving the cascade ahead of IPTLT or by
  correcting those five items IPTLT to 5.
- DCCU and DCSG. The current hardcoded 5-day rule for these classes disappears,
  and PCREQUIRED holds only KITC and KITS. Almost certainly harmless because IPTLT
  is reached first, but worth a glance if those items matter.
- Restore path for the compile. No SG5 copy of SGPGM exists, so decide how the
  previous program objects get saved before REPLACE(*YES).

---

## 6. Sequence, once approved

1. Source backups. Already done and checksum-verified (manifest below).
2. Splice the three IFS copies, produce a unified diff per member for review.
   Nothing reaches QRPGLESRC at this stage.
3. Review the diffs, approve or adjust.
4. Push into the QSYS source file with CPYFRMSTMF, one member at a time.
5. Save the current program objects so the running version can be restored.
6. Compile, noting this replaces the live object:

```
CRTSQLRPGI OBJ(SGPGM/XML850R_AR) SRCFILE(SGSRC/QRPGLESRC)
           SRCMBR(XML850R_AR) OBJTYPE(*PGM) COMMIT(*NONE)
           OPTION(*SYS) DBGVIEW(*SOURCE) REPLACE(*YES)
```

7. Replay an archived inbound order in test mode and check the resulting O1RQDT
   and H1RQDT against expectation before live traffic arrives.

---

## 7. Backup manifest

Taken 2026-08-26 14:57 EDT to `W:\Claude-Extracts\XML850R-backup-20260826-1457\`.
Each copy verified against its source by MD5 after copying.

| File | Origin | MD5 |
|---|---|---|
| XML850R_AR.sqlrpgle.ifs.bak | IFS | 07ec4fb859889c4f6b76e5daf20f1ffe |
| XML850R_AR.MBR.qsys.bak | QSYS | 60e631a9e48a524c3fd6e9064224df35 |
| XML850R_R1.sqlrpgle.ifs.bak | IFS | f0655e9d895d462f1937dcd04be58801 |
| XML850R_R1.MBR.qsys.bak | QSYS | 273e0423e80e3973ec75beabc3cc53f3 |
| XML850R_WM.sqlrpgle.ifs.bak | IFS | b3dbb8c60d24cbf0789e05488ffa49e8 |
| XML850R_WM.MBR.qsys.bak | QSYS | 8da3915b5c70a4ccbea0ba04347df0cb |

---

Programs authored by Chris Hutchinson, Pro I.T. Resource Group. Every figure came
from a read-only query against 192.168.120.40 or from the cited source line.
