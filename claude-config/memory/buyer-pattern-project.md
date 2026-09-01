---
name: buyer-pattern-project
description: "The Buyer Pattern EIP page - what it is, where it lives, and what is still outstanding as of 2026-08-27"
metadata: 
  node_type: memory
  type: project
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-27T21:22:47.115Z
---

**Goal:** find customers who habitually order in the NEXT quarter so reps call them now and pull
those orders into the CURRENT quarter, plus a wider recovery list of lapsed and declining
accounts. Started as a Q4-specific analysis on 2026-08-17; generalised to any quarter on
2026-08-27 at Bill's request. Sales has to increase and the lever is calling existing customers.

**The page:** `SG/Order Entry/BuyerPattern.php`, on both SG5 Test and EIP Live. Reached from
SG Inquiries -> Order Entry -> Buyer Pattern. The `$reportMap` entry in
`SG/sg_portal_landing.php` is **Test only** - the Live menu entry was deliberately held pending
sign-off, so the page exists on Live but is unlinked. Earlier name `Q4BuyerPattern.php` was
retired into Backup Files when "Q4" came out of the title.

**Five drill levels:** L1 tiles -> L2 tier / customer class / stopped items -> L3 customer list
-> L4 one customer with every product -> L5 the raw order lines behind the money. Both seasonal
charts drill into L3 via a period filter. Chart.js 4.4.1 from cdnjs, matching SalesDashboard,
BookingsDashboard, CustClassSales5Yr and RevenueVsGoal.

**Five tiers** (target-quarter share >= 35% and orders in 2+ of 3 history years = "strong"):
1 strong + nothing this year; 2 strong + under half normal; 3 strong + buying normally;
4 lapsed, no strong skew; 5 under half normal, no strong skew. Counts on 2026-08-27:
53 / 66 / 120 / 1,671 / 531 - which tracks the 17 Aug deck's 54 / 63 / 120 / 1,688 / 447.

**"At stake" is tier-specific**, and this was a deliberate correction: a full normal year only
for tiers 1 and 4 (they bought nothing), the shortfall for tiers 2 and 5, and the average
target-quarter order value for tier 3 (they buy fine - only timing is in play). One normal year
= history divided by the full 3-year window, not by years active; `?basis=active` switches it.
Window basis totals $6.47M, active basis $10.03M. An earlier version summing a full normal year
for everyone produced $16.10M, which exceeds annual company sales and was wrong.

**Added 2026-08-27, later:** UDC-driven access control, an append-only contact log on Level 4
with a COO activity view, sales number and name shown on every view, both seasonal charts made
drillable by period, and CMac.ws / Yelp / Google Maps lookup buttons. See
[[buyer-pattern-access-and-log]].

**Still outstanding:**
- Live `$reportMap` entry + `SYPGMO` registration (needs a real description, never 'View'),
  plus `SGOBJ.BPCALLLOG` created and granted before the Live menu entry goes in.
- Follow-up alerts. Agreed shape: layer 1 in-page (overdue banner, "my follow-ups" view, count
  tile); layer 2 a nightly email using the existing `EMAIL` UDC pattern plus `SYUSER.USEMAL`,
  with RPGMAIL/ROBOTLIB as the likely path - needs Bill's call on scheduler and mail method.
- No dedicated rep breakdown at Level 2, though the sales number is now shown everywhere.
- Exports are single-sheet CSV; Bill wants multi-sheet formatted workbooks like the originals.
  See [[sales-pattern-source-files]].
- Status vocabulary is coarser than the workbooks': mine collapses to `stopped`, theirs splits
  STOPPED-was-regular-thru-2025 / none-since-2024 / none-since-2023, the first being the
  strongest reorder ask.
- Not yet added though available: Product Group, Order Entered By, Address 2, Days Since.

Data rules live in [[harris-revenue-rules]]; field locations in [[harris-useful-fields]].
