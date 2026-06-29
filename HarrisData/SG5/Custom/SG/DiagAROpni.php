<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

$tables = [
    'ARAIWK',   // AR Aging work table (populated by HARAG2 RPG)
    'ARARIW',   // AR AR Invoice Work? (Physical file)
    'ARIVIV',   // AR Invoice (IV prefix fields)
    'ARIVDS',   // AR Invoice Detail/Summary
    'ARIVDW',   // AR Invoice Detail
    'ARIVEN',   // AR Invoice Entry
];

echo '<pre style="font-family:monospace;font-size:12px;background:#111;color:#0f0;padding:12px">';
echo "=== AR Aging Table Diagnostic ===\n";
echo "Run: " . date('Y-m-d H:i:s') . "\n";

foreach ($tables as $tbl) {
    // Try SGHDSDATA first, then QTEMP (for work tables)
    foreach (['SGHDSDATA', 'QTEMP'] as $schema) {
        $sql = "SELECT COLUMN_NAME, DATA_TYPE, LENGTH, NUMERIC_SCALE, COLUMN_TEXT
                FROM QSYS2.SYSCOLUMNS
                WHERE TABLE_NAME = '$tbl'
                  AND TABLE_SCHEMA = '$schema'
                ORDER BY ORDINAL_POSITION";
        $stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
        if (!$stmt) continue;
        $cols = [];
        while ($r = db2_fetch_assoc($stmt)) { $cols[] = $r; }
        db2_free_stmt($stmt);
        if (count($cols) === 0) continue;

        echo "\n\n=== $schema.$tbl (" . count($cols) . " columns) ===\n";
        foreach ($cols as $c) {
            $scale = $c['NUMERIC_SCALE'] !== null ? '.' . $c['NUMERIC_SCALE'] : '';
            $txt   = trim((string)$c['COLUMN_TEXT']);
            printf("  %-28s %-14s  %s\n",
                $c['COLUMN_NAME'],
                $c['DATA_TYPE'] . '(' . $c['LENGTH'] . $scale . ')',
                $txt);
        }

        // 5 sample rows
        $s2 = db2_exec($conn, "SELECT * FROM $schema.$tbl FETCH FIRST 5 ROWS ONLY", array('cursor' => DB2_SCROLLABLE));
        if ($s2) {
            echo "  -- Sample data (5 rows):\n";
            $first = true;
            while ($row = db2_fetch_assoc($s2)) {
                if ($first) { echo '  ' . implode(' | ', array_keys($row)) . "\n"; $first = false; }
                echo '  ' . implode(' | ', array_map(function($v){ return trim((string)$v); }, array_values($row))) . "\n";
            }
            db2_free_stmt($s2);
        }
        break; // found in this schema, don't check others
    }
}

// Also check the hdList table definition for tblID=177
echo "\n\n=== HDLIST table def tblID=177 ===\n";
$s = db2_exec($conn,
    "SELECT * FROM SGHDSDATA.HDHDRM WHERE HHTBID=177 FETCH FIRST 1 ROWS ONLY",
    array('cursor' => DB2_SCROLLABLE));
if ($s) {
    $first = true;
    while ($r = db2_fetch_assoc($s)) {
        if ($first) { echo implode(' | ', array_keys($r)) . "\n"; $first = false; }
        echo implode(' | ', array_map(function($v){ return trim((string)$v); }, array_values($r))) . "\n";
    }
    db2_free_stmt($s);
} else {
    echo "  HDHDRM query error or table not found\n";
}

echo "\n\n=== Done ===\n";
echo '</pre>';
?>