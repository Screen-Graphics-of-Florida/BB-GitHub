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

$page_title     = "Vendor 1099 Code Search";
$scriptName     = "Vendor1099Search.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("PTTPDSU","A","Description"),array("PTPTCD","A","1099 Code"));

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

	Build_AdvSrch_Entry("1099 Code","srch1099Code","","oper1099Code","opersel_alph_short","A","10","2");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","10","25");
	Build_AdvSrch_Entry("IRS Forms Number","srchIRSFormsNumber","","operIRSFormsNumber","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Box Number","srchBoxNumber","","operBoxNumber","opersel_alph_short","A","10","3");

	$focusField = "srchDesc";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "1099Code")       {$orby = array(array("PTPTCD","A","1099 Code"));}
	elseif ($sequence == "Description")    {$orby = array(array("PTTPDSU","A","Description"),array("PTPTCD","A","1099 Code"));}
	elseif ($sequence == "IRSFormsNumber") {$orby = array(array("PTFOR","A","IRS Forms Number"));}
	elseif ($sequence == "BoxNumber")      {$orby = array(array("PTBOX","A","Box Number"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("PTPTCD", "1099 Code", $_POST['srch1099Code'], "U", $_POST['oper1099Code'], "A");
	$returnValue=Build_WildCard("upper(PTTPDS)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("PTFOR#", "IRS Forms Number", $_POST['srchIRSFormsNumber'], "U", $_POST['operIRSFormsNumber'], "A");
	$returnValue=Build_WildCard("PTBOX#", "Box Number", $_POST['srchBoxNumber'], "U", $_POST['operBoxNumber'], "A");
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

print "\n function select1099Code(code,desc){ ";
print "\n window.opener.document.$docName.$fldName.value = code; ";
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
$stmtSQL .= " Select PTPTCD, PTTPDS, PTFOR#, PTBOX#, upper(PTTPDS) as PTTPDSU, PTFOR# as PTFOR, PTBOX# as PTBOX ";
$fileSQL .= " APP109 ";
if ($wildCardSearch!="") {$selectSQL="PTPTCD<>' ' ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"PTPTCD|null|1099 Code|A|U\" title=\"1099 Code\">1099 Code";
$qsOpt  = "\n <option value=\"upper(PTTPDS)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
$qsOpt .= "\n <option value=\"PTFOR#|null|IRS Forms Number|A|U\" title=\"IRS Forms Number\">IRS Forms Number";
$qsOpt .= "\n <option value=\"PTBOX#|null|Box Number|A|U\" title=\"Box Number\">Box Number";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("PTPTCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=1099Code\" title=\"Sequence By 1099 Code\">{$sortPoint}1099 Code</a></th>";
$returnValue=OrderBy_Sort("PTTPDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, 1099 Code\">{$sortPoint}Description</a></th>";
$returnValue=OrderBy_Sort("PTFOR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=IRSFormsNumber\" title=\"Sequence By IRS Forms Number\">{$sortPoint}IRS Forms Number</a></th>";
$returnValue=OrderBy_Sort("PTBOX"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BoxNumber\" title=\"Sequence By Box Number\">{$sortPoint}Box Number</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Desc=Format_Quote($row['PTTPDS']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colcode\">$row[PTPTCD]</td>";
	print "\n     <td class=\"colalph\"><a href=\"javascript:select1099Code('" . trim($row['PTPTCD']) . "','" . trim($F_Desc) . "')\" title=\"Select 1099 Code\">$F_Desc</a></td> ";
	print "\n     <td class=\"colalph\">$row[PTFOR]</td>";
	print "\n     <td class=\"colcode\">$row[PTBOX]</td>";
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
