---
name: feedback-write-both-config-copies
description: Any change to CLAUDE.md or settings.json must be written to the live path AND the claude-config repo copy in the same turn
metadata: 
  node_type: memory
  type: feedback
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-27T21:27:53.327Z
---

Whenever `CLAUDE.md` or `settings.json` changes, write the change to **both** locations in the
same turn. Never update one and leave the other stale, and never ask Bill to copy it across
afterwards.

| Live path | Repo copy |
|---|---|
| `C:\Users\Bill\CLAUDE.md` | `C:\Users\Bill\SG GitHub\claude-config\CLAUDE.md` |
| `C:\Users\Bill\.claude\settings.json` | `C:\Users\Bill\SG GitHub\claude-config\settings.json` |

**Why:** Bill works across two PCs and is reformatting the current one for personal use. The
repo is what carries his config to the other machine, so a live-only edit is lost at the moment
he switches, and a repo-only edit does nothing on the machine he is actually using. He asked for
this explicitly on 2026-08-27 after I told him to copy the files across by hand - that manual
step is exactly what he does not want.

**How to apply:** edit the live file first, copy it to the repo, then verify with `cmp` that the
two are byte-identical and say so. The rule is also recorded as section 10 of `CLAUDE.md`
itself, so it survives in any session on any machine even without this memory.

The auto-memory folder needs no copying - `.claude\projects\c--Users-Bill\memory` is a junction
pointing into `claude-config\memory`, so memory writes are already in the repo. Committing and
pushing stays Bill's call; never push on his behalf.

See [[claude-config-portable]].
