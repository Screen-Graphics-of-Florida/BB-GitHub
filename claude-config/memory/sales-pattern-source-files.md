---
name: sales-pattern-source-files
description: "Where the original Buyer Pattern deck, call workbooks and prompts live, and how they were generated"
metadata: 
  node_type: memory
  type: reference
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-27T18:14:16.009Z
---

The 2026-08-17 originals, all under
`C:\Users\Bill\Downloads\HarrisData\Custom Programming\Sales Patterns By Customer 2026\`:

- `Q4 Buyer Pattern.pptx` - the 10-slide deck. Also published as an Artifact:
  https://claude.ai/code/artifact/7c193f23-05c4-402d-a99d-06ef0b5987b6
- `Q4 Buyer Pattern - Call Lists.xlsx`
- `Tier Call Workbooks\` - `Tier 1..5 *.xlsx`, the format Bill wants exports to match
- `Sales Pattern Claude Prompt.txt` and `... Update 1.txt` - the original brief and the
  follow-up business rules
- `All Sales Invoice Detail 2023 To Present.xlsx` - the 61-column source extract (also .xlsb,
  and a 2020-to-present version)

**These were built by Claude on a different PC** (Bill's older machine, back from Lenovo repair),
using **Python + openpyxl 3.1.5**, timestamped 2026-08-17T16:31Z. The generating script is not on
this machine. It worked from the Excel extract, not from DB2 - which is why the deck's revenue
formula omits `DHORUF`: the extract has no such column, only DHQORD, DHQSTC, DHSLPR and a
precomputed TTLSALE. Do not treat the deck's formula as authoritative; see
[[harris-revenue-rules]].

**Each tier workbook has five parts**, worth copying:
1. `Read Me` - 38 rows: who is on the list, layout, column definitions, status legend, caveats,
   source line
2. `Summary by Salesperson` - Salesperson, Customers, $ At Stake, Stopped $/yr, Stopped Items,
   current-year Sales, Normal Year
3. One tab per salesperson - **one row per product**, 37 columns, ship-to and full address
   repeated on every row so any row survives filtering, sorting or printing on its own. Blue
   line marks each new customer; red rows = stopped items, amber = reduced.
4. `All Customers` - one row per customer, the first 24 of those columns
5. `Products Stopped` - SKU rollup: Customers Who Stopped, Annual $ Stopped, Annual Qty,
   2023-25 Revenue, Last Ordered By Anyone

The Read Me confirms "Normally Buys $/yr = their 2023-2025 total for that item divided by 3",
i.e. the window basis, which is what [[buyer-pattern-project]] defaults to.

Reproducing these from PHP on the IBM i cannot use openpyxl. Options are SpreadsheetML 2003 XML
(multi-sheet and formatted, pure PHP, lowest risk), real .xlsx via `ZipArchive`, or the `HSSF`
library already in the SG library list - check what that is used for before writing anything new.
