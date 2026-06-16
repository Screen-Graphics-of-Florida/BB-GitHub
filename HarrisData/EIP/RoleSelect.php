<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$newRole = $_GET['newRole'];

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'NewWindowVariables.php';
require_once 'WildCard.php';
require_once 'VarBase.php';

$page_title     = "Change Role";
$scriptName     = "RoleSelect.php";
$scriptVarBase  = "{$genericVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$nextPrevVar    = "{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$advanceSearch  = "N";
$popUpWin	    = "Y";
$dftOrderBy     = array(array("RUROLE","A","Role"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "ACCEPT"){
	require 'stmtSQLClear.php';
	$stmtSQL .= " Update SYHAND Set HNROLE='$newRole' Where HNXHND='$profileHandle' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n opener.location.reload(true);";
	print "\n window.close();";
	print "\n </script> \n";
	exit();
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if ($sequence == "description"){
		$orby = array(array("RMDESCU","A","Description"),array("RUROLE","A","Role"));
	} elseif ($sequence == "role"){
		$orby = array(array("RUROLE","A","Role"));
	}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("RUROLE", "Role", $_POST['srchRole'], "U", $_POST['operRole'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> 	<head>";
require_once ($headInclude);
$formName = "Search";
print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'ShowHideSelCriteria.php';

print "\n function validate(searchForm) {";
print "\n return true;";
print "\n } \n";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
print "<table $contentTable><colgroup><col width=\"80%\"><col width=\"15%\">\n<tr><td><h1>$page_title</h1></td>";
require_once 'HelpPage.php';
print "\n </table> ";
$roleDesc=RetValue("RMROLE='{$activeRole}'", "SYROLM", "RMDESC");
print "\n <table $contentTable>";
Format_Header("Current Role", $roleDesc, $activeRole);
print "\n </table> ";
print $hrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .= " Select SYROLU.*, RMDESC, RMDESCU ";
$fileSQL .= "  SYROLU inner join SYROLM on RUROLE=RMROLE ";
$selectSQL .= " RUUSER='$_SERVER[PHP_AUTH_USER]' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$rowCount = 0;

if ($formatToPrint == ""){
	$qsOpt  = "\n <option value=\"RUROLE|null|Role|A|U\" title=\"Role\">Role";
	$qsOpt .= "\n <option value=\"RMDESCU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	require 'QuickSearchOption.php';
}
print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("RUROLE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=role\"  title=\"Sequence By Role\">{$sortPoint}Role</a></th>";
$returnValue=OrderBy_Sort("RMDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=description\"    title=\"Sequence By Description, Role\">{$sortPoint}Description</a></th>";
$rowsPrinted = 0;

while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$genericVarBase}&amp;tag=ACCEPT&amp;newRole=" . urlencode($row['RUROLE']) . "\" title=\"Select Role\">$row[RUROLE]</a></td>";
	print "\n <td class=\"colalph\">$row[RMDESC]</td> </tr>";

	$startRow ++;
	$rowCount ++;
}
if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';

print "</td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>