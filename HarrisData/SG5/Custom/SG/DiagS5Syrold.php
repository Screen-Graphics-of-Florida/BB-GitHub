<?php
// DiagS5Syrold.php
// Compare ENAPOLES SYROLD entries in S5HDSDATA vs SGHDSDATA.
// Confirms whether the test portal is reading from the wrong schema.
// URL: https://portal.screen-graphics.com:5610/Custom/SG/DiagS5Syrold.php

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role = isset($_GET['role']) ? strtoupper(trim($_GET['role'])) : 'ENAPOLES';
$safe = str_replace("'", "''", $role);

function qrows($conn, $sql) {
    $rows = array(); $s = @db2_exec($conn, $sql);
    if (!$s) return array('__error' => db2_stmt_errormsg());
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}

$s5 = qrows($conn,
    "SELECT RTRIM(RDPORT) AS RDPORT, RDSEQN "
  . "FROM S5HDSDATA.SYROLD WHERE RDROLE='$safe' ORDER BY RDSEQN");

$sg = qrows($conn,
    "SELECT RTRIM(RDPORT) AS RDPORT, RDSEQN "
  . "FROM SGHDSDATA.SYROLD WHERE RDROLE='$safe' ORDER BY RDSEQN");

$s5cnt = isset($s5['__error']) ? 0 : count($s5);
$sgcnt = isset($sg['__error']) ? 0 : count($sg);

// Portals in SG but not S5
$sgPorts = array();
if (!isset($sg['__error'])) foreach ($sg as $r) $sgPorts[] = rtrim($r['RDPORT']);
$s5Ports = array();
if (!isset($s5['__error'])) foreach ($s5 as $r) $s5Ports[] = rtrim($r['RDPORT']);
$missing = array_diff($sgPorts, $s5Ports);
$extra   = array_diff($s5Ports, $sgPorts);

db2_close($conn);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>DiagS5Syrold</title>
<style>
body{font:12px Arial,sans-serif;background:#f0f2f5;padding:16px;}
h2{background:#2a5a8c;color:#fff;padding:7px 14px;border-radius:4px;font-size:13px;margin:14px 0 6px;}
table{border-collapse:collapse;width:100%;background:#fff;border-radius:4px;margin-bottom:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);}
th{background:#2a5a8c;color:#fff;padding:4px 10px;text-align:left;font-size:11px;}
td{padding:3px 10px;font-size:11px;font-family:monospace;border-bottom:1px solid #f0f0f0;}
.hit{color:#2e7d32;font-weight:bold;} .miss{color:#c62828;font-weight:bold;}
.warn{background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:8px 12px;margin-bottom:10px;font-weight:bold;}
.ok{background:#d4edda;border:1px solid #28a745;border-radius:4px;padding:8px 12px;margin-bottom:10px;font-weight:bold;}
form{margin-bottom:12px;display:flex;gap:8px;align-items:center;}
input{padding:4px 8px;font-size:13px;border:1px solid #ccc;border-radius:3px;width:180px;}
button{padding:4px 12px;font-size:13px;background:#2a5a8c;color:#fff;border:none;border-radius:3px;cursor:pointer;}
</style></head><body>
<h2>DiagS5Syrold — S5HDSDATA vs SGHDSDATA SYROLD comparison</h2>
<form><label>Role:</label>
<input name="role" value="<?=htmlspecialchars($role)?>">
<button>Check</button></form>

<?php if (!empty($missing)): ?>
<div class="warn">
  CONFIRMED: S5HDSDATA.SYROLD is missing <?=count($missing)?> portals that SGHDSDATA.SYROLD has for <?=htmlspecialchars($role)?>.
  These are invisible in the test portal.<br>
  Missing: <?=htmlspecialchars(implode(', ', $missing))?>
</div>
<?php elseif ($s5cnt === $sgcnt): ?>
<div class="ok">S5HDSDATA and SGHDSDATA have the same <?=$s5cnt?> portals for <?=htmlspecialchars($role)?>. Schema is NOT the issue.</div>
<?php endif; ?>

<h2>S5HDSDATA.SYROLD — <?=$s5cnt?> rows</h2>
<table><tr><th>Seq</th><th>RDPORT</th><th>In SGHDSDATA?</th></tr>
<?php if (isset($s5['__error'])): ?>
<tr><td colspan="3" class="miss">ERROR: <?=htmlspecialchars($s5['__error'])?></td></tr>
<?php else: foreach ($s5 as $r): $inSg = in_array(rtrim($r['RDPORT']), $sgPorts); ?>
<tr><td><?=htmlspecialchars($r['RDSEQN'])?></td>
    <td><?=htmlspecialchars($r['RDPORT'])?></td>
    <td class="<?=$inSg?'hit':'miss'?>"><?=$inSg?'YES':'EXTRA (not in SG)'?></td></tr>
<?php endforeach; endif; ?>
</table>

<h2>SGHDSDATA.SYROLD — <?=$sgcnt?> rows</h2>
<table><tr><th>Seq</th><th>RDPORT</th><th>In S5HDSDATA?</th></tr>
<?php if (isset($sg['__error'])): ?>
<tr><td colspan="3" class="miss">ERROR: <?=htmlspecialchars($sg['__error'])?></td></tr>
<?php else: foreach ($sg as $r): $inS5 = in_array(rtrim($r['RDPORT']), $s5Ports); ?>
<tr><td><?=htmlspecialchars($r['RDSEQN'])?></td>
    <td><?=htmlspecialchars($r['RDPORT'])?></td>
    <td class="<?=$inS5?'hit':'miss'?>"><?=$inS5?'YES':'MISSING from S5'?></td></tr>
<?php endforeach; endif; ?>
</table>
</body></html>
