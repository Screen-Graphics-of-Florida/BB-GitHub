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
$scriptName    = "ApplCashPayer.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)). "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("PYPYNMU","A","Name"),array("PYPAYR","A","Payer"));
$tabID         = "PAYER";
$fromType      = "P";
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
	print "\n   if (editNum(document.Search.srchPayer, 7, 0) && ";
	print "\n       editNum(document.Search.srchPhone, 11, 0) && ";
	print "\n       editNum(document.Search.srchContactPhone, 11, 0) && ";
	print "\n       editNum(document.Search.srchInvoice, 7, 0)) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHPAYERSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","20","26");
	Build_AdvSrch_Entry("Payer","srchPayer","","operPayer","opersel_num_short","N","20","7");

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

	Build_AdvSrch_Entry("Zip","srchZip","","operZip","opersel_alph_short","A","20","14");

	print "\n <tr><td class=\"dsphdr\">Phone</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputnmbr\"> <input type=\"text\" name=\"srchPhone\" size=\"20\" maxlength=\"11\"></td>";
	print "\n </tr>";

	$operNbr = "operCountry";
	print "\n <tr><td class=\"dsphdr\">Country</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchCountry\" size=\"20\" maxlength=\"3\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CountrySearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchCountry&amp;fldDesc=countryDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"countryDesc\"></span></td>";
	print "\n </tr>";

	Build_AdvSrch_Entry("Contact","srchContact","","operContact","opersel_alph_short","A","20","16");

	print "\n <tr><td class=\"dsphdr\">Contact Phone</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputnmbr\"> <input type=\"text\" name=\"srchContactPhone\" size=\"20\" maxlength=\"11\"></td>";
	print "\n </tr>";

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
	if     ($sequence == "Name")      {$orby = array(array("PYPYNMU","A","Name"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "Payer")     {$orby = array(array("PYPAYR","A","Payer"));}
	elseif ($sequence == "City")      {$orby = array(array("PYCITYU","A","City"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "State")     {$orby = array(array("PYST","A","State"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "Method")    {$orby = array(array("PSDESCU","A","Preferred Payment Method"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "OpenAR")    {$orby = array(array("ARCARB","A","Open A/R"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "InUse")     {$orby = array(array("THIS_FLDESCU","A","In Use By This"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "User")      {$orby = array(array("OTHR_FLDESCU","A","In Use By Other"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "Errors")    {$orby = array(array("ARPYENERROR","A","Number of Errors"),array("PYPAYR","A","Payer"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){
	if (strpos($_POST['qsName'],'IVAINV') !== false) {
		if ($_POST['qsOper']=="") {$_POST['qsOper']="=";}
		$_POST['qsValue'] = Build_SelData($_POST['qsValue'],"",$_POST['qsOper'],"N");
		$_POST['qsName']="z.PYPAYR|null|Payer|N|";
		$_POST['qsValue']="(Select PCPAYR from ARPYRC inner join HDINVC on IVBLTO=PCCUST and IVAINV $_POST[qsOper] $_POST[qsValue] where PCPAYR=z.PYPAYR)";
		$_POST['qsOper']="IN";
	}
	if (strpos($_POST['qsName'],'IVARPO') !== false) {
		if ($_POST['qsOper']=="") {$_POST['qsOper']="LIKE";}
		$_POST['qsValue'] = Build_SelData($_POST['qsValue'],"U",$_POST['qsOper'],"A");
		$_POST['qsName']="z.PYPAYR|null|Payer|N|";
		$_POST['qsValue']="(Select PCPAYR from ARPYRC inner join HDINVC on IVBLTO=PCCUST and IVARPO $_POST[qsOper] '$_POST[qsValue]' where PCPAYR=z.PYPAYR)";
		$_POST['qsOper']="IN";
	}
	require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard ("PYPYNMU", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard ("PYPAYR", "Payer", $_POST['srchPayer'], "", $_POST['operPayer'], "N");
	if (trim($_POST['srchAddress']) != ""){
		$returnValue=Build_WildCard("PYADR1U", "Address", $_POST['srchAddress'], "U", "LIKE", "V");
		$_POST['srchAddress'] = Build_SelData($_POST['srchAddress'],"U","LIKE","A");
		$wildCardTemp .= " (trim(PYADR1U)  LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(PYADR2U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(PYADR3U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(PYCITYU) LIKE '$_POST[srchAddress]')";
	}
	$returnValue=Build_WildCard ("PYST", "State", $_POST['srchState'], "U", $_POST['operSt'], "A");
	$returnValue=Build_WildCard ("PYZIP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	$returnValue=Build_WildCard ("PYPHON", "Phone", $_POST['srchPhone'], "", "", "P");
	$returnValue=Build_WildCard ("PYCONTU", "Contact", $_POST['srchContact'], "U", $_POST['operContact'], "A");
	$returnValue=Build_WildCard ("PYCPHN", "Contact Phone", $_POST['srchContactPhone'], "", "", "P");
	$returnValue=Build_WildCard ("PYCTRY", "Country", $_POST['srchCountry'], "U", $_POST['operCountry'], "A");
	if (trim($_POST['srchInvoice'])!="") {$returnValue=Build_WildCard("PYPAYR", "Invoice", "(Select PCPAYR from ARPYRC inner join HDINVC on (IVAINV,IVBLTO)=($_POST[srchInvoice],PCCUST) where PCPAYR=z.PYPAYR)", "", "in", "N");}
	if (trim($_POST['srchPO'])!="")      {
		$_POST['srchPO'] = Build_SelData($_POST['srchPO'],"U","LIKE","A");
		$returnValue=Build_WildCard("PYPAYR", "Reference Number", "(Select PCPAYR from ARPYRC inner join HDINVC on IVARPO like '$_POST[srchPO]' and IVBLTO=PCCUST where PCPAYR=z.PYPAYR)", "", "in", "N");
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

print "\n function confirmNoInvoice() {alert(\"Payer has no invoice and user is not authorized to unapplied cash.\");} \n";
print "\n function confirmNotDepEntry() {alert(\"Payer has no invoice and batch does not allow unapplied cash.\");} \n";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "APPLCASHPAYER";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$BMBCHT=RetValue("(BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank)", "ARPBCH", "BMBCHT");
require_once 'ApplCashCustomerTabInclude.php';

$uv_PayerName ="PYPAYR";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select PYPYNM,PYPAYR,PYADR1,PYADR2,PYADR3,PYCITY,PYST,PYZIP,PYSBCD,PYPYNMU,PYCITYU,";
$stmtSQL .= " Coalesce(a.FLDESC,' ') as THIS_FLDESC, Coalesce(Upper(a.FLDESC),' ') as THIS_FLDESCU,";
$stmtSQL .= " Coalesce(b.FLDESC,' ') as OTHR_FLDESC, Coalesce(Upper(b.FLDESC),' ') as OTHR_FLDESCU,";
$stmtSQL .= " Coalesce(PSDESC,' ') as PSDESC, Coalesce(PSDESCU,' ') as PSDESCU,";
$stmtSQL .= " Coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'P',z.PYPAYR)),0) + Coalesce((Select Count(*) from ARDCER Where (CRBCHN,CRBCHD,CRBCHB,CRTYPE,CRID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'P',z.PYPAYR)),0) as ARPYENERROR, ";
$stmtSQL .= " Coalesce((Select SUM(ARCARB) from HDCARB inner join ARPYRC on (PCPAYR,PCCUST)=(z.PYPAYR,ARCUST) ";
$stmtSQL .= " Where ARCTYP='I' ";
if ($HDMCRL>0 && $CRPRMC=="Y") {$stmtSQL .= " and ARCURT='$BKCURT'"; }
else                           {$stmtSQL .= " and ARCURT=' '"; }
$stmtSQL .= "),0) as ARCARB, ";
$stmtSQL .= " (Select Count(*) from HDCUSI Where (CIIUSR,CITYPE,CIID)=('$userProfile','$fromType',z.PYPAYR)) as HDCUSIRELEASE, ";
$stmtSQL .= " Coalesce((select Count(*) from HDINVC inner join ARPYRC on (PCPAYR,PCCUST)=(z.PYPAYR,IVBLTO)),0) as HDINVCCOUNT ";
$fileSQL .= " ARPYRH z ";
$fileSQL .= " left join SYFLAG a on (a.FLTYPE,a.FLVALU)=('BY',Case When (Select Count(*) from HDCUSI Where (CIIBCH,CIIDTE,CIIBNK,CIIUSR,CITYPE,CIID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$userProfile','$fromType',z.PYPAYR))>0 Then 'Y' Else ' ' End)";
$fileSQL .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('BY',Case When (Select Count(*) from ARPYRC inner join HDCUSI on CICUST=PCCUST and (CIIBCH<>$fromBatchNumber or CIIDTE<>$fromBatchDate or CIIBNK<>$fromBatchBank or CIIUSR<>'$userProfile' or CITYPE<>'$fromType' or CIID<>z.PYPAYR) Where PCPAYR=z.PYPAYR)>0 Then 'Y' Else ' ' End)";
$fileSQL .= " left join ARPYSB on PSSBCD=PYSBCD ";
$viewCheckSQL="";
if     ($viewCheckBox[0] && $HDMCRL>0 && $CRPRMC=="Y") {$viewCheckSQL .= " (Select Count(*) from HDINVC inner join ARPYRC on (PCPAYR,PCCUST)=(z.PYPAYR,IVBLTO) Where (IVCURT,IVCURD)=('$BKCURT','$CFCURT') and IVIVAM-IVNPOS-IVPPOS<>0)>0 ";}
elseif ($viewCheckBox[0])                              {$viewCheckSQL .= " (Select Count(*) from HDINVC inner join ARPYRC on (PCPAYR,PCCUST)=(z.PYPAYR,IVBLTO) Where IVIVAM-IVNPOS-IVPPOS<>0)>0 ";}
if ($viewCheckBox[1] && $viewCheckSQL!="")             {$viewCheckSQL .= " or (Select Count(*) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'P',z.PYPAYR))>0 ";}
elseif ($viewCheckBox[1])                              {$viewCheckSQL .= " (Select Count(*) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'P',z.PYPAYR))>0 ";}

if     ($viewCheckSQL != "")                {$selectSQL  = "($viewCheckSQL)";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL .= " PYPAYR<>0 ";}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"PYPYNMU|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"PYPAYR|null|Payer|N|\" title=\"Payer\">Payer";
	$qsOpt .= "\n <option value=\"PYCITYU|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"PYST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"Coalesce(PSDESCU,' ')|null|Preferred Payment Method Description|A|U\" title=\"Preferred Payment Method Description\">Preferred Payment Method Description";
	$qsOpt .= "\n <option value=\"IVAINV|null|Invoice|N|\" title=\"Invoice\">Invoice";
	$qsOpt .= "\n <option value=\"IVARPO|null|Reference Number|N|\" title=\"Reference Number\">Reference Number";
	require 'QuickSearchOption.php';
}

print "\n <table $contentTable> <tr>";
if ($formatToPrint != "Y"){
	print "\n <th class=\"colhdr\">$optionHeading</th>";
}

$returnValue=OrderBy_Sort("PYPYNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\"      title=\"Sequence By Name\">{$sortPoint}Name</a></th>";
$returnValue=OrderBy_Sort("PYPAYR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Payer\"  title=\"Sequence By Payer\">{$sortPoint}Payer</a></th>";
$returnValue=OrderBy_Sort("PYCITYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\"      title=\"Sequence By City\">{$sortPoint}City</a></th>";
$returnValue=OrderBy_Sort("PYST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"     title=\"Sequence By State\">{$sortPoint}State</a></th>";
$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Method\"    title=\"Sequence By Preferred Payment Method\">{$sortPoint}Preferred Payment Method</a></th>";
$returnValue=OrderBy_Sort("ARCARB"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OpenAR\"    title=\"Sequence By Open A/R\">{$sortPoint}Open A/R</a></th> ";
$returnValue=OrderBy_Sort("THIS_FLDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InUse\"     title=\"Sequence By In Use By This\">{$sortPoint}In Use By This</a></th>";
$returnValue=OrderBy_Sort("OTHR_FLDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\"      title=\"Sequence By In Use By Other\">{$sortPoint}In Use By Other</a></th>";
$returnValue=OrderBy_Sort("ARPYENERROR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Errors\"    title=\"Sequence By Number of Errors, Batch\">{$sortPoint}Number of Errors</a></th>";
print "\n </tr>";

$rowCount = 0;
$beginRow=$startRow;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	$row['CIIUSR']=trim($row['CIIUSR']);
	$maintainVar = "{$scriptVarBase}&amp;fromType=" . $fromType . "&amp;fromID=" . urlencode(trim($row['PYPAYR'])) . "&amp;fromScript=" . urlencode(trim($scriptName));

	require  'SetRowClass.php';
	$confirmDesc = Format_Confirm_Desc("$row[PYPAYR]", "", "", "", "", "");
	print "\n <tr class=\"$rowClass\">";
	if ($formatToPrint != "Y"){
		print "\n <td class=\"opticon\">";
		if ($row['HDINVCCOUNT']==0 && $harced_OPT['sec_02']=="N" && trim($row['OTHR_FLDESCU'])!="YES"){
			print "\n <a onClick=\"return confirmNoInvoice()\" >$arCashPmtImageSml</a>";
		} elseif ($row['HDINVCCOUNT']==0 && $CRBBAL=="Y" && $BMBCHT!="D" && trim($row['OTHR_FLDESCU'])!="YES"){
			print "\n <a onClick=\"return confirmNotDepEntry()\" >$arCashPmtImageSml</a>";
		} elseif (trim($row['OTHR_FLDESCU'])!="YES"){
			print "\n <a onClick=\"return PayerPAYMENT('$row[PYPAYR]')\" href=\"javascript:NewWindow('{$homeURL}{$phpPath}ApplCashPaymentDocument.php{$maintainVar}&amp;tag=REPORT','arDocument_win','$arDocumentWinPctH','$arDocumentWinPctW','$arDocumentWinSB','$arDocumentWinRZ','$arDocumentWinTB','$arDocumentWinMB','$arDocumentWinST')\">$arCashPmtImageSml</a>";
		}
		if ($row['HDCUSIRELEASE']>0){
			print "\n <a onClick=\"PayerRELEASE('$row[PYPAYR]');\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;startRow=" . urlencode(trim($beginRow)) . "\">$releaseCustomerImage</a>";
		}
		print "\n </td>";
	}
	$address=trim($row['PYADR1']);
	if (trim($row['PYADR2'])!="") {$address .= ", " . trim($row['PYADR2']);}
	if (trim($row['PYADR3'])!="") {$address .= ", " . trim($row['PYADR3']);}
	$address .= ", " . trim($row['PYCITY']) . " " . $row[PYST] . " " . $row[PYZIP];

	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}PayerInquiry.php{$maintainVar}&amp;tag=REPORT&amp;fromType=P&amp;fromID=" . urlencode(trim($row['PYPAYR'])) . "\" onclick=\"{$inquiryWinVar}\" title=\"View Payer\">$row[PYPYNM]</a></td>";
	print "\n <td class=\"colnmbr\">$row[PYPAYR]</td>";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$address\">$row[PYCITY]</span></td>";
	print "\n <td class=\"colalph\">$row[PYST]</td>";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PYSBCD]\">$row[PSDESC]</span></td>";
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ARBalanceInquiry.php{$maintainVar}&amp;tag=REPORT&amp;fromCategory=I&amp;fromCurrency=" . urlencode(trim($BKCURT)) . "\" onclick=\"{$inquiryWinVar}\" title=\"View Customer A/R Balance\">" . number_format($row['ARCARB'],2) . "</a></td>";
	print "\n <td class=\"colalph\">$row[THIS_FLDESC]</td>";
	print "\n <td class=\"colalph\">$row[OTHR_FLDESC]</td>";
	if ($row['ARPYENERROR']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashErrorInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Error\">$row[ARPYENERROR]</a></td>";}
	else                       {print "\n <td class=\"colnmbr\">$row[ARPYENERROR]</td>";}
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