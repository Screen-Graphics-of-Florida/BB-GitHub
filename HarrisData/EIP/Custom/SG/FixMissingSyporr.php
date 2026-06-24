<?php
// ============================================================
// Fix Missing SYPORR Rows
// Finds SYROLD portals that have no SYPORR top-level row for a
// given role, and inserts them (PRTSPT='Y', PRTSUS='BILL').
//
// Diagnose: load with no params (defaults to ENAPOLES)
// Fix:      ?confirm=FIX
// Other role: ?role=ROLENAME
// Other role + fix: ?role=ROLENAME&confirm=FIX
//
// URL:
//   https://portal.screen-graphics.com:5601/Custom/SG/FixMissingSyporr.php
//   https://portal.screen-graphics.com:5601/Custom/SG/FixMissingSyporr.php?role=ENAPOLES&confirm=FIX
// ============================================================

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role = isset($_GET['role']) ? strtoupper(trim($_GET['role'])) : 'ENAPOLES';

function qry($conn, $sql) {
    $rows = array();
    $s = @db2_exec($conn, $sql);
    if ($s === false) {
        return array('__error' => db2_stmt_errormsg());
    }
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}

// SYROLD portals for this role
$syrold = qry($conn,
    "SELECT RTRIM(RDPORT) AS RDPORT, RDSEQN "
  . "FROM SGHDSDATA.SYROLD "
  . "WHERE RDROLE='$role' ORDER BY RDSEQN");

// Existing top-level SYPORR rows for this role
$existing = qry($conn,
    "SELECT RTRIM(PRPORT) AS PRPORT "
  . "FROM SGHDSDATA.SYPORR "
  . "WHERE PRROLE='$role' AND RTRIM(PRPAGE)=''");

$existingPorts = array();
foreach ($existing as $r) {
    if (!isset($r['__error'])) $existingPorts[$r['PRPORT']] = true;
}

// Find SYROLD portals with no top-level SYPORR row
$missing = array();
foreach ($syrold as $r) {
    if (!isset($r['__error']) && !isset($existingPorts[$r['RDPORT']])) {
        $missing[] = $r['RDPORT'];
    }
}

// For each missing portal, get sub-items from SYPORT
$portalSubs = array();
foreach ($missing as $pcode) {
    $subs = qry($conn,
        "SELECT FPSEQ FROM SGHDSDATA.SYPORT "
      . "WHERE FPPORT='$pcode' AND RTRIM(FPPAGE)='$pcode' ORDER BY FPSEQ");
    $seqs = array();
    foreach ($subs as $s) {
        if (!isset($s['__error'])) $seqs[] = (int)$s['FPSEQ'];
    }
    $portalSubs[$pcode] = !empty($seqs) ? $seqs : range(1, 6);
}

// ---- Backup + Fix ------------------------------------------
$backupSql    = '';
$backupFile   = '';
$backupStatus = '';
$cntOk = $cntSkip = $cntFail = 0;
$log   = array();
$fixed = false;

function runSql($label, $sql) {
    global $conn, $cntOk, $cntSkip, $cntFail, $log;
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) {
        $cntFail++;
        $log[] = array('FAIL', $label, db2_stmt_errormsg());
    } else {
        $n = db2_num_rows($stmt);
        if ($n > 0) { $cntOk++;   $log[] = array('OK',   $label, ''); }
        else        { $cntSkip++; $log[] = array('SKIP', $label, 'already exists'); }
    }
}

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX' && !empty($missing)) {

    // Backup existing rows for this role (for reference)
    $bkStmt = @db2_exec($conn,
        "SELECT * FROM SGHDSDATA.SYPORR WHERE PRROLE='$role'");
    $lines = array(
        '-- FixMissingSyporr backup',
        '-- Generated: ' . date('Y-m-d H:i:s'),
        '-- Role: ' . $role,
        '-- Missing portals being added: ' . implode(', ', $missing),
        '',
    );
    if ($bkStmt) {
        $colCount = db2_num_fields($bkStmt);
        $cols = array();
        for ($i = 0; $i < $colCount; $i++) $cols[] = db2_field_name($bkStmt, $i);
        $colList = implode(', ', $cols);
        while ($row = db2_fetch_assoc($bkStmt)) {
            $vals = array();
            foreach ($row as $v) {
                $vals[] = ($v === null) ? 'NULL'
                        : "'" . str_replace("'", "''", rtrim($v)) . "'";
            }
            $lines[] = "INSERT INTO SGHDSDATA.SYPORR ($colList) VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }
    $backupSql = implode("\n", $lines);
    $ts        = date('Ymd_His');
    $outDir    = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) $outDir = dirname(__FILE__);
    $backupFile   = $outDir . '/FixMissingSyporr_' . $role . '_' . $ts . '.sql';
    $written      = file_put_contents($backupFile, $backupSql);
    $backupStatus = ($written !== false) ? 'OK'
                  : 'WRITE FAILED — copy SQL from textarea below';

    // Insert missing top-level + sub-item rows
    foreach ($missing as $pcode) {
        // Top-level row
        $prid_top = "$role/$pcode";
        runSql("top $role/$pcode",
            "INSERT INTO SGHDSDATA.SYPORR "
          . "    (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT) "
          . "SELECT '$role','$pcode','',1,'$prid_top','Y', "
          . "       CURRENT_TIMESTAMP,'BILL','Y' "
          . "FROM SYSIBM.SYSDUMMY1 "
          . "WHERE NOT EXISTS ( "
          . "    SELECT 1 FROM SGHDSDATA.SYPORR "
          . "    WHERE PRROLE='$role' AND PRPORT='$pcode' AND RTRIM(PRPAGE)='')");

        // Sub-item rows
        foreach ($portalSubs[$pcode] as $seq) {
            $seqstr   = number_format($seq, 2);
            $prid_sub = "$role/$pcode/$pcode/$seqstr";
            runSql("sub $role/$pcode/$seq",
                "INSERT INTO SGHDSDATA.SYPORR "
              . "    (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT) "
              . "SELECT '$role','$pcode','$pcode',$seq,'$prid_sub','Y', "
              . "       CURRENT_TIMESTAMP,'BILL','Y' "
              . "FROM SYSIBM.SYSDUMMY1 "
              . "WHERE NOT EXISTS ( "
              . "    SELECT 1 FROM SGHDSDATA.SYPORR "
              . "    WHERE PRROLE='$role' AND PRPORT='$pcode' "
              . "      AND RTRIM(PRPAGE)='$pcode' AND PRSEQ=$seq)");
        }
    }
    $fixed = true;
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Fix Missing SYPORR — <?= htmlspecialchars($role) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:24px; }
.header { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
          padding:14px 24px; border-radius:6px; border-bottom:3px solid #f90;
          margin-bottom:20px; }
.header h2 { font-size:20px; }
.header .sub { font-size:11px; opacity:.75; margin-top:3px; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:6px;
        padding:12px 18px; font-size:13px; margin-bottom:16px; }
.warn { background:#fff8e1; border:1px solid #ffe082; border-radius:6px;
        padding:12px 18px; font-size:13px; margin-bottom:16px; }
.err  { background:#fce4ec; border:1px solid #f48fb1; border-radius:6px;
        padding:12px 18px; font-size:13px; margin-bottom:16px; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:6px;
        padding:12px 18px; font-size:13px; margin-bottom:16px; }
.cards { display:flex; gap:16px; margin-bottom:20px; }
.card { background:#fff; border-radius:6px; padding:14px 24px;
        min-width:100px; text-align:center;
        box-shadow:0 2px 4px rgba(0,0,0,.06); }
.card.ok   { border-left:4px solid #2e7d32; }
.card.skip { border-left:4px solid #e65100; }
.card.fail { border-left:4px solid #c62828; }
.card .num { font-size:32px; font-weight:bold; color:#333; }
.card .lbl { font-size:11px; color:#666; margin-top:4px; }
table { width:100%; border-collapse:collapse; background:#fff;
        border-radius:6px; overflow:hidden;
        box-shadow:0 2px 6px rgba(0,0,0,.06); margin-bottom:20px; }
th { background:#2a5a8c; color:#fff; padding:7px 14px;
     text-align:left; font-size:11px; }
td { padding:5px 14px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f2f5; }
.btn { display:inline-block; margin-top:12px; background:#1565c0; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#0d47a1; }
textarea { width:100%; height:160px; font-family:monospace; font-size:11px;
           border:1px solid #ccc; border-radius:4px; padding:8px;
           background:#fff; margin-top:6px; }
form { margin-bottom:16px; }
input  { padding:6px 10px; font-size:13px; border:1px solid #ccc;
         border-radius:4px; width:200px; }
button { padding:6px 14px; font-size:13px; background:#2a5a8c; color:#fff;
         border:none; border-radius:4px; cursor:pointer; margin-left:6px; }
tr.ok   td:first-child { color:#2e7d32; font-weight:bold; }
tr.skip td:first-child { color:#999; }
tr.fail td { color:#c62828; font-weight:bold; }
</style>
</head>
<body>
<div class="header">
  <h2>Fix Missing SYPORR Rows</h2>
  <div class="sub">SGHDSDATA &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<form>
  <input name="role" value="<?= htmlspecialchars($role) ?>">
  <button>Check Role</button>
</form>

<?php if ($fixed): ?>
<div class="ok">
  <strong>Done.</strong> <?= $cntOk ?> inserted, <?= $cntSkip ?> skipped, <?= $cntFail ?> failed.
  Log out and back in to see changes.
</div>
<div class="<?= strpos($backupStatus,'FAIL')!==false ? 'warn' : 'ok' ?>">
  <strong>Backup:</strong> <?= htmlspecialchars(basename($backupFile)) ?>
  &mdash; <?= htmlspecialchars($backupStatus) ?>
</div>
<?php if (strpos($backupStatus,'WRITE FAILED') !== false): ?>
<div><strong>Backup SQL:</strong>
<textarea readonly><?= htmlspecialchars($backupSql) ?></textarea></div>
<?php endif; ?>
<?php if ($fixed && !empty($log)): ?>
<div class="cards">
  <div class="card ok"><div class="num"><?= $cntOk ?></div><div class="lbl">Inserted</div></div>
  <div class="card skip"><div class="num"><?= $cntSkip ?></div><div class="lbl">Skipped</div></div>
  <div class="card fail"><div class="num"><?= $cntFail ?></div><div class="lbl">Failed</div></div>
</div>
<table>
  <tr><th>Status</th><th>Item</th><th>Note</th></tr>
  <?php foreach ($log as $entry): ?>
  <tr class="<?= strtolower($entry[0]) ?>">
    <td><?= htmlspecialchars($entry[0]) ?></td>
    <td><?= htmlspecialchars($entry[1]) ?></td>
    <td><?= htmlspecialchars($entry[2]) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<?php elseif (empty($missing)): ?>
<div class="ok">
  <strong>All good.</strong>
  All <?= count($syrold) ?> SYROLD portals for <?= htmlspecialchars($role) ?>
  have top-level SYPORR rows.
</div>

<?php else: ?>
<div class="warn">
  <strong><?= count($missing) ?> SYROLD portal(s) are missing top-level SYPORR rows
  for <?= htmlspecialchars($role) ?>.</strong>
  Fix inserts them with PRTSPT='Y' PRTSUS='BILL'.
</div>

<table>
  <tr><th>Missing Portal</th><th>SYPORT sub-items found</th></tr>
  <?php foreach ($missing as $pcode): ?>
  <tr>
    <td><?= htmlspecialchars($pcode) ?></td>
    <td><?= implode(', ', $portalSubs[$pcode]) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<a class="btn" href="?role=<?= urlencode($role) ?>&confirm=FIX">
  Fix <?= count($missing) ?> Missing Portal(s) for <?= htmlspecialchars($role) ?>
</a>
<?php endif; ?>

<!-- SYROLD list for reference -->
<h3 style="margin:20px 0 8px;font-size:14px;color:#2a5a8c;">
  SYROLD for <?= htmlspecialchars($role) ?> (<?= count($syrold) ?> portals)
</h3>
<table>
  <tr><th>Seq</th><th>Portal</th><th>SYPORR top-level?</th></tr>
  <?php foreach ($syrold as $r):
      $port  = $r['RDPORT'];
      $hasSy = isset($existingPorts[$port]);
      $isMis = in_array($port, $missing);
  ?>
  <tr>
    <td><?= htmlspecialchars(rtrim($r['RDSEQN'])) ?></td>
    <td><?= htmlspecialchars($port) ?></td>
    <td style="color:<?= $hasSy?'#2e7d32':'#c62828' ?>;font-weight:<?= $isMis?'bold':'normal' ?>">
      <?= $hasSy ? 'YES' : 'MISSING' ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

</body>
</html>
