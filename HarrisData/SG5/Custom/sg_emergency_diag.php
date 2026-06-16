<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();
$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Emergency Diag</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 6px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#dfd;}.miss{background:#ffe;font-weight:bold;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
</style></head><body><h1>Emergency State Check</h1>';

// 1. SYPORT: SG5 vs Live
echo '<h2>1. SYPORT — SG5 vs Live</h2>';
echo '<table><tr><th>Source</th><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPRESV</th></tr>';
foreach (array('S5HDSDATA'=>'SG5','SGHDSDATA'=>'Live') as $schema=>$label) {
    $r = db2_exec($conn,"SELECT FPPORT,FPPAGE,FPSEQ,FPRESV FROM ".$schema.".SYPORT WHERE FPPORT IN (".$pList.") ORDER BY FPPORT,FPSEQ");
    $cnt=0;
    if ($r) while ($row=db2_fetch_assoc($r)) {
        echo '<tr class="hi"><td>'.$label.'</td><td>'.htmlspecialchars(trim($row['FPPORT'])).'</td><td>'.htmlspecialchars(trim($row['FPPAGE'])).'</td><td>'.htmlspecialchars(trim($row['FPSEQ'])).'</td><td>'.htmlspecialchars(trim($row['FPRESV'])).'</td></tr>';
        $cnt++;
    } else echo '<tr><td colspan="5" class="err">'.$label.' query failed: '.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
    if ($cnt===0) echo '<tr><td colspan="5" class="miss">'.$label.': 0 rows found!</td></tr>';
}
echo '</table>';

// 2. SYROLD for SG portals (HD_ALL_SG + BBUSCH-relevant roles)
echo '<h2>2. SYROLD — SG portals (all roles)</h2>';
$r = db2_exec($conn,"SELECT RDROLE,RDPORT,RDSEQN FROM S5HDSDATA.SYROLD WHERE RDPORT IN (".$pList.") ORDER BY RDPORT,RDROLE");
echo '<table><tr><th>RDROLE</th><th>RDPORT</th><th>RDSEQN</th></tr>';
$cnt=0;
if ($r) while ($row=db2_fetch_assoc($r)) {
    echo '<tr><td>'.htmlspecialchars(trim($row['RDROLE'])).'</td><td>'.htmlspecialchars(trim($row['RDPORT'])).'</td><td>'.htmlspecialchars(trim($row['RDSEQN'])).'</td></tr>';
    $cnt++;
} else echo '<tr><td colspan="3" class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
if ($cnt===0) echo '<tr><td colspan="3" class="miss">0 rows — SYROLD has no SG portal entries!</td></tr>';
echo '</table><p>'.$cnt.' rows</p>';

// 3. SYROLD Live for SG portals
echo '<h2>3. SYROLD Live — SG portals</h2>';
$r = db2_exec($conn,"SELECT RDROLE,RDPORT,RDSEQN FROM SGHDSDATA.SYROLD WHERE RDPORT IN (".$pList.") ORDER BY RDPORT,RDROLE");
echo '<table><tr><th>RDROLE</th><th>RDPORT</th><th>RDSEQN</th></tr>';
$cnt=0;
if ($r) while ($row=db2_fetch_assoc($r)) {
    echo '<tr class="hi"><td>'.htmlspecialchars(trim($row['RDROLE'])).'</td><td>'.htmlspecialchars(trim($row['RDPORT'])).'</td><td>'.htmlspecialchars(trim($row['RDSEQN'])).'</td></tr>';
    $cnt++;
} else echo '<tr><td colspan="3" class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
echo '</table><p>'.$cnt.' rows on Live</p>';

// 4. SYPORR for HD_ALL_SG + SG portals
echo '<h2>4. SYPORR — HD_ALL_SG + SG portals</h2>';
$r = db2_exec($conn,"SELECT PRROLE,PRPORT,PRPAGE,PRSEQ FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='HD_ALL_SG' AND PRPORT IN (".$pList.") ORDER BY PRPORT,PRSEQ");
echo '<table><tr><th>PRROLE</th><th>PRPORT</th><th>PRPAGE</th><th>PRSEQ</th></tr>';
$cnt=0;
if ($r) while ($row=db2_fetch_assoc($r)) {
    echo '<tr><td>'.htmlspecialchars(trim($row['PRROLE'])).'</td><td>'.htmlspecialchars(trim($row['PRPORT'])).'</td><td>'.htmlspecialchars(trim($row['PRPAGE'])).'</td><td>'.htmlspecialchars(trim($row['PRSEQ'])).'</td></tr>';
    $cnt++;
}
if ($cnt===0) echo '<tr><td colspan="4" class="miss">0 rows for HD_ALL_SG</td></tr>';
echo '</table>';

// 5. SYPORW for HD_ALL_SG + SG portals
echo '<h2>5. SYPORW — HD_ALL_SG + SG portals</h2>';
$r = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORW WHERE TRIM(PWROLE)='HD_ALL_SG' AND PWPORT IN (".$pList.") ORDER BY PWPORT");
echo '<table><tr><th colspan="10">SYPORW rows</th></tr>';
$cnt=0;
if ($r) {
    $nf=db2_num_fields($r); $cols=array(); for($i=0;$i<$nf;$i++) $cols[]=db2_field_name($r,$i);
    echo '<tr>'; foreach($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>'; echo '</tr>';
    while ($row=db2_fetch_assoc($r)) {
        echo '<tr>'; foreach($cols as $c) echo '<td>'.htmlspecialchars(rtrim((string)$row[$c])).'</td>'; echo '</tr>'; $cnt++;
    }
} else echo '<tr><td class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
if ($cnt===0) echo '<tr><td colspan="10" class="miss">0 rows for HD_ALL_SG in SYPORW</td></tr>';
echo '</table>';

echo '</body></html>';
?>
