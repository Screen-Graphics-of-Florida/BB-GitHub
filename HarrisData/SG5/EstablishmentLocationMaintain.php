<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromELID = $_GET ['fromELID'];
$fKey1 = $_GET ['fKey1'];
$fVal1 = $_GET ['fVal1'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$fDsc1 = RetValue("ESESID=$fVal1", "PEESTB", "ESNAME");
if ($maintenanceCode != "A") {
    $fVal1 = RetValue("ELELID=$fromELID", "PEESLC", "ELESID");
    $fDsc1 = RetValue("ESESID={$fVal1}", "PEESTB", "ESNAME");
    $fKey1 = 'ELESID';
}

$page_title = "Establishment Location Maintenance";
$scriptName = "EstablishmentLocationMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromELID=" . urlencode(trim($fromELID)) . "&amp;fKey1=" . urlencode(trim($fKey1)) . "&amp;fVal1=" . urlencode(trim($fVal1)) . "&amp;fDsc1=" . urlencode(trim($fDsc1));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "ESTABLISH";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=513";
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
        $lastUpdatedCurrent = RetValue("ELELID={$_POST [ELELID]}", "PEESLC", "ELLUPD");
        if ($lastUpdatedCurrent != $_POST [ELLUPD]) {
            $errMsg = "Row has been previously updated";
        }
    } elseif ($maintenanceCode == "D") {
        $desc = RetValue("ELELID={$fromELID}", "PEESLC", "ELELID");

        $stmtSQL = " Delete From PEESLC Where ELELID=" . $fromELID;
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $desc, "", "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        exit ();
    }

    if (!$errMsg && $maintenanceCode != "D") {
        $_POST[ELCODE] = strtoupper($_POST[ELCODE]);

        $fieldDesc = RetValue("CFCOMP=$_POST[ELCO] and CFFACL=0", "HRCOFC", "CFNAME");
        if ($fieldDesc == '') {
            $Err_ELCO = "Invalid Company Number";
            $errMsg = 'Please correct all errors';
        }

        if ($_POST[ELCODE] != '*ALL') {
            $locDesc = RetValue("ODCOMP=$_POST[ELCO] and ODFACL=0 and ODTYPE='O' and ODCODE='{$_POST[ELCODE]}'", "PECODE", "ODDESC");
            if ($locDesc == '') {
                $Err_ELCODE = "Invalid Location";
                $errMsg = 'Please correct all errors';
            }
        }

        if ($maintenanceCode == "A" && $_POST[ELCODE] != '*ALL') {
            $id = RetValue("ELELID=$_POST[ELELID] and ELCO=$_POST[ELCO] and ELCODE='{$_POST[ELCODE]}'", "PEESLC", "ELELID");
            if ($id > 0) {
                $Err_ELCO = "Row already exists for Company " . $_POST[ELCO] . " Location " . $_POST[ELCODE];
                $errMsg = 'Please correct all errors';
            }
        }
    }

    if (!$errMsg) {
        if ($maintenanceCode == "A" && $_POST[ELCODE] == '*ALL') {

            $stmtSQL = "Select ODCODE,ODDESC From PECODE 
                        Where ODCOMP=$_POST[ELCO] and ODFACL=0 and ODTYPE='O' and  
                        not exists (Select * From PEESLC 
                        Where ELESID={$fVal1} and ELCO={$_POST[ELCO]} and ELCODE=ODCODE)";
            $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));
            $startRow = 1;
            while ($data = db2_fetch_assoc($sqlResult, $startRow)) {
                if ($startRow == 1) {
                    $stmtSQL = " Insert Into PEESLC (ELESID, ELCO, ELCODE, ELDESC)";
                    $stmtSQL .= " Values ({$fVal1},{$_POST ['ELCO']},'{$data ['ODCODE']}','{$data ['ODDESC']}') ";
                } else {
                    $stmtSQL .= " ,({$fVal1},{$_POST ['ELCO']},'{$data ['ODCODE']}','{$data ['ODDESC']}') ";
                }
                $startRow++;
            }
        } elseif ($maintenanceCode == "A") {
            $stmtSQL = " Insert Into PEESLC (ELESID, ELCO, ELCODE, ELDESC)";
            $stmtSQL .= " Values ({$fVal1},{$_POST ['ELCO']},'{$_POST ['ELCODE']}','{$locDesc}') ";
        } else {
            $stmtSQL = " Update PEESLC set ELCO={$_POST ['ELCO']},ELCODE='{$_POST ['ELCODE']}',ELDESC='{$locDesc}',ELLUPD=CURRENT_TIMESTAMP";
            $stmtSQL .= " Where ELELID={$_POST['ELELID']} ";
        }
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
        // If row not added, set identity column and try again
        if (!$status && $maintenanceCode == "A") {
            Check_Identity_Column('PEESLC', 'ELELID', $stmtSQL);
        }

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $_POST ['ELCODE'], "", "", "", "", "");
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
    print "\n if (editNum(document.Chg.ELCO, 2, 0)) ";
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
    $pageID = "ESTABLISHMENTLOCMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    $stmtSQL = "";
    if ($maintenanceCode == "A") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL = " Select *  From PEESLC Where ELELID=$fromELID ";
    }
    require 'stmtSQLEnd.php';

    // Program Option Security
    $prog_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
    $sec_01 = $prog_OPT ['sec_01'];
    $sec_02 = $prog_OPT ['sec_02'];
    $sec_03 = $prog_OPT ['sec_03'];
    $sec_04 = $prog_OPT ['sec_04'];
    require_once 'MaintainTop.php';
    print "<table class=\"contenttable\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" summary=\"contenttable\"><tr><td class='hdrtitl'>Establishment Location ID:</td><td class='hdrdata'>{$fDsc1} &nbsp; [{$fVal1}]</td></tr></table>";
    print $hrTagAttr;
    require_once 'RequiredField.php';
    require_once 'ErrorDisplay.php';
    if ($errMsg != '') {
        print "\n <span class=\"error\" $textOvr>$errMsg</span>";
    }

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    if ($maintenanceCode == "A" || $errMsg) {
        $row [ELELID] = $_POST ['ELELID'];
        $row [ELCO] = $_POST ['ELCO'];
        $row [ELCODE] = $_POST ['ELCODE'];
        $focusField = "ELCO";
    } else {
        $focusField = "ELCO";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";
    print "\n <tr><td><input type=\"hidden\" name=\"ELLUPD\" value=\"" . rtrim($row ['ELLUPD']) . "\"></td></tr> ";
    print "\n <tr><td><input type=\"hidden\" name=\"ELELID\" value=\"" . rtrim($row ['ELELID']) . "\"></td></tr> ";

    $fieldDesc = RetValue("CFCOMP=$row[ELCO] and CFFACL=0", "HRCOFC", "CFNAME");
    $textOvr = SetTextOvr($Err_ELCO);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company Number</span></td>";
    print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"ELCO\" value=\"" . rtrim($row['ELCO']) . "\" size=\"2\" maxlength=\"2\">$reqFieldChar";
    print "\n                         <a href=\"{$homeURL}{$phpPath}HRCompanySearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=ELCO&amp;fldDesc=coDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
    print "\n <span class=\"dspdesc\" id=\"coDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_ELCO);

    $fieldDesc = RetValue("ODCOMP=$row[ELCO] and ODFACL=0 and ODTYPE='O' and ODCODE='{$row ['ELCODE']}'", "PECODE", "ODDESC");
    $textOvr = SetTextOvr($Err_ELCODE);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Location</span></td>";
    print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"ELCODE\" value=\"" . rtrim($row ['ELCODE']) . "\" size=\"4\" maxlength=\"4\">";
    print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=ELCODE&amp;fldType=O&amp;fldDesc=locName\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage </a>";
    print "\n     <span class=\"dspdesc\" id=\"locName\">$fieldDesc </span>  <span class=\"dsphdr\">(Enter *ALL to add all locations for the Company Number)</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_ELCODE);

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