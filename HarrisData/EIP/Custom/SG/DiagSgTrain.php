<?php
// DiagSgTrain.php  — READ-ONLY, no DB writes
// Full footprint of the SG Training Guides portal (SGTRAIN) in BOTH schemas,
// with SGINQ shown alongside as the known-good reference built by SgApplyAll.php.
//
// Answers three questions:
//   1. Does SGTRAIN exist at all in S5HDSDATA (SG5 test) vs SGHDSDATA (EIP live)?
//   2. Do its SYURLM/SYPORT rows use SgApplyAll's naming ('SGTRAIN/PORTAL',
//      'SGTRAIN_OE', ...) or ad-hoc IDs? If ad-hoc, adding SGTRAIN to
//      SgApplyAll's $portals would INSERT DUPLICATES, because the STEP 1 and
//      STEP 4 guards key on FUID/FPID, not on portal+sequence.
//   3. Does CUSTSRVC have the 6-row SYPORR grid that Portal By Role
//      Maintenance needs in order to show checkboxes?
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/DiagSgTrain.php
//      https://portal.screen-graphics.com:5601/Custom/SG/DiagSgTrain.php
// Optional: ?role=CUSTSRVC  (default CUSTSRVC)

$PORTAL = 'SGTRAIN';
$REF    = 'SGINQ';      // reference portal known to be correct
$CATS   = array('ACCT' => 1, 'INVMGMT' => 2, 'MFG' => 3, 'OE' => 4, 'PLN' => 5, 'PUR' => 6);

$role = 'CUSTSRVC';
if (!empty($_GET['role'])) {
    $r = strtoupper(trim($_GET['role']));
    if (preg_match('/^[A-Z][A-Z0-9_]{0,9}$/', $r)) $role = $r;
}
$roleSafe = str_replace("'", "''", $role);

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB2 connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

function qrows($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if ($s === false) return array('__error' => db2_stmt_errormsg());
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    return $out;
}
function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if ($s === false) return null;
    $r = db2_fetch_row($s);
    return $r ? db2_result($s, 0) : null;
}
function h($v) { return htmlspecialchars(trim((string)$v)); }

$schemas = array('S5HDSDATA' => 'SG5 TEST', 'SGHDSDATA' => 'EIP LIVE');

// ── Collect everything up front (connection closes before render) ───────────
$D = array();
foreach ($schemas as $sc => $envLabel) {
    $D[$sc] = array(
        'label'  => $envLabel,
        'urlm'   => qrows($conn,
            "SELECT RTRIM(FUID) AS FUID, RTRIM(FUDESC) AS FUDESC, RTRIM(FUTITL) AS FUTITL, "
          . "       RTRIM(FUTRGT) AS FUTRGT, RTRIM(FURESV) AS FURESV, LEFT(RTRIM(FUURL),90) AS FUURL "
          . "FROM $sc.SYURLM "
          . "WHERE RTRIM(FUID) LIKE '$PORTAL%' ORDER BY FUID"),
        'port'   => qrows($conn,
            "SELECT RTRIM(FPPORT) AS FPPORT, RTRIM(FPPAGE) AS FPPAGE, FPSEQ, "
          . "       RTRIM(FPID) AS FPID, RTRIM(FPDESC) AS FPDESC, RTRIM(FPTITL) AS FPTITL "
          . "FROM $sc.SYPORT WHERE RTRIM(FPPORT)='$PORTAL' ORDER BY FPPAGE, FPSEQ"),
        'refport' => qrows($conn,
            "SELECT RTRIM(FPPORT) AS FPPORT, RTRIM(FPPAGE) AS FPPAGE, FPSEQ, "
          . "       RTRIM(FPID) AS FPID, RTRIM(FPDESC) AS FPDESC "
          . "FROM $sc.SYPORT WHERE RTRIM(FPPORT)='$REF' ORDER BY FPPAGE, FPSEQ"),
        'rold'   => qrows($conn,
            "SELECT RTRIM(RDROLE) AS RDROLE, RDSEQN FROM $sc.SYROLD "
          . "WHERE RTRIM(RDPORT)='$PORTAL' ORDER BY RDROLE"),
        'roleRold' => qrows($conn,
            "SELECT RTRIM(RDPORT) AS RDPORT, RDSEQN FROM $sc.SYROLD "
          . "WHERE RTRIM(RDROLE)='$roleSafe' ORDER BY RDSEQN"),
        'rolePorr' => qrows($conn,
            "SELECT RTRIM(PRPORT) AS PRPORT, RTRIM(PRPAGE) AS PRPAGE, PRSEQ, "
          . "       RTRIM(PRID) AS PRID, RTRIM(PRSEL) AS PRSEL "
          . "FROM $sc.SYPORR "
          . "WHERE RTRIM(PRROLE)='$roleSafe' AND RTRIM(PRPORT) IN ('$PORTAL','$REF') "
          . "ORDER BY PRPORT, PRPAGE, PRSEQ"),
        'porrCnt' => (int)qval($conn,
            "SELECT COUNT(*) FROM $sc.SYPORR WHERE RTRIM(PRROLE)='$roleSafe'"),
        'roleExists' => (int)qval($conn,
            "SELECT COUNT(*) FROM $sc.SYROLM WHERE RTRIM(RMROLE)='$roleSafe'"),
    );

    // What SgApplyAll's guards would find if SGTRAIN were added to $portals
    $expect = array();
    $expect["FUID $PORTAL/PORTAL"] = (int)qval($conn,
        "SELECT COUNT(*) FROM $sc.SYURLM WHERE RTRIM(FUID)='$PORTAL/PORTAL'");
    foreach ($CATS as $cc => $seq) {
        $expect["FUID {$PORTAL}_$cc"] = (int)qval($conn,
            "SELECT COUNT(*) FROM $sc.SYURLM WHERE RTRIM(FUID)='{$PORTAL}_$cc'");
    }
    $expect["SYPORT top (FPPAGE='')"] = (int)qval($conn,
        "SELECT COUNT(*) FROM $sc.SYPORT WHERE RTRIM(FPPORT)='$PORTAL' AND RTRIM(FPPAGE)=''");
    foreach ($CATS as $cc => $seq) {
        $expect["FPID {$PORTAL}_$cc"] = (int)qval($conn,
            "SELECT COUNT(*) FROM $sc.SYPORT WHERE RTRIM(FPPORT)='$PORTAL' AND RTRIM(FPID)='{$PORTAL}_$cc'");
    }
    $D[$sc]['expect'] = $expect;

    // Sub-item rows already present that are NOT one of the 6 canonical FPIDs —
    // these are the ad-hoc rows that would collide on FPSEQ after an Apply All.
    $canon = array();
    foreach ($CATS as $cc => $seq) $canon[] = "'{$PORTAL}_$cc'";
    $D[$sc]['adhoc'] = qrows($conn,
        "SELECT RTRIM(FPPAGE) AS FPPAGE, FPSEQ, RTRIM(FPID) AS FPID, RTRIM(FPDESC) AS FPDESC "
      . "FROM $sc.SYPORT WHERE RTRIM(FPPORT)='$PORTAL' AND RTRIM(FPPAGE)<>'' "
      . "AND RTRIM(FPID) NOT IN (" . implode(',', $canon) . ") ORDER BY FPSEQ");
}

db2_close($conn);

function tbl($rows, $empty = 'no rows') {
    if (isset($rows['__error'])) {
        echo '<table><tr><td style="color:#c62828">ERR: ' . h($rows['__error']) . '</td></tr></table>';
        return;
    }
    echo '<table>';
    if (empty($rows)) {
        echo '<tr><td><em>' . h($empty) . '</em></td></tr></table>';
        return;
    }
    echo '<tr>';
    foreach (array_keys($rows[0]) as $c) echo '<th>' . h($c) . '</th>';
    echo '</tr>';
    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($r as $v) echo '<td>' . h($v) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Diag — SGTRAIN footprint</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:Arial,sans-serif; background:#f0f2f5; padding:24px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:14px 24px; border-radius:6px; border-bottom:3px solid #f90; margin-bottom:18px; }
.hdr h2 { font-size:18px; }
.hdr .sub { font-size:11px; opacity:.8; margin-top:3px; }
h3 { font-size:15px; color:#2a5a8c; margin:22px 0 8px; }
h4 { font-size:12px; color:#555; margin:12px 0 4px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:5px;
        overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); margin-bottom:10px; }
th { background:#2a5a8c; color:#fff; padding:6px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; border-bottom:1px solid #f0f2f5; font-family:monospace; }
.env { display:inline-block; padding:3px 10px; border-radius:3px; font-size:11px;
       font-weight:bold; color:#fff; margin-left:8px; }
.env.t { background:#2e7d32; } .env.l { background:#c62828; }
.info { background:#e7f1fb; border:1px solid #a8c8e8; border-radius:5px;
        padding:11px 15px; font-size:12px; margin-bottom:12px; line-height:1.6; }
.bad  { background:#fdecea; border:1px solid #c62828; color:#8b1a17; border-radius:5px;
        padding:11px 15px; font-size:12px; margin-bottom:12px; line-height:1.6; font-weight:bold; }
.good { background:#e8f5e9; border:1px solid #2e7d32; color:#1b5e20; border-radius:5px;
        padding:11px 15px; font-size:12px; margin-bottom:12px; line-height:1.6; }
.yes { color:#2e7d32; font-weight:bold; } .no { color:#c62828; font-weight:bold; }
.col2 { display:flex; gap:18px; } .col2 > div { flex:1; min-width:0; }
</style>
</head>
<body>
<div class="hdr">
  <h2>SGTRAIN (SG Training Guides) — full DB footprint</h2>
  <div class="sub">READ-ONLY &nbsp;|&nbsp; role under test: <?= h($role) ?>
    &nbsp;|&nbsp; reference portal: <?= $REF ?>
    &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<div class="info">
  <strong>What to look for.</strong> Portal By Role Maintenance draws its checkbox grid from
  <code>SYPORR</code>. For <?= h($role) ?> to have 6 checkboxes under <?= $PORTAL ?> the way it
  does under <?= $REF ?>, all of these must exist in the schema that environment reads:
  <code>SYURLM</code> (7 rows: portal + 6 cats) &rarr; <code>SYPORT</code> (7 rows) &rarr;
  <code>SYROLD</code> (1 row per role) &rarr; <code>SYPORR</code> (1 top + 6 sub per role).
</div>

<?php foreach ($D as $sc => $d): ?>
<h3><?= h($sc) ?><span class="env <?= $sc === 'S5HDSDATA' ? 't' : 'l' ?>"><?= h($d['label']) ?></span></h3>

  <?php
  $portRows = isset($d['port']['__error']) ? array() : $d['port'];
  $topOk    = $d['expect']["SYPORT top (FPPAGE='')"];
  $subCanon = 0;
  foreach ($CATS as $cc => $sq) $subCanon += $d['expect']["FPID {$PORTAL}_$cc"];
  $urlmCanon = $d['expect']["FUID $PORTAL/PORTAL"];
  foreach ($CATS as $cc => $sq) $urlmCanon += $d['expect']["FUID {$PORTAL}_$cc"];
  $adhoc = isset($d['adhoc']['__error']) ? array() : $d['adhoc'];
  ?>

  <?php if (empty($portRows)): ?>
    <div class="bad">SGTRAIN does not exist in <?= h($sc) ?>.SYPORT at all — no role in this
      environment can be granted it, and it will never appear in Portal By Role Maintenance here.
      The portal has to be created in this schema first.</div>
  <?php endif; ?>

  <?php if (!empty($adhoc)): ?>
    <div class="bad">DUPLICATE RISK: <?= count($adhoc) ?> sub-item row(s) exist under
      non-canonical FPIDs (below). SgApplyAll STEP 4 guards on
      <code>FPID='SGTRAIN_&lt;CAT&gt;'</code>, so adding SGTRAIN to <code>$portals</code> would
      NOT skip these — it would insert 6 more rows on top, colliding on FPSEQ.
      These must be renamed to the canonical FPIDs or deleted first.</div>
    <?php tbl($adhoc); ?>
  <?php endif; ?>

  <div class="info">
    Canonical SYURLM rows present: <span class="<?= $urlmCanon === 7 ? 'yes' : 'no' ?>"><?= $urlmCanon ?> of 7</span>
    &nbsp;&bull;&nbsp; SYPORT top-level: <span class="<?= $topOk ? 'yes' : 'no' ?>"><?= $topOk ? 'yes' : 'MISSING' ?></span>
    &nbsp;&bull;&nbsp; canonical SYPORT sub-items: <span class="<?= $subCanon === 6 ? 'yes' : 'no' ?>"><?= $subCanon ?> of 6</span>
    &nbsp;&bull;&nbsp; roles with SGTRAIN in SYROLD:
      <span class="<?= (!isset($d['rold']['__error']) && count($d['rold'])) ? 'yes' : 'no' ?>"><?=
        isset($d['rold']['__error']) ? 'err' : count($d['rold']) ?></span>
  </div>

  <h4>What SgApplyAll's WHERE NOT EXISTS guards would find (1 = skip, 0 = will insert)</h4>
  <table>
    <tr><th>Guard</th><th>Found</th><th>Effect of an Apply All</th></tr>
    <?php foreach ($d['expect'] as $k => $v): ?>
    <tr><td><?= h($k) ?></td>
        <td class="<?= $v ? 'yes' : 'no' ?>"><?= (int)$v ?></td>
        <td><?= $v ? 'skip' : 'INSERT' ?></td></tr>
    <?php endforeach; ?>
  </table>

  <h4><?= h($sc) ?>.SYURLM — FUID LIKE 'SGTRAIN%'</h4>
  <?php tbl($d['urlm']); ?>

  <h4><?= h($sc) ?>.SYPORT — FPPORT = 'SGTRAIN'</h4>
  <?php tbl($d['port']); ?>

  <h4><?= h($sc) ?>.SYPORT — FPPORT = '<?= $REF ?>' (reference: what correct looks like)</h4>
  <?php tbl($d['refport']); ?>

  <h4><?= h($sc) ?>.SYROLD — every role holding SGTRAIN</h4>
  <?php tbl($d['rold'], 'no role has SGTRAIN in this schema'); ?>

  <h4><?= h($sc) ?> — <?= h($role) ?> role exists in SYROLM?
      <span class="<?= $d['roleExists'] ? 'yes' : 'no' ?>"><?= $d['roleExists'] ? 'yes' : 'NO' ?></span>
      &nbsp;|&nbsp; total SYPORR rows for <?= h($role) ?>: <?= (int)$d['porrCnt'] ?>
      (<?= $d['porrCnt'] > 0 ? 'whitelist mode' : 'bypass mode' ?>)</h4>

  <h4><?= h($sc) ?>.SYROLD — all portals for <?= h($role) ?></h4>
  <?php tbl($d['roleRold']); ?>

  <h4><?= h($sc) ?>.SYPORR — <?= h($role) ?>, SGTRAIN vs <?= $REF ?></h4>
  <?php tbl($d['rolePorr']); ?>

<?php endforeach; ?>

<h3>Read this first</h3>
<div class="info">
  Compare the two <code>SYPORT</code> blocks. If <?= $REF ?> has a top-level row plus 6 sub-items
  with FPIDs <code><?= $REF ?>_ACCT</code>&hellip;<code><?= $REF ?>_PUR</code> and SGTRAIN does not
  match that shape, that mismatch — not the role setup — is why SG Training Guides has no
  checkbox grid. Fix the portal definition to the canonical shape, then Apply All can own it
  from that point on.
</div>

</body>
</html>