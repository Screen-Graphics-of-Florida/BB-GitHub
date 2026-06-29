<?php
// FixQC01NativePortals.php
// QC01 is in whitelist mode (478 SYPORR rows). ITEM shows because it has a
// SYPORR entry. MYCUSTOMPORTAL, CALENDAR, CUSTOMER, MFGMGMT, WAREHOUSEMANAGEMENT
// are in SYROLD but missing from SYPORR — whitelist mode hides them.
//
// SG5 also has 35 wrong SG portal rows (SGINQ/SGDASH/SGDINT/SGRPT/SGSOP)
// added in error — those are deleted first.
//
// Fix: delete wrong SG rows, then insert SYPORR rows for the 5 missing
// native portals by reading their FPSEQ values from SYPORT.
//
// Preview: https://portal.screen-graphics.com:5610/Custom/SG/FixQC01NativePortals.php
// Execute: https://portal.screen-graphics.com:5610/Custom/SG/FixQC01NativePortals.php?confirm=FIX

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

// Portals to add (ITEM already has SYPORR rows; 9999999999 is not a real portal)
$missing = array('MYCUSTOMPORTAL','CALENDAR','CUSTOMER','MFGMGMT','WAREHOUSEMANAGEMENT');
$portIn  = "'" . implode("','", $missing) . "'";

// Wrong SG portal rows to remove from SG5
$wrongPorts = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
$wrongIn    = "'" . implode("','", $wrongPorts) . "'";

// Current SYPORR state
$totalRows  = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");
$wrongRows  = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR"
  . " WHERE PRROLE='$role' AND RTRIM(PRPORT) IN ($wrongIn)");
$missingHas = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR"
  . " WHERE PRROLE='$role' AND RTRIM(PRPORT) IN ($portIn)");

// SYPORT entries for the 5 missing portals (top-level + own-page sub-header)
$syport = qrows($conn,
    "SELECT RTRIM(FPPORT) AS FPPORT, RTRIM(FPPAGE) AS FPPAGE,"
  . " FPSEQ, RTRIM(FPID) AS FPID, RTRIM(FPDESC) AS FPDESC"
  . " FROM SGHDSDATA.SYPORT"
  . " WHERE RTRIM(FPPORT) IN ($portIn)"
  . " AND (RTRIM(FPPAGE)='' OR RTRIM(FPPAGE)=RTRIM(FPPORT))"
  . " ORDER BY FPPORT, FPPAGE, FPSEQ");

$done = false;
$log  = array();
$delOk = $insOk = $insFail = $insSkip = 0;

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX') {

    // Step 1: delete wrong SG portal rows
    if ($wrongRows > 0) {
        $s = @db2_exec($conn,
            "DELETE FROM SGHDSDATA.SYPORR"
          . " WHERE PRROLE='$role' AND RTRIM(PRPORT) IN ($wrongIn)");
        if ($s !== false) {
            $n = db2_num_rows($s);
            $log[] = array('DEL', "Removed wrong SG portal rows", "$n deleted");
            $delOk = $n;
        } else {
            $log[] = array('FAIL', "Delete wrong SG rows", db2_stmt_errormsg());
        }
    } else {
        $log[] = array('SKIP', "No wrong SG rows to delete", '0 rows');
    }

    // Step 2: insert missing native portal rows from SYPORT
    foreach ($syport as $sp) {
        $port  = $sp['FPPORT'];
        $page  = $sp['FPPAGE'];
        $seq   = $sp['FPSEQ'];
        $fpid  = $sp['FPID'];
        $label = "$port / page='$page' seq=$seq";

        $sql =
            "INSERT INTO SGHDSDATA.SYPORR"
          . " (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)"
          . " SELECT '$role','$port','$page',$seq,'$fpid','Y',"
          . "   CURRENT_TIMESTAMP,'SGFIX',''"
          . " FROM SYSIBM.SYSDUMMY1"
          . " WHERE NOT EXISTS ("
          . "   SELECT 1 FROM SGHDSDATA.SYPORR"
          . "   WHERE PRROLE='$role' AND RTRIM(PRPORT)='$port'"
          . "   AND RTRIM(PRPAGE)='$page' AND PRSEQ=$seq)";

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

$afterRows = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Fix QC01 Native Portals</title>
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
.sect { font-weight:bold; font-size:13px; margin:14px 0 5px;
        color:#1a3d5c; border-bottom:2px solid #90caf9; padding-bottom:3px; }
.cards { display:flex; gap:14px; margin-bottom:16px; flex-wrap:wrap; }
.card { background:#fff; border-radius:5px; padding:10px 20px; text-align:center;
        box-shadow:0 1px 4px rgba(0,0,0,.08); min-width:80px; }
.card.g { border-left:4px solid #2e7d32; }
.card.a { border-left:4px solid #e65100; }
.card.r { border-left:4px solid #c62828; }
.card.b { border-left:4px solid #1565c0; }
.card .num { font-size:28px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:2px; }
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

<div class="hdr">Fix QC01 — Add Missing Native Portals to SYPORR</div>

<div class="info">
  <strong>Problem:</strong> QC01 is in whitelist mode (<?php echo $totalRows; ?> SYPORR rows).
  ITEM shows because it has SYPORR entries. MYCUSTOMPORTAL, CALENDAR, CUSTOMER,
  MFGMGMT, WAREHOUSEMANAGEMENT are in SYROLD but missing from SYPORR &mdash;
  whitelist mode filters them out.<br>
  <strong>SG5 also has <?php echo $wrongRows; ?> wrong SG portal rows</strong>
  (SGINQ/SGDASH/SGDINT/SGRPT/SGSOP) added in error &mdash; deleted in step 1.<br>
  SYPORR rows for missing portals already present: <?php echo $missingHas; ?>.
</div>

<div class="sect">SYPORT entries found for missing portals (rows to be inserted)</div>
<?php if (count($syport) == 0): ?>
<div class="warn">No SYPORT entries found for those portals &mdash;
  MYCUSTOMPORTAL/CALENDAR/CUSTOMER/MFGMGMT/WAREHOUSEMANAGEMENT
  may not be in SYPORT.</div>
<?php else: ?>
<table>
  <tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPID</th><th>FPDESC</th></tr>
  <?php foreach ($syport as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['FPPORT']); ?></td>
    <td><?php echo htmlspecialchars($r['FPPAGE']); ?></td>
    <td><?php echo htmlspecialchars($r['FPSEQ']); ?></td>
    <td><?php echo htmlspecialchars($r['FPID']); ?></td>
    <td><?php echo htmlspecialchars($r['FPDESC']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ($done): ?>
<div class="<?php echo $insFail > 0 ? 'warn' : 'ok'; ?>">
  <strong>Done.</strong>
  <?php echo $delOk; ?> wrong rows deleted,
  <?php echo $insOk; ?> native portal rows inserted,
  <?php echo $insSkip; ?> already existed,
  <?php echo $insFail; ?> failed.
  SYPORR row count: <?php echo $afterRows; ?>.
  <?php if ($insOk > 0 || $delOk > 0): ?>
  <strong>Log out and back in as QC01 to verify all native portals now appear.</strong>
  <?php endif; ?>
</div>
<div class="cards">
  <div class="card b"><div class="num"><?php echo $delOk; ?></div><div class="lbl">Wrong Deleted</div></div>
  <div class="card g"><div class="num"><?php echo $insOk; ?></div><div class="lbl">Inserted</div></div>
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
  <strong>Preview &mdash; nothing changed yet.</strong>
  Step 1: delete <?php echo $wrongRows; ?> wrong SG portal rows.
  Step 2: insert up to <?php echo count($syport); ?> native portal SYPORR rows.
</div>
<a class="btn" href="?confirm=FIX">Run Fix</a>
<?php endif; ?>

</body>
</html>
