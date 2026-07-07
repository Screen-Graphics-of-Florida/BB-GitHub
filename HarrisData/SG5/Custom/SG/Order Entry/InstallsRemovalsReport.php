<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/New_York');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$page_title  = 'Installs & Removals Report';
$eiBase      = 'https://portal.screen-graphics.com:5601';

// ── Helpers ──────────────────────────────────────────────────────────────────

function inrm_cYmdToDate($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    $mm = intval(($v % 10000)   / 100);
    $dd = $v % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return '';
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $c * 100 + $yy);
}

function inrm_dateToCymd($y, $m, $d) {
    $yr = (int)$y - 1900;
    $c  = intval($yr / 100);
    $yy = $yr % 100;
    return $c * 1000000 + $yy * 10000 + (int)$m * 100 + (int)$d;
}

function inrm_int($v) {
    return ($v === null || $v === '') ? '0' : number_format((int)$v);
}

function inrm_cur($v) {
    $n = (float)$v;
    return ($n < 0 ? '-$' : '$') . number_format(abs($n), 2);
}

function inrm_pct($v) {
    return number_format((float)$v, 2) . '%';
}

function inrm_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// ── Date filter (defaults to 1st / last day of current month) ────────────────

$defFrom = date('Y-m-01');
$defTo   = date('Y-m-t');

$fFrom = (isset($_GET['ffrom']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['ffrom'])) ? $_GET['ffrom'] : $defFrom;
$fTo   = (isset($_GET['fto'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['fto']))   ? $_GET['fto']   : $defTo;

list($fy, $fm, $fd) = explode('-', $fFrom);
list($ty, $tm, $td) = explode('-', $fTo);
$fromCymd = inrm_dateToCymd($fy, $fm, $fd);
$toCymd   = inrm_dateToCymd($ty, $tm, $td);

// ── Query ─────────────────────────────────────────────────────────────────────
//
//  Item-class / item-prefix rule per CSDataIntegrityDashboard.php Q6:
//  installs/removals/trip/travel lines should carry ODPCLS = 'INST', but the
//  class code and the item prefix sometimes disagree, so both are checked.
//
//  HDINVC (invoices) has no line-level reference back to OEORDT - IVORLN is
//  always 0 (confirmed against live data). Invoicing is an order-level
//  concept here, so every invoice # tied to the CO is shown, not a single
//  guessed match per line.

$sql = "
    SELECT
        h.OESHTO                                                   AS SHIPTO,
        TRIM(c.CMCNA1)                                              AS SHIPNAME,
        h.\"OEORD#\"                                                 AS ORDNUM,
        TRIM(h.OEORST)                                              AS ORDSTAT,
        TRIM(d.ODITEM)                                              AS ITEM,
        TRIM(d.ODIMDS)                                              AS ITEMDESC,
        CAST(d.ODQSTD AS INTEGER)                                   AS QTYSHIP,
        d.ODSLPR                                                    AS SLPR,
        d.ODCOST                                                    AS UCOST
    FROM SGHDSDATA.OEORDT d
    INNER JOIN SGHDSDATA.OEORHD h ON d.\"ODORD#\" = h.\"OEORD#\"
    LEFT JOIN SGHDSDATA.HDCUST c ON h.OESHTO = c.CMCUST
    WHERE d.ODDTLI BETWEEN $fromCymd AND $toCymd
      AND ( TRIM(d.ODPCLS) = 'INST'
         OR TRIM(d.ODITEM) LIKE 'INST%'
         OR TRIM(d.ODITEM) LIKE 'TRIP%'
         OR TRIM(d.ODITEM) LIKE 'TRAV%'
         OR TRIM(d.ODITEM) LIKE 'REM%' )
    ORDER BY h.OESHTO, TRIM(c.CMCNA1), h.\"OEORD#\", d.\"ODORL#\"
";

$conn   = $i5Connect->getConnection();
$stmt   = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
$rows   = array();
$sqlErr = '';
$diagCols = array();

if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $rows[] = $r;
    }
    db2_free_stmt($stmt);
} else {
    $sqlErr = db2_stmt_errormsg();

    // Diagnostic fallback: dump actual OEORDT columns instead of a blank page.
    $diagStmt = db2_exec($conn,
        "SELECT COLUMN_NAME FROM QSYS2.SYSCOLUMNS
         WHERE TABLE_SCHEMA = 'SGHDSDATA' AND TABLE_NAME = 'OEORDT'
         ORDER BY ORDINAL_POSITION");
    if ($diagStmt) {
        while ($dr = db2_fetch_assoc($diagStmt)) {
            $diagCols[] = $dr['COLUMN_NAME'];
        }
        db2_free_stmt($diagStmt);
    }
}

// ── Invoice #s per CO (order-level - HDINVC has no line reference) ──────────

$invoicesByOrder = array();
if (!empty($rows)) {
    $ordNums = array();
    foreach ($rows as $r) {
        $ordNums[(int)$r['ORDNUM']] = true;
    }
    $ordList = implode(',', array_keys($ordNums));
    $invSql  = "SELECT IVORD, IVAINV, IVIVDT FROM SGHDSDATA.HDINVC
                WHERE IVORD IN ($ordList) ORDER BY IVORD, IVAINV";
    $invStmt = db2_exec($conn, $invSql, array('cursor' => DB2_SCROLLABLE));
    if ($invStmt) {
        while ($ir = db2_fetch_assoc($invStmt)) {
            $ord = (int)$ir['IVORD'];
            if (!isset($invoicesByOrder[$ord])) $invoicesByOrder[$ord] = array();
            $invoicesByOrder[$ord][] = array(
                'num'  => trim((string)$ir['IVAINV']),
                'date' => (int)$ir['IVIVDT']
            );
        }
        db2_free_stmt($invStmt);
    }
}

// ── Group by Ship-To, compute extensions + subtotals ─────────────────────────

$groups = array();
$gtQty = 0; $gtSale = 0.0; $gtCost = 0.0;

foreach ($rows as $r) {
    $shipto = trim((string)$r['SHIPTO']);
    if (!isset($groups[$shipto])) {
        $groups[$shipto] = array(
            'name' => trim((string)$r['SHIPNAME']),
            'rows' => array(),
            'qty' => 0, 'sale' => 0.0, 'cost' => 0.0
        );
    }
    $qty  = (int)$r['QTYSHIP'];
    $sale = (float)$r['SLPR'] * $qty;
    $cost = (float)$r['UCOST'] * $qty;

    $r['QTY']     = $qty;
    $r['EXTSALE'] = $sale;
    $r['EXTCOST'] = $cost;

    $groups[$shipto]['rows'][] = $r;
    $groups[$shipto]['qty']  += $qty;
    $groups[$shipto]['sale'] += $sale;
    $groups[$shipto]['cost'] += $cost;

    $gtQty  += $qty;
    $gtSale += $sale;
    $gtCost += $cost;
}

$rowCount = count($rows);

// ── CSV export ────────────────────────────────────────────────────────────────

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $hideSub = isset($_GET['hidesub']) && $_GET['hidesub'] === '1';
    $subOnly = isset($_GET['subonly']) && $_GET['subonly'] === '1';

    $gtProfit = $gtSale - $gtCost;
    $gtGm     = $gtSale != 0 ? ($gtProfit / $gtSale * 100) : 0;

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="InstallsRemovals_' . ($subOnly ? 'Subtotals_' : '') . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');

    if ($subOnly) {
        fputcsv($out, array('Ship-To #', 'Ship-To Customer Name', 'Qty Ship', 'Total Sale', 'Total Cost', 'Profit/Loss', 'GM%'));
        fputcsv($out, array(
            '', 'Grand Total', $gtQty,
            number_format($gtSale, 2, '.', ''),
            number_format($gtCost, 2, '.', ''),
            number_format($gtProfit, 2, '.', ''),
            number_format($gtGm, 2, '.', '')
        ));
        foreach ($groups as $shipto => $g) {
            $gProfit = $g['sale'] - $g['cost'];
            $gGm     = $g['sale'] != 0 ? ($gProfit / $g['sale'] * 100) : 0;
            fputcsv($out, array(
                $shipto, $g['name'], $g['qty'],
                number_format($g['sale'], 2, '.', ''),
                number_format($g['cost'], 2, '.', ''),
                number_format($gProfit, 2, '.', ''),
                number_format($gGm, 2, '.', '')
            ));
        }
        fclose($out);
        exit;
    }

    fputcsv($out, array(
        'Ship-To #', 'Ship-To Customer Name', 'CO #', 'Invoice #', 'Item Desc',
        'Qty Ship', 'Total Sale', 'Total Cost', 'Profit/Loss', 'GM%'
    ));
    fputcsv($out, array(
        '', 'Grand Total', '', '', '', $gtQty,
        number_format($gtSale, 2, '.', ''),
        number_format($gtCost, 2, '.', ''),
        number_format($gtProfit, 2, '.', ''),
        number_format($gtGm, 2, '.', '')
    ));

    foreach ($groups as $shipto => $g) {
        foreach ($g['rows'] as $r) {
            $profit  = $r['EXTSALE'] - $r['EXTCOST'];
            $gm      = $r['EXTSALE'] != 0 ? ($profit / $r['EXTSALE'] * 100) : 0;
            $ordKey  = (int)$r['ORDNUM'];
            $invList = isset($invoicesByOrder[$ordKey]) ? $invoicesByOrder[$ordKey] : array();
            $invNums = implode('; ', array_map(function ($iv) { return $iv['num']; }, $invList));
            fputcsv($out, array(
                $shipto, $g['name'], trim((string)$r['ORDNUM']), $invNums,
                trim((string)$r['ITEMDESC']), $r['QTY'],
                number_format($r['EXTSALE'], 2, '.', ''),
                number_format($r['EXTCOST'], 2, '.', ''),
                number_format($profit, 2, '.', ''),
                number_format($gm, 2, '.', '')
            ));
        }
        if (!$hideSub) {
            $gProfit = $g['sale'] - $g['cost'];
            $gGm     = $g['sale'] != 0 ? ($gProfit / $g['sale'] * 100) : 0;
            fputcsv($out, array(
                $shipto, $g['name'] . ' - Subtotal', '', '', '', $g['qty'],
                number_format($g['sale'], 2, '.', ''),
                number_format($g['cost'], 2, '.', ''),
                number_format($gProfit, 2, '.', ''),
                number_format($gGm, 2, '.', '')
            ));
        }
    }
    fclose($out);
    exit;
}

// ── HTML output ───────────────────────────────────────────────────────────────

$preserveParams = $_GET;
unset($preserveParams['ffrom'], $preserveParams['fto'], $preserveParams['export']);

$exportParams           = $preserveParams;
$exportParams['ffrom']  = $fFrom;
$exportParams['fto']    = $fTo;
$exportParams['export'] = 'csv';
$exportURL              = '?' . http_build_query($exportParams);

$exportSubOnlyParams           = $exportParams;
$exportSubOnlyParams['subonly'] = '1';
$exportSubOnlyURL               = '?' . http_build_query($exportSubOnlyParams);

$clearParams = $preserveParams;
$clearURL    = '?' . http_build_query($clearParams);

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
#inrm-grid { width:100% !important; min-width:100% !important; }
#inrm-grid thead th { background-color:#374151 !important; color:#fff !important;
                      font-weight:bold !important; white-space:nowrap; }
#inrm-grid tbody .inrm-row:nth-child(odd)  { background:#F7F7F7; }
#inrm-grid tbody .inrm-row:nth-child(even) { background:#FFFFFF; }
#inrm-grid tbody .inrm-row:hover           { background:#EFF6FF !important; }
#inrm-grid tbody td a { color:#2563EB !important; text-decoration:none !important;
                        font-weight:bold !important; }
#inrm-grid tbody td a:hover { text-decoration:underline !important; }
#inrm-grid tbody td { color:#111827 !important; }
#inrm-grid tbody td.inrm-negative { color:#CC1F20 !important; font-weight:bold !important; }
#inrm-grid tbody tr.inrm-zerocost td { background:#FFFF00 !important; }
#inrm-grid tbody tr.inrm-zerocost:hover td { background:#FFEB3B !important; }
#inrm-grid tr.inrm-sub td { background:#DCE8FF !important; font-weight:bold; color:#111827 !important;
                            border-top:1px solid #D1D5DB; border-bottom:2px solid #D1D5DB; }
#inrm-grid tr.inrm-gt td  { background:#374151 !important; color:#fff !important; font-weight:bold;
                            font-size:13px; border-bottom:3px solid #2563EB !important; }
#inrm-grid.inrm-hide-sub tr.inrm-sub { display:none; }
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
    Installs &amp; Removals Report
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
<div style="padding:10px 14px;">
  <p style="color:#CC1F20;font-weight:bold;">SQL Error: <?php echo inrm_h($sqlErr); ?></p>
  <?php if (!empty($diagCols)): ?>
  <p style="font-weight:bold;">Actual SGHDSDATA.OEORDT columns (diagnostic dump):</p>
  <p style="font-family:monospace;font-size:12px;"><?php echo inrm_h(implode(', ', $diagCols)); ?></p>
  <?php endif; ?>
</div>
<?php endif; ?>

<style type="text/css">
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
      <div class="refresh-dot" id="inrm-dot"></div>
      <span id="inrm-status">Live &ndash; refreshes once daily at 12:00 PM (M&ndash;F)</span>
      <div class="refresh-progress"><div class="refresh-fill" id="inrm-prog" style="width:0%"></div></div>
      <span>Next refresh in: <strong id="inrm-cd">--:--</strong></span>
      <span class="refresh-pill">Last refresh: <strong id="inrm-lastrefresh">--</strong></span>
      <span class="refresh-pill" style="background:#fff3cd;border-color:#f0c060;color:#856404;">As of: <span id="inrm-asof">--</span></span>
    </div>

    <!-- Filter bar -->
    <div style="display:flex;align-items:center;gap:10px;padding:6px 10px;
                background:#F7F7F7;font-size:12px;flex:1;">
      <form id="inrm-filter-form" method="get" style="display:contents;">
        <?php foreach ($preserveParams as $pk => $pv): ?>
        <input type="hidden" name="<?php echo inrm_h($pk); ?>" value="<?php echo inrm_h($pv); ?>">
        <?php endforeach; ?>
        <label style="white-space:nowrap;font-weight:600;">From:
          <input type="date" name="ffrom" value="<?php echo inrm_h($fFrom); ?>"
                 style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;font-size:12px;margin-left:4px;">
        </label>
        <label style="white-space:nowrap;font-weight:600;">To:
          <input type="date" name="fto" value="<?php echo inrm_h($fTo); ?>"
                 style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;font-size:12px;margin-left:4px;">
        </label>
        <button type="submit"
                style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #1d4ed8;
                       border-radius:3px;background:#2563EB;color:#fff;font-weight:bold;">View</button>
        <a href="<?php echo inrm_h($clearURL); ?>"
           style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                  border-radius:3px;background:#fff;color:#111827;text-decoration:none;display:inline-block;">Clear</a>
      </form>
      <button type="button" id="inrm-toggle-sub-btn"
              style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                     border-radius:3px;background:#fff;color:#111827;">Hide Subtotals</button>
      <b id="inrm-fcount-text" style="margin-left:auto;white-space:nowrap;font-size:12px;">
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
    <a href="<?php echo inrm_h($exportURL); ?>" id="inrm-export-link"
       style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
              font-weight:bold;text-decoration:none;white-space:nowrap;
              text-align:center;display:block;">
      &#8595; Export to Excel
    </a>
    <a href="<?php echo inrm_h($exportSubOnlyURL); ?>"
       style="background:#0891B2;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
              font-weight:bold;text-decoration:none;white-space:nowrap;
              text-align:center;display:block;">
      &#8595; Export Subtotals Only
    </a>
  </div>

</div>

<div style="overflow-x:auto;">
<table id="inrm-grid" <?php echo $contentTable; ?> style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th class="colhdr">Ship-To #</th>
      <th class="colhdr">Ship-To Customer Name</th>
      <th class="colhdr">CO #</th>
      <th class="colhdr">Invoice #</th>
      <th class="colhdr">Item Desc</th>
      <th class="colhdr">Qty Ship</th>
      <th class="colhdr">Total Sale</th>
      <th class="colhdr">Total Cost</th>
      <th class="colhdr">Profit/Loss</th>
      <th class="colhdr">GM%</th>
    </tr>
  </thead>
  <tbody>
<?php if (!empty($groups)):
    $gtProfit = $gtSale - $gtCost;
    $gtGm     = $gtSale != 0 ? ($gtProfit / $gtSale * 100) : 0;
?>
    <tr class="inrm-gt">
      <td class="colcode" colspan="5" style="text-align:right !important;">Grand Total</td>
      <td class="colcode" align="right"><?php echo inrm_int($gtQty); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_cur($gtSale)); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_cur($gtCost)); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_cur($gtProfit)); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_pct($gtGm)); ?></td>
    </tr>
<?php endif; ?>
<?php if (empty($groups) && !$sqlErr): ?>
    <tr>
      <td colspan="10" class="colcode" align="center" style="padding:20px;">
        No install/removal/trip/travel lines found for <?php echo inrm_h(inrm_cYmdToDate($fromCymd)); ?> - <?php echo inrm_h(inrm_cYmdToDate($toCymd)); ?>.
      </td>
    </tr>
<?php endif; ?>
<?php foreach ($groups as $shipto => $g):
    foreach ($g['rows'] as $r):
        $ordNum  = trim((string)$r['ORDNUM']);
        $ordStat = trim((string)$r['ORDSTAT']);
        $ordKey  = (int)$r['ORDNUM'];
        $invList = isset($invoicesByOrder[$ordKey]) ? $invoicesByOrder[$ordKey] : array();
        $profit  = $r['EXTSALE'] - $r['EXTCOST'];
        $gm      = $r['EXTSALE'] != 0 ? ($profit / $r['EXTSALE'] * 100) : 0;

        $coUrl = ($ordStat === 'C')
            ? $eiBase . '/harris-CGI/SelectOrderHistory.d2w/REPORT'
                . '?baseVar=BaseConfiguration.icl&portal=CUSTOMER&eID=' . rawurlencode($eID)
                . '&customerName=' . rawurlencode($g['name'])
                . '&customerNumber=' . rawurlencode($shipto)
                . '&orderNumber=' . rawurlencode($ordNum)
                . '&orderSequence=0'
            : $eiBase . '/harris-CGI/SelectOrder.d2w/REPORT'
                . '?baseVar=BaseConfiguration.icl&portal=CUSTOMER&eID=' . rawurlencode($eID)
                . '&customerName=' . rawurlencode($g['name'])
                . '&customerNumber=' . rawurlencode($shipto)
                . '&orderNumber=' . rawurlencode($ordNum);

        $invLinks = array();
        foreach ($invList as $iv) {
            if ($iv['num'] === '' || $iv['num'] === '0') continue;
            $invUrl = $eiBase . '/harris-CGI/SelectInvoice.d2w/DISPLAY'
                . '?formatToPrint=Y&baseVar=BaseConfiguration.icl&portal=CUSTOMER&eID=' . rawurlencode($eID)
                . '&customerNumber=' . rawurlencode($shipto)
                . '&invoiceDate=' . $iv['date']
                . '&invoiceNumber=' . rawurlencode($iv['num']);
            $invLinks[] = '<a href="' . inrm_h($invUrl) . '" target="_blank">' . inrm_h($iv['num']) . '</a>';
        }
    $zeroCost = ((float)$r['EXTCOST'] === 0.0);
?>
    <tr class="inrm-row<?php echo $zeroCost ? ' inrm-zerocost' : ''; ?>">
      <td class="colcode"><?php echo inrm_h($shipto); ?></td>
      <td class="colcode"><?php echo inrm_h($g['name']); ?></td>
      <td class="colcode"><a href="<?php echo inrm_h($coUrl); ?>" target="_blank"><?php echo inrm_h($ordNum); ?></a></td>
      <td class="colcode"><?php echo !empty($invLinks) ? implode(', ', $invLinks) : '&nbsp;'; ?></td>
      <td class="colcode"><?php echo inrm_h(trim((string)$r['ITEMDESC'])); ?></td>
      <td class="colcode" align="right"><?php echo inrm_int($r['QTY']); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_cur($r['EXTSALE'])); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_cur($r['EXTCOST'])); ?></td>
      <td class="colcode<?php echo $profit < 0 ? ' inrm-negative' : ''; ?>" align="right"><?php echo inrm_h(inrm_cur($profit)); ?></td>
      <td class="colcode<?php echo $gm < 0 ? ' inrm-negative' : ''; ?>" align="right"><?php echo inrm_h(inrm_pct($gm)); ?></td>
    </tr>
<?php endforeach;
    $gProfit = $g['sale'] - $g['cost'];
    $gGm     = $g['sale'] != 0 ? ($gProfit / $g['sale'] * 100) : 0;
?>
    <tr class="inrm-sub">
      <td class="colcode" colspan="2"><?php echo inrm_h($g['name']); ?> - Subtotal</td>
      <td class="colcode"></td>
      <td class="colcode"></td>
      <td class="colcode"></td>
      <td class="colcode" align="right"><?php echo inrm_int($g['qty']); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_cur($g['sale'])); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_cur($g['cost'])); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_cur($gProfit)); ?></td>
      <td class="colcode" align="right"><?php echo inrm_h(inrm_pct($gGm)); ?></td>
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
    var dotEl  = document.getElementById('inrm-dot');
    var statEl = document.getElementById('inrm-status');
    var cdEl   = document.getElementById('inrm-cd');
    var progEl = document.getElementById('inrm-prog');
    var lrEl   = document.getElementById('inrm-lastrefresh');
    var asEl   = document.getElementById('inrm-asof');
    var tzAbbr = new Date().toLocaleTimeString('en-US', {timeZoneName:'short'}).split(' ').pop();

    // Every time value here comes straight from the viewer's own PC clock/timezone
    // (plain `new Date()`) - never hardcode or convert to a specific IANA zone.
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
    // Next weekday (M-F) noon on the viewer's own local clock.
    function nextNoon() {
        var now = new Date();
        var target = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 12, 0, 0, 0);
        if (now.getTime() >= target.getTime()) target.setDate(target.getDate() + 1);
        while (target.getDay() === 0 || target.getDay() === 6) target.setDate(target.getDate() + 1);
        return target;
    }

    var loadTime = new Date();
    var target    = nextNoon();
    var totalMs   = target.getTime() - loadTime.getTime();

    if (lrEl) lrEl.textContent = loadTime.toLocaleTimeString();
    if (asEl) asEl.textContent = loadTime.toLocaleDateString('en-US', { weekday:'short', month:'short', day:'numeric', year:'numeric' });

    function updateBar() {
        var now = new Date();
        var remainMs = target.getTime() - now.getTime();
        if (remainMs <= 0) { location.reload(); return; }
        if (statEl) statEl.textContent = 'Live – refreshes once daily at 12:00 PM (M–F, ' + tzAbbr + ')';
        if (progEl) progEl.style.width = Math.max(0, 100 - (remainMs / totalMs * 100)).toFixed(1) + '%';
        if (cdEl)   cdEl.textContent   = fmt(Math.floor(remainMs / 1000));
    }
    setInterval(updateBar, 1000);
    updateBar();
}());

(function () {
    var btn = document.getElementById('inrm-toggle-sub-btn');
    var tbl = document.getElementById('inrm-grid');
    var exp = document.getElementById('inrm-export-link');
    if (!btn || !tbl) return;
    btn.addEventListener('click', function () {
        var hidden = tbl.classList.toggle('inrm-hide-sub');
        btn.textContent = hidden ? 'Show Subtotals' : 'Hide Subtotals';
    });
    // Export to Excel should skip subtotal rows when they're currently hidden on screen.
    if (exp) {
        exp.addEventListener('click', function (e) {
            if (!tbl.classList.contains('inrm-hide-sub')) return;
            e.preventDefault();
            var url = exp.getAttribute('href');
            url += (url.indexOf('?') === -1 ? '?' : '&') + 'hidesub=1';
            window.location.href = url;
        });
    }
}());
</script>

</body>
</html>