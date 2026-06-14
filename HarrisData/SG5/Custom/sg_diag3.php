<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Diag3</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 6px;white-space:nowrap;}
th{background:#ddd;}.diff{background:#ffc;font-weight:bold;}
</style></head><body><h1>SG Diag3 -- Full Column Comparison</h1>';

// --- 1. ALL columns for ACAREPORTING (working) ---
echo '<h2>1. SYPORR ALL COLS -- ACAREPORTING/CUSTSRVC (WORKING)</h2>';
$r = db2_exec($conn,
    "SELECT * FROM S5HDSDATA.SYPORR"
    . " WHERE TRIM(PRROLE)='CUSTSRVC' AND TRIM(PRPORT)='ACAREPORTING'"
    . " ORDER BY PRSEQ");
if ($r) {
    $nc = db2_num_fields($r);
    $cols = array();
    for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($r, $i);
    echo '<table><tr>';
    foreach ($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>';
    echo '</tr>';
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr>';
        foreach ($cols as $c) {
            $v = rtrim((string)$row[$c]);
            echo '<td>'.htmlspecialchars($v===''?'(empty)':$v).'</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
} else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

// --- 2. ALL columns for SGINQ (broken) ---
echo '<h2>2. SYPORR ALL COLS -- SGINQ/CUSTSRVC (BROKEN)</h2>';
$r = db2_exec($conn,
    "SELECT * FROM S5HDSDATA.SYPORR"
    . " WHERE TRIM(PRROLE)='CUSTSRVC' AND TRIM(PRPORT)='SGINQ'"
    . " ORDER BY PRSEQ");
if ($r) {
    $nc = db2_num_fields($r);
    $cols = array();
    for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($r, $i);
    echo '<table><tr>';
    foreach ($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>';
    echo '</tr>';
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr>';
        foreach ($cols as $c) {
            $v = rtrim((string)$row[$c]);
            echo '<td>'.htmlspecialchars($v===''?'(empty)':$v).'</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
} else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

// --- 3. ALL columns for another known working portal (CUSTOMER) ---
echo '<h2>3. SYPORR ALL COLS -- CUSTOMER/CUSTSRVC (another working portal)</h2>';
$r = db2_exec($conn,
    "SELECT * FROM S5HDSDATA.SYPORR"
    . " WHERE TRIM(PRROLE)='CUSTSRVC' AND TRIM(PRPORT)='CUSTOMER'"
    . " ORDER BY PRSEQ FETCH FIRST 5 ROWS ONLY");
if ($r) {
    $nc = db2_num_fields($r);
    $cols = array();
    for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($r, $i);
    echo '<table><tr>';
    foreach ($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>';
    echo '</tr>';
    $cnt = 0;
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr>';
        foreach ($cols as $c) {
            $v = rtrim((string)$row[$c]);
            echo '<td>'.htmlspecialchars($v===''?'(empty)':$v).'</td>';
        }
        echo '</tr>';
        $cnt++;
    }
    if ($cnt === 0) echo '<tr><td colspan="'.count($cols).'" class="err">0 rows</td></tr>';
    echo '</table>';
} else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

// --- 4. SYPGMO check ---
echo '<h2>4. SYPGMO -- does it have SG portal entries?</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(PMPORT) AS PMPORT, TRIM(PMPAGE) AS PMPAGE,"
    . " TRIM(PMSEQN) AS PMSEQN, TRIM(PMPGM) AS PMPGM, TRIM(PMPARM) AS PMPARM"
    . " FROM S5HDSDATA.SYPGMO"
    . " WHERE TRIM(PMPORT) IN ('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP','ACAREPORTING')"
    . " ORDER BY PMPORT,PMSEQN FETCH FIRST 30 ROWS ONLY");
if ($r) {
    echo '<table><tr><th>PMPORT</th><th>PMPAGE</th><th>PMSEQN</th>'
       . '<th>PMPGM</th><th>PMPARM</th></tr>';
    $cnt = 0;
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr><td>'.htmlspecialchars(trim($row['PMPORT'])).'</td>'
           . '<td>'.htmlspecialchars(trim($row['PMPAGE'])===''?'(empty)':trim($row['PMPAGE'])).'</td>'
           . '<td>'.htmlspecialchars(trim($row['PMSEQN'])).'</td>'
           . '<td>'.htmlspecialchars(trim($row['PMPGM'])).'</td>'
           . '<td>'.htmlspecialchars(trim($row['PMPARM'])).'</td></tr>';
        $cnt++;
    }
    if ($cnt === 0) echo '<tr><td colspan="5" class="err">0 rows -- SYPGMO has no SG portal entries</td></tr>';
    echo '</table><p>'.$cnt.' rows</p>';
} else echo '<p class="err">SYPGMO query failed: '.htmlspecialchars(db2_stmt_errormsg()).'</p>';

// --- 5. SYURLM for ACAREPORTING sub-pages ---
echo '<h2>5. SYURLM ALL COLS -- ACAREPORTING/REPORT (working portal header)</h2>';
$r = db2_exec($conn,
    "SELECT * FROM S5HDSDATA.SYURLM WHERE TRIM(FUID)='ACAREPORTING/REPORT'");
if ($r) {
    $nc = db2_num_fields($r);
    $cols = array();
    for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($r, $i);
    echo '<table><tr>';
    foreach ($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>';
    echo '</tr>';
    $cnt = 0;
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr>';
        foreach ($cols as $c) {
            $v = rtrim((string)$row[$c]);
            echo '<td>'.htmlspecialchars($v===''?'(empty)':$v).'</td>';
        }
        echo '</tr>';
        $cnt++;
    }
    if ($cnt===0) echo '<tr><td colspan="'.count($cols).'" class="err">0 rows</td></tr>';
    echo '</table>';
} else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

// --- 6. SYURLM for SGINQ/PORTAL ---
echo '<h2>6. SYURLM ALL COLS -- SGINQ/PORTAL</h2>';
$r = db2_exec($conn,
    "SELECT * FROM S5HDSDATA.SYURLM WHERE TRIM(FUID)='SGINQ/PORTAL'");
if ($r) {
    $nc = db2_num_fields($r);
    $cols = array();
    for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($r, $i);
    echo '<table><tr>';
    foreach ($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>';
    echo '</tr>';
    $cnt = 0;
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr>';
        foreach ($cols as $c) {
            $v = rtrim((string)$row[$c]);
            echo '<td>'.htmlspecialchars($v===''?'(empty)':$v).'</td>';
        }
        echo '</tr>';
        $cnt++;
    }
    if ($cnt===0) echo '<tr><td colspan="'.count($cols).'" class="err">0 rows</td></tr>';
    echo '</table>';
} else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

// --- 7. SYDSGN check ---
echo '<h2>7. SYDSGN PDTBID=199</h2>';
$r = db2_exec($conn,
    "SELECT TRIM(PDTBID) AS PDTBID, TRIM(PDPGID) AS PDPGID,"
    . " TRIM(PDUSER) AS PDUSER, TRIM(PDNAME) AS PDNAME"
    . " FROM SG5STDPGM.SYDSGN WHERE PDTBID=199");
if ($r) {
    echo '<table><tr><th>PDTBID</th><th>PDPGID</th><th>PDUSER</th><th>PDNAME</th></tr>';
    $cnt = 0;
    while ($row = db2_fetch_assoc($r)) {
        $userOk = (trim($row['PDUSER']) === '');
        echo '<tr><td>'.htmlspecialchars(trim($row['PDTBID'])).'</td>'
           . '<td>'.htmlspecialchars(trim($row['PDPGID'])).'</td>'
           . '<td style="'.($userOk?'color:green':'color:red;font-weight:bold').';">'
           . htmlspecialchars(trim($row['PDUSER'])===''?'(space/empty - OK)':trim($row['PDUSER'])).'</td>'
           . '<td>'.htmlspecialchars(trim($row['PDNAME'])).'</td></tr>';
        $cnt++;
    }
    if ($cnt===0) echo '<tr><td colspan="4" class="err">0 rows</td></tr>';
    echo '</table>';
} else echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>';

echo '</body></html>';
?>
