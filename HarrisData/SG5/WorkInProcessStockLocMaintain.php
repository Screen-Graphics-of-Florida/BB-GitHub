<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound = $_GET['errFound'];
$wrnVar = $_GET['wrnVar'];

$plantNumber = (isset($_GET['plantNumber'])) ? strtoupper($_GET['plantNumber']) : '';
$mfgOrder = (isset($_GET['mfgOrder'])) ? strtoupper($_GET['mfgOrder']) : '';
$seqNumber = (isset($_GET['seqNumber'])) ? strtoupper($_GET['seqNumber']) : '';
$stockLocId = (isset($_GET['stockLocId'])) ? strtoupper($_GET['stockLocId']) : '';

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "WIP Stock Location Maintenance";
$scriptName = "WorkInProcessStockLocMaintain.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D&amp;plantNumber=" . urlencode(trim($plantNumber)) . "&amp;mfgOrder=" . urlencode(trim($mfgOrder)) . "&amp;seqNumber=" . urlencode(trim($seqNumber)) . "&amp;stockLocId=" . urlencode(trim($stockLocId));
$programName = "HIVWSL_W";

$backURL = $_SESSION[$fromURL];
//if ($backURL == "" || $maintenanceCode == "D") {
$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=508";
//}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth == "F") {
    require_once 'ProgSecurityError.php';
    exit();
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

    print "\n function autotab(current,to){";
    print "\n     if (current.getAttribute && ";
    print "\n         current.value.length==current.getAttribute(\"maxlength\")) {";
    print "\n         to.focus()";
    print "\n     }";
    print "\n }";

    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.seqNumber.value ==\"\" || ";
    print "\n     document.Chg.mfgOrder.value ==\"\" || ";
    print "\n     document.Chg.plantNumber.value ==\"\" ||";
    if ($maintenanceCode == "C" || $maintenanceCode == "D") {
        print "\n     document.Chg.stockLocId.value ==\"\" ";
    } else {
        print "\n     (document.Chg.stockroom.value ==\"\" && document.Chg.aisle.value ==\"\" && document.Chg.stkLoc.value ==\"\")";
    }
    print "\n     )";
    print "\n {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (editZero(document.Chg.seqNumber, 3, 0) && ";
    print "\n     editZero(document.Chg.plantNumber, 3, 0) && ";
    if ($maintenanceCode == "C") {
        print "\n     editNum(document.Chg.adjustQty, 9, 4) ";
    } else {
        print "\n     editNum(document.Chg.quantity, 9, 4) ";
    }
    print "\n     )";
    print "\n return true;";
    print "\n }";

    print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";

    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "WIPSTOCKLOCMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";

    $stmtSQL = "";
    if ($maintenanceCode == "A") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL .= " Select * ";
        $stmtSQL .= " From HDWPSL";
        $stmtSQL .= " Where WSPLT=$plantNumber and WSORD='$mfgOrder' and WSSEQN=$seqNumber and WSSTID=$stockLocId";
    }
    require 'stmtSQLEnd.php';

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    // Program Option Security
    $hivwsl = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
    $sec_01 = $hivwsl['sec_01'];
    $sec_02 = $hivwsl['sec_02'];
    $sec_03 = $hivwsl['sec_03'];
    $sec_04 = $hivwsl['sec_04'];
    require_once 'MaintainTop.php';

    print $hrTagAttr;
    require_once 'RequiredField.php';
    require_once 'ErrorDisplay.php';

    if ($errFound != "" || $maintenanceCode == "A") {
        if ($errFound == "" && $maintenanceCode == "A") {
            if ($plantNumber == "") {
                $dftPlant = RtvDftPlant();
                $plantNumber = $dftPlant['dftPltNumber'];
                $focusField = "mfgOrder";
            } else {
                $focusField = "plantNumber";
            }
            $edtVar = "";
        } elseif ($errFound != "") {
            $focusField = "";
            $edtVar = EdtVarErr($profileHandle, $edtVar);
            $errVar = ErrVarErr($profileHandle, $errVar);
            $Err_WSSEQN = DecatErr_Field("@@seqn", "seqNumber");
            $Err_WSSTID = DecatErr_Field("@@stid", "stockLocId");
            $Err_WSPLT = DecatErr_Field("@@plt@", "plantNumber");
            $Err_WSORD = DecatErr_Field("@@ord@", "mfgOrder");
            $Err_WSQTY = DecatErr_Field("@@qty@", "quantity");
            $Err_WSCMNT = DecatErr_Field("@@cmnt", "comment");
            $errFound = "";
        }

        $row['WSSEQN'] = Decat_Field("@@seqn", $edtVar);
        $row['WSSTID'] = Decat_Field("@@stid", $edtVar);
        $stkr = Decat_Field("@@stkr", $edtVar);
        $aile = Decat_Field("@@aile", $edtVar);
        $sloc = Decat_Field("@@sloc", $edtVar);
        $row['WSPLT'] = Decat_Field("@@plt@", $edtVar);
        $row['WSORD'] = Decat_Field("@@ord@", $edtVar);
        $row['WSQTY'] = Decat_Field("@@qty@", $edtVar);
        $row['WSCMNT'] = Decat_Field("@@cmnt", $edtVar);
        if ($errFound == "" && $maintenanceCode == "A" && $plantNumber > "0") {
            $row['WSPLT'] = $plantNumber;
            $row['WSORD'] = $mfgOrder;
            $row['WSSEQN'] = $seqNumber;
            if ($mfgOrder != '') {
                $focusField = "stockroom";
            }
        }
    } elseif ($maintenanceCode == "Z") {
        $row['WSPLT'] = $plantNumber;
        $row['WSORD'] = $mfgOrder;
        $focusField = 'seqNumber';
    } else {
        $focusField = 'adjustQty';
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";

    $disabled = true;
    if ($maintenanceCode == "C" || $maintenanceCode == "D") {
        $disabled = false;
    }

    $fieldDesc = RetValue("PLPLNT=$row[WSPLT]", "HDPLNT", "PLNAME");
    if ($disabled) {
        $textOvr = SetTextOvr($Err_WSPLT);
        print "\n <tr><td class=\"dsphdr\"><span $textOvr>Plant Number</span></td> ";
        print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"plantNumber\" value=\"" . rtrim($row['WSPLT']) . "\" size=\"5\" maxlength=\"3\"" . $disabled . ">";
        print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=plantNumber&amp;fldDesc=plantNumberDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
        print "\n     <span class=\"dspdesc\" id=\"plantNumberDesc\">$fieldDesc</span></td>";
        print "\n </tr> ";
        DspErrMsg($Err_WSPLT);
    } else {
        print "\n <tr><td class=\"dsphdr\">Plant Number</td><td class=\"inputalph\">" . $fieldDesc . "  [" . $row[WSPLT] . "]</td> ";
        print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"plantNumber\" value=\"" . rtrim($row['WSPLT']) . "\"></tr>";
    }

    if ($disabled) {
        $textOvr = SetTextOvr($Err_WSORD);
        print "\n <tr><td class=\"dsphdr\">Order Number</td>";
        print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"mfgOrder\"  value=\"" . rtrim($row['WSORD']) . "\"size=\"9\" maxlength=\"9\">";
        print "\n                             <a href=\"{$homeURL}{$phpPath}MfgOrderSearch.php{$genericVarBase}&amp;forPlant=0&amp;docName=Chg&amp;fldorder=mfgOrder\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage </a></td> ";
        print "\n </tr> ";
        DspErrMsg($Err_WSORD);
    } else {
        print "\n <tr><td class=\"dsphdr\">Order Number</td><td class=\"inputalph\">" . $row[WSORD] . "</td> ";
        print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"mfgOrder\" value=\"" . rtrim($row['WSORD']) . "\"></tr>";
    }

    $fieldDesc = RetValue("WHWHS=$row[WSSEQN]", "HDWHSM", "WHWHNM");
    if ($disabled) {
        Build_Fld_Entry("Routing Sequence", "seqNumber", "inputalph", "", "WSSEQN", $row['WSSEQN'], $Err_WSSEQN, "3", "3", "Y", "", "");
    } else {
        print "\n <tr><td class=\"dsphdr\">Routing Sequence</td><td class=\"inputalph\">" . $row[WSSEQN] . "</td> ";
        print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"seqNumber\" value=\"" . rtrim($row['WSSEQN']) . "\"></tr>";
    }

    if ($disabled) {
        $textOvr = SetTextOvr($Err_WSSTID);
        print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Stock Location</span></td> ";
        print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"stockroom\" value=\"" . $stkr . "\" size=\"3\" onKeyup=\"autotab(this, document.Chg.aisle)\" maxlength=\"3\"> ";
        print "\n                                     <input type=\"text\" name=\"aisle\" value=\"" . $aile . "\" size=\"4\" onKeyup=\"autotab(this, document.Chg.stkLoc)\" maxlength=\"4\"> ";
        print "\n                                     <input type=\"text\" name=\"stkLoc\" value=\"" . $sloc . "\" size=\"8\" onKeyup=\"autotab(this, document.Chg.quantity)\" maxlength=\"8\"> ";
        print "\n                                     <input type=\"hidden\" name=\"stockLocId\" value=\"" . rtrim($row['WSSTID']) . "\"> ";
        print "\n                                     <a href=\"{$homeURL}{$phpPath}StockLocSearch.php{$genericVarBase}&amp;docName=Chg&amp;forWhs=&amp;fldStkr=stockroom&amp;fldAisle=aisle&amp;fldLoc=stkLoc&amp;fldStkID=stockLocId\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
        print "\n             </td>";
        print "\n         </tr> ";
        DspErrMsg($Err_WSSTID);
    } else {
        $stockroom = RetValue("SLSTID=$row[WSSTID]", "HDSTLC", "SLSTKR");
        $aisle = RetValue("SLSTID=$row[WSSTID]", "HDSTLC", "SLAILE");
        $stockloc = RetValue("SLSTID=$row[WSSTID]", "HDSTLC", "SLSLOC");
        print "\n <tr><td class=\"dsphdr\">Stock Location</td><td class=\"inputalph\">" . $stockroom . ' - ' . $aisle . ' - ' . $stockloc . "</td> ";
        print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"stockLocId\" value=\"" . rtrim($row['WSSTID']) . "\"></tr>";
    }

    if ($disabled) {
        Build_Fld_Entry("Quantity", "quantity", "inputnmbr", "", "WSQTY", $row[WSQTY], $Err_WSQTY, "15", "15", "", "", "");
    } else {
        $F_WSQTY = Format_Nbr($row[WSQTY], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n <tr><td class=\"dsphdr\">Quantity</td><td class=\"inputalph\">" . $F_WSQTY . "</td></tr>";
        print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"quantity\" value=\"" . $row[WSQTY] . "\"></tr>";
        Build_Fld_Entry("Adjust Quantity", "adjustQty", "inputnmbr", "", "", "", "", "15", "15", "", "", "");
    }
    Build_Fld_Entry("Comment", "comment", "inputalph", "", "WSCMNT", $row[WSCMNT], $Err_WSCMNT, "100", "", "", "", "");

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
        $_POST['seqNumber'] = (isset($_GET['seqNumber'])) ? strtoupper($_GET['seqNumber']) : $_POST['seqNumber'];
        $stkLocId = (isset($_GET['stockLocId'])) ? strtoupper($_GET['stockLocId']) : $_POST['stockLocId'];
        $_POST['plantNumber'] = (isset($_GET['plantNumber'])) ? strtoupper($_GET['plantNumber']) : $_POST['plantNumber'];
        $_POST['mfgOrder'] = (isset($_GET['mfgOrder'])) ? strtoupper($_GET['mfgOrder']) : $_POST['mfgOrder'];
    } elseif ($maintenanceCode == "C") {
        $stkLocId = (isset($_GET['stockLocId'])) ? strtoupper($_GET['stockLocId']) : $_POST['stockLocId'];
    } else {
        if ($_POST['stockLocId'] != "") {
            $stkLocId = $_POST['stockLocId'];
        } else {
            $where = "SLSTKR='" . strtoupper($_POST['stockroom']) . "' and SLAILE='" . strtoupper($_POST['aisle']) . "' and SLSLOC='" . strtoupper($_POST['stkLoc']) . "'";
            $stkLocId = RetValue($where, "HDSTLC", "SLSTID");
        }
    }

    $edtVar = "";
    Concat_Field("@@seqn", strtoupper($_POST['seqNumber']));
    Concat_Field("@@stid", $stkLocId);
    Concat_Field("@@stkr", strtoupper($_POST['stockroom']));
    Concat_Field("@@aile", strtoupper($_POST['aisle']));
    Concat_Field("@@sloc", strtoupper($_POST['stkLoc']));
    Concat_Field("@@plt@", strtoupper($_POST['plantNumber']));
    Concat_Field("@@ord@", strtoupper($_POST['mfgOrder']));
    $qty = ($_POST['adjustQty']) ? $_POST['quantity'] + $_POST['adjustQty'] : $_POST['quantity'];
    Concat_Field("@@qty@", $qty);
    Concat_Field("@@cmnt", $_POST['comment']);
    $edtVar .= "}{";
    //echo $edtVar;
    //exit();

    $returnValue = Maintain_Edit("HIVWSL_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar);
    $maintenanceCode = $returnValue['maintenanceCode'];
    $errFound = $returnValue['errFound'];
    $edtVar = $returnValue['edtVar'];
    $errVar = $returnValue['errVar'];
    $wrnVar = $returnValue['wrnVar'];

    if (($errFound == "" || $maintenanceCode == "D")) {
        if ($errFound == "") {
            $confMessage = Format_ConfMsg_Desc($maintenanceCode, "Plant [{$_POST['plantNumber']}] Order [{$_POST['mfgOrder']}] Seq [{$_POST['seqNumber']}]", "", "", "", "", "");
        } else {
            $Err_WSSEQN = DecatErr_Field("@@po@@", "poNumber");
            $confMessage = Format_ConfMsg_Desc("Plant [{$_POST['plantNumber']}] Order [{$_POST['mfgOrder']}] Seq [{$_POST['seqNumber']}]", "", "", "", "", "<br>$Err_WSSEQN", "");
        }
        print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
    } else {
        $poNumber = Decat_Field("@@po@@", $edtVar);
        EdtVarErr($profileHandle, $edtVar);
        ErrVarErr($profileHandle, $errVar);
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;plantNumber=" . urlencode(trim($poNumber)) . "&amp;mfgOrder=" . urlencode(trim($_POST[itemNumber])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;newRow=" . urlencode(trim($newRow)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
    }
}

?>