<?php
// FixQC01Syporr.php
// Deletes all SYPORR rows for QC01 -> puts role in BYPASS mode.
// QC01A has 0 SYPORR rows (BYPASS) and works correctly.
// QC01 SYROLD contains only native portals so BYPASS mode is safe.
//
// Preview: https://portal.screen-graphics.com:5610/Custom/SG/FixQC01Syporr.php
// Execute: https://portal.screen-graphics.com:5610/Custom/SG/FixQC01Syporr.php?confirm=FIX

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

$totalBefore = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");

// Top-level ports currently in SYPORR for QC01
$topPorts = qrows($conn,
    "SELECT RTRIM(PRPORT) AS PORT, COUNT(*) AS CNT"
  . " FROM SGHDSDATA.SYPORR"
  . " WHERE PRROLE='$role'"
  . " GROUP BY RTRIM(PRPORT)"
  . " ORDER BY RTRIM(PRPORT)");

// SYROLD portals for QC01 (what will show after fix)
$syrold = qrows($conn,
    "SELECT RTRIM(RDPORT) AS PORT, RDSEQN AS SEQ"
  . " FROM SGHDSDATA.SYROLD WHERE RDROLE='$role' ORDER BY RDSEQN");

$done = false;
$deleted = 0;
$errMsg = '';

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX') {
    $s = @db2_exec($conn,
        "DELETE FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");
    if ($s === false) {
        $errMsg = db2_stmt_errormsg();
    } else {
        $deleted = db2_num_rows($s);
        $done = true;
    }
}

$totalAfter = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Fix QC01 SYPORR - SG5</title>
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
.err  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.sect { font-weight:bold; font-size:13px; margin:18px 0 6px;
        color:#1a3d5c; border-bottom:2px solid #90caf9; padding-bottom:3px; }
.cards { display:flex; gap:14px; margin-bottom:16px; flex-wrap:wrap; }
.card { background:#fff; border-radius:5px; padding:10px 22px; text-align:center;
        box-shadow:0 1px 4px rgba(0,0,0,.08); min-width:90px; }
.card.b { border-left:4px solid #c62828; }
.card.g { border-left:4px solid #2e7d32; }
.card .num { font-size:30px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:3px; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
     padding:5px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.btn { display:inline-block; margin-top:10px; background:#c62828; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#8e0000; }
</style>
</head>
<body>

<div class="hdr">Fix QC01 SYPORR &mdash; SG5 Test Only</div>

<div class="info">
  <strong>Root cause:</strong> QC01 has <?php echo $totalBefore; ?> SYPORR rows (WHITELIST mode).
  Native portals (MYCUSTOMPORTAL, CALENDAR, CUSTOMER, MFGMGMT, WAREHOUSEMANAGEMENT) are not
  in that whitelist, so GetMenu hides them. ITEM shows only because ITEM IS in the whitelist.<br><br>
  <strong>Fix:</strong> Delete all <?php echo $totalBefore; ?> SYPORR rows for QC01 &rarr; BYPASS mode.
  QC01A has 0 SYPORR rows (BYPASS) and works correctly. QC01's SYROLD contains only native portals,
  so BYPASS mode is safe &mdash; it will display exactly and only those portals.
</div>

<?php if ($done): ?>
<div class="ok">
  <strong>Done.</strong> <?php echo $deleted; ?> SYPORR rows deleted for QC01.
  SYPORR count now: <strong><?php echo $totalAfter; ?></strong>.
  QC01 is now in BYPASS mode. Log in as a QC01 user in SG5 (port 5610) to verify.
</div>
<div class="cards">
  <div class="card b"><div class="num"><?php echo $deleted; ?></div><div class="lbl">Rows Deleted</div></div>
  <div class="card g"><div class="num"><?php echo $totalAfter; ?></div><div class="lbl">Remaining</div></div>
</div>

<?php elseif ($errMsg): ?>
<div class="err"><strong>Error:</strong> <?php echo htmlspecialchars($errMsg); ?></div>

<?php else: ?>
<div class="warn">
  <strong>Preview &mdash; nothing changed yet.</strong>
  Will delete <strong><?php echo $totalBefore; ?></strong> SYPORR rows for QC01
  and put it in BYPASS mode.
</div>

<div class="sect">SYPORR rows to be deleted (<?php echo $totalBefore; ?> total, by port)</div>
<table>
  <tr><th>PRPORT</th><th>Row Count</th></tr>
  <?php foreach ($topPorts as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['PORT']); ?></td>
    <td><?php echo htmlspecialchars($r['CNT']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<div class="sect">QC01 SYROLD &mdash; portals that will display after fix (BYPASS mode)</div>
<table>
  <tr><th>PORT</th><th>SEQ</th></tr>
  <?php foreach ($syrold as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['PORT']); ?></td>
    <td><?php echo htmlspecialchars($r['SEQ']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<a class="btn" href="?confirm=FIX">Delete <?php echo $totalBefore; ?> SYPORR Rows</a>
<?php endif; ?>

</body>
</html>
