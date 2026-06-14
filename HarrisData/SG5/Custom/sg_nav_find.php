<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Nav Finder</title>
<style>body{font-family:monospace;font-size:11px;padding:16px;}
pre{background:#f5f5f5;border:1px solid #ccc;padding:8px;overflow-x:auto;white-space:pre-wrap;word-break:break-all;font-size:10px;}
h3{color:#00c;margin:12px 0 4px;}</style></head><body>';
echo '<h2>HarrisData Nav Menu — Source Finder</h2>';

// Common IFS paths where HarrisData portal PHP might live
$searchDirs = array(
    '/www/sg5eip/htdocs/',
    '/www/sg5eip/',
    '/www/zendsvr/htdocs/',
    '/HarrisData/',
    '/HarrisData/SG5/',
    '/usr/local/zendsvr6/htdocs/',
    '/QOpenSys/usr/local/seiden/',
);

// Keywords that would appear in nav-building code
$keywords = array('SYROLD', 'SYPORT', 'SYURLM', 'navigation', 'portal', 'navmenu', 'NavMenu', 'PortalNav');

echo '<h3>1. Directory listings of candidate paths</h3>';
foreach ($searchDirs as $dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        echo '<strong>' . htmlspecialchars($dir) . '</strong> (' . count($files) . ' entries)<br>';
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $fp = $dir . $f;
            $type = is_dir($fp) ? '[DIR]' : '[FILE]';
            echo '&nbsp;&nbsp;' . $type . ' ' . htmlspecialchars($f) . '<br>';
        }
        echo '<br>';
    } else {
        echo '<strong>' . htmlspecialchars($dir) . '</strong> — <em>not found</em><br>';
    }
}

// Search for PHP files containing SYROLD (nav-building code)
echo '<h3>2. PHP files referencing SYROLD or SYPORT (nav query candidates)</h3>';
$searchRoots = array('/www/sg5eip/htdocs/', '/www/sg5eip/', '/HarrisData/');
foreach ($searchRoots as $root) {
    if (!is_dir($root)) continue;
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($iter as $file) {
        if ($file->getExtension() !== 'php') continue;
        $path = $file->getPathname();
        $content = @file_get_contents($path);
        if ($content === false) continue;
        if (stripos($content, 'SYROLD') !== false || stripos($content, 'navmenu') !== false || stripos($content, 'NavMenu') !== false) {
            echo '<strong>' . htmlspecialchars($path) . '</strong> (' . number_format(strlen($content)) . ' bytes)<br>';
        }
    }
}

// Show the content of GetURLParm.php to understand what variables are set
echo '<h3>3. GetURLParm.php content (first 100 lines)</h3>';
$gpFile = '/www/sg5eip/htdocs/GetURLParm.php';
if (!file_exists($gpFile)) {
    // Try relative
    $gpFile = realpath(dirname(__FILE__) . '/../GetURLParm.php');
}
if (file_exists($gpFile)) {
    $lines = file($gpFile);
    $out = array_slice($lines, 0, 100);
    echo '<pre>' . htmlspecialchars(implode('', $out)) . '</pre>';
} else {
    echo '<p>Not found at ' . htmlspecialchars($gpFile) . '</p>';
}

echo '</body></html>';
?>
