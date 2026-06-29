<?php
// FixQC01Syrold.php
// Adds SGINQ/SGDASH/SGDINT/SGRPT/SGSOP to SYROLD for QC01.
// QC01's SYROLD only has native portals — SG portals are missing entirely,
// so GetMenu's SYROLD->SYPORT join never produces SG menu rows.
// Uses WHERE NOT EXISTS — safe to re-run.
//
// Preview: https://portal.screen-graphics.com:5610/Custom/SG/FixQC01Syrold.php
// Execute: https://portal.screen-graphics.com:5610/Custom/SG/FixQC01Syrold.php?confirm=FIX

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

$role    = 'QC01';
$portals = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
$portIn  = "'" . implode("','", $portals) . "'";

// Current SYROLD for QC01
$current = qrows($conn,
    "SELECT RTRIM(RDROLE) AS RDROLE, RTRIM(RDPORT) AS RDPORT, RDSEQN"
  . " FROM SGHDSDATA.SYROLD WHERE RDROLE='$role' ORDER BY RDSEQN");

// Reference: find another role that already has SGINQ in SYROLD
$refRole = qval($conn,
    "SELECT RTRIM(RDROLE) FROM SGHDSDATA.SYROLD"
  . " WHERE RTRIM(RDPORT)='SGINQ' AND RTRIM(RDROLE)<>'$role'"
  . " FETCH FIRST 1 ROWS ONLY");

$refSeqs = array();
if ($refRole) {
    $refs = qrows($conn,
        "SELECT RTRIM(RDPORT) AS P, RDSEQN AS S"
      . " FROM SGHDSDATA.SYROLD"
      . " WHERE RDROLE='$refRole' AND RTRIM(RDPORT) IN ($portIn)");
    foreach ($refs as $r) $refSeqs[$r['P']] = $r['S'];
}

// Max existing RDSEQN for QC01 (to avoid conflicts if ref sequences clash)
$maxSeq = (int)qval($conn,
    "SELECT MAX(RDSEQN) FROM SGHDSDATA.SYROLD WHERE RDROLE='$role'");

// Set of existing QC01 RDSEQN values
$existingSeqs = array();
foreach ($current as $r) $existingSeqs[] = (float)$r['RDSEQN'];

// Build insert list: use reference role's sequences, or fallback to max+offset
$inserts = array();
$fallback = $maxSeq;
foreach ($portals as $p) {
    if (isset($refSeqs[$p])) {
        $seq = (float)$refSeqs[$p];
        // If that seq conflicts with an existing QC01 row, use fallback
        if (in_array($seq, $existingSeqs)) {
            $fallback++;
            $seq = $fallback;
        }
    } else {
        $fallback++;
        $seq = $fallback;
    }
    $inserts[] = array(
        "$role/$p seq=$seq",
        "INSERT INTO SGHDSDATA.SYROLD (RDROLE,RDPORT,RDSEQN)"
      . " SELECT '$role','$p',$seq FROM SYSIBM.SYSDUMMY1"
      . " WHERE NOT EXISTS ("
      . "   SELECT 1 FROM SGHDSDATA.SYROLD"
      . "   WHERE RDROLE='$role' AND RTRIM(RDPORT)='$p')"
    );
}

$done = false;
$log  = array();
$ok = $skip = $fail = 0;

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX') {
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
            $log[] = array('SKIP', $label, 'already in SYROLD');
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
<title>Fix QC01 SYROLD</title>
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
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.cards { display:flex; gap:14px; margin-bottom:16px; }
.card { background:#fff; border-radius:5px; padding:12px 22px; text-align:center;
        box-shadow:0 1px 4px rgba(0,0,0,.08); min-width:90px; }
.card.g { border-left:4px solid #2e7d32; }
.card.a { border-left:4px solid #e65100; }
.card.r { border-left:4px solid #c62828; }
.card .num { font-size:30px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:3px; }
.sect { font-weight:bold; font-size:13px; margin:18px 0 6px;
        color:#1a3d5c; border-bottom:2px solid #90caf9; padding-bottom:3px; }
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

<div class="hdr">Fix QC01 SYROLD — Add Missing SG Portals</div>

<div class="info">
  <strong>Root cause:</strong> QC01's SYROLD has no SG portals (SGINQ/SGDASH/SGDINT/SGRPT/SGSOP).
  GetMenu joins SYROLD to SYPORT — without SYROLD entries, SG portals never appear in the menu
  regardless of SYPORR state. SYPORR rows for QC01 are already correct.
  <?php if ($refRole): ?>
  <br>Reference role for RDSEQN values: <strong><?php echo htmlspecialchars($refRole); ?></strong>.
  <?php else: ?>
  <br>No reference role found with SGINQ — using sequences after QC01 max (<?php echo $maxSeq; ?>).
  <?php endif; ?>
</div>

<div class="sect">Current QC01 SYROLD (before fix)</div>
<table>
  <tr><th>RDROLE</th><th>RDPORT</th><th>RDSEQN</th></tr>
  <?php foreach ($current as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['RDROLE']); ?></td>
    <td><?php echo htmlspecialchars($r['RDPORT']); ?></td>
    <td><?php echo htmlspecialchars($r['RDSEQN']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<div class="sect">Rows to insert into SYROLD</div>
<table>
  <tr><th>Label</th><th>SQL</th></tr>
  <?php foreach ($inserts as $ins): ?>
  <tr>
    <td><?php echo htmlspecialchars($ins[0]); ?></td>
    <td style="font-size:10px"><?php echo htmlspecialchars($ins[1]); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php if ($done): ?>
<div class="<?php echo $fail > 0 ? 'warn' : 'ok'; ?>">
  <strong>Done.</strong>
  <?php echo $ok; ?> inserted, <?php echo $skip; ?> already existed,
  <?php echo $fail; ?> failed.
  <?php if ($ok > 0): ?>
  <strong>Log out and back in as a QC01 user to verify menus now appear.</strong>
  <?php endif; ?>
</div>
<div class="cards">
  <div class="card g"><div class="num"><?php echo $ok; ?></div><div class="lbl">Inserted</div></div>
  <div class="card a"><div class="num"><?php echo $skip; ?></div><div class="lbl">Existed</div></div>
  <div class="card r"><div class="num"><?php echo $fail; ?></div><div class="lbl">Failed</div></div>
</div>
<?php if (count($log)): ?>
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
<?php endif; ?>

<?php else: ?>
<div class="warn">
  <strong>Preview — nothing changed yet.</strong>
  Will add <?php echo count($inserts); ?> SYROLD rows for QC01.
  Rows already present are skipped.
</div>
<a class="btn" href="?confirm=FIX">Add <?php echo count($inserts); ?> Rows to SYROLD</a>
<?php endif; ?>

</body>
</html>
