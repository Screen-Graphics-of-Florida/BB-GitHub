<?php

print "\n <table $contentTable> ";
print "\n <tr><td class=\"toolbar\"> ";
if ($backHome != $scriptName && $backHome != "" && $backHome != "@@backHome") {
	if (strpos(strtoupper($backHome), ".D2W") !== false) {print "<a href=\"{$homeURL}{$cGIPath}{$backHome}{$altVarBase}\" title=\"Back Home\">{$portalHome}</a>";}
	else {print "<a href=\"{$homeURL}{$phpPath}{$backHome}{$scriptVarBase}\" title=\"Back Home\">{$portalHome}</a>";}
}

print "\n <a href=\"javascript:check(document.Chg)\">$sbmSchdImage</a> ";

print "\n <a href=\"javascript:document.Chg.saveSelection.value='Y'; check(document.Chg)\">$sbmSchdSaveImage</a> ";
if ($reportCount) {print "\n <a href=\"{$homeURL}{$phpPath}FilterSelection.php{$genericVarBase}&amp;tag=REPORT&amp;fromTblID=" . urlencode($tblID) . "&amp;fromPagID=" . urlencode($pagID) . "&amp;fromScript=" . urlencode($scriptName) . "&amp;pageHeading1=" . urlencode($page_title) . "\" onclick=\" saveFilterURL();{$selectionWinVar}\">$sbmSchdRtvImage</a>";}
	
if ($allowScheduleJob != "N") {print "\n <a href=\"javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg)\">$sbmSchdParmImage</a> ";}

print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;resetSelectionFlag=Y&amp;timeStamp=". urlencode($_SERVER['REQUEST_TIME']) . "\">$sbmSchdResetImage</a> ";
require_once 'HelpPage.php';
print "\n </td></tr></table> ";
if ($_SESSION['saveSel'] == "Y") {
	$_SESSION['saveSel']="";
	print "\n  <meta http-equiv=\"refresh\" content=\"0; URL=javascript:NewWindow('{$homeURL}{$phpPath}FilterSelection.php{$genericVarBase}&amp;tag=REPORT&amp;fromTblID=" . urlencode($tblID) . "&amp;fromPagID=" . urlencode($pagID) . "&amp;fromScript=" . urlencode($scriptName) . "&amp;pageHeading1=" . urlencode($page_title) . "\" onclick=\" saveFilterURL());\">";
}
?>