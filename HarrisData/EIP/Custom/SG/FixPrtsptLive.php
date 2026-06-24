<?php
// ============================================================
// SG EIP — Fix PRTSPT on SYPORR rows
// All existing (BILL) SYPORR rows have PRTSPT='Y'.
// Our PUSHALL/PUSHOE rows have PRTSPT=''. EIP skips tabs
// where PRTSPT is blank. Fix: UPDATE SET PRTSPT='Y'.
//
// Diagnose: load with no params
// Backup + Fix: ?confirm=FIX
//
// URL:
//   https://portal.screen-graphics.com:5601/Custom/SG/FixPrtsptLive.php
//   https://portal.screen-graphics.com:5601/Custom/SG/FixPrtsptLive.php?confirm=FIX
// ============================================================

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('<pre>DB2 connect failed: ' . htmlspecialchars(db2_conn_errormsg()) . '</pre>');

$sgPorts   = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
$portList  = "'" . implode("','", $sgPorts) . "'";

// Count rows that need fixing
$cntStmt = db2_exec($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR "
  . "WHERE PRTSUS IN ('PUSHALL','PUSHOE') "
  . "AND PRPORT IN ($portList) "
  . "AND PRTSPT <> 'Y'");
$cntRow  = $cntStmt ? db2_fetch_array($cntStmt) : array(0);
$cntNeed = (int)$cntRow[0];

// Count already-correct rows (sanity check)
$cntOkStmt = db2_exec($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR "
  . "WHERE PRTSUS IN ('PUSHALL','PUSHOE') "
  . "AND PRPORT IN ($portList) "
  . "AND PRTSPT = 'Y'");
$cntOkRow = $cntOkStmt ? db2_fetch_array($cntOkStmt) : array(0);
$cntAlreadyOk = (int)$cntOkRow[0];

// Also check SYURLM entries exist
$syurlmStmt = db2_exec($conn,
    "SELECT FUID, FUTSPT FROM SGHDSDATA.SYURLM "
  . "WHERE FUID LIKE 'SG%' "
  . "AND (FUID LIKE '%/PORTAL' OR FUID LIKE '%_OE' OR FUID LIKE '%_MFG' "
  . "  OR FUID LIKE '%_ACCT' OR FUID LIKE '%_PLN' OR FUID LIKE '%_PUR' "
  . "  OR FUID LIKE '%_INVMGMT') "
  . "ORDER BY FUID");
$syurlmRows = array();
if ($syurlmStmt) {
    while ($r = db2_fetch_assoc($syurlmStmt)) $syurlmRows[] = $r;
}

$fixed      = false;
$cntUpdated = 0;
$updateErr  = '';
$backupSql  = '';
$backupStatus = '';

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX' && $cntNeed > 0) {

    // Backup rows before update
    $bkStmt = db2_exec($conn,
        "SELECT * FROM SGHDSDATA.SYPORR "
      . "WHERE PRTSUS IN ('PUSHALL','PUSHOE') "
      . "AND PRPORT IN ($portList) "
      . "AND PRTSPT <> 'Y'");
    $lines = array(
        '-- FixPrtsptLive backup (rows before PRTSPT update)',
        '-- Generated: ' . date('Y-m-d H:i:s'),
        '-- Restoring: UPDATE ... SET PRTSPT=\'\' WHERE PRTSUS IN (\'PUSHALL\',\'PUSHOE\')',
        '',
    );
    if ($bkStmt) {
        $colCount = db2_num_fields($bkStmt);
        $cols = array();
        for ($i = 0; $i < $colCount; $i++) $cols[] = db2_field_name($bkStmt, $i);
        $colList = implode(', ', $cols);
        while ($r = db2_fetch_assoc($bkStmt)) {
            $vals = array();
            foreach ($r as $v) {
                $vals[] = ($v === null) ? 'NULL'
                        : "'" . str_replace("'", "''", rtrim($v)) . "'";
            }
            $lines[] = "INSERT INTO SGHDSDATA.SYPORR ($colList) VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }
    $backupSql = implode("\n", $lines);
    $ts        = date('Ymd_His');
    $outDir    = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) $outDir = dirname(__FILE__);
    $backupFile   = $outDir . '/FixPrtspt_backup_' . $ts . '.sql';
    $written      = file_put_contents($backupFile, $backupSql);
    $backupStatus = ($written !== false) ? 'OK'
                  : 'WRITE FAILED — copy SQL from textarea below';

    // Run the UPDATE
    $updStmt = db2_exec($conn,
        "UPDATE SGHDSDATA.SYPORR SET PRTSPT='Y' "
      . "WHERE PRTSUS IN ('PUSHALL','PUSHOE') "
      . "AND PRPORT IN ($portList) "
      . "AND PRTSPT <> 'Y'");
    if ($updStmt === false) {
        $updateErr = db2_stmt_errormsg();
    } else {
        $cntUpdated = db2_num_rows($updStmt);
        $fixed = true;
        $cntNeed = 0;
    }
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG EIP — Fix PRTSPT</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:24px; }
.header { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
          padding:14px 24px; border-radius:6px; border-bottom:3px solid #f90;
          margin-bottom:20px; }
.header h2 { font-size:20px; }
.header .sub { font-size:11px; opacity:.75; margin-top:3px; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:6px;
        padding:12px 18px; font-size:13px; margin-bottom:16px; }
.warn { background:#fff8e1; border:1px solid #ffe082; border-radius:6px;
        padding:12px 18px; font-size:13px; margin-bottom:16px; }
.err  { background:#fce4ec; border:1px solid #f48fb1; border-radius:6px;
        padding:12px 18px; font-size:13px; margin-bottom:16px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:6px;
        box-shadow:0 2px 6px rgba(0,0,0,.06); margin-bottom:20px; }
th { background:#2a5a8c; color:#fff; padding:7px 14px; text-align:left; font-size:11px; }
td { padding:5px 14px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f2f5; }
.btn { display:inline-block; margin-top:12px; background:#1565c0; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#0d47a1; }
textarea { width:100%; height:160px; font-family:monospace; font-size:11px;
           border:1px solid #ccc; border-radius:4px; padding:8px;
           background:#fff; margin-top:6px; }
</style>
</head>
<body>
<div class="header">
  <h2>SG EIP &mdash; Fix SYPORR PRTSPT</h2>
  <div class="sub">SGHDSDATA &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<?php if ($fixed): ?>
<div class="ok">
  <strong>Fixed.</strong> Updated <?= $cntUpdated ?> SYPORR rows: PRTSPT set to 'Y'.
  SG portal tabs should now be visible to all roles. Log out and back in to see changes.
</div>
<div class="<?= strpos($backupStatus,'FAIL')!==false ? 'warn' : 'ok' ?>">
  <strong>Backup:</strong> <?= htmlspecialchars(basename($backupFile)) ?>
  &mdash; <?= htmlspecialchars($backupStatus) ?>
</div>
<?php if (strpos($backupStatus,'WRITE FAILED') !== false): ?>
<div><strong>Backup SQL:</strong>
<textarea readonly><?= htmlspecialchars($backupSql) ?></textarea></div>
<?php endif; ?>
<?php endif; ?>

<?php if ($updateErr): ?>
<div class="err"><strong>UPDATE failed:</strong> <?= htmlspecialchars($updateErr) ?></div>
<?php endif; ?>

<!-- Diagnosis -->
<table>
  <tr><th colspan="2">SYPORR PRTSPT Status — SG Portal Rows</th></tr>
  <tr>
    <td>Rows needing PRTSPT='Y' fix</td>
    <td><strong style="color:<?= $cntNeed>0?'#c62828':'#2e7d32' ?>"><?= $cntNeed ?></strong></td>
  </tr>
  <tr>
    <td>Rows already have PRTSPT='Y'</td>
    <td><?= $cntAlreadyOk ?></td>
  </tr>
</table>

<?php if (!$fixed && $cntNeed > 0): ?>
<p style="font-size:13px;margin-bottom:4px;">
  Fix sets PRTSPT='Y' on all <?= $cntNeed ?> PUSHALL/PUSHOE SG portal rows.
  Backup runs first.
</p>
<a class="btn" href="?confirm=FIX">Fix <?= $cntNeed ?> Rows Now</a>
<?php elseif (!$fixed && $cntNeed === 0 && $cntAlreadyOk > 0): ?>
<div class="ok"><strong>All SG portal SYPORR rows already have PRTSPT='Y'.</strong></div>
<?php endif; ?>

<!-- SYURLM check -->
<h3 style="margin:20px 0 8px;font-size:14px;color:#2a5a8c;">
  SYURLM — SG Portal entries (<?= count($syurlmRows) ?> found)
</h3>
<?php if (empty($syurlmRows)): ?>
<div class="err"><strong>SYURLM has NO SG portal entries!</strong>
PushAllMenusLive.php Step 1 may not have run correctly.</div>
<?php else: ?>
<table>
  <tr><th>FUID</th><th>FUTSPT</th></tr>
  <?php foreach ($syurlmRows as $r): ?>
  <tr>
    <td><?= htmlspecialchars(rtrim($r['FUID'])) ?></td>
    <td><?= htmlspecialchars(rtrim($r['FUTSPT'])) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
