<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>hdList Reader</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
pre{background:#f5f5f5;border:1px solid #ccc;padding:8px;overflow-x:auto;font-size:10px;white-space:pre-wrap;word-break:break-all;}
.ok{color:green;font-weight:bold;}.miss{color:red;}
</style></head><body><h1>hdList.php Design Page 199 — Source Finder</h1>';

// 1. Find hdList.php on the IFS
$candidates = array(
    '/www/sg5eip/htdocs/hdList.php',
    '/www/sg5eip/htdocs/HdList.php',
    '/www/sg5eip/htdocs/hdlist.php',
    '/www/sg5eip2/htdocs/hdList.php',
    '/www/zendsvr/htdocs/hdList.php',
    '/www/zendsvr6/htdocs/hdList.php',
    '/HarrisData/SG5/hdList.php',
    '/HarrisData/hdList.php',
);

echo '<h2>1. Locating hdList.php</h2>';
$hdListPath = '';
foreach ($candidates as $p) {
    $exists = file_exists($p);
    echo ($exists ? '<span class="ok">FOUND</span>' : '<span class="miss">miss </span>') . '  ' . htmlspecialchars($p) . '<br>';
    if ($exists && $hdListPath === '') $hdListPath = $p;
}

// 2. Show key section of hdList.php (search for "design" or "tblID" or "199")
if ($hdListPath !== '') {
    echo '<h2>2. hdList.php — lines containing "design", "tblID", "tbl_id", "dpPath", "include" (first 60 matches)</h2>';
    $lines = file($hdListPath);
    $cnt = 0;
    foreach ($lines as $n => $line) {
        if (preg_match('/design|tblID|tbl_id|dpPath|DesignPage|designPage|dp_path|dp\d+|tblid/i', $line)) {
            echo '<b>L'.($n+1).':</b> '.htmlspecialchars(rtrim($line)).'<br>';
            if (++$cnt >= 60) { echo '...(truncated)<br>'; break; }
        }
    }
    if ($cnt === 0) echo '<em>No matching lines found — showing first 80 lines:</em><br>';

    echo '<h2>3. hdList.php — first 80 lines</h2><pre>';
    echo htmlspecialchars(implode('', array_slice($lines, 0, 80)));
    echo '</pre>';
} else {
    echo '<p style="color:red">hdList.php not found in any candidate path.</p>';
}

// 3. Search for dp199 files
echo '<h2>4. IFS search for dp199 / design page 199 files</h2>';
$searchRoots = array('/www/sg5eip/', '/HarrisData/SG5/', '/HarrisData/', '/www/');
foreach ($searchRoots as $root) {
    if (!is_dir($root)) { echo htmlspecialchars($root) . ' — not accessible<br>'; continue; }
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

// 4. Check all non-SY tables in S5HDSDATA vs SGHDSDATA for design page mapping
echo '<h2>5. Non-SY tables in S5HDSDATA with a numeric-looking column (possible tblID map)</h2>';
$r = db2_exec($conn, "SELECT T.TABLE_NAME, C.COLUMN_NAME, C.DATA_TYPE
    FROM QSYS2.SYSTABLES T
    JOIN QSYS2.SYSCOLUMNS C ON C.TABLE_SCHEMA=T.TABLE_SCHEMA AND C.TABLE_NAME=T.TABLE_NAME
    WHERE T.TABLE_SCHEMA='S5HDSDATA'
    AND T.TABLE_NAME NOT LIKE 'SY%'
    AND (C.COLUMN_NAME LIKE '%TBL%' OR C.COLUMN_NAME LIKE '%TABLE%' OR C.COLUMN_NAME LIKE '%PGID%' OR C.COLUMN_NAME LIKE '%DSGNPG%')
    ORDER BY T.TABLE_NAME, C.COLUMN_NAME
    FETCH FIRST 50 ROWS ONLY");
echo '<table border="1" cellpadding="3" style="border-collapse:collapse;"><tr><th>TABLE</th><th>COLUMN</th><th>TYPE</th></tr>';
$cnt5 = 0;
while ($row = db2_fetch_assoc($r)) {
    echo '<tr><td>'.htmlspecialchars($row['TABLE_NAME']).'</td><td>'.htmlspecialchars($row['COLUMN_NAME']).'</td><td>'.htmlspecialchars($row['DATA_TYPE']).'</td></tr>';
    $cnt5++;
}
if ($cnt5 === 0) echo '<tr><td colspan="3"><em>None found</em></td></tr>';
echo '</table>';

echo '</body></html>';
?>
