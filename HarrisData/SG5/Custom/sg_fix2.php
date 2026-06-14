<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";
$rList = "'ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR',"
       . "'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN',"
       . "'MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION',"
       . "'PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM'";

$portals = array(
    'SGINQ'  => 'SG Inquiries',
    'SGDASH' => 'SG Dashboards',
    'SGDINT' => 'SG Data Integrity',
    'SGRPT'  => 'SG Reports',
    'SGSOP'  => "SG SOP's",
);
// FPSEQ/PRSEQ start at 3 to avoid conflict with portal header seq=1
$cats = array(
    'ACCT'   => array('seq' => '3.00', 'desc' => 'Accounting'),
    'INVMGMT'=> array('seq' => '4.00', 'desc' => 'Inventory Management'),
    'MFG'    => array('seq' => '5.00', 'desc' => 'Manufacturing'),
    'OE'     => array('seq' => '6.00', 'desc' => 'Order Entry'),
    'PLN'    => array('seq' => '7.00', 'desc' => 'Planning'),
    'PUR'    => array('seq' => '8.00', 'desc' => 'Purchasing'),
);
$roles = array(
    'ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR',
    'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN',
    'MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION',
    'PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM',
);

// -------------------------------------------------------
// BACKUP -- must be before any HTML output
// -------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array(
        '-- sg_fix2 pre-backup -- ' . date('Y-m-d H:i:s'),
        '-- UNDO SYPORT sub-pages: DELETE FROM S5HDSDATA.SYPORT'
        . ' WHERE FPPORT IN ('.$pList.') AND TRIM(FPPAGE)<>\'\';',
        '-- UNDO SYPORR: DELETE FROM S5HDSDATA.SYPORR'
        . ' WHERE PRPORT IN ('.$pList.') AND TRIM(PRROLE) IN ('.$rList.');',
        '',
    );
    $bqry = array(
        'SYPORT'  => 'SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORT IN ('.$pList.') ORDER BY FPPORT,FPSEQ',
        'SYPORR'  => 'SELECT * FROM S5HDSDATA.SYPORR WHERE PRPORT IN ('.$pList.')'
                   . ' AND TRIM(PRROLE) IN ('.$rList.') ORDER BY PRPORT,PRROLE,PRPAGE,PRSEQ',
    );
    foreach ($bqry as $tbl => $sql) {
        $lines[] = ''; $lines[] = '-- '.$tbl;
        $rb = db2_exec($conn, $sql);
        if (!$rb) { $lines[] = '-- FAILED: '.db2_stmt_errormsg(); continue; }
        $nc = db2_num_fields($rb); $bc = array();
        for ($i = 0; $i < $nc; $i++) $bc[] = db2_field_name($rb, $i);
        $cnt = 0;
        while ($row = db2_fetch_assoc($rb)) {
            $vals = array();
            foreach ($bc as $c)
                $vals[] = "'".str_replace("'","''",(string)rtrim($row[$c]))."'";
            $lines[] = 'INSERT INTO '.$tbl.' ('.implode(',',$bc).') VALUES ('.implode(',',$vals).');';
            $cnt++;
        }
        $lines[] = '-- '.$cnt.' rows';
    }
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sg_fix2_backup_'.date('Ymd_His').'.sql"');
    header('Cache-Control: no-cache, no-store');
    echo implode("\r\n", $lines);
    exit;
}

// -------------------------------------------------------
// EXECUTE
// -------------------------------------------------------
if (isset($_POST['fix'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix2</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
.skip{color:#888;}h2{color:#00c;margin:14px 0 4px;border-bottom:1px solid #00c;}
</style></head><body><h1>SG Fix2</h1>';

    $now = date('Y-m-d-H.i.s.000000');
    $totalErr = 0;

    // --- A. Delete SYPORT sub-pages (wrong FPSEQ 1-6) ---
    echo '<h2>A. Delete SYPORT sub-pages (old FPSEQ 1-6)</h2>';
    $r = db2_exec($conn,
        "DELETE FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pList) AND TRIM(FPPAGE)<>''");
    if ($r) echo '<span class="ok">OK</span> deleted '.db2_num_rows($r).' SYPORT sub-pages<br>';
    else { $totalErr++; echo '<span class="err">ERR</span> '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }

    // --- B. Reinsert SYPORT sub-pages with FPSEQ 3-8 ---
    echo '<h2>B. Insert SYPORT sub-pages (FPSEQ 3-8)</h2>';
    $ins = 0; $err = 0;
    foreach ($portals as $port => $ptitle) {
        foreach ($cats as $cat => $cInfo) {
            $fpid   = $port . '_' . $cat;
            $fpdesc = str_replace("'","''",$cInfo['desc']);
            $fptitl = str_replace("'","''",$ptitle . ' - ' . $cInfo['desc']);
            $sql = "INSERT INTO S5HDSDATA.SYPORT"
                 . " (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,"
                 . "FPTSTP,FPTSUS,FPTSWS,FPTSPT)"
                 . " VALUES ('$port','$port','".$cInfo['seq']."','$fpid',"
                 . "'$fpdesc','$fptitl','','','$now','BILL','','')";
            $r = db2_exec($conn, $sql);
            if ($r) $ins++;
            else {
                $err++; $totalErr++;
                echo '<span class="err">ERR</span> SYPORT '.$port.'/'.$cat.': '
                   . htmlspecialchars(db2_stmt_errormsg()).'<br>';
            }
        }
    }
    echo '<b>SYPORT sub-pages: ins='.$ins.' err='.$err.'</b><br>';

    // --- C. Delete SYPORR sub-pages (any with PRPAGE<>'') ---
    echo '<h2>C. Delete SYPORR sub-pages</h2>';
    $r = db2_exec($conn,
        "DELETE FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList)"
        . " AND TRIM(PRROLE) IN ($rList) AND TRIM(PRPAGE)<>''");
    if ($r) echo '<span class="ok">OK</span> deleted '.db2_num_rows($r).' SYPORR sub-page rows<br>';
    else { $totalErr++; echo '<span class="err">ERR</span> '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }

    // --- D. Load SYPORR template ---
    echo '<h2>D. SYPORR template + insert headers + sub-pages</h2>';
    $ptRow   = db2_exec($conn,
        "SELECT * FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' FETCH FIRST 1 ROWS ONLY");
    $porrTmpl = db2_fetch_assoc($ptRow);
    if (!$porrTmpl) {
        echo '<span class="err">ERR</span> CUSTSRVC template not found -- aborting SYPORR inserts.<br>';
        $totalErr++;
    } else {
        $porrCols = array_keys($porrTmpl);
        echo '<span class="ok">Template cols:</span> '.implode(', ',$porrCols).'<br>';

        $hIns=0; $hSkip=0; $sIns=0; $sErr=0;

        foreach ($roles as $role) {
            foreach ($portals as $port => $ptitle) {
                // Portal header row (PRPAGE='', PRSEQ=1)
                $ck = db2_fetch_assoc(db2_exec($conn,
                    "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR"
                    . " WHERE TRIM(PRROLE)='$role' AND TRIM(PRPORT)='$port'"
                    . " AND TRIM(PRPAGE)='' AND TRIM(PRSEQ)='1.00'"));
                if ((int)$ck['N'] > 0) {
                    $hSkip++;
                } else {
                    $prid = substr($role.'/'.$port, 0, 55);
                    $vals = array();
                    foreach ($porrCols as $c) {
                        if      ($c==='PRROLE') $vals[] = "'$role'";
                        elseif  ($c==='PRPORT') $vals[] = "'$port'";
                        elseif  ($c==='PRPAGE') $vals[] = "''";
                        elseif  ($c==='PRSEQ')  $vals[] = "'1.00'";
                        elseif  ($c==='PRID')   $vals[] = "'".str_replace("'","''",$prid)."'";
                        elseif  ($c==='PRTSTP') $vals[] = "'$now'";
                        elseif  ($c==='PRTSUS') $vals[] = "'BILL'";
                        elseif  ($c==='PRTSWS') $vals[] = "''";
                        elseif  ($c==='PRTSPT') $vals[] = "''";
                        else    $vals[] = "'".str_replace("'","''",(string)rtrim($porrTmpl[$c]))."'";
                    }
                    $ri = db2_exec($conn,
                        "INSERT INTO S5HDSDATA.SYPORR (".implode(',',$porrCols).")"
                        . " VALUES (".implode(',',$vals).")");
                    if ($ri) $hIns++;
                    else {
                        $totalErr++;
                        echo '<span class="err">ERR</span> SYPORR hdr '.$role.'/'.$port.': '
                           . htmlspecialchars(db2_stmt_errormsg()).'<br>';
                    }
                }

                // Sub-page rows (PRPAGE=port, PRSEQ=3-8)
                foreach ($cats as $cat => $cInfo) {
                    $prseq = $cInfo['seq'];
                    $prid  = substr($role.'/'.$port.'/'.$port.'/'.$prseq, 0, 55);
                    $vals  = array();
                    foreach ($porrCols as $c) {
                        if      ($c==='PRROLE') $vals[] = "'$role'";
                        elseif  ($c==='PRPORT') $vals[] = "'$port'";
                        elseif  ($c==='PRPAGE') $vals[] = "'$port'";
                        elseif  ($c==='PRSEQ')  $vals[] = "'$prseq'";
                        elseif  ($c==='PRID')   $vals[] = "'".str_replace("'","''",$prid)."'";
                        elseif  ($c==='PRTSTP') $vals[] = "'$now'";
                        elseif  ($c==='PRTSUS') $vals[] = "'BILL'";
                        elseif  ($c==='PRTSWS') $vals[] = "''";
                        elseif  ($c==='PRTSPT') $vals[] = "''";
                        else    $vals[] = "'".str_replace("'","''",(string)rtrim($porrTmpl[$c]))."'";
                    }
                    $ri = db2_exec($conn,
                        "INSERT INTO S5HDSDATA.SYPORR (".implode(',',$porrCols).")"
                        . " VALUES (".implode(',',$vals).")");
                    if ($ri) $sIns++;
                    else {
                        $sErr++; $totalErr++;
                        echo '<span class="err">ERR</span> SYPORR sub '.$role.'/'.$port.'/'.$cat.': '
                           . htmlspecialchars(db2_stmt_errormsg()).'<br>';
                    }
                }
            }
        }
        echo '<b>SYPORR headers: ins='.$hIns.' skip='.$hSkip.'</b><br>';
        echo '<b>SYPORR sub-pages: ins='.$sIns.' err='.$sErr.'</b><br>';
    }

    // --- E. SYDSGN fix ---
    echo '<h2>E. SYDSGN fix</h2>';
    $r = db2_exec($conn,
        "UPDATE SG5STDPGM.SYDSGN SET PDUSER=' ' WHERE PDTBID=199 AND PDPGID=0");
    if ($r) echo '<span class="ok">OK</span> SYDSGN: '.db2_num_rows($r).' rows updated<br>';
    else { $totalErr++; echo '<span class="err">ERR</span> '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }

    // --- F. Counts ---
    echo '<h2>F. Final counts</h2>';
    $chks = array(
        array('SYPORT headers',    5,   "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pList) AND TRIM(FPPAGE)=''"),
        array('SYPORT sub-pages', 30,   "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pList) AND TRIM(FPPAGE)<>''"),
        array('SYPORR headers',  115,   "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList) AND TRIM(PRROLE) IN ($rList) AND TRIM(PRPAGE)=''"),
        array('SYPORR sub-pages',690,   "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList) AND TRIM(PRROLE) IN ($rList) AND TRIM(PRPAGE)<>''"),
        array('SYROLD rows',     115,   "SELECT COUNT(*) AS N FROM S5HDSDATA.SYROLD WHERE RDPORT IN ($pList)"),
    );
    foreach ($chks as $c) {
        $row = db2_fetch_assoc(db2_exec($conn, $c[2]));
        $n   = (int)$row['N'];
        $cls = ($n === $c[1]) ? 'ok' : 'err';
        echo '<span class="'.$cls.'">'.$c[0].': '.$n.' (need '.$c[1].')</span><br>';
    }

    if ($totalErr === 0)
        echo '<br><span class="ok">DONE -- 0 errors. Log SG5TEST out and back in.</span>';
    else
        echo '<br><span class="err">DONE WITH '.$totalErr.' ERRORS -- check above.</span>';
    echo '</body></html>';
    exit;
}

// -------------------------------------------------------
// DIAGNOSTIC PAGE
// -------------------------------------------------------
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix2</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
.miss{background:#fdd;font-weight:bold;}.good{background:#dfd;}
.step{border:1px solid #999;padding:10px;margin:8px 0;background:#f9f9f9;}
</style></head><body><h1>SG Fix2 -- Sub-menu Fix</h1>
<p><b>Root cause:</b> SYPORT sub-pages had FPSEQ=1-6; SYPORR sub-pages had PRSEQ=1 which'
. ' conflicts with portal header PRSEQ=1 (unique key on PRROLE,PRPORT,PRSEQ).<br>'
. '<b>Fix:</b> Delete + reinsert SYPORT sub-pages with FPSEQ=3-8; delete + reinsert SYPORR'
. ' sub-pages with PRSEQ=3-8.</p>';

// SYPORT counts
$hdr = db2_fetch_assoc(db2_exec($conn,
    "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pList) AND TRIM(FPPAGE)=''"));
$sub = db2_fetch_assoc(db2_exec($conn,
    "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pList) AND TRIM(FPPAGE)<>''"));
echo '<h2>SYPORT</h2>';
echo 'Headers: <b>'.(int)$hdr['N'].'</b> (need 5)&nbsp;&nbsp;';
echo 'Sub-pages: <b>'.(int)$sub['N'].'</b> (need 30)<br>';

// SYPORR counts (no PRRESV)
$ph = db2_fetch_assoc(db2_exec($conn,
    "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList)"
    . " AND TRIM(PRROLE) IN ($rList) AND TRIM(PRPAGE)=''"));
$ps = db2_fetch_assoc(db2_exec($conn,
    "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList)"
    . " AND TRIM(PRROLE) IN ($rList) AND TRIM(PRPAGE)<>''"));
echo '<h2>SYPORR (no PRRESV in query)</h2>';
$phN = (int)$ph['N']; $psN = (int)$ps['N'];
$c1 = ($phN===115)?'ok':'err'; $c2 = ($psN===690)?'ok':'err';
echo '<span class="'.$c1.'">Headers: '.$phN.' (need 115)</span>&nbsp;&nbsp;';
echo '<span class="'.$c2.'">Sub-pages: '.$psN.' (need 690)</span><br>';

// SYPORR sample for HD_ALL_SG
echo '<h2>SYPORR sample -- HD_ALL_SG (first 10)</h2>';
$r = db2_exec($conn,
    "SELECT PRPORT,PRPAGE,PRSEQ,PRID FROM S5HDSDATA.SYPORR"
    . " WHERE TRIM(PRROLE)='HD_ALL_SG' AND PRPORT IN ($pList)"
    . " ORDER BY PRPORT,PRPAGE,PRSEQ FETCH FIRST 10 ROWS ONLY");
echo '<table style="border-collapse:collapse"><tr><th style="border:1px solid #bbb;padding:2px 6px">PRPORT</th>'
   . '<th style="border:1px solid #bbb;padding:2px 6px">PRPAGE</th>'
   . '<th style="border:1px solid #bbb;padding:2px 6px">PRSEQ</th>'
   . '<th style="border:1px solid #bbb;padding:2px 6px">PRID</th></tr>';
$cnt = 0;
if ($r) while ($row = db2_fetch_assoc($r)) {
    echo '<tr><td style="border:1px solid #bbb;padding:2px 6px">'.htmlspecialchars(trim($row['PRPORT'])).'</td>'
       . '<td style="border:1px solid #bbb;padding:2px 6px">'.htmlspecialchars(trim($row['PRPAGE'])?:' (header)').'</td>'
       . '<td style="border:1px solid #bbb;padding:2px 6px">'.htmlspecialchars(trim($row['PRSEQ'])).'</td>'
       . '<td style="border:1px solid #bbb;padding:2px 6px">'.htmlspecialchars(trim($row['PRID'])).'</td></tr>';
    $cnt++;
}
if ($cnt===0) echo '<tr><td colspan="4" style="color:red;padding:4px">0 rows</td></tr>';
echo '</table>';

// SYURLM check with TRIM
echo '<h2>SYURLM portal headers (using TRIM)</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(FUID) AS FUID, TRIM(FUURL) AS FUURL FROM S5HDSDATA.SYURLM"
    . " WHERE TRIM(FUID) IN ('SGINQ/PORTAL','SGDASH/PORTAL','SGDINT/PORTAL',"
    . "'SGRPT/PORTAL','SGSOP/PORTAL')");
$ucnt = 0;
if ($r) while ($row = db2_fetch_assoc($r)) {
    $hasUrl = (trim($row['FUURL']) !== '');
    echo htmlspecialchars(trim($row['FUID'])) . ': URL='
       . ($hasUrl ? '<span class="ok">SET</span>' : '<span class="err">EMPTY</span>') . '<br>';
    $ucnt++;
}
echo '('.$ucnt.' of 5 SYURLM portal header rows found)';
if ($ucnt < 5) echo ' <span class="err">&larr; some missing -- need sg_menu_fix.php re-run</span>';

echo '<div class="step"><b>Step 1:</b> <a href="?action=backup" style="background:#060;color:#fff;'
   . 'padding:6px 14px;text-decoration:none;">Download Backup</a></div>';
echo '<div class="step"><b>Step 2:</b> <form method="post">'
   . '<button name="fix" value="1" style="background:#c00;color:#fff;padding:8px 24px;font-size:13px;">'
   . 'Fix Sub-menus (SYPORT FPSEQ + SYPORR PRSEQ 3-8)</button></form></div>';
echo '</body></html>';
?>
