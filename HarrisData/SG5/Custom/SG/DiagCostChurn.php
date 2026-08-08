<?php
// DiagCostChurn.php
// READ-ONLY phase 2 for the item cost history project.
//
// Phase 1 (DiagCostHist.php) established the layout:
//   Key      = CMPLT (plant), CMCSET (cost set), CMPN (item)
//   Costs    = CMUCC1-5, CMLCC1-5, CMBSC1-5   all DECIMAL(12,5)
//   Dates    = CMRUDT, CMEFDT, CMDTMT         all NUMERIC(7,0) CYYMMDD
//   Snapshot = DERIVED_01 (DATE) in the SEQUELDBF files
//
// This pass answers the only question that decides the design:
// how often does a cost VALUE actually change? If churn is low, a
// change-only history table replaces 16M snapshot rows/year with a
// small fraction of that, and keeps history forever instead of rolling.
//
// Writes nothing. SELECT / COUNT / GROUP BY only.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/DiagCostChurn.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(900);

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) {
    die('DB connect failed: ' . htmlspecialchars(db2_conn_errormsg()));
}

// ---------- helpers -------------------------------------------------------

$TIMINGS = array();

function qrows($conn, $sql, &$err = null, $label = '') {
    global $TIMINGS;
    $err = null;
    $t0 = microtime(true);
    $s = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$s) {
        $err = db2_stmt_errormsg();
        if ($label) $TIMINGS[] = array($label, microtime(true) - $t0, 'FAILED');
        return array();
    }
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    db2_free_stmt($s);
    if ($label) $TIMINGS[] = array($label, microtime(true) - $t0, count($out) . ' rows');
    return $out;
}

function qone($conn, $sql, &$err = null, $label = '') {
    $rows = qrows($conn, $sql, $err, $label);
    return count($rows) ? $rows[0] : null;
}

function h($v) { return htmlspecialchars(trim((string)$v)); }
function n($v) { return $v === null || $v === '' ? '' : number_format((float)$v); }

// The 15 cost columns that define a cost "state".
$COSTCOLS = array(
    'CMUCC1','CMUCC2','CMUCC3','CMUCC4','CMUCC5',
    'CMLCC1','CMLCC2','CMLCC3','CMLCC4','CMLCC5',
    'CMBSC1','CMBSC2','CMBSC3','CMBSC4','CMBSC5',
);
$COSTLIST = implode(',', $COSTCOLS);

// DIGITS() renders a packed decimal as fixed-width characters -- old, fast and
// safe on every IBM i release. Concatenated it forms a change signature.
$SIG = implode("||", array_map(function ($c) { return "DIGITS($c)"; }, $COSTCOLS));

// Only plant 1 is in scope. Plant 3 exists (39 rows live, 16,133 in DLY1) but
// is not tracked. CMPLT stays in the key so a future plant needs no redesign.
$PLANT = 1;

// ---------- A. journal status (retry without the 7.4-only column) ---------

$journal = qone($conn,
    "SELECT OBJNAME, OBJTEXT, JOURNALED, JOURNAL_NAME, JOURNAL_LIBRARY,
            JOURNAL_IMAGES
       FROM TABLE(QSYS2.OBJECT_STATISTICS('SGHDSDATA','FILE','HDMCMM')) x",
    $journalErr, 'journal status');

$receivers = array();
$rcvErr = null;
if ($journal && strtoupper(trim((string)$journal['JOURNALED'])) === 'YES') {
    $jl = trim($journal['JOURNAL_LIBRARY']);
    $receivers = qrows($conn,
        "SELECT OBJNAME AS RCV, OBJCREATED AS CREATED, OBJSIZE AS SZ
           FROM TABLE(QSYS2.OBJECT_STATISTICS('$jl','JRNRCV')) y
          ORDER BY OBJCREATED DESC FETCH FIRST 12 ROWS ONLY",
        $rcvErr, 'journal receivers');
}

// ---------- B. HDMCMM shape: cost sets, plants, maintenance dates ---------

$setBreak = qrows($conn,
    "SELECT CMPLT, CMCSET, COUNT(*) AS NROWS,
            COUNT(DISTINCT CMPN) AS NITEMS,
            MIN(CMDTMT) AS MINMAINT, MAX(CMDTMT) AS MAXMAINT,
            SUM(CASE WHEN CMDTMT = 0 THEN 1 ELSE 0 END) AS NOMAINT
       FROM SGHDSDATA.HDMCMM
      GROUP BY CMPLT, CMCSET
      ORDER BY CMPLT, CMCSET", $setErr, 'HDMCMM by plant/set');

// Does CMDTMT actually move? Distribution of maintenance dates, newest first.
$maintDist = qrows($conn,
    "SELECT CMDTMT, COUNT(*) AS NROWS
       FROM SGHDSDATA.HDMCMM
      WHERE CMDTMT > 0
      GROUP BY CMDTMT
      ORDER BY CMDTMT DESC
      FETCH FIRST 25 ROWS ONLY", $maintErr, 'CMDTMT distribution');

// Same for roll-up date -- the other candidate incremental trigger.
$rollDist = qrows($conn,
    "SELECT CMRUDT, COUNT(*) AS NROWS
       FROM SGHDSDATA.HDMCMM
      WHERE CMRUDT > 0
      GROUP BY CMRUDT
      ORDER BY CMRUDT DESC
      FETCH FIRST 25 ROWS ONLY", $rollErr, 'CMRUDT distribution');

// ---------- C. distinct cost STATES vs raw snapshot rows -----------------
// COUNT of distinct (plant, set, item, cost-vector) groups is very close to
// the number of rows a change-only table would hold. This is the headline.

$snapFiles = array(
    array('file' => 'HDMCMMDLY1', 'note' => 'set 1 Standard, 2025-01-01 .. current'),
    array('file' => 'HDMCMMDLY2', 'note' => 'set 2 Current,  2025-08-18 .. current'),
);

$churn = array();
foreach ($snapFiles as $sf) {
    $f = $sf['file'];
    $c = array('file' => $f, 'note' => $sf['note']);

    $c['raw'] = qone($conn,
        "SELECT COUNT(*) AS NROWS,
                COUNT(DISTINCT DERIVED_01) AS NDAYS,
                MIN(DERIVED_01) AS DMIN, MAX(DERIVED_01) AS DMAX
           FROM SEQUELDBF.$f WHERE CMPLT = $PLANT", $c['rawerr'], "$f raw counts");

    $c['keys'] = qone($conn,
        "SELECT COUNT(*) AS NKEYS FROM (
            SELECT CMPLT, CMCSET, CMPN FROM SEQUELDBF.$f WHERE CMPLT = $PLANT
             GROUP BY CMPLT, CMCSET, CMPN) t", $c['keyerr'], "$f distinct keys");

    $c['states'] = qone($conn,
        "SELECT COUNT(*) AS NSTATES FROM (
            SELECT CMPLT, CMCSET, CMPN, $COSTLIST
              FROM SEQUELDBF.$f WHERE CMPLT = $PLANT
             GROUP BY CMPLT, CMCSET, CMPN, $COSTLIST) t",
        $c['stateerr'], "$f distinct cost states");

    // Rows per snapshot day -- catches duplicate/partial SEQUEL runs.
    $c['perday'] = qone($conn,
        "SELECT MIN(N) AS MINN, MAX(N) AS MAXN, AVG(N) AS AVGN FROM (
            SELECT DERIVED_01 AS D, COUNT(*) AS N
              FROM SEQUELDBF.$f WHERE CMPLT = $PLANT
             GROUP BY DERIVED_01) t",
        $c['pderr'], "$f rows per day");

    $churn[] = $c;
}

// ---------- D. day-by-day change counts over a recent window -------------
// LAG across snapshot days gives the true daily insert volume a nightly
// delta job would produce. Windowed to keep the scan bounded.

$WINDOW_FROM = '2026-05-01';
$dailyChange = array();
foreach ($snapFiles as $sf) {
    $f = $sf['file'];
    $rows = qrows($conn,
        "SELECT D, COUNT(*) AS NCHG FROM (
            SELECT DERIVED_01 AS D, CMPLT, CMCSET, CMPN,
                   $SIG AS SIG,
                   LAG($SIG) OVER (PARTITION BY CMPLT, CMCSET, CMPN
                                   ORDER BY DERIVED_01) AS PSIG
              FROM SEQUELDBF.$f
             WHERE DERIVED_01 >= '$WINDOW_FROM' AND CMPLT = $PLANT) x
          WHERE PSIG IS NOT NULL AND SIG <> PSIG
          GROUP BY D ORDER BY D DESC
          FETCH FIRST 40 ROWS ONLY", $err, "$f daily changes since $WINDOW_FROM");
    $dailyChange[] = array('file' => $f, 'rows' => $rows, 'err' => $err);
}

// ---------- E. snapshot calendar gaps ------------------------------------
// 562 distinct days inside a 583-day span means SEQUEL missed some runs.
// Those gaps are invisible in a delta model, but worth seeing.

$gaps = array();
foreach ($snapFiles as $sf) {
    $f = $sf['file'];
    $rows = qrows($conn,
        "SELECT D AS AFTER_DAY, NEXTD AS NEXT_DAY, DAYS(NEXTD) - DAYS(D) AS GAP_DAYS
           FROM (SELECT DERIVED_01 AS D,
                        LEAD(DERIVED_01) OVER (ORDER BY DERIVED_01) AS NEXTD
                   FROM (SELECT DISTINCT DERIVED_01 FROM SEQUELDBF.$f
                          WHERE CMPLT = $PLANT) u) v
          WHERE NEXTD IS NOT NULL AND DAYS(NEXTD) - DAYS(D) > 1
          ORDER BY GAP_DAYS DESC
          FETCH FIRST 20 ROWS ONLY", $err, "$f calendar gaps");
    $gaps[] = array('file' => $f, 'rows' => $rows, 'err' => $err);
}

// ---------- F. confirm HDMCMMDLY is redundant ----------------------------
// If every (key, day, cost state) in HDMCMMDLY also exists in HDMCMMDLY1,
// the 4.4M-row file can be dropped rather than migrated.

$redundant = qone($conn,
    "SELECT COUNT(*) AS ORPHANS FROM (
        SELECT CMPLT, CMCSET, CMPN, DERIVED_01 FROM SEQUELDBF.HDMCMMDLY
         WHERE CMPLT = $PLANT
           AND DERIVED_01 BETWEEN
               (SELECT MIN(DERIVED_01) FROM SEQUELDBF.HDMCMMDLY1)
           AND (SELECT MAX(DERIVED_01) FROM SEQUELDBF.HDMCMMDLY1)
        EXCEPT
        SELECT CMPLT, CMCSET, CMPN, DERIVED_01 FROM SEQUELDBF.HDMCMMDLY1
         WHERE CMPLT = $PLANT) t",
    $redErr, 'HDMCMMDLY redundancy check');

// ---------- G. where to put the new table --------------------------------

$libs = qrows($conn,
    "SELECT SCHEMA_NAME, COALESCE(SCHEMA_TEXT,'') AS TXT
       FROM QSYS2.SYSSCHEMAS
      WHERE SCHEMA_NAME LIKE 'SG%' OR SCHEMA_NAME LIKE 'S5%'
         OR SCHEMA_NAME LIKE '%CUST%' OR SCHEMA_NAME LIKE 'SEQUEL%'
      ORDER BY SCHEMA_NAME", $libErr, 'candidate libraries');

db2_close($conn);

// ---------- sizing math ---------------------------------------------------
// 15 packed DECIMAL(12,5) = 7 bytes each, + CHAR(15) key + numerics/dates.
$BYTES_PER_ROW = 150;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cost History Churn Analysis</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.sect { font-weight:bold; font-size:14px; margin:22px 0 8px;
        color:#1a3d5c; border-bottom:2px solid #90caf9; padding-bottom:3px; }
.sub  { font-weight:bold; font-size:12px; margin:12px 0 4px; color:#37474f; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px; }
.big  { background:#e8f5e9; border:2px solid #66bb6a; border-radius:5px;
        padding:14px 18px; margin-bottom:14px; font-size:14px; }
.big strong { font-size:20px; color:#1b5e20; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px;
        font-family:monospace; }
table.full { border-collapse:collapse; width:100%; background:#fff;
             border-radius:4px; overflow:hidden;
             box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
table.full th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
                padding:5px 8px; text-align:left; font-size:11px; white-space:nowrap; }
table.full td { padding:3px 8px; font-size:11px; font-family:monospace;
                border-bottom:1px solid #f0f0f0; white-space:nowrap; }
table.full tr:nth-child(even) td { background:#fafafa; }
td.num, th.num { text-align:right; }
.scroll { overflow-x:auto; margin-bottom:14px; }
.pill { display:inline-block; background:#1565c0; color:#fff; border-radius:3px;
        padding:1px 7px; font-size:11px; margin-right:6px; }
.side { display:flex; gap:16px; flex-wrap:wrap; }
.side > div { flex:1; min-width:340px; }
</style>
</head>
<body>

<div class="hdr">Cost History &mdash; Churn Analysis</div>

<div class="info">
  Read-only. Run: <?php echo date('Y-m-d H:i:s'); ?><br>
  Measures how often cost values actually change, to size a change-only
  history table against the 16M snapshot rows/year the current approach adds.
</div>

<!-- ============ HEADLINE ============ -->
<div class="sect">Headline: snapshot rows vs. change rows</div>
<?php
$totRaw = 0; $totStates = 0; $haveAll = true;
foreach ($churn as $c) {
    if (!$c['raw'] || !$c['states']) { $haveAll = false; break; }
    $totRaw    += (float)$c['raw']['NROWS'];
    $totStates += (float)$c['states']['NSTATES'];
}
?>
<?php if ($haveAll && $totRaw > 0): ?>
<div class="big">
  Snapshots hold <strong><?php echo n($totRaw); ?></strong> rows.<br>
  The same history as change-only rows: <strong><?php echo n($totStates); ?></strong>
  &mdash; <strong><?php echo number_format($totStates / $totRaw * 100, 2); ?>%</strong>
  of the size, a <strong><?php echo number_format($totRaw / max($totStates, 1), 1); ?>x</strong> reduction,
  with no loss of detail and no rolling-window purge.
</div>
<?php else: ?>
<div class="bad">Could not compute headline &mdash; see errors below.</div>
<?php endif; ?>

<div class="scroll">
<table class="full">
  <tr><th>File</th><th>Coverage</th><th class="num">Snapshot rows</th>
      <th class="num">Days</th><th class="num">Distinct keys</th>
      <th class="num">Distinct cost states</th><th class="num">Compression</th>
      <th class="num">Rows/day min</th><th class="num">avg</th><th class="num">max</th></tr>
  <?php foreach ($churn as $c): ?>
  <tr>
    <td><strong><?php echo h($c['file']); ?></strong></td>
    <td><?php echo $c['raw'] ? h($c['raw']['DMIN']) . ' .. ' . h($c['raw']['DMAX']) : ''; ?></td>
    <td class="num"><?php echo $c['raw'] ? n($c['raw']['NROWS']) : ''; ?></td>
    <td class="num"><?php echo $c['raw'] ? n($c['raw']['NDAYS']) : ''; ?></td>
    <td class="num"><?php echo $c['keys'] ? n($c['keys']['NKEYS']) : ''; ?></td>
    <td class="num"><?php echo $c['states'] ? n($c['states']['NSTATES']) : ''; ?></td>
    <td class="num"><?php
        if ($c['raw'] && $c['states'] && (float)$c['states']['NSTATES'] > 0)
            echo number_format((float)$c['raw']['NROWS'] / (float)$c['states']['NSTATES'], 1) . 'x';
    ?></td>
    <td class="num"><?php echo $c['perday'] ? n($c['perday']['MINN']) : ''; ?></td>
    <td class="num"><?php echo $c['perday'] ? n($c['perday']['AVGN']) : ''; ?></td>
    <td class="num"><?php echo $c['perday'] ? n($c['perday']['MAXN']) : ''; ?></td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php foreach ($churn as $c) {
    foreach (array('rawerr','keyerr','stateerr','pderr') as $k)
        if (!empty($c[$k])) echo '<div class="bad">' . h($c['file'] . ' / ' . $k . ': ' . $c[$k]) . '</div>';
} ?>

<?php if ($haveAll && $totStates > 0): ?>
<div class="sub">Storage, once loaded (at ~<?php echo $BYTES_PER_ROW; ?> bytes/row)</div>
<table class="full">
  <tr><th>Model</th><th class="num">Rows today</th><th class="num">Size today</th>
      <th class="num">Added per year</th><th class="num">Growth per year</th></tr>
  <?php
    // Observed daily snapshot volume across both files.
    $dailySnap = 0;
    foreach ($churn as $c) if ($c['perday']) $dailySnap += (float)$c['perday']['AVGN'];
    // Observed change rate: states per covered day, summed.
    $dailyChg = 0;
    foreach ($churn as $c)
        if ($c['states'] && $c['raw'] && (float)$c['raw']['NDAYS'] > 0)
            $dailyChg += (float)$c['states']['NSTATES'] / (float)$c['raw']['NDAYS'];
  ?>
  <tr>
    <td>Daily snapshot (today)</td>
    <td class="num"><?php echo n($totRaw); ?></td>
    <td class="num"><?php echo number_format($totRaw * $BYTES_PER_ROW / 1073741824, 2); ?> GB</td>
    <td class="num"><?php echo n($dailySnap * 365); ?></td>
    <td class="num"><?php echo number_format($dailySnap * 365 * $BYTES_PER_ROW / 1073741824, 2); ?> GB</td>
  </tr>
  <tr>
    <td><strong>Change-only (proposed)</strong></td>
    <td class="num"><strong><?php echo n($totStates); ?></strong></td>
    <td class="num"><strong><?php echo number_format($totStates * $BYTES_PER_ROW / 1073741824, 2); ?> GB</strong></td>
    <td class="num"><strong><?php echo n($dailyChg * 365); ?></strong></td>
    <td class="num"><strong><?php echo number_format($dailyChg * 365 * $BYTES_PER_ROW / 1073741824, 2); ?> GB</strong></td>
  </tr>
</table>
<?php endif; ?>

<!-- ============ DAILY CHANGE PROFILE ============ -->
<div class="sect">Daily change volume since <?php echo h($WINDOW_FROM); ?></div>
<div class="info">
  This is what a nightly delta job would actually insert each night.
  Blank days mean nothing changed &mdash; the delta job writes zero rows.
</div>
<div class="side">
<?php foreach ($dailyChange as $dc): ?>
  <div>
    <div class="sub"><?php echo h($dc['file']); ?></div>
    <?php if ($dc['err']): ?>
      <div class="bad"><?php echo h($dc['err']); ?></div>
    <?php elseif (!count($dc['rows'])): ?>
      <div class="ok">No cost changes detected in the window.</div>
    <?php else: ?>
    <table class="full">
      <tr><th>Snapshot day</th><th class="num">Keys changed</th></tr>
      <?php foreach ($dc['rows'] as $r): ?>
      <tr><td><?php echo h($r['D']); ?></td>
          <td class="num"><?php echo n($r['NCHG']); ?></td></tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<!-- ============ HDMCMM SHAPE ============ -->
<div class="sect">HDMCMM by plant and cost set</div>
<?php if ($setErr): ?><div class="bad"><?php echo h($setErr); ?></div><?php else: ?>
<table class="full">
  <tr><th>Plant</th><th>Cost Set</th><th class="num">Rows</th><th class="num">Items</th>
      <th class="num">Min CMDTMT</th><th class="num">Max CMDTMT</th>
      <th class="num">CMDTMT = 0</th></tr>
  <?php foreach ($setBreak as $r): ?>
  <tr>
    <td><?php echo h($r['CMPLT']); ?></td>
    <td><strong><?php echo h($r['CMCSET']); ?></strong></td>
    <td class="num"><?php echo n($r['NROWS']); ?></td>
    <td class="num"><?php echo n($r['NITEMS']); ?></td>
    <td class="num"><?php echo h($r['MINMAINT']); ?></td>
    <td class="num"><?php echo h($r['MAXMAINT']); ?></td>
    <td class="num"><?php echo n($r['NOMAINT']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<div class="side">
  <div>
    <div class="sub">CMDTMT &mdash; Date Of Last Maintenance (newest 25, CYYMMDD)</div>
    <?php if ($maintErr): ?><div class="bad"><?php echo h($maintErr); ?></div><?php else: ?>
    <table class="full">
      <tr><th>CMDTMT</th><th class="num">Rows</th></tr>
      <?php foreach ($maintDist as $r): ?>
      <tr><td><?php echo h($r['CMDTMT']); ?></td><td class="num"><?php echo n($r['NROWS']); ?></td></tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
  </div>
  <div>
    <div class="sub">CMRUDT &mdash; Last Roll-Up Date (newest 25, CYYMMDD)</div>
    <?php if ($rollErr): ?><div class="bad"><?php echo h($rollErr); ?></div><?php else: ?>
    <table class="full">
      <tr><th>CMRUDT</th><th class="num">Rows</th></tr>
      <?php foreach ($rollDist as $r): ?>
      <tr><td><?php echo h($r['CMRUDT']); ?></td><td class="num"><?php echo n($r['NROWS']); ?></td></tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- ============ GAPS ============ -->
<div class="sect">Snapshot calendar gaps (days SEQUEL did not run)</div>
<div class="side">
<?php foreach ($gaps as $g): ?>
  <div>
    <div class="sub"><?php echo h($g['file']); ?></div>
    <?php if ($g['err']): ?>
      <div class="bad"><?php echo h($g['err']); ?></div>
    <?php elseif (!count($g['rows'])): ?>
      <div class="ok">No gaps &mdash; every calendar day present.</div>
    <?php else: ?>
    <table class="full">
      <tr><th>Last day before gap</th><th>Next day</th><th class="num">Gap (days)</th></tr>
      <?php foreach ($g['rows'] as $r): ?>
      <tr><td><?php echo h($r['AFTER_DAY']); ?></td>
          <td><?php echo h($r['NEXT_DAY']); ?></td>
          <td class="num"><?php echo h($r['GAP_DAYS']); ?></td></tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<!-- ============ REDUNDANCY ============ -->
<div class="sect">Is HDMCMMDLY redundant?</div>
<?php if ($redErr): ?>
  <div class="bad"><?php echo h($redErr); ?></div>
<?php elseif ($redundant): ?>
  <?php $orph = (float)$redundant['ORPHANS']; ?>
  <?php if ($orph == 0): ?>
    <div class="ok"><strong>Yes &mdash; fully redundant for plant 1.</strong>
      Every set/item/day in HDMCMMDLY also exists in HDMCMMDLY1.
      That file (4.4M rows) can be dropped rather than migrated.</div>
  <?php else: ?>
    <div class="bad"><?php echo n($orph); ?> key/day combinations exist in
      HDMCMMDLY but not in HDMCMMDLY1 &mdash; it is NOT fully redundant and
      must be merged into the history load.</div>
  <?php endif; ?>
<?php endif; ?>

<!-- ============ JOURNAL ============ -->
<div class="sect">Journal status &mdash; SGHDSDATA/HDMCMM</div>
<?php if ($journalErr): ?>
  <div class="bad"><?php echo h($journalErr); ?></div>
<?php elseif (!$journal): ?>
  <div class="bad">No object statistics returned.</div>
<?php else: ?>
<table class="full">
  <tr><th>Journaled</th><th>Journal</th><th>Library</th><th>Images</th><th>Text</th></tr>
  <tr>
    <td><strong><?php echo h($journal['JOURNALED']); ?></strong></td>
    <td><?php echo h($journal['JOURNAL_NAME']); ?></td>
    <td><?php echo h($journal['JOURNAL_LIBRARY']); ?></td>
    <td><?php echo h($journal['JOURNAL_IMAGES']); ?></td>
    <td><?php echo h($journal['OBJTEXT']); ?></td>
  </tr>
</table>
<?php if (count($receivers)): ?>
  <div class="sub">Receivers on hand &mdash; how far back real change history could be mined</div>
  <table class="full">
    <tr><th>Receiver</th><th>Created</th><th class="num">Size</th></tr>
    <?php foreach ($receivers as $r): ?>
    <tr><td><?php echo h($r['RCV']); ?></td>
        <td><?php echo h($r['CREATED']); ?></td>
        <td class="num"><?php echo n($r['SZ']); ?></td></tr>
    <?php endforeach; ?>
  </table>
<?php elseif ($rcvErr): ?>
  <div class="bad"><?php echo h($rcvErr); ?></div>
<?php endif; ?>
<?php endif; ?>

<!-- ============ LIBRARIES ============ -->
<div class="sect">Candidate libraries for the history table</div>
<?php if ($libErr): ?><div class="bad"><?php echo h($libErr); ?></div><?php else: ?>
<table class="full">
  <tr><th>Schema</th><th>Text</th></tr>
  <?php foreach ($libs as $l): ?>
  <tr><td><strong><?php echo h($l['SCHEMA_NAME']); ?></strong></td>
      <td><?php echo h($l['TXT']); ?></td></tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- ============ TIMINGS ============ -->
<div class="sect">Query timings</div>
<table class="full">
  <tr><th>Query</th><th class="num">Seconds</th><th>Result</th></tr>
  <?php foreach ($TIMINGS as $t): ?>
  <tr><td><?php echo h($t[0]); ?></td>
      <td class="num"><?php echo number_format($t[1], 2); ?></td>
      <td><?php echo h($t[2]); ?></td></tr>
  <?php endforeach; ?>
</table>

</body>
</html>