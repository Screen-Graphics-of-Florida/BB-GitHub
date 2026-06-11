<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

// Map: old FPID/FUID => new FPID/FUID (matching MFGMGMT/REPORT pattern)
$portals = array(
    'SGINQ'  => 'SGINQ/PORTAL',
    'SGDASH' => 'SGDASH/PORTAL',
    'SGDINT' => 'SGDINT/PORTAL',
    'SGRPT'  => 'SGRPT/PORTAL',
    'SGSOP'  => 'SGSOP/PORTAL',
);

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Menu Fix v3</title>
<style>body{font-family:monospace;font-size:12px;padding:16px;}
table{border-collapse:collapse;margin:8px 0;}td,th{border:1px solid #ccc;padding:3px 8px;}th{background:#eee;}</style></head><body>';
echo '<h2>SG Menu Fix v3 — Rename FPID/FUID to slash format</h2>';
echo '<p>Working portals use <strong>X/Y</strong> format (MFGMGMT/REPORT, MYPORTAL/REPORT, HDLIST/CUSTOMER).<br>SG portals currently use bare codes. This is why they are hidden for standard roles.</p>';

if (isset($_POST['fix'])) {

    foreach ($portals as $oldId => $newId) {
        // Update SYPORT header row FPID
        $sql = "UPDATE SYPORT SET FPID = '" . $newId . "' WHERE FPPORT = '" . $oldId . "' AND TRIM(FPPAGE) = ''";
        $r = db2_exec($conn, $sql);
        if ($r) {
            echo 'OK  SYPORT FPID  ' . $oldId . ' => ' . $newId . ' (' . db2_num_rows($r) . ' row)<br>';
        } else {
            echo 'ERR SYPORT FPID  ' . $oldId . ': ' . htmlspecialchars(db2_stmt_errormsg()) . '<br>';
        }

        // Update SYURLM FUID (must match FPID)
        $sql = "UPDATE SYURLM SET FUID = '" . $newId . "' WHERE FUID = '" . $oldId . "'";
        $r = db2_exec($conn, $sql);
        if ($r) {
            echo 'OK  SYURLM FUID  ' . $oldId . ' => ' . $newId . ' (' . db2_num_rows($r) . ' row)<br>';
        } else {
            echo 'ERR SYURLM FUID  ' . $oldId . ': ' . htmlspecialchars(db2_stmt_errormsg()) . '<br>';
        }
    }

    echo '<br><strong>Done. Log out and back in as SG5Test to test.</strong>';
    echo '</body></html>';
    exit;
}

// Preview current state
echo '<h3>Current SYPORT header FPIDs for SG portals</h3>';
$inList = "'" . implode("','", array_keys($portals)) . "'";
$r = db2_exec($conn, "SELECT FPPORT, FPID FROM SYPORT WHERE FPPORT IN (" . $inList . ") AND TRIM(FPPAGE) = '' ORDER BY FPPORT");
echo '<table><tr><th>FPPORT</th><th>Current FPID</th><th>New FPID</th></tr>';
while ($row = db2_fetch_assoc($r)) {
    $p = trim($row['FPPORT']);
    echo '<tr><td>' . htmlspecialchars($p) . '</td><td>' . htmlspecialchars(trim($row['FPID'])) . '</td><td>' . htmlspecialchars($portals[$p]) . '</td></tr>';
}
echo '</table>';

echo '<br><form method="post"><button name="fix" value="1" type="submit">Rename FPID/FUID to slash format on all 5 SG portals</button></form>';
echo '</body></html>';
?>
