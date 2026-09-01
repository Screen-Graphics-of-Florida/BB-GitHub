---
name: upgrade-safety-scripts-project
description: "The HarrisData Upgrade Safety Scripts project - engineering is complete and deployed, the costing is paused pending Bill's rulebook revision; reopen from the RESUME HERE handoff file"
metadata: 
  node_type: memory
  type: project
  originSessionId: 4ca92f1d-6c4a-4459-ac9c-97977893b73e
  modified: 2026-08-28T17:55:43.219Z
---

**HarrisData Upgrade Safety Scripts.** Started and largely finished Friday 2026-08-28. A separate
project from Buyer Pattern - its time and cost are tracked on its own and deliberately excluded from
the Buyer Pattern figures.

**Engineering status: complete and deployed.** Two runbooks, byte-identical at
`W:\HarrisData\SG5\Custom\SG\` and the matching EIP path:
`SG_PreUpgrade_DropCustomAccessPaths.sql` (285 lines, run BEFORE any HarrisData update) and
`SG_PostUpgrade_RebuildCustomObjects.sql` (673 lines, run AFTER). Every SQL statement in them was
executed against the live system while they were written. They are runbooks, not pure SQL - steps are
labelled `[SQL]`, `[CL]` or `[MENU]` - and they are self-contained, to be run **instead of** Chris
Hutchinson's 2018 menu utility, not as well as it. His code was deliberately left untouched. The
rules and the utility's three gaps live in [[custom-db-objects-and-upgrades]]; the database layout in
[[sg-environment-topology]].

**To reopen, read this file first:**
`C:\Users\Bill\Downloads\HarrisData\Custom Programming\HarrisData Upgrade Safety Scripts - Costing HANDOFF - RESUME HERE 2026-08-28.md`

It carries the current status, the numbered task list, and the twelve gathered task line items with
hours. Alongside it in the same folder:
`HarrisData Upgrade Safety Scripts - Project Summary 2026-08-28.md` (the project record, sections 1
to 10) and `HarrisData Upgrade Safety Scripts - Outside Cost Bottom-Up Estimate 2026-08-28.md` (a
**drafted, not accepted** estimate - keep it only for its task line items).

**What is actually left:**

1. **The costing, paused by Bill on 2026-08-28** while he revises the memory files and the CLAUDE.md
   rulebook. Do not recalculate until he says the rulebook is done, then re-read
   [[feedback-project-time-and-cost]] fresh and rebuild from it. Carry forward no dollar figure,
   rate, multiplier or savings number from either existing document. The working time on record is
   36 m (12:38 - 13:14 ET); the estimate-drafting segment that followed, roughly 13:31 - 13:55 ET, is
   not yet measured or attributed.
2. **Dry-run both scripts end to end against `S5HDSDATA`.** Nothing blocks this and it is the item
   that matters most - until it is done the scripts are verified documentation, not a rehearsed
   procedure.
3. Four questions still open: are `SGORHHL001`/`SGORHHL002` wanted (source exists, no compiled
   object); should the `HDSSTDPGM`/`SG5STDPGM` copies be dropped before an upgrade (HarrisData
   Support); is `F_TIMESTMP` unshipped or missing from the HD5.0 Source install (Support); and
   `SGHDSDATA/J_HDINVC`, a physical file owned by BILL created 2026-03-24 inside a Harris schema,
   needs its users identified before it moves to `SGOBJ`. Also worth asking Support whether they
   accept custom indexes over their files at all.

**Findings from this project that nobody had catalogued** and that matter beyond it: `F_TIMESTMP` is
the one documented UDF that exists nowhere and has no source member, so `CRTSQLFNC FUNCTION(*ALL)`
cannot build it. The supplied procedure's `CHGOBJ` lists say `OEORDH17`, which does not exist - the
correct name is `OEORHD17`, so as written that step fails. `SGOBJ.SGBOOKDT` is a bookings view,
correctly placed, but it reads `OEORHD`, `OEORDT`, `HDSLSM` and `HDCUST` and was in nobody's upgrade
notes. `APOPENV02` is absent from `S5HDSDATA` while present in the other three. No custom indexes
exist anywhere - all 2,058 over the two data schemas are HarrisData's own.

Related: [[custom-db-objects-and-upgrades]], [[sg-environment-topology]],
[[feedback-project-time-and-cost]], [[feedback-post-deliverables-in-full]].
