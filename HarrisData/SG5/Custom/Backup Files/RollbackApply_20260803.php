<?php
// RollbackApply_20260803.php
// Undo the SgApplyAll.php run of 2026-08-03 22:02 that executed against SGHDSDATA
// with NO pre-apply backup (writeBackup returned WRITE FAILED and nothing gated on it).
//
// Every row that run INSERTED carries TSUS='SGAPPLY' and a TSTP inside the run's
// window, so the inserts are identifiable without the .sql backup. Earlier
// SgApplyAll runs also stamped 'SGAPPLY', so the TIMESTAMP WINDOW — not TSUS
// alone — is what scopes this. Do not widen it.
//
// NOT RECOVERABLE by this script (no per-row record exists):
//   STEP 9's "SYPORR fix PRSEL blanks -- 8 row(s) updated". That was an UPDATE
//   setting PRSEL='Y' on 8 top-level rows that were previously ''. It did not
//   touch PRTSTP, so those 8 rows are indistinguishable from always-'Y' rows.
//   Those 8 portals are now visible to their roles and must be found by eye in
//   Portal By Role Maintenance.
//
// Preview:  .../Custom/SG/RollbackApply_20260803.php
// Execute:  .../Custom/SG/RollbackApply_20260803.php?confirm=ROLLBACK
// Scope:    &only=SYROLD|SYPORR|SYPGMS|SYPGMO   (default: show all, delete all)

// IBM i native timestamp literal form (yyyy-mm-dd-hh.mm.ss.nnnnnn) — unambiguous to
// the TIMESTAMP() scalar. The run stamped 22:02:37 (backup attempt) through 22:02:41
// (page render); the window is padded either side of that.
$WIN_FROM = '2026-08-03-22.02.00.000000';
$WIN_TO   = '2026-08-03-22.03.30.000000';
$SCHEMA   = 'SGHDSDATA';          // the run targeted live regardless of port
$PGMLIB   = 'HDSSTDPGM';

$only = '';
if (!empty($_GET['only'])) {
    $o = strtoupper(trim($_GET['only']));
    if (in_array($o, array('SYROLD','SYPORR','SYPGMS','SYPGMO'), true)) $only = $o;
}

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB2 connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

function qrows($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if ($s === false) return array('__error' => db2_stmt_errormsg());
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    return $out;
}
function h($v) { return htmlspecialchars(trim((string)$v)); }

// Each entry: label => [select, delete, key columns]
$T = array();

$T['SYROLD'] = array(
    'desc' => 'Role-to-portal assignments inserted by the run',
    'sel'  => "SELECT RTRIM(RDROLE) AS ROLE, RTRIM(RDPORT) AS PORT, RDSEQN AS SEQ, "
            . "       RTRIM(RDTSUS) AS TSUS, CHAR(RDTSTP) AS TSTP "
            . "FROM $SCHEMA.SYROLD "
            . "WHERE RTRIM(RDTSUS)='SGAPPLY' "
            . "  AND RDTSTP >= TIMESTAMP('$WIN_FROM') AND RDTSTP <= TIMESTAMP('$WIN_TO') "
            . "ORDER BY RDROLE, RDPORT",
    'del'  => "DELETE FROM $SCHEMA.SYROLD "
            . "WHERE RTRIM(RDTSUS)='SGAPPLY' "
            . "  AND RDTSTP >= TIMESTAMP('$WIN_FROM') AND RDTSTP <= TIMESTAMP('$WIN_TO')",
);

$T['SYPORR'] = array(
    'desc' => 'Whitelist rows inserted by the run — includes STEP 6 top-level, '
            . 'STEP 7 sub-items, and the 108 STEP 8 native-portal rows (PRTSPT=Y)',
    'sel'  => "SELECT RTRIM(PRROLE) AS ROLE, RTRIM(PRPORT) AS PORT, RTRIM(PRPAGE) AS PAGE, "
            . "       PRSEQ AS SEQ, RTRIM(PRSEL) AS SEL, RTRIM(PRID) AS PRID, "
            . "       RTRIM(PRTSPT) AS STEP8, CHAR(PRTSTP) AS TSTP "
            . "FROM $SCHEMA.SYPORR "
            . "WHERE RTRIM(PRTSUS)='SGAPPLY' "
            . "  AND PRTSTP >= TIMESTAMP('$WIN_FROM') AND PRTSTP <= TIMESTAMP('$WIN_TO') "
            . "ORDER BY PRROLE, PRPORT, PRPAGE, PRSEQ",
    'del'  => "DELETE FROM $SCHEMA.SYPORR "
            . "WHERE RTRIM(PRTSUS)='SGAPPLY' "
            . "  AND PRTSTP >= TIMESTAMP('$WIN_FROM') AND PRTSTP <= TIMESTAMP('$WIN_TO')",
);

// SYPGMS / SYPGMO can NOT be scoped by timestamp: SgApplyAll STEP 10 inserts only
// (SOPGID,SOMOPT,SOMDES,SORESV) and STEP 11 only (SPUSER,SPPGID,SPOP01..15) — neither
// sets any timestamp column. So these two are scoped from the run log instead.
//
// POREQRPT: the SYPGMO registration itself was new (STEP 10 logged one OK), so every
// SYPGMS row for POREQRPT was created by this run — the log shows OK for every user
// and no SKIP. Deleting all SPPGID='POREQRPT' is therefore exact.
$NEW_PGM = 'POREQRPT';

// The only other SYPGMS rows the run created, verbatim from the log's OK lines.
$PGMS_PAIRS = array(
    array('DGILLESPIE','MOREQ'), array('DGILLESPIE','CSSRVINQ'), array('DGILLESPIE','MODLYLBR'),
    array('LCERVANTES','MOREQ'), array('LCERVANTES','CSSRVINQ'), array('LCERVANTES','MODLYLBR'),
    array('MREID','MOREQ'),      array('MREID','CSSRVINQ'),      array('MREID','MODLYLBR'),
    array('ZBLAKE','MOREQ'),     array('ZBLAKE','CSSRVINQ'),     array('ZBLAKE','MODLYLBR'),
);
$pairPred = array();
foreach ($PGMS_PAIRS as $p) {
    $pairPred[] = "(RTRIM(SPUSER)='" . $p[0] . "' AND RTRIM(SPPGID)='" . $p[1] . "')";
}
$pgmsWhere = "WHERE RTRIM(SPPGID)='$NEW_PGM' OR " . implode(' OR ', $pairPred);

$T['SYPGMS'] = array(
    'desc' => "User/program security rows inserted by the run: all $NEW_PGM rows, plus "
            . count($PGMS_PAIRS) . ' explicit pairs taken from the log (no timestamp column exists)',
    'sel'  => "SELECT RTRIM(SPUSER) AS USR, RTRIM(SPPGID) AS PGM, RTRIM(SPOP01) AS OP01 "
            . "FROM $SCHEMA.SYPGMS $pgmsWhere ORDER BY SPPGID, SPUSER",
    'del'  => "DELETE FROM $SCHEMA.SYPGMS $pgmsWhere",
);

$T['SYPGMO'] = array(
    'desc' => "Program registration inserted by the run in $PGMLIB — the single "
            . "$NEW_PGM/option-1 row (no timestamp column exists)",
    'sel'  => "SELECT RTRIM(SOPGID) AS PGM, SOMOPT AS OPT, RTRIM(SOMDES) AS DESCR "
            . "FROM $PGMLIB.SYPGMO WHERE RTRIM(SOPGID)='$NEW_PGM' AND SOMOPT=1",
    'del'  => "DELETE FROM $PGMLIB.SYPGMO WHERE RTRIM(SOPGID)='$NEW_PGM' AND SOMOPT=1",
);

if ($only !== '') $T = array($only => $T[$only]);

// ── Gather preview ──────────────────────────────────────────────────────────
$found = array();
$total = 0;
foreach ($T as $k => $t) {
    $rows = qrows($conn, $t['sel']);
    $found[$k] = $rows;
    if (!isset($rows['__error'])) $total += count($rows);
}

// ── Execute ─────────────────────────────────────────────────────────────────
$done = false; $results = array();
if (isset($_GET['confirm']) && $_GET['confirm'] === 'ROLLBACK' && $total > 0) {
    $done = true;
    // Children first: SYPORR references the SYROLD assignment conceptually.
    $order = array('SYPGMS','SYPGMO','SYPORR','SYROLD');
    foreach ($order as $k) {
        if (!isset($T[$k])) continue;
        $r = @db2_exec($conn, $T[$k]['del']);
        if ($r === false) $results[$k] = array('FAIL', db2_stmt_errormsg());
        else              $results[$k] = array('OK', db2_num_rows($r) . ' row(s) deleted');
    }
}
db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Rollback — SgApplyAll 2026-08-03 22:02</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:Arial,sans-serif; background:#f0f2f5; padding:24px; }
.hdr { background:linear-gradient(135deg,#8b1a17,#c62828); color:#fff; padding:14px 24px;
       border-radius:6px; border-bottom:3px solid #f90; margin-bottom:18px; }
.hdr h2 { font-size:18px; } .hdr .sub { font-size:11px; opacity:.85; margin-top:3px; }
h3 { font-size:14px; color:#2a5a8c; margin:20px 0 6px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:5px;
        overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); margin-bottom:12px; }
th { background:#2a5a8c; color:#fff; padding:6px 10px; text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; border-bottom:1px solid #f0f2f5; font-family:monospace; }
.live { background:#fdecea; border:1px solid #c62828; color:#8b1a17; border-radius:5px;
        padding:12px 16px; font-size:12px; margin-bottom:12px; font-weight:bold; line-height:1.6; }
.info { background:#e7f1fb; border:1px solid #a8c8e8; border-radius:5px; padding:11px 15px;
        font-size:12px; margin-bottom:12px; line-height:1.6; }
.warn { background:#fff3cd; border:1px solid #e0a800; border-radius:5px; padding:11px 15px;
        font-size:12px; margin-bottom:12px; line-height:1.6; }
.good { background:#e8f5e9; border:1px solid #2e7d32; color:#1b5e20; border-radius:5px;
        padding:11px 15px; font-size:12px; margin-bottom:12px; }
.btn { display:inline-block; margin-top:8px; padding:10px 24px; background:#c62828; color:#fff;
       text-decoration:none; border-radius:4px; font-size:14px; font-weight:bold; }
.btn.alt { background:#2a5a8c; font-weight:normal; }
.step8 { color:#8b1a17; font-weight:bold; }
</style>
</head>
<body>
<div class="hdr">
  <h2>Rollback &mdash; SgApplyAll run of 2026-08-03 22:02</h2>
  <div class="sub"><?= h($SCHEMA) ?> + <?= h($PGMLIB) ?>
    &nbsp;|&nbsp; window <?= h($WIN_FROM) ?> &rarr; <?= h($WIN_TO) ?>
    &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<div class="live">This DELETES rows from the LIVE menu tables. Read the preview first.
  Scoped strictly to rows stamped by that run &mdash; the timestamp window is what makes it
  safe. Earlier SgApplyAll runs also used TSUS='SGAPPLY', so never widen the window.</div>

<div class="warn"><strong>Not covered by this rollback.</strong> The run's
  <code>SYPORR fix PRSEL blanks &mdash; 8 row(s) updated</code> was an UPDATE that set
  <code>PRSEL='Y'</code> on 8 top-level rows previously <code>''</code>. It left
  <code>PRTSTP</code> untouched, so those 8 rows cannot be told apart from rows that were
  always 'Y'. Those 8 portals are now visible to their roles; they have to be found by eye
  in Portal By Role Maintenance and unchecked.</div>

<?php if ($done): ?>
  <h3>Result</h3>
  <table>
    <tr><th>Table</th><th>Status</th><th>Note</th></tr>
    <?php foreach ($results as $k => $r): ?>
    <tr><td><?= h($k) ?></td>
        <td style="color:<?= $r[0]==='OK'?'#2e7d32':'#c62828' ?>;font-weight:bold"><?= h($r[0]) ?></td>
        <td><?= h($r[1]) ?></td></tr>
    <?php endforeach; ?>
  </table>
  <div class="good">Users must log out and back in before their nav rebuilds.</div>
  <a class="btn alt" href="?">Re-check</a>

<?php elseif ($total === 0): ?>
  <div class="good">Nothing found in that window &mdash; either the rollback already ran,
    or the rows were stamped outside <?= h($WIN_FROM) ?>&ndash;<?= h($WIN_TO) ?>.
    If the latter, widen the window in the file, do not guess here.</div>

<?php else: ?>
  <div class="info"><strong><?= (int)$total ?> row(s)</strong> match the run's stamp and would be
    deleted. In the SYPORR table below, <span class="step8">STEP8=Y</span> marks the 108
    native-portal rows added in bulk &mdash; those were only logged as a count, so this listing
    is the only per-row record of them that exists.</div>

  <?php foreach ($found as $k => $rows): ?>
    <h3><?= h($k) ?> &mdash; <?= isset($rows['__error']) ? 'error' : count($rows) ?> row(s)</h3>
    <div style="font-size:11px;color:#666;margin-bottom:4px"><?= h($T[$k]['desc']) ?></div>
    <table>
    <?php if (isset($rows['__error'])): ?>
      <tr><td style="color:#c62828">ERR: <?= h($rows['__error']) ?></td></tr>
    <?php elseif (empty($rows)): ?>
      <tr><td><em>none</em></td></tr>
    <?php else: ?>
      <tr><?php foreach (array_keys($rows[0]) as $c) echo '<th>' . h($c) . '</th>'; ?></tr>
      <?php foreach ($rows as $r): ?>
      <tr<?= (isset($r['STEP8']) && trim($r['STEP8'])==='Y') ? ' class="step8"' : '' ?>>
        <?php foreach ($r as $v) echo '<td>' . h($v) . '</td>'; ?>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </table>
  <?php endforeach; ?>

  <a class="btn" href="?confirm=ROLLBACK<?= $only ? '&only='.urlencode($only) : '' ?>">
    Delete <?= (int)$total ?> Row(s) &mdash; <?= h($SCHEMA) ?></a>
  <a class="btn alt" href="?only=SYPORR">Scope: SYPORR only</a>
  <a class="btn alt" href="?only=SYROLD">Scope: SYROLD only</a>
<?php endif; ?>

</body>
</html>