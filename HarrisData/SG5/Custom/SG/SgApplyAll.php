<?php
// SgApplyAll.php
// Idempotent re-apply of all SG DB customizations after a HarrisData update.
// Safe to run multiple times — all inserts use WHERE NOT EXISTS.
//
// Improvements over PushAllMenusLive.php:
//   - Roles are read DYNAMICALLY from SYROLM (no hardcoded list to maintain)
//   - Reserved roles (RMRESV='Y') are automatically excluded
//   - Step 9: native portal SYPORR rows added for every role in whitelist mode
//             (prevents the TIFFANY/ENAPOLES "portals disappear" issue)
//   - Step 10: fixes any PRSEL='' to 'Y' for non-reserved roles
//   - Preview mode before any changes; backup written before execute
//   - pgmlib param for test vs live program library
//
// USERS list is still hardcoded (Step 8 SYPGMS). Add new users here manually.
//
// Preview:  https://portal.screen-graphics.com:5601/Custom/SG/SgApplyAll.php
// Execute:  https://portal.screen-graphics.com:5601/Custom/SG/SgApplyAll.php?confirm=PUSH
// Test pgm: append &pgmlib=SG5STDPGM

set_time_limit(300);

$pgmlib = 'HDSSTDPGM';
if (!empty($_GET['pgmlib'])) {
    $p = strtoupper(trim($_GET['pgmlib']));
    if (preg_match('/^[A-Z][A-Z0-9]{1,9}$/', $p)) $pgmlib = $p;
}

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

// ============================================================
// Configuration
// ============================================================

$portals = [
    'SGINQ'  => 'SG Inquiries',
    'SGDASH' => 'SG Dashboards',
    'SGDINT' => 'SG Data Integrity',
    'SGRPT'  => 'SG Reports',
    'SGSOP'  => 'SG SOPs',
];
$cats = [
    'ACCT'    => 'Accounting',
    'INVMGMT' => 'Inventory Mgmt',
    'MFG'     => 'Manufacturing',
    'OE'      => 'Order Entry',
    'PLN'     => 'Planning',
    'PUR'     => 'Purchasing',
];
$pgms = [
    'SGPORTLND' => 'Portal Landing Page',
    'MOREQ'     => 'MO Requirements',
    'BOOKDASH'  => 'Bookings Dashboard',
    'BOOKDRLL'  => 'Bookings Drilldown',
    'SALESDASH' => 'Sales Dashboard',
    'SALESDRLL' => 'Sales Drilldown',
    'SHIPSDASH' => 'Shipments Dashboard',
    'OPENORDLC' => 'OO Line Item Cmts',
    'CSSRVINQ'  => 'CS Service Inquiry',
    'CSDATINT'  => 'CS Data Integrity',
    'INVDATINT' => 'Inv Data Integrity',
    'MODLYLBR'  => 'MO Daily Labor',
    'MOMATLCMP' => 'MO Material Comps',
    'POREQRPT'  => 'PO Requirements Report',
];

// Add new users to this list as they are created.
$users = [
    'ADAVIS','ALOPEZ','AMEDEROS','AMEDLEY','ART','BBUSCH','BILL',
    'CHUTCH','DBROWNE','DBUSCH','DGILLESPIE','EBORRELL','EHESSLER',
    'FXAVIOR','FXAVIORSG5','GFORD','HDACCT','HDACKDOCS','HDFILES',
    'HDSTEST','JJIMENEZ','JOYCELD','JUDE','KHART','LCERVANTES',
    'LSEARLES','LTINNEY','MANNY','MCRESPO','MLOPEZ','MREID',
    'MSOMOZA','MTAKACS','NICK','PURCHASING','QC01','RBLANCHARD',
    'RECEPTION','SCANTALUPO','SCREENRIP','SGIT','SHIP','SHOP',
    'TIFFANY','TREID','TRIDDELL','USERTEST','VMINGUILLO','ZBLAKE',
];

$pcodes      = array_keys($portals);
$sgList      = "'" . implode("','", $pcodes) . "'";
$pgmList     = "'" . implode("','", array_keys($pgms)) . "'";
$landingBase = '@@homeURL@@phpPathCustom/SG/sg_portal_landing.php';

// ============================================================
// Helpers
// ============================================================

function qrows($conn, $sql) {
    $rows = []; $s = @db2_exec($conn, $sql);
    if ($s === false) return ['__error' => db2_stmt_errormsg()];
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}
function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s); return $r ? db2_result($s, 0) : null;
}
function toInserts($rows, $table) {
    if (empty($rows) || isset($rows[0]['__error'])) return [];
    $cols = implode(', ', array_keys($rows[0]));
    $out  = [];
    foreach ($rows as $row) {
        $vals = [];
        foreach ($row as $v) {
            $vals[] = ($v === null) ? 'NULL'
                    : "'" . str_replace("'", "''", rtrim((string)$v)) . "'";
        }
        $out[] = "INSERT INTO $table ($cols) VALUES (" . implode(', ', $vals) . ")";
    }
    return $out;
}

// ============================================================
// Dynamic role detection
// ============================================================

// Reserved roles — never touch these
$rsvQ   = qrows($conn, "SELECT RTRIM(RMROLE) AS R FROM SGHDSDATA.SYROLM WHERE RTRIM(RMRESV)='Y'");
$rsvArr = [];
foreach ($rsvQ as $r) { if (!isset($r['__error'])) $rsvArr[] = "'" . $r['R'] . "'"; }
$rsvList = empty($rsvArr) ? "'__NONE__'" : implode(',', $rsvArr);

// Non-reserved roles (dynamic — picks up new roles automatically)
$rolesQ = qrows($conn,
    "SELECT RTRIM(RMROLE) AS RMROLE FROM SGHDSDATA.SYROLM "
  . "WHERE RMROLE NOT IN ($rsvList) ORDER BY RMROLE");
$roles = [];
foreach ($rolesQ as $r) { if (!isset($r['__error'])) $roles[] = $r['RMROLE']; }

// HD_ALL_SG is the bypass role — include in SYROLD/SYURLM/SYPORT but exclude from SYPORR
$bypassRoles = ['HD_ALL_SG'];

// ============================================================
// Backup helpers (same logic as SgBackup.php)
// ============================================================

function writeBackup($conn, $sgList, $rsvList, $pgmList, $pgmlib) {
    $tables = [
        'SGHDSDATA.SYURLM' => qrows($conn,
            "SELECT * FROM SGHDSDATA.SYURLM WHERE RTRIM(FUID) LIKE 'SG%' ORDER BY FUID"),
        'SGHDSDATA.SYPORT' => qrows($conn,
            "SELECT * FROM SGHDSDATA.SYPORT WHERE FPPORT IN ($sgList) ORDER BY FPPORT,FPPAGE,FPSEQ"),
        'SGHDSDATA.SYROLD' => qrows($conn,
            "SELECT * FROM SGHDSDATA.SYROLD WHERE RDPORT IN ($sgList) AND RDROLE NOT IN ($rsvList) ORDER BY RDROLE,RDSEQN"),
        'SGHDSDATA.SYPORR' => qrows($conn,
            "SELECT * FROM SGHDSDATA.SYPORR WHERE PRROLE NOT IN ($rsvList) ORDER BY PRROLE,PRPORT,PRPAGE,PRSEQ"),
        'SGHDSDATA.SYPGMS' => qrows($conn,
            "SELECT * FROM SGHDSDATA.SYPGMS WHERE SPPGID IN ($pgmList) ORDER BY SPUSER,SPPGID"),
        "$pgmlib.SYPGMO"   => qrows($conn,
            "SELECT * FROM $pgmlib.SYPGMO WHERE SOPGID IN ($pgmList) ORDER BY SOPGID,SOMOPT"),
    ];
    $lines = [
        '-- SgApplyAll.php pre-apply backup — ' . date('Y-m-d H:i:s'),
        "-- DELETE THEN INSERT to restore this state:",
        '',
        "DELETE FROM SGHDSDATA.SYPGMS WHERE SPPGID IN ($pgmList)",
        "DELETE FROM $pgmlib.SYPGMO WHERE SOPGID IN ($pgmList)",
        "DELETE FROM SGHDSDATA.SYPORR WHERE PRROLE NOT IN ($rsvList)",
        "DELETE FROM SGHDSDATA.SYROLD WHERE RDPORT IN ($sgList) AND RDROLE NOT IN ($rsvList)",
        "DELETE FROM SGHDSDATA.SYPORT WHERE FPPORT IN ($sgList)",
        "DELETE FROM SGHDSDATA.SYURLM WHERE RTRIM(FUID) LIKE 'SG%'",
        '',
    ];
    foreach ($tables as $table => $rows) {
        $lines[] = "-- $table";
        $lines   = array_merge($lines, toInserts($rows, $table));
        $lines[] = '';
    }
    $ts      = date('Ymd_His');
    $outDir  = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) $outDir = dirname(__FILE__);
    $file    = $outDir . '/SgApplyAll_pre_' . $ts . '.sql';
    $written = file_put_contents($file, implode("\n", $lines));
    return [$file, $written !== false ? 'OK' : 'WRITE FAILED'];
}

// ============================================================
// Preview counts (no changes)
// ============================================================

$preview = [];

// SYROLD: SG portals missing for any non-reserved role
$preview['SYROLD'] = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYROLM m "
  . "CROSS JOIN (SELECT '$pcodes[0]' AS P FROM SYSIBM.SYSDUMMY1"
  . implode('', array_map(fn($p) => " UNION ALL SELECT '$p' FROM SYSIBM.SYSDUMMY1", array_slice($pcodes,1))) . ") AS v "
  . "WHERE m.RMROLE NOT IN ($rsvList) "
  . "  AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYROLD WHERE RDROLE=m.RMROLE AND RTRIM(RDPORT)=v.P)");

// SYPORR top-level: SG portals missing for non-bypass, non-reserved roles
$preview['SYPORR_SG'] = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYROLM m "
  . "CROSS JOIN SGHDSDATA.SYPORT p "
  . "WHERE RTRIM(p.FPPORT) IN ($sgList) AND RTRIM(p.FPPAGE)='' "
  . "  AND m.RMROLE NOT IN ($rsvList) AND m.RMROLE <> 'HD_ALL_SG' "
  . "  AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYPORR WHERE PRROLE=m.RMROLE AND RTRIM(PRPORT)=RTRIM(p.FPPORT) AND RTRIM(PRPAGE)='')");

// SYPORR native: any SYROLD portal missing a top-level SYPORR row (whitelist fix)
$preview['SYPORR_NATIVE'] = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYROLD r "
  . "WHERE r.RDROLE NOT IN ($rsvList) AND r.RDROLE <> 'HD_ALL_SG' "
  . "  AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYPORR WHERE PRROLE=r.RDROLE AND RTRIM(PRPORT)=RTRIM(r.RDPORT) AND RTRIM(PRPAGE)='')");

// SYPORR PRSEL='' that need fixing
$preview['PRSEL_FIX'] = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE NOT IN ($rsvList) AND RTRIM(PRPAGE)='' AND RTRIM(PRSEL)<>'Y'");

// SYPGMO: programs missing from pgmlib
$preview['SYPGMO'] = (int)qval($conn,
    "SELECT COUNT(*) FROM SYSIBM.SYSDUMMY1 WHERE 1=0"); // placeholder — computed below

// ============================================================
// Execute
// ============================================================

$confirm = (isset($_GET['confirm']) && $_GET['confirm'] === 'PUSH');
$cntOk = $cntSkip = $cntFail = 0;
$log = [];
$backupFile = ''; $backupStatus = '';

function runSql($label, $sql) {
    global $conn, $cntOk, $cntSkip, $cntFail, $log;
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) {
        $cntFail++;
        $log[] = ['FAIL', $label, db2_stmt_errormsg()];
    } else {
        $n = db2_num_rows($stmt);
        if ($n > 0) { $cntOk++;   $log[] = ['OK',   $label, "$n row(s)"]; }
        else        { $cntSkip++; $log[] = ['SKIP', $label, 'already exists']; }
    }
}

if ($confirm) {

    // Backup first
    [$backupFile, $backupStatus] = writeBackup($conn, $sgList, $rsvList, $pgmList, $pgmlib);

    // ----------------------------------------------------------
    // STEP 1: SYURLM — portal-level entries (5)
    // ----------------------------------------------------------
    foreach ($portals as $pcode => $pdesc) {
        $fuid   = "$pcode/PORTAL";
        $url    = "$landingBase?portal=$pcode";
        $pdescu = strtoupper($pdesc);
        runSql("SYURLM $fuid",
            "INSERT INTO SGHDSDATA.SYURLM
                 (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,FUTSTP,FUTSUS,FUTSWS,FUTSPT)
             SELECT '$fuid','$pdesc','$pdesc','','$url','','','$pdescu',
                    CURRENT_TIMESTAMP,'SGAPPLY','','Y'
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYURLM WHERE FUID='$fuid')");
    }

    // ----------------------------------------------------------
    // STEP 2: SYURLM — category sub-items (30)
    // ----------------------------------------------------------
    foreach ($portals as $pcode => $pdesc) {
        foreach ($cats as $ccode => $cdesc) {
            $fuid   = "{$pcode}_{$ccode}";
            $url    = "$landingBase?portal=$pcode&cat=$ccode";
            $futitl = "$pdesc - $cdesc";
            runSql("SYURLM $fuid",
                "INSERT INTO SGHDSDATA.SYURLM
                     (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,FUTSTP,FUTSUS,FUTSWS,FUTSPT)
                 SELECT '$fuid','$cdesc','$futitl','_blank','$url','','Y','',
                        CURRENT_TIMESTAMP,'','',''
                 FROM SYSIBM.SYSDUMMY1
                 WHERE NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYURLM WHERE FUID='$fuid')");
        }
    }

    // ----------------------------------------------------------
    // STEP 3: SYPORT — top-level nav tabs (5)
    // ----------------------------------------------------------
    foreach ($portals as $pcode => $pdesc) {
        $fpid = "$pcode/PORTAL";
        runSql("SYPORT top $pcode",
            "INSERT INTO SGHDSDATA.SYPORT
                 (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,FPTSTP,FPTSUS,FPTSWS,FPTSPT)
             SELECT '$pcode','',1,'$fpid','','$pdesc','','',
                    CURRENT_TIMESTAMP,'SGAPPLY','','Y'
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYPORT WHERE FPPORT='$pcode' AND FPPAGE='')");
    }

    // ----------------------------------------------------------
    // STEP 4: SYPORT — category sub-items (30)
    // ----------------------------------------------------------
    foreach ($portals as $pcode => $pdesc) {
        $catseq = 0;
        foreach ($cats as $ccode => $cdesc) {
            $catseq++;
            $fpid   = "{$pcode}_{$ccode}";
            $fptitl = "$pdesc - $cdesc";
            runSql("SYPORT $fpid",
                "INSERT INTO SGHDSDATA.SYPORT
                     (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,FPTSTP,FPTSUS,FPTSWS,FPTSPT)
                 SELECT '$pcode','$pcode',$catseq,'$fpid','$cdesc','$fptitl','','',
                        CURRENT_TIMESTAMP,'SGAPPLY','',''
                 FROM SYSIBM.SYSDUMMY1
                 WHERE NOT EXISTS (SELECT 1 FROM SGHDSDATA.SYPORT WHERE FPPORT='$pcode' AND FPID='$fpid')");
        }
    }

    // ----------------------------------------------------------
    // STEP 5: SYROLD — role-to-portal assignments (dynamic roles)
    // ----------------------------------------------------------
    foreach ($roles as $role) {
        foreach ($pcodes as $pcode) {
            runSql("SYROLD $role/$pcode",
                "INSERT INTO SGHDSDATA.SYROLD
                     (RDROLE,RDPORT,RDSEQN,RDRESV,RDTSTP,RDTSUS,RDTSWS,RDTSPT)
                 SELECT '$role','$pcode',
                        COALESCE((SELECT MAX(RDSEQN) FROM SGHDSDATA.SYROLD WHERE RDROLE='$role'),0)+1,
                        '',CURRENT_TIMESTAMP,'SGAPPLY','SGAPPLY',''
                 FROM SYSIBM.SYSDUMMY1
                 WHERE NOT EXISTS (
                     SELECT 1 FROM SGHDSDATA.SYROLD WHERE RDROLE='$role' AND RTRIM(RDPORT)='$pcode')");
        }
    }

    // ----------------------------------------------------------
    // STEP 6: SYPORR — SG portal top-level rows (non-bypass roles)
    // ----------------------------------------------------------
    foreach ($roles as $role) {
        if (in_array($role, $bypassRoles)) continue;
        foreach ($portals as $pcode => $pdesc) {
            $prid = "$role/$pcode";
            runSql("SYPORR top $role/$pcode",
                "INSERT INTO SGHDSDATA.SYPORR
                     (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
                 SELECT '$role','$pcode','',1,'$prid','Y',
                        CURRENT_TIMESTAMP,'SGAPPLY',''
                 FROM SYSIBM.SYSDUMMY1
                 WHERE NOT EXISTS (
                     SELECT 1 FROM SGHDSDATA.SYPORR
                     WHERE PRROLE='$role' AND RTRIM(PRPORT)='$pcode' AND RTRIM(PRPAGE)='')");
        }
    }

    // ----------------------------------------------------------
    // STEP 7: SYPORR — SG portal sub-item rows (non-bypass roles)
    // ----------------------------------------------------------
    foreach ($roles as $role) {
        if (in_array($role, $bypassRoles)) continue;
        foreach ($portals as $pcode => $pdesc) {
            for ($i = 1; $i <= 6; $i++) {
                $seqstr = number_format($i, 2);
                $prid   = "$role/$pcode/$pcode/$seqstr";
                runSql("SYPORR sub $role/$pcode/$i",
                    "INSERT INTO SGHDSDATA.SYPORR
                         (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
                     SELECT '$role','$pcode','$pcode',$i,'$prid','Y',
                            CURRENT_TIMESTAMP,'SGAPPLY',''
                     FROM SYSIBM.SYSDUMMY1
                     WHERE NOT EXISTS (
                         SELECT 1 FROM SGHDSDATA.SYPORR
                         WHERE PRROLE='$role' AND RTRIM(PRPORT)='$pcode'
                           AND RTRIM(PRPAGE)='$pcode' AND PRSEQ=$i)");
            }
        }
    }

    // ----------------------------------------------------------
    // STEP 8: SYPORR — native portal top-level rows (whitelist fix)
    // Ensures every SYROLD portal for every non-reserved, non-bypass
    // role has a top-level SYPORR row (PRPAGE='', PRSEL='Y').
    // This prevents the TIFFANY/ENAPOLES "portals disappear" issue.
    // ----------------------------------------------------------
    $nativeResult = @db2_exec($conn,
        "INSERT INTO SGHDSDATA.SYPORR
             (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
         SELECT DISTINCT RTRIM(r.RDROLE), RTRIM(r.RDPORT), '', 1,
                RTRIM(r.RDROLE) || '/' || RTRIM(r.RDPORT),
                'Y', CURRENT_TIMESTAMP, 'SGAPPLY', 'Y'
         FROM SGHDSDATA.SYROLD r
         WHERE r.RDROLE NOT IN ($rsvList) AND r.RDROLE <> 'HD_ALL_SG'
           AND NOT EXISTS (
               SELECT 1 FROM SGHDSDATA.SYPORR
               WHERE PRROLE=r.RDROLE AND RTRIM(PRPORT)=RTRIM(r.RDPORT) AND RTRIM(PRPAGE)='')");
    if ($nativeResult === false) {
        $cntFail++;
        $log[] = ['FAIL', 'SYPORR native portals (bulk)', db2_stmt_errormsg()];
    } else {
        $n = db2_num_rows($nativeResult);
        if ($n > 0) { $cntOk++;   $log[] = ['OK',   'SYPORR native portals (bulk)', "$n row(s) added"]; }
        else        { $cntSkip++; $log[] = ['SKIP', 'SYPORR native portals (bulk)', 'all present']; }
    }

    // ----------------------------------------------------------
    // STEP 9: SYPORR — fix any PRSEL='' to 'Y' (belt & suspenders)
    // ----------------------------------------------------------
    $prselResult = @db2_exec($conn,
        "UPDATE SGHDSDATA.SYPORR SET PRSEL='Y'
         WHERE PRROLE NOT IN ($rsvList) AND RTRIM(PRPAGE)='' AND RTRIM(PRSEL)<>'Y'");
    if ($prselResult === false) {
        $cntFail++;
        $log[] = ['FAIL', 'SYPORR fix PRSEL blanks', db2_stmt_errormsg()];
    } else {
        $n = db2_num_rows($prselResult);
        if ($n > 0) { $cntOk++;   $log[] = ['OK',   'SYPORR fix PRSEL blanks', "$n row(s) updated"]; }
        else        { $cntSkip++; $log[] = ['SKIP', 'SYPORR fix PRSEL blanks', 'none found']; }
    }

    // ----------------------------------------------------------
    // STEP 10: SYPGMO — program registrations
    // ----------------------------------------------------------
    foreach ($pgms as $pgmid => $pgmdesc) {
        runSql("SYPGMO $pgmid",
            "INSERT INTO $pgmlib.SYPGMO (SOPGID,SOMOPT,SOMDES,SORESV)
             SELECT '$pgmid',1,'$pgmdesc',' '
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM $pgmlib.SYPGMO WHERE SOPGID='$pgmid' AND SOMOPT=1)");
    }

    // ----------------------------------------------------------
    // STEP 11: SYPGMS — user-program security
    // ----------------------------------------------------------
    foreach ($users as $user) {
        foreach ($pgms as $pgmid => $pgmdesc) {
            runSql("SYPGMS $user/$pgmid",
                "INSERT INTO SGHDSDATA.SYPGMS
                     (SPUSER,SPPGID,SPOP01,SPOP02,SPOP03,SPOP04,SPOP05,SPOP06,
                      SPOP07,SPOP08,SPOP09,SPOP10,SPOP11,SPOP12,SPOP13,SPOP14,SPOP15)
                 SELECT '$user','$pgmid','Y','N','N','N','N','N',
                        'N','N','N','N','N','N','N','N','N'
                 FROM SYSIBM.SYSDUMMY1
                 WHERE NOT EXISTS (
                     SELECT 1 FROM SGHDSDATA.SYPGMS WHERE SPUSER='$user' AND SPPGID='$pgmid')");
        }
    }
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG Apply All</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.hdr .sub { font-size:11px; opacity:.75; margin-top:3px; font-weight:normal; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.warn { background:#fff8e1; border:1px solid #ffe082; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.err  { background:#fce4ec; border:1px solid #f48fb1; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; color:#c62828; font-weight:bold; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; font-size:12px; }
.cards { display:flex; gap:14px; margin-bottom:16px; }
.card { background:#fff; border-radius:5px; padding:12px 22px; text-align:center;
        box-shadow:0 1px 4px rgba(0,0,0,.08); min-width:90px; }
.card.ok   { border-left:4px solid #2e7d32; }
.card.skip { border-left:4px solid #e65100; }
.card.fail { border-left:4px solid #c62828; }
.card .num { font-size:30px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:3px; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff; padding:5px 10px;
     text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:12px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
tr.ok   td:first-child { color:#2e7d32; font-weight:bold; }
tr.skip td:first-child { color:#aaa; }
tr.fail td { color:#c62828; font-weight:bold; }
.section { font-size:13px; font-weight:bold; color:#2a5a8c; margin:16px 0 6px; }
.btn { display:inline-block; margin-top:10px; background:#1565c0; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#0d47a1; }
.note { margin-top:8px; font-size:11px; color:#666; }
ul.steps { padding-left:20px; font-size:12px; line-height:1.8; }
</style>
</head>
<body>

<div class="hdr">
  SG Apply All — Idempotent Re-Apply
  <div class="sub">SGHDSDATA + <?= htmlspecialchars($pgmlib) ?> &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<?php if ($confirm): ?>

<!-- ===== EXECUTE RESULTS ===== -->
<div class="<?= $cntFail > 0 ? 'warn' : 'ok' ?>">
  <strong><?= $cntFail > 0 ? 'Completed with errors.' : 'Done.' ?></strong>
  <?= $cntOk ?> inserted/updated, <?= $cntSkip ?> already existed, <?= $cntFail ?> failed.
  Log out and back in to verify portal navigation.
</div>
<div class="<?= strpos($backupStatus,'FAIL')!==false ? 'warn' : 'ok' ?>">
  <strong>Pre-apply backup:</strong> <?= htmlspecialchars(basename($backupFile)) ?>
  &mdash; <?= htmlspecialchars($backupStatus) ?>
</div>

<div class="cards">
  <div class="card ok">  <div class="num"><?= $cntOk ?></div>  <div class="lbl">Inserted/Updated</div></div>
  <div class="card skip"><div class="num"><?= $cntSkip ?></div><div class="lbl">Already Existed</div></div>
  <div class="card fail"><div class="num"><?= $cntFail ?></div><div class="lbl">Failed</div></div>
</div>

<table>
  <tr><th>Status</th><th>Item</th><th>Note</th></tr>
  <?php foreach ($log as [$status, $label, $note]): ?>
  <tr class="<?= strtolower($status) ?>">
    <td><?= htmlspecialchars($status) ?></td>
    <td><?= htmlspecialchars($label) ?></td>
    <td style="font-family:Arial"><?= htmlspecialchars($note) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php else: ?>

<!-- ===== PREVIEW ===== -->
<div class="info">
  <strong>Safe to run anytime.</strong> All inserts use WHERE NOT EXISTS — rows that already exist are
  skipped. Backup is written automatically before any changes.<br><br>
  <strong>What this applies:</strong>
  <ul class="steps">
    <li>Steps 1–2: SYURLM — 5 portal + 30 sub-item URL definitions</li>
    <li>Steps 3–4: SYPORT — 5 portal + 30 sub-item navigation entries</li>
    <li>Step 5: SYROLD — SG portals added to all <?= count($roles) ?> non-reserved roles</li>
    <li>Steps 6–7: SYPORR — SG portal top-level + sub-item rows (non-bypass roles)</li>
    <li>Step 8: SYPORR — native portal rows for every role in whitelist mode <em>(prevents portals disappearing)</em></li>
    <li>Step 9: SYPORR — fix any PRSEL='' to 'Y'</li>
    <li>Step 10: SYPGMO — <?= count($pgms) ?> SG program registrations (<?= htmlspecialchars($pgmlib) ?>)</li>
    <li>Step 11: SYPGMS — <?= count($users) ?> users × <?= count($pgms) ?> programs = <?= count($users)*count($pgms) ?> security rows</li>
  </ul>
</div>

<div class="section">Roles (<?= count($roles) ?> non-reserved — will be processed)</div>
<table>
  <tr><th>Role</th><th>SG Portals in SYROLD?</th><th>Native SYPORR gaps</th></tr>
  <?php foreach ($roles as $role):
      $hasSG = (int)qval($conn,
          "SELECT COUNT(*) FROM SGHDSDATA.SYROLD WHERE RDROLE='$role' AND RDPORT IN ($sgList)");
      $gaps  = in_array($role, $bypassRoles) ? -1 :
               (int)qval($conn,
                   "SELECT COUNT(*) FROM SGHDSDATA.SYROLD r "
                 . "WHERE r.RDROLE='$role' AND NOT EXISTS ("
                 . "  SELECT 1 FROM SGHDSDATA.SYPORR WHERE PRROLE='$role' AND RTRIM(PRPORT)=RTRIM(r.RDPORT) AND RTRIM(PRPAGE)='')");
  ?>
  <tr>
    <td><?= htmlspecialchars($role) ?></td>
    <td style="color:<?= $hasSG===5?'#2e7d32':'#c62828' ?>">
      <?= $hasSG ?>/5</td>
    <td style="color:<?= $gaps>0?'#c62828':($gaps<0?'#999':'#2e7d32') ?>">
      <?= $gaps < 0 ? 'bypass — skip' : ($gaps > 0 ? "$gaps missing" : 'OK') ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php if (!empty($rsvArr)): ?>
<div class="section">Reserved Roles — skipped (<?= count($rsvArr) ?> roles)</div>
<div class="info" style="font-family:monospace">
  <?= implode(', ', array_map(fn($r) => htmlspecialchars(trim($r,"'")), $rsvArr)) ?>
</div>
<?php endif; ?>

<div class="warn">
  <strong>Preview summary:</strong>
  ~<?= $preview['SYROLD'] ?> SYROLD inserts needed &bull;
  ~<?= $preview['SYPORR_SG'] ?> SG SYPORR inserts needed &bull;
  ~<?= $preview['SYPORR_NATIVE'] ?> native SYPORR inserts needed &bull;
  <?= $preview['PRSEL_FIX'] ?> PRSEL rows to fix.
</div>

<a class="btn" href="?confirm=PUSH<?= $pgmlib !== 'HDSSTDPGM' ? '&pgmlib=' . urlencode($pgmlib) : '' ?>">
  Run Apply All &mdash; <?= count($roles) ?> Roles
</a>
<p class="note">
  Backup written to <code>../Backup Files/SgApplyAll_pre_YYYYMMDD_HHmmss.sql</code> before any changes.<br>
  Test env program library: <a href="?pgmlib=SG5STDPGM">use &amp;pgmlib=SG5STDPGM</a>
</p>

<?php endif; ?>

</body>
</html>
