<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$paymentType        = "Y";
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
$scriptName    = "ApplCashPaymentApplyError.php";
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
	if     ($sequence == "Select")         {$orby = array(array("PESSEQ" ,"A","Selected Sequence"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "Invoice")        {$orby = array(array("PESINV" ,"A","Invoice"),array("PEISEQ","A",""),array("PEENID","A",""));}
	elseif ($sequence == "PmtAmount")      {$orby = array(array("PEAMT" ,"A","Payment Amount"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "Discount")       {$orby = array(array("PEDAMT" ,"A","Discount"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "InvBal")         {$orby = array(array("IVBALN" ,"A","Invoice Balance"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "NetBal")         {$orby = array(array("IVNETB" ,"A","Net Balance"),array("PESINV" ,"A","Invoice"));}
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
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'ShowHideSelCriteria.php';
require_once 'StringTrimJavaScript.php';

require_once 'ApplCashPaymentApplyJava.php';
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
$stmtSQL .= " Select PEISEQ,PEENID,PESSEQ,PEAMT,PEDAMT,PECMNT,PEPOTP,PEPOEN ";
$stmtSQL .= "       ,Coalesce(IVAINV,PESINV) as PESINV ";
$stmtSQL .= "       ,Coalesce(IVISEQ,0) as IVISEQ,IVIVCD,IVAINV,IVDSCD ";
$stmtSQL .= "       ,$InvBalSQL as IVBALN ";
$stmtSQL .= "       ,Coalesce(IVIVAM-IVNPOS-IVPPOS,0) as IVNETB ";
require_once 'ApplCashPaymentInvoiceIncludeSQLSelect.php';  // Includes HDINVC columns
$fileSQL .= " ARPYEN b ";
$fileSQL .= " left join HDINVC a on IVISEQ=PEISEQ ";
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
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\"      title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th>";
$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtAmount\"       title=\"Sequence By Payment Amount\">{$sortPoint}Payment Amount</a></th>";
if ($columnDisplay['PEDAMT']=="Y") {
	$returnValue=OrderBy_Sort("PEDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Discount\"     title=\"Sequence By Discount\">{$sortPoint}Discount</a></th>";
}

// Icons
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

require_once 'ApplCashPaymentJavaUpdateHiddenInclude.php';

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
		$PESINV_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PESINV') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		$PEAMT_ERROR =RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID)       =($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID]) and PRCOLM in ('PEAMT','PESBCD') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
		$PEDAMT_ERROR=RetValue("(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$paymentType','$paymentID',$row[PEISEQ],$row[PEENID],'PEDAMT') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Coalesce(ERERDS,PRERR,' '))");
	} else {
		$PESINV_ERROR="";
		$PEAMT_ERROR="";
		$PEDAMT_ERROR="";
	}
	print "\n <tr class=\"$rowClass\" id=\"row{$row['PEISEQ']}_{$row['PEENID']}\"> ";

	// Delete icon
	print "\n     <td class=\"inputcode\"> ";
	print "\n         <span id=\"spmt{$row['PEISEQ']}_{$row['PEENID']}\"> ";
	if ($applCashPaymentDeletePrompt=="Y") {
 		if (trim($row['PEPOSB'])>"") {$deleteMsg="Apply Credit payment and Payoff for invoice {$row[IVAINV]}";}
		else                         {$deleteMsg="Apply Credit payment for invoice {$row[IVAINV]}";}
		print "\n <a onClick=\"if(confirmDelete('$deleteMsg')) {delARPYENLine('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','I');} \">$deleteImageSml</a> ";
	} else {
		print "\n <a onClick=\"delARPYENLine('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]','I'); \">$deleteImageSml</a> ";
	}
	print "\n         </span> ";
	print "\n     </td>";

	// Entry - Invoice
	if ($PESINV_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PESINV_ERROR\"  ";}
	else                   {$FldStyle="";}
	print "\n <td class=\"colnmbr\"> ";
	if (trim($row[PEISEQ])==0) {print "\n <input type=\"text\" name=\"sinv{$row['PEISEQ']}_{$row['PEENID']}\" id=\"sinv{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESINV']) . "\" size=\"7\" maxlength=\"7\" $FldStyle onBlur=\"editPESINV('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"> ";}
	else                       {print "\n <input type=\"hidden\" name=\"sinv{$row['PEISEQ']}_{$row['PEENID']}\" id=\"sinv{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PESINV']) . "\" $FldStyle>$row[PESINV] ";}
	print "\n </td> ";

	// Entry - Payment Amount
	if ($PEAMT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEAMT_ERROR\"  ";}
	else                  {$FldStyle="";}
	if ($row['PEAMT']!=0) {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEAMT'],2, '.', '') . "\" size=\"15\" maxlength=\"15\" $FldStyle onChange=\"editPEAMT('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
	else                  {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"amt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"\" size=\"15\" maxlength=\"15\" $FldStyle onChange=\"editPEAMT('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}

	// Entry - Discount
	if ($columnDisplay['PEDAMT']=="Y") {
		if ($PEDAMT_ERROR!="") {$FldStyle="style=\"background-color: $errorBackground\" title=\"$PEDAMT_ERROR\"  ";}
		else                   {$FldStyle="";}
		if ($row['PEDAMT']!=0) {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . number_format($row['PEDAMT'],2, '.', '') . "\" size=\"15\" maxlength=\"15\" $FldStyle onChange=\"editPEDAMT('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
		else                   {print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"\" size=\"15\" maxlength=\"15\" $FldStyle onChange=\"editPEDAMT('$row[PEISEQ]','$row[PEENID]','$row[MINPEENID]')\"></td> ";}
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

	// Other Pending Payments
	if ($columnDisplay['ADJAMT']=="Y") {
		if ($row['ADJAMT']<>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashPaymentOtherInquiry.php{$maintainVar}\" title=\"View Other Pending Activity\" onclick=\"$searchWinVar\">" . number_format($row['ADJAMT'],2) . "</a></td> ";}
		else                   {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
	}

	// Information columns
	require 'ApplCashPaymentInvoiceIncludeDetail.php';

	// Add hidden fields needed for Javascript
	if ($columnDisplay['PEDAMT']!="Y") {print "\n <td><input type=\"hidden\" name=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"damt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . rtrim($row['PEDAMT']) . "\"></td> ";}
	if ($columnDisplay['IVNETB']!="Y") {print "\n <td><input type=\"hidden\"   id=\"netb{$row['PEISEQ']}_{$row['PEENID']}\"</td> ";}
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
