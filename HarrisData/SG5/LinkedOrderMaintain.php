<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$newRow = $_GET['newRow'];
$errFound = $_GET['errFound'];
$wrnVar = $_GET['wrnVar'];

$orderNumber = (isset($_GET['orderNumber'])) ? strtoupper($_GET['orderNumber']) : '';
$lineNumber = (isset($_GET['lineNumber'])) ? strtoupper($_GET['lineNumber']) : '';
$readyForApproval = (isset($_GET['ready'])) ? strtoupper($_GET['ready']) : null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Linked Order Maintenance";
$scriptName = "LinkedOrderMaintain.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;orderNumber=" . urlencode(trim($orderNumber)) . "&amp;lineNumber=" . urlencode(trim($lineNumber));
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D&amp;orderNumber=" . urlencode(trim($orderNumber)) . "&amp;lineNumber=" . urlencode(trim($lineNumber));
$programName = "HOELOM_W";

$backURL = $_SESSION[$fromURL];
if ($backURL == "" || $maintenanceCode == "D") {
    $backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=502";
}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth == "F") {
    require_once 'ProgSecurityError.php';
    exit;
}

// Program Option Security
$hporqr_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $hporqr_OPT['sec_01'];
$sec_02 = $hporqr_OPT['sec_02'];

$rcptOnly = ($maintenanceCode == 'R') ? " and IHSTAT='S'" : "";
$stmtSQL = " Select * ";
$stmtSQL .= " From OEORDTV01 ";
$stmtSQL .= " Where ODORD#=$orderNumber and ODORL#=$lineNumber $rcptOnly";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

if (($tag == "Edit_Data" && ($row['OEBUSY'] == 'B' || $row['POBUSY'] == 'B')) || ($tag == "" && $maintenanceCode == 'R')) {
    $tag = 'MAINTAIN';
}

if ($tag == "MAINTAIN") {
    require_once($docType);
    print "\n <html> <head>";
    require_once($headInclude);
    $formName = "Chg";
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'Menu.js';

    require_once 'CheckEnterChg.php';
    require_once 'NumEdit.php';
    require_once 'UpperCase.php';
    require_once 'CalendarInclude.php';
    require_once 'DateEdit.php';

    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.quantity.value ==\"\" || ";
    print "\n     document.Chg.reqDate.value ==\"\" )";
    print "\n {alert(\"$reqFieldError\"); return false;} ";
    if ($maintenanceCode != 'R') {
        print "\n if (editZero(document.Chg.quantity, 13, 4) && ";
        print "\n     editdate(document.Chg.reqDate)) ";
    }
    print "\n return true;";
    print "\n }";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";

    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "REQREQUESTMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";

    $updAllowed = true;
    if ($row['OEBUSY'] == 'B' || $row['POBUSY'] == 'B' || ($allowUpdLinkedPOPrinted != 'Y' && $row['POFL01'] == '2')  || ($maintenanceCode != 'R' && $row['PDQRCV'] > 0)) {
        $updAllowed = false;
    }

    print "\n <table $contentTable>";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td>";
    print "\n     <td class=\"toolbar\">";
    if ($updAllowed) {
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
    }
    if ($backURL != "") {
        print "\n <a href=\"$backURL\">$cancelImageMed</a>";
    } else {
        print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";
    }
    print "\n </td></tr></table>";

    print $hrTagAttr;

    require_once 'RequiredField.php';
    require_once 'ErrorDisplay.php';

    if ($errFound != "") {
        $focusField = "";
        $edtVar = EdtVarErr($profileHandle, $edtVar);
        $errVar = ErrVarErr($profileHandle, $errVar);
        $Err_PDQTOR = DecatErr_Field("@@sqor", "quantity");
        $Err_PDRQDT = DecatErr_Field("@@rqdt", "reqDate");
        $Err_PDQRTC = DecatErr_Field("@@qrtc", "received");
        $errFound = "";

        $row['PDQTOR'] = Decat_Field("@@sqor", $edtVar);
        $row['PDRQDT'] = Decat_Field("@@rqdt", $edtVar);
        $row['PDQRTC'] = Decat_Field("@@qrtc", $edtVar);
        $dtlcmt = $_SESSION[$eID]['dtlcmt'];

    } else {
        $focusField = "quantity";
        $row['PDRQDT'] = DateInputFromCYMD($row['PDRQDT']);
        $dtlcmt = Linked_OrderComments($orderNumber, $lineNumber);
        $hdrcmt = Linked_OrderComments($orderNumber, 000);
        $trlcmt = Linked_OrderComments($orderNumber, 999);
    }

    print "\n \n <form class=\"formClass\" METHOD=POST id='Chg' NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";
    Build_DspFld("Vendor", $row[VMVNA1], '[' . $row[POVEND] . ']', "A");
    $disabled = '';
    if ($row['POBUSY'] == 'B') {
        $desc = 'Update not allowed. Purchase Order is busy.';
        $disabled = 'DISABLED';
    } elseif ($allowUpdLinkedPOPrinted != 'Y' && $row[POFL01] == '2') {
        $desc = 'Update not allowed. Purchase Order has been printed.';
        $disabled = 'DISABLED';
    } elseif ($row[POFL01] == '2') {
        $desc = 'Printed';
    } else {
        $desc = 'Not Printed';
    }
    if ($maintenanceCode == 'R') {
        $disabled = 'DISABLED';
    }
    $poDesc = '<span class="accessError">' . $desc . '</span>';
    Build_DspFld("Purchase Order", $row[PDPO], $poDesc, "N");

    Build_DspFld("Customer", $row[ST_CMCNA1], '[' . $row[ODSHTO] . ']', "A");
    $soDesc = ($row['OEBUSY'] == 'B') ? '<span class="accessError">Update not allowed. Sales Order is busy</span>' : '';
    Build_DspFld("Sales Order", $row['ODORD#'], $soDesc, "N");
    Build_DspFld("Item", $row[ODIMDS], '[' . $row[ODITEM] . ']', "A");
    $fieldDesc = RetValue("PCPCLS='$row[ODPCLS]'", "HDPCLS", "PCPCDS");
    Build_DspFld("Product Class", $fieldDesc, '[' . $row[ODPCLS] . ']', "A");

    $textOvr = SetTextOvr($Err_PDQTOR);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Quantity Ordered</span></td> ";
    print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"quantity\" name=\"quantity\" value=\"" . rtrim($row['PDQTOR']) . "\" size=\"15\" maxlength=\"15\"\" $disabled>";
    print "\n </tr> ";
    DspErrMsg($Err_PDQTOR);

    if ($maintenanceCode == 'R') {
        $textOvr = SetTextOvr($Err_PDQRTC);
        print "\n <tr><td class=\"dsphdr\"><span $textOvr>Quantity Received</span></td> ";
        print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"received\" name=\"received\" value=\"" . rtrim($row['PDQRTC']) . "\" size=\"15\" maxlength=\"15\"\">";
        print "\n </tr> ";
        DspErrMsg($Err_PDQRTC);
    }

    $dspOnly = ($disabled) ? 'Y' : '';
    Build_Fld_Entry("Required Date", "reqDate", "inputdate", "Date", "PDRQDT", $row[PDRQDT], $Err_PDRQDT, "6", "6", "Y", $dspOnly, "");
    print "\n </table>";

    print "\n <fieldset class=\"legendBody\"> ";
    print "\n     <legend class=\"legendTitle\">Comments</legend> ";
    print "\n <table $contentTable>";
    if ($maintenanceCode == 'R') {
        print "\n <tr><td>&nbsp;</td></tr> ";
    } else {
        print "\n <tr><td><td class=\"inputcode\"><input name=\"icar\" type=\"radio\" VALUE='A' CHECKED >Append &nbsp; <input name=\"icar\" type=\"radio\" VALUE='R'>Replace</td></td></tr> ";
    }
    print "\n <tr><td class=\"dsphdr\">Item</td> ";
    print "\n     <td class=\"inputcmt\">";
    if ($dtlcmt != '') {
        print "\n     <textarea id=\"dtlcmt\" name=\"dtlcmt\" ROWS=10 COLS=60 WRAP=\"hard\" $disabled>{$dtlcmt}</textarea>";
    } else {
        print "\n     <textarea id=\"dtlcmt\" name=\"dtlcmt\" ROWS=10 COLS=60 WRAP=\"hard\" $disabled></textarea>";
    }
    print "\n </td> ";
    print "\n <td><input type=\"hidden\" name=\"dtlcmtorig\" value=\"" . $dtlcmt . "\">";
    print "\n </tr> ";

    print "\n <tr><td>&nbsp;</td></tr> ";
    if ($maintenanceCode == 'R') {
        print "\n <tr><td>&nbsp;</td></tr> ";
    } else {
        print "\n <tr><td><td class=\"inputcode\"><input name=\"hcar\" type=\"radio\" VALUE='A' CHECKED >Append &nbsp; <input name=\"hcar\" type=\"radio\" VALUE='R'>Replace</td></td></tr> ";
    }
    print "\n <tr><td class=\"dsphdr\">Header</td> ";
    print "\n     <td class=\"inputcmt\">";
    if ($hdrcmt != '') {
        print "\n     <textarea id=\"hdrcmt\" name=\"hdrcmt\" ROWS=10 COLS=60 WRAP=\"hard\" $disabled>{$hdrcmt}</textarea>";
    } else {
        print "\n     <textarea id=\"hdrcmt\" name=\"hdrcmt\" ROWS=10 COLS=60 WRAP=\"hard\" $disabled></textarea>";
    }
    print "\n </td> ";
    print "\n <td><input type=\"hidden\" name=\"hdrcmtorig\" value=\"" . $hdrcmt . "\">";
    print "\n </tr> ";

    print "\n <tr><td>&nbsp;</td></tr> ";
    if ($maintenanceCode == 'R') {
        print "\n <tr><td>&nbsp;</td></tr> ";
    } else {
        print "\n <tr><td><td class=\"inputcode\"><input name=\"tcar\" type=\"radio\" VALUE='A' CHECKED >Append &nbsp; <input name=\"tcar\" type=\"radio\" VALUE='R'>Replace</td></td></tr> ";
    }
    print "\n <tr><td class=\"dsphdr\">Trailer</td> ";
    print "\n     <td class=\"inputcmt\">";
    if ($trlcmt != '') {
        print "\n     <textarea id=\"trlcmt\" name=\"trlcmt\" ROWS=10 COLS=60 WRAP=\"hard\" $disabled>{$trlcmt}</textarea>";
    } else {
        print "\n     <textarea id=\"trlcmt\" name=\"trlcmt\" ROWS=10 COLS=60 WRAP=\"hard\" $disabled></textarea>";
    }
    print "\n </td> ";
    print "\n <td><input type=\"hidden\" name=\"trlcmtorig\" value=\"" . $trlcmt . "\">";
    print "\n </tr> ";

    print "\n </table> ";
    print "\n </fieldset> ";

    print "\n <script TYPE=\"text/javascript\">";
    print "\n document.Chg.$focusField.focus();";
    print "\n </script>";
    print "\n </form>";

    print "\n <table $contentTable>";
    print "\n     <td class=\"toolbar\">";
    if ($updAllowed) {
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
    }
    if ($backURL != "") {
        print "\n <a href=\"$backURL\">$cancelImageMed</a>";
    } else {
        print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";
    }
    print "\n </td></tr></table>";
    print $hrTagAttr;
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    require_once 'Trailer.php';
    print "</body> </html>";
    exit;
}

if ($tag == "Edit_Data") {

    $qty = (isset($_POST['quantity'])) ? $_POST['quantity'] : 0;
    $reqDate = (isset($_POST['reqDate'])) ? $_POST['reqDate'] : 0;
    $dtlcmt = (isset($_POST['dtlcmt'])) ? $_POST['dtlcmt'] : '';
    $hdrcmt = (isset($_POST['hdrcmt'])) ? $_POST['hdrcmt'] : '';
    $trlcmt = (isset($_POST['trlcmt'])) ? $_POST['trlcmt'] : '';
    $icar = (isset($_POST['icar'])) ? $_POST['icar'] : 'A';
    $hcar = (isset($_POST['hcar'])) ? $_POST['hcar'] : 'A';
    $tcar = (isset($_POST['tcar'])) ? $_POST['tcar'] : 'A';
    $dtlCmtChg = ($dtlcmt != $_POST['dtlcmtorig']) ? 'Y' : 'N';
    $hdrCmtChg = ($hdrcmt != $_POST['hdrcmtorig']) ? 'Y' : 'N';
    $trlCmtChg = ($trlcmt != $_POST['trlcmtorig']) ? 'Y' : 'N';

    $edtVar = "";
    Concat_Field("@@sord", $orderNumber);
    Concat_Field("@@sorl", $lineNumber);
    Concat_Field("@@qord", $qty);
    Concat_Field("@@rqdt", $reqDate);
    Concat_Field("@@dcmc", $dtlCmtChg);
    Concat_Field("@@hcmc", $hdrCmtChg);
    Concat_Field("@@tcmc", $trlCmtChg);
    Concat_Field("@@icar", $icar);
    Concat_Field("@@hcar", $hcar);
    Concat_Field("@@tcar", $tcar);
    if ($maintenanceCode == 'R') {
        $qty = (isset($_POST['received'])) ? $_POST['received'] : 0;
        Concat_Field("@@qrtc", $qty);
    }
    Concat_Field("@@logu", $logUpdLinkedPONotPrinted);
    $edtVar .= "}{";

    $returnValue = Linked_Order_Edit("HOELOM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $dtlcmt, $hdrcmt, $trlcmt);
    $maintenanceCode = $returnValue['maintenanceCode'];
    $errFound = $returnValue['errFound'];
    $edtVar = $returnValue['edtVar'];
    $errVar = $returnValue['errVar'];
    $_SESSION[$eID]['dtlcmt'] = $dtlcmt;
    $_SESSION[$eID]['hdrcmt'] = $hdrcmt;
    $_SESSION[$eID]['trlcmt'] = $trlcmt;

    if ($errFound == "") {
        $confMessage = Format_ConfMsg_Desc("C", "Sales Order", $orderNumber, "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
    } else {
        EdtVarErr($profileHandle, $edtVar);
        ErrVarErr($profileHandle, $errVar);
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;orderNumber=" . urlencode(trim($orderNumber)) . "&amp;lineNumber=" . urlencode(trim($lineNumber)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
    }
}

// Maintenance Edit
function Linked_Order_Edit($pgmName, $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $dtlCmt, $hdrCmt, $trlCmt)
{
    global $pgmLibrary, $i5Connect;
    if (is_null($errFound)) $errFound = "";
    if (is_null($edtVar)) $edtVar = "";
    if (is_null($errVar)) $errVar = "";
    if (is_null($dtlCmt)) $dtlCmt = "";
    if (is_null($hdrCmt)) $hdrCmt = "";
    if (is_null($trlCmt)) $trlCmt = "";

    $pgmCall = array(
        array("Name" => "userProfile", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "10"),
        array("Name" => "maintenanceCode", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1"),
        array("Name" => "errFound", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1"),
        array("Name" => "edtVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"),
        array("Name" => "errVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"),
        array("Name" => "dtlCmt", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"),
        array("Name" => "hdrCmt", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"),
        array("Name" => "trlCmt", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"));

    $pgm = i5_program_prepare("$pgmName", $pgmCall);
    if (!$pgm) {
        die("<br>Validate_Data ($pgmName) prepare error. Error Number=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = array(
        "userProfile" => $userProfile,
        "maintenanceCode" => $maintenanceCode,
        "errFound" => $errFound,
        "edtVar" => $edtVar,
        "errVar" => $errVar,
        "dtlCmt" => $dtlCmt,
        "hdrCmt" => $hdrCmt,
        "trlCmt" => $trlCmt);

    $parmOut = array(
        "userProfile" => "userProfile",
        "maintenanceCode" => "maintenanceCode",
        "errFound" => "errFound",
        "edtVar" => "edtVar",
        "errVar" => "errVar",
        "dtlCmt" => $dtlCmt,
        "hdrCmt" => $hdrCmt,
        "trlCmt" => $trlCmt);

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br>Validate_Data ($pgmName) call errno=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $returnValue['userProfile'] = $userProfile;
    $returnValue['maintenanceCode'] = $maintenanceCode;
    $returnValue['errFound'] = $errFound;
    $returnValue['edtVar'] = $edtVar;
    $returnValue['errVar'] = $errVar;
    $returnValue['dtlCmt'] = $dtlCmt;
    $returnValue['hdrCmt'] = $hdrCmt;
    $returnValue['trlCmt'] = $trlCmt;
    return $returnValue;
}

// Check For Existence Of Vendor/Customer Item Comments
function Linked_OrderComments($order, $line, $rel = 000, $documentType = 'PO')
{
    global $i5Connect;
    $cmt = "";
    if (!$i5Connect) die("<br>Linked_OrderComments Connection Failed. Error number =" . i5_errno() . " msg=" . i5_errormsg());

    $pgmCall = array(
        array("Name" => "order", "IO" => I5_IN, "Type" => I5_TYPE_PACKED, "Length" => "8"),
        array("Name" => "line", "IO" => I5_IN, "Type" => I5_TYPE_PACKED, "Length" => "3"),
        array("Name" => "rel", "IO" => I5_IN, "Type" => I5_TYPE_PACKED, "Length" => "3"),
        array("Name" => "documentType", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "3"),
        array("Name" => "cmt", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"));

    $pgm = i5_program_prepare("HOEOCM_P", $pgmCall);
    if (!$pgm) {
        die("<br>Linked_OrderComments Program prepare errno=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = array(
        "order" => $order,
        "line" => $line,
        "rel" => $rel,
        "documentType" => $documentType,
        "cmt" => $cmt
    );

    $parmOut = array(
        "cmt" => "cmt"
    );
    $ret = i5_program_call($pgm, $parmIn, $parmOut);

    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br> Linked_OrderComments Program call errno=" . i5_errno() . " msg=" . i5_errormsg() . "Program:$pgm In:$parmIn");
    }

    return $cmt;
}

?>