<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$now  = new DateTime();
$yr   = (int)$now->format('Y');
$mo   = (int)$now->format('m');
$dy   = (int)$now->format('d');
$next = clone $now; $next->modify('+1 day');

function dateToCymd(DateTime $dt) {
    return ((int)$dt->format('Y') - 1900) * 10000
         + (int)$dt->format('m') * 100
         + (int)$dt->format('d');
}

function yrRange(DateTime $base, $targetYear) {
    $start = ($targetYear - 1900) * 10000 + 101;
    $endDt = new DateTime(
        $targetYear . '-' . $base->format('m') . '-' . $base->format('d')
    );
    $endDt->modify('+1 day');
    return array($start, dateToCymd($endDt));
}

function pctCell($curr, $prev) {
    if ($prev == 0 && $curr == 0) return '<td class="pct-na">--</td>';
    if ($prev == 0) return '<td class="pct-up">new</td>';
    $p = round(($curr - $prev) / abs($prev) * 100, 1);
    $cls = $p >= 0 ? 'pct-up' : 'pct-dn';
    return '<td class="' . $cls . '">' . ($p >= 0 ? '+' : '') . $p . '%</td>';
}

$y0 = $yr - 4;  $y1 = $yr - 3;  $y2 = $yr - 2;  $y3 = $yr - 1;  $y4 = $yr;
list($s0, $e0) = yrRange($now, $y0);
list($s1, $e1) = yrRange($now, $y1);
list($s2, $e2) = yrRange($now, $y2);
list($s3, $e3) = yrRange($now, $y3);
list($s4, $e4) = yrRange($now, $y4);   // $e4 = tomorrow CYMD

$todayCymd = ($yr - 1900) * 10000 + $mo * 100 + $dy;
$mtdStart  = ($yr - 1900) * 10000 + $mo * 100 + 1;
$earliest  = $s0;

$conn    = $i5Connect->getConnection();
$dbError = '';
$rows    = array();

$sql = "
    SELECT
        COALESCE(TRIM(cust.CMCCLS), '??')  AS CLSCODE,
        COALESCE(TRIM(cls.CCCCDS),  '')    AS CLSDESC,
        SUM(CASE WHEN d.DHDTLI >= $s0 AND d.DHDTLI < $e0 AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)  AS YR0,
        SUM(CASE WHEN d.DHDTLI >= $s1 AND d.DHDTLI < $e1 AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)  AS YR1,
        SUM(CASE WHEN d.DHDTLI >= $s2 AND d.DHDTLI < $e2 AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)  AS YR2,
        SUM(CASE WHEN d.DHDTLI >= $s3 AND d.DHDTLI < $e3 AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)  AS YR3,
        SUM(CASE WHEN d.DHDTLI >= $s4 AND d.DHDTLI < $e4 AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)  AS YR4,
        SUM(CASE WHEN d.DHDTLI >= $mtdStart AND d.DHDTLI < $e4 AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)  AS MTD,
        SUM(CASE WHEN d.DHDTLI =  $todayCymd AND d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)  AS TODAY
    FROM SGHDSDATA.OEORDH d
    JOIN  SGHDSDATA.OEORHD h        ON d.\"DHORD#\" = h.\"OEORD#\"
    LEFT JOIN SGHDSDATA.HDCUST cust ON h.OESHTO    = cust.CMCUST
    LEFT JOIN SGHDSDATA.HDCCLS cls  ON cust.CMCCLS = cls.CCCCLS
    WHERE d.\"DHSEQ#\" <> 0
      AND d.DHQSTC <> 0
      AND d.DHDTLI >= $earliest
      AND d.DHDTLI <  $e4
    GROUP BY cust.CMCCLS, cls.CCCCDS
    ORDER BY YR4 DESC
";

$totals = array('y0'=>0,'y1'=>0,'y2'=>0,'y3'=>0,'y4'=>0,'mtd'=>0,'today'=>0);
$stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $row = array(
            'code'  => trim((string)$r['CLSCODE']),
            'desc'  => trim((string)$r['CLSDESC']),
            'y0'    => round((float)$r['YR0'], 2),
            'y1'    => round((float)$r['YR1'], 2),
            'y2'    => round((float)$r['YR2'], 2),
            'y3'    => round((float)$r['YR3'], 2),
            'y4'    => round((float)$r['YR4'], 2),
            'mtd'   => round((float)$r['MTD'], 2),
            'today' => round((float)$r['TODAY'], 2),
        );
        $rows[] = $row;
        foreach (array('y0','y1','y2','y3','y4','mtd','today') as $k)
            $totals[$k] += $row[$k];
    }
    db2_free_stmt($stmt);
} else {
    $dbError = db2_stmt_errormsg();
}
foreach (array('y0','y1','y2','y3','y4','mtd','today') as $k)
    $totals[$k] = round($totals[$k], 2);

$rowsJson    = json_encode($rows);
$totalsJson  = json_encode($totals);
$refreshedAt = $now->format('g:i:s A');
$asOf        = $now->format('D, M j, Y');
$periodLbl   = 'Jan 1 &ndash; ' . $now->format('M j');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cust Class Daily Sales Past 5 Years &mdash; <?php echo $yr; ?></title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Roboto+Condensed:wght@400;700&display=swap');

  :root {
    --hd-blue:     #003087;
    --hd-nav-bg:   #0046a8;
    --hd-nav-width:180px;
    --hd-border:   #d0d7e2;
    --hd-text:     #1a2233;
    --hd-muted:    #5a6478;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Roboto Condensed', Arial, sans-serif;
    font-size: 13px; color: var(--hd-text);
    background: #edf1f7; display: flex; flex-direction: column;
    height: 100vh; overflow: hidden;
  }

  /* ── Top bar ──────────────────────────────────── */
  .topbar {
    background: var(--hd-blue); color: white;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 16px; height: 42px; flex-shrink: 0;
  }
  .topbar-logo { font-size: 15px; font-weight: 700; letter-spacing: .5px; }
  .topbar-logo span { color: #6db3ff; }
  .topbar-right { display: flex; align-items: center; gap: 20px; font-size: 12px; color: #b8cfee; }
  .topbar-right a { color: #b8cfee; text-decoration: none; }

  .layout { display: flex; flex: 1; overflow: hidden; }

  /* ── Left nav ─────────────────────────────────── */
  .nav { width: var(--hd-nav-width); background: var(--hd-nav-bg); flex-shrink: 0; overflow-y: auto; }
  .nav-section { padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,.1); }
  .nav-item { display: block; padding: 6px 14px; color: #cde0ff; font-size: 12px; text-decoration: none; white-space: nowrap; }
  .nav-item:hover { background: rgba(255,255,255,.12); color: white; }
  .nav-item.active { background: rgba(255,255,255,.2); color: white; font-weight: 700; border-left: 3px solid #6db3ff; padding-left: 11px; }
  .nav-header { padding: 8px 14px 4px; color: #89afd4; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
  .nw-badge { display: inline-block; background: rgba(109,179,255,.25); color: #9ecfff; font-size: 9px; padding: 1px 4px; border-radius: 2px; margin-left: 4px; }

  /* ── Main column ──────────────────────────────── */
  .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

  .page-header {
    background: linear-gradient(to bottom, #ece9d8, #d4d0c8);
    border-bottom: 2px solid #888; padding: 6px 12px;
    display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
  }
  .page-title { font-size: 14px; font-weight: 700; color: #003; }
  .page-meta  { font-size: 11px; color: var(--hd-muted); }
  .btn {
    font-size: 12px; padding: 3px 10px; border-radius: 3px; cursor: pointer;
    border: 1px solid var(--hd-border); display: inline-flex; align-items: center; gap: 4px;
    background: white; color: var(--hd-text); text-decoration: none;
  }
  .btn:hover { background: #f0f4fa; }
  .btn-back { background: #f0f0e8; border-color: #aaa; color: #333; }
  .btn-back:hover { background: #e8e8d8; }

  /* ── Refresh bar ──────────────────────────────── */
  .refresh-bar {
    background: #e8f0fb; border-bottom: 1px solid #bdd0ee;
    padding: 4px 14px; display: flex; align-items: center; gap: 14px;
    font-size: 11px; color: #5a6478; flex-shrink: 0;
  }
  .refresh-dot { width: 8px; height: 8px; border-radius: 50%; background: #1a7a3c; animation: pulse 2s infinite; flex-shrink: 0; }
  .refresh-dot--off { background: #94a3b8; animation: none; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
  .refresh-progress { flex: 1; max-width: 160px; height: 4px; background: #d0dced; border-radius: 2px; overflow: hidden; }
  .refresh-fill { height: 100%; background: #0055b3; border-radius: 2px; transition: width 1s linear; }
  .refresh-pill { background: #fff; border: 1px solid #c8d0de; border-radius: 12px; padding: 2px 10px; font-size: 11px; font-weight: 600; white-space: nowrap; }

  /* ── Dashboard body ───────────────────────────── */
  .dash-body {
    flex: 1; overflow-y: auto; padding: 16px;
    background: linear-gradient(160deg, #dbd8cc 0%, #c8c4bc 100%);
  }
  .content-wrap { width: 100%; max-width: 1200px; margin: 0 auto; }

  /* ── Win98 panel ──────────────────────────────── */
  .panel { background: #fff; border: 2px solid; border-color: #fff #888 #888 #fff; box-shadow: 1px 1px 0 #000; }
  .panel-title { background: #000080; color: white; font-size: 11px; font-weight: 700; padding: 2px 6px; letter-spacing: .5px; }

  /* ── Filter bar ───────────────────────────────── */
  .filter-bar {
    display: flex; align-items: center; gap: 8px;
    padding: 6px 12px; background: #f0f0f0; border-bottom: 1px solid #ccc;
  }
  .filter-lbl { font-size: 12px; font-weight: 700; color: #333; white-space: nowrap; }
  .filter-input {
    font-size: 12px; padding: 3px 8px; border: 1px solid #bbb;
    border-radius: 3px; width: 180px; font-family: 'Roboto Condensed', sans-serif;
  }
  .btn-clear {
    font-size: 11px; padding: 2px 8px; background: #fff0e8;
    border-color: #d08060; color: #8b3010;
  }
  .btn-clear:hover { background: #ffe0c8; }
  .btn-clear:disabled { background: #f0f0f0; border-color: #ccc; color: #aaa; cursor: default; }
  .row-count { font-size: 12px; font-style: italic; color: #555; }
  .btn-export {
    margin-left: auto;
    font-size: 13px; font-weight: 700; padding: 5px 18px;
    background: #1a7a3c; border-color: #0a4a1c; color: white;
    border-radius: 4px;
  }
  .btn-export:hover { background: #0a5a2a; }

  /* ── Table ────────────────────────────────────── */
  .tbl-wrap { overflow-x: auto; }
  table.dtbl { width: 100%; border-collapse: collapse; font-size: 12px; font-family: 'Roboto Condensed', sans-serif; }
  table.dtbl thead th { background: #000080; color: white; padding: 4px 8px; text-align: right; font-size: 11px; font-weight: 700; white-space: nowrap; border-right: 1px solid #3333aa; }
  table.dtbl thead th.l { text-align: left; }
  table.dtbl thead th.cur { background: #004000; }
  table.dtbl tbody tr:nth-child(even) { background: #f0f0f0; }
  table.dtbl tbody tr:hover { background: #ddeeff; }
  table.dtbl tbody tr.hidden { display: none; }
  table.dtbl tbody td { padding: 4px 8px; text-align: right; white-space: nowrap; }
  table.dtbl tbody td.l { text-align: left; }
  table.dtbl tbody td.zero { color: #bbb; }
  table.dtbl tfoot td { background: #ddd; font-weight: 700; padding: 4px 8px; text-align: right; border-top: 2px solid #888; white-space: nowrap; }
  table.dtbl tfoot td.l { text-align: left; }

  .cls-link { color: #0055b3; text-decoration: underline; cursor: pointer; font-weight: 700; font-variant-numeric: tabular-nums; }
  .cls-link:hover { color: #003087; background: #e8f0ff; border-radius: 2px; }

  /* ── YoY % columns ────────────────────────────── */
  table.dtbl thead th.pct-hdr { background: #1a1a6e; min-width: 54px; font-size: 10px; letter-spacing: .5px; }
  table.dtbl thead th.pct-hdr.cur { background: #003300; }
  table.dtbl tbody td.pct-up  { color: #1a7a3c; font-size: 11px; text-align: center; background: #f0fff4; }
  table.dtbl tbody td.pct-dn  { color: #b91c1c; font-size: 11px; text-align: center; background: #fff4f4; }
  table.dtbl tbody td.pct-na  { color: #bbb;    font-size: 11px; text-align: center; }
  table.dtbl tfoot td.pct-up  { color: #1a7a3c; font-size: 11px; text-align: center; }
  table.dtbl tfoot td.pct-dn  { color: #b91c1c; font-size: 11px; text-align: center; }
  table.dtbl tfoot td.pct-na  { color: #bbb;    font-size: 11px; text-align: center; }

  /* ── Chart modal ──────────────────────────────── */
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); align-items: center; justify-content: center; z-index: 1100; }
  .modal-overlay.open { display: flex; }
  .modal { background: #fff; border: 2px solid; border-color: #fff #888 #888 #fff; box-shadow: 4px 4px 0 #000; max-width: 95vw; max-height: 88vh; display: flex; flex-direction: column; width: 820px; }
  .modal-hdr { background: #000080; color: white; padding: 3px 8px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
  .modal-hdr-title { font-size: 12px; font-weight: 700; letter-spacing: .5px; }
  .modal-x { background: #d4d0c8; border: 2px solid; border-color: #fff #808080 #808080 #fff; color: #000; font-size: 11px; font-weight: 700; cursor: pointer; padding: 0 6px; height: 18px; line-height: 14px; font-family: monospace; }
  .modal-x:hover { background: #c0b8b0; }
  .modal-body { display: flex; gap: 0; padding: 0; overflow: hidden; flex: 1; }
  .modal-pie-col { padding: 16px 10px 16px 16px; flex-shrink: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; border-right: 1px solid #ddd; }
  .modal-tbl-col { flex: 1; overflow-y: auto; padding: 16px; }
  .modal-tbl-col table { width: 100%; border-collapse: collapse; font-size: 12px; font-family: 'Roboto Condensed', sans-serif; }
  .modal-tbl-col thead th { background: #000080; color: white; padding: 4px 10px; font-size: 11px; font-weight: 700; white-space: nowrap; text-align: right; }
  .modal-tbl-col thead th.l { text-align: left; }
  .modal-tbl-col tbody tr:nth-child(even) { background: #f0f0f0; }
  .modal-tbl-col tbody td { padding: 5px 10px; text-align: right; white-space: nowrap; }
  .modal-tbl-col tbody td.l { text-align: left; }
  .modal-tbl-col tfoot td { background: #ddd; font-weight: 700; padding: 5px 10px; text-align: right; border-top: 2px solid #888; }
  .modal-tbl-col tfoot td.l { text-align: left; }
  .yr-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; vertical-align: middle; flex-shrink: 0; }
  .cur-yr-row { font-weight: 700; }

  /* ── Footer ───────────────────────────────────── */
  .page-footer {
    background: linear-gradient(to bottom, #d4d0c8, #c8c4bc);
    border-top: 1px solid #999; padding: 3px 14px;
    display: flex; align-items: center; justify-content: space-between;
    font-size: 10px; color: #555; flex-shrink: 0;
  }
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
      <div class="nav-header">SG Management</div>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Management/RevenueVsGoal.php">Revenue vs Goal <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Management/NewAccountsRevenue.php">New Acct Rev vs Goal <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Management/BottomHalfRevenue.php">Bottom Half Rev Growth <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item active" href="#">Daily Sales 5-Yr Report</a>
    </div>
    <div class="nav-section">
      <div class="nav-header">SG Dashboards</div>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/BookingsDashboard.php">Bookings <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/ShipmentsDashboard.php">Shipments <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/SalesDashboard.php">Sales <span class="nw-badge">&#8599;</span></a>
    </div>
  </nav>

  <main class="main">
    <div class="page-header">
      <div>
        <div class="page-title">Customer Class Daily Sales &mdash; Past 5 Years</div>
        <div class="page-meta">
          SG Management &nbsp;&bull;&nbsp; YTD: <?php echo $periodLbl; ?> &nbsp;&bull;&nbsp;
          <?php echo count($rows); ?> classes &nbsp;&bull;&nbsp;
          Click a class code to see 5-year pie chart
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:8px;">
        <a class="btn btn-back" href="javascript:history.back()">&#8592; Back to EIP</a>
        <button class="btn" onclick="triggerRefresh()">&#8635; Refresh Now</button>
      </div>
    </div>

    <div class="refresh-bar">
      <div class="refresh-dot" id="cs5-dot"></div>
      <span id="cs5-status">Checking...</span>
      <div class="refresh-progress"><div class="refresh-fill" id="cs5-prog" style="width:100%"></div></div>
      <span>Next refresh: <strong id="cs5-cd">&ndash;</strong></span>
      <span class="refresh-pill">Last refresh: <strong><?php echo $refreshedAt; ?></strong></span>
      <span class="refresh-pill" style="background:#fff0d0;border-color:#f0c060;color:#885500;">As of: <?php echo $asOf; ?></span>
    </div>

    <?php if ($dbError): ?>
    <div style="background:#5a1010;color:#ffaaaa;padding:7px 14px;font-size:12px;font-family:monospace;">
      Query error: <?php echo htmlspecialchars($dbError); ?>
    </div>
    <?php endif; ?>

    <div class="dash-body">
      <div class="content-wrap">

        <div class="panel">
          <div class="panel-title">&#9632; CUSTOMER CLASS DAILY SALES &mdash; PAST 5 YEARS &mdash; YTD <?php echo $periodLbl; ?> EACH YEAR</div>

          <div class="filter-bar">
            <span class="filter-lbl">Search:</span>
            <input class="filter-input" type="text" id="searchBox"
              placeholder="Class code or description..."
              oninput="applyFilter()" autocomplete="off">
            <button class="btn btn-clear" id="clearBtn" disabled onclick="clearFilter()">&#x2715; Clear</button>
            <span class="row-count" id="rowCount"></span>
            <button class="btn btn-export" onclick="exportExcel()">&#8595; Export to Excel</button>
          </div>

          <div class="tbl-wrap">
            <table class="dtbl">
              <thead>
                <tr>
                  <th class="l" rowspan="1">Code</th>
                  <th class="l">Description</th>
                  <th><?php echo "'{$y0}"; ?> YTD</th>
                  <th><?php echo "'{$y1}"; ?> YTD</th>
                  <th class="pct-hdr">%</th>
                  <th><?php echo "'{$y2}"; ?> YTD</th>
                  <th class="pct-hdr">%</th>
                  <th><?php echo "'{$y3}"; ?> YTD</th>
                  <th class="pct-hdr">%</th>
                  <th class="cur"><?php echo "'{$y4}"; ?> YTD</th>
                  <th class="pct-hdr cur">%</th>
                  <th class="cur"><?php echo $now->format('M'); ?> MTD</th>
                  <th class="cur">Today</th>
                </tr>
              </thead>
              <tbody id="tblBody">
<?php
foreach ($rows as $idx => $row):
    $amt = function($v) { return $v == 0 ? '<td class="zero">$.00</td>' : '<td>$' . number_format($v, 2) . '</td>'; };
?>
                <tr data-code="<?php echo htmlspecialchars(strtolower($row['code'])); ?>"
                    data-desc="<?php echo htmlspecialchars(strtolower($row['desc'])); ?>"
                    data-idx="<?php echo $idx; ?>">
                  <td class="l"><span class="cls-link" onclick="openChart(<?php echo $idx; ?>)"
                    title="Click for 5-year comparison chart"><?php echo htmlspecialchars($row['code']); ?></span></td>
                  <td class="l"><?php echo htmlspecialchars($row['desc']); ?></td>
                  <?php echo $amt($row['y0']); ?>
                  <?php echo $amt($row['y1']); ?><?php echo pctCell($row['y1'],$row['y0']); ?>
                  <?php echo $amt($row['y2']); ?><?php echo pctCell($row['y2'],$row['y1']); ?>
                  <?php echo $amt($row['y3']); ?><?php echo pctCell($row['y3'],$row['y2']); ?>
                  <?php echo $amt($row['y4']); ?><?php echo pctCell($row['y4'],$row['y3']); ?>
                  <?php echo $amt($row['mtd']); ?>
                  <?php echo $amt($row['today']); ?>
                </tr>
<?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr id="tblFoot">
                  <td class="l" colspan="2">GRAND TOTAL &mdash; <?php echo count($rows); ?> classes</td>
                  <td>$<?php echo number_format($totals['y0'], 2); ?></td>
                  <td>$<?php echo number_format($totals['y1'], 2); ?></td>
                  <?php echo pctCell($totals['y1'],$totals['y0']); ?>
                  <td>$<?php echo number_format($totals['y2'], 2); ?></td>
                  <?php echo pctCell($totals['y2'],$totals['y1']); ?>
                  <td>$<?php echo number_format($totals['y3'], 2); ?></td>
                  <?php echo pctCell($totals['y3'],$totals['y2']); ?>
                  <td>$<?php echo number_format($totals['y4'], 2); ?></td>
                  <?php echo pctCell($totals['y4'],$totals['y3']); ?>
                  <td>$<?php echo number_format($totals['mtd'], 2); ?></td>
                  <td>$<?php echo number_format($totals['today'], 2); ?></td>
                </tr>
              </tfoot>
            </table>
          </div><!-- /tbl-wrap -->

        </div><!-- /panel -->

      </div><!-- /content-wrap -->
    </div><!-- /dash-body -->

    <div class="page-footer">
      <span>SGHDSDATA/OEORDH &rarr; OEORHD &rarr; HDCUST (CMCCLS) &rarr; HDCCLS &mdash; DHSLPR &times; DHQSTC &divide; DHORUF &mdash; YTD <?php echo $periodLbl; ?> each year (<?php echo $y0; ?>&ndash;<?php echo $y4; ?>)</span>
      <span><?php echo $now->format('m/d/Y') . '  ' . $refreshedAt; ?></span>
    </div>
  </main>
</div>

<!-- ── Pie chart modal ────────────────────────────────────────── -->
<div class="modal-overlay" id="chartModal">
  <div class="modal">
    <div class="modal-hdr">
      <span class="modal-hdr-title" id="chartModalTitle">&#9632; 5-Year YTD Sales Comparison</span>
      <button class="modal-x" onclick="closeChart()">&#x2715;</button>
    </div>
    <div class="modal-body">
      <div class="modal-pie-col">
        <canvas id="pieChart" width="300" height="300"></canvas>
        <div id="pieNote" style="font-size:10px;color:#777;text-align:center;margin-top:8px;max-width:300px;line-height:1.4"></div>
      </div>
      <div class="modal-tbl-col">
        <table>
          <thead>
            <tr>
              <th class="l">Year</th>
              <th>YTD Amount</th>
              <th>vs Prev Year</th>
              <th>% YoY</th>
            </tr>
          </thead>
          <tbody id="chartTblBody"></tbody>
          <tfoot id="chartTblFoot"></tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
var ROWS       = <?php echo $rowsJson; ?>;
var TOTALS     = <?php echo $totalsJson; ?>;
var YEARS      = [<?php echo "$y0,$y1,$y2,$y3,$y4"; ?>];
var YKEYS      = ['y0','y1','y2','y3','y4'];
var PERIOD_LBL = <?php echo json_encode('Jan 1 – ' . $now->format('M j')); ?>;

var YR_COLORS  = ['#94a3b8', '#3b82f6', '#10b981', '#f59e0b', '#003087'];
var YR_HOVER   = ['#64748b', '#1d4ed8', '#047857', '#d97706', '#0055b3'];

var pieInst = null;

// ── Utilities ──────────────────────────────────────────────────
function fmtMoney(n) {
  return '$' + Math.abs(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}
function fmtDiff(n) {
  if (n === null) return '<span style="color:#aaa">&ndash;</span>';
  var pos = n >= 0;
  var s   = (pos ? '+$' : '-$') + Math.abs(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
  return '<span style="color:' + (pos ? '#1a7a3c' : '#b91c1c') + '">' + s + '</span>';
}
function calcPct(curr, prev) {
  if (prev === 0 && curr === 0) return '--';
  if (prev === 0) return 'new';
  var p = Math.round((curr - prev) / Math.abs(prev) * 1000) / 10;
  return (p >= 0 ? '+' : '') + p.toFixed(1) + '%';
}
function setFootPct(cell, curr, prev) {
  if (prev === 0 && curr === 0) { cell.textContent = '--';   cell.className = 'pct-na'; return; }
  if (prev === 0)               { cell.textContent = 'new';  cell.className = 'pct-up'; return; }
  var p = Math.round((curr - prev) / Math.abs(prev) * 1000) / 10;
  cell.textContent = (p >= 0 ? '+' : '') + p.toFixed(1) + '%';
  cell.className   = p >= 0 ? 'pct-up' : 'pct-dn';
}

// ── Filter ─────────────────────────────────────────────────────
function applyFilter() {
  var q    = (document.getElementById('searchBox').value || '').toLowerCase().trim();
  var rows = document.querySelectorAll('#tblBody tr');
  var vis  = 0;
  var totals = {y0:0,y1:0,y2:0,y3:0,y4:0,mtd:0,today:0};

  rows.forEach(function(tr) {
    var code = tr.getAttribute('data-code') || '';
    var desc = tr.getAttribute('data-desc') || '';
    var show = !q || code.indexOf(q) >= 0 || desc.indexOf(q) >= 0;
    tr.classList.toggle('hidden', !show);
    if (show) {
      vis++;
      var idx = parseInt(tr.getAttribute('data-idx'), 10);
      var r   = ROWS[idx];
      if (r) {
        YKEYS.forEach(function(k) { totals[k] += r[k]; });
        totals.mtd   += r.mtd;
        totals.today += r.today;
      }
    }
  });

  document.getElementById('clearBtn').disabled = !q;
  document.getElementById('rowCount').textContent = vis + ' class' + (vis !== 1 ? 'es' : '') + ' shown';

  // Update footer totals
  // cells: [0]=label  [1]=y0  [2]=y1  [3]=%  [4]=y2  [5]=%  [6]=y3  [7]=%  [8]=y4  [9]=%  [10]=mtd  [11]=today
  var foot = document.getElementById('tblFoot');
  if (foot) {
    foot.cells[0].textContent  = 'GRAND TOTAL — ' + vis + ' class' + (vis !== 1 ? 'es' : '');
    foot.cells[1].textContent  = fmtMoney(totals.y0);
    foot.cells[2].textContent  = fmtMoney(totals.y1);
    setFootPct(foot.cells[3],  totals.y1,  totals.y0);
    foot.cells[4].textContent  = fmtMoney(totals.y2);
    setFootPct(foot.cells[5],  totals.y2,  totals.y1);
    foot.cells[6].textContent  = fmtMoney(totals.y3);
    setFootPct(foot.cells[7],  totals.y3,  totals.y2);
    foot.cells[8].textContent  = fmtMoney(totals.y4);
    setFootPct(foot.cells[9],  totals.y4,  totals.y3);
    foot.cells[10].textContent = fmtMoney(totals.mtd);
    foot.cells[11].textContent = fmtMoney(totals.today);
  }
}

function clearFilter() {
  document.getElementById('searchBox').value = '';
  applyFilter();
}

// ── Pie chart modal ────────────────────────────────────────────
function openChart(idx) {
  var r = ROWS[idx];
  if (!r) return;

  var vals  = YKEYS.map(function(k) { return r[k]; });
  var total = vals.reduce(function(s,v) { return s + v; }, 0);
  var label = r.code + (r.desc ? ': ' + r.desc : '');

  document.getElementById('chartModalTitle').textContent =
    '■ 5-Year YTD Sales — ' + label;
  document.getElementById('pieNote').textContent =
    PERIOD_LBL + ' comparison  •  ' + YEARS[0] + '–' + YEARS[4] +
    '  •  5-yr total: ' + fmtMoney(total);

  buildPie(r, vals, total);
  buildChartTable(r, vals, total);

  document.getElementById('chartModal').classList.add('open');
}

function closeChart() { document.getElementById('chartModal').classList.remove('open'); }
document.getElementById('chartModal').addEventListener('click', function(e) {
  if (e.target === this) closeChart();
});

function buildPie(r, vals, total) {
  if (pieInst) { pieInst.destroy(); pieInst = null; }
  var labels = YEARS.map(function(y, i) {
    var pct = total > 0 ? (vals[i] / total * 100).toFixed(1) : '0.0';
    return "'" + String(y).slice(2) + ' YTD (' + pct + '%)';
  });
  pieInst = new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        data: vals,
        backgroundColor: YR_COLORS,
        hoverBackgroundColor: YR_HOVER,
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: { font: {size:11, family:"'Roboto Condensed',sans-serif"}, boxWidth: 12, padding: 8 }
        },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              var v   = ctx.raw;
              var pct = total > 0 ? (v / total * 100).toFixed(1) : '0.0';
              return '  ' + fmtMoney(v) + '  (' + pct + '%)';
            }
          }
        }
      }
    }
  });
}

function buildChartTable(r, vals, total) {
  var tbody = document.getElementById('chartTblBody');
  tbody.innerHTML = '';
  var fiveYrTotal = 0;
  vals.forEach(function(v, i) {
    fiveYrTotal += v;
    var diff  = i > 0 ? v - vals[i-1] : null;
    var isCur = (i === 4);
    var yoyCell;
    if (i === 0) {
      yoyCell = '<span style="color:#aaa">&ndash;</span>';
    } else {
      var prev = vals[i-1];
      if (prev === 0 && v === 0) {
        yoyCell = '<span style="color:#aaa">&ndash;</span>';
      } else if (prev === 0) {
        yoyCell = '<span style="color:#1a7a3c">new</span>';
      } else {
        var p = Math.round((v - prev) / Math.abs(prev) * 1000) / 10;
        var c = p >= 0 ? '#1a7a3c' : '#b91c1c';
        yoyCell = '<span style="color:' + c + '">' + (p >= 0 ? '+' : '') + p.toFixed(1) + '%</span>';
      }
    }
    var tr = document.createElement('tr');
    if (isCur) tr.className = 'cur-yr-row';
    tr.innerHTML =
      '<td class="l"><span class="yr-dot" style="background:' + YR_COLORS[i] + '"></span>'
      + YEARS[i] + ' YTD' + (isCur ? ' <em style="color:#1a7a3c;font-size:10px">(current)</em>' : '') + '</td>'
      + '<td>' + fmtMoney(v) + '</td>'
      + '<td>' + fmtDiff(diff) + '</td>'
      + '<td>' + yoyCell + '</td>';
    tbody.appendChild(tr);
  });
  var ytdRow = document.createElement('tr');
  ytdRow.style.background = '#f0f4ff';
  ytdRow.innerHTML =
    '<td class="l" style="padding-top:6px"><em style="color:#777;font-size:11px">MTD / Today</em></td>'
    + '<td style="color:#555"><em>' + fmtMoney(r.mtd) + ' / ' + fmtMoney(r.today) + '</em></td>'
    + '<td></td><td></td>';
  tbody.appendChild(ytdRow);
  document.getElementById('chartTblFoot').innerHTML =
    '<tr><td class="l">5-Year Total</td><td>' + fmtMoney(fiveYrTotal) + '</td><td></td><td></td></tr>';
}

// ── Export to Excel ────────────────────────────────────────────
function exportExcel() {
  function csvField(v) {
    v = String(v == null ? '' : v);
    return (v.indexOf(',') >= 0 || v.indexOf('"') >= 0 || v.indexOf('\n') >= 0)
      ? '"' + v.replace(/"/g, '""') + '"' : v;
  }
  var colHeaders = [
    'Code', 'Description',
    "'" + String(YEARS[0]).slice(2) + ' YTD',
    "'" + String(YEARS[1]).slice(2) + ' YTD', '%YoY',
    "'" + String(YEARS[2]).slice(2) + ' YTD', '%YoY',
    "'" + String(YEARS[3]).slice(2) + ' YTD', '%YoY',
    "'" + String(YEARS[4]).slice(2) + ' YTD', '%YoY',
    'MTD', 'Today'
  ];
  var result = [colHeaders];

  var sums = {y0:0,y1:0,y2:0,y3:0,y4:0,mtd:0,today:0};
  document.querySelectorAll('#tblBody tr:not(.hidden)').forEach(function(tr) {
    var idx = parseInt(tr.getAttribute('data-idx'), 10);
    var r   = ROWS[idx];
    if (!r) return;
    ['y0','y1','y2','y3','y4','mtd','today'].forEach(function(k) { sums[k] += r[k]; });
    result.push([
      r.code, r.desc,
      r.y0.toFixed(2),
      r.y1.toFixed(2), calcPct(r.y1, r.y0),
      r.y2.toFixed(2), calcPct(r.y2, r.y1),
      r.y3.toFixed(2), calcPct(r.y3, r.y2),
      r.y4.toFixed(2), calcPct(r.y4, r.y3),
      r.mtd.toFixed(2), r.today.toFixed(2)
    ]);
  });

  result.push([
    'TOTAL', '',
    sums.y0.toFixed(2),
    sums.y1.toFixed(2), calcPct(sums.y1, sums.y0),
    sums.y2.toFixed(2), calcPct(sums.y2, sums.y1),
    sums.y3.toFixed(2), calcPct(sums.y3, sums.y2),
    sums.y4.toFixed(2), calcPct(sums.y4, sums.y3),
    sums.mtd.toFixed(2), sums.today.toFixed(2)
  ]);

  var csv  = '﻿' + result.map(function(row) { return row.map(csvField).join(','); }).join('\r\n');
  var blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
  var a    = document.createElement('a');
  a.href   = URL.createObjectURL(blob);
  var d = new Date();
  var ds = d.getFullYear() + ('0'+(d.getMonth()+1)).slice(-2) + ('0'+d.getDate()).slice(-2);
  a.download = 'CustClassSales5Yr_' + ds + '.csv';
  document.body.appendChild(a); a.click(); document.body.removeChild(a);
  URL.revokeObjectURL(a.href);
}

// ── Auto-refresh at 4:30 pm & 5:00 pm ET (M–F) ───────────────
(function() {
  var TARGETS   = [{h:16,m:30},{h:17,m:0}];
  var lastFired = null;
  var startMs   = null;

  function etNow() { return new Date(new Date().toLocaleString('en-US',{timeZone:'America/New_York'})); }

  function nextRefreshMs() {
    var et = etNow();
    for (var offset = 0; offset <= 7; offset++) {
      var d = new Date(et); d.setDate(d.getDate() + offset);
      if (d.getDay() === 0 || d.getDay() === 6) continue;
      for (var i = 0; i < TARGETS.length; i++) {
        var t = new Date(d); t.setHours(TARGETS[i].h, TARGETS[i].m, 0, 0);
        if (t > et) return t - et;
      }
    }
    return null;
  }

  function fmtCd(ms) {
    var tot = Math.max(0, Math.floor(ms / 1000));
    var d = Math.floor(tot / 86400);
    var h = Math.floor((tot % 86400) / 3600);
    var m = Math.floor((tot % 3600) / 60);
    var s = tot % 60;
    var mm = (m < 10 ? '0' : '') + m;
    var ss = (s < 10 ? '0' : '') + s;
    if (d > 0) return d + (d === 1 ? ' day ' : ' days ') + (h < 10 ? '0' : '') + h + ':' + mm + ':' + ss;
    if (h > 0) return h + ':' + mm + ':' + ss;
    return m + ':' + ss;
  }

  function tick() {
    var et   = etNow();
    var wd   = et.getDay();
    var h    = et.getHours();
    var m    = et.getMinutes();
    var key  = h * 100 + m;
    var isWd = wd >= 1 && wd <= 5;
    var isTgt = isWd && TARGETS.some(function(t) { return t.h === h && t.m === m; });

    if (isTgt && key !== lastFired) { lastFired = key; location.reload(); return; }
    if (!isTgt && lastFired === key) lastFired = null;

    var dotEl  = document.getElementById('cs5-dot');
    var statEl = document.getElementById('cs5-status');
    var fillEl = document.getElementById('cs5-prog');
    var cdEl   = document.getElementById('cs5-cd');
    var remMs  = nextRefreshMs();
    if (remMs !== null && startMs === null) startMs = remMs;

    if (isWd && remMs !== null) {
      dotEl.className    = 'refresh-dot';
      statEl.textContent = 'Live – auto-refreshes at 4:30 pm & 5:00 pm ET (M–F)';
      cdEl.textContent   = fmtCd(remMs);
      fillEl.style.width = (startMs ? Math.min(100, remMs / startMs * 100) : 100).toFixed(1) + '%';
    } else {
      dotEl.className    = 'refresh-dot refresh-dot--off';
      statEl.textContent = 'Auto-refresh off – outside M–F scheduled times. Use Refresh Now.';
      cdEl.textContent   = '–';
      fillEl.style.width = '0%';
    }
    setTimeout(tick, 1000);
  }
  tick();
}());

function triggerRefresh() {
  document.getElementById('cs5-dot').className      = 'refresh-dot';
  document.getElementById('cs5-status').textContent = 'Refreshing…';
  setTimeout(function() { location.reload(); }, 300);
}

// ── Init ───────────────────────────────────────────────────────
applyFilter();
</script>
</body>
</html>