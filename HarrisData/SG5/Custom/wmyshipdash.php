<?php
/**
 * W/M/Y Shipping Summary Dashboard
 * Mirrors the W/M/Y Bookings Summary Dashboard style.
 *
 * Ship date uses ODDTLI on OEORDT (detail line).
 * Shipped quantity uses ODQSTC on OEORDT.
 */
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

// ── CYMD helpers (IBM i 7-digit packed date: (Year-1900)*10000 + MM*100 + DD) ──
function dateToCYMD($d) {
    return ((int)$d->format('Y') - 1900) * 10000
         + (int)$d->format('n') * 100
         + (int)$d->format('j');
}

$now        = new DateTime();
$dow        = (int)$now->format('N');
$weekStart  = clone $now;
$weekStart->modify('-' . ($dow - 1) . ' days');
$monthStart = new DateTime($now->format('Y-m-01'));
$yearStart  = new DateTime($now->format('Y-01-01'));

$cymd = array(
    'today' => dateToCYMD($now),
    'week'  => dateToCYMD($weekStart),
    'month' => dateToCYMD($monthStart),
    'year'  => dateToCYMD($yearStart),
);

// ── Query: shipped (not yet invoiced) by salesperson for a CYMD date range ──
function fetchShipping($conn, $from, $to) {
    $sql = "
        SELECT
            COALESCE(TRIM(s.SMSNA1), TRIM(CHAR(h.OESLSM))) AS nm,
            TRIM(CHAR(h.OESLSM))                            AS slsnum,
            SUM(d.ODQSTC * d.ODSLPR)                        AS amt
        FROM SGHDSDATA.OEORHD h
        JOIN SGHDSDATA.OEORDT d ON h.\"OEORD#\" = d.\"ODORD#\"
        LEFT JOIN SGHDSDATA.HDSLSM s ON h.OESLSM = s.SMSLSM
        WHERE d.ODQSTC > 0
          AND d.ODDTLI BETWEEN $from AND $to
        GROUP BY s.SMSNA1, h.OESLSM
        ORDER BY s.SMSNA1
    ";

    $stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    $rows  = array();
    $total = 0.0;
    if ($stmt) {
        while ($r = db2_fetch_assoc($stmt)) {
            $a      = (float)(isset($r['AMT']) ? $r['AMT'] : 0);
            $rows[] = array(
                'name'   => (string)(isset($r['NM'])     ? $r['NM']     : ''),
                'slsnum' => (string)(isset($r['SLSNUM']) ? $r['SLSNUM'] : ''),
                'amt'    => $a,
            );
            $total += $a;
        }
        db2_free_stmt($stmt);
    }
    return array(
        'total' => $total,
        'rows'  => $rows,
        'err'   => $stmt ? '' : db2_stmt_errormsg(),
    );
}

$conn    = $i5Connect->getConnection();
$periods = array(
    'day'   => fetchShipping($conn, $cymd['today'], $cymd['today']),
    'week'  => fetchShipping($conn, $cymd['week'],  $cymd['today']),
    'month' => fetchShipping($conn, $cymd['month'], $cymd['today']),
    'year'  => fetchShipping($conn, $cymd['year'],  $cymd['today']),
);

// ── Build merged summary table ────────────────────────────────────────────────
$summary = array();
foreach ($periods as $key => $p) {
    foreach ($p['rows'] as $r) {
        $nm = $r['name'];
        if (!isset($summary[$nm])) {
            $summary[$nm] = array(
                'name'   => $nm,
                'slsnum' => $r['slsnum'],
                'day'    => 0.0,
                'week'   => 0.0,
                'month'  => 0.0,
                'year'   => 0.0,
            );
        }
        $summary[$nm][$key] = $r['amt'];
    }
}
ksort($summary);

// ── Colour palette — index is consistent per salesperson ─────────────────────
$palette = array(
    '#FF6384','#FF9F40','#FFCD56','#4BC0C0','#36A2EB',
    '#9966FF','#C9CBCF','#7BC8A4','#E7E9ED','#58508D',
    '#BC5090','#FF6361','#FFA600','#003F5C','#2F4B7C',
);
$slsIndex = array();
$idx = 0;
foreach ($periods['year']['rows'] as $r) {
    if (!isset($slsIndex[$r['name']])) {
        $slsIndex[$r['name']] = $idx++;
    }
}
foreach ($periods as $p) {
    foreach ($p['rows'] as $r) {
        if (!isset($slsIndex[$r['name']])) {
            $slsIndex[$r['name']] = $idx++;
        }
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function money($v) {
    return '$' . number_format((float)$v, 2);
}
function firstWord($s) {
    $parts = explode(' ', trim($s));
    return $parts[0];
}

function jsChartData($period, $label, $slsIndex, $palette) {
    $rows      = $period['rows'];
    $labelArr  = array();
    $fullArr   = array();
    $dataArr   = array();
    $colorArr  = array();
    $palCount  = count($palette);

    foreach ($rows as $r) {
        $labelArr[] = firstWord($r['name']);
        $fullArr[]  = $r['name'];
        $dataArr[]  = round($r['amt'], 2);
        $colorArr[] = $palette[$slsIndex[$r['name']] % $palCount];
    }

    $labels = json_encode($labelArr);
    $full   = json_encode($fullArr);
    $data   = json_encode($dataArr);
    $colors = json_encode($colorArr);
    $pLabel = json_encode($label);

return <<<JS
{
    type: 'bar',
    data: {
        labels: $labels,
        datasets: [{
            label: $pLabel,
            data: $data,
            backgroundColor: $colors,
            borderRadius: 3,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    title: function(items) {
                        var fullNames = $full;
                        return fullNames[items[0].dataIndex] || items[0].label;
                    },
                    label: function(ctx) {
                        return ' \$' + ctx.parsed.y.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
                    }
                }
            }
        },
        scales: {
            x: { ticks: { color:'#bbb', font:{size:11} }, grid:{ color:'#2a3a5a' } },
            y: {
                ticks: {
                    color:'#bbb', font:{size:11},
                    callback: function(v) {
                        return v >= 1000000 ? '\$'+(v/1000000).toFixed(1)+'M'
                             : v >= 1000    ? '\$'+(v/1000).toFixed(0)+'K'
                             : '\$'+v.toFixed(0);
                    }
                },
                grid:{ color:'#2a3a5a' }
            }
        }
    }
}
JS;
}

$refreshMins = 5;
$refreshTime = $now->format('g:i:s A');
$dateLabel   = 'As of: ' . $now->format('D, M j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>W/M/Y Shipping Summary Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #131d35;
    color: #dde;
    min-height: 100vh;
}

.topbar {
    background: #0b1225;
    padding: 7px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #1e3060;
    position: sticky; top: 0; z-index: 100;
}
.topbar h1 {
    font-size: 17px;
    font-weight: bold;
    color: #e8eaf0;
    letter-spacing: 0.4px;
}
.right-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: #99aacc;
}
.as-of-badge {
    background: #f5a623;
    color: #000;
    border-radius: 12px;
    padding: 2px 10px;
    font-weight: bold;
    font-size: 11px;
    white-space: nowrap;
}
.refresh-btn {
    background: #1e4da0;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 5px 13px;
    cursor: pointer;
    font-size: 12px;
}
.refresh-btn:hover { background: #2a5fc0; }
.auto-meta { font-size: 11px; color: #8899bb; line-height: 1.4; }

.dash-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2px;
    padding: 2px;
}

.panel {
    background: #111927;
    border: 1px solid #1e3060;
    overflow: hidden;
}
.panel-header {
    background: #162548;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: bold;
    color: #c8d8f0;
    letter-spacing: 1px;
    border-bottom: 1px solid #1e3060;
    display: flex;
    align-items: center;
    gap: 6px;
}
.panel-header::before { content: '\25A0'; color: #4a90d9; font-size: 10px; }

.total-box {
    padding: 18px 12px 12px;
    text-align: center;
}
.total-amount {
    font-size: 44px;
    font-weight: bold;
    font-family: 'Courier New', Courier, monospace;
    letter-spacing: 3px;
    line-height: 1;
}
.total-label {
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 2.5px;
    margin-top: 6px;
    opacity: 0.80;
}

.theme-day  .total-box, .theme-week .total-box  { background: #040c1a; }
.theme-month .total-box                          { background: #1a0e00; }
.theme-year  .total-box                          { background: #001a0d; }

.theme-day  .total-amount, .theme-day  .total-label,
.theme-week .total-amount, .theme-week .total-label { color: #00e5ff; }
.theme-month .total-amount, .theme-month .total-label { color: #ffb300; }
.theme-year  .total-amount, .theme-year  .total-label { color: #00e676; }

.chart-wrap { padding: 4px 10px 10px; }
.chart-title {
    font-size: 11px;
    font-weight: bold;
    color: #99aacc;
    text-align: center;
    margin-bottom: 4px;
}
.chart-container { height: 215px; position: relative; }

.summary-section {
    margin: 2px;
    border: 1px solid #1e3060;
    background: #0f1828;
}
.summary-header {
    background: #162548;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: bold;
    color: #c8d8f0;
    letter-spacing: 1px;
    text-align: center;
    border-bottom: 2px solid #1e3060;
}
.summary-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.summary-table th {
    background: #162040;
    color: #a8bcd8;
    padding: 8px 14px;
    text-align: left;
    font-size: 12px;
    font-weight: bold;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #1e3060;
    white-space: nowrap;
}
.summary-table th.r { text-align: right; }
.summary-table td {
    padding: 6px 14px;
    border-bottom: 1px solid #182038;
}
.summary-table td.r {
    text-align: right;
    font-family: 'Courier New', Courier, monospace;
    font-size: 13px;
}
.summary-table tbody tr:hover td { background: #1a2d45; }
.sls-name { color: #e0e8f8; font-weight: bold; }
.sls-num  { color: #7788aa; font-size: 11px; }

.col-day,  .col-week  { color: #00c8e0 !important; }
.col-month             { color: #f5a000 !important; }
.col-year              { color: #00cc66 !important; }

.summary-table tfoot td {
    background: #162040;
    font-weight: bold;
    color: #e0e8f8;
    border-top: 2px solid #1e3060;
    padding: 8px 14px;
    font-family: 'Courier New', Courier, monospace;
    font-size: 13px;
}
.summary-table tfoot td.lbl { font-family: Arial, sans-serif; }
.err-bar { background: #5a1010; color: #ffaaaa; padding: 8px 14px; font-size: 12px; }

@media (max-width: 900px) {
    .dash-grid { grid-template-columns: 1fr; }
    .total-amount { font-size: 32px; }
}
</style>
</head>
<body>

<div class="topbar">
    <h1>W/M/Y Shipping Summary Dashboard</h1>
    <div class="right-meta">
        <div class="auto-meta">
            Next refresh in: <span id="countdown"><?php echo $refreshMins; ?>:00</span><br>
            Last refresh: <?php echo htmlspecialchars($refreshTime); ?>
        </div>
        <span class="as-of-badge"><?php echo htmlspecialchars($dateLabel); ?></span>
        <button class="refresh-btn" onclick="location.reload()">&#8635; Refresh Now</button>
    </div>
</div>

<?php foreach ($periods as $key => $p): ?>
    <?php if ($p['err']): ?>
        <div class="err-bar">Query error (<?php echo htmlspecialchars($key); ?>): <?php echo htmlspecialchars($p['err']); ?></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="dash-grid">

    <div class="panel theme-day">
        <div class="panel-header">TODAY'S SHIPPED</div>
        <div class="total-box">
            <div class="total-amount"><?php echo money($periods['day']['total']); ?></div>
            <div class="total-label">DAY SHIPPED TOTAL</div>
        </div>
        <div class="chart-wrap">
            <div class="chart-title">Shipped Today by Sales Person</div>
            <div class="chart-container"><canvas id="chart-day"></canvas></div>
        </div>
    </div>

    <div class="panel theme-week">
        <div class="panel-header">WEEK-TO-DATE SHIPPED</div>
        <div class="total-box">
            <div class="total-amount"><?php echo money($periods['week']['total']); ?></div>
            <div class="total-label">WEEK SHIPPED TOTAL</div>
        </div>
        <div class="chart-wrap">
            <div class="chart-title">Shipped This Week by Sales Person</div>
            <div class="chart-container"><canvas id="chart-week"></canvas></div>
        </div>
    </div>

    <div class="panel theme-month">
        <div class="panel-header">MONTH-TO-DATE SHIPPED</div>
        <div class="total-box">
            <div class="total-amount"><?php echo money($periods['month']['total']); ?></div>
            <div class="total-label">MONTH SHIPPED TOTAL</div>
        </div>
        <div class="chart-wrap">
            <div class="chart-title">Shipped This Month by Sales Person</div>
            <div class="chart-container"><canvas id="chart-month"></canvas></div>
        </div>
    </div>

    <div class="panel theme-year">
        <div class="panel-header">YEAR-TO-DATE SHIPPED</div>
        <div class="total-box">
            <div class="total-amount"><?php echo money($periods['year']['total']); ?></div>
            <div class="total-label">YEAR SHIPPED TOTAL</div>
        </div>
        <div class="chart-wrap">
            <div class="chart-title">Shipped This Year by Sales Person</div>
            <div class="chart-container"><canvas id="chart-year"></canvas></div>
        </div>
    </div>

</div>

<div class="summary-section">
    <div class="summary-header">W/M/Y SHIPPING SUMMARY DETAIL</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Salesperson</th>
                <th>Sls#</th>
                <th class="r col-day">Day Shipped</th>
                <th class="r col-week">Week Shipped</th>
                <th class="r col-month">Month Shipped</th>
                <th class="r col-year">Year Shipped</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($summary as $s): ?>
            <tr>
                <td class="sls-name"><?php echo htmlspecialchars($s['name']); ?></td>
                <td class="sls-num"><?php echo htmlspecialchars($s['slsnum']); ?></td>
                <td class="r col-day"><?php echo money($s['day']); ?></td>
                <td class="r col-week"><?php echo money($s['week']); ?></td>
                <td class="r col-month"><?php echo money($s['month']); ?></td>
                <td class="r col-year"><?php echo money($s['year']); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($summary)): ?>
            <tr>
                <td colspan="6" style="text-align:center;color:#778;padding:20px;">
                    No shipped records found for the current date range.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="lbl" colspan="2">TOTALS</td>
                <td class="r col-day"><?php echo money($periods['day']['total']); ?></td>
                <td class="r col-week"><?php echo money($periods['week']['total']); ?></td>
                <td class="r col-month"><?php echo money($periods['month']['total']); ?></td>
                <td class="r col-year"><?php echo money($periods['year']['total']); ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<script>
(function () {
    var configs = {
        day:   <?php echo jsChartData($periods['day'],   'Day Shipped',   $slsIndex, $palette); ?>,
        week:  <?php echo jsChartData($periods['week'],  'Week Shipped',  $slsIndex, $palette); ?>,
        month: <?php echo jsChartData($periods['month'], 'Month Shipped', $slsIndex, $palette); ?>,
        year:  <?php echo jsChartData($periods['year'],  'Year Shipped',  $slsIndex, $palette); ?>
    };
    for (var id in configs) {
        if (configs.hasOwnProperty(id)) {
            var el = document.getElementById('chart-' + id);
            if (el) new Chart(el, configs[id]);
        }
    }
})();

(function () {
    var secs = <?php echo (int)$refreshMins * 60; ?>;
    var el = document.getElementById('countdown');
    function tick() {
        if (secs <= 0) { location.reload(); return; }
        var m = Math.floor(secs / 60), s = secs % 60;
        el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        secs--;
        setTimeout(tick, 1000);
    }
    tick();
})();
</script>
</body>
</html>
