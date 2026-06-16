<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";
$roles = array('ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR',
               'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN',
               'MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION',
               'PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM');
$rList = "'".implode("','",$roles)."'";

// Get sub-pages from SYPORT using correct column FPPORT
function getSgPages($conn, $pList) {
    $pages = array();
    $r = db2_exec($conn, "SELECT FPPORT,FPPAGE,FPSEQ FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") AND TRIM(FPPAGE)<>'' ORDER BY FPPORT,FPSEQ");
    if ($r) while ($row = db2_fetch_assoc($r)) {
        $pages[] = array('port'=>trim($row['FPPORT']), 'page'=>trim($row['FPPAGE']), 'seq'=>trim($row['FPSEQ']));
    }
    return $pages;
}

// BACKUP before any output
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array('-- SYPORR SG sub-pages backup -- ' . date('Y-m-d H:i:s'));
    $lines[] = '-- Undo: DELETE FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE) IN ('.$rList.') AND PRPORT IN ('.$pList.') AND TRIM(PRPAGE)<>\'\';';
    $lines[] = '';
    $rb = db2_exec($conn, "SELECT * FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE) IN (".$rList.") AND PRPORT IN (".$pList.") AND TRIM(PRPAGE)<>'' ORDER BY PRROLE,PRPORT,PRPAGE");
    if ($rb) {
        $nc=db2_num_fields($rb); $bc=array();
        for($i=0;$i<$nc;$i++) $bc[]=db2_field_name($rb,$i);
        while ($row=db2_fetch_assoc($rb)) {
            $vals=array();
            foreach($bc as $c) $vals[]="'".str_replace("'","''",(string)rtrim($row[$c]))."'";
            $lines[]="INSERT INTO S5HDSDATA.SYPORR (".implode(',',$bc).") VALUES (".implode(',',$vals).");";
        }
    }
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sg_porr_pages_backup_'.date('Ymd_His').'.sql"');
    header('Cache-Control: no-cache, no-store');
    echo implode("\r\n", $lines);
    exit;
}

// INSERT
if (isset($_POST['insert'])) {
    $tr = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' FETCH FIRST 1 ROWS ONLY");
    $tmplRow = db2_fetch_assoc($tr);
    $tmplCols = array_keys($tmplRow);
    $pages = getSgPages($conn, $pList);

    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Insert</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}</style></head><body>';
    echo '<h1>Inserting SYPORR sub-page rows</h1>';
    $ins=0; $skip=0; $err=0;
    foreach ($roles as $role) {
        foreach ($pages as $pg) {
            $ck = db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='".$role."' AND TRIM(PRPORT)='".$pg['port']."' AND TRIM(PRPAGE)='".$pg['page']."'");
            $cr = db2_fetch_assoc($ck);
            if ($cr && $cr['N']>0) { $skip++; continue; }
            $vals=array();
            foreach ($tmplCols as $c) {
                if ($c==='PRROLE')     $vals[]="'".$role."'";
                elseif ($c==='PRPORT') $vals[]="'".$pg['port']."'";
                elseif ($c==='PRPAGE') $vals[]="'".$pg['page']."'";
                elseif ($c==='PRSEQ')  $vals[]="'".$pg['seq']."'";
                elseif ($c==='PRID')   $vals[]="'".$role."/".$pg['port']."/".$pg['page']."'";
                else                   $vals[]="'".str_replace("'","''",(string)rtrim($tmplRow[$c]))."'";
            }
            $ri = db2_exec($conn,"INSERT INTO S5HDSDATA.SYPORR (".implode(',',$tmplCols).") VALUES (".implode(',',$vals).")");
            if ($ri) { $ins++; echo '<span class="ok">OK</span> '.$role.' / '.$pg['port'].' / '.$pg['page'].'<br>'; }
            else      { $err++; echo '<span class="err">ERR</span> '.$role.' / '.$pg['port'].' / '.$pg['page'].' -- '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }
        }
    }
    echo '<br><strong>Inserted: '.$ins.' &nbsp; Skipped: '.$skip.' &nbsp; Errors: '.$err.'</strong>';
    echo '<br><br><strong>Log SG5TEST out and back in to see sub-menus.</strong>';
    echo '</body></html>'; exit;
}

// HTML
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SYPORR Pages Fix</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 6px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#dfd;font-weight:bold;}.miss{background:#ffe;}
.step{border:1px solid #999;padding:10px;margin:10px 0;background:#f9f9f9;}
</style></head><body><h1>SYPORR Sub-Page Fix</h1>';

// Show SYPORT sub-pages for SG portals
echo '<h2>1. SYPORT sub-pages for SG portals (FPPORT/FPPAGE)</h2>';
$pages = getSgPages($conn, $pList);
echo '<table><tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th></tr>';
foreach ($pages as $pg) echo '<tr class="hi"><td>'.htmlspecialchars($pg['port']).'</td><td>'.htmlspecialchars($pg['page']).'</td><td>'.htmlspecialchars($pg['seq']).'</td></tr>';
if (empty($pages)) {
    echo '<tr><td colspan="3" style="color:red">0 sub-pages found in SYPORT for SG portals</td></tr>';
    // Show all SYPORT rows for SG portals (no page filter)
    $rAll = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") ORDER BY FPPORT,FPSEQ");
    if ($rAll) {
        $nf=db2_num_fields($rAll); $cols=array(); for($i=0;$i<$nf;$i++) $cols[]=db2_field_name($rAll,$i);
        echo '</table><p>All SYPORT rows for SG portals:</p><table><tr>';
        foreach($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>'; echo '</tr>';
        while ($row=db2_fetch_assoc($rAll)) {
            echo '<tr>'; foreach($cols as $c) echo '<td>'.htmlspecialchars(rtrim((string)$row[$c])).'</td>'; echo '</tr>';
        }
    } else echo '<tr><td colspan="3" style="color:red">SYPORT query failed: '.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
}
echo '</table>';

// Show SYROLD for SG portals (correct column: RDPORT)
echo '<h2>2. SYROLD rows for SG portals (RDPORT)</h2>';
$r = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYROLD WHERE RDPORT IN (".$pList.") ORDER BY RDPORT,RDROLE");
if ($r) {
    $nf=db2_num_fields($r); $cols=array(); for($i=0;$i<$nf;$i++) $cols[]=db2_field_name($r,$i);
    echo '<table><tr>'; foreach($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>'; echo '</tr>';
    $cnt=0;
    while ($row=db2_fetch_assoc($r)) {
        echo '<tr>'; foreach($cols as $c) echo '<td>'.htmlspecialchars(rtrim((string)$row[$c])).'</td>'; echo '</tr>'; $cnt++;
    }
    if($cnt===0) echo '<tr><td colspan="'.$nf.'" style="color:red">0 rows — SYROLD has no SG portal entries!</td></tr>';
    echo '</table><p>'.$cnt.' rows</p>';
} else echo '<p style="color:red">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

// Missing SYPORR sub-page rows
if (!empty($pages)) {
    echo '<h2>3. Missing SYPORR sub-page rows for CUSTSRVC</h2>';
    echo '<table><tr><th>PRPORT</th><th>PRPAGE</th><th>Status</th></tr>';
    $miss=0;
    foreach ($pages as $pg) {
        $ck = db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' AND TRIM(PRPORT)='".$pg['port']."' AND TRIM(PRPAGE)='".$pg['page']."'");
        $cr = db2_fetch_assoc($ck); $ex = $cr && $cr['N']>0;
        echo '<tr'.($ex?'':' class="miss"').'><td>'.htmlspecialchars($pg['port']).'</td><td>'.htmlspecialchars($pg['page']).'</td><td>'.($ex?'exists':'MISSING').'</td></tr>';
        if (!$ex) $miss++;
    }
    echo '</table>';
    $total = count($roles)*$miss;
    echo '<p>'.$miss.' missing for CUSTSRVC; '.$total.' total ('.count($roles).' roles)</p>';
    if ($total>0) {
        echo '<div class="step"><b>Step 1:</b> <a href="?action=backup" style="background:#060;color:#fff;padding:6px 16px;text-decoration:none;">Download backup</a></div>';
        echo '<div class="step"><b>Step 2:</b> <form method="post"><button name="insert" value="1" style="background:#c00;color:#fff;padding:7px 20px;font-size:13px;">Insert '.$total.' sub-page rows into SYPORR</button></form></div>';
    } else { echo '<p style="color:green;font-weight:bold;">All sub-page SYPORR rows exist.</p>'; }
}
echo '</body></html>';
?>
