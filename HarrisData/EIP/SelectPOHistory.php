<?php
require_once 'GetURLParm.php';
$vendorNumber = $_GET['vendorNumber'];
$vendorName   = $_GET['vendorName'];
$purchaseOrderNumber  = $_GET['purchaseOrderNumber'];
$orderSequence = (isset($_GET['orderSequence']))  ? $_GET['orderSequence'] : 1;
$_SESSION['orderSeq'] = $orderSequence;

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once "MCControl$dataBaseID.php";
require_once "OEControl$dataBaseID.php";
require_once "POControl$dataBaseID.php";
require_once 'VarBase.php';
require_once 'WildCard.php';

$scriptVarBase = "{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber));
$dspMaxRows    = 9999;
$prtMaxRows    = 9999;

require 'stmtSQLClear.php';
$stmtSQL .= " WITH Order as (Select PHPO,PHSEQ# From POPOHH)";
$stmtSQL .= " Select PHPO,PHSEQ#,(Select min(PHSEQ#) From Order b Where b.PHPO=a.PHPO and b.PHSEQ# < a.PHSEQ#) as FIRST ,(Select max(PHSEQ#) From Order b Where b.PHPO=a.PHPO and b.PHSEQ# > a.PHSEQ#) as LAST, ";
$stmtSQL .= " (Select max(PHSEQ#) From Order b Where b.PHPO=a.PHPO and b.PHSEQ# < a.PHSEQ#) as PREV, (Select min(PHSEQ#) From Order b Where b.PHPO=a.PHPO and b.PHSEQ# > a.PHSEQ#) as NEXT ";
$stmtSQL .= " From Order a Where PHPO=$purchaseOrderNumber and PHSEQ#=$orderSequence ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

print "<div class=\"dspalph\">Receipt:  ";
if ($formatToPrint != "Y"){
	if ($row[FIRST] > 0) {print "\n  <a href=\"#\" onclick=\"getPOHistory('$baseVar', '$vendorNumber', '$vendorName', '$purchaseOrderNumber', '$row[FIRST]')\">$previousImageBegSml</a>";} else {print $nextPrevBlank;}
	if ($row[PREV] > 0)  {print "\n  <a href=\"#\" onclick=\"getPOHistory('$baseVar', '$vendorNumber', '$vendorName', '$purchaseOrderNumber', '$row[PREV]')\">$previousImageSml</a>";}  else {print $nextPrevBlank;}
}
print "<span class=\"H2\">" . $orderSequence."</span>";
if ($formatToPrint != "Y"){
	if ($row[NEXT] > 0) {print "\n  <a href=\"#\" onclick=\"getPOHistory('$baseVar', '$vendorNumber', '$vendorName', '$purchaseOrderNumber', '$row[NEXT]')\">$nextImageSml</a>";}
	if ($row[LAST] > 0) {print "\n  <a href=\"#\" onclick=\"getPOHistory('$baseVar', '$vendorNumber', '$vendorName', '$purchaseOrderNumber', '$row[LAST]')\">$nextImageEndSml</a>";}
}
print "\n </div>";

require 'stmtSQLClear.php';
$stmtSQL .= " Select PHDSHP,PHRQDT,PHDTEN,PHLRDT,PHSVSV,PHSVDS,PHFOB,PHFOBC,PHPTRM,PHPOTD,PHPORF,PHFALR,PHSTLR,PHSCLR,PHMCT1,PHMCT2,PHMCT3,PHMCT4,PHMCT5,PHMCT6,";
$stmtSQL .= " (PHFALR+PHSTLR+PHSCLR+PHMCT1+PHMCT2+PHMCT3+PHMCT4+PHMCT5+PHMCT6) as PHTAMT,coalesce(OTDESC,' ') as OTDESC,";
$stmtSQL .= " VMVNA1,VMVNA2,VMVNA3,VMVNA4,VMVCTY,VMST,VMZIP,coalesce(BMBNA1,' ') as BMBNA1,";
$stmtSQL .= " coalesce(CMCNA1,a.DSNAME,b.DSNAME,WHWHNM) as STNAME, coalesce(CMCNA2,a.DSADR1,b.DSADR1,WHWHAD) as STADR1, coalesce(CMCNA3,a.DSADR2,b.DSADR2,WHWHA2) as STADR2, ";
$stmtSQL .= " coalesce(CMCNA4,a.DSADR3,b.DSADR3,'') as STADR3, coalesce(CMCCTY,a.DSCITY,b.DSCITY,WHWHCT) as STCITY, coalesce(CMST,a.DSST,b.DSST,WHWHST) as STST, coalesce(CMZIP,a.DSZIP,b.DSZIP,WHHWZP) as STZIP ";
$fileSQL .= " POPOHH";
$fileSQL .= " left join HDVEND on PHVEND=VMVEND";
$fileSQL .= " left join HDCUST on PHDSHP=CMCUST and PHDSHC='C' and PHDSHP>0";
$fileSQL .= " left join HDDSHP a on PHVEND=a.DSVNCS and PHDSHP=a.DSNMBR and a.DSVCF='C' and PHDSHP>0 and PHDSHC='C'";
$fileSQL .= " left join HDDSHP b on PHDSHP=b.DSNMBR and b.DSVCF='V' and PHDSHP>0 and PHDSHC=' '";
$fileSQL .= " left join HDWHSM on PHWHS=WHWHS";
$fileSQL .= " left join HDBUYR on PHBUYR=BMBUYR";
$fileSQL .= " left join HDOTYP on OTOTCD=PHTYPE and OTAPID='PO'";
$selectSQL = "PHPO=$purchaseOrderNumber and PHSEQ#=$orderSequence";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By PHPO ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

$PHCURT=$row[PHCURT];
$PHTAMT=$row[PHTAMT];
$PHFALR=$row[PHFALR];
$PHSTLR=$row[PHSTLR];
$PHSCLR=$row[PHSCLR];
$PHMCT1=$row[PHMCT1];
$PHMCT2=$row[PHMCT2];
$PHMCT3=$row[PHMCT3];
$PHMCT4=$row[PHMCT4];
$PHMCT5=$row[PHMCT5];
$PHMCT6=$row[PHMCT6];

if ($row[PHDSHP] > 0) {$sthdr = "Drop Ship";} else {$sthdr = "Ship-To";}
print "
  <table {$contentTable}>
      <colgroup>  
          <col width=\"30%\">  
          <col width=\"10%\">  
          <col width=\"30%\">  
          <col width=\"10%\">  
			
      <tr><td class=\"colhdr\">Vendor</td>
          <td>&nbsp;</td>
          <td class=\"colhdr\">$sthdr</td>
          <td>&nbsp;</td>
      </tr>
      <tr valign=top>
          <td rowspan=\"5\" class=\"colalph\"> {$row[VMVNA1]} <br>
";
if (trim($row[VMVNA2]) != "") {print " $row[VMVNA2] <br>";}
if (trim($row[VMVNA3]) != "") {print " $row[VMVNA3] <br>";}
if (trim($row[VMVNA4]) != "") {print " $row[VMVNA4] <br>";}
$csz = trim($row[VMVCTY]) . ', ' . $row[VMST] . ' ' . $row[VMZIP];
print "
    $csz
    </td>
    <td>&nbsp;</td>
    <td rowspan=\"5\" class=\"colalph\"> $row[STNAME] <br>
";
if (trim($row[STADR1]) != "") {print " $row[STADR1] <br>";}
if (trim($row[STADR2]) != "") {print " $row[STADR2] <br>";}
if (trim($row['STADR3']) != "") {print " $row[STADR3] <br>";}
$csz = trim($row[STCITY]) . ', ' . $row[STST] . ' ' . $row[STZIP];
print "$csz </td></tr></table>";

print "<table $contentTable> <tr>";
print "<th class=\"colhdr\">Ordered</th>";
print "<th class=\"colhdr\">Required</th>";
print "<th class=\"colhdr\">Received</th>";
print "<th class=\"colhdr\">Buyer</th>";
print "<th class=\"colhdr\">Ship Via</th>";
print "<th class=\"colhdr\">FOB</th>";
print "<th class=\"colhdr\">Terms</th>";
print "<th class=\"colhdr\">Reference</th>";
print "<th class=\"colhdr\">Order Type</th>";
print "\n </tr>";

$wrkDate = Date_CYMD_ISO($row['PHDTEN']);
$H_PHDTEN = date('l F dS Y', strtotime($wrkDate));
$F_PHDTEN=Format_Date($row['PHDTEN'], "D");
$wrkDate = Date_CYMD_ISO($row['PHRQDT']);
$H_PHRQDT = date('l F dS Y', strtotime($wrkDate));
$F_PHRQDT=Format_Date($row['PHRQDT'], "D");
$wrkDate = Date_CYMD_ISO($row['PHLRDT']);
$H_PHLRDT = date('l F dS Y', strtotime($wrkDate));
$F_PHLRDT=Format_Date($row['PHLRDT'], "D");
print "\n <tr class=\"$rowClass\">";
print "\n <td class=\"coldate\"><span $helpCursor title=\"$H_PHDTEN\">$F_PHDTEN</span></td>";
print "\n <td class=\"coldate\"><span $helpCursor title=\"$H_PHRQDT\">$F_PHRQDT</span></td>";
print "\n <td class=\"coldate\"><span $helpCursor title=\"$H_PHLRDT\">$F_PHLRDT</span></td>";
print "\n <td class=\"colalph\">$row[BMBNA1]</td>";
print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ShipViaInquiry.d2w/DISPLAY{$altVarBase}&amp;shipVia=" . urlencode(trim($row['PHSVSV'])) . "\" onclick=\"$inquiryWinVar\" title=\"Ship Via Quickview\">$row[PHSVDS]</a></td>";
print "\n <td class=\"colalph\">$row[PHFOB]</td>";
$termsDesc=RetValue("VTRMS='$row[PHPTRM]'", "APVTRM", "VTVTDS");
print "\n <td class=\"colalph\">$termsDesc</td>";
print "\n <td class=\"colcode\">$row[PHPORF]</td>";
print "\n <td class=\"colalph\">$row[OTDESC]</td>";

print "\n </tr></table>";


print "<table $contentTable> <tr>";
if ($formatToPrint != "Y"){
	print "<th class=\"colhdr\">$optionHeading</th>";
}
print "<th class=\"colhdr\">Line</th>";
print "<th class=\"colhdr\">Rel</th>";
print "<th class=\"colhdr\">Whs</th>";
print "<th class=\"colhdr\">Item Number</th>";
print "<th class=\"colhdr\">Description</th>";
print "<th class=\"colhdr\">Required</th>";
print "<th class=\"colhdr\">UP</th>";
print "<th class=\"colhdr\">Quantity<br>Received</th>";
print "<th class=\"colhdr\">Cost</th>";
print "<th class=\"colhdr\">Extended<br>Cost</th>";
print "\n </tr>";

require 'stmtSQLClear.php';
$stmtSQL .= " Select POPOHD.*, PITRQT/PIPCPB as PITRQT, PIPOL# as PIPOL,";
$stmtSQL .= " dec(round((PITRQT*PIDSCC)/PIPCPB,2),15,2) as PIEXTA, coalesce(UMUMLD,'') as PUOM,";
$stmtSQL .= " (Select count(*) From POHCMT Where OHORD#=$purchaseOrderNumber and OHSSEQ=$orderSequence and OHORL#=PIPOL# and OHBLN#=PIPORL) as CMTCNT";
$fileSQL .= " POPOHD";
$fileSQL .= " left join HDUOM on PIBUOM=UMUOM";
$selectSQL = "PIPO=$purchaseOrderNumber and PISEQ#=$orderSequence";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By PIPOL,PIPORL ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$subtotal = 0;
$startRow = 1;
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	$maintainVar = "{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['BMBCHN'])) . "&amp;fromBatchDate=" . urlencode(trim($row['BMBCHD'])) . "&amp;fromBatchBank=" . urlencode(trim($row['BMBCHB'])) . "&amp;fromScript=" . urlencode(trim($scriptName));
	$wrkDate  = Date_CYMD_ISO($row['PIRQDT']);
	$H_PIRQDT = date('l F dS Y', strtotime($wrkDate));
	$F_PIRQDT = Format_Date($row['PIRQDT'], "D");
	$F_PITRQT = Format_Nbr($row['PITRQT'], $qtyNbrDec, $qtyEditCode, "", "", "");
	$F_PIDSCC = Format_Nbr($row['PIDSCC'], $cstNbrDec, $cstEditCode, "", "", "");
	$F_PIEXTA = Format_Nbr($row['PIEXTA'], "2", $cstEditCode, "", "", "");

	require 'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	if ($formatToPrint != "Y"){
		print "\n <td class=\"opticon\">";
		print "\n     <a href=\"{$homeURL}{$phpPath}SelectPO.php{$scriptVarBase}&amp;lineNumber=" . urlencode(trim($row['PIPOL'])) . "&amp;releaseNumber=" . urlencode(trim($row['PIPORL'])) . "&amp;tabID=LINE\">$smMoreInfoImage</a>";
		if ($row[PDPOEC] != "N") {
			$itemImage = $row[PIITEM] . $itemImageExt;
			if (file_exists("{$homePath}images/item/{$itemImage}") !== false) {
				$imagePARM = "&amp;imageDisplayPath={$homeURL}{$homePath}images/item/{$itemImage}";
				print "\n <a href=\"{$homeURL}{$cGIPath}ImageDisplay.d2w/DISPLAY{$altVarBase}{$imagePARM}&amp;imageDesc=" . urlencode(trim($row['PIIMDS'])) . "\" onclick=\"$itemImageWinVar\">$foundImage</a>";

			}
		}
		if ($row[CMTCNT] > 0) {print "\n <a href=\"{$homeURL}{$phpPath}SelectPO.php{$scriptVarBase}&amp;lineNumber=" . urlencode(trim($row['PIPOL'])) . "&amp;releaseNumber=" . urlencode(trim($row['PIPORL'])) . "&amp;tabID=LINE\">$commentExistImage</a>";}
		print "\n </td>";
	}
	$line=$row[PIPOL];
	$subtotal+=$row['PIEXTA'];
	print "\n <td class=\"colnmbr\">$line</td>";
	print "\n <td class=\"colnmbr\">$row[PIPORL]</td>";
	if ($row[PDPOEC] != "N") {
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}ItemWarehouseSelect.d2w/REPORT{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['PIITEM'])) . "&amp;warehouseNumber=" . urlencode(trim($row['PIOVWH'])) . "\" title=\"View Item/Warehouse\">$row[PIOVWH]</a></td>";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemInquiry.d2w/DISPLAY{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['PIITEM'])) . "\" onclick=\"{$inquiryWinVar}\" title=\"Item Quickview\">$row[PIITEM]</a></td>";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['PIITEM'])) . "&amp;itemDescription=" . urlencode(trim($row['PIIMDS'])) . "\" title=\"View Item\">$row[PIIMDS]</a></td>";
	} else {
		print "\n <td class=\"colnmbr\">$row[PIOVWH]</td>";
		print "\n <td class=\"colalph\">$row[PIITEM]</td>";
		print "\n <td class=\"colalph\">$row[PIIMDS]</td>";
	}
	print "\n <td class=\"colnmbr\"><span $helpCursor title=\"$H_PIRQDT\">$F_PIRQDT</span></td>";
	print "\n <td class=\"colcode\"><span $helpCursor title=\"$row[PUOM]\">$row[PIBUOM]</span></td>";
	print "\n <td class=\"colnmbr\">$F_PITRQT</td>";
	print "\n <td class=\"colnmbr\">$F_PIDSCC</td>";
	print "\n <td class=\"colnmbr\">$F_PIEXTA</td>";

	print "\n </tr>";
	$startRow ++;
	$rowCount ++;
}
if ($formatToPrint != "Y"){$colSpan = 10;} else {$colSpan = 9;}
$F_subtotal = Format_Nbr($subtotal, "2", $cstEditCode, "", "", "");
$PHTAMT += $subtotal;
if ($PHTAMT != $subtotal) {
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Subtotal</td><td class=\"coltotal\">$F_subtotal</td></tr>";
}
if ($PHFALR != 0) {
	$F_PHFALR = Format_Nbr($PHFALR, "2", $cstEditCode, "", "", "");
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Freight Charge</td><td class=\"colnmbr\">$F_PHFALR</td></tr>";
}
if ($PHSTLR != 0) {
	$F_PHSTLR = Format_Nbr($PHSTLR, "2", $cstEditCode, "", "", "");
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Sales Tax</td><td class=\"colnmbr\">$F_PHSTLR</td></tr>";
}
if ($PHSCLR != 0) {
	$F_PHSCLR = Format_Nbr($PHSCLR, "2", $cstEditCode, "", "", "");
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Special Charge</td><td class=\"colnmbr\">$F_PHSCLR</td></tr>";
}
if ($PHMCT1 != 0) {
	$F_PHMCT1 = Format_Nbr($PHMCT1, "2", $cstEditCode, "", "", "");
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM11 $CORM12</td><td class=\"colnmbr\">$F_PHMCT1</td></tr>";
}
if ($PHMCT2 != 0) {
	$F_PHMCT2 = Format_Nbr($PHMCT2, "2", $cstEditCode, "", "", "");
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM21 $CORM22</td><td class=\"colnmbr\">$F_PHMCT2</td></tr>";
}
if ($PHMCT3 != 0) {
	$F_PHMCT3 = Format_Nbr($PHMCT3, "2", $cstEditCode, "", "", "");
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM31 $CORM32</td><td class=\"colnmbr\">$F_PHMCT3</td></tr>";
}
if ($PHMCT4 != 0) {
	$F_PHMCT4 = Format_Nbr($PHMCT4, "2", $cstEditCode, "", "", "");
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM41 $CORM42</td><td class=\"colnmbr\">$F_PHMCT4</td></tr>";
}
if ($PHMCT5 != 0) {
	$F_PHMCT5 = Format_Nbr($PHMCT5, "2", $cstEditCode, "", "", "");
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM51 $CORM52</td><td class=\"colnmbr\">$F_PHMCT5</td></tr>";
}
if ($PHMCT6 != 0) {
	$F_PHMCT6 = Format_Nbr($PHMCT6, "2", $cstEditCode, "", "", "");
	print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM61 $CORM62</td><td class=\"colnmbr\">$F_PHMCT6</td></tr>";
}
$F_PHTAMT = Format_Nbr($PHTAMT, "2", $cstEditCode, "", "", "");
if ($MUPMCD == "Y") {$curt = "(".$PHCURT.")";} else {$curt = "";}
print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Order Total $curt</td><td class=\"coltotal\">$F_PHTAMT</td></tr>";

print "\n </table>";
require_once 'WildCardPrint.php';
print "\n  </div></div>";
?>
