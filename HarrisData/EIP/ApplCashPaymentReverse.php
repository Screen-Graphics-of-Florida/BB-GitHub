<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$paymentType        = "V";
$paymentID          = "";
$entryType          = "";

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$fromType           = $_GET['fromType'];
$fromID             = $_GET['fromID'];
$fromDocument       = $_GET['fromDocument'];

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

require_once 'ApplCashInclude.php';

if ($_GET['columnDisplay']) {$columnDisplay = $_GET['columnDisplay'];}
else                        {$columnDisplay = RtvColArray($profileHandle,$paymentType,$userProfile);}

$page_title    = "Application of Cash";
$scriptName    = "ApplCashPaymentReverse.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId)) . "&amp;columnDisplay" . $columnDisplay . "&amp;paymentType=" . urlencode(trim($paymentType)) . "&amp;harcedProgram=HARCED_P{$paymentType}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("IVAINV" ,"A","Invoice"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));
$tabID         = "REVERSE";
$programName   = "HARCED";

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("Invoice:", "Selected", $viewCheckBoxURL, "1", "1"),
array("", "Available For Reverse", $viewCheckBoxURL, "2", "1"),
array("", "Reversed", $viewCheckBoxURL, "3", "0"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "ACCEPT"){
	require 'stmtSQLClear.php';
	$stmtSQL .= " Delete from HDCUSI ";
	$stmtSQL .= " Where (CIIBCH,CIIDTE,CIIBNK,CIIUSR,CITYPE,CIID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$userProfile','$fromType',$fromID) ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	if ($fromType=="C") {print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}ApplCashCustomer.php{$scriptVarBase}&amp;tag=REPORT\"> ";}
	else                {print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}ApplCashPayer.php{$scriptVarBase}&amp;tag=REPORT\"> ";}
	exit;
}

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
	print "\n    && editNum(document.$formName.frPaymentAmt, 11, 2) ";
	print "\n    && editNum(document.$formName.toPaymentAmt, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frPaymentAmt, document.$formName.toPaymentAmt, document.$formName.operPaymentAmt, 15) ";
	print "\n    && editNum(document.$formName.frDiscountAmt, 11, 2) ";
	print "\n    && editNum(document.$formName.toDiscountAmt, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frDiscountAmt, document.$formName.toDiscountAmt, document.$formName.operDiscountAmt, 15) ";
	print "\n    && editdate(document.$formName.frDatePaid) ";
	print "\n    && editdate(document.$formName.toDatePaid) ";
	print "\n    && editFromToOper(document.$formName.frDatePaid, document.$formName.toDatePaid, document.$formName.operDatePaid, 'D') ";
	print "\n    && editNum(document.$formName.frBatch, 4, 0) ";
	print "\n    && editNum(document.$formName.toBatch, 4, 0) ";
	print "\n    && editFromToOper(document.$formName.frBatch, document.$formName.toBatch, document.$formName.operBatch, 4) ";
	print "\n    && editNum(document.$formName.frLocation, 3, 0) ";
	print "\n    && editNum(document.$formName.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.$formName.frLocation, document.$formName.toLocation, document.$formName.operLocation, 3) ";
	print "\n    && editdate(document.$formName.frInvoiceDate) ";
	print "\n    && editdate(document.$formName.toInvoiceDate) ";
	print "\n    && editFromToOper(document.$formName.frInvoiceDate, document.$formName.toInvoiceDate, document.$formName.operInvoiceDate, 'D') ";
	print "\n    && editdate(document.$formName.frDueDate) ";
	print "\n    && editdate(document.$formName.toDueDate) ";
	print "\n    && editFromToOper(document.$formName.frDueDate, document.$formName.toDueDate, document.$formName.operDueDate, 'D') ";
	print "\n    && editNum(document.$formName.frOEOrder, 8, 0) ";
	print "\n    && editNum(document.$formName.toOEOrder, 8, 0) ";
	print "\n    && editFromToOper(document.$formName.frOEOrder, document.$formName.toOEOrder, document.$formName.operOEOrder, 8) ";
	if ($fromType=="P") {
		print "\n   && editNum(document.$formName.frBillTo, 7, 0) ";
		print "\n   && editNum(document.$formName.toBillTo, 7, 0) ";
		print "\n    && editFromToOper(document.$formName.frBillTo, document.$formName.toBillTo, document.$formName.operBillTo, 7) ";
	}
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHBATCHSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Invoice","frInvoice","toInvoice","operInvoice","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Amount Paid","frPaymentAmt","toPaymentAmt","operPaymentAmt","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Discount","frDiscountAmt","toDiscountAmt","operDiscountAmt","opersel_num2_short","N","15","15");

	$operNbr = "operSubCode";
	print "\n <tr><td class=\"dsphdr\">Payment Code</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchSubCode\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$scriptVarBase}&amp;docName=Search&amp;fldName=srchSubCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Payment Code Description","srchSubCodeDesc","","operSubCodeDesc","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Document","srchDocument","","operDocument","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Date Paid","frDatePaid","toDatePaid","operDatePaid","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Batch","frBatch","toBatch","operBatch","opersel_num2_short","N","4","4");

	$operNbr = "operLocation";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Location Name","srchLocationName","","operLocationName","opersel_alph_short","A","20","30");
	Build_AdvSrch_Entry("Reference Number","srchPONumber","","operPONumber","opersel_alph_short","A","10","22");
	Build_AdvSrch_Entry("Invoice Date","frInvoiceDate","toInvoiceDate","operInvoiceDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Due Date","frDueDate","toDueDate","operDueDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Comment","srchComment","","operComment","opersel_alph_short","A","20","69");
	Build_AdvSrch_Entry("Memo","srchMemo","","operMemo","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Order Number","frOEOrder","toOEOrder","operOEOrder","opersel_num2_short","N","8","8");

	if ($fromType=="P") {
		$operNbr = "operBillTo";
		print "\n <tr><td class=\"dsphdr\">Bill-To</td>";
		print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frBillTo\" size=\"7\" maxlength=\"7\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frBillTo&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toBillTo\" size=\"7\" maxlength=\"7\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toBillTo&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n </tr>";

		Build_AdvSrch_Entry("Bill-To Name","srchBillToName","","operBillToName","opersel_alph_short","A","20","30");
	}
	Build_AdvSrch_Entry("Invoice Code","srchInvoiceCode","","operInvoiceCode","opersel_alph_short","A","1","1");

	$focusField = "frInvoice";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Select")        {$orby = array(array("PESSEQ" ,"A","Selected Sequence"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "Invoice")       {$orby = array(array("IVAINV" ,"A","Invoice"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "PmtAmount")     {$orby = array(array("YPAMT" ,"A","Amount Paid"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "Discount")      {$orby = array(array("YPDAMT" ,"A","Discount"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "ReasonCode")    {$orby = array(array("RHRSCD" ,"A","Reason Code"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "PmtCode")       {$orby = array(array("PSDESCU" ,"A","Payment Code"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "Document")      {$orby = array(array("YPCHK" ,"A","Document"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "DatePaid")      {$orby = array(array("YPBDAT" ,"A","Date Paid"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "Batch")         {$orby = array(array("YPBCH" ,"A","Batch"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "Location")      {$orby = array(array("IVLOC" ,"A","Loc"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "PONumber")      {$orby = array(array("IVARPO" ,"A","Reference Number"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "InvoiceDate")   {$orby = array(array("IVIVDT" ,"A","Invoice Date"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "DueDate")       {$orby = array(array("IVDUED" ,"A","Due Date"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "Comment")       {$orby = array(array("HAS_YPCMNT" ,"A","Comment"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "Memo")          {$orby = array(array("YPMEMO" ,"A","Memo"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "TypePayment")   {$orby = array(array("PEPMID" ,"A","Transaction Type"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "OEOrder")       {$orby = array(array("IVORD" ,"A","Order Number"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	elseif ($sequence == "BillTo")        {$orby = array(array("CMCNA1U" ,"A","Bill-To"),array("ORIG_YPAINV,YPOISQ,YPOPSQ,PARENT" ,"A","Invoice"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Range_WildCard("IVAINV", "Invoice", $_POST['frInvoice'], $_POST['toInvoice'], "", $_POST['operInvoice'], "N");
	$returnValue=Range_WildCard("YPAMT", "Amount Paid", $_POST['frPaymentAmt'], $_POST['toPaymentAmt'], "", $_POST['operPaymentAmt'], "N");
	$returnValue=Range_WildCard("YPDAMT", "Discount", $_POST['frDiscountAmt'], $_POST['toDiscountAmt'], "", $_POST['operDiscountAmt'], "N");
	$returnValue=Build_WildCard("YPSBCD", "Payment Code", $_POST['srchSubCode'], "U", $_POST['operSubCode'], "A");
	$returnValue=Build_WildCard("coalesce(PSDESCU, ' ')", "Payment Code Description", $_POST['srchSubCodeDesc'], "U", $_POST['operSubCodeDesc'], "A");
	$returnValue=Build_WildCard("trim(YPCHK)", "Document", $_POST['srchDocument'], "U", $_POST['operDocument'], "A");
	$returnValue=Range_WildCard("YPBDAT", "Date Paid", $_POST['frDatePaid'], $_POST['toDatePaid'], "", $_POST['operDatePaid'], "D");
	$returnValue=Range_WildCard("YPBCH", "Batch", $_POST['frBatch'], $_POST['toBatch'], "", $_POST['operBatch'], "N");
	$returnValue=Range_WildCard("IVLOC", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Build_WildCard("coalesce(Upper(LOLNA1), ' ')", "Location Name", $_POST['srchLocationName'], "U", $_POST['operLocationName'], "A");
	$returnValue=Build_WildCard("IVARPO", "Reference Number", $_POST['srchPONumber'], "U", $_POST['operPONumber'], "A");
	$returnValue=Range_WildCard("IVIVDT", "Invoice Date", $_POST['frInvoiceDate'], $_POST['toInvoiceDate'], "", $_POST['operInvoiceDate'], "D");
	$returnValue=Range_WildCard("IVDUED", "Due Date", $_POST['frDueDate'], $_POST['toDueDate'], "", $_POST['operDueDate'], "I");
	$returnValue=Build_WildCard("Upper(YPCMNT)", "Comment", $_POST['srchComment'], "U", $_POST['operComment'], "A");
	$returnValue=Build_WildCard("YPMEMO", "Memo", $_POST['srchMemo'], "U", $_POST['operMemo'], "A");
	$returnValue=Range_WildCard("IVORD", "Order Number", $_POST['frOEOrder'], $_POST['toOEOrder'], "", $_POST['operOEOrder'], "N");
	$returnValue=Range_WildCard("IVBLTO", "Bill-To", $_POST['frBillTo'], $_POST['toBillTo'], "", $_POST['operBillTo'], "N");
	$returnValue=Build_WildCard("coalesce(aa.CMCNA1U, ' ')", "Bill-To Name", $_POST['srchBillToName'], "U", $_POST['operBillToName'], "A");
	$returnValue=Build_WildCard("IVIVCD", "Invoice Code", $_POST['srchInvoiceCode'], "U", $_POST['operInvoiceCode'], "A");

	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

// Program Option Security
$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);

$uv_CustomerName ="IVBLTO";
$uv_CustomerClassName ="aa.CMCCLS";
$uv_RegionName ="aa.CMCRGN";
$uv_BillingLocationName = "IVLOC";
$uv_SalesmanName = "IVSLSM";
require 'UserView.php';

require 'stmtSQLClear.php';
$withSQL .= " With OTHERREVERSE ";
$withSQL .= " as (Select YPOISQ as RVHL_YPOISQ,YPOPSQ as RVHL_YPOPSQ,RHPMID as RVHL_RHPMID,Count(*) as RVHL_COUNT ";
$withSQL .= "     From ARRVHL ";
$withSQL .= "     Inner Join ARYPTD on (YPISEQ,YPPSEQ)=(RHISEQ,RHPSEQ) ";
$withSQL .= "     Where RHPMID='R' and (RHBCHN,RHBCHD,RHBCHB,RHTYPE,RHID,trim(RHCHK))<>($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "')  ";
$withSQL .= "     Group BY YPOISQ,YPOPSQ,RHPMID) ";
$stmtSQL .= " Select IVISEQ,IVBLTO,IVIVCD,IVAINV,IVIVDT,IVDUED,IVLOC,IVARPO,IVORD ";
$stmtSQL .= "       ,YPPSEQ,YPOISQ,YPOPSQ,YPRPSQ ";
$stmtSQL .= "       ,YPAMT,YPDAMT,YPCHK,YPBCH,YPBDAT,YPBANK,YPSBCD,YPPAYR,YPCMNT,YPMEMO ";
$stmtSQL .= "       ,Case When YPCMNT<>' ' Then 'Y' ELSE ' ' End as HAS_YPCMNT ";
$stmtSQL .= "       ,Coalesce(PEISEQ, IVISEQ) as PEISEQ ";
$stmtSQL .= "       ,Coalesce(PEENID,0)   as PEENID ";
$stmtSQL .= "       ,Coalesce(PESSEQ,0)   as PESSEQ ";
$stmtSQL .= "       ,Coalesce(PEPTYP,' ') as PEPTYP ";
$stmtSQL .= "       ,Coalesce(PEPMID,' ') as PEPMID ";
$stmtSQL .= "       ,Coalesce(PESPMT,' ') as PESPMT ";
$stmtSQL .= "       ,Case When Coalesce(PESPMT, ' ')='Y' Then 'CHECKED' ELSE ' ' End as CHECKSELECTION ";
$stmtSQL .= "       ,Coalesce(PECMNT, ' ')   as PECMNT ";
$stmtSQL .= "       ,Coalesce(RHRSCD, ' ')   as RHRSCD ";
$stmtSQL .= "       ,Coalesce(bb.FLDESC,' ') as PEPMID_FLDESC ";
$stmtSQL .= "       ,Coalesce(CMCNA1,' ') as CMCNA1,Coalesce(CMCNA1U,' ') as CMCNA1U  ";
$stmtSQL .= "       ,Coalesce(PSDESC,' ') as PSDESC,Coalesce(PSDESCU,' ') as PSDESCU  ";
$stmtSQL .= "       ,Coalesce(PYTYPE,' ') as PYTYPE ";
$stmtSQL .= "       ,Coalesce(RVHL_COUNT,0) as RVHL_COUNT " ;
$stmtSQL .= "       ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
$stmtSQL .= "       ,Coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,PRCHK,PRPTYP,PRPMID,PRISEQ,PRENID)=(b.PEBCHN,b.PEBCHD,b.PEBCHB,b.PETYPE,b.PEID,b.PECHK,b.PEPTYP,b.PEPMID,b.PEISEQ,b.PEENID)), 0) as ARPYENERROR ";
$stmtSQL .= "       ,Coalesce((Select YPAINV from ARYPTD Where (YPISEQ,YPPSEQ)=(c.YPOISQ,c.YPOPSQ)), 0) as ORIG_YPAINV ";
$stmtSQL .= "       ,Case When (YPISEQ,YPPSEQ)=(YPOISQ,YPOPSQ) Then 'A' ELSE 'B' End as PARENT ";
if ($HDOERL<=0) {
	$stmtSQL .= "     ,0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
} else {
	$stmtSQL .= "     ,(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(IVAINV,IVIVDT,IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
	$stmtSQL .= "     ,(Select Count(*) From OEORHH Where HHLIV#=IVAINV and HHBLTO=IVBLTO) as OEHISTORY " ;
	$stmtSQL .= "     ,(Select Count(*) From OEORHH Where IVORD<>0 and HHORD#=IVORD and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
}
$fileSQL .= " HDINVC a ";
$fileSQL .= " inner join ARYPTD c on YPISEQ=IVISEQ and (YPBANK=$fromBatchBank or (YPBANK,YPPYCD)=(0,'7'))";
$fileSQL .= " Left Join ARRVHL on RHPMID in ('R','C') and (RHBCHN,RHBCHD,RHBCHB,RHTYPE,RHID,trim(RHCHK),RHISEQ,RHPSEQ)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "',IVISEQ,YPPSEQ) ";
$fileSQL .= " Left join ARPYEN b on (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,PECHK,PEPTYP,PEPMID,PEISEQ,PEENID)=(RHBCHN,RHBCHD,RHBCHB,RHTYPE,RHID,RHCHK,RHPTYP,RHPMID,RHISEQ,RHENID) ";
$fileSQL .= " Left Join OTHERREVERSE on (RVHL_YPOISQ,RVHL_YPOPSQ,RVHL_RHPMID)=(YPOISQ,YPOPSQ,Coalesce(PEPMID,'R')) ";
$fileSQL .= " left join ARPYSB on PSSBCD=YPSBCD ";
$fileSQL .= " left join ARPYCD on PYPYCD=PSPYCD ";
$fileSQL .= " left join HDCUST aa on aa.CMCUST=IVBLTO ";
$fileSQL .= " left join SYFLAG bb on (bb.FLTYPE,bb.FLVALU)=('ARPMTID',PEPMID) ";
$fileSQL .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=IVORD and HHLIV#=IVAINV ";
if ($fromType=="C") {$selectSQL .= "IVBLTO=$fromID and YPPAYR=0 ";}
else                {$selectSQL .= "YPPAYR=$fromID ";}
$selectSQL .= " and YPPYCD<>'$CRNPYC' and YPDPTP<>'N' ";  // Not NSF
$selectSQL .= " and (YPISEQ,YPPSEQ)=(YPOISQ,YPOPSQ) ";  // Only Originating Payments
$viewCheckSQL="";
if ($viewCheckBox[0] || $viewCheckBox[1] || $viewCheckBox[2]) {
	if     ($viewCheckBox[0])                        {$viewCheckSQL.= " (Coalesce(PESPMT,' ')='Y'";}
	if     ($viewCheckBox[1] && $viewCheckSQL == "") {$viewCheckSQL.= " (Coalesce(PESPMT,' ')=' ' and (Coalesce(RVHL_COUNT,0)=0 and YPRPSQ=0)";}
	elseif ($viewCheckBox[1])                        {$viewCheckSQL.= "  or Coalesce(PESPMT,' ')=' ' and (Coalesce(RVHL_COUNT,0)=0 and YPRPSQ=0)";}
	if     ($viewCheckBox[2] && $viewCheckSQL == "") {$viewCheckSQL.= " (Coalesce(PESPMT,' ')=' ' and (Coalesce(RVHL_COUNT,0)<>0 or YPRPSQ<>0)";}
	elseif ($viewCheckBox[2])                        {$viewCheckSQL.= "  or Coalesce(PESPMT,' ')=' ' and (Coalesce(RVHL_COUNT,0)<>0 or YPRPSQ<>0)";}
	$viewCheckSQL.=")";
} else {
	$viewCheckSQL.= " IVIVAM<>IVIVAM";
}
if ($selectSQL == "") {$selectSQL  = $viewCheckSQL;
} else                {$selectSQL .= " and $viewCheckSQL ";}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';

$sv_withSQL=$withSQL;
$sv_stmtSQL=$stmtSQL;
$sv_fileSQL=$fileSQL;
$sv_selectSQL=$selectSQL;

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
print "\n <link rel=stylesheet type=\"text/css\" href=\"{$ARApplCashStyleSheet}\"> ";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n var optionWin;";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';
require_once 'Menu.js';

$formName = "Search";  // Need to Calendar Include
require_once 'CalendarInclude.php';
$formName = "Chg";
require_once 'CheckEnterAjax.php';
require_once 'CheckEnterSearch.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
require_once 'StringTrimJavaScript.php';

require_once 'ApplCashPaymentReverseJava.php';
require_once 'ApplCashPaymentJava.php';
print "\n function confirmDelete(deleteMsg) {return confirm(\"{$delRecordConf} \" +  \"\\n\" + \"\\n\" + deleteMsg);} ";

print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "APPLCASHPAYMENT";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

require_once 'ApplCashDocRetInfoInclude.php';
require_once 'ApplCashPaymentTabInclude.php';

$withSQL=$sv_withSQL;
$stmtSQL=$sv_stmtSQL;
$fileSQL=$sv_fileSQL;
$selectSQL=$sv_selectSQL;
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint != "Y"){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"IVAINV|null|Invoice|N|\" title=\"Invoice\" SELECTED>Invoice";
	$qsOpt .= "\n <option value=\"YPAMT|null|Amount Paid|N|\" title=\"Amount Paid\">Amount Paid";
	$qsOpt .= "\n <option value=\"YPDAMT|null|Discount|N|\" title=\"Discount\">Discount";
	$qsOpt .= "\n <option value=\"YPSBCD|null|Payment Code|A|U\" title=\"Payment Code\">Payment Code";
	$qsOpt .= "\n <option value=\"coalesce(PSDESCU,' ')|null|Payment Code Description|A|U\" title=\"Payment Code Description\">Payment Code Description";
	$qsOpt .= "\n <option value=\"trim(YPCHK)|null|Document|A|U\" title=\"Document\">Document";
	$qsOpt .= "\n <option value=\"YPBDAT|DATE|Date Paid|D|\" title=\"Date Paid\">Date Paid";
	$qsOpt .= "\n <option value=\"YPBCH|null|Batch|N|\" title=\"Batch\">Batch";
	$qsOpt .= "\n <option value=\"IVLOC|null|Location|N|\" title=\"Location\">Location";
	$qsOpt .= "\n <option value=\"coalesce(Upper(LOLNA1), ' ')|null|Location Name|A|U\" title=\"Location Name\">Location Name";
	$qsOpt .= "\n <option value=\"IVARPO|null|Reference Number|A|U\" title=\"Reference Number\">Reference Number";
	$qsOpt .= "\n <option value=\"IVIVDT|DATE|Invoice Date|D|\" title=\"Invoice Date\">Invoice Date";
	$qsOpt .= "\n <option value=\"IVDUED|DATE|Due Date|I|\" title=\"Due Date\">Due Date";
	$qsOpt .= "\n <option value=\"Upper(YPCMNT)|null|Comment|A|U\" title=\"Comment\">Comment";
	$qsOpt .= "\n <option value=\"YPMEMO|null|Memo|A|U\" title=\"Memo\">Memo";
	$qsOpt .= "\n <option value=\"Coalesce(PEPMID,' ')|null|Transaction Type|A|U\" title=\"Transaction Type\">Transaction Type";
	$qsOpt .= "\n <option value=\"IVORD|null|Order Number|N|\" title=\"Order Number\">Order Number";
	if ($fromType=="P") {
		$qsOpt .= "\n <option value=\"IVBLTO|null|Bill-To|N|\" title=\"Bill-To\">Bill-To";
		$qsOpt .= "\n <option value=\"coalesce(aa.CMCNA1U, ' ')|null|Bill-To Name|A|U\" title=\"Bill-To Name\">Bill-To Name";
	}
	$qsOpt .= "\n <option value=\"IVIVCD|null|Invoice Code|A|U\" title=\"Invoice Code\">Invoice Code";
	require 'QuickSearchOption.php';

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=U&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\" onSubmit=\"return false;\">";
	print "\n <table $contentTable id=\"paymentTable\"> <tr>";
	$returnValue=OrderBy_Sort("PESSEQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Select\"    title=\"Sequence By Selected\">{$sortPoint}Sel</a></th>";
	$returnValue=OrderBy_Sort("IVAINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\"   title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th>";
	$returnValue=OrderBy_Sort("YPAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtAmount\" title=\"Sequence By Amount Paid\">{$sortPoint}Amount Paid</a></th>";
	$returnValue=OrderBy_Sort("YPDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Discount\"  title=\"Sequence By Discount\">{$sortPoint}Discount</a></th>";
	$returnValue=OrderBy_Sort("RHRSCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ReasonCode\"   title=\"Sequence By Reversal Reason\">{$sortPoint}Reversal Reason</a></th>";

	// icons
	print "\n <th class=\"colhdr\">&nbsp;</th>";

	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtCode\"       title=\"Sequence By Payment Code\">{$sortPoint}Payment Code</a></th>";
	$returnValue=OrderBy_Sort("YPCHK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Document\"       title=\"Sequence By Document\">{$sortPoint}Document</a></th>";
	$returnValue=OrderBy_Sort("YPBDAT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DatePaid\"       title=\"Sequence By Date Paid\">{$sortPoint}Date Paid</a></th>";
	$returnValue=OrderBy_Sort("YPBCH"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Batch\"       title=\"Sequence By Batch\">{$sortPoint}Batch</a></th>";
	$returnValue=OrderBy_Sort("IVLOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Location\"       title=\"Sequence By Loc\">{$sortPoint}Loc</a></th>";
	$returnValue=OrderBy_Sort("IVARPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PONumber\"       title=\"Sequence By Reference Number\">{$sortPoint}Reference Number</a></th>";
	$returnValue=OrderBy_Sort("IVIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvoiceDate\"       title=\"Sequence By Invoice Date\">{$sortPoint}Invoice Date</a></th>";
	$returnValue=OrderBy_Sort("IVDUED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DueDate\"       title=\"Sequence By Due Date\">{$sortPoint}Due Date</a></th>";
	$returnValue=OrderBy_Sort("HAS_YPCMNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Comment\"       title=\"Sequence By Cmt\">{$sortPoint}Cmt</a></th>";
	$returnValue=OrderBy_Sort("YPMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Memo\"       title=\"Sequence By Memo\">{$sortPoint}Memo</a></th>";
	$returnValue=OrderBy_Sort("PEPMID"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TypePayment\"   title=\"Sequence By Transaction Type\">{$sortPoint}Trans Type</a></th>";
	$returnValue=OrderBy_Sort("IVORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OEOrder\"       title=\"Sequence By Order Number\">{$sortPoint}Order Number</a></th>";
	if ($fromType=="P") {
		$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BillTo\"   title=\"Sequence By Bill-To\">{$sortPoint}Bill-To</a></th>";
	}
	print "\n </tr>";

	require_once 'ApplCashPaymentJavaUpdateHiddenInclude.php';

	// Payment rows
	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		$maintainVar = "{$scriptVarBase}&amp;fromPaymentType=" . urlencode(trim($paymentType)) . "&amp;fromPaymentID=" . urlencode(trim($paymentID)) . "&amp;fromInvoiceSeq=" . urlencode(trim($row['IVISEQ'])) . "&amp;fromEntryID=" . urlencode(trim($row['PEENID']));

		$hiddenJavaArray .= "new Array(\"{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\",\"{$row['IVISEQ']}\",\"{$row['YPPSEQ']}\",\"{$row['PEENID']}\",\"{$row['PEPMID']}\"),";
		require  'SetRowClass.php';

		$F_YPBDAT=Format_Date($row['YPBDAT'], "D");
		$F_IVDUED=Format_Date_ISO($row['IVDUED'], "D");
		$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
		$F_IVPSDT=Format_Date($row['IVPSDT'], "D");
		if ($row['ARPYENERROR']>0) {
			$RHRSCD_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$row[PEPTYP]','$row[PEPMID]',$row[IVISEQ],$row[PEENID]) and PRCOLM in ('RHRSCD') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		} else {
			$RHRSCD_ERROR="";
		}

		print "\n <tr class=\"$rowClass\" id=\"row{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\"> ";
		// Select Checkbox
		if     ($row['PYTYPE']=='C' && $harced_OPT['sec_01']=="Y" && ($CRBBAL!="Y" || $BMBCHT=="D")) {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='U' && $harced_OPT['sec_02']=="Y" && ($CRBBAL!="Y" || $BMBCHT=="D")) {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='J' && $harced_OPT['sec_03']=="Y") {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='Y' && $harced_OPT['sec_04']=="Y") {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='A' && $harced_OPT['sec_05']=="Y" && ($CRBBAL!="Y" || $BMBCHT=="D")) {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='D' && $harced_OPT['sec_06']=="Y") {$pmtAuth="Y";}
		else {$pmtAuth="";}
		
		if ($pmtAuth=="Y" && $row['YPRPSQ']==0 && $row['RVHL_COUNT']==0 && $row['IVISEQ']==$row['YPOISQ'] && $row['YPPSEQ']==$row['YPOPSQ']) {
			print "\n <td class=\"inputcode\"><input type=\"checkbox\" name=\"spmt{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" id=\"spmt{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" value='S' $row[CHECKSELECTION] onClick=\"editPESPMT('$row[IVISEQ]','$row[YPPSEQ]','$row[PEENID]','$row[IVAINV]') \" title=\"Reverse Payment\"></td> ";
		} else {print "\n <td class=\"inputcode\">&nbsp;</td> ";}

		// Invoice column
		if     ($row['IVIVCD']=="C") {$ivcdClass="archargeinvoice";}
		elseif ($row['IVIVCD']=="D") {$ivcdClass="ardeductioninvoice";}
		elseif ($row['IVIVCD']=="N") {$ivcdClass="arnsfinvoice";}
		elseif ($row['IVIVCD']=="S") {$ivcdClass="arserviceinvoice";}
		elseif ($row['IVIVCD']=="U") {$ivcdClass="arunappliedinvoice";}
		else                         {$ivcdClass="inputnmbr";}
		

		if     ($row['YPRPSQ']>0 || $row['RVHL_COUNT']>0)                         {$IVAINV_ERROR="Reversed Payment";}
		elseif ($row['IVISEQ']!=$row['YPOISQ'] || $row['YPPSEQ']!=$row['YPOPSQ']) {$IVAINV_ERROR="Attached to another Payment";}
		elseif ($pmtAuth!="Y")                                                    {$IVAINV_ERROR="Not Authorized to Payment ";}
		else                                                                      {$IVAINV_ERROR="";}
		if ($IVAINV_ERROR!="") {$FldStyle=" title=\"$IVAINV_ERROR\"  ";}
		else                   {$FldStyle="";}
		
		if ($row['OEINVCOUNT']>0) {print "\n <td class=\"$ivcdClass\" $FldStyle><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['IVAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['IVIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[IVAINV]</a></td> ";}
		else                      {print "\n <td class=\"$ivcdClass\" $FldStyle>$row[IVAINV]</td> ";}

		print "\n <td class=\"colnmbr\">" . number_format($row['YPAMT'],2) . "</td> ";
		print "\n <td class=\"colnmbr\">" . number_format($row['YPDAMT'],2) . "</td> ";

		if ($pmtAuth=="Y" && $row['YPRPSQ']==0 && $row['RVHL_COUNT']==0 && $row['IVISEQ']==$row['YPOISQ'] && $row['YPPSEQ']==$row['YPOPSQ']) {
			if ($RHRSCD_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$RHRSCD_ERROR\"  ";}
			else                   {$FldStyle="";}
			print "\n <td class=\"inputalph\"><input type=\"text\" name=\"rscd{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" id=\"rscd{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['RHRSCD']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editRHRSCD('$row[IVISEQ]','$row[YPPSEQ]','$row[PEENID]')\"> ";
			print "\n                         <a href=\"{$homeURL}{$phpPath}ARReverseReasonSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=rscd{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		} else {
			print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"rscd{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" id=\"rscd{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\"></td> ";
		}
		// icons
		if ($row['YPRPSQ']==0 && $row['RVHL_COUNT']==0 && $row['IVISEQ']==$row['YPOISQ'] && $row['YPPSEQ']==$row['YPOPSQ']) {
			if ($row['PEENID']==0) {$disabled="style=\"visibility: hidden;\" " ;} else {$disabled="";}
			print "\n <td class=\"inputalph\" id=\"icon{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" $disabled> ";
			// Comment icon
			print "\n <a onclick=\"showSel('commententry{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}')\" onMouseOver=\"showSel('commentshow{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}')\" onMouseOut=\"hideSel('commentshow{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}')\"><span  id=\"cmt{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\"> ";
			if (trim($row['PECMNT'])!="") {print "\n $commentExistImageNoTitle ";}
			else                          {print "\n $commentImage ";}
			print "\n </span></a> ";
			print "\n <div id=\"commentshow{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" class=\"moreInfo\">";
			print "\n     <table $contentTable> ";
			print "\n         <tr><td class=\"dspalph\" id=\"oldcmt{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\">" . trim($row[PECMNT]) . "</td></tr> ";
			print "\n     </table> ";
			print "\n </div>";
			print "\n <div id=\"commententry{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" class=\"moreInfo\"> ";
			print "\n     <table $contentTable> ";
			print "\n         <tr><td><input type=\"text\" name=\"newcmt{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" id=\"newcmt{$row['IVISEQ']}_{$row['YPPSEQ']}_{$row['PEENID']}\" value=\"" . trim($row['PECMNT']) . "\" size=\"75\" maxlength=\"69\"</td></tr> ";
			print "\n     <tr><td> ";
			print "\n         <a onClick=\"AcceptCmtEntryReverse('$row[IVISEQ]','$row[YPPSEQ]','$row[PEENID]');\">$commentAcceptImage</a> ";
			print "\n         <a onClick=\"ResetCmtEntryReverse('$row[IVISEQ]','$row[YPPSEQ]','$row[PEENID]');\">$commentResetImage</a> ";
			print "\n         <a onClick=\"ClearCmtEntryReverse('$row[IVISEQ]','$row[YPPSEQ]','$row[PEENID]');\">$commentClearImage</a> ";
			print "\n         <a onClick=\"CloseCmtEntryReverse('$row[IVISEQ]','$row[YPPSEQ]','$row[PEENID]');\">$closeImageMed</a> ";
			print "\n     </td></tr></table> ";
			print "\n </div>";
			print "\n </td> ";
		} else {print "\n <td class=\"inputcode\">&nbsp;</td> ";}

		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[YPSBCD]\">$row[PSDESC]</span></td>";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}ARCheckInquiry.php{$scriptVarBase}&amp;tag=REPORT&amp;fromDocument=" . urlencode(trim($row['YPCHK'])) . "&amp;fromDatePaid=" . urlencode(trim($row['YPBDAT'])) . "&amp;fromBank=" . urlencode(trim($row['YPBANK'])) . "&amp;fromPayer=" . urlencode(trim($row['YPPAYR'])) . "&amp;fromCustomer=" . urlencode(trim($row['IVBLTO'])) . "\" onclick=\"$drillDownWinVar\" title=\"A/R Document Quickview\">$row[YPCHK]</a></td> ";
		print "\n <td class=\"coldate\">$F_YPBDAT</td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashBatchSelect.php{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['YPBCH'])) . "&amp;fromBatchDate=" . urlencode(trim($row['YPBDAT'])) . "&amp;fromBatchBank=" . urlencode(trim($row['YPBANK'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Batch\">$row[YPBCH]</a></td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['IVLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location\">$row[IVLOC]</a></td> ";
		print "\n <td class=\"colalph\">$row[IVARPO]</td> ";
		print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$scriptVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['IVISEQ'])) . "&amp;noMenu=Y\" title=\"View A/R Invoice\" onclick=\"$searchWinVar\">$F_IVIVDT</a></td> ";
		print "\n <td class=\"coldate\">$F_IVDUED</td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[YPCMNT]\">$row[HAS_YPCMNT]</span></td> ";
		print "\n <td class=\"colalph\">$row[YPMEMO]</td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row[PEPMID_FLDESC]) . "\">$row[PEPMID]</span></td>";
		if     ($row['OESELECT']>0)  {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['IVORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[IVORD]</a></td> ";}
		elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['IVBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['IVAINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[IVORD]</a></td> ";}
		else                         {print "\n <td class=\"colnmbr\">$row[IVORD]</td> ";}
		if ($fromType=="P") {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[IVBLTO]]\">$row[CMCNA1]</a></td> ";}
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}

	if ($rowCount == 0){require 'NoRecordsFound.php';}

	print "\n </table></form> ";
}
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
require 'EndTabInclude.php';
print "\n </table>";
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
require_once  'ApplCashPaymentJavaHiddenUpdateArrayScript.php';  // Add Hidden fields used for Ajax in Java script
print "\n </body> \n </html>";

?>
