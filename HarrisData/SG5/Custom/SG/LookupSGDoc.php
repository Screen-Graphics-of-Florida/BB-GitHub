<?php
// READ-ONLY diagnostic — no DB writes
// Run at: https://portal.screen-graphics.com:5610/Custom/SG/LookupSGDoc.php

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB2 connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

function dump($conn, $label, $sql) {
    echo "<h3 style='font-family:Arial;margin:16px 0 4px'>" . htmlspecialchars($label) . "</h3>";
    $stmt = db2_exec($conn, $sql);
    if (!$stmt) {
        echo "<p style='color:red;font-family:monospace'>ERR: " . htmlspecialchars(db2_stmt_errormsg()) . "</p>";
        return;
    }
    echo "<table border='1' cellpadding='4' style='border-collapse:collapse;font-size:12px;font-family:monospace'>";
    $first = true;
    while ($row = db2_fetch_assoc($stmt)) {
        if ($first) {
            echo "<tr style='background:#2a5a8c;color:#fff'>";
            foreach (array_keys($row) as $col) echo "<th style='padding:4px 8px'>$col</th>";
            echo "</tr>";
            $first = false;
        }
        echo "<tr>";
        foreach ($row as $v) echo "<td style='padding:3px 8px'>" . htmlspecialchars(trim((string)$v)) . "</td>";
        echo "</tr>";
    }
    if ($first) echo "<tr><td style='color:#999;padding:4px 8px'><em>No rows</em></td></tr>";
    echo "</table>";
}

echo "<body style='font-family:Arial;padding:20px;background:#f5f5f5'>";
echo "<h2 style='color:#2a5a8c'>SG Documentation — DB Lookup (READ ONLY)</h2>";

// 1. Find the portal in SYURLM — anything with DOC in FUID or description
dump($conn, '1. SYURLM rows matching DOC',
    "SELECT FUID, FUDESC, FUTITL, FUTRGT, LEFT(FUURL,80) AS FUURL
     FROM S5HDSDATA.SYURLM
     WHERE UPPER(FUID) LIKE '%DOC%'
        OR UPPER(FUDESC) LIKE '%DOC%'
        OR UPPER(FUTITL) LIKE '%DOC%'
     ORDER BY FUID");

// 2. Find all SYPORT rows for any portal containing DOC
dump($conn, '2. SYPORT rows matching DOC',
    "SELECT FPPORT, FPPAGE, FPSEQ, FPID, FPDESC, FPTITL
     FROM S5HDSDATA.SYPORT
     WHERE UPPER(FPPORT) LIKE '%DOC%'
        OR UPPER(FPID)   LIKE '%DOC%'
        OR UPPER(FPDESC) LIKE '%DOC%'
     ORDER BY FPPORT, FPPAGE, FPSEQ");

// 3. If we suspect SGDOC, show ALL SYPORT rows for it regardless of name
dump($conn, '3. ALL SYPORT rows where FPPORT = SGDOC',
    "SELECT FPPORT, FPPAGE, FPSEQ, FPID, FPDESC, FPTITL
     FROM S5HDSDATA.SYPORT
     WHERE FPPORT = 'SGDOC'
     ORDER BY FPPAGE, FPSEQ");

// 4. ALL SYURLM rows whose FUID starts with SGDOC
dump($conn, '4. SYURLM rows where FUID starts with SGDOC',
    "SELECT FUID, FUDESC, FUTITL, FUTRGT, LEFT(FUURL,100) AS FUURL
     FROM S5HDSDATA.SYURLM
     WHERE FUID LIKE 'SGDOC%'
     ORDER BY FUID");

// 5. Look for "Order Creation" or "E-Mailing" in SYURLM to find the pattern
dump($conn, '5. SYURLM rows matching Order Creation or E-Mailing',
    "SELECT FUID, FUDESC, FUTITL, FUTRGT, LEFT(FUURL,100) AS FUURL
     FROM S5HDSDATA.SYURLM
     WHERE UPPER(FUDESC) LIKE '%ORDER CREATION%'
        OR UPPER(FUDESC) LIKE '%E-MAIL%'
        OR UPPER(FUTITL) LIKE '%ORDER CREATION%'
        OR UPPER(FUTITL) LIKE '%E-MAIL%'
     ORDER BY FUID");

db2_close($conn);
echo "</body>";
?>
