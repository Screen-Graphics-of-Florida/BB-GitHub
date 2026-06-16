<?php
require_once 'GetURLParm.php';

$purchaseOrderNumber  = $_GET['purchaseOrderNumber'];
$orderSequence= $_SESSION['orderSeq'];
$dspMaxRows    = 9999;

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

if ($orderSequence > 0) {
	$stmtSQL  = " Select PHFL01 as POFL01, PHFL02 as POFL02, PHFL03 as POFL03, PHFL04 as POFL04, PHFL05 as POFL05, ";
	$stmtSQL .= "        PHFL06 as POFL06, PHFL07 as POFL07, PHFL08 as POFL08, PHFL09 as POFL09, PHFL10 as POFL10, ";
	$stmtSQL .= "        PHFL11 as POFL11, PHFL12 as POFL12, PHFL13 as POFL13, PHFL14 as POFL14, PHFL15 as POFL15, ";
	$stmtSQL .= "        PHFL16 as POFL16, PHFL17 as POFL17, PHFL18 as POFL18, PHFL19 as POFL19, PHFL20 as POFL20, ";
	$stmtSQL .= "        PHTYPE as POTYPE ";
	$stmtSQL .= " From POPOHH Where PHPO=$purchaseOrderNumber and PHSEQ#=$orderSequence";
}else {
	$stmtSQL = " Select * From POPOMS Where POPO=$purchaseOrderNumber";
}
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$porow = db2_fetch_assoc($sqlResult);

print "<table $contentTable> <tr>";
print "<th class=\"colhdr\">Description</th>";
print "<th class=\"colhdr\">Value</th>";
print "\n </tr>";

require 'stmtSQLClear.php';
$stmtSQL .= " Select a.FLVALU as FLVALU,a.FLDESC as FLDESC, b.FLDESC as FLVLDS, b.FLVALU as FLVLFG ";
$fileSQL .= " SYFLAG a inner join SYFLAG b on b.FLTYPE='POFLAG'||a.FLVALU ";
$fileSQL .= "          inner join HDORFG c on a.FLVALU=FGFGFG and FGAPID='PO' and FGORTY='$porow[POTYPE]' ";
$selectSQL = " a.FLTYPE='POFLAGDSC'";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By a.FLVALU";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$startRow = 1;
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($saveFlag != $row['FLVALU']) {
		require 'SetRowClass.php';
	}
	if ($saveFlag == "" || $saveFlag != $row['FLVALU']) {
		$saveDesc = $row['FLDESC'];
		$saveFlag = $row['FLVALU'];
	}
	$row['POFL01'] = 1;
	$flagID="POFL" . trim($row['FLVALU']);
	$flagSel = "";
	$flagClass = "colalph";
	if ($porow[$flagID] == trim($row['FLVLFG'])) {$flagSel = "CHECKED"; $flagClass = "colvcat";}

	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\">$saveDesc</td>";
	print "\n     <td class=\"colalph\"><label class=\"$flagClass\"><input type=\"radio\" name=\"$flagID\" $flagSel DISABLED>$row[FLVLDS]</label></td>";
	print "\n </tr>";
	$saveDesc = "&nbsp;";
	$startRow ++;
	$rowCount ++;
}

print "\n </table>";
require_once 'WildCardPrint.php';
print "\n  </div></div>";
?>
