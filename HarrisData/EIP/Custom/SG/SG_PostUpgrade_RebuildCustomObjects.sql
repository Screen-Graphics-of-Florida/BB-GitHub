-- =============================================================================
--  SG_PostUpgrade_RebuildCustomObjects.sql
--
--  RUN THIS AFTER ANY HarrisData install, upgrade, Cum, Fix Pack or Fix.
--
--  Matched pair with:  SG_PreUpgrade_DropCustomAccessPaths.sql
--  Always edit both in the same sitting. THIS FILE IS THE AUTHORITATIVE
--  INVENTORY - if a custom object is not listed in Part 1, it will not survive
--  the next upgrade.
--
--  This file is a RUNBOOK, not a pure SQL script. It contains three kinds of
--  step and each is labelled:
--        [SQL] run in ACS "Run SQL Scripts", or STRSQL
--        [CL]  type on an IBM i command line
--        [MENU] a HarrisData menu option
--
--  IBM i DB2 rejects a trailing semicolon on a statement run on its own, so
--  the SQL below carries none.
--
--  Order matters. Work top to bottom.
-- =============================================================================


-- #############################################################################
--  PART 1 - OUR CUSTOM OBJECTS OVER HarrisData FILES
-- #############################################################################
--
--  *** THE LOGICAL FILES ARE REBUILT BY AN EXISTING UTILITY, NOT BY THIS FILE.
--
--  Chris Hutchinson (Pro I.T. Resource Group, 2018), on a custom HarrisData
--  menu. Run it and answer A (after) plus T or L:
--
--      SGPGM/CLFSONHD   CL     does the DLTF/CRTLF work
--      SGPGM/HLFSONHD   RPGLE  the BEFORE/AFTER and TEST/LIVE prompt
--      SGPGM/DLFSONHD   DSPF   the screen
--
--  It rebuilds these four custom logicals over HarrisData physicals:
--      SGOEORDTL1  over OEORDT  from SRCFILE(SG/SGSRC)
--      SGORHDL001  over OEORHD  from SRCFILE(SGSRC/QDDSSRC)
--      SGORHDL002  over OEORHD  from SRCFILE(SGSRC/QDDSSRC)
--      SGORHDL003  over OEORHD  from SRCFILE(SGSRC/QDDSSRC)
--
--  CORRECTION, 2026-08-28: an earlier version of this file claimed no custom
--  logicals existed over HarrisData physicals. Wrong - these four are live.
--  They are owned by HDS, not by a person, so an owner-based search misses
--  them. Use the SG name prefix (query 1.1 below).
--
--  KNOWN GAPS in CLFSONHD - see the pre-upgrade script for the detail:
--    1. S5HDSDATA is absent. The utility handles SGHDSDATA (L) and T1HDSDATA
--       (T), both of which are current - it predates S5HDSDATA and needs a
--       third case. Three databases, two program environments:
--           SGHDSDATA -> HDSSTDPGM  LIVE
--           T1HDSDATA -> HDSSTDPGM  test copy, same environment as Live
--           S5HDSDATA -> SG5STDPGM  test copy, its own environment
--    2. it does not touch HDSSTDPGM or SG5STDPGM, which also hold all four
--    3. its AFTER branch would create SGORHHL001/SGORHHL002, which have DDS
--       source but no compiled object anywhere today
--
--  ===========================================================================
--  1.2  THE REBUILD - all three databases, self-contained
--  ===========================================================================
--  Decision, Bill 2026-08-28: replicate Chris's functionality here rather than
--  modify SGPGM/CLFSONHD. His program stays exactly as it is. Run THIS instead
--  of the menu utility, not as well as it.
--
--  *** THE LIBRARY LIST IS NOT OPTIONAL HERE. READ THIS. ***
--
--  The DDS names the physical file WITHOUT a library:
--
--        R OEORHDR      PFILE(OEORHD)              <- SGORHDL001/002/003
--        R OEORDTR      PFILE(OEORDT)              <- SGOEORDTL1
--        R SGORHHF002   JFILE(OEORDH OEORHH)       <- SGORHHL002
--
--  So CRTLF binds the logical to whichever OEORHD/OEORDT the JOB'S LIBRARY LIST
--  finds first. Creating S5HDSDATA/SGORHDL001 with SGHDSDATA ahead of
--  S5HDSDATA on the library list produces a Test logical reading LIVE DATA,
--  with no error and no warning. Chris's program gets away with not setting the
--  list because it is launched from the menu of the environment you are already
--  in. A typed command has no such protection.
--
--  Therefore: set the library list explicitly before each block, and do this in
--  a fresh 5250 session so you are not fighting an existing list. Note your
--  starting list first with DSPLIBL if you want to put it back.
--
--  [CL] LIVE - SGHDSDATA over HDSSTDPGM
--       CHGLIBL LIBL(SGHDSDATA HDSSTDPGM QGPL QTEMP)
--       CRTLF FILE(SGHDSDATA/SGOEORDTL1) SRCFILE(SG/SGSRC)
--       CRTLF FILE(SGHDSDATA/SGORHDL001) SRCFILE(SGSRC/QDDSSRC)
--       CRTLF FILE(SGHDSDATA/SGORHDL002) SRCFILE(SGSRC/QDDSSRC)
--       CRTLF FILE(SGHDSDATA/SGORHDL003) SRCFILE(SGSRC/QDDSSRC)
--
--  [CL] TEST 1 - T1HDSDATA over HDSSTDPGM (same environment as Live)
--       CHGLIBL LIBL(T1HDSDATA HDSSTDPGM QGPL QTEMP)
--       CRTLF FILE(T1HDSDATA/SGOEORDTL1) SRCFILE(SG/SGSRC)
--       CRTLF FILE(T1HDSDATA/SGORHDL001) SRCFILE(SGSRC/QDDSSRC)
--       CRTLF FILE(T1HDSDATA/SGORHDL002) SRCFILE(SGSRC/QDDSSRC)
--       CRTLF FILE(T1HDSDATA/SGORHDL003) SRCFILE(SGSRC/QDDSSRC)
--
--  [CL] TEST 2 - S5HDSDATA over SG5STDPGM (its own environment).
--       Chris's utility does not cover this database.
--       CHGLIBL LIBL(S5HDSDATA SG5STDPGM QGPL QTEMP)
--       CRTLF FILE(S5HDSDATA/SGOEORDTL1) SRCFILE(SG/SGSRC)
--       CRTLF FILE(S5HDSDATA/SGORHDL001) SRCFILE(SGSRC/QDDSSRC)
--       CRTLF FILE(S5HDSDATA/SGORHDL002) SRCFILE(SGSRC/QDDSSRC)
--       CRTLF FILE(S5HDSDATA/SGORHDL003) SRCFILE(SGSRC/QDDSSRC)
--
--  Source locations are not interchangeable, and this is not a typo:
--       SGOEORDTL1  ->  SG/SGSRC          (its DDS exists ONLY there)
--       SGORHDL001  ->  SGSRC/QDDSSRC
--       SGORHDL002  ->  SGSRC/QDDSSRC
--       SGORHDL003  ->  SGSRC/QDDSSRC
--  SGSRC/QDDSSRC does not contain SGOEORDTL1. SG/SGSRC contains all six.
--  A copy of the four also sits in SGPGM/QRPGLESRC and SGSRC_CDH/QDDSSRC -
--  ignore those, they are not the source of record.
--
--  After each CRTLF, verify the logical bound to the right physical:
--       DSPDBR FILE(S5HDSDATA/OEORHD)
--  should list S5HDSDATA/SGORHDL001, 002 and 003 as dependents. If they appear
--  under SGHDSDATA/OEORHD instead, the library list was wrong - delete and redo.
--
--  ---------------------------------------------------------------------------
--  1.3  SGORHHL001 and SGORHHL002 - DO NOT CREATE without deciding
--  ---------------------------------------------------------------------------
--  Both have DDS source in SG/SGSRC and NO compiled object in any schema
--  (verified 2026-08-28: zero objects by that name anywhere on the system).
--
--       SGORHHL001   over OEORHH, keyed HHLDTI
--       SGORHHL002   JOIN logical over OEORDH and OEORHH, JDFTVAL,
--                    joined on DHORD#=HHORD# and DHSEQ#=HHSEQ#
--
--  OEORHH exists in all three databases, so they COULD be built. Chris's AFTER
--  branch would create them, and its CRTLF is monitored only for CPF5813, so
--  nothing stops a successful create - which is why running his utility rather
--  than this script would resurrect two logicals nobody has had for some time.
--
--  Left deliberately disabled. Enable only once it is known whether they were
--  dropped on purpose or lost in a past upgrade:
--
--       CHGLIBL LIBL(SGHDSDATA HDSSTDPGM QGPL QTEMP)
--       CRTLF FILE(SGHDSDATA/SGORHHL001) SRCFILE(SG/SGSRC)
--       CRTLF FILE(SGHDSDATA/SGORHHL002) SRCFILE(SG/SGSRC)
--       ...and the same for T1HDSDATA and S5HDSDATA with their own CHGLIBL
--
-- [SQL] 1.1 VERIFY the logicals came back, and are bound to the right database.
--       Run before AND after; the two results must match. Expected as at
--       2026-08-28: four rows, each showing 5 schemas.
SELECT TABLE_NAME,
       COUNT(*) AS IN_N_SCHEMAS,
       CAST(LISTAGG(TABLE_SCHEMA, ', ') AS VARCHAR(150)) AS SCHEMAS
  FROM QSYS2.SYSTABLES
 WHERE TABLE_SCHEMA IN ('SGHDSDATA','S5HDSDATA','T1HDSDATA',
                        'HDSSTDPGM','SG5STDPGM')
   AND TABLE_NAME LIKE 'SG%'
 GROUP BY TABLE_NAME
 ORDER BY TABLE_NAME

--  1.1b THE BINDING CHECK - catches a wrong library list
--
--  No SQL catalogue on this release reports the physical file behind a DDS
--  logical: SYSVIEWDEP and SYSTABLEDEP both return nothing for them, and
--  SYSTABLES has no BASE_TABLE columns populated. DSPDBR to an outfile is the
--  way, and it IS scriptable. Run the CL and the SELECT in the same job so
--  QTEMP persists.
--
--  Repeat per database, substituting SGHDSDATA / T1HDSDATA / S5HDSDATA:
--
--  [CL]  DSPDBR FILE(S5HDSDATA/OEORHD) OUTPUT(*OUTFILE) OUTFILE(QTEMP/DBRCHK)
-- [SQL]
SELECT TRIM(WHRLI) AS PF_LIB, TRIM(WHRFI) AS PHYSICAL,
       TRIM(WHRELI) AS LF_LIB, TRIM(WHREFI) AS DEPENDENT,
       CASE WHEN TRIM(WHRELI) = TRIM(WHRLI)      THEN 'ok'
            WHEN TRIM(WHRELI) IN ('SGOBJ','SG5OBJ') THEN 'ok - our own view'
            ELSE '*** WRONG ENVIRONMENT ***' END AS VERDICT
  FROM QTEMP.DBRCHK
 WHERE TRIM(WHREFI) LIKE 'SG%'
 ORDER BY 4
--  [CL]  DLTF FILE(QTEMP/DBRCHK)        then repeat for OEORDT and the next db
--
--  Verified correct 2026-08-28 - all twelve logicals bound to their own
--  database's physical:
--      SGHDSDATA.OEORHD <- SGORHDL001, 002, 003     (+ SGOBJ.SGBOOKDT, fine)
--      SGHDSDATA.OEORDT <- SGOEORDTL1               (+ SGOBJ.SGBOOKDT, fine)
--      T1HDSDATA.OEORHD <- SGORHDL001, 002, 003
--      T1HDSDATA.OEORDT <- SGOEORDTL1
--      S5HDSDATA.OEORHD <- SGORHDL001, 002, 003
--      S5HDSDATA.OEORDT <- SGOEORDTL1
--
--  A dependent in SGOBJ or SG5OBJ is NOT an error - see 1.4 below.

--  ---------------------------------------------------------------------------
--  1.4  SGOBJ.SGBOOKDT - a custom SQL VIEW over HarrisData files
--  ---------------------------------------------------------------------------
--  Found 2026-08-28 by the DSPDBR check above, and it was in nobody's upgrade
--  notes. It is a bookings view in SGOBJ - correctly placed per the standing
--  rule - but it READS four HarrisData tables:
--
--      SGHDSDATA.OEORHD, SGHDSDATA.OEORDT,
--      SGHDSDATA.HDSLSM, SGHDSDATA.HDCUST
--
--  So an upgrade that drops and recreates any of those four drops this view
--  with them. Unlike the logical files, Chris's utility knows nothing about it.
--
--  [SQL] Check it survived. Zero rows means it must be recreated:
SELECT TABLE_SCHEMA, TABLE_NAME FROM QSYS2.SYSVIEWS WHERE TABLE_NAME = 'SGBOOKDT'

--  [SQL] Recreate it if it is gone. This is its definition as at 2026-08-28,
--  retrieved from the catalogue - if the view is changed, update this block in
--  the same edit.
--
-- CREATE VIEW SGOBJ.SGBOOKDT AS
-- SELECT
--  H."OEORD#" AS ORDNUM,
--  H."OELOC#" AS LOC,
--  H.OEBLTO  AS BILLTO,
--  H.OESHTO  AS SHIPTO,
--  H.OESLSM  AS SLSNUM,
--  S.SMSNA1  AS SLSNAME,
--  C.CMCNA1  AS CUSTNAME,
--  C.CMCCTY  AS CUSTCITY,
--  C.CMST    AS CUSTST,
--  H.OEBDTE  AS ORDDATE,
--  H.OEORTY  AS ORDTYP,
--  H.OEORRF  AS ORDRFF,
--  H.OESVDS  AS SHIPVIA,
--  H.OERQDT  AS REQDAT,
--  DATE(D.ODOSTP) AS BKGDATE,
--  D.ODOSTP  AS BKGTMS,
--  D."ODORL#" AS LINNUM,
--  D.ODITEM  AS ITNUM,
--  D.ODIMDS  AS ITDESC,
--  D.ODQORD  AS QTYORD,
--  D.ODSLPR  AS SLPRIC,
--  D.ODWH    AS WH,
--  CASE WHEN D.ODSLPR = 0 THEN 0 ELSE D.ODQORD * D.ODSLPR END AS BKGAMT
-- FROM SGHDSDATA.OEORHD H
-- JOIN SGHDSDATA.OEORDT D ON D."ODORD#" = H."OEORD#"
-- JOIN SGHDSDATA.HDSLSM S ON S.SMSLSM   = H.OESLSM
-- JOIN SGHDSDATA.HDCUST C ON C.CMCUST   = H.OEBLTO
-- WHERE H.OEORTY NOT IN ('Q','U','V')
--
--  Note it is hardcoded to SGHDSDATA, so it always reads LIVE data regardless
--  of which environment queries it. That is presumably deliberate, but worth
--  knowing before anyone uses it from Test.

--  ---------------------------------------------------------------------------
--  OUR OWN OBJECTS - nothing to rebuild
--  ---------------------------------------------------------------------------
--  Everything else we own sits in SGOBJ or SG5OBJ over our own files, where a
--  HarrisData upgrade cannot reach it:
--
--      SGOBJ.BPCALLLOG      Buyer Pattern Contact Log
--      SG5OBJ.BPCALLLOG     Buyer Pattern Contact Log (Test)
--      SGOBJ.SGCSTHST       Item Cost History - change only
--      SGOBJ.SGCSTLOG       Item Cost History - capture run log
--      SGOBJ.SGCSTHSTL1     index over SGOBJ.SGCSTHST
--      SGOBJ.SGCSTHSTL2     index over SGOBJ.SGCSTHST
--      SGOBJ.SGACCLOG, SGPRAUDIT, SGPGMSBK, ALLSLSXLDB, OEOUDTF, SGBOOKDT
--      plus the dated SYPORR_/SYROLD_/SYPGMO_ backup tables
--
--  Nothing in that list needs rebuilding after a HarrisData upgrade.
--
--  ONE THING TO FIX, unrelated to upgrades: SGHDSDATA/J_HDINVC is a physical
--  file owned by BILL, created 2026-03-24, sitting inside the HarrisData data
--  schema. By the standing rule it belongs in SGOBJ. Find what uses it before
--  moving it. A HarrisData upgrade could delete it without warning.
--
-- -----------------------------------------------------------------------------
--  READY BUT NOT ENABLED - the two Buyer Pattern performance indexes.
--
--  From the IBM i Index Advisor, 2026-08-28, isolated to this page's own
--  queries. Estimated build time 1 second each. They target the two hot spots:
--  the EXISTS probe into OEORDT for ODOREC = 'S', and the order-type plus
--  order-date filter on OEORHD. Buyer Pattern spends 4.9 of its 7.5 seconds in
--  SQL, so these are the cheapest available win.
--
--  NOT CREATED. If they are ever created, uncomment here AND uncomment the
--  matching DROP in the pre-upgrade script, in the same edit.
--
--  Note on placement: an index must be created over the table it indexes, so
--  these cannot live in SGOBJ. That is exactly why this pair of scripts exists.
--  Worth testing whether DB2 for i will accept the index in a different library
--  from the table (CREATE INDEX SGOBJ.name ON SGHDSDATA.table) - if it will,
--  and the optimizer still uses it, that is the better home. Verify on Test.
-- -----------------------------------------------------------------------------

-- [SQL] LIVE
-- CREATE INDEX SGHDSDATA.SG_OEORDT_OREC ON SGHDSDATA.OEORDT (ODOREC, "ODORL#", "ODORD#")
-- LABEL ON INDEX SGHDSDATA.SG_OEORDT_OREC IS 'SG custom - Buyer Pattern EXISTS probe'
-- CREATE INDEX SGHDSDATA.SG_OEORHD_TYDT ON SGHDSDATA.OEORHD (OEORTY, "OEORD#", OEBDTE)
-- LABEL ON INDEX SGHDSDATA.SG_OEORHD_TYDT IS 'SG custom - Buyer Pattern type+date filter'

-- [SQL] TEST
-- CREATE INDEX S5HDSDATA.SG_OEORDT_OREC ON S5HDSDATA.OEORDT (ODOREC, "ODORL#", "ODORD#")
-- LABEL ON INDEX S5HDSDATA.SG_OEORDT_OREC IS 'SG custom - Buyer Pattern EXISTS probe'
-- CREATE INDEX S5HDSDATA.SG_OEORHD_TYDT ON S5HDSDATA.OEORHD (OEORTY, "OEORD#", OEBDTE)
-- LABEL ON INDEX S5HDSDATA.SG_OEORHD_TYDT IS 'SG custom - Buyer Pattern type+date filter'


-- #############################################################################
--  PART 2 - THE HarrisData POST-UPGRADE PROCEDURE
-- #############################################################################
--
--  Supplied by Bill 2026-08-28 and reproduced here so the whole job lives in
--  one place. Reformatted for readability; the commands themselves are
--  unchanged except for one corrected object name, flagged below.
--
--  ===========================================================================
--  07/08/2025 - Add library parts to SGSRC/QCLLESRC/BUILDVIEWS
--  ===========================================================================
--
--  The User Defined Functions (UDFs) need to be manually created for a New
--  Install, an Upgrade, and the apply of a Cum, a Fix Pack or a Fix.
--
--  ---------------------------------------------------------------------------
--  2.1  Create the HD5.0 UDFs in your HD5.0 Program schema
--  ---------------------------------------------------------------------------
--  Replace <HDSSTDPGM> with your HD5.0 Program Schema name.
--  Replace <HDSSTDSRC> with your HD5.0 Source Schema name.
--  For Screen Graphics:  LIVE = HDSSTDPGM / HDSSTDSRC
--                        TEST = SG5STDPGM / SG5STDSRC
--
--  [CL]  EDTLIBL
--          The library list should look like:
--              <HDSSTDPGM>
--
--  [CL]  CRTSQLFNC        then press F4 and fill in:
--
--              Create SQL Function (CRTSQLFNC)
--            Program Schema . . . . . . . . .   HDSSTDPGM     Name
--            Function . . . . . . . . . . . .   *ALL          Name, generic*, *ALL
--            Target Release . . . . . . . . .   V7R2M0        VxRxMx
--            Source Table . . . . . . . . . .   QSQLSRC       Name
--            Schema . . . . . . . . . . . . .   HDSSTDSRC     Name
--
--          Press Enter.
--
--  When the job completes these UDFs will exist on the system:
--            F_CVTCUR      Convert Currency
--            F_CVTCURDT    Convert Currency Date
--            F_CVTCUROP    Convert Currency Operand
--            F_CVTCURRT    Convert Currency Rate
--            F_DECHOURS    Function For SETCDT.DECHOURS
--            F_DECHRS5     Function for SETCDT.DECHOURS5
--            F_MAKEDATE    Make Date from CYYMMDD
--            F_QTYAVAIL    Quantity Available
--            F_QTYAVPCK    Quantity Available To Pick
--            F_TIMESTMP    Make Timestamp from CYYMMDD
--            F_TRACKURL    Tracking URL
--
--  ---------------------------------------------------------------------------
--  2.2  The SQL Views the OS fails to catalogue - FOR ALL IBM OS LEVELS
--  ---------------------------------------------------------------------------
--  For HD5.0, HarrisData changed DDS defined Physical (PF) and Logical (LF)
--  files to DDL. These new DDL SQL Tables, Indexes and Views are catalogued in
--  the IBM OS. Something changed in the OS recently and some Views are not
--  catalogued correctly, which means a HD5.0 Install/Upgrade cannot create them
--  on the customer system. Until IBM resolves it, up to 13 SQL Views may need
--  to be created manually, depending on application mix:
--
--       1. APOPENV01   Open Voucher View (Vendor)
--       2. APOPENV02   Open Voucher View (Open/Paid)
--       3. APPAIDV01   Paid Voucher View (Vendor)
--       4. ETBLWK03    Time Review Work
--       5. ETBLWK04    Time Review Work
--       6. HDIMST05    Item To Item/Warehouse
--       7. HDIMST09    Item To Item/Warehouse To Synonym
--       8. HDINVCV01   Invoice View (Customer, Instance)
--       9. HDINVCV02   Invoice View (Open/Paid)  (Widget)
--      10. OEORHD17    Order Header To Detail To Item/Warehouse
--      11. HDVENDV01   Vendor View 01
--      12. HDIMST16    Stock Loc Variance
--      13. HDIMST17    Comprehensive Item List
--
--  HD5.0 Source must be installed on the system to continue. If it is not,
--  contact a HarrisData Support Representative. If you are running
--  Common/Individual, extra steps are needed - also contact Support.
--
--  *** TWO CORRECTIONS TO THE SUPPLIED PROCEDURE, verified on the box
--      2026-08-28: ***
--
--    (a) The CHGOBJ list as supplied says OEORDH17. That object does not exist
--        in any schema. The correct name is OEORHD17, which is what the SQLTBL
--        list uses. Running CHGOBJ against OEORDH17 fails with "not found".
--        The commands below use OEORHD17 throughout.
--
--    (b) HDCUSTV01 appears in the SQLTBL and CHGOBJ lists but not in the
--        numbered list of 13, and HDINVCV02 appears in the numbered list but
--        not in the command lists. Both objects exist on the system, so both
--        are included below. Confirm with HarrisData Support which belong in
--        your application mix.
--
--  Current state, checked 2026-08-28 - all present except APOPENV02 in
--  S5HDSDATA, which is missing:
--
--        VIEW        HDSSTDPGM  SGHDSDATA  SG5STDPGM  S5HDSDATA
--        APOPENV01       Y          Y          Y          Y
--        APOPENV02       Y          Y          Y          -  <-- missing
--        APPAIDV01       Y          Y          Y          Y
--        ETBLWK03        Y          Y          Y          Y
--        ETBLWK04        Y          Y          Y          Y
--        HDCUSTV01       Y          Y          Y          Y
--        HDIMST05        Y          Y          Y          Y
--        HDIMST09        Y          Y          Y          Y
--        HDIMST16        Y          Y          Y          Y
--        HDIMST17        Y          Y          Y          Y
--        HDINVCV01       Y          Y          Y          Y
--        HDINVCV02       Y          Y          Y          Y
--        HDVENDV01       Y          Y          Y          Y
--        OEORHD17        Y          Y          Y          Y
--
--  Run step 2.7 below FIRST after an upgrade - it tells you which of these are
--  actually missing, so you only create what is needed.
--
--  ---------------------------------------------------------------------------
--  2.3  LIVE - create the missing views in the HD5.0 Program schema
--  ---------------------------------------------------------------------------
--  [CL]  EDTLIBL       library list should look like:
--              HDSSTDPGM
--              QGPL
--              QTEMP
--
--  [CL]  Create the views that apply to your application mix:
--
--        SQLTBL TBL(HDSSTDPGM/APOPENV01) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/APOPENV02) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/APPAIDV01) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/ETBLWK03)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/ETBLWK04)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/HDIMST05)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/HDIMST09)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/HDIMST16)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/HDIMST17)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/HDINVCV01) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/HDINVCV02) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/OEORHD17)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/HDVENDV01) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(HDSSTDPGM/HDCUSTV01) SRC(HDSSTDSRC/QSQLSRC)
--
--  [CL]  Run CHGOBJ once for each view you created:
--
--        CHGOBJ OBJ(HDSSTDPGM/APOPENV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/APOPENV02) OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/APPAIDV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/ETBLWK03)  OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/ETBLWK04)  OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/HDIMST05)  OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/HDIMST09)  OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/HDIMST16)  OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/HDIMST17)  OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/HDINVCV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/HDINVCV02) OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/OEORHD17)  OBJTYP(*ALL)     <-- was OEORDH17
--        CHGOBJ OBJ(HDSSTDPGM/HDVENDV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(HDSSTDPGM/HDCUSTV01) OBJTYP(*ALL)
--
--  ---------------------------------------------------------------------------
--  2.4  LIVE - create the missing views in EACH database schema
--  ---------------------------------------------------------------------------
--  Repeat for every HD5.0 database schema. For Screen Graphics that is
--  SGHDSDATA on Live.
--
--  [CL]  EDTLIBL       library list should look like:
--              SGHDSDATA
--              HDSSTDPGM
--              QGPL
--              QTEMP
--
--  [CL]  SQLTBL TBL(SGHDSDATA/APOPENV01) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/APOPENV02) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/APPAIDV01) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/ETBLWK03)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/ETBLWK04)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/HDIMST05)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/HDIMST09)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/HDIMST16)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/HDIMST17)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/HDINVCV01) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/HDINVCV02) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/OEORHD17)  SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/HDVENDV01) SRC(HDSSTDSRC/QSQLSRC)
--        SQLTBL TBL(SGHDSDATA/HDCUSTV01) SRC(HDSSTDSRC/QSQLSRC)
--
--  [CL]  CHGOBJ OBJ(SGHDSDATA/APOPENV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/APOPENV02) OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/APPAIDV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/ETBLWK03)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/ETBLWK04)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/HDIMST05)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/HDIMST09)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/HDIMST16)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/HDIMST17)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/HDINVCV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/HDINVCV02) OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/OEORHD17)  OBJTYP(*ALL)     <-- was OEORDH17
--        CHGOBJ OBJ(SGHDSDATA/HDVENDV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(SGHDSDATA/HDCUSTV01) OBJTYP(*ALL)
--
--  ---------------------------------------------------------------------------
--  2.5  TEST (S5) - program schema SG5STDPGM
--  ---------------------------------------------------------------------------
--  [CL]  SQLTBL TBL(SG5STDPGM/APOPENV01) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/APOPENV02) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/APPAIDV01) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/ETBLWK03)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/ETBLWK04)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/HDIMST05)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/HDIMST09)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/HDIMST16)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/HDIMST17)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/HDINVCV01) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/HDINVCV02) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/OEORHD17)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/HDVENDV01) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(SG5STDPGM/HDCUSTV01) SRC(SG5STDSRC/QSQLSRC)
--
--  [CL]  CHGOBJ OBJ(SG5STDPGM/APOPENV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/APOPENV02) OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/APPAIDV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/ETBLWK03)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/ETBLWK04)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/HDIMST05)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/HDIMST09)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/HDIMST16)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/HDIMST17)  OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/HDINVCV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/HDINVCV02) OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/OEORHD17)  OBJTYP(*ALL)     <-- was OEORDH17
--        CHGOBJ OBJ(SG5STDPGM/HDVENDV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(SG5STDPGM/HDCUSTV01) OBJTYP(*ALL)
--
--  ---------------------------------------------------------------------------
--  2.6  TEST (S5) - database schema S5HDSDATA
--  ---------------------------------------------------------------------------
--  NOTE: APOPENV02 is currently MISSING from S5HDSDATA (checked 2026-08-28).
--
--  [CL]  SQLTBL TBL(S5HDSDATA/APOPENV01) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/APOPENV02) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/APPAIDV01) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/ETBLWK03)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/ETBLWK04)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/HDIMST05)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/HDIMST09)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/HDIMST16)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/HDIMST17)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/HDINVCV01) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/HDINVCV02) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/OEORHD17)  SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/HDVENDV01) SRC(SG5STDSRC/QSQLSRC)
--        SQLTBL TBL(S5HDSDATA/HDCUSTV01) SRC(SG5STDSRC/QSQLSRC)
--
--  [CL]  CHGOBJ OBJ(S5HDSDATA/APOPENV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/APOPENV02) OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/APPAIDV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/ETBLWK03)  OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/ETBLWK04)  OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/HDIMST05)  OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/HDIMST09)  OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/HDIMST16)  OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/HDIMST17)  OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/HDINVCV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/HDINVCV02) OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/OEORHD17)  OBJTYP(*ALL)     <-- was OEORDH17
--        CHGOBJ OBJ(S5HDSDATA/HDVENDV01) OBJTYP(*ALL)
--        CHGOBJ OBJ(S5HDSDATA/HDCUSTV01) OBJTYP(*ALL)
--
--  ---------------------------------------------------------------------------
--  2.7  Which views are actually missing - RUN THIS FIRST
--  ---------------------------------------------------------------------------
--  Save yourself running 56 commands blind. This reports which of the views
--  are absent from each schema, so you only create what is missing. A blank
--  cell is a view that needs creating in that schema.

-- [SQL]
SELECT V.NAME AS VIEW_NAME,
       MAX(CASE WHEN T.TABLE_SCHEMA = 'HDSSTDPGM' THEN 'Y' ELSE '' END) AS HDSSTDPGM,
       MAX(CASE WHEN T.TABLE_SCHEMA = 'SGHDSDATA' THEN 'Y' ELSE '' END) AS SGHDSDATA,
       MAX(CASE WHEN T.TABLE_SCHEMA = 'SG5STDPGM' THEN 'Y' ELSE '' END) AS SG5STDPGM,
       MAX(CASE WHEN T.TABLE_SCHEMA = 'S5HDSDATA' THEN 'Y' ELSE '' END) AS S5HDSDATA
  FROM (VALUES ('APOPENV01'),('APOPENV02'),('APPAIDV01'),('ETBLWK03'),('ETBLWK04'),
               ('HDIMST05'),('HDIMST09'),('HDIMST16'),('HDIMST17'),('HDINVCV01'),
               ('HDINVCV02'),('OEORHD17'),('HDVENDV01'),('HDCUSTV01')) AS V(NAME)
  LEFT JOIN QSYS2.SYSTABLES T
         ON T.TABLE_NAME = V.NAME
        AND T.TABLE_SCHEMA IN ('HDSSTDPGM','SGHDSDATA','SG5STDPGM','S5HDSDATA')
 GROUP BY V.NAME
 ORDER BY V.NAME

--  ---------------------------------------------------------------------------
--  2.8  Confirm the UDFs came back
--  ---------------------------------------------------------------------------

-- [SQL]
SELECT SPECIFIC_SCHEMA, ROUTINE_NAME, COALESCE(ROUTINE_TEXT,'') AS ROUTINE_TEXT
  FROM QSYS2.SYSROUTINES
 WHERE SPECIFIC_SCHEMA IN ('HDSSTDPGM','SG5STDPGM')
   AND ROUTINE_NAME IN ('F_CVTCUR','F_CVTCURDT','F_CVTCUROP','F_CVTCURRT',
                        'F_DECHOURS','F_DECHRS5','F_MAKEDATE','F_QTYAVAIL',
                        'F_QTYAVPCK','F_TIMESTMP','F_TRACKURL')
 ORDER BY SPECIFIC_SCHEMA, ROUTINE_NAME
--  Expect 11 rows per program schema. Anything short means CRTSQLFNC did not
--  complete - check the job log before going any further.
--
--  *** OPEN ITEM, found 2026-08-28 before any upgrade was run ***
--
--  There are only 10 per schema, not 11. F_TIMESTMP ("Make Timestamp from
--  CYYMMDD") does not exist anywhere on this system - not in HDSSTDPGM, not in
--  SG5STDPGM, not under any other schema. The other ten are present in both.
--
--  There is also no F_TIMESTMP source member in HDSSTDSRC/QSQLSRC or
--  SG5STDSRC/QSQLSRC (the only F_ prefixed members there are F_QTYAVAIL and
--  F_QTYAVPCK), so CRTSQLFNC FUNCTION(*ALL) has nothing to build it from.
--
--  This is a question for HarrisData Support, not something to invent locally:
--  either F_TIMESTMP is not shipped for the Screen Graphics application mix, or
--  the source is missing from the HD5.0 Source install. Do not hand-write a
--  replacement - if a Harris program expects it, it must be Harris's version.
--
--  Use this to see exactly which are missing:
SELECT S.LIB, F.NAME AS MISSING_UDF
  FROM (VALUES ('HDSSTDPGM'),('SG5STDPGM')) AS S(LIB)
 CROSS JOIN (VALUES ('F_CVTCUR'),('F_CVTCURDT'),('F_CVTCUROP'),('F_CVTCURRT'),
                    ('F_DECHOURS'),('F_DECHRS5'),('F_MAKEDATE'),('F_QTYAVAIL'),
                    ('F_QTYAVPCK'),('F_TIMESTMP'),('F_TRACKURL')) AS F(NAME)
 WHERE NOT EXISTS (SELECT 1 FROM QSYS2.SYSROUTINES R
                    WHERE R.SPECIFIC_SCHEMA = S.LIB
                      AND R.ROUTINE_NAME = F.NAME)
 ORDER BY 1, 2


-- #############################################################################
--  PART 3 - AFTER THE UPDATES, IN HarrisData
-- #############################################################################
--
--  [MENU]  99, 22, 1
--  [MENU]  99, 22, 2
--  [MENU]  99, 22, 6      then change PUBLIC to *USE


-- #############################################################################
--  PART 4 - CHECK OUR OWN THINGS STILL WORK
-- #############################################################################

-- 4.1 [SQL] The Buyer Pattern contact log survived, with its rows and its
--           append-only grant. Expect the row count you noted before starting.
SELECT COUNT(*) AS ROWS_IN_LOG FROM SGOBJ.BPCALLLOG
SELECT COUNT(*) AS ROWS_IN_LOG FROM SG5OBJ.BPCALLLOG

-- 4.2 [SQL] The log must still be SELECT and INSERT only for PUBLIC. If an
--           upgrade or a restore has granted UPDATE or DELETE, the audit trail
--           is no longer trustworthy and the grant must be reset.
--     QSYS2.TABLE_PRIVILEGES is the right view on this release. SYSTABLEPERM
--     does not exist here (SQL0204), whatever other DB2 platforms call it.
SELECT TABLE_SCHEMA, TABLE_NAME, GRANTEE, PRIVILEGE_TYPE
  FROM QSYS2.TABLE_PRIVILEGES
 WHERE TABLE_NAME = 'BPCALLLOG'
 ORDER BY TABLE_SCHEMA, GRANTEE, PRIVILEGE_TYPE
--     Correct result, verified 2026-08-28: PUBLIC has exactly SELECT and INSERT
--     on both SGOBJ.BPCALLLOG and SG5OBJ.BPCALLLOG, and nothing else. BILL as
--     owner holds the full set, which is expected and is not a problem - the
--     guarantee is that no ordinary user can UPDATE or DELETE a logged contact.
--  If UPDATE or DELETE appears for PUBLIC, run:
--      REVOKE ALL ON SGOBJ.BPCALLLOG FROM PUBLIC
--      GRANT SELECT, INSERT ON SGOBJ.BPCALLLOG TO PUBLIC

-- 4.3 [SQL] Program security registration survived
SELECT 'HDSSTDPGM' AS LIB, COUNT(*) AS BUYPATTERN_ROWS
  FROM HDSSTDPGM.SYPGMO WHERE RTRIM(SOPGID) = 'BUYPATTERN'
--  and the per-user grants
SELECT COUNT(*) AS SYPGMS_GRANTS
  FROM SGHDSDATA.SYPGMS WHERE RTRIM(SPPGID) = 'BUYPATTERN'
--  A count of 0 here means the page silently reverts to open-to-everyone,
--  because the gate is fail-open until the first grant exists.

-- 4.4 [SQL] The UDC access rows survived. PROITRG is a single library shared by
--           both environments and is not part of the HarrisData product, so it
--           should be untouched - verify anyway.
SELECT UPPER(TRIM(UDCSYSTEMD)) AS SYS, UPPER(TRIM(UDCCODED)) AS CD,
       UPPER(TRIM(UDCKEY)) AS KEYVAL
  FROM PROITRG.UDCDETAIL
 WHERE UPPER(TRIM(UDCSYSTEMD)) = 'BUYPATTERN'
 ORDER BY CD, KEYVAL

-- 4.5 Open the page in both environments and confirm it renders:
--       TEST  https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/BuyerPattern.php
--       LIVE  https://portal.screen-graphics.com:5601/Custom/SG/Order%20Entry/BuyerPattern.php
--     Then pull a workbook from each:  ?export=book
--
--     If a page returns a bare 500, the error is NOT in the Apache log. Look in
--     /QOpenSys/var/log/php_error.log, and remember the portal runs PHP 5.6.23
--     under ZendServer - lint with /usr/local/zendsvr6/bin/php, never the 7.4
--     binary on the PATH.

-- =============================================================================
--  END. Keep this file and SG_PreUpgrade_DropCustomAccessPaths.sql together and
--  edit them as a pair.
-- =============================================================================
