<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$paymentType        = "M";
$paymentID          = "";
$entryType          = "";

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$fromDocument       = "";

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

$fromType       = "C";
$fromID         = $CRMSCC;  // Need it here since it comes from ARControl include
$page_title    = "Application of Cash";
$scriptName    = "ApplCashPaymentMisc.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId)) . "&amp;columnDisplay" . $columnDisplay . "&amp;paymentType=" . urlencode(trim($paymentType)) . "&amp;harcedProgram=HARCED_P{$paymentType}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("IVAINV","A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));
$tabID         = "MISCELLANEOUS";
$programName   = "HARCED";

if (CustomerUserView($profileHandle, $dataBaseID, $fromID, "N")=="N") {
	require 'userViewErrorInclude.php';
	exit;
}

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("View:", "Current Batch", $viewCheckBoxURL, "1", "1"),
array("", "Payment History", $viewCheckBoxURL, "2", "0"));

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
	print "\n    && editNum(document.$formName.frPaymentAmt, 11, 2) ";
	print "\n    && editNum(document.$formName.toPaymentAmt, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frPaymentAmt, document.$formName.toPaymentAmt, document.$formName.operPaymentAmt, 15) ";
	print "\n    && editdate(document.$formName.frInvoiceDate) ";
	print "\n    && editdate(document.$formName.toInvoiceDate) ";
	print "\n    && editFromToOper(document.$formName.frInvoiceDate, document.$formName.toInvoiceDate, document.$formName.operInvoiceDate, 'D') ";
	print "\n    && editNum(document.$formName.frLocation, 3, 0) ";
	print "\n    && editNum(document.$formName.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.$formName.frLocation, document.$formName.toLocation, document.$formName.operLocation, 3) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHBATCHSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Miscellaneous Payment Number","frInvoice","toInvoice","operInvoice","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Payment Amount","frPaymentAmt","toPaymentAmt","operPaymentAmt","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Payment Date","frInvoiceDate","toInvoiceDate","operInvoiceDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Memo","srchMemo","","operMemo","opersel_alph_short","A","10","15");
	Build_AdvSrch_Entry("Reference Number","srchPONumber","","operPONumber","opersel_alph_short","A","10","22");

	$operNbr = "operLocation";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Location Name","srchLocationName","","operLocationName","opersel_alph_short","A","20","30");
	Build_AdvSrch_Entry("Created By Payment Code","srchSubCode","","operSubCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Created By Payment Code Description","srchSubCodeDesc","","operSubCodeDesc","opersel_alph_short","A","20","50");

	$focusField = "frInvoice";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Select")         {$orby = array(array("PESPMT" ,"A","Selection"),array("PESSEQ" ,"A","Selected Sequence"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "Invoice")        {$orby = array(array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "Document")       {$orby = array(array("PEMCHK" ,"A","Document"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "SubCode")        {$orby = array(array("PESBCD" ,"A","Payment Code"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "PmtAmount")      {$orby = array(array("PEAMT" ,"A","Payment Amount"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryMemo")      {$orby = array(array("PEMEMO" ,"A","Memo"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryReference") {$orby = array(array("PEARPO" ,"A","Reference Number"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "EntryLoc")       {$orby = array(array("PELOC" ,"A","Location"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "Company")        {$orby = array(array("PEOFCO,PEOFFC" ,"A","Company/Facility"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "Account")        {$orby = array(array("PEOFAC,PEOFSB" ,"A","Account"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "InvoiceDate")    {$orby = array(array("IVIVDT" ,"A","Payment Date"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "LocationName")   {$orby = array(array("LOLNA1U" ,"A","Location Name"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "SubCodeDesc")    {$orby = array(array("PSDESCU" ,"A","Created By Payment Code Description"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "TypePmt")        {$orby = array(array("PEPMID" ,"A","Transaction Type"),array("IVAINV" ,"A","Miscellaneous Payment Number"),array("IVISEQ","A",""),array("PEENID","A",""));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Range_WildCard("Case When IECRTB='I' Then IVAINV else PESINV End ", "Miscellaneous Payment Number", $_POST['frInvoice'], $_POST['toInvoice'], "", $_POST['operInvoice'], "N");
	$returnValue=Range_WildCard("Case When IECRTB='I' Then IVIVAM else PEAMT End", "Payment Amount", $_POST['frPaymentAmt'], $_POST['toPaymentAmt'], "", $_POST['operPaymentAmt'], "N");
	$returnValue=Range_WildCard("Case When IECRTB='I' Then IVIVDT else PEBCHD End", "Payment Date", $_POST['frInvoiceDate'], $_POST['toInvoiceDate'], "", $_POST['operInvoiceDate'], "D");
	$returnValue=Build_WildCard("Case When IECRTB='I' Then ' ' else PEMEMO End ", "Memo", $_POST['srchMemo'], "U", $_POST['operMemo'], "A");
	$returnValue=Build_WildCard("Case When IECRTB='I' Then IVARPO else PEARPO End ", "Reference Number", $_POST['srchPONumber'], "U", $_POST['operPONumber'], "A");
	$returnValue=Range_WildCard("Case When IECRTB='I' Then IVLOC else PELOC End", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Build_WildCard("Coalesce(Upper(LOLNA1), ' ')", "Location Name", $_POST['srchLocationName'], "U", $_POST['operLocationName'], "A");
	$returnValue=Build_WildCard("Case When IECRTB='I' Then IVSBCD else PESBCD End", "Created By Payment Code", $_POST['srchSubCode'], "U", $_POST['operSubCode'], "A");
	$returnValue=Build_WildCard("Coalesce(PSDESCU, ' ')", "Created By Payment Code Description", $_POST['srchSubCodeDesc'], "U", $_POST['operSubCodeDesc'], "A");
	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Chg";

print "\n <link rel=stylesheet type=\"text/css\" href=\"{$ARApplCashStyleSheet}\"> ";
print "\n \n <script TYPE=\"text/javascript\">";
print "\n var optionWin;";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';
require_once 'Menu.js';

require_once 'CalendarInclude.php';
require_once 'CheckEnterAjax.php';
require_once 'CheckEnterSearch.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
require_once 'StringTrimJavaScript.php';

require_once 'ApplCashPaymentMiscJava.php';
require_once 'ApplCashPaymentJava.php';

print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
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

// Program Option Security
$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$BMBCHT=RetValue("(BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank)", "ARPBCH", "BMBCHT");
require_once 'ApplCashCustomerTabInclude.php';

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
$withSQL .= "       (IVBLTO,IVISEQ,IVAINV,IVIVAM,IVIVDT,IVARPO,IVLOC ,IVSLSM,IVSBCD,IVCURT,IVCURD,IECRTB) ";
$withSQL .= " as ( ";
$withSQL .= " Select IVBLTO,IVISEQ,IVAINV,IVIVAM,IVIVDT,IVARPO,IVLOC ,IVSLSM,IVSBCD,IVCURT,IVCURD,'I' as IECRTB ";
$withSQL .= " From HDINVC Where IVBLTO=$fromID";
$withSQL .= " union ";
$withSQL .= " Select IVBLTO,IVISEQ,IVAINV,IVIVAM,IVIVDT,IVARPO,IVLOC ,IVSLSM,IVSBCD,IVCURT,IVCURD,'R' as IECRTB ";
$withSQL .= " From HDINVC Where IVBLTO=$fromID ";
$withSQL .= "             and Exists (Select * From ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEISEQ)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType',IVISEQ)) ";
$withSQL .= " union ";
$withSQL .= " Select $CRMSCC as IVBLTO, IEISEQ as IVISEQ,0 as IVAINV,0 as IVIVAM,0 as IVIVDT,' ' as IVARPO,0 as IVLOC ,0 as IVSLSM,' ' as IVSBCD,IECURT as IVCURT,IECURD as IVCURD,'A' as IECRTB ";
$withSQL .= " From ARIVEN Where IEPTYP='M' ";
$withSQL .= " ) ";

$stmtSQL .= " Select IECRTB,IVBLTO,IVISEQ ";
$stmtSQL .= "       ,Case When IECRTB='I' Then IVAINV else Coalesce(PESINV,0) End as IVAINV ";
$stmtSQL .= "       ,IVIVDT,IVIVAM ";
$stmtSQL .= "       ,Coalesce(PEISEQ, IVISEQ)   as PEISEQ ";
$stmtSQL .= "       ,Coalesce(PEENID, 0) as PEENID ";
$stmtSQL .= "       ,Coalesce(PESSEQ, 0) as PESSEQ ";
$stmtSQL .= "       ,Coalesce(PEPMID,' ') as PEPMID ";
$stmtSQL .= "       ,Coalesce(PESPMT, ' ') as PESPMT ";
$stmtSQL .= "       ,Case When Coalesce(PESPMT, ' ')='Y' Then 'CHECKED' ELSE ' ' End as CHECKSELECTION ";
$stmtSQL .= "       ,Case When IECRTB='I' Then IVIVAM else Coalesce(PEAMT,0) End as PEAMT ";
$stmtSQL .= "       ,Coalesce(PEMCHK , ' ') as PEMCHK ";
$stmtSQL .= "       ,Case When IECRTB='I' Then IVSBCD else Coalesce(PESBCD, ' ') End as PESBCD ";
$stmtSQL .= "       ,Case When IECRTB='I' Then IVLOC else Coalesce(PELOC, 0) End as PELOC ";
$stmtSQL .= "       ,Coalesce(PEMEMO, ' ') as PEMEMO ";
$stmtSQL .= "       ,Case When IECRTB='I' Then IVARPO else Coalesce(PEARPO, ' ') End as PEARPO ";
$stmtSQL .= "       ,Coalesce(PEOFCO, 0) as PEOFCO ";
$stmtSQL .= "       ,Coalesce(PEOFFC, 0) as PEOFFC ";
$stmtSQL .= "       ,Coalesce(PEOFAC, 0) as PEOFAC ";
$stmtSQL .= "       ,Coalesce(PEOFSB, 0) as PEOFSB ";
$stmtSQL .= "       ,Coalesce(PECMNT, ' ') as PECMNT ";
$stmtSQL .= "       ,Coalesce(LOLNA1, ' ') as LOLNA1, Coalesce(Upper(LOLNA1), ' ') as LOLNA1U ";
$stmtSQL .= "       ,Coalesce(LOCO#,0)   as LOCO, Coalesce(LOFAC#,0) as LOFAC ";
$stmtSQL .= "       ,Coalesce(PSDESC, ' ') as PSDESC, Coalesce(PSDESCU, ' ') as PSDESCU ";
$stmtSQL .= "       ,Coalesce(FLDESC, ' ') as FLDESC ";
$stmtSQL .= "       ,Coalesce((Select min(PEENID) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID,PEISEQ)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType',Coalesce(b.PEPMID,' '),Coalesce(a.IVISEQ,0))), 1) as MINPEENID ";
$stmtSQL .= "       ,Coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType',Coalesce(b.PEPMID,' '),Coalesce(b.PEISEQ,0),Coalesce(b.PEENID,0))), 0) as ARPYENERROR ";
$fileSQL .= " INVOICE a ";
if ($HDMCRL>0 && $CRPRMC=="Y") {
	$fileSQL .= " inner join HDBANK on (BKBANK,BKCURT)=($fromBatchBank,IVCURT) ";
	$fileSQL .= " inner join HDCFAC on (CFCO#,CFFAC#,CFCURT)=(BKCO,BKFAC,IVCURD) ";
}
$fileSQL .= " left join ARPYEN b on (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEISEQ)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType',IVISEQ) and IECRTB in ('A','R') ";
$fileSQL .= " left join HDCUST aa on CMCUST=Coalesce(IVBLTO,PEBLTO) ";
$fileSQL .= " left join HDLCTN on LOLOC#=IVLOC  ";
$fileSQL .= " left join ARPYSB on PSSBCD=IVSBCD and PSSBCD<>' ' ";
$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('ARPMTID',PEPMID) ";
$selectSQL .= "IVBLTO=$fromID ";
$viewCheckSQL="";
if ($viewCheckBox[0] || $viewCheckBox[1]) {
	if     ($viewCheckBox[0])                        {$viewCheckSQL.= " (Coalesce(PESPMT,' ')='Y' or Coalesce(PEENID,0)>1";}
	if     ($viewCheckBox[1] && $viewCheckSQL == "") {$viewCheckSQL.= " (IECRTB='I' " ;}     // Create By Invoice
	elseif ($viewCheckBox[1])                        {$viewCheckSQL.= "  or IECRTB='I' ";}
	$viewCheckSQL.=")";
}
if ($selectSQL == "") {$selectSQL  = $viewCheckSQL;
} else                {$selectSQL .= " and $viewCheckSQL ";}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint != "Y"){
	print "\n <table $contentTable>";
	print "\n <tr><th class=\"colhdr\">&nbsp;</th>";
	print "\n     <th class=\"colhdr\">Count</th>";
	print "\n     <th class=\"colhdr\">Payment</th>";
	print "\n </tr> ";
	$CASHCNT=Trim(RetValue("(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ", "ARDCEN", "Char(Coalesce(CECICN,0))"));
	$CASHAMT=Trim(RetValue("(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ", "ARDCEN", "Char(Coalesce(CECSAM,0))"));
	print "\n <tr><td class=\"dsphdr\">Pending</td> ";
	print "\n     <td class=\"colnmbr\"><span id=\"CASHCNT\">" . number_format($CASHCNT,0) . "</span></td> ";
	print "\n     <td class=\"colnmbr\"><span id=\"CASHAMT\">" . number_format($CASHAMT,2) . "</span></td> ";
	print "\n </tr> ";
	print "\n </table>";

	$qsOpt = "";
	$qsOpt .= "\n <option value=\"Case When IECRTB='I' Then IVAINV else PESINV End|null|Miscellaneous Payment Number|N|\" title=\"Miscellaneous Payment Number\" SELECTED>Miscellaneous Payment Number";
	$qsOpt .= "\n <option value=\"Case When IECRTB='I' Then IVIVAM else PEAMT End|null|Payment Amount|N|\" title=\"Payment Amount\">Payment Amount";
	$qsOpt .= "\n <option value=\"Case When IECRTB='I' Then IVIVDT else PEBCHD End|DATE|Payment Date|D|\" title=\"Payment Date\">Payment Date";
	$qsOpt .= "\n <option value=\"Case When IECRTB='I' Then ' ' else PEMEMO End|null|Memo|A|U\" title=\"Memo\">Memo";
	$qsOpt .= "\n <option value=\"Case When IECRTB='I' Then IVARPO else PEARPO End|null|Reference Number|A|U\" title=\"Reference Number\">Reference Number";
	$qsOpt .= "\n <option value=\"Case When IECRTB='I' Then IVLOC else PELOC End|null|Location|N|\" title=\"Location\">Location";
	$qsOpt .= "\n <option value=\"Coalesce(Upper(LOLNA1), ' ')|null|Location Name|A|U\" title=\"Location Name\">Location Name";
	$qsOpt .= "\n <option value=\"Case When IECRTB='I' Then IVSBCD else PESBCD End|null|Created By Payment Code|A|U\" title=\"Created By Payment Code\">Created By Payment Code";
	$qsOpt .= "\n <option value=\"Coalesce(PSDESCU, ' ')|null|Created By Payment Code Description|A|U\" title=\"Created By Payment Code Description\">Created By Payment Code Description";
	require 'QuickSearchOption.php';

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"\" onSubmit=\"return ARQuickEntry(); \">";
	print "\n <table $contentTable id=\"paymentTable\"> <tr>";
	$returnValue=OrderBy_Sort("PESPMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Select\"    title=\"Sequence By Selected\">{$sortPoint}Opt</a></th>";
	$returnValue=OrderBy_Sort("IVAINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\"   title=\"Sequence By Miscellaneous Payment Number\">{$sortPoint}Miscellaneous Payment Number</a></th>";
	$returnValue=OrderBy_Sort("PEMCHK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Document\"   title=\"Sequence By Document\">{$sortPoint}Document</a></th>";
	$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtAmount\" title=\"Sequence By Payment Amount\">{$sortPoint}Payment Amount</a></th>";
	$returnValue=OrderBy_Sort("PESBCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SubCode\"    title=\"Sequence By Payment Code\">{$sortPoint}Payment Code</a></th>";
	print "\n <th class=\"colhdr\">&nbsp;</th>";
	$returnValue=OrderBy_Sort("PEMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryMemo\"    title=\"Sequence By Memo\">{$sortPoint}Memo</a></th>";
	$returnValue=OrderBy_Sort("PEARPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryReference\"    title=\"Sequence By Reference Number\">{$sortPoint}Reference Number</a></th>";
	$returnValue=OrderBy_Sort("PELOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryLoc\"    title=\"Sequence By Location\">{$sortPoint}Location</a></th>";
	$returnValue=OrderBy_Sort("PEOFCO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Company\"    title=\"Sequence By Co/Fac\">{$sortPoint}Co/Fac</a></th>";
	$returnValue=OrderBy_Sort("PEOFAC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Account\"    title=\"Sequence By Account\">{$sortPoint}Account</a></th>";
	$returnValue=OrderBy_Sort("IVIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvoiceDate\"       title=\"Sequence By Date Paid\">{$sortPoint}Date Paid</a></th>";
	$returnValue=OrderBy_Sort("LOLNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LocationName\"       title=\"Sequence By Location Name\">{$sortPoint}Location Name</a></th>";
	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SubCodeDesc\"       title=\"Sequence By Created By Payment Code Description\">{$sortPoint}Created By Payment Code Description</a></th>";
	$returnValue=OrderBy_Sort("PEPMID"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TypePmt\"       title=\"Sequence By Transaction Type\">{$sortPoint}Trans Type</a></th>";
	print "\n </tr>";

	// Quick Entry Row
	require  'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colicon\"><a onClick=\"ARQuickEntry();\">$applCashAcceptImage</a></td>";
	print "\n     <td class=\"entry\"><input type=\"hidden\" name=\"addInvoiceNumber\" id=\"addInvoiceNumber\" size=\"7\" maxlength=\"7\"></td> ";
	print "\n     <td class=\"entry\"><input type=\"text\" name=\"addDocument\" id=\"addDocument\" size=\"9\" maxlength=\"15\"></td> ";
	print "\n     <td class=\"entry\"><input type=\"text\" name=\"addAmount\" id=\"addAmount\" size=\"9\" maxlength=\"15\"></td> ";
	print "\n     <td class=\"entry\" nowrap><input type=\"text\" name=\"addPmtCode\" id=\"addPmtCode\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=addPmtCode&amp;fldDesc=none&amp;specificBatchType={$BMBCHT}&amp;specificPmtType=$paymentType&amp;forceChange=\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"entry\">&nbsp;</td> ";
	print "\n     <td class=\"entry\"><input type=\"text\" name=\"addMemo\" id=\"addMemo\" size=\"9\" maxlength=\"15\"></td> ";
	print "\n     <td class=\"entry\"><input type=\"text\" name=\"addReference\" id=\"addReference\" size=\"9\" maxlength=\"22\"></td> ";
	print "\n     <td class=\"entry\" nowrap><input type=\"text\" name=\"addLocation\" id=\"addLocation\" size=\"3\" maxlength=\"3\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=addLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"entry\" nowrap><input type=\"text\" name=\"addCompany\" id=\"addCompany\" size=\"2\" maxlength=\"2\"> ";
	print "\n                             <input type=\"text\" name=\"addFacility\" id=\"addFacility\" size=\"4\" maxlength=\"4\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=addCompany&amp;fldFac=addFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"entry\" nowrap><input type=\"text\" name=\"addAccount\" id=\"addAccount\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                <input type=\"text\" name=\"addSubaccount\" id=\"addSubaccount\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=addAccount&amp;subFld=addSubaccount&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr> ";

	require_once 'ApplCashPaymentJavaUpdateHiddenInclude.php';

	// Invoice rows
	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}

		require  'ApplCashPaymentJavaHiddenUpdateArrayElement.php';  // Add hidden Java Values to Variable
		require  'SetRowClass.php';
		$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
		if ($row['ARPYENERROR']>0) {
			$PESINV_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$row[PEPMID]',$row[PEISEQ],$row[PEENID],'PESINV') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEMCHK_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$row[PEPMID]',$row[PEISEQ],$row[PEENID],'PEMCHK') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEAMT_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$row[PEPMID]',$row[PEISEQ],$row[PEENID],'PEAMT') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PESBCD_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$row[PEPMID]',$row[PEISEQ],$row[PEENID],'PESBCD') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEMEMO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$row[PEPMID]',$row[PEISEQ],$row[PEENID],'PEMEMO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEARPO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$row[PEPMID]',$row[PEISEQ],$row[PEENID],'PEARPO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PELOC_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$row[PEPMID]',$row[PEISEQ],$row[PEENID],'PELOC') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEOFCO_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$row[PEPMID]',$row[PEISEQ],$row[PEENID],'PEOFCO') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
			$PEOFAC_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$row[PEPMID]',$row[PEISEQ],$row[PEENID],'PEOFAC') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		} else {
			$PESINV_ERROR="";
			$PEMCHK_ERROR="";
			$PEAMT_ERROR="";
			$PESBCD_ERROR="";
			$PEMEMO_ERROR="";
			$PEARPO_ERROR="";
			$PELOC_ERROR="";
			$PEOFCO_ERROR="";
			$PEOFAC_ERROR="";
		}

		print "\n <tr class=\"$rowClass\" id=\"row{$row['IVISEQ']}_{$row['PEENID']}\"> ";

		// Delete icon
		$confirmDesc = Format_Confirm_Desc("Miscellaneous Payment Number", "$row[IVAINV]", "Document", "$row[PEMCHK]", "Payment Amount", number_format($row['PEAMT'],2));
		print "\n     <td class=\"colcode\"> ";
		print "\n         <span id=\"spmt{$row['IVISEQ']}_{$row['PEENID']}\"> ";
		if ($row['IECRTB']=="A" || $row['IECRTB']=="R") {
			if ($applCashPaymentDeletePrompt=="Y") {
				print "\n <a onClick=\"if(confirmDelete('$confirmDesc')) {delARPYENLine('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]','$row[IECRTB]');} \">$deleteImageSml</a> ";
			} else {
				print "\n <a onClick=\"delARPYENLine('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]','$row[IECRTB]'); \">$deleteImageSml</a> ";
			}
		} else {
			print "\n &nbsp; ";
		}
		print "\n         </span> ";
		print "\n     </td>";

		// Entry - Selected Invoice
		if ($row['IECRTB']=="A") {
			if ($PESINV_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESINV_ERROR\"  ";}
			else                   {$FldStyle="";}
			print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"sinv{$row['IVISEQ']}_{$row['PEENID']}\" id=\"sinv{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['IVAINV']) . "\" size=\"7\" maxlength=\"7\" $FldStyle onChange=\"editPESINV('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\">" . rtrim($row['IVAINV']) . "</td> ";
		} else {print "\n <td class=\"inputnmbr\">$row[IVAINV]</td> ";}

		// Entry - Document
		if ($row['IECRTB']=="A") {
			if ($PEMCHK_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEMCHK_ERROR\"  ";}
			else                   {$FldStyle="";}
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"mchk{$row['IVISEQ']}_{$row['PEENID']}\" id=\"mchk{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . trim($row['PEMCHK']) . "\" size=\"9\" maxlength=\"15\" $FldStyle onBlur=\"editPEMCHK('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
			if ($BMBCHT=="D") {
				$searchImageAlt= (string) "<img border=\"{$imageBorder}\" src=\"{$homeURL}{$imagePath}smSearch.gif\" title=\"Search Deposit Entry\" alt=\"Search\">";
				print "\n                  <a href=\"{$homeURL}{$phpPath}ApplCashPaymentDepositEntrySearch.php{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;tag=REPORT&amp;docName=Chg&amp;fldDocNumber=mchk{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDocAmount=PaymentAmount\" onclick=\"$searchWinVar\">$searchImageAlt</a> ";
				print "\n                  <input type=\"hidden\" name=\"PaymentAmount\"> ";
			}
			print "\n     </td> ";
		} else {print "\n <td class=\"inputalph\">&nbsp;</td> ";}

		// Entry - Payment Amount
		if ($row['IECRTB']=="A") {
			if ($PEAMT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEAMT_ERROR\"  ";}
			else                  {$FldStyle="";}
			if ($row['PEAMT']!=0) {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEAMT'],2, '.', '') . "\" size=\"9\" maxlength=\"15\" $FldStyle onChange=\"editPEAMT('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
			else                  {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" id=\"amt{$row['IVISEQ']}_{$row['PEENID']}\" value=\"\" size=\"9\" maxlength=\"15\" $FldStyle onChange=\"editPEAMT('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
		} else {print "\n <td class=\"colnmbr\">" . number_format($row['PEAMT'],2, '.', '') . "</td> ";}

		// Entry - Payment Code
		if ($row['IECRTB']=="A") {
			if ($PESBCD_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESBCD_ERROR\"  ";}
			else                   {$FldStyle="";}
			print "\n     <td class=\"inputalph\" nowrap><input type=\"text\" name=\"sbcd{$row['IVISEQ']}_{$row['PEENID']}\" id=\"sbcd{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESBCD']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPESBCD('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
			print "\n                                    <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=sbcd{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none&amp;specificBatchType={$BMBCHT}&amp;specificPmtType=$paymentType\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		} else {print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PSDESC]\">$row[PESBCD]</span></td>";}

		// icons
		if ($row['IECRTB']=="A") {require 'ApplCashPaymentIconsInclude.php';}
		else                     {print "\n <td class=\"inputalph\">&nbsp;</td> ";}

		// Entry - Memo
		if ($row['IECRTB']=="A") {
			if ($PEMEMO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEMEMO_ERROR\"  ";}
			else                   {$FldStyle="";}
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"memo{$row['IVISEQ']}_{$row['PEENID']}\" id=\"memo{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEMEMO']) . "\" size=\"9\" maxlength=\"15\" $FldStyle onChange=\"editPEMEMO('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
		} else {print "\n <td class=\"inputalph\">&nbsp;</td> ";}

		// Entry - Reference
		if ($row['IECRTB']=="A") {
			if ($PEARPO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEARPO_ERROR\"  ";}
			else                   {$FldStyle="";}
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"arpo{$row['IVISEQ']}_{$row['PEENID']}\" id=\"arpo{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEARPO']) . "\" size=\"9\" maxlength=\"22\" $FldStyle onChange=\"editPEARPO('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";
		} else {print "\n <td class=\"colalph\">$row[PEARPO]</td> ";}

		// Entry - Location
		if ($row['IECRTB']=="A") {
			if ($PELOC_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PELOC_ERROR\"  ";}
			else                  {$FldStyle="";}
			print "\n     <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"loc{$row['IVISEQ']}_{$row['PEENID']}\" id=\"loc{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PELOC']) . "\" size=\"3\" maxlength=\"3\" $FldStyle onBlur=\"editPELOC('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
			print "\n                                    <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=loc{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		} else {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[LOLNA1] ($row[LOCO]/$row[LOFAC])\">$row[PELOC]</span></td>";}

		// Entry - Offset Company/Facility
		if ($row['IECRTB']=="A") {
			if ($PEOFCO_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEOFCO_ERROR\"  ";}
			else                   {$FldStyle="";}
			print "\n     <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"ofco{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ofco{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEOFCO']) . "\" size=\"2\" maxlength=\"2\" $FldStyle onBlur=\"editPEOFCO('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
			print "\n                                    <input type=\"text\" name=\"offc{$row['IVISEQ']}_{$row['PEENID']}\" id=\"offc{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEOFFC']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPEOFFC('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
			print "\n                                    <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=ofco{$row['IVISEQ']}_{$row['PEENID']}&amp;fldFac=offc{$row['IVISEQ']}_{$row['PEENID']}&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		} else {print "\n <td class=\"inputalph\">&nbsp;</td> ";}

		// Entry - Offset Account
		if ($row['IECRTB']=="A") {
			if ($PEOFAC_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEOFAC_ERROR\"  ";}
			else                   {$FldStyle="";}
			print "\n     <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"ofac{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ofac{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEOFAC']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPEOFAC('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
			print "\n                                    <input type=\"text\" name=\"ofsb{$row['IVISEQ']}_{$row['PEENID']}\" id=\"ofsb{$row['IVISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEOFSB']) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editPEOFSB('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";
			print "\n                                    <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=ofac{$row['IVISEQ']}_{$row['PEENID']}&amp;subFld=ofsb{$row['IVISEQ']}_{$row['PEENID']}&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		} else {print "\n <td class=\"inputalph\">&nbsp;</td> ";}

		// Invoice columns
		if ($row['IECRTB']=="A") {print "\n <td class=\"coldate\">$F_IVIVDT</td> ";}
		else                     {print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['IVISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_IVIVDT</a></td> ";}
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PELOC] ($row[LOCO]/$row[LOFAC])\">$row[LOLNA1]</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PESBCD]\">$row[PSDESC]</span></td>";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row['FLDESC']) . "\">$row[PEPMID]</span></td>";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}

	if ($rowCount == 0){require 'NoRecordsFound.php';}

	print "\n </table> </form> ";
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
