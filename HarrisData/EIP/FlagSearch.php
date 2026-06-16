<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName     = $_GET['docName'];
$fldName     = $_GET['fldName'];
$fldDesc     = (isset($_GET['fldDesc']))           ? $_GET['fldDesc']           : "none";
$flagSrchHdr = $_GET['flagSrchHdr'];
$flagType    = $_GET['flagType'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "$flagSrchHdr Search";
$scriptName     = "FlagSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;flagType=" . urlencode(trim($flagType)) . "&amp;flagSrchHdr=" . urlencode(trim($flagSrchHdr)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("FLDESCU","A","Description"),array("FLVALU","A","Flag"));

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

	Build_AdvSrch_Entry("Flag","srchFlag","","operFlag","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","20","30");

	$focusField = "srchFlag";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Flag")         {$orby = array(array("FLVALU","A","Flag"));}
	elseif ($sequence == "Description")  {$orby = array(array("FLDESCU","A","Description"),array("FLVALU","A","Flag"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("FLVALU ", "Flag", $_POST['srchFlag'], "U", $_POST['operFlag'], "A");
	$returnValue=Build_WildCard("upper(FLDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectFlag(flag,flagDesc){ ";
print "\n window.opener.document.$docName.$fldName.value = flag; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = flagDesc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = flagDesc;}";
print "\n window.opener.document.$docName.$fldName.focus(); ";
print "\n window.close(); ";
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
$stmtSQL .= " Select FLVALU,FLDESC,upper(FLDESC) as FLDESCU ";
$fileSQL .= " SYFLAG ";
$selectSQL .= " FLTYPE='$flagType'";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"FLVALU|null|Flag|A|U\" title=\"Flag\">Flag";
$qsOpt .= "\n <option value=\"upper(FLDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("FLVALU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Flag\"      title=\"Sequence By Flag\">{$sortPoint}Flag</a></th>";
$returnValue=OrderBy_Sort("FLDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\"       title=\"Sequence By Description, Flag\">{$sortPoint}Description</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Desc=Format_Quote($row['FLDESC']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\">$row[FLVALU]</td> ";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectFlag('" . trim($row['FLVALU']) . "','" . trim($F_Desc) . "')\" title=\"Select Flag\">$F_Desc</a></td> ";
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
