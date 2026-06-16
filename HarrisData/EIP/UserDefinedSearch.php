<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName        = $_GET['docName'];
$fldName        = $_GET['fldName'];
$fldDesc        = $_GET['fldDesc'];
$fieldName      = $_GET['fieldName'];
$fileName       = $_GET['fileName'];
$fldType        = $_GET['fldType'];
$userEventCode  = $_GET['userEventCode'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "User-Defined Search";
$scriptName     = "UserDefinedSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;fldType=" . urlencode(trim($fldType)) . "&amp;fileName=" . urlencode(trim($fileName)) . "&amp;userEventCode=" . urlencode(trim($userEventCode));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$advanceSearch  = "N";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$maxRows = $dspMaxRows;
if ($_SESSION['udfCol'] != $fldName) {$tag = "QSEARCH"; $_SESSION['udfCol']=$fldName;}
if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectValue(fieldValue){ ";
print "\n window.opener.document.$docName.$fldName.value = fieldValue; ";
print "\n window.opener.document.$docName.$fldName.focus(); ";
print "\n window.close(); ";
print "\n } ";

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
$stmtSQL .= " Select UVFLDV ";
$fileSQL .= " SYUDFV ";
$selectSQL .= " UVFILN='$fileName' and UVFLDN='$fldName' and (UVEVNT='$userEventCode' or UVEVNT=' ') ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By UVSEQ# ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt = "\n <option value=\"UVFLDV|null|$fldDesc|A|U\" title=\"$fldDesc\" SELECTED>$fldDesc";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
print "\n <th class=\"colhdr\">$fldDesc</th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_UVFLDV=Format_Quote($row['UVFLDV']);
	$fldValue=$row['UVFLDV'];
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectValue('" . trim($fldValue) . "')\" title=\"Select Column\">" . trim($fldValue) . "</a></td> ";
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
