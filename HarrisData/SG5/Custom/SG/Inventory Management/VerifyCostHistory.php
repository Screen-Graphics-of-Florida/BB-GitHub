<?php
// VerifyCostHistory.php
// READ-ONLY verification of SGOBJ.SGCSTHST after the one-time build.
//
// The build ran these statements in order:
//   1 CREATE TABLE      5 SEED from HDMCMMDLY1
//   2 LABEL ON          6 SEED from HDMCMMDLY2
//   3 CREATE INDEX L1   7 CREATE PROCEDURE SGCSTCAP   <-- last
//   4 CREATE INDEX L2
//
// Because the procedure is created LAST, its existence proves every earlier
// statement committed. That is the completeness test below.
//
// Also runs integrity checks that would catch a partial or double-seeded
// table: more than one open row per key, end dates before effective dates,
// overlapping or gapped date ranges, and history keys that do not reconcile
// against the live cost master.
//
// Writes nothing.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/Inventory%20Management/VerifyCostHistory.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(600);

$LIB   = 'SGOBJ';
$TBL   = 'SGCSTHST';
$PLANT = 1;
$OPEN  = "DATE('9999-12-31')";

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

function qrows($conn, $sql, &$err = null) {
    $err = null;
    $s = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$s) { $err = db2_stmt_errormsg(); return array(); }
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    db2_free_stmt($s);
    return $out;
}
function qone($conn, $sql, &$err = null) {
    $r = qrows($conn, $sql, $err);
    return count($r) ? $r[0] : null;
}
function h($v)  { return htmlspecialchars(trim((string)$v)); }
function nf($v) { return $v === null || $v === '' ? '' : number_format((float)$v); }

// ---------- 1. did the build finish? --------------------------------------

// QSYS2.SYSROUTINES has no CREATED column on this release -- ask only for
// columns that exist everywhere.
$proc = qone($conn,
    "SELECT ROUTINE_NAME, ROUTINE_TYPE
       FROM QSYS2.SYSROUTINES
      WHERE ROUTINE_SCHEMA = '$LIB' AND ROUTINE_NAME = 'SGCSTCAP'", $procErr);

// Fall back to the object catalog if the routine catalog is unavailable.
$procObj = qone($conn,
    "SELECT OBJNAME, OBJTYPE, OBJCREATED
       FROM TABLE(QSYS2.OBJECT_STATISTICS('$LIB','*PGM *SRVPGM')) x
      WHERE OBJNAME = 'SGCSTCAP'", $procObjErr);

$idx = qrows($conn,
    "SELECT OBJNAME AS INDEX_NAME, OBJTYPE, OBJTEXT
       FROM TABLE(QSYS2.OBJECT_STATISTICS('$LIB','*FILE')) x
      WHERE OBJNAME LIKE 'SGCSTHST%'
      ORDER BY OBJNAME", $idxErr);

$total = qone($conn, "SELECT COUNT(*) AS N FROM $LIB.$TBL", $totErr);

// ---------- 2. breakdown by cost set --------------------------------------

$bySet = qrows($conn,
    "SELECT CHCSET,
            COUNT(*)                AS NROWS,
            COUNT(DISTINCT CHPN)    AS NITEMS,
            SUM(CASE WHEN CHSRC='B' THEN 1 ELSE 0 END) AS NBASE,
            SUM(CASE WHEN CHSRC='S' THEN 1 ELSE 0 END) AS NCHG,
            SUM(CASE WHEN CHSRC='N' THEN 1 ELSE 0 END) AS NNIGHT,
            MIN(CHEFFD)             AS DMIN,
            MAX(CHEFFD)             AS DMAX,
            SUM(CASE WHEN CHENDD = $OPEN THEN 1 ELSE 0 END) AS NOPEN
       FROM $LIB.$TBL
      GROUP BY CHCSET ORDER BY CHCSET", $bySetErr);

// ---------- 3. integrity checks -------------------------------------------

$checks = array();

// a. exactly one open row per key -- more than one means a broken seed
$c = qone($conn,
    "SELECT COUNT(*) AS N FROM (
        SELECT CHPLT, CHCSET, CHPN, COUNT(*) AS NOPEN
          FROM $LIB.$TBL WHERE CHENDD = $OPEN
         GROUP BY CHPLT, CHCSET, CHPN HAVING COUNT(*) > 1) t", $e);
$checks[] = array('Keys with more than one open row', $c ? $c['N'] : null, 0,
    'Each item/cost set must have exactly one row currently in effect.', $e);

// b. end date must not precede effective date
$c = qone($conn,
    "SELECT COUNT(*) AS N FROM $LIB.$TBL WHERE CHENDD < CHEFFD", $e);
$checks[] = array('Rows with end date before effective date', $c ? $c['N'] : null, 0,
    'Would indicate a bad range calculation in the seed.', $e);

// c. no overlapping ranges within a key
$c = qone($conn,
    "SELECT COUNT(*) AS N FROM (
        SELECT CHPLT, CHCSET, CHPN, CHEFFD, CHENDD,
               LEAD(CHEFFD) OVER (PARTITION BY CHPLT, CHCSET, CHPN
                                  ORDER BY CHEFFD) AS NEXTEFF
          FROM $LIB.$TBL) t
      WHERE NEXTEFF IS NOT NULL AND NEXTEFF <= CHENDD", $e);
$checks[] = array('Overlapping date ranges', $c ? $c['N'] : null, 0,
    'One row must end the day before the next begins.', $e);

// d. no gaps within a key -- history should be continuous.
//    DAYS() arithmetic, not "+ 1 DAY": adding a day to the 9999-12-31 open
//    marker runs off the end of the calendar and raises SQLCODE -183.
$c = qone($conn,
    "SELECT COUNT(*) AS N FROM (
        SELECT CHPLT, CHCSET, CHPN, CHENDD,
               LEAD(CHEFFD) OVER (PARTITION BY CHPLT, CHCSET, CHPN
                                  ORDER BY CHEFFD) AS NEXTEFF
          FROM $LIB.$TBL) t
      WHERE NEXTEFF IS NOT NULL
        AND DAYS(NEXTEFF) <> DAYS(CHENDD) + 1", $e);
$checks[] = array('Gaps between consecutive rows', $c ? $c['N'] : null, 0,
    'A gap means a period with no recorded cost.', $e);

// e. exactly one baseline row per key
$c = qone($conn,
    "SELECT COUNT(*) AS N FROM (
        SELECT CHPLT, CHCSET, CHPN FROM $LIB.$TBL WHERE CHSRC = 'B'
         GROUP BY CHPLT, CHCSET, CHPN HAVING COUNT(*) > 1) t", $e);
$checks[] = array('Keys with more than one baseline row', $c ? $c['N'] : null, 0,
    'A second baseline would mean a seed statement ran twice.', $e);

// f. baseline rows should all sit on the backfill floor (or the file's start)
$baseDates = qrows($conn,
    "SELECT CHCSET, CHEFFD, COUNT(*) AS N
       FROM $LIB.$TBL WHERE CHSRC = 'B'
      GROUP BY CHCSET, CHEFFD ORDER BY CHCSET, CHEFFD
      FETCH FIRST 10 ROWS ONLY", $baseErr);

// ---------- 3b. seed completeness, recomputed from source ----------------
// The strongest test available: re-run the seed's own change-detection logic
// against the snapshot files and compare the row count it SHOULD have produced
// with what is actually stored. Independent of whether the procedure exists.

$SEED_FROM = '2025-08-07';
$COSTC = array('UCC1','UCC2','UCC3','UCC4','UCC5','LCC1','LCC2','LCC3','LCC4','LCC5',
               'BSC1','BSC2','BSC3','BSC4','BSC5');
$SIGX = implode("||", array_map(function ($c) { return "DIGITS(CM$c)"; }, $COSTC));

$seedCheck = array();
foreach (array(array('HDMCMMDLY1', 1), array('HDMCMMDLY2', 2)) as $sf) {
    list($file, $set) = $sf;
    $exp = qone($conn, "
        SELECT COUNT(*) AS N FROM (
          SELECT CMPLT, CMCSET, CMPN, DERIVED_01, $SIGX AS SIG,
                 LAG($SIGX) OVER (PARTITION BY CMPLT, CMCSET, CMPN
                                  ORDER BY DERIVED_01) AS PSIG
            FROM (SELECT CMPLT, CMCSET, CMPN, DERIVED_01, " . implode(',', array_map(
                        function ($c) { return "CM$c"; }, $COSTC)) . ",
                         ROW_NUMBER() OVER (PARTITION BY CMPLT, CMCSET, CMPN, DERIVED_01
                                            ORDER BY DERIVED_01) AS RN
                    FROM SEQUELDBF.$file
                   WHERE CMPLT = $PLANT AND DERIVED_01 >= DATE('$SEED_FROM')) d
           WHERE RN = 1) t
         WHERE PSIG IS NULL OR SIG <> PSIG", $eSeed);

    $act = qone($conn,
        "SELECT COUNT(*) AS N FROM $LIB.$TBL
          WHERE CHCSET = $set AND CHSRC IN ('B','S')", $eAct);

    $seedCheck[] = array(
        'file'     => $file,
        'set'      => $set,
        'expected' => $exp ? (int)$exp['N'] : null,
        'actual'   => $act ? (int)$act['N'] : null,
        'err'      => $eSeed ? $eSeed : $eAct,
    );
}

$seedComplete = true;
foreach ($seedCheck as $s) {
    if ($s['expected'] === null || $s['actual'] === null
        || $s['expected'] !== $s['actual']) $seedComplete = false;
}

// ---------- 4. reconcile against the live cost master ---------------------

$recon = qone($conn,
    "SELECT
       (SELECT COUNT(*) FROM SGHDSDATA.HDMCMM WHERE CMPLT = $PLANT)      AS LIVE_ROWS,
       (SELECT COUNT(*) FROM $LIB.$TBL WHERE CHENDD = $OPEN)             AS HIST_OPEN,
       (SELECT COUNT(*) FROM (
            SELECT CMCSET, CMPN FROM SGHDSDATA.HDMCMM WHERE CMPLT = $PLANT
            EXCEPT
            SELECT CHCSET, CHPN FROM $LIB.$TBL WHERE CHENDD = $OPEN) a)  AS LIVE_NOT_HIST,
       (SELECT COUNT(*) FROM (
            SELECT CHCSET, CHPN FROM $LIB.$TBL WHERE CHENDD = $OPEN
            EXCEPT
            SELECT CMCSET, CMPN FROM SGHDSDATA.HDMCMM WHERE CMPLT = $PLANT) b) AS HIST_NOT_LIVE
     FROM SYSIBM.SYSDUMMY1", $reconErr);

// Do the open history rows actually agree with live values?
$COST = array('UCC1','UCC2','UCC3','UCC4','UCC5','LCC1','LCC2','LCC3','LCC4','LCC5',
              'BSC1','BSC2','BSC3','BSC4','BSC5');
$MATCH = implode(" AND ", array_map(function ($c) { return "m.CM$c = h.CH$c"; }, $COST));

$mismatch = qone($conn,
    "SELECT COUNT(*) AS N
       FROM $LIB.$TBL h
       JOIN SGHDSDATA.HDMCMM m
         ON m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET AND m.CMPN = h.CHPN
      WHERE h.CHENDD = $OPEN AND NOT ($MATCH)", $misErr);

// ---------- 5. a worked example -------------------------------------------
// The item with the most history rows, so the timeline is worth looking at.

$busiest = qone($conn,
    "SELECT CHPLT, CHCSET, CHPN, COUNT(*) AS N
       FROM $LIB.$TBL GROUP BY CHPLT, CHCSET, CHPN
      ORDER BY COUNT(*) DESC FETCH FIRST 1 ROWS ONLY", $busyErr);

$timeline = array();
if ($busiest) {
    $pn = str_replace("'", "''", trim($busiest['CHPN']));
    $timeline = qrows($conn,
        "SELECT CHCSET, CHPN, CHEFFD, CHENDD, CHTOTU, CHSRC,
                CHUCC1, CHUCC2, CHUCC3, CHUCC4, CHUCC5
           FROM $LIB.$TBL
          WHERE CHPLT = $PLANT AND CHPN = '$pn'
          ORDER BY CHCSET, CHEFFD", $tlErr);
}

// ---------- 6. change volume by month -------------------------------------

$byMonth = qrows($conn,
    "SELECT YEAR(CHEFFD) AS YR, MONTH(CHEFFD) AS MO, CHCSET,
            COUNT(*) AS N
       FROM $LIB.$TBL WHERE CHSRC <> 'B'
      GROUP BY YEAR(CHEFFD), MONTH(CHEFFD), CHCSET
      ORDER BY 1, 2, 3", $monErr);

db2_close($conn);

$buildComplete = ($proc !== null) || ($procObj !== null);
$allChecksPass = true;
foreach ($checks as $ck) if ($ck[1] === null || (int)$ck[1] !== (int)$ck[2]) $allChecksPass = false;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Verify Cost History</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.sect { font-weight:bold; font-size:14px; margin:22px 0 8px; color:#1a3d5c;
        border-bottom:2px solid #90caf9; padding-bottom:3px; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.big  { border-radius:5px; padding:14px 18px; margin-bottom:14px; font-size:14px; }
.bigok  { background:#e8f5e9; border:2px solid #66bb6a; }
.bigbad { background:#ffebee; border:2px solid #ef5350; }
.big strong { font-size:18px; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px; font-family:monospace; }
table.full { border-collapse:collapse; width:100%; background:#fff; border-radius:4px;
             overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
table.full th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
                padding:5px 8px; text-align:left; font-size:11px; white-space:nowrap; }
table.full td { padding:4px 8px; font-size:11px; border-bottom:1px solid #f0f0f0; }
td.num, th.num { text-align:right; font-family:monospace; white-space:nowrap; }
.pass { color:#2e7d32; font-weight:bold; }
.fail { color:#c62828; font-weight:bold; }
.scroll { overflow-x:auto; margin-bottom:14px; }
</style>
</head>
<body>

<div class="hdr">Verify Cost History &mdash; SGOBJ.SGCSTHST</div>
<div class="info">Read-only. Run: <?php echo date('Y-m-d H:i:s'); ?></div>

<!-- completeness ----------------------------------------------------------->
<div class="sect">Did the build finish?</div>
<?php if ($buildComplete && $seedComplete): ?>
  <div class="big bigok">
    <strong>Yes &mdash; build completed and the seed is provably whole.</strong><br>
    <code>SGOBJ.SGCSTCAP</code> exists (it was the last statement, so everything
    before it committed), and the stored row counts match a fresh recomputation
    of the seed logic against the snapshot files exactly.
  </div>
<?php elseif ($seedComplete): ?>
  <div class="big bigbad">
    <strong>Seed is complete, but the procedure is missing.</strong><br>
    The data is whole &mdash; stored counts match a fresh recomputation. Only
    <code>SGOBJ.SGCSTCAP</code> failed to create. Do <em>not</em> drop the table;
    the procedure alone can be recreated.
  </div>
<?php else: ?>
  <div class="big bigbad">
    <strong>Seed does not reconcile.</strong><br>
    Stored row counts differ from a fresh recomputation &mdash; see the seed
    completeness table below. Drop and re-run:
    <code>DROP TABLE <?php echo h("$LIB.$TBL"); ?></code>
  </div>
<?php endif; ?>

<table class="full">
  <tr><th>Object</th><th>Status</th></tr>
  <tr><td><?php echo h("$LIB.$TBL"); ?></td>
      <td><?php echo $total ? nf($total['N']) . ' rows' : h($totErr); ?></td></tr>
  <?php foreach ($idx as $i): ?>
  <tr><td><?php echo h($LIB . '.' . $i['INDEX_NAME']); ?></td>
      <td><?php echo h($i['OBJTYPE']); ?>
          <?php echo h($i['OBJTEXT']) ? '&mdash; ' . h($i['OBJTEXT']) : ''; ?></td></tr>
  <?php endforeach; ?>
  <?php if ($idxErr): ?>
  <tr><td colspan="2" style="color:#c62828"><?php echo h($idxErr); ?></td></tr>
  <?php endif; ?>
  <tr><td><?php echo h("$LIB.SGCSTCAP"); ?></td>
      <td><?php if ($proc) { echo h($proc['ROUTINE_TYPE']) . ' &mdash; present (SYSROUTINES)'; }
                elseif ($procObj) { echo h($procObj['OBJTYPE']) . ' &mdash; present, created '
                                       . h($procObj['OBJCREATED']); }
                else { echo 'MISSING'; if ($procErr) echo ' &mdash; ' . h($procErr); } ?></td></tr>
</table>

<div class="sect">Seed completeness &mdash; recomputed from the snapshot files</div>
<div class="info">
  Re-runs the seed's own change-detection logic against SEQUELDBF and compares
  the row count it should have produced with what is stored. This is
  independent of whether the procedure was created, and is the check that
  actually settles whether the load finished.
</div>
<table class="full">
  <tr><th>Source file</th><th>Cost set</th><th class="num">Expected rows</th>
      <th class="num">Stored rows</th><th class="num">Difference</th><th>Result</th></tr>
  <?php foreach ($seedCheck as $s): ?>
  <tr>
    <td><?php echo h($s['file']); ?></td>
    <td><?php echo h($s['set']); ?></td>
    <td class="num"><?php echo $s['expected'] === null ? '?' : nf($s['expected']); ?></td>
    <td class="num"><?php echo $s['actual'] === null ? '?' : nf($s['actual']); ?></td>
    <td class="num"><?php echo ($s['expected'] === null || $s['actual'] === null)
                        ? '' : nf($s['actual'] - $s['expected']); ?></td>
    <td class="<?php echo ($s['expected'] !== null && $s['expected'] === $s['actual'])
                    ? 'pass' : 'fail'; ?>">
      <?php echo ($s['expected'] !== null && $s['expected'] === $s['actual'])
                 ? 'PASS' : 'FAIL'; ?>
      <?php if ($s['err']) echo '<br>' . h($s['err']); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<!-- by cost set ------------------------------------------------------------>
<div class="sect">History by cost set</div>
<?php if ($bySetErr): ?><div class="bad"><?php echo h($bySetErr); ?></div><?php endif; ?>
<table class="full">
  <tr><th>Cost Set</th><th class="num">Rows</th><th class="num">Items</th>
      <th class="num">Baseline B</th><th class="num">Seeded change S</th>
      <th class="num">Nightly N</th><th>Earliest</th><th>Latest</th>
      <th class="num">Open now</th><th class="num">Avg changes/item</th></tr>
  <?php foreach ($bySet as $v): ?>
  <tr>
    <td><strong><?php echo h($v['CHCSET']); ?></strong></td>
    <td class="num"><?php echo nf($v['NROWS']); ?></td>
    <td class="num"><?php echo nf($v['NITEMS']); ?></td>
    <td class="num"><?php echo nf($v['NBASE']); ?></td>
    <td class="num"><?php echo nf($v['NCHG']); ?></td>
    <td class="num"><?php echo nf($v['NNIGHT']); ?></td>
    <td><?php echo h($v['DMIN']); ?></td>
    <td><?php echo h($v['DMAX']); ?></td>
    <td class="num"><?php echo nf($v['NOPEN']); ?></td>
    <td class="num"><?php
        echo (int)$v['NITEMS'] ? number_format((float)$v['NROWS'] / (float)$v['NITEMS'], 1) : '';
    ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<!-- integrity -------------------------------------------------------------->
<div class="sect">Integrity checks</div>
<?php if ($allChecksPass): ?>
  <div class="big bigok"><strong>All checks pass.</strong>
    Ranges are continuous and non-overlapping, one open row per key,
    one baseline per key.</div>
<?php else: ?>
  <div class="big bigbad"><strong>One or more checks failed</strong> &mdash;
    see the table below before scheduling the nightly job.</div>
<?php endif; ?>
<table class="full">
  <tr><th>Check</th><th class="num">Found</th><th class="num">Expected</th>
      <th>Result</th><th>Meaning</th></tr>
  <?php foreach ($checks as $ck): ?>
  <tr>
    <td><?php echo h($ck[0]); ?></td>
    <td class="num"><?php echo $ck[1] === null ? '?' : nf($ck[1]); ?></td>
    <td class="num"><?php echo nf($ck[2]); ?></td>
    <td class="<?php echo ($ck[1] !== null && (int)$ck[1] === (int)$ck[2]) ? 'pass' : 'fail'; ?>">
        <?php echo ($ck[1] !== null && (int)$ck[1] === (int)$ck[2]) ? 'PASS' : 'FAIL'; ?></td>
    <td><?php echo h($ck[3]); ?><?php if (!empty($ck[4])) echo ' &mdash; ' . h($ck[4]); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<div class="sect">Baseline row dates</div>
<div class="info">All baseline rows should sit on the backfill floor
  (2025-08-07 for set 1) or on the snapshot file's own first day
  (2025-08-18 for set 2). Scattered dates would mean items appeared mid-window,
  which is normal for new items.</div>
<table class="full">
  <tr><th>Cost Set</th><th>Effective date</th><th class="num">Rows</th></tr>
  <?php foreach ($baseDates as $b): ?>
  <tr><td><?php echo h($b['CHCSET']); ?></td>
      <td><?php echo h($b['CHEFFD']); ?></td>
      <td class="num"><?php echo nf($b['N']); ?></td></tr>
  <?php endforeach; ?>
</table>

<!-- reconciliation --------------------------------------------------------->
<div class="sect">Reconciliation against live SGHDSDATA/HDMCMM</div>
<?php if ($reconErr): ?><div class="bad"><?php echo h($reconErr); ?></div><?php endif; ?>
<?php if ($recon): ?>
<table class="full">
  <tr><th>Measure</th><th class="num">Count</th><th>Meaning</th></tr>
  <tr><td>Live HDMCMM rows (plant 1)</td><td class="num"><?php echo nf($recon['LIVE_ROWS']); ?></td>
      <td>All three cost sets.</td></tr>
  <tr><td>History rows currently open</td><td class="num"><?php echo nf($recon['HIST_OPEN']); ?></td>
      <td>Will be short by cost set 3 until the capture procedure runs.</td></tr>
  <tr><td>In live but not in history</td><td class="num"><?php echo nf($recon['LIVE_NOT_HIST']); ?></td>
      <td>Expected to be ~23,000 &mdash; cost set 3, plus anything added since
          the last snapshot. The first capture run absorbs these.</td></tr>
  <tr><td>In history but not in live</td><td class="num"><?php echo nf($recon['HIST_NOT_LIVE']); ?></td>
      <td>Items deleted from the cost master since the snapshot. The capture
          run closes these off.</td></tr>
  <tr><td>Open rows disagreeing with live values</td>
      <td class="num"><?php echo $mismatch ? nf($mismatch['N']) : h($misErr); ?></td>
      <td>Costs changed between the last snapshot (2026-08-06) and now. The
          capture run writes these as changes.</td></tr>
</table>
<?php endif; ?>

<!-- worked example --------------------------------------------------------->
<div class="sect">Worked example &mdash; item with the most history</div>
<?php if ($busiest): ?>
<div class="info">Item <strong><?php echo h($busiest['CHPN']); ?></strong>
  &mdash; <?php echo h($busiest['N']); ?> rows in cost set
  <?php echo h($busiest['CHCSET']); ?>. Shown across all cost sets.
  <code>CHENDD</code> of 9999-12-31 is the value in effect now.</div>
<div class="scroll">
<table class="full">
  <tr><th>Set</th><th>Effective</th><th>Through</th><th class="num">Total unit cost</th>
      <th class="num">Cat 1</th><th class="num">Cat 2</th><th class="num">Cat 3</th>
      <th class="num">Cat 4</th><th class="num">Cat 5</th><th>Source</th></tr>
  <?php $prev = array(); foreach ($timeline as $t):
      $k = $t['CHCSET']; $d = null;
      if (isset($prev[$k]) && (float)$prev[$k] != 0)
          $d = ((float)$t['CHTOTU'] - (float)$prev[$k]) / (float)$prev[$k] * 100;
      $prev[$k] = $t['CHTOTU'];
  ?>
  <tr>
    <td><?php echo h($t['CHCSET']); ?></td>
    <td><?php echo h($t['CHEFFD']); ?></td>
    <td><?php echo trim($t['CHENDD']) === '9999-12-31' ? '<strong>current</strong>' : h($t['CHENDD']); ?></td>
    <td class="num"><?php echo number_format((float)$t['CHTOTU'], 5);
        if ($d !== null) printf(' <span style="color:%s">(%+.1f%%)</span>',
                                $d >= 0 ? '#c62828' : '#2e7d32', $d); ?></td>
    <td class="num"><?php echo number_format((float)$t['CHUCC1'], 5); ?></td>
    <td class="num"><?php echo number_format((float)$t['CHUCC2'], 5); ?></td>
    <td class="num"><?php echo number_format((float)$t['CHUCC3'], 5); ?></td>
    <td class="num"><?php echo number_format((float)$t['CHUCC4'], 5); ?></td>
    <td class="num"><?php echo number_format((float)$t['CHUCC5'], 5); ?></td>
    <td><?php echo h($t['CHSRC']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<!-- monthly ---------------------------------------------------------------->
<div class="sect">Change volume by month (baseline rows excluded)</div>
<?php if ($monErr): ?><div class="bad"><?php echo h($monErr); ?></div><?php endif; ?>
<table class="full">
  <tr><th>Month</th><th class="num">Set 1 Standard</th><th class="num">Set 2 Current</th>
      <th class="num">Set 3</th></tr>
  <?php
    $m = array();
    foreach ($byMonth as $r) {
        $key = sprintf('%04d-%02d', $r['YR'], $r['MO']);
        if (!isset($m[$key])) $m[$key] = array(1=>0, 2=>0, 3=>0);
        $m[$key][(int)$r['CHCSET']] = $r['N'];
    }
    foreach ($m as $key => $v): ?>
  <tr><td><?php echo h($key); ?></td>
      <td class="num"><?php echo nf($v[1]); ?></td>
      <td class="num"><?php echo nf($v[2]); ?></td>
      <td class="num"><?php echo nf($v[3]); ?></td></tr>
  <?php endforeach; ?>
</table>

</body>
</html>