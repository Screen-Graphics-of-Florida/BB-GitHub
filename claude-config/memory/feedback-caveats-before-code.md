---
name: feedback-caveats-before-code
description: State decisions, questions and caveats BEFORE giving SQL or code, never after
metadata:
  type: feedback
---

Structure every answer that contains SQL or code as:

1. Anything Bill needs to decide, stated up front
2. Caveats, risks, and what the statement will and will not touch
3. The SQL or the file

**Why:** I repeatedly wrote out a full statement and then added "one thing to decide
before you run it" underneath. Bill: "You give instructions then say Oh one thing to
decide before you do x. You work backwards and that isn't acceptable." He reads the SQL
first and runs it - a caveat placed after the code has already been skipped. On
2026-08-24 this pattern nearly had him run a wholesale SYPGMO replace before reaching
the note that it would drop Test-only registrations.

**How to apply:** Before writing a single line of SQL, work out what it could destroy,
what it leaves behind, and any choice that changes its shape. Put that first. If a
choice has an obvious safe default (additive over destructive), make the call, say so
in one line, and proceed - do not hand back a menu of options. He wants to do a thing
once, quickly.

Related: [[feedback-verify-before-update]], [[feedback-one-query-at-a-time]]
