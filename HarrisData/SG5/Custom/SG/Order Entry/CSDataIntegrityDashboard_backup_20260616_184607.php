<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
date_default_timezone_set('America/Chicago');

$conn = $i5Connect->getConnection();
$now  = new DateTime();

function cymdToDate($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $yy = intval($v / 10000);
    $mm = intval(($v % 10000) / 100);
    $dd = $v % 100;
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $yy);
}
function fmtMoney($n) { return '$' . number_format((float)$n, 2); }
function esc($s)      { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

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

function dateToCymd(DateTime $dt) {
    return ((int)$dt->format('Y') - 1900) * 10000
         + (int)$dt->format('m') * 100
         + (int)$dt->format('d');
}

$cutoff     = new DateTime();
$cutoff->modify('-7 days');
$cutoffCymd = dateToCymd($cutoff);

// ── Q1: CO's with Duplicate PO #'s ─────────────────────────────────────────
$sql1 = "
SELECT TRIM(CHAR(h.OEBLTO))       AS OEBLTO,
       TRIM(CHAR(h.OESHTO))       AS OESHTO,
       TRIM(h.OEORRF)             AS OEORRF,
       COUNT(h.\"OEORD#\")         AS ORDCOUNT,
       MAX(h.OEOSTP)              AS MAXOEOSTP,
       TRIM(h.OEUSER)             AS OEUSER
FROM SGHDSDATA.OEORHD h
WHERE h.OEORTY NOT IN ('Q', 'P', 'V')
  AND h.OEBDTE >= $cutoffCymd
  AND h.OEBDTE > 0
  AND h.OEORST = 'O'
  AND TRIM(h.OEORRF) <> 'DAN OLIVE'
  AND TRIM(h.OEORRF) <> ''
GROUP BY h.OEBLTO, h.OESHTO, h.OEORRF, h.OEUSER
HAVING COUNT(h.\"OEORD#\") > 1
ORDER BY h.OEBLTO ASC
";
list($rows1, $err1) = runQ($conn, $sql1);

// ── Q2: Open Order Taxes ────────────────────────────────────────────────────
$sql2 = "
SELECT * FROM (
    SELECT h.\"OEORD#\"                   AS ORDNUM,
           TRIM(CHAR(h.OESHTO))           AS OESHTO,
           COALESCE(TRIM(c.CMCNA1), '')   AS CMCNA1,
           COALESCE(TRIM(c.CMST), '')     AS CMST,
           TRIM(h.OECTXC)                AS OECTXC,
           h.OETIVA,
           h.OETSTX + h.OETCTX + h.OETYTX + h.OETLC1 + h.OETLC2 + h.OETLC3 AS TAXES,
           CASE WHEN (h.OETSTX + h.OETCTX + h.OETYTX + h.OETLC1 + h.OETLC2 + h.OETLC3) = h.OETIVA
                     THEN 'Check This Order'
                WHEN TRIM(h.OECTXC) <> 'T' THEN 'Check For TEC'
                ELSE ' ' END             AS COMMENT
    FROM SGHDSDATA.OEORHD h
    JOIN SGHDSDATA.HDCUST c ON h.OESHTO = c.CMCUST
    WHERE h.OEORST = 'O'
      AND h.OETSTX <> 0
) X
WHERE X.COMMENT IN ('Check This Order', 'Check For TEC')
ORDER BY X.COMMENT DESC, X.TAXES ASC, X.ORDNUM ASC
";
list($rows2, $err2) = runQ($conn, $sql2);

// ── Q3: Open Order Credit Card Fees ─────────────────────────────────────────
$sql3 = "
SELECT TRIM(CHAR(h.OESLSM))              AS OESLSM,
       TRIM(h.OEORTY)                    AS OEORTY,
       'Open'                            AS STATUS,
       h.\"OEORD#\"                       AS ORDNUM,
       TRIM(h.OEORRF)                    AS OEORRF,
       h.OEBLTO,
       COALESCE(TRIM(c.CMCNA1), '')      AS CMCNA1,
       h.OECTRM,
       c.CMCTRM,
       COALESCE(TRIM(t.TMCTDS), '')      AS TMCTDS,
       h.OETIVA,
       h.OETMSG,
       CASE WHEN h.OECTRM = 8  THEN '4%'
            WHEN h.OECTRM = 13 THEN '2%'
            ELSE '0%' END                AS PCT,
       CASE WHEN h.OECTRM = 8  THEN (h.OETIVA - h.OETMSG) * 0.04
            WHEN h.OECTRM = 13 THEN (h.OETIVA - h.OETMSG) * 0.02
            ELSE 0 END                   AS CCFEE,
       h.OEBDTE,
       h.OELDTI,
       h.OEOSTP,
       TRIM(h.OEOSUS)                    AS OEOSUS,
       TRIM(h.OEUSER)                    AS OEUSER
FROM SGHDSDATA.OEORHD h
JOIN SGHDSDATA.HDCUST c ON h.OESHTO = c.CMCUST
JOIN SGHDSDATA.HDTRMS t ON h.OECTRM = t.TMCTRM
WHERE h.OEORST <> 'C'
  AND h.OECTRM IN (8, 13)
  AND h.OETIVA > 0
  AND h.OEORTY <> 'Q'
  AND h.\"OEORD#\" <> 351079
ORDER BY h.OETMSG DESC, h.OEBDTE DESC, h.OELDTI ASC
";
list($rows3, $err3) = runQ($conn, $sql3);

// ── Q4: Customers With Bad Data ──────────────────────────────────────────────
$locCase = "
    CASE WHEN TRIM(h.CMCCLS)='AG' AND h.\"CMLOC#\"=308 THEN ' '
         WHEN TRIM(h.CMCCLS)='BL' AND h.\"CMLOC#\"=309 THEN ' '
         WHEN TRIM(h.CMCCLS)='BS' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='BE' AND h.\"CMLOC#\"=312 THEN ' '
         WHEN TRIM(h.CMCCLS)='CB' AND h.\"CMLOC#\"=312 THEN ' '
         WHEN TRIM(h.CMCCLS)='CO' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='CT' AND h.\"CMLOC#\"=312 THEN ' '
         WHEN TRIM(h.CMCCLS)='CW' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='EC' AND h.\"CMLOC#\"=312 THEN ' '
         WHEN TRIM(h.CMCCLS)='EQ' AND h.\"CMLOC#\"=305 THEN ' '
         WHEN TRIM(h.CMCCLS)='FC' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='GC' AND h.\"CMLOC#\"=312 THEN ' '
         WHEN TRIM(h.CMCCLS)='GF' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='GS' AND h.\"CMLOC#\"=308 THEN ' '
         WHEN TRIM(h.CMCCLS)='GV' AND h.\"CMLOC#\"=312 THEN ' '
         WHEN TRIM(h.CMCCLS)='HF' AND h.\"CMLOC#\"=305 THEN ' '
         WHEN TRIM(h.CMCCLS)='HG' AND h.\"CMLOC#\"=308 THEN ' '
         WHEN TRIM(h.CMCCLS)='IW' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='LB' AND h.\"CMLOC#\"=312 THEN ' '
         WHEN TRIM(h.CMCCLS)='LG' AND h.\"CMLOC#\"=309 THEN ' '
         WHEN TRIM(h.CMCCLS)='PP' AND h.\"CMLOC#\"=308 THEN ' '
         WHEN TRIM(h.CMCCLS)='PR' AND h.\"CMLOC#\"=312 THEN ' '
         WHEN TRIM(h.CMCCLS)='RC' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='RP' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='SF' AND h.\"CMLOC#\"=312 THEN ' '
         WHEN TRIM(h.CMCCLS)='SP' AND h.\"CMLOC#\"=308 THEN ' '
         WHEN TRIM(h.CMCCLS)='SS' AND h.\"CMLOC#\"=308 THEN ' '
         WHEN TRIM(h.CMCCLS)='TC' AND h.\"CMLOC#\"=309 THEN ' '
         WHEN TRIM(h.CMCCLS)='TS' AND h.\"CMLOC#\"=305 THEN ' '
         WHEN TRIM(h.CMCCLS)='US' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='WB' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='WC' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='WM' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='WP' AND h.\"CMLOC#\"=307 THEN ' '
         WHEN TRIM(h.CMCCLS)='XX' AND h.\"CMLOC#\" <> 0 THEN ' '
         ELSE 'Wrong Loc Code' END";

$sql4 = "
SELECT * FROM (
    SELECT COALESCE(TRIM(s.SMSNA1), '')  AS SALESPERSON,
           CASE WHEN TRIM(h.CMORTY) <> '' THEN ' '
                ELSE 'Missing Order Type' END AS DFTORDTYP,
           h.\"CMLOC#\"                  AS LOCNUM,
           $locCase                      AS LOCATION,
           TRIM(h.CMCCLS)               AS CMCCLS,
           h.CMBLTO,
           h.CMCUST,
           COALESCE(TRIM(h.CMCNA1), '') AS CMCNA1,
           COALESCE(TRIM(h.CMCNA2), '') AS CMCNA2,
           COALESCE(TRIM(h.CMCNA3), '') AS CMCNA3,
           COALESCE(TRIM(h.CMCCTY), '') AS CMCCTY,
           COALESCE(TRIM(h.CMST), '')   AS CMST,
           CASE WHEN TRIM(h.CMCTRY) = 'US' AND TRIM(h.CMZIP) <> '' THEN ' '
                WHEN TRIM(h.CMCTRY) <> 'US' THEN ' '
                ELSE 'Missing Zip' END  AS ZIP,
           COALESCE(TRIM(h.CMCTRY), '') AS CMCTRY,
           h.CMDLPO,
           COALESCE(TRIM(h.CMTSUS), '') AS CMTSUS,
           h.CMTSTP
    FROM SGHDSDATA.HDCUST h
    LEFT JOIN SGHDSDATA.HDSLSM s ON h.CMSLSM = s.SMSLSM
    WHERE h.\"CMLOC#\" <> 100
) X
WHERE X.DFTORDTYP = 'Missing Order Type'
   OR X.CMBLTO = 0
   OR X.LOCATION = 'Wrong Loc Code'
   OR X.ZIP = 'Missing Zip'
ORDER BY X.ZIP DESC, X.CMCCLS DESC, X.CMBLTO ASC, X.CMCUST ASC
";
list($rows4, $err4) = runQ($conn, $sql4);

// ── Q5: Order Entry Line Items With No Cost ──────────────────────────────────
$sql5 = "
SELECT TRIM(h.OEORTY)             AS OEORTY,
       d.\"ODORD#\"                AS ORDNUM,
       d.\"ODORL#\"                AS LINENUM,
       TRIM(d.ODPCLS)             AS ODPCLS,
       TRIM(d.ODITEM)             AS ODITEM,
       TRIM(d.ODIMDS)             AS ODIMDS,
       CAST(d.ODQORD AS INTEGER)  AS ODQORD,
       d.ODSLPR,
       d.ODCOST,
       h.OEBDTE,
       DATE(d.ODTSTP)             AS TSTAMP,
       TRIM(d.ODTSUS)             AS ODTSUS
FROM SGHDSDATA.OEORDT d
JOIN SGHDSDATA.OEORHD h ON d.\"ODORD#\" = h.\"OEORD#\"
WHERE h.OEORTY <> 'Q'
  AND d.ODORST = 'O'
  AND d.ODCOST = 0
ORDER BY TSTAMP ASC, d.\"ODORD#\" ASC, d.\"ODORL#\" ASC
";
list($rows5, $err5) = runQ($conn, $sql5);

// ── Q6: QM Product Class On Open Customer Orders ─────────────────────────────
$sql6 = "
SELECT TRIM(h.OEORTY)             AS OEORTY,
       TRIM(d.ODPCLS)             AS ODPCLS,
       CASE WHEN d.ODITEM LIKE 'INST%' THEN 'Product Class is supposed to be INST'
            WHEN d.ODITEM LIKE 'REMO%' THEN 'Product Class is supposed to be INST'
            WHEN d.ODITEM LIKE 'TRAV%' THEN 'Product Class is supposed to be INST'
            WHEN d.ODITEM LIKE 'CUST%' THEN 'Verify Product Class'
            ELSE 'Incorrect Product Class' END AS COMMENT,
       d.\"ODORD#\"                AS ORDNUM,
       d.\"ODORL#\"                AS LINENUM,
       TRIM(d.ODITEM)             AS ODITEM,
       TRIM(d.ODIMDS)             AS ODIMDS,
       CAST(d.ODQORD AS INTEGER)  AS ODQORD,
       CAST(d.ODQSTD AS INTEGER)  AS ODQSTD,
       d.ODSLPR * d.ODQORD        AS TTLSALE,
       TRIM(d.ODOSUS)             AS ODOSUS
FROM SGHDSDATA.OEORDT d
JOIN SGHDSDATA.OEORHD h ON d.\"ODORD#\" = h.\"OEORD#\"
WHERE d.ODORST = 'O'
  AND TRIM(d.ODPCLS) = 'QM'
  AND h.OEORTY <> 'Q'
  AND TRIM(d.ODITEM) <> 'BLANK PO'
ORDER BY d.ODPCLS DESC, COMMENT ASC, d.\"ODORD#\" ASC, d.\"ODORL#\" ASC
";
list($rows6, $err6) = runQ($conn, $sql6);

$runAt = $now->format('m/d/Y g:i:s A');

// ── summary counts ───────────────────────────────────────────────────────────
$totalIssues = count($rows1) + count($rows2) + count($rows3)
             + count($rows4) + count($rows5) + count($rows6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CS Data Integrity Dashboard</title>
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
.badge-err  { background: #ffcdd2; color: #b71c1c; border: 1px solid #f44336; }
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

/* Win95 panel */
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
.panel-note {
  background: #fff3cd;
  border-bottom: 1px solid #f0c040;
  padding: 4px 8px;
  font-size: 11px;
  color: #856404;
  font-weight: 700;
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
.tag-check {
  background: #e67e00;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  padding: 1px 4px;
  border-radius: 2px;
  margin-left: 4px;
}

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
  <h1>CS Data Integrity Dashboard</h1>
  <div class="meta">SG Data Integrity &rsaquo; Order Entry</div>
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
// ── Helper: render a section ─────────────────────────────────────────────────
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
    <?php if ($note): ?><div class="panel-note"><?php echo htmlspecialchars($note); ?></div><?php endif; ?>
    <div class="tbl-wrap"><?php $renderFn($rows); ?></div>
    <?php endif; ?>
  </div>
</div>
    <?php
}
?>

<?php
// ── Section 1: Duplicate PO #'s ──────────────────────────────────────────────
renderSection('q1', "CO's with Duplicate PO #'s (last 7 days, open orders)", $rows1, $err1, '',
function($rows) { ?>
<table class="dtbl">
  <thead><tr>
    <th>Bill-To</th><th>Ship-To</th><th>PO # (OEORRF)</th>
    <th class="r">Order Count</th><th>Latest Rcvd In System</th><th>User</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?php echo esc($r['OEBLTO']); ?></td>
    <td><?php echo esc($r['OESHTO']); ?></td>
    <td><?php echo esc($r['OEORRF']); ?></td>
    <td class="r"><strong><?php echo (int)$r['ORDCOUNT']; ?></strong></td>
    <td><?php echo esc($r['MAXOEOSTP']); ?></td>
    <td><?php echo esc($r['OEUSER']); ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php });
?>

<?php
// ── Section 2: Open Order Taxes ──────────────────────────────────────────────
$note2 = count($rows2) > 0 ? 'Review and check these orders.' : '';
renderSection('q2', 'Open Order Taxes', $rows2, $err2, $note2,
function($rows) { ?>
<table class="dtbl">
  <thead><tr>
    <th>Order #</th><th>Ship-To</th><th>Customer</th><th>St</th>
    <th>Tax Code</th><th class="r">Invoice Amt</th>
    <th class="r">Total Taxes</th><th>Comment</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r):
      $cls = $r['COMMENT'] === 'Check This Order' ? 'tag-warn' : 'tag-check';
  ?>
  <tr>
    <td><?php echo (int)$r['ORDNUM']; ?></td>
    <td><?php echo esc($r['OESHTO']); ?></td>
    <td><?php echo esc($r['CMCNA1']); ?></td>
    <td><?php echo esc($r['CMST']); ?></td>
    <td><?php echo esc($r['OECTXC']); ?></td>
    <td class="r"><?php echo fmtMoney($r['OETIVA']); ?></td>
    <td class="r"><?php echo fmtMoney($r['TAXES']); ?></td>
    <td><span class="<?php echo $cls; ?>"><?php echo esc($r['COMMENT']); ?></span></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php });
?>

<?php
// ── Section 3: Open Order Credit Card Fees ───────────────────────────────────
renderSection('q3', 'Open Order Credit Card Fees', $rows3, $err3, '',
function($rows) { ?>
<table class="dtbl">
  <thead><tr>
    <th>Sls</th><th>Typ</th><th>Order #</th><th>Customer</th><th>PO #</th>
    <th>Bill-To</th><th>Ord Terms</th><th>Cust Terms</th><th>Terms Desc</th>
    <th class="r">Invoice Amt</th><th class="r">CC Fee On Ord</th>
    <th>Fee%</th><th class="r">Expected Fee</th>
    <th>Ord Date</th><th>Inv Date</th><th>Orig User</th><th>Last User</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?php echo esc($r['OESLSM']); ?></td>
    <td><?php echo esc($r['OEORTY']); ?></td>
    <td><?php echo (int)$r['ORDNUM']; ?></td>
    <td><?php echo esc($r['CMCNA1']); ?></td>
    <td><?php echo esc($r['OEORRF']); ?></td>
    <td><?php echo esc($r['OEBLTO']); ?></td>
    <td><?php echo esc($r['OECTRM']); ?></td>
    <td><?php echo esc($r['CMCTRM']); ?></td>
    <td><?php echo esc($r['TMCTDS']); ?></td>
    <td class="r"><?php echo fmtMoney($r['OETIVA']); ?></td>
    <td class="r"><?php echo fmtMoney($r['OETMSG']); ?></td>
    <td><?php echo esc($r['PCT']); ?></td>
    <td class="r"><?php echo fmtMoney($r['CCFEE']); ?></td>
    <td><?php echo cymdToDate($r['OEBDTE']); ?></td>
    <td><?php echo cymdToDate($r['OELDTI']); ?></td>
    <td><?php echo esc($r['OEOSUS']); ?></td>
    <td><?php echo esc($r['OEUSER']); ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php });
?>

<?php
// ── Section 4: Customers With Bad Data ──────────────────────────────────────
renderSection('q4', 'Customers With Bad Data', $rows4, $err4, '',
function($rows) { ?>
<table class="dtbl">
  <thead><tr>
    <th>Salesperson</th><th>Dflt Ord Typ</th><th>Loc#</th><th>Location</th>
    <th>Cls</th><th>Bill-To</th><th>Cust#</th><th>Name</th>
    <th>City</th><th>St</th><th>Zip</th><th>Country</th>
    <th>Last Purchase</th><th>TS User</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r):
      $issues = array();
      if ($r['DFTORDTYP'] === 'Missing Order Type') $issues[] = 'Missing Order Type';
      if ((int)$r['CMBLTO'] === 0)                  $issues[] = 'No Bill-To';
      if ($r['LOCATION'] === 'Wrong Loc Code')       $issues[] = 'Wrong Loc';
      if ($r['ZIP'] === 'Missing Zip')               $issues[] = 'Missing Zip';
  ?>
  <tr>
    <td><?php echo esc($r['SALESPERSON']); ?></td>
    <td><?php foreach ($issues as $iss): ?><span class="tag-warn"><?php echo esc($iss); ?></span> <?php endforeach; ?></td>
    <td><?php echo esc($r['LOCNUM']); ?></td>
    <td><?php echo $r['LOCATION'] === 'Wrong Loc Code' ? '<span class="tag-warn">Wrong Loc Code</span>' : '&nbsp;'; ?></td>
    <td><?php echo esc($r['CMCCLS']); ?></td>
    <td><?php echo esc($r['CMBLTO']); ?></td>
    <td><?php echo esc($r['CMCUST']); ?></td>
    <td><?php
        $name = trim($r['CMCNA1']);
        if (trim($r['CMCNA2']) !== '') $name .= ' / ' . trim($r['CMCNA2']);
        echo esc($name);
    ?></td>
    <td><?php echo esc($r['CMCCTY']); ?></td>
    <td><?php echo esc($r['CMST']); ?></td>
    <td><?php echo $r['ZIP'] === 'Missing Zip' ? '<span class="tag-warn">Missing Zip</span>' : '&nbsp;'; ?></td>
    <td><?php echo esc($r['CMCTRY']); ?></td>
    <td><?php echo cymdToDate($r['CMDLPO']); ?></td>
    <td><?php echo esc($r['CMTSUS']); ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php });
?>

<?php
// ── Section 5: Line Items With No Cost ───────────────────────────────────────
renderSection('q5', 'Order Entry Line Items With No Cost (open lines)', $rows5, $err5,
    'When these items become real item numbers, notify the user to change the product class on the item.',
function($rows) { ?>
<table class="dtbl">
  <thead><tr>
    <th>Typ</th><th>Order #</th><th>Ln</th><th>Cls</th>
    <th>Item</th><th>Description</th><th class="r">Qty Ord</th>
    <th class="r">Sell Price</th><th class="r">Cost</th>
    <th>Ord Date</th><th>Last Changed</th><th>TS User</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?php echo esc($r['OEORTY']); ?></td>
    <td><?php echo (int)$r['ORDNUM']; ?></td>
    <td><?php echo (int)$r['LINENUM']; ?></td>
    <td><?php echo esc($r['ODPCLS']); ?></td>
    <td><?php echo esc($r['ODITEM']); ?></td>
    <td><?php echo esc($r['ODIMDS']); ?></td>
    <td class="r"><?php echo number_format((int)$r['ODQORD']); ?></td>
    <td class="r"><?php echo fmtMoney($r['ODSLPR']); ?></td>
    <td class="r"><span class="tag-warn">$0.00</span></td>
    <td><?php echo cymdToDate($r['OEBDTE']); ?></td>
    <td><?php echo esc($r['TSTAMP']); ?></td>
    <td><?php echo esc($r['ODTSUS']); ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php });
?>

<?php
// ── Section 6: QM Product Class ──────────────────────────────────────────────
renderSection('q6', 'QM Product Class On Open Customer Orders', $rows6, $err6, '',
function($rows) { ?>
<table class="dtbl">
  <thead><tr>
    <th>Typ</th><th>Cls</th><th>Comment</th>
    <th>Order #</th><th>Ln</th><th>Item</th><th>Description</th>
    <th class="r">Qty Ord</th><th class="r">Qty Shp</th>
    <th class="r">Total Sale</th><th>Orig User</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?php echo esc($r['OEORTY']); ?></td>
    <td><?php echo esc($r['ODPCLS']); ?></td>
    <td><span class="tag-check"><?php echo esc($r['COMMENT']); ?></span></td>
    <td><?php echo (int)$r['ORDNUM']; ?></td>
    <td><?php echo (int)$r['LINENUM']; ?></td>
    <td><?php echo esc($r['ODITEM']); ?></td>
    <td><?php echo esc($r['ODIMDS']); ?></td>
    <td class="r"><?php echo number_format((int)$r['ODQORD']); ?></td>
    <td class="r"><?php echo number_format((int)$r['ODQSTD']); ?></td>
    <td class="r"><?php echo fmtMoney($r['TTLSALE']); ?></td>
    <td><?php echo esc($r['ODOSUS']); ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php });
?>

</div><!-- .content -->

<div class="footer">
  <span>Source: SGHDSDATA/OEORHD, OEORDT, HDCUST, HDSLSM, HDTRMS &nbsp;&mdash;&nbsp; Auto-refresh: 30 min</span>
  <span id="footerClock"></span>
</div>

<script>
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
    ['q1','q2','q3','q4','q5','q6'].forEach(function(id) {
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
</script>
</body>
</html>
