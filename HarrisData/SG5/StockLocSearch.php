<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$forWhs   = $_GET['forWhs'];
$fldStkr  = $_GET['fldStkr'];
$fldAisle = $_GET['fldAisle'];
$fldLoc   = $_GET['fldLoc'];
$fldStkID = $_GET['fldStkID'];
$touchScreen  = $_GET['touchScreen'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Stock Location Search";
$scriptName     = "StockLocSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;forWhs=" . urlencode(trim($forWhs)) . "&amp;fldStkr=" . urlencode(trim($fldStkr)) . "&amp;fldAisle=" . urlencode(trim($fldAisle)) . "&amp;fldLoc=" . urlencode(trim($fldLoc)) . "&amp;fldStkID=" . urlencode(trim($fldStkID)) . "&amp;touchScreen=" . urlencode(trim($touchScreen));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
if ($forWhs > 0) {
	$dftOrderBy     = array(array("SLSTKR","A","Stockroom"),array("SLSTKR","A","Aisle"),array("SLSLOC","A","Location"));
} else {$dftOrderBy     = array(array("SLWHS","A","Warehouse"),array("SLSTKR","A","Stockroom"),array("SLSTKR","A","Aisle"),array("SLSLOC","A","Location")); }
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
	if ($forWhs == "") {print "\n if (editNum(document.Search.srchWhs, 3, 0) && ";}
	else               {print "\n if (";}
	print "\n     editNum(document.Search.srchPriority, 1, 0)) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Warehouse","srchWhs","","operWhs","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("Stockroom","srchStkr","","operStkr","opersel_alph_short","A","3","3");
	Build_AdvSrch_Entry("Aisle","srchAisle","","operAisle","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Location","srchLoc","","srchLoc","opersel_alph_short","A","8","8");
	Build_AdvSrch_Entry("Priority","srchPriority","","operPriority","opersel_num_short","N","1","1");

	$focusField = "srchWhs";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if     ($sequence == "Whs")      {$orby = array(array("SLWHS","A","Warehouse"),array("SLSTKR","A","Stockroom"),array("SLSTKR","A","Aisle"),array("SLSLOC","A","Location"));}
	elseif ($sequence == "StockLoc") {$orby = array(array("SLSTKR","A","Stockroom"),array("SLSTKR","A","Aisle"),array("SLSLOC","A","Location"),array("SLWHS","A","Warehouse"));}
	elseif ($sequence == "Priority") {$orby = array(array("SLRATE","A","Priority"),array("SLSTKR","A","Stockroom"),array("SLSTKR","A","Aisle"),array("SLSLOC","A","Location"));}

	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("SLWHS", "Warehouse", $_POST['srchWhs'], "", $_POST['operWhs'], "N");
	$returnValue=Build_WildCard("SLSTKR", "Stockroom", $_POST['srchStkr'], "U", $_POST['operStkr'], "A");
	$returnValue=Build_WildCard("SLAILE", "Aisle", $_POST['srchAisle'], "U", $_POST['operAisle'], "A");
	$returnValue=Build_WildCard("SLSLOC", "Location", $_POST['srchLoc'], "U", $_POST['operLoc'], "A");
	$returnValue=Build_WildCard("SLRATE", "Priority", $_POST['srchPriority'], "", $_POST['operPriority'], "N");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectStockLoc(stkr, aisle, loc, stkid){ ";
print "\n window.opener.document.$docName.$fldStkr.value = stkr; ";
print "\n window.opener.document.$docName.$fldAisle.value = aisle; ";
print "\n window.opener.document.$docName.$fldLoc.value = loc; ";
print "\n window.opener.document.$docName.$fldStkID.value = stkid; ";
if ($touchScreen != "Y") {print "\n window.opener.document.$docName.$fldStkr.focus(); ";}
print "\n window.close(); ";
print "\n } ";

require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
if ($touchScreen == "Y") {require_once 'KeyboardFunctionsTS.js';}
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
if ($touchScreen == "Y") {$displayCloseIcon = "Y";}
require_once 'PageTitleInclude.php';
if ($forWhs > 0) {
	$fieldDesc=RetValue("WHWHS=$forWhs", "HDWHSM", "WHWHNM");
	print "\n <table $baseTable>";
	Format_Header("Warehouse", $fieldDesc, $forWhs);
	print "</table>";
}

print $searchhrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .= " Select * " ;
$fileSQL .= " HDSTLC ";
if ($forWhs>0) {$selectSQL .= " SLWHS=$forWhs ";}
elseif ($wildCardSearch!="") {$selectSQL="SLWHS>0 ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt = "";
if ($forWhs == "") {$qsOpt .= "\n <option value=\"SLWHS|null|Warehouse|N|\" title=\"Warehouse\">Warehouse";}
$qsOpt .= "\n <option value=\"SLSTKR|null|Stockroom|A|U\" title=\"Stockroom\" SELECTED>Stockroom";
$qsOpt .= "\n <option value=\"SLAILE|null|Aisle|A|U\" title=\"Aisle\">Aisle";
$qsOpt .= "\n <option value=\"SLSLOC|null|Location|A|U\" title=\"Location\">Location";
$qsOpt .= "\n <option value=\"SLRATE|null|Priority|N|\" title=\"Priority\">Priority";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
if ($forWhs == "") {
	$returnValue=OrderBy_Sort("SLWHS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Whs\" title=\"Sequence By Warehouse, Stock Location\">{$sortPoint}Whs</a></th>";
}
$returnValue=OrderBy_Sort("SLSTKR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"  colspan=\"3\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StockLoc\" title=\"Sequence By Stock Location, Warehouse\">{$sortPoint}Stock Location</a></th>";
$returnValue=OrderBy_Sort("SLRATE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Priority\" title=\"Sequence By Priority, Stock Location\">{$sortPoint}Priority</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";

	if ($forWhs == "") {print "\n     <td class=\"colnmbr\">$row[SLWHS]</td>";}
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectStockLoc('" . trim($row['SLSTKR']) . "','" . trim($row['SLAILE']) . "','" . trim($row['SLSLOC']) . "','" . trim($row['SLSTID']) . "')\" title=\"Select Stock Location\">$row[SLSTKR]</a></td> ";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectStockLoc('" . trim($row['SLSTKR']) . "','" . trim($row['SLAILE']) . "','" . trim($row['SLSLOC']) . "','" . trim($row['SLSTID']) . "')\" title=\"Select Stock Location\">$row[SLAILE]</a></td> ";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectStockLoc('" . trim($row['SLSTKR']) . "','" . trim($row['SLAILE']) . "','" . trim($row['SLSLOC']) . "','" . trim($row['SLSTID']) . "')\" title=\"Select Stock Location\">$row[SLSLOC]</a></td> ";
	print "\n     <td class=\"colcode\">$row[SLRATE]</td>";

	print "\n </tr>";
	$startRow ++;
	$rowCount ++;
}
if ($rowCount == 0){require 'NoRecordsFound.php';}
print "</table>";

require_once 'PageBottom.php';
require_once 'WildCardprint.php';

print "$searchhrTagAttr";
require_once 'Copyright.php';
if ($touchScreen == "Y") {require_once 'KeyboardTS.htm';}
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
