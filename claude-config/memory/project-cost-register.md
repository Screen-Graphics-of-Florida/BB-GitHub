---
name: project-cost-register
description: "All project time-and-cost figures go in claude-config\PROJECT-COSTS.md, the register Bill presents to management"
metadata:
  type: project
---

Set up 2026-08-28. Bill needs project cost figures in **one place** he can present to management on
a regular basis, rather than scattered across chats and Evernote notes.

**The register:** `C:\Users\Bill\SG GitHub\claude-config\PROJECT-COSTS.md`

- Summary table (one row per project) plus a full detail section per project.
- Lives in the `claude-config` repo, so it travels between machines and is version-controlled.
- Chosen over Evernote because it can be appended automatically and diffed; Evernote keeps the
  narrative writeups, the register keeps the numbers.

**Required on every project close** (also written into `CLAUDE.md` section 12):
append a summary row and a detail section, **recompute the TOTAL row**, and still post the full
writeup in the chat - see [[feedback-post-deliverables-in-full]] and
[[feedback-project-time-and-cost]].

Entry 1 is the IBM i email delivery fix - see [[ibmi-mail-not-delivered]].

**Not yet backfilled:** Buyer Pattern, Upgrade Safety Scripts, Estimating Extracts. Their figures
must be **recomputed** under the current rate band, not carried over - the old "rounded hours x 3 x
$150" method is superseded.
