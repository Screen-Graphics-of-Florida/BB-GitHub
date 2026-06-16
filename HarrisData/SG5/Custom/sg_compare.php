<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Role Compare</title>
<style>
body{font-family:monospace;font-size:11px;padding:12px;}
h2{color:#00c;border-bottom:2px solid #00c;margin:16px 0 4px;}
h3{color:#800;margin:12px 0 3px;}
table{border-collapse:collapse;margin:4px 0 12px;}
td,th{border:1px solid #bbb;padding:2px 7px;white-space:nowrap;}
th{background:#ddd;}
.r1{background:#e8f0ff;}
.r2{background:#fff8e8;}
.diff{background:#ffa;font-weight:bold;}
.miss{background:#fdd;font-style:italic;}
.sg{background:#efe;}
pre{margin:0;}
</style></head><body>';

echo '<h1>HD_ALL_SG vs CUSTSRVC — Full Comparison</h1>';
echo '<p><span style="background:#e8f0ff;padding:2px 6px;">Blue = HD_ALL_SG / BBUSCH</span> &nbsp;
      <span style="background:#fff8e8;padding:2px 6px;">Yellow = CUSTSRVC / SG5TEST</span> &nbsp;
      <span style="background:#ffa;padding:2px 6px;">Highlighted = DIFFERENT</span></p>';

// Helper: fetch all rows as array keyed by first column value
function fetchAll($conn, $sql) {
    $r = db2_exec($conn, $sql);
    if (!$r) return array('_err' => db2_stmt_errormsg());
    $rows = array();
    while ($row = db2_fetch_assoc($r)) $rows[] = $row;
    return $rows;
}

// -------------------------------------------------------
// 1. SYROLD — all portals for each role
// -------------------------------------------------------
echo '<h2>1. SYROLD — all portals assigned to each role</h2>';
$r1a = fetchAll($conn, "SELECT RDPORT, RDSEQN FROM SYROLD WHERE TRIM(RDROLE)='HD_ALL_SG' ORDER BY RDSEQN");
$r1b = fetchAll($conn, "SELECT RDPORT, RDSEQN FROM SYROLD WHERE TRIM(RDROLE)='CUSTSRVC' ORDER BY RDSEQN");
$ports_hd   = array(); foreach ($r1a as $r) $ports_hd[trim($r['RDPORT'])]   = $r['RDSEQN'];
$ports_cs   = array(); foreach ($r1b as $r) $ports_cs[trim($r['RDPORT'])]   = $r['RDSEQN'];
$all_ports  = array_unique(array_merge(array_keys($ports_hd), array_keys($ports_cs)));
sort($all_ports);
echo '<table><tr><th>RDPORT</th><th>HD_ALL_SG seq</th><th>CUSTSRVC seq</th></tr>';
foreach ($all_ports as $p) {
    $hd  = isset($ports_hd[$p]) ? $ports_hd[$p] : '';
    $cs  = isset($ports_cs[$p]) ? $ports_cs[$p] : '';
    $cls = ($hd !== '' && $cs === '') ? ' class="miss"' : (($hd === '' && $cs !== '') ? ' class="miss"' : '');
    $sg  = (substr($p,0,2)==='SG') ? ' style="background:#efe;font-weight:bold"' : '';
    echo '<tr><td'.$sg.'>'.htmlspecialchars($p).'</td>';
    echo '<td class="r1">'.$hd.'</td>';
    echo '<td class="r2"'.$cls.'>'.$cs.'</td></tr>';
}
echo '</table>';

// -------------------------------------------------------
// 2. SYROLM — role master rows
// -------------------------------------------------------
echo '<h2>2. SYROLM — role master rows</h2>';
$r2a = fetchAll($conn, "SELECT * FROM SYROLM WHERE TRIM(RMROLE)='HD_ALL_SG' ORDER BY 1,2");
$r2b = fetchAll($conn, "SELECT * FROM SYROLM WHERE TRIM(RMROLE)='CUSTSRVC' ORDER BY 1,2");

function showTable($rows, $cls) {
    if (empty($rows)) { echo '<em>(no rows)</em><br>'; return; }
    if (isset($rows['_err'])) { echo '<span style="color:red">'.htmlspecialchars($rows['_err']).'</span><br>'; return; }
    $cols = array_keys($rows[0]);
    echo '<table><tr>'; foreach ($cols as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
    foreach ($rows as $row) {
        echo '<tr class="'.$cls.'">'; foreach ($cols as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
    }
    echo '</table>';
}
echo '<b>HD_ALL_SG:</b><br>'; showTable($r2a, 'r1');
echo '<b>CUSTSRVC:</b><br>';  showTable($r2b, 'r2');

// -------------------------------------------------------
// 3. SYROLU — role URL rows
// -------------------------------------------------------
echo '<h2>3. SYROLU — role URL rows</h2>';
$r3a = fetchAll($conn, "SELECT * FROM SYROLU WHERE TRIM(RUROLE)='HD_ALL_SG' ORDER BY 1,2");
$r3b = fetchAll($conn, "SELECT * FROM SYROLU WHERE TRIM(RUROLE)='CUSTSRVC' ORDER BY 1,2");
echo '<b>HD_ALL_SG:</b><br>'; showTable($r3a, 'r1');
echo '<b>CUSTSRVC:</b><br>';  showTable($r3b, 'r2');

// -------------------------------------------------------
// 4. SYUSER — ALL columns for BBUSCH vs SG5TEST
// -------------------------------------------------------
echo '<h2>4. SYUSER — all columns BBUSCH vs SG5TEST</h2>';
$ru = db2_exec($conn, "SELECT * FROM SYUSER WHERE TRIM(SUSRID) IN ('BBUSCH','SG5TEST') ORDER BY SUSRID");
$nc = db2_num_fields($ru); $ucols = array();
for ($i=0;$i<$nc;$i++) $ucols[] = db2_field_name($ru,$i);
$urows = array();
while ($row = db2_fetch_assoc($ru)) $urows[trim($row['SUSRID'])] = $row;
echo '<table><tr><th>Column</th><th class="r1">BBUSCH</th><th class="r2">SG5TEST</th></tr>';
foreach ($ucols as $c) {
    $b = isset($urows['BBUSCH'])  ? trim((string)$urows['BBUSCH'][$c])  : '(missing)';
    $s = isset($urows['SG5TEST']) ? trim((string)$urows['SG5TEST'][$c]) : '(missing)';
    $diff = ($b !== $s) ? ' class="diff"' : '';
    echo '<tr'.$diff.'><td>'.$c.'</td><td class="r1">'.htmlspecialchars($b).'</td><td class="r2">'.htmlspecialchars($s).'</td></tr>';
}
echo '</table>';

// -------------------------------------------------------
// 5. SYPGMS — both users
// -------------------------------------------------------
echo '<h2>5. SYPGMS — program option security for both users</h2>';
$r5a = fetchAll($conn, "SELECT * FROM SYPGMS WHERE TRIM(SPUSER)='BBUSCH' ORDER BY SPPGID");
$r5b = fetchAll($conn, "SELECT * FROM SYPGMS WHERE TRIM(SPUSER)='SG5TEST' ORDER BY SPPGID");
echo '<b>BBUSCH ('.count($r5a).' rows):</b><br>'; showTable($r5a, 'r1');
echo '<b>SG5TEST ('.count($r5b).' rows):</b><br>'; showTable($r5b, 'r2');

// -------------------------------------------------------
// 6. SYPORT — ALL columns for SG portals vs a working HD_ALL_SG portal
//    Find a portal in HD_ALL_SG that is NOT in CUSTSRVC
// -------------------------------------------------------
echo '<h2>6. SYPORT — SG portal headers (all columns) after latest fix</h2>';
$r6 = db2_exec($conn, "SELECT * FROM SYPORT WHERE FPPORT IN ('SGINQ','SGDASH','SGRPT','SGDINT','SGSOP') AND TRIM(FPPAGE)='' ORDER BY FPPORT");
$nc = db2_num_fields($r6); $sc = array();
for ($i=0;$i<$nc;$i++) $sc[] = db2_field_name($r6,$i);
echo '<table><tr>'; foreach ($sc as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
while ($row = db2_fetch_assoc($r6)) {
    echo '<tr class="sg">'; foreach ($sc as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
}
echo '</table>';

// -------------------------------------------------------
// 7. SYURLM — SG header rows (all columns)
// -------------------------------------------------------
echo '<h2>7. SYURLM — SG portal header rows (all columns) after latest fix</h2>';
$fList = "'SGINQ/PORTAL','SGDASH/PORTAL','SGDINT/PORTAL','SGRPT/PORTAL','SGSOP/PORTAL'";
$r7 = db2_exec($conn, "SELECT * FROM SYURLM WHERE FUID IN (".$fList.") ORDER BY FUID");
$nc = db2_num_fields($r7); $uc = array();
for ($i=0;$i<$nc;$i++) $uc[] = db2_field_name($r7,$i);
echo '<table><tr>'; foreach ($uc as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
while ($row = db2_fetch_assoc($r7)) {
    echo '<tr class="sg">'; foreach ($uc as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
}
echo '</table>';

// -------------------------------------------------------
// 8. Full SYROLD — compare every field for SG portals
// -------------------------------------------------------
echo '<h2>8. SYROLD — all columns for SG portals (both roles)</h2>';
$r8 = db2_exec($conn, "SELECT * FROM SYROLD WHERE TRIM(RDPORT) IN ('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP') AND TRIM(RDROLE) IN ('HD_ALL_SG','CUSTSRVC') ORDER BY RDPORT,RDROLE");
$nc = db2_num_fields($r8); $rc = array();
for ($i=0;$i<$nc;$i++) $rc[] = db2_field_name($r8,$i);
echo '<table><tr>'; foreach ($rc as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
while ($row = db2_fetch_assoc($r8)) {
    $cls = trim($row['RDROLE'])==='HD_ALL_SG' ? 'r1' : 'r2';
    echo '<tr class="'.$cls.'">'; foreach ($rc as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
}
echo '</table>';

// -------------------------------------------------------
// 9. All SY* tables that contain either role name
// -------------------------------------------------------
echo '<h2>9. All SY* tables referencing HD_ALL_SG or CUSTSRVC</h2>';
$tabR = db2_exec($conn, "SELECT TABLE_NAME FROM QSYS2.SYSTABLES WHERE TABLE_SCHEMA='S5HDSDATA' AND TABLE_NAME LIKE 'SY%' ORDER BY TABLE_NAME");
echo '<table><tr><th>Table</th><th>HD_ALL_SG rows</th><th>CUSTSRVC rows</th><th>Notes</th></tr>';
while ($tabRow = db2_fetch_assoc($tabR)) {
    $tbl = $tabRow['TABLE_NAME'];
    $colR = db2_exec($conn, "SELECT COLUMN_NAME FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA='S5HDSDATA' AND TABLE_NAME='".$tbl."' AND (COLUMN_NAME LIKE '%ROLE%' OR COLUMN_NAME LIKE '%RLID%' OR COLUMN_NAME LIKE '%RID%') FETCH FIRST 1 ROWS ONLY");
    if (!$colR) continue;
    $colRow = db2_fetch_assoc($colR);
    if (!$colRow) continue;
    $col = $colRow['COLUMN_NAME'];
    $cntHD = db2_fetch_assoc(db2_exec($conn, "SELECT COUNT(*) AS N FROM ".$tbl." WHERE TRIM(".$col.")='HD_ALL_SG'"));
    $cntCS = db2_fetch_assoc(db2_exec($conn, "SELECT COUNT(*) AS N FROM ".$tbl." WHERE TRIM(".$col.")='CUSTSRVC'"));
    $hdn = $cntHD ? $cntHD['N'] : '?';
    $csn = $cntCS ? $cntCS['N'] : '?';
    $note = ($hdn != $csn) ? 'DIFFERENT' : '';
    $diff = ($note !== '') ? ' class="diff"' : '';
    echo '<tr'.$diff.'><td>'.$tbl.'</td><td class="r1">'.$hdn.'</td><td class="r2">'.$csn.'</td><td>'.$note.' (col:'.$col.')</td></tr>';
}
echo '</table>';

echo '</body></html>';
?>
