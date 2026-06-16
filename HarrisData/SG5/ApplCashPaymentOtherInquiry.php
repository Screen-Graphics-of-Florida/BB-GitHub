<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$fromID             = $_GET['fromID'];
$fromType           = $_GET['fromType'];
$fromDocument       = $_GET['fromDocument'];
$fromPaymentType    = $_GET['fromPaymentType'];
$fromPaymentID      = $_GET['fromPaymentID'];
$fromInvoiceSeq     = $_GET['fromInvoiceSeq'];
$fromEntryID        = $_GET['fromEntryID'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Application Of Cash: Other Pending Activity";
$scriptName     = "ApplCashPaymentOtherInquiry.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;fromPaymentType=" . urlencode(trim($fromPaymentType)) . "&amp;fromPaymentID=" . urlencode(trim($fromPaymentID)) . "&amp;fromInvoiceSeq=" . urlencode(trim($fromInvoiceSeq)) . "&amp;fromEntryID=" . urlencode(trim($fromEntryID)) ;
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("PSDESCU","A","Payment Code"));
$medIcon        = "Y";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$dspMaxRows = $prtMaxRows;
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "PmtType")   {$orby = array(array("CPDESC","A","Payment Type"),array("PSDESCU","A","Payment Code"));}
	elseif ($sequence == "PmtID")     {$orby = array(array("PEPMID_FLDESC","A","Transaction Type"),array("PSDESCU","A","Payment Code"));}
	elseif ($sequence == "PmtAmount") {$orby = array(array("PEAMT" ,"A","Amount"),array("PSDESCU","A","Payment Code"));}
	elseif ($sequence == "Discount")  {$orby = array(array("PEDAMT" ,"A","Discount"),array("PSDESCU","A","Payment Code"));}
	elseif ($sequence == "SubCode")   {$orby = array(array("PSDESCU" ,"A","Payment Code"));}
	require_once 'OrderByUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onLoad=\"window.focus()\" onBlur=\"window.close()\">";
require $inquiryBanner;
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

print "\n <td class=\"toolbar\">";
require_once 'HelpPage.php';
require 'CloseWindow.php';
print "</td>";
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

print $inquiryhrTagAttr;

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
$stmtSQL .= " Select PEPTYP,PEPMID,PESBCD,PEAMT,PEDAMT, ";
$stmtSQL .= " Case When PEPTYP='M' Then 'Miscellaneous' Else Coalesce(CPDESC,' ') End as CPDESC, ";
$stmtSQL .= " Coalesce(b.FLDESC,' ') as PEPMID_FLDESC, ";
$stmtSQL .= " Coalesce(PSDESC, PESBCD) as PSDESC, Coalesce(PSDESCU,' ') as PSDESCU ";
$fileSQL .= " ARPYEN ";
$fileSQL .= " left join HDINVC on IVISEQ=PEISEQ ";
$fileSQL .= " left join HDCUST on CMCUST=Coalesce(Nullif(PEBLTO,0),IVBLTO) ";
$fileSQL .= " left join ARPAYT on CPTYPE=PEPTYP ";
$fileSQL .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTID',PEPMID) ";
$fileSQL .= " left join ARPYSB on PSSBCD=PESBCD ";
$selectSQL .= "(PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEISEQ)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "',$fromInvoiceSeq) ";
$selectSQL .= " and (PEPTYP<>'$fromPaymentType' or PEPMID<>'$fromPaymentID') ";
$selectSQL .= " and PEENID<>$fromEntryID ";
if ($PEPOTP!="") {$selectSQL .= " and (PEPTYP<>'$PEPOTP' or PEENID<>$PEPOEN) ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable> <tr>";
$returnValue=OrderBy_Sort("CPDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$scriptVarBase}&amp;tag=ORDERBY&amp;sequence=PmtType\"     title=\"Sequence By Payment Type\">{$sortPoint}Payment Type</a></th>";
$returnValue=OrderBy_Sort("PEPMID_FLDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$scriptVarBase}&amp;tag=ORDERBY&amp;sequence=PmtID\"     title=\"Sequence By Transaction Type\">{$sortPoint}Trans Type</a></th>";
$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$scriptVarBase}&amp;tag=ORDERBY&amp;sequence=PmtAmount\"       title=\"Sequence By Payment Amount\">{$sortPoint}Payment Amount</a></th>";
$returnValue=OrderBy_Sort("PEDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$scriptVarBase}&amp;tag=ORDERBY&amp;sequence=Discount\"     title=\"Sequence By Discount\">{$sortPoint}Discount</a></th>";
$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$scriptVarBase}&amp;tag=ORDERBY&amp;sequence=SubCode\"     title=\"Sequence By Payment Code\">{$sortPoint}Payment Code</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PEPTYP]\">$row[CPDESC]</span></td> ";
	print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row[PEPMID_FLDESC]) . "\">$row[PEPMID]</span></td>";
	print "\n <td class=\"colnmbr\">" . number_format($row['PEAMT'],2) . "</td> ";
	print "\n <td class=\"colnmbr\">" . number_format($row['PEDAMT'],2) . "</td> ";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PESBCD]\">$row[PSDESC]</span></td> ";
	print "\n </tr>";

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table>";
print $inquiryhrTagAttr;
require 'CloseWindow.php';
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require $inquiryTrailer;
print "\n </body> \n </html>";

?>
