<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'GetGLDDParm.php';

$customerNumber  = $_GET['customerNumber'];
$glDDSeq         = $_GET['glDDSeq'];
$glDDFile        = $_GET['glDDFile'];
$glJrnl          = $_GET['glJrnl'];
$invoiceSequence = $_GET['invoiceSequence'];
$locationNumber  = $_GET['locationNumber'];
$paymentSequence = $_GET['paymentSequence'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'WildCardAcctInclude.php';

$page_title     = "Invoice Detail - Distribution";
$scriptName     = "GLDDArDist.php";
$scriptVarBase  = "{$genericVarBase}{$glDDVarBase}&amp;customerNumber=" . urlencode(trim($customerNumber)) . "&amp;locationNumber=" . urlencode(trim($locationNumber)) . "&amp;glJrnl=" . urlencode(trim($glJrnl)) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDFile=" . urlencode(trim($glDDFile)) . "&amp;invoiceSequence=" . urlencode(trim($invoiceSequence)) . "&amp;paymentSequence=" . urlencode(trim($paymentSequence));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
if ($fromType=="")            {$dftOrderBy = array(array("TYPEDESCU","A","Type"),array("ARCO","A","Company"),array("ARFAC","A","Facility"),array("ARACCT","A","Acount"),array("ARSUB","A",""));}

if (CustomerUserView($profileHandle, $dataBaseID, $customerNumber, "Y") == "N") {
	require 'UserViewErrorInclude.php';
	exit;
}

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

	require_once 'CheckEnterSearch.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.Search.srchAmt, 13, 2) ";
	print "\n    && editNum(document.Search.srchCo, 2, 0) ";
	print "\n    && editNum(document.Search.srchFac, 4, 0) ";
	print "\n    && editNum(document.Search.srchAcct, 4, 0) ";
	print "\n    && editNum(document.Search.srchSub, 4, 0) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "I";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Amount","srchAmt","","operAmt","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Type Description","srchType","","operType","opersel_alph_short","A","30","50");
	Build_AdvSrch_Entry("Company","srchCo","","operCo","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Facility","srchFac","","operFac","opersel_num_short","N","4","4");
	Build_AdvSrch_Entry("Company/Facility Name","srchCoFacName","","operCoFacName","opersel_alph_short","A","30","30");

	print "\n <tr><td class=\"dsphdr\">Account</td> ";
	$operNbr="operAcct";
	print "\n     <td>"; require "opersel_num_short.php"; print "\n </td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"srchAcct\" size=\"4\" maxlength=\"4\"> ";
	print "\n                             <input type=\"text\" name=\"srchSub\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n </tr> ";

	Build_AdvSrch_Entry("Account Description","srchAcctDesc","","operAcctDesc","opersel_alph_short","A","30","30");
	$focusField = "srchType";
	require_once 'AdvSearchBottom.php';
}

if ($tag == "ORDERBY"){
	if     ($sequence == "Amount") {$orby = array(array("ARAMT","A","Amount"),array("ARCO","A","Company/Facility"),array("ARFAC","A",""),array("ARACCT","A","Account"),array("ARSUB","A","")) ;}
	elseif ($sequence == "Type") {$orby = array(array("TYPEDESCU","A","Type"),array("ARCO","A","Company/Facility"),array("ARFAC","A",""),array("ARACCT","A","Account"),array("ARSUB","A","")) ;}
	elseif ($sequence == "CoFac") {$orby = array(array("ARCO","A","Company/Facility"),array("ARFAC","A",""),array("ARACCT","A","Account"),array("ARSUB","A","")) ;}
	elseif ($sequence == "CoFacName") {$orby = array(array("CFCFNMU","A","Name"),array("ARCO","A","Company/Facility"),array("ARFAC","A",""),array("ARACCT","A","Account"),array("ARSUB","A","")) ;}
	elseif ($sequence == "Account") {$orby = array(array("ARACCT","A","Account"),array("ARSUB","A",""),array("ARCO","A","Company/Facility"),array("ARFAC","A","")) ;}
	elseif ($sequence == "AccountDesc") {$orby = array(array("CHCHDSU","A","Description"),array("ARACCT","A","Account"),array("ARSUB","A",""),array("ARCO","A","Company/Facility"),array("ARFAC","A","")) ;}
	elseif ($sequence == "ForeignAmt")   {$orby = array(array("ARFAMT","A","Foreign Amount"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}

	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard ("ARAMT", "Amount", $_POST['srchAmt'], "", $_POST['operAmt'], "N");
	$returnValue=Build_WildCard ("upper(d.FLDESC)", "Type Description", $_POST['srchType'], "U", $_POST['operType'], "A");
	$returnValue=Build_WildCard ("ARCO", "Company", $_POST['srchCo'], "", $_POST['operCo'], "N");
	$returnValue=Build_WildCard ("ARFAC", "Facility", $_POST['srchFac'], "", $_POST['operFac'], "N");
	$returnValue=Build_WildCard ("CFCFNMU", "Company/Facility Name", $_POST['srchCoFacName'], "U", $_POST['operCoFacName'], "A");
	$returnValue=Build_WildCard_Acct ("ARACCT", "ARSUB", "Account", $_POST['srchAcct'], $_POST['srchSub'], $_POST['operAcct']);
	$returnValue=Build_WildCard ("CHCHDSU", "Account Description", $_POST['srchAcctDesc'], "U", $_POST['operAcctDesc'], "A");

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
print "\n         <table $contentTable><colgroup><col width=\"80%\"><col width=\"15%\">";
print "\n             <tr><td><h1>$page_title</h1></td>";
if ($formatToPrint != "Y") {
	print "\n <td class=\"toolbar\">";
	if ($ddReport!="") {print "\n <a href=\"{$homeURL}{$cGIPath}GLDDReport.d2w/REPORT{$altVarBase}{$glDDVarBase}&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . "\" title=\"Return To Drill Down Report\">$portalHome</a> ";}
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	print "\n </td>";
}
print "\n </tr> ";

$uv_CompanyName="ARCO";
$uv_FacilityName="ARFAC";
$uv_AccountName="ARACCT";
$uv_SubaccountName="ARSUB";
$uv_CustomerName="ARBLTO";
$uv_CustomerClassName="CMCCLS";
$uv_RegionName="CMCRGN";
$uv_BillingLocationName="CMLOC#";
$uv_SalesmanName="CMSLSM";
$uv_WarehouseName="CMWH#";
require 'UserView.php';

if ($uv_Sql != "") {print "\n <tr><td><h3> You may not be authorized to view some transactions </h3></td></tr> ";}
print "\n </table> ";

print $inquiryhrTagAttr;

// Customer Header ************************************************
print "\n <table $contentTable> ";
print "\n     <colgroup><col width=\"30%\"><col width=\"10%\"><col width=\"30%\"><col width=\"10%\"> ";
print "\n     <tr valign=\"top\"> ";
print "\n         <td> ";
require 'stmtSQLClear.php';
$appendUserView="N";
$appendWildCard="N";
$stmtSQL .= " Select CMCNA1,CMCNA2,CMCNA3,CMCNA4,CMCCTY,CMST,CMZIP ";
$fileSQL .= " HDCUST ";
$selectSQL .= " CMCUST=$customerNumber ";
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

print "\n <table $contentTable> ";
print "\n     <tr><th class=\"colhdr\">Customer</th></tr> ";
print "\n     <tr><td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($customerNumber)) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$customerNumber]\">$row[CMCNA1]</a></td></tr> ";
print "\n     <tr><td class=\"colalph\">$row[CMCNA2]</td></tr> ";
if (trim($row['CMCNA3']) != "") {print "\n     <tr><td class=\"colalph\">$row[CMCNA3]</td></tr> ";}
if (trim($row['CMCNA4']) != "") {print "\n     <tr><td class=\"colalph\">$row[CMCNA4]</td></tr> ";}
if (trim($row['CMCCTY']) != "" || trim($row['CMST']) != "" || $row['CMZIP'] >0) {print "\n     <tr><td class=\"colalph\">$row[CMCCTY], $row[CMST] $row[CMZIP]</td></tr> ";}
print "\n </table> ";
print "\n </td> ";

print "\n <td>&nbsp;</td> ";

// Location Header ************************************************
print "\n <td> ";
require 'stmtSQLClear.php';
$appendUserView="N";
$appendWildCard="N";
$stmtSQL .= " Select LOLOC# as LOLOC,LOLNA1,LOLNA2,LOLNA3,LOLNA4,LOLCTY,LOST,LOZIP ";
$fileSQL .= " HDLCTN ";
$selectSQL .= " LOLOC#=$locationNumber ";
require 'stmtSQLSelect.php';
$stmtSQL .= "  order by LOLOC#  ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

print "\n <table $contentTable> ";
print "\n     <tr><th class=\"colhdr\">Location</th></tr> ";
print "\n     <tr><td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['LOLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location [$row[LOLOC]]\">$row[LOLNA1]</a></td></tr> ";
print "\n     <tr><td class=\"colalph\">$row[LOLNA2]</td></tr> ";
if (trim($row['LOLNA3']) != "") {print "\n     <tr><td class=\"colalph\">$row[LOLNA3]</td></tr> ";}
if (trim($row['LOLNA4']) != "") {print "\n     <tr><td class=\"colalph\">$row[LOLNA4]</td></tr> ";}
print "\n     <tr><td class=\"colalph\">$row[LOLCTY], $row[LOST] $row[LOZIP]</td></tr> ";
print "\n </table> ";

print "\n </td>";

print "\n <td>&nbsp;</td> ";
print "\n </tr> ";
print "\n </table> ";

// Invoice Information ******************************************************
require 'stmtSQLClear.php';
$appendUserView="N";
$appendWildCard="N";
$stmtSQL .= " SELECT Distinct ARDDSQ,ARISEQ,ARPSEQ,ARAINV,ARIVDT,ARDUED,ARSLSM,ARORD,ARPLT,ARMORD,ARBLTO ";
$stmtSQL .= "                ,Coalesce(SMSNA1,' ') as SMSNA1 ";
$stmtSQL .= "                ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
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
$fileSQL .= " left join HDINVC on IVISEQ=ARISEQ ";
$fileSQL .= " left join HDSLSM on SMSLSM=ARSLSM ";
$fileSQL .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=ARORD and HHLIV#=ARAINV ";
$selectSQL .= " (ARDDSQ,ARISEQ,ARPSEQ)=($glDDSeq,$invoiceSequence,$paymentSequence) ";
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

print "\n <table $contentTable> ";
print "\n     <tr><th class=\"colhdr\">Invoice</th> ";
print "\n         <th class=\"colhdr\">Invoice Date</th> ";
print "\n         <th class=\"colhdr\">Due Date</th> ";
print "\n         <th class=\"colhdr\">Salesman</th> ";
print "\n         <th class=\"colhdr\">Order Number</th> ";
print "\n         <th class=\"colhdr\">Mfg Order</th> ";
print "\n     </tr> ";

require 'SetRowClass.php';

$F_ARIVDT=Format_Date($row['ARIVDT'], "D");
$F_ARDUED=Format_Date_ISO($row['ARDUED'], "D");

print "\n     <tr class=\"$rowClass\"> ";
if ($row['OEINVCOUNT']>0) {print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['ARAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['ARIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[ARAINV]</a></td> ";}
else                      {print "\n     <td class=\"colnmbr\">$row[ARAINV]</td> ";}
print "\n         <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$scriptVarBase}{$glDDVarBase}&amp;tag=REPORT&amp;glJrnl=" . urlencode(trim($row['ARJRNL'])) . "&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['ARISEQ'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDFile=" . urlencode(trim($glDDFile)) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View A/R Invoice\">$F_ARIVDT</a></td> ";
print "\n         <td class=\"coldate\">$F_ARDUED</td> ";
print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[SMSNA1]\">$row[ARSLSM]</span></td> ";
if     ($row['OESELECT']>0)  {print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['ARORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[ARORD]</a></td> ";}
elseif ($row['OEHISTORY']>0) {print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['ARBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['ARAINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[ARORD]</a></td> ";}
else                         {print "\n     <td class=\"colnmbr\">$row[ARORD]</td> ";}
if ($row['MFGORDCOUNT']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['ARPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['ARMORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[ARMORD]</a></td> ";}
else                       {print "\n <td class=\"colalph\">$row[ARMORD]</td> ";}
print "\n     </tr> ";
print "\n </table> ";

// Distribution Information ************************************************
require 'stmtSQLClear.php';
$appendUserView="";
$appendWildCard="";
$stmtSQL .= " SELECT ARCO,ARFAC,ARACCT,ARSUB,ARAMT ";
if ($HDMCRL>0 && $CRPRMC=="Y") {$stmtSQL .= "       ,ARFAMT,ARCURT,ARCURD,ARDSTP,ARCRTE,AROPER ";}
$stmtSQL .= "       ,coalesce(CFCFNM,' ') as CFCFNM, coalesce(CFCFNMU,' ') as CFCFNMU ";
$stmtSQL .= "       ,coalesce(CHCHDS,' ') as CHCHDS, coalesce(CHCHDSU,' ') as CHCHDSU ";
$stmtSQL .= "       ,coalesce(d.FLDESC,' ') as TYPEDESC, upper(coalesce(d.FLDESC,' ')) as TYPEDESCU ";
$fileSQL .= " ARGLDD ";
$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(ARCO,ARFAC) ";
$fileSQL .= " left join HDCHRT on (CHACCT,CHSUB)=(ARACCT,ARSUB) ";
$fileSQL .= " left join SYFLAG d on (d.FLTYPE,trim(ARARTY))=('ARDISTTYPE',trim(d.FLVALU)) ";
$selectSQL .= " (ARDDFL,ARISEQ,ARPSEQ)=('$glDDFile',$invoiceSequence,$paymentSequence) ";
require 'stmtSQLSelect.php';
$stmtSQL .= "  ORDER BY $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$DifCurCount=RetValue("($selectSQL) and ARCURT<>ARCURD and ARFAMT<>0", $fileSQL, "Char(Count(*))");
if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"ARAMT|null|Amount|N|\" title=\"Amount\">Amount";
	$qsOpt .= "\n <option value=\"ARCO|null|Company|N|\" title=\"Company\">Company";
	$qsOpt .= "\n <option value=\"ARFAC|null|Facility|N|\" title=\"Facility\">Facility";
	$qsOpt .= "\n <option value=\"CFCFNMU|null|Company/Facility Name|A|U\" title=\"Company/Facility Name\">Company/Facility Name";
	$qsOpt .= "\n <option value=\"ARACCT|null|Account Number|N|\" title=\"Account Number\">Account Number";
	$qsOpt .= "\n <option value=\"ARSUB|null|Subaccount Number|N|\" title=\"Subaccount Number\">Subaccount Number";
	$qsOpt .= "\n <option value=\"CHCHDSU|null|Account Description|A|U\" title=\"Account Description\" SELECTED>Account Description";
	require 'QuickSearchOption.php';
}
print "\n <table $contentTable> ";
print "\n     <tr> ";
$returnValue=OrderBy_Sort("ARAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Amount\"       title=\"Sequence By Amount, Company/Facility, Account\">{$sortPoint}Amount</a></th> ";
$returnValue=OrderBy_Sort("TYPEDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Type\"        title=\"Sequence By Type, Company/Facility, Account\">{$sortPoint}Type</a></th> ";
$returnValue=OrderBy_Sort("ARCO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CoFac\"       title=\"Sequence By Company/Facility, Account\">{$sortPoint}Co/Fac</a></th> ";
$returnValue=OrderBy_Sort("CFCFNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CoFacName\"   title=\"Sequence By Name, Company/Facility, Account\">{$sortPoint}Name</a></th> ";
$returnValue=OrderBy_Sort("ARACCT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Account\"     title=\"Sequence By Account, Company/Facility\">{$sortPoint}Account</a></th> ";
$returnValue=OrderBy_Sort("CHCHDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=AccountDesc\" title=\"Sequence By Description, Account, Company/Facility\">{$sortPoint}Description</a></th> ";
if ($HDMCRL>0 && $CRPRMC=="Y" && $DifCurCount>0) {
	$returnValue=OrderBy_Sort("ARFAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=ForeignAmt\" title=\"Sequence By Foreign Amount\">{$sortPoint}Foreign Amount</a></th> ";
}
print "\n </tr> ";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require 'SetRowClass.php';

	$F_AcctSub=Format_Acct($row['ARACCT'], $row['ARSUB'],"N");
	$F_ARAMT=Format_Nbr($row['ARAMT'],"2", $amtEditCode, "Y", "", "");
	$F_CoFac=Format_CoFac($row['ARCO'], $row['ARFAC'],"N");
	if ($HDMCRL>0 && $CRPRMC=="Y" && $DifCurCount>0 && $row['ARFAMT']!=0 && $row['ARCURT']!=$row['ARCURD']) {
		$F_ARFAMT=Format_Nbr($row['ARFAMT'],  "2", $amtEditCode, "Y", "", "");
		$F_DomHover=Format_Domestic_Hover_Info($row['ARCURT'], $row['ARCURD'], $row['ARDSTP'], $row['AROPER'], $row['ARCRTE']);
	}

	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colnmbr\">$F_ARAMT</td> ";
	print "\n     <td class=\"colalph\">$row[TYPEDESC]</td> ";
	print "\n     <td class=\"colnmbr\">$F_CoFac</td> ";
	print "\n     <td class=\"colalph\">$row[CFCFNM]</td> ";
	print "\n     <td class=\"colnmbr\">$F_AcctSub</td> ";
	print "\n     <td class=\"colalph\">$row[CHCHDS]</td> ";
	if ($HDMCRL>0 && $CRPRMC=="Y" && $DifCurCount>0) {
		if ($row['ARFAMT']!=0 && $row['ARCURT']!=$row['ARCURD']) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_ARFAMT</span></td> ";}
		else                                                     {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
	}
	print "\n </tr> ";
	$startRow ++;
	$rowCount ++;
}
print "\n </table> ";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print $inquiryhrTagAttr;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($inquiryTrailer);
print "\n </body> \n </html>";
?>
