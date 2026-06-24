<?php
// FixEnapolesSyporr.php
//
// Removes top-level SYPORR rows (PRPAGE='') for ENAPOLES.
// These rows were added by push scripts and put ENAPOLES into
// whitelist mode, hiding all portals not in SYURLM as /PORTAL.
//
// SYROLD (Role Master) is the authority for which portals a role
// sees. Top-level SYPORR rows interfere with that. Sub-page rows
// (PRPAGE != '') are kept — they control SG portal category access.
//
// Usage:
//   Preview: https://portal.screen-graphics.com:5601/Custom/SG/FixEnapolesSyporr.php
//   Execute: https://portal.screen-graphics.com:5601/Custom/SG/FixEnapolesSyporr.php?confirm=FIX

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role = 'ENAPOLES';

function qrows($conn, $sql) {
    $rows = array(); $s = @db2_exec($conn, $sql);
    if (!$s) return $rows;
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}
function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s); return $r ? db2_result($s, 0) : null;
}

// Rows to DELETE: top-level (PRPAGE='')
$topRows = qrows($conn,
    "SELECT RTRIM(PRPORT) AS PRPORT, RTRIM(PRPAGE) AS PRPAGE, "
  . "       RTRIM(PRTSPT) AS PRTSPT, RTRIM(PRTSUS) AS PRTSUS "
  . "FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)='' "
  . "ORDER BY PRPORT");

// Rows to KEEP: sub-page (PRPAGE != '')
$subCount = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)<>''");

$totalCount = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");

$deleted = false;
$backupFile = '';
$backupStatus = '';
$errMsg = '';

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX') {

    // Backup top-level rows
    $lines = array(
        '-- FixEnapolesSyporr backup: top-level SYPORR rows for ENAPOLES',
        '-- Generated: ' . date('Y-m-d H:i:s'),
        '-- These are the rows being DELETED to restore bypass mode.',
        '-- To restore whitelist mode, re-insert these rows.',
        '',
    );
    $bkStmt = @db2_exec($conn,
        "SELECT * FROM SGHDSDATA.SYPORR "
      . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)=''");
    if ($bkStmt) {
        $colCount = db2_num_fields($bkStmt);
        $cols = array();
        for ($i = 0; $i < $colCount; $i++) $cols[] = db2_field_name($bkStmt, $i);
        $colList = implode(', ', $cols);
        while ($row = db2_fetch_assoc($bkStmt)) {
            $vals = array();
            foreach ($row as $v) {
                $vals[] = ($v === null) ? 'NULL'
                        : "'" . str_replace("'", "''", rtrim($v)) . "'";
            }
            $lines[] = "INSERT INTO SGHDSDATA.SYPORR ($colList) VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }
    $backupSql = implode("\n", $lines);
    $ts = date('Ymd_His');
    $outDir = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) $outDir = dirname(__FILE__);
    $backupFile = $outDir . '/FixEnapolesSyporr_toplevel_' . $ts . '.sql';
    $written = file_put_contents($backupFile, $backupSql);
    $backupStatus = ($written !== false) ? 'OK'
                  : 'WRITE FAILED — copy SQL from page source';

    // Delete top-level rows
    $stmt = @db2_exec($conn,
        "DELETE FROM SGHDSDATA.SYPORR "
      . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)=''");
    if ($stmt === false) {
        $errMsg = db2_stmt_errormsg();
    } else {
        $deleted = true;
    }
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Fix ENAPOLES SYPORR</title>
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
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:6px;
        padding:12px 18px; font-size:12px; margin-bottom:16px; }
.cards { display:flex; gap:16px; margin-bottom:20px; }
.card { background:#fff; border-radius:6px; padding:14px 24px;
        min-width:120px; text-align:center;
        box-shadow:0 2px 4px rgba(0,0,0,.06); }
.card.del  { border-left:4px solid #c62828; }
.card.keep { border-left:4px solid #2e7d32; }
.card.tot  { border-left:4px solid #1565c0; }
.card .num { font-size:32px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:4px; }
table { width:100%; border-collapse:collapse; background:#fff;
        border-radius:6px; overflow:hidden;
        box-shadow:0 2px 6px rgba(0,0,0,.06); margin-bottom:20px; }
th { background:#2a5a8c; color:#fff; padding:6px 12px;
     text-align:left; font-size:11px; }
td { padding:4px 12px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f2f5; }
.btn { display:inline-block; margin-top:12px; background:#c62828; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#b71c1c; }
</style>
</head>
<body>

<div class="header">
  <h2>Fix ENAPOLES: Remove Top-Level SYPORR Rows</h2>
  <div class="sub">SGHDSDATA &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<div class="info">
  <strong>What this fixes:</strong> SYPORR top-level rows (PRPAGE='') put ENAPOLES into
  <em>whitelist mode</em>, which overrides Role Master (SYROLD) and hides all portals
  that lack a SYURLM /PORTAL entry. Removing them restores <em>bypass mode</em>:
  ENAPOLES sees exactly the portals assigned in Role Master, same as PLANNING.<br><br>
  <strong>Sub-page rows (PRPAGE ≠ '') are kept</strong> — they control category
  navigation within the SG portals and are not affected.
</div>

<?php if ($deleted): ?>
<div class="ok">
  <strong>Done.</strong> <?= count($topRows) ?> top-level SYPORR rows deleted.
  ENAPOLES is now in bypass mode. Log out and back in to verify all portals appear.
</div>
<div class="ok">
  <strong>Backup:</strong> <?= htmlspecialchars(basename($backupFile)) ?>
  &mdash; <?= htmlspecialchars($backupStatus) ?>
</div>
<div class="cards">
  <div class="card del"><div class="num"><?= count($topRows) ?></div>
    <div class="lbl">Top-level rows deleted</div></div>
  <div class="card keep"><div class="num"><?= $subCount ?></div>
    <div class="lbl">Sub-page rows kept</div></div>
</div>

<?php elseif ($errMsg): ?>
<div class="err"><strong>Error:</strong> <?= htmlspecialchars($errMsg) ?></div>

<?php else: ?>

<div class="warn">
  <strong>Preview — nothing has been changed yet.</strong>
  <?= count($topRows) ?> top-level rows will be deleted. <?= $subCount ?> sub-page rows will be kept.
</div>

<div class="cards">
  <div class="card tot"><div class="num"><?= $totalCount ?></div>
    <div class="lbl">Total SYPORR rows</div></div>
  <div class="card del"><div class="num"><?= count($topRows) ?></div>
    <div class="lbl">To DELETE (PRPAGE='')</div></div>
  <div class="card keep"><div class="num"><?= $subCount ?></div>
    <div class="lbl">To KEEP (PRPAGE≠'')</div></div>
</div>

<table>
  <tr>
    <th>PRPORT (Portal)</th>
    <th>PRTSPT</th>
    <th>PRTSUS</th>
    <th>Action</th>
  </tr>
  <?php foreach ($topRows as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['PRPORT']) ?></td>
    <td><?= htmlspecialchars($r['PRTSPT'] === '' ? '(blank)' : $r['PRTSPT']) ?></td>
    <td><?= htmlspecialchars($r['PRTSUS']) ?></td>
    <td style="color:#c62828;font-weight:bold">DELETE</td>
  </tr>
  <?php endforeach; ?>
</table>

<div class="warn">
  Back up is written automatically before any delete.
  After running, log out and back in to verify ENAPOLES sees all Role Master portals.
</div>

<a class="btn" href="?confirm=FIX">Delete <?= count($topRows) ?> Top-Level SYPORR Rows</a>

<?php endif; ?>

</body>
</html>
