<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$now      = new DateTime();
$curYear  = (int)$now->format('Y');
$prevYear = $curYear - 1;
$next     = clone $now; $next->modify('+1 day');

function dateToCymd(DateTime $dt) {
    return ((int)$dt->format('Y') - 1900) * 10000
         + (int)$dt->format('m') * 100
         + (int)$dt->format('d');
}

// Full prior year (for ranking customers)
$lyFYStart = ($prevYear - 1900) * 10000 + 101;   // e.g. 1250101
$lyFYEnd   = ($curYear  - 1900) * 10000 + 101;   // e.g. 1260101 (exclusive)

// YTD prior year — same calendar period as this year (for comparison)
$lyYTDEndDt = new DateTime($prevYear . '-' . $now->format('m') . '-' . $now->format('d'));
$lyYTDEndDt->modify('+1 day');
$lyYTDEnd = dateToCymd($lyYTDEndDt);             // e.g. 1250627

// Current YTD
$tyStart  = ($curYear - 1900) * 10000 + 101;     // e.g. 1260101
$tyEnd    = dateToCymd($next);                    // e.g. 1260627

$lyPeriodLabel = 'Jan 1–' . $now->format('M j') . ', ' . $prevYear;
$tyPeriodLabel = 'Jan 1–' . $now->format('M j, Y');

$conn    = $i5Connect->getConnection();
$dbError = '';
$allRows = array();

$sql = "
    WITH AllCustRev AS (
        SELECT
            h.OESHTO                                           AS CUST,
            SUM(CASE WHEN d.DHDTLI >= $lyFYStart
                          AND d.DHDTLI <  $lyFYEnd
                          AND d.DHORUF <> 0
                     THEN d.DHSLPR * d.DHQSTC / d.DHORUF
                     ELSE 0 END)                               AS REV_LY_FULL,
            SUM(CASE WHEN d.DHDTLI >= $lyFYStart
                          AND d.DHDTLI <  $lyYTDEnd
                          AND d.DHORUF <> 0
                     THEN d.DHSLPR * d.DHQSTC / d.DHORUF
                     ELSE 0 END)                               AS REV_LY_YTD,
            SUM(CASE WHEN d.DHDTLI >= $tyStart
                          AND d.DHDTLI <  $tyEnd
                          AND d.DHORUF <> 0
                     THEN d.DHSLPR * d.DHQSTC / d.DHORUF
                     ELSE 0 END)                               AS REV_TY
        FROM SGHDSDATA.OEORDH d
        JOIN SGHDSDATA.OEORHD h ON d.\"DHORD#\" = h.\"OEORD#\"
        WHERE d.\"DHSEQ#\" <> 0
          AND d.DHQSTC    <> 0
          AND d.DHDTLI    >= $lyFYStart
          AND d.DHDTLI    <  $tyEnd
        GROUP BY h.OESHTO
    ),
    RankedCust AS (
        SELECT
            CUST, REV_LY_FULL, REV_LY_YTD, REV_TY,
            ROW_NUMBER() OVER (ORDER BY REV_LY_FULL ASC, CUST ASC) AS RNK,
            COUNT(*)      OVER ()                                    AS TOTAL
        FROM AllCustRev
        WHERE REV_LY_FULL > 0
    )
    SELECT
        r.CUST,
        COALESCE(TRIM(c.CMCNA1), '')  AS CUSTNAME,
        COALESCE(TRIM(c.CMCCLS), '')  AS CLSCODE,
        COALESCE(c.CMSLSM, 0)         AS SLSMNUM,
        COALESCE(TRIM(s.SMSNA1), '')  AS SLSMNAME,
        r.REV_LY_FULL, r.REV_LY_YTD, r.REV_TY,
        r.RNK, r.TOTAL
    FROM RankedCust r
    LEFT JOIN SGHDSDATA.HDCUST  c ON r.CUST    = c.CMCUST
    LEFT JOIN SGHDSDATA.HDSLSM  s ON c.CMSLSM  = s.SMSLSM
    ORDER BY r.REV_LY_FULL ASC
";

$stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $allRows[] = array(
            'cust'      => (int)$r['CUST'],
            'custName'  => trim((string)$r['CUSTNAME']),
            'clsCode'   => trim((string)$r['CLSCODE']) ?: '(none)',
            'slsmNum'   => (int)$r['SLSMNUM'],
            'slsmName'  => trim((string)$r['SLSMNAME']),
            'revLyFull' => round((float)$r['REV_LY_FULL'], 2),
            'revLyYtd'  => round((float)$r['REV_LY_YTD'], 2),
            'revTy'     => round((float)$r['REV_TY'],     2),
            'rnk'       => (int)$r['RNK'],
            'total'     => (int)$r['TOTAL'],
        );
    }
    db2_free_stmt($stmt);
} else {
    $dbError = db2_stmt_errormsg();
}

// Compute bottom-half cutoff
$totalCusts = count($allRows) ? $allRows[0]['total'] : 0;
$halfCutoff = (int)floor($totalCusts / 2);

// Aggregate stats
$btmLY = 0.0; $btmTY = 0.0; $btmCount = 0;
$allLY = 0.0; $allTY = 0.0;

foreach ($allRows as $r) {
    $allLY += $r['revLyYtd'];
    $allTY += $r['revTy'];
    if ($r['rnk'] <= $halfCutoff) {
        $btmLY += $r['revLyYtd'];
        $btmTY += $r['revTy'];
        $btmCount++;
    }
}

$btmLY = round($btmLY, 2);
$btmTY = round($btmTY, 2);
$allLY = round($allLY, 2);
$allTY = round($allTY, 2);

$btmDelta = round($btmTY - $btmLY, 2);
$btmPct   = ($btmLY != 0) ? round(($btmDelta / $btmLY) * 100, 1) : 0;
$allDelta = round($allTY - $allLY, 2);
$allPct   = ($allLY != 0) ? round(($allDelta / $allLY) * 100, 1) : 0;

$refreshedAt = $now->format('g:i:s A');
$eiBase      = 'https://portal.screen-graphics.com:5601';
$custJson    = json_encode($allRows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bottom 50% Revenue Growth <?php echo $curYear; ?> vs <?php echo $prevYear; ?></title>
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

  /* ── Top bar ─────────────────────────────────────────────── */
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

  /* ── Left nav ────────────────────────────────────────────── */
  .nav { width: var(--hd-nav-width); background: var(--hd-nav-bg); flex-shrink: 0; overflow-y: auto; }
  .nav-section  { padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,.1); }
  .nav-item     { display: block; padding: 6px 14px; color: #cde0ff; font-size: 12px; text-decoration: none; white-space: nowrap; }
  .nav-item:hover { background: rgba(255,255,255,.12); color: white; }
  .nav-item.active { background: rgba(255,255,255,.2); color: white; font-weight: 700; border-left: 3px solid #6db3ff; padding-left: 11px; }
  .nav-header   { padding: 8px 14px 4px; color: #89afd4; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
  .nw-badge     { display: inline-block; background: rgba(109,179,255,.25); color: #9ecfff; font-size: 9px; padding: 1px 4px; border-radius: 2px; margin-left: 4px; }

  /* ── Main column ─────────────────────────────────────────── */
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

  /* ── Refresh bar ─────────────────────────────────────────── */
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

  /* ── Dashboard body ──────────────────────────────────────── */
  .dash-body {
    flex: 1; overflow-y: auto; padding: 16px;
    background: linear-gradient(160deg, #dbd8cc 0%, #c8c4bc 100%);
  }
  .content-wrap { width: 100%; max-width: 1080px; margin: 0 auto; }

  /* ── Win98 panel ─────────────────────────────────────────── */
  .panel { background: #fff; border: 2px solid; border-color: #fff #888 #888 #fff; box-shadow: 1px 1px 0 #000; margin-bottom: 14px; }
  .panel-title { background: #000080; color: white; font-size: 11px; font-weight: 700; padding: 2px 6px; font-family: 'Roboto Condensed', sans-serif; letter-spacing: .5px; }

  /* ── Summary metrics ─────────────────────────────────────── */
  .metrics-row { display: flex; gap: 0; border-bottom: 1px solid #ccc; }
  .metric-card { flex: 1; padding: 12px 16px; border-right: 1px solid #d0d0d0; position: relative; }
  .metric-card:last-child { border-right: none; }
  .metric-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-bottom: 4px; }
  .metric-val   { font-size: 26px; font-weight: 700; font-family: 'Share Tech Mono', monospace; color: #003087; }
  .metric-sub   { font-size: 10px; color: #888; margin-top: 2px; font-style: italic; }
  .metric-val.pos  { color: #1a7a3c; }
  .metric-val.neg  { color: #b91c1c; }
  .metric-val.neut { color: #64748b; }
  .delta-badge { display: inline-block; font-size: 13px; font-weight: 700; padding: 1px 8px; border-radius: 3px; margin-left: 6px; vertical-align: middle; }
  .delta-badge.pos { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
  .delta-badge.neg { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
  .delta-badge.neut{ background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

  .compare-row { display: flex; gap: 0; padding: 8px 16px; background: #f5f5f5; border-bottom: 1px solid #e0e0e0; font-size: 11px; color: #555; align-items: center; }
  .compare-lbl { font-weight: 700; color: #333; margin-right: 6px; }
  .compare-val { font-family: 'Share Tech Mono', monospace; font-weight: 700; margin: 0 4px; }
  .compare-sep { margin: 0 10px; color: #bbb; }

  /* ── Filter bar ──────────────────────────────────────────── */
  .filter-bar {
    display: flex; align-items: center; gap: 10px;
    padding: 6px 14px; background: #f0f0f0; border-bottom: 1px solid #ccc;
  }
  .filter-lbl { font-size: 12px; font-weight: 700; color: #333; }
  .filter-sel {
    font-size: 12px; padding: 3px 8px; border: 1px solid #bbb;
    border-radius: 3px; background: white; color: var(--hd-text); font-family: 'Roboto Condensed', sans-serif;
  }
  .btn-clear { font-size: 11px; padding: 2px 8px; background: #fff0e8; border-color: #d08060; color: #8b3010; }
  .btn-clear:hover { background: #ffe0c8; }
  .btn-clear:disabled { background: #f0f0f0; border-color: #ccc; color: #aaa; cursor: default; }
  .row-count-badge { font-size: 12px; font-style: italic; color: #555; background: none; border: none; padding: 0; }
  .btn-export {
    margin-left: auto; font-size: 13px; font-weight: 700; padding: 5px 18px;
    background: #1a7a3c; border-color: #0a4a1c; color: white; border-radius: 4px;
  }
  .btn-export:hover { background: #0a5a2a; }

  /* ── Table ───────────────────────────────────────────────── */
  .tbl-wrap { overflow-x: auto; }
  table.dtbl { width: 100%; border-collapse: collapse; font-size: 12px; font-family: 'Roboto Condensed', sans-serif; }
  table.dtbl thead th { background: #000080; color: white; padding: 4px 10px; text-align: right; font-size: 11px; font-weight: 700; white-space: nowrap; cursor: pointer; user-select: none; }
  table.dtbl thead th:first-child, table.dtbl thead th:nth-child(2) { text-align: left; }
  table.dtbl thead th.sorted-asc::after  { content: ' ↑'; }
  table.dtbl thead th.sorted-desc::after { content: ' ↓'; }
  table.dtbl tbody tr:nth-child(even) { background: #f0f0f0; }
  table.dtbl tbody tr:hover { background: #ddeeff; }
  table.dtbl tbody td { padding: 5px 10px; text-align: right; white-space: nowrap; }
  table.dtbl tbody td:first-child, table.dtbl tbody td:nth-child(2) { text-align: left; }
  table.dtbl tfoot td { background: #ddd; font-weight: 700; padding: 5px 10px; text-align: right; border-top: 2px solid #888; }
  table.dtbl tfoot td:first-child { text-align: left; }
  .chg-pos { color: #166534; font-weight: 700; }
  .chg-neg { color: #991b1b; font-weight: 700; }
  .chg-zero{ color: #64748b; }
  .cust-link { color: #0055b3; text-decoration: underline; cursor: pointer; }
  .cust-link:hover { color: #003087; }

  /* ── Footer ──────────────────────────────────────────────── */
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
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Management/NewAccountsRevenue.php">New Acct Revenue <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item active" href="#">Bottom 50% Growth <span class="nw-badge">&#8599;</span></a>
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
        <div class="page-title">Bottom 50% Customer Revenue Growth &mdash; <?php echo $prevYear; ?> vs <?php echo $curYear; ?></div>
        <div class="page-meta">SG Management &rsaquo; Customers ranked by <?php echo $prevYear; ?> full-year revenue &bull; Comparing <?php echo $prevYear; ?> YTD vs <?php echo $curYear; ?> YTD</div>
      </div>
      <div>
        <button class="btn" onclick="triggerRefresh()">&#8635; Refresh Now</button>
      </div>
    </div>

    <div class="refresh-bar">
      <div class="refresh-dot" id="rb-dot"></div>
      <span id="rb-status">Live</span>
      <div class="refresh-progress"><div class="refresh-fill" id="rb-prog" style="width:100%"></div></div>
      <span>Next refresh: <strong id="rb-cd">&ndash;</strong></span>
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

        <!-- ═══ Summary Panel ═══════════════════════════════ -->
        <div class="panel">
          <div class="panel-title">&#9632; BOTTOM 50% OF CUSTOMERS &mdash; REVENUE GROWTH (<?php echo $prevYear; ?> YTD vs <?php echo $curYear; ?> YTD)</div>

          <!-- Metric cards: bottom half -->
          <div class="metrics-row">
            <div class="metric-card">
              <div class="metric-label"><?php echo $prevYear; ?> YTD Revenue</div>
              <div class="metric-val" id="btmLY"><?php echo '$' . number_format($btmLY, 0); ?></div>
              <div class="metric-sub">Jan 1&ndash;<?php echo $now->format('M j'); ?>, <?php echo $prevYear; ?></div>
            </div>
            <div class="metric-card">
              <div class="metric-label"><?php echo $curYear; ?> YTD Revenue</div>
              <div class="metric-val" id="btmTY"><?php echo '$' . number_format($btmTY, 0); ?></div>
              <div class="metric-sub">Jan 1&ndash;<?php echo $now->format('M j, Y'); ?></div>
            </div>
            <div class="metric-card">
              <div class="metric-label">Change ($)</div>
              <?php $cls = $btmDelta > 0 ? 'pos' : ($btmDelta < 0 ? 'neg' : 'neut'); ?>
              <div class="metric-val <?php echo $cls; ?>" id="btmDelta">
                <?php echo ($btmDelta >= 0 ? '+' : '') . '$' . number_format($btmDelta, 0); ?>
              </div>
              <div class="metric-sub">vs same period <?php echo $prevYear; ?></div>
            </div>
            <div class="metric-card">
              <div class="metric-label">Change (%)</div>
              <?php $pctCls = $btmPct > 0 ? 'pos' : ($btmPct < 0 ? 'neg' : 'neut'); ?>
              <div class="metric-val <?php echo $pctCls; ?>" id="btmPct">
                <?php echo ($btmPct >= 0 ? '+' : '') . $btmPct . '%'; ?>
              </div>
              <div class="metric-sub">year-over-year</div>
            </div>
            <div class="metric-card">
              <div class="metric-label">Customers in Group</div>
              <div class="metric-val neut" id="btmCount"><?php echo number_format($btmCount); ?></div>
              <div class="metric-sub">of <?php echo number_format($totalCusts); ?> total with <?php echo $prevYear; ?> revenue</div>
            </div>
          </div>

          <!-- Context row: all customers -->
          <div class="compare-row">
            <span class="compare-lbl">All <?php echo number_format($totalCusts); ?> customers:</span>
            <span><?php echo $prevYear; ?> YTD</span>
            <span class="compare-val">$<?php echo number_format($allLY, 0); ?></span>
            <span>&rarr;</span>
            <span><?php echo $curYear; ?> YTD</span>
            <span class="compare-val">$<?php echo number_format($allTY, 0); ?></span>
            <span class="compare-sep">|</span>
            <?php $acls = $allDelta >= 0 ? 'chg-pos' : 'chg-neg'; ?>
            <span>Change:</span>
            <span class="compare-val <?php echo $acls; ?>">
              <?php echo ($allDelta >= 0 ? '+' : '') . '$' . number_format($allDelta, 0); ?>
              (<?php echo ($allPct >= 0 ? '+' : '') . $allPct; ?>%)
            </span>
          </div>
        </div>

        <!-- ═══ Detail Panel ════════════════════════════════ -->
        <div class="panel">
          <div class="panel-title">&#9632; BOTTOM 50% CUSTOMERS &mdash; DETAIL</div>

          <!-- Filter bar -->
          <div class="filter-bar">
            <span class="filter-lbl">Class:</span>
            <select class="filter-sel" id="clsFilter" onchange="applyFilter()">
              <option value="">&#8212; All Classes &#8212;</option>
            </select>
            <span class="filter-lbl">Salesperson:</span>
            <select class="filter-sel" id="slsmFilter" onchange="applyFilter()">
              <option value="">&#8212; All &#8212;</option>
            </select>
            <span class="filter-lbl">Change %:</span>
            <select class="filter-sel" id="pctOp" style="width:52px" onchange="applyFilter()">
              <option value="">&#8212;</option>
              <option value="eq">=</option>
              <option value="ge">&ge;</option>
              <option value="le">&le;</option>
            </select>
            <input type="number" class="filter-sel" id="pctVal" style="width:72px;text-align:right"
                   placeholder="%" step="0.1" oninput="applyFilter()">
            <button class="btn btn-clear" id="clearBtn" disabled onclick="clearFilter()">&#x2715; Clear</button>
            <span class="row-count-badge" id="rowCount">0 customers</span>
            <button class="btn btn-export" onclick="exportExcel()">&#8595; Export to Excel</button>
          </div>

          <!-- Table -->
          <div class="tbl-wrap">
            <table class="dtbl">
              <thead>
                <tr>
                  <th onclick="sortBy('cust')"     title="Customer #">Cust #</th>
                  <th onclick="sortBy('custName')" title="Customer Name">Name</th>
                  <th onclick="sortBy('clsCode')"  title="Class Code">Class</th>
                  <th onclick="sortBy('slsmNum')"  title="Salesperson #">Slsm #</th>
                  <th onclick="sortBy('slsmName')" title="Salesperson Name">Salesperson</th>
                  <th onclick="sortBy('revLyFull')" title="Full-year <?php echo $prevYear; ?> (used for ranking)"><?php echo $prevYear; ?> Full Year</th>
                  <th onclick="sortBy('revLyYtd')"  title="YTD <?php echo $prevYear; ?> — Jan 1&ndash;<?php echo $now->format('M j'); ?>"><?php echo $prevYear; ?> YTD</th>
                  <th onclick="sortBy('revTy')"    title="YTD <?php echo $curYear; ?> — Jan 1&ndash;<?php echo $now->format('M j'); ?>"><?php echo $curYear; ?> YTD</th>
                  <th onclick="sortBy('delta')"    title="Change ($)">Change $</th>
                  <th onclick="sortBy('pct')"      title="Change (%)">Change %</th>
                </tr>
              </thead>
              <tbody id="custBody"></tbody>
              <tfoot id="custFoot"></tfoot>
            </table>
          </div>
        </div>

      </div><!-- /content-wrap -->
    </div><!-- /dash-body -->

    <div class="page-footer">
      <span>SGHDSDATA/OEORDH &rarr; OEORHD &rarr; HDCUST &mdash; DHSLPR &times; DHQSTC &divide; DHORUF &mdash;
        Ranked by <?php echo $prevYear; ?> full year &bull;
        Comparing Jan 1&ndash;<?php echo $now->format('M j'); ?>, <?php echo $prevYear; ?> vs <?php echo $curYear; ?></span>
      <span><?php echo $now->format('m/d/Y') . '  ' . $refreshedAt; ?></span>
    </div>
  </main>
</div>

<script>
var CUST_DATA    = <?php echo $custJson; ?>;
var HALF_CUTOFF  = <?php echo $halfCutoff; ?>;
var PREV_YEAR    = <?php echo $prevYear; ?>;
var CUR_YEAR     = <?php echo $curYear; ?>;
var EI_BASE      = <?php echo json_encode($eiBase); ?>;
var EI_EID       = <?php echo json_encode($eID); ?>;
var LY_PERIOD    = 'Jan 1–<?php echo $now->format('M j'); ?>, <?php echo $prevYear; ?>';
var TY_PERIOD    = 'Jan 1–<?php echo $now->format('M j, Y'); ?>';

// Only the bottom half
var BTM_DATA = CUST_DATA.filter(function(r) { return r.rnk <= HALF_CUTOFF; });

// Add computed fields
BTM_DATA.forEach(function(r) {
  r.delta = r.revTy - r.revLyYtd;
  r.pct   = r.revLyYtd > 0 ? Math.round(r.delta / r.revLyYtd * 1000) / 10
          : Math.round(r.revTy * 10) / 10;  // LY<=0: use TY $ value as %
});

var filteredData = BTM_DATA.slice();
var sortKey      = 'pct';
var sortDir      = 1;   // 1=asc, -1=desc  (pct asc = most negative first)

// ── Utilities ────────────────────────────────────────────────
function fmtMoney(n) {
  var neg = n < 0;
  var s   = Math.abs(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
  return (neg ? '-$' : '$') + s;
}
function fmtMoneyFull(n) {
  var neg = n < 0;
  var s   = Math.abs(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  return (neg ? '-$' : '$') + s;
}
function fmtChg(n) {
  var sign = n >= 0 ? '+' : '';
  return sign + fmtMoney(n);
}
function fmtPct(n) {
  var sign = n >= 0 ? '+' : '';
  return sign + n.toFixed(1) + '%';
}
function chgClass(n) {
  return n > 0 ? 'chg-pos' : (n < 0 ? 'chg-neg' : 'chg-zero');
}

// ── Filter option builders ────────────────────────────────────
function buildClassOptions() {
  var seen = {}, sel = document.getElementById('clsFilter');
  BTM_DATA.forEach(function(r) { seen[r.clsCode] = true; });
  Object.keys(seen).sort().forEach(function(c) {
    var opt = document.createElement('option');
    opt.value = c; opt.textContent = c;
    sel.appendChild(opt);
  });
}

function buildSlsmOptions() {
  var seen = {}, sel = document.getElementById('slsmFilter');
  BTM_DATA.forEach(function(r) { if (r.slsmName) seen[r.slsmName] = true; });
  Object.keys(seen).sort().forEach(function(n) {
    var opt = document.createElement('option');
    opt.value = n; opt.textContent = n;
    sel.appendChild(opt);
  });
}

// ── Filters ───────────────────────────────────────────────────
function applyFilter() {
  var cls    = document.getElementById('clsFilter').value;
  var slsm   = document.getElementById('slsmFilter').value;
  var pctOp  = document.getElementById('pctOp').value;
  var pctVal = parseFloat(document.getElementById('pctVal').value);
  var hasPct = pctOp && !isNaN(pctVal);
  document.getElementById('clearBtn').disabled = !(cls || slsm || hasPct);

  filteredData = BTM_DATA.filter(function(r) {
    if (cls  && r.clsCode  !== cls)  return false;
    if (slsm && r.slsmName !== slsm) return false;
    if (hasPct) {
      if (pctOp === 'eq' && r.pct !== pctVal)  return false;
      if (pctOp === 'ge' && r.pct <  pctVal)   return false;
      if (pctOp === 'le' && r.pct >  pctVal)   return false;
    }
    return true;
  });
  buildTable();
}

function clearFilter() {
  document.getElementById('clsFilter').value  = '';
  document.getElementById('slsmFilter').value = '';
  document.getElementById('pctOp').value      = '';
  document.getElementById('pctVal').value     = '';
  applyFilter();
}

// ── Sort ─────────────────────────────────────────────────────
var COLS = ['cust','custName','clsCode','slsmNum','slsmName','revLyFull','revLyYtd','revTy','delta','pct'];

function sortBy(key) {
  if (sortKey === key) { sortDir *= -1; }
  else { sortKey = key; sortDir = 1; }
  document.querySelectorAll('table.dtbl thead th').forEach(function(th) {
    th.classList.remove('sorted-asc', 'sorted-desc');
  });
  var idx = COLS.indexOf(key);
  if (idx >= 0) {
    var ths = document.querySelectorAll('table.dtbl thead th');
    if (ths[idx]) ths[idx].classList.add(sortDir === 1 ? 'sorted-asc' : 'sorted-desc');
  }
  filteredData.sort(function(a, b) {
    var av = a[key], bv = b[key];
    if (typeof av === 'string') return av.localeCompare(bv) * sortDir;
    return (av - bv) * sortDir;
  });
  renderTable();
}

// ── Build / render table ──────────────────────────────────────
function buildTable() {
  filteredData.sort(function(a, b) {
    var av = a[sortKey], bv = b[sortKey];
    if (typeof av === 'string') return av.localeCompare(bv) * sortDir;
    return (av - bv) * sortDir;
  });
  renderTable();
}

function renderTable() {
  var tbody = document.getElementById('custBody');
  tbody.innerHTML = '';
  var totLY = 0, totTY = 0, totDelta = 0;

  filteredData.forEach(function(r, idx) {
    totLY    += r.revLyYtd;
    totTY    += r.revTy;
    totDelta += r.delta;
    var dCls = chgClass(r.delta);
    var pCls = chgClass(r.pct);
    var tr   = document.createElement('tr');
    tr.innerHTML =
      '<td><span class="cust-link" onclick="openCustLink(' + idx + ')" title="Open in Harris EIP">' + r.cust + '</span></td>'
      + '<td style="max-width:200px;overflow:hidden;text-overflow:ellipsis">' + (r.custName || '') + '</td>'
      + '<td style="text-align:center">' + r.clsCode + '</td>'
      + '<td style="text-align:center">' + (r.slsmNum || '') + '</td>'
      + '<td>' + (r.slsmName || '') + '</td>'
      + '<td>' + fmtMoney(r.revLyFull) + '</td>'
      + '<td>' + fmtMoney(r.revLyYtd)  + '</td>'
      + '<td>' + fmtMoney(r.revTy)     + '</td>'
      + '<td class="' + dCls + '">' + fmtChg(r.delta) + '</td>'
      + '<td class="' + pCls + '">' + fmtPct(r.pct)   + '</td>';
    tbody.appendChild(tr);
  });

  var totPct      = totLY > 0 ? Math.round((totDelta / totLY) * 1000) / 10 : 0;
  var totDeltaCls = chgClass(totDelta);
  var totPctCls   = chgClass(totPct);
  document.getElementById('custFoot').innerHTML =
    '<tr>'
    + '<td colspan="5">TOTAL (' + filteredData.length + ' customers)</td>'
    + '<td></td>'
    + '<td>' + fmtMoney(totLY)    + '</td>'
    + '<td>' + fmtMoney(totTY)    + '</td>'
    + '<td class="' + totDeltaCls + '">' + fmtChg(totDelta) + '</td>'
    + '<td class="' + totPctCls   + '">' + fmtPct(totPct)   + '</td>'
    + '</tr>';

  document.getElementById('rowCount').textContent =
    filteredData.length + ' customer' + (filteredData.length !== 1 ? 's' : '') + ' shown';
}

// ── Customer link ─────────────────────────────────────────────
function openCustLink(idx) {
  var r = filteredData[idx]; if (!r) return;
  var url = EI_BASE + '/harris-CGI/CustomerSelect.d2w/REPORT'
    + '?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
    + '&eID=' + EI_EID
    + '&customerName='   + encodeURIComponent(r.custName)
    + '&customerNumber=' + encodeURIComponent(r.cust);
  window.open(url, '_blank', 'width=1300,height=750,scrollbars=yes,resizable=yes');
}

// ── Export to Excel ───────────────────────────────────────────
function exportExcel() {
  function csvField(v) {
    v = String(v == null ? '' : v);
    return (v.indexOf(',') >= 0 || v.indexOf('"') >= 0 || v.indexOf('\n') >= 0)
      ? '"' + v.replace(/"/g, '""') + '"'
      : v;
  }
  var rows = [[
    'Cust#', 'Name', 'Class', 'Slsm#', 'Salesperson',
    PREV_YEAR + ' Full Year', PREV_YEAR + ' YTD', CUR_YEAR + ' YTD',
    'Change $', 'Change %'
  ]];
  filteredData.forEach(function(r) {
    rows.push([
      r.cust, r.custName, r.clsCode, r.slsmNum, r.slsmName,
      r.revLyFull.toFixed(2), r.revLyYtd.toFixed(2), r.revTy.toFixed(2),
      r.delta.toFixed(2), r.pct.toFixed(1) + '%'
    ]);
  });
  var csv  = '﻿' + rows.map(function(row) { return row.map(csvField).join(','); }).join('\r\n');
  var blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
  var a    = document.createElement('a');
  a.href   = URL.createObjectURL(blob);
  a.download = 'Bottom50Revenue_' + CUR_YEAR + '_vs_' + PREV_YEAR + '.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(a.href);
}

// ── Auto-refresh (4:30 pm & 5:00 pm ET, M–F) ─────────────────
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
    var d   = Math.floor(tot / 86400);
    var h   = Math.floor((tot % 86400) / 3600);
    var m   = Math.floor((tot % 3600) / 60);
    var s   = tot % 60;
    var mm  = (m < 10 ? '0' : '') + m;
    var ss  = (s < 10 ? '0' : '') + s;
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

    var dotEl  = document.getElementById('rb-dot');
    var statEl = document.getElementById('rb-status');
    var fillEl = document.getElementById('rb-prog');
    var cdEl   = document.getElementById('rb-cd');
    var remMs  = nextRefreshMs();
    if (remMs !== null && startMs === null) startMs = remMs;

    if (isWd && remMs !== null) {
      dotEl.className      = 'refresh-dot';
      statEl.textContent   = 'Live – auto-refreshes at 4:30 pm & 5:00 pm ET (M–F)';
      cdEl.textContent     = fmtCd(remMs);
      fillEl.style.width   = (startMs ? Math.min(100, remMs / startMs * 100) : 100).toFixed(1) + '%';
    } else {
      dotEl.className      = 'refresh-dot refresh-dot--off';
      statEl.textContent   = 'Auto-refresh off – outside M–F scheduled times. Use Refresh Now.';
      cdEl.textContent     = '–';
      fillEl.style.width   = '0%';
    }
    setTimeout(tick, 1000);
  }
  tick();
}());

function triggerRefresh() {
  document.getElementById('rb-status').textContent = 'Refreshing…';
  setTimeout(function() { location.reload(); }, 300);
}

// ── Init ──────────────────────────────────────────────────────
buildClassOptions();
buildSlsmOptions();
applyFilter();
// Mark initial sort column
(function() {
  var idx = COLS.indexOf(sortKey);
  var ths = document.querySelectorAll('table.dtbl thead th');
  if (ths[idx]) ths[idx].classList.add(sortDir === 1 ? 'sorted-asc' : 'sorted-desc');
}());
</script>
</body>
</html>