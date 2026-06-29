<?php
// DiagCompareRoles.php — SGHDSDATA (EIP Live schema)
// Read-only full comparison of two roles across all portal-related tables.
// Usage: ?role1=QC01&role2=QC01A
//
// SG5:  https://portal.screen-graphics.com:5610/Custom/SG/DiagCompareRoles.php?role1=QC01&role2=QC01A
// EIP:  https://portal.screen-graphics.com:5601/Custom/SG/DiagCompareRoles.php?role1=QC01&role2=QC01A

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

$r1 = isset($_GET['role1']) ? strtoupper(trim($_GET['role1'])) : 'QC01';
$r2 = isset($_GET['role2']) ? strtoupper(trim($_GET['role2'])) : 'QC01A';

// ── SYROLM ─────────────────────────────────────────────────────────────────
// NOTE: DESC is a reserved word — alias as DESCR
$syrolm = qrows($conn,
    "SELECT RTRIM(RLROLE) AS ROLE, RTRIM(RLDESC) AS DESCR,"
  . " RTRIM(RLRSV) AS RSV, RTRIM(RLTYPE) AS RLTYPE"
  . " FROM SGHDSDATA.SYROLM"
  . " WHERE RTRIM(RLROLE) IN ('$r1','$r2')"
  . " ORDER BY RLROLE");

// ── SYROLD ─────────────────────────────────────────────────────────────────
$syrold1 = qrows($conn,
    "SELECT RTRIM(RDPORT) AS PORT, RDSEQN AS SEQ"
  . " FROM SGHDSDATA.SYROLD WHERE RDROLE='$r1' ORDER BY RDSEQN");
$syrold2 = qrows($conn,
    "SELECT RTRIM(RDPORT) AS PORT, RDSEQN AS SEQ"
  . " FROM SGHDSDATA.SYROLD WHERE RDROLE='$r2' ORDER BY RDSEQN");

$syrold1_ports = array();
foreach ($syrold1 as $r) $syrold1_ports[] = $r['PORT'];
$syrold2_ports = array();
foreach ($syrold2 as $r) $syrold2_ports[] = $r['PORT'];
$only_syrold_r1 = array_diff($syrold1_ports, $syrold2_ports);
$only_syrold_r2 = array_diff($syrold2_ports, $syrold1_ports);

// ── SYPORR ─────────────────────────────────────────────────────────────────
$syporr1_cnt = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$r1'");
$syporr2_cnt = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$r2'");

$syporr1_top = qrows($conn,
    "SELECT RTRIM(PRPORT) AS PORT, PRSEQ AS SEQ, RTRIM(PRSEL) AS SEL"
  . " FROM SGHDSDATA.SYPORR"
  . " WHERE PRROLE='$r1' AND RTRIM(PRPAGE)=''"
  . " ORDER BY PRPORT");
$syporr2_top = qrows($conn,
    "SELECT RTRIM(PRPORT) AS PORT, PRSEQ AS SEQ, RTRIM(PRSEL) AS SEL"
  . " FROM SGHDSDATA.SYPORR"
  . " WHERE PRROLE='$r2' AND RTRIM(PRPAGE)=''"
  . " ORDER BY PRPORT");

$ports1_top = array();
foreach ($syporr1_top as $r) $ports1_top[] = $r['PORT'];
$ports2_top = array();
foreach ($syporr2_top as $r) $ports2_top[] = $r['PORT'];
$only_r1_top = array_diff($ports1_top, $ports2_top);
$only_r2_top = array_diff($ports2_top, $ports1_top);

// ── GetMenu simulation — what each role would actually see ─────────────────
function getMenuRows($conn, $role, $syporr_cnt) {
    $sql =
        "SELECT RTRIM(RDPORT) AS PORT, RTRIM(FPID) AS FPID,"
      . " RTRIM(FPDESC) AS FPDESC"
      . " FROM SGHDSDATA.SYROLD"
      . " INNER JOIN SGHDSDATA.SYPORT ON FPPORT=RDPORT"
      . " INNER JOIN SGHDSDATA.SYURLM ON FUID=FPID";
    if ($syporr_cnt > 0) {
        $sql .= " INNER JOIN SGHDSDATA.SYPORR"
              . " ON RDROLE=PRROLE AND FPPORT=PRPORT"
              . " AND FPPAGE=PRPAGE AND FPSEQ=PRSEQ";
    }
    $sql .= " WHERE RDROLE='$role'"
          . " AND (FPPAGE='' OR FPPAGE=FPPORT)";
    if ($syporr_cnt > 0) {
        $sql .= " AND PRSEL='Y'";
    }
    $sql .= " ORDER BY RDSEQN, FPSEQ";
    $s = @db2_exec($conn, $sql);
    if (!$s) return array();
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    return $out;
}

$menu1 = getMenuRows($conn, $r1, $syporr1_cnt);
$menu2 = getMenuRows($conn, $r2, $syporr2_cnt);

// Top-level portals visible in each menu (FPID contains '/' + no sub-page means PORT row)
$menuports1 = array();
foreach ($menu1 as $r) { if (!in_array($r['PORT'], $menuports1)) $menuports1[] = $r['PORT']; }
$menuports2 = array();
foreach ($menu2 as $r) { if (!in_array($r['PORT'], $menuports2)) $menuports2[] = $r['PORT']; }
$only_menu1 = array_diff($menuports1, $menuports2);
$only_menu2 = array_diff($menuports2, $menuports1);

// ── SYPGMO ─────────────────────────────────────────────────────────────────
$sypgmo1 = qrows($conn,
    "SELECT RTRIM(PMPORT) AS PORT, RTRIM(PMPAGE) AS PAGE, RTRIM(PMPGM) AS PGM"
  . " FROM SGHDSDATA.SYPGMO WHERE RTRIM(PMROLE)='$r1'"
  . " ORDER BY PMPORT, PMPAGE, PMPGM");
$sypgmo2 = qrows($conn,
    "SELECT RTRIM(PMPORT) AS PORT, RTRIM(PMPAGE) AS PAGE, RTRIM(PMPGM) AS PGM"
  . " FROM SGHDSDATA.SYPGMO WHERE RTRIM(PMROLE)='$r2'"
  . " ORDER BY PMPORT, PMPAGE, PMPGM");

$pgmo1_keys = array();
foreach ($sypgmo1 as $r) $pgmo1_keys[] = $r['PORT'].'/'.$r['PAGE'].'/'.$r['PGM'];
$pgmo2_keys = array();
foreach ($sypgmo2 as $r) $pgmo2_keys[] = $r['PORT'].'/'.$r['PAGE'].'/'.$r['PGM'];
$only_pgmo1 = array_diff($pgmo1_keys, $pgmo2_keys);
$only_pgmo2 = array_diff($pgmo2_keys, $pgmo1_keys);

// ── SYPGMS ─────────────────────────────────────────────────────────────────
$sypgms1_cnt = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPGMS WHERE RTRIM(SPROLE)='$r1'");
$sypgms2_cnt = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPGMS WHERE RTRIM(SPROLE)='$r2'");

// ── SYROLU — users assigned to each role ───────────────────────────────────
$syrolu1_cnt = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYROLU WHERE RTRIM(RUROLE)='$r1'");
$syrolu2_cnt = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYROLU WHERE RTRIM(RUROLE)='$r2'");

$syrolu1 = qrows($conn,
    "SELECT RTRIM(RUUSER) AS USR FROM SGHDSDATA.SYROLU"
  . " WHERE RTRIM(RUROLE)='$r1' ORDER BY RUUSER");
$syrolu2 = qrows($conn,
    "SELECT RTRIM(RUUSER) AS USR FROM SGHDSDATA.SYROLU"
  . " WHERE RTRIM(RUROLE)='$r2' ORDER BY RUUSER");

db2_close($conn);

function modeBadge($cnt) {
    $mode  = $cnt > 0 ? 'WHITELIST' : 'BYPASS';
    $color = $cnt > 0 ? '#c62828'   : '#2e7d32';
    return "<span style='background:$color;color:#fff;padding:2px 8px;"
         . "border-radius:3px;font-size:11px;font-weight:bold'>$mode ($cnt rows)</span>";
}
function rsvBadge($val) {
    $v = trim($val);
    $color = ($v === 'Y') ? '#c62828' : '#2e7d32';
    return "<span style='background:$color;color:#fff;padding:1px 7px;"
         . "border-radius:3px;font-size:11px;font-weight:bold'>"
         . htmlspecialchars($v ?: 'N') . "</span>";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SGHDSDATA: <?php echo htmlspecialchars($r1); ?> vs <?php echo htmlspecialchars($r2); ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:4px; font-size:17px; font-weight:bold; }
.schema-tag { font-size:11px; color:#90caf9; font-weight:normal;
              font-family:monospace; margin-bottom:16px; display:block;
              padding:4px 20px; background:rgba(0,0,0,.2); }
.sect { font-weight:bold; font-size:13px; margin:20px 0 6px;
        color:#1a3d5c; border-bottom:2px solid #90caf9; padding-bottom:3px; }
.diff  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
         padding:10px 14px; margin-bottom:10px; font-size:12px; line-height:1.7; }
.match { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
         padding:10px 14px; margin-bottom:10px; font-size:12px; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
     padding:5px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; vertical-align:top; }
td.r1 { background:#fff8e1; }
td.r2 { background:#e8f5e9; }
.tag1 { background:#e65100; color:#fff; padding:1px 6px; border-radius:3px;
        font-size:10px; font-weight:bold; white-space:nowrap; }
.tag2 { background:#2e7d32; color:#fff; padding:1px 6px; border-radius:3px;
        font-size:10px; font-weight:bold; white-space:nowrap; }
.cards { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:14px; }
.card { background:#fff; border-radius:4px; padding:10px 18px; text-align:center;
        box-shadow:0 1px 4px rgba(0,0,0,.08); min-width:80px; }
.card.r1c { border-top:3px solid #e65100; }
.card.r2c { border-top:3px solid #2e7d32; }
.card .num { font-size:26px; font-weight:bold; font-family:monospace; color:#333; }
.card .lbl { font-size:10px; color:#777; margin-top:2px; }
</style>
</head>
<body>

<div class="hdr">
  SGHDSDATA &mdash; Role Comparison:
  <span style="color:#ffcc02"><?php echo htmlspecialchars($r1); ?></span>
  vs
  <span style="color:#a5d6a7"><?php echo htmlspecialchars($r2); ?></span>
</div>
<div class="schema-tag">Schema: SGHDSDATA (EIP Live) &nbsp;|&nbsp; Read-only</div>

<!-- ── SYROLM ─────────────────────────────────────────────────────────────── -->
<div class="sect">SYROLM &mdash; Role Definition</div>
<?php if (count($syrolm) === 0): ?>
<div class="diff">No SYROLM rows found for either role.</div>
<?php else: ?>
<table>
  <tr><th>ROLE</th><th>DESCRIPTION</th><th>RSV</th><th>TYPE</th></tr>
  <?php foreach ($syrolm as $r): ?>
  <tr>
    <td><?php echo htmlspecialchars($r['ROLE']); ?></td>
    <td><?php echo htmlspecialchars($r['DESCR']); ?></td>
    <td><?php echo rsvBadge($r['RSV']); ?></td>
    <td><?php echo htmlspecialchars($r['RLTYPE']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- ── SYROLU ─────────────────────────────────────────────────────────────── -->
<div class="sect">SYROLU &mdash; Users Assigned to Role</div>
<div class="cards">
  <div class="card r1c">
    <div class="num"><?php echo $syrolu1_cnt; ?></div>
    <div class="lbl"><?php echo htmlspecialchars($r1); ?> users</div>
  </div>
  <div class="card r2c">
    <div class="num"><?php echo $syrolu2_cnt; ?></div>
    <div class="lbl"><?php echo htmlspecialchars($r2); ?> users</div>
  </div>
</div>
<?php if ($syrolu1_cnt > 0 || $syrolu2_cnt > 0): ?>
<table>
  <tr>
    <th><?php echo htmlspecialchars($r1); ?> Users</th>
    <th><?php echo htmlspecialchars($r2); ?> Users</th>
  </tr>
  <?php
  $maxU = max(count($syrolu1), count($syrolu2));
  for ($i = 0; $i < $maxU; $i++):
    $u1 = isset($syrolu1[$i]) ? $syrolu1[$i]['USR'] : '';
    $u2 = isset($syrolu2[$i]) ? $syrolu2[$i]['USR'] : '';
  ?>
  <tr>
    <td class="r1"><?php echo htmlspecialchars($u1); ?></td>
    <td class="r2"><?php echo htmlspecialchars($u2); ?></td>
  </tr>
  <?php endfor; ?>
</table>
<?php endif; ?>

<!-- ── SYROLD ─────────────────────────────────────────────────────────────── -->
<div class="sect">SYROLD &mdash; Portals Assigned to Role</div>
<?php if (count($only_syrold_r1) || count($only_syrold_r2)): ?>
<div class="diff">
  <?php if (count($only_syrold_r1)): ?>
  <span class="tag1"><?php echo htmlspecialchars($r1); ?> only</span>
  &nbsp;<?php echo implode(', ', array_map('htmlspecialchars', $only_syrold_r1)); ?><br>
  <?php endif; ?>
  <?php if (count($only_syrold_r2)): ?>
  <span class="tag2"><?php echo htmlspecialchars($r2); ?> only</span>
  &nbsp;<?php echo implode(', ', array_map('htmlspecialchars', $only_syrold_r2)); ?>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="match">Both roles have identical SYROLD portals.</div>
<?php endif; ?>
<table>
  <tr>
    <th colspan="2"><?php echo htmlspecialchars($r1); ?></th>
    <th colspan="2"><?php echo htmlspecialchars($r2); ?></th>
  </tr>
  <tr><th>PORT</th><th>SEQ</th><th>PORT</th><th>SEQ</th></tr>
  <?php
  $maxR = max(count($syrold1), count($syrold2));
  for ($i = 0; $i < $maxR; $i++):
    $a = isset($syrold1[$i]) ? $syrold1[$i] : null;
    $b = isset($syrold2[$i]) ? $syrold2[$i] : null;
  ?>
  <tr>
    <td class="r1"><?php echo $a ? htmlspecialchars($a['PORT']) : ''; ?></td>
    <td class="r1"><?php echo $a ? htmlspecialchars($a['SEQ'])  : ''; ?></td>
    <td class="r2"><?php echo $b ? htmlspecialchars($b['PORT']) : ''; ?></td>
    <td class="r2"><?php echo $b ? htmlspecialchars($b['SEQ'])  : ''; ?></td>
  </tr>
  <?php endfor; ?>
</table>

<!-- ── SYPORR ─────────────────────────────────────────────────────────────── -->
<div class="sect">SYPORR &mdash; Whitelist Mode &amp; Row Counts</div>
<table>
  <tr><th>ROLE</th><th>Mode</th><th>Top-Level Ports (PRPAGE='')</th></tr>
  <tr>
    <td><?php echo htmlspecialchars($r1); ?></td>
    <td><?php echo modeBadge($syporr1_cnt); ?></td>
    <td><?php echo htmlspecialchars(implode(', ', $ports1_top)); ?></td>
  </tr>
  <tr>
    <td><?php echo htmlspecialchars($r2); ?></td>
    <td><?php echo modeBadge($syporr2_cnt); ?></td>
    <td><?php echo htmlspecialchars(implode(', ', $ports2_top)); ?></td>
  </tr>
</table>
<?php if (count($only_r1_top) || count($only_r2_top)): ?>
<div class="diff">
  <strong>Top-level SYPORR differences:</strong><br>
  <?php if (count($only_r1_top)): ?>
  <span class="tag1"><?php echo htmlspecialchars($r1); ?> only</span>
  &nbsp;<?php echo implode(', ', array_map('htmlspecialchars', $only_r1_top)); ?><br>
  <?php endif; ?>
  <?php if (count($only_r2_top)): ?>
  <span class="tag2"><?php echo htmlspecialchars($r2); ?> only</span>
  &nbsp;<?php echo implode(', ', array_map('htmlspecialchars', $only_r2_top)); ?>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="match">Both roles have identical top-level SYPORR ports.</div>
<?php endif; ?>

<!-- ── GetMenu simulation ──────────────────────────────────────────────────── -->
<div class="sect">GetMenu Simulation &mdash; Portals That Would Appear in Left Nav</div>
<?php if (count($only_menu1) || count($only_menu2)): ?>
<div class="diff">
  <?php if (count($only_menu1)): ?>
  <span class="tag1"><?php echo htmlspecialchars($r1); ?> only</span>
  &nbsp;<?php echo implode(', ', array_map('htmlspecialchars', $only_menu1)); ?><br>
  <?php endif; ?>
  <?php if (count($only_menu2)): ?>
  <span class="tag2"><?php echo htmlspecialchars($r2); ?> only</span>
  &nbsp;<?php echo implode(', ', array_map('htmlspecialchars', $only_menu2)); ?>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="match">Both roles would show identical portals in the left nav.</div>
<?php endif; ?>
<table>
  <tr>
    <th colspan="2"><?php echo htmlspecialchars($r1); ?>
      (<?php echo count($menu1); ?> rows,
      <?php echo $syporr1_cnt > 0 ? 'WHITELIST' : 'BYPASS'; ?>)</th>
    <th colspan="2"><?php echo htmlspecialchars($r2); ?>
      (<?php echo count($menu2); ?> rows,
      <?php echo $syporr2_cnt > 0 ? 'WHITELIST' : 'BYPASS'; ?>)</th>
  </tr>
  <tr><th>PORT</th><th>FPID</th><th>PORT</th><th>FPID</th></tr>
  <?php
  $maxM = max(count($menu1), count($menu2));
  for ($i = 0; $i < $maxM; $i++):
    $a = isset($menu1[$i]) ? $menu1[$i] : null;
    $b = isset($menu2[$i]) ? $menu2[$i] : null;
  ?>
  <tr>
    <td class="r1"><?php echo $a ? htmlspecialchars($a['PORT']) : ''; ?></td>
    <td class="r1"><?php echo $a ? htmlspecialchars($a['FPID']) : ''; ?></td>
    <td class="r2"><?php echo $b ? htmlspecialchars($b['PORT']) : ''; ?></td>
    <td class="r2"><?php echo $b ? htmlspecialchars($b['FPID']) : ''; ?></td>
  </tr>
  <?php endfor; ?>
</table>

<!-- ── SYPGMO ─────────────────────────────────────────────────────────────── -->
<div class="sect">SYPGMO &mdash; Program Options
  (<?php echo htmlspecialchars($r1); ?>=<?php echo count($sypgmo1); ?>,
   <?php echo htmlspecialchars($r2); ?>=<?php echo count($sypgmo2); ?>)</div>
<?php if (count($only_pgmo1) || count($only_pgmo2)): ?>
<div class="diff">
  <?php if (count($only_pgmo1)): ?>
  <span class="tag1"><?php echo htmlspecialchars($r1); ?> only (<?php echo count($only_pgmo1); ?>)</span>
  &nbsp;<?php echo implode(', ', array_map('htmlspecialchars', array_values($only_pgmo1))); ?><br>
  <?php endif; ?>
  <?php if (count($only_pgmo2)): ?>
  <span class="tag2"><?php echo htmlspecialchars($r2); ?> only (<?php echo count($only_pgmo2); ?>)</span>
  &nbsp;<?php echo implode(', ', array_map('htmlspecialchars', array_values($only_pgmo2))); ?>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="match">SYPGMO entries are identical for both roles.</div>
<?php endif; ?>

<!-- ── SYPGMS ─────────────────────────────────────────────────────────────── -->
<div class="sect">SYPGMS &mdash; Program Security</div>
<table>
  <tr><th>ROLE</th><th>SYPGMS Rows</th></tr>
  <tr>
    <td><?php echo htmlspecialchars($r1); ?></td>
    <td><?php echo $sypgms1_cnt ? $sypgms1_cnt : '0'; ?></td>
  </tr>
  <tr>
    <td><?php echo htmlspecialchars($r2); ?></td>
    <td><?php echo $sypgms2_cnt ? $sypgms2_cnt : '0'; ?></td>
  </tr>
</table>

</body>
</html>
