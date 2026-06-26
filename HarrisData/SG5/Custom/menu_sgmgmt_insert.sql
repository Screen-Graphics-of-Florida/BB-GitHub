-- ============================================================
-- SGMGMT portal registration — SYPORT + SYURLM
-- Run on: SG5 test database
-- Date:   2026-06-26
-- Pages:  RevenueVsGoal.php, NewAccountsRevenue.php,
--         BottomHalfRevenue.php
--         (all served via sg_portal_landing.php?portal=SGMGMT)
-- ============================================================

-- SYURLM: portal header
INSERT INTO SYURLM (FUID, FUDESC, FUTITL, FUTRGT, FUURL, FUIMG, FURESV, FUDESCU, FUTSTP, FUTSUS, FUTSWS, FUTSPT)
VALUES ('SGMGMT/PORTAL', 'SG Management', 'SG Management', '', '', '', '', '', CURRENT_TIMESTAMP, '', '', 'Y')

-- SYURLM: Order Entry subcategory nav link
INSERT INTO SYURLM (FUID, FUDESC, FUTITL, FUTRGT, FUURL, FUIMG, FURESV, FUDESCU, FUTSTP, FUTSUS, FUTSWS, FUTSPT)
VALUES ('SGMGMT_OE', 'Order Entry', 'SG Management - Order Entry', '_blank', '@@homeURL@@phpPathCustom/SG/sg_portal_landing.php?portal=SGMGMT&cat=OE', '', 'Y', '', CURRENT_TIMESTAMP, '', '', '')

-- SYPORT: portal header
INSERT INTO SYPORT (FPPORT, FPPAGE, FPSEQ, FPID, FPDESC, FPTITL, FPRESV, FPDESCU, FPTSTP, FPTSUS, FPTSWS, FPTSPT)
VALUES ('SGMGMT', '', '1.00', 'SGMGMT/PORTAL', '', 'SG Management', 'Y', '', CURRENT_TIMESTAMP, '', '', 'Y')

-- SYPORT: Order Entry subcategory
INSERT INTO SYPORT (FPPORT, FPPAGE, FPSEQ, FPID, FPDESC, FPTITL, FPRESV, FPDESCU, FPTSTP, FPTSUS, FPTSWS, FPTSPT)
VALUES ('SGMGMT', 'SGMGMT', '1.00', 'SGMGMT_OE', 'Order Entry', 'SG Management - Order Entry', '', '', CURRENT_TIMESTAMP, '', '', '')
