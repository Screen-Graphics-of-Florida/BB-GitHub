<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');
$now      = new DateTime();
$yrStCymd = ((int)$now->format('Y') - 1900) * 10000 + 0101;
$tmrwCymd = ((int)$now->format('Y') - 1900) * 10000 + (int)$now->format('m') * 100 + (int)$now->format('d') + 1;

$conn = $i5Connect->getConnection();
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Diag: HDCUST.CMDFES</title>
<style>body{font-family:monospace;padding:16px;background:#f5f5f5}
h2{margin-bottom:8px}table{border-collapse:collapse;font-size:12px}
th,td{border:1px solid #ccc;padding:4px 8px}th{background:#000080;color:#fff}
tr:nth-child(even){background:#f0f0f0}.err{color:red}</style>
</head><body>

<h2>HDCUST.CMDFES Diagnostic &mdash; <?php echo $now->format('Y-m-d H:i:s'); ?></h2>
<p>yrStCymd = <?php echo $yrStCymd; ?> &nbsp;|&nbsp; tmrwCymd = <?php echo $tmrwCymd; ?></p>

<?php
// Sample of CMDFES values in current year
$sql = "
    SELECT CMCUST, TRIM(CMCNA1) AS CNAME, CMDFES, CHAR(CMDFES) AS DFES_CHAR
    FROM SGHDSDATA.HDCUST
    WHERE CMDFES >= $yrStCymd
      AND CMDFES <  $tmrwCymd
    ORDER BY CMDFES DESC
    FETCH FIRST 50 ROWS ONLY
";
$stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    $rows = array();
    while ($r = db2_fetch_assoc($stmt)) $rows[] = $r;
    db2_free_stmt($stmt);
    echo '<h3>New accounts this year (CMDFES >= ' . $yrStCymd . '): ' . count($rows) . ' shown (max 50)</h3>';
    echo '<table><tr><th>CMCUST</th><th>CNAME</th><th>CMDFES (raw)</th><th>CMDFES (char)</th></tr>';
    foreach ($rows as $r) {
        echo '<tr><td>' . htmlspecialchars($r['CMCUST']) . '</td>'
           . '<td>' . htmlspecialchars($r['CNAME'])      . '</td>'
           . '<td>' . htmlspecialchars($r['CMDFES'])     . '</td>'
           . '<td>' . htmlspecialchars($r['DFES_CHAR'])  . '</td></tr>';
    }
    echo '</table>';
} else {
    echo '<p class="err">Error on q1: ' . htmlspecialchars(db2_stmt_errormsg()) . '</p>';
}

// Count total new accounts
$cnt = 0;
$s2 = db2_exec($conn, "SELECT COUNT(*) AS CNT FROM SGHDSDATA.HDCUST WHERE CMDFES >= $yrStCymd AND CMDFES < $tmrwCymd", array('cursor' => DB2_SCROLLABLE));
if ($s2) { $r = db2_fetch_assoc($s2); $cnt = $r ? (int)$r['CNT'] : 0; db2_free_stmt($s2); }
echo '<p><strong>Total new accounts this year: ' . $cnt . '</strong></p>';

// Revenue from new accounts this year
$sql3 = "
    SELECT
        c.CMCUST,
        COALESCE(TRIM(c.CMCNA1), '') AS CNAME,
        c.CMDFES,
        SUM(CASE WHEN d.DHORUF <> 0 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END) AS AMT
    FROM SGHDSDATA.OEORDH d
    JOIN SGHDSDATA.OEORHD h ON d.\"DHORD#\" = h.\"OEORD#\"
    JOIN SGHDSDATA.HDCUST c ON h.OESHTO = c.CMCUST
    WHERE d.\"DHSEQ#\" <> 0
      AND d.DHQSTC <> 0
      AND d.DHDTLI >= $yrStCymd
      AND d.DHDTLI <  $tmrwCymd
      AND c.CMDFES >= $yrStCymd
      AND c.CMDFES <  $tmrwCymd
    GROUP BY c.CMCUST, c.CMCNA1, c.CMDFES
    ORDER BY AMT DESC
    FETCH FIRST 50 ROWS ONLY
";
$s3 = db2_exec($conn, $sql3, array('cursor' => DB2_SCROLLABLE));
if ($s3) {
    $rows3 = array(); $tot = 0;
    while ($r = db2_fetch_assoc($s3)) { $rows3[] = $r; $tot += (float)$r['AMT']; }
    db2_free_stmt($s3);
    echo '<h3>Revenue from new accounts this year (top 50 by amt)</h3>';
    echo '<p>Total shown: $' . number_format($tot, 2) . '</p>';
    echo '<table><tr><th>CMCUST</th><th>CNAME</th><th>CMDFES</th><th>AMT</th></tr>';
    foreach ($rows3 as $r) {
        echo '<tr><td>' . htmlspecialchars($r['CMCUST']) . '</td>'
           . '<td>' . htmlspecialchars($r['CNAME'])     . '</td>'
           . '<td>' . htmlspecialchars($r['CMDFES'])    . '</td>'
           . '<td>$' . number_format((float)$r['AMT'], 2) . '</td></tr>';
    }
    echo '</table>';
} else {
    echo '<p class="err">Error on q3: ' . htmlspecialchars(db2_stmt_errormsg()) . '</p>';
}
?>
</body></html>