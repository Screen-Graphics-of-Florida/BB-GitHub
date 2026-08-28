-- =============================================================================
--  SG_PreUpgrade_DropCustomAccessPaths.sql
--
--  RUN THIS BEFORE ANY HarrisData install, upgrade, Cum, Fix Pack or Fix.
--
--  Purpose: remove every logical file and index that WE have created over a
--  HarrisData physical file, so the HarrisData update runs against untouched
--  Harris objects. Its matched pair is:
--
--      SG_PostUpgrade_RebuildCustomObjects.sql
--
--  Those two files must always be edited together. The rebuild script is the
--  authoritative inventory - if an object is not in it, it will not come back.
--
--  Standing rule (Bill, 2026-08-28): every custom database object - physical
--  file, logical file, index, view, table - belongs in SG5OBJ (Test) or SGOBJ
--  (Live). Nothing custom is created in SGHDSDATA, S5HDSDATA, SG5STDPGM or
--  HDSSTDPGM. This script exists for the exceptions: an index or logical file
--  has to sit over the Harris physical file it indexes, so those cannot live
--  in our own library and are therefore at risk from every upgrade.
--
--  -------------------------------------------------------------------------
--  *** READ THIS BEFORE YOU RUN ANYTHING ***
--
--  DO NOT drop indexes or logical files owned by HDS. They are HarrisData's
--  own and the ERP will not run without them. Measured 2026-08-28: there are
--  1,029 indexes over SGHDSDATA and another 1,029 over S5HDSDATA, and every
--  one of the 27 over OEORDH, OEORHD, OEORDT and HDCUST is owned by HDS.
--
--  A script that literally dropped "all logical files and indexes over the
--  HarrisData files" would destroy the installation. This script therefore
--  drops ONLY the named objects in Section B, and Section A merely REPORTS
--  what is out there so nothing of ours is missed.
--  -------------------------------------------------------------------------
--
--  How to run: ACS -> Run SQL Scripts, or STRSQL. One statement at a time,
--  reading the result of each. IBM i DB2 rejects trailing semicolons on a
--  statement run on its own, so the statements below carry none.
-- =============================================================================


-- =============================================================================
--  SECTION A - DISCOVERY. Report only. Nothing is changed by these.
-- =============================================================================

-- A1. Anything in the HarrisData schemas NOT owned by HDS. This is the catch-all
--     that finds custom objects somebody created in the wrong place. Expect one
--     row as of 2026-08-28: SGHDSDATA/J_HDINVC, a physical file owned by BILL,
--     created 2026-03-24. It is not ours to drop blindly - identify what uses it
--     first, and by the standing rule it should be moved to SGOBJ.
SELECT OBJNAME, OBJTYPE, OBJATTRIBUTE, OBJOWNER, CHAR(OBJCREATED) AS CREATED,
       COALESCE(OBJTEXT,'') AS OBJTEXT
  FROM TABLE(QSYS2.OBJECT_STATISTICS('SGHDSDATA','*FILE'))
 WHERE OBJOWNER <> 'HDS'
 ORDER BY OBJCREATED DESC

-- A2. Same for the Test data schema
SELECT OBJNAME, OBJTYPE, OBJATTRIBUTE, OBJOWNER, CHAR(OBJCREATED) AS CREATED,
       COALESCE(OBJTEXT,'') AS OBJTEXT
  FROM TABLE(QSYS2.OBJECT_STATISTICS('S5HDSDATA','*FILE'))
 WHERE OBJOWNER <> 'HDS'
 ORDER BY OBJCREATED DESC

-- A3. Same for the two program schemas
SELECT 'HDSSTDPGM' AS LIB, OBJNAME, OBJTYPE, OBJATTRIBUTE, OBJOWNER,
       CHAR(OBJCREATED) AS CREATED
  FROM TABLE(QSYS2.OBJECT_STATISTICS('HDSSTDPGM','*FILE'))
 WHERE OBJOWNER <> 'HDS'
UNION ALL
SELECT 'SG5STDPGM' AS LIB, OBJNAME, OBJTYPE, OBJATTRIBUTE, OBJOWNER,
       CHAR(OBJCREATED) AS CREATED
  FROM TABLE(QSYS2.OBJECT_STATISTICS('SG5STDPGM','*FILE'))
 WHERE OBJOWNER <> 'HDS'
 ORDER BY 1, 2

-- A4. Every index over the Buyer Pattern tables, with its owner, so you can see
--     at a glance that they are all Harris's. Anything here NOT owned by HDS is
--     ours and belongs in Section B.
SELECT i.TABLE_SCHEMA, i.TABLE_NAME, i.INDEX_NAME,
       COALESCE(o.OBJOWNER,'?') AS OWNER
  FROM QSYS2.SYSINDEXES i
  LEFT JOIN TABLE(QSYS2.OBJECT_STATISTICS('SGHDSDATA','*FILE')) o
         ON o.OBJNAME = i.INDEX_NAME
 WHERE i.TABLE_SCHEMA = 'SGHDSDATA'
   AND i.TABLE_NAME IN ('OEORDH','OEORHD','OEORDT','HDCUST')
 ORDER BY i.TABLE_NAME, i.INDEX_NAME

-- A5. Count of indexes per HarrisData schema, as a sanity check before and
--     after. Note the number. If it changes by more than the number of objects
--     you deliberately dropped in Section B, stop and investigate.
SELECT TABLE_SCHEMA, COUNT(*) AS INDEXES_OVER_THIS_SCHEMA
  FROM QSYS2.SYSINDEXES
 WHERE TABLE_SCHEMA IN ('SGHDSDATA','S5HDSDATA')
 GROUP BY TABLE_SCHEMA

-- A6. THE IMPORTANT ONE. Every SG-named object sitting in a HarrisData or
--     program schema, and which schemas hold it. These are ours by naming
--     convention even though HDS owns them, so an owner-based search misses
--     them - which is exactly how the four logicals below got overlooked once.
--     Note this list BEFORE the upgrade and compare it AFTER.
SELECT TABLE_NAME,
       COUNT(*) AS IN_N_SCHEMAS,
       CAST(LISTAGG(TABLE_SCHEMA, ', ') AS VARCHAR(150)) AS SCHEMAS
  FROM QSYS2.SYSTABLES
 WHERE TABLE_SCHEMA IN ('SGHDSDATA','S5HDSDATA','T1HDSDATA',
                        'HDSSTDPGM','SG5STDPGM')
   AND TABLE_NAME LIKE 'SG%'
 GROUP BY TABLE_NAME
 ORDER BY TABLE_NAME
--     Expected as at 2026-08-28: four rows, each in 5 schemas -
--       SGOEORDTL1, SGORHDL001, SGORHDL002, SGORHDL003


-- =============================================================================
--  SECTION B - THE DROPS
-- =============================================================================
--
--  *** THE LOGICAL FILES ARE ALREADY HANDLED BY AN EXISTING UTILITY.        ***
--  *** DO NOT DUPLICATE IT HERE. Two tools doing the same job will diverge. ***
--
--  Chris Hutchinson (Pro I.T. Resource Group) wrote this in Jan/Feb 2018 and it
--  is on a custom menu inside HarrisData:
--
--      SGPGM/CLFSONHD   CL   the DLTF/CRTLF list - the program that does the work
--      SGPGM/HLFSONHD   RPGLE  the prompt: asks BEFORE/AFTER and TEST/LIVE
--      SGPGM/DLFSONHD   DSPF   the screen
--      Source: SGSRC/QCLLESRC(CLFSONHD), SGSRC/QRPGLESRC(HLFSONHD),
--              SGSRC/QDDSSRC(DLFSONHD)
--
--  Run it from the HarrisData custom menu and answer B (before) and T or L.
--  Run it again afterwards with A (after) to rebuild.
--
--  It manages these four custom logical files over HarrisData physicals:
--
--      SGOEORDTL1   over OEORDT   By Plt/ReqDt(D)/Ord#/line#
--      SGORHDL001   over OEORHD   BY OEBDTE
--      SGORHDL002   over OEORHD   BY OEUDF1/OEBDTE
--      SGORHDL003   over OEORHD   BY OESLSM/OERQDT/OEORD#
--
--  CORRECTION, 2026-08-28: an earlier version of this file said no custom
--  logical files or indexes existed over HarrisData physicals. That was wrong.
--  The four above are live. They were missed because they are OWNED BY HDS, not
--  by a person - they get created under Harris authority - so an owner-based
--  search does not find them. The reliable marker is the SG name prefix, which
--  is what query A6 below uses.
--
--  ---------------------------------------------------------------------------
--  THREE GAPS IN CLFSONHD, found 2026-08-28. Until they are fixed, the manual
--  steps in Section B2 cover them.
--  ---------------------------------------------------------------------------
--
--  GAP 1 - S5HDSDATA is missing entirely. It needs a THIRD case, not a fix.
--
--          There are three databases and two program environments (confirmed
--          by Bill 2026-08-28). Both test databases are copied from SGHDSDATA
--          on demand:
--
--            SGHDSDATA  -> HDSSTDPGM   LIVE
--            T1HDSDATA  -> HDSSTDPGM   test copy, SAME environment as Live
--            S5HDSDATA  -> SG5STDPGM   test copy, its OWN environment
--
--          CLFSONHD's L branch handles SGHDSDATA and its T branch handles
--          T1HDSDATA. Both are correct and current - T1 is NOT stale. The
--          utility was written in 2018, before S5HDSDATA existed, so that
--          database is simply absent. Its four logicals (created 2025-12-27)
--          are never dropped or rebuilt by the menu utility.
--
--  GAP 2 - The program libraries are not touched at all.
--          All four logicals also exist in HDSSTDPGM and SG5STDPGM. Five
--          schemas hold each one; CLFSONHD manages two.
--          Full picture as at 2026-08-28, all four present in each of:
--              HDSSTDPGM, SGHDSDATA, SG5STDPGM, S5HDSDATA, T1HDSDATA
--          Note HDSSTDPGM serves BOTH SGHDSDATA and T1HDSDATA, so one set of
--          program-library logicals covers Live and T1 together.
--
--  GAP 3 - The AFTER branch would CREATE two objects that do not exist today.
--          SGORHHL001 (over OEORHH by HHLDTI) and SGORHHL002 (181-line join
--          logical over OEORHH and OEORDH) have DDS source in SG/SGSRC but no
--          compiled object in any schema. The DLTF is harmlessly monitored for
--          CPF2105, but the CRTLF is only monitored for CPF5813 (already
--          exists), so a successful create would resurrect two logicals nobody
--          currently has. Decide whether they are wanted before running A.
--
--  Not a bug, for the record: SGOEORDTL1 rebuilds from SRCFILE(SG/SGSRC) while
--  the others use SGSRC/QDDSSRC. That is correct - SGOEORDTL1's DDS exists only
--  in SG/SGSRC.
--
--  ---------------------------------------------------------------------------
--  B1a. SGOBJ.SGBOOKDT - do NOT drop, but know it is exposed
--  ---------------------------------------------------------------------------
--  Found 2026-08-28. A custom SQL view in SGOBJ - correctly placed - but it
--  READS four HarrisData tables:
--      SGHDSDATA.OEORHD, SGHDSDATA.OEORDT, SGHDSDATA.HDSLSM, SGHDSDATA.HDCUST
--
--  It does not need dropping before an upgrade. It DOES get destroyed if the
--  upgrade drops and recreates any of those four tables, and Chris's utility
--  knows nothing about it. The rebuild script carries its full definition, so
--  check for it afterwards. Note whether it exists BEFORE you start:
SELECT TABLE_SCHEMA, TABLE_NAME FROM QSYS2.SYSVIEWS WHERE TABLE_NAME = 'SGBOOKDT'

--  ---------------------------------------------------------------------------
--  B1. OUR OWN OBJECTS - nothing to drop
--  ---------------------------------------------------------------------------
--  Everything else we own sits in SGOBJ or SG5OBJ over OUR OWN physical files,
--  where a HarrisData upgrade cannot reach it. Do NOT drop these:
--
--      SGOBJ.BPCALLLOG      Buyer Pattern contact log
--      SG5OBJ.BPCALLLOG     Buyer Pattern contact log, Test
--      SGOBJ.SGCSTHST       Item cost history
--      SGOBJ.SGCSTHSTL1     index over SGOBJ.SGCSTHST
--      SGOBJ.SGCSTHSTL2     index over SGOBJ.SGCSTHST
--      ...and the other SGOBJ tables, all over our own files
--
--  ---------------------------------------------------------------------------
--  B2. THE DROPS - all three databases, self-contained
--  ---------------------------------------------------------------------------
--  Decision, Bill 2026-08-28: replicate Chris's functionality here rather than
--  modify SGPGM/CLFSONHD. His program stays exactly as it is. Run THIS instead
--  of the menu utility - not as well as it, or you will simply get "not found"
--  messages on the second run.
--
--  Nothing below depends on the library list. DLTF is fully qualified.
--
--  [CL] LIVE
--       DLTF FILE(SGHDSDATA/SGOEORDTL1)
--       DLTF FILE(SGHDSDATA/SGORHDL001)
--       DLTF FILE(SGHDSDATA/SGORHDL002)
--       DLTF FILE(SGHDSDATA/SGORHDL003)
--
--  [CL] TEST 1 - T1HDSDATA (shares HDSSTDPGM with Live)
--       DLTF FILE(T1HDSDATA/SGOEORDTL1)
--       DLTF FILE(T1HDSDATA/SGORHDL001)
--       DLTF FILE(T1HDSDATA/SGORHDL002)
--       DLTF FILE(T1HDSDATA/SGORHDL003)
--
--  [CL] TEST 2 - S5HDSDATA (own environment SG5STDPGM). Chris's utility does
--       NOT cover this one - it predates the database.
--       DLTF FILE(S5HDSDATA/SGOEORDTL1)
--       DLTF FILE(S5HDSDATA/SGORHDL001)
--       DLTF FILE(S5HDSDATA/SGORHDL002)
--       DLTF FILE(S5HDSDATA/SGORHDL003)
--
--  CPF2105 "object not found" on any of these is fine - it means the object was
--  already gone. Note that MONMSG cannot be used on a typed command; it only
--  works inside a compiled CL program, which is why Chris's version has it and
--  this list does not. Read each message and carry on.
--
--  ---------------------------------------------------------------------------
--  B3. THE PROGRAM LIBRARIES - decide, do not guess
--  ---------------------------------------------------------------------------
--  All four logicals also exist in HDSSTDPGM and SG5STDPGM. Chris's utility
--  never touched them, in ten years of upgrades, which is evidence they do not
--  need dropping - and HDSSTDPGM serves both SGHDSDATA and T1HDSDATA, so one
--  set covers Live and T1 together.
--
--  Do NOT drop these without asking HarrisData Support first. Left here as a
--  record, deliberately not enabled:
--
--       DLTF FILE(HDSSTDPGM/SGOEORDTL1)
--       DLTF FILE(HDSSTDPGM/SGORHDL001)
--       DLTF FILE(HDSSTDPGM/SGORHDL002)
--       DLTF FILE(HDSSTDPGM/SGORHDL003)
--       DLTF FILE(SG5STDPGM/SGOEORDTL1)
--       DLTF FILE(SG5STDPGM/SGORHDL001)
--       DLTF FILE(SG5STDPGM/SGORHDL002)
--       DLTF FILE(SG5STDPGM/SGORHDL003)
--
-- -----------------------------------------------------------------------------
--  READY BUT NOT ENABLED: the two performance indexes under consideration for
--  Buyer Pattern (Index Advisor, 2026-08-28, 1 second each to build). They have
--  NOT been created. If they are ever created, uncomment the matching DROP here
--  AND the CREATE in the rebuild script, in the same edit.
-- -----------------------------------------------------------------------------

-- DROP INDEX SGHDSDATA.SG_OEORDT_OREC
-- DROP INDEX SGHDSDATA.SG_OEORHD_TYDT
-- DROP INDEX S5HDSDATA.SG_OEORDT_OREC
-- DROP INDEX S5HDSDATA.SG_OEORHD_TYDT


-- =============================================================================
--  SECTION C - VERIFY
-- =============================================================================

-- C1. Confirm nothing of ours is left over a HarrisData file. Every row this
--     returns should be owned by HDS.
SELECT i.TABLE_SCHEMA, i.TABLE_NAME, i.INDEX_NAME,
       COALESCE(o.OBJOWNER,'?') AS OWNER
  FROM QSYS2.SYSINDEXES i
  LEFT JOIN TABLE(QSYS2.OBJECT_STATISTICS('SGHDSDATA','*FILE')) o
         ON o.OBJNAME = i.INDEX_NAME
 WHERE i.TABLE_SCHEMA = 'SGHDSDATA'
   AND COALESCE(o.OBJOWNER,'HDS') <> 'HDS'

-- C2. Re-run A5 and compare the counts to what you noted.

-- =============================================================================
--  WHEN THE UPGRADE IS FINISHED, RUN:
--      SG_PostUpgrade_RebuildCustomObjects.sql
--  It rebuilds our objects AND carries the HarrisData post-upgrade procedure
--  (the UDFs and the SQL Views that the OS fails to catalogue).
-- =============================================================================
