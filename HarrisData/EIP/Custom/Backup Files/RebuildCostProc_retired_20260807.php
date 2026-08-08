<?php
// RebuildCostProc.php
// Rebuilds SGPGM.SGCSTCAP so it can be driven from CL, and adds a run log.
//
// WHY:
//   The CL used CALL PGM(SGPGM/SGCSTCAP), which fails with MCH3601
//   "Pointer not set for location referenced". An SQL procedure is a *PGM but
//   it expects the SQL runtime to build its parameter structure; a native CL
//   CALL leaves those pointers unset.
//
//   The supported way to drive it from CL is RUNSQL:
//       RUNSQL SQL('CALL SGPGM.SGCSTCAP(1)') COMMIT(*NONE)
//
//   RUNSQL cannot receive OUT parameters, so the procedure drops P_CLOSED and
//   P_OPENED and writes its results to SGOBJ.SGCSTLOG instead. That also fixes
//   the bigger problem: an unattended 01:00 job previously left no durable
//   record of whether it ran.
//
// WHAT IT DOES:
//   1 CREATE TABLE SGOBJ.SGCSTLOG        one row per capture run
//   2 CREATE OR REPLACE PROCEDURE SGPGM.SGCSTCAP (IN plant)  -- logs, no OUTs
//   3 GRTOBJAUT *PUBLIC *ALL on the log table
//   4 Smoke test: CALL through SQL, then show the log row it wrote
//
// The history table SGOBJ.SGCSTHST and its 163,199 rows are NOT touched.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/Inventory%20Management/RebuildCostProc.php
//      add ?go=1 to execute.

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(600);

$GO     = isset($_GET['go']) && $_GET['go'] === '1';
$TBLLIB = 'SGOBJ';
$TBL    = 'SGCSTHST';
$LOG    = 'SGCSTLOG';
$PGMLIB = 'SGPGM';
$PROC   = 'SGCSTCAP';

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

function rows($conn, $sql, &$err = null) {
    $err = null;
    $st = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$st) { $err = db2_stmt_errormsg(); return array(); }
    $o = array();
    while ($r = db2_fetch_assoc($st)) $o[] = $r;
    db2_free_stmt($st);
    return $o;
}
function one($conn, $sql, &$err = null) {
    $r = rows($conn, $sql, $err);
    return count($r) ? $r[0] : null;
}
function h($v)  { return htmlspecialchars(trim((string)$v)); }
function nf($v) { return $v === null || $v === '' ? '' : number_format((float)$v); }

$COST  = array('UCC1','UCC2','UCC3','UCC4','UCC5','LCC1','LCC2','LCC3','LCC4','LCC5',
               'BSC1','BSC2','BSC3','BSC4','BSC5');
$MATCH = implode(" AND ", array_map(function ($c) { return "m.CM$c = h.CH$c"; }, $COST));
$TGT   = implode(",", array_map(function ($c) { return "CH$c"; }, $COST));
$SRC   = implode(",", array_map(function ($c) { return "CM$c"; }, $COST));
$TOTU  = "CMUCC1+CMUCC2+CMUCC3+CMUCC4+CMUCC5";

// ---------------------------------------------------------------------------

$steps = array();

$steps[] = array(
  'label' => "CREATE TABLE $TBLLIB.$LOG",
  'note'  => 'One row per capture run, however it was triggered -- scheduler, '
           . 'CL by hand, or the browser page. This is what makes a silently '
           . 'dead nightly job visible.',
  'sql'   => "
CREATE TABLE $TBLLIB.$LOG (
  LGSTART  TIMESTAMP     NOT NULL DEFAULT CURRENT TIMESTAMP,
  LGEND    TIMESTAMP,
  LGRUND   DATE          NOT NULL,
  LGPLT    NUMERIC(3,0)  NOT NULL,
  LGCLOSED INTEGER       NOT NULL DEFAULT 0,
  LGOPENED INTEGER       NOT NULL DEFAULT 0,
  LGSTAT   CHAR(1)       NOT NULL DEFAULT 'O',
  LGMSG    VARCHAR(300)  NOT NULL DEFAULT '',
  LGUSER   VARCHAR(18)   NOT NULL DEFAULT '',
  PRIMARY KEY (LGSTART)
)");

$steps[] = array(
  'label' => "LABEL ON $TBLLIB.$LOG",
  'note'  => 'Object text.',
  'sql'   => "LABEL ON TABLE $TBLLIB.$LOG IS 'Item Cost History - capture run log'");

// The procedure: same four capture statements as before, now with no OUT
// parameters and a log row written at the end. An EXIT handler records a
// failure row so a crash is still visible in the log.
$steps[] = array(
  'label' => "CREATE OR REPLACE PROCEDURE $PGMLIB.$PROC (IN plant)",
  'note'  => 'OUT parameters removed so RUNSQL can drive it from CL. Results '
           . 'go to the log table. Still idempotent.',
  'sql'   => "
CREATE OR REPLACE PROCEDURE $PGMLIB.$PROC ( IN P_PLANT DECIMAL(3,0) )
  LANGUAGE SQL
  MODIFIES SQL DATA
  SET OPTION COMMIT = *NONE, DATFMT = *ISO
BEGIN
  DECLARE V_RUN    DATE;
  DECLARE V_START  TIMESTAMP;
  DECLARE V_CLOSED INTEGER DEFAULT 0;
  DECLARE V_OPENED INTEGER DEFAULT 0;
  DECLARE V_MSG    VARCHAR(300) DEFAULT '';

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    GET DIAGNOSTICS CONDITION 1 V_MSG = MESSAGE_TEXT;
    INSERT INTO $TBLLIB.$LOG
      (LGSTART, LGEND, LGRUND, LGPLT, LGCLOSED, LGOPENED, LGSTAT, LGMSG, LGUSER)
    VALUES (V_START, CURRENT TIMESTAMP, V_RUN, P_PLANT,
            V_CLOSED, V_OPENED, 'F', V_MSG, SESSION_USER);
  END;

  SET V_START = CURRENT TIMESTAMP;
  SET V_RUN   = CURRENT DATE;

  -- 0. Drop a row opened earlier today that live no longer matches.
  DELETE FROM $TBLLIB.$TBL h
   WHERE h.CHPLT  = P_PLANT
     AND h.CHENDD = DATE('9999-12-31')
     AND h.CHEFFD = V_RUN
     AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                      WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                        AND m.CMPN  = h.CHPN  AND $MATCH);

  -- 0b. Reopen a row closed earlier today if live matches it again.
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

  -- 1. Close open rows the live master no longer agrees with.
  UPDATE $TBLLIB.$TBL h
     SET CHENDD = CASE WHEN V_RUN - 1 DAY < h.CHEFFD
                       THEN h.CHEFFD ELSE V_RUN - 1 DAY END
   WHERE h.CHPLT  = P_PLANT
     AND h.CHENDD = DATE('9999-12-31')
     AND NOT EXISTS (SELECT 1 FROM SGHDSDATA.HDMCMM m
                      WHERE m.CMPLT = h.CHPLT AND m.CMCSET = h.CHCSET
                        AND m.CMPN  = h.CHPN  AND $MATCH);
  GET DIAGNOSTICS V_CLOSED = ROW_COUNT;

  -- 2. Open a row for anything now lacking one.
  INSERT INTO $TBLLIB.$TBL
    (CHPLT, CHCSET, CHPN, CHEFFD, CHENDD, $TGT,
     CHTOTU, CHRUDT, CHEFDT, CHDTMT, CHCEFL, CHSRC)
  SELECT m.CMPLT, m.CMCSET, m.CMPN, V_RUN, DATE('9999-12-31'), $SRC,
         $TOTU, m.CMRUDT, m.CMEFDT, m.CMDTMT, m.CMCEFL, 'N'
    FROM SGHDSDATA.HDMCMM m
   WHERE m.CMPLT = P_PLANT
     AND NOT EXISTS (SELECT 1 FROM $TBLLIB.$TBL h
                      WHERE h.CHPLT = m.CMPLT AND h.CHCSET = m.CMCSET
                        AND h.CHPN  = m.CMPN  AND h.CHENDD = DATE('9999-12-31'));
  GET DIAGNOSTICS V_OPENED = ROW_COUNT;

  -- 3. A brand new item's first row is an opening value, not a change.
  UPDATE $TBLLIB.$TBL h
     SET CHSRC = 'B'
   WHERE h.CHPLT = P_PLANT
     AND h.CHSRC = 'N'
     AND h.CHEFFD = (SELECT MIN(o.CHEFFD) FROM $TBLLIB.$TBL o
                      WHERE o.CHPLT  = h.CHPLT
                        AND o.CHCSET = h.CHCSET
                        AND o.CHPN   = h.CHPN);

  INSERT INTO $TBLLIB.$LOG
    (LGSTART, LGEND, LGRUND, LGPLT, LGCLOSED, LGOPENED, LGSTAT, LGMSG, LGUSER)
  VALUES (V_START, CURRENT TIMESTAMP, V_RUN, P_PLANT,
          V_CLOSED, V_OPENED, 'O', '', SESSION_USER);
END");

$steps[] = array(
  'label' => "GRTOBJAUT $TBLLIB/$LOG *PUBLIC *ALL",
  'note'  => 'The nightly job runs under the scheduler profile, not QTMHHTTP, '
           . 'so it must be able to write the log.',
  'sql'   => "CALL QSYS2.QCMDEXC('GRTOBJAUT OBJ($TBLLIB/$LOG) OBJTYPE(*FILE) "
           . "USER(*PUBLIC) AUT(*ALL)')");

// ---------------------------------------------------------------------------

$logExists = one($conn,
    "SELECT COUNT(*) AS N FROM QSYS2.SYSTABLES
      WHERE TABLE_SCHEMA = '$TBLLIB' AND TABLE_NAME = '$LOG'");
$haveLog = $logExists && (int)$logExists['N'] > 0;

$results = array(); $aborted = false; $smoke = null; $smokeErr = null;

if ($GO) {
    foreach ($steps as $st) {
        // Skip the CREATE TABLE if the log already exists, so this page can be
        // re-run safely after a partial failure.
        if ($haveLog && strpos($st['label'], 'CREATE TABLE') === 0) {
            $results[] = array($st['label'], 0, 'SKIPPED', 'already exists');
            continue;
        }
        $t0 = microtime(true);
        $ok = @db2_exec($conn, $st['sql']);
        if (!$ok) {
            $results[] = array($st['label'], microtime(true) - $t0, 'FAILED',
                               db2_stmt_errormsg());
            $aborted = true;
            break;
        }
        $results[] = array($st['label'], microtime(true) - $t0, 'OK', '');
        @db2_free_stmt($ok);
    }

    // Smoke test through SQL, exactly as RUNSQL will drive it.
    if (!$aborted) {
        $t0 = microtime(true);
        $ok = @db2_exec($conn, "CALL $PGMLIB.$PROC(1)");
        $smoke = array(microtime(true) - $t0, $ok ? 'OK' : 'FAILED',
                       $ok ? '' : db2_stmt_errormsg());
        if ($ok) @db2_free_stmt($ok);
    }
}

$runs = array();
if ($haveLog || ($GO && !$aborted)) {
    $runs = rows($conn,
        "SELECT LGSTART, LGEND, LGRUND, LGPLT, LGCLOSED, LGOPENED,
                LGSTAT, LGMSG, LGUSER
           FROM $TBLLIB.$LOG ORDER BY LGSTART DESC FETCH FIRST 20 ROWS ONLY", $runErr);
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Rebuild Cost Capture Procedure</title>
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
                padding:5px 8px; text-align:left; font-size:11px; }
table.full td { padding:4px 8px; font-size:11px; border-bottom:1px solid #f0f0f0; }
td.num { text-align:right; font-family:monospace; }
pre { background:#1e1e1e; color:#d4d4d4; padding:10px 14px; border-radius:4px;
      font-size:11px; overflow-x:auto; margin-bottom:12px; line-height:1.45; }
.go { display:inline-block; background:#c62828; color:#fff !important; padding:9px 20px;
      border-radius:4px; text-decoration:none; font-weight:bold; font-size:13px; }
.pass { color:#2e7d32; font-weight:bold; } .fail { color:#c62828; font-weight:bold; }
.stepnote { color:#555; font-size:11px; font-style:italic; margin-bottom:3px; }
</style>
</head>
<body>

<div class="hdr">Rebuild Cost Capture Procedure &mdash; CL-callable + run log</div>

<div class="info">
  Run: <?php echo date('Y-m-d H:i:s'); ?> &nbsp;|&nbsp;
  Mode: <strong><?php echo $GO ? 'EXECUTE' : 'DRY RUN'; ?></strong><br>
  <strong>Why:</strong> <code>CALL PGM(SGPGM/SGCSTCAP)</code> fails with MCH3601
  because an SQL procedure needs the SQL runtime to build its parameter
  structure. It must be driven with <code>RUNSQL</code>, which cannot take OUT
  parameters &mdash; so the procedure now writes its results to a log table
  instead.
  <br><br>
  <strong>SGOBJ.SGCSTHST and its 163,199 rows are not touched.</strong>
</div>

<?php if (!$GO): ?>
<div class="sect">Statements</div>
<?php foreach ($steps as $i => $st): ?>
  <div><strong><?php echo ($i + 1) . '. ' . h($st['label']); ?></strong></div>
  <div class="stepnote"><?php echo h($st['note']); ?></div>
  <pre><?php echo h($st['sql']); ?></pre>
<?php endforeach; ?>

<div class="sect">Execute</div>
<div class="warn">
  Creates <?php echo h("$TBLLIB.$LOG"); ?> and replaces
  <?php echo h("$PGMLIB.$PROC"); ?>. Then smoke-tests it with
  <code>CALL <?php echo h("$PGMLIB.$PROC"); ?>(1)</code>, which should report
  0 closed and 0 opened since the capture is already current.
  <br><br>
  <a class="go" href="?go=1">Rebuild procedure and add run log</a>
</div>
<?php else: ?>

<div class="sect">Execution</div>
<table class="full">
  <tr><th>Step</th><th class="num">Seconds</th><th>Result</th><th>Detail</th></tr>
  <?php foreach ($results as $r): ?>
  <tr><td><?php echo h($r[0]); ?></td>
      <td class="num"><?php echo number_format($r[1], 2); ?></td>
      <td class="<?php echo $r[2] === 'OK' ? 'pass' : ($r[2] === 'SKIPPED' ? '' : 'fail'); ?>">
        <?php echo h($r[2]); ?></td>
      <td style="font-family:monospace"><?php echo h($r[3]); ?></td></tr>
  <?php endforeach; ?>
  <?php if ($smoke): ?>
  <tr><td><strong>Smoke test: CALL <?php echo h("$PGMLIB.$PROC"); ?>(1)</strong></td>
      <td class="num"><?php echo number_format($smoke[0], 2); ?></td>
      <td class="<?php echo $smoke[1] === 'OK' ? 'pass' : 'fail'; ?>">
        <?php echo h($smoke[1]); ?></td>
      <td style="font-family:monospace"><?php echo h($smoke[2]); ?></td></tr>
  <?php endif; ?>
</table>

<?php if (!$aborted && $smoke && $smoke[1] === 'OK'): ?>
<div class="big bigok">
  <strong>Done.</strong> The procedure now runs under SQL and logs every run.
  Update the CL to use RUNSQL &mdash; see below &mdash; then recompile it.
</div>
<?php elseif ($aborted): ?>
<div class="big bigbad"><strong>Aborted.</strong> Nothing after the failing
  statement ran. The history table is unaffected.</div>
<?php endif; ?>
<?php endif; ?>

<div class="sect">Capture run log &mdash; <?php echo h("$TBLLIB.$LOG"); ?></div>
<?php if (!count($runs)): ?>
  <div class="info">No runs logged yet<?php
    if (isset($runError) && $runError) echo ' &mdash; ' . h($runError); ?>.</div>
<?php else: ?>
<table class="full">
  <tr><th>Started</th><th>Finished</th><th>Business date</th><th class="num">Plant</th>
      <th class="num">Closed</th><th class="num">Opened</th><th>Status</th>
      <th>User</th><th>Message</th></tr>
  <?php foreach ($runs as $r): ?>
  <tr>
    <td><?php echo h($r['LGSTART']); ?></td>
    <td><?php echo h($r['LGEND']); ?></td>
    <td><?php echo h($r['LGRUND']); ?></td>
    <td class="num"><?php echo h($r['LGPLT']); ?></td>
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

<div class="sect">Replacement CL &mdash; recompile after running this</div>
<div class="info">
  The CALL is replaced by RUNSQL. The program is much shorter now because the
  results live in the log table rather than in OUT parameters.
</div>
<pre>             PGM

             DCL        VAR(&amp;MSG) TYPE(*CHAR) LEN(132)

             MONMSG     MSGID(CPF0000 MCH0000) EXEC(GOTO CMDLBL(ERROR))

             RUNSQL     SQL('CALL SGPGM.SGCSTCAP(1)') COMMIT(*NONE)

             SNDPGMMSG  MSG('Cost history capture done - see SGOBJ/SGCSTLOG') +
                          TOPGMQ(*EXT) MSGTYPE(*INFO)

             RETURN

 ERROR:      SNDMSG     MSG('SGCSTCAPC FAILED - item cost history was +
                          NOT captured. Check SGOBJ/SGCSTLOG and the joblog.') +
                          TOUSR(*SYSOPR)
             MONMSG     MSGID(CPF0000)

             ENDPGM</pre>
<pre>CRTBNDCL   PGM(SGPGM/SGCSTCAPC) SRCFILE(SGSRC/QCLSRC) SRCMBR(SGCSTCAPC)
             TEXT('Nightly item cost history capture')

CALL       PGM(SGPGM/SGCSTCAPC)

ADDJOBSCDE JOB(SGCSTCAP) CMD(CALL PGM(SGPGM/SGCSTCAPC))
             FRQ(*WEEKLY) SCDDATE(*NONE) SCDDAY(*ALL) SCDTIME(010000)
             TEXT('Item cost history - daily capture')</pre>

</body>
</html>