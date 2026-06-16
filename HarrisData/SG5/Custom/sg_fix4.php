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

if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array('-- sg_fix4 pre-backup -- '.date('Y-m-d H:i:s'),'');
    $r = db2_exec($conn,
        "SELECT * FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList)"
        . " AND TRIM(PRROLE) IN ($rList) ORDER BY PRPORT,PRROLE,PRPAGE,PRSEQ");
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
    header('Content-Disposition: attachment; filename="sg_fix4_backup_'.date('Ymd_His').'.sql"');
    header('Cache-Control: no-cache, no-store');
    echo implode("\r\n",$lines);
    exit;
}

if (isset($_POST['fix'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix4</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
</style></head><body><h1>SG Fix4 -- Set PRTSPT=Y</h1>';

    $totalErr = 0;

    // A. Set PRTSPT='Y' on all SG SYPORR rows
    echo '<h2>A. UPDATE SYPORR SET PRTSPT=Y</h2>';
    $r = db2_exec($conn,
        "UPDATE S5HDSDATA.SYPORR SET PRTSPT='Y'"
        . " WHERE PRPORT IN ($pList) AND TRIM(PRROLE) IN ($rList)");
    if ($r) echo '<span class="ok">OK</span> '.db2_num_rows($r).' rows updated<br>';
    else { $totalErr++; echo '<span class="err">ERR</span> '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }

    // B. Also set FURESV='Y' on SYURLM portal headers (matches ACAREPORTING pattern)
    echo '<h2>B. UPDATE SYURLM portal headers FURESV=Y</h2>';
    $r = db2_exec($conn,
        "UPDATE S5HDSDATA.SYURLM SET FURESV='Y'"
        . " WHERE TRIM(FUID) IN ('SGINQ/PORTAL','SGDASH/PORTAL','SGDINT/PORTAL',"
        . "'SGRPT/PORTAL','SGSOP/PORTAL')");
    if ($r) echo '<span class="ok">OK</span> '.db2_num_rows($r).' SYURLM portal header rows updated<br>';
    else { $totalErr++; echo '<span class="err">ERR</span> '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }

    // C. Verify sample
    echo '<h2>C. Verify -- SYPORR CUSTSRVC/SGINQ after fix</h2>';
    $r = db2_exec($conn,
        "SELECT TRIM(PRPAGE) AS PRPAGE, TRIM(PRSEQ) AS PRSEQ,"
        . " TRIM(PRID) AS PRID, TRIM(PRSEL) AS PRSEL, TRIM(PRTSPT) AS PRTSPT"
        . " FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC'"
        . " AND TRIM(PRPORT)='SGINQ' ORDER BY PRSEQ");
    echo '<table style="border-collapse:collapse">'
       . '<tr><th style="border:1px solid #bbb;padding:2px 6px">PRPAGE</th>'
       . '<th style="border:1px solid #bbb;padding:2px 6px">PRSEQ</th>'
       . '<th style="border:1px solid #bbb;padding:2px 6px">PRID</th>'
       . '<th style="border:1px solid #bbb;padding:2px 6px">PRSEL</th>'
       . '<th style="border:1px solid #bbb;padding:2px 6px">PRTSPT</th></tr>';
    if ($r) while ($row = db2_fetch_assoc($r)) {
        $tsptOk = (trim($row['PRTSPT']) === 'Y');
        echo '<tr>'
           . '<td style="border:1px solid #bbb;padding:2px 6px">'
           . htmlspecialchars(trim($row['PRPAGE'])===''?'(header)':trim($row['PRPAGE'])).'</td>'
           . '<td style="border:1px solid #bbb;padding:2px 6px">'.htmlspecialchars(trim($row['PRSEQ'])).'</td>'
           . '<td style="border:1px solid #bbb;padding:2px 6px">'.htmlspecialchars(trim($row['PRID'])).'</td>'
           . '<td style="border:1px solid #bbb;padding:2px 6px">'.htmlspecialchars(trim($row['PRSEL'])).'</td>'
           . '<td style="border:1px solid #bbb;padding:2px 6px;'.($tsptOk?'color:green':'color:red;font-weight:bold').'">'
           . htmlspecialchars(trim($row['PRTSPT'])===''?'(empty - WRONG)':'Y - OK').'</td></tr>';
    }
    echo '</table>';

    if ($totalErr === 0)
        echo '<br><span class="ok">DONE -- 0 errors. Log SG5TEST out and back in.</span>';
    else
        echo '<br><span class="err">DONE WITH '.$totalErr.' ERRORS.</span>';
    echo '</body></html>';
    exit;
}

// Diagnostic
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix4</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
.err{color:red;font-weight:bold;}.step{border:1px solid #999;padding:10px;margin:8px 0;background:#f9f9f9;}
</style></head><body><h1>SG Fix4 -- Set PRTSPT=Y</h1>
<p><b>Fix:</b> ACAREPORTING and CUSTOMER portals have PRTSPT=Y on every SYPORR row.<br>
SG portal rows have PRTSPT=(empty). This UPDATE sets PRTSPT=Y for all 805 rows.</p>';

$row = db2_fetch_assoc(db2_exec($conn,
    "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList)"
    . " AND TRIM(PRROLE) IN ($rList) AND TRIM(PRTSPT)<>'Y'"));
$n = (int)$row['N'];
echo '<p>Rows with PRTSPT &lt;&gt; Y: <b style="color:'.($n>0?'red':'green').'">'.$n.'</b> (need 0 after fix)</p>';

echo '<div class="step"><b>Step 1:</b> <a href="?action=backup" style="background:#060;color:#fff;'
   . 'padding:6px 14px;text-decoration:none;">Download Backup</a></div>';
echo '<div class="step"><b>Step 2:</b> <form method="post">'
   . '<button name="fix" value="1" style="background:#c00;color:#fff;padding:8px 24px;font-size:13px;">'
   . 'Set PRTSPT=Y on all SG portal rows</button></form></div>';
echo '</body></html>';
?>
