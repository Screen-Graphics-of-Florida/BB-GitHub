<?php

require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';

require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Select Environment";
$d2wName        = "SignonSelect.php";
$d2wVarBase     = "&amp;baseVar=" . urlencode($baseVar) . "&amp;eID=" . urlencode($eID);
$nextPrevVar    = $d2wVarBase;
$RPT_MAX_ROWS   = "9999";
$ROW_NUM        = "0";
$START_ROW_NUM  = "1";
$dspMaxRows     = "9999";
$prtMaxRows     = $prtMaxRowsDft;
$rowIndexNext   = "1";
$totalPages     = "0";
$advanceSearch  = "N";
$maxRows        = $dspMaxRows;

if ($tag == "ORDERBY"){
	$RPT_MAX_ROWS = $dspMaxRows;
	if     ($sequence == "DBID")   {$orby = array(array("DBDBID","A","DB ID"));}
	elseif ($sequence == "Desc")   {$orby = array(array("DBDBDSU","A","Desc"));}
	elseif ($sequence == "Base")   {$orby = array(array("DBBSVN","A","Base 1"));}
	require_once 'OrderByUpdate.php';
}

if ($tag != "ORDERBY") {$orderBy = "DBDBID";}
$RPT_MAX_ROWS = $dspMaxRows;

require $docType;
print "<html> <head>";
require $headInclude;
require $genericHead;
print "</head> <body $bodyTagAttr>";
print "\n<!-- Start Of Banner Code -->";
require_once 'Banner.php';
print "\n <!-- End Of Banner Code -->";
print "\n <table $baseTable> <tr valign=\"top\">";
print "\n <td class=\"menu\">&nbsp;</td>";
print "\n <td class=\"content\">";
require 'stmtSQLClear.php';
$stmtSQL =  " Select DBDBID, DBBSVN, DBDBDS, upper(DBDBDS) as DBDBDSU ";
$fileSQL =  $pgmLibrary . "/SYDBID ";
$selectSQL =  "DBBSVN>' ' ";
require 'stmtSQLSelect.php';
$stmtSQL = $stmtSQL . " Order By " . $orderBy ;
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

require_once 'PageTitleInclude.php';
print "$hrTagAttr <table $contentTable> <tr><td>";
require 'AssignPageValue.php';
print "</td></tr> </table>";

print "<table $contentTable> ";
$orderByVar = $d2wVarBase . $searchVarBase;
print "<tr>";
$returnValue=OrderBy_Sort("DBDBID"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "<th class=\"colhdr{$sortVar}\"><a href=\"{$homeURL}{$cGIPath}{$d2wName}/?tag=ORDERBY{$orderByVar}&amp;sequence=DBID\" title=\"Sequence By Database ID\"> DBID</a></th>";
$returnValue=OrderBy_Sort("DBDBDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "<th class=\"colhdr{$sortVar}\"><a href=\"{$homeURL}{$cGIPath}{$d2wName}/?tag=ORDERBY{$orderByVar}&amp;sequence=Desc\" title=\"Sequence By Description\"> Description</a></th>";
$returnValue=OrderBy_Sort("DBBSVN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "<th class=\"colhdr{$sortVar}\"><a href=\"{$homeURL}{$cGIPath}{$d2wName}/?tag=ORDERBY{$orderByVar}&amp;sequence=Base\" title=\"Sequence By Base Variable Name\"> Base Variable Name</a></th>";
print "</tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){

	$row[DBBSVN] = str_replace(".icl", ".php", $row[DBBSVN]);
	require 'SetRowClass.php';
	print "<tr class=\"{$rowClass}\">";
	print "<td class=\"colalph\">$row[DBDBID]</a></td>";
	print "<td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}Signon.php?baseVar=" . trim($row[DBBSVN]) . "&amp;eID=" . urlencode($eID) . "&amp;fromSelect=Y\" title=\"Select Environment\">" . $row[DBDBDS] . "</a></td>";
	print "<td class=\"colalph\">$row[DBBSVN]</a></td> </tr>";

	$startRow ++;
	$rowCount ++;

};
if ($rowCount == 0){require 'NoRecordsFound.php';}
print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print $hrTagAttr;
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";

?>

