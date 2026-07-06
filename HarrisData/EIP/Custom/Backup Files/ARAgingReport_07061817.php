<?php
error_reporting(E_ERROR | E_PARSE);
// AR Aging Report. Detail + Excel export + Summary toggle + clickable inv/cust + age-as-of date
// SG5: https://portal.screen-graphics.com:5610/Custom/SG/ARAgingReport.php
// EIP: https://portal.screen-graphics.com:5601/Custom/SG/ARAgingReport.php
//
// Tables: SGHDSDATA.HDINVC (master invoice, IV prefix)
//         SGHDSDATA.HDCUST (customer master, CM prefix)

require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$page_title  = 'AR Aging Report';
$refreshSecs = 600;
$conn        = $i5Connect->getConnection();
$eiBase      = 'https://portal.screen-graphics.com:5601';
$todayY      = date('Y-m-d');

function cymdFmt($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $y = (int)($v / 10000) + 1900;
    $m = (int)(($v % 10000) / 100);
    $d = $v % 100;
    return sprintf('%02d/%02d/%04d', $m, $d, $y);
}
function isoFmt($s) {
    if (!$s || $s === '0001-01-01') return '';
    $dt = DateTime::createFromFormat('Y-m-d', $s);
    return $dt ? $dt->format('m/d/Y') : $s;
}
function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Average Days To Pay: HDCUST.CMT#DY (running total days to pay) / CMT#IV (running total # payments)
// is a HarrisData-maintained per-customer average covering ALL invoices (not just currently-open
// ones), so it is the only accurate source for a true "days to pay" figure from this dataset alone.
// Existence-checked first so an unexpected column name can never break the main query.
$hasAvgPayCols = false;
$ccStmt = db2_exec($conn,
    "SELECT COUNT(*) AS CNT FROM QSYS2.SYSCOLUMNS
     WHERE TABLE_SCHEMA='SGHDSDATA' AND TABLE_NAME='HDCUST'
       AND COLUMN_NAME IN ('CMT#DY','CMT#IV')");
if ($ccStmt) {
    $ccRow = db2_fetch_assoc($ccStmt);
    $hasAvgPayCols = ((int)$ccRow['CNT'] === 2);
    db2_free_stmt($ccStmt);
}
$avgPaySelect = $hasAvgPayCols
    ? "DECIMAL(COALESCE(c.CMT#DY,0),15,2) AS TOTDAYS, DECIMAL(COALESCE(c.CMT#IV,0),15,2) AS TOTPMTS,\n        "
    : "";

// Balance = IVIVAM - IVNPOS. IVDUED is DATE type, returned as YYYY-MM-DD by CHAR(col,ISO).
// Aging buckets computed entirely in JavaScript so "Age As Of" date picker works client-side.
$sql = "
    SELECT
        TRIM(CHAR(h.IVBLTO))                                      AS CUSTNUM,
        COALESCE(TRIM(c.CMCNA1),'')                               AS CUSTNAME,
        TRIM(CHAR(h.IVAINV))                                      AS INVNUM,
        INTEGER(COALESCE(h.IVIVDT,0))                             AS INVDATE,
        TRIM(CHAR(h.IVORD))                                       AS ORDNUM,
        TRIM(COALESCE(h.IVARPO,''))                               AS REFNUM,
        CHAR(h.IVDUED, ISO)                                       AS DUEDATE,
        DECIMAL(COALESCE(h.IVIVAM,0),15,2)                       AS INVAMT,
        DECIMAL(COALESCE(h.IVNPOS,0),15,2)                       AS PAIDAMT,
        DECIMAL(COALESCE(h.IVIVAM,0)-COALESCE(h.IVNPOS,0),15,2) AS BALANCE,
        $avgPaySelect
        1 AS DUMMY
    FROM SGHDSDATA.HDINVC h
    LEFT JOIN SGHDSDATA.HDCUST c ON h.IVBLTO = c.CMCUST
    WHERE (COALESCE(h.IVIVAM,0) - COALESCE(h.IVNPOS,0)) <> 0
    ORDER BY COALESCE(TRIM(c.CMCNA1),''), h.IVBLTO, h.IVAINV
";

$rawRows = []; $sqlErr = ''; $diagCols = [];
$stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) $rawRows[] = $r;
    db2_free_stmt($stmt);
} else {
    $sqlErr = db2_stmt_errormsg();
    $ds = db2_exec($conn,
        "SELECT COLUMN_NAME, DATA_TYPE, LENGTH, NUMERIC_SCALE, COLUMN_TEXT
         FROM QSYS2.SYSCOLUMNS
         WHERE TABLE_NAME='HDINVC' AND TABLE_SCHEMA='SGHDSDATA'
         ORDER BY ORDINAL_POSITION",
        array('cursor' => DB2_SCROLLABLE));
    if ($ds) {
        while ($dc = db2_fetch_assoc($ds)) $diagCols[] = $dc;
        db2_free_stmt($ds);
    }
}

// Build row data. Aging buckets computed client-side from dueDateISO.
// invDateRaw = CYMD integer used in SelectInvoice.d2w URL (e.g. 1250820 = 2025-08-20).
$rows = [];
foreach ($rawRows as $r) {
    $balance = round((float)$r['BALANCE'], 2);
    $dueStr  = trim((string)$r['DUEDATE']);
    $avgPay  = null;
    if ($hasAvgPayCols) {
        $totDays = (float)$r['TOTDAYS'];
        $totPmts = (float)$r['TOTPMTS'];
        if ($totPmts > 0) $avgPay = round($totDays / $totPmts, 1);
    }
    $rows[] = [
        'custNum'    => (int)$r['CUSTNUM'],
        'custName'   => $r['CUSTNAME'],
        'invNum'     => $r['INVNUM'],
        'invDateRaw' => (int)$r['INVDATE'],
        'ordNum'     => $r['ORDNUM'],
        'refNum'     => $r['REFNUM'],
        'invDate'    => cymdFmt((int)$r['INVDATE']),
        'dueDate'    => isoFmt($dueStr),
        'dueDateISO' => ($dueStr && $dueStr !== '0001-01-01') ? $dueStr : '',
        'invAmt'     => round((float)$r['INVAMT'],  2),
        'paidAmt'    => round((float)$r['PAIDAMT'], 2),
        'balance'    => $balance,
        'avgPay'     => $avgPay,
    ];
}

$rowCount = count($rows);
$dataJson = json_encode($rows);

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
.arag-grid { width:100% !important; min-width:100% !important; border-collapse:collapse; font-size:11px; }
.arag-grid thead th { background-color:#374151 !important; color:#fff !important; font-weight:bold !important;
                      padding:4px 6px; white-space:nowrap; position:sticky; top:0; z-index:10;
                      cursor:pointer; user-select:none; border:1px solid #4B5563; }
.arag-grid thead th:hover { background-color:#4B5563 !important; }
.arag-grid thead th.sa::after { content:' \25B2'; font-size:9px; }
.arag-grid thead th.sd::after { content:' \25BC'; font-size:9px; }
.arag-grid tbody td { padding:3px 6px; border-bottom:1px solid #E5E7EB; white-space:nowrap; color:#111827 !important; }
.arag-grid tbody tr:nth-child(odd)  td { background:#F7F7F7; }
.arag-grid tbody tr:nth-child(even) td { background:#FFFFFF; }
.arag-grid tbody tr:hover td { background:#EFF6FF !important; }
.arag-grid tbody td a { color:#2563EB !important; text-decoration:none !important; font-weight:bold !important; }
.arag-grid tbody td a:hover { text-decoration:underline !important; }
.arag-grid tbody td.num { text-align:right; }
.arag-grid tbody td.neg { color:#CC1F20 !important; font-weight:bold !important; }
.arag-grid tr.sub td { background:#DCE8FF !important; font-weight:bold; color:#111827 !important;
                       border-top:1px solid #D1D5DB; border-bottom:2px solid #D1D5DB; }
.arag-grid tr.gt td { background:#374151 !important; color:#fff !important; font-weight:bold; }
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
    AR Aging Report
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
<div style="background:#fff0f0;border:2px solid #c00;padding:10px;margin:8px;
            font-family:monospace;font-size:11px;white-space:pre-wrap;">
<strong>Query Error:</strong> <?= esc($sqlErr) ?>

<?php if ($diagCols): ?>
<strong>HDINVC columns in SGHDSDATA:</strong>
<?php foreach ($diagCols as $c):
    $sc = $c['NUMERIC_SCALE'] !== null ? '.' . $c['NUMERIC_SCALE'] : '';
    $tx = trim((string)$c['COLUMN_TEXT']);
    echo '  ' . esc($c['COLUMN_NAME'])
       . ' ' . esc($c['DATA_TYPE'] . '(' . $c['LENGTH'] . $sc . ')')
       . ($tx ? '  ' . esc($tx) : '') . "\n";
endforeach; ?>
<?php else: ?>
SGHDSDATA.HDINVC not found.
<?php endif; ?>
</div>

<?php else: ?>

<style type="text/css">
.refresh-dot { width:8px; height:8px; border-radius:50%; animation:pulse 2s infinite; flex-shrink:0; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
.refresh-progress { flex:1; max-width:160px; height:4px; background:rgba(255,255,255,0.18); border-radius:2px; overflow:hidden; }
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
      <div class="refresh-dot" id="arag-dot"></div>
      <span id="arag-status">Live &ndash; auto-refreshes every 10 min (M&ndash;F, 7:00am&ndash;6:00pm)</span>
      <div class="refresh-progress"><div class="refresh-fill" id="arag-prog" style="width:100%"></div></div>
      <span>Next refresh in: <strong id="arag-cd">10:00</strong></span>
      <span class="refresh-pill">Last refresh: <strong><?php echo date('g:i:s A'); ?></strong></span>
      <span class="refresh-pill" style="background:#fff3cd;border-color:#f0c060;color:#856404;">As of: <?php echo date('D, M j, Y'); ?></span>
    </div>

    <!-- Filter bar -->
    <div style="display:flex;align-items:center;gap:10px;padding:6px 10px;
                background:#F7F7F7;font-size:12px;flex:1;flex-wrap:wrap;">
      <label style="white-space:nowrap;font-weight:600;">Customer:
        <select id="fCust" style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                                   font-size:12px;margin-left:4px;">
          <option value="">All Customers</option>
        </select>
      </label>

      <label style="white-space:nowrap;font-weight:600;">Bucket:
        <select id="fBkt" style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                                  font-size:12px;margin-left:4px;">
          <option value="">All Buckets</option>
          <option value="c">Current</option>
          <option value="1">1-30 Days</option>
          <option value="2">31-60 Days</option>
          <option value="3">61-90 Days</option>
          <option value="4">Over 90 Days</option>
        </select>
      </label>

      <label style="white-space:nowrap;font-weight:600;">Age As Of:
        <input type="date" id="fDate" value="<?= esc($todayY) ?>"
               style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;font-size:12px;margin-left:4px;">
      </label>

      <label style="white-space:nowrap;font-weight:600;">Search:
        <input type="text" id="fSrch" placeholder="Invoice / Order / PO #"
               style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;font-size:12px;
                      margin-left:4px;width:140px;">
      </label>

      <div style="display:flex;gap:3px;">
        <button class="tb on" id="btnD" onclick="setView('d')"
                style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                       border-radius:3px;background:#fff;">Detail</button>
        <button class="tb" id="btnS" onclick="setView('s')"
                style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                       border-radius:3px;background:#fff;">Summary</button>
      </div>

      <div style="display:flex;gap:3px;">
        <button class="tb on" id="btnCrAll" onclick="setCredit('all')"
                style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                       border-radius:3px;background:#fff;">All Invoices</button>
        <button class="tb" id="btnCrEx" onclick="setCredit('ex')"
                style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                       border-radius:3px;background:#fff;">Exclude Credits</button>
        <button class="tb" id="btnCrOnly" onclick="setCredit('only')"
                style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                       border-radius:3px;background:#fff;">Credits Only</button>
      </div>

      <button id="clearBtn" onclick="clearF()"
              style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                     border-radius:3px;background:#fff;">Clear</button>

      <b id="cnt" style="margin-left:auto;white-space:nowrap;font-size:12px;"></b>
    </div>

  </div>

  <!-- Right: Refresh directly above Export, same column -->
  <div style="display:flex;flex-direction:column;align-items:stretch;justify-content:center;
              gap:4px;padding:6px 10px;background:#F7F7F7;border-left:2px solid #D1D5DB;">
    <button onclick="location.reload();"
            style="font-size:12px;padding:3px 14px;cursor:pointer;border:1px solid #4a0f6e;
                   border-radius:3px;background:#7B1FA2;color:#fff;font-weight:bold;
                   white-space:nowrap;text-align:center;">&#x21BB; Refresh</button>
    <button onclick="exportXLSX();"
            style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
                   font-weight:bold;border:1px solid #14802a;cursor:pointer;white-space:nowrap;
                   text-align:center;display:block;">
      &#8595; Export to Excel
    </button>
  </div>

</div>

<style>
.tb.on { background:#2563EB !important; color:#fff !important; border-color:#1d4ed8 !important; }
</style>

<div style="overflow-x:auto;">

  <!-- DETAIL TABLE -->
  <div id="secD">
  <table id="dtTbl" class="arag-grid">
    <thead><tr id="dtHdr">
      <th data-k="custNum">Cust #</th>
      <th data-k="custName">Customer Name</th>
      <th data-k="invNum">Invoice #</th>
      <th data-k="ordNum">Order #</th>
      <th data-k="refNum">Reference</th>
      <th data-k="invDate">Inv Date</th>
      <th data-k="dueDate">Due Date</th>
      <th data-k="invAmt"  class="num">Invoice Amt</th>
      <th data-k="paidAmt" class="num">Amt Paid</th>
      <th data-k="balance" class="num">Balance</th>
      <th data-k="current" class="num">Current</th>
      <th data-k="b1_30"   class="num">1-30 Days</th>
      <th data-k="b31_60"  class="num">31-60 Days</th>
      <th data-k="b61_90"  class="num">61-90 Days</th>
      <th data-k="b90plus" class="num">Over 90</th>
    </tr></thead>
    <tbody id="dtBody"></tbody>
  </table>
  </div>

  <!-- SUMMARY TABLE -->
  <div id="secS" style="display:none">
  <table id="smTbl" class="arag-grid">
    <thead><tr id="smHdr">
      <th data-k="custNum">Cust #</th>
      <th data-k="custName">Customer Name</th>
      <th data-k="invAmt"  class="num">Invoice Amt</th>
      <th data-k="paidAmt" class="num">Amt Paid</th>
      <th data-k="balance" class="num">Balance</th>
      <th data-k="current" class="num">Current</th>
      <th data-k="b1_30"   class="num">1-30 Days</th>
      <th data-k="b31_60"  class="num">31-60 Days</th>
      <th data-k="b61_90"  class="num">61-90 Days</th>
      <th data-k="b90plus" class="num">Over 90</th>
    </tr></thead>
    <tbody id="smBody"></tbody>
  </table>
  </div>

</div>

<script>
var ALL = <?= $dataJson ?>;
var EIB = <?= json_encode($eiBase) ?>;
var EID = <?= json_encode($eID) ?>;
var VR  = [];   // visible sorted detail rows (indexed by openInv/openCust/openOrd)
var SR  = [];   // visible sorted summary rows (indexed by openCustSm)
var SK  = 'custName', SA = true;
var SSK = 'custName', SSA = true;
var CV  = 'd';
var CRV = 'all';   // credit view: 'all' | 'ex' (exclude credits) | 'only' (credits only)

// Age As Of date - defaults to server today, updated by date picker
var AGO_DATE = (function(){
    var v=document.getElementById('fDate').value;
    var p=v.split('-');
    var d=new Date(+p[0],+p[1]-1,+p[2]);
    d.setHours(0,0,0,0);
    return d;
})();

/* ── helpers ── */
function esc(s){
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;')
                    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmt(n){
    if(n===0||n===null||n===undefined) return '';
    var abs=Math.abs(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
    return n<0?'('+abs+')':abs;
}
function fmtT(n){
    var abs=Math.abs(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
    return n<0?'('+abs+')':abs;
}
function nc(n){ return n<0?'num neg':'num'; }

/* ── bucket calculator (all aging is client-side) ── */
function getBkt(r){
    var bal=r.balance;
    if(!r.dueDateISO){return {c:bal,b1:0,b2:0,b3:0,b4:0};}
    var p=r.dueDateISO.split('-');
    var dd=new Date(+p[0],+p[1]-1,+p[2]);
    var diff=Math.round((AGO_DATE-dd)/86400000);
    if(diff<=0)  return {c:bal,b1:0,  b2:0,  b3:0,  b4:0};
    if(diff<=30) return {c:0,  b1:bal,b2:0,  b3:0,  b4:0};
    if(diff<=60) return {c:0,  b1:0,  b2:bal,b3:0,  b4:0};
    if(diff<=90) return {c:0,  b1:0,  b2:0,  b3:bal,b4:0};
    return              {c:0,  b1:0,  b2:0,  b3:0,  b4:bal};
}

/* ── EIP links ── */
function openInv(idx){
    var r=VR[idx];
    if(!r) return;
    window.open(EIB+'/harris-CGI/SelectInvoice.d2w/DISPLAY'
        +'?formatToPrint=Y&baseVar=BaseConfiguration.icl&portal=CUSTOMER'
        +'&eID='+encodeURIComponent(EID)
        +'&customerNumber='+encodeURIComponent(r.custNum)
        +'&invoiceDate='+encodeURIComponent(r.invDateRaw)
        +'&invoiceNumber='+encodeURIComponent(r.invNum),'_blank');
}
function openOrd(idx){
    var r=VR[idx];
    if(!r||!r.ordNum) return;
    window.open(EIB+'/harris-CGI/SelectOrderHistory.d2w/REPORT'
        +'?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
        +'&eID='+encodeURIComponent(EID)
        +'&customerName='+encodeURIComponent(r.custName)
        +'&customerNumber='+encodeURIComponent(r.custNum)
        +'&orderNumber='+encodeURIComponent(r.ordNum)
        +'&orderSequence=0','_blank');
}
function openCust(idx){
    var r=VR[idx];
    if(!r) return;
    window.open(EIB+'/harris-CGI/CustomerSelect.d2w/REPORT'
        +'?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
        +'&eID='+encodeURIComponent(EID)
        +'&customerNumber='+encodeURIComponent(r.custNum)
        +'&customerName='+encodeURIComponent(r.custName)
        +'&noMenu=&fromSel=Y&quicklinkSelected=commentsummary&quicklinkSelSeq=4','_blank');
}
function openCustSm(idx){
    var r=SR[idx];
    if(!r) return;
    window.open(EIB+'/harris-CGI/CustomerSelect.d2w/REPORT'
        +'?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
        +'&eID='+encodeURIComponent(EID)
        +'&customerNumber='+encodeURIComponent(r.custNum)
        +'&customerName='+encodeURIComponent(r.custName)
        +'&noMenu=&fromSel=Y&quicklinkSelected=commentsummary&quicklinkSelSeq=4','_blank');
}

/* ── populate Customer dropdown ── */
(function(){
    var seen={}, opts=['<option value="">All Customers<\/option>'];
    ALL.forEach(function(r){
        if(!seen[r.custNum]){
            seen[r.custNum]=1;
            opts.push('<option value="'+r.custNum+'">'+r.custNum+' - '+esc(r.custName)+'<\/option>');
        }
    });
    document.getElementById('fCust').innerHTML=opts.join('');
})();

/* ── sort header clicks ── */
function wireHdr(hdrId,getSK,setSK){
    document.getElementById(hdrId).querySelectorAll('th').forEach(function(th){
        th.addEventListener('click',function(){
            var k=th.getAttribute('data-k');
            if(!k) return;
            var numK={invAmt:1,paidAmt:1,balance:1,current:1,
                      b1_30:1,b31_60:1,b61_90:1,b90plus:1,custNum:1};
            var cur=getSK();
            if(cur.k===k) setSK(k,!cur.asc);
            else setSK(k,!numK[k]);
            render();
        });
    });
}
wireHdr('dtHdr',function(){return {k:SK,asc:SA};},function(k,a){SK=k;SA=a;});
wireHdr('smHdr',function(){return {k:SSK,asc:SSA};},function(k,a){SSK=k;SSA=a;});

/* ── filter ── */
function filtered(){
    var fc=document.getElementById('fCust').value;
    var fb=document.getElementById('fBkt').value;
    var fs=document.getElementById('fSrch').value.trim().toLowerCase();
    return ALL.filter(function(r){
        if(fc && String(r.custNum)!==fc) return false;
        if(CRV==='ex'   && r.invAmt<0) return false;
        if(CRV==='only' && r.invAmt>=0) return false;
        if(fb){
            var bk=getBkt(r);
            if(fb==='c' && !bk.c)  return false;
            if(fb==='1' && !bk.b1) return false;
            if(fb==='2' && !bk.b2) return false;
            if(fb==='3' && !bk.b3) return false;
            if(fb==='4' && !bk.b4) return false;
        }
        if(fs){
            var h=(String(r.invNum)+' '+String(r.ordNum)+' '+String(r.refNum)).toLowerCase();
            if(h.indexOf(fs)<0) return false;
        }
        return true;
    });
}

/* ── sort ── */
function sortArr(arr,k,asc){
    return arr.slice().sort(function(a,b){
        var av=a[k],bv=b[k];
        if(typeof av==='string') av=av.toLowerCase();
        if(typeof bv==='string') bv=bv.toLowerCase();
        return av<bv?(asc?-1:1):av>bv?(asc?1:-1):0;
    });
}

/* ── summary rollup (uses getBkt for dynamic aging) ── */
function mkSum(rows){
    var m={},ord=[];
    rows.forEach(function(r){
        var bk=getBkt(r);
        if(!m[r.custNum]){
            m[r.custNum]={custNum:r.custNum,custName:r.custName,
                invAmt:0,paidAmt:0,balance:0,current:0,b1_30:0,b31_60:0,b61_90:0,b90plus:0};
            ord.push(r.custNum);
        }
        var s=m[r.custNum];
        s.invAmt+=r.invAmt; s.paidAmt+=r.paidAmt; s.balance+=r.balance;
        s.current+=bk.c; s.b1_30+=bk.b1; s.b31_60+=bk.b2; s.b61_90+=bk.b3; s.b90plus+=bk.b4;
    });
    return ord.map(function(k){return m[k];});
}

/* ── render detail ── */
function renderDt(rows){
    var h='',prev=null,sub=null;
    var gt={ia:0,pa:0,ba:0,c:0,b1:0,b2:0,b3:0,b4:0};

    function flush(){
        if(!sub) return;
        var avgTxt=(sub.avgPay===null||sub.avgPay===undefined)?'':'Avg Days to Pay: '+sub.avgPay;
        h+='<tr class="sub">'
          +'<td colspan="2">'+esc(sub.nm)+' - Subtotal<\/td>'
          +'<td colspan="4"><\/td>'
          +'<td>'+avgTxt+'<\/td>'
          +'<td class="'+nc(sub.ia)+'">'+fmtT(sub.ia)+'<\/td>'
          +'<td class="'+nc(sub.pa)+'">'+fmtT(sub.pa)+'<\/td>'
          +'<td class="'+nc(sub.ba)+'">'+fmtT(sub.ba)+'<\/td>'
          +'<td class="'+nc(sub.c)+'">'+fmtT(sub.c)+'<\/td>'
          +'<td class="'+nc(sub.b1)+'">'+fmtT(sub.b1)+'<\/td>'
          +'<td class="'+nc(sub.b2)+'">'+fmtT(sub.b2)+'<\/td>'
          +'<td class="'+nc(sub.b3)+'">'+fmtT(sub.b3)+'<\/td>'
          +'<td class="'+nc(sub.b4)+'">'+fmtT(sub.b4)+'<\/td>'
          +'<\/tr>';
    }

    rows.forEach(function(r,idx){
        var bk=getBkt(r);
        if(r.custNum!==prev){
            flush();
            prev=r.custNum;
            sub={nm:r.custName,ia:0,pa:0,ba:0,c:0,b1:0,b2:0,b3:0,b4:0,avgPay:r.avgPay};
        }
        sub.ia+=r.invAmt; sub.pa+=r.paidAmt; sub.ba+=r.balance;
        sub.c+=bk.c; sub.b1+=bk.b1; sub.b2+=bk.b2; sub.b3+=bk.b3; sub.b4+=bk.b4;
        gt.ia+=r.invAmt; gt.pa+=r.paidAmt; gt.ba+=r.balance;
        gt.c+=bk.c; gt.b1+=bk.b1; gt.b2+=bk.b2; gt.b3+=bk.b3; gt.b4+=bk.b4;

        var cl='<a class="lnk" onclick="openCust('+idx+')">'+r.custNum+'<\/a>';
        var il=r.invNum?'<a class="lnk" onclick="openInv('+idx+')">'+esc(r.invNum)+'<\/a>':'';
        var ol=r.ordNum?'<a class="lnk" onclick="openOrd('+idx+')">'+esc(r.ordNum)+'<\/a>':'';

        h+='<tr>'
          +'<td>'+cl+'<\/td>'
          +'<td>'+esc(r.custName)+'<\/td>'
          +'<td>'+il+'<\/td>'
          +'<td>'+ol+'<\/td>'
          +'<td>'+esc(r.refNum)+'<\/td>'
          +'<td>'+esc(r.invDate)+'<\/td>'
          +'<td>'+esc(r.dueDate)+'<\/td>'
          +'<td class="'+nc(r.invAmt)+'">'+fmt(r.invAmt)+'<\/td>'
          +'<td class="'+nc(r.paidAmt)+'">'+fmt(r.paidAmt)+'<\/td>'
          +'<td class="'+nc(r.balance)+'">'+fmt(r.balance)+'<\/td>'
          +'<td class="'+nc(bk.c)+'">'+fmt(bk.c)+'<\/td>'
          +'<td class="'+nc(bk.b1)+'">'+fmt(bk.b1)+'<\/td>'
          +'<td class="'+nc(bk.b2)+'">'+fmt(bk.b2)+'<\/td>'
          +'<td class="'+nc(bk.b3)+'">'+fmt(bk.b3)+'<\/td>'
          +'<td class="'+nc(bk.b4)+'">'+fmt(bk.b4)+'<\/td>'
          +'<\/tr>';
    });
    flush();

    if(rows.length){
        h+='<tr class="gt">'
          +'<td colspan="7">Grand Total<\/td>'
          +'<td class="'+nc(gt.ia)+'">'+fmtT(gt.ia)+'<\/td>'
          +'<td class="'+nc(gt.pa)+'">'+fmtT(gt.pa)+'<\/td>'
          +'<td class="'+nc(gt.ba)+'">'+fmtT(gt.ba)+'<\/td>'
          +'<td class="'+nc(gt.c)+'">'+fmtT(gt.c)+'<\/td>'
          +'<td class="'+nc(gt.b1)+'">'+fmtT(gt.b1)+'<\/td>'
          +'<td class="'+nc(gt.b2)+'">'+fmtT(gt.b2)+'<\/td>'
          +'<td class="'+nc(gt.b3)+'">'+fmtT(gt.b3)+'<\/td>'
          +'<td class="'+nc(gt.b4)+'">'+fmtT(gt.b4)+'<\/td>'
          +'<\/tr>';
    }
    document.getElementById('dtBody').innerHTML=h;
}

/* ── render summary ── */
function renderSm(rows){
    SR=rows;
    var h='';
    var gt={ia:0,pa:0,ba:0,c:0,b1:0,b2:0,b3:0,b4:0};
    rows.forEach(function(r,idx){
        gt.ia+=r.invAmt; gt.pa+=r.paidAmt; gt.ba+=r.balance;
        gt.c+=r.current; gt.b1+=r.b1_30; gt.b2+=r.b31_60; gt.b3+=r.b61_90; gt.b4+=r.b90plus;
        var cl='<a class="lnk" onclick="openCustSm('+idx+')">'+r.custNum+'<\/a>';
        h+='<tr>'
          +'<td>'+cl+'<\/td>'
          +'<td>'+esc(r.custName)+'<\/td>'
          +'<td class="'+nc(r.invAmt)+'">'+fmtT(r.invAmt)+'<\/td>'
          +'<td class="'+nc(r.paidAmt)+'">'+fmtT(r.paidAmt)+'<\/td>'
          +'<td class="'+nc(r.balance)+'">'+fmtT(r.balance)+'<\/td>'
          +'<td class="'+nc(r.current)+'">'+fmtT(r.current)+'<\/td>'
          +'<td class="'+nc(r.b1_30)+'">'+fmtT(r.b1_30)+'<\/td>'
          +'<td class="'+nc(r.b31_60)+'">'+fmtT(r.b31_60)+'<\/td>'
          +'<td class="'+nc(r.b61_90)+'">'+fmtT(r.b61_90)+'<\/td>'
          +'<td class="'+nc(r.b90plus)+'">'+fmtT(r.b90plus)+'<\/td>'
          +'<\/tr>';
    });
    if(rows.length){
        h+='<tr class="gt">'
          +'<td colspan="2">Grand Total<\/td>'
          +'<td class="'+nc(gt.ia)+'">'+fmtT(gt.ia)+'<\/td>'
          +'<td class="'+nc(gt.pa)+'">'+fmtT(gt.pa)+'<\/td>'
          +'<td class="'+nc(gt.ba)+'">'+fmtT(gt.ba)+'<\/td>'
          +'<td class="'+nc(gt.c)+'">'+fmtT(gt.c)+'<\/td>'
          +'<td class="'+nc(gt.b1)+'">'+fmtT(gt.b1)+'<\/td>'
          +'<td class="'+nc(gt.b2)+'">'+fmtT(gt.b2)+'<\/td>'
          +'<td class="'+nc(gt.b3)+'">'+fmtT(gt.b3)+'<\/td>'
          +'<td class="'+nc(gt.b4)+'">'+fmtT(gt.b4)+'<\/td>'
          +'<\/tr>';
    }
    document.getElementById('smBody').innerHTML=h;
}

/* ── main render ── */
function render(){
    var fr=filtered();
    VR=sortArr(fr,SK,SA);
    var sm=sortArr(mkSum(VR),SSK,SSA);
    renderDt(VR);
    renderSm(sm);
    var lbl=CV==='d'
        ? VR.length+' invoice'+(VR.length!==1?'s':'')
        : sm.length+' customer'+(sm.length!==1?'s':'');
    document.getElementById('cnt').textContent=lbl;
    document.querySelectorAll('#dtHdr th,#smHdr th').forEach(function(th){
        th.classList.remove('sa','sd');
    });
    (CV==='d'?document.querySelectorAll('#dtHdr th'):document.querySelectorAll('#smHdr th'))
        .forEach(function(th){
            if(th.getAttribute('data-k')===(CV==='d'?SK:SSK))
                th.classList.add(CV==='d'?(SA?'sa':'sd'):(SSA?'sa':'sd'));
        });
}

/* ── view toggle ── */
function setView(v){
    CV=v;
    document.getElementById('secD').style.display=v==='d'?'':'none';
    document.getElementById('secS').style.display=v==='s'?'':'none';
    document.getElementById('btnD').className='tb'+(v==='d'?' on':'');
    document.getElementById('btnS').className='tb'+(v==='s'?' on':'');
    render();
}

/* ── credit view toggle ── */
function setCredit(v){
    CRV=v;
    document.getElementById('btnCrAll').className='tb'+(v==='all'?' on':'');
    document.getElementById('btnCrEx').className='tb'+(v==='ex'?' on':'');
    document.getElementById('btnCrOnly').className='tb'+(v==='only'?' on':'');
    render();
}

/* ── clear (resets date to today) ── */
function clearF(){
    document.getElementById('fCust').value='';
    document.getElementById('fBkt').value='';
    document.getElementById('fSrch').value='';
    setCredit('all');
    var t=new Date();
    var yy=t.getFullYear();
    var mm=String(t.getMonth()+1).padStart(2,'0');
    var dd=String(t.getDate()).padStart(2,'0');
    document.getElementById('fDate').value=yy+'-'+mm+'-'+dd;
    AGO_DATE=new Date(yy,t.getMonth(),t.getDate());
    render();
}

/* ── XLSX export - pure JS OpenXML + uncompressed ZIP, no libraries ── */
// CRC32 table for ZIP integrity checks
var CRCT=(function(){var t=new Uint32Array(256);for(var i=0;i<256;i++){var c=i;for(var j=0;j<8;j++)c=c&1?0xEDB88320^(c>>>1):c>>>1;t[i]=c;}return t;})();
function crc32b(b){var c=0xFFFFFFFF;for(var i=0;i<b.length;i++)c=CRCT[(c^b[i])&0xFF]^(c>>>8);return(c^0xFFFFFFFF)>>>0;}
function u8s(s){if(typeof TextEncoder!=='undefined')return new TextEncoder().encode(s);var b=[];for(var i=0;i<s.length;i++){var c=s.charCodeAt(i);if(c<0x80)b.push(c);else if(c<0x800)b.push(0xC0|(c>>6),0x80|(c&0x3F));else b.push(0xE0|(c>>12),0x80|((c>>6)&0x3F),0x80|(c&0x3F));}return new Uint8Array(b);}
function zf(nm,ct){var fn=u8s(nm),d=u8s(ct),crc=crc32b(d),sz=d.length;var lh=new Uint8Array(30+fn.length),v=new DataView(lh.buffer);v.setUint32(0,0x04034b50,true);v.setUint16(4,20,true);v.setUint32(14,crc,true);v.setUint32(18,sz,true);v.setUint32(22,sz,true);v.setUint16(26,fn.length,true);lh.set(fn,30);return{lh:lh,d:d,fn:fn,crc:crc,sz:sz};}
function bzip(fs){var es=[],pts=[],off=0;fs.forEach(function(e){e.off=off;off+=e.lh.length+e.sz;pts.push(e.lh,e.d);es.push(e);});var cdOff=off;es.forEach(function(e){var cd=new Uint8Array(46+e.fn.length),v=new DataView(cd.buffer);v.setUint32(0,0x02014b50,true);v.setUint16(4,20,true);v.setUint16(6,20,true);v.setUint32(16,e.crc,true);v.setUint32(20,e.sz,true);v.setUint32(24,e.sz,true);v.setUint16(28,e.fn.length,true);v.setUint32(42,e.off,true);cd.set(e.fn,46);pts.push(cd);off+=cd.length;});var cdSz=off-cdOff,eocd=new Uint8Array(22),v2=new DataView(eocd.buffer);v2.setUint32(0,0x06054b50,true);v2.setUint16(8,es.length,true);v2.setUint16(10,es.length,true);v2.setUint32(12,cdSz,true);v2.setUint32(16,cdOff,true);pts.push(eocd);var tot=pts.reduce(function(s,p){return s+p.length;},0),res=new Uint8Array(tot),pos=0;pts.forEach(function(p){res.set(p,pos);pos+=p.length;});return res;}
function colL(n){var s='';n++;while(n>0){s=String.fromCharCode(65+(n-1)%26)+s;n=Math.floor((n-1)/26);}return s;}
function xe(v){return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

function exportXLSX(){
    var fr=filtered();
    var HDR=['Bill-To Customer','Customer Name','Invoice Number','Order Number',
             'Reference Number','Invoice Date','Due Date','Invoice Amount',
             'Amount Paid','Invoice Balance','Current','1-30 Days','31-60 Days',
             '61-90 Days','Over 90 Days'];
    var NTC=7; // cols 0-6 text, 7-14 numeric

    var STYLES='\x3C?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        +'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        +'<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00_);[Red](#,##0.00)"/></numFmts>'
        +'<fonts count="3">'
        +'<font><sz val="10"/><name val="Calibri"/></font>'
        +'<font><sz val="10"/><name val="Calibri"/><b/><color rgb="FFFFFFFF"/></font>'
        +'<font><sz val="10"/><name val="Calibri"/><b/></font>'
        +'</fonts>'
        +'<fills count="5">'
        +'<fill><patternFill patternType="none"/></fill>'
        +'<fill><patternFill patternType="gray125"/></fill>'
        +'<fill><patternFill patternType="solid"><fgColor rgb="FF374151"/></patternFill></fill>'
        +'<fill><patternFill patternType="solid"><fgColor rgb="FFDCE8FF"/></patternFill></fill>'
        +'<fill><patternFill patternType="solid"><fgColor rgb="FF111827"/></patternFill></fill>'
        +'</fills>'
        +'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
        +'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        +'<cellXfs count="8">'
        +'<xf numFmtId="0"   fontId="0" fillId="0" borderId="0" xfId="0"/>'
        +'<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
        +'<xf numFmtId="0"   fontId="1" fillId="2" borderId="0" xfId="0" applyFill="1" applyFont="1"/>'
        +'<xf numFmtId="164" fontId="1" fillId="2" borderId="0" xfId="0" applyFill="1" applyFont="1" applyNumberFormat="1"/>'
        +'<xf numFmtId="0"   fontId="2" fillId="3" borderId="0" xfId="0" applyFill="1" applyFont="1"/>'
        +'<xf numFmtId="164" fontId="2" fillId="3" borderId="0" xfId="0" applyFill="1" applyFont="1" applyNumberFormat="1"/>'
        +'<xf numFmtId="0"   fontId="1" fillId="4" borderId="0" xfId="0" applyFill="1" applyFont="1"/>'
        +'<xf numFmtId="164" fontId="1" fillId="4" borderId="0" xfId="0" applyFill="1" applyFont="1" applyNumberFormat="1"/>'
        +'</cellXfs>'
        +'</styleSheet>';

    function xcell(ref,val,si){
        if(typeof val==='number')return '<c r="'+ref+'" s="'+si+'"><v>'+val+'</v></c>';
        if(val===''||val===null||val===undefined)return '<c r="'+ref+'" s="'+si+'"/>';
        return '<c r="'+ref+'" t="inlineStr" s="'+si+'"><is><t>'+xe(val)+'</t></is></c>';
    }
    function xrow(rn,vals,ts,ns){
        var r='<row r="'+rn+'">';
        vals.forEach(function(v,ci){r+=xcell(colL(ci)+rn,v,ci<NTC?ts:ns);});
        return r+'</row>';
    }

    var rows=[],rn=1;
    rows.push(xrow(rn++,HDR,2,3));

    var grps={},ord=[];
    fr.forEach(function(r){if(!grps[r.custNum]){grps[r.custNum]=[];ord.push(r.custNum);}grps[r.custNum].push(r);});
    var gt={ia:0,pa:0,ba:0,c:0,b1:0,b2:0,b3:0,b4:0};
    ord.forEach(function(cn){
        var g=grps[cn],sub={ia:0,pa:0,ba:0,c:0,b1:0,b2:0,b3:0,b4:0,avgPay:g[0]?g[0].avgPay:null};
        g.forEach(function(r){
            var bk=getBkt(r);
            rows.push(xrow(rn++,[r.custNum,r.custName,r.invNum,r.ordNum,r.refNum,r.invDate,r.dueDate,
                r.invAmt,r.paidAmt,r.balance,bk.c,bk.b1,bk.b2,bk.b3,bk.b4],0,1));
            sub.ia+=r.invAmt;sub.pa+=r.paidAmt;sub.ba+=r.balance;
            sub.c+=bk.c;sub.b1+=bk.b1;sub.b2+=bk.b2;sub.b3+=bk.b3;sub.b4+=bk.b4;
        });
        var avgTxt=(sub.avgPay===null||sub.avgPay===undefined)?'':'Avg Days to Pay: '+sub.avgPay;
        rows.push(xrow(rn++,['',g[0].custName+' - Subtotal','','','',''  ,avgTxt,
            sub.ia,sub.pa,sub.ba,sub.c,sub.b1,sub.b2,sub.b3,sub.b4],4,5));
        gt.ia+=sub.ia;gt.pa+=sub.pa;gt.ba+=sub.ba;
        gt.c+=sub.c;gt.b1+=sub.b1;gt.b2+=sub.b2;gt.b3+=sub.b3;gt.b4+=sub.b4;
    });
    rows.push(xrow(rn,['','Grand Total','','','','','',
        gt.ia,gt.pa,gt.ba,gt.c,gt.b1,gt.b2,gt.b3,gt.b4],6,7));

    var SHEET='\x3C?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        +'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        +'<sheetData>'+rows.join('')+'</sheetData></worksheet>';

    var CT='\x3C?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
    var RELS='\x3C?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    var WB='\x3C?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="AR Aging" sheetId="1" r:id="rId1"/></sheets></workbook>';
    var WBRELS='\x3C?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';

    var files=[zf('[Content_Types].xml',CT),zf('_rels/.rels',RELS),zf('xl/workbook.xml',WB),
               zf('xl/_rels/workbook.xml.rels',WBRELS),zf('xl/styles.xml',STYLES),
               zf('xl/worksheets/sheet1.xml',SHEET)];
    var blob=new Blob([bzip(files)],{type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
    var url=URL.createObjectURL(blob);
    var a=document.createElement('a');
    a.href=url;
    a.download='ARAgingReport_'+new Date().toISOString().slice(0,10)+'.xlsx';
    document.body.appendChild(a);a.click();document.body.removeChild(a);URL.revokeObjectURL(url);
}

/* ── wire events ── */
document.getElementById('fCust').addEventListener('change',render);
document.getElementById('fBkt').addEventListener('change',render);
document.getElementById('fSrch').addEventListener('input',render);
document.getElementById('fDate').addEventListener('change',function(){
    var val=this.value;
    if(val){
        var p=val.split('-');
        AGO_DATE=new Date(+p[0],+p[1]-1,+p[2]);
    } else {
        AGO_DATE=new Date();
    }
    AGO_DATE.setHours(0,0,0,0);
    render();
});
render();

/* ── auto-refresh bar ── */
(function () {
    var AUTO_SECS = <?php echo (int)$refreshSecs; ?>;
    var countdown = AUTO_SECS;
    var dotEl  = document.getElementById('arag-dot');
    var statEl = document.getElementById('arag-status');
    var cdEl   = document.getElementById('arag-cd');
    var progEl = document.getElementById('arag-prog');
    var tzAbbr = new Date().toLocaleTimeString('en-US', {timeZoneName:'short'}).split(' ').pop();

    function getCtTime() {
        var now = new Date();
        return new Date(now.toLocaleString('en-US', { timeZone: 'America/Chicago' }));
    }
    function inWindow() {
        var ct = getCtTime(), day = ct.getDay(), h = ct.getHours();
        return day >= 1 && day <= 5 && h >= 7 && h < 18;
    }
    function fmtCd(s) {
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
            if (dotEl)  { dotEl.style.background = '#16A34A'; dotEl.style.animation = 'pulse 2s infinite'; }
            if (statEl) statEl.textContent = 'Live – auto-refreshes every 10 min (M–F, 7:00am–6:00pm ' + tzAbbr + ')';
            if (progEl) progEl.style.width = (countdown / AUTO_SECS * 100).toFixed(1) + '%';
            if (cdEl)   cdEl.textContent   = fmtCd(countdown);
        } else {
            if (dotEl)  { dotEl.style.background = '#888'; dotEl.style.animation = 'none'; }
            if (statEl) statEl.textContent = 'Auto-refresh paused – outside M–F 7:00am–6:00pm ' + tzAbbr + '. Use Refresh.';
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
</script>

<?php endif; ?>

</td>
</tr>
</table>

</body>
</html>