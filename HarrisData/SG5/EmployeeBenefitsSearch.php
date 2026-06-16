<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName        = $_GET['docName'];
$fldCo          = (isset($_GET['fldCo']))           ? $_GET['fldCo']          : null;
$fldFacl        = (isset($_GET['fldFacl']))         ? $_GET['fldFacl']        : null;
$fldCoName      = (isset($_GET['fldCoName']))       ? $_GET['fldCoName']      : null;
$fldEmpl        = (isset($_GET['fldEmpl']))         ? $_GET['fldEmpl']        : null;
$fldEmplName    = (isset($_GET['fldEmplName']))     ? $_GET['fldEmplName']    : null;
$fldCovr        = (isset($_GET['fldCovr']))         ? $_GET['fldCovr']        : null;
$fldCovrDesc    = (isset($_GET['fldCovrDesc']))     ? $_GET['fldCovrDesc']    : null;
$fldPlan        = (isset($_GET['fldPlan']))         ? $_GET['fldPlan']        : null;
$fldPlanDesc    = (isset($_GET['fldPlanDesc']))     ? $_GET['fldPlanDesc']    : null;

$moreInfo = $_GET['moreInfo'];
$moreCo =   $_GET['moreCo'];
$moreFacl = $_GET['moreFacl'];
$moreEmpl = $_GET['moreEmpl'];
$moreCovr = $_GET['moreCovr'];
$morePlan = $_GET['morePlan'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Employee Benefits Search";
$scriptName     = "EmployeeBenefitsSearch.php";

$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldCo=" . urlencode(trim($fldCo)). "&amp;fldFacl=" . urlencode(trim($fldFacl)) . "&amp;fldCoName=" . urlencode(trim($fldCoName)) . "&amp;fldEmpl=" . urlencode(trim($fldEmpl)). "&amp;fldEmplName=" . urlencode(trim($fldEmplName)) . "&amp;fldCovr=" . urlencode(trim($fldCovr)) . "&amp;fldCovrDesc=" . urlencode(trim($fldCovrDesc)) . "&amp;fldPlan=" . urlencode(trim($fldPlan)) . "&amp;fldPlanDesc=" . urlencode(trim($fldPlanDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("EMCOMP","A","Company"),array("EMFACL","A",""),array("EMPEMP","A","Employee"));
$dataType       = "SSNO";
$modeD          = "D";
$modeI          = "I";

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
	print "\n if (editNum(document.Search.srchCo, 2, 0) && ";
	print "\n     editNum(document.Search.srchFacl, 4, 0) && ";
	print "\n     editNum(document.Search.srchEmpl, 9, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';
	Build_AdvSrch_Entry("Company","srchCo","","operCo","opersel_num_short","N","4","2");
	Build_AdvSrch_Entry("Facility","srchFacl","","operFacl","opersel_num_short","N","4","4");
	Build_AdvSrch_Entry("Co/Fac Name","srchCoFaclName","","operCoFaclName","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Employee","srchEmpl","","operEmpl","opersel_num_short","N","9","9");
	Build_AdvSrch_Entry("Last Name","srchLastName","","operLastName","opersel_alph_short","A","18","18");
	Build_AdvSrch_Entry("First Name","srchFirstName","","operFirstName","opersel_alph_short","A","18","18");
	Build_AdvSrch_Entry("Benefit","srchBenefit","","operBenefit","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Plan","srchPlan","","operPlan","opersel_alph_short","A","3","3");
	$focusField = "srchCo";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Company")     {$orby = array(array("EMCOMP","A","Company"),array("EMFACL","A",""));}
	elseif ($sequence == "Facility")    {$orby = array(array("EMFACL","A","Facility"),array("EMCOMP","A","Company"));}
	elseif ($sequence == "Co/Fac Name") {$orby = array(array("CFNAME","A","Name"),array("EMCOMP","A","Company"),array("EMFACL","A",""));}
	elseif ($sequence == "Employee")    {$orby = array(array("EMPEMP","A","Employee"),array("EMCOMP","A","Company"),array("EMFACL","A",""));}
	elseif ($sequence == "Last Name")   {$orby = array(array("EMLNAMU","A","Last Name"),array("EMCOMP","A","Company"),array("EMFACL","A",""));}
	elseif ($sequence == "First Name")  {$orby = array(array("EMFNAMU","A","First Name"),array("EMCOMP","A","Company"),array("EMFACL","A",""));}
	elseif ($sequence == "Benefit")     {$orby = array(array("ECCOVR","A","Benefit"));}
	elseif ($sequence == "Plan")        {$orby = array(array("ECPLAN","A","Plan"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("EMCOMP", "Company", $_POST['srchCo'], "", $_POST['operCo'], "N");
	$returnValue=Build_WildCard("EMFACL", "Facility", $_POST['srchFacl'], "", $_POST['operFacl'], "N");
	$returnValue=Build_WildCard("CFNAME", "Name", $_POST['srchCoFaclName'], "U", $_POST['operCoFaclName'], "A");
	$returnValue=Build_WildCard("EMPEMP", "Employee", $_POST['srchEmpl'], "", $_POST['operEmpl'], "N");
	$returnValue=Build_WildCard("EMLNAMU", "Last Name", $_POST['srchLastName'], "U", $_POST['operLastName'], "A");
	$returnValue=Build_WildCard("EMFNAMU", "First Name", $_POST['srchFirstName'], "U", $_POST['operFirstName'], "A");
	$returnValue=Build_WildCard("ECCOVR", "Benefit", $_POST['srchBenefit'], "U", $_POST['operBenefit'], "A");
	$returnValue=Build_WildCard("ECPLAN", "Plan", $_POST['srchPlan'], "U", $_POST['operPlan'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectEmpl(coNum,faclNum,coNumDesc,emplNum,emplDesc,covr,covrDesc,plan,planDesc){ ";

if ($fldCo!=null) {
	print "\n if (window.opener.document.$docName.$fldCo) ";
	print "\n    {window.opener.document.$docName.$fldCo.value = coNum;} ";
}
if ($fldFacl!=null) {
	print "\n if (window.opener.document.$docName.$fldFacl) ";
	print "\n    {window.opener.document.$docName.$fldFacl.value = faclNum;} ";
}
if ($fldCoName!=null) {
	print "\n if (window.opener.document.$docName.$fldCoName) { ";
	print "\n    window.opener.document.$docName.$fldCoName.value = coNumDesc; ";
	print "\n } else if (window.opener.document.getElementById('$fldCoName')) { ";
	print "\n    window.opener.document.getElementById('$fldCoName').innerHTML = coNumDesc; ";
	print "\n } ";
}
if ($fldEmpl!=null) {
	print "\n if (window.opener.document.$docName.$fldEmpl) ";
	print "\n    {window.opener.document.$docName.$fldEmpl.value = emplNum;} ";
}
if ($fldEmplName!=null) {
	print "\n if (window.opener.document.$docName.$fldEmplName) { ";
	print "\n    window.opener.document.$docName.$fldEmplName.value = emplDesc; ";
	print "\n } else if (window.opener.document.getElementById('$fldEmplName')) { ";
	print "\n    window.opener.document.getElementById('$fldEmplName').innerHTML = emplDesc; ";
	print "\n } ";
}
if ($fldCovr!=null) {
	print "\n if (window.opener.document.$docName.$fldCovr) ";
	print "\n    {window.opener.document.$docName.$fldCovr.value = covr;} ";
}
if ($fldCovrDesc!=null) {
	print "\n if (window.opener.document.$docName.$fldCovrDesc) { ";
	print "\n    window.opener.document.$docName.$fldCovrDesc.value = covrDesc; ";
	print "\n } else if (window.opener.document.getElementById('$fldCovrDesc')) { ";
	print "\n    window.opener.document.getElementById('$fldCovrDesc').innerHTML = covrDesc; ";
	print "\n } ";
}
if ($fldPlan!=null) {
	print "\n if (window.opener.document.$docName.$fldPlan) ";
	print "\n    {window.opener.document.$docName.$fldPlan.value = plan;} ";
}
if ($fldPlanDesc!=null) {
	print "\n if (window.opener.document.$docName.$fldPlanDesc) { ";
	print "\n    window.opener.document.$docName.$fldPlanDesc.value = planDesc; ";
	print "\n } else if (window.opener.document.getElementById('$fldPlanDesc')) { ";
	print "\n    window.opener.document.getElementById('$fldPlanDesc').innerHTML = planDesc; ";
	print "\n } ";
}
print "\n window.opener.document.$docName.$fldEmpl.focus(); ";
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

$uv_CompanyName ="EMCOMP";
require 'UserViewEmpl.php';
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select EMCOMP, EMFACL, EMPEMP, EMSSNO, EMLNAMU, EMLNAM, EMFNAM, EMMIDI,";
$stmtSQL .= " ECCOVR, ECPLAN, ECDCE, ECPREM, ECAMT, ECDCT, ECPHSS, ECPHC, coalesce(CFNAME,' ') as CFNAME,";
$stmtSQL .= " coalesce(CVDESC,' ') as CVDESC, coalesce(CCDESC,' ') as CCDESC ";
$fileSQL .= " HREMPL ";
$fileSQL .= " inner join HREMCV on EMCOMP=ECCOMP and EMFACL=ECFACL and EMPEMP=ECEMPL ";
$fileSQL .= " inner join HRCOFC on EMCOMP=CFCOMP and EMFACL=CFFACL ";
$fileSQL .= " left join CBCOVR on CVCOMP=ECCOMP and CVFACL=ECFACL and CVCOVR=ECCOVR and CVPLAN=ECPLAN ";
$fileSQL .= " left join CBCODE on CCTYPE='P' and CCCODE=ECPLAN ";
$selectSQL .= " ECCLAS='E' and EMPEAC=' ' and CVTYPE='FSA ' ";

if ($moreInfo=="Y") {$selectSQL .= " and EMCOMP=$moreCo and EMFACL=$moreFacl and EMPEMP=$moreEmpl and ECCOVR='$moreCovr' and ECPLAN='$morePlan' ";}
elseif ($wildCardSearch!="") {$selectSQL .= " and EMPEMP<>0 ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = i5_query($stmtSQL);

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"EMCOMP|null|Company Number|N|\" title=\"Company Number\">Company Number";
	$qsOpt .= "\n <option value=\"EMFACL|null|Facility Number|N|\" title=\"Facility Number\">Facility Number";
	$qsOpt .= "\n <option value=\"CFNAME|null|Company/Facility Name|A|U\" title=\"Company/Facility Name\">Company/Facility Name";
	$qsOpt .= "\n <option value=\"EMPEMP|null|Employee Number|N|\" title=\"Employee Number\">Employee Number";
	$qsOpt .= "\n <option value=\"EMLNAMU|null|Last Name|A|U\" title=\"Last Name\" SELECTED>Last Name";
	$qsOpt .= "\n <option value=\"EMFNAMU|null|First Name|A|U\" title=\"First Name\">First Name";
	$qsOpt .= "\n <option value=\"ECCOVR|null|Benefit Code|A|U\" title=\"Benefit Code\">Benefit Code";
	$qsOpt .= "\n <option value=\"ECPLAN|null|Plan Code|A|U\" title=\"Plan Code\">Plan Code";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("EMCOMP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Company\"      title=\"Sequence By Company\">{$sortPoint}Company</a></th>";
	$returnValue=OrderBy_Sort("CFNAME"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Co/Fac Name\"  title=\"Sequence By Co/Fac Name, Company\">{$sortPoint}Co/Fac Name</a></th>";
	$returnValue=OrderBy_Sort("EMPEMP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Employee\" title=\"Sequence By Employee\">{$sortPoint}Employee</a></th>";
	$returnValue=OrderBy_Sort("EMLNAMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Last Name\" title=\"Sequence By Last Name, Employee\">{$sortPoint}Last Name</a></th>";
	$returnValue=OrderBy_Sort("EMFNAMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=First Name\" title=\"Sequence By First Name, Employee\">{$sortPoint}First Name</a></th>";
	$returnValue=OrderBy_Sort("ECCOVR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Benefit\" title=\"Sequence By Benefit\">{$sortPoint}Benefit</a></th>";
	$returnValue=OrderBy_Sort("ECPLAN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Plan\" title=\"Sequence By Plan\">{$sortPoint}Plan</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	i5_data_seek($sqlResult, $startRow);
	while ($row = i5_fetch_assoc($sqlResult)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_CoFac=Format_CoFac($row['EMCOMP'],$row['EMFACL'],"N");
		$name=Format_EmplName(trim($row[EMFNAM]),trim($row[EMLNAM]),$row[EMMIDI],"","","");
		$F_Name=Format_Quote($name);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$F_CoFac</td>";
		print "\n     <td class=\"colalph\">$row[CFNAME]</td>";
		print "\n     <td class=\"colnmbr\">$row[EMPEMP]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectEmpl('" . trim($row['EMCOMP']) . "','" . trim($row['EMFACL']) . "','" . trim($row['CFNAME']) . "','" . trim($row['EMPEMP']) . "','" . trim($F_Name) . "','" . trim($row['ECCOVR']) . "','" . trim($row['CVDESC']) . "','" . trim($row['ECPLAN']) . "','" . trim($row['CCDESC']) . "')\" title=\"Select Employee\">$row[EMLNAM]</a></td> ";
		print "\n     <td class=\"colalph\">$row[EMFNAM]</td>";
		print "\n     <td class=\"colalph\">$row[ECCOVR]</td>";
		print "\n     <td class=\"colalph\">$row[ECPLAN]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCo=" . urlencode(trim($row['EMCOMP'])) . "&amp;moreFacl=" . urlencode(trim($row['EMFACL'])) . "&amp;moreEmpl=" . urlencode(trim($row['EMPEMP'])) . "&amp;moreCovr=" . urlencode(trim($row['ECCOVR'])) . "&amp;morePlan=" . urlencode(trim($row['ECPLAN'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a>";
		print "\n </td>";
		print "\n </tr>";
		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = i5_fetch_assoc($sqlResult);

	$moreInfoSelect = "href=\"javascript:selectEmpl('" . trim($row['EMCOMP']) . "','" . trim($row['EMFACL']) . "','" . trim($row['CFNAME']) . "','" . trim($row['EMPEMP']) . "','" . trim($row['EMLNAM']) . "','" . trim($row['ECCOVR']) . "','" . trim($row['CVDESC']) . "','" . trim($row['ECPLAN']) . "','" . trim($row['CCDESC']) . "')\" title=\"Select Employee\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	$F_CoFac=Format_HRCoFac($row['EMCOMP'],$row['EMFACL'],"Y");
	Build_DspFld("Company/Facility Number",$F_CoFac,"","A");
	$fieldDesc=$row[EMPEMP];
	$fieldDesc.="&nbsp; $row[EMLNAM] , &nbsp; $row[EMFNAM] &nbsp; $row[EMMIDI]";
	Build_DspFld("Employee Number",$fieldDesc,"","A");
	$fieldDesc=$row[ECCOVR];
	$fieldDesc2=RetValue("CVCOVR='$row[ECCOVR]'", "CBCOVR", "CVDESC");
	$fieldDesc.="&nbsp; $fieldDesc2";
	Build_DspFld("Benefit Code",$fieldDesc,"","A");
	$fieldDesc=$row[ECPLAN];
	$fieldDesc2=RetValue("CCTYPE='P' and CCCODE='$row[ECPLAN]'", "CBCODE", "CCDESC");
	$fieldDesc.="&nbsp; $fieldDesc2";
	Build_DspFld("Plan Code",$fieldDesc,"","A");
	$F_EMSSNO=RetColValue($profileHandle, $dataBaseID, "EMCOMP=$row[EMCOMP] and EMFACL=$row[EMFACL] and EMEMPL='$row[EMEMPL]", "HREMPL", "EMSSNO", "I");
	$F_EMSSNO=Format_SSN($F_EMSSNO);
	Build_DspFld("Social Security Number",$F_EMSSNO,"","A");
	$F_Date=Format_Date($row['ECDCE'], "1");
	Build_DspFld("Date Effective",$F_Date,"","N");
	Build_DspFld("Premium",$row[ECPREM],"","N");
	Build_DspFld("Amount",$row[ECAMT],"","N");
	$F_Date=Format_Date($row['ECDCT'], "1");
	Build_DspFld("Coverage Term Date",$F_Date,"","N");
	$F_ECPHSS=RetColValue($profileHandle, $dataBaseID, "ECCOMP=$row[EMCOMP] and ECFACL=$row[EMFACL] and ECEMPL=$row[EMPEMP] and ECSPDP=$row[ECSPDP] and ECCOVR='$row[ECCOVR]' and ECPLAN='$row[ECPLAN]'", "HREMCV", "ECPHSS", "I");
	$F_ECPHSS=Format_SSN($F_ECPHSS);
	Build_DspFld("Plan Holder SS#",$F_ECPHSS,"","A");
	Build_DspFld("Plan Holder Code",$row[ECPHC],"","A");

	print "\n </table> ";
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
