<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SYPGMS Inspector</title>
<style>body{font-family:monospace;font-size:11px;padding:16px;}
h3{color:#00c;margin:12px 0 4px;}
table{border-collapse:collapse;margin:6px 0;}td,th{border:1px solid #ccc;padding:2px 6px;}th{background:#ddd;}
.hi{background:#ffa;font-weight:bold;}
</style></head><body>';
echo '<h2>SYPGMS / SYPGMO Inspector</h2>';

// 1. Show ALL columns of SYPGMS for SG5TEST
echo '<h3>1. SYPGMS — all rows for SG5TEST (all columns)</h3>';
$r = db2_exec($conn, "SELECT * FROM SYPGMS WHERE TRIM(SPUSER) = 'SG5TEST' ORDER BY 1,2,3");
if ($r) {
    $nc = db2_num_fields($r); $cols = array();
    for ($i=0; $i<$nc; $i++) $cols[] = db2_field_name($r, $i);
    echo '<table><tr>'; foreach ($cols as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
    $count = 0;
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr>'; foreach ($cols as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
        $count++;
    }
    if ($count === 0) echo '<tr><td colspan="'.count($cols).'"><em>No rows</em></td></tr>';
    echo '</table><p>' . $count . ' row(s)</p>';
} else {
    echo '<p style="color:red">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';
}

// 2. Show a WORKING example from SYPGMS (not SG5TEST) for comparison
echo '<h3>2. SYPGMS — sample rows for a working portal (CUSTOMER, MFGMGMT, SG_DOCUMENTATION)</h3>';
$r2 = db2_exec($conn, "SELECT * FROM SYPGMS WHERE TRIM(SPUSER) = 'BBUSCH' FETCH FIRST 10 ROWS ONLY");
if ($r2) {
    $nc = db2_num_fields($r2); $cols2 = array();
    for ($i=0; $i<$nc; $i++) $cols2[] = db2_field_name($r2, $i);
    echo '<table><tr>'; foreach ($cols2 as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
    $cnt = 0;
    while ($row = db2_fetch_assoc($r2)) {
        echo '<tr>'; foreach ($cols2 as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
        $cnt++;
    }
    if ($cnt === 0) echo '<tr><td colspan="'.count($cols2).'"><em>No rows for BBUSCH</em></td></tr>';
    echo '</table>';
}

// 3. Current SYPGMO state for SG portals
echo '<h3>3. SYPGMO — current state for SG portals (we deleted these)</h3>';
$r3 = db2_exec($conn, "SELECT * FROM SYPGMO WHERE SOPGID IN ('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP','SGINQ/PORTAL','SGDASH/PORTAL','SGDINT/PORTAL','SGRPT/PORTAL','SGSOP/PORTAL') ORDER BY SOPGID,SOMOPT");
if ($r3) {
    $nc = db2_num_fields($r3); $cols3 = array();
    for ($i=0; $i<$nc; $i++) $cols3[] = db2_field_name($r3, $i);
    echo '<table><tr>'; foreach ($cols3 as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
    $cnt3 = 0;
    while ($row = db2_fetch_assoc($r3)) {
        echo '<tr>'; foreach ($cols3 as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
        $cnt3++;
    }
    if ($cnt3 === 0) echo '<tr><td colspan="'.count($cols3).'"><em>No rows — SYPGMO entries were deleted</em></td></tr>';
    echo '</table>';
}

// 4. SYPGMO for a known working portal (SG_DOCUMENTATION)
echo '<h3>4. SYPGMO — SG_DOCUMENTATION (working) for comparison</h3>';
$r4 = db2_exec($conn, "SELECT * FROM SYPGMO WHERE TRIM(SOPGID) = 'SG_DOCUMENTATION' ORDER BY SOMOPT");
if ($r4) {
    $nc = db2_num_fields($r4); $cols4 = array();
    for ($i=0; $i<$nc; $i++) $cols4[] = db2_field_name($r4, $i);
    echo '<table><tr>'; foreach ($cols4 as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
    $cnt4 = 0;
    while ($row = db2_fetch_assoc($r4)) {
        echo '<tr>'; foreach ($cols4 as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
        $cnt4++;
    }
    if ($cnt4 === 0) echo '<tr><td colspan="'.count($cols4).'"><em>No rows — SG_DOCUMENTATION not in SYPGMO</em></td></tr>';
    echo '</table>';
}

echo '</body></html>';
?>
