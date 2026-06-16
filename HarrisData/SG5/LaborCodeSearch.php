<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';


$docName	=	(isset($_GET['docName']))		?	$_GET['docName']		:	null;
$fldName	=	(isset($_GET['fldName']))		?	$_GET['fldName']		:	null;
$fldDesc	=	(isset($_GET['fldDesc']))		?	$_GET['fldDesc']		:	null;
$moreInfo	=	(isset($_GET['moreInfo']))		?	$_GET['moreInfo']		:	"";
$moreCode	=	(isset($_GET['moreCode']))		?	$_GET['moreCode']		:	"";
$touchScreen=	(isset($_GET['touchScreen']))	?	$_GET['touchScreen']	:	"";

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Labor Code Search";
$scriptName     = "LaborCodeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;touchScreen=" . urlencode(trim($touchScreen));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("LRADESU","A","Description"),array("LRLCOD","A","Code"));

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
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Code","srchCode","","operCode","opersel_alph_short","N","2","2");
	Build_AdvSrch_Entry("Description(Short)","srchDescS","","operDescS","opersel_alph_short","A","16","16");
	
	$focusField = "srchCode";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Code")  {$orby = array(array("LRLCOD","A","Code"));}
	elseif ($sequence == "DescS")    {$orby = array(array("LRADES","A","DescS"),array("LRLCOD","A","Code"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("LRLCOD", "Code", $_POST['srchCode'], "U", $_POST['operCode'], "A");
	$returnValue=Build_WildCard("upper(LRADES)", "Description (Short)", $_POST['srchDescS'], "U", $_POST['operDescS'], "A");
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
if ($touchScreen == "Y") {require_once 'KeyboardFunctionsTS.js';}

print "\n function selectCode(code,descS){ ";
print "\n window.opener.document.$docName.$fldName.value = code; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = descS;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = descS;}";
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

$uv_BillingLocationName ="LOLOC#";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select SIMLRC.*,upper(LRADES) as LRADESU, ";
$stmtSQL .= " upper(LRDES1) as LRDES1U, upper(LRDES2) as LRDES2U ";
$fileSQL .= " SIMLRC ";
if ($moreInfo=="Y")          {$selectSQL .= " LRLCOD='$moreCode' ";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL="LRLCOD<>'' ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"LRLCOD|null|Code|A|\" title=\"Labor Code\">Labor Code";
	$qsOpt .= "\n <option value=\"upper(LRADES)|null|DescS|A|U\" title=\"Description(Short)\" SELECTED>Description(Short)";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";

	$returnValue=OrderBy_Sort("LRLCOD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Code\" title=\"Sequence By Code\">{$sortPoint}Code</a></th>";
	$returnValue=OrderBy_Sort("LRADESU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DescS\" title=\"Sequence By Desc(Short), Code\">{$sortPoint}Description(Short)</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_DescS=Format_Quote($row['LRADES']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colcode\">$row[LRLCOD]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectCode('" . trim($row['LRLCOD']) . "','" . trim($F_DescS) . "')\" title=\"Select Labor Code\">$F_DescS</a></td> ";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCode=" . urlencode(trim($row['LRLCOD'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$moreInfoSelect = "href=\"javascript:selectCode('" . trim($row['LRLCOD']) . "','" . trim($F_Code) . "')\" title=\"Select Code\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Labor Entry Code",$row[LRLCOD],"","A");
	Build_DspFld("Description (Short)",$row[LRADES],"","A");
	Build_DspFld("Description Line 1",$row[LRDES1],"","A");
	Build_DspFld("Description Line 2",$row[LRDES2],"","A");
	$F_AcctSub=Format_Acct("$row[LRMAJ]", "$row[LRSUB]", "Y");
	print "\n     <tr><td class=\"dsphdr\">Account</td> ";
	print "\n         <td class=\"dspalph\">$F_AcctSub</td></tr> ";
	

	print "\n </table> ";
	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardPrint.php';
}

print "$searchhrTagAttr";
require_once 'Copyright.php';
if ($touchScreen == "Y") {require_once 'KeyboardTS.htm';}
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
