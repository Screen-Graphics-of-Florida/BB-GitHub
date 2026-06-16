<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'GetGLDDParm.php';

$glDate          = $_GET['glDate'];
$glDDAp          = $_GET['glDDAp'];
$glDDFile        = $_GET['glDDFile'];
$glDDSeq         = $_GET['glDDSeq'];
$glJrnl          = $_GET['glJrnl'];
$glJrnlRec       = $_GET['glJrnlRec'];
$glJrnlSeq       = $_GET['glJrnlSeq'];
$glPer           = $_GET['glPer'];
$unpostedRRN     = $_GET['unpostedRRN'];

require_once 'SetLibraryList.php';

require_once "GLControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Purchasing Detail";
$scriptName     = "GLDDDetailPO.php";
$scriptVarBase  = "{$genericVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($glJrnl)) . "&amp;glPer=" . urlencode(trim($glPer)) . "&amp;glDate=" . urlencode(trim($glDate)) . "&amp;glJrnlSeq=" . urlencode(trim($glJrnlSeq)) . "&amp;glJrnlRec=" . urlencode(trim($glJrnlRec)) . "&amp;unpostedRRN=" . urlencode(trim($unpostedRRN)) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDAp=" . urlencode(trim($glDDAp)) . "&amp;glDDFile=" . urlencode(trim($glDDFile));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$backHome       = "glDDReport.d2w/REPORT";
$dftOrderBy = array(array("POTRDT","D","Date"),array("POITEM","A","Item"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	$fromToSearch = "";
	require_once ($headInclude);
	print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}{$GLDDStyleSheet}\"> ";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.Search.srchAmt, 13, 2) ";
	print "\n    && editdate(document.Search.srchTranDate) ";
	print "\n    && editNum(document.Search.srchWhs, 3, 0) ";
	if ($HDPDRL > 0) {
		print "\n    && editNum(document.Search.srchPlt, 3, 0) ";
	}
	print "\n    && editNum(document.Search.srchPONumber, 9, 0) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "I";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Amount","srchAmt","","operAmt","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Transaction Date","srchTranDate","","operTranDate","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("Item Number","srchItem","","operItem","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Warehouse","srchWhs","","operWhs","opersel_num_short","N","3","3");
	if ($HDPDRL > 0) {Build_AdvSrch_Entry("Plant","srchPlt","","operPlt","opersel_num_short","N","3","3");}
	Build_AdvSrch_Entry("Purchase Order","srchPONumber","","operPONumber","opersel_num_short","N","9","9");
	Build_AdvSrch_Entry("Product Class","srchProdClass","","operProdClass","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Inventory Type","srchInvType","","operInvType","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Vendor Name","srchVendorName","","operVendorName","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Vendor Number","srchVendorNumber","","operVendorNumber","opersel_num_short","N","7","7");

	$focusField = "srchAmt";
	require_once 'AdvSearchBottom.php';
}

if ($tag == "ORDERBY"){
	if     ($sequence == "Amount")       {$orby = array(array("POAMT","A","Amount")) ;}
	elseif ($sequence == "TransDate")    {$orby = array(array("POTRDT","A","Transaction Date")) ;}
	elseif ($sequence == "Item")         {$orby = array(array("POITEM","A","Item")) ;}
	elseif ($sequence == "ItemDesc")     {$orby = array(array("POIMDSU","A","Description")) ;}
	elseif ($sequence == "Warehouse")    {$orby = array(array("POWHS","A","Warehouse")) ;}
	elseif ($sequence == "Plant")        {$orby = array(array("POPLT","A","Plant")) ;}
	elseif ($sequence == "PONumber")     {$orby = array(array("POPO","A","Purchase Order")) ;}
	elseif ($sequence == "Quantity")     {$orby = array(array("POQTY","A","Quantity")) ;}
	elseif ($sequence == "ProdClass")    {$orby = array(array("POPCLS","A","Product Class")) ;}
	elseif ($sequence == "InvType")      {$orby = array(array("POITC","A","Inventory Type")) ;}
	elseif ($sequence == "VendorName")   {$orby = array(array("VMVNA1U","A","Vendor Name")) ;}
	elseif ($sequence == "VendorNumber") {$orby = array(array("POVEND","A","Vendor Number")) ;}

	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard ("POAMT", "Amount", $_POST['srchAmt'], "", $_POST['operAmt'], "N");
	$returnValue=Build_WildCard ("POTRDT", "Transaction Date", $_POST['srchTranDate'], "", $_POST['operTranDate'], "D");
	$returnValue=Build_WildCard ("POITEM", "Item Number", $_POST['srchItem'], "U", $_POST['operItem'], "A");
	$returnValue=Build_WildCard ("POIMDSU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard ("POWHS", "Warehouse", $_POST['srchWhs'], "", $_POST['operWhs'], "N");
	$returnValue=Build_WildCard ("POPLT", "Plant", $_POST['srchPlt'], "", $_POST['operPlt'], "N");
	$returnValue=Build_WildCard ("POPO", "Purchase Order", $_POST['srchPONumber'], "", $_POST['operPONumber'], "N");
	$returnValue=Build_WildCard ("POPCLS", "Product Class", $_POST['srchProdClass'], "U", $_POST['operProdClass'], "A");
	$returnValue=Build_WildCard ("POITC", "Inventory Type", $_POST['srchInvType'], "U", $_POST['operInvType'], "A");
	$returnValue=Build_WildCard ("VMVNA1U", "Vendor Name", $_POST['srchVendorName'], "U", $_POST['operVendorName'], "A");
	$returnValue=Build_WildCard ("POVEND", "Vendor Number", $_POST['srchVendorNumber'], "", $_POST['operVendorNumber'], "N");

	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

// Build Page ****************************************
require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}{$GLDDStyleSheet}\"> ";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';

$formName = "Search";  // Need to Calendar Include
require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require $inquiryBanner;

print "\n <table $contentTable> ";
print "\n     <tr><td> ";
print "\n         <table $contentTable><colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n             <tr><td><h1>$page_title</h1></td> ";
if ($formatToPrint != "Y") {
	print "\n <td class=\"toolbar\"> ";
	if ($ddReport!="") {print "\n <a href=\"{$homeURL}{$cGIPath}GLDDReport.d2w/REPORT{$altVarBase}{$glDDVarBase}&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . "\" title=\"Return To Drill Down Report\">$portalHome</a> ";}
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	print "\n </td> ";
}
print "\n </tr> ";

if ($unpostedRRN>0) {
	$uv_CompanyName   ="JOCO";
	$uv_FacilityName  ="JOFAC";
	$uv_AccountName   ="JOACC";
	$uv_SubaccountName="JOSUB";
	require 'UserView.php';
	$uv_Sql1=$uv_Sql;
} else {
	$uv_CompanyName   ="TDCO";
	$uv_FacilityName  ="TDFAC";
	$uv_AccountName   ="TDACCT";
	$uv_SubaccountName="TDSUB";
	require 'UserView.php';
	$uv_Sql1=$uv_Sql;
}

$uv_CompanyName   ="POCO";
$uv_FacilityName  ="POFAC";
$uv_AccountName   ="POACCT";
$uv_SubaccountName="POSUB";
require 'UserView.php';
$uv_Sql2=$uv_Sql;

if ($uv_Sql != "") {print "\n <tr><td><h3> You may not be authorized to view some transactions </h3></td></tr> ";}
print "\n </table> ";

print $inquiryhrTagAttr;

if ($unpostedRRN>0) {
	// Unposted Journal ****************************************
	require 'stmtSQLClear.php';
	$uv_Sql=$uv_Sql1;
	$appendUserView="";
	$appendWildCard="N";
	$stmtSQL .= " SELECT JOREF,JOCO,JOFAC,JOACC,JOSUB,JOAMT,JODESC ";
	$stmtSQL .= "       ,coalesce(CFCFNM,' ') as CFCFNM, coalesce(CFCURT,' ') as CFCURT ";
	$stmtSQL .= "       ,coalesce(CHCHDS,' ') as CHCHDS, coalesce(CHACSG,' ') as CHACSG ";
	$stmtSQL .= "       ,coalesce(FLDESC,' ') as FLDESC,  upper(coalesce(FLDESC,' ')) as FLDESCU ";
	$stmtSQL .= "       ,coalesce(CYDESC,' ') as CYDESC,  upper(coalesce(CYDESC,' ')) as CYDESCU ";
	$fileSQL .= " HDPOTR ";
	$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(JOCO,JOFAC) ";
	$fileSQL .= " left join HDCHRT on (CHACCT,CHSUB)=(JOACC,JOSUB) ";
	$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('USAGE',CHACSG) ";
	$fileSQL .= " left join HDCTYP on CYTYPE=CFCURT ";
	$selectSQL .= " RRN(HDPOTR)=$unpostedRRN ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);
	if (! $row) {exit;}

	print "\n <table $contentTable> ";
	print "\n <tr><th class=\"colhdr\">Reference</th> ";
	print "\n <th class=\"colhdr\">Co/Fac</th> ";
	print "\n <th class=\"colhdr\">Name</th> ";
	print "\n <th class=\"colhdr\">Account</th> ";
	print "\n <th class=\"colhdr\">Account Description</th> ";
	print "\n <th class=\"colhdr\">Usage</th> ";
	if ($HDMCRL>0 && $CTPRMC=="Y") {print "\n <th class=\"colhdr\">Cur</th> ";}
	print "\n <th class=\"colhdr\">Amount</th> ";
	print "\n <th class=\"colhdr\">Description</th> ";
	print "\n </tr> ";

	$F_AcctSub=Format_Acct($row['JOACC'], $row['JOSUB'],"N");
	$F_JOAMT=Format_Nbr($row['JOAMT'], "2", $amtEditCode, "Y", "", "");
	$F_CoFac=Format_CoFac($row['JOCO'], $row['JOFAC'],"N");
	require 'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colalph\">$row[JOREF]</td> ";
	print "\n <td class=\"colnmbr\">$F_CoFac</td> ";
	print "\n <td class=\"colalph\">$row[CFCFNM]</td> ";
	print "\n <td class=\"colnmbr\">$F_AcctSub</td> ";
	print "\n <td class=\"colalph\">$row[CHCHDS]</td> ";
	print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CHACSG]\">$row[FLDESC]</span></td> ";
	if ($HDMCRL>0 && $CTPRMC=="Y") {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CFCURT]\">$row[CYDESC]</span></td> ";}
	print "\n <td class=\"colnmbr\">$F_JOAMT</td> ";
	print "\n <td class=\"colalph\">$row[JODESC]</td> ";
	print "\n </tr> ";
	print "\n </table> ";
} else {
	// Posted Journal ****************************************
	require 'stmtSQLClear.php';
	$uv_Sql=$uv_Sql1;
	$appendUserView="";
	$appendWildCard="N";
	$stmtSQL .= " SELECT TDPER# as TDPER,TDCO,TDFAC,TDACCT,TDSUB,TDREF,TDAMT,TDDESC ";
	$fileSQL .= " GLTRDT ";
	$selectSQL .= " (TDJRNL,TDPER#,TDTDTE,TDJSEQ,TDREC)=('$glJrnl',$glPer,$glDate,$glJrnlSeq,$glJrnlRec) ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);
	if (! $row) {exit;}

	print "\n <table $contentTable> ";
	print "\n <tr><th class=\"colhdr\">Reference</th> ";
	print "\n <th class=\"colhdr\">Co/Fac</th> ";
	print "\n <th class=\"colhdr\">Name</th> ";
	print "\n <th class=\"colhdr\">Account</th> ";
	print "\n <th class=\"colhdr\">Account Name</th> ";
	print "\n <th class=\"colhdr\">Usage</th> ";
	if ($HDMCRL>0 && $CTPRMC=="Y") {print "\n <th class=\"colhdr\">Cur</th> ";}
	print "\n <th class=\"colhdr\">Amount</th> ";
	print "\n <th class=\"colhdr\">Description</th> ";
	print "\n </tr> ";

	$incUnposted="N";
	$returnValue=Retrieve_AcctJrnl_Data($profileHandle, $dataBaseID, $row['TDCO'], $row['TDFAC'], $row['TDACCT'], $row['TDSUB'], $row['TDPER'], $row['TDPER'], $incUnposted);
	$acctName      = $returnValue['acctName'];
	$coFacName     = $returnValue['coFacName'];
	$balanceIncome = $returnValue['balanceIncome'];
	$currencyUnit  = $returnValue['currencyUnit'];
	$currencyType  = $returnValue['currencyType'];
	$beginBal      = $returnValue['beginBal'];

	$F_CoFac=Format_CoFac($row['TDCO'], $row['TDFAC'],"N");
	$F_AcctSub=Format_Acct($row['TDACCT'], $row['TDSUB'],"N");
	$F_TDAMT=Format_Nbr($row['TDAMT'], "2", $amtEditCode, "Y", "", "");

	require 'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colalph\">$row[TDREF]</td> ";
	print "\n <td class=\"colnmbr\">$F_CoFac</td> ";
	print "\n <td class=\"colalph\">$coFacName</td> ";
	print "\n <td class=\"colnmbr\">$F_AcctSub</td> ";
	print "\n <td class=\"colalph\">$acctName</td> ";
	print "\n <td class=\"colalph\">$currencyUnit</td> ";
	if ($HDMCRL>0 && $CTPRMC=="Y") {print "\n <td class=\"colalph\">$currencyType</td> ";}
	print "\n <td class=\"colnmbr\">$F_TDAMT</td> ";
	print "\n <td class=\"colalph\">$row[TDDESC]</td> ";
	print "\n </tr> ";
	print "\n </table> ";
}

// Drill Down transactions ****************************************
require 'stmtSQLClear.php';
$uv_Sql=$uv_Sql2;
$appendUserView="";
$appendWildCard="";
$stmtSQL .= " SELECT POAMT,POQTY,POTRDT,POWHS,POPLT,POVEND,POPO,POSEQ "; 
$stmtSQL .= "       ,POPCLS,POITC,POPOEC,POITEM,POIMDS,POIMDSU ";
$stmtSQL .= "       ,coalesce(WHWHNM,' ') as WHWHNM ";
$stmtSQL .= "       ,coalesce(PLNAME,' ') as PLNAME ";
$stmtSQL .= "       ,coalesce(VMVNA1,' ') as VMVNA1 ";
$stmtSQL .= "       ,coalesce(VMVNA1U, ' ') as VMVNA1U ";
$stmtSQL .= "       ,coalesce(PCPCDS,' ') as PCPCDS ";
$stmtSQL .= "       ,coalesce(ITDESC,' ') as ITDESC ";
$fileSQL .= " POGLDD ";
$fileSQL .= " left join HDWHSM on WHWHS =POWHS ";
$fileSQL .= " left join HDPLNT on PLPLNT=POPLT ";
$fileSQL .= " left join HDVEND on VMVEND = POVEND ";
$fileSQL .= " left join HDPCLS on PCPCLS=POPCLS ";
$fileSQL .= " left join HDITYP on ITITC =POITC ";
$selectSQL .= " (POPER#,PODDSQ)=($glPer,$glDDSeq) ";
require 'stmtSQLSelect.php';
$stmtSQL .= "  ORDER BY $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"POAMT|null|Amount|N|\" title=\"Amount\">Amount";
	$qsOpt .= "\n <option value=\"POTRDT|DATE|Transaction Date|D|\" title=\"Transaction Date\">Transaction Date";
	$qsOpt .= "\n <option value=\"POITEM|null|Item Number|A|U\" title=\"Item Number\">Item Number";
	$qsOpt .= "\n <option value=\"POIMDSU|null|Description|A|U\" title=\"Description\">Description";
	$qsOpt .= "\n <option value=\"POWHS|null|Warehouse|N|\" title=\"Warehouse\">Warehouse";
	if ($HDPDRL > 0) {$qsOpt .= "\n <option value=\"POPLT|null|Plant|N|\" title=\"Plant\">Plant";}
	$qsOpt .= "\n <option value=\"POPO|null|Purchase Order|N|\" title=\"Purchase Order\" SELECTED>Purchase Order";
	$qsOpt .= "\n <option value=\"POPCLS|null|Product Class|A|U\" title=\"Product Class\">Product Class";
	$qsOpt .= "\n <option value=\"POITC|null|Inventory Type|A|U\" title=\"Inventory Type\">Inventory Type";
	$qsOpt .= "\n <option value=\"VMVNA1U|null|Vendor Name|A|U\" title=\"Vendor Name\">Vendor Name";
	$qsOpt .= "\n <option value=\"POVEND|null|Vendor Number|N|\" title=\"Vendor Number\">Vendor Number";
	require 'QuickSearchOption.php';
}
print "\n <table $contentTable> ";
print "\n <tr> ";
$returnValue=OrderBy_Sort("POAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount\">{$sortPoint}Amount</a></th> ";
$returnValue=OrderBy_Sort("POTRDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TransDate\" title=\"Sequence By Transaction Date\">{$sortPoint}Trans<br>Date</a></th> ";
$returnValue=OrderBy_Sort("POITEM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Item\" title=\"Sequence By Item Number\">{$sortPoint}Item Number</a></th> ";
$returnValue=OrderBy_Sort("POIMDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ItemDesc\" title=\"Sequence By Description\">{$sortPoint}Description</a></th> ";
$returnValue=OrderBy_Sort("POWHS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Warehouse\" title=\"Sequence By Warehouse\">{$sortPoint}Whs</a></th> ";
if ($HDPDRL > 0) {
	$returnValue=OrderBy_Sort("POPLT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Plant\" title=\"Sequence By Plant\">{$sortPoint}Plant</a></th> ";
}
$returnValue=OrderBy_Sort("POPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PONumber\" title=\"Sequence By Purchase Order\">{$sortPoint}Purchase<br>Order</a></th> ";
$returnValue=OrderBy_Sort("POQTY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Quantity\" title=\"Sequence By Quantity\">{$sortPoint}Quantity</a></th> ";
$returnValue=OrderBy_Sort("POPCLS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ProdClass\" title=\"Sequence By Product Class\">{$sortPoint}Prod<br>Class</a></th> ";
$returnValue=OrderBy_Sort("POITC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvType\" title=\"Sequence By Inventory Type\">{$sortPoint}Inv<br>Type</a></th> ";
$returnValue=OrderBy_Sort("VMVNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=VendorName\" title=\"Sequence By Vendor Name\">{$sortPoint}Vendor Name</a></th> ";
$returnValue=OrderBy_Sort("POVEND"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=VendorNumber\" title=\"Sequence By Vendor Number\">{$sortPoint}Vendor Number</a></th> ";
print "\n </tr> ";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require 'SetRowClass.php';

	$F_POAMT=Format_Nbr($row['POAMT'], "2", $amtEditCode, "Y", "", "");
	$F_POQTY=Format_Nbr($row['POQTY'], $qtyNbrDec, $qtyEditCode, "Y", "", "");
	$F_POTRDT=Format_Date($row['POTRDT'], "D");

	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDPoReceiptDetail.d2w/ENTRY{$altVarBase}&amp;poNumber=" . urlencode(trim($row['POPO'])) . "&amp;receiptSeq=" . urlencode(trim($row['POSEQ'])) . "\" title=\"View P/O Receipt Detail\">$F_POAMT</a></td> ";
	print "\n <td class=\"coldate\">$F_POTRDT</td> ";
	print "\n <td class=\"colalph\">$row[POITEM]</td> ";
	if ($row['POPOEC'] != "N") {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemInquiry.d2w/DISPLAY{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['POITEM'])) . "\" onclick=\"$inquiryWinVar\" title=\"Item Quickview\">$row[POIMDS]</a></td> ";}
	else                       {print "\n <td class=\"colalph\">$row[POIMDS]</td> ";}
	print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[WHWHNM]\">$row[POWHS]</span></td> ";
	if ($HDPDRL > 0) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[PLNAME]\">$row[POPLT]</span></td> ";}
	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}SelectPO.php{$genericVarBase}&amp;tabID=RECEIPTS&amp;vendorNumber=" . urlencode(trim($row['POVEND'])) . "&amp;purchaseOrderNumber=" . urlencode(trim($row['POPO'])) . "&amp;orderSequence=" . urlencode(trim($row['POSEQ'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Purchase Order\">$row[POPO]</a></td> ";
	print "\n <td class=\"colnmbr\">$F_POQTY</td> ";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PCPCDS]\">$row[POPCLS]</span></td> ";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[ITDESC]\">$row[POITC]</span></td> ";
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber=" . urlencode(trim($row['POVEND'])) . "\" title=\"View Vendor\">$row[VMVNA1]</a></td> ";
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}VendorInquiry.d2w/DISPLAY{$altVarBase}&amp;vendorNumber=" . urlencode(trim($row['POVEND'])) . "\" onclick=\"$inquiryWinVar\" title=\"Vendor Quickview\">$row[POVEND]</a></td> ";
	print "\n </tr> ";
	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "\n </table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print $inquiryhrTagAttr;
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require $inquiryTrailer;
print "\n </body> \n </html>";
?>
