---
name: sg-environment-topology
description: "Three databases, two program environments - T1HDSDATA shares HDSSTDPGM with Live, only S5HDSDATA has its own program library"
metadata: 
  node_type: memory
  type: project
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-28T17:01:38.144Z
---

Confirmed by Bill 2026-08-28. Both test databases are **copied from `SGHDSDATA` on demand**, so
their data is a point-in-time snapshot of Live, not independent data.

| Database | Program library | What it is |
|---|---|---|
| `SGHDSDATA` | `HDSSTDPGM` | **LIVE** |
| `T1HDSDATA` | `HDSSTDPGM` | test copy of Live, running the **same** environment as Live |
| `S5HDSDATA` | `SG5STDPGM` | test copy of Live, with its **own** environment (SG5 / HD5) |

**Why it matters:**

- "Test" is ambiguous. There are two test databases. Anything that says "test" must name the
  database - `T1HDSDATA` or `S5HDSDATA`.
- `T1HDSDATA` shares `HDSSTDPGM` with Live, so a program-library object serves Live and T1 at once.
  `S5HDSDATA` is the only database with a separate program library.
- Anything written before `S5HDSDATA` existed covers only `SGHDSDATA` and `T1HDSDATA`. That is
  exactly the gap in `SGPGM/CLFSONHD`, Chris Hutchinson's 2018 logical-file utility: its TEST branch
  targets `T1HDSDATA`, which was correct then and still works, but `S5HDSDATA` is simply absent from
  it. Not a stale reference - a missing third case. See
  [[custom-db-objects-and-upgrades]].
- The web portal on port 5610 reads its portal/menu tables from `S5HDSDATA` and 5601 from
  `SGHDSDATA`, while business data is always `SGHDSDATA` in both - see CLAUDE.md section 3.

Recorded in CLAUDE.md section 3 as well, so it survives on any machine.
