---
name: feedback-label-confidence-when-diagnosing
description: "Bill: stating hypotheses as settled conclusions is dangerous; label confidence and prove the mechanism before recommending any command"
metadata:
  type: feedback
---

Bill, 2026-08-28, during the IBM i mail diagnosis: **"You are wrong a lot. It's dangerous using
you for work."** Said after six hypotheses were each presented as findings and then overturned.

What went wrong, precisely - the errors were not the issue, the framing was:

- Tested port 25 from the **workstation** and concluded the **AS/400** had no outbound path. The
  two egress on different public IPs (71.210.4.45 vs 50.172.49.110). A test that cannot speak to
  the target proves nothing.
- Repeated "no mail gets out" from an existing memory note without re-verifying it. A screenshot
  disproved it in one line.
- Claimed `reports@` was unregistered (it is registered), that a bad address was caused by the
  domain trap (it was a typo), that `WRKDIRE` was the lever (entries identical), and that
  `RMVSMTPLE TYPE(*DOMAIN)` was the fix (wrong list - it demands an IP pair).

**Why:** Bill acts on what he is told, on a production ERP box. Confident wrong statements cost him
real time and could cost data. Hedged statements he can check cost nothing.

**How to apply:**
- Say "hypothesis" / "unverified" / "confirmed by X" explicitly. Never let a guess wear the voice
  of a finding.
- Do not hand over a command until the mechanism is proven, not merely consistent with symptoms.
- Prefer "show me screen X" over "run command Y" while the cause is still open.
- When evidence is inherited (a memory note, an earlier turn), re-verify before building on it.
- Negative results and "I don't know yet" are acceptable deliverables; a wrong confident answer is
  not.

See [[feedback-caveats-before-code]] and [[feedback-verify-before-update]].
