-- SGMGMT Portal -- SYURLM + SYPORT insert
-- EIP (Live) -- run AFTER SG5 confirmed working
-- 2026-06-25
-- NOTE: SYPORT-MGMT.csv had FPID='SGTMGMT/PORTAL' (extra T); corrected to 'SGMGMT/PORTAL' to match SYURLM.
--
-- Step 1: Backup current rows if they exist (run first):
--   SELECT * FROM SGHDSDATA.SYURLM WHERE FUID = 'SGMGMT/PORTAL'
--   SELECT * FROM SGHDSDATA.SYPORT WHERE FPPORT = 'SGMGMT'
--
-- Step 2: Verify not already present (if rows returned above, stop here):
--
-- Step 3: Run INSERTs below.

INSERT INTO SGHDSDATA.SYURLM
  (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,FUTSTP,FUTSUS,FUTSWS,FUTSPT)
  VALUES ('SGMGMT/PORTAL','Management','Management','',
          '@@homeURL@@phpPathCustom/SG/sg_portal_landing.php?portal=SGMGMT',
          '','','Management Portal','2026-06-23-14.33.20.320620','PUSHALL','','Y')

INSERT INTO SGHDSDATA.SYPORT
  (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,FPTSTP,FPTSUS,FPTSWS,FPTSPT)
  VALUES ('SGMGMT','','1.00','SGMGMT/PORTAL','','Management Portal','','',
          '2026-06-25-10.43.20.390345','PUSHALL','','Y')
