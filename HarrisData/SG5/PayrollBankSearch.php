<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$moreBank = $_GET['moreBank'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Payroll Bank Search";
$scriptName     = "PayrollBankSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("BABKNO","A","Number"),array("BABKNMU","A","Bank"));

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
	print "\n if (editNum(document.Search.srchNumber, 4, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Number","srchNumber","","operNumber","opersel_num_short","N","10","2");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","10","30");

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Number")          {$orby = array(array("BABKNO","A","Number"));}
	elseif ($sequence == "Name")            {$orby = array(array("BABKNMU","A","Name"),array("BABKNO","A","Number"));}
	elseif ($sequence == "Account")         {$orby = array(array("BAACCT","A","Account"),array("BABKNO","A","Number"));}
	elseif ($sequence == "Destination")     {$orby = array(array("BAIMD","A","Destiantion"),array("BABKNO","A","Number"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("BABKNO", "Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("upper(BABKNM)", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectBank(number,name){ ";
print "\n window.opener.document.$docName.$fldName.value = number; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = name;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = name;}";
print "\n window.opener.document.$docName.$fldName.focus(); ";
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

$uv_PRBankName ="BABKNO";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select PRBANK.*, upper(BABKNM) as BABKNMU, BAIMD# as BAIMD ";
$fileSQL .= " PRBANK ";
$selectSQL="BABKNO>0 ";
if     ($moreInfo=="Y")  {$selectSQL="BABKNO=$moreBank ";}
elseif ($wildCardSearch!="") {$selectSQL="BABKNO=BABKNO ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"BABKNO|null|Bank Number|N|\" title=\"Bank Number\">Bank Number";
	$qsOpt .= "\n <option value=\"upper(BABKNM)|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"BAACCT|null|Account|A|U\" title=\"Account\">Account";
	$qsOpt .= "\n <option value=\"BAIMD#|null|Destination|A|U\" title=\"Destination\">Destination";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("BABKNO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number\">{$sortPoint}Number</a></th>";
	$returnValue=OrderBy_Sort("BABKNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("BAACCT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Account\" title=\"Sequence By Account\">{$sortPoint}Account</a></th>";
	$returnValue=OrderBy_Sort("BAIMD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Destination\" title=\"Sequence By Destination, Number\">{$sortPoint}Destination</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Name=Format_Quote($row['BABKNM']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[BABKNO]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectBank('" . trim($row['BABKNO']) . "','" . trim($F_Name) . "')\" title=\"Select Name\">$F_Name</a></td> ";
		print "\n     <td class=\"colalph\">$row[BAACCT]</td>";
		print "\n     <td class=\"colalph\">$row[BAIMD]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreBank=" . urlencode(trim($row['BABKNO'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Name=Format_Quote($row['BABKNM']);
	$moreInfoSelect = "href=\"javascript:selectBank('" . trim($row['BABKNO']) . "','" . trim($F_Name) . "')\" title=\"Select Bank\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Bank Number",$row[BABKNO],"","N");
	$F_Name=Format_Quote($row['BABKNM']);
	Build_DspFld("Bank Name",$F_Name,"","A");
	Build_DspFld("Bank Account Number",$row[BAACCT],"","A");
	Build_DspFld("Print Check# On Check",$row[BAPRCK],"","A");
	Build_DspFld("Print Check# On Stub",$row[BAPCOS],"","A");
	Build_DspFld("Number Of Leader Checks",$row[BALEAD],"","N");
	$fieldDesc=RetValue("NFFMT='$row[BAFMT]'", "HRNFMT", "NFDESC");
	Build_DspFld("Name Format For Checks",$row[BAFMT],"$fieldDesc","N");
	$fieldDesc=RetValue("SRPGID='HPRBKM' and SRCODE='$row[BASORT]'", "HRSORT", "SRDESC");
	Build_DspFld("Sort Sequence For Checks",$row[BASORT],"$fieldDesc","N");
	Build_DspFld("Check/Advice Comment 1",$row[BACMN1],"","A");
	Build_DspFld("Check/Advice Comment 2",$row[BACMN2],"","A");
	Build_DspFld("Print Check Total Hours",$row[BATOTH],"","A");
	Build_DspFld("Hourly Pay Rate Option",$row[BAPRT],"","N");
	Build_DspFld("Round Rate To Two Decimal",$row[BARND],"","A");
	Build_DspFld("Bank Table",$row[BAFILE],"","A");
	Build_DspFld("Bank Table Exit Program",$row[BAEXIT],"","A");

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
