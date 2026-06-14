<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";
$rList = "'ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR','EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN','MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION','PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM'";

if (isset($_POST['revert'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Revert</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}</style></head><body>';
    echo '<h1>Full Revert</h1>';

    // 1. Delete ALL SYPORT rows with non-blank FPPAGE for SG portals
    $r1 = db2_exec($conn, "DELETE FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") AND TRIM(FPPAGE)<>''");
    if ($r1) echo '<span class="ok">OK</span> SYPORT sub-pages deleted: '.db2_num_rows($r1).' rows<br>';
    else     echo '<span class="err">ERR</span> SYPORT: '.htmlspecialchars(db2_stmt_errormsg()).'<br>';

    // 2. Delete ALL SYPORR rows (portal headers + sub-pages) for SG portals x 23 roles
    $r2 = db2_exec($conn, "DELETE FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE) IN (".$rList.") AND PRPORT IN (".$pList.")");
    if ($r2) echo '<span class="ok">OK</span> SYPORR all SG rows deleted: '.db2_num_rows($r2).' rows<br>';
    else     echo '<span class="err">ERR</span> SYPORR: '.htmlspecialchars(db2_stmt_errormsg()).'<br>';

    // Verify
    $v1 = db2_fetch_assoc(db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") AND TRIM(FPPAGE)<>''"));
    $v2 = db2_fetch_assoc(db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE PRPORT IN (".$pList.") AND TRIM(PRROLE) IN (".$rList.")"));
    echo '<br>SYPORT sub-page rows remaining: '.(int)$v1['N'].' (should be 0)<br>';
    echo 'SYPORR SG rows remaining: '.(int)$v2['N'].' (should be 0)<br>';
    echo '<br><strong>Done. Log out and back in.</strong>';
    echo '</body></html>'; exit;
}

// Counts
$c1 = db2_fetch_assoc(db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.") AND TRIM(FPPAGE)<>''"));
$c2 = db2_fetch_assoc(db2_exec($conn,"SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE) IN (".$rList.") AND PRPORT IN (".$pList.")"));

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Full Revert</title>
<style>body{font-family:monospace;font-size:12px;padding:20px;}
.warn{background:#fee;border:2px solid #c00;padding:14px;margin:12px 0;font-size:13px;}
</style></head><body><h1>Full Revert: All SG portal SYPORT + SYPORR changes</h1>';
echo '<div class="warn"><b>Will DELETE:</b><br><br>';
echo '&bull; SYPORT sub-page rows for SG portals: <b>'.(int)$c1['N'].'</b> rows<br>';
echo '&bull; SYPORR ALL rows for SG portals x 23 roles: <b>'.(int)$c2['N'].'</b> rows<br><br>';
echo 'This reverts sg_porr_fix.php + sg_porr_pages_fix.php + sg_syport_fix.php completely.</div>';
echo '<form method="post"><button name="revert" value="1" style="background:#c00;color:#fff;padding:10px 28px;font-size:14px;">REVERT ALL NOW</button></form>';
echo '</body></html>';
?>
