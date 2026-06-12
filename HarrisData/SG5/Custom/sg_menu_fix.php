<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Menu Fix v4</title>
<style>body{font-family:monospace;font-size:11px;padding:16px;}
h2{color:#00c;}h3{color:#800;margin:14px 0 4px;}
table{border-collapse:collapse;margin-bottom:10px;}
td,th{border:1px solid #ccc;padding:3px 7px;}th{background:#ddd;}
.diff{background:#ffa;font-weight:bold;}
</style></head><body>';
echo '<h2>SG Menu Fix v4 — Compare working custom portal vs SG portals</h2>';

// Find the FPID for SG_DOCUMENTATION from SYPORT
$r = db2_exec($conn, "SELECT FPPORT, FPID, FPRESV, FPTSPT, FPTSWS FROM SYPORT WHERE TRIM(FPPAGE) = '' AND FPPORT IN ('SG_DOCUMENTATION','SGINQ','SGDASH') ORDER BY FPPORT");
echo '<h3>SYPORT header rows: SG_DOCUMENTATION vs SG portals</h3>';
echo '<table><tr><th>FPPORT</th><th>FPID</th><th>FPRESV</th><th>FPTSPT</th><th>FPTSWS</th></tr>';
$sgdocFpid = '';
while ($row = db2_fetch_assoc($r)) {
    if (trim($row['FPPORT']) === 'SG_DOCUMENTATION') $sgdocFpid = trim($row['FPID']);
    echo '<tr><td>' . htmlspecialchars(trim($row['FPPORT'])) . '</td><td>' . htmlspecialchars(trim($row['FPID'])) . '</td><td>' . htmlspecialchars(trim($row['FPRESV'])) . '</td><td>' . htmlspecialchars(trim($row['FPTSPT'])) . '</td><td>' . htmlspecialchars(trim($row['FPTSWS'])) . '</td></tr>';
}
echo '</table>';

// Full column compare: SG_DOCUMENTATION vs SGINQ in SYPORT
echo '<h3>ALL SYPORT columns: SG_DOCUMENTATION vs SGINQ</h3>';
$r = db2_exec($conn, "SELECT * FROM SYPORT WHERE FPPORT IN ('SG_DOCUMENTATION','SGINQ') AND TRIM(FPPAGE) = '' ORDER BY FPPORT");
$nc = db2_num_fields($r); $cols = array();
for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($r, $i);
$rows = array();
while ($row = db2_fetch_assoc($r)) $rows[trim($row['FPPORT'])] = $row;
echo '<table><tr><th>Field</th><th>SG_DOCUMENTATION</th><th>SGINQ</th></tr>';
foreach ($cols as $c) {
    $a = isset($rows['SG_DOCUMENTATION']) ? trim((string)$rows['SG_DOCUMENTATION'][$c]) : '(no row)';
    $b = isset($rows['SGINQ'])             ? trim((string)$rows['SGINQ'][$c])             : '(no row)';
    $diff = ($a !== $b && $c !== 'FPPORT' && $c !== 'FPID' && $c !== 'FPTITL' && $c !== 'FPTSTP' && $c !== 'FPTSUS') ? ' class="diff"' : '';
    echo '<tr' . $diff . '><td>' . $c . '</td><td>' . htmlspecialchars($a) . '</td><td>' . htmlspecialchars($b) . '</td></tr>';
}
echo '</table>';

// SYURLM compare: SG_DOCUMENTATION FPID vs SGINQ
echo '<h3>SYURLM: SG_DOCUMENTATION FPID (' . htmlspecialchars($sgdocFpid) . ') vs SGINQ</h3>';
$lookupFuids = array('SGINQ', 'SGINQ/PORTAL');
if ($sgdocFpid !== '') $lookupFuids[] = $sgdocFpid;
$fuList = "'" . implode("','", array_map(function($f){ return str_replace("'","''",$f); }, $lookupFuids)) . "'";
$r = db2_exec($conn, "SELECT * FROM SYURLM WHERE FUID IN (" . $fuList . ") ORDER BY FUID");
$nc = db2_num_fields($r); $ucols = array();
for ($i = 0; $i < $nc; $i++) $ucols[] = db2_field_name($r, $i);
$urows = array();
while ($row = db2_fetch_assoc($r)) $urows[trim($row['FUID'])] = $row;
echo '<table><tr><th>Field</th><th>' . htmlspecialchars($sgdocFpid) . '</th><th>SGINQ or SGINQ/PORTAL</th></tr>';
$sginqKey = isset($urows['SGINQ/PORTAL']) ? 'SGINQ/PORTAL' : 'SGINQ';
foreach ($ucols as $c) {
    $a = isset($urows[$sgdocFpid]) ? trim((string)$urows[$sgdocFpid][$c]) : '(no row)';
    $b = isset($urows[$sginqKey])  ? trim((string)$urows[$sginqKey][$c])  : '(no row)';
    $diff = ($a !== $b && $c !== 'FUID' && $c !== 'FUDESC' && $c !== 'FUTITL' && $c !== 'FUTSTP') ? ' class="diff"' : '';
    echo '<tr' . $diff . '><td>' . $c . '</td><td>' . htmlspecialchars($a) . '</td><td>' . htmlspecialchars($b) . '</td></tr>';
}
echo '</table>';

// Also show ALL columns of SYROLD for SG_DOCUMENTATION vs SGINQ for one role
echo '<h3>SYROLD: SG_DOCUMENTATION vs SGINQ — all columns for CUSTSRVC</h3>';
$r = db2_exec($conn, "SELECT * FROM SYROLD WHERE RDROLE = 'CUSTSRVC' AND RDPORT IN ('SG_DOCUMENTATION','SGINQ') ORDER BY RDPORT");
$nc = db2_num_fields($r); $rcols = array();
for ($i = 0; $i < $nc; $i++) $rcols[] = db2_field_name($r, $i);
$rrows = array();
while ($row = db2_fetch_assoc($r)) $rrows[trim($row['RDPORT'])] = $row;
echo '<table><tr><th>Field</th><th>SG_DOCUMENTATION</th><th>SGINQ</th></tr>';
foreach ($rcols as $c) {
    $a = isset($rrows['SG_DOCUMENTATION']) ? trim((string)$rrows['SG_DOCUMENTATION'][$c]) : '(no row)';
    $b = isset($rrows['SGINQ'])             ? trim((string)$rrows['SGINQ'][$c])             : '(no row)';
    $diff = ($a !== $b && $c !== 'RDPORT' && $c !== 'RDSEQN' && $c !== 'RDTSTP' && $c !== 'RDTSUS') ? ' class="diff"' : '';
    echo '<tr' . $diff . '><td>' . $c . '</td><td>' . htmlspecialchars($a) . '</td><td>' . htmlspecialchars($b) . '</td></tr>';
}
echo '</table>';

echo '</body></html>';
?>
