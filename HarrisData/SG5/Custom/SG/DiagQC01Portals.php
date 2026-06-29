<?php
// DiagQC01Portals.php
// Compares SYPORT + SYURLM entries for QC01's portals.
// ITEM shows in menu; others don't. Finds why.
// Also runs the actual GetMenu bypass-mode query for QC01.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/DiagQC01Portals.php

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

function qrows($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return array();
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    return $out;
}
function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s); return $r ? db2_result($s, 0) : null;
}

$role = 'QC01';

// All portals in QC01's SYROLD
$syrold = qrows($conn,
    "SELECT RTRIM(RDPORT) AS PORT, RDSEQN AS SEQ"
  . " FROM SGHDSDATA.SYROLD WHERE RDROLE='$role' ORDER BY RDSEQN");

$ports = array();
foreach ($syrold as $r) $ports[] = $r['PORT'];
$portIn = "'" . implode("','", $ports) . "'";

// SYPORT rows for those portals (top-level and own-page sub-header)
$syport = qrows($conn,
    "SELECT RTRIM(FPPORT) AS PORT, RTRIM(FPPAGE) AS PAGE,"
  . " FPSEQ AS SEQ, RTRIM(FPID) AS FPID, RTRIM(FPDESC) AS DESC"
  . " FROM SGHDSDATA.SYPORT"
  . " WHERE RTRIM(FPPORT) IN ($portIn)"
  . " AND (RTRIM(FPPAGE)='' OR RTRIM(FPPAGE)=RTRIM(FPPORT))"
  . " ORDER BY FPPORT, FPPAGE, FPSEQ");

// SYURLM rows for each FPID found above
$fpids = array();
foreach ($syport as $r) { if ($r['FPID'] !== '') $fpids[] = $r['FPID']; }
$furlm = array();
if (count($fpids)) {
    $fpidIn = "'" . implode("','", $fpids) . "'";
    $urlrows = qrows($conn,
        "SELECT RTRIM(FUID) AS FUID, RTRIM(FUURL) AS URL,"
      . " RTRIM(FUDESC) AS DESC"
      . " FROM SGHDSDATA.SYURLM"
      . " WHERE RTRIM(FUID) IN ($fpidIn)"
      . " ORDER BY FUID");
    foreach ($urlrows as $r) $furlm[$r['FUID']] = $r;
}

// Simulate GetMenu bypass-mode query for QC01
$menuRows = qrows($conn,
    "SELECT RTRIM(RDPORT) AS PORT, RTRIM(FPID) AS FPID,"
  . " RTRIM(FPDESC) AS DESC, RTRIM(FUURL) AS URL"
  . " FROM SGHDSDATA.SYROLD"
  . " INNER JOIN SGHDSDATA.SYPORT ON FPPORT=RDPORT"
  . " INNER JOIN SGHDSDATA.SYURLM ON FUID=FPID"
  . " WHERE RDROLE='$role'"
  . " AND (FPPAGE='' OR FPPAGE=FPPORT)"
  . " ORDER BY RDSEQN");

// SYPORR count to confirm bypass mode
$syporr_cnt = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");

db2_close($conn);

// Index syport by port for quick lookup
$spByPort = array();
foreach ($syport as $r) {
    if (!isset($spByPort[$r['PORT']])) $spByPort[$r['PORT']] = array();
    $spByPort[$r['PORT']][] = $r;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>QC01 Portal Diagnostic - SG5</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:8px 14px; margin-bottom:8px; font-size:12px; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:8px 14px; margin-bottom:8px; font-size:12px; }
.sect { font-weight:bold; font-size:13px; margin:18px 0 6px;
        color:#1a3d5c; border-bottom:2px solid #90caf9; padding-bottom:3px; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
     padding:5px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.yes { color:#2e7d32; font-weight:bold; }
.no  { color:#c62828; font-weight:bold; }
</style>
</head>
<body>

<div class="hdr">QC01 Portal Diagnostic &mdash; SG5 Test (read-only)</div>

<div class="info">
  SYPORR rows for QC01: <strong><?php echo $syporr_cnt; ?></strong>
  &mdash; Mode: <strong><?php echo $syporr_cnt > 0 ? 'WHITELIST' : 'BYPASS'; ?></strong><br>
  GetMenu bypass query returns <strong><?php echo count($menuRows); ?></strong> portal rows for QC01.
</div>

<!-- Simulated GetMenu result -->
<div class="sect">GetMenu Result &mdash; Portals That Will Appear in Left Nav</div>
<?php if (count($menuRows) === 0): ?>
<div class="bad">Zero rows returned. GetMenu will show nothing.</div>
<?php else: ?>
<table>
  <tr><th>PORT</th><th>FPID</th><th>DESC</th><th>URL</th></tr>
  <?php foreach ($menuRows as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['PORT']); ?></td>
    <td><?php echo htmlspecialchars($r['FPID']); ?></td>
    <td><?php echo htmlspecialchars($r['DESC']); ?></td>
    <td><?php echo htmlspecialchars($r['URL']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- Per-portal breakdown -->
<div class="sect">Per-Portal: SYROLD + SYPORT + SYURLM Status</div>
<table>
  <tr>
    <th>PORT (SYROLD)</th>
    <th>SEQ</th>
    <th>In SYPORT?</th>
    <th>FPID</th>
    <th>In SYURLM?</th>
    <th>URL</th>
  </tr>
  <?php foreach ($syrold as $rd):
    $port = $rd['PORT'];
    $seq  = $rd['SEQ'];
    $spRows = isset($spByPort[$port]) ? $spByPort[$port] : array();
    $topSp  = null;
    foreach ($spRows as $sp) {
        if (rtrim($sp['PAGE']) === '') { $topSp = $sp; break; }
    }
    $inSyport  = ($topSp !== null);
    $fpid      = $inSyport ? $topSp['FPID'] : '';
    $urlRow    = ($fpid !== '' && isset($furlm[$fpid])) ? $furlm[$fpid] : null;
    $inSyurlm  = ($urlRow !== null);
  ?>
  <tr>
    <td><strong><?php echo htmlspecialchars($port); ?></strong></td>
    <td><?php echo htmlspecialchars($seq); ?></td>
    <td class="<?php echo $inSyport ? 'yes' : 'no'; ?>">
      <?php echo $inSyport ? 'YES' : 'NO - MISSING'; ?></td>
    <td><?php echo htmlspecialchars($fpid); ?></td>
    <td class="<?php echo $inSyurlm ? 'yes' : 'no'; ?>">
      <?php echo $inSyurlm ? 'YES' : ($fpid ? 'NO - MISSING' : 'N/A'); ?></td>
    <td><?php echo $urlRow ? htmlspecialchars($urlRow['URL']) : ''; ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<!-- Full SYPORT detail -->
<div class="sect">SYPORT Rows Found for QC01 Portals</div>
<?php if (count($syport) === 0): ?>
<div class="bad">No SYPORT rows found for any of QC01's portals.</div>
<?php else: ?>
<table>
  <tr><th>PORT</th><th>PAGE</th><th>SEQ</th><th>FPID</th><th>DESC</th></tr>
  <?php foreach ($syport as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['PORT']); ?></td>
    <td><?php echo htmlspecialchars($r['PAGE']); ?></td>
    <td><?php echo htmlspecialchars($r['SEQ']); ?></td>
    <td><?php echo htmlspecialchars($r['FPID']); ?></td>
    <td><?php echo htmlspecialchars($r['DESC']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- Full SYURLM detail -->
<div class="sect">SYURLM Rows Found for Those FPIDs</div>
<?php if (count($furlm) === 0): ?>
<div class="bad">No SYURLM rows found for any FPID.</div>
<?php else: ?>
<table>
  <tr><th>FUID</th><th>DESC</th><th>URL</th></tr>
  <?php foreach ($furlm as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['FUID']); ?></td>
    <td><?php echo htmlspecialchars($r['DESC']); ?></td>
    <td><?php echo htmlspecialchars($r['URL']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
