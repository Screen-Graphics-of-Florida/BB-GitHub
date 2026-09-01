---
name: claude-config-portable
description: "How Bill's CLAUDE.md and auto-memory are shared across machines, and the one folder that must never be synced"
metadata: 
  node_type: memory
  type: project
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-27T18:14:30.613Z
---

Bill works across two PCs - a company machine and his own, which he bought himself and is
reformatting for personal use once he moves back to the older Lenovo (returned 2026-08-27 after a
CPU replacement). Set up 2026-08-27 so context follows him.

**The repo:** `C:\Users\Bill\SG GitHub\claude-config\`, inside the existing `SG GitHub` git repo
(remotes: `Screen-Graphics-of-Florida/BB-GitHub`, and `bbuschsg/bbusch-SG-GitHub` as a second
push target). It holds `CLAUDE.md`, `memory\` and a sanitized `settings.json`.

**The live memory folder is a junction** pointing at the repo copy:
`.claude\projects\c--Users-Bill\memory` -> `SG GitHub\claude-config\memory`. Memory writes
therefore land in the repo automatically and a `git pull` on the other machine brings them over.
Junctions need no admin rights, unlike symlinks. The original folder was preserved as
`memory.bak-<stamp>` rather than deleted.

**Never sync `.claude\.credentials.json`** - it sits in the same tree and is a live auth token.
The repo covers only CLAUDE.md, memory and sanitized settings.

**Two things that will bite:**
- The folder name `c--Users-Bill` is derived from the working directory. It only matches on
  another machine if that machine is also `C:\Users\Bill`. Check before junctioning there.
- Do not run Claude Code on both machines at once against a synced memory folder; concurrent
  writes conflict.

**What still does NOT travel:** session transcripts (`*.jsonl`, deliberately not synced - large
and per-session), keybindings, plugins. claude.ai connectors are account-level and follow the
login automatically. Most importantly, **synced memory carries distilled facts, not
conversations** - anything worth keeping has to be written into memory or CLAUDE.md on purpose.
On 2026-08-27 a whole sales-analysis project was invisible to a new session for exactly this
reason; see [[sales-pattern-source-files]].
