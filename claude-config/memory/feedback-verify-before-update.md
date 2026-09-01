---
name: feedback-verify-before-update
description: Prove an UPDATE's scope on a known-good row before running it across many rows
metadata:
  type: feedback
---

Before running a set-based UPDATE on the EIP menu tables, prove the join and the WHERE
clause against a row that is already known to be correct. Run the SELECT form first and
read the rows it would change.

**Why:** On 2026-08-24 a broadened predicate ("fix every row that disagrees with
SYPORT") would have rewritten 247 legitimate hand-built rows in Bill's own BILL role -
`EMPLOYEE/REPORT` to `HDLIST/EMPLOYEE` and so on - because SYPORR.PRID is NOT required
to equal SYPORT.FPID at the same sequence. Only a preview SELECT caught it. Two other
near-misses the same day: a role named BILLING made `PRID LIKE 'BILLING/%'` match a
legitimate native PRID, and a blanket `PRSEL='Y'` would have granted departments roles
were never meant to have.

**How to apply:** SELECT before UPDATE, always, showing current value and replacement
side by side. Prefer the narrow provable scope (rows a known script wrote) over the
broad plausible one. Back up the whole table to SGOBJ first and verify the row counts
match. Note that a restore from a pre-change snapshot also reverts earlier fixes, so
take a fresh checkpoint between phases.

Related: [[feedback-one-query-at-a-time]], [[feedback-sypgmo-descriptions]]
