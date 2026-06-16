<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound = $_GET['errFound'];
$wrnVar = $_GET['wrnVar'];

$vendorNumber = $_GET['vendorNumber'];
$orderControl = (isset($_GET['orderControl'])) ? $_GET['orderControl'] : 0;
$fromType = (isset($_GET['fromType'])) ? $_GET['fromType'] : '';
$fromReqNbr = (isset($_GET['fromReqNbr'])) ? $_GET['fromReqNbr'] : '';
$fromItem = (isset($_GET['fromItem'])) ? $_GET['fromItem'] : '';
$fromItemDesc = (isset($_GET['fromItemDesc'])) ? $_GET['fromItemDesc'] : '';
$plantNumber = (isset($_GET['plantNumber'])) ? $_GET['plantNumber'] : 0;
$seqNbr = (isset($_GET['seqNbr'])) ? $_GET['seqNbr'] : 0;
$dueDate = (isset($_GET['dueDate'])) ? $_GET['dueDate'] : 0;
$orderQty = (isset($_GET['orderQty'])) ? $_GET['orderQty'] : 0;

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

if ($orderControl == 0 && $maintenanceCode != "H") {
    $maintenanceCode = 'A';
    $tag = 'MAINTAIN';
}

$backURL = $_SESSION[$fromURL];
if ($backURL == "") {
    $backURL = "{$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}";
}

$page_title = "Create PO Maintenance";
$scriptName = "CreatePOMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;orderControl=" . urlencode(trim($orderControl));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D&amp;fromType={$fromType}&amp;fromReqNbr={$fromReqNbr}&amp;fromItem={$fromItem}&amp;startRow=" . urlencode(trim($startRow));

if ($tag == "MAINTAIN") {
    $stmtSQL = " Select * From HDOTYP Where OTAPID='PO' and OTOTCD='P' ";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $poTypeRow = db2_fetch_assoc($sqlResult);

    $page_title = "PO Header Maintenance";
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
    print "\n     document.Chg.warehouse.value ==\"\" ||";
    print "\n     document.Chg.reqDate.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";

    print "\n if (editNum(document.Chg.vendor, 7, 0) && ";
    print "\n     editNum(document.Chg.warehouse, 3, 0) && ";
    print "\n     editNum(document.Chg.buyer, 7, 0) && ";
    print "\n     editNum(document.Chg.disc1, 5, 2) && ";
    print "\n     editNum(document.Chg.disc2, 5, 2) && ";
    print "\n     editNum(document.Chg.disc3, 5, 2) && ";
    if (trim($poTypeRow[OTSDT1]) != '') {
        print "\n editdate(document.Chg.uh1Date) && ";
    }
    if (trim($poTypeRow[OTSDT2]) != '') {
        print "\n editdate(document.Chg.uh2Date) && ";
    }
    if (trim($poTypeRow[OTSDT3]) != '') {
        print "\n editdate(document.Chg.uh3Date) && ";
    }
    print "\n     editdate(document.Chg.reqDate) ";
    print "\n ) return true; ";
    print "\n } ";

    print "\n  function confirmDelete() {return confirm(\"$delRecordConf\")} ";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";

    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "POMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    $stmtSQL = "";
    $stmtSQL .= " Select * ";
    $stmtSQL .= " From POHDRW ";
    $stmtSQL .= " Where H1OCTL=$orderControl ";
    require 'stmtSQLEnd.php';
    require_once 'MaintainTop.php';

    print $hrTagAttr;
    require_once 'RequiredField.php';
    require_once 'ErrorDisplay.php';

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    if ($errFound != "" || $maintenanceCode == "A") {
        if ($errFound == "" && $maintenanceCode == "A") {
            $edtVar = "";
        } elseif ($errFound != "") {
            $focusField = "";
            $edtVar = EdtVarErr($profileHandle, $edtVar);
            $errVar = ErrVarErr($profileHandle, $errVar);
            $Err_H1VEND = DecatErr_Field("@@vend", "vendor");
            $Err_H1WHS = DecatErr_Field("@@whs@", "warehouse");
            $Err_H1BUYR = DecatErr_Field("@@buyr", "buyer");
            $Err_H1RQDT = DecatErr_Field("@@rqdt", "reqDate");
            $Err_H1DT1 = DecatErr_Field("@@dt1@", "uh1Date");
            $Err_H1DT2 = DecatErr_Field("@@dt2@", "uh2Date");
            $Err_H1DT3 = DecatErr_Field("@@dt3@", "uh3Date");
            $Err_H1PTRM = DecatErr_Field("@@ptrm", "termsCode");
            $Err_H1SVSV = DecatErr_Field("@@svsv", "shipVia");
            $Err_H1SVDS = DecatErr_Field("@@svds", "shipViaDesc");
            $Err_H1FOBC = DecatErr_Field("@@fobc", "fobCode");
            $Err_H1FOB  = DecatErr_Field("@@fobd", "fobCodeDesc");
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
        $row['H1WHS'] = Decat_Field("@@whs@", $edtVar);
        $row['H1BUYR'] = Decat_Field("@@buyr", $edtVar);
        $row['H1RQDT'] = Decat_Field("@@rqdt", $edtVar);
        $row['H1PORF'] = Decat_Field("@@porf", $edtVar);
        $row['H1DT1'] = Decat_Field("@@dt1@", $edtVar);
        $row['H1DT2'] = Decat_Field("@@dt2@", $edtVar);
        $row['H1DT3'] = Decat_Field("@@dt3@", $edtVar);
        $row['H1PTRM'] = Decat_Field("@@ptrm", $edtVar);
        $row['H1SVSV'] = Decat_Field("@@svsv", $edtVar);
        $row['H1SVDS'] = Decat_Field("@@svds", $edtVar);
        $row['H1FOBC'] = Decat_Field("@@fobc", $edtVar);
        $row['H1FOB']  = Decat_Field("@@fobd", $edtVar);
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
    } else {
        $row['H1RQDT'] = DateInputFromCYMD($row['H1RQDT']);
        $row['H1DT2'] = DateInputFromCYMD($row['H1DT2']);
        $row['H1DT3'] = DateInputFromCYMD($row['H1DT3']);
        $row['H1DT4'] = DateInputFromCYMD($row['H1DT4']);
        $row['H1DSC1'] = round($row['H1DSC1'], 2);
        $row['H1DSC2'] = round($row['H1DSC2'], 2);
        $row['H1DSC3'] = round($row['H1DSC3'], 2);
        $focusField = "vendor";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=H\">";
    print "\n <table $contentTable>";

    $fieldDesc = RetValue("VMVEND=$row[H1VEND]", "HDVEND", "VMVNA1");
    $textOvr = SetTextOvr($Err_H1VEND);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Vendor Number</span></td> ";
    if ($maintenanceCode == "A") {
        print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"vendor\" value=\"" . trim($row['H1VEND']) . "\" size=\"6\" maxlength=\"7\"> ";
        print "\n                              <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=vendor&amp;fldDesc=vendorName\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
        print "\n                              <span class=\"dspdesc\" id=\"vendorName\">$fieldDesc</span></td>";
    } else {
        print "\n      <td class=\"inputalph\"><input type=\"hidden\" name=\"vendor\" value=\"" . trim($row['H1VEND']) . "\" size=\"6\" maxlength=\"7\">{$row['H1VEND']}  <span class=\"dspdesc\" id=\"vendorName\">$fieldDesc</span></td>";
    }
    print "\n  </tr> ";
    DspErrMsg($Err_H1VEND);

    $fieldDesc = RetValue("WHWHS=$row[H1WHS]", "HDWHSM", "WHWHNM");
    $textOvr = SetTextOvr($Err_H1WHS);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Number</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"warehouse\" value=\"" . trim($row['H1WHS']) . "\" size=\"6\" maxlength=\"3\"> ";
    print "\n                              <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=warehouse&amp;fldDesc=warehouseName\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
    print "\n                              <span class=\"dspdesc\" id=\"warehouseName\">$fieldDesc</span></td>";
    print "\n  </tr> ";
    DspErrMsg($Err_H1WHS);

    Build_Fld_Entry("Required Date", "reqDate", "inputdate", "Date", "H1RQDT", $row[H1RQDT], $Err_H1RQDT, "6", "6", "Y", "", "");
    if (trim($poTypeRow[OTSDT1]) != '') {
        Build_Fld_Entry($poTypeRow[OTSDT1], "uh1Date", "inputdate", "Date", "H1DT2", $row[H1DT2], $Err_H1DT2, "6", "6", "", "", "");
    }
    if (trim($poTypeRow[OTSDT2]) != '') {
        Build_Fld_Entry($poTypeRow[OTSDT2], "uh2Date", "inputdate", "Date", "H1DT3", $row[H1DT3], $Err_H1DT3, "6", "6", "", "", "");
    }
    if (trim($poTypeRow[OTSDT3]) != '') {
        Build_Fld_Entry($poTypeRow[OTSDT3], "uh3Date", "inputdate", "Date", "H1DT4", $row[H1DT4], $Err_H1DT4, "6", "6", "", "", "");
    }

    $fieldDesc = RetValue("TMCTRM='$row[H1PTRM]'", "HDTRMS", "TMCTDS");
    $textOvr = SetTextOvr($Err_H1PTRM);
    print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Terms Code</span></td> ";
    print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"termsCode\" value=\"" . rtrim($row['H1PTRM']) . "\" size=\"6\" maxlength=\"2\"> ";
    print "\n                                     <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=termsCode&amp;fldDesc=termsCodeDesc\" onclick=\"$searchWinVar\"> $searchImage</a>";
    print "\n                                     <span class=\"dspdesc\" id=\"termsCodeDesc\">$fieldDesc</span></td> ";
    print "\n         </tr> ";
    DspErrMsg($Err_H1PTRM);

    $fieldDesc = RetValue("SVSVSV='$row[H1SVSV]'", "HDSHPV", "SVSVDS");
    $textOvr = SetTextOvr($Err_H1SVSV);
    print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Ship Via</span></td> ";
    print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"shipVia\" value=\"" . rtrim($row['H1SVSV']) . "\" size=\"6\" maxlength=\"2\"> ";
    print "\n                                     <a href=\"{$homeURL}{$phpPath}ShipViaSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=shipVia&amp;fldDesc=shipViaDesc\" onclick=\"$searchWinVar\"> $searchImage</a>";
    print "\n                                     <input type=\"text\" name=\"shipViaDesc\" value=\"" . rtrim($fieldDesc) . "\" size=\"15\" maxlength=\"15\"></td>";
    print "\n         </tr> ";
    DspErrMsg($Err_H1SVSV);

    $fieldDesc = RetValue("FBFBCD='$row[H1FOBC]'", "HDFOBM", "FBFBDS");
    $textOvr = SetTextOvr($Err_H1FOBC);
    print "\n         <tr><td class=\"dsphdr\"><span $textOvr>FOB Code</span></td> ";
    print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"fobCode\" value=\"" . rtrim($row['H1FOBC']) . "\" size=\"6\" maxlength=\"2\"> ";
    print "\n                                     <a href=\"{$homeURL}{$cGIPath}FOBCodeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=fobCode&amp;fldDesc=fobCodeDesc\" onclick=\"$searchWinVar\"> $searchImage</a>";
    print "\n                                     <input type=\"text\" name=\"fobCodeDesc\" value=\"" . rtrim($fieldDesc) . "\" size=\"15\" maxlength=\"15\"></td>";
    print "\n         </tr> ";
    DspErrMsg($Err_H1FOBC);

    $fieldDesc = RetValue("BMBUYR=$row[H1BUYR]", "HDBUYR", "BMBNA1");
    $textOvr = SetTextOvr($Err_H1BUYR);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Buyer Number</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"buyer\" value=\"" . trim($row['H1BUYR']) . "\" size=\"6\" maxlength=\"7\"> ";
    print "\n                              <a href=\"{$homeURL}{$phpPath}BuyerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=buyer&amp;fldDesc=buyerName\" onclick=\"$searchWinVar\"> $searchImage</a>";
    print "\n                              <span class=\"dspdesc\" id=\"buyerName\">$fieldDesc</span></td>";
    print "\n  </tr> ";
    DspErrMsg($Err_H1BUYR);

    $textOvr = SetTextOvr($Err_H1PORF);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Reference</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"reference\" value=\"" . trim($row['H1PORF']) . "\" size=\"15\" maxlength=\"15\"> ";
    print "\n  </tr> ";
    DspErrMsg($Err_H1PORF);

    Build_Fld_Entry("Allow Backorders", "allowBO", "inputalph", "YORN", "H1BOAL", $row[H1BOAL], $Err_H1BOAL, "1", "1", "Y", "", "");
    Build_Fld_Entry("Confirmation Flag", "confFlag", "inputalph", "YORN", "H1CONF", $row[H1CONF], $Err_H1CONF, "1", "1", "Y", "", "");
    Build_Fld_Entry("Change Notice Number", "chgNotice", "inputnmbr", "", "H1CHNN", $row[H1CHNN], $Err_H1CHNN, "2", "2", "", "", "");

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

    $textOvr = SetTextOvr($Err_H1DSC1);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Trade Discount Percent</span></td> ";
    print "\n      <td class=\"inputalph\"> ";
    print "\n      <input type=\"text\" name=\"disc1\" value=\"" . trim($row['H1DSC1']) . "\" size=\"6\" maxlength=\"6\"> ";
    print "\n      <input type=\"text\" name=\"disc2\" value=\"" . trim($row['H1DSC2']) . "\" size=\"6\" maxlength=\"6\"> ";
    print "\n      <input type=\"text\" name=\"disc3\" value=\"" . trim($row['H1DSC3']) . "\" size=\"6\" maxlength=\"6\"> ";
    print "\n  </td></tr> ";
    DspErrMsg($Err_H1DSC1);

    print "\n </table> ";

    print "\n <script TYPE=\"text/javascript\">";
    print "\n document.Chg.$focusField.focus();";
    print "\n </script>";
    print "\n </form>";
    require_once 'MaintainBottom.php';
    print $hrTagAttr;
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    require_once 'Trailer.php';
    print "</body> </html>";
    exit();
}

if ($tag == "ITEMMAINTAIN") {
    $page_title = "PO Item Maintenance";
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
    require_once 'DateEdit.php';
    require_once 'UpperCase.php';

    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.prodClass.value ==\"\" || ";
    print "\n     document.Chg.warehouse.value ==\"\" || ";
    print "\n     document.Chg.reqDate.value ==\"\" || ";
    print "\n     document.Chg.qtyOrdered.value ==\"\" ";
    if ($maintenanceCode == 'N') {
        print "\n  || document.Chg.itemNumber.value ==\"\" ";
        print "\n  || document.Chg.itemDesc.value ==\"\" ";
        print "\n  || document.Chg.uom.value ==\"\" ";
    }
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";

    print "\n if (editNum(document.Chg.qtyOrdered, 13, 4) && ";
    print "\n     editNum(document.Chg.warehouse, 3, 0) && ";
    print "\n     editdate(document.Chg.reqDate) ";
    if ($poDetailDate1 != '') {
        print "\n  && editdate(document.Chg.ud1Date) ";
    }
    if ($poDetailDate2 != '') {
        print "\n  && editdate(document.Chg.ud2Date) ";
    }
    if ($poDetailDate3 != '') {
        print "\n  && editdate(document.Chg.ud3Date) ";
    }
    print "\n ) return true; ";
    print "\n } ";

    print "\n  function confirmDelete() {return confirm(\"$delRecordConf\")} ";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";

    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "CREATEPOMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    $stmtSQL = "";
    $stmtSQL .= " Select * ";
    $stmtSQL .= " From PODTLW ";
    $stmtSQL .= " Where D1OCTL=$orderControl and D1TYPE='$fromType' and D1REQN='$fromReqNbr' and D1ITEM='$fromItem' ";
    require 'stmtSQLEnd.php';
    require_once 'MaintainTop.php';

    print $hrTagAttr;
    require_once 'RequiredField.php';
    require_once 'ErrorDisplay.php';

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    if ($errFound != "" || $maintenanceCode == "A" || $maintenanceCode == "N") {
        if ($errFound == "" && ($maintenanceCode == "A" || $maintenanceCode == "N")) {
            $edtVar = "";
            if ($maintenanceCode == "N") {
                $h1rqdt = RetValue("H1OCTL=$orderControl", "POHDRW", "H1RQDT");
                $row[D1RQDT] = DateInputFromCYMD($h1rqdt);
                $focusField = "itemNumber";
            }
        } elseif ($errFound != "") {
            $focusField = "";
            $edtVar = EdtVarErr($profileHandle, $edtVar);
            $errVar = ErrVarErr($profileHandle, $errVar);
            if ($maintenanceCode == 'N') {
                $Err_D1ITEM = DecatErr_Field("@@item", "itemNumber");
                $Err_D1IMDS = DecatErr_Field("@@imds", "itemDesc");
                $Err_D1BUOM = DecatErr_Field("@@buom", "uom");

            }
            $Err_D1OVWH = DecatErr_Field("@@whs@", "warehouse");
            $Err_D1PCLS = DecatErr_Field("@@pcls", "pcls");
            $Err_D1QTOR = DecatErr_Field("@@qtor", "qtyOrdered");
            $Err_D1DSCC = DecatErr_Field("@@cost", "cost");
            $Err_D1RQDT = DecatErr_Field("@@rqdt", "rqdt");
            if ($maintenanceCode == 'N') {
                $row['D1ITEM'] = Decat_Field("@@item", $edtVar);
                $row['D1IMDS'] = Decat_Field("@@imds", $edtVar);
                $row['D1BUOM'] = Decat_Field("@@buom", $edtVar);
            }
            $row['D1OVWH'] = Decat_Field("@@whs@", $edtVar);
            $row['D1PCLS'] = Decat_Field("@@pcls", $edtVar);
            $row['D1QTOR'] = Decat_Field("@@qty@", $edtVar);
            $row['D1DSCC'] = Decat_Field("@@cost", $edtVar);
            $row['D1RQDT'] = Decat_Field("@@rqdt", $edtVar);
            $row['D1OPDT'] = Decat_Field("@@opdt", $edtVar);
            $row['D1CPDT'] = Decat_Field("@@cpdt", $edtVar);
            $row['D1LADT'] = Decat_Field("@@ladt", $edtVar);
        }
    } else {
        $row['D1RQDT'] = DateInputFromCYMD($row['D1RQDT']);
        $row['D1OPDT'] = DateInputFromCYMD($row['D1OPDT']);
        $row['D1CPDT'] = DateInputFromCYMD($row['D1CPDT']);
        $row['D1LADT'] = DateInputFromCYMD($row['D1LADT']);
        $focusField = "reqDate";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;fromType={$fromType}&amp;fromReqNbr={$fromReqNbr}&amp;fromItem={$fromItem}&amp;startRow={$startRow}&amp;tag=Edit_Data&amp;maintenanceCode={$maintenanceCode}\">";
    print "\n <table $contentTable>";

    if ($maintenanceCode == 'N' || $row[D1POEC] == "N") {
        Build_Fld_Entry('Item Number', "itemNumber", "inputalph", "", "D1ITEM", $row[D1ITEM], $Err_D1ITEM, "15", "15", "Y", "", "");
        Build_Fld_Entry('Description', "itemDesc", "inputalph", "", "D1IMDS", $row[D1IMDS], $Err_D1IMDS, "30", "30", "Y", "", "");
    } else {
        $fieldDesc = RetValue("IMITEM='$row[D1ITEM]'", "HDIMST", "IMIMDS");
        $fieldDesc = ($fieldDesc == '') ? $row[D1IMDS] : $fieldDesc;
        Build_DspFld('Item Number', trim($fieldDesc) . '&nbsp; &nbsp; [' . trim($row[D1ITEM]) . ']', '', 'A');
    }

    $fieldDesc = RetValue("WHWHS=$row[D1OVWH]", "HDWHSM", "WHWHNM");
    $textOvr = SetTextOvr($Err_D1OVWH);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Number</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"warehouse\" value=\"" . trim($row['D1OVWH']) . "\" size=\"6\" maxlength=\"3\"> ";
    print "\n                              <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=warehouse&amp;fldDesc=warehouseName\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
    print "\n                              <span class=\"dspdesc\" id=\"warehouseName\">$fieldDesc</span></td>";
    print "\n  </tr> ";
    DspErrMsg($Err_D1OVWH);

    if ($row[D1POEC] == "S" || $row[D1POEC] == "X") {
        $stockChecked = ($row[D1POEC] == "S") ? 'CHECKED' : '';
        $notStockedChecked = ($row[D1POEC] == "X") ? 'CHECKED' : '';
        print "\n  <tr><td class=\"dsphdr\">Entry Code</td> ";
        print "\n      <td class=\"inputalph\"><input type=\"radio\" name=\"entryCode\" value=\"S\" $stockChecked>Stock<input type=\"radio\" name=\"entryCode\" value=\"X\" $notStockedChecked>Not Stocked";
        print "\n  </tr> ";
    } else {
        $fieldDesc = ($row[D1POEC] == "S") ? 'Stock Item' : 'Nonstocked Item';
        Build_DspFld('Entry Code', trim($fieldDesc), '', 'A');
        print "\n  <tr><td><input type=\"hidden\" name=\"entryCode\" value=\"N\"></td></tr> ";
    }

    $fieldDesc = RetValue("UMUOM='$row[D1BUOM]'", "HDUOM", "UMUMLD");
    $textOvr = SetTextOvr($Err_D1BUOM);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Purchasing Unit Of Measure</span></td> ";
    print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"uom\" value=\"" . rtrim($row['D1BUOM']) . "\" size=\"3\" maxlength=\"3\">";
    print "\n                             <a href=\"{$homeURL}{$phpPath}UnitOfMeasureSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=uom&amp;fldDesc=uomDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
    print "\n     <span class=\"dspdesc\" id=\"uomDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_D1BUOM);

    $fieldDesc = RetValue("PCPCLS='$row[D1PCLS]'", "HDPCLS", "PCPCDS");
    $textOvr = SetTextOvr($Err_D1PCLS);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Product Class</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"prodClass\" value=\"" . trim($row['D1PCLS']) . "\" size=\"6\" maxlength=\"4\"> ";
    print "\n                              <a href=\"{$homeURL}{$phpPath}ProdClassSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=prodClass&amp;fldDesc=prodClassDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
    print "\n                              <span class=\"dspdesc\" id=\"prodClassDesc\">$fieldDesc</span></td>";
    print "\n  </tr> ";
    DspErrMsg($Err_D1PCLS);

    Build_Fld_Entry("Required Date", "reqDate", "inputdate", "Date", "D1RQDT", $row[D1RQDT], $Err_D1RQDT, "6", "6", "Y", "", "");
    if ($poDetailDate1 != '') {
        Build_Fld_Entry($poDetailDate1, "ud1Date", "inputdate", "Date", "D1OPDT", $row[D1OPDT], $Err_D1OPDT, "6", "6", "", "", "");
    }
    if ($poDetailDate2 != '') {
        Build_Fld_Entry($poDetailDate2, "ud2Date", "inputdate", "Date", "D1CPDT", $row[D1CPDT], $Err_D1CPDT, "6", "6", "", "", "");
    }
    if ($poDetailDate3 != '') {
        Build_Fld_Entry($poDetailDate3, "ud3Date", "inputdate", "Date", "D1LADT", $row[D1LADT], $Err_D1LADT, "6", "6", "", "", "");
    }

    if ($hpopem_OPT['sec_07'] != 'N') {
        $textOvr = SetTextOvr($Err_D1DSCC);
        print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Cost</span></td> ";
        print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"cost\" value=\"" . trim($row['D1DSCC']) . "\" size=\"15\" maxlength=\"15\"> ";
        print "\n  </tr> ";
        DspErrMsg($Err_D1DSCC);
    } else {
        Build_DspFld('Cost', $row['D1DSCC'], '', 'N');
    }

    $textOvr = SetTextOvr($Err_D1QTOR);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Order Quantity</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"qtyOrdered\" value=\"" . trim($row['D1QTOR']) . "\" size=\"15\" maxlength=\"15\"> $reqFieldChar ";
    print "\n  </tr> ";
    DspErrMsg($Err_D1QTOR);

    print "\n </table> ";

    print "\n <script TYPE=\"text/javascript\">";
    print "\n document.Chg.$focusField.focus();";
    print "\n </script>";
    print "\n </form>";
    require_once 'MaintainBottom.php';
    print $hrTagAttr;
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    require_once 'Trailer.php';
    print "</body> </html>";
    exit();
}

if ($tag == "Edit_Data") {

    if ($maintenanceCode == 'A') {
        $job = RetValue("PSTYPE='$fromType' and PSREQN='$fromReqNbr' and PSITEM='$fromItem'", "POSGPO", "PSJOB");
        if ($job > 0) {
            $user = RetValue("H1OCTL=$job", "POHDRW inner join SYUSER on H1USER=USUSER", "USDESC");
            $errorHdr = "Item " . trim($fromItemDesc) . ' [' . trim($fromItem) . "] has been selected by {$user}";
            print "<meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;errorHdr=" . urlencode(trim($errorHdr)) . "\"> ";
            exit();
        }
    }

    $edtVar = "";

    if ($maintenanceCode == 'M') {
        $stmtSQL = " Select * From HDMPLM Where PLPLT={$plantNumber} and PLTYPE='{$fromType}' and 
                      PLPN='{$fromItem}' and PLDAT='{$dueDate}' and PLSEQN={$seqNbr}";
        $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
        $mrpRow = db2_fetch_assoc($sqlResult);

        if (trim($mrpRow['PLPN']) == trim($fromItem)) {
            $stmtSQL1 = " Select * From POHDRW Where H1USER='{$userProfile}' Fetch First Row Only";
            $sqlResult1 = db2_exec($i5Connect->getConnection(), $stmtSQL1);
            $hdrRow = db2_fetch_assoc($sqlResult1);
            $orderControl = (array_key_exists('H1OCTL', $hdrRow)) ? $hdrRow['H1OCTL'] : 0;
            $maintenanceCode = 'I';
            $_POST['addItem'] = $fromItem;
            $_POST['addQty'] = $mrpRow['PLPQTY'];
            Concat_Field("@@rqdt", Date_ISO_MDY($mrpRow['PLDAT']));
        }
    }

    Concat_Field("@@octl", $orderControl);
    if ($maintenanceCode == 'H') {
        Concat_Field("@@vend", $_POST['vendor']);
        Concat_Field("@@whs@", $_POST['warehouse']);
        Concat_Field("@@buyr", $_POST['buyer']);
        Concat_Field("@@rqdt", $_POST['reqDate']);
        Concat_Field("@@porf", strtoupper($_POST['reference']));
    } elseif ($maintenanceCode == 'C' || $maintenanceCode == 'N') {
        if ($maintenanceCode == 'N') {
            Concat_Field("@@item", strtoupper($_POST['itemNumber']));
            Concat_Field("@@imds", $_POST['itemDesc']);
            Concat_Field("@@buom", strtoupper($_POST['uom']));
        } else {
            Concat_Field("@@type", $fromType);
            Concat_Field("@@reqn", $fromReqNbr);
            Concat_Field("@@item", $fromItem);
        }
        Concat_Field("@@whs@", $_POST['warehouse']);
        Concat_Field("@@pcls", strtoupper($_POST['prodClass']));
        if (isset($_POST['entryCode'])) {
            Concat_Field("@@poec", $_POST['entryCode']);
        }
        Concat_Field("@@rqdt", $_POST['reqDate']);
        if ($poDetailDate1 != '') {
            Concat_Field("@@opdt", $_POST['ud1Date']);
        }
        if ($poDetailDate2 != '') {
            Concat_Field("@@cpdt", $_POST['ud2Date']);
        }
        if ($poDetailDate3 != '') {
            Concat_Field("@@ladt", $_POST['ud3Date']);
        }
        Concat_Field("@@cost", $_POST['cost']);
        Concat_Field("@@qty@", $_POST['qtyOrdered']);
    } elseif ($maintenanceCode == 'I') {
        Concat_Field("@@item", strtoupper($_POST['addItem']));
        Concat_Field("@@qty@", $_POST['addQty']);
    } else {
        Concat_Field("@@type", $fromType);
        Concat_Field("@@reqn", $fromReqNbr);
        Concat_Field("@@item", $fromItem);
    }
    $edtVar .= "}{";
    // echo $edtVar;
    // exit();
    $returnValue = Maintain_Edit("HPOHDR_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
    $maintenanceCode = $returnValue['maintenanceCode'];
    $errFound = $returnValue['errFound'];
    $edtVar = $returnValue['edtVar'];
    $errVar = $returnValue['errVar'];

    if ($errFound == "") {
        if ($maintenanceCode == 'D' && $fromItem == '') {
            if (isset($_SESSION["returnURL"])) {
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$_SESSION["returnURL"]}\">";
            } else {
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}&amp;tag=QSEARCH&amp;startRow=1\"> ";
            }
        } else {
            if ($maintenanceCode == 'P') {
                $addPO = Decat_Field("@@adpo", $edtVar);
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}&amp;tag=QSEARCH&amp;startRow=1&addPO={$addPO}\"> ";
            } elseif ($orderControl == '') {
                $orderControl = Decat_Field("@@octl", $edtVar);
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;startRow=" . urlencode(trim($startRow)) . "\"> ";
            } else {
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;startRow=1\"> ";
            }
        }
    } elseif ($maintenanceCode == "A" || $maintenanceCode == "I" || $maintenanceCode == "P") {
        $errorHdr = DecatErr_Field("@@hdr@", "header");
        $errorItem = DecatErr_Field("@@item", "item");
        print "<meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;errorHdr=" . urlencode(trim($errorHdr)) . "&amp;errorItem=" . urlencode(trim($errorItem)) . "\"> ";
    } else {
        EdtVarErr($profileHandle, $edtVar);
        ErrVarErr($profileHandle, $errVar);
        $tag = ($maintenanceCode == "C" || $maintenanceCode == "N") ? 'ITEMMAINTAIN' : 'MAINTAIN';
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag={$tag}&amp;fromType={$fromType}&amp;fromReqNbr={$fromReqNbr}&amp;fromItem={$fromItem}&amp;startRow=" . urlencode(trim($startRow)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
    }
}

?>
