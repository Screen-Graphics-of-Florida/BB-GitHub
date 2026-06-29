<?php
// DiagQC01Seq.php
// Compares actual SYPORT FPSEQ values against SYPORR PRSEQ for QC01.
// The GetMenu inner join requires FPSEQ=PRSEQ — a mismatch = no menus.
//
// Preview: https://portal.screen-graphics.com:5601/Custom/SG/DiagQC01Seq.php

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

function qrows($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return array();
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    return $out;
}

$role = 'QC01';
$portals = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP');

// 1. SYPORR current state for QC01
$syporr = qrows($conn,
    "SELECT RTRIM(PRPORT) AS PRPORT, RTRIM(PRPAGE) AS PRPAGE,"
  . " PRSEQ, RTRIM(PRSEL) AS PRSEL"
  . " FROM SGHDSDATA.SYPORR"
  . " WHERE PRROLE='$role'"
  . " ORDER BY PRPORT, PRPAGE, PRSEQ");

// 2. SYPORT entries for QC01 portals (top-level + sub-pages)
$portList = "'" . implode("','", $portals) . "'";
$syport = qrows($conn,
    "SELECT RTRIM(FPPORT) AS FPPORT, RTRIM(FPPAGE) AS FPPAGE,"
  . " FPSEQ, RTRIM(FPDESC) AS FPDESC, RTRIM(FPID) AS FPID"
  . " FROM SGHDSDATA.SYPORT"
  . " WHERE FPPORT IN ($portList)"
  . " AND (RTRIM(FPPAGE)='' OR RTRIM(FPPAGE)=RTRIM(FPPORT))"
  . " ORDER BY FPPORT, FPPAGE, FPSEQ");

// 3. SYROLD rows for QC01
$syrold = qrows($conn,
    "SELECT RTRIM(RDROLE) AS RDROLE, RTRIM(RDPORT) AS RDPORT, RDSEQN"
  . " FROM SGHDSDATA.SYROLD"
  . " WHERE RDROLE='$role'"
  . " ORDER BY RDSEQN, RDPORT");

// 4. Simulate the GetMenu join — how many rows would it return?
$syporr_cnt = (int)current(current(qrows($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'")));
$join_sql =
    "SELECT COUNT(*) AS CNT FROM SGHDSDATA.SYROLD"
  . " INNER JOIN SGHDSDATA.SYPORT ON FPPORT=RDPORT"
  . " INNER JOIN SGHDSDATA.SYURLM ON FUID=FPID";
if ($syporr_cnt) {
    $join_sql .= " INNER JOIN SGHDSDATA.SYPORR ON RDROLE=PRROLE"
               . " AND FPPORT=PRPORT AND FPPAGE=PRPAGE AND FPSEQ=PRSEQ";
}
$join_sql .= " WHERE (((RDROLE='$role') AND (FPPAGE='' OR FPPAGE=FPPORT))"
           . " OR (RDROLE='$role' AND FPPORT='SGINQ' AND FPPAGE='SGINQ'))";
if ($syporr_cnt) {
    $join_sql .= " AND PRSEL='Y'";
}
$join_rows = qrows($conn, $join_sql);
$join_count = $join_rows ? (int)$join_rows[0]['CNT'] : 0;

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>DiagQC01Seq</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.sect { font-weight:bold; font-size:13px; margin:18px 0 6px;
        color:#1a3d5c; border-bottom:2px solid #90caf9; padding-bottom:3px; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; }
.warn { background:#fff3e0; border:1px solid #ffcc02; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
     padding:5px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.match { color:#2e7d32; font-weight:bold; }
.mis   { color:#c62828; font-weight:bold; }
</style>
</head>
<body>

<div class="hdr">QC01 SYPORR Sequence Diagnostic</div>

<div class="info <?php echo $syporr_cnt > 0 ? 'warn' : 'ok'; ?>">
  <strong>Mode:</strong>
  <?php if ($syporr_cnt > 0): ?>
    WHITELIST (<?php echo $syporr_cnt; ?> SYPORR rows) &mdash;
    GetMenu INNER JOIN on FPSEQ=PRSEQ required.
    <strong>GetMenu join returns <?php echo $join_count; ?> rows.</strong>
    <?php if ($join_count == 0): ?>
      <span style="color:#c62828">PRSEQ mismatch is causing zero menus!</span>
    <?php endif; ?>
  <?php else: ?>
    BYPASS (0 SYPORR rows) &mdash; no SYPORR join, all SYROLD portals shown.
    GetMenu would return <?php echo $join_count; ?> rows.
  <?php endif; ?>
</div>

<div class="sect">SYPORT rows for QC01 portals (FPSEQ is what the GetMenu join needs)</div>
<table>
  <tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPDESC</th><th>FPID</th></tr>
  <?php foreach ($syport as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['FPPORT']); ?></td>
    <td><?php echo htmlspecialchars($r['FPPAGE']); ?></td>
    <td><strong><?php echo htmlspecialchars($r['FPSEQ']); ?></strong></td>
    <td><?php echo htmlspecialchars($r['FPDESC']); ?></td>
    <td><?php echo htmlspecialchars($r['FPID']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<div class="sect">SYPORR current state for QC01 (PRSEQ must match SYPORT FPSEQ above)</div>
<table>
  <tr><th>PRPORT</th><th>PRPAGE</th><th>PRSEQ</th><th>PRSEL</th><th>Matches SYPORT?</th></tr>
  <?php foreach ($syporr as $r):
    // Find matching SYPORT row
    $match = false;
    foreach ($syport as $sp) {
        if (rtrim($sp['FPPORT']) === rtrim($r['PRPORT'])
         && rtrim($sp['FPPAGE']) === rtrim($r['PRPAGE'])
         && (int)$sp['FPSEQ'] === (int)$r['PRSEQ']) {
            $match = true; break;
        }
    }
  ?>
  <tr>
    <td><?php echo htmlspecialchars($r['PRPORT']); ?></td>
    <td><?php echo htmlspecialchars($r['PRPAGE']); ?></td>
    <td><strong><?php echo htmlspecialchars($r['PRSEQ']); ?></strong></td>
    <td><?php echo htmlspecialchars($r['PRSEL']); ?></td>
    <td class="<?php echo $match ? 'match' : 'mis'; ?>">
      <?php echo $match ? 'YES' : 'NO - MISMATCH'; ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<div class="sect">SYROLD rows for QC01</div>
<table>
  <tr><th>RDROLE</th><th>RDPORT</th><th>RDSEQN</th></tr>
  <?php foreach ($syrold as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['RDROLE']); ?></td>
    <td><?php echo htmlspecialchars($r['RDPORT']); ?></td>
    <td><?php echo htmlspecialchars($r['RDSEQN']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

</body>
</html>
