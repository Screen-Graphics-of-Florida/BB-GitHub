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
	if ($allowSaveFilter != "N") {
		$reportCount=RetValue("LFSCRNU<>' ' and LFSCRNU='" . strtoupper($scriptName). "'", "SYLFLT", "count(*)");
		//if ($reportCount) {print "\n <a onClick=\"saveCurrentURL();\" href=\"{$homeURL}{$phpPath}FilterSelection.php{$scriptVarBase}amp;tag=REPORT&amp;fromTblID=" . urlencode($tblID) . "&amp;fromPagID=" . urlencode($pagID) . "&amp;fromScript=" . urlencode($scriptName) . "&amp;pageHeading1=" . urlencode($page_title) . "&amp;sylMaxSeq=" . urlencode($sylMaxSeq) . " \" onclick=\"{$searchWinVar}\">$sbmSchdRtvImage</a>";}	
		if ($reportCount) {print "\n <a href=\"{$homeURL}{$phpPath}FilterSelection.php{$scriptVarBase}amp;tag=REPORT&amp;fromTblID=" . urlencode($tblID) . "&amp;fromPagID=" . urlencode($pagID) . "&amp;fromScript=" . urlencode($scriptName) . "&amp;pageHeading1=" . urlencode($page_title) . "&amp;sylMaxSeq=" . urlencode($sylMaxSeq) . " \" onclick=\" saveFilterURL();{$selectionWinVar}\">$sbmSchdRtvImage</a>";}	
	
	}
}
if (strtoupper($scriptName) != "SUBMITJOB.PHP"){
	$timeStamp = time();
	print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}amp;tag=REPORT&amp;resetSelectionFlag=Y&amp;timeStamp={$timeStamp}\">$sbmSchdResetImage</a>";
}
require_once 'HelpPage.php';
print "</td></tr></table>";
?>