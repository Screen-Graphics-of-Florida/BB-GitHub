<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldForm  = $_GET['fldForm'];
$fldBox   = $_GET['fldBox'];
$fldDesc  = $_GET['fldDesc'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "IRS Forms/Box Number Search";
$scriptName     = "IRSFormsBoxNumberSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldForm=" . urlencode(trim($fldForm)) . "&amp;fldBox=" . urlencode(trim($fldBox)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("BXDESCU","A","Description"),array("BXFOR","A","Forms Number"),array("BXBOX","A","Box Number"));

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

	Build_AdvSrch_Entry("Forms Number","srchFormsNumber","","operFormsNumber","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Box Number","srchBoxNumber","","operBoxNumber","opersel_alph_short","A","3","3");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","25","25");

	$focusField = "srchFormsNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if ($sequence == "FormsNumber")    {$orby = array(array("BXFOR","A","Forms Number"),array("BXBOX","A","Box Number"));}
	elseif ($sequence == "BoxNumber")  {$orby = array(array("BXBOX","A","Box Number"),array("BXFOR","A","Forms Number"));}
	elseif ($sequence == "Description") {$orby = array(array("BXDESCU","A","Description"),array("BXFOR","A","Forms Number"),array("BXBOX","A",""));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("BXFOR#", "Forms Number", $_POST['srchFormsNumber'], "U", $_POST['operFormsNumber'], "A");
	$returnValue=Build_WildCard("BXBOX#", "Box Number", $_POST['srchBoxNumber'], "U", $_POST['operBoxNumber'], "A");
	$returnValue=Build_WildCard("upper(BXDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
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

print "\n function selectFormsBox(form,box,desc){ ";
print "\n window.opener.document.$docName.$fldForm.value = form; ";
print "\n window.opener.document.$docName.$fldBox.value = box; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = desc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = desc;}";
print "\n window.opener.document.$docName.$fldForm.focus(); ";
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
$stmtSQL .= " Select BXFOR#, BXBOX#, BXDESC, BXFOR# as BXFOR, BXBOX# as BXBOX, upper(BXDESC) as BXDESCU ";
$fileSQL .= " APFBOX ";
if ($wildCardSearch!="") {$selectSQL="BXFOR#<>' ' ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"BXFOR#|null|Forms Number|A|U\" title=\"Forms Number\">Forms Number";
$qsOpt .= "\n <option value=\"BXBOX#|null|Box Number|A|U\" title=\"Box Number\">Box Number";
$qsOpt .= "\n <option value=\"upper(BXDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("BXFOR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=FormsNumber\" title=\"Sequence By Forms Number, Box Number\">{$sortPoint}Forms Number</a></th>";
$returnValue=OrderBy_Sort("BXBOX"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BoxNumber\" title=\"Sequence By Box Number, Forms Number\">{$sortPoint}Box Number</a></th>";
$returnValue=OrderBy_Sort("BXDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Forms Number, Box Number\">{$sortPoint}Description</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Desc=Format_Quote($row['BXDESC']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colcode\">$row[BXFOR]</td>";
	print "\n     <td class=\"colcode\">$row[BXBOX]</td>";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectFormsBox('" . trim($row['BXFOR']) . "','" . trim($row['BXBOX']) . "','" . trim($F_Desc) . "')\" title=\"Select Forms/Box Number\">$F_Desc</a></td> ";
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
