<?php
// ============================================================
// SG Custom EIP Table Backup — LIVE Environment (SGHDSDATA)
// Dumps all SG portal rows from SYURLM, SYPORT, SYROLD, SYPORR
// as INSERT statements. Writes to Backup Files; also displays
// SQL in a textarea in case the file write fails.
//
// Run this BEFORE PushSGInqOELive.php.
//
// URL:
//   https://portal.screen-graphics.com:5601/Custom/SG/EipBackupLive.php?confirm=BACKUP
// ============================================================

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'BACKUP') {
    die('<h2>SG EIP Live Backup</h2>'
      . '<p>Add <code>?confirm=BACKUP</code> to the URL to run.</p>');
}

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) {
    die('<pre>DB2 connection failed: '
      . htmlspecialchars(db2_conn_errormsg()) . '</pre>');
}

$schema = 'SGHDSDATA';

$pcodes = ['SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'];
$cats   = ['ACCT','INVMGMT','MFG','OE','PLN','PUR'];

$fuids = [];
foreach ($pcodes as $p) {
    $fuids[] = "'$p/PORTAL'";
    $fuids[] = "'$p/REPORT'";
    $fuids[] = "'$p'";
    foreach ($cats as $c) { $fuids[] = "'{$p}_{$c}'"; }
}
$fuidList  = implode(',', $fuids);
$pcodeList = "'" . implode("','", $pcodes) . "'";

$queries = [
    "$schema.SYURLM" => "SELECT * FROM $schema.SYURLM WHERE FUID IN ($fuidList)",
    "$schema.SYPORT" => "SELECT * FROM $schema.SYPORT WHERE FPPORT IN ($pcodeList)",
    "$schema.SYROLD" => "SELECT * FROM $schema.SYROLD WHERE RDPORT IN ($pcodeList)",
    "$schema.SYPORR" => "SELECT * FROM $schema.SYPORR WHERE PRPORT IN ($pcodeList)",
];

$ts      = date('Ymd_His');
$outDir  = dirname(__FILE__) . '/../Backup Files';
if (!is_dir($outDir)) { $outDir = dirname(__FILE__); }
$outFile = $outDir . '/EipTables_live_backup_' . $ts . '.sql';

$lines   = [];
$summary = [];
$errors  = [];
$lines[] = "-- SG EIP Table Backup - $schema (LIVE)";
$lines[] = '-- Generated: ' . date('Y-m-d H:i:s');
$lines[] = '';

foreach ($queries as $tbl => $sql) {
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) {
        $err           = db2_stmt_errormsg();
        $errors[$tbl]  = $err;
        $lines[]       = "-- FAILED to query $tbl: $err";
        $summary[$tbl] = 'FAILED';
        continue;
    }
    $colCount = db2_num_fields($stmt);
    $cols = [];
    for ($i = 0; $i < $colCount; $i++) { $cols[] = db2_field_name($stmt, $i); }
    $colList = implode(', ', $cols);
    $count = 0;
    $lines[] = "-- $tbl";
    while ($row = db2_fetch_assoc($stmt)) {
        $vals = [];
        foreach ($row as $v) {
            if ($v === null) { $vals[] = 'NULL'; }
            else             { $vals[] = "'" . str_replace("'", "''", rtrim($v)) . "'"; }
        }
        $lines[] = "INSERT INTO $tbl ($colList) VALUES (" . implode(', ', $vals) . ")";
        $count++;
    }
    $lines[] = "-- $tbl: $count rows";
    $lines[] = '';
    $summary[$tbl] = $count . ' rows';
}

db2_close($conn);

$sqlText = implode("\n", $lines);
$written = file_put_contents($outFile, $sqlText);
$status  = ($written !== false) ? 'OK' : 'WRITE FAILED — copy SQL from textarea below';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG EIP Live Backup</title>
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
table { border-collapse: collapse; background: #fff; border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,.06); margin-bottom: 20px; }
th { background: #2a5a8c; color: #fff; padding: 8px 20px;
     text-align: left; font-size: 12px; }
td { padding: 6px 20px; font-size: 12px;
     border-bottom: 1px solid #f0f2f5; font-family: monospace; }
.ok   { color: #2e7d32; font-weight: bold; }
.fail { color: #c62828; font-weight: bold; }
.warn { background: #fff8e1; border: 1px solid #ffe082; border-radius: 6px;
        padding: 12px 18px; font-size: 13px; margin-bottom: 16px; }
.err  { background: #fce4ec; border: 1px solid #f48fb1; border-radius: 6px;
        padding: 12px 18px; font-size: 12px; font-family: monospace;
        margin-bottom: 16px; white-space: pre-wrap; }
textarea { width: 100%; height: 300px; font-family: monospace; font-size: 11px;
           border: 1px solid #ccc; border-radius: 4px; padding: 8px;
           background: #fff; margin-top: 8px; }
.sql-label { font-size: 13px; font-weight: bold; margin-bottom: 4px; }
</style>
</head>
<body>
<div class="header">
  <h2>SG EIP Live Backup &mdash; SGHDSDATA</h2>
  <div class="sub"><?= date('Y-m-d H:i:s') ?></div>
</div>

<?php if (!empty($errors)): ?>
<div class="err"><strong>DB2 Errors:</strong>
<?php foreach ($errors as $tbl => $msg): ?><?= htmlspecialchars($tbl) ?>: <?= htmlspecialchars($msg) ?>
<?php endforeach; ?></div>
<?php endif; ?>

<table>
  <tr><th>Table</th><th>Result</th></tr>
  <?php foreach ($summary as $tbl => $cnt): ?>
  <tr>
    <td><?= htmlspecialchars($tbl) ?></td>
    <td class="<?= strpos($cnt,'FAIL')!==false ? 'fail' : 'ok' ?>"><?= htmlspecialchars($cnt) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr>
    <td><strong>Output file</strong></td>
    <td class="<?= strpos($status,'FAIL')!==false ? 'fail' : 'ok' ?>"><?= htmlspecialchars(basename($outFile)) ?> &mdash; <?= htmlspecialchars($status) ?></td>
  </tr>
</table>

<?php if (strpos($status, 'WRITE FAILED') !== false): ?>
<div class="warn">File write failed. Copy the SQL below and save it manually as your backup before running PushSGInqOELive.php.</div>
<div class="sql-label">Backup SQL (select all &rarr; copy &rarr; save as .sql file):</div>
<textarea readonly><?= htmlspecialchars($sqlText) ?></textarea>
<?php endif; ?>

</body>
</html>
