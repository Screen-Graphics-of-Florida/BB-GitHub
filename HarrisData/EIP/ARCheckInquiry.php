<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromDocument       = $_GET['fromDocument'];
$fromDatePaid       = $_GET['fromDatePaid'];
$fromBank           = $_GET['fromBank'];
$fromPayer          = $_GET['fromPayer'];
$fromCustomer       = $_GET['fromCustomer'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "A/R Document Inquiry";
$scriptName     = "ARCheckInquiry.php";
$scriptVarBase  = "{$genericVarBase}{$glDDVarBase}&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;fromDatePaid=" . urlencode(trim($fromDatePaid)) . "&amp;fromBank=" . urlencode(trim($fromBank)) . "&amp;fromPayer=" . urlencode(trim($fromPayer)) . "&amp;fromCustomer=" . urlencode(trim($fromCustomer)) ;
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"),array("IVBLTO","A","Customer"),array("IVISEQ","A",""));
$medIcon        = "Y";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

$BKCURT=RetValue("BKBANK=$fromBank", "HDBANK", "Coalesce(BKCURT,' ')");
$CFCURT=RetValue("BKBANK=$fromBank", "HDBANK inner join HDCFAC on (CFCO#,CFFAC#)=(BKCO,BKFAC)", "Coalesce(CFCURT,' ')");

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	$fromToSearch = "Y";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.$formName.frAmt, 11, 2) ";
	print "\n    && editNum(document.$formName.toAmt, 11, 2) ";
	print "\n    && editFromToOper(document.$formName.frAmt, document.$formName.toAmt, document.$formName.operAmt, 13) ";
	print "\n    && editNum(document.$formName.frInv, 7, 0) ";
	print "\n    && editNum(document.$formName.toInv, 7, 0) ";
	print "\n    && editFromToOper(document.$formName.frInv, document.$formName.toInv, document.$formName.operInv, 7) ";
	print "\n    && editdate(document.$formName.frInvDate) ";
	print "\n    && editdate(document.$formName.toInvDate) ";
	print "\n    && editFromToOper(document.$formName.frInvDate, document.$formName.toInvDate, document.$formName.operInvDate, 'D') ";
	print "\n    && editdate(document.$formName.frDueDate) ";
	print "\n    && editdate(document.$formName.toDueDate) ";
	print "\n    && editFromToOper(document.$formName.frDueDate, document.$formName.toDueDate, document.$formName.operDueDate, 'D') ";
	print "\n    && editNum(document.$formName.frCust, 7, 0) ";
	print "\n    && editNum(document.$formName.toCust, 7, 0) ";
	print "\n    && editFromToOper(document.$formName.frCust, document.$formName.toCust, document.$formName.operCust, 7) ";
	print "\n    && editNum(document.$formName.frOrd, 8, 0) ";
	print "\n    && editNum(document.$formName.toOrd, 8, 0) ";
	print "\n    && editFromToOper(document.$formName.frOrd, document.$formName.toOrd, document.$formName.operOrd, 8) ";
	if ($HDMCRL>0 && $CTPRMC=="Y" && $DifCurCount>0) {
		print "\n    && editNum(document.$formName.frDomAmt, 11, 2) ";
		print "\n    && editNum(document.$formName.toDomAmt, 11, 2) ";
		print "\n    && editFromToOper(document.$formName.frDomAmt, document.$formName.toDomAmt, document.$formName.operDomAmt, 13) ";
	}
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "I";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Amount","frAmt","toAmt","operAmt","opersel_num2_short","N","15","15");
	Build_AdvSrch_Entry("Payment Code","srchPmtCode","","operPmtCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Invoice","frInv","toInv","operInv","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Invoice Date","frInvDate","toInvDate","operInvDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Due Date","frDueDate","toDueDate","operDueDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Customer","frCust","toCust","operCust","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Comment","srchComment","","operComment","opersel_alph_short","A","15","69");
	Build_AdvSrch_Entry("Memo","srchMemo","","operMemo","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Order Number","frOrd","toOrd","operOrd","opersel_num2_short","N","8","8");
	Build_AdvSrch_Entry("Mfg Order","srchMfgOrder","","operMfgOrder","opersel_alph_short","A","9","9");
	if ($HDMCRL>0 && $CRPRMC=="Y" && $BKCURT!=$CFCURT) {Build_AdvSrch_Entry("Domestic Amount","frDomAmt","toDomAmt","operDomAmt","opersel_num2_short","N","15","15");}

	$focusField = "srchInv";
	require_once 'AdvSearchBottom.php';
}

if ($tag == "ORDERBY"){
	if     ($sequence == "Amount")      {$orby = array(array("YPAMT","A","Amount"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"),array("IVBLTO","A","Customer"),array("IVISEQ","A","")) ;}
	elseif ($sequence == "PaymentCode") {$orby = array(array("YPSBCD","A","Payment Code"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"),array("IVBLTO","A","Customer"),array("IVISEQ","A","")) ;}
	elseif ($sequence == "Invoice")     {$orby = array(array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"),array("IVBLTO","A","Customer"),array("IVISEQ","A","")) ;}
	elseif ($sequence == "InvDate")     {$orby = array(array("IVIVDT","A","Invoice Date"),array("IVAINV","A","Invoice"),array("IVBLTO","A","Customer"),array("IVISEQ","A","")) ;}
	elseif ($sequence == "DueDate")     {$orby = array(array("IVDUED","A","Due Date"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"),array("IVBLTO","A","Customer"),array("IVISEQ","A","")) ;}
	elseif ($sequence == "Customer")    {$orby = array(array("IVBLTO","A","Customer"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"),array("IVISEQ","A","")) ;}
	elseif ($sequence == "HasComment")  {$orby = array(array("HAS_YPCMNT","A","Has Comment"),array("IVAINV","A","Invoice")) ;}
	elseif ($sequence == "Memo")        {$orby = array(array("YPMEMO","A","Memo"),array("IVAINV","A","Invoice")) ;}
	elseif ($sequence == "DaysPast")    {$orby = array(array("DAYSPAST","A","Days Past Invoice"),array("IVAINV","A","Invoice")) ;}
	elseif ($sequence == "Terms")       {$orby = array(array("IVTRMS","A","Terms"),array("IVAINV","A","Invoice")) ;}
	elseif ($sequence == "OrderNumber") {$orby = array(array("IVORD","A","Order Number"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"),array("IVBLTO","A","Customer"),array("IVISEQ","A","")) ;}
	elseif ($sequence == "MfgOrder")    {$orby = array(array("IVMORD","A","Mfg Order"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"),array("IVBLTO","A","Customer"),array("IVISEQ","A","")) ;}
	elseif ($sequence == "DomAmount")   {$orby = array(array("YPCAMT","A","Domestic Amount"),array("IVAINV","A","Invoice"),array("IVIVDT","A","Invoice Date"),array("IVBLTO","A","Customer"),array("IVISEQ","A","")) ;}

	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Range_WildCard("YPAMT", "Amount", $_POST['frAmt'], $_POST['toAmt'], "", $_POST['operAmt'], "N");
	$returnValue=Build_WildCard("YPSBCD", "Payment Code", $_POST['srchPmtCode'], "U", $_POST['operPmtCode'], "A");
	$returnValue=Range_WildCard("IVAINV", "Invoice", $_POST['frInv'], $_POST['toInv'], "", $_POST['operInv'], "N");
	$returnValue=Range_WildCard("IVIVDT", "Invoice Date", $_POST['frInvDate'], $_POST['toInvDate'], "", $_POST['operInvDate'], "D");
	$returnValue=Range_WildCard("IVDUED", "Due Date", $_POST['frDueDate'], $_POST['toDueDate'], "", $_POST['operDueDate'], "I");
	$returnValue=Range_WildCard("IVBLTO", "Customer", $_POST['frCust'], $_POST['toCust'], "", $_POST['operCust'], "N");
	$returnValue=Build_WildCard("Upper(YPCMNT)", "Comment", $_POST['srchComment'], "U", $_POST['operComment'], "A");
	$returnValue=Build_WildCard("YPMEMO", "Memo", $_POST['srchMemo'], "U", $_POST['operMemo'], "A");
	$returnValue=Range_WildCard("IVORD", "Order Number", $_POST['frOrd'], $_POST['toOrd'], "", $_POST['operOrd'], "N");
	$returnValue=Build_WildCard("IVMORD", "Mfg Order", $_POST['srchMfgOrder'], "U", $_POST['operMfgOrder'], "A");
	$returnValue=Range_WildCard("YPCAMT", "Domestic Amount", $_POST['frDomAmt'], $_POST['toDomAmt'], "", $_POST['operDomAmt'], "N");

	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

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

$fromDocument=trim($fromDocument);
$SVselectSQL = " (YPBDAT,YPBANK)=($fromDatePaid,$fromBank) and trim(YPCHK) like '$fromDocument' ";
if (is_null($fromPayer)==false) {$SVselectSQL .= " and YPPAYR=$fromPayer ";}
if (is_null($fromCustomer)==false && (is_null($fromPayer) || $fromPayer==0)) {$SVselectSQL .= " and YPBLTO=$fromCustomer ";}

print "\n <table $contentTable> ";
$F_BKBKNM=RetValue("BKBANK=$fromBank", "HDBANK", "Coalesce(BKBKNM,' ')");
if ($HDMCRL>0 && $CRPRMC=="Y" && $BKCURT!="") {$bankCur="Bank $BKCURT Domestic $CFCURT";}
else                                          {$bankCur="";}
Format_Header("Bank", $F_BKBKNM, "$fromBank $bankCur");

Format_Header("Document", $fromDocument, "");

$F_YPBDAT=Format_Date($fromDatePaid, "H");
Format_Header("Date Paid", $F_YPBDAT, "");

if (is_null($fromPayer)==false && $fromPayer>0) {
	$F_PYPYNM=RetValue("PYPAYR=$fromPayer", "ARPYRH", "PYPYNM");
	Format_Header("Payer", $F_PYPYNM, $fromPayer);
}

if (is_null($fromCustomer)==false && (is_null($fromPayer) || $fromPayer==0)) {
	$F_CMCNA1=RetValue("CMCUST=$fromCustomer", "HDCUST", "CMCNA1");
	Format_Header("Customer", $F_CMCNA1, $fromCustomer);
}

$F_DEPOSITAMT=RetValue("$SVselectSQL and (PYTYPE='M' or CPTRNT='C')", "ARYPTD left join ARPYCD on PYPYCD=YPPYCD left join ARPAYT on CPTYPE=PYTYPE", "CHAR(SUM(YPAMT))");
$F_DEPOSITAMT=Format_Nbr($F_DEPOSITAMT,  "2", $amtEditCode, "Y", "", "");
if ($HDMCRL>0 && $CRPRMC=="Y" && $BKCURT!=$CFCURT) {
	$F_DEPOSITDOM=RetValue("$SVselectSQL and (PYTYPE='M' or CPTRNT='C')", "ARYPTD left join ARPYCD on PYPYCD=YPPYCD left join ARPAYT on CPTYPE=PYTYPE", "CHAR(SUM(YPCAMT))");
	$F_DEPOSITDOM=Format_Nbr($F_DEPOSITDOM,  "2", $amtEditCode, "Y", "", "");
	Format_Header("Deposit Amount", $F_DEPOSITAMT, $F_DEPOSITDOM);
} else {
	Format_Header("Deposit Amount", $F_DEPOSITAMT, "");
}

$F_OTHAMT=RetValue("$SVselectSQL and CPTRNT='A'", "ARYPTD left join ARPYCD on PYPYCD=YPPYCD left join ARPAYT on CPTYPE=PYTYPE", "CHAR(SUM(YPAMT))");
$F_OTHAMT=Format_Nbr($F_OTHAMT,  "2", $amtEditCode, "Y", "", "");
if ($HDMCRL>0 && $CRPRMC=="Y" && $BKCURT!=$CFCURT) {
	$F_OTHDOM=RetValue("$SVselectSQL and CPTRNT='A'", "ARYPTD left join ARPYCD on PYPYCD=YPPYCD left join ARPAYT on CPTYPE=PYTYPE", "CHAR(SUM(YPCAMT))");
	$F_OTHDOM=Format_Nbr($F_OTHDOM,  "2", $amtEditCode, "Y", "", "");
	Format_Header("Other Amount", $F_OTHAMT, $F_OTHDOM);
} else {
	Format_Header("Other Amount", $F_OTHAMT, "");
}
print "\n </table> ";

print $inquiryhrTagAttr;

$uv_CustomerName        = "IVBLTO";
$uv_CustomerClassName   = "CMCCLS";
$uv_RegionName          = "CMCRGN";
$uv_BillingLocationName = "CMLOC#";
$uv_SalesmanName        = "CMSLSM";
$uv_WarehouseName       = "CMWH#";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select IVISEQ,IVBLTO,IVAINV,IVIVDT,IVDUED,IVTRMS,IVORD,IVPLT,IVMORD ";
$stmtSQL .= "       ,YPAMT,YPSBCD,YPCMNT,YPMEMO,YPCAMT,YPCURT,YPCURD,YPDSTP,YPCRTE,YPOPER ";
$stmtSQL .= "       ,Case When YPCMNT<>' ' Then 'Y' Else ' ' End HAS_YPCMNT ";
$stmtSQL .= "       ,Days(F_MAKEDATE(YPBDAT))-Days(F_MAKEDATE(IVIVDT)) as DAYSPAST ";
$stmtSQL .= "       ,Coalesce(CMCNA1,' ') as CMCNA1 ";
$stmtSQL .= "       ,Coalesce(PSDESC,' ') as PSDESC ";
$stmtSQL .= "       ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
if ($HDOERL<=0) {
	$stmtSQL .= ",0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
} else {
	$stmtSQL .= ",(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(b.IVAINV,b.IVIVDT,b.IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where HHLIV#=b.IVAINV and HHBLTO=b.IVBLTO) as OEHISTORY " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where IVORD<>0 and HHORD#=IVORD and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
}
if ($HDPDRL<=0) {
	$stmtSQL .= ",0 as MFGORDCOUNT " ;
} else {
	$stmtSQL .= ",(Select Count(*) From HDMOHM Where (OHPLT,OHORD)=(IVPLT,IVMORD)) as MFGORDCOUNT " ;
}
$fileSQL .= " ARYPTD a  ";
$fileSQL .= " inner join HDINVC b on IVISEQ=YPISEQ ";
$fileSQL .= " left join HDCUST on IVBLTO=CMCUST ";
$fileSQL .= " left join ARPYSB on PSSBCD=YPSBCD ";
$fileSQL .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=IVORD and HHLIV#=IVAINV ";
$selectSQL .= $SVselectSQL;

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"YPAMT|null|Amount|N|\" title=\"Amount\">Amount";
	$qsOpt .= "\n <option value=\"YPSBCD|null|Payment Code|A|U\" title=\"Payment Code\">Payment Code";
	$qsOpt .= "\n <option value=\"IVAINV|null|Invoice|N|\" title=\"Invoice\" SELECTED>Invoice";
	$qsOpt .= "\n <option value=\"IVIVDT|DATE|Invoice Date|D|\" title=\"Invoice Date\">Invoice Date";
	$qsOpt .= "\n <option value=\"IVDUED|DATE|Due Date|I|\" title=\"Due Date\">Due Date";
	$qsOpt .= "\n <option value=\"IVBLTO|null|Customer|N|\" title=\"Customer\">Customer";
	$qsOpt .= "\n <option value=\"Upper(YPCMNT)|null|Comment|A|U\" title=\"Comment\">Comment";
	$qsOpt .= "\n <option value=\"YPMEMO|null|Memo|A|U\" title=\"Memo\">Memo";
	$qsOpt .= "\n <option value=\"IVORD|null|Order Number|N|\" title=\"Order Number\">Order Number";
	$qsOpt .= "\n <option value=\"IVMORD|null|Mfg Order|A|U\" title=\"Mfg Order\">Mfg Order";
	if ($HDMCRL>0 && $CRPRMC=="Y" && $BKCURT!=$CFCURT) {
		$qsOpt .= "\n <option value=\"YPCAMT|null|Amount|N|\" title=\"Domestic Amount\">Domestic Amount";
	}
	require 'QuickSearchOption.php';
}

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("YPAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount, Invoice, Invoice Date, Customer\">{$sortPoint}Amount</a></th>";
$returnValue=OrderBy_Sort("YPSBCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PaymentCode\" title=\"Sequence By Payment Code, Invoice, Invoice Date, Customer\">{$sortPoint}Payment Code</a></th>";
$returnValue=OrderBy_Sort("IVAINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\" title=\"Sequence By Invoice, Invoice Date, Customer\">{$sortPoint}Invoice</a></th>";
$returnValue=OrderBy_Sort("IVIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvDate\" title=\"Sequence By Invoice Date, Invoice, Customer\">{$sortPoint}Invoice Date</a></th>";
$returnValue=OrderBy_Sort("IVDUED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DueDate\" title=\"Sequence By Due Date, Invoice, Customer\">{$sortPoint}Due Date</a></th>";
$returnValue=OrderBy_Sort("IVBLTO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Customer\" title=\"Sequence By Customer, Invoice, Invoice Date\">{$sortPoint}Customer</a></th>";
$returnValue=OrderBy_Sort("HAS_YPCMNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=HasComment\" title=\"Sequence By Has Comment, Invoice\">{$sortPoint}Cmt</a></th>";
$returnValue=OrderBy_Sort("YPMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Memo\" title=\"Sequence By Memo, Invoice, Invoice Date\">{$sortPoint}Memo</a></th>";
$returnValue=OrderBy_Sort("DAYSPAST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DaysPast\" title=\"Sequence By Days Past Invoice, Invoice, Invoice Date\">{$sortPoint}Days Past Invoice</a></th>";
$returnValue=OrderBy_Sort("IVTRMS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Terms\" title=\"Sequence By Terms, Invoice, Invoice Date\">{$sortPoint}Terms</a></th>";
$returnValue=OrderBy_Sort("IVORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OrderNumber\" title=\"Sequence By Order Number, Invoice, Invoice Date, Customer\">{$sortPoint}Order Number</a></th>";
$returnValue=OrderBy_Sort("IVMORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=MfgOrder\" title=\"Sequence By Mfg Order, Invoice, Invoice Date, Customer\">{$sortPoint}Mfg Order</a></th>";
if ($HDMCRL>0 && $CRPRMC=="Y" && $BKCURT!=$CFCURT) {
	$returnValue=OrderBy_Sort("YPCAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DomAmount\" title=\"Sequence By Domestic Amount, Invoice, Invoice Date, Customer\">{$sortPoint}Domestic Amount</a></th>";
}
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require 'SetRowClass.php';

	$F_YPAMT=Format_Nbr($row['YPAMT'],  "2", $amtEditCode, "Y", "", "");
	$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
	$F_IVDUED=Format_Date_ISO($row['IVDUED'], "D");
	if ($HDMCRL>0 && $CRPRMC=="Y" && $BKCURT!=$CFCURT) {
		$F_YPCAMT=Format_Nbr($row['YPCAMT'],  "2", $amtEditCode, "Y", "", "");
		$F_DomHover=Format_Domestic_Hover_Info($row['YPCURT'], $row['YPCURD'], $row['YPDSTP'], $row['YPOPER'], $row['YPCRTE']);
	}

	print "\n <tr class=\"$rowClass\"> ";

	print "\n <td class=\"colnmbr\">$F_YPAMT</td> ";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[YPSBCD]\">$row[PSDESC]</span></td> ";
	if ($row['OEINVCOUNT']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['IVAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['IVIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[IVAINV]</a></td> ";}
	else                      {print "\n <td class=\"colnmbr\">$row[IVAINV]</td> ";}
	print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['IVISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_IVIVDT</a></td> ";
	print "\n <td class=\"coldate\">$F_IVDUED</td> ";
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}CustomerInquiry.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "\" onclick=\"$inquiryWinVar\" title=\"Customer Quickview\">$row[IVBLTO]</a></td> ";
	print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[YPCMNT]\">$row[HAS_YPCMNT]</span></td> ";
	print "\n <td class=\"colalph\">$row[YPMEMO]</td> ";
	print "\n <td class=\"colnmbr\">$row[DAYSPAST]</td> ";
	print "\n <td class=\"colalph\">$row[IVTRMS]</td> ";
	if     ($row['OESELECT']>0)  {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['IVORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[IVORD]</a></td> ";}
	elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['IVBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['IVAINV'])) . "&amp;nMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View Customer Order History\">$row[IVORD]</a></td> ";}
	elseif ($row['IVORD']<>0)    {print "\n <td class=\"colnmbr\">$row[IVORD]</td> ";}
	else                         {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
	if ($row['MFGORDCOUNT']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['IVPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['IVMORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[IVMORD]</a></td> ";}
	else                       {print "\n <td class=\"colalph\">$row[IVMORD]</td> ";}
	if ($HDMCRL>0 && $CRPRMC=="Y" && $BKCURT!=$CFCURT) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_YPCAMT</span></td> ";}
	print "\n </tr> ";

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

