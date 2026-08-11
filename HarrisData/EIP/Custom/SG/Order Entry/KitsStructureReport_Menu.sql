-- ============================================================================
-- KitsStructureReport_Menu.sql
-- Add "Kits Structure Report" under SG Inquiries / Manufacturing
-- Target page: Custom/SG/Order Entry/KitsStructureReport.php
-- ============================================================================
--
-- READ THIS BEFORE RUNNING ANYTHING.
--
-- 1. HOW REPORTS NORMALLY GET ONTO THIS MENU
--    The SG portals do NOT carry one SYURLM/SYPORT row per report. The menu
--    tables only define the portal (SGINQ) and its six category tiles
--    (ACCT, INVMGMT, MFG, OE, PLN, PUR). The individual reports listed under a
--    tile come from the $reportMap array in Custom/SG/sg_portal_landing.php.
--
--    So the normal, zero-SQL way to publish this report is Step A below. The
--    SYURLM/SYPORT/SYPORR inserts in Step B are only needed if you also want a
--    direct entry in the EIP left-hand nav, bypassing the category tile.
--
-- 2. SCHEMA
--    Live EIP (port 5601) menu rows live in SGHDSDATA. The SG5 test instance
--    (port 5610) has previously been seen reading S5HDSDATA for some menu
--    files -- Custom/SG/DiagS5Schema.php was written specifically to determine
--    which schema GetMenu.php actually resolves to. Run that first and use the
--    schema it reports. Every statement below is written against SGHDSDATA;
--    change the qualifier if the diagnostic says otherwise.
--
-- 3. IDs AND SEQUENCES
--    FUID / FPID are CHARACTER keys, not numbers -- the convention in
--    SgApplyAll.php is '<PORTAL>/PORTAL' for a portal and '<PORTAL>_<CAT>' for
--    a category tile. FPSEQ / PRSEQ are ordering numbers within a portal page.
--    The values below are proposals; confirm them against the live rows with
--    the verification queries in Step 0 before inserting.
--
-- 4. ROLES
--    PRROLE values must be real roles from SYROLM. HD_ALL_SG is the bypass role
--    and deliberately gets no SYPORR rows. Do not invent a role code here.
--
-- All inserts are guarded with WHERE NOT EXISTS so re-running is safe and an
-- existing row is never duplicated or overwritten.
-- ============================================================================


-- ============================================================================
-- STEP 0 -- READ FIRST (no writes). Quote this output before inserting.
-- ============================================================================

-- 0a. Does the SGINQ portal + MFG category tile already exist?
SELECT RTRIM(FPPORT) AS PORT, RTRIM(FPPAGE) AS PAGE, FPSEQ,
       RTRIM(FPID)   AS FPID, RTRIM(FPDESC) AS DESCR, RTRIM(FPTITL) AS TITLE
  FROM SGHDSDATA.SYPORT
 WHERE RTRIM(FPPORT) = 'SGINQ'
 ORDER BY FPPAGE, FPSEQ;

-- 0b. Matching URL definitions.
SELECT RTRIM(FUID) AS FUID, RTRIM(FUDESC) AS DESCR, RTRIM(FUTITL) AS TITLE,
       RTRIM(FUURL) AS URL
  FROM SGHDSDATA.SYURLM
 WHERE RTRIM(FUID) LIKE 'SGINQ%'
 ORDER BY FUID;

-- 0c. Is this report already on the menu? (must return 0 rows)
SELECT * FROM SGHDSDATA.SYURLM WHERE RTRIM(FUID) = 'SGINQ_MFGKITS';
SELECT * FROM SGHDSDATA.SYPORT WHERE RTRIM(FPID) = 'SGINQ_MFGKITS';

-- 0d. Next free sequence number on the SGINQ second level.
SELECT COALESCE(MAX(FPSEQ), 0) + 1 AS NEXT_FPSEQ
  FROM SGHDSDATA.SYPORT
 WHERE RTRIM(FPPORT) = 'SGINQ' AND RTRIM(FPPAGE) = 'SGINQ';

-- 0e. Which roles can currently see SGINQ (candidates for the SYPORR grant).
SELECT DISTINCT RTRIM(PRROLE) AS ROLE
  FROM SGHDSDATA.SYPORR
 WHERE RTRIM(PRPORT) = 'SGINQ' AND RTRIM(PRPAGE) = '' AND RTRIM(PRSEL) = 'Y'
 ORDER BY 1;

-- 0f. Back up the rows you are about to touch.
--     (SgBackup.php / SgApplyAll.php write CSV backups of these same files.)
SELECT * FROM SGHDSDATA.SYURLM WHERE RTRIM(FUID)   LIKE 'SG%';
SELECT * FROM SGHDSDATA.SYPORT WHERE RTRIM(FPPORT) = 'SGINQ';
SELECT * FROM SGHDSDATA.SYPORR WHERE RTRIM(PRPORT) = 'SGINQ';


-- ============================================================================
-- STEP A -- PREFERRED: no SQL at all
-- ============================================================================
-- Edit Custom/SG/sg_portal_landing.php and add this element to
-- $reportMap['SGINQ']['MFG'] (note the %20 for the folder space, matching the
-- other Order Entry entries):
--
--     array(
--         'title' => 'Kits Structure Report',
--         'desc'  => 'Complete kit product structures with component qty on hand, '
--                  . 'sold YTD, issued YTD, mfg YTD and qty committed to MOs. '
--                  . 'Item pattern + product class filters; export to Excel',
--         'file'  => 'Order%20Entry/KitsStructureReport.php',
--     ),
--
-- Nothing else is required -- SGINQ/MFG already exists in SYURLM/SYPORT, so the
-- report shows up on the Manufacturing tile for every role that can see SGINQ.
-- Remember the SG5 and EIP trees are separate physical copies: apply the edit to
-- W:\HarrisData\SG5\Custom\SG\sg_portal_landing.php, then copy it over
-- W:\HarrisData\EIP\Custom\SG\sg_portal_landing.php.


-- ============================================================================
-- STEP B -- OPTIONAL: direct left-nav entry via the menu tables
-- ============================================================================
-- Only run this if the report should also appear as its own item in the EIP
-- navigation, alongside the category tiles. Column lists follow the shapes used
-- by SgApplyAll.php and InsertMgmtPortal_SG5.sql.

-- B1. URL definition.
--     FUTSUS is the "changed by" stamp; 'SGAPPLY' is what the app's own
--     maintenance scripts write. FURESV/FUTSWS/FUTSPT stay blank for a
--     non-reserved custom entry.
INSERT INTO SGHDSDATA.SYURLM
    (FUID, FUDESC, FUTITL, FUTRGT, FUURL, FUIMG,
     FURESV, FUDESCU, FUTSTP, FUTSUS, FUTSWS, FUTSPT)
SELECT 'SGINQ_MFGKITS',
       'Kits Structure',
       'Kits Structure Report',
       '',
       '@@homeURL@@phpPathCustom/SG/Order%20Entry/KitsStructureReport.php',
       '',
       '', 'KITS STRUCTURE', CURRENT_TIMESTAMP, 'SGAPPLY', '', ''
  FROM SYSIBM.SYSDUMMY1
 WHERE NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYURLM WHERE RTRIM(FUID) = 'SGINQ_MFGKITS');

-- B2. Menu placement: second level of the SGINQ portal.
--     FPPAGE = FPPORT is what makes it a level-2 item -- SgReportNav.php and
--     GetMenu.php both select on (FPPAGE = '' OR FPPAGE = FPPORT).
--     Replace 99 with the NEXT_FPSEQ value returned by query 0d.
INSERT INTO SGHDSDATA.SYPORT
    (FPPORT, FPPAGE, FPSEQ, FPID, FPDESC, FPTITL,
     FPRESV, FPDESCU, FPTSTP, FPTSUS, FPTSWS, FPTSPT)
SELECT 'SGINQ', 'SGINQ', 99, 'SGINQ_MFGKITS',
       'Kits Structure',
       'SG Inquiries - Kits Structure Report',
       '', '', CURRENT_TIMESTAMP, 'SGAPPLY', '', ''
  FROM SYSIBM.SYSDUMMY1
 WHERE NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYPORT
                    WHERE RTRIM(FPPORT) = 'SGINQ' AND RTRIM(FPID) = 'SGINQ_MFGKITS');

-- B3. Role visibility.
--     PRSEQ must equal the FPSEQ used in B2, and PRID must be the FPID -- Portal
--     By Role Maintenance resolves its Description column through PRID, so a
--     mismatched PRID leaves that column blank (see the STEP 6/7 comments in
--     SgApplyAll.php).
--
--     This grants every role that already has SGINQ, except the bypass role,
--     rather than hard-coding a role code. Confirm the list with query 0e first.
INSERT INTO SGHDSDATA.SYPORR
    (PRROLE, PRPORT, PRPAGE, PRSEQ, PRID, PRSEL, PRTSTP, PRTSUS, PRTSPT)
SELECT RTRIM(t.PRROLE), 'SGINQ', 'SGINQ', 99, 'SGINQ_MFGKITS', 'Y',
       CURRENT_TIMESTAMP, 'SGAPPLY', ''
  FROM (SELECT DISTINCT PRROLE
          FROM SGHDSDATA.SYPORR
         WHERE RTRIM(PRPORT) = 'SGINQ'
           AND RTRIM(PRPAGE) = ''
           AND RTRIM(PRSEL)  = 'Y'
           AND RTRIM(PRROLE) <> 'HD_ALL_SG') t
 WHERE NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYPORR
                    WHERE PRROLE = t.PRROLE
                      AND RTRIM(PRPORT) = 'SGINQ'
                      AND RTRIM(PRPAGE) = 'SGINQ'
                      AND PRSEQ = 99);


-- ============================================================================
-- STEP C -- VERIFY
-- ============================================================================
SELECT RTRIM(p.FPPORT) AS PORT, RTRIM(p.FPPAGE) AS PAGE, p.FPSEQ,
       RTRIM(p.FPID)   AS FPID, RTRIM(u.FUDESC) AS DESCR, RTRIM(u.FUURL) AS URL
  FROM SGHDSDATA.SYPORT p
  JOIN SGHDSDATA.SYURLM u ON RTRIM(u.FUID) = RTRIM(p.FPID)
 WHERE RTRIM(p.FPPORT) = 'SGINQ'
 ORDER BY p.FPPAGE, p.FPSEQ;

SELECT RTRIM(PRROLE) AS ROLE, PRSEQ, RTRIM(PRID) AS PRID, RTRIM(PRSEL) AS SEL
  FROM SGHDSDATA.SYPORR
 WHERE RTRIM(PRPORT) = 'SGINQ' AND RTRIM(PRID) = 'SGINQ_MFGKITS'
 ORDER BY PRROLE;


-- ============================================================================
-- ROLLBACK (Step B only)
-- ============================================================================
-- DELETE FROM SGHDSDATA.SYPORR WHERE RTRIM(PRID)   = 'SGINQ_MFGKITS';
-- DELETE FROM SGHDSDATA.SYPORT WHERE RTRIM(FPID)   = 'SGINQ_MFGKITS';
-- DELETE FROM SGHDSDATA.SYURLM WHERE RTRIM(FUID)   = 'SGINQ_MFGKITS';
