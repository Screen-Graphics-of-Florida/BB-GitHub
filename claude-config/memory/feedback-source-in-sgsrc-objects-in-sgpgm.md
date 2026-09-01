---
name: feedback-source-in-sgsrc-objects-in-sgpgm
description: Source goes in SGSRC, compiled objects only in SGPGM, log and data files in SGOBJ
metadata: 
  node_type: memory
  type: feedback
  originSessionId: 87a1eefa-c18f-4083-8d2f-7ce758d64c9b
  modified: 2026-08-28T21:56:09.396Z
---

Three libraries, three purposes. Standing rules from Bill, 2026-08-28.

| Library | Holds | Never holds |
|---|---|---|
| `SGSRC` | all source members | compiled objects |
| `SGPGM` | **compiled programs only** | source files, log files, data files |
| `SGOBJ` | **log files**, and every other custom data object | source |

**Source code goes in `SGSRC`. Only compiled programs go in `SGPGM`.** Never create source
physical files in `SGPGM` - that was a mistake made and cleaned up the same day.

**Log files go in `SGOBJ`, not `SGPGM`.** Bill's correction the same day, after Claude created an
`FTPLOG` physical file in `SGPGM`. A log is a data object, so it follows the same rule as every
other custom database object - see [[custom-db-objects-and-upgrades]], which is the authority on
`SGOBJ` (`SG5OBJ` on Test) and on keeping custom objects out of the HarrisData schemas.

The source files in `SGSRC`, all verified present and **all CCSID 37**:

| Source file | Holds |
|---|---|
| `SGSRC/QCLLESRC` | CLLE (ILE CL) source |
| `SGSRC/QDDSSRC` | DDS source |
| `SGSRC/QRPGLESRC` | ILE RPG source |
| `SGSRC/QSQLSRC` | SQL source |

**`QCLLESRC` means ILE CL, so use `SRCTYPE(CLLE)` and `CRTBNDCL`** - not `SRCTYPE(CLP)` with the
OPM `CRTCLPGM`. Getting this wrong produces source in the wrong file and an OPM object.

```
CRTBNDCL PGM(SGPGM/<name>) SRCFILE(SGSRC/QCLLESRC) SRCMBR(<name>)
```

Because these files are CCSID 37, any `CPYFRMSTMF` loading ASCII source written on the IFS must
use **`STMFCCSID(819)`** - see [[estimating-extracts-project]] for why 1208 fails with `SQL8018`.

**Why:** separating source from objects keeps the program library clean and makes the source
findable in one known place. `SGSRC` is where every other custom program on the box already keeps
its source, so anything created outside it becomes orphaned and invisible to whoever maintains it
next.

**How to apply:** before creating any IBM i object, put its source in the matching `SGSRC` file,
compile the object into `SGPGM`, and never leave source behind in a program library. For the IFS
edit / QSYS compile round trip on RPG members, see [[xml850r-dual-source-layout]].
