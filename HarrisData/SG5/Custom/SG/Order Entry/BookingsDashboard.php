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

// OEBDTE (CYMD on order header) is the booking date anchor.
// All comparisons stay as integer CYMD — no SQL date conversion needed.
function dateToCymd(DateTime $dt) {
    return ((int)$dt->format('Y') - 1900) * 10000 + (int)$dt->format('m') * 100 + (int)$dt->format('d');
}
$todayIso     = $now->format('Y-m-d');
$todayCymd    = dateToCymd($now);
$wkStCymd     = dateToCymd($wkSt);
$moStCymd     = dateToCymd($moSt);
$yrStCymd     = dateToCymd($yrSt);
$tomorrowCymd = dateToCymd($tomorrow);

$conn = $i5Connect->getConnection();

$sql = "
    SELECT
        TRIM(CHAR(h.OESLSM))                                                             AS SLSNUM,
        CASE WHEN s.SMREGN <> 'INACT' THEN TRIM(s.SMSNA1) ELSE 'Ex-Sales' END          AS SLSNAME,
        SUM(CASE WHEN h.OEBDTE =  $todayCymd THEN d.ODQORD * d.ODSLPR ELSE 0 END)      AS DAYAMT,
        SUM(CASE WHEN h.OEBDTE >= $wkStCymd  THEN d.ODQORD * d.ODSLPR ELSE 0 END)      AS WEEKAMT,
        SUM(CASE WHEN h.OEBDTE >= $moStCymd  THEN d.ODQORD * d.ODSLPR ELSE 0 END)      AS MONTHAMT,
        SUM(d.ODQORD * d.ODSLPR)                                                          AS YEARAMT
    FROM SGHDSDATA.OEORHD h
    JOIN SGHDSDATA.OEORDT d ON h.\"OEORD#\" = d.\"ODORD#\"
    LEFT JOIN SGHDSDATA.HDSLSM s ON h.OESLSM = s.SMSLSM
    WHERE h.OEORTY NOT IN ('Q', 'U')
      AND h.OEBDTE >= $yrStCymd
      AND h.OEBDTE <  $tomorrowCymd
    GROUP BY h.OESLSM, s.SMREGN, s.SMSNA1
    ORDER BY s.SMSNA1, h.OESLSM
";

$dbRows  = array();
$dbError = '';
$stmt    = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $dbRows[] = array(
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
    $dbError = db2_stmt_errormsg();
}

$dataJson    = json_encode($dbRows);
$refreshedAt = $now->format('g:i:s A');
$asOfLabel   = 'As of: ' . $now->format('D, M j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bookings Dashboard</title>
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
    --panel-shadow: inset 1px 1px 0 #fff, inset -1px -1px 0 #888;
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

  /* Top bar */
  .topbar { background: var(--hd-blue); color: white; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; height: 42px; flex-shrink: 0; }
  .topbar-logo { font-size: 15px; font-weight: 700; letter-spacing: 0.5px; font-family: 'Roboto Condensed', sans-serif; }
  .topbar-logo span { color: #6db3ff; }
  .topbar-right { display: flex; align-items: center; gap: 20px; font-size: 12px; color: #b8cfee; }
  .topbar-right a { color: #b8cfee; text-decoration: none; }

  .layout { display: flex; flex: 1; overflow: hidden; }

  /* Nav */
  .nav { width: var(--hd-nav-width); background: var(--hd-nav-bg); flex-shrink: 0; overflow-y: auto; }
  .nav-section { padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
  .nav-item { display: block; padding: 6px 14px; color: #cde0ff; font-size: 12px; text-decoration: none; cursor: pointer; white-space: nowrap; }
  .nav-item:hover { background: rgba(255,255,255,0.12); color: white; }
  .nav-item.active { background: rgba(255,255,255,0.2); color: white; font-weight: 700; border-left: 3px solid #6db3ff; padding-left: 11px; }
  .nav-header { padding: 8px 14px 4px; color: #89afd4; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
  .nw-badge { display: inline-block; background: rgba(109,179,255,0.25); color: #9ecfff; font-size: 9px; padding: 1px 4px; border-radius: 2px; margin-left: 4px; }

  /* Main */
  .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

  /* Page header */
  .page-header { background: var(--bg); border-bottom: 2px solid #888; padding: 6px 12px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
    background: linear-gradient(to bottom, #ece9d8, #d4d0c8); }
  .page-title { font-size: 14px; font-weight: 700; color: #003; font-family: 'Roboto Condensed', sans-serif; }
  .page-meta { font-size: 11px; color: var(--hd-muted); }
  .header-right { display: flex; align-items: center; gap: 10px; }

  /* Refresh bar */
  .refresh-bar { background: #e8f0fb; border-bottom: 1px solid #bdd0ee; padding: 4px 14px; display: flex; align-items: center; gap: 14px; font-size: 11px; color: var(--hd-muted); flex-shrink: 0; }
  .refresh-dot { width: 8px; height: 8px; border-radius: 50%; background: #1a7a3c; animation: pulse 2s infinite; flex-shrink: 0; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
  .refresh-progress { flex: 1; max-width: 160px; height: 4px; background: #d0dced; border-radius: 2px; overflow: hidden; }
  .refresh-fill { height: 100%; background: #0055b3; border-radius: 2px; transition: width 1s linear; }
  .stat-pill { background: white; border: 1px solid var(--hd-border); border-radius: 12px; padding: 2px 10px; font-size: 11px; font-weight: 600; }
  .btn { font-size: 12px; padding: 3px 10px; border-radius: 3px; cursor: pointer; border: 1px solid; display: inline-flex; align-items: center; gap: 4px; background: white; color: var(--hd-text); border-color: var(--hd-border); }
  .btn:hover { background: #f0f4fa; }

  /* Dashboard body */
  .dash-body { flex: 1; overflow-y: auto; padding: 10px 12px 12px; background: var(--bg);
    background: linear-gradient(160deg, #dbd8cc 0%, #c8c4bc 100%); }

  /* Win98-style panel */
  .panel { background: var(--panel-bg); border: 2px solid; border-color: #fff #888 #888 #fff;
    box-shadow: 1px 1px 0 #000; }
  .panel-title { background: #000080; color: white; font-size: 11px; font-weight: 700; padding: 2px 6px;
    font-family: 'Roboto Condensed', sans-serif; letter-spacing: 0.5px; }

  /* Grid layouts */
  .top-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
  .bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
  .table-section { }

  /* LCD display panel */
  .lcd-panel { padding: 8px 12px 6px; }
  .lcd-display {
    background: #1a1a1a;
    border: 3px inset #666;
    border-radius: 4px;
    padding: 8px 12px;
    text-align: center;
    font-family: 'Share Tech Mono', monospace;
    letter-spacing: 2px;
    position: relative;
    overflow: hidden;
  }
  .lcd-display::before {
    content: '';
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(0,0,0,0.08) 3px, rgba(0,0,0,0.08) 4px);
    pointer-events: none;
  }
  .lcd-val { font-size: 36px; font-weight: 400; line-height: 1; }
  .lcd-label { font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    font-family: 'Roboto Condensed', sans-serif; margin-top: 3px; }

  .lcd-cyan .lcd-display { background: #001a22; box-shadow: 0 0 12px rgba(0,200,255,0.3) inset; }
  .lcd-cyan .lcd-val { color: var(--lcd-cyan); text-shadow: 0 0 8px rgba(0,200,255,0.8); }
  .lcd-cyan .lcd-dim { color: var(--lcd-cyan-dim); }
  .lcd-cyan .lcd-label { color: #00aacc; }

  .lcd-blue .lcd-display { background: #00051a; box-shadow: 0 0 12px rgba(50,100,255,0.3) inset; }
  .lcd-blue .lcd-val { color: var(--lcd-blue); text-shadow: 0 0 8px rgba(50,120,255,0.8); }
  .lcd-blue .lcd-dim { color: var(--lcd-blue-dim); }
  .lcd-blue .lcd-label { color: #3366cc; }

  .lcd-orange .lcd-display { background: #1a0a00; box-shadow: 0 0 12px rgba(255,150,0,0.3) inset; }
  .lcd-orange .lcd-val { color: var(--lcd-orange); text-shadow: 0 0 8px rgba(255,150,0,0.8); }
  .lcd-orange .lcd-dim { color: var(--lcd-orange-dim); }
  .lcd-orange .lcd-label { color: #cc7700; }

  .lcd-green .lcd-display { background: #001400; box-shadow: 0 0 12px rgba(0,200,0,0.3) inset; }
  .lcd-green .lcd-val { color: var(--lcd-green); text-shadow: 0 0 8px rgba(0,200,0,0.8); }
  .lcd-green .lcd-dim { color: var(--lcd-green-dim); }
  .lcd-green .lcd-label { color: #33aa33; }

  /* Chart panel */
  .chart-panel { padding: 6px 8px 8px; }
  .chart-title { font-size: 12px; font-weight: 700; text-align: center; margin-bottom: 4px;
    color: #111; font-family: 'Roboto Condensed', sans-serif; }
  .chart-wrap { height: 180px; position: relative; }

  /* Quad layout: LCD top, chart bottom in each cell */
  .quad-cell { display: flex; flex-direction: column; }
  .quad-cell .panel { margin-bottom: 0; }

  /* Detail table */
  .tbl-wrap { overflow-x: auto; }
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
  .zero { color: #aaa; }
  a.drill-link {
    color: inherit; text-decoration: none; display: block;
    padding: 4px 10px; margin: -4px -10px; border-radius: 2px;
    transition: background 0.15s; font-variant-numeric: tabular-nums;
  }
  a.drill-link:hover { background: #bbdaff; text-decoration: underline; cursor: pointer; }
  td.drillcell { padding: 0 !important; text-align: right; }
  .drill-link {
    color: inherit; text-decoration: none; display: block;
    padding: 4px 10px; margin: -4px -10px;
    border-radius: 2px; transition: background 0.15s;
    font-variant-numeric: tabular-nums;
  }
  .drill-link:hover { background: #bbdaff; text-decoration: underline; cursor: pointer; }

  /* Footer */
  .page-footer { background: var(--bg); border-top: 1px solid #999; padding: 3px 14px;
    display: flex; align-items: center; justify-content: space-between; font-size: 10px;
    color: #555; flex-shrink: 0;
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
      <a class="nav-item active" href="#">Bookings Dashboard <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/ShipmentsDashboard.php">Shipments Dashboard <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/SalesDashboard.php">Sales Dashboard <span class="nw-badge">&#8599;</span></a>
    </div>
  </nav>

  <main class="main">
    <div class="page-header">
      <div>
        <div class="page-title">Bookings Dashboard</div>
        <div class="page-meta">SG Dashboards &rsaquo; Order Entry &nbsp;&bull;&nbsp; Opens in new window &nbsp;&bull;&nbsp; All groups: View</div>
      </div>
      <div class="header-right">
        <button class="btn" onclick="exportCSV()">&#8595; Export</button>
        <button class="btn" onclick="triggerRefresh()">&#8635; Refresh Now</button>
      </div>
    </div>

    <div class="refresh-bar">
      <div class="refresh-dot" id="rDot"></div>
      <span id="rStatus">Live &mdash; refreshes every 5 minutes</span>
      <div class="refresh-progress"><div class="refresh-fill" id="rFill" style="width:100%"></div></div>
      <span>Next refresh in: <strong id="rCount">5m 0s</strong></span>
      <span class="stat-pill" id="rTime">Last refresh: just now</span>
      <span class="stat-pill" style="background:#fff0d0;border-color:#f0c060;color:#885500;" id="asOfDate"></span>
    </div>

    <?php if ($dbError): ?>
    <div style="background:#5a1010;color:#ffaaaa;padding:7px 14px;font-size:12px;font-family:monospace;">
      Query error: <?php echo htmlspecialchars($dbError); ?>
    </div>
    <?php endif; ?>
    <div class="dash-body">

      <!-- Top row: Day + Week -->
      <div class="top-grid">

        <!-- DAY -->
        <div class="quad-cell">
          <div class="panel lcd-cyan">
            <div class="panel-title">&#9632; TODAY&rsquo;S BOOKINGS</div>
            <div class="lcd-panel">
              <div class="lcd-display">
                <div class="lcd-val" id="lcd-day">$0.00</div>
              </div>
              <div class="lcd-label" style="color:#00aacc;text-align:center;margin-top:4px;">DAY BOOKED TOTAL</div>
            </div>
          </div>
          <div style="height:6px"></div>
          <div class="panel">
            <div class="panel-title">Day Booked by Salesperson</div>
            <div class="chart-panel">
              <div class="chart-wrap"><canvas id="chartDay"></canvas></div>
            </div>
          </div>
        </div>

        <!-- WEEK -->
        <div class="quad-cell">
          <div class="panel lcd-blue">
            <div class="panel-title">&#9632; WEEK-TO-DATE BOOKINGS</div>
            <div class="lcd-panel">
              <div class="lcd-display">
                <div class="lcd-val" id="lcd-week">$0.00</div>
              </div>
              <div class="lcd-label" style="color:#3366cc;text-align:center;margin-top:4px;">WEEK BOOKED TOTAL</div>
            </div>
          </div>
          <div style="height:6px"></div>
          <div class="panel">
            <div class="panel-title">Week Booked by Salesperson</div>
            <div class="chart-panel">
              <div class="chart-wrap"><canvas id="chartWeek"></canvas></div>
            </div>
          </div>
        </div>

      </div>

      <!-- Bottom row: Month + Year -->
      <div class="bottom-grid">

        <!-- MONTH -->
        <div class="quad-cell">
          <div class="panel lcd-orange">
            <div class="panel-title">&#9632; MONTH-TO-DATE BOOKINGS</div>
            <div class="lcd-panel">
              <div class="lcd-display">
                <div class="lcd-val" id="lcd-month">$0.00</div>
              </div>
              <div class="lcd-label" style="color:#cc7700;text-align:center;margin-top:4px;">MONTH BOOKED TOTAL</div>
            </div>
          </div>
          <div style="height:6px"></div>
          <div class="panel">
            <div class="panel-title">Month Booked by Salesperson</div>
            <div class="chart-panel">
              <div class="chart-wrap"><canvas id="chartMonth"></canvas></div>
            </div>
          </div>
        </div>

        <!-- YEAR -->
        <div class="quad-cell">
          <div class="panel lcd-green">
            <div class="panel-title">&#9632; YEAR-TO-DATE BOOKINGS</div>
            <div class="lcd-panel">
              <div class="lcd-display">
                <div class="lcd-val" id="lcd-year">$0.00</div>
              </div>
              <div class="lcd-label" style="color:#33aa33;text-align:center;margin-top:4px;">YEAR BOOKED TOTAL</div>
            </div>
          </div>
          <div style="height:6px"></div>
          <div class="panel">
            <div class="panel-title">Year Booked by Salesperson</div>
            <div class="chart-panel">
              <div class="chart-wrap"><canvas id="chartYear"></canvas></div>
            </div>
          </div>
        </div>

      </div>

      <!-- Detail Table -->
      <div class="panel table-section">
        <div class="panel-title">&#9632; BOOKINGS SUMMARY DETAIL</div>
        <div class="tbl-wrap" style="padding:6px 8px 8px;">
          <table class="detail" id="detailTable">
            <thead>
              <tr>
                <th style="text-align:left">Salesperson</th>
                <th style="text-align:center">Sls#</th>
                <th>Day Booked</th>
                <th>Week Booked</th>
                <th>Month Booked</th>
                <th>Year Booked</th>
              </tr>
            </thead>
            <tbody id="detailBody"></tbody>
            <tfoot id="detailFoot"></tfoot>
          </table>
        </div>
      </div>

    </div><!-- /dash-body -->

    <div class="page-footer">
      <span>192.168.120.40 / SEQUELPROD / SGBOOKINGS &nbsp;&mdash;&nbsp; Source: SGHDSDATA/OEORHD, OEORDT, HDCUST, HDSLSM</span>
      <span id="footerTime"></span>
    </div>
  </main>
</div>

<script>
// ─── SALESPERSON DATA ────────────────────────────────────────────────────────
// Colors matching the original report legend order
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

// Live data from SGHDSDATA — OEORHD joined OEORDT, amount = ODQORD * ODSLPR
const DATA = <?php echo $dataJson; ?>;

// ─── FORMATTING ──────────────────────────────────────────────────────────────
function fmtLCD(n) {
  return '$' + n.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}
const PERIOD_LABELS = { day:'Today', week:'This Week', month:'This Month', year:'This Year' };

const DRILLDOWN_BASE = '<?php echo "https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/BookingsDrilldown.php"; ?>';

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
    'title="' + label + ' orders for ' + slsName + '">' + dollars + '</a>';
}

// ─── LCD TOTALS ───────────────────────────────────────────────────────────────
function updateTotals() {
  const t = DATA.reduce((a,r) => ({
    day: a.day + r.day,
    week: a.week + r.week,
    month: a.month + r.month,
    year: a.year + r.year
  }), {day:0,week:0,month:0,year:0});
  document.getElementById('lcd-day').textContent   = fmtLCD(t.day);
  document.getElementById('lcd-week').textContent  = fmtLCD(t.week);
  document.getElementById('lcd-month').textContent = fmtLCD(t.month);
  document.getElementById('lcd-year').textContent  = fmtLCD(t.year);
  return t;
}

// ─── CHART BUILDER ───────────────────────────────────────────────────────────
let charts = {};

function makeBarChart(id, field) {
  const labels = DATA.map(r => r.name.split(' ')[0]); // first name only for axis
  const values = DATA.map(r => r[field]);
  const colors = DATA.map(r => SLS_COLORS[r.name] || '#999');

  if (charts[id]) charts[id].destroy();

  charts[id] = new Chart(document.getElementById(id), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: colors,
        borderColor: colors.map(c => c),
        borderWidth: 1,
        barPercentage: 0.75,
      }]
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
            boxWidth: 10,
            padding: 4,
            generateLabels: () => DATA.map(r => ({
              text: r.name,
              fillStyle: SLS_COLORS[r.name],
              strokeStyle: SLS_COLORS[r.name],
              lineWidth: 1,
              hidden: false,
            }))
          }
        },
        tooltip: {
          callbacks: {
            label: ctx => ' ' + fmtLCD(ctx.raw),
            title: ctx => DATA[ctx[0].dataIndex].name
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 9, family: 'Roboto Condensed' }, maxRotation: 0 }
        },
        y: {
          grid: { color: '#e0e0e0' },
          ticks: {
            font: { size: 9, family: 'Roboto Condensed' },
            callback: v => {
              if (v >= 1000000) return '$' + (v/1000000).toFixed(1) + 'M';
              if (v >= 1000) return '$' + (v/1000).toFixed(0) + 'K';
              return '$' + v;
            }
          }
        }
      }
    }
  });
}

function buildCharts() {
  makeBarChart('chartDay',   'day');
  makeBarChart('chartWeek',  'week');
  makeBarChart('chartMonth', 'month');
  makeBarChart('chartYear',  'year');
}

// ─── DETAIL TABLE ─────────────────────────────────────────────────────────────
function buildTable() {
  const tbody = document.getElementById('detailBody');
  tbody.innerHTML = '';
  DATA.forEach(r => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="display:flex;align-items:center;gap:6px;">
        <span style="display:inline-block;width:10px;height:10px;background:${SLS_COLORS[r.name]};flex-shrink:0;"></span>
        ${r.name}
      </td>
      <td style="text-align:center">${r.num}</td>
      <td class="drillcell">${fmtMoney(r.day,   r.num, r.name, 'day')}</td>
      <td class="drillcell">${fmtMoney(r.week,  r.num, r.name, 'week')}</td>
      <td class="drillcell">${fmtMoney(r.month, r.num, r.name, 'month')}</td>
      <td class="drillcell">${fmtMoney(r.year,  r.num, r.name, 'year')}</td>`;
    tbody.appendChild(tr);
  });

  const t = DATA.reduce((a,r) => ({day:a.day+r.day,week:a.week+r.week,month:a.month+r.month,year:a.year+r.year}),{day:0,week:0,month:0,year:0});
  document.getElementById('detailFoot').innerHTML = `
    <tr>
      <td>TOTAL</td><td></td>
      <td>${fmtMoney(t.day)}</td>
      <td>${fmtMoney(t.week)}</td>
      <td>${fmtMoney(t.month)}</td>
      <td>${fmtMoney(t.year)}</td>
    </tr>`;
}

// ─── REFRESH ──────────────────────────────────────────────────────────────────
// Auto-refresh: every 15 minutes, M-F only, 7:00 AM – 6:00 PM Eastern Time
// Outside that window: manual Refresh Now button only
// Eastern Time is always used regardless of browser/server timezone

const AUTO_SECS = 900; // 15 minutes

let countdown = AUTO_SECS;

function getEasternTime() {
  // Returns a Date object representing current Eastern Time (handles EST/EDT automatically)
  const now = new Date();
  const etStr = now.toLocaleString('en-US', { timeZone: 'America/New_York' });
  return new Date(etStr);
}

function isAutoRefreshTime() {
  const et    = getEasternTime();
  const day   = et.getDay();    // 0=Sun 1=Mon 2=Tue 3=Wed 4=Thu 5=Fri 6=Sat
  const hour  = et.getHours();  // 0-23
  const isWeekday = day >= 1 && day <= 5;
  // Window: 7:00 AM to 6:00 PM Eastern
  const afterStart  = (hour >= 7);
  const beforeEnd   = (hour < 18);
  return isWeekday && afterStart && beforeEnd;
}

function fmtCountdown(secs) {
  const tot = Math.max(0, secs);
  const d = Math.floor(tot / 86400);
  const h = Math.floor((tot % 86400) / 3600);
  const m = Math.floor((tot % 3600) / 60);
  const s = tot % 60;
  const mm = String(m).padStart(2, '0');
  const ss = String(s).padStart(2, '0');
  if (d > 0) return d + (d === 1 ? ' day ' : ' days ') + String(h).padStart(2, '0') + ':' + mm + ':' + ss;
  if (h > 0) return h + ':' + mm + ':' + ss;
  return m + ':' + ss;
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
    countdown = AUTO_SECS; // reset so it fires promptly when window reopens
  }
}

function triggerRefresh() {
  document.getElementById('rDot').style.background = '#f39c12';
  document.getElementById('rStatus').textContent = 'Refreshing...';
  setTimeout(function() { location.reload(); }, 300);
}

setInterval(() => {
  if (isAutoRefreshTime()) {
    countdown--;
    if (countdown <= 0) {
      countdown = AUTO_SECS;
      triggerRefresh();
      return;
    }
  }
  updateStatusBar();
}, 1000);

function exportCSV() {
  const rows = [['Salesperson','Sls#','Day Booked','Week Booked','Month Booked','Year Booked']];
  DATA.forEach(r => rows.push([r.name, r.num, r.day, r.week, r.month, r.year]));
  const t = DATA.reduce((a,r)=>({day:a.day+r.day,week:a.week+r.week,month:a.month+r.month,year:a.year+r.year}),{day:0,week:0,month:0,year:0});
  rows.push(['TOTAL','',t.day,t.week,t.month,t.year]);
  const csv = rows.map(r => r.map(v => '"'+v+'"').join(',')).join('\n');
  const a = document.createElement('a');
  a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
  a.download = 'Bookings_' + new Date().toISOString().slice(0,10) + '.csv';
  a.click();
}

// ─── INIT ─────────────────────────────────────────────────────────────────────
document.getElementById('rTime').textContent    = 'Last refresh: <?php echo $refreshedAt; ?>';
document.getElementById('footerTime').textContent = '<?php echo $now->format('m/d/Y') . '  ' . $refreshedAt; ?>';
document.getElementById('asOfDate').textContent  = '<?php echo $asOfLabel; ?>';
updateTotals();
buildTable();
buildCharts();
</script>
</body>
</html>
