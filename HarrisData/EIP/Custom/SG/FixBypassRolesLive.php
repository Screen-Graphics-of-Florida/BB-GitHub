<?php
// ============================================================
// SG EIP Live Fix — Restore Bypass Roles
// Finds roles whose ONLY SYPORR rows came from our push scripts
// (PRTSUS IN 'PUSHALL','PUSHOE') — those were in bypass mode
// before and were accidentally switched to whitelist mode.
//
// Backup → Verify → Fix pattern:
//   Diagnose:  load page with no params
//   Fix:       ?confirm=FIX  (backs up rows first, then deletes)
//
// URL (diagnose only):
//   https://portal.screen-graphics.com:5601/Custom/SG/FixBypassRolesLive.php
//
// URL (backup + fix):
//   https://portal.screen-graphics.com:5601/Custom/SG/FixBypassRolesLive.php?confirm=FIX
// ============================================================

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) {
    die('<pre>DB2 connection failed: '
      . htmlspecialchars(db2_conn_errormsg()) . '</pre>');
}

// Find roles whose ONLY SYPORR rows came from either push script
$diagSql =
    "SELECT DISTINCT PRROLE FROM SGHDSDATA.SYPORR "
  . "WHERE PRTSUS IN ('PUSHALL','PUSHOE') "
  . "EXCEPT "
  . "SELECT DISTINCT PRROLE FROM SGHDSDATA.SYPORR "
  . "WHERE PRTSUS NOT IN ('PUSHALL','PUSHOE')";

$stmt = @db2_exec($conn, $diagSql);
$bypassRoles = [];
if ($stmt) {
    while ($row = db2_fetch_array($stmt)) {
        $bypassRoles[] = rtrim($row[0]);
    }
}

// ---- Backup ------------------------------------------------
$backupSql  = '';
$backupFile = '';
$backupStatus = '';

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX' && !empty($bypassRoles)) {
    $roleList = "'" . implode("','", $bypassRoles) . "'";

    // Dump the rows about to be deleted as INSERT statements
    $bkStmt = @db2_exec($conn,
        "SELECT * FROM SGHDSDATA.SYPORR "
      . "WHERE PRTSUS IN ('PUSHALL','PUSHOE') AND PRROLE IN ($roleList)");

    $lines   = [];
    $lines[] = '-- FixBypassRolesLive backup — rows being deleted';
    $lines[] = '-- Generated: ' . date('Y-m-d H:i:s');
    $lines[] = '';
    if ($bkStmt) {
        $colCount = db2_num_fields($bkStmt);
        $cols = [];
        for ($i = 0; $i < $colCount; $i++) { $cols[] = db2_field_name($bkStmt, $i); }
        $colList = implode(', ', $cols);
        while ($row = db2_fetch_assoc($bkStmt)) {
            $vals = [];
            foreach ($row as $v) {
                if ($v === null) { $vals[] = 'NULL'; }
                else             { $vals[] = "'" . str_replace("'", "''", rtrim($v)) . "'"; }
            }
            $lines[] = "INSERT INTO SGHDSDATA.SYPORR ($colList) VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }
    $backupSql = implode("\n", $lines);

    $ts       = date('Ymd_His');
    $outDir   = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) { $outDir = dirname(__FILE__); }
    $backupFile   = $outDir . '/FixBypassRoles_backup_' . $ts . '.sql';
    $written      = file_put_contents($backupFile, $backupSql);
    $backupStatus = ($written !== false) ? 'OK' : 'WRITE FAILED — copy SQL from textarea below';

    // ---- Delete --------------------------------------------
    $fixed  = false;
    $cntDel = 0;
    $delErr = '';

    $delStmt = @db2_exec($conn,
        "DELETE FROM SGHDSDATA.SYPORR "
      . "WHERE PRTSUS IN ('PUSHALL','PUSHOE') AND PRROLE IN ($roleList)");
    if ($delStmt === false) {
        $delErr = db2_stmt_errormsg();
    } else {
        $cntDel = db2_num_rows($delStmt);
        $fixed  = true;
    }
} else {
    $fixed  = false;
    $cntDel = 0;
    $delErr = '';
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG EIP Fix — Bypass Roles</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 24px; }
.header {
    background: linear-gradient(135deg, #2a5a8c 0%, #1a3d5c 100%);
    color: #fff; padding: 14px 24px; border-radius: 6px;
    border-bottom: 3px solid #f90; margin-bottom: 20px;
}
.header h2 { font-size: 20px; }
.header .sub { font-size: 12px; opacity: .75; margin-top: 3px; }
.info  { background: #e3f2fd; border: 1px solid #90caf9; border-radius: 6px;
         padding: 12px 18px; font-size: 13px; margin-bottom: 16px; }
.warn  { background: #fff8e1; border: 1px solid #ffe082; border-radius: 6px;
         padding: 12px 18px; font-size: 13px; margin-bottom: 16px; }
.ok    { background: #e8f5e9; border: 1px solid #a5d6a7; border-radius: 6px;
         padding: 12px 18px; font-size: 13px; margin-bottom: 16px; }
.err   { background: #fce4ec; border: 1px solid #f48fb1; border-radius: 6px;
         padding: 12px 18px; font-size: 13px; margin-bottom: 16px;
         font-family: monospace; }
table { border-collapse: collapse; background: #fff; border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,.06); margin-bottom: 20px; }
th { background: #2a5a8c; color: #fff; padding: 8px 20px;
     text-align: left; font-size: 12px; }
td { padding: 6px 20px; font-size: 12px;
     border-bottom: 1px solid #f0f2f5; font-family: monospace; }
.btn {
    display: inline-block; margin-top: 12px;
    background: #c62828; color: #fff; font-weight: bold;
    padding: 10px 24px; border-radius: 4px; text-decoration: none;
    font-size: 14px;
}
.btn:hover { background: #b71c1c; }
textarea { width: 100%; height: 220px; font-family: monospace; font-size: 11px;
           border: 1px solid #ccc; border-radius: 4px; padding: 8px;
           background: #fff; margin-top: 8px; }
</style>
</head>
<body>
<div class="header">
  <h2>SG EIP Fix &mdash; Restore Bypass Roles</h2>
  <div class="sub">SGHDSDATA &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<?php if ($fixed): ?>
  <div class="ok">
    <strong>Fixed.</strong> Deleted <?= $cntDel ?> SYPORR rows for
    <?= count($bypassRoles) ?> role(s). Those roles are back in bypass mode
    and can see all portals again.
  </div>
  <div class="<?= strpos($backupStatus,'FAIL')!==false ? 'warn' : 'ok' ?>">
    <strong>Backup:</strong> <?= htmlspecialchars(basename($backupFile)) ?>
    &mdash; <?= htmlspecialchars($backupStatus) ?>
  </div>
  <?php if (strpos($backupStatus, 'WRITE FAILED') !== false): ?>
  <div><strong>Backup SQL (save this before closing):</strong>
  <textarea readonly><?= htmlspecialchars($backupSql) ?></textarea></div>
  <?php endif; ?>
<?php endif; ?>

<?php if ($delErr): ?>
  <div class="err"><strong>Delete failed:</strong> <?= htmlspecialchars($delErr) ?></div>
<?php endif; ?>

<?php if (empty($bypassRoles)): ?>
  <div class="info">
    <strong>No bypass roles affected.</strong>
    All roles with SYPORR rows had pre-existing entries before the push.
    No action needed.
  </div>
<?php else: ?>
  <div class="warn">
    <strong><?= count($bypassRoles) ?> role(s) switched from bypass to whitelist mode</strong>
    by the push. These roles had no SYPORR rows before and now only see
    the 5 SG portals. Fix deletes our rows and restores bypass mode
    (sees all portals again, including SG portals).
  </div>

  <table>
    <tr><th>Role</th><th>Status</th></tr>
    <?php foreach ($bypassRoles as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r) ?></td>
      <td><?= $fixed
            ? '<span style="color:#2e7d32;font-weight:bold">RESTORED</span>'
            : '<span style="color:#c62828;font-weight:bold">NEEDS FIX</span>' ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <?php if (!$fixed): ?>
  <p style="font-size:13px;margin-bottom:4px;">
    Clicking Fix will <strong>back up the affected rows first</strong>, then delete them.
  </p>
  <a class="btn" href="?confirm=FIX">Fix All <?= count($bypassRoles) ?> Roles Now</a>
  <?php endif; ?>
<?php endif; ?>

</body>
</html>
