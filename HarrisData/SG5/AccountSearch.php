<?php

require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName      = $_GET['docName'];
$acctFld      = $_GET['acctFld'];
$subFld       = $_GET['subFld'];
$descFld      = $_GET['descFld'];
$forceChange  = $_GET['forceChange'];
$moreInfo     = $_GET['moreInfo'];
$moreAcct     = $_GET['moreAcct'];
$moreSub      = $_GET['moreSub'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Account Search";
$scriptName     = "AccountSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;forceChange=" . urlencode(trim($forceChange)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;acctFld=" . urlencode(trim($acctFld)). "&amp;subFld=" . urlencode(trim($subFld)) . "&amp;descFld=" . urlencode(trim($descFld));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("CHCHDSU","A","Description"),array("CHACCT","A","Account"),array("CHSUB","A",""));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	include ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	include ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CalendarInclude.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchAcct, 4, 0) && ";
	print "\n     editNum(document.Search.srchSub, 4, 0) && ";
	print "\n     editdate(document.Search.srchDateEst) && ";
	print "\n     editdate(document.Search.srchDateDea) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	$operNbr = "operAcct";
	print "\n <tr><td class=\"dsphdr\">Account</td>";
	print "\n     <td>"; require 'opersel_num_short.php'; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchAcct\" size=\"4\" maxlength=\"4\">&nbsp;-&nbsp;<input name=\"srchSub\" type=\"text\" size=\"4\" maxlength=\"4\"></td>";
	print "\n </tr>";

	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Date Established","srchDateEst","","operDateEst","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("Date Deactivated","srchDateDea","","operDateDea","opersel_num_short","D","6","6");

	$focusField = "srchAcct";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Account")      {$orby = array(array("CHACCT","A","Account"),array("CHSUB","A",""));}
	elseif ($sequence == "Description")  {$orby = array(array("CHCHDSU","A","Description"),array("CHACCT","A","Account"),array("CHSUB","A",""));}
	elseif ($sequence == "Established")  {$orby = array(array("CHDTES","A","Date Established"),array("CHACCT","A","Account"),array("CHSUB","A",""));}
	elseif ($sequence == "Deactivated")  {$orby = array(array("CHDTDE","A","Date Deactivated"),array("CHACCT","A","Account"),array("CHSUB","A",""));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require 'WildCardClear.php';
	$returnValue=Build_WildCard("CHACCT ", "Account", $_POST['srchAcct'], "", $_POST['operAcct'], "N");
	$returnValue=Build_WildCard("CHSUB", "SubAccount", $_POST['srchSub'], "", $_POST['operSub'], "N");
	$returnValue=Build_WildCard("CHCHDSU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("CHDTES", "Date Established", $_POST['srchDateEst'], "", $_POST['operDateEst'], "D");
	$returnValue=Build_WildCard("CHDTDE", "Date Deactivated", $_POST['srchDateDea'], "", $_POST['operDateDea'], "D");
	$returnValue=Build_WildCard("CHRTYP", "Currency Rate Type", $_POST['srchCurt'], "U", $_POST['operCurt'], "A");
	require 'WildCardUpdate.php';
}

include ($docType);
print "\n <html> \n	<head>";
include ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectAccount(account,subAccount,accountDesc){ ";
print "\n window.opener.document.$docName.$acctFld.value = account; ";
print "\n window.opener.document.$docName.$subFld.value = subAccount; ";
print "\n if      (window.opener.document.$docName.$descFld)          {window.opener.document.$docName.$descFld.value = accountDesc;}  ";
print "\n else if (window.opener.document.getElementById('$descFld')) {window.opener.document.getElementById('$descFld').innerHTML = accountDesc;} ";
if ($forceChange=="Y") {
	print "\n window.opener.document.getElementById('$acctFld').onchange(); ";
	print "\n window.opener.document.getElementById('$subFld').onchange(); ";
}
print "\n window.opener.document.$docName.$acctFld.focus(); ";
print "\n window.close(); ";
print "\n } ";
print "\n </script> \n";

include ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
include $searchBanner;
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

$uv_AccountName ="CHACCT";
$uv_SubaccountName ="CHSUB";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " HDCHRT ";
if ($moreInfo=="Y")                         {$selectSQL .= " CHACCT=$moreAcct and CHSUB=$moreSub ";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL .= " CHACCT=CHACCT ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"CHACCT|null|Account Number|N|\" title=\"Account Number\">Account Number";
	$qsOpt .= "\n <option value=\"CHSUB|null|Subaccount Number|N|\" title=\"Subaccount Number\">Subaccount Number";
	$qsOpt .= "\n <option value=\"CHCHDSU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"CHDTES|DATE|Date Established|D|\" title=\"Date Established\">Date Established";
	$qsOpt .= "\n <option value=\"CHDTDE|DATE|Date Deactivated|D|\" title=\"Date Deactivated\">Date Deactivated";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("CHACCT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Account\"      title=\"Sequence By Account\">{$sortPoint}Account</a></th>";
	$returnValue=OrderBy_Sort("CHCHDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\"  title=\"Sequence By Description, Account\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("CHDTES"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Established\"  title=\"Sequence By Date Established, Account\">{$sortPoint}Date<br>Established</a></th>";
	$returnValue=OrderBy_Sort("CHDTDE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Deactivated\"  title=\"Sequence By Date Deactivated, Account\">{$sortPoint}Date<br>Deactivated</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		include  'SetRowClass.php';
		$F_AcctSub=Format_Acct($row['CHACCT'],$row['CHSUB'],"N");
		$F_CHDTES=Format_Date($row['CHDTES'],"D");
		$F_CHDTDE=Format_Date($row['CHDTDE'],"D");
		$F_Desc=Format_Quote($row['CHCHDS']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$F_AcctSub</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectAccount('" . trim($row['CHACCT']) . "','" . trim($row['CHSUB']) . "','" . trim($F_Desc) . "')\" title=\"Select Account\">$F_Desc</a></td> ";
		print "\n     <td class=\"coldate\">$F_CHDTES</td>";
		print "\n     <td class=\"coldate\">$F_CHDTDE</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreAcct=" . urlencode(trim($row['CHACCT'])) . "&amp;moreSub=" . urlencode(trim($row['CHSUB'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);

	$moreInfoSelect = "href=\"javascript:selectAccount('" . trim($row['CHACCT']) . "','" . trim($row['CHSUB']) . "','" . trim($row['CHCHDS']) . "')\" title=\"Select Account\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	$F_AcctSub=Format_Acct($row['CHACCT'],$row['CHSUB'],"N");
	Build_DspFld("Account",$F_AcctSub,"","A");
	Build_DspFld("Description",$row[CHCHDS],"","A");
	Build_DspFld("Normal Account Balance",$row[CHNAB],"","A");
	Build_DspFld("Account Usage",$row[CHACSG],"","A");

	$F_CHDTES=Format_Date($row['CHDTES'],"H");
	Build_DspFld("Date Established",$F_CHDTES,"","A");

	$F_CHDTDE=Format_Date($row['CHDTDE'],"H");
	Build_DspFld("Date Deactivated",$F_CHDTDE,"","A");

	if ($HDMCRL > 0) {Build_DspFld("Currency Rate Type",$row[CHRTYP],"","A");}

	if ($row['CHUPDT'] > 0) {$F_CHUPDT=Format_Date($row['CHUPDT'],"H");
	Build_DspFld("Date Last Maintained",$F_CHUPDT,"","A");}

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
include $searchTrailer;
print "\n </body> \n </html>";
?>	
