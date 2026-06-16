<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$paymentType        = "Y";
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
$scriptName    = "ApplCashPaymentApply.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId)) . "&amp;columnDisplay" . $columnDisplay . "&amp;paymentType=" . urlencode(trim($paymentType)) . "&amp;harcedProgram=HARCED_P{$paymentType}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("IVAINV","A","Invoice"));
$tabID         = "PAYMENT";
$programName   = "HARCED";

$fromBatchDate_ISO = date ( 'Y-m-d', strtotime ( Date_CYMD_ISO ( $fromBatchDate ) . " - " . $CRDSPD . " days " ) );
$InvBalSQL     = "IVIVAM-IVNPOS-IVPPOS-(-Coalesce(PEAMT-(-PEDAMT),0))";
$NetBalSQL     = "IVIVAM-IVNPOS-IVPPOS ";
$DscBalSQL     = "IVDSCT-IVDSTK-Coalesce(OTH_PEDAMT,0) ";
$DscAmtSQL     = "date('$fromBatchDate_ISO') >IVDSCD Then 0 When Sign(IVIVAM)<>Sign(IVIVAM-IVNPOS-IVPPOS-(-Coalesce(PEAMT-(-PEDAMT),0))) Then 0 When Sign(IVIVAM)<>Sign($DscBalSQL) Then 0 ";

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("Invoice:", "Selected", $viewCheckBoxURL, "1", "1"),
array("", "Debit", $viewCheckBoxURL, "2", "1"),
array("", "Credit", $viewCheckBoxURL, "3", "1"),
array("", "Fully Paid", $viewCheckBoxURL, "4", "0"),
array("<br>Default:", "Calculated Payment", $viewCheckBoxURL, "5", "1"),
array("", "Discount", $viewCheckBoxURL, "6", "0"));

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
	if     ($sequence == "Select")        {$orby = array(array("PESPMT" ,"A","Selection"),array("PESSEQ" ,"A","Selected Sequence"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "Invoice")       {$orby = array(array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "PmtAmount")     {$orby = array(array("PEAMT" ,"A","Payment Amount"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "Discount")      {$orby = array(array("PEDAMT" ,"A","Discount"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "InvBal")        {$orby = array(array("IVBALN" ,"A","Invoice Balance"),array("IVAINV" ,"A","Invoice"));}
	elseif ($sequence == "NetBal")        {$orby = array(array("IVNETB" ,"A","Net Balance"),array("IVAINV" ,"A","Invoice"));}
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
if ($viewCheckBox[4]) {$sclcDefault = true;}
else                  {$sclcDefault = false;}
if ($viewCheckBox[5]) {$sdscDefault = true;}
else                  {$sdscDefault = false;}

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
$stmtSQL .= " Select IVISEQ,IVIVCD,IVAINV,IVDSCD ";
$stmtSQL .= "       ,$InvBalSQL as IVBALN ";
$stmtSQL .= "       ,$NetBalSQL as IVNETB ";
$stmtSQL .= "       ,Case When $DscAmtSQL ";
$stmtSQL .= "             When ABS($InvBalSQL) < ABS($DscBalSQL) Then $InvBalSQL "; 
$stmtSQL .= "             Else $DscBalSQL End as IVDSCT ";
$stmtSQL .= "       ,Coalesce(PEISEQ, IVISEQ) as PEISEQ ";
$stmtSQL .= "       ,Coalesce(PEENID,0)      as PEENID ";
$stmtSQL .= "       ,Coalesce(PESSEQ,0)      as PESSEQ ";
$stmtSQL .= "       ,Coalesce(PESPMT,' ')    as PESPMT ";
$stmtSQL .= "       ,Case When Coalesce(PESPMT, ' ')='Y' Then 'CHECKED' ELSE ' ' End as CHECKSELECTION ";
$stmtSQL .= "       ,Coalesce(PEAMT ,0)      as PEAMT ";
$stmtSQL .= "       ,Case When Coalesce(PESCLC, ' ')='Y' Then 'CHECKED' ELSE ' ' End as CHECKCALCULATE ";
$stmtSQL .= "       ,Case When PEDAMT is not Null Then PEDAMT ";
$stmtSQL .= "             When $DscAmtSQL ";
$stmtSQL .= "             When ABS($InvBalSQL) < ABS($DscBalSQL) Then $InvBalSQL "; 
$stmtSQL .= "             Else $DscBalSQL End as PEDAMT ";
$stmtSQL .= "       ,Case When Coalesce(PESDSC, ' ')='Y' Then 'CHECKED' ELSE ' ' End as CHECKDISCOUNT ";
$stmtSQL .= "       ,Coalesce(PECMNT, ' ')    as PECMNT ";
$stmtSQL .= "       ,Coalesce(PEPOTP,' ') as PEPOTP ";
$stmtSQL .= "       ,Coalesce(PEPOEN,0)   as PEPOEN ";
require_once 'ApplCashPaymentInvoiceIncludeSQLSelect.php';  // Includes HDINVC columns
$fileSQL .= " HDINVC a ";
$fileSQL .= " left join ARPYEN b on (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID,PEISEQ)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',IVISEQ) ";
require_once 'ApplCashPaymentInvoiceIncludeSQLFrom.php';
if ($fromType=="C") {$selectSQL .= "IVBLTO=$fromID ";}
else                {$selectSQL .= "IVBLTO in (Select CICUST from HDCUSI Where (CIIBCH,CIIDTE,CIIBNK,CIIUSR,CITYPE,CIID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$userProfile','$fromType',$fromID))";}
if ($HDMCRL>0 && $CRPRMC=="Y") {
	$BKCURT=RetValue("BKBANK=$fromBatchBank", "HDBANK", "BKCURT");
	$CFCURT=RetValue("BKBANK=$fromBatchBank", "HDBANK inner join HDCFAC on (CFCO#,CFFAC#)=(BKCO,BKFAC)", "CFCURT");
	$selectSQL .= " and (IVCURT,IVCURD)=('$BKCURT','$CFCURT') ";
}
$viewCheckSQL="";
if ($viewCheckBox[0] || $viewCheckBox[1] || $viewCheckBox[2] || $viewCheckBox[3]) {
	if     ($viewCheckBox[0])                        {$viewCheckSQL.= " (Coalesce(PESPMT,' ')='Y'";}
	if     ($viewCheckBox[1] && $viewCheckSQL == "") {$viewCheckSQL.= " (Coalesce(PESPMT,' ')=' ' and (IVIVAM-IVNPOS-IVPPOS)>0";}     // minus a minus= add
	elseif ($viewCheckBox[1])                        {$viewCheckSQL.= "  or Coalesce(PESPMT,' ')=' ' and (IVIVAM-IVNPOS-IVPPOS)>0";}
	if     ($viewCheckBox[2] && $viewCheckSQL == "") {$viewCheckSQL.= " (Coalesce(PESPMT,' ')=' ' and (IVIVAM-IVNPOS-IVPPOS)<0";}
	elseif ($viewCheckBox[2])                        {$viewCheckSQL.= "  or Coalesce(PESPMT,' ')=' ' and (IVIVAM-IVNPOS-IVPPOS)<0";}
	if     ($viewCheckBox[3] && $viewCheckSQL == "") {$viewCheckSQL.= " (Coalesce(PESPMT,' ')=' ' and (IVIVAM-IVNPOS-IVPPOS)=0";}
	elseif ($viewCheckBox[3])                        {$viewCheckSQL.= "  or Coalesce(PESPMT,' ')=' ' and (IVIVAM-IVNPOS-IVPPOS)=0";}
	$viewCheckSQL.=")";
} else {
	$viewCheckSQL.= " IVIVAM<>IVIVAM";
}
if ($selectSQL == "") {$selectSQL  = $viewCheckSQL;
} else                {$selectSQL .= " and $viewCheckSQL ";}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';

$sv_withSQL = NULL;
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

require_once 'ApplCashPaymentApplyJava.php';
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

$stmtSQL=$sv_stmtSQL;
$fileSQL=$sv_fileSQL;
$selectSQL=$sv_selectSQL;
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint != "Y"){
	$qsOpt = "";
	require 'ApplCashPaymentInvoiceIncludeQuickFilter.php';
	require 'QuickSearchOption.php';

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=UPDATE\">";
	print "\n <table $contentTable id=\"paymentTable\"> <tr>";
	$returnValue=OrderBy_Sort("PESSEQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Select\"    title=\"Sequence By Selected\">{$sortPoint}Sel</a></th>";
	$returnValue=OrderBy_Sort("IVAINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\"   title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th>";
	$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtAmount\" title=\"Sequence By Payment Amount\">{$sortPoint}Payment Amount</a></th>";
	if ($columnDisplay['PEDAMT']=="Y") {
		$returnValue=OrderBy_Sort("PEDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Discount\"  title=\"Sequence By Discount\">{$sortPoint}Discount</a></th>";
	}
	// icons
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

	require_once 'ApplCashPaymentInvoiceIncludeHeading.php';
	print "\n </tr>";

	// Quick Entry Row
	require  'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"entry\"><a onClick=\"ARQuickEntry();\">$applCashAcceptImage</a></td>";
	print "\n     <td class=\"entry\"><input type=\"text\" name=\"addInvoiceNumber\" id=\"addInvoiceNumber\" size=\"7\" maxlength=\"7\"></td> ";
	print "\n     <td class=\"entry\"><input type=\"text\" name=\"addAmount\" id=\"addAmount\" size=\"8\" maxlength=\"15\"></td> ";
	if ($columnDisplay['PEDAMT']=="Y") {
		print "\n     <td class=\"entry\"><input type=\"text\" name=\"addDiscount\" id=\"addDiscount\" size=\"8\" maxlength=\"15\"></td> ";
	}

	// Add hidden fields needed for Javascript and the form
	if ($columnDisplay['PEDAMT']!="Y") {print "\n     <td><input type=\"hidden\" name=\"addDiscount\" id=\"addDiscount\"></td> ";}

	print "\n </tr> ";

	require_once 'ApplCashPaymentJavaUpdateHiddenInclude.php';

	// Invoice rows
	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		$maintainVar = "{$scriptVarBase}&amp;fromPaymentType=" . urlencode(trim($paymentType)) . "&amp;fromPaymentID=" . urlencode(trim($paymentID)) . "&amp;fromInvoiceSeq=" . urlencode(trim($row['PEISEQ'])) . "&amp;fromEntryID=" . urlencode(trim($row['PEENID']));
		$row['ADJAMT'] = RetValue ("(PEISEQ,PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK))=(" . $row['PEISEQ'] . ",$fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') and (PEPTYP<>'$paymentType' or PEPMID<>'$paymentID') and (PEPTYP<>'" . $row['PEPOTP'] . "' or PEENID<>" . $row['PEPOEN'] . ") ", "ARPYEN", "Sum(PEAMT-(-PEDAMT))" );
		
		require  'ApplCashPaymentJavaHiddenUpdateArrayElement.php';  // Add hidden Java Values to Variable
		require  'SetRowClass.php';

		$F_IVDUED=Format_Date_ISO($row['IVDUED'], "D");
		$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
		$F_IVPSDT=Format_Date($row['IVPSDT'], "D");
		if ($row['ARPYENERROR']>0) {
			$PEAMT_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEAMT') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEDAMT_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEDAMT') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		} else {
			$PEAMT_ERROR="";
			$PEDAMT_ERROR="";
		}

		print "\n <tr class=\"$rowClass\" id=\"row{$row['IVISEQ']}_{$row['PEENID']}\"> ";

		// Select Checkbox
		print "\n     <td class=\"inputcode\"><input type=\"checkbox\" name=\"spmt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"spmt{$row['PEISEQ']}_{$row['PEENID']}\" value='S' $row[CHECKSELECTION] onClick=\"editPESPMT('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','$row[IVAINV]','$sclcDefault','$sdscDefault') \" title=\"Select Payment\"></td> ";

		// Invoice column
		if     ($row['IVIVCD']=="C") {$ivcdClass="archargeinvoice";}
		elseif ($row['IVIVCD']=="D") {$ivcdClass="ardeductioninvoice";}
		elseif ($row['IVIVCD']=="N") {$ivcdClass="arnsfinvoice";}
		elseif ($row['IVIVCD']=="S") {$ivcdClass="arserviceinvoice";}
		elseif ($row['IVIVCD']=="U") {$ivcdClass="arunappliedinvoice";}
		else                         {$ivcdClass="inputnmbr";}
		if ($row['OEINVCOUNT']>0) {print "\n <td class=\"$ivcdClass\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['IVAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['IVIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[IVAINV]</a></td> ";}
		else                      {print "\n <td class=\"$ivcdClass\">$row[IVAINV]</td> ";}

		// Entry - Payment Amount
		if ($PEAMT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEAMT_ERROR\"  ";}
		else                  {$FldStyle="";}
		if ($row['PEAMT']!=0) {print "\n     <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEAMT'],2, '.', '') . "\" size=\"8\" maxlength=\"15\" $FldStyle onChange=\"editPEAMTApply('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','$sdscDefault')\"> ";}
		else                  {print "\n     <td class=\"inputnmbr\" nowrap><input type=\"text\"     name=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"\" size=\"8\" maxlength=\"15\" $FldStyle onChange=\"editPEAMTApply('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','$sdscDefault')\"> ";}
		print "\n                                    <input type=\"checkbox\" name=\"sclc{$row['PEISEQ']}_{$row['PEENID']}\" id=\"sclc{$row['PEISEQ']}_{$row['PEENID']}\" value='S' $row[CHECKCALCULATE] onClick=\"editPESCLCApply('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','$sdscDefault')\" title=\"Calculate Payment\"></td> ";

		// Entry - Discount
		if ($columnDisplay['PEDAMT']=="Y") {
			if ($PEDAMT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEDAMT_ERROR\"  ";}
			else                   {$FldStyle="";}
			if ($iso_fromBatchDate>$row['IVDSCD'] && $row[IVBALN]!=0) {$dscClass="ardiscpastdue";}
			else                                                      {$dscClass="inputnmbr";}
			if ($row['PEDAMT']!=0) {print "\n <td class=\"$dscClass\" nowrap><input type=\"text\"     name=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEDAMT'],2, '.', '') . "\" size=\"8\" maxlength=\"15\" $FldStyle onChange=\"editPEDAMTApply('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','$sclcDefault') \">  ";}
			else                   {print "\n <td class=\"$dscClass\" nowrap><input type=\"text\"     name=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"\" size=\"8\" maxlength=\"15\" $FldStyle onChange=\"editPEDAMTApply('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','$sclcDefault') \">  ";}
			print "\n                                <input type=\"checkbox\" name=\"sdsc{$row['PEISEQ']}_{$row['PEENID']}\" id=\"sdsc{$row['PEISEQ']}_{$row['PEENID']}\" value=\"S\" $row[CHECKDISCOUNT] onClick=\"editPESDSCApply('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','$sclcDefault')\" title=\"Select Discount\"></td>  ";
		}

		// icons
		require 'ApplCashPaymentIconsInclude.php';

		// Invoice Balance
		if ($columnDisplay['IVBALN']=="Y") {
			if ($iso_fromBatchDate>$row['IVDUED'] && $row[IVBALN]!=0) {$balClass="arinvcpastdue";}
			else                                                      {$balClass="colnmbr";}
			print "\n <td class=\"$balClass\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$scriptVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['IVISEQ'])) . "&amp;noMenu=Y\" title=\"View A/R Invoice\" onclick=\"$searchWinVar\">" . number_format($row['IVBALN'],2) . "</a></td> ";
		}

		// Net Balance
		if ($columnDisplay['IVNETB']=="Y") {print "\n <td class=\"colnmbr\" id=\"netb{$row['PEISEQ']}_{$row['PEENID']}\">" . number_format($row['IVNETB'],2) . "</td> ";}

		// Other Pending
		if ($columnDisplay['ADJAMT']=="Y") {
			if ($row['ADJAMT']<>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashPaymentOtherInquiry.php{$maintainVar}\" title=\"View Other Pending Activity\" onclick=\"$searchWinVar\">" . number_format($row['ADJAMT'],2) . "</a></td> ";}
			else                   {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
		}

		// Information columns
		require 'ApplCashPaymentInvoiceIncludeDetail.php';

		// Add hidden fields needed for Javascript
		if ($columnDisplay['PEDAMT']!="Y") {
			print "\n <td><input type=\"hidden\"   name=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEDAMT']) . "\"> ";
			print "\n     <input style=\"display:none\" type=\"checkbox\"  name=\"sdsc{$row['PEISEQ']}_{$row['PEENID']}\" id=\"sdsc{$row['PEISEQ']}_{$row['PEENID']}\"></td> ";
		}
		if ($columnDisplay['IVNETB']!="Y") {print "\n <td><input type=\"hidden\"  id=\"netb{$row['PEISEQ']}_{$row['PEENID']}\"</td> ";}
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
