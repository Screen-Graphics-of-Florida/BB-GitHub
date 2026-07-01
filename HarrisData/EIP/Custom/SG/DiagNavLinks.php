<?php
require_once dirname(__FILE__) . '/../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

print "\n<html><head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n</head>";
require_once 'Banner.php';
require_once dirname(__FILE__) . '/SgReportNav.php';
?>
<table <?php echo $baseTable; ?>>
<tr valign="top">
<td class="content">
<style>
table[summary="banner"]{display:none!important;}
body{font:12px Arial,sans-serif;}
h2{background:#1E3A6E;color:#fff;padding:8px 14px;border-radius:4px;margin:0 0 12px;font-size:13px;}
.dtbl{border-collapse:collapse;width:100%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.1);border-radius:4px;margin-top:10px;}
.dtbl th{background:#374151;color:#fff;padding:6px 10px;text-align:left;font-size:11px;}
.dtbl td{padding:5px 10px;font-size:11px;border-bottom:1px solid #eee;font-family:monospace;vertical-align:top;word-break:break-all;}
.dtbl tr:hover td{background:#f0f4ff;}
.ok{color:#166534;font-weight:bold;}
.bad{color:#991b1b;font-weight:bold;}
.void{color:#92400e;font-weight:bold;}
p.meta{font-size:11px;color:#555;margin:0 0 8px;}
</style>
<h2>Nav Link Diagnostic</h2>
<p class="meta">
  <b>Role:</b> <?php echo htmlspecialchars($_sgnRole); ?> &nbsp;|&nbsp;
  <b>homeURL:</b> <?php echo htmlspecialchars($_sgnHome); ?> &nbsp;|&nbsp;
  <b>navBv:</b> <?php echo htmlspecialchars($_sgnNavBv); ?> &nbsp;|&nbsp;
  <b>eID:</b> <?php echo htmlspecialchars($_sgnEid); ?> &nbsp;|&nbsp;
  <b>portal:</b> <?php echo htmlspecialchars($_sgnPort); ?>
</p>
<?php
$_dc = $i5Connect->getConnection();
$rs  = str_replace("'", "''", $_sgnRole);

$pc = 0;
$s  = @db2_exec($_dc, "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$rs'");
if ($s) { $r = db2_fetch_row($s); if ($r) $pc = (int)db2_result($s, 0); }

$sql = "SELECT RTRIM(FPPORT) AS FPPORT,"
     . "RTRIM(FPDESC) AS FPDESC,RTRIM(FUDESC) AS FUDESC,RTRIM(FUURL) AS FUURL "
     . "FROM SGHDSDATA.SYROLD "
     . "INNER JOIN SGHDSDATA.SYPORT ON FPPORT=RDPORT "
     . "INNER JOIN SGHDSDATA.SYURLM ON FUID=FPID ";
if ($pc > 0) $sql .= "INNER JOIN SGHDSDATA.SYPORR ON RDROLE=PRROLE AND FPPORT=PRPORT AND FPPAGE=PRPAGE AND FPSEQ=PRSEQ ";
$sql .= "WHERE RDROLE='$rs' AND RTRIM(FPPAGE)='' ";
if ($pc > 0) $sql .= "AND PRSEL='Y' ";
$sql .= "ORDER BY RDSEQN,FPSEQ";

$stmt = @db2_exec($_dc, $sql);
$rows = [];
if ($stmt) { while ($row = db2_fetch_assoc($stmt)) $rows[] = $row; }
?>
<p class="meta">Whitelist rows: <b><?php echo $pc; ?></b> &nbsp;|&nbsp; Nav items returned: <b><?php echo count($rows); ?></b></p>
<table class="dtbl">
<tr><th>#</th><th>Label</th><th>FPPORT</th><th>Raw FUURL</th><th>Built URL</th><th>Test</th></tr>
<?php
foreach ($rows as $i => $row) {
    $raw   = rtrim($row['FUURL']);
    $label = (rtrim($row['FPDESC']) !== '') ? rtrim($row['FPDESC']) : rtrim($row['FUDESC']);
    $built = sgn_buildURL(
        $raw, rtrim($row['FPPORT']),
        $_sgnHome, $_sgnPhp, $_sgnCgi, $_sgnHelp,
        $_sgnNavBv, $_sgnEid, $_sgnPrfh, $_sgnNews, $_sgnBrws
    );
    $void = ($built === 'javascript:void(0);');
    echo '<tr>';
    echo '<td>' . ($i+1) . '</td>';
    echo '<td>' . htmlspecialchars($label) . '</td>';
    echo '<td>' . htmlspecialchars(rtrim($row['FPPORT'])) . '</td>';
    echo '<td class="' . ($raw===''?'bad':'') . '">' . ($raw===''?'(empty)':htmlspecialchars($raw)) . '</td>';
    echo '<td class="' . ($void?'void':'ok') . '">' . htmlspecialchars($built) . '</td>';
    echo '<td>' . (!$void ? '<a href="'.htmlspecialchars($built,ENT_QUOTES).'" target="_blank">Test</a>' : '—') . '</td>';
    echo '</tr>';
}
if (empty($rows)) echo '<tr><td colspan="6" class="bad">No rows returned from query.</td></tr>';
?>
</table>
</td></tr></table>
