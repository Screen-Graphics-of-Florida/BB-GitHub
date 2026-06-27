<?php
// FixS5EnapolesBypass.php
// Deletes ALL S5HDSDATA.SYPORR rows for ENAPOLES, returning it to bypass mode.
// Bypass mode = no SYPORR rows = all 24 portals in S5HDSDATA.SYROLD are visible.
// Does NOT touch SGHDSDATA.
//
// Preview: https://portal.screen-graphics.com:5610/Custom/SG/FixS5EnapolesBypass.php
// Execute: https://portal.screen-graphics.com:5610/Custom/SG/FixS5EnapolesBypass.php?confirm=FIX

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role = 'ENAPOLES';
$safe = str_replace("'", "''", $role);

function qval($c, $s) {
    $r = @db2_exec($c, $s); if (!$r) return null;
    $row = db2_fetch_row($r); return $row ? db2_result($r, 0) : null;
}
function qrows($c, $s) {
    $rows = array(); $r = @db2_exec($c, $s);
    if (!$r) return array('__error' => db2_stmt_errormsg());
    while ($row = db2_fetch_assoc($r)) $rows[] = $row;
    return $rows;
}

$cnt = (int)qval($conn, "SELECT COUNT(*) FROM S5HDSDATA.SYPORR WHERE PRROLE='$safe'");

$preview = qrows($conn,
    "SELECT RTRIM(PRPORT) AS PRPORT, RTRIM(PRPAGE) AS PRPAGE, "
  . "       RTRIM(PRSEL) AS PRSEL, PRSEQ "
  . "FROM S5HDSDATA.SYPORR WHERE PRROLE='$safe' ORDER BY PRPORT, PRPAGE, PRSEQ");

$fixed = false; $errMsg = ''; $rowsDeleted = 0; $backupStatus = '';

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX' && $cnt > 0) {
    // Backup first
    $lines = array(
        '-- FixS5EnapolesBypass backup: ALL S5HDSDATA.SYPORR rows for ENAPOLES',
        '-- Generated: ' . date('Y-m-d H:i:s'),
        '-- Fix: DELETE all rows to restore bypass mode',
        '',
    );
    $bk = @db2_exec($conn, "SELECT * FROM S5HDSDATA.SYPORR WHERE PRROLE='$safe'");
    if ($bk) {
        $nc = db2_num_fields($bk);
        $cols = array(); for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($bk, $i);
        while ($row = db2_fetch_assoc($bk)) {
            $vals = array();
            foreach ($row as $v)
                $vals[] = ($v === null) ? 'NULL' : "'" . str_replace("'","''",(string)rtrim($v)) . "'";
            $lines[] = "INSERT INTO S5HDSDATA.SYPORR (" . implode(', ', $cols) . ") VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }
    $ts  = date('Ymd_His');
    $backupSql = implode("\n", $lines);
    $dir = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($dir)) $dir = dirname(__FILE__);
    $file    = $dir . '/FixS5EnapolesBypass_' . $ts . '.sql';
    $written = file_put_contents($file, $backupSql);
    $backupStatus = ($written !== false) ? 'Backup OK: ' . basename($file) : 'File write failed — backup SQL shown below';

    // Execute delete
    $del = @db2_exec($conn, "DELETE FROM S5HDSDATA.SYPORR WHERE PRROLE='$safe'");
    if ($del === false) {
        $errMsg = db2_stmt_errormsg();
    } else {
        $rowsDeleted = db2_num_rows($del);
        $fixed = true;
    }
}
db2_close($conn);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Fix S5 ENAPOLES Bypass</title>
<style>
body{font:13px Arial,sans-serif;background:#f0f2f5;padding:20px;}
.hdr{background:linear-gradient(135deg,#2a5a8c,#1a3d5c);color:#fff;
     padding:12px 20px;border-radius:5px;border-bottom:3px solid #f90;
     margin-bottom:16px;font-size:17px;font-weight:bold;}
.ok  {background:#e8f5e9;border:1px solid #a5d6a7;border-radius:5px;padding:12px 16px;margin-bottom:14px;}
.warn{background:#fff8e1;border:1px solid #ffe082;border-radius:5px;padding:12px 16px;margin-bottom:14px;}
.err {background:#fce4ec;border:1px solid #f48fb1;border-radius:5px;padding:12px 16px;margin-bottom:14px;color:#c62828;font-weight:bold;}
.info{background:#e3f2fd;border:1px solid #90caf9;border-radius:5px;padding:12px 16px;margin-bottom:14px;font-size:12px;}
table{border-collapse:collapse;width:100%;background:#fff;border-radius:4px;
      box-shadow:0 1px 4px rgba(0,0,0,.08);margin-bottom:14px;}
th{background:#2a5a8c;color:#fff;padding:5px 10px;text-align:left;font-size:11px;}
td{padding:4px 10px;font-size:11px;font-family:monospace;border-bottom:1px solid #f0f0f0;}
.btn{display:inline-block;margin-top:10px;background:#c62828;color:#fff;
     font-weight:bold;padding:10px 28px;border-radius:4px;text-decoration:none;font-size:14px;}
.btn:hover{background:#8b1a1a;}
</style></head><body>
<div class="hdr">Fix S5HDSDATA ENAPOLES — Restore Bypass Mode</div>

<div class="info">
  <strong>Schema:</strong> S5HDSDATA only &mdash; SGHDSDATA is not touched.<br>
  <strong>Fix:</strong> Delete all <?=$cnt?> SYPORR rows for ENAPOLES from S5HDSDATA.
  This restores bypass mode: all 24 portals in S5HDSDATA.SYROLD become visible automatically.
</div>

<?php if ($fixed): ?>
<div class="ok"><strong>Done.</strong> <?=$rowsDeleted?> rows deleted from S5HDSDATA.SYPORR.
ENAPOLES is now in bypass mode &mdash; log out and back in to see the full menu.</div>
<div class="<?=($written!==false)?'ok':'warn'?>"><strong><?=htmlspecialchars($backupStatus)?></strong></div>
<?php if ($written === false): ?>
<pre style="background:#fff;border:1px solid #ccc;padding:10px;font-size:10px;overflow-x:auto;white-space:pre-wrap;"><?=htmlspecialchars($backupSql)?></pre>
<?php endif; ?>

<?php elseif ($errMsg): ?>
<div class="err">Error: <?=htmlspecialchars($errMsg)?></div>

<?php else: ?>
<div class="warn">
  <strong>Preview.</strong> <?=$cnt?> SYPORR rows will be deleted from S5HDSDATA for ENAPOLES.
  This returns ENAPOLES to bypass mode (shows all portals in SYROLD).
</div>
<table>
  <tr><th>PRPORT</th><th>PRPAGE</th><th>PRSEQ</th><th>PRSEL</th></tr>
  <?php if (isset($preview['__error'])): ?>
  <tr><td colspan="4" style="color:#c62828">Error: <?=htmlspecialchars($preview['__error'])?></td></tr>
  <?php else: foreach ($preview as $r): ?>
  <tr>
    <td><?=htmlspecialchars($r['PRPORT'])?></td>
    <td><?=htmlspecialchars($r['PRPAGE'])?></td>
    <td><?=htmlspecialchars($r['PRSEQ'])?></td>
    <td><?=htmlspecialchars($r['PRSEL'])?></td>
  </tr>
  <?php endforeach; endif; ?>
</table>
<?php if ($cnt > 0): ?>
<a class="btn" href="?confirm=FIX">Delete <?=$cnt?> Rows &mdash; Restore Bypass Mode</a>
<?php endif; ?>
<?php endif; ?>
</body></html>
