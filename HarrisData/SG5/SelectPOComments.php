<?php
require_once 'GetURLParm.php';

$vendorNumber = $_GET['vendorNumber'];
$vendorName   = $_GET['vendorName'];
$purchaseOrderNumber  = $_GET['purchaseOrderNumber'];
$orderSequence= $_SESSION['orderSeq'];

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$scriptVarBase = "{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber));

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'SelectPO.js';
print "\n </script> \n";

$dspMaxRows    = 9999;
require 'stmtSQLClear.php';
if ($orderSequence > 0) {
	$stmtSQL  .= " Select OHORL# as OCORL, OHDOCT as OCDOCT, OHCSEQ as OCCSEQ, OHCMNT as OCCMNT,DODESC ";
	$fileSQL  .= " POHCMT inner join HDDOCT on OHDOCT=DODOCT and (DOAPID='PO' or DOAPID=' ') ";
	$selectSQL = " OHORD#=$purchaseOrderNumber and OHSSEQ=$orderSequence and (OHORL#=000 or OHORL#=999)";
} else {
	$stmtSQL  .= " Select OCORL# as OCORL,OCDOCT,OCCMNT,DODESC ";
	$fileSQL  .= " POOCMT inner join HDDOCT on OCDOCT=DODOCT and (DOAPID='PO' or DOAPID=' ') ";
	$selectSQL = " OCORD#=$purchaseOrderNumber and (OCORL#=000 or OCORL#=999)";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By OCORL,OCDOCT,OCCSEQ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
$saveLine = 1;
$startRow = 1;
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($saveLine != $row[OCORL]) {
		if ($row[OCORL] > 1) {print "\n </div></fieldset></fieldset><br>";}
		if ($row[OCORL] == 0) {$hdrTitle = "Header";} else {$hdrTitle = "Trailer";}
		print "\n <fieldset class=\"legendbody\"><legend class=\"legendTitle\">$hdrTitle Comments</legend>";
		print "\n <fieldset class=\"legendBodyFO\"><legend class=\"legendTitleFO\">$row[DODESC]</legend><div class=\"dspalph\">";
		$saveLine = $row[OCORL];
	}
	elseif ($saveDoct != $row[OCDOCT]) {
		if ($saveDoct != "") {print "\n </div></fieldset><br>";}
		print "\n <fieldset class=\"legendBodyFO\"><legend class=\"legendTitleFO\">$row[DODESC]</legend><div class=\"dspalph\">";
	}

	print "\n $row[OCCMNT] <br>";
	$saveDoct = $row[OCDOCT];
	$startRow ++;
	$rowCount ++;
}
if ($rowCount > 0) {print "\n </div></fieldset></fieldset>";}

require_once 'WildCardPrint.php';
print "\n  </div></div>";
?>
