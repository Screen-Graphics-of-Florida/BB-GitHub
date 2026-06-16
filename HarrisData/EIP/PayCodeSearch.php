<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$forCo  = (isset($_GET['forCo']))         ? $_GET['forCo']        : null;
$forFac = (isset($_GET['forFac']))        ? $_GET['forFac']       : null;
$fldCo  = (isset($_GET['fldCo']))         ? $_GET['fldCo']        : null;
$fldFac = (isset($_GET['fldFac']))        ? $_GET['fldFac']       : null;
$moreInfo = $_GET['moreInfo'];
$moreCode = $_GET['moreCode'];
$moreCo   = $_GET['moreCo'];
$moreFac  = $_GET['moreFac'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Pay Code Search";
$scriptName     = "PayCodeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldCo=" . urlencode(trim($fldCo)) . "&amp;fldFac=" . urlencode(trim($fldFac)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;forCo=" . urlencode(trim($forCo)) . "&amp;forFac=" . urlencode(trim($forFac));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
if ($forCo==null and $forFac==null) {$dftOrderBy     = array(array("C2DESCU","A","desc"),array("C2CODEU","A","Pay Code"));}
else {$dftOrderBy     = array(array("C2COMP","N","Company"), array("C2FACL","N","Facility"), array("C2CODEU","A","Pay Code"));}
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
	require_once 'CheckEnterSearch.php';
	require_once 'NumEdit.php';
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchAcct, 4, 0)  ";
	print "\n &&  editNum(document.Search.srchSub, 4, 0)  ";
	if ($forCo==null and $forFac==null) {
		print "\n &&  editNum(document.Search.srchComp, 2, 0)  ";
		print "\n &&  editNum(document.Search.srchFacl, 4, 0)  ";
	}
	print "\n &&  editdate(document.Search.srchStart)  ";
	print "\n &&  editdate(document.Search.srchEnd) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	if ($forCo==null and $forFac==null) {
		Build_AdvSrch_Entry("Company","srchComp","","operComp","opersel_num_short","N","2","2");
		Build_AdvSrch_Entry("Facility","srchFacl","","operFacl","opersel_num_short","N","4","4");
	}
	Build_AdvSrch_Entry("Pay Code","srchPayCode","","operPayCode","opersel_alph_short","A","3","3");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Hours Or Units","srchHourUnit","","operHourUnit","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Start Date","srchStart","","operStart","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("End Date","srchEnd","","operEnd","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("G/L Account","srchAcct","","operAcct","opersel_num_short","N","4","4");
	Build_AdvSrch_Entry("G/L Sub","srchSub","","operSub","opersel_num_short","N","4","4");
	Build_AdvSrch_Entry("Summary Type","srchSumType","","operSumType","opersel_alph_short","A","1","1");

	if ($forCo==null and $forFac==null) {
		$focusField = "srchComp";
	} else {
		$focusField = "srchPayCode";
	}
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if     ($sequence == "coFac")     {$orby = array(array("C2COMP","N","company"),array("C2FACL","N","facility"),array("C2CODEU","A","payCode"));}
	elseif ($sequence == "payCode")   {$orby = array(array("C2CODEU","A","payCode"),array("C2COMP","N","company"),array("C2FACL","N","facility"));}
	elseif ($sequence == "desc")      {$orby = array(array("C2DESCU","A","desc"),array("C2CODEU","A","payCode"));}
	elseif ($sequence == "hourUnit")  {$orby = array(array("C2HRCD","A","hourUnit"),array("C2CODEU","A","payCode"));}
	elseif ($sequence == "startDate") {$orby = array(array("C2STRT","D","startDate"),array("C2CODEU","A","payCode"));}
	elseif ($sequence == "endDate")   {$orby = array(array("C2END","D","endDate"),array("C2CODEU","A","payCode"));}
	elseif ($sequence == "account")   {$orby = array(array("C2ACCT","N","account"),array("C2SUB","N","subAccount"),array("C2CODEU","A","payCode"));}
	elseif ($sequence == "sumType")   {$orby = array(array("C2SMTY","A","sumType"),array("C2CODEU","A","payCode"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD") {
	$andOr = $_POST['andOr'];
	require 'WildCardClear.php';
	if ($forCo==null and $forFac==null) {
		$returnValue=Build_WildCard("C2COMP", "Company", $_POST['srchComp'], "", $_POST['operComp'], "N");
		$returnValue=Build_WildCard("C2FACL", "Facility", $_POST['srchFacl'], "", $_POST['operFacl'], "N");
	}
	$returnValue=Build_WildCard("upper(C2CODE)", "PayCode", $_POST['srchPayCode'], "U", $_POST['operPayCode'], "A");
	$returnValue=Build_WildCard("upper(C2DESC)", "Desc", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("C2HRCD", "Hours Or Units", $_POST['srchHourUnit'], "U", $_POST['operHourUnit'], "A");
	$returnValue=Build_WildCard("C2STRT", "Start Date", $_POST['srchStart'], "", $_POST['operStart'], "D");
	$returnValue=Build_WildCard("C2END", "End Date", $_POST['srchEnd'], "", $_POST['operEnd'], "D");
	$returnValue=Build_WildCard("C2ACCT", "Account", $_POST['srchAcct'], "", $_POST['operAcct'], "N");
	$returnValue=Build_WildCard("C2SUB", "Sub", $_POST['srchSub'], "", $_POST['operSub'], "N");
	$returnValue=Build_WildCard("C2SMTY", "Summary Type", $_POST['srchSumType'], "U", $_POST['operSumType'], "A");
	require 'WildCardUpdate.php';
}

include ($docType);
print "\n <html> \n	<head>";
include ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectPayCode(companyNumber, facilityNumber, payCode, payCodeDesc){ ";
if ($fldCo) {
print "\n   window.opener.document.$docName.$fldCo.value = companyNumber; ";
}
if ($fldFac) {
print "\n   window.opener.document.$docName.$fldFac.value = facilityNumber; ";
}
print "\n   window.opener.document.$docName.$fldName.value = payCode; ";
print "\n   if (window.opener.document.$docName.$fldDesc) ";
print "\n      {window.opener.document.$docName.$fldDesc.value = payCodeDesc;} ";
print "\n   else if (window.opener.document.getElementById('$fldDesc'))";
print "\n           {window.opener.document.getElementById('$fldDesc').innerHTML = payCodeDesc;}";
print "\n   window.opener.document.$docName.$fldName.focus(); ";
print "\n   window.close(); ";
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
if ($forCo!=null and $forFac!=null) {
	print "<table $contentTable>";
	$F_coFac=($forCo);
	$F_coFac=($F_coFac .= "/");
	$F_coFac=($F_coFac .= "$forFac");
	$fieldDesc=RetValue("CFCOMP='$forCo' and CFFACL='$forFac'", "HRCOFC", "CFNAME");
	Format_Header("Company/Facility", "$fieldDesc", "$F_coFac");
	print "\n </table>";
}
print $searchhrTagAttr;

if ($forCo!=null and $forFac!=null) {
	$uv_CompanyName ="C2COMP";
	$uv_FacilityName ="C2FACL";
	require 'UserView.php';
}

require 'stmtSQLClear.php';
$stmtSQL .= " Select PRCODE.*, upper(C2DESC) as C2DESCU, upper(C2CODE) as C2CODEU ";
$fileSQL .= " PRCODE ";
$selectSQL="C2COMP>0 ";
if ($forCo!=null and $forFac!=null) {$selectSQL.=" and C2COMP=$forCo and C2FACL=$forFac ";}
if ($moreInfo=="Y") {$selectSQL.= " and C2CODE='$moreCode' and C2COMP=$moreCo and C2FACL=$moreFac ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt = "";
	if ($forCo==null and $forFac==null) {
		$qsOpt .= "\n <option value=\"C2COMP|null|Company Number|N|\" title=\"Company Number\">Company Number";
		$qsOpt .= "\n <option value=\"C2FACL|null|Facility Number|N|\" title=\"Facility Number\">Facility Number";
	}
	$qsOpt .= "\n <option value=\"upper(C2CODE)|null|Pay Code|A|U\" title=\"Pay Code\">Pay Code";
	$qsOpt .= "\n <option value=\"upper(C2DESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"C2HRCD|HOURUNIT|Hours/Units|A|U\" title=\"Hours/Units\">Hours/Units";
	$qsOpt .= "\n <option value=\"C2STRT|DATE|Start Date|D|\" title=\"Start Date\">Start Date";
	$qsOpt .= "\n <option value=\"C2END|DATE|End Date|D|\" title=\"End Date\">End Date";
	$qsOpt .= "\n <option value=\"C2ACCT|null|Account Number|N|\" title=\"Account Number\">Account Number";
	$qsOpt .= "\n <option value=\"C2SUB|null|Subaccount Number|N|\" title=\"Subaccount Number\">Subaccount Number";
	$qsOpt .= "\n <option value=\"C2SMTY|PRSUMT|Summary Type|A|U\" title=\"Summary Type\">Summary Type";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	if ($forCo==null and $forFac==null) {
		$returnValue=OrderBy_Sort("C2COMP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=coFac\" title=\"Sequence By Company/Facility, Pay Code\">{$sortPoint}Co/Fac</a></th>";
	}
	$returnValue=OrderBy_Sort("C2CODEU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=payCode\" title=\"Sequence By Pay Code\">{$sortPoint}Pay<br>Code</a></th>";
	$returnValue=OrderBy_Sort("C2DESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=desc\" title=\"Sequence By Description, Pay Code\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("C2HRCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=hourUnit\" title=\"Sequence By Hours/Units, Pay Code\">{$sortPoint}Hours/<br>Units</a></th>";
	$returnValue=OrderBy_Sort("C2STRT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=startDate\" title=\"Sequence By Start Date, Pay Code\">{$sortPoint}Start<br>Date</a></th>";
	$returnValue=OrderBy_Sort("C2END"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=endDate\" title=\"Sequence By End Date, Pay Code\">{$sortPoint}End<br>Date</a></th>";
	$returnValue=OrderBy_Sort("C2ACCT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=account\" title=\"Sequence By Account, Pay Code\">{$sortPoint}Account</a></th>";
	$returnValue=OrderBy_Sort("C2SMTY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=sumType\" title=\"Sequence By Summary Type, Pay Code\">{$sortPoint}Summary<br>Type</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		$F_Desc=Format_Quote($row['C2DESC']);
		if ($forCo==null and $forFac==null) {
			$F_coFac=Format_CoFac($row[C2COMP],$row[C2FACL],"N");
			print "\n     <td class=\"colnmbr\">$F_coFac</td>";
		}
		print "\n     <td class=\"colalph\">$row[C2CODE]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectPayCode('" . trim($row['C2COMP']) . "','" . trim($row['C2FACL']) . "','" . trim($row['C2CODE']) . "','" . trim($F_Desc) . "')\" title=\"Select Desc\">$F_Desc</a></td> ";
		print "\n     <td class=\"colcode\">$row[C2HRCD]</td>";
		$F_dateStart=Format_Date($row[C2STRT], "D");
		print "\n     <td class=\"coldate\">$F_dateStart</td>";
		$F_dateEnd=Format_Date($row[C2END], "D");
		print "\n     <td class=\"coldate\">$F_dateEnd</td>";
		$F_acctSub=Format_Acct($row[C2ACCT],$row[C2SUB],"N");
		print "\n     <td class=\"colalph\">$F_acctSub</td>";
		print "\n     <td class=\"colcode\">$row[C2SMTY]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCode=" . urlencode(trim($row['C2CODE'])) . "&amp;moreCo=" . urlencode(trim($row['C2COMP'])) . "&amp;moreFac=" . urlencode(trim($row['C2FACL'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a> ";

		print "\n </td>";
		print "\n </tr>";
		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['C2DESC']);
	$moreInfoSelect = "href=\"javascript:selectPayCode('" . trim($row['C2COMP']) . "','" . trim($row['C2FACL']) . "','" . trim($row['C2CODE']) . "','" . trim($F_Desc) . "')\" title=\"Select Pay Code\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Processing Variables</legend> ";
	print "\n <table $contentTable> ";
	if ($forCo==null and $forFac==null) {
		$F_coFac=Format_CoFac($row[C2COMP],$row[C2FACL],"Y");
		Build_DspFld("Company/Facility",$F_coFac,"","N");
	}
	Build_DspFld("Pay Code",$row[C2CODE],"","A");
	Build_DspFld("Description",$row[C2DESC],"","A");
	Build_DspFld("Report Heading",$row[C2RPHD],"","A");
	Build_DspFld("Summary Pay Type",$row[C2SMTY],"","A");
	Build_DspFld("Hours Or Units",$row[C2HRCD],"","A");
	Build_DspFld("Apply Shift Premium",$row[C2PREM],"","A");
	Build_DspFld("Take Deductions",$row[C2DED],"","A");
	Build_DspFld("Include In Vacation/Sick Accruals",$row[C2ACCR],"","A");
	Build_DspFld("Include In Overtime",$row[C2IOTC],"","A");
	Build_DspFld("Process Distribution Tables",$row[C2WGDS],"","A");
	Build_DspFld("Process Salary Distribution",$row[C2SLDS],"","A");
	Build_DspFld("Processing Sequence",$row[C2SEQ],"","N");
	$F_acctSub=Format_Acct($row[C2ACCT],$row[C2SUB],"Y");
	Build_DspFld("G/L Account",$F_acctSub,"","N");
	Build_DspFld("Unpaid Hours/Units",$row[C2NOPY],"","A");
	Build_DspFld("Worked Hours",$row[C2RPHR],"","A");
	Build_DspFld("Attendance Exception",$row[C2ATCD],"","A");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Employee Defaults</legend> ";
	print "\n <table $contentTable> ";
	Build_DspFld("Hours/Units/Earnings",$row[C2HRS],"","N");
	Build_DspFld("Department Worked",$row[C2DEPT],"","A");
	Build_DspFld("Override Rate",$row[C2OVRT],"","N");
	Build_DspFld("Shift Worked",$row[C2SHFW],"","A");
	Build_DspFld("Job Number",$row[C2JOB],"","A");
	$F_dateStart=Format_Date($row[C2STRT], "H");
	Build_DspFld("Start Date",$F_dateStart,"","D");
	$F_dateEnd=Format_Date($row[C2END], "H");
	Build_DspFld("End Date",$F_dateEnd,"","D");
	Build_DspFld("Maximum YTD Hours/Units",$row[C2MAX1],"","N");
	Build_DspFld("Maximum Earnings",$row[C2MAX2],"","N");
	Build_DspFld("Default Row",$row[C2DFLT],"","A");
	print "\n </table> ";
	print "\n </fieldset> ";

	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardprint.php';
}

print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
