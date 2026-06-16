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
$backHome           = $_GET['backHome'];

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

$page_title     = "Application of Cash: Batch Pending Payments";
$scriptName     = "ApplCashBatchReview.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;backHome=" . urlencode(trim($backHome)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$altScriptVarBase = "{$altVarBase}&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("CMCNA1U","A","Customer Name"),array("PESINV","A","Invoice"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	$fromToSearch = "Y";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.$formName.frPaymentAmt, 11, 2) ";
	print "\n    && editNum(document.$formName.toPaymentAmt, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frPaymentAmt, document.$formName.toPaymentAmt, document.$formName.operPaymentAmt, 15) ";
	print "\n    && editNum(document.$formName.frDiscount, 11, 2) ";
	print "\n    && editNum(document.$formName.toDiscount, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frDiscount, document.$formName.toDiscount, document.$formName.operDiscount, 15) ";
	print "\n    && editNum(document.$formName.frInvoice, 7, 0) ";
	print "\n    && editNum(document.$formName.toInvoice, 7, 0) ";
	print "\n    && editFromToOper(document.$formName.frInvoice, document.$formName.toInvoice, document.$formName.operInvoice, 7) ";
	print "\n    && editdate(document.$formName.frInvDate) ";
	print "\n    && editdate(document.$formName.toInvDate) ";
	print "\n    && editFromToOper(document.$formName.frInvDate, document.$formName.toInvDate, document.$formName.operInvDate, 'D') ";
	print "\n    && editNum(document.$formName.frCustomer, 7, 0) ";
	print "\n    && editNum(document.$formName.toCustomer, 7, 0) ";
	print "\n    && editFromToOper(document.$formName.frCustomer, document.$formName.toCustomer, document.$formName.operCustomer, 7) ";
	print "\n    && editNum(document.$formName.frLocation, 3, 0) ";
	print "\n    && editNum(document.$formName.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.$formName.frLocation, document.$formName.toLocation, document.$formName.operLocation, 3) ";
	print "\n    && editNum(document.$formName.frPayer, 7, 0) ";
	print "\n    && editNum(document.$formName.toPayer, 7, 0) ";
	print "\n    && editFromToOper(document.$formName.frPayer, document.$formName.toPayer, document.$formName.operPayer, 7) ";
	print "\n    && editNum(document.$formName.frOrderNumber, 8, 0) ";
	print "\n    && editNum(document.$formName.toOrderNumber, 8, 0) ";
	print "\n    && editFromToOper(document.$formName.frOrderNumber, document.$formName.toOrderNumber, document.$formName.operOrderNumber, 8) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHBATCHSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Payment Amount","frPaymentAmt","toPaymentAmt","operPaymentAmt","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Discount","frDiscount","toDiscount","operDiscount","opersel_num2_short","N","15","15");

	$operNbr = "operSubCode";
	print "\n <tr><td class=\"dsphdr\">Payment Code</td>";
	print "\n     <td>"; require "OperSel_Alph_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchSubCode\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frSubCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Payment Code Description","srchSubCodeDesc","","operSubCodeDesc","OperSel_Alph_Short","A","20","30");
	Build_AdvSrch_Entry("Invoice","frInvoice","toInvoice","operInvoice","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Invoice Date","frInvDate","toInvDate","operInvDate","opersel_num2_short","D","6","6");

	$operNbr = "operCustomer";
	print "\n <tr><td class=\"dsphdr\">Customer</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frCustomer\" size=\"7\" maxlength=\"7\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frCustomer&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toCustomer\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toCustomer&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Customer Name","srchCustName","","operCustName","OperSel_Alph_Short","A","20","30");
	Build_AdvSrch_Entry("Document","srchDocument","","operDocument","OperSel_Alph_Short","A","15","15");
	Build_AdvSrch_Entry("Comment","srchComment","","operComment","opersel_alph_short","A","15","69");
	Build_AdvSrch_Entry("Memo","srchMemo","","operMemo","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Reference Number","srchReferenceNumber","","operReferenceNumber","opersel_alph_short","A","15","22");

	$operNbr = "operLocation";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Location Name","srchLocName","","operLocName","OperSel_Alph_Short","A","20","30");

	$operNbr = "operPayer";
	print "\n <tr><td class=\"dsphdr\">Payer</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frPayer\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PayerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frPayer&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toPayer\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PayerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toPayer&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Payer Name","srchPayerName","","operPayerName","OperSel_Alph_Short","A","20","30");

	Build_AdvSrch_Entry("Order Number","frOrderNumber","toOrderNumber","operOrderNumber","opersel_num2_short","N","8","8");
	Build_AdvSrch_Entry("Mfg Order","srchMfgOrder","","operMfgOrder","opersel_alph_short","A","9","9");
	$focusField = "frPaymentAmt";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "PaymentAmt")      {$orby = array(array("PEAMT","A","Payment Amount"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "Discount")        {$orby = array(array("PEDAMT","A","Discount"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "PaymentCode")     {$orby = array(array("PSDESCU","A","Payment Code"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "Invoice")         {$orby = array(array("PESINV","A","Invoice"),array("CMCNA1U","A","Customer"));}
	elseif ($sequence == "InvoiceDate")     {$orby = array(array("PEIVDT","A","Invoice Date"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "Customer")        {$orby = array(array("CMCNA1U","A","Customer"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "Document")        {$orby = array(array("PEMCHK","A","Document"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "Comment")         {$orby = array(array("HAS_PECMNT","A","Comment"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "Memo")            {$orby = array(array("PEMEMO","A","Memo"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "ReferenceNumber") {$orby = array(array("IVARPO","A","Reference Number"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "Location")        {$orby = array(array("PELOC","A","Location"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "Payer")           {$orby = array(array("PYPYNMU","A","Payer"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "OrderNumber")     {$orby = array(array("PEORD","A","Order Number"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "MfgOrder")        {$orby = array(array("PEMORD","A","Mfg Order"),array("PESINV","A","Invoice"));}
	elseif ($sequence == "TypePayment")     {$orby = array(array("PEPMID" ,"A","Transaction Type"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "Errors")          {$orby = array(array("ARPYENERROR","A","Number of Errors"),array("PESINV","A","Invoice"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Range_WildCard("PEAMT", "Payment Amount", $_POST['frPaymentAmt'], $_POST['toPaymentAmt'], "", $_POST['operPaymentAmt'], "N");
	$returnValue=Range_WildCard("PEDAMT", "Discount", $_POST['frDiscount'], $_POST['toDiscount'], "", $_POST['operDiscount'], "N");
	$returnValue=Build_WildCard("PESBCD", "Payment Code", $_POST['srchSubCode'], "U", $_POST['operSubCode'], "A");
	$returnValue=Build_WildCard("PSDESCU", "Payment Code Description", $_POST['srchSubCodeDesc'], "U", $_POST['operSubCodeDesc'], "A");
	$returnValue=Range_WildCard("Coalesce(IVAINV,PESINV)", "Invoice", $_POST['frInvoice'], $_POST['toInvoice'], "", $_POST['operInvoice'], "N");
	$returnValue=Range_WildCard("Coalesce(IVIVDT,PEBCHD)", "Invoice Date", $_POST['frInvDate'], $_POST['toInvDate'], "", $_POST['operInvDate'], "D");
	$returnValue=Range_WildCard("Coalesce(IVBLTO,PEBLTO)", "Customer", $_POST['frCustomer'], $_POST['toCustomer'], "", $_POST['operCustomer'], "N");
	$returnValue=Build_WildCard("Coalesce(CMCNA1U,' ')", "CustomerName", $_POST['srchCustName'], "U", $_POST['operCustName'], "A");
	$returnValue=Build_WildCard("Case When PEPTYP='M' Then PEMCHK Else PECHK End", "Document", $_POST['srchDocument'], "U", $_POST['operDocument'], "A");
	$returnValue=Build_WildCard ("Upper(PECMNT)", "Comment", $_POST['srchComment'], "U", $_POST['operComment'], "A");
	$returnValue=Build_WildCard ("PEMEMO", "Memo", $_POST['srchMemo'], "U", $_POST['operMemo'], "A");
	$returnValue=Build_WildCard("Coalesce(IVARPO,PEARPO)", "Reference Number", $_POST['srchReferenceNumber'], "U", $_POST['operReferenceNumber'], "A");
	$returnValue=Range_WildCard("Coalesce(IVLOC,PELOC)", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Build_WildCard("Coalesce(Upper(LOLNA1), ' ')", "Location Name", $_POST['srchLocName'], "U", $_POST['operLocName'], "A");
	$returnValue=Range_WildCard("Case When PETYPE='P' Then PEID Else 0 End", "Payer", $_POST['frPayer'], $_POST['toPayer'], "", $_POST['operPayer'], "N");
	$returnValue=Build_WildCard("Coalesce(PYPYNMU, ' ')", "Payer Name", $_POST['srchPayerName'], "U", $_POST['operPayerName'], "A");
	$returnValue=Range_WildCard("Coalesce(IVORD ,PEORD )", "Order Number", $_POST['frOrderNumber'], $_POST['toOrderNumber'], "", $_POST['operOrderNumber'], "N");
	$returnValue=Build_WildCard("PEMORD", "Mfg Order", $_POST['srchMfgOrder'], "U", $_POST['operMfgOrder'], "A");

	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

if ($tag != "EXPORT"){
	require_once ($docType);
	print "\n <html> <head> ";
	$formName = "Search";
	$F_fromBatchDate=Format_Date($fromBatchDate, "D");
	$title="$fromBatchNumber $F_fromBatchDate $fromBatchBank";
	require_once ($headInclude);

	print "\n \n <script TYPE=\"text/javascript\">";
	require_once 'AJAXRequest.js';
	require_once 'CheckSel.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NoFormValidate.php';
	require_once 'NumEdit.php';
	require_once 'SaveCurrentURL.php';
	require_once 'ShowHideSelCriteria.php';
	print "\n </script> ";

	require_once ($genericHead);
	print "\n    </head> ";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID="APPLCASHBATCHREVIEW";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";

	// *****************************************************************************
	// Batch Heading
	// *****************************************************************************
	$uv_BankName= "BMBCHB";
	require 'userview.php';

	require 'stmtSQLClear.php';
	$appendWildCard="N";  // Do not append wildCardSearch
	$stmtSQL   .= " Select  BMBCHN, BMBCHD, BMBCHB, BMBCHS, ";
	$stmtSQL   .= " BMDEPA, BMDEPE, BMDEPP, BMDEPD,  ";
	$stmtSQL   .= " BMADJT, BMADJE, BMADJP, ";
	$stmtSQL   .= " Coalesce(BKBKNM,' ') as BKBKNM, ";
	$stmtSQL   .= " Coalesce(c.FLDESC,' ') as FLDESC_BMBCHS ";
	$fileSQL   .= " ARPBCH ";
	$fileSQL   .= " left join HDBANK on BKBANK=BMBCHB ";
	$fileSQL   .= " left join SYFLAG c on (c.FLTYPE,c.FLVALU)=('ARBCHSTAT',BMBCHS) ";
	$selectSQL .= " (BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);
	if (! $row) {require 'UserViewErrorInclude.php'; Exit;}

	print "\n <table $contentTable>";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\">";
	print "\n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y") {
		print "\n <td> ";
		require_once 'FormatToPrint.php';
		require_once 'HelpPage.php';
		print "\n </td> ";
	}
	print "\n </tr> ";
	print "\n </table> ";
	require_once 'ConfMessageDisplay.php';

	print $hrTagAttr;

	print "\n <table $contentTable>";
	$F_BMBCHD=Format_Code(Format_Date($row['BMBCHD'], "D"));
	Build_DspFld("Batch","$row[BMBCHN] $F_BMBCHD","","A");
	$F_BMBCHB=Format_Code($row['BMBCHB']);
	Build_DspFld("Bank","$row[BKBKNM] $F_BMBCHB","","A");
	$F_BMBCHS=Format_Code($row['BMBCHS']);
	Build_DspFld("Batch Status","$row[FLDESC_BMBCHS] $F_BMBCHS","","A");
	print "\n </table> ";

	print "\n <table $contentTable>";
	print "\n <tr><td class=\"dsphdr\">&nbsp;</td> ";
	print "\n     <td class=\"colhdr\">Total</td> ";
	print "\n     <td class=\"colhdr\">Posted</td> ";
	print "\n     <td class=\"colhdr\">Pending</td> ";
	print "\n     <td class=\"colhdr\">Variance</td> ";
	print "\n </tr> ";

	$result=$row['BMDEPA'] - ($row['BMDEPP'] + $row['BMDEPE']);
	print "\n <tr><td class=\"dsphdr\">Deposit</td> ";
	print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMDEPA'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMDEPP'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMDEPE'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $result, '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n </tr> ";

	$result=$row['BMADJT'] - ($row['BMADJP'] + $row['BMADJE']);
	print "\n <tr><td class=\"dsphdr\">Other</td> ";
	print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMADJT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMADJP'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMADJE'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $result, '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n </tr> ";

	print "\n </table> ";

	print $hrTagAttr;
}

// *****************************************************************************
// Payments
// *****************************************************************************
$uv_BankName ="PEBCHB";
$uv_CustomerName ="@@BLTO";
$uv_CustomerClassName ="CMCCLS";
$uv_RegionName ="CMCRGN";
$uv_BillingLocationName = "@@LOC";
$uv_SalesmanName = "@@SLSM";
require 'UserView.php';
if ($uv_Sql!="") {
	$uv_Sql=str_replace('@@BLTO','Coalesce(IVBLTO,PEBLTO)',$uv_Sql);
	$uv_Sql=str_replace('@@LOC','Coalesce(IVLOC,PELOC)',$uv_Sql);
	$uv_Sql=str_replace('@@SLSM','Coalesce(IVSLSM,PESLSM)',$uv_Sql);
	$uv_Sql="PEUSER='$userProfile' or $uv_Sql";
}

require 'stmtSQLClear.php';
$moreScript = "ApplCashBatchReview.php";
$dftOrderBy = array(array("PETYPE","A","Type"),array("PEID","A","ID"),array("PEMCHK","A","Document"));
Retrieve_Filter($moreScript);
if ($tag == "EXPORT") {
	$stmtSQL   .= " Select ARPYEN.*  ";
} else {
	$stmtSQL .= " Select PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,PECHK,PEPLT,PEMORD,PEMEMO  ";
	$stmtSQL .= "       ,PEPTYP,PEPMID,PEISEQ,PEENID,PECRTB,PEAMT,PEDAMT,PESBCD  ";
	$stmtSQL .= "         ,PECMNT,Case When PECMNT<>' ' Then 'Y' Else ' ' End HAS_PECMNT ";
	$stmtSQL .= "       ,Case When PEPTYP='M' Then PEMCHK Else PECHK End as PEMCHK  ";
	$stmtSQL .= "       ,Case When PETYPE='P' Then PEID Else 0 End as PEPAYR ";
	$stmtSQL .= "       ,Coalesce(IVBLTO,PEBLTO) as IVBLTO ";
	$stmtSQL .= "       ,Coalesce(IVAINV,PESINV) as PESINV ";
	$stmtSQL .= "       ,Coalesce(IVIVDT,PEBCHD) as PEIVDT ";
	$stmtSQL .= "       ,Coalesce(IVLOC, PELOC ) as PELOC ";
	$stmtSQL .= "       ,Coalesce(IVORD ,PEORD ) as PEORD ";
	$stmtSQL .= "       ,Coalesce(IVARPO,PEARPO) as IVARPO ";
}
$stmtSQL .= " ,Coalesce(PYPYNM,' ') as PYPYNM, Coalesce(PYPYNMU,' ') as PYPYNMU ";
$stmtSQL .= " ,Coalesce(PSDESC,' ') as PSDESC, Coalesce(PSDESCU,' ') as PSDESCU ";
$stmtSQL .= " ,Coalesce(CMCNA1,' ') as CMCNA1, Coalesce(CMCNA1U,' ') as CMCNA1U ";
$stmtSQL .= " ,Coalesce(LOLNA1,' ') as LOLNA1 ";
$stmtSQL .= " ,Coalesce(b.FLDESC,' ') as PEPMID_FLDESC ";
$stmtSQL .= " ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
$stmtSQL .= " ,Coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,PRCHK,PRPTYP,PRPMID,PRISEQ,PRENID)=(z.PEBCHN,z.PEBCHD,z.PEBCHB,z.PETYPE,z.PEID,z.PECHK,z.PEPTYP,z.PEPMID,z.PEISEQ,z.PEENID)),0) as ARPYENERROR ";
if ($HDOERL<=0) {
	$stmtSQL .= ",0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
} else {
	$stmtSQL .= ",(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(IVAINV,IVIVDT,IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where HHLIV#=IVAINV and HHBLTO=IVBLTO) as OEHISTORY " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where coalesce(IVORD,PEORD)<>0 and HHORD#=coalesce(IVORD,PEORD) and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
}
if ($HDPDRL<=0) {
	$stmtSQL .= ",0 as MFGORDCOUNT " ;
} else {
	$stmtSQL .= ",(Select Count(*) From HDMOHM Where (OHPLT,OHORD)=(PEPLT,PEMORD)) as MFGORDCOUNT " ;
}
$fileSQL   .= " ARPYEN z ";
$fileSQL   .= " left join HDINVC on IVISEQ=PEISEQ ";
$fileSQL   .= " left join ARPYRH on PYPAYR=Case When PETYPE='P' Then PEID Else 0 End ";
$fileSQL   .= " left join ARPYSB on PSSBCD=PESBCD ";
$fileSQL   .= " left join HDCUST on CMCUST=Coalesce(IVBLTO,PEBLTO) ";
$fileSQL   .= " left join HDLCTN on LOLOC#=Coalesce(IVLOC, PELOC) ";
$fileSQL   .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTID',PEPMID) ";
$fileSQL   .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=coalesce(IVORD,PEORD) and HHLIV#=IVAINV ";
$selectSQL .= " (PEBCHN,PEBCHD,PEBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
require 'stmtSQLSelect.php';
if ($orderBy=="") {$orderBy="CMCNA1U, PESINV";}
$stmtSQL   .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	if ($formatToPrint == ""){
		$qsOpt = "";
		$qsOpt .= "\n <option value=\"PEAMT|null|Payment Amount|N|\" title=\"Payment Amount\">Payment Amount";
		$qsOpt .= "\n <option value=\"PEDAMT|null|Discount|N|\" title=\"Discount\">Discount";
		$qsOpt .= "\n <option value=\"PESBCD|null|Payment Code|A|U\" title=\"Payment Code\">Payment Code";
		$qsOpt .= "\n <option value=\"Coalesce(PSDESCU,' ')|null|Payment Code Description|A|U\" title=\"Payment Code Description\">Payment CodeDescription";
		$qsOpt .= "\n <option value=\"Coalesce(IVAINV,PESINV)|null|Invoice|N|\" title=\"Invoice\" SELECTED>Invoice";
		$qsOpt .= "\n <option value=\"Coalesce(IVIVDT,PEBCHD)|DATE|Invoice Date|D|\" title=\"Invoice Date\">Invoice Date";
		$qsOpt .= "\n <option value=\"Coalesce(IVBLTO,PEBLTO)|null|Customer|N|\" title=\"Customer\">Customer";
		$qsOpt .= "\n <option value=\"Coalesce(CMCNA1U,' ')|null|Customer Name|A|U\" title=\"Customer Name\">Customer Name";
		$qsOpt .= "\n <option value=\"Case When PEPTYP='M' Then PEMCHK Else PECHK End|null|Document|A|U\" title=\"Document\">Document";
		$qsOpt .= "\n <option value=\"Upper(PECMNT)|null|Comment|A|U\" title=\"Comment\">Comment";
		$qsOpt .= "\n <option value=\"PEMEMO|null|Memo|A|U\" title=\"Memo\">Memo";
		$qsOpt .= "\n <option value=\"Coalesce(IVARPO,PEARPO)|null|Reference Number|A|U\" title=\"Reference Number\">Reference Number";
		$qsOpt .= "\n <option value=\"Coalesce(IVLOC,PELOC)|null|Location|N|\" title=\"Location\">Location";
		$qsOpt .= "\n <option value=\"Coalesce(Upper(LOLNA1), ' ')|null|Location Name|A|U\" title=\"Location Name\">Location Name";
		$qsOpt .= "\n <option value=\"Case When PETYPE='P' Then PEID Else 0 End|null|Payer|N|\" title=\"Payer\">Payer";
		$qsOpt .= "\n <option value=\"Coalesce(PYPYNMU, ' ')|null|Payer Name|A|U\" title=\"Payer Name\">Payer Name";
		$qsOpt .= "\n <option value=\"Coalesce(IVORD ,PEORD )|null|Order Number|N|\" title=\"Order Number\">Order Number";
		$qsOpt .= "\n <option value=\"PEMORD|null|Mfg Order|A|U\" title=\"Mfg Order\">Mfg Order";

		require 'QuickSearchOption.php';
	}
	print "\n <table $contentTable>";

	print "\n <tr> ";
	$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PaymentAmt\"      title=\"Sequence By Payment Amount\">{$sortPoint}Payment Amount</a></th>";
	$returnValue=OrderBy_Sort("PEDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Discount\"      title=\"Sequence By Discount\">{$sortPoint}Discount</a></th>";
	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PaymentCode\"      title=\"Sequence By Payment Code\">{$sortPoint}Payment Code</a></th>";
	$returnValue=OrderBy_Sort("PESINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\"      title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th>";
	$returnValue=OrderBy_Sort("PEIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvoiceDate\"      title=\"Sequence By Invoice Date\">{$sortPoint}Invoice Date</a></th>";
	$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Customer\"      title=\"Sequence By Customer\">{$sortPoint}Customer</a></th>";
	$returnValue=OrderBy_Sort("PEMCHK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Document\"      title=\"Sequence By Document\">{$sortPoint}Document</a></th>";
	$returnValue=OrderBy_Sort("HAS_PECMNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Comment\" title=\"Sequence By Comment\">{$sortPoint}Cmt</a></th> ";
	$returnValue=OrderBy_Sort("PEMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Memo\" title=\"Sequence By Memo\">{$sortPoint}Memo</a></th> ";
	$returnValue=OrderBy_Sort("IVARPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ReferenceNumber\"      title=\"Sequence By Reference Number\">{$sortPoint}Reference Number</a></th>";
	$returnValue=OrderBy_Sort("PELOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Location\" title=\"Sequence By Location, Invoice, Invoice Date, Customer\">{$sortPoint}Loc</a></th>";
	$returnValue=OrderBy_Sort("PYPYNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Payer\" title=\"Sequence By Payer, Invoice, Invoice Date, Customer\">{$sortPoint}Payer</a></th>";
	$returnValue=OrderBy_Sort("PEORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OrderNumber\"      title=\"Sequence By Order Number\">{$sortPoint}Order Number</a></th>";
	$returnValue=OrderBy_Sort("PEMORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=MfgOrder\" title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th> ";
	$returnValue=OrderBy_Sort("PEPMID"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TypePayment\"   title=\"Sequence By Transaction Type\">{$sortPoint}Trans Type</a></th>";
	$returnValue=OrderBy_Sort("ARPYENERROR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Errors\"      title=\"Sequence By Number of Errors\">{$sortPoint}Number of Errors</a></th>";
	print "\n </tr> ";
}

if ($tag == "EXPORT"){$xmlListName = "ApplCashBatchList"; require_once 'XMLInit.php';}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	if ($tag == "EXPORT"){
		$xmlID  = $xmlDoc->createElement(ApplCashBatch); $xmlRoot->appendChild($xmlID);
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Batch"));  $xmlTag->appendChild($xmlDoc->createTextNode($row['PEBCHN']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("BatchDate"));  $xmlTag->appendChild($xmlDoc->createTextNode($row['PEBCHD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("BatchBank"));  $xmlTag->appendChild($xmlDoc->createTextNode($row['PEBCHB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CustomerPayerType"));  $xmlTag->appendChild($xmlDoc->createTextNode($row['PETYPE']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CustomerPayerID")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEID']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Document")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PECHK']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PaymentType")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEPTYP']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("TypeOfPayment")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEPMID']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("InvoiceSequence")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEISEQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("EntryID")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEENID']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("EditPayment")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEEDIT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CreatedBy")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PECRTB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("SelectedPaymentOrder")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PESSEQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("SelectedInvoiceNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PESINV']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("SelectedForPayment")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PESPMT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PaymentAmount")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEAMT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("SelectedForCalculate")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PESCLC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("DiscountAmount")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEDAMT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("SelectedForDiscount")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PESDSC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("MiscellaneousDocument")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEMCHK']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("GeneralDeduction")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEGDED']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Invoice")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PENINV']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PaymentSubCode")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PESBCD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ReferenceNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEARPO']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Memo")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEMEMO']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Location")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PELOC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("BillToCustomer")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEBLTO']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ShipToCustomer")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PESHTO']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CashAccountNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PECSAC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CashSubaccountNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PECSSB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ARAccountNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEARAC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ARSubaccountNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEARSB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OffsetCompanyNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEOFCO']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OffsetFacilityNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEOFFC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OffsetAccountNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEOFAC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OffsetSubaccountNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEOFSB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OrderNumber ")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEORD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OrderDate ")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEORDT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("LineNumber")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEORLN']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Plant")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEPLT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("MfgOrder ")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEMORD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Salesman")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PESLSM']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CustomerTermsCode ")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PETRMS']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ARPaymentHistoryComment")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PECMNT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PayOffPaymentSubCode")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEPOSB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PayOffPaymentType")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEPOTP']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PayOffEntry ID ")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEPOEN']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("User")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PEUSER']));

	} else {
		$maintainVar = "{$scriptVarBase}&amp;fromType=" . urlencode(trim($row['PETYPE'])) . "&amp;fromID=" . urlencode(trim($row['PEID'])) . "&amp;fromDocument=" . urlencode(trim($row['PECHK'])) . "&amp;fromPaymentType=" . urlencode(trim($row['PEPTYP'])) . "&amp;fromPaymentID=" . urlencode(trim($row['PEPMID'])) . "&amp;fromInvoiceSeq=" . urlencode(trim($row['PEISEQ'])) . "&amp;fromEntryID=" . urlencode(trim($row['PEENID'])) . "&amp;fromScript=" . urlencode(trim($scriptName));
		require 'SetRowClass.php';

		$F_PEIVDT=Format_Date($row['PEIVDT'], "D");

		print "\n <tr class=\"$rowClass\"> ";
		print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['PEAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
		print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['PEDAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PESBCD]\">$row[PSDESC]</span></td>";
		if ($row['OEINVCOUNT']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['PESINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['PEIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[PESINV]</a></td> ";}
		else                      {print "\n <td class=\"colnmbr\">$row[PESINV]</td> ";}
		if ($row['PECRTB']=="A") {print "\n <td class=\"colnmbr\">$F_PEIVDT</td> ";}
		else                     {print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['PEISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_PEIVDT</a></td> ";}
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[IVBLTO]]\">$row[CMCNA1]</a></td> ";
		print "\n <td class=\"colalph\">$row[PEMCHK]</td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[PECMNT]\">$row[HAS_PECMNT]</span></td> ";
		print "\n <td class=\"colalph\">$row[PEMEMO]</td> ";
		print "\n <td class=\"colalph\">$row[IVARPO]</td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['PELOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location [$row[LOLNA1]]\">$row[PELOC]</a></td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PEPAYR]\">$row[PYPYNM]</span></td> ";
		if     ($row['OESELECT']>0)  {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['PEORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[PEORD]</a></td> ";}
		elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['IVBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['PESINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[PEORD]</a></td> ";}
		else                         {print "\n <td class=\"colnmbr\">$row[PEORD]</td> ";}
		if ($row['MFGORDCOUNT']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['ARPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['ARMORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[ARMORD]</a></td> ";}
		else                       {print "\n <td class=\"colalph\">$row[ARMORD]</td> ";}
		print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row[PEPMID_FLDESC]) . "\">$row[PEPMID]</span></td>";
		if ($row['ARPYENERROR']==0) {print "\n <td class=\"colnmbr\">$row[ARPYENERROR]</td>";}
		else                        {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashErrorInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Error\">$row[ARPYENERROR]</a></td>";}
		print "\n </tr> ";
	}

	$startRow ++;
	$rowCount ++;
}

require_once 'XMLExport.php';

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "\n </table> ";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>										