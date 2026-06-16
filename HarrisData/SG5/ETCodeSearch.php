<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$fldType  = $_GET['fldType'];
$moreInfo = $_GET['moreInfo'];
$moreCode = $_GET['moreCode'];
$touchScreen  = $_GET['touchScreen'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$scriptName     = "ETCodeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;fldType=" . urlencode(trim($fldType)) . "&amp;touchScreen=" . urlencode(trim($touchScreen));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("EVCODE","A","Code"));

$fieldDesc=RetValue("CTTYPE='$fldType'", "ETCTYP", "CTDESC");
$page_title = trim($fieldDesc). " Code Search";
require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterSearch.php';
	require_once 'NoFormValidate.php';
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Code","srchCode","","operCode","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","20");

	$focusField = "srchCode";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if     ($sequence == "Code")        {$orby = array(array("EVCODE","A","Code"));}
	elseif ($sequence == "Description") {$orby = array(array("EVDESCU","A","Description"),array("EVCODE","A","Code"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("EVCODE", "Code", $_POST['srchCode'], "U", $_POST['operCode'], "A");
	$returnValue=Build_WildCard("upper(EVDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectCode(Code, codeDesc){ ";
print "\n window.opener.document.$docName.$fldName.value = Code; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = codeDesc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = codeDesc;}";
if ($touchScreen != "Y") {print "\n window.opener.document.$docName.$fldName.focus(); ";}
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
print $searchhrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDEVNT.*, upper(EVDESC) as EVDESCU " ;
$fileSQL .= " HDEVNT ";
$selectSQL .= " EVTYPE='$fldType' ";
if ($moreInfo=="Y")   {$selectSQL .= " and EVCODE='$moreCode' ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"EVCODE|null|Code|A|U\" title=\"Code\">Code";
	$qsOpt .= "\n <option value=\"upper(EVDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("EVCODE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Code\" title=\"Sequence By Code\">{$sortPoint}Code</a></th>";
	$returnValue=OrderBy_Sort("EVDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Code\">{$sortPoint}Description</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Desc=Format_Quote($row['EVDESC']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\">$row[EVCODE]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectCode('" . trim($row['EVCODE']) . "','" . trim($F_Desc) . "')\" title=\"Select Desc\">$F_Desc</a></td> ";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCode=" . urlencode(trim($row['EVCODE'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a>";
		print "\n </td>";
		print "\n </tr>";
		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['EVDESC']);
	$moreInfoSelect = "href=\"javascript:selectCode('" . trim($row['EVCODE']) . "','" . trim($F_Desc) . "')\" title=\"Select Code\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	if ($fldType=='A') {
		Build_DspFld("Code",$row['EVCODE'],"","A");
		Build_DspFld("Description",$row['EVDESC'],"","A");
		Build_DspFld("Attendance Points",$row['EVATPT'],"","N");
	}  elseif  ($fldType=='E')  {
		Build_DspFld("Code",$row['EVCODE'],"","A");
		Build_DspFld("Description",$row['EVDESC'],"","A");
		Build_DspFld("Worked",$row['EVWRKD'],"","A");
		Build_DspFld("Print on Schedule Exception Report",$row['EVPOER'],"","A");
		Build_DspFld("Feed Schedule Exception to Payroll",$row['EVFDPR'],"","A");
		Build_DspFld("Pay Code",$row['EVPYCD'],"","A");
	}  elseif  ($fldType=='I')  {
		Build_DspFld("Code",$row['EVCODE'],"","A");
		Build_DspFld("Description",$row['EVDESC'],"","A");
		Build_DspFld("Pay Code",$row['EVPYCD'],"","A");
	}  elseif  ($fldType=='L')  {
		Build_DspFld("Code",$row['EVCODE'],"","A");
		Build_DspFld("Description",$row['EVDESC'],"","A");
	}  elseif  ($fldType=='X')  {
		Build_DspFld("Code",$row['EVCODE'],"","A");
		Build_DspFld("Description",$row['EVDESC'],"","A");
		Build_DspFld("Pay Code",$row['EVPYCD'],"","A");
		Build_DspFld("Error Level",$row['EVLEVL'],"","A");
	}

	print "\n </table> ";
	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardprint.php';
}

print "$searchhrTagAttr";
require_once 'Copyright.php';
if ($touchScreen == "Y") {require_once 'KeyboardTS.htm';}
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
