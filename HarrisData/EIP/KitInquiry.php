<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$itemNumber = $_GET['itemNumber'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Kit Inquiry";
$scriptName     = "KitInquiry.php";
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
$stmtSQL .= " Select *";
$fileSQL .= " HDKIT";
$selectSQL .= " KTKTIT='$itemNumber'";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By KTKTIT";
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
Format_Header("Item", $row[KTKTDS], $itemNumber);

print "\n </table> ";
print $inquiryhrTagAttr;
print "\n <table $contentTable> <tr>";
$kitRel = $row[KTKTRL];
Build_Flag_Entry("Relieved By","","KITOPT","relBy","$kitRel","","","","","Y","");
Build_Flag_Entry("Priced By","","KITOPT","prcBy","$row[KTKTPR]","","","","","Y","");
Build_Flag_Entry("Cost By","","KITOPT","cstBy","$row[KTKTCS]","","","","","Y","");
Build_Flag_Entry("Document Print","dors","DORS","","$row[KTINVC]","","","","","Y","");
Build_DspFld("Orders in Process",$row[KTORDP],"","N");
print "\n </tr>";

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDKTIT.*,HDKTOP.*";
$fileSQL .= " HDKTIT left join HDKTOP on KIKTIT=KOKTIT and KIITEM=KOCITM";
$selectSQL .= " KIKTIT='$itemNumber'";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By KIKTIT,KOCITM";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable> <tr>";
print "\n <th class=\"colhdr\">Component Item</th>";
if ($row[KTKTRL] == "O") {print "\n <th class=\"colhdr\">Optional Item</th>";}
print "\n <th class=\"colhdr\">Description</th>";
print "\n <th class=\"colhdr\">Quantity</th>";
print "\n <th class=\"colhdr\">Mand</th>";
print "\n <th class=\"colhdr\">N/S</th>";
print "\n <th class=\"colhdr\">Mult</th>";
if ($row[KTKTRL] == "O") {
	print "\n <th class=\"colhdr\">Extra Charge</th>";
	print "\n <th class=\"colhdr\">Dft</th>";
}
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	// if ($rowCount >= $dspMaxRows) {break;}
	if ($saveItem != $row[KIITEM]) {
		require  'SetRowClass.php';
		$saveItem = $row[KIITEM];
		$F_IMQT = Format_Nbr($row[KIIMQT], "4", ($qtyEditCode), "", "", "");
		print "\n <tr class=\"$rowClass\">";
		print "\n <td class=\"colalph\">$row[KIITEM]</td>";
		if ($kitRel == "O") {print "\n <td class=\"colalph\">&nbsp;</td>";}
		print "\n <td class=\"colalph\">$row[KIIMDS]</td>";
		print "\n <td class=\"colnmbr\">$F_IMQT</td>";
		print "\n <td class=\"colcode\">$row[KIMAND]</td>";
		print "\n <td class=\"colcode\">$row[KINS]</td>";
		print "\n <td class=\"colcode\">$row[KIMULT]</td>";
		if ($kitRel == "O") {
			print "\n <td class=\"colnmbr\">&nbsp;</td>";
			print "\n <td class=\"colalph\">&nbsp;</td>";
		}
		print "\n</tr>";
	}
	if (trim($row[KOOITM]) != "") {
		$F_XCHG = Format_Nbr($row[KOXCHG], "3", ($amtEditCode), "", "", "");
		print "\n <tr class=\"$rowClass\">";
		print "\n <td class=\"colalph\">&nbsp;</td>";
		print "\n <td class=\"colalph\">$row[KOOITM]</td>";
		print "\n <td class=\"colalph\">$row[KOIMDS]</td>";
		print "\n <td class=\"colnmbr\">&nbsp;</td>";
		print "\n <td class=\"colalph\">&nbsp;</td>";
		print "\n <td class=\"colalph\">&nbsp;</td>";
		print "\n <td class=\"colalph\">&nbsp;</td>";
		print "\n <td class=\"colnmbr\">$F_XCHG</td>";
		print "\n <td class=\"colalph\">$row[KODFLT]</td>";
		print "\n</tr>";
	}
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
