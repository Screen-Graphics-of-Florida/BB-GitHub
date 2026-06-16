<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$paymentType        = "U";
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
$scriptName    = "ApplCashPaymentUnapplied.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId)) . "&amp;columnDisplay" . $columnDisplay . "&amp;paymentType=" . urlencode(trim($paymentType)) . "&amp;harcedProgram=HARCED_P{$paymentType}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("IVAINV","A","Invoice"),array("IVISEQ","A",""));
$tabID         = "PAYMENT";
$programName   = "HARCED";

$fromBatchDate_ISO = date ( 'Y-m-d', strtotime ( Date_CYMD_ISO ( $fromBatchDate ) . " - " . $CRDSPD . " days " ) );
$InvBalSQL     = "Case When IECRTB='A' Then 0 else IVIVAM-IVNPOS-IVPPOS-(-Coalesce((Select sum(PEAMT) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID,PEISEQ)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "',Coalesce(b.PEPTYP,' '),'$paymentID',a.IVISEQ)), 0)) End ";
$NetBalSQL     = "Case When IECRTB='I' Then IVIVAM-IVNPOS-IVPPOS Else Coalesce((Select sum(-PEAMT-PEDAMT) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID,PEISEQ)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "',Coalesce(b.PEPTYP,' '),'$paymentID',a.IVISEQ)), 0) End ";
$DscBalSQL     = "IVDSCT-IVDSTK-Coalesce(OTH_PEDAMT,0) ";
$DscAmtSQL     = "date('$fromBatchDate_ISO') >IVDSCD Then 0 When Sign(IVIVAM)<>Sign(IVIVAM-IVNPOS-IVPPOS-(-Coalesce(PEAMT-(-PEDAMT),0))) Then 0 When Sign(IVIVAM)<>Sign($DscBalSQL) Then 0 ";

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("Invoice:", "Selected", $viewCheckBoxURL, "1", "1"),
array("", "Debit", $viewCheckBoxURL, "2", "1"),
array("", "Credit", $viewCheckBoxURL, "3", "1"),
array("", "Fully Paid", $viewCheckBoxURL, "4", "0"));

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
	print "\n    && editNum(document.$formName.frInvoiceBal, 11, 2) ";
	print "\n    && editNum(document.$formName.toInvoiceBal, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frInvoiceBal, document.$formName.toInvoiceBal, document.$formName.operInvoiceBal, 15) ";
	print "\n    && editNum(document.$formName.frNetBal, 11, 2) ";
	print "\n    && editNum(document.$formName.toNetBal, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frNetBal, document.$formName.toNetBal, document.$formName.operNetBal, 15) ";
	require_once 'ApplCashPaymentInvoiceIncludeMasterSearchjs.php';
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHBATCHSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Invoice","frInvoice","toInvoice","operInvoice","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Payment Amount","frPaymentAmt","toPaymentAmt","operPaymentAmt","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Discount","frDiscountAmt","toDiscountAmt","operDiscountAmt","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Invoice Balance","frInvoiceBal","toInvoiceBal","operInvoiceBal","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Net Balance","frNetBal","toNetBal","operNetBal","opersel_num2_short","N","15","15");

	require 'ApplCashPaymentInvoiceIncludeMasterSearch.php';

	$focusField = "frInvoice";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Select")         {$orby = array(array("PESSEQ" ,"A","Selected Sequence"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "Invoice")        {$orby = array(array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "PmtAmount")      {$orby = array(array("PEAMT" ,"A","Payment Amount"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "Discount")       {$orby = array(array("PEDAMT" ,"A","Discount"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "InvBal")         {$orby = array(array("IVBALN" ,"A","Invoice Balance"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "NetBal")         {$orby = array(array("IVNETB" ,"A","Net Balance"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntrySubCode")   {$orby = array(array("PESBCD" ,"A","Payment Code"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryMemo")      {$orby = array(array("PEMEMO" ,"A","Entry Memo"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryReference") {$orby = array(array("PEARPO" ,"A","Entry Reference"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryLoc")       {$orby = array(array("PELOC" ,"A","Entry Location"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryBillTo")    {$orby = array(array("PEBLTO" ,"A","Entry Bill-To"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryShipTo")    {$orby = array(array("PESHTO" ,"A","Entry Ship-To"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryAccount")   {$orby = array(array("PEARAC,PEARSB" ,"A","Account"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryOEOrder")   {$orby = array(array("PEORD" ,"A","Entry Order Number"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryOrderDate") {$orby = array(array("PEORDT" ,"A","Entry Order Date"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryOrderLine") {$orby = array(array("PEORLN" ,"A","Entry Line Number"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryPlant")     {$orby = array(array("PEPLT" ,"A","Entry Plant"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryMfgOrder")  {$orby = array(array("PEMORD" ,"A","Entry Mfg Order"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntrySalesman")  {$orby = array(array("PESLSM" ,"A","Entry Salesman"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryTerms")     {$orby = array(array("PETRMS" ,"A","Entry Terms"),array("IVAINV" ,"A","Invoice"),array("IVISEQ","A",""),array("PEENID","A",""));}
	require_once 'ApplCashPaymentInvoiceIncludeOrderBy.php';
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Range_WildCard("IVAINV", "Invoice", $_POST['frInvoice'], $_POST['toInvoice'], "", $_POST['operInvoice'], "N");
	$returnValue=Range_WildCard("Coalesce(PEAMT,0)", "Payment Amount", $_POST['frPaymentAmt'], $_POST['toPaymentAmt'], "", $_POST['operPaymentAmt'], "N");
	$returnValue=Range_WildCard("Coalesce(PEDAMT,Case When $DscAmtSQL When ABS($InvBalSQL) < ABS($DscBalSQL) Then $InvBalSQL Else $DscBalSQL End,0)", "Discount", $_POST['frDiscountAmt'], $_POST['toDiscountAmt'], "", $_POST['operDiscountAmt'], "N");
	$returnValue=Range_WildCard($InvBalSQL, "Invoice Balance", $_POST['frInvoiceBal'], $_POST['toInvoiceBal'], "", $_POST['operInvoiceBal'], "N");
	$returnValue=Range_WildCard($NetBalSQL, "Net Balance", $_POST['frNetBal'], $_POST['toNetBal'], "", $_POST['operNetBal'], "N");
	require_once 'ApplCashPaymentInvoiceIncludeWildCard.php';
	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}
$sclcDefault = false;
$sdscDefault = false;

// Program Option Security
$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);

$iso_fromBatchDate=Reformat_Date_ISO($fromBatchDate, "*YMD", "*ISO");

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
$withSQL .= " With INVOICE ";
$withSQL .= " (IVBLTO,IVCUST,IVISEQ,IVIVCD,IVAINV,IVIVAM,IVNPOS,IVPPOS,IVDSCT,IVDSTK,IVFRT ,IVSTAX,IVSPC ,IVTRMS ";
$withSQL .= " ,IVDUED,IVDSCD,IVIVDT,IVARPO,IVORD ,IVORDT,IVORLN,IVPLT,IVMORD,IVLOC ,IVSLSM,IVPSDT,IVSBCD,IVCURT,IVCURD,IECRTB) ";
$withSQL .= " as ( ";
$withSQL .= " Select IVBLTO,IVCUST,IVISEQ,IVIVCD,IVAINV,IVIVAM,IVNPOS,IVPPOS,IVDSCT,IVDSTK,IVFRT ,IVSTAX,IVSPC ,IVTRMS ";
$withSQL .= "       ,IVDUED,IVDSCD,IVIVDT,IVARPO,IVORD ,IVORDT,IVORLN,IVPLT,IVMORD,IVLOC ,IVSLSM,IVPSDT,IVSBCD,IVCURT,IVCURD,'I' as IECRTB ";
$withSQL .= " From HDINVC";
$withSQL .= " union ";
$withSQL .= " Select PEBLTO as IVBLTO,PESHTO as IVCUST,IEISEQ as IVISEQ,IEPTYP as IVIVCD,0 as IVAINV,0 as IVIVAM,0 as IVNPOS,0 as IVPPOS,0 as IVDSCT,0 as IVDSTK, 0 as IVFRT ,0 as IVSTAX,0 as IVSPC,' ' as IVTRMS ";
$withSQL .= "       ,'0001-01-01' as IVDUED,'0001-01-01' as IVDSCD,0 as IVIVDT,' ' as IVARPO,0 as IVORD ,0 as IVORDT,0 as IVORLN,0 as IVPLT,' ' as IVMORD,0 as IVLOC ,0 as IVSLSM,0 as IVPSDT,' ' as IVSBCD,IECURT as IVCURT,IECURD as IVCURD,'A' as IECRTB ";
$withSQL .= " From ARIVEN Inner Join ARPYEN On (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,PECHK,PEPTYP,PEPMID,PEISEQ)=(IEBCHN,IEBCHD,IEBCHB,IETYPE,IEID,IECHK,IEPTYP,' ',IEISEQ) Where (IEBCHN,IEBCHD,IEBCHB,IETYPE,IEID,trim(IECHK),IEPTYP)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType') ";
$withSQL .= " ) ";
$stmtSQL .= " Select IECRTB,IVISEQ,IVIVCD,IVDSCD ";
$stmtSQL .= "       ,Case When IECRTB='I' Then IVAINV else Coalesce(PESINV,0) End as IVAINV ";
$stmtSQL .= "       ,$InvBalSQL as IVBALN ";
$stmtSQL .= "       ,$NetBalSQL as IVNETB ";
$stmtSQL .= "       ,Case When $DscAmtSQL ";
$stmtSQL .= "             When ABS($InvBalSQL) < ABS($DscBalSQL) Then $InvBalSQL "; 
$stmtSQL .= "             Else $DscBalSQL End as IVDSCT ";
$stmtSQL .= "       ,Coalesce(PEISEQ,IVISEQ)   as PEISEQ ";
$stmtSQL .= "       ,Coalesce(PEENID,0)   as PEENID ";
$stmtSQL .= "       ,Coalesce(PESSEQ,0)   as PESSEQ ";
$stmtSQL .= "       ,Coalesce(PESINV,0)   as PESINV ";
$stmtSQL .= "       ,Coalesce(PESPMT,' ') as PESPMT ";
$stmtSQL .= "       ,Case When Coalesce(PESPMT, ' ')='Y' Then 'CHECKED' ELSE ' ' End as CHECKSELECTION ";
$stmtSQL .= "       ,Coalesce(PEAMT , 0)   as PEAMT ";
$stmtSQL .= "       ,Case When PEDAMT is not Null Then PEDAMT ";
$stmtSQL .= "             When $DscAmtSQL ";
$stmtSQL .= "             When ABS($InvBalSQL) < ABS($DscBalSQL) Then $InvBalSQL "; 
$stmtSQL .= "             Else $DscBalSQL End as PEDAMT ";
$stmtSQL .= "       ,Coalesce(PESBCD,' ') as PESBCD ";
$stmtSQL .= "       ,Coalesce(PEMEMO,' ') as PEMEMO ";
$stmtSQL .= "       ,Coalesce(PEARPO,' ') as PEARPO ";
$stmtSQL .= "       ,Coalesce(PELOC,0)    as PELOC ";
$stmtSQL .= "       ,Coalesce(PEBLTO,0)   as PEBLTO ";
$stmtSQL .= "       ,Coalesce(PESHTO,0)   as PESHTO ";
$stmtSQL .= "       ,Coalesce(PEARAC,0)   as PEARAC ";
$stmtSQL .= "       ,Coalesce(PEARSB,0)   as PEARSB ";
$stmtSQL .= "       ,Coalesce(PEORD,0)    as PEORD ";
$stmtSQL .= "       ,Coalesce(PEORDT,0)   as PEORDT ";
$stmtSQL .= "       ,Coalesce(PEORLN,0)   as PEORLN ";
$stmtSQL .= "       ,Coalesce(PEPLT,0)    as PEPLT ";
$stmtSQL .= "       ,Coalesce(PEMORD,' ') as PEMORD ";
$stmtSQL .= "       ,Coalesce(PESLSM,0)   as PESLSM ";
$stmtSQL .= "       ,Coalesce(PETRMS,' ') as PETRMS ";
$stmtSQL .= "       ,Coalesce(PECMNT,' ') as PECMNT ";
$stmtSQL .= "       ,Coalesce(PEPOTP,' ') as PEPOTP ";
$stmtSQL .= "       ,Coalesce(PEPOEN,0)   as PEPOEN ";
require_once 'ApplCashPaymentInvoiceIncludeSQLSelect.php';  // Includes HDINVC columns
$fileSQL .= " INVOICE a ";
$fileSQL .= " left join ARPYEN b on (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID,PEISEQ)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',IVISEQ) ";
require_once 'ApplCashPaymentInvoiceIncludeSQLFrom.php';
$selectSQL .= "(IECRTB='A' or (IECRTB = 'I' and ";
if ($fromType=="C") {$selectSQL .= "IVBLTO=$fromID ";}
else                {$selectSQL .= "IVBLTO in (Select CICUST from HDCUSI Where (CIIBCH,CIIDTE,CIIBNK,CIIUSR,CITYPE,CIID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$userProfile','$fromType',$fromID))";}
$selectSQL .= " and IVIVCD='U')) ";                // Unapplied Cash
if ($HDMCRL>0 && $CRPRMC=="Y") {
	$BKCURT=RetValue("BKBANK=$fromBatchBank", "HDBANK", "BKCURT");
	$CFCURT=RetValue("BKBANK=$fromBatchBank", "HDBANK inner join HDCFAC on (CFCO#,CFFAC#)=(BKCO,BKFAC)", "CFCURT");
	$selectSQL .= " and (IVCURT,IVCURD)=('$BKCURT','$CFCURT') ";
}
$viewCheckSQL="";
if ($viewCheckBox[0] || $viewCheckBox[1] || $viewCheckBox[2] || $viewCheckBox[3]) {
	if     ($viewCheckBox[0])                        {$viewCheckSQL.= " (Coalesce(PESPMT,' ')='Y' or Coalesce(PEENID,0)>0";}
	if     ($viewCheckBox[1] && $viewCheckSQL == "") {$viewCheckSQL.= " (Coalesce(PESPMT,' ')=' ' and Coalesce(PEENID,0)=0 and IVIVAM-IVNPOS-IVPPOS>0";}     // minus a minus= add
	elseif ($viewCheckBox[1])                        {$viewCheckSQL.= "  or Coalesce(PESPMT,' ')=' ' and Coalesce(PEENID,0)=0 and IVIVAM-IVNPOS-IVPPOS>0";}
	if     ($viewCheckBox[2] && $viewCheckSQL == "") {$viewCheckSQL.= " (Coalesce(PESPMT,' ')=' ' and Coalesce(PEENID,0)=0 and IVIVAM-IVNPOS-IVPPOS<0";}
	elseif ($viewCheckBox[2])                        {$viewCheckSQL.= "  or Coalesce(PESPMT,' ')=' ' and Coalesce(PEENID,0)=0 and IVIVAM-IVNPOS-IVPPOS<0";}
	if     ($viewCheckBox[3] && $viewCheckSQL == "") {$viewCheckSQL.= " (Coalesce(PESPMT,' ')=' ' and Coalesce(PEENID,0)=0 and IVIVAM-IVNPOS-IVPPOS=0";}
	elseif ($viewCheckBox[3])                        {$viewCheckSQL.= "  or Coalesce(PESPMT,' ')=' ' and Coalesce(PEENID,0)=0 and IVIVAM-IVNPOS-IVPPOS=0";}
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

require_once 'ApplCashPaymentUnappliedJava.php';
require_once 'ApplCashPaymentJava.php';

print "\n function confirmDelete(deleteMsg) {return confirm(\"{$delRecordConf} \" +  \"\\n\" + \"\\n\" + deleteMsg);} ";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterAjax(ARQuickEntry)\">";
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
	require 'ApplCashPaymentInvoiceIncludeQuickFilter.php';
	require 'QuickSearchOption.php';

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"\" onSubmit=\"return ARQuickEntry(); \">";
	print "\n <table $contentTable id=\"paymentTable\"> <tr>";
	if (1==0) {print "\n <th>&nbsp;</th>";}
	$returnValue=OrderBy_Sort("PESSEQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Select\"    title=\"Sequence By Selected\">{$sortPoint}Sel</a></th>";
	$returnValue=OrderBy_Sort("IVAINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\"   title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th>";
	$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtAmount\" title=\"Sequence By Payment Amount\">{$sortPoint}Payment Amount</a></th>";
	if ($columnDisplay['PEDAMT']=="Y") {
		$returnValue=OrderBy_Sort("PEDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Discount\"    title=\"Sequence By Discount\">{$sortPoint}Discount</a></th>";
	}
	print "\n <th class=\"colhdr\">&nbsp;</th>";
	if ($columnDisplay['IVBALN']=="Y") {
		$returnValue=OrderBy_Sort("IVBALN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvBal\"    title=\"Sequence By Invoice Balance\">{$sortPoint}Invoice Balance</a></th>";
	}
	if ($columnDisplay['IVNETB']=="Y") {
		$returnValue=OrderBy_Sort("IVNETB"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=NetBal\"    title=\"Sequence By Net Balance\">{$sortPoint}Net Balance</a></th>";
	}
	if ($columnDisplay['ADJAMT']=="Y") {
        print "\n <th class=\"colhdr\">Other Pending Activity</th> ";
	}
	if ($columnDisplay['PESBCD']=="Y") {
		$returnValue=OrderBy_Sort("PESBCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntrySubCode\"    title=\"Sequence By Payment Code\">{$sortPoint}Payment Code</a></th>";
	}
	if ($columnDisplay['PEMEMO']=="Y") {
		$returnValue=OrderBy_Sort("PEMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryMemo\"    title=\"Sequence By Memo\">{$sortPoint}Memo</a></th>";
	}
	if ($columnDisplay['PEARPO']=="Y") {
		$returnValue=OrderBy_Sort("PEARPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryReference\"    title=\"Sequence By Reference Number\">{$sortPoint}Reference Number</a></th>";
	}
	if ($columnDisplay['PELOC']=="Y") {
		$returnValue=OrderBy_Sort("PELOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryLoc\"    title=\"Sequence By Location\">{$sortPoint}Location</a></th>";
	}
	if ($columnDisplay['PEBLTO']=="Y" && $fromType=="P") {
		$returnValue=OrderBy_Sort("PEBLTO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryBillTo\"    title=\"Sequence By Bill-To\">{$sortPoint}Bill-To</a></th>";
	}
	if ($columnDisplay['PESHTO']=="Y") {
		$returnValue=OrderBy_Sort("PESHTO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryShipTo\"    title=\"Sequence By Ship-To\">{$sortPoint}Ship-To</a></th>";
	}
	if ($columnDisplay['PEARAC']=="Y") {
		$returnValue=OrderBy_Sort("PEARAC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryAccount\"    title=\"Sequence By A/R Account\">{$sortPoint}A/R Account</a></th>";
	}
	if ($columnDisplay['PEORD']=="Y") {
		$returnValue=OrderBy_Sort("PEORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryOEOrder\"    title=\"Sequence By Order Number\">{$sortPoint}Order Number</a></th>";
	}
	if ($columnDisplay['PEORDT']=="Y") {
		$returnValue=OrderBy_Sort("PEORDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryOrderDate\"    title=\"Sequence By Order Date\">{$sortPoint}Order Date</a></th>";
	}
	if ($columnDisplay['PEORLN']=="Y") {
		$returnValue=OrderBy_Sort("PEORLN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryOrderLine\"    title=\"Sequence By Line Number\">{$sortPoint}Line Number</a></th>";
	}
	if ($columnDisplay['PEPLT']=="Y") {
		$returnValue=OrderBy_Sort("PEPLT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryPlant\"    title=\"Sequence By Plant\">{$sortPoint}Plant</a></th>";
	}
	if ($columnDisplay['PEMORD']=="Y") {
		$returnValue=OrderBy_Sort("PEMORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryMfgOrder\"    title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th>";
	}
	if ($columnDisplay['PESLSM']=="Y") {
		$returnValue=OrderBy_Sort("PESLSM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntrySalesman\"    title=\"Sequence By Salesman\">{$sortPoint}Salesman</a></th>";
	}
	if ($columnDisplay['PETRMS']=="Y") {
		$returnValue=OrderBy_Sort("PETRMS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryTerms\"    title=\"Sequence By Terms\">{$sortPoint}Terms</a></th>";
	}

	require_once 'ApplCashPaymentInvoiceIncludeHeading.php';
	print "\n </tr>";

	// Quick Entry Row
	require  'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	if (1==0) {print "\n     <td class=\"entry\">&nbsp;</td> ";}
	print "\n     <td class=\"entry\"><a onClick=\"ARQuickEntry();\">$applCashAcceptImage</a></td>";
	if ($CRUAUI=="E") {print "\n <td class=\"entry\"><input type=\"text\" name=\"addInvoiceNumber\" id=\"addInvoiceNumber\" size=\"7\" maxlength=\"7\"></td> ";}
	else              {print "\n <td class=\"entry\"><input type=\"hidden\" name=\"addInvoiceNumber\" id=\"addInvoiceNumber\"></td> ";}
	print "\n     <td class=\"entry\"><input type=\"text\" name=\"addAmount\" id=\"addAmount\" size=\"9\" maxlength=\"15\"></td> ";
	if ($columnDisplay['PEDAMT']=="Y")  {print "\n     <td class=\"entry\"><input type=\"text\" name=\"addDiscount\" id=\"addDiscount\" size=\"9\" maxlength=\"15\"></td> ";}
	print "\n     <td class=\"entry\">&nbsp;</td> ";
	if ($columnDisplay['IVBALN']=="Y") {print "\n     <td class=\"entry\">&nbsp;</td> ";}
	if ($columnDisplay['IVNETB']=="Y") {print "\n     <td class=\"entry\">&nbsp;</td> ";}
	if ($columnDisplay['ADJAMT']=="Y") {print "\n     <td class=\"entry\">&nbsp;</td> ";}

	if ($columnDisplay['PESBCD']=="Y")  {
		print "\n <td class=\"entry\" nowrap><input type=\"text\" name=\"addPmtCode\" id=\"addPmtCode\" size=\"4\" maxlength=\"4\"> ";
		print "\n                            <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=addPmtCode&amp;fldDesc=none&amp;specificBatchType={$BMBCHT}&amp;specificPmtType=$paymentType&amp;forceChange=\" onclick=\"$searchWinVar\">$searchImage</a></td>  ";
	}
	if ($columnDisplay['PEMEMO']=="Y") {print "\n     <td class=\"entry\"><input type=\"text\" name=\"addMemo\" id=\"addMemo\" size=\"9\" maxlength=\"15\"></td> ";}
	if ($columnDisplay['PEARPO']=="Y") {print "\n <td class=\"entry\"><input type=\"text\" name=\"addReference\" id=\"addReference\" size=\"10\" maxlength=\"22\"></td> ";}
	if ($columnDisplay['PELOC']=="Y")  {
		print "\n <td class=\"entry\" nowrap><input type=\"text\" name=\"addLocation\" id=\"addLocation\" size=\"3\" maxlength=\"3\"> ";
		print "\n                     <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=addLocation&amp;fldDesc=none&amp;forceChange=\" onclick=\"$searchWinVar\">$searchImage </a></td>  ";
	}
	if ($columnDisplay['PEBLTO']=="Y" && $fromType=="P") {
		print "\n <td class=\"entry\" nowrap><input type=\"text\" name=\"addBillTo\" id=\"addBillTo\" size=\"7\" maxlength=\"7\"> ";
		print "\n                     <a href=\"{$homeURL}{$phpPath}PayerCustomerSearch.php{$scriptVarBase}&amp;specificPayer={$fromID}&amp;docName=Chg&amp;fldName=addBillTo&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td>  ";
	}
	if ($columnDisplay['PESHTO']=="Y") {
		print "\n <td class=\"entry\" nowrap><input type=\"text\" name=\"addShipTo\" id=\"addShipTo\" size=\"7\" maxlength=\"7\"> ";
		if ($fromType=="P") {print "\n                     <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=addShipTo&amp;fldDesc=none&amp;forceChange=\" onclick=\"$searchWinVar\">$searchImage </a></td>  ";}
		else                {print "\n                     <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=addShipTo&amp;fldDesc=none&amp;forceChange=&amp;forCustomer=$fromID\" onclick=\"$searchWinVar\">$searchImage </a></td>  ";}
	}
	if ($columnDisplay['PEARAC']=="Y")  {
		print "\n     <td class=\"entry\" nowrap><input type=\"text\" name=\"addAccount\" id=\"addAccount\" size=\"4\" maxlength=\"4\"> ";
		print "\n                                <input type=\"text\" name=\"addSubaccount\" id=\"addSubaccount\" size=\"4\" maxlength=\"4\"> ";
		print "\n                                <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=addAccount&amp;subFld=addSubaccount&amp;descFld=none&amp;forceChange=\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	}
	if ($columnDisplay['PEORD']=="Y")  {print "\n <td class=\"entry\"><input type=\"text\" name=\"addOEOrder\" id=\"addOEOrder\" size=\"8\" maxlength=\"8\"></td> ";}
	if ($columnDisplay['PEORDT']=="Y") {print "\n <td class=\"entry\"><input type=\"text\" name=\"addOrderDate\" id=\"addOrderDate\" size=\"6\" maxlength=\"6\"></td> ";}
	if ($columnDisplay['PEORLN']=="Y") {print "\n <td class=\"entry\"><input type=\"text\" name=\"addOrderLine\" id=\"addOrderLine\" size=\"3\" maxlength=\"3\"></td> ";}
	if ($columnDisplay['PEPLT']=="Y") {
		print "\n <td class=\"entry\" nowrap><input type=\"text\"     name=\"addPlant\" id=\"addPlant\" size=\"3\" maxlength=\"3\"> ";
		if ($HDPDRL>0) {print "\n                            <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=addPlant&amp;fldDesc=none&amp;forceChange=\" onclick=\"$searchWinVar\">$searchImage</a>";}
		print "\n </td>  ";
	}
	if ($columnDisplay['PEMORD']=="Y") {print "\n <td class=\"entry\"><input type=\"text\" name=\"addMfgOrder\" id=\"addMfgOrder\" size=\"9\" maxlength=\"9\"></td> ";}
	if ($columnDisplay['PESLSM']=="Y") {
		print "\n <td class=\"entry\" nowrap><input type=\"text\"     name=\"addSalesman\" id=\"addSalesman\" size=\"3\" maxlength=\"3\"> ";
		print "\n                                <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=addSalesman&amp;fldDesc=none&amp;forceChange=\" onclick=\"$searchWinVar\">$searchImage</a></td>  ";
	}
	if ($columnDisplay['PETRMS']=="Y") {
		print "\n <td class=\"entry\" nowrap><input type=\"text\"     name=\"addTermsCode\" id=\"addTermsCode\" size=\"2\" maxlength=\"2\"> ";
		print "\n                                <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=addTermsCode&amp;fldDesc=none&amp;forceChange=\" onclick=\"$searchWinVar\">$searchImage</a></td>  ";
	}
	// Add hidden fields needed for Javascript and the form
	if ($columnDisplay['PEDAMT']!="Y") {print "\n <td><input type=\"hidden\" name=\"addDiscount\" id=\"addDiscount\" value=\"\"></td> ";}
	if ($columnDisplay['PESBCD']!="Y") {print "\n <td><input type=\"hidden\" name=\"addPmtCode\" id=\"addPmtCode\" value=\"\"></td> ";}
	if ($columnDisplay['PEMEMO']!="Y") {print "\n <td><input type=\"hidden\" name=\"addMemo\" id=\"addMemo\" value=\"\"></td> ";}
	if ($columnDisplay['PEARPO']!="Y") {print "\n <td><input type=\"hidden\" name=\"addReference\" id=\"addReference\" value=\"\"></td> ";}
	if ($columnDisplay['PELOC']!="Y")  {print "\n <td><input type=\"hidden\" name=\"addLocation\" id=\"addLocation\" value=\"\"></td> ";}
	if ($columnDisplay['PEBLTO']!="Y" || $fromType=="C") {print "\n <td><input type=\"hidden\" name=\"addBillTo\" id=\"addBillTo\" value=\"\"></td> ";}
	if ($columnDisplay['PESHTO']!="Y") {print "\n <td><input type=\"hidden\" name=\"addShipTo\" id=\"addShipTo\" value=\"\"></td> ";}
	if ($columnDisplay['PEARAC']!="Y") {
		print "\n <td><input type=\"hidden\" name=\"addAccount\" id=\"addAccount\" value=\"\"> ";
		print "\n     <input type=\"hidden\" name=\"addSubaccount\" id=\"addSubaccount\" value=\"\"></td> ";
	}
	if ($columnDisplay['PEORD']!="Y")  {print "\n <td><input type=\"hidden\" name=\"addOEOrder\" id=\"addOEOrder\" value=\"\"></td> ";}
	if ($columnDisplay['PEORDT']!="Y") {print "\n <td><input type=\"hidden\" name=\"addOrderDate\" id=\"addOrderDate\" value=\"\"></td> ";}
	if ($columnDisplay['PEORLN']!="Y") {print "\n <td><input type=\"hidden\" name=\"addOrderLine\" id=\"addOrderLine\" value=\"\"></td> ";}
	if ($columnDisplay['PEPLT']!="Y")  {print "\n <td><input type=\"hidden\" name=\"addPlant\" id=\"addPlant\" value=\"\"></td> ";}
	if ($columnDisplay['PEMORD']!="Y") {print "\n <td><input type=\"hidden\" name=\"addMfgOrder\" id=\"addMfgOrder\" value=\"\"></td> ";}
	if ($columnDisplay['PESLSM']!="Y") {print "\n <td><input type=\"hidden\" name=\"addSalesman\" id=\"addSalesman\" value=\"\"></td> ";}
	if ($columnDisplay['PETRMS']!="Y") {print "\n <td><input type=\"hidden\" name=\"addTermsCode\" id=\"addTermsCode\" value=\"\"></td> ";}
	print "\n </tr> ";

	require_once 'ApplCashPaymentJavaUpdateHiddenInclude.php';

	// Invoice rows
	$rowCount = 0;
	$saveIVISEQ=0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		$maintainVar = "{$scriptVarBase}&amp;fromPaymentType=" . urlencode(trim($paymentType)) . "&amp;fromPaymentID=" . urlencode(trim($paymentID)) . "&amp;fromInvoiceSeq=" . urlencode(trim($row['PEISEQ'])) . "&amp;fromEntryID=" . urlencode(trim($row['PEENID']));
		$row['ADJAMT'] = RetValue ("(PEISEQ,PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK))=(" . $row['PEISEQ'] . ",$fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') and (PEPTYP<>'$paymentType' or PEPMID<>'$paymentID') and (PEPTYP<>'" . $row['PEPOTP'] . "' or PEENID<>" . $row['PEPOEN'] . ") ", "ARPYEN", "Sum(PEAMT-(-PEDAMT))" );
		
		require  'ApplCashPaymentJavaHiddenUpdateArrayElement.php';  // Add hidden Java Values to Variable
		require  'SetRowClass.php';

		$row['PEORDT']=DateInputFromCYMD($row['PEORDT']);
		$F_IVDUED=Format_Date_ISO($row['IVDUED'], "D");
		$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
		$F_IVPSDT=Format_Date($row['IVPSDT'], "D");
		if ($row['ARPYENERROR']>0) {
			$PESINV_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PESINV') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEAMT_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEAMT') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEDAMT_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEDAMT') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PESBCD_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PESBCD') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEMEMO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEMEMO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEARPO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEARPO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PELOC_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PELOC') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEBLTO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEBLTO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PESHTO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PESHTO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEARAC_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEARAC') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEORD_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEORD') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEORDT_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEORDT') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEORLN_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEORLN') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEPLT_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEPLT') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEMORD_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEMORD') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PESLSM_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PESLSM') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PETRMS_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PETRMS') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		} else {
			$PESINV_ERROR="";
			$PEAMT_ERROR="";
			$PEDAMT_ERROR="";
			$PESBCD_ERROR="";
			$PEMEMO_ERROR="";
			$PEARPO_ERROR="";
			$PELOC_ERROR="";
			$PEBLTO_ERROR="";
			$PESHTO_ERROR="";
			$PEARAC_ERROR="";
			$PEORD_ERROR="";
			$PEORDT_ERROR="";
			$PEORLN_ERROR="";
			$PEPLT_ERROR="";
			$PEMORD_ERROR="";
			$PESLSM_ERROR="";
			$PETRMS_ERROR="";
		}

		print "\n <tr class=\"$rowClass\" id=\"row{$row['IVISEQ']}_{$row['PEENID']}\"> ";

		// Insert icon
		if (1==0) {
			print "\n     <td class=\"inputcode\" id=addLine{$row['IVISEQ']}_{$row['PEENID']}> ";
			if ($row['PEENID']==$row['MINPEENID'] && $row['PESSEQ']>0) {print "\n <a onClick=\"insertARPYENLine('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\">$applCashAddPaymentLine</a> ";}
			else                                                       {print "\n &nbsp; ";}
			print "\n     </td>" ;
		}

		// Delete icon
		print "\n     <td class=\"inputcode\"> ";
		print "\n         <span id=\"spmt{$row['IVISEQ']}_{$row['PEENID']}\"> ";
		$deleteMsg="Payment for invoice {$row[IVAINV]}";
		if ($row['PESPMT']=="Y") {
			if ($applCashPaymentDeletePrompt=="Y") {
				print "\n <a onClick=\"if(confirmDelete('$deleteMsg')) {delARPYENLine('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]','$row[IECRTB]');} \">$deleteImageSml</a> ";
			} else {
				print "\n <a onClick=\"delARPYENLine('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]','$row[IECRTB]'); \">$deleteImageSml</a> ";
			}
		} else {
			print "\n &nbsp; ";
		}
		print "\n         </span> ";
		print "\n     </td>";

		// Entry - Selected Invoice
		if ($row['IVISEQ']!=$saveIVISEQ) {
			if ($row['IECRTB']=="A" && $CRUAUI=="E") {
				if ($PESINV_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESINV_ERROR\"  ";}
				else                   {$FldStyle="";}
				print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"sinv{$row['IVISEQ']}_{$row['PEENID']}\" id=\"sinv{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['IVAINV']) . "\" size=\"7\" maxlength=\"7\" $FldStyle onChange=\"editPESINV('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
			} else {print "\n <td class=\"colnmbr\"><input type=\"hidden\" name=\"sinv{$row['IVISEQ']}_{$row['PEENID']}\" id=\"sinv{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['IVAINV']) . "\">$row[IVAINV]</td> ";}
		} else {
			print "\n <td class=\"colnmbr\"><input type=\"hidden\" name=\"sinv{$row['IVISEQ']}_{$row['PEENID']}\" id=\"sinv{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['IVAINV']) . "\">&nbsp;</td> ";
		}
		$saveIVISEQ=$row['IVISEQ'];

		// Entry - Payment Amount
		if ($row['IECRTB']=="A") {
			if ($PEAMT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEAMT_ERROR\"  ";}
			else                  {$FldStyle="";}
			if ($row['PEAMT']!=0) {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEAMT'],2, '.', '') . "\" size=\"9\" maxlength=\"15\" $FldStyle onChange=\"editPEAMT('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
			else                  {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"\" size=\"9\" maxlength=\"15\" $FldStyle onChange=\"editPEAMT('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
		} else {print "\n <td class=\"colnmbr\"><input type=\"hidden\" name=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEAMT'],2, '.', '') . "\">&nbsp;</td> ";}


		// Entry - Discount
		if ($columnDisplay['PEDAMT']=="Y") {
			if ($row['IECRTB']=="A") {
				if ($PEDAMT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEDAMT_ERROR\"  ";}
				else                   {$FldStyle="";}
				if ($row['PEDAMT']!=0) {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"damt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"damt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEDAMT'],2, '.', '') . "\" size=\"9\" maxlength=\"15\" $FldStyle onChange=\"editPEDAMT('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
				else                   {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"damt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"damt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"\" size=\"9\" maxlength=\"15\" $FldStyle onChange=\"editPEDAMT('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
			} else {print "\n <td class=\"colnmbr\"><input type=\"hidden\" name=\"damt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"damt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEDAMT'],2, '.', '') . "\">&nbsp;</td> ";}
		}

		// icons
		require 'ApplCashPaymentIconsInclude.php';

		// Invoice Balance
		if ($columnDisplay['IVBALN']=="Y") {
			$balClass="colnmbr";
			if ($row['IECRTB']=="I" && $row['PEENID']==$row['MINPEENID']) {print "\n <td class=\"$balClass\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$scriptVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['IVISEQ'])) . "&amp;noMenu=Y\" title=\"View Miscellaneous Payment Number\" onclick=\"$searchWinVar\">" . number_format($row[IVBALN],2) . "</a></td> ";}
			elseif ($row['PEENID']==$row['MINPEENID'])                    {print "\n <td class=\"$balClass\">" . number_format($row[IVBALN],2) . "</td> ";}
			else                                                          {print "\n <td class=\"$balClass\">&nbsp;</td> ";}
		}

		// Net Balance
		if ($columnDisplay['IVNETB']=="Y") {
			if ($row['PEENID']==$row['MINPEENID']) {print "\n <td class=\"colnmbr\" id=\"netb{$row['IVISEQ']}_{$row['PEENID']}\">" . number_format($row[IVNETB],2) . "</td> ";}
			else                                   {print "\n <td class=\"colnmbr\" id=\"netb{$row['IVISEQ']}_{$row['PEENID']}\">&nbsp;</td> ";}
		}

		// Other Pending
		if ($columnDisplay['ADJAMT']=="Y") {
			if ($row['ADJAMT']<>0 && $row['PEENID']==$row['MINPEENID']) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashPaymentOtherInquiry.php{$maintainVar}\" title=\"View Other Pending Activity\" onclick=\"$searchWinVar\">" . number_format($row['ADJAMT'],2) . "</a></td> ";}
			else                                                        {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
		}

		// Entry - Payment Code
		if ($columnDisplay['PESBCD']=="Y") {
			if ($row['IECRTB']=="A") {
				if ($PESBCD_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESBCD_ERROR\"  ";}
				else                   {$FldStyle="";}
				print "\n     <td class=\"inputalph\" nowrap><input type=\"text\" name=\"sbcd{$row['IVISEQ']}_{$row['PEENID']}\" id=\"sbcd{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESBCD']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPESBCD('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
				print "\n                                    <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=sbcd{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none&amp;specificBatchType={$BMBCHT}&amp;specificPmtType=$paymentType\" onclick=\"$searchWinVar\">$searchImage</a></td>  ";
			} else {print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"sbcd{$row['IVISEQ']}_{$row['PEENID']}\" id=\"sbcd{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESBCD']) . "\">&nbsp;</td> ";}
		}

		// Entry - Memo
		if ($columnDisplay['PEMEMO']=="Y") {
			if ($row['IECRTB']=="A") {
				if ($PEMEMO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEMEMO_ERROR\"  ";}
				else                   {$FldStyle="";}
				print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"memo{$row['IVISEQ']}_{$row['PEENID']}\" id=\"memo{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMEMO']) . "\" size=\"9\" maxlength=\"15\" $FldStyle onChange=\"editPEMEMO('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
			} else {print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"memo{$row['IVISEQ']}_{$row['PEENID']}\" id=\"memo{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMEMO']) . "\">&nbsp;</td> ";}
		}

		// Entry - Reference
		if ($columnDisplay['PEARPO']=="Y") {
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				if ($PEARPO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEARPO_ERROR\"  ";}
				else                   {$FldStyle="";}
				print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"arpo{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arpo{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARPO']) . "\" size=\"9\" maxlength=\"22\" $FldStyle onChange=\"editPEARPO('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
			} else {
				print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"arpo{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arpo{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARPO']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Location
		if ($columnDisplay['PELOC']=="Y") {
			if ($PELOC_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PELOC_ERROR\"  ";}
			else                  {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n     <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"loc{$row['IVISEQ']}_{$row['PEENID']}\" id=\"loc{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PELOC']) . "\" size=\"3\" maxlength=\"3\" $FldStyle onBlur=\"editPELOC('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
				print "\n                                    <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=loc{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td>  ";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"loc{$row['IVISEQ']}_{$row['PEENID']}\" id=\"loc{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PELOC']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Bill-To
		if ($columnDisplay['PEBLTO']=="Y" && $fromType=="P") {
			if ($PEBLTO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEBLTO_ERROR\"  ";}
			else                   {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"blto{$row['IVISEQ']}_{$row['PEENID']}\" id=\"blto{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEBLTO']) . "\" size=\"7\" maxlength=\"7\" $FldStyle onBlur=\"editPEBLTO('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
				print "\n                                <a href=\"{$homeURL}{$phpPath}PayerCustomerSearch.php{$scriptVarBase}&amp;specificPayer={$fromID}&amp;docName=Chg&amp;fldName=blto{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td>  ";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"blto{$row['IVISEQ']}_{$row['PEENID']}\" id=\"blto{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEBLTO']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Ship-To
		if ($columnDisplay['PESHTO']=="Y") {
			if ($PESHTO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESHTO_ERROR\"  ";}
			else                   {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"shto{$row['IVISEQ']}_{$row['PEENID']}\" id=\"shto{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESHTO']) . "\" size=\"7\" maxlength=\"7\" $FldStyle onBlur=\"editPESHTO('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
				if ($fromType=="P") {print "\n                     <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=shto{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td>  ";}
				else                {print "\n                     <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=shto{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none&amp;forCustomer=$fromID\" onclick=\"$searchWinVar\">$searchImage </a></td>  ";}
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"shto{$row['IVISEQ']}_{$row['PEENID']}\" id=\"shto{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESHTO']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - A/R Account
		if ($columnDisplay['PEARAC']=="Y") {
			if ($PEARAC_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEARAC_ERROR\"  ";}
			else                   {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n     <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"arac{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arac{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARAC']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPEARAC('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
				print "\n                                    <input type=\"text\" name=\"arsb{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arsb{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARSB']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPEARSB('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
				print "\n                                    <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=arac{$row['IVISEQ']}_{$row['PEENID']}&amp;subFld=arsb{$row['IVISEQ']}_{$row['PEENID']}&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"arac{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arac{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARAC']) . "\" $FldStyle> ";
				print "\n                         <input type=\"hidden\" name=\"arsb{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arsb{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARSB']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Order Number
		if ($columnDisplay['PEORD']=="Y") {
			if ($PEORD_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEORD_ERROR\"  ";}
			else                  {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"ord{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ord{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORD']) . "\" size=\"8\" maxlength=\"8\" $FldStyle onChange=\"editPEORD('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"ord{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ord{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORD']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Order Date
		if ($columnDisplay['PEORDT']=="Y") {
			if ($PEORDT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEORDT_ERROR\"  ";}
			else                  {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"ordt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ordt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORDT']) . "\" size=\"6\" maxlength=\"6\" $FldStyle onBlur=\"editPEORDT('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"ordt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ordt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORDT']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Order Line
		if ($columnDisplay['PEORLN']=="Y") {
			if ($PEORLN_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEORLN_ERROR\"  ";}
			else                   {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"orln{$row['IVISEQ']}_{$row['PEENID']}\" id=\"orln{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORLN']) . "\" size=\"3\" maxlength=\"3\" $FldStyle onChange=\"editPEORLN('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"orln{$row['IVISEQ']}_{$row['PEENID']}\" id=\"orln{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORLN']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Plant
		if ($columnDisplay['PEPLT']=="Y") {
			if ($PEPLT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEPLT_ERROR\"  ";}
			else                  {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"plt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"plt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEPLT']) . "\" size=\"3\" maxlength=\"3\" $FldStyle onBlur=\"editPEPLT('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
				if ($HDPDRL>0) {print "\n                                <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=plt{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a> ";}
				print "\n </td>  ";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"plt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"plt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEPLT']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Mfg Order
		if ($columnDisplay['PEMORD']=="Y") {
			if ($PEMORD_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEMORD_ERROR\"  ";}
			else                   {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n <td class=\"inputalph\"><input type=\"text\" name=\"mord{$row['IVISEQ']}_{$row['PEENID']}\" id=\"mord{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMORD']) . "\" size=\"9\" maxlength=\"9\" $FldStyle onChange=\"editPEMORD('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
			} else {
				print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"mord{$row['IVISEQ']}_{$row['PEENID']}\" id=\"mord{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMORD']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Salesman
		if ($columnDisplay['PESLSM']=="Y") {
			if ($PESLSM_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESLSM_ERROR\"  ";}
			else                   {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"slsm{$row['IVISEQ']}_{$row['PEENID']}\" id=\"slsm{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESLSM']) . "\" size=\"3\" maxlength=\"3\" $FldStyle onBlur=\"editPESLSM('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
				print "\n                                <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=slsm{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td>  ";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"slsm{$row['IVISEQ']}_{$row['PEENID']}\" id=\"slsm{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESLSM']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Entry - Terms
		if ($columnDisplay['PETRMS']=="Y") {
			if ($PETRMS_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PETRMS_ERROR\"  ";}
			else                   {$FldStyle="";}
			if ($row['PEENID']==$row['MINPEENID'] && $row['IECRTB']=="A") {
				print "\n <td class=\"inputalph\" nowrap><input type=\"text\"     name=\"trms{$row['IVISEQ']}_{$row['PEENID']}\" id=\"trms{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PETRMS']) . "\" size=\"2\" maxlength=\"2\" $FldStyle onBlur=\"editPETRMS('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
				print "\n                                <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=trms{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td>  ";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"trms{$row['IVISEQ']}_{$row['PEENID']}\" id=\"trms{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PETRMS']) . "\" $FldStyle>&nbsp;</td> ";
			}
		}

		// Information columns
		if ($row['PEENID']==$row['MINPEENID']) {require 'ApplCashPaymentInvoiceIncludeDetail.php';}

		// Add hidden fields needed for Javascript
		print "\n <td><input type=\"hidden\" id=\"pseq{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEISEQ']) . "\"></td> ";
		if ($columnDisplay['IVNETB']!="Y") {print "\n <td><input type=\"hidden\" id=\"netb{$row['IVISEQ']}_{$row['PEENID']}\"></td> ";}
		if ($columnDisplay['PEDAMT']!="Y") {print "\n <td><input type=\"hidden\" name=\"damt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"damt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEDAMT']) . "\"></td> ";}
		if ($columnDisplay['PESBCD']!="Y") {print "\n <td><input type=\"hidden\" name=\"sbcd{$row['IVISEQ']}_{$row['PEENID']}\" id=\"sbcd{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESBCD']) . "\"></td> ";}
		if ($columnDisplay['PEMEMO']!="Y") {print "\n <td><input type=\"hidden\" name=\"memo{$row['IVISEQ']}_{$row['PEENID']}\" id=\"memo{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMEMO']) . "\"></td> ";}
		if ($columnDisplay['PEARPO']!="Y") {print "\n <td><input type=\"hidden\" name=\"arpo{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arpo{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARPO']) . "\"></td> ";}
		if ($columnDisplay['PELOC']!="Y")  {print "\n <td><input type=\"hidden\" name=\"loc{$row['IVISEQ']}_{$row['PEENID']}\"  id=\"loc{$row['IVISEQ']}_{$row['PEENID']}\"  value=\"" . rtrim($row['PELOC']) . "\" ></td> ";}
		if ($columnDisplay['PEBLTO']!="Y" || $fromType=="C") {print "\n <td><input type=\"hidden\" name=\"blto{$row['IVISEQ']}_{$row['PEENID']}\" id=\"blto{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEBLTO']) . "\"></td> ";}
		if ($columnDisplay['PESHTO']!="Y") {print "\n <td><input type=\"hidden\" name=\"shto{$row['IVISEQ']}_{$row['PEENID']}\" id=\"shto{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESHTO']) . "\"></td> ";}
		if ($columnDisplay['PEARAC']!="Y") {
			print "\n <td><input type=\"hidden\" name=\"arac{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arac{$row['IVISEQ']}_{$row['PEENID']}\" value=\"\"> ";
			print "\n     <input type=\"hidden\" name=\"arsb{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arsb{$row['IVISEQ']}_{$row['PEENID']}\" value=\"\"></td> ";
		}
		if ($columnDisplay['PEORD']!="Y")  {print "\n <td><input type=\"hidden\" name=\"ord{$row['IVISEQ']}_{$row['PEENID']}\"  id=\"ord{$row['IVISEQ']}_{$row['PEENID']}\"  value=\"" . rtrim($row['PEORD']) . "\" ></td> ";}
		if ($columnDisplay['PEORDT']!="Y") {print "\n <td><input type=\"hidden\" name=\"ordt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ordt{$row['IVISEQ']}_{$row['PEENID']}\"  value=\"" . rtrim($row['PEORDT']) . "\" ></td> ";}
		if ($columnDisplay['PEORLN']!="Y") {print "\n <td><input type=\"hidden\" name=\"orln{$row['IVISEQ']}_{$row['PEENID']}\" id=\"orln{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORLN']) . "\"></td> ";}
		if ($columnDisplay['PEPLT']!="Y")  {print "\n <td><input type=\"hidden\" name=\"plt{$row['IVISEQ']}_{$row['PEENID']}\"  id=\"plt{$row['IVISEQ']}_{$row['PEENID']}\"  value=\"" . rtrim($row['PEPLT']) . "\" ></td> ";}
		if ($columnDisplay['PEMORD']!="Y") {print "\n <td><input type=\"hidden\" name=\"mord{$row['IVISEQ']}_{$row['PEENID']}\" id=\"mord{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMORD']) . "\"></td> ";}
		if ($columnDisplay['PESLSM']!="Y") {print "\n <td><input type=\"hidden\" name=\"slsm{$row['IVISEQ']}_{$row['PEENID']}\" id=\"slsm{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESLSM']) . "\"></td> ";}
		if ($columnDisplay['PETRMS']!="Y") {print "\n <td><input type=\"hidden\" name=\"trms{$row['IVISEQ']}_{$row['PEENID']}\" id=\"trms{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PETRMS']) . "\"></td> ";}
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
