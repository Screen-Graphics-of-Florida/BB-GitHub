<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Reversal Reason Search";
$scriptName     = "ARReverseReasonSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("RVDESCU","A","Description"),array("RVRSCD","A","Reason"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CalendarInclude.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchRating, 2, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Reason","srchReason","","operReason","opersel_alph_short","A","10","4");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","10","30");
	Build_AdvSrch_Entry("Rating","srchRating","","operRating","opersel_num_short","N","10","2");

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Reason")          {$orby = array(array("RVRSCD","A","Reason"));}
	elseif ($sequence == "Description")     {$orby = array(array("RVDESCU","A","Description"),array("RVRSCD","A","Reason"));}
	elseif ($sequence == "Rating")          {$orby = array(array("RVRATE","A","Rating"),array("RVRSCD","A","Reason"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("RVRSCD", "Reason", $_POST['srchReason'], "U", $_POST['operReason'], "A");
	$returnValue=Build_WildCard("RVDESCU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("RVRATE", "Rating", $_POST['srchRating'], "U", $_POST['operRating'], "N");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectReason(reverseReason, desc){ ";
print "\n   window.opener.document.$docName.$fldName.value = reverseReason; ";
print "\n   if      (window.opener.document.$docName.$fldDesc)          {window.opener.document.$docName.$fldDesc.value = desc;} ";
print "\n   else if (window.opener.document.getElementById('$fldDesc')) {window.opener.document.getElementById('$fldDesc').innerHTML = desc;}";
print "\n   window.opener.document.$docName.$fldName.focus(); ";
print "\n   window.close(); ";
print "\n } ";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .= " Select RVRSCD, RVDESC, RVRATE, RVDESCU ";
$fileSQL .= " ARRVRS ";
if ($wildCardSearch!="") {$selectSQL="RVRSCD=RVRSCD ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt = "";
$qsOpt .= "\n <option value=\"RVDESCU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
$qsOpt .= "\n <option value=\"RVRSCD|null|Reason|A|U\" title=\"Reason\">Reason";
$qsOpt .= "\n <option value=\"RVRATE|null|Rating|N|\" title=\"Rating\">Rating";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";

$returnValue=OrderBy_Sort("RVDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Reason\">{$sortPoint}Description</a></th>";
$returnValue=OrderBy_Sort("RVRSCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Reason\" title=\"Sequence By Reason, Bank\">{$sortPoint}Reason</a></th>";
$returnValue=OrderBy_Sort("RVRATE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Rating\" title=\"Sequence By Rating, Reason\">{$sortPoint}Rating</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_RVRSCD=Format_Quote($row['RVRSCD']);
	$F_RVDESC=Format_Quote($row['RVDESC']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectReason('" . trim($F_RVRSCD) . "','" . trim($F_RVDESC) . "')\" title=\"Select Reason\">$row[RVDESC]</a></td> ";
	print "\n     <td class=\"colalph\">$row[RVRSCD]</td>";
	print "\n     <td class=\"colalph\">$row[RVRATE]</td>";
	print "\n </tr>";

	$startRow ++;
	$rowCount ++;
}
if ($rowCount == 0){require 'NoRecordsFound.php';}
print "</table>";

require_once 'PageBottom.php';
require_once 'WildCardPrint.php';

print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
