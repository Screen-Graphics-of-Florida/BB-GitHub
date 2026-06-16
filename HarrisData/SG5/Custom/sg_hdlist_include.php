<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>hdListInclude Reader</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
pre{background:#f5f5f5;border:1px solid #ccc;padding:8px;font-size:10px;white-space:pre-wrap;word-break:break-all;overflow-x:auto;}
.ok{color:green;font-weight:bold;}.miss{color:#c00;}
</style></head><body><h1>hdListInclude.php — Design Page Resolution</h1>';

// 1. Find hdListInclude.php
$candidates = array(
    '/HarrisData/SG5/hdListInclude.php',
    '/HarrisData/SG5/HdListInclude.php',
    '/HarrisData/EIP/hdListInclude.php',
    '/www/sg5eip/htdocs/hdListInclude.php',
);

echo '<h2>1. Locating hdListInclude.php</h2>';
$found = '';
foreach ($candidates as $p) {
    $ex = file_exists($p);
    echo ($ex ? '<span class="ok">FOUND</span>' : '<span class="miss">miss </span>') . '  ' . htmlspecialchars($p) . '<br>';
    if ($ex && $found === '') $found = $p;
}

// 2. Show lines mentioning design page, tblID, include, require, dp
if ($found !== '') {
    $lines = file($found);
    echo '<h2>2. Lines mentioning design, tblID, dp, include, require, path (first 80 matches)</h2>';
    $cnt = 0;
    foreach ($lines as $n => $line) {
        if (preg_match('/design|tblID|tbl_id|dpPath|dp\d|DesignPage|designPg|SYDSGN|PDTBID|require|include|\.php/i', $line)) {
            echo '<b>L'.($n+1).':</b> '.htmlspecialchars(rtrim($line)).'<br>';
            if (++$cnt >= 80) { echo '...(truncated)<br>'; break; }
        }
    }
    echo '<h2>3. Full file (first 150 lines)</h2><pre>';
    echo htmlspecialchars(implode('', array_slice($lines, 0, 150)));
    echo '</pre>';
} else {
    echo '<p class="miss">hdListInclude.php not found. Trying broader IFS search...</p>';
}

// 3. Broader search for hdListInclude
echo '<h2>4. Broader search for hdListInclude.php on IFS</h2>';
$roots = array('/HarrisData/', '/www/sg5eip/');
foreach ($roots as $root) {
    if (!is_dir($root)) continue;
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CATCH_GET_CHILD
    );
    foreach ($iter as $file) {
        $name = strtolower($file->getFilename());
        if (strpos($name, 'hdlistinclude') !== false || strpos($name, 'hdlist') !== false) {
            echo '<span class="ok">FOUND</span> ' . htmlspecialchars($file->getPathname()) . '<br>';
        }
    }
}

// 4. Also look for any file named dp199 anywhere
echo '<h2>5. Search for dp199 design page files</h2>';
$roots2 = array('/HarrisData/', '/www/sg5eip/');
foreach ($roots2 as $root) {
    if (!is_dir($root)) continue;
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CATCH_GET_CHILD
    );
    foreach ($iter as $file) {
        $name = strtolower($file->getFilename());
        if (strpos($name, '199') !== false || strpos($name, 'dp199') !== false) {
            echo '<span class="ok">FOUND</span> ' . htmlspecialchars($file->getPathname()) . '<br>';
        }
    }
}
echo '<p>(search complete)</p>';

echo '</body></html>';
?>
