<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldCo  = $_GET['fldCo'];
$fldFac   = $_GET['fldFac'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$moreCo = $_GET['moreCo'];
$moreFac  = $_GET['moreFac'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Company/Facility Search";
$scriptName     = "CoFacSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldCo=" . urlencode(trim($fldCo)). "&amp;fldFac=" . urlencode(trim($fldFac)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;forceChange=" . urlencode(trim($forceChange));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("CFCFNMU","A","Name"),array("CFCO#","A","Company"),array("CFFAC#","A",""));

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
	print "\n if (editNum(document.Search.srchCo, 2, 0) && ";
	print "\n     editNum(document.Search.srchFac, 4, 0) && ";
	print "\n     editdate(document.Search.srchDateEst) && ";
	print "\n     editdate(document.Search.srchDateDea) && ";
	print "\n     editNum(document.Search.srchBudget, 3, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Company","srchCo","","operCo","opersel_num_short","N","4","4");
	Build_AdvSrch_Entry("Facility","srchFac","","operFac","opersel_num_short","N","4","4");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Date Established","srchDateEst","","operDateEst","opersel_num_short","D","4","6");
	Build_AdvSrch_Entry("Date Deactivated","srchDateDea","","operDateDea","opersel_num_short","D","4","6");
	Build_AdvSrch_Entry("Payer TIN","srchPayer","","operPayer","opersel_num_short","N","4","9");
	Build_AdvSrch_Entry("Default Budget Plan","srchBudget","","operBudget","opersel_num_short","N","4","3");
	if ($HDMCRL > 0) {Build_AdvSrch_Entry("Currency Type","srchCur","","operCur","opersel_alph_short","A","4","4");}

	$focusField = "srchCo";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Company")      {$orby = array(array("CFCO#","A","Company"),array("CFFAC#","A",""));}
	elseif ($sequence == "Name")         {$orby = array(array("CFCFNMU","A","Name"),array("CFCO#","A","Co/Fac"),array("CFFAC#","A",""));}
	elseif ($sequence == "Established")  {$orby = array(array("CFDTES","A","Date Established"),array("CFCO#","A","Co/Fac"),array("CFFAC#","A",""));}
	elseif ($sequence == "Deactivated")  {$orby = array(array("CFDTDE","A","Date Deactivated"),array("CFCO#","A","Co/Fac"),array("CFFAC#","A",""));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("CFCO# ", "Company", $_POST['srchCo'], "", $_POST['operCo'], "N");
	$returnValue=Build_WildCard("CFFAC#", "Facility", $_POST['srchFac'], "", $_POST['operFac'], "N");
	$returnValue=Build_WildCard("CFCFNMU", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("CFDTES", "Date Established", $_POST['srchDateEst'], "", $_POST['operDateEst'], "D");
	$returnValue=Build_WildCard("CFDTDE", "Date Deactivated", $_POST['srchDateDea'], "", $_POST['operDateDea'], "D");
	$returnValue=Build_WildCard("CFTIN#", "Payer TIN", $_POST['srchPayer'], "", $_POST['operPayer'], "N");
	$returnValue=Build_WildCard("CFDBGT", "Budget Plan", $_POST['srchBudget'], "", $_POST['operBudget'], "N");
	$returnValue=Build_WildCard("CFCURT", "Currency Type", $_POST['srchCur'], "U", $_POST['operCur'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";

require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectCoFac(company,facility,companyName){ ";
print "\n window.opener.document.$docName.$fldCo.value = company; ";
print "\n window.opener.document.$docName.$fldFac.value = facility; ";
print "\n if      (window.opener.document.$docName.$fldDesc)          {window.opener.document.$docName.$fldDesc.value = companyName;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc')) {window.opener.document.getElementById('$fldDesc').innerHTML = companyName;}";
print "\n window.opener.document.$docName.$fldCo.focus(); ";
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

$uv_CompanyName ="CFCO#";
$uv_FacilityName ="CFFAC#";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " HDCFAC ";
if ($moreInfo=="Y")          {$selectSQL .= " CFCO#=$moreCo and CFFAC#=$moreFac ";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL="CFCO#<>0 ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"CFCO#|null|Company Number|N|\" title=\"Company Number\">Company Number";
	$qsOpt .= "\n <option value=\"CFFAC#|null|Facility Number|N|\" title=\"Facility Number\">Facility Number";
	$qsOpt .= "\n <option value=\"CFCFNMU|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"CFDTES|DATE|Date Established|D|\" title=\"Date Established\">Date Established";
	$qsOpt .= "\n <option value=\"CFDTDE|DATE|Date Deactivated|D|\" title=\"Date Deactivated\">Date Deactivated";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("CFCO#"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Company\"      title=\"Sequence By Co/Fac\">{$sortPoint}Co/Fac</a></th>";
	$returnValue=OrderBy_Sort("CFCFNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\"  title=\"Sequence By Name, Co/Fac\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("CFDTES"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Established\"  title=\"Sequence By Date Established, Co/Fac\">{$sortPoint}Date<br>Established</a></th>";
	$returnValue=OrderBy_Sort("CFDTDE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Deactivated\"  title=\"Sequence By Date Deactivated, Co/Fac\">{$sortPoint}Date<br>Deactivated</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_CoFac=Format_CoFac($row['CFCO#'],$row['CFFAC#'],"N");
		$F_CFDTES=Format_Date($row['CFDTES'],"D");
		$F_CFDTDE=Format_Date($row['CFDTDE'],"D");
		$F_Desc=Format_Quote($row['CFCFNM']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$F_CoFac</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectCoFac('" . trim($row['CFCO#']) . "','" . trim($row['CFFAC#']) . "','" . trim($F_Desc) . "')\" title=\"Select Company/Facility\">$F_Desc</a></td> ";
		print "\n     <td class=\"coldate\">$F_CFDTES</td>";
		print "\n     <td class=\"coldate\">$F_CFDTDE</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCo=" . urlencode(trim($row['CFCO#'])) . "&amp;moreFac=" . urlencode(trim($row['CFFAC#'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['CFCFNM']);
	$moreInfoSelect = "href=\"javascript:selectCoFac('" . trim($row['CFCO#']) . "','" . trim($row['CFFAC#']) . "','" . trim($F_Desc) . "')\" title=\"Select Company/Facility\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	$F_CoFac=Format_CoFac($row['CFCO#'],$row['CFFAC#'],"N");
	Build_DspFld("Company/Facility",$F_CoFac,"","A");
	Build_DspFld("Name",$row[CFCFNM],"","A");
	$F_AcctSub=Format_Acct($row['CFDTAC'],$row['CFDTSB'],"N");
	Build_DspFld("Inter-Company Due To",$F_AcctSub,"","A");
	$F_AcctSub=Format_Acct($row['CFDFAC'],$row['CFDFSB'],"N");
	Build_DspFld("Inter-Company Due From",$F_AcctSub,"","A");
	$F_AcctSub=Format_Acct($row['CFAPA'],$row['CFAPS'],"N");
	Build_DspFld("A/P Account",$F_AcctSub,"","A");
	$F_AcctSub=Format_Acct($row['CFIAAC'],$row['CFIASB'],"N");
	Build_DspFld("Inter-Co Bal Allocations",$F_AcctSub,"","A");
	$F_AcctSub=Format_Acct($row['CFICAC'],$row['CFICSB'],"N");
	Build_DspFld("Inter-Co Bal Current",$F_AcctSub,"","A");
	$F_CFDTES=Format_Date($row['CFDTES'],"H");
	Build_DspFld("Date Established",$F_CFDTES,"","A");
	$F_CFDTDE=Format_Date($row['CFDTDE'],"H");
	Build_DspFld("Date Deactivated",$F_CFDTDE,"","A");
	if ($HDMCRL > 0) {
		Build_DspFld("Currency Type",$row[CFCURT],"","A");
		$F_CoFac=Format_CoFac($row['CFTRCO'],$row['CFTRFC'],"N");
		Build_DspFld("Translated Company/Facility",$F_CoFac,"","A");
	}

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
