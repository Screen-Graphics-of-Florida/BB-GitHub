<?php
// RestoreQC01Syporr.php
// Restores the 35 SYPORR rows for QC01 that were deleted this morning.
// 5 top-level rows (one per SG portal) + 30 sub-page rows (6 cats each).
// Uses WHERE NOT EXISTS — safe to run multiple times.
//
// Preview: https://portal.screen-graphics.com:5601/Custom/SG/RestoreQC01Syporr.php
// Execute: https://portal.screen-graphics.com:5601/Custom/SG/RestoreQC01Syporr.php?confirm=RESTORE

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role    = 'QC01';
$portals = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
$cats    = array('ACCT','INVMGMT','MFG','OE','PLN','PUR');

function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s); return $r ? db2_result($s, 0) : null;
}

$existing = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");

$inserts = array();

foreach ($portals as $p) {
    $prid = "$role/$p";
    $inserts[] = array(
        "Top-level $p",
        "INSERT INTO SGHDSDATA.SYPORR"
      . " (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)"
      . " SELECT '$role','$p','',1,'$prid','Y',CURRENT_TIMESTAMP,'RESTORE',''"
      . " FROM SYSIBM.SYSDUMMY1"
      . " WHERE NOT EXISTS ("
      . "   SELECT 1 FROM SGHDSDATA.SYPORR"
      . "   WHERE PRROLE='$role' AND RTRIM(PRPORT)='$p' AND RTRIM(PRPAGE)='')"
    );
}

foreach ($portals as $p) {
    $seq = 0;
    foreach ($cats as $c) {
        $seq++;
        $prid = $role . '/' . $p . '/' . $p . '/' . number_format($seq, 2);
        $inserts[] = array(
            "Sub $p/$c",
            "INSERT INTO SGHDSDATA.SYPORR"
          . " (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)"
          . " SELECT '$role','$p','$p',$seq,'$prid','Y',CURRENT_TIMESTAMP,'RESTORE',''"
          . " FROM SYSIBM.SYSDUMMY1"
          . " WHERE NOT EXISTS ("
          . "   SELECT 1 FROM SGHDSDATA.SYPORR"
          . "   WHERE PRROLE='$role' AND RTRIM(PRPORT)='$p'"
          . "   AND RTRIM(PRPAGE)='$p' AND PRSEQ=$seq)"
        );
    }
}

$done = false;
$log  = array();
$ok = $skip = $fail = 0;

if (isset($_GET['confirm']) && $_GET['confirm'] === 'RESTORE') {
    foreach ($inserts as $ins) {
        $label = $ins[0];
        $sql   = $ins[1];
        $s = @db2_exec($conn, $sql);
        if ($s === false) {
            $log[] = array('FAIL', $label, db2_stmt_errormsg());
            $fail++;
        } elseif (db2_num_rows($s) > 0) {
            $log[] = array('OK', $label, 'inserted');
            $ok++;
        } else {
            $log[] = array('SKIP', $label, 'already exists');
            $skip++;
        }
    }
    $done = true;
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Restore QC01 SYPORR</title>
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
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; font-size:12px; }
.cards { display:flex; gap:14px; margin-bottom:16px; }
.card { background:#fff; border-radius:5px; padding:12px 22px; text-align:center;
        box-shadow:0 1px 4px rgba(0,0,0,.08); min-width:90px; }
.card.g { border-left:4px solid #2e7d32; }
.card.a { border-left:4px solid #e65100; }
.card.r { border-left:4px solid #c62828; }
.card .num { font-size:30px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:3px; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
     padding:5px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.btn { display:inline-block; margin-top:10px; background:#1565c0; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#0d47a1; }
</style>
</head>
<body>

<div class="hdr">Restore QC01 SYPORR Rows</div>

<div class="info">
  Restores the <?php echo count($inserts); ?> SYPORR rows for QC01
  (5 top-level + 30 sub-page) deleted this morning.
  Uses WHERE NOT EXISTS &mdash; safe to re-run.
  Current row count: <strong><?php echo $existing; ?></strong>.
</div>

<?php if ($done): ?>
<div class="<?php echo $fail > 0 ? 'warn' : 'ok'; ?>">
  <strong>Done.</strong>
  <?php echo $ok; ?> inserted, <?php echo $skip; ?> already existed,
  <?php echo $fail; ?> failed. Log out and back in to verify QC01 menus.
</div>
<div class="cards">
  <div class="card g"><div class="num"><?php echo $ok; ?></div><div class="lbl">Inserted</div></div>
  <div class="card a"><div class="num"><?php echo $skip; ?></div><div class="lbl">Existed</div></div>
  <div class="card r"><div class="num"><?php echo $fail; ?></div><div class="lbl">Failed</div></div>
</div>
<table>
  <tr><th>Status</th><th>Item</th><th>Note</th></tr>
  <?php foreach ($log as $entry): ?>
  <tr>
    <td style="color:<?php echo $entry[0]==='OK'?'#2e7d32':($entry[0]==='FAIL'?'#c62828':'#999'); ?>;font-weight:bold">
      <?php echo htmlspecialchars($entry[0]); ?></td>
    <td><?php echo htmlspecialchars($entry[1]); ?></td>
    <td><?php echo htmlspecialchars($entry[2]); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php else: ?>
<div class="warn">
  <strong>Preview &mdash; nothing changed yet.</strong>
  Will insert up to <?php echo count($inserts); ?> SYPORR rows for QC01.
  Rows already present are skipped.
</div>
<a class="btn" href="?confirm=RESTORE">Restore <?php echo count($inserts); ?> Rows</a>
<?php endif; ?>

</body>
</html>
