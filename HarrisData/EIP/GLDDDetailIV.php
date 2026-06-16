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

$page_title     = "Inventory Detail";
$scriptName     = "GLDDDetailIV.php";
$scriptVarBase  = "{$genericVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($glJrnl)) . "&amp;glPer=" . urlencode(trim($glPer)) . "&amp;glDate=" . urlencode(trim($glDate)) . "&amp;glJrnlSeq=" . urlencode(trim($glJrnlSeq)) . "&amp;glJrnlRec=" . urlencode(trim($glJrnlRec)) . "&amp;unpostedRRN=" . urlencode(trim($unpostedRRN)) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDAp=" . urlencode(trim($glDDAp)) . "&amp;glDDFile=" . urlencode(trim($glDDFile));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$backHome       = "glDDReport.d2w/REPORT";
$dftOrderBy = array(array("IVTRDT","D","Transaction Date"),array("IVITEM","A","Item"));

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
	if ($glDDFile == "HDDTRN") {print "\n    && editNum(document.Search.srchWhs, 3, 0) ";}
	if ($HDPDRL > 0)           {print "\n    && editNum(document.Search.srchPlt, 3, 0) ";}
	print "\n    && editdate(document.Search.srchTranDate) ";
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
	if ($glDDFile == "HDDTRN") {Build_AdvSrch_Entry("Warehouse","srchWhs","","operWhs","opersel_num_short","N","3","3");}
	if ($HDPDRL > 0) {
		Build_AdvSrch_Entry("Plant","srchPlt","","operPlt","opersel_num_short","N","3","3");
		Build_AdvSrch_Entry("Mfg Order","srchMfgOrder","","operMfgOrder","opersel_alph_short","A","9","9");
	}
	if ($glDDFile == "HDDTRN") {
		Build_AdvSrch_Entry("Product Class","srchProdClass","","operProdClass","opersel_alph_short","A","4","4");
		Build_AdvSrch_Entry("Inventory Type","srchInvType","","operInvType","opersel_alph_short","A","4","4");
	}

	$focusField = "srchAmt";
	require_once 'AdvSearchBottom.php';
}

if ($tag == "ORDERBY"){
	if     ($sequence == "Amount")    {$orby = array(array("IVAMT","A","Amount")) ;}
	elseif ($sequence == "TransDate") {$orby = array(array("IVTRDT","A","Transaction Date")) ;}
	elseif ($sequence == "Item")      {$orby = array(array("IVITEM","A","Item")) ;}
	elseif ($sequence == "ItemDesc")  {$orby = array(array("IMIMDS","A","Description")) ;}
	elseif ($sequence == "Warehouse") {$orby = array(array("IVWHS","A","Warehouse")) ;}
	elseif ($sequence == "Plant")     {$orby = array(array("IVPLT","A","Plant")) ;}
	elseif ($sequence == "MfgOrder")  {$orby = array(array("IVMORD","A","Mfg Order")) ;}
	elseif ($sequence == "Quantity")  {$orby = array(array("IVQTY","A","Quantity")) ;}
	elseif ($sequence == "ProdClass") {$orby = array(array("IVPCLS","A","Product Class")) ;}
	elseif ($sequence == "InvType")   {$orby = array(array("IVITC","A","Inventory Type")) ;}

	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard ("IVAMT", "Amount", $_POST['srchAmt'], "", $_POST['operAmt'], "N");
	$returnValue=Build_WildCard ("IVTRDT", "Transaction Date", $_POST['srchTranDate'], "", $_POST['operTranDate'], "D");
	$returnValue=Build_WildCard ("IVITEM", "Item Number", $_POST['srchItem'], "U", $_POST['operItem'], "A");
	$returnValue=Build_WildCard ("IMIMDSU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard ("IVWHS", "Warehouse", $_POST['srchWhs'], "", $_POST['operWhs'], "N");
	$returnValue=Build_WildCard ("IVPLT", "Plant", $_POST['srchPlt'], "", $_POST['operPlt'], "N");
	$returnValue=Build_WildCard ("IVMORD", "Mfg Order", $_POST['srchMfgOrder'], "U", $_POST['operMfgOrder'], "A");
	$returnValue=Build_WildCard ("IVPCLS", "Product Class", $_POST['srchProdClass'], "U", $_POST['operProdClass'], "A");
	$returnValue=Build_WildCard ("IVITC", "Inventory Type", $_POST['srchInvType'], "U", $_POST['operInvType'], "A");

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
	$uv_CompanyName   ="JICO";
	$uv_FacilityName  ="JIFAC";
	$uv_AccountName   ="JIACC";
	$uv_SubaccountName="JISUB";
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

$uv_CompanyName   ="IVCO";
$uv_FacilityName  ="IVFAC";
$uv_AccountName   ="IVACCT";
$uv_SubaccountName="IVSUB";
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
	$stmtSQL .= " SELECT JIREF,JICO,JIFAC,JIACC,JISUB,JIAMT,JIDESC ";
	$stmtSQL .= "       ,coalesce(CFCFNM,' ') as CFCFNM, coalesce(CFCURT,' ') as CFCURT ";
	$stmtSQL .= "       ,coalesce(CHCHDS,' ') as CHCHDS, coalesce(CHACSG,' ') as CHACSG ";
	$stmtSQL .= "       ,coalesce(FLDESC,' ') as FLDESC,  upper(coalesce(FLDESC,' ')) as FLDESCU ";
	$stmtSQL .= "       ,coalesce(CYDESC,' ') as CYDESC,  upper(coalesce(CYDESC,' ')) as CYDESCU ";
	$fileSQL .= " HDIVTR ";
	$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(JICO,JIFAC) ";
	$fileSQL .= " left join HDCHRT on (CHACCT,CHSUB)=(JIACC,JISUB) ";
	$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('USAGE',CHACSG) ";
	$fileSQL .= " left join HDCTYP on CYTYPE=CFCURT ";
	$selectSQL .= " RRN(HDIVTR)=$unpostedRRN ";
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

	$F_AcctSub=Format_Acct($row['JIACC'], $row['JISUB'],"N");
	$F_JIAMT=Format_Nbr($row['JIAMT'], "2", $amtEditCode, "Y", "", "");
	$F_CoFac=Format_CoFac($row['JICO'], $row['JIFAC'],"N");
	require 'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colalph\">$row[JIREF]</td> ";
	print "\n <td class=\"colnmbr\">$F_CoFac</td> ";
	print "\n <td class=\"colalph\">$row[CFCFNM]</td> ";
	print "\n <td class=\"colnmbr\">$F_AcctSub</td> ";
	print "\n <td class=\"colalph\">$row[CHCHDS]</td> ";
	print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CHACSG]\">$row[FLDESC]</span></td> ";
	if ($HDMCRL>0 && $CTPRMC=="Y") {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CFCURT]\">$row[CYDESC]</span></td> ";}
	print "\n <td class=\"colnmbr\">$F_JIAMT</td> ";
	print "\n <td class=\"colalph\">$row[JIDESC]</td> ";
	print "\n </tr> ";
	print "\n </table> ";
} else {
	// Posted Journal ****************************************
	require 'stmtSQLClear.php';
	$uv_Sql=$uv_Sql1;
	$appendUserView="";
	$appendWildCard="N";
	$stmtSQL .= " SELECT TDPER# as TDPER,TDCO,TDFAC,TDACCT,TDSUB,TDREF,TDAMT,TDDESC ";
	$stmtSQL .= "       ,TDDTSP,TDFCAM,TDCRTE,TDOPER,TDFCUR,TDTCUR ";
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
$stmtSQL .= " SELECT IVAMT,IVQTY,IVDSEQ,IVOSEQ,IVTRDT,IVWHS,IVPCLS,IVITC ";
$stmtSQL .= "       ,IVITEM,IVPLT,IVMORD ";
$stmtSQL .= "       ,coalesce(WHWHNM,' ') as WHWHNM ";
$stmtSQL .= "       ,coalesce(PLNAME,' ') as PLNAME ";
$stmtSQL .= "       ,coalesce(PCPCDS,' ') as PCPCDS ";
$stmtSQL .= "       ,coalesce(ITDESC,' ') as ITDESC ";
$stmtSQL .= "       ,coalesce(IMIMDS,' ') as IMIMDS, coalesce(IMIMDSU,' ') as IMIMDSU ";
if ($HDPDRL == 0) {$stmtSQL .= "  ,0 as MFGHISTORY ";}
else              {$stmtSQL .= "  ,(Select Count(*) From HDMOHM Where (OHPLT,OHORD)=(IVPLT,IVMORD)) as MFGHISTORY ";}
$fileSQL .= " IVGLDD ";
$fileSQL .= " left join HDWHSM on WHWHS =IVWHS ";
$fileSQL .= " left join HDPLNT on PLPLNT=IVPLT ";
$fileSQL .= " left join HDPCLS on PCPCLS=IVPCLS ";
$fileSQL .= " left join HDITYP on ITITC =IVITC ";
$fileSQL .= " left join HDIMST on IVITEM=IMITEM ";
$selectSQL .= " (IVPER#,IVDDSQ)=($glPer,$glDDSeq) ";
require 'stmtSQLSelect.php';
$stmtSQL .= "  ORDER BY $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"IVAMT|null|Amount|N|\" title=\"Amount\">Amount";
	$qsOpt .= "\n <option value=\"IVTRDT|DATE|Transaction Date|D|\" title=\"Transaction Date\">Transaction Date";
	$qsOpt .= "\n <option value=\"IVITEM|null|Item Number|A|U\" title=\"Item Number\" SELECTED>Item Number";
	$qsOpt .= "\n <option value=\"IMIMDSU|null|Description|A|U\" title=\"Description\">Description";
	if ($glDDFile == "HDDTRN") {$qsOpt .= "\n <option value=\"IVWHS|null|Warehouse|N|\" title=\"Warehouse\">Warehouse";}
	if ($HDPDRL > 0) {$qsOpt .= "\n <option value=\"IVPLT|null|Plant|N|\" title=\"Plant\">Plant";}
	$qsOpt .= "\n <option value=\"IVMORD|null|Mfg Order|A|U\" title=\"Mfg Order\">Mfg Order";
	$qsOpt .= "\n <option value=\"IVPCLS|null|Product Class|A|U\" title=\"Product Class\">Product Class";
	$qsOpt .= "\n <option value=\"IVITC|null|Inventory Type|A|U\" title=\"Inventory Type\">Inventory Type";
	require 'QuickSearchOption.php';
}
print "\n <table $contentTable> ";

print "\n <tr> ";
$returnValue=OrderBy_Sort("IVAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount\">{$sortPoint}Amount</a></th> ";
$returnValue=OrderBy_Sort("IVTRDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TransDate\" title=\"Sequence By Transaction Date\">{$sortPoint}Trans<br>Date</a></th> ";
$returnValue=OrderBy_Sort("IVITEM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Item\" title=\"Sequence By Item Number\">{$sortPoint}Item Number</a></th> ";
$returnValue=OrderBy_Sort("IMIMDS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ItemDesc\" title=\"Sequence By Description\">{$sortPoint}Description</a></th> ";
if ($glDDFile == "HDDTRN") {
	$returnValue=OrderBy_Sort("IVWHS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Warehouse\" title=\"Sequence By Warehouse\">{$sortPoint}Whs</a></th> ";
}
if ($HDPDRL > 0) {
	$returnValue=OrderBy_Sort("IVPLT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Plant\" title=\"Sequence By Plant\">{$sortPoint}Plant</a></th> ";
	$returnValue=OrderBy_Sort("IVMORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=MfgOrder\" title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th> ";
}
if ($glDDFile == "HDDTRN") {
	$returnValue=OrderBy_Sort("IVQTY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Quantity\" title=\"Sequence By Quantity\">{$sortPoint}Quantity</a></th> ";
	$returnValue=OrderBy_Sort("IVPCLS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ProdClass\" title=\"Sequence By Product Class\">{$sortPoint}Prod<br>Class</a></th> ";
	$returnValue=OrderBy_Sort("IVITC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvType\" title=\"Sequence By Inventory Type\">{$sortPoint}Inv<br>Type</a></th> ";
}
print "\n </tr> ";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require 'SetRowClass.php';

	$F_IVAMT=Format_Nbr($row['IVAMT'], "2", $amtEditCode, "Y", "", "");
	$F_IVQTY=Format_Nbr($row['IVQTY'], $qtyNbrDec, $qtyEditCode, "Y", "", "");
	$F_IVTRDT=Format_Date($row['IVTRDT'], "D");

	print "\n <tr class=\"$rowClass\"> ";
	if ($row['IVOSEQ'] != 0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDIvOrigTrans.d2w/ENTRY{$altVarBase}&amp;origTransSequence=" . urlencode(trim($row['IVOSEQ'])) . "\" title=\"View Transaction\">$F_IVAMT</a></td> ";}
	else                     {print "\n <td class=\"colnmbr\">$F_IVAMT</td> ";}
	print "\n <td class=\"coldate\">$F_IVTRDT</td> ";
	if (trim($row['IMIMDS']) != "") {
		print "\n <td class=\"colalph\">$row[IVITEM]</td> ";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemInquiry.d2w/DISPLAY{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['IVITEM'])) . "\" onclick=\"$inquiryWinVar\" title=\"Item Quickview\">$row[IMIMDS]</a></td> ";
	} else {
		print "\n <td class=\"colalph\">$row[IVITEM]</td> ";
		print "\n <td class=\"colalph\">&nbsp;</td> ";
	}
	if ($glDDFile == "HDDTRN") {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[WHWHNM]\">$row[IVWHS]</span></td> ";}
	if ($HDPDRL > 0) {
		print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[PLNAME]\">$row[IVPLT]</span></td> ";
		if ($row['MFGHISTORY'] > 0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['IVPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['IVMORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[IVMORD]</a></td> ";}
		else                        {print "\n <td class=\"colalph\">&nbsp;</td> ";}
	}
	if ($glDDFile == "HDDTRN") {
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}TransactionHistoryInquiry.d2w/DISPLAY{$altVarBase}&amp;origTransSequence=" . urlencode(trim($row['IVOSEQ'])) . "&amp;transSequence=" . urlencode(trim($row['IVDSEQ'])) . "\" onclick=\"$inquiryWinVar\" title=\"Transaction Quickview\">$F_IVQTY</a></td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PCPCDS]\">$row[IVPCLS]</span></td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[ITDESC]\">$row[IVITC]</span></td> ";
	}
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
