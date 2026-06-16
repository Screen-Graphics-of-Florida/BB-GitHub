<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

$portals = array(
    'SGINQ'  => array('fuid' => 'SGINQ/PORTAL',  'desc' => 'SG INQUIRIES'),
    'SGDASH' => array('fuid' => 'SGDASH/PORTAL', 'desc' => 'SG DASHBOARDS'),
    'SGDINT' => array('fuid' => 'SGDINT/PORTAL', 'desc' => 'SG DATA INTEGRITY'),
    'SGRPT'  => array('fuid' => 'SGRPT/PORTAL',  'desc' => 'SG REPORTS'),
    'SGSOP'  => array('fuid' => 'SGSOP/PORTAL',  'desc' => 'SG SOPS'),
);
$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";
$fList = "'SGINQ/PORTAL','SGDASH/PORTAL','SGDINT/PORTAL','SGRPT/PORTAL','SGSOP/PORTAL'";

// -------------------------------------------------------
// DOWNLOAD BACKUP SQL
// -------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array();
    $lines[] = '-- SG Fix v8 Backup -- ' . date('Y-m-d H:i:s');
    $lines[] = '-- Restore SYPORT FPRESV and SYURLM FUURL/FUDESCU/FUTSUS/FUTSWS for SG portals';
    $lines[] = '';

    $r = db2_exec($conn, "SELECT FPPORT, FPRESV FROM SYPORT WHERE FPPORT IN (" . $pList . ") AND TRIM(FPPAGE) = '' ORDER BY FPPORT");
    $lines[] = '-- SYPORT restore';
    while ($row = db2_fetch_assoc($r)) {
        $port = str_replace("'","''", rtrim($row['FPPORT']));
        $resv = str_replace("'","''", rtrim($row['FPRESV']));
        $lines[] = "UPDATE SYPORT SET FPRESV = '" . $resv . "' WHERE FPPORT = '" . $port . "' AND TRIM(FPPAGE) = '';";
    }

    $lines[] = '';
    $lines[] = '-- SYURLM restore';
    $r2 = db2_exec($conn, "SELECT FUID, FUURL, FUDESCU, FUTSUS, FUTSWS FROM SYURLM WHERE FUID IN (" . $fList . ") ORDER BY FUID");
    while ($row = db2_fetch_assoc($r2)) {
        $fuid    = str_replace("'","''", rtrim($row['FUID']));
        $fuurl   = str_replace("'","''", rtrim($row['FUURL']));
        $fudescu = str_replace("'","''", rtrim($row['FUDESCU']));
        $futsus  = str_replace("'","''", rtrim($row['FUTSUS']));
        $futsws  = str_replace("'","''", rtrim($row['FUTSWS']));
        $lines[] = "UPDATE SYURLM SET FUURL = '" . $fuurl . "', FUDESCU = '" . $fudescu . "', FUTSUS = '" . $futsus . "', FUTSWS = '" . $futsws . "' WHERE FUID = '" . $fuid . "';";
    }

    $fname = 'sg_fix_v8_backup_' . date('Ymd_His') . '.sql';
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Cache-Control: no-cache');
    echo implode("\r\n", $lines);
    exit;
}

// -------------------------------------------------------
// APPLY FIX
// -------------------------------------------------------
if (isset($_POST['fix'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix v8</title>
<style>body{font-family:monospace;font-size:12px;padding:16px;}.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}</style></head><body>';
    echo '<h2>SG Fix v8 — Applying</h2>';

    foreach ($portals as $port => $info) {
        $fuid = $info['fuid'];
        $url  = '@@homeURL@@phpPathCustom/SG/sg_portal_landing.php?portal=' . $port;

        $r1 = db2_exec($conn, "UPDATE SYPORT SET FPRESV = '' WHERE FPPORT = '" . $port . "' AND TRIM(FPPAGE) = ''");
        echo ($r1 ? '<span class="ok">OK</span>' : '<span class="err">ERR</span>');
        echo '  SYPORT FPRESV=empty  ' . $port . ($r1 ? ' (' . db2_num_rows($r1) . ' row)' : ': ' . htmlspecialchars(db2_stmt_errormsg())) . '<br>';

        $r2 = db2_exec($conn, "UPDATE SYURLM SET FUURL = '" . $url . "', FUDESCU = '" . $info['desc'] . "', FUTSUS = 'BILL', FUTSWS = 'BILLA1' WHERE FUID = '" . $fuid . "'");
        echo ($r2 ? '<span class="ok">OK</span>' : '<span class="err">ERR</span>');
        echo '  SYURLM FUURL set     ' . $fuid . ($r2 ? ' (' . db2_num_rows($r2) . ' row)' : ': ' . htmlspecialchars(db2_stmt_errormsg())) . '<br>';
    }

    echo '<br><strong>Done. Log out SG5TEST completely and log back in as SG5TEST / CUSTSRVC.</strong>';
    echo '</body></html>';
    exit;
}

// -------------------------------------------------------
// DEFAULT: show state + buttons
// -------------------------------------------------------
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix v8</title>
<style>body{font-family:monospace;font-size:12px;padding:16px;}
table{border-collapse:collapse;margin:8px 0;}td,th{border:1px solid #ccc;padding:3px 8px;}th{background:#eee;}
.hi{background:#ffa;font-weight:bold;}.ok{color:green;}.step{border:1px solid #999;padding:10px;margin:8px 0;background:#f9f9f9;}
</style></head><body>';
echo '<h2>SG Menu Fix v8</h2>';
echo '<p>Sets <b>SYPORT.FPRESV = empty</b> and <b>SYURLM.FUURL = landing page</b> to match SG_DOCUMENTATION (only working custom portal).</p>';

echo '<h3>Current state</h3>';
echo '<b>SYPORT.FPRESV (need: empty):</b><br>';
$r = db2_exec($conn, "SELECT FPPORT, FPRESV FROM SYPORT WHERE FPPORT IN (" . $pList . ") AND TRIM(FPPAGE) = '' ORDER BY FPPORT");
echo '<table><tr><th>FPPORT</th><th>FPRESV</th></tr>';
while ($row = db2_fetch_assoc($r)) {
    $v = trim($row['FPRESV']);
    echo '<tr><td>' . htmlspecialchars(trim($row['FPPORT'])) . '</td>';
    echo '<td' . ($v !== '' ? ' class="hi"' : '') . '>' . ($v === '' ? '(empty)' : htmlspecialchars($v)) . '</td></tr>';
}
echo '</table>';

echo '<br><b>SYURLM.FUURL (need: landing page URL):</b><br>';
$r2 = db2_exec($conn, "SELECT FUID, FUURL FROM SYURLM WHERE FUID IN (" . $fList . ") ORDER BY FUID");
echo '<table><tr><th>FUID</th><th>FUURL</th></tr>';
while ($row = db2_fetch_assoc($r2)) {
    $v = trim($row['FUURL']);
    echo '<tr><td>' . htmlspecialchars(trim($row['FUID'])) . '</td>';
    echo '<td' . ($v === '' ? ' class="hi"' : '') . '>' . ($v === '' ? '(empty)' : htmlspecialchars($v)) . '</td></tr>';
}
echo '</table>';

echo '<h3>Steps</h3>';
echo '<div class="step"><b>Step 1 — Download backup to your PC (required):</b><br><br>';
echo '<a href="?action=backup" style="background:#060;color:#fff;padding:6px 16px;text-decoration:none;font-size:13px;">Download backup SQL</a>';
echo '<br><small>Saves sg_fix_v8_backup_YYYYMMDD_HHmmss.sql to your Downloads folder</small></div>';

echo '<div class="step"><b>Step 2 — Apply fix (after backup is saved):</b><br><br>';
echo '<form method="post"><button name="fix" value="1" type="submit"
  style="background:#c00;color:#fff;padding:6px 16px;font-size:13px;">
  Apply Fix</button></form></div>';

echo '</body></html>';
?>
