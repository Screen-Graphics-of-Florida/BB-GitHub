<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$now  = new DateTime();
$yrSt = new DateTime($now->format('Y-01-01'));
$next = clone $now; $next->modify('+1 day');

function dateToCymd(DateTime $dt) {
    return ((int)$dt->format('Y') - 1900) * 10000
         + (int)$dt->format('m') * 100
         + (int)$dt->format('d');
}
$yrStCymd     = dateToCymd($yrSt);
$tomorrowCymd = dateToCymd($next);

$conn    = $i5Connect->getConnection();
$GOAL    = 18300000.00;
$dbError = '';

// Single query — order-level detail with class description and order date
$sql = "
    SELECT
        h.\"OEORD#\"                                                            AS ORDNUM,
        TRIM(h.OEORST)                                                          AS STATUS,
        h.OESHTO                                                                AS SHIPTO,
        COALESCE(TRIM(cust.CMCNA1), '')                                         AS CUSTNAME,
        COALESCE(TRIM(cust.CMCCLS), '')                                         AS CLSCODE,
        COALESCE(TRIM(cls.CCCCDS),  '')                                         AS CLSDESC,
        MIN(d.DHDTLI)                                                           AS ORDATE,
        SUM(CASE WHEN d.DHORUF <> 0
                 THEN d.DHSLPR * d.DHQSTC / d.DHORUF
                 ELSE 0 END)                                                    AS ORDAMT
    FROM SGHDSDATA.OEORDH d
    JOIN SGHDSDATA.OEORHD h         ON d.\"DHORD#\"  = h.\"OEORD#\"
    LEFT JOIN SGHDSDATA.HDCUST cust ON h.OESHTO      = cust.CMCUST
    LEFT JOIN SGHDSDATA.HDCCLS cls  ON cust.CMCCLS   = cls.CCCCLS
    WHERE d.\"DHSEQ#\" <> 0
      AND d.DHQSTC <> 0
      AND d.DHDTLI >= $yrStCymd
      AND d.DHDTLI <  $tomorrowCymd
    GROUP BY h.\"OEORD#\", h.OEORST, h.OESHTO,
             cust.CMCNA1, cust.CMCCLS, cls.CCCCDS
    ORDER BY cust.CMCCLS, ORDAMT DESC
";

$orderRows = array();
$ytdTotal  = 0.0;
$stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $code = trim((string)$r['CLSCODE']);
        if ($code === '') $code = '(none)';
        $amt = round((float)$r['ORDAMT'], 2);
        $ytdTotal += $amt;
        $orderRows[] = array(
            'ordNum'   => (int)$r['ORDNUM'],
            'status'   => trim((string)$r['STATUS']),
            'shipTo'   => (int)$r['SHIPTO'],
            'custName' => trim((string)$r['CUSTNAME']),
            'clsCode'  => $code,
            'clsDesc'  => trim((string)$r['CLSDESC']),
            'orDate'   => (int)$r['ORDATE'],
            'ordAmt'   => $amt,
        );
    }
    db2_free_stmt($stmt);
} else {
    $dbError = db2_stmt_errormsg();
}

$ytdTotal    = round($ytdTotal, 2);
$orderJson   = json_encode($orderRows);
$refreshedAt = $now->format('g:i:s A');
$yearLabel   = $now->format('Y');
$eiBase      = 'https://portal.screen-graphics.com:5601';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Revenue vs Goal <?php echo $yearLabel; ?></title>
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
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Roboto Condensed', Arial, sans-serif;
    font-size: 13px; color: var(--hd-text);
    background: #edf1f7; display: flex; flex-direction: column;
    height: 100vh; overflow: hidden;
  }

  /* ── Top bar ─────────────────────────────────────────── */
  .topbar {
    background: var(--hd-blue); color: white;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 16px; height: 42px; flex-shrink: 0;
  }
  .topbar-logo { font-size: 15px; font-weight: 700; letter-spacing: .5px; font-family: 'Roboto Condensed', sans-serif; }
  .topbar-logo span { color: #6db3ff; }
  .topbar-right { display: flex; align-items: center; gap: 20px; font-size: 12px; color: #b8cfee; }
  .topbar-right a { color: #b8cfee; text-decoration: none; }

  .layout { display: flex; flex: 1; overflow: hidden; }

  /* ── Left nav ────────────────────────────────────────── */
  .nav { width: var(--hd-nav-width); background: var(--hd-nav-bg); flex-shrink: 0; overflow-y: auto; }
  .nav-section { padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,.1); }
  .nav-item { display: block; padding: 6px 14px; color: #cde0ff; font-size: 12px; text-decoration: none; white-space: nowrap; }
  .nav-item:hover { background: rgba(255,255,255,.12); color: white; }
  .nav-item.active { background: rgba(255,255,255,.2); color: white; font-weight: 700; border-left: 3px solid #6db3ff; padding-left: 11px; }
  .nav-header { padding: 8px 14px 4px; color: #89afd4; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
  .nw-badge { display: inline-block; background: rgba(109,179,255,.25); color: #9ecfff; font-size: 9px; padding: 1px 4px; border-radius: 2px; margin-left: 4px; }

  /* ── Main column ─────────────────────────────────────── */
  .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

  .page-header {
    background: linear-gradient(to bottom, #ece9d8, #d4d0c8);
    border-bottom: 2px solid #888; padding: 6px 12px;
    display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
  }
  .page-title { font-size: 14px; font-weight: 700; color: #003; font-family: 'Roboto Condensed', sans-serif; }
  .page-meta  { font-size: 11px; color: var(--hd-muted); }
  .btn {
    font-size: 12px; padding: 3px 10px; border-radius: 3px; cursor: pointer;
    border: 1px solid var(--hd-border); display: inline-flex; align-items: center; gap: 4px;
    background: white; color: var(--hd-text);
  }
  .btn:hover { background: #f0f4fa; }

  /* ── Refresh bar ─────────────────────────────────────── */
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

  /* ── Dashboard body ──────────────────────────────────── */
  .dash-body {
    flex: 1; overflow-y: auto; padding: 16px;
    background: linear-gradient(160deg, #dbd8cc 0%, #c8c4bc 100%);
  }
  .content-wrap { width: 100%; max-width: 980px; margin: 0 auto; }

  /* ── Win98 panel ─────────────────────────────────────── */
  .panel { background: #fff; border: 2px solid; border-color: #fff #888 #888 #fff; box-shadow: 1px 1px 0 #000; }
  .panel-title { background: #000080; color: white; font-size: 11px; font-weight: 700; padding: 2px 6px; font-family: 'Roboto Condensed', sans-serif; letter-spacing: .5px; }

  /* ── Combined card sections ──────────────────────────── */
  .rv-top-row {
    display: flex; gap: 0; border-bottom: 1px solid #ccc;
  }
  .rv-lcd-section {
    flex: 2; padding: 10px 14px 12px;
    border-right: 1px solid #ccc;
  }
  .rv-goal-section { flex: 3; padding: 10px 14px 12px; }

  /* ── LCD display ─────────────────────────────────────── */
  .lcd-display {
    background: #001a22; border: 3px inset #666; border-radius: 4px;
    padding: 14px 16px; text-align: center;
    font-family: 'Share Tech Mono', monospace; letter-spacing: 2px;
    position: relative; overflow: hidden;
    box-shadow: 0 0 16px rgba(0,200,255,.3) inset;
  }
  .lcd-display::before {
    content: ''; position: absolute; inset: 0;
    background: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(0,0,0,.08) 3px, rgba(0,0,0,.08) 4px);
    pointer-events: none;
  }
  .lcd-val { font-size: 38px; font-weight: 400; line-height: 1; color: #00e5ff; text-shadow: 0 0 10px rgba(0,200,255,.9); }
  .lcd-sublabel { font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; font-family: 'Roboto Condensed', sans-serif; margin-top: 7px; color: #00aacc; text-align: center; }

  /* ── Goal section ────────────────────────────────────── */
  .goal-hdr-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px; }
  .goal-k { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #555; font-family: 'Roboto Condensed', sans-serif; }
  .goal-v { font-size: 22px; font-weight: 700; color: #003087; font-family: 'Share Tech Mono', monospace; margin-bottom: 6px; }
  .divider { border: none; border-top: 1px solid #e0e0e0; margin: 6px 0; }
  .prog-lbl { font-size: 11px; color: #555; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
  .prog-track { background: #ddd; border-radius: 6px; height: 28px; overflow: hidden; border: 1px solid #bbb; position: relative; }
  .prog-fill { height: 100%; border-radius: 5px; position: relative; overflow: hidden; transition: width .4s ease, background .4s ease; }
  .prog-fill::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 50%; background: rgba(255,255,255,.18); border-radius: 5px 5px 0 0; }
  .prog-pct-text { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; font-weight: 700; font-family: 'Roboto Condensed', sans-serif; color: white; text-shadow: 0 1px 3px rgba(0,0,0,.6); pointer-events: none; }
  .bottom-row { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; }
  .remaining-block .lbl { font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 1px; }
  .remaining-block .val { font-size: 16px; font-weight: 700; font-family: 'Share Tech Mono', monospace; }
  .remaining-block .val.under { color: #b91c1c; }
  .remaining-block .val.over  { color: #1a7a3c; }
  .pct-badge { display: inline-block; padding: 5px 16px; border-radius: 20px; font-size: 18px; font-weight: 700; font-family: 'Roboto Condensed', sans-serif; transition: background .4s ease; }

  /* ── Filter row ──────────────────────────────────────── */
  .rv-filter-row {
    display: flex; align-items: center; gap: 10px;
    padding: 6px 14px; background: #f0f0f0;
    border-bottom: 1px solid #ccc;
  }
  .filter-lbl { font-size: 12px; font-weight: 700; color: #333; }
  .filter-sel {
    font-size: 12px; padding: 3px 8px; border: 1px solid #bbb;
    border-radius: 3px; background: white; color: var(--hd-text); font-family: 'Roboto Condensed', sans-serif;
  }
  .btn-clear {
    font-size: 11px; padding: 2px 8px; background: #fff0e8;
    border-color: #d08060; color: #8b3010;
  }
  .btn-clear:hover { background: #ffe0c8; }
  .btn-clear:disabled { background: #f0f0f0; border-color: #ccc; color: #aaa; cursor: default; }
  .btn-export {
    margin-left: auto;
    font-size: 13px; font-weight: 700; padding: 5px 18px;
    background: #1a7a3c; border-color: #0a4a1c; color: white;
    border-radius: 4px;
  }
  .btn-export:hover { background: #0a5a2a; }
  .row-count-badge {
    font-size: 12px; font-style: italic; color: #555;
    background: none; border: none; padding: 0; font-weight: 400;
  }

  /* ── Breakdown: pie + table ──────────────────────────── */
  .rv-breakdown {
    display: flex; gap: 0; align-items: flex-start;
  }
  .rv-pie-wrap {
    flex-shrink: 0; padding: 14px 12px 14px 14px;
    border-right: 1px solid #e0e0e0;
  }
  .rv-tbl-wrap { flex: 1; overflow-x: auto; }

  /* ── Shared table styles ─────────────────────────────── */
  table.dtbl { width: 100%; border-collapse: collapse; font-size: 12px; font-family: 'Roboto Condensed', sans-serif; }
  table.dtbl thead th { background: #000080; color: white; padding: 4px 10px; text-align: right; font-size: 11px; font-weight: 700; white-space: nowrap; }
  table.dtbl thead th:first-child { text-align: left; }
  table.dtbl tbody tr:nth-child(even) { background: #f0f0f0; }
  table.dtbl tbody tr:hover { background: #ddeeff; }
  table.dtbl tbody td { padding: 5px 10px; text-align: right; white-space: nowrap; }
  table.dtbl tbody td:first-child { text-align: left; }
  table.dtbl tfoot td { background: #ddd; font-weight: 700; padding: 5px 10px; text-align: right; border-top: 2px solid #888; }
  table.dtbl tfoot td:first-child { text-align: left; }

  .rev-link { color: #0055b3; text-decoration: underline; cursor: pointer; font-variant-numeric: tabular-nums; }
  .rev-link:hover { color: #003087; background: #e8f0ff; border-radius: 2px; }

  /* ── Shared modal base ───────────────────────────────── */
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); align-items: center; justify-content: center; }
  .modal-overlay.open { display: flex; }
  .modal { background: #fff; border: 2px solid; border-color: #fff #888 #888 #fff; box-shadow: 4px 4px 0 #000; max-width: 95vw; max-height: 88vh; display: flex; flex-direction: column; }
  .modal-hdr { background: #000080; color: white; padding: 3px 8px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
  .modal-hdr-title { font-size: 12px; font-weight: 700; letter-spacing: .5px; font-family: 'Roboto Condensed', sans-serif; }
  .modal-x { background: #d4d0c8; border: 2px solid; border-color: #fff #808080 #808080 #fff; color: #000; font-size: 11px; font-weight: 700; cursor: pointer; padding: 0 6px; height: 18px; line-height: 14px; font-family: monospace; }
  .modal-x:hover { background: #c0b8b0; }

  /* ── Orders drilldown modal ──────────────────────────── */
  #ordModal { z-index: 1100; }
  #ordModal .modal { width: 960px; }
  .ord-body { padding: 10px 12px 12px; overflow-y: auto; flex: 1; }
  .ord-info-bar { font-size: 11px; color: #555; margin-bottom: 8px; font-style: italic; }
  .ord-link { color: #0055b3; text-decoration: none; cursor: pointer; font-weight: 700; font-variant-numeric: tabular-nums; border-bottom: 1px dotted #0055b3; }
  .ord-link:hover { color: #003087; border-bottom-style: solid; }
  .cust-link { color: #1a7a3c; text-decoration: none; cursor: pointer; font-weight: 700; font-variant-numeric: tabular-nums; border-bottom: 1px dotted #1a7a3c; }
  .cust-link:hover { color: #0a4a1c; border-bottom-style: solid; }
  .badge-open   { display: inline-block; background: #1a7a3c; color: white; font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 3px; margin-left: 5px; vertical-align: middle; }
  .badge-closed { display: inline-block; background: #64748b; color: white; font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 3px; margin-left: 5px; vertical-align: middle; }

  .btn-set-goal-inline {
    background: #003087; color: white; border-color: #001a4d;
    font-size: 12px; font-weight: 700; padding: 4px 14px;
    border-radius: 4px; white-space: nowrap; align-self: center;
  }
  .btn-set-goal-inline:hover { background: #0055b3; }

  /* ── Set Goal modal ──────────────────────────────────── */
  #goalModal { z-index: 1200; }
  #goalModal .modal { width: 380px; }
  .goal-modal-body { padding: 20px; }
  .goal-modal-body p { font-size: 12px; color: #555; margin-bottom: 12px; }
  .goal-input-row { display: flex; gap: 8px; align-items: center; }
  .goal-dollar { font-size: 20px; font-weight: 700; color: #003087; }
  .goal-input {
    flex: 1; padding: 6px 10px; font-size: 18px;
    font-family: 'Share Tech Mono', monospace;
    border: 2px inset #999; border-radius: 2px; text-align: right;
    color: #003087;
  }
  .goal-modal-btns { display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px; }
  .btn-primary { background: #0055b3; color: white; border-color: #003087; font-weight: 700; }
  .btn-primary:hover { background: #003087; }

  /* ── Footer ──────────────────────────────────────────── */
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
      <a class="nav-item active" href="#">Revenue vs Goal <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Management/NewAccountsRevenue.php" target="_blank">New Acct Rev vs Growth <span class="nw-badge">&#8599;</span></a>
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
        <div class="page-title">Revenue vs Goal &mdash; <?php echo $yearLabel; ?></div>
        <div class="page-meta">SG Management &rsaquo; Order Entry &nbsp;&bull;&nbsp; Goal: $18,300,000</div>
      </div>
      <div>
        <button class="btn" onclick="triggerRefresh()">&#8635; Refresh Now</button>
      </div>
    </div>

    <div class="refresh-bar">
      <div class="refresh-dot" id="rvg-dot"></div>
      <span id="rvg-status">Checking...</span>
      <div class="refresh-progress"><div class="refresh-fill" id="rvg-prog" style="width:100%"></div></div>
      <span>Next refresh: <strong id="rvg-cd">&ndash;</strong></span>
      <span class="refresh-pill">Last refresh: <strong><?php echo $refreshedAt; ?></strong></span>
      <span class="refresh-pill" style="background:#fff0d0;border-color:#f0c060;color:#885500;">As of: <?php echo $now->format('D, M j, Y'); ?></span>
    </div>

    <?php if ($dbError): ?>
    <div style="background:#5a1010;color:#ffaaaa;padding:7px 14px;font-size:12px;font-family:monospace;">
      Query error: <?php echo htmlspecialchars($dbError); ?>
    </div>
    <?php endif; ?>

    <div class="dash-body">
      <div class="content-wrap">

        <div class="panel">
          <div class="panel-title">&#9632; <?php echo $yearLabel; ?> REVENUE vs GOAL</div>

          <!-- Top row: LCD + Goal -->
          <div class="rv-top-row">

            <div class="rv-lcd-section">
              <div class="lcd-display">
                <div class="lcd-val" id="lcdVal"><?php echo '$' . number_format($ytdTotal, 2); ?></div>
              </div>
              <div class="lcd-sublabel" id="lcdSublabel">YEAR-TO-DATE REVENUE</div>
            </div>

            <div class="rv-goal-section">
              <div class="goal-k" id="goalLabel">Annual Goal</div>
              <div class="goal-hdr-row">
                <div class="goal-v" id="goalVal"></div>
                <button class="btn btn-set-goal-inline" onclick="openGoalModal()">&#9660; Set Goal</button>
              </div>
              <hr class="divider">
              <div class="prog-lbl">Progress to Goal</div>
              <div class="prog-track">
                <div class="prog-fill" id="progFill" style="width:0%"></div>
                <span class="prog-pct-text" id="progPct"></span>
              </div>
              <div class="bottom-row">
                <div class="remaining-block">
                  <div class="lbl" id="remLabel">Remaining</div>
                  <div class="val under" id="remVal"></div>
                </div>
                <div class="pct-badge" id="pctBadge"></div>
              </div>
            </div>

          </div><!-- /rv-top-row -->

          <!-- Filter row -->
          <div class="rv-filter-row">
            <span class="filter-lbl">Month:</span>
            <select class="filter-sel" id="monthFilter" onchange="applyFilters()">
              <option value="">&#8212; All Months &#8212;</option>
            </select>
            <button class="btn btn-clear" id="clearBtn" disabled onclick="clearFilters()">&#x2715; Clear</button>
            <span class="row-count-badge" id="rowCount">0 classes shown</span>
            <button class="btn btn-export" onclick="exportExcel()">&#8595; Export to Excel</button>
          </div>

          <!-- Breakdown: pie + table -->
          <div class="rv-breakdown">
            <div class="rv-pie-wrap">
              <canvas id="pieChart" width="230" height="230"></canvas>
            </div>
            <div class="rv-tbl-wrap">
              <table class="dtbl">
                <thead>
                  <tr>
                    <th style="text-align:left">Class Code</th>
                    <th style="text-align:left">Description</th>
                    <th title="Click $ to see orders for that class">Revenue &#9660; (click to drill)</th>
                    <th>% of Total</th>
                  </tr>
                </thead>
                <tbody id="clsBody"></tbody>
                <tfoot id="clsFoot"></tfoot>
              </table>
            </div>
          </div>

        </div><!-- /panel -->

      </div><!-- /content-wrap -->
    </div><!-- /dash-body -->

    <div class="page-footer">
      <span>SGHDSDATA/OEORDH &rarr; OEORHD &rarr; HDCUST (CMCCLS) &rarr; HDCCLS (CCCCDS) &mdash; DHSLPR &times; DHQSTC &divide; DHORUF &mdash; YTD <?php echo $yrSt->format('M j, Y'); ?> &ndash; <?php echo $now->format('M j, Y'); ?></span>
      <span><?php echo $now->format('m/d/Y') . '  ' . $refreshedAt; ?></span>
    </div>
  </main>
</div>

<!-- ═══════════════════════════════════════════════════
     Modal: Orders drilldown for one class code
     ═══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="ordModal">
  <div class="modal">
    <div class="modal-hdr">
      <span class="modal-hdr-title" id="ordModalTitle">&#9632; YTD Shipped Orders</span>
      <button class="modal-x" onclick="closeOrdModal()">&#x2715;</button>
    </div>
    <div class="ord-body">
      <div class="ord-info-bar" id="ordInfoBar"></div>
      <div style="overflow-x:auto">
        <table class="dtbl">
          <thead>
            <tr>
              <th style="text-align:left">Order #</th>
              <th>Ship-To #</th>
              <th style="text-align:left">Customer</th>
              <th>Shipped Revenue</th>
            </tr>
          </thead>
          <tbody id="ordBody"></tbody>
          <tfoot id="ordFoot"></tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════
     Modal: Set Goal
     ═══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="goalModal">
  <div class="modal">
    <div class="modal-hdr">
      <span class="modal-hdr-title">&#9632; Set Annual Revenue Goal</span>
      <button class="modal-x" onclick="closeGoalModal()">&#x2715;</button>
    </div>
    <div class="goal-modal-body">
      <p>Enter the annual revenue goal. The monthly goal is calculated as Annual &divide; 12 when a month filter is active.</p>
      <div class="goal-input-row">
        <span class="goal-dollar">$</span>
        <input type="text" class="goal-input" id="goalInput" placeholder="18,300,000.00"
               onkeydown="if(event.key==='Enter')saveGoal()">
      </div>
      <div class="goal-modal-btns">
        <button class="btn" onclick="closeGoalModal()">Cancel</button>
        <button class="btn btn-primary" onclick="saveGoal()">Save Goal</button>
      </div>
    </div>
  </div>
</div>

<script>
// ── Server data ───────────────────────────────────────────────────────
var ORDER_DATA  = <?php echo $orderJson; ?>;
var EI_BASE     = <?php echo json_encode($eiBase); ?>;
var EI_EID      = <?php echo json_encode($eID); ?>;
var PHP_GOAL    = <?php echo json_encode($GOAL); ?>;

// Load saved goal from localStorage, fall back to PHP default
var ANNUAL_GOAL = (function() {
  var s = localStorage.getItem('rvg_annual_goal');
  return (s && parseFloat(s) > 0) ? parseFloat(s) : PHP_GOAL;
}());

var MONTH_NAMES = ['','January','February','March','April','May','June',
                   'July','August','September','October','November','December'];

var PIE_COLORS = [
  '#003087','#0055b3','#1a7a3c','#b45309','#8b0000',
  '#5b21b6','#0e7490','#9a3412','#1e40af','#4d7c0f',
  '#7c3aed','#92400e'
];

var currentMonth     = '';   // '' = all, '1'–'12' = specific month
var currentClassData = [];   // [{code, desc, amt}] aggregated for active filter
var currentTotal     = 0;
var ORD_VIEW         = [];   // orders shown in drilldown modal
var pieInst          = null;

// ── Utilities ─────────────────────────────────────────────────────────
function fmtMoney(n) {
  return '$' + Math.abs(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}

// Extract month (1–12) from CYMD date (e.g. 1260625 → 6)
function cymdMonth(cymd) {
  return Math.floor((cymd % 10000) / 100);
}

// ── Aggregation ───────────────────────────────────────────────────────
function getFilteredOrders() {
  if (!currentMonth) return ORDER_DATA;
  var m = parseInt(currentMonth, 10);
  return ORDER_DATA.filter(function(r) { return cymdMonth(r.orDate) === m; });
}

function aggregateClasses(orders) {
  var map = {};
  orders.forEach(function(r) {
    if (!map[r.clsCode]) map[r.clsCode] = {code: r.clsCode, desc: r.clsDesc, amt: 0};
    map[r.clsCode].amt += r.ordAmt;
  });
  return Object.values(map).sort(function(a, b) { return b.amt - a.amt; });
}

// ── Month dropdown ────────────────────────────────────────────────────
function buildMonthOptions() {
  var months = {};
  ORDER_DATA.forEach(function(r) {
    var m = cymdMonth(r.orDate);
    if (m >= 1 && m <= 12) months[m] = true;
  });
  var sel = document.getElementById('monthFilter');
  Object.keys(months).map(Number).sort(function(a, b) { return a - b; }).forEach(function(m) {
    var opt = document.createElement('option');
    opt.value = m;
    opt.textContent = MONTH_NAMES[m];
    sel.appendChild(opt);
  });
}

// ── Apply / clear filters ─────────────────────────────────────────────
function applyFilters() {
  currentMonth = document.getElementById('monthFilter').value;
  document.getElementById('clearBtn').disabled = !currentMonth;

  var orders       = getFilteredOrders();
  currentClassData = aggregateClasses(orders);
  currentTotal     = currentClassData.reduce(function(s, r) { return s + r.amt; }, 0);

  updateLCD();
  updateGoalPanel();
  buildClsTable();
  buildPieChart();
  document.getElementById('rowCount').textContent =
    currentClassData.length + ' class' + (currentClassData.length !== 1 ? 'es' : '') + ' shown';
}

function clearFilters() {
  document.getElementById('monthFilter').value = '';
  applyFilters();
}

// ── LCD ───────────────────────────────────────────────────────────────
function updateLCD() {
  document.getElementById('lcdVal').textContent = fmtMoney(currentTotal);
  document.getElementById('lcdSublabel').textContent = currentMonth
    ? MONTH_NAMES[parseInt(currentMonth, 10)].toUpperCase() + ' REVENUE'
    : 'YEAR-TO-DATE REVENUE';
}

// ── Goal panel ────────────────────────────────────────────────────────
function goalColors(pct) {
  if (pct >= 100) return {bar:'linear-gradient(90deg,#1a7a3c,#16a34a)', bg:'#1a7a3c'};
  if (pct >=  75) return {bar:'linear-gradient(90deg,#0055b3,#2563eb)', bg:'#0055b3'};
  if (pct >=  50) return {bar:'linear-gradient(90deg,#b45309,#d97706)', bg:'#b45309'};
  return              {bar:'linear-gradient(90deg,#b91c1c,#dc2626)',    bg:'#b91c1c'};
}

function updateGoalPanel() {
  var target = currentMonth ? ANNUAL_GOAL / 12 : ANNUAL_GOAL;
  var pct    = target > 0 ? Math.round(currentTotal / target * 1000) / 10 : 0;
  var rem    = target - currentTotal;
  var c      = goalColors(pct);

  document.getElementById('goalLabel').textContent = currentMonth
    ? 'Monthly Goal (' + MONTH_NAMES[parseInt(currentMonth, 10)] + ')'
    : 'Annual Goal';
  document.getElementById('goalVal').textContent  = fmtMoney(target);
  document.getElementById('progFill').style.width      = Math.min(100, pct) + '%';
  document.getElementById('progFill').style.background = c.bar;
  document.getElementById('progPct').textContent       = pct + '%';
  document.getElementById('remLabel').textContent      = rem >= 0 ? 'Remaining' : 'Over Goal';
  document.getElementById('remVal').textContent        = (rem >= 0 ? '-$' : '+$')
    + Math.abs(rem).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
  document.getElementById('remVal').className          = 'val ' + (rem >= 0 ? 'under' : 'over');
  document.getElementById('pctBadge').textContent      = pct + '% Complete';
  document.getElementById('pctBadge').style.background = c.bg;
  document.getElementById('pctBadge').style.color      = 'white';
}

// ── Class code table ──────────────────────────────────────────────────
function buildClsTable() {
  var tbody = document.getElementById('clsBody');
  tbody.innerHTML = '';
  currentClassData.forEach(function(r, i) {
    var pct   = currentTotal > 0 ? (r.amt / currentTotal * 100).toFixed(1) : '0.0';
    var color = PIE_COLORS[i % PIE_COLORS.length];
    var tr    = document.createElement('tr');
    tr.innerHTML =
      '<td style="display:flex;align-items:center;gap:6px;">'
        + '<span style="display:inline-block;width:11px;height:11px;border-radius:50%;background:'
        + color + ';flex-shrink:0"></span>'
        + '<strong>' + (r.code || '(none)') + '</strong></td>'
      + '<td style="text-align:left;color:#444">' + (r.desc || '&ndash;') + '</td>'
      + '<td><span class="rev-link" onclick="openOrdModal(' + i + ')" '
        + 'title="Click to see orders for ' + (r.code || '') + '">'
        + fmtMoney(r.amt) + '</span></td>'
      + '<td>' + pct + '%</td>';
    tbody.appendChild(tr);
  });
  document.getElementById('clsFoot').innerHTML =
    '<tr><td>TOTAL</td><td></td><td>' + fmtMoney(currentTotal) + '</td><td>100.0%</td></tr>';
}

// ── Pie chart ─────────────────────────────────────────────────────────
function buildPieChart() {
  if (pieInst) { pieInst.destroy(); pieInst = null; }
  if (!currentClassData.length) return;
  pieInst = new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
      labels: currentClassData.map(function(r) {
        return r.desc ? (r.code + ': ' + r.desc) : (r.code || '(none)');
      }),
      datasets: [{
        data: currentClassData.map(function(r) { return r.amt; }),
        backgroundColor: currentClassData.map(function(_, i) { return PIE_COLORS[i % PIE_COLORS.length]; }),
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: false,
      plugins: {
        legend: {display: false},
        tooltip: {
          callbacks: {
            label: function(ctx) {
              var pct = currentTotal > 0 ? (ctx.raw / currentTotal * 100).toFixed(1) : '0.0';
              return ' ' + fmtMoney(ctx.raw) + '  (' + pct + '%)';
            }
          }
        }
      }
    }
  });
}

// ── Orders drilldown modal ────────────────────────────────────────────
function openOrdModal(classIdx) {
  var cls = currentClassData[classIdx];
  if (!cls) return;

  var orders = getFilteredOrders();
  ORD_VIEW = orders.filter(function(o) { return o.clsCode === cls.code; });

  var label = cls.desc ? cls.code + ': ' + cls.desc : cls.code;
  var monthPart = currentMonth
    ? ' — ' + MONTH_NAMES[parseInt(currentMonth, 10)] + ' only'
    : ' — <?php echo $yrSt->format("M j, Y"); ?> through <?php echo $now->format("M j, Y"); ?>';
  document.getElementById('ordModalTitle').textContent = '■ Shipped Orders — Class: ' + label;
  document.getElementById('ordInfoBar').textContent =
    ORD_VIEW.length + ' order' + (ORD_VIEW.length !== 1 ? 's' : '')
    + ' with invoiced revenue' + monthPart + ' — click an order # to open it';

  buildOrdTable();
  document.getElementById('ordModal').classList.add('open');
}

function closeOrdModal() { document.getElementById('ordModal').classList.remove('open'); }
document.getElementById('ordModal').addEventListener('click', function(e) { if (e.target === this) closeOrdModal(); });

function buildOrdTable() {
  var tbody = document.getElementById('ordBody');
  tbody.innerHTML = '';
  var total = 0;
  ORD_VIEW.forEach(function(r, idx) {
    total += r.ordAmt;
    var badge = r.status === 'A'
      ? '<span class="badge-open">OPEN</span>'
      : '<span class="badge-closed">CLOSED</span>';
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td><span class="ord-link" onclick="openOrderLink(' + idx + ')" '
        + 'title="Open order ' + r.ordNum + ' in Harris EIP">'
        + r.ordNum + '</span>' + badge + '</td>'
      + '<td style="text-align:center"><span class="cust-link" onclick="openCustLink(' + idx + ')" '
        + 'title="Open customer ' + r.shipTo + ' in Harris EIP">'
        + r.shipTo + '</span></td>'
      + '<td style="text-align:left">' + (r.custName || '') + '</td>'
      + '<td>' + fmtMoney(r.ordAmt) + '</td>';
    tbody.appendChild(tr);
  });
  document.getElementById('ordFoot').innerHTML =
    '<tr><td colspan="3">TOTAL (' + ORD_VIEW.length + ' orders)</td>'
    + '<td>' + fmtMoney(total) + '</td></tr>';
}

// Row-index link pattern
function openOrderLink(idx) {
  var r = ORD_VIEW[idx]; if (!r) return;
  var url;
  if (r.status === 'A') {
    url = EI_BASE + '/harris-CGI/SelectOrder.d2w/REPORT'
      + '?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
      + '&eID=' + EI_EID
      + '&customerName='   + encodeURIComponent(r.custName)
      + '&customerNumber=' + encodeURIComponent(r.shipTo)
      + '&orderNumber='    + encodeURIComponent(r.ordNum);
  } else {
    url = EI_BASE + '/harris-CGI/SelectOrderHistory.d2w/REPORT'
      + '?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
      + '&eID=' + EI_EID
      + '&customerName='   + encodeURIComponent(r.custName)
      + '&customerNumber=' + encodeURIComponent(r.shipTo)
      + '&orderNumber='    + encodeURIComponent(r.ordNum)
      + '&orderSequence=0';
  }
  window.open(url, '_blank', 'width=1300,height=750,scrollbars=yes,resizable=yes');
}

function openCustLink(idx) {
  var r = ORD_VIEW[idx]; if (!r) return;
  var url = EI_BASE + '/harris-CGI/CustomerSelect.d2w/REPORT'
    + '?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
    + '&eID=' + EI_EID
    + '&customerName='   + encodeURIComponent(r.custName)
    + '&customerNumber=' + encodeURIComponent(r.shipTo);
  window.open(url, '_blank', 'width=1300,height=750,scrollbars=yes,resizable=yes');
}

// ── Set Goal modal ────────────────────────────────────────────────────
function openGoalModal() {
  document.getElementById('goalInput').value =
    ANNUAL_GOAL.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
  document.getElementById('goalModal').classList.add('open');
  setTimeout(function() { document.getElementById('goalInput').select(); }, 50);
}

function closeGoalModal() { document.getElementById('goalModal').classList.remove('open'); }
document.getElementById('goalModal').addEventListener('click', function(e) { if (e.target === this) closeGoalModal(); });

function saveGoal() {
  var raw = document.getElementById('goalInput').value.replace(/[^0-9.]/g, '');
  var v   = parseFloat(raw);
  if (isNaN(v) || v <= 0) {
    document.getElementById('goalInput').style.borderColor = '#b91c1c';
    return;
  }
  document.getElementById('goalInput').style.borderColor = '';
  ANNUAL_GOAL = v;
  localStorage.setItem('rvg_annual_goal', v);
  closeGoalModal();
  applyFilters();
}

// ── Export to Excel ───────────────────────────────────────────────────
function exportExcel() {
  var monthLabel = currentMonth ? MONTH_NAMES[parseInt(currentMonth, 10)] : 'YTD';
  function csvField(v) {
    v = String(v == null ? '' : v);
    return (v.indexOf(',') >= 0 || v.indexOf('"') >= 0 || v.indexOf('\n') >= 0)
      ? '"' + v.replace(/"/g, '""') + '"' : v;
  }
  var rows = [['Class Code', 'Description', 'Revenue', '% of Total']];
  currentClassData.forEach(function(r) {
    var pct = currentTotal > 0 ? (r.amt / currentTotal * 100).toFixed(1) : '0.0';
    rows.push([r.code, r.desc, r.amt.toFixed(2), pct + '%']);
  });
  rows.push(['TOTAL', '', currentTotal.toFixed(2), '100.0%']);

  var csv  = '﻿' + rows.map(function(row) { return row.map(csvField).join(','); }).join('\r\n');
  var blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
  var a    = document.createElement('a');
  a.href   = URL.createObjectURL(blob);
  a.download = 'RevenueByClass_<?php echo $yearLabel; ?>_' + monthLabel + '.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(a.href);
}

// ── Auto-refresh scheduler ────────────────────────────────────────────
(function() {
  var TARGETS   = [{h:16, m:30}, {h:17, m:0}];
  var lastFired = null;
  var startMs   = null;

  function etNow() {
    return new Date(new Date().toLocaleString('en-US', {timeZone: 'America/New_York'}));
  }

  function nextRefreshMs() {
    var et = etNow();
    for (var offset = 0; offset <= 7; offset++) {
      var d = new Date(et);
      d.setDate(d.getDate() + offset);
      var wd = d.getDay();
      if (wd === 0 || wd === 6) continue;
      for (var i = 0; i < TARGETS.length; i++) {
        var t = new Date(d);
        t.setHours(TARGETS[i].h, TARGETS[i].m, 0, 0);
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
    var isTarget = isWd && TARGETS.some(function(t) { return t.h === h && t.m === m; });

    if (isTarget && key !== lastFired) { lastFired = key; location.reload(); return; }
    if (!isTarget && lastFired === key) lastFired = null;

    var dotEl  = document.getElementById('rvg-dot');
    var statEl = document.getElementById('rvg-status');
    var fillEl = document.getElementById('rvg-prog');
    var cdEl   = document.getElementById('rvg-cd');
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
  document.getElementById('rvg-dot').className      = 'refresh-dot';
  document.getElementById('rvg-status').textContent = 'Refreshing…';
  setTimeout(function() { location.reload(); }, 300);
}

// ── Init ──────────────────────────────────────────────────────────────
buildMonthOptions();
applyFilters();
</script>
</body>
</html>