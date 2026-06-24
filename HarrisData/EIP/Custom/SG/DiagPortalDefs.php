<?php
// Diagnostic: SYURLM + SYPORT + SYPORR for all of a role's SYROLD portals
// Shows exactly why each portal does or does not appear in EIP nav.
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagPortalDefs.php
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagPortalDefs.php?role=ENAPOLES

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role = isset($_GET['role']) ? strtoupper(trim($_GET['role'])) : 'ENAPOLES';

function qrow($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_assoc($s);
    return $r ? $r : null;
}
function qrows($conn, $sql) {
    $rows = array();
    $s = @db2_exec($conn, $sql);
    if (!$s) return $rows;
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}

// SYROLD portals for this role
$syrold = qrows($conn,
    "SELECT RTRIM(RDPORT) AS RDPORT, RDSEQN "
  . "FROM SGHDSDATA.SYROLD "
  . "WHERE RDROLE='$role' ORDER BY RDSEQN");

// Build info for each portal
$rows = array();
foreach ($syrold as $sr) {
    $p = $sr['RDPORT'];

    // SYURLM top-level entry (FUID = 'PCODE/PORTAL' OR just 'PCODE')
    $syurlm = qrow($conn,
        "SELECT RTRIM(FUID) AS FUID, RTRIM(FUTSPT) AS FUTSPT, "
      . "       RTRIM(FUTSUS) AS FUTSUS "
      . "FROM SGHDSDATA.SYURLM "
      . "WHERE FUID='$p/PORTAL'");
    if (!$syurlm) {
        // some portals might be registered just as 'PCODE'
        $syurlm = qrow($conn,
            "SELECT RTRIM(FUID) AS FUID, RTRIM(FUTSPT) AS FUTSPT, "
          . "       RTRIM(FUTSUS) AS FUTSUS "
          . "FROM SGHDSDATA.SYURLM WHERE FUID='$p'");
    }

    // SYPORT top-level entry (FPPAGE='')
    $syport = qrow($conn,
        "SELECT RTRIM(FPPORT) AS FPPORT, RTRIM(FPTSPT) AS FPTSPT, "
      . "       RTRIM(FPTSUS) AS FPTSUS "
      . "FROM SGHDSDATA.SYPORT "
      . "WHERE FPPORT='$p' AND RTRIM(FPPAGE)=''");

    // SYPORR top-level entry (PRPAGE='')
    $syporr = qrow($conn,
        "SELECT RTRIM(PRPORT) AS PRPORT, RTRIM(PRTSPT) AS PRTSPT, "
      . "       RTRIM(PRTSUS) AS PRTSUS "
      . "FROM SGHDSDATA.SYPORR "
      . "WHERE PRROLE='$role' AND PRPORT='$p' AND RTRIM(PRPAGE)=''");

    $rows[] = array(
        'port'   => $p,
        'seq'    => rtrim($sr['RDSEQN']),
        'syurlm' => $syurlm,
        'syport' => $syport,
        'syporr' => $syporr,
    );
}

db2_close($conn);

// Determine overall status for each portal
// Visible = SYPORR exists AND PRTSPT='Y' (our push portals need this)
// But native portals might not need SYURLM at all
function yno($v) {
    if ($v === null) return '<span class="m">—</span>';
    $val = rtrim($v);
    $cls = ($val === 'Y') ? 'y' : 'n';
    return '<span class="' . $cls . '">' . htmlspecialchars($val === '' ? '(blank)' : $val) . '</span>';
}
function yn($b) {
    return $b ? '<span class="y">YES</span>' : '<span class="n">NO</span>';
}
function getStatus($r) {
    $hasUrlm  = ($r['syurlm'] !== null);
    $urlmOk   = $hasUrlm && ($r['syurlm']['FUTSPT'] === 'Y');
    $hasPort  = ($r['syport'] !== null);
    $portOk   = $hasPort && ($r['syport']['FPTSPT'] === 'Y');
    $hasPorr  = ($r['syporr'] !== null);
    $porrOk   = $hasPorr && ($r['syporr']['PRTSPT'] === 'Y');

    if ($hasUrlm && $hasPort && $hasPorr) {
        if ($urlmOk && $portOk && $porrOk) return 'OK-ALL';
        if ($porrOk) return 'OK-SYPORR';
        return 'WARN';
    }
    if (!$hasUrlm && $hasPort && $hasPorr && $porrOk) return 'NATIVE';
    if (!$hasUrlm && !$hasPort && $hasPorr && $porrOk) return 'SYPORR-ONLY';
    if (!$hasPorr) return 'NO-SYPORR';
    return 'PARTIAL';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Portal Defs: <?= htmlspecialchars($role) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:12px Arial,sans-serif; background:#f0f2f5; padding:20px; }
h2 { background:#2a5a8c; color:#fff; padding:8px 16px; margin:0 0 10px;
     border-radius:4px; font-size:15px; border-bottom:3px solid #f90; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,.08);
        margin-bottom:16px; }
th { background:#2a5a8c; color:#fff; padding:5px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.y { color:#2e7d32; font-weight:bold; }
.n { color:#c62828; font-weight:bold; }
.m { color:#888; }
form { margin-bottom:14px; }
input  { padding:5px 9px; font-size:13px; border:1px solid #ccc;
         border-radius:4px; width:200px; }
button { padding:5px 13px; font-size:13px; background:#2a5a8c; color:#fff;
         border:none; border-radius:4px; cursor:pointer; margin-left:6px; }
.badge { display:inline-block; padding:1px 7px; border-radius:10px;
         font-size:10px; font-weight:bold; white-space:nowrap; }
.ok-all   { background:#c8e6c9; color:#1b5e20; }
.ok-syporr { background:#dcedc8; color:#33691e; }
.native   { background:#e3f2fd; color:#1565c0; }
.syporr-only { background:#fff9c4; color:#f57f17; }
.warn     { background:#fff3e0; color:#e65100; }
.no-syporr { background:#ffcdd2; color:#b71c1c; }
.partial  { background:#ffe0b2; color:#bf360c; }
.box { background:#fff; border-radius:4px; padding:10px 14px;
       box-shadow:0 2px 4px rgba(0,0,0,.08); margin-bottom:14px; font-size:12px; }
</style>
</head>
<body>

<form>
  <input name="role" value="<?= htmlspecialchars($role) ?>">
  <button>Check</button>
</form>

<div class="box">
  <strong>Role:</strong> <?= htmlspecialchars($role) ?>
  &nbsp;|&nbsp; <strong>SYROLD portals:</strong> <?= count($syrold) ?>
  &nbsp;|&nbsp;
  <strong>Legend:</strong>
  <span class="badge ok-all">OK-ALL</span> = in SYURLM+SYPORT+SYPORR all Y &nbsp;
  <span class="badge native">NATIVE</span> = in SYPORT+SYPORR (no SYURLM needed) &nbsp;
  <span class="badge syporr-only">SYPORR-ONLY</span> = only SYPORR row &nbsp;
  <span class="badge no-syporr">NO-SYPORR</span> = missing SYPORR row &nbsp;
  <span class="badge warn">WARN</span> = has rows but a PRTSPT/FPTSPT/FUTSPT is not Y
</div>

<h2>Portal Definitions for <?= htmlspecialchars($role) ?></h2>
<table>
  <tr>
    <th>Seq</th>
    <th>Portal</th>
    <th>Status</th>
    <th>SYURLM?</th>
    <th>FUTSPT</th>
    <th>FUTSUS</th>
    <th>SYPORT?</th>
    <th>FPTSPT</th>
    <th>FPTSUS</th>
    <th>SYPORR?</th>
    <th>PRTSPT</th>
    <th>PRTSUS</th>
  </tr>
  <?php foreach ($rows as $r):
      $st = getStatus($r);
      $badgeCls = strtolower($st);

      $urlm  = $r['syurlm'];
      $port  = $r['syport'];
      $porr  = $r['syporr'];
  ?>
  <tr>
    <td><?= htmlspecialchars($r['seq']) ?></td>
    <td><strong><?= htmlspecialchars($r['port']) ?></strong></td>
    <td><span class="badge <?= $badgeCls ?>"><?= $st ?></span></td>
    <td><?= yn($urlm !== null) ?></td>
    <td><?= yno($urlm ? $urlm['FUTSPT'] : null) ?></td>
    <td><?= $urlm ? htmlspecialchars($urlm['FUTSUS']) : '<span class="m">—</span>' ?></td>
    <td><?= yn($port !== null) ?></td>
    <td><?= yno($port ? $port['FPTSPT'] : null) ?></td>
    <td><?= $port ? htmlspecialchars($port['FPTSUS']) : '<span class="m">—</span>' ?></td>
    <td><?= yn($porr !== null) ?></td>
    <td><?= yno($porr ? $porr['PRTSPT'] : null) ?></td>
    <td><?= $porr ? htmlspecialchars($porr['PRTSUS']) : '<span class="m">—</span>' ?></td>
  </tr>
  <?php endforeach; ?>
</table>

</body>
</html>
