<?php
// SgBackup.php
// Complete pre-update backup of all SG DB customizations.
// Run before any HarrisData library refresh or schema update.
// The generated SQL file restores the full SG state via STRSQL or RUNSQLSTM.
//
// Tables: SYURLM, SYPORT, SYROLD, SYPORR, SYPGMS (SGHDSDATA) + SYPGMO ($pgmlib)
// Reserved roles are excluded — they should never have SG customizations.
//
// Preview: https://portal.screen-graphics.com:5601/Custom/SG/SgBackup.php
// Backup:  https://portal.screen-graphics.com:5601/Custom/SG/SgBackup.php?run=YES
// Test pgm lib: append &pgmlib=SG5STDPGM

$pgmlib = 'HDSSTDPGM';
if (!empty($_GET['pgmlib'])) {
    $p = strtoupper(trim($_GET['pgmlib']));
    if (preg_match('/^[A-Z][A-Z0-9]{1,9}$/', $p)) $pgmlib = $p;
}

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$SG_PORTS = ['SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'];
$sgList   = "'" . implode("','", $SG_PORTS) . "'";

$SG_PGMS  = ['SGPORTLND','MOREQ','BOOKDASH','BOOKDRLL','SALESDASH','SALESDRLL',
              'SHIPSDASH','OPENORDLC','CSSRVINQ','CSDATINT','INVDATINT',
              'MODLYLBR','MOMATLCMP'];
$pgmList  = "'" . implode("','", $SG_PGMS) . "'";

function qrows($conn, $sql) {
    $rows = []; $s = @db2_exec($conn, $sql);
    if ($s === false) return ['__error' => db2_stmt_errormsg()];
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}
function cnt($rows) {
    return (empty($rows) || isset($rows[0]['__error'])) ? 0 : count($rows);
}
function toInserts($rows, $table) {
    if (empty($rows) || isset($rows[0]['__error'])) return [];
    $cols = implode(', ', array_keys($rows[0]));
    $out  = [];
    foreach ($rows as $row) {
        $vals = [];
        foreach ($row as $v) {
            $vals[] = ($v === null) ? 'NULL'
                    : "'" . str_replace("'", "''", rtrim((string)$v)) . "'";
        }
        $out[] = "INSERT INTO $table ($cols) VALUES (" . implode(', ', $vals) . ")";
    }
    return $out;
}

// Reserved roles
$rsvQ   = qrows($conn, "SELECT RTRIM(RMROLE) AS R FROM SGHDSDATA.SYROLM WHERE RTRIM(RMRESV)='Y'");
$rsvArr = [];
foreach ($rsvQ as $r) { if (!isset($r['__error'])) $rsvArr[] = "'" . $r['R'] . "'"; }
$rsvList = empty($rsvArr) ? "'__NONE__'" : implode(',', $rsvArr);

// Collect backup rows
$tables = [
    'SGHDSDATA.SYURLM' => qrows($conn,
        "SELECT * FROM SGHDSDATA.SYURLM WHERE RTRIM(FUID) LIKE 'SG%' ORDER BY FUID"),
    'SGHDSDATA.SYPORT' => qrows($conn,
        "SELECT * FROM SGHDSDATA.SYPORT WHERE FPPORT IN ($sgList) ORDER BY FPPORT,FPPAGE,FPSEQ"),
    'SGHDSDATA.SYROLD' => qrows($conn,
        "SELECT * FROM SGHDSDATA.SYROLD WHERE RDPORT IN ($sgList) AND RDROLE NOT IN ($rsvList) ORDER BY RDROLE,RDSEQN"),
    'SGHDSDATA.SYPORR' => qrows($conn,
        "SELECT * FROM SGHDSDATA.SYPORR WHERE PRROLE NOT IN ($rsvList) ORDER BY PRROLE,PRPORT,PRPAGE,PRSEQ"),
    'SGHDSDATA.SYPGMS' => qrows($conn,
        "SELECT * FROM SGHDSDATA.SYPGMS WHERE SPPGID IN ($pgmList) ORDER BY SPUSER,SPPGID"),
    "$pgmlib.SYPGMO"   => qrows($conn,
        "SELECT * FROM $pgmlib.SYPGMO WHERE SOPGID IN ($pgmList) ORDER BY SOPGID,SOMOPT"),
];
$descs = [
    'SGHDSDATA.SYURLM' => 'SG portal URL definitions',
    'SGHDSDATA.SYPORT' => 'SG portal navigation structure',
    'SGHDSDATA.SYROLD' => 'SG portal role assignments (non-reserved roles)',
    'SGHDSDATA.SYPORR' => 'Portal whitelist — all non-reserved role rows',
    'SGHDSDATA.SYPGMS' => 'User-program security for SG programs',
    "$pgmlib.SYPGMO"   => 'SG program registrations',
];
$totalRows = 0;
foreach ($tables as $r) $totalRows += cnt($r);

// Execute backup
$run = (isset($_GET['run']) && $_GET['run'] === 'YES');
$backupFile = ''; $backupStatus = ''; $byteCount = 0;

if ($run) {
    $ts    = date('Ymd_His');
    $rsvDisplay = implode(', ', array_map(fn($r) => trim($r,"'"), $rsvArr));
    $lines = [
        '-- SgBackup.php — SG Customizations Restore Script',
        '-- Generated: ' . date('Y-m-d H:i:s'),
        "-- Program library: $pgmlib",
        "-- Reserved roles excluded: $rsvDisplay",
        "-- Total rows: $totalRows",
        '',
        '-- ============================================================',
        '-- STEP 1: DELETE existing SG rows (safe clean-slate)',
        '-- Run each DELETE, then all INSERTs below.',
        '-- ============================================================',
        '',
        "DELETE FROM SGHDSDATA.SYPGMS WHERE SPPGID IN ($pgmList)",
        "DELETE FROM $pgmlib.SYPGMO WHERE SOPGID IN ($pgmList)",
        "DELETE FROM SGHDSDATA.SYPORR WHERE PRROLE NOT IN ($rsvList)",
        "DELETE FROM SGHDSDATA.SYROLD WHERE RDPORT IN ($sgList) AND RDROLE NOT IN ($rsvList)",
        "DELETE FROM SGHDSDATA.SYPORT WHERE FPPORT IN ($sgList)",
        "DELETE FROM SGHDSDATA.SYURLM WHERE RTRIM(FUID) LIKE 'SG%'",
        '',
        '-- ============================================================',
        '-- STEP 2: RE-INSERT (in dependency order)',
        '-- ============================================================',
    ];
    foreach ($tables as $table => $rows) {
        $lines[] = '';
        $lines[] = "-- $table (" . cnt($rows) . " rows)";
        $lines   = array_merge($lines, toInserts($rows, $table));
    }
    $sql        = implode("\n", $lines);
    $outDir     = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) $outDir = dirname(__FILE__);
    $backupFile = $outDir . '/SgBackup_' . $ts . '.sql';
    $written    = file_put_contents($backupFile, $sql);
    $byteCount  = $written !== false ? $written : 0;
    $backupStatus = $written !== false ? 'OK' : 'WRITE FAILED';
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG Backup</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.hdr .sub { font-size:11px; opacity:.75; margin-top:3px; font-weight:normal; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.warn { background:#fff8e1; border:1px solid #ffe082; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; }
.err  { background:#fce4ec; border:1px solid #f48fb1; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; color:#c62828; font-weight:bold; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:12px 16px; margin-bottom:14px; font-size:12px; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
th { background:#2a5a8c; color:#fff; padding:5px 10px;
     text-align:left; font-size:11px; }
td { padding:5px 10px; font-size:12px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
td.num { text-align:right; font-weight:bold; }
.btn { display:inline-block; margin-top:10px; background:#1565c0; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#0d47a1; }
.note { margin-top:8px; font-size:11px; color:#666; }
</style>
</head>
<body>

<div class="hdr">
  SG Backup — Pre-Update Snapshot
  <div class="sub">SGHDSDATA + <?= htmlspecialchars($pgmlib) ?> &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<?php if ($run && $backupStatus === 'OK'): ?>
<div class="ok">
  <strong>Backup complete.</strong> <?= number_format($totalRows) ?> rows written to
  <code><?= htmlspecialchars(basename($backupFile)) ?></code>
  (<?= number_format($byteCount / 1024, 1) ?> KB).<br>
  Restore: run the SQL file in STRSQL or <code>RUNSQLSTM</code> on the IBM i after a library refresh.
</div>
<?php elseif ($run): ?>
<div class="err">Backup write failed. Check <code>../Backup Files/</code> directory permissions.</div>
<?php else: ?>
<div class="info">
  <strong>What this backs up:</strong> All SG-managed rows from six tables covering portal definitions,
  role assignments, whitelist rows, program registrations, and user security.<br>
  Reserved roles (<?= implode(', ', array_map(fn($r) => '<strong>'.htmlspecialchars(trim($r,"'")).'</strong>', $rsvArr)) ?>)
  are excluded — they must not have SG customizations.<br><br>
  The output SQL contains DELETE + INSERT statements. After a HarrisData library refresh, run it in STRSQL to fully restore SG state. Then run
  <a href="SgApplyAll.php">SgApplyAll.php</a> to catch any new roles added since the last backup.
</div>
<?php endif; ?>

<table>
  <tr><th>Table</th><th>Description</th><th style="text-align:right">Rows</th></tr>
  <?php foreach ($tables as $table => $rows):
      $n   = cnt($rows);
      $err = isset($rows[0]['__error']) ? $rows[0]['__error'] : null;
  ?>
  <tr>
    <td><?= htmlspecialchars($table) ?></td>
    <td style="font-family:Arial"><?= htmlspecialchars($descs[$table] ?? '') ?></td>
    <td class="num"><?= $err
        ? '<span style="color:#c62828" title="' . htmlspecialchars($err) . '">ERR</span>'
        : number_format($n) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr style="background:#f5f5f5">
    <td colspan="2" style="font-weight:bold">Total</td>
    <td class="num" style="font-weight:bold"><?= number_format($totalRows) ?></td>
  </tr>
</table>

<?php if (!$run): ?>
<a class="btn" href="?run=YES<?= $pgmlib !== 'HDSSTDPGM' ? '&pgmlib=' . urlencode($pgmlib) : '' ?>">
  Run Backup &mdash; <?= number_format($totalRows) ?> rows
</a>
<p class="note">
  Test env program library: <a href="?run=YES&pgmlib=SG5STDPGM">use &amp;pgmlib=SG5STDPGM</a>
</p>
<?php endif; ?>

</body>
</html>
