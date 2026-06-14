<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

$sgPList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";
$roles = array(
    'ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR',
    'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN',
    'MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION',
    'PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM',
);

// -------------------------------------------------------
// BACKUP
// -------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sg_fix5_backup_'.date('Ymd_His').'.sql"');
    header('Cache-Control: no-cache, no-store');
    echo '-- sg_fix5 backup -- '.date('Y-m-d H:i:s')."\r\n";
    echo "-- UNDO: DELETE FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE) IN (rList) AND TRIM(PRPORT) NOT IN ($sgPList) AND PRTSUS='BILL' AND PRTSTP >= '2026-06-13';\r\n\r\n";
    // Affected roles backup
    foreach ($roles as $role) {
        $r = db2_exec($conn,
            "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='$role'"
            . " AND TRIM(PRPORT) NOT IN ($sgPList)");
        $row = db2_fetch_assoc($r);
        if ((int)$row['N'] === 0)
            echo "-- $role: 0 non-SG rows (AFFECTED -- will get rows added)\r\n";
    }
    exit;
}

// -------------------------------------------------------
// EXECUTE
// -------------------------------------------------------
if (isset($_POST['fix'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix5</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
.skip{color:#888;} h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
</style></head><body><h1>SG Fix5 -- Add missing SYPORR rows for standard portals</h1>';

    $now = date('Y-m-d-H.i.s.000000');
    $totalIns = 0; $totalErr = 0;

    foreach ($roles as $role) {
        // Count existing non-SG SYPORR rows for this role
        $ck = db2_fetch_assoc(db2_exec($conn,
            "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='$role'"
            . " AND TRIM(PRPORT) NOT IN ($sgPList)"));
        $existing = (int)$ck['N'];

        if ($existing > 0) {
            echo '<span class="skip">SKIP '.$role.': already has '.$existing.' non-SG SYPORR rows</span><br>';
            continue;
        }

        // Affected role -- insert SYPORR rows from SYROLD+SYPORT+SYURLM
        echo '<b>Processing '.$role.'...</b> ';
        $sql =
            "INSERT INTO S5HDSDATA.SYPORR"
            . " (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)"
            . " SELECT '$role', TRIM(S.FPPORT), S.FPPAGE, S.FPSEQ, S.FPID,"
            . " 'Y', '$now', 'BILL', 'Y'"
            . " FROM S5HDSDATA.SYROLD R"
            . " INNER JOIN S5HDSDATA.SYPORT S ON TRIM(S.FPPORT)=TRIM(R.RDPORT)"
            . " INNER JOIN S5HDSDATA.SYURLM U ON TRIM(U.FUID)=TRIM(S.FPID)"
            . " WHERE TRIM(R.RDROLE)='$role'"
            . " AND TRIM(R.RDPORT) NOT IN ($sgPList)"
            . " AND (TRIM(S.FPPAGE)='' OR TRIM(S.FPPAGE)=TRIM(S.FPPORT))"
            . " AND NOT EXISTS ("
            . "   SELECT 1 FROM S5HDSDATA.SYPORR X"
            . "   WHERE TRIM(X.PRROLE)='$role'"
            . "   AND TRIM(X.PRPORT)=TRIM(S.FPPORT)"
            . "   AND X.PRPAGE=S.FPPAGE AND X.PRSEQ=S.FPSEQ"
            . " )";
        $r = db2_exec($conn, $sql);
        if ($r) {
            $n = db2_num_rows($r);
            $totalIns += $n;
            echo '<span class="ok">inserted '.$n.' rows</span><br>';
        } else {
            $totalErr++;
            echo '<span class="err">ERR: '.htmlspecialchars(db2_stmt_errormsg()).'</span><br>';
        }
    }

    echo '<h2>Summary</h2>';
    echo 'Total inserted: <b>'.$totalIns.'</b><br>';
    echo 'Errors: <b style="color:'.($totalErr?'red':'green').'">'.$totalErr.'</b><br>';

    // Verify HD_ALL_SG
    echo '<h2>Verify HD_ALL_SG SYPORR row counts</h2>';
    $sg = db2_fetch_assoc(db2_exec($conn,
        "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='HD_ALL_SG'"
        . " AND TRIM(PRPORT) IN ($sgPList)"));
    $std = db2_fetch_assoc(db2_exec($conn,
        "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='HD_ALL_SG'"
        . " AND TRIM(PRPORT) NOT IN ($sgPList)"));
    echo 'SG portal SYPORR rows: <b>'.(int)$sg['N'].'</b><br>';
    echo 'Standard portal SYPORR rows: <b style="color:'.((int)$std['N']>0?'green':'red').'">'.(int)$std['N'].'</b><br>';

    if ($totalErr === 0)
        echo '<br><span class="ok">DONE -- 0 errors. Log SG5TEST out and back in.</span>';
    else
        echo '<br><span class="err">DONE WITH '.$totalErr.' ERRORS.</span>';
    echo '</body></html>';
    exit;
}

// -------------------------------------------------------
// DIAGNOSTIC
// -------------------------------------------------------
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Fix5</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
.skip{color:#888;}.step{border:1px solid #999;padding:10px;margin:8px 0;background:#f9f9f9;}
</style></head><body>
<h1>SG Fix5 -- Add missing SYPORR rows for standard portals</h1>
<p><b>Root cause (GetMenu.php line 35/41):</b><br>
If a role has ANY SYPORR rows, SYPORR is INNER JOINed.<br>
HD_ALL_SG previously had 0 SYPORR rows so join was skipped and all 41 portals showed.<br>
After our SG portal work it has 35 rows, join is active, only SG portals have rows &rarr; standard portals vanish.</p>
<h2>Affected roles (0 non-SG SYPORR rows)</h2>';

$affected = array();
foreach ($roles as $role) {
    $r = db2_fetch_assoc(db2_exec($conn,
        "SELECT COUNT(*) AS N FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE)='$role'"
        . " AND TRIM(PRPORT) NOT IN ($sgPList)"));
    $n = (int)$r['N'];
    if ($n === 0) {
        $affected[] = $role;
        echo '<span class="err">'.$role.': 0 non-SG rows &larr; AFFECTED</span><br>';
    } else {
        echo '<span class="skip">'.$role.': '.$n.' non-SG rows (ok)</span><br>';
    }
}

if (empty($affected))
    echo '<p class="ok">No affected roles found -- all roles have non-SG SYPORR rows.</p>';

echo '<div class="step"><b>Step 1:</b> <a href="?action=backup" style="background:#060;color:#fff;'
   . 'padding:6px 14px;text-decoration:none;">Download Backup</a></div>';
echo '<div class="step"><b>Step 2:</b> <form method="post">'
   . '<button name="fix" value="1" style="background:#c00;color:#fff;padding:8px 24px;font-size:13px;">'
   . 'Add Missing Standard Portal SYPORR Rows</button></form></div>';
echo '</body></html>';
?>
