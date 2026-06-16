<?php
require_once 'GetURLParm.php';

$vendorNumber = $_GET['vendorNumber'];
$vendorName   = $_GET['vendorName'];
$purchaseOrderNumber  = $_GET['purchaseOrderNumber'];
$lineNumber   = $_GET['lineNumber'];
$releaseNumber = $_GET['releaseNumber'];
$orderSequence= $_SESSION['orderSeq'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'POUserDefinedInclude.php';
require_once 'VarBase.php';

$scriptVarBase = "{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber));
$programName = "HPOPEM";
$hpopem_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'SelectPO.js';
print "\n </script> \n";

require 'stmtSQLClear.php';
if ($orderSequence > 0) {
	$userDefTable = "POOUHD";
	if ($releaseNumber == 0) {$releaseNumber =RetValue("PIPO=$purchaseOrderNumber and PISEQ#=$orderSequence and PIPOL#=$lineNumber", "POPOHD", "min(PIPORL)");}
	$stmtSQL .= " Select PIPO as PDPO, PIPOL# as PDPOL#, PIPORL as PDPORL, PIOVWH as PDOVWH, PIITEM as PDITEM, PIIMDS as PDIMDS, PIITCS as PDITCS, ";
	$stmtSQL .= " PIDSCC as PDDSCC, PIQTOR as PDQTOR, PIRQDT as PDRQDT, PIDLRC as PDDLRC, PISUOM as PDSUOM, PIBUOM as PDBUOM, PIPCPB as PDPCPB, PIPOEC as PDPOEC, ";
	$stmtSQL .= " PIPOLT as PDPOLT, PIPCLS as PDPCLS, PIDSCF as PDDSCF, PIBOAL as PDBOAL, PITXCD as PDTXCD, PIVDSN as PDVDSN, 'B' as POBUSY, PHTYPE as POTYPE, ";
	$stmtSQL .= " '' as PDLATY, '' as PDLADT, 0 as PDOOQT, ";
	$stmtSQL .= " Case When PITRXN='SR' Then PITRQT Else 0 End as PDQRST, Case When PITRXN='RC' Then PITRQT Else 0 End as PDQRRT, ";
	$stmtSQL .= " Case When PITRXN='RV' Then PITRQT Else 0 End as PDQRVT, Case When PITRXN='FR' Then PITRQT Else 0 End as PDQRFT, ";
	$stmtSQL .= " (PITRQT*PIDSCC)/PIPCPB as PDEXTC, 1 as PDRECV,";
	$stmtSQL .= " coalesce(WHWHNM,'') as WHWHNM, coalesce(a.UMUMLD,'') as SUOM, coalesce(b.UMUMLD,'') as PUOM, coalesce(PCPCDS,'') as PCPCDS ";
	$fileSQL .= " POPOHD inner join POPOHH on PIPO=PHPO";
	$fileSQL .= " left join HDWHSM on PIOVWH=WHWHS";
	$fileSQL .= " left join HDUOM a on PISUOM=a.UMUOM";
	$fileSQL .= " left join HDUOM b on PIBUOM=b.UMUOM";
	$fileSQL .= " left join HDPCLS on PIPCLS=PCPCLS";
	$selectSQL = "PIPO=$purchaseOrderNumber and PISEQ#=$orderSequence and PIPOL#=$lineNumber and PIPORL=$releaseNumber";
} else {
	$userDefTable = "POOUMD";
	$stmtSQL .= " Select POPOMD.*,POBUSY,(PDQTOR*PDDSCC)/PDPCPB as PDEXTC,";
	$stmtSQL .= " PDQTOR-(PDQRST+PDQRRT+PDQRFT) as PDOPEN, (PDQRST+PDQRRT+PDQRVT+PDQRFT)/PDPCPB as PDRECV,";
	$stmtSQL .= " coalesce(WHWHNM,'') as WHWHNM, coalesce(a.UMUMLD,'') as SUOM, coalesce(b.UMUMLD,'') as PUOM, coalesce(PCPCDS,'') as PCPCDS, POTYPE";
	$fileSQL .= " POPOMD inner join POPOMS on PDPO=POPO";
	$fileSQL .= " left join HDWHSM on PDOVWH=WHWHS";
	$fileSQL .= " left join HDUOM a on PDSUOM=a.UMUOM";
	$fileSQL .= " left join HDUOM b on PDBUOM=b.UMUOM";
	$fileSQL .= " left join HDPCLS on PDPCLS=PCPCLS";
	$selectSQL = "PDPO=$purchaseOrderNumber and PDPOL#=$lineNumber and PDPORL=$releaseNumber";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By PDPOL#,PDPORL";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);
$itemNumber = $row[PDITEM];
$itemDesc = $row[PDIMDS];

$udCol = Rtv_PO_UserDefined_Columns($userDefTable, $vendorNumber, $row['POTYPE'], $row['PDPCLS']);
$userDefDtlCnt = (!empty($udCol)) ? count($udCol) : 0;

if ($orderSequence > 0) {
	if ($lineNumber > 1) {$prevLine =RetValue("PIPO=$purchaseOrderNumber and PISEQ#=$orderSequence and PIPOL#<$lineNumber", "POPOHD", "max(PIPOL#)");}
	else                 {$prevLine = 0;}
	$nextLine=RetValue("PIPO=$purchaseOrderNumber and PISEQ#=$orderSequence and PIPOL#>$lineNumber", "POPOHD", "min(PIPOL#)");
	$prevRel =RetValue("PIPO=$purchaseOrderNumber and PISEQ#=$orderSequence and PIPOL#=$lineNumber and PIPORL<$releaseNumber", "POPOHD", "max(PIPORL)");
	$nextRel =RetValue("PIPO=$purchaseOrderNumber and PISEQ#=$orderSequence and PIPOL#=$lineNumber and PIPORL>$releaseNumber", "POPOHD", "min(PIPORL)");
} else {
	if ($lineNumber > 1) {$prevLine =RetValue("PDPO=$purchaseOrderNumber and PDPOL#<$lineNumber", "POPOMD", "max(PDPOL#)");}
	else                 {$prevLine = 0;}
	$nextLine=RetValue("PDPO=$purchaseOrderNumber and PDPOL#>$lineNumber", "POPOMD", "min(PDPOL#)");
	$prevRel =RetValue("PDPO=$purchaseOrderNumber and PDPOL#=$lineNumber and PDPORL<$releaseNumber", "POPOMD", "max(PDPORL)");
	$nextRel =RetValue("PDPO=$purchaseOrderNumber and PDPOL#=$lineNumber and PDPORL>$releaseNumber", "POPOMD", "min(PDPORL)");

	if ($hpopem_OPT['sec_02'] == 'Y' && trim($row[POBUSY]) == '' && trim($row[PDSTAT]) == 'O' && $_SESSION['POSTAT'] == 'O') {
		if ($userDefDtlCnt > 0) {
			$lgU = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgU.gif\" title=\"Update User-Defined\" alt=\"U\">";
			print "\n <div class=\"quickLinksTop\" align='bottom'><a href=\"{$homeURL}{$phpPath}POUserDefinedMaintain.php{$scriptVarBase}&amp;udTable=POOUMD&amp;tag=MAINTAIN" . "&amp;fromPO=" . trim($purchaseOrderNumber) . "&amp;fromLine=" . trim($lineNumber) . "&amp;fromItem=" . trim($row[PDITEM]) . "&amp;fromItemDesc=" . urlencode(trim($row[PDIMDS])) . "\"  onclick = \"$inquiryWinVar\">$lgU</a></div>";
		}
		print "\n <div class=\"quickLinksTop\" align='bottom'><a href=\"{$homeURL}{$phpPath}PODetailMaintain.php{$scriptVarBase}&amp;lineNumber={$lineNumber}&amp;tag=MAINTAIN&amp;maintenanceCode=C&amp;noMenu=Y\" onclick = \"$commentWinVar\" title=\"Comments\">$changeImageLrg</a>";
		print "&nbsp;<a href=\"{$homeURL}{$phpPath}POComment.php{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrder=" . urlencode(trim($purchaseOrderNumber)) . "&amp;itemNumber=" . urlencode(trim($row['PDITEM'])) . "&amp;itemDesc=" . urlencode(trim($row['PDIMDS'])) . "&amp;cmtLine=" . urlencode($lineNumber) . "&amp;noMenu=Y\" onclick = \"$commentWinVar\" title=\"Comments\">$commentExistImageLrg </a></div>";
	}
}

print "<table $contentTable>";
print "\n  <tr><td class=\"dsphdr\">Line Number</td><td class=\"colalph\">";
if ($prevLine > 0) {print "\n  <a href=\"#\" onclick=\"getPOLine('$baseVar', '$vendorNumber', '$vendorName', '$purchaseOrderNumber', '$prevLine', '0')\">$previousImageSml</a>";}
print $lineNumber;
if ($nextLine > 0) {print "\n  <a href=\"#\" onclick=\"getPOLine('$baseVar', '$vendorNumber', '$vendorName', '$purchaseOrderNumber', '$nextLine', '0')\">$nextImageSml</a>";}
print "\n </td></tr>";

if ($prevRel > 0 || $nextRel > 0 || $releaseNumber >0) {
	print "\n  <tr><td class=\"dsphdr\">Release Number</td><td class=\"colalph\">";
	if ($prevRel > 0 || ($orderSequence == 0 && $releaseNumber > 0)) {print "\n  <a href=\"#\" onclick=\"getPOLine('$baseVar', '$vendorNumber', '$vendorName', '$purchaseOrderNumber', '$lineNumber', '$prevRel')\">$previousImageSml</a>";}
	print $releaseNumber;
	if ($nextRel > 0) {print "\n  <a href=\"#\" onclick=\"getPOLine('$baseVar', '$vendorNumber', '$vendorName', '$purchaseOrderNumber', '$lineNumber', '$nextRel')\">$nextImageSml</a>";}
	print "\n </td></tr>";
}

if ($orderSequence > 0) {
	$row[PDOPEN] = $row[PDQTOR]-($row[PDQRST]+$row[PDQRRT]+$row[PDQRFT]);
	Build_DspFld("Receipt",$orderSequence);
} else {
	Build_Fld_Entry("Status","stat","dspalph","ORDSTATUS","",$row[PDSTAT],"","","","","Y","");
}

$whs = Format_Code($row[PDOVWH]);
$item = Format_Code($row[PDITEM]);
if ($row[PDPOEC] != "N") {
	print "\n <tr><td class=\"dsphdr\">Warehouse Number</td><td class=\"dspalph\"><a href=\"{$homeURL}{$cGIPath}ItemWarehouseSelect.d2w/REPORT{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['PDITEM'])) . "&amp;warehouseNumber=" . urlencode(trim($row['PDOVWH'])) . "\" title=\"View Item/Warehouse\">$row[WHWHNM]</a></td><td class=\"dspalph\">$whs</td>";
	print "\n <tr><td class=\"dsphdr\">Item Number</td><td class=\"dspalph\"><a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['PDITEM'])) . "&amp;itemDescription=" . urlencode(trim($row['PDIMDS'])) . "\" title=\"View Item\">$row[PDIMDS]</a></td><td class=\"dspalph\">$item</td>";
} else {
	print "\n <tr><td class=\"dsphdr\">Warehouse Number</td><td class=\"dspalph\">$row[WHWHNM]</td><td class=\"dspalph\">$whs</td>";
	print "\n <tr><td class=\"dsphdr\">Item Number</td><td class=\"dspalph\">$row[PDIMDS]</td><td class=\"dspalph\">$item</td>";
}

print "\n  <td>&nbsp;</td><td rowspan=\"10\"> ";
print "\n <fieldset><legend class=\"legendTitle\">Quantities</legend> ";
print "\n <table class=\"contenttable\" float=\"right\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" summary=\"contenttable\">";
print "<table $contentTable>";
$F_PDQTOR = Format_Nbr($row['PDQTOR'], $qtyNbrDec, $qtyEditCode, "", "", "");
print "\n <tr><td class=\"dsphdr\">Ordered</td><td class=\"colnmbr\">$F_PDQTOR</td></tr>";
$F_PDOPEN = Format_Nbr($row['PDOPEN'], $qtyNbrDec, $qtyEditCode, "", "", "");
print "\n <tr><td class=\"dsphdr\">Open</td><td class=\"colnmbr\">$F_PDOPEN</td></tr>";
if ($row['PDQRST'] != 0) {
	$F_PDQRST = Format_Nbr($row['PDQRST'], $qtyNbrDec, $qtyEditCode, "", "", "");
	print "\n <tr><td class=\"dsphdr\">To Stock</td><td class=\"colnmbr\">$F_PDQRST</td></tr>";
}
if ($row['PDQRRT'] != 0) {
	$F_PDQRRT = Format_Nbr($row['PDQRRT'], $qtyNbrDec, $qtyEditCode, "", "", "");
	print "\n <tr><td class=\"dsphdr\">To Receiving</td><td class=\"colnmbr\">$F_PDQRRT</td></tr>";
}
if ($row['PDQHRT'] != 0) {
	$F_PDQHRT = Format_Nbr($row['PDQHRT'], $qtyNbrDec, $qtyEditCode, "", "", "");
	print "\n <tr><td class=\"dsphdr\">Held In Receiving</td><td class=\"colnmbr\">$F_PDQHRT</td></tr>";
}
if ($row['PDQRVT'] != 0) {
	$F_PDQRVT = Format_Nbr($row['PDQRVT'], $qtyNbrDec, $qtyEditCode, "", "", "");
	print "\n <tr><td class=\"dsphdr\">Returned To Vendor</td><td class=\"colnmbr\">$F_PDQRVT</td></tr>";
}
if ($row['PDQRFT'] != 0) {
	$F_PDQRFT = Format_Nbr($row['PDQRFT'], $qtyNbrDec, $qtyEditCode, "", "", "");
	print "\n <tr><td class=\"dsphdr\">To Floorstock</td><td class=\"colnmbr\">$F_PDQRFT</td></tr>";
}
print "\n </table>";
print "\n </fieldset></td>";

$F_PDITCS = Format_Nbr($row['PDITCS'], $cstNbrDec, $cstEditCode, "", "", "");
Build_DspFld("Inventory Cost",$F_PDITCS);
$F_PDDSCC = Format_Nbr($row['PDDSCC'], $cstNbrDec, $cstEditCode, "", "", "");
Build_DspFld("Discounted Cost",$F_PDDSCC);
$F_PDEXTC = Format_Nbr($row['PDEXTC'], "2", $prcEditCode, "", "", "");
Build_DspFld("Extended Cost",$F_PDEXTC);
$PDRQDT=DateInputFromCYMD($row['PDRQDT']);
Build_Fld_Entry("Required Date","rqdt","dspalph","Date","",$PDRQDT,"","","","","Y","");
if ($row[PDDLRC] > 0) {
	$lastRec = DateInputFromCYMD($row[PDDLRC]);
	Build_Fld_Entry("Last Received","lastrcv","dspalph","Date","",$lastRec,"","","","","Y","");
}
Build_DspFld("Stocking U/M",$row['SUOM'],Format_Code($row['PDSUOM']),$fldType);
Build_DspFld("Purchasing U/M",$row['PUOM'],Format_Code($row['PDBUOM']),$fldType);
$F_PDPCPB = Format_Nbr($row['PDPCPB'], $qtyNbrDec, $qtyEditCode, "", "", "");
Build_DspFld("Pieces Per Stocking U/M",$F_PDPCPB);
Build_DspFld("Entry Code",$row[PDPOEC]);
Build_DspFld("Line Type",$row[PDPOLT]);
Build_DspFld("Product Class",$row['PCPCDS'],Format_Code($row['PDPCLS']),$fldType);

if ($poDetailDate1 != '') {
	$udt1=Format_Date($row[PDOPDT], "D");
	Build_DspFld($poDetailDate1,$udt1,"",$fldType);
}
if ($poDetailDate2 != '') {
	$udt2=Format_Date($row[PDCPDT], "D");
	Build_DspFld($poDetailDate2,$udt2,"",$fldType);
}
if ($poDetailDate3 != '') {
	$udt3=Format_Date($row[PDLADT], "D");
	Build_DspFld($poDetailDate3,$udt3,"",$fldType);
}

Build_DspFld("Discount Allowed",$row[PDDSCF]);
Build_DspFld("Backorder Allowed",$row[PDBOAL]);
Build_DspFld("Taxable",$row[PDTXCD]);
if ($row[PDVDSN] != "") {Build_DspFld("Vendor Item",$row[PDVDSN]);}
if ($row[PDRORD] != "") {Build_DspFld("Reference Order",$row[PDRORD]);}
if ($orderSequence == "") {
	Build_DspFld("Update Current Cost",$row[PDUCC]);
	if ($row[PDECN] != "") {Build_DspFld("Eng Change Notice",$row[PDECN]);}
}

if ($userDefDtlCnt > 0) {
	Display_PO_UserDefined_Columns($udCol, $purchaseOrderNumber, $orderSequence, $lineNumber);
}

print "\n </table>";

if (trim($row[PDLATY]) != "") {
	print "\n <fieldset class=\"legendbody\"><legend class=\"legendTitle\">Last Activity</legend> ";
	print "<table $contentTable>";
	$flagDesc = RetValue("FLTYPE='LINKPOCHG' and FLVALU='$row[PDLATY]'", "SYFLAG", "FLDESC");
	Build_DspFld("Type",$flagDesc,Format_Code($row['PDLATY']),$fldType);
	$PDLADT=DateInputFromCYMD($row['PDLADT']);
	Build_Fld_Entry("Date","rqdt","dspalph","Date","",$PDLADT,"","","","","Y","");
	$F_PDOOQT = Format_Nbr($row['PDOOQT'], $qtyNbrDec, $qtyEditCode, "", "", "");
	Build_DspFld("Original Order Quantity",$F_PDOOQT);
	print "\n </table>";
	print "\n </fieldset>";
}

$dspMaxRows    = 9999;
require 'stmtSQLClear.php';
if ($orderSequence > 0) {
	$stmtSQL  .= " Select OHDOCT as OCDOCT, OHCSEQ as OCCSEQ, OHCMNT as OCCMNT,DODESC ";
	$fileSQL  .= " POHCMT inner join HDDOCT on OHDOCT=DODOCT and (DOAPID='PO' or DOAPID=' ') ";
	$selectSQL = " OHORD#=$purchaseOrderNumber and OHSSEQ=$orderSequence and OHORL#=$lineNumber and OHBLN#=$releaseNumber";
} else {
	$stmtSQL  .= " Select OCDOCT,OCCMNT,DODESC ";
	$fileSQL  .= " POOCMT inner join HDDOCT on OCDOCT=DODOCT and (DOAPID='PO' or DOAPID=' ') ";
	$selectSQL = " OCORD#=$purchaseOrderNumber and OCORL#=$lineNumber and OCBLN#=$releaseNumber";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By OCDOCT,OCCSEQ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$startRow = 1;
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($saveDoct != $row[OCDOCT]) {
		if ($saveDoct == "") {print "\n <fieldset class=\"legendbody\"><legend class=\"legendTitle\">Comments</legend>";} else {print "\n </div></fieldset><br>";}
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
