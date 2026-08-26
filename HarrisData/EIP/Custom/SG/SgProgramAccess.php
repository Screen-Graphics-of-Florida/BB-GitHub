<?php
// SgProgramAccess.php
// Grant / revoke HarrisData Program Option Security (SYPGMS) for one program across
// many users at once - tick as many boxes as you like, one Save.
//
// HarrisData's own Program Option Security Maintenance refreshes the page on every
// single tick, which makes granting a program to twenty people a twenty-refresh job.
// This does the same writes in one round trip.
//
// WHAT IT WRITES
//   <dataSchema>.SYPGMS  - SPUSER + SPPGID + SPOP01..SPOP15
//   Options shown are the ones actually registered for the program in SYPGMO
//   (program library), labelled with SOMDES - so our custom pages show one column
//   and a Harris program with 15 options shows 15.
//
// SAFETY
//   - Every Save snapshots the program's existing SYPGMS rows to SGOBJ.SGPGMSBK first
//   - Only changed cells are written
//   - Copy-to-other-environment is two-step: preview the differences, then confirm
//
// NOTE ON REFRESHES
//   SYPGMS lives in the data schema, so a SGHDSDATA -> S5HDSDATA refresh overwrites
//   Test grants with Live's. Don't do grant work on Test the night before a refresh.
//
//   .../Custom/SG/SgProgramAccess.php
//   .../Custom/SG/SgProgramAccess.php?pgm=MODLYLBR
//   Schema override: &schema=S5HDSDATA

// Framework headers - needed so the guard below can identify the signed-in profile.
// Without these, $userProfile does not exist and every request would be denied.
require_once dirname(__FILE__) . '/../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

// This page can grant any program to any user, so it is restricted to the profiles
// ticked for SGPGMACC in Program Option Security.
require_once dirname(__FILE__) . '/SgRequireAccess.php';
sgRequireAccess('SGPGMACC');

$BACKUP = 'SGOBJ.SGPGMSBK';

$port    = (string)@$_SERVER['SERVER_PORT'];
$schema  = ($port === '5610') ? 'S5HDSDATA' : 'SGHDSDATA';
if (!empty($_GET['schema'])) {
    $s = strtoupper(trim($_GET['schema']));
    if (in_array($s, array('S5HDSDATA', 'SGHDSDATA'), true)) $schema = $s;
}
$isLive   = ($schema === 'SGHDSDATA');
$pgmlib   = $isLive ? 'HDSSTDPGM' : 'SG5STDPGM';
$other    = $isLive ? 'S5HDSDATA' : 'SGHDSDATA';
$otherLib = $isLive ? 'SG5STDPGM' : 'HDSSTDPGM';
$otherNm  = $isLive ? 'SG5 TEST'  : 'EIP LIVE';

$pgm = '';
$src = isset($_POST['pgm']) ? $_POST['pgm'] : (isset($_GET['pgm']) ? $_GET['pgm'] : '');
if ($src !== '') {
    $p = strtoupper(trim($src));
    if (preg_match('/^[A-Z0-9_@#$]{1,10}$/', $p)) $pgm = $p;
}
$pgmSafe = str_replace("'", "''", $pgm);
$filter  = isset($_GET['f']) ? strtoupper(trim($_GET['f'])) : '';
$fSafe   = str_replace("'", "''", $filter);
$roleF   = isset($_GET['role']) ? strtoupper(trim($_GET['role'])) : '';
$roleSafe= str_replace("'", "''", $roleF);

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB2 connect failed: ' . htmlspecialchars(db2_conn_errormsg()));

function qrows($conn, $sql) {
    $out = array();
    $st  = @db2_exec($conn, $sql);
    if ($st === false) return array(array('__error' => db2_stmt_errormsg()));
    while ($r = db2_fetch_assoc($st)) $out[] = $r;
    return $out;
}
function qval($conn, $sql) {
    $st = @db2_exec($conn, $sql);
    if ($st === false) return null;
    $r = db2_fetch_row($st);
    return $r ? db2_result($st, 0) : null;
}
function h($v) { return htmlspecialchars(trim((string)$v)); }
function optCol($n) { return sprintf('SPOP%02d', (int)$n); }

// ── Options registered for this program ─────────────────────────────────────
$opts = array();
if ($pgm !== '') {
    foreach (qrows($conn,
        "SELECT SOMOPT, RTRIM(SOMDES) AS SOMDES FROM $pgmlib.SYPGMO "
      . "WHERE RTRIM(SOPGID)='$pgmSafe' ORDER BY SOMOPT") as $o) {
        if (isset($o['__error'])) continue;
        $n = (int)$o['SOMOPT'];
        if ($n >= 1 && $n <= 15) $opts[$n] = $o['SOMDES'];
    }
}

// ── Snapshot before any write ───────────────────────────────────────────────
function snapshot($conn, $schema, $pgmSafe, $BACKUP, &$log) {
    @db2_exec($conn,
        "CREATE TABLE $BACKUP AS (SELECT CURRENT_TIMESTAMP AS AUSTMP, "
      . "CAST('' AS CHAR(10)) AS AUUSER, R.* FROM $schema.SYPGMS R WHERE 1=0) WITH DATA");
    $ok = @db2_exec($conn,
        "INSERT INTO $BACKUP SELECT CURRENT_TIMESTAMP,'BBUSCH',R.* "
      . "FROM $schema.SYPGMS R WHERE RTRIM(R.SPPGID)='$pgmSafe'");
    $log[] = ($ok === false)
        ? array('FAIL', 'snapshot', db2_stmt_errormsg())
        : array('OK', 'snapshot', db2_num_rows($ok) . " row(s) saved to $BACKUP");
}

$log = array();
$did = false;
$copyPreview = null;

// ============================================================================
// SAVE
// ============================================================================
if ($pgm !== '' && isset($_POST['save']) && $opts) {
    $did = true;
    snapshot($conn, $schema, $pgmSafe, $BACKUP, $log);

    $want = (isset($_POST['sel']) && is_array($_POST['sel'])) ? $_POST['sel'] : array();
    $shown = (isset($_POST['shown']) && is_array($_POST['shown'])) ? $_POST['shown'] : array();

    $cur = array();
    foreach (qrows($conn, "SELECT * FROM $schema.SYPGMS WHERE RTRIM(SPPGID)='$pgmSafe'") as $r) {
        if (!isset($r['__error'])) $cur[strtoupper(trim($r['SPUSER']))] = $r;
    }

    $ins = $upd = 0;
    foreach ($shown as $u => $_) {
        $u  = strtoupper(trim($u));
        if (!preg_match('/^[A-Z0-9_@#$]{1,10}$/', $u)) continue;
        $uS = str_replace("'", "''", $u);
        $have = isset($cur[$u]);

        $sets = array();
        foreach ($opts as $n => $desc) {
            $col = optCol($n);
            $on  = !empty($want[$u][$n]) ? 'Y' : 'N';
            $was = $have ? strtoupper(trim((string)$cur[$u][$col])) : 'N';
            if ($was !== $on) $sets[$col] = $on;
        }
        if (!$sets) continue;

        if ($have) {
            $frag = array();
            foreach ($sets as $c => $v) $frag[] = "$c='$v'";
            $ok = @db2_exec($conn,
                "UPDATE $schema.SYPGMS SET " . implode(',', $frag)
              . " WHERE RTRIM(SPUSER)='$uS' AND RTRIM(SPPGID)='$pgmSafe'");
            if ($ok === false) $log[] = array('FAIL', $u, db2_stmt_errormsg());
            else { $upd++; $log[] = array('OK', $u, 'updated: ' . implode(', ',
                       array_map(function($c, $v) { return "$c=$v"; },
                                 array_keys($sets), array_values($sets)))); }
        } else {
            // New row: every option N except the ones being granted.
            $cols = array('SPUSER', 'SPPGID');
            $vals = array("'$uS'", "'$pgmSafe'");
            for ($i = 1; $i <= 15; $i++) {
                $cols[] = optCol($i);
                $vals[] = (isset($sets[optCol($i)]) && $sets[optCol($i)] === 'Y') ? "'Y'" : "'N'";
            }
            $ok = @db2_exec($conn,
                "INSERT INTO $schema.SYPGMS (" . implode(',', $cols) . ") VALUES ("
              . implode(',', $vals) . ")");
            if ($ok === false) $log[] = array('FAIL', $u, db2_stmt_errormsg());
            else { $ins++; $log[] = array('OK', $u, 'row created with grant'); }
        }
    }
    if ($ins === 0 && $upd === 0) $log[] = array('SKIP', $pgm, 'no changes - nothing to write');
}

// ============================================================================
// COPY TO OTHER ENVIRONMENT - step 1 preview, step 2 apply
// ============================================================================
if ($pgm !== '' && (isset($_POST['copyprev']) || isset($_POST['copygo'])) && $opts) {
    $cols = array();
    foreach ($opts as $n => $d) $cols[] = optCol($n);

    $sel = array();
    foreach ($cols as $c) $sel[] = "RTRIM(S.$c) AS S_$c, COALESCE(RTRIM(T.$c),'-') AS T_$c";
    $diffWhere = array();
    foreach ($cols as $c) $diffWhere[] = "COALESCE(RTRIM(T.$c),'?') <> RTRIM(S.$c)";

    $copyPreview = qrows($conn,
        "SELECT RTRIM(S.SPUSER) AS SPUSER, " . implode(', ', $sel) . " "
      . "FROM $schema.SYPGMS S "
      . "LEFT JOIN $other.SYPGMS T "
      . "  ON RTRIM(T.SPUSER)=RTRIM(S.SPUSER) AND RTRIM(T.SPPGID)=RTRIM(S.SPPGID) "
      . "WHERE RTRIM(S.SPPGID)='$pgmSafe' AND (" . implode(' OR ', $diffWhere) . ") "
      . "ORDER BY S.SPUSER");

    if (isset($_POST['copygo'])) {
        $did = true;
        snapshot($conn, $other, $pgmSafe, $BACKUP, $log);
        $setFrag = array();
        foreach ($cols as $c) {
            $setFrag[] = "$c = (SELECT S.$c FROM $schema.SYPGMS S "
                       . "WHERE RTRIM(S.SPUSER)=RTRIM(T.SPUSER) AND RTRIM(S.SPPGID)=RTRIM(T.SPPGID))";
        }
        $ok = @db2_exec($conn,
            "UPDATE $other.SYPGMS T SET " . implode(', ', $setFrag)
          . " WHERE RTRIM(T.SPPGID)='$pgmSafe' AND EXISTS (SELECT 1 FROM $schema.SYPGMS S "
          . "   WHERE RTRIM(S.SPUSER)=RTRIM(T.SPUSER) AND RTRIM(S.SPPGID)=RTRIM(T.SPPGID))");
        $log[] = ($ok === false)
            ? array('FAIL', "copy to $otherNm", db2_stmt_errormsg())
            : array('OK', "copy to $otherNm", db2_num_rows($ok) . ' existing row(s) updated');

        $allCols = array('SPUSER', 'SPPGID');
        for ($i = 1; $i <= 15; $i++) $allCols[] = optCol($i);
        $ok2 = @db2_exec($conn,
            "INSERT INTO $other.SYPGMS (" . implode(',', $allCols) . ") "
          . "SELECT " . implode(',', array_map(function($c) { return "S.$c"; }, $allCols))
          . " FROM $schema.SYPGMS S WHERE RTRIM(S.SPPGID)='$pgmSafe' "
          . "  AND NOT EXISTS (SELECT 1 FROM $other.SYPGMS T "
          . "      WHERE RTRIM(T.SPUSER)=RTRIM(S.SPUSER) AND RTRIM(T.SPPGID)=RTRIM(S.SPPGID))");
        $log[] = ($ok2 === false)
            ? array('FAIL', "copy to $otherNm", db2_stmt_errormsg())
            : array('OK', "copy to $otherNm", db2_num_rows($ok2) . ' new row(s) inserted');
        $copyPreview = null;
    }
}

// ── Display data ────────────────────────────────────────────────────────────
// Sort for the program list. Server-side is fine here - this page holds no unsaved
// state, unlike the grid where a reload would discard ticks.
$sortKey = isset($_GET['s']) ? strtoupper(trim($_GET['s'])) : 'PGM';
$sortDir = (isset($_GET['d']) && strtoupper($_GET['d']) === 'D') ? 'DESC' : 'ASC';
if (!in_array($sortKey, array('PGM', 'DESCR', 'OPTS'), true)) $sortKey = 'PGM';
$orderBy = array('PGM' => '1', 'DESCR' => '2', 'OPTS' => '3');

$programs = array();
if ($pgm === '') {
    $w = ($filter !== '') ? "AND (RTRIM(O.SOPGID) LIKE '%$fSafe%' OR UPPER(O.SOMDES) LIKE '%$fSafe%')" : '';
    // DESCR is the description of the FIRST option (lowest SOMOPT), not the
    // alphabetically first - MIN(SOMDES) made AVATAX read "Delete" and EEO "View".
    $programs = qrows($conn,
        "SELECT RTRIM(O.SOPGID) AS PGM, "
      . "       (SELECT RTRIM(O2.SOMDES) FROM $pgmlib.SYPGMO O2 "
      . "         WHERE RTRIM(O2.SOPGID)=RTRIM(O.SOPGID) "
      . "         ORDER BY O2.SOMOPT FETCH FIRST 1 ROW ONLY) AS DESCR, "
      . "       COUNT(*) AS OPTS "
      . "FROM $pgmlib.SYPGMO O WHERE 1=1 $w GROUP BY O.SOPGID "
      . "ORDER BY " . $orderBy[$sortKey] . " $sortDir "
      . "FETCH FIRST 300 ROWS ONLY");
}

$users = array();
$grants = array();
if ($pgm !== '') {
    $rw = ($roleF !== '') ? "WHERE RTRIM(USROLE)='$roleSafe'" : '';
    $users = qrows($conn,
        "SELECT RTRIM(USUSER) AS U, RTRIM(USDESC) AS D, RTRIM(USROLE) AS R "
      . "FROM $schema.SYUSER $rw ORDER BY USUSER");
    foreach (qrows($conn, "SELECT * FROM $schema.SYPGMS WHERE RTRIM(SPPGID)='$pgmSafe'") as $r) {
        if (!isset($r['__error'])) $grants[strtoupper(trim($r['SPUSER']))] = $r;
    }
}
$roles = qrows($conn,
    "SELECT DISTINCT RTRIM(USROLE) AS R FROM $schema.SYUSER WHERE RTRIM(USROLE)<>'' ORDER BY 1");

$pgmDesc = ($pgm !== '') ? (string)qval($conn,
    "SELECT MIN(RTRIM(SOMDES)) FROM $pgmlib.SYPGMO WHERE RTRIM(SOPGID)='$pgmSafe'") : '';

db2_close($conn);
$qs = 'schema=' . urlencode($schema);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Program Access by User</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 22px; }
.hdr { background: linear-gradient(135deg,#111827,#6B7280); color:#fff;
       padding:14px 24px; border-radius:6px; margin-bottom:16px; }
.hdr h1 { font-size:20px; } .hdr .sub { font-size:11px; opacity:.85; margin-top:4px; }
.section { font-size:14px; color:#111827; font-weight:bold; margin:18px 0 6px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:6px;
        overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,.06); margin-bottom:12px; }
th { background:#374151; color:#fff; padding:7px 10px; text-align:left; font-size:12px; }
td { padding:4px 10px; font-size:12px; border-bottom:1px solid #f0f2f5; }
tr:hover td { background:#EFF6FF; }
td.c, th.c { text-align:center; }
.mono { font-family:monospace; }
tr.ok td:first-child { color:#1DA032; font-weight:bold; }
tr.skip td:first-child { color:#888; }
tr.fail td { color:#CC1F20; font-weight:bold; }
.info { background:#EFF6FF; border:1px solid #2563EB; border-radius:6px;
        padding:11px 15px; font-size:12px; margin-bottom:12px; line-height:1.55; }
.live { background:#fdecea; border:1px solid #CC1F20; border-radius:6px; color:#8b1a17;
        padding:11px 15px; font-size:12px; margin-bottom:12px; font-weight:bold; }
.warn { background:#fff3cd; border:1px solid #e0a800; border-radius:6px;
        padding:11px 15px; font-size:12px; margin-bottom:12px; line-height:1.55; }
.btn { display:inline-block; margin:6px 6px 0 0; padding:9px 20px; background:#06B6D4;
       color:#fff; text-decoration:none; border-radius:4px; font-size:13px; border:0;
       cursor:pointer; }
.btn.go { background:#1DA032; } .btn.warnb { background:#7B1FA2; }
a.rl { font-family:monospace; color:#2563EB; text-decoration:none; font-weight:bold; }
input[type=text] { padding:6px 8px; font-size:13px; border:1px solid #cbd5e1; border-radius:4px; }
select { padding:6px 8px; font-size:13px; border:1px solid #cbd5e1; border-radius:4px; }
</style>
<script>
function sgAll(col, on) {
  var b = document.getElementsByClassName('opt' + col);
  for (var i = 0; i < b.length; i++) if (!b[i].disabled) b[i].checked = on;
  return false;
}
</script>
</head>
<body>
<div class="hdr">
  <h1>Program Access by User</h1>
  <div class="sub">Schema <?= h($schema) ?> &nbsp;|&nbsp; <?= $isLive ? 'EIP LIVE' : 'SG5 TEST' ?>
    &nbsp;|&nbsp; programs from <?= h($pgmlib) ?> &nbsp;|&nbsp; port <?= h($port) ?>
    &nbsp;|&nbsp; signed in as <strong><?= h(isset($userProfile) ? $userProfile : '?') ?></strong>
    <?= isset($activeRole) ? ' (' . h($activeRole) . ')' : '' ?></div>
  <div style="margin-top:8px">
    <a class="btn" style="background:#06B6D4" href="<?= htmlspecialchars(
        (isset($homeURL) ? rtrim($homeURL, '/') : '') . '/Welcome.php?baseVar='
      . rawurlencode(isset($baseVar) ? $baseVar : '') . '&eID='
      . rawurlencode(isset($eID) ? $eID : '') . '&portal=9999999999', ENT_QUOTES) ?>">&#8592; Back to EIP</a>
    <?php // EIP's own New Session - SYURLM ADDITIONALBROWSERSESSION/REPORT. ?>
    <a class="btn" style="background:#CC1F20" target="_blank" href="<?= htmlspecialchars(
        (isset($homeURL) ? rtrim($homeURL, '/') : '')
      . (isset($phpPath) ? $phpPath : '/') . 'Signon.php?newSession=Y', ENT_QUOTES) ?>">Sign in as another user</a>
  </div>
</div>

<?php if ($isLive): ?>
<div class="live">Pointed at SGHDSDATA - LIVE. Grants take effect for real users immediately.</div>
<?php endif; ?>

<?php if ($did): ?>
  <div class="section">Result</div>
  <table>
    <tr><th>Status</th><th>Item</th><th>Note</th></tr>
    <?php foreach ($log as $L): ?>
    <tr class="<?= strtolower($L[0]) ?>"><td><?= $L[0] ?></td>
      <td class="mono"><?= h($L[1]) ?></td><td><?= h($L[2]) ?></td></tr>
    <?php endforeach; ?>
  </table>
  <div class="warn"><strong>Rollback</strong> - restores this program's grants as they were
    immediately before this change:<br>
    <span class="mono">
    DELETE FROM <?= h($schema) ?>.SYPGMS WHERE RTRIM(SPPGID)='<?= h($pgm) ?>'<br>
    INSERT INTO <?= h($schema) ?>.SYPGMS SELECT SPUSER,SPPGID,SPOP01,SPOP02,SPOP03,SPOP04,SPOP05,
      SPOP06,SPOP07,SPOP08,SPOP09,SPOP10,SPOP11,SPOP12,SPOP13,SPOP14,SPOP15
      FROM <?= $BACKUP ?> WHERE RTRIM(SPPGID)='<?= h($pgm) ?>'
      AND AUSTMP=(SELECT MAX(AUSTMP) FROM <?= $BACKUP ?> WHERE RTRIM(SPPGID)='<?= h($pgm) ?>')
    </span>
  </div>
  <a class="btn" href="?pgm=<?= urlencode($pgm) ?>&<?= $qs ?>">Back to <?= h($pgm) ?></a>
  <a class="btn" href="?<?= $qs ?>">Program list</a>

<?php elseif ($copyPreview !== null): ?>
  <div class="section">Copy <?= h($pgm) ?> grants to <?= h($otherNm) ?></div>
  <?php if (!$copyPreview || isset($copyPreview[0]['__error'])): ?>
    <div class="info"><?= isset($copyPreview[0]['__error']) ? h($copyPreview[0]['__error'])
        : 'No differences - the two environments already match for this program.' ?></div>
    <a class="btn" href="?pgm=<?= urlencode($pgm) ?>&<?= $qs ?>">Back</a>
  <?php else: ?>
    <div class="warn">These <?= count($copyPreview) ?> user(s) differ. Applying will make
      <strong><?= h($otherNm) ?></strong> match <strong><?= h($schema) ?></strong> for
      <?= h($pgm) ?> only. Users present here but missing there will be created.
      A snapshot of the target is taken first.</div>
    <table>
      <tr><th>User</th>
        <?php foreach ($opts as $n => $d): ?>
        <th class="c"><?= h($d) ?><br><span style="font-weight:normal">here &rarr; there</span></th>
        <?php endforeach; ?></tr>
      <?php foreach ($copyPreview as $r): if (isset($r['__error'])) continue; ?>
      <tr><td class="mono"><?= h($r['SPUSER']) ?></td>
        <?php foreach ($opts as $n => $d): $c = optCol($n); ?>
        <td class="c mono"><?= h($r['S_' . $c]) ?> &rarr; <?= h($r['T_' . $c]) ?></td>
        <?php endforeach; ?></tr>
      <?php endforeach; ?>
    </table>
    <form method="post" action="?pgm=<?= urlencode($pgm) ?>&<?= $qs ?>">
      <input type="hidden" name="pgm" value="<?= h($pgm) ?>">
      <button class="btn warnb" type="submit" name="copygo" value="1">
        Apply to <?= h($otherNm) ?></button>
      <a class="btn" href="?pgm=<?= urlencode($pgm) ?>&<?= $qs ?>">Cancel</a>
    </form>
  <?php endif; ?>

<?php elseif ($pgm !== ''): ?>
  <div class="section"><?= h($pgm) ?><?= $pgmDesc !== '' ? ' &mdash; ' . h($pgmDesc) : '' ?></div>
  <?php if (!$opts): ?>
    <div class="live"><?= h($pgm) ?> is not registered in <?= h($pgmlib) ?>.SYPGMO, so it has
      no options to grant. Register it first.</div>
    <a class="btn" href="?<?= $qs ?>">Program list</a>
  <?php else: ?>
  <div class="info">Tick as many as you need and press Save once. Only changed rows are
    written. A user with no existing row gets one created, with every other option set to N.</div>

  <form method="get" style="margin-bottom:10px">
    <input type="hidden" name="pgm" value="<?= h($pgm) ?>">
    <input type="hidden" name="schema" value="<?= h($schema) ?>">
    Filter by role:
    <select name="role" onchange="this.form.submit()">
      <option value="">(all roles)</option>
      <?php foreach ($roles as $r): if (isset($r['__error'])) continue; ?>
      <option value="<?= h($r['R']) ?>" <?= ($roleF === $r['R']) ? 'selected' : '' ?>><?= h($r['R']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if ($roleF !== ''): ?>
      <a class="rl" href="?pgm=<?= urlencode($pgm) ?>&<?= $qs ?>">clear</a>
    <?php endif; ?>
  </form>

  <form method="post" action="?pgm=<?= urlencode($pgm) ?>&<?= $qs ?>&role=<?= urlencode($roleF) ?>">
  <input type="hidden" name="pgm" value="<?= h($pgm) ?>">
  <table>
    <tr><th>User</th><th>Name</th><th>Role</th>
      <?php foreach ($opts as $n => $d): ?>
      <th class="c"><?= h($d) ?><br>
        <span style="font-weight:normal;font-size:10px">
        <a href="#" style="color:#9ecbff" onclick="return sgAll(<?= $n ?>,true)">all</a> /
        <a href="#" style="color:#9ecbff" onclick="return sgAll(<?= $n ?>,false)">none</a></span></th>
      <?php endforeach; ?></tr>
    <?php foreach ($users as $u): if (isset($u['__error'])) continue;
        $uk = strtoupper($u['U']); ?>
    <tr>
      <td class="mono"><?= h($u['U']) ?><input type="hidden"
            name="shown[<?= h($u['U']) ?>]" value="1"></td>
      <td><?= h($u['D']) ?></td>
      <td class="mono"><?= h($u['R']) ?></td>
      <?php foreach ($opts as $n => $d):
          $col = optCol($n);
          $on  = isset($grants[$uk]) && strtoupper(trim((string)$grants[$uk][$col])) === 'Y'; ?>
      <td class="c"><input type="checkbox" class="opt<?= $n ?>"
            name="sel[<?= h($u['U']) ?>][<?= $n ?>]" value="1" <?= $on ? 'checked' : '' ?>></td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </table>
  <button class="btn go" type="submit" name="save" value="1">Save <?= h($pgm) ?> &mdash; <?= h($schema) ?></button>
  <button class="btn warnb" type="submit" name="copyprev" value="1">Compare with <?= h($otherNm) ?></button>
  <a class="btn" href="?<?= $qs ?>">Program list</a>
  </form>
  <?php endif; ?>

<?php else: ?>
  <div class="info">Pick a program. Nothing changes until you tick boxes and press Save.</div>
  <form method="get" style="margin-bottom:10px">
    <input type="hidden" name="schema" value="<?= h($schema) ?>">
    <input type="text" name="f" value="<?= h($filter) ?>" placeholder="program name or description">
    <button class="btn" type="submit">Search</button>
  </form>
  <div class="section">Programs in <?= h($pgmlib) ?>.SYPGMO
    (<?= count($programs) ?><?= count($programs) >= 300 ? ', showing first 300 - narrow the search' : '' ?>)</div>
  <table>
    <?php
      // Clicking a header re-sorts; clicking the active one flips direction.
      $hdrs = array('PGM' => array('Program', ''), 'DESCR' => array('Description', ''),
                    'OPTS' => array('Options', 'c'));
      echo '<tr>';
      foreach ($hdrs as $k => $hh) {
          $nd  = ($sortKey === $k && $sortDir === 'ASC') ? 'D' : 'A';
          $ar  = ($sortKey === $k) ? ($sortDir === 'ASC' ? ' &#9650;' : ' &#9660;') : '';
          $url = '?' . $qs . '&f=' . urlencode($filter) . '&s=' . $k . '&d=' . $nd;
          echo '<th' . ($hh[1] ? ' class="' . $hh[1] . '"' : '') . '>'
             . '<a href="' . htmlspecialchars($url, ENT_QUOTES)
             . '" style="color:#fff;text-decoration:none">' . $hh[0] . $ar . '</a></th>';
      }
      echo '<th></th></tr>';
    ?>
    <?php foreach ($programs as $p): if (isset($p['__error'])) continue; ?>
    <tr><td class="mono"><?= h($p['PGM']) ?></td><td><?= h($p['DESCR']) ?></td>
      <td class="c"><?= (int)$p['OPTS'] ?></td>
      <td><a class="rl" href="?pgm=<?= urlencode($p['PGM']) ?>&<?= $qs ?>">open &rsaquo;</a></td></tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

</body>
</html>
