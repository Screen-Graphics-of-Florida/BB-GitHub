-- SGMGMT Portal -- SYURLM + SYPORT insert
-- SG5 (Test) -- run FIRST
-- 2026-06-25
-- NOTE: SYPORT-MGMT.csv had FPID='SGTMGMT/PORTAL' (extra T); corrected to 'SGMGMT/PORTAL' to match SYURLM.
--
-- Step 1: Backup current rows if they exist (run first):
--   SELECT * FROM S5HDSDATA.SYURLM WHERE FUID = 'SGMGMT/PORTAL'
--   SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORT = 'SGMGMT'
--
-- Step 2: Verify not already present (if rows returned above, stop here):
--
-- Step 3: Run INSERTs below.

INSERT INTO S5HDSDATA.SYURLM
  (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,FUTSTP,FUTSUS,FUTSWS,FUTSPT)
  VALUES ('SGMGMT/PORTAL','Management','Management','',
          '@@homeURL@@phpPathCustom/SG/sg_portal_landing.php?portal=SGMGMT',
          '','','Management Portal','2026-06-23-14.33.20.320620','PUSHALL','','Y')

INSERT INTO S5HDSDATA.SYPORT
  (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,FPTSTP,FPTSUS,FPTSWS,FPTSPT)
  VALUES ('SGMGMT','','1.00','SGMGMT/PORTAL','','Management Portal','','',
          '2026-06-25-10.43.20.390345','PUSHALL','','Y')
