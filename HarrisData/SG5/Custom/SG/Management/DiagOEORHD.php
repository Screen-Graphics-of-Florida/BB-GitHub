<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

// List all columns in OEORDT
$sql = "SELECT COLUMN_NAME, DATA_TYPE, LENGTH, NUMERIC_SCALE
        FROM SGHDSDATA.SYSCOLUMNS
        WHERE TABLE_NAME = 'OEORDT'
        ORDER BY ORDINAL_POSITION";

$stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
echo '<pre>';
echo "Columns in SGHDSDATA.OEORDT:\n\n";

// Also show 3 sample rows
$samp = db2_exec($conn, "SELECT * FROM SGHDSDATA.OEORDT FETCH FIRST 3 ROWS ONLY", array('cursor' => DB2_SCROLLABLE));
if ($samp) {
    echo "\nSample rows:\n";
    $first = true;
    while ($r = db2_fetch_assoc($samp)) {
        if ($first) { echo implode("\t", array_keys($r)) . "\n"; $first = false; }
        echo implode("\t", array_values($r)) . "\n";
    }
    db2_free_stmt($samp);
}
echo "\nColumn list:\n";
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        printf("%-30s %s(%s)\n",
            $r['COLUMN_NAME'],
            $r['DATA_TYPE'],
            $r['NUMERIC_SCALE'] !== null ? $r['LENGTH'].'.'.$r['NUMERIC_SCALE'] : $r['LENGTH']
        );
    }
    db2_free_stmt($stmt);
} else {
    echo "Error: " . db2_stmt_errormsg() . "\n";
    // Try alternate catalog
    $sql2 = "SELECT COLUMN_NAME, DATA_TYPE, LENGTH
             FROM QSYS2.SYSCOLUMNS
             WHERE TABLE_NAME = 'OEORHD'
               AND TABLE_SCHEMA = 'SGHDSDATA'
             ORDER BY ORDINAL_POSITION";
    $stmt2 = db2_exec($conn, $sql2, array('cursor' => DB2_SCROLLABLE));
    if ($stmt2) {
        echo "\nVia QSYS2.SYSCOLUMNS:\n\n";
        while ($r = db2_fetch_assoc($stmt2)) {
            printf("%-30s %s(%s)\n", $r['COLUMN_NAME'], $r['DATA_TYPE'], $r['LENGTH']);
        }
        db2_free_stmt($stmt2);
    } else {
        echo "Error2: " . db2_stmt_errormsg() . "\n";
    }
}
echo '</pre>';
?>
