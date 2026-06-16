<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

function tbl($r, $cols) {
    echo '<table style="border-collapse:collapse;margin:4px 0 10px;">';
    echo '<tr>';
    foreach ($cols as $c)
        echo '<th style="border:1px solid #999;padding:2px 8px;background:#ddd;">'
           . htmlspecialchars($c) . '</th>';
    echo '</tr>';
    $cnt = 0;
    if ($r) {
        while ($row = db2_fetch_assoc($r)) {
            echo '<tr>';
            foreach ($cols as $c) {
                $v   = isset($row[$c]) ? trim((string)$row[$c]) : '?';
                $bg  = '';
                echo '<td style="border:1px solid #bbb;padding:2px 8px;'.$bg.'">'
                   . htmlspecialchars($v === '' ? '(empty)' : $v) . '</td>';
            }
            echo '</tr>';
            $cnt++;
        }
    } else {
        echo '<tr><td colspan="'.count($cols).'" style="color:red;padding:4px">'
           . htmlspecialchars(db2_stmt_errormsg()) . '</td></tr>';
    }
    echo '</table><p style="margin:2px 0 10px;color:#666;">'.$cnt.' rows</p>';
}

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Diag2</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h1{font-size:14px;} h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
h3{color:#060;margin:10px 0 2px;} .ok{color:green;font-weight:bold;}
.err{color:red;font-weight:bold;} .warn{color:#c60;font-weight:bold;}
</style></head><body><h1>SG Nav Diagnostic 2 -- Working vs Broken Comparison</h1>';

// ---- 1. SYPORR for ACAREPORTING (working reference) ----
echo '<h2>1. SYPORR -- ACAREPORTING (WORKING reference) for CUSTSRVC</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(PRPORT) AS PRPORT, TRIM(PRPAGE) AS PRPAGE, TRIM(PRSEQ) AS PRSEQ,"
    . " TRIM(PRID) AS PRID"
    . " FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC'"
    . " AND TRIM(PRPORT)='ACAREPORTING' ORDER BY PRSEQ");
tbl($r, array('PRPORT','PRPAGE','PRSEQ','PRID'));

// ---- 2. SYPORR for SGINQ (broken) ----
echo '<h2>2. SYPORR -- SGINQ (BROKEN) for CUSTSRVC</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(PRPORT) AS PRPORT, TRIM(PRPAGE) AS PRPAGE, TRIM(PRSEQ) AS PRSEQ,"
    . " TRIM(PRID) AS PRID"
    . " FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='CUSTSRVC'"
    . " AND TRIM(PRPORT)='SGINQ' ORDER BY PRSEQ");
tbl($r, array('PRPORT','PRPAGE','PRSEQ','PRID'));

// ---- 3. SYPORT for ACAREPORTING ----
echo '<h2>3. SYPORT -- ACAREPORTING (WORKING reference)</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(FPPORT) AS FPPORT, TRIM(FPPAGE) AS FPPAGE, TRIM(FPSEQ) AS FPSEQ,"
    . " TRIM(FPID) AS FPID, TRIM(FPDESC) AS FPDESC"
    . " FROM S5HDSDATA.SYPORT WHERE TRIM(FPPORT)='ACAREPORTING' ORDER BY FPSEQ");
tbl($r, array('FPPORT','FPPAGE','FPSEQ','FPID','FPDESC'));

// ---- 4. SYPORT for SGINQ ----
echo '<h2>4. SYPORT -- SGINQ (BROKEN)</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(FPPORT) AS FPPORT, TRIM(FPPAGE) AS FPPAGE, TRIM(FPSEQ) AS FPSEQ,"
    . " TRIM(FPID) AS FPID, TRIM(FPDESC) AS FPDESC"
    . " FROM S5HDSDATA.SYPORT WHERE TRIM(FPPORT)='SGINQ' ORDER BY FPSEQ");
tbl($r, array('FPPORT','FPPAGE','FPSEQ','FPID','FPDESC'));

// ---- 5. SYURLM for ACAREPORTING ----
echo '<h2>5. SYURLM -- ACAREPORTING sub-pages (WORKING reference)</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(FUID) AS FUID, TRIM(FUTRGT) AS FUTRGT, TRIM(FUDESC) AS FUDESC,"
    . " TRIM(FURESV) AS FURESV, SUBSTR(FUURL,1,80) AS URL"
    . " FROM S5HDSDATA.SYURLM WHERE TRIM(FUID) LIKE 'ACAREPORTING%' ORDER BY FUID");
tbl($r, array('FUID','FUTRGT','FUDESC','FURESV','URL'));

// ---- 6. SYURLM for SGINQ ----
echo '<h2>6. SYURLM -- SGINQ sub-pages (BROKEN)</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(FUID) AS FUID, TRIM(FUTRGT) AS FUTRGT, TRIM(FUDESC) AS FUDESC,"
    . " TRIM(FURESV) AS FURESV, SUBSTR(FUURL,1,80) AS URL"
    . " FROM S5HDSDATA.SYURLM WHERE TRIM(FUID) LIKE 'SGINQ%' ORDER BY FUID");
tbl($r, array('FUID','FUTRGT','FUDESC','FURESV','URL'));

// ---- 7. SYURLM portal headers all SG ----
echo '<h2>7. SYURLM -- SG portal header rows (FUID ends /PORTAL)</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(FUID) AS FUID, TRIM(FUTRGT) AS FUTRGT,"
    . " SUBSTR(FUURL,1,80) AS URL"
    . " FROM S5HDSDATA.SYURLM"
    . " WHERE TRIM(FUID) IN ('SGINQ/PORTAL','SGDASH/PORTAL','SGDINT/PORTAL',"
    . "'SGRPT/PORTAL','SGSOP/PORTAL') ORDER BY FUID");
tbl($r, array('FUID','FUTRGT','URL'));

// ---- 8. SYURLM all SG* ----
echo '<h2>8. SYURLM -- ALL rows where FUID starts SG</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(FUID) AS FUID, TRIM(FUTRGT) AS FUTRGT,"
    . " SUBSTR(FUURL,1,60) AS URL"
    . " FROM S5HDSDATA.SYURLM WHERE TRIM(FUID) LIKE 'SG%' ORDER BY FUID");
tbl($r, array('FUID','FUTRGT','URL'));

// ---- 9. SYPORR counts ----
echo '<h2>9. SYPORR row counts for SG portals</h2>';
$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";
$rList = "'ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR',"
       . "'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN',"
       . "'MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION',"
       . "'PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM'";
$chks = array(
    array('SYPORR headers  (need 115)',
        "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList)"
        . " AND TRIM(PRROLE) IN ($rList) AND TRIM(PRPAGE)=''", 115),
    array('SYPORR sub-pages (need 690)',
        "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pList)"
        . " AND TRIM(PRROLE) IN ($rList) AND TRIM(PRPAGE)<>''", 690),
);
foreach ($chks as $c) {
    $row = db2_fetch_assoc(db2_exec($conn, $c[1]));
    $n   = (int)$row['N'];
    $cls = ($n === $c[2]) ? 'ok' : 'err';
    echo '<span class="'.$cls.'">'.$c[0].': '.$n.'</span><br>';
}

// ---- 10. SYROLD for HD_ALL_SG ----
echo '<h2>10. SYROLD for HD_ALL_SG</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(RDROLE) AS RDROLE, TRIM(RDPORT) AS RDPORT, TRIM(RDSEQN) AS RDSEQN"
    . " FROM S5HDSDATA.SYROLD WHERE TRIM(RDROLE)='HD_ALL_SG' ORDER BY RDSEQN");
tbl($r, array('RDROLE','RDPORT','RDSEQN'));

echo '</body></html>';
?>
