<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Menu Diag</title>
<style>body{font-family:monospace;font-size:11px;padding:12px;}
h2{color:#00f;margin:14px 0 4px;}h3{color:#800;margin:10px 0 3px;}
table{border-collapse:collapse;margin-bottom:8px;}
td,th{border:1px solid #ccc;padding:2px 6px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#ffa;font-weight:bold;}
</style></head><body>';

echo '<h2>Menu Diagnostic</h2>';

// 1. Compare SYPORT header rows: existing working portal vs SG portals
echo '<h2>1. SYPORT header rows (FPPAGE empty) for key portals</h2>';
$sql = "SELECT FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU
        FROM SYPORT
        WHERE TRIM(FPPAGE) = ''
        AND FPPORT IN ('CUSTOMER','SGINQ','SGDASH','SGDINT','SGRPT','SGSOP')
        ORDER BY FPPORT";
$r = db2_exec($conn, $sql);
$cols = array('FPPORT','FPPAGE','FPSEQ','FPID','FPDESC','FPTITL','FPRESV','FPDESCU');
echo '<table><tr>'; foreach ($cols as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
while ($row = db2_fetch_assoc($r)) {
    $sg = substr($row['FPPORT'],0,2) === 'SG';
    echo '<tr>';
    foreach ($cols as $c) {
        $v = trim((string)$row[$c]);
        echo '<td' . ($sg ? ' class="hi"' : '') . '>' . htmlspecialchars($v) . '</td>';
    }
    echo '</tr>';
}
echo '</table>';

// 2. Compare SYURLM entries
echo '<h2>2. SYURLM entries for same portals</h2>';
$sql = "SELECT FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV
        FROM SYURLM
        WHERE FUID IN ('CUSTOMER','SGINQ','SGDASH','SGDINT','SGRPT','SGSOP')
        ORDER BY FUID";
$r = db2_exec($conn, $sql);
$cols2 = array('FUID','FUDESC','FUTITL','FUTRGT','FUURL','FUIMG','FURESV');
echo '<table><tr>'; foreach ($cols2 as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
while ($row = db2_fetch_assoc($r)) {
    $sg = substr($row['FUID'],0,2) === 'SG';
    echo '<tr>';
    foreach ($cols2 as $c) {
        $v = trim((string)$row[$c]);
        echo '<td' . ($sg ? ' class="hi"' : '') . '>' . htmlspecialchars($v) . '</td>';
    }
    echo '</tr>';
}
echo '</table>';

// 3. SYROLD for CUSTSRVC
echo '<h2>3. SYROLD for CUSTSRVC (all portals, ordered by seq)</h2>';
$sql = "SELECT RDROLE,RDPORT,RDSEQN FROM SYROLD WHERE RDROLE='CUSTSRVC' ORDER BY RDSEQN";
$r = db2_exec($conn, $sql);
echo '<table><tr><th>RDROLE</th><th>RDPORT</th><th>RDSEQN</th></tr>';
while ($row = db2_fetch_assoc($r)) {
    $sg = substr(trim($row['RDPORT']),0,2) === 'SG';
    echo '<tr' . ($sg ? ' class="hi"' : '') . '><td>' . htmlspecialchars(trim($row['RDROLE'])) . '</td><td>' . htmlspecialchars(trim($row['RDPORT'])) . '</td><td>' . $row['RDSEQN'] . '</td></tr>';
}
echo '</table>';

// 4. SYPGMO for SG portals
echo '<h2>4. SYPGMO for SG portals</h2>';
$sql = "SELECT SOPGID,SOMOPT,SOMDES,SORESV FROM SYPGMO WHERE SOPGID IN ('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP','CUSTOMER') ORDER BY SOPGID,SOMOPT";
$r = db2_exec($conn, $sql);
echo '<table><tr><th>SOPGID</th><th>SOMOPT</th><th>SOMDES</th><th>SORESV</th></tr>';
while ($row = db2_fetch_assoc($r)) {
    $sg = substr(trim($row['SOPGID']),0,2) === 'SG';
    echo '<tr' . ($sg ? ' class="hi"' : '') . '><td>' . htmlspecialchars(trim($row['SOPGID'])) . '</td><td>' . $row['SOMOPT'] . '</td><td>' . htmlspecialchars(trim($row['SOMDES'])) . '</td><td>' . htmlspecialchars(trim($row['SORESV'])) . '</td></tr>';
}
echo '</table>';

// 5. Full SYPORT column list for one SG vs one existing
echo '<h2>5. ALL columns — SYPORT row for CUSTOMER vs SGINQ header</h2>';
$sql = "SELECT * FROM SYPORT WHERE FPPORT IN ('CUSTOMER','SGINQ') AND TRIM(FPPAGE) = '' ORDER BY FPPORT";
$r = db2_exec($conn, $sql);
$nc = db2_num_fields($r); $cols3 = array();
for ($i=0;$i<$nc;$i++) $cols3[] = db2_field_name($r,$i);
echo '<table><tr><th>Field</th><th>CUSTOMER</th><th>SGINQ</th></tr>';
$rows3 = array();
while ($row = db2_fetch_assoc($r)) $rows3[trim($row['FPPORT'])] = $row;
foreach ($cols3 as $c) {
    $cust = isset($rows3['CUSTOMER']) ? trim((string)$rows3['CUSTOMER'][$c]) : '';
    $sginq = isset($rows3['SGINQ'])   ? trim((string)$rows3['SGINQ'][$c])   : '';
    $diff = ($cust !== $sginq) ? ' class="hi"' : '';
    echo '<tr' . $diff . '><td>'.$c.'</td><td>'.htmlspecialchars($cust).'</td><td>'.htmlspecialchars($sginq).'</td></tr>';
}
echo '</table>';

// 6. ALL columns — SYURLM row for CUSTOMER vs SGINQ
echo '<h2>6. ALL columns — SYURLM row for CUSTOMER vs SGINQ</h2>';
$sql = "SELECT * FROM SYURLM WHERE FUID IN ('CUSTOMER','SGINQ') ORDER BY FUID";
$r = db2_exec($conn, $sql);
$nc = db2_num_fields($r); $cols4 = array();
for ($i=0;$i<$nc;$i++) $cols4[] = db2_field_name($r,$i);
echo '<table><tr><th>Field</th><th>CUSTOMER</th><th>SGINQ</th></tr>';
$rows4 = array();
while ($row = db2_fetch_assoc($r)) $rows4[trim($row['FUID'])] = $row;
foreach ($cols4 as $c) {
    $cust  = isset($rows4['CUSTOMER']) ? trim((string)$rows4['CUSTOMER'][$c]) : '';
    $sginq = isset($rows4['SGINQ'])    ? trim((string)$rows4['SGINQ'][$c])    : '';
    $diff  = ($cust !== $sginq) ? ' class="hi"' : '';
    echo '<tr' . $diff . '><td>'.$c.'</td><td>'.htmlspecialchars($cust).'</td><td>'.htmlspecialchars($sginq).'</td></tr>';
}
echo '</table>';

echo '</body></html>';
?>
