<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$roleFld  = $_GET['roleFld'];
$descFld  = $_GET['descFld'];
$moreInfo = $_GET['moreInfo'];
$moreRole = $_GET['moreRole'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Role Search";
$scriptName     = "RoleSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;roleFld=" . urlencode(trim($roleFld)) . "&amp;descFld=" . urlencode(trim($descFld));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("RMDESCU","A","Description"),array("RMROLE","A","Role"));

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
	require_once 'NoFormValidate.php';
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Role","srchRole","","operRole","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","10","30");

	$focusField = "srchRole";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Role")         {$orby = array(array("RMROLE","A","Role"));}
	elseif ($sequence == "Description")  {$orby = array(array("RMDESCU","A","Description"),array("RMROLE","A","Role"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("RMROLE ", "Role", $_POST['srchRole'], "U", $_POST['operRole'], "A");
	$returnValue=Build_WildCard("RMDESCU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
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

print "\n function selectRole(role,desc){ ";
print "\n window.opener.document.$docName.$roleFld.value = role; ";
print "\n if (window.opener.document.$docName.$descFld)               {window.opener.document.$docName.$descFld.value = desc;} ";
print "\n else if (window.opener.document.getElementById('$descFld')) {window.opener.document.getElementById('$descFld').innerHTML = desc;}";
print "\n window.opener.document.$docName.$roleFld.focus(); ";
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
$stmtSQL .= " Select * ";
$fileSQL .= " SYROLM ";
if ($moreInfo=="Y")          {$selectSQL .= " RMROLE='$moreRole' ";}
elseif ($wildCardSearch!="") {$selectSQL="RMROLE<>' ' ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"RMDESCU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"RMROLE|null|Role|A|U\" title=\"Role\">Role";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("RMDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\"  title=\"Sequence By Description, Role\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("RMROLE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Role\"      title=\"Sequence By Role\">{$sortPoint}Role</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Desc=Format_Quote($row['RMDESC']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectRole('" . trim($row['RMROLE']) . "','" . trim($F_Desc) . "')\" title=\"Select Role\">$F_Desc</a></td> ";
		print "\n     <td class=\"colalph\">$row[RMROLE]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreRole=" . urlencode(trim($row['RMROLE'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['RMDESC']);
	$moreInfoSelect = "href=\"javascript:selectRole('" . trim($row['RMROLE']) . "','" . trim($F_Desc) . "')\" title=\"Select Role\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Role",$row[RMROLE],"","A");
	Build_DspFld("Description",$row[RMDESC],"","A");

	require 'stmtSQLClear.php';
	$stmtSQL .= " Select SYROLD.*, coalesce(FUDESC,' ') as FUDESC ";
	$fileSQL .= " SYROLD inner join SYPORT on FPPORT=RDPORT and FPPAGE=' ' left join SYURLM on FUID=FPID";
	$selectSQL .= " RDROLE='$moreRole' ";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By RDROLE,RDSEQN ";
	$dspMaxRows = 999;
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	print "\n <tr><td>&nbsp;</td><td> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Portals</legend> ";
	print "\n <table $contentTable>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\">$row[FUDESC]</td>";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	print "\n </table>";
	print "\n </fieldset> ";
	print "\n </td></tr> ";

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
