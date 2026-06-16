<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$fldType  = $_GET['fldType'];
$moreInfo = $_GET['moreInfo'];
$moreCode = $_GET['moreCode'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Hold Code Search";
$scriptName     = "HoldCodeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;fldType=" . urlencode(trim($fldType));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("HCDESCU","A","Description"),array("HCHLCD","A","Hold Code"));

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
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Hold Code","srchCode","","operCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");

	$focusField = "srchCode";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Code")        {$orby = array(array("HCHLCD","A","Code"));}
	elseif ($sequence == "Description") {$orby = array(array("HCDESC","A","Description"),array("HCHLCD","A","Code"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("HCHLCD", "Hold Code", $_POST['srchCode'], "U", $_POST['operCode'], "A");
	$returnValue=Build_WildCard("HCDESCU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectCode(Code, codeDesc){ ";
print "\n window.opener.document.$docName.$fldName.value = Code; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = codeDesc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = codeDesc;}";
print "\n window.opener.document.$docName.$fldName.focus(); ";
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

require 'stmtSQLClear.php';
$stmtSQL .= " Select * " ;
$fileSQL .= " HDHLCD ";
$selectSQL = " HCHLCD<>'' ";
if ($fldType<>"") {$selectSQL .= " and HCTYPE='$fldType' ";}
if ($moreInfo=="Y") {$selectSQL .= " and HCHLCD='$moreCode' ";}

require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"HCHLCD|null|Hold Code|A|U\" title=\"Hold Code\">Hold Code";
	$qsOpt .= "\n <option value=\"HCDESCU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("HCHLCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Code\" title=\"Sequence By Hold Code\">{$sortPoint}Hold Code</a></th>";
	$returnValue=OrderBy_Sort("HCDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Hold Code\">{$sortPoint}Description</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Desc=Format_Quote($row['HCDESC']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\">$row[HCHLCD]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectCode('" . trim($row['HCHLCD']) . "','" . trim($F_Desc) . "')\" title=\"Select Hold Code\">$F_Desc</a></td> ";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCode=" . urlencode(trim($row['HCHLCD'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a>";
		print "\n </td>";
		print "\n </tr>";
		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['HCDESC']);
	$moreInfoSelect = "href=\"javascript:selectCode('" . trim($row['HCHLCD']) . "','" . trim($F_Desc) . "')\" title=\"Select Code\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	Build_DspFld("Hold Code",$row['HCHLCD'],"","A");
	Build_DspFld("Description",$row['HCDESC'],"","A");
	if (trim($row[HCICON]) != "") {
		Build_DspFld("Icon Name",$row['HCICON'],"","A");
	}
	if  ($fldType=='O' && $HDPDRL>0)  {
		$fieldDesc=RetValue("HCHLCD='$row[HCMHLD]' and HCTYPE='M'", "HDHLCD", "HCDESC");
		Build_DspFld("Mfg Hold Code",$fieldDesc,$row['HCHLCD'],"A");
	}  elseif  ($fldType=='M')  {
		$fieldDesc=RtvYNDesc($row[HCPRTO]);
		Build_DspFld("Print Order",$fieldDesc,"","A");
		$fieldDesc=RtvYNDesc($row[HCISSC]);
		Build_DspFld("Issue Components",$fieldDesc,"","A");
		$fieldDesc=RtvYNDesc($row[HCRPTL]);
		Build_DspFld("Report Labor",$fieldDesc,"","A");
		$fieldDesc=RtvYNDesc($row[HCORDR]);
		Build_DspFld("Order Receipt",$fieldDesc,"","A");
		$fieldDesc=RtvYNDesc($row[HCORDC]);
		Build_DspFld("Order Close",$fieldDesc,"","A");
		$fieldDesc=RtvYNDesc($row[HCPMAT]);
		Build_DspFld("Plan Material",$fieldDesc,"","A");
		$fieldDesc=RtvYNDesc($row[HCPCAP]);
		Build_DspFld("Plan Capacity",$fieldDesc,"","A");
		$fieldDesc=RtvYNDesc($row[HCSCHL]);
		Build_DspFld("Schedule Labor",$fieldDesc,"","A");
		$fieldDesc=RtvYNDesc($row[HCHAEO]);
		Build_DspFld("Hold Auto-Explode Orders",$fieldDesc,"","A");
	}

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
