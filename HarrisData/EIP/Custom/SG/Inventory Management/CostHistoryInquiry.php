<?php
// CostHistoryInquiry.php
// Item cost history inquiry over SGOBJ.SGCSTHST.
//
// Shows what an item cost, and when it changed, for cost set 1 (Standard),
// 2 (Current) and 3 (Future/staging), across any date range.
//
// The history table stores one row per distinct set of cost values with the
// date range it applied, so "what did this cost on 3 March" is a range lookup,
// not a scan. CHSRC distinguishes an opening value from a real change:
//     B  opening value  -- what it already cost when tracking began
//     S  change observed in the SEQUEL snapshots
//     N  change caught by the nightly capture
//
// Data is maintained by SGPGM/SGCSTCAP, run nightly by SGPGM/SGCSTCAPC.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/Inventory%20Management/CostHistoryInquiry.php

require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$conn  = $i5Connect->getConnection();
$LIB   = 'SGOBJ';
$TBL   = 'SGCSTHST';
$PLANT = 1;
$OPENM = '9999-12-31';

// ── helpers ─────────────────────────────────────────────────────────────────

function ch_h($s)   { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function ch_q($s)   { return str_replace("'", "''", (string)$s); }
function ch_n($v, $d = 5) { return number_format((float)$v, $d); }
function ch_int($v) { return number_format((int)$v); }

function ch_rows($conn, $sql, &$err = null) {
    $err = null;
    $st = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$st) { $err = db2_stmt_errormsg(); return array(); }
    $out = array();
    while ($r = db2_fetch_assoc($st)) $out[] = $r;
    db2_free_stmt($st);
    return $out;
}
function ch_one($conn, $sql, &$err = null) {
    $r = ch_rows($conn, $sql, $err);
    return count($r) ? $r[0] : null;
}

// Source tag -> human label. An opening value is not a price change.
function ch_srcLabel($s) {
    $s = trim((string)$s);
    if ($s === 'B') return 'Opening value';
    if ($s === 'N') return 'Change (nightly)';
    return 'Change';
}

$SETNAME = array(1 => 'Standard', 2 => 'Current', 3 => 'Future');

// ── filters ─────────────────────────────────────────────────────────────────

$defTo   = date('Y-m-d');
$defFrom = date('Y-m-d', strtotime('-12 months'));

$fItem = isset($_GET['item']) ? strtoupper(trim($_GET['item'])) : '';
$fFrom = (isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']))
         ? $_GET['from'] : $defFrom;
$fTo   = (isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']))
         ? $_GET['to']   : $defTo;
if ($fFrom > $fTo) { $t = $fFrom; $fFrom = $fTo; $fTo = $t; }

$fSets = isset($_GET['sets']) && is_array($_GET['sets'])
         ? array_values(array_intersect(array_map('intval', $_GET['sets']), array(1,2,3)))
         : array(1, 2);
if (!count($fSets)) $fSets = array(1, 2);
$setList = implode(',', $fSets);

$fAsOf = (isset($_GET['asof']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['asof']))
         ? $_GET['asof'] : '';

// Category 5 is unused across the item file, so it is hidden by default to
// keep the table narrow. The total unit cost still includes it, and the CSV
// export always carries all five -- nothing is silently dropped from the data.
$showCat5 = isset($_GET['cat5']) && $_GET['cat5'] === '1';

$isCsv = isset($_GET['csv']) && $_GET['csv'] === '1';

// ── data ────────────────────────────────────────────────────────────────────

$matches = array();     // item picker, when the search is not an exact hit
$timeline = array();    // the selected item's history
$itemDesc = '';
$exactItem = '';
$asOfRows = array();
$err = '';

if ($fItem !== '') {
    $q = ch_q($fItem);

    // Exact match first; fall back to a contains-search.
    $ex = ch_one($conn,
        "SELECT TRIM(CHPN) AS CHPN FROM $LIB.$TBL
          WHERE CHPLT = $PLANT AND TRIM(CHPN) = '$q'
          FETCH FIRST 1 ROWS ONLY", $err);

    if ($ex) {
        $exactItem = trim($ex['CHPN']);
    } else {
        $matches = ch_rows($conn,
            "SELECT TRIM(h.CHPN) AS CHPN,
                    COALESCE(TRIM(i.IMIMDS),'') AS DESCR,
                    COUNT(*) AS NROWS,
                    MAX(h.CHEFFD) AS LASTCHG
               FROM $LIB.$TBL h
               LEFT JOIN SGHDSDATA.HDIMST i ON TRIM(i.IMITEM) = TRIM(h.CHPN)
              WHERE h.CHPLT = $PLANT AND UPPER(TRIM(h.CHPN)) LIKE '%$q%'
              GROUP BY TRIM(h.CHPN), COALESCE(TRIM(i.IMIMDS),'')
              ORDER BY 1
              FETCH FIRST 100 ROWS ONLY", $err);
        if (count($matches) === 1) $exactItem = trim($matches[0]['CHPN']);
    }
}

if ($exactItem !== '') {
    $q = ch_q($exactItem);

    $d = ch_one($conn,
        "SELECT COALESCE(TRIM(IMIMDS),'') AS DESCR
           FROM SGHDSDATA.HDIMST WHERE TRIM(IMITEM) = '$q'
          FETCH FIRST 1 ROWS ONLY");
    $itemDesc = $d ? $d['DESCR'] : '';

    // Rows in effect at any point inside the window.
    $timeline = ch_rows($conn,
        "SELECT CHCSET, CHEFFD, CHENDD, CHSRC,
                CHTOTU, CHUCC1, CHUCC2, CHUCC3, CHUCC4, CHUCC5
           FROM $LIB.$TBL
          WHERE CHPLT = $PLANT AND TRIM(CHPN) = '$q'
            AND CHCSET IN ($setList)
            AND CHEFFD <= '$fTo' AND CHENDD >= '$fFrom'
          ORDER BY CHCSET, CHEFFD", $err);
}

// As-of lookup: one row per cost set, whatever was in effect that day.
if ($fAsOf !== '' && $exactItem !== '') {
    $q = ch_q($exactItem);
    $asOfRows = ch_rows($conn,
        "SELECT CHCSET, CHEFFD, CHENDD, CHTOTU, CHSRC,
                CHUCC1, CHUCC2, CHUCC3, CHUCC4, CHUCC5
           FROM $LIB.$TBL
          WHERE CHPLT = $PLANT AND TRIM(CHPN) = '$q'
            AND CHEFFD <= '$fAsOf' AND CHENDD >= '$fAsOf'
          ORDER BY CHCSET");
}

// ── CSV export ──────────────────────────────────────────────────────────────

if ($isCsv && $exactItem !== '') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="cost_history_'
           . preg_replace('/[^A-Za-z0-9_\-]/', '_', $exactItem) . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array('Item','Description','Cost Set','Set Name','Effective',
                        'Through','Total Unit Cost','Cat 1','Cat 2','Cat 3',
                        'Cat 4','Cat 5','Type'));
    foreach ($timeline as $r) {
        $cs = (int)$r['CHCSET'];
        fputcsv($out, array(
            $exactItem, $itemDesc, $cs,
            isset($SETNAME[$cs]) ? $SETNAME[$cs] : '',
            trim($r['CHEFFD']),
            trim($r['CHENDD']) === $OPENM ? 'current' : trim($r['CHENDD']),
            $r['CHTOTU'], $r['CHUCC1'], $r['CHUCC2'], $r['CHUCC3'],
            $r['CHUCC4'], $r['CHUCC5'], ch_srcLabel($r['CHSRC']),
        ));
    }
    fclose($out);
    exit;
}

// ── shape the data for display and for the chart ────────────────────────────

$bySet = array();                    // set => rows, in date order
foreach ($timeline as $r) $bySet[(int)$r['CHCSET']][] = $r;

$stats = array();                    // set => opening/closing/delta/changes
foreach ($bySet as $cs => $rows) {
    $first = $rows[0];
    $last  = $rows[count($rows) - 1];
    $chg   = 0;
    foreach ($rows as $r) if (trim($r['CHSRC']) !== 'B') $chg++;
    $o = (float)$first['CHTOTU'];
    $c = (float)$last['CHTOTU'];
    $stats[$cs] = array(
        'open'    => $o,
        'close'   => $c,
        'delta'   => $c - $o,
        'pct'     => $o != 0 ? ($c - $o) / $o * 100 : null,
        'changes' => $chg,
        'lastchg' => trim($last['CHEFFD']),
        'iscur'   => trim($last['CHENDD']) === $OPENM,
    );
}

// Chart series: a step point at every change, clipped to the window, plus a
// final point at the window end so the last value is drawn to full width.
$series = array();
foreach ($bySet as $cs => $rows) {
    $pts = array();
    foreach ($rows as $r) {
        $d = trim($r['CHEFFD']);
        if ($d < $fFrom) $d = $fFrom;          // clip the opening row
        $pts[] = array('d' => $d, 'v' => (float)$r['CHTOTU'],
                       's' => trim($r['CHSRC']));
    }
    $lastEnd = trim($rows[count($rows) - 1]['CHENDD']);
    $tail = ($lastEnd === $OPENM || $lastEnd > $fTo) ? $fTo : $lastEnd;
    $pts[] = array('d' => $tail,
                   'v' => (float)$rows[count($rows) - 1]['CHTOTU'],
                   's' => 'tail');
    $series[$cs] = $pts;
}

$SERIES_COLOR = array(1 => '#2a78d6', 2 => '#eb6834', 3 => '#1baf7a');
$chartJson = json_encode(array(
    'from'   => $fFrom,
    'to'     => $fTo,
    'series' => $series,
    'names'  => $SETNAME,
    'colors' => $SERIES_COLOR,
));

// A combined view for the change log.
// Each row's change is measured against the previous row of the SAME cost set,
// so the deltas must be computed walking forward in date order. Only after
// that is the list reversed for display, newest first.
$flat = $timeline;
usort($flat, function ($a, $b) {
    $c = strcmp(trim($a['CHEFFD']), trim($b['CHEFFD']));
    return $c !== 0 ? $c : ((int)$a['CHCSET'] - (int)$b['CHCSET']);
});

$prevBySet = array();
foreach ($flat as $i => $r) {
    $cs  = (int)$r['CHCSET'];
    $tot = (float)$r['CHTOTU'];
    $isB = trim($r['CHSRC']) === 'B';
    $flat[$i]['_D'] = null;
    $flat[$i]['_P'] = null;
    if (!$isB && isset($prevBySet[$cs]) && $prevBySet[$cs] != 0) {
        $flat[$i]['_D'] = $tot - $prevBySet[$cs];
        $flat[$i]['_P'] = $flat[$i]['_D'] / $prevBySet[$cs] * 100;
    }
    $prevBySet[$cs] = $tot;
}

// Newest first for display.
$flat = array_reverse($flat);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Item Cost History Inquiry</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; color:#0b0b0b; }

/* chart roles -- house chrome stays blue; marks use validated slots */
.viz-root {
  --surface-1:      #ffffff;
  --text-primary:   #0b0b0b;
  --text-secondary: #52514e;
  --text-muted:     #8a8985;
  --grid:           #e6e6e3;
  --series-1:       #2a78d6;
  --series-2:       #eb6834;
  --series-3:       #1baf7a;
}

.content { padding:18px 20px 40px; }
.pg-hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
          padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
          margin-bottom:14px; }
.pg-hdr h1 { font-size:17px; font-weight:bold; }
.pg-hdr .sub { font-size:11px; opacity:.85; margin-top:2px; font-weight:normal; }

.filters { background:#fff; border-radius:5px; box-shadow:0 1px 4px rgba(0,0,0,.08);
           padding:12px 14px; margin-bottom:14px;
           display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap; }
.fgrp { display:flex; flex-direction:column; gap:3px; }
.fgrp label { font-size:11px; color:var(--text-secondary); font-weight:bold; }
.fgrp input[type=text], .fgrp input[type=date] {
  border:1px solid #c9c9c6; border-radius:3px; padding:5px 8px; font:12px Arial,sans-serif; }
.fgrp input[type=text] { width:190px; text-transform:uppercase; }
.setbox { display:flex; gap:10px; align-items:center; }
.setbox label { font-weight:normal; font-size:12px; display:flex; gap:4px; align-items:center; }
.btn { background:#2a5a8c; color:#fff; border:none; border-radius:3px;
       padding:7px 16px; font:bold 12px Arial,sans-serif; cursor:pointer; }
.btn:hover { background:#1a3d5c; }
.btn-alt { background:#fff; color:#2a5a8c; border:1px solid #2a5a8c; }
a.btn { text-decoration:none; display:inline-block; }

.sect { font-weight:bold; font-size:13px; margin:18px 0 8px; color:#1a3d5c;
        border-bottom:2px solid #90caf9; padding-bottom:3px; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:9px 13px; margin-bottom:12px; font-size:12px; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:8px 13px; margin-bottom:10px; font-size:12px; font-family:monospace; }

.tiles { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:6px; }
.tile { background:var(--surface-1); border-radius:5px; box-shadow:0 1px 4px rgba(0,0,0,.08);
        padding:11px 15px; min-width:210px; flex:1; border-left:4px solid var(--grid); }
.tile .t-lbl { font-size:11px; color:var(--text-secondary); font-weight:bold;
               text-transform:uppercase; letter-spacing:.3px; }
.tile .t-val { font-size:22px; font-weight:bold; margin:3px 0 1px;
               color:var(--text-primary); font-variant-numeric:tabular-nums; }
.tile .t-sub { font-size:11px; color:var(--text-muted); }
.up   { color:#c62828; font-weight:bold; }
.down { color:#2e7d32; font-weight:bold; }
.flat { color:var(--text-muted); }

.chartwrap { background:var(--surface-1); border-radius:5px;
             box-shadow:0 1px 4px rgba(0,0,0,.08); padding:14px 16px 8px;
             margin-bottom:14px; position:relative; }
.legend { display:flex; gap:18px; margin-bottom:6px; font-size:12px;
          color:var(--text-secondary); }
.legend span.k { display:inline-block; width:11px; height:11px; border-radius:2px;
                 margin-right:5px; vertical-align:-1px; }
#tip { position:absolute; pointer-events:none; background:#1a1a19; color:#fff;
       border-radius:4px; padding:7px 10px; font-size:11px; display:none;
       white-space:nowrap; z-index:20; line-height:1.5; }
#tip .tk { display:inline-block; width:8px; height:8px; border-radius:2px; margin-right:5px; }

table.full { border-collapse:collapse; width:100%; background:#fff; border-radius:4px;
             overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
table.full th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
                padding:6px 9px; text-align:left; font-size:11px; white-space:nowrap; }
table.full td { padding:4px 9px; font-size:11px; border-bottom:1px solid #f0f0f0;
                white-space:nowrap; }
table.full tr:nth-child(even) td { background:#fafafa; }
td.num, th.num { text-align:right; font-family:monospace;
                 font-variant-numeric:tabular-nums; }
.scroll { overflow-x:auto; }
.pill { display:inline-block; border-radius:3px; padding:1px 7px; font-size:10px;
        font-weight:bold; color:#fff; }
.tag-open { background:#78716c; }
.tag-chg  { background:#2a5a8c; }
.cur { font-weight:bold; color:#1a3d5c; }
a.itemlink { color:#1565c0; text-decoration:none; font-weight:bold; }
a.itemlink:hover { text-decoration:underline; }
@media print { .filters, .btn { display:none !important; } }
</style>
</head>
<body>
<?php require_once dirname(__FILE__) . '/../SgReportNav.php'; ?>

<div class="content viz-root">

<div class="pg-hdr">
  <h1>Item Cost History Inquiry</h1>
  <div class="sub">Standard, Current and Future cost over time &mdash; plant <?php echo $PLANT; ?></div>
</div>

<form method="get" class="filters">
  <div class="fgrp">
    <label for="item">Item number</label>
    <input type="text" id="item" name="item" value="<?php echo ch_h($fItem); ?>"
           placeholder="full or partial" autofocus>
  </div>
  <div class="fgrp">
    <label for="from">From</label>
    <input type="date" id="from" name="from" value="<?php echo ch_h($fFrom); ?>">
  </div>
  <div class="fgrp">
    <label for="to">To</label>
    <input type="date" id="to" name="to" value="<?php echo ch_h($fTo); ?>">
  </div>
  <div class="fgrp">
    <label>Cost sets</label>
    <div class="setbox">
      <?php foreach ($SETNAME as $k => $nm): ?>
      <label><input type="checkbox" name="sets[]" value="<?php echo $k; ?>"
        <?php echo in_array($k, $fSets) ? 'checked' : ''; ?>>
        <?php echo $k . ' ' . ch_h($nm); ?></label>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="fgrp">
    <label for="asof">Cost as of</label>
    <input type="date" id="asof" name="asof" value="<?php echo ch_h($fAsOf); ?>">
  </div>
  <div class="fgrp">
    <label>Columns</label>
    <div class="setbox">
      <label><input type="checkbox" name="cat5" value="1"
        <?php echo $showCat5 ? 'checked' : ''; ?>> Show Cat 5</label>
    </div>
  </div>
  <div class="fgrp">
    <button class="btn" type="submit">Show history</button>
  </div>
  <?php if ($exactItem !== '' && count($timeline)): ?>
  <div class="fgrp">
    <a class="btn btn-alt" href="?<?php echo ch_h(http_build_query(array(
        'item' => $exactItem, 'from' => $fFrom, 'to' => $fTo,
        'sets' => $fSets, 'csv' => '1'))); ?>">Download CSV</a>
  </div>
  <?php endif; ?>
</form>

<?php if ($err): ?><div class="bad"><?php echo ch_h($err); ?></div><?php endif; ?>

<?php if ($fItem === ''): ?>
  <div class="info">
    Enter an item number to see its cost history. Partial entries list matches.
    The window defaults to the last 12 months; history begins 2025-08-07 for
    Standard, 2025-08-18 for Current, and 2026-08-07 for Future.
  </div>

<?php elseif ($exactItem === ''): ?>
  <?php if (!count($matches)): ?>
    <div class="info">No item matching
      <strong><?php echo ch_h($fItem); ?></strong> has cost history.</div>
  <?php else: ?>
    <div class="sect"><?php echo count($matches); ?> matching items</div>
    <table class="full">
      <tr><th>Item</th><th>Description</th><th class="num">History rows</th>
          <th>Last change</th></tr>
      <?php foreach ($matches as $m): ?>
      <tr>
        <td><a class="itemlink" href="?<?php echo ch_h(http_build_query(array(
              'item' => trim($m['CHPN']), 'from' => $fFrom, 'to' => $fTo,
              'sets' => $fSets))); ?>"><?php echo ch_h(trim($m['CHPN'])); ?></a></td>
        <td><?php echo ch_h($m['DESCR']); ?></td>
        <td class="num"><?php echo ch_int($m['NROWS']); ?></td>
        <td><?php echo ch_h(trim($m['LASTCHG'])); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

<?php elseif (!count($timeline)): ?>
  <div class="info">
    <strong><?php echo ch_h($exactItem); ?></strong>
    <?php echo $itemDesc ? '&mdash; ' . ch_h($itemDesc) : ''; ?>
    has no cost history in the selected sets between
    <?php echo ch_h($fFrom); ?> and <?php echo ch_h($fTo); ?>.
  </div>

<?php else: ?>

  <div class="sect">
    <?php echo ch_h($exactItem); ?>
    <?php echo $itemDesc ? ' &mdash; ' . ch_h($itemDesc) : ''; ?>
  </div>

  <!-- stat tiles ------------------------------------------------------- -->
  <div class="tiles">
    <?php foreach ($stats as $cs => $s):
      $col = isset($SERIES_COLOR[$cs]) ? $SERIES_COLOR[$cs] : '#999';
      $cls = $s['delta'] > 0.000005 ? 'up' : ($s['delta'] < -0.000005 ? 'down' : 'flat');
      $arrow = $s['delta'] > 0.000005 ? '&#9650;' : ($s['delta'] < -0.000005 ? '&#9660;' : '&mdash;');
    ?>
    <div class="tile" style="border-left-color:<?php echo $col; ?>">
      <div class="t-lbl">Cost set <?php echo $cs . ' &middot; ' . ch_h($SETNAME[$cs]); ?></div>
      <div class="t-val"><?php echo ch_n($s['close']); ?></div>
      <div class="t-sub">
        <span class="<?php echo $cls; ?>"><?php echo $arrow; ?>
          <?php echo ch_n(abs($s['delta'])); ?>
          <?php if ($s['pct'] !== null) printf('(%+.1f%%)', $s['pct']); ?></span>
        over the window &middot;
        <?php echo ch_int($s['changes']); ?>
        change<?php echo $s['changes'] == 1 ? '' : 's'; ?>
      </div>
      <div class="t-sub">Last changed <?php echo ch_h($s['lastchg']); ?><?php
        echo $s['iscur'] ? ', still in effect' : ''; ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- chart ------------------------------------------------------------ -->
  <div class="chartwrap">
    <div class="legend">
      <?php foreach ($series as $cs => $p): ?>
      <span><span class="k" style="background:<?php echo $SERIES_COLOR[$cs]; ?>"></span>
        <?php echo ch_h($SETNAME[$cs]); ?> (set <?php echo $cs; ?>)</span>
      <?php endforeach; ?>
      <span style="margin-left:auto;color:var(--text-muted)">
        Total unit cost &mdash; cost holds its value until it changes</span>
    </div>
    <svg id="chart" viewBox="0 0 960 330" preserveAspectRatio="xMidYMid meet"
         style="width:100%;height:auto;display:block" role="img"
         aria-label="Step chart of total unit cost over time by cost set"></svg>
    <div id="tip"></div>
  </div>

  <!-- as-of lookup ----------------------------------------------------- -->
  <?php if ($fAsOf !== ''): ?>
  <div class="sect">Cost in effect on <?php echo ch_h($fAsOf); ?></div>
  <?php if (!count($asOfRows)): ?>
    <div class="info">No cost recorded for this item on that date.</div>
  <?php else: ?>
  <table class="full">
    <tr><th>Set</th><th>Name</th><th class="num">Total unit cost</th>
        <th class="num">Cat 1</th><th class="num">Cat 2</th><th class="num">Cat 3</th>
        <th class="num">Cat 4</th><?php if ($showCat5): ?><th class="num">Cat 5</th><?php endif; ?>
        <th>In effect from</th><th>Through</th></tr>
    <?php foreach ($asOfRows as $r): $cs = (int)$r['CHCSET']; ?>
    <tr>
      <td><?php echo $cs; ?></td>
      <td><?php echo ch_h(isset($SETNAME[$cs]) ? $SETNAME[$cs] : ''); ?></td>
      <td class="num cur"><?php echo ch_n($r['CHTOTU']); ?></td>
      <td class="num"><?php echo ch_n($r['CHUCC1']); ?></td>
      <td class="num"><?php echo ch_n($r['CHUCC2']); ?></td>
      <td class="num"><?php echo ch_n($r['CHUCC3']); ?></td>
      <td class="num"><?php echo ch_n($r['CHUCC4']); ?></td>
      <?php if ($showCat5): ?><td class="num"><?php echo ch_n($r['CHUCC5']); ?></td><?php endif; ?>
      <td><?php echo ch_h(trim($r['CHEFFD'])); ?></td>
      <td><?php echo trim($r['CHENDD']) === $OPENM
            ? '<span class="cur">current</span>' : ch_h(trim($r['CHENDD'])); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
  <?php endif; ?>

  <!-- table view ------------------------------------------------------- -->
  <div class="sect">Change log &mdash; <?php echo count($flat); ?> rows</div>
  <div class="info">
    An <span class="pill tag-open">Opening value</span> row is what the item
    already cost when tracking began &mdash; it is not a price change.
    Percentages compare against the previous row of the same cost set.
    <?php if (!$showCat5): ?>
    Category 5 is hidden because it is unused; it is still included in the
    total unit cost and in the CSV export. Tick <em>Show Cat 5</em> to display it.
    <?php endif; ?>
  </div>
  <div class="scroll">
  <table class="full">
    <tr><th>Effective</th><th>Through</th><th>Set</th><th>Name</th>
        <th class="num">Total unit cost</th><th class="num">Change</th>
        <th class="num">Cat 1</th><th class="num">Cat 2</th><th class="num">Cat 3</th>
        <th class="num">Cat 4</th><?php if ($showCat5): ?><th class="num">Cat 5</th><?php endif; ?>
        <th>Type</th></tr>
    <?php foreach ($flat as $r):
      $cs  = (int)$r['CHCSET'];
      $tot = (float)$r['CHTOTU'];
      $isB = trim($r['CHSRC']) === 'B';
      $d   = $r['_D'];
      $pct = $r['_P'];
      $cls = $d === null ? 'flat' : ($d > 0.000005 ? 'up' : ($d < -0.000005 ? 'down' : 'flat'));
    ?>
    <tr>
      <td><?php echo ch_h(trim($r['CHEFFD'])); ?></td>
      <td><?php echo trim($r['CHENDD']) === $OPENM
            ? '<span class="cur">current</span>' : ch_h(trim($r['CHENDD'])); ?></td>
      <td><span class="pill" style="background:<?php
            echo isset($SERIES_COLOR[$cs]) ? $SERIES_COLOR[$cs] : '#999'; ?>">
            <?php echo $cs; ?></span></td>
      <td><?php echo ch_h(isset($SETNAME[$cs]) ? $SETNAME[$cs] : ''); ?></td>
      <td class="num"><?php echo ch_n($tot); ?></td>
      <td class="num <?php echo $cls; ?>"><?php
          if ($d === null) echo '&mdash;';
          else printf('%+.5f (%+.1f%%)', $d, $pct); ?></td>
      <td class="num"><?php echo ch_n($r['CHUCC1']); ?></td>
      <td class="num"><?php echo ch_n($r['CHUCC2']); ?></td>
      <td class="num"><?php echo ch_n($r['CHUCC3']); ?></td>
      <td class="num"><?php echo ch_n($r['CHUCC4']); ?></td>
      <?php if ($showCat5): ?><td class="num"><?php echo ch_n($r['CHUCC5']); ?></td><?php endif; ?>
      <td><span class="pill <?php echo $isB ? 'tag-open' : 'tag-chg'; ?>">
            <?php echo ch_h(ch_srcLabel($r['CHSRC'])); ?></span></td>
    </tr>
    <?php endforeach; ?>
  </table>
  </div>

  <script>
  (function () {
    var D = <?php echo $chartJson ? $chartJson : '{}'; ?>;
    var svg = document.getElementById('chart');
    if (!svg || !D.series) return;

    var W = 960, H = 330, M = { t: 14, r: 96, b: 34, l: 74 };
    var pw = W - M.l - M.r, ph = H - M.t - M.b;
    var NS = 'http://www.w3.org/2000/svg';

    function days(s) { var p = s.split('-'); return Date.UTC(+p[0], +p[1] - 1, +p[2]) / 864e5; }
    var d0 = days(D.from), d1 = days(D.to);
    if (d1 <= d0) d1 = d0 + 1;

    var lo = Infinity, hi = -Infinity;
    for (var k in D.series) D.series[k].forEach(function (p) {
      if (p.v < lo) lo = p.v; if (p.v > hi) hi = p.v;
    });
    if (!isFinite(lo)) return;
    if (hi === lo) { hi = lo + Math.max(1, Math.abs(lo) * 0.05); lo -= Math.abs(lo) * 0.05; }
    var pad = (hi - lo) * 0.12; lo -= pad; hi += pad;

    var X = function (s) { return M.l + (days(s) - d0) / (d1 - d0) * pw; };
    var Y = function (v) { return M.t + ph - (v - lo) / (hi - lo) * ph; };

    function el(n, a) {
      var e = document.createElementNS(NS, n);
      for (var k in a) e.setAttribute(k, a[k]);
      return e;
    }
    function fmt(v) {
      return v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // grid + y axis -- recessive
    var ticks = 5;
    for (var i = 0; i <= ticks; i++) {
      var v = lo + (hi - lo) * i / ticks, y = Y(v);
      svg.appendChild(el('line', { x1: M.l, y1: y, x2: M.l + pw, y2: y,
        stroke: 'var(--grid)', 'stroke-width': 1 }));
      var t = el('text', { x: M.l - 9, y: y + 4, 'text-anchor': 'end',
        fill: 'var(--text-secondary)', 'font-size': 11,
        'font-family': 'Arial,sans-serif' });
      t.textContent = fmt(v);
      svg.appendChild(t);
    }

    // x axis -- month starts, thinned to avoid collisions
    var months = [], cur = new Date(d0 * 864e5);
    cur = new Date(Date.UTC(cur.getUTCFullYear(), cur.getUTCMonth(), 1));
    while (cur.getTime() / 864e5 <= d1) {
      var dd = cur.getTime() / 864e5;
      if (dd >= d0) months.push({ d: dd, lbl: (cur.getUTCMonth() + 1) + '/'
        + String(cur.getUTCFullYear()).slice(2) });
      cur = new Date(Date.UTC(cur.getUTCFullYear(), cur.getUTCMonth() + 1, 1));
    }
    var step = Math.ceil(months.length / 12) || 1;
    months.forEach(function (m, i) {
      if (i % step) return;
      var x = M.l + (m.d - d0) / (d1 - d0) * pw;
      svg.appendChild(el('line', { x1: x, y1: M.t, x2: x, y2: M.t + ph,
        stroke: 'var(--grid)', 'stroke-width': 1 }));
      var t = el('text', { x: x, y: M.t + ph + 18, 'text-anchor': 'middle',
        fill: 'var(--text-secondary)', 'font-size': 11,
        'font-family': 'Arial,sans-serif' });
      t.textContent = m.lbl;
      svg.appendChild(t);
    });
    svg.appendChild(el('line', { x1: M.l, y1: M.t + ph, x2: M.l + pw, y2: M.t + ph,
      stroke: '#c9c9c6', 'stroke-width': 1 }));

    // step paths -- cost holds until it changes, so H then V, never a diagonal
    var order = Object.keys(D.series).sort();
    order.forEach(function (cs) {
      var pts = D.series[cs], col = D.colors[cs] || '#666', dd = '';
      pts.forEach(function (p, i) {
        var x = X(p.d), y = Y(p.v);
        if (!i) dd += 'M' + x + ',' + y;
        else    dd += 'H' + x + 'V' + y;
      });
      svg.appendChild(el('path', { d: dd, fill: 'none', stroke: col,
        'stroke-width': 2, 'stroke-linejoin': 'round' }));

      // markers at genuine changes only, with a surface ring so overlaps read
      pts.forEach(function (p, i) {
        if (i === pts.length - 1 || p.s === 'tail') return;
        svg.appendChild(el('circle', { cx: X(p.d), cy: Y(p.v), r: 4, fill: col,
          stroke: 'var(--surface-1)', 'stroke-width': 2 }));
      });

      // direct label at the series end
      var last = pts[pts.length - 1];
      var lt = el('text', { x: X(last.d) + 9, y: Y(last.v) + 4,
        fill: col, 'font-size': 11, 'font-weight': 'bold',
        'font-family': 'Arial,sans-serif' });
      lt.textContent = D.names[cs] + ' ' + fmt(last.v);
      svg.appendChild(lt);
    });

    // hover: crosshair + tooltip showing every series at that date
    var hair = el('line', { y1: M.t, y2: M.t + ph, stroke: '#9a9a96',
      'stroke-width': 1, 'stroke-dasharray': '3 3', visibility: 'hidden' });
    svg.appendChild(hair);
    var tip = document.getElementById('tip');
    var hit = el('rect', { x: M.l, y: M.t, width: pw, height: ph,
      fill: 'transparent', style: 'cursor:crosshair' });
    svg.appendChild(hit);

    function valueAt(pts, dd) {
      var v = null;
      for (var i = 0; i < pts.length; i++) {
        if (days(pts[i].d) <= dd) v = pts[i].v; else break;
      }
      return v;
    }

    hit.addEventListener('mousemove', function (ev) {
      var r = svg.getBoundingClientRect();
      var sx = (ev.clientX - r.left) * (W / r.width);
      var dd = d0 + (sx - M.l) / pw * (d1 - d0);
      hair.setAttribute('x1', sx); hair.setAttribute('x2', sx);
      hair.setAttribute('visibility', 'visible');

      var dt = new Date(Math.round(dd) * 864e5);
      var iso = dt.toISOString().slice(0, 10);
      var html = '<div style="color:#c3c2b7;margin-bottom:3px">' + iso + '</div>';
      order.forEach(function (cs) {
        var v = valueAt(D.series[cs], Math.round(dd));
        if (v === null) return;
        html += '<div><span class="tk" style="background:' + D.colors[cs] + '"></span>'
              + D.names[cs] + ' &nbsp;<strong>' + fmt(v) + '</strong></div>';
      });
      tip.innerHTML = html;
      tip.style.display = 'block';
      var px = (sx / W) * r.width;
      tip.style.left = Math.min(px + 14, r.width - tip.offsetWidth - 8) + 'px';
      tip.style.top  = '22px';
    });
    hit.addEventListener('mouseleave', function () {
      hair.setAttribute('visibility', 'hidden');
      tip.style.display = 'none';
    });
  })();
  </script>

<?php endif; ?>

</div><!-- .content -->
</body>
</html>