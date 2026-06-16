<?php
$saveImageTitle = "Import XML";
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Table";
$scriptName    = "Table.php";
$scriptVarBase  = "{$genericVarBase}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL    = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;startRow=" . urlencode($startRow);
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("DSTBLD","A","Description"));
$programName   = "HSYXXX";
$_SESSION[$fromURL]=$currentURL;

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterSearch.php';
	require_once 'Menu.js';
	require_once 'NoFormValidate.php';
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "PAGE";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Table","srchTable","","operTable","opersel_alph_short","A","10","130");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","10","100");

	$focusField = "srchDesc";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Desc")      {$orby = array(array("upper(DSTBLD)","A","Description"),array("DSTBLN","A","Table"));}
	elseif ($sequence == "Table")     {$orby = array(array("DSTBLN","A","Table"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("upper(DSTBLD)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("DSTBLN", "Table", $_POST['srchTable'], "U", $_POST['operTable'], "A");
	require_once 'WildCardUpdate.php';
}

if ($tag != "EXPORT"){
	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);
	$formName = "Search";

	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
	require_once 'AJAXRequest.js';
	require_once 'CheckEnterSearch.php';
	require_once 'CheckSel.js';
	require_once 'Menu.js';
	require_once 'NoFormValidate.php';
	require_once 'SaveCurrentURL.php';
	require_once 'ShowHideSelCriteria.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "TABLE";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
	print "\n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		print "\n <td class=\"toolbar\">";
		require_once 'XMLFormat.php';
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td>";
	}

	print "\n </tr></table>";
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

require 'stmtSQLClear.php';
$stmtSQL .= " Select DSTBID, DSTBLD, DSTBLN, DSROLE, DSUSER, DSCRTB, DSTSTP";
$fileSQL  = " SYDCST";
$selectSQL .= " DSTBID>0 ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	if ($formatToPrint == ""){
		$qsOpt  = "\n <option value=\"upper(DSTBLD)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
		$qsOpt .= "\n <option value=\"DSTBLN|null|Table|A|U\" title=\"Table\">Table";
		require 'QuickSearchOption.php';
	}
	print "<table $contentTable> <tr>";
	if ($formatToPrint != "Y"){
		print "<th class=\"colhdr\">$optionHeading</th>";
	}
	$returnValue=OrderBy_Sort("DSTBLN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Table\" title=\"Sequence By Table\">{$sortPoint}Table</a></th>";
	$returnValue=OrderBy_Sort("upper(DSTBLD)"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\"  title=\"Sequence By Description, Table\">{$sortPoint}Description</a></th>";
	print "\n </tr>";
}

if ($tag == "EXPORT"){$xmlListName = "TableList"; require_once 'XMLInit.php';}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	if ($tag == "EXPORT"){
		$xmlID  = $xmlDoc->createElement(Page); $xmlRoot->appendChild($xmlID);
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Table"));          $xmlTag->appendChild($xmlDoc->createTextNode($row['DSTBLN']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Description"));    $xmlTag->appendChild($xmlDoc->createTextNode($row['DSTBLD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("UserProfile"));    $xmlTag->appendChild($xmlDoc->createTextNode($row['DSUSER']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CreatedBy"));      $xmlTag->appendChild($xmlDoc->createTextNode($row['DSCRTB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Timestamp"));      $xmlTag->appendChild($xmlDoc->createTextNode($row['DSTSTP']));

	} else {
		$maintainVar  = "{$scriptVarBase}&amp;tblID=" . urlencode($row[DSTBID]) . "&amp;tableName=" . urlencode(trim($row[DSTBLN])) . "&amp;tableDesc=" . urlencode(trim($row[DSTBLD]));
		require 'SetRowClass.php';
		$confirmDesc = Format_Confirm_Desc("$row[DSTBLD]", "$row[DSTBLN]", "", "", "", "");
		print "\n <tr class=\"$rowClass\">";
		if ($formatToPrint != "Y"){
			print "\n <td class=\"opticon\">";
			// print "\n <a href=\"{$homeURL}{$phpPath}Conversion_Support_Maintain.php{$maintainVar}\">$fixPrepImage</a>";
			print "\n <a href=\"{$homeURL}{$phpPath}Page.php{$maintainVar}\">$pageIDImageSml</a>";
			print "\n <a href=\"{$homeURL}{$phpPath}TableImportXML.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$xmlImport</a>";
			print "\n </td>";
		}
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=" . urlencode($row[DSTBID]) . "\" title=\"Display a list of " .$row[DSTBLD]."\">$row[DSTBLN]</a></td>";
		print "\n <td class=\"colalph\">$row[DSTBLD]</td>";
		print "\n </tr>";
	}
	$startRow ++;
	$rowCount ++;
}

require_once 'XMLExport.php';

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>