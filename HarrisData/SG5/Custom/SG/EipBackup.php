<?php
// ============================================================
// SG Custom EIP Table Backup — SG5 Test Environment
// Dumps all SG portal rows from SYURLM, SYPORT, SYROLD, SYPORR
// as INSERT statements to a timestamped .sql file in Backup Files.
//
// URL:
//   https://portal.screen-graphics.com:5610/Custom/SG/EipBackup.php?confirm=BACKUP
// ============================================================

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'BACKUP') {
    die('<h2>SG EIP Backup</h2>'
      . '<p>Add <code>?confirm=BACKUP</code> to the URL to run.</p>');
}

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) {
    die('<pre>DB2 connection failed: '
      . htmlspecialchars(db2_conn_errormsg()) . '</pre>');
}

$pcodes  = ['SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'];
$cats    = ['ACCT','INVMGMT','MFG','OE','PLN','PUR'];

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
    'S5HDSDATA.SYURLM' => "SELECT * FROM S5HDSDATA.SYURLM WHERE FUID IN ($fuidList)",
    'S5HDSDATA.SYPORT' => "SELECT * FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pcodeList)",
    'S5HDSDATA.SYROLD' => "SELECT * FROM S5HDSDATA.SYROLD WHERE RDPORT IN ($pcodeList)",
    'S5HDSDATA.SYPORR' => "SELECT * FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pcodeList)",
];

$ts      = date('Ymd_His');
$outDir  = dirname(__FILE__) . '/../Backup Files';
if (!is_dir($outDir)) { $outDir = dirname(__FILE__); }
$outFile = $outDir . '/EipTables_backup_' . $ts . '.sql';

$lines   = [];
$summary = [];
$lines[] = "-- SG EIP Table Backup - S5HDSDATA";
$lines[] = "-- Generated: " . date('Y-m-d H:i:s');
$lines[] = "";

foreach ($queries as $tbl => $sql) {
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) {
        $lines[] = "-- FAILED to query $tbl: " . db2_stmt_errormsg();
        $summary[$tbl] = 'FAILED';
        continue;
    }
    $colCount = db2_num_fields($stmt);
    $cols = [];
    for ($i = 0; $i < $colCount; $i++) {
        $cols[] = db2_field_name($stmt, $i);
    }
    $colList = implode(', ', $cols);
    $count = 0;
    $lines[] = "-- $tbl";
    while ($row = db2_fetch_assoc($stmt)) {
        $vals = [];
        foreach ($row as $v) {
            if ($v === null) {
                $vals[] = 'NULL';
            } else {
                $vals[] = "'" . str_replace("'", "''", rtrim($v)) . "'";
            }
        }
        $lines[] = "INSERT INTO $tbl ($colList) VALUES (" . implode(', ', $vals) . ")";
        $count++;
    }
    $lines[] = "-- $tbl: $count rows";
    $lines[] = "";
    $summary[$tbl] = $count . ' rows';
}

db2_close($conn);

$written = file_put_contents($outFile, implode("\n", $lines));
$status  = ($written !== false) ? 'OK' : 'WRITE FAILED';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG EIP Backup</title>
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
        box-shadow: 0 2px 6px rgba(0,0,0,.06); }
th { background: #2a5a8c; color: #fff; padding: 8px 20px; text-align: left; font-size: 12px; }
td { padding: 6px 20px; font-size: 12px; border-bottom: 1px solid #f0f2f5; font-family: monospace; }
.ok   { color: #2e7d32; font-weight: bold; }
.fail { color: #c62828; font-weight: bold; }
</style>
</head>
<body>
<div class="header">
  <h2>SG EIP Table Backup</h2>
  <div class="sub">S5HDSDATA &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>
<table>
  <tr><th>Table</th><th>Rows</th></tr>
  <?php foreach ($summary as $tbl => $cnt): ?>
  <tr>
    <td><?= htmlspecialchars($tbl) ?></td>
    <td class="<?= strpos($cnt,'FAIL')!==false ? 'fail' : 'ok' ?>"><?= htmlspecialchars($cnt) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr>
    <td><strong>Output file</strong></td>
    <td class="<?= $status==='OK' ? 'ok' : 'fail' ?>"><?= htmlspecialchars(basename($outFile)) ?> — <?= $status ?></td>
  </tr>
</table>
</body>
</html>
