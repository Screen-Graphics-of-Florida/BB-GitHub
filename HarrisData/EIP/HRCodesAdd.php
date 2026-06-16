<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromType    = (isset($_GET['fromType']))   ? $_GET['fromType']   : "";

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title      = "H/R Code Selection";
$scriptName      = "HRCodesAdd.php";
$scriptVarBase   = "{$genericVarBase}";
$baseURL         = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$nextPrevVar     = "{$scriptVarBase}";
$dspMaxRows      = 999;
$prtMaxRows      = $prtMaxRowsDft;
$popUpWin        = "Y";
$advanceSearch   = "N";
$allowSaveFilter = "N";
$dftOrderBy      = array(array("FLVALU","A","Type"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$browser = getenv("HTTP_USER_AGENT");
$iePos = strpos($browser, "MSIE");

if ($tag == "SELECT") {
	$fromURL = "{$homeURL}{$phpPath}HRCodesMaintain.php{$genericVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A&amp;fromType=" . urlencode($fromType) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME']));
	$fromURL  = str_replace("amp;", "", $fromURL);
	print "\n <script TYPE=\"text/javascript\">";
	print "\n opener.location.href='$fromURL'";
	print "\n opener.focus();";
	print "\n window.close();";
	print "\n </script>";
	exit();
}

if ($formatToPrint != "") {$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if ($sequence == "Type") {
		$orby = array(array("FLVALU","A","Type"));
	} elseif ($sequence == "Description") {
		$orby = array(array("upper(FLDESC)","A","Description"));
	}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

require_once ($docType);
print "\n <html> 	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

require 'stmtSQLClear.php';
$stmtSQL .=  " Select FLTYPE, FLVALU, FLDESC, upper(FLDESC) as FLDESCU ";
$fileSQL .=  " SYFLAG";
$selectSQL .=  " FLTYPE='HRCODETYPE' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td>";
if ($formatToPrint != "Y"){
	print "\n <td class=\"toolbar\">";
	if ($iePos === false) {
		print "\n <a href=\"javascript:self.close()\">$closeImageMed</a>";
	} else {
		print "\n <a href=\"javascript:window.close()\">$closeImageMed</a>";
	}
	require_once 'HelpPage.php';
	print "</td>";
}

print "\n </tr>";
print "\n </table>";
require_once 'ConfMessageDisplay.php';
print $hrTagAttr;

if ($formatToPrint == "") {
	$qsOpt  = "\n <option value=\"FLVALU|null|Type|A|U\" title=\"Type\" SELECTED>Type";
	$qsOpt .= "\n <option value=\"upper(FLDESC)|null|Description|A|U\" title=\"Description\">Description";
	require 'QuickSearchOption.php';
}

print "\n <table $contentTable><tr>";
$returnValue=OrderBy_Sort("FLVALU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Type\" title=\"Sequence By Type\">{$sortPoint}Type</a></th>";
$returnValue=OrderBy_Sort("FLDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\" title=\"Sequence By Description\">{$sortPoint}Description</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	print "\n <td class=\"colalph\">" . trim($row['FLVALU']) . "</td>";
	print "\n <td class=\"colalph\"><a href=\"{$baseURL}&amp;tag=SELECT&amp;fromType=" . urlencode($row['FLVALU']) . "\" title=\"Select Code\">" . trim($row['FLDESC']) . "</a></td>";
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
