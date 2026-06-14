<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Submenu Diag</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 6px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#dfd;font-weight:bold;}
.err{color:red;font-weight:bold;}
</style></head><body><h1>Submenu Diagnostic</h1>';

$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";

// 1. SYPORT columns
echo '<h2>1. SYPORT columns</h2>';
$rc = db2_exec($conn,"SELECT COLUMN_NAME FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA='S5HDSDATA' AND TABLE_NAME='SYPORT' ORDER BY ORDINAL_POSITION");
$sCols = array();
if ($rc) while ($r=db2_fetch_assoc($rc)) { echo htmlspecialchars($r['COLUMN_NAME']).' '; $sCols[]=$r['COLUMN_NAME']; }
echo '<br>';

// 2. SYPORT rows for SG portals (all columns)
echo '<h2>2. SYPORT rows for SG portals</h2>';
$r = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORTID IN (".$pList.") ORDER BY FPPORTID,FPSEQNO");
if ($r) {
    $nf=db2_num_fields($r); $cols=array(); for($i=0;$i<$nf;$i++) $cols[]=db2_field_name($r,$i);
    echo '<table><tr>'; foreach($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>'; echo '</tr>';
    $cnt=0;
    while ($row=db2_fetch_assoc($r)) {
        echo '<tr class="hi">'; foreach($cols as $c) echo '<td>'.htmlspecialchars(rtrim((string)$row[$c])).'</td>'; echo '</tr>'; $cnt++;
    }
    if($cnt===0) echo '<tr><td colspan="'.$nf.'"><em class="err">0 rows</em></td></tr>';
    echo '</table>';
} else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

// 3. SYROLD columns
echo '<h2>3. SYROLD columns</h2>';
$rc = db2_exec($conn,"SELECT COLUMN_NAME FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA='S5HDSDATA' AND TABLE_NAME='SYROLD' ORDER BY ORDINAL_POSITION");
if ($rc) while ($r=db2_fetch_assoc($rc)) echo htmlspecialchars($r['COLUMN_NAME']).' ';
echo '<br>';

// 4. SYROLD rows for SG portals
echo '<h2>4. SYROLD rows for SG portals</h2>';
$r = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYROLD WHERE RDPORTID IN (".$pList.") ORDER BY RDPORTID,RDROLE");
if ($r) {
    $nf=db2_num_fields($r); $cols=array(); for($i=0;$i<$nf;$i++) $cols[]=db2_field_name($r,$i);
    echo '<table><tr>'; foreach($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>'; echo '</tr>';
    $cnt=0;
    while ($row=db2_fetch_assoc($r)) {
        echo '<tr>'; foreach($cols as $c) echo '<td>'.htmlspecialchars(rtrim((string)$row[$c])).'</td>'; echo '</tr>'; $cnt++;
    }
    if($cnt===0) echo '<tr><td colspan="'.$nf.'"><em class="err">0 rows</em></td></tr>';
    echo '</table><p>'.$cnt.' rows</p>';
} else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

// 5. Compare: pick an existing portal CUSTSRVC can see — show its SYPORT + SYROLD structure
// Find a non-SG portal CUSTSRVC has SYPORR access to
echo '<h2>5. Reference portal: SYPORR for CUSTSRVC (first non-SG portal)</h2>';
$rRef = db2_exec($conn,"SELECT PRPORT FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' AND PRPORT NOT IN (".$pList.") AND TRIM(PRPAGE)='' FETCH FIRST 1 ROWS ONLY");
$refPort = ''; if ($rRef) { $rr=db2_fetch_assoc($rRef); if ($rr) $refPort=trim($rr['PRPORT']); }
echo 'Reference portal: <b>'.htmlspecialchars($refPort).'</b><br>';

if ($refPort) {
    // Its SYPORT pages
    echo '<h2>5a. SYPORT pages for reference portal: '.$refPort.'</h2>';
    $r = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORTID='".$refPort."' ORDER BY FPSEQNO");
    if ($r) {
        $nf=db2_num_fields($r); $cols=array(); for($i=0;$i<$nf;$i++) $cols[]=db2_field_name($r,$i);
        echo '<table><tr>'; foreach($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>'; echo '</tr>';
        $cnt=0;
        while ($row=db2_fetch_assoc($r)) {
            echo '<tr>'; foreach($cols as $c) echo '<td>'.htmlspecialchars(rtrim((string)$row[$c])).'</td>'; echo '</tr>'; $cnt++;
        }
        if($cnt===0) echo '<tr><td colspan="'.$nf.'"><em>0 rows</em></td></tr>';
        echo '</table>';
    } else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

    // Its SYROLD entries for CUSTSRVC
    echo '<h2>5b. SYROLD for CUSTSRVC + reference portal: '.$refPort.'</h2>';
    $r = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYROLD WHERE TRIM(RDROLE)='CUSTSRVC' AND RDPORTID='".$refPort."' ORDER BY RDSEQN");
    if ($r) {
        $nf=db2_num_fields($r); $cols=array(); for($i=0;$i<$nf;$i++) $cols[]=db2_field_name($r,$i);
        echo '<table><tr>'; foreach($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>'; echo '</tr>';
        $cnt=0;
        while ($row=db2_fetch_assoc($r)) {
            echo '<tr>'; foreach($cols as $c) echo '<td>'.htmlspecialchars(rtrim((string)$row[$c])).'</td>'; echo '</tr>'; $cnt++;
        }
        if($cnt===0) echo '<tr><td colspan="'.$nf.'"><em>0 rows</em></td></tr>';
        echo '</table>';
    } else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

    // Its SYPORR entries for CUSTSRVC (all pages)
    echo '<h2>5c. SYPORR for CUSTSRVC + reference portal: '.$refPort.'</h2>';
    $r = db2_exec($conn,"SELECT PRROLE,PRPORT,PRPAGE,PRSEQ FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' AND TRIM(PRPORT)='".$refPort."' ORDER BY PRSEQ");
    if ($r) {
        echo '<table><tr><th>PRROLE</th><th>PRPORT</th><th>PRPAGE</th><th>PRSEQ</th></tr>';
        $cnt=0;
        while ($row=db2_fetch_assoc($r)) {
            echo '<tr>'; foreach(array('PRROLE','PRPORT','PRPAGE','PRSEQ') as $c) echo '<td>'.htmlspecialchars(rtrim((string)$row[$c])).'</td>'; echo '</tr>'; $cnt++;
        }
        if($cnt===0) echo '<tr><td colspan="4"><em>0 rows</em></td></tr>';
        echo '</table>';
    } else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';
}

echo '</body></html>';
?>
