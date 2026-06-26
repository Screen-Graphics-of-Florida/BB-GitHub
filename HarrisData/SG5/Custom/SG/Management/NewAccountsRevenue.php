<?php
// ── Goal config paths ─────────────────────────────────────────────────────────
// Primary: local Management directory  Secondary: /tmp (always writable on IBM i)
define('NAGOAL_FILE', dirname(__FILE__) . '/nagoal.json');
define('NAGOAL_TMP',  '/tmp/nagoal_sg.json');

// ── Handle AJAX goal-save POST ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'setgoal') {
    header('Content-Type: application/json');
    $newGoal = round((float)$_POST['goal'], 2);
    if ($newGoal > 0) {
        $payload = json_encode(array('goal' => $newGoal));
        @chmod(NAGOAL_FILE, 0666);                          // try to unlock primary
        $written = @file_put_contents(NAGOAL_FILE, $payload);
        if ($written === false) {                            // fall back to /tmp
            $written = @file_put_contents(NAGOAL_TMP, $payload);
        }
        if ($written !== false) {
            echo json_encode(array('ok' => true, 'goal' => $newGoal));
        } else {
            echo json_encode(array('ok' => false, 'error'
                => 'Cannot write goal file. On IBM i run: CHGAUT OBJ(\'' . NAGOAL_FILE . '\') USER(*PUBLIC) DTAAUT(*RWX)'));
        }
    } else {
        echo json_encode(array('ok' => false, 'error' => 'Goal must be > 0'));
    }
    exit;
}

require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$now  = new DateTime();
$yrSt = new DateTime($now->format('Y-01-01'));
$next = clone $now; $next->modify('+1 day');

function dateToCymd(DateTime $dt) {
    return ((int)$dt->format('Y') - 1900) * 10000 + (int)$dt->format('m') * 100 + (int)$dt->format('d');
}
function cymdToDate($c) {
    $c = (int)$c;
    if ($c <= 0) return '';
    $yr = (int)($c / 10000) + 1900;
    $mo = (int)(($c % 10000) / 100);
    $dy = $c % 100;
    return sprintf('%02d/%02d/%04d', $mo, $dy, $yr);
}

$yrStCymd     = dateToCymd($yrSt);
$tomorrowCymd = dateToCymd($next);

// ── Load persisted goal — try primary path, then /tmp fallback ───────────────
$GOAL = 4000000.00;
foreach (array(NAGOAL_FILE, NAGOAL_TMP) as $_gf) {
    if (file_exists($_gf)) {
        $gc = @json_decode(@file_get_contents($_gf), true);
        if (isset($gc['goal']) && $gc['goal'] > 0) { $GOAL = (float)$gc['goal']; break; }
    }
}

$conn    = $i5Connect->getConnection();
$dbError = '';

// ── Query 1: YTD invoiced revenue by new account, with salesperson ───────────
// Salesperson comes from HDCUST.CMSLSM (customer's assigned rep).
// One row per customer — CMSLSM is a single field on the customer record.
$sql1 = "
    SELECT
        c.CMCUST                                                                              AS CUSTNUM,
        COALESCE(TRIM(c.CMCNA1), '')                                                          AS CUSTNAME,
        c.CMDFES                                                                              AS DFES,
        c.CMSLSM                                                                              AS SLSNUM,
        CASE WHEN s.SMREGN <> 'INACT' THEN COALESCE(TRIM(s.SMSNA1), '') ELSE 'Ex-Sales' END  AS SLSNAME,
        SUM(CASE WHEN d.DHORUF <> 0 THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END)         AS AMT
    FROM SGHDSDATA.OEORDH d
    JOIN SGHDSDATA.OEORHD h ON d.\"DHORD#\" = h.\"OEORD#\"
    JOIN SGHDSDATA.HDCUST c ON h.OESHTO = c.CMCUST
    LEFT JOIN SGHDSDATA.HDSLSM s ON c.CMSLSM = s.SMSLSM
    WHERE d.\"DHSEQ#\" <> 0
      AND d.DHQSTC <> 0
      AND d.DHDTLI >= $yrStCymd
      AND d.DHDTLI <  $tomorrowCymd
      AND c.CMDFES >= $yrStCymd
      AND c.CMDFES <  $tomorrowCymd
    GROUP BY c.CMCUST, c.CMCNA1, c.CMDFES, c.CMSLSM, s.SMREGN, s.SMSNA1
    ORDER BY AMT DESC
";

$custRows = array();
$ytdTotal = 0.0;
$stmt1 = db2_exec($conn, $sql1, array('cursor' => DB2_SCROLLABLE));
if ($stmt1) {
    while ($r = db2_fetch_assoc($stmt1)) {
        $amt = round((float)$r['AMT'], 2);
        $dfesRaw = (int)$r['DFES'];
        $custRows[] = array(
            'slsNum'   => (int)$r['SLSNUM'],
            'slsName'  => trim((string)$r['SLSNAME']),
            'custNum'  => (int)$r['CUSTNUM'],
            'custName' => trim((string)$r['CUSTNAME']),
            'dfes'     => cymdToDate($dfesRaw),
            'dfesMo'   => (int)(($dfesRaw % 10000) / 100),
            'amt'      => $amt,
        );
        $ytdTotal += $amt;
    }
    db2_free_stmt($stmt1);
} else {
    $dbError = db2_stmt_errormsg();
}

// ── Query 2: Total count of all new accounts this year ───────────────────────
$sql2 = "
    SELECT COUNT(*) AS CNT
    FROM SGHDSDATA.HDCUST
    WHERE CMDFES >= $yrStCymd
      AND CMDFES <  $tomorrowCymd
";
$newAcctCount = 0;
$stmt2 = db2_exec($conn, $sql2, array('cursor' => DB2_SCROLLABLE));
if ($stmt2) {
    $r2 = db2_fetch_assoc($stmt2);
    if ($r2) $newAcctCount = (int)$r2['CNT'];
    db2_free_stmt($stmt2);
} elseif (!$dbError) {
    $dbError = db2_stmt_errormsg();
}

$ytdTotal     = round($ytdTotal, 2);
$pctComplete  = ($GOAL > 0) ? round(($ytdTotal / $GOAL) * 100, 1) : 0.0;
$remaining    = round($GOAL - $ytdTotal, 2);
$custJson     = json_encode($custRows);
$goalJson     = json_encode($GOAL);
$refreshedAt  = $now->format('g:i:s A');
$yearLabel    = $now->format('Y');
$eiBase       = 'https://portal.screen-graphics.com:5601';
$withRevCount = count($custRows);

if ($pctComplete >= 100) {
    $barColor = 'linear-gradient(90deg,#1a7a3c,#16a34a)';
    $badgeBg  = '#1a7a3c'; $badgeFg = 'white';
} elseif ($pctComplete >= 75) {
    $barColor = 'linear-gradient(90deg,#0055b3,#2563eb)';
    $badgeBg  = '#0055b3'; $badgeFg = 'white';
} elseif ($pctComplete >= 50) {
    $barColor = 'linear-gradient(90deg,#b45309,#d97706)';
    $badgeBg  = '#b45309'; $badgeFg = 'white';
} else {
    $barColor = 'linear-gradient(90deg,#b91c1c,#dc2626)';
    $badgeBg  = '#b91c1c'; $badgeFg = 'white';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Account Revenue vs Goal <?php echo $yearLabel; ?></title>
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
    background: #edf1f7; display: flex; flex-direction: column; height: 100vh; overflow: hidden;
  }

  /* ── Top bar ──────────────────────────────────────────────── */
  .topbar { background: var(--hd-blue); color: white; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; height: 42px; flex-shrink: 0; }
  .topbar-logo { font-size: 15px; font-weight: 700; letter-spacing: 0.5px; font-family: 'Roboto Condensed', sans-serif; }
  .topbar-logo span { color: #6db3ff; }
  .topbar-right { display: flex; align-items: center; gap: 20px; font-size: 12px; color: #b8cfee; }
  .topbar-right a { color: #b8cfee; text-decoration: none; }

  .layout { display: flex; flex: 1; overflow: hidden; }

  /* ── Left nav ─────────────────────────────────────────────── */
  .nav { width: var(--hd-nav-width); background: var(--hd-nav-bg); flex-shrink: 0; overflow-y: auto; }
  .nav-section { padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
  .nav-item { display: block; padding: 6px 14px; color: #cde0ff; font-size: 12px; text-decoration: none; white-space: nowrap; }
  .nav-item:hover { background: rgba(255,255,255,0.12); color: white; }
  .nav-item.active { background: rgba(255,255,255,0.2); color: white; font-weight: 700; border-left: 3px solid #6db3ff; padding-left: 11px; }
  .nav-header { padding: 8px 14px 4px; color: #89afd4; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
  .nw-badge { display: inline-block; background: rgba(109,179,255,0.25); color: #9ecfff; font-size: 9px; padding: 1px 4px; border-radius: 2px; margin-left: 4px; }

  /* ── Main column ──────────────────────────────────────────── */
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

  /* ── Refresh bar — standard ───────────────────────────────── */
  .refresh-bar { background: #e8f0fb; border-bottom: 1px solid #bdd0ee; padding: 4px 14px; display: flex; align-items: center; gap: 14px; font-size: 11px; color: #5a6478; flex-shrink: 0; }
  .refresh-dot { width: 8px; height: 8px; border-radius: 50%; background: #1a7a3c; animation: pulse 2s infinite; flex-shrink: 0; }
  .refresh-dot--off { background: #94a3b8; animation: none; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
  .refresh-progress { flex: 1; max-width: 160px; height: 4px; background: #d0dced; border-radius: 2px; overflow: hidden; }
  .refresh-fill { height: 100%; background: #0055b3; border-radius: 2px; transition: width 1s linear; }
  .refresh-pill { background: #fff; border: 1px solid #c8d0de; border-radius: 12px; padding: 2px 10px; font-size: 11px; font-weight: 600; white-space: nowrap; }

  /* ── Dashboard body ───────────────────────────────────────── */
  .dash-body {
    flex: 1; overflow-y: auto; padding: 16px;
    background: linear-gradient(160deg, #dbd8cc 0%, #c8c4bc 100%);
  }
  .content-wrap { width: 100%; max-width: 1060px; margin: 0 auto; }

  /* ── Win98 panel ──────────────────────────────────────────── */
  .panel { background: #fff; border: 2px solid; border-color: #fff #888 #888 #fff; box-shadow: 1px 1px 0 #000; }
  .panel-title { background: #000080; color: white; font-size: 11px; font-weight: 700; padding: 2px 6px; font-family: 'Roboto Condensed', sans-serif; letter-spacing: 0.5px; }

  /* ── Top grid ─────────────────────────────────────────────── */
  .top-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }

  /* ── LCD display ──────────────────────────────────────────── */
  .lcd-body { padding: 10px 14px 12px; }
  .lcd-display {
    background: #001a22;
    border: 3px inset #666; border-radius: 4px;
    padding: 14px 16px; text-align: center;
    font-family: 'Share Tech Mono', monospace; letter-spacing: 2px;
    position: relative; overflow: hidden;
    box-shadow: 0 0 16px rgba(0,200,255,0.3) inset;
  }
  .lcd-display::before {
    content: ''; position: absolute; inset: 0;
    background: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(0,0,0,0.08) 3px, rgba(0,0,0,0.08) 4px);
    pointer-events: none;
  }
  .lcd-val { font-size: 38px; font-weight: 400; line-height: 1; color: #00e5ff; text-shadow: 0 0 10px rgba(0,200,255,0.9); }
  .lcd-sublabel { font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; font-family: 'Roboto Condensed', sans-serif; margin-top: 6px; color: #00aacc; text-align: center; }
  .lcd-stat { font-size: 11px; color: #6a8a99; margin-top: 6px; font-family: 'Roboto Condensed', sans-serif; text-align: center; }

  /* ── Goal panel ───────────────────────────────────────────── */
  .goal-body { padding: 12px 14px; }
  .goal-kv { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
  .goal-k { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #555; font-family: 'Roboto Condensed', sans-serif; }
  .goal-v { font-size: 22px; font-weight: 700; color: #003087; font-family: 'Share Tech Mono', monospace; }
  .goal-right { display: flex; align-items: center; gap: 10px; }
  .btn-setgoal { font-size: 11px; padding: 2px 8px; background: #003087; color: white; border-color: #002060; border-radius: 3px; }
  .btn-setgoal:hover { background: #002060; color: white; }
  .divider { border: none; border-top: 1px solid #e0e0e0; margin: 8px 0; }
  .prog-lbl { font-size: 11px; color: #555; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
  .prog-track { background: #ddd; border-radius: 6px; height: 28px; overflow: hidden; border: 1px solid #bbb; position: relative; }
  .prog-fill { height: 100%; border-radius: 5px; position: relative; overflow: hidden; }
  .prog-fill::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 50%; background: rgba(255,255,255,0.18); border-radius: 5px 5px 0 0; }
  .prog-pct-text { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; font-weight: 700; font-family: 'Roboto Condensed', sans-serif; color: white; text-shadow: 0 1px 3px rgba(0,0,0,0.6); pointer-events: none; }
  .bottom-row { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
  .remaining-block .lbl { font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 1px; }
  .remaining-block .val { font-size: 16px; font-weight: 700; font-family: 'Share Tech Mono', monospace; }
  .remaining-block .val.under { color: #b91c1c; }
  .remaining-block .val.over  { color: #1a7a3c; }
  .pct-badge { display: inline-block; padding: 5px 16px; border-radius: 20px; font-size: 18px; font-weight: 700; font-family: 'Roboto Condensed', sans-serif; }

  /* ── Detail table ─────────────────────────────────────────── */
  .tbl-section { margin-bottom: 12px; }
  .tbl-toolbar { padding: 5px 10px; background: #f0f3f8; border-bottom: 1px solid #d0d7e2; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
  .tbl-filter-area { display: flex; align-items: center; gap: 8px; }
  .filter-label { font-size: 11px; font-weight: 700; color: #333; white-space: nowrap; font-family: 'Roboto Condensed', sans-serif; }
  .filter-select { font-size: 12px; padding: 3px 8px; border: 1px solid #c8d0de; border-radius: 3px; font-family: 'Roboto Condensed', sans-serif; color: #1a2233; background: white; min-width: 180px; }
  .filter-count { font-size: 11px; color: #5a6478; font-style: italic; white-space: nowrap; }
  .btn-clear-filter { font-size: 11px; padding: 2px 7px; background: #fff0e8; border-color: #d08060; color: #8b3010; }
  .btn-clear-filter:hover { background: #ffe0c8; }
  .btn-clear-filter:disabled { background: #f0f0f0; border-color: #ccc; color: #aaa; cursor: default; }
  .btn-export { background: #1a7a3c; color: white; border-color: #155e30; font-weight: 700; }
  .btn-export:hover { background: #155e30; color: white; }
  .tbl-wrap { overflow-x: auto; }
  table.dtbl { width: 100%; border-collapse: collapse; font-size: 12px; font-family: 'Roboto Condensed', sans-serif; }
  table.dtbl thead th { background: #000080; color: white; padding: 4px 10px; text-align: right; font-size: 11px; font-weight: 700; white-space: nowrap; border-right: 1px solid #3333aa; }
  table.dtbl thead th.c  { text-align: center; }
  table.dtbl thead th.l  { text-align: left; }
  table.dtbl tbody tr:nth-child(even) { background: #f0f0f0; }
  table.dtbl tbody tr:hover { background: #ddeeff; }
  table.dtbl tbody td { padding: 5px 10px; text-align: right; white-space: nowrap; }
  table.dtbl tbody td.c { text-align: center; }
  table.dtbl tbody td.l { text-align: left; font-weight: 600; }
  table.dtbl tfoot td { background: #ddd; font-weight: 700; padding: 5px 10px; text-align: right; border-top: 2px solid #888; }
  table.dtbl tfoot td:first-child { text-align: left; }

  .cust-link { color: #0055b3; text-decoration: none; font-weight: 700; font-variant-numeric: tabular-nums; border-bottom: 1px dotted #0055b3; cursor: pointer; }
  .cust-link:hover { color: #003087; border-bottom-style: solid; }
  .money { font-variant-numeric: tabular-nums; }
  .zero  { color: #aaa; }

  /* ── Set Goal modal ───────────────────────────────────────── */
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); align-items: center; justify-content: center; z-index: 2000; }
  .modal-overlay.open { display: flex; }
  .modal { background: #fff; border: 2px solid; border-color: #fff #888 #888 #fff; box-shadow: 4px 4px 0 #000; width: 340px; }
  .modal-hdr { background: #000080; color: white; padding: 3px 8px; display: flex; align-items: center; justify-content: space-between; }
  .modal-hdr-title { font-size: 12px; font-weight: 700; letter-spacing: 0.5px; font-family: 'Roboto Condensed', sans-serif; }
  .modal-x { background: #d4d0c8; border: 2px solid; border-color: #fff #808080 #808080 #fff; color: #000; font-size: 11px; font-weight: 700; cursor: pointer; padding: 0 6px; height: 18px; line-height: 14px; font-family: monospace; }
  .modal-x:hover { background: #c0b8b0; }
  .modal-body { padding: 16px; }
  .modal-label { font-size: 12px; font-weight: 700; color: #333; margin-bottom: 6px; display: block; font-family: 'Roboto Condensed', sans-serif; }
  .modal-input { width: 100%; font-size: 16px; padding: 6px 10px; border: 2px inset #999; font-family: 'Share Tech Mono', monospace; color: #003087; }
  .modal-hint { font-size: 11px; color: #777; margin-top: 4px; }
  .modal-footer { padding: 8px 16px 14px; display: flex; gap: 8px; justify-content: flex-end; }
  .btn-save { background: #1a7a3c; color: white; border-color: #155e30; font-weight: 700; padding: 4px 16px; }
  .btn-save:hover { background: #155e30; color: white; }
  .goal-save-msg { font-size: 11px; margin-top: 6px; text-align: center; min-height: 16px; }

  /* ── Footer ───────────────────────────────────────────────── */
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
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Management/RevenueVsGoal.php" target="_blank">Revenue vs Goal <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item active" href="#">New Acct Rev vs Growth <span class="nw-badge">&#8599;</span></a>
    </div>
    <div class="nav-section">
      <div class="nav-header">SG Dashboards</div>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/BookingsDashboard.php" target="_blank">Bookings <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/ShipmentsDashboard.php" target="_blank">Shipments <span class="nw-badge">&#8599;</span></a>
      <a class="nav-item" href="https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/SalesDashboard.php" target="_blank">Sales <span class="nw-badge">&#8599;</span></a>
    </div>
  </nav>

  <main class="main">
    <div class="page-header">
      <div>
        <div class="page-title">New Account Revenue vs Goal &mdash; <?php echo $yearLabel; ?></div>
        <div class="page-meta">SG Management &rsaquo; Order Entry &nbsp;&bull;&nbsp; Goal: $<?php echo number_format($GOAL, 0); ?> &nbsp;&bull;&nbsp; New accounts defined by HDCUST.CMDFES in <?php echo $yearLabel; ?> &nbsp;&bull;&nbsp; Click customer # to open in EIP</div>
      </div>
      <div>
        <button class="btn" onclick="triggerRefresh()">&#8635; Refresh Now</button>
      </div>
    </div>

    <div class="refresh-bar">
      <div class="refresh-dot" id="nar-dot"></div>
      <span id="nar-status">Checking...</span>
      <div class="refresh-progress"><div class="refresh-fill" id="nar-prog" style="width:100%"></div></div>
      <span>Next refresh: <strong id="nar-cd">&ndash;</strong></span>
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

        <div class="top-grid">

          <!-- YTD New Account Revenue LCD -->
          <div class="panel">
            <div class="panel-title">&#9632; YTD NEW ACCOUNT REVENUE <?php echo $yearLabel; ?></div>
            <div class="lcd-body">
              <div class="lcd-display">
                <div class="lcd-val"><?php echo '$' . number_format($ytdTotal, 2); ?></div>
              </div>
              <div class="lcd-stat"><?php echo $newAcctCount; ?> new accounts in <?php echo $yearLabel; ?> &nbsp;&bull;&nbsp; <?php echo $withRevCount; ?> with invoiced revenue</div>
            </div>
          </div>

          <!-- Goal & Progress -->
          <div class="panel">
            <div class="panel-title" id="goalPanelTitle">&#9632; <?php echo $yearLabel; ?> NEW ACCOUNT REVENUE GOAL</div>
            <div class="goal-body">
              <div class="goal-kv">
                <span class="goal-k" id="goalLabel">Annual Goal</span>
                <div class="goal-right">
                  <span class="goal-v" id="goalDisplay">$<?php echo number_format($GOAL, 2); ?></span>
                  <button class="btn btn-setgoal" onclick="openGoalModal()">&#9998; Set Goal</button>
                </div>
              </div>
              <hr class="divider">
              <div class="prog-lbl">Progress to Goal</div>
              <div class="prog-track">
                <div class="prog-fill" id="progFill" style="width:<?php echo min(100, $pctComplete); ?>%;background:<?php echo $barColor; ?>"></div>
                <span class="prog-pct-text" id="progPct"><?php echo $pctComplete; ?>%</span>
              </div>
              <div class="bottom-row">
                <div class="remaining-block">
                  <div class="lbl" id="remLbl"><?php echo $remaining >= 0 ? 'Remaining' : 'Over Goal'; ?></div>
                  <div class="val <?php echo $remaining >= 0 ? 'under' : 'over'; ?>" id="remVal">
                    <?php echo ($remaining >= 0 ? '-$' : '+$') . number_format(abs($remaining), 2); ?>
                  </div>
                </div>
                <div class="pct-badge" id="pctBadge" style="background:<?php echo $badgeBg; ?>;color:<?php echo $badgeFg; ?>">
                  <?php echo $pctComplete; ?>% Complete
                </div>
              </div>
            </div>
          </div>

        </div><!-- /top-grid -->

        <!-- New accounts with revenue detail table -->
        <div class="tbl-section panel">
          <div class="panel-title">&#9632; NEW ACCOUNTS <?php echo $yearLabel; ?> &mdash; YTD INVOICED REVENUE &nbsp;(<?php echo $withRevCount; ?> accounts with revenue &mdash; click customer # to open in EIP)</div>
          <div class="tbl-toolbar">
            <div class="tbl-filter-area">
              <span class="filter-label">Filter by Salesperson:</span>
              <select class="filter-select" id="slsFilter" onchange="applyFilter()">
                <option value="">&#8212; All Salespeople &#8212;</option>
              </select>
              <button class="btn btn-clear-filter" id="clearFilterBtn" disabled onclick="clearFilter()">&#x2715; Clear</button>
              <span style="width:1px;background:#c8d0de;align-self:stretch;margin:0 4px"></span>
              <span class="filter-label">Month:</span>
              <select class="filter-select" id="moFilter" style="min-width:130px" onchange="applyFilter()">
                <option value="">&#8212; All Months &#8212;</option>
              </select>
              <button class="btn btn-clear-filter" id="clearMoBtn" disabled onclick="clearMoFilter()">&#x2715; Clear</button>
              <span class="filter-count" id="filterCount"></span>
            </div>
            <button class="btn btn-export" onclick="exportToCSV()">&#8659; Export to Excel</button>
          </div>
          <div class="tbl-wrap">
            <table class="dtbl">
              <thead>
                <tr>
                  <th class="c">Slsm #</th>
                  <th class="l">Salesperson</th>
                  <th class="c">Cust #</th>
                  <th class="l">Customer Name</th>
                  <th class="c">Date Established</th>
                  <th>YTD Revenue</th>
                </tr>
              </thead>
              <tbody>
<?php
$tableTotal = 0.0;
foreach ($custRows as $idx => $row):
    $tableTotal += $row['amt'];
    $amtFmt = '$' . number_format($row['amt'], 2);
    $amtCls = $row['amt'] == 0 ? 'zero' : 'money';
?>
                <tr>
                  <td class="c"><?php echo $row['slsNum']; ?></td>
                  <td class="l"><?php echo htmlspecialchars($row['slsName']); ?></td>
                  <td class="c"><span class="cust-link" onclick="openCustLink(<?php echo $idx; ?>)"
                    title="Open customer <?php echo $row['custNum']; ?> in Harris EIP"><?php echo $row['custNum']; ?></span></td>
                  <td class="l"><?php echo htmlspecialchars($row['custName']); ?></td>
                  <td class="c"><?php echo htmlspecialchars($row['dfes']); ?></td>
                  <td class="<?php echo $amtCls; ?>"><?php echo $amtFmt; ?></td>
                </tr>
<?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="5">TOTAL &mdash; <?php echo $withRevCount; ?> accounts with revenue</td>
                  <td>$<?php echo number_format($tableTotal, 2); ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

      </div><!-- /content-wrap -->
    </div><!-- /dash-body -->

    <div class="page-footer">
      <span>SGHDSDATA/OEORDH &rarr; OEORHD &rarr; HDCUST.CMDFES+CMSLSM &rarr; HDSLSM &mdash; New accts: <?php echo $yrSt->format('M j, Y'); ?>&ndash;today &mdash; DHSLPR &times; DHQSTC &divide; DHORUF &mdash; YTD thru <?php echo $now->format('M j, Y'); ?></span>
      <span><?php echo $now->format('m/d/Y') . '  ' . $refreshedAt; ?></span>
    </div>
  </main>
</div>

<!-- ── Set Goal Modal ────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="goalModal">
  <div class="modal">
    <div class="modal-hdr">
      <span class="modal-hdr-title">&#9632; Set Annual New Account Revenue Goal &mdash; <?php echo $yearLabel; ?></span>
      <button class="modal-x" onclick="closeGoalModal()">&#x2715;</button>
    </div>
    <div class="modal-body">
      <label class="modal-label" for="goalInput">Annual Goal Amount ($)</label>
      <input class="modal-input" type="number" id="goalInput" min="1" step="1000"
        value="<?php echo number_format($GOAL, 0, '.', ''); ?>">
      <div class="modal-hint">Enter the dollar goal without commas, e.g. 4000000</div>
      <div class="goal-save-msg" id="goalSaveMsg"></div>
    </div>
    <div class="modal-footer">
      <button class="btn" onclick="closeGoalModal()">Cancel</button>
      <button class="btn btn-save" onclick="saveGoal()">&#10003; Save Goal</button>
    </div>
  </div>
</div>

<script>
// ── Server data ───────────────────────────────────────────────────────────
var CUST_DATA  = <?php echo $custJson; ?>;
var EI_BASE    = <?php echo json_encode($eiBase); ?>;
var EI_EID     = <?php echo json_encode($eID); ?>;
var YEAR_LABEL = <?php echo json_encode($yearLabel); ?>;
var YTD_TOTAL  = <?php echo json_encode($ytdTotal); ?>;
var CURRENT_GOAL = <?php echo $goalJson; ?>;

function fmtMoney(n) {
  return '$' + n.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}

// ── Customer link ─────────────────────────────────────────────────────────
function openCustLink(idx) {
  var r = CUST_DATA[idx];
  if (!r) return;
  var url = EI_BASE + '/harris-CGI/CustomerSelect.d2w/REPORT'
    + '?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
    + '&eID='            + EI_EID
    + '&customerName='   + encodeURIComponent(r.custName)
    + '&customerNumber=' + encodeURIComponent(r.custNum);
  window.open(url, '_blank', 'width=1300,height=750,scrollbars=yes,resizable=yes');
}

// ── Filter setup ─────────────────────────────────────────────────────────
var MONTH_NAMES = ['','January','February','March','April','May','June',
                   'July','August','September','October','November','December'];

(function() {
  // Populate salesperson dropdown
  var names = {};
  CUST_DATA.forEach(function(r) { if (r.slsName) names[r.slsName] = true; });
  Object.keys(names).sort().forEach(function(n) {
    var opt = document.createElement('option');
    opt.value = n; opt.textContent = n;
    document.getElementById('slsFilter').appendChild(opt);
  });

  // Populate month dropdown (only months that have data, in calendar order)
  var months = {};
  CUST_DATA.forEach(function(r) { if (r.dfesMo) months[r.dfesMo] = true; });
  Object.keys(months).map(Number).sort(function(a,b){return a-b;}).forEach(function(m) {
    var opt = document.createElement('option');
    opt.value = m; opt.textContent = MONTH_NAMES[m];
    document.getElementById('moFilter').appendChild(opt);
  });

  setFilterCount(CUST_DATA.length);
}());

function applyFilter() {
  var slsVal = document.getElementById('slsFilter').value;
  var moVal  = parseInt(document.getElementById('moFilter').value, 10) || 0;
  var rows   = document.querySelectorAll('table.dtbl tbody tr');
  var total  = 0, count = 0;

  rows.forEach(function(tr, idx) {
    var r    = CUST_DATA[idx];
    var show = r
      && (!slsVal || r.slsName === slsVal)
      && (!moVal  || r.dfesMo  === moVal);
    tr.style.display = show ? '' : 'none';
    if (show) { total += r.amt; count++; }
  });

  var tfoot = document.querySelector('table.dtbl tfoot tr');
  if (tfoot) {
    tfoot.cells[0].textContent = 'TOTAL — ' + count + ' account' + (count !== 1 ? 's' : '') + ' with revenue';
    tfoot.cells[tfoot.cells.length - 1].textContent = fmtMoney(total);
  }

  setFilterCount(count);
  document.getElementById('clearFilterBtn').disabled = !slsVal;
  document.getElementById('clearMoBtn').disabled     = !moVal;

  // Update goal panel: monthly view when month selected, annual when not
  if (moVal) {
    recalcGoalPanel(total, CURRENT_GOAL / 12,
      'Monthly Goal (' + MONTH_NAMES[moVal] + ')');
  } else {
    recalcGoalPanel(total, CURRENT_GOAL, 'Annual Goal');
  }
}

function clearFilter() {
  document.getElementById('slsFilter').value = '';
  applyFilter();
}
function clearMoFilter() {
  document.getElementById('moFilter').value = '';
  applyFilter();
}

function setFilterCount(count) {
  var el = document.getElementById('filterCount');
  if (el) el.textContent = count + ' account' + (count !== 1 ? 's' : '') + ' shown';
}

function recalcGoalPanel(total, goal, label) {
  var pct = goal > 0 ? Math.round(total / goal * 1000) / 10 : 0;
  var rem = goal - total;
  var barColor, badgeBg;
  if (pct >= 100) { barColor = 'linear-gradient(90deg,#1a7a3c,#16a34a)'; badgeBg = '#1a7a3c'; }
  else if (pct >= 75) { barColor = 'linear-gradient(90deg,#0055b3,#2563eb)'; badgeBg = '#0055b3'; }
  else if (pct >= 50) { barColor = 'linear-gradient(90deg,#b45309,#d97706)'; badgeBg = '#b45309'; }
  else                { barColor = 'linear-gradient(90deg,#b91c1c,#dc2626)'; badgeBg = '#b91c1c'; }

  document.getElementById('goalLabel').textContent        = label;
  document.getElementById('goalDisplay').textContent      = fmtMoney(goal);
  document.getElementById('progFill').style.width         = Math.min(100, pct) + '%';
  document.getElementById('progFill').style.background    = barColor;
  document.getElementById('progPct').textContent          = pct + '%';
  document.getElementById('pctBadge').textContent         = pct + '% Complete';
  document.getElementById('pctBadge').style.background    = badgeBg;
  document.getElementById('remLbl').textContent           = rem >= 0 ? 'Remaining' : 'Over Goal';
  document.getElementById('remVal').className             = 'val ' + (rem >= 0 ? 'under' : 'over');
  document.getElementById('remVal').textContent           = (rem >= 0 ? '-' : '+') + fmtMoney(Math.abs(rem));
}

// ── Export to CSV (exports only currently visible rows) ───────────────────
function exportToCSV() {
  var filterVal = document.getElementById('slsFilter').value;
  var filtered  = filterVal
    ? CUST_DATA.filter(function(r) { return r.slsName === filterVal; })
    : CUST_DATA;
  var headers = ['Slsm #', 'Salesperson', 'Cust #', 'Customer Name', 'Date Established', 'YTD Revenue'];
  var rows    = [headers];
  var total   = 0;
  filtered.forEach(function(r) {
    total += r.amt;
    rows.push([r.slsNum, r.slsName, r.custNum, r.custName, r.dfes, r.amt.toFixed(2)]);
  });
  rows.push(['', '', '', 'TOTAL', '', total.toFixed(2)]);

  var csv = rows.map(function(row) {
    return row.map(function(cell) {
      var s = String(cell == null ? '' : cell);
      if (s.indexOf(',') !== -1 || s.indexOf('"') !== -1 || s.indexOf('\n') !== -1) {
        s = '"' + s.replace(/"/g, '""') + '"';
      }
      return s;
    }).join(',');
  }).join('\r\n');

  var today = new Date();
  var ds = today.getFullYear()
    + ('0'+(today.getMonth()+1)).slice(-2)
    + ('0'+today.getDate()).slice(-2);
  var bom  = '﻿';
  var blob = new Blob([bom + csv], {type: 'text/csv;charset=utf-8'});
  var url  = URL.createObjectURL(blob);
  var a    = document.createElement('a');
  a.href     = url;
  a.download = 'NewAcctRevenue_' + YEAR_LABEL + '_' + ds + '.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

// ── Set Goal modal ────────────────────────────────────────────────────────
function openGoalModal() {
  document.getElementById('goalInput').value = CURRENT_GOAL;
  document.getElementById('goalSaveMsg').textContent = '';
  document.getElementById('goalModal').classList.add('open');
  document.getElementById('goalInput').focus();
}
function closeGoalModal() {
  document.getElementById('goalModal').classList.remove('open');
}
document.getElementById('goalModal').addEventListener('click', function(e) {
  if (e.target === this) closeGoalModal();
});

function saveGoal() {
  var val = parseFloat(document.getElementById('goalInput').value);
  if (isNaN(val) || val <= 0) {
    document.getElementById('goalSaveMsg').style.color = '#b91c1c';
    document.getElementById('goalSaveMsg').textContent = 'Please enter a valid dollar amount.';
    return;
  }
  document.getElementById('goalSaveMsg').style.color = '#555';
  document.getElementById('goalSaveMsg').textContent = 'Saving…';

  var fd = new FormData();
  fd.append('action', 'setgoal');
  fd.append('goal', val);

  fetch(location.href, {method: 'POST', body: fd})
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data.ok) {
        CURRENT_GOAL = data.goal;
        updateGoalDisplay(data.goal);
        document.getElementById('goalSaveMsg').style.color = '#1a7a3c';
        document.getElementById('goalSaveMsg').textContent = '✓ Goal saved. Page will refresh in 2 seconds.';
        setTimeout(function() { location.reload(); }, 2000);
      } else {
        document.getElementById('goalSaveMsg').style.color = '#b91c1c';
        document.getElementById('goalSaveMsg').textContent = 'Error: ' + (data.error || 'Save failed');
      }
    })
    .catch(function() {
      document.getElementById('goalSaveMsg').style.color = '#b91c1c';
      document.getElementById('goalSaveMsg').textContent = 'Network error — could not save.';
    });
}

function updateGoalDisplay(goal) {
  // After saving a new goal, re-run the current filter state so panel recalculates
  var moVal = parseInt(document.getElementById('moFilter').value, 10) || 0;
  if (moVal) {
    recalcGoalPanel(currentFilteredTotal(), goal / 12, 'Monthly Goal (' + MONTH_NAMES[moVal] + ')');
  } else {
    recalcGoalPanel(currentFilteredTotal(), goal, 'Annual Goal');
  }
}
function currentFilteredTotal() {
  var slsVal = document.getElementById('slsFilter').value;
  var moVal  = parseInt(document.getElementById('moFilter').value, 10) || 0;
  var total  = 0;
  CUST_DATA.forEach(function(r) {
    if ((!slsVal || r.slsName === slsVal) && (!moVal || r.dfesMo === moVal)) total += r.amt;
  });
  return total;
}

// ── Auto-refresh at 4:30 pm & 5:00 pm ET on weekdays ──────────────────────
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
    var dotEl  = document.getElementById('nar-dot');
    var statEl = document.getElementById('nar-status');
    var fillEl = document.getElementById('nar-prog');
    var cdEl   = document.getElementById('nar-cd');
    var remMs  = nextRefreshMs();
    if (remMs !== null && startMs === null) startMs = remMs;
    if (isWd && remMs !== null) {
      dotEl.className    = 'refresh-dot';
      statEl.textContent = 'Live – auto-refreshes at 4:30 pm & 5:00 pm ET (M–F)';
      cdEl.textContent   = fmtCd(remMs);
      var pct = startMs ? Math.min(100, remMs / startMs * 100) : 100;
      fillEl.style.width = pct.toFixed(1) + '%';
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
  document.getElementById('nar-dot').className      = 'refresh-dot';
  document.getElementById('nar-status').textContent = 'Refreshing…';
  setTimeout(function() { location.reload(); }, 300);
}
</script>
</body>
</html>