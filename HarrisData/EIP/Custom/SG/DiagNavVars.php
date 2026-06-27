<?php
// DiagNavVars.php — run once to find session/role variables for SgReportNav.
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagNavVars.php?baseVar=BaseConfiguration.icl&eID=YOUR_EID&portal=SGINQ
require_once dirname(__FILE__) . '/../GetURLParm.php';

$conn = $i5Connect->getConnection();

// 1. Which PHP session variables exist?
$sessVars = array();
if (isset($_SESSION)) {
    foreach ($_SESSION as $k => $v) {
        $sessVars[$k] = is_scalar($v) ? (string)$v : gettype($v);
    }
}

// 2. Which framework globals are set?
$fwVars = array();
foreach (array('profileHandle','profileName','roleName','role','userRole',
               'dataBaseID','title','eID','baseVar','portal') as $n) {
    if (isset($$n)) $fwVars[$n] = (string)$$n;
}

// 3. Sample FUURL from SYURLM for first 5 portal-type rows
$fuurlRows = array();
$s = @db2_exec($conn,
    "SELECT RTRIM(FUID) AS FUID, RTRIM(FUURL) AS FUURL
     FROM SGHDSDATA.SYURLM
     WHERE RTRIM(FUTSPT)='Y' AND RTRIM(FUURL)<>''
     ORDER BY FUID
     FETCH FIRST 10 ROWS ONLY");
if ($s) { while ($r = db2_fetch_assoc($s)) $fuurlRows[] = $r; }

// 4. Try each candidate role variable and count matching SYROLD rows
$candidates = array('profileHandle','profileName','roleName','role','userRole');
$roleTries  = array();
foreach ($candidates as $c) {
    if (!empty($$c)) {
        $safe = str_replace("'","''", strtoupper(trim((string)$$c)));
        $cnt  = 0;
        $sc   = @db2_exec($conn, "SELECT COUNT(*) FROM SGHDSDATA.SYROLD WHERE RDROLE='$safe'");
        if ($sc) { $r2 = db2_fetch_row($sc); if ($r2) $cnt = (int)db2_result($sc,0); }
        $roleTries[$c] = array('value' => (string)$$c, 'syrold_count' => $cnt);
    }
}
// Also try eID as a role
if (!empty($eID)) {
    $safe = str_replace("'","''", strtoupper(trim($eID)));
    $cnt  = 0;
    $sc   = @db2_exec($conn, "SELECT COUNT(*) FROM SGHDSDATA.SYROLD WHERE RDROLE='$safe'");
    if ($sc) { $r2 = db2_fetch_row($sc); if ($r2) $cnt = (int)db2_result($sc,0); }
    $roleTries['eID'] = array('value' => $eID, 'syrold_count' => $cnt);
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>DiagNavVars</title>
<style>
body{font:12px Arial,sans-serif;background:#f0f2f5;padding:16px;}
h2{background:#2a5a8c;color:#fff;padding:7px 14px;border-radius:4px;font-size:13px;margin:14px 0 6px;}
table{border-collapse:collapse;width:100%;background:#fff;border-radius:4px;margin-bottom:10px;
      box-shadow:0 1px 4px rgba(0,0,0,.08);}
th{background:#2a5a8c;color:#fff;padding:4px 10px;text-align:left;font-size:11px;}
td{padding:3px 10px;font-size:11px;font-family:monospace;border-bottom:1px solid #f0f0f0;}
.hit{color:#2e7d32;font-weight:bold;} .miss{color:#aaa;}
</style></head><body>
<h2>1. PHP Session Variables</h2>
<table><tr><th>Key</th><th>Value</th></tr>
<?php if (empty($sessVars)): ?>
<tr><td colspan="2" class="miss">(no session variables)</td></tr>
<?php else: foreach ($sessVars as $k=>$v): ?>
<tr><td><?=htmlspecialchars($k)?></td><td><?=htmlspecialchars($v)?></td></tr>
<?php endforeach; endif; ?>
</table>

<h2>2. Framework Global Variables</h2>
<table><tr><th>Variable</th><th>Value</th></tr>
<?php if (empty($fwVars)): ?>
<tr><td colspan="2" class="miss">(none of the checked vars are set)</td></tr>
<?php else: foreach ($fwVars as $k=>$v): ?>
<tr><td>$<?=htmlspecialchars($k)?></td><td><?=htmlspecialchars($v)?></td></tr>
<?php endforeach; endif; ?>
</table>

<h2>3. Role Candidate Test (which variable matches SYROLD?)</h2>
<table><tr><th>Variable</th><th>Value</th><th>SYROLD rows with RDROLE=value</th></tr>
<?php foreach ($roleTries as $k=>$d): ?>
<tr>
  <td>$<?=htmlspecialchars($k)?></td>
  <td><?=htmlspecialchars($d['value'])?></td>
  <td class="<?=$d['syrold_count']>0?'hit':'miss'?>"><?=$d['syrold_count']?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>4. Sample SYURLM FUURL values (FUTSPT=Y)</h2>
<table><tr><th>FUID</th><th>FUURL</th></tr>
<?php foreach ($fuurlRows as $r): ?>
<tr><td><?=htmlspecialchars($r['FUID'])?></td>
    <td style="word-break:break-all"><?=htmlspecialchars($r['FUURL'])?></td></tr>
<?php endforeach; ?>
</table>
</body></html>
