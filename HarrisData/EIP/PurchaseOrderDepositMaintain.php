<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$newRow = $_GET['newRow'];
$errFound = $_GET['errFound'];
$wrnVar = $_GET['wrnVar'];

$fromPO = (isset($_GET['fromPO'])) ? strtoupper($_GET['fromPO']) : '';
$fromLine = (isset($_GET['fromLine'])) ? strtoupper($_GET['fromLine']) : '';

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Purchase Order Deposit Maintenance";
$scriptName = "PurchaseOrderDepositMaintain.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D&amp;fromPO=" . urlencode(trim($fromPO)) . "&amp;fromLine=" . urlencode(trim($fromLine));
$programName = "HPOPDP_W";

$backURL = $_SESSION[$fromURL];
if ($backURL == "" || $maintenanceCode == "D") {
    $backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=503";
}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth == "F") {
    require_once 'ProgSecurityError.php';
    exit();
}

if ($tag == "MAINTAIN") {
    
    require_once ($docType);
    print "\n <html> <head>";
    require_once ($headInclude);
    $formName = "Chg";
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'Menu.js';
    require_once 'CheckEnterChg.php';
    require_once 'NumEdit.php';
    require_once 'UpperCase.php';
    require_once 'CalendarInclude.php';
    require_once 'DateEdit.php';
    
    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.poNumber.value ==\"\" || ";
    print "\n     document.Chg.itemNumber.value ==\"\" || ";
    print "\n     document.Chg.whsNumber.value ==\"\" || ";
    print "\n     document.Chg.voucher.value ==\"\" )";
    print "\n {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (editZero(document.Chg.voucher, 9, 0) && ";
    print "\n     editNum(document.Chg.depAmount, 11, 2) && ";
    print "\n     editZero(document.Chg.whsNumber, 3, 0)) ";
    print "\n return true;";
    print "\n }";
    
    print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
    print "\n </script> \n";
    
    require_once ($genericHead);
    print "\n </head>";
    
    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "PODEPOSITMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    
    $stmtSQL = "";
    if ($maintenanceCode == "A") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL .= " Select * ";
        $stmtSQL .= " From POPDEPV01 ";
        $stmtSQL .= " Where DPPO=$fromPO and DPLINE=$fromLine";
    }
    require 'stmtSQLEnd.php';
    
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);
    $saveDPAMT = $row['DPAMT'];
    
    // Program Option Security
    $hpopdp_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
    $sec_01 = $hpopdp_OPT['sec_01'];
    $sec_02 = $hpopdp_OPT['sec_02'];
    $sec_03 = $hpopdp_OPT['sec_03'];
    $sec_04 = $hpopdp_OPT['sec_04'];
    if ($row['PDDLRC'] > 0) {
        $sec_03 = 'N';
    }
    require_once 'MaintainTop.php';
    
    print $hrTagAttr;
    require_once 'RequiredField.php';
    require_once 'ErrorDisplay.php';
    
    if ($errFound != "" || $maintenanceCode == "A") {
        if ($errFound == "" && $maintenanceCode == "A") {
            $focusField = "poNumber";
            $edtVar = "";
        } elseif ($errFound != "") {
            $focusField = "";
            $edtVar = EdtVarErr($profileHandle, $edtVar);
            $errVar = ErrVarErr($profileHandle, $errVar);
            $Err_DPPO = DecatErr_Field("@@po@@", "poNumber");
            $Err_DPLINE = DecatErr_Field("@@line", "line");
            $Err_DPITEM = DecatErr_Field("@@item", "itemNumber");
            $Err_DPWHS = DecatErr_Field("@@whs@", "whsNumber");
            $Err_DPAMT = DecatErr_Field("@@damt", "deposit");
            $Err_DPVOU = DecatErr_Field("@@vou@", "voucher");
            $Err_DPREF = DecatErr_Field("@@ref@", "reference");
            $errFound = "";
        }
        
        $row['DPPO'] = Decat_Field("@@po@@", $edtVar);
        $row['DPLINE'] = Decat_Field("@@line", $edtVar);
        $row['DPITEM'] = Decat_Field("@@item", $edtVar);
        $row['DPWHS'] = Decat_Field("@@whs@", $edtVar);
        $row['DPAMT'] = Decat_Field("@@damt", $edtVar);
        $row['DPVOU'] = Decat_Field("@@vou@", $edtVar);
        $row['DPREF'] = Decat_Field("@@ref@", $edtVar);
    } else {
        if ($maintenanceCode == "A") {
            $focusField = "poNumber";
        } else {
            $focusField = "itemNumber";
        }
    }
    
    $readOnly = ($saveDPAMT == $row['DPBAL']) ? null : true;
    
    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";
    
    $textOvr = SetTextOvr($Err_DPPO);
    if ($errFound != "" || $maintenanceCode == "A" || $maintenanceCode == "Z") {
        print "\n <tr><td class=\"dsphdr\"><span $textOvr>Purchase Order Number</span></td> ";
        print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"poNumber\" value=\"" . rtrim($row['DPPO']) . "\" size=\"8\" maxlength=\"8\">";
        print "\n                             <a href=\"{$homeURL}{$phpPath}PurchaseOrderSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=poNumber\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a></td> ";
        print "\n </tr> ";
    } else {
        print "\n <tr><td class=\"dsphdr\"><span $textOvr>Purchase Order Number</span></td> ";
        print "\n     <td class=\"inputnmbr\"><input type=\"hidden\" name=\"poNumber\" value=\"" . rtrim($row['DPPO']) . "\">$row[DPPO]</td></tr>";
        print "\n <tr><td class=\"dsphdr\">Line Number</td> ";
        print "\n     <td class=\"inputnmbr\"><input type=\"hidden\" name=\"line\" value=\"" . rtrim($row['DPLINE']) . "\">$row[DPLINE]</td></tr>";
    }
    DspErrMsg($Err_DPPO);
    
    $fieldDesc = RetValue("IMITEM='$row[DPITEM]'", "HDIMST", "IMIMDS");
    $textOvr = SetTextOvr($Err_DPITEM);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Item Number</span></td> ";
    if ($readOnly) {
        print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"itemNumber\" value=\"" . rtrim($row['DPITEM']) . "\">$row[DPITEM]";
    } else {
        print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"itemNumber\" value=\"" . rtrim($row['DPITEM']) . "\" size=\"15\" maxlength=\"15\">";
        print "\n                             <a href=\"{$homeURL}{$phpPath}ItemSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=itemNumber&amp;fldDesc=itemDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
    }
    print "\n     <span class=\"dspdesc\" id=\"itemDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_DPITEM);
    
    $fieldDesc = RetValue("WHWHS='$row[DPWHS]'", "HDWHSM", "WHWHNM");
    $textOvr = SetTextOvr($Err_DPWHS);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Number</span></td> ";
    if ($readOnly) {
        print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"whsNumber\" value=\"" . rtrim($row['DPWHS']) . "\">$row[DPWHS]";
    } else {
        print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"whsNumber\" value=\"" . rtrim($row['DPWHS']) . "\" size=\"15\" maxlength=\"3\">";
        print "\n                             <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=whsNumber&amp;fldDesc=whsDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
    }
    print "\n     <span class=\"dspdesc\" id=\"whsDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_DPWHS);
    
    $fieldDesc = '';
    if ($row[DPVOU] > 0) {
        $deposit = RetValue("VOUCHER=$row[DPVOU]", "APOPENV02", "TRNAMT");
        $fieldDesc = Format_Nbr($deposit, "2", $amtEditCode);
    }
    $textOvr = SetTextOvr($Err_DPVOU);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Voucher Number</span></td> ";
    if ($readOnly) {
        print "\n     <td class=\"inputnmbr\"><input type=\"hidden\" name=\"voucher\" value=\"" . rtrim($row['DPVOU']) . "\">$row[DPVOU]";
    } else {
        print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"voucher\" value=\"" . rtrim($row['DPVOU']) . "\" size=\"15\" maxlength=\"15\">";
        print "\n                             <a href=\"{$homeURL}{$phpPath}VoucherSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=voucher&amp;fldDesc=deposit\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a></td> ";
    }
    print "\n </tr> ";
    DspErrMsg($Err_DPVOU);

    $textOvr = SetTextOvr($Err_DPAMT);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Deposit Amount</span></td> ";
    if ($readOnly) {
        print "\n     <td class=\"inputnmbr\"><input type=\"hidden\" name=\"depAmount\" value=\"" . rtrim($row['DPAMT']) . "\">$row[DPAMT]";
    } else {
        print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"deposit\" name=\"depAmount\" value=\"" . rtrim($row['DPAMT']) . "\" size=\"15\" maxlength=\"15\"></td>";
    }
    print "\n </tr> ";
    DspErrMsg($Err_DPAMT);

    Build_Fld_Entry("Reference", "reference", "inputalph", "", "DPREF", $row[DPREF], $Err_DPREF, "20", "20", "", "", "");
    
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
    if ($maintenanceCode == "Z") {
        $maintenanceCode = "A";
    }
    
    if ($maintenanceCode == "D") {
        $_POST['poNumber'] = (isset($_GET['fromPO'])) ? strtoupper($_GET['fromPO']) : $_POST['poNumber'];
        $_POST['line'] = (isset($_GET['fromLine'])) ? strtoupper($_GET['fromLine']) : $_POST['line'];
    }
    
    $edtVar = "";
    Concat_Field("@@po@@", strtoupper($_POST['poNumber']));
    Concat_Field("@@line", strtoupper($_POST['line']));
    Concat_Field("@@item", strtoupper($_POST['itemNumber']));
    Concat_Field("@@whs@", strtoupper($_POST['whsNumber']));
    Concat_Field("@@damt", $_POST['depAmount']);
    Concat_Field("@@vou@", $_POST['voucher']);
    Concat_Field("@@ref@", $_POST['reference']);
    $edtVar .= "}{";
    
    $returnValue = Maintain_Edit("HPOPDP_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar);
    $maintenanceCode = $returnValue['maintenanceCode'];
    $errFound = $returnValue['errFound'];
    $edtVar = $returnValue['edtVar'];
    $errVar = $returnValue['errVar'];
    $wrnVar = $returnValue['wrnVar'];
    
    if (is_null($newRow) && ($errFound == "" || $maintenanceCode == "D")) {
        if ($errFound == "") {
            $confMessage = Format_ConfMsg_Desc($maintenanceCode, "Purchase Order", $_POST['poNumber'], "", "", "", "");
        } else {
            $Err_DPPO = DecatErr_Field("@@po@@", "poNumber");
            $confMessage = Format_ConfMsg_Desc("Purchase Order", $_POST['poNumber'], "", "", "", "<br>$Err_DPPO", "");
        }
        print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
    } else {
        if ($errFound == "Y" && $newRow == "Y") {
            $newRow = "E";
        }
        $poNumber = Decat_Field("@@po@@", $edtVar);
        EdtVarErr($profileHandle, $edtVar);
        ErrVarErr($profileHandle, $errVar);
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;fromPO=" . urlencode(trim($poNumber)) . "&amp;fromLine=" . urlencode(trim($_POST[itemNumber])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;newRow=" . urlencode(trim($newRow)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
    }
}

?>