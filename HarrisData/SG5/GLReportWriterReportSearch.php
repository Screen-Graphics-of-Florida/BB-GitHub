
<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName    = $_GET['docName'];
$fldName    = $_GET['fldName'];
$fldDesc    = $_GET['fldDesc'];
$moreInfo   = $_GET['moreInfo'];
$moreReport = $_GET['moreReport'];

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "G/L Report Writer Report Search";
$scriptName     = "GLReportWriterReportSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("GAGRRDU","A","Description"),array("GAGRRN","A","Report"));

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
	require_once 'NoFormValidate.php';
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Report","srchReport","","operReport","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("User ID","srchUserID","","operUserID","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Category","srchCategory","","operCategory","opersel_alph_short","A","4","4");
	$focusField = "srchReport";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Description") {$orby = array(array("GAGRRDU","A","Description"),array("GAGRRN","A","Report"));}
	elseif ($sequence == "Report")      {$orby = array(array("GAGRRN","A","Report"));}
	elseif ($sequence == "User")        {$orby = array(array("GAGRUI","A","User ID"),array("GAGRRN","A","Report"));}
	elseif ($sequence == "Category")    {$orby = array(array("GAGRCT","A","Category"),array("GAGRRN","A","Report"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("GAGRRDU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("GAGRRN", "Report", $_POST['srchReport'], "U", $_POST['operReport'], "A");
	$returnValue=Build_WildCard("GAGRUI", "User ID", $_POST['srchUserID'], "U", $_POST['operUserID'], "A");
	$returnValue=Build_WildCard("GAGRCT", "Category", $_POST['srchCategory'], "U", $_POST['operCategory'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectReport(report, desc){ ";
print "\n   window.opener.document.$docName.$fldName.value = report; ";
print "\n   if      (window.opener.document.$docName.$fldDesc)          {window.opener.document.$docName.$fldDesc.value = desc;} ";
print "\n   else if (window.opener.document.getElementById('$fldDesc')) {window.opener.document.getElementById('$fldDesc').innerHTML = desc;}";
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
print $searchhrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .= " Select GAGRRN,GAGRRD,GAGRRDU,GAGRH1,GAGRF1,GAGRH2,GAGRF2,GAGRH3,GAGRF3 ";
$stmtSQL .= "       ,GAGRCT,GARSSZ,GAGRUI,GALMNT,GALPRC ";
$fileSQL .= " GLWRDM ";
if     ($moreInfo=="Y")      {$selectSQL .= " GAGRRN='$moreReport' ";}
elseif ($wildCardSearch!="") {$selectSQL .= " GAGRRN=GAGRRN ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"GAGRRDU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"GAGRRN|null|Report|A|U\" title=\"Report\">Report";
	$qsOpt .= "\n <option value=\"GAGRUI|null|User ID|A|U\" title=\"User ID\">User ID";
	$qsOpt .= "\n <option value=\"GAGRCT|null|Category|A|U\" title=\"Category\">Category";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("GAGRRDU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\"  title=\"Sequence By Description, Report\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("GAGRRN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Report\"       title=\"Sequence By Report\">{$sortPoint}Report</a></th>";
	$returnValue=OrderBy_Sort("GAGRUI"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\"         title=\"Sequence By User ID, Report\">{$sortPoint}User ID</a></th>";
	$returnValue=OrderBy_Sort("GAGRCT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Category\"     title=\"Sequence By Category, Report\">{$sortPoint}Category</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		include  'SetRowClass.php';
		$F_GAGRRN=Format_Quote($row['GAGRRN']);
		$F_GAGRRD=Format_Quote($row['GAGRRD']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectReport('" . trim($F_GAGRRN) . "','" . trim($F_GAGRRD) . "')\" title=\"Select Report\">$row[GAGRRD]</a></td> ";
		print "\n     <td class=\"colalph\">$row[GAGRRN]</td>";
		print "\n     <td class=\"colalph\">$row[GAGRUI]</td>";
		print "\n     <td class=\"colalph\">$row[GAGRCT]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreReport=" . urlencode(trim($row['GAGRRN'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}

	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_GAGRRN=Format_Quote($row['GAGRRN']);
	$F_GAGRRD=Format_Quote($row['GAGRRD']);

	$moreInfoSelect = "href=\"javascript:selectReport('" . trim($F_GAGRRN) . "','" . trim($F_GAGRRD) . "')\" title=\"Select Report\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	Build_DspFld("Report",$row['GAGRRN'],"","A");
	Build_DspFld("Description",$row['GAGRRD'],"","A");
	Build_DspFld("Report Heading One",$row['GAGRH1'],"","A");
	Build_DspFld("Or Co/Fac Column Name",$row['GAGRF1'],"","A");
	Build_DspFld("Report Heading Two",$row['GAGRH2'],"","A");
	Build_DspFld("Or Co/Fac Column Name",$row['GAGRF2'],"","A");
	Build_DspFld("Report Heading Three",$row['GAGRH3'],"","A");
	Build_DspFld("Or Co/Fac Column Name",$row['GAGRF3'],"","A");
	Build_DspFld("Report Category",$row['GAGRCT'],"","A");
	Build_DspFld("Report Page Size",$row['GARSSZ'],"","N");
	Build_DspFld("User ID",$row['GAGRUI'],"","A");
	$F_GALMNT=Format_Date($row['GALMNT'],"D");
	Build_DspFld("Date Last Maintained",$F_GALMNT,"","D");
	$F_GALPRC=Format_Date($row['GALPRC'],"D");
	Build_DspFld("Date Last Processed",$F_GALPRC,"","D");

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
include $searchTrailer;
print "\n </body> \n </html>";
?>
