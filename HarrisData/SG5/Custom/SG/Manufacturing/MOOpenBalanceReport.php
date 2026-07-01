<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$page_title  = 'MO Receipt Report';
$refreshSecs = 600;
$eiBase      = 'https://portal.screen-graphics.com:5601';

// ── Helpers ──────────────────────────────────────────────────────────────────

function morr_fmtDate($v) {
    $s = trim((string)$v);
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m)) {
        return $m[2] . '/' . $m[3] . '/' . $m[1];
    }
    $n = (int)$s;
    if ($n <= 0) return '';
    $c  = intval($n / 1000000);
    $yy = intval(($n % 1000000) / 10000);
    $mm = intval(($n % 10000)   / 100);
    $dd = $n % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return '';
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $c * 100 + $yy);
}

function morr_dateSortVal($v) {
    $s = trim((string)$v);
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s)) {
        return (int)str_replace('-', '', $s);
    }
    return (int)$s;
}

function morr_int($v) {
    return ($v === null || $v === '') ? '' : number_format((int)$v);
}

function morr_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// ── Query ─────────────────────────────────────────────────────────────────────

$sql = "
    WITH BASE AS (
        SELECT
            CASE WHEN OHSTC = 'A' THEN 'Active'
                 WHEN OHSTC = 'T' THEN 'Tagged'
                 ELSE 'N/A' END  AS STATUS,
            OHORD                AS ORDNO,
            OHPN                 AS ITEM,
            OHLRDT               AS LRDATE,
            OHCQTY               AS ORDQTY,
            OHQTYR               AS QTYRCVD,
            OHCQTY - OHQTYR      AS BALANCE
        FROM SGHDSDATA.HDMOHM
        WHERE OHSTC <> 'C'
          AND OHQTYR <> 0
    )
    SELECT
        STATUS,
        ORDNO,
        ITEM,
        LRDATE,
        ORDQTY,
        QTYRCVD,
        BALANCE,
        CASE WHEN BALANCE <> 0 AND STATUS = 'Active' THEN 'Check & Verify'
             WHEN STATUS = 'Tagged'                   THEN 'Close'
             ELSE 'Final Tag & Close' END             AS ACTION
    FROM BASE
    ORDER BY ACTION DESC, LRDATE ASC, STATUS ASC, ORDNO ASC
";

$conn   = $i5Connect->getConnection();
$stmt   = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
$rows   = array();
$sqlErr = '';

if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $rows[] = $r;
    }
    db2_free_stmt($stmt);
} else {
    $sqlErr = db2_stmt_errormsg();
}

$rowCount = count($rows);

// ── CSV export ────────────────────────────────────────────────────────────────

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="MOReceiptReport_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array('Action To Take', 'Status', 'Order #', 'Item #',
                        'Last Date Received', 'Order Qty', 'Qty Received', 'Balance Due'));
    foreach ($rows as $r) {
        fputcsv($out, array(
            trim((string)$r['ACTION']),
            trim((string)$r['STATUS']),
            trim((string)$r['ORDNO']),
            trim((string)$r['ITEM']),
            morr_fmtDate($r['LRDATE']),
            (int)$r['ORDQTY'],
            (int)$r['QTYRCVD'],
            (int)$r['BALANCE']
        ));
    }
    fclose($out);
    exit;
}

// ── Dropdown option lists ─────────────────────────────────────────────────────

$statusOptions = array();
$actionOptions = array();
foreach ($rows as $r) {
    $st = trim((string)$r['STATUS']);
    $ac = trim((string)$r['ACTION']);
    if ($st !== '') $statusOptions[$st] = true;
    if ($ac !== '') $actionOptions[$ac] = true;
}
ksort($statusOptions, SORT_NATURAL);
ksort($actionOptions, SORT_NATURAL);

// ── HTML output ───────────────────────────────────────────────────────────────

$exportParams           = $_GET;
$exportParams['export'] = 'csv';
$exportURL              = '?' . http_build_query($exportParams);

print "\n<html><head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n</head>";
require_once 'Banner.php';
require_once dirname(__FILE__) . '/../SgReportNav.php';

?>
<table <?php echo $baseTable; ?>>
<tr valign="top">
<td class="content">

<style>
table[summary="banner"] { display:none !important; }
body { box-sizing:border-box !important; }
body > table { width:100% !important; max-width:none !important; table-layout:auto !important; }
td.content { width:calc(100vw - 155px) !important; max-width:none !important; box-sizing:border-box !important; }
#morr-grid { width:100% !important; min-width:100% !important; }
#morr-grid thead th { background-color:#1840A8 !important; color:#fff !important;
                      font-weight:bold !important; }
#morr-grid tbody .morr-row:nth-child(odd)  { background:#F7F7F7; }
#morr-grid tbody .morr-row:nth-child(even) { background:#FFFFFF; }
#morr-grid tbody .morr-row:hover           { background:#FFF9C4 !important; }
#morr-grid tbody td a { color:#1840A8 !important; text-decoration:none !important;
                        font-weight:bold !important; }
#morr-grid tbody td a:hover { text-decoration:underline !important; }
.refresh-fill { background:#1840A8 !important; }
.refresh-dot  { background:#1DA032 !important; }
</style>

<!-- Full-width gradient title bar -->
<div style="position:relative; left:-155px; width:calc(100% + 155px); box-sizing:border-box;
            display:flex; align-items:center;
            padding:10px 14px 10px calc(155px + 14px);
            background:linear-gradient(to right,
                #1DA032 0%,
                #1840A8 20%,
                #7B1FA2 40%,
                #CC1F20 60%,
                #E86200 80%,
                #FFD000 100%);
            border-bottom:3px solid rgba(0,0,0,0.22);
            gap:10px; margin-bottom:6px;">
  <h1 style="font-size:22px;color:#fff !important;margin:0;flex:1;font-weight:bold !important;
              text-shadow:0 1px 3px rgba(0,0,0,0.4);">
    MO Receipt Report
  </h1>
  <a href="<?php echo htmlspecialchars($_sgnHome . '/Welcome.php?baseVar=' . rawurlencode($_sgnBv) . '&eID=' . rawurlencode($_sgnEid) . '&portal=9999999999', ENT_QUOTES); ?>"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:rgba(0,0,0,0.28);
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid rgba(255,255,255,0.4);white-space:nowrap;display:inline-block;">&#8592; Back to EIP</a>
  <a href="https://screen-graphics.com/"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#CC1F20;
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid #8b1010;white-space:nowrap;display:inline-block;">Logout</a>
</div>

<?php if ($sqlErr): ?>
<p style="color:red;font-weight:bold;padding:8px;"><?php echo morr_h('SQL Error: ' . $sqlErr); ?></p>
<?php endif; ?>

<style type="text/css">
#morr-grid thead th { cursor:pointer; user-select:none; white-space:nowrap; }
#morr-grid thead th:hover { opacity:0.85; }
#morr-grid thead th.morr-asc::after  { content:' \25B2'; font-size:9px; }
#morr-grid thead th.morr-desc::after { content:' \25BC'; font-size:9px; }
.refresh-dot { width:8px; height:8px; border-radius:50%;
               animation:pulse 2s infinite; flex-shrink:0; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
.refresh-progress { flex:1; max-width:160px; height:4px; background:#dde3f0;
                    border-radius:2px; overflow:hidden; }
.refresh-fill { height:100%; border-radius:2px; transition:width 1s linear; }
.refresh-pill { background:#fff; border:1px solid #ddd; border-radius:12px;
                padding:2px 10px; font-size:11px; font-weight:600; white-space:nowrap; }
</style>

<div style="display:flex;align-items:stretch;border-bottom:2px solid #E86200;">
  <div style="flex:1;display:flex;flex-direction:column;">

    <!-- Refresh status bar -->
    <div style="background:#F7F7F7;border-bottom:1px solid #E86200;padding:4px 14px;
                display:flex;align-items:center;gap:14px;font-size:11px;color:#444;flex:1;">
      <div class="refresh-dot" id="morr-dot"></div>
      <span id="morr-status">Live &ndash; auto-refreshes every 10 min (M&ndash;F, 7:00am&ndash;6:00pm CT)</span>
      <div class="refresh-progress"><div class="refresh-fill" id="morr-prog" style="width:100%"></div></div>
      <span>Next refresh in: <strong id="morr-cd">10:00</strong></span>
      <span class="refresh-pill">Last refresh: <strong><?php echo date('g:i:s A'); ?></strong></span>
      <span class="refresh-pill" style="background:#fff3e0;border-color:#E86200;color:#8b4000;">As of: <?php echo date('D, M j, Y'); ?></span>
    </div>

    <!-- Filter bar -->
    <div style="display:flex;align-items:center;gap:10px;padding:6px 10px;
                background:#F7F7F7;font-size:12px;flex:1;">
      <label style="white-space:nowrap;font-weight:600;">Action:
        <select id="morr-fac"
                style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($actionOptions) as $v): ?>
          <option value="<?php echo morr_h($v); ?>"><?php echo morr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="white-space:nowrap;font-weight:600;">Status:
        <select id="morr-fst"
                style="padding:2px 4px;border:1px solid #b0bac8;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($statusOptions) as $v): ?>
          <option value="<?php echo morr_h($v); ?>"><?php echo morr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button id="morr-clear-btn"
              style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                     border-radius:3px;background:#fff;">Clear</button>
      <b id="morr-fcount-text" style="margin-left:auto;white-space:nowrap;font-size:12px;">
        <?php echo $rowCount; ?>&nbsp;order<?php echo $rowCount === 1 ? '' : 's'; ?>
      </b>
    </div>

  </div>

  <!-- Right: Refresh directly above Export -->
  <div style="display:flex;flex-direction:column;align-items:stretch;justify-content:center;
              gap:4px;padding:6px 10px;background:#F7F7F7;border-left:2px solid #E86200;">
    <button onclick="location.reload();"
            style="font-size:12px;padding:3px 14px;cursor:pointer;border:1px solid #4a0f6e;
                   border-radius:3px;background:#7B1FA2;color:#fff;font-weight:bold;
                   white-space:nowrap;text-align:center;">&#x21BB; Refresh</button>
    <a href="<?php echo morr_h($exportURL); ?>"
       style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
              font-weight:bold;text-decoration:none;white-space:nowrap;
              text-align:center;display:block;">
      &#8595; Export to Excel
    </a>
  </div>

</div>

<div style="overflow-x:auto;">
<table id="morr-grid" <?php echo $contentTable; ?> style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th class="colhdr morr-desc">Action To Take</th>
      <th class="colhdr">Sts Cde</th>
      <th class="colhdr">Order #</th>
      <th class="colhdr">Item #</th>
      <th class="colhdr">Last Date<br>Received</th>
      <th class="colhdr">Order<br>Qty</th>
      <th class="colhdr">Qty<br>Received</th>
      <th class="colhdr">Balance<br>Due</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($rows) && !$sqlErr): ?>
    <tr>
      <td colspan="8" class="colcode" align="center" style="padding:20px;">
        No open MO balances found.
      </td>
    </tr>
<?php endif; ?>
<?php foreach ($rows as $r):
    $balance = (int)$r['BALANCE'];
    $status  = trim((string)$r['STATUS']);
    $action  = trim((string)$r['ACTION']);
    $ord     = trim((string)$r['ORDNO']);
    $item    = trim((string)$r['ITEM']);

    $ordUrl  = $eiBase . '/harris-CGI/SelectMfgOrder.d2w/REPORT'
             . '?baseVar=BaseConfiguration.icl&portal=MFGMGMT'
             . '&eID='        . rawurlencode($eID)
             . '&mfgOrder='   . rawurlencode($ord)
             . '&plantNumber=1';
    $itemUrl = $eiBase . '/harris-CGI/ItemSelect.d2w/REPORT'
             . '?baseVar=BaseConfiguration.icl&portal=ITEM'
             . '&eID='        . rawurlencode($eID)
             . '&itemNumber=' . rawurlencode($item);

    if ($action === 'Check & Verify') {
        $badgeStyle = 'background:#fff3e0;color:#8b4000;border:1px solid #E86200;';
    } elseif ($action === 'Close') {
        $badgeStyle = 'background:#e3f0ff;color:#1840A8;border:1px solid #1840A8;';
    } else {
        $badgeStyle = 'background:#e8f5e9;color:#155724;border:1px solid #1DA032;';
    }
?>
    <tr class="morr-row">
      <td class="colcode" align="center">
        <span style="<?php echo $badgeStyle; ?> padding:2px 8px;border-radius:10px;
                     font-size:11px;font-weight:700;white-space:nowrap;display:inline-block;">
          <?php echo morr_h($action); ?>
        </span>
      </td>
      <td class="colcode"><?php echo morr_h($status); ?></td>
      <td class="colcode">
        <a href="<?php echo morr_h($ordUrl); ?>" target="_blank"><?php echo morr_h($ord); ?></a>
      </td>
      <td class="colcode">
        <a href="<?php echo morr_h($itemUrl); ?>" target="_blank"><?php echo morr_h($item); ?></a>
      </td>
      <td class="colcode" align="center" data-val="<?php echo morr_dateSortVal($r['LRDATE']); ?>">
        <?php echo morr_h(morr_fmtDate($r['LRDATE'])); ?>
      </td>
      <td class="colcode" align="right"><?php echo morr_int($r['ORDQTY']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['QTYRCVD']); ?></td>
      <td class="colcode" align="right"
          style="<?php echo $balance !== 0 ? 'color:#CC1F20;font-weight:bold;' : ''; ?>">
        <?php echo morr_int($balance); ?>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

</td>
</tr>
</table>

<script type="text/javascript">
(function () {
    var AUTO_SECS = <?php echo (int)$refreshSecs; ?>;
    var countdown = AUTO_SECS;
    var dotEl  = document.getElementById('morr-dot');
    var statEl = document.getElementById('morr-status');
    var cdEl   = document.getElementById('morr-cd');
    var progEl = document.getElementById('morr-prog');

    function getCtTime() {
        var now = new Date();
        return new Date(now.toLocaleString('en-US', { timeZone: 'America/Chicago' }));
    }
    function inWindow() {
        var ct = getCtTime(), day = ct.getDay(), h = ct.getHours();
        return day >= 1 && day <= 5 && h >= 7 && h < 18;
    }
    function fmt(s) {
        var tot = Math.max(0, s);
        var d   = Math.floor(tot / 86400);
        var h   = Math.floor((tot % 86400) / 3600);
        var m   = Math.floor((tot % 3600) / 60);
        var r   = tot % 60;
        var mm  = (m < 10 ? '0' : '') + m;
        var ss  = (r < 10 ? '0' : '') + r;
        if (d > 0) return d + (d === 1 ? ' day ' : ' days ') + (h < 10 ? '0' : '') + h + ':' + mm + ':' + ss;
        if (h > 0) return h + ':' + mm + ':' + ss;
        return m + ':' + ss;
    }
    function updateBar() {
        var active = inWindow();
        if (active) {
            if (dotEl)  { dotEl.style.background = '#1a7a3c'; dotEl.style.animation = 'pulse 2s infinite'; }
            if (statEl) statEl.textContent = 'Live – auto-refreshes every 10 min (M–F, 7:00am–6:00pm CT)';
            if (progEl) progEl.style.width = (countdown / AUTO_SECS * 100).toFixed(1) + '%';
            if (cdEl)   cdEl.textContent   = fmt(countdown);
        } else {
            if (dotEl)  { dotEl.style.background = '#888'; dotEl.style.animation = 'none'; }
            if (statEl) statEl.textContent = 'Auto-refresh paused – outside M–F 7:00am–6:00pm CT. Use Refresh Now.';
            if (progEl) progEl.style.width = '0%';
            if (cdEl)   cdEl.textContent   = '—';
            countdown = AUTO_SECS;
        }
    }
    setInterval(function () {
        if (inWindow()) {
            countdown--;
            if (countdown <= 0) { location.reload(); return; }
        }
        updateBar();
    }, 1000);
    updateBar();
}());

(function () {
    var stIn   = document.getElementById('morr-fst');
    var acIn   = document.getElementById('morr-fac');
    var clrBtn = document.getElementById('morr-clear-btn');
    var fcount = document.getElementById('morr-fcount-text');
    var tbl    = document.getElementById('morr-grid');
    if (!tbl) return;
    var tbody  = tbl.querySelector('tbody');

    function applyFilters() {
        var st  = stIn ? stIn.value : '';
        var ac  = acIn ? acIn.value : '';
        var rows = tbody.querySelectorAll('tr');
        var shown = 0;
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].cells;
            if (!cells || cells.length < 2) { rows[i].style.display = ''; continue; }
            var acVal = cells[0].textContent.trim();
            var stVal = cells[1].textContent.trim();
            var ok = (!ac || acVal === ac)
                  && (!st || stVal === st);
            rows[i].style.display = ok ? '' : 'none';
            if (ok) shown++;
        }
        if (fcount) fcount.textContent = shown + (shown === 1 ? ' order' : ' orders');
    }

    if (stIn)   stIn.addEventListener('change',  applyFilters);
    if (acIn)   acIn.addEventListener('change',  applyFilters);
    if (clrBtn) clrBtn.addEventListener('click', function () {
        if (stIn)  stIn.value  = '';
        if (acIn)  acIn.value  = '';
        applyFilters();
    });
}());

(function () {
    var tbl   = document.getElementById('morr-grid');
    if (!tbl) return;
    var tbody = tbl.querySelector('tbody');
    var ths   = tbl.querySelectorAll('thead th');
    var state = { col: 0, dir: -1 };

    function cellVal(td) {
        if (td.hasAttribute('data-val')) {
            return parseFloat(td.getAttribute('data-val')) || 0;
        }
        var t = td.textContent.replace(/[,$]/g, '').trim();
        if (t === '') return null;
        var n = parseFloat(t);
        return isNaN(n) ? t.toLowerCase() : n;
    }

    function sortBy(col) {
        state.dir = (state.col === col) ? -state.dir : 1;
        state.col = col;

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        rows.sort(function (a, b) {
            var va = cellVal(a.cells[col]);
            var vb = cellVal(b.cells[col]);
            if (va === null && vb === null) return 0;
            if (va === null) return 1;
            if (vb === null) return -1;
            if (va < vb) return -state.dir;
            if (va > vb) return  state.dir;
            return 0;
        });
        rows.forEach(function (r) { tbody.appendChild(r); });

        for (var i = 0; i < ths.length; i++) {
            ths[i].className = ths[i].className.replace(/\s*morr-(asc|desc)/g, '');
        }
        ths[col].className += (state.dir === 1 ? ' morr-asc' : ' morr-desc');
    }

    for (var i = 0; i < ths.length; i++) {
        (function (col) {
            ths[col].addEventListener('click', function () { sortBy(col); });
        }(i));
    }
}());
</script>

</body>
</html>
