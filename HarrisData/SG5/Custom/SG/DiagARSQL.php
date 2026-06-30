<?php
// AR Aging SQL diagnostic — tests exact query used in ARAgingReport.php
// URL: https://portal.screen-graphics.com:5610/Custom/SG/DiagARSQL.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<pre style="font-family:monospace;font-size:12px;background:#111;color:#0f0;padding:12px">';
echo "=== AR Aging SQL Diagnostic ===\n";
echo "Run: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1 — simple HDINVC row count
$s1 = db2_exec($conn, "SELECT COUNT(*) AS CNT FROM SGHDSDATA.HDINVC",
               array('cursor' => DB2_SCROLLABLE));
if ($s1) {
    $row = db2_fetch_assoc($s1);
    echo "HDINVC total rows: " . $row['CNT'] . "\n";
    db2_free_stmt($s1);
} else {
    echo "HDINVC count failed: " . db2_stmt_errormsg() . "\n";
}

// Test 2 — open invoice count (balance <> 0)
$s2 = db2_exec($conn,
    "SELECT COUNT(*) AS CNT FROM SGHDSDATA.HDINVC
     WHERE (COALESCE(IVIVAM,0) - COALESCE(IVNPOS,0)) <> 0",
    array('cursor' => DB2_SCROLLABLE));
if ($s2) {
    $row = db2_fetch_assoc($s2);
    echo "Open invoices (balance<>0): " . $row['CNT'] . "\n";
    db2_free_stmt($s2);
} else {
    echo "Open count failed: " . db2_stmt_errormsg() . "\n";
}

// Test 3 — CHAR(IVDUED, ISO) syntax check
echo "\nTest CHAR(IVDUED, ISO) on 3 rows:\n";
$s3 = db2_exec($conn,
    "SELECT IVBLTO, IVAINV, IVDUED, CHAR(IVDUED, ISO) AS DUEISO,
            IVIVAM, IVNPOS
     FROM SGHDSDATA.HDINVC
     WHERE (COALESCE(IVIVAM,0) - COALESCE(IVNPOS,0)) <> 0
     FETCH FIRST 3 ROWS ONLY",
    array('cursor' => DB2_SCROLLABLE));
if ($s3) {
    $first = true;
    while ($r = db2_fetch_assoc($s3)) {
        if ($first) { echo '  ' . implode(' | ', array_keys($r)) . "\n"; $first = false; }
        echo '  ' . implode(' | ',
            array_map(function($v){ return str_pad(trim((string)$v), 14); },
                      array_values($r))) . "\n";
    }
    db2_free_stmt($s3);
} else {
    echo "CHAR(IVDUED,ISO) failed: " . db2_stmt_errormsg() . "\n";

    // Test 3b — fallback: try without CHAR conversion
    echo "\nFallback — raw IVDUED type check:\n";
    $s3b = db2_exec($conn,
        "SELECT IVBLTO, IVAINV, IVDUED, IVIVAM, IVNPOS
         FROM SGHDSDATA.HDINVC
         WHERE (COALESCE(IVIVAM,0) - COALESCE(IVNPOS,0)) <> 0
         FETCH FIRST 3 ROWS ONLY",
        array('cursor' => DB2_SCROLLABLE));
    if ($s3b) {
        $first = true;
        while ($r = db2_fetch_assoc($s3b)) {
            if ($first) { echo '  ' . implode(' | ', array_keys($r)) . "\n"; $first = false; }
            echo '  ' . implode(' | ',
                array_map(function($v){ return str_pad(trim((string)$v), 14); },
                          array_values($r))) . "\n";
        }
        db2_free_stmt($s3b);
    } else {
        echo "Fallback also failed: " . db2_stmt_errormsg() . "\n";
    }
}

// Test 4 — full query with HDCUST join (first 3 rows)
echo "\nFull query (first 3 rows):\n";
$sql = "
    SELECT
        TRIM(CHAR(h.IVBLTO))                                      AS CUSTNUM,
        COALESCE(TRIM(c.CMCNA1),'')                               AS CUSTNAME,
        TRIM(CHAR(h.IVAINV))                                      AS INVNUM,
        TRIM(CHAR(h.IVORD))                                       AS ORDNUM,
        TRIM(COALESCE(h.IVARPO,''))                               AS REFNUM,
        INTEGER(COALESCE(h.IVIVDT,0))                             AS INVDATE,
        CHAR(h.IVDUED, ISO)                                       AS DUEDATE,
        DECIMAL(COALESCE(h.IVIVAM,0),15,2)                       AS INVAMT,
        DECIMAL(COALESCE(h.IVNPOS,0),15,2)                       AS PAIDAMT,
        DECIMAL(COALESCE(h.IVIVAM,0)-COALESCE(h.IVNPOS,0),15,2) AS BALANCE
    FROM SGHDSDATA.HDINVC h
    LEFT JOIN SGHDSDATA.HDCUST c ON h.IVBLTO = c.CMCUST
    WHERE (COALESCE(h.IVIVAM,0) - COALESCE(h.IVNPOS,0)) <> 0
    ORDER BY COALESCE(TRIM(c.CMCNA1),''), h.IVBLTO, h.IVAINV
    FETCH FIRST 3 ROWS ONLY
";
$s4 = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($s4) {
    $first = true;
    while ($r = db2_fetch_assoc($s4)) {
        if ($first) { echo '  ' . implode(' | ', array_keys($r)) . "\n"; $first = false; }
        echo '  ' . implode(' | ',
            array_map(function($v){ return str_pad(trim((string)$v), 16); },
                      array_values($r))) . "\n";
    }
    db2_free_stmt($s4);
} else {
    echo "Full query failed: " . db2_stmt_errormsg() . "\n";
}

// Also check if $eID variable exists in this context
echo "\n\$eID available: " . (isset($eID) ? "YES => " . var_export($eID, true) : "NO (undefined)") . "\n";

echo "\n=== Done ===\n";
echo '</pre>';
?>