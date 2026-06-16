<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
if (is_null($_GET['fromID'])) {
	$fromID   == "";
	$fromType == "";
} else {
	$fromID   = $_GET['fromID'];
	$fromType = $_GET['fromType'];
}
if (is_null($_GET['fromDocument'])) {$fromDocument  = "";}
else                                {$fromDocument  = $_GET['fromDocument'];}
if (is_null($_GET['fromPaymentType'])) {
	$fromPaymentType == "";
	$fromPaymentID   == "";
	$fromInvoiceSeq  == "";
	$fromEntryID     == "";
} else {
	$fromPaymentType = $_GET['fromPaymentType'];
	$fromPaymentID   = $_GET['fromPaymentID'];
	$fromInvoiceSeq  = $_GET['fromInvoiceSeq'];
	$fromEntryID     = $_GET['fromEntryID'];
}

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Application Of Cash: Error";
$scriptName     = "ApplCashErrorInquiry.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;fromPaymentType=" . urlencode(trim($fromPaymentType)) . "&amp;fromPaymentID" . urlencode(trim($fromPaymentID)) . "&amp;fromInvoiceSeq" . urlencode(trim($fromInvoiceSeq)) . "amp;$fromEntryID" . urlencode(trim($fromEntryID)) ;
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$medIcon        = "Y";
if ($fromType=="")            {$dftOrderBy = array(array("PRTYPE_FLDESC","A","Type"),array("PRID_NAMEU","A","ID"),array("PRCHK","A","Document"),array("PESINV","A","Invoice"),array("PSDESCU","A","Payment Code"));}
elseif ($fromDocument=="")    {$dftOrderBy = array(array("PRCHK","A","Document"),array("PESINV","A","Invoice"),array("PSDESCU","A","Payment Code"));}
elseif ($fromPaymentType=="") {$dftOrderBy = array(array("PRID_NAMEU","A","ID"),array("PRCHK","A","Document"),array("PESINV","A","Invoice"),array("PSDESCU","A","Payment Code"));}
else                          {$dftOrderBy = array(array("PRCOLM","A","Column"));}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Type")      {$orby = array(array("PRTYPE_FLDESC","A","Type"),array("PRID_NAMEU","A","ID"),array("PRCHK","A","Document"),array("PSDESCU","A","Payment Code"));}
	elseif ($sequence == "IDName")    {$orby = array(array("PRID_NAMEU","A","ID"),array("PRTYPE_FLDESC","A","Type"),array("PRCHK","A","Document"),array("PSDESCU","A","Payment Code"));}
	elseif ($sequence == "Document")  {$orby = array(array("PRCHK" ,"A","Document"),array("PSDESCU","A","Payment Code"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "PmtType")   {$orby = array(array("CPDESC" ,"A","Payment Type"),array("PSDESCU","A","Payment Code"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "PmtID")     {$orby = array(array("PRPMID_FLDESC" ,"A","Transaction Type"),array("PSDESCU","A","Payment Code"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "SubCode")   {$orby = array(array("PSDESCU" ,"A","Payment Code"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "Invoice")   {$orby = array(array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "Date")      {$orby = array(array("IVIVDT" ,"A","Invoice Date"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "PmtAmount") {$orby = array(array("PEAMT" ,"A","Amount"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "Discount")  {$orby = array(array("PEDAMT" ,"A","Discount"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "Customer")  {$orby = array(array("CMCNA1U" ,"A","Customer"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "Column")    {$orby = array(array("PRCOLM_FLDESC" ,"A","Column"),array("PESINV" ,"A","Invoice"));}
	elseif ($sequence == "Error")     {$orby = array(array("ERERDS" ,"A","Error"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';

$formName = "Search";  // Need to Calendar Include
require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require $inquiryBanner;
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y"){
	print "\n <td class=\"toolbar\">";
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	require 'CloseWindow.php';
	print "</td>";
}
print "\n </tr></table>";

print "\n <table $contentTable> ";
$F_BIBCHD=Format_Date($fromBatchDate, "D");
Format_Header("Batch", $fromBatchNumber, $F_BIBCHD);
$BKBKNM=RetValue("BKBANK=$fromBatchBank", "HDBANK", "BKBKNM");
Format_Header("Bank", $BKBKNM, $fromBatchBank);
if ($fromType=="C"){
	$CMCNA1=RetValue("CMCUST=$fromID", "HDCUST", "CMCNA1");
	Format_Header("Customer", $CMCNA1, $fromID);
} elseif ($fromType=="P") {
	$PYPYNM=RetValue("PYPAYR=$fromID", "ARPYRH", "PYPYNM");
	Format_Header("Payer", $PYPYNM, $fromID);
}
if ($fromDocument!=""){Format_Header("Document", $fromDocument, "");}
print "\n </table> ";

if ($fromPaymentType!="") {
	print $inquiryhrTagAttr;

	$uv_BankName ="PEBCHB";
	require 'UserView.php';

	require 'stmtSQLClear.php';
	$stmtSQL .= " Select PEPTYP,PEPMID,PEISEQ,PEENID ";
	$stmtSQL .= "       ,PESBCD,Coalesce(IVBLTO,PEBLTO) as PEBLTO,PEAMT,PEDAMT,PEISEQ,PECRTB,PESINV ";
	$stmtSQL .= "       ,Coalesce(IVISEQ,0) as IVISEQ ";
	$stmtSQL .= "       ,Coalesce(IVAINV,0) as IVAINV ";
	$stmtSQL .= "       ,Coalesce(IVIVDT,PEBCHD,0) as IVIVDT ";
	$stmtSQL .= "       ,Case When PEPTYP='M' Then 'Miscellaneous' Else Coalesce(CPDESC,' ') End as CPDESC ";
	$stmtSQL .= "       ,Coalesce(b.FLDESC,' ') as PEPMID_FLDESC ";
	if ($fromType=="P") {$stmtSQL .= ",Coalesce(CMCNA1, ' ') as CMCNA1, Coalesce(CMCNA1U,' ') as CMCNA1U ";}
	$stmtSQL .= "       ,Coalesce(PSDESC, PESBCD) as PSDESC, Coalesce(PSDESCU,' ') as PSDESCU ";
	if ($HDOERL<=0) {$stmtSQL .= ",0 as OEINVCOUNT " ;}
	else            {$stmtSQL .= ",(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(c.IVAINV,c.IVIVDT,c.IVBLTO) and c.IVIVCD='C') as OEINVCOUNT " ;}
	$fileSQL .= " ARPYEN ";
	$fileSQL .= " left join HDINVC c on IVISEQ=PEISEQ ";
	if ($fromType=="P") {$fileSQL .= " left join HDCUST on CMCUST=Coalesce(IVBLTO,PEBLTO) ";}
	$fileSQL .= " left join ARPAYT on CPTYPE=PEPTYP ";
	$fileSQL .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTID',PEPMID) ";
	$fileSQL .= " left join ARPYSB on PSSBCD=PESBCD ";
	$selectSQL .= " (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEISEQ,PEENID)= ";
	$selectSQL .= " ($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "','$fromPaymentType',$fromInvoiceSeq,$fromEntryID) ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

	print "\n <table $contentTable> <tr>";
	print "\n <th class=\"colhdr\">Payment Type</th>";
	print "\n <th class=\"colhdr\">Trans Type</th>";
	print "\n <th class=\"colhdr\">Invoice</th>";
	print "\n <th class=\"colhdr\">Date</th>";
	print "\n <th class=\"colhdr\">Payment Amount</th>";
	print "\n <th class=\"colhdr\">Discount</th>";
	print "\n <th class=\"colhdr\">Payment Code</th>";
	if ($fromType=="P") {print "\n <th class=\"colhdr\">Customer</th>";}
	print "\n </tr>";

	$row = db2_fetch_assoc($sqlResult);
	$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
	print "\n <tr>";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PEPTYP]\">$row[CPDESC]</span></td> ";
	print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row[PEPMID_FLDESC]) . "\">$row[PEPMID]</span></td>";
	if ($row['OEINVCOUNT']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['PEBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['IVAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['IVIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[IVAINV]</a></td> ";}
	else                      {print "\n <td class=\"colnmbr\">$row[IVAINV]</td> ";}
	if ($row['IVISEQ']==0) {print "\n <td class=\"coldate\">$F_IVIVDT</td> ";}
	else                   {print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['PEBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['PEISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_IVIVDT</a></td> ";}
	if ($row['PEAMT']!=0) {print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['PEAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";}
	else                  {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
	if ($row['PEDAMT']!=0) {print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['PEDAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";}
	else                   {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PESBCD]\">$row[PSDESC]</span></td> ";
	if ($fromType=="P") {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['PEBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[PEBLTO]]\">$row[CMCNA1]</a></td> ";}
	print "\n </tr>";
	print "\n </table> ";
}

print $inquiryhrTagAttr;

$uv_BankName ="PRBCHB";
require 'UserView.php';

require 'stmtSQLClear.php';
$withSQL .= " With ERROR ";
$withSQL .= "(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,PRCHK,PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM,PRERR) ";
$withSQL .= " as ( ";
$withSQL .= " Select PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,PRCHK,PRPTYP,PRPMID,PRISEQ,PRENID,PRCOLM,PRERR ";
$withSQL .= " From ARPYER";
$withSQL .= " union ";
$withSQL .= " Select CRBCHN,CRBCHD,CRBCHB,CRTYPE,CRID,CRCHK,'0' as PRPTYP,' ' as PRPMID,0 as PRISEQ,0 as PRENID,' ' as PRCOLM,CRERR ";
$withSQL .= " From ARDCER ";
$withSQL .= " ) ";
$stmtSQL .= " Select PRCOLM,PRERR ";
if ($fromType=="") {
	$stmtSQL .= " ,PRTYPE,PRID ";
	$stmtSQL .= " ,Coalesce(a.FLDESC,' ') as PRTYPE_FLDESC ";
	$stmtSQL .= " ,Case When PRTYPE='P' Then Coalesce(PYPYNM, ' ') Else Coalesce(y.CMCNA1, ' ') End as PRID_NAME ";
	$stmtSQL .= " ,Case When PRTYPE='P' Then Coalesce(PYPYNMU, ' ') Else Coalesce(y.CMCNA1U, ' ') End as PRID_NAMEU ";
} else {
	$stmtSQL .= " ,' ' as PRTYPE,0 as PRID ";
	$stmtSQL .= " ,' ' as PRTYPE_FLDESC ";
	$stmtSQL .= " ,' ' as PRID_NAME ";
	$stmtSQL .= " ,' ' as PRID_NAMEU ";
}

if ($fromType=="" || $fromType=="P") {$stmtSQL .= " ,Coalesce(w.CMCNA1, ' ') as CMCNA1, Coalesce(w.CMCNA1U,' ') as CMCNA1U ";}
else                                 {$stmtSQL .= " ,' ' as CMCNA1, ' ' as CMCNA1U ";}

if ($fromDocument==""){$stmtSQL .= " ,PRCHK ";}
else                  {$stmtSQL .= " ,' ' as PRCHK ";}

if ($fromPaymentType=="") {
	$stmtSQL .= " ,PRPTYP,PRPMID,PRISEQ,PRENID ";
	$stmtSQL .= " ,Coalesce(IVISEQ,0) as IVISEQ ";
	$stmtSQL .= " ,Coalesce(IVIVDT,PEBCHD,0) as IVIVDT ";
	$stmtSQL .= " ,Case When PRPTYP='M' Then 'Miscellaneous' When PRPTYP='0' Then 'Document' Else Coalesce(CPDESC,' ') End as CPDESC ";
	$stmtSQL .= " ,Coalesce(b.FLDESC,' ') as PRPMID_FLDESC ";
	$stmtSQL .= " ,Coalesce(PSDESC, ' ') as PSDESC, Coalesce(PSDESCU,' ') as PSDESCU ";
	if ($HDOERL<=0) {
		$stmtSQL .= ",0 as OEINVCOUNT " ;
	} else {
		$stmtSQL .= ",(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(IVAINV,IVIVDT,IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
	}
} else {
	$stmtSQL .= " ,' ' as PRPTYP,0 as PRPMID,0 as PRISEQ,0 as PRENID ";
	$stmtSQL .= " ,0 as IVISEQ ";
	$stmtSQL .= " ,0 as IVIVDT ";
	$stmtSQL .= " ,' ' as CPDESC ";
	$stmtSQL .= " ,' ' as PRPMID_FLDESC ";
	$stmtSQL .= " ,' ' as PSDESC, ' ' as PSDESCU ";
	$stmtSQL .= " ,0 as OEINVCOUNT " ;
}
$stmtSQL .= " ,Coalesce(PEPTYP, ' ') as PEPTYP  ";
$stmtSQL .= " ,Coalesce(PEPMID, ' ') as PEPMID  ";
$stmtSQL .= " ,Coalesce(IVAINV,PESINV,0) as PESINV  ";
$stmtSQL .= " ,Coalesce(PEAMT, 0) as PEAMT  ";
$stmtSQL .= " ,Coalesce(PEDAMT, 0) as PEDAMT  ";
$stmtSQL .= " ,Coalesce(PESBCD, ' ') as PESBCD  ";
$stmtSQL .= " ,Coalesce(IVBLTO,PEBLTO) as PEBLTO  ";
$stmtSQL .= " ,Coalesce(ERERDS, PRERR) as ERERDS  ";
$stmtSQL .= " ,Coalesce(c.FLDESC,' ') as PRCOLM_FLDESC ";
$fileSQL .= " ERROR ";
$fileSQL .= " left join ARPYEN on (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,PECHK,PEPTYP,PEPMID,PEISEQ,PEENID)= ";
$fileSQL .= "                     (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,PRCHK,PRPTYP,PRPMID,PRISEQ,PRENID) ";
$fileSQL .= " left join HDERROR on ERER#=PRERR ";
$fileSQL .= " left join SYFLAG c on (c.FLTYPE,c.FLVALU)=('ARPMTCOLM',PRCOLM) ";
$fileSQL .= " left join HDINVC on IVISEQ=PEISEQ ";
if ($fromType=="") {
	$fileSQL .= " left join SYFLAG a on (a.FLTYPE,a.FLVALU)=('ARCUSTPAYR',PRTYPE) ";
	$fileSQL .= " left join ARPYRH on PYPAYR=PRID and PRTYPE='P' ";
	$fileSQL .= " left join HDCUST y on y.CMCUST=PRID ";
}
if ($fromType=="" || $fromType=="P") {$fileSQL .= " left join HDCUST w on w.CMCUST=Coalesce(IVBLTO,PEBLTO) ";}
if ($fromPaymentType=="") {
	$fileSQL .= " left join ARPAYT on CPTYPE=PRPTYP ";
	$fileSQL .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTID',PRPMID) ";
	$fileSQL .= " left join ARPYSB on PSSBCD=PESBCD ";
}
$selectSQL .= " (PRBCHN,PRBCHD,PRBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
if ($fromType!="")        {$selectSQL .= " and PRTYPE='$fromType' and PRID=$fromID ";}
if ($fromDocument!="")    {$selectSQL .= " and trim(PRCHK)='" . trim($fromDocument) . "' ";}
if ($fromPaymentType!="") {$selectSQL .= " and (PRPTYP,PRISEQ,PRENID)=('$fromPaymentType',$fromInvoiceSeq,$fromEntryID) ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$advanceSearch = "N";	
	
	$qsOpt  = "";
	if ($fromType=="") {
		$qsOpt .= "\n <option value=\"Upper(Coalesce(a.FLDESC,' '))|null|Type Description|A|U\" title=\"Type Description\">Type Description";
		$qsOpt .= "\n <option value=\"Case When PRTYPE='P' Then Coalesce(PYPYNMU, ' ') Else Coalesce(y.CMCNA1U, ' ') End|null|ID Name|A|U\" title=\"ID Name\">ID Name";
	}
	if ($fromDocument==""){$qsOpt .= "\n <option value=\"trim(PRCHK)|null|Document|A|U\" title=\"Document\">Document";}
	if ($fromPaymentType=="") {
		$qsOpt .= "\n <option value=\"Upper(Case When PEPTYP='M' Then 'MISCELLANEOUS' Else Coalesce(CPDESC,' ') End)|null|Payment Type Description|A|U\" title=\"Payment Type Description\">Payment Type Description";
		$qsOpt .= "\n <option value=\"Coalesce(b.FLDESC,' ')|null|Transaction Type Description|A|U\" title=\"Transaction Type Description\">Transaction Type Description";
		$qsOpt .= "\n <option value=\"Coalesce(IVAINV,PESINV,0)|null|Invoice|N|\" title=\"Invoice\">Invoice";
		$qsOpt .= "\n <option value=\"Coalesce(IVIVDT,PEBCHD,0)|DATE|Date|D|\" title=\"Date\">Date";
		$qsOpt .= "\n <option value=\"PEAMT|null|Payment Amount|N|\" title=\"Payment Amount\">Payment Amount";
		$qsOpt .= "\n <option value=\"PEDAMT|null|Discount|N|\" title=\"Discount\">Discount";
		$qsOpt .= "\n <option value=\"Coalesce(PSDESCU,' ')|null|Payment Code Description|A|U\" title=\"Payment Code Description\">Payment Code Description";
		if ($fromType=="" || ($fromType=="P")) {
			$qsOpt .= "\n <option value=\"Coalesce(w.CMCNA1U,' ')|null|Customer Name|A|U\" title=\"Customer Name\">Customer Name";
		}
	}
	$qsOpt .= "\n <option value=\"Upper(Coalesce(c.FLDESC,' '))|null|Column Description|A|U\" title=\"Column Description\">Column Description";
	$qsOpt .= "\n <option value=\"ERERDS|null|Error Description|A|U\" title=\"Error Description\" SELECTED>Error Description";
	require 'QuickSearchOption.php';
}

print "\n <table $contentTable> <tr>";
if ($fromType=="") {
	$returnValue=OrderBy_Sort("PRTYPE_FLDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Type\"     title=\"Sequence By Payer/Customer Type\">{$sortPoint}Type</a></th>";
	$returnValue=OrderBy_Sort("PRID_NAMEU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=IDName\"     title=\"Sequence By Payer/Customer ID\">{$sortPoint}ID</a></th>";
}
if ($fromDocument=="") {
	$returnValue=OrderBy_Sort("PRCHK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Document\"     title=\"Sequence By Document\">{$sortPoint}Document</a></th>";
}
if ($fromPaymentType=="") {
	$returnValue=OrderBy_Sort("CPDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtType\"     title=\"Sequence By Payment Type\">{$sortPoint}Payment Type</a></th>";
	$returnValue=OrderBy_Sort("PRPMID_FLDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtID\"     title=\"Sequence By Transaction Type\">{$sortPoint}Trans Type</a></th>";
	$returnValue=OrderBy_Sort("PESINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\"      title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th>";
	$returnValue=OrderBy_Sort("IVIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Date\"         title=\"Sequence By Invoice Date\">{$sortPoint}Date</a></th>";
	$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtAmount\"       title=\"Sequence By Payment Amount\">{$sortPoint}Payment Amount</a></th>";
	$returnValue=OrderBy_Sort("PEDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Discount\"     title=\"Sequence By Discount\">{$sortPoint}Discount</a></th>";
	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SubCode\"     title=\"Sequence By Payment Code\">{$sortPoint}Payment Code</a></th>";
	if ($fromType=="" || ($fromType=="P")) {
		$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Customer\"     title=\"Sequence By Customer\">{$sortPoint}Customer</a></th>";
	}
}
$returnValue=OrderBy_Sort("PRCOLM_FLDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Column\"       title=\"Sequence By Column\">{$sortPoint}Column</a></th>";
print "\n <th class=\"colhdr\">Value</th>";
$returnValue=OrderBy_Sort("ERERDS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Error\"       title=\"Sequence By Error\">{$sortPoint}Error</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\"> ";
	if ($fromType=="") {
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PRTYPE]\">$row[PRTYPE_FLDESC]</span></td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PRID]\">$row[PRID_NAME]</span></td> ";
	}
	if ($fromDocument=="") {
		print "\n <td class=\"colalph\">$row[PRCHK]</td> ";
	}
	if ($fromPaymentType=="") {
		$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PRPTYP]\">$row[CPDESC]</span></td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row[PRPMID_FLDESC]) . "\">$row[PRPMID]</span></td>";
		if ($row['OEINVCOUNT']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['PEBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['PESINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['IVIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[PESINV]</a></td> ";}
		else                      {print "\n <td class=\"colnmbr\">$row[PESINV]</td> ";}
		if ($row['IVISEQ']==0) {print "\n <td class=\"coldate\">$F_IVIVDT</td> ";}
		else                   {print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['PRISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_IVIVDT</a></td> ";}
		if ($row['PEAMT']!=0) {print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['PEAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";}
		else                  {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
		if ($row['PEDAMT']!=0) {print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['PEDAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";}
		else                   {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PESBCD]\">$row[PSDESC]</span></td> ";
		if ($fromType=="" || ($fromType=="P")) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['PEBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[PEBLTO]]\">$row[CMCNA1]</a></td> ";}
	}
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PRCOLM]\">$row[PRCOLM_FLDESC]</span></td> ";
	$value=trim($row[PRCOLM]);
	print "\n <td class=\"colalph\">$row[$value]</td> ";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PRERR]\">$row[ERERDS]</span></td> ";
	print "\n </tr>";

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print $inquiryhrTagAttr;
if ($formatToPrint != "Y") {require 'CloseWindow.php';}
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require $inquiryTrailer;
print "\n </body> \n </html>";

?>
