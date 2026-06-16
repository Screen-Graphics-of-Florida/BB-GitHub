<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$moreWhs  = $_GET['moreWhs'];
$touchScreen  = $_GET['touchScreen'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Warehouse Search";
$scriptName     = "WarehouseSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;touchScreen=" . urlencode(trim($touchScreen));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("WHWHNMU","A","Name"),array("WHWHS","A","Number"));

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
	print "\n if (editNum(document.Search.srchNumber, 3, 0) && ";
	print "\n     editNum(document.Search.srchCo, 2, 0) && ";
	print "\n     editNum(document.Search.srchFac, 4, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Number","srchNumber","","operNumber","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","26","26");

	$operNbr = "operCo";
	print "\n <tr><td class=\"dsphdr\">Co/Fac</td>";
	print "\n     <td>"; require "opersel_num_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchCo\" size=\"2\" maxlength=\"2\">&nbsp;/&nbsp;<input name=\"srchFac\" type=\"text\" size=\"4\" maxlength=\"4\"></td>";
	print "\n </tr>";

	Build_AdvSrch_Entry("City","srchCity","","operCity","opersel_alph_short","A","26","26");
	Build_AdvSrch_Entry("State","srchState","","operState","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Zip","srchZip","","operZip","opersel_alph_short","A","13","13");

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if     ($sequence == "Number") {$orby = array(array("WHWHS","A","Number"));}
	elseif ($sequence == "Name")   {$orby = array(array("WHWHNMU","A","Name"),array("WHWHS","A","Number"));}
	elseif ($sequence == "CoFac")  {$orby = array(array("WHCO","A","CoFac"),array("WHFAC","A",""),array("WHWHNMU","A","Name"),array("WHWHS","A","Number"));}
	elseif ($sequence == "City")   {$orby = array(array("WHWHCTU","A","City"),array("WHWHNMU","A","Name"),array("WHWHS","A","Number"));}
	elseif ($sequence == "State")  {$orby = array(array("WHWHST","A","State"),array("WHWHNMU","A","Name"),array("WHWHS","A","Number"));}
	elseif ($sequence == "Zip")    {$orby = array(array("WHHWZP","A","Zip"),array("WHWHNMU","A","Name"),array("WHWHS","A","Number"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("WHWHS", "Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "A");
	$returnValue=Build_WildCard("upper(WHWHNM)", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("WHCO#", "Co", $_POST['srchCo'], "U", $_POST['operCo'], "N");
	$returnValue=Build_WildCard("WHFAC#", "Co", $_POST['srchFac'], "U", $_POST['operFac'], "N");
	$returnValue=Build_WildCard("upper(WHWHCT)", "City", $_POST['srchCity'], "U", $_POST['operCity'], "A");
	$returnValue=Build_WildCard("WHWHST", "State", $_POST['srchState'], "U", $_POST['operState'], "A");
	$returnValue=Build_WildCard("WHHWZP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectWhs(number,name){ ";
print "\n window.opener.document.$docName.$fldName.value = number; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = name;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = name;}";
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

$uv_WarehouseName ="WHWHS";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDWHSM.*, upper(WHWHNM) as WHWHNMU, upper(WHWHCT) as WHWHCTU, WHCO# as WHCO, WHFAC# as WHFAC ";
$fileSQL .= " HDWHSM ";
if ($moreInfo=="Y")          {$selectSQL .= " WHWHS=$moreWhs ";}
elseif ($wildCardSearch!="" || $uv_Sql != "") {$selectSQL="WHWHS<>0 ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"WHWHS|null|Number|N|\" title=\"Number\">Number";
	$qsOpt .= "\n <option value=\"upper(WHWHNM)|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"WHCO#|null|Company Number|N|\" title=\"Company Number\">Company Number";
	$qsOpt .= "\n <option value=\"WHFAC#|null|Facility Number|N|\" title=\"Facility Number\">Facility Number";
	$qsOpt .= "\n <option value=\"upper(WHWHCT)|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"WHWHST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"WHHWZP|null|Zip|A|U\" title=\"Zip\">Zip";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("WHWHS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number\">{$sortPoint}Number</a></th>";
	$returnValue=OrderBy_Sort("WHWHNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("WHCO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CoFac\" title=\"Sequence By Co/Fac, Name, Number\">{$sortPoint}Co/Fac</a></th>";
	$returnValue=OrderBy_Sort("WHWHCTU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\" title=\"Sequence By City, Name, Number\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("WHWHST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"      title=\"Sequence By State, Name, Number\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("WHHWZP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Zip\"      title=\"Sequence By Zip, Name, Number\">{$sortPoint}Zip</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_CoFac=Format_CoFac($row['WHCO'],$row['WHFAC'],"N");
		$F_Name=Format_Quote($row['WHWHNM']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colcode\">$row[WHWHS]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectWhs('" . trim($row['WHWHS']) . "','" . trim($F_Name) . "')\" title=\"Select Name\">$F_Name</a></td> ";
		print "\n     <td class=\"colnmbr\">$F_CoFac</td>";
		print "\n     <td class=\"colalph\">$row[WHWHCT]</td>";
		print "\n     <td class=\"colalph\">$row[WHWHST]</td>";
		print "\n     <td class=\"colalph\">$row[WHHWZP]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreWhs=" . urlencode(trim($row['WHWHS'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Name=Format_Quote($row['WHWHNM']);
	$moreInfoSelect = "href=\"javascript:selectWhs('" . trim($row['WHWHS']) . "','" . trim($F_Name) . "')\" title=\"Select Whs\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Warehouse Number",$row[WHWHS],"","N");
	Build_DspFld("Warehouse Name",$row[WHWHNM],"","A");
	$F_CoFac=Format_CoFac($row['WHCO'],$row['WHFAC'],"Y");
	Build_DspFld("Company/Facility",$F_CoFac,"","A");
	if (trim($row[WHWHAD]) != "") {Build_DspFld("Address Line One",$row[WHWHAD],"","A");}
	if (trim($row[WHWHCT]) != "") {Build_DspFld("City",$row[WHWHCT],"","A");}
	if (trim($row[WHWHST]) != "") {Build_DspFld("State",$row[WHWHST],"","A");}
	if (trim($row[WHHWZP]) != "") {Build_DspFld("Zip",$row[WHHWZP],"","A");}
	if (trim($row[WHCTRY]) != "") {
		$fieldDesc=RetValue("CNCTCD='$row[WHCTRY]'", "HDCTRY", "CNCDES");
		Build_DspFld("Country",$fieldDesc,"","A");
	}
	if ($row[WHSCHD] != 0) {
		$fieldDesc=RetValue("SMSCHD='$row[WHSCHD]'", "HDSCHM", "SMDESC");
		print "\n <tr><td class=\"dsphdr\">Schedule</td> ";
		print "\n     <td class=\"dspnmbr\">$row[WHSCHD] &nbsp; $fieldDesc</td> ";
		print "\n </tr> ";
	}
	if (trim($row[WHPSEG]) != "") {Build_DspFld("Page Segment Name",$row[WHPSEG],"","A");}
	if ($row[WHTCVA] !=0) {
		$F_AcctSub=Format_Acct($row['WHTCVA'],$row['WHTCVS'],"Y");
		Build_DspFld("Transfer Cost Variance Account",$F_AcctSub,"","A");
	}
	if (trim($row[WHSTKL]) != "") {
		$fieldDesc=RtvYNDesc($row[WHSTKL]);
		Build_DspFld("Stock Locator Active",$fieldDesc,"","A");
	}
	if (trim($row[WHACS]) != "") {
		$fieldDesc=RtvYNDesc($row[WHACS]);
		Build_DspFld("Auto Update Item Stock Loc",$fieldDesc,"","A");
	}
	if (trim($row[WHMRPN]) != "") {
		$fieldDesc=RtvYNDesc($row[WHMRPN]);
		Build_DspFld("MRP Netable Warehouse",$fieldDesc,"","A");
	}
	if (trim($row[WHABCI]) != "") {
		$fieldDesc=RtvYNDesc($row[WHABCI]);
		Build_DspFld("ABC Analysis By Inventory Type",$fieldDesc,"","A");
	}
	if ($row[WHVLA]!= .000) {
		$fieldDesc = Format_Nbr(($row['WHVLA'] * 100), "1", "4", "Y", "", "");
		Build_DspFld("Acceptance Limit Percentage Class A",$fieldDesc,"","N");
	}
	if ($row[WHVLB]!=.000) {
		$fieldDesc = Format_Nbr(($row['WHVLB'] * 100), "1", "4", "Y", "", "");
		Build_DspFld("Acceptance Limit Percentage Class B",$fieldDesc,"","N");
	}
	if ($row[WHVLC]!=.000) {
		$fieldDesc = Format_Nbr(($row['WHVLC'] * 100), "1", "4", "Y", "", "");
		Build_DspFld("Acceptance Limit Percentage Class C",$fieldDesc,"","N");
	}
	if ($row[WHCAW] != 0) {Build_DspFld("Cycle Days Class A",$row[WHCAW],"","N");}
	if ($row[WHCBW] != 0) {Build_DspFld("Cycle Days Class B",$row[WHCBW],"","N");}
	if ($row[WHCBW] != 0) {Build_DspFld("Cycle Days Class C",$row[WHCCW],"","N");}
	if ($row[WHPCA] != 0) {Build_DspFld("Percentage For Class A",$row[WHPCA],"","N");}
	if ($row[WHPCB] != 0) {Build_DspFld("Percentage For Class B",$row[WHPCB],"","N");}
	if ($row[WHUVRA] !=0) {
		$F_AcctSub=Format_Acct($row['WHUVRA'],$row['WHUVRS'],"Y");
		Build_DspFld("Unvouchered Receipts Expense Account",$F_AcctSub,"","A");
	}
	if ($row[WHPRVA] !=0) {
		$F_AcctSub=Format_Acct($row['WHPRVA'],$row['WHPRVS'],"Y");
		Build_DspFld("Standard Variance Account",$F_AcctSub,"","A");
	}
	if ($row[WHFGHA] !=0) {
		$F_AcctSub=Format_Acct($row['WHFGHA'],$row['WHFGHS'],"Y");
		Build_DspFld("Freight Expense Account",$F_AcctSub,"","A");
	}
	if ($row[WHFGVA] !=0) {
		$F_AcctSub=Format_Acct($row['WHFGVA'],$row['WHFGVS'],"Y");
		Build_DspFld("Freight Variance Account",$F_AcctSub,"","A");
	}
	if ($row[WHSLTA] !=0) {
		$F_AcctSub=Format_Acct($row['WHSLTA'],$row['WHSLTS'],"Y");
		Build_DspFld("Sales Tax Expense Account",$F_AcctSub,"","A");
	}
	if ($row[WHSTVA] !=0) {
		$F_AcctSub=Format_Acct($row['WHSTVA'],$row['WHSTVS'],"Y");
		Build_DspFld("Sales Tax Variance Account",$F_AcctSub,"","A");
	}
	if ($row[WHSPGA] !=0) {
		$F_AcctSub=Format_Acct($row['WHSPGA'],$row['WHSPCS'],"Y");
		Build_DspFld("Special Charge Expense Account",$F_AcctSub,"","A");
	}
	if ($row[WHSCVA] !=0) {
		$F_AcctSub=Format_Acct($row['WHSCVA'],$row['WHSCVS'],"Y");
		Build_DspFld("Special Charge Variance Account",$F_AcctSub,"","A");
	}
	$COSMC1=RetValue("COSMC1<>' '", "POCTRL", "COSMC1" );
	if (trim($COSMC1) != "") {
		if ($row[WHMC1A] != 0) {
			$F_AcctSub=Format_Acct($row['WHMC1A'],$row['WHMC1S'],"Y");
			Build_DspFld("$COSMC1 Expense Account",$F_AcctSub,"","A");}
			if ($row[WHMV1A] != 0) {
				$F_AcctSub=Format_Acct($row['WHMV1A'],$row['WHMV1S'],"Y");
				Build_DspFld("$COSMC1 Variance Account",$F_AcctSub,"","A");}
	}
	$COSMC2=RetValue("COSMC2<>' '", "POCTRL", "COSMC2" );
	if (trim($COSMC2) != "") {
		if ($row[WHMC2A] != 0) {
			$F_AcctSub=Format_Acct($row['WHMC2A'],$row['WHMC2S'],"Y");
			Build_DspFld("$COSMC2 Expense Account",$F_AcctSub,"","A");}
			if ($row[WHMV2A] != 0) {
				$F_AcctSub=Format_Acct($row['WHMV2A'],$row['WHMV2S'],"Y");
				Build_DspFld("$COSMC2 Variance Account",$F_AcctSub,"","A");}
	}
	$COSMC3=RetValue("COSMC3<>' '", "POCTRL", "COSMC3" );
	if (trim($COSMC3) != "") {
		if ($row[WHMC3A] != 0) {
			$F_AcctSub=Format_Acct($row['WHMC3A'],$row['WHMC3S'],"Y");
			Build_DspFld("$COSMC3 Expense Account",$F_AcctSub,"","A");}
			if ($row[WHMV3A] != 0) {
				$F_AcctSub=Format_Acct($row['WHMV3A'],$row['WHMV3S'],"Y");
				Build_DspFld("$COSMC3 Variance Account",$F_AcctSub,"","A");}
	}
	$COSMC4=RetValue("COSMC4<>' '", "POCTRL", "COSMC4" );
	if (trim($COSMC4) != "") {
		if ($row[WHMC4A] != 0) {
			$F_AcctSub=Format_Acct($row['WHMC4A'],$row['WHMC4S'],"Y");
			Build_DspFld("$COSMC4 Expense Account",$F_AcctSub,"","A");}
			if ($row[WHMV4A] != 0) {
				$F_AcctSub=Format_Acct($row['WHMV4A'],$row['WHMV4S'],"Y");
				Build_DspFld("$COSMC4 Variance Account",$F_AcctSub,"","A");}
	}
	$COSMC5=RetValue("COSMC5<>' '", "POCTRL", "COSMC5" );
	if (trim($COSMC5) != "") {
		if ($row[WHMC5A] != 0) {
			$F_AcctSub=Format_Acct($row['WHMC5A'],$row['WHMC5S'],"Y");
			Build_DspFld("$COSMC5 Expense Account",$F_AcctSub,"","A");}
			if ($row[WHMV5A] != 0) {
				$F_AcctSub=Format_Acct($row['WHMV5A'],$row['WHMV5S'],"Y");
				Build_DspFld("$COSMC5 Variance Account",$F_AcctSub,"","A");}
	}
	$COSMC6=RetValue("COSMC6<>' '", "POCTRL", "COSMC6" );
	if (trim($COSMC6) != "") {
		if ($row[WHMC6A] != 0) {
			$F_AcctSub=Format_Acct($row['WHMC6A'],$row['WHMC6S'],"Y");
			Build_DspFld("$COSMC6 Expense Account",$F_AcctSub,"","A");}
			if ($row[WHMV6A] != 0) {
				$F_AcctSub=Format_Acct($row['WHMV6A'],$row['WHMV6S'],"Y");
				Build_DspFld("$COSMC6 Variance Account",$F_AcctSub,"","A");}
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
if ($touchScreen == "Y") {require_once 'KeyboardTS.htm';}
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
