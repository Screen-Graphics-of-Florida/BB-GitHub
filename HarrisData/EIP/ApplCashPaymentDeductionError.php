<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$paymentType        = "D";
$paymentID          = "";
$entryType          = "E";

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
$scriptName    = "ApplCashPaymentDeductionError.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId)) . "&amp;columnDisplay" . $columnDisplay . "&amp;paymentType=" . urlencode(trim($paymentType)) . "&amp;harcedProgram=HARCED_P{$paymentType}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("PESSEQ","A","Selected Sequence"));
$tabID         = "ERRORS";
$programName   = "HARCED";
$advanceSearch    = "N";
$allowSaveFilter  = "N";

$InvBalSQL     = "IVIVAM-IVNPOS-IVPPOS-(-Coalesce(PEAMT-(-PEDAMT),0))";

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

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Select")         {$orby = array(array("PESSEQ" ,"A","Selected Sequence"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "Invoice")        {$orby = array(array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "PmtAmount")      {$orby = array(array("PEAMT" ,"A","Payment Amount"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "InvBal")         {$orby = array(array("IVBALN" ,"A","Invoice Balance"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "NetBal")         {$orby = array(array("IVNETB" ,"A","Net Balance"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryGenDed")    {$orby = array(array("PEGDED" ,"A","General Deduction"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryDedInv")    {$orby = array(array("PENINV" ,"A","Deduction Invoice"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntrySubCode")   {$orby = array(array("PESBCD" ,"A","Payment Code"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryMemo")      {$orby = array(array("PEMEMO" ,"A","Entry Memo"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryReference") {$orby = array(array("PEARPO" ,"A","Entry Reference"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryLoc")       {$orby = array(array("PELOC" ,"A","Entry Location"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryBillTo")    {$orby = array(array("PEBLTO" ,"A","Entry Bill-To"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryAccount")   {$orby = array(array("PEARAC,PEARSB" ,"A","Account"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryOEOrder")   {$orby = array(array("PEORD" ,"A","Entry Order Number"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryOrderDate") {$orby = array(array("PEORDT" ,"A","Entry Order Date"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryOrderLine") {$orby = array(array("PEORLN" ,"A","Entry Line Number"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryPlant")     {$orby = array(array("PEPLT" ,"A","Entry Plant"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryMfgOrder")  {$orby = array(array("PEMORD" ,"A","Entry Mfg Order"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntrySalesman")  {$orby = array(array("PESLSM" ,"A","Entry Salesman"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryTerms")     {$orby = array(array("PETRMS" ,"A","Entry Terms"),array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	require_once 'ApplCashPaymentInvoiceIncludeOrderBy.php';
	require_once 'OrderByUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Chg";

print "\n <link rel=stylesheet type=\"text/css\" href=\"{$ARApplCashStyleSheet}\"> ";
print "\n \n <script TYPE=\"text/javascript\">";
print "\n var optionWin;";
require_once 'AJAXRequest.js';
require_once 'Menu.js';

require_once 'CheckEnterAjax.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'ShowHideSelCriteria.php';
require_once 'StringTrimJavaScript.php';

require_once 'ApplCashPaymentDeductionJava.php';
require_once 'ApplCashPaymentJava.php';

print "\n function confirmDelete(deleteMsg) {return confirm(\"{$delRecordConf} \" +  \"\\n\" + \"\\n\" + deleteMsg);} ";
print "\n </script> \n";

if ($HDMCRL>0 && $CRPRMC=="Y") {
	$BKCURT=RetValue("BKBANK=$fromBatchBank", "HDBANK", "BKCURT");
	$CFCURT=RetValue("BKBANK=$fromBatchBank", "HDBANK inner join HDCFAC on (CFCO#,CFFAC#)=(BKCO,BKFAC)", "CFCURT");
}

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "APPLCASHPAYMENT";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

// Program Option Security
$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
require_once 'ApplCashDocRetInfoInclude.php';
require_once 'ApplCashPaymentTabInclude.php';
$stmtSQL= "";

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

// Payment Selected section
require 'stmtSQLClear.php';
$withSQL .= " With INVOICE ";
$withSQL .= " (IVBLTO,IVCUST,IVISEQ,IVIVCD,IVAINV,IVIVAM,IVNPOS,IVPPOS,IVDSCT,IVDSTK,IVFRT ,IVSTAX,IVSPC ,IVTRMS ";
$withSQL .= " ,IVDUED,IVDSCD,IVIVDT,IVARPO,IVORD ,IVORDT,IVORLN,IVPLT,IVMORD,IVLOC ,IVSLSM,IVPSDT,IVSBCD,IVCURT,IVCURD,IECRTB) ";
$withSQL .= " as ( ";
$withSQL .= " Select IVBLTO,IVCUST,IVISEQ,IVIVCD,IVAINV,IVIVAM,IVNPOS,IVPPOS,IVDSCT,IVDSTK,IVFRT ,IVSTAX,IVSPC ,IVTRMS ";
$withSQL .= "       ,IVDUED,IVDSCD,IVIVDT,IVARPO,IVORD ,IVORDT,IVORLN,IVPLT,IVMORD,IVLOC ,IVSLSM,IVPSDT,IVSBCD,IVCURT,IVCURD,'I' as IECRTB ";
$withSQL .= " From HDINVC";
$withSQL .= " union ";
$withSQL .= " Select 0 as IVBLTO,0 as IVCUST,IEISEQ as IVISEQ,IEPTYP as IVIVCD,0 as IVAINV,0 as IVIVAM,0 as IVNPOS,0 as IVPPOS,0 as IVDSCT,0 as IVDSTK, 0 as IVFRT ,0 as IVSTAX,0 as IVSPC,' ' as IVTRMS ";
$withSQL .= "       ,Current_Date as IVDUED,Current_Date as IVDSCD,0 as IVIVDT,' ' as IVARPO,0 as IVORD ,0 as IVORDT,0 as IVORLN,0 as IVPLT, ' ' as IVMORD,0 as IVLOC ,0 as IVSLSM,0 as IVPSDT,' ' as IVSBCD,IECURT as IVCURT,IECURD as IVCURD,'A' as IECRTB ";
$withSQL .= " From ARIVEN Where (IEBCHN,IEBCHD,IEBCHB,IETYPE,IEID,trim(IECHK),IEPTYP)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType') ";
$withSQL .= " ) ";

$stmtSQL .= " Select PEISEQ,PEENID,PESSEQ,PEAMT ,PEGDED,PENINV,PESBCD ";
$stmtSQL .= "       ,PEARPO,PEMEMO,PELOC ,PEBLTO,PEARAC,PEARSB,PESLSM,PEORD ,PEORDT,PEORLN,PEPLT ,PEMORD,PESLSM,PETRMS,PECMNT,PEPOTP,PEPOEN ";
$stmtSQL .= "       ,Case When Coalesce(PEGDED, ' ')='G' Then 'CHECKED' ELSE ' ' End as CHECKGENERAL ";
$stmtSQL .= "       ,Coalesce(PESINV,0)   as PESINV ";
$stmtSQL .= "       ,IECRTB,Coalesce(IVISEQ,0) as IVISEQ,IVIVCD,IVAINV,IVDSCD ";
$stmtSQL .= "       ,$InvBalSQL as IVBALN ";
$stmtSQL .= "       ,Coalesce(IVIVAM-IVNPOS-IVPPOS,0) as IVNETB ";
require_once 'ApplCashPaymentInvoiceIncludeSQLSelect.php';  // Includes HDINVC columns
$fileSQL .= " ARPYEN b ";
$fileSQL .= " left join INVOICE a on IVISEQ=PEISEQ ";
require_once 'ApplCashPaymentInvoiceIncludeSQLFrom.php';
$selectSQL .= "(PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID') ";
$selectSQL .= " and exists (Select * from ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',b.PEISEQ,Coalesce(b.PEENID,0))) ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt = "";
require 'QuickSearchOption.php';

print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"\" onSubmit=\"return ARQuickEntry(); \">";
print "\n <table $contentTable id=\"paymentTable\"> <tr>";
$returnValue=OrderBy_Sort("PESSEQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Select\"    title=\"Sequence By Selected\">{$sortPoint}Opt</a></th>";
$returnValue=OrderBy_Sort("PESINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\"   title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th>";
$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtAmount\" title=\"Sequence By Amount\">{$sortPoint}Amount</a></th>";
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
if ($columnDisplay['PEGDED']=="Y" && ($HDMCRL==0 || $CRPRMC!="Y" || $BKCURT==$CFCURT)) {
	$returnValue=OrderBy_Sort("PEGDED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryGenDed\"    title=\"Sequence By General Deduction\">{$sortPoint}General Deduction</a></th>";
}
if ($columnDisplay['PENINV']=="Y") {
	$returnValue=OrderBy_Sort("PENINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryDedInv\"    title=\"Sequence By Deduction Invoice\">{$sortPoint}Deduction Invoice</a></th>";
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

require_once 'ApplCashPaymentJavaUpdateHiddenInclude.php';

$rowCount = 0;
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
		$PENINV_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PENINV') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		$PESBCD_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PESBCD') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		$PEMEMO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEMEMO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		$PEARPO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEARPO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		$PELOC_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PELOC') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		$PEBLTO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEBLTO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
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
		$PENINV_ERROR="";
		$PESBCD_ERROR="";
		$PEMEMO_ERROR="";
		$PEARPO_ERROR="";
		$PELOC_ERROR="";
		$PEBLTO_ERROR="";
		$PEARAC_ERROR="";
		$PEORD_ERROR="";
		$PEORDT_ERROR="";
		$PEORLN_ERROR="";
		$PEPLT_ERROR="";
		$PEMORD_ERROR="";
		$PESLSM_ERROR="";
		$PETRMS_ERROR="";
	}

	print "\n <tr class=\"$rowClass\" id=\"row{$row['PEISEQ']}_{$row['PEENID']}\"> ";

	// Delete icon
	print "\n     <td class=\"inputcode\"> ";
	print "\n         <span id=\"spmt{$row['PEISEQ']}_{$row['PEENID']}\"> ";
	if ($applCashPaymentDeletePrompt=="Y") {
		$deleteMsg="Deduction for invoice {$row[IVAINV]}";
		print "\n <a onClick=\"if(confirmDelete('$deleteMsg')) {delARPYENLine('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','$row[IECRTB]');} \">$deleteImageSml</a> ";
	} else {
		print "\n <a onClick=\"delARPYENLine('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','$row[IECRTB]'); \">$deleteImageSml</a> ";
	}
	print "\n         </span> ";
	print "\n     </td>";

	// Entry - Invoice
	if ($PESINV_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESINV_ERROR\"  ";}
	else                   {$FldStyle="";}
	print "\n <td class=\"colnmbr\"> ";
	if (trim($row['PEISEQ'])==0 || trim($row['PEGDED'])=="G") {print "\n <input type=\"text\"   name=\"sinv{$row['PEISEQ']}_{$row['PEENID']}\" id=\"sinv{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESINV']) . "\" size=\"7\" maxlength=\"7\" $FldStyle onBlur=\"editPESINV('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";}
	else                                                      {print "\n <input type=\"hidden\" name=\"sinv{$row['PEISEQ']}_{$row['PEENID']}\" id=\"sinv{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESINV']) . "\" $FldStyle>$row[PESINV] ";}
	print "\n </td> ";

	// Entry - Payment Amount
	if ($PEAMT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEAMT_ERROR\"  ";}
	else                  {$FldStyle="";}
	if ($row['PEAMT']!=0) {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEAMT'],2, '.', '') . "\" size=\"8\" maxlength=\"15\" $FldStyle onChange=\"editPEAMT('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
	else                  {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"\" size=\"8\" maxlength=\"15\" $FldStyle onChange=\"editPEAMT('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}

	// icons
	require 'ApplCashPaymentIconsInclude.php';

	// Invoice Balance
	if ($columnDisplay['IVBALN']=="Y") {
		if ($iso_fromBatchDate>$row['IVDUED'] && $row[IVBALN]!=0) {$balClass="arinvcpastdue";}
		else                                                      {$balClass="colnmbr";}
		if ($row['PEISEQ']>0 && $row['PEENID']==$row['MINPEENID']) {print "\n <td class=\"$balClass\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$scriptVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['PEISEQ'])) . "&amp;noMenu=Y\" title=\"View A/R Invoice\" onclick=\"$searchWinVar\">" . number_format($row[IVBALN],2) . "</a></td> ";}
		elseif ($row['PEENID']==$row['MINPEENID'])                 {print "\n <td class=\"$balClass\">" . number_format($row[IVBALN],2) . "</td> ";}
		else                                                       {print "\n <td class=\"$balClass\">&nbsp;</td> ";}
	}

	// Net Balance
	if ($columnDisplay['IVNETB']=="Y") {
		if ($row['PEENID']==$row['MINPEENID']) {print "\n <td class=\"colnmbr\" id=\"netb{$row['PEISEQ']}_{$row['PEENID']}\">" . number_format($row[IVNETB],2) . "</td> ";}
		else                                   {print "\n <td class=\"colnmbr\" id=\"netb{$row['PEISEQ']}_{$row['PEENID']}\">&nbsp;</td> ";}
	}

	// Other Pending Payments
	if ($columnDisplay['ADJAMT']=="Y") {
		if ($row['ADJAMT']<>0 && $row['PEENID']==$row['MINPEENID']) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashPaymentOtherInquiry.php{$maintainVar}\" title=\"View Other Pending Activity\" onclick=\"$searchWinVar\">" . number_format($row['ADJAMT'],2) . "</a></td> ";}
		else                                                        {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
	}

	// Entry - General Deduction
	if ($columnDisplay['PEGDED']=="Y" && ($HDMCRL==0 || $CRPRMC!="Y" || $BKCURT==$CFCURT)) {print "\n <td class=\"inputcode\"><input type=\"checkbox\" name=\"gded{$row['PEISEQ']}_{$row['PEENID']}\" id=\"gded{$row['PEISEQ']}_{$row['PEENID']}\" value='G' $row[CHECKGENERAL] onClick=\"editPEGDED('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\" title=\"General Deduction\"></td> ";}

	// Entry - Deduction Invoice
	if ($columnDisplay['PENINV']=="Y") {
		if ($PENINV_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PENINV_ERROR\"  ";}
		else                   {$FldStyle="";}
		if ($CRIAUI=="E" && $row['IECRTB']=="I" || $CRGAUI=="E" && $row['IECRTB']=="A" || $PENINV_ERROR!="") {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"ninv{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ninv{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PENINV']) . "\" size=\"7\" maxlength=\"7\" $FldStyle onChange=\"editPENINV('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
		else {print "\n     <td class=\"colnmbr\" $FldStyle ><input type=\"hidden\" name=\"ninv{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ninv{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PENINV']) . "\">$row[PENINV]</td> ";}
	}

	// Entry - Payment Code
	if ($columnDisplay['PESBCD']=="Y") {
		if ($PESBCD_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESBCD_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n <td class=\"inputalph\" nowrap><input type=\"text\"     name=\"sbcd{$row['PEISEQ']}_{$row['PEENID']}\" id=\"sbcd{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESBCD']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPESBCD('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
		print "\n                                <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=sbcd{$row['PEISEQ']}_{$row['PEENID']}&amp;fldDesc=none&amp;specificBatchType={$BMBCHT}&amp;specificPmtType=$paymentType\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	}

	// Entry - Memo
	if ($columnDisplay['PEMEMO']=="Y") {
		if ($PEMEMO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEMEMO_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"memo{$row['PEISEQ']}_{$row['PEENID']}\" id=\"memo{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMEMO']) . "\" size=\"9\" maxlength=\"15\" $FldStyle onChange=\"editPEMEMO('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
	}

	// Entry - Reference
	if ($columnDisplay['PEARPO']=="Y") {
		if ($PEARPO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEARPO_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n <td class=\"inputalph\"><input type=\"text\" name=\"arpo{$row['PEISEQ']}_{$row['PEENID']}\" id=\"arpo{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARPO']) . "\" size=\"9\" maxlength=\"22\" $FldStyle onChange=\"editPEARPO('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
	}

	// Entry - Location
	if ($columnDisplay['PELOC']=="Y") {
		if ($PELOC_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PELOC_ERROR\"  ";}
		else                  {$FldStyle="";}
		print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"loc{$row['PEISEQ']}_{$row['PEENID']}\" id=\"loc{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PELOC']) . "\" size=\"3\" maxlength=\"3\" $FldStyle onBlur=\"editPELOC('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
		print "\n                                <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=loc{$row['PEISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	}

	// Entry - Bill-To
	if ($columnDisplay['PEBLTO']=="Y" && $fromType=="P") {
		if ($PEBLTO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEBLTO_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"blto{$row['PEISEQ']}_{$row['PEENID']}\" id=\"blto{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEBLTO']) . "\" size=\"7\" maxlength=\"7\" $FldStyle onBlur=\"editPEBLTO('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
		print "\n                                <a href=\"{$homeURL}{$phpPath}PayerCustomerSearch.php{$scriptVarBase}&amp;specificPayer={$fromID}&amp;docName=Chg&amp;fldName=blto{$row['PEISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td>  ";
	}

	// Entry - A/R Account
	if ($columnDisplay['PEARAC']=="Y") {
		if ($PEARAC_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEARAC_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n     <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"arac{$row['PEISEQ']}_{$row['PEENID']}\" id=\"arac{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARAC']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPEARAC('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
		print "\n                                    <input type=\"text\" name=\"arsb{$row['PEISEQ']}_{$row['PEENID']}\" id=\"arsb{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARSB']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPEARSB('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
		print "\n                                    <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=arac{$row['PEISEQ']}_{$row['PEENID']}&amp;subFld=arsb{$row['PEISEQ']}_{$row['PEENID']}&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	}

	// Entry - Order Number
	if ($columnDisplay['PEORD']=="Y") {
		if ($PEORD_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEORD_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"ord{$row['PEISEQ']}_{$row['PEENID']}\" id=\"ord{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORD']) . "\" size=\"8\" maxlength=\"8\" $FldStyle onChange=\"editPEORD('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
	}

	// Entry - Order Date
	if ($columnDisplay['PEORDT']=="Y") {
		if ($PEORDT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEORDT_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"ordt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"ordt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORDT']) . "\" size=\"6\" maxlength=\"6\" $FldStyle onChange=\"editPEORDT('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
	}

	// Entry - Order Line
	if ($columnDisplay['PEORLN']=="Y") {
		if ($PEORLN_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEORLN_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"orln{$row['PEISEQ']}_{$row['PEENID']}\" id=\"orln{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORLN']) . "\" size=\"3\" maxlength=\"3\" $FldStyle onChange=\"editPEORLN('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
	}

	// Entry - Plant
	if ($columnDisplay['PEPLT']=="Y") {
		if ($PEPLT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEPLT_ERROR\"  ";}
		else                  {$FldStyle="";}
		print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"plt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"plt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEPLT']) . "\" size=\"3\" maxlength=\"3\" $FldStyle onBlur=\"editPEPLT('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
		if ($HDPDRL>0) {print "\n                                <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=plt{$row['PEISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a> ";}
		print "\n </td>  ";
	}

	// Entry - Mfg Order
	if ($columnDisplay['PEMORD']=="Y") {
		if ($PEMORD_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEMORD_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n <td class=\"inputalph\"><input type=\"text\" name=\"mord{$row['PEISEQ']}_{$row['PEENID']}\" id=\"mord{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMORD']) . "\" size=\"9\" maxlength=\"9\" $FldStyle onChange=\"editPEMORD('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
	}

	// Entry - Salesman
	if ($columnDisplay['PESLSM']=="Y") {
		if ($PESLSM_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESLSM_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"slsm{$row['PEISEQ']}_{$row['PEENID']}\" id=\"slsm{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESLSM']) . "\" size=\"3\" maxlength=\"3\" $FldStyle onBlur=\"editPESLSM('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
		print "\n                                          <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=slsm{$row['PEISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	}

	// Entry - Terms
	if ($columnDisplay['PETRMS']=="Y") {
		if ($PETRMS_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PETRMS_ERROR\"  ";}
		else                   {$FldStyle="";}
		print "\n <td class=\"inputalph\" nowrap><input type=\"text\"     name=\"trms{$row['PEISEQ']}_{$row['PEENID']}\" id=\"trms{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PETRMS']) . "\" size=\"2\" maxlength=\"2\" $FldStyle onBlur=\"editPETRMS('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
		print "\n                                <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=trms{$row['PEISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	}

	// Information columns
	require 'ApplCashPaymentInvoiceIncludeDetail.php';

	// Add hidden fields needed for Javascript
	if ($columnDisplay['IVNETB']!="Y") {print "\n <td><input type=\"hidden\" id=\"netb{$row['PEISEQ']}_{$row['PEENID']}\"></td> ";}
	if ($columnDisplay['PESBCD']!="Y") {print "\n <td><input type=\"hidden\" name=\"sbcd{$row['PEISEQ']}_{$row['PEENID']}\" id=\"sbcd{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESBCD']) . "\"></td> ";}
	if ($columnDisplay['PENINV']!="Y") {print "\n <td><input type=\"hidden\" name=\"ninv{$row['PEISEQ']}_{$row['PEENID']}\" id=\"ninv{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PENINV']) . "\"></td> ";}
	if ($columnDisplay['PEMEMO']!="Y") {print "\n <td><input type=\"hidden\" name=\"memo{$row['PEISEQ']}_{$row['PEENID']}\" id=\"memo{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMEMO']) . "\"></td> ";}
	if ($columnDisplay['PEARPO']!="Y") {print "\n <td><input type=\"hidden\" name=\"arpo{$row['PEISEQ']}_{$row['PEENID']}\" id=\"arpo{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARPO']) . "\"></td> ";}
	if ($columnDisplay['PELOC']!="Y")  {print "\n <td><input type=\"hidden\" name=\"loc{$row['PEISEQ']}_{$row['PEENID']}\"  id=\"loc{$row['PEISEQ']}_{$row['PEENID']}\"  value=\"" . rtrim($row['PELOC']) . "\" ></td> ";}
	if ($columnDisplay['PEBLTO']!="Y" || $fromType=="C") {print "\n <td><input type=\"hidden\" name=\"blto{$row['PEISEQ']}_{$row['PEENID']}\" id=\"blto{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEBLTO']) . "\"></td> ";}
	if ($columnDisplay['PEARAC']!="Y") {
		print "\n <td><input type=\"hidden\" name=\"arac{$row['PEISEQ']}_{$row['PEENID']}\" id=\"arac{$row['PEISEQ']}_{$row['PEENID']}\" value=\"\"> ";
		print "\n     <input type=\"hidden\" name=\"arsb{$row['PEISEQ']}_{$row['PEENID']}\" id=\"arsb{$row['PEISEQ']}_{$row['PEENID']}\" value=\"\"></td> ";
	}
	if ($columnDisplay['PEORD']!="Y")  {print "\n <td><input type=\"hidden\" name=\"ord{$row['PEISEQ']}_{$row['PEENID']}\"  id=\"ord{$row['PEISEQ']}_{$row['PEENID']}\"  value=\"" . rtrim($row['PEORD']) . "\" ></td> ";}
	if ($columnDisplay['PEORDT']!="Y") {print "\n <td><input type=\"hidden\" name=\"ordt{$row['PEISEQ']}_{$row['PEENID']}\"  id=\"ordt{$row['PEISEQ']}_{$row['PEENID']}\"  value=\"" . rtrim($row['PEORDT']) . "\" ></td> ";}
	if ($columnDisplay['PEORLN']!="Y") {print "\n <td><input type=\"hidden\" name=\"orln{$row['PEISEQ']}_{$row['PEENID']}\" id=\"orln{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEORLN']) . "\"></td> ";}
	if ($columnDisplay['PEPLT']!="Y")  {print "\n <td><input type=\"hidden\" name=\"plt{$row['PEISEQ']}_{$row['PEENID']}\"  id=\"plt{$row['PEISEQ']}_{$row['PEENID']}\"  value=\"" . rtrim($row['PEPLT']) . "\" ></td> ";}
	if ($columnDisplay['PEMORD']!="Y") {print "\n <td><input type=\"hidden\" name=\"mord{$row['PEISEQ']}_{$row['PEENID']}\" id=\"mord{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMORD']) . "\"></td> ";}
	if ($columnDisplay['PESLSM']!="Y") {print "\n <td><input type=\"hidden\" name=\"slsm{$row['PEISEQ']}_{$row['PEENID']}\" id=\"slsm{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESLSM']) . "\"></td> ";}
	if ($columnDisplay['PETRMS']!="Y") {print "\n <td><input type=\"hidden\" name=\"trms{$row['PEISEQ']}_{$row['PEENID']}\" id=\"trms{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PETRMS']) . "\"></td> ";}
	print "\n </tr>";

	if (trim($row[PEISEQ])==0) {
		print "\n <tr class=\"$rowClass\"> ";
		print "\n     <td class=\"colalph\"></td> ";
		print "\n     <td class=\"colalph\" colspan=\"20\"><span id=\"added{$row['PEISEQ']}_{$row['PEENID']}\"></span></td> ";
		print "\n </tr>";
	};

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table> ";
print "\n </form>";

require_once 'PageBottom.php';
require 'EndTabInclude.php';
print "\n </table>";
print "$hrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
require_once  'ApplCashPaymentJavaHiddenUpdateArrayScript.php';  // Add Hidden fields used for Ajax in Java script
print "\n </body> \n </html>";

?>
