---
name: xml850r-dual-source-layout
description: SGSRC RPG source lives in two places - edit the IFS copy, but CRTSQLRPGI compiles the QSYS member
metadata:
  type: project
---

Chris Hutchinson's RPG / SQLRPGLE source for SGSRC exists as two separate copies that
must be kept in sync by hand:

- Edit copy (IFS stream files): `/home/CHUTCH/src/SGSRC/QRPGLESRC/<member>.sqlrpgle`,
  reachable from Windows at `W:\home\CHUTCH\src\SGSRC\QRPGLESRC\`
- Build copy (QSYS source physical file member): `SGSRC/QRPGLESRC`, reachable at
  `W:\QSYS.LIB\SGSRC.LIB\QRPGLESRC.FILE\<member>.MBR`

`CRTSQLRPGI ... SRCFILE(SGSRC/QRPGLESRC)` compiles the QSYS member, NOT the IFS file.
Editing the IFS copy alone compiles stale source - a `CPYFRMSTMF` step has to sit
between the edit and the compile. Established by timestamp forensics 2026-08-26: QSYS
members written 2026-08-18 11:31:01, about 4 minutes after the IFS files at
11:27:13-11:27:18, i.e. edit IFS -> push to QRPGLESRC -> compile.

Note also that `SGPGM` (the target *PGM library for these programs) sits at seq 50 in
BOTH the Test and Live library lists, so there is no SG5 copy - a
`CRTSQLRPGI OBJ(SGPGM/...) REPLACE(*YES)` is a direct-to-Live change with no test path.
See [[feedback-tick-before-promote]].
