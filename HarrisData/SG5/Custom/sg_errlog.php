<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG5 Logs</title>
<style>body{font-family:monospace;font-size:11px;padding:10px;background:#111;color:#0f0;}
h2,h3{color:#ff0;}a{color:#8cf;}pre{background:#000;padding:8px;border:1px solid #333;overflow-x:auto;}</style></head><body>';
echo '<h2>SG5 Diagnostics</h2>';

// PHP ini info
echo '<h3>PHP Error Config</h3><pre>';
echo 'PHP Version: ' . phpversion() . "\n";
echo 'error_log (ini): ' . ini_get('error_log') . "\n";
echo 'log_errors:      ' . ini_get('log_errors') . "\n";
echo 'display_errors:  ' . ini_get('display_errors') . "\n";
echo 'error_reporting: ' . ini_get('error_reporting') . "\n";
echo '</pre>';

// /tmp/sg_install_error.log
echo '<h3>/tmp/sg_install_error.log</h3>';
$tmpLog = '/tmp/sg_install_error.log';
if (!file_exists($tmpLog)) {
    echo '<p style="color:#f80">File does not exist — install script never ran file_put_contents, likely a parse error before any PHP executed.</p>';
} else {
    $content = file_get_contents($tmpLog);
    if ($content === false || $content === '') {
        echo '<p style="color:#f80">File exists but is empty.</p>';
    } else {
        echo '<pre>' . htmlspecialchars($content) . '</pre>';
    }
    echo '<p><a href="?cleartmp=1">Clear /tmp log</a></p>';
}

if (isset($_GET['cleartmp'])) {
    file_put_contents($tmpLog, '');
    echo '<p style="color:#0f0">Cleared.</p>';
}

// Apache error log
$logDir  = '/www/sg5eip/logs/';
$pattern = $logDir . 'error_log.*';
$files   = glob($pattern);
$logFile = '';
if ($files) { rsort($files); $logFile = $files[0]; }

$lines = 80;
if (isset($_GET['lines'])) $lines = max(10, min(500, (int)$_GET['lines']));

echo '<h3>Apache Error Log</h3>';
if (!$logFile) {
    echo '<p style="color:red">No error_log.* file found in ' . htmlspecialchars($logDir) . '</p>';
} else {
    echo '<p>File: ' . htmlspecialchars($logFile) . ' &nbsp;|&nbsp; ';
    echo '<a href="?lines=50">50</a> <a href="?lines=80">80</a> <a href="?lines=200">200</a> lines</p>';
    $allLines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($allLines === false) {
        echo '<p style="color:red">Could not read file.</p>';
    } else {
        $tail = array_slice($allLines, -$lines);
        echo '<pre>';
        foreach ($tail as $line) echo htmlspecialchars($line) . "\n";
        echo '</pre>';
    }
}

echo '</body></html>';
?>
