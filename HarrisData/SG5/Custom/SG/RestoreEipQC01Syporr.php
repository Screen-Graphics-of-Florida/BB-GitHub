<?php
// RestoreEipQC01Syporr.php
// Restores the 478 SGHDSDATA.SYPORR rows for QC01 that were
// accidentally deleted by FixQC01Syporr.php.
// Copies rows from S5HDSDATA.SYPORR (still intact) into SGHDSDATA.SYPORR.
// Uses WHERE NOT EXISTS -- safe to re-run.
//
// Preview: https://portal.screen-graphics.com:5610/Custom/SG/RestoreEipQC01Syporr.php
// Execute: https://portal.screen-graphics.com:5610/Custom/SG/RestoreEipQC01Syporr.php?confirm=RESTORE

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s); return $r ? db2_result($s, 0) : null;
}

$role = 'QC01';

$s5Count = (int)qval($conn,
    "SELECT COUNT(*) FROM S5HDSDATA.SYPORR WHERE PRROLE='$role'");
$sgBefore = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");
$toInsert = (int)qval($conn,
    "SELECT COUNT(*) FROM S5HDSDATA.SYPORR s"
  . " WHERE s.PRROLE='$role'"
  . " AND NOT EXISTS ("
  . "   SELECT 1 FROM SGHDSDATA.SYPORR g"
  . "   WHERE g.PRROLE=s.PRROLE"
  . "   AND RTRIM(g.PRPORT)=RTRIM(s.PRPORT)"
  . "   AND RTRIM(g.PRPAGE)=RTRIM(s.PRPAGE)"
  . "   AND g.PRSEQ=s.PRSEQ)");

$done    = false;
$inserted = 0;
$errMsg  = '';

if (isset($_GET['confirm']) && $_GET['confirm'] === 'RESTORE') {
    $s = @db2_exec($conn,
        "INSERT INTO SGHDSDATA.SYPORR"
      . " (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)"
      . " SELECT s.PRROLE,s.PRPORT,s.PRPAGE,s.PRSEQ,s.PRID,"
      . "   s.PRSEL,CURRENT_TIMESTAMP,s.PRTSUS,s.PRTSPT"
      . " FROM S5HDSDATA.SYPORR s"
      . " WHERE s.PRROLE='$role'"
      . " AND NOT EXISTS ("
      . "   SELECT 1 FROM SGHDSDATA.SYPORR g"
      . "   WHERE g.PRROLE=s.PRROLE"
      . "   AND RTRIM(g.PRPORT)=RTRIM(s.PRPORT)"
      . "   AND RTRIM(g.PRPAGE)=RTRIM(s.PRPAGE)"
      . "   AND g.PRSEQ=s.PRSEQ)");
    if ($s === false) {
        $errMsg = db2_stmt_errormsg();
    } else {
        $inserted = db2_num_rows($s);
        $done = true;
    }
}

$sgAfter = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Restore EIP Live QC01 SYPORR</title>
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
.err  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.cards { display:flex; gap:14px; margin-bottom:16px; flex-wrap:wrap; }
.card { background:#fff; border-radius:5px; padding:10px 22px; text-align:center;
        box-shadow:0 1px 4px rgba(0,0,0,.08); min-width:90px; }
.card.g { border-left:4px solid #2e7d32; }
.card.b { border-left:4px solid #1565c0; }
.card .num { font-size:30px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:3px; }
.btn { display:inline-block; margin-top:10px; background:#7b1fa2; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#4a0072; }
</style>
</head>
<body>

<div class="hdr">Restore EIP Live QC01 SYPORR &mdash; Copy from S5HDSDATA</div>

<div class="info">
  <strong>Source:</strong> S5HDSDATA.SYPORR has <strong><?php echo $s5Count; ?></strong>
  rows for QC01 (intact).<br>
  <strong>Target:</strong> SGHDSDATA.SYPORR currently has <strong><?php echo $sgBefore; ?></strong>
  rows for QC01 (was accidentally deleted).<br>
  <strong>Rows to insert:</strong> <strong><?php echo $toInsert; ?></strong>
  (WHERE NOT EXISTS prevents duplicates).
</div>

<?php if ($done): ?>
<div class="ok">
  <strong>Done.</strong> <?php echo $inserted; ?> rows inserted into SGHDSDATA.SYPORR for QC01.
  SGHDSDATA.SYPORR count now: <strong><?php echo $sgAfter; ?></strong>.
  EIP Live QC01 SYPORR is restored.
</div>
<div class="cards">
  <div class="card g"><div class="num"><?php echo $inserted; ?></div><div class="lbl">Inserted</div></div>
  <div class="card b"><div class="num"><?php echo $sgAfter; ?></div><div class="lbl">Total Now</div></div>
</div>

<?php elseif ($errMsg): ?>
<div class="err"><strong>Error:</strong> <?php echo htmlspecialchars($errMsg); ?></div>

<?php else: ?>
<div class="warn">
  <strong>Preview &mdash; nothing changed yet.</strong>
  Will copy <strong><?php echo $toInsert; ?></strong> rows from S5HDSDATA.SYPORR
  into SGHDSDATA.SYPORR for QC01.
  SGHDSDATA count will go from <?php echo $sgBefore; ?> to approximately <?php echo $sgBefore + $toInsert; ?>.
</div>
<a class="btn" href="?confirm=RESTORE">Restore <?php echo $toInsert; ?> Rows to SGHDSDATA.SYPORR</a>
<?php endif; ?>

</body>
</html>
