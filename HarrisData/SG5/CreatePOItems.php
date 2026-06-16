<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$vendorNumber = $_GET['vendorNumber'];
$vendorPassed = (isset($vendorNumber) && trim($vendorNumber) != '') ? true : null;
$orderControl = (isset($_GET['orderControl']) && trim($_GET['orderControl'] != '')) ? $_GET['orderControl'] : null;
$errorHdr = (isset($_GET['errorHdr']) && trim($_GET['errorHdr'] != '')) ? $_GET['errorHdr'] : null;
$errorItem = (isset($_GET['errorItem']) && trim($_GET['errorItem'] != '')) ? $_GET['errorItem'] : null;

$resetReturnURL = (isset($_GET['resetReturnURL'])) ? $_GET['resetReturnURL'] : null;
$fromBuyer = (isset($_GET['buyer']) && trim($_GET['buyer'] != '')) ? $_GET['buyer'] : null;
$fromVendor = (isset($_GET['fromVendor']) && trim($_GET['fromVendor'] != '')) ? $_GET['fromVendor'] : null;
$fromReq = (isset($_GET['fromReq']) && trim($_GET['fromReq'] != '')) ? $_GET['fromReq'] : null;
$selItems = (isset($_GET['selItems'])) ? $_GET['selItems'] : null;

$userDefHdrCnt = 0;
$userDefDtlCnt = 0;

require_once 'SetLibraryList.php';
require_once "POControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'POUserDefinedInclude.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

if ($resetReturnURL == 'Y') {
    unset ($_SESSION["returnURL"]);
    $tag = 'QSEARCH';
}
if ($selItems) {
    $_POST['qsOper'] = '>';
    $_POST['qsValue'] = '0';
    $_POST['qsName'] = 'D1QTOR|null|Order Quantity|N|';
    $tag = 'QSEARCH';
} elseif ($fromBuyer) {
    $buyerName = RetValue("BMBUYR=$fromBuyer", "HDBUYR", "BMBNA1");
    $_POST['qsOper'] = 'LIKE';
    $_POST['qsValue'] = $buyerName;
    $_POST['qsName'] = 'PSBNA1|null|Buyer|A|';
    $tag = 'QSEARCH';
} elseif ($fromVendor) {
    $vendorName = RetValue("VMVEND=$fromVendor", "HDVEND", "VMVNA1");
    $_POST['qsOper'] = 'LIKE';
    $_POST['qsValue'] = $vendorName;
    $_POST['qsName'] = 'PSVNA1|null|Vendor|A|';
    $tag = 'QSEARCH';
} elseif ($fromReq) {
    $_POST['qsOper'] = 'LIKE';
    $_POST['qsValue'] = $fromReq;
    $_POST['qsName'] = 'PSREQN|null|Requisition Number|A|';
    $tag = 'QSEARCH';
    unset ($_SESSION['po_reqNbr']);
    unset ($_SESSION ['po_reqFilter']);
}

if (is_null($orderControl)) {
    if (!is_null($fromBuyer) || !is_null($fromVendor)) {
        unset ($_SESSION["returnURL"]);
        if (!is_null($fromBuyer)) {
            $_SESSION["returnURL"] = "{$homeURL}{$phpPath}BuyerSelect.php{$genericVarBase}&amp;buyer={$fromBuyer}&amp;buyerDesc={$buyerName}";
        } elseif (!is_null($fromVendor) && $fromVendor != '@@vendorNumber') {
            $_SESSION["returnURL"] = "{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$fromVendor}&amp;vendorName={$vendorName}";
        }
    }
    $stmtSQL1 = " Select * From POHDRW Where H1USER='{$userProfile}' Fetch First Row Only";
    $sqlResult1 = db2_exec($i5Connect->getConnection(), $stmtSQL1);
    $hdrRow = db2_fetch_assoc($sqlResult1);
    $orderControl = (array_key_exists('H1OCTL', $hdrRow)) ? $hdrRow['H1OCTL'] : null;
}

if ($orderControl > 0) {
    $stmtSQL2 = " Select * From POHDRW Where H1OCTL={$orderControl} Fetch First Row Only";
    $sqlResult2 = db2_exec($i5Connect->getConnection(), $stmtSQL2);
    $hdrRow = db2_fetch_assoc($sqlResult2);
    $vendorNumber = (array_key_exists('H1VEND', $hdrRow)) ? $hdrRow['H1VEND'] : null;
    if (is_null($vendorNumber)) {
        $orderControl = '';
    }

    $itemCnt = RetValue("D1OCTL={$orderControl}", "PODTLW", "char(count(*))");
    $itemWhsError = 0;
    if ($itemCnt > 0) {
        $stmtSQL4 = " Select count(*) NOITEMWHSCNT From PODTLW a Where a.D1OCTL={$orderControl}
                     and a.D1POEC in ('S','X') and not exists (Select * from HDIWHS where IWITEM=a.D1ITEM and IWWHS=a.D1OVWH)";
        $sqlResult4 = db2_exec($i5Connect->getConnection(), $stmtSQL4);
        $itemWhsRow = db2_fetch_assoc($sqlResult4);
        $itemWhsError = ($itemWhsRow['NOITEMWHSCNT'] > 0) ? $itemWhsRow['NOITEMWHSCNT'] : 0;
    }

    $udCol = Rtv_PO_UserDefined_Columns("POOUMS", $hdrRow['H1VEND'], $hdrRow['H1TYPE']);
    $userDefHdrCnt = (!empty($udCol)) ? count($udCol) : 0;
}

$page_title = "Create Purchase Order";
$scriptName = "CreatePOItems.php";
$scriptVarBase = "{$genericVarBase}&amp;orderControl=" . urlencode(trim($orderControl));
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "HPOPEM";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$pageSelectList = 'N';
$advanceSearch = 'N';
$dftOrderBy = [["PSITEM", "A", "Item Number"]];

// $userPass = VendorUserView($profileHandle, $vendorNumber, "Y");
if ($userPass == "N") {
    require_once 'UserViewErrorInclude.php';
    exit();
}

$_SESSION[$fromURL] = $baseURL . '&startRow=' . $startRow;

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($vendorNumber) {
    $stmtSQL5 = " Select * From HDVENDV01 Where VMVEND={$vendorNumber} Fetch First Row Only";
    $sqlResult5 = db2_exec($i5Connect->getConnection(), $stmtSQL5);
    $vendorRow = db2_fetch_assoc($sqlResult5);
    $vendorName = $vendorRow['VMVNA1'];
}

require_once($docType);
print "\n
<html> <head> ";
$title = "$vendorNumber $vendorName";
require_once($headInclude);

print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
print "\n function confirmOpt(msg,text) {return confirm(msg + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> ";

require_once($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "CREATEPOITEMS";
if ($formatToPrint == "") {
    require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";

// Program Option Security
$hpopem_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$hhdiwu_OPT = pgmOptSecurity($profileHandle, $dataBaseID, "HHDIWU");

// PO Number must be auto assigned to Create a PO
if ($COASSN != 'Y') {
    $hpopem_OPT['sec_01'] = 'N';
}

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"70%\"><col width=\"25%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

$userHdrErrors = '';
if ($formatToPrint != "Y") {
    print "\n <td class=\"toolbar\">";
    if ($orderControl > 0) {
        $userHdrErrors = Check_UserDefined_Errors($orderControl,$line = 0);
        if (isset($_SESSION['returnURL'])) {
            print "<a href=\"{$_SESSION['returnURL']}\" title=\"Return to previous page\">$portalHome</a>";
        }
        if ($itemCnt > 0 && !$itemWhsError && $userHdrErrors == '') {
            $confirmPO = "return confirmOpt('Confirm create of Purchase Order','');";
            print "\n <a onClick=\"{$confirmPO}\" onClick=saveCurrentURL(); href=\"{$homeURL}{$phpPath}POHeaderMaintain.php{$scriptVarBase}&amp;tag=Edit_Data&amp;maintenanceCode=P\">$sbmSchdImage</a>";
        }
        if ($userDefHdrCnt > 0) {
            $lgU = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgU.gif\" title=\"Update User-Defined\" alt=\"U\">";
            print "\n <a href=\"{$homeURL}{$phpPath}POUserDefinedMaintain.php{$scriptVarBase}&amp;udTable=POOUMS&amp;tag=MAINTAIN\"  onclick = \"$inquiryWinVar\">$lgU</a>";
        }
        $confirmDel = "return confirmOpt('Confirm delete of all selected items','');";
        print "\n <a onClick=saveCurrentURL(); href=\"{$homeURL}{$phpPath}POHeaderMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageLrg</a><a onClick=\"{$confirmDel}\" saveCurrentURL(); href=\"{$homeURL}{$phpPath}POHeaderMaintain.php{$scriptVarBase}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageLrg</a>";
        print "<a href=\"{$homeURL}{$phpPath}POComment.php{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;cmtLine=0&amp;noMenu=Y\" onclick = \"$inquiryWinVar\" title=\"Comments\">$commentExistImageLrg </a>";
    }
    if ($vendorNumber) {
        print "<a href=\"{$homeURL}{$phpPath}VendorDashboard.php{$genericVarBase}&amp;vendorNumber={$vendorNumber}\" title=\"View Vendor Dashboard\">$dashboard</a>";
    }
    require_once 'FormatToPrint.php';
    require_once 'HelpPage.php';
    print "\n </td><td>&nbsp;</td> ";
}
print "\n </tr> ";
print "\n </table> ";

$addPO = (isset($_GET['addPO'])) ? $_GET['addPO'] : null;
if ($addPO) {
    $vendor = RetValue("POPO=$addPO", "POPOMS", "POVEND");
    $name = RetValue("VMVEND=$vendor", "HDVEND", "VMVNA1");
    $selectVar = "$genericVarBase&amp;vendorNumber=" . urlencode(trim($vendor)) . "&amp;vendorName=" . urlencode(trim($name)) . "&amp;orderSequence=000&amp;tabID=";
    $confMessage = "<a href=\"{$homeURL}{$phpPath}SelectPO.php{$selectVar}&amp;purchaseOrderNumber={$addPO}\">Purchase Order {$addPO} has been created.  Click here to view.</a>";
}

require_once 'ConfMessageDisplay.php';
if ($errorHdr) {
    print "\n <span class=\"error\"><h3>$errorHdr</h3></span>";
}
if ($userHdrErrors == 'Y') {
    print "\n <span class=\"error\"><a href=\"{$homeURL}{$phpPath}POUserDefinedMaintain.php{$scriptVarBase}&amp;udTable=POOUMS&amp;tag=MAINTAIN\" onclick=\"$inquiryWinVar\">User-Defined column errors. Click here to update.</a></span>";
}

if ($orderControl > 0) {
    print $hrTagAttr;

    print "\n <table $contentTable><colgroup><col width=\"18%\"><col width=\"18%\"><col width=\"15%\"><col width=\"15%\"><col width=\"15%\"><col width=\"15%\">";
    print "\n <tr><td class=\"hdrdata\" rowspan=\"5\">{$vendorRow['VMVNA1']} <br>";
    if (trim($vendorRow['VMVNA2']) != "") {
        print "\n {$vendorRow['VMVNA2']}<br>";
    }
    if (trim($vendorRow['VMVNA3']) != "") {
        print "\n {$vendorRow['VMVNA3']}<br>";
    }
    if (trim($vendorRow['VMVNA4']) != "") {
        print "\n {$vendorRow['VMVNA4']}<br>";
    }
    $city = trim($vendorRow['VMVCTY']);
    print "\n {$city}, {$vendorRow['VMST']} {$vendorRow['VMZIP']}";
    if (trim($vendorRow['VMCTRY']) != $HDCTCD) {
        $fieldDesc = RetValue("CNCTCD='$vendorRow[VMCTRY]'", "HDCTRY", "CNCDES");
        print "\n <br>{$fieldDesc}";
    }
    if ($vendorRow[VMPHON] > "0") {
        $phone = EditPhoneNumber($vendorRow[VMPHON]);
        print "\n <br>$phone";
    }
    print "\n </td>";
    print "\n <td class=\"dsphdr\">Vendor Number: </td><td class=\"dspnmbr\">$vendorNumber</td>";
    print "\n <td class=\"dsphdr\">Reference:</td><td class=\"dspalph\">$hdrRow[H1PORF]</td>";
    $openTotal = Format_Nbr($vendorRow[OPENTOTAL], '2', $amtEditCode, '', '$', '');
    print "\n <td class=\"dsphdr\">Total Open:</td><td class=\"dspnmbr\">$openTotal</td>";
    print "\n </tr>";

    print "\n <td class=\"dsphdr\">Warehouse Number: </td><td class=\"dspnmbr\">$hdrRow[H1WHS]</td>";
    $reqDate = DateFromCYMD($hdrRow[H1RQDT]);
    print "\n <td class=\"dsphdr\">Required Date:</td><td class=\"dspdate\">$reqDate</td>";
    $pastTotal = Format_Nbr($vendorRow[PASTTOTAL], '2', $amtEditCode, '', '$', '');
    print "\n <td class=\"dsphdr\">Past Due Total:</td><td class=\"dspnmbr\">$pastTotal</td>";
    print "\n </tr>";

    print "\n <tr>";

    $buyerName = RetValue("BMBUYR=$hdrRow[H1BUYR]", "HDBUYR", "BMBNA1");
    print "\n <td class=\"dsphdr\">Buyer: </td><td class=\"dspalph\">$buyerName</td>";
    print "\n <td class=\"dsphdr\">Item Count:</td><td class=\"dspnmbr\">$itemCnt</td>";
    $pastDuePct = Format_Nbr($vendorRow[PASTPCT], '2', $pctEditCode, '', '', '%');
    print "\n <td class=\"dsphdr\">Percent Past Due:</td><td class=\"dspnmbr\">$pastDuePct</td>";
    print "\n </tr>";
    print "\n </table> ";
}

print $hrTagAttr;

if ($formatToPrint) {
    $dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
    if ($sequence == "Number") {
        $orby = [["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "Type") {
        $orby = [["PSTYPE", "A", "Type"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "Desc") {
        $orby = [["PSIMDS", "A", "Description"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "Whs") {
        $orby = [["PSWHS", "A", "Warehouse"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "ProdClass") {
        $orby = [["IMPCLS", "A", "Product Class"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "StkUOM") {
        $orby = [["IMUOMS", "A", "Stocking UOM"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "SugQty") {
        $orby = [["PSSQOR", "A", "Suggested Order Quantity"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "OrderQty") {
        $orby = [["D1QTOR", "A", "Order Quantity"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "Cost") {
        $orby = [["D1DSCC", "A", "Cost"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "ExtCost") {
        $orby = [["D1EXTC", "A", "Extended Cost"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "EntryCode") {
        $orby = [["D1POEC", "A", "Entry Code"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "StartDate") {
        $orby = [["PSGDAT", "A", "Start Date"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "ReqDate") {
        $orby = [["PSRQDT", "A", "Required Date"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "Buyer") {
        $orby = [["PSBNA1", "A", "Buyer"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "Vendor") {
        $orby = [["PSVNA1", "A", "Vendor"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "ReqNumber") {
        $orby = [["PSREQN", "A", "Requisition Number"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "ApprovedBy") {
        $orby = [["D1AUNM", "A", "Approved By User"], ["PSITEM", "A", "Item Number"]];
    } elseif ($sequence == "ApprovedDate") {
        $orby = [["D1ADAT", "A", "Approved Date"], ["PSITEM", "A", "Item Number"]];
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD") {
    $andOr = $_POST['andOr'];
    require_once 'WildCardClear.php';
    $returnValue = Build_WildCard("PSTYPE", "Type", $_POST['srchType'], "U", $_POST['operType'], "A");
    $returnValue = Build_WildCard("max(PSITEM)", "Item Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "A");
    $returnValue = Build_WildCard("upper(IMIMDS)", "Desc", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
    $returnValue = Build_WildCard("PSWHS", "Warehouse", $_POST['srchWhs'], "", $_POST['operWhs'], "N");
    $returnValue = Build_WildCard("IMPCLS", "Product Class", $_POST['srchProdClass'], "U", $_POST['operProdClass'], "A");
    $returnValue = Build_WildCard("IMUOMS", "Stocking UOM", $_POST['srchStkUOM'], "U", $_POST['operStkUOM'], "A");
    $returnValue = Build_WildCard("PSSQOR", "Suggested Order Quantity", $_POST['srchSugQty'], "", $_POST['operSugQty'], "N");
    $returnValue = Build_WildCard("D1QTOR", "Order Quantity", $_POST['srchSugQty'], "", $_POST['operSugQty'], "N");
    $returnValue = Build_WildCard("D1DSCC", "Cost", $_POST['srchCost'], "", $_POST['operCost'], "N");
    $returnValue = Build_WildCard("D1EXTC", "Extended Cost", $_POST['srchExtCost'], "", $_POST['operExtCost'], "N");
    $returnValue = Build_WildCard("D1POEC", "Entry Code", $_POST['srchEntryCode'], "U", $_POST['operEntryCode'], "A");
    $returnValue = Build_WildCard("PSGDAT", "Start Date", $_POST['srchStartDate'], "", $_POST['operStartDate'], "D");
    $returnValue = Build_WildCard("PSRQDT", "Required Date", $_POST['srchReqDate'], "", $_POST['operReqDate'], "D");
    $returnValue = Build_WildCard("PSREQN", "Requisition Number", $_POST['srchReqNbr'], "U", $_POST['operReqNbr'], "A");
    $returnValue = Build_WildCard("D1AUNM", "Approved By User", $_POST['srchApprUser'], "", $_POST['operApprUser'], "A");
    $returnValue = Build_WildCard("D1ADAT", "Approved Date", $_POST['srchApprDate'], "", $_POST['operApprDate'], "D");
    require_once 'WildCardUpdate.php';
}

require_once($docType);
print "\n <html> \n	<head>";
require_once($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
require_once 'NumEdit.php';

print "\n function checkChg(chgForm) {
          if (validateChg(chgForm))
             chgForm.submit();}";

print "\n function validateChg(chgForm) {";
print "\n if (document.Chg.addItem.value ==\"\" || ";
print "\n     document.Chg.addQty.value ==\"\" ";
print "\n ) {alert(\"Item Number and Quantity are required\"); return false;} ";

print "\n   if (editZero(document.Chg.addQty, 9, 4) ) ";
print "\n return true;";
print "\n } ";

print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once($searchBanner);
print "\n <table $baseTable>";

$uv_ProductClassName = "IMPCLS";
$uv_VendorName = "PSVEND";
$uv_BuyerName = "PSBUYR";
$uv_WarehouseName = "PSWHS";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL = "Select POSGPOV02.*, 
            Case When D1PCPB > 0 Then dec(round(D1QTOR*D1DSCC/D1PCPB,2), 15, 2) Else dec(D1QTOR*D1DSCC, 15, 2) End as D1EXTC,
            Case When PSSQOR<>D1QTOR Then 'Y' Else 'N' End as OvrQty,
            (SELECT coalesce(sum(DBQTY),0) From PODTBW Where           
             PSJOB=DBOCTL and PSTYPE=DBTYPE and        
             PSREQN=DBREQN and PSITEM=DBITEM) as BLKQTY";
$fileSQL .= " POSGPOV02 ";
$job = ($orderControl > 0) ? $orderControl : 0;
$selectSQL = " PSPO=0 and (PSJOB=0 or PSJOB={$job}) ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
$sql_Record_Count = 99999999999;
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);

// echo $stmtSQL;

$qsOpt = "\n <option value=\"PSTYPE|null|Type|A|U\" title=\"Type\">Type";
$qsOpt .= "\n <option value=\"PSITEM|null|Item Number|A|U\" title=\"Item Number\" SELECTED>Item Number";
$qsOpt .= "\n <option value=\"IMIMDS|null|Description|A|U\" title=\"Description\">Description";
$qsOpt .= "\n <option value=\"PSWHS|null|Warehouse|N|\" title=\"Warehouse\">Whs";
$qsOpt .= "\n <option value=\"IMPCLS|null|Product Class|A|U\" title=\"Product Class\">Product Class";
$qsOpt .= "\n <option value=\"IMUOMS|null|Stocking UOM|A|U\" title=\"Stocking UOM\">Stocking UOM";
$qsOpt .= "\n <option value=\"PSSQOR|null|Suggested Order Quantity|N|\" title=\"Suggested Order Quantity\">Suggested Order Quantity";
if ($orderControl > 0) {
    $qsOpt .= "\n <option value=\"D1QTOR|null|Order Quantity|N|\" title=\"Order Quantity\">Order Quantity";
    $qsOpt .= "\n <option value=\"D1DSCC|null|Cost|N|\" title=\"Cost\">Cost";
    $qsOpt .= "\n <option value=\"(D1QTOR*D1DSCC)|null|Extended Cost|N|\" title=\"Extended Cost\">Extended Cost";
}
$qsOpt .= "\n <option value=\"D1POEC|null|Entry Code|A|U\" title=\"Entry Code\">Entry Code";
$qsOpt .= "\n <option value=\"PSGDAT|DATE|Start Date|D|\" title=\"Start Date\">Start Date";
$qsOpt .= "\n <option value=\"PSRQDT|DATE|Required Date|D|\" title=\"Required Date\">Required Date";
$qsOpt .= "\n <option value=\"upper(PSBNA1)|null|Buyer|A|U\" title=\"Buyer\">Buyer";
$qsOpt .= "\n <option value=\"upper(PSVNA1)|null|Vendor|A|U\" title=\"Vendor\">Vendor";
$qsOpt .= "\n <option value=\"upper(PSREQN)|null|Requistion Number|A|U\" title=\"Requistion Number\">Requistion Number";
$qsOpt .= "\n <option value=\"upper(D1AUNM)|null|Approved By User|A|U\" title=\"Approved By User\">Approved By User";
$qsOpt .= "\n <option value=\"D1ADAT|DATE|Approved Date|I|\" title=\"Approved Date\">Approved Date";

print "<table $contentTable> <tr>";
if ($formatToPrint != "Y") {
    print "<tr><th colspan=\"6\">";
    require 'QuickSearchOption.php';
    if ($hpopem_OPT['sec_01'] == "Y") {
        print "</th><th>&nbsp;</th>";
        print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}PODetailMaintain.php{$scriptVarBase}&amp;tag=Edit_Data&amp;maintenanceCode=I&amp;startRow=" . urlencode(trim($startRow)) . "\">";
        print "\n <td class=\"dsphdr\">&nbsp; Add Item: ";
        print "\n     <input type=\"text\" name=\"addItem\" value=\"\" size=\"12\" maxlength=\"15\">";
        print "\n     <a href=\"{$homeURL}{$phpPath}ItemSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=addItem&amp;fldDesc=itemDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
        print "\n     <span class=\"dspdesc\" id=\"itemDesc\"></span>";
        print "\n </td>";
        print "\n <td class=\"dsphdr\">Quantity: ";
        print "\n     <input type=\"text\" name=\"addQty\" value=\"\" size=\"10\" maxlength=\"15\"></td>";
    //    if ($orderControl > 0) {
            $nonStock = "<a href=\"{$homeURL}{$phpPath}PODetailMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=N\">$nonStockImage</a>";
    //    } else {
    //        $nonStock = "<a href=\"{$homeURL}{$phpPath}PODetailMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$nonStockImage</a>";
    //    }
        print "\n <td><a href=\"javascript:checkChg(document.Chg)\">$acceptImageMed</a>&nbsp;{$nonStock}&nbsp;";
        print "\n </td>";
        print "\n </form>";
        if ($orderControl > 0) {
            print "\n <td class=\"dsphdr\">&nbsp; View Selected Only<input type=\"checkbox\" onchange=\"window.location.href='{$baseURL}&amp;selItems=Y'\"></td>";
        }
    }
    print "</tr>";
    if ($errorItem) {
        print "\n <tr><td colspan=\"6\">&nbsp;</td><td class=\"error\" colspan=\"7\">$errorItem</td></tr>";
    }

}
print "</table>";
print "<table $contentTable> <tr>";
if ($formatToPrint != "Y" && $hpopem_OPT['sec_01'] == "Y") {
    print "\n <th class=\"colhdr\">$optionHeading</th>";
}

$returnValue = OrderBy_Sort("PSTYPE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Type\" title=\"Sequence By Type, Item Number\">{$sortPoint}Type</a></th>";
$returnValue = OrderBy_Sort("PSITEM");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Item Number\">{$sortPoint}Item Number</a></th>";
$returnValue = OrderBy_Sort("PSIMDS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\" title=\"Sequence By Description, Item Number\">{$sortPoint}Description</a></th>";
$returnValue = OrderBy_Sort("PSWHS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Whs\" title=\"Sequence By Warehouse, Item Number\">{$sortPoint}Whs</a></th>";
$returnValue = OrderBy_Sort("IMPCLS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ProdClass\" title=\"Sequence By Product Class, Item Number\">{$sortPoint}Product<br>Class</a></th>";
$returnValue = OrderBy_Sort("IMUOMS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StkUOM\" title=\"Sequence By Stocking UOM, Item Number\">{$sortPoint}Stk<br>UOM</a></th>";
$returnValue = OrderBy_Sort("PSSQOR");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SugQty\" title=\"Sequence By Suggested Order Quantity, Item Number\">{$sortPoint}Suggested<br>Order Quantity</a></th>";
if ($orderControl > 0) {
    $returnValue = OrderBy_Sort("D1QTOR");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OrderQty\" title=\"Sequence By Order Quantity, Item Number\">{$sortPoint}Order<br>Quantity</a></th>";
    print "\n <th class=\"colhdr\">Blanket<br>Quantity</th>";
    print "\n <th class=\"colhdr\">S</th>";
    $returnValue = OrderBy_Sort("D1DSCC");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Cost\" title=\"Sequence By Cost, Item Number\">{$sortPoint}Cost</a></th>";
    $returnValue = OrderBy_Sort("D1EXT");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ExtCost\" title=\"Sequence By Extended Cost, Item Number\">{$sortPoint}Extended<br>Cost</a></th>";
}
$returnValue = OrderBy_Sort("D1POEC");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EntryCode\" title=\"Sequence By Entry Code, Item Number\">{$sortPoint}E</a></th>";
$returnValue = OrderBy_Sort("PSGDAT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StartDate\" title=\"Sequence By Start Date, Item Number\">{$sortPoint}Start<br>Date</a></th>";
$returnValue = OrderBy_Sort("PSRQDT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ReqDate\" title=\"Sequence By Required Date, Item Number\">{$sortPoint}Required<br>Date</a></th>";
$returnValue = OrderBy_Sort("PSBNA1");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Buyer\" title=\"Sequence By Buyer, Item Number\">{$sortPoint}Buyer</a></th>";
$returnValue = OrderBy_Sort("PSVNA1");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Vendor\" title=\"Sequence By Vendor, Item Number\">{$sortPoint}Vendor</a></th>";
$returnValue = OrderBy_Sort("PSREQN");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ReqNumber\" title=\"Sequence By Requisition Number, Item Number\">{$sortPoint}Requisition<br>Number</a></th>";
$returnValue = OrderBy_Sort("D1AUNM");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ApprovedBy\" title=\"Sequence By Approved By User, Item Number\">{$sortPoint}Approved<br>By User</a></th>";
$returnValue = OrderBy_Sort("D1ADAT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ApprovedDate\" title=\"Sequence By Approved Date, Item Number\">{$sortPoint}Approved<br>Date</a></th>";
print "\n </tr>";

$saveStart = $startRow;
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    $userDtlErrors = '';
    require 'SetRowClass.php';
    $F_Desc = Format_Quote($row['IMIMDS']);
    $F_D1QTOR = Format_Nbr($row[D1QTOR], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n <tr class=\"$rowClass\">";
    if ($formatToPrint != "Y" && $hpopem_OPT['sec_01'] == "Y") {
        print "\n <td class=\"opticon\">";
        $maintainVar = "{$scriptVarBase}&amp;fromType={$row[PSTYPE]}&amp;fromReqNbr=" . trim($row[PSREQN]) . "&amp;fromItem=" . trim($row[PSITEM]) . "&amp;fromItemDesc=" . urlencode(trim($F_Desc)) . "&amp;vendorNumber=" . trim($row[PSVEND]) . "&amp;startRow=" . urlencode(trim($saveStart));
        if ($orderControl > 0 && $row[PSJOB] == $orderControl) {
            print "\n <a onClick=saveCurrentURL(); href=\"{$homeURL}{$phpPath}PODetailMaintain.php{$maintainVar}&amp;maintenanceCode=C&amp;tag=MAINTAIN\">$chgImageSml</a>";
            if ($row[D1SELC] != "S") {
                $udCol = Rtv_PO_UserDefined_Columns("POOUMD", $hdrRow['H1VEND'], $hdrRow['H1TYPE'], $row[PSPCLS]);
                $userDefDtlCnt = (!empty($udCol)) ? count($udCol) : 0;
                if ($userDefDtlCnt > 0) {
                    $userDtlErrors = Check_UserDefined_Errors($orderControl,$row[D1LINE]);
                    $smU = "<img border=\"0\" src=\"{$homeURL}{$imagePath}smU.gif\" title=\"Update User-Defined\" alt=\"U\">";
                    print "\n <a href=\"{$homeURL}{$phpPath}POUserDefinedMaintain.php{$scriptVarBase}&amp;udTable=POOUMD&amp;tag=MAINTAIN" . "&amp;fromLine=" . trim($row[D1LINE]) . "&amp;fromItem=" . trim($row[PSITEM]) . "&amp;fromItemDesc=" . urlencode(trim($F_Desc)) . "\"  onclick = \"$inquiryWinVar\">$smU</a>";
                }
            }
            $confirmDesc = 'Type: ' . trim($row[PSTYPE]) . '\n Item Number: ' . trim($row[PSITEM]) . '\n Description: ' . trim($F_Desc) . '\n Warehouse: ' . $row[PSWHS] . '\n Order Quantity: ' . $F_D1QTOR;
            $confirmDel = "return confirmOpt('Confirm delete of:','{$confirmDesc}');";
            print "\n <a onClick=\"{$confirmDel}\" saveCurrentURL(); href=\"{$homeURL}{$phpPath}PODetailMaintain.php{$maintainVar}&amp;maintenanceCode=D&amp;tag=Edit_Data\">$deleteImageSml</a>";
        } else {
            $sumImage = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgSqAcceptAll.gif\" title=\"Add Item to Purchase Order (summarized)\">";
            print "\n <a onClick=saveCurrentURL(); href=\"{$homeURL}{$phpPath}PODetailMaintain.php{$maintainVar}&amp;maintenanceCode=S&amp;tag=Edit_Data\">$sumImage</a>";
            $addItemImage = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgSqAdd.gif\" title=\"Add Item to Purchase Order\">";
            print "\n <a saveCurrentURL(); href=\"{$homeURL}{$phpPath}PODetailMaintain.php{$maintainVar}&amp;maintenanceCode=A&amp;tag=Edit_Data\">$addItemImage</a>";
        }
        print "\n </td>";
    }
    print "\n     <td class=\"colcode\">$row[PSTYPE]</td>";
    $item = RetValue("IWITEM='$row[PSITEM]' and IWWHS=$row[PSWHS]", "HDIWHS", "IWITEM");
    if (trim($item) == trim($row[PSITEM])) {
        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber=" . trim($row[PSITEM]) . "&amp;itemDescription=" . trim($F_Desc) . "\" title=\"View Item\">" . trim($row[PSITEM]) . "</a></td> ";
        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemWarehouseSelect.d2w/REPORT{$altVarBase}&amp;itemNumber=" . trim($row[PSITEM]) . "&amp;warehouseNumber={$row[PSWHS]}&amp;itemDescription=" . trim($F_Desc) . "\" title=\"View Item/Warehouse\">" . trim($F_Desc) . "</a></td> ";
    } else {
        print "\n     <td class=\"colalph\">$row[PSITEM]</td> ";
        $itemWhsError = ($row[D1POEC] == 'S' || $row[D1POEC] == 'X') ? '&nbsp; <span class="oepriceover">Item/Whs not found</span>' : '';
        print "\n     <td class=\"colalph\">" . trim($F_Desc);
        if ($orderControl > 0 && $row[PSJOB] == $orderControl) {
            if (($row[D1POEC] == 'S' || $row[D1POEC] == 'X')) {
                if ($hhdiwu_OPT['sec_01'] == 'Y') {
                    print "\n     <span class=\"oepriceover\"><a href=\"{$homeURL}{$cGIPath}ItemWarehouseMaintain.d2w/Edit_Warehouse{$altVarBase}&amp;maintenanceCode=A&amp;noMenu=Y&amp;itemNumber=" . trim($row[PSITEM]) . "&amp;warehouseNumber=" . trim($row[PSWHS]) . "\" title=\"Create Item/Warehouse\" onclick=\"$searchWinVar\">Item/Whs not found - click here to create</a></span> ";
                } else {
                    print "\n     <span class=\"oepriceover\">Item/Whs not found</span> ";
                }
            }
        }
        print "\n     </td>";
    }
    $whsDesc = RetValue("WHWHS=$row[PSWHS]", "HDWHSM", "WHWHNM");
    if ($whsDesc != '') {
        print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}WarehouseSelect.d2w/REPORT{$altVarBase}&amp;warehouseNumber={$row[PSWHS]}\" title=\"View Warehouse\">$row[PSWHS]</a></td> ";
    } else {
        print "\n     <td class=\"colnmbr\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$whsDesc}\">$row[PSWHS]</span></td>";
    }
    $pclsDesc = RetValue("PCPCLS='$row[IMPCLS]'", "HDPCLS", "PCPCDS");
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$pclsDesc}\">$row[IMPCLS]</span></td>";
    $uomDesc = RetValue("UMUOM='$row[IMUOMS]'", "HDUOM", "UMUMLD");
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$uomDesc}\">$row[IMUOMS]</span></td>";
    $F_PSSQOR = Format_Nbr($row[PSSQOR], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_PSSQOR</td>";
    if ($orderControl > 0) {
        if ($row[D1SELC] != '') {
            $class = ($row[OVRQTY] == 'Y') ? 'oepriceover' : 'colnmbr';
            $sumQty = ($row[D1SELC] == 'S') ? $checkImage : '&nbsp;';
            print "\n     <td class=\"{$class}\">$F_D1QTOR</td>";
            $F_BLKQTY = Format_Nbr($row[BLKQTY], $qtyNbrDec, $qtyEditCode, '', '', '');
            $desc = (trim($row[IMIMDS]) != '') ? trim($row[IMIMDS]) : trim($row[PSIMDS]);
            if (($row[D1SELC] == 'S')) {
                print "\n <td class=\"colcode\">&nbsp;</td>";
            } else {
                print "\n     <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}CreatePOBlanketMaintain.php{$scriptVarBase}&amp;psjob={$row[PSJOB]}&amp;pstype={$row[PSTYPE]}&amp;psreqn={$row[PSREQN]}&amp;psitem=" . urlencode(trim($row[PSITEM])) . "&amp;psimds=" . urlencode(trim($desc)) . "&amp;pswhs=" . urlencode(trim($row[PSWHS])) . "&amp;psqtor={$row[D1QTOR]}\" onclick=\"$searchWinVar\" title=\"Maintain Blanket Lines\">$F_BLKQTY</a></td>";
            }
            print "\n     <td class=\"colcode\">$sumQty</td>";
            $F_D1DSCC = Format_Nbr($row[D1DSCC], $cstNbrDec, $cstEditCode, '', '', '');
            print "\n     <td class=\"colnmbr\">$F_D1DSCC</td>";
            $F_D1EXTC = Format_Nbr($row[D1EXTC], $amtNbrDec, $amtEditCode, '', '', '');
            print "\n     <td class=\"colnmbr\">$F_D1EXTC</td>";
        } else {
            print "\n     <td colspan=\"5\">&nbsp;</td>";
        }
    }
    $ecDesc = 'Stock Item';
    if ($row[D1POEC] == 'X') {
        $ecDesc = 'Stock Item - no inventory update';
    } elseif ($row[D1POEC] == 'N') {
        $ecDesc = 'Nonstocked Item';
    }
    print "\n     <td class=\"colcode\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$ecDesc}\">$row[D1POEC]</span></td>";
    $F_PSGDAT = Format_Date($row['PSGDAT'], "D");
    print "\n     <td class=\"coldate\">$F_PSGDAT</td>";
    $F_PSRQDT = Format_Date($row['PSRQDT'], "D");
    print "\n     <td class=\"coldate\">$F_PSRQDT</td>";
    if ($row[PSBNA1] != '') {
        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}BuyerSelect.php{$genericVarBase}&amp;buyer={$row[PSBUYR]}\" title=\"View Buyer\">" . trim($row[PSBNA1]) . "</a></td>";
    } else {
        print "\n     <td class=\"colalph\">&nbsp;</td>";
    }
    if ($row[PSVNA1] != '') {
        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$row[PSVEND]}\" title=\"View Vendor\">" . trim($row[PSVNA1]) . "</a></td>";
    } else {
        print "\n     <td class=\"colalph\">&nbsp;</td>";
    }
    $reqCnt = RetValue("RQREQN='$row[PSREQN]' and RQITEM='$row[PSITEM]'", "POREQR", "count(*)");
    if ($reqCnt > 0) {
        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=502&amp;fKey1=RQREQN&fVal1={$row[PSREQN]}&tag=QSEARCH\" title=\"View Requisition Request\">$row[PSREQN]</a></td>";
    } else {
        print "\n     <td class=\"colalph\">$row[PSREQN]</td>";
    }
    print "\n     <td class=\"colalph\">$row[D1AUNM]</td>";

    $approvedDate = (!is_null($row[D1ADAT])) ? DateFromISO($row[D1ADAT]) : '';
    print "\n <td class=\"coldate\">$approvedDate</td>";

    print "\n </tr>";
    if ($userDtlErrors == 'Y') {
        print "\n <tr><td>&nbsp;</td><td colspan=\"10\"><span class=\"error\"><a href=\"{$homeURL}{$phpPath}POUserDefinedMaintain.php{$scriptVarBase}&amp;udTable=POOUMD&amp;tag=MAINTAIN" . "&amp;fromLine=" . trim($row[D1LINE]) . "&amp;fromItem=" . trim($row[PSITEM]) . "&amp;fromItemDesc=" . urlencode(trim($F_Desc)) . "\" onclick=\"$inquiryWinVar\">User-Defined column errors. Click here to update.</a></span></td></tr>";
    }
    $startRow++;
    $rowCount++;
}
if ($rowCount == 0) {
    require 'NoRecordsFound.php';
} else {
    if ($orderControl > 0) {
        require 'stmtSQLClear.php';
        $stmtSQL = "Select sum(PSSQOR) as T_SQOR, sum(D1QTOR) as T_QTOR, ";
        $stmtSQL .= " sum(Case When D1PCPB > 0 Then dec(round(D1QTOR*D1DSCC/D1PCPB,2), 15, 2) Else dec(D1QTOR*D1DSCC, 15, 2) End) as T_EXTC";
        $fileSQL .= " POSGPOV02 ";
        $selectSQL = " PSPO=0 and (PSJOB=0 or PSJOB={$job}) ";
        require 'stmtSQLSelect.php';
        require 'stmtSQLEnd.php';

        $sqlResult6 = db2_exec($i5Connect->getConnection(), $stmtSQL);
        $row = db2_fetch_assoc($sqlResult6);
        $F_T_SQOR = Format_Nbr($row[T_SQOR], $qtyNbrDec, $qtyEditCode, '', '', '');
        $F_T_QTOR = Format_Nbr($row[T_QTOR], $qtyNbrDec, $qtyEditCode, '', '', '');
        $F_T_EXTC = Format_Nbr($row[T_EXTC], $amtNbrDec, $amtEditCode, '', '', '');
        $cols = ($formatToPrint != "Y") ? '7' : '6';
        print "\n <tr>";
        print "\n     <td colspan=\"{$cols}\">&nbsp;</td>";
        print "\n     <td class=\"coltotal\">$F_T_SQOR</td>";
        print "\n     <td class=\"coltotal\">$F_T_QTOR</td>";
        print "\n     <td colspan=\"3\">&nbsp;</td>";
        print "\n     <td class=\"coltotal\">$F_T_EXTC</td>";
        print "\n </tr>";
    }
}
print "</table>";
print $hrTagAttr;
require_once 'Copyright.php';

?>
