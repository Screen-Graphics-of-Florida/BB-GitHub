<?php
require_once 'GetURLParm.php';
$vendorNumber = $_GET['vendorNumber'];
$vendorName = $_GET['vendorName'];
$purchaseOrderNumber = $_GET['purchaseOrderNumber'];
$_SESSION['orderSeq'] = 0;
$formatToPrint = $_SESSION['openPOFmt'];
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
$dspMaxRows = 9999;
$prtMaxRows = 9999;
$programName = "HPOPEM";
$hpopem_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);

require 'stmtSQLClear.php';
$stmtSQL .= " Select POSTAT,POBUSY,PODSHP,PORQDT,PODTEN,POSVSV,POSVDS,POFOB,POFOBC,POPTRM,POPOTD,POPORF,POFRTA,POSLTA,POSCGA,POMCA1,POMCA2,POMCA3,POMCA4,POMCA5,POMCA6,";
$stmtSQL .= " (POFRTA+POSLTA+POSCGA+POMCA1+POMCA2+POMCA3+POMCA4+POMCA5+POMCA6) as POTAMT,coalesce(OTDESC,' ') as OTDESC,";
$stmtSQL .= " VMVNA1,VMVNA2,VMVNA3,VMVNA4,VMVCTY,VMST,VMZIP,coalesce(BMBNA1,' ') as BMBNA1,";
$stmtSQL .= " coalesce(CMCNA1,a.DSNAME,b.DSNAME,WHWHNM) as STNAME, coalesce(CMCNA2,a.DSADR1,b.DSADR1,WHWHAD) as STADR1, coalesce(CMCNA3,a.DSADR2,b.DSADR2,WHWHA2) as STADR2, ";
$stmtSQL .= " coalesce(CMCNA4,a.DSADR3,b.DSADR3,'') as STADR3, coalesce(CMCCTY,a.DSCITY,b.DSCITY,WHWHCT) as STCITY, coalesce(CMST,a.DSST,b.DSST,WHWHST) as STST, coalesce(CMZIP,a.DSZIP,b.DSZIP,WHHWZP) as STZIP ";
$fileSQL .= " POPOMS";
$fileSQL .= " left join HDVEND on POVEND=VMVEND";
$fileSQL .= " left join HDCUST on PODSHP=CMCUST and PODSHC='C' and PODSHP>0";
$fileSQL .= " left join HDDSHP a on POVEND=a.DSVNCS and PODSHP=a.DSNMBR and a.DSVCF='C' and PODSHP>0 and PODSHC='C'";
$fileSQL .= " left join HDDSHP b on PODSHP=b.DSNMBR and b.DSVCF='V' and PODSHP>0 and PODSHC=' '";
$fileSQL .= " left join HDWHSM on POWHS=WHWHS";
$fileSQL .= " left join HDBUYR on POBUYR=BMBUYR";
$fileSQL .= " left join HDOTYP on OTOTCD=POTYPE and OTAPID='PO'";
$selectSQL = "POPO=$purchaseOrderNumber";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By POPO ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

$POSTAT = $row[POSTAT];
$POBUSY = $row[POBUSY];
$POCURT = $row[POCURT];
$POTAMT = $row[POTAMT];
$POFRTA = $row[POFRTA];
$POSLTA = $row[POSLTA];
$POSCGA = $row[POSCGA];
$POMCA1 = $row[POMCA1];
$POMCA2 = $row[POMCA2];
$POMCA3 = $row[POMCA3];
$POMCA4 = $row[POMCA4];
$POMCA5 = $row[POMCA5];
$POMCA6 = $row[POMCA6];

if ($row[PODSHP] > 0) {
    $sthdr = "Drop Ship";
} else {
    $sthdr = "Ship-To";
}
if ($formatToPrint != "Y" && $hpopem_OPT['sec_02'] == 'Y' && trim($POBUSY) == '' && $_SESSION['POSTAT'] == 'O') {
    print "\n <div class=\"quickLinksTop\"><a href=\"{$homeURL}{$phpPath}PODetailMaintain.php{$scriptVarBase}&amp;maintenanceCode=A&amp;tag=MAINTAIN&amp;noMenu=Y\" onclick = \"$commentWinVar\">$addImageLrg</a></div>";
}
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
            <td rowspan=\"5\" class=\"colalph\"> {$row[VMVNA1]} <br>";
if (trim($row[VMVNA2]) != "") {
    print " $row[VMVNA2] <br>";
}
if (trim($row[VMVNA3]) != "") {
    print " $row[VMVNA3] <br>";
}
if (trim($row[VMVNA4]) != "") {
    print " $row[VMVNA4] <br>";
}
$csz = trim($row[VMVCTY]) . ', ' . $row[VMST] . ' ' . $row[VMZIP];
print "
	$csz
    </td>
    <td>&nbsp;</td>
    <td rowspan=\"5\" class=\"colalph\"> $row[STNAME] <br>
";
if (trim($row[STADR1]) != "") {
    print " $row[STADR1] <br>";
}
if (trim($row[STADR2]) != "") {
    print " $row[STADR2] <br>";
}
if (trim($row[STADR3]) != "") {
    print " $row[STADR3] <br>";
}
$csz = trim($row[STCITY]) . ', ' . $row[STST] . ' ' . $row[STZIP];
print "$csz </td></tr></table>";

print "<table $contentTable> <tr>";
print "<th class=\"colhdr\">Ordered</th>";
print "<th class=\"colhdr\">Required</th>";
print "<th class=\"colhdr\">Buyer</th>";
print "<th class=\"colhdr\">Ship Via</th>";
print "<th class=\"colhdr\">FOB</th>";
print "<th class=\"colhdr\">Terms</th>";
print "<th class=\"colhdr\">Reference</th>";
print "<th class=\"colhdr\">Order Type</th>";
print "\n </tr>";

$wrkDate = Date_CYMD_ISO($row['PODTEN']);
$H_PODTEN = date('l F dS Y', strtotime($wrkDate));
$F_PODTEN = Format_Date($row['PODTEN'], "D");
$wrkDate = Date_CYMD_ISO($row['PORQDT']);
$H_PORQDT = date('l F dS Y', strtotime($wrkDate));
$F_PORQDT = Format_Date($row['PORQDT'], "D");

print "\n <tr class=\"$rowClass\">";
print "\n <td class=\"coldate\"><span $helpCursor title=\"$H_PODTEN\">$F_PODTEN</span></td>";
print "\n <td class=\"coldate\"><span $helpCursor title=\"$H_PORQDT\">$F_PORQDT</span></td>";
print "\n <td class=\"colalph\">$row[BMBNA1]</td>";
print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ShipViaInquiry.d2w/DISPLAY{$altVarBase}&amp;shipVia=" . urlencode(trim($row['POSVSV'])) . "\" onclick=\"$inquiryWinVar\" title=\"Ship Via Quickview\">$row[POSVDS]</a></td>";
print "\n <td class=\"colalph\">$row[POFOB]</td>";
$termsDesc = RetValue("VTRMS='$row[POPTRM]'", "APVTRM", "VTVTDS");
print "\n <td class=\"colalph\">$termsDesc</td>";
print "\n <td class=\"colcode\">$row[POPORF]</td>";
print "\n <td class=\"colalph\">$row[OTDESC]</td>";

print "\n </tr></table>";

print "<table $contentTable> <tr>";

if ($formatToPrint != "Y") {
    print "<th class=\"colhdr\">$optionHeading</th>";
}
print "<th class=\"colhdr\">Line</th>";
print "<th class=\"colhdr\">Rel</th>";
print "<th class=\"colhdr\">S</th>";
print "<th class=\"colhdr\">E</th>";
print "<th class=\"colhdr\">Whs</th>";
print "<th class=\"colhdr\">Item Number</th>";
print "<th class=\"colhdr\">Description</th>";
print "<th class=\"colhdr\">Required</th>";
print "<th class=\"colhdr\">UP</th>";
print "<th class=\"colhdr\">Quantity<br>Ordered</th>";
print "<th class=\"colhdr\">Quantity<br>Received</th>";
print "<th class=\"colhdr\">Quantity<br>Open</th>";
print "<th class=\"colhdr\">Cost</th>";
print "<th class=\"colhdr\">Extended<br>Cost</th>";
if ($CEOEPO != "N") {
    print "<th class=\"colhdr\">O/E Order<br>Number</th>";
}
print "\n </tr>";

require 'stmtSQLClear.php';
$stmtSQL .= " Select POPOMD.*,PDPOL# as PDPOL, PDQTOR/PDPCPB as PDQTOR, (PDQRST+PDQRRT+PDQRVT+PDQRFT)/PDPCPB as PDRECV,";
$stmtSQL .= " (PDQTOR-(PDQRST+PDQRRT+PDQRFT))/PDPCPB as PDOPEN,";
$stmtSQL .= " dec(round((PDQTOR*PDDSCC)/PDPCPB,2),15,2) as PDEXTA, coalesce(UMUMLD,'') as PUOM,";
$stmtSQL .= " (Select count(*) From POOCMT Where OCORD#=$purchaseOrderNumber and OCORL#=PDPOL# and OCBLN#=PDPORL) as CMTCNT";
$fileSQL .= " POPOMD";
$fileSQL .= " left join HDUOM on PDBUOM=UMUOM";
$selectSQL = "PDPO=$purchaseOrderNumber";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By PDPOL,PDPORL ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

$subtotal = 0;
$startRow = 1;
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    $maintainVar = "{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['BMBCHN'])) . "&amp;fromBatchDate=" . urlencode(trim($row['BMBCHD'])) . "&amp;fromBatchBank=" . urlencode(trim($row['BMBCHB'])) . "&amp;fromScript=" . urlencode(trim($scriptName));
    $wrkDate = Date_CYMD_ISO($row['PDRQDT']);
    $H_PDRQDT = date('l F dS Y', strtotime($wrkDate));
    $F_PDRQDT = Format_Date($row['PDRQDT'], "D");
    $F_PDQTOR = Format_Nbr($row['PDQTOR'], $qtyNbrDec, $qtyEditCode, "", "", "");
    $F_PDRECV = Format_Nbr($row['PDRECV'], $qtyNbrDec, $qtyEditCode, "", "", "");
    $F_PDOPEN = Format_Nbr($row['PDOPEN'], $qtyNbrDec, $qtyEditCode, "", "", "");
    $F_PDDSCC = Format_Nbr($row['PDDSCC'], $cstNbrDec, $cstEditCode, "", "", "");
    $F_PDEXTA = Format_Nbr($row['PDEXTA'], "2", $cstEditCode, "", "", "");

    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\">";
    if ($formatToPrint != "Y") {
        print "\n <td class=\"opticon\">";
        print "\n     <a href=\"{$homeURL}{$phpPath}SelectPO.php{$scriptVarBase}&amp;lineNumber=" . urlencode(trim($row['PDPOL'])) . "&amp;releaseNumber=" . urlencode(trim($row['PDPORL'])) . "&amp;tabID=LINE\">$smMoreInfoImage</a>";
        if ($row[PDPOEC] != "N") {
            $itemImage = $row[PDITEM] . $itemImageExt;
            if (file_exists("{$homePath}images/item/{$itemImage}") !== false) {
                $imagePARM = "&amp;imageDisplayPath={$homeURL}{$homePath}images/item/{$itemImage}";
                print "\n <a href=\"{$homeURL}{$cGIPath}ImageDisplay.d2w/DISPLAY{$altVarBase}{$imagePARM}&amp;imageDesc=" . urlencode(trim($row['PDIMDS'])) . "\" onclick=\"$itemImageWinVar\">$foundImage</a>";

            }
        }
        if ($hpopem_OPT['sec_02'] == 'Y' && $POBUSY != 'B') {
            if ($POSTAT = 'O' && $row[PDSTAT] == 'O' && $row[PDPORL] == 0) {
                $cmtIcon = ($row[CMTCNT] > 0) ? $commentExistImage : $commentImage;
                print "<a href=\"{$homeURL}{$phpPath}POComment.php{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrder=" . urlencode(trim($purchaseOrderNumber)) . "&amp;itemNumber=" . urlencode(trim($row['PDITEM'])) . "&amp;itemDesc=" . urlencode(trim($row['PDIMDS'])) . "&amp;cmtLine=" . urlencode(trim($row['PDPOL'])) . "&amp;noMenu=Y\" onclick = \"$commentWinVar\" title=\"Comments\">$cmtIcon </a>";
                $maintainVar = "{$scriptVarBase}&amp;lineNumber=" . urlencode(trim($row['PDPOL']));
                print "\n <a href=\"{$homeURL}{$phpPath}PODetailMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C&amp;noMenu=Y\" onclick = \"$commentWinVar\" title=\"Comments\">$changeImageSml</a>";
                $confirmDesc = Format_Confirm_Desc("Line: {$row[PDPOL]}", "", "Item: {$row[PDITEM]}", "", "Description: {$row[PDIMDS]}", "");
                if ($row[PDQRRT] == "0" && $row[PDQHRT] == "0" && $row[PDQRST] == "0" && $row[PDQRFT] == "0") {
                    if ($row[PDOEPO] == 'Y') {
                        print "\n <a onClick=\"return confirmDeleteLinked('$confirmDesc'); \" href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;closeCancel=X\">{$deleteImageSml}</a>";
                    } else {
                        print "\n <a onClick=\"return confirmDeleteLine('$confirmDesc'); \" href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;closeCancel=X\">{$deleteImageSml}</a>";
                    }
                } elseif ($hpopem_OPT['sec_04'] == 'Y' && $row[PDQRRT] == "0" && $row[PDQHRT] == "0") {
                    $closeIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}smC.gif\" title=\"Close Line\" alt=\"C\">";
                    if ($row[PDOEPO] == 'Y') {
                        print "\n <a onClick=\"return confirmCloseLinked('$confirmDesc'); \" href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;closeCancel=C\">{$closeIcon}</a>";
                    } else {
                        print "\n <a onClick=\"return confirmCloseLine('$confirmDesc'); \" href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;closeCancel=C\">{$closeIcon}</a>";
                    }
                }

            }
        }
        print "\n </td>";
    }
    if ($row[PDPORL] == 0) {
        $line = $row[PDPOL];
        $subtotal += $row['PDEXTA'];
    } else {
        $line = "";
    }

    $openCloseDesc = ($row[PDSTAT] == 'O') ? 'Open' : 'Closed';
    if ($row[PDPORL] == 0) {
        print "\n <td class=\"colnmbr\">$line</td>";
        print "\n <td class=\"colnmbr\">$row[PDPORL]</td>";
        print "\n <td class=\"colcode\"><span $helpCursor title=\"$openCloseDesc\">$row[PDSTAT]</span></td>";
        $entryCodeDesc = RetValue("FLTYPE='OEENTCODE' and FLVALU='{$row[PDPOEC]}'", "SYFLAG", "FLDESC");
        print "\n <td class=\"colcode\"><span $helpCursor title=\"$entryCodeDesc\">$row[PDPOEC]</span></td>";
        if ($row[PDPOEC] != "N") {
            print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}ItemWarehouseSelect.d2w/REPORT{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['PDITEM'])) . "&amp;warehouseNumber=" . urlencode(trim($row['PDOVWH'])) . "\" title=\"View Item/Warehouse\">$row[PDOVWH]</a></td>";
            print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemInquiry.d2w/DISPLAY{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['PDITEM'])) . "\" onclick=\"{$inquiryWinVar}\" title=\"Item Quickview\">$row[PDITEM]</a></td>";
            print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['PDITEM'])) . "&amp;itemDescription=" . urlencode(trim($row['PDIMDS'])) . "\" title=\"View Item\">$row[PDIMDS]</a></td>";
        } else {
            print "\n <td class=\"colnmbr\">$row[PDOVWH]</td>";
            print "\n <td class=\"colalph\">$row[PDITEM]</td>";
            print "\n <td class=\"colalph\">$row[PDIMDS]</td>";
        }
        print "\n <td class=\"colnmbr\"><span $helpCursor title=\"$H_PDRQDT\">$F_PDRQDT</span></td>";
        print "\n <td class=\"colcode\"><span $helpCursor title=\"$row[PUOM]\">$row[PDBUOM]</span></td>";
        if ($row[PDSTAT] == "O" && $POBUSY != 'B' && ($row['PDRECV'] == 0 || $row[PDPOLT] == "B")) {
            $maintainVar = "{$scriptVarBase}&amp;lineNumber=" . urlencode(trim($row['PDPOL']));
            print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}POBlanketMaintain.php{$maintainVar}&amp;noMenu=Y\" onclick = \"$commentWinVar\" title=\"Click here to maintain blanket releases\">$F_PDQTOR</a></td>";
        } else {
            print "\n <td class=\"colnmbr\">$F_PDQTOR</td>";
        }
        print "\n <td class=\"colnmbr\">$F_PDRECV</td>";
        print "\n <td class=\"colnmbr\">$F_PDOPEN</td>";
        print "\n <td class=\"colnmbr\">$F_PDDSCC</td>";
        print "\n <td class=\"colnmbr\">$F_PDEXTA</td>";
        if ($CEOEPO != "N") {
            $oeNumber = RetValue("ODPO#=$purchaseOrderNumber and ODPOL#=$row[PDPOL] and ODPORL=$row[PDPORL]", "OEORDT inner join OEORHD on ODORD#=OEORD#", "ODORD#");
            if ($oeNumber > 0) {
                print "\n <td class=\"colnmbr\"><a onClick=\"saveCurrentURL();\" href=\"{$homeURL}{$cGIPath}SelectOrder.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode($oeNumber) . "\" title=\"View Order Detail\">$oeNumber</a></td>";
            } else {
                print "\n <td>&nbsp;</td>";
            }
        }
    } else {
        print "\n <td class=\"colalph\">&nbsp;</td>";
        print "\n <td class=\"colnmbr\">$row[PDPORL]</td>";
        print "\n <td class=\"colcode\"><span $helpCursor title=\"$openCloseDesc\">$row[PDSTAT]</span></td>";
        print "\n <td class=\"colalph\">&nbsp;</td>";
        print "\n <td class=\"colalph\">&nbsp;</td>";
        print "\n <td class=\"colalph\">&nbsp;</td>";
        print "\n <td class=\"colalph\">&nbsp;</td>";
        print "\n <td class=\"colnmbr\"><span $helpCursor title=\"$H_PDRQDT\">$F_PDRQDT</span></td>";
        print "\n <td class=\"colalph\">&nbsp;</td>";
        print "\n <td class=\"colnmbr\">$F_PDQTOR</td>";
        print "\n <td class=\"colnmbr\">$F_PDRECV</td>";
        print "\n <td class=\"colnmbr\">$F_PDOPEN</td>";
        print "\n <td class=\"colalph\">&nbsp;</td>";
        print "\n <td class=\"colalph\">&nbsp;</td>";
        if ($CEOEPO != "N") {
            print "\n <td class=\"colalph\">&nbsp;</td>";
        }
    }

    print "\n </tr>";
    $startRow++;
    $rowCount++;
}
if ($formatToPrint != "Y") {
    $colSpan = 14;
} else {
    $colSpan = 13;
}
$F_subtotal = Format_Nbr($subtotal, "2", $cstEditCode, "", "", "");
$POTAMT += $subtotal;
if ($POTAMT != $subtotal) {
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Subtotal</td><td class=\"coltotal\">$F_subtotal</td></tr>";
}
if ($POFRTA != 0) {
    $F_POFRTA = Format_Nbr($POFRTA, "2", $cstEditCode, "", "", "");
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Freight Charge</td><td class=\"colnmbr\">$F_POFRTA</td></tr>";
}
if ($POSLTA != 0) {
    $F_POSLTA = Format_Nbr($POSLTA, "2", $cstEditCode, "", "", "");
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Sales Tax</td><td class=\"colnmbr\">$F_POSLTA</td></tr>";
}
if ($POSCGA != 0) {
    $F_POSCGA = Format_Nbr($POSCGA, "2", $cstEditCode, "", "", "");
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Special Charge</td><td class=\"colnmbr\">$F_POSCGA</td></tr>";
}
if ($POMCA1 != 0) {
    $F_POMCA1 = Format_Nbr($POMCA1, "2", $cstEditCode, "", "", "");
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM11 $CORM12</td><td class=\"colnmbr\">$F_POMCA1</td></tr>";
}
if ($POMCA2 != 0) {
    $F_POMCA2 = Format_Nbr($POMCA2, "2", $cstEditCode, "", "", "");
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM21 $CORM22</td><td class=\"colnmbr\">$F_POMCA2</td></tr>";
}
if ($POMCA3 != 0) {
    $F_POMCA3 = Format_Nbr($POMCA3, "2", $cstEditCode, "", "", "");
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM31 $CORM32</td><td class=\"colnmbr\">$F_POMCA3</td></tr>";
}
if ($POMCA4 != 0) {
    $F_POMCA4 = Format_Nbr($POMCA4, "2", $cstEditCode, "", "", "");
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM41 $CORM42</td><td class=\"colnmbr\">$F_POMCA4</td></tr>";
}
if ($POMCA5 != 0) {
    $F_POMCA5 = Format_Nbr($POMCA5, "2", $cstEditCode, "", "", "");
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM51 $CORM52</td><td class=\"colnmbr\">$F_POMCA5</td></tr>";
}
if ($POMCA6 != 0) {
    $F_POMCA6 = Format_Nbr($POMCA6, "2", $cstEditCode, "", "", "");
    print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >$CORM61 $CORM62</td><td class=\"colnmbr\">$F_POMCA6</td></tr>";
}
$F_POTAMT = Format_Nbr($POTAMT, "2", $cstEditCode, "", "", "");
if ($MUPMCD == "Y") {
    $curt = "(" . $POCURT . ")";
} else {
    $curt = "";
}
print "\n <tr><td class=\"colnmbr\" colspan=\"$colSpan\" >Order Total $curt</td><td class=\"coltotal\">$F_POTAMT</td></tr>";

print "\n </table>";
require_once 'WildCardPrint.php';
print "\n  </div></div>";
?>
