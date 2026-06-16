<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName        = $_GET['docName'];
$forHRCo        = (isset($_GET['forHRCo']))         ? $_GET['forHRCo']        : null;
$fldEmpl        = (isset($_GET['fldEmpl']))         ? $_GET['fldEmpl']        : null;
$fldEmplName    = (isset($_GET['fldEmplName']))     ? $_GET['fldEmplName']    : null;
$fldEmplID      = (isset($_GET['fldEmplID']))       ? $_GET['fldEmplID']        : null;
$fldCo          = (isset($_GET['fldCo']))           ? $_GET['fldCo']          : null;
$fldFacl        = (isset($_GET['fldFacl']))         ? $_GET['fldFacl']        : null;
$fldCoName      = (isset($_GET['fldCoName']))       ? $_GET['fldCoName']      : null;
$fldHRCo        = (isset($_GET['fldHRCo']))         ? $_GET['fldHRCo']        : null;
$fldHREmpl      = (isset($_GET['fldHREmpl']))       ? $_GET['fldHREmpl']      : null;
$inclActive     = (isset($_GET['inclActive']))      ? $_GET['inclActive']     : "Y";
$inclTerminated = (isset($_GET['inclTerminated']))  ? $_GET['inclTerminated'] : null;
$chgActive      = (isset($_GET['chgActive']))       ? $_GET['chgActive']      : null;
$chgTerminated  = (isset($_GET['chgTerminated']))   ? $_GET['chgTerminated']  : null;

if (isset($chgActive))  {if ($chgActive == "Y" && $inclActive=="Y")	{$inclActive="";} else {$inclActive="Y";}}
if (isset($chgTerminated))  {if ($chgTerminated == "Y"&& $inclTerminated=="Y")	{$inclTerminated="";} else {$inclTerminated="Y";}}

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Employee Search";
$scriptName     = "EmployeeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;forHRCo=" . urlencode(trim($forHRCo)) . "&amp;fldEmpl=" . urlencode(trim($fldEmpl)). "&amp;fldEmplName=" . urlencode(trim($fldEmplName)) . "&amp;fldEmplID=" . urlencode(trim($fldEmplID)) . "&amp;fldCo=" . urlencode(trim($fldCo)). "&amp;fldFacl=" . urlencode(trim($fldFacl)) . "&amp;fldCoName=" . urlencode(trim($fldCoName)) . "&amp;fldHRCo=" . urlencode(trim($fldHRCo)). "&amp;fldHREmpl=" . urlencode(trim($fldHREmpl)). "&amp;inclActive=" . urlencode(trim($inclActive)) . "&amp;inclTerminated=" . urlencode(trim($inclTerminated));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("EMLNAMU","A","Last Name"),array("EMCOMP","A","Company Number"),array("EMFACL","A",""),array("EMEMPL","A","Employee Number"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	require_once 'NoFormValidate.php';
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Last Name","srchLast","","operLast","opersel_alph_short","A","18","18");
	Build_AdvSrch_Entry("First Name","srchFirst","","operFirst","opersel_alph_short","A","18","18");
	Build_AdvSrch_Entry("Company","srchCo","","operCo","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Facility","srchFac","","operFac","opersel_num_short","N","2","4");
	Build_AdvSrch_Entry("Employee Number","srchEmployee","","operEmployee","opersel_num_short","N","3","5");
	Build_AdvSrch_Entry("H/R Company","srchHRCo","","operHRCo","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("H/R Employee","srchHREmpl","","operHREmpl","opersel_num_short","N","9","9");
	Build_AdvSrch_Entry("Location","srchLocation","","operLocation","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Department","srchDept","","operDept","opersel_alph_short","A","5","5");

	$focusField = "srchLast";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "LastName")       {$orby = array(array("EMLNAMU","A","Last Name"),array("EMCOMP","A","Company Number"),array("EMFACL","A",""),array("EMEMPL","A","Employee Number"));}
	elseif ($sequence == "FirstName")      {$orby = array(array("EMFNAMU","A","First Name"),array("EMLNAMU","A","Last Name"));}
	elseif ($sequence == "CompanyNumber")  {$orby = array(array("EMCOMP","A","Company Number"),array("EMFACL","A",""),array("EMEMPL","A","Employee Number"));}
	elseif ($sequence == "EmployeeNumber") {$orby = array(array("EMEMPL","A","Employee Number"),array("EMCOMP","A","Company Number"),array("EMFACL","A",""));}
	elseif ($sequence == "HRCompany")      {$orby = array(array("EMPECP","A","HR Company"),array("EMPEMP","A","HR Employee"));}
	elseif ($sequence == "HREmployee")     {$orby = array(array("EMPEMP","A","HR Employee"),array("EMPECP","A","HR Company"));}
	elseif ($sequence == "Location")       {$orby = array(array("EMLOC","A","Location"),array("EMLNAMU","A","Last Name"),array("EMFNAMU","A","First Name"),array("EMCOMP","A","Company Number"),array("EMFACL","A",""),array("EMEMPL","A","Employee Number"));}
	elseif ($sequence == "Department")     {$orby = array(array("EMDEPT","A","Department"),array("EMLNAMU","A","Last Name"),array("EMFNAMU","A","First Name"),array("EMCOMP","A","Company Number"),array("EMFACL","A",""),array("EMEMPL","A","Employee Number"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("EMLNAMU", "Last Name", $_POST['srchLast'], "U", $_POST['operLast'], "A");
	$returnValue=Build_WildCard("EMFNAMU", "First Name", $_POST['srchFirst'], "U", $_POST['operFirst'], "A");
	$returnValue=Build_WildCard("EMCOMP", "Company Number", $_POST['srchCo'], "", $_POST['operCo'], "N");
	$returnValue=Build_WildCard("EMFACL", "Facility Number", $_POST['srchFac'], "", $_POST['operFac'], "N");
	$returnValue=Build_WildCard("EMEMPL", "Employee Number", $_POST['srchEmployee'], "", $_POST['operEmployee'], "N");
	$returnValue=Build_WildCard("EMPECP", "H/R Company", $_POST['srchHRCo'], "", $_POST['operHRCo'], "N");
	$returnValue=Build_WildCard("EMPEMP", "H/R Employee", $_POST['srchHREmpl'], "", $_POST['operHREmpl'], "N");
	$returnValue=Build_WildCard("EMLOC", "Location", $_POST['srchLocation'], "U", $_POST['operLocation'], "A");
	$returnValue=Build_WildCard("EMDEPT", "Department", $_POST['srchDept'], "U", $_POST['operDept'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectEmpl(emplNum,emplDesc,emplID,coNum,faclNum,coNumDesc,coHRNum,emplHRNum){ ";
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
if ($fldEmplID!=null) {
	print "\n if (window.opener.document.$docName.$fldEmplID) ";
	print "\n    {window.opener.document.$docName.$fldEmplID.value = emplID;} ";
}
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
if ($fldHRCo!=null) {
	print "\n if (window.opener.document.$docName.$fldHRCo) ";
	print "\n    {window.opener.document.$docName.$fldHRCo.value = coHRNum;} ";
}
if ($fldHREmpl!=null) {
	print "\n if (window.opener.document.$docName.$fldHREmpl) ";
	print "\n    {window.opener.document.$docName.$fldHREmpl.value = emplHRNum;} ";
}
	print "\n window.opener.document.$docName.focus(); ";
if ($fldEmplID!=null) {
	print "\n window.opener.document.$docName.$fldEmplID.focus(); ";
} elseif ($forHRCo==null && $fldEmpl!=null) {
	print "\n window.opener.document.$docName.$fldEmpl.focus(); ";
} else {
	print "\n window.opener.document.$docName.$fldHREmpl.focus(); ";
}

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
if ($forHRCo!=null) {
	print "<table $contentTable>";
	$fieldDesc=RetValue("CFCOMP='$forHRCo'", "HRCOFC", "CFNAME");
	Format_Header("Company", $fieldDesc, "$forHRCo");
	print "\n </table>";
}
print $searchhrTagAttr;

$uv_CompanyName ="EMCOMP";
require 'UserViewEmpl.php';
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HREMPL.*,  coalesce(CFNAME,' ') as CFNAME ";
$fileSQL .= " HREMPL left join HRCOFC on CFCOMP=EMCOMP and CFFACL=EMFACL ";
$selectSQL="EMACT<>'X' ";
if ($wildCardSearch!="") {$selectSQL="EMEMPL<>0 ";}

if ($forHRCo!=null) {$selectSQL.=" and EMPECP=$forHRCo and EMPEMP<>999999999 ";}

if ($inclActive == "Y" && $inclTerminated != "Y") {
	$selectSQL.=" and EMTRCD = ' ' ";
}elseif ($inclActive != "Y" && $inclTerminated == "Y"){
	$selectSQL.=" and EMTRCD <> ' ' ";
}elseif ($inclActive != "Y" && $inclTerminated != "Y"){
	$selectSQL.=" and EMTRCD = ' ' and EMTRCD <> ' ' ";
}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$viewCheckBox="HR";
$activeChecked=Field_Checked($inclActive,"Y");
$terminatedChecked=Field_Checked($inclTerminated,"Y");
$qsOpt  = "\n <option value=\"EMLNAMU|null|Last Name|A|U\" title=\"Last Name\" SELECTED>Last Name";
$qsOpt .= "\n <option value=\"EMFNAMU|null|First Name|A|U\" title=\"First Name\">First Name";
if ($forHRCo==null) {
	$qsOpt .= "\n <option value=\"EMCOMP|null|Company Number|N|\" title=\"Company Number\">Company Number";
	$qsOpt .= "\n <option value=\"EMFACL|null|Facility Number|N|\" title=\"Facility Number\">Facility Number";
	$qsOpt .= "\n <option value=\"EMEMPL|null|Employee Number|N|\" title=\"Employee Number\">Employee Number";
	$qsOpt .= "\n <option value=\"EMPECP|null|H/R Company Number|N|\" title=\"H/R Company Number\">H/R Company Number";
}
$qsOpt .= "\n <option value=\"EMPEMP|null|H/R Employee Number|N|\" title=\"H/R Employee Number\">H/R Employee Number";
$qsOpt .= "\n <option value=\"EMLOC|null|Location|A|U\" title=\"Location\">Location";
$qsOpt .= "\n <option value=\"EMDEPT|null|Department|A|U\" title=\"Department\">Department";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("EMLNAMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LastName\" title=\"Sequence By Last Name, Co/Fac, Employee\">{$sortPoint}Last Name</a></th>";
$returnValue=OrderBy_Sort("EMFNAMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=FirstName\" title=\"Sequence By First Name, Last Name, Co/Fac, Employee\">{$sortPoint}First Name</a></th>";
if ($forHRCo==null) {
	$returnValue=OrderBy_Sort("EMCOMP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CompanyNumber\" title=\"Sequence By Co/Fac, Employee\">{$sortPoint}Co/Fac</a></th>";
	$returnValue=OrderBy_Sort("EMEMPL"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EmployeeNumber\" title=\"Sequence By Employee, Co/Fac\">{$sortPoint}Employee Number</a></th>";
	$returnValue=OrderBy_Sort("EMPECP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=HRCompany\" title=\"Sequence By H/R Company, H/R Employee\">{$sortPoint}H/R Company</a></th>";
}
$returnValue=OrderBy_Sort("EMPEMP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=HREmployee\" title=\"Sequence By H/R Employee, H/R Company\">{$sortPoint}H/R Employee</a></th>";
$returnValue=OrderBy_Sort("EMLOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Location\" title=\"Sequence By Location, Last Name, First Name, Co/Fac, Employee\">{$sortPoint}Location</a></th>";
$returnValue=OrderBy_Sort("EMDEPT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Department\" title=\"Sequence By Department, Last Name, First Name, Co/Fac, Employee\">{$sortPoint}Dept</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_CoFac=Format_CoFac($row['EMCOMP'],$row['EMFACL'],"N");
	$name=Format_EmplName(trim($row[EMFNAM]),trim($row[EMLNAM]),"","","","");
	$F_EmplName=Format_Quote($name);
	$F_CoFacName=Format_Quote($row['CFNAME']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectEmpl('" . trim($row['EMEMPL']) . "','" . trim($F_EmplName) . "','" . trim($row['EMEMID']) . "','" . trim($row['EMCOMP']) . "','" . trim($row['EMFACL']) . "','" . trim($F_CoFacName) . "','" . trim($row['EMPECP']) . "','" . trim($row['EMPEMP']) . "')\" title=\"Select Employee\">$row[EMLNAM]</a></td> ";
	print "\n     <td class=\"colalph\">$row[EMFNAM]</td>";
	if ($forHRCo==null) {
		print "\n     <td class=\"colnmbr\">$F_CoFac</td>";
		print "\n     <td class=\"colnmbr\">$row[EMEMPL]</td>";
		print "\n     <td class=\"colnmbr\">$row[EMPECP]</td>";
	}
	print "\n     <td class=\"colnmbr\">$row[EMPEMP]</td>";
	print "\n     <td class=\"colalph\">$row[EMLOC]</td>";
	print "\n     <td class=\"colalph\">$row[EMDEPT]</td>";
	print "\n </tr>";
	$startRow ++;
	$rowCount ++;
}
if ($rowCount == 0){require 'NoRecordsFound.php';}
print "</table>";

require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
