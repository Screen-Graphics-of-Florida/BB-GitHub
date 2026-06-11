<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
date_default_timezone_set('America/Chicago');

$slsNum  = isset($_GET['sls'])    ? (int)$_GET['sls']       : 0;
$period  = isset($_GET['period']) ? $_GET['period']          : 'day';
$slsName = isset($_GET['name'])   ? trim($_GET['name'])      : '';

$now     = new DateTime();
$dow     = (int)$now->format('N');
$wkSt    = clone $now; $wkSt->modify('-' . ($dow - 1) . ' days');
$moSt    = new DateTime($now->format('Y-m-01'));
$yrSt    = new DateTime($now->format('Y-01-01'));
$next    = clone $now; $next->modify('+1 day');

$periodDates = array(
    'day'   => array('from' => $now->format('Y-m-d'),       'to' => $next->format('Y-m-d'),    'label' => 'Today'),
    'week'  => array('from' => $wkSt->format('Y-m-d'),      'to' => $next->format('Y-m-d'),    'label' => 'This Week'),
    'month' => array('from' => $moSt->format('Y-m-d'),      'to' => $next->format('Y-m-d'),    'label' => 'This Month'),
    'year'  => array('from' => $yrSt->format('Y-m-d'),      'to' => $next->format('Y-m-d'),    'label' => 'This Year'),
);

if (!isset($periodDates[$period])) $period = 'day';
$pd   = $periodDates[$period];
$from = $pd['from'];
$to   = $pd['to'];

$conn = $i5Connect->getConnection();

$sql = "
    SELECT
        h.\"OEORD#\"                                                                   AS ORDNUM,
        h.OEBDTE                                                                       AS BDTE,
        d.ODOSTP                                                                       AS ODOSTP,
        COALESCE(TRIM(c.CMCNA1), '')                                                   AS CUSTNAME,
        TRIM(d.ODITEM)                                                                 AS ITEM,
        TRIM(d.ODIMDS)                                                                 AS ITEMDESC,
        d.ODQORD                                                                       AS QORD,
        d.ODSLPR                                                                       AS SLPR,
        d.ODQORD * d.ODSLPR                                                            AS LINEAMT,
        CASE WHEN s.SMREGN <> 'INACT' THEN TRIM(s.SMSNA1) ELSE 'Ex-Sales' END        AS SLSNAME
    FROM SGHDSDATA.OEORHD h
    JOIN SGHDSDATA.OEORDT d  ON h.\"OEORD#\" = d.\"ODORD#\"
    LEFT JOIN SGHDSDATA.HDCUST c ON h.OESHTO = c.CMCUST
    LEFT JOIN SGHDSDATA.HDSLSM s ON h.OESLSM = s.SMSLSM
    WHERE h.OESLSM = $slsNum
      AND h.OEORTY NOT IN ('Q', 'U')
      AND d.ODOSTP >= TIMESTAMP('$from', '00:00:00')
      AND d.ODOSTP <  TIMESTAMP('$to',   '00:00:00')
    ORDER BY h.\"OEORD#\", d.ODOSTP
";

$rows  = array();
$total = 0.0;
$err   = '';
$stmt  = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $rows[]  = $r;
        $total  += (float)$r['LINEAMT'];
    }
    db2_free_stmt($stmt);
} else {
    $err = db2_stmt_errormsg();
}

if (!empty($rows)) { $slsName = $rows[0]['SLSNAME']; }

// Group by order number for subtotals
$orders = array();
foreach ($rows as $r) {
    $ord = $r['ORDNUM'];
    if (!isset($orders[$ord])) {
        $orders[$ord] = array(
            'ordnum'   => $ord,
            'bdte'     => $r['BDTE'],
            'custname' => $r['CUSTNAME'],
            'lines'    => array(),
            'subtotal' => 0.0,
        );
    }
    $orders[$ord]['lines'][]  = $r;
    $orders[$ord]['subtotal'] += (float)$r['LINEAMT'];
}

function fmt($n) { return '$' . number_format((float)$n, 2); }
function cymdToDate($v) {
    $v = (int)$v; if ($v <= 0) return '';
    $yy = intval($v / 10000); $mm = intval(($v % 10000) / 100); $dd = $v % 100;
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $yy);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bookings Detail &mdash; <?php echo htmlspecialchars($slsName); ?> &mdash; <?php echo $pd['label']; ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; background: #edf1f7; color: #1a2233; }
.topbar { background: #003087; color: #fff; padding: 8px 16px; display: flex; align-items: center; justify-content: space-between; }
.topbar h1 { font-size: 14px; font-weight: 700; }
.topbar .meta { font-size: 11px; color: #b8cfee; }
.content { padding: 12px 14px; }
.summary-bar { display: flex; gap: 16px; margin-bottom: 10px; flex-wrap: wrap; }
.kpi { background: #fff; border: 1px solid #d0d7e2; border-radius: 4px; padding: 8px 16px; min-width: 140px; }
.kpi .val { font-size: 22px; font-weight: 700; color: #003087; }
.kpi .lbl { font-size: 10px; color: #5a6478; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }
.order-block { background: #fff; border: 1px solid #c8d0de; border-radius: 4px; margin-bottom: 8px; overflow: hidden; }
.order-header { background: #002060; color: #fff; padding: 5px 12px; display: flex; align-items: center; gap: 16px; font-size: 12px; font-weight: 700; }
.order-header .ord-num { font-size: 14px; }
.order-header .cust { color: #a8c4f0; font-weight: 400; }
.order-header .subtot { margin-left: auto; font-size: 14px; color: #6db3ff; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
th { background: #1a3a6b; color: #c8d8f0; padding: 4px 10px; text-align: right; font-size: 11px; font-weight: 700; white-space: nowrap; }
th:first-child, th:nth-child(2), th:nth-child(3) { text-align: left; }
td { padding: 4px 10px; text-align: right; border-bottom: 1px solid #eef; white-space: nowrap; color: #222; }
td:first-child, td:nth-child(2), td:nth-child(3) { text-align: left; }
tr:nth-child(even) td { background: #f7f8fc; }
.neg { color: #b00; }
.err { background: #fdd; color: #900; padding: 8px 12px; border-radius: 4px; margin-bottom: 10px; font-family: monospace; }
.grand-total { background: #fff; border: 2px solid #003087; border-radius: 4px; padding: 8px 16px; display: inline-block; font-size: 16px; font-weight: 700; color: #003087; margin-top: 8px; }
.back { display: inline-block; margin-bottom: 10px; color: #003087; text-decoration: none; font-size: 12px; }
.back:hover { text-decoration: underline; }
.empty { text-align: center; padding: 30px; color: #888; }
</style>
</head>
<body>
<div class="topbar">
  <h1>Bookings Detail &mdash; <?php echo htmlspecialchars($slsName ?: 'Sls#'.$slsNum); ?> &mdash; <?php echo htmlspecialchars($pd['label']); ?></h1>
  <div class="meta"><?php echo htmlspecialchars($from); ?><?php echo $period !== 'day' ? ' through ' . $now->format('Y-m-d') : ''; ?> &nbsp;&bull;&nbsp; Source: SGHDSDATA/OEORHD, OEORDT</div>
</div>
<div class="content">
  <a class="back" href="javascript:window.close()">&#8592; Close Window</a>
  <?php if ($err): ?>
    <div class="err">Query error: <?php echo htmlspecialchars($err); ?></div>
  <?php endif; ?>
  <div class="summary-bar">
    <div class="kpi"><div class="val"><?php echo count($orders); ?></div><div class="lbl">Orders</div></div>
    <div class="kpi"><div class="val"><?php echo count($rows); ?></div><div class="lbl">Lines</div></div>
    <div class="kpi"><div class="val"><?php echo fmt($total); ?></div><div class="lbl">Total Booked</div></div>
  </div>
  <?php if (empty($orders)): ?>
    <div class="empty">No booked lines found for this salesperson and period.</div>
  <?php else: ?>
    <?php foreach ($orders as $ord): ?>
    <div class="order-block">
      <div class="order-header">
        <span class="ord-num">Order #<?php echo htmlspecialchars($ord['ordnum']); ?></span>
        <span class="cust"><?php echo htmlspecialchars($ord['custname']); ?></span>
        <span>Bk Date: <?php echo cymdToDate($ord['bdte']); ?></span>
        <span class="subtot"><?php echo fmt($ord['subtotal']); ?></span>
      </div>
      <table>
        <thead><tr>
          <th>Item</th><th>Description</th><th>Line Timestamp</th>
          <th>Qty Ord</th><th>Unit Price</th><th>Line Amt</th>
        </tr></thead>
        <tbody>
        <?php foreach ($ord['lines'] as $ln): $amt = (float)$ln['LINEAMT']; ?>
        <tr>
          <td><?php echo htmlspecialchars($ln['ITEM']); ?></td>
          <td><?php echo htmlspecialchars($ln['ITEMDESC']); ?></td>
          <td><?php echo htmlspecialchars($ln['ODOSTP']); ?></td>
          <td><?php echo number_format((float)$ln['QORD'], 0); ?></td>
          <td><?php echo '$'.number_format((float)$ln['SLPR'], 5); ?></td>
          <td class="<?php echo $amt < 0 ? 'neg' : ''; ?>"><?php echo fmt($amt); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endforeach; ?>
    <div class="grand-total">Grand Total: <?php echo fmt($total); ?></div>
  <?php endif; ?>
</div>
</body>
</html>
