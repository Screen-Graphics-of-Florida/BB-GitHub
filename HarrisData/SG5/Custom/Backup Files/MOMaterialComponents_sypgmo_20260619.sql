-- MO Material Components Issues — SYPGMO registration
-- Run SG5 (Test) FIRST, then EIP (Live)
-- Date: 2026-06-19
--
-- Step 1: Confirm SYPGMO location (run once):
--   SELECT TABLE_SCHEMA, TABLE_NAME FROM QSYS2.SYSTABLES WHERE TABLE_NAME = 'SYPGMO'
--
-- Step 2: Verify entry does not already exist:
--   SELECT * FROM SG5STDPGM.SYPGMO WHERE SOPGID = 'SGMOCMPISS'
--
-- Step 3: If not found, run the INSERT below.

-- SG5 (Test)
INSERT INTO SG5STDPGM.SYPGMO (SOPGID, SOMOPT, SOMDES, SORESV)
  VALUES ('SGMOCMPISS', '1', 'View', 'Y')

-- EIP (Live) — run after SG5 confirmed working:
-- INSERT INTO HDSSTDPGM.SYPGMO (SOPGID, SOMOPT, SOMDES, SORESV)
--   VALUES ('SGMOCMPISS', '1', 'View', 'Y')
