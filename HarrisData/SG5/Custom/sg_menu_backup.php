<?php
/**
 * SG Menu Backup — SG5 Test
 *
 * Captures the current state of SYURLM, SYPORT, SYROLD, SYPGMO
 * as a downloadable SQL file before the install script runs.
 *
 * Usage:
 *   Browse  : https://.../Custom/sg_menu_backup.php           (view on screen)
 *   Download: https://.../Custom/sg_menu_backup.php?dl=1      (save as .sql file)
 */

require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn     = $i5Connect->getConnection();
$download = isset($_GET['dl']) && $_GET['dl'] == '1';
$ts       = date('Ymd_His');

if ($download) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sg_menu_backup_' . $ts . '.sql"');
    echo "-- SG Menu Backup — SG5 Test\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Restore with: db2 -f this_file.sql\n\n";
}

function dumpTable($conn, $table, $download) {
    $r = db2_exec($conn, "SELECT * FROM $table ORDER BY 1, 2 FETCH FIRST 5000 ROWS ONLY");
    if (!$r) {
        $msg = "-- ERROR reading $table: " . db2_stmt_errormsg() . "\n";
        echo $download ? $msg : '<p class="err">' . htmlspecialchars($msg) . '</p>';
        return 0;
    }

    $cols    = array();
    $nCols   = db2_num_fields($r);
    for ($i = 0; $i < $nCols; $i++) {
        $cols[] = db2_field_name($r, $i);
    }
    $colList = implode(', ', $cols);
    $count   = 0;

    if ($download) {
        echo "-- TABLE: $table\n";
        echo "-- Columns: $colList\n\n";
    } else {
        echo '<h2>' . htmlspecialchars($table) . '</h2>';
        echo '<table><tr>';
        foreach ($cols as $c) echo '<th>' . htmlspecialchars($c) . '</th>';
        echo '</tr>';
    }

    while ($row = db2_fetch_assoc($r)) {
        if ($download) {
            $vals = array();
            foreach ($row as $k => $v) {
                if ($v === null)  { $vals[] = 'NULL'; }
                else              { $vals[] = "'" . str_replace("'", "''", trim((string)$v)) . "'"; }
            }
            echo "INSERT INTO $table ($colList) VALUES (" . implode(', ', $vals) . ");\n";
        } else {
            echo '<tr>';
            foreach ($row as $v) echo '<td>' . htmlspecialchars(trim((string)$v)) . '</td>';
            echo '</tr>';
        }
        $count++;
    }

    if ($download) {
        echo "\n-- $table: $count rows\n\n";
    } else {
        echo '</table><p>Rows: ' . $count . '</p>';
    }
    return $count;
}

if ($download) {
    dumpTable($conn, 'SYURLM',  true);
    dumpTable($conn, 'SYPORT',  true);
    dumpTable($conn, 'SYROLD',  true);
    dumpTable($conn, 'SYPGMO',  true);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG Menu Backup</title>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; padding: 12px; background: #f5f5f5; }
h1   { color: #2a5a8c; }
h2   { background: #2a5a8c; color: #fff; padding: 5px 10px; margin: 16px 0 6px; font-size: 13px; }
.btn { display: inline-block; padding: 9px 20px; border-radius: 4px; text-decoration: none;
       font-size: 13px; font-weight: bold; margin: 4px 4px 12px 0; background: #2a5a8c; color: #fff; }
table { border-collapse: collapse; width: 100%; margin-bottom: 6px; }
th    { background: #2a5a8c; color: #fff; padding: 3px 6px; text-align: left; }
td    { border: 1px solid #ddd; padding: 2px 6px; background: #fff; }
tr:nth-child(even) td { background: #f8f8f8; }
.ok  { background: #e8f4e8; border: 1px solid #9c9; border-radius: 4px; padding: 10px; margin: 10px 0; }
.err { color: #c00; font-weight: bold; }
</style>
</head>
<body>
<h1>SG Menu Backup — SG5 Test</h1>
<div class="ok">
  <strong>Backup instructions:</strong><br>
  1. Click <strong>Download SQL Backup</strong> — save the file to
     <code>W:\HarrisData\SG5\Custom\Backup Files\</code> before running the install script.<br>
  2. The tables shown below are what exists <em>right now</em>.
</div>
<p>
  <a href="?dl=1" class="btn">&#11015; Download SQL Backup</a>
</p>
<?php
$totals = array();
foreach (array('SYURLM','SYPORT','SYROLD','SYPGMO') as $tbl) {
    $totals[$tbl] = dumpTable($conn, $tbl, false);
}
?>
<p style="margin-top:16px;color:#555;">
  Total rows captured:
  <?php foreach ($totals as $t => $n) echo "$t=$n &nbsp; "; ?>
</p>
</body>
</html>
