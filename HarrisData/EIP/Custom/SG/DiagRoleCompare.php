<?php
// DiagRoleCompare.php
// Compares two roles side-by-side: SYPORR row counts (top-level vs sub-page),
// and shows whether each SYROLD portal has a top-level SYPORR row.
// Goal: understand why PLANNING sees all portals but ENAPOLES does not.
//
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagRoleCompare.php
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagRoleCompare.php?r1=PLANNING&r2=ENAPOLES

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$r1 = isset($_GET['r1']) ? strtoupper(trim($_GET['r1'])) : 'PLANNING';
$r2 = isset($_GET['r2']) ? strtoupper(trim($_GET['r2'])) : 'ENAPOLES';

function qrows($conn, $sql) {
    $rows = array(); $s = @db2_exec($conn, $sql);
    if (!$s) return $rows;
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}
function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s); return $r ? db2_result($s, 0) : null;
}

function getRoleSummary($conn, $role) {
    $totalRows = qval($conn,
        "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");
    $topLevel = qval($conn,
        "SELECT COUNT(*) FROM SGHDSDATA.SYPORR "
      . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)=''");
    $subPage = qval($conn,
        "SELECT COUNT(*) FROM SGHDSDATA.SYPORR "
      . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)<>''");
    $syroldCount = qval($conn,
        "SELECT COUNT(*) FROM SGHDSDATA.SYROLD WHERE RDROLE='$role'");
    return array(
        'total'    => (int)$totalRows,
        'toplevel' => (int)$topLevel,
        'subpage'  => (int)$subPage,
        'syrold'   => (int)$syroldCount,
        'mode'     => ((int)$topLevel === 0) ? 'BYPASS' : 'WHITELIST',
    );
}

function getPortalRows($conn, $role) {
    // SYROLD portals
    $syrold = qrows($conn,
        "SELECT RTRIM(RDPORT) AS PORT, RDSEQN AS SEQ "
      . "FROM SGHDSDATA.SYROLD WHERE RDROLE='$role' ORDER BY RDSEQN");
    $out = array();
    foreach ($syrold as $sr) {
        $p = $sr['PORT'];
        // top-level SYPORR
        $sporr = qrows($conn,
            "SELECT RTRIM(PRTSPT) AS PRTSPT, RTRIM(PRTSUS) AS PRTSUS "
          . "FROM SGHDSDATA.SYPORR "
          . "WHERE PRROLE='$role' AND PRPORT='$p' AND RTRIM(PRPAGE)=''");
        // /PORTAL in SYURLM?
        $safe = str_replace("'","''",$p.'/PORTAL');
        $hasPortal = qval($conn,
            "SELECT COUNT(*) FROM SGHDSDATA.SYURLM WHERE FUID='$safe'");
        $out[$p] = array(
            'seq'      => rtrim($sr['SEQ']),
            'sporr'    => $sporr,
            'hasPortal'=> ((int)$hasPortal > 0),
        );
    }
    return $out;
}

$s1 = getRoleSummary($conn, $r1);
$s2 = getRoleSummary($conn, $r2);
$p1 = getPortalRows($conn, $r1);
$p2 = getPortalRows($conn, $r2);

// Union of all portal codes
$allPortals = array_unique(array_merge(array_keys($p1), array_keys($p2)));

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Role Compare</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:12px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.header { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
          padding:10px 20px; border-radius:6px; border-bottom:3px solid #f90;
          margin-bottom:16px; }
.header h2 { font-size:18px; }
form { margin-bottom:14px; display:flex; gap:8px; align-items:center; }
input  { padding:5px 9px; font-size:13px; border:1px solid #ccc;
         border-radius:4px; width:160px; }
button { padding:5px 13px; font-size:13px; background:#2a5a8c; color:#fff;
         border:none; border-radius:4px; cursor:pointer; }
.summary { display:flex; gap:14px; margin-bottom:16px; }
.sbox { background:#fff; border-radius:6px; padding:12px 18px; flex:1;
        box-shadow:0 2px 4px rgba(0,0,0,.07); }
.sbox h3 { font-size:13px; color:#2a5a8c; margin-bottom:8px; border-bottom:1px solid #eee; padding-bottom:4px; }
.sbox .kv { display:flex; gap:6px; margin:3px 0; font-size:12px; }
.sbox .k { color:#666; min-width:120px; }
.sbox .v { font-weight:bold; }
.bypass { color:#2e7d32; }
.whitelist { color:#c62828; }
table { width:100%; border-collapse:collapse; background:#fff;
        border-radius:6px; overflow:hidden;
        box-shadow:0 2px 4px rgba(0,0,0,.07); }
th { background:#2a5a8c; color:#fff; padding:5px 10px; text-align:left; font-size:11px; }
td { padding:3px 10px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f2f5; }
.y { color:#2e7d32; font-weight:bold; }
.n { color:#c62828; font-weight:bold; }
.m { color:#aaa; }
.diff { background:#fff8e1; }
</style>
</head>
<body>

<div class="header">
  <h2>Role Comparison: <?= htmlspecialchars($r1) ?> vs <?= htmlspecialchars($r2) ?></h2>
</div>

<form>
  Role 1: <input name="r1" value="<?= htmlspecialchars($r1) ?>">
  Role 2: <input name="r2" value="<?= htmlspecialchars($r2) ?>">
  <button>Compare</button>
</form>

<div class="summary">
  <?php foreach (array($r1=>$s1, $r2=>$s2) as $role=>$s): ?>
  <div class="sbox">
    <h3><?= htmlspecialchars($role) ?></h3>
    <div class="kv"><span class="k">Mode:</span>
      <span class="v <?= strtolower($s['mode']) ?>"><?= $s['mode'] ?></span></div>
    <div class="kv"><span class="k">SYPORR total:</span>
      <span class="v"><?= $s['total'] ?></span></div>
    <div class="kv"><span class="k">Top-level (PRPAGE=''):</span>
      <span class="v"><?= $s['toplevel'] ?></span></div>
    <div class="kv"><span class="k">Sub-page rows:</span>
      <span class="v"><?= $s['subpage'] ?></span></div>
    <div class="kv"><span class="k">SYROLD portals:</span>
      <span class="v"><?= $s['syrold'] ?></span></div>
    <div class="kv"><span class="k">Bypass if:</span>
      <span class="v">top-level SYPORR = 0</span></div>
  </div>
  <?php endforeach; ?>
</div>

<table>
  <tr>
    <th>Portal</th>
    <th><?= htmlspecialchars($r1) ?> in SYROLD?</th>
    <th><?= htmlspecialchars($r1) ?> SYPORR top-level</th>
    <th>/PORTAL in SYURLM?</th>
    <th><?= htmlspecialchars($r2) ?> in SYROLD?</th>
    <th><?= htmlspecialchars($r2) ?> SYPORR top-level</th>
    <th>Diff?</th>
  </tr>
  <?php
  sort($allPortals);
  foreach ($allPortals as $port):
      $d1 = isset($p1[$port]) ? $p1[$port] : null;
      $d2 = isset($p2[$port]) ? $p2[$port] : null;
      $in1 = ($d1 !== null);
      $in2 = ($d2 !== null);
      $has1 = $in1 && !empty($d1['sporr']);
      $has2 = $in2 && !empty($d2['sporr']);
      $prtspt1 = $has1 ? $d1['sporr'][0]['PRTSPT'] : '';
      $prtsus1  = $has1 ? $d1['sporr'][0]['PRTSUS'] : '';
      $prtspt2 = $has2 ? $d2['sporr'][0]['PRTSPT'] : '';
      $prtsus2  = $has2 ? $d2['sporr'][0]['PRTSUS'] : '';
      $hasPortal = ($d1 && $d1['hasPortal']) || ($d2 && $d2['hasPortal']);
      $diff = ($has1 != $has2);
      $rowCls = $diff ? 'diff' : '';
  ?>
  <tr class="<?= $rowCls ?>">
    <td><strong><?= htmlspecialchars($port) ?></strong></td>
    <td class="<?= $in1?'y':'m' ?>"><?= $in1?'YES':'—' ?></td>
    <td>
      <?php if (!$in1): ?><span class="m">—</span>
      <?php elseif (!$has1): ?><span class="n">none</span>
      <?php else: ?>
        <span class="<?= $prtspt1==='Y'?'y':'n' ?>"><?= htmlspecialchars($prtspt1) ?></span>
        <span style="color:#666">(<?= htmlspecialchars($prtsus1) ?>)</span>
      <?php endif; ?>
    </td>
    <td class="<?= $hasPortal?'y':'m' ?>"><?= $hasPortal?'YES':'—' ?></td>
    <td class="<?= $in2?'y':'m' ?>"><?= $in2?'YES':'—' ?></td>
    <td>
      <?php if (!$in2): ?><span class="m">—</span>
      <?php elseif (!$has2): ?><span class="n">none</span>
      <?php else: ?>
        <span class="<?= $prtspt2==='Y'?'y':'n' ?>"><?= htmlspecialchars($prtspt2) ?></span>
        <span style="color:#666">(<?= htmlspecialchars($prtsus2) ?>)</span>
      <?php endif; ?>
    </td>
    <td class="<?= $diff?'n':'y' ?>"><?= $diff?'DIFF':'—' ?></td>
  </tr>
  <?php endforeach; ?>
</table>

</body>
</html>
