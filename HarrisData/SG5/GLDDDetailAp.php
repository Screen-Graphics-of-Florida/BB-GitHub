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

require_once "APControl$dataBaseID.php";
require_once "GLControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Accounts Payable Detail";
$scriptName     = "GLDDDetailAp.php";
$scriptVarBase  = "{$genericVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($glJrnl)) . "&amp;glPer=" . urlencode(trim($glPer)) . "&amp;glDate=" . urlencode(trim($glDate)) . "&amp;glJrnlSeq=" . urlencode(trim($glJrnlSeq)) . "&amp;glJrnlRec=" . urlencode(trim($glJrnlRec)) . "&amp;unpostedRRN=" . urlencode(trim($unpostedRRN)) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDAp=" . urlencode(trim($glDDAp)) . "&amp;glDDFile=" . urlencode(trim($glDDFile));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$backHome       = "glDDReport.d2w/REPORT";
$dftOrderBy = array(array("VMVNA1U","A","Vendor Name"),array("APVOU","A","Voucher"),array("APLINE","A",""));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

$APDDFL=RetValue("(APPER#,APDDSQ)=($glPer,$glDDSeq)", "APGLDD", "MAX(APDDFL)");

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
	print "\n    && editdate(document.Search.srchInvDate) ";
	print "\n    && editNum(document.Search.srchPeriod, 4, 0) ";
	print "\n    && editNum(document.Search.srchPurchase, 8, 0) ";
	if ($APDDFL == "APPAID") {
		print "\n    && editNum(document.Search.srchBank, 2, 0) ";
		print "\n    && editNum(document.Search.srchCheckNumber, 9, 0) ";
		print "\n    && editdate(document.Search.srchCheckDate) ";
	}
	print "\n    && editNum(document.Search.srchVoucher, 9, 0) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "I";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Amount","srchAmt","","operAmt","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Invoice","srchInv","","operInv","opersel_alph_short","A","20","20");
	Build_AdvSrch_Entry("Invoice Date","srchInvDate","","operInvDate","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("Vendor Name","srchVendName","","operVendName","opersel_alph_short","A","26","26");
	Build_AdvSrch_Entry("Distribution Period","srchPeriod","","operPeriod","opersel_num_short","DP","4","4");
	Build_AdvSrch_Entry("Purchase Order","srchPurchase","","operPurchase","opersel_num_short","N","8","8");
	Build_AdvSrch_Entry("Memo","srchMemo","","operMemo","opersel_alph_short","A","16","16");
	Build_AdvSrch_Entry("Voucher","srchVoucher","","operVoucher","opersel_num_short","N","9","9");
	if ($APDDFL == "APPAID") {
		Build_AdvSrch_Entry("Bank","srchBank","","operBank","opersel_num_short","N","2","2");
		Build_AdvSrch_Entry("Check","srchCheckNumber","","operCheckNumber","opersel_num_short","N","9","9");
		Build_AdvSrch_Entry("Check Date","srchCheckDate","","operCheckDate","opersel_num_short","D","6","6");
		Build_AdvSrch_Entry("Check Code","srchCheckCode","","operCheckCode","opersel_alph_short","A","1","1");
	}

	$focusField = "srchAmt";
	require_once 'AdvSearchBottom.php';
}

if ($tag == "ORDERBY"){
	if     ($sequence == "Amount")      {$orby = array(array("APAMT","A","Amount"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "Invoice")     {$orby = array(array("APINV","A","Invoice"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "InvDate")     {$orby = array(array("APINVD","A","Invoice Date"),array("APINV","A","Invoice"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "VendorName")  {$orby = array(array("VMVNA1U","A","Vendor Name"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "DistPd")      {$orby = array(array("APDSTD","A","Distribution Period"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "PONumber")    {$orby = array(array("APPO","A","Purchase Order"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "Memo")        {$orby = array(array("APMEMO","A","Memo"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "Voucher")     {$orby = array(array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "Bank")        {$orby = array(array("APBANK","A","Bank"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "CheckNumber") {$orby = array(array("APCHK","A","Check"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "CheckDate")   {$orby = array(array("APCHKD","A","Check Date"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "CheckCode")   {$orby = array(array("APCKCD","A","Check Code"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}

	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard ("APAMT", "Amount", $_POST['srchAmt'], "", $_POST['operAmt'], "N");
	$returnValue=Build_WildCard ("APINV#", "Invoice", $_POST['srchInv'], "U", $_POST['operInv'], "A");
	$returnValue=Build_WildCard ("APINVD", "Invoice Date", $_POST['srchInvDate'], "", $_POST['operInvDate'], "D");
	$returnValue=Build_WildCard ("VMVNA1U", "Vendor Name", $_POST['srchVendName'], "U", $_POST['operVendName'], "A");
	$returnValue=Build_WildCard ("APDSTD", "Distribution Period", $_POST['srchPeriod'], "", $_POST['operPeriod'], "DP");
	$returnValue=Build_WildCard ("APPO#", "Purchase Order", $_POST['srchPurchase'], "", $_POST['operPurchase'], "N");
	$returnValue=Build_WildCard ("APMEMO", "Memo", $_POST['srchMemo'], "U", $_POST['operMemo'], "A");
	$returnValue=Build_WildCard ("APVOU#", "Voucher", $_POST['srchVoucher'], "", $_POST['operVoucher'], "N");
	$returnValue=Build_WildCard ("APBANK", "Bank", $_POST['srchBank'], "", $_POST['operBank'], "N");
	$returnValue=Build_WildCard ("APCHK#", "Check", $_POST['srchCheckNumber'], "", $_POST['operCheckNumber'], "N");
	$returnValue=Build_WildCard ("APCHKD", "Check Date", $_POST['srchCheckDate'], "", $_POST['operCheckDate'], "D");
	$returnValue=Build_WildCard ("APCKCD", "Check Code", $_POST['srchCheckCode'], "U", $_POST['operCheckCode'], "A");

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
	$uv_CompanyName   ="JACO";
	$uv_FacilityName  ="JAFAC";
	$uv_AccountName   ="JAACC";
	$uv_SubaccountName="JASUB";
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

$uv_CompanyName   ="APCO";
$uv_FacilityName  ="APFAC";
$uv_AccountName   ="APACCT";
$uv_SubaccountName="APSUB";
$uv_VendorName    ="APVEND";
$uv_VendorTypeName="VMVTYP";
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
	$stmtSQL .= " SELECT JAREF,JACO,JAFAC,JAACC,JASUB,JAAMT,JADESC ";
	$stmtSQL .= "       ,JAFCAM,JADTSP,JACRTE,JAOPER,JAFCUR,JATCUR ";
	$stmtSQL .= "       ,coalesce(CFCFNM,' ') as CFCFNM, coalesce(CFCURT,' ') as CFCURT ";
	$stmtSQL .= "       ,coalesce(CHCHDS,' ') as CHCHDS, coalesce(CHACSG,' ') as CHACSG ";
	$stmtSQL .= "       ,coalesce(FLDESC,' ') as FLDESC,  upper(coalesce(FLDESC,' ')) as FLDESCU ";
	$stmtSQL .= "       ,coalesce(CYDESC,' ') as CYDESC,  upper(coalesce(CYDESC,' ')) as CYDESCU ";
	$fileSQL .= " HDAPTR ";
	$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(JACO,JAFAC) ";
	$fileSQL .= " left join HDCHRT on (CHACCT,CHSUB)=(JAACC,JASUB) ";
	$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('USAGE',CHACSG) ";
	$fileSQL .= " left join HDCTYP on CYTYPE=CFCURT ";
	$selectSQL .= " RRN(HDAPTR)=$unpostedRRN ";
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

	$F_AcctSub=Format_Acct($row['JAACC'], $row['JASUB'],"N");
	$F_JAAMT=Format_Nbr($row['JAAMT'], "2", $amtEditCode, "Y", "", "");
	$F_CoFac=Format_CoFac($row['JACO'], $row['JAFAC'],"N");
	IF ($HDMCRL>0 && $CTPRMC=="Y" && $row['JAFCAM']!=0 && $row['JAFCUR']!=$row['JATCUR']) {
		$F_JAFCAM=Format_Nbr($row['JAFCAM'], "2", $amtEditCode, "Y", "", "");
		$F_DomHover=Format_Domestic_Hover_Info($row['JAFCUR'], $row['JATCUR'], $row['JADTSP'], $row['JAOPER'], $row['JACRTE']);
	}
	require 'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colalph\">$row[JAREF]</td> ";
	print "\n <td class=\"colnmbr\">$F_CoFac</td> ";
	print "\n <td class=\"colalph\">$row[CFCFNM]</td> ";
	print "\n <td class=\"colnmbr\">$F_AcctSub</td> ";
	print "\n <td class=\"colalph\">$row[CHCHDS]</td> ";
	print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CHACSG]\">$row[FLDESC]</span></td> ";
	if ($HDMCRL>0 && $CTPRMC=="Y") {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CFCURT]\">$row[CYDESC]</span></td> ";}
	if ($HDMCRL>0 && $CTPRMC=="Y" && $row['JAFCAM']!=0 && $row['JAFCUR']!=$row['JATCUR']) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"Invoice Amount: $F_JAFCAM $F_DomHover\">$F_JAAMT</span></td> ";}
	else                                                                                  {print "\n <td class=\"colnmbr\">$F_JAAMT</td> ";}
	print "\n <td class=\"colalph\">$row[JADESC]</td> ";
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
	if ($HDMCRL>0 && $CTPRMC=="Y" && $row['TDFCAM']!=0 && $row['TDFCUR']!=$row['TDTCUR']) {
		$F_TDFCAM=Format_Nbr($row['TDFCAM'], "2", $amtEditCode, "Y", "", "");
		$F_DomHover=Format_Domestic_Hover_Info($row['TDFCUR'], $row['TDTCUR'], $row['TDDTSP'], $row['TDOPER'], $row['TDCRTE']);
	}

	require 'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colalph\">$row[TDREF]</td> ";
	print "\n <td class=\"colnmbr\">$F_CoFac</td> ";
	print "\n <td class=\"colalph\">$coFacName</td> ";
	print "\n <td class=\"colnmbr\">$F_AcctSub</td> ";
	print "\n <td class=\"colalph\">$acctName</td> ";
	print "\n <td class=\"colalph\">$currencyUnit</td> ";
	if ($HDMCRL>0 && $CTPRMC=="Y") {print "\n <td class=\"colalph\">$currencyType</td> ";}
	if ($HDMCRL>0 && $CTPRMC=="Y" && $row['TDFCAM']!=0 && $row['TDFCUR']!=$row['TDTCUR']) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"Invoice Amount: $F_TDFCAM $F_DomHover\">$F_TDAMT</span></td> ";}
	else                                                                                  {print "\n <td class=\"colnmbr\">$F_TDAMT</td> ";}
	print "\n <td class=\"colalph\">$row[TDDESC]</td> ";
	print "\n </tr> ";
	print "\n </table> ";
}

// Drill Down transactions ****************************************
require 'stmtSQLClear.php';
$uv_Sql=$uv_Sql2;
$appendUserView="";
$appendWildCard="";
$stmtSQL .= " SELECT APDDSQ,APJRNL,APAMT,APBANK,APVOU# as APVOU,APLINE,APVEND ";
$stmtSQL .= "       ,APCHK# as APCHK,APCHKD,APCKCD,APINV# as APINV,APINVD ";
$stmtSQL .= "       ,APMEMO,APPO# as APPO,APDSTD,APFAMT,APFCUR,APTCUR ";
$stmtSQL .= "       ,coalesce(FLDESC,' ') as FLDESC ";
$stmtSQL .= "       ,coalesce(VMVNA1,' ') as VMVNA1, coalesce(VMVNA1U,' ') as VMVNA1U ";
$stmtSQL .= "       ,coalesce(BKBKNM,' ') as BKBKNM ";
if ($HDPORL<=0) {$stmtSQL .= ",0 as POHISTORY " ;}
else            {$stmtSQL .= ",(Select Count(*) From POPOHH Where PHPO=APPO# and PHRTOV=APVEND) as POHISTORY " ;}
$fileSQL .= " APGLDD ";
$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('APCHECKCD',APCKCD) ";
$fileSQL .= " left join HDVEND on VMVEND=APVEND ";
$fileSQL .= " left join HDBANK on BKBANK=APBANK ";
$selectSQL .= " (APPER#,APDDSQ)=($glPer,$glDDSeq) ";
require 'stmtSQLSelect.php';
$stmtSQL .= "  ORDER BY $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"APAMT|null|Amount|N|\" title=\"Amount\">Amount";
	$qsOpt .= "\n <option value=\"APINV#|null|Invoice|A|U\" title=\"Invoice\" SELECTED>Invoice";
	$qsOpt .= "\n <option value=\"APINVD|DATE|Invoice Date|D|\" title=\"Invoice Date\">Invoice Date";
	$qsOpt .= "\n <option value=\"VMVNA1U|null|Vendor Name|A|U\" title=\"Vendor Name\">Vendor Name";
	$qsOpt .= "\n <option value=\"APDSTD|null|Distribution Period|DP|\" title=\"Distribution Period\">Distribution Period";
	$qsOpt .= "\n <option value=\"APPO#|null|Purchase Order|N|\" title=\"Purchase Order\">Purchase Order";
	$qsOpt .= "\n <option value=\"APMEMO|null|Memo|A|U\" title=\"Memo\">Memo";
	$qsOpt .= "\n <option value=\"APVOU#|null|Voucher|N|\" title=\"Voucher\">Voucher";
	$qsOpt .= "\n <option value=\"APBANK|null|Bank|N|\" title=\"Bank\">Bank";
	$qsOpt .= "\n <option value=\"APCHK#|null|Check|N|\" title=\"Check\">Check";
	$qsOpt .= "\n <option value=\"APCHKD|DATE|Check Date|D|\" title=\"Check Date\">Check Date";
	$qsOpt .= "\n <option value=\"APCKCD|null|Check Code|A|U\" title=\"Check Code\">Check Code";
	require 'QuickSearchOption.php';
}
print "\n <table $contentTable> ";

print "\n <tr> ";
$returnValue=OrderBy_Sort("APAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount, Voucher\">{$sortPoint}Amount</a></th> ";
$returnValue=OrderBy_Sort("APINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\" title=\"Sequence By Invoice, Voucher\">{$sortPoint}Invoice</a></th> ";
$returnValue=OrderBy_Sort("APINVD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvDate\" title=\"Sequence By Invoice Date, Invoice, Voucher\">{$sortPoint}Invoice Date</a></th> ";
$returnValue=OrderBy_Sort("VMVNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=VendorName\" title=\"Sequence By Vendor Name, Voucher\">{$sortPoint}Vendor Name</a></th> ";
$returnValue=OrderBy_Sort("APDSTD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DistPd\" title=\"Sequence By Distribution Period, Voucher\">{$sortPoint}Dist Period</a></th> ";
$returnValue=OrderBy_Sort("APPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PONumber\" title=\"Sequence By Purchase Order, Voucher\">{$sortPoint}Purchase Order</a></th> ";
$returnValue=OrderBy_Sort("APMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Memo\" title=\"Sequence By Memo, Voucher\">{$sortPoint}Memo</a></th> ";
$returnValue=OrderBy_Sort("APVOU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Voucher\" title=\"Sequence By Voucher\">{$sortPoint}Voucher</a></th> ";
if ($APDDFL == "APPAID") {
	$returnValue=OrderBy_Sort("APBANK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Bank\" title=\"Sequence By Bank, Voucher\">{$sortPoint}Bank</a></th> ";
	$returnValue=OrderBy_Sort("APCHK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CheckNumber\" title=\"Sequence By Check, Voucher\">{$sortPoint}Check</a></th> ";
	$returnValue=OrderBy_Sort("APCHKD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CheckDate\" title=\"Sequence By Check Date, Voucher\">{$sortPoint}Check Date</a></th> ";
	$returnValue=OrderBy_Sort("APCKCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CheckCode\" title=\"Sequence By Check Code, Voucher\">{$sortPoint}Check Code</a></th> ";
}
print "\n </tr> ";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require 'SetRowClass.php';

	$F_APAMT=Format_Nbr($row['APAMT'], "2", $amtEditCode, "Y", "", "");
	$F_APDSTD=PeriodFromCYP($row['APDSTD']);
	$F_APINVD=Format_Date($row['APINVD'], "D");
	$F_APCHKD=Format_Date($row['APCHKD'], "D");

	print "\n <tr class=\"$rowClass\"> ";
	if     ($row['APDDSQ'] != 0 && $row['APDDFL'] == "APDIST") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDApDist.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;vendorNumber=" . urlencode(trim($row['APVEND'])) . "&amp;bankNumber=" . urlencode(trim($row['APBANK'])) . "&amp;voucherNumber=" . urlencode(trim($row['APVOU'])) . "&amp;lineNumber=" . urlencode(trim($row['APLINE'])) . "&amp;checkNumber=" . urlencode(trim($row['APCHK'])) . "&amp;checkDate=" . urlencode(trim($row['APCHKD'])) . "&amp;checkCode=" . urlencode(trim($row['APCKCD'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "\" title=\"View A/P Invoice Detail - Distribution\">$F_APAMT</a></td> ";}
	elseif ($row['APDDSQ'] != 0 && $row['APDDFL'] == "APPAID") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDApPymt.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;vendorNumber=" . urlencode(trim($row['APVEND'])) . "&amp;bankNumber=" . urlencode(trim($row['APBANK'])) . "&amp;voucherNumber=" . urlencode(trim($row['APVOU'])) . "&amp;lineNumber=" . urlencode(trim($row['APLINE'])) . "&amp;checkNumber=" . urlencode(trim($row['APCHK'])) . "&amp;checkDate=" . urlencode(trim($row['APCHKD'])) . "&amp;checkCode=" . urlencode(trim($row['APCKCD'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "\" title=\"View A/P Invoice Detail - Payments\">$F_APAMT</a></td> ";}
	else                                                       {print "\n <td class=\"colnmbr\">$F_APAMT</td> ";}
	print "\n <td class=\"colalph\">$row[APINV]</td> ";
	print "\n <td class=\"coldate\">$F_APINVD</td> ";
	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorInquiry.d2w/DISPLAY{$altVarBase}&amp;vendorNumber=" . urlencode(trim($row['APVEND'])) . "\" onclick=\"$inquiryWinVar\" title=\"Vendor Quickview\">$row[VMVNA1]</a></td> ";
	print "\n <td class=\"colnmbr\">$F_APDSTD</td> ";
	if ($row['POHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=215&amp;fKey1=PHRTOV&amp;fVal1=" . urlencode(trim($row['APVEND'])) . "&amp;fKey2=PHPO&amp;fVal2=" . urlencode(trim($row['APPO'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Vendor Order History\">$row[APPO]</a></td> ";}
	else                     {print "\n <td class=\"colnmbr\">$row[APPO]</td> ";}
	print "\n <td class=\"colalph\">$row[APMEMO]</td> ";
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}APInvoice.d2w/ENTRY{$altVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;vendorNumber=" . urlencode(trim($row['APVEND'])) . "&amp;voucherNumber=" . urlencode(trim($row['APVOU'])) . "&amp;ddReport=" . urlencode(trim($ddReport)) . "&amp;glJrnl=" . urlencode(trim($row['APJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;ddDescr=" . urlencode(trim($ddDescr)) . "&amp;ddCompany=" . urlencode(trim($ddCompany)) . "&amp;ddFacility=" . urlencode(trim($ddFacility)) . "&amp;noMenu=Y\" title=\"View A/P Invoice\">$row[APVOU]</a></td> ";
	if ($APDDFL == "APPAID") {
		if ($row['APBANK'] != 0) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[BKBKNM]\">$row[APBANK]</span></td> ";}
		else                     {print "\n <td class=\"colnmbr\">$row[APBANK]</td> ";}
		if ($row['APCHK'] != 0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}APCheckInquiry.d2w/ENTRY{$altVarBase}&amp;checkNumber=" . urlencode(trim($row['APCHK'])) . "&amp;checkDate=" . urlencode(trim($row['APCHKD'])) . "&amp;bankNumber=" . urlencode(trim($row['APBANK'])) . "\" onclick=\"$drillDownWinVar\" title=\"A/P Check Quickview\">$row[APCHK]</a></td> ";}
		else                    {print "\n <td class=\"colnmbr\">$row[APCHK]</td> ";}
		print "\n <td class=\"coldate\">$F_APCHKD</td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row['FLDESC']) . "\">$row[APCKCD]</span></td> ";
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
