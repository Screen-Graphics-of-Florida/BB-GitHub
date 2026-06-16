<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SYPGMS Diag</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 7px;white-space:nowrap;}
th{background:#ddd;}.ok{color:green;font-weight:bold;}.err{color:red;}
</style></head><body><h1>Program Option Security Diagnostic</h1>';

// 1. SYPGMS columns
echo '<h2>1. SYPGMS columns</h2>';
$r = db2_exec($conn, "SELECT * FROM S5HDSDATA.SYPGMS FETCH FIRST 1 ROWS ONLY");
if ($r) {
    $nf = db2_num_fields($r); echo '<table><tr>';
    for ($i=0;$i<$nf;$i++) echo '<th>'.db2_field_name($r,$i).'</th>';
    echo '</tr>';
    if ($row = db2_fetch_assoc($r)) {
        echo '<tr>'; foreach ($row as $v) echo '<td>'.htmlspecialchars(rtrim((string)$v)).'</td>'; echo '</tr>';
    }
    echo '</table>';
} else { echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>'; }

// 2. SYPGMO columns + SG rows
echo '<h2>2. SYPGMO columns + SG portal rows</h2>';
$r = db2_exec($conn, "SELECT * FROM S5HDSDATA.SYPGMO WHERE FPPGMID LIKE 'SG%' ORDER BY FPPGMID,FPOPTNO FETCH FIRST 60 ROWS ONLY");
if ($r) {
    $nf = db2_num_fields($r); echo '<table><tr>';
    for ($i=0;$i<$nf;$i++) echo '<th>'.db2_field_name($r,$i).'</th>';
    echo '</tr>';
    $cnt=0;
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr>'; foreach ($row as $v) echo '<td>'.htmlspecialchars(rtrim((string)$v)).'</td>'; echo '</tr>'; $cnt++;
    }
    if ($cnt===0) echo '<tr><td colspan="'.$nf.'"><em class="err">No SG rows</em></td></tr>';
    echo '</table>';
} else { echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>'; }

// 3. SYURLM FUPGM for SG portal landing pages
echo '<h2>3. SYURLM — FUPGM for SG portal submenu entries</h2>';
$r = db2_exec($conn, "SELECT FUID,FUURL,FUPGM,FUDESCR FROM S5HDSDATA.SYURLM WHERE FUID LIKE 'SG%' AND FUURL LIKE '%sg_portal_landing%' ORDER BY FUID FETCH FIRST 60 ROWS ONLY");
if ($r) {
    echo '<table><tr><th>FUID</th><th>FUURL (truncated)</th><th>FUPGM</th><th>FUDESCR</th></tr>';
    $cnt=0;
    while ($row = db2_fetch_assoc($r)) {
        $url = substr(rtrim($row['FUURL']),0,80);
        echo '<tr><td>'.htmlspecialchars(rtrim($row['FUID'])).'</td><td>'.htmlspecialchars($url).'</td><td>'.htmlspecialchars(rtrim($row['FUPGM'])).'</td><td>'.htmlspecialchars(rtrim($row['FUDESCR'])).'</td></tr>';
        $cnt++;
    }
    if ($cnt===0) echo '<tr><td colspan="4"><em class="err">No rows</em></td></tr>';
    echo '</table>';
} else { echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>'; }

// 4. Sample SYPGMS rows (first 20)
echo '<h2>4. SYPGMS sample rows (first 20)</h2>';
$r = db2_exec($conn, "SELECT * FROM S5HDSDATA.SYPGMS ORDER BY 1,2 FETCH FIRST 20 ROWS ONLY");
if ($r) {
    $nf = db2_num_fields($r); echo '<table><tr>';
    for ($i=0;$i<$nf;$i++) echo '<th>'.db2_field_name($r,$i).'</th>';
    echo '</tr>';
    $cnt=0;
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr>'; foreach ($row as $v) echo '<td>'.htmlspecialchars(rtrim((string)$v)).'</td>'; echo '</tr>'; $cnt++;
    }
    if ($cnt===0) echo '<tr><td colspan="'.$nf.'"><em>No rows</em></td></tr>';
    echo '</table>';
} else { echo '<p class="err">'.htmlspecialchars(db2_stmt_errormsg()).'</p>'; }

// 5. How does the current EIP session expose the user?
echo '<h2>5. Session / global user variables available</h2>';
echo 'GLOBALS keys with "user","role","profile" in name:<br>';
foreach (array_keys($GLOBALS) as $k) {
    if (preg_match('/user|role|profile|uid|pgm|parm/i',$k))
        echo htmlspecialchars($k).'='.htmlspecialchars((string)$GLOBALS[$k]).'<br>';
}
echo '<br>$userProfile='.htmlspecialchars(isset($userProfile)?(string)$userProfile:'(not set)').'<br>';
echo '$activeRole='.htmlspecialchars(isset($activeRole)?(string)$activeRole:'(not set)').'<br>';
echo '$i5UserProfile='.htmlspecialchars(isset($i5UserProfile)?(string)$i5UserProfile:'(not set)').'<br>';

echo '</body></html>';
?>
