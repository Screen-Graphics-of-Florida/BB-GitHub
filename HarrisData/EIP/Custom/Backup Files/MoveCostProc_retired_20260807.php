<?php
// MoveCostProc.php
// Moves the cost capture procedure out of SGOBJ and into SGPGM.
//
// CREATE PROCEDURE ... LANGUAGE SQL creates a *PGM object. The original build
// created it as SGOBJ.SGCSTCAP, which put a program in the database-object
// library. Convention here is:
//     SGOBJ  database objects   (SGCSTHST stays)
//     SGSRC  source
//     SGPGM  compiled programs  (SGCSTCAP belongs here)
//
// Recreates the identical procedure as SGPGM.SGCSTCAP, verifies it, then drops
// SGOBJ.SGCSTCAP. The table SGOBJ.SGCSTHST is not touched -- no data moves and
// no history is at risk.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/Inventory%20Management/MoveCostProc.php
//      add ?go=1 to execute.

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

$GO      = isset($_GET['go']) && $_GET['go'] === '1';
$TBLLIB  = 'SGOBJ';    // where the history table lives -- unchanged
$TBL     = 'SGCSTHST';
$OLDLIB  = 'SGOBJ';    // where the procedure wrongly lives
$NEWLIB  = 'SGPGM';    // where it belongs
$PROC    = 'SGCSTCAP';

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

// ---------- rebuild the procedure text, identical except for the library ---

$COST = array('UCC1','UCC2','UCC3','UCC4','UCC5','LCC1','LCC2','LCC3','LCC4','LCC5',
              'BSC1','BSC2','BSC3','BSC4','BSC5');
$MATCH    = implode(" AND ", array_map(function ($c) { return "m.CM$c = h.CH$c"; }, $COST));
$TGT_COLS = implode(",", array_map(function ($c) { return "CH$c"; }, $COST));
$SRC_COLS = implode(",", array_map(function ($c) { return "CM$c"; }, $COST));
$TOTU_SRC = "CMUCC1+CMUCC2+CMUCC3+CMUCC4+CMUCC5";

$procSql = "
CREATE OR REPLACE PROCEDURE $NEWLIB.$PROC (
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

  DELETE FROM $TBLLIB.$TBL h
   WHERE h.CHPLT  = P_PLANT
     AND h.CHENDD = DATE('9999-12-31')
     AND h.CHEFFD = V_RUN
     AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                      WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                        AND m.CMPN  = h.CHPN  AND $MATCH);

  UPDATE $TBLLIB.$TBL h
     SET CHENDD = DATE('9999-12-31')
   WHERE h.CHPLT  = P_PLANT
     AND h.CHENDD = V_RUN - 1 DAY
     AND NOT EXISTS (SELECT 1 FROM $TBLLIB.$TBL o
                      WHERE o.CHPLT = h.CHPLT AND o.CHCSET = h.CHCSET
                        AND o.CHPN  = h.CHPN  AND o.CHENDD = DATE('9999-12-31'))
     AND EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                  WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                    AND m.CMPN  = h.CHPN  AND $MATCH);

  UPDATE $TBLLIB.$TBL h
     SET CHENDD = CASE WHEN V_RUN - 1 DAY < h.CHEFFD
                       THEN h.CHEFFD ELSE V_RUN - 1 DAY END
   WHERE h.CHPLT  = P_PLANT
     AND h.CHENDD = DATE('9999-12-31')
     AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                      WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                        AND m.CMPN  = h.CHPN  AND $MATCH);
  GET DIAGNOSTICS P_CLOSED = ROW_COUNT;

  INSERT INTO $TBLLIB.$TBL
    (CHPLT, CHCSET, CHPN, CHEFFD, CHENDD, $TGT_COLS,
     CHTOTU, CHRUDT, CHEFDT, CHDTMT, CHCEFL, CHSRC)
  SELECT m.CMPLT, m.CMCSET, m.CMPN, V_RUN, DATE('9999-12-31'), $SRC_COLS,
         $TOTU_SRC, m.CMRUDT, m.CMEFDT, m.CMDTMT, m.CMCEFL, 'N'
    FROM SGHDSDATA.HDMCMM m
   WHERE m.CMPLT = P_PLANT
     AND NOT EXISTS (SELECT 1 FROM $TBLLIB.$TBL h
                      WHERE h.CHPLT = m.CMPLT AND h.CHCSET = m.CMCSET
                        AND h.CHPN  = m.CMPN  AND h.CHENDD = DATE('9999-12-31'));
  GET DIAGNOSTICS P_OPENED = ROW_COUNT;
END";

$dropSql = "DROP PROCEDURE $OLDLIB.$PROC";

// ---------- current state -------------------------------------------------

function procWhere($conn, $lib, $proc) {
    $r = qone($conn,
        "SELECT ROUTINE_NAME, ROUTINE_TYPE FROM QSYS2.SYSROUTINES
          WHERE ROUTINE_SCHEMA = '$lib' AND ROUTINE_NAME = '$proc'", $e);
    return $r !== null;
}

$inOld = procWhere($conn, $OLDLIB, $PROC);
$inNew = procWhere($conn, $NEWLIB, $PROC);

$libNew = qone($conn,
    "SELECT COUNT(*) AS N FROM QSYS2.SYSSCHEMAS WHERE SCHEMA_NAME = '$NEWLIB'", $lnErr);
$newLibExists = $libNew && (int)$libNew['N'] > 0;

$rowsBefore = qone($conn, "SELECT COUNT(*) AS N FROM $TBLLIB.$TBL", $rbErr);

// ---------- execute -------------------------------------------------------

$steps = array();
if ($GO && $newLibExists) {
    // 1. create in the right place
    $t0 = microtime(true);
    $ok = @db2_exec($conn, $procSql);
    $steps[] = array("CREATE OR REPLACE PROCEDURE $NEWLIB.$PROC",
                     microtime(true) - $t0,
                     $ok ? 'OK' : 'FAILED',
                     $ok ? '' : db2_stmt_errormsg());
    if ($ok) @db2_free_stmt($ok);

    // 2. prove it works before removing the old one -- a no-op call, because
    //    the capture already ran today and the procedure is idempotent.
    $callOk = false; $cClosed = null; $cOpened = null; $callMsg = '';
    if ($ok) {
        $t0 = microtime(true);
        $stmt = @db2_prepare($conn, "CALL $NEWLIB.$PROC(?, ?, ?)");
        if ($stmt) {
            $p1 = 1; $p2 = 0; $p3 = 0;
            @db2_bind_param($stmt, 1, 'p1', DB2_PARAM_IN,  DB2_LONG);
            @db2_bind_param($stmt, 2, 'p2', DB2_PARAM_OUT, DB2_LONG);
            @db2_bind_param($stmt, 3, 'p3', DB2_PARAM_OUT, DB2_LONG);
            if (@db2_execute($stmt)) {
                $callOk = true; $cClosed = $p2; $cOpened = $p3;
                $callMsg = "closed $p2, opened $p3";
            } else { $callMsg = db2_stmt_errormsg(); }
            @db2_free_stmt($stmt);
        } else { $callMsg = db2_stmt_errormsg(); }
        $steps[] = array("Smoke test: CALL $NEWLIB.$PROC(1, ?, ?)",
                         microtime(true) - $t0,
                         $callOk ? 'OK' : 'FAILED', $callMsg);
    }

    // 3. only now remove the misplaced original
    if ($callOk) {
        $t0 = microtime(true);
        $d = @db2_exec($conn, $dropSql);
        $steps[] = array("DROP PROCEDURE $OLDLIB.$PROC",
                         microtime(true) - $t0,
                         $d ? 'OK' : 'FAILED',
                         $d ? '' : db2_stmt_errormsg());
        if ($d) @db2_free_stmt($d);
    } else {
        $steps[] = array("DROP PROCEDURE $OLDLIB.$PROC", 0, 'SKIPPED',
                         'Smoke test did not pass -- original left in place.');
    }

    $inOld = procWhere($conn, $OLDLIB, $PROC);
    $inNew = procWhere($conn, $NEWLIB, $PROC);
}

$rowsAfter = qone($conn, "SELECT COUNT(*) AS N FROM $TBLLIB.$TBL", $raErr);
db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Move Cost Capture Procedure to SGPGM</title>
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
table.full { border-collapse:collapse; width:100%; background:#fff; border-radius:4px;
             overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
table.full th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
                padding:5px 8px; text-align:left; font-size:11px; }
table.full td { padding:4px 8px; font-size:11px; border-bottom:1px solid #f0f0f0; }
td.num { text-align:right; font-family:monospace; }
pre { background:#1e1e1e; color:#d4d4d4; padding:10px 14px; border-radius:4px;
      font-size:11px; overflow-x:auto; margin-bottom:12px; line-height:1.45; }
.go { display:inline-block; background:#c62828; color:#fff !important; padding:9px 20px;
      border-radius:4px; text-decoration:none; font-weight:bold; font-size:13px; }
.pass { color:#2e7d32; font-weight:bold; } .fail { color:#c62828; font-weight:bold; }
</style>
</head>
<body>

<div class="hdr">Move Cost Capture Procedure &rarr; SGPGM</div>

<div class="info">
  Run: <?php echo date('Y-m-d H:i:s'); ?> &nbsp;|&nbsp;
  Mode: <strong><?php echo $GO ? 'EXECUTE' : 'DRY RUN'; ?></strong><br>
  <code>CREATE PROCEDURE</code> builds a <code>*PGM</code> object, so
  <code>SGCSTCAP</code> was created into the database library by mistake.
  <strong>SGOBJ.SGCSTHST is not touched</strong> &mdash; no data moves.
</div>

<div class="sect">Where the procedure lives now</div>
<table class="full">
  <tr><th>Library</th><th>Role</th><th>SGCSTCAP present?</th></tr>
  <tr><td><?php echo h($OLDLIB); ?></td><td>Database objects</td>
      <td class="<?php echo $inOld ? 'fail' : 'pass'; ?>">
        <?php echo $inOld ? 'YES - wrong library' : 'no'; ?></td></tr>
  <tr><td><?php echo h($NEWLIB); ?></td><td>Compiled programs</td>
      <td class="<?php echo $inNew ? 'pass' : 'fail'; ?>">
        <?php echo $inNew ? 'YES - correct' : 'no'; ?></td></tr>
  <tr><td><?php echo h($TBLLIB . '.' . $TBL); ?></td><td>History table</td>
      <td><?php echo $rowsAfter ? nf($rowsAfter['N']) . ' rows' : h($raErr); ?></td></tr>
</table>
<?php if (!$newLibExists): ?>
  <div class="big bigbad">Library <?php echo h($NEWLIB); ?> not found.</div>
<?php endif; ?>

<?php if (!$GO): ?>
<div class="sect">What will happen</div>
<div class="info">
  Three steps, in this order, so the working procedure is never removed before
  a replacement is proven: <strong>create in SGPGM</strong>, then
  <strong>smoke-test it</strong> with a real CALL, then
  <strong>drop the SGOBJ copy</strong> &mdash; and only if the smoke test
  passed. Today's smoke test should report closed 0, opened 0, since the
  capture already ran and the procedure is idempotent.
</div>
<pre><?php echo h($procSql); ?></pre>
<pre><?php echo h($dropSql); ?></pre>

<div class="sect">Execute</div>
<div class="warn">
  Creates a program in <?php echo h($NEWLIB); ?> and removes one from
  <?php echo h($OLDLIB); ?>. The history table and all
  <?php echo $rowsBefore ? nf($rowsBefore['N']) : ''; ?> rows are untouched.
  <br><br>
  <a class="go" href="?go=1">Move SGCSTCAP to SGPGM</a>
</div>
<?php else: ?>

<div class="sect">Execution</div>
<table class="full">
  <tr><th>Step</th><th class="num">Seconds</th><th>Result</th><th>Detail</th></tr>
  <?php foreach ($steps as $s): ?>
  <tr><td><?php echo h($s[0]); ?></td>
      <td class="num"><?php echo number_format($s[1], 2); ?></td>
      <td class="<?php echo $s[2] === 'OK' ? 'pass' : 'fail'; ?>"><?php echo h($s[2]); ?></td>
      <td style="font-family:monospace"><?php echo h($s[3]); ?></td></tr>
  <?php endforeach; ?>
</table>

<?php if ($inNew && !$inOld): ?>
  <div class="big bigok"><strong>Done.</strong>
    <code><?php echo h("$NEWLIB.$PROC"); ?></code> is in place and tested;
    the <?php echo h($OLDLIB); ?> copy is gone.
    The history table still holds
    <?php echo $rowsAfter ? nf($rowsAfter['N']) : '?'; ?> rows.
    <br><br>
    Update the CL to call the new location:
    <code>CALL PGM(SGPGM/SGCSTCAP) PARM(&amp;PLANT &amp;CLOSED &amp;OPENED)</code>
  </div>
<?php elseif ($inNew && $inOld): ?>
  <div class="big bigbad"><strong>Partly done.</strong>
    The new procedure exists but the old one is still there. Check the
    step detail above before calling either.</div>
<?php endif; ?>
<?php endif; ?>

</body>
</html>