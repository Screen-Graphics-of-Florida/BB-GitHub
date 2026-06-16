<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromPBID = $_GET ['fromPBID'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Pay Bands Maintenance";
$scriptName = "PayBandsMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromPBID=" . urlencode(trim($fromPBID));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=511";
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
        $lastUpdatedCurrent = RetValue("PBID={$fromPBID}", "PEPYBD", "PBLUPD");
        if ($lastUpdatedCurrent != $_POST ['PBLUPD']) {
            $errMsg = "Row has been previously updated";
        }
    } elseif ($maintenanceCode == "D") {
        $desc = RetValue("PBID={$fromPBID}", "PEPYBD", "PBCD");

        $stmtSQL = " Delete From PEPYBD Where PBID=" . $fromPBID;
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $desc, "", "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        exit ();
    }

    if (!$errMsg && $maintenanceCode != "D") {
        $effDate = ($_POST ['PBEFDT'] > 0) ? "'" . Date_MDY_ISO($_POST ['PBEFDT']) . "'" : null;
        $expDate = ($_POST ['PBEXDT'] > 0) ? "'" . Date_MDY_ISO($_POST ['PBEXDT']) . "'" : null;

        if (!is_null($effDate) && !is_null($expDate) && $effDate > $expDate) {
            $Err_PBEFDT = "Effective Date must be less than Expire Date";
            $errMsg = 'Please correct all errors';
        }

        $pbid = ($maintenanceCode == "A") ? null : $_POST['PBID'];
        $dupErr = dupDates($pbid, $_POST ['PBCD'], $effDate, $expDate);
        if ($dupErr) {
            $Err_PBEFDT = "Duplicate Effective/Expire dates found for Code {$_POST ['PBCD']}";
            $errMsg = 'Please correct all errors';
        }

        if ($_POST ['PBMINW'] > $_POST ['PBMAXW']) {
            $Err_PBMINW = "Mininum Federal Wage cannot be greater than Maxinum Federal Wage";
            $errMsg = 'Please correct all errors';
        }
    }

    if (!$errMsg) {
        if ($maintenanceCode == "A") {
            $stmtSQL = " Insert Into PEPYBD (PBCD, PBDESC, PBEFDT, PBEXDT, PBMINW, PBMAXW, PBALTKY)";
            $stmtSQL .= " Values ({$_POST ['PBCD']},'{$_POST ['PBDESC']}',{$effDate},{$expDate},{$_POST ['PBMINW']},{$_POST ['PBMAXW']},'{$_POST ['PBALTKY']}') ";
        } else {
            $effDate = (is_null($effDate)) ? 'NULL' : $effDate;
            $expDate = (is_null($expDate)) ? 'NULL' : $expDate;
            $stmtSQL = " Update PEPYBD set PBCD={$_POST ['PBCD']},PBDESC='{$_POST ['PBDESC']}',PBEFDT={$effDate},PBEXDT={$expDate},PBMINW={$_POST ['PBMINW']},PBMAXW={$_POST ['PBMAXW']},PBALTKY='{$_POST ['PBALTKY']}',PBLUPD=CURRENT_TIMESTAMP";
            $stmtSQL .= " Where PBID={$_POST['PBID']} ";
        }
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        // If row not added, set identity column and try again
        if (!$status && $maintenanceCode == "A") {
            Check_Identity_Column('PEPYBD', 'PBID', $stmtSQL);
        }

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, "Code", $_POST ['PBCD'], $_POST ['PBDESC'], "", "", "");
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
    print "\n if (document.Chg.PBCD.value ==\"\" || ";
    print "\n     document.Chg.PBDESC.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (editdate(document.Chg.PBEFDT) && ";
    print "\n     editdate(document.Chg.PBEXDT)) ";
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
    $pageID = "PAYBANDSMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    $stmtSQL = "";
    if ($maintenanceCode == "A") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL = " Select *  From PEPYBD Where PBID=$fromPBID ";
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
        $row [PBCD] = $_POST ['PBCD'];
        $row [PBDESC] = $_POST ['PBDESC'];
        $row [PBEFDT] = $_POST ['PBEFDT'];
        $row [PBEXDT] = $_POST ['PBEXDT'];
        $row [PBMINW] = $_POST ['PBMINW'];
        $row [PBMAXW] = $_POST ['PBMAXW'];
        $row [PBALTKY] = $_POST ['PBALTKY'];
        $focusField = "PBCD";
    } else {
        $row [PBEFDT] = ($row [PBEFDT] == '0001-01-01') ? '' : Date_ISO_MDY($row ['PBEFDT']);
        $row [PBEXDT] = ($row [PBEXDT] == '0001-01-01') ? '' : Date_ISO_MDY($row ['PBEXDT']);
        $focusField = "PBCD";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";
    print "\n <tr><td><input type=\"hidden\" name=\"PBLUPD\" value=\"" . rtrim($row ['PBLUPD']) . "\"></td></tr> ";
    print "\n <tr><td><input type=\"hidden\" name=\"PBID\" value=\"" . rtrim($row ['PBID']) . "\"></td></tr> ";

    Build_Fld_Entry("Code", "PBCD", "inputnbr", "", "PBCD", $row [PBCD], $Err_PBCD, "2", "2", "Y", "", "");
    Build_Fld_Entry("Description", "PBDESC", "inputalph", "", "PBDESC", $row [PBDESC], $Err_PBDESC, "64", "64", "Y", "", "");
    Build_Fld_Entry("Effective Date", "PBEFDT", "inputdate", "Date", "PBEFDT", $row [PBEFDT], $Err_PBEFDT, "6", "6", "", "", "");
    Build_Fld_Entry("Expire Date", "PBEXDT", "inputdate", "Date", "PBEXDT", $row [PBEXDT], $Err_PBEXDT, "6", "6", "", "", "");
    Build_Fld_Entry("Minimum Federal Wages", "PBMINW", "inputnbr", "", "PBMINW", $row [PBMINW], $Err_PBMINW, "15", "15", "", "", "");
    Build_Fld_Entry("Maximum Federal Wages", "PBMAXW", "inputnbr", "", "PBMAXW", $row [PBMAXW], $Err_PBMAXW, "15", "15", "", "", "");
    Build_Fld_Entry("Alternate Key", "PBALTKY", "inputalph", "", "PBALTKY", $row [PBALTKY], $Err_PBALTKY, "64", "128", "", "", "");

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

function dupDates($pbid, $pbcd, $effDate, $expDate)
{
    global $i5Connect;

    $pbidSel = (is_null($pbid)) ? '' : 'pbid<>' . $pbid . ' and ';
    $stmtSQL = "Select count(*) as CNT from PEPYBD where {$pbidSel} pbcd={$pbcd}";
    if (!is_null($effDate)) {
        $stmtSQL .= " and {$effDate} <= coalesce(PBEXDT,{$effDate})";
    }
    if (!is_null($expDate)) {
        $stmtSQL .= " and {$expDate} >= coalesce(PBEFDT,{$expDate})";
    }

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $dupRow = db2_fetch_assoc($sqlResult);

    return ($dupRow[CNT] > 0 ) ? true : false;
}

?>