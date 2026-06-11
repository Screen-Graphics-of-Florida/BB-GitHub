<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Portal Sequences</title>
<style>body{font-family:monospace;font-size:12px;padding:16px;}
table{border-collapse:collapse;}td,th{border:1px solid #ccc;padding:4px 8px;}
th{background:#eee;}tr.sg{background:#ffe;font-weight:bold;}</style></head><body>';
echo '<h2>SYROLD — Portal Sequence Numbers</h2>';

// Pick a representative role to show sequence ordering
// Show distinct RDPORT + min RDSEQN across all roles, ordered by seq
$sql = "SELECT RDPORT, MIN(RDSEQN) AS MINSEQ, COUNT(*) AS RCNT
        FROM SYROLD
        GROUP BY RDPORT
        ORDER BY MIN(RDSEQN), RDPORT";

$r = db2_exec($conn, $sql);
if (!$r) {
    echo '<p style="color:red">Query failed: ' . htmlspecialchars(db2_stmt_errormsg()) . '</p>';
} else {
    echo '<table><tr><th>RDPORT</th><th>Min RDSEQN</th><th>Role Count</th></tr>';
    while ($row = db2_fetch_assoc($r)) {
        $isSG = in_array($row['RDPORT'], array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'));
        $cls  = $isSG ? ' class="sg"' : '';
        echo '<tr' . $cls . '><td>' . htmlspecialchars($row['RDPORT']) . '</td><td>' . $row['MINSEQ'] . '</td><td>' . $row['RCNT'] . '</td></tr>';
    }
    echo '</table>';
}

// Also show the SYPORT description for Event Calendar to find its code
echo '<h3>SYPORT — Portal titles containing "event" or "calendar"</h3>';
$sql2 = "SELECT FPPORT, FPTITL FROM SYPORT WHERE TRIM(FPPAGE) = '' AND (UPPER(FPTITL) LIKE '%EVENT%' OR UPPER(FPTITL) LIKE '%CALENDAR%') GROUP BY FPPORT, FPTITL ORDER BY FPPORT";
$r2 = db2_exec($conn, $sql2);
if ($r2) {
    echo '<table><tr><th>FPPORT</th><th>FPTITL</th></tr>';
    while ($row = db2_fetch_assoc($r2)) {
        echo '<tr><td>' . htmlspecialchars($row['FPPORT']) . '</td><td>' . htmlspecialchars($row['FPTITL']) . '</td></tr>';
    }
    echo '</table>';
}

// UPDATE form
if (isset($_POST['newseq']) && isset($_POST['afterport'])) {
    $afterPort = strtoupper(trim($_POST['afterport']));
    $newSeq    = (int)$_POST['newseq'];
    $sgPorts   = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
    echo '<h3>Updating SG Portal Sequences</h3>';
    $errors = array();
    foreach ($sgPorts as $i => $p) {
        $seq = $newSeq + $i;
        $sql = "UPDATE SYROLD SET RDSEQN = " . $seq . " WHERE RDPORT = '" . $p . "'";
        $res = db2_exec($conn, $sql);
        if ($res) {
            echo 'OK  ' . $p . ' -> RDSEQN=' . $seq . '<br>';
        } else {
            $errors[] = $p . ': ' . db2_stmt_errormsg();
            echo 'ERR ' . $p . ' - ' . end($errors) . '<br>';
        }
    }
    echo '<br><strong>' . (empty($errors) ? 'Done. Reload the page to verify.' : 'Errors occurred.') . '</strong>';
}

echo '<h3>Update SG Portal Sequences</h3>';
echo '<p>Enter the sequence number for the portal AFTER which you want SG menus to appear.</p>';
echo '<form method="post">
After portal code: <input name="afterport" value="EVENTCAL" size="12"> &nbsp;
New start seq for SG Inquiries: <input name="newseq" value="4" size="6">
<br><small>(SG Dashboards=start+1, SG Data Integrity=start+2, SG Reports=start+3, SG SOPs=start+4)</small><br><br>
<input type="submit" value="Update Sequences">
</form>';

echo '</body></html>';
?>
