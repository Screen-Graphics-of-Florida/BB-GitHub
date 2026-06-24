<?php
// Show SYPORT.FPID for each SYROLD portal + SYURLM lookup via FPID
// This reveals whether native portals have SYURLM entries via their actual FPID key.
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagFpid.php
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagFpid.php?role=ENAPOLES

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role = isset($_GET['role']) ? strtoupper(trim($_GET['role'])) : 'ENAPOLES';

function qrows($conn, $sql) {
    $rows = array();
    $s = @db2_exec($conn, $sql);
    if (!$s) return $rows;
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}
function qrow($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_assoc($s);
    return $r ? $r : null;
}

// SYROLD portals for role
$syrold = qrows($conn,
    "SELECT RTRIM(RDPORT) AS RDPORT, RDSEQN "
  . "FROM SGHDSDATA.SYROLD "
  . "WHERE RDROLE='$role' ORDER BY RDSEQN");

$rows = array();
foreach ($syrold as $sr) {
    $p = $sr['RDPORT'];

    // SYPORT top-level — need FPID
    $sport = qrow($conn,
        "SELECT RTRIM(FPID)   AS FPID, "
      . "       RTRIM(FPTSPT) AS FPTSPT, "
      . "       RTRIM(FPTSUS) AS FPTSUS "
      . "FROM SGHDSDATA.SYPORT "
      . "WHERE FPPORT='$p' AND RTRIM(FPPAGE)=''");

    // SYPORR top-level
    $sporr = qrow($conn,
        "SELECT RTRIM(PRTSPT) AS PRTSPT, RTRIM(PRTSUS) AS PRTSUS "
      . "FROM SGHDSDATA.SYPORR "
      . "WHERE PRROLE='$role' AND PRPORT='$p' AND RTRIM(PRPAGE)=''");

    // SYURLM lookup via FPID (the actual EIP lookup)
    $fpid    = $sport ? rtrim($sport['FPID']) : '';
    $syurlm  = null;
    if ($fpid !== '') {
        $safe = str_replace("'", "''", $fpid);
        $syurlm = qrow($conn,
            "SELECT RTRIM(FUID) AS FUID, RTRIM(FUTSPT) AS FUTSPT, "
          . "       RTRIM(FUTSUS) AS FUTSUS, RTRIM(FUURL) AS FUURL "
          . "FROM SGHDSDATA.SYURLM "
          . "WHERE FUID='$safe'");
    }

    // Also check /PORTAL suffix (SG portals)
    $syurlmPortal = null;
    $safe2 = str_replace("'", "''", "$p/PORTAL");
    $syurlmPortal = qrow($conn,
        "SELECT RTRIM(FUID) AS FUID, RTRIM(FUTSPT) AS FUTSPT "
      . "FROM SGHDSDATA.SYURLM WHERE FUID='$safe2'");

    $rows[] = array(
        'port'         => $p,
        'seq'          => rtrim($sr['RDSEQN']),
        'fpid'         => $fpid,
        'fptspt'       => $sport ? $sport['FPTSPT'] : '',
        'fptsus'       => $sport ? $sport['FPTSUS'] : '',
        'prtspt'       => $sporr ? $sporr['PRTSPT'] : null,
        'prtsus'       => $sporr ? $sporr['PRTSUS'] : null,
        'syurlm'       => $syurlm,
        'syurlmPortal' => $syurlmPortal,
    );
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>FPID Diag: <?= htmlspecialchars($role) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:12px Arial,sans-serif; background:#f0f2f5; padding:16px; }
h2 { background:#2a5a8c; color:#fff; padding:6px 14px; border-radius:4px;
     font-size:14px; border-bottom:3px solid #f90; margin-bottom:10px; }
form { margin-bottom:12px; display:flex; gap:8px; align-items:center; }
input  { padding:5px 9px; font-size:13px; border:1px solid #ccc;
         border-radius:4px; width:200px; }
button { padding:5px 13px; font-size:13px; background:#2a5a8c; color:#fff;
         border:none; border-radius:4px; cursor:pointer; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 2px 4px rgba(0,0,0,.08); }
th { background:#2a5a8c; color:#fff; padding:5px 8px;
     text-align:left; font-size:10px; }
td { padding:3px 8px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.y   { color:#2e7d32; font-weight:bold; }
.n   { color:#c62828; font-weight:bold; }
.m   { color:#aaa; }
.show { background:#e8f5e9; }
.hide { background:#fff8f8; }
</style>
</head>
<body>

<form>
  <input name="role" value="<?= htmlspecialchars($role) ?>">
  <button>Check</button>
</form>

<h2>SYPORT.FPID → SYURLM lookup for <?= htmlspecialchars($role) ?></h2>
<table>
  <tr>
    <th>Seq</th>
    <th>Portal</th>
    <th>SYPORT FPID</th>
    <th>FPTSPT</th>
    <th>FPTSUS</th>
    <th>SYPORR PRTSPT</th>
    <th>PRTSUS</th>
    <th>SYURLM via FPID?</th>
    <th>FUTSPT(FPID)</th>
    <th>FUURL(FPID)</th>
    <th>SYURLM /PORTAL?</th>
    <th>Likely shows?</th>
  </tr>
  <?php foreach ($rows as $r):
      $hasFpidUrlm  = ($r['syurlm'] !== null);
      $fpidUrlmOk   = $hasFpidUrlm && ($r['syurlm']['FUTSPT'] === 'Y');
      $hasPortalUrlm = ($r['syurlmPortal'] !== null);
      $portOk        = $hasPortalUrlm && ($r['syurlmPortal']['FUTSPT'] === 'Y');
      $hasPorr       = ($r['prtspt'] !== null);
      $porrOk        = $hasPorr && ($r['prtspt'] === 'Y');
      $fptsptOk      = ($r['fptspt'] === 'Y');
      // Shows if: (FPID→SYURLM FUTSPT=Y OR /PORTAL SYURLM FUTSPT=Y) AND SYPORR Y AND SYPORT Y
      $shows = ($fptsptOk && $porrOk && ($fpidUrlmOk || $portOk));
      $rowCls = $shows ? 'show' : 'hide';
  ?>
  <tr class="<?= $rowCls ?>">
    <td><?= htmlspecialchars($r['seq']) ?></td>
    <td><strong><?= htmlspecialchars($r['port']) ?></strong></td>
    <td><?= htmlspecialchars($r['fpid']) ?></td>
    <td class="<?= $fptsptOk?'y':'n' ?>"><?= htmlspecialchars($r['fptspt']===''?'(blank)':$r['fptspt']) ?></td>
    <td><?= htmlspecialchars($r['fptsus']) ?></td>
    <td class="<?= $porrOk?'y':($hasPorr?'n':'m') ?>">
      <?= $hasPorr ? htmlspecialchars($r['prtspt']===''?'(blank)':$r['prtspt']) : '—' ?>
    </td>
    <td><?= $hasPorr ? htmlspecialchars($r['prtsus']) : '<span class="m">—</span>' ?></td>
    <td class="<?= $hasFpidUrlm?'y':'n' ?>"><?= $hasFpidUrlm ? 'YES' : 'NO' ?></td>
    <td class="<?= $fpidUrlmOk?'y':($hasFpidUrlm?'n':'m') ?>">
      <?= $hasFpidUrlm ? htmlspecialchars($r['syurlm']['FUTSPT']===''?'(blank)':$r['syurlm']['FUTSPT']) : '—' ?>
    </td>
    <td style="max-width:200px;word-break:break-all;font-size:10px">
      <?= $hasFpidUrlm ? htmlspecialchars(substr($r['syurlm']['FUURL'],0,80)) : '<span class="m">—</span>' ?>
      <?= ($hasFpidUrlm && strlen($r['syurlm']['FUURL'])===0) ? '<em class="m">(blank)</em>' : '' ?>
    </td>
    <td class="<?= $portOk?'y':($hasPortalUrlm?'n':'m') ?>">
      <?= $hasPortalUrlm ? $r['syurlmPortal']['FUTSPT'] : '—' ?>
    </td>
    <td class="<?= $shows?'y':'n' ?>"><strong><?= $shows?'YES':'NO' ?></strong></td>
  </tr>
  <?php endforeach; ?>
</table>

<p style="margin-top:12px;font-size:11px;color:#666">
  <strong>Shows?</strong> = SYPORT FPTSPT=Y AND SYPORR PRTSPT=Y AND (SYURLM via FPID FUTSPT=Y OR SYURLM /PORTAL FUTSPT=Y)
</p>

</body>
</html>
