<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName    = $_GET['docName'];
$fldName    = $_GET['fldName'];
$fldDesc    = $_GET['fldDesc'];
$moreInfo   = $_GET['moreInfo'];
$fieldName  = $_GET['fieldName'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "User View Column Search";
$scriptName     = "UserViewFieldSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$advanceSearch  = "N";
$dftOrderBy     = array(array("UFDESCU","A","Description"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Column")  {$orby = array(array("UFFLDN","A","Column"));}
	elseif ($sequence == "Desc")   {$orby = array(array("UFDESCU","A","Description"),array("UFFLDN","A","Column"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("UFFLDN ", "Table", $_POST['srchField'], "U", $_POST['operField'], "A");
	$returnValue=Build_WildCard("upper(UFDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
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

print "\n function selectField(fieldName,fieldDesc){ ";
print "\n window.opener.document.$docName.$fldName.value = fieldName; ";
print "\n window.opener.document.$docName.$fldDesc.value = fieldDesc; ";
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
$stmtSQL .= " Select UFFLDN,UFDESC,upper(UFDESC) as UFDESCU ";
$fileSQL .= " SYUFLD ";
if ($moreInfo=="Y") {
	$stmtSQL .= ",UFSCRN ";
	$selectSQL .= " UFFLDN='$fieldName' ";
}
elseif ($wildCardSearch!="") {
	$selectSQL="UFFLDN<>' ' ";
}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"UFFLDN|null|Column|A|U\" title=\"Column\">Column";
	$qsOpt .= "\n <option value=\"CHCHDSU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("UFFLDN "); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Column\" title=\"Sequence By Column\">{$sortPoint}Column</a></th>";
	$returnValue=OrderBy_Sort("upper(UFDESC)"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\"  title=\"Sequence By Description, Column\">{$sortPoint}Description</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Desc=Format_Quote($row['UFDESC']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectField('" . trim($row['UFFLDN']) . "','" . trim($F_Desc) . "')\" title=\"Select Column\">" . trim($row['UFFLDN']) . "</a></td> ";
		print "\n     <td class=\"colalph\">" . trim($row['UFDESC']) . "</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;fieldName=" . urlencode(trim($row['UFFLDN'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['UFDESC']);
	$moreInfoSelect = "href=\"javascript:selectField('" . trim($row['UFFLDN']) . "','" . trim($F_Desc) . "')\" title=\"Select Column\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	print "\n <tr><td class=\"dsphdr\">Column</td> ";
	print "\n     <td class=\"dspalph\">{$row['UFFLDN']}</td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\">Description</td> ";
	print "\n     <td class=\"dspalph\">{$row['UFDESC']}</td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\">U/V Script Name</td> ";
	print "\n     <td class=\"dspalph\">{$row['UFSCRN']}</td> ";
	print "\n </tr> ";

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
