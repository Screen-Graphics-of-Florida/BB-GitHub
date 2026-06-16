<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$reqNumber = $_GET['reqNumber'];
$itemNumber = $_GET['itemNumber'];
$itemDescription = $_GET['itemDescription'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Requisition Comment Inquiry";
$scriptName     = "ReqCommentInquiry.php";
$scriptVarBase  = "{$genericVarBase}";
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$maxRows = 9999;

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require $inquiryBanner;
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

require 'stmtSQLClear.php';
$stmtSQL .= " Select RCCMNT";
$fileSQL .= " PORQCM";
$selectSQL .= " RCREQN='$reqNumber' and RCITEM='$itemNumber' and RCDOCT='INT' ";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By RCCSEQ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";
if ($formatToPrint != "Y") {
	print "\n <td>&nbsp;</td>";
	print "\n <td class=\"toolbar\"> ";
	require_once 'HelpPage.php';
	require 'CloseWindow.php';
	print "\n </td> ";
}
print "\n </tr> </table> ";

$row = db2_fetch_assoc($sqlResult, $startRow);
print "\n <table $contentTable> ";
Format_Header("Requisition", $reqNumber, '');
Format_Header("Item", $itemDescription, $itemNumber);

print "\n </table> ";
print $inquiryhrTagAttr;
print "\n <table $contentTable> <tr>";

$rowCount = 0;
require  'SetRowClass.php';
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	print "\n <tr class=\"$rowClass\">";
	print "\n <td class=\"colalph\">$row[RCCMNT]</td>";
	print "\n</tr>";
	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "\n </table>";
print $inquiryhrTagAttr;
require 'CloseWindow.php';
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require $inquiryTrailer;
print "\n </body> \n </html>";
?>	
