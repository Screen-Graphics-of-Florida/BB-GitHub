<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

// Revert sg_menu_fix.php v8 changes:
//   SYPORT.FPRESV  was 'Y'  → was set to ''  → restore to 'Y'
//   SYURLM.FUURL   was ''   → was set to URL  → restore to ''
//   SYURLM.FUDESCU was ''   → was set to desc → restore to ''
//   SYURLM.FUTSUS  was ''   → was set to BILL → restore to ''
//   SYURLM.FUTSWS  was ''   → was set to BILLA1 → restore to ''

$fList = "'SGINQ/PORTAL','SGDASH/PORTAL','SGDINT/PORTAL','SGRPT/PORTAL','SGSOP/PORTAL'";
$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";

if (isset($_POST['revert'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Revert v8</title>
<style>body{font-family:monospace;font-size:12px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}</style></head><body>';
    echo '<h2>Reverting sg_menu_fix v8 changes</h2>';

    $r1 = db2_exec($conn, "UPDATE SYPORT SET FPRESV = 'Y' WHERE FPPORT IN (" . $pList . ") AND TRIM(FPPAGE) = ''");
    echo ($r1 ? '<span class="ok">OK</span>' : '<span class="err">ERR</span>');
    echo '  SYPORT.FPRESV = Y for 5 SG portals' . ($r1 ? ' (' . db2_num_rows($r1) . ' rows)' : ': ' . htmlspecialchars(db2_stmt_errormsg())) . '<br>';

    $r2 = db2_exec($conn, "UPDATE SYURLM SET FUURL = '', FUDESCU = '', FUTSUS = '', FUTSWS = '' WHERE FUID IN (" . $fList . ")");
    echo ($r2 ? '<span class="ok">OK</span>' : '<span class="err">ERR</span>');
    echo '  SYURLM.FUURL/FUDESCU/FUTSUS/FUTSWS = empty for 5 SG portals' . ($r2 ? ' (' . db2_num_rows($r2) . ' rows)' : ': ' . htmlspecialchars(db2_stmt_errormsg())) . '<br>';

    echo '<br><strong>Reverted. Now go test Configuration &gt; Portal.</strong>';
    echo '</body></html>';
    exit;
}

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Revert v8</title>
<style>body{font-family:monospace;font-size:12px;padding:14px;}
table{border-collapse:collapse;margin:6px 0;}td,th{border:1px solid #bbb;padding:2px 8px;}th{background:#eee;}
</style></head><body>';
echo '<h2>Revert sg_menu_fix v8 — current state</h2>';

echo '<b>SYPORT.FPRESV (should say empty if v8 ran, will restore to Y):</b><br>';
$r = db2_exec($conn, "SELECT FPPORT, FPRESV FROM SYPORT WHERE FPPORT IN (" . $pList . ") AND TRIM(FPPAGE)='' ORDER BY FPPORT");
echo '<table><tr><th>FPPORT</th><th>FPRESV now</th><th>Will restore to</th></tr>';
while ($row = db2_fetch_assoc($r))
    echo '<tr><td>'.trim($row['FPPORT']).'</td><td>'.htmlspecialchars(trim($row['FPRESV'])).'</td><td>Y</td></tr>';
echo '</table>';

echo '<b>SYURLM.FUURL (should show URL if v8 ran, will restore to empty):</b><br>';
$r2 = db2_exec($conn, "SELECT FUID, FUURL, FUDESCU, FUTSUS, FUTSWS FROM SYURLM WHERE FUID IN (" . $fList . ") ORDER BY FUID");
echo '<table><tr><th>FUID</th><th>FUURL now</th><th>Will restore to</th></tr>';
while ($row = db2_fetch_assoc($r2))
    echo '<tr><td>'.trim($row['FUID']).'</td><td>'.htmlspecialchars(trim($row['FUURL'])).'</td><td>(empty)</td></tr>';
echo '</table>';

echo '<br><form method="post"><button name="revert" value="1" type="submit"
    style="background:#c00;color:#fff;padding:8px 22px;font-size:14px;">
    Revert SYPORT + SYURLM to pre-fix state</button></form>';
echo '</body></html>';
?>
