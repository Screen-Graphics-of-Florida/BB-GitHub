<?php
// Diagnostic — AR invoice table columns
// URL: https://portal.screen-graphics.com:5610/Custom/SG/DiagAROpni.php

require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

// Tables to inspect — HDINVC added per user tip; ARIVIV is the AR open-item table
$tables = ['HDINVC', 'ARIVIV', 'ARIVDS', 'ARIVEN'];

echo '<pre style="font-family:monospace;font-size:12px;background:#111;color:#0f0;padding:12px">';
echo "=== AR Invoice Table Column Diagnostic ===\n";
echo "Run: " . date('Y-m-d H:i:s') . "\n";

foreach ($tables as $tbl) {
    $sql = "SELECT COLUMN_NAME, DATA_TYPE, LENGTH, NUMERIC_SCALE, COLUMN_TEXT
            FROM QSYS2.SYSCOLUMNS
            WHERE TABLE_NAME = '$tbl'
              AND TABLE_SCHEMA = 'SGHDSDATA'
            ORDER BY ORDINAL_POSITION";
    $stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$stmt) { echo "\n=== $tbl: query error ===\n"; continue; }
    $cols = [];
    while ($r = db2_fetch_assoc($stmt)) { $cols[] = $r; }
    db2_free_stmt($stmt);
    if (!$cols) { echo "\n=== $tbl: not found in SGHDSDATA ===\n"; continue; }

    echo "\n\n=== SGHDSDATA.$tbl (" . count($cols) . " columns) ===\n";
    foreach ($cols as $c) {
        $sc  = $c['NUMERIC_SCALE'] !== null ? '.' . $c['NUMERIC_SCALE'] : '';
        $txt = trim((string)$c['COLUMN_TEXT']);
        printf("  %-28s %-14s  %s\n",
            $c['COLUMN_NAME'],
            $c['DATA_TYPE'] . '(' . $c['LENGTH'] . $sc . ')',
            $txt);
    }

    // 5 sample rows
    $s2 = db2_exec($conn, "SELECT * FROM SGHDSDATA.$tbl FETCH FIRST 5 ROWS ONLY",
                   array('cursor' => DB2_SCROLLABLE));
    if ($s2) {
        echo "  -- Sample (5 rows):\n";
        $first = true;
        while ($row = db2_fetch_assoc($s2)) {
            if ($first) { echo '  ' . implode(' | ', array_keys($row)) . "\n"; $first = false; }
            echo '  ' . implode(' | ',
                array_map(function($v){ return str_pad(trim((string)$v), 12); },
                          array_values($row))) . "\n";
        }
        db2_free_stmt($s2);
    }
}

echo "\n\n=== Done ===\n";
echo '</pre>';
?>