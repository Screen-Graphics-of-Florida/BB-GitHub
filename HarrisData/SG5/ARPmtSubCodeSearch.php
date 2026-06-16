<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'ARPmtTypeInclude.php';

$docName            = $_GET['docName'];
$fldName            = $_GET['fldName'];
$fldDesc            = $_GET['fldDesc'];
$moreInfo           = $_GET['moreInfo'];
$forceChange        = $_GET['forceChange'];
$specificPmtType    = $_GET['specificPmtType'];
$specificBatchType  = $_GET['specificBatchType'];
$pmtSubCode         = $_GET['pmtSubCode'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Payment Sub Code Search";
$scriptName     = "ARPmtSubCodeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;forceChange=" . urlencode(trim($forceChange)) . "&amp;specificPmtType=" . urlencode(trim($specificPmtType)) . "&amp;specificBatchType=" . urlencode(trim($specificBatchType)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("PSSBCD","A","Payment Sub Code"));
$programName    = "HARCED";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($specificPmtType!="" || $moreInfo=="Y") {
	if ($specificPmtType!="") {$PYTYPE=$specificPmtType;}
	else                      {$PYTYPE=RetValue("PSSBCD='$pmtSubCode'", "ARPYSB inner join ARPYCD on PYPYCD=PSPYCD", "PYTYPE");}
	if     ($PYTYPE=="C") {$CPCFOV=$C_CPCFOV; $CPACOV=$C_CPACOV; $CPCSAC=$C_CPCSAC; $CPARAC=$C_CPARAC; $CPOFAC=$C_CPOFAC;}
	elseif ($PYTYPE=="D") {$CPCFOV=$D_CPCFOV; $CPACOV=$D_CPACOV; $CPCSAC=$D_CPCSAC; $CPARAC=$D_CPARAC; $CPOFAC=$D_CPOFAC;}
	elseif ($PYTYPE=="J") {$CPCFOV=$J_CPCFOV; $CPACOV=$J_CPACOV; $CPCSAC=$J_CPCSAC; $CPARAC=$J_CPARAC; $CPOFAC=$J_CPOFAC;}
	elseif ($PYTYPE=="M") {$CPCFOV=$M_CPCFOV; $CPACOV=$M_CPACOV; $CPCSAC=$M_CPCSAC; $CPARAC=$M_CPARAC; $CPOFAC=$M_CPOFAC;}
	elseif ($PYTYPE=="U") {$CPCFOV=$U_CPCFOV; $CPACOV=$U_CPACOV; $CPCSAC=$U_CPCSAC; $CPARAC=$U_CPARAC; $CPOFAC=$U_CPOFAC;}
	elseif ($PYTYPE=="Y") {$CPCFOV=$Y_CPCFOV; $CPACOV=$Y_CPACOV; $CPCSAC=$Y_CPCSAC; $CPARAC=$Y_CPARAC; $CPOFAC=$Y_CPOFAC;}
	else                  {$CPCFOV="N"; $CPACOV="N"; $CPCSAC="N"; $CPARAC="N"; $CPOFAC="N";}
} 	else {$CPCFOV="Y"; $CPACOV="Y"; $CPCSAC="Y"; $CPARAC="Y"; $CPOFAC="Y";}

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
	print "\n if (editdate(document.Search.srchDeactDate) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","20","30");
	Build_AdvSrch_Entry("Payment Code Description","srchPmtCodeDesc","","operPmtCodeDesc","opersel_alph_short","A","20","30");
	Build_AdvSrch_Entry("Payment Sub Code","srchSubCode","","operSubCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Payment Code","srchPmtCode","","operPmtCode","opersel_alph_short","A","4","1");
	if ($CPCFOV=="Y") {Build_AdvSrch_Entry("Allow Company/Facility Override Description","srchCoFacOvr","","operCoFacOvr","opersel_alph_short","A","4","3");}
	if ($CPACOV=="Y") {Build_AdvSrch_Entry("Allow Account Override Description","srchAcctOvr","","operAcctOvr","opersel_alph_short","A","4","3");}
	if ($CPCSAC=="Y") {Build_AdvSrch_Entry("Cash Account Description","srchCashAcct","","operCashAcct","opersel_alph_short","A","30","30");}
	if ($CPARAC=="Y") {Build_AdvSrch_Entry("A/R Account Description","srchARAcct","","operARAcct","opersel_alph_short","A","30","30");}
	if ($CPOFAC=="Y") {
		Build_AdvSrch_Entry("Offset Co/Fac Name","srchOffCoFac","","operOffCoFac","opersel_alph_short","A","30","30");
		Build_AdvSrch_Entry("Offset Account Description","srchOffAcct","","operOffAcct","opersel_alph_short","A","30","30");
	}
	Build_AdvSrch_Entry("Statement Description","srchStmtDesc","","operStmtDesc","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Date Deactivated","srchDeactDate","","operDeactDate","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("Payment Type","srchPmtType","","operPmtType","opersel_alph_short","A","1","1");
	
	$focusField = "srchDesc";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Description")   {$orby = array(array("PSDESCU","A","Description"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "PmtCode")       {$orby = array(array("PYPYDSU","A","Payment Code"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "SubCode")       {$orby = array(array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "CashAcct")      {$orby = array(array("CSH_CHCHDSU","A","Cash Account"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "ARAcct")        {$orby = array(array("AR_CHCHDSU","A","A/R Account"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "OffsetCoFac")   {$orby = array(array("CFCFNMU","A","Offset Company/Facility"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "OffsetAccount") {$orby = array(array("OFF_CHCHDSU","A","Offset Account"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "StmtDescr")     {$orby = array(array("PSSTDS","A","Statement Description"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "Deactivated")   {$orby = array(array("PSDTDE","A","Date Deactivated"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "PmtType")       {$orby = array(array("PYTYPE","A","Payment Type"),array("PSSBCD","A","Payment Sub Code"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard ("PSDESCU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard ("PYPYDSU", "Payment Code Description", $_POST['srchPmtCodeDesc'], "U", $_POST['operPmtCodeDesc'], "A");
	$returnValue=Build_WildCard ("PSSBCD", "Payment Sub Code", $_POST['srchSubCode'], "U", $_POST['operSubCode'], "A");
	$returnValue=Build_WildCard ("PSPYCD", "Payment Code", $_POST['srchPmtCode'], "U", $_POST['operPmtCode'], "A");
	$returnValue=Build_WildCard ("upper(d.FLDESC)", "Allow Company/Facility Override Description", $_POST['srchCoFacOvr'], "U", $_POST['operCoFacOvr'], "A");
	$returnValue=Build_WildCard ("upper(e.FLDESC)", "Allow Account Override Description", $_POST['srchAcctOvr'], "U", $_POST['operAcctOvr'], "A");
	$returnValue=Build_WildCard ("a.CHCHDSU", "Cash Account Description", $_POST['srchCashAcct'], "U", $_POST['operCashAcct'], "A");
	$returnValue=Build_WildCard ("b.CHCHDSU", "A/R Account Description", $_POST['srchARAcct'], "U", $_POST['operARAcct'], "A");
	$returnValue=Build_WildCard ("CFCFNMU", "Offset Co/Fac Name", $_POST['srchOffCoFac'], "U", $_POST['operOffCoFac'], "A");
	$returnValue=Build_WildCard ("c.CHCHDSU", "Offset Account Description", $_POST['srchOffAcct'], "U", $_POST['operOffAcct'], "A");
	$returnValue=Build_WildCard ("PSSTDSU", "Statement Description", $_POST['srchStmtDesc'], "U", $_POST['operStmtDesc'], "A");
	$returnValue=Build_WildCard ("Coalesce(PSDTDE,'0001-01-01')", "Date Deactivated", $_POST['srchDeactDate'], "", $_POST['operDeactDate'], "I");
	$returnValue=Build_WildCard ("PYTYPE", "Payment Type", $_POST['srchPmtType'], "U", $_POST['operPmtType'], "A");
	require_once 'WildCardUpdate.php';
}

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

print "\n function selectSubCode(pmtSubCode, pmtSubCodeDesc) { ";
print "\n   if (window.opener.document.getElementById('PopUpWindow')===true) {window.opener.document.getElementById('PopUpWindow').innerHTML = self.name;} ";
print "\n   window.opener.document.$docName.$fldName.value = pmtSubCode; ";
print "\n   if      (window.opener.document.$docName.$fldDesc)          {window.opener.document.$docName.$fldDesc.value = pmtSubCodeDesc;} ";
print "\n   else if (window.opener.document.getElementById('$fldDesc')) {window.opener.document.getElementById('$fldDesc').innerHTML = pmtSubCodeDesc;} ";
print "\n   window.opener.document.$docName.$fldName.focus(); ";
if ($forceChange=="Y") {
	print "\n window.opener.document.getElementById('$fldName').onchange(); ";
	print "\n setTimeout('self.close()',3000); ";
} else {
	print "\n window.close(); ";
}
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
if ($specificPmtType!="") {
	print "\n <table $contentTable> ";
	if ($specificPmtType=="PAYOFF") {Format_Header("Payment Type", "Pay Off", "");}
	else {
		if ($specificPmtType=="M") {$CPDESC="Miscellaneous Cash";}
		else                       {$CPDESC=RetValue("Coalesce(CPTYPE,'$specificPmtType')='$specificPmtType'", "ARPAYT", "CPDESC");}
		Format_Header("Payment Type", $CPDESC, $specificPmtType);
	}
	print "\n </table> ";
}
print $searchhrTagAttr;

$optSecSelect="";
$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
if ($specificBatchType!="") {
	if (($CRBBAL=="Y" && $specificBatchType=="C" && $harced_OPT['sec_01']=="N" && $harced_OPT['sec_02']=="N") ||
	($harced_OPT['sec_01']=="N" && $harced_OPT['sec_02']=="N" && $harced_OPT['sec_03']=="N" && $harced_OPT['sec_04']=="N" && ($harced_OPT['sec_06']=="N" || $CRDPYC==""))) {
		$optSecSelect=" CPTYPE<>CPTYPE ";}
}
if (($harced_OPT['sec_01']=="N" || $harced_OPT['sec_02']=="N" || $harced_OPT['sec_03']=="N" || $harced_OPT['sec_04']=="N" || $harced_OPT['sec_06']=="N" && $CRDPYC!="")) {
	if     ($harced_OPT['sec_01']=="Y" && $optSecSelect=="") {$optSecSelect .= " Coalesce(CPTYPE,PYTYPE) in ('C','M') ";}
	elseif ($harced_OPT['sec_01']=="Y")                      {$optSecSelect .= " or Coalesce(CPTYPE,PYTYPE)in ('C','M') ";}
	if     ($harced_OPT['sec_02']=="Y" && $optSecSelect=="") {$optSecSelect .= " Coalesce(CPTYPE,PYTYPE)='U' ";}
	elseif ($harced_OPT['sec_02']=="Y")                      {$optSecSelect .= " or Coalesce(CPTYPE,PYTYPE)='U' ";}
	if     ($harced_OPT['sec_03']=="Y" && $optSecSelect=="") {$optSecSelect .= " Coalesce(CPTYPE,PYTYPE)='J' ";}
	elseif ($harced_OPT['sec_03']=="Y")                      {$optSecSelect .= " or Coalesce(CPTYPE,PYTYPE)='J' ";}
	if     ($harced_OPT['sec_04']=="Y" && $optSecSelect=="") {$optSecSelect .= " Coalesce(CPTYPE,PYTYPE)='Y' ";}
	elseif ($harced_OPT['sec_04']=="Y")                      {$optSecSelect .= " or Coalesce(CPTYPE,PYTYPE)='Y' ";}
	if     ($harced_OPT['sec_06']=="Y" && $CRDPYC!="" && $optSecSelect=="") {$optSecSelect .= " Coalesce(CPTYPE,PYTYPE)='D' ";}
	elseif ($harced_OPT['sec_06']=="Y" && $CRDPYC!="")       {$optSecSelect .= " or Coalesce(CPTYPE,PYTYPE)='D' ";}
}

require 'stmtSQLClear.php';
$stmtSQL .= " Select PSSBCD,PSDESC,PSPYCD,PSSTDS,PSCSAC,PSCSSB,PSARAC,PSARSB,PSOFCO,PSOFFC,PSOFAC,PSOFSB,PSDTDE,   ";
$stmtSQL .= " coalesce(PYPYDS,' ') as PYPYDS, upper(coalesce(PYPYDS,' ')) as PYPYDSU,   ";
$stmtSQL .= " coalesce(PYTYPE,' ') as PYTYPE,   ";
$stmtSQL .= " coalesce(d.FLDESC,' ') as COFAC_FLDESC,   ";
$stmtSQL .= " coalesce(e.FLDESC,' ') as ACCT_FLDESC,   ";
$stmtSQL .= " coalesce(a.CHCHDS,' ') as CSH_CHCHDS, coalesce(a.CHCHDSU,' ') as CSH_CHCHDSU,   ";
$stmtSQL .= " coalesce(b.CHCHDS,' ') as AR_CHCHDS, coalesce(b.CHCHDSU,' ') as AR_CHCHDSU,   ";
$stmtSQL .= " coalesce(CFCFNM,' ') as CFCFNM, coalesce(CFCFNMU,' ') as CFCFNMU,   ";
$stmtSQL .= " coalesce(c.CHCHDS,' ') as OFF_CHCHDS, coalesce(c.CHCHDSU,' ') as OFF_CHCHDSU   ";
$fileSQL .= " ARPYSB ";
$fileSQL .= " left join ARPYCD on PYPYCD=PSPYCD ";
$fileSQL .= " left join SYFLAG d on (d.FLTYPE,d.FLVALU)=('YORN',PSCFOV) ";
$fileSQL .= " left join SYFLAG e on (e.FLTYPE,e.FLVALU)=('YORN',PSACOV) ";
$fileSQL .= " left join HDCHRT a on (a.CHACCT,a.CHSUB)=(PSCSAC,PSCSSB) ";
$fileSQL .= " left join HDCHRT b on (b.CHACCT,b.CHSUB)=(PSARAC,PSARSB) ";
$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(PSOFCO,PSOFFC) ";
$fileSQL .= " left join HDCHRT c on (c.CHACCT,c.CHSUB)=(PSOFAC,PSOFSB) ";
$fileSQL .= " left join ARPAYT on CPTYPE=PYTYPE ";
if     ($moreInfo=="Y")             {$selectSQL .=" PSSBCD='$pmtSubCode'";}
elseif ($specificPmtType=="PAYOFF") {$selectSQL .=" Coalesce(PYTYPE,' ') in ('D','J') and PSPYCD<>'$CRPYCD' ";}
elseif ($specificPmtType!="")       {$selectSQL .=" Coalesce(PYTYPE,' ')='$specificPmtType' ";}
elseif ($CRBBAL=="Y" && $specificBatchType=="C") {$selectSQL .=" and CPTRNT='A' ";}
elseif ($specificBatchType!="")     {$selectSQL .=" Coalesce(CPTYPE,' ')<>' ' and CPTYPE<>'A'";}

if     ($optSecSelect!="" && $selectSQL !="")   {$selectSQL .=" and ($optSecSelect) ";}
elseif ($optSecSelect!="")                      {$selectSQL .=" ($optSecSelect) ";}
elseif ($wildCardSearch!="" && $selectSQL =="") {$selectSQL .=" PSSBCD=PSSBCD ";}

require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"PSDESCU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"PYPYDSU|null|Payment Code Description|A|U\" title=\"Payment Code Description\">Payment Code Description";
	$qsOpt .= "\n <option value=\"PSPYCD|null|Payment Code|A|U\" title=\"Payment Code\">Payment Code";
	$qsOpt .= "\n <option value=\"PSSBCD|null|Payment Sub Code|A|U\" title=\"Payment Sub Code\">Payment Sub Code";
	if ($CPCFOV=="Y") {$qsOpt .= "\n <option value=\"upper(d.FLDESC)|null|Allow Company/Facility Override Description|A|U\" title=\"Allow Company/Facility Override Description\">Allow Company/Facility Override Description";}
	if ($CPACOV=="Y") {$qsOpt .= "\n <option value=\"upper(e.FLDESC)|null|Allow Account Override Description|A|U\" title=\"Allow Account Override Description\">Allow Account Override Description";}
	if ($CPCSAC=="Y") {$qsOpt .= "\n <option value=\"a.CHCHDSU|null|Cash Account Description|A|U\" title=\"Cash Account Description\">Cash Account Description";}
	if ($CPARAC=="Y") {$qsOpt .= "\n <option value=\"b.CHCHDSU|null|A/R Account Description|A|U\" title=\"A/R Account Description\">A/R Account Description";}
	if ($CPOFAC=="Y") {
		$qsOpt .= "\n <option value=\"CFCFNMU|null|Offset Co/Fac Name|A|U\" title=\"Offset Co/Fac Name\">Offset Co/Fac Name";
		$qsOpt .= "\n <option value=\"c.CHCHDSU|null|Offset Account Description|A|U\" title=\"Offset Account Description\">Offset Account Description";
	}
	$qsOpt .= "\n <option value=\"PSSTDSU|null|Statement Description|A|U\" title=\"Statement Description\">Statement Description";
	$qsOpt .= "\n <option value=\"Coalesce(PSDTDE,'0001-01-01')|DATE|Date Deactivated|I|\" title=\"Date Deactivated\">Date Deactivated";
	$qsOpt .= "\n <option value=\"PYTYPE|null|Payment Type|A|U\" title=\"Payment Type\">Payment Type";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Payment Sub Code\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("PYPYDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtCode\" title=\"Sequence By Payment Code, Payment Sub Code\">{$sortPoint}Payment Code</a></th>";
	$returnValue=OrderBy_Sort("PSSBCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SubCode\" title=\"Sequence By Payment Sub Code\">{$sortPoint}Payment Sub Code</a></th>";
	if ($CPCSAC=="Y") {
		$returnValue=OrderBy_Sort("CSH_CHCHDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CashAcct\" title=\"Sequence By Cash Account, Payment Sub Code\">{$sortPoint}Cash Account</a></th>";
	}
	if ($CPARAC=="Y") {
		$returnValue=OrderBy_Sort("AR_CHCHDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ARAcct\" title=\"Sequence By A/R Account, Payment Sub Code\">{$sortPoint}A/R Account</a></th>";
	}
	if ($CPOFAC=="Y") {
		$returnValue=OrderBy_Sort("CFCFNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OffsetCoFac\" title=\"Sequence By Offset Company/Facility, Payment Sub Code\">{$sortPoint}Offset Co/Fac</a></th>";
		$returnValue=OrderBy_Sort("OFF_CHCHDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OffsetAccount\" title=\"Sequence By Offset Account, Payment Sub Code\">{$sortPoint}Offset Account</a></th>";
	}
	$returnValue=OrderBy_Sort("PSSTDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StmtDescr\" title=\"Sequence By Statement Description, Payment Sub Code\">{$sortPoint}Statement Description</a></th>";
	$returnValue=OrderBy_Sort("PSDTDE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Deactivated\" title=\"Sequence By Date Deactivated, Payment Sub Code\">{$sortPoint}Date Deactivated</a></th>";
	$returnValue=OrderBy_Sort("PYTYPE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtType\" title=\"Sequence By Payment Type, Payment Sub Code\">{$sortPoint}Payment Type</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';

		$F_PSSBCD=Format_Quote($row['PSSBCD']);
		$F_PSDESC=Format_Quote($row['PSDESC']);
		$F_PSDTDE=Format_Date_ISO($row['PSDTDE'], "D");

		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectSubCode('" . trim($F_PSSBCD) . "','" . trim($F_PSDESC) . "')\" title=\"Select Payment Sub Code\">$row[PSDESC]</a></td> ";
		print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[PSPYCD]\">$row[PYPYDS]</span></td> ";
		print "\n <td class=\"colalph\">$row[PSSBCD]</td> ";
		if ($CPCSAC=="Y") {
			$F_AcctSub=Format_Acct($row['PSCSAC'],$row['PSCSSB'],"N");
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_AcctSub\">$row[CSH_CHCHDS]</span></td>";
		}
		if ($CPARAC=="Y") {
			$F_AcctSub=Format_Acct($row['PSARAC'],$row['PSARSB'],"N");
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_AcctSub\">$row[AR_CHCHDS]</span></td>";
		}
		if ($CPOFAC=="Y") {
			$F_CoFac=Format_CoFac($row['PSOFCO'], $row['PSOFFC'],"N");
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_CoFac\">$row[CFCFNM]</span></td>";
			$F_AcctSub=Format_Acct($row['PSOFAC'],$row['PSOFSB'],"N");
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_AcctSub\">$row[OFF_CHCHDS]</span></td>";
		}
		print "\n <td class=\"colalph\">$row[PSSTDS]</td>";
		print "\n <td class=\"colalph\">$F_PSDTDE</td>";
		print "\n <td class=\"colcode\">$row[PYTYPE]</td> ";
		print "\n <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;pmtSubCode=" . urlencode(trim($row['PSSBCD'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";

} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_PSSBCD=Format_Quote($row['PSSBCD']);
	$F_PSDESC=Format_Quote($row['PSDESC']);
	$F_PSDTDE=Format_Date_ISO($row['PSDTDE'], "D");
	$moreInfoSelect = "href=\"javascript:selectSubCode('" . trim($F_PSSBCD) . "','" . trim($F_PSDESC) . "')\" title=\"Select Payment Sub Code\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	print "\n     <tr><td class=\"dsphdr\">Payment Sub Code</td> ";
	print "\n         <td class=\"dspalph\">$row[PSSBCD]</td> ";
	print "\n     </tr> ";

	print "\n     <tr><td class=\"dsphdr\">Description</td> ";
	print "\n         <td class=\"dspalph\">$row[PSDESC]</td> ";
	print "\n     </tr> ";

	$F_PSPYCD=Format_Code($row['PSPYCD']);
	print "\n     <tr><td class=\"dsphdr\">Payment Code</td> ";
	print "\n         <td class=\"dspalph\">$row[PYPYDS] $F_PSPYCD</td> ";
	print "\n     </tr> ";

	print "\n     <tr><td class=\"dsphdr\">Statement Description</td> ";
	print "\n         <td class=\"dspalph\">$row[PSSTDS]</td> ";
	print "\n     </tr> ";

	if ($CPCFOV=="Y") {
		$F_PSCFOV=Format_Code($row['PSCFOV']);
		print "\n     <tr><td class=\"dsphdr\">Allow Company/Facility Override</td> ";
		print "\n         <td class=\"dspalph\">$row[COFAC_FLDESC] $F_PSCFOV</td> ";
		print "\n     </tr> ";
	}

	if ($CPACOV=="Y") {
		$F_PSACOV=Format_Code($row['PSACOV']);
		print "\n     <tr><td class=\"dsphdr\">Allow Account Override</td> ";
		print "\n         <td class=\"dspalph\">$row[ACCT_FLDESC] $F_PSACOV</td> ";
		print "\n     </tr> ";
	}

	if ($CPCSAC=="Y") {
		$F_AcctSub=Format_Code(Format_Acct($row['PSCSAC'],$row['PSCSSB'],"N"));
		print "\n     <tr><td class=\"dsphdr\">Cash Account</td> ";
		print "\n         <td class=\"dspnmbr\">$row[CSH_CHCHDS] $F_AcctSub</td> ";
		print "\n     </tr> ";
	}

	if ($CPARAC=="Y") {
		$F_AcctSub=Format_Code(Format_Acct($row['PSARAC'],$row['PSARSB'],"N"));
		print "\n     <tr><td class=\"dsphdr\">A/R Account</td> ";
		print "\n     <td class=\"dspnmbr\">$row[AR_CHCHDS] $F_AcctSub</td> ";
		print "\n     </tr> ";
	}

	if ($CPOFAC=="Y") {
		$F_CoFac=Format_Code(Format_CoFac($row['PSOFCO'], $row['PSOFFC'],"N"));
		print "\n     <tr><td class=\"dsphdr\">Offset Company/Facility</td> ";
		print "\n     <td class=\"dspnmbr\">$row[CFCFNM] $F_CoFac</td> ";
		print "\n     </tr> ";

		$F_AcctSub=Format_Code(Format_Acct($row['PSOFAC'],$row['PSOFSB'],"N"));
		print "\n     <tr><td class=\"dsphdr\">Offset Account</td> ";
		print "\n     <td class=\"dspnmbr\">$row[OFF_CHCHDS] $F_AcctSub</td> ";
		print "\n     </tr> ";
	}

	$F_PSDTDE=Format_Date_ISO($row['PSDTDE'], "D");
	print "\n     <tr><td class=\"dsphdr\">Date Deactivated</td> ";
	print "\n     <td class=\"dspalph\">$F_PSDTDE</td> ";
	print "\n     </tr> ";

	print "\n     <tr><td class=\"dsphdr\">Payment Type</td> ";
	print "\n         <td class=\"dspalph\">$row[PYTYPE]</td> ";
	print "\n     </tr> ";
	
	print "\n </table> ";
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


