<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromTblID    = (isset($_GET['fromTblID']))   ? $_GET['fromTblID']   : 0;
$fromPagID    = (isset($_GET['fromPagID']))   ? $_GET['fromPagID']   : 0;
$tableName    = (isset($_GET['tableName']))   ? $_GET['tableName']   : "";
$tableDesc    = (isset($_GET['tableDesc']))   ? ($_GET['tableDesc']) : "";
$pageDesc     = (isset($_GET['pageDesc']))    ? ($_GET['pageDesc'])  : "";

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title      = "Filter Checkbox";
$scriptName      = "FilterCheckbox.php";
$scriptVarBase   = "{$genericVarBase}&amp;fromTblID=" . urlencode($fromTblID) . "&amp;fromPagID=" . urlencode($fromPagID) . "&amp;tableName=" . urlencode($tableName) . "&amp;tableDesc=" . urlencode($tableDesc) . "&amp;pageDesc=" . urlencode($pageDesc);
$baseURL         = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$nextPrevVar     = "{$scriptVarBase}";
$dspMaxRows      = $dspMaxRowsDft;
$prtMaxRows      = $prtMaxRowsDft;
$advanceSearch   = "N";
$allowSaveFilter = "N";
$dftOrderBy      = array(array("TVDSEQ","A","Display Sequence"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != "") {$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if     ($sequence == "dspSeq")  {$orby = array(array("TVDSEQ","A","Display Sequence"));}
	elseif ($sequence == "label")   {$orby = array(array("TVLABL","A","Label"));}
	elseif ($sequence == "group")   {$orby = array(array("TVGRPN","A","Group"));}
	elseif ($sequence == "default") {$orby = array(array("TVDFTV","A","Default"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

require_once ($docType);
print "\n <html> 	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'Menu.js';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "FILTERCHECKBOX";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y"){
	// Program Option Security
	$hsyxxx_OPT=pgmOptSecurity($profileHandle, $dataBaseID, "hsyxxx");

	print "\n <td class=\"toolbar\">";
	if ($hsyxxx_OPT['sec_01'] == "Y"){print "\n <a href=\"{$homeURL}{$phpPath}FilterCheckboxMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a>";}

	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	print "</td>";
}

print "\n </tr></table>";
print "<table $contentTable>";
$customTable = '';
if ($fromTblID >= 5000) {
  $customTable = 'Custom';
}Format_Header_URL("Table", $tableDesc, $tableName, "{$homeURL}{$cGIPath}{$customTable}Table.d2w/REPORT{$altVarBase}&intHD=Y&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']));
if ($fromPagID) {
	Format_Header_URL("Page", $pageDesc, "", "{$homeURL}{$cGIPath}Page.d2w/REPORT{$altVarBase}&amp;tableName=" . urlencode($tableName). "&amp;tableDesc=" . urlencode($tableDesc) . "&amp;tblID=" . urlencode($fromTblID) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']));
}
print "\n </table>";
require_once 'ConfMessageDisplay.php';
print $hrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .=  " Select TVCBID, TVDSEQ, TVGRPN, TVLABL, TVDFTV, TVFILD ";
$fileSQL .=  "  SYTBFC ";
$selectSQL .= " TVTBID=$fromTblID and TVPGID=$fromPagID ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt  = "\n <option value=\"TVLABLU|null|Label|A|U\" title=\"Label\" SELECTED>Label";
	$qsOpt .= "\n <option value=\"TVDSEQ|null|Display Sequence|N|\" title=\"Display Sequence\">Display Sequence";
	require 'QuickSearchOption.php';
}

print "<table $contentTable><tr>";
if ($formatToPrint != "Y"){
	print "<th class=\"colhdr\">$optionHeading</th>";
}
$returnValue=OrderBy_Sort("TVLABLU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=label\" title=\"Sequence By Label\">{$sortPoint}Label</a></th>";
$returnValue=OrderBy_Sort("TVDSEQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=dspSeq\"  title=\"Sequence By Display Sequence\">{$sortPoint}Display<br>Sequence</a></th>";
$returnValue=OrderBy_Sort("TVGRPNU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=group\" title=\"Sequence By Group\">{$sortPoint}Group</a></th>";
$returnValue=OrderBy_Sort("TVDFTV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=default\"    title=\"Sequence By Default\">{$sortPoint}Default</a></th>";
print "\n <th class=\"colhdr\">Search Criteria</th>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	$confirmDesc = Format_Confirm_Desc(trim($row['TVLABL']), $row['TVDSEQ'], "", "", "", "");
	$maintainVar = "{$scriptVarBase}&amp;checkboxID=" . urlencode($row['TVCBID']);
	$F_default   = ($row['TVDFTV'] == '1') ? $checkImage : "&nbsp;";

	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\" valign=\"top\">";
	if ($formatToPrint != "Y" && ($hsyxxx_OPT['sec_02'] == "Y" || $hsyxxx_OPT['sec_03'] == "Y" || $hsyxxx_OPT['sec_04'] == "Y")){
		print "\n <td class=\"opticon\">";
		if  ($hsyxxx_OPT['sec_02'] == "Y" || $hsyxxx_OPT['sec_03'] == "Y"){
			print "\n <a href=\"{$homeURL}{$phpPath}FilterCheckboxMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageSml</a>";
		}
		if ($hsyxxx_OPT['sec_04'] == "Y"){
			print "\n <a href=\"{$homeURL}{$phpPath}FilterCheckboxMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=Z\">$copyImageSml</a>";
		}
		if ($hsyxxx_OPT['sec_03'] == "Y"){
			print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}FilterCheckboxMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
		}
		print "</td>";
	}
	print "\n <td class=\"colalph\">" . trim($row['TVLABL']) . "</td>";
	print "\n <td class=\"colnmbr\">{$row['TVDSEQ']}</td>";
	print "\n <td class=\"colalph\">" . trim($row['TVGRPN']) . "</td>";
	print "\n <td class=\"colcode\">$F_default</td>";
	print "\n <td class=\"colalph\">{$row['TVFILD']}</td>";
	print "\n </tr>";

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0) {require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';
print "</td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";

?>