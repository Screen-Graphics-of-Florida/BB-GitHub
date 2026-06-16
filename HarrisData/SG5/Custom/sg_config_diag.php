<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Config Diag</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 7px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#ffa;font-weight:bold;}
</style></head><body>';

echo '<h1>Configuration/Portal Diagnostic</h1>';

// 1. Find SYURLM entry that links to hdList.php?tblID=199
echo '<h2>1. SYURLM — any entry with tblID=199</h2>';
$r = db2_exec($conn, "SELECT * FROM SYURLM WHERE FUURL LIKE '%tblID=199%' OR FUURL LIKE '%tblid=199%'");
$nc = db2_num_fields($r); $c = array();
for ($i=0;$i<$nc;$i++) $c[] = db2_field_name($r,$i);
echo '<table><tr>'; foreach ($c as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt = 0;
while ($row = db2_fetch_assoc($r)) {
    echo '<tr>'; foreach ($c as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt++;
}
if ($cnt === 0) echo '<tr><td colspan="'.count($c).'"><em>No SYURLM rows with tblID=199</em></td></tr>';
echo '</table>';

// 2. SYPORT + SYURLM for CONFIGURATION portal
echo '<h2>2. SYPORT — CONFIGURATION portal (all sub-pages)</h2>';
$r2 = db2_exec($conn, "SELECT * FROM SYPORT WHERE TRIM(FPPORT)='CONFIGURATION' ORDER BY FPSEQ");
$nc2 = db2_num_fields($r2); $c2 = array();
for ($i=0;$i<$nc2;$i++) $c2[] = db2_field_name($r2,$i);
echo '<table><tr>'; foreach ($c2 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
while ($row = db2_fetch_assoc($r2)) {
    echo '<tr>'; foreach ($c2 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
}
echo '</table>';

echo '<h2>3. SYURLM — CONFIGURATION portal entries</h2>';
$r3 = db2_exec($conn, "SELECT * FROM SYURLM WHERE FUID LIKE 'CONFIGURATION%' OR FUID LIKE 'CONFIG%' ORDER BY FUID");
$nc3 = db2_num_fields($r3); $c3 = array();
for ($i=0;$i<$nc3;$i++) $c3[] = db2_field_name($r3,$i);
echo '<table><tr>'; foreach ($c3 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
while ($row = db2_fetch_assoc($r3)) {
    echo '<tr>'; foreach ($c3 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
}
echo '</table>';

// 4. SYPGMO for CONFIGURATION
echo '<h2>4. SYPGMO — CONFIGURATION entries</h2>';
$r4 = db2_exec($conn, "SELECT * FROM SYPGMO WHERE SOPGID LIKE 'CONFIG%' ORDER BY SOPGID, SOMOPT");
$nc4 = db2_num_fields($r4); $c4 = array();
for ($i=0;$i<$nc4;$i++) $c4[] = db2_field_name($r4,$i);
echo '<table><tr>'; foreach ($c4 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt4 = 0;
while ($row = db2_fetch_assoc($r4)) {
    echo '<tr>'; foreach ($c4 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt4++;
}
if ($cnt4 === 0) echo '<tr><td colspan="'.count($c4).'"><em>No rows</em></td></tr>';
echo '</table>';

// 5. Check SYPORR for CONFIGURATION entries for CUSTSRVC
echo '<h2>5. SYPORR — CONFIGURATION portal for CUSTSRVC</h2>';
$r5 = db2_exec($conn, "SELECT * FROM SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' AND PRPORT LIKE 'CONFIG%'");
$nc5 = db2_num_fields($r5); $c5 = array();
for ($i=0;$i<$nc5;$i++) $c5[] = db2_field_name($r5,$i);
echo '<table><tr>'; foreach ($c5 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt5 = 0;
while ($row = db2_fetch_assoc($r5)) {
    echo '<tr>'; foreach ($c5 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt5++;
}
if ($cnt5 === 0) echo '<tr><td colspan="'.count($c5).'"><em>No rows</em></td></tr>';
echo '</table>';

// 6. SYPORR columns (full structure)
echo '<h2>6. SYPORR table — all columns</h2>';
$r6 = db2_exec($conn, "SELECT COLUMN_NAME, DATA_TYPE, LENGTH FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA='S5HDSDATA' AND TABLE_NAME='SYPORR' ORDER BY ORDINAL_POSITION");
echo '<table><tr><th>Column</th><th>Type</th><th>Len</th></tr>';
while ($row = db2_fetch_assoc($r6))
    echo '<tr><td>'.$row['COLUMN_NAME'].'</td><td>'.$row['DATA_TYPE'].'</td><td>'.$row['LENGTH'].'</td></tr>';
echo '</table>';

// 7. Sample 5 SYPORR rows for CUSTSRVC to see full structure
echo '<h2>7. SYPORR — 5 sample rows for CUSTSRVC (full columns)</h2>';
$r7 = db2_exec($conn, "SELECT * FROM SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' ORDER BY PRPORT FETCH FIRST 5 ROWS ONLY");
$nc7 = db2_num_fields($r7); $c7 = array();
for ($i=0;$i<$nc7;$i++) $c7[] = db2_field_name($r7,$i);
echo '<table><tr>'; foreach ($c7 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
while ($row = db2_fetch_assoc($r7)) {
    echo '<tr>'; foreach ($c7 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
}
echo '</table>';

// 8. What SYPORR rows exist for CUSTSRVC containing 'SG' in port name
echo '<h2>8. SYPORR — CUSTSRVC rows with SG portal codes</h2>';
$r8 = db2_exec($conn, "SELECT * FROM SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' AND (PRPORT LIKE 'SG%' OR PRPORT LIKE '%SG%')");
$nc8 = db2_num_fields($r8); $c8 = array();
for ($i=0;$i<$nc8;$i++) $c8[] = db2_field_name($r8,$i);
echo '<table><tr>'; foreach ($c8 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt8 = 0;
while ($row = db2_fetch_assoc($r8)) {
    echo '<tr>'; foreach ($c8 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt8++;
}
if ($cnt8 === 0) echo '<tr><td colspan="'.count($c8).'"><em>No SG rows in SYPORR for CUSTSRVC</em></td></tr>';
echo '</table>';

echo '</body></html>';
?>
