# claude-config

Portable Claude Code context for Bill, shared across machines.

| Path | What it is |
|---|---|
| `CLAUDE.md` | Machine/environment notes. Copy to `C:\Users\Bill\CLAUDE.md` on a new machine. |
| `memory/` | Auto-memory. The live folder is a **junction** pointing here. |
| `settings.json` | Sanitized Claude Code settings. Copy to `C:\Users\Bill\.claude\settings.json`. |

## Setting up a new machine

1. Clone/pull this repo.
2. Copy `CLAUDE.md` to `C:\Users\Bill\CLAUDE.md`.
3. Copy `settings.json` to `C:\Users\Bill\.claude\settings.json`.
4. Point the memory folder here (no admin rights needed):

```powershell
$live = "C:\Users\Bill\.claude\projects\c--Users-Bill\memory"
$repo = "C:\Users\Bill\SG GitHub\claude-config\memory"
if (Test-Path $live) { Rename-Item $live "memory.bak-$(Get-Date -Format yyyyMMdd_HHmmss)" }
New-Item -ItemType Junction -Path $live -Target $repo
```

## Rules

- **Never** add `.claude/.credentials.json` or any real password to this repo.
- The folder name `c--Users-Bill` is derived from the working directory. It only matches if the
  new machine is also `C:\Users\Bill` — check first.
- Do not run Claude Code on two machines at once against this memory folder; writes will conflict.
- Session transcripts (`*.jsonl`) are deliberately not synced.
- Synced memory carries distilled facts, not conversations. Anything worth keeping must be
  written into `memory/` or `CLAUDE.md` on purpose.
