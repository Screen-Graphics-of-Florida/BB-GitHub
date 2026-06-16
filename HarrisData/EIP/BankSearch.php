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
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Bank Search";
$scriptName     = "BankSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("BKBKNMU","A","Name"),array("BKBANK","A","Bank"));

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
	print "\n if (editNum(document.Search.srchAcct, 4, 0) && ";
	print "\n     editNum(document.Search.srchSub, 4, 0) && ";
	print "\n     editNum(document.Search.srchNumber, 2, 0) && ";
	print "\n     editNum(document.Search.srchCo, 2, 0) && ";
	print "\n     editNum(document.Search.srchFac, 4, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Bank","srchNumber","","operNumber","opersel_num_short","N","10","2");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","10","30");
	Build_AdvSrch_Entry("Checking Account","srchCheckingAccount","","operCheckingAccount","opersel_alph_short","A","10","12");

	$operNbr = "operCo";
	print "\n <tr><td class=\"dsphdr\">Co/Fac</td>";
	print "\n     <td>"; require "opersel_num_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchCo\" size=\"2\" maxlength=\"2\">&nbsp;/&nbsp;<input name=\"srchFac\" type=\"text\" size=\"4\" maxlength=\"4\"></td>";
	print "\n </tr>";

	$operNbr = "operAcct";
	print "\n <tr><td class=\"dsphdr\">Account</td>";
	print "\n     <td>"; require "opersel_num_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchAcct\" size=\"4\" maxlength=\"4\">&nbsp;-&nbsp;<input name=\"srchSub\" type=\"text\" size=\"4\" maxlength=\"4\"></td>";
	print "\n </tr>";

	if ($HDMCRL>0) {Build_AdvSrch_Entry("Currency Type","srchCur","","operCur","opersel_alph_short","A","3","3");}

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Number")          {$orby = array(array("BKBANK","A","Bank"));}
	elseif ($sequence == "Name")            {$orby = array(array("BKBKNMU","A","Name"),array("BKBANK","A","Bank"));}
	elseif ($sequence == "CheckingAccount") {$orby = array(array("BKBACC","A","Checking Account"));}
	elseif ($sequence == "CoFac")           {$orby = array(array("BKCO","A","CoFac"),array("BKFAC","A",""),array("BKBANK","A","Bank"));}
	elseif ($sequence == "Account")         {$orby = array(array("BKACCT","A","Account"),array("BKSUB","A",""),array("BKBANK","A","Bank"));}
	elseif ($sequence == "Cur")             {$orby = array(array("BKCURT","A","Currency Type"),array("BKBANK","A","Bank"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("BKBANK", "Bank Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("upper(BKBKNM)", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("BKBACC", "Checking Account", $_POST['srchCheckingAccount'], "U", $_POST['operCheckingAccount'], "A");
	$returnValue=Build_WildCard("BKCO", "Company Number", $_POST['srchCo'], "U", $_POST['operCo'], "N");
	$returnValue=Build_WildCard("BKFAC", "Facility Number", $_POST['srchFac'], "U", $_POST['operFac'], "N");
	$returnValue=Build_WildCard("BKACCT ", "Account Number", $_POST['srchAcct'], "", $_POST['operAcct'], "N");
	$returnValue=Build_WildCard("BKSUB", "SubAccount Number", $_POST['srchSub'], "", $_POST['operSub'], "N");
	$returnValue=Build_WildCard("BKCURT", "Currency Type", $_POST['srchCur'], "U", $_POST['operCur'], "A");
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
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectBank(number,name){ ";
print "\n window.opener.document.$docName.$fldName.value = number; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = name;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = name;}";
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

$uv_BankName ="BKBANK";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDBANK.*, upper(BKBKNM) as BKBKNMU, BKVCK# as BKVCK ";
$fileSQL .= " HDBANK ";
if     ($moreInfo=="Y")      {$selectSQL="BKBANK=$moreBank ";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL="BKBANK=BKBANK ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"BKBANK|null|Bank Number|N|\" title=\"Bank Number\">Bank Number";
	$qsOpt .= "\n <option value=\"upper(BKBKNM)|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"BKBACC|null|Checking Account|A|U\" title=\"Checking Account\">Checking Account";
	$qsOpt .= "\n <option value=\"BKCO|null|Company Number|N|\" title=\"Company Number\">Company Number";
	$qsOpt .= "\n <option value=\"BKFAC|null|Facility Number|N|\" title=\"Facility Number\">Facility Number";
	$qsOpt .= "\n <option value=\"BKACCT|null|Account Number|N|\" title=\"Account Number\">Account Number";
	$qsOpt .= "\n <option value=\"BKSUB|null|Subaccount Number|N|\" title=\"Subaccount Number\">Subaccount Number";
	if ($HDMCRL>0) {$qsOpt .= "\n <option value=\"BKCURT|null|Currency Type|A|U\" title=\"Currency Type\">Currency Type";}
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("BKBANK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Bank\">{$sortPoint}Bank</a></th>";
	$returnValue=OrderBy_Sort("BKBKNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Bank\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("BKBACC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CheckingAccount\" title=\"Sequence By Checking Account\">{$sortPoint}Checking<br>Account</a></th>";
	$returnValue=OrderBy_Sort("BKCO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CoFac\" title=\"Sequence By Co/Fac, Bank\">{$sortPoint}Co/Fac</a></th>";
	$returnValue=OrderBy_Sort("BKACCT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Account\"      title=\"Sequence By Account, Bank\">{$sortPoint}Account</a></th>";
	if ($HDMCRL>0) {
		$returnValue=OrderBy_Sort("BKCURT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Cur\" title=\"Sequence By Currency Type, Bank\">{$sortPoint}Cur</a></th>";
	}
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_CoFac=Format_CoFac($row['BKCO'],$row['BKFAC'],"N");
		$coFacName=RetValue("(CFCO#,CFFAC#)=($row[BKCO],$row[BKFAC])", "HDCFAC", "CFCFNM");
		$F_AcctSub=Format_Acct($row['BKACCT'],$row['BKSUB'],"N");
		$acctDesc=RetValue("(CHACCT,CHSUB)=($row[BKACCT],$row[BKSUB])", "HDCHRT", "CHCHDS");
		$F_BKBLSD=Format_Date($row['BKBLSD'],"D");
		$F_Name=Format_Quote($row['BKBKNM']);
		if ($HDMCRL>0) {$curDesc=RetValue("CYTYPE='$row[BKCURT]'", "HDCTYP", "CYDESC");}
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colcode\">$row[BKBANK]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectBank('" . trim($row['BKBANK']) . "','" . trim($F_Name) . "')\" title=\"Select Name\">$F_Name</a></td> ";
		print "\n     <td class=\"colalph\">$row[BKBACC]</td>";
		print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$coFacName\">$F_CoFac</span></td>";
		print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$acctDesc\">$F_AcctSub</span></td>";
		if ($HDMCRL>0) {
			print "\n     <td class=\"colalph\" $helpCursor><span title=\"$curDesc\">$row[BKCURT]</span></td>";
		}
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreBank=" . urlencode(trim($row['BKBANK'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);

	$moreInfoSelect = "href=\"javascript:selectBank('" . trim($row['BKBANK']) . "','" . trim($row['BKBKNM']) . "')\" title=\"Select Bank\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Bank",$row[BKBANK],"","N");
	Build_DspFld("Bank Name",$row[BKBKNM],"","A");
	Build_DspFld("Checking Account",$row[BKBACC],"","A");
	$F_CoFac=Format_CoFac($row['BKCO'],$row['BKFAC'],"Y");
	Build_DspFld("Company/Facility",$F_CoFac,"","A");
	$F_AcctSub=Format_Acct($row['BKACCT'],$row['BKSUB'],"Y");
	Build_DspFld("Account",$F_AcctSub,"","A");
	$F_BKBLSD=Format_Date($row['BKBLSD'],"H");
	Build_DspFld("Statement Date",$F_BKBLSD,"","A");
	Build_DspFld("Number Of Checks To Void",$row[BKVCK],"","N");
	Build_DspFld("Print Check# On The Check",$row[BKPNOC],"","A");
	Build_DspFld("Print Check# On The Stub",$row[BKPNOS],"","A");
	if ($HDMCRL>0) Build_DspFld("Currency Type",$row[BKCURT],"","A");

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
