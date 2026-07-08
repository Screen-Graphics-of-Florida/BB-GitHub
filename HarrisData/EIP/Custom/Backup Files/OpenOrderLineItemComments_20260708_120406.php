<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
date_default_timezone_set('America/Chicago');

function oolc_cymdToDate($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    $mm = intval(($v % 10000)   / 100);
    $dd = $v % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return '';
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $c * 100 + $yy);
}

$conn    = $i5Connect->getConnection();
$dbError = '';

$sql = "
    SELECT
        cm.\"OCORD#\"        AS ORDNUM,
        cm.\"OCORL#\"        AS ORDLINE,
        cm.OCCMNT            AS CMT,
        cm.OCCSEQ            AS CMTSEQ,
        cm.OCDOCT            AS DOCTYPE,
        h.OEBDTE             AS ORDDATE,
        h.OERQDT             AS RQDDATE,
        h.OESHTO             AS SHIPTO,
        h.\"OESEQ#\"         AS ORDSEQ,
        dt.ODITEM            AS ITEMNUM,
        dt.ODIMDS            AS ITEMDESC,
        dt.ODQORD            AS QTYORD,
        TRIM(cust.CMCNA1)    AS CUSTNAME,
        mo.OHORD             AS MONUM,
        mo.OHPN              AS MOPN
    FROM SGHDSDATA.OEOCMT cm
    JOIN  SGHDSDATA.OEORHD h    ON cm.\"OCORD#\" = h.\"OEORD#\"
    JOIN  SGHDSDATA.OEORDT dt   ON cm.\"OCORD#\" = dt.\"ODORD#\"
                               AND cm.\"OCORL#\" = dt.\"ODORL#\"
    LEFT JOIN SGHDSDATA.HDCUST cust ON h.OESHTO = cust.CMCUST
    LEFT JOIN SGHDSDATA.HDMOHM mo   ON cm.\"OCORD#\" = mo.\"OHORD#\"
                                    AND cm.\"OCORL#\" = mo.\"OHORL#\"
    WHERE h.OEORTY <> 'Q'
      AND h.OEORST = 'O'
      AND cm.\"OCORL#\" <> 999
      AND cm.OCDOCT = 'ACK'
      AND dt.ODORST = 'O'
    ORDER BY h.OEBDTE ASC, cm.\"OCORD#\" ASC, cm.\"OCORL#\" ASC
";

$rows = array();
$stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $rows[] = array(
            'ordDate'  => oolc_cymdToDate($r['ORDDATE']),
            'ordNum'   => (int)$r['ORDNUM'],
            'ordLine'  => (int)$r['ORDLINE'],
            'rqdDate'  => oolc_cymdToDate($r['RQDDATE']),
            'itemNum'  => trim((string)$r['ITEMNUM']),
            'itemDesc' => trim((string)$r['ITEMDESC']),
            'qtyOrd'   => (int)$r['QTYORD'],
            'custName' => trim((string)$r['CUSTNAME']),
            'shipTo'   => trim((string)$r['SHIPTO']),
            'comment'        => trim((string)$r['CMT']),
            'hasSeeAttached' => (stripos(trim((string)$r['CMT']), 'see attached') !== false),
            'cmtSeq'         => (int)$r['CMTSEQ'],
            'docType'  => trim((string)$r['DOCTYPE']),
            'ordSeq'   => (int)$r['ORDSEQ'],
            'moNum'    => trim((string)$r['MONUM']),
            'moPn'     => trim((string)$r['MOPN']),
        );
    }
    db2_free_stmt($stmt);
} else {
    $dbError = db2_stmt_errormsg();
}

$dataJson    = json_encode($rows);
$now         = new DateTime();
$refreshedAt = $now->format('g:i:s A');
$rowCount    = count($rows);
$eiBase      = 'https://portal.screen-graphics.com:5601';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Open Order Line Item Comments</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&display=swap');

  :root {
    --hd-blue: #003087;
    --hd-nav-bg: #0046a8;
    --hd-nav-width: 180px;
    --hd-border: #d0d7e2;
    --hd-text: #1a2233;
    --hd-muted: #5a6478;
    --bg: #d4d0c8;
    --panel-bg: #ffffff;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Roboto Condensed', Arial, sans-serif; font-size: 13px;
         color: var(--hd-text); background: #edf1f7;
         display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

  .topbar { background: var(--hd-blue); color: white; display: flex; align-items: center;
            justify-content: space-between; padding: 0 16px; height: 42px; flex-shrink: 0; }
  .topbar-logo { font-size: 15px; font-weight: 700; letter-spacing: 0.5px; }
  .topbar-logo span { color: #6db3ff; }
  .topbar-right { display: flex; align-items: center; gap: 20px; font-size: 12px; color: #b8cfee; }
  .topbar-right a { color: #b8cfee; text-decoration: none; }

  .layout { display: flex; flex: 1; overflow: hidden; }

  .nav { width: var(--hd-nav-width); background: var(--hd-nav-bg); flex-shrink: 0; overflow-y: auto; }
  .nav-section { padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
  .nav-item { display: block; padding: 6px 14px; color: #cde0ff; font-size: 12px;
              text-decoration: none; cursor: pointer; white-space: nowrap; }
  .nav-item:hover { background: rgba(255,255,255,0.12); color: white; }
  .nav-item.active { background: rgba(255,255,255,0.2); color: white; font-weight: 700;
                     border-left: 3px solid #6db3ff; padding-left: 11px; }
  .nav-header { padding: 8px 14px 4px; color: #89afd4; font-size: 10px; font-weight: 700;
                letter-spacing: 1px; text-transform: uppercase; }
  .nw-badge { display: inline-block; background: rgba(109,179,255,0.25); color: #9ecfff;
              font-size: 9px; padding: 1px 4px; border-radius: 2px; margin-left: 4px; }

  .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

  .page-header { background: linear-gradient(to bottom, #ece9d8, #d4d0c8);
                 border-bottom: 2px solid #888; padding: 6px 12px;
                 display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
  .page-title { font-size: 14px; font-weight: 700; color: #003; }
  .page-meta  { font-size: 11px; color: var(--hd-muted); }
  .header-right { display: flex; align-items: center; gap: 8px; }

  .refresh-bar { background: #e8f0fb; border-bottom: 1px solid #bdd0ee; padding: 4px 14px;
                 display: flex; align-items: center; gap: 14px; font-size: 11px;
                 color: var(--hd-muted); flex-shrink: 0; flex-wrap: wrap; }
  .refresh-dot { width: 8px; height: 8px; border-radius: 50%; background: #1a7a3c;
                 animation: pulse 2s infinite; flex-shrink: 0; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
  .refresh-progress { flex: 1; max-width: 160px; height: 4px; background: #d0dced;
                      border-radius: 2px; overflow: hidden; }
  .refresh-fill { height: 100%; background: #0055b3; border-radius: 2px; transition: width 1s linear; }
  .stat-pill { background: white; border: 1px solid var(--hd-border); border-radius: 12px;
               padding: 2px 10px; font-size: 11px; font-weight: 600; white-space: nowrap; }
  .btn { font-size: 12px; padding: 3px 10px; border-radius: 3px; cursor: pointer; border: 1px solid;
         display: inline-flex; align-items: center; gap: 4px; background: white;
         color: var(--hd-text); border-color: var(--hd-border); white-space: nowrap; }
  .btn:hover { background: #f0f4fa; }

  .report-body { flex: 1; overflow: auto; padding: 10px 12px 12px;
                 background: linear-gradient(160deg, #dbd8cc 0%, #c8c4bc 100%); }

  .panel { background: var(--panel-bg); border: 2px solid;
           border-color: #fff #888 #888 #fff; box-shadow: 1px 1px 0 #000; }
  .panel-title { background: #000080; color: white; font-size: 11px; font-weight: 700;
                 padding: 2px 6px; letter-spacing: 0.5px; }

  .tbl-wrap { overflow-x: auto; }
  table.grid { width: 100%; border-collapse: collapse; font-size: 11px;
               font-family: 'Roboto Condensed', Arial, sans-serif; white-space: nowrap; }
  table.grid thead th {
    background: #000080; color: white; padding: 4px 8px;
    font-size: 10px; font-weight: 700; border-right: 1px solid #3333aa;
    text-align: left; position: sticky; top: 0; z-index: 1;
    cursor: pointer; user-select: none;
  }
  table.grid thead th:hover { background: #1111aa; }
  table.grid thead th.num { text-align: right; }
  table.grid thead th .sort-ind { font-size: 9px; margin-left: 3px; opacity: 0.6; }
  table.grid thead th.sorted    { background: #1a1a99; }
  table.grid thead th.sorted .sort-ind { opacity: 1; color: #ffff88; }
  table.grid tbody tr { border-bottom: 1px solid #ddd; }
  table.grid tbody tr:nth-child(even) { background: #f0f0f0; }
  table.grid tbody tr:hover { background: #ddeeff; }
  table.grid tbody td { padding: 3px 8px; color: #111; text-align: left; }
  table.grid tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
  table.grid tfoot td { background: #ddd; font-weight: 700; padding: 3px 8px;
                        border-top: 2px solid #888; font-size: 10px; }
  table.grid tbody td a { color: #3366cc; text-decoration: underline; cursor: pointer; }
  table.grid tbody td a:hover { color: #0033aa; }
  table.grid tbody td a.attach-link { color: #b35c00; font-weight: 600; }
  table.grid tbody td a.attach-link:hover { color: #7a3e00; }

  .page-footer { background: linear-gradient(to bottom, #d4d0c8, #c8c4bc);
                 border-top: 1px solid #999; padding: 3px 14px;
                 display: flex; align-items: center; justify-content: space-between;
                 font-size: 10px; color: #555; flex-shrink: 0; }
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-logo">Harris<span>Data</span> EIP</div>
  <div class="topbar-right">
    <a href="#">My Portal</a>
    <a href="#">Help</a>
    <span>sgadmin</span>
    <a href="#">Logout</a>
  </div>
</div>

<div class="layout">
  <nav class="nav">
    <div class="nav-section">
      <div class="nav-header">My Portal</div>
      <a class="nav-item" href="#">Event Calendar</a>
    </div>
    <div class="nav-section">
      <div class="nav-header">SG Reports</div>
      <a class="nav-item active" href="#">OE Line Item Comments <span class="nw-badge">&#8599;</span></a>
    </div>
  </nav>

  <main class="main">
    <div class="page-header">
      <div>
        <div class="page-title">Open Order Line Item Comments</div>
        <div class="page-meta">SG Reports &rsaquo; Planning &nbsp;&bull;&nbsp; Opens in new window</div>
      </div>
      <div class="header-right">
        <button class="btn" onclick="sortByOrdNumLineCmt()" title="Sort by Order # then Line # then Comment Seq#">&#8597; Ord# / Line# / Cmt Seq#</button>
        <button class="btn" onclick="exportExcel()">&#8595; Export to Excel</button>
        <button class="btn" onclick="triggerRefresh()">&#8635; Refresh Now</button>
      </div>
    </div>

    <div class="refresh-bar">
      <div class="refresh-dot" id="rDot"></div>
      <span id="rStatus">Live</span>
      <div class="refresh-progress"><div class="refresh-fill" id="rFill" style="width:100%"></div></div>
      <span>Next: <strong id="rCount">15m 0s</strong></span>
      <span class="stat-pill" id="rTime">Last refresh: just now</span>
      <span class="stat-pill" id="rowPill"><?php echo number_format($rowCount); ?> rows</span>
    </div>

<?php if ($dbError): ?>
    <div style="background:#5a1010;color:#ffaaaa;padding:7px 14px;font-size:12px;font-family:monospace;">
      Query error: <?php echo htmlspecialchars($dbError); ?>
    </div>
<?php endif; ?>

    <div class="report-body">
      <div class="panel">
        <div class="panel-title">&#9632; OPEN ORDER LINE ITEM COMMENTS &mdash; <span id="panelCount"><?php echo number_format($rowCount); ?> rows</span></div>
        <div class="tbl-wrap">
          <table class="grid" id="mainTable">
            <thead>
              <tr id="hdrRow"></tr>
            </thead>
            <tbody id="gridBody"></tbody>
            <tfoot id="gridFoot"></tfoot>
          </table>
        </div>
      </div>
    </div>

    <div class="page-footer">
      <span>Source: SGHDSDATA &mdash; OEOCMT, OEORHD, OEORDT, HDCUST, HDMOHM</span>
      <span id="footerTime"></span>
    </div>
  </main>
</div>

<script>
const DATA   = <?php echo $dataJson; ?>;
var EI_BASE   = <?php echo json_encode($eiBase); ?>;
var EI_EID    = <?php echo json_encode($eID); ?>;
var OOLC_ROWS = [];

const COLS = [
  { key:'ordDate',  label:'Ord Date',    num:false },
  { key:'ordNum',   label:'Order #',     num:true  },
  { key:'ordLine',  label:'Line #',      num:true  },
  { key:'rqdDate',  label:'Req Date',    num:false },
  { key:'itemNum',  label:'Item #',      num:false },
  { key:'itemDesc', label:'Description', num:false },
  { key:'qtyOrd',   label:'Qty Ord',     num:true  },
  { key:'custName', label:'Customer',    num:false },
  { key:'shipTo',   label:'Ship-To',     num:false },
  { key:'comment',  label:'Comment',     num:false },
  { key:'cmtSeq',   label:'Cmt Seq#',   num:true  },
  { key:'moNum',    label:'MO #',        num:false },
];

let sortCol = 'ordNum';
let sortDir = 'asc';

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function buildHeader() {
  const tr = document.getElementById('hdrRow');
  tr.innerHTML = '';
  COLS.forEach(c => {
    const th = document.createElement('th');
    if (c.num) th.className = 'num';
    if (c.key === sortCol) th.classList.add('sorted');
    const ind = (c.key === sortCol) ? (sortDir === 'asc' ? ' ▲' : ' ▼') : ' ⇅';
    th.innerHTML = esc(c.label) + '<span class="sort-ind">' + ind + '</span>';
    th.title = 'Sort by ' + c.label;
    th.addEventListener('click', () => onSort(c.key));
    tr.appendChild(th);
  });
}

function onSort(col) {
  if (sortCol === col) {
    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
  } else {
    sortCol = col;
    sortDir = 'asc';
  }
  buildGrid();
}

function sortByOrdNumLineCmt() {
  sortCol = 'ordNum';
  sortDir = 'asc';
  buildGrid();
}

function getSortedData() {
  const d = DATA.slice();
  const dir = sortDir === 'asc' ? 1 : -1;
  d.sort((a, b) => {
    let av = a[sortCol], bv = b[sortCol];
    if (typeof av === 'string') av = av.toLowerCase();
    if (typeof bv === 'string') bv = bv.toLowerCase();
    const cmp = (av < bv ? -1 : av > bv ? 1 : 0) * dir;
    if (cmp !== 0) return cmp;
    var nc = a.ordNum - b.ordNum;
    if (nc !== 0) return nc;
    var lc = a.ordLine - b.ordLine;
    if (lc !== 0) return lc;
    return a.cmtSeq - b.cmtSeq;
  });
  return d;
}

function buildGrid() {
  buildHeader();
  OOLC_ROWS = getSortedData();
  const tbody  = document.getElementById('gridBody');
  tbody.innerHTML = '';
  OOLC_ROWS.forEach((r, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML =
      '<td>'           + esc(r.ordDate)  + '</td>' +
      '<td class="num"><a href="#" onclick="openOrder(' + idx + ');return false;">' + r.ordNum + '</a></td>' +
      '<td class="num">'+ r.ordLine       + '</td>' +
      '<td>'           + esc(r.rqdDate)  + '</td>' +
      '<td>' + (r.itemNum ? '<a href="#" onclick="openItem(' + idx + ');return false;">' + esc(r.itemNum) + '</a>' : '') + '</td>' +
      '<td>'           + esc(r.itemDesc) + '</td>' +
      '<td class="num">'+ r.qtyOrd        + '</td>' +
      '<td>'           + esc(r.custName) + '</td>' +
      '<td>' + (r.shipTo ? '<a href="#" onclick="openCustomer(' + idx + ');return false;">' + esc(r.shipTo) + '</a>' : '') + '</td>' +
      '<td>' + (r.hasSeeAttached ? '<a href="#" class="attach-link" onclick="openAttachment(' + idx + ');return false;">&#128206; ' + esc(r.comment) + '</a>' : '<a href="#" onclick="openComment(' + idx + ');return false;">' + esc(r.comment) + '</a>') + '</td>' +
      '<td class="num">'+ r.cmtSeq        + '</td>' +
      '<td>' + (r.moNum ? '<a href="#" onclick="openMO(' + idx + ');return false;">' + esc(r.moNum) + '</a>' : '') + '</td>';
    tbody.appendChild(tr);
  });
  document.getElementById('gridFoot').innerHTML =
    '<tr><td colspan="12" style="text-align:right;padding-right:12px;">' +
    OOLC_ROWS.length.toLocaleString() + ' rows</td></tr>';
  document.getElementById('panelCount').textContent = OOLC_ROWS.length.toLocaleString() + ' rows';
  document.getElementById('rowPill').textContent    = OOLC_ROWS.length.toLocaleString() + ' rows';
}

// ─── EIP LINKS ────────────────────────────────────────────────────────────────
function openOrder(idx) {
  var r = OOLC_ROWS[idx];
  var url = EI_BASE + '/harris-CGI/SelectOrder.d2w/REPORT' +
    '?baseVar=BaseConfiguration.icl' +
    '&portal=CUSTOMER' +
    '&eID=' + EI_EID +
    '&customerName=' + encodeURIComponent(r.custName) +
    '&customerNumber=' + encodeURIComponent(r.shipTo) +
    '&orderNumber=' + encodeURIComponent(r.ordNum);
  window.open(url, '_blank');
}

function openMO(idx) {
  var r = OOLC_ROWS[idx];
  if (!r.moNum) return;
  var url = EI_BASE + '/harris-CGI/SelectMfgOrder.d2w/REPORT' +
    '?baseVar=BaseConfiguration.icl' +
    '&portal=MFGMGMT' +
    '&eID=' + EI_EID +
    '&mfgOrder=' + encodeURIComponent(r.moNum) +
    '&plantNumber=1';
  window.open(url, '_blank');
}

function openItem(idx) {
  var r = OOLC_ROWS[idx];
  if (!r.itemNum) return;
  var url = EI_BASE + '/harris-CGI/ItemSelect.d2w/REPORT' +
    '?baseVar=BaseConfiguration.icl' +
    '&portal=ITEM' +
    '&eID=' + EI_EID +
    '&itemNumber=' + encodeURIComponent(r.itemNum) +
    '&itemDescription=' + encodeURIComponent(r.itemDesc);
  window.open(url, '_blank');
}

function openCustomer(idx) {
  var r = OOLC_ROWS[idx];
  if (!r.shipTo) return;
  var url = EI_BASE + '/harris-CGI/CustomerSelect.d2w/REPORT' +
    '?baseVar=BaseConfiguration.icl' +
    '&portal=CUSTOMER' +
    '&eID=' + EI_EID +
    '&customerName=' + encodeURIComponent(r.custName) +
    '&customerNumber=' + encodeURIComponent(r.shipTo);
  window.open(url, '_blank');
}

function openComment(idx) {
  var r = OOLC_ROWS[idx];
  var url = EI_BASE + '/harris-CGI/SelectOrderComments.d2w/REPORT' +
    '?baseVar=BaseConfiguration.icl' +
    '&portal=CUSTOMER' +
    '&eID=' + EI_EID +
    '&orderNumber=' + encodeURIComponent(r.ordNum) +
    '&lineNumber=' + encodeURIComponent(r.ordLine) +
    '&batchNumber=&turnaround=';
  window.open(url, '_blank');
}

function openAttachment(idx) {
  var r = OOLC_ROWS[idx];
  window.open('OpenAttachment.php?ordNum=' + r.ordNum + '&eID=' + encodeURIComponent(EI_EID), '_blank');
}

// ─── AUTO-REFRESH ─────────────────────────────────────────────────────────────
const AUTO_SECS = 900;
let countdown = AUTO_SECS;

function isAutoRefreshTime() {
  const now   = new Date();
  const ctStr = now.toLocaleString('en-US', { timeZone: 'America/Chicago' });
  const ct    = new Date(ctStr);
  const day   = ct.getDay(); // 0=Sun, 6=Sat
  const h     = ct.getHours();
  return day >= 1 && day <= 5 && h >= 7 && h < 16;
}

function fmtCountdown(secs) {
  const tot = Math.max(0, secs);
  const d = Math.floor(tot / 86400);
  const h = Math.floor((tot % 86400) / 3600);
  const m = Math.floor((tot % 3600) / 60);
  const s = tot % 60;
  const mm = String(m).padStart(2, '0');
  const ss = String(s).padStart(2, '0');
  if (d > 0) return d + (d === 1 ? ' day ' : ' days ') + String(h).padStart(2, '0') + ':' + mm + ':' + ss;
  if (h > 0) return h + ':' + mm + ':' + ss;
  return m + ':' + ss;
}

function updateStatusBar() {
  const auto  = isAutoRefreshTime();
  const dot   = document.getElementById('rDot');
  const stat  = document.getElementById('rStatus');
  const fill  = document.getElementById('rFill');
  const count = document.getElementById('rCount');
  if (auto) {
    dot.style.background = '#1a7a3c';
    dot.style.animation  = 'pulse 2s infinite';
    stat.textContent     = 'Live — auto-refreshes every 15 min (M–F, 7:00am–4:00pm CT)';
    fill.style.width     = ((countdown / AUTO_SECS) * 100) + '%';
    count.textContent    = fmtCountdown(countdown);
  } else {
    dot.style.background = '#888';
    dot.style.animation  = 'none';
    stat.textContent     = 'Auto-refresh paused — outside M–F 7:00am–4:00pm CT. Use Refresh Now.';
    fill.style.width     = '0%';
    count.textContent    = '—';
    countdown = AUTO_SECS;
  }
}

function triggerRefresh() {
  document.getElementById('rDot').style.background = '#f39c12';
  document.getElementById('rStatus').textContent   = 'Refreshing…';
  setTimeout(function() { location.reload(); }, 300);
}

setInterval(function() {
  if (isAutoRefreshTime()) {
    countdown--;
    if (countdown <= 0) { countdown = AUTO_SECS; triggerRefresh(); return; }
  }
  updateStatusBar();
}, 1000);

// ─── EXPORT ───────────────────────────────────────────────────────────────────
function exportExcel() {
  const headers = COLS.map(c => c.label);
  const sorted  = getSortedData();
  const csvRows = [headers];
  sorted.forEach(r => csvRows.push([
    r.ordDate, r.ordNum, r.ordLine, r.rqdDate, r.itemNum, r.itemDesc,
    r.qtyOrd, r.custName, r.shipTo, r.comment, r.cmtSeq, r.docType,
    r.ordSeq, r.moNum, r.moPn
  ]));
  const csv = csvRows.map(row =>
    row.map(v => '"' + String(v === null || v === undefined ? '' : v).replace(/"/g, '""') + '"').join(',')
  ).join('\r\n');
  const a = document.createElement('a');
  a.href     = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
  a.download = 'OpenOrderLineItemComments_<?php echo $now->format('Ymd'); ?>.csv';
  a.click();
}

// ─── INIT ─────────────────────────────────────────────────────────────────────
document.getElementById('rTime').textContent      = 'Last refresh: <?php echo $refreshedAt; ?>';
document.getElementById('footerTime').textContent = '<?php echo $now->format('m/d/Y') . '  ' . $refreshedAt; ?>';
updateStatusBar();
buildGrid();
</script>
</body>
</html>
