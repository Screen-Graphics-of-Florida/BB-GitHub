<?php
// SgPortalRole.php
// Add / remove / restrict SG portal access for one role, as checkboxes.
//
// Replaces the per-portal ad hoc scripts (AddSgTrainToRole.php and friends). Those
// each invented their own PRID format; this page always reads PRID from SYPORT.FPID,
// which is the only correct source. See the 2026-08-24 cleanup for what the synthetic
// "$role/$portal/..." form cost us: blank Description columns, top-level menu entries
// that would not render, and 1,242 rows to repair per environment.
//
// RULES ENFORCED HERE (all learned the hard way):
//   1. PRID = the matching SYPORT row's FPID. Never constructed.
//   2. Top-level row (PRPAGE='') must carry PRSEL='Y' when the portal is granted,
//      or the parent entry hides while its children still render.
//   3. A role with zero SYPORR rows is in BYPASS mode - it sees every SYROLD portal.
//      Writing SYPORR rows for it flips it to whitelist mode and hides everything
//      else. This page refuses to do that.
//   4. Reserved roles (SYROLM.RMRESV='Y') are never touched.
//   5. Every write is copied to SGOBJ.SGPRAUDIT first, with rollback SQL shown.
//
// Preview:  .../Custom/SG/SgPortalRole.php
// A role:   .../Custom/SG/SgPortalRole.php?role=MCRESPO
// Schema override: &schema=S5HDSDATA   (default: port 5610 -> test, else live)

// ── SG portals this page manages ────────────────────────────────────────────
$SG_PORTALS = array('SGDASH', 'SGDINT', 'SGINQ', 'SGRPT', 'SGSOP', 'SGTRAIN');
$BYPASS     = 'HD_ALL_SG';
$AUDIT      = 'SGOBJ.SGPRAUDIT';

// ── Schema: test (SG5, port 5610) vs live (EIP, port 5601) ──────────────────
$port   = (string)@$_SERVER['SERVER_PORT'];
$schema = ($port === '5610') ? 'S5HDSDATA' : 'SGHDSDATA';
if (!empty($_GET['schema'])) {
    $s = strtoupper(trim($_GET['schema']));
    if (in_array($s, array('S5HDSDATA', 'SGHDSDATA'), true)) $schema = $s;
}
$isLive = ($schema === 'SGHDSDATA');

$role = '';
$src  = isset($_POST['role']) ? $_POST['role'] : (isset($_GET['role']) ? $_GET['role'] : '');
if ($src !== '') {
    $r = strtoupper(trim($src));
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

$portalList = "'" . implode("','", $SG_PORTALS) . "'";

// ── SYPORT definition for every SG portal: the source of truth for PRID ─────
$defs = array();   // [portal]['top'] = row ; [portal]['kids'][seq] = row
$defRows = qrows($conn,
    "SELECT RTRIM(FPPORT) AS FPPORT, RTRIM(FPPAGE) AS FPPAGE, FPSEQ, "
  . "       RTRIM(FPID) AS FPID, RTRIM(FPDESC) AS FPDESC "
  . "FROM $schema.SYPORT "
  . "WHERE RTRIM(FPPORT) IN ($portalList) "
  . "  AND (RTRIM(FPPAGE)='' OR RTRIM(FPPAGE)=RTRIM(FPPORT)) "
  . "ORDER BY FPPORT, FPPAGE, FPSEQ");
$defErr = (isset($defRows[0]['__error'])) ? $defRows[0]['__error'] : '';
if (!$defErr) {
    foreach ($defRows as $d) {
        $p = $d['FPPORT'];
        if (!isset($defs[$p])) $defs[$p] = array('top' => null, 'kids' => array());
        if ($d['FPPAGE'] === '') $defs[$p]['top'] = $d;
        else $defs[$p]['kids'][(string)(int)$d['FPSEQ']] = $d;
    }
}

// ── Role state ──────────────────────────────────────────────────────────────
function roleMode($conn, $schema, $roleSafe) {
    return (int)qval($conn,
        "SELECT COUNT(*) FROM $schema.SYPORR WHERE RTRIM(PRROLE)='$roleSafe'");
}
function currentSel($conn, $schema, $roleSafe, $portalList) {
    $out = array();
    foreach (qrows($conn,
        "SELECT RTRIM(PRPORT) AS PRPORT, RTRIM(PRPAGE) AS PRPAGE, PRSEQ, "
      . "       RTRIM(PRSEL) AS PRSEL, RTRIM(PRID) AS PRID "
      . "FROM $schema.SYPORR "
      . "WHERE RTRIM(PRROLE)='$roleSafe' AND RTRIM(PRPORT) IN ($portalList)") as $r) {
        if (isset($r['__error'])) continue;
        $k = $r['PRPORT'] . '|' . ($r['PRPAGE'] === '' ? 'TOP' : (string)(int)$r['PRSEQ']);
        $out[$k] = $r;
    }
    return $out;
}
function grantedPortals($conn, $schema, $roleSafe, $portalList) {
    $out = array();
    foreach (qrows($conn,
        "SELECT RTRIM(RDPORT) AS RDPORT FROM $schema.SYROLD "
      . "WHERE RTRIM(RDROLE)='$roleSafe' AND RTRIM(RDPORT) IN ($portalList)") as $r) {
        if (!isset($r['__error'])) $out[$r['RDPORT']] = true;
    }
    return $out;
}

// ── Audit: capture the role's SG rows before any change ─────────────────────
function auditSnapshot($conn, $schema, $roleSafe, $portalList, $AUDIT, &$log) {
    // Create on first use, borrowing SYPORR's own column types.
    @db2_exec($conn,
        "CREATE TABLE $AUDIT AS (SELECT CURRENT_TIMESTAMP AS AUSTMP, "
      . "CAST('' AS CHAR(10)) AS AUUSER, R.* FROM $schema.SYPORR R WHERE 1=0) WITH DATA");
    $ok = @db2_exec($conn,
        "INSERT INTO $AUDIT SELECT CURRENT_TIMESTAMP, 'BBUSCH', R.* "
      . "FROM $schema.SYPORR R WHERE RTRIM(R.PRROLE)='$roleSafe' "
      . "  AND RTRIM(R.PRPORT) IN ($portalList)");
    if ($ok === false) { $log[] = array('FAIL', 'audit snapshot', db2_stmt_errormsg()); return false; }
    $log[] = array('OK', 'audit snapshot', db2_num_rows($ok) . ' row(s) copied to ' . $AUDIT);
    return true;
}

// ============================================================================
// APPLY
// ============================================================================
$log = array();
$did = false;

if ($role !== '' && isset($_POST['apply']) && !$defErr) {
    $mode = roleMode($conn, $schema, $roleSafe);
    $isReserved = (strtoupper(trim((string)qval($conn,
        "SELECT RMRESV FROM $schema.SYROLM WHERE RTRIM(RMROLE)='$roleSafe'"))) === 'Y');

    if ($isReserved) {
        $log[] = array('FAIL', $role, 'reserved role (SYROLM.RMRESV=Y) - never modified by this page');
        $did = true;
    } elseif ($role === $BYPASS || $mode === 0) {
        $log[] = array('FAIL', $role,
            'role is in BYPASS mode (0 SYPORR rows). Writing whitelist rows here would '
          . 'hide every other portal it currently sees. Grant via SYROLD only.');
        $did = true;
    } else {
        $did = true;
        auditSnapshot($conn, $schema, $roleSafe, $portalList, $AUDIT, $log);
        $want = isset($_POST['sel']) && is_array($_POST['sel']) ? $_POST['sel'] : array();
        $cur  = currentSel($conn, $schema, $roleSafe, $portalList);
        $gr   = grantedPortals($conn, $schema, $roleSafe, $portalList);

        foreach ($SG_PORTALS as $p) {
            if (!isset($defs[$p]) || !$defs[$p]['top']) continue;
            $pSafe   = str_replace("'", "''", $p);
            $kids    = $defs[$p]['kids'];
            $wantP   = isset($want[$p]) && is_array($want[$p]) ? $want[$p] : array();
            $anyKid  = false;
            foreach ($kids as $seq => $d) if (!empty($wantP[$seq])) $anyKid = true;

            // -- SYROLD grant follows "any child ticked" -------------------
            if ($anyKid && !isset($gr[$p])) {
                $ok = @db2_exec($conn,
                    "INSERT INTO $schema.SYROLD
                         (RDROLE,RDPORT,RDSEQN,RDRESV,RDTSTP,RDTSUS,RDTSWS,RDTSPT)
                     SELECT '$roleSafe','$pSafe',
                            COALESCE((SELECT MAX(RDSEQN) FROM $schema.SYROLD
                                       WHERE RTRIM(RDROLE)='$roleSafe'),0)+1,
                            '',CURRENT_TIMESTAMP,'BBUSCH','BBUSCH',''
                     FROM SYSIBM.SYSDUMMY1");
                $log[] = ($ok === false)
                    ? array('FAIL', "SYROLD $p", db2_stmt_errormsg())
                    : array('OK', "SYROLD $p", 'granted');
            } elseif (!$anyKid && isset($gr[$p])) {
                $ok = @db2_exec($conn,
                    "DELETE FROM $schema.SYROLD
                      WHERE RTRIM(RDROLE)='$roleSafe' AND RTRIM(RDPORT)='$pSafe'");
                $log[] = ($ok === false)
                    ? array('FAIL', "SYROLD $p", db2_stmt_errormsg())
                    : array('OK', "SYROLD $p", 'revoked');
            }

            // -- Top-level row: present and PRSEL='Y' whenever any child is on
            $topFpid = str_replace("'", "''", $defs[$p]['top']['FPID']);
            $topSeq  = (int)$defs[$p]['top']['FPSEQ'];
            $haveTop = isset($cur[$p . '|TOP']);
            if ($anyKid) {
                $sql = $haveTop
                    ? "UPDATE $schema.SYPORR SET PRSEL='Y', PRID='$topFpid'
                        WHERE RTRIM(PRROLE)='$roleSafe' AND RTRIM(PRPORT)='$pSafe'
                          AND RTRIM(PRPAGE)=''"
                    : "INSERT INTO $schema.SYPORR
                           (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
                       VALUES ('$roleSafe','$pSafe','',$topSeq,'$topFpid','Y',
                               CURRENT_TIMESTAMP,'BBUSCH','')";
                $ok = @db2_exec($conn, $sql);
                $log[] = ($ok === false)
                    ? array('FAIL', "$p top", db2_stmt_errormsg())
                    : array('OK', "$p top", ($haveTop ? 'set PRSEL=Y' : 'inserted') . " ($topFpid)");
            } elseif ($haveTop) {
                $ok = @db2_exec($conn,
                    "DELETE FROM $schema.SYPORR
                      WHERE RTRIM(PRROLE)='$roleSafe' AND RTRIM(PRPORT)='$pSafe'
                        AND RTRIM(PRPAGE)=''");
                $log[] = ($ok === false)
                    ? array('FAIL', "$p top", db2_stmt_errormsg())
                    : array('OK', "$p top", 'removed');
            }

            // -- Children --------------------------------------------------
            foreach ($kids as $seq => $d) {
                $on      = !empty($wantP[$seq]);
                $have    = isset($cur[$p . '|' . $seq]);
                $wasOn   = $have && (strtoupper($cur[$p . '|' . $seq]['PRSEL']) === 'Y');
                $fpid    = str_replace("'", "''", $d['FPID']);
                $seqInt  = (int)$seq;
                if ($on && !$have) {
                    $ok = @db2_exec($conn,
                        "INSERT INTO $schema.SYPORR
                             (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
                         VALUES ('$roleSafe','$pSafe','$pSafe',$seqInt,'$fpid','Y',
                                 CURRENT_TIMESTAMP,'BBUSCH','')");
                    $log[] = ($ok === false)
                        ? array('FAIL', "$p/$seq", db2_stmt_errormsg())
                        : array('OK', "$p/$seq", "granted ($fpid)");
                } elseif ($have && $on !== $wasOn) {
                    $v  = $on ? 'Y' : '';
                    $ok = @db2_exec($conn,
                        "UPDATE $schema.SYPORR SET PRSEL='$v', PRID='$fpid'
                          WHERE RTRIM(PRROLE)='$roleSafe' AND RTRIM(PRPORT)='$pSafe'
                            AND RTRIM(PRPAGE)='$pSafe' AND PRSEQ=$seqInt");
                    $log[] = ($ok === false)
                        ? array('FAIL', "$p/$seq", db2_stmt_errormsg())
                        : array('OK', "$p/$seq", $on ? 'granted' : 'restricted');
                }
            }
        }
        if (count($log) === 1) $log[] = array('SKIP', $role, 'no changes - selections already match');
    }
}

// ── Data for display ────────────────────────────────────────────────────────
$roles = array();
foreach (qrows($conn,
    "SELECT RTRIM(RMROLE) AS R, RTRIM(RMDESC) AS D, RTRIM(RMRESV) AS V "
  . "FROM $schema.SYROLM ORDER BY RMROLE") as $r) {
    if (isset($r['__error'])) continue;
    $rs = str_replace("'", "''", $r['R']);
    $r['MODE'] = roleMode($conn, $schema, $rs) > 0 ? 'whitelist' : 'bypass';
    $r['SG']   = (int)qval($conn,
        "SELECT COUNT(*) FROM $schema.SYPORR WHERE RTRIM(PRROLE)='$rs' "
      . "AND RTRIM(PRPORT) IN ($portalList) AND RTRIM(PRSEL)='Y'");
    $roles[] = $r;
}

$cur = $gr = array();
$mode = 0; $isReserved = false;
if ($role !== '') {
    $cur  = currentSel($conn, $schema, $roleSafe, $portalList);
    $gr   = grantedPortals($conn, $schema, $roleSafe, $portalList);
    $mode = roleMode($conn, $schema, $roleSafe);
    $isReserved = (strtoupper(trim((string)qval($conn,
        "SELECT RMRESV FROM $schema.SYROLM WHERE RTRIM(RMROLE)='$roleSafe'"))) === 'Y');
}
db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG Portal Access by Role</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 24px; }
.hdr { background: linear-gradient(135deg,#111827,#6B7280); color:#fff;
       padding:14px 24px; border-radius:6px; margin-bottom:18px; }
.hdr h1 { font-size:20px; }
.hdr .sub { font-size:11px; opacity:.85; margin-top:4px; }
.section { font-size:14px; color:#111827; font-weight:bold; margin:20px 0 6px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:6px;
        overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,.06); margin-bottom:14px; }
th { background:#374151; color:#fff; padding:7px 12px; text-align:left; font-size:12px; }
td { padding:5px 12px; font-size:12px; border-bottom:1px solid #f0f2f5; }
tr:hover td { background:#EFF6FF; }
td.mono, th.mono { font-family:monospace; }
tr.ok td:first-child { color:#1DA032; font-weight:bold; }
tr.skip td:first-child { color:#888; }
tr.fail td { color:#CC1F20; font-weight:bold; }
.info { background:#EFF6FF; border:1px solid #2563EB; border-radius:6px;
        padding:12px 16px; font-size:12px; margin-bottom:14px; line-height:1.55; }
.warn { background:#fff3cd; border:1px solid #e0a800; border-radius:6px;
        padding:12px 16px; font-size:12px; margin-bottom:14px; line-height:1.55; }
.live { background:#fdecea; border:1px solid #CC1F20; border-radius:6px; color:#8b1a17;
        padding:12px 16px; font-size:12px; margin-bottom:14px; font-weight:bold; }
.rollback { background:#fff3cd; border:1px solid #e0a800; border-radius:6px;
            padding:12px 16px; font-family:monospace; font-size:12px;
            margin-bottom:14px; line-height:1.7; }
.btn { display:inline-block; margin-top:8px; padding:10px 24px; background:#06B6D4;
       color:#fff; text-decoration:none; border-radius:4px; font-size:14px; border:0;
       cursor:pointer; }
.btn.go { background:#1DA032; }
a.rl { font-family:monospace; color:#2563EB; text-decoration:none; font-weight:bold; }
a.rl:hover { text-decoration:underline; }
.pgroup td { background:#f9fafb; font-weight:bold; }
</style>
</head>
<body>
<div class="hdr">
  <h1>SG Portal Access by Role</h1>
  <div class="sub">Schema <?= h($schema) ?>
    &nbsp;|&nbsp; <?= $isLive ? 'EIP LIVE' : 'SG5 TEST' ?>
    &nbsp;|&nbsp; port <?= h($port) ?></div>
</div>

<?php if ($isLive): ?>
<div class="live">Pointed at SGHDSDATA - the LIVE menu tables. Changes take effect for
  real users at their next login. To rehearse on Test, append
  <code>&amp;schema=S5HDSDATA</code>.</div>
<?php endif; ?>

<?php if ($defErr): ?>
<div class="live">Could not read <?= h($schema) ?>.SYPORT: <?= h($defErr) ?></div>
<?php endif; ?>

<?php if ($did): ?>
  <div class="section">Result &mdash; <?= h($role) ?></div>
  <table>
    <tr><th>Status</th><th>Item</th><th>Note</th></tr>
    <?php foreach ($log as $L): ?>
    <tr class="<?= strtolower($L[0]) ?>">
      <td><?= $L[0] ?></td><td class="mono"><?= h($L[1]) ?></td><td><?= h($L[2]) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <div class="rollback"><strong>Rollback &mdash; restores this role's SG rows as they were:</strong><br><br>
    DELETE FROM <?= h($schema) ?>.SYPORR WHERE RTRIM(PRROLE)='<?= h($role) ?>'
      AND RTRIM(PRPORT) IN (<?= h($portalList) ?>)<br>
    INSERT INTO <?= h($schema) ?>.SYPORR SELECT PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT
      FROM <?= $AUDIT ?> WHERE RTRIM(PRROLE)='<?= h($role) ?>'
      AND AUSTMP=(SELECT MAX(AUSTMP) FROM <?= $AUDIT ?> WHERE RTRIM(PRROLE)='<?= h($role) ?>')
  </div>
  <div class="info">Have a user with role <strong><?= h($role) ?></strong> sign out and back
    in - the nav is built at login.</div>
  <a class="btn" href="?schema=<?= urlencode($schema) ?>">Back to role list</a>
  <a class="btn" href="?role=<?= urlencode($role) ?>&schema=<?= urlencode($schema) ?>">Re-open <?= h($role) ?></a>

<?php elseif ($role !== ''): ?>
  <div class="section">SG portal access &mdash; <?= h($role) ?></div>
  <?php if ($isReserved): ?>
  <div class="live"><?= h($role) ?> is a reserved role (SYROLM.RMRESV='Y'). This page will
    not modify it.</div>
  <?php elseif ($mode === 0 || $role === $BYPASS): ?>
  <div class="warn"><strong><?= h($role) ?> is in BYPASS mode</strong> (0 SYPORR rows).
    It already sees every portal granted in SYROLD, and adding whitelist rows here would
    hide all of them except what you tick. Grant or revoke this role through SYROLD
    instead. Nothing on this page will write to it.</div>
  <?php else: ?>
  <div class="info">Tick a sub-item to grant it, untick to restrict. The top-level menu
    entry and the SYROLD grant are managed automatically: a portal appears when at least
    one of its sub-items is ticked and disappears when none are. PRID always comes from
    SYPORT, so the Description column stays populated.</div>
  <?php endif; ?>

  <form method="post" action="?schema=<?= urlencode($schema) ?>">
  <input type="hidden" name="role" value="<?= h($role) ?>">
  <table>
    <tr><th style="width:70px">Grant</th><th>Portal</th><th>Sub-item</th>
        <th class="mono">PRID (from SYPORT)</th><th style="width:90px">Now</th></tr>
    <?php foreach ($SG_PORTALS as $p):
        if (!isset($defs[$p])) continue;
        $topOn = isset($cur[$p . '|TOP']) && strtoupper($cur[$p . '|TOP']['PRSEL']) === 'Y'; ?>
      <tr class="pgroup">
        <td><?= isset($gr[$p]) ? 'SYROLD' : '&mdash;' ?></td>
        <td colspan="2"><?= h($p) ?><?= $defs[$p]['top'] ? '' : ' (no top-level SYPORT row)' ?></td>
        <td class="mono"><?= $defs[$p]['top'] ? h($defs[$p]['top']['FPID']) : '' ?></td>
        <td><?= $topOn ? 'parent Y' : 'parent off' ?></td>
      </tr>
      <?php foreach ($defs[$p]['kids'] as $seq => $d):
          $k  = $p . '|' . $seq;
          $on = isset($cur[$k]) && strtoupper($cur[$k]['PRSEL']) === 'Y'; ?>
      <tr>
        <td style="text-align:center">
          <input type="checkbox" name="sel[<?= h($p) ?>][<?= h($seq) ?>]" value="1"
                 <?= $on ? 'checked' : '' ?>
                 <?= ($isReserved || $mode === 0) ? 'disabled' : '' ?>></td>
        <td></td>
        <td><?= h($d['FPDESC']) ?></td>
        <td class="mono"><?= h($d['FPID']) ?></td>
        <td><?= isset($cur[$k]) ? ($on ? 'granted' : 'restricted') : 'no row' ?></td>
      </tr>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </table>
  <?php if (!$isReserved && $mode > 0 && $role !== $BYPASS && !$defErr): ?>
  <button class="btn go" type="submit" name="apply" value="1">Apply to <?= h($role) ?> &mdash; <?= h($schema) ?></button>
  <?php endif; ?>
  <a class="btn" href="?schema=<?= urlencode($schema) ?>">Cancel</a>
  </form>

<?php else: ?>
  <div class="info">Pick a role. Nothing changes until you tick boxes and press Apply.
    Every apply copies the role's existing SG rows to <?= $AUDIT ?> first and prints
    rollback SQL.</div>
  <div class="section">Roles in <?= h($schema) ?>.SYROLM (<?= count($roles) ?>)</div>
  <table>
    <tr><th>Role</th><th>Description</th><th>Reserved</th><th>Menu mode</th>
        <th>SG sub-items granted</th><th></th></tr>
    <?php foreach ($roles as $rr): ?>
    <tr>
      <td class="mono"><?= h($rr['R']) ?></td>
      <td><?= h($rr['D']) ?></td>
      <td><?= (strtoupper(trim($rr['V'])) === 'Y') ? 'Y' : '' ?></td>
      <td><?= h($rr['MODE']) ?></td>
      <td><?= (int)$rr['SG'] ?></td>
      <td><a class="rl" href="?role=<?= urlencode($rr['R']) ?>&schema=<?= urlencode($schema) ?>">open &rsaquo;</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

</body>
</html>
