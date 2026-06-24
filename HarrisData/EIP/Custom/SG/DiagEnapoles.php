<?php
$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . db2_conn_errormsg());

$role = isset($_GET['role']) ? strtoupper(trim($_GET['role'])) : 'ENAPOLES';

function qry($conn, $sql) {
    $rows = array();
    $s = db2_exec($conn, $sql);
    if ($s === false) return array('__error' => db2_stmt_errormsg());
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}

$syrold = qry($conn,
    "SELECT RDROLE, RDPORT, RDSEQN FROM SGHDSDATA.SYROLD "
  . "WHERE RDROLE='$role' ORDER BY RDSEQN");

$syporr = qry($conn,
    "SELECT PRROLE, PRPORT, PRPAGE, PRSEQ, PRID, PRSEL, PRTSUS, PRTSPT "
  . "FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE='$role' ORDER BY PRPORT, PRPAGE, PRSEQ");

$sgFuids = array();
foreach ($sgPorts as $p) {
    $sgFuids[] = "'$p/PORTAL'";
    foreach (array('ACCT','INVMGMT','MFG','OE','PLN','PUR') as $c) {
        $sgFuids[] = "'{$p}_{$c}'";
    }
}
$syurlm = qry($conn,
    "SELECT FUID, FUDESC, FUURL, FUTSPT FROM SGHDSDATA.SYURLM "
  . "WHERE FUID IN (" . implode(',', $sgFuids) . ") ORDER BY FUID");

$syport = qry($conn,
    "SELECT FPPORT, FPPAGE, FPSEQ, FPID, FPTITL, FPTSPT FROM SGHDSDATA.SYPORT "
  . "WHERE FPPORT IN ('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP') ORDER BY FPPORT, FPPAGE, FPSEQ");

db2_close($conn);

$sgPorts = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');
$syporr_ports = array();
foreach ($syporr as $r) {
    if (!isset($r['__error'])) $syporr_ports[rtrim($r['PRPORT'])] = true;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Diag: <?= htmlspecialchars($role) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:24px; }
h2 { background:#2a5a8c; color:#fff; padding:10px 18px; border-radius:6px 6px 0 0;
     border-bottom:3px solid #f90; font-size:16px; }
.box { background:#fff; border-radius:0 0 6px 6px;
       box-shadow:0 2px 6px rgba(0,0,0,.08); margin-bottom:20px; }
table { width:100%; border-collapse:collapse; }
th { background:#e8eef5; padding:6px 12px; text-align:left; font-size:11px;
     border-bottom:2px solid #c0d0e0; }
td { padding:5px 12px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
tr:hover td { background:#fafcff; }
.sg { background:#e8f5e9 !important; }
.missing { background:#fce4ec !important; }
.badge { display:inline-block; padding:2px 7px; border-radius:10px;
         font-size:10px; font-weight:bold; }
.ok  { background:#c8e6c9; color:#1b5e20; }
.no  { background:#ffcdd2; color:#b71c1c; }
.hdr { padding:10px 16px; font-size:13px; }
</style>
</head>
<body>

<form style="margin-bottom:16px">
  <input name="role" value="<?= htmlspecialchars($role) ?>"
         style="padding:6px 10px;font-size:13px;border:1px solid #ccc;border-radius:4px;width:200px">
  <button style="padding:6px 14px;font-size:13px;background:#2a5a8c;color:#fff;
                 border:none;border-radius:4px;cursor:pointer">Check Role</button>
</form>

<!-- SG Portal Coverage -->
<h2>SG Portal Coverage for <?= htmlspecialchars($role) ?></h2>
<div class="box">
  <table>
    <tr><th>Portal</th><th>In SYROLD</th><th>In SYPORR</th></tr>
    <?php
    $syrold_ports = array();
    foreach ($syrold as $r) { if (!isset($r['__error'])) $syrold_ports[rtrim($r['RDPORT'])] = true; }
    foreach ($sgPorts as $p):
        $inSyrold = isset($syrold_ports[$p]);
        $inSyporr = isset($syporr_ports[$p]);
        $cls = (!$inSyrold || !$inSyporr) ? ' class="missing"' : ' class="sg"';
    ?>
    <tr<?= $cls ?>>
      <td><?= $p ?></td>
      <td><span class="badge <?= $inSyrold?'ok':'no' ?>"><?= $inSyrold?'YES':'MISSING' ?></span></td>
      <td><span class="badge <?= $inSyporr?'ok':'no' ?>"><?= $inSyporr?'YES':'MISSING' ?></span></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<!-- SYPORR rows -->
<h2>SYPORR rows for <?= htmlspecialchars($role) ?> (<?= count($syporr) ?> rows)</h2>
<div class="box">
<?php if (isset($syporr[0]['__error'])): ?>
  <div class="hdr" style="color:red"><?= htmlspecialchars($syporr[0]['__error']) ?></div>
<?php elseif (empty($syporr)): ?>
  <div class="hdr"><strong>0 rows</strong> — role is in BYPASS mode (sees all portals in SYROLD)</div>
<?php else: ?>
  <table>
    <tr><th>PRPORT</th><th>PRPAGE</th><th>PRSEQ</th><th>PRID</th><th>PRSEL</th><th>PRTSUS</th><th>PRTSPT</th></tr>
    <?php foreach ($syporr as $r):
        $port = rtrim($r['PRPORT']);
        $cls  = in_array($port, $sgPorts) ? ' class="sg"' : '';
    ?>
    <tr<?= $cls ?>>
      <td><?= htmlspecialchars(rtrim($r['PRPORT'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['PRPAGE'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['PRSEQ'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['PRID'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['PRSEL'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['PRTSUS'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['PRTSPT'])) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>
</div>

<!-- SYROLD rows -->
<h2>SYROLD rows for <?= htmlspecialchars($role) ?> (<?= count($syrold) ?> rows)</h2>
<div class="box">
<?php if (empty($syrold)): ?>
  <div class="hdr">0 rows</div>
<?php else: ?>
  <table>
    <tr><th>RDROLE</th><th>RDPORT</th><th>RDSEQN</th></tr>
    <?php foreach ($syrold as $r):
        $port = rtrim($r['RDPORT']);
        $cls  = in_array($port, $sgPorts) ? ' class="sg"' : '';
    ?>
    <tr<?= $cls ?>>
      <td><?= htmlspecialchars(rtrim($r['RDROLE'])) ?></td>
      <td><?= htmlspecialchars($port) ?></td>
      <td><?= htmlspecialchars(rtrim($r['RDSEQN'])) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>
</div>

<!-- SYURLM for SG portals -->
<h2>SYURLM — SG Portal entries (<?= count($syurlm) ?> rows)</h2>
<div class="box">
<?php if (empty($syurlm)): ?>
  <div class="hdr" style="color:red"><strong>0 rows — SYURLM entries are MISSING for SG portals!</strong></div>
<?php else: ?>
  <table>
    <tr><th>FUID</th><th>FUDESC</th><th>FUTSPT</th><th>FUURL (truncated)</th></tr>
    <?php foreach ($syurlm as $r): ?>
    <tr>
      <td><?= htmlspecialchars(rtrim($r['FUID'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['FUDESC'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['FUTSPT'])) ?></td>
      <td><?= htmlspecialchars(substr(rtrim($r['FUURL']),0,80)) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>
</div>

<!-- SYPORT for SG portals -->
<h2>SYPORT — SG Portal entries (<?= count($syport) ?> rows)</h2>
<div class="box">
<?php if (empty($syport)): ?>
  <div class="hdr" style="color:red"><strong>0 rows — SYPORT entries are MISSING for SG portals!</strong></div>
<?php else: ?>
  <table>
    <tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPID</th><th>FPTITL</th><th>FPTSPT</th></tr>
    <?php foreach ($syport as $r):
        $cls = (rtrim($r['FPPAGE'])==='' ? ' class="sg"' : '');
    ?>
    <tr<?= $cls ?>>
      <td><?= htmlspecialchars(rtrim($r['FPPORT'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['FPPAGE'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['FPSEQ'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['FPID'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['FPTITL'])) ?></td>
      <td><?= htmlspecialchars(rtrim($r['FPTSPT'])) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>
</div>

</body>
</html>
