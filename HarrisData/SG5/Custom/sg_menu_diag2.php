<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Diag2</title>
<style>body{font-family:Arial,sans-serif;font-size:12px;padding:10px;}
h2{background:#336699;color:#fff;padding:4px 8px;margin-top:20px;}
table{border-collapse:collapse;width:100%;margin-bottom:10px;}
th{background:#336699;color:#fff;padding:3px 6px;text-align:left;}
td{border:1px solid #ccc;padding:2px 6px;}
tr:nth-child(even){background:#f5f5f5;}
.err{color:red;font-weight:bold;}
pre{background:#f0f0f0;padding:8px;font-size:11px;}
</style></head><body>';

echo '<h1>SG Menu Diagnostic 2</h1>';

function qTable($conn, $label, $sql) {
    echo "<h2>$label</h2>";
    $r = db2_exec($conn, $sql);
    if (!$r) {
        echo '<p class="err">Error: ' . htmlspecialchars(db2_stmt_errormsg()) . '</p>';
        return;
    }
    $first = true; $count = 0;
    while ($row = db2_fetch_assoc($r)) {
        if ($first) {
            echo '<table><tr>';
            foreach ($row as $k => $v) echo '<th>' . htmlspecialchars($k) . '</th>';
            echo '</tr>';
            $first = false;
        }
        echo '<tr>';
        foreach ($row as $k => $v) echo '<td>' . htmlspecialchars(trim((string)$v)) . '</td>';
        echo '</tr>';
        $count++;
    }
    if ($first) echo '<p><em>No rows.</em></p>';
    else echo '</table><p>Rows: ' . $count . '</p>';
}

// 1. SYURLM columns via SYSCOLUMNS
qTable($conn, 'SYURLM — Column Names',
    "SELECT COLUMN_NAME, DATA_TYPE, LENGTH, COLUMN_DEFAULT, IS_NULLABLE
     FROM QSYS2.SYSCOLUMNS
     WHERE TABLE_NAME = 'SYURLM'
     ORDER BY ORDINAL_POSITION");

// 2. SYURLM — first 5 rows (no cast)
qTable($conn, 'SYURLM — First 5 rows (sample structure)',
    "SELECT * FROM SYURLM FETCH FIRST 5 ROWS ONLY");

// 3. SYURLM — lookup known FUIDs from SGCUSTRPT portal
qTable($conn, 'SYURLM — SGCUSTRPT-related rows (SGSHIPDASH, SGCUSTRPT, and FUID=1)',
    "SELECT * FROM SYURLM WHERE FUID IN ('SGSHIPDASH','SGCUSTRPT','1')");

// 4. SYPGMO columns
qTable($conn, 'SYPGMO — Column Names',
    "SELECT COLUMN_NAME, DATA_TYPE, LENGTH
     FROM QSYS2.SYSCOLUMNS
     WHERE TABLE_NAME = 'SYPGMO'
     ORDER BY ORDINAL_POSITION");

// 5. SYPGMO — first 5 rows
qTable($conn, 'SYPGMO — First 5 rows',
    "SELECT * FROM SYPGMO FETCH FIRST 5 ROWS ONLY");

// 6. SYROLD columns
qTable($conn, 'SYROLD — Column Names',
    "SELECT COLUMN_NAME, DATA_TYPE, LENGTH
     FROM QSYS2.SYSCOLUMNS
     WHERE TABLE_NAME = 'SYROLD'
     ORDER BY ORDINAL_POSITION");

// 7. SYROLD — check if SGCUSTRPT is assigned
qTable($conn, 'SYROLD — SGCUSTRPT assignments',
    "SELECT * FROM SYROLD WHERE RDPORT = 'SGCUSTRPT'");

// 8. SYPORT — check if any SG* portals already exist
qTable($conn, 'SYPORT — Any existing SG* portals',
    "SELECT * FROM SYPORT WHERE FPPORT LIKE 'SG%' ORDER BY FPPORT, FPSEQ");

// 9. Max numeric-safe FUID
qTable($conn, 'SYURLM — FUID values that look numeric (for our custom IDs)',
    "SELECT FUID FROM SYURLM WHERE FUID NOT LIKE '%/%' ORDER BY FUID FETCH FIRST 30 ROWS ONLY");

echo '<p style="margin-top:20px;color:#666;">Diagnostic 2 complete — ' . date('Y-m-d H:i:s') . '</p>';
echo '</body></html>';
?>
