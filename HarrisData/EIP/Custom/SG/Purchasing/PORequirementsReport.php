<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$page_title  = 'PO Requirements Report';
$refreshSecs = 600;
$eiBase      = 'https://portal.screen-graphics.com:5601';

// ── Helpers ──────────────────────────────────────────────────────────────────

function porr_cYmdToDate($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    $mm = intval(($v % 10000)   / 100);
    $dd = $v % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return '';
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $c * 100 + $yy);
}

function porr_int($v) {
    return ($v === null || $v === '') ? '' : number_format((int)$v);
}

function porr_dec4($v) {
    return ($v === null || $v === '') ? '' : number_format((float)$v, 4);
}

function porr_cur5($v) {
    return ($v === null || $v === '') ? '' : '$' . number_format((float)$v, 5);
}

function porr_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// ── Query ─────────────────────────────────────────────────────────────────────
//
//  Converted from SEQUEL to standard DB2 SQL.
//  SEQUEL VALID_DATE/CVTDATE -> raw CYMD integer returned; PHP formats it.
//  SEQUEL EDTCDE(L) on IPBAC -> PHP trims and displays as-is.

$sql = "
    SELECT
        T02.IWWHS                                                          AS WH,
        T01.IPBAC                                                          AS BUYCODE,
        T01.IPITEM                                                         AS ITEM,
        T03.IMIMDS                                                         AS ITEMDESC,
        T01.IPSSQ                                                          AS SFTYSTOCK,
        T02.IWMAXQ                                                         AS MAXQTY,
        T02.IWOHQT                                                         AS OHQTY,
        T02.IWQOO                                                          AS QONORD,
        T01.IPCMTO + T02.IWRESQ                                            AS COMO,
        (T02.IWOHQT + T02.IWQOO) - (T01.IPCMTO + T02.IWRESQ + T01.IPSSQ) AS AVAILABLE,
        T01.IPAAU                                                          AS ANNAVGUSG,
        (T02.IWQIYT + T02.IWQKYT + T02.IWQSTD) + (T02.IWQAYT * -1)       AS IPYTDU,
        T02.IWLPDT                                                         AS LSTPODTE,
        T02.IWUOMB                                                         AS BUYUOM,
        T01.IPPSUM                                                         AS PRMSUPLR,
        T03.IMUOMS                                                         AS STOKUOM,
        T02.IWABCC                                                         AS ABCCLASS,
        T02.IWABCO                                                         AS ABCOVERRIDE,
        T02.IWONC                                                          AS ABCOVRDAYS
    FROM SGHDSDATA.HDIPLT T01
    INNER JOIN SGHDSDATA.HDIWHS T02 ON T01.IPITEM = T02.IWITEM
    INNER JOIN SGHDSDATA.HDIMST T03 ON T01.IPITEM = T03.IMITEM
    WHERE T01.IPPTYP <> 'M'
      AND (T02.IWOHQT + T02.IWQOO) - (T01.IPCMTO + T02.IWRESQ + T01.IPSSQ) < 0
      AND T01.IPITEM <> 'PHANTOM'
      AND T02.IWWHS NOT IN (5, 6, 15)
      AND T01.IPPLT = 1
    ORDER BY T01.IPITEM ASC
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
    header('Content-Disposition: attachment; filename="PORequirements_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array(
        'W/H', 'Buyer Code', 'Item #', 'Description', 'Safety Stock',
        'Max Qty', 'On Hand', 'Qty On Order', "Qty Committed To CO's/MO's",
        'Qty Available', 'Annual Avg Usage', 'Usage YTD', 'Last P/O Date',
        'Buy UOM', 'Purch Std Mtl Cost', 'Stock UOM', 'ABC Class',
        'ABC Override', 'ABC OVR Days'
    ));
    foreach ($rows as $r) {
        fputcsv($out, array(
            $r['WH'],
            trim((string)$r['BUYCODE']),
            trim((string)$r['ITEM']),
            trim((string)$r['ITEMDESC']),
            (int)$r['SFTYSTOCK'],
            (int)$r['MAXQTY'],
            (float)$r['OHQTY'],
            (int)$r['QONORD'],
            (int)$r['COMO'],
            number_format((float)$r['AVAILABLE'], 4, '.', ''),
            (int)$r['ANNAVGUSG'],
            (int)$r['IPYTDU'],
            porr_cYmdToDate($r['LSTPODTE']),
            trim((string)$r['BUYUOM']),
            trim((string)$r['PRMSUPLR']),
            trim((string)$r['STOKUOM']),
            trim((string)$r['ABCCLASS']),
            trim((string)$r['ABCOVERRIDE']),
            (int)$r['ABCOVRDAYS']
        ));
    }
    fclose($out);
    exit;
}

// ── Dropdown option lists ─────────────────────────────────────────────────────

$whOptions  = array();
$buyOptions = array();
foreach ($rows as $r) {
    $wh  = trim((string)$r['WH']);
    $buy = trim((string)$r['BUYCODE']);
    if ($wh  !== '') $whOptions[$wh]   = true;
    if ($buy !== '') $buyOptions[$buy] = true;
}
ksort($whOptions,  SORT_NATURAL);
ksort($buyOptions, SORT_NATURAL);

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
/* Full-width stretch — override portal table constraints */
body { box-sizing:border-box !important; }
body > table { width:100% !important; max-width:none !important; table-layout:auto !important; }
td.content { width:calc(100vw - 155px) !important; max-width:none !important; box-sizing:border-box !important; }
#porr-grid { width:100% !important; min-width:100% !important; }
/* Modern gray scheme */
#porr-grid thead th { background-color:#374151 !important; color:#fff !important;
                      font-weight:bold !important; }
#porr-grid tbody .porr-row:nth-child(odd)  { background:#F7F7F7; }
#porr-grid tbody .porr-row:nth-child(even) { background:#FFFFFF; }
#porr-grid tbody .porr-row:hover           { background:#EFF6FF !important; }
#porr-grid tbody td a { color:#2563EB !important; text-decoration:none !important;
                        font-weight:bold !important; }
#porr-grid tbody td a:hover { text-decoration:underline !important; }
#porr-grid tbody td { color:#111827 !important; }
.refresh-fill { background:#3B82F6 !important; }
.refresh-dot  { background:#16A34A !important; }
</style>

<!-- Full-width title bar: escapes the 155px nav offset to span 100vw -->
<div style="position:relative; left:-155px; width:calc(100% + 155px); box-sizing:border-box;
            display:flex; align-items:center;
            padding:10px 14px 10px calc(155px + 14px);
            background:linear-gradient(to right,
                #111827 0%,
                #1F2937 25%,
                #374151 55%,
                #4B5563 78%,
                #6B7280 100%);
            border-bottom:3px solid rgba(0,0,0,0.15);
            gap:10px; margin-bottom:6px;">
  <h1 style="font-size:22px;color:#fff !important;margin:0;flex:1;font-weight:bold !important;
              text-shadow:0 1px 3px rgba(0,0,0,0.4);">
    PO Requirements Report
  </h1>
  <a href="<?php echo htmlspecialchars($_sgnHome . '/Welcome.php?baseVar=' . rawurlencode($_sgnBv) . '&eID=' . rawurlencode($_sgnEid) . '&portal=9999999999', ENT_QUOTES); ?>"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#06B6D4;
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid #0891B2;white-space:nowrap;display:inline-block;">&#8592; Back to EIP</a>
  <a href="https://screen-graphics.com/"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#CC1F20;
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid #8b1010;white-space:nowrap;display:inline-block;">Logout</a>
</div>

<?php if ($sqlErr): ?>
<p style="color:red;font-weight:bold;padding:8px;"><?php echo porr_h('SQL Error: ' . $sqlErr); ?></p>
<?php endif; ?>

<style type="text/css">
#porr-grid thead th { cursor:pointer; user-select:none; white-space:nowrap; }
#porr-grid thead th:hover { opacity:0.85; }
#porr-grid thead th.porr-asc::after  { content:' \25B2'; font-size:9px; }
#porr-grid thead th.porr-desc::after { content:' \25BC'; font-size:9px; }
.refresh-dot { width:8px; height:8px; border-radius:50%;
               animation:pulse 2s infinite; flex-shrink:0; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
.refresh-progress { flex:1; max-width:160px; height:4px; background:rgba(255,255,255,0.18);
                    border-radius:2px; overflow:hidden; }
.refresh-fill { height:100%; border-radius:2px; transition:width 1s linear; }
.refresh-pill { background:#fff; border:1px solid #ddd; border-radius:12px;
                padding:2px 10px; font-size:11px; font-weight:700; white-space:nowrap;
                color:#2563EB !important; }
</style>

<div style="display:flex;align-items:stretch;border-bottom:2px solid #D1D5DB;">

  <!-- Left: two bars stacked -->
  <div style="flex:1;display:flex;flex-direction:column;">

    <!-- Refresh status bar -->
    <div style="background:#2563EB;border-bottom:1px solid #1d4ed8;padding:4px 14px;
                display:flex;align-items:center;gap:14px;font-size:11px;color:#fff;flex:1;">
      <div class="refresh-dot" id="porr-dot"></div>
      <span id="porr-status">Live &ndash; auto-refreshes every 10 min (M&ndash;F, 7:00am&ndash;6:00pm)</span>
      <div class="refresh-progress"><div class="refresh-fill" id="porr-prog" style="width:100%"></div></div>
      <span>Next refresh in: <strong id="porr-cd">10:00</strong></span>
      <span class="refresh-pill">Last refresh: <strong><?php echo date('g:i:s A'); ?></strong></span>
      <span class="refresh-pill" style="background:#fff3cd;border-color:#f0c060;color:#856404;">As of: <?php echo date('D, M j, Y'); ?></span>
    </div>

    <!-- Filter bar -->
    <div style="display:flex;align-items:center;gap:10px;padding:6px 10px;
                background:#F7F7F7;font-size:12px;flex:1;">
      <label style="white-space:nowrap;font-weight:600;">W/H #:
        <select id="porr-fwh"
                style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($whOptions) as $v): ?>
          <option value="<?php echo porr_h($v); ?>"><?php echo porr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="white-space:nowrap;font-weight:600;">Buyer Code:
        <select id="porr-fbuy"
                style="padding:2px 4px;border:1px solid #b0bac8;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($buyOptions) as $v): ?>
          <option value="<?php echo porr_h($v); ?>"><?php echo porr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button id="porr-clear-btn"
              style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                     border-radius:3px;background:#fff;">Clear</button>
      <b id="porr-fcount-text" style="margin-left:auto;white-space:nowrap;font-size:12px;">
        <?php echo $rowCount; ?>&nbsp;item<?php echo $rowCount === 1 ? '' : 's'; ?>
      </b>
    </div>

  </div>

  <!-- Right: Refresh directly above Export, same column -->
  <div style="display:flex;flex-direction:column;align-items:stretch;justify-content:center;
              gap:4px;padding:6px 10px;background:#F7F7F7;border-left:2px solid #D1D5DB;">
    <button onclick="location.reload();"
            style="font-size:12px;padding:3px 14px;cursor:pointer;border:1px solid #4a0f6e;
                   border-radius:3px;background:#7B1FA2;color:#fff;font-weight:bold;
                   white-space:nowrap;text-align:center;">&#x21BB; Refresh</button>
    <a href="<?php echo porr_h($exportURL); ?>"
       style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
              font-weight:bold;text-decoration:none;white-space:nowrap;
              text-align:center;display:block;">
      &#8595; Export to Excel
    </a>
  </div>

</div>

<div style="overflow-x:auto;">
<table id="porr-grid" <?php echo $contentTable; ?> style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th class="colhdr">W/H</th>
      <th class="colhdr">Buyer Code</th>
      <th class="colhdr porr-asc">Item #</th>
      <th class="colhdr">Description</th>
      <th class="colhdr">Safety Stock</th>
      <th class="colhdr">Max Qty</th>
      <th class="colhdr">On Hand</th>
      <th class="colhdr">Qty On Order</th>
      <th class="colhdr">Qty Committed<br>To CO's/MO's</th>
      <th class="colhdr">Qty Available</th>
      <th class="colhdr">Annual Avg<br>Usage</th>
      <th class="colhdr">Usage YTD</th>
      <th class="colhdr">Last P/O Date</th>
      <th class="colhdr">Buy UOM</th>
      <th class="colhdr">Purch Std Mtl Cost</th>
      <th class="colhdr">Stock UOM</th>
      <th class="colhdr">ABC<br>Class</th>
      <th class="colhdr">ABC<br>Override</th>
      <th class="colhdr">ABC<br>OVR Days</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($rows) && !$sqlErr): ?>
    <tr>
      <td colspan="19" class="colcode" align="center" style="padding:20px;">
        No items with negative availability found.
      </td>
    </tr>
<?php endif; ?>
<?php foreach ($rows as $r):
    $available = (float)(isset($r['AVAILABLE']) ? $r['AVAILABLE'] : 0);
    $item      = trim((string)$r['ITEM']);
    $desc      = trim((string)$r['ITEMDESC']);
    $itemUrl   = $eiBase . '/harris-CGI/ItemSelect.d2w/REPORT'
               . '?baseVar=BaseConfiguration.icl&portal=ITEM'
               . '&eID=' . rawurlencode($eID)
               . '&itemDescription=' . rawurlencode($desc)
               . '&itemNumber=' . rawurlencode($item);
?>
    <tr class="porr-row">
      <td class="colcode" align="right"><?php echo porr_h($r['WH']); ?></td>
      <td class="colcode"><?php echo porr_h(trim((string)$r['BUYCODE'])); ?></td>
      <td class="colcode">
        <a href="<?php echo porr_h($itemUrl); ?>" target="_blank"><?php echo porr_h($item); ?></a>
      </td>
      <td class="colcode"><?php echo porr_h($desc); ?></td>
      <td class="colcode" align="right"><?php echo porr_int($r['SFTYSTOCK']); ?></td>
      <td class="colcode" align="right"><?php echo porr_int($r['MAXQTY']); ?></td>
      <td class="colcode" align="right"><?php echo porr_dec4($r['OHQTY']); ?></td>
      <td class="colcode" align="right"><?php echo porr_int($r['QONORD']); ?></td>
      <td class="colcode" align="right"><?php echo porr_int($r['COMO']); ?></td>
      <td class="colcode" align="right"
          style="<?php echo $available < 0 ? 'color:#CC1F20 !important;font-weight:bold !important;' : ''; ?>">
        <?php echo porr_dec4($r['AVAILABLE']); ?>
      </td>
      <td class="colcode" align="right"><?php echo porr_int($r['ANNAVGUSG']); ?></td>
      <td class="colcode" align="right"><?php echo porr_int($r['IPYTDU']); ?></td>
      <td class="colcode" data-val="<?php echo (int)$r['LSTPODTE']; ?>">
        <?php echo porr_h(porr_cYmdToDate($r['LSTPODTE'])); ?>
      </td>
      <td class="colcode"><?php echo porr_h(trim((string)$r['BUYUOM'])); ?></td>
      <td class="colcode" align="right"><?php echo porr_cur5($r['PRMSUPLR']); ?></td>
      <td class="colcode"><?php echo porr_h(trim((string)$r['STOKUOM'])); ?></td>
      <td class="colcode"><?php echo porr_h(trim((string)$r['ABCCLASS'])); ?></td>
      <td class="colcode"><?php echo porr_h(trim((string)$r['ABCOVERRIDE'])); ?></td>
      <td class="colcode" align="right"><?php echo porr_int($r['ABCOVRDAYS']); ?></td>
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
    var dotEl  = document.getElementById('porr-dot');
    var statEl = document.getElementById('porr-status');
    var cdEl   = document.getElementById('porr-cd');
    var progEl = document.getElementById('porr-prog');
    var tzAbbr = new Date().toLocaleTimeString('en-US', {timeZoneName:'short'}).split(' ').pop();

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
    function updateBar() {
        var active = inWindow();
        if (active) {
            if (dotEl)  { dotEl.style.background = '#1a7a3c'; dotEl.style.animation = 'pulse 2s infinite'; }
            if (statEl) statEl.textContent = 'Live – auto-refreshes every 10 min (M–F, 7:00am–6:00pm ' + tzAbbr + ')';
            if (progEl) progEl.style.width = (countdown / AUTO_SECS * 100).toFixed(1) + '%';
            if (cdEl)   cdEl.textContent   = fmt(countdown);
        } else {
            if (dotEl)  { dotEl.style.background = '#888'; dotEl.style.animation = 'none'; }
            if (statEl) statEl.textContent = 'Auto-refresh paused – outside M–F 7:00am–6:00pm ' + tzAbbr + '. Use Refresh Now.';
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
    var whIn   = document.getElementById('porr-fwh');
    var buyIn  = document.getElementById('porr-fbuy');
    var clrBtn = document.getElementById('porr-clear-btn');
    var fcount = document.getElementById('porr-fcount-text');
    var tbl    = document.getElementById('porr-grid');
    if (!tbl) return;
    var tbody  = tbl.querySelector('tbody');

    function applyFilters() {
        var wh  = whIn  ? whIn.value  : '';
        var buy = buyIn ? buyIn.value : '';
        var rows = tbody.querySelectorAll('tr');
        var shown = 0;
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].cells;
            if (!cells || cells.length < 2) { rows[i].style.display = ''; continue; }
            var whVal  = cells[0].textContent.trim();
            var buyVal = cells[1].textContent.trim();
            var ok = (!wh  || whVal  === wh)
                  && (!buy || buyVal === buy);
            rows[i].style.display = ok ? '' : 'none';
            if (ok) shown++;
        }
        if (fcount) fcount.textContent = shown + (shown === 1 ? ' item' : ' items');
    }

    if (whIn)   whIn.addEventListener('change',  applyFilters);
    if (buyIn)  buyIn.addEventListener('change',  applyFilters);
    if (clrBtn) clrBtn.addEventListener('click', function () {
        if (whIn)  whIn.value  = '';
        if (buyIn) buyIn.value = '';
        applyFilters();
    });
}());

(function () {
    var tbl   = document.getElementById('porr-grid');
    if (!tbl) return;
    var tbody = tbl.querySelector('tbody');
    var ths   = tbl.querySelectorAll('thead th');
    var state = { col: 2, dir: 1 };

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
            ths[i].className = ths[i].className.replace(/\s*porr-(asc|desc)/g, '');
        }
        ths[col].className += (state.dir === 1 ? ' porr-asc' : ' porr-desc');
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