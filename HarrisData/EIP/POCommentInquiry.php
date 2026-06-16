<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$poNumber = $_GET['purchaseOrder'];
$document = $_GET['document'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Purchase Order Comment Inquiry";
$scriptName     = "POCommentInquiry.php";
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
$stmtSQL .= " Select * ";
$fileSQL .= " POPOMD inner join POOCMT on PDPO=OCORD# and PDPOL#=OCORL# and PDPORL=OCBLN# and OCDOCT='$document'";
$selectSQL .= " PDPO=$poNumber ";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By OCORL#,OCBLN#,OCCSEQ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n </tr> </table> ";

$row = db2_fetch_assoc($sqlResult, $startRow);
print "\n <table $contentTable> ";
Format_Header("Purchase Order", $poNumber, '');
Format_Header("Document", $document, '');
print "\n </table> ";
print $inquiryhrTagAttr;

$rowCount = 0;
$line = 0;
require  'SetRowClass.php';
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($row['OCORL#'] <> $line) {
		if ($line > 0) {
			print "\n </table>";
			print "\n </fieldset><p></p>";
		}
		$line = $row['OCORL#'];
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n     <legend class=\"hdrdata\">Line: {$row['PDPOL#']} &nbsp; &nbsp; Item:  $row[PDIMDS] &nbsp; [$row[PDITEM]]</legend> ";
		print "\n <table $contentTable>";
	}
	print "\n <tr class=\"$rowClass\"><td class=\"colalph\">$row[OCCMNT]</td></tr>";
	$startRow ++;
	$rowCount ++;
}

if ($line > 0) {
	print "\n </table>";
	print "\n </fieldset></td>";
}

require $inquiryTrailer;
print "\n </body> \n </html>";
?>	
