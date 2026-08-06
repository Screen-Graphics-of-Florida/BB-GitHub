<?php
// AddSgTrainToRole.php
// Add the SG Training Guides portal (SGTRAIN) to one role's EIP left-nav menu.
//
// SGTRAIN was added ad hoc and is NOT in SgApplyAll.php's $portals list, so
// re-running Apply All never grants it to a role. This script does that job.
//
// The nav is driven by two tables (see SgReportNav.php / GetMenu.php):
//   SYROLD  role -> portal assignment + display sequence   (always required)
//   SYPORR  per-role whitelist, ONLY consulted when the role has >=1 SYPORR row
//
// SAFETY — bypass vs whitelist mode:
//   A role with zero SYPORR rows is in BYPASS mode and sees every SYROLD portal.
//   Inserting SYPORR rows for SGTRAIN alone would flip it into WHITELIST mode and
//   hide every other portal (the TIFFANY/ENAPOLES "portals disappear" failure).
//   So this script inserts SYPORR rows ONLY for roles already in whitelist mode.
//
// Preview roles:  .../Custom/SG/AddSgTrainToRole.php
// Preview a role: .../Custom/SG/AddSgTrainToRole.php?role=CUSTSERV
// Execute:        .../Custom/SG/AddSgTrainToRole.php?role=CUSTSERV&confirm=ADD
// Schema override: append &schema=S5HDSDATA  (default: port 5610 -> S5HDSDATA,
//                                                      otherwise SGHDSDATA)

$PORTAL = 'SGTRAIN';
$BYPASS = 'HD_ALL_SG';   // same bypass role SgApplyAll.php excludes

// ── Schema: test (SG5, port 5610) vs live (EIP, port 5601) ───────────────────
$port   = (string)@$_SERVER['SERVER_PORT'];
$schema = ($port === '5610') ? 'S5HDSDATA' : 'SGHDSDATA';
if (!empty($_GET['schema'])) {
    $s = strtoupper(trim($_GET['schema']));
    if (in_array($s, array('S5HDSDATA', 'SGHDSDATA'), true)) $schema = $s;
}
$isLive = ($schema === 'SGHDSDATA');

$role = '';
if (!empty($_GET['role'])) {
    $r = strtoupper(trim($_GET['role']));
    if (preg_match('/^[A-Z][A-Z0-9_]{0,9}$/', $r)) $role = $r;
}
$roleSafe = str_replace("'", "''", $role);

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB2 connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

function qrows($conn, $sql) {
    $out  = array();
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) return array(array('__error' => db2_stmt_errormsg()));
    while ($row = db2_fetch_assoc($stmt)) $out[] = $row;
    return $out;
}
function qval($conn, $sql) {
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) return null;
    $r = db2_fetch_row($stmt);
    return $r ? db2_result($stmt, 0) : null;
}
function h($v) { return htmlspecialchars(trim((string)$v)); }

// ── SGTRAIN's own SYPORT definition (top level + sub-items) ──────────────────
// FPPAGE='' is the top-level entry; FPPAGE=FPPORT rows are the flyout children.
// Sub-item PRSEQ must match the child's FPSEQ, so read the real values rather
// than assuming 1..6 the way SgApplyAll.php's STEP 7 does.
$portRows = qrows($conn,
    "SELECT RTRIM(FPPORT) AS FPPORT, RTRIM(FPPAGE) AS FPPAGE, FPSEQ, "
  . "       RTRIM(FPDESC) AS FPDESC, RTRIM(FPID) AS FPID "
  . "FROM $schema.SYPORT "
  . "WHERE RTRIM(FPPORT)='$PORTAL' AND (RTRIM(FPPAGE)='' OR RTRIM(FPPAGE)=RTRIM(FPPORT)) "
  . "ORDER BY FPPAGE, FPSEQ");
$portErr  = (isset($portRows[0]['__error'])) ? $portRows[0]['__error'] : '';
$subSeqs  = array();
$topCount = 0;
if (!$portErr) {
    foreach ($portRows as $pr) {
        if ($pr['FPPAGE'] === '') $topCount++;
        else $subSeqs[] = (int)$pr['FPSEQ'];
    }
}

// ── Per-role status helper ──────────────────────────────────────────────────
function roleStatus($conn, $schema, $r, $PORTAL) {
    $rs = str_replace("'", "''", $r);
    return array(
        'syrold'   => (int)qval($conn, "SELECT COUNT(*) FROM $schema.SYROLD "
                              . "WHERE RTRIM(RDROLE)='$rs' AND RTRIM(RDPORT)='$PORTAL'"),
        'porrAny'  => (int)qval($conn, "SELECT COUNT(*) FROM $schema.SYPORR "
                              . "WHERE RTRIM(PRROLE)='$rs'"),
        'porrTop'  => (int)qval($conn, "SELECT COUNT(*) FROM $schema.SYPORR "
                              . "WHERE RTRIM(PRROLE)='$rs' AND RTRIM(PRPORT)='$PORTAL' "
                              . "AND RTRIM(PRPAGE)='' AND RTRIM(PRSEL)='Y'"),
        'porrSub'  => (int)qval($conn, "SELECT COUNT(*) FROM $schema.SYPORR "
                              . "WHERE RTRIM(PRROLE)='$rs' AND RTRIM(PRPORT)='$PORTAL' "
                              . "AND RTRIM(PRPAGE)='$PORTAL' AND RTRIM(PRSEL)='Y'"),
    );
}

// ============================================================================
// EXECUTE
// ============================================================================
$log = array();
$did = false;

if ($role !== '' && isset($_GET['confirm']) && $_GET['confirm'] === 'ADD' && !$portErr) {
    $did = true;
    $st  = roleStatus($conn, $schema, $role, $PORTAL);
    $whitelistMode = ($st['porrAny'] > 0);

    function runSql($conn, $label, $sql, &$log) {
        $stmt = @db2_exec($conn, $sql);
        if ($stmt === false) { $log[] = array('FAIL', $label, db2_stmt_errormsg()); return; }
        $n = db2_num_rows($stmt);
        $log[] = ($n > 0) ? array('OK', $label, 'inserted')
                          : array('SKIP', $label, 'already exists');
    }

    // 1. SYROLD — the assignment itself. Sequence = end of this role's list.
    runSql($conn, "SYROLD $role/$PORTAL",
        "INSERT INTO $schema.SYROLD
             (RDROLE,RDPORT,RDSEQN,RDRESV,RDTSTP,RDTSUS,RDTSWS,RDTSPT)
         SELECT '$roleSafe','$PORTAL',
                COALESCE((SELECT MAX(RDSEQN) FROM $schema.SYROLD
                          WHERE RTRIM(RDROLE)='$roleSafe'),0)+1,
                '',CURRENT_TIMESTAMP,'BBUSCH','BBUSCH',''
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM $schema.SYROLD
             WHERE RTRIM(RDROLE)='$roleSafe' AND RTRIM(RDPORT)='$PORTAL')", $log);

    // 2/3. SYPORR — whitelist rows. Skipped entirely in bypass mode, otherwise
    //      the role would flip to whitelist mode and lose every other portal.
    if ($role === $BYPASS) {
        $log[] = array('SKIP', "SYPORR $role", "$BYPASS is the bypass role - no SYPORR rows by design");
    } elseif (!$whitelistMode) {
        $log[] = array('SKIP', "SYPORR $role",
                       'role is in BYPASS mode (0 SYPORR rows) - SYROLD alone is enough; '
                     . 'adding SYPORR here would hide all its other portals');
    } else {
        $prid = "$role/$PORTAL";
        runSql($conn, "SYPORR top $role/$PORTAL",
            "INSERT INTO $schema.SYPORR
                 (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
             SELECT '$roleSafe','$PORTAL','',1,'$prid','Y',
                    CURRENT_TIMESTAMP,'BBUSCH',''
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM $schema.SYPORR
                 WHERE RTRIM(PRROLE)='$roleSafe' AND RTRIM(PRPORT)='$PORTAL'
                   AND RTRIM(PRPAGE)='')", $log);

        // One row per real SYPORT child; PRSEQ mirrors the child's FPSEQ.
        foreach ($subSeqs as $i) {
            $seqstr = number_format($i, 2);
            $sprid  = "$role/$PORTAL/$PORTAL/$seqstr";
            runSql($conn, "SYPORR sub $role/$PORTAL/$i",
                "INSERT INTO $schema.SYPORR
                     (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
                 SELECT '$roleSafe','$PORTAL','$PORTAL',$i,'$sprid','Y',
                        CURRENT_TIMESTAMP,'BBUSCH',''
                 FROM SYSIBM.SYSDUMMY1
                 WHERE NOT EXISTS (
                     SELECT 1 FROM $schema.SYPORR
                     WHERE RTRIM(PRROLE)='$roleSafe' AND RTRIM(PRPORT)='$PORTAL'
                       AND RTRIM(PRPAGE)='$PORTAL' AND PRSEQ=$i)", $log);
        }

        // Belt & suspenders: any pre-existing SGTRAIN row left at PRSEL='' hides it.
        $u = @db2_exec($conn,
            "UPDATE $schema.SYPORR SET PRSEL='Y'
             WHERE RTRIM(PRROLE)='$roleSafe' AND RTRIM(PRPORT)='$PORTAL'
               AND RTRIM(PRSEL)<>'Y'");
        if ($u === false) $log[] = array('FAIL', 'PRSEL fix', db2_stmt_errormsg());
        else {
            $n = db2_num_rows($u);
            $log[] = ($n > 0) ? array('OK', 'PRSEL fix', "$n row(s) set to Y")
                              : array('SKIP', 'PRSEL fix', 'none needed');
        }
    }
}

// ── Data for display ────────────────────────────────────────────────────────
$roles = array();
$rQ = qrows($conn,
    "SELECT RTRIM(RMROLE) AS R, RTRIM(RMDESC) AS D, RTRIM(RMRESV) AS V "
  . "FROM $schema.SYROLM ORDER BY RMROLE");
foreach ($rQ as $r) {
    if (isset($r['__error'])) continue;
    // Resolve status now — the connection is closed before the HTML renders.
    $r['ST'] = roleStatus($conn, $schema, $r['R'], $PORTAL);
    $roles[] = $r;
}

$curRows = array();
if ($role !== '') {
    $curRows['SYROLD'] = qrows($conn,
        "SELECT RTRIM(RDROLE) AS RDROLE, RTRIM(RDPORT) AS RDPORT, RDSEQN "
      . "FROM $schema.SYROLD WHERE RTRIM(RDROLE)='$roleSafe' ORDER BY RDSEQN");
    $curRows['SYPORR (SGTRAIN only)'] = qrows($conn,
        "SELECT RTRIM(PRROLE) AS PRROLE, RTRIM(PRPORT) AS PRPORT, RTRIM(PRPAGE) AS PRPAGE, "
      . "       PRSEQ, RTRIM(PRID) AS PRID, RTRIM(PRSEL) AS PRSEL "
      . "FROM $schema.SYPORR WHERE RTRIM(PRROLE)='$roleSafe' AND RTRIM(PRPORT)='$PORTAL' "
      . "ORDER BY PRPAGE, PRSEQ");
}
$st = ($role !== '') ? roleStatus($conn, $schema, $role, $PORTAL) : null;

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Add SG Training Guides to a Role</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 24px; }
.hdr { background: linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:14px 24px; border-radius:6px; border-bottom:3px solid #f90; margin-bottom:20px; }
.hdr h2 { font-size:18px; }
.hdr .sub { font-size:11px; opacity:.8; margin-top:3px; }
.section { font-size:14px; color:#2a5a8c; font-weight:bold; margin:22px 0 6px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:6px;
        overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,.06); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff; padding:7px 12px; text-align:left; font-size:12px; }
td { padding:5px 12px; font-size:12px; border-bottom:1px solid #f0f2f5; font-family:monospace; }
tr.ok td:first-child { color:#2e7d32; font-weight:bold; }
tr.skip td:first-child { color:#888; }
tr.fail td { color:#c62828; font-weight:bold; }
.info { background:#e7f1fb; border:1px solid #a8c8e8; border-radius:6px;
        padding:12px 16px; font-size:12px; margin-bottom:14px; line-height:1.55; }
.warn { background:#fff3cd; border:1px solid #e0a800; border-radius:6px;
        padding:12px 16px; font-size:12px; margin-bottom:14px; line-height:1.55; }
.live { background:#fdecea; border:1px solid #c62828; border-radius:6px; color:#8b1a17;
        padding:12px 16px; font-size:12px; margin-bottom:14px; font-weight:bold; }
.rollback { background:#fff3cd; border:1px solid #e0a800; border-radius:6px;
            padding:12px 16px; font-family:monospace; font-size:12px;
            margin-bottom:14px; line-height:1.7; }
.btn { display:inline-block; margin-top:8px; padding:10px 24px; background:#2a5a8c;
       color:#fff; text-decoration:none; border-radius:4px; font-size:14px; }
.btn.go { background:#2e7d32; }
a.rl { font-family:monospace; color:#2a5a8c; text-decoration:none; font-weight:bold; }
a.rl:hover { text-decoration:underline; }
.yes { color:#2e7d32; font-weight:bold; }
.no  { color:#c62828; font-weight:bold; }
.mode { font-size:11px; color:#666; }
</style>
</head>
<body>
<div class="hdr">
  <h2>Add &ldquo;SG Training Guides&rdquo; (<?= $PORTAL ?>) to a Role</h2>
  <div class="sub">Schema <?= h($schema) ?>
    &nbsp;|&nbsp; <?= $isLive ? 'EIP LIVE' : 'SG5 TEST' ?>
    &nbsp;|&nbsp; port <?= h($port) ?>
    &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<?php if ($isLive): ?>
<div class="live">This page is pointed at SGHDSDATA &mdash; the LIVE EIP menu tables.
  Changes take effect for real users at next login. To rehearse on SG5 test instead,
  append <code>&amp;schema=S5HDSDATA</code>.</div>
<?php endif; ?>

<?php if ($portErr): ?>
<div class="live">Could not read <?= h($schema) ?>.SYPORT: <?= h($portErr) ?></div>
<?php elseif ($topCount === 0): ?>
<div class="live">No top-level SYPORT row found for <?= $PORTAL ?> in <?= h($schema) ?>
  (FPPAGE=''). The portal itself does not exist in this schema, so no role can be
  granted it. Create the SYPORT/SYURLM rows first.</div>
<?php else: ?>
<div class="info">
  <strong><?= $PORTAL ?> definition in <?= h($schema) ?>.SYPORT:</strong>
  1 top-level entry + <?= count($subSeqs) ?> flyout sub-item(s)<?php
    if ($subSeqs) echo ' (FPSEQ ' . implode(', ', $subSeqs) . ')'; ?>.
  <?php foreach ($portRows as $pr): ?>
    <br><span style="font-family:monospace"><?= $pr['FPPAGE'] === '' ? 'TOP ' : 'SUB ' ?>
    seq <?= h($pr['FPSEQ']) ?> &mdash; <?= h($pr['FPDESC']) ?> &mdash; <?= h($pr['FPID']) ?></span>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($did): ?>
  <div class="section">Result &mdash; <?= h($role) ?></div>
  <table>
    <tr><th>Status</th><th>Item</th><th>Note</th></tr>
    <?php foreach ($log as $L): ?>
    <tr class="<?= strtolower($L[0]) ?>">
      <td><?= $L[0] ?></td><td><?= h($L[1]) ?></td><td><?= h($L[2]) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <div class="info">Have a user with role <strong><?= h($role) ?></strong> log out of EIP and
    back in &mdash; the nav is built at login. Then confirm &ldquo;SG Training Guides&rdquo;
    appears in the left bar.</div>
  <div class="rollback"><strong>Rollback:</strong><br>
    DELETE FROM <?= h($schema) ?>.SYROLD WHERE RTRIM(RDROLE)='<?= h($role) ?>' AND RTRIM(RDPORT)='<?= $PORTAL ?>'<br>
    DELETE FROM <?= h($schema) ?>.SYPORR WHERE RTRIM(PRROLE)='<?= h($role) ?>' AND RTRIM(PRPORT)='<?= $PORTAL ?>'
  </div>
  <a class="btn" href="?schema=<?= urlencode($schema) ?>">Back to role list</a>

<?php elseif ($role !== ''): ?>
  <?php $whitelistMode = ($st['porrAny'] > 0); ?>
  <div class="section">Current state &mdash; <?= h($role) ?></div>
  <div class="info">
    SYROLD row for <?= $PORTAL ?>:
      <span class="<?= $st['syrold'] ? 'yes' : 'no' ?>"><?= $st['syrold'] ? 'YES' : 'MISSING' ?></span><br>
    Menu mode: <strong><?= $whitelistMode ? 'WHITELIST' : 'BYPASS' ?></strong>
      <span class="mode">(<?= (int)$st['porrAny'] ?> SYPORR row(s) for this role<?=
        $whitelistMode ? ' &mdash; SYPORR rows are required' :
        ' &mdash; sees every SYROLD portal, no SYPORR needed' ?>)</span><br>
    <?php if ($whitelistMode): ?>
    SYPORR top-level (PRSEL='Y'):
      <span class="<?= $st['porrTop'] ? 'yes' : 'no' ?>"><?= $st['porrTop'] ? 'YES' : 'MISSING' ?></span><br>
    SYPORR sub-items (PRSEL='Y'):
      <span class="<?= ($st['porrSub'] >= count($subSeqs) && count($subSeqs)) ? 'yes' : 'no' ?>">
        <?= (int)$st['porrSub'] ?> of <?= count($subSeqs) ?></span>
    <?php endif; ?>
  </div>

  <?php if (!$whitelistMode && $role !== $BYPASS): ?>
  <div class="warn"><strong><?= h($role) ?> is in BYPASS mode.</strong> Only the SYROLD row
    will be inserted. No SYPORR rows &mdash; adding them would switch this role to
    whitelist mode and hide every portal except SG Training Guides.</div>
  <?php endif; ?>

  <div class="section">Backup &mdash; rows that exist right now</div>
  <?php foreach ($curRows as $label => $rows): ?>
    <div style="font-size:12px;font-weight:bold;color:#555;margin:8px 0 4px"><?= h($label) ?></div>
    <table>
    <?php if (!empty($rows) && !isset($rows[0]['__error'])): ?>
      <tr><?php foreach (array_keys($rows[0]) as $c) echo '<th>' . h($c) . '</th>'; ?></tr>
      <?php foreach ($rows as $rw): ?>
      <tr><?php foreach ($rw as $v) echo '<td>' . h($v) . '</td>'; ?></tr>
      <?php endforeach; ?>
    <?php elseif (isset($rows[0]['__error'])): ?>
      <tr><td style="color:#c62828"><?= h($rows[0]['__error']) ?></td></tr>
    <?php else: ?>
      <tr><td><em>no rows</em></td></tr>
    <?php endif; ?>
    </table>
  <?php endforeach; ?>

  <div class="rollback"><strong>Rollback SQL &mdash; save this before proceeding:</strong><br><br>
    DELETE FROM <?= h($schema) ?>.SYROLD WHERE RTRIM(RDROLE)='<?= h($role) ?>' AND RTRIM(RDPORT)='<?= $PORTAL ?>'<br>
    DELETE FROM <?= h($schema) ?>.SYPORR WHERE RTRIM(PRROLE)='<?= h($role) ?>' AND RTRIM(PRPORT)='<?= $PORTAL ?>'
  </div>

  <?php if (!$portErr && $topCount > 0): ?>
  <a class="btn go" href="?role=<?= urlencode($role) ?>&schema=<?= urlencode($schema) ?>&confirm=ADD">
    Add <?= $PORTAL ?> to <?= h($role) ?> &mdash; <?= h($schema) ?></a>
  <?php endif; ?>
  <a class="btn" href="?schema=<?= urlencode($schema) ?>">Cancel</a>

<?php else: ?>
  <div class="info">Pick the customer service role below. Nothing is changed until you
    confirm on the next screen. All inserts use WHERE NOT EXISTS, so re-running is safe.</div>
  <div class="section">Roles in <?= h($schema) ?>.SYROLM (<?= count($roles) ?>)</div>
  <table>
    <tr><th>Role</th><th>Description</th><th>Reserved</th><th>Has <?= $PORTAL ?>?</th>
        <th>Menu mode</th><th></th></tr>
    <?php foreach ($roles as $rr): $rs = $rr['ST']; ?>
    <tr>
      <td><?= h($rr['R']) ?></td>
      <td style="font-family:Arial"><?= h($rr['D']) ?></td>
      <td><?= (strtoupper(trim($rr['V'])) === 'Y') ? 'Y' : '' ?></td>
      <td class="<?= $rs['syrold'] ? 'yes' : 'no' ?>"><?= $rs['syrold'] ? 'YES' : 'no' ?></td>
      <td><?= $rs['porrAny'] > 0 ? 'whitelist' : 'bypass' ?></td>
      <td><a class="rl" href="?role=<?= urlencode($rr['R']) ?>&schema=<?= urlencode($schema) ?>">select &rsaquo;</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

</body>
</html>