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

require_once "ARControl$dataBaseID.php";
require_once "GLControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Accounts Receivable Detail";
$scriptName     = "GLDDDetailAr.php";
$scriptVarBase  = "{$genericVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($glJrnl)) . "&amp;glPer=" . urlencode(trim($glPer)) . "&amp;glDate=" . urlencode(trim($glDate)) . "&amp;glJrnlSeq=" . urlencode(trim($glJrnlSeq)) . "&amp;glJrnlRec=" . urlencode(trim($glJrnlRec)) . "&amp;unpostedRRN=" . urlencode(trim($unpostedRRN)) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDAp=" . urlencode(trim($glDDAp)) . "&amp;glDDFile=" . urlencode(trim($glDDFile));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$backHome       = "glDDReport.d2w/REPORT";
$dftOrderBy = array(array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Bill-To"),array("ARISEQ","A",""));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

$ARDDFL=RetValue("(ARPER,ARDDSQ)=($glPer,$glDDSeq) ", "ARGLDD", "MAX(ARDDFL)");

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	$fromToSearch = "";
	require_once ($headInclude);
	print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}{$GLDDStyleSheet}\"> ";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.$formName.frAmt, 13, 2) ";
	print "\n    && editNum(document.$formName.toAmt, 13, 2) ";
	print "\n    && editFromToOper(document.$formName.frAmt, document.$formName.toAmt, document.$formName.operAmt, 15) ";
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
	if ($ARDDFL == "ARYPTD") {
		print "\n    && editdate(document.Search.frDatePaid) ";
		print "\n    && editdate(document.Search.toDatePaid) ";
		print "\n    && editFromToOper(document.$formName.frDatePaid, document.$formName.toDatePaid, document.$formName.operDatePaid, 'D') ";
	}
	print "\n    && editNum(document.$formName.frLoc, 3, 0) ";
	print "\n    && editNum(document.$formName.toLoc, 3, 0) ";
	print "\n    && editFromToOper(document.$formName.frLoc, document.$formName.toLoc, document.$formName.operLoc, 3) ";
	print "\n    && editNum(document.$formName.frSalesman, 3, 0) ";
	print "\n    && editNum(document.$formName.toSalesman, 3, 0) ";
	print "\n    && editFromToOper(document.$formName.frSalesman, document.$formName.toSalesman, document.$formName.operSalesman, 3) ";
	if ($ARDDFL == "ARYPTD") {
		print "\n    && editNum(document.$formName.frBank, 2, 0) ";
		print "\n    && editNum(document.$formName.toBank, 2, 0) ";
		print "\n    && editFromToOper(document.$formName.frBank, document.$formName.toBank, document.$formName.operBank, 2) ";
		print "\n    && editNum(document.$formName.frBatch, 4, 0) ";
		print "\n    && editNum(document.$formName.toBatch, 4, 0) ";
		print "\n    && editFromToOper(document.$formName.frBatch, document.$formName.toBatch, document.$formName.operBatch, 4) ";
	}
	print "\n    && editNum(document.$formName.frOrd, 8, 0) ";
	print "\n    && editNum(document.$formName.toOrd, 8, 0) ";
	print "\n    && editFromToOper(document.$formName.frOrd, document.$formName.toOrd, document.$formName.operOrd, 8) ";
	if ($HDMCRL>0 && $CTPRMC=="Y" && $DifCurCount>0) {
		print "\n    && editNum(document.$formName.frForeignAmt, 13, 2) ";
		print "\n    && editNum(document.$formName.toForeignAmt, 13, 2) ";
		print "\n    && editFromToOper(document.$formName.frForeignAmt, document.$formName.toForeignAmt, document.$formName.operForeignAmt, 15) ";
	}
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "I";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Amount","frAmt","toAmt","operAmt","opersel_num2_short","N","17","17");
	if ($ARDDFL == "ARYPTD") {Build_AdvSrch_Entry("Payment Code Description","srchPmtCode","","operPmtCode","opersel_alph_short","A","15","30");}
	Build_AdvSrch_Entry("Invoice","frInv","toInv","operInv","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Invoice Date","frInvDate","toInvDate","operInvDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Due Date","frDueDate","toDueDate","operDueDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Customer","frCust","toCust","operCust","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Customer Name","srchCustName","","operCustName","opersel_alph_short","A","15","26");
	if ($ARDDFL == "ARYPTD") {
		Build_AdvSrch_Entry("Date Paid","frDatePaid","toDatePaid","operDatePaid","opersel_num2_short","D","6","6");
		Build_AdvSrch_Entry("Document","srchCheck","","operCheck","opersel_alph_short","A","15","15");
		Build_AdvSrch_Entry("Comment","srchComment","","operComment","opersel_alph_short","A","15","69");
		Build_AdvSrch_Entry("Memo","srchMemo","","operMemo","opersel_alph_short","A","15","15");
	}
	Build_AdvSrch_Entry("Location","frLoc","toLoc","operLoc","opersel_num2_short","N","3","3");
	Build_AdvSrch_Entry("Salesman","frSalesman","toSalesman","operSalesman","opersel_num2_short","N","3","3");
	if ($ARDDFL == "ARYPTD") {
		Build_AdvSrch_Entry("Payer Name","srchPayer","","operPayer","opersel_alph_short","A","15","30");
		Build_AdvSrch_Entry("Bank","frBank","toBank","operBank","opersel_num2_short","N","2","2");
		Build_AdvSrch_Entry("Batch","frBatch","toBatch","operBatch","opersel_num2_short","N","4","4");
	}
	Build_AdvSrch_Entry("Order Number","frOrd","toOrd","operOrd","opersel_num2_short","N","8","8");
	Build_AdvSrch_Entry("Mfg Order","srchMfgOrder","","operMfgOrder","opersel_alph_short","A","9","9");
	if ($HDMCRL>0 && $CTPRMC=="Y" && $DifCurCount>0) {Build_AdvSrch_Entry("Amount","frForeignAmt","toForeignAmt","operForeignAmt","opersel_num2_short","N","17","17");}

	$focusField = "srchAmt";
	require_once 'AdvSearchBottom.php';
}

if ($tag == "ORDERBY"){
	if     ($sequence == "Amount") {$orby = array(array("ARAMT","A","Amount"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "PmtCode") {$orby = array(array("PSDESCU","A","Payment Code"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Invoice") {$orby = array(array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "InvDate") {$orby = array(array("ARIVDT","A","Invoice Date"),array("ARAINV","A","Invoice"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "DueDate") {$orby = array(array("ARDUED","A","Due Date"),array("ARAINV","A","Invoice"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "CustomerName") {$orby = array(array("CMCNA1U","A","Customer Name"),array("ARBLTO","A","Customer"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "DatePaid") {$orby = array(array("ARDTPD","A","Date Paid"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "CheckNumber") {$orby = array(array("ARCHK","A","Document"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Comment") {$orby = array(array("HAS_YPCMNT","A","Comment"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Memo") {$orby = array(array("YPMEMO","A","Memo"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Location") {$orby = array(array("ARLOC","A","Location"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Salesman")     {$orby = array(array("ARSLSM","A","Salesman"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Payer") {$orby = array(array("PYPYNMU","A","Payer"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Bank") {$orby = array(array("ARBANK","A","Bank"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Batch")        {$orby = array(array("YPBCH","A","Batch"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "OrderNumber")  {$orby = array(array("ARORD","A","Order Number"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "MfgOrder")     {$orby = array(array("ARMORD","A","Mfg Order"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "ForeignAmt")   {$orby = array(array("ARFAMT","A","Foreign Amount"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}

	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Range_WildCard ("ARAMT", "Amount", $_POST['frAmt'], $_POST['toAmt'], "", $_POST['operAmt'], "N");
	$returnValue=Build_WildCard ("PSDESCU", "Payment Code Description", $_POST['srchPmtCode'], "U", $_POST['operPmtCode'], "A");
	$returnValue=Range_WildCard ("ARAINV", "Invoice", $_POST['frInv'], $_POST['toInv'], "", $_POST['operInv'], "N");
	$returnValue=Range_WildCard ("ARIVDT", "Invoice Date", $_POST['frInvDate'], $_POST['toInvDate'], "", $_POST['operInvDate'], "D");
	$returnValue=Range_WildCard ("ARDUED", "Due Date", $_POST['frDueDate'], $_POST['toDueDate'], "", $_POST['operDueDate'], "I");
	$returnValue=Range_WildCard ("ARBLTO", "Customer", $_POST['frCust'], $_POST['toCust'], "", $_POST['operCust'], "N");
	$returnValue=Build_WildCard ("CMCNA1U", "Customer Name", $_POST['srchCustName'], "U", $_POST['operCustName'], "A");
	$returnValue=Range_WildCard ("ARDTPD", "Date Paid", $_POST['frDatePaid'], $_POST['toDatePaid'], "", $_POST['operDatePaid'], "D");
	$returnValue=Build_WildCard ("trim(ARCHK)", "Document", $_POST['srchCheck'], "U", $_POST['operCheck'], "A");
	$returnValue=Build_WildCard ("Upper(YPCMNT)", "Comment", $_POST['srchComment'], "U", $_POST['operComment'], "A");
	$returnValue=Build_WildCard ("YPMEMO", "Memo", $_POST['srchMemo'], "U", $_POST['operMemo'], "A");
	$returnValue=Range_WildCard ("ARLOC", "Location", $_POST['frLoc'], $_POST['toLoc'], "", $_POST['operLoc'], "N");
	$returnValue=Range_WildCard ("ARSLSM", "Salesman", $_POST['frSalesman'], $_POST['frSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Build_WildCard ("PYPYNMU", "Payer Name", $_POST['srchPayer'], "U", $_POST['operPayer'], "A");
	$returnValue=Range_WildCard ("ARBANK", "Bank", $_POST['frBank'], $_POST['toBank'], "", $_POST['operBank'], "N");
	$returnValue=Range_WildCard ("YPBCH", "Batch", $_POST['frBatch'], $_POST['toBatch'], "", $_POST['operBatch'], "N");
	$returnValue=Range_WildCard ("ARORD", "Order Number", $_POST['frOrd'], $_POST['toOrd'], "", $_POST['operOrd'], "N");
	$returnValue=Build_WildCard ("ARMORD", "Mfg Order", $_POST['srchMfgOrder'], "U", $_POST['operMfgOrder'], "A");
	$returnValue=Range_WildCard ("ARFAMT", "Foreign Amount", $_POST['frForeignAmt'], $_POST['toForeignAmt'], "", $_POST['operForeignAmt'], "N");

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
	$uv_CompanyName   ="JRCO";
	$uv_FacilityName  ="JRFAC";
	$uv_AccountName   ="JRACC";
	$uv_SubaccountName="JRSUB";
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

$uv_CompanyName        ="ARCO";
$uv_FacilityName       ="ARFAC";
$uv_AccountName        ="ARACCT";
$uv_SubaccountName     ="ARSUB";
$uv_CustomerName       ="ARBLTO";
$uv_CustomerClassName  ="CMCCLS";
$uv_RegionName         ="CMCRGN";
$uv_BillingLocationName="CMLOC#";
$uv_SalesmanName       ="CMSLSM";
$uv_WarehouseName      ="CMWH#";
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
	$stmtSQL .= " SELECT JRREF,JRCO,JRFAC,JRACC,JRSUB,JRAMT,JRDESC  ";
	$stmtSQL .= "       ,JRFCAM,JRCRTE,JROPER,JRFCUR,JRTCUR ";
	$stmtSQL .= "       ,coalesce(CFCFNM,' ') as CFCFNM, coalesce(CFCURT,' ') as CFCURT ";
	$stmtSQL .= "       ,coalesce(CHCHDS,' ') as CHCHDS, coalesce(CHACSG,' ') as CHACSG ";
	$stmtSQL .= "       ,coalesce(FLDESC,' ') as FLDESC,  upper(coalesce(FLDESC,' ')) as FLDESCU ";
	$stmtSQL .= "       ,coalesce(CYDESC,' ') as CYDESC,  upper(coalesce(CYDESC,' ')) as CYDESCU ";
	$fileSQL .= " HDARTR ";
	$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(JRCO,JRFAC) ";
	$fileSQL .= " left join HDCHRT on (CHACCT,CHSUB)=(JRACC,JRSUB) ";
	$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('USAGE',CHACSG) ";
	$fileSQL .= " left join HDCTYP on CYTYPE=CFCURT ";
	$selectSQL .= " RRN(HDARTR)=$unpostedRRN ";
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

	$F_AcctSub=Format_Acct($row['JRACC'], $row['JRSUB'],"N");
	$F_JRAMT=Format_Nbr($row['JRAMT'], "2", $amtEditCode, "Y", "", "");
	$F_CoFac=Format_CoFac($row['JRCO'], $row['JRFAC'],"N");
	IF ($HDMCRL>0 && $CTPRMC=="Y" && $row['JRFCAM']!=0 && $row['JRFCUR']!=$row['JRTCUR']) {
		$F_JRFCAM=Format_Nbr($row['JRFCAM'], "2", $amtEditCode, "Y", "", "");
		$F_DomHover=Format_Domestic_Hover_Info($row['JRFCUR'], $row['JRTCUR'], $row['JRDTSP'], $row['JROPER'], $row['JRCRTE']);
	}
	require 'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colalph\">$row[JRREF]</td> ";
	print "\n <td class=\"colnmbr\">$F_CoFac</td> ";
	print "\n <td class=\"colalph\">$row[CFCFNM]</td> ";
	print "\n <td class=\"colnmbr\">$F_AcctSub</td> ";
	print "\n <td class=\"colalph\">$row[CHCHDS]</td> ";
	print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CHACSG]\">$row[FLDESC]</span></td> ";
	if ($HDMCRL>0 && $CTPRMC=="Y") {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CFCURT]\">$row[CYDESC]</span></td> ";}
	if ($HDMCRL>0 && $CTPRMC=="Y" && $row['JRFCAM']!=0 && $row['JRFCUR']!=$row['JRTCUR']) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"Invoice Amount: $F_JRFCAM $F_DomHover\">$F_JRAMT</span></td> ";}
	else                                                                                  {print "\n <td class=\"colnmbr\">$F_JRAMT</td> ";}
	print "\n <td class=\"colalph\">$row[JRDESC]</td> ";
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
$stmtSQL .= " SELECT ARDDSQ,ARJRNL,ARSBCD,ARCHK ,ARDTPD,ARBANK,ARAINV,ARIVDT,ARDUED,ARPAYR,ARBLTO ";
$stmtSQL .= "       ,ARCUST,ARPAYR,ARCO,ARFAC,ARACCT,ARSUB,ARAMT,ARLOC,ARORD,ARPLT ,ARMORD ";
$stmtSQL .= "       ,ARSLSM,ARISEQ,ARPSEQ ";
if ($HDMCRL>0 && $CTPRMC=="Y") {$stmtSQL .= "       ,ARFAMT,ARCURT,ARCURD,ARDSTP,ARCRTE,AROPER ";}
$stmtSQL .= "       ,coalesce(PYPYNM,' ') as PYPYNM, coalesce(PYPYNMU,' ') as PYPYNMU ";
$stmtSQL .= "       ,coalesce(PSDESC,' ') as PSDESC, coalesce(PSDESCU,' ') as PSDESCU ";
$stmtSQL .= "       ,Coalesce(YPCMNT,' ') as YPCMNT,Case When YPCMNT<>' ' Then 'Y' Else ' ' End HAS_YPCMNT ";
$stmtSQL .= "       ,Coalesce(YPMEMO,' ') as YPMEMO,Coalesce(YPBCH,0) as YPBCH ";
$stmtSQL .= "       ,coalesce(BKBKNM,' ') as BKBKNM ";
$stmtSQL .= "       ,coalesce(CMCNA1,' ') as CMCNA1, coalesce(CMCNA1U,' ') as CMCNA1U ";
$stmtSQL .= "       ,coalesce(LOLNA1,' ') as LOLNA1 ";
$stmtSQL .= "       ,coalesce(SMSNA1,' ') as SMSNA1 ";
$stmtSQL .= "       ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
if ($HDOERL<=0) {
	$stmtSQL .= ",0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
} else {
	$stmtSQL .= ",(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(ARAINV,ARIVDT,ARBLTO) and IVIVCD='C') as OEINVCOUNT " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where HHLIV#=ARAINV and HHBLTO=ARBLTO) as OEHISTORY " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where ARORD<>0 and HHORD#=ARORD and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
}
if ($HDPDRL<=0) {
	$stmtSQL .= ",0 as MFGORDCOUNT " ;
} else {
	$stmtSQL .= ",(Select Count(*) From HDMOHM Where (OHPLT,OHORD)=(ARPLT,ARMORD)) as MFGORDCOUNT " ;
}
$fileSQL .= " ARGLDD ";
$fileSQL .= " left join ARPYRH on PYPAYR=ARPAYR ";
$fileSQL .= " left join ARPYSB on PSSBCD=ARSBCD and ARGLDD.ARRPTS <> 'SLS' ";
$fileSQL .= " left join ARYPTD on (YPISEQ,YPPSEQ)=(ARISEQ,ARPSEQ) ";
$fileSQL .= " left join HDINVC on IVISEQ=ARISEQ ";
$fileSQL .= " left join HDBANK on BKBANK=ARBANK ";
$fileSQL .= " left join HDCUST on CMCUST=ARCUST ";
$fileSQL .= " left join HDLCTN on LOLOC#=ARLOC ";
$fileSQL .= " left join HDSLSM on SMSLSM=ARSLSM";
$fileSQL .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=ARORD and HHLIV#=ARAINV ";
$selectSQL .= " (ARPER,ARDDSQ)=($glPer,$glDDSeq) ";
require 'stmtSQLSelect.php';
$stmtSQL .= "  ORDER BY $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$DifCurCount=RetValue("($selectSQL) and ARCURT<>ARCURD and ARFAMT<>0", $fileSQL, "Char(Count(*))");

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"ARAMT|null|Amount|N|\" title=\"Amount\">Amount";
	if ($ARDDFL == "ARYPTD") {
		$qsOpt .= "\n <option value=\"PSDESCU|null|Payment Code Description|A|U\" title=\"Payment Code Description\">Payment Code Description";
	}
	$qsOpt .= "\n <option value=\"ARAINV|null|Invoice|N|\" title=\"Invoice\" SELECTED>Invoice";
	$qsOpt .= "\n <option value=\"ARIVDT|DATE|Invoice Date|D|\" title=\"Invoice Date\">Invoice Date";
	$qsOpt .= "\n <option value=\"ARDUED|DATE|Due Date|I|\" title=\"Due Date\">Due Date";
	$qsOpt .= "\n <option value=\"ARBLTO|null|Customer|N|\" title=\"Customer\">Customer";
	$qsOpt .= "\n <option value=\"CMCNA1U|null|Customer Name|A|U\" title=\"Customer Name\">Customer Name";
	if ($ARDDFL == "ARYPTD") {
		$qsOpt .= "\n <option value=\"ARDTPD|DATE|Date Paid|D|\" title=\"Date Paid\">Date Paid";
		$qsOpt .= "\n <option value=\"trim(ARCHK)|null|Document|A|U\" title=\"Document\">Document";
		$qsOpt .= "\n <option value=\"Upper(YPCMNT)|null|Comment|A|U\" title=\"Comment\">Comment";
		$qsOpt .= "\n <option value=\"YPMEMO|null|Memo|A|U\" title=\"Memo\">Memo";
	}
	$qsOpt .= "\n <option value=\"ARLOC|null|Location|N|\" title=\"Location\">Location";
	$qsOpt .= "\n <option value=\"ARSLSM|null|Salesman|N|\" title=\"Salesman\">Salesman";
	if ($ARDDFL == "ARYPTD") {
		$qsOpt .= "\n <option value=\"PYPYNMU|null|Payer Name|A|U\" title=\"Payer Name\">Payer Name";
		$qsOpt .= "\n <option value=\"ARBANK|null|Bank|N|\" title=\"Bank\">Bank";
		$qsOpt .= "\n <option value=\"YPBCH|null|Batch|N|\" title=\"Batch\">Batch";
	}
	$qsOpt .= "\n <option value=\"ARORD|null|Order Number|N|\" title=\"Order Number\">Order Number";
	$qsOpt .= "\n <option value=\"ARMORD|null|Mfg Order|A|U\" title=\"Mfg Order\">Mfg Order";
	if ($HDMCRL>0 && $CTPRMC=="Y" && $DifCurCount>0) {$qsOpt .= "\n <option value=\"ARFAMT|null|Foreign Amount|N|\" title=\"Foreign Amount\">Foreign Amount";}

	require 'QuickSearchOption.php';
}
print "\n <table $contentTable> ";

print "\n <tr> ";
$returnValue=OrderBy_Sort("ARAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount, Invoice, Invoice Date, Customer\">{$sortPoint}Amount</a></th> ";
if ($ARDDFL == "ARYPTD") {
	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtCode\" title=\"Sequence By Payment Code, Invoice, Invoice Date, Customer\">{$sortPoint}Payment Code</a></th>";
}
$returnValue=OrderBy_Sort("ARAINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Invoice\" title=\"Sequence By Invoice, Invoice Date, Customer\">{$sortPoint}Invoice</a></th>";
$returnValue=OrderBy_Sort("ARIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvDate\" title=\"Sequence By Invoice Date, Invoice, Customer\">{$sortPoint}Invoice Date</a></th>";
$returnValue=OrderBy_Sort("ARDUED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DueDate\" title=\"Sequence By Due Date, Invoice, Customer\">{$sortPoint}Due Date</a></th>";
$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CustomerName\" title=\"Sequence By Customer Name, Customer, Invoice, Invoice Date\">{$sortPoint}Customer Name</a></th>";
if ($ARDDFL == "ARYPTD") {
	$returnValue=OrderBy_Sort("ARDTPD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DatePaid\" title=\"Sequence By Date Paid, Invoice, Invoice Date, Customer\">{$sortPoint}Date Paid</a></th>";
	$returnValue=OrderBy_Sort("ARCHK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CheckNumber\" title=\"Sequence By Document, Invoice, Invoice Date, Customer\">{$sortPoint}Document</a></th>";
	$returnValue=OrderBy_Sort("HAS_YPCMNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Comment\" title=\"Sequence By Comment\">{$sortPoint}Cmt</a></th> ";
	$returnValue=OrderBy_Sort("YPMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Memo\" title=\"Sequence By Memo\">{$sortPoint}Memo</a></th> ";
}
$returnValue=OrderBy_Sort("ARLOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Location\" title=\"Sequence By Location, Invoice, Invoice Date, Customer\">{$sortPoint}Loc</a></th>";
$returnValue=OrderBy_Sort("ARSLSM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Salesman\" title=\"Sequence By Salesman\">{$sortPoint}Salesman</a></th> ";
if ($ARDDFL == "ARYPTD") {
	$returnValue=OrderBy_Sort("PYPYNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Payer\" title=\"Sequence By Payer, Invoice, Invoice Date, Customer\">{$sortPoint}Payer</a></th>";
	$returnValue=OrderBy_Sort("ARBANK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Bank\" title=\"Sequence By Bank, Invoice, Invoice Date, Customer\">{$sortPoint}Bank</a></th>";
	$returnValue=OrderBy_Sort("YPBCH"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Batch\" title=\"Sequence By Batch\">{$sortPoint}Batch</a></th> ";
}
$returnValue=OrderBy_Sort("ARORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OrderNumber\" title=\"Sequence By Order Number, Order Date, Invoice, Invoice Date, Customer\">{$sortPoint}Order Number</a></th>";
$returnValue=OrderBy_Sort("ARMORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=MfgOrder\" title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th> ";
if ($HDMCRL>0 && $CTPRMC=="Y" && $DifCurCount>0) {
	$returnValue=OrderBy_Sort("ARFAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ForeignAmt\" title=\"Sequence By Foreign Amount\">{$sortPoint}Foreign Amount</a></th> ";
}
print "\n </tr> ";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require 'SetRowClass.php';

	$F_ARAMT=Format_Nbr($row['ARAMT'], "2", $amtEditCode, "Y", "", "");
	$F_ARIVDT=Format_Date($row['ARIVDT'], "D");
	$F_ARDUED=Format_Date_ISO($row['ARDUED'], "D");
	$F_ARDTPD=Format_Date($row['ARDTPD'], "D");
	if ($HDMCRL>0 && $CTPRMC=="Y" && $DifCurCount>0 && $row['ARFAMT']!=0 && $row['ARCURT']!=$row['ARCURD']) {
		$F_ARFAMT=Format_Nbr($row['ARFAMT'],  "2", $amtEditCode, "Y", "", "");
		$F_DomHover=Format_Domestic_Hover_Info($row['ARCURT'], $row['ARCURD'], $row['ARDSTP'], $row['AROPER'], $row['ARCRTE']);
	}

	print "\n <tr class=\"$rowClass\"> ";
	if     ($row['ARDDSQ'] != 0 && $ARDDFL=="HDARDS") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}GLDDArDist.php{$scriptVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($row['ARJRNL'])) . "&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;locationNumber=" . urlencode(trim($row['ARLOC'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDFile=" . urlencode(trim($ARDDFL)) . "&amp;invoiceSequence=" . urlencode(trim($row['ARISEQ'])) . "&amp;paymentSequence=" . urlencode(trim($row['ARPSEQ'])) . "\" title=\"View Invoice Detail - Distribution\">$F_ARAMT</a></td> ";}
	elseif ($row['ARDDSQ'] != 0 && $ARDDFL=="HDINVC") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}GLDDArDist.php{$scriptVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($row['ARJRNL'])) . "&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;locationNumber=" . urlencode(trim($row['ARLOC'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDFile=" . urlencode(trim($ARDDFL)) . "&amp;invoiceSequence=" . urlencode(trim($row['ARISEQ'])) . "&amp;paymentSequence=" . urlencode(trim($row['ARPSEQ'])) . "\" title=\"View Invoice Detail - Revaluation\">$F_ARAMT</a></td> ";}
	elseif ($row['ARDDSQ'] != 0 && $ARDDFL=="ARYPTD") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}GLDDArPymt.php{$scriptVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($row['ARJRNL'])) . "&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;locationNumber=" . urlencode(trim($row['ARLOC'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;invoiceSequence=" . urlencode(trim($row['ARISEQ'])) . "&amp;paymentSequence=" . urlencode(trim($row['ARPSEQ'])) . "\" title=\"View Invoice Detail - Payment\">$F_ARAMT</a></td> ";}
	else                                              {print "\n <td class=\"colnmbr\">$F_ARAMT</td> ";}
	if ($ARDDFL == "ARYPTD") {
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[ARSBCD]\">$row[PSDESC]</span></td> ";
	}
	if ($row['OEINVCOUNT']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['ARAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['ARIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[ARAINV]</a></td> ";}
	else                      {print "\n <td class=\"colnmbr\">$row[ARAINV]</td> ";}
	print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$scriptVarBase}{$glDDVarBase}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($backHome)) . "&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['ARISEQ'])) . "&amp;glJrnl=" . urlencode(trim($row['ARJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View A/R Invoice\">$F_ARIVDT</a></td> ";
	print "\n <td class=\"coldate\">$F_ARDUED</td> ";
	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[ARBLTO]]\">$row[CMCNA1]</a></td> ";
	if ($ARDDFL == "ARYPTD") {
		print "\n <td class=\"coldate\">$F_ARDTPD</td> ";
		if (trim($row['ARCHK']) != "" && $row['ARBANK']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}ARCheckInquiry.php{$scriptVarBase}&amp;tag=REPORT&amp;fromDocument=" . urlencode(trim($row['ARCHK'])) . "&amp;fromDatePaid=" . urlencode(trim($row['ARDTPD'])) . "&amp;fromBank=" . urlencode(trim($row['ARBANK'])) . "&amp;fromPayer=" . urlencode(trim($row['ARPAYR'])) . "&amp;fromCustomer=" . urlencode(trim($row['ARBLTO'])) . "\" onclick=\"$drillDownWinVar\" title=\"A/R Document Quickview\">$row[ARCHK]</a></td> ";}
		else {print "\n <td class=\"colalph\">$row[ARCHK]</td> ";}
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[YPCMNT]\">$row[HAS_YPCMNT]</span></td> ";
		print "\n <td class=\"colalph\">$row[YPMEMO]</td> ";
	}
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['ARLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location [$row[LOLNA1]]\">$row[ARLOC]</a></td> ";
	print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[SMSNA1]\">$row[ARSLSM]</span></td> ";
	if ($ARDDFL == "ARYPTD") {
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[ARPAYR]\">$row[PYPYNM]</span></td> ";
		if ($row['ARBANK'] != 0) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[BKBKNM]\">$row[ARBANK]</span></td> ";}
		else                     {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashBatchSelect.php{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['YPBCH'])) . "&amp;fromBatchDate=" . urlencode(trim($row['ARDTPD'])) . "&amp;fromBatchBank=" . urlencode(trim($row['ARBANK'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Batch\">$row[YPBCH]</a></td> ";
	}
	if     ($row['OESELECT']>0)  {print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['ARORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[ARORD]</a></td> ";}
	elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['ARBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['ARAINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[ARORD]</a></td> ";}
	else                         {print "\n <td class=\"colnmbr\">$row[ARORD]</td> ";}
	if ($row['MFGORDCOUNT']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['ARPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['ARMORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[ARMORD]</a></td> ";}
	else                       {print "\n <td class=\"colalph\">$row[ARMORD]</td> ";}
	if ($HDMCRL>0 && $CTPRMC=="Y" && $DifCurCount>0) {
		if ($row['ARFAMT']!=0 && $row['ARCURT']!=$row['ARCURD']) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_ARFAMT</span></td> ";}
		else                                                     {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
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
