<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound = $_GET['errFound'];
$wrnVar = $_GET['wrnVar'];

$fromWhs = (isset($_GET['fromWhs'])) ? strtoupper($_GET['fromWhs']) : '';
$fromStkr = (isset($_GET['fromStkr'])) ? strtoupper($_GET['fromStkr']) : '';
$toWhs = (isset($_GET['toWhs'])) ? strtoupper($_GET['toWhs']) : '';
$toStkr = (isset($_GET['toStkr'])) ? strtoupper($_GET['toStkr']) : '';

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Replenish Stock From Whs Maintenance";
$scriptName = "ReplenishStockFromWhsMaintain.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D&amp;fromWhs=" . urlencode(trim($fromWhs)) . "&amp;fromStkr=" . urlencode(trim($fromStkr)) . "&amp;toWhs=" . urlencode(trim($toWhs)) . "&amp;toStkr=" . urlencode(trim($toStkr));
$programName = "HIVRFW_W";

$backURL = $_SESSION[$fromURL];
if ($backURL == "" || $maintenanceCode == "D") {
    $backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=507";
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
    print "\n if (document.Chg.toWhs.value ==\"\" || ";
    print "\n     document.Chg.fromWhs.value ==\"\" )";
    print "\n {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (editZero(document.Chg.toWhs, 3, 0) && ";
    print "\n     editZero(document.Chg.fromWhs, 3, 0) && ";
    print "\n     editNum(document.Chg.priority, 2, 0)) ";
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
    $pageID = "REPLENISHSTOCKMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";

    $stmtSQL = "";
    if ($maintenanceCode == "A") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL .= " Select * ";
        $stmtSQL .= " From IVRSFW ";
        $stmtSQL .= " Where RSTWHS=$toWhs and RSTSKR='$toStkr' and RSFWHS=$fromWhs and RSFSKR='$fromStkr'";
    }
    require 'stmtSQLEnd.php';

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    // Program Option Security
    $hivrfw_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
    $sec_01 = $hivrfw_OPT['sec_01'];
    $sec_02 = $hivrfw_OPT['sec_02'];
    $sec_03 = $hivrfw_OPT['sec_03'];
    $sec_04 = $hivrfw_OPT['sec_04'];
    require_once 'MaintainTop.php';

    print $hrTagAttr;
    require_once 'RequiredField.php';
    require_once 'ErrorDisplay.php';

    if ($errFound != "" || $maintenanceCode == "A") {
        if ($errFound == "" && $maintenanceCode == "A") {
            $focusField = "toWhs";
            $edtVar = "";
        } elseif ($errFound != "") {
            $focusField = "";
            $edtVar = EdtVarErr($profileHandle, $edtVar);
            $errVar = ErrVarErr($profileHandle, $errVar);
            $Err_RSTWHS = DecatErr_Field("@@twhs", "toWhs");
            $Err_RSTSKR = DecatErr_Field("@@tskr", "toStkr");
            $Err_RSFWHS = DecatErr_Field("@@fwhs", "fromWhs");
            $Err_RSFSKR = DecatErr_Field("@@fskr", "fromStkr");
            $Err_RSPRTY = DecatErr_Field("@@prty", "priority");
            $errFound = "";
        }

        $row['RSTWHS'] = Decat_Field("@@twhs", $edtVar);
        $row['RSTSKR'] = Decat_Field("@@tskr", $edtVar);
        $row['RSFWHS'] = Decat_Field("@@fwhs", $edtVar);
        $row['RSFSKR'] = Decat_Field("@@fskr", $edtVar);
        $row['RSPRTY'] = Decat_Field("@@prty", $edtVar);
    } else {
        $focusField = "toWhs";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";

	$disabled = '';
	$displayOnly = '';
	if ($maintenanceCode=="C" || $maintenanceCode=="D") {
		$disabled = 'DISABLED';
		$displayOnly = 'Y';
	}
	
    $fieldDesc = RetValue("WHWHS=$row[RSTWHS]", "HDWHSM", "WHWHNM");
	if ($disabled == '') {
		$textOvr = SetTextOvr($Err_RSTWHS);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>To Warehouse</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"toWhs\" value=\"" . rtrim($row['RSTWHS']) . "\" size=\"5\" maxlength=\"3\"" . $disabled . ">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=toWhs&amp;fldDesc=toWhsDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"toWhsDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_RSTWHS);
	} else {
		print "\n <tr><td class=\"dsphdr\">To Warehouse</td><td class=\"inputalph\">" . $fieldDesc . "  [" . $row[RSTWHS] . "]</td> ";
		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"toWhs\" value=\"" . rtrim($row['RSTWHS']) . "\"></tr>";
	}

	if ($disabled == '') {
		Build_Fld_Entry("To Stockroom", "toStkr", "inputalph", "", "RSTSKR", $row[RSTSKR], $Err_RSTSKR, "5", "3", "", "", "");
	} else {
		print "\n <tr><td class=\"dsphdr\">To Stockroom</td><td class=\"inputalph\">" . $row[RSTSKR] . "</td> ";
		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"toStkr\" value=\"" . rtrim($row['RSTSKR']) . "\"></tr>";
	}
		
    $fieldDesc = RetValue("WHWHS=$row[RSFWHS]", "HDWHSM", "WHWHNM");
	if ($disabled == '') {
		$textOvr = SetTextOvr($Err_RSFWHS);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>From Warehouse</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"fromWhs\" value=\"" . rtrim($row['RSFWHS']) . "\" size=\"5\" maxlength=\"3\"" . $disabled . ">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=fromWhs&amp;fldDesc=fromWhsDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"fromWhsDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_RSFWHS);
	} else {
		print "\n <tr><td class=\"dsphdr\">From Warehouse</td><td class=\"inputalph\">" . $fieldDesc . "  [" . $row[RSFWHS] . "]</td> ";
		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"fromWhs\" value=\"" . rtrim($row['RSFWHS']) . "\"></tr>";
	}

	if ($disabled == '') {
		Build_Fld_Entry("From Stockroom", "fromStkr", "inputalph", "", "RSFSKR", $row[RSFSKR], $Err_RSFSKR, "5", "3", "", "", "");
	} else {
		print "\n <tr><td class=\"dsphdr\">From Stockroom</td><td class=\"inputalph\">" . $row[RSFSKR] . "</td> ";
		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"fromStkr\" value=\"" . rtrim($row['RSFSKR']) . "\"></tr>";
	}
	
    Build_Fld_Entry("Priority", "priority", "inputnmbr", "", "RSPRTY", $row[RSPRTY], $Err_RSPRTY, "5", "2", "", "", "");

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
        $_POST['toWhs'] = (isset($_GET['toWhs'])) ? strtoupper($_GET['toWhs']) : $_POST['toWhs'];
        $_POST['toStkr'] = (isset($_GET['toStkr'])) ? strtoupper($_GET['toStkr']) : $_POST['toStkr'];
        $_POST['fromWhs'] = (isset($_GET['fromWhs'])) ? strtoupper($_GET['fromWhs']) : $_POST['fromWhs'];
        $_POST['fromStkr'] = (isset($_GET['fromStkr'])) ? strtoupper($_GET['fromStkr']) : $_POST['fromStkr'];
    }

    $edtVar = "";
    Concat_Field("@@twhs", strtoupper($_POST['toWhs']));
    Concat_Field("@@tskr", strtoupper($_POST['toStkr']));
    Concat_Field("@@fwhs", strtoupper($_POST['fromWhs']));
    Concat_Field("@@fskr", strtoupper($_POST['fromStkr']));
    Concat_Field("@@prty", strtoupper($_POST['priority']));
    $edtVar .= "}{";

    $returnValue = Maintain_Edit("HIVRFW_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar);
    $maintenanceCode = $returnValue['maintenanceCode'];
    $errFound = $returnValue['errFound'];
    $edtVar = $returnValue['edtVar'];
    $errVar = $returnValue['errVar'];
    $wrnVar = $returnValue['wrnVar'];

    if (($errFound == "" || $maintenanceCode == "D")) {
        if ($errFound == "") {
            $confMessage = Format_ConfMsg_Desc($maintenanceCode, "To Whs [{$_POST['toWhs']}]   To Stockroom [{$_POST['toStkr']}]", "", "", "", "", "");
        } else {
            $Err_RSTWHS = DecatErr_Field("@@po@@", "poNumber");
            $confMessage = Format_ConfMsg_Desc("To Whs [{$_POST['toWhs']}]   To Stockroom [{$_POST['toStkr']}]", "", "", "", "", "<br>$Err_RSTWHS", "");
        }
        print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
    } else {
        $poNumber = Decat_Field("@@po@@", $edtVar);
        EdtVarErr($profileHandle, $edtVar);
        ErrVarErr($profileHandle, $errVar);
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;fromWhs=" . urlencode(trim($poNumber)) . "&amp;fromStkr=" . urlencode(trim($_POST[itemNumber])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;newRow=" . urlencode(trim($newRow)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
    }
}

?>