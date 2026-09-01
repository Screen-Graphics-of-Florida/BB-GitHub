---
name: estimating-extracts-project
description: "NICKXTRCTS - nine HarrisData CSV extracts written via QNTC to the Claude-Extracts share for Nick's estimating tool; live and scheduled in Robot as CLAUDENGX"
metadata: 
  node_type: memory
  type: project
  originSessionId: 87a1eefa-c18f-4083-8d2f-7ce758d64c9b
  modified: 2026-08-28T22:56:53.481Z
---

**DELIVERED AND SCHEDULED, 2026-08-28.** Nine read-only CSV extracts from HarrisData land on
`\\SGFS1\HarrisDataAI\Claude-Extracts` for Nick's Excel estimating tool and for Cowork.

**Robot Schedule job `CLAUDENGX`** - runs **Monday to Saturday at 12:30 PM and 5:30 PM**, command
`CALL PGM(SGPGM/NICKXTRCTS)` under profile `HDCLXTRACT`. **It takes 3 to 4 minutes for all nine
files to populate** - anyone reading them right at the start of a run can catch a partial set, so
tell consumers to allow that window.

**Objects:**
- `SGPGM/NICKXTRCTS` - the one ILE CL program, all nine files
- `SGSRC/QCLLESRC(NICKXTRCTS)` and `SGSRC/QSQLSRC(NICKXTRCTS)` - source
- `W:\Claude-Extracts\src\` - IFS working copies of both source members, kept for adding files later
- `W:\Claude-Extracts\src\NICKXTRCTS-Harris Master File Layouts.xlsx` - nine-tab column layout doc
- Service profile **`HDCLXTRACT`** on both IBM i and Windows, matching passwords (password is NOT
  recorded here; `QPWDLVL` is 2 so it is case-sensitive)

**Transport is QNTC, not FTP.** `CPYTOIMPF` writes straight to
`/QNTC/192.168.120.11/HarrisDataAI/Claude-Extracts/`. No FileZilla account, no cleartext password
anywhere. Use the **IP**, not `SGFS1` - the IBM i cannot resolve the name.

**The nine files** (filename is the full three-part string, spaces and capitals as shown):

| Output file | Table | Cols |
|---|---|---|
| `Customer Master-HDCUST-customermaster.csv` | HDCUST | 88 |
| `Item Master-HDIMST-itemmaster.csv` | HDIMST | 36 |
| `Item Plant-HDIPLT-itemplant.csv` | HDIPLT | 128 |
| `Item Warehouse-HDIWHS-itemwarehouse.csv` | HDIWHS | 83 |
| `Product Structures-HDMPSM-productstructures.csv` | HDMPSM | 23 |
| `Routings-HDMRTM-routings.csv` | HDMRTM | 40 |
| `Standard Operations-HDMSDM-standardoperations.csv` | HDMSDM | 3 |
| `Work Centers-HDMWCM-workcenters.csv` | HDMWCM | 27 |
| `Work Center Costs-HDMEDT-workcentercosts.csv` | HDMEDT | 18 |

`HDMEDT` is filtered to `WHERE DECSET = 1` (Cost Set 1, Standard Costs). All others are unfiltered,
all columns.

**Conventions Bill settled - do not "improve" these:** uppercase CSV headers; IBM i timestamp
format left as-is; character columns blank becomes empty via `NULLIF(TRIM())`; **numeric columns
keep 0** (a real zero and no-value differ in cost and routing data); the redundant HarrisData
"(Uppercase)" shadow columns are included, suffixed `_UPPERCASE`.

**Hard-won IBM i details - these each cost a round trip:**
- `CPYFRMSTMF` must use **`STMFCCSID(819)`**, never 1208, and watch for
  `Stream file copied to object with truncated records` - `SGSRC/QSQLSRC` holds only **80**
  characters per line, `QCLLESRC` 100. Generated SQL must wrap to fit.
- `CPYTOIMPF` uses `MBROPT(*REPLACE)`, not `STMFOPT` (that belongs to `CPYTOSTMF`).
- **`MOV` has no `REPLACE` parameter** - `RMVLNK` the target first with a bare `MONMSG`.
- `SNDPGMMSG` cannot send impromptu `MSG()` as `*ESCAPE`; use `MSGID(CPF9898) MSGF(QCPFMSG)`.
- `RUNSQLSTM` needs `NAMING(*SYS)`, and ODBC needs `NAM=1`, because the SQL uses `LIB/FILE`.
- CL literal continuation `-` takes the next line from column 1 - never continue a path literal;
  concatenate with `%TRIM` and `*TCAT`.
- **`HDCLXTRACT` cannot create source members or compile** - `CPD3232 no authority` on `SGSRC`.
  That is correct least privilege: Bill runs the build under his own profile, the service account
  only reads HarrisData and writes to QNTC.

**Error behaviour:** one failing file sends a `*DIAG` naming it and the other eight still publish;
the job then ends with an escape so Robot shows it failed. A total SQL failure writes nothing.

**Adding a file later:** add a `CREATE TABLE QTEMP/NXxxxx` block to the SQL member and a matching
publish block to the CL, reload both with `STMFCCSID(819)`, `CRTBNDCL`, and update the layouts
workbook. See [[feedback-source-in-sgsrc-objects-in-sgpgm]], [[feedback-joblog-to-qezjoblog]],
[[feedback-ask-before-naming]], [[sg-environment-topology]].

**Background:** the original 2026-08-20 scoping doc lives at
`W:\Claude-Extracts\harrisdata-ai-extracts-context.md` (cleaned and corrected). Note the file
server is **`SGFS1`**, not SGSF1, and Bill's laptop `billacei9-12` uses a local account so it
cannot reach `\\SGFS1\...` - he views the share over TeamViewer.
