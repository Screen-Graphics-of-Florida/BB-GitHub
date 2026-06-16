<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromRPID = $_GET ['fromRPID'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "EEO-1 Report Maintenance";
$scriptName = "EEO1ReportMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromRPID=" . urlencode(trim($fromRPID));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "EEOREPORT";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=514";
require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth == "F") {
    require_once 'ProgSecurityError.php';
    exit ();
}

if ($tag == "Edit_Data") {
    $errMsg = NULL;
    if ($maintenanceCode == "Z") {
        $maintenanceCode = "A";
    }

    if ($maintenanceCode == "A") {
    } elseif ($maintenanceCode == "C") {
        $lastUpdatedCurrent = RetValue("RPRPID={$_POST [RPRPID]}", "PEEORP", "RPLUPD");
        if ($lastUpdatedCurrent != $_POST [RPLUPD]) {
            $errMsg = "Row has been previously updated";
        }
    } elseif ($maintenanceCode == "D") {
        $desc = RetValue("RPRPID={$fromRPID}", "PEEORP", "RPRPID");

        $stmtSQL = " Delete From PEEORP Where RPRPID=" . $fromRPID;
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $desc, "", "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        exit ();
    }

    if (!$errMsg && $maintenanceCode != "D") {
        $fromDate = ($_POST ['RPFRDT'] > 0) ? "'" . Date_MDY_ISO($_POST ['RPFRDT']) . "'" : null;
        $fromYear = substr($fromDate, 1, 4);
        $toDate = ($_POST ['RPTODT'] > 0) ? "'" . Date_MDY_ISO($_POST ['RPTODT']) . "'" : null;
        $toYear = substr($toDate, 1, 4);
        if (is_null($fromDate) || is_null($toDate) || $fromYear != $toYear) {
            $Err_RPFRDT = "From/To Dates must be the same Year and Month-Day between 10-01 and 12-31";
            $errMsg = 'Please correct all errors';
        }

        if ($fromDate > $toDate) {
            $Err_RPFRDT = "From Date cannot be greater than To Date";
            $errMsg = 'Please correct all errors';
        }

        $fromMD = substr($fromDate, 6, 2) . substr($fromDate, 9, 2);
        if ($fromMD < 1001 || $fromMD > 1231) {
            $Err_RPFRDT = "From Month-Day must be between 10-01 and 12-31";
            $errMsg = 'Please correct all errors';
        }

        $toMD = substr($toDate, 6, 2) . substr($toDate, 9, 2);
        if ($toMD < 1001 || $toMD > 1231) {
            $Err_RPTODT = "To Month-Day must be between 10-01 and 12-31";
            $errMsg = 'Please correct all errors';
        }

        $eeoStatus = RetValue("ESESID=$_POST[RPESID]", "PEESTB", "ESSTAT");
        if ($eeoStatus == 0) {
            $Err_RPESID = "Invalid Establishment ID";
            $errMsg = 'Please correct all errors';
        }
    }

    if (!$errMsg) {
        if ($maintenanceCode == "A") {
            $stmtSQL = " Insert Into PEEORP (RPDESC, RPESID, RPSTAT, RPFRDT, RPTODT)";
            $stmtSQL .= " Values ('{$_POST ['RPDESC']}',{$_POST ['RPESID']},{$eeoStatus},{$fromDate},{$toDate}) ";
        } else {
            $stmtSQL = " Update PEEORP set RPDESC='{$_POST ['RPDESC']}',RPESID={$_POST ['RPESID']},RPSTAT={$eeoStatus},RPFRDT={$fromDate},RPTODT={$toDate},RPLUPD=CURRENT_TIMESTAMP";
            $stmtSQL .= " Where RPRPID={$_POST['RPRPID']} ";
        }
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        // If row not added, set identity column and try again
        if (!$status && $maintenanceCode == "A") {
            Check_Identity_Column('PEEORP', 'RPRPID', $stmtSQL);
        }

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $_POST ['RPDESC'], "", "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
    }
}

if ($tag == "MAINTAIN" || $errMsg) {
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

    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.RPESID.value ==\"\" || ";
    print "\n     document.Chg.RPFRDT.value ==\"\" || ";
    print "\n     document.Chg.RPTODT.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (editNum(document.Chg.RPESID, 7, 0) ";
    print "\n    && editdate(document.Chg.RPFRDT) ";
    print "\n    && editdate(document.Chg.RPTODT))";
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
    $pageID = "EEO1REPORTMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    $stmtSQL = "";
    if ($maintenanceCode == "A") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL = " Select *  From PEEORP Where RPRPID=$fromRPID ";
    }
    require 'stmtSQLEnd.php';

    // Program Option Security
    $prog_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
    $sec_01 = $prog_OPT ['sec_01'];
    $sec_02 = $prog_OPT ['sec_02'];
    $sec_03 = $prog_OPT ['sec_03'];
    $sec_04 = $prog_OPT ['sec_04'];
    require_once 'MaintainTop.php';
    print $hrTagAttr;
    require_once 'RequiredField.php';
    require_once 'ErrorDisplay.php';
    if ($errMsg != '') {
        print "\n <span class=\"error\" $textOvr>$errMsg</span>";
    }

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    if ($maintenanceCode == "A" || $errMsg) {
        $row [RPESID] = $_POST ['RPESID'];
        $row [RPDESC] = $_POST ['RPDESC'];
        $row [RPFRDT] = $_POST ['RPFRDT'];
        $row [RPTODT] = $_POST ['RPTODT'];
        $focusField = "RPDESC";
    } else {
        $row [RPFRDT] = ($row [RPFRDT] == '0001-01-01') ? '' : Date_ISO_MDY($row ['RPFRDT']);
        $row [RPTODT] = ($row [RPTODT] == '0001-01-01') ? '' : Date_ISO_MDY($row ['RPTODT']);
        $focusField = "RPDESC";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";
    print "\n <tr><td><input type=\"hidden\" name=\"RPLUPD\" value=\"" . rtrim($row ['RPLUPD']) . "\"></td></tr> ";
    print "\n <tr><td><input type=\"hidden\" name=\"RPRPID\" value=\"" . rtrim($row ['RPRPID']) . "\"></td></tr> ";

    Build_Fld_Entry("Description", "RPDESC", "inputalph", "", "RPDESC", $row [RPDESC], $Err_RPDESC, "64", "128", "Y", "", "");

    $fieldDesc = RetValue("ESESID={$row[RPESID]}", "PEESTB", "ESNAME");
    $textOvr = SetTextOvr($Err_RPESID);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Establishment ID</span></td>";
    print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"RPESID\" value=\"" . rtrim($row['RPESID']) . "\" size=\"6\" maxlength=\"7\"> $reqFieldChar";
    print "\n                         <a href=\"{$homeURL}{$phpPath}EstablishmentSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=RPESID&amp;fldDesc=estabDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
    print "\n <span class=\"dspdesc\" id=\"estabDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_RPESID);
    
    Build_Fld_Entry("From Date", "RPFRDT", "inputdate", "Date", "RPFRDT", $row [RPFRDT], $Err_RPFRDT, "6", "6", "Y", "", "");
    Build_Fld_Entry("To Date", "RPTODT", "inputdate", "Date", "RPTODT", $row [RPTODT], $Err_RPTODT, "6", "6", "Y", "", "");

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
    exit ();
}

?>