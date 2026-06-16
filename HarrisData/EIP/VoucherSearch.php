<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];

require_once 'SetLibraryList.php';
require_once "APControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Voucher Search";
$scriptName     = "VoucherSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("VMVNA1U","A","Name"),array("VENDOR","A","Number"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CalendarInclude.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchNumber, 7, 0) && ";
	print "\n     editNum(document.Search.srchPhone, 11, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Number","srchNumber","","operNumber","opersel_num_short","N","7","7");
	Build_AdvSrch_Entry("Voucher","srchVoucher","","operVoucher","opersel_num_short","N","9","9");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","26","26");
	Build_AdvSrch_Entry("Invoice","srchInvoice","","operVoucher","opersel_alph_short","A","20","20");
	Build_AdvSrch_Entry("Amount","srchAmount","","operAmount","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("PO","srchPO","","operPO","opersel_num_short","N","8","8");
	Build_AdvSrch_Entry("Memo","srchMemo","","operMemo","opersel_alph_short","A","16","16");

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Number")  {$orby = array(array("VENDOR","A","Number"),array("VOUCHER","A","Voucher"));}
	elseif ($sequence == "Voucher") {$orby = array(array("VOUCHER","A","Voucher"),array("VENDOR","A","Number"));}
	elseif ($sequence == "Name")    {$orby = array(array("VMVNA1U","A","Name"),array("VOUCHER","A","Voucher"));}
	elseif ($sequence == "Invoice") {$orby = array(array("INVOICE","A","Invoice"));}
	elseif ($sequence == "Amount")  {$orby = array(array("TRNAMT","A","Amount"),array("VENDOR","A","Number"));}
	elseif ($sequence == "PO")      {$orby = array(array("PO","A","Purchase Order"));}
	elseif ($sequence == "Memo")    {$orby = array(array("MEMO","A","Memo"),array("VENDOR","A","Number"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("VENDOR", "Vendor Number", $_POST['srchNumber'], "", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("VMVNA1U", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("INVOICE", "Invoice", $_POST['srchInvoice'], "U", $_POST['operInvoice'], "A");
	$returnValue=Build_WildCard("VOUCHER", "Voucher", $_POST['srchVoucher'], "", $_POST['operVoucher'], "N");
	$returnValue=Build_WildCard("TRNAMT", "Amount", $_POST['srchAmount'], "", $_POST['operAmount'], "N");
	$returnValue=Build_WildCard("PO", "PO Number", $_POST['srchPO'], "", $_POST['operPO'], "N");
	$returnValue=Build_WildCard("MEMO", "Memo", $_POST['srchMemo'], "U", $_POST['operMemo'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectVoucher(number,amount){ ";
print "\n amount = amount.trim(); ";
print "\n window.opener.document.$docName.$fldName.value = number; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = amount;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = amount;}";
print "\n window.opener.document.$docName.$fldName.focus(); ";
print "\n window.close(); ";
print "\n } ";

require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
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

$uv_VendorName ="VENDOR";
$uv_VendorTypeName ="VMVTYP";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " APOPENV02 ";
$selectSQL="VENDOR<>0 and TRNAMT>0 ";
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	$qsOpt  = "\n <option value=\"VENDOR|null|Vendor Number|N|\" title=\"Vendor Number\">Vendor Number";
	$qsOpt .= "\n <option value=\"VMVNA1U|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"INVOICE|null|Invoice|A|U\" title=\"Invoice Number\">Invoice Number";
	$qsOpt .= "\n <option value=\"VOUCHER|null|Voucher|N|\" title=\"Voucher Number\">Voucher Number";
	$qsOpt .= "\n <option value=\"TRNAMT|null|Amount|N|\" title=\"Invoice Amount\">Invoice Amount";
	$qsOpt .= "\n <option value=\"PO|null|Purchase Order Number|N|\" title=\"PO Number\">PO Number";
	$qsOpt .= "\n <option value=\"MEMO|null|Memo|A|U\" title=\"Memo\">Memo";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("VOUCHER"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Voucher\" title=\"Sequence By City, Name\">{$sortPoint}Voucher<br>Number</a></th>";
	$returnValue=OrderBy_Sort("VENDOR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number\">{$sortPoint}Vendor<br>Number</a></th>";
	$returnValue=OrderBy_Sort("VMVNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Vendor<br>Name</a></th>";
	$returnValue=OrderBy_Sort("INVOICE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\" title=\"Sequence By Address\">{$sortPoint}Invoice<br>Number</a></th>";
	$returnValue=OrderBy_Sort("TRNAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Amount\"      title=\"Sequence By State, Name\">{$sortPoint}Invoice<br>Amount</a></th>";
	$returnValue=OrderBy_Sort("PO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PO\"      title=\"Sequence By Zip, Name\">{$sortPoint}Purchase<br>Order</a></th>";
	$returnValue=OrderBy_Sort("MEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Memo\" title=\"Sequence By Phone\">{$sortPoint}Memo</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Name=Format_Quote($row['VMVNA1']);
		print "\n <tr class=\"$rowClass\">";
        $depAmt = Format_Nbr($row['TRNAMT'], "2", $amtEditCode);
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectVoucher('" . trim($row['VOUCHER']) . "','" . trim($depAmt) . "')\" title=\"Select Voucher\">$row[VOUCHER]</a></td> ";
		print "\n     <td class=\"colnmbr\">$row[VENDOR]</td>";
		print "\n     <td class=\"colalph\">$F_Name</td> ";
		print "\n     <td class=\"colalph\">$row[INVOICE]</td>";
		print "\n     <td class=\"colnmbr\">$row[TRNAMT]</td>";
		print "\n     <td class=\"colnmbr\">$row[PO]</td>";
		print "\n     <td class=\"colalph\">$row[MEMO]</td>";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";

	require_once 'PageBottom.php';
	require_once 'WildCardPrint.php';

print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
