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

$page_title     = "Currency Type Search";
$scriptName     = "CurrencyTypeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("CYDESCU","A","Description"),array("CYTYPE","A","Type"));

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

	Build_AdvSrch_Entry("Type","srchType","","operType","opersel_alph_short","A","3","3");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");

	$focusField = "srchType";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Type")      {$orby = array(array("CYTYPE","A","Type"),array("CYDESCU","A","Description"));}
	elseif ($sequence == "Description")  {$orby = array(array("CYDESCU","A","Description"),array("CYTYPE","A","Type"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("CYTYPE ", "Type", $_POST['srchType'], "U", $_POST['operType'], "A");
	$returnValue=Build_WildCard("upper(CYDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
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

print "\n function selectType(type,desc){ ";
print "\n window.opener.document.$docName.$fldName.value = type; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = desc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = desc;}";
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
$stmtSQL .= " Select CYTYPE, CYDESC, upper(CYDESC) as CYDESCU ";
$fileSQL .= " HDCTYP ";
if ($wildCardSearch!="") {$selectSQL="CYTYPE<>' ' ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"CYTYPE|null|Type|A|U\" title=\"Type\">Type";
$qsOpt .= "\n <option value=\"upper(CYDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("CYTYPE "); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Type\" title=\"Sequence By Type, Description\">{$sortPoint}Type</a></th>";
$returnValue=OrderBy_Sort("CYDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Type\">{$sortPoint}Description</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Desc=Format_Quote($row['CYDESC']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colcode\">$row[CYTYPE]</td>";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectType('" . trim($row['CYTYPE']) . "','" . trim($F_Desc) . "')\" title=\"Select Type\">$F_Desc</a></td> ";
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
