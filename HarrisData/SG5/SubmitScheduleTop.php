<?php
print "\n <table $contentTable> ";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td> ";
print "\n     <td class=\"toolbar\">";

if ($backHome != $scriptName && $backHome != "" && $backHome != "@@backHome") {
	if (strpos(strtoupper($backHome), ".D2W") !== false) {print "<a href=\"{$homeURL}{$cGIPath}{$backHome}{$altVarBase}\" title=\"Back Home\">{$portalHome}</a>";}
	else {print "<a href=\"{$homeURL}{$phpPath}{$backHome}{$scriptVarBase}\" title=\"Back Home\">{$portalHome}</a>";}
}
print "\n <a href=\"javascript:check(document.Chg)\">$sbmSchdImage</a>";
if ($submitNoSelection == ""){
	print "\n <a href=\"javascript:document.Chg.saveSelection.value='Y'; check(document.Chg)\">$sbmSchdSaveImage</a>";
	$reportCount=RetValue("DRD2WN<>' ' and DRD2WN='" . strtoupper($scriptName). "'", "SYD2WR", "count(*)");
	if ($reportCount){print "\n <a href=\"{$homeURL}{$cGIPath}ReportSelection.d2w/REPORT{$altVarBase}&amp;reportSelType=" . urlencode(trim($reportSelType)) . "&amp;reportSelD2W=" . urlencode(trim($scriptName)) . "&amp;reportSelUser=" . urlencode(trim($userProfile)) . "&amp;rtvSelection=Y&amp;maintenanceCode=C\" onclick=\"{$searchWinVar}\">$sbmSchdRtvImage</a>";}
}
if ($allowScheduleJob != "N"){print "\n <a href=\"javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg)\">$sbmSchdParmImage</a>";}
if ($submitNoReset=="") {print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;resetSelectionFlag=Y&amp;timeStamp=". urlencode($_SERVER['REQUEST_TIME']) . "\">$sbmSchdResetImage</a> ";}

require_once 'HelpPage.php';
print "</td></tr></table>";
?>