<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Session Fix</title>
<style>body{font-family:monospace;font-size:11px;padding:16px;}
h3{color:#00c;margin:12px 0 4px;}
table{border-collapse:collapse;margin:6px 0;}td,th{border:1px solid #ccc;padding:2px 8px;}th{background:#ddd;}
</style></head><body>';
echo '<h2>SYHAND Session Inspector</h2>';

// Show all columns in SYHAND
echo '<h3>1. SYHAND columns</h3>';
$r = db2_exec($conn, "SELECT COLUMN_NAME, DATA_TYPE, LENGTH FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA='S5HDSDATA' AND TABLE_NAME='SYHAND' ORDER BY ORDINAL_POSITION");
echo '<table><tr><th>COLUMN</th><th>TYPE</th><th>LEN</th></tr>';
while ($row = db2_fetch_assoc($r)) {
    echo '<tr><td>' . htmlspecialchars($row['COLUMN_NAME']) . '</td><td>' . $row['DATA_TYPE'] . '</td><td>' . $row['LENGTH'] . '</td></tr>';
}
echo '</table>';

// Show SG5TEST sessions
echo '<h3>2. SYHAND — SG5TEST sessions</h3>';
$r2 = db2_exec($conn, "SELECT * FROM SYHAND WHERE TRIM(HNUSER) = 'SG5TEST' ORDER BY 1");
if ($r2) {
    $nc = db2_num_fields($r2); $cols = array();
    for ($i=0; $i<$nc; $i++) $cols[] = db2_field_name($r2, $i);
    echo '<table><tr>'; foreach ($cols as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
    $cnt = 0;
    while ($row = db2_fetch_assoc($r2)) {
        echo '<tr>'; foreach ($cols as $c) echo '<td>'.htmlspecialchars(substr(trim((string)$row[$c]),0,40)).'</td>'; echo '</tr>';
        $cnt++;
    }
    echo '</table><p>' . $cnt . ' row(s)</p>';
}

// Check SYPORR and SYPORW — possible nav cache tables
echo '<h3>3. SYPORR columns (possible cached nav table)</h3>';
$r3 = db2_exec($conn, "SELECT COLUMN_NAME, DATA_TYPE FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA='S5HDSDATA' AND TABLE_NAME='SYPORR' ORDER BY ORDINAL_POSITION");
echo '<table><tr><th>COLUMN</th><th>TYPE</th></tr>';
while ($row = db2_fetch_assoc($r3)) echo '<tr><td>'.htmlspecialchars($row['COLUMN_NAME']).'</td><td>'.$row['DATA_TYPE'].'</td></tr>';
echo '</table>';

echo '<h3>4. SYPORW columns</h3>';
$r4 = db2_exec($conn, "SELECT COLUMN_NAME, DATA_TYPE FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA='S5HDSDATA' AND TABLE_NAME='SYPORW' ORDER BY ORDINAL_POSITION");
echo '<table><tr><th>COLUMN</th><th>TYPE</th></tr>';
while ($row = db2_fetch_assoc($r4)) echo '<tr><td>'.htmlspecialchars($row['COLUMN_NAME']).'</td><td>'.$row['DATA_TYPE'].'</td></tr>';
echo '</table>';

// Delete option
if (isset($_POST['delhand'])) {
    $r = db2_exec($conn, "DELETE FROM SYHAND WHERE TRIM(HNUSER) = 'SG5TEST'");
    echo '<p><strong>SYHAND entries for SG5TEST deleted (' . db2_num_rows($r) . ' rows). SG5TEST must log in fresh.</strong></p>';
}

echo '<h3>5. Delete SYHAND sessions for SG5TEST</h3>';
echo '<p>This forces a completely fresh session build on next login, rebuilding the nav from current DB data.</p>';
echo '<form method="post"><button name="delhand" value="1" type="submit">Delete SG5TEST sessions from SYHAND</button></form>';

echo '</body></html>';
?>
