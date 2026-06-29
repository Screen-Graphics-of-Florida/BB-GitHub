<?php
// ReverseQC01NativePortals.php
// Reverses FixQC01NativePortals.php:
//   Step 1 — delete the 36 native portal rows stamped SGFIX
//   Step 2 — restore the 35 SG portal rows that were deleted
//
// Preview: https://portal.screen-graphics.com:5610/Custom/SG/ReverseQC01NativePortals.php
// Execute: https://portal.screen-graphics.com:5610/Custom/SG/ReverseQC01NativePortals.php?confirm=REVERSE

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s); return $r ? db2_result($s, 0) : null;
}
function qrows($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return array();
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    return $out;
}

$role = 'QC01';

// Rows to delete: native portal rows we inserted (stamped SGFIX)
$sgfixCount = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR"
  . " WHERE PRROLE='$role' AND RTRIM(PRTSUS)='SGFIX'");

// Rows to restore: SG portal rows deleted by step 1 of fix
$portals = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
$cats    = array('ACCT','INVMGMT','MFG','OE','PLN','PUR');

$restoreInserts = array();
foreach ($portals as $p) {
    $prid = "$role/$p";
    $restoreInserts[] = array(
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
        $restoreInserts[] = array(
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

$currentTotal = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");

$done = false;
$log  = array();
$delOk = $insOk = $insSkip = $insFail = 0;

if (isset($_GET['confirm']) && $_GET['confirm'] === 'REVERSE') {

    // Step 1: delete SGFIX rows
    $s = @db2_exec($conn,
        "DELETE FROM SGHDSDATA.SYPORR"
      . " WHERE PRROLE='$role' AND RTRIM(PRTSUS)='SGFIX'");
    if ($s !== false) {
        $delOk = db2_num_rows($s);
        $log[] = array('DEL', "Deleted SGFIX native portal rows", "$delOk deleted");
    } else {
        $log[] = array('FAIL', "Delete SGFIX rows", db2_stmt_errormsg());
    }

    // Step 2: restore SG portal rows
    foreach ($restoreInserts as $ins) {
        $label = $ins[0];
        $sql   = $ins[1];
        $s = @db2_exec($conn, $sql);
        if ($s === false) {
            $log[] = array('FAIL', $label, db2_stmt_errormsg());
            $insFail++;
        } elseif (db2_num_rows($s) > 0) {
            $log[] = array('OK', $label, 'inserted');
            $insOk++;
        } else {
            $log[] = array('SKIP', $label, 'already exists');
            $insSkip++;
        }
    }
    $done = true;
}

$afterTotal = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Reverse QC01 Native Portals Fix</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#7b1fa2,#4a0072); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.warn { background:#fff8e1; border:1px solid #ffe082; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.cards { display:flex; gap:14px; margin-bottom:16px; flex-wrap:wrap; }
.card { background:#fff; border-radius:5px; padding:10px 20px; text-align:center;
        box-shadow:0 1px 4px rgba(0,0,0,.08); min-width:80px; }
.card.b { border-left:4px solid #1565c0; }
.card.g { border-left:4px solid #2e7d32; }
.card.a { border-left:4px solid #e65100; }
.card.r { border-left:4px solid #c62828; }
.card .num { font-size:28px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:2px; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#7b1fa2; color:#fff !important; font-weight:bold !important;
     padding:5px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.btn { display:inline-block; margin-top:10px; background:#7b1fa2; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#4a0072; }
</style>
</head>
<body>

<div class="hdr">Reverse QC01 — Undo FixQC01NativePortals</div>

<div class="info">
  Current SYPORR row count for QC01: <strong><?php echo $currentTotal; ?></strong>.<br>
  SGFIX rows to delete: <strong><?php echo $sgfixCount; ?></strong>.<br>
  SG portal rows to restore: <strong><?php echo count($restoreInserts); ?></strong>
  (SGINQ/SGDASH/SGDINT/SGRPT/SGSOP top-level + sub-pages).
</div>

<?php if ($done): ?>
<div class="<?php echo $insFail > 0 ? 'warn' : 'ok'; ?>">
  <strong>Done.</strong>
  <?php echo $delOk; ?> SGFIX rows deleted,
  <?php echo $insOk; ?> SG portal rows restored,
  <?php echo $insSkip; ?> already existed,
  <?php echo $insFail; ?> failed.
  SYPORR count now: <strong><?php echo $afterTotal; ?></strong>.
</div>
<div class="cards">
  <div class="card b"><div class="num"><?php echo $delOk; ?></div><div class="lbl">Deleted</div></div>
  <div class="card g"><div class="num"><?php echo $insOk; ?></div><div class="lbl">Restored</div></div>
  <div class="card a"><div class="num"><?php echo $insSkip; ?></div><div class="lbl">Existed</div></div>
  <div class="card r"><div class="num"><?php echo $insFail; ?></div><div class="lbl">Failed</div></div>
</div>
<table>
  <tr><th>Status</th><th>Item</th><th>Note</th></tr>
  <?php foreach ($log as $entry): ?>
  <tr>
    <td style="color:<?php
      echo $entry[0]==='OK'||$entry[0]==='DEL' ? '#2e7d32'
         : ($entry[0]==='FAIL' ? '#c62828' : '#999');
    ?>;font-weight:bold"><?php echo htmlspecialchars($entry[0]); ?></td>
    <td><?php echo htmlspecialchars($entry[1]); ?></td>
    <td><?php echo htmlspecialchars($entry[2]); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php else: ?>
<div class="warn">
  <strong>Preview — nothing changed yet.</strong>
  Will delete <?php echo $sgfixCount; ?> SGFIX rows and restore
  <?php echo count($restoreInserts); ?> SG portal rows.
</div>
<a class="btn" href="?confirm=REVERSE">Reverse the Fix</a>
<?php endif; ?>

</body>
</html>
