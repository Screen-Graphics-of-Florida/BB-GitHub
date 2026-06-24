<?php
// Quick SYPORR diagnostic — shows summary + SYROLD coverage for any role
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagSyporr.php
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagSyporr.php?role=ENAPOLES

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role = isset($_GET['role']) ? strtoupper(trim($_GET['role'])) : 'ENAPOLES';

function qry($conn, $sql) {
    $rows = array();
    $s = @db2_exec($conn, $sql);
    if ($s === false) return array();
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}

// Summary: row counts by PRTSUS + PRTSPT
$summary = qry($conn,
    "SELECT RTRIM(PRTSUS) AS PRTSUS, RTRIM(PRTSPT) AS PRTSPT, COUNT(*) AS CNT "
  . "FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE='$role' "
  . "GROUP BY PRTSUS, PRTSPT ORDER BY PRTSUS, PRTSPT");

// Top-level rows (PRPAGE='') — one per portal
$topRows = qry($conn,
    "SELECT RTRIM(PRPORT) AS PRPORT, RTRIM(PRTSPT) AS PRTSPT, "
  . "       RTRIM(PRTSUS) AS PRTSUS "
  . "FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)='' "
  . "ORDER BY PRPORT");

// SYROLD for role
$syrold = qry($conn,
    "SELECT RTRIM(RDPORT) AS RDPORT, RDSEQN "
  . "FROM SGHDSDATA.SYROLD "
  . "WHERE RDROLE='$role' ORDER BY RDSEQN");

db2_close($conn);

// Index top-level SYPORR rows by PRPORT
$syporr_map = array();
foreach ($topRows as $r) $syporr_map[$r['PRPORT']] = $r;

$totalRows = 0;
foreach ($summary as $r) $totalRows += (int)$r['CNT'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SYPORR Diag: <?= htmlspecialchars($role) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
h2 { background:#2a5a8c; color:#fff; padding:8px 16px; margin:0 0 10px;
     border-radius:4px; font-size:15px; border-bottom:3px solid #f90; }
table { border-collapse:collapse; width:100%; background:#fff;
        margin-bottom:16px; border-radius:4px; overflow:hidden;
        box-shadow:0 2px 4px rgba(0,0,0,.08); }
th { background:#2a5a8c; color:#fff; padding:6px 12px;
     text-align:left; font-size:11px; }
td { padding:5px 12px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.ok  { color:#2e7d32; font-weight:bold; }
.bad { color:#c62828; font-weight:bold; }
.warn{ color:#e65100; font-weight:bold; }
form { margin-bottom:16px; }
input  { padding:6px 10px; font-size:13px; border:1px solid #ccc;
         border-radius:4px; width:200px; }
button { padding:6px 14px; font-size:13px; background:#2a5a8c; color:#fff;
         border:none; border-radius:4px; cursor:pointer; margin-left:6px; }
.box { background:#fff; border-radius:4px; padding:10px 16px;
       box-shadow:0 2px 4px rgba(0,0,0,.08); margin-bottom:16px;
       font-size:13px; }
</style>
</head>
<body>

<form>
  <input name="role" value="<?= htmlspecialchars($role) ?>">
  <button>Check</button>
</form>

<div class="box">
  <strong>Role:</strong> <?= htmlspecialchars($role) ?>
  &nbsp;|&nbsp;
  <strong>Total SYPORR rows:</strong>
  <span class="<?= $totalRows>0?'ok':'bad' ?>"><?= $totalRows ?></span>
  &nbsp;|&nbsp;
  <strong>SYROLD portals:</strong> <?= count($syrold) ?>
  &nbsp;|&nbsp;
  <strong>SYPORR top-level rows:</strong> <?= count($topRows) ?>
</div>

<!-- Summary by PRTSUS + PRTSPT -->
<h2>SYPORR Summary by Tag &amp; PRTSPT</h2>
<table>
  <tr><th>PRTSUS (tag)</th><th>PRTSPT</th><th>Row Count</th><th>Note</th></tr>
  <?php if (empty($summary)): ?>
  <tr><td colspan="4" class="bad">0 rows &mdash; BYPASS MODE (sees all portals)</td></tr>
  <?php else: ?>
  <?php foreach ($summary as $r):
      $prtspt = $r['PRTSPT'];
      $note = ($prtspt === 'Y') ? 'Visible' : 'INVISIBLE (PRTSPT not Y)';
      $cls  = ($prtspt === 'Y') ? 'ok' : 'bad';
  ?>
  <tr>
    <td><?= htmlspecialchars($r['PRTSUS']) ?></td>
    <td class="<?= $cls ?>"><?= htmlspecialchars($prtspt) === '' ? '(blank)' : htmlspecialchars($prtspt) ?></td>
    <td><?= (int)$r['CNT'] ?></td>
    <td class="<?= $cls ?>"><?= $note ?></td>
  </tr>
  <?php endforeach; ?>
  <?php endif; ?>
</table>

<!-- SYROLD vs SYPORR coverage -->
<h2>SYROLD Portals vs SYPORR (top-level rows only)</h2>
<table>
  <tr><th>Seq</th><th>RDPORT (SYROLD)</th><th>SYPORR row?</th>
      <th>PRTSPT</th><th>PRTSUS</th></tr>
  <?php foreach ($syrold as $r):
      $port   = $r['RDPORT'];
      $sy     = isset($syporr_map[$port]) ? $syporr_map[$port] : null;
      $prtspt = $sy ? $sy['PRTSPT'] : '';
      $prtsus = $sy ? $sy['PRTSUS'] : '';
      $hasSy  = ($sy !== null);
      $rowCls = $hasSy ? ($prtspt==='Y' ? 'ok' : 'bad') : 'bad';
  ?>
  <tr>
    <td><?= htmlspecialchars(rtrim($r['RDSEQN'])) ?></td>
    <td><?= htmlspecialchars($port) ?></td>
    <td class="<?= $hasSy?'ok':'bad' ?>"><?= $hasSy ? 'YES' : 'MISSING' ?></td>
    <td class="<?= $prtspt==='Y'?'ok':($hasSy?'bad':'') ?>">
      <?= $hasSy ? (htmlspecialchars($prtspt)===''?'(blank)':htmlspecialchars($prtspt)) : '' ?>
    </td>
    <td><?= htmlspecialchars($prtsus) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<!-- SYPORR top-level rows NOT in SYROLD -->
<?php
$syrold_ports = array();
foreach ($syrold as $r) $syrold_ports[$r['RDPORT']] = true;
$extra = array();
foreach ($topRows as $r) {
    if (!isset($syrold_ports[$r['PRPORT']])) $extra[] = $r;
}
?>
<?php if (!empty($extra)): ?>
<h2>SYPORR Top-Level Rows NOT in SYROLD</h2>
<table>
  <tr><th>PRPORT</th><th>PRTSPT</th><th>PRTSUS</th></tr>
  <?php foreach ($extra as $r):
      $prtspt = $r['PRTSPT'];
      $cls = ($prtspt === 'Y') ? 'ok' : 'bad';
  ?>
  <tr>
    <td><?= htmlspecialchars($r['PRPORT']) ?></td>
    <td class="<?= $cls ?>"><?= $prtspt==='' ? '(blank)' : htmlspecialchars($prtspt) ?></td>
    <td><?= htmlspecialchars($r['PRTSUS']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
