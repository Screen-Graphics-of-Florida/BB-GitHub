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

// -------------------------------------------------------
// BACKUP before any output
// -------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array('-- SG full menu backup -- ' . date('Y-m-d H:i:s'));
    $lines[] = '-- UNDO SYPORT: DELETE FROM S5HDSDATA.SYPORT WHERE FPPORT IN ('.$pList.');';
    $lines[] = '-- UNDO SYPORR: DELETE FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE) IN ('.$rList.') AND PRPORT IN ('.$pList.');';
    $lines[] = '';
    foreach (array('S5HDSDATA.SYPORT WHERE FPPORT IN ('.$pList.')'=>'SYPORT',
                   'S5HDSDATA.SYPORR WHERE TRIM(PRROLE) IN ('.$rList.') AND PRPORT IN ('.$pList.')'=>'SYPORR') as $where=>$tbl) {
        $lines[] = ''; $lines[] = '-- '.$tbl;
        $rb = db2_exec($conn,"SELECT * FROM ".$where." ORDER BY 1,2");
        if (!$rb) { $lines[] = '-- failed: '.db2_stmt_errormsg(); continue; }
        $nc=db2_num_fields($rb); $bc=array();
        for($i=0;$i<$nc;$i++) $bc[]=db2_field_name($rb,$i);
        while ($row=db2_fetch_assoc($rb)) {
            $vals=array();
            foreach($bc as $c) $vals[]="'".str_replace("'","''",(string)rtrim($row[$c]))."'";
            $lines[]="INSERT INTO ".$tbl." (".implode(',',$bc).") VALUES (".implode(',',$vals).");";
        }
    }
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sg_menu_backup_'.date('Ymd_His').'.sql"');
    header('Cache-Control: no-cache, no-store');
    echo implode("\r\n",$lines); exit;
}

// -------------------------------------------------------
// EXECUTE
// -------------------------------------------------------
if (isset($_POST['insert'])) {
    // SYPORR template
    $pt = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' FETCH FIRST 1 ROWS ONLY");
    $porrTmpl = db2_fetch_assoc($pt);
    $porrCols = array_keys($porrTmpl);

    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Insert</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}</style></head><body>';
    echo '<h1>Inserting SG menus + submenus</h1>';
    $sIns=0;$sSkip=0;$sErr=0;
    $pIns=0;$pSkip=0;$pErr=0;

    // Step 1: Copy SYPORT rows from Live (SGHDSDATA) to SG5 (S5HDSDATA) for SG portals
    $liveSport = db2_exec($conn,"SELECT * FROM SGHDSDATA.SYPORT WHERE FPPORT IN (".$pList.") ORDER BY FPPORT,FPSEQ");
    if (!$liveSport) {
        echo '<span class="err">Cannot read SGHDSDATA.SYPORT: '.htmlspecialchars(db2_stmt_errormsg()).'</span><br>';
    } else {
        $nc=db2_num_fields($liveSport); $sc=array();
        for($i=0;$i<$nc;$i++) $sc[]=db2_field_name($liveSport,$i);
        while ($row=db2_fetch_assoc($liveSport)) {
            $port=trim($row['FPPORT']); $pg=trim($row['FPPAGE']);
            $ck = db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT='".$port."' AND TRIM(FPPAGE)='".str_replace("'","''",$pg)."'");
            $cr = db2_fetch_assoc($ck);
            if ($cr && $cr['N']>0) { $sSkip++; continue; }
            $vals=array();
            foreach($sc as $c) $vals[]="'".str_replace("'","''",(string)rtrim($row[$c]))."'";
            $ri = db2_exec($conn,"INSERT INTO S5HDSDATA.SYPORT (".implode(',',$sc).") VALUES (".implode(',',$vals).")");
            if ($ri) { $sIns++; echo '<span class="ok">SYPORT</span> '.$port.'/'.($pg?$pg:'(header)').'<br>'; }
            else     { $sErr++; echo '<span class="err">SYPORT ERR</span> '.$port.'/'.$pg.' '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }
        }
    }
    echo '<br>';

    // Step 2: Get all SYPORT pages for SG portals (now populated from Live)
    $pages = array(); // array of [port, fppage, fpseq]
    $rp = db2_exec($conn,"SELECT FPPORT,FPPAGE,FPSEQ FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") ORDER BY FPPORT,FPSEQ");
    if ($rp) while ($row=db2_fetch_assoc($rp)) $pages[]=array(trim($row['FPPORT']),trim($row['FPPAGE']),trim($row['FPSEQ']));

    // Step 3: Insert SYPORR for all roles — portal header + each sub-page
    foreach ($roles as $role) {
        foreach ($pages as $pg) {
            list($port,$fppage,$fpseq) = $pg;
            $ck = db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='".$role."' AND TRIM(PRPORT)='".$port."' AND TRIM(PRPAGE)='".str_replace("'","''",$fppage)."'");
            $cr = db2_fetch_assoc($ck);
            if ($cr && $cr['N']>0) { $pSkip++; continue; }
            $vals=array();
            foreach ($porrCols as $c) {
                if      ($c==='PRROLE') $vals[]="'".$role."'";
                elseif  ($c==='PRPORT') $vals[]="'".$port."'";
                elseif  ($c==='PRPAGE') $vals[]="'".str_replace("'","''",$fppage)."'";
                elseif  ($c==='PRSEQ')  $vals[]="'".$fpseq."'";
                elseif  ($c==='PRID')   $vals[]="'".substr($role.'/'.$port.($fppage?'/'.$fppage:''),0,55)."'";
                else                    $vals[]="'".str_replace("'","''",(string)rtrim($porrTmpl[$c]))."'";
            }
            $ri = db2_exec($conn,"INSERT INTO S5HDSDATA.SYPORR (".implode(',',$porrCols).") VALUES (".implode(',',$vals).")");
            if ($ri) $pIns++; else { $pErr++; echo '<span class="err">PORR ERR</span> '.$role.'/'.$port.'/'.($fppage?$fppage:'hdr').' '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }
        }
    }

    echo '<br><strong>SYPORT: inserted='.$sIns.' skipped='.$sSkip.' errors='.$sErr.'</strong><br>';
    echo '<strong>SYPORR: inserted='.$pIns.' skipped='.$pSkip.' errors='.$pErr.'</strong>';
    echo '<br><br><strong>Log SG5TEST out and back in to verify all sub-menus.</strong>';
    echo '</body></html>'; exit;
}

// -------------------------------------------------------
// SHOW STATE
// -------------------------------------------------------
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Menu Fix</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 6px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#dfd;}.miss{background:#ffe;font-weight:bold;}
.err{color:red;font-weight:bold;}
.step{border:1px solid #999;padding:10px;margin:10px 0;background:#f9f9f9;}
</style></head><body><h1>SG Full Menu Fix (source: Live)</h1>';

// Live SYPORT for SG portals
echo '<h2>1. Live (SGHDSDATA) SYPORT rows for SG portals</h2>';
$r = db2_exec($conn,"SELECT FPPORT,FPPAGE,FPSEQ,FPID,FPDESC FROM SGHDSDATA.SYPORT WHERE FPPORT IN (".$pList.") ORDER BY FPPORT,FPSEQ");
echo '<table><tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPID</th><th>FPDESC</th></tr>';
$liveRows=array(); $lcnt=0;
if ($r) while ($row=db2_fetch_assoc($r)) {
    echo '<tr class="hi"><td>'.htmlspecialchars(trim($row['FPPORT'])).'</td><td>'.htmlspecialchars(trim($row['FPPAGE'])).'</td><td>'.htmlspecialchars(trim($row['FPSEQ'])).'</td><td>'.htmlspecialchars(trim($row['FPID'])).'</td><td>'.htmlspecialchars(trim($row['FPDESC'])).'</td></tr>';
    $liveRows[]=$row; $lcnt++;
} else echo '<tr><td colspan="5" class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
echo '</table><p>'.$lcnt.' rows on Live</p>';

// SG5 SYPORT for SG portals
echo '<h2>2. SG5 (S5HDSDATA) SYPORT rows for SG portals</h2>';
$r = db2_exec($conn,"SELECT FPPORT,FPPAGE,FPSEQ,FPID,FPDESC FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") ORDER BY FPPORT,FPSEQ");
echo '<table><tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPID</th><th>FPDESC</th></tr>';
$scnt=0;
if ($r) while ($row=db2_fetch_assoc($r)) {
    echo '<tr><td>'.htmlspecialchars(trim($row['FPPORT'])).'</td><td>'.htmlspecialchars(trim($row['FPPAGE'])).'</td><td>'.htmlspecialchars(trim($row['FPSEQ'])).'</td><td>'.htmlspecialchars(trim($row['FPID'])).'</td><td>'.htmlspecialchars(trim($row['FPDESC'])).'</td></tr>';
    $scnt++;
} else echo '<tr><td colspan="5" class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
echo '</table><p>'.$scnt.' rows on SG5 (Live has '.$lcnt.')</p>';

$missing = $lcnt - $scnt;
echo '<h2>Summary</h2>';
echo 'SYPORT rows to copy from Live: <b>'.max(0,$missing).'</b><br>';
echo 'SYPORR rows to insert: <b>~'.($lcnt*count($roles)).'</b> ('.count($roles).' roles x '.$lcnt.' SYPORT pages)<br>';

echo '<div class="step"><b>Step 1:</b> <a href="?action=backup" style="background:#060;color:#fff;padding:6px 16px;text-decoration:none;">Download backup</a></div>';
echo '<div class="step"><b>Step 2:</b> <form method="post"><button name="insert" value="1" style="background:#c00;color:#fff;padding:8px 24px;font-size:13px;">Copy from Live + insert all SYPORR rows</button></form></div>';
echo '</body></html>';
?>
