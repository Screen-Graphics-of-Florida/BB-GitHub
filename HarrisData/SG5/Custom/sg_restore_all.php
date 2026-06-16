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
$cats = array(
    'ACCT'   => array('seq' => '1.00', 'desc' => 'Accounting'),
    'INVMGMT'=> array('seq' => '2.00', 'desc' => 'Inventory Management'),
    'MFG'    => array('seq' => '3.00', 'desc' => 'Manufacturing'),
    'OE'     => array('seq' => '4.00', 'desc' => 'Order Entry'),
    'PLN'    => array('seq' => '5.00', 'desc' => 'Planning'),
    'PUR'    => array('seq' => '6.00', 'desc' => 'Purchasing'),
);
$roles = array(
    'ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR',
    'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN',
    'MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION',
    'PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM',
);
// SYROLD sequence numbers (portals appear after Event Calendar / other portals)
$portSeq = array(
    'SGINQ' => '4', 'SGDASH' => '5', 'SGDINT' => '6',
    'SGRPT' => '7', 'SGSOP'  => '8',
);
// SYPORT header timestamps from June 10 original insert
$hdrTstp = array(
    'SGINQ'  => '2026-06-10-17.21.16.334872',
    'SGDASH' => '2026-06-10-17.21.16.422682',
    'SGDINT' => '2026-06-10-17.21.16.479867',
    'SGRPT'  => '2026-06-10-17.21.16.535429',
    'SGSOP'  => '2026-06-10-17.21.16.590915',
);

// -------------------------------------------------------
// BACKUP (must be at top -- before any HTML output)
// -------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array(
        '-- sg_restore_all pre-backup -- ' . date('Y-m-d H:i:s'),
        '-- Captures state BEFORE restore runs.',
        '',
        '-- UNDO SYPORT:  DELETE FROM S5HDSDATA.SYPORT WHERE FPPORT IN ('.$pList.');',
        '-- UNDO SYROLD:  DELETE FROM S5HDSDATA.SYROLD WHERE RDPORT IN ('.$pList.');',
        '-- UNDO SYPORR:  DELETE FROM S5HDSDATA.SYPORR WHERE PRPORT IN ('.$pList.')'
        .'  AND TRIM(PRROLE) IN ('.$rList.');',
        '-- UNDO SYDSGN:  UPDATE SG5STDPGM.SYDSGN SET PDUSER=\'BILL\' WHERE PDTBID=199 AND PDPGID=0;',
        '',
    );
    $tblQueries = array(
        'SYPORT' => 'SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORT IN ('.$pList.') ORDER BY FPPORT,FPSEQ',
        'SYROLD' => 'SELECT * FROM S5HDSDATA.SYROLD WHERE RDPORT IN ('.$pList.') ORDER BY RDPORT,RDROLE',
        'SYPORR' => 'SELECT * FROM S5HDSDATA.SYPORR WHERE PRPORT IN ('.$pList.')'
                  . ' AND TRIM(PRROLE) IN ('.$rList.') ORDER BY PRPORT,PRROLE,PRPAGE,PRSEQ',
        'SYURLM_SG' => "SELECT * FROM S5HDSDATA.SYURLM WHERE FUID LIKE 'SG%' ORDER BY FUID",
    );
    foreach ($tblQueries as $tblLabel => $sql) {
        $lines[] = ''; $lines[] = '-- ' . $tblLabel;
        $rb = db2_exec($conn, $sql);
        if (!$rb) { $lines[] = '-- query failed: ' . db2_stmt_errormsg(); continue; }
        $nc = db2_num_fields($rb); $bc = array();
        for ($i = 0; $i < $nc; $i++) $bc[] = db2_field_name($rb, $i);
        $cnt = 0;
        while ($row = db2_fetch_assoc($rb)) {
            $vals = array();
            foreach ($bc as $c)
                $vals[] = "'" . str_replace("'", "''", (string)rtrim($row[$c])) . "'";
            $tbl = ($tblLabel === 'SYURLM_SG') ? 'SYURLM' : $tblLabel;
            $lines[] = 'INSERT INTO ' . $tbl . ' (' . implode(',', $bc) . ') VALUES ('
                     . implode(',', $vals) . ');';
            $cnt++;
        }
        if ($cnt === 0) $lines[] = '-- 0 rows';
    }
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sg_restore_all_backup_'
         . date('Ymd_His') . '.sql"');
    header('Cache-Control: no-cache, no-store');
    echo implode("\r\n", $lines);
    exit;
}

// -------------------------------------------------------
// EXECUTE
// -------------------------------------------------------
if (isset($_POST['restore'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Restore</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
.skip{color:#888;}.warn{color:#c80;font-weight:bold;}
h2{color:#00c;margin:14px 0 4px;border-bottom:1px solid #00c;}
</style></head><body><h1>SG Restore All</h1>';

    $now = date('Y-m-d-H.i.s.000000');
    $totalErr = 0;

    // --------------------------------------------------
    // A. SYPORT headers (5)
    // --------------------------------------------------
    echo '<h2>A. SYPORT Headers</h2>';
    $ins = 0; $skip = 0; $err = 0;
    foreach ($portals as $port => $ptitle) {
        $fpid = $port . '/PORTAL';
        $ck = db2_fetch_assoc(db2_exec($conn,
            "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT"
            . " WHERE FPPORT='$port' AND TRIM(FPPAGE)=''"));
        if ((int)$ck['N'] > 0) {
            $skip++;
            echo '<span class="skip">SKIP</span> '.$port.' header exists<br>';
            continue;
        }
        $etitle = str_replace("'", "''", $ptitle);
        $sql = "INSERT INTO S5HDSDATA.SYPORT"
             . " (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,"
             . "FPTSTP,FPTSUS,FPTSWS,FPTSPT)"
             . " VALUES ('$port','','1.00','$fpid','','$etitle','','','"
             . $hdrTstp[$port] . "','BILL','DSP01','Y')";
        $r = db2_exec($conn, $sql);
        if ($r) {
            $ins++;
            echo '<span class="ok">OK</span> SYPORT header '.$port.'<br>';
        } else {
            $err++; $totalErr++;
            echo '<span class="err">ERR</span> SYPORT header '.$port.': '
               . htmlspecialchars(db2_stmt_errormsg()) . '<br>';
        }
    }
    echo '<b>SYPORT headers: ins='.$ins.' skip='.$skip.' err='.$err.'</b><br>';

    // --------------------------------------------------
    // B. SYPORT sub-pages (30 = 5 portals x 6 cats)
    // FPPAGE = portal name, FPID = PORTAL_CAT
    // --------------------------------------------------
    echo '<h2>B. SYPORT Sub-Pages (30)</h2>';
    $ins = 0; $skip = 0; $err = 0;
    foreach ($portals as $port => $ptitle) {
        foreach ($cats as $cat => $cInfo) {
            $fppage = $port;
            $fpid   = $port . '_' . $cat;
            $fpseq  = $cInfo['seq'];
            $fpdesc = str_replace("'", "''", $cInfo['desc']);
            $fptitl = str_replace("'", "''", $ptitle . ' - ' . $cInfo['desc']);
            $ck = db2_fetch_assoc(db2_exec($conn,
                "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT"
                . " WHERE FPPORT='$port' AND FPID='$fpid'"));
            if ((int)$ck['N'] > 0) { $skip++; continue; }
            $sql = "INSERT INTO S5HDSDATA.SYPORT"
                 . " (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,"
                 . "FPTSTP,FPTSUS,FPTSWS,FPTSPT)"
                 . " VALUES ('$port','$fppage','$fpseq','$fpid','$fpdesc','$fptitl','','',"
                 . "'$now','BILL','','')";
            $r = db2_exec($conn, $sql);
            if ($r) {
                $ins++;
            } else {
                $err++; $totalErr++;
                echo '<span class="err">ERR</span> SYPORT sub '.$port.'/'.$cat.': '
                   . htmlspecialchars(db2_stmt_errormsg()) . '<br>';
            }
        }
    }
    echo '<b>SYPORT sub-pages: ins='.$ins.' skip='.$skip.' err='.$err.'</b><br>';

    // --------------------------------------------------
    // C. SYROLD (23 roles x 5 portals = 115)
    // --------------------------------------------------
    echo '<h2>C. SYROLD (115 rows)</h2>';
    $ins = 0; $skip = 0; $err = 0;
    foreach ($roles as $role) {
        foreach ($portSeq as $port => $seqn) {
            $ck = db2_fetch_assoc(db2_exec($conn,
                "SELECT COUNT(*) AS N FROM S5HDSDATA.SYROLD"
                . " WHERE TRIM(RDROLE)='$role' AND TRIM(RDPORT)='$port'"));
            if ((int)$ck['N'] > 0) { $skip++; continue; }
            $sql = "INSERT INTO S5HDSDATA.SYROLD"
                 . " (RDROLE,RDPORT,RDSEQN,RDRESV,RDTSTP,RDTSUS,RDTSWS,RDTSPT)"
                 . " VALUES ('$role','$port','$seqn','','$now','BILL','','')";
            $r = db2_exec($conn, $sql);
            if ($r) {
                $ins++;
            } else {
                $err++; $totalErr++;
                echo '<span class="err">ERR</span> SYROLD '.$role.'/'.$port.': '
                   . htmlspecialchars(db2_stmt_errormsg()) . '<br>';
            }
        }
    }
    echo '<b>SYROLD: ins='.$ins.' skip='.$skip.' err='.$err.'</b><br>';

    // --------------------------------------------------
    // D. SYPORR template from CUSTSRVC/ACAREPORTING
    // --------------------------------------------------
    echo '<h2>D. SYPORR (115 headers + 690 sub-pages)</h2>';
    $ptRow = db2_exec($conn,
        "SELECT * FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC'"
        . " FETCH FIRST 1 ROWS ONLY");
    $porrTmpl = db2_fetch_assoc($ptRow);
    if (!$porrTmpl) {
        echo '<span class="err">ERR</span> Cannot load SYPORR template from CUSTSRVC.'
           . ' Cannot insert SYPORR rows.<br>';
        $totalErr++;
    } else {
        $porrCols = array_keys($porrTmpl);
        $pIns = 0; $pSkip = 0; $pErr = 0;

        // Build list of all rows to insert: array of [role, port, prpage, prseq]
        $porrRows = array();
        foreach ($roles as $role) {
            foreach ($portals as $port => $ptitle) {
                $porrRows[] = array($role, $port, '',     '1.00');
                foreach ($cats as $cat => $cInfo) {
                    $porrRows[] = array($role, $port, $port, $cInfo['seq']);
                }
            }
        }

        foreach ($porrRows as $pr) {
            $role   = $pr[0]; $port  = $pr[1];
            $prpage = $pr[2]; $prseq = $pr[3];
            $eppage = str_replace("'", "''", $prpage);
            $ck = db2_fetch_assoc(db2_exec($conn,
                "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR"
                . " WHERE TRIM(PRROLE)='$role' AND TRIM(PRPORT)='$port'"
                . " AND TRIM(PRPAGE)='$eppage' AND TRIM(PRSEQ)='$prseq'"));
            if ((int)$ck['N'] > 0) { $pSkip++; continue; }
            $prid = substr(
                $role . '/' . $port . ($prpage !== '' ? '/' . $prpage . '/' . $prseq : ''),
                0, 55);
            $vals = array();
            foreach ($porrCols as $c) {
                if      ($c === 'PRROLE') $vals[] = "'$role'";
                elseif  ($c === 'PRPORT') $vals[] = "'$port'";
                elseif  ($c === 'PRPAGE') $vals[] = "'$eppage'";
                elseif  ($c === 'PRSEQ')  $vals[] = "'$prseq'";
                elseif  ($c === 'PRID')   $vals[] = "'" . str_replace("'","''",$prid) . "'";
                elseif  ($c === 'PRTSTP') $vals[] = "'$now'";
                elseif  ($c === 'PRTSUS') $vals[] = "'BILL'";
                elseif  ($c === 'PRTSWS') $vals[] = "''";
                elseif  ($c === 'PRTSPT') $vals[] = "''";
                else    $vals[] = "'" . str_replace("'","''",(string)rtrim($porrTmpl[$c])) . "'";
            }
            $sql = "INSERT INTO S5HDSDATA.SYPORR (" . implode(',', $porrCols) . ")"
                 . " VALUES (" . implode(',', $vals) . ")";
            $r = db2_exec($conn, $sql);
            if ($r) {
                $pIns++;
            } else {
                $pErr++; $totalErr++;
                echo '<span class="err">ERR</span> SYPORR '.$role.'/'.$port.'/'
                   . ($prpage ? $prpage : 'hdr') . '/' . $prseq . ': '
                   . htmlspecialchars(db2_stmt_errormsg()) . '<br>';
            }
        }
        echo '<b>SYPORR: ins='.$pIns.' skip='.$pSkip.' err='.$pErr.'</b><br>';
    }

    // --------------------------------------------------
    // E. SYDSGN: set PDUSER=' ' to fix Configuration/Portal
    // --------------------------------------------------
    echo '<h2>E. SYDSGN Fix</h2>';
    $r = db2_exec($conn,
        "UPDATE SG5STDPGM.SYDSGN SET PDUSER=' ' WHERE PDTBID=199 AND PDPGID=0");
    if ($r) {
        $rows = db2_num_rows($r);
        echo '<span class="ok">OK</span> SYDSGN PDUSER set to blank: '.$rows.' row(s)<br>';
    } else {
        $totalErr++;
        echo '<span class="err">ERR</span> SYDSGN: '
           . htmlspecialchars(db2_stmt_errormsg()) . '<br>';
    }

    // --------------------------------------------------
    // F. Verify counts
    // --------------------------------------------------
    echo '<h2>F. Verification</h2>';
    $checks = array(
        'SYPORT headers'   => "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pList) AND TRIM(FPPAGE)=''",
        'SYPORT sub-pages' => "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pList) AND TRIM(FPPAGE)<>''",
        'SYROLD rows'      => "SELECT COUNT(*) AS N FROM S5HDSDATA.SYROLD WHERE RDPORT IN ($pList)",
        'SYPORR headers'   => "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList) AND TRIM(PRPAGE)=''",
        'SYPORR sub-pages' => "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList) AND TRIM(PRPAGE)<>''",
    );
    $expected = array(5, 30, 115, 115, 690);
    $i = 0;
    foreach ($checks as $label => $sql) {
        $row = db2_fetch_assoc(db2_exec($conn, $sql));
        $n   = (int)$row['N'];
        $exp = $expected[$i++];
        $cls = ($n === $exp) ? 'ok' : 'err';
        echo '<span class="'.$cls.'">'.$label.': '.$n.' (expected '.$exp.')</span><br>';
    }

    if ($totalErr === 0) {
        echo '<br><span class="ok">ALL DONE - 0 errors. Log SG5TEST out and back in.</span>';
    } else {
        echo '<br><span class="err">DONE WITH '.$totalErr.' ERRORS - check above.</span>';
    }
    echo '</body></html>';
    exit;
}

// -------------------------------------------------------
// DIAGNOSTIC PAGE (default GET)
// -------------------------------------------------------
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Restore All</title>
<style>
body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 6px;white-space:nowrap;font-size:11px;}
th{background:#ddd;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
.miss{background:#fdd;font-weight:bold;}.good{background:#dfd;}
.step{border:1px solid #999;padding:10px;margin:8px 0;background:#f9f9f9;}
.warn{background:#fee;border:2px solid #c00;padding:10px;margin:10px 0;}
</style></head><body><h1>SG Restore All -- Diagnostic</h1>';

echo '<div class="warn"><b>This script restores:</b><ul style="margin:6px 0 0 18px;line-height:1.8;">'
   . '<li>SYPORT: 5 portal headers + 30 sub-page rows</li>'
   . '<li>SYROLD: 23 roles x 5 portals = 115 rows</li>'
   . '<li>SYPORR: 23 roles x 5 portals x 7 rows = 805 rows (115 headers + 690 sub-pages)</li>'
   . '<li>SYDSGN: PDUSER set to blank (fixes Configuration/Portal error)</li>'
   . '</ul>'
   . '<b>SYURLM is NOT touched</b> (was never deleted -- verify below).</div>';

// 1. SYPORT state
echo '<h2>1. SYPORT for SG portals</h2>';
$r = db2_exec($conn, "SELECT FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPRESV"
    . " FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pList) ORDER BY FPPORT,FPSEQ");
echo '<table><tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPID</th>'
   . '<th>FPDESC</th><th>FPRESV</th></tr>';
$cnt = 0;
if ($r) while ($row = db2_fetch_assoc($r)) {
    echo '<tr><td>'.htmlspecialchars(trim($row['FPPORT'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FPPAGE'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FPSEQ'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FPID'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FPDESC'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FPRESV'])).'</td></tr>';
    $cnt++;
} else echo '<tr><td colspan="6" class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
$cls = ($cnt === 35) ? 'good' : 'miss';
echo '</table><p class="'.$cls.'">'.$cnt.' rows (need 35: 5 headers + 30 sub-pages)</p>';

// 2. SYROLD state
echo '<h2>2. SYROLD for SG portals</h2>';
$r = db2_exec($conn, "SELECT COUNT(*) AS N FROM S5HDSDATA.SYROLD WHERE RDPORT IN ($pList)");
$row = db2_fetch_assoc($r);
$n = (int)$row['N'];
$cls = ($n === 115) ? 'good' : 'miss';
echo '<p class="'.$cls.'">'.$n.' rows (need 115)</p>';

// 3. SYPORR state
echo '<h2>3. SYPORR for SG portals</h2>';
$r = db2_exec($conn, "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR"
    . " WHERE PRPORT IN ($pList) AND TRIM(PRROLE) IN ($rList)");
$row = db2_fetch_assoc($r);
$n = (int)$row['N'];
$cls = ($n === 805) ? 'good' : 'miss';
echo '<p class="'.$cls.'">'.$n.' rows (need 805)</p>';

// 4. SYURLM state
echo '<h2>4. SYURLM SG entries (informational)</h2>';
$r = db2_exec($conn, "SELECT FUID,FUTITL,FUTRGT,"
    . "SUBSTR(FUURL,1,70) AS FUURL_SHORT,FURESV"
    . " FROM S5HDSDATA.SYURLM WHERE FUID LIKE 'SG%' ORDER BY FUID");
echo '<table><tr><th>FUID</th><th>FUTITL</th><th>FUTRGT</th><th>FUURL(70)</th>'
   . '<th>FURESV</th></tr>';
$ucnt = 0;
if ($r) while ($row = db2_fetch_assoc($r)) {
    $nourl = (trim($row['FUURL_SHORT']) === '');
    echo '<tr'.($nourl?' style="background:#ffc;"':'').'>'
       . '<td>'.htmlspecialchars(trim($row['FUID'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FUTITL'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FUTRGT'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FUURL_SHORT'])).'</td>'
       . '<td>'.htmlspecialchars(trim($row['FURESV'])).'</td></tr>';
    $ucnt++;
} else echo '<tr><td colspan="5" class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</td></tr>';
echo '</table><p>'.$ucnt.' SYURLM rows starting with SG (expect 40: 5 headers + 35 entries)</p>';

// 5. SYDSGN state
echo '<h2>5. SYDSGN state</h2>';
$r = db2_exec($conn,
    "SELECT PDTBID,PDPGID,PDUSER FROM SG5STDPGM.SYDSGN WHERE PDTBID=199 AND PDPGID=0");
$row = db2_fetch_assoc($r);
if ($row) {
    $pduser = rtrim($row['PDUSER']);
    $cls = ($pduser === '' || $pduser === ' ') ? 'ok' : 'err';
    echo '<span class="'.$cls.'">PDTBID=199 PDPGID=0 PDUSER='.htmlspecialchars(var_export($pduser,true)).'</span>'
       . ($cls === 'err' ? ' &larr; will be fixed to blank' : ' (already OK)') . '<br>';
} else {
    echo '<span class="err">Row not found</span><br>';
}

// 6. SYPORR template check
echo '<h2>6. SYPORR template (CUSTSRVC)</h2>';
$r = db2_exec($conn,
    "SELECT PRROLE,PRPORT,PRPAGE,PRSEQ FROM S5HDSDATA.SYPORR"
    . " WHERE TRIM(PRROLE)='CUSTSRVC' FETCH FIRST 3 ROWS ONLY");
$tmplOk = false;
if ($r) {
    echo '<table><tr><th>PRROLE</th><th>PRPORT</th><th>PRPAGE</th><th>PRSEQ</th></tr>';
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr><td>'.htmlspecialchars(trim($row['PRROLE'])).'</td>'
           . '<td>'.htmlspecialchars(trim($row['PRPORT'])).'</td>'
           . '<td>'.htmlspecialchars(trim($row['PRPAGE'])).'</td>'
           . '<td>'.htmlspecialchars(trim($row['PRSEQ'])).'</td></tr>';
        $tmplOk = true;
    }
    echo '</table>';
    if (!$tmplOk) echo '<span class="err">No CUSTSRVC rows found -- SYPORR restore will FAIL</span><br>';
    else echo '<span class="ok">Template OK</span><br>';
} else {
    echo '<span class="err">Query failed: '.htmlspecialchars(db2_stmt_errormsg()).'</span><br>';
}

// Action buttons
echo '<div class="step"><b>Step 1:</b> '
   . '<a href="?action=backup" style="background:#060;color:#fff;padding:6px 16px;'
   . 'text-decoration:none;">Download Backup</a>'
   . ' (download before executing)</div>';
echo '<div class="step"><b>Step 2:</b> '
   . '<form method="post"><button name="restore" value="1" style="background:#c00;'
   . 'color:#fff;padding:8px 24px;font-size:13px;">'
   . 'Restore All (SYPORT + SYROLD + SYPORR + SYDSGN)</button></form></div>';
echo '</body></html>';
?>
