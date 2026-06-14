<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

$sgPortals = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";

$roles = array('ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR',
               'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN',
               'MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION',
               'PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM');
$rList = "'".implode("','",$roles)."'";

// -------------------------------------------------------
// BACKUP DOWNLOAD — must be FIRST, before any output
// -------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array();
    $lines[] = '-- SYPORR Backup -- ' . date('Y-m-d H:i:s');
    $lines[] = '-- Existing SYPORR rows for 23 roles x SG portals (before insert)';
    $lines[] = '-- Undo command: DELETE FROM SYPORR WHERE PRROLE IN (' . $rList . ') AND PRPORT IN (' . $pList . ');';
    $lines[] = '';
    $rBk = db2_exec($conn, "SELECT * FROM SYPORR WHERE TRIM(PRROLE) IN (" . $rList . ") AND PRPORT IN (" . $pList . ") ORDER BY PRROLE,PRPORT");
    $nc = db2_num_fields($rBk); $bc = array();
    for ($i = 0; $i < $nc; $i++) $bc[] = db2_field_name($rBk, $i);
    $cnt = 0;
    while ($row = db2_fetch_assoc($rBk)) {
        $vals = array();
        foreach ($bc as $c) $vals[] = "'" . str_replace("'", "''", rtrim((string)$row[$c])) . "'";
        $lines[] = "INSERT INTO SYPORR (" . implode(',', $bc) . ") VALUES (" . implode(',', $vals) . ");";
        $cnt++;
    }
    if ($cnt === 0) $lines[] = '-- No existing rows found (INSERT is safe, undo with DELETE above)';
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sg_porr_backup_' . date('Ymd_His') . '.sql"');
    header('Cache-Control: no-cache, no-store');
    header('Pragma: no-cache');
    echo implode("\r\n", $lines);
    exit;
}

// -------------------------------------------------------
// INSERT
// -------------------------------------------------------
if (isset($_POST['insert'])) {
    // Get template row from CUSTSRVC for column structure
    $tmpl = db2_exec($conn, "SELECT * FROM SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' FETCH FIRST 1 ROWS ONLY");
    $tmplRow  = db2_fetch_assoc($tmpl);
    $tmplCols = array_keys($tmplRow);

    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SYPORR Insert</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}</style></head><body>';
    echo '<h2>Inserting SG portals into SYPORR</h2>';
    $inserted = 0; $skipped = 0; $errors = 0;

    foreach ($roles as $role) {
        foreach ($sgPortals as $port) {
            $chk = db2_exec($conn, "SELECT COUNT(*) AS N FROM SYPORR WHERE TRIM(PRROLE)='" . $role . "' AND TRIM(PRPORT)='" . $port . "'");
            $chkRow = db2_fetch_assoc($chk);
            if ($chkRow && $chkRow['N'] > 0) { $skipped++; continue; }

            $vals = array();
            foreach ($tmplCols as $c) {
                if ($c === 'PRROLE')      $vals[] = "'" . $role . "'";
                elseif ($c === 'PRPORT')  $vals[] = "'" . $port . "'";
                else                      $vals[] = "'" . str_replace("'","''", rtrim((string)$tmplRow[$c])) . "'";
            }
            $sql = "INSERT INTO SYPORR (" . implode(',', $tmplCols) . ") VALUES (" . implode(',', $vals) . ")";
            $ri = db2_exec($conn, $sql);
            if ($ri) {
                $inserted++;
                echo '<span class="ok">OK</span>  ' . $role . ' / ' . $port . '<br>';
            } else {
                $errors++;
                echo '<span class="err">ERR</span> ' . $role . ' / ' . $port . ' -- ' . htmlspecialchars(db2_stmt_errormsg()) . '<br>';
            }
        }
    }
    echo '<br><strong>Inserted: ' . $inserted . ' &nbsp; Skipped: ' . $skipped . ' &nbsp; Errors: ' . $errors . '</strong>';
    echo '<br><br><b>Log SG5TEST out completely and log back in as SG5TEST / CUSTSRVC.</b>';
    echo '</body></html>';
    exit;
}

// -------------------------------------------------------
// DEFAULT — show state + buttons
// -------------------------------------------------------
// Get template to show preview
$tmpl2 = db2_exec($conn, "SELECT * FROM SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' FETCH FIRST 1 ROWS ONLY");
$tmplRow2  = db2_fetch_assoc($tmpl2);
$tmplCols2 = array_keys($tmplRow2);

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SYPORR Fix</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:2px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 7px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#ffa;font-weight:bold;}
.step{border:1px solid #999;padding:10px;margin:10px 0;background:#f9f9f9;}
</style></head><body>';

echo '<h1>SYPORR Fix — Add SG portals to role read-access list</h1>';
echo '<p><b>Root cause:</b> CUSTSRVC visibility is controlled by SYPORR (514 rows). SG portals are not in that list.</p>';

echo '<h2>1. SYPORR columns</h2>';
$rc = db2_exec($conn, "SELECT COLUMN_NAME, DATA_TYPE, LENGTH FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA='S5HDSDATA' AND TABLE_NAME='SYPORR' ORDER BY ORDINAL_POSITION");
echo '<table><tr><th>Column</th><th>Type</th><th>Len</th></tr>';
while ($row = db2_fetch_assoc($rc)) echo '<tr><td>'.$row['COLUMN_NAME'].'</td><td>'.$row['DATA_TYPE'].'</td><td>'.$row['LENGTH'].'</td></tr>';
echo '</table>';

echo '<h2>2. Template row (first CUSTSRVC row in SYPORR)</h2>';
echo '<table><tr>'; foreach ($tmplCols2 as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
echo '<tr>'; foreach ($tmplCols2 as $c) echo '<td>'.htmlspecialchars(trim((string)$tmplRow2[$c])).'</td>'; echo '</tr>';
echo '</table>';

echo '<h2>3. SG portals in SYPORR for CUSTSRVC — current (should be empty)</h2>';
$r3 = db2_exec($conn, "SELECT * FROM SYPORR WHERE TRIM(PRROLE)='CUSTSRVC' AND PRPORT IN (".$pList.")");
$nc3 = db2_num_fields($r3); $c3 = array();
for ($i=0;$i<$nc3;$i++) $c3[] = db2_field_name($r3,$i);
echo '<table><tr>'; foreach ($c3 as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
$cnt3 = 0;
while ($row = db2_fetch_assoc($r3)) {
    echo '<tr>'; foreach ($c3 as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
    $cnt3++;
}
if ($cnt3 === 0) echo '<tr><td colspan="'.count($c3).'"><em>No rows — confirmed root cause</em></td></tr>';
echo '</table>';

echo '<h2>4. Rows to insert ('.count($roles).' roles x '.count($sgPortals).' portals)</h2>';
echo '<table><tr><th>PRROLE</th><th>PRPORT</th><th>Status</th></tr>';
foreach ($roles as $role) {
    foreach ($sgPortals as $port) {
        $chk = db2_exec($conn, "SELECT COUNT(*) AS N FROM SYPORR WHERE TRIM(PRROLE)='".$role."' AND TRIM(PRPORT)='".$port."'");
        $cr = db2_fetch_assoc($chk);
        $exists = $cr && $cr['N'] > 0;
        echo '<tr'.($exists ? '' : ' class="hi"').'>';
        echo '<td>'.$role.'</td><td>'.$port.'</td>';
        echo '<td>'.($exists ? 'already exists' : 'WILL INSERT').'</td></tr>';
    }
}
echo '</table>';

echo '<div class="step"><b>Step 1 — Download backup:</b><br><br>';
echo '<a href="?action=backup" style="background:#060;color:#fff;padding:6px 16px;text-decoration:none;font-size:13px;">Download SYPORR backup SQL</a>';
echo ' &nbsp;<small>Downloads .sql file confirming no SG rows exist yet + undo DELETE statement</small></div>';

echo '<div class="step"><b>Step 2 — Insert after backup saved:</b><br><br>';
echo '<form method="post"><button name="insert" value="1" type="submit"
    style="background:#c00;color:#fff;padding:8px 24px;font-size:14px;">
    INSERT SG portals into SYPORR for all 23 roles</button></form></div>';

echo '</body></html>';
?>
