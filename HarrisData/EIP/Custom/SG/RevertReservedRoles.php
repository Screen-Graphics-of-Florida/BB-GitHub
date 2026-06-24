<?php
// RevertReservedRoles.php
//
// Reverts HarrisData system-reserved roles (Rsv='Y' in SYROLM) back to
// their original state by removing SG-custom additions made by
// PushAllMenusLive.php:
//
//   1. DELETE SYPORR rows for reserved roles
//      (restores bypass mode — shows all SYROLD-assigned portals)
//   2. DELETE SYROLD SG portal entries for reserved roles
//      (removes SGINQ/SGDASH/SGDINT/SGRPT/SGSOP from their nav)
//
// RULE: Never add SG custom menus to HarrisData system reserved roles.
//
// Preview: https://portal.screen-graphics.com:5601/Custom/SG/RevertReservedRoles.php
// Execute: https://portal.screen-graphics.com:5601/Custom/SG/RevertReservedRoles.php?confirm=FIX

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$sgPortals = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
$inList = "'" . implode("','", $sgPortals) . "'";

function qrows($conn, $sql) {
    $rows = array(); $s = @db2_exec($conn, $sql);
    if (!$s) return array('__error' => db2_stmt_errormsg());
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}
function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s); return $r ? db2_result($s, 0) : null;
}

// Reserved HarrisData system roles — identified by HD_ prefix.
// HD_ALL_SG is intentionally excluded (it is the SG bypass role and
// is expected to have SG portals in its navigation).
$reservedRoles = array(
    'HD_ALL','HD_CUST','HD_EMPL',
    'HD_HRADMIN','HD_MOBILE','HD_SECURE','HD_SLSM','HD_VEND',
);

// Narrow to only those that actually have anything to revert
$roleIn   = "'" . implode("','", $reservedRoles) . "'";

// Preview: SYPORR rows to delete
$syporr = qrows($conn,
    "SELECT RTRIM(PRROLE) AS PRROLE, COUNT(*) AS CNT "
  . "FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE IN ($roleIn) "
  . "GROUP BY PRROLE ORDER BY PRROLE");

// Preview: SYROLD SG rows to delete
$syrold = qrows($conn,
    "SELECT RTRIM(RDROLE) AS RDROLE, RTRIM(RDPORT) AS RDPORT "
  . "FROM SGHDSDATA.SYROLD "
  . "WHERE RDROLE IN ($roleIn) AND RDPORT IN ($inList) "
  . "ORDER BY RDROLE, RDPORT");

$syporr_cnt = 0;
foreach ($syporr as $r) { if (!isset($r['__error'])) $syporr_cnt += (int)$r['CNT']; }
$syrold_cnt = count($syrold);

// ---- Execute -------------------------------------------------------
$fixed = false;
$errMsg = '';
$backupFile = '';
$backupStatus = '';

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX'
    && ($syporr_cnt > 0 || $syrold_cnt > 0)) {

    // Backup
    $lines = array(
        '-- RevertReservedRoles backup',
        '-- Generated: ' . date('Y-m-d H:i:s'),
        '-- Reserved roles: ' . implode(', ', $reservedRoles),
        '-- SG portals being removed from SYROLD: ' . implode(', ', $sgPortals),
        '',
        '-- SYPORR rows (to restore whitelist mode if needed):',
    );
    $bk1 = @db2_exec($conn,
        "SELECT * FROM SGHDSDATA.SYPORR WHERE PRROLE IN ($roleIn)");
    if ($bk1) {
        $nc = db2_num_fields($bk1);
        $cols = array();
        for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($bk1, $i);
        $cl = implode(', ', $cols);
        while ($row = db2_fetch_assoc($bk1)) {
            $vals = array();
            foreach ($row as $v) {
                $vals[] = ($v === null) ? 'NULL'
                        : "'" . str_replace("'", "''", rtrim($v)) . "'";
            }
            $lines[] = "INSERT INTO SGHDSDATA.SYPORR ($cl) VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }
    $lines[] = '';
    $lines[] = '-- SYROLD SG portal rows (to re-add if needed):';
    $bk2 = @db2_exec($conn,
        "SELECT * FROM SGHDSDATA.SYROLD "
      . "WHERE RDROLE IN ($roleIn) AND RDPORT IN ($inList)");
    if ($bk2) {
        $nc = db2_num_fields($bk2);
        $cols = array();
        for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($bk2, $i);
        $cl = implode(', ', $cols);
        while ($row = db2_fetch_assoc($bk2)) {
            $vals = array();
            foreach ($row as $v) {
                $vals[] = ($v === null) ? 'NULL'
                        : "'" . str_replace("'", "''", rtrim($v)) . "'";
            }
            $lines[] = "INSERT INTO SGHDSDATA.SYROLD ($cl) VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }

    $backupSql = implode("\n", $lines);
    $ts = date('Ymd_His');
    $outDir = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) $outDir = dirname(__FILE__);
    $backupFile   = $outDir . '/RevertReservedRoles_' . $ts . '.sql';
    $written      = file_put_contents($backupFile, $backupSql);
    $backupStatus = ($written !== false) ? 'OK' : 'WRITE FAILED';

    // Delete SYPORR for reserved roles
    $d1 = @db2_exec($conn,
        "DELETE FROM SGHDSDATA.SYPORR WHERE PRROLE IN ($roleIn)");
    if ($d1 === false) { $errMsg = 'SYPORR: ' . db2_stmt_errormsg(); }

    // Delete SYROLD SG portal entries for reserved roles
    if (!$errMsg) {
        $d2 = @db2_exec($conn,
            "DELETE FROM SGHDSDATA.SYROLD "
          . "WHERE RDROLE IN ($roleIn) AND RDPORT IN ($inList)");
        if ($d2 === false) { $errMsg = 'SYROLD: ' . db2_stmt_errormsg(); }
    }

    if (!$errMsg) $fixed = true;
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Revert Reserved Roles</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.warn { background:#fff8e1; border:1px solid #ffe082; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.err  { background:#fce4ec; border:1px solid #f48fb1; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; color:#c62828; font-weight:bold; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; font-size:12px; }
.sec  { font-size:13px; font-weight:bold; color:#2a5a8c; margin:16px 0 6px; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff; padding:5px 10px;
     text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:12px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.btn { display:inline-block; margin-top:10px; background:#c62828; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#b71c1c; }
.cards { display:flex; gap:14px; margin-bottom:16px; }
.card { background:#fff; border-radius:5px; padding:12px 20px; text-align:center;
        box-shadow:0 1px 4px rgba(0,0,0,.08); }
.card.del { border-left:4px solid #c62828; }
.card .num { font-size:28px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:3px; }
</style>
</head>
<body>

<div class="hdr">Revert HarrisData Reserved Roles</div>

<div class="info">
  <strong>What this does:</strong> Removes SG-custom additions from all
  HarrisData system-reserved roles (Rsv='Y' in SYROLM).<br>
  &bull; Deletes all SYPORR rows &rarr; restores <em>bypass mode</em>
  (roles see their full SYROLD-assigned portals again).<br>
  &bull; Deletes SYROLD rows for SGINQ/SGDASH/SGDINT/SGRPT/SGSOP
  &rarr; removes SG portals from these roles' navigation.<br>
  Full backup written before any delete.
</div>

<?php if ($fixed): ?>
<div class="ok">
  <strong>Done.</strong> Reserved roles reverted. Log out and back in to verify.
</div>
<div class="<?= strpos($backupStatus,'FAIL')!==false ? 'warn' : 'ok' ?>">
  <strong>Backup:</strong> <?= htmlspecialchars(basename($backupFile)) ?>
  &mdash; <?= htmlspecialchars($backupStatus) ?>
</div>

<?php elseif ($errMsg): ?>
<div class="err">Error: <?= htmlspecialchars($errMsg) ?></div>

<?php else: ?>
<div class="warn">
  <strong>Preview — nothing changed yet.</strong>
</div>

<div class="cards">
  <div class="card del">
    <div class="num"><?= $syporr_cnt ?></div>
    <div class="lbl">SYPORR rows to delete</div>
  </div>
  <div class="card del">
    <div class="num"><?= $syrold_cnt ?></div>
    <div class="lbl">SYROLD SG rows to delete</div>
  </div>
</div>

<div class="sec">Reserved Roles (Rsv='Y') — <?= count($reservedRoles) ?> found</div>
<table>
  <tr><th>Role</th><th>SYPORR rows</th><th>SYROLD SG rows</th></tr>
  <?php
  $syporr_by_role = array();
  foreach ($syporr as $r) {
      if (!isset($r['__error'])) $syporr_by_role[$r['PRROLE']] = (int)$r['CNT'];
  }
  $syrold_by_role = array();
  foreach ($syrold as $r) {
      if (!isset($r['__error'])) {
          if (!isset($syrold_by_role[$r['RDROLE']])) $syrold_by_role[$r['RDROLE']] = array();
          $syrold_by_role[$r['RDROLE']][] = $r['RDPORT'];
      }
  }
  foreach ($reservedRoles as $rl):
      $pc = isset($syporr_by_role[$rl]) ? $syporr_by_role[$rl] : 0;
      $sc = isset($syrold_by_role[$rl]) ? implode(', ', $syrold_by_role[$rl]) : '—';
  ?>
  <tr>
    <td><strong><?= htmlspecialchars($rl) ?></strong></td>
    <td style="color:<?= $pc>0?'#c62828':'#2e7d32' ?>;font-weight:bold">
      <?= $pc > 0 ? $pc . ' (DELETE)' : '0 (clean)' ?>
    </td>
    <td style="color:<?= $sc!=='—'?'#c62828':'#2e7d32' ?>">
      <?= htmlspecialchars($sc) ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<?php if ($syporr_cnt > 0 || $syrold_cnt > 0): ?>
<a class="btn" href="?confirm=FIX">Revert <?= count($reservedRoles) ?> Reserved Roles</a>
<?php else: ?>
<div class="ok"><strong>All reserved roles are already clean.</strong> Nothing to do.</div>
<?php endif; ?>

<?php endif; ?>

</body>
</html>
