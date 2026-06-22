<?php
// Add Customer Service Inquiry Training Guide to SG Documentation portal
// SGHDSDATA (EIP live environment)
// PPTX file: W:\HarrisData\EIP\Custom\SG\
//            Customer_Service_Inquiry_Training_Guide_06-2026.pptx
//
// URL: https://portal.screen-graphics.com:5601/Custom/SG/AddCSInqTrainingToSGDoc.php?confirm=ADD

$schema = 'SGHDSDATA';
$fuid   = 'SG_DOCUMENTATION/CS INQUIRY TRAINING';
$fuurl  = '@@homeURL/custom/SG/Customer_Service_Inquiry_Training_Guide_06-2026.pptx';

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB2 connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

// ── BACKUP: always show current state first ───────────────────────────────────
$backup = [];
$stmt = db2_exec($conn,
    "SELECT FUID, FUDESC, FUTITL, FUTRGT, FURESV, FUDESCU, LEFT(FUURL,100) AS FUURL
     FROM $schema.SYURLM
     WHERE FUID LIKE 'SG_DOCUMENTATION%'
     ORDER BY FUID");
while ($row = db2_fetch_assoc($stmt)) $backup[] = $row;

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'ADD') {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8">'
       . '<title>Backup — SG Doc SYURLM</title>'
       . '<style>body{font-family:Arial;background:#f0f2f5;padding:24px}'
       . '.hdr{background:linear-gradient(135deg,#2a5a8c,#1a3d5c);color:#fff;'
       .      'padding:14px 24px;border-radius:6px;border-bottom:3px solid #f90;margin-bottom:20px}'
       . 'table{border-collapse:collapse;background:#fff;border-radius:6px;'
       .       'box-shadow:0 2px 6px rgba(0,0,0,.06);width:100%;margin-bottom:16px}'
       . 'th{background:#2a5a8c;color:#fff;padding:7px 12px;text-align:left;font-size:12px}'
       . 'td{padding:5px 12px;font-size:12px;border-bottom:1px solid #f0f2f5;font-family:monospace}'
       . '.rollback{background:#fff3cd;border:1px solid #e0a800;border-radius:6px;'
       .           'padding:14px 18px;font-family:monospace;font-size:13px;margin-bottom:16px}'
       . '.btn{display:inline-block;margin-top:16px;padding:10px 24px;background:#2a5a8c;'
       .      'color:#fff;text-decoration:none;border-radius:4px;font-size:14px}'
       . '</style></head><body>';
    echo '<div class="hdr"><h2>BACKUP — SG Documentation SYURLM</h2>'
       . '<div style="font-size:11px;opacity:.75;margin-top:3px">' . $schema
       . ' &nbsp;|&nbsp; ' . date('Y-m-d H:i:s') . '</div></div>';
    echo '<p style="margin-bottom:12px"><strong>Screenshot or save these rows before proceeding.</strong>'
       . ' They are the current SG_DOCUMENTATION entries in ' . $schema . '.SYURLM.</p>';
    echo '<table><tr>';
    if (!empty($backup)) {
        foreach (array_keys($backup[0]) as $col) echo '<th>' . $col . '</th>';
        echo '</tr>';
        foreach ($backup as $r) {
            echo '<tr>';
            foreach ($r as $v) echo '<td>' . htmlspecialchars(trim((string)$v)) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<td><em>No rows found</em></td>';
    }
    echo '</table>';
    echo '<div class="rollback"><strong>Rollback SQL (run if you need to undo):</strong><br><br>'
       . "DELETE FROM $schema.SYURLM WHERE FUID = '$fuid'</div>";
    echo '<a class="btn" href="?confirm=ADD">Backup noted — run the INSERT now</a>';
    echo '</body></html>';
    db2_close($conn);
    exit;
}

$log = [];

function runSql($conn, $label, $sql, &$log) {
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) {
        $log[] = ['FAIL', $label, db2_stmt_errormsg()];
    } else {
        $n = db2_num_rows($stmt);
        $log[] = $n > 0
            ? ['OK',   $label, 'inserted']
            : ['SKIP', $label, 'already exists'];
    }
}

// ── INSERT new SYURLM row ─────────────────────────────────────────────────────
runSql($conn, "SYURLM $fuid",
    "INSERT INTO $schema.SYURLM
         (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,
          FUTSTP,FUTSUS,FUTSWS,FUTSPT)
     SELECT '$fuid',
            'CS Inq Training',
            'Customer Service Inquiry Training Guide',
            '_blank',
            '$fuurl',
            '','',
            'CUSTOMER SERVICE INQUIRY TRAINING GUIDE',
            CURRENT_TIMESTAMP,'BBUSCH','',''
     FROM SYSIBM.SYSDUMMY1
     WHERE NOT EXISTS (
         SELECT 1 FROM $schema.SYURLM WHERE FUID='$fuid')",
    $log);

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Add CS Inquiry Training — SG Doc</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 24px; }
.hdr { background: linear-gradient(135deg,#2a5a8c,#1a3d5c);
       color:#fff; padding:14px 24px; border-radius:6px;
       border-bottom:3px solid #f90; margin-bottom:20px; }
.hdr h2 { font-size:18px; }
.hdr .sub { font-size:11px; opacity:.75; margin-top:3px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:6px;
        overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,.06); margin-bottom:20px; }
th { background:#2a5a8c; color:#fff; padding:7px 12px; text-align:left; font-size:12px; }
td { padding:5px 12px; font-size:12px; border-bottom:1px solid #f0f2f5;
     font-family:monospace; }
tr.ok   td:first-child { color:#2e7d32; font-weight:bold; }
tr.skip td:first-child { color:#888; }
tr.fail td             { color:#c62828; font-weight:bold; }
h3 { font-size:14px; color:#2a5a8c; margin:20px 0 6px; }
</style>
</head>
<body>
<div class="hdr">
  <h2>Add CS Inquiry Training Guide — SG Documentation</h2>
  <div class="sub"><?= $schema ?> &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<h3>Result</h3>
<table>
  <tr><th>Status</th><th>Item</th><th>Note</th></tr>
  <?php foreach ($log as list($s,$l,$n)): ?>
  <tr class="<?= strtolower($s) ?>">
    <td><?= $s ?></td>
    <td><?= htmlspecialchars($l) ?></td>
    <td><?= htmlspecialchars($n) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php if ($log[0][0] === 'OK'): ?>
<p style="color:#2e7d32;font-weight:bold">
  Done. Go to the SG Documentation page in EIP and verify
  "Customer Service Inquiry Training Guide" appears as a new link.
</p>
<?php endif; ?>
</body>
</html>
