<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$fromType           = $_GET['fromType'];
$fromID             = $_GET['fromID'];

$docName            = $_GET['docName'];
$fldDocNumber       = $_GET['fldDocNumber'];
$fldPaymentAmount   = $_GET['fldPaymentAmount'];
$fldOtherAmount     = $_GET['fldOtherAmount'];
$fldCCType          = $_GET['fldCCType'];
$fldCCTypeDesc      = $_GET['fldCCTypeDesc'];

$moreInfo           = $_GET['moreInfo'];
$docNumber          = $_GET['docNumber'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$CRPERN=RetValue("RRN(ARCTRL)=1", "ARCTRL", "CRDPER");
$ARPdBegDate=RetValue("PDPER#=$CRPERN", "HDPBED", "PDBDAT");

$page_title     = "Application Of Cash Document Search";
$scriptName     = "ApplCashPaymentDocumentSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldDocNumber=" . urlencode(trim($fldDocNumber)) . "&amp;fldPaymentAmount=" . urlencode(trim($fldPaymentAmount)) . "&amp;fldOtherAmount=" . urlencode(trim($fldOtherAmount)) . "&amp;fldCCType=" . urlencode(trim($fldCCType)) . "&amp;fldCCTypeDesc=" . urlencode(trim($fldCCTypeDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("CECHK","A","Document"));

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("View:", "With Errors", $viewCheckBoxURL, "1", "0"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchPayment, 13, 2) && ";
	print "\n     editNum(document.Search.srchOther, 13, 2) && ";
	print "\n     editNum(document.Search.srchCashPayment, 13, 2) && ";
	print "\n     editNum(document.Search.srchCashDiscount, 13, 2) && ";
	print "\n     editNum(document.Search.srchCashCount, 7, 0) && ";
	print "\n     editNum(document.Search.srchUnappliedPayment, 13, 2) && ";
	print "\n     editNum(document.Search.srchUnappliedDiscount, 13, 2) && ";
	print "\n     editNum(document.Search.srchUnappliedCount, 7, 0) && ";
	print "\n     editNum(document.Search.srchGeneralPayment, 13, 2) && ";
	print "\n     editNum(document.Search.srchGeneralCount, 7, 0) && ";
	print "\n     editNum(document.Search.srchDeductPayment, 13, 2) && ";
	print "\n     editNum(document.Search.srchDeductCount, 7, 0) && ";
	print "\n     editNum(document.Search.srchAdjustPayment, 13, 2) && ";
	print "\n     editNum(document.Search.srchAdjustCount, 7, 0) && ";
	print "\n     editNum(document.Search.srchApplyPayment, 13, 2) && ";
	print "\n     editNum(document.Search.srchApplyDiscount, 13, 2) && ";
	print "\n     editNum(document.Search.srchApplyCount, 7, 0) ) ";
	print "\n     return true;";
	print "\n    }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Document","srchDocument","","operDocument","opersel_alph_short","A","9","9");
	Build_AdvSrch_Entry("Credit Card Type Description","srchCCType","","operCCType","opersel_alph_short","A","20","20");
	Build_AdvSrch_Entry("Payment Document Amount","srchPayment","","operPayment","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Other Document Amount","srchOther","","operOther","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Cash Payment Code","srchCashSubCode","","operCashSubCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Cash Payment Amount","srchCashPayment","","operCashPayment","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Cash Discount","srchCashDiscount","","operCashDiscount","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Cash Invoice Count","srchCashCount","","operCashCount","opersel_num_short","N","7","7");
	Build_AdvSrch_Entry("Unapplied Cash Payment Code","srchUnappliedSubCode","","operUnappliedSubCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Unapplied Cash Payment Amount","srchUnappliedPayment","","operUnappliedPayment","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Unapplied Cash Discount","srchUnappliedDiscount","","operUnappliedDiscount","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Unapplied Cash Invoice Count","srchUnappliedCount","","operUnappliedCount","opersel_num_short","N","7","7");
	Build_AdvSrch_Entry("Deduction Payment Code","srchDeductSubCode","","operDeductSubCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("General Deduction Payment Amount","srchGeneralPayment","","operGeneralPayment","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("General Deduction Invoice Count","srchGeneralCount","","operGeneralCount","opersel_num_short","N","7","7");
	Build_AdvSrch_Entry("Specific Deduction Payment Amount","srchDeductPayment","","operDeductPayment","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Specific Deduction Invoice Count","srchDeductCount","","operDeductCount","opersel_num_short","N","7","7");
	Build_AdvSrch_Entry("Adjustment Payment Code","srchAdjustSubCode","","operAdjustSubCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Adjustment Payment Amount","srchAdjustPayment","","operAdjustPayment","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Adjustment Invoice Count","srchAdjustCount","","operAdjustCount","opersel_num_short","N","7","7");
	Build_AdvSrch_Entry("Apply Credit Payment Code","srchApplySubCode","","operApplySubCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Apply Credit Payment Amount","srchApplyPayment","","operApplyPayment","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Apply Credit Cash Discount","srchApplyDiscount","","operApplyDiscount","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Apply Credit Invoice Count","srchApplyCount","","operApplyCount","opersel_num_short","N","7","7");

	$focusField = "srchDocument";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Document")      {$orby = array(array("CECHK","A","Document"));}
	elseif ($sequence == "crCardType")    {$orby = array(array("OECCDSU","A","Credit Card Type"),array("CECHK","A","Document"));}
	elseif ($sequence == "DepAmount")     {$orby = array(array("CECAMT","A","Payment Document Amount"),array("CECHK","A","Document"));}
	elseif ($sequence == "DepVariance")   {$orby = array(array("PAYMENTVAR","A","Payment Variance"),array("CECHK","A","Document"));}
	elseif ($sequence == "OtherAmount")   {$orby = array(array("CEJAMT","A","Other Document Amount"),array("CECHK","A","Document"));}
	elseif ($sequence == "OtherVariance") {$orby = array(array("OTHERVAR","A","Other Variance"),array("CECHK","A","Document"));}
	elseif ($sequence == "InvoiceCnt")    {$orby = array(array("INVOICECOUNT","A","Invoice Count"),array("CECHK","A","Document"));}
	elseif ($sequence == "Error")         {$orby = array(array("DOCERROR","A","Number Of Errors"),array("CECHK","A","Document"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("trim(CECHK) ", "Document", $_POST['srchDocument'], "U", $_POST['operDocument'], "A");
	$returnValue=Build_WildCard("upper(OECCDS)", "Credit Card Type Description", $_POST['srchCCType'], "U", $_POST['operCCType'], "A");
	$returnValue=Build_WildCard("CECAMT ", "Payment Document Amount", $_POST['srchPayment'], "", $_POST['operPayment'], "N");
	$returnValue=Build_WildCard("CEJAMT ", "Other Document Amount", $_POST['srchOther'], "", $_POST['operOther'], "N");
	$returnValue=Build_WildCard("CECSBC", "Cash Payment Code", $_POST['srchCashSubCode'], "U", $_POST['operCashSubCode'], "A");
	$returnValue=Build_WildCard("CECSAM", "Cash Payment Amount", $_POST['srchCashPayment'], "", $_POST['operCashPayment'], "N");
	$returnValue=Build_WildCard("CECDAM", "Cash Discount", $_POST['srchCashDiscount'], "", $_POST['operCashDiscount'], "N");
	$returnValue=Build_WildCard("CECICN", "Cash Invoice Count", $_POST['srchCashCount'], "", $_POST['operCashCount'], "N");
	$returnValue=Build_WildCard("CEUSBC", "Unapplied Cash Payment Code", $_POST['srchUnappliedSubCode'], "U", $_POST['operUnappliedSubCode'], "A");
	$returnValue=Build_WildCard("CEUSAM", "Unapplied Cash Payment Amount", $_POST['srchUnappliedPayment'], "", $_POST['operUnappliedPayment'], "N");
	$returnValue=Build_WildCard("CEUDAM", "Unapplied Cash Discount", $_POST['srchUnappliedDiscount'], "", $_POST['operUnappliedDiscount'], "N");
	$returnValue=Build_WildCard("CEUICN", "Unapplied Cash Invoice Count", $_POST['srchUnappliedCount'], "", $_POST['operUnappliedCount'], "N");
	$returnValue=Build_WildCard("CEDSBC", "Deduction Payment Code", $_POST['srchDeductSubCode'], "U", $_POST['operDeductSubCode'], "A");
	$returnValue=Build_WildCard("CEDGAM", "General Deduction Payment Amount", $_POST['srchGeneralPayment'], "", $_POST['operGeneralPayment'], "N");
	$returnValue=Build_WildCard("CEDGIC", "General Deduction Invoice Count", $_POST['srchGeneralCount'], "", $_POST['operGeneralCount'], "N");
	$returnValue=Build_WildCard("CEDSAM", "Specific Deduction Payment Amount", $_POST['srchDeductPayment'], "", $_POST['operDeductPayment'], "N");
	$returnValue=Build_WildCard("CEDICN", "Specific Deduction Invoice Count", $_POST['srchDeductCount'], "", $_POST['operDeductCount'], "N");
	$returnValue=Build_WildCard("CEJSBC", "Adjustment Payment Code", $_POST['srchAdjustSubCode'], "U", $_POST['operAdjustSubCode'], "A");
	$returnValue=Build_WildCard("CEJSAM", "Adjustment Payment Amount", $_POST['srchAdjustPayment'], "", $_POST['operAdjustPayment'], "N");
	$returnValue=Build_WildCard("CEJICN", "Adjustment Invoice Count", $_POST['srchAdjustCount'], "", $_POST['operAdjustCount'], "N");
	$returnValue=Build_WildCard("CEYSBC", "Apply Credit Payment Code", $_POST['srchApplySubCode'], "U", $_POST['operApplySubCode'], "A");
	$returnValue=Build_WildCard("CEYSAM", "Apply Credit Payment Amount", $_POST['srchApplyPayment'], "", $_POST['operApplyPayment'], "N");
	$returnValue=Build_WildCard("CEYDAM", "Apply Credit Discount", $_POST['srchApplyDiscount'], "", $_POST['operApplyDiscount'], "N");
	$returnValue=Build_WildCard("CEYICN", "Apply Credit Invoice Count", $_POST['srchApplyCount'], "", $_POST['operApplyCount'], "N");
	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectDocument(docNumber,paymentAmount,otherAmount,ccType,ccTypeDesc){ ";
print "\n   window.opener.document.$docName.$fldDocNumber.value = docNumber; ";
print "\n   if (window.opener.document.$docName.$fldPaymentAmount) {window.opener.document.$docName.$fldPaymentAmount.value = paymentAmount;} ";
print "\n   if (window.opener.document.$docName.$fldOtherAmount)   {window.opener.document.$docName.$fldOtherAmount.value = otherAmount;} ";
print "\n   if (window.opener.document.$docName.$fldCCType)        {window.opener.document.$docName.$fldCCType.value = ccType;} ";
print "\n   if (window.opener.document.$docName.$fldCCTypeDesc)    {window.opener.document.$docName.$fldCCTypeDesc.value = ccTypeDesc;} ";
print "\n   window.opener.document.$docName.$fldDocNumber.focus(); ";
print "\n   window.close(); ";
print "\n } ";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

require 'ApplCashBatchRetInfoInclude.php';
require 'ApplCashCustomerRetInfoInclude.php';

print "\n <table $contentTable><tr> ";
print "\n <td> ";
print "\n <div>";
print "\n     <table $contentTable> ";
Format_Header_Hover("Batch", $fromBatchNumber, $F_fromBatchDate,"batchSelection");
Format_Header("Bank", $bankName, $fromBatchBank);
Format_Header_Hover($idText, $idName, $fromID,"payerSelection");
print "\n     </table> ";
print "\n </div>";
print "\n <div id=\"batchSelection\" class=\"moreInfo\">{$batchInfo}</div>";
print "\n <div id=\"payerSelection\" class=\"moreInfo\">{$payerInfo}</div>";
print "\n </td> ";
print "\n </tr></table> ";

$uv_BankName ="CEBCHB";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select CECHK ,CECCTP, ";
$stmtSQL .= "        CECAMT,CEJAMT,CECSBC, ";
$stmtSQL .= "        CECAMT-(CECSAM+CEUSAM-CEDGAM) as PAYMENTVAR, ";
$stmtSQL .= "        CEJAMT-(CEJSAM+CEYSAM+CEYDAM+CECDAM+CEUDAM) as OTHERVAR, ";
$stmtSQL .= "        CECICN+CEJICN+CEUICN+CEDGIC+CEDICN+CEYICN as INVOICECOUNT, ";
if ($moreInfo=="Y") {
	$stmtSQL .= " CECSAM,CECDAM,CECICN, ";
	$stmtSQL .= " CEJSBC,CEJSAM,CEJICN, ";
	$stmtSQL .= " CEUSBC,CEUSAM,CEUDAM,CEUICN, ";
	$stmtSQL .= " CEDSBC,CEDGAM,CEDGIC,CEDSAM,CEDICN, ";
	$stmtSQL .= " CEYSBC,CEYSAM,CEYDAM,CEYICN, ";
	$stmtSQL .= " Coalesce(a.PSDESC,' ') as PSDESC_C, ";
	$stmtSQL .= " Coalesce(b.PSDESC,' ') as PSDESC_D, ";
	$stmtSQL .= " Coalesce(c.PSDESC,' ') as PSDESC_J, ";
	$stmtSQL .= " Coalesce(d.PSDESC,' ') as PSDESC_U, ";
	$stmtSQL .= " Coalesce(e.PSDESC,' ') as PSDESC_Y, ";
}
$stmtSQL .= " Coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,PRCHK)=(z.CEBCHN,z.CEBCHD,z.CEBCHB,z.CETYPE,z.CEID,z.CECHK)),0) + Coalesce((Select Count(*) from ARDCER Where (CRBCHN,CRBCHD,CRBCHB,CRTYPE,CRID,CRCHK)=(z.CEBCHN,z.CEBCHD,z.CEBCHB,z.CETYPE,z.CEID,z.CECHK)),0) as DOCERROR, ";
$stmtSQL .= " Coalesce(OECCDS,' ') as OECCDS, Coalesce(Upper(OECCDS),' ') as OECCDSU ";
$fileSQL .= " ARDCEN z ";
$fileSQL .= " left join OECCTM   on OECCTP=CECCTP ";
if ($moreInfo=="Y") {
	$fileSQL .= " left join ARPYSB a on a.PSSBCD=CECSBC ";
	$fileSQL .= " left join ARPYSB b on b.PSSBCD=CEDSBC and CEDSBC<>' ' ";
	$fileSQL .= " left join ARPYSB c on c.PSSBCD=CEJSBC and CEJSBC<>' ' ";
	$fileSQL .= " left join ARPYSB d on d.PSSBCD=CEUSBC and CEUSBC<>' ' ";
	$fileSQL .= " left join ARPYSB e on e.PSSBCD=CEYSBC and CEYSBC<>' ' ";
}
$selectSQL .= " (CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID) ";
if ($moreInfo=="Y") {$selectSQL .= " and trim(CECHK)='" . trim($docNumber) . "' ";}

if ($viewCheckBox[0]) {$selectSQL.= " and Coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,PRCHK)=(z.CEBCHN,z.CEBCHD,z.CEBCHB,z.CETYPE,z.CEID,z.CECHK)),0) + Coalesce((Select Count(*) from ARDCER Where (CRBCHN,CRBCHD,CRBCHB,CRTYPE,CRID,CRCHK)=(z.CEBCHN,z.CEBCHD,z.CEBCHB,z.CETYPE,z.CEID,z.CECHK)),0)>0 ";}

require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"trim(CECHK)|null|Document|A|U\" title=\"Document\" SELECTED>Document";
	$qsOpt .= "\n <option value=\"upper(OECCDS)|null|Credit Card Type Description|A|U\" title=\"Credit Card Type Description\">Credit Card Type Description";
	$qsOpt .= "\n <option value=\"CECAMT |null|Payment Document Amount|N|\" title=\"Payment Document Amount\">Payment Document Amount";
	$qsOpt .= "\n <option value=\"CEJAMT|null|Other Document Amount|N|\" title=\"Other Document Amount\">Other Document Amount";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("CECHK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Document\"      title=\"Sequence By Document\">{$sortPoint}Document</a></th>";
	$returnValue=OrderBy_Sort("OECCDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=crCardType\"    title=\"Sequence By Credit Card Type, Document\">{$sortPoint}Credit Card Type</a></th>";
	$returnValue=OrderBy_Sort("CECAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DepAmount\"     title=\"Sequence By Payment Document Amount, Document\">{$sortPoint}Payment Document Amount</a></th>";
	$returnValue=OrderBy_Sort("PAYMENTVAR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DepVariance\"   title=\"Sequence By Payment Variance, Document\">{$sortPoint}Payment Variance</a></th>";
	$returnValue=OrderBy_Sort("CEJAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OtherAmount\"     title=\"Sequence By Other Document Amount, Document\">{$sortPoint}Other Document Amount</a></th>";
	$returnValue=OrderBy_Sort("OTHERVAR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OtherVariance\"   title=\"Sequence By Other Variance, Document\">{$sortPoint}Other Variance</a></th>";
	$returnValue=OrderBy_Sort("INVOICECOUNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvoiceCnt\"    title=\"Sequence By Invoice Count, Document\">{$sortPoint}Invoice Count</a></th>";
	$returnValue=OrderBy_Sort("DOCERROR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Error\"    title=\"Sequence By Number of Errors, Document\">{$sortPoint}Number of Errors</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectDocument('" . trim($row['CECHK']) . "','" . number_format($row['CECAMT'],2,'.','') . "','" . number_format($row['CEJAMT'],2,'.','') . "','" . trim($row['CECCTP']) . "','" . trim($row['OECCDS']) . "')\" title=\"Select Document\">$row[CECHK]</a></td> ";
		print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[CECCTP]\">$row[OECCDS]</span></td>";
		print "\n     <td class=\"colnmbr\">" . number_format($row['CECAMT'],2) . "</td>";
		print "\n     <td class=\"colnmbr\">" . number_format($row['PAYMENTVAR'],2) . "</td>";
		print "\n     <td class=\"colnmbr\">" . number_format($row['CEJAMT'],2) . "</td>";
		print "\n     <td class=\"colnmbr\">" . number_format($row['OTHERVAR'],2) . "</td>";
		print "\n     <td class=\"colnmbr\">$row[INVOICECOUNT]</td>";
		print "\n     <td class=\"colnmbr\">$row[DOCERROR]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;docNumber=" . urlencode(trim($row['CECHK'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);

	print "\n <table $contentTable> ";
	Format_Header("Document", $row[CECHK], "");
	print "\n </table> ";

	$moreInfoSelect = "href=\"javascript:selectDocument('" . trim($row['CECHK']) . "','" . number_format($row['CECAMT'],2,'.','') . "','" . number_format($row['CEJAMT'],2,'.','') . "','" . trim($row['CECCTP']) . "','" . trim($row['OECCDS']) . "')\" title=\"Select Document\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Payments</legend> ";
	print "\n <table $contentTable>";

	print "\n <tr><td class=\"colhdr\">&nbsp;</td> ";
	print "\n     <td class=\"colhdr\">Payment Code</td> ";
	print "\n     <td class=\"colhdr\">Document Amount</td> ";
	print "\n     <td class=\"colhdr\">Payment</td> ";
	print "\n     <td class=\"colhdr\">Discount</td> ";
	print "\n     <td class=\"colhdr\">Variance</td> ";
	print "\n     <td class=\"colhdr\">Invoice Count</td> ";
	print "\n  </tr> ";

	$F_CECSBC=Format_Code($row['CECSBC']);
	print "\n <tr> <td class=\"dsphdr\">Cash</td> ";
	print "\n      <td class=\"colalph\">$row[PSDESC_C] $F_CECSBC</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CECAMT'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CECSAM'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CECDAM'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['PAYMENTVAR'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">$row[CECICN]</td> ";
	print "\n </tr> ";

	if (trim($row['CEUSBC'])!="") {$F_CEUSBC=Format_Code($row['CEUSBC']);}
	else                          {$F_CECSBC="";}
	print "\n <tr> <td class=\"dsphdr\">Unapplied Cash</td> ";
	print "\n      <td class=\"colalph\">$row[PSDESC_U] $F_CEUSBC</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CEUSAM'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CEUDAM'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">$row[CEUICN]</td> ";
	print "\n </tr> ";

	print "\n <tr> <td class=\"dsphdr\">Less General Deduction</td> ";
	print "\n      <td class=\"colalph\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CEDGAM'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">$row[CEDGIC]</td> ";
	print "\n </tr> ";

	if (trim($row['CEJSBC'])!="") {$F_CEJSBC=Format_Code($row['CEJSBC']);}
	else                          {$F_CEJSBC="";}
	print "\n <tr> <td class=\"dsphdr\">Adjustment</td> ";
	print "\n      <td class=\"colalph\">$row[PSDESC_J] $F_CEJSBC</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CEJAMT'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CEJSAM'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['OTHERVAR'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">$row[CEJICN]</td> ";
	print "\n </tr> ";

	if (trim($row['CEYSBC'])!="") {$F_CEYSBC=Format_Code($row['CEYSBC']);}
	else                          {$F_CEYSBC="";}
	print "\n <tr> <td class=\"dsphdr\">Apply Credit</td> ";
	print "\n      <td class=\"colalph\">$row[PSDESC_Y] $F_CEYSBC</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CEYSAM'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CEYDAM'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">$row[CEYICN]</td> ";
	print "\n </tr> ";

	if (trim($row['CEDSBC'])!="") {$F_CEDSBC=Format_Code($row['CEDSBC']);}
	else                          {$F_CEDSBC="";}
	print "\n <tr> <td class=\"dsphdr\">Specific Deduction</td> ";
	print "\n      <td class=\"colalph\">$row[PSDESC_D] $F_CEDSBC</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">" . number_format($row['CEDSAM'],2) . "</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n      <td class=\"colnmbr\">$row[CEDICN]</td> ";
	print "\n </tr> ";
	print "\n </tr> ";

	print "\n </table> ";
	print "\n </fieldset> ";

	if (trim($row['CECCTP'])!="") {
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n <legend class=\"legendTitle\">Credit Card</legend> ";
		print "\n <table $contentTable>";

		$F_CECCTP=Format_Code($row['CECCTP']);
		print "\n <tr><td class=\"dsphdr\">Credit Card Type</td> ";
		print "\n     <td class=\"dspalph\">$row[OECCDS] $F_CECCTP</td> ";
		print "\n </tr> ";

		print "\n </table> ";
		print "\n </fieldset> ";
	}

	if ($row['DOCERROR']>0) {
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n <legend class=\"legendTitle\">Errors</legend> ";
		print "\n <table $contentTable>";
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
		$stmtSQL .= " Select PRCOLM,PRERR,PRPTYP,PRPMID,PRISEQ,PRENID ";
		if ($fromType=="P") {$stmtSQL .= " ,Coalesce(w.CMCNA1, ' ') as CMCNA1, Coalesce(w.CMCNA1U,' ') as CMCNA1U ";}
		else                {$stmtSQL .= " ,' ' as CMCNA1, ' ' as CMCNA1U ";}
		$stmtSQL .= " ,Coalesce(IVIVDT,PEBCHD,0) as IVIVDT ";
		$stmtSQL .= " ,Case When PRPTYP='M' Then 'Miscellaneous' When PRPTYP='0' Then 'Document' Else Coalesce(CPDESC,' ') End as CPDESC ";
		$stmtSQL .= " ,Coalesce(b.FLDESC,' ') as PRPMID_FLDESC ";
		$stmtSQL .= " ,Coalesce(PSDESC, ' ') as PSDESC, Coalesce(PSDESCU,' ') as PSDESCU ";
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
		if ($fromType=="P") {$fileSQL .= " left join HDCUST w on w.CMCUST=Coalesce(IVBLTO,PEBLTO) ";}
		$fileSQL .= " left join ARPAYT on CPTYPE=PRPTYP ";
		$fileSQL .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTID',PRPMID) ";
		$fileSQL .= " left join ARPYSB on PSSBCD=PESBCD ";
		$selectSQL .= " (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($docNumber) . "') ";
		require 'stmtSQLSelect.php';
		$stmtSQL .= " Order By ERERDS ";
		require 'stmtSQLEnd.php';
		require 'stmtSQLTotalRows.php';
		$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

		print "<table $contentTable> <tr>";
		print "\n <th class=\"colhdr\">Payment Type</th>";
		print "\n <th class=\"colhdr\">Trans Type</th>";
		print "\n <th class=\"colhdr\">Invoice</th>";
		print "\n <th class=\"colhdr\">Date</th>";
		print "\n <th class=\"colhdr\">Payment Amount</th>";
		print "\n <th class=\"colhdr\">Discount</th>";
		print "\n <th class=\"colhdr\">Payment Code</th>";
		if ($fromType=="P") {
			print "\n <th class=\"colhdr\">Customer</th>";
		}
		print "\n <th class=\"colhdr\">Column</th>";
		print "\n <th class=\"colhdr\">Value</th>";
		print "\n <th class=\"colhdr\">Error</th>";
		print "\n </tr>";

		while ($row = db2_fetch_assoc($sqlResult)){
			require  'SetRowClass.php';
			print "\n <tr class=\"$rowClass\">";
			$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PRPTYP]\">$row[CPDESC]</span></td> ";
			print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row[PRPMID_FLDESC]) . "\">$row[PRPMID]</span></td>";
			print "\n <td class=\"colnmbr\">$row[PESINV]</td> ";
			print "\n <td class=\"coldate\">$F_IVIVDT</td> ";
			if ($row['PEAMT']!=0) {print "\n <td class=\"colnmbr\">" . number_format($row['PEAMT'],2) . "</td> ";}
			else                  {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
			if ($row['PEDAMT']!=0) {print "\n <td class=\"colnmbr\">" . number_format($row['PEDAMT'],2) . "</td> ";}
			else                   {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PESBCD]\">$row[PSDESC]</span></td> ";
			if ($fromType=="P") {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['PEBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[PEBLTO]]\">$row[CMCNA1]</a></td> ";}
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PRCOLM]\">$row[PRCOLM_FLDESC]</span></td> ";
			$value=trim($row[PRCOLM]);
			print "\n <td class=\"colalph\">$row[$value]</td> ";
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PRERR]\">$row[ERERDS]</span></td> ";
			print "\n </tr>";
		}
		print "\n </table> ";
		print "\n </fieldset> ";
	}
	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardPrint.php';
}

print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
