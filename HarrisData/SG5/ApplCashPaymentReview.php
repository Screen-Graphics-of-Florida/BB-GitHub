<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$paymentType        = "R";
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
$scriptName    = "ApplCashPaymentReview.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId)) . "&amp;columnDisplay" . $columnDisplay . "&amp;paymentType=" . urlencode(trim($paymentType)) . "&amp;harcedProgram=HARCED_P{$paymentType}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("PESSEQ","A","Selected Sequence"));
$tabID         = "REVIEW";
$programName   = "HARCED";

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
	Build_AdvSrch_Entry("Payment Amount","frPaymentAmt","toPaymentAmt","operPaymentAmt","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Discount","frDiscountAmt","toDiscountAmt","operDiscountAmt","opersel_num2_short","N","15","15");

	$operNbr = "operSubCode";
	print "\n <tr><td class=\"dsphdr\">Payment Code</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchSubCode\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$scriptVarBase}&amp;docName=Search&amp;fldName=srchSubCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Payment Code Description","srchSubCodeDesc","","operSubCodeDesc","opersel_alph_short","A","20","50");

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
	if     ($sequence == "Select")        {$orby = array(array("PESSEQ" ,"A","Selected Sequence"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "Invoice")       {$orby = array(array("IVAINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "PmtAmount")     {$orby = array(array("PEAMT" ,"A","Payment Amount"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "Discount")      {$orby = array(array("PEDAMT" ,"A","Discount"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "PmtCode")       {$orby = array(array("PSDESCU" ,"A","Payment Code"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "GenDed")        {$orby = array(array("PEGDED" ,"A","General Deduction"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "Location")      {$orby = array(array("IVLOC" ,"A","Loc"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "PONumber")      {$orby = array(array("IVARPO" ,"A","Reference Number"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "InvoiceDate")   {$orby = array(array("IVIVDT" ,"A","Invoice Date"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "DueDate")       {$orby = array(array("IVDUED" ,"A","Due Date"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "Comment")       {$orby = array(array("HAS_PECMNT" ,"A","Comment"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "Memo")          {$orby = array(array("PEMEMO" ,"A","Memo"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "TypePayment")   {$orby = array(array("PEPMID" ,"A","Transaction Type"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "OEOrder")       {$orby = array(array("IVORD" ,"A","Order Number"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "BillTo")        {$orby = array(array("CMCNA1U" ,"A","Bill-To"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "PmtType")       {$orby = array(array("CPDESCU" ,"A","Pmt Type"),array("IVAINV" ,"A","Invoice"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Range_WildCard("Case When PECRTB='A' and PEPTYP='D' Then PENINV else Coalesce(IVAINV,PESINV) End", "Invoice", $_POST['frInvoice'], $_POST['toInvoice'], "", $_POST['operInvoice'], "N");
	$returnValue=Range_WildCard("PEAMT", "Payment Amount", $_POST['frPaymentAmt'], $_POST['toPaymentAmt'], "", $_POST['operPaymentAmt'], "N");
	$returnValue=Range_WildCard("PEDAMT", "Discount", $_POST['frDiscountAmt'], $_POST['toDiscountAmt'], "", $_POST['operDiscountAmt'], "N");
	$returnValue=Build_WildCard("PESBCD", "Payment Code", $_POST['srchSubCode'], "U", $_POST['operSubCode'], "A");
	$returnValue=Build_WildCard("Coalesce(PSDESCU, ' ')", "Payment Code Description", $_POST['srchSubCodeDesc'], "U", $_POST['operSubCodeDesc'], "A");
	$returnValue=Range_WildCard("Coalesce(IVLOC,PELOC)", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Build_WildCard("Coalesce(Upper(LOLNA1), ' ')", "Location Name", $_POST['srchLocationName'], "U", $_POST['operLocationName'], "A");
	$returnValue=Build_WildCard("Coalesce(IVARPO,PEARPO)", "Reference Number", $_POST['srchPONumber'], "U", $_POST['operPONumber'], "A");
	$returnValue=Range_WildCard("Coalesce(IVIVDT,PEBCHD)", "Invoice Date", $_POST['frInvoiceDate'], $_POST['toInvoiceDate'], "", $_POST['operInvoiceDate'], "D");
	$returnValue=Range_WildCard("Coalesce(IVDUED,F_MAKEDATE(PEBCHD))", "Due Date", $_POST['frDueDate'], $_POST['toDueDate'], "", $_POST['operDueDate'], "I");
	$returnValue=Build_WildCard("Upper(PECMNT)", "Comment", $_POST['srchComment'], "U", $_POST['operComment'], "A");
	$returnValue=Build_WildCard("PEMEMO", "Memo", $_POST['srchMemo'], "U", $_POST['operMemo'], "A");
	$returnValue=Range_WildCard("Coalesce(IVORD,PEORD)", "Order Number", $_POST['frOEOrder'], $_POST['toOEOrder'], "", $_POST['operOEOrder'], "N");
	$returnValue=Range_WildCard("Coalesce(IVBLTO,PEBLTO)", "Bill-To", $_POST['frBillTo'], $_POST['toBillTo'], "", $_POST['operBillTo'], "N");
	$returnValue=Build_WildCard("Coalesce(aa.CMCNA1U, ' ')", "Bill-To Name", $_POST['srchBillToName'], "U", $_POST['operBillToName'], "A");
	$returnValue=Build_WildCard("IVIVCD", "Invoice Code", $_POST['srchInvoiceCode'], "U", $_POST['operInvoiceCode'], "A");

	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

// Program Option Security
$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);

$uv_CustomerName ="@@BLTO";
$uv_CustomerClassName ="aa.CMCCLS";
$uv_RegionName ="aa.CMCRGN";
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
$stmtSQL .= " Select PEISEQ,PEPTYP,PEPMID,PEENID,PECRTB,PESSEQ,PESPMT,PEAMT,PEDAMT,PESBCD,PECMNT,PEMEMO,PEGDED ";
$stmtSQL .= "       ,Case When PECRTB='A' and PEPTYP='D' Then PENINV else Coalesce(IVAINV,PESINV) End As IVAINV ";
$stmtSQL .= "       ,Case When PECMNT<>' ' Then 'Y' ELSE ' ' End as HAS_PECMNT ";
$stmtSQL .= "       ,Case When Coalesce(PESPMT, ' ')='Y' Then 'CHECKED' ELSE ' ' End as CHECKSELECTION ";
$stmtSQL .= "       ,Coalesce(b.FLDESC,' ') as PEPMID_FLDESC ";
$stmtSQL .= "       ,Coalesce(IVISEQ,0) as IVISEQ ";
$stmtSQL .= "       ,Coalesce(IVBLTO,PEBLTO) as IVBLTO ";
$stmtSQL .= "       ,Coalesce(IVIVCD,' ') as IVIVCD";
$stmtSQL .= "       ,Coalesce(IVIVDT,PEBCHD) as IVIVDT ";
$stmtSQL .= "       ,Coalesce(IVDUED,F_MAKEDATE(PEBCHD)) as IVDUED ";
$stmtSQL .= "       ,Coalesce(IVLOC,PELOC) as IVLOC ";
$stmtSQL .= "       ,Coalesce(IVARPO,PEARPO) as IVARPO ";
$stmtSQL .= "       ,Coalesce(IVORD,PEORD) as IVORD ";
$stmtSQL .= "       ,Coalesce(CPDESC,' ') as CPDESC,Coalesce(CPDESCU,' ') as CPDESCU  ";
$stmtSQL .= "       ,Coalesce(CMCNA1,' ') as CMCNA1,Coalesce(CMCNA1U,' ') as CMCNA1U  ";
$stmtSQL .= "       ,Coalesce(PSDESC,' ') as PSDESC,Coalesce(PSDESCU,' ') as PSDESCU  ";
$stmtSQL .= "       ,Coalesce(PYTYPE,' ') as PYTYPE ";
$stmtSQL .= "       ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
if ($HDOERL<=0) {
	$stmtSQL .= "     ,0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
} else {
	$stmtSQL .= "     ,(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(Case When PECRTB='A' and PEPTYP='D' Then PENINV else Coalesce(IVAINV,PESINV) End,IVIVDT,IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
	$stmtSQL .= "     ,(Select Count(*) From OEORHH Where HHLIV#=Case When PECRTB='A' and PEPTYP='D' Then PENINV else Coalesce(IVAINV,PESINV) End and HHBLTO=IVBLTO) as OEHISTORY " ;
	$stmtSQL .= "     ,(Select Count(*) From OEORHH Where coalesce(IVORD,PEORD)<>0 and HHORD#=coalesce(IVORD,PEORD) and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
}
$fileSQL .= " ARPYEN ";
$fileSQL .= " Left Join HDINVC a  on IVISEQ=PEISEQ ";
$fileSQL .= " left join ARPAYT    on CPTYPE=PEPTYP ";
$fileSQL .= " left join HDCUST aa on aa.CMCUST=coalesce(IVBLTO,PEBLTO) ";
$fileSQL .= " left join ARPYSB    on PSSBCD=PESBCD ";
$fileSQL .= " left join ARPYCD    on PYPYCD=PSPYCD ";
$fileSQL .= " left join SYFLAG b  on (b.FLTYPE,b.FLVALU)=('ARPMTID',PEPMID) ";
$fileSQL .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=coalesce(IVORD,PEORD) and HHLIV#=IVAINV ";
$selectSQL.= " (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';

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

require_once 'ApplCashPaymentReviewJava.php';
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

$stmtSQL=$sv_stmtSQL;
$fileSQL=$sv_fileSQL;
$selectSQL=$sv_selectSQL;
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint != "Y"){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"Case When PECRTB='A' and PEPTYP='D' Then PENINV else Coalesce(IVAINV,PESINV) End|null|Invoice|N|\" title=\"Invoice\" SELECTED>Invoice";
	$qsOpt .= "\n <option value=\"PEAMT|null|Payment Amount|N|\" title=\"Payment Amount\">Payment Amount";
	$qsOpt .= "\n <option value=\"PEDAMT|null|Discount|N|\" title=\"Discount\">Discount";
	$qsOpt .= "\n <option value=\"PESBCD|null|Payment Code|A|U\" title=\"Payment Code\">Payment Code";
	$qsOpt .= "\n <option value=\"Coalesce(PSDESCU,' ')|null|Payment Code Description|A|U\" title=\"Payment Code Description\">Payment Code Description";
	$qsOpt .= "\n <option value=\"Coalesce(IVLOC,PELOC)|null|Location|N|\" title=\"Location\">Location";
	$qsOpt .= "\n <option value=\"Coalesce(Upper(LOLNA1), ' ')|null|Location Name|A|U\" title=\"Location Name\">Location Name";
	$qsOpt .= "\n <option value=\"Coalesce(IVARPO,PEARPO)|null|Reference Number|A|U\" title=\"Reference Number\">Reference Number";
	$qsOpt .= "\n <option value=\"Coalesce(IVIVDT,PEBCHD)|DATE|Invoice Date|D|\" title=\"Invoice Date\">Invoice Date";
	$qsOpt .= "\n <option value=\"Coalesce(IVDUED,F_MAKEDATE(PEBCHD))|DATE|Due Date|I|\" title=\"Due Date\">Due Date";
	$qsOpt .= "\n <option value=\"Upper(PECMNT)|null|Comment|A|U\" title=\"Comment\">Comment";
	$qsOpt .= "\n <option value=\"PEMEMO|null|Memo|A|U\" title=\"Memo\">Memo";
	$qsOpt .= "\n <option value=\"Coalesce(PEPMID,' ')|null|Transaction Type|A|U\" title=\"Transaction Type\">Transaction Type";
	$qsOpt .= "\n <option value=\"Coalesce(IVORD,PEORD)|null|Order Number|N|\" title=\"Order Number\">Order Number";
	if ($fromType=="P") {
		$qsOpt .= "\n <option value=\"Coalesce(IVBLTO,PEBLTO)|null|Bill-To|N|\" title=\"Bill-To\">Bill-To";
		$qsOpt .= "\n <option value=\"Coalesce(aa.CMCNA1U, ' ')|null|Bill-To Name|A|U\" title=\"Bill-To Name\">Bill-To Name";
	}
	$qsOpt .= "\n <option value=\"IVIVCD|null|Invoice Code|A|U\" title=\"Invoice Code\">Invoice Code";
	require 'QuickSearchOption.php';

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=U&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\" onSubmit=\"return false;\">";
	print "\n <table $contentTable id=\"paymentTable\"> <tr>";
	$returnValue=OrderBy_Sort("PESSEQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Select\"    title=\"Sequence By Selected\">{$sortPoint}Sel</a></th>";
	$returnValue=OrderBy_Sort("IVAINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\"   title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th>";
	$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtAmount\" title=\"Sequence By Payment Amount\">{$sortPoint}Payment Amount</a></th>";
	$returnValue=OrderBy_Sort("PEDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Discount\"  title=\"Sequence By Discount\">{$sortPoint}Discount</a></th>";
	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtCode\"       title=\"Sequence By Payment Code\">{$sortPoint}Payment Code</a></th>";
	$returnValue=OrderBy_Sort("PEGDED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=GenDed\"       title=\"Sequence By Gen\">{$sortPoint}Gen</a></th>";
	$returnValue=OrderBy_Sort("IVLOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Location\"       title=\"Sequence By Loc\">{$sortPoint}Loc</a></th>";
	$returnValue=OrderBy_Sort("IVARPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PONumber\"       title=\"Sequence By Reference Number\">{$sortPoint}Reference Number</a></th>";
	$returnValue=OrderBy_Sort("IVIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvoiceDate\"       title=\"Sequence By Invoice Date\">{$sortPoint}Invoice Date</a></th>";
	$returnValue=OrderBy_Sort("IVDUED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DueDate\"       title=\"Sequence By Due Date\">{$sortPoint}Due Date</a></th>";
	$returnValue=OrderBy_Sort("HAS_PECMNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Comment\"       title=\"Sequence By Cmt\">{$sortPoint}Cmt</a></th>";
	$returnValue=OrderBy_Sort("PEMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Memo\"       title=\"Sequence By Memo\">{$sortPoint}Memo</a></th>";
	$returnValue=OrderBy_Sort("PEPMID"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TypePayment\"   title=\"Sequence By Transaction Type\">{$sortPoint}Trans Type</a></th>";
	$returnValue=OrderBy_Sort("IVORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OEOrder\"       title=\"Sequence By Order Number\">{$sortPoint}Order Number</a></th>";
	if ($fromType=="P") {
		$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BillTo\"   title=\"Sequence By Bill-To\">{$sortPoint}Bill-To</a></th>";
	}
	$returnValue=OrderBy_Sort("CPDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtType\"   title=\"Sequence By Pmt Type\">{$sortPoint}Pmt Type</a></th>";
	print "\n </tr>";

	require_once 'ApplCashPaymentJavaUpdateHiddenInclude.php';

	// Payment rows
	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}

		require  'SetRowClass.php';

		$F_IVDUED=Format_Date_ISO($row['IVDUED'], "D");
		$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
		$F_IVPSDT=Format_Date($row['IVPSDT'], "D");

		print "\n <tr class=\"$rowClass\" id=\"row{$row['PEISEQ']}_{$row['PEENID']}\"> ";

		// Delete icon
		if     ($row['PYTYPE']=='C' && $harced_OPT['sec_01']=="Y") {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='U' && $harced_OPT['sec_02']=="Y") {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='J' && $harced_OPT['sec_03']=="Y") {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='Y' && $harced_OPT['sec_04']=="Y") {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='A' && $harced_OPT['sec_05']=="Y") {$pmtAuth="Y";}
		elseif ($row['PYTYPE']=='D' && $harced_OPT['sec_06']=="Y") {$pmtAuth="Y";}
		else {$pmtAuth="";}

		if ($pmtAuth=="Y" && $row['PEPMID']!="R") {
			if ($applCashPaymentDeletePrompt=="Y") {
				$deleteMsg="Payment for invoice {$row[IVAINV]}";
				print "\n <td class=\"inputcode\"><span id=\"spmt{$row['PEISEQ']}_{$row['PEENID']}\"><a onClick=\"if(confirmDelete('$deleteMsg')) {delARPYENLine('$row[PEISEQ]','$row[PEENID]','$row[PEPMID]','$row[PEPTYP]','$row[PECRTB]');} \">$deleteImageSml</a></span></td> ";
			} else {
				print "\n <td class=\"inputcode\"><span id=\"spmt{$row['PEISEQ']}_{$row['PEENID']}\"><a onClick=\"delARPYENLine('$row[PEISEQ]','$row[PEENID]','$row[PEPMID]','$row[PEPTYP]','$row[PECRTB]'); \">$deleteImageSml</a></span></td> ";
			}
		} else {
			print "\n <td class=\"inputcode\">&nbsp;</td> ";
		}

		// Invoice column
		if     ($row['IVIVCD']=="C") {$ivcdClass="archargeinvoice";}
		elseif ($row['IVIVCD']=="D") {$ivcdClass="ardeductioninvoice";}
		elseif ($row['IVIVCD']=="N") {$ivcdClass="arnsfinvoice";}
		elseif ($row['IVIVCD']=="S") {$ivcdClass="arserviceinvoice";}
		elseif ($row['IVIVCD']=="U") {$ivcdClass="arunappliedinvoice";}
		else                         {$ivcdClass="inputnmbr";}
		if ($row['OEINVCOUNT']>0) {print "\n <td class=\"$ivcdClass\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['IVAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['IVIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[IVAINV]</a></td> ";}
		else                      {print "\n <td class=\"$ivcdClass\">$row[IVAINV]</td> ";}

		if ($row['PEAMT']!=0) {print "\n <td class=\"colnmbr\">" . number_format($row['PEAMT'],2) . "</td> ";}
		else                  {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
		
		if ($row['PEDAMT']!=0) {print "\n <td class=\"colnmbr\">" . number_format($row['PEDAMT'],2) . "</td> ";}
		else                   {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
		
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PESBCD]\">$row[PSDESC]</span></td>";
		print "\n <td class=\"colalph\">$row[PEGDED]</td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['IVLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location\">$row[IVLOC]</a></td> ";
		print "\n <td class=\"colalph\">$row[IVARPO]</td> ";
		if ($row['IVISEQ']==0) {print "\n <td class=\"coldate\">$F_IVIVDT</td> ";}
		else                   {print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['PEISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_IVIVDT</a></td> ";}
		print "\n <td class=\"coldate\">$F_IVDUED</td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[PECMNT]\">$row[HAS_PECMNT]</span></td> ";
		print "\n <td class=\"colalph\">$row[PEMEMO]</td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row[PEPMID_FLDESC]) . "\">$row[PEPMID]</span></td>";
		if     ($row['OESELECT']>0)  {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['IVORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[IVORD]</a></td> ";}
		elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['IVBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['IVAINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[IVORD]</a></td> ";}
		else                         {print "\n <td class=\"colnmbr\">$row[IVORD]</td> ";}
		if ($fromType=="P") {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[IVBLTO]]\">$row[CMCNA1]</a></td> ";}
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PEPTYP]\">$row[CPDESC]</span></td>";
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
print "\n </body> \n </html>";

?>
