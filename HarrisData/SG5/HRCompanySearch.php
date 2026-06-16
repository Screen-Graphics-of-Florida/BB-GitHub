<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName   = $_GET['docName'];
$fldCo     = $_GET['fldCo'];
$fldDesc   = $_GET['fldDesc'];
$moreInfo  = $_GET['moreInfo'];
$moreCode  = $_GET['moreCode'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "H/R Company Search";
$scriptName     = "HRCompanySearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldCo=" . urlencode(trim($fldCo)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("CFNAMEU","A","Name"), array("CFCOMP","N","Company"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchComp, 2, 0) ";
	print "\n     )";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Company","srchComp","","operComp","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","30","30");

	$focusField = "srchComp";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "company")     {$orby = array(array("CFCOMP","N","company"));}
	elseif ($sequence == "name") {$orby = array(array("CFNAMEU","A","name"),array("CFCOMP","A","company"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("CFCOMP", "Company", $_POST['srchComp'], "", $_POST['operComp'], "N");
	$returnValue=Build_WildCard("CFNAMEU", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectComp(comp, compDesc){ ";
print "\n window.opener.document.$docName.$fldCo.value = comp; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = compDesc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = compDesc;}";
print "\n window.opener.document.$docName.$fldCo.focus(); ";
print "\n window.close(); ";
print "\n } ";

require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
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

$uv_CompanyName ="CFCOMP";
require 'UserView.php';
require 'stmtSQLClear.php';
$stmtSQL .= " Select HRCOFC.* " ;
$fileSQL .= " HRCOFC ";
$selectSQL="CFFACL=0 ";

if ($moreInfo=="Y")         {$selectSQL .= " and CFCOMP='$moreCode' ";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL.=" and CFCOMP<>0 ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );


if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"CFCOMP|null|Company Number|N|\" title=\"Company Number\">Company Number";
	$qsOpt .= "\n <option value=\"CFNAMEU|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("CFCOMP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=comp\" title=\"Sequence By Company Number\">{$sortPoint}Company</a></th>";
	$returnValue=OrderBy_Sort("CFNAMEU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=name\" title=\"Sequence By Name\">{$sortPoint}Name</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Desc=Format_Quote($row['CFNAME']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[CFCOMP]</td>";

		print "\n     <td class=\"colalph\"><a href=\"javascript:selectComp('" . trim($row['CFCOMP']) . "','" . trim($F_Desc) . "')\" title=\"Select Company\">$F_Desc</a></td> ";
		print "\n 	  <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCode=" . urlencode(trim($row['CFCOMP'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a>";

		print "\n </td>";
		print "\n </tr>";
		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['CFNAME']);
	$moreInfoSelect = "href=\"javascript:selectComp('" . trim($row['CFCOMP']) . "','" . trim($F_Desc) . "')\" title=\"Select Company\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	Build_DspFld("Company",$row[CFCOMP],"","N");
	Build_DspFld("Name",$row[CFNAME],"","A");
	Build_DspFld("Address Line 1",$row[CFADR1],"","A");
	Build_DspFld("Address Line 2",$row[CFADR2],"","A");
	Build_DspFld("City",$row[CFCITY],"","A");
	$fldDesc=RetValue("STID='$row[CFSTID]'", "HDSTID", "STDESC");
	Build_DspFld("State",$row[CFSTID],"$fldDesc","A");
	Build_DspFld("Zip Code",$row[CFZIP],"","A");
	$phone=EditPhoneNumber($row[CFPHON]);
	Build_DspFld("Phone Number",$phone,"","A");
	print "\n </table> ";

	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardprint.php';
}

print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
