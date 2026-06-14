<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

$pList  = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";
$roles  = array('HD_ALL_SG','CUSTSRVC','ACCOUNTING','HD_ALL');

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SYPORR Compare</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 6px;white-space:nowrap;}
th{background:#ddd;}.hdr{background:#dfd;}.sub{background:#eff;}
.miss{background:#fdd;font-weight:bold;}.ok{color:green;font-weight:bold;}
</style></head><body><h1>SYPORR Comparison</h1>';

foreach ($roles as $role) {
    echo '<h2>SYPORR for '.$role.' -- SG portals</h2>';
    $r = db2_exec($conn,
        "SELECT PRPORT,PRPAGE,PRSEQ,PRID,PRRESV"
        . " FROM S5HDSDATA.SYPORR"
        . " WHERE TRIM(PRROLE)='$role' AND PRPORT IN ($pList)"
        . " ORDER BY PRPORT,PRPAGE,PRSEQ");
    echo '<table><tr><th>PRPORT</th><th>PRPAGE</th><th>PRSEQ</th>'
       . '<th>PRID</th><th>PRRESV</th></tr>';
    $cnt = 0;
    if ($r) {
        while ($row = db2_fetch_assoc($r)) {
            $pg  = trim($row['PRPAGE']);
            $cls = ($pg === '') ? 'hdr' : 'sub';
            echo '<tr class="'.$cls.'">'
               . '<td>'.htmlspecialchars(trim($row['PRPORT'])).'</td>'
               . '<td>'.htmlspecialchars($pg === '' ? '(header)' : $pg).'</td>'
               . '<td>'.htmlspecialchars(trim($row['PRSEQ'])).'</td>'
               . '<td>'.htmlspecialchars(trim($row['PRID'])).'</td>'
               . '<td>'.htmlspecialchars(trim($row['PRRESV'])).'</td></tr>';
            $cnt++;
        }
    } else {
        echo '<tr><td colspan="5" style="color:red">'.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
    }
    echo '</table><p>'.$cnt.' rows (need 35: 5 portal headers + 30 sub-pages)</p>';
}

echo '<h2>SYPORT sub-page rows for SG portals</h2>';
$r = db2_exec($conn,
    "SELECT FPPORT,FPPAGE,FPSEQ,FPID,FPDESC"
    . " FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pList)"
    . " ORDER BY FPPORT,FPSEQ");
echo '<table><tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPID</th><th>FPDESC</th></tr>';
$cnt = 0;
if ($r) while ($row = db2_fetch_assoc($r)) {
    $pg  = trim($row['FPPAGE']);
    $cls = ($pg === '') ? 'hdr' : 'sub';
    echo '<tr class="'.$cls.'">'
       . '<td>'.htmlspecialchars(trim($row['FPPORT'])).'</td>'
       . '<td>'.htmlspecialchars($pg === '' ? '(header)' : $pg).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FPSEQ'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FPID'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FPDESC'])).'</td></tr>';
    $cnt++;
}
echo '</table><p>'.$cnt.' rows</p>';

echo '<h2>SYROLD for HD_ALL_SG</h2>';
$r = db2_exec($conn,
    "SELECT RDROLE,RDPORT,RDSEQN FROM S5HDSDATA.SYROLD"
    . " WHERE TRIM(RDROLE)='HD_ALL_SG' ORDER BY RDSEQN");
echo '<table><tr><th>RDROLE</th><th>RDPORT</th><th>RDSEQN</th></tr>';
$cnt = 0;
if ($r) while ($row = db2_fetch_assoc($r)) {
    echo '<tr><td>'.htmlspecialchars(trim($row['RDROLE'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['RDPORT'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['RDSEQN'])).'</td></tr>';
    $cnt++;
}
echo '</table><p>'.$cnt.' rows</p>';

echo '<h2>SYURLM -- SG portal headers (FUID ending /PORTAL)</h2>';
$r = db2_exec($conn,
    "SELECT FUID,FUTRGT,SUBSTR(FUURL,1,80) AS URL,FURESV"
    . " FROM S5HDSDATA.SYURLM WHERE FUID LIKE '%/PORTAL' AND FUID LIKE 'SG%'"
    . " ORDER BY FUID");
echo '<table><tr><th>FUID</th><th>FUTRGT</th><th>FUURL(80)</th><th>FURESV</th></tr>';
$cnt = 0;
if ($r) while ($row = db2_fetch_assoc($r)) {
    $nourl = (trim($row['URL']) === '');
    echo '<tr'.($nourl ? ' style="background:#ffc;"' : '').'>'
       . '<td>'.htmlspecialchars(trim($row['FUID'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FUTRGT'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['URL'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FURESV'])).'</td></tr>';
    $cnt++;
}
echo '</table><p>'.$cnt.' portal header SYURLM rows</p>';

echo '</body></html>';
?>
