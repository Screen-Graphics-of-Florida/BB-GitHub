---
name: feedback-one-query-at-a-time
description: Give Bill one SQL statement at a time and wait for the result before the next
metadata:
  type: feedback
---

When diagnosing against the IBM i, hand Bill **one** statement, then stop and wait for
the output. Do not stack three or four queries in a message, and do not put a
non-runnable predicate fragment in a code block - he will paste it into Run SQL Scripts
and get a syntax error.

**Why:** Bill runs every statement by hand in Run SQL Scripts and pastes results back.
A message with four queries costs him four executions and a lot of scrolling to work out
which output I actually wanted. His words: "one a time and wait until I return the
results before you give me the next one."

**How to apply:** One statement per message. Say what each possible result means so he
knows what he is looking for before he runs it. Prefer a single query that reveals
several facts at once (SELECT * on a small table shows columns *and* data) over several
narrow ones. Before writing any predicate, confirm the real column names from
QSYS2.SYSCOLUMNS - guessing column names (FUPGM, HDPRGM, HDDOCT, SYDSGN) burned several
round trips on 2026-08-24.

Related: [[feedback-verify-before-update]]
