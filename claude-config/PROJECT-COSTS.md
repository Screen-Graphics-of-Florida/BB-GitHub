# Project Time and Cost Register

Running record of every project costed under `CLAUDE.md` section 12.
**Source of truth for the numbers.** Append a row and a detail section as each project closes.
Never edit a closed row; corrections go in as a dated note under that project's detail.

Rates in force (revised 2026-08-28):
- In-house: **$76.16/hr** on unrounded actual working time.
- Outside contract band: **$90 low / $120 headline / $150 high**, costed on **midpoint hours**.
- Outside hours come from a **line-item bottom-up estimate**, never a blanket multiplier.

**How working time is measured.** Summed from Claude Code session transcripts, taking intervals
between consecutive events and dropping idle gaps over **15 minutes**. Duplicate and forked
sessions are de-duplicated by computing active time over the **union** of timestamps, so no minute
is counted twice. Where a session covers more than one project, its measured time is split in
proportion to the session's content by project. That split is an **allocation, not a stopwatch
measurement**, and is labelled as such.

---

## Summary

| # | Status | Project | Working time | In-house | Outside hrs (lo/mid/hi) | Outside @ $120 | Savings @ $120 | Multiple |
|--:|---|---|---|--:|---|--:|--:|--:|
| 1 | closed | Buyer / Sales Pattern | 11 h 03 m | $841.57 | 61 / 94 / 127 | $11,280.00 | $10,438.43 | 13.4x |
| 2 | closed | Program Security Layer | 9 h 22 m | $713.37 | 35 / 53 / 71 | $6,360.00 | $5,646.63 | 8.9x |
| 3 | closed | Estimating Extracts | 4 h 01 m | $305.91 | 26 / 39.5 / 53 | $4,740.00 | $4,434.09 | 15.5x |
| 5 | closed | IBM i email delivery fix | 2 h 40 m | $203.09 | 25 / 43 / 61 | $5,160.00 | $4,956.91 | 25.4x |
| | | **TOTAL (closed)** | **27 h 06 m** | **$2,063.94** | **147 / 229.5 / 312** | **$27,540.00** | **$25,476.06** | **13.3x** |
| 4 | **open** | XML850R dual-source layout | 3 h 38 m to date | $276.71 | 18 / 28 / 38 | $3,360.00 | $3,083.29 | 12.1x |
| 6 | **open** | Upgrade Safety Scripts | 1 h 22 m to date | $104.09 | 17 / 26.5 / 36 | $3,180.00 | $3,075.91 | 30.6x |

Projects 4 and 6 are **not finished** and are excluded from the totals. Their figures are
provisional and must be recomputed when they close. Upgrade Safety Scripts resumes week of
2026-08-31.

**Programme position at 2026-08-28.** Four closed projects, **27 h 06 m** of Bill's time, in-house
cost **$2,063.94**. The same work contracted out is estimated at **229.5 hours** (**6.12 weeks** at
37.5 h/week), costing **$20,655 to $34,425**, headline **$27,540**. Absolute envelope: floor
147 h x $90 = **$13,230**; ceiling 312 h x $150 = **$46,800**.

| Rate | Outside | In-house | Savings | Multiple |
|---|--:|--:|--:|--:|
| $90 | $20,655.00 | $2,063.94 | **$18,591.06** | 10.0x |
| $120 | $27,540.00 | $2,063.94 | **$25,476.06** | 13.3x |
| $150 | $34,425.00 | $2,063.94 | **$32,361.06** | 16.7x |

Even at the low end of the estimate the programme saved **$11,166.06**. At the midpoint, each hour
of your time produced **$1,016** of contracted-out work.

---

## 1. Buyer / Sales Pattern

**Closed:** 2026-08-28 · Combined entry covering the Buyer Pattern EIP page and the Sales Pattern
analysis deck and tier workbooks.

Next-quarter buyer call-list page in EIP, driven off Harris order history, with UDC-controlled
visibility (`BUYPATTERN` / `SALESPRSN` in `PROITRG.UDCDETAIL`), an append-only contact log, and the
supporting sales-pattern deck and tier workbooks.

**Working time: 11 h 03 m** = 663 min. Composed of 483 min measured and allocated from transcripts
plus **180 min added by Bill** for the original spreadsheet work done on this PC before the
transcripted sessions began. In-house cost **11.0500 hr x $76.16 = $841.57**.

| Work item | Low | High |
|---|--:|--:|
| Requirements: define call-list logic, quarters, tiers | 4 | 8 |
| Data analysis: Harris revenue model, DHQORD grain, avoid double counting | 6 | 14 |
| SQL and views for next-quarter candidates | 8 | 16 |
| EIP PHP page: filters, table, sorting, export | 12 | 24 |
| Contact log table DDL and append-only write path | 4 | 10 |
| UDC-driven visibility rules | 4 | 8 |
| Integrate with program security | 2 | 5 |
| Sales pattern deck and tier workbooks | 8 | 16 |
| Menu wiring (SYURLM, SYPORT, SYROLD, SYPGMO) | 2 | 4 |
| Testing, rework, deploy to SG5 and EIP | 8 | 16 |
| Documentation | 3 | 6 |
| **Total** | **61** | **127** |

Midpoint **94 h** = 2.51 weeks. Envelope floor $5,490, ceiling $19,050.

| Rate | Outside | Savings | Multiple |
|---|--:|--:|--:|
| $90 | $8,460.00 | $7,618.43 | 10.1x |
| $120 | $11,280.00 | $10,438.43 | 13.4x |
| $150 | $14,100.00 | $13,258.43 | 16.8x |

---

## 2. Program Security Layer

**Closed:** 2026-08-28

Reusable page-level security for every custom SG page: `SYPGMO` registration, the
`sgRequireAccess()` gate, and `SgProgramAccess.php` maintaining `SYPGMS` per environment, including
the lockout-avoidance design that only enforces once at least one grant exists.

**Working time: 9 h 22 m** = 562 min, de-duplicated across two forked sessions that shared 100% of
their events. In-house cost **9.3667 hr x $76.16 = $713.37**.

| Work item | Low | High |
|---|--:|--:|
| Design the model: SYPGMO / SYPGMS, roles versus program level | 4 | 8 |
| `sgRequireAccess()` gate helper | 4 | 8 |
| `SgProgramAccess.php` grant and revoke admin page | 10 | 20 |
| Environment resolution from SERVER_PORT, dual environment support | 2 | 5 |
| SYPGMO registration process and descriptions | 2 | 4 |
| Lockout-avoidance logic and enforcement-state badge | 3 | 6 |
| Testing across roles and users in both environments | 6 | 12 |
| Deploy and documentation | 4 | 8 |
| **Total** | **35** | **71** |

Midpoint **53 h** = 1.41 weeks. Envelope floor $3,150, ceiling $10,650.

| Rate | Outside | Savings | Multiple |
|---|--:|--:|--:|
| $90 | $4,770.00 | $4,056.63 | 6.7x |
| $120 | $6,360.00 | $5,646.63 | 8.9x |
| $150 | $7,950.00 | $7,236.63 | 11.1x |

---

## 3. Estimating Extracts

**Closed:** 2026-08-28

`NICKXTRCTS`: nine HarrisData extracts written as CSV to the Claude-Extracts share over QNTC, run
by Robot job `CLAUDENGX` twice daily Monday to Saturday.

**Working time: 4 h 01 m** = 241 min. In-house cost **4.0167 hr x $76.16 = $305.91**.

| Work item | Low | High |
|---|--:|--:|
| Define the nine extracts with the estimator | 3 | 6 |
| SQL for each extract (nine files) | 9 | 18 |
| CL driver program and QNTC share write | 4 | 8 |
| Robot scheduling, twice daily Mon to Sat | 2 | 4 |
| Error handling and joblog routing | 2 | 5 |
| Testing and validation of output files | 4 | 8 |
| Documentation | 2 | 4 |
| **Total** | **26** | **53** |

Midpoint **39.5 h** = 1.05 weeks. Envelope floor $2,340, ceiling $7,950.

| Rate | Outside | Savings | Multiple |
|---|--:|--:|--:|
| $90 | $3,555.00 | $3,249.09 | 11.6x |
| $120 | $4,740.00 | $4,434.09 | 15.5x |
| $150 | $5,925.00 | $5,619.09 | 19.4x |

---

## 4. XML850R dual-source layout - OPEN

**Status: not finished.** Figures below are provisional and are **excluded from the programme
totals**. Recompute in full when the project closes.

EDI 850 processing program where the IFS copy is edited but `CRTSQLRPGI` compiles the QSYS member,
requiring `CPYFRMSTMF` between the two. Establishing and documenting that workflow.

**Working time to date: 3 h 38 m** = 218 min, de-duplicated across three overlapping sessions.
In-house cost to date **3.6333 hr x $76.16 = $276.71**.

| Work item | Low | High |
|---|--:|--:|
| Establish the dual-source IFS and QSYS layout problem | 3 | 6 |
| Modify the SQLRPGLE source | 6 | 12 |
| CPYFRMSTMF workflow and compile process | 3 | 6 |
| Testing EDI 850 output | 4 | 10 |
| Documentation | 2 | 4 |
| **Total** | **18** | **38** |

Midpoint **28 h** = 0.75 weeks. Envelope floor $1,620, ceiling $5,700.

| Rate | Outside | Savings | Multiple |
|---|--:|--:|--:|
| $90 | $2,520.00 | $2,243.29 | 9.1x |
| $120 | $3,360.00 | $3,083.29 | 12.1x |
| $150 | $4,200.00 | $3,923.29 | 15.2x |

---

## 5. IBM i email delivery fix

**Closed:** 2026-08-28 · **System:** SGAS400V6, IBM i 7.5

Reports emailed from the IBM i reached some recipients and silently failed for others, undetected
since the M365 cutover on 18 April 2026. Root cause: `CHGSMTPA` **SMTP domain alias** was set to
`SCREEN-GRAPHICS.COM`, so the box claimed the company mail domain and delivered mail for three
bound users to `/QTCPTMM/MAIL/<profile>` instead of the M365 forwarding mailhub. Failure notices
were addressed to one of the trapped addresses, which is why nobody saw it. Fixed by setting the
alias to `*NONE` and recycling SMTP.

Impact recovered: 700 undelivered messages since April; the nightly "Open Order Date GT Today"
report had never once reached the Reports mailbox in four months; a separate incorrect address
(`manny@`) was corrected to `mrodriguezrivera@`.

**Working time: 2 h 40 m** = 160 min. In-house cost **2.6667 hr x $76.16 = $203.09**.

> **Note 2026-08-28:** this row originally read 2 h 14 m, measured from the fix session alone. The
> backfill allocation correctly attributes a further 26 min of mail diagnosis that occurred inside
> the earlier Buyer Pattern session. Revised with Bill's approval.

| Work item | Low | High |
|---|--:|--:|
| Scope the problem, reproduce, establish which addresses fail | 2 | 4 |
| Inventory 700 queued IFS messages, parse and classify headers | 3 | 7 |
| Analyse bounce notifications, extract recipients and status codes | 2 | 4 |
| Correlate failures against report schedules and job times | 1 | 3 |
| Verify outbound path: headers, SPF, DNS, MX, connectivity | 2 | 5 |
| Investigate and eliminate WRKNAMSMTP, WRKDIRE, ADDSMTPLE dead ends | 4 | 10 |
| Decode USERS.DAT (EBCDIC) and identify per-user domain binding | 3 | 8 |
| Locate SMTP domain alias parameter, confirm against config member | 2 | 6 |
| Design backup and rollback with verification | 1 | 3 |
| Execute change, recycle SMTP, test and verify | 2 | 4 |
| Safely archive and clear the stale queues | 1 | 3 |
| Documentation and handover | 2 | 4 |
| **Total** | **25** | **61** |

Midpoint **43 h** = 1.15 weeks. Envelope floor $2,250, ceiling $9,150.

| Rate | Outside | Savings | Multiple |
|---|--:|--:|--:|
| $90 | $3,870.00 | $3,666.91 | 19.1x |
| $120 | $5,160.00 | $4,956.91 | 25.4x |
| $150 | $6,450.00 | $6,246.91 | 31.8x |

---

## 6. Upgrade Safety Scripts - OPEN

**Status: not finished.** Work resumes week of 2026-08-31. Figures below are provisional and are
**excluded from the programme totals**. Recompute in full when the project closes.

Paired drop and rebuild scripts for custom objects built over HarrisData files, so a HarrisData
upgrade cannot silently destroy them. Custom objects live in `SG5OBJ` / `SGOBJ`.

**Working time to date: 1 h 22 m** = 82 min. In-house cost to date **1.3667 hr x $76.16 = $104.09**.

| Work item | Low | High |
|---|--:|--:|
| Inventory custom objects built over HarrisData files | 3 | 6 |
| Paired drop and rebuild scripts | 8 | 16 |
| Testing against an upgrade cycle | 4 | 10 |
| Documentation | 2 | 4 |
| **Total** | **17** | **36** |

Provisional midpoint **26.5 h**. At $120 that is $3,180.
