<?php
// Waste Pro Program Comparison - 2024 / 2025 / 2026
// Pricing from program sheets + live sales from SGHDSDATA.OEORHH / OEORDT
// Customer Class: WP

$conn = @db2_connect(
    'DRIVER={IBM i Access ODBC Driver};SYSTEM=10.10.0.5;',
    '', '',
    array(DB2_ATTR_AUTOCOMMIT => DB2_AUTOCOMMIT_ON)
);
$dbError = !$conn ? db2_conn_errormsg() : null;

$salesRows = [];
$priceLists = [];

if ($conn) {
    // Which price lists do WP customers use?
    $s = db2_exec($conn,
        "SELECT DISTINCT TRIM(H.HHPRLV) AS PL, COUNT(*) AS CNT
         FROM SGHDSDATA.OEORHH H
         WHERE H.HHCLAS = 'WP' AND YEAR(H.HHINDT) IN (2024,2025,2026)
         GROUP BY H.HHPRLV ORDER BY CNT DESC"
    );
    while ($r = db2_fetch_assoc($s)) $priceLists[] = $r;

    // Sales by item 2024/2025/2026
    $s2 = db2_exec($conn,
        "SELECT
             TRIM(D.DTITEM)  AS ITEM,
             TRIM(D.DTDESC)  AS DESC_,
             SUM(CASE WHEN YEAR(H.HHINDT)=2024 THEN D.DTQSHP ELSE 0 END) AS QTY24,
             SUM(CASE WHEN YEAR(H.HHINDT)=2024 THEN D.DTQSHP*D.DTUPRC ELSE 0 END) AS SAL24,
             AVG(CASE WHEN YEAR(H.HHINDT)=2024 AND D.DTQSHP>0 THEN D.DTUPRC ELSE NULL END) AS AVG24,
             SUM(CASE WHEN YEAR(H.HHINDT)=2025 THEN D.DTQSHP ELSE 0 END) AS QTY25,
             SUM(CASE WHEN YEAR(H.HHINDT)=2025 THEN D.DTQSHP*D.DTUPRC ELSE 0 END) AS SAL25,
             AVG(CASE WHEN YEAR(H.HHINDT)=2025 AND D.DTQSHP>0 THEN D.DTUPRC ELSE NULL END) AS AVG25,
             SUM(CASE WHEN YEAR(H.HHINDT)=2026 THEN D.DTQSHP ELSE 0 END) AS QTY26,
             SUM(CASE WHEN YEAR(H.HHINDT)=2026 THEN D.DTQSHP*D.DTUPRC ELSE 0 END) AS SAL26,
             AVG(CASE WHEN YEAR(H.HHINDT)=2026 AND D.DTQSHP>0 THEN D.DTUPRC ELSE NULL END) AS AVG26
         FROM SGHDSDATA.OEORHH H
         JOIN SGHDSDATA.OEORDT D ON D.DTORD = H.HHORD
         WHERE H.HHCLAS = 'WP'
           AND H.HHSTAT IN ('I','C')
           AND YEAR(H.HHINDT) IN (2024,2025,2026)
         GROUP BY D.DTITEM, D.DTDESC
         HAVING SUM(D.DTQSHP) > 0
         ORDER BY SAL24 DESC, SAL25 DESC, SAL26 DESC"
    );
    if ($s2) {
        while ($r = db2_fetch_assoc($s2)) $salesRows[] = $r;
    }
    db2_close($conn);
}

// -----------------------------------------------------------
// 2024 Program sheet prices (from HPPRCEXTV5 extract / PDF)
// Note: 901-1660 at 250 pcs is $4.95 in system (PDF showed $5.95)
// -----------------------------------------------------------
$prog2024 = [
    '901-0001'    => ['desc'=>'Large Side Roll-Off Logo Decal','size'=>'71"W x 29"H','cat'=>'Roll-Off',
                      'brk'=>[50=>49.35,100=>43.34,200=>38.58,300=>36.99,500=>35.72]],
    '901-0002'    => ['desc'=>'Small Side Roll-Off Logo Decal','size'=>'44.75"W x 18.62"H','cat'=>'Roll-Off',
                      'brk'=>[50=>28.74,100=>22.44,200=>19.15,300=>18.05,500=>17.17]],
    '901-7310'    => ['desc'=>'Large Roll-Off Phone Number','size'=>'73"W x 10"H','cat'=>'Roll-Off',
                      'brk'=>[50=>14.82,100=>13.62,250=>12.90,500=>12.66]],
    '901-4306'    => ['desc'=>'Small Roll-Off Phone Number','size'=>'43.65"W x 6"H','cat'=>'Roll-Off',
                      'brk'=>[50=>8.28,100=>7.08,250=>6.36,500=>6.12]],
    '901-1660'    => ['desc'=>'Container Logo Decal (Standard)','size'=>'22.25"W x 17.25"H','cat'=>'Container',
                      'brk'=>[250=>4.95,500=>4.43,700=>3.99,1000=>3.67,2500=>3.21]],
    '901-1660-HT' => ['desc'=>'Container Logo Decal (High-Tack)','size'=>'22.25"W x 17.25"H','cat'=>'Coastal/HT',
                      'brk'=>[100=>8.25,200=>7.15,300=>6.79,500=>6.50]],
    '901-1409'    => ['desc'=>'Cart Logo Decal (Standard)','size'=>'14"W x 9"H','cat'=>'Cart',
                      'brk'=>[250=>3.65,500=>2.31,700=>1.93,1000=>1.64,2500=>1.24]],
    '901-1409-HT' => ['desc'=>'Cart Logo Decal (High-Tack)','size'=>'14"W x 9"H','cat'=>'Coastal/HT',
                      'brk'=>[100=>4.95,200=>3.83,300=>3.46,500=>3.16]],
    '901-1208'    => ['desc'=>'Small Cart Logo Decal (Standard)','size'=>'12"W x 8"H','cat'=>'Cart',
                      'brk'=>[250=>2.27,500=>1.82,700=>1.69,1000=>1.59,2500=>1.46]],
    '901-1208-HT' => ['desc'=>'Small Cart Logo Decal (High-Tack)','size'=>'12"W x 8"H','cat'=>'Coastal/HT',
                      'brk'=>[100=>3.94,200=>2.82,300=>2.44,500=>2.14]],
];

// 2026 program sheet prices (from 2026 Waste Pro Program Sheet 01.pdf)
$prog2026 = [
    '901-0001'    => [50=>51.82,100=>45.51,200=>42.44],
    '901-0002'    => [50=>30.18,100=>24.68,200=>21.07],
    '901-7310'    => [50=>16.26,100=>14.90,200=>14.22],
    '901-4306'    => [50=>10.30,100=>8.99,200=>8.34],
    '901-1660'    => [250=>6.13,500=>4.65,1000=>4.04,2500=>3.53],
    '901-1660-HT' => [100=>8.50,200=>7.36,300=>6.99,500=>6.70],
    '901-1409'    => [250=>3.76,500=>2.54,1000=>1.80,2500=>1.36],
    '901-1409-HT' => [100=>5.10,200=>3.94,300=>3.56,500=>3.25],
    '901-1208'    => [250=>2.34,500=>1.91,1000=>1.67,2500=>1.53],
    '901-1208-HT' => [100=>4.06,200=>2.90,300=>2.51,500=>2.20],
];

// Build sales lookup by item
$salesByItem = [];
foreach ($salesRows as $r) {
    $salesByItem[trim($r['ITEM'])] = $r;
}

function fmt($v) { return $v > 0 ? '$'.number_format($v,2) : '-'; }
function fmtN($v) { return $v > 0 ? number_format($v) : '-'; }
function pct($old,$new) {
    if (!$old) return '';
    $p = (($new-$old)/$old)*100;
    return ($p>=0?'+':'').round($p,1).'%';
}
function pctClass($old,$new) {
    if (!$old) return '';
    $p = (($new-$old)/$old)*100;
    if ($p < 3)  return 'chg-low';
    if ($p < 10) return 'chg-mid';
    if ($p < 20) return 'chg-high';
    return 'chg-max';
}

$cats = ['Roll-Off','Container','Cart','Coastal/HT'];
$catLabels = ['Roll-Off'=>'Roll-Off Logo Decals','Container'=>'Container Logo Decals','Cart'=>'Cart Logo Decals','Coastal/HT'=>'Coastal Branch (High-Tack) Decals'];

$now = date('m/d/Y g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Waste Pro Program Comparison 2024/2025/2026</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,'Segoe UI',Roboto,sans-serif;font-size:13px;background:#f3f4f6;color:#374151}
a{color:#2563EB;text-decoration:none}
/* Title bar */
.title-bar{background:linear-gradient(135deg,#1e3a5f 0%,#2d5a8e 50%,#1e3a5f 100%);color:#fff;padding:10px 18px;display:flex;align-items:center;gap:12px}
.title-bar h1{font-size:16px;font-weight:700;letter-spacing:.3px}
.title-bar .sub{font-size:11px;opacity:.8;margin-left:auto}
/* Action bar */
.action-bar{background:#2563EB;padding:6px 18px;display:flex;align-items:center;gap:10px}
.action-bar .stamp{color:#bfdbfe;font-size:11px;margin-left:auto}
.btn{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border:none;border-radius:3px;cursor:pointer;font-size:12px;font-weight:600;text-decoration:none}
.btn-cyan{background:#0891b2;color:#fff}
.btn-green{background:#059669;color:#fff;margin-left:auto}
.btn-purple{background:#7c3aed;color:#fff}
/* Content */
.content{padding:14px 18px;display:flex;flex-direction:column;gap:16px}
/* Section header */
.section-hdr{background:#374151;color:#fff;font-weight:700;font-size:12px;letter-spacing:.5px;padding:6px 10px;text-transform:uppercase;border-radius:3px 3px 0 0}
/* Tables */
.tbl-wrap{overflow-x:auto;border-radius:0 0 4px 4px;box-shadow:0 1px 3px rgba(0,0,0,.12)}
table{width:100%;border-collapse:collapse;background:#fff;font-size:12px}
th{background:#374151;color:#fff!important;font-weight:700;padding:7px 8px;text-align:left;white-space:nowrap}
th.r{text-align:right}
td{padding:6px 8px;border-bottom:1px solid #e5e7eb;vertical-align:middle;font-variant-numeric:tabular-nums}
td.r{text-align:right}
tr:hover td{background:#EFF6FF}
/* Spanning item rows */
td.item-part{font-weight:700;color:#1e40af;white-space:nowrap}
td.item-desc{color:#374151}
td.item-size{color:#6b7280;font-size:11px;white-space:nowrap}
/* Change % badges */
.chg-low  td.chg{background:#d1fae5;color:#065f46;font-weight:700}
.chg-mid  td.chg{background:#fef3c7;color:#92400e;font-weight:700}
.chg-high td.chg{background:#fed7aa;color:#7c2d12;font-weight:700}
.chg-max  td.chg{background:#fecaca;color:#7f1d1d;font-weight:700}
td.disco{color:#9ca3af;font-style:italic;font-size:11px}
/* Sales table */
.sales-sum{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.sum-card{background:#fff;border-radius:4px;padding:10px 14px;box-shadow:0 1px 3px rgba(0,0,0,.1)}
.sum-card .yr{font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.4px}
.sum-card .amt{font-size:20px;font-weight:700;color:#1e40af;font-variant-numeric:tabular-nums}
.sum-card .units{font-size:11px;color:#6b7280;margin-top:2px}
/* Error */
.err{background:#fee2e2;border:1px solid #fca5a5;color:#7f1d1d;padding:10px 14px;border-radius:4px;font-size:12px}
/* Cat row divider in sales table */
.cat-row td{background:#f9fafb;font-weight:700;color:#374151;font-size:11px;letter-spacing:.3px;text-transform:uppercase}
/* Footnote */
.footnote{font-size:11px;color:#9ca3af;padding:6px 0}
</style>
</head>
<body>

<div class="title-bar">
  <h1>Waste Pro Program Sheet - Pricing &amp; Sales Comparison</h1>
  <span class="sub">Customer Class: WP &nbsp;|&nbsp; Generated: <?= $now ?></span>
</div>

<div class="action-bar">
  <a href="javascript:history.back()" class="btn btn-cyan">&#8592; Back</a>
  <a href="javascript:window.print()" class="btn btn-purple">&#128438; Print</a>
  <a href="javascript:exportXL()" class="btn btn-green">&#128194; Export to Excel</a>
  <span class="stamp">Prices: 2024 from HPPRCEXTV5 extract &amp; PDF &nbsp;|&nbsp; 2026 from Program Sheet dated 2026-07-01 &nbsp;|&nbsp; Sales from SGHDSDATA.OEORHH/OEORDT</span>
</div>

<div class="content">

<?php if ($dbError): ?>
<div class="err"><strong>DB Connection Error:</strong> <?= htmlspecialchars($dbError) ?></div>
<?php endif; ?>

<!-- SUMMARY CARDS -->
<?php
$tot24 = $tot25 = $tot26 = $qty24 = $qty25 = $qty26 = 0;
foreach ($salesRows as $r) {
    $tot24 += $r['SAL24']; $tot25 += $r['SAL25']; $tot26 += $r['SAL26'];
    $qty24 += $r['QTY24']; $qty25 += $r['QTY25']; $qty26 += $r['QTY26'];
}
?>
<div class="sales-sum">
  <div class="sum-card">
    <div class="yr">2024 Full Year</div>
    <div class="amt">$<?= number_format($tot24,0) ?></div>
    <div class="units"><?= number_format($qty24) ?> units &nbsp;|&nbsp; <?= count(array_filter($salesRows,fn($r)=>$r['SAL24']>0)) ?> items ordered</div>
  </div>
  <div class="sum-card">
    <div class="yr">2025 Full Year</div>
    <div class="amt">$<?= number_format($tot25,0) ?></div>
    <div class="units"><?= number_format($qty25) ?> units &nbsp;|&nbsp; <?= count(array_filter($salesRows,fn($r)=>$r['SAL25']>0)) ?> items ordered</div>
  </div>
  <div class="sum-card">
    <div class="yr">2026 YTD (thru <?= date('M j') ?>)</div>
    <div class="amt">$<?= number_format($tot26,0) ?></div>
    <div class="units"><?= number_format($qty26) ?> units &nbsp;|&nbsp; <?= count(array_filter($salesRows,fn($r)=>$r['SAL26']>0)) ?> items ordered</div>
  </div>
</div>

<!-- PROGRAM SHEET PRICE COMPARISON -->
<?php foreach ($cats as $cat):
    $items = array_filter($prog2024, fn($v)=>$v['cat']===$cat);
    if (!$items) continue;
?>
<div>
  <div class="section-hdr"><?= $catLabels[$cat] ?> - 2024 vs 2026 Program Sheet Pricing</div>
  <div class="tbl-wrap">
  <table>
    <thead>
      <tr>
        <th>Part #</th>
        <th>Description</th>
        <th>Size</th>
        <th class="r">Qty Break</th>
        <th class="r">2024 Price</th>
        <th class="r">2026 Price</th>
        <th class="r">Change $</th>
        <th class="r">Change %</th>
        <th class="r">2024 Units</th>
        <th class="r">2024 Sales</th>
        <th class="r">2025 Units</th>
        <th class="r">2025 Sales</th>
        <th class="r">2026 Units</th>
        <th class="r">2026 Sales</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $part => $info):
        $brks24 = $info['brk'];
        $brks26 = $prog2026[$part] ?? [];
        // Merge all breaks from both years
        $allBreaks = array_unique(array_merge(array_keys($brks24), array_keys($brks26)));
        sort($allBreaks);
        $first = true;
        $sd = $salesByItem[$part] ?? null;
        foreach ($allBreaks as $qty):
            $p24 = $brks24[$qty] ?? null;
            $p26 = $brks26[$qty] ?? null;
            $inBoth = $p24 !== null && $p26 !== null;
            $chgDol = $inBoth ? ($p26 - $p24) : null;
            $chgPct = $inBoth ? pct($p24,$p26) : '';
            $chgCls = $inBoth ? pctClass($p24,$p26) : '';
            $rowCls = $chgCls ? "class=\"$chgCls\"" : '';
    ?>
      <tr <?= $rowCls ?>>
        <?php if ($first): ?>
        <td class="item-part" rowspan="<?= count($allBreaks) ?>"><?= htmlspecialchars($part) ?></td>
        <td class="item-desc" rowspan="<?= count($allBreaks) ?>"><?= htmlspecialchars($info['desc']) ?></td>
        <td class="item-size" rowspan="<?= count($allBreaks) ?>"><?= htmlspecialchars($info['size']) ?></td>
        <?php else: ?>
        <?php endif; ?>
        <td class="r"><?= number_format($qty) ?></td>
        <td class="r"><?= $p24 !== null ? '$'.number_format($p24,2) : '<span class="disco">New in 2026</span>' ?></td>
        <td class="r"><?= $p26 !== null ? '$'.number_format($p26,2) : '<span class="disco">Discontinued</span>' ?></td>
        <td class="r chg"><?= $chgDol !== null ? ($chgDol>=0?'+':'').'$'.number_format($chgDol,2) : '' ?></td>
        <td class="r chg"><?= $chgPct ?></td>
        <?php if ($first): ?>
        <td class="r" rowspan="<?= count($allBreaks) ?>"><?= $sd ? fmtN($sd['QTY24']) : '-' ?></td>
        <td class="r" rowspan="<?= count($allBreaks) ?>"><?= $sd ? fmt($sd['SAL24']) : '-' ?></td>
        <td class="r" rowspan="<?= count($allBreaks) ?>"><?= $sd ? fmtN($sd['QTY25']) : '-' ?></td>
        <td class="r" rowspan="<?= count($allBreaks) ?>"><?= $sd ? fmt($sd['SAL25']) : '-' ?></td>
        <td class="r" rowspan="<?= count($allBreaks) ?>"><?= $sd ? fmtN($sd['QTY26']) : '-' ?></td>
        <td class="r" rowspan="<?= count($allBreaks) ?>"><?= $sd ? fmt($sd['SAL26']) : '-' ?></td>
        <?php endif; ?>
      </tr>
    <?php $first = false; endforeach; endforeach; ?>
    </tbody>
  </table>
  </div>
  <p class="footnote">Color key: <span style="background:#d1fae5;padding:1px 6px;border-radius:2px">Under 3%</span> &nbsp; <span style="background:#fef3c7;padding:1px 6px;border-radius:2px">3-10%</span> &nbsp; <span style="background:#fed7aa;padding:1px 6px;border-radius:2px">10-20%</span> &nbsp; <span style="background:#fecaca;padding:1px 6px;border-radius:2px">Over 20%</span></p>
</div>
<?php endforeach; ?>

<!-- ALL WP ITEMS - FULL SALES DETAIL -->
<div>
  <div class="section-hdr">All WP Customer Items - Full Sales History (All Items Invoiced)</div>
  <div class="tbl-wrap">
  <table id="allItems">
    <thead>
      <tr>
        <th>Part #</th>
        <th>Description</th>
        <th class="r">2024 Units</th>
        <th class="r">2024 Avg Price</th>
        <th class="r">2024 Sales</th>
        <th class="r">2025 Units</th>
        <th class="r">2025 Avg Price</th>
        <th class="r">2025 Sales</th>
        <th class="r">2026 Units</th>
        <th class="r">2026 Avg Price</th>
        <th class="r">2026 Sales</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($salesRows)): ?>
      <tr><td colspan="11" style="text-align:center;color:#9ca3af;padding:20px"><?= $dbError ? 'Database unavailable' : 'No sales data found for WP class' ?></td></tr>
    <?php else: ?>
      <?php foreach ($salesRows as $r): ?>
      <tr>
        <td class="item-part"><?= htmlspecialchars(trim($r['ITEM'])) ?></td>
        <td><?= htmlspecialchars(trim($r['DESC_'])) ?></td>
        <td class="r"><?= fmtN($r['QTY24']) ?></td>
        <td class="r"><?= $r['AVG24'] > 0 ? '$'.number_format($r['AVG24'],2) : '-' ?></td>
        <td class="r"><?= fmt($r['SAL24']) ?></td>
        <td class="r"><?= fmtN($r['QTY25']) ?></td>
        <td class="r"><?= $r['AVG25'] > 0 ? '$'.number_format($r['AVG25'],2) : '-' ?></td>
        <td class="r"><?= fmt($r['SAL25']) ?></td>
        <td class="r"><?= fmtN($r['QTY26']) ?></td>
        <td class="r"><?= $r['AVG26'] > 0 ? '$'.number_format($r['AVG26'],2) : '-' ?></td>
        <td class="r"><?= fmt($r['SAL26']) ?></td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

</div><!-- /content -->

<script>
function exportXL() {
    var tbl = document.getElementById('allItems');
    var html = tbl.outerHTML;
    var blob = new Blob([html], {type:'application/vnd.ms-excel'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'WP_Sales_' + new Date().toISOString().slice(0,10) + '.xls';
    a.click();
}
</script>
</body>
</html>