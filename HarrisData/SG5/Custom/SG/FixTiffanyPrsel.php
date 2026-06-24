<?php
// FixTiffanyPrsel.php
// Fix: SYPORR rows for native portals have PRSEL='' instead of 'Y'.
// Menu.php filters AND PRSEL='Y' so they never show.
// Fix: UPDATE SYPORR SET PRSEL='Y' WHERE PRROLE='TIFFANY' AND PRSEL=''.
//
// Preview: https://portal.screen-graphics.com:5601/Custom/SG/FixTiffanyPrsel.php
// Execute: https://portal.screen-graphics.com:5601/Custom/SG/FixTiffanyPrsel.php?confirm=FIX

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role = 'TIFFANY';

function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s);
    return $r ? db2_result($s, 0) : null;
}
function qrows($conn, $sql) {
    $rows = array(); $s = @db2_exec($conn, $sql);
    if (!$s) return $rows;
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}

// Count rows to fix (top-level only — those control portal visibility)
$toFix = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)='' AND RTRIM(PRSEL)<>'Y'");

$alreadyY = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)='' AND RTRIM(PRSEL)='Y'");

// Preview rows
$preview = qrows($conn,
    "SELECT RTRIM(PRPORT) AS PRPORT, RTRIM(PRSEL) AS PRSEL, "
  . "       RTRIM(PRTSUS) AS PRTSUS "
  . "FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)='' AND RTRIM(PRSEL)<>'Y' "
  . "ORDER BY PRPORT");

$fixed = false;
$errMsg = '';
$backupFile = '';
$backupStatus = '';
$rowsUpdated = 0;

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX' && $toFix > 0) {

    // Backup ALL SYPORR rows for TIFFANY
    $lines = array(
        '-- FixTiffanyPrsel backup: ALL SYPORR rows for TIFFANY',
        '-- Generated: ' . date('Y-m-d H:i:s'),
        '-- Fix: UPDATE top-level rows WHERE PRSEL<>\'Y\' -> SET PRSEL=\'Y\'',
        '',
    );
    $bk = @db2_exec($conn, "SELECT * FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");
    if ($bk) {
        $nc = db2_num_fields($bk);
        $cols = array();
        for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($bk, $i);
        $cl = implode(', ', $cols);
        while ($row = db2_fetch_assoc($bk)) {
            $vals = array();
            foreach ($row as $v) {
                $vals[] = ($v === null) ? 'NULL'
                        : "'" . str_replace("'", "''", rtrim($v)) . "'";
            }
            $lines[] = "INSERT INTO SGHDSDATA.SYPORR ($cl) VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }
    $backupSql = implode("\n", $lines);
    $ts = date('Ymd_His');
    $outDir = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) $outDir = dirname(__FILE__);
    $backupFile   = $outDir . '/FixTiffanyPrsel_' . $ts . '.sql';
    $written      = file_put_contents($backupFile, $backupSql);
    $backupStatus = ($written !== false) ? 'OK' : 'WRITE FAILED';

    // Execute the fix
    $upd = @db2_exec($conn,
        "UPDATE SGHDSDATA.SYPORR "
      . "SET PRSEL='Y' "
      . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)='' AND RTRIM(PRSEL)<>'Y'");
    if ($upd === false) {
        $errMsg = db2_stmt_errormsg();
    } else {
        $rowsUpdated = db2_num_rows($upd);
        $fixed = true;
    }
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Fix TIFFANY PRSEL</title>
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
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff; padding:5px 10px;
     text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:12px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.btn { display:inline-block; margin-top:10px; background:#1565c0; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#0d47a1; }
</style>
</head>
<body>

<div class="hdr">Fix TIFFANY: Set PRSEL='Y' for Native Portals</div>

<div class="info">
  <strong>Root cause:</strong> SYPORR top-level rows for native portals have
  <code>PRSEL=''</code> (blank). Menu.php filters <code>AND PRSEL='Y'</code>,
  so they never appear.<br>
  <strong>Fix:</strong> UPDATE top-level rows (PRPAGE='') where PRSEL is not 'Y'
  &rarr; set PRSEL='Y'. Full backup taken first.
</div>

<?php if ($fixed): ?>
<div class="ok">
  <strong>Done.</strong> <?= $rowsUpdated ?> rows updated (PRSEL set to 'Y').
  Log out and back in — all portals should now appear in the left navigation.
</div>
<div class="<?= strpos($backupStatus,'FAIL')!==false ? 'warn' : 'ok' ?>">
  <strong>Backup:</strong> <?= htmlspecialchars(basename($backupFile)) ?>
  &mdash; <?= htmlspecialchars($backupStatus) ?>
</div>

<?php elseif ($errMsg): ?>
<div class="err">Error: <?= htmlspecialchars($errMsg) ?></div>

<?php else: ?>
<div class="warn">
  <strong>Preview.</strong> <?= $toFix ?> top-level SYPORR rows have PRSEL='' and will be
  updated to PRSEL='Y'. <?= $alreadyY ?> rows already have PRSEL='Y' and are unchanged.
</div>
<table>
  <tr><th>PRPORT (Portal)</th><th>PRSEL (current)</th><th>PRTSUS</th><th>Action</th></tr>
  <?php foreach ($preview as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['PRPORT']) ?></td>
    <td style="color:#c62828"><?= htmlspecialchars($r['PRSEL']===''?'(blank)':$r['PRSEL']) ?></td>
    <td><?= htmlspecialchars($r['PRTSUS']) ?></td>
    <td style="color:#1565c0;font-weight:bold">SET PRSEL='Y'</td>
  </tr>
  <?php endforeach; ?>
</table>
<a class="btn" href="?confirm=FIX">Fix <?= $toFix ?> Rows &mdash; Set PRSEL='Y'</a>
<?php endif; ?>

</body>
</html>