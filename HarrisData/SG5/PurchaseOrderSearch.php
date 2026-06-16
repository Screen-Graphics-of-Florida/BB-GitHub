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

$page_title     = "Purchase Order Search";
$scriptName     = "PurchaseOrderSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("VMVNA1U","A","Name"),array("POVEND","A","Number"));

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

	Build_AdvSrch_Entry("Purchase Order","srchPO","","operPO","opersel_num_short","N","8","8");
	Build_AdvSrch_Entry("Vendor","srchNumber","","operNumber","opersel_num_short","N","7","7");
	Build_AdvSrch_Entry("Warehouse","srchWhs","","operWhs","opersel_num_short","N","7","7");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","26","26");
	Build_AdvSrch_Entry("Required Date","srchReqDate","","operReqDate","opersel_num_short","N","6","6");
	Build_AdvSrch_Entry("Reference","srchRef","","operCity","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Terms","srchTerms","","operTerms","opersel_alph_short","A","20","20");

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "PO")       {$orby = array(array("POPO","A","Purchase Order"));}
	elseif ($sequence == "Vendor")   {$orby = array(array("POVEND","A","Vendor"),array("POPO","D","Purchase Order"));}
	elseif ($sequence == "Whs")      {$orby = array(array("POWHS","A","Warehouse"),array("POPO","D","Purchase Order"));}
	elseif ($sequence == "Name")     {$orby = array(array("VMVNA1U","A","Name"),array("POPO","D","Purchase Order"));}
	elseif ($sequence == "ReqDate")  {$orby = array(array("PORQDT","A","Required Date"),array("POPO","D","Purchase Order"));}
	elseif ($sequence == "Reference"){$orby = array(array("POPORF","A","Reference"));}
	elseif ($sequence == "Terms")    {$orby = array(array("POPOTD","A","Terms"),array("VMVNA1U","A","Name"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("POPO", "Purchase Order", $_POST['srchPO'], "", $_POST['operPO'], "N");
	$returnValue=Build_WildCard("POVEND", "Number", $_POST['srchNumber'], "", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("VMVNA1U", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("POWHS", "Warehouse", $_POST['srchWhs'], "", $_POST['operWhs'], "N");
	$returnValue=Build_WildCard("PORQDT", "Required Date", $_POST['srchReqDate'], "", $_POST['operReqDate'], "D");
	$returnValue=Build_WildCard("POPORF", "State", $_POST['srchRef'], "U", $_POST['operRef'], "A");
	$returnValue=Build_WildCard("POPOTD", "Terms", $_POST['srchTerms'], "U", $_POST['operTerms'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectPONumber(number){ ";
print "\n window.opener.document.$docName.$fldName.value = number; ";
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

$uv_VendorName ="POVEND";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select POPO,POVEND,POWHS,POPORF,PORQDT,POPTRM,POPOTD,VMVNA1,VMVNA1U ";
$fileSQL .= " POPOMS inner join HDVEND on POVEND=VMVEND ";
$selectSQL="POSTAT='O' ";
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	$qsOpt  = "\n <option value=\"POPO|null|Purchase Order|N|\" title=\"Purchase Order\">Purchase Order";
    $qsOpt .= "\n <option value=\"POVEND|null|Vendor Number|N|\" title=\"Vendor Number\">Vendor Number";
	$qsOpt .= "\n <option value=\"POWHS|null|Warehouse|N|U\" title=\"Warehouse\">Warehouse";
    $qsOpt .= "\n <option value=\"VMVNA1U|null|Vendor Name|A|U\" title=\"Vendor Name\" SELECTED>Vendor Name";
	$qsOpt .= "\n <option value=\"POPORF|null|Reference|A|U\" title=\"Reference\">Reference";
	$qsOpt .= "\n <option value=\"PORQDT|null|Required|D|\" title=\"Required Date\">Required Date";
	$qsOpt .= "\n <option value=\"POPOTD|null|Terms|A|U\" title=\"Terms\">Terms";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("POPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PO\"      title=\"Sequence By PO Number, Name\">{$sortPoint}Purchase<br>Order</a></th>";
	$returnValue=OrderBy_Sort("POVEND"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Vendor\" title=\"Sequence By Number\">{$sortPoint}Vendor<br>Number</a></th>";
	$returnValue=OrderBy_Sort("POWHS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Whs\" title=\"Sequence By Whs\">{$sortPoint}Whs</a></th>";
	$returnValue=OrderBy_Sort("VMVNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Vendor<br>Name</a></th>";
	$returnValue=OrderBy_Sort("PORQDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ReqDate\"      title=\"Sequence By Required Date, Name\">{$sortPoint}Required<br>Date</a></th>";
	$returnValue=OrderBy_Sort("POPORF"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Reference\" title=\"Sequence By Reference\">{$sortPoint}Reference</a></th>";
	$returnValue=OrderBy_Sort("POPOTD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Terms\" title=\"Sequence By Terms\">{$sortPoint}Terms</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Name=Format_Quote($row['VMVNA1']);
		$F_PORQDT=Format_Date($row['PORQDT'], "D");
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\"><a href=\"javascript:selectPONumber('" . trim($row['POPO']) . "')\" title=\"Select Voucher\">$row[POPO]</a></td> ";
		print "\n     <td class=\"colnmbr\">$row[POVEND]</td>";
		print "\n     <td class=\"colnmbr\">$row[POWHS]</td>";
		print "\n     <td class=\"colalph\">$F_Name</td> ";
		print "\n     <td class=\"coldate\">$F_PORQDT</td>";
		print "\n     <td class=\"colalph\">$row[POPORF]</td>";
		print "\n     <td class=\"colalph\">$row[POPOTD]</td>";
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
