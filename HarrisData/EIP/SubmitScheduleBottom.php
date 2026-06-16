<?php
print "\n <table $contentTable> ";
print "\n <tr><td class=\"toolbar\"> ";
if ($backHome != $scriptName && $backHome != "" && $backHome != "@@backHome") {
	if (strpos(strtoupper($backHome), ".D2W") !== false) {print "<a href=\"{$homeURL}{$cGIPath}{$backHome}{$altVarBase}\" title=\"Back Home\">{$portalHome}</a>";}
	else {print "<a href=\"{$homeURL}{$phpPath}{$backHome}{$scriptVarBase}\" title=\"Back Home\">{$portalHome}</a>";}
}

print "\n <a href=\"javascript:check(document.Chg)\">$sbmSchdImage</a> ";
if ($submitNoSelection=="") {
	print "\n <a href=\"javascript:document.Chg.saveSelection.value='Y'; check(document.Chg)\">$sbmSchdSaveImage</a> ";
	if ($reportCount){print "\n <a href=\"{$homeURL}{$cGIPath}ReportSelection.d2w/REPORT{$altVarBase}&amp;reportSelType=" . urlencode(trim($reportSelType)) . "&amp;reportSelD2W=" . urlencode(trim($scriptName)) . "&amp;reportSelUser=" . urlencode(trim($userProfile)) . "&amp;rtvSelection=Y&amp;maintenanceCode=C\" onclick=\"{$searchWinVar}\">$sbmSchdRtvImage</a>";}
}
if ($allowScheduleJob != "N") {print "\n <a href=\"javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg)\">$sbmSchdParmImage</a> ";}
if ($submitNoReset=="") {print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;resetSelectionFlag=Y&amp;timeStamp=". urlencode($_SERVER['REQUEST_TIME']) . "\">$sbmSchdResetImage</a> ";}

require_once 'HelpPage.php';
print "\n </td></tr></table> ";
if ($_SESSION['saveSel'] == "Y") {
	$_SESSION['saveSel']="";
	print "\n  <meta http-equiv=\"refresh\" content=\"0; URL=javascript:NewWindow('{$homeURL}{$cGIPath}ReportSelection.d2w/REPORT{$altVarBase}&amp;reportSelType=" . urlencode(trim($reportSelType)) . "&amp;reportSelD2W=" . urlencode(trim($scriptName)) . "&amp;reportDesc=" . urlencode(trim($page_title)) . "&amp;reportSelUser=" . urlencode(trim($userProfile)) . "&amp;rtvSelection=" . urlencode(trim($rtvSelection)) . "&amp;maintenanceCode=C','report_win','{$orderEntryWinPctH}','{$searchWinPctH}','{$searchWinPctW}','{$searchWinSB}','{$searchWinRZ}','{$searchWinTB}','{$searchWinMB}','{$searchWinST}');\">";
}
?>