<?php
// DiagCostAuth.php
// READ-ONLY. Why the inquiry gets SQLCODE -551 on SGOBJ/SGCSTHST.
//
// The build/diagnostic pages connect with db2_connect('*LOCAL','',''), which
// runs under the web server's own profile -- the profile that created and
// therefore owns the objects. The inquiry uses $i5Connect->getConnection(),
// which runs under the signed-on EIP user. That user has no authority to the
// new objects, because CRTLIB/CREATE TABLE default *PUBLIC to *EXCLUDE.
//
// This page reports both profiles side by side, the owner and *PUBLIC
// authority on each new object, and the same for SGHDSDATA/HDMCMM so the
// grant can match an existing, already-working object rather than guessing.
//
// Writes nothing.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/Inventory%20Management/DiagCostAuth.php

require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

function h($v) { return htmlspecialchars(trim((string)$v)); }

function rows($conn, $sql, &$err = null) {
    $err = null;
    if (!$conn) { $err = 'no connection'; return array(); }
    $st = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$st) { $err = db2_stmt_errormsg(); return array(); }
    $out = array();
    while ($r = db2_fetch_assoc($st)) $out[] = $r;
    db2_free_stmt($st);
    return $out;
}
function one($conn, $sql, &$err = null) {
    $r = rows($conn, $sql, $err);
    return count($r) ? $r[0] : null;
}

// ---- the two connections -------------------------------------------------

$connWeb  = @db2_connect('*LOCAL', '', '');       // owner profile
$connUser = $i5Connect->getConnection();          // signed-on EIP user

$whoSql = "SELECT CURRENT_USER AS CUR, SESSION_USER AS SES, SYSTEM_USER AS SYS
             FROM SYSIBM.SYSDUMMY1";
$whoWeb  = one($connWeb,  $whoSql, $whoWebErr);
$whoUser = one($connUser, $whoSql, $whoUserErr);

// Can the signed-on user actually read the table?
$probe = one($connUser, "SELECT COUNT(*) AS N FROM SGOBJ.SGCSTHST", $probeErr);
$probeHd = one($connUser, "SELECT COUNT(*) AS N FROM SGHDSDATA.HDMCMM", $probeHdErr);

// ---- object owners -------------------------------------------------------
// Read with the OWNER connection, which is authorised to look.

$targets = array(
    array('SGOBJ', 'SGCSTHST',   '*FILE', 'History table'),
    array('SGOBJ', 'SGCSTHSTL1', '*FILE', 'Index'),
    array('SGOBJ', 'SGCSTHSTL2', '*FILE', 'Index'),
    array('SGPGM', 'SGCSTCAP',   '*PGM',  'Capture procedure'),
    array('SGHDSDATA', 'HDMCMM', '*FILE', 'Harris cost master (the model)'),
);

$owners = array();
foreach ($targets as $t) {
    list($lib, $obj, $typ, $note) = $t;
    $r = one($connWeb,
        "SELECT OBJNAME, OBJTYPE, OBJOWNER, OBJDEFINER, OBJTEXT
           FROM TABLE(QSYS2.OBJECT_STATISTICS('$lib','$typ','$obj')) x", $e);
    $owners[] = array('lib' => $lib, 'obj' => $obj, 'typ' => $typ,
                      'note' => $note, 'row' => $r, 'err' => $e);
}

// Library-level authority matters too -- *USE on the library is required
// before any object inside it can be reached.
$libs = array();
foreach (array('SGOBJ', 'SGPGM', 'SGHDSDATA') as $l) {
    $r = one($connWeb,
        "SELECT OBJNAME, OBJOWNER, OBJTEXT
           FROM TABLE(QSYS2.OBJECT_STATISTICS('QSYS','*LIB','$l')) x", $e);
    $libs[] = array('lib' => $l, 'row' => $r, 'err' => $e);
}

// ---- privilege lists -----------------------------------------------------

function privs($conn, $lib, $obj, &$err) {
    return rows($conn,
        "SELECT AUTHORIZATION_NAME, OBJECT_AUTHORITY,
                COALESCE(OBJECT_OPERATIONAL,'') AS OPR,
                COALESCE(DATA_READ,'')  AS DREAD,
                COALESCE(DATA_ADD,'')   AS DADD,
                COALESCE(DATA_UPDATE,'')AS DUPD,
                COALESCE(DATA_DELETE,'')AS DDEL
           FROM QSYS2.OBJECT_PRIVILEGES
          WHERE SYSTEM_OBJECT_SCHEMA = '$lib'
            AND SYSTEM_OBJECT_NAME   = '$obj'
          ORDER BY AUTHORIZATION_NAME", $err);
}

$pHist  = privs($connWeb, 'SGOBJ', 'SGCSTHST', $pHistErr);
$pModel = privs($connWeb, 'SGHDSDATA', 'HDMCMM', $pModelErr);
$pLib   = privs($connWeb, 'QSYS', 'SGOBJ', $pLibErr);

if ($connWeb) db2_close($connWeb);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cost History Authority Diagnostic</title>
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
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:9px 14px; margin-bottom:10px; font-size:12px; }
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
.pass { color:#2e7d32; font-weight:bold; } .fail { color:#c62828; font-weight:bold; }
</style>
</head>
<body>

<div class="hdr">Cost History &mdash; Authority Diagnostic</div>
<div class="info">Read-only. Run: <?php echo date('Y-m-d H:i:s'); ?></div>

<div class="sect">Which profile is each connection running under?</div>
<table class="full">
  <tr><th>Connection</th><th>Used by</th><th>CURRENT_USER</th>
      <th>SESSION_USER</th><th>SYSTEM_USER</th></tr>
  <tr>
    <td>db2_connect('*LOCAL','','')</td>
    <td>Build and diagnostic pages &mdash; created the objects</td>
    <td><?php echo $whoWeb ? h($whoWeb['CUR']) : h($whoWebErr); ?></td>
    <td><?php echo $whoWeb ? h($whoWeb['SES']) : ''; ?></td>
    <td><?php echo $whoWeb ? h($whoWeb['SYS']) : ''; ?></td>
  </tr>
  <tr>
    <td>$i5Connect-&gt;getConnection()</td>
    <td>The inquiry &mdash; runs as the signed-on EIP user</td>
    <td><?php echo $whoUser ? h($whoUser['CUR']) : h($whoUserErr); ?></td>
    <td><?php echo $whoUser ? h($whoUser['SES']) : ''; ?></td>
    <td><?php echo $whoUser ? h($whoUser['SYS']) : ''; ?></td>
  </tr>
</table>

<div class="sect">What can the signed-on user actually read?</div>
<table class="full">
  <tr><th>Object</th><th>Result</th></tr>
  <tr><td>SGOBJ.SGCSTHST</td>
      <td class="<?php echo $probe ? 'pass' : 'fail'; ?>">
        <?php echo $probe ? number_format((float)$probe['N']) . ' rows - OK'
                          : h($probeErr); ?></td></tr>
  <tr><td>SGHDSDATA.HDMCMM</td>
      <td class="<?php echo $probeHd ? 'pass' : 'fail'; ?>">
        <?php echo $probeHd ? number_format((float)$probeHd['N']) . ' rows - OK'
                            : h($probeHdErr); ?></td></tr>
</table>
<div class="info">
  If HDMCMM reads fine and SGCSTHST does not, the difference is purely object
  authority &mdash; the connection and library list are working.
</div>

<div class="sect">Object owners</div>
<table class="full">
  <tr><th>Object</th><th>Type</th><th>Owner</th><th>Definer</th><th>Text</th><th>Note</th></tr>
  <?php foreach ($owners as $o): ?>
  <tr>
    <td><?php echo h($o['lib'] . '/' . $o['obj']); ?></td>
    <td><?php echo h($o['typ']); ?></td>
    <td><?php echo $o['row'] ? h($o['row']['OBJOWNER']) : '<span class="fail">'
                 . h($o['err']) . '</span>'; ?></td>
    <td><?php echo $o['row'] ? h($o['row']['OBJDEFINER']) : ''; ?></td>
    <td><?php echo $o['row'] ? h($o['row']['OBJTEXT']) : ''; ?></td>
    <td><?php echo h($o['note']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<div class="sect">Library owners</div>
<table class="full">
  <tr><th>Library</th><th>Owner</th><th>Text</th></tr>
  <?php foreach ($libs as $l): ?>
  <tr><td><?php echo h($l['lib']); ?></td>
      <td><?php echo $l['row'] ? h($l['row']['OBJOWNER']) : '<span class="fail">'
               . h($l['err']) . '</span>'; ?></td>
      <td><?php echo $l['row'] ? h($l['row']['OBJTEXT']) : ''; ?></td></tr>
  <?php endforeach; ?>
</table>

<div class="sect">Privileges on SGOBJ/SGCSTHST (the broken one)</div>
<?php if ($pHistErr): ?><div class="bad"><?php echo h($pHistErr); ?></div><?php endif; ?>
<table class="full">
  <tr><th>Authorization name</th><th>Authority</th><th>Opr</th>
      <th>Read</th><th>Add</th><th>Upd</th><th>Del</th></tr>
  <?php foreach ($pHist as $p): ?>
  <tr><td><?php echo h($p['AUTHORIZATION_NAME']); ?></td>
      <td><?php echo h($p['OBJECT_AUTHORITY']); ?></td>
      <td><?php echo h($p['OPR']); ?></td><td><?php echo h($p['DREAD']); ?></td>
      <td><?php echo h($p['DADD']); ?></td><td><?php echo h($p['DUPD']); ?></td>
      <td><?php echo h($p['DDEL']); ?></td></tr>
  <?php endforeach; ?>
</table>

<div class="sect">Privileges on SGHDSDATA/HDMCMM (the working model to copy)</div>
<?php if ($pModelErr): ?><div class="bad"><?php echo h($pModelErr); ?></div><?php endif; ?>
<table class="full">
  <tr><th>Authorization name</th><th>Authority</th><th>Opr</th>
      <th>Read</th><th>Add</th><th>Upd</th><th>Del</th></tr>
  <?php foreach ($pModel as $p): ?>
  <tr><td><?php echo h($p['AUTHORIZATION_NAME']); ?></td>
      <td><?php echo h($p['OBJECT_AUTHORITY']); ?></td>
      <td><?php echo h($p['OPR']); ?></td><td><?php echo h($p['DREAD']); ?></td>
      <td><?php echo h($p['DADD']); ?></td><td><?php echo h($p['DUPD']); ?></td>
      <td><?php echo h($p['DDEL']); ?></td></tr>
  <?php endforeach; ?>
</table>

<div class="sect">Privileges on the SGOBJ library itself</div>
<?php if ($pLibErr): ?><div class="bad"><?php echo h($pLibErr); ?></div><?php endif; ?>
<table class="full">
  <tr><th>Authorization name</th><th>Authority</th></tr>
  <?php foreach ($pLib as $p): ?>
  <tr><td><?php echo h($p['AUTHORIZATION_NAME']); ?></td>
      <td><?php echo h($p['OBJECT_AUTHORITY']); ?></td></tr>
  <?php endforeach; ?>
</table>

<div class="sect">Likely fix</div>
<div class="info">
  Read-only authority for everyone, matching how a reporting file is normally
  exposed. Run on a green screen from a profile with authority. Confirm the
  HDMCMM model above first &mdash; if it shows something narrower than
  <code>*PUBLIC *USE</code>, mirror that instead.
</div>
<pre>GRTOBJAUT OBJ(QSYS/SGOBJ)        OBJTYPE(*LIB)  USER(*PUBLIC) AUT(*USE)
GRTOBJAUT OBJ(SGOBJ/SGCSTHST)    OBJTYPE(*FILE) USER(*PUBLIC) AUT(*USE)
GRTOBJAUT OBJ(SGOBJ/SGCSTHSTL1)  OBJTYPE(*FILE) USER(*PUBLIC) AUT(*USE)
GRTOBJAUT OBJ(SGOBJ/SGCSTHSTL2)  OBJTYPE(*FILE) USER(*PUBLIC) AUT(*USE)
GRTOBJAUT OBJ(SGPGM/SGCSTCAP)    OBJTYPE(*PGM)  USER(*PUBLIC) AUT(*USE)</pre>
<div class="info">
  <strong>*USE is read-only.</strong> It lets the inquiry select rows; it does
  not let anyone insert, update or delete. Only the nightly capture writes, and
  it runs under its own profile.
</div>

</body>
</html>