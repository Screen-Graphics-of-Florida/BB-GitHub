<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];    if (is_null($fromBatchNumber))    {$fromBatchNumber    = $_GET['amp;fromBatchNumber'];}
$fromBatchDate      = $_GET['fromBatchDate'];      if (is_null($fromBatchDate))      {$fromBatchDate      = $_GET['amp;fromBatchDate'];}
$fromBatchBank      = $_GET['fromBatchBank'];      if (is_null($fromBatchBank))      {$fromBatchBank      = $_GET['amp;fromBatchBank'];}
$WFReview           = $_GET['WFReview'];           if (is_null($WFReview))           {$WFReview           = $_GET['amp;WFReview'];}
$wfInstance         = $_GET['wfInstance'];         if (is_null($wfInstance))         {$wfInstance         = $_GET['amp;wfInstance'];}
$wfInstanceDate     = $_GET['wfInstanceDate'];     if (is_null($wfInstanceDate))     {$wfInstanceDate     = $_GET['amp;wfInstanceDate'];}
$wfWorkItem         = $_GET['wfWorkItem'];         if (is_null($wfWorkItem))         {$wfWorkItem         = $_GET['amp;wfWorkItem'];}
$wfWorkItemSequence = $_GET['wfWorkItemSequence']; if (is_null($wfWorkItemSequence)) {$wfWorkItemSequence = $_GET['amp;wfWorkItemSequence'];}
$wfParticipantId    = $_GET['wfParticipantId'];    if (is_null($wfParticipantId))    {$wfParticipantId    = $_GET['amp;wfParticipantId'];}

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Application of Cash";
$scriptName    = "ApplCashCustomer.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("CMCNA1U","A","Name"),array("CMCUST","A","Customer"));
$tabID         = "CUSTOMER";
$fromType      = "C";
$programName   = "HARCED";

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("View:", "Only With Open Invoices", $viewCheckBoxURL, "1", "0"),
array("", "With Pending Payments", $viewCheckBoxURL, "2", "0"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH"){
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CheckEnterSearch.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.Search.srchCust, 7, 0) && ";
	print "\n     editNum(document.Search.srchPhone, 11, 0) && ";
	print "\n     editNum(document.Search.srchLoc, 3, 0) && ";
	print "\n     editNum(document.Search.srchSalesman, 3, 0) && ";
	print "\n     editNum(document.Search.srchPriority, 3, 1) && ";
	print "\n     editNum(document.Search.srchInvoice, 7, 0)) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHCUSTOMERSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","20","26");
	Build_AdvSrch_Entry("Customer","srchCust","","operCust","opersel_num_short","N","20","7");

	print "\n <tr><td class=\"dsphdr\">Address</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchAddress\" size=\"20\" maxlength=\"26\"></td>";
	print "\n </tr>";

	$operNbr = "operSt";
	print "\n <tr><td class=\"dsphdr\">State</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchState\" size=\"20\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchState&amp;fldDesc=stateDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"stateDesc\"></span></td>";
	print "\n </tr>";

	Build_AdvSrch_Entry("Zip","srchZip","","operZip","opersel_alph_short","A","20","13");
	Build_AdvSrch_Entry("Credit Contact","srchContact","","operContact","opersel_alph_short","A","20","16");

	print "\n <tr><td class=\"dsphdr\">Phone</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputnmbr\"> <input type=\"text\" name=\"srchPhone\" size=\"20\" maxlength=\"11\"></td>";
	print "\n </tr>";

	$operNbr = "operTerms";
	print "\n <tr><td class=\"dsphdr\">Terms Code</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchTerms\" size=\"20\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchTerms&amp;fldDesc=termsCodeDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"termsCodeDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operClass";
	print "\n <tr><td class=\"dsphdr\">Class Code</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchClass\" size=\"20\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerClassSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchClass&amp;fldDesc=classCodeDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"classCodeDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operRegion";
	print "\n <tr><td class=\"dsphdr\">Customer Region</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchRegion\" size=\"20\" maxlength=\"5\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}RegionSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchRegion&amp;fldDesc=regionDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"regionDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operLoc";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "opersel_num_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"srchLoc\" size=\"20\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchLoc&amp;fldDesc=srchLocDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"srchLocDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operSalesman";
	print "\n <tr><td class=\"dsphdr\">Salesman</td>";
	print "\n     <td>"; require "opersel_num_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"srchSalesman\" size=\"20\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchSalesman&amp;fldDesc=salesmanDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"salesmanDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operCountry";
	print "\n <tr><td class=\"dsphdr\">Country</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchCountry\" size=\"20\" maxlength=\"3\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CountrySearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchCountry&amp;fldDesc=countryDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"countryDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operHold";
	print "\n <tr><td class=\"dsphdr\">Hold Code</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input name=\"srchHold\" type=\"text\" size=\"20\" maxlength=\"4\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HoldCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Search&amp;fldType=O&amp;fldName=srchHold&amp;fldDesc=srchHoldDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"srchHoldDesc\">$fieldDesc</span></td>";
	print "\n </tr>";

	if ($CRSCR1 != "") {Build_AdvSrch_Entry("$CRSCR1","srchUDF1","","operUDF1","opersel_alph_short","A","20","15");}
	if ($CRSCR2 != "") {Build_AdvSrch_Entry("$CRSCR2","srchUDF2","","operUDF2","opersel_alph_short","A","20","15");}
	if ($CRSCR3 != "") {Build_AdvSrch_Entry("$CRSCR3","srchUDF3","","operUDF3","opersel_alph_short","A","20","15");}
	if ($CRSCR4 != "") {Build_AdvSrch_Entry("$CRSCR4","srchUDF4","","operUDF4","opersel_alph_short","A","20","15");}
	if ($CRSCR5 != "") {Build_AdvSrch_Entry("$CRSCR5","srchUDF5","","operUDF5","opersel_alph_short","A","20","15");}

	Build_AdvSrch_Entry("Management Priority","srchPriority","","operMgmt","opersel_num_short","N","20","5");
	Build_AdvSrch_Entry("Bill-To Name","srchBillTo","","operBillTo","opersel_alph_short","A","20","26");

	print "\n <tr><td class=\"dsphdr\">Invoice</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputnmbr\"> <input type=\"text\" name=\"srchInvoice\" size=\"20\" maxlength=\"7\"></td>";
	print "\n </tr>";

	print "\n <tr><td class=\"dsphdr\">Reference Number</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"> <input type=\"text\" name=\"srchPO\" size=\"20\" maxlength=\"22\"></td>";
	print "\n </tr>";

	$focusField = "srchName";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Name")      {$orby = array(array("CMCNA1U","A","Name"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "Customer")  {$orby = array(array("CMCUST","A","Customer"));}
	elseif ($sequence == "PartPayer") {$orby = array(array("FLDESCU","A","Part Of Payer"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "City")      {$orby = array(array("CMCCTYU","A","City"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "State")     {$orby = array(array("CMST","A","State"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "Method")    {$orby = array(array("PSDESCU","A","Preferred Payment Method"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "OpenAR")    {$orby = array(array("ARCARB","A","Open A/R"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "InUse")     {$orby = array(array("CIIBCH","A","In Use By Batch"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "User")      {$orby = array(array("USDESCU","A","In Use By User"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "Errors")    {$orby = array(array("ARPYENERROR","A","Number of Errors"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "BillTo")    {$orby = array(array("CMCNA1U_BLTO","A","Bill-To"),array("CMCUST","A","Customer"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){
	if (strpos($_POST['qsName'],'IVAINV') !== false) {
		if ($_POST['qsOper']=="") {$_POST['qsOper']="=";}
		$_POST['qsValue'] = Build_SelData($_POST['qsValue'],"",$_POST['qsOper'],"N");
		$_POST['qsName']="z.CMCUST|null|Customer|N|";
		$_POST['qsValue']="(Select IVBLTO from HDINVC where IVBLTO=z.CMCUST and IVAINV $_POST[qsOper] $_POST[qsValue])";
		$_POST['qsOper']="IN";
	}
	if (strpos($_POST['qsName'],'IVARPO') !== false) {
		if ($_POST['qsOper']=="") {$_POST['qsOper']="LIKE";}
		$_POST['qsValue'] = Build_SelData($_POST['qsValue'],"U",$_POST['qsOper'],"A");
		$_POST['qsName']="z.CMCUST|null|Customer|N|";
		$_POST['qsValue']="(Select IVBLTO from HDINVC where IVBLTO=z.CMCUST and IVARPO $_POST[qsOper] '$_POST[qsValue]')";
		$_POST['qsOper']="IN";
	}
	require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard ("z.CMCNA1U", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard ("z.CMCUST", "Customer", $_POST['srchCust'], "", $_POST['operCust'], "N");
	if (trim($_POST['srchAddress']) != ""){
		$returnValue=Build_WildCard("z.CMCNA2U", "Address", $_POST['srchAddress'], "U", "LIKE", "V");
		$_POST['srchAddress'] = Build_SelData($_POST['srchAddress'],"U","LIKE","A");
		$wildCardTemp .= " (trim(z.CMCNA2U)  LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(z.CMCNA3U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(z.CMCNA4U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(z.CMCCTYU) LIKE '$_POST[srchAddress]')";
	}
	$returnValue=Build_WildCard ("z.CMST", "State", $_POST['srchState'], "U", $_POST['operSt'], "A");
	$returnValue=Build_WildCard ("z.CMZIP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	$returnValue=Build_WildCard ("upper(z.CMCRCT)", "Credit Contact", $_POST['srchContact'], "U", $_POST['operContact'], "A");
	$returnValue=Build_WildCard ("z.CMPHON", "Phone", $_POST['srchPhone'], "", "", "P");
	$returnValue=Build_WildCard ("z.CMCTRM", "Terms Code", $_POST['srchTerms'], "U", $_POST['operTerms'], "A");
	$returnValue=Build_WildCard ("z.CMCCLS", "Class Code", $_POST['srchClass'], "U", $_POST['operClass'], "A");
	$returnValue=Build_WildCard ("z.CMCRGN", "Customer Region", $_POST['srchRegion'], "U", $_POST['operRegion'], "A");
	$returnValue=Build_WildCard ("z.CMLOC#", "Location", $_POST['srchLoc'], "", $_POST['operLoc'], "N");
	$returnValue=Build_WildCard ("z.CMSLSM", "Salesman", $_POST['srchSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Build_WildCard ("z.CMCTRY", "Country", $_POST['srchCountry'], "U", $_POST['operCountry'], "A");
	$returnValue=Build_WildCard ("z.CMCHLD", "Hold Code", $_POST['srchHold'], "U", $_POST['operHold'], "A");
	$returnValue=Build_WildCard ("upper(z.CMUDF1)", $CRSCR1, $_POST['srchUDF1'], "U", $_POST['operUDF1'], "A");
	$returnValue=Build_WildCard ("upper(z.CMUDF2)", $CRSCR2, $_POST['srchUDF2'], "U", $_POST['operUDF2'], "A");
	$returnValue=Build_WildCard ("upper(z.CMUDF3)", $CRSCR3, $_POST['srchUDF3'], "U", $_POST['operUDF3'], "A");
	$returnValue=Build_WildCard ("upper(z.CMUDF4)", $CRSCR4, $_POST['srchUDF4'], "U", $_POST['operUDF4'], "A");
	$returnValue=Build_WildCard ("upper(z.CMUDF5)", $CRSCR5, $_POST['srchUDF5'], "U", $_POST['operUDF5'], "A");
	$returnValue=Build_WildCard ("z.CMMPTY", "Management Priority", $_POST['srchPriority'], "", $_POST['operMgmt'], "N");
	$returnValue=Build_WildCard ("b.CMCNA1U", "Bill-To Name", $_POST['srchBillTo'], "U", $_POST['operBillTo'], "A");
	if (trim($_POST['srchInvoice'])!="") {$returnValue=Build_WildCard("z.CMCUST", "Invoice", "(Select IVBLTO from HDINVC where (IVBLTO,IVAINV)=(z.CMCUST,$_POST[srchInvoice]))", "", "in", "N");}
	if (trim($_POST['srchPO'])!="")      {
		$_POST['srchPO'] = Build_SelData($_POST['srchPO'],"U","LIKE","A");
		$returnValue=Build_WildCard("z.CMCUST", "Reference Number", "(Select IVBLTO from HDINVC where IVBLTO=z.CMCUST and IVARPO like '$_POST[srchPO]')", "", "in", "N");
	}
	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';
require_once 'Menu.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

require_once 'ApplCashCustomerJava.php';

print "\n function confirmNoInvoice() {alert(\"Customer has no invoice and user is not authorized to unapplied cash.\");} \n";
print "\n function confirmNotDepEntry() {alert(\"Customer has no invoice and batch does not allow unapplied cash.\");} \n";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "APPLCASHCUSTOMER";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$BMBCHT=RetValue("(BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank)", "ARPBCH", "BMBCHT");
require_once 'ApplCashCustomerTabInclude.php';

$uv_CustomerName ="z.CMCUST";
$uv_CustomerClassName ="z.CMCCLS";
$uv_RegionName ="z.CMCRGN";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select z.CMCNA1,z.CMCUST,z.CMBLTO,z.CMCNA2,z.CMCNA3,z.CMCNA4,z.CMCCTY,z.CMST,z.CMZIP,z.CMSBCD,z.CMCNA1U,z.CMCCTYU,";
$stmtSQL .= " Coalesce(b.CMCNA1,' ') as CMCNA1_BLTO, Coalesce(b.CMCNA1U,' ') as CMCNA1U_BLTO,";
$stmtSQL .= " Coalesce(CIIBCH,0) as CIIBCH, Coalesce(CIIDTE,0) as CIIDTE,";
$stmtSQL .= " Coalesce(CIIBNK,0) as CIIBNK, Coalesce(CIIUSR,' ') as CIIUSR,";
$stmtSQL .= " Coalesce(CITYPE,' ') as CITYPE, Coalesce(CIID,0) as CIID,";
$stmtSQL .= " Coalesce(ARCARB,0) as ARCARB,";
$stmtSQL .= " Coalesce(FLDESC,' ') as FLDESC, Coalesce(Upper(FLDESC),' ') as FLDESCU,";
$stmtSQL .= " Coalesce(PSDESC,' ') as PSDESC, Coalesce(PSDESCU,' ') as PSDESCU,";
$stmtSQL .= " Coalesce(USDESC,' ') as USDESC, Coalesce(USDESCU,' ') as USDESCU, ";
$stmtSQL .= " Coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'C',z.CMCUST)),0) + Coalesce((Select Count(*) from ARDCER Where (CRBCHN,CRBCHD,CRBCHB,CRTYPE,CRID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'C',z.CMCUST)),0) as ARPYENERROR, ";
$stmtSQL .= " Coalesce((select Count(*) from HDINVC Where IVBLTO=z.CMCUST),0) as HDINVCCOUNT ";
$fileSQL .= " HDCUST z ";
$fileSQL .= " left join HDCUST b on b.CMCUST=z.CMBLTO and z.CMCUST<>z.CMBLTO";
$fileSQL .= " left join HDCUSI on CICUST=z.CMCUST";
if ($HDMCRL>0 && $CRPRMC=="Y") {$fileSQL .= " left join HDCARB on (ARCUST,ARCTYP,ARCURT)=(z.CMCUST,'I','$BKCURT')"; }
else                           {$fileSQL .= " left join HDCARB on (ARCUST,ARCTYP,ARCURT)=(z.CMCUST,'I',' ') ";}
$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('BY',Case When (Select Count(*) from ARPYRC Where PCCUST=z.CMCUST)>0 Then 'Y' Else ' ' End)";
$fileSQL .= " left join ARPYSB on PSSBCD=z.CMSBCD ";
$fileSQL .= " left join SYUSER on USUSER=CIIUSR ";
$selectSQL .= " z.CMCUST<>$CRMSCC ";
$viewCheckSQL="";
if     ($viewCheckBox[0] && $HDMCRL>0 && $CRPRMC=="Y") {$viewCheckSQL .= " (Select Count(*) from HDINVC Where (IVBLTO,IVCURT,IVCURD)=(z.CMCUST,'$BKCURT','$CFCURT') and IVIVAM-IVNPOS<>0)>0 ";}
elseif ($viewCheckBox[0])                              {$viewCheckSQL .= " (Select Count(*) from HDINVC Where IVBLTO=z.CMCUST and IVIVAM-IVNPOS<>0)>0 ";}
if ($viewCheckBox[1] && $viewCheckSQL!="")             {$viewCheckSQL .= " or (Select Count(*) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'C',z.CMCUST))>0 ";}
elseif ($viewCheckBox[1])                              {$viewCheckSQL .= " (Select Count(*) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'C',z.CMCUST))>0 ";}

if ($viewCheckSQL != "") {$selectSQL .= " and ($viewCheckSQL)";}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php'; 
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"z.CMCNA1U|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"z.CMCUST|null|Customer|N|\" title=\"Customer\">Customer";
	$qsOpt .= "\n <option value=\"Coalesce(Upper(FLDESC),' ')|null|Part of Payer Description|A|U\" title=\"Part of Payer Description\">Part of Payer Description";
	$qsOpt .= "\n <option value=\"z.CMCCTYU|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"z.CMST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"Coalesce(PSDESCU,' ')|null|Preferred Payment Method Description|A|U\" title=\"Preferred Payment Method Description\">Preferred Payment Method Description";
	$qsOpt .= "\n <option value=\"Coalesce(ARCARB,0)|null|Open A/R|N|\" title=\"Open A/R\">Open A/R";
	$qsOpt .= "\n <option value=\"Coalesce(CIIBCH,0)|null|In Use By Batch|N|\" title=\"In Use By Batch\">In Use By Batch";
	$qsOpt .= "\n <option value=\"Coalesce(USDESCU,' ')|null|In Use By User Name|A|U\" title=\"In Use By User Name\">In Use By User Name";
	$qsOpt .= "\n <option value=\"Coalesce(b.CMCNA1U,' ')|null|Bill-To Name|A|U\" title=\"Bill-To Name\">Bill-To Name";
	$qsOpt .= "\n <option value=\"IVAINV|null|Invoice|N|\" title=\"Invoice\">Invoice";
	$qsOpt .= "\n <option value=\"IVARPO|null|Reference Number|N|\" title=\"Reference Number\">Reference Number";
	require 'QuickSearchOption.php';
}

print "\n <table $contentTable> <tr>";
if ($formatToPrint != "Y"){
	print "\n <th class=\"colhdr\">$optionHeading</th>";
}

$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\"      title=\"Sequence By Name\">{$sortPoint}Name</a></th>";
$returnValue=OrderBy_Sort("CMCUST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Customer\"  title=\"Sequence By Customer\">{$sortPoint}Customer</a></th>";
$returnValue=OrderBy_Sort("FLDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PartPayer\" title=\"Sequence By Part of Payer\">{$sortPoint}Part of Payer</a></th>";
$returnValue=OrderBy_Sort("CMCCTYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\"      title=\"Sequence By City\">{$sortPoint}City</a></th>";
$returnValue=OrderBy_Sort("CMST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"     title=\"Sequence By State\">{$sortPoint}State</a></th>";
$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Method\"    title=\"Sequence By Preferred Payment Method\">{$sortPoint}Preferred Payment Method</a></th>";
$returnValue=OrderBy_Sort("ARCARB"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OpenAR\"    title=\"Sequence By Open A/R\">{$sortPoint}Open A/R</a></th> ";
$returnValue=OrderBy_Sort("CIIBCH"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InUse\"     title=\"Sequence By In Use By Batch\">{$sortPoint}In Use By Batch</a></th>";
$returnValue=OrderBy_Sort("USDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\"      title=\"Sequence By In Use By User\">{$sortPoint}In Use By User</a></th>";
$returnValue=OrderBy_Sort("ARPYENERROR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Errors\"    title=\"Sequence By Number of Errors, Batch\">{$sortPoint}Number of Errors</a></th>";
$returnValue=OrderBy_Sort("CMCNA1U_BLTO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BillTo\"      title=\"Sequence By Bill-To\">{$sortPoint}Bill-To</a></th>";
print "\n </tr>";

$rowCount = 0;
$beginRow=$startRow;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	$row['CIIUSR']=trim($row['CIIUSR']);
	$row['CITYPE']=trim($row['CITYPE']);
	$maintainVar = "{$scriptVarBase}&amp;fromType=" . $fromType . "&amp;fromID=" . urlencode(trim($row['CMCUST'])) . "&amp;fromScript=" . urlencode(trim($scriptName));
	$maintainVarD2w = "{$altVarBase}&amp;fromType=" . $fromType . "&amp;fromID=" . urlencode(trim($row['CMCUST'])) . "&amp;fromScript=" . urlencode(trim($scriptName));

	require  'SetRowClass.php';
	$confirmDesc = Format_Confirm_Desc("$row[CMCUST]", "", "", "", "", "");
	print "\n <tr class=\"$rowClass\">";
	if ($formatToPrint != "Y"){
		print "\n <td class=\"opticon\">";
		if ($row['HDINVCCOUNT']==0 && $harced_OPT['sec_02']=="N" && (trim($row['CIIUSR'])=="" || $row['CIIBCH']==$fromBatchNumber && $row['CIIDTE']==$fromBatchDate && $row['CIIBNK']==$fromBatchBank && $row['CIIUSR']==trim($userProfile) && trim($row['CITYPE'])==$fromType && $row['CIID']==$row['CMCUST'])){
			print "\n <a onClick=\"return confirmNoInvoice()\" >$arCashPmtImageSml</a>";
		} elseif ($row['HDINVCCOUNT']==0 && $CRBBAL=="Y" && $BMBCHT!="D" && (trim($row['CIIUSR'])=="" || $row['CIIBCH']==$fromBatchNumber && $row['CIIDTE']==$fromBatchDate && $row['CIIBNK']==$fromBatchBank && $row['CIIUSR']==trim($userProfile) && trim($row['CITYPE'])==$fromType && $row['CIID']==$row['CMCUST'])){
			print "\n <a onClick=\"return confirmNotDepEntry()\" >$arCashPmtImageSml</a>";
		} elseif ((trim($row['CIIUSR'])=="" || $row['CIIBCH']==$fromBatchNumber && $row['CIIDTE']==$fromBatchDate && $row['CIIBNK']==$fromBatchBank && $row['CIIUSR']==trim($userProfile) && trim($row['CITYPE'])==$fromType && $row['CIID']==$row['CMCUST'])){
			print "\n <a onClick=\"return CustomerPAYMENT('$row[CMCUST]')\" href=\"javascript:NewWindow('{$homeURL}{$phpPath}ApplCashPaymentDocument.php{$maintainVar}&amp;tag=REPORT','arDocument_win','$arDocumentWinPctH','$arDocumentWinPctW','$arDocumentWinSB','$arDocumentWinRZ','$arDocumentWinTB','$arDocumentWinMB','$arDocumentWinST')\">$arCashPmtImageSml</a>";
		}
		if ($row['CIIUSR']==$userProfile && $row['CITYPE']==$fromType && $row['CIID']==$row['CMCUST']){
			print "\n <a onClick=\"CustomerRELEASE('$row[CMCUST]');\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;startRow=" . urlencode(trim($beginRow)) . "\">$releaseCustomerImage</a>";
		}
		print "\n </td>";
	}
	$address=trim($row['CMCNA2']);
	if (trim($row['CMCNA3'])!="") {$address .= ", " . trim($row['CMCNA3']);}
	if (trim($row['CMCNA4'])!="") {$address .= ", " . trim($row['CMCNA4']);}
	$address .= ", " . trim($row['CMCCTY']) . " " . $row[CMST] . " " . $row[CMZIP];

	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['CMCUST'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[CMCUST]]\">$row[CMCNA1]</a></td> ";
	print "\n <td class=\"colnmbr\">$row[CMCUST]</td>";
	if (trim($row['FLDESC'])=="Yes") {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}PayerInquiry.php{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Payer\">$row[FLDESC]</a></td>";}
	else                             {print "\n <td class=\"colalph\">$row[FLDESC]</td>";}
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$address\">$row[CMCCTY]</span></td>";
	print "\n <td class=\"colalph\">$row[CMST]</td>";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[CMSBCD]\">$row[PSDESC]</span></td>";
	print "\n <td class=\"colnmbr\">" . number_format($row['ARCARB'],2) . "</td>";
	print "\n <td class=\"colnmbr\">$row[CIIBCH]</td>";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[CIIUSR]\">$row[USDESC]</span></td>";
	if ($row['ARPYENERROR']==0) {print "\n <td class=\"colnmbr\">$row[ARPYENERROR]</td>";}
	else                        {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashErrorInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Error\">$row[ARPYENERROR]</a></td>";}
	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['CMBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[CMBLTO]]\">$row[CMCNA1_BLTO]</a></td> ";
	print "\n </tr>";

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
require 'EndTabInclude.php';
print "\n </table>";
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>