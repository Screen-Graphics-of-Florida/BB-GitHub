<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>hdList Diag</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 7px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#ffa;font-weight:bold;}
</style></head><body><h1>hdList Design Page 199 Diagnostic</h1>';

// 1. All SY* tables that have a numeric column - look for tblID=199
echo '<h2>1. SYPGMO — any row where SOMOPT=199</h2>';
$r = db2_exec($conn, "SELECT * FROM SYPGMO WHERE SOMOPT = 199");
$nc = db2_num_fields($r); $c = array();
for ($i=0;$i<$nc;$i++) $c[] = db2_field_name($r,$i);
echo '<table><tr>'; foreach ($c as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt = 0;
while ($row = db2_fetch_assoc($r)) {
    echo '<tr>'; foreach ($c as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt++;
}
if ($cnt === 0) echo '<tr><td colspan="'.count($c).'"><em>No rows with SOMOPT=199</em></td></tr>';
echo '</table>';

// 2. SYPGMO — HDLIST and PORTAL entries
echo '<h2>2. SYPGMO — rows for HDLIST or PORTAL</h2>';
$r2 = db2_exec($conn, "SELECT * FROM SYPGMO WHERE TRIM(SOPGID) IN ('HDLIST','PORTAL','HDLIST/PORTAL') OR TRIM(SOPGID) LIKE 'HDLIST%' ORDER BY SOPGID,SOMOPT");
$nc2 = db2_num_fields($r2); $c2 = array();
for ($i=0;$i<$nc2;$i++) $c2[] = db2_field_name($r2,$i);
echo '<table><tr>'; foreach ($c2 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt2 = 0;
while ($row = db2_fetch_assoc($r2)) {
    echo '<tr>'; foreach ($c2 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt2++;
}
if ($cnt2 === 0) echo '<tr><td colspan="'.count($c2).'"><em>No rows</em></td></tr>';
echo '</table>';

// 3. All SY* tables — look for column named TBLID or similar
echo '<h2>3. Tables in S5HDSDATA with a TBLID column</h2>';
$r3 = db2_exec($conn, "SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, LENGTH FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA='S5HDSDATA' AND (COLUMN_NAME LIKE '%TBLID%' OR COLUMN_NAME LIKE '%TBL_ID%' OR COLUMN_NAME = 'HDTBL') ORDER BY TABLE_NAME, COLUMN_NAME");
$cnt3 = 0;
echo '<table><tr><th>TABLE</th><th>COLUMN</th><th>TYPE</th><th>LEN</th></tr>';
while ($row = db2_fetch_assoc($r3)) {
    echo '<tr><td>'.htmlspecialchars($row['TABLE_NAME']).'</td><td>'.$row['COLUMN_NAME'].'</td><td>'.$row['DATA_TYPE'].'</td><td>'.$row['LENGTH'].'</td></tr>';
    $cnt3++;
}
if ($cnt3 === 0) echo '<tr><td colspan="4"><em>No TBLID columns found in S5HDSDATA</em></td></tr>';
echo '</table>';

// 4. Look for a SYDESIGN or design-page table
echo '<h2>4. Tables matching DESIGN or DSGPG or DPAG</h2>';
$r4 = db2_exec($conn, "SELECT TABLE_NAME FROM QSYS2.SYSTABLES WHERE TABLE_SCHEMA='S5HDSDATA' AND (TABLE_NAME LIKE '%DESIGN%' OR TABLE_NAME LIKE '%DSGPG%' OR TABLE_NAME LIKE '%DPAG%' OR TABLE_NAME LIKE '%HDLST%' OR TABLE_NAME LIKE 'HDLST%') ORDER BY TABLE_NAME");
$cnt4 = 0;
echo '<table><tr><th>TABLE_NAME</th></tr>';
while ($row = db2_fetch_assoc($r4)) {
    echo '<tr><td>'.htmlspecialchars($row['TABLE_NAME']).'</td></tr>';
    $cnt4++;
}
if ($cnt4 === 0) echo '<tr><td><em>None found</em></td></tr>';
echo '</table>';

// 5. SYPORT — HDLIST portal entries
echo '<h2>5. SYPORT — HDLIST portal (all rows)</h2>';
$r5 = db2_exec($conn, "SELECT * FROM SYPORT WHERE TRIM(FPPORT)='HDLIST' ORDER BY FPSEQ");
$nc5 = db2_num_fields($r5); $c5 = array();
for ($i=0;$i<$nc5;$i++) $c5[] = db2_field_name($r5,$i);
echo '<table><tr>'; foreach ($c5 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt5 = 0;
while ($row = db2_fetch_assoc($r5)) {
    echo '<tr>'; foreach ($c5 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt5++;
}
if ($cnt5 === 0) echo '<tr><td colspan="'.count($c5).'"><em>No HDLIST rows in SYPORT</em></td></tr>';
echo '</table>';

// 6. Recently modified SYURLM rows (anything touched after June 1 2026)
echo '<h2>6. SYURLM — rows modified after 2026-06-01 (my changes)</h2>';
$r6 = db2_exec($conn, "SELECT FUID, FUDESC, FUURL, FUTSUS, FUTSTP FROM SYURLM WHERE FUTSTP > '2026-06-01-00.00.00.000000' ORDER BY FUTSTP DESC FETCH FIRST 30 ROWS ONLY");
$nc6 = db2_num_fields($r6); $c6 = array();
for ($i=0;$i<$nc6;$i++) $c6[] = db2_field_name($r6,$i);
echo '<table><tr>'; foreach ($c6 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt6 = 0;
while ($row = db2_fetch_assoc($r6)) {
    echo '<tr class="hi">'; foreach ($c6 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt6++;
}
if ($cnt6 === 0) echo '<tr><td colspan="'.count($c6).'"><em>No rows modified after June 1 2026</em></td></tr>';
echo '</table>';

// 7. Recently modified SYPORT rows
echo '<h2>7. SYPORT — rows modified after 2026-06-01 (my changes)</h2>';
$r7 = db2_exec($conn, "SELECT FPPORT, FPPAGE, FPID, FPRESV, FPTSUS, FPTSTP FROM SYPORT WHERE FPTSTP > '2026-06-01-00.00.00.000000' ORDER BY FPTSTP DESC FETCH FIRST 30 ROWS ONLY");
$nc7 = db2_num_fields($r7); $c7 = array();
for ($i=0;$i<$nc7;$i++) $c7[] = db2_field_name($r7,$i);
echo '<table><tr>'; foreach ($c7 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt7 = 0;
while ($row = db2_fetch_assoc($r7)) {
    echo '<tr class="hi">'; foreach ($c7 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt7++;
}
if ($cnt7 === 0) echo '<tr><td colspan="'.count($c7).'"><em>No rows modified after June 1 2026</em></td></tr>';
echo '</table>';

// 8. Check SYPORT for HDLIST/PORTAL as an FPID
echo '<h2>8. SYPORT — row with FPID = HDLIST/PORTAL</h2>';
$r8 = db2_exec($conn, "SELECT * FROM SYPORT WHERE TRIM(FPID)='HDLIST/PORTAL'");
$nc8 = db2_num_fields($r8); $c8 = array();
for ($i=0;$i<$nc8;$i++) $c8[] = db2_field_name($r8,$i);
echo '<table><tr>'; foreach ($c8 as $col) echo '<th>'.$col.'</th>'; echo '</tr>';
$cnt8 = 0;
while ($row = db2_fetch_assoc($r8)) {
    echo '<tr>'; foreach ($c8 as $col) echo '<td>'.htmlspecialchars(trim((string)$row[$col])).'</td>'; echo '</tr>';
    $cnt8++;
}
if ($cnt8 === 0) echo '<tr><td colspan="'.count($c8).'"><em>No SYPORT row with FPID=HDLIST/PORTAL</em></td></tr>';
echo '</table>';

echo '</body></html>';
?>
