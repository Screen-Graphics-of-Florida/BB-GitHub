<?php
// DiagS5Schema.php
// Checks SGHDSDATA vs S5HDSDATA SYPORR and SYROLD for QC01.
// GetMenu.php uses unqualified table names resolved by library list.
// SG5 library list has S5HDSDATA at position 10 -- not SGHDSDATA.
// This determines which schema GetMenu is actually reading.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/DiagS5Schema.php

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return 'ERR:' . db2_stmt_errormsg();
    $r = db2_fetch_row($s); return $r ? db2_result($s, 0) : '0';
}
function qrows($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return array();
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    return $out;
}

$role = 'QC01';

// --- SGHDSDATA ---
$sg_syporr  = qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");
$sg_syrold  = qrows($conn,
    "SELECT RTRIM(RDPORT) AS PORT, RDSEQN AS SEQ"
  . " FROM SGHDSDATA.SYROLD WHERE RDROLE='$role' ORDER BY RDSEQN");
$sg_top_ports = qrows($conn,
    "SELECT RTRIM(PRPORT) AS PORT FROM SGHDSDATA.SYPORR"
  . " WHERE PRROLE='$role' AND RTRIM(PRPAGE)=''"
  . " ORDER BY PRPORT");

// --- S5HDSDATA ---
$s5_syporr  = qval($conn,
    "SELECT COUNT(*) FROM S5HDSDATA.SYPORR WHERE PRROLE='$role'");
$s5_syrold  = qrows($conn,
    "SELECT RTRIM(RDPORT) AS PORT, RDSEQN AS SEQ"
  . " FROM S5HDSDATA.SYROLD WHERE RDROLE='$role' ORDER BY RDSEQN");
$s5_top_ports = qrows($conn,
    "SELECT RTRIM(PRPORT) AS PORT FROM S5HDSDATA.SYPORR"
  . " WHERE PRROLE='$role' AND RTRIM(PRPAGE)=''"
  . " ORDER BY PRPORT");

// GetMenu simulation against S5HDSDATA
$s5_menu = qrows($conn,
    "SELECT RTRIM(RDPORT) AS PORT, RTRIM(FPID) AS FPID, RTRIM(FPDESC) AS DESC"
  . " FROM S5HDSDATA.SYROLD"
  . " INNER JOIN S5HDSDATA.SYPORT ON FPPORT=RDPORT"
  . " INNER JOIN S5HDSDATA.SYURLM ON FUID=FPID"
  . " WHERE RDROLE='$role'"
  . " AND (FPPAGE='' OR FPPAGE=FPPORT)"
  . " ORDER BY RDSEQN");

db2_close($conn);

function modeLabel($cnt) {
    if (strpos($cnt, 'ERR') === 0) return 'SCHEMA NOT FOUND';
    return ((int)$cnt > 0) ? 'WHITELIST (' . $cnt . ' rows)' : 'BYPASS (0 rows)';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Schema Comparison - SG5</title>
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
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px; }
.warn { background:#fff8e1; border:1px solid #ffe082; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px; }
.side { display:flex; gap:16px; flex-wrap:wrap; }
.box  { flex:1; min-width:300px; background:#fff; border-radius:5px;
        box-shadow:0 1px 4px rgba(0,0,0,.08); overflow:hidden; }
.box-hdr { padding:8px 12px; font-weight:bold; font-size:12px; color:#fff; }
.sg  { background:#1565c0; }
.s5  { background:#2e7d32; }
.box table { width:100%; border-collapse:collapse; }
.box th { background:rgba(0,0,0,.08); color:#333 !important; font-weight:bold !important;
          padding:4px 10px; text-align:left; font-size:11px; }
.box td { padding:3px 10px; font-size:11px; font-family:monospace;
          border-bottom:1px solid #f0f0f0; }
table.full { border-collapse:collapse; width:100%; background:#fff;
             border-radius:4px; overflow:hidden;
             box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
table.full th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
                padding:5px 10px; text-align:left; font-size:11px; }
table.full td { padding:4px 10px; font-size:11px; font-family:monospace;
                border-bottom:1px solid #f0f0f0; }
</style>
</head>
<body>

<div class="hdr">Schema Comparison: SGHDSDATA vs S5HDSDATA &mdash; QC01</div>

<div class="info">
  <strong>Why this matters:</strong> GetMenu.php uses unqualified table names (SYPORR, SYROLD, SYPORT, SYURLM).
  On SG5 (port 5610), the library list resolves those to <strong>S5HDSDATA</strong>.
  All our fix scripts used explicit <strong>SGHDSDATA</strong> (EIP Live).
  If S5HDSDATA has different data, our fix had no effect on SG5's menu.
</div>

<!-- SYPORR counts -->
<div class="sect">SYPORR Row Counts for QC01</div>
<div class="side">
  <div class="box">
    <div class="box-hdr sg">SGHDSDATA.SYPORR (EIP Live)</div>
    <table>
      <tr><th>Count</th><th>Mode</th><th>Top-Level Ports</th></tr>
      <tr>
        <td><?php echo htmlspecialchars((string)$sg_syporr); ?></td>
        <td><?php echo htmlspecialchars(modeLabel((string)$sg_syporr)); ?></td>
        <td>
          <?php echo implode(', ', array_map(function($r){ return htmlspecialchars($r['PORT']); }, $sg_top_ports)); ?>
        </td>
      </tr>
    </table>
  </div>
  <div class="box">
    <div class="box-hdr s5">S5HDSDATA.SYPORR (SG5 Test)</div>
    <table>
      <tr><th>Count</th><th>Mode</th><th>Top-Level Ports</th></tr>
      <tr>
        <td><?php echo htmlspecialchars((string)$s5_syporr); ?></td>
        <td><?php echo htmlspecialchars(modeLabel((string)$s5_syporr)); ?></td>
        <td>
          <?php echo implode(', ', array_map(function($r){ return htmlspecialchars($r['PORT']); }, $s5_top_ports)); ?>
        </td>
      </tr>
    </table>
  </div>
</div>

<!-- SYROLD -->
<div class="sect">SYROLD Portals for QC01</div>
<div class="side">
  <div class="box">
    <div class="box-hdr sg">SGHDSDATA.SYROLD</div>
    <table>
      <tr><th>PORT</th><th>SEQ</th></tr>
      <?php foreach ($sg_syrold as $r): ?>
      <tr><td><?php echo htmlspecialchars($r['PORT']); ?></td>
          <td><?php echo htmlspecialchars($r['SEQ']); ?></td></tr>
      <?php endforeach; ?>
    </table>
  </div>
  <div class="box">
    <div class="box-hdr s5">S5HDSDATA.SYROLD</div>
    <table>
      <tr><th>PORT</th><th>SEQ</th></tr>
      <?php foreach ($s5_syrold as $r): ?>
      <tr><td><?php echo htmlspecialchars($r['PORT']); ?></td>
          <td><?php echo htmlspecialchars($r['SEQ']); ?></td></tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<!-- GetMenu simulation using S5HDSDATA -->
<div class="sect">GetMenu Simulation Using S5HDSDATA (<?php echo count($s5_menu); ?> rows)</div>
<?php if (count($s5_menu) === 0): ?>
<div class="bad">Zero rows returned from S5HDSDATA GetMenu simulation
  &mdash; either schema not found or tables empty.</div>
<?php else: ?>
<table class="full">
  <tr><th>PORT</th><th>FPID</th><th>DESC</th></tr>
  <?php foreach ($s5_menu as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['PORT']); ?></td>
    <td><?php echo htmlspecialchars($r['FPID']); ?></td>
    <td><?php echo htmlspecialchars($r['DESC']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
