<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
date_default_timezone_set('America/Chicago');

$conn = $i5Connect->getConnection();
$now  = new DateTime();

function esc($s)    { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function fmtQty($n) { return number_format((float)$n, 0); }

function runQ($conn, $sql) {
    $rows = array(); $err = '';
    $stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if ($stmt) {
        while ($r = db2_fetch_assoc($stmt)) $rows[] = $r;
        db2_free_stmt($stmt);
    } else {
        $err = db2_stmt_errormsg();
    }
    return array($rows, $err);
}

// ── Q1: Products W/Incorrect Inventory Type Code ─────────────────────────────
$sql1 = "
SELECT TRIM(i.IMITEM) AS IMITEM,
       TRIM(i.IMIMDS) AS IMIMDS,
       TRIM(i.IMPCLS) AS IMPCLS,
       TRIM(i.IMITC)  AS IMITC,
       TRIM(i.IMIMAC) AS IMIMAC,
       TRIM(i.IMTSUS) AS IMTSUS,
       CASE
           WHEN TRIM(i.IMPCLS) = 'OS1' THEN 'Change Inv Type Code to ZOS1'
           WHEN TRIM(i.IMPCLS) = 'OS2' THEN 'Change Inv Type Code to ZOS2'
           WHEN TRIM(i.IMITC)  = ''    THEN 'You must assign an Inventory Type Code.'
           ELSE 'Review Inventory Type Code'
       END AS CORRECTION
FROM SGHDSDATA.HDIMST i
JOIN SGHDSDATA.HDPCLS p ON TRIM(i.IMPCLS) = TRIM(p.PCPCLS)
WHERE TRIM(i.IMITC) NOT IN ('COS', 'FG', 'MC', 'RM', 'SUP', 'ZOS1', 'ZOS2')
   OR (TRIM(i.IMIMAC) = 'I' AND TRIM(i.IMITC) NOT IN ('ZOS1', 'ZOS2'))
   OR (TRIM(p.PCPCLS) LIKE 'OS%' AND TRIM(i.IMITC) NOT IN ('ZOS1', 'ZOS2'))
ORDER BY i.IMITEM ASC
";
list($rows1, $err1) = runQ($conn, $sql1);

// ── Q2: Costing Errors ────────────────────────────────────────────────────────
$sql2 = "
SELECT TRIM(i.IMPCLS)                              AS IMPCLS,
       TRIM(i.IMITEM)                              AS IMITEM,
       TRIM(i.IMIMDS)                              AS IMIMDS,
       COALESCE(TRIM(w.IWWHS), '')                 AS IWWHS,
       COALESCE(CAST(w.IWOHQT AS DECIMAL(11,0)), 0) AS IWOHQT,
       TRIM(p.IPCEFL)                              AS IPCEFL,
       CASE WHEN TRIM(i.IMPCLS) IN ('OS2', 'OS1', 'COS')
                THEN 'Add All 3 Cost Records & Set To Not Roll'
            WHEN TRIM(p.IPCEFL) = 'K' THEN 'Missing A Code In Routing'
            WHEN TRIM(p.IPCEFL) = 'L' THEN 'No Product Structure'
            WHEN TRIM(p.IPCEFL) = 'N' THEN 'No Material Cost For Component(s)'
            WHEN TRIM(p.IPCEFL) = 'P' THEN 'Prior Error In Component/s'
            WHEN TRIM(p.IPCEFL) = 'W' THEN 'Verify All Work Centers Numbers'
            WHEN TRIM(p.IPCEFL) = 'X' THEN 'No Costs Exist For This Item'
            ELSE 'Review COMPLETE Item/Process'
       END AS ERROR_DESC
FROM SGHDSDATA.HDIMST i
LEFT JOIN SGHDSDATA.HDIPLT p ON i.IMITEM = p.IPITEM
LEFT JOIN SGHDSDATA.HDIWHS w ON i.IMITEM = w.IWITEM
WHERE TRIM(i.IMIMAC) <> 'I'
  AND TRIM(p.IPCEFL) <> ''
  AND TRIM(p.IPCSRC) <> 'Y'
ORDER BY p.IPCEFL ASC, i.IMITEM ASC
";
list($rows2, $err2) = runQ($conn, $sql2);

$runAt = $now->format('m/d/Y g:i:s A');

$totalIssues = count($rows1) + count($rows2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventory Data Integrity Dashboard</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Segoe UI', Arial, sans-serif;
  font-size: 12px;
  background: #d4d0c8;
  color: #1a2233;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.topbar {
  background: #003087;
  color: #fff;
  padding: 7px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.topbar h1 { font-size: 15px; font-weight: 700; }
.topbar .meta { font-size: 11px; color: #b8cfee; }

.toolbar {
  background: #ece9d8;
  border-bottom: 2px solid #888;
  padding: 5px 14px;
  display: flex;
  align-items: center;
  gap: 14px;
  flex-shrink: 0;
}
.toolbar .issue-badge {
  font-size: 13px;
  font-weight: 700;
  padding: 3px 12px;
  border-radius: 3px;
}
.badge-ok   { background: #c8e6c9; color: #1b5e20; border: 1px solid #4caf50; }
.badge-warn { background: #fff3e0; color: #bf360c; border: 1px solid #ff9800; }
.btn {
  font-size: 11px;
  padding: 3px 10px;
  border: 1px solid #888;
  border-radius: 2px;
  background: #ece9d8;
  cursor: pointer;
  font-weight: 700;
}
.btn:hover { background: #d4d0c8; }
.run-at { font-size: 11px; color: #5a6478; margin-left: auto; }

.content {
  flex: 1;
  padding: 10px 12px;
  overflow-y: auto;
}

.panel {
  background: #d4d0c8;
  border: 2px solid;
  border-color: #fff #888 #888 #fff;
  box-shadow: 1px 1px 0 #000;
  margin-bottom: 8px;
}
.panel-title {
  background: #000080;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 7px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  user-select: none;
}
.panel-title .pcount {
  background: #ffcc00;
  color: #000;
  font-size: 10px;
  font-weight: 700;
  padding: 0 5px;
  border-radius: 2px;
  margin-left: 8px;
}
.panel-title .pcount.zero {
  background: #44aa44;
  color: #fff;
}
.panel-title .toggle {
  font-size: 10px;
  color: #aaccff;
  margin-left: auto;
  margin-right: 0;
}
.panel-body { padding: 0; }

.ok-msg {
  background: #e8f5e9;
  color: #1b5e20;
  padding: 8px 12px;
  font-size: 11px;
  font-weight: 700;
  border-top: 1px solid #a5d6a7;
}
.err-msg {
  background: #ffcdd2;
  color: #b71c1c;
  padding: 8px 12px;
  font-size: 11px;
  font-family: monospace;
  border-top: 1px solid #ef9a9a;
  word-break: break-all;
}

.tbl-wrap { overflow-x: auto; max-height: 320px; overflow-y: auto; }
table.dtbl {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
  white-space: nowrap;
}
table.dtbl thead th {
  background: #000080;
  color: #fff;
  padding: 3px 8px;
  font-size: 10px;
  font-weight: 700;
  text-align: left;
  border-right: 1px solid #3333aa;
  position: sticky;
  top: 0;
  z-index: 1;
}
table.dtbl thead th.r { text-align: right; }
table.dtbl tbody td {
  padding: 2px 8px;
  border-bottom: 1px solid #c8c4bc;
  text-align: left;
  vertical-align: middle;
}
table.dtbl tbody td.r { text-align: right; }
table.dtbl tbody tr:nth-child(even) td { background: #ece9d8; }
table.dtbl tbody tr:hover td { background: #c8ddf8; }

.tag-warn {
  background: #ff6b35;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  padding: 1px 4px;
  border-radius: 2px;
  margin-left: 4px;
}
.tag-fix {
  background: #1565c0;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  padding: 1px 4px;
  border-radius: 2px;
}
.tag-review {
  background: #e67e00;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  padding: 1px 4px;
  border-radius: 2px;
}

table.dtbl thead th.sortable { cursor: pointer; user-select: none; }
table.dtbl thead th.sortable:hover { background: #1a1a99; }
.sort-ind { margin-left: 3px; color: #aaccff; font-size: 9px; }

.filter-bar {
  background: #ece9d8;
  border-bottom: 1px solid #aaa;
  padding: 4px 8px;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.flt-label { font-size: 11px; font-weight: 700; white-space: nowrap; }
.flt-sel {
  font-size: 11px;
  padding: 1px 4px;
  border: 1px solid #888;
  background: #fff;
  border-radius: 2px;
  max-width: 260px;
}
.flt-clear { margin-left: 4px; }
.btn-export { margin-left: 4px; background: #d4edda; border-color: #5a9e6f; color: #155724; }
.btn-export:hover { background: #b8dac4; }

.scroll-fab {
  position: fixed;
  right: 16px;
  bottom: 44px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  z-index: 50;
}
.fab {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid #001f5c;
  background: #003087;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 1px 1px 3px rgba(0,0,0,0.4);
  opacity: 0.85;
  line-height: 1;
}
.fab:hover { opacity: 1; background: #0048c0; }

.footer {
  border-top: 1px solid #888;
  padding: 3px 14px;
  font-size: 10px;
  color: #555;
  background: #c8c4bc;
  flex-shrink: 0;
  display: flex;
  justify-content: space-between;
}
</style>
</head>
<body>

<div class="topbar">
  <h1>Inventory Data Integrity Dashboard</h1>
  <div class="meta">SG Data Integrity &rsaquo; Inventory Management</div>
</div>

<div class="toolbar">
  <span class="issue-badge <?php echo $totalIssues > 0 ? 'badge-warn' : 'badge-ok'; ?>">
    <?php echo $totalIssues > 0
        ? "&#9888; {$totalIssues} Issue" . ($totalIssues !== 1 ? 's' : '') . ' Found'
        : '&#10003; No Issues Found'; ?>
  </span>
  <button class="btn" onclick="location.reload()">&#8635; Refresh</button>
  <button class="btn" onclick="toggleAll(true)">&#9660; Expand All</button>
  <button class="btn" onclick="toggleAll(false)">&#9650; Collapse All</button>
  <span class="run-at">Run: <?php echo $runAt; ?></span>
</div>

<div class="content">

<?php
function renderSection($id, $title, $rows, $err, $note, $renderFn) {
    $cnt     = count($rows);
    $hasRows = $cnt > 0;
    $pclass  = $cnt === 0 ? 'zero' : '';
    ?>
<div class="panel" id="panel-<?php echo $id; ?>">
  <div class="panel-title" onclick="togglePanel('<?php echo $id; ?>')">
    <span>&#9632; <?php echo htmlspecialchars($title); ?>
      <span class="pcount <?php echo $pclass; ?>"><?php echo $cnt . ' row' . ($cnt !== 1 ? 's' : ''); ?></span>
    </span>
    <span class="toggle" id="tog-<?php echo $id; ?>">&#9650;</span>
  </div>
  <div class="panel-body" id="body-<?php echo $id; ?>">
    <?php if ($err): ?>
    <div class="err-msg">Query error: <?php echo htmlspecialchars($err); ?></div>
    <?php elseif (!$hasRows): ?>
    <div class="ok-msg">&#10003; No issues found.</div>
    <?php else: ?>
    <?php if ($note): ?><div class="panel-note" style="background:#fff3cd;border-bottom:1px solid #f0c040;padding:4px 8px;font-size:11px;color:#856404;font-weight:700;"><?php echo htmlspecialchars($note); ?></div><?php endif; ?>
    <?php $renderFn($rows); ?>
    <?php endif; ?>
  </div>
</div>
    <?php
}
?>

<?php
// ── Section 1: Products W/Incorrect Inventory Type Code ─────────────────────
renderSection('q1', 'Products W/Incorrect Inventory Type Code', $rows1, $err1, '',
function($rows) { ?>
<div class="filter-bar">
  <span class="flt-label">Filter:</span>
  <label class="flt-label">Correction Required</label>
  <select class="flt-sel" data-filter-table="tbl-q1" data-col="6" onchange="applyFilters('tbl-q1')">
    <option value="">(All)</option>
  </select>
  <button class="btn flt-clear" onclick="clearFilters('tbl-q1')">&#10006; Clear</button>
  <button class="btn btn-export" onclick="exportCSV('tbl-q1','InvTypeCode_Export',[5])">&#8595; Export to Excel</button>
</div>
<div class="tbl-wrap">
<table class="dtbl" id="tbl-q1">
  <thead><tr>
    <th class="sortable" onclick="sortTable(this,'str')">Item #<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Description<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Prod Class<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Inv Type Code<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Active<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">TS User<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Correction Required<span class="sort-ind"></span></th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r):
      $corr = $r['CORRECTION'];
      if ($corr === 'Change Inv Type Code to ZOS1' ||
          $corr === 'Change Inv Type Code to ZOS2') {
          $tag = '<span class="tag-fix">' . esc($corr) . '</span>';
      } elseif ($corr === 'You must assign an Inventory Type Code.') {
          $tag = '<span class="tag-warn">' . esc($corr) . '</span>';
      } else {
          $tag = '<span class="tag-review">' . esc($corr) . '</span>';
      }
  ?>
  <tr>
    <td><?php echo esc($r['IMITEM']); ?></td>
    <td><?php echo esc($r['IMIMDS']); ?></td>
    <td><?php echo esc($r['IMPCLS']); ?></td>
    <td><?php echo esc($r['IMITC']); ?></td>
    <td><?php echo esc($r['IMIMAC']); ?></td>
    <td><?php echo esc($r['IMTSUS']); ?></td>
    <td><?php echo $tag; ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div><!-- .tbl-wrap -->
<?php });
?>

<?php
// ── Section 2: Costing Errors ─────────────────────────────────────────────────
renderSection('q2', 'Costing Errors', $rows2, $err2, '',
function($rows) { ?>
<div class="filter-bar">
  <span class="flt-label">Filter:</span>
  <label class="flt-label">Error Description</label>
  <select class="flt-sel" data-filter-table="tbl-q2" data-col="6" onchange="applyFilters('tbl-q2')">
    <option value="">(All)</option>
  </select>
  <label class="flt-label">On Hand Qty</label>
  <select class="flt-sel" data-filter-table="tbl-q2" data-col="4" data-mode="qty" onchange="applyFilters('tbl-q2')">
    <option value="">(All)</option>
    <option value="gt0">Qty &gt; 0</option>
    <option value="le0">Qty &le; 0</option>
  </select>
  <button class="btn flt-clear" onclick="clearFilters('tbl-q2')">&#10006; Clear</button>
  <button class="btn btn-export" onclick="exportCSV('tbl-q2','CostingErrors_Export')">&#8595; Export to Excel</button>
</div>
<div class="tbl-wrap">
<table class="dtbl" id="tbl-q2">
  <thead><tr>
    <th class="sortable" onclick="sortTable(this,'str')">Prod Class<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Item #<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Description<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Whs<span class="sort-ind"></span></th>
    <th class="sortable r" onclick="sortTable(this,'num')">On Hand Qty<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Cost Err<span class="sort-ind"></span></th>
    <th class="sortable" onclick="sortTable(this,'str')">Error Description<span class="sort-ind"></span></th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r):
      $errDesc = $r['ERROR_DESC'];
      if ($errDesc === 'Add All 3 Cost Records & Set To Not Roll') {
          $tag = '<span class="tag-fix">' . esc($errDesc) . '</span>';
      } else {
          $tag = '<span class="tag-warn">' . esc($errDesc) . '</span>';
      }
  ?>
  <tr>
    <td><?php echo esc($r['IMPCLS']); ?></td>
    <td><?php echo esc($r['IMITEM']); ?></td>
    <td><?php echo esc($r['IMIMDS']); ?></td>
    <td><?php echo esc($r['IWWHS']); ?></td>
    <td class="r"><?php echo fmtQty($r['IWOHQT']); ?></td>
    <td><?php echo esc($r['IPCEFL']); ?></td>
    <td><?php echo $tag; ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div><!-- .tbl-wrap -->
<?php });
?>

</div><!-- .content -->

<div class="scroll-fab">
  <button class="fab" title="Go to top" onclick="scrollContent('top')">&#9650;</button>
  <button class="fab" title="Go to bottom" onclick="scrollContent('bottom')">&#9660;</button>
</div>

<div class="footer">
  <span>Source: SGHDSDATA/HDIMST, HDPCLS, HDIPLT, HDIWHS &nbsp;&mdash;&nbsp; Auto-refresh: 30 min</span>
  <span id="footerClock"></span>
</div>

<script>
function scrollContent(where) {
    var c = document.querySelector('.content');
    if (!c) return;
    c.scrollTo({ top: where === 'top' ? 0 : c.scrollHeight, behavior: 'smooth' });
}
function populateFilter(sel, tableId, colIdx) {
    var table = document.getElementById(tableId);
    if (!sel || !table) return;
    var seen = {}, vals = [];
    table.querySelectorAll('tbody tr').forEach(function(r) {
        var v = r.children[colIdx] ? r.children[colIdx].textContent.trim() : '';
        if (!seen[v]) { seen[v] = 1; vals.push(v); }
    });
    vals.sort();
    vals.forEach(function(v) {
        var o = document.createElement('option');
        o.value = v; o.textContent = v;
        sel.appendChild(o);
    });
}
function applyFilters(tableId) {
    var table = document.getElementById(tableId);
    var sels  = Array.prototype.slice.call(
        document.querySelectorAll('.flt-sel[data-filter-table="' + tableId + '"]')
    );
    table.querySelectorAll('tbody tr').forEach(function(r) {
        var show = sels.every(function(s) {
            if (!s.value) return true;
            var idx = parseInt(s.getAttribute('data-col'));
            var v   = r.children[idx] ? r.children[idx].textContent.trim() : '';
            if (s.getAttribute('data-mode') === 'qty') {
                var n = parseFloat(v.replace(/,/g, '')) || 0;
                if (s.value === 'gt0') return n > 0;
                if (s.value === 'le0') return n <= 0;
                return true;
            }
            return v === s.value;
        });
        r.style.display = show ? '' : 'none';
    });
}
function clearFilters(tableId) {
    document.querySelectorAll('.flt-sel[data-filter-table="' + tableId + '"]')
        .forEach(function(s) { s.value = ''; });
    applyFilters(tableId);
}
function exportCSV(tableId, baseName, skipCols) {
    skipCols = skipCols || [];
    var table = document.getElementById(tableId);
    var lines = [];
    var headers = [];
    table.querySelectorAll('thead th').forEach(function(th, i) {
        if (skipCols.indexOf(i) !== -1) return;
        var clone = th.cloneNode(true);
        var ind = clone.querySelector('.sort-ind');
        if (ind) ind.remove();
        headers.push('"' + clone.textContent.trim().replace(/"/g, '""') + '"');
    });
    lines.push(headers.join(','));
    table.querySelectorAll('tbody tr').forEach(function(tr) {
        if (tr.style.display === 'none') return;
        var cells = [];
        tr.querySelectorAll('td').forEach(function(td, i) {
            if (skipCols.indexOf(i) !== -1) return;
            cells.push('"' + td.textContent.trim().replace(/"/g, '""') + '"');
        });
        lines.push(cells.join(','));
    });
    var d = new Date();
    var stamp = d.getFullYear() + ('0'+(d.getMonth()+1)).slice(-2) + ('0'+d.getDate()).slice(-2);
    var blob = new Blob([lines.join('\r\n')], {type: 'text/csv'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = baseName + '_' + stamp + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(a.href);
}

var sortState = {};
function sortTable(th, type) {
    var table = th.closest('table');
    var idx   = Array.prototype.indexOf.call(th.parentNode.children, th);
    var key   = table.id + '_' + idx;
    var asc   = sortState[key] !== true;
    sortState[key] = asc;
    table.querySelectorAll('thead .sort-ind').forEach(function(s) {
        s.textContent = '';
    });
    th.querySelector('.sort-ind').textContent = asc ? ' ▲' : ' ▼';
    var tbody = table.querySelector('tbody');
    var rows  = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    rows.sort(function(a, b) {
        var av = a.children[idx] ? a.children[idx].textContent.trim() : '';
        var bv = b.children[idx] ? b.children[idx].textContent.trim() : '';
        if (type === 'num') {
            av = parseFloat(av.replace(/,/g, '')) || 0;
            bv = parseFloat(bv.replace(/,/g, '')) || 0;
            return asc ? av - bv : bv - av;
        }
        return asc ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    rows.forEach(function(r) { tbody.appendChild(r); });
}

var AUTO_SECS = 1800;
var countdown = AUTO_SECS;

function togglePanel(id) {
    var body = document.getElementById('body-' + id);
    var tog  = document.getElementById('tog-' + id);
    if (body.style.display === 'none') {
        body.style.display = '';
        tog.innerHTML = '&#9650;';
    } else {
        body.style.display = 'none';
        tog.innerHTML = '&#9660;';
    }
}
function toggleAll(expand) {
    ['q1','q2'].forEach(function(id) {
        var body = document.getElementById('body-' + id);
        var tog  = document.getElementById('tog-' + id);
        if (body) {
            body.style.display = expand ? '' : 'none';
            tog.innerHTML = expand ? '&#9650;' : '&#9660;';
        }
    });
}

setInterval(function() {
    countdown--;
    if (countdown <= 0) { location.reload(); }
}, 1000);

var clkEl = document.getElementById('footerClock');
function tick() {
    var d = new Date();
    var h = d.getHours(), m = d.getMinutes(), s = d.getSeconds();
    var ap = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    clkEl.textContent = (h < 10 ? '0'+h : h) + ':' +
        (m < 10 ? '0'+m : m) + ':' + (s < 10 ? '0'+s : s) + ' ' + ap +
        '  —  Refresh in: ' + Math.floor(countdown / 60) + 'm ' + (countdown % 60) + 's';
}
tick();
setInterval(tick, 1000);

// Populate filter dropdowns from live table data
(function() {
    document.querySelectorAll('.flt-sel').forEach(function(sel) {
        if (sel.getAttribute('data-mode') === 'qty') return;
        populateFilter(sel, sel.getAttribute('data-filter-table'),
                       parseInt(sel.getAttribute('data-col')));
    });
})();
</script>
</body>
</html>
