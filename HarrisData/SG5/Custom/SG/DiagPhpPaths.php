<?php
// DiagPhpPaths.php
// Shows PHP's view of __FILE__ and backup dir writability in SG5.
// URL: https://portal.screen-graphics.com:5610/Custom/SG/DiagPhpPaths.php

$here    = dirname(__FILE__);
$backup  = $here . '/../Backup Files';
$tmp     = '/tmp';

function testDir($path) {
    $ex = is_dir($path);
    $wr = $ex ? is_writable($path) : false;
    $rp = $ex ? realpath($path) : 'N/A';
    return ['exists' => $ex, 'writable' => $wr, 'realpath' => $rp];
}

function testWrite($path, $label) {
    $file = rtrim($path, '/') . '/diag_write_test_' . time() . '.tmp';
    $ok   = @file_put_contents($file, 'test');
    if ($ok !== false) { @unlink($file); return 'OK'; }
    return 'FAILED — ' . (error_get_last()['message'] ?? 'unknown error');
}

$dirs = [
    'dirname(__FILE__)'        => $here,
    '../Backup Files'          => $backup,
    '/tmp'                     => $tmp,
];
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>DiagPhpPaths</title>
<style>
body{font:12px Arial,sans-serif;background:#f0f2f5;padding:16px;}
h2{background:#2a5a8c;color:#fff;padding:7px 14px;border-radius:4px;font-size:13px;margin:14px 0 6px;}
table{border-collapse:collapse;width:100%;background:#fff;border-radius:4px;margin-bottom:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);}
th{background:#2a5a8c;color:#fff;padding:4px 10px;text-align:left;font-size:11px;}
td{padding:4px 10px;font-size:11px;font-family:monospace;border-bottom:1px solid #f0f0f0;}
.ok{color:#2e7d32;font-weight:bold;} .fail{color:#c62828;font-weight:bold;}
</style></head><body>
<h2>PHP Path Diagnostics — SG5 (port 5610)</h2>
<table>
<tr><th>Label</th><th>Path</th><th>is_dir</th><th>is_writable</th><th>realpath</th><th>Write test</th></tr>
<?php foreach ($dirs as $label => $path): $d = testDir($path); ?>
<tr>
  <td><?=htmlspecialchars($label)?></td>
  <td><?=htmlspecialchars($path)?></td>
  <td class="<?=$d['exists']?'ok':'fail'?>"><?=$d['exists']?'YES':'NO'?></td>
  <td class="<?=$d['writable']?'ok':'fail'?>"><?=$d['writable']?'YES':'NO'?></td>
  <td><?=htmlspecialchars($d['realpath'])?></td>
  <td class="<?=($d['exists']&&$d['writable'])?'ok':'fail'?>">
    <?=$d['exists'] ? testWrite($path, $label) : 'N/A — dir missing'?>
  </td>
</tr>
<?php endforeach; ?>
</table>

<h2>PHP Environment</h2>
<table>
<tr><th>Variable</th><th>Value</th></tr>
<tr><td>__FILE__</td><td><?=htmlspecialchars(__FILE__)?></td></tr>
<tr><td>PHP_OS</td><td><?=htmlspecialchars(PHP_OS)?></td></tr>
<tr><td>PHP_VERSION</td><td><?=htmlspecialchars(PHP_VERSION)?></td></tr>
<tr><td>DOCUMENT_ROOT</td><td><?=htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A')?></td></tr>
<tr><td>SCRIPT_FILENAME</td><td><?=htmlspecialchars($_SERVER['SCRIPT_FILENAME'] ?? 'N/A')?></td></tr>
<tr><td>sys_get_temp_dir()</td><td><?=htmlspecialchars(sys_get_temp_dir())?></td></tr>
</table>
</body></html>
