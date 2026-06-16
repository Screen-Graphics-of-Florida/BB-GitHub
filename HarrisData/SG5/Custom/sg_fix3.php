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

// -------------------------------------------------------
// BACKUP
// -------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array('-- sg_fix3 pre-backup -- ' . date('Y-m-d H:i:s'), '');
    $sql   = "SELECT * FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList)"
           . " AND TRIM(PRROLE) IN ($rList) ORDER BY PRPORT,PRROLE,PRPAGE,PRSEQ";
    $r = db2_exec($conn, $sql);
    $nc = db2_num_fields($r); $cols = array();
    for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($r, $i);
    $cnt = 0;
    while ($row = db2_fetch_assoc($r)) {
        $vals = array();
        foreach ($cols as $c)
            $vals[] = "'".str_replace("'","''",(string)rtrim($row[$c]))."'";
        $lines[] = 'INSERT INTO SYPORR ('.implode(',',$cols).') VALUES ('.implode(',',$vals).');';
        $cnt++;
    }
    $lines[] = '-- '.$cnt.' rows';
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sg_fix3_backup_'.date('Ymd_His').'.sql"');
    header('Cache-Control: no-cache, no-store');
    echo implode("\r\n", $lines);
    exit;
}

// -------------------------------------------------------
// EXECUTE
// -------------------------------------------------------
if (isset($_POST['fix'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix3</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
</style></head><body><h1>SG Fix3 -- PRID Correction</h1>';

    $totalErr = 0;

    // A. Fix SYPORR header PRID: should be PRPORT||'/PORTAL'
    echo '<h2>A. Fix SYPORR portal header PRID (set to PRPORT/PORTAL)</h2>';
    $r = db2_exec($conn,
        "UPDATE S5HDSDATA.SYPORR SET PRID = TRIM(PRPORT)||'/PORTAL'"
        . " WHERE PRPORT IN ($pList) AND TRIM(PRROLE) IN ($rList)"
        . " AND TRIM(PRPAGE)=''");
    if ($r) echo '<span class="ok">OK</span> '.db2_num_rows($r).' header rows updated<br>';
    else { $totalErr++; echo '<span class="err">ERR</span> '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }

    // B. Fix SYPORR sub-page PRID: should be PRPORT||'_'||cat
    //    PRSEQ 3=ACCT 4=INVMGMT 5=MFG 6=OE 7=PLN 8=PUR
    echo '<h2>B. Fix SYPORR sub-page PRID (set to PRPORT_CAT)</h2>';
    $r = db2_exec($conn,
        "UPDATE S5HDSDATA.SYPORR SET PRID ="
        . " TRIM(PRPORT)||CASE TRIM(PRSEQ)"
        . " WHEN '3.00' THEN '_ACCT'"
        . " WHEN '4.00' THEN '_INVMGMT'"
        . " WHEN '5.00' THEN '_MFG'"
        . " WHEN '6.00' THEN '_OE'"
        . " WHEN '7.00' THEN '_PLN'"
        . " WHEN '8.00' THEN '_PUR'"
        . " ELSE '_UNKNOWN' END"
        . " WHERE PRPORT IN ($pList) AND TRIM(PRROLE) IN ($rList)"
        . " AND TRIM(PRPAGE)<>''");
    if ($r) echo '<span class="ok">OK</span> '.db2_num_rows($r).' sub-page rows updated<br>';
    else { $totalErr++; echo '<span class="err">ERR</span> '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }

    // C. Verify PRID values look right (sample)
    echo '<h2>C. SYPORR sample after fix -- CUSTSRVC/SGINQ</h2>';
    $r = db2_exec($conn,
        "SELECT TRIM(PRPAGE) AS PRPAGE, TRIM(PRSEQ) AS PRSEQ, TRIM(PRID) AS PRID"
        . " FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC'"
        . " AND TRIM(PRPORT)='SGINQ' ORDER BY PRSEQ");
    echo '<table style="border-collapse:collapse">'
       . '<tr><th style="border:1px solid #bbb;padding:2px 6px">PRPAGE</th>'
       . '<th style="border:1px solid #bbb;padding:2px 6px">PRSEQ</th>'
       . '<th style="border:1px solid #bbb;padding:2px 6px">PRID</th></tr>';
    if ($r) while ($row = db2_fetch_assoc($r)) {
        $pg = trim($row['PRPAGE']);
        echo '<tr><td style="border:1px solid #bbb;padding:2px 6px">'
           . htmlspecialchars($pg==='' ? '(header)' : $pg) . '</td>'
           . '<td style="border:1px solid #bbb;padding:2px 6px">'
           . htmlspecialchars(trim($row['PRSEQ'])) . '</td>'
           . '<td style="border:1px solid #bbb;padding:2px 6px">'
           . htmlspecialchars(trim($row['PRID'])) . '</td></tr>';
    }
    echo '</table>';

    // D. Check PRID matches SYURLM FUID
    echo '<h2>D. PRID vs SYURLM cross-check for SGINQ</h2>';
    $r = db2_exec($conn,
        "SELECT TRIM(P.PRID) AS PRID,"
        . " CASE WHEN U.FUID IS NULL THEN 'MISSING' ELSE 'OK' END AS URLM_STATUS,"
        . " TRIM(U.FUDESC) AS FUDESC"
        . " FROM S5HDSDATA.SYPORR P"
        . " LEFT JOIN S5HDSDATA.SYURLM U ON TRIM(U.FUID) = TRIM(P.PRID)"
        . " WHERE TRIM(P.PRROLE)='CUSTSRVC' AND TRIM(P.PRPORT)='SGINQ'"
        . " ORDER BY P.PRSEQ");
    echo '<table style="border-collapse:collapse">'
       . '<tr><th style="border:1px solid #bbb;padding:2px 6px">PRID</th>'
       . '<th style="border:1px solid #bbb;padding:2px 6px">SYURLM match</th>'
       . '<th style="border:1px solid #bbb;padding:2px 6px">FUDESC</th></tr>';
    if ($r) while ($row = db2_fetch_assoc($r)) {
        $ok  = ($row['URLM_STATUS'] === 'OK');
        $cls = $ok ? 'color:green' : 'color:red;font-weight:bold';
        echo '<tr><td style="border:1px solid #bbb;padding:2px 6px">'
           . htmlspecialchars(trim($row['PRID'])) . '</td>'
           . '<td style="border:1px solid #bbb;padding:2px 6px;'.$cls.'">'
           . htmlspecialchars($row['URLM_STATUS']) . '</td>'
           . '<td style="border:1px solid #bbb;padding:2px 6px">'
           . htmlspecialchars(trim($row['FUDESC'])) . '</td></tr>';
    }
    echo '</table>';

    if ($totalErr === 0)
        echo '<br><span class="ok">DONE -- 0 errors. Log SG5TEST out and back in to see sub-menus.</span>';
    else
        echo '<br><span class="err">DONE WITH '.$totalErr.' ERRORS -- check above.</span>';
    echo '</body></html>';
    exit;
}

// -------------------------------------------------------
// DIAGNOSTIC
// -------------------------------------------------------
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix3</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
.step{border:1px solid #999;padding:10px;margin:8px 0;background:#f9f9f9;}
</style></head><body><h1>SG Fix3 -- PRID Correction</h1>
<p><b>Root cause:</b> SYPORR.PRID must equal SYPORT.FPID (which equals SYURLM.FUID).<br>
sg_fix2.php built PRID from the role name. This fix UPDATEs PRID to the correct values:<br>
Headers: PRPORT||<b>/PORTAL</b> &nbsp; Sub-pages: PRPORT||<b>_CAT</b></p>';

// Show current wrong PRID for CUSTSRVC/SGINQ
echo '<h2>Current PRID (WRONG) for CUSTSRVC/SGINQ</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(PRPAGE) AS PRPAGE, TRIM(PRSEQ) AS PRSEQ, TRIM(PRID) AS PRID"
    . " FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC'"
    . " AND TRIM(PRPORT)='SGINQ' ORDER BY PRSEQ");
echo '<table style="border-collapse:collapse">'
   . '<tr><th style="border:1px solid #bbb;padding:2px 6px">PRPAGE</th>'
   . '<th style="border:1px solid #bbb;padding:2px 6px">PRSEQ</th>'
   . '<th style="border:1px solid #bbb;padding:2px 6px">PRID (current)</th>'
   . '<th style="border:1px solid #bbb;padding:2px 6px">PRID (needed)</th></tr>';
$seqMap = array(
    '1.00' => 'SGINQ/PORTAL',
    '3.00' => 'SGINQ_ACCT',
    '4.00' => 'SGINQ_INVMGMT',
    '5.00' => 'SGINQ_MFG',
    '6.00' => 'SGINQ_OE',
    '7.00' => 'SGINQ_PLN',
    '8.00' => 'SGINQ_PUR',
);
if ($r) while ($row = db2_fetch_assoc($r)) {
    $seq   = trim($row['PRSEQ']);
    $need  = isset($seqMap[$seq]) ? $seqMap[$seq] : '?';
    $match = (trim($row['PRID']) === $need);
    $bg    = $match ? 'background:#dfd' : 'background:#fdd';
    echo '<tr style="'.$bg.'"><td style="border:1px solid #bbb;padding:2px 6px">'
       . htmlspecialchars(trim($row['PRPAGE'])==='' ? '(header)' : trim($row['PRPAGE'])) . '</td>'
       . '<td style="border:1px solid #bbb;padding:2px 6px">'.$seq.'</td>'
       . '<td style="border:1px solid #bbb;padding:2px 6px">'
       . htmlspecialchars(trim($row['PRID'])) . '</td>'
       . '<td style="border:1px solid #bbb;padding:2px 6px;font-weight:bold">'
       . htmlspecialchars($need) . '</td></tr>';
}
echo '</table>';

echo '<div class="step"><b>Step 1:</b> <a href="?action=backup" style="background:#060;color:#fff;'
   . 'padding:6px 14px;text-decoration:none;">Download Backup</a></div>';
echo '<div class="step"><b>Step 2:</b> <form method="post">'
   . '<button name="fix" value="1" style="background:#c00;color:#fff;padding:8px 24px;font-size:13px;">'
   . 'Fix PRID Values</button></form></div>';
echo '</body></html>';
?>
