<?php
// GrantCostAuth.php
// Grants *PUBLIC *ALL on the cost history objects, matching the authority
// SGHDSDATA/HDMCMM already carries.
//
// Why this is needed:
//   The objects were created by the web server profile QTMHHTTP, and
//   CREATE TABLE defaults *PUBLIC to *EXCLUDE. The inquiry runs under the
//   signed-on EIP profile (HDS), which therefore gets SQLCODE -551.
//
// QTMHHTTP owns the objects, so it can grant on them. The grants run through
// QSYS2.QCMDEXC on the owner connection; verification runs on the EIP user's
// connection, so a PASS here means the inquiry itself will work.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/Inventory%20Management/GrantCostAuth.php
//      add ?go=1 to execute.

require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

$GO = isset($_GET['go']) && $_GET['go'] === '1';

function h($v) { return htmlspecialchars(trim((string)$v)); }
function rows($conn, $sql, &$err = null) {
    $err = null;
    if (!$conn) { $err = 'no connection'; return array(); }
    $st = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$st) { $err = db2_stmt_errormsg(); return array(); }
    $o = array();
    while ($r = db2_fetch_assoc($st)) $o[] = $r;
    db2_free_stmt($st);
    return $o;
}
function one($conn, $sql, &$err = null) {
    $r = rows($conn, $sql, $err);
    return count($r) ? $r[0] : null;
}

// Owner connection (QTMHHTTP) does the granting.
$connOwner = @db2_connect('*LOCAL', '', '');
// EIP user connection (HDS) proves the fix.
$connUser  = $i5Connect->getConnection();

// AUT(*ALL) mirrors SGHDSDATA/HDMCMM, which is *PUBLIC *ALL.
// The nightly capture also needs write authority on SGCSTHST when it runs
// under the job scheduler's profile, so *ALL covers that case too.
$cmds = array(
    array('SGOBJ/SGCSTHST',   '*FILE', 'History table'),
    array('SGOBJ/SGCSTHSTL1', '*FILE', 'Index - opened to satisfy queries'),
    array('SGOBJ/SGCSTHSTL2', '*FILE', 'Index - opened to satisfy queries'),
    array('SGPGM/SGCSTCAP',   '*PGM',  'Capture procedure - called by the nightly CL'),
);

$results = array();
if ($GO) {
    foreach ($cmds as $c) {
        list($obj, $typ, $note) = $c;
        $cmd = "GRTOBJAUT OBJ($obj) OBJTYPE($typ) USER(*PUBLIC) AUT(*ALL)";
        $sql = "CALL QSYS2.QCMDEXC('" . str_replace("'", "''", $cmd) . "')";
        $t0  = microtime(true);
        $ok  = @db2_exec($connOwner, $sql);
        $results[] = array($cmd, microtime(true) - $t0,
                           $ok ? 'OK' : 'FAILED',
                           $ok ? '' : db2_stmt_errormsg());
        if ($ok) @db2_free_stmt($ok);
    }
}

// ---- current authority ---------------------------------------------------

function pubAut($conn, $lib, $obj) {
    $r = one($conn,
        "SELECT OBJECT_AUTHORITY FROM QSYS2.OBJECT_PRIVILEGES
          WHERE SYSTEM_OBJECT_SCHEMA = '$lib' AND SYSTEM_OBJECT_NAME = '$obj'
            AND AUTHORIZATION_NAME = '*PUBLIC'", $e);
    return $r ? trim($r['OBJECT_AUTHORITY']) : ($e ? 'ERR' : 'none');
}

$state = array();
foreach ($cmds as $c) {
    list($obj, $typ, $note) = $c;
    list($lib, $nm) = explode('/', $obj);
    $state[] = array('obj' => $obj, 'typ' => $typ, 'note' => $note,
                     'aut' => pubAut($connOwner, $lib, $nm));
}
$modelAut = pubAut($connOwner, 'SGHDSDATA', 'HDMCMM');

// ---- does the EIP user actually get in now? ------------------------------

$probe   = one($connUser, "SELECT COUNT(*) AS N FROM SGOBJ.SGCSTHST", $probeErr);
$probeIx = one($connUser,
    "SELECT COUNT(*) AS N FROM SGOBJ.SGCSTHST
      WHERE CHPLT = 1 AND CHCSET = 1 AND CHENDD = '9999-12-31'", $probeIxErr);

if ($connOwner) db2_close($connOwner);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Grant Cost History Authority</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.sect { font-weight:bold; font-size:14px; margin:22px 0 8px; color:#1a3d5c;
        border-bottom:2px solid #90caf9; padding-bottom:3px; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.big  { border-radius:5px; padding:14px 18px; margin-bottom:14px; font-size:14px; }
.bigok  { background:#e8f5e9; border:2px solid #66bb6a; }
.bigbad { background:#ffebee; border:2px solid #ef5350; }
.warn { background:#fff8e1; border:2px solid #ffc107; border-radius:5px;
        padding:12px 16px; margin-bottom:12px; font-size:13px; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px; font-family:monospace; }
table.full { border-collapse:collapse; width:100%; background:#fff; border-radius:4px;
             overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
table.full th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
                padding:5px 8px; text-align:left; font-size:11px; }
table.full td { padding:4px 8px; font-size:11px; border-bottom:1px solid #f0f0f0;
                font-family:monospace; }
pre { background:#1e1e1e; color:#d4d4d4; padding:12px 14px; border-radius:4px;
      font-size:12px; overflow-x:auto; line-height:1.6; margin-bottom:12px; }
.go { display:inline-block; background:#c62828; color:#fff !important; padding:9px 20px;
      border-radius:4px; text-decoration:none; font-weight:bold; font-size:13px; }
.pass { color:#2e7d32; font-weight:bold; } .fail { color:#c62828; font-weight:bold; }
</style>
</head>
<body>

<div class="hdr">Grant Cost History Authority &mdash; *PUBLIC *ALL</div>
<div class="info">
  Run: <?php echo date('Y-m-d H:i:s'); ?> &nbsp;|&nbsp;
  Mode: <strong><?php echo $GO ? 'EXECUTE' : 'DRY RUN'; ?></strong><br>
  Grants run as <strong>QTMHHTTP</strong>, which owns these objects.
  Verification runs as the signed-on EIP profile, so a PASS below means the
  inquiry will work.
</div>

<div class="sect">Current *PUBLIC authority</div>
<table class="full">
  <tr><th>Object</th><th>Type</th><th>*PUBLIC now</th><th>Target</th><th>Note</th></tr>
  <?php foreach ($state as $s): ?>
  <tr>
    <td><?php echo h($s['obj']); ?></td>
    <td><?php echo h($s['typ']); ?></td>
    <td class="<?php echo $s['aut'] === '*ALL' ? 'pass' : 'fail'; ?>">
        <?php echo h($s['aut']); ?></td>
    <td>*ALL</td>
    <td><?php echo h($s['note']); ?></td>
  </tr>
  <?php endforeach; ?>
  <tr>
    <td>SGHDSDATA/HDMCMM</td><td>*FILE</td>
    <td class="pass"><?php echo h($modelAut); ?></td><td>&mdash;</td>
    <td>Harris cost master &mdash; the model being matched</td>
  </tr>
</table>

<?php if (!$GO): ?>
<div class="sect">Commands to be run</div>
<pre><?php foreach ($cmds as $c)
        echo h("GRTOBJAUT OBJ({$c[0]}) OBJTYPE({$c[1]}) USER(*PUBLIC) AUT(*ALL)") . "\n"; ?></pre>
<div class="warn">
  <strong>*ALL</strong> matches what SGHDSDATA/HDMCMM already carries, so this
  is the same posture as the rest of the cost data &mdash; not a loosening
  relative to house practice. It does mean any user could in principle modify
  history rows; if you would rather restrict that, <code>*USE</code> is enough
  for the inquiry, but the nightly capture then needs its own explicit grant
  to write.
  <br><br>
  <a class="go" href="?go=1">Grant *PUBLIC *ALL</a>
</div>
<?php else: ?>
<div class="sect">Execution</div>
<table class="full">
  <tr><th>Command</th><th>Seconds</th><th>Result</th><th>Detail</th></tr>
  <?php foreach ($results as $r): ?>
  <tr><td><?php echo h($r[0]); ?></td>
      <td><?php echo number_format($r[1], 2); ?></td>
      <td class="<?php echo $r[2] === 'OK' ? 'pass' : 'fail'; ?>"><?php echo h($r[2]); ?></td>
      <td><?php echo h($r[3]); ?></td></tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<div class="sect">Can the signed-on EIP user read it now?</div>
<table class="full">
  <tr><th>Test</th><th>Result</th></tr>
  <tr><td>SELECT COUNT(*) FROM SGOBJ.SGCSTHST</td>
      <td class="<?php echo $probe ? 'pass' : 'fail'; ?>">
        <?php echo $probe ? number_format((float)$probe['N']) . ' rows - PASS'
                          : h($probeErr); ?></td></tr>
  <tr><td>Indexed lookup (current Standard rows)</td>
      <td class="<?php echo $probeIx ? 'pass' : 'fail'; ?>">
        <?php echo $probeIx ? number_format((float)$probeIx['N']) . ' rows - PASS'
                            : h($probeIxErr); ?></td></tr>
</table>

<?php if ($probe && $probeIx): ?>
<div class="big bigok">
  <strong>Authority is correct.</strong> The signed-on EIP profile can read the
  history table and its indexes. Open the inquiry:<br>
  <a href="CostHistoryInquiry.php?item=94-D-91A-CA">CostHistoryInquiry.php</a>
</div>
<?php elseif ($GO): ?>
<div class="big bigbad">
  <strong>Still blocked.</strong> The grants ran but the EIP profile still
  cannot read. Check the failing detail above.
</div>
<?php endif; ?>

</body>
</html>