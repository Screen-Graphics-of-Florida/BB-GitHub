<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$WFReview           = $_GET['WFReview'];
$wfInstance         = $_GET['wfInstance'];
$wfInstanceDate     = $_GET['wfInstanceDate'];
$wfWorkItem         = $_GET['wfWorkItem'];
$wfWorkItemSequence = $_GET['wfWorkItemSequence'];
$wfParticipantId    = $_GET['wfParticipantId'];

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
$scriptName    = "ApplCashInvoice.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("IVAINV","A","Invoice"),array("IVIVDT","A","Date"));
$tabID         = "INVOICE";
$fromType      = "C";
$programName   = "HARCED";

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("View:", "Open", $viewCheckBoxURL, "1", "1", "IVIVAM-IVNPOS-IVPPOS<>0"),
array("", "Paid", $viewCheckBoxURL, "2", "0", "IVIVAM-IVNPOS-IVPPOS=0"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH"){
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.$formName.frInvoice, 7, 0) ";
	print "\n    && editNum(document.$formName.toInvoice, 7, 0) ";
	print "\n    && editFromToOper(document.$formName.frInvoice, document.$formName.toInvoice, document.$formName.operInvoice, 7) ";
	print "\n    && editdate(document.$formName.frInvoiceDate) ";
	print "\n    && editdate(document.$formName.toInvoiceDate) ";
	print "\n    && editFromToOper(document.$formName.frInvoiceDate, document.$formName.toInvoiceDate, document.$formName.operInvoiceDate, 'D') ";
	print "\n    && editdate(document.$formName.frDueDate) ";
	print "\n    && editdate(document.$formName.toDueDate) ";
	print "\n    && editFromToOper(document.$formName.frDueDate, document.$formName.toDueDate, document.$formName.operDueDate, 'D') ";
	print "\n    && editNum(document.$formName.frInvoiceBal, 11, 2) ";
	print "\n    && editNum(document.$formName.toInvoiceBal, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frInvoiceBal, document.$formName.toInvoiceBal, document.$formName.operInvoiceBal, 15) ";
	print "\n    && editNum(document.$formName.frInvoiceAmount, 11, 2) ";
	print "\n    && editNum(document.$formName.toInvoiceAmount, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frInvoiceAmount, document.$formName.toInvoiceAmount, document.$formName.operInvoiceAmount, 15) ";
	print "\n    && editNum(document.$formName.frDiscountAmt, 11, 2) ";
	print "\n    && editNum(document.$formName.toDiscountAmt, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frDiscountAmt, document.$formName.toDiscountAmt, document.$formName.operDiscountAmt, 15) ";
	print "\n    && editNum(document.$formName.frBillTo, 7, 0) ";
	print "\n    && editNum(document.$formName.toBillTo, 7, 0) ";
	print "\n    && editFromToOper(document.$formName.frBillTo, document.$formName.toBillTo, document.$formName.operBillTo, 7) ";
	print "\n    && editNum(document.$formName.frOEOrder, 8, 0) ";
	print "\n    && editNum(document.$formName.toOEOrder, 8, 0) ";
	print "\n    && editFromToOper(document.$formName.frOEOrder, document.$formName.toOEOrder, document.$formName.operOEOrder, 8) ";
	print "\n    && editNum(document.$formName.frLocation, 3, 0) ";
	print "\n    && editNum(document.$formName.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.$formName.frLocation, document.$formName.toLocation, document.$formName.operLocation, 3) ";
	print "\n    && editNum(document.$formName.frSalesman, 3, 0) ";
	print "\n    && editNum(document.$formName.toSalesman, 3, 0) ";
	print "\n    && editFromToOper(document.$formName.frSalesman, document.$formName.toSalesman, document.$formName.operSalesman, 3) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHINVOICESEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Invoice","frInvoice","toInvoice","operInvoice","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Invoice Date","frInvoiceDate","toInvoiceDate","operInvoiceDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Due Date","frDueDate","toDueDate","operDueDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Invoice Balance","frInvoiceBal","toInvoiceBal","operInvoiceBal","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Invoice Amount","frInvoiceAmount","toInvoiceAmount","operInvoiceAmount","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Discount","frDiscountAmt","toDiscountAmt","operDiscountAmt","opersel_num2_short","N","15","15");

	$operNbr = "operBillTo";
	print "\n <tr><td class=\"dsphdr\">Bill-To</td>";
	print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frBillTo\" size=\"7\" maxlength=\"7\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frBillTo&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toBillTo\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toBillTo&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Bill-To Name","srchBillToName","","operBillToName","opersel_alph_short","A","20","30");
	Build_AdvSrch_Entry("Reference Number","srchPONumber","","operPONumber","opersel_alph_short","A","10","22");
	Build_AdvSrch_Entry("Order Number","frOEOrder","toOEOrder","operOEOrder","opersel_num2_short","N","8","8");
	Build_AdvSrch_Entry("Mfg Order","srchMfgOrder","","operMfgOrder","opersel_alph_short","N","9","9");

	$operNbr = "operLocation";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frLocation&amp;fldDesc=frLocationDesc\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toLocation&amp;fldDesc=toLocationDesc\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Location Name","srchLocationName","","operLocationName","opersel_alph_short","A","20","30");

	$operNbr = "operSalesman";
	print "\n <tr><td class=\"dsphdr\">Salesman</td>";
	print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frSalesman&amp;fldDesc=frSalesmanName\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toSalesman&amp;fldDesc=toSalesmanName\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Salesman Name","srchSalesmanName","","operSalesmanName","opersel_alph_short","A","20","30");

	$focusField = "frInvoice";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Invoice")   {$orby = array(array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "InvDate")   {$orby = array(array("IVIVDT","A","Invoice Date"),array("IVAINV","A","Invoice"));}
	elseif ($sequence == "DueDate")   {$orby = array(array("IVDUED","A","Due Date"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "Balance")   {$orby = array(array("IVBALC","A","Balance"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "Discount")  {$orby = array(array("IVDSCT","A","Discount"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "Customer")  {$orby = array(array("IVBLTO","A","Bill-To"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "Name")      {$orby = array(array("CMCNA1U","A","Bill-To Name"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "PO")        {$orby = array(array("IVARPO","A","Reference Number"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "OEOrder")   {$orby = array(array("IVORD","A","Order Number"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "MfgOrder")  {$orby = array(array("IVMORD","A","Mfg Order"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "Loc")       {$orby = array(array("IVLOC","A","Location"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "LocName")   {$orby = array(array("LOLNA1U","A","Location Name"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "Slsm")      {$orby = array(array("IVSLSM","A","Salesman"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "SlsmName")  {$orby = array(array("SMSNA1U","A","Salesman Name"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"));}
	elseif ($sequence == "InUse")     {$orby = array(array("CIIBCH","A","In Use By Batch"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "User")      {$orby = array(array("USDESCU","A","In Use By User"),array("CMCUST","A","Customer"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Range_WildCard("IVAINV", "Invoice", $_POST['frInvoice'], $_POST['toInvoice'], "", $_POST['operInvoice'], "N");
	$returnValue=Range_WildCard("IVIVDT", "Invoice Date", $_POST['frInvoiceDate'], $_POST['toInvoiceDate'], "", $_POST['operInvoiceDate'], "D");
	$returnValue=Range_WildCard("IVDUED", "Due Date", $_POST['frDueDate'], $_POST['toDueDate'], "", $_POST['operDueDate'], "I");
	$returnValue=Range_WildCard("IVIVAM-IVNPOS-IVPPOS", "Invoice Balance", $_POST['frInvoiceBal'], $_POST['toInvoiceBal'], "", $_POST['operInvoiceBal'], "N");
	$returnValue=Range_WildCard("IVIVAM", "Invoice Amount", $_POST['frInvoiceAmount'], $_POST['toInvoiceAmount'], "", $_POST['operInvoiceAmount'], "N");
	$returnValue=Range_WildCard("Case When IVIVAM-IVNPOS-IVPPOS=0 Then 0 Else IVDSCT-IVDSTK End ", "Discount", $_POST['frDiscountAmt'], $_POST['toDiscountAmt'], "", $_POST['operDiscountAmt'], "N");
	$returnValue=Range_WildCard("IVBLTO", "Bill-To", $_POST['frBillTo'], $_POST['toBillTo'], "", $_POST['operBillTo'], "N");
	$returnValue=Build_WildCard("CMCNA1U", "Bill-To Name", $_POST['srchBillToName'], "U", $_POST['operBillToName'], "A");
	$returnValue=Build_WildCard("IVARPO", "Reference Number", $_POST['srchPONumber'], "U", $_POST['operPONumber'], "A");
	$returnValue=Range_WildCard("IVORD", "Order Number", $_POST['frOEOrder'], $_POST['toOEOrder'], "", $_POST['operOEOrder'], "N");
	$returnValue=Build_WildCard("IVMORD", "Mfg Order", $_POST['srchMfgOrder'], "U", $_POST['operMfgOrder'], "A");
	$returnValue=Range_WildCard("IVLOC", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Build_WildCard("Upper(LOLNA1)", "Location Name", $_POST['srchLocationName'], "U", $_POST['operLocationName'], "A");
	$returnValue=Range_WildCard("IVSLSM", "Salesman", $_POST['frSalesman'], $_POST['toSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Build_WildCard("Upper(SMSNA1)", "Salesman Name", $_POST['srchSalesmanName'], "U", $_POST['operSalesmanName'], "A");
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

$formName = "Search";  // Need to Calendar Include
require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

require_once 'ApplCashCustomerJava.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "APPLCASHINVOICE";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$BMBCHT=RetValue("(BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank)", "ARPBCH", "BMBCHT");
require_once 'ApplCashCustomerTabInclude.php';

$uv_CustomerName ="IVBLTO";
$uv_CustomerClassName ="CMCCLS";
$uv_RegionName ="CMCRGN";
$uv_BillingLocationName = "IVLOC";
$uv_SalesmanName = "IVSLSM";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select IVISEQ,IVAINV,IVIVDT,IVDUED,IVBLTO,IVCUST,IVARPO,IVORD,IVPLT,IVMORD,IVLOC,IVSLSM,IVCURT,IVCURD ";
$stmtSQL .= "       ,Case When IVIVAM-IVNPOS-IVPPOS=0 Then 0 Else IVDSCT-IVDSTK End as IVDSCT ";
$stmtSQL .= "       ,IVIVAM-IVNPOS-IVPPOS as IVBALC ";
$stmtSQL .= "       ,Coalesce(CIIBCH,0) as CIIBCH, Coalesce(CIIDTE,0) as CIIDTE ";
$stmtSQL .= "       ,Coalesce(CIIBNK,0) as CIIBNK, Coalesce(CIIUSR,' ') as CIIUSR ";
$stmtSQL .= "       ,Coalesce(CITYPE,' ') as CITYPE, Coalesce(CIID,0) as CIID ";
$stmtSQL .= "       ,Coalesce(CMCNA1,' ') as CMCNA1, Coalesce(CMCNA1U,' ') as CMCNA1U ";
$stmtSQL .= "       ,Coalesce(LOLNA1,' ') as LOLNA1, Coalesce(upper(LOLNA1),' ') as LOLNA1U ";
$stmtSQL .= "       ,Coalesce(SMSNA1,' ') as SMSNA1, Coalesce(upper(SMSNA1),' ') as SMSNA1U ";
$stmtSQL .= "       ,Coalesce(USDESC,' ') as USDESC, Coalesce(USDESCU,' ') as USDESCU ";
$stmtSQL .= "       ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
if ($HDOERL<=0) {
	$stmtSQL .= "   ,0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
} else {
	$stmtSQL .= "   ,(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(IVAINV,IVIVDT,IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
	$stmtSQL .= "   ,(Select Count(*) From OEORHH Where HHLIV#=IVAINV and HHBLTO=IVBLTO) as OEHISTORY " ;
	$stmtSQL .= "   ,(Select Count(*) From OEORHH Where IVORD<>0 and HHORD#=IVORD and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
}
$fileSQL .= " HDINVC z ";
$fileSQL .= " left join HDCUSI on CICUST=IVBLTO";
$fileSQL .= " left join HDCUST on CMCUST=IVBLTO ";
$fileSQL .= " left join HDTRMS on TMCTRM=IVTRMS ";
$fileSQL .= " left join HDLCTN on LOLOC#=IVLOC  ";
$fileSQL .= " left join HDSLSM on SMSLSM=IVSLSM ";
$fileSQL .= " left join SYUSER on USUSER=CIIUSR ";
$fileSQL .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=IVORD and HHLIV#=IVAINV ";
if (!$viewCheckBox[0] && !$viewCheckBox[1]) {$selectSQL .= " IVIVAM<>IVIVAM";}
else {
	if ($HDMCRL>0 && $CRPRMC=="Y") {$selectSQL .= " (IVCURT,IVCURD)=('$BKCURT','$CFCURT') ";}
	if ($viewCheckBox[0] && $viewCheckBox[1]) {
		if (($wildCardSearch!="" || $uv_Sql!="") && $selectSQL=="") {$selectSQL .= " IVBLTO=IVBLTO ";}
	} else {
		$viewCheckSQL = Build_CheckBoxSQL($viewCheckBoxDef, $viewCheckBox);
		if ($selectSQL == "") {$selectSQL  = $viewCheckSQL;}
		else                  {$selectSQL .= " and $viewCheckSQL ";}
	}
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"IVAINV|null|Invoice|N|\" title=\"Invoice\" SELECTED>Invoice";
	$qsOpt .= "\n <option value=\"IVIVDT|DATE|Invoice Date|D|\" title=\"Invoice Date\">Invoice Date";
	$qsOpt .= "\n <option value=\"IVDUED|DATE|Due Date|I|\" title=\"Due Date\">Due Date";
	$qsOpt .= "\n <option value=\"IVIVAM-IVNPOS-IVPPOS|null|Invoice Balance|N|\" title=\"Invoice Balance\">Invoice Balance";
	$qsOpt .= "\n <option value=\"IVIVAM|null|Invoice Amount|N|\" title=\"Invoice Amount\">Invoice Amount";
	$qsOpt .= "\n <option value=\"Case When IVIVAM-IVNPOS-IVPPOS=0 Then 0 Else IVDSCT-IVDSTK End|null|Discount|N|\" title=\"Discount\">Discount";
	$qsOpt .= "\n <option value=\"IVBLTO|null|Bill-To|N|\" title=\"Bill-To\">Bill-To";
	$qsOpt .= "\n <option value=\"CMCNA1U|null|Bill-To Name|A|U\" title=\"Bill-To Name\">Bill-To Name";
	$qsOpt .= "\n <option value=\"IVARPO|null|Reference Number|A|U\" title=\"Reference Number\">Reference Number";
	$qsOpt .= "\n <option value=\"IVORD|null|Order Number|N|\" title=\"Order Number\">Order Number";
	$qsOpt .= "\n <option value=\"IVMORD|null|Mfg Order|A|U\" title=\"Mfg Order\">Mfg Order";
	$qsOpt .= "\n <option value=\"IVLOC|null|Location|N|\" title=\"Location\">Location";
	$qsOpt .= "\n <option value=\"Upper(LOLNA1)|null|Location Name|A|U\" title=\"Location Name\">Location Name";
	$qsOpt .= "\n <option value=\"IVSLSM|null|Salesman|N|\" title=\"Salesman\">Salesman";
	$qsOpt .= "\n <option value=\"Upper(SMSNA1)|null|Salesman Name|A|U\" title=\"Salesman Name\">Salesman Name";
	$qsOpt .= "\n <option value=\"Coalesce(CIIBCH,0)|null|In Use By Batch|A|U\" title=\"In Use By Batch\">In Use By Batch";
	$qsOpt .= "\n <option value=\"Coalesce(USDESCU,' ')|null|In Use By User|A|U\" title=\"In Use By User\">In Use By User";
	require 'QuickSearchOption.php';
}

print "\n <table $contentTable> <tr>";
if ($formatToPrint != "Y"){
	print "\n <th class=\"colhdr\">$optionHeading</th>";
}

$returnValue=OrderBy_Sort("IVAINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\" title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th>";
$returnValue=OrderBy_Sort("IVIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvDate\" title=\"Sequence By Invoice Date\">{$sortPoint}Invoice Date</a></th>";
$returnValue=OrderBy_Sort("IVDUED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DueDate\" title=\"Sequence By Due Date\">{$sortPoint}Due Date</a></th>";
$returnValue=OrderBy_Sort("IVBALC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Balance\" title=\"Sequence By Invoice Balance\">{$sortPoint}Invoice Balance</a></th> ";
$returnValue=OrderBy_Sort("IVDSCT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Discount\" title=\"Sequence By Discount\">{$sortPoint}Discount</a></th> ";
$returnValue=OrderBy_Sort("IVBLTO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Customer\" title=\"Sequence By Bill-To\">{$sortPoint}Bill-To</a></th>";
$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\"      title=\"Sequence By Bill-To Name\">{$sortPoint}Bill-To Name</a></th>";
$returnValue=OrderBy_Sort("IVARPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PO\"        title=\"Sequence By Reference Number\">{$sortPoint}Reference Number</a></th>";
$returnValue=OrderBy_Sort("IVORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OEOrder\"   title=\"Sequence By Order Number\">{$sortPoint}Order Number</a></th>";
$returnValue=OrderBy_Sort("IVMORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=MfgOrder\"  title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th>";
$returnValue=OrderBy_Sort("IVLOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Loc\"     title=\"Sequence By Location\">{$sortPoint}Loc</a></th>";
$returnValue=OrderBy_Sort("LOLNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LocName\"     title=\"Sequence By Location Name\">{$sortPoint}Location Name</a></th>";
$returnValue=OrderBy_Sort("IVSLSM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Slsm\"     title=\"Sequence By Salesman\">{$sortPoint}Slsm</a></th>";
$returnValue=OrderBy_Sort("SMSNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SlsmName\"      title=\"Sequence By Salesman Name\">{$sortPoint}Salesman Name</a></th>";
$returnValue=OrderBy_Sort("CIIBCH"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InUse\"     title=\"Sequence By In Use By Batch\">{$sortPoint}In Use By Batch</a></th>";
$returnValue=OrderBy_Sort("USDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\"      title=\"Sequence By In Use By User\">{$sortPoint}In Use By User</a></th>";
print "\n </tr>";

$rowCount = 0;
$beginRow=$startRow;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	$row['CIIUSR']=trim($row['CIIUSR']);
	$row['CITYPE']=trim($row['CITYPE']);
	$maintainVar = "{$scriptVarBase}&amp;fromType=" . $fromType . "&amp;fromID=" . urlencode(trim($row['IVBLTO'])) . "&amp;fromInvSeq=" . urlencode(trim($row['IVISEQ'])) . "&amp;fromScript=" . urlencode(trim($scriptName));
	$maintainVarD2w = "{$altVarBase}&amp;fromType=" . $fromType . "&amp;fromID=" . urlencode(trim($row['IVBLTO'])) . "&amp;fromInvSeq=" . urlencode(trim($row['IVISEQ'])) . "&amp;fromScript=" . urlencode(trim($scriptName));

	require  'SetRowClass.php';
	$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
	$F_IVDUED=Format_Date_ISO($row['IVDUED'], "D");
	$confirmDesc = Format_Confirm_Desc("$row[IVBLTO]", "", "", "", "", "");
	print "\n <tr class=\"$rowClass\">";
	if ($formatToPrint != "Y"){
		print "\n <td class=\"opticon\">";
		if ((trim($row['CIIUSR'])=="" || $row['CIIBCH']==$fromBatchNumber && $row['CIIDTE']==$fromBatchDate && $row['CIIBNK']==$fromBatchBank && $row['CIIUSR']==$userProfile && $row['CITYPE']==$fromType && $row['CIID']==$fromID)){
			print "\n <a onClick=\"return CustomerPAYMENT('$row[IVBLTO]')\" href=\"javascript:NewWindow('{$homeURL}{$phpPath}ApplCashPaymentDocument.php{$maintainVar}&amp;tag=REPORT','arDocument_win','$arDocumentWinPctH','$arDocumentWinPctW','$arDocumentWinSB','$arDocumentWinRZ','$arDocumentWinTB','$arDocumentWinMB','$arDocumentWinST')\">$arCashPmtImageSml</a>";
		}
		print "\n </td>";
	}
	if ($row['OEINVCOUNT']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['IVAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['IVIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[IVAINV]</a></td> ";}
	else                      {print "\n <td class=\"colnmbr\">$row[IVAINV]</td> ";}
	print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['IVISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_IVIVDT</a></td> ";
	print "\n <td class=\"coldate\">$F_IVDUED</td>";
	print "\n <td class=\"colnmbr\">" . number_format($row['IVBALC'],2) . "</td>";
	print "\n <td class=\"colnmbr\">" . number_format($row['IVDSCT'],2) . "</td>";
	print "\n <td class=\"colnmbr\">$row[IVBLTO]</td>";
	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[IVBLTO]]\">$row[CMCNA1]</a></td> ";
	print "\n <td class=\"colalph\">$row[IVARPO]</td>";
	if     ($row['OESELECT']>0)  {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['IVORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View Shipment\">$row[IVORD]</a></td> ";}
	elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['IVBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['IVAINV'])) . "&amp;nMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View Customer Order History\">$row[IVORD]</a></td> ";}
	elseif ($row['IVORD']<>0)    {print "\n <td class=\"colnmbr\">$row[IVORD]</td> ";}
	else                         {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
	if (trim($row['IVMORD']) != "") {$mfgOrderCnt=RetValue("(OHPLT,OHORD)=($row[IVPLT],'$row[IVMORD]')", "HDMOHM", "CHAR(COUNT(OHORD))");}
	else                            {$mfgOrderCnt=0;}
	if ($mfgOrderCnt>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['IVPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['IVMORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[IVMORD]</a></td> ";}
	else                {print "\n <td class=\"colalph\">$row[IVMORD]</td> ";}
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['IVLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location\">$row[IVLOC]</a></td> ";
	print "\n <td class=\"colalph\">$row[LOLNA1]</td>";
	print "\n <td class=\"colnmbr\">$row[IVSLSM]</td>";
	print "\n <td class=\"colalph\">$row[SMSNA1]</td>";
	print "\n <td class=\"colnmbr\">$row[CIIBCH]</td>";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[CIIUSR]\">$row[USDESC]</span></td>";
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