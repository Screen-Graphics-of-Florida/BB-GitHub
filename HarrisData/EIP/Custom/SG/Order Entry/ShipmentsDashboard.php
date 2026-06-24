<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$now      = new DateTime();
$dow      = (int)$now->format('w');   // 0=Sun … 6=Sat (week starts Sunday)
$wkSt     = clone $now; $wkSt->modify('-' . $dow . ' days');
$moSt     = new DateTime($now->format('Y-m-01'));
$yrSt     = new DateTime($now->format('Y-01-01'));
$tomorrow = clone $now; $tomorrow->modify('+1 day');

// OESHDT (order header ship date) and DHDTLI (invoice date) are stored as CYMD integers.
function dateToCymd(DateTime $dt) {
    return ((int)$dt->format('Y') - 1900) * 10000 + (int)$dt->format('m') * 100 + (int)$dt->format('d');
}
$todayCymd    = dateToCymd($now);
$wkStCymd     = dateToCymd($wkSt);
$moStCymd     = dateToCymd($moSt);
$yrStCymd     = dateToCymd($yrSt);
$tomorrowCymd = dateToCymd($tomorrow);

$conn   = $i5Connect->getConnection();
$eiBase = 'https://portal.screen-graphics.com:5601';

// ─── QUERY 1: Orders shipped today ───────────────────────────────────────────
// Ship date anchor = OEORHD.OESHDT; amount = ODQSTC × ODSLPR (pre-invoice).
// Restrict to open detail lines (ODORST='O') with qty shipped > 0.
$sqlShip = "
    SELECT
        h.\"OEORD#\"                                                               AS ORDNUM,
        TRIM(CHAR(h.OESHTO))                                                       AS CUSTNUM,
        COALESCE(TRIM(c.CMCNA1), '')                                               AS CUSTNAME,
        TRIM(CHAR(h.OESLSM))                                                       AS SLSNUM,
        CASE WHEN s.SMREGN <> 'INACT' THEN TRIM(s.SMSNA1) ELSE 'Ex-Sales' END    AS SLSNAME,
        TRIM(CHAR(h.OEORST))                                                       AS ORST,
        TRIM(d.ODITEM)                                                             AS ITEM,
        TRIM(d.ODIMDS)                                                             AS ITEMDESC,
        d.ODQSTC                                                                   AS QSTC,
        d.ODSLPR                                                                   AS SLPR,
        d.ODQSTC * d.ODSLPR                                                        AS LINEAMT
    FROM SGHDSDATA.OEORHD h
    JOIN SGHDSDATA.OEORDT d  ON h.\"OEORD#\" = d.\"ODORD#\"
    LEFT JOIN SGHDSDATA.HDCUST c ON h.OESHTO = c.CMCUST
    LEFT JOIN SGHDSDATA.HDSLSM s ON h.OESLSM = s.SMSLSM
    WHERE h.OESHDT = $todayCymd
      AND d.ODORST = 'O'
      AND d.ODQSTC <> 0
    ORDER BY h.\"OEORD#\", d.ODITEM
";

$shipError = '';
$shipRows  = array();
$stmt = db2_exec($conn, $sqlShip, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $shipRows[] = $r;
    }
    db2_free_stmt($stmt);
} else {
    $shipError = db2_stmt_errormsg();
}

// Group shipped-today rows by order number; build salesperson totals
$shipOrders = array();
$shipBySls  = array();
$shipTotal  = 0.0;

foreach ($shipRows as $r) {
    $ord = (int)$r['ORDNUM'];
    if (!isset($shipOrders[$ord])) {
        $shipOrders[$ord] = array(
            'ordnum'   => $ord,
            'custnum'  => trim((string)$r['CUSTNUM']),
            'custname' => (string)$r['CUSTNAME'],
            'slsnum'   => (int)$r['SLSNUM'],
            'slsname'  => (string)$r['SLSNAME'],
            'orst'     => trim((string)$r['ORST']),
            'lines'    => array(),
            'subtotal' => 0.0,
        );
    }
    $shipOrders[$ord]['lines'][]  = array(
        'item'    => (string)$r['ITEM'],
        'desc'    => (string)$r['ITEMDESC'],
        'qstc'    => (int)$r['QSTC'],
        'slpr'    => round((float)$r['SLPR'], 2),
        'lineamt' => round((float)$r['LINEAMT'], 2),
    );
    $shipOrders[$ord]['subtotal'] += (float)$r['LINEAMT'];
    $shipTotal += (float)$r['LINEAMT'];

    $sn = (string)$r['SLSNAME'];
    if (!isset($shipBySls[$sn])) {
        $shipBySls[$sn] = array('name' => $sn, 'num' => (int)$r['SLSNUM'], 'amt' => 0.0);
    }
    $shipBySls[$sn]['amt'] += (float)$r['LINEAMT'];
}

function buildOrderUrl($eiBase, $eID, $ordnum, $orst, $custnum, $custname) {
    $base = ($orst === 'C')
        ? $eiBase . '/harris-CGI/SelectOrderHistory.d2w/REPORT'
        : $eiBase . '/harris-CGI/SelectOrder.d2w/REPORT';
    $url  = $base
        . '?baseVar=BaseConfiguration.icl'
        . '&portal=CUSTOMER'
        . '&eID='            . urlencode($eID)
        . '&customerName='   . urlencode($custname)
        . '&customerNumber=' . urlencode($custnum)
        . '&orderNumber='    . urlencode($ordnum);
    if ($orst === 'C') $url .= '&orderSequence=0';
    return $url;
}

$shipOrdersJs = array();
foreach ($shipOrders as $ord) {
    $o           = $ord;
    $o['url']    = buildOrderUrl($eiBase, $eID, $ord['ordnum'], $ord['orst'], $ord['custnum'], $ord['custname']);
    $o['subtotal'] = round($ord['subtotal'], 2);
    $shipOrdersJs[] = $o;
}

// ─── QUERY 2: Invoice totals by salesperson ───────────────────────────────────
// Uses the same formula as SalesDashboard: DHSLPR × DHQSTC ÷ DHORUF.
// Date anchor: DHDTLI (invoice date on OEORDH detail rows).
$sqlInv = "
    SELECT
        TRIM(CHAR(h.OESLSM))                                                                        AS SLSNUM,
        CASE WHEN h.OESLSM = s.SMSLSM AND s.SMREGN <> 'INACT' THEN TRIM(s.SMSNA1) ELSE 'Ex-Sales' END AS SLSNAME,
        SUM(CASE WHEN d.DHDTLI =  $todayCymd AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)                                    AS DAYAMT,
        SUM(CASE WHEN d.DHDTLI >= $wkStCymd  AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)                                    AS WEEKAMT,
        SUM(CASE WHEN d.DHDTLI >= $moStCymd  AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)                                    AS MONTHAMT,
        SUM(CASE WHEN d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)                                    AS YEARAMT
    FROM SGHDSDATA.OEORDH d
    JOIN SGHDSDATA.OEORHD h ON d.\"DHORD#\" = h.\"OEORD#\"
    LEFT JOIN SGHDSDATA.HDSLSM s ON h.OESLSM = s.SMSLSM
    WHERE d.\"DHSEQ#\" <> 0
      AND d.DHQSTC <> 0
      AND d.DHDTLI >= $yrStCymd
      AND d.DHDTLI <  $tomorrowCymd
    GROUP BY h.OESLSM, s.SMSLSM, s.SMREGN, s.SMSNA1
    ORDER BY s.SMSNA1, h.OESLSM
";

$invRows  = array();
$invError = '';
$stmt = db2_exec($conn, $sqlInv, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $invRows[] = array(
            'name'  => (string)$r['SLSNAME'],
            'num'   => (int)$r['SLSNUM'],
            'day'   => round((float)$r['DAYAMT'],   2),
            'week'  => round((float)$r['WEEKAMT'],  2),
            'month' => round((float)$r['MONTHAMT'], 2),
            'year'  => round((float)$r['YEARAMT'],  2),
        );
    }
    db2_free_stmt($stmt);
} else {
    $invError = db2_stmt_errormsg();
}

$invJson       = json_encode($invRows);
$shipBySlsJson = json_encode(array_values($shipBySls));
$shipOrdJson   = json_encode($shipOrdersJs);

$refreshedAt = $now->format('g:i:s A');
$asOfLabel   = 'As of: ' . $now->format('D, M j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shipments Dashboard</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Roboto+Condensed:wght@400;700&display=swap');

  :root {
    --hd-blue: #003087;
    --hd-nav-bg: #0046a8;
    --hd-nav-width: 180px;
    --hd-border: #d0d7e2;
    --hd-text: #1a2233;
    --hd-muted: #5a6478;
    --bg: #d4d0c8;
    --panel-bg: #ffffff;
    --panel-border: #999;
    --lcd-cyan: #00e5ff;
    --lcd-cyan-dim: #004455;
    --lcd-blue: #4488ff;
    --lcd-blue-dim: #001133;
    --lcd-orange: #ff9900;
    --lcd-orange-dim: #442200;
    --lcd-green: #44ff44;
    --lcd-green-dim: #004400;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Roboto Condensed', Arial, sans-serif; font-size: 13px; color: var(--hd-text); background: #edf1f7; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

  .topbar { background: var(--hd-blue); color: white; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; height: 42px; flex-shrink: 0; }
  .topbar-logo { font-size: 15px; font-weight: 700; letter-spacing: 0.5px; font-family: 'Roboto Condensed', sans-serif; }
  .topbar-logo span { color: #6db3ff; }
  .topbar-right { display: flex; align-items: center; gap: 20px; font-size: 12px; color: #b8cfee; }
  .topbar-right a { color: #b8cfee; text-decoration: none; }

  .layout { display: flex; flex: 1; overflow: hidden; }

  .nav { width: var(--hd-nav-width); background: var(--hd-nav-bg); flex-shrink: 0; overflow-y: auto; }
  .nav-section { padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
  .nav-item { display: block; padding: 6px 14px; color: #cde0ff; font-size: 12px; text-decoration: none; cursor: pointer; white-space: nowrap; }
  .nav-item:hover { background: rgba(255,255,255,0.12); color: white; }
  .nav-item.active { background: rgba(255,255,255,0.2); color: white; font-weight: 700; border-left: 3px solid #6db3ff; padding-left: 11px; }
  .nav-header { padding: 8px 14px 4px; color: #89afd4; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
  .nw-badge { display: inline-block; background: rgba(109,179,255,0.25); color: #9ecfff; font-size: 9px; padding: 1px 4px; border-radius: 2px; margin-left: 4px; }

  .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

  .page-header { border-bottom: 2px solid #888; padding: 6px 12px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
    background: linear-gradient(to bottom, #ece9d8, #d4d0c8); }
  .page-title { font-size: 14px; font-weight: 700; color: #003; font-family: 'Roboto Condensed', sans-serif; }
  .page-meta { font-size: 11px; color: var(--hd-muted); }
  .header-right { display: flex; align-items: center; gap: 10px; }

  .refresh-bar { background: #e8f0fb; border-bottom: 1px solid #bdd0ee; padding: 4px 14px; display: flex; align-items: center; gap: 14px; font-size: 11px; color: var(--hd-muted); flex-shrink: 0; }
  .refresh-dot { width: 8px; height: 8px; border-radius: 50%; background: #1a7a3c; animation: pulse 2s infinite; flex-shrink: 0; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
  .refresh-progress { flex: 1; max-width: 160px; height: 4px; background: #d0dced; border-radius: 2px; overflow: hidden; }
  .refresh-fill { height: 100%; background: #0055b3; border-radius: 2px; transition: width 1s linear; }
  .stat-pill { background: white; border: 1px solid var(--hd-border); border-radius: 12px; padding: 2px 10px; font-size: 11px; font-weight: 600; }
  .btn { font-size: 12px; padding: 3px 10px; border-radius: 3px; cursor: pointer; border: 1px solid; display: inline-flex; align-items: center; gap: 4px; background: white; color: var(--hd-text); border-color: var(--hd-border); }
  .btn:hover { background: #f0f4fa; }

  .dash-body { flex: 1; overflow-y: auto; padding: 10px 12px 12px;
    background: linear-gradient(160deg, #dbd8cc 0%, #c8c4bc 100%); }

  .panel { background: var(--panel-bg); border: 2px solid; border-color: #fff #888 #888 #fff; box-shadow: 1px 1px 0 #000; }
  .panel-title { background: #000080; color: white; font-size: 11px; font-weight: 700; padding: 2px 6px; font-family: 'Roboto Condensed', sans-serif; letter-spacing: 0.5px; }

  /* Shipped-today section: 2-column, detail left / LCD+chart right */
  .ship-grid { display: grid; grid-template-columns: 2fr 3fr; gap: 8px; margin-bottom: 8px; }

  /* Scrollable shipped-today detail table */
  .ship-detail-wrap { max-height: 288px; overflow-y: auto; }
  table.ship-tbl { width: 100%; border-collapse: collapse; font-size: 12px; font-family: 'Roboto Condensed', sans-serif; }
  table.ship-tbl thead th { background: #000080; color: white; padding: 4px 10px; font-size: 11px; font-weight: 700; border-right: 1px solid #3333aa; white-space: nowrap; text-align: left; }
  table.ship-tbl thead th:last-child { text-align: right; }
  table.ship-tbl tbody tr.ship-order-row { background: #f0f4ff; border-bottom: 1px solid #ccd; }
  table.ship-tbl tbody tr.ship-order-row:hover { background: #ddeeff; }
  table.ship-tbl tbody tr.ship-order-row td { padding: 4px 10px; font-weight: 600; }
  table.ship-tbl tbody tr.ship-line-row td { padding: 2px 10px 2px 20px; font-size: 11px; color: #555; border-bottom: 1px solid #eee; }
  table.ship-tbl tfoot td { background: #ddd; font-weight: 700; padding: 4px 10px; border-top: 2px solid #888; }
  .ord-link { color: #0033aa; text-decoration: none; font-weight: 700; }
  .ord-link:hover { text-decoration: underline; }

  /* Invoice totals grids */
  .top-grid    { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
  .bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }

  .lcd-panel { padding: 8px 12px 6px; }
  .lcd-display {
    background: #1a1a1a; border: 3px inset #666; border-radius: 4px;
    padding: 8px 12px; text-align: center; font-family: 'Share Tech Mono', monospace;
    letter-spacing: 2px; position: relative; overflow: hidden;
  }
  .lcd-display::before {
    content: ''; position: absolute; inset: 0;
    background: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(0,0,0,0.08) 3px, rgba(0,0,0,0.08) 4px);
    pointer-events: none;
  }
  .lcd-val   { font-size: 36px; font-weight: 400; line-height: 1; }
  .lcd-label { font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    font-family: 'Roboto Condensed', sans-serif; margin-top: 3px; }

  .lcd-cyan .lcd-display   { background: #001a22; box-shadow: 0 0 12px rgba(0,200,255,0.3) inset; }
  .lcd-cyan .lcd-val       { color: var(--lcd-cyan); text-shadow: 0 0 8px rgba(0,200,255,0.8); }
  .lcd-cyan .lcd-label     { color: #00aacc; }

  .lcd-blue .lcd-display   { background: #00051a; box-shadow: 0 0 12px rgba(50,100,255,0.3) inset; }
  .lcd-blue .lcd-val       { color: var(--lcd-blue); text-shadow: 0 0 8px rgba(50,120,255,0.8); }
  .lcd-blue .lcd-label     { color: #3366cc; }

  .lcd-orange .lcd-display { background: #1a0a00; box-shadow: 0 0 12px rgba(255,150,0,0.3) inset; }
  .lcd-orange .lcd-val     { color: var(--lcd-orange); text-shadow: 0 0 8px rgba(255,150,0,0.8); }
  .lcd-orange .lcd-label   { color: #cc7700; }

  .lcd-green .lcd-display  { background: #001400; box-shadow: 0 0 12px rgba(0,200,0,0.3) inset; }
  .lcd-green .lcd-val      { color: var(--lcd-green); text-shadow: 0 0 8px rgba(0,200,0,0.8); }
  .lcd-green .lcd-label    { color: #33aa33; }

  .chart-panel { padding: 6px 8px 8px; }
  .chart-wrap  { height: 180px; position: relative; }
  .quad-cell   { display: flex; flex-direction: column; }

  table.detail { width: 100%; border-collapse: collapse; font-size: 12px; font-family: 'Roboto Condensed', sans-serif; }
  table.detail thead th { background: #000080; color: white; padding: 4px 10px; text-align: right; font-size: 11px; font-weight: 700; border-right: 1px solid #3333aa; white-space: nowrap; }
  table.detail thead th:first-child { text-align: left; }
  table.detail thead th:nth-child(2) { text-align: center; }
  table.detail tbody tr { border-bottom: 1px solid #ddd; }
  table.detail tbody tr:nth-child(even) { background: #f0f0f0; }
  table.detail tbody tr:hover { background: #ddeeff; }
  table.detail tbody td { padding: 4px 10px; text-align: right; white-space: nowrap; color: #111; }
  table.detail tbody td.drillcell { padding: 0; text-align: right; }
  table.detail tbody td:first-child { text-align: left; font-weight: 600; }
  table.detail tbody td:nth-child(2) { text-align: center; color: #555; }
  table.detail tfoot td { background: #ddd; font-weight: 700; padding: 4px 10px; text-align: right; border-top: 2px solid #888; }
  table.detail tfoot td:first-child { text-align: left; }
  .money { font-variant-numeric: tabular-nums; }
  .zero  { color: #aaa; }
  td.drillcell { padding: 0 !important; text-align: right; }
  .drill-link {
    color: inherit; text-decoration: none; display: block;
    padding: 4px 10px; margin: -4px -10px;
    border-radius: 2px; transition: background 0.15s; font-variant-numeric: tabular-nums;
  }
  .drill-link:hover { background: #bbdaff; text-decoration: underline; cursor: pointer; }

  .page-footer { border-top: 1px solid #999; padding: 3px 14px; display: flex; align-items: center;
    justify-content: space-between; font-size: 10px; color: #555; flex-shrink: 0;
    background: linear-gradient(to bottom, #d4d0c8, #c8c4bc); }
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-logo">Harris<span>Data</span> EIP</div>
  <div class="topbar-right">
    <a href="#">My Portal</a>
    <a href="#">Help</a>
    <span>sgadmin</span>
    <a href="#">Logout</a>
  </div>
</div>

<div class="layout">
  <nav class="nav">
    <div class="nav-section">
      <div class="nav-header">My Portal</div>
      <a class="nav-item" href="#">Event Calendar</a>
    </div>
    <div class="nav-section">
      <div class="nav-header">Modules</div>
      <a class="nav-item" href="#">Customer</a>
      <a class="nav-item" href="#">Manufacturing</a>
      <a class="nav-item" href="#">Receivables</a>
      <a class="nav-item" href="#">Reports</a>
    </div>
    <div class="nav-section">
      <div class="nav-header">SG Dashboards</div>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/BookingsDashboard.php">Bookings Dashboard <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item active" href="#">Shipments Dashboard <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/SalesDashboard.php">Sales Dashboard <span class="nw-badge">&#8599;</span></a>
    </div>
  </nav>

  <main class="main">
    <div class="page-header">
      <div>
        <div class="page-title">Shipments Dashboard</div>
        <div class="page-meta">SG Dashboards &rsaquo; Order Entry &nbsp;&bull;&nbsp; Opens in new window &nbsp;&bull;&nbsp; All groups: View</div>
      </div>
      <div class="header-right">
        <button class="btn" onclick="exportCSV()">&#8595; Export</button>
        <button class="btn" onclick="triggerRefresh()">&#8635; Refresh Now</button>
      </div>
    </div>

    <div class="refresh-bar">
      <div class="refresh-dot" id="rDot"></div>
      <span id="rStatus">Live &mdash; refreshes every 15 minutes</span>
      <div class="refresh-progress"><div class="refresh-fill" id="rFill" style="width:100%"></div></div>
      <span>Next refresh in: <strong id="rCount">15m 0s</strong></span>
      <span class="stat-pill" id="rTime">Last refresh: just now</span>
      <span class="stat-pill" style="background:#fff0d0;border-color:#f0c060;color:#885500;" id="asOfDate"></span>
    </div>

    <?php if ($shipError): ?>
    <div style="background:#5a1010;color:#ffaaaa;padding:7px 14px;font-size:12px;font-family:monospace;">
      Ship query error: <?php echo htmlspecialchars($shipError); ?>
    </div>
    <?php endif; ?>
    <?php if ($invError): ?>
    <div style="background:#5a1010;color:#ffaaaa;padding:7px 14px;font-size:12px;font-family:monospace;">
      Invoice query error: <?php echo htmlspecialchars($invError); ?>
    </div>
    <?php endif; ?>

    <div class="dash-body">

      <!-- ── TOP: Shipped Today detail (left) + Shipped $ LCD + chart (right) ── -->
      <div class="ship-grid">

        <!-- Shipped Today detail table -->
        <div class="panel" style="display:flex;flex-direction:column;">
          <div class="panel-title">&#9632; ORDERS SHIPPED TODAY</div>
          <div style="background:#fff8e0;border-bottom:1px solid #ddcc88;padding:3px 8px;font-size:10px;color:#775500;flex-shrink:0;">
            &#9888;&nbsp;Shipped dollars valid until invoicing
          </div>
          <div class="ship-detail-wrap">
            <table class="ship-tbl">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Salesperson</th>
                  <th style="text-align:right">Shipped $</th>
                </tr>
              </thead>
              <tbody id="shipBody"></tbody>
              <tfoot id="shipFoot"></tfoot>
            </table>
          </div>
        </div>

        <!-- Shipped Today LCD + chart -->
        <div class="quad-cell">
          <div class="panel lcd-cyan" style="margin-bottom:6px;">
            <div class="panel-title">&#9632; SHIPPED TODAY &mdash; VALID UNTIL INVOICED</div>
            <div class="lcd-panel">
              <div class="lcd-display">
                <div class="lcd-val" id="lcd-shipped">$0.00</div>
              </div>
              <div class="lcd-label" style="color:#00aacc;text-align:center;margin-top:4px;">TOTAL SHIPPED TODAY (PRE-INVOICE)</div>
            </div>
          </div>
          <div class="panel">
            <div class="panel-title">Shipped Today by Salesperson</div>
            <div class="chart-panel">
              <div class="chart-wrap"><canvas id="chartShipped"></canvas></div>
            </div>
          </div>
        </div>

      </div>

      <!-- ── INVOICE TOTALS — Day + Week ── -->
      <div class="top-grid">

        <!-- DAY INVOICED -->
        <div class="quad-cell">
          <div class="panel lcd-cyan">
            <div class="panel-title">&#9632; TODAY&rsquo;S INVOICED</div>
            <div class="lcd-panel">
              <div class="lcd-display">
                <div class="lcd-val" id="lcd-day">$0.00</div>
              </div>
              <div class="lcd-label" style="color:#00aacc;text-align:center;margin-top:4px;">DAY INVOICE TOTAL</div>
            </div>
          </div>
          <div style="height:6px"></div>
          <div class="panel">
            <div class="panel-title">Invoiced Today by Sales Person</div>
            <div class="chart-panel">
              <div class="chart-wrap"><canvas id="chartDay"></canvas></div>
            </div>
          </div>
        </div>

        <!-- WEEK INVOICED -->
        <div class="quad-cell">
          <div class="panel lcd-blue">
            <div class="panel-title">&#9632; WEEK-TO-DATE INVOICED</div>
            <div class="lcd-panel">
              <div class="lcd-display">
                <div class="lcd-val" id="lcd-week">$0.00</div>
              </div>
              <div class="lcd-label" style="color:#3366cc;text-align:center;margin-top:4px;">WEEK INVOICE TOTAL</div>
            </div>
          </div>
          <div style="height:6px"></div>
          <div class="panel">
            <div class="panel-title">Invoiced This Week by Sales Person</div>
            <div class="chart-panel">
              <div class="chart-wrap"><canvas id="chartWeek"></canvas></div>
            </div>
          </div>
        </div>

      </div>

      <!-- ── INVOICE TOTALS — Month + Year ── -->
      <div class="bottom-grid">

        <!-- MONTH INVOICED -->
        <div class="quad-cell">
          <div class="panel lcd-orange">
            <div class="panel-title">&#9632; MONTH-TO-DATE INVOICED</div>
            <div class="lcd-panel">
              <div class="lcd-display">
                <div class="lcd-val" id="lcd-month">$0.00</div>
              </div>
              <div class="lcd-label" style="color:#cc7700;text-align:center;margin-top:4px;">MONTH INVOICE TOTAL</div>
            </div>
          </div>
          <div style="height:6px"></div>
          <div class="panel">
            <div class="panel-title">Invoiced This Month by Sales Person</div>
            <div class="chart-panel">
              <div class="chart-wrap"><canvas id="chartMonth"></canvas></div>
            </div>
          </div>
        </div>

        <!-- YEAR INVOICED -->
        <div class="quad-cell">
          <div class="panel lcd-green">
            <div class="panel-title">&#9632; YEAR-TO-DATE INVOICED</div>
            <div class="lcd-panel">
              <div class="lcd-display">
                <div class="lcd-val" id="lcd-year">$0.00</div>
              </div>
              <div class="lcd-label" style="color:#33aa33;text-align:center;margin-top:4px;">YEAR INVOICE TOTAL</div>
            </div>
          </div>
          <div style="height:6px"></div>
          <div class="panel">
            <div class="panel-title">Invoiced This Year by Sales Person</div>
            <div class="chart-panel">
              <div class="chart-wrap"><canvas id="chartYear"></canvas></div>
            </div>
          </div>
        </div>

      </div>

      <!-- ── Invoice Summary Detail Table ── -->
      <div class="panel">
        <div class="panel-title">&#9632; INVOICE SUMMARY DETAIL</div>
        <div style="overflow-x:auto;padding:6px 8px 8px;">
          <table class="detail" id="detailTable">
            <thead>
              <tr>
                <th style="text-align:left">Salesperson</th>
                <th style="text-align:center">Sls#</th>
                <th>Day Invoiced</th>
                <th>Week Invoiced</th>
                <th>Month Invoiced</th>
                <th>Year Invoiced</th>
              </tr>
            </thead>
            <tbody id="detailBody"></tbody>
            <tfoot id="detailFoot"></tfoot>
          </table>
        </div>
      </div>

    </div><!-- /dash-body -->

    <div class="page-footer">
      <span>192.168.120.40 / SEQUELPROD / SGSHIP &nbsp;&mdash;&nbsp; Source: SGHDSDATA/OEORHD, OEORDT, OEORDH, HDCUST, HDSLSM</span>
      <span id="footerTime"></span>
    </div>
  </main>
</div>

<script>
// ─── SALESPERSON COLORS (shared with Bookings/Sales) ─────────────────────────
const SLS_COLORS = {
  'Adriane Medley':     '#8B0000',
  'Alexandra Davis':    '#FF8C00',
  'Dayna Browne':       '#AACC00',
  'Jude Close':         '#1a7a3c',
  'Kelly Hart':         '#00AAAA',
  'Lari Ann Searles':   '#7B2FBE',
  'SG General Customer':'#1155CC',
  'SG House Sanitation':'#66CC44',
  'Tiffany Reichman':   '#CCAA00',
};

// ─── DATA FROM SERVER ─────────────────────────────────────────────────────────
const SHIP_ORDERS = <?php echo $shipOrdJson; ?>;
const SHIP_SLS    = <?php echo $shipBySlsJson; ?>;
const SHIP_TOTAL  = <?php echo json_encode(round($shipTotal, 2)); ?>;
const DATA        = <?php echo $invJson; ?>;   // invoice totals by salesperson

// ─── FORMATTING ───────────────────────────────────────────────────────────────
function fmtLCD(n) {
  return '$' + n.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}
function escHtml(s) {
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

const PERIOD_LABELS = { day:'Today', week:'This Week', month:'This Month', year:'This Year' };
const DRILLDOWN_BASE = '<?php echo "https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/SalesDrilldown.php"; ?>';

function drillDown(slsNum, slsName, period) {
  const url = DRILLDOWN_BASE
    + '?sls='    + encodeURIComponent(slsNum)
    + '&period=' + encodeURIComponent(period)
    + '&name='   + encodeURIComponent(slsName);
  window.open(url, '_blank', 'width=1300,height=750,scrollbars=yes,resizable=yes');
}

function fmtMoney(n, slsNum, slsName, period) {
  const dollars = '$' + n.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
  if (n === 0) return '<span class="zero">$0.00</span>';
  if (!slsNum || !period) return dollars;
  const label = PERIOD_LABELS[period] || period;
  return '<a class="drill-link" href="#" onclick="event.preventDefault();drillDown(' +
    slsNum + ',\'' + slsName.replace(/'/g,"\\'") + '\',\'' + period + '\')" ' +
    'title="' + label + ' invoiced for ' + escHtml(slsName) + '">' + dollars + '</a>';
}

// ─── SHIPPED TODAY TABLE ──────────────────────────────────────────────────────
function buildShipToday() {
  document.getElementById('lcd-shipped').textContent = fmtLCD(SHIP_TOTAL);

  const tbody = document.getElementById('shipBody');
  tbody.innerHTML = '';

  if (!SHIP_ORDERS.length) {
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#aaa;padding:14px;">No shipments found for today</td></tr>';
    document.getElementById('shipFoot').innerHTML = '';
    return;
  }

  SHIP_ORDERS.forEach(function(o) {
    // Order header row
    const tr = document.createElement('tr');
    tr.className = 'ship-order-row';
    tr.innerHTML =
      '<td><a class="ord-link" href="' + escHtml(o.url) + '" target="_blank">' + o.ordnum + '</a></td>' +
      '<td>' + escHtml(o.custname) + '</td>' +
      '<td>' + escHtml(o.slsname) + '</td>' +
      '<td style="text-align:right;font-variant-numeric:tabular-nums">' + fmtLCD(o.subtotal) + '</td>';
    tbody.appendChild(tr);

    // Item detail rows
    o.lines.forEach(function(l) {
      const tl = document.createElement('tr');
      tl.className = 'ship-line-row';
      tl.innerHTML =
        '<td>' + escHtml(l.item) + '</td>' +
        '<td colspan="2">' + escHtml(l.desc) + '</td>' +
        '<td style="text-align:right;font-variant-numeric:tabular-nums">' +
          l.qstc + '&times;' + fmtLCD(l.slpr) + '&nbsp;=&nbsp;' + fmtLCD(l.lineamt) +
        '</td>';
      tbody.appendChild(tl);
    });
  });

  document.getElementById('shipFoot').innerHTML =
    '<tr>' +
    '<td colspan="3" style="text-align:left">TOTAL SHIPPED TODAY</td>' +
    '<td style="text-align:right">' + fmtLCD(SHIP_TOTAL) + '</td>' +
    '</tr>';
}

// ─── INVOICE LCD TOTALS ───────────────────────────────────────────────────────
function updateTotals() {
  const t = DATA.reduce(function(a,r) {
    return { day:a.day+r.day, week:a.week+r.week, month:a.month+r.month, year:a.year+r.year };
  }, {day:0,week:0,month:0,year:0});
  document.getElementById('lcd-day').textContent   = fmtLCD(t.day);
  document.getElementById('lcd-week').textContent  = fmtLCD(t.week);
  document.getElementById('lcd-month').textContent = fmtLCD(t.month);
  document.getElementById('lcd-year').textContent  = fmtLCD(t.year);
  return t;
}

// ─── CHART BUILDER ────────────────────────────────────────────────────────────
var charts = {};

function makeBarChart(id, dataArr, nameKey, valKey) {
  var labels = dataArr.map(function(r){ return r[nameKey].split(' ')[0]; });
  var values = dataArr.map(function(r){ return r[valKey]; });
  var colors = dataArr.map(function(r){ return SLS_COLORS[r[nameKey]] || '#999'; });

  if (charts[id]) charts[id].destroy();

  charts[id] = new Chart(document.getElementById(id), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{ data: values, backgroundColor: colors, borderColor: colors, borderWidth: 1, barPercentage: 0.75 }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'right',
          labels: {
            font: { size: 9, family: 'Roboto Condensed' },
            boxWidth: 10, padding: 4,
            generateLabels: function() {
              return dataArr.map(function(r) {
                return { text: r[nameKey], fillStyle: SLS_COLORS[r[nameKey]] || '#999',
                  strokeStyle: SLS_COLORS[r[nameKey]] || '#999', lineWidth: 1, hidden: false };
              });
            }
          }
        },
        tooltip: {
          callbacks: {
            label: function(ctx) { return ' ' + fmtLCD(ctx.raw); },
            title: function(ctx) { return dataArr[ctx[0].dataIndex][nameKey]; }
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 9, family: 'Roboto Condensed' }, maxRotation: 0 } },
        y: {
          grid: { color: '#e0e0e0' },
          ticks: {
            font: { size: 9, family: 'Roboto Condensed' },
            callback: function(v) {
              if (v >= 1000000) return '$' + (v/1000000).toFixed(1) + 'M';
              if (v >= 1000)    return '$' + (v/1000).toFixed(0) + 'K';
              return '$' + v;
            }
          }
        }
      }
    }
  });
}

function buildCharts() {
  makeBarChart('chartShipped', SHIP_SLS, 'name', 'amt');
  makeBarChart('chartDay',   DATA, 'name', 'day');
  makeBarChart('chartWeek',  DATA, 'name', 'week');
  makeBarChart('chartMonth', DATA, 'name', 'month');
  makeBarChart('chartYear',  DATA, 'name', 'year');
}

// ─── INVOICE DETAIL TABLE ─────────────────────────────────────────────────────
function buildTable() {
  const tbody = document.getElementById('detailBody');
  tbody.innerHTML = '';
  DATA.forEach(function(r) {
    const tr = document.createElement('tr');
    tr.innerHTML =
      '<td style="display:flex;align-items:center;gap:6px;">' +
        '<span style="display:inline-block;width:10px;height:10px;background:' + (SLS_COLORS[r.name]||'#999') + ';flex-shrink:0;"></span>' +
        escHtml(r.name) +
      '</td>' +
      '<td style="text-align:center">' + r.num + '</td>' +
      '<td class="drillcell">' + fmtMoney(r.day,   r.num, r.name, 'day')   + '</td>' +
      '<td class="drillcell">' + fmtMoney(r.week,  r.num, r.name, 'week')  + '</td>' +
      '<td class="drillcell">' + fmtMoney(r.month, r.num, r.name, 'month') + '</td>' +
      '<td class="drillcell">' + fmtMoney(r.year,  r.num, r.name, 'year')  + '</td>';
    tbody.appendChild(tr);
  });
  const t = DATA.reduce(function(a,r){ return {day:a.day+r.day,week:a.week+r.week,month:a.month+r.month,year:a.year+r.year}; }, {day:0,week:0,month:0,year:0});
  document.getElementById('detailFoot').innerHTML =
    '<tr>' +
    '<td>TOTAL</td><td></td>' +
    '<td>' + fmtMoney(t.day)   + '</td>' +
    '<td>' + fmtMoney(t.week)  + '</td>' +
    '<td>' + fmtMoney(t.month) + '</td>' +
    '<td>' + fmtMoney(t.year)  + '</td>' +
    '</tr>';
}

// ─── AUTO-REFRESH (M–F 7:00am–6:00pm ET, every 15 min) ───────────────────────
const AUTO_SECS = 900;
var countdown = AUTO_SECS;

function getEasternTime() {
  const now = new Date();
  return new Date(now.toLocaleString('en-US', { timeZone: 'America/New_York' }));
}

function isAutoRefreshTime() {
  const et = getEasternTime();
  const day  = et.getDay();
  const hour = et.getHours();
  return (day >= 1 && day <= 5) && (hour >= 7) && (hour < 18);
}

function fmtCountdown(secs) {
  const m = Math.floor(secs / 60);
  const s = String(secs % 60).padStart(2, '0');
  return m + 'm ' + s + 's';
}

function updateStatusBar() {
  const auto    = isAutoRefreshTime();
  const dotEl   = document.getElementById('rDot');
  const statEl  = document.getElementById('rStatus');
  const fillEl  = document.getElementById('rFill');
  const countEl = document.getElementById('rCount');

  if (auto) {
    dotEl.style.background = '#1a7a3c';
    dotEl.style.animation  = 'pulse 2s infinite';
    statEl.textContent     = 'Live — auto-refreshes every 15 min (M–F, 7:00am–6:00pm ET)';
    fillEl.style.width     = ((countdown / AUTO_SECS) * 100) + '%';
    countEl.textContent    = fmtCountdown(countdown);
  } else {
    dotEl.style.background = '#888888';
    dotEl.style.animation  = 'none';
    statEl.textContent     = 'Auto-refresh paused — outside M–F 7:00am–6:00pm ET. Use Refresh Now.';
    fillEl.style.width     = '0%';
    countEl.textContent    = '—';
    countdown = AUTO_SECS;
  }
}

function triggerRefresh() {
  document.getElementById('rDot').style.background    = '#f39c12';
  document.getElementById('rStatus').textContent      = 'Refreshing...';
  setTimeout(function() { location.reload(); }, 300);
}

setInterval(function() {
  if (isAutoRefreshTime()) {
    countdown--;
    if (countdown <= 0) { countdown = AUTO_SECS; triggerRefresh(); return; }
  }
  updateStatusBar();
}, 1000);

// ─── CSV EXPORT (invoice totals) ──────────────────────────────────────────────
function exportCSV() {
  const rows = [['Salesperson','Sls#','Day Invoiced','Week Invoiced','Month Invoiced','Year Invoiced']];
  DATA.forEach(function(r){ rows.push([r.name, r.num, r.day, r.week, r.month, r.year]); });
  const t = DATA.reduce(function(a,r){ return {day:a.day+r.day,week:a.week+r.week,month:a.month+r.month,year:a.year+r.year}; }, {day:0,week:0,month:0,year:0});
  rows.push(['TOTAL','',t.day,t.week,t.month,t.year]);
  const csv = rows.map(function(r){ return r.map(function(v){ return '"'+v+'"'; }).join(','); }).join('\n');
  const a = document.createElement('a');
  a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
  a.download = 'Shipments_' + new Date().toISOString().slice(0,10) + '.csv';
  a.click();
}

// ─── INIT ─────────────────────────────────────────────────────────────────────
document.getElementById('rTime').textContent      = 'Last refresh: <?php echo $refreshedAt; ?>';
document.getElementById('footerTime').textContent = '<?php echo $now->format('m/d/Y') . '  ' . $refreshedAt; ?>';
document.getElementById('asOfDate').textContent   = '<?php echo $asOfLabel; ?>';

buildShipToday();
updateTotals();
buildTable();
buildCharts();
</script>
</body>
</html>
