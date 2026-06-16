<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$customerNumber = $_GET['customerNumber'];
$customerName   = $_GET['customerName'];
$fromState      = $_GET['fromState'];
$fromCounty     = $_GET['fromCounty'];
$fromCity       = $_GET['fromCity'];
$fromLocal1     = $_GET['fromLocal1'];
$fromLocal2     = $_GET['fromLocal2'];
$fromLocal3     = $_GET['fromLocal3'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Tax Definition Inquiry";
$scriptName     = "TaxDefinitionInquiry.php";
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
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require $inquiryBanner;
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

require 'stmtSQLClear.php';
$stmtSQL .= " Select *";
$fileSQL .= " HDTAXD ";
$selectSQL .= " (TDSTCD='$fromState' and TDCNYC=0 and TDCTYC=0 and TDLOC1=0 and TDLOC2=0 and TDLOC3=0) ";
if ($fromCounty > 0) {$selectSQL .= " or (TDSTCD='$fromState' and TDCNYC=$fromCounty and TDCTYC=0 and TDLOC1=0 and TDLOC2=0 and TDLOC3=0) ";}
if ($fromCity > 0)   {$selectSQL .= " or (TDSTCD='$fromState' and TDCNYC=$fromCounty and TDCTYC=$fromCity and TDLOC1=0 and TDLOC2=0 and TDLOC3=0) ";}
if ($fromLocal1 > 0) {$selectSQL .= " or (TDSTCD='$fromState' and TDCNYC=$fromCounty and TDCTYC=$fromCity and TDLOC1=$fromLocal1 and TDLOC2=0 and TDLOC3=0) ";}
if ($fromLocal2 > 0) {$selectSQL .= " or (TDSTCD='$fromState' and TDCNYC=$fromCounty and TDCTYC=$fromCity and TDLOC1=$fromLocal1 and TDLOC2=$fromLocal2 and TDLOC3=0) ";}
if ($fromLocal3 > 0) {$selectSQL .= " or (TDSTCD='$fromState' and TDCNYC=$fromCounty and TDCTYC=$fromCity and TDLOC1=$fromLocal1 and TDLOC2=$fromLocal2 and TDLOC3=$fromLocal3) ";}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By TDSTCD,TDCNYC,TDCTYC,TDLOC1,TDLOC2,TDLOC3";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";
if ($formatToPrint != "Y") {
	print "\n <td>&nbsp;</td>";
	print "\n <td class=\"toolbar\"> ";
	$medIcon="Y";
	require_once 'HelpPage.php';
	require 'CloseWindow.php';
	print "\n </td> ";
}
print "\n </tr> </table> ";

$totalRate =0;
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	if ($rowCount == 0) {
		print "\n <table $contentTable> ";
		Format_Header("Customer", $customerName, $customerNumber);
		Format_Header("State", $row[TDTDSC], $row[TDSTCD]);
		Format_Header("Freight Taxable", $row[TDFRTX], "");
		if ($row[TDTXAC]>0) {
			$fieldDesc=RetValue("CHACCT=$row[TDTXAC] and CHSUB=$row[TDTXSB]", "HDCHRT", "CHCHDS");
			$F_AcctSub=Format_Acct($row['TDTXAC'],$row['TDTXSB'],"N");
			Format_Header("Sales Tax Account", $fieldDesc, $F_AcctSub);
		}
		print "\n </table> ";
        print $inquiryhrTagAttr;

		print "\n <table $contentTable> <tr>";
		print "\n <th class=\"colhdr\">Description</th>";
		print "\n <th class=\"colhdr\">County</th>";
		print "\n <th class=\"colhdr\">City</th>";
		print "\n <th class=\"colhdr\">Local<br>1</th>";
		print "\n <th class=\"colhdr\">Local<br>2</th>";
		print "\n <th class=\"colhdr\">Local<br>3</th>";
		print "\n <th class=\"colhdr\">Tax Rate<br>Percent</th>";
		print "\n <th class=\"colhdr\">Total Tax<br>Rate<br>Percent</th>";
		print "\n </tr>";
	}
	require  'SetRowClass.php';
	if ($row[TDCNYC] == 0) {$row[TDCNYC] = "";}
	if ($row[TDCTYC] == 0) {$row[TDCTYC] = "";}
	if ($row[TDLOC1] == 0) {$row[TDLOC1] = "";}
	if ($row[TDLOC2] == 0) {$row[TDLOC2] = "";}
	if ($row[TDLOC3] == 0) {$row[TDLOC3] = "";}
	$rate = $row[TDTAX1]*100;
	$totalRate += $rate;
	$F_rate = Format_Nbr($rate, "4", "3", "", "", "");
	$F_totalRate = Format_Nbr($totalRate, "4", "3", "", "", "");
	print "\n <tr class=\"$rowClass\">";
	print "\n <td class=\"colalph\">$row[TDTDSC]</td>";
	print "\n <td class=\"colnmbr\">$row[TDCNYC]</td>";
	print "\n <td class=\"colnmbr\">$row[TDCTYC]</td>";
	print "\n <td class=\"colnmbr\">$row[TDLOC1]</td>";
	print "\n <td class=\"colnmbr\">$row[TDLOC2]</td>";
	print "\n <td class=\"colnmbr\">$row[TDLOC3]</td>";
	print "\n <td class=\"colnmbr\">$F_rate</td>";
	print "\n <td class=\"colnmbr\">$F_totalRate</td>";
	print "\n</tr>";

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "\n </table>";
print $inquiryhrTagAttr;
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require $inquiryTrailer;
print "\n </body> \n </html>";
?>	
