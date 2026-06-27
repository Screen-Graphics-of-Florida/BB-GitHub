<?php
// DiagGetMenu.php
// Runs the EXACT GetMenu.php SQL for a role and shows which
// SYROLD portals match the SYPORR join and which do not.
// Also shows SYPORT.FPSEQ vs SYPORR.PRSEQ for each portal.
//
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagGetMenu.php
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagGetMenu.php?role=ENAPOLES

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$role = isset($_GET['role']) ? strtoupper(trim($_GET['role'])) : 'ENAPOLES';

function qrows($conn, $sql) {
    $rows = array(); $s = @db2_exec($conn, $sql);
    if (!$s) return array('__error' => db2_stmt_errormsg());
    while ($r = db2_fetch_assoc($s)) $rows[] = $r;
    return $rows;
}
function qval($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s);
    return $r ? db2_result($s, 0) : null;
}

// Step 1: Is the role in whitelist or bypass mode?
$role_safe = str_replace("'", "''", $role);
$syporr_cnt = (int)qval($conn,
    "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role_safe'");
$mode = ($syporr_cnt > 0) ? 'WHITELIST' : 'BYPASS';

// Step 2: SYROLD portals for this role
$syrold = qrows($conn,
    "SELECT RTRIM(RDPORT) AS RDPORT, RDSEQN "
  . "FROM SGHDSDATA.SYROLD "
  . "WHERE RDROLE='$role_safe' ORDER BY RDSEQN");

// Step 3: For each SYROLD portal, show SYPORT top-level FPSEQ and SYPORR PRSEQ/PRSEL
$portalDetail = array();
foreach ($syrold as $sr) {
    if (isset($sr['__error'])) continue;
    $p = rtrim($sr['RDPORT']);
    $psafe = str_replace("'", "''", $p);

    // SYPORT top-level row
    $sport = qrows($conn,
        "SELECT FPSEQ, RTRIM(FPID) AS FPID, RTRIM(FPTSPT) AS FPTSPT "
      . "FROM SGHDSDATA.SYPORT "
      . "WHERE FPPORT='$psafe' AND RTRIM(FPPAGE)='' "
      . "ORDER BY FPSEQ");

    // SYPORR top-level row(s) for this role+portal
    $sporr = qrows($conn,
        "SELECT PRSEQ, RTRIM(PRSEL) AS PRSEL, RTRIM(PRTSPT) AS PRTSPT, "
      . "       RTRIM(PRTSUS) AS PRTSUS "
      . "FROM SGHDSDATA.SYPORR "
      . "WHERE PRROLE='$role_safe' AND PRPORT='$psafe' AND RTRIM(PRPAGE)='' "
      . "ORDER BY PRSEQ");

    // SYURLM via FPID
    $fpid    = (!empty($sport) && !isset($sport[0]['__error'])) ? rtrim($sport[0]['FPID']) : '';
    $fpseq   = (!empty($sport) && !isset($sport[0]['__error'])) ? (int)$sport[0]['FPSEQ'] : null;
    $fptspt  = (!empty($sport) && !isset($sport[0]['__error'])) ? $sport[0]['FPTSPT'] : '';
    $syurlm  = null;
    if ($fpid !== '') {
        $fsafe = str_replace("'", "''", $fpid);
        $urow = qrows($conn,
            "SELECT RTRIM(FUID) AS FUID, RTRIM(FUTSPT) AS FUTSPT "
          . "FROM SGHDSDATA.SYURLM WHERE FUID='$fsafe'");
        if (!empty($urow) && !isset($urow[0]['__error'])) $syurlm = $urow[0];
    }

    // In whitelist mode: does ANY SYPORR row match FPSEQ + PRSEL='Y'?
    $matchFound = false;
    if ($mode === 'BYPASS') {
        $matchFound = true; // bypass: no join needed
    } else {
        foreach ($sporr as $sr2) {
            if (isset($sr2['__error'])) continue;
            if ((int)$sr2['PRSEQ'] === $fpseq && $sr2['PRSEL'] === 'Y') {
                $matchFound = true;
                break;
            }
        }
    }

    $portalDetail[$p] = array(
        'rdseqn'   => rtrim($sr['RDSEQN']),
        'fpseq'    => $fpseq,
        'fpid'     => $fpid,
        'fptspt'   => $fptspt,
        'sport_cnt'=> count($sport),
        'sporr'    => $sporr,
        'syurlm'   => $syurlm,
        'match'    => $matchFound,
    );
}

// Step 4: Run the EXACT Menu.php SQL (top-level portals only)
$exactSQL = "SELECT RTRIM(FPPORT) AS FPPORT, RTRIM(FPPAGE) AS FPPAGE, "
          . "       FPSEQ, RTRIM(FPID) AS FPID, RTRIM(FUID) AS FUID "
          . "FROM SGHDSDATA.SYROLD "
          . "     INNER JOIN SGHDSDATA.SYPORT ON FPPORT=RDPORT "
          . "     INNER JOIN SGHDSDATA.SYURLM ON FUID=FPID ";
if ($syporr_cnt > 0) {
    $exactSQL .= "INNER JOIN SGHDSDATA.SYPORR "
              . "    ON RDROLE=PRROLE AND FPPORT=PRPORT "
              . "    AND FPPAGE=PRPAGE AND FPSEQ=PRSEQ ";
}
$exactSQL .= "WHERE (((RDROLE='$role_safe') AND (FPPAGE='' OR FPPAGE=FPPORT)) OR "
           . "       (RDROLE='$role_safe' AND FPPORT='SGDASH' AND FPPAGE='SGDASH')) ";
if ($syporr_cnt > 0) {
    $exactSQL .= "AND PRSEL='Y' ";
}
$exactSQL .= "ORDER BY RDSEQN, RDPORT, FPPAGE, FPSEQ";

$menuRows = qrows($conn, $exactSQL);

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>DiagGetMenu: <?= htmlspecialchars($role) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:12px Arial,sans-serif; background:#f0f2f5; padding:16px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:10px 18px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:14px; font-size:16px; font-weight:bold; }
.hdr span { font-size:11px; opacity:.7; margin-left:14px; }
.mode-W { display:inline-block; background:#c62828; color:#fff;
          padding:2px 10px; border-radius:10px; font-size:12px;
          font-weight:bold; margin-bottom:12px; }
.mode-B { display:inline-block; background:#2e7d32; color:#fff;
          padding:2px 10px; border-radius:10px; font-size:12px;
          font-weight:bold; margin-bottom:12px; }
.sec { font-size:13px; font-weight:bold; color:#2a5a8c; margin:16px 0 6px; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden;
        box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:16px; }
th { background:#2a5a8c; color:#fff; padding:5px 8px;
     text-align:left; font-size:10px; }
td { padding:3px 8px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; vertical-align:top; }
.y   { color:#2e7d32; font-weight:bold; }
.n   { color:#c62828; font-weight:bold; }
.m   { color:#aaa; }
.ok  { background:#e8f5e9; }
.bad { background:#fff3f3; }
form { margin-bottom:12px; display:flex; gap:8px; align-items:center; }
input  { padding:4px 8px; font-size:13px; border:1px solid #ccc;
         border-radius:3px; width:180px; }
button { padding:4px 12px; font-size:13px; background:#2a5a8c; color:#fff;
         border:none; border-radius:3px; cursor:pointer; }
.sql { background:#f5f5f5; border:1px solid #ddd; border-radius:4px;
       padding:8px 12px; font-family:monospace; font-size:10px;
       white-space:pre-wrap; word-break:break-all; margin-bottom:14px; }
</style>
</head>
<body>

<div class="hdr">DiagGetMenu
  <span>SGHDSDATA | <?= date('Y-m-d H:i:s') ?></span>
</div>

<form>
  <label>Role:</label>
  <input name="role" value="<?= htmlspecialchars($role) ?>">
  <button>Diagnose</button>
</form>

<div class="mode-<?= $mode[0] ?>">
  <?= $mode ?> MODE
  &mdash; <?= $syporr_cnt ?> total SYPORR rows for <?= htmlspecialchars($role) ?>
</div>

<!-- ============================================================
     TABLE 1: SYROLD portals with FPSEQ vs PRSEQ breakdown
     ============================================================ -->
<div class="sec">Portal Details: SYPORT.FPSEQ vs SYPORR.PRSEQ
  (<?= count($portalDetail) ?> SYROLD portals)</div>

<table>
  <tr>
    <th>Seq</th>
    <th>Portal</th>
    <th>SYPORT FPID</th>
    <th>FPSEQ</th>
    <th>FPTSPT</th>
    <th>SYURLM?</th>
    <th>SYPORR rows (PRSEQ/PRSEL/PRTSUS)</th>
    <th>PRSEQ=FPSEQ?</th>
    <th>PRSEL=Y?</th>
    <th>Will show?</th>
  </tr>
  <?php foreach ($portalDetail as $p => $d):
      $prseq_match = false;
      $prsel_y     = false;
      $sporr_strs  = array();
      foreach ($d['sporr'] as $sp) {
          if (isset($sp['__error'])) continue;
          $ms = ((int)$sp['PRSEQ'] === $d['fpseq']) ? '=MATCH' : '!=MISS';
          $sporr_strs[] = 'PRSEQ='.$sp['PRSEQ'].' PRSEL='.$sp['PRSEL']
                        . ' ('.$sp['PRTSUS'].') ['.$ms.']';
          if ((int)$sp['PRSEQ'] === $d['fpseq']) $prseq_match = true;
          if ($sp['PRSEL'] === 'Y') $prsel_y = true;
      }
      $will_show = ($mode === 'BYPASS') ? true : ($d['match']);
      $rowCls = $will_show ? 'ok' : 'bad';
      $urlm_ok = ($d['syurlm'] !== null);
  ?>
  <tr class="<?= $rowCls ?>">
    <td><?= htmlspecialchars($d['rdseqn']) ?></td>
    <td><strong><?= htmlspecialchars($p) ?></strong></td>
    <td style="font-size:9px"><?= htmlspecialchars($d['fpid']) ?></td>
    <td><?= $d['fpseq'] !== null ? $d['fpseq'] : '<span class="n">NO SYPORT</span>' ?></td>
    <td class="<?= $d['fptspt']==='Y'?'y':'n' ?>"><?= htmlspecialchars($d['fptspt']===''?'blank':$d['fptspt']) ?></td>
    <td class="<?= $urlm_ok?'y':'n' ?>"><?= $urlm_ok ? 'YES' : 'NO' ?></td>
    <td style="font-size:10px">
      <?php if (empty($d['sporr'])): ?>
        <span class="n">NONE</span>
      <?php else: ?>
        <?= implode('<br>', array_map('htmlspecialchars', $sporr_strs)) ?>
      <?php endif; ?>
    </td>
    <td class="<?= ($mode==='BYPASS')?'m':($prseq_match?'y':'n') ?>">
      <?= ($mode==='BYPASS') ? 'N/A' : ($prseq_match ? 'YES' : 'NO') ?>
    </td>
    <td class="<?= ($mode==='BYPASS')?'m':($prsel_y?'y':'n') ?>">
      <?= ($mode==='BYPASS') ? 'N/A' : ($prsel_y ? 'YES' : 'NO') ?>
    </td>
    <td class="<?= $will_show?'y':'n' ?>"><strong><?= $will_show?'YES':'NO' ?></strong></td>
  </tr>
  <?php endforeach; ?>
</table>

<!-- ============================================================
     TABLE 2: Exact GetMenu SQL result (what actually shows)
     ============================================================ -->
<div class="sec">Exact GetMenu SQL Results
  (<?= isset($menuRows['__error']) ? 'ERROR' : count($menuRows) ?> rows returned)</div>

<?php if (isset($menuRows['__error'])): ?>
<div style="color:#c62828;font-weight:bold;margin-bottom:12px">
  SQL Error: <?= htmlspecialchars($menuRows['__error']) ?>
</div>
<?php else: ?>
<table>
  <tr>
    <th>FPPORT</th>
    <th>FPPAGE</th>
    <th>FPSEQ</th>
    <th>FPID</th>
    <th>FUID (SYURLM)</th>
  </tr>
  <?php foreach ($menuRows as $mr): ?>
  <tr>
    <td><strong><?= htmlspecialchars(rtrim($mr['FPPORT'])) ?></strong></td>
    <td><?= htmlspecialchars(rtrim($mr['FPPAGE'])) ?></td>
    <td><?= htmlspecialchars($mr['FPSEQ']) ?></td>
    <td style="font-size:10px"><?= htmlspecialchars(rtrim($mr['FPID'])) ?></td>
    <td style="font-size:10px"><?= htmlspecialchars(rtrim($mr['FUID'])) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- ============================================================
     SQL used
     ============================================================ -->
<div class="sec">SQL Used for GetMenu</div>
<div class="sql"><?= htmlspecialchars($exactSQL) ?></div>

</body>
</html>
