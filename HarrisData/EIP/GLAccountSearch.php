<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName      = $_GET['docName'];
$fldCo        = $_GET['fldCo'];
$fldFac       = $_GET['fldFac'];
$fldAcct      = $_GET['fldAcct'];
$fldSub       = $_GET['fldSub'];
$fldDesc      = $_GET['fldDesc'];
$forceChange  = $_GET['forceChange'];
$moreInfo     = $_GET['moreInfo'];
$moreCo       = $_GET['moreCo'];
$moreFac      = $_GET['moreFac'];
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
require_once 'WildCardAcctInclude.php';

$page_title     = "G/L Account Search";
$scriptName     = "GLAccountSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;forceChange=" . urlencode(trim($forceChange)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldCo=" . urlencode(trim($fldCo)) . "&amp;fldFac=" . urlencode(trim($fldFac)) . "&amp;fldAcct=" . urlencode(trim($fldAcct)) . "&amp;fldSub=" . urlencode(trim($fldSub)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("OBGMNMU","A","Name"),array("OBCO","A","Company/Facility"),array("OBFAC","A",""),array("OBACCT","A","Account"),array("OBSUB","A",""));

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
	print "\n if (editNum(document.Search.srchCo, 2, 0) ";
	print "\n  && editNum(document.Search.srchFac, 4, 0) ";
	print "\n  && editNum(document.Search.srchAcct, 4, 0) ";
	print "\n  && editNum(document.Search.srchSub, 4, 0) ";
	print "\n    ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	$operNbr = "operCoFac";
	print "\n <tr><td class=\"dsphdr\">Company/Facility</td>";
	print "\n     <td>"; require 'opersel_num_short.php'; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchCo\" size=\"2\" maxlength=\"2\"> ";
	print "\n                           - <input type=\"text\" name=\"srchFac\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n </tr>";

	$operNbr = "operAcct";
	print "\n <tr><td class=\"dsphdr\">Account</td>";
	print "\n     <td>"; require 'opersel_num_short.php'; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchAcct\" size=\"4\" maxlength=\"4\"> ";
	print "\n                           - <input type=\"text\" name=\"srchSub\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Description","srchName","","operName","opersel_alph_short","A","30","30");

	$focusField = "srchCo";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "CoFac")        {$orby = array(array("OBCO","A","Company/Facility"),array("OBFAC","A",""),array("OBACCT","A","Account"),array("OBSUB","A",""));}
	elseif ($sequence == "Account")      {$orby = array(array("OBACCT","A","Account"),array("OBSUB","A",""),array("OBCO","A","Company/Facility"),array("OBFAC","A",""));}
	elseif ($sequence == "Description")  {$orby = array(array("OBGMNMU","A","Name"),array("OBCO","A","Company/Facility"),array("OBFAC","A",""),array("OBACCT","A","Account"),array("OBSUB","A",""));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard_CoFac("OBCO", "OBFAC", "Company/Facility", $_POST['srchCo'], $_POST['srchFac'], $_POST['operCoFac']);
	$returnValue=Build_WildCard_Acct("OBACCT", "OBSUB", "G/L Account", $_POST['srchAcct'], $_POST['srchSub'], $_POST['operAcct']);
	$returnValue=Build_WildCard("OBGMNMU", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectAccount(company,facility,account,subAccount,accountDesc){ ";
print "\n window.opener.document.$docName.$fldCo.value = company; ";
print "\n window.opener.document.$docName.$fldFac.value = facility; ";
print "\n window.opener.document.$docName.$fldAcct.value = account; ";
print "\n window.opener.document.$docName.$fldSub.value = subAccount; ";
print "\n if      (window.opener.document.$docName.$fldDesc)          {window.opener.document.$docName.$fldDesc.value = accountDesc;}  ";
print "\n else if (window.opener.document.getElementById('$fldDesc')) {window.opener.document.getElementById('$fldDesc').innerHTML = accountDesc;} ";
if ($forceChange=="Y") {
	print "\n window.opener.document.getElementById('$fldCo').onchange(); ";
	print "\n window.opener.document.getElementById('$fldFac').onchange(); ";
	print "\n window.opener.document.getElementById('$fldAcct').onchange(); ";
	print "\n window.opener.document.getElementById('$fldSub').onchange(); ";
}
print "\n window.opener.document.$docName.$fldCo.focus(); ";
print "\n window.close(); ";
print "\n } ";

require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
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

$uv_CompanyName ="OBCO";
$uv_FacilityName ="OBFAC";
$uv_AccountName ="OBACCT";
$uv_SubaccountName ="OBSUB";
require 'UserView.php';

require 'stmtSQLClear.php';
$withSQL .= " With GLTROB_YEAR ";
$withSQL .= " (YR_OBCO,YR_OBFAC,YR_OBACCT,YR_OBSUB,YR_OBYEAR)";
$withSQL .= " as ";
$withSQL .= " (Select OBCO,OBFAC,OBACCT,OBSUB,Max(OBYEAR) ";
$withSQL .= "  From GLTROB ";
$withSQL .= "  Group By OBCO,OBFAC,OBACCT,OBSUB) ";

$stmtSQL .= " Select OBCO,OBFAC,OBACCT,OBSUB,OBGMNM ";
$stmtSQL .= "       ,Coalesce(CFCFNM,' ') as CFCFNM ";
$fileSQL .= " GLTROB ";
$fileSQL .= " inner join GLTROB_YEAR on (YR_OBCO,YR_OBFAC,YR_OBACCT,YR_OBSUB,YR_OBYEAR)=(OBCO,OBFAC,OBACCT,OBSUB,OBYEAR) ";
$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(OBCO,OBFAC) ";
if ($moreInfo=="Y")                         {$selectSQL .= " (OBCO,OBFAC,OBACCT,OBSUB)=($moreCo,$moreFac,$moreAcct,$moreSub) ";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL .= " OBACCT=OBACCT ";}

require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"OBCO|null|Company|N|\" title=\"Company\">Company";
	$qsOpt .= "\n <option value=\"OBFAC|null|Facility|N|\" title=\"Facility\">Facility";
	$qsOpt .= "\n <option value=\"OBACCT|null|Account Number|N|\" title=\"Account Number\">Account Number";
	$qsOpt .= "\n <option value=\"OBSUB|null|Subaccount Number|N|\" title=\"Subaccount Number\">Subaccount Number";
	$qsOpt .= "\n <option value=\"OBGMNMU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("OBCO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CoFac\"  title=\"Sequence By Company/Facility, Account\">{$sortPoint}Co/Fac</a></th>";
	$returnValue=OrderBy_Sort("OBACCT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Account\"      title=\"Sequence By Account\">{$sortPoint}Account</a></th>";
	$returnValue=OrderBy_Sort("OBGMNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\"  title=\"Sequence By Description, Account\">{$sortPoint}Description</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_CoFac=Format_CoFac($row['OBCO'],$row['OBFAC'],"N");
		$F_AcctSub=Format_Acct($row['OBACCT'],$row['OBSUB'],"N");
		$F_Name=Format_Quote($row['OBGMNM']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$row[CFCFNM]\">$F_CoFac</span></td>";
		print "\n     <td class=\"colnmbr\">$F_AcctSub</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectAccount('" . trim($row['OBCO']) . "','" . trim($row['OBFAC']) . "','" . trim($row['OBACCT']) . "','" . trim($row['OBSUB']) . "','" . trim($F_Name) . "')\" title=\"Select Account\">$F_Name</a></td> ";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCo=" . urlencode(trim($row['OBCO'])) . "&amp;moreFac=" . urlencode(trim($row['OBFAC'])) . "&amp;moreAcct=" . urlencode(trim($row['OBACCT'])) . "&amp;moreSub=" . urlencode(trim($row['OBSUB'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);

	$moreInfoSelect = "href=\"javascript:selectAccount('" . trim($row['OBCO']) . "','" . trim($row['OBFAC']) . "','" . trim($row['OBACCT']) . "','" . trim($row['OBSUB']) . "','" . trim($row['OBGMNM']) . "')\" title=\"Select Account\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	$F_CoFac=Format_CoFac($row['OBCO'],$row['OBFAC'],"N");
	$F_AcctSub=Format_Acct($row['OBACCT'],$row['OBSUB'],"N");
	Build_DspFld("Co/Fac",$F_CoFac,$row['CFCFNM'],"A");
	Build_DspFld("Account",$F_AcctSub,"","A");
	Build_DspFld("Description",$row['OBGMNM'],"","A");

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
