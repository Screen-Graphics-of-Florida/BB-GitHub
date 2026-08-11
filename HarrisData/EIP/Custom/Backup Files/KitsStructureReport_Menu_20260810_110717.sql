-- ================================================================
-- Add Kits Structure Report to SG Inquiries / Manufacturing
-- ================================================================
-- This is the menu insert that belongs in the IBM i menu tables used by
-- the Harris app (SYURLM / SYPORT / SYPORR).  The exact menu IDs and
-- sequence values must match your live environment, so run this as a
-- template and replace the placeholders with the real next available IDs.
-- ================================================================

-- 1) Find the next available URL/menu IDs in your environment:
-- SELECT MAX(FUID) + 1 FROM SGHDSDATA.SYURLM;
-- SELECT MAX(FPID) + 1 FROM SGHDSDATA.SYPORT;
-- SELECT MAX(FPSEQ) + 1 FROM SGHDSDATA.SYPORT WHERE FPPORT = 'SGINQ';

-- 2) Insert the URL item (report page)
INSERT INTO SGHDSDATA.SYURLM (
    FUID,
    FUURL,
    FUDESC,
    FUTITL,
    FUIMG,
    FUTRGT
)
VALUES (
    99999,
    '@@homeURL@@phpPathKitsStructureReport.php?baseVar=@@baseVar&amp;eID=@@eID&amp;portal=@@portal',
    'Kits Structure',
    'Kits Structure Report',
    '',
    ''
);

-- 3) Insert the menu item under SG Inquiries / Manufacturing
-- Replace 99999 with the same FUID used above.
INSERT INTO SGHDSDATA.SYPORT (
    FPPORT,
    FPPAGE,
    FPSEQ,
    FPID,
    FPDESC,
    FPTITL,
    FPRGCD,
    FPRGFL
)
VALUES (
    'SGINQ',
    'MFG',
    999,
    99999,
    'Kits Structure',
    'Kits Structure Report',
    '',
    ''
);

-- 4) Grant access to the menu item for the desired role(s)
-- Replace the role code with the actual role used for SG Inquiries.
INSERT INTO SGHDSDATA.SYPORR (
    PRROLE,
    PRPORT,
    PRPAGE,
    PRSEQ,
    PRSEL
)
VALUES (
    'SGMFG',
    'SGINQ',
    'MFG',
    999,
    'Y'
);

-- ================================================================
-- Notes:
--  - If your menu uses a different portal code than SGINQ or a different
--    manufacturing page code than MFG, replace those values.
--  - If the menu item is already present, update the existing row instead
--    of inserting again.
--  - If you have a menu-management utility in your app, use it instead of
--    direct SQL inserts.
-- ================================================================
