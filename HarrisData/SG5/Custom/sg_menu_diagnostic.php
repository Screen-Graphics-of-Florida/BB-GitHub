<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8">
<title>SG Menu Diagnostic</title>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; padding: 10px; }
h2 { background: #336699; color: #fff; padding: 4px 8px; margin-top: 20px; }
table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
th { background: #336699; color: #fff; padding: 3px 6px; text-align: left; }
td { border: 1px solid #ccc; padding: 2px 6px; }
tr:nth-child(even) { background: #f5f5f5; }
.err { color: red; font-weight: bold; }
</style></head><body>';

echo '<h1>SG Menu Diagnostic — SG5 Test</h1>';

function qTable($conn, $label, $sql) {
    echo "<h2>$label</h2>";
    $r = db2_exec($conn, $sql);
    if (!$r) {
        echo '<p class="err">Query error: ' . htmlspecialchars(db2_stmt_errormsg()) . '</p>';
        return;
    }
    $first = true;
    $count = 0;
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
    if ($first) {
        echo '<p><em>No rows returned.</em></p>';
    } else {
        echo '</table><p>Rows: ' . $count . '</p>';
    }
}

// SYPORT — full content
qTable($conn, 'SYPORT — All Portal Rows (ordered by FPPORT, FPSEQ)',
    "SELECT * FROM SYPORT ORDER BY FPPORT, FPSEQ FETCH FIRST 500 ROWS ONLY");

// Max FPID in SYPORT
qTable($conn, 'SYPORT — Max FPID',
    "SELECT MAX(CAST(FPID AS INTEGER)) AS MAX_FPID, COUNT(*) AS TOTAL_ROWS FROM SYPORT");

// SYURLM — last 20 rows and max
qTable($conn, 'SYURLM — Max FUID and row count',
    "SELECT MAX(CAST(FUID AS INTEGER)) AS MAX_FUID, COUNT(*) AS TOTAL_ROWS FROM SYURLM");

qTable($conn, 'SYURLM — Last 20 rows (highest FUID)',
    "SELECT * FROM SYURLM ORDER BY CAST(FUID AS INTEGER) DESC FETCH FIRST 20 ROWS ONLY");

// SYROLD — role assignments
qTable($conn, 'SYROLD — All Role-Portal Assignments',
    "SELECT * FROM SYROLD ORDER BY RDPORT, RDROLE FETCH FIRST 200 ROWS ONLY");

// SYPGMO — sample to understand structure
qTable($conn, 'SYPGMO — Last 10 rows (to understand structure)',
    "SELECT * FROM SYPGMO ORDER BY PMPGM DESC FETCH FIRST 10 ROWS ONLY");

// SYPORT rows containing 'EVENT' or 'CALENDAR' to find Event Calendar
qTable($conn, 'SYPORT — Event Calendar search',
    "SELECT * FROM SYPORT WHERE UPPER(FPDESC) LIKE '%EVENT%' OR UPPER(FPPORT) LIKE '%EVENT%' OR UPPER(FPDESC) LIKE '%CALENDAR%'");

// SYROLD — distinct roles
qTable($conn, 'SYROLD — Distinct Roles',
    "SELECT DISTINCT RDROLE FROM SYROLD ORDER BY RDROLE");

echo '<p style="margin-top:20px;color:#666;">Diagnostic complete — ' . date('Y-m-d H:i:s') . '</p>';
echo '</body></html>';
?>