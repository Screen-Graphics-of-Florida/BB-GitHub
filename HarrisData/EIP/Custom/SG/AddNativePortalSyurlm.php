<?php
// ============================================================
// AddNativePortalSyurlm.php
//
// Root-cause fix for native portals not appearing as EIP tabs.
//
// Problem: EIP requires SYURLM FUID='PORTALNAME/PORTAL' (with
// FUTSPT='Y') to render a portal tab.  Native HDS portals only
// have 'PORTALNAME/REPORT' entries — so they are invisible in
// whitelist mode even when SYPORR and SYPORT are correct.
//
// Fix: for every SYROLD portal in the reference role that lacks
// a /PORTAL SYURLM entry, insert one using the URL from the
// portal's FPID→SYURLM lookup.  Because SYURLM is global (not
// per-role), this one-time insert fixes the tab for all roles.
//
// Usage:
//   Diagnose: load with no ?confirm  (shows what would be inserted)
//   Execute:  add ?confirm=FIX
//
// URL: https://portal.screen-graphics.com:5601/Custom/SG/AddNativePortalSyurlm.php
// URL: https://portal.screen-graphics.com:5601/Custom/SG/AddNativePortalSyurlm.php?role=ENAPOLES&confirm=FIX
// ============================================================

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

// Reference role — used to discover which portals need /PORTAL entries
$role = isset($_GET['role']) ? strtoupper(trim($_GET['role'])) : 'ENAPOLES';

function qrows($conn, $sql) {
    $rows = array(); $s = @db2_exec($conn, $sql);
    if (!$s) return $rows;
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}
function qrow($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_assoc($s); return $r ? $r : null;
}

// 1. SYROLD portals for reference role
$syrold = qrows($conn,
    "SELECT RTRIM(RDPORT) AS RDPORT, RDSEQN "
  . "FROM SGHDSDATA.SYROLD "
  . "WHERE RDROLE='$role' ORDER BY RDSEQN");

// 2. Build candidate list: portals that have SYPORT (FPTSPT=Y) but no /PORTAL in SYURLM
$candidates = array();
foreach ($syrold as $sr) {
    $p = $sr['RDPORT'];

    // Already has /PORTAL?
    $safe = str_replace("'", "''", "$p/PORTAL");
    $hasPortal = qrow($conn,
        "SELECT 1 FROM SGHDSDATA.SYURLM WHERE FUID='$safe'");
    if ($hasPortal) continue;  // already fixed

    // SYPORT top-level — need FPID
    $sport = qrow($conn,
        "SELECT RTRIM(FPID) AS FPID, RTRIM(FPTSPT) AS FPTSPT "
      . "FROM SGHDSDATA.SYPORT "
      . "WHERE FPPORT='$p' AND RTRIM(FPPAGE)=''");
    if (!$sport || $sport['FPTSPT'] !== 'Y') continue; // not active

    $fpid = rtrim($sport['FPID']);

    // SYURLM via FPID — get URL to carry forward
    $fuurl = '';
    if ($fpid !== '') {
        $safeFpid = str_replace("'", "''", $fpid);
        $urlm = qrow($conn,
            "SELECT RTRIM(FUURL) AS FUURL "
          . "FROM SGHDSDATA.SYURLM WHERE FUID='$safeFpid'");
        if ($urlm) $fuurl = rtrim($urlm['FUURL']);
    }

    $candidates[] = array(
        'pcode' => $p,
        'seq'   => rtrim($sr['RDSEQN']),
        'fpid'  => $fpid,
        'fuurl' => $fuurl,
        'fuid'  => "$p/PORTAL",
    );
}

// ---- Execute ---------------------------------------------------
$fixed = false;
$cntOk = $cntSkip = $cntFail = 0;
$log   = array();
$backupSql    = '';
$backupFile   = '';
$backupStatus = '';

function runSql($conn, $label, $sql) {
    global $cntOk, $cntSkip, $cntFail, $log;
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

if (isset($_GET['confirm']) && $_GET['confirm'] === 'FIX' && !empty($candidates)) {

    // Backup: fetch current SYURLM rows whose FUID matches any candidate /PORTAL
    $fuidList = array();
    foreach ($candidates as $c) {
        $fuidList[] = "'" . str_replace("'", "''", $c['fuid']) . "'";
    }
    $inClause = implode(',', $fuidList);
    $bkStmt   = @db2_exec($conn,
        "SELECT * FROM SGHDSDATA.SYURLM WHERE FUID IN ($inClause)");
    $lines = array(
        '-- AddNativePortalSyurlm backup (pre-insert)',
        '-- Generated: ' . date('Y-m-d H:i:s'),
        '-- Role reference: ' . $role,
        '-- Entries being inserted (none existed, so backup is empty):',
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
            $lines[] = "INSERT INTO SGHDSDATA.SYURLM ($colList) VALUES ("
                     . implode(', ', $vals) . ")";
        }
    }
    $backupSql  = implode("\n", $lines);
    $ts         = date('Ymd_His');
    $outDir     = dirname(__FILE__) . '/../Backup Files';
    if (!is_dir($outDir)) $outDir = dirname(__FILE__);
    $backupFile   = $outDir . '/AddNativePortalSyurlm_' . $role . '_' . $ts . '.sql';
    $written      = file_put_contents($backupFile, $backupSql);
    $backupStatus = ($written !== false) ? 'OK'
                  : 'WRITE FAILED — copy SQL from textarea below';

    // Insert /PORTAL SYURLM entries
    foreach ($candidates as $c) {
        $fuid  = str_replace("'", "''", $c['fuid']);
        $pcode = str_replace("'", "''", $c['pcode']);
        $fuurl = str_replace("'", "''", $c['fuurl']);
        runSql($conn, $c['fuid'],
            "INSERT INTO SGHDSDATA.SYURLM "
          . "    (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,"
          . "     FUTSTP,FUTSUS,FUTSWS,FUTSPT) "
          . "SELECT '$fuid','$pcode','$pcode','','$fuurl','','','',"
          . "       CURRENT_TIMESTAMP,'BILL','','Y' "
          . "FROM SYSIBM.SYSDUMMY1 "
          . "WHERE NOT EXISTS ("
          . "    SELECT 1 FROM SGHDSDATA.SYURLM WHERE FUID='$fuid')");
    }
    $fixed = true;
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Add Native Portal SYURLM</title>
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
        padding:12px 18px; font-size:12px; margin-bottom:16px; }
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
th { background:#2a5a8c; color:#fff; padding:6px 12px;
     text-align:left; font-size:11px; }
td { padding:4px 12px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f2f5; }
.btn { display:inline-block; margin-top:12px; background:#c62828; color:#fff;
       font-weight:bold; padding:10px 28px; border-radius:4px;
       text-decoration:none; font-size:14px; }
.btn:hover { background:#b71c1c; }
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
  <h2>Add Native Portal SYURLM /PORTAL Entries</h2>
  <div class="sub">SGHDSDATA &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>

<form>
  <input name="role" value="<?= htmlspecialchars($role) ?>">
  <button>Refresh</button>
</form>

<div class="info">
  <strong>What this fixes:</strong> EIP requires SYURLM FUID=<em>PORTALNAME/PORTAL</em>
  (FUTSPT=Y) to show a portal tab. Native HDS portals only have
  <em>PORTALNAME/REPORT</em> entries — no <em>/PORTAL</em> entry — so they are
  invisible in whitelist mode even when SYPORR and SYPORT are correct.<br><br>
  <strong>This insert is global</strong> (SYURLM is not per-role). Once done, the
  portal tabs will appear for ALL roles that have those portals with active SYPORR rows.
  The URL for each entry is taken from the portal's FPID→SYURLM lookup.
</div>

<?php if ($fixed): ?>
<div class="ok">
  <strong>Done.</strong> <?= $cntOk ?> inserted, <?= $cntSkip ?> skipped,
  <?= $cntFail ?> failed.
  Log out and back in to see all portals in the EIP tab bar.
</div>
<div class="<?= strpos($backupStatus,'FAIL')!==false ? 'warn' : 'ok' ?>">
  <strong>Backup:</strong> <?= htmlspecialchars(basename($backupFile)) ?>
  &mdash; <?= htmlspecialchars($backupStatus) ?>
</div>
<?php if (strpos($backupStatus,'WRITE FAILED') !== false): ?>
<div><strong>Backup SQL:</strong>
<textarea readonly><?= htmlspecialchars($backupSql) ?></textarea></div>
<?php endif; ?>
<div class="cards">
  <div class="card ok"><div class="num"><?= $cntOk ?></div><div class="lbl">Inserted</div></div>
  <div class="card skip"><div class="num"><?= $cntSkip ?></div><div class="lbl">Skipped</div></div>
  <div class="card fail"><div class="num"><?= $cntFail ?></div><div class="lbl">Failed</div></div>
</div>
<?php if (!empty($log)): ?>
<table>
  <tr><th>Status</th><th>FUID</th><th>Note</th></tr>
  <?php foreach ($log as $e): ?>
  <tr class="<?= strtolower($e[0]) ?>">
    <td><?= htmlspecialchars($e[0]) ?></td>
    <td><?= htmlspecialchars($e[1]) ?></td>
    <td><?= htmlspecialchars($e[2]) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<?php elseif (empty($candidates)): ?>
<div class="ok">
  <strong>All portals in <?= htmlspecialchars($role) ?>'s SYROLD already have
  /PORTAL entries in SYURLM.</strong> Nothing to do.
</div>

<?php else: ?>
<div class="warn">
  <strong><?= count($candidates) ?> portal(s) are missing /PORTAL SYURLM entries.</strong>
  Preview below. Click the red button to insert them.
</div>

<table>
  <tr>
    <th>Seq</th>
    <th>Portal</th>
    <th>New FUID (to insert)</th>
    <th>SYPORT FPID</th>
    <th>FUURL (from FPID lookup)</th>
  </tr>
  <?php foreach ($candidates as $c): ?>
  <tr>
    <td><?= htmlspecialchars($c['seq']) ?></td>
    <td><strong><?= htmlspecialchars($c['pcode']) ?></strong></td>
    <td style="color:#1565c0;font-weight:bold"><?= htmlspecialchars($c['fuid']) ?></td>
    <td><?= htmlspecialchars($c['fpid']) ?></td>
    <td>
      <?php if ($c['fuurl'] === ''): ?>
        <em style="color:#999">(blank — portal container)</em>
      <?php else: ?>
        <?= htmlspecialchars(substr($c['fuurl'], 0, 100)) ?>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<div class="warn">
  <strong>IMPORTANT:</strong> This modifies SGHDSDATA.SYURLM globally.
  These entries will make the above portals visible as tabs for any role
  that has active SYPORR and SYPORT rows for them.
  Portals marked <em>(blank)</em> are HDS menu containers — they will appear
  as tabs; clicking them navigates to the portal's default EIP page.
  Back up is taken automatically before any insert.
</div>

<a class="btn"
   href="?role=<?= urlencode($role) ?>&confirm=FIX">
  Insert <?= count($candidates) ?> /PORTAL Entries into SYURLM
</a>
<?php endif; ?>

</body>
</html>
