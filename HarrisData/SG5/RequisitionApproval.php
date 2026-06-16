<?php

$approved        = (isset($_GET['approved'])) ? $_GET['approved'] : '';
$fromReqNumber   = (isset($_GET['fromReqNumber'])) ? $_GET['fromReqNumber'] : '';
$fromItemNumber  = (isset($_GET['fromItemNumber'])) ? $_GET['fromItemNumber'] : '';
$maintenanceCode = (isset($_GET['maintenanceCode'])) ? $_GET['maintenanceCode'] : '';

require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = ($maintenanceCode == "R") ? "Remove Requisition Approval" : "Requisition Approval";
$scriptName = "RequisitionApproval.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;

// Retrieve Exit Point PO.REQ.APPROVAL Program
$exitPgm = RetValue("XPEXPT='PO.REQ.APPROVAL'", "SYUXPM", "XPUXPG");

// Get Total Cost for Requisition
$stmtSQL = " Select sum(dec(RQSQOR*RQCOST,11,2)) as TOT From POREQR Where RQREQN='$fromReqNumber'";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$reqRow = db2_fetch_assoc($sqlResult);
$totalCost = $reqRow['TOT'];

// Not Remove Approval - Check for Approval Level
$hasLimits = null;
if ($maintenanceCode != "R") {
	$stmtSQL = " Select count(*) as CNT From PORQAL";
	$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
	$limitRow = db2_fetch_assoc($sqlResult);
	$hasLimits = ($limitRow['CNT'] > 0) ? true : null;

	$userLimit = [];
	if ($hasLimits) {
		$stmtSQL = " Select * From PORQAL Where ALUSER='{$userProfile}' or ALUSER='*DFT'
                     Order By ALUSER desc Limit 1";
		$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
		$userLimit = db2_fetch_assoc($sqlResult);
	}
}

require 'stmtSQLClear.php';
$stmtSQL .= " Select POREQR.*,dec(RQSQOR*RQCOST,11,2) as RQEXTC";
$fileSQL .= " POREQR ";
$where = ($maintenanceCode == "R") ? "RQASUS<>''" : "RQASUS=''";
$selectSQL = "RQREQN='$fromReqNumber' and " . $where;
if ($fromItemNumber <> '') {
    $selectSQL .= " and RQITEM='" . $fromItemNumber . "'";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By RQITEM";
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );

if ($approved != "") {
// Remove Approval
	if ($approved == "R") {
		$ready = ($readyForApproval == "Y") ? 'Y' : '';
		$stmtSQL = " Update POREQR Set RQASTP = '0001-01-01-00.00.00.000000',RQASUS='' Where RQREQN='" . $fromReqNumber . "'";
		$status = db2_exec($i5Connect->getConnection(), $stmtSQL);
	}

// Approve Requisitions
	if ($approved == 'Y') {
		$rows = array();
		while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
			$startRow++;
			$rows [] = $row;
		}
		Approve_Requisitions($rows);
		if ($exitPgm != '') {
			ExitPOApproval();
		}
	}

	print "\n <script TYPE=\"text/javascript\">";
	print "\n     opener.location.href=opener.location.href;";
	print "\n     window.close();";
	print "\n </script>";
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Chg";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
if ($maintenanceCode == "R") {
	print "\n function confirmAccept(text) {return confirm(\"Confirm Remove of Approval for Requisition\")}";
} else {
	print "\n function confirmAccept(text) {return confirm(\"Confirm Approval of Requisition\")}";
}
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
print "\n <table $contentTable> ";
print "\n     <colgroup> <col width=\"60%\"><col width=\"35%\"> ";
print "\n     <tr><td><h1>$page_title</h1></td> ";
print "\n         <td class=\"toolbar\"> ";
if ($maintenanceCode == "R" || is_null($hasLimits) || floatval($userLimit['ALLMT']) >= floatval($totalCost)) {
	$approve = ($maintenanceCode == "R") ? 'R' : 'Y';
	print "\n <a onClick=\"return confirmAccept()\" href=\"{$homeURL}{$phpPath}{$scriptName}{$genericVarBase}&approved=" . urlencode(trim($approve)) . "&amp;fromReqNumber=" . urlencode(trim($fromReqNumber)) . "&amp;fromItemNumber=" . urlencode(trim($fromItemNumber)) . "\">$mbOrderAccept</a>";
}
print "\n &nbsp;<a href=\"javascript:window.close()\">$closeImageMed</a> ";
print "\n </td></tr></table> ";
if ($maintenanceCode != "R" && $hasLimits && floatval($userLimit['ALLMT']) < floatval($totalCost)) {
	if ($exitPgm != '') {
		ExitPOApproval();
	}
	$F_TOT = Format_Nbr ( $totalCost, $amtNbrDec, "J", "Y", "$", "" );
	$F_LMT = Format_Nbr ( $userLimit['ALLMT'], $amtNbrDec, "J", "Y", "$", "" );
	print "<h3>Total Cost of {$F_TOT} exceeds your approval limit of {$F_LMT}</h3>";
}
print $hrTagAttr;

print "\n <table $contentTable> ";
Format_Header("Requisition Number", $fromReqNumber, "");
print "\n </table> ";

print "<table $contentTable><tr>";
print "\n <th class=\"colhdr\">Item<br>Number</th>";
print "\n <th class=\"colhdr\">Description</th>";
print "\n <th class=\"colhdr\">Whs</th>";
print "\n <th class=\"colhdr\">Quantity</th>";
print "\n <th class=\"colhdr\">Cost</th>";
print "\n <th class=\"colhdr\">Extended<br>Cost</th>";
print "\n <th class=\"colhdr\">Required<br>Date</th>";
print "\n </tr>";

$rowCount = 0;
$saveItem = NULL;
$saveWhs = NULL;
while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
	require 'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\">$row[RQITEM]</td>";
	print "\n     <td class=\"colalph\">$row[RQIMDS]</td> ";
	print "\n     <td class=\"colnmbr\">$row[RQWHS]</td>";
	$F_QTY = Format_Nbr ( $row [RQSQOR], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n     <td class=\"colnmbr\">$F_QTY</td>";
	$F_COST = Format_Nbr ( $row [RQCOST], $cstNbrDec, $cstEditCode, "Y", "", "" );
	print "\n     <td class=\"colnmbr\">$F_COST</td>";
	$F_EXTC = Format_Nbr ( $row [RQEXTC], $amtNbrDec, $amtEditCode, "Y", "", "" );
	print "\n     <td class=\"colnmbr\">$F_EXTC</td>";
	$row['RQRQDT']=DateFromCYMD($row['RQRQDT']);
	print "\n     <td class=\"coldate\">$row[RQRQDT]</td>";
	print "\n </tr>";
	$startRow ++;
	$rowCount ++;
}
if ($rowCount == 0) {
	require 'NoRecordsFound.php';
} else {
    print "\n <tr class=\"oddrow\">";
	print "\n     <td class=\"colalph\" colspan=\"5\">&nbsp;</td>";
    $F_TOTC = Format_Nbr ( $totalCost, $amtNbrDec, $amtEditCode, "Y", "", "" );
	print "\n     <td class=\"colTotal\">$F_TOTC</td>";
    print "\n </tr>";
    
}
print "</table>";

print "$hrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
print "\n </body> \n </html>";

function Approve_Requisitions ($rows = array()){
    global $pgmLibrary, $i5Connect, $userProfile, $edtVar;
    foreach ( $rows as $row ) {
        $maintenanceCode = "R";
    	$errFound= "";
        $errVar= "";
        $wrnVar= "";
        $edtVar= "";
        Concat_Field("@@reqn", $row['RQREQN']);
    	Concat_Field("@@item", $row['RQITEM']);
    	$edtVar .= "}{";
    	$returnValue=Maintain_Edit("HPORQR_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
    }
}

// Call Exit Program for Exit Point PO.REQ.APPROVAL
function ExitPOApproval (){
	global $exitPgm, $fromReqNumber, $userProfile, $totalCost, $approved, $userLimit;

	$cost = strval($totalCost);
	$approve = ($approved == "Y") ? $approved : 'N';
	$pgmCall = array(
		array("Name"=>"userProfile"  , "IO"=>I5_IN,  "Type"=>I5_TYPE_CHAR,   "Length"=>"10"),
		array("Name"=>"reqNumber"    , "IO"=>I5_IN,  "Type"=>I5_TYPE_CHAR,   "Length"=>"8"),
		array("Name"=>"approve"      , "IO"=>I5_IN,  "Type"=>I5_TYPE_CHAR,   "Length"=>"1"),
		array("Name"=>"limit"        , "IO"=>I5_IN,  "Type"=>I5_TYPE_CHAR,   "Length"=>"15"),
		array("Name"=>"totalCost"    , "IO"=>I5_IN,  "Type"=>I5_TYPE_CHAR,   "Length"=>"15"));

	$pgm = i5_program_prepare($exitPgm, $pgmCall);
	if (!$pgm) {die("<br>{$exitPgm} Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
		"userProfile"  =>$userProfile,
		"reqNumber"    =>$fromReqNumber,
		"approve"      =>$approve,
		"limit"        =>$userLimit['ALLMT'],
		"totalCost"    =>$cost
	);

	$parmOut = [];

	$ret = i5_program_call($pgm, $parmIn, $parmOut);

	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> {$exitPgm} Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmIn");}
}

?>	
