<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Currency Rate Type Search";
$scriptName     = "CurrencyRateTypeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("RYDESCU","A","Description"),array("RYTYPE","A","Rate Type"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	require_once 'NoFormValidate.php';
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Rate Type","srchRateType","","operRateType","opersel_alph_short","A","15","1");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","15","15");

	$focusField = "srchRateType";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "PmtType")      {$orby = array(array("RYTYPE","A","Rate Type"));}
	elseif ($sequence == "Description")  {$orby = array(array("RYDESCU","A","Description"),array("RYTYPE","A","Rate Type"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("RYTYPE ", "Rate Type", $_POST['srchRateType'], "U", $_POST['operRateType'], "A");
	$returnValue=Build_WildCard("upper(RYDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectRateType(rateType,rateTypeDesc){ ";
print "\n window.opener.document.$docName.$fldName.value = rateType; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = rateTypeDesc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = rateTypeDesc;}";
print "\n window.opener.document.$docName.$fldName.focus(); ";
print "\n window.close(); ";
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
$stmtSQL .= " Select RYTYPE, RYDESC, upper(RYDESC) as RYDESCU ";
$fileSQL .= " HDRTYP ";
if ($wildCardSearch!="") {$selectSQL="RYTYPE<>' ' ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"RYTYPE|null|Rate Type|A|U\" title=\"Rate Type\">Rate Type";
$qsOpt .= "\n <option value=\"upper(RYDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("RYTYPE "); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtType\" title=\"Sequence By Rate Type\">{$sortPoint}Rate Type</a></th>";
$returnValue=OrderBy_Sort("RYDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Rate Type\">{$sortPoint}Description</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Desc=Format_Quote($row['RYDESC']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectRateType('" . trim($row['RYTYPE']) . "','" . trim($F_Desc) . "')\" title=\"Select Rate Type\">$row[RYTYPE]</a></td> ";
	print "\n     <td class=\"colalph\">$row[RYDESC]</td>";
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
