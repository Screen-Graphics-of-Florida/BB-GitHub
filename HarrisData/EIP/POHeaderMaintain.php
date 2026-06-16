<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound = $_GET['errFound'];
$wrnVar = $_GET['wrnVar'];
$noMenu = (isset($_GET ['noMenu'])) ? $_GET ['noMenu'] : '';

$vendorNumber = $_GET['vendorNumber'];
$purchaseOrder = (isset($_GET['purchaseOrderNumber'])) ? $_GET['purchaseOrderNumber'] : 0;
$orderControl = (isset($_GET['orderControl'])) ? $_GET['orderControl'] : 0;
$fromType = (isset($_SESSION ['po_type'])) ? $_SESSION ['po_type'] : '';
$fromReqNbr = (isset($_SESSION ['po_reqNbr'])) ? $_SESSION ['po_reqNbr'] : '';
$fromItem = (isset($_SESSION ['po_item'])) ? $_SESSION ['po_item'] : '';
$reqDate = (isset($_SESSION ['po_dueDate'])) ? $_SESSION ['po_dueDate'] : 0;
$buyer = (isset($_SESSION ['po_buyer'])) ? $_SESSION ['po_buyer'] : 0;
$poBusy = (isset($_GET['poBusy'])) ? $_GET['poBusy'] : null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';

$programName = "HPOPEM";
$hpopem_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $hpopem_OPT['sec_01'];
$sec_02 = $hpopem_OPT['sec_02'];
$sec_03 = $hpopem_OPT['sec_04'];
if ($purchaseOrder > 0) {
    $sec_03 = 'N';
}
if (!is_null($poBusy)) {
    $sec_01 = 'N';
    $sec_02 = 'N';
    $sec_03 = 'N';
}

$backURL = $_SESSION[$fromURL];
if ($backURL == "") {
    $backURL = "{$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}";
}

$page_title = "PO Header Maintenance";
$scriptName = "POHeaderMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrder)) . "&amp;noMenu=" . urlencode(trim($noMenu));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D&amp;fromType={$fromType}&amp;fromReqNbr={$fromReqNbr}&amp;fromItem={$fromItem}&amp;startRow=" . urlencode(trim($startRow));

if ($tag == "MAINTAIN") {
    $stmtSQL = "";
    if ($purchaseOrder > 0) {
        $stmtSQL .= " Select 0 as H1OCTL,POPO as H1PO,POTYPE as H1TYPE,POVEND as H1VEND,PORTOV as H1RTOV,
                      PODSHP as H1DSHP,POWHS as H1WHS,POBUYR as H1BUYR,POPORF as H1PORF,PORQDT as H1RQDT,
                      PODT2 as H1DT2,PODT3 as H1DT3,PODT4 as H1DT4,PODSC1 as H1DSC1,PODSC2 as H1DSC2,
                      PODSC3 as H1DSC3,POPTRM as H1PTRM,POSVSV as H1SVSV,POSVDS as H1SVDS,POFOBC as H1FOBC,
                      POFOB as H1FOB,POBOAL as H1BOAL,POCONF as H1CONF,POCHNN as H1CHNN,POUDF1 as H1UDF1,
                      POUDF2 as H1UDF2,POUDF3 as H1UDF3,POUDF4 as H1UDF4,POUDF5 as H1UDF5,POUSER as H1USER ";
        $stmtSQL .= " From POPOMS ";
        $stmtSQL .= " Where POPO=$purchaseOrder ";
    } else {
        $stmtSQL .= " Select * ";
        $stmtSQL .= " From POHDRW ";
        $stmtSQL .= " Where H1OCTL=$orderControl ";
    }
    require 'stmtSQLEnd.php';

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    $stmtSQL = " Select * From HDOTYP Where OTAPID='PO' and OTOTCD='{$row['H1TYPE']}' Fetch First Row Only";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $poTypeRow = db2_fetch_assoc($sqlResult);

    require_once($docType);
    print "\n <html> <head>";
    require_once($headInclude);
    $formName = "Chg";
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'Menu.js';

    require_once 'CalendarInclude.php';
    require_once 'CheckEnterChg.php';
    require_once 'DateEdit.php';
    require_once 'NumEdit.php';
    require_once 'SaveCurrentURL.php';
    require_once 'UpperCase.php';

    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.vendor.value ==\"\" || ";
    print "\n     document.Chg.orderType.value ==\"\" ||";
    print "\n     document.Chg.warehouse.value ==\"\" ||";
    print "\n     document.Chg.reqDate.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";

    print "\n if (editZero(document.Chg.vendor, 7, 0) && ";
    print "\n     editNum(document.Chg.warehouse, 3, 0) && ";
    print "\n     editNum(document.Chg.buyer, 7, 0) && ";
    print "\n     editNum(document.Chg.dsc1, 5, 2) && ";
    print "\n     editNum(document.Chg.dsc2, 5, 2) && ";
    print "\n     editNum(document.Chg.dsc3, 5, 2) && ";
    if (trim($poTypeRow[OTSDT1]) != '') {
        print "\n editdate(document.Chg.uh2Date) && ";
    }
    if (trim($poTypeRow[OTSDT2]) != '') {
        print "\n editdate(document.Chg.uh3Date) && ";
    }
    if (trim($poTypeRow[OTSDT3]) != '') {
        print "\n editdate(document.Chg.uh4Date) && ";
    }
    print "\n     editdate(document.Chg.reqDate) ";
    print "\n ) return true; ";
    print "\n } ";

    print "\n  function confirmDelete() {return confirm(\"$delRecordConf\")} ";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";

    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    if ($noMenu != "Y") {
        require_once 'Banner.php';
    }
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    if ($noMenu != "Y") {
        $pageID = "POHEADERMAINTAIN";
        require_once 'MenuDisplay.php';
    }
    print "\n <td class=\"content\">";
    print "\n <table $contentTable>";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td>";
    print "\n     <td class=\"toolbar\">";
    if (($sec_01 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "Z")) || ($sec_02 != "N" && $maintenanceCode == "C") || ($maintenanceCode != "A" && $maintenanceCode != "C" && $maintenanceCode != "D" && $maintenanceCode != "Z")) {
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
    }
    if ($noMenu == "Y") {
        print "\n <a href=\"javascript:opener.location.href=opener.location.href; javascript:window.close()\">$closeImageMed</a> ";
    } elseif ($backURL != "") {
        print "\n <a href=\"$backURL\">$cancelImageMed</a>";
    } else {
        print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";
    }

    if ($sec_03 != "N" && $maintenanceCode == "C") {
        print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";
    }

    $medIcon = "Y";
    require 'HelpPage.php';
    print "\n </td></tr></table>";

    if ($purchaseOrder > 0) {
        print "\n <table $contentTable>";
        Format_Header_URL("Order", $purchaseOrder, "", "");
        print "\n </table> ";
    }
    if ($_SESSION ['po_reqNbr'] != '') {
        print "\n <table $contentTable>";
        $reqDesc = RetValue("RQREQN='{$_SESSION ['po_reqNbr']}'", "POREQR", "RQDESC");
        Format_Header_URL("Requisition", $reqDesc, $_SESSION ['po_reqNbr'], "");
        print "\n </table> ";
    }

    print $hrTagAttr;
    require_once 'RequiredField.php';
    if (!is_null($poBusy)) {
        print "\n <span class=\"error\" $textOvr> &nbsp; &nbsp; Cannot update - Order is being maintained by another user</span>";
    } else {
        require_once 'ErrorDisplay.php';
    }

    if ($errFound != "" || $maintenanceCode == "A") {
        if ($errFound == "" && $maintenanceCode == "A") {
            $edtVar = "";
        } elseif ($errFound != "") {
            $focusField = "";
            $edtVar = EdtVarErr($profileHandle, $edtVar);
            $errVar = ErrVarErr($profileHandle, $errVar);
            $Err_POBUSY = DecatErr_Field("@@popo", "popo");
            $Err_H1VEND = DecatErr_Field("@@vend", "vendor");
            $Err_H1RTOV = DecatErr_Field("@@rtov", "remitTo");
            $Err_H1DSHP = DecatErr_Field("@@dshp", "dropShip");
            $Err_H1TYPE = DecatErr_Field("@@type", "orderType");
            $Err_H1WHS = DecatErr_Field("@@whs@", "warehouse");
            $Err_H1BUYR = DecatErr_Field("@@buyr", "buyer");
            $Err_H1RQDT = DecatErr_Field("@@rqdt", "reqDate");
            $Err_H1DT2 = DecatErr_Field("@@udt2", "uh2Date");
            $Err_H1DT3 = DecatErr_Field("@@udt3", "uh3Date");
            $Err_H1DT4 = DecatErr_Field("@@udt4", "uh4Date");
            $Err_H1PTRM = DecatErr_Field("@@ptrm", "termsCode");
            $Err_H1SVSV = DecatErr_Field("@@svsv", "shipVia");
            $Err_H1SVDS = DecatErr_Field("@@svds", "shipViaDesc");
            $Err_H1FOBC = DecatErr_Field("@@fobc", "fobCode");
            $Err_H1FOB = DecatErr_Field("@@fobd", "fobCodeDesc");
            $Err_H1BOAL = DecatErr_Field("@@boal", "allowBO");
            $Err_H1CONF = DecatErr_Field("@@conf", "confFlag");
            $Err_H1CHNN = DecatErr_Field("@@chnn", "chgNotice");
            $Err_H1DSC1 = DecatErr_Field("@@dsc1", "disc1");
            $Err_H1DSC2 = DecatErr_Field("@@dsc2", "disc2");
            $Err_H1DSC3 = DecatErr_Field("@@dsc3", "disc3");
            $Err_H1UDF1 = DecatErr_Field("@@udf1", "udf1");
            $Err_H1UDF2 = DecatErr_Field("@@udf2", "udf2");
            $Err_H1UDF3 = DecatErr_Field("@@udf3", "udf3");
            $Err_H1UDF4 = DecatErr_Field("@@udf3", "udf3");
            $Err_H1UDF5 = DecatErr_Field("@@udf3", "udf3");
        }
        $row['H1VEND'] = Decat_Field("@@vend", $edtVar);
        $row['H1RTOV'] = Decat_Field("@@rtov", $edtVar);
        $row['H1DSHP'] = Decat_Field("@@dshp", $edtVar);
        $row['H1TYPE'] = Decat_Field("@@type", $edtVar);
        $row['H1WHS'] = Decat_Field("@@whs@", $edtVar);
        $row['H1BUYR'] = Decat_Field("@@buyr", $edtVar);
        $row['H1RQDT'] = Decat_Field("@@rqdt", $edtVar);
        $row['H1PORF'] = Decat_Field("@@porf", $edtVar);
        $row['H1DT2'] = Decat_Field("@@udt2", $edtVar);
        $row['H1DT3'] = Decat_Field("@@udt3", $edtVar);
        $row['H1DT4'] = Decat_Field("@@udt4", $edtVar);
        $row['H1PTRM'] = Decat_Field("@@ptrm", $edtVar);
        $row['H1SVSV'] = Decat_Field("@@svsv", $edtVar);
        $row['H1SVDS'] = Decat_Field("@@svds", $edtVar);
        $row['H1FOBC'] = Decat_Field("@@fobc", $edtVar);
        $row['H1FOB'] = Decat_Field("@@fobd", $edtVar);
        $row['H1BOAL'] = Decat_Field("@@boal", $edtVar);
        $row['H1CONF'] = Decat_Field("@@conf", $edtVar);
        $row['H1CHNN'] = Decat_Field("@@chnn", $edtVar);
        $row['H1DSC1'] = Decat_Field("@@dsc1", $edtVar);
        $row['H1DSC2'] = Decat_Field("@@dsc2", $edtVar);
        $row['H1DSC3'] = Decat_Field("@@dsc3", $edtVar);
        $row['H1UDF1'] = Decat_Field("@@udf1", $edtVar);
        $row['H1UDF2'] = Decat_Field("@@udf2", $edtVar);
        $row['H1UDF3'] = Decat_Field("@@udf3", $edtVar);
        $row['H1UDF4'] = Decat_Field("@@udf4", $edtVar);
        $row['H1UDF5'] = Decat_Field("@@udf5", $edtVar);
        if ($errFound == "" && $maintenanceCode == "A") {
            if (trim($fromType != '')) {
                $stmtSQL = " Select * From POSGPO Where PSTYPE='{$fromType}' and PSREQN='{$fromReqNbr}' and PSITEM='{$fromItem}' Fetch First Row Only";
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
                $sugPORow = db2_fetch_assoc($sqlResult);
                if (!empty($sugPORow)) {
                    if ($sugPORow['PSVEND'] > 0) {
                        $stmtSQL = " Select * From HDVEND Where VMVEND={$sugPORow['PSVEND']} Fetch First Row Only";
                        $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
                        $vendorRow = db2_fetch_assoc($sqlResult);
                        $row['H1RTOV'] = $vendorRow['VMRTVD'];
                        $row['H1PTRM'] = $vendorRow['VMPTRM'];
                        $row['H1SVSV'] = $vendorRow['VMSVSV'];
                        $row['H1FOBC'] = $vendorRow['VMFOBC'];
                    }
                    $row['H1VEND'] = $sugPORow['PSVEND'];
                    $row['H1BUYR'] = $sugPORow['PSBUYR'];
                    $row['H1WHS'] = $sugPORow['PSWHS'];
                    $row['H1RQDT'] = ($sugPORow['PSRQDT'] > 0) ? DateInputFromCYMD($sugPORow['PSRQDT']) : 0;
                } else {
                    $row['H1VEND'] = ($vendorNumber > 0) ? $vendorNumber : $row['H1VEND'];
                    $row['H1BUYR'] = ($buyer > 0) ? $buyer : $row['H1BUYR'];
                    $row['H1RQDT'] = ($reqDate > 0) ? DateInputFromISO($reqDate) : $row['H1RQDT'];
                }
            }
            if ($row['H1RQDT'] == 0) {
                $row['H1RQDT'] = DateInputFromCYMD(DateTodayCYMD());
            }
            $row['H1TYPE'] = 'P';
            $row['H1BOAL'] = 'Y';

            $stmtSQL = " Select * From POREQR Where RQREQN='{$fromReqNbr}' and RQITEM='{$fromItem}'";
            $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
            $reqRow = db2_fetch_assoc($sqlResult);
            $row['H1SVSV'] = ($reqRow['RQSVSV'] != '') ? $reqRow['RQSVSV'] : $row['H1SVSV'];
            $row['H1VEND'] = ($reqRow['RQVEND'] > 0) ? $reqRow['RQVEND'] : $row['H1VEND'];
        }
    } else {
        $row['H1RQDT'] = DateInputFromCYMD($row['H1RQDT']);
        $row['H1DT2'] = DateInputFromCYMD($row['H1DT2']);
        $row['H1DT3'] = DateInputFromCYMD($row['H1DT3']);
        $row['H1DT4'] = DateInputFromCYMD($row['H1DT4']);
        $row['H1DSC1'] = bcmul(floatval($row ['H1DSC1']), 100, 2);
        $row['H1DSC2'] = bcmul(floatval($row ['H1DSC2']), 100, 2);
        $row['H1DSC3'] = bcmul(floatval($row ['H1DSC3']), 100, 2);
        $focusField = "vendor";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$scriptVarBase}&amp;tag=Edit_Data&amp;maintenanceCode={$maintenanceCode}\">";
    print "\n <table $contentTable>";
    DspErrMsg($Err_POBUSY);

    $vendName = RetValue("VMVEND=$row[H1VEND]", "HDVEND", "VMVNA1");
    $textOvr = SetTextOvr($Err_H1VEND);
    if ($maintenanceCode == "A") {
        print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Vendor</span></td> ";
        print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"vendor\" value=\"" . trim($row['H1VEND']) . "\" size=\"6\" maxlength=\"7\"> ";
        print "\n                              <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=vendor&amp;fldDesc=vendorName\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
        print "\n                              <span class=\"dspdesc\" id=\"vendorName\">$vendName</span></td>";
    } else {
        $vendDesc = $vendName . '&nbsp;  [' . $row['H1VEND'] . ']';
        Build_DspFld("Vendor", $vendDesc, "");
        print "\n      <td class=\"inputalph\"><input type=\"hidden\" name=\"vendor\" value=\"" . trim($row['H1VEND']) . "\"></td>";
    }
    print "\n  </tr> ";
    DspErrMsg($Err_H1VEND);

    $fieldDesc = RetValue("OTAPID='PO' and OTOTCD='$row[H1TYPE]'", "HDOTYP", "OTDESC");
    $textOvr = SetTextOvr($Err_H1TYPE);
    if ($purchaseOrder == 0) {
        print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Order Type</span></td> ";
        print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"orderType\" value=\"" . rtrim($row['H1TYPE']) . "\" size=\"6\" maxlength=\"1\"> ";
        print "\n                                     <a href=\"{$homeURL}{$cGIPath}OrderTypeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=orderType&amp;fldDesc=orderTypeDesc&amp;appID=PO\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
        print "\n                                     <span class=\"dspdesc\" id=\"orderTypeDesc\">$fieldDesc</span></td>";
    } else {
        $typeDesc = $fieldDesc . '&nbsp;  [' . $row['H1TYPE'] . ']';
        Build_DspFld("Order Type", $typeDesc, "");
        print "\n      <td class=\"inputalph\"><input type=\"hidden\" name=\"orderType\" value=\"" . trim($row['H1TYPE']) . "\"></td>";
    }
    print "\n         </tr> ";
    DspErrMsg($Err_H1TYPE);

    $fieldDesc = RetValue("WHWHS=$row[H1WHS]", "HDWHSM", "WHWHNM");
    $textOvr = SetTextOvr($Err_H1WHS);
    if ($purchaseOrder == 0) {
        print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Warehouse</span></td> ";
        print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"warehouse\" value=\"" . trim($row['H1WHS']) . "\" size=\"6\" maxlength=\"3\"> ";
        print "\n                              <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=warehouse&amp;fldDesc=warehouseName\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
        print "\n                              <span class=\"dspdesc\" id=\"warehouseName\">$fieldDesc</span></td>";
    } else {
        $whsDesc = $fieldDesc . '&nbsp;  [' . $row['H1WHS'] . ']';
        Build_DspFld("Warehouse", $whsDesc, "");
        print "\n      <td class=\"inputalph\"><input type=\"hidden\" name=\"warehouse\" value=\"" . trim($row['H1WHS']) . "\"></td>";
    }
    print "\n  </tr> ";
    DspErrMsg($Err_H1WHS);

    $fieldDesc = RetValue("DSVCF='V' and DSNMBR=$row[H1DSHP]", "HDDSHP", "DSNAME");
    $textOvr = SetTextOvr($Err_H1DSHP);
    print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Drop Ship</span></td> ";
    print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"dropShip\" value=\"" . rtrim($row['H1DSHP']) . "\" size=\"6\" maxlength=\"7\"> ";
    print "\n                                     <a href=\"{$homeURL}{$cGIPath}OrderEntryDropShip.d2w/REPORT{$altVarBase}&amp;docName=Chg&amp;fldName=dropShip&amp;fldDesc=dropShipName&amp;vendCustFlag=V&amp;vendCustNumber={$row['H1VEND']}&amp;vendCustName={$vendName}&amp;orderControlNumber={$orderControl}\" onclick=\"$searchWinVar\">$searchImage</a> ";
    print "\n                                     <span class=\"dspdesc\" id=\"dropShipName\">$fieldDesc</span></td>";
    print "\n         </tr> ";
    DspErrMsg($Err_H1DSHP);

    $remitToName = RetValue("VMVEND=$row[H1RTOV]", "HDVEND", "VMVNA1");
    $textOvr = SetTextOvr($Err_H1RTOV);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Remit-To Vendor</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"remitTo\" value=\"" . trim($row['H1RTOV']) . "\" size=\"6\" maxlength=\"7\"> ";
    print "\n                              <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=remitTo&amp;fldDesc=remitToName\" onclick=\"$searchWinVar\">$searchImage</a> ";
    print "\n                              <span class=\"dspdesc\" id=\"remitToName\">$remitToName</span></td>";
    print "\n  </tr> ";
    DspErrMsg($Err_H1RTOV);

    $fieldDesc = RetValue("BMBUYR=$row[H1BUYR]", "HDBUYR", "BMBNA1");
    $textOvr = SetTextOvr($Err_H1BUYR);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Buyer</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"buyer\" value=\"" . trim($row['H1BUYR']) . "\" size=\"6\" maxlength=\"7\"> ";
    print "\n                              <a href=\"{$homeURL}{$phpPath}BuyerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=buyer&amp;fldDesc=buyerName\" onclick=\"$searchWinVar\"> $searchImage</a>";
    print "\n                              <span class=\"dspdesc\" id=\"buyerName\">$fieldDesc</span></td>";
    print "\n  </tr> ";
    DspErrMsg($Err_H1BUYR);

    Build_Fld_Entry("Reference", "reference", "inputalph", "", "H1PORF", $row[H1PORF], $Err_H1PORF, "20", "15", "", "", "");
    Build_Fld_Entry("Required Date", "reqDate", "inputdate", "Date", "H1RQDT", $row[H1RQDT], $Err_H1RQDT, "6", "6", "Y", "", "");
    Build_Fld_Entry("Allow Backorders", "allowBO", "inputalph", "YORN", "H1BOAL", $row[H1BOAL], $Err_H1BOAL, "1", "1", "Y", "", "");

    if ($row[H1FOBC] == '99') {
        $fieldDesc = $row[H1FOB];
    } else {
        $fieldDesc = RetValue("FBFBCD='$row[H1FOBC]'", "HDFOBM", "FBFBDS");
    }
    $textOvr = SetTextOvr($Err_H1FOBC);
    print "\n         <tr><td class=\"dsphdr\"><span $textOvr>FOB</span></td> ";
    print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"fobCode\" value=\"" . rtrim($row['H1FOBC']) . "\" size=\"6\" maxlength=\"2\"> ";
    print "\n                                     <a href=\"{$homeURL}{$cGIPath}FOBCodeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=fobCode&amp;fldDesc=fobCodeDesc\" onclick=\"$searchWinVar\"> $searchImage </a>";
    print "\n                                     <span class=\"dspdesc\" id=\"fobCodeDesc\">$fieldDesc</span></td> ";
    print "\n         </tr> ";
    DspErrMsg($Err_H1FOBC);
    DspErrMsg($Err_H1FOB);

    if ($row[H1SVSV] == '99') {
        $fieldDesc = $row[H1SVDS];
    } else {
        $fieldDesc = RetValue("SVSVSV='$row[H1SVSV]'", "HDSHPV", "SVSVDS");
    }
    $textOvr = SetTextOvr($Err_H1SVSV);
    print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Ship Via</span></td> ";
    print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"shipVia\" value=\"" . rtrim($row['H1SVSV']) . "\" size=\"6\" maxlength=\"2\"> ";
    print "\n                                     <a href=\"{$homeURL}{$phpPath}ShipViaSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=shipVia&amp;fldDesc=shipViaDesc\" onclick=\"$searchWinVar\"> $searchImage </a>";
    print "\n                                     <span class=\"dspdesc\" id=\"shipViaDesc\">$fieldDesc</span></td> ";
    print "\n         </tr> ";
    DspErrMsg($Err_H1SVSV);
    DspErrMsg($Err_H1SVDS);

    $fieldDesc = RetValue("VTRMS='$row[H1PTRM]'", "APVTRM", "VTVTDS");
    $textOvr = SetTextOvr($Err_H1PTRM);
    print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Terms</span></td> ";
    print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"termsCode\" value=\"" . rtrim($row['H1PTRM']) . "\" size=\"6\" maxlength=\"2\"> ";
    print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorTermsCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=termsCode&amp;fldDesc=termsCodeDesc\" onclick=\"$searchWinVar\"> $searchImage</a>";
    print "\n                                     <span class=\"dspdesc\" id=\"termsCodeDesc\">$fieldDesc</span></td> ";
    print "\n         </tr> ";
    DspErrMsg($Err_H1PTRM);

    Build_Fld_Entry("Confirmation Flag", "confFlag", "inputalph", "YORN", "H1CONF", $row[H1CONF], $Err_H1CONF, "1", "1", "Y", "", "");
    Build_Fld_Entry("Change Notice", "chgNotice", "inputnmbr", "", "H1CHNN", $row[H1CHNN], $Err_H1CHNN, "2", "2", "", "", "");

    $textOvr = SetTextOvr($Err_H1DSC1);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Trade Discount</span></td> ";
    print "\n      <td class=\"inputalph\"> ";
    print "\n      <input type=\"text\" name=\"dsc1\" value=\"" . trim($row['H1DSC1']) . "\" size=\"6\" maxlength=\"6\"> ";
    print "\n      <input type=\"text\" name=\"dsc2\" value=\"" . trim($row['H1DSC2']) . "\" size=\"6\" maxlength=\"6\"> ";
    print "\n      <input type=\"text\" name=\"dsc3\" value=\"" . trim($row['H1DSC3']) . "\" size=\"6\" maxlength=\"6\"> ";
    print "\n  </td></tr> ";
    DspErrMsg($Err_H1DSC1);

    if (trim($poTypeRow[OTSDT1]) != '') {
        Build_Fld_Entry($poTypeRow[OTSDT1], "uh2Date", "inputdate", "Date", "H1DT2", $row[H1DT2], $Err_H1DT2, "6", "6", "", "", "");
    }
    if (trim($poTypeRow[OTSDT2]) != '') {
        Build_Fld_Entry($poTypeRow[OTSDT2], "uh3Date", "inputdate", "Date", "H1DT3", $row[H1DT3], $Err_H1DT3, "6", "6", "", "", "");
    }
    if (trim($poTypeRow[OTSDT3]) != '') {
        Build_Fld_Entry($poTypeRow[OTSDT3], "uh4Date", "inputdate", "Date", "H1DT4", $row[H1DT4], $Err_H1DT4, "6", "6", "", "", "");
    }
    if (trim($poTypeRow[OTSCR1]) != '') {
        Build_Fld_Entry($poTypeRow[OTSCR1], "udf1", "inputalph", "", "H1UDF1", $row[H1UDF1], $Err_H1UDF1, "15", "15", "", "", "");
    }
    if (trim($poTypeRow[OTSCR2]) != '') {
        Build_Fld_Entry($poTypeRow[OTSCR2], "udf2", "inputalph", "", "H1UDF2", $row[H1UDF2], $Err_H1UDF2, "15", "15", "", "", "");
    }
    if (trim($poTypeRow[OTSCR3]) != '') {
        Build_Fld_Entry($poTypeRow[OTSCR3], "udf3", "inputalph", "", "H1UDF3", $row[H1UDF3], $Err_H1UDF3, "15", "15", "", "", "");
    }
    if (trim($poTypeRow[OTSCR4]) != '') {
        Build_Fld_Entry($poTypeRow[OTSCR4], "udf4", "inputalph", "", "H1UDF4", $row[H1UDF4], $Err_H1UDF4, "15", "15", "", "", "");
    }
    if (trim($poTypeRow[OTSCR5]) != '') {
        Build_Fld_Entry($poTypeRow[OTSCR5], "udf5", "inputalph", "", "H1UDF5", $row[H1UDF5], $Err_H1UDF5, "15", "15", "", "", "");
    }

    print "\n </table> ";

    print "\n <script TYPE=\"text/javascript\">";
    print "\n document.Chg.$focusField.focus();";
    print "\n </script>";
    print "\n </form>";
    print "\n <table $contentTable>";
    print "\n     <td class=\"toolbar\">";
    if (($sec_01 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "Z")) || ($sec_02 != "N" && $maintenanceCode == "C") || ($maintenanceCode != "A" && $maintenanceCode != "C" && $maintenanceCode != "D" && $maintenanceCode != "Z")) {
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed </a>";
    }
    if ($noMenu == "Y") {
        print "\n <a href=\"javascript:opener.location.href=opener.location.href; javascript:window.close()\">$closeImageMed </a> ";
    } elseif ($backURL != "") {
        print "\n <a href=\"$backURL\">$cancelImageMed </a>";
    } else {
        print "\n <a href=\"javascript:history.back()\">$cancelImageMed </a>";
    }

    if ($sec_03 != "N" && $maintenanceCode == "C") {
        print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";
    }

    $medIcon = "Y";
    require 'HelpPage.php';
    print "\n </td></tr></table>";
    print $hrTagAttr;
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    if ($noMenu != "Y") {
        require_once 'Trailer.php';
    }
    print "</body> </html>";
    exit();
}

if ($tag == "Edit_Data") {

    $edtVar = "";
    Concat_Field("@@octl", $orderControl);
    Concat_Field("@@popo", $purchaseOrder);
    Concat_Field("@@vend", $_POST['vendor']);
    Concat_Field("@@rtov", $_POST['remitTo']);
    Concat_Field("@@dshp", $_POST['dropShip']);
    Concat_Field("@@type", strtoupper($_POST['orderType']));
    Concat_Field("@@whs@", $_POST['warehouse']);
    Concat_Field("@@buyr", $_POST['buyer']);
    Concat_Field("@@rqdt", $_POST['reqDate']);
    if (!isset($_POST['uh2Date'])) {
        $_POST['uh2Date'] = "";
    }
    Concat_Field("@@udt2", $_POST['uh2Date']);
    if (!isset($_POST['uh3Date'])) {
        $_POST['uh3Date'] = "";
    }
    Concat_Field("@@udt3", $_POST['uh3Date']);
    if (!isset($_POST['uh4Date'])) {
        $_POST['uh4Date'] = "";
    }
    Concat_Field("@@udt4", $_POST['uh4Date']);
    Concat_Field("@@porf", strtoupper($_POST['reference']));
    Concat_Field("@@ptrm", strtoupper($_POST['termsCode']));
    Concat_Field("@@svsv", strtoupper($_POST['shipVia']));
    Concat_Field("@@svds", strtoupper($_POST['shipViaDesc']));
    Concat_Field("@@fobc", strtoupper($_POST['fobCode']));
    Concat_Field("@@fobd", strtoupper($_POST['fobCodeDesc']));
    Concat_Field("@@chnn", $_POST['chgNotice']);
    Concat_Field("@@udf1", $_POST['udf1']);
    Concat_Field("@@udf2", $_POST['udf2']);
    Concat_Field("@@udf3", $_POST['udf3']);
    Concat_Field("@@udf4", $_POST['udf4']);
    Concat_Field("@@udf5", $_POST['udf5']);
    Concat_Field("@@dsc1", $_POST['dsc1']);
    Concat_Field("@@dsc2", $_POST['dsc2']);
    Concat_Field("@@dsc3", $_POST['dsc3']);
    if (!isset($_POST['allowBO'])) {
        $_POST['allowBO'] = "N";
    }
    Concat_Field("@@boal", $_POST['allowBO']);
    if (!isset($_POST['confFlag'])) {
        $_POST['confFlag'] = "N";
    }
    Concat_Field("@@conf", $_POST['confFlag']);
    Concat_Field("@@podc", $poDefaultOrderComments);
    Concat_Field("@@podr", $poAddDrawingNumberToComments);
    $edtVar .= "}{";


    $poBusy = null;
    if ($purchaseOrder > 0) {
        $busy = RetValue("POPO=$purchaseOrder", "POPOMS", "POBUSY");
        if ($busy == 'B') {
            $poBusy = 'Y';
            $errFound = 'Y';
        }
    }

    if ($errFound == "") {
        // echo $edtVar;
        // exit();
        $returnValue = Maintain_Edit("HPOHDR_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
        $maintenanceCode = $returnValue['maintenanceCode'];
        $errFound = $returnValue['errFound'];
        $edtVar = $returnValue['edtVar'];
        $errVar = $returnValue['errVar'];
    }

    if ($errFound == "") {
        $orderControl = Decat_Field("@@octl", $edtVar);
        if ($purchaseOrder > 0) {
            print "\n <script TYPE=\"text/javascript\">";
            print "\n     opener.location.href=opener.location.href;";
            print "\n     window.close();";
            print "\n </script>";

        } elseif ($maintenanceCode == 'D' && $fromItem == '') {
            if (isset($_SESSION["returnURL"])) {
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$_SESSION["returnURL"]}\">";
            } else {
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}&amp;tag=QSEARCH&amp;startRow=1\"> ";
            }
        } else {
            if ($_SESSION ['po_setHdr'] == 'Y') {
                if ($_SESSION ['po_mncd'] == 'N') {
                    print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}PODetailMaintain.php{$genericVarBase}&amp;tag=MAINTAIN&maintenanceCode=N&errFound=&amp;orderControl=" . urlencode(trim($orderControl)) . "\"> ";
                } else {
                    print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}PODetailMaintain.php{$genericVarBase}&amp;tag=Edit_Data&amp;orderControl=" . urlencode(trim($orderControl)) . "\"> ";
                }
            } elseif ($maintenanceCode == 'P') {
                $addPO = Decat_Field("@@adpo", $edtVar);
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}&amp;tag=QSEARCH&amp;startRow=1&addPO={$addPO}\"> ";
            } elseif ($orderControl == '') {
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;startRow=" . urlencode(trim($startRow)) . "\"> ";
            } else {
                $tag = ($maintenanceCode == 'D') ? '&amp;tag=QSEARCH' : '';
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}&amp;orderControl=" . urlencode(trim($orderControl)) . $tag . "&amp;startRow=1\"> ";
            }
        }
    } else {
        EdtVarErr($profileHandle, $edtVar);
        ErrVarErr($profileHandle, $errVar);
        $busyErr = (!is_null($poBusy)) ? '&amp;poBusy=Y' : '';
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;orderControl={$orderControl}&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "{$busyErr}&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
    }
}

?>
