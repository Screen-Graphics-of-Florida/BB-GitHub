<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';


$forPlant	=	(isset($_GET['forPlant']))		?	$_GET['forPlant']		:	0;
$docName	=	(isset($_GET['docName']))		?	$_GET['docName']		:	null;
$fldPlant	=	(isset($_GET['fldPlant']))		?	$_GET['fldPlant']		:	0;
$fldPltName	=	(isset($_GET['fldPltName']))	?	$_GET['fldPltName']		:	"";
$flddept	=	(isset($_GET['flddept']))		?	$_GET['flddept']		:	"";
$fldWC		=	(isset($_GET['fldWC']))			?	$_GET['fldWC']			:	"";
$fldDesc	=	(isset($_GET['fldDesc']))		?	$_GET['fldDesc']		:	"";
$moreInfo	=	(isset($_GET['moreInfo']))		?	$_GET['moreInfo']		:	"";
$morePlt	=	(isset($_GET['morePlt']))		?	$_GET['morePlt']		:	0;
$moreDept	=	(isset($_GET['moreDept']))		?	$_GET['moreDept']		:	"";
$moreWC		=	(isset($_GET['moreWC']))		?	$_GET['moreWC']			:	"";
$touchScreen=	(isset($_GET['touchScreen']))	?	$_GET['touchScreen']	:	"";
$dispatch	=	(isset($_GET['dispatch']))		?	$_GET['dispatch']		:	"";

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Department/Work Center Search";
$scriptName     = "DeptWCSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;forPlant=" . urlencode(trim($forPlant)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldPlant=" . urlencode(trim($fldPlant)) . "&amp;fldPltName=" . urlencode(trim($fldPltName)) . "&amp;flddept=" . urlencode(trim($flddept)) . "&amp;fldWC=" . urlencode(trim($fldWC)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;touchScreen=" . urlencode(trim($touchScreen)) . "&amp;dispatch=" . urlencode(trim($dispatch));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("WCPLT","N","Plant"),array("WCDEPT","A","Department"),array("WCWC","A","Work Center"));
$plant          = 0;
$department     = "";
$workcenter     = "";

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

	if ($forPlant == 0) {
		Build_AdvSrch_Entry("Plant","srchPlant","","operPlant","opersel_num_short","N","3","3");
	}

	Build_AdvSrch_Entry("Department","srchDept","","operDept","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Work Center","srchWC","","operWC","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");

	$focusField = "srchDept";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "plant")  {$orby = array(array("WCPLT","N","Plant"));}
	elseif ($sequence == "dept")  {$orby = array(array("WCDEPT","A","Dept"),array("WCWC","A","Work Center"));}
	elseif ($sequence == "workcenter")  {$orby = array(array("WCWC","A","Work Center"),array("WCDEPT","A","Dept"));}
	elseif ($sequence == "description")  {$orby = array(array("upper(WCDESC)","A","Description"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("WCPLT", "Plant", $_POST['srchPlant'], "", $_POST['operPlant'], "N");
	$returnValue=Build_WildCard("WCDEPT", "Dept", $_POST['srchDept'], "U", $_POST['operDept'], "A");
	$returnValue=Build_WildCard("WCWC", "Work Center", $_POST['srchWC'], "U", $_POST['operWC'], "A");
	$returnValue=Build_WildCard("upper(WCDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
if ($forPlant == 0) {
	print "\n function selectPltDeptWC(plant,pltname,department,workcenter,desc){ ";
	print "\n window.opener.document.$docName.$fldPlant.value = plant; ";
	print "\n window.opener.document.$docName.$fldPltName.value = pltname; ";
	print "\n window.opener.document.$docName.$flddept.value = department; ";
	print "\n window.opener.document.$docName.$fldWC.value = workcenter; ";
	print "\n if (window.opener.document.$docName.$fldDesc) ";
	print "\n    {window.opener.document.$docName.$fldDesc.value = desc;} ";
	print "\n else if (window.opener.document.getElementById('$fldDesc'))";
	print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = desc;}";

	if ($touchScreen != 'Y') {print "\n    window.opener.document.$docName.$flddept.focus;  ";}
	if ($dispatch == 'Y')    {print "\n    window.opener.document.$docName.submit(); ";}
	print "\n window.close(); ";
}  else {
	print "\n function selectPltDeptWC(department,workcenter,desc){ ";
	print "\n window.opener.document.$docName.$flddept.value = department; ";
	print "\n window.opener.document.$docName.$fldWC.value = workcenter; ";
	print "\n if (window.opener.document.$docName.$fldDesc) ";
	print "\n    {window.opener.document.$docName.$fldDesc.value = desc;} ";
	print "\n else if (window.opener.document.getElementById('$fldDesc'))";
	print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = desc;}";

	if ($touchScreen != 'Y') {print "\n    window.opener.document.$docName.$flddept.focus;  ";}
	if ($dispatch == 'Y')    {print "\n    window.opener.document.$docName.submit(); ";}
	print "\n window.close(); ";
}
print "\n } ";

require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
if ($touchScreen == "Y") {require_once 'KeyboardFunctionsTS.js';}
function validate($searchForm) {
	if ($forPlant == 0)  {
		if (editNum(document.Search.srchPlant, 3, 0))  {
			return true;
		}  else   {
			return true;
		}
	}
}
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
if ($touchScreen == "Y") {$displayCloseIcon = "Y";}
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

$uv_PlantName = "WCPLT";
$uv_MfgDepartmentName = "WCDEPT";
$uv_WorkCenterName = "WCWC";
require_once 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDMWCM.* ";
$fileSQL .= " HDMWCM ";
if ($moreInfo == "Y") {$selectSQL="WCPLT = $morePlt and WCDEPT = '$moreDept' and WCWC = '$moreWC' ";}
elseif ($forPlant!=0) {$selectSQL="WCPLT = $forPlant ";}
elseif ($wildCardSearch!="" || $uv_sql!="") {$selectSQL="WCPLT<>0 ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"WCPLT|null|Plant|N|\" title=\"Plant\">Plant";
	$qsOpt .= "\n <option value=\"WCDEPT|null|Department|A|U\" title=\"Department\" SELECTED>Department";
	$qsOpt .= "\n <option value=\"WCWC|null|Work Center|A|U\" title=\"Work Center\">Work Center";
	$qsOpt .= "\n <option value=\"upper(WCDESC)|null|Description|A|U\" title=\"Description\">Description";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("WCPLT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=plant\" title=\"Sequence By Plant\">{$sortPoint}Plant</a></th>";
	$returnValue=OrderBy_Sort("WCDEPT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=dept\" title=\"Sequence By Dept\">{$sortPoint}Dept</a></th>";
	$returnValue=OrderBy_Sort("WCWC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=workcenter\" title=\"Sequence By Work Center, Dept\">{$sortPoint}Work Center</a></th>";
	$returnValue=OrderBy_Sort("upper(WCDESC)"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=description\" title=\"Sequence By Description\">{$sortPoint}Description</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$fldPltName=RetValue("PLPLNT=$row[WCPLT]", "HDPLNT", "PLNAME");
		$F_Desc=Format_Quote($row['WCDESC']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[WCPLT]</td>";
		print "\n     <td class=\"colalph\">$row[WCDEPT]</td>";
		print "\n     <td class=\"colalph\">$row[WCWC]</td>";
		if ($forPlant == 0) {
			print "\n     <td class=\"colalph\"><a href=\"javascript:selectPltDeptWC('" . trim($row[WCPLT]) . "','" . trim($fldPltName) . "','" . trim($row[WCDEPT]) . "','" . trim($row[WCWC]) . "','" . trim($F_Desc) . "')\" title=\"Select Department/WC\">$F_Desc</a></td> ";
		} else {
			print "\n     <td class=\"colalph\"><a href=\"javascript:selectPltDeptWC('" . trim($row[WCDEPT]) . "','" . trim($row[WCWC]) . "','" . trim($F_Desc) . "')\" title=\"Select Department/WC\">$F_Desc</a></td> ";
		}
		print "\n    <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;morePlt=" . urlencode(trim($row['WCPLT'])) . "&amp;moreDept=" . urlencode(trim($row['WCDEPT'])) . "&amp;moreWC=" . urlencode(trim($row['WCWC'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a>";

		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['WCDESC']);

	if ($forPlant == 0) {
		$moreInfoSelect = "href=\"javascript:selectPltDeptWC('" . trim($row[WCPLT]) . "','" . trim($fldPltName) . "','" . trim($row[WCDEPT]) . "','" . trim($row[WCWC]) . "','" . trim($F_Desc) . "')\" title=\"Select Department/WC\">";
	} else {
		$moreInfoSelect = "href=\"javascript:selectPltDeptWC('" . trim($row[WCDEPT]) . "','" . trim($row[WCWC]) . "','" . trim($F_Desc) . "')\" title=\"Select Department/WC\">";
	}
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	$fldPltName=RetValue("PLPLNT=$row[WCPLT]", "HDPLNT", "PLNAME");
	$F_CoFac=Format_CoFac($row['WCCO'],$row['WCFAC'],"Y");
	Build_DspFld("Plant",$fldPltName,"","N");
	Build_DspFld("Department",$row['WCDEPT'],"","A");
	Build_DspFld("Work Center",$row['WCWC'],"","A");
	Build_DspFld("Description",$row['WCDESC'],"","A");
	Build_DspFld("Labor Utilization",$row['WCLAUT'],"","A");
	Build_DspFld("Labor Efficiency",$row['WCLAEF'],"","A");
	Build_DspFld("Direct Labor Hours Per Day",$row['WCLHRD'],"","A");
	Build_DspFld("Machine Utilization",$row['WCMAUT'],"","A");
	Build_DspFld("Machine Hours Per Day",$row['WCMHRD'],"","A");
	Build_DspFld("Setup Hours Per Day",$row['WCSHRD'],"","A");
	Build_DspFld("Transit Days",$row['WCMTD'],"","A");
	Build_DspFld("Key Load Code",$row['WCKLCD'],"","A");
	Build_DspFld("Company/Facility",$F_CoFac,"","A");
	$fieldDesc=RetValue("SMSCHD='$row[WCSCHD]'", "HDSCHM", "SMDESC");
	Build_DspFld("Schedule",$fieldDesc,"","A");
	print "\n </table> ";
	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardPrint.php';
}

print "$searchhrTagAttr";
require_once 'Copyright.php';
if ($touchScreen == "Y") {require_once 'KeyboardTS.htm';}
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
