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

$page_title     = "Fixed Assets Detail";
$scriptName     = "GLDDDetailFa.php";
$scriptVarBase  = "{$genericVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($glJrnl)) . "&amp;glPer=" . urlencode(trim($glPer)) . "&amp;glDate=" . urlencode(trim($glDate)) . "&amp;glJrnlSeq=" . urlencode(trim($glJrnlSeq)) . "&amp;glJrnlRec=" . urlencode(trim($glJrnlRec)) . "&amp;unpostedRRN=" . urlencode(trim($unpostedRRN)) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDAp=" . urlencode(trim($glDDAp)) . "&amp;glDDFile=" . urlencode(trim($glDDFile));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$backHome       = "glDDReport.d2w/REPORT";
$dftOrderBy = array(array("GFRPTS","A","Transaction Type"),array("GFSITE","A","Site"),array("GFASST","A","Asset"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

$GFRPTS=RetValue("(GFPER#,GFDDSQ)=($glPer,$glDDSeq) ", "FAGLDD", "MAX(GFRPTS)");
$GFTRFG=RetValue("(GFPER#,GFDDSQ)=($glPer,$glDDSeq) ", "FAGLDD", "MAX(GFTRFG)");

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
	print "\n    && editNum(document.Search.srchAsset, 12, 0) ";
	print "\n    && editdate(document.Search.srchBegDeprDate) ";
	print "\n    && editdate(document.Search.srchAcqDate) ";
	print "\n    && editNum(document.Search.srchLife, 2, 0) ";
	print "\n    && editNum(document.Search.srchPeriod, 4, 0) ";
	print "\n    && editNum(document.Search.srchSite, 3, 0) ";
	print "\n    && editNum(document.Search.srchSched, 2, 0) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "I";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Amount","srchAmt","","operAmt","opersel_num_short","N","17","17");
	Build_AdvSrch_Entry("Transaction Type","srchTransType","","operTransType","opersel_alph_short","A","3","3");
	Build_AdvSrch_Entry("Asset","srchAsset","","operAsset","opersel_num_short","N","12","12");
	Build_AdvSrch_Entry("Asset Description","srchAssetDescr","","operAssetDescr","opersel_alph_short","A","30","60");
	Build_AdvSrch_Entry("Property Type","srchProperty","","operProperty","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Begin Depreciation Date","srchBegDeprDate","","operBegDeprDate","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("Acquisition Date","srchAcqDate","","operAcqDate","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("Life","srchLife","","operLife","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Depreciation Method","srchDeprMeth","","operDeprMeth","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Depreciation Period","srchPeriod","","operPeriod","opersel_num_short","DP","4","4");
	Build_AdvSrch_Entry("Site","srchSite","","operSite","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("Site Name","srchSiteName","","operSiteName","opersel_alph_short","A","26","26");
	Build_AdvSrch_Entry("Schedule","srchSched","","operSched","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Schedule Description","srchSchedDesc","","operSchedDesc","opersel_alph_short","A","30","30");

	$focusField = "srchAmt";
	require_once 'AdvSearchBottom.php';
}

if ($tag == "ORDERBY"){
	if     ($sequence == "Amount")        {$orby = array(array("GFAMT","A","Amount"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "TransType")     {$orby = array(array("GFRPTS","A","Transaction Type"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "Asset")         {$orby = array(array("GFASST","A","Asset"),array("GFSITE","A","Site")) ;}
	elseif ($sequence == "AssetDescr")    {$orby = array(array("GFDESCU","A","Asset Description"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "PropType")      {$orby = array(array("PTDESCU","A","Property Type"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "RetireCode")    {$orby = array(array("GFFPCD","A","Retirement Code"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "RetireDate")    {$orby = array(array("GFRTDT","A","Retirement Date"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "RetireReason")  {$orby = array(array("GFRRES","A","Retirement Reason"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "TrfCode")       {$orby = array(array("GFFPCD","A","Transfer Code"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "TrfDate")       {$orby = array(array("GFRTDT","A","Transfer Date"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "TrfToAsset")    {$orby = array(array("GFTSIT","A","Transfer To Asset"),array("GFTAST","A",""),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "TrfPeriod")     {$orby = array(array("GFTFPD","A","Transfer Period"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "TrfFrAsset")    {$orby = array(array("GFTSIT","A","Transfer From Asset"),array("GFTAST","A",""),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "BeginDeprDate") {$orby = array(array("GFBDDT","A","Begin Depr Date"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "Life")          {$orby = array(array("GFLFYY","A","Life"),array("GFLFMM","A",""),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "DeprMeth")      {$orby = array(array("GFDPCD","A","Depreciation Method"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "DeprPeriod")    {$orby = array(array("GFPER","A","Depreciation Period"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "SiteName")      {$orby = array(array("ACLONMU","A","Site Name"),array("GFASST","A","Asset")) ;}
	elseif ($sequence == "Schedule")      {$orby = array(array("SMDESCU","A","Schedule Name"),array("GFSITE","A","Site"),array("GFASST","A","Asset")) ;}

	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard ("GFAMT", "Amount", $_POST['srchAmt'], "", $_POST['operAmt'], "N");
	$returnValue=Build_WildCard ("GFRPTS", "Transaction Type", $_POST['srchTransType'], "U", $_POST['operTransType'], "A");
	$returnValue=Build_WildCard ("GFASST", "Asset", $_POST['srchAsset'], "", $_POST['operAsset'], "N");
	$returnValue=Build_WildCard ("upper(GFDESC)", "Asset Description", $_POST['srchAssetDescr'], "U", $_POST['operAssetDescr'], "A");
	$returnValue=Build_WildCard ("upper(PTDESC)", "Property Type", $_POST['srchProperty'], "U", $_POST['operProperty'], "A");
	$returnValue=Build_WildCard ("GFBDDT", "Begin Depreciation Date", $_POST['srchBegDeprDate'], "", $_POST['operBegDeprDate'], "D");
	$returnValue=Build_WildCard ("GFAQDT", "Acquisition Date", $_POST['srchAcqDate'], "", $_POST['operAcqDate'], "D");
	$returnValue=Build_WildCard ("GFLFYY", "Life", $_POST['srchLife'], "", $_POST['operLife'], "N");
	$returnValue=Build_WildCard ("GFDPCD", "Depreciation Method", $_POST['srchDeprMeth'], "U", $_POST['operDeprMeth'], "A");
	$returnValue=Build_WildCard ("GFPER#", "Depreciation Period", $_POST['srchPeriod'], "", $_POST['operPeriod'], "DP");
	$returnValue=Build_WildCard ("GFSITE", "Site", $_POST['srchSite'], "", $_POST['operSite'], "N");
	$returnValue=Build_WildCard ("upper(ACLONM)", "Site Name", $_POST['srchSiteName'], "U", $_POST['operSiteName'], "A");
	$returnValue=Build_WildCard ("GFSCHD", "Schedule", $_POST['srchSched'], "", $_POST['operSched'], "N");
	$returnValue=Build_WildCard ("upper(SMDESC)", "Schedule Description", $_POST['srchSchedDesc'], "U", $_POST['operSchedDesc'], "A");

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
	$uv_CompanyName   ="JFCO";
	$uv_FacilityName  ="JFFAC";
	$uv_AccountName   ="JFACC";
	$uv_SubaccountName="JFSUB";
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

$uv_CompanyName     ="GFCO";
$uv_FacilityName    ="GFFAC";
$uv_AccountName     ="GFACCT";
$uv_SubaccountName  ="GFSUB";
$uv_SiteName        ="AMSITE";
$uv_PropertyTypeName="AMPROP";
$uv_FamilyCodeName  ="AMFMCD";
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
	$stmtSQL .= " SELECT JFREF,JFCO,JFFAC,JFACC,JFSUB,JFAMT,JFDESC ";
	$stmtSQL .= "       ,coalesce(CFCFNM,' ') as CFCFNM, coalesce(CFCURT,' ') as CFCURT ";
	$stmtSQL .= "       ,coalesce(CHCHDS,' ') as CHCHDS, coalesce(CHACSG,' ') as CHACSG ";
	$stmtSQL .= "       ,coalesce(FLDESC,' ') as FLDESC,  upper(coalesce(FLDESC,' ')) as FLDESCU ";
	$stmtSQL .= "       ,coalesce(CYDESC,' ') as CYDESC,  upper(coalesce(CYDESC,' ')) as CYDESCU ";
	$fileSQL .= " HDFATR ";
	$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(JFCO,JFFAC) ";
	$fileSQL .= " left join HDCHRT on (CHACCT,CHSUB)=(JFACC,JFSUB) ";
	$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('USAGE',CHACSG) ";
	$fileSQL .= " left join HDCTYP on CYTYPE=CFCURT ";
	$selectSQL .= " RRN(HDFATR)=$unpostedRRN ";
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

	$F_AcctSub=Format_Acct($row['JFACC'], $row['JFSUB'],"N");
	$F_JFAMT=Format_Nbr($row['JFAMT'], "2", $amtEditCode, "Y", "", "");
	$F_CoFac=Format_CoFac($row['JFCO'], $row['JFFAC'],"N");
	require 'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colalph\">$row[JFREF]</td> ";
	print "\n <td class=\"colnmbr\">$F_CoFac</td> ";
	print "\n <td class=\"colalph\">$row[CFCFNM]</td> ";
	print "\n <td class=\"colnmbr\">$F_AcctSub</td> ";
	print "\n <td class=\"colalph\">$row[CHCHDS]</td> ";
	print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CHACSG]\">$row[FLDESC]</span></td> ";
	if ($HDMCRL>0 && $CTPRMC=="Y") {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[CFCURT]\">$row[CYDESC]</span></td> ";}
	print "\n <td class=\"colnmbr\">$F_JFAMT</td> ";
	print "\n <td class=\"colalph\">$row[JFDESC]</td> ";
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
$stmtSQL .= " SELECT GFPER# as GFPER,GFRPTS,GFJRNL,GFAMT,GFSITE,GFASST,GFSCHD ";
$stmtSQL .= "       ,GFDESC,GFAQDT,GFPROP,GFFPCD,GFRTDT,GFRRES,GFTFPD ";
$stmtSQL .= "       ,GFTSIT,GFTAST,GFBDDT,GFLFYY,GFLFMM,GFDPCD ";
$stmtSQL .= "       ,upper(GFDESC) as GFDESCU  ";
$stmtSQL .= "       ,coalesce(FLDESC,' ') as FLDESC ";
$stmtSQL .= "       ,coalesce(SMDESC,' ') as SMDESC, upper(coalesce(SMDESC,' ')) as SMDESCU ";
$stmtSQL .= "       ,coalesce(ACLONM,' ') as ACLONM, upper(coalesce(ACLONM,' ')) as ACLONMU ";
$stmtSQL .= "       ,coalesce(PTDESC,' ') as PTDESC, upper(coalesce(PTDESC,' ')) as PTDESCU ";
$stmtSQL .= "       ,(Select Count(*) From FAMSTR Where (AMSITE,AMASST)=(GFSITE,GFASST)) as FAHISTORY " ;
$fileSQL .= " FAGLDD ";
$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('FAFEED',GFRPTS) ";
$fileSQL .= " left join FASCHD on SMSCHD=GFSCHD ";
$fileSQL .= " left join FASITE on ACLOCN=GFSITE ";
$fileSQL .= " left join FAPROP on PTTYPE=GFPROP ";
$selectSQL .= " (GFPER#,GFDDSQ)=($glPer,$glDDSeq) ";
require 'stmtSQLSelect.php';
$stmtSQL .= "  ORDER BY $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"GFAMT|null|Amount|N|\" title=\"Amount\">Amount";
	$qsOpt .= "\n <option value=\"GFRPTS|null|Transaction Type|A|U\" title=\"Transaction Type\">Transaction Type";
	$qsOpt .= "\n <option value=\"GFASST|null|Asset|N|\" title=\"Asset\" SELECTED>Asset";
	$qsOpt .= "\n <option value=\"upper(GFDESC)|null|Asset Description|A|U\" title=\"Asset Description\">Asset Description";
	$qsOpt .= "\n <option value=\"upper(PTDESC)|null|Property Type|A|U\" title=\"Property Type\">Property Type";
	$qsOpt .= "\n <option value=\"GFBDDT|DATE|Begin Depreciation Date|D|\" title=\"Begin Depreciation Date\">Begin Depreciation Date";
	$qsOpt .= "\n <option value=\"GFAQDT|DATE|Acquisition Date|D|\" title=\"Acquisition Date\">Acquisition Date";
	$qsOpt .= "\n <option value=\"GFLFYY|null|Life|N|\" title=\"Life\">Life";
	$qsOpt .= "\n <option value=\"GFDPCD|null|Depreciation Method|A|U\" title=\"Depreciation Method\">Depreciation Method";
	$qsOpt .= "\n <option value=\"GFPER#|null|Depreciation Period|DP|\" title=\"Depreciation Period\">Depreciation Period";
	$qsOpt .= "\n <option value=\"GFSITE|null|Site|N|\" title=\"Site\">Site";
	$qsOpt .= "\n <option value=\"upper(ACLONM)|null|Site Name|A|U\" title=\"Site Name\">Site Name";
	$qsOpt .= "\n <option value=\"GFSCHD|null|Schedule|N|\" title=\"Schedule\">Schedule";
	$qsOpt .= "\n <option value=\"upper(SMDESC)|null|Schedule Description|A|U\" title=\"Schedule Description\">Schedule Description";
	require 'QuickSearchOption.php';
}
print "\n <table $contentTable> ";
print "\n <tr> ";
$returnValue=OrderBy_Sort("GFAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount, Site, Asset\">{$sortPoint}Amount</a></th> ";
$returnValue=OrderBy_Sort("GFRPTS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TransType\" title=\"Sequence By Transaction Type, Site, Asset\">{$sortPoint}Trans Type</a></th> ";
$returnValue=OrderBy_Sort("GFASST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Asset\" title=\"Sequence By Asset, Site\">{$sortPoint}Asset</a></th> ";
$returnValue=OrderBy_Sort("GFDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=AssetDescr\" title=\"Sequence By Asset Description, Site, Asset\">{$sortPoint}Asset Description</a></th> ";
$returnValue=OrderBy_Sort("PTDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PropType\" title=\"Sequence By Property Type, Site, Asset\">{$sortPoint}Property Type</a></th> ";
if ($GFRPTS == "RET") {
	$returnValue=OrderBy_Sort("GFFPCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=RetireCode\" title=\"Sequence By Retirement Code, Site, Asset\">{$sortPoint}Retire Code</a></th> ";
	$returnValue=OrderBy_Sort("GFRTDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=RetireDate\" title=\"Sequence By Retirement Date, Site, Asset\">{$sortPoint}Retirement Date</a></th> ";
	$returnValue=OrderBy_Sort("GFRRES"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=RetireReason\" title=\"Sequence By Retirement Reason, Site, Asset\">{$sortPoint}Retirement Reason</a></th> ";
}
if ($GFRPTS == "TRF" && $GFTRFG == "F") {
	$returnValue=OrderBy_Sort("GFFPCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TrfCode\" title=\"Sequence By Transfer Code, Site, Asset\">{$sortPoint}Transfer Code</a></th> ";
	$returnValue=OrderBy_Sort("GFRTDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TrfDate\" title=\"Sequence By Transfer Date, Site, Asset\">{$sortPoint}Transfer Date</a></th> ";
	$returnValue=OrderBy_Sort("GFTSIT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TrfToAsset\" title=\"Sequence By Transfer To Asset, Site, Asset\">{$sortPoint}Transfer To Asset</a></th> ";
}
if ($GFRPTS == "TRF") {
	$returnValue=OrderBy_Sort("GFTFPD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TrfPeriod\" title=\"Sequence By Transfer Period, Site, Asset\">{$sortPoint}Transfer Period</a></th> ";
}
if ($GFRPTS == "TRF" && $GFTRFG == "T") {
	$returnValue=OrderBy_Sort("GFTSIT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TrfFrAsset\" title=\"Sequence By Transfer From Asset, Site, Asset\">{$sortPoint}Transfer From Asset</a></th> ";
}
$returnValue=OrderBy_Sort("GFBDDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BeginDeprDate\" title=\"Sequence By Begin Depr Date, Site, Asset\">{$sortPoint}Begin Depr Date</a></th> ";
$returnValue=OrderBy_Sort("GFLFYY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Life\" title=\"Sequence By Life, Site, Asset\">{$sortPoint}Life</a></th> ";
$returnValue=OrderBy_Sort("GFDPCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DeprMeth\" title=\"Sequence By Depreciation Method, Site, Asset\">{$sortPoint}Depr Meth</a></th> ";
$returnValue=OrderBy_Sort("GFPER"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DeprPeriod\" title=\"Sequence By Depreciation Period, Site, Asset\">{$sortPoint}Depr Period</a></th> ";
$returnValue=OrderBy_Sort("ACLONMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SiteName\" title=\"Sequence By Site Name, Asset\">{$sortPoint}Site Name</a></th> ";
$returnValue=OrderBy_Sort("SMDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Schedule\" title=\"Sequence By Schedule Name, Site, Asset\">{$sortPoint}Schedule</a></th> ";
print "\n </tr> ";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require 'SetRowClass.php';

	$F_GFAMT=Format_Nbr($row['GFAMT'], "2", $amtEditCode, "Y", "", "");
	$F_GFTFPD=PeriodFromCYP($row['GFTFPD']);
	$F_GFPER =PeriodFromCYP($row['GFPER']);
	$F_GFRTDT=Format_Date($row['GFRTDT'], "D");
	$F_GFBDDT=Format_Date($row['GFBDDT'], "D");

	print "\n <tr class=\"$rowClass\"> ";
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDFAAssetDetail.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;siteNumber=" . urlencode(trim($row['GFSITE'])) . "&amp;assetNumber=" . urlencode(trim($row['GFASST'])) . "&amp;scheduleNumber=" . urlencode(trim($row['GFSCHD'])) . "&amp;ddRptRef=" . urlencode(trim($row['GFRPTS'])) . "&amp;deprPeriod=" . urlencode(trim($row['GFPER'])) . "&amp;glJrnl=" . urlencode(trim($row['GFJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "\" title=\"View Asset Drill Down History For Transaction\">$F_GFAMT</a></td> ";
	print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row['FLDESC']) . "\">$row[GFRPTS]</span></td> ";
	print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDFAAssetDetail.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;siteNumber=" . urlencode(trim($row['GFSITE'])) . "&amp;assetNumber=" . urlencode(trim($row['GFASST'])) . "&amp;scheduleNumber=" . urlencode(trim($row['GFSCHD'])) . "&amp;glJrnl=" . urlencode(trim($row['GFJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "\" title=\"View Asset Drill Down History\">$row[GFASST]</a></td> ";
	if ($row['FAHISTORY']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}FAAssetSelect.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;siteNumber=" . urlencode(trim($row['GFSITE'])) . "&amp;assetNumber=" . urlencode(trim($row['GFASST'])) . "&amp;scheduleNumber=" . urlencode(trim($row['GFSCHD'])) . "&amp;glJrnl=" . urlencode(trim($row['GFJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "\" title=\"View Asset\">$row[GFDESC]</a></td> ";}
	else                     {print "\n <td class=\"colnmbr\">$row[GFDESC]</td> ";}
	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}FAPropertyInquiry.d2w/DISPLAY{$altVarBase}&amp;propertyType=" . urlencode(trim($row['GFPROP'])) . "\" onclick=\"$inquiryWinVar\" title=\"Property Type Quickview\">$row[PTDESC]</a></td> ";
	if ($GFRPTS == "RET") {
		print "\n <td class=\"colcode\">$row[GFFPCD]</td> ";
		print "\n <td class=\"coldate\">$F_GFRTDT</td> ";
		print "\n <td class=\"colalph\">$row[GFRRES]</td> ";
	}
	if ($GFRPTS == "TRF" && $GFTRFG == "F") {
		print "\n <td class=\"colcode\">$row[GFFPCD]</td> ";
		print "\n <td class=\"coldate\">$F_GFRTDT</td> ";
		print "\n <td class=\"colnmbr\">$row[GFTSIT]-$row[GFTAST]</td> ";
	}
	if ($GFRPTS == "TRF") {
		print "\n <td class=\"colnmbr\">$F_GFTFPD</td> ";
	}
	if ($GFRPTS == "TRF" && $GFTRFG == "T") {
		print "\n <td class=\"colnmbr\">$row[GFTSIT]-$row[GFTAST]</td> ";
	}
	print "\n <td class=\"coldate\">$F_GFBDDT</td> ";
	print "\n <td class=\"colnmbr\">$row[GFLFYY]/$row[GFLFMM]</td> ";
	print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$cGIPath}FADeprMethodInquiry.d2w/DISPLAY{$altVarBase}&amp;deprMethod=" . urlencode(trim($row['GFDPCD'])) . "\" onclick=\"$inquiryWinVar\" title=\"Depreciation Method Quickview\">$row[GFDPCD]</a></td> ";
	print "\n <td class=\"colnmbr\">$F_GFPER</td> ";
	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}FASiteInquiry.d2w/DISPLAY{$altVarBase}&amp;siteNumber=" . urlencode(trim($row['GFSITE'])) . "\" onclick=\"$inquiryWinVar\" title=\"Site Quickview\">$row[ACLONM]</a></td> ";
	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}FAScheduleInquiry.d2w/DISPLAY{$altVarBase}&amp;scheduleNumber=" . urlencode(trim($row['GFSCHD'])) . "\" onclick=\"$inquiryWinVar\" title=\"Schedule Quickview\">$row[SMDESC]</a></td> ";
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
