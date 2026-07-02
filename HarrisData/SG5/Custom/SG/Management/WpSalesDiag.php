<?php
// WP Sales Diagnostic - 2024/2025/2026 by item, customer class WP
$conn = db2_connect(
    'DRIVER={IBM i Access ODBC Driver};SYSTEM=10.10.0.5;',
    '', '', array(DB2_ATTR_AUTOCOMMIT => DB2_AUTOCOMMIT_ON)
);
if (!$conn) {
    echo "CONNECT ERROR: " . db2_conn_errormsg() . "\n";
    exit;
}

// Check which price list WP customers use
$sql1 = "
SELECT DISTINCT H.HHPRLV AS PriceList, COUNT(*) AS OrderCount
FROM SGHDSDATA.OEORHH H
WHERE H.HHCLAS = 'WP'
  AND YEAR(H.HHINDT) IN (2024, 2025, 2026)
GROUP BY H.HHPRLV
ORDER BY OrderCount DESC
";
echo "=== WP Price Lists Used ===\n";
$stmt = db2_exec($conn, $sql1);
if (!$stmt) echo "ERR1: " . db2_stmt_errormsg() . "\n";
else {
    while ($row = db2_fetch_assoc($stmt)) {
        echo "PriceList=" . trim($row['PRICELIST']) . " Orders={$row['ORDERCOUNT']}\n";
    }
}

// Sales by item for WP customer class 2024/2025/2026
$sql2 = "
SELECT
    D.DTITEM AS ItemNum,
    D.DTDESC AS Description,
    SUM(CASE WHEN YEAR(H.HHINDT) = 2024 THEN D.DTQSHP ELSE 0 END) AS Qty2024,
    SUM(CASE WHEN YEAR(H.HHINDT) = 2024 THEN D.DTQSHP * D.DTUPRC ELSE 0 END) AS Sales2024,
    AVG(CASE WHEN YEAR(H.HHINDT) = 2024 AND D.DTQSHP > 0 THEN D.DTUPRC ELSE NULL END) AS AvgPrice2024,
    SUM(CASE WHEN YEAR(H.HHINDT) = 2025 THEN D.DTQSHP ELSE 0 END) AS Qty2025,
    SUM(CASE WHEN YEAR(H.HHINDT) = 2025 THEN D.DTQSHP * D.DTUPRC ELSE 0 END) AS Sales2025,
    AVG(CASE WHEN YEAR(H.HHINDT) = 2025 AND D.DTQSHP > 0 THEN D.DTUPRC ELSE NULL END) AS AvgPrice2025,
    SUM(CASE WHEN YEAR(H.HHINDT) = 2026 THEN D.DTQSHP ELSE 0 END) AS Qty2026,
    SUM(CASE WHEN YEAR(H.HHINDT) = 2026 THEN D.DTQSHP * D.DTUPRC ELSE 0 END) AS Sales2026,
    AVG(CASE WHEN YEAR(H.HHINDT) = 2026 AND D.DTQSHP > 0 THEN D.DTUPRC ELSE NULL END) AS AvgPrice2026
FROM SGHDSDATA.OEORHH H
JOIN SGHDSDATA.OEORDT D ON D.DTORD = H.HHORD
WHERE H.HHCLAS = 'WP'
  AND H.HHSTAT IN ('I','C')
  AND YEAR(H.HHINDT) IN (2024, 2025, 2026)
GROUP BY D.DTITEM, D.DTDESC
HAVING SUM(D.DTQSHP) > 0
ORDER BY D.DTITEM
";

echo "\n=== WP Sales 2024/2025/2026 ===\n";
$stmt2 = db2_exec($conn, $sql2);
if (!$stmt2) {
    echo "ERROR: " . db2_stmt_errormsg() . "\n";
} else {
    $rows = [];
    while ($row = db2_fetch_assoc($stmt2)) {
        $rows[] = $row;
    }
    echo json_encode($rows, JSON_PRETTY_PRINT);
}

db2_close($conn);