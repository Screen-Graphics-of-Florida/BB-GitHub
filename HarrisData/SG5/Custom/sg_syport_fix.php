<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

$sgPortals = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
$pList     = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";
$subCats   = array('ACCT','INVMGMT','MFG','OE','PLN','PUR');
$roles     = array('ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR',
                   'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN',
                   'MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION',
                   'PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM');
$rList     = "'".implode("','",$roles)."'";

$catSeq    = array('ACCT'=>3,'INVMGMT'=>4,'MFG'=>5,'OE'=>6,'PLN'=>7,'PUR'=>8);
$catDesc   = array(
    'ACCT'   =>'Accounting',
    'INVMGMT'=>'Inventory Management',
    'MFG'    =>'Manufacturing',
    'OE'     =>'Order Entry',
    'PLN'    =>'Planning',
    'PUR'    =>'Purchasing',
);

// BACKUP before any output
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array('-- SYPORT + SYPORR SG backup -- ' . date('Y-m-d H:i:s'));
    // SYPORT
    $lines[] = ''; $lines[] = '-- SYPORT';
    $rb = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") ORDER BY FPPORT,FPSEQ");
    if ($rb) {
        $nc=db2_num_fields($rb); $bc=array();
        for($i=0;$i<$nc;$i++) $bc[]=db2_field_name($rb,$i);
        while ($row=db2_fetch_assoc($rb)) {
            $vals=array();
            foreach($bc as $c) $vals[]="'".str_replace("'","''",(string)rtrim($row[$c]))."'";
            $lines[]="INSERT INTO S5HDSDATA.SYPORT (".implode(',',$bc).") VALUES (".implode(',',$vals).");";
        }
    }
    // SYPORR sub-page rows
    $lines[] = ''; $lines[] = '-- SYPORR sub-page rows';
    $rb2 = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE) IN (".$rList.") AND PRPORT IN (".$pList.") AND TRIM(PRPAGE)<>'' ORDER BY PRROLE,PRPORT,PRPAGE");
    if ($rb2) {
        $nc=db2_num_fields($rb2); $bc=array();
        for($i=0;$i<$nc;$i++) $bc[]=db2_field_name($rb2,$i);
        while ($row=db2_fetch_assoc($rb2)) {
            $vals=array();
            foreach($bc as $c) $vals[]="'".str_replace("'","''",(string)rtrim($row[$c]))."'";
            $lines[]="INSERT INTO S5HDSDATA.SYPORR (".implode(',',$bc).") VALUES (".implode(',',$vals).");";
        }
    }
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sg_syport_backup_'.date('Ymd_His').'.sql"');
    header('Cache-Control: no-cache, no-store');
    echo implode("\r\n", $lines);
    exit;
}

// INSERT
if (isset($_POST['insert'])) {
    // Get SYPORT ACCT template for each portal
    $tr = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") AND TRIM(FPPAGE)<>'' ORDER BY FPPORT,FPSEQ FETCH FIRST 1 ROWS ONLY");
    $tmpl = db2_fetch_assoc($tr);
    $tmplCols = array_keys($tmpl);

    // Get SYPORR template
    $tr2 = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' FETCH FIRST 1 ROWS ONLY");
    $porrTmpl = db2_fetch_assoc($tr2);
    $porrCols = array_keys($porrTmpl);

    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Fix</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}</style></head><body>';
    echo '<h1>Inserting missing SYPORT + SYPORR rows</h1>';

    $sportIns=0; $sportSkip=0; $sportErr=0;
    $porrIns=0;  $porrSkip=0;  $porrErr=0;

    foreach ($sgPortals as $port) {
        // Get ACCT template row for this specific portal
        $tRef = db2_exec($conn,"SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORT='".$port."' AND TRIM(FPPAGE)<>'' FETCH FIRST 1 ROWS ONLY");
        $portTmpl = $tRef ? db2_fetch_assoc($tRef) : $tmpl;

        foreach ($subCats as $cat) {
            $fppage = $port . '_' . $cat;
            $fpseq  = $catSeq[$cat];

            // SYPORT: check & insert
            $ck = db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT='".$port."' AND TRIM(FPPAGE)='".$fppage."'");
            $cr = db2_fetch_assoc($ck);
            if ($cr && $cr['N']>0) {
                $sportSkip++;
            } else {
                $vals=array();
                foreach ($tmplCols as $c) {
                    if ($c==='FPPORT')     $vals[]="'".$port."'";
                    elseif ($c==='FPPAGE') $vals[]="'".$fppage."'";
                    elseif ($c==='FPSEQ')  $vals[]="'".$fpseq."'";
                    elseif ($c==='FPID')   $vals[]="'".$fppage."'";
                    elseif ($c==='FPDESC') $vals[]="'".$catDesc[$cat]."'";
                    elseif ($c==='FPIITL') $vals[]="'".$catDesc[$cat]."'";
                    elseif ($c==='FPRESV') $vals[]="''";
                    else                   $vals[]="'".str_replace("'","''",(string)rtrim($portTmpl[$c]))."'";
                }
                $ri = db2_exec($conn,"INSERT INTO S5HDSDATA.SYPORT (".implode(',',$tmplCols).") VALUES (".implode(',',$vals).")");
                if ($ri) { $sportIns++; echo '<span class="ok">SYPORT OK</span> '.$port.'/'.$fppage.'<br>'; }
                else     { $sportErr++; echo '<span class="err">SYPORT ERR</span> '.$port.'/'.$fppage.' -- '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }
            }

            // SYPORR: insert for all roles
            foreach ($roles as $role) {
                $ck2 = db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='".$role."' AND TRIM(PRPORT)='".$port."' AND TRIM(PRPAGE)='".$fppage."'");
                $cr2 = db2_fetch_assoc($ck2);
                if ($cr2 && $cr2['N']>0) { $porrSkip++; continue; }
                $vals2=array();
                foreach ($porrCols as $c) {
                    if ($c==='PRROLE')     $vals2[]="'".$role."'";
                    elseif ($c==='PRPORT') $vals2[]="'".$port."'";
                    elseif ($c==='PRPAGE') $vals2[]="'".$fppage."'";
                    elseif ($c==='PRSEQ')  $vals2[]="'".$fpseq."'";
                    elseif ($c==='PRID')   $vals2[]="'".$role."/".$port."/".$fppage."'";
                    else                   $vals2[]="'".str_replace("'","''",(string)rtrim($porrTmpl[$c]))."'";
                }
                $ri2 = db2_exec($conn,"INSERT INTO S5HDSDATA.SYPORR (".implode(',',$porrCols).") VALUES (".implode(',',$vals2).")");
                if ($ri2) $porrIns++;
                else      { $porrErr++; echo '<span class="err">SYPORR ERR</span> '.$role.'/'.$port.'/'.$fppage.' -- '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }
            }
        }
    }

    echo '<br><strong>SYPORT: inserted='.$sportIns.' skipped='.$sportSkip.' errors='.$sportErr.'</strong><br>';
    echo '<strong>SYPORR: inserted='.$porrIns.' skipped='.$porrSkip.' errors='.$porrErr.'</strong>';
    echo '<br><br><strong>Log SG5TEST out and back in.</strong>';
    echo '</body></html>'; exit;
}

// HTML - show current state
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SYPORT Fix</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 6px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#dfd;}.miss{background:#ffe;font-weight:bold;}
.step{border:1px solid #999;padding:10px;margin:10px 0;background:#f9f9f9;}
</style></head><body><h1>SYPORT Sub-page Fix</h1>';

// Expected pages
echo '<h2>1. SYPORT: expected vs actual for SG portals</h2>';
echo '<table><tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>Status</th></tr>';
$missCnt=0;
foreach ($sgPortals as $port) {
    foreach ($subCats as $cat) {
        $fppage = $port . '_' . $cat;
        $ck = db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT='".$port."' AND TRIM(FPPAGE)='".$fppage."'");
        $cr = db2_fetch_assoc($ck); $ex = $cr && $cr['N']>0;
        echo '<tr'.($ex?' class="hi"':' class="miss"').'>';
        echo '<td>'.$port.'</td><td>'.$fppage.'</td><td>'.$catSeq[$cat].'</td>';
        echo '<td>'.($ex?'EXISTS':'MISSING').'</td></tr>';
        if (!$ex) $missCnt++;
    }
}
echo '</table><p>'.$missCnt.' missing SYPORT rows (expected 30 total, 5 portals x 6 sub-pages)</p>';

echo '<h2>2. SYPORT: all current rows for SG portals</h2>';
$r = db2_exec($conn,"SELECT FPPORT,FPPAGE,FPSEQ,FPID,FPDESC FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") ORDER BY FPPORT,FPSEQ");
echo '<table><tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPID</th><th>FPDESC</th></tr>';
if ($r) {
    $cnt=0;
    while ($row=db2_fetch_assoc($r)) {
        echo '<tr><td>'.htmlspecialchars(trim($row['FPPORT'])).'</td><td>'.htmlspecialchars(trim($row['FPPAGE'])).'</td><td>'.htmlspecialchars(trim($row['FPSEQ'])).'</td><td>'.htmlspecialchars(trim($row['FPID'])).'</td><td>'.htmlspecialchars(trim($row['FPDESC'])).'</td></tr>';
        $cnt++;
    }
    if ($cnt===0) echo '<tr><td colspan="5" style="color:red">0 rows</td></tr>';
} else echo '<tr><td colspan="5" style="color:red">'.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
echo '</table>';

if ($missCnt>0) {
    echo '<div class="step"><b>Step 1:</b> <a href="?action=backup" style="background:#060;color:#fff;padding:6px 16px;text-decoration:none;">Download backup (SYPORT + SYPORR)</a></div>';
    echo '<div class="step"><b>Step 2:</b> <form method="post"><button name="insert" value="1" style="background:#c00;color:#fff;padding:7px 20px;font-size:13px;">Insert '.$missCnt.' SYPORT rows + '.($missCnt*count($roles)).' SYPORR rows</button></form></div>';
} else {
    echo '<p style="color:green;font-weight:bold;">All SYPORT sub-pages exist. Check SYPORR separately.</p>';
}
echo '</body></html>';
?>
