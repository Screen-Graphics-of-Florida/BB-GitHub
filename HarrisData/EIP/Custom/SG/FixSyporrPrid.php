<?php
// FixSyporrPrid.php
// Repair SYPORR.PRID on SG-portal rows so Portal By Role Maintenance can resolve
// its Description column.
//
// Harris writes PRID as the target FPID ('SGINQ/PORTAL', 'SGINQ_OE'). SgApplyAll.php
// used to write a synthetic '$role/$port/$page/$seq' ('CUSTSRVC/SGINQ/SGINQ/4.00'),
// which the maintenance screen cannot look up — so those rows show an unlabelled
// checkbox with no Description. Menu rendering is unaffected either way: the nav
// joins on FPPORT/FPPAGE/FPSEQ, never on PRID (see SgReportNav.php).
//
// The correct PRID is derived from SYPORT by PRPORT/PRPAGE/PRSEQ, not from a
// hardcoded category list, so this stays right if the grid ever changes.
//
// SCOPE: SG* portals only. Native Harris portal rows are never touched.
//
// Preview:  .../Custom/SG/FixSyporrPrid.php
// Execute:  .../Custom/SG/FixSyporrPrid.php?confirm=FIX
// Options:  &role=CUSTSRVC   limit to one role
//           &schema=S5HDSDATA | SGHDSDATA   (default: port 5610 -> S5, else SG)

$PORTALS = array('SGINQ','SGDASH','SGDINT','SGRPT','SGSOP','SGTRAIN');
$pList   = "'" . implode("','", $PORTALS) . "'";

$port   = (string)@$_SERVER['SERVER_PORT'];
$schema = ($port === '5610') ? 'S5HDSDATA' : 'SGHDSDATA';
if (!empty($_GET['schema'])) {
    $s = strtoupper(trim($_GET['schema']));
    if (in_array($s, array('S5HDSDATA','SGHDSDATA'), true)) $schema = $s;
}
$isLive = ($schema === 'SGHDSDATA');

$role = '';
if (!empty($_GET['role'])) {
    $r = strtoupper(trim($_GET['role']));
    if (preg_match('/^[A-Z][A-Z0-9_]{0,9}$/', $r)) $role = $r;
}
$roleFilter = ($role !== '') ? " AND RTRIM(pr.PRROLE)='" . str_replace("'","''",$role) . "' " : '';

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB2 connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

function h($v) { return htmlspecialchars(trim((string)$v)); }

// Correlated subquery giving the FPID that PRID should hold.
$goodPrid = "(SELECT RTRIM(p.FPID) FROM $schema.SYPORT p "
          . " WHERE RTRIM(p.FPPORT)=RTRIM(pr.PRPORT) "
          . "   AND RTRIM(p.FPPAGE)=RTRIM(pr.PRPAGE) "
          . "   AND p.FPSEQ=pr.PRSEQ)";

// Rows that are fixable: a matching SYPORT row exists and PRID disagrees with it.
$where = "WHERE RTRIM(pr.PRPORT) IN ($pList) $roleFilter "
       . "  AND EXISTS (SELECT 1 FROM $schema.SYPORT p "
       . "              WHERE RTRIM(p.FPPORT)=RTRIM(pr.PRPORT) "
       . "                AND RTRIM(p.FPPAGE)=RTRIM(pr.PRPAGE) "
       . "                AND p.FPSEQ=pr.PRSEQ) "
       . "  AND RTRIM(pr.PRID) <> $goodPrid ";

$rows = array();
$err  = '';
$stmt = @db2_exec($conn,
    "SELECT RTRIM(pr.PRROLE) AS PRROLE, RTRIM(pr.PRPORT) AS PRPORT, "
  . "       RTRIM(pr.PRPAGE) AS PRPAGE, pr.PRSEQ, RTRIM(pr.PRSEL) AS PRSEL, "
  . "       RTRIM(pr.PRID) AS OLD_PRID, $goodPrid AS NEW_PRID "
  . "FROM $schema.SYPORR pr $where "
  . "ORDER BY pr.PRROLE, pr.PRPORT, pr.PRPAGE, pr.PRSEQ");
if ($stmt === false) $err = db2_stmt_errormsg();
else while ($r = db2_fetch_assoc($stmt)) $rows[] = $r;

// Orphan rows — SYPORR with no matching SYPORT row. Not fixable here; they are a
// separate problem (a checkbox that can never resolve to a page).
$orphans = array();
$oStmt = @db2_exec($conn,
    "SELECT RTRIM(pr.PRROLE) AS PRROLE, RTRIM(pr.PRPORT) AS PRPORT, "
  . "       RTRIM(pr.PRPAGE) AS PRPAGE, pr.PRSEQ, RTRIM(pr.PRID) AS PRID "
  . "FROM $schema.SYPORR pr "
  . "WHERE RTRIM(pr.PRPORT) IN ($pList) $roleFilter "
  . "  AND NOT EXISTS (SELECT 1 FROM $schema.SYPORT p "
  . "                  WHERE RTRIM(p.FPPORT)=RTRIM(pr.PRPORT) "
  . "                    AND RTRIM(p.FPPAGE)=RTRIM(pr.PRPAGE) "
  . "                    AND p.FPSEQ=pr.PRSEQ) "
  . "ORDER BY pr.PRROLE, pr.PRPORT, pr.PRPAGE, pr.PRSEQ");
if ($oStmt !== false) while ($r = db2_fetch_assoc($oStmt)) $orphans[] = $r;

// ── Execute ─────────────────────────────────────────────────────────────────
$done = false; $updated = 0; $updErr = '';
if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX' && !$err && !empty($rows)) {
    $done = true;
    $u = @db2_exec($conn, "UPDATE $schema.SYPORR pr SET PRID = $goodPrid $where");
    if ($u === false) $updErr = db2_stmt_errormsg();
    else $updated = db2_num_rows($u);
}
db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Fix SYPORR PRID</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:Arial,sans-serif; background:#f0f2f5; padding:24px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff; padding:14px 24px;
       border-radius:6px; border-bottom:3px solid #f90; margin-bottom:18px; }
.hdr h2 { font-size:18px; } .hdr .sub { font-size:11px; opacity:.8; margin-top:3px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:5px;
        overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff; padding:6px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; border-bottom:1px solid #f0f2f5; font-family:monospace; }
.old { color:#c62828; } .new { color:#2e7d32; font-weight:bold; }
.info { background:#e7f1fb; border:1px solid #a8c8e8; border-radius:5px; padding:11px 15px;
        font-size:12px; margin-bottom:12px; line-height:1.6; }
.good { background:#e8f5e9; border:1px solid #2e7d32; color:#1b5e20; border-radius:5px;
        padding:11px 15px; font-size:12px; margin-bottom:12px; }
.live { background:#fdecea; border:1px solid #c62828; color:#8b1a17; border-radius:5px;
        padding:11px 15px; font-size:12px; margin-bottom:12px; font-weight:bold; }
.warn { background:#fff3cd; border:1px solid #e0a800; border-radius:5px; padding:11px 15px;
        font-size:12px; margin-bottom:12px; line-height:1.6; }
.rollback { background:#fff3cd; border:1px solid #e0a800; border-radius:5px; padding:11px 15px;
            font-family:monospace; font-size:11px; margin-bottom:12px; line-height:1.6; }
.btn { display:inline-block; margin-top:8px; padding:10px 24px; background:#2a5a8c; color:#fff;
       text-decoration:none; border-radius:4px; font-size:14px; }
.btn.go { background:#2e7d32; }
h3 { font-size:14px; color:#2a5a8c; margin:20px 0 6px; }
</style>
</head>
<body>
<div class="hdr">
  <h2>Fix SYPORR.PRID &mdash; restore Description in Portal By Role Maintenance</h2>
  <div class="sub"><?= h($schema) ?> &nbsp;|&nbsp; <?= $isLive ? 'EIP LIVE' : 'SG5 TEST' ?>
    &nbsp;|&nbsp; port <?= h($port) ?>
    &nbsp;|&nbsp; role filter: <?= $role === '' ? 'all roles' : h($role) ?>
    &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<?php if ($isLive): ?>
<div class="live">Pointed at SGHDSDATA &mdash; LIVE menu tables. Append
  <code>&amp;schema=S5HDSDATA</code> to rehearse on SG5 first.</div>
<?php endif; ?>

<div class="info">
  Sets <code>PRID</code> to the <code>FPID</code> of the SYPORT row it already points at
  (matched on PRPORT/PRPAGE/PRSEQ). <strong>Cosmetic only</strong> &mdash; it changes which
  label the maintenance screen can display, not which menu items a role sees. No
  <code>PRSEL</code> value is touched, so your existing checkbox choices are preserved
  exactly. SG* portals only.
</div>

<?php if ($err): ?>
  <div class="live">Query failed: <?= h($err) ?></div>

<?php elseif ($done): ?>
  <?php if ($updErr): ?>
    <div class="live">UPDATE failed: <?= h($updErr) ?></div>
  <?php else: ?>
    <div class="good"><strong><?= (int)$updated ?> row(s) updated.</strong> Reopen Portal By
      Role Maintenance &mdash; the Description column should now be populated.</div>
  <?php endif; ?>
  <a class="btn" href="?schema=<?= urlencode($schema) ?><?= $role ? '&role='.urlencode($role) : '' ?>">Re-check</a>

<?php elseif (empty($rows)): ?>
  <div class="good">Nothing to fix &mdash; every SG-portal SYPORR row in <?= h($schema) ?>
    already carries the correct FPID as its PRID.</div>

<?php else: ?>
  <h3><?= count($rows) ?> row(s) to change</h3>
  <div class="rollback"><strong>Rollback.</strong> PRID is derived, so the old synthetic values
    can be rebuilt if ever needed:<br>
    UPDATE <?= h($schema) ?>.SYPORR SET PRID =<br>
    &nbsp;&nbsp;RTRIM(PRROLE)||'/'||RTRIM(PRPORT)||<br>
    &nbsp;&nbsp;CASE WHEN RTRIM(PRPAGE)='' THEN '' ELSE '/'||RTRIM(PRPAGE)||'/'||
      LTRIM(CHAR(DECIMAL(PRSEQ,5,2))) END<br>
    &nbsp;&nbsp;WHERE RTRIM(PRPORT) IN (<?= h($pList) ?>)<br>
    <em>&mdash; but take the table backup below instead; it is exact.</em>
  </div>
  <div class="warn">Recommended: run <code>SgBackup.php</code> first, or note that
    <code>SgApplyAll.php</code> writes a full SYPORR backup before its own changes.</div>
  <table>
    <tr><th>Role</th><th>Portal</th><th>Page</th><th>Seq</th><th>Sel</th>
        <th>PRID now</th><th>PRID after</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= h($r['PRROLE']) ?></td>
      <td><?= h($r['PRPORT']) ?></td>
      <td><?= h($r['PRPAGE']) ?></td>
      <td><?= h($r['PRSEQ']) ?></td>
      <td><?= h($r['PRSEL']) ?></td>
      <td class="old"><?= h($r['OLD_PRID']) ?></td>
      <td class="new"><?= h($r['NEW_PRID']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <a class="btn go" href="?confirm=FIX&schema=<?= urlencode($schema) ?><?= $role ? '&role='.urlencode($role) : '' ?>">
    Update <?= count($rows) ?> Row(s) &mdash; <?= h($schema) ?></a>
<?php endif; ?>

<?php if (!empty($orphans)): ?>
  <h3>Orphan SYPORR rows &mdash; <?= count($orphans) ?> (not changed)</h3>
  <div class="warn">These have no matching SYPORT row for their PRPORT/PRPAGE/PRSEQ, so they
    cannot resolve to a page and this script leaves them alone. They render as checkboxes that
    do nothing. Usually left over from a portal whose sub-items were renumbered.</div>
  <table>
    <tr><th>Role</th><th>Portal</th><th>Page</th><th>Seq</th><th>PRID</th></tr>
    <?php foreach ($orphans as $r): ?>
    <tr><td><?= h($r['PRROLE']) ?></td><td><?= h($r['PRPORT']) ?></td>
        <td><?= h($r['PRPAGE']) ?></td><td><?= h($r['PRSEQ']) ?></td>
        <td><?= h($r['PRID']) ?></td></tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

</body>
</html>