<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$displayMenu        = $_GET['displayMenu'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Application Of Cash: Batch In Use";
$scriptName     = "ApplCashBatchUserInquiry.php";
$scriptVarBase  = "{$genericVarBase}&amp;displayMenu=" . urlencode(trim($displayMenu)) . "&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("USDESCU","A","User"),array("BUUSER","A","User Profile"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "User")    {$orby = array(array("USDESCU","A","User"),array("BUUSER","A","User Profile"));}
	elseif ($sequence == "Profile") {$orby = array(array("BUUSER","A","User Profile"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';
require_once 'Menu.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
if ($displayMenu=="Y") {require_once 'Banner.php';}
else                   {require $inquiryBanner;}
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
if ($displayMenu=="Y") {
	$pageID = "APPLCASHBATCHUSERINQ";
	require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";

$uv_BankName ="BUBCHB";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select BUUSER,";
$stmtSQL .= " Coalesce(USDESC,' ') as USDESC, Coalesce(USDESCU,' ') as USDESCU ";
$fileSQL .= " ARPBCU ";
$fileSQL .= " left join SYUSER   on USUSER=BUUSER ";
$selectSQL .= " (BUBCHN,BUBCHD,BUBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y") {
	print "\n <td class=\"toolbar\"> ";
	$medIcon = "Y";
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	if ($displayMenu!="Y") {require 'CloseWindow.php';}
	print "\n </td> ";
}
print "\n </tr></table> ";

print "\n <table $contentTable> ";
$F_BUBCHD=Format_Date($fromBatchDate, "D");
Format_Header("Batch", $fromBatchNumber, $F_BUBCHD);
$V_BKBKNM=RetValue("BKBANK=$fromBatchBank", "HDBANK", "BKBKNM");
Format_Header("Bank", $V_BKBKNM, $fromBatchBank);
print "\n </table> ";

if ($displayMenu=="Y") {print $hrTagAttr;}
else                   {print $inquiryhrTagAttr;}

if ($formatToPrint != "Y") {
	$advanceSearch = "N";	
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"USDESCU|null|User Name|A|U\" title=\"User Name\" SELECTED>User Name";
	$qsOpt .= "\n <option value=\"BUUSER|null|User Profile|A|U\" title=\"User Profile\">User Profile";
	require 'QuickSearchOption.php';
}

print " \n <table $contentTable> <tr>";
$returnValue=OrderBy_Sort("USDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\"      title=\"Sequence By User\">{$sortPoint}User</a></th>";
$returnValue=OrderBy_Sort("BUUSER"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Profile\"   title=\"Sequence By User Profile\">{$sortPoint}User Profile</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require  'SetRowClass.php';

	print "\n <tr class=\"$rowClass\">";
	print "\n <td class=\"colalph\">$row[USDESC]</td>";
	print "\n <td class=\"colalph\">$row[BUUSER]</td>";
	print "\n</tr>";

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "\n </table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
if ($displayMenu=="Y") {print $hrTagAttr;}
else {
	print $inquiryhrTagAttr;
	if ($formatToPrint != "Y") {require 'CloseWindow.php';}
}
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
if ($displayMenu=="Y") {require_once 'Trailer.php';}
else                   {require $inquiryTrailer;}
print "\n </body> \n </html>";
?>	
