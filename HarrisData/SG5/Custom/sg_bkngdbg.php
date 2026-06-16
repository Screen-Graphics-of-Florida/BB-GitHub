<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
date_default_timezone_set('America/Chicago');

$conn    = $i5Connect->getConnection();
$slsnum  = isset($_GET['sls'])  ? (int)$_GET['sls']  : 64;
$dateiso = isset($_GET['date']) ? $_GET['date']       : date('Y-m-d');
$next    = date('Y-m-d', strtotime($dateiso . ' +1 day'));

// Diagnostic 1: check for duplicate SMSLSM rows in HDSLSM for this salesperson
$sqlDup = "SELECT COUNT(*) AS CNT FROM SGHDSDATA.HDSLSM WHERE SMSLSM = $slsnum";
$stmtDup = db2_exec($conn, $sqlDup);
$dupCount = $stmtDup ? (int)db2_fetch_assoc($stmtDup)['CNT'] : '?';
if ($stmtDup) db2_free_stmt($stmtDup);

$sql = "
    SELECT
        h.\"OEORD#\"          AS ORDNUM,
        h.OEORTY              AS ORTY,
        TRIM(CHAR(h.OESLSM))  AS SLSNUM,
        h.OEBDTE              AS BDTE,
        d.ODOSTP              AS ODOSTP,
        TRIM(d.ODITEM)        AS ITEM,
        d.ODQORD              AS QORD,
        d.ODSLPR              AS SLPR,
        d.ODQORD * d.ODSLPR   AS LINEAMT,
        TRIM(d.ODIMDS)        AS ITEMDESC
    FROM SGHDSDATA.OEORHD h
    JOIN SGHDSDATA.OEORDT d ON h.\"OEORD#\" = d.\"ODORD#\"
    WHERE h.OESLSM = $slsnum
      AND h.OEORTY NOT IN ('Q', 'U')
      AND d.ODOSTP >= TIMESTAMP('$dateiso', '00:00:00')
      AND d.ODOSTP <  TIMESTAMP('$next',    '00:00:00')
    ORDER BY h.\"OEORD#\", d.ODOSTP
";

$stmt  = db2_exec($conn, $sql);
$rows  = array();
$total = 0.0;
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) { $rows[] = $r; $total += (float)$r['LINEAMT']; }
    db2_free_stmt($stmt);
}
$err = $stmt ? '' : db2_stmt_errormsg();
?>
<!DOCTYPE html><html><head><meta charset="utf-8">
<title>Bookings Debug &mdash; Sls#<?php echo $slsnum; ?> <?php echo $dateiso; ?></title>
<style>
body{font-family:Arial,sans-serif;font-size:13px;padding:16px;background:#f5f5f5;}
h2{margin-bottom:8px;}
table{border-collapse:collapse;width:100%;background:#fff;}
th{background:#003087;color:#fff;padding:5px 10px;text-align:right;}
th:first-child,th:nth-child(2),th:last-child{text-align:left;}
td{padding:4px 10px;border-bottom:1px solid #ddd;text-align:right;white-space:nowrap;}
td:first-child,td:nth-child(2),td:last-child{text-align:left;}
tr:nth-child(even){background:#f0f0f0;}
tfoot td{background:#ddd;font-weight:bold;border-top:2px solid #888;}
.err{background:#fdd;color:#900;padding:8px;margin-bottom:12px;}
form{margin-bottom:14px;}
input{padding:3px 6px;font-size:13px;}
</style>
</head><body>
<h2>Bookings Line Detail &mdash; Sls# <?php echo $slsnum; ?> &mdash; <?php echo $dateiso; ?></h2>
<form method="get">
  Sls#: <input name="sls" value="<?php echo $slsnum; ?>" size="5">
  Date: <input name="date" value="<?php echo $dateiso; ?>" size="12">
  <input type="submit" value="Go">
</form>
<?php if ($err): ?>
<div class="err">Query error: <?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>
<p>Filter: ODOSTP &gt;= '<?php echo $dateiso; ?> 00:00:00' AND &lt; '<?php echo $next; ?> 00:00:00' &nbsp;&bull;&nbsp; <?php echo count($rows); ?> lines &nbsp;&bull;&nbsp; OEORTY NOT IN (Q,U) &nbsp;&bull;&nbsp; <strong style="color:<?php echo $dupCount > 1 ? 'red' : 'green'; ?>">HDSLSM rows for sls#<?php echo $slsnum; ?>: <?php echo $dupCount; ?><?php echo $dupCount > 1 ? ' ← DUPLICATE — causing fan-out!' : ''; ?></strong></p>
<table>
<thead><tr>
  <th>Order#</th><th>OrTy</th><th>ODOSTP (timestamp)</th>
  <th>BDTE (CYMD)</th><th>Item</th><th>Description</th>
  <th>ODQORD</th><th>ODSLPR</th><th>Line Amt</th>
</tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?php echo htmlspecialchars($r['ORDNUM']); ?></td>
  <td><?php echo htmlspecialchars($r['ORTY']); ?></td>
  <td><?php echo htmlspecialchars($r['ODOSTP']); ?></td>
  <td><?php echo htmlspecialchars($r['BDTE']); ?></td>
  <td><?php echo htmlspecialchars($r['ITEM']); ?></td>
  <td><?php echo htmlspecialchars($r['ITEMDESC']); ?></td>
  <td><?php echo number_format((float)$r['QORD'], 4); ?></td>
  <td><?php echo number_format((float)$r['SLPR'], 5); ?></td>
  <td><?php echo '$' . number_format((float)$r['LINEAMT'], 2); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot><tr>
  <td colspan="7">TOTAL (<?php echo count($rows); ?> lines)</td>
  <td>$<?php echo number_format($total, 2); ?></td>
</tr></tfoot>
</table>
</body></html>
