<?php
// RunCostCapture.php
// Runs SGOBJ.SGCSTCAP by hand and shows what it did.
//
// The nightly CL (SGPGM/SGCSTCAPC) calls the same procedure. This page exists
// so the FIRST run can be previewed and inspected, and so the capture can be
// driven manually later without a green screen.
//
// Preview (no ?go=1) shows exactly what the procedure is about to do, using
// the same predicates it uses internally. Nothing is written until ?go=1.
//
// After the CALL it runs one tidy-up: the earliest row for a key is an opening
// value, not a change, so any such row still tagged 'N' is re-tagged 'B'. On
// the first run this affects cost set 3, which has no snapshot history and is
// seeing its costs recorded for the first time. It also correctly tags brand
// new items added later.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/Inventory%20Management/RunCostCapture.php
//      add ?go=1 to execute.

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(900);

$GO    = isset($_GET['go']) && $_GET['go'] === '1';
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

$COST  = array('UCC1','UCC2','UCC3','UCC4','UCC5','LCC1','LCC2','LCC3','LCC4','LCC5',
               'BSC1','BSC2','BSC3','BSC4','BSC5');
$MATCH = implode(" AND ", array_map(function ($c) { return "m.CM$c = h.CH$c"; }, $COST));

// ---------- preview: what will the procedure do? --------------------------
// Same predicates the procedure uses, counted rather than applied.

$willClose = qone($conn,
    "SELECT COUNT(*) AS N FROM $LIB.$TBL h
      WHERE h.CHPLT = $PLANT AND h.CHENDD = $OPEN
        AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                         WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                           AND m.CMPN = h.CHPN AND $MATCH)", $closeErr);

$willOpen = qone($conn,
    "SELECT COUNT(*) AS N FROM SGHDSDATA.HDMCMM m
      WHERE m.CMPLT = $PLANT
        AND NOT EXISTS (SELECT 1 FROM $LIB.$TBL h
                         WHERE h.CHPLT = m.CMPLT AND h.CHCSET = m.CMCSET
                           AND h.CHPN = m.CMPN AND h.CHENDD = $OPEN)", $openErr);

// Break the opens down by cost set so set 3's first appearance is obvious.
$openBySet = qrows($conn,
    "SELECT m.CMCSET, COUNT(*) AS N FROM SGHDSDATA.HDMCMM m
      WHERE m.CMPLT = $PLANT
        AND NOT EXISTS (SELECT 1 FROM $LIB.$TBL h
                         WHERE h.CHPLT = m.CMPLT AND h.CHCSET = m.CMCSET
                           AND h.CHPN = m.CMPN AND h.CHENDD = $OPEN)
      GROUP BY m.CMCSET ORDER BY m.CMCSET", $obsErr);

$closeBySet = qrows($conn,
    "SELECT h.CHCSET, COUNT(*) AS N FROM $LIB.$TBL h
      WHERE h.CHPLT = $PLANT AND h.CHENDD = $OPEN
        AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                         WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                           AND m.CMPN = h.CHPN AND $MATCH)
      GROUP BY h.CHCSET ORDER BY h.CHCSET", $cbsErr);

$before = qone($conn, "SELECT COUNT(*) AS N FROM $LIB.$TBL");

// ---------- execute -------------------------------------------------------

$ran = false; $callErr = null; $closed = null; $opened = null;
$after = null; $secs = 0;

if ($GO) {
    // IN-only signature. The nightly CL drives this through RUNSQL, which
    // cannot receive OUT parameters, so results are written to SGOBJ.SGCSTLOG
    // and read back from there. The opening-value tidy-up now lives inside the
    // procedure, so it runs for scheduled executions too, not just this page.
    $t0 = microtime(true);
    $ok = @db2_exec($conn, "CALL SGPGM.SGCSTCAP($PLANT)");
    $secs = microtime(true) - $t0;

    if ($ok) {
        $ran = true;
        @db2_free_stmt($ok);
        $lg = qone($conn,
            "SELECT LGCLOSED, LGOPENED, LGSTAT, LGMSG
               FROM $LIB.SGCSTLOG
              ORDER BY LGSTART DESC FETCH FIRST 1 ROWS ONLY", $lgErr);
        if ($lg) {
            $closed = $lg['LGCLOSED'];
            $opened = $lg['LGOPENED'];
            if (trim($lg['LGSTAT']) !== 'O') $callErr = trim($lg['LGMSG']);
        }
        $after = qone($conn, "SELECT COUNT(*) AS N FROM $LIB.$TBL");
    } else {
        $callErr = db2_stmt_errormsg();
    }
}

// Recent runs, however they were triggered -- Robot, CL by hand, or this page.
$runLog = qrows($conn,
    "SELECT LGSTART, LGEND, LGRUND, LGCLOSED, LGOPENED, LGSTAT, LGMSG, LGUSER
       FROM $LIB.SGCSTLOG ORDER BY LGSTART DESC FETCH FIRST 15 ROWS ONLY",
    $runLogErr);

// ---------- post-run state ------------------------------------------------

$bySet = qrows($conn,
    "SELECT CHCSET,
            COUNT(*) AS NROWS, COUNT(DISTINCT CHPN) AS NITEMS,
            SUM(CASE WHEN CHSRC='B' THEN 1 ELSE 0 END) AS NBASE,
            SUM(CASE WHEN CHSRC='S' THEN 1 ELSE 0 END) AS NCHG,
            SUM(CASE WHEN CHSRC='N' THEN 1 ELSE 0 END) AS NNIGHT,
            MIN(CHEFFD) AS DMIN, MAX(CHEFFD) AS DMAX,
            SUM(CASE WHEN CHENDD = $OPEN THEN 1 ELSE 0 END) AS NOPEN
       FROM $LIB.$TBL GROUP BY CHCSET ORDER BY CHCSET", $bySetErr);

// Residual drift: after a successful run these must all be zero.
$residual = qone($conn,
    "SELECT
       (SELECT COUNT(*) FROM (
          SELECT CMCSET, CMPN FROM SGHDSDATA.HDMCMM WHERE CMPLT = $PLANT
          EXCEPT
          SELECT CHCSET, CHPN FROM $LIB.$TBL WHERE CHENDD = $OPEN) a) AS LIVE_NOT_HIST,
       (SELECT COUNT(*) FROM $LIB.$TBL h
          JOIN SGHDSDATA.HDMCMM m
            ON m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET AND m.CMPN = h.CHPN
         WHERE h.CHENDD = $OPEN AND NOT ($MATCH))                      AS MISMATCH
     FROM SYSIBM.SYSDUMMY1", $resErr);

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Run Cost Capture</title>
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
.warn { background:#fff8e1; border:2px solid #ffc107; border-radius:5px;
        padding:12px 16px; margin-bottom:12px; font-size:13px; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px; font-family:monospace; }
table.full { border-collapse:collapse; width:100%; background:#fff; border-radius:4px;
             overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
table.full th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
                padding:5px 8px; text-align:left; font-size:11px; white-space:nowrap; }
table.full td { padding:4px 8px; font-size:11px; border-bottom:1px solid #f0f0f0; }
td.num, th.num { text-align:right; font-family:monospace; white-space:nowrap; }
.pass { color:#2e7d32; font-weight:bold; } .fail { color:#c62828; font-weight:bold; }
.go { display:inline-block; background:#c62828; color:#fff !important; padding:9px 20px;
      border-radius:4px; text-decoration:none; font-weight:bold; font-size:13px; }
</style>
</head>
<body>

<div class="hdr">Run Cost Capture &mdash; SGOBJ.SGCSTCAP</div>
<div class="info">
  Run: <?php echo date('Y-m-d H:i:s'); ?> &nbsp;|&nbsp;
  Mode: <strong><?php echo $GO ? 'EXECUTE' : 'PREVIEW'; ?></strong> &nbsp;|&nbsp;
  Plant <?php echo $PLANT; ?> &nbsp;|&nbsp;
  Table holds <?php echo $before ? nf($before['N']) : '?'; ?> rows
</div>

<?php if (!$GO): ?>
<!-- ============ PREVIEW ============ -->
<div class="sect">What the capture will do</div>
<div class="info">
  Counted using the procedure's own predicates. Nothing has been written.
</div>
<table class="full">
  <tr><th>Action</th><th class="num">Rows</th><th>Meaning</th></tr>
  <tr>
    <td><strong>Close</strong> (end-date an open row)</td>
    <td class="num"><?php echo $willClose ? nf($willClose['N']) : h($closeErr); ?></td>
    <td>Costs that changed since the last snapshot, plus items removed from
        the cost master.</td>
  </tr>
  <tr>
    <td><strong>Open</strong> (write a new current row)</td>
    <td class="num"><?php echo $willOpen ? nf($willOpen['N']) : h($openErr); ?></td>
    <td>Replacements for the above, plus cost set 3 and any new items,
        recorded for the first time.</td>
  </tr>
</table>

<div class="sect">Opens by cost set</div>
<table class="full">
  <tr><th>Cost Set</th><th class="num">Rows to open</th><th>Why</th></tr>
  <?php foreach ($openBySet as $o): ?>
  <tr><td><strong><?php echo h($o['CMCSET']); ?></strong></td>
      <td class="num"><?php echo nf($o['N']); ?></td>
      <td><?php echo ((int)$o['CMCSET'] === 3)
            ? 'No snapshot history &mdash; every item recorded for the first time.'
            : 'Changed since the last snapshot, or newly added.'; ?></td></tr>
  <?php endforeach; ?>
</table>

<div class="sect">Closes by cost set</div>
<table class="full">
  <tr><th>Cost Set</th><th class="num">Rows to close</th></tr>
  <?php foreach ($closeBySet as $c): ?>
  <tr><td><strong><?php echo h($c['CHCSET']); ?></strong></td>
      <td class="num"><?php echo nf($c['N']); ?></td></tr>
  <?php endforeach; ?>
</table>

<div class="sect">Execute</div>
<div class="warn">
  Writes to <?php echo h("$LIB.$TBL"); ?> only. Reads SGHDSDATA but never
  writes to it. The procedure is idempotent &mdash; running it again the same
  day is a no-op, so this is safe to repeat.
  <br><br>
  <a class="go" href="?go=1">Run SGOBJ.SGCSTCAP now</a>
</div>
<?php endif; ?>

<?php if ($GO): ?>
<!-- ============ RESULT ============ -->
<div class="sect">Result</div>
<?php if ($callErr): ?>
  <div class="big bigbad"><strong>CALL failed.</strong><br>
    <?php echo h($callErr); ?></div>
<?php elseif ($ran): ?>
  <div class="big bigok">
    <strong>Capture completed.</strong><br>
    Closed <strong><?php echo nf($closed); ?></strong> rows,
    opened <strong><?php echo nf($opened); ?></strong> rows
    in <?php echo number_format($secs, 2); ?> seconds.
    <br>Table went from <?php echo $before ? nf($before['N']) : '?'; ?>
    to <?php echo $after ? nf($after['N']) : '?'; ?> rows.
  </div>
<?php endif; ?>
<?php endif; ?>

<!-- ============ STATE ============ -->
<div class="sect">History by cost set<?php echo $GO ? ' (after)' : ' (current)'; ?></div>
<?php if ($bySetErr): ?><div class="bad"><?php echo h($bySetErr); ?></div><?php endif; ?>
<table class="full">
  <tr><th>Cost Set</th><th class="num">Rows</th><th class="num">Items</th>
      <th class="num">Opening B</th><th class="num">Seeded change S</th>
      <th class="num">Nightly N</th><th>Earliest</th><th>Latest</th>
      <th class="num">Open now</th></tr>
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
  </tr>
  <?php endforeach; ?>
</table>

<div class="sect">Recent capture runs</div>
<div class="info">
  Every run is logged here, whether it was triggered by Robot, by the CL on a
  green screen, or by this page. Robot owns job status and alerting; this
  records what each run actually changed.
</div>
<?php if ($runLogErr): ?><div class="bad"><?php echo h($runLogErr); ?></div><?php endif; ?>
<?php if (!count($runLog)): ?>
  <div class="info">No runs logged yet.</div>
<?php else: ?>
<table class="full">
  <tr><th>Started</th><th>Finished</th><th>Business date</th>
      <th class="num">Closed</th><th class="num">Opened</th>
      <th>Status</th><th>User</th><th>Message</th></tr>
  <?php foreach ($runLog as $r): ?>
  <tr>
    <td><?php echo h($r['LGSTART']); ?></td>
    <td><?php echo h($r['LGEND']); ?></td>
    <td><?php echo h($r['LGRUND']); ?></td>
    <td class="num"><?php echo nf($r['LGCLOSED']); ?></td>
    <td class="num"><?php echo nf($r['LGOPENED']); ?></td>
    <td class="<?php echo trim($r['LGSTAT']) === 'O' ? 'pass' : 'fail'; ?>">
      <?php echo trim($r['LGSTAT']) === 'O' ? 'OK' : 'FAILED'; ?></td>
    <td><?php echo h($r['LGUSER']); ?></td>
    <td><?php echo h($r['LGMSG']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<div class="sect">Residual drift against live</div>
<div class="info">
  After a successful capture both of these must be zero. Non-zero means the
  history no longer agrees with the live cost master.
</div>
<?php if ($resErr): ?><div class="bad"><?php echo h($resErr); ?></div><?php endif; ?>
<?php if ($residual): ?>
<table class="full">
  <tr><th>Measure</th><th class="num">Count</th><th>Result</th></tr>
  <tr><td>Live rows with no open history row</td>
      <td class="num"><?php echo nf($residual['LIVE_NOT_HIST']); ?></td>
      <td class="<?php echo (int)$residual['LIVE_NOT_HIST'] === 0 ? 'pass' : 'fail'; ?>">
        <?php echo (int)$residual['LIVE_NOT_HIST'] === 0 ? 'PASS' : 'FAIL'; ?></td></tr>
  <tr><td>Open history rows disagreeing with live values</td>
      <td class="num"><?php echo nf($residual['MISMATCH']); ?></td>
      <td class="<?php echo (int)$residual['MISMATCH'] === 0 ? 'pass' : 'fail'; ?>">
        <?php echo (int)$residual['MISMATCH'] === 0 ? 'PASS' : 'FAIL'; ?></td></tr>
</table>
<?php endif; ?>

</body>
</html>