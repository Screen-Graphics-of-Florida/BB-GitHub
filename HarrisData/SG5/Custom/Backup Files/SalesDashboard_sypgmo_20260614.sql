-- Sales Dashboard (and Bookings Dashboard) SYPGMO registration
-- Run on SG5 (Test) FIRST using S5HDSDATA, then on EIP (Live) using SGHDSDATA
-- Date: 2026-06-14
--
-- Step 1: Find which library SYPGMO lives in (run once to confirm):
--   SELECT TABLE_SCHEMA, TABLE_NAME FROM QSYS2.SYSTABLES WHERE TABLE_NAME = 'SYPGMO';
--
-- Step 2: Verify neither entry already exists:
--   SELECT * FROM SYPGMO WHERE SOPGID IN ('SGBKDASH','SGSLSDASH');
--
-- Step 3: If not found, run the INSERTs below.
--   No library qualifier needed — your session library list resolves SYPGMO automatically.

-- Bookings Dashboard (register if missing)
INSERT INTO SG5STDPGM.SYPGMO (SOPGID, SOMOPT, SOMDES, SORESV)
  VALUES ('SGBKDASH', '1', 'View', 'Y');

-- Sales Dashboard
INSERT INTO SG5STDPGM.SYPGMO (SOPGID, SOMOPT, SOMDES, SORESV)
  VALUES ('SGSLSDASH', '1', 'View', 'Y');
