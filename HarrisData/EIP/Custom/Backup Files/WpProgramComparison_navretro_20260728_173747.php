<?php
$conn = @db2_connect(
    'DRIVER={IBM i Access ODBC Driver};SYSTEM=10.10.0.5;',
    '', '',
    array(DB2_ATTR_AUTOCOMMIT => DB2_AUTOCOMMIT_ON)
);
$raw=[]; $tot24=$tot25=$tot26=0; $qty24t=$qty25t=$qty26t=0; $itms24=$itms25=$itms26=0;
$dbErr=''; $sqlErr='';
if (!$conn) {
    $dbErr = db2_conn_errormsg();
} else {
    // CYMD date boundaries (IBM i format: CYMD where C=century offset from 1900)
    // 2024: 1240101-1250101, 2025: 1250101-1260101, 2026: 1260101-1270101
    $sql = "SELECT
         TRIM(d.DHITEM) AS ITEM,
         TRIM(d.DHIMDS) AS DESC_,
         SUM(CASE WHEN d.DHDTLI >= 1240101 AND d.DHDTLI < 1250101 THEN d.DHQSTC ELSE 0 END) AS QTY24,
         SUM(CASE WHEN d.DHDTLI >= 1240101 AND d.DHDTLI < 1250101 AND d.DHORUF <> 0
                  THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END) AS SAL24,
         SUM(CASE WHEN d.DHDTLI >= 1250101 AND d.DHDTLI < 1260101 THEN d.DHQSTC ELSE 0 END) AS QTY25,
         SUM(CASE WHEN d.DHDTLI >= 1250101 AND d.DHDTLI < 1260101 AND d.DHORUF <> 0
                  THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END) AS SAL25,
         SUM(CASE WHEN d.DHDTLI >= 1260101 AND d.DHDTLI < 1270101 THEN d.DHQSTC ELSE 0 END) AS QTY26,
         SUM(CASE WHEN d.DHDTLI >= 1260101 AND d.DHDTLI < 1270101 AND d.DHORUF <> 0
                  THEN d.DHSLPR * d.DHQSTC / d.DHORUF ELSE 0 END) AS SAL26
    FROM SGHDSDATA.OEORDH d
    JOIN SGHDSDATA.OEORHD h ON d.\"DHORD#\" = h.\"OEORD#\"
    LEFT JOIN SGHDSDATA.HDCUST cust ON h.OESHTO = cust.CMCUST
    WHERE d.\"DHSEQ#\" <> 0
      AND d.DHQSTC <> 0
      AND cust.CMCCLS = 'WP'
      AND d.DHDTLI >= 1240101
      AND d.DHDTLI < 1270101
    GROUP BY d.DHITEM, d.DHIMDS
    HAVING SUM(d.DHQSTC) > 0
    ORDER BY SAL24 DESC, SAL25 DESC, SAL26 DESC";
    $stmt = db2_exec($conn, $sql);
    if (!$stmt) {
        $sqlErr = db2_stmt_errormsg();
    } else {
        $salesMap = [];
        while ($r = db2_fetch_assoc($stmt)) {
            $item = trim($r['ITEM']);
            $salesMap[$item] = [
                'desc' => trim($r['DESC_']),
                'q24'  => (int)$r['QTY24'],  's24' => round((float)$r['SAL24'],2),
                'q25'  => (int)$r['QTY25'],  's25' => round((float)$r['SAL25'],2),
                'q26'  => (int)$r['QTY26'],  's26' => round((float)$r['SAL26'],2),
                'o24'  => 0, 'o25' => 0, 'o26' => 0,
            ];
        }
    }

    // Separate query for distinct order counts per item per year
    if (!$sqlErr) {
        $sqlOrd = "SELECT TRIM(d.DHITEM) AS ITEM,
                   CASE WHEN d.DHDTLI >= 1240101 AND d.DHDTLI < 1250101 THEN 2024
                        WHEN d.DHDTLI >= 1250101 AND d.DHDTLI < 1260101 THEN 2025
                        WHEN d.DHDTLI >= 1260101 AND d.DHDTLI < 1270101 THEN 2026
                        ELSE 0 END AS YR,
                   COUNT(DISTINCT d.\"DHORD#\") AS ORDCNT
              FROM SGHDSDATA.OEORDH d
              JOIN SGHDSDATA.OEORHD h ON d.\"DHORD#\" = h.\"OEORD#\"
              LEFT JOIN SGHDSDATA.HDCUST cust ON h.OESHTO = cust.CMCUST
             WHERE d.\"DHSEQ#\" <> 0
               AND d.DHQSTC > 0
               AND cust.CMCCLS = 'WP'
               AND d.DHDTLI >= 1240101
               AND d.DHDTLI < 1270101
             GROUP BY d.DHITEM,
                   CASE WHEN d.DHDTLI >= 1240101 AND d.DHDTLI < 1250101 THEN 2024
                        WHEN d.DHDTLI >= 1250101 AND d.DHDTLI < 1260101 THEN 2025
                        WHEN d.DHDTLI >= 1260101 AND d.DHDTLI < 1270101 THEN 2026
                        ELSE 0 END";
        $stmtO = db2_exec($conn, $sqlOrd);
        if ($stmtO) {
            while ($o = db2_fetch_assoc($stmtO)) {
                $item = trim($o['ITEM']); $yr = (int)$o['YR']; $cnt = (int)$o['ORDCNT'];
                if (isset($salesMap[$item]) && $yr > 0) {
                    if ($yr===2024) $salesMap[$item]['o24']=$cnt;
                    elseif ($yr===2025) $salesMap[$item]['o25']=$cnt;
                    elseif ($yr===2026) $salesMap[$item]['o26']=$cnt;
                }
            }
        }
    }

    // Build $raw array in same order as sales query
    if (!$sqlErr && isset($salesMap)) {
        foreach ($salesMap as $item => $d) {
            $raw[] = [$item,$d['desc'],$d['q24'],$d['s24'],$d['o24'],$d['q25'],$d['s25'],$d['o25'],$d['q26'],$d['s26'],$d['o26']];
            $tot24+=$d['s24']; $tot25+=$d['s25']; $tot26+=$d['s26'];
            $qty24t+=$d['q24']; $qty25t+=$d['q25']; $qty26t+=$d['q26'];
            if($d['q24']>0)$itms24++; if($d['q25']>0)$itms25++; if($d['q26']>0)$itms26++;
        }
    }

    db2_close($conn);
}
$rawJson   = json_encode($raw, JSON_UNESCAPED_UNICODE);
$itemCount = count($raw);
$today     = date('m/d/Y');
$diff      = $tot25 - $tot24;
$pct       = $tot24 > 0 ? round($diff/$tot24*100,1) : 0;
$sign      = $diff >= 0 ? '+' : '-';
$card25cls = $diff >= 0 ? 'up' : 'dn';
$card25chg = $sign.'$'.number_format(abs($diff)).' ('.($pct>=0?'+':'').$pct.'%) vs 2024';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Waste Pro Program Sheet 2024-2026</title>
<meta name="description" content="Side-by-side pricing and actual sales comparison of Waste Pro decal program items 2024 to 2026, Screen Graphics internal use.">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,'Segoe UI',Roboto,sans-serif;font-size:13px;background:#f3f4f6;color:#374151}
.title-bar{background:linear-gradient(135deg,#1e3a5f 0%,#2d5a8e 50%,#1e3a5f 100%);color:#fff;padding:10px 18px;display:flex;align-items:center;gap:12px}
.title-bar h1{font-size:15px;font-weight:700}
.title-bar .sub{font-size:11px;opacity:.75;margin-left:auto;white-space:nowrap}
.btn{display:inline-flex;align-items:center;gap:4px;padding:4px 11px;border:none;border-radius:3px;cursor:pointer;font-size:11px;font-weight:600;text-decoration:none;white-space:nowrap}
.btn-cyan{background:#0891b2;color:#fff}.btn-green{background:#059669;color:#fff}
.tabs{background:#1e3a5f;display:flex;gap:4px;padding:7px 18px 0;border-bottom:3px solid #2563EB;align-items:flex-end}
.tab{padding:9px 22px;cursor:pointer;font-size:12px;font-weight:700;color:rgba(255,255,255,.65);background:#2d4f7c;border-radius:6px 6px 0 0;border:1px solid rgba(255,255,255,.25);border-bottom:none;transition:.15s}
.tab:hover{color:#fff;background:#3d6499}
.tab.active{color:#1e3a5f;background:#f0f4f8;border-color:#6b9bd2;font-weight:700}
.part-link{color:#1e3a8a;text-decoration:none;cursor:pointer;font-weight:700}
.part-link:hover{color:#2563EB;text-decoration:underline}
.tab-pane{display:none}.tab-pane.active{display:block}
.content{padding:14px 18px;display:flex;flex-direction:column;gap:14px}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.card{background:#fff;border-radius:4px;padding:10px 14px;box-shadow:0 1px 3px rgba(0,0,0,.1)}
.card .yr{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280}
.card .amt{font-size:22px;font-weight:700;color:#1e40af;font-variant-numeric:tabular-nums}
.card .sub{font-size:11px;color:#6b7280;margin-top:2px;font-variant-numeric:tabular-nums}
.card .chg{font-size:11px;font-weight:700;margin-top:4px}
.up{color:#059669}.dn{color:#dc2626}
.sec-hdr{background:#374151;color:#fff!important;font-weight:700;font-size:11px;letter-spacing:.5px;text-transform:uppercase;padding:6px 10px;border-radius:3px 3px 0 0}
.tbl-wrap{overflow-x:auto;border-radius:0 0 4px 4px;box-shadow:0 1px 3px rgba(0,0,0,.1)}
table{width:100%;border-collapse:collapse;background:#fff;font-size:12px;font-variant-numeric:tabular-nums}
th{background:#374151;color:#fff!important;font-weight:700!important;padding:5px 6px;text-align:left;white-space:normal;position:sticky;top:0;z-index:2;vertical-align:bottom;line-height:1.25}
#tDrill th{white-space:nowrap}
#pane0 table{table-layout:fixed;width:100%}
th.r,td.r{text-align:right}
td{padding:5px 8px;border-bottom:1px solid #e5e7eb;vertical-align:middle}
tr:last-child td{border-bottom:none}
#tDrill tbody tr:nth-child(even) td{background:#f3f4f6}
#tDrill tbody tr:nth-child(odd) td{background:#fff}
#tDrill tbody tr:hover td{background:#dbeafe!important;cursor:pointer}
td.part{font-weight:700;color:#1e3a8a;vertical-align:top;padding-top:7px;border-right:2px solid #e5e7eb;white-space:nowrap}
td.desc-cell{vertical-align:top;padding-top:7px;max-width:220px;word-wrap:break-word}
.iname{font-weight:600;color:#111827;line-height:1.3}
.isize{font-size:11px;color:#374151;font-weight:600;margin-top:3px}
.bg-low{background:#d1fae5!important}.bg-mid{background:#fef9c3!important}
.bg-high{background:#fed7aa!important}.bg-max{background:#fecaca!important}
.tx-low{color:#065f46;font-weight:700}.tx-mid{color:#92400e;font-weight:700}
.tx-high{color:#7c2d12;font-weight:700}.tx-max{color:#7f1d1d;font-weight:700}
.disco{color:#9ca3af;font-style:italic;font-size:11px}
th.y24{border-top:3px solid #6366f1}
th.y25{border-top:3px solid #f59e0b}
th.y26{border-top:3px solid #10b981}
.fn{font-size:11px;color:#9ca3af;padding:4px 0}
.filter-bar{background:#fff;border-radius:4px;padding:8px 12px;box-shadow:0 1px 3px rgba(0,0,0,.1);display:flex;align-items:center;gap:10px}
.filter-bar input{border:1px solid #d1d5db;border-radius:3px;padding:5px 10px;font-size:12px;flex:1;max-width:280px}
.filter-bar input:focus{outline:none;border-color:#2563EB}
.cnt{font-size:12px;font-style:italic;color:#6b7280}
td.neg{color:#dc2626}
th.sortable{cursor:pointer;user-select:none}
th.sortable:hover{background:#4b5563!important}
th.sortable::after{content:' \2195';opacity:.4;font-size:10px}
th.sort-asc::after{content:' \2191';opacity:1}
th.sort-desc::after{content:' \2193';opacity:1}
.ord-link{color:#2563EB;font-weight:700;text-decoration:none;cursor:pointer}
.ord-link:hover{text-decoration:underline}
.agg-row td{background:#dbeafe!important;border-top:2px solid #93c5fd}
.agg-row td:first-child{font-weight:700;color:#1e3a5f}
</style>
</head><body>

<div style="position:sticky;top:0;z-index:200">
<div class="title-bar">
  <h1>Waste Pro &mdash; Program Sheet Pricing &amp; Sales</h1>
  <span class="sub">Customer Class: WP &nbsp;&middot;&nbsp; As of <?php echo $today; ?></span>
</div>
<?php if($dbErr||$sqlErr): ?>
<div style="background:#fee2e2;color:#7f1d1d;padding:6px 18px;font-size:12px;font-weight:700">
  DB ERROR: <?php echo htmlspecialchars($dbErr.$sqlErr); ?>
</div>
<?php endif; ?>
<div class="tabs">
  <div class="tab active" onclick="showTab(0)">Program Sheet Pricing</div>
  <div class="tab" onclick="showTab(1)">All WP Items &mdash; Sales Drill-Down (<?php echo $itemCount; ?> items)</div>
  <div style="margin-left:auto;display:flex;align-items:center;gap:8px;padding-bottom:8px">
    <a href="javascript:history.back()" class="btn btn-cyan">&#8592; Back</a>
    <button onclick="exportXL()" class="btn btn-green">&#128194; Export to Excel</button>
  </div>
</div>
<div style="background:#fff;border-bottom:1px solid #e5e7eb;padding:8px 18px;display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
  <div class="card" style="box-shadow:none;padding:6px 10px">
    <div class="yr">2024 Full Year</div>
    <div class="amt" style="font-size:18px">$<?php echo number_format($tot24); ?></div>
    <div class="sub"><?php echo number_format($qty24t); ?> units &nbsp;&middot;&nbsp; <?php echo $itms24; ?> items invoiced</div>
  </div>
  <div class="card" style="box-shadow:none;padding:6px 10px">
    <div class="yr">2025 Full Year</div>
    <div class="amt" style="font-size:18px">$<?php echo number_format($tot25); ?></div>
    <div class="sub"><?php echo number_format($qty25t); ?> units &nbsp;&middot;&nbsp; <?php echo $itms25; ?> items invoiced</div>
    <div class="chg <?php echo $card25cls; ?>"><?php echo $card25chg; ?></div>
  </div>
  <div class="card" style="box-shadow:none;padding:6px 10px">
    <div class="yr">2026 YTD (thru <?php echo $today; ?>)</div>
    <div class="amt" style="font-size:18px">$<?php echo number_format($tot26); ?></div>
    <div class="sub"><?php echo number_format($qty26t); ?> units &nbsp;&middot;&nbsp; <?php echo $itms26; ?> items invoiced</div>
    <div class="chg" style="color:#6b7280">YTD thru <?php echo $today; ?></div>
  </div>
</div>
</div><!-- /sticky header -->

<!-- TAB 0: PRICING COMPARISON -->
<div class="tab-pane active" id="pane0">
<div class="content">

<!-- ROLL-OFF -->
<div>
<div class="sec-hdr">Roll-Off Logo Decals &nbsp;<span style="font-weight:400;font-size:10px;opacity:.8">Material: 3MIJ180CV3 &middot; 5-Yr Warranty &middot; Easy Application &amp; Removal</span></div>
<div class="tbl-wrap"><table>
<thead><tr>
  <th style="width:68px">Part #</th><th style="width:220px">Description</th><th style="width:62px">Size</th>
  <th class="r" style="width:42px">Qty Brk</th><th class="r" style="width:50px">2024 Price</th><th class="r" style="width:50px">2026 Price</th>
  <th class="r" style="width:48px">Chg $</th><th class="r" style="width:40px">Chg %</th>
  <th class="r y24" style="width:36px"># Ord '24</th><th class="r y24" style="width:50px">Avg Units/ Ord '24</th><th class="r y24" style="width:44px">2024 Units</th><th class="r y24" style="width:50px">Avg $/pc '24</th><th class="r y24" style="width:54px">2024 Sales</th>
  <th class="r y25" style="width:36px"># Ord '25</th><th class="r y25" style="width:50px">Avg Units/ Ord '25</th><th class="r y25" style="width:44px">2025 Units</th><th class="r y25" style="width:50px">Avg $/pc '25</th><th class="r y25" style="width:54px">2025 Sales</th>
  <th class="r y26" style="width:36px"># Ord '26</th><th class="r y26" style="width:50px">Avg Units/ Ord '26</th><th class="r y26" style="width:44px">2026 Units</th><th class="r y26" style="width:50px">Avg $/pc '26</th><th class="r y26" style="width:54px">2026 Sales</th>
</tr></thead>
<tbody>
<tr class="bg-mid"><td class="part" rowspan="5"><a href="javascript:void(0)" onclick="goFilter('901-0001')" class="part-link">901-0001</a></td><td class="desc-cell" rowspan="5"><div class="iname">Large Side Roll-Off Logo Decal</div><div class="isize">71" W &times; 29" H</div></td><td rowspan="5">71&times;29</td>
  <td class="r">50</td><td class="r">$49.35</td><td class="r">$51.82</td><td class="r tx-mid bg-mid">+$2.47</td><td class="r tx-mid bg-mid">+5.0%</td>
  <td class="r y24" rowspan="5" id="ro0001-o24"></td><td class="r y24" rowspan="5" id="ro0001-u24" style="font-style:italic;color:#6b7280"></td><td class="r y24" rowspan="5">50</td><td class="r y24" rowspan="5" id="ro0001-a24" style="font-style:italic;color:#6b7280"></td><td class="r y24" rowspan="5">$2,602</td>
  <td class="r y25" rowspan="5" id="ro0001-o25"></td><td class="r y25" rowspan="5" id="ro0001-u25" style="font-style:italic;color:#6b7280"></td><td class="r y25" rowspan="5">220</td><td class="r y25" rowspan="5" id="ro0001-a25" style="font-style:italic;color:#6b7280"></td><td class="r y25" rowspan="5">$10,470</td>
  <td class="r y26" rowspan="5" id="ro0001-o26"></td><td class="r y26" rowspan="5" id="ro0001-u26" style="font-style:italic;color:#6b7280"></td><td class="r y26" rowspan="5">53</td><td class="r y26" rowspan="5" id="ro0001-a26" style="font-style:italic;color:#6b7280"></td><td class="r y26" rowspan="5">$2,902</td></tr>
<tr class="bg-mid"><td class="r">100</td><td class="r">$43.34</td><td class="r">$45.51</td><td class="r tx-mid bg-mid">+$2.17</td><td class="r tx-mid bg-mid">+5.0%</td></tr>
<tr class="bg-high"><td class="r">200</td><td class="r">$38.58</td><td class="r">$42.44</td><td class="r tx-high bg-high">+$3.86</td><td class="r tx-high bg-high">+10.0%</td></tr>
<tr><td class="r">300</td><td class="r">$36.99</td><td class="r"><span class="disco">Discontinued</span></td><td class="r">&mdash;</td><td class="r">&mdash;</td></tr>
<tr><td class="r">500</td><td class="r">$35.72</td><td class="r"><span class="disco">Discontinued</span></td><td class="r">&mdash;</td><td class="r">&mdash;</td></tr>
<tr class="bg-mid"><td class="part" rowspan="5"><a href="javascript:void(0)" onclick="goFilter('901-0002')" class="part-link">901-0002</a></td><td class="desc-cell" rowspan="5"><div class="iname">Small Side Roll-Off Logo Decal</div><div class="isize">44.75" W &times; 18.62" H</div></td><td rowspan="5">44.75&times;18.62</td>
  <td class="r">50</td><td class="r">$28.74</td><td class="r">$30.18</td><td class="r tx-mid bg-mid">+$1.44</td><td class="r tx-mid bg-mid">+5.0%</td>
  <td class="r y24" rowspan="5" id="ro0002-o24"></td><td class="r y24" rowspan="5" id="ro0002-u24" style="font-style:italic;color:#6b7280"></td><td class="r y24" rowspan="5">50</td><td class="r y24" rowspan="5" id="ro0002-a24" style="font-style:italic;color:#6b7280"></td><td class="r y24" rowspan="5">$1,437</td>
  <td class="r y25" rowspan="5" id="ro0002-o25"></td><td class="r y25" rowspan="5" id="ro0002-u25" style="font-style:italic;color:#6b7280"></td><td class="r y25" rowspan="5">8</td><td class="r y25" rowspan="5" id="ro0002-a25" style="font-style:italic;color:#6b7280"></td><td class="r y25" rowspan="5">$230</td>
  <td class="r y26" rowspan="5" id="ro0002-o26"></td><td class="r y26" rowspan="5" id="ro0002-u26" style="font-style:italic;color:#6b7280"></td><td class="r y26" rowspan="5">&mdash;</td><td class="r y26" rowspan="5" id="ro0002-a26" style="font-style:italic;color:#6b7280"></td><td class="r y26" rowspan="5">&mdash;</td></tr>
<tr class="bg-high"><td class="r">100</td><td class="r">$22.44</td><td class="r">$24.68</td><td class="r tx-high bg-high">+$2.24</td><td class="r tx-high bg-high">+10.0%</td></tr>
<tr class="bg-high"><td class="r">200</td><td class="r">$19.15</td><td class="r">$21.07</td><td class="r tx-high bg-high">+$1.92</td><td class="r tx-high bg-high">+10.0%</td></tr>
<tr><td class="r">300</td><td class="r">$18.05</td><td class="r"><span class="disco">Discontinued</span></td><td class="r">&mdash;</td><td class="r">&mdash;</td></tr>
<tr><td class="r">500</td><td class="r">$17.17</td><td class="r"><span class="disco">Discontinued</span></td><td class="r">&mdash;</td><td class="r">&mdash;</td></tr>
<tr class="bg-mid"><td class="part" rowspan="4"><a href="javascript:void(0)" onclick="goFilter('901-7310')" class="part-link">901-7310</a></td><td class="desc-cell" rowspan="4"><div class="iname">Large Roll-Off Phone Number</div><div class="isize">73" W &times; 10" H</div></td><td rowspan="4">73&times;10</td>
  <td class="r">50</td><td class="r">$14.82</td><td class="r">$16.26</td><td class="r tx-mid bg-mid">+$1.44</td><td class="r tx-mid bg-mid">+9.7%</td>
  <td class="r y24" rowspan="4" id="ro7310-o24"></td><td class="r y24" rowspan="4" id="ro7310-u24" style="font-style:italic;color:#6b7280"></td><td class="r y24" rowspan="4">&mdash;</td><td class="r y24" rowspan="4" id="ro7310-a24" style="font-style:italic;color:#6b7280"></td><td class="r y24" rowspan="4">&mdash;</td>
  <td class="r y25" rowspan="4" id="ro7310-o25"></td><td class="r y25" rowspan="4" id="ro7310-u25" style="font-style:italic;color:#6b7280"></td><td class="r y25" rowspan="4">&mdash;</td><td class="r y25" rowspan="4" id="ro7310-a25" style="font-style:italic;color:#6b7280"></td><td class="r y25" rowspan="4">&mdash;</td>
  <td class="r y26" rowspan="4" id="ro7310-o26"></td><td class="r y26" rowspan="4" id="ro7310-u26" style="font-style:italic;color:#6b7280"></td><td class="r y26" rowspan="4">52</td><td class="r y26" rowspan="4" id="ro7310-a26" style="font-style:italic;color:#6b7280"></td><td class="r y26" rowspan="4">$846</td></tr>
<tr class="bg-mid"><td class="r">100</td><td class="r">$13.62</td><td class="r">$14.90</td><td class="r tx-mid bg-mid">+$1.28</td><td class="r tx-mid bg-mid">+9.4%</td></tr>
<tr class="bg-high"><td class="r">250</td><td class="r">$12.90</td><td class="r">$14.22</td><td class="r tx-high bg-high">+$1.32</td><td class="r tx-high bg-high">+10.2%</td></tr>
<tr><td class="r">500</td><td class="r">$12.66</td><td class="r"><span class="disco">Discontinued</span></td><td class="r">&mdash;</td><td class="r">&mdash;</td></tr>
<tr class="bg-max"><td class="part" rowspan="4"><a href="javascript:void(0)" onclick="goFilter('901-4306')" class="part-link">901-4306</a></td><td class="desc-cell" rowspan="4"><div class="iname">Small Roll-Off Phone Number</div><div class="isize">43.65" W &times; 6" H</div></td><td rowspan="4">43.65&times;6</td>
  <td class="r">50</td><td class="r">$8.28</td><td class="r">$10.30</td><td class="r tx-max bg-max">+$2.02</td><td class="r tx-max bg-max">+24.4%</td>
  <td class="r y24" rowspan="4">1</td><td class="r y24" rowspan="4" style="font-style:italic;color:#6b7280">50</td><td class="r y24" rowspan="4">50</td><td class="r y24" rowspan="4" style="font-style:italic;color:#6b7280">$8.28</td><td class="r y24" rowspan="4">$414</td>
  <td class="r y25" rowspan="4">&mdash;</td><td class="r y25" rowspan="4" style="font-style:italic;color:#6b7280">&mdash;</td><td class="r y25" rowspan="4">&mdash;</td><td class="r y25" rowspan="4" style="font-style:italic;color:#6b7280">&mdash;</td><td class="r y25" rowspan="4">&mdash;</td>
  <td class="r y26" rowspan="4">&mdash;</td><td class="r y26" rowspan="4" style="font-style:italic;color:#6b7280">&mdash;</td><td class="r y26" rowspan="4">&mdash;</td><td class="r y26" rowspan="4" style="font-style:italic;color:#6b7280">&mdash;</td><td class="r y26" rowspan="4">&mdash;</td></tr>
<tr class="bg-max"><td class="r">100</td><td class="r">$7.08</td><td class="r">$8.99</td><td class="r tx-max bg-max">+$1.91</td><td class="r tx-max bg-max">+27.0%</td></tr>
<tr class="bg-max"><td class="r">250</td><td class="r">$6.36</td><td class="r">$8.34</td><td class="r tx-max bg-max">+$1.98</td><td class="r tx-max bg-max">+31.1%</td></tr>
<tr><td class="r">500</td><td class="r">$6.12</td><td class="r"><span class="disco">Discontinued</span></td><td class="r">&mdash;</td><td class="r">&mdash;</td></tr>
</tbody></table></div>
<div class="fn">&#8224; Sales totals include all location-specific variants. Click the part # to drill into the detail.</div>
</div>

<!-- CONTAINER -->
<div>
<div class="sec-hdr">Container Logo Decals (Standard) &nbsp;<span style="font-weight:400;font-size:10px;opacity:.8">MT2183 3.2 mil &middot; 3-Yr Performance</span></div>
<div class="tbl-wrap"><table>
<thead><tr>
  <th style="width:68px">Part #</th><th style="width:220px">Description</th><th style="width:62px">Size</th>
  <th class="r" style="width:42px">Qty Brk</th><th class="r" style="width:50px">2024 Price</th><th class="r" style="width:50px">2026 Price</th>
  <th class="r" style="width:48px">Chg $</th><th class="r" style="width:40px">Chg %</th>
  <th class="r y24" style="width:36px"># Ord '24</th><th class="r y24" style="width:50px">Avg Units/ Ord '24</th><th class="r y24" style="width:44px">2024 Units</th><th class="r y24" style="width:50px">Avg $/pc '24</th><th class="r y24" style="width:54px">2024 Sales</th>
  <th class="r y25" style="width:36px"># Ord '25</th><th class="r y25" style="width:50px">Avg Units/ Ord '25</th><th class="r y25" style="width:44px">2025 Units</th><th class="r y25" style="width:50px">Avg $/pc '25</th><th class="r y25" style="width:54px">2025 Sales</th>
  <th class="r y26" style="width:36px"># Ord '26</th><th class="r y26" style="width:50px">Avg Units/ Ord '26</th><th class="r y26" style="width:44px">2026 Units</th><th class="r y26" style="width:50px">Avg $/pc '26</th><th class="r y26" style="width:54px">2026 Sales</th>
</tr></thead>
<tbody>
<tr class="bg-max"><td class="part" rowspan="5"><a href="javascript:void(0)" onclick="goFilter('1660')" class="part-link">901-1660</a></td>
  <td class="desc-cell" rowspan="5"><div class="iname">Container Logo Decal</div><div class="isize">22.25" W &times; 17.25" H</div><div class="isize" style="color:#2563EB;margin-top:3px" id="vcnt-1660-desc"></div></td>
  <td rowspan="5">22.25&times;17.25</td>
  <td class="r">250</td><td class="r">$4.95 &#8224;</td><td class="r">$6.13</td><td class="r tx-max bg-max">+$1.18</td><td class="r tx-max bg-max">+23.8%</td>
  <td rowspan="5" colspan="15" style="text-align:center;font-size:12px;font-weight:700;color:#92400e;background:#fef3c7;letter-spacing:.01em">&#9660; Location-specific variants only &nbsp;&mdash;&nbsp; see totals row below</td></tr>
<tr class="bg-mid"><td class="r">500</td><td class="r">$4.43</td><td class="r">$4.65</td><td class="r tx-mid bg-mid">+$0.22</td><td class="r tx-mid bg-mid">+5.0%</td></tr>
<tr><td class="r">700</td><td class="r">$3.99</td><td class="r"><span class="disco">Discontinued</span></td><td class="r">&mdash;</td><td class="r">&mdash;</td></tr>
<tr class="bg-high"><td class="r">1,000</td><td class="r">$3.67</td><td class="r">$4.04</td><td class="r tx-high bg-high">+$0.37</td><td class="r tx-high bg-high">+10.1%</td></tr>
<tr class="bg-high"><td class="r">2,500</td><td class="r">$3.21</td><td class="r">$3.53</td><td class="r tx-high bg-high">+$0.32</td><td class="r tx-high bg-high">+10.0%</td></tr>
<tr class="agg-row">
  <td colspan="8">&#9658; All Container (1660) Variants &nbsp;<span id="vcnt-1660" style="font-weight:400;font-size:10px;color:#374151"></span></td>
  <td class="r y24" id="av-1660-o24"></td><td class="r y24" id="av-1660-u24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1660-q24"></td><td class="r y24" id="av-1660-a24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1660-s24"></td>
  <td class="r y25" id="av-1660-o25"></td><td class="r y25" id="av-1660-u25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1660-q25"></td><td class="r y25" id="av-1660-a25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1660-s25"></td>
  <td class="r y26" id="av-1660-o26"></td><td class="r y26" id="av-1660-u26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1660-q26"></td><td class="r y26" id="av-1660-a26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1660-s26"></td>
</tr>
</tbody></table></div>
<div style="margin-top:6px;padding:8px 14px;background:#fffbeb;border:1px solid #f59e0b;border-left:4px solid #d97706;border-radius:4px;font-size:12px;color:#78350f;display:flex;align-items:center;gap:8px">
  <span style="font-size:16px">&#9888;</span>
  <span><strong>&#8224; Pricing note:</strong> The 2024 system price for 901-1660 was $4.95; the printed 2024 program sheet listed $5.95. System pricing is used as the authoritative source throughout this report.</span>
</div>
</div>

<!-- CART -->
<div>
<div class="sec-hdr">Cart Logo Decals (Standard) &nbsp;<span style="font-weight:400;font-size:10px;opacity:.8">MT2183 3.2 mil &middot; 3-Yr Performance</span></div>
<div class="tbl-wrap"><table>
<thead><tr>
  <th style="width:68px">Part #</th><th style="width:220px">Description</th><th style="width:62px">Size</th>
  <th class="r" style="width:42px">Qty Brk</th><th class="r" style="width:50px">2024 Price</th><th class="r" style="width:50px">2026 Price</th>
  <th class="r" style="width:48px">Chg $</th><th class="r" style="width:40px">Chg %</th>
  <th class="r y24" style="width:36px"># Ord '24</th><th class="r y24" style="width:50px">Avg Units/ Ord '24</th><th class="r y24" style="width:44px">2024 Units</th><th class="r y24" style="width:50px">Avg $/pc '24</th><th class="r y24" style="width:54px">2024 Sales</th>
  <th class="r y25" style="width:36px"># Ord '25</th><th class="r y25" style="width:50px">Avg Units/ Ord '25</th><th class="r y25" style="width:44px">2025 Units</th><th class="r y25" style="width:50px">Avg $/pc '25</th><th class="r y25" style="width:54px">2025 Sales</th>
  <th class="r y26" style="width:36px"># Ord '26</th><th class="r y26" style="width:50px">Avg Units/ Ord '26</th><th class="r y26" style="width:44px">2026 Units</th><th class="r y26" style="width:50px">Avg $/pc '26</th><th class="r y26" style="width:54px">2026 Sales</th>
</tr></thead>
<tbody>
<tr class="bg-low"><td class="part" rowspan="5"><a href="javascript:void(0)" onclick="goFilter('1409')" class="part-link">901-1409</a></td>
  <td class="desc-cell" rowspan="5"><div class="iname">Cart Logo Decal</div><div class="isize">14" W &times; 9" H</div></td>
  <td rowspan="5">14&times;9</td>
  <td class="r">250</td><td class="r">$3.65</td><td class="r">$3.76</td><td class="r tx-low bg-low">+$0.11</td><td class="r tx-low bg-low">+3.0%</td>
  <td rowspan="5" colspan="15" style="text-align:center;font-size:12px;font-weight:700;color:#92400e;background:#fef3c7;letter-spacing:.01em">&#9660; Location-specific variants only &nbsp;&mdash;&nbsp; see totals row below</td></tr>
<tr class="bg-high"><td class="r">500</td><td class="r">$2.31</td><td class="r">$2.54</td><td class="r tx-high bg-high">+$0.23</td><td class="r tx-high bg-high">+10.0%</td></tr>
<tr><td class="r">700</td><td class="r">$1.93</td><td class="r"><span class="disco">Discontinued</span></td><td class="r">&mdash;</td><td class="r">&mdash;</td></tr>
<tr class="bg-high"><td class="r">1,000</td><td class="r">$1.64</td><td class="r">$1.80</td><td class="r tx-high bg-high">+$0.16</td><td class="r tx-high bg-high">+9.8%</td></tr>
<tr class="bg-high"><td class="r">2,500</td><td class="r">$1.24</td><td class="r">$1.36</td><td class="r tx-high bg-high">+$0.12</td><td class="r tx-high bg-high">+9.7%</td></tr>
<tr class="agg-row">
  <td colspan="8">&#9658; All Cart (1409) Variants &nbsp;<span id="vcnt-1409" style="font-weight:400;font-size:10px;color:#374151"></span></td>
  <td class="r y24" id="av-1409-o24"></td><td class="r y24" id="av-1409-u24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1409-q24"></td><td class="r y24" id="av-1409-a24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1409-s24"></td>
  <td class="r y25" id="av-1409-o25"></td><td class="r y25" id="av-1409-u25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1409-q25"></td><td class="r y25" id="av-1409-a25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1409-s25"></td>
  <td class="r y26" id="av-1409-o26"></td><td class="r y26" id="av-1409-u26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1409-q26"></td><td class="r y26" id="av-1409-a26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1409-s26"></td>
</tr>
<tr class="bg-low"><td class="part" rowspan="5"><a href="javascript:void(0)" onclick="goFilter('1208')" class="part-link">901-1208</a></td>
  <td class="desc-cell" rowspan="5"><div class="iname">Small Cart Logo Decal</div><div class="isize">12" W &times; 8" H</div></td>
  <td rowspan="5">12&times;8</td>
  <td class="r">250</td><td class="r">$2.27</td><td class="r">$2.34</td><td class="r tx-low bg-low">+$0.07</td><td class="r tx-low bg-low">+3.1%</td>
  <td rowspan="5" colspan="15" style="text-align:center;font-size:12px;font-weight:700;color:#92400e;background:#fef3c7;letter-spacing:.01em">&#9660; Location-specific variants only &nbsp;&mdash;&nbsp; see totals row below</td></tr>
<tr class="bg-low"><td class="r">500</td><td class="r">$1.82</td><td class="r">$1.91</td><td class="r tx-low bg-low">+$0.09</td><td class="r tx-low bg-low">+4.9%</td></tr>
<tr><td class="r">700</td><td class="r">$1.69</td><td class="r"><span class="disco">Discontinued</span></td><td class="r">&mdash;</td><td class="r">&mdash;</td></tr>
<tr class="bg-mid"><td class="r">1,000</td><td class="r">$1.59</td><td class="r">$1.67</td><td class="r tx-mid bg-mid">+$0.08</td><td class="r tx-mid bg-mid">+5.0%</td></tr>
<tr class="bg-low"><td class="r">2,500</td><td class="r">$1.46</td><td class="r">$1.53</td><td class="r tx-low bg-low">+$0.07</td><td class="r tx-low bg-low">+4.8%</td></tr>
<tr class="agg-row">
  <td colspan="8">&#9658; All Small Cart (1208) Variants &nbsp;<span id="vcnt-1208" style="font-weight:400;font-size:10px;color:#374151"></span></td>
  <td class="r y24" id="av-1208-o24"></td><td class="r y24" id="av-1208-u24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1208-q24"></td><td class="r y24" id="av-1208-a24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1208-s24"></td>
  <td class="r y25" id="av-1208-o25"></td><td class="r y25" id="av-1208-u25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1208-q25"></td><td class="r y25" id="av-1208-a25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1208-s25"></td>
  <td class="r y26" id="av-1208-o26"></td><td class="r y26" id="av-1208-u26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1208-q26"></td><td class="r y26" id="av-1208-a26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1208-s26"></td>
</tr>
</tbody></table></div>
</div>

<!-- COASTAL HT -->
<div>
<div class="sec-hdr">Coastal Branch Decals (High-Tack / FEL Plastic) &nbsp;<span style="font-weight:400;font-size:10px;opacity:.8">3MIJ39 3.5 mil &middot; 5-Yr Life &middot; High-Tack adhesive</span></div>
<div class="tbl-wrap"><table>
<thead><tr>
  <th style="width:68px">Part #</th><th style="width:220px">Description</th><th style="width:62px">Size</th>
  <th class="r" style="width:42px">Qty Brk</th><th class="r" style="width:50px">2024 Price</th><th class="r" style="width:50px">2026 Price</th>
  <th class="r" style="width:48px">Chg $</th><th class="r" style="width:40px">Chg %</th>
  <th class="r y24" style="width:36px"># Ord '24</th><th class="r y24" style="width:50px">Avg Units/ Ord '24</th><th class="r y24" style="width:44px">2024 Units</th><th class="r y24" style="width:50px">Avg $/pc '24</th><th class="r y24" style="width:54px">2024 Sales</th>
  <th class="r y25" style="width:36px"># Ord '25</th><th class="r y25" style="width:50px">Avg Units/ Ord '25</th><th class="r y25" style="width:44px">2025 Units</th><th class="r y25" style="width:50px">Avg $/pc '25</th><th class="r y25" style="width:54px">2025 Sales</th>
  <th class="r y26" style="width:36px"># Ord '26</th><th class="r y26" style="width:50px">Avg Units/ Ord '26</th><th class="r y26" style="width:44px">2026 Units</th><th class="r y26" style="width:50px">Avg $/pc '26</th><th class="r y26" style="width:54px">2026 Sales</th>
</tr></thead>
<tbody>
<tr class="bg-low"><td class="part" rowspan="4"><a href="javascript:void(0)" onclick="goFilter('1660-HT')" class="part-link">901-1660-HT</a></td>
  <td class="desc-cell" rowspan="4"><div class="iname">Container Logo Decal (High-Tack)</div><div class="isize">22.25" W &times; 17.25" H</div></td>
  <td rowspan="4">22.25&times;17.25</td>
  <td class="r">100</td><td class="r">$8.25</td><td class="r">$8.50</td><td class="r tx-low bg-low">+$0.25</td><td class="r tx-low bg-low">+3.0%</td>
  <td rowspan="4" colspan="15" style="text-align:center;font-size:12px;font-weight:700;color:#92400e;background:#fef3c7;letter-spacing:.01em">&#9660; Location-specific variants only &nbsp;&mdash;&nbsp; see totals row below</td></tr>
<tr class="bg-low"><td class="r">200</td><td class="r">$7.15</td><td class="r">$7.36</td><td class="r tx-low bg-low">+$0.21</td><td class="r tx-low bg-low">+2.9%</td></tr>
<tr class="bg-low"><td class="r">300</td><td class="r">$6.79</td><td class="r">$6.99</td><td class="r tx-low bg-low">+$0.20</td><td class="r tx-low bg-low">+2.9%</td></tr>
<tr class="bg-low"><td class="r">500</td><td class="r">$6.50</td><td class="r">$6.70</td><td class="r tx-low bg-low">+$0.20</td><td class="r tx-low bg-low">+3.1%</td></tr>
<tr class="agg-row">
  <td colspan="8">&#9658; All Container HT (1660-HT) Variants &nbsp;<span id="vcnt-1660ht" style="font-weight:400;font-size:10px;color:#374151"></span></td>
  <td class="r y24" id="av-1660ht-o24"></td><td class="r y24" id="av-1660ht-u24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1660ht-q24"></td><td class="r y24" id="av-1660ht-a24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1660ht-s24"></td>
  <td class="r y25" id="av-1660ht-o25"></td><td class="r y25" id="av-1660ht-u25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1660ht-q25"></td><td class="r y25" id="av-1660ht-a25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1660ht-s25"></td>
  <td class="r y26" id="av-1660ht-o26"></td><td class="r y26" id="av-1660ht-u26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1660ht-q26"></td><td class="r y26" id="av-1660ht-a26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1660ht-s26"></td>
</tr>
<tr class="bg-low"><td class="part" rowspan="4"><a href="javascript:void(0)" onclick="goFilter('1409-HT')" class="part-link">901-1409-HT</a></td>
  <td class="desc-cell" rowspan="4"><div class="iname">Cart Logo Decal (High-Tack)</div><div class="isize">14" W &times; 9" H</div></td>
  <td rowspan="4">14&times;9</td>
  <td class="r">100</td><td class="r">$4.95</td><td class="r">$5.10</td><td class="r tx-low bg-low">+$0.15</td><td class="r tx-low bg-low">+3.0%</td>
  <td rowspan="4" colspan="15" style="text-align:center;font-size:12px;font-weight:700;color:#92400e;background:#fef3c7;letter-spacing:.01em">&#9660; Location-specific variants only &nbsp;&mdash;&nbsp; see totals row below</td></tr>
<tr class="bg-low"><td class="r">200</td><td class="r">$3.83</td><td class="r">$3.94</td><td class="r tx-low bg-low">+$0.11</td><td class="r tx-low bg-low">+2.9%</td></tr>
<tr class="bg-low"><td class="r">300</td><td class="r">$3.46</td><td class="r">$3.56</td><td class="r tx-low bg-low">+$0.10</td><td class="r tx-low bg-low">+2.9%</td></tr>
<tr class="bg-low"><td class="r">500</td><td class="r">$3.16</td><td class="r">$3.25</td><td class="r tx-low bg-low">+$0.09</td><td class="r tx-low bg-low">+2.8%</td></tr>
<tr class="agg-row">
  <td colspan="8">&#9658; All Cart HT (1409-HT) Variants &nbsp;<span id="vcnt-1409ht" style="font-weight:400;font-size:10px;color:#374151"></span></td>
  <td class="r y24" id="av-1409ht-o24"></td><td class="r y24" id="av-1409ht-u24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1409ht-q24"></td><td class="r y24" id="av-1409ht-a24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1409ht-s24"></td>
  <td class="r y25" id="av-1409ht-o25"></td><td class="r y25" id="av-1409ht-u25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1409ht-q25"></td><td class="r y25" id="av-1409ht-a25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1409ht-s25"></td>
  <td class="r y26" id="av-1409ht-o26"></td><td class="r y26" id="av-1409ht-u26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1409ht-q26"></td><td class="r y26" id="av-1409ht-a26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1409ht-s26"></td>
</tr>
<tr class="bg-low"><td class="part" rowspan="4"><a href="javascript:void(0)" onclick="goFilter('1208-HT')" class="part-link">901-1208-HT</a></td>
  <td class="desc-cell" rowspan="4"><div class="iname">Small Cart Logo Decal (High-Tack)</div><div class="isize">12" W &times; 8" H</div></td>
  <td rowspan="4">12&times;8</td>
  <td class="r">100</td><td class="r">$3.94</td><td class="r">$4.06</td><td class="r tx-low bg-low">+$0.12</td><td class="r tx-low bg-low">+3.0%</td>
  <td rowspan="4" colspan="15" style="text-align:center;font-size:12px;font-weight:700;color:#92400e;background:#fef3c7;letter-spacing:.01em">&#9660; Location-specific variants only &nbsp;&mdash;&nbsp; see totals row below</td></tr>
<tr class="bg-low"><td class="r">200</td><td class="r">$2.82</td><td class="r">$2.90</td><td class="r tx-low bg-low">+$0.08</td><td class="r tx-low bg-low">+2.8%</td></tr>
<tr class="bg-low"><td class="r">300</td><td class="r">$2.44</td><td class="r">$2.51</td><td class="r tx-low bg-low">+$0.07</td><td class="r tx-low bg-low">+2.9%</td></tr>
<tr class="bg-low"><td class="r">500</td><td class="r">$2.14</td><td class="r">$2.20</td><td class="r tx-low bg-low">+$0.06</td><td class="r tx-low bg-low">+2.8%</td></tr>
<tr class="agg-row">
  <td colspan="8">&#9658; All Small Cart HT (1208-HT) Variants &nbsp;<span id="vcnt-1208ht" style="font-weight:400;font-size:10px;color:#374151"></span></td>
  <td class="r y24" id="av-1208ht-o24"></td><td class="r y24" id="av-1208ht-u24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1208ht-q24"></td><td class="r y24" id="av-1208ht-a24" style="font-style:italic;color:#6b7280"></td><td class="r y24" id="av-1208ht-s24"></td>
  <td class="r y25" id="av-1208ht-o25"></td><td class="r y25" id="av-1208ht-u25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1208ht-q25"></td><td class="r y25" id="av-1208ht-a25" style="font-style:italic;color:#6b7280"></td><td class="r y25" id="av-1208ht-s25"></td>
  <td class="r y26" id="av-1208ht-o26"></td><td class="r y26" id="av-1208ht-u26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1208ht-q26"></td><td class="r y26" id="av-1208ht-a26" style="font-style:italic;color:#6b7280"></td><td class="r y26" id="av-1208ht-s26"></td>
</tr>
</tbody></table></div>
<div style="margin-top:6px;padding:8px 14px;background:#f9fafb;border:1px solid #d1d5db;border-left:4px solid #374151;border-radius:4px;display:flex;align-items:center;gap:16px;font-size:12px;color:#374151">
  <span style="font-weight:700;white-space:nowrap">Price Change Color Key:</span>
  <span class="bg-low" style="padding:3px 10px;border-radius:3px;font-weight:700;font-size:12px">&lt; 3%</span>
  <span class="bg-mid" style="padding:3px 10px;border-radius:3px;font-weight:700;font-size:12px">3% &ndash; 10%</span>
  <span class="bg-high" style="padding:3px 10px;border-radius:3px;font-weight:700;font-size:12px">10% &ndash; 20%</span>
  <span class="bg-max" style="padding:3px 10px;border-radius:3px;font-weight:700;font-size:12px">&gt; 20%</span>
</div>
</div>

</div><!-- /content pane0 -->
</div><!-- /pane0 -->

<!-- TAB 1: DRILL-DOWN -->
<div class="tab-pane" id="pane1">
<div class="content">
<div class="filter-bar">
  <input type="text" id="srch" placeholder="Search part # or description..." oninput="filterRows()">
  <button onclick="clearFilter()" class="btn btn-cyan">Clear</button>
  <span class="cnt" id="cnt"><?php echo $itemCount; ?> items</span>
  <button onclick="exportXL()" class="btn btn-green" style="margin-left:auto">&#128194; Export to Excel</button>
</div>
<div class="sec-hdr">All WP Customer Items &mdash; Invoiced 2024 / 2025 / 2026</div>
<div class="tbl-wrap" style="max-height:75vh;overflow-y:auto">
<table id="tDrill">
<thead><tr>
  <th class="sortable" data-col="0">Part #</th>
  <th class="sortable" data-col="1">Description</th>
  <th class="r sortable y24" data-col="4"># Ord '24</th>
  <th class="r sortable y24" data-col="15">Avg Units/Ord '24</th>
  <th class="r sortable y24" data-col="2">2024 Units</th>
  <th class="r sortable y24" data-col="12">Avg $/pc '24</th>
  <th class="r sortable y24" data-col="3">2024 Sales</th>
  <th class="r sortable y25" data-col="7"># Ord '25</th>
  <th class="r sortable y25" data-col="16">Avg Units/Ord '25</th>
  <th class="r sortable y25" data-col="5">2025 Units</th>
  <th class="r sortable y25" data-col="13">Avg $/pc '25</th>
  <th class="r sortable y25" data-col="6">2025 Sales</th>
  <th class="r sortable y26" data-col="10"># Ord '26</th>
  <th class="r sortable y26" data-col="17">Avg Units/Ord '26</th>
  <th class="r sortable y26" data-col="8">2026 Units</th>
  <th class="r sortable y26" data-col="14">Avg $/pc '26</th>
  <th class="r sortable y26" data-col="9">2026 Sales</th>
  <th class="r sortable" data-col="11">3-Yr Total</th>
</tr></thead>
<tbody id="drillBody"></tbody>
</table>
</div>
</div>
</div>

<script>
var RAW=<?php echo $rawJson; ?>;

function fmt(v){if(v===undefined||v===null)return'-';var n=parseFloat(v);if(n===0)return'-';if(n<0)return'<span class="neg">($'+Math.abs(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',')+')';return'$'+n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');}
function fmtN(v){var n=parseInt(v)||0;if(n===0)return'-';if(n<0)return'<span class="neg">('+Math.abs(n).toLocaleString()+')</span>';return n.toLocaleString();}
function fmtS(v){var n=parseFloat(v)||0;if(n<=0)return'-';return'$'+Math.round(n).toLocaleString();}
function fmtQ(v){var n=parseInt(v)||0;if(n<=0)return'-';return n.toLocaleString();}
function fmtAvg(s,q){var n=parseFloat(s)||0,d=parseFloat(q)||0;if(d<=0||n<=0)return'-';return'$'+(n/d).toFixed(2);}
function fmtUPO(u,o){var uv=parseFloat(u)||0,ov=parseFloat(o)||0;if(ov<=0||uv<=0)return'-';return Math.round(uv/ov).toLocaleString();}

// Aggregate helpers
function aggVars(pattern,exclude){
  return RAW.filter(function(r){
    return r[0].indexOf(pattern)>=0&&(!exclude||r[0].indexOf(exclude)<0);
  });
}
function sumField(rows,idx){return rows.reduce(function(s,r){return s+(parseFloat(r[idx])||0);},0);}

function fillAgg(prefix,pattern,exclude,filterPat){
  var rows=aggVars(pattern,exclude);
  var n=rows.length;
  var cntEl=document.getElementById('vcnt-'+prefix);
  if(cntEl)cntEl.textContent='('+n+' location variants)';
  var cntDesc=document.getElementById('vcnt-'+prefix+'-desc');
  if(cntDesc)cntDesc.textContent=n+' location-specific items';
  var yrs=[['24',2,3,4],['25',5,6,7],['26',8,9,10]];
  yrs.forEach(function(y){
    var q=sumField(rows,y[1]),s=sumField(rows,y[2]),o=sumField(rows,y[3]);
    var qEl=document.getElementById('av-'+prefix+'-q'+y[0]);
    var sEl=document.getElementById('av-'+prefix+'-s'+y[0]);
    var oEl=document.getElementById('av-'+prefix+'-o'+y[0]);
    var uEl=document.getElementById('av-'+prefix+'-u'+y[0]);
    var aEl=document.getElementById('av-'+prefix+'-a'+y[0]);
    if(qEl)qEl.textContent=fmtQ(q);
    if(sEl)sEl.textContent=fmtS(s);
    if(uEl)uEl.textContent=fmtUPO(q,o);
    if(aEl)aEl.textContent=fmtAvg(s,q);
    if(oEl){
      if(o>0){oEl.innerHTML='<a href="javascript:void(0)" onclick="goFilter(\''+filterPat+'\')" class="ord-link">'+Math.round(o)+'</a>';}
      else{oEl.textContent='-';}
    }
  });
}

function fillFromRaw(partNum,idPfx){
  var r=RAW.find(function(x){return x[0]===partNum;});
  if(!r)return;
  var yrs=[['24',4,3,2],['25',7,6,5],['26',10,9,8]];
  yrs.forEach(function(y){
    var oEl=document.getElementById(idPfx+'-o'+y[0]);
    if(oEl)oEl.textContent=((parseInt(r[y[1]])||0)>0)?r[y[1]].toString():'-';
    var uEl=document.getElementById(idPfx+'-u'+y[0]);
    if(uEl)uEl.textContent=fmtUPO(r[y[3]],r[y[1]]);
    var aEl=document.getElementById(idPfx+'-a'+y[0]);
    if(aEl)aEl.textContent=fmtAvg(r[y[2]],r[y[3]]);
  });
}

// Populate Tab 1 order counts
fillFromRaw('901-0001','ro0001');
fillFromRaw('901-0002','ro0002');
fillFromRaw('901-7310','ro7310');

// Variant aggregates (standard)
fillAgg('1660','1660','1660-HT','1660');
fillAgg('1409','1409','1409-HT','1409');
fillAgg('1208','1208','1208-HT','1208');

// Variant aggregates (HT)
fillAgg('1660ht','1660-HT',null,'1660-HT');
fillAgg('1409ht','1409-HT',null,'1409-HT');
fillAgg('1208ht','1208-HT',null,'1208-HT');

// Tab 2 drill-down
var sortCol=-1,sortDir=1;
function renderDrill(data){
  var b=document.getElementById('drillBody');
  b.innerHTML=data.map(function(r){
    var tot=(parseFloat(r[3])||0)+(parseFloat(r[6])||0)+(parseFloat(r[9])||0);
    return '<tr>'
      +'<td style="font-weight:700;color:#1e3a8a;white-space:nowrap">'+r[0]+'</td>'
      +'<td>'+r[1]+'</td>'
      +'<td class="r">'+fmtN(r[4])+'</td>'
      +'<td class="r" style="color:#6b7280;font-style:italic">'+fmtUPO(r[2],r[4])+'</td>'
      +'<td class="r">'+fmtN(r[2])+'</td>'
      +'<td class="r" style="color:#6b7280;font-style:italic">'+fmtAvg(r[3],r[2])+'</td>'
      +'<td class="r">'+fmt(r[3])+'</td>'
      +'<td class="r">'+fmtN(r[7])+'</td>'
      +'<td class="r" style="color:#6b7280;font-style:italic">'+fmtUPO(r[5],r[7])+'</td>'
      +'<td class="r">'+fmtN(r[5])+'</td>'
      +'<td class="r" style="color:#6b7280;font-style:italic">'+fmtAvg(r[6],r[5])+'</td>'
      +'<td class="r">'+fmt(r[6])+'</td>'
      +'<td class="r">'+fmtN(r[10])+'</td>'
      +'<td class="r" style="color:#6b7280;font-style:italic">'+fmtUPO(r[8],r[10])+'</td>'
      +'<td class="r">'+fmtN(r[8])+'</td>'
      +'<td class="r" style="color:#6b7280;font-style:italic">'+fmtAvg(r[9],r[8])+'</td>'
      +'<td class="r">'+fmt(r[9])+'</td>'
      +'<td class="r" style="font-weight:600">'+fmt(tot)+'</td>'
      +'</tr>';
  }).join('');
  document.getElementById('cnt').textContent=data.length+' items';
}
var filtered=RAW.slice();
renderDrill(filtered);

function filterRows(){
  var q=document.getElementById('srch').value.trim().toLowerCase();
  filtered=q?RAW.filter(function(r){return r[0].toLowerCase().indexOf(q)>=0||r[1].toLowerCase().indexOf(q)>=0;}):RAW.slice();
  if(sortCol>=0)sortData();else renderDrill(filtered);
}
function clearFilter(){document.getElementById('srch').value='';filterRows();}

function goFilter(pat){
  showTab(1);
  document.getElementById('srch').value=pat;
  filterRows();
}

document.querySelectorAll('#tDrill th.sortable').forEach(function(th){
  th.addEventListener('click',function(){
    var c=parseInt(th.dataset.col);
    if(sortCol===c)sortDir*=-1;else{sortCol=c;sortDir=-1;}
    document.querySelectorAll('#tDrill th').forEach(function(h){h.classList.remove('sort-asc','sort-desc');});
    th.classList.add(sortDir===1?'sort-asc':'sort-desc');
    sortData();
  });
});
function sortData(){
  filtered.sort(function(a,b){
    var va,vb;
    if(sortCol===11){va=(parseFloat(a[3])||0)+(parseFloat(a[6])||0)+(parseFloat(a[9])||0);vb=(parseFloat(b[3])||0)+(parseFloat(b[6])||0)+(parseFloat(b[9])||0);}
    else if(sortCol===12){va=(parseFloat(a[2])||0)>0?(parseFloat(a[3])||0)/(parseFloat(a[2])||1):0;vb=(parseFloat(b[2])||0)>0?(parseFloat(b[3])||0)/(parseFloat(b[2])||1):0;}
    else if(sortCol===13){va=(parseFloat(a[5])||0)>0?(parseFloat(a[6])||0)/(parseFloat(a[5])||1):0;vb=(parseFloat(b[5])||0)>0?(parseFloat(b[6])||0)/(parseFloat(b[5])||1):0;}
    else if(sortCol===14){va=(parseFloat(a[8])||0)>0?(parseFloat(a[9])||0)/(parseFloat(a[8])||1):0;vb=(parseFloat(b[8])||0)>0?(parseFloat(b[9])||0)/(parseFloat(b[8])||1):0;}
    else if(sortCol===15){va=(parseFloat(a[4])||0)>0?(parseFloat(a[2])||0)/(parseFloat(a[4])||1):0;vb=(parseFloat(b[4])||0)>0?(parseFloat(b[2])||0)/(parseFloat(b[4])||1):0;}
    else if(sortCol===16){va=(parseFloat(a[7])||0)>0?(parseFloat(a[5])||0)/(parseFloat(a[7])||1):0;vb=(parseFloat(b[7])||0)>0?(parseFloat(b[5])||0)/(parseFloat(b[7])||1):0;}
    else if(sortCol===17){va=(parseFloat(a[10])||0)>0?(parseFloat(a[8])||0)/(parseFloat(a[10])||1):0;vb=(parseFloat(b[10])||0)>0?(parseFloat(b[8])||0)/(parseFloat(b[10])||1):0;}
    else{va=a[sortCol]||0;vb=b[sortCol]||0;}
    if(typeof va==='string')return sortDir*(va.localeCompare(vb));
    return sortDir*(vb-va);
  });
  renderDrill(filtered);
}

function showTab(i){
  document.querySelectorAll('.tab').forEach(function(t,idx){t.classList.toggle('active',idx===i);});
  document.querySelectorAll('.tab-pane').forEach(function(p,idx){p.classList.toggle('active',idx===i);});
}

function exportXL(){
  var active=document.querySelector('.tab-pane.active');
  var tbl=active.querySelector('table');
  if(!tbl){alert('No table to export');return;}
  var blob=new Blob([tbl.outerHTML],{type:'application/vnd.ms-excel'});
  var a=document.createElement('a');
  a.href=URL.createObjectURL(blob);
  a.download='WP_Program_<?php echo date('Y-m-d'); ?>.xls';
  a.click();
}
</script>
</body></html>
