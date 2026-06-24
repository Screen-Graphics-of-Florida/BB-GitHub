<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ============================================================
// SG EIP — Restore Bypass Role Portal Access
// For roles that currently have 0 SYPORR rows (bypass mode),
// queries SYROLD to get every portal they are assigned to,
// queries SYPORT for each portal's sub-items, then adds SYPORR
// rows for ALL portals + sub-items.
//
// Diagnose: load page with no params
// Backup + Fix: ?confirm=FIX
//
// URL:
//   https://portal.screen-graphics.com:5601/Custom/SG/RestoreBypassRolePorts.php
//   https://portal.screen-graphics.com:5601/Custom/SG/RestoreBypassRolePorts.php?confirm=FIX
// ============================================================

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) {
    die('<pre>DB2 connection failed: '
      . htmlspecialchars(db2_conn_errormsg()) . '</pre>');
}

// Find roles with SYROLD entries but NO SYPORR entries (bypass mode)
// Exclude HD_ALL_SG which is the intentional permanent bypass role
$bypassRoles = [];
$bpStmt = db2_exec($conn,
    "SELECT DISTINCT RDROLE FROM SGHDSDATA.SYROLD "
  . "EXCEPT "
  . "SELECT DISTINCT PRROLE FROM SGHDSDATA.SYPORR");
if ($bpStmt === false) {
    echo '<pre>Bypass query failed: '
       . htmlspecialchars(db2_stmt_errormsg()) . '</pre>';
}
if ($bpStmt) {
    while ($row = db2_fetch_array($bpStmt)) {
        $role = rtrim($row[0]);
        if ($role !== 'HD_ALL_SG') { $bypassRoles[] = $role; }
    }
}

// For each bypass role, get all portals from SYROLD
$rolePortals = [];
foreach ($bypassRoles as $role) {
    $pStmt = @db2_exec($conn,
        "SELECT RDPORT FROM SGHDSDATA.SYROLD WHERE RDROLE='$role' ORDER BY RDSEQN");
    if ($pStmt) {
        while ($row = db2_fetch_array($pStmt)) {
            $rolePortals[$role][] = rtrim($row[0]);
        }
    }
}

// For each portal, get its sub-items from SYPORT (FPPAGE=FPPORT = sub-item rows)
$portalSubs = [];
$allPortals = [];
foreach ($rolePortals as $role => $portals) {
    foreach ($portals as $pcode) { $allPortals[$pcode] = true; }
}
foreach (array_keys($allPortals) as $pcode) {
    $sStmt = @db2_exec($conn,
        "SELECT FPSEQ FROM SGHDSDATA.SYPORT "
      . "WHERE FPPORT='$pcode' AND FPPAGE='$pcode' ORDER BY FPSEQ");
    $portalSubs[$pcode] = [];
    if ($sStmt) {
        while ($row = db2_fetch_array($sStmt)) {
            $portalSubs[$pcode][] = (int)$row[0];
        }
    }
}

// ---- Backup + Fix ------------------------------------------
$backupSql    = '';
$backupFile   = '';
$backupStatus = '';
$cntOk = $cntSkip = $cntFail = 0;
$log   = [];
$fixed = false;

function runSql($label, $sql) {
    global $conn, $cntOk, $cntSkip, $cntFail, $log;
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) {
        $cntFail++;
        $log[] = ['FAIL', $label, db2_stmt_errormsg()];
    } else {
        $n = db2_num_rows($stmt);
        if ($n > 0) { $cntOk++;   $log[] = ['OK',   $label, '']; }
        else        { $cntSkip++; $log[] = ['SKIP', $label, 'already exists']; }
    }
}

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX' && !empty($bypassRoles)) {

    // Backup: current SYPORR state (should be 0 rows, but capture anyway)
    $roleList = "'" . implode("','", $bypassRoles) . "'";
    $bkStmt   = @db2_exec($conn,
        "SELECT * FROM SGHDSDATA.SYPORR WHERE PRROLE IN ($roleList)");
    $lines = [
        '-- RestoreBypassRolePorts backup',
        '-- Generated: ' . date('Y-m-d H:i:s'),
        '-- Bypass roles: ' . implode(', ', $bypassRoles),
        '',
    ];
    if ($bkStmt) {
        $colCount = db2_num_fields($bkStmt);
        $cols = [];
        for ($i = 0; $i < $colCount; $i++) { $cols[] = db2_field_name($bkStmt, $i); }
        $colList = implode(', ', $cols);
        while ($row = db2_fetch_assoc($bkStmt)) {
            $vals = [];
            foreach ($row as $v) {
                $vals[] = ($v === null) ? 'NULL'
                        : "'" . str_replace("'", "''", rtrim($v)) . "'";
            }
            $lines[] = "INSERT INTO SGHDSDATA.SYPORR ($colList) VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }
    $backupSql  = implode("\n", $lines);
    $ts         = date('Ymd_His');
    $outDir     = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) { $outDir = dirname(__FILE__); }
    $backupFile   = $outDir . '/RestoreBypassRoles_backup_' . $ts . '.sql';
    $written      = file_put_contents($backupFile, $backupSql);
    $backupStatus = ($written !== false) ? 'OK'
                  : 'WRITE FAILED — copy SQL from textarea below';

    // Insert SYPORR rows for all bypass roles × all their portals × sub-items
    foreach ($bypassRoles as $role) {
        if (empty($rolePortals[$role])) continue;
        foreach ($rolePortals[$role] as $pcode) {
            // Top-level row
            $prid_top = "$role/$pcode";
            runSql("SYPORR top $role/$pcode",
                "INSERT INTO SGHDSDATA.SYPORR
                     (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
                 SELECT '$role','$pcode','',1,'$prid_top','Y',
                        CURRENT_TIMESTAMP,'BYPASSFIX',''
                 FROM SYSIBM.SYSDUMMY1
                 WHERE NOT EXISTS (
                     SELECT 1 FROM SGHDSDATA.SYPORR
                     WHERE PRROLE='$role' AND PRPORT='$pcode' AND PRPAGE='')");

            // Sub-item rows
            $seqs = !empty($portalSubs[$pcode]) ? $portalSubs[$pcode] : range(1, 6);
            foreach ($seqs as $i) {
                $seqstr   = number_format($i, 2);
                $prid_sub = "$role/$pcode/$pcode/$seqstr";
                runSql("SYPORR sub $role/$pcode/$i",
                    "INSERT INTO SGHDSDATA.SYPORR
                         (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
                     SELECT '$role','$pcode','$pcode',$i,'$prid_sub','Y',
                            CURRENT_TIMESTAMP,'BYPASSFIX',''
                     FROM SYSIBM.SYSDUMMY1
                     WHERE NOT EXISTS (
                         SELECT 1 FROM SGHDSDATA.SYPORR
                         WHERE PRROLE='$role' AND PRPORT='$pcode'
                           AND PRPAGE='$pcode' AND PRSEQ=$i)");
            }
        }
    }
    $fixed = true;
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG EIP — Restore Bypass Role Ports</title>
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
.summary { display: flex; gap: 16px; margin-bottom: 20px; }
.card { background: #fff; border-radius: 6px; padding: 14px 24px;
        border-left: 4px solid #ccc; min-width: 110px; text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,.06); }
.card.ok   { border-color: #2e7d32; }
.card.skip { border-color: #e65100; }
.card.fail { border-color: #c62828; }
.card .num { font-size: 32px; font-weight: bold; color: #333; }
.card .lbl { font-size: 11px; color: #666; margin-top: 4px; }
.info  { background: #e3f2fd; border: 1px solid #90caf9; border-radius: 6px;
         padding: 12px 18px; font-size: 13px; margin-bottom: 16px; }
.warn  { background: #fff8e1; border: 1px solid #ffe082; border-radius: 6px;
         padding: 12px 18px; font-size: 13px; margin-bottom: 16px; }
.ok-box { background: #e8f5e9; border: 1px solid #a5d6a7; border-radius: 6px;
          padding: 12px 18px; font-size: 13px; margin-bottom: 16px; }
table { width: 100%; border-collapse: collapse; background: #fff;
        border-radius: 6px; overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,.06); margin-bottom: 20px; }
th { background: #2a5a8c; color: #fff; padding: 8px 14px;
     text-align: left; font-size: 12px; }
td { padding: 5px 14px; font-size: 12px;
     border-bottom: 1px solid #f0f2f5; font-family: monospace; }
tr.ok   td:first-child { color: #2e7d32; font-weight: bold; }
tr.skip td:first-child { color: #999; }
tr.fail td { color: #c62828; font-weight: bold; }
.btn { display: inline-block; margin-top: 12px; background: #1565c0;
       color: #fff; font-weight: bold; padding: 10px 24px;
       border-radius: 4px; text-decoration: none; font-size: 14px; }
.btn:hover { background: #0d47a1; }
textarea { width: 100%; height: 180px; font-family: monospace; font-size: 11px;
           border: 1px solid #ccc; border-radius: 4px; padding: 8px;
           background: #fff; margin-top: 8px; }
</style>
</head>
<body>
<div class="header">
  <h2>SG EIP &mdash; Restore Bypass Role Portal Access</h2>
  <div class="sub">SGHDSDATA &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<?php if ($fixed): ?>
<div class="ok-box">
  <strong>Done.</strong> <?= $cntOk ?> inserted, <?= $cntSkip ?> already existed,
  <?= $cntFail ?> failed.
</div>
<div class="<?= strpos($backupStatus,'FAIL')!==false ? 'warn' : 'ok-box' ?>">
  <strong>Backup:</strong> <?= htmlspecialchars(basename($backupFile)) ?>
  &mdash; <?= htmlspecialchars($backupStatus) ?>
</div>
<?php if (strpos($backupStatus,'WRITE FAILED') !== false): ?>
<div><strong>Backup SQL:</strong>
<textarea readonly><?= htmlspecialchars($backupSql) ?></textarea></div>
<?php endif; ?>
<?php endif; ?>

<?php if (empty($bypassRoles)): ?>
<div class="info"><strong>No bypass roles found.</strong>
All roles already have SYPORR entries.</div>
<?php else: ?>
<div class="warn">
  <strong><?= count($bypassRoles) ?> role(s) currently have 0 SYPORR rows.</strong>
  Fix will add SYPORR rows for every portal each role has in SYROLD,
  covering all sub-items found in SYPORT.
</div>

<table>
  <tr><th>Role</th><th>Portals in SYROLD</th></tr>
  <?php foreach ($bypassRoles as $role): ?>
  <tr>
    <td><?= htmlspecialchars($role) ?></td>
    <td><?= htmlspecialchars(implode(', ', isset($rolePortals[$role]) ? $rolePortals[$role] : array())) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php if (!$fixed): ?>
<p style="font-size:13px;margin-bottom:4px;">
  Fix backs up first, then adds SYPORR rows for all portals listed above.
</p>
<a class="btn" href="?confirm=FIX">Fix All <?= count($bypassRoles) ?> Roles Now</a>
<?php endif; ?>
<?php endif; ?>

<?php if ($fixed && !empty($log)): ?>
<div class="summary">
  <div class="card ok"><div class="num"><?= $cntOk ?></div><div class="lbl">Inserted</div></div>
  <div class="card skip"><div class="num"><?= $cntSkip ?></div><div class="lbl">Skipped</div></div>
  <div class="card fail"><div class="num"><?= $cntFail ?></div><div class="lbl">Failed</div></div>
</div>
<table>
  <tr><th>Status</th><th>Item</th><th>Note</th></tr>
  <?php foreach ($log as list($status, $label, $note)): ?>
  <tr class="<?= strtolower($status) ?>">
    <td><?= $status ?></td>
    <td><?= htmlspecialchars($label) ?></td>
    <td><?= htmlspecialchars($note) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
