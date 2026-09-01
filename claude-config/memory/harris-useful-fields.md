---
name: harris-useful-fields
description: "Harris field names verified 2026-08-27 - product group, order-entered-by, address lines, and why contact names are unavailable"
metadata: 
  node_type: memory
  type: project
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-27T18:13:34.859Z
---

Field locations confirmed by querying `QSYS2.SYSCOLUMNS` and the data itself on 2026-08-27.
Saves re-deriving these.

**Product Group / Class** - `OEORDH.DHPGRP` (CHAR 4, "Prod Group") sits on the invoice history
line itself, so no join to the item master is needed. `OEORDT.ODPGRP` is the open-order twin.
Product Class is also on the line. `HDIWHS.IWPGRP` carries it at warehouse level.

**"Order Entered By (SG)"** - `OEORHD.OEUDF1` (CHAR 15, "User-Def Field 1"). Holds the Screen
Graphics person who keyed the order: LAUREN, TERRI, LARI ANN, ADRIANE, KELLY, TIVANI, ERIN,
ALEXANDRA, DANIELLE and about 22 values in all. **67% blank** (26,582 of 39,397 orders since
2023), so expect it empty for most customers. Not the salesperson and not the customer's buyer,
but that person has most likely spoken to them. Dead ends checked: `OEORDT.ODUDA1` is blank in
all 190,576 rows, and `HDCUST.CMUDF1` holds something else (3,286 distinct values over 5,910
customers).

**Customer address lines** - `HDCUST.CMCNA1` is the name; `CMCNA2` = Address #1, `CMCNA3` =
Address #2, `CMCNA4` = Address #3. Then `CMCCTY` city, `CMST` state, `CMZIP`, `CMPHON` phone,
`CMCCLS` class, `CMSLSM` salesperson, `CMBLTO` bill-to.

**Customer contact names are effectively unavailable.** `CRCNTM` exists with the right columns
(`CRLNAM`, `CRFNAM`, `CRTITL`, `CRCUST`) but holds only 493 rows across 395 customers, and no row
passed a test for a populated last name on an active record. Any "Contact Name" column should be
left blank for the rep to fill in. NOTE: the 17 Aug deck claimed "333 of 5,141 ship-tos have
one" - that conflicts with what I measured, so re-check before promising a populated column.

**Class and salesperson lookups** - `HDCCLS`: `CCCCLS` code, `CCCCDS` description.
`HDSLSM`: `SMSLSM` code, `SMSNA1` name, `SMREGN` region.

**DB2 gotcha:** `DECIMAL / DECIMAL` can truncate the quotient's scale to 0, turning a ratio into
0 or 1. A share calculation done in SQL returned the same count at every threshold because of
this. Cast to `DOUBLE` before dividing, or compute ratios in PHP.

See [[harris-revenue-rules]] and [[buyer-pattern-project]].
