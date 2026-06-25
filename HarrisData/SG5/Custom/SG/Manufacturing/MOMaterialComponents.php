<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
date_default_timezone_set('America/Chicago');

// ── Order Status filter ───────────────────────────────────────────────────────
$validStatuses  = array('A', 'C', 'P', 'T');
$statusLabels   = array(
    'A' => 'Active', 'C' => 'Closed',
    'P' => 'Planned', 'T' => 'Final Tagged',
);
$selectedStatuses = array();
if (isset($_GET['status']) && is_array($_GET['status'])) {
    foreach ($_GET['status'] as $s) {
        if (in_array($s, $validStatuses, true)) {
            $selectedStatuses[] = $s;
        }
    }
}
if (empty($selectedStatuses)) {
    $selectedStatuses = array('A');
}

// ── Closed date-range filter (only applied when Closed is selected) ────────────
$closedSel  = in_array('C', $selectedStatuses, true);
$closedFrom = date('Y-m-d', strtotime('-7 days'));
$closedTo   = date('Y-m-d');
if ($closedSel) {
    if (isset($_GET['cfrom']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['cfrom'])) {
        $closedFrom = $_GET['cfrom'];
    }
    if (isset($_GET['cto']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['cto'])) {
        $closedTo = $_GET['cto'];
    }
}

// ── Auto-refresh: M–F, 7 am–5 pm Eastern ─────────────────────────────────────
$estNow      = new DateTime('now', new DateTimeZone('America/New_York'));
$estDow      = (int)$estNow->format('N'); // 1=Mon … 7=Sun
$estHour     = (int)$estNow->format('G');
$autoRefresh = ($estDow >= 1 && $estDow <= 5 && $estHour >= 7 && $estHour < 17);
$refreshSecs = 600;
$refreshedAt = date('m/d/Y g:i:s A');

// ── Helpers ───────────────────────────────────────────────────────────────────
function momc_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function momc_dec($v, $dp = 4) {
    if ($v === null || $v === '') return '';
    return number_format((float)$v, $dp);
}
function momc_curr($v, $dp = 5) {
    if ($v === null || $v === '') return '';
    $n = (float)$v;
    return ($n < 0 ? '-' : '') . '$' . number_format(abs($n), $dp);
}
function momc_date($v) {
    if (!$v) return '';
    $d = DateTime::createFromFormat('Y-m-d', (string)$v);
    return $d ? $d->format('m/d/Y') : (string)$v;
}
function momc_pct($v) {
    if ($v === null || $v === '') return '';
    return number_format((float)$v, 2) . '%';
}

// ── SQL ───────────────────────────────────────────────────────────────────────
$inList = implode(',', array_map(
    function ($s) { return "'" . $s . "'"; },
    $selectedStatuses
));

$dateWhere = $closedSel
    ? "WHERE DATE(T01.CRTSTP) BETWEEN '$closedFrom' AND '$closedTo'"
    : '';

$sql = "
    SELECT CRORD, CRSEQN, WCWC, WCDESC, CRPPN, CRCPN,
           IMIMDS, IMUOMS, OHCQTY, QTYREPORTD, CRQPER,
           QTYRQD, CRQTYI, PCTDIFF, UNITCOST, MTLVARCST,
           REPORTDATE
    FROM (
        SELECT CRORD, CRSEQN, WCWC, WCDESC, CRPPN, CRCPN,
               IMIMDS, IMUOMS, OHCQTY, QTYREPORTD, CRQPER,
               QTYRQD, CRQTYI, UNITCOST, REPORTDATE,
               CASE WHEN CRQTYI = 0   THEN 100.0
                    WHEN QTYRQD  = 0   THEN 0.0
                    WHEN QTYRQD <> CRQTYI
                         THEN (QTYRQD - CRQTYI) / QTYRQD * 100.0
                    ELSE 0.0 END                AS PCTDIFF,
               (QTYRQD - CRQTYI) * UNITCOST    AS MTLVARCST
        FROM (
            SELECT T01.CRORD,
                   T01.CRSEQN,
                   T05.WCWC,
                   T05.WCDESC,
                   T01.CRPPN,
                   T01.CRCPN,
                   T02.IMIMDS,
                   T02.IMUOMS,
                   T06.OHCQTY,
                   SUM(T04.LDQTYC + T04.LDRSCR)                    AS QTYREPORTD,
                   T01.CRQPER,
                   T06.OHCQTY * T01.CRQPER                          AS QTYRQD,
                   T01.CRQTYI,
                   T03.CMUCC1 + T03.CMUCC2 + T03.CMUCC3
                       + T03.CMUCC4 + T03.CMUCC5                    AS UNITCOST,
                   DATE(T01.CRTSTP)                                  AS REPORTDATE
            FROM SGHDSDATA.HDMCRM T01
            JOIN SGHDSDATA.HDIMST  T02 ON T01.CRCPN  = T02.IMITEM
            JOIN SGHDSDATA.HDMCMM  T03 ON T01.CRCPN  = T03.CMPN
                                      AND T03.CMCSET  = 1
            JOIN SGHDSDATA.HDMLDM  T04 ON T01.CRORD   = T04.LDORD
                                      AND T01.CRSEQN  = T04.LDSEQN
            JOIN SGHDSDATA.HDMWCM  T05 ON T04.LDWC   = T05.WCWC
            JOIN SGHDSDATA.HDMOHM  T06 ON T01.CRORD   = T06.OHORD
                                      AND T06.OHSTC  IN ($inList)
            $dateWhere
            GROUP BY T01.CRORD, T01.CRSEQN, T05.WCWC, T05.WCDESC,
                     T01.CRPPN, T01.CRCPN, T02.IMIMDS, T02.IMUOMS,
                     T06.OHCQTY, T01.CRQPER, T01.CRQTYI,
                     T03.CMUCC1, T03.CMUCC2, T03.CMUCC3,
                     T03.CMUCC4, T03.CMUCC5, DATE(T01.CRTSTP)
        ) AS BASE1
    ) AS BASE2
    WHERE PCTDIFF <> 0
    ORDER BY CRORD ASC, CRCPN ASC, CRPPN ASC, CRSEQN ASC
";

$conn   = $i5Connect->getConnection();
$rows   = array();
$sqlErr = '';

// ── CSV / Excel export ────────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if ($stmt) {
        while ($r = db2_fetch_assoc($stmt)) { $rows[] = $r; }
        db2_free_stmt($stmt);
    }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="MOMaterialComponents_'
        . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array(
        'MO #', 'Seq', 'Work Ctr', 'WC Description', 'Parent Part #',
        'Component Item #', 'Item Description', 'UOM', 'Order Qty',
        'Qty Reported', 'Qty Per', 'Qty Required', 'Qty Issued',
        'Pct Diff %', 'Unit Cost', 'Material Var Cost', 'Date Reported',
    ));
    foreach ($rows as $r) {
        fputcsv($out, array(
            trim((string)$r['CRORD']),
            (int)$r['CRSEQN'],
            trim((string)$r['WCWC']),
            trim((string)$r['WCDESC']),
            trim((string)$r['CRPPN']),
            trim((string)$r['CRCPN']),
            trim((string)$r['IMIMDS']),
            trim((string)$r['IMUOMS']),
            number_format((float)$r['OHCQTY'],    4, '.', ''),
            number_format((float)$r['QTYREPORTD'], 4, '.', ''),
            number_format((float)$r['CRQPER'],    4, '.', ''),
            number_format((float)$r['QTYRQD'],    4, '.', ''),
            number_format((float)$r['CRQTYI'],    4, '.', ''),
            number_format((float)$r['PCTDIFF'],   2, '.', ''),
            number_format((float)$r['UNITCOST'],  5, '.', ''),
            number_format((float)$r['MTLVARCST'], 5, '.', ''),
            momc_date($r['REPORTDATE']),
        ));
    }
    fclose($out);
    exit;
}

// ── Normal page load ──────────────────────────────────────────────────────────
$stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) { $rows[] = $r; }
    db2_free_stmt($stmt);
} else {
    $sqlErr = db2_stmt_errormsg();
}
$rowCount = count($rows);

$eiBase = 'https://portal.screen-graphics.com:5601';

$exportParams           = $_GET;
$exportParams['export'] = 'csv';
$exportURL              = '?' . http_build_query($exportParams);

$jsRows = array();
foreach ($rows as $r) {
    $jsRows[] = array('moNum' => trim((string)$r['CRORD']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MO Material Components Issues</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px;
       background: #edf1f7; color: #1a2233; }

.topbar { background: #003087; color: #fff; padding: 8px 16px;
          display: flex; align-items: center;
          justify-content: space-between; }
.topbar h1 { font-size: 15px; font-weight: 700; }
.topbar-right { display: flex; align-items: center; gap: 10px;
                font-size: 11px; color: #b8cfee; flex-shrink: 0; }
.btn-refresh { background: #1a5276; color: #fff; border: 1px solid #2980b9;
               padding: 5px 14px; border-radius: 3px; font-size: 12px;
               cursor: pointer; white-space: nowrap; }
.btn-refresh:hover { background: #21618c; }

.filter-bar { background: #fff; border-bottom: 1px solid #c8d0de;
              padding: 8px 16px; display: flex; align-items: center;
              gap: 14px; flex-wrap: wrap; }
.filter-lbl { font-size: 11px; font-weight: 700; color: #5a6478;
              text-transform: uppercase; letter-spacing: .5px;
              white-space: nowrap; }
.filter-checks { display: flex; gap: 10px; flex-wrap: wrap; }
.filter-checks label { font-size: 12px; display: flex; align-items: center;
                       gap: 4px; cursor: pointer; white-space: nowrap; }
.filter-checks input[type=checkbox] { cursor: pointer; }
.btn-apply { background: #003087; color: #fff; border: none;
             padding: 5px 16px; border-radius: 3px; font-size: 12px;
             font-weight: 700; cursor: pointer; }
.btn-apply:hover { background: #002060; }
.btn-export { background: #1a7a1a; color: #fff; border: none;
              padding: 5px 14px; border-radius: 3px; font-size: 12px;
              cursor: pointer; text-decoration: none;
              display: inline-block; white-space: nowrap; }
.btn-export:hover { background: #155a15; color: #fff; }
.meta-info { display: flex; gap: 12px; align-items: center;
             font-size: 11px; color: #5a6478; margin-left: auto;
             flex-wrap: wrap; }
.meta-info b { color: #003087; }
.closed-date-grp { display: flex; align-items: center; gap: 6px;
                   border-left: 2px solid #c8d0de; padding-left: 14px;
                   flex-wrap: wrap; }
.closed-date-grp label { font-size: 11px; font-weight: 700; color: #5a6478;
                         white-space: nowrap; }
.closed-date-grp input[type=date] { border: 1px solid #b0bac8; border-radius: 3px;
                                     padding: 4px 7px; font-size: 12px;
                                     background: #fff; color: #1a2233; width: 132px; }
.closed-date-grp input[type=date]:focus { outline: none; border-color: #2980b9; }
.closed-date-note { font-size: 10px; color: #b06000; font-style: italic;
                    white-space: nowrap; }

.content { padding: 10px 14px; }
.tbl-wrap { overflow-x: auto; overflow-y: auto;
            max-height: calc(100vh - 90px); }
table { width: 100%; border-collapse: collapse; min-width: 1500px; }
thead th { background: #003087; color: #fff; padding: 5px 7px;
           font-size: 11px; font-weight: 700; white-space: nowrap;
           cursor: pointer; user-select: none;
           position: sticky; top: 0; z-index: 2; }
thead th:first-child { position: sticky; top: 0; left: 0; z-index: 3; }
thead th:hover { background: #002060; }
thead th.sort-asc::after  { content: ' \25B2'; font-size: 9px; }
thead th.sort-desc::after { content: ' \25BC'; font-size: 9px; }
th.L, td.L { text-align: left; }
th.R, td.R { text-align: right; }
th.C, td.C { text-align: center; }
td { padding: 4px 7px; border-bottom: 1px solid #e4e8ef;
     white-space: nowrap; vertical-align: middle; font-size: 12px; }
tr:nth-child(even) td { background: #f4f7fc; }
tr:hover td { background: #eaf0fb; }
td:first-child { position: sticky; left: 0; background: #fff; z-index: 1; }
tr:nth-child(even) td:first-child { background: #f4f7fc; }
tr:hover td:first-child { background: #eaf0fb; }
.mo-link { color: #003087; text-decoration: none; font-weight: 700; }
.mo-link:hover { text-decoration: underline; color: #0050c0; }
.pct-short { color: #cc0000; font-weight: 700; }
.cost-neg  { color: #cc0000; font-weight: 700; }
.err   { background: #fdd; color: #900; padding: 8px 12px;
         border-radius: 4px; margin-bottom: 10px;
         font-family: monospace; font-size: 12px; }
.empty { text-align: center; padding: 40px; color: #888; font-size: 14px; }

/* ── Standard refresh bar (matches BookingsDashboard.php) ── */
.refresh-bar { background: #e8f0fb; border-bottom: 1px solid #bdd0ee; padding: 4px 14px; display: flex; align-items: center; gap: 14px; font-size: 11px; color: #5a6478; flex-shrink: 0; }
.refresh-dot { width: 8px; height: 8px; border-radius: 50%; background: #1a7a3c; animation: pulse 2s infinite; flex-shrink: 0; }
.refresh-dot--off { background: #94a3b8; animation: none; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
.refresh-progress { flex: 1; max-width: 160px; height: 4px; background: #d0dced; border-radius: 2px; overflow: hidden; }
.refresh-fill { height: 100%; background: #0055b3; border-radius: 2px; transition: width 1s linear; }
.refresh-pill { background: #fff; border: 1px solid #c8d0de; border-radius: 12px; padding: 2px 10px; font-size: 11px; font-weight: 600; white-space: nowrap; }
</style>
</head>
<body>

<div class="topbar">
  <h1>MO Material Components Issues</h1>
</div>
<?php if ($autoRefresh): ?>
<div class="refresh-bar">
  <div class="refresh-dot"></div>
  <span>Live &ndash; auto-refreshes every 10 min (M&ndash;F, 7:00am&ndash;5:00pm ET)</span>
  <div class="refresh-progress"><div class="refresh-fill" id="momc-prog" style="width:100%"></div></div>
  <span>Next refresh in: <strong id="momc-cd">10m 0s</strong></span>
  <span class="refresh-pill">Last refresh: <strong><?php echo date('g:i:s A'); ?></strong></span>
  <span class="refresh-pill" style="background:#fff0d0;border-color:#f0c060;color:#885500;">As of: <?php echo date('D, M j, Y'); ?></span>
</div>
<?php else: ?>
<div class="refresh-bar">
  <div class="refresh-dot refresh-dot--off"></div>
  <span>Auto-refresh off (outside M&ndash;F 7:00am&ndash;5:00pm ET)</span>
  <span style="flex:1"></span>
  <span class="refresh-pill">Last refresh: <strong><?php echo date('g:i:s A'); ?></strong></span>
  <span class="refresh-pill" style="background:#fff0d0;border-color:#f0c060;color:#885500;">As of: <?php echo date('D, M j, Y'); ?></span>
</div>
<?php endif; ?>

<form method="get" action="">
<div class="filter-bar">
  <span class="filter-lbl">Order Status:</span>
  <div class="filter-checks">
    <?php foreach ($statusLabels as $code => $label): ?>
    <label>
      <input type="checkbox" name="status[]"
             value="<?php echo momc_h($code); ?>"
             <?php echo in_array($code, $selectedStatuses, true) ? 'checked' : ''; ?>>
      <?php echo momc_h($label); ?>
    </label>
    <?php endforeach; ?>
  </div>
  <div class="closed-date-grp">
    <label>Closed From:</label>
    <input type="date" name="cfrom"
           value="<?php echo momc_h($closedFrom); ?>">
    <label>To:</label>
    <input type="date" name="cto"
           value="<?php echo momc_h($closedTo); ?>">
    <span class="closed-date-note">&#9432; applies to Closed orders only</span>
  </div>
  <button type="submit" class="btn-apply">Apply Filter</button>
  <a class="btn-export" href="<?php echo momc_h($exportURL); ?>">
    &#x21E9; Export to Excel
  </a>
  <div class="meta-info">
    <span><b><?php echo $rowCount; ?></b>
      row<?php echo $rowCount !== 1 ? 's' : ''; ?></span>
  </div>
</div>
</form>

<div class="content">

<?php if ($sqlErr): ?>
<div class="err">Query error: <?php echo momc_h($sqlErr); ?></div>
<?php endif; ?>

<div class="tbl-wrap">
<table id="momc-grid">
  <thead>
    <tr>
      <th class="L">MO #</th>
      <th class="R">Seq</th>
      <th class="L">Work Ctr</th>
      <th class="L">WC Description</th>
      <th class="L">Parent Part #</th>
      <th class="L">Component Item #</th>
      <th class="L">Item Description</th>
      <th class="C">UOM</th>
      <th class="R">Order Qty</th>
      <th class="R">Qty Reported</th>
      <th class="R">Qty Per</th>
      <th class="R">Qty Required</th>
      <th class="R">Qty Issued</th>
      <th class="R">Pct Diff</th>
      <th class="R">Unit Cost</th>
      <th class="R">Material Var Cost</th>
      <th class="C">Date Reported</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($rows) && !$sqlErr): ?>
  <tr>
    <td colspan="17" class="empty">
      No records found for the selected status filter.
    </td>
  </tr>
<?php endif; ?>
<?php foreach ($rows as $idx => $r):
    $mo       = trim((string)$r['CRORD']);
    $pctDiff  = (float)$r['PCTDIFF'];
    $mtvCost  = (float)$r['MTLVARCST'];
    $dtRaw    = (string)$r['REPORTDATE'];
    $pctClass = $pctDiff > 0 ? ' pct-short' : '';
    $cstClass = $mtvCost > 0 ? ' cost-neg'  : '';
?>
  <tr>
    <td class="L">
      <a class="mo-link"
         href="javascript:openMO(<?php echo $idx; ?>)">
        <?php echo momc_h($mo); ?>
      </a>
    </td>
    <td class="R"
        data-val="<?php echo (int)$r['CRSEQN']; ?>">
      <?php echo (int)$r['CRSEQN']; ?>
    </td>
    <td class="L"><?php echo momc_h(trim((string)$r['WCWC'])); ?></td>
    <td class="L"><?php echo momc_h(trim((string)$r['WCDESC'])); ?></td>
    <td class="L"><?php echo momc_h(trim((string)$r['CRPPN'])); ?></td>
    <td class="L"><?php echo momc_h(trim((string)$r['CRCPN'])); ?></td>
    <td class="L"><?php echo momc_h(trim((string)$r['IMIMDS'])); ?></td>
    <td class="C"><?php echo momc_h(trim((string)$r['IMUOMS'])); ?></td>
    <td class="R"
        data-val="<?php echo (float)$r['OHCQTY']; ?>">
      <?php echo momc_dec($r['OHCQTY'], 4); ?>
    </td>
    <td class="R"
        data-val="<?php echo (float)$r['QTYREPORTD']; ?>">
      <?php echo momc_dec($r['QTYREPORTD'], 4); ?>
    </td>
    <td class="R"
        data-val="<?php echo (float)$r['CRQPER']; ?>">
      <?php echo momc_dec($r['CRQPER'], 4); ?>
    </td>
    <td class="R"
        data-val="<?php echo (float)$r['QTYRQD']; ?>">
      <?php echo momc_dec($r['QTYRQD'], 4); ?>
    </td>
    <td class="R"
        data-val="<?php echo (float)$r['CRQTYI']; ?>">
      <?php echo momc_dec($r['CRQTYI'], 4); ?>
    </td>
    <td class="R<?php echo $pctClass; ?>"
        data-val="<?php echo $pctDiff; ?>">
      <?php echo momc_pct($r['PCTDIFF']); ?>
    </td>
    <td class="R"
        data-val="<?php echo (float)$r['UNITCOST']; ?>">
      <?php echo momc_curr($r['UNITCOST'], 5); ?>
    </td>
    <td class="R<?php echo $cstClass; ?>"
        data-val="<?php echo $mtvCost; ?>">
      <?php echo momc_curr($r['MTLVARCST'], 5); ?>
    </td>
    <td class="C"
        data-val="<?php echo momc_h($dtRaw); ?>">
      <?php echo momc_h(momc_date($dtRaw)); ?>
    </td>
  </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
</div>

<script>
var EI_BASE   = <?php echo json_encode($eiBase); ?>;
var EI_EID    = <?php echo json_encode($eID); ?>;
var MOMC_ROWS = <?php echo json_encode(array_values($jsRows)); ?>;

function openMO(idx) {
    var r = MOMC_ROWS[idx];
    if (!r || !r.moNum) return;
    window.open(
        EI_BASE + '/harris-CGI/SelectMfgOrder.d2w/REPORT'
        + '?baseVar=BaseConfiguration.icl&portal=MFGMGMT'
        + '&eID='       + EI_EID
        + '&mfgOrder='  + encodeURIComponent(r.moNum)
        + '&plantNumber=1',
        '_blank'
    );
}

// ── Sortable columns ──────────────────────────────────────────────────────────
(function () {
    var tbl   = document.getElementById('momc-grid');
    if (!tbl) return;
    var tbody = tbl.querySelector('tbody');
    var ths   = tbl.querySelectorAll('thead th');
    var state = { col: 0, dir: 1 };

    function cellVal(td) {
        if (td.hasAttribute('data-val')) {
            var raw = td.getAttribute('data-val');
            var n   = parseFloat(raw);
            return isNaN(n) ? raw.toLowerCase() : n;
        }
        var t = td.textContent.replace(/[\$,%]/g, '').trim();
        if (t === '' || t === '—') return null;
        var n = parseFloat(t);
        return isNaN(n) ? t.toLowerCase() : n;
    }

    function sortBy(col) {
        state.dir = (state.col === col) ? -state.dir : 1;
        state.col = col;
        var trs = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        trs.sort(function (a, b) {
            var va = cellVal(a.cells[col]);
            var vb = cellVal(b.cells[col]);
            if (va === null && vb === null) return 0;
            if (va === null) return  1;
            if (vb === null) return -1;
            if (va < vb) return -state.dir;
            if (va > vb) return  state.dir;
            return 0;
        });
        trs.forEach(function (r) { tbody.appendChild(r); });
        for (var i = 0; i < ths.length; i++) {
            ths[i].className = ths[i].className
                .replace(/\s*sort-(asc|desc)/g, '');
        }
        ths[col].className += (state.dir === 1 ? ' sort-asc' : ' sort-desc');
    }

    for (var i = 0; i < ths.length; i++) {
        (function (col) {
            ths[col].addEventListener('click', function () { sortBy(col); });
        }(i));
    }
    // Show initial sort indicator: MO# ascending
    ths[0].className += ' sort-asc';
}());

// ── Auto-refresh countdown ────────────────────────────────────────────────────
<?php if ($autoRefresh): ?>
(function () {
    var total = <?php echo (int)$refreshSecs; ?>;
    var secs  = total;
    var cd    = document.getElementById('momc-cd');
    var prog  = document.getElementById('momc-prog');
    function fmt(s) {
        var tot = Math.max(0, s);
        var d = Math.floor(tot / 86400);
        var h = Math.floor((tot % 86400) / 3600);
        var m = Math.floor((tot % 3600) / 60);
        var r = tot % 60;
        var mm = (m < 10 ? '0' : '') + m;
        var ss = (r < 10 ? '0' : '') + r;
        if (d > 0) return d + (d === 1 ? ' day ' : ' days ') + (h < 10 ? '0' : '') + h + ':' + mm + ':' + ss;
        if (h > 0) return h + ':' + mm + ':' + ss;
        return m + ':' + ss;
    }
    function tick() {
        if (secs <= 0) { location.reload(); return; }
        if (cd)   cd.textContent   = fmt(secs);
        if (prog) prog.style.width = (secs / total * 100).toFixed(1) + '%';
        secs--;
        setTimeout(tick, 1000);
    }
    tick();
}());
<?php endif; ?>
</script>

</body>
</html>
