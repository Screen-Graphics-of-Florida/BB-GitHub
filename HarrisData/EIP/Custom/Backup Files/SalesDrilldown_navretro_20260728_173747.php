<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
date_default_timezone_set('America/Chicago');

$slsNum  = isset($_GET['sls'])    ? (int)$_GET['sls']       : 0;
$period  = isset($_GET['period']) ? $_GET['period']          : 'day';
$slsName = isset($_GET['name'])   ? trim($_GET['name'])      : '';

$now  = new DateTime();
$dow  = (int)$now->format('w');
$wkSt = clone $now; $wkSt->modify('-' . $dow . ' days');
$moSt = new DateTime($now->format('Y-m-01'));
$yrSt = new DateTime($now->format('Y-01-01'));
$next = clone $now; $next->modify('+1 day');

$periodDates = array(
    'day'   => array('from' => $now->format('Y-m-d'),  'to' => $next->format('Y-m-d'), 'label' => 'Today'),
    'week'  => array('from' => $wkSt->format('Y-m-d'), 'to' => $next->format('Y-m-d'), 'label' => 'This Week'),
    'month' => array('from' => $moSt->format('Y-m-d'), 'to' => $next->format('Y-m-d'), 'label' => 'This Month'),
    'year'  => array('from' => $yrSt->format('Y-m-d'), 'to' => $next->format('Y-m-d'), 'label' => 'This Year'),
);

if (!isset($periodDates[$period])) $period = 'day';
$pd   = $periodDates[$period];
$from = $pd['from'];

function dateToCymd(DateTime $dt) {
    return ((int)$dt->format('Y') - 1900) * 10000 + (int)$dt->format('m') * 100 + (int)$dt->format('d');
}
$periodCymds = array(
    'day'   => array('from' => dateToCymd($now),  'to' => dateToCymd($next)),
    'week'  => array('from' => dateToCymd($wkSt), 'to' => dateToCymd($next)),
    'month' => array('from' => dateToCymd($moSt), 'to' => dateToCymd($next)),
    'year'  => array('from' => dateToCymd($yrSt), 'to' => dateToCymd($next)),
);
$fromCymd = $periodCymds[$period]['from'];
$toCymd   = $periodCymds[$period]['to'];

$conn = $i5Connect->getConnection();

$sql = "
    SELECT
        d.\"DHORD#\"                                                                        AS ORDNUM,
        d.DHDTLI                                                                            AS INVDTE,
        COALESCE(TRIM(c.CMCNA1), '')                                                        AS CUSTNAME,
        TRIM(d.DHITEM)                                                                      AS ITEM,
        TRIM(d.DHIMDS)                                                                      AS ITEMDESC,
        d.DHQSTC                                                                            AS QSTC,
        d.DHSLPR                                                                            AS SLPR,
        d.DHORUF                                                                            AS ORUF,
        CASE WHEN d.DHORUF <> 0 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END            AS LINEAMT,
        CASE WHEN h.OESLSM = s.SMSLSM AND s.SMREGN <> 'INACT' THEN TRIM(s.SMSNA1) ELSE 'Ex-Sales' END AS SLSNAME,
        h.\"OELIV#\"                                                                        AS INVNUM,
        h.OEBLTO                                                                            AS CUSTNUM
    FROM SGHDSDATA.OEORDH d
    JOIN SGHDSDATA.OEORHD h ON d.\"DHORD#\" = h.\"OEORD#\"
    LEFT JOIN SGHDSDATA.HDCUST c ON h.OESHTO = c.CMCUST
    LEFT JOIN SGHDSDATA.HDSLSM s ON h.OESLSM = s.SMSLSM
    WHERE h.OESLSM = $slsNum
      AND d.\"DHSEQ#\" <> 0
      AND d.DHQSTC <> 0
      AND d.DHDTLI >= $fromCymd
      AND d.DHDTLI <  $toCymd
    ORDER BY d.\"DHORD#\", d.\"DHSEQ#\"
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

// Group by order number, track latest invoice date per order
$orders = array();
foreach ($rows as $r) {
    $ord = $r['ORDNUM'];
    if (!isset($orders[$ord])) {
        $orders[$ord] = array(
            'ordnum'   => $ord,
            'invdte'   => $r['INVDTE'],
            'custname' => $r['CUSTNAME'],
            'invnum'   => (int)$r['INVNUM'],
            'custnum'  => (int)$r['CUSTNUM'],
            'lines'    => array(),
            'subtotal' => 0.0,
        );
    }
    if ($r['INVDTE'] > $orders[$ord]['invdte']) {
        $orders[$ord]['invdte'] = $r['INVDTE'];
    }
    $orders[$ord]['lines'][]  = $r;
    $orders[$ord]['subtotal'] += (float)$r['LINEAMT'];
}

// Sort: newest invoice date first, then newest order number
uasort($orders, function($a, $b) {
    if ($b['invdte'] !== $a['invdte']) return $b['invdte'] - $a['invdte'];
    return strcmp($b['ordnum'], $a['ordnum']);
});

// Build by-date summary (shown for week/month/year only)
$byDate = array();
foreach ($orders as $ord) {
    $key = $ord['invdte'];
    if (!isset($byDate[$key])) $byDate[$key] = array('invdte' => $key, 'orders' => 0, 'total' => 0.0);
    $byDate[$key]['orders']++;
    $byDate[$key]['total'] += $ord['subtotal'];
}
krsort($byDate); // descending by CYMD integer = newest first

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
<title>Sales Detail &mdash; <?php echo htmlspecialchars($slsName); ?> &mdash; <?php echo $pd['label']; ?></title>
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
.order-header .inv-link { color: #6db3ff; text-decoration: underline; font-weight: 400; }
.order-header .inv-link:hover { color: #fff; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
th { background: #1a3a6b; color: #c8d8f0; padding: 4px 10px; text-align: right; font-size: 11px; font-weight: 700; white-space: nowrap; }
th:first-child, th:nth-child(2) { text-align: left; }
td { padding: 4px 10px; text-align: right; border-bottom: 1px solid #eef; white-space: nowrap; color: #222; }
td:first-child, td:nth-child(2) { text-align: left; }
tr:nth-child(even) td { background: #f7f8fc; }
.neg { color: #b00; }
.err { background: #fdd; color: #900; padding: 8px 12px; border-radius: 4px; margin-bottom: 10px; font-family: monospace; }
.grand-total { background: #fff; border: 2px solid #003087; border-radius: 4px; padding: 8px 16px; display: inline-block; font-size: 16px; font-weight: 700; color: #003087; margin-top: 8px; }
.back { display: inline-block; margin-bottom: 10px; color: #003087; text-decoration: none; font-size: 12px; }
.back:hover { text-decoration: underline; }
.empty { text-align: center; padding: 30px; color: #888; }
.date-summary { background: #fff; border: 1px solid #c8d0de; border-radius: 4px; margin-bottom: 12px; overflow: hidden; }
.date-summary-hdr { background: #1a3a6b; color: #c8d8f0; padding: 5px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
.date-summary table { font-size: 12px; }
.date-summary th { background: #e8edf5; color: #1a2233; padding: 4px 12px; font-size: 11px; font-weight: 700; }
.date-summary th:first-child { text-align: left; }
.date-summary td { padding: 4px 12px; border-bottom: 1px solid #eef; color: #222; }
.date-summary td:first-child { text-align: left; }
.date-summary tfoot td { background: #dde3ef; font-weight: 700; border-top: 2px solid #1a3a6b; }
</style>
</head>
<body>
<div class="topbar">
  <h1>Sales Detail &mdash; <?php echo htmlspecialchars($slsName ?: 'Sls#'.$slsNum); ?> &mdash; <?php echo htmlspecialchars($pd['label']); ?></h1>
  <div class="meta"><?php echo htmlspecialchars($from); ?><?php echo $period !== 'day' ? ' through ' . $now->format('Y-m-d') : ''; ?> &nbsp;&bull;&nbsp; Source: SGHDSDATA/OEORDH</div>
</div>
<div class="content">
  <a class="back" href="javascript:window.close()">&#8592; Close Window</a>
  <?php if ($err): ?>
    <div class="err">Query error: <?php echo htmlspecialchars($err); ?></div>
  <?php endif; ?>
  <div class="summary-bar">
    <div class="kpi"><div class="val"><?php echo count($orders); ?></div><div class="lbl">Orders</div></div>
    <div class="kpi"><div class="val"><?php echo count($rows); ?></div><div class="lbl">Lines</div></div>
    <div class="kpi"><div class="val"><?php echo fmt($total); ?></div><div class="lbl">Total Sales</div></div>
  </div>
  <?php if ($period !== 'day' && !empty($byDate)): ?>
  <div class="date-summary">
    <div class="date-summary-hdr">Total by Invoice Date</div>
    <table>
      <thead><tr>
        <th>Invoice Date</th><th style="text-align:right">Orders</th><th style="text-align:right">Total Sales</th>
      </tr></thead>
      <tbody>
      <?php foreach ($byDate as $d): ?>
      <tr>
        <td><?php echo cymdToDate($d['invdte']); ?></td>
        <td style="text-align:right"><?php echo $d['orders']; ?></td>
        <td style="text-align:right"><?php echo fmt($d['total']); ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot><tr>
        <td>Total</td>
        <td style="text-align:right"><?php echo count($orders); ?></td>
        <td style="text-align:right"><?php echo fmt($total); ?></td>
      </tr></tfoot>
    </table>
  </div>
  <?php endif; ?>
  <?php if (empty($orders)): ?>
    <div class="empty">No invoiced lines found for this salesperson and period.</div>
  <?php else: ?>
    <?php foreach ($orders as $ord): ?>
    <div class="order-block">
      <div class="order-header">
        <span class="ord-num">Order #<?php echo htmlspecialchars($ord['ordnum']); ?></span>
        <span class="cust"><?php echo htmlspecialchars($ord['custname']); ?></span>
        <span>Invoiced Date: <?php echo cymdToDate($ord['invdte']); ?></span>
        <?php if ($ord['invnum'] > 0):
            $invUrl = 'https://portal.screen-graphics.com:5601/harris-CGI/SelectInvoice.d2w/DISPLAY'
                . '?formatToPrint=Y&baseVar=BaseConfiguration.icl&portal=CUSTOMER'
                . '&eID=' . urlencode($eID)
                . '&customerNumber=' . $ord['custnum']
                . '&invoiceDate=' . $ord['invdte']
                . '&invoiceNumber=' . $ord['invnum'];
        ?>
        <span>Invoice: <a class="inv-link" href="<?php echo htmlspecialchars($invUrl); ?>" target="_blank"><?php echo $ord['invnum']; ?></a></span>
        <?php endif; ?>
        <span class="subtot"><?php echo fmt($ord['subtotal']); ?></span>
      </div>
      <table>
        <thead><tr>
          <th>Item</th><th>Description</th><th>Qty Shipped</th><th>Unit Price</th><th>Line Amt</th>
        </tr></thead>
        <tbody>
        <?php foreach ($ord['lines'] as $ln): $amt = (float)$ln['LINEAMT']; ?>
        <tr>
          <td><?php echo htmlspecialchars($ln['ITEM']); ?></td>
          <td><?php echo htmlspecialchars($ln['ITEMDESC']); ?></td>
          <td><?php echo number_format((float)$ln['QSTC'], 0); ?></td>
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
