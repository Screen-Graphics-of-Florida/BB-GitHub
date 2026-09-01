---
name: feedback-joblog-to-qezjoblog
description: "For IBM i objects, always route error joblogs to QEZJOBLOG so failures can be read back"
metadata: 
  node_type: memory
  type: feedback
  originSessionId: 87a1eefa-c18f-4083-8d2f-7ce758d64c9b
  modified: 2026-08-28T20:43:13.772Z
---

**For IBM i objects, output all error joblog to `QEZJOBLOG`.** Standing instruction from Bill,
2026-08-28.

Any command or job that creates, compiles, or runs an IBM i object - `CRTCLPGM`, `CRTSQLRPGI`,
`CRTBNDRPG`, `RUNSQLSTM`, a submitted extract job - must leave its joblog in `QEZJOBLOG` when it
fails, rather than vanishing with the job.

The usual mechanism is to raise logging and point the job's output there before running the work:

```
CHGJOB LOG(4 00 *SECLVL) LOGCLPGM(*YES) OUTQ(QUSRSYS/QEZJOBLOG)
```

and for submitted work, carry the same on the `SBMJOB`:

```
SBMJOB CMD(CALL PGM(...)) LOG(4 00 *SECLVL) LOGCLPGM(*YES) OUTQ(QUSRSYS/QEZJOBLOG)
```

**Why:** an IBM i failure message on the screen is a one-line summary - "Program X not created",
"not authorized" - with the actual cause sitting in the joblog. Without the joblog spooled
somewhere findable, diagnosis becomes guess-and-retry, which wastes Bill's time and burns turns.
`QEZJOBLOG` is the agreed place to find it.

**How to apply:** when handing Bill IBM i commands that create or run objects, include the joblog
routing rather than waiting for a failure and then asking for it. When something does fail, ask
for the joblog from `QEZJOBLOG` - `WRKSPLF` / `WRKOUTQ QUSRSYS/QEZJOBLOG` - instead of proposing
a speculative fix. See [[ibmi-operational-lessons]] and [[feedback-one-query-at-a-time]].
