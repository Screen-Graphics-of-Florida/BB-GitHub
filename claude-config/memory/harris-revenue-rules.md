---
name: harris-revenue-rules
description: "The agreed rules for computing SG sales revenue from OEORDH, including the DHQORD line-grain trap that inflates totals"
metadata: 
  node_type: memory
  type: project
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-27T18:13:16.373Z
---

Revenue from invoiced history in `SGHDSDATA` must be computed exactly this way. Confirmed
against live data 2026-08-27; these rules produced 2023 $11.13M, 2024 $13.03M, 2025 $12.53M,
2026-to-Aug $8.23M.

**Formula** (Bill's wording): `CASE WHEN DHSLPR = 0 THEN 0 ELSE DHQORD * DHSLPR / DHORUF END`.
Guard `DHORUF = 0` as well or the query dies on divide-by-zero. Ordered qty, never shipped -
overages ship but are not reliably billable.

**The trap: `DHQORD` restates the FULL line quantity on every partial-shipment row of OEORDH.**
Order 341181 line 44 carries qty 85 on nine separate rows. Summing row by row multiplies each
line by its shipment count and inflated 2025 from $13.6M to $18.8M. Always aggregate to
`(DHORD#, DHORL#)` grain first - `MAX(DHQORD)`, `MAX(DHSLPR)`, `MAX(DHORUF)` - then sum. Note
`DHQSTC` does NOT have this problem, since each row holds that shipment's own quantity.

**`DHORUF` is 0 or 1 throughout this data**, so the divisor changes yearly totals by $0.00.
Keep it anyway: every other SG page divides by it and a future item could be priced per 100.

**Exclusions, all required:**
- `DHSEQ# <> 0` on OEORDH. Never union OEORDT in - that double-counts.
- Order types `NOT IN ('P','Q','S','U','V')`. F and N were excluded earlier; Bill removed them
  2026-08-27 and it changed totals by $0.00.
- Entry code must be S: `EXISTS (SELECT 1 FROM OEORDT t WHERE t.ODORD# = d.DHORD# AND
  t.ODORL# = d.DHORL# AND t.ODOREC = 'S')`. Use EXISTS, not a join - OEORDT has multiple rows
  for some lines and a join re-introduces double counting. Drops 1,673 lines / $3.75M.
- Items: `<> 'AD0166'`, `NOT LIKE 'LTL%'`, `NOT LIKE '%SAMP%'`.
- Bill-to `NOT IN (9999999, 9999800, 9999201, 9999200, 9999100)`.
- Orders `NOT IN (356066, 347305, 356706)`.
- **INNER** join HDCUST and require a non-blank `CMCCLS`. Nine ship-tos carry orders with no
  HDCUST record at all and surfaced as a '??' customer class; a LEFT JOIN also let them slip
  past the bill-to exclusion because `COALESCE(CMBLTO, 0)` made NULL look like 0.

**Open orders are a different question.** `OEORHD.OEORST` and `OEORDT.ODORST` are the status
fields: `C` closed, `O` open. Value the UNSHIPPED balance, `(ODQORD - ODQSTD) * ODSLPR`. Filtering
on `ODOREC <> 'N'` instead of status counted 186,916 closed lines back to 1991 and reported
$38.2M of open work; the real figure is ~215 orders / ~$336K.

Periods are keyed on **order** date `OEORHD.OEBDTE`, not invoice date, whenever the question is
about when a customer orders. See [[buyer-pattern-project]] and [[harris-useful-fields]].
