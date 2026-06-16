<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$buyer = $_GET['buyer'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once "APControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'QuickLink.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "Buyer";
$scriptName = "BuyerSelect.php";
$scriptVarBase = "{$genericVarBase}&amp;buyer=" . urlencode(trim($buyer)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$altScriptVarBase = "{$altVarBase}&amp;buyer=" . urlencode(trim($buyer)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$attachFolder = "Buyer";
$programName = "HHDBUP";
$quickLinkByUser = "Y";

$backURL = $_SESSION[$fromURL];
if ($backURL == "") {
    $backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=103";
}

$buyerName = RetValue("BMBUYR=$buyer ", "HDBUYR", "BMBNA1");

require_once ($docType);
print "\n <html> <head> ";
$title = "$buyer $buyerName";
require_once ($headInclude);

print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> ";

require_once ($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "BuyerSelect";
if ($formatToPrint == "") {
    require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";

require_once 'QuickLinkTable.php'; // QuickLink Table
require_once 'QuickLinkByUser.php'; // QuickLink By User

require 'stmtSQLClear.php';
$stmtSQL .= " Select distinct(VEND)
              From (Select PHVEND as VEND From POPOHH where PHBUYR={$buyer}
                    union
                    Select POVEND as VEND from POPOMS where POBUYR={$buyer}
              ) myVendors ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));
$myVendors = '';
$startRow = 1;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    $myVendors = ($myVendors == '') ? $row['VEND'] : $myVendors . ', ' . $row['VEND'];
    $startRow ++;
    $rowCount ++;
}

// Program Option Security
$hhdbup_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $hhdbup_OPT['sec_01'];
$sec_02 = $hhdbup_OPT['sec_02'];
$sec_03 = $hhdbup_OPT['sec_03'];
$sec_04 = $hhdbup_OPT['sec_04'];
print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

$attachVarKey = trim($buyer);
$attachForDesc = "";
$attachPrg1 = "HDBUYR Where BMBUYR=$buyer ";
if ($formatToPrint != "Y") {
    $maintainVar = "{$altScriptVarBase}&amp;fromScript=$scriptName";
    print "\n <td class=\"toolbar\">";
    print "\n <a href=\"{$backURL}\" title=\"Back Home\">$portalHome</a> ";
    require_once 'AttachmentInclude.php';
    if ($sec_01 == "Y") {
        print "\n <a href=\"{$homeURL}{$cGIPath}BuyerMaintain.d2w/MAINTAIN{$maintainVar}&amp;maintenanceCode=A\">$addImageLrg</a> ";
    }
    if ($sec_01 == "Y" && $sec_04 == "Y") {
        print "\n <a href=\"{$homeURL}{$cGIPath}BuyerMaintain.d2w/MAINTAIN{$maintainVar}&amp;maintenanceCode=Z\">$copyImageLrg</a> ";
    }
    if ($sec_02 == "Y" || $sec_03 == "Y") {
        print "\n <a href=\"{$homeURL}{$cGIPath}BuyerMaintain.d2w/MAINTAIN{$maintainVar}&amp;maintenanceCode=C\">$changeImageLrg</a> ";
    }
    if ($sec_03 == "Y") {
        $confirmDesc = Format_Confirm_Desc($buyerName, $buyer, "", "", "", "");
        print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$cGIPath}BuyerMaintain.d2w/MAINTAIN{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageLrg</a> ";
    }
    require_once 'FormatToPrint.php';
    require_once 'HelpPage.php';
    print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";
require_once 'ConfMessageDisplay.php';

print $hrTagAttr;

print "\n <table $contentTable>";
Format_Header("Buyer", $buyerName, $buyer);
print "\n </table> ";

// Remove Quick Links if PDB not installed
if ($HDPDRL == "0") {
    foreach ($quicklinkSeqTable as $key => $quickRow) {
        $link = trim(strtolower($quickRow[QDQLNKU]));
        if ($link == 'orderactions' || $link == 'plannedorders') {
            unset($quicklinkSeqTable[$key]);
        }
    }
}

print $hrTagAttr;
require_once 'QuickLinkDisplay.php';
// *****************************************************************************
$x = 1;
foreach ($quicklinkSeqTable as $quickRow) {
    if ($x <= $quicklinkCount) {
        require 'QuickLinkBegLoop.php'; // Quicklink Begin
        if ($qLinkPos !== false) {

            // *****************************************************************************
            // My Vendors
            // *****************************************************************************
            if ($quicklinkRef == "myvendors") {
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                $stmtSQL .= " Select * ";
                $fileSQL .= " HDVENDV01 ";
                $selectSQL .= " VMVEND in ({$myVendors}) ";
                require 'stmtSQLSelect.php';
                $orderBy = "VMDTLA DESC,VMVNA1,VMVEND";
                $stmtSQL .= " Order By {$orderBy}";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
                    'cursor' => DB2_SCROLLABLE
                ));

                print "\n <a name=\"myvendors\"></a> ";
                $displayMaxRowsMsg = "Y";
                $moreURL = "";
                require 'QuickLinkTopOfForm.php';

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        Format_Column_Header("VPVEND", "Vendor<br>Number");
                        Format_Column_Header("VMVNA1", "Vendor<br>Name");
                        Format_Column_Header("VMPHON", "Phone<br>Number");
                        Format_Column_Header("VMDTLA", "Date of<br>Last Activity");
                        Format_Column_Header("LCALTOTAL", "Total Receipts<br>Last Year");
                        Format_Column_Header("LYTDTOTAL", "YTD Receipts<br>Last Year");
                        Format_Column_Header("CYTDTOTAL", "YTD Receipts<br>This Year");
                        Format_Column_Header("YTDVAR", "YTD<br>Variance");
                        Format_Column_Header("OPENTOTAL", "Total<br>Open PO");
                        Format_Column_Header("NEXT5TOTAL", "Due Next<br>5 Days");
                        Format_Column_Header("PASTTOTAL", "Total<br>Past Due PO");
                        Format_Column_Header("PASTPCT", "Percent<br>Past Due");
                        Format_Column_Header("CREDTOTAL", "Credits/<br>Deposits");
                        if (trim($CPSCR1) != '') {
                            Format_Column_Header("UDF1", "$CPSCR1");
                        }
                        if (trim($CPSCR2) != '') {
                            Format_Column_Header("UDF2", "$CPSCR2");
                        }
                        if (trim($CPSCR3) != '') {
                            Format_Column_Header("UDF3", "$CPSCR3");
                        }
                        if (trim($CPSCR4) != '') {
                            Format_Column_Header("UDF4", "$CPSCR4");
                        }
                        if (trim($CPSCR5) != '') {
                            Format_Column_Header("UDF5", "$CPSCR5");
                        }
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }
                    require 'SetRowClass.php';
                    $F_LCALTOTAL = Format_Nbr($row['LCALTOTAL'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_LYTDTOTAL = Format_Nbr($row['LYTDTOTAL'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_CYTDTOTAL = Format_Nbr($row['CYTDTOTAL'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_YTDVAR = Format_Nbr($row['YTDVAR'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_OPENTOTAL = Format_Nbr($row['OPENTOTAL'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_NEXT5TOTAL = Format_Nbr($row['NEXT5TOTAL'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_PASTTOTAL = Format_Nbr($row['PASTTOTAL'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_CREDTOTAL = Format_Nbr($row['CREDTOTAL'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_PASTPCT = Format_Nbr($row['PASTPCT'], $pctNbrDec, $pctEditCode, 'Y', '', '');
                    $F_VMPHON = EditPhoneNumber($row['VMPHON']);
                    $F_VMDTLA = Format_Date($row['VMDTLA'], "D");

                    print "\n <tr class=\"$rowClass\">
                    <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}VendorDashboard.php{$genericVarBase}&amp;vendorNumber={$row[VMVEND]}\" title=\"View Vendor Dashboard\">$row[VMVEND]</a></td>
                    <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$row[VMVEND]}\" title=\"View Vendor\">$row[VMVNA1]</a></td>
                    <td class=\"colalph\">$F_VMPHON</td>
                    <td class=\"coldate\">$F_VMDTLA</td>
                    <td class=\"colnmbr\">$F_LCALTOTAL</td>
                    <td class=\"colnmbr\">$F_LYTDTOTAL</td>
                    <td class=\"colnmbr\">$F_CYTDTOTAL</td>
                    <td class=\"colnmbr\">$F_YTDVAR</td>
                    <td class=\"colnmbr\">$F_OPENTOTAL</td>
                    <td class=\"colnmbr\">$F_NEXT5TOTAL</td>
                    <td class=\"colnmbr\">$F_PASTTOTAL</td>
                    <td class=\"colnmbr\">$F_PASTPCT</td>
                    <td class=\"colnmbr\">$F_CREDTOTAL</td>";
                    if (trim($CPSCR1) != '') {
                        print "\n <td class=\"colalph\">$row[VMUDF1]</td>";
                    }
                    if (trim($CPSCR2) != '') {
                        print "\n <td class=\"colalph\">$row[VMUDF2]</td>";
                    }
                    if (trim($CPSCR3) != '') {
                        print "\n <td class=\"colalph\">$row[VMUDF3]</td>";
                    }
                    if (trim($CPSCR4) != '') {
                        print "\n <td class=\"colalph\">$row[VMUDF4]</td>";
                    }
                    if (trim($CPSCR5) != '') {
                        print "\n <td class=\"colalph\">$row[VMUDF5]</td>";
                    }
                    print "\n </tr> ";

                    $startRow ++;
                    $rowCount ++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table>
                          </fieldset> ";
            }

            // *****************************************************************************
            // Open Items
            // *****************************************************************************
            if ($quicklinkRef == "openitems") {
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                $uv_WarehouseName = "PDOVWH";
                $uv_VendorName = "POVEND";
                require 'UserView.php';
                $appendWildCard = "N"; // Do not append wildCardSearch
                $stmtSQL .= " Select POTYPE, POVEND, PDITEM, PDOVWH, PDIMDS, PDPO, PDPOL# as PDPOL, PDPORL, PDDSCC, PDPCPB, ";
                $stmtSQL .= "        PDRQDT, PDQTOR, PDQHRT, PDQRFT, PDQRRT, PDQRST, PDQRVT, PDQRST+PDQRRT+PDQRFT as PDQRCV, VN_VMVNA1";
                $fileSQL .= " POPOMS08 ";
                $selectSQL .= " POBUYR=$buyer ";
                require 'stmtSQLSelect.php';
                $orderBy = "PDRQDT";
                $stmtSQL .= " Order By {$orderBy}";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
                    'cursor' => DB2_SCROLLABLE
                ));

                print "\n <a name=\"openitems\"></a> ";
                $displayMaxRowsMsg = "Y";
                $moreURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=186&amp;fKey1=POBUYR&amp;fVal1=$buyer&amp;tag=QSEARCH";
                require 'QuickLinkTopOfForm.php';

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        Format_Column_Header("POVEND", "Vendor<br>Number");
                        Format_Column_Header("VN_VMVNA1", "Vendor<br>Name");
                        Format_Column_Header("POTYPE", "Order<br>Type");
                        Format_Column_Header("PDITEM", "Item<br>Number");
                        Format_Column_Header("PDOVWH", "Whs");
                        Format_Column_Header("PDIMDS", "Description");
                        Format_Column_Header("PDPO", "Purchase<br>Order");
                        Format_Column_Header("PDPOL#", "Line");
                        Format_Column_Header("PDPORL", "Rel");
                        Format_Column_Header("PDRQDT", "Required<br>Date");
                        Format_Column_Header("PDQTOR", "Quantity<br>Ordered");
                        Format_Column_Header("xxx", "Quantity<br>Held In Receiving");
                        Format_Column_Header("xxx", "Quantity<br>Received To Date");
                        Format_Column_Header("PDDSCC", "Cost");
                        Format_Column_Header("xxx", "Extended<br>Cost");
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }

                    require 'SetRowClass.php';
                    $F_PDRQDT = Format_Date($row['PDRQDT'], "D");
                    $row['PDQTOR'] = ($row['PDPCPB'] > "0") ? bcdiv($row['PDQTOR'], $row['PDPCPB']) : $row['PDQTOR'];
                    $F_PDQTOR = Format_Nbr($row['PDQTOR'], $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $F_PDQHRT = Format_Nbr($row['PDQHRT'], $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $F_PDQRCV = Format_Nbr($row['PDQRCV'], $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $F_PDOPEN = Format_Nbr(($row['PDQTOR'] - $row['PDQRCV']), $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $F_PDDSCC = Format_Nbr($row['PDDSCC'], $prcNbrDec, $prcEditCode, '', '', '');
                    $F_EXT = Format_Nbr($row[PDQTOR] * $row[PDDSCC], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $poTypeDesc = RetValue("OTAPID='PO' and OTOTCD='$row[POTYPE]'", "HDOTYP", "OTDESC");

                    $itemCnt = RetValue("IMITEM='$row[PDITEM]'", "HDIMST", "char(count(*))");
                    if ($itemCnt == "0") {
                        $itemSelect = $row[PDITEM];
                        $itemWhsSelect = $row[PDIMDS];
                    } else {
                        $itemSelect = "<a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PDITEM]}&amp;itemDescription={$row[PDIMDS]}\" title=\"View Item\">$row[PDITEM]</a>";
                        $itemWhsSelect = "<a href=\"{$homeURL}{$cGIPath}ItemWarehouseSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PDITEM]}&amp;warehouseNumber={$row[PDOVWH]}\" title=\"View Item/Warehouse\">$row[PDIMDS]</a>";
                    }

                    print "\n <tr class=\"$rowClass\">
                                  <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}VendorDashboard.php{$genericVarBase}&amp;vendorNumber={$row[POVEND]}\" title=\"View Vendor Dashboard\">$row[POVEND]</a></td>
                                  <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$row[POVEND]}\" title=\"View Vendor\">$row[VN_VMVNA1]</a></td>
                                  <td class=\"colcode\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$poTypeDesc}\">$row[POTYPE]</span></td>
                                  <td class=\"colalph\">$itemSelect</td>
                                  <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}WarehouseSelect.d2w/REPORT{$altVarBase}&amp;warehouseNumber={$row[PDOVWH]}\" title=\"View Warehouse\">$row[PDOVWH]</a></td>
                                  <td class=\"colalph\">$itemWhsSelect</td>
                                  <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}SelectPO.php{$genericVarBase}&amp;vendorNumber={$row[POVEND]}&amp;vendorName={$row[VN_VMVNA1]}&amp;purchaseOrderNumber={$row[PDPO]}\" title=\"View Purchase Order\">{$row[PDPO]}</a></td>
                                  <td class=\"colnmbr\">$row[PDPOL]</td>
                                  <td class=\"colnmbr\">$row[PDPORL]</td>
                                  <td class=\"coldate\">$F_PDRQDT</td>
                                  <td class=\"colnmbr\">$F_PDQTOR</td>
                                  <td class=\"colnmbr\">$F_PDQHRT</td>
                                  <td class=\"colnmbr\">$F_PDQRCV</td>
                                  <td class=\"colnmbr\">$F_PDDSCC</td>
                                  <td class=\"colnmbr\">$F_EXT</td>
                              </tr> ";

                    $startRow ++;
                    $rowCount ++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table>
                          </fieldset> ";
            }

            // *****************************************************************************
            // Order Actions
            // *****************************************************************************
            if ($quicklinkRef == "orderactions") {
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                $uv_VendorName = "POVEND";
                require 'UserView.php';
                $appendWildCard = "N"; // Do not append wildCardSearch
                $stmtSQL .= " Select * ";
                $fileSQL .= " MRMOWM03 ";
                $selectSQL .= " OWBAC=$buyer ";
                require 'stmtSQLSelect.php';
                $orderBy = "OWADAT";
                $stmtSQL .= " Order By {$orderBy}";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
                    'cursor' => DB2_SCROLLABLE
                ));

                print "\n <a name=\"orderactions\"></a> ";
                $displayMaxRowsMsg = "Y";
                $moreURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=461&amp;fKey1=OWBAC&amp;fVal1=$buyer&amp;tag=QSEARCH";
                require 'QuickLinkTopOfForm.php';

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        Format_Column_Header("POVEND", "Vendor<br>Number");
                        Format_Column_Header("VMVNA1", "Vendor<br>Name");
                        Format_Column_Header("OWPLT", "Plant");
                        Format_Column_Header("OWPN", "Item<br>Number");
                        Format_Column_Header("IMIMDS", "Description");
                        Format_Column_Header("OWORD", "Order<br>Number");
                        Format_Column_Header("OWCQT2", "Current<br>Order Quantity");
                        Format_Column_Header("OWOACT", "Order<br>Action Code");
                        Format_Column_Header("OWADAT", "Order<br>Action Date");
                        Format_Column_Header("OWDAT", "Due<br>Date");
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }
                    require 'SetRowClass.php';
                    $oword = trim($row['OWORD']);
                    if ($row[OWORD] > "0") {
                        $poNumber = RetValue("POPO={$row[OWORD]} and POVEND={$row[POVEND]}", "POPOMS", "coalesce(POPO,'')");
                    }
                    $F_OWADAT = Format_Date_ISO($row['OWADAT'], "D");
                    $F_OWDAT = Format_Date_ISO($row['OWDAT'], "D");
                    $F_OWCQT2 = Format_Nbr($row['OWCQT2'], $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $orderActionDesc = RetValue("FLTYPE='ORDACTION' AND FLVALU='$row[OWOACT]'", "SYFLAG", "FLDESC");

                    print "\n <tr class=\"$rowClass\"> ";
                    if ($row[POVEND] > 0) {
                        print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}VendorDashboard.php{$genericVarBase}&amp;vendorNumber={$row[POVEND]}\" title=\"View Vendor Dashboard\">$row[POVEND]</a></td> ";
                        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$row[POVEND]}\" title=\"View Vendor\">$row[VMVNA1]</a></td> ";
                    } else {
                        print "\n     <td class=\"colnmbr\">&nbsp;</td> ";
                        print "\n     <td class=\"colalph\">&nbsp;</td> ";
                    }
                    print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}PlantSelect.d2w/REPORT{$altVarBase}&amp;plantNumber={$row[OWPLT]}\" title=\"View Plant\">$row[OWPLT]</a></td> ";
                    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[OWPN]}&amp;itemDescription={$row[IMIMDS]}\" title=\"View Item\">$row[OWPN]</a></td> ";
                    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemPlantSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[OWPN]}&amp;plantNumber={$row[OWPLT]}\" title=\"View Item/Plant\">$row[IMIMDS]</a></td> ";
                    if ($poNumber > "0") {
                        print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}SelectPO.php{$genericVarBase}&amp;vendorNumber={$row[POVEND]}&amp;purchaseOrderNumber={$oword}&amp;vendorName={$row[VMVNA1]}\" title=\"View Purchase Order\">{$oword}</a></td> ";
                    } else {
                        print "\n     <td class=\"colnmbr\">{$oword}</td> ";
                    }
                    print "\n     <td class=\"colnmbr\">$F_OWCQT2</td> ";
                    print "\n     <td class=\"colcode\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$orderActionDesc}\">$row[OWOACT]</span></td> ";
                    print "\n     <td class=\"coldate\">$F_OWADAT</td> ";
                    print "\n     <td class=\"coldate\">$F_OWDAT</td> ";
                    print "\n </tr> ";

                    $startRow ++;
                    $rowCount ++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table> ";
                print "\n </fieldset> ";
            }

            // *****************************************************************************
            // Planned Orders
            // *****************************************************************************
            if ($quicklinkRef == "plannedorders") {
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                $appendUserView = "N"; // Do not append user view security
                $appendWildCard = "N"; // Do not append wildCardSearch
                $stmtSQL .= " Select * ";
                $fileSQL .= " HDMPLM05 ";
                $selectSQL .= " (PLBAC = $buyer or (PLBAC = 0 and PLVEND in ($myVendors)))";
                require 'stmtSQLSelect.php';
                $orderBy = "PLDAT";
                $stmtSQL .= " Order By $orderBy";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
                    'cursor' => DB2_SCROLLABLE
                ));

                print "\n <a name=\"plannedorders\"></a> ";
                $displayMaxRowsMsg = "Y";
                $moreURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=460&amp;fKey1=PLBAC&amp;fVal1=$buyer&amp;tag=QSEARCH";
                require 'QuickLinkTopOfForm.php';

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        Format_Column_Header("PLVEND", "Vendor<br>Number");
                        Format_Column_Header("VMVNA1", "Vendor<br>Name");
                        Format_Column_Header("PLPLT", "Plant");
                        Format_Column_Header("PLPN", "Item Number");
                        Format_Column_Header("IMIMDS", "Item Description");
                        Format_Column_Header("PLPTYP", "Part Type");
                        Format_Column_Header("PLPQTY", "Planned<br>Order Quantity");
                        Format_Column_Header("PLDAT", "Due<br>Date");
                        Format_Column_Header("PLSDAT", "Start<br>Date");
                        Format_Column_Header("PLCPNS", "Component<br>Shortage");
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }

                    $F_PLDAT = Format_Date_ISO($row['PLDAT'], "D");
                    $F_PLSDAT = Format_Date_ISO($row['PLSDAT'], "D");
                    $F_PLPQTY = Format_Nbr($row['PLPQTY'], $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $compShortDesc = RetValue("FLTYPE='BY' AND FLVALU='$row[PLCPNS]'", "SYFLAG", "FLDESC");
                    $partTypeDesc = RetValue("FLTYPE='PARTTYPE' AND FLVALU='$row[PLPTYP]'", "SYFLAG", "FLDESC");
                    if ($row[PLCPNS] == "Y") {
                        $F_PLCPNS = "<a href=\"{$homeURL}{$cGIPath}ProductStructureIndentedStockStatusInquiry.d2w/DISPLAY{$altVarBase}&amp;plantNumber={$row[PLPLT]}&amp;itemNumber={$row[PLPN]}\" onclick=\"$inquiryWinVar\" title=\"Product Structure Idented Stock Status\">$row[PLCPNS]</a>";
                    } else {
                        $F_PLCPNS = "";
                    }
                    $row[PLVEND] = ($row[PLVEND] == "0") ? '' : $row[PLVEND];

                    require 'SetRowClass.php';
                    print "\n <tr class=\"$rowClass\">
                    			  <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}VendorDashboard.php{$genericVarBase}&amp;vendorNumber={$row[PLVEND]}\" title=\"View Vendor Dashboard\">$row[PLVEND]</a></td>
                    			  <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$row[PLVEND]}\" title=\"View Vendor\">$row[VMVNA1]</a></td>
                                  <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}PlantSelect.d2w/REPORT{$altVarBase}&amp;plantNumber={$row[PLPLT]}\" title=\"View Plant\">$row[PLPLT]</a></td>
                                  <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PLPN]}&amp;itemDescription={$row[IMIMDS]}\" title=\"View Item\">$row[PLPN]</a></td>
                                  <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemPlantSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PLPN]}&amp;plantNumber={$row[PLPLT]}\" title=\"View Item/Plant\">$row[IMIMDS]</a></td>
                                  <td class=\"colcode\">$row[PLPTYP]</td>
                                  <td class=\"colnmbr\">$F_PLPQTY</td>
                                  <td class=\"coldate\">$F_PLDAT</td>
                                  <td class=\"coldate\">$F_PLSDAT</td>
                                  <td class=\"colcode\">$F_PLCPNS</td>
                              </tr> ";

                    $startRow ++;
                    $rowCount ++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table>
                          </fieldset> ";
            }

            // *****************************************************************************
            // Receipt History
            // *****************************************************************************
            if ($quicklinkRef == "receipthistory") {
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                require 'UserView.php';
                $appendWildCard = "N"; // Do not append wildCardSearch
                $stmtSQL .= " Select POPOHHV02.*,PIPOL# as PIPOL, PISEQ# as PISEQ ";
                $fileSQL .= " POPOHHV02 ";
                $selectSQL .= " PHBUYR=$buyer ";
                require 'stmtSQLSelect.php';
                $orderBy = "PIDLRC desc";
                $stmtSQL .= " Order By {$orderBy}";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
                    'cursor' => DB2_SCROLLABLE
                ));

                print "\n <a name=\"receipthistory\"></a> ";
                $displayMaxRowsMsg = "Y";
                $moreURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=505&amp;fKey1=PHBUYR&amp;fVal1=$buyer&amp;tag=QSEARCH";
                require 'QuickLinkTopOfForm.php';

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        Format_Column_Header("PHVEND", "Vendor<br>Number");
                        Format_Column_Header("VN_VMVNA1", "Vendor<br>Name");
                        Format_Column_Header("PHPO", "Purchase<br>Order");
                        Format_Column_Header("PISEQ", "Seq");
                        Format_Column_Header("PIPOL", "Line");
                        Format_Column_Header("PIPORL", "Rel");
                        Format_Column_Header("PIITEM", "Item<br>Number");
                        Format_Column_Header("PIOVWH", "Whs");
                        Format_Column_Header("PIIMDS", "Item Description");
                        Format_Column_Header("PIPCLS", "Product<br>Class");
                        Format_Column_Header("PIDLRC", "Date<br>Received");
                        Format_Column_Header("PIQTOR", "Quantity<br>Ordered");
                        Format_Column_Header("QTYOPEN", "Quantity<br>Open");
                        Format_Column_Header("PITRQT", "Transaction<br>Quantity");
                        Format_Column_Header("PITRXN", "Trans<br>Code");
                        Format_Column_Header("PIDSCC", "Purchase<br>Cost");
                        Format_Column_Header("PIBUOM", "Pur<br>UOM");
                        Format_Column_Header("EXTCOST", "Extended<br>Cost");
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }

                    require 'SetRowClass.php';
                    $F_PIDLRC = Format_Date($row['PIDLRC'], "D");
                    $F_PIQTOR = Format_Nbr($row['PIQTOR'], $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $F_QTYOPEN = Format_Nbr($row['QTYOPEN'], $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $F_PITRQT = Format_Nbr($row['PITRQT'], $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $F_PIDSCC = Format_Nbr($row['PIDSCC'], $cstNbrDec, $cstEditCode, '', '', '');
                    $F_EXTCOST = Format_Nbr($row[EXTCOST], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $prodClassDesc = RetValue("PCPCLS='$row[PIPCLS]'", "HDPCLS", "PCPCDS");
                    $purUOMDesc = RetValue("UMUOM='$row[PIBUOM]'", "HDUOM", "UMUMLD");
                    $transCodeDesc = RetValue("TTTYPE='$row[PITRXN]'", "HDTTYP", "TTDESC");

                    $itemCnt = RetValue("IMITEM='$row[PIITEM]'", "HDIMST", "char(count(*))");
                    if ($itemCnt == "0") {
                        $itemSelect = $row[PIITEM];
                        $itemWhsSelect = $row[PIIMDS];
                    } else {
                        $itemSelect = "<a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PIITEM]}&amp;itemDescription={$row[PIIMDS]}\" title=\"View Item\">$row[PIITEM]</a>";
                        $itemWhsSelect = "<a href=\"{$homeURL}{$cGIPath}ItemWarehouseSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PIITEM]}&amp;warehouseNumber={$row[PIOVWH]}\" title=\"View Item/Warehouse\">$row[PIIMDS]</a>";
                    }

                    print "\n <tr class=\"$rowClass\">
                    <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}VendorDashboard.php{$genericVarBase}&amp;vendorNumber={$row[PHVEND]}\" title=\"View Vendor Dashboard\">$row[PHVEND]</a></td>
                    <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$row[PHVEND]}\" title=\"View Vendor\">$row[VN_VMVNA1]</a></td>
                    <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}SelectPO.php{$genericVarBase}&amp;tabID=RECEIPTS&amp;vendorNumber={$row[PHVEND]}&amp;vendorName={$row[VN_VMVNA1]}&amp;purchaseOrderNumber={$row[PIPO]}\" title=\"View Purchase Order\">{$row[PIPO]}</a></td>
                    <td class=\"colnmbr\">$row[PISEQ]</td>
                    <td class=\"colnmbr\">$row[PIPOL]</td>
                    <td class=\"colnmbr\">$row[PIPORL]</td>
                    <td class=\"colalph\">$itemSelect</td>
                    <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}WarehouseSelect.d2w/REPORT{$altVarBase}&amp;warehouseNumber={$row[PIOVWH]}\" title=\"View Warehouse\">$row[PIOVWH]</a></td>
                    <td class=\"colalph\">$itemWhsSelect</td>
                    <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$prodClassDesc}\">$row[PIPCLS]</span></td>
                    <td class=\"coldate\">$F_PIDLRC</td>
                    <td class=\"colnmbr\">$F_PIQTOR</td>
                    <td class=\"colnmbr\">$F_QTYOPEN</td>
                    <td class=\"colnmbr\">$F_PITRQT</td>
                    <td class=\"colcode\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$transCodeDesc}\">$row[PITRXN]</span></td>
                    <td class=\"colnmbr\">$F_PIDSCC</td>
                    <td class=\"colcode\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$purUOMDesc}\">$row[PIBUOM]</span></td>
                    <td class=\"colnmbr\">$F_EXTCOST</td>
                    </tr> ";

                    $startRow ++;
                    $rowCount ++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table>
                          </fieldset> ";
            }

            // *****************************************************************************
            // Requisitions
            // *****************************************************************************
            if ($quicklinkRef == "requisitions") {
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                $stmtSQL .= " Select a.*,coalesce(b.IMIMDS,'') as IMIMDS,coalesce(c.WHWHNM,'') as WHWHNM ";
                $fileSQL .= " POSGPOV01 a left join HDIMST b on a.PSITEM=b.IMITEM
                                          left join HDWHSM c on a.PSWHS=c.WHWHS";
                $selectSQL .= " PSTYPE in ('R','S') and (PSBUYR={$buyer} or (PSBUYR=0 and PSVEND in ($myVendors)) or POBUYR={$buyer} or (POBUYR=0 and POVEND in ($myVendors))) ";
                require 'stmtSQLSelect.php';
                $orderBy = "PSRQDT,PSITEM";
                $stmtSQL .= " Order By {$orderBy}";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
                    'cursor' => DB2_SCROLLABLE
                ));
                ;
                print "\n <a name=\"requisitions\"></a> ";
                $displayMaxRowsMsg = "Y";
                $moreURL = "{$homeURL}{$phpPath}CreatePOItems.php{$scriptVarBase}";
                require 'QuickLinkTopOfForm.php';

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        Format_Column_Header("PSVEND", "Vendor<br>Number");
                        Format_Column_Header("PSVNA1", "Vendor<br>Name");
                        Format_Column_Header("PSTYPE", "Type");
                        Format_Column_Header("PSITEM", "Item<br>Number");
                        Format_Column_Header("PSWHS", "Whs");
                        Format_Column_Header("PSIMDS", "Description");
                        Format_Column_Header("PSPO", "Purchase<br>Order");
                        Format_Column_Header("PSRQDT", "Required<br>Date");
                        Format_Column_Header("PSSQOR", "Quantity");
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }

                    require 'SetRowClass.php';
                    $F_PSRQDT = Format_Date($row['PSRQDT'], "D");
                    $F_PSSQOR = Format_Nbr($row['PSSQOR'], $qtyNbrDec, $qtyEditCode, 'Y', '', '');
                    $row[PSVEND] = ($row[PSVEND] == '0') ? '' : $row[PSVEND];
                    $row[PSPO] = ($row[PSPO] == '0') ? '' : $row[PSPO];
                    $row[PSWHS] = ($row[PSWHS] == '0') ? '' : $row[PSWHS];
                    if (trim($row[IMIMDS]) != '') {
                        $row[PSIMDS] = $row[IMIMDS];
                        $itemSelect = "<a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PSITEM]}&amp;itemDescription={$row[PSIMDS]}\" title=\"View Item\">$row[PSITEM]</a>";
                        $itemWhsSelect = "<a href=\"{$homeURL}{$cGIPath}ItemWarehouseSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PSITEM]}&amp;warehouseNumber={$row[PSWHS]}\" title=\"View Item/Warehouse\">$row[PSIMDS]</a>";
                    } else {
                        $itemSelect = "$row[PSITEM]";
                        $itemWhsSelect = "$row[PSIMDS]";
                    }
                    if (trim($row[WHWHNM]) != '') {
                        $whsSelect = "<a href=\"{$homeURL}{$cGIPath}WarehouseSelect.d2w/REPORT{$altVarBase}&amp;warehouseNumber={$row[PSWHS]}\" title=\"View Warehouse\">$row[PSWHS]</a>";
                    } else {
                        $whsSelect = "$row[PSWHS]";
                    }
                    if ($row[PSPO] > '0' && $row[PSVEND] > '0') {
                        $poSelect = "<a href=\"{$homeURL}{$phpPath}SelectPO.php{$genericVarBase}&amp;vendorNumber={$row[PSVEND]}&amp;vendorName={$row[PSVNA1]}&amp;purchaseOrderNumber={$row[PSPO]}\" title=\"View Purchase Order\">{$row[PSPO]}</a>";
                    } else {
                        $poSelect = $row[PSPO];
                    }

                    print "\n <tr class=\"$rowClass\">
                    <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}VendorDashboard.php{$genericVarBase}&amp;vendorNumber={$row[PSVEND]}\" title=\"View Vendor Dashboard\">$row[PSVEND]</a></td>
                    <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$row[PSVEND]}\" title=\"View Vendor\">$row[PSVNA1]</a></td>
                    <td class=\"colcode\">$row[PSTYPE]</td>
                    <td class=\"colalph\">{$itemSelect}</td>
                    <td class=\"colnmbr\">{$whsSelect}</td>
                    <td class=\"colalph\">{$itemWhsSelect}</td>
                    <td class=\"colnmbr\">{$poSelect}</td>
                    <td class=\"coldate\">$F_PSRQDT</td>
                    <td class=\"colnmbr\">$F_PSSQOR</td>
                    </tr> ";

                    $startRow ++;
                    $rowCount ++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table>
                          </fieldset> ";
            }

            // *****************************************************************************
            // Vendor Performance
            // *****************************************************************************
            if ($quicklinkRef == "vendorperformance") {
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                $uv_VendorName = "VPVEND";
                require 'UserView.php';
                $appendWildCard = "N"; // Do not append wildCardSearch
                $stmtSQL .= " Select VPCYR, VPVEND, VMVNA1, VP#TRC as VP_TRC, VP#TRV as VP_TRV, VP#ERC as VP_ERC, VP#LRC as VP_LRC,
                              VPTDSE, VPTDSL, VPYTDO, VPYTDR, VPYTDS";
                $fileSQL .= " HDJLF27 ";
                $selectSQL .= " VPVEND in ($myVendors) ";
                require 'stmtSQLSelect.php';
                $orderBy = "VPCYR DESC, VMVNA1";
                $stmtSQL .= " Order By {$orderBy}";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
                    'cursor' => DB2_SCROLLABLE
                ));

                print "\n <a name=\"vendorperformance\"></a> ";
                $displayMaxRowsMsg = "Y";
                $moreURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=463&amp;tag=QSEARCH";
                require 'QuickLinkTopOfForm.php';

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        Format_Column_Header("VPVEND", "Vendor<br>Number");
                        Format_Column_Header("VMVNA1", "Vendor<br>Name");
                        Format_Column_Header("VPCYR", "Year");
                        Format_Column_Header("VP_TRC", "Total Lines<br>Received");
                        Format_Column_Header("VP_TRV", "Total Lines<br>Returned");
                        Format_Column_Header("VP_ERC", "Early<br>Receipts");
                        Format_Column_Header("VP_LRC", "Late<br>Receipts");
                        Format_Column_Header("VPTDSE", "Days<br>Early");
                        Format_Column_Header("VPTDSL", "Days<br>Late");
                        Format_Column_Header("VPYTDO", "Amount<br>Ordered");
                        Format_Column_Header("VPYTDR", "Amount<br>Received");
                        Format_Column_Header("VPYTDS", "Amount<br>Returned");
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }
                    require 'SetRowClass.php';
                    $F_VPYTDO = Format_Nbr($row['VPYTDO'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_VPYTDR = Format_Nbr($row['VPYTDR'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_VPYTDS = Format_Nbr($row['VPYTDS'], $amtNbrDec, $amtEditCode, 'Y', '', '');
                    $F_VPCYR = YearFromCYY($row['VPCYR']);

                    print "\n <tr class=\"$rowClass\">
                                  <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}VendorDashboard.php{$genericVarBase}&amp;vendorNumber={$row[VPVEND]}\" title=\"View Vendor Dashboard\">$row[VPVEND]</a></td>
                                  <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$row[VPVEND]}\" title=\"View Vendor\">$row[VMVNA1]</a></td>
                                  <td class=\"colnmbr\">$F_VPCYR</td>
                                  <td class=\"colnmbr\">$row[VP_TRC]</td>
                                  <td class=\"colnmbr\">$row[VP_TRV]</td>
                                  <td class=\"colnmbr\">$row[VP_ERC]</td>
                                  <td class=\"colnmbr\">$row[VP_LRC]</td>
                                  <td class=\"colnmbr\">$row[VPTDSE]</td>
                                  <td class=\"colnmbr\">$row[VPTDSL]</td>
                                  <td class=\"colnmbr\">$F_VPYTDO</td>
                                  <td class=\"colnmbr\">$F_VPYTDR</td>
                                  <td class=\"colnmbr\">$F_VPYTDS</td>
                              </tr> ";

                    $startRow ++;
                    $rowCount ++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table>
                          </fieldset> ";
            }

            // *****************************************************************************
            // Attachments
            // *****************************************************************************
            require 'AttachmentSQLInclude.php';
        }
        require 'QuickLinkEndLoop.php'; // Quicklink End
    }
}

print $hrTagAttr;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";
exit();

?>