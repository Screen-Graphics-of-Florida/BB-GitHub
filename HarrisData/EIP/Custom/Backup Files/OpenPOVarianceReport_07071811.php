<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

// NOTE: deliberately NO date_default_timezone_set() here.
// Per CLAUDE.md section 3, the server is physically Eastern and viewer-facing
// times must come from the viewer's own browser (JS), never a server assumption.

$page_title  = 'Open PO Variance Report';
$refreshSecs = 600;
$eiBase      = 'https://portal.screen-graphics.com:5601';

// -- Helpers -------------------------------------------------------------------

function opvr_cYmdToDate($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    $mm = intval(($v % 10000)   / 100);
    $dd = $v % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return '';
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $c * 100 + $yy);
}

function opvr_int($v) {
    return ($v === null || $v === '') ? '' : number_format((int)$v);
}

function opvr_cur2($v) {
    return ($v === null || $v === '') ? '' : '$' . number_format((float)$v, 2);
}

function opvr_cur5($v) {
    return ($v === null || $v === '') ? '' : '$' . number_format((float)$v, 5);
}

function opvr_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// -- Query ---------------------------------------------------------------------
//
//  Converted from SEQUEL to standard DB2 SQL.
//    - SEQUEL field.file qualification -> aliased file.field.
//    - Prefix map: PO* = POPOMS (header), PD* = POPOMD (detail), IP* = HDIPLT (cost).
//    - SEQUEL VALID_DATE/CVTDATE -> raw CYMD integer returned; PHP formats it.
//    - Derived PCEA / VarEa / VarTtl computed in an inner select so the
//      VarTtl <> 0 filter is legal in DB2 (cannot reference a SELECT alias in WHERE).
//    - NULLIF(PDPCPB,0) guards divide-by-zero; those rows fall out via VarTtl <> 0.
//  No trailing semicolon (IBM i DB2 rejects it).

$sql = "
    SELECT * FROM (
        SELECT
            H.POWHS                                                    AS WHS,
            H.PODTEN                                                   AS DATEENTERED,
            D.PDPO                                                     AS PONUM,
            D.PDRQDT                                                   AS DATEREQD,
            D.PDPOL#                                                   AS POLINE,
            D.PDITEM                                                   AS ITEM,
            D.PDIMDS                                                   AS ITEMDESC,
            D.PDQTOR                                                   AS QTYORD,
            D.PDDSCC                                                   AS EXTCOST,
            D.PDPCPB                                                   AS PCSPERPUR,
            D.PDDSCC / NULLIF(D.PDPCPB, 0)                             AS PCEA,
            C.IPPSUM                                                   AS STDCOSTEA,
            (D.PDDSCC / NULLIF(D.PDPCPB, 0)) - C.IPPSUM               AS VAREA,
            ((D.PDDSCC / NULLIF(D.PDPCPB, 0)) - C.IPPSUM) * D.PDQTOR  AS VARTTL
        FROM SGHDSDATA.POPOMD D
        INNER JOIN SGHDSDATA.POPOMS H ON D.PDPO   = H.POPO
        INNER JOIN SGHDSDATA.HDIPLT C ON D.PDITEM = C.IPITEM
                                     AND D.PDPLT  = C.IPPLT
        WHERE D.PDSTAT = 'O'
          AND D.PDPOEC = 'S'
          AND D.PDPOLT <> 'B'
    ) X
    WHERE VARTTL <> 0
    ORDER BY WHS ASC, DATEENTERED DESC, PONUM ASC, POLINE ASC
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

// -- CSV export ----------------------------------------------------------------

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="OpenPOVariance_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array(
        'W/H', 'Date Entered', 'PO #', 'Reqd Date', 'Line', 'Item #',
        'Description', 'Qty Ordered', 'Ext Cost', 'Pieces Per Purch',
        'Purchase Cost/Ea', 'Std Cost/Ea', 'Variance Each', 'Variance Total'
    ));
    foreach ($rows as $r) {
        fputcsv($out, array(
            trim((string)$r['WHS']),
            opvr_cYmdToDate($r['DATEENTERED']),
            trim((string)$r['PONUM']),
            opvr_cYmdToDate($r['DATEREQD']),
            trim((string)$r['POLINE']),
            trim((string)$r['ITEM']),
            trim((string)$r['ITEMDESC']),
            (int)$r['QTYORD'],
            number_format((float)$r['EXTCOST'], 2, '.', ''),
            (int)$r['PCSPERPUR'],
            number_format((float)$r['PCEA'], 5, '.', ''),
            number_format((float)$r['STDCOSTEA'], 5, '.', ''),
            number_format((float)$r['VAREA'], 5, '.', ''),
            number_format((float)$r['VARTTL'], 2, '.', '')
        ));
    }
    fclose($out);
    exit;
}

// -- Dropdown option lists -----------------------------------------------------

$whOptions   = array();
$itemOptions = array();
foreach ($rows as $r) {
    $wh = trim((string)$r['WHS']);
    $it = trim((string)$r['ITEM']);
    if ($wh !== '') $whOptions[$wh]   = true;
    if ($it !== '') $itemOptions[$it] = true;
}
ksort($whOptions,   SORT_NATURAL);
ksort($itemOptions, SORT_NATURAL);

// -- HTML output ---------------------------------------------------------------

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
/* Full-width stretch - override portal table constraints */
body { box-sizing:border-box !important; }
body > table { width:100% !important; max-width:none !important; table-layout:auto !important; }
td.content { width:calc(100vw - 155px) !important; max-width:none !important; box-sizing:border-box !important; }
#opvr-grid { width:100% !important; min-width:100% !important; }
/* Modern gray scheme */
#opvr-grid thead th { background-color:#374151 !important; color:#fff !important;
                      font-weight:bold !important; }
#opvr-grid tbody .opvr-row:nth-child(odd)  { background:#F7F7F7; }
#opvr-grid tbody .opvr-row:nth-child(even) { background:#FFFFFF; }
#opvr-grid tbody .opvr-row:hover           { background:#EFF6FF !important; }
#opvr-grid tbody td a { color:#2563EB !important; text-decoration:none !important;
                        font-weight:bold !important; }
#opvr-grid tbody td a:hover { text-decoration:underline !important; }
#opvr-grid tbody td { color:#111827 !important; }
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
    Open PO Variance Report
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
<p style="color:red;font-weight:bold;padding:8px;"><?php echo opvr_h('SQL Error: ' . $sqlErr); ?></p>
<?php endif; ?>

<style type="text/css">
#opvr-grid thead th { cursor:pointer; user-select:none; white-space:nowrap; }
#opvr-grid thead th:hover { opacity:0.85; }
#opvr-grid thead th.opvr-asc::after  { content:' \25B2'; font-size:9px; }
#opvr-grid thead th.opvr-desc::after { content:' \25BC'; font-size:9px; }
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
      <div class="refresh-dot" id="opvr-dot"></div>
      <span id="opvr-status">Live &ndash; auto-refreshes every 10 min (M&ndash;F, 7:00am&ndash;6:00pm)</span>
      <div class="refresh-progress"><div class="refresh-fill" id="opvr-prog" style="width:100%"></div></div>
      <span>Next refresh in: <strong id="opvr-cd">10:00</strong></span>
      <span class="refresh-pill">Last refresh: <strong id="opvr-last">&ndash;</strong></span>
      <span class="refresh-pill" style="background:#fff3cd;border-color:#f0c060;color:#856404;">As of: <strong id="opvr-asof">&ndash;</strong></span>
    </div>

    <!-- Filter bar -->
    <div style="display:flex;align-items:center;gap:10px;padding:6px 10px;
                background:#F7F7F7;font-size:12px;flex:1;">
      <label style="white-space:nowrap;font-weight:600;">W/H #:
        <select id="opvr-fwh"
                style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($whOptions) as $v): ?>
          <option value="<?php echo opvr_h($v); ?>"><?php echo opvr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="white-space:nowrap;font-weight:600;">Item #:
        <select id="opvr-fitem"
                style="padding:2px 4px;border:1px solid #b0bac8;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($itemOptions) as $v): ?>
          <option value="<?php echo opvr_h($v); ?>"><?php echo opvr_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button id="opvr-clear-btn"
              style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                     border-radius:3px;background:#fff;">Clear</button>
      <b id="opvr-fcount-text" style="margin-left:auto;white-space:nowrap;font-size:12px;">
        <?php echo $rowCount; ?>&nbsp;line<?php echo $rowCount === 1 ? '' : 's'; ?>
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
    <a href="<?php echo opvr_h($exportURL); ?>"
       style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
              font-weight:bold;text-decoration:none;white-space:nowrap;
              text-align:center;display:block;">
      &#8595; Export to Excel
    </a>
  </div>

</div>

<div style="overflow-x:auto;">
<table id="opvr-grid" <?php echo $contentTable; ?> style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th class="colhdr opvr-asc">W/H</th>
      <th class="colhdr">Date<br>Entered</th>
      <th class="colhdr">PO #</th>
      <th class="colhdr">Reqd<br>Date</th>
      <th class="colhdr">Line</th>
      <th class="colhdr">Item #</th>
      <th class="colhdr">Description</th>
      <th class="colhdr">Qty<br>Ordered</th>
      <th class="colhdr">Ext Cost</th>
      <th class="colhdr">Pieces Per<br>Purch</th>
      <th class="colhdr">Purchase<br>Cost/Ea</th>
      <th class="colhdr">Std<br>Cost/Ea</th>
      <th class="colhdr">Variance<br>Each</th>
      <th class="colhdr">Variance<br>Total</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($rows) && !$sqlErr): ?>
    <tr>
      <td colspan="14" class="colcode" align="center" style="padding:20px;">
        No open purchase order lines with a cost variance were found.
      </td>
    </tr>
<?php endif; ?>
<?php foreach ($rows as $r):
    $varTtl  = (float)(isset($r['VARTTL']) ? $r['VARTTL'] : 0);
    $varEa   = (float)(isset($r['VAREA'])  ? $r['VAREA']  : 0);
    $item    = trim((string)$r['ITEM']);
    $desc    = trim((string)$r['ITEMDESC']);
    // Unfavorable (paying over standard) = red; favorable (under standard) = green.
    $ttlStyle = $varTtl > 0 ? 'color:#CC1F20 !important;font-weight:bold !important;'
              : ($varTtl < 0 ? 'color:#1DA032 !important;font-weight:bold !important;' : '');
    $eaStyle  = $varEa  > 0 ? 'color:#CC1F20 !important;'
              : ($varEa  < 0 ? 'color:#1DA032 !important;' : '');
    $itemUrl = $eiBase . '/harris-CGI/ItemSelect.d2w/REPORT'
             . '?baseVar=BaseConfiguration.icl&portal=ITEM'
             . '&eID=' . rawurlencode($eID)
             . '&itemDescription=' . rawurlencode($desc)
             . '&itemNumber=' . rawurlencode($item);
?>
    <tr class="opvr-row">
      <td class="colcode" align="right"><?php echo opvr_h(trim((string)$r['WHS'])); ?></td>
      <td class="colcode" data-val="<?php echo (int)$r['DATEENTERED']; ?>">
        <?php echo opvr_h(opvr_cYmdToDate($r['DATEENTERED'])); ?>
      </td>
      <td class="colcode" align="right" data-val="<?php echo (int)$r['PONUM']; ?>"><?php echo opvr_h(trim((string)$r['PONUM'])); ?></td>
      <td class="colcode" data-val="<?php echo (int)$r['DATEREQD']; ?>">
        <?php echo opvr_h(opvr_cYmdToDate($r['DATEREQD'])); ?>
      </td>
      <td class="colcode" align="right" data-val="<?php echo (int)$r['POLINE']; ?>"><?php echo opvr_h(trim((string)$r['POLINE'])); ?></td>
      <td class="colcode">
        <a href="<?php echo opvr_h($itemUrl); ?>" target="_blank"><?php echo opvr_h($item); ?></a>
      </td>
      <td class="colcode"><?php echo opvr_h($desc); ?></td>
      <td class="colcode" align="right"><?php echo opvr_int($r['QTYORD']); ?></td>
      <td class="colcode" align="right"><?php echo opvr_cur2($r['EXTCOST']); ?></td>
      <td class="colcode" align="right"><?php echo opvr_int($r['PCSPERPUR']); ?></td>
      <td class="colcode" align="right"><?php echo opvr_cur5($r['PCEA']); ?></td>
      <td class="colcode" align="right"><?php echo opvr_cur5($r['STDCOSTEA']); ?></td>
      <td class="colcode" align="right" style="<?php echo $eaStyle; ?>"><?php echo opvr_cur5($r['VAREA']); ?></td>
      <td class="colcode" align="right" style="<?php echo $ttlStyle; ?>"><?php echo opvr_cur2($r['VARTTL']); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

</td>
</tr>
</table>

<script type="text/javascript">
/* Timezone: all viewer-facing times come from the browser clock (CLAUDE.md section 3). */
(function () {
    var lastEl = document.getElementById('opvr-last');
    var asofEl = document.getElementById('opvr-asof');
    var now = new Date();
    if (lastEl) lastEl.textContent = now.toLocaleTimeString('en-US',
        { hour: 'numeric', minute: '2-digit', second: '2-digit', timeZoneName: 'short' });
    if (asofEl) asofEl.textContent = now.toLocaleDateString('en-US',
        { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}());

(function () {
    var AUTO_SECS = <?php echo (int)$refreshSecs; ?>;
    var countdown = AUTO_SECS;
    var dotEl  = document.getElementById('opvr-dot');
    var statEl = document.getElementById('opvr-status');
    var cdEl   = document.getElementById('opvr-cd');
    var progEl = document.getElementById('opvr-prog');
    var tzAbbr = new Date().toLocaleTimeString('en-US', {timeZoneName:'short'}).split(' ').pop();

    // Business-hours window is evaluated in the VIEWER's local time, not a hardcoded zone.
    function inWindow() {
        var d = new Date(), day = d.getDay(), h = d.getHours();
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
            if (statEl) statEl.textContent = 'Live - auto-refreshes every 10 min (M-F, 7:00am-6:00pm ' + tzAbbr + ')';
            if (progEl) progEl.style.width = (countdown / AUTO_SECS * 100).toFixed(1) + '%';
            if (cdEl)   cdEl.textContent   = fmt(countdown);
        } else {
            if (dotEl)  { dotEl.style.background = '#888'; dotEl.style.animation = 'none'; }
            if (statEl) statEl.textContent = 'Auto-refresh paused - outside M-F 7:00am-6:00pm ' + tzAbbr + '. Use Refresh Now.';
            if (progEl) progEl.style.width = '0%';
            if (cdEl)   cdEl.textContent   = '-';
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
    var whIn   = document.getElementById('opvr-fwh');
    var itemIn = document.getElementById('opvr-fitem');
    var clrBtn = document.getElementById('opvr-clear-btn');
    var fcount = document.getElementById('opvr-fcount-text');
    var tbl    = document.getElementById('opvr-grid');
    if (!tbl) return;
    var tbody  = tbl.querySelector('tbody');

    function applyFilters() {
        var wh   = whIn   ? whIn.value   : '';
        var item = itemIn ? itemIn.value : '';
        var rows = tbody.querySelectorAll('tr');
        var shown = 0;
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].cells;
            if (!cells || cells.length < 6) { rows[i].style.display = ''; continue; }
            var whVal   = cells[0].textContent.trim();
            var itemVal = cells[5].textContent.trim();
            var ok = (!wh   || whVal   === wh)
                  && (!item || itemVal === item);
            rows[i].style.display = ok ? '' : 'none';
            if (ok) shown++;
        }
        if (fcount) fcount.textContent = shown + (shown === 1 ? ' line' : ' lines');
    }

    if (whIn)   whIn.addEventListener('change',  applyFilters);
    if (itemIn) itemIn.addEventListener('change', applyFilters);
    if (clrBtn) clrBtn.addEventListener('click', function () {
        if (whIn)   whIn.value   = '';
        if (itemIn) itemIn.value = '';
        applyFilters();
    });
}());

(function () {
    var tbl   = document.getElementById('opvr-grid');
    if (!tbl) return;
    var tbody = tbl.querySelector('tbody');
    var ths   = tbl.querySelectorAll('thead th');
    var state = { col: 0, dir: 1 };

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
            ths[i].className = ths[i].className.replace(/\s*opvr-(asc|desc)/g, '');
        }
        ths[col].className += (state.dir === 1 ? ' opvr-asc' : ' opvr-desc');
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
