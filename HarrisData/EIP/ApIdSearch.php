<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName            = $_GET['docName'];
$fldName            = $_GET['fldName'];
$fldDesc            = $_GET['fldDesc'];
$moreInfo           = $_GET['moreInfo'];
$apIdCode           = $_GET['apIdCode'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Application ID Search";
$scriptName     = "ApIdSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("AIAPDSU","A","Description"),array("AIAPID","A","Application ID"));

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
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchMenuOpt, 2, 0) ";
	print "\n    ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","20","20");
	Build_AdvSrch_Entry("Application ID","srchApId","","operApId","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Menu Option Of HHDM00","srchMenuOpt","","operMenuOpt","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Program To Call","srchProgram","","operProgram","opersel_alph_short","A","10","10");

	$focusField = "srchDesc";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "ApIdCode")    {$orby = array(array("AIAPID","A","Application ID"));}
	elseif ($sequence == "Description") {$orby = array(array("AIAPDSU","A","Description"));}
	elseif ($sequence == "MenuOpt")     {$orby = array(array("AIMOPT","A","Menu Option Of HHDM00"));}
	elseif ($sequence == "Program")     {$orby = array(array("AIPGM","A","Program To Call"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard ("upper(AIAPDS)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard ("AIAPID", "Application ID", $_POST['srchApId'], "U", $_POST['operApId'], "A");
	$returnValue=Build_WildCard ("AIMOPT", "Menu Option Of HHDM00", $_POST['srchMenuOpt'], "", $_POST['operMenuOpt'], "N");
	$returnValue=Build_WildCard ("AIPGM ", "Program To Call", $_POST['srchProgram'], "U", $_POST['operProgram'], "A");

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

print "\n function selectApIdCode(apIdCode, apIdDesc){ ";
print "\n   window.opener.document.$docName.$fldName.value = apIdCode; ";
print "\n   if (window.opener.document.$docName.$fldDesc) ";
print "\n      {window.opener.document.$docName.$fldDesc.value = apIdDesc;} ";
print "\n   else if (window.opener.document.getElementById('$fldDesc'))";
print "\n           {window.opener.document.getElementById('$fldDesc').innerHTML = apIdDesc;}";
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
$stmtSQL .= " Select AIAPID, AIAPDS, AIMOPT, AIPGM ";
$stmtSQL .= " ,upper(AIAPDS) as AIAPDSU ";
$fileSQL .= " SYAPID ";
if ($moreInfo=="Y")          {$selectSQL .= " AIAPID='$apIdCode' ";}
elseif ($wildCardSearch!="") {$selectSQL="AIAPID=AIAPID ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"upper(AIAPDS)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"AIAPID|null|Application ID|A|U\" title=\"Application ID\">Application ID";
	$qsOpt .= "\n <option value=\"AIMOPT|null|Menu Option Of HHDM00|N|\" title=\"Menu Option Of HHDM00\">Menu Option Of HHDM00";
	$qsOpt .= "\n <option value=\"AIPGM|null|Program To Call|A|U\" title=\"Program To Call\">Program To Call";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";

	$returnValue=OrderBy_Sort("AIAPID"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ApIdCode\" title=\"Sequence By Application ID\">{$sortPoint}Application ID</a></th>";
	$returnValue=OrderBy_Sort("AIAPDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("AIMOPT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=MenuOpt\" title=\"Sequence By Menu Option Of HHDM00\">{$sortPoint}Menu Option Of HHDM00</a></th>";
	$returnValue=OrderBy_Sort("AIPGM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Program\" title=\"Sequence By Program To Call\">{$sortPoint}Program To Call</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_AIAPID=Format_Quote($row['AIAPID']);
		$F_AIAPDS=Format_Quote($row['AIAPDS']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colcode\">$row[AIAPID]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectApIdCode('" . trim($F_AIAPID) . "','" . trim($F_AIAPDS) . "')\" title=\"Select Application ID\">$row[AIAPDS]</a></td> ";
		print "\n     <td class=\"colalph\">$row[AIMOPT]</td>";
		print "\n     <td class=\"colalph\">$row[AIPGM]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;apIdCode=" . urlencode(trim($row['AIAPID'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_AIAPID=Format_Quote($row['AIAPID']);
	$F_AIAPDS=Format_Quote($row['AIAPDS']);
	$moreInfoSelect = "href=\"javascript:selectApIdCode('" . trim($F_AIAPID) . "','" . trim($F_AIAPDS) . "')\" title=\"Select Application ID\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Application ID",$row[AIAPID],"","A");
	Build_DspFld("Description",$row[AIAPDS],"","A");
	Build_DspFld("Menu Option Of HHDM00",$row[AIMOPT],"","N");
	Build_DspFld("Program To Call",$row[AIPGM],"","A");
	print "\n </table> ";
	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardPrint.php';
}

print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
