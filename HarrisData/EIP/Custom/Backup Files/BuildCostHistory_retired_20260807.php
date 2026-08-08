<?php
// BuildCostHistory.php
// ONE-TIME build of the item cost history table and its nightly capture
// procedure. Follows the existing Fix*.php pattern: dry-run by default,
// writes only when ?go=1 is supplied.
//
// Creates in SGOBJ:
//   SGCSTHST   change-only cost history (one row per item/cost set per
//              distinct set of cost values, with the date range it applied)
//   SGCSTHSTL1 index supporting current-row lookups
//   SGCSTCAP   stored procedure -- the nightly capture, called from CL
//
// Seeds from the SEQUEL snapshots, compressing them into change rows:
//   SEQUELDBF/HDMCMMDLY1  cost set 1 Standard, 2025-01-01 .. current
//   SEQUELDBF/HDMCMMDLY2  cost set 2 Current,  2025-08-18 .. current
//   SEQUELDBF/HDMCMMDLY   redundant subset of DLY1 -- deliberately skipped
//
// Backfill is limited to $SEED_FROM (12 months) rather than the full 562 days
// of snapshot. The first row for each item is therefore a BASELINE, not an
// observed change -- it records what the cost already was at the cutoff. Those
// rows are tagged CHSRC='B' so the inquiry can label them honestly instead of
// implying every item changed cost on the same day.
//
// This truncates the SEED only. Nothing purges afterwards, so history keeps
// accumulating from the cutoff forward -- it is a fixed 12-month floor, not a
// rolling window.
//
// Cost set 3 has no snapshot history and starts from the first capture run.
// Plant 1 only; CMPLT stays in the key so a future plant needs no redesign.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/Inventory%20Management/BuildCostHistory.php
//      add ?go=1 to actually execute.

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(1800);

$GO    = isset($_GET['go']) && $_GET['go'] === '1';
$PLANT = 1;
$LIB   = 'SGOBJ';
$TBL   = 'SGCSTHST';

// Backfill floor. Twelve months back from the 2026-08-07 build date.
// Cost set 2's snapshot only starts 2025-08-18, so in practice the two sets
// begin within eleven days of each other -- close enough that the inquiry
// will not show one set with months more history than the other.
// Change this and rebuild if a different depth is wanted.
$SEED_FROM = '2025-08-07';

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

// ---------------------------------------------------------------------------
// Column plumbing. Fifteen cost columns map CMxxx (source) -> CHxxx (target).
// Generated rather than typed out, so the lists can never drift apart.
// ---------------------------------------------------------------------------

$COST = array('UCC1','UCC2','UCC3','UCC4','UCC5',
              'LCC1','LCC2','LCC3','LCC4','LCC5',
              'BSC1','BSC2','BSC3','BSC4','BSC5');

$SRC_COST = array_map(function ($c) { return "CM$c"; }, $COST);   // CMUCC1 ...
$TGT_COST = array_map(function ($c) { return "CH$c"; }, $COST);   // CHUCC1 ...

// Equality test between a live HDMCMM row (m) and a history row (h).
$MATCH = implode(" AND ", array_map(
    function ($c) { return "m.CM$c = h.CH$c"; }, $COST));

// Change signature over the snapshot files. DIGITS() renders packed decimal as
// fixed-width characters -- ancient, fast, and safe on every IBM i release.
$SIG = implode("||", array_map(function ($c) { return "DIGITS(CM$c)"; }, $COST));

// Unit cost total, precomputed so the inquiry never has to sum at read time.
$TOTU_SRC = "CMUCC1+CMUCC2+CMUCC3+CMUCC4+CMUCC5";

$TGT_COLS = implode(",", $TGT_COST);
$SRC_COLS = implode(",", $SRC_COST);

// ---------------------------------------------------------------------------
// Statements. Each is [label, sql, note]. Executed in order when ?go=1.
// ---------------------------------------------------------------------------

$steps = array();

// --- 1. table -------------------------------------------------------------
$costDdl = implode(",\n  ", array_map(
    function ($c) { return str_pad("CH$c", 8) . " DECIMAL(12,5) NOT NULL DEFAULT 0"; }, $COST));

$steps[] = array(
    'label' => "CREATE TABLE $LIB.$TBL",
    'note'  => 'One row per plant/cost set/item per distinct cost state. '
             . 'CHENDD 9999-12-31 marks the row currently in effect.',
    'sql'   => "
CREATE TABLE $LIB.$TBL (
  CHPLT    NUMERIC(3,0)  NOT NULL,
  CHCSET   NUMERIC(2,0)  NOT NULL,
  CHPN     CHAR(15)      NOT NULL,
  CHEFFD   DATE          NOT NULL,
  CHENDD   DATE          NOT NULL DEFAULT '9999-12-31',
  $costDdl,
  CHTOTU   DECIMAL(13,5) NOT NULL DEFAULT 0,
  CHRUDT   NUMERIC(7,0)  NOT NULL DEFAULT 0,
  CHEFDT   NUMERIC(7,0)  NOT NULL DEFAULT 0,
  CHDTMT   NUMERIC(7,0)  NOT NULL DEFAULT 0,
  CHCEFL   CHAR(1)       NOT NULL DEFAULT ' ',
  CHSRC    CHAR(1)       NOT NULL DEFAULT 'N',
  CHCRTS   TIMESTAMP     NOT NULL DEFAULT CURRENT TIMESTAMP,
  PRIMARY KEY (CHPLT, CHCSET, CHPN, CHEFFD)
)");

$steps[] = array(
    'label' => "LABEL ON $LIB.$TBL",
    'note'  => 'Object text, so it is identifiable from WRKOBJ.',
    'sql'   => "LABEL ON TABLE $LIB.$TBL IS 'Item Cost History - change only'");

$steps[] = array(
    'label' => "CREATE INDEX $LIB.SGCSTHSTL1",
    'note'  => 'Supports the current-row probe the nightly capture does 69,026 '
             . 'times, and the as-of-date lookup the inquiry does.',
    'sql'   => "CREATE INDEX $LIB.SGCSTHSTL1
                ON $LIB.$TBL (CHPLT, CHCSET, CHPN, CHENDD)");

$steps[] = array(
    'label' => "CREATE INDEX $LIB.SGCSTHSTL2",
    'note'  => 'Supports \"what changed on date X\" across all items.',
    'sql'   => "CREATE INDEX $LIB.SGCSTHSTL2
                ON $LIB.$TBL (CHEFFD, CHPLT, CHCSET)");

// --- 2. seed from the snapshots ------------------------------------------
// Per key, walk the snapshot days in order, keep only the days where the cost
// vector differs from the previous day, then derive CHENDD from the next kept
// day. ROW_NUMBER guards against SEQUEL having written a key twice in a day.

function seedSql($file, $lib, $tbl, $plant, $tgtCols, $srcCols, $sig, $totu, $seedFrom) {
    return "
INSERT INTO $lib.$tbl
  (CHPLT, CHCSET, CHPN, CHEFFD, CHENDD, $tgtCols,
   CHTOTU, CHRUDT, CHEFDT, CHDTMT, CHCEFL, CHSRC)
WITH dedup AS (
  SELECT CMPLT, CMCSET, CMPN, DERIVED_01, $srcCols,
         CMRUDT, CMEFDT, CMDTMT, CMCEFL,
         ROW_NUMBER() OVER (PARTITION BY CMPLT, CMCSET, CMPN, DERIVED_01
                            ORDER BY DERIVED_01) AS RN
    FROM SEQUELDBF.$file
   WHERE CMPLT = $plant
     AND DERIVED_01 >= DATE('$seedFrom')
),
tagged AS (
  SELECT d.*, $sig AS SIG,
         LAG($sig) OVER (PARTITION BY CMPLT, CMCSET, CMPN
                         ORDER BY DERIVED_01) AS PSIG
    FROM dedup d
   WHERE RN = 1
),
changes AS (
  SELECT * FROM tagged WHERE PSIG IS NULL OR SIG <> PSIG
),
ranged AS (
  SELECT c.*,
         LEAD(DERIVED_01) OVER (PARTITION BY CMPLT, CMCSET, CMPN
                                ORDER BY DERIVED_01) AS NEXTD
    FROM changes c
)
SELECT CMPLT, CMCSET, CMPN,
       DERIVED_01,
       COALESCE(NEXTD - 1 DAY, DATE('9999-12-31')),
       $srcCols,
       $totu, CMRUDT, CMEFDT, CMDTMT, CMCEFL,
       CASE WHEN PSIG IS NULL THEN 'B' ELSE 'S' END
  FROM ranged";
}

$steps[] = array(
    'label' => 'SEED from HDMCMMDLY1 (cost set 1, Standard)',
    'note'  => "Snapshot days from $SEED_FROM forward. One baseline row per item "
             . "(CHSRC='B') plus one row per observed change (CHSRC='S').",
    'sql'   => seedSql('HDMCMMDLY1', $LIB, $TBL, $PLANT, $TGT_COLS, $SRC_COLS,
                       $SIG, $TOTU_SRC, $SEED_FROM));

$steps[] = array(
    'label' => 'SEED from HDMCMMDLY2 (cost set 2, Current)',
    'note'  => "Snapshot days from $SEED_FROM forward. This file itself only "
             . "starts 2025-08-18, so its baseline lands there.",
    'sql'   => seedSql('HDMCMMDLY2', $LIB, $TBL, $PLANT, $TGT_COLS, $SRC_COLS,
                       $SIG, $TOTU_SRC, $SEED_FROM));

// --- 3. the nightly capture procedure ------------------------------------
// Four statements, in this order, so that running twice in one day is a no-op
// rather than a primary-key violation:
//   0  drop a row opened earlier today that no longer matches live
//   0b reopen a row closed earlier today if live matches it again
//   1  close open rows whose values no longer match live
//   2  open a row for any key without one (changed items and new items)

$procSql = "
CREATE OR REPLACE PROCEDURE SGPGM.SGCSTCAP (
    IN  P_PLANT   DECIMAL(3,0),
    OUT P_CLOSED  INTEGER,
    OUT P_OPENED  INTEGER )
  LANGUAGE SQL
  MODIFIES SQL DATA
  SET OPTION COMMIT = *NONE, DATFMT = *ISO
BEGIN
  DECLARE V_RUN DATE;
  SET V_RUN = CURRENT DATE;
  SET P_CLOSED = 0;
  SET P_OPENED = 0;

  -- 0. A row opened earlier today that live no longer matches is an
  --    intraday intermediate state. History is daily grain, so drop it.
  DELETE FROM $LIB.$TBL h
   WHERE h.CHPLT  = P_PLANT
     AND h.CHENDD = DATE('9999-12-31')
     AND h.CHEFFD = V_RUN
     AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                      WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                        AND m.CMPN  = h.CHPN  AND $MATCH);

  -- 0b. If the cost reverted to what it was before today, reopen the row we
  --     closed earlier today rather than leaving an artificial split.
  UPDATE $LIB.$TBL h
     SET CHENDD = DATE('9999-12-31')
   WHERE h.CHPLT  = P_PLANT
     AND h.CHENDD = V_RUN - 1 DAY
     AND NOT EXISTS (SELECT 1 FROM $LIB.$TBL o
                      WHERE o.CHPLT = h.CHPLT AND o.CHCSET = h.CHCSET
                        AND o.CHPN  = h.CHPN  AND o.CHENDD = DATE('9999-12-31'))
     AND EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                  WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                    AND m.CMPN  = h.CHPN  AND $MATCH);

  -- 1. Close every open row the live master no longer agrees with. Also
  --    closes items deleted from HDMCMM, which is the correct behaviour.
  UPDATE $LIB.$TBL h
     SET CHENDD = CASE WHEN V_RUN - 1 DAY < h.CHEFFD
                       THEN h.CHEFFD ELSE V_RUN - 1 DAY END
   WHERE h.CHPLT  = P_PLANT
     AND h.CHENDD = DATE('9999-12-31')
     AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                      WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                        AND m.CMPN  = h.CHPN  AND $MATCH);
  GET DIAGNOSTICS P_CLOSED = ROW_COUNT;

  -- 2. Open a row for anything now lacking one: values that just changed,
  --    plus items and cost sets seen for the first time.
  INSERT INTO $LIB.$TBL
    (CHPLT, CHCSET, CHPN, CHEFFD, CHENDD, $TGT_COLS,
     CHTOTU, CHRUDT, CHEFDT, CHDTMT, CHCEFL, CHSRC)
  SELECT m.CMPLT, m.CMCSET, m.CMPN, V_RUN, DATE('9999-12-31'), $SRC_COLS,
         $TOTU_SRC, m.CMRUDT, m.CMEFDT, m.CMDTMT, m.CMCEFL, 'N'
    FROM SGHDSDATA.HDMCMM m
   WHERE m.CMPLT = P_PLANT
     AND NOT EXISTS (SELECT 1 FROM $LIB.$TBL h
                      WHERE h.CHPLT = m.CMPLT AND h.CHCSET = m.CMCSET
                        AND h.CHPN  = m.CMPN  AND h.CHENDD = DATE('9999-12-31'));
  GET DIAGNOSTICS P_OPENED = ROW_COUNT;
END";

$steps[] = array(
    'label' => "CREATE PROCEDURE SGPGM.SGCSTCAP",
    'note'  => 'The nightly capture. Idempotent -- a second run the same day '
             . 'finds nothing to do, so a retry after a failure is safe.',
    'sql'   => $procSql);

// ---------------------------------------------------------------------------
// Pre-flight: what exists already?
// ---------------------------------------------------------------------------

function qone($conn, $sql, &$err = null) {
    $err = null;
    $s = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$s) { $err = db2_stmt_errormsg(); return null; }
    $r = db2_fetch_assoc($s);
    db2_free_stmt($s);
    return $r ? $r : null;
}
function h($v) { return htmlspecialchars(trim((string)$v)); }
function nf($v) { return $v === null || $v === '' ? '' : number_format((float)$v); }

$exists = qone($conn,
    "SELECT COUNT(*) AS N FROM QSYS2.SYSTABLES
      WHERE TABLE_SCHEMA = '$LIB' AND TABLE_NAME = '$TBL'", $exErr);
$tableExists = $exists && (int)$exists['N'] > 0;

$existingRows = null;
if ($tableExists) {
    $er = qone($conn, "SELECT COUNT(*) AS N FROM $LIB.$TBL", $erErr);
    $existingRows = $er ? $er['N'] : null;
}

$libOk = qone($conn,
    "SELECT COUNT(*) AS N FROM QSYS2.SYSSCHEMAS WHERE SCHEMA_NAME = '$LIB'", $libErr);
$libExists = $libOk && (int)$libOk['N'] > 0;

// ---------------------------------------------------------------------------
// Execute
// ---------------------------------------------------------------------------

$results = array();
$aborted = false;

if ($GO && $libExists && !$tableExists) {
    foreach ($steps as $st) {
        $t0 = microtime(true);
        $ok = @db2_exec($conn, $st['sql']);
        $secs = microtime(true) - $t0;
        if (!$ok) {
            $results[] = array($st['label'], $secs, 'FAILED', db2_stmt_errormsg());
            $aborted = true;
            break;
        }
        $n = @db2_num_rows($ok);
        $results[] = array($st['label'], $secs, 'OK',
                           $n > 0 ? number_format($n) . ' rows' : '');
        @db2_free_stmt($ok);
    }
}

// Post-build verification
$verify = array();
if ($GO && !$aborted && count($results)) {
    $verify['total'] = qone($conn, "SELECT COUNT(*) AS N FROM $LIB.$TBL");
    $verify['bySet'] = array();
    $s = @db2_exec($conn,
        "SELECT CHCSET, COUNT(*) AS NROWS, COUNT(DISTINCT CHPN) AS NITEMS,
                SUM(CASE WHEN CHSRC = 'B' THEN 1 ELSE 0 END) AS NBASE,
                SUM(CASE WHEN CHSRC = 'S' THEN 1 ELSE 0 END) AS NCHG,
                MIN(CHEFFD) AS DMIN, MAX(CHEFFD) AS DMAX,
                SUM(CASE WHEN CHENDD = '9999-12-31' THEN 1 ELSE 0 END) AS NOPEN
           FROM $LIB.$TBL GROUP BY CHCSET ORDER BY CHCSET",
        array('cursor' => DB2_SCROLLABLE));
    if ($s) { while ($r = db2_fetch_assoc($s)) $verify['bySet'][] = $r; db2_free_stmt($s); }
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Build Cost History - SGOBJ.SGCSTHST</title>
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
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:10px 14px; margin-bottom:10px; font-size:12px; }
.warn { background:#fff8e1; border:2px solid #ffc107; border-radius:5px;
        padding:12px 16px; margin-bottom:12px; font-size:13px; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px; font-family:monospace; }
table.full { border-collapse:collapse; width:100%; background:#fff;
             border-radius:4px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.08);
             margin-bottom:14px; }
table.full th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
                padding:5px 8px; text-align:left; font-size:11px; white-space:nowrap; }
table.full td { padding:4px 8px; font-size:11px; border-bottom:1px solid #f0f0f0;
                vertical-align:top; }
td.num { text-align:right; font-family:monospace; white-space:nowrap; }
pre { background:#1e1e1e; color:#d4d4d4; padding:10px 14px; border-radius:4px;
      font-size:11px; overflow-x:auto; margin-bottom:12px; line-height:1.45; }
.go { display:inline-block; background:#c62828; color:#fff !important; padding:9px 20px;
      border-radius:4px; text-decoration:none; font-weight:bold; font-size:13px; }
.stepnote { color:#555; font-size:11px; font-style:italic; }
</style>
</head>
<body>

<div class="hdr">Build Cost History &mdash; SGOBJ.SGCSTHST</div>

<div class="info">
  Run: <?php echo date('Y-m-d H:i:s'); ?> &nbsp;|&nbsp;
  Mode: <strong><?php echo $GO ? 'EXECUTE' : 'DRY RUN'; ?></strong> &nbsp;|&nbsp;
  Plant <?php echo $PLANT; ?>, cost sets 1/2/3 &nbsp;|&nbsp;
  Backfill from <strong><?php echo h($SEED_FROM); ?></strong> (12 months)
</div>
<div class="warn">
  <strong>One box, one SGOBJ.</strong> SG5 and Live share this machine, so the
  objects built here are the real ones &mdash; there is no separate test copy.
  The procedure reads live <code>SGHDSDATA/HDMCMM</code>. Nothing is written to
  SGHDSDATA or SEQUELDBF, and the dry run below is the review step.
</div>

<!-- pre-flight ------------------------------------------------------------->
<div class="sect">Pre-flight</div>
<?php if (!$libExists): ?>
  <div class="bad">Library <?php echo h($LIB); ?> not found. Create it first:
    <code>CRTLIB LIB(<?php echo h($LIB); ?>)</code></div>
<?php elseif ($tableExists): ?>
  <div class="bad">
    <?php echo h("$LIB.$TBL"); ?> already exists
    (<?php echo nf($existingRows); ?> rows). This script only builds from
    scratch and will not touch it. To rebuild, drop it first:<br>
    <code>DROP TABLE <?php echo h("$LIB.$TBL"); ?></code>
  </div>
<?php else: ?>
  <div class="ok">Library <?php echo h($LIB); ?> present.
    <?php echo h($TBL); ?> does not exist yet &mdash; ready to build.</div>
<?php endif; ?>

<!-- what it will do -------------------------------------------------------->
<?php if (!$GO): ?>
<div class="sect">What this will do (<?php echo count($steps); ?> statements)</div>
<div class="info">
  Nothing below has been executed. Review, then use the button at the bottom.
</div>
<?php foreach ($steps as $i => $st): ?>
  <div><strong><?php echo ($i + 1) . '. ' . h($st['label']); ?></strong></div>
  <div class="stepnote"><?php echo h($st['note']); ?></div>
  <pre><?php echo h($st['sql']); ?></pre>
<?php endforeach; ?>
<?php endif; ?>

<!-- results ---------------------------------------------------------------->
<?php if ($GO && count($results)): ?>
<div class="sect">Execution</div>
<table class="full">
  <tr><th>#</th><th>Statement</th><th class="num">Seconds</th><th>Result</th><th>Detail</th></tr>
  <?php foreach ($results as $i => $r): ?>
  <tr>
    <td><?php echo $i + 1; ?></td>
    <td><?php echo h($r[0]); ?></td>
    <td class="num"><?php echo number_format($r[1], 2); ?></td>
    <td style="color:<?php echo $r[2] === 'OK' ? '#2e7d32' : '#c62828'; ?>;font-weight:bold">
        <?php echo h($r[2]); ?></td>
    <td style="font-family:monospace"><?php echo h($r[3]); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php if ($aborted): ?>
  <div class="bad">Aborted at the failing statement. Nothing after it ran.
    Drop <?php echo h("$LIB.$TBL"); ?> and re-run once the cause is fixed.</div>
<?php endif; ?>
<?php endif; ?>

<!-- verification ----------------------------------------------------------->
<?php if ($GO && !$aborted && count($verify)): ?>
<div class="sect">Verification</div>
<div class="ok">
  <?php echo nf($verify['total']['N']); ?> history rows built, replacing
  20,016,139 snapshot rows.
</div>
<table class="full">
  <tr><th>Cost Set</th><th class="num">History rows</th><th class="num">Items</th>
      <th class="num">Baseline (B)</th><th class="num">Changes (S)</th>
      <th>Earliest</th><th>Latest</th><th class="num">Currently open</th></tr>
  <?php foreach ($verify['bySet'] as $v): ?>
  <tr>
    <td><strong><?php echo h($v['CHCSET']); ?></strong></td>
    <td class="num"><?php echo nf($v['NROWS']); ?></td>
    <td class="num"><?php echo nf($v['NITEMS']); ?></td>
    <td class="num"><?php echo nf($v['NBASE']); ?></td>
    <td class="num"><?php echo nf($v['NCHG']); ?></td>
    <td><?php echo h($v['DMIN']); ?></td>
    <td><?php echo h($v['DMAX']); ?></td>
    <td class="num"><?php echo nf($v['NOPEN']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<div class="info">
  <strong>Baseline vs change.</strong> Rows tagged <code>B</code> record what a
  cost already was on <?php echo h($SEED_FROM); ?> &mdash; the backfill floor.
  They are not changes, and the inquiry labels them as the opening value rather
  than pretending every item was repriced that day. Rows tagged <code>S</code>
  are real observed changes; <code>N</code> rows come from the nightly capture.
</div>
<div class="info">
  <strong>Next:</strong> cost set 3 will not appear above &mdash; it has no
  snapshot history. It gets its first rows when the capture procedure runs for
  the first time, which also brings sets 1 and 2 forward from the last snapshot
  day to today. Run it once by hand to confirm, then schedule it:
  <pre>CALL SGOBJ.SGCSTCAP(1, ?, ?)</pre>
</div>
<?php endif; ?>

<!-- the button ------------------------------------------------------------->
<?php if (!$GO && $libExists && !$tableExists): ?>
<div class="sect">Execute</div>
<div class="warn">
  This creates objects in <?php echo h($LIB); ?> &mdash; the real library, shared
  by SG5 and Live &mdash; and inserts twelve months of history from
  <?php echo h($SEED_FROM); ?>. It reads SGHDSDATA and SEQUELDBF but
  <strong>writes nothing to either</strong>. Expect the two seed statements to
  take the longest.
  <br><br>
  <a class="go" href="?go=1">Build SGOBJ.SGCSTHST now</a>
</div>
<?php endif; ?>

</body>
</html>