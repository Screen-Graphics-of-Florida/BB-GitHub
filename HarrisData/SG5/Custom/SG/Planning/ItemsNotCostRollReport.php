<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$page_title  = 'Items Selected to Not Cost Roll';
$refreshSecs = 1800;
$eiBase      = 'https://portal.screen-graphics.com:5601';

// ── Helpers ──────────────────────────────────────────────────────────────────

function incr_cYmdToDate($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    $mm = intval(($v % 10000)   / 100);
    $dd = $v % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return '';
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $c * 100 + $yy);
}

function incr_int($v) {
    return ($v === null || $v === '') ? '' : number_format((int)$v);
}

function incr_dec4($v) {
    return ($v === null || $v === '') ? '' : number_format((float)$v, 4);
}

function incr_cur2($v) {
    return ($v === null || $v === '') ? '' : '$' . number_format((float)$v, 2);
}

function incr_cymdYear($v) {
    $v = (int)$v;
    if ($v <= 0) return 0;
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    return 1900 + $c * 100 + $yy;
}

// Normalize an IBM i CYMD value to a true YYYYMMDD integer for sorting.
// e.g. 980403 -> 19980403, 1031124 -> 20031124. Returns 0 for blank/invalid.
function incr_cymdKey($v) {
    $v = (int)$v;
    if ($v <= 0) return 0;
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    $mm = intval(($v % 10000)   / 100);
    $dd = $v % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return 0;
    return (1900 + $c * 100 + $yy) * 10000 + $mm * 100 + $dd;
}

function incr_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// ── Query ─────────────────────────────────────────────────────────────────────
//
//  Items flagged to skip cost rollup (IPCSRL/IPCSRC/IPCSRF = 'Y' on HDIPLT).
//  Joined 3-way on item number: HDIPLT.IPITEM = HDIMST.IMITEM = HDIWHS.IWITEM.

$sql = "
    SELECT
        T03.IMIMAC                       AS INACTIVE,
        T01.IPPTYP                       AS PARTTYPE,
        T02.IWWHS                        AS WH,
        T01.IPITEM                       AS ITEM,
        T03.IMIMDS                       AS ITEMDESC,
        T03.IMUOMS                       AS STOKUOM,
        T01.IPALTS                       AS ACCTLOTSIZE,
        T01.IPNBOM                       AS NBRBOM,
        T01.IPNRTG                       AS NBRRTG,
        T02.IWOHQT                       AS OHQTY,
        CAST((C.CMUCC1 + C.CMUCC2 + C.CMUCC3 + C.CMUCC4 + C.CMUCC5) AS DECIMAL(11,2))               AS STDCOST,
        CAST((C.CMUCC1 + C.CMUCC2 + C.CMUCC3 + C.CMUCC4 + C.CMUCC5) * T02.IWOHQT AS DECIMAL(11,2))  AS STDCOSTTOT,
        T01.IPCMTO                       AS CMTOMFG,
        CAST(T02.IWRESQ AS DECIMAL(9,0)) AS RESQTY,
        T02.IWDTLS                       AS DTLSSOLD,
        T02.IWDLWD                       AS DTLSWD,
        T02.IWDLCY                       AS DTLSCYCT
    FROM SGHDSDATA.HDIPLT T01
    INNER JOIN SGHDSDATA.HDIMST T03 ON T01.IPITEM = T03.IMITEM
    INNER JOIN SGHDSDATA.HDIWHS T02 ON T01.IPITEM = T02.IWITEM
    LEFT JOIN SGHDSDATA.HDMCMM C ON T01.IPITEM = C.CMPN
                                 AND T01.IPPLT  = C.CMPLT
                                 AND C.CMCSET   = 1
    WHERE (T01.IPCSRL = 'Y' OR T01.IPCSRC = 'Y' OR T01.IPCSRF = 'Y')
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

$grandTotal = 0;
foreach ($rows as $r) {
    $grandTotal += (float)$r['STDCOSTTOT'];
}

// ── CSV export ────────────────────────────────────────────────────────────────

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ItemsNotCostRoll_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array(
        'Inactive', 'Part Type', 'W/H', 'Item #', 'Description', 'Stock UOM',
        'Acctg Lot Size', '# Active BOMs', '# Active Rtgs', 'Qty On Hand',
        'Std Cost Per', 'Total Std Cost',
        'Qty Committed to Mfg', 'Qty Reserved', 'Date Last Sold',
        'Date Last W/D', 'Date Last Cycle Count'
    ));
    foreach ($rows as $r) {
        fputcsv($out, array(
            trim((string)$r['INACTIVE']),
            trim((string)$r['PARTTYPE']),
            trim((string)$r['WH']),
            trim((string)$r['ITEM']),
            trim((string)$r['ITEMDESC']),
            trim((string)$r['STOKUOM']),
            (int)$r['ACCTLOTSIZE'],
            (int)$r['NBRBOM'],
            (int)$r['NBRRTG'],
            number_format((float)$r['OHQTY'], 4, '.', ''),
            number_format((float)$r['STDCOST'], 2, '.', ''),
            number_format((float)$r['STDCOSTTOT'], 2, '.', ''),
            (int)$r['CMTOMFG'],
            (int)$r['RESQTY'],
            incr_cYmdToDate($r['DTLSSOLD']),
            incr_cYmdToDate($r['DTLSWD']),
            incr_cYmdToDate($r['DTLSCYCT'])
        ));
    }
    fclose($out);
    exit;
}

// ── Dropdown option lists ─────────────────────────────────────────────────────

$whOptions = array();
$ptypOptions = array();
$inactOptions = array();
$soldYearOptions = array();
foreach ($rows as $r) {
    $wh = trim((string)$r['WH']);
    $pt = trim((string)$r['PARTTYPE']);
    $ia = trim((string)$r['INACTIVE']);
    $yr = incr_cymdYear($r['DTLSSOLD']);
    if ($wh !== '') $whOptions[$wh] = true;
    if ($pt !== '') $ptypOptions[$pt] = true;
    $inactOptions[$ia === '' ? '(blank)' : $ia] = true;
    if ($yr > 0) $soldYearOptions[$yr] = true;
}
ksort($whOptions,    SORT_NATURAL);
ksort($ptypOptions,  SORT_NATURAL);
ksort($inactOptions, SORT_NATURAL);
krsort($soldYearOptions, SORT_NUMERIC);

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
#incr-grid { width:100% !important; min-width:100% !important; }
#incr-grid thead th { background-color:#374151 !important; color:#fff !important;
                      font-weight:bold !important; }
#incr-grid tbody .incr-row:nth-child(odd)  { background:#F7F7F7; }
#incr-grid tbody .incr-row:nth-child(even) { background:#FFFFFF; }
#incr-grid tbody .incr-row:hover           { background:#EFF6FF !important; }
#incr-grid tbody td a { color:#2563EB !important; text-decoration:none !important;
                        font-weight:bold !important; }
#incr-grid tbody td a:hover { text-decoration:underline !important; }
#incr-grid tbody td { color:#111827 !important; }
.refresh-fill { background:#3B82F6 !important; }
.refresh-dot  { background:#16A34A !important; }
</style>

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
    Items Selected to Not Cost Roll
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
<p style="color:red;font-weight:bold;padding:8px;"><?php echo incr_h('SQL Error: ' . $sqlErr); ?></p>
<?php endif; ?>

<style type="text/css">
#incr-grid thead th { cursor:pointer; user-select:none; white-space:nowrap; }
#incr-grid thead th:hover { opacity:0.85; }
#incr-grid thead th.incr-asc::after  { content:' \25B2'; font-size:9px; }
#incr-grid thead th.incr-desc::after { content:' \25BC'; font-size:9px; }
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

  <div style="flex:1;display:flex;flex-direction:column;">

    <div style="background:#2563EB;border-bottom:1px solid #1d4ed8;padding:4px 14px;
                display:flex;align-items:center;gap:14px;font-size:11px;color:#fff;flex:1;">
      <div class="refresh-dot" id="incr-dot"></div>
      <span id="incr-status">Live &ndash; auto-refreshes every 30 min (M&ndash;F, 7:00am&ndash;6:00pm)</span>
      <div class="refresh-progress"><div class="refresh-fill" id="incr-prog" style="width:100%"></div></div>
      <span>Next refresh in: <strong id="incr-cd">30:00</strong></span>
      <span class="refresh-pill">Last refresh: <strong><?php echo date('g:i:s A'); ?></strong></span>
      <span class="refresh-pill" style="background:#fff3cd;border-color:#f0c060;color:#856404;">As of: <?php echo date('D, M j, Y'); ?></span>
    </div>

    <div style="display:flex;align-items:center;gap:10px;padding:6px 10px;
                background:#F7F7F7;font-size:12px;flex:1;">
      <label style="white-space:nowrap;font-weight:600;">Part Type:
        <select id="incr-fptyp"
                style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($ptypOptions) as $v): ?>
          <option value="<?php echo incr_h($v); ?>"><?php echo incr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="white-space:nowrap;font-weight:600;">W/H:
        <select id="incr-fwh"
                style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($whOptions) as $v): ?>
          <option value="<?php echo incr_h($v); ?>"><?php echo incr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="white-space:nowrap;font-weight:600;">Inactive:
        <select id="incr-finact"
                style="padding:2px 4px;border:1px solid #b0bac8;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($inactOptions) as $v): ?>
          <option value="<?php echo incr_h($v); ?>"><?php echo incr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="white-space:nowrap;font-weight:600;">Total Std Cost:
        <select id="incr-fcost"
                style="padding:2px 4px;border:1px solid #b0bac8;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <option value="gt">Greater than $0</option>
          <option value="lt">Less than $0</option>
          <option value="eq">Equal to $0</option>
        </select>
      </label>
      <label style="white-space:nowrap;font-weight:600;">Date Last Sold Year:
        <select id="incr-fyear"
                style="padding:2px 4px;border:1px solid #b0bac8;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <option value="blank">No Sales</option>
          <?php foreach (array_keys($soldYearOptions) as $v): ?>
          <option value="<?php echo incr_h($v); ?>"><?php echo incr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button id="incr-clear-btn"
              style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                     border-radius:3px;background:#fff;">Clear</button>
      <b id="incr-grandtotal-text" style="margin-left:auto;white-space:nowrap;font-size:13px;color:#CC1F20;">
        Grand Total: $<?php echo number_format($grandTotal, 2); ?>
      </b>
      <b id="incr-fcount-text" style="white-space:nowrap;font-size:12px;">
        <?php echo $rowCount; ?>&nbsp;item<?php echo $rowCount === 1 ? '' : 's'; ?>
      </b>
    </div>

  </div>

  <div style="display:flex;flex-direction:column;align-items:stretch;justify-content:center;
              gap:4px;padding:6px 10px;background:#F7F7F7;border-left:2px solid #D1D5DB;">
    <button onclick="location.reload();"
            style="font-size:12px;padding:3px 14px;cursor:pointer;border:1px solid #4a0f6e;
                   border-radius:3px;background:#7B1FA2;color:#fff;font-weight:bold;
                   white-space:nowrap;text-align:center;">&#x21BB; Refresh</button>
    <a href="<?php echo incr_h($exportURL); ?>"
       style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
              font-weight:bold;text-decoration:none;white-space:nowrap;
              text-align:center;display:block;">
      &#8595; Export to Excel
    </a>
  </div>

</div>

<div style="overflow-x:auto;">
<table id="incr-grid" <?php echo $contentTable; ?> style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th class="colhdr">Inactive</th>
      <th class="colhdr">Part Type</th>
      <th class="colhdr">W/H</th>
      <th class="colhdr incr-asc">Item #</th>
      <th class="colhdr">Description</th>
      <th class="colhdr">Stock UOM</th>
      <th class="colhdr">Acctg<br>Lot Size</th>
      <th class="colhdr"># Active<br>BOMs</th>
      <th class="colhdr"># Active<br>Rtgs</th>
      <th class="colhdr">Qty On Hand</th>
      <th class="colhdr">Std Cost Per</th>
      <th class="colhdr">Total Std Cost</th>
      <th class="colhdr">Qty Committed<br>to Mfg</th>
      <th class="colhdr">Qty Reserved</th>
      <th class="colhdr">Date Last Sold</th>
      <th class="colhdr">Date Last W/D</th>
      <th class="colhdr">Date Last<br>Cycle Count</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($rows) && !$sqlErr): ?>
    <tr>
      <td colspan="17" class="colcode" align="center" style="padding:20px;">
        No items flagged to skip cost rollup.
      </td>
    </tr>
<?php endif; ?>
<?php foreach ($rows as $r):
    $item    = trim((string)$r['ITEM']);
    $desc    = trim((string)$r['ITEMDESC']);
    $itemUrl = $eiBase . '/harris-CGI/ItemSelect.d2w/REPORT'
             . '?baseVar=BaseConfiguration.icl&portal=ITEM'
             . '&eID=' . rawurlencode($eID)
             . '&itemDescription=' . rawurlencode($desc)
             . '&itemNumber=' . rawurlencode($item);
?>
    <tr class="incr-row">
      <td class="colcode"><?php echo incr_h(trim((string)$r['INACTIVE'])); ?></td>
      <td class="colcode"><?php echo incr_h(trim((string)$r['PARTTYPE'])); ?></td>
      <td class="colcode" align="right"><?php echo incr_h(trim((string)$r['WH'])); ?></td>
      <td class="colcode">
        <a href="<?php echo incr_h($itemUrl); ?>" target="_blank"><?php echo incr_h($item); ?></a>
      </td>
      <td class="colcode"><?php echo incr_h($desc); ?></td>
      <td class="colcode"><?php echo incr_h(trim((string)$r['STOKUOM'])); ?></td>
      <td class="colcode" align="right"><?php echo incr_int($r['ACCTLOTSIZE']); ?></td>
      <td class="colcode" align="right"><?php echo incr_int($r['NBRBOM']); ?></td>
      <td class="colcode" align="right"><?php echo incr_int($r['NBRRTG']); ?></td>
      <td class="colcode" align="right"><?php echo incr_dec4($r['OHQTY']); ?></td>
      <td class="colcode" align="right"><?php echo incr_cur2($r['STDCOST']); ?></td>
      <td class="colcode" align="right"><?php echo incr_cur2($r['STDCOSTTOT']); ?></td>
      <td class="colcode" align="right"><?php echo incr_int($r['CMTOMFG']); ?></td>
      <td class="colcode" align="right"><?php echo incr_int($r['RESQTY']); ?></td>
      <td class="colcode" data-val="<?php echo incr_cymdKey($r['DTLSSOLD']); ?>">
        <?php echo incr_h(incr_cYmdToDate($r['DTLSSOLD'])); ?>
      </td>
      <td class="colcode" data-val="<?php echo incr_cymdKey($r['DTLSWD']); ?>">
        <?php echo incr_h(incr_cYmdToDate($r['DTLSWD'])); ?>
      </td>
      <td class="colcode" data-val="<?php echo incr_cymdKey($r['DTLSCYCT']); ?>">
        <?php echo incr_h(incr_cYmdToDate($r['DTLSCYCT'])); ?>
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
    var dotEl  = document.getElementById('incr-dot');
    var statEl = document.getElementById('incr-status');
    var cdEl   = document.getElementById('incr-cd');
    var progEl = document.getElementById('incr-prog');
    var tzAbbr = new Date().toLocaleTimeString('en-US', {timeZoneName:'short'}).split(' ').pop();

    function getLocalTime() {
        return new Date();
    }
    function inWindow() {
        var now = getLocalTime(), day = now.getDay(), h = now.getHours();
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
            if (statEl) statEl.textContent = 'Live – auto-refreshes every 30 min (M–F, 7:00am–6:00pm ' + tzAbbr + ')';
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
    var ptypIn  = document.getElementById('incr-fptyp');
    var whIn    = document.getElementById('incr-fwh');
    var inactIn = document.getElementById('incr-finact');
    var costIn  = document.getElementById('incr-fcost');
    var yearIn  = document.getElementById('incr-fyear');
    var clrBtn  = document.getElementById('incr-clear-btn');
    var fcount  = document.getElementById('incr-fcount-text');
    var ftotal  = document.getElementById('incr-grandtotal-text');
    var tbl     = document.getElementById('incr-grid');
    if (!tbl) return;
    var tbody   = tbl.querySelector('tbody');

    function costOk(cost, costVal) {
        if (!cost) return true;
        if (cost === 'gt') return costVal > 0;
        if (cost === 'lt') return costVal < 0;
        return costVal === 0;
    }

    function cymdYear(v) {
        v = parseInt(v, 10) || 0;
        if (v <= 0) return 0;
        return Math.floor(v / 10000);
    }

    function yearOk(year, soldYear) {
        if (!year) return true;
        if (year === 'blank') return soldYear === 0;
        return String(soldYear) === year;
    }

    function applyFilters() {
        var ptyp  = ptypIn  ? ptypIn.value  : '';
        var wh    = whIn    ? whIn.value    : '';
        var inact = inactIn ? inactIn.value : '';
        var cost  = costIn  ? costIn.value  : '';
        var year  = yearIn  ? yearIn.value  : '';
        var rows = tbody.querySelectorAll('tr');
        var shown = 0;
        var total = 0;
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].cells;
            if (!cells || cells.length < 2) { rows[i].style.display = ''; continue; }
            var inactVal = cells[0].textContent.trim() === '' ? '(blank)' : cells[0].textContent.trim();
            var ptypVal  = cells[1].textContent.trim();
            var whVal    = cells[2].textContent.trim();
            var costVal  = parseFloat(cells[11].textContent.replace(/[,$]/g, '')) || 0;
            var soldYear = cymdYear(cells[14].getAttribute('data-val'));
            var ok = (!ptyp  || ptypVal  === ptyp)
                  && (!wh    || whVal    === wh)
                  && (!inact || inactVal === inact)
                  && costOk(cost, costVal)
                  && yearOk(year, soldYear);
            rows[i].style.display = ok ? '' : 'none';
            if (ok) {
                shown++;
                total += costVal;
            }
        }
        if (fcount) fcount.textContent = shown + (shown === 1 ? ' item' : ' items');
        if (ftotal) ftotal.textContent = 'Grand Total: $' + total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }

    if (ptypIn)  ptypIn.addEventListener('change', applyFilters);
    if (whIn)    whIn.addEventListener('change', applyFilters);
    if (inactIn) inactIn.addEventListener('change', applyFilters);
    if (costIn)  costIn.addEventListener('change', applyFilters);
    if (yearIn)  yearIn.addEventListener('change', applyFilters);
    if (clrBtn)  clrBtn.addEventListener('click', function () {
        if (ptypIn)  ptypIn.value  = '';
        if (whIn)    whIn.value    = '';
        if (inactIn) inactIn.value = '';
        if (costIn)  costIn.value  = '';
        if (yearIn)  yearIn.value  = '';
        applyFilters();
    });
}());

(function () {
    var tbl   = document.getElementById('incr-grid');
    if (!tbl) return;
    var tbody = tbl.querySelector('tbody');
    var ths   = tbl.querySelectorAll('thead th');
    var state = { col: 3, dir: 1 };

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
            ths[i].className = ths[i].className.replace(/\s*incr-(asc|desc)/g, '');
        }
        ths[col].className += (state.dir === 1 ? ' incr-asc' : ' incr-desc');
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