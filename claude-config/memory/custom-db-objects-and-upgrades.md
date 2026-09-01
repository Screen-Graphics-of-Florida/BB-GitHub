---
name: custom-db-objects-and-upgrades
description: "Every custom database object lives in SG5OBJ/SGOBJ, and two paired SQL scripts must exist to drop and rebuild anything we put over HarrisData files before an upgrade"
metadata: 
  node_type: memory
  type: feedback
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-28T17:02:03.153Z
---

**Every custom database object belongs in `SG5OBJ` (Test) or `SGOBJ` (Live).** Physical files,
logical files, indexes, views, tables, functions - all of it. Never create a custom object inside
`SGHDSDATA`, `S5HDSDATA`, `SG5STDPGM` or `HDSSTDPGM`. Bill set this rule on 2026-08-28.

**Why:** a HarrisData install, upgrade, Cum, Fix Pack or Fix can delete and recreate the HarrisData
physical files. Anything of ours sitting in their schemas goes with it, silently - no error, the
page just gets slow again or a report stops working. Keeping our objects in our own libraries also
means Harris support is never looking at unsupported objects in their schema.

**Two scripts must exist and stay current, and they are a matched pair:**

1. **Pre-upgrade drop script** - removes every logical file and index we have created over a
   HarrisData physical file, so the upgrade runs against untouched Harris objects. Bill runs this
   BEFORE any HarrisData update.
2. **Post-upgrade rebuild script** - recreates every single one of those items afterwards.

The rebuild script must also carry the HarrisData post-upgrade procedure itself, because it has to
be done in the same sitting: creating the UDFs with `CRTSQLFNC` in the program schema, and manually
creating the up-to-13 SQL Views that the OS fails to catalogue (`APOPENV01`, `APPAIDV01`,
`ETBLWK03/04`, `HDIMST05/09/16/17`, `HDINVCV01/02`, `OEORHD17`, `HDVENDV01`, `HDCUSTV01`) with
`SQLTBL` followed by `CHGOBJ` for each, in the program schema AND in every database schema, then
the menu steps `99,22,1`, `99,22,2` and `99,22,6` (change PUBLIC to `*USE`). Bill supplied that
procedure verbatim on 2026-08-28 and it is reproduced in the rebuild script.

Scripts live at `W:\HarrisData\SG5\Custom\SG\` and the matching EIP path:
- `SG_PreUpgrade_DropCustomAccessPaths.sql`
- `SG_PostUpgrade_RebuildCustomObjects.sql`

**A utility already exists for the logical files - do not duplicate it.** Chris Hutchinson (Pro
I.T. Resource Group) wrote it in 2018 and it sits on a custom menu inside HarrisData. It prompts
BEFORE/AFTER and TEST/LIVE, then deletes or rebuilds accordingly:

| Object | Type | Role |
|---|---|---|
| `SGPGM/CLFSONHD` | CLLE | holds the DLTF/CRTLF list - **the one to edit** |
| `SGPGM/HLFSONHD` | RPGLE | the B/A and T/L prompt only |
| `SGPGM/DLFSONHD` | DSPF | the screen |

Source: `SGSRC/QCLLESRC(CLFSONHD)`, `SGSRC/QRPGLESRC(HLFSONHD)`, `SGSRC/QDDSSRC(DLFSONHD)`.

It manages four custom logicals over HarrisData physicals, each present in five schemas
(`SGHDSDATA`, `S5HDSDATA`, `T1HDSDATA`, `HDSSTDPGM`, `SG5STDPGM`):
`SGOEORDTL1` (over OEORDT), `SGORHDL001` (OEORHD by OEBDTE), `SGORHDL002` (by OEUDF1/OEBDTE),
`SGORHDL003` (by OESLSM/OERQDT/OEORD#).

**Three known gaps in CLFSONHD, found 2026-08-28:**

1. **`S5HDSDATA` is missing entirely.** Its `T` branch handles `T1HDSDATA` and its `L` branch
   `SGHDSDATA`. That was complete in 2018 - `S5HDSDATA` came later. T1 is NOT a stale reference; it
   is a live test database that shares `HDSSTDPGM` with Live. The utility needs a **third** case,
   not a corrected one. See [[sg-environment-topology]].
2. It ignores the program libraries `HDSSTDPGM` and `SG5STDPGM`, which also hold all four logicals.
3. Its AFTER branch would create `SGORHHL001`/`SGORHHL002`, which have DDS source in `SG/SGSRC` but
   no compiled object anywhere - so running it would resurrect two logicals nobody currently has.
   The `CRTLF` is monitored only for CPF5813, so a successful create is not prevented.

Awaiting Bill's decision on whether `SGORHHL001/002` are wanted before the CL is updated.

**Owner is NOT a reliable way to find our objects.** Those four logicals are owned by `HDS`, not by
a person, because they get created under Harris authority. Search by the `SG` name prefix instead -
an owner-based query missed all four and led me to tell Bill, wrongly, that nothing custom existed
over the Harris files.

**How to apply:** whenever a custom index, logical file or any other object over a HarrisData file
is added, update BOTH scripts in the same turn, and `CLFSONHD` too if it is a logical file. The
rebuild script is the authoritative inventory - if an object is not in it, it will not survive the
next upgrade. The two indexes under consideration for Buyer Pattern performance were NOT created.

See [[buyer-pattern-project]] and [[feedback-write-both-config-copies]] for the same
write-both-copies discipline.
