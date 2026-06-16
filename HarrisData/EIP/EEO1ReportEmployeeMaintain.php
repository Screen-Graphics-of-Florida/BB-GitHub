<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromREID = $_GET ['fromREID'];
$fromRPID = (isset($_GET ['fromRPID'])) ? $_GET ['fromRPID'] : $_GET ['fVal1'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "EEO-1 Report Employee Maintenance";
$scriptName = "EEO1ReportEmployeeMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromREID=" . urlencode(trim($fromREID)) . "&amp;fromRPID=" . urlencode(trim($fromRPID));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "EEOREPORT";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=515";
require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth == "F") {
    require_once 'ProgSecurityError.php';
    exit ();
}

// Get EEO-1 Report and Establishment
$stmtSQL = " Select * From PEEORP inner join PEESTB on RPESID=ESESID Where RPRPID=$fromRPID ";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$rowRPID = db2_fetch_assoc($sqlResult);

if ($tag == "Edit_Data") {
    $errMsg = NULL;
    if ($maintenanceCode == "Z") {
        $maintenanceCode = "A";
    }

    if ($maintenanceCode == "A") {
    } elseif ($maintenanceCode == "C") {
        $lastUpdatedCurrent = RetValue("REREID={$fromREID}", "PEEORE", "RELUPD");
        if ($lastUpdatedCurrent != $_POST ['RELUPD']) {
            $errMsg = "Row has been previously updated";
        }
    } elseif ($maintenanceCode == "D") {
        $name = RetValue("REREID={$fromREID}", "PEEORE", "RELNAM||', '||REFNAM");

        $stmtSQL = " Delete From PEEORE Where REREID=" . $fromREID;
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $name, "", "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;fKey1=RERPID&amp;fVal1={$fromRPID}&amp;fDsc1=" . urlencode(trim($rowRPID[RPDESC])) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        exit ();
    }

    if (!$errMsg && $maintenanceCode != "D") {
        $_POST[REGNDR] = strtoupper($_POST[REGNDR]);
        $incEEO = ($_POST[REINCT] == 'Y') ? 'Y' : 'N';

        $fieldDesc = RetValue("RPRPID=$_POST[RERPID]", "PEEORP", "RPDESC");
        if ($fieldDesc == '') {
            $Err_RERPID = "Invalid EEO-1 Report";
            $errMsg = 'Please correct all errors';
        }

        $emplId = RetValue("EMCOMP={$_POST[RECOMP]} and EMFACL={$_POST[REFACL]} and EMEMPL={$_POST[REEMPL]}", "HREMPL", "coalesce(EMEMID,'')");
        if ($emplId == '') {
            $Err_ESST = "Invalid Co/Fac Employee";
            $errMsg = 'Please correct all errors';
        } else {
            $_POST ['RELNAM'] = RetValue("EMCOMP={$_POST[RECOMP]} and EMFACL={$_POST[REFACL]} and EMEMPL={$_POST[REEMPL]}", "HREMPL", "EMLNAM");
            $_POST ['REFNAM'] = RetValue("EMCOMP={$_POST[RECOMP]} and EMFACL={$_POST[REFACL]} and EMEMPL={$_POST[REEMPL]}", "HREMPL", "EMFNAM");
        }
        $jobCat = floatval($_POST[REJBCT]);
        $fieldDesc = RetValue("ODFACL=0 and ODTYPE='Y' and ODCODE='{$jobCat}'", "PECODE", "max(ODDESC)");
        if ($fieldDesc == '') {
            $Err_REJBCT = "Invalid Job Category";
            $errMsg = 'Please correct all errors';
        }

        $flagDesc = RetValue("FLTYPE='GENDER' and FLVALU='$_POST[REGNDR]'", "SYFLAG", "FLDESC");
        if ($flagDesc == '') {
            $Err_REGNDR = "Invalid Gender";
            $errMsg = 'Please correct all errors';
        }

        $flagDesc = RetValue("FLTYPE='ETHNICID' and FLVALU='$_POST[REETCT]'", "SYFLAG", "FLDESC");
        if ($flagDesc == '') {
            $Err_REETCT = "Invalid Ethnic Category";
            $errMsg = 'Please correct all errors';
        }
    }

    if (!$errMsg) {
        $today = date("Y-m-d");
        // $pbcd = RetValue("({$_POST ['REW2B1']} >= PBMINW and {$_POST ['REW2B1']} <= PBMAXW or {$_POST ['REW2B1']} > PBMINW and PBMAXW=0) and '{$today}' >= coalesce(PBEFDT,'{$today}') and '{$today}' <=coalesce(PBEXDT,'{$today}')", "PEPYBD", "PBCD");
        if ($maintenanceCode == "A") {
            // $stmtSQL = " Insert Into PEEORE (RERPID,REJBCT,RECOMP,REFACL,REEMPL,REEMID,RELNAM,REFNAM,REGNDR,REETCT,REINCT,REPBID,REW2B1,REHRSW)";
            // $stmtSQL .= " Values ({$_POST[RERPID]},{$_POST ['REJBCT']},{$_POST ['RECOMP']},{$_POST ['REFACL']},{$_POST ['REEMPL']},{$emplId},'{$_POST ['RELNAM']}','{$_POST ['REFNAM']}','{$_POST ['REGNDR']}','{$_POST ['REETCT']}','{$incEEO}',{$pbcd},{$_POST ['REW2B1']},{$_POST ['REHRSW']}) ";
            $stmtSQL = " Insert Into PEEORE (RERPID,REJBCT,RECOMP,REFACL,REEMPL,REEMID,RELNAM,REFNAM,REGNDR,REETCT,REINCT)";
            $stmtSQL .= " Values ({$_POST[RERPID]},{$_POST ['REJBCT']},{$_POST ['RECOMP']},{$_POST ['REFACL']},{$_POST ['REEMPL']},{$emplId},'{$_POST ['RELNAM']}','{$_POST ['REFNAM']}','{$_POST ['REGNDR']}','{$_POST ['REETCT']}','{$incEEO}') ";
        } else {
            // $stmtSQL = " Update PEEORE set RERPID={$_POST[RERPID]},REJBCT={$_POST ['REJBCT']},RELNAM='{$_POST ['RELNAM']}',REFNAM='{$_POST ['REFNAM']}',REGNDR='{$_POST ['REGNDR']}',REETCT='{$_POST ['REETCT']}',REINCT='{$incEEO}',REPBID={$pbcd},REW2B1={$_POST ['REW2B1']},REHRSW={$_POST ['REHRSW']},RELUPD=CURRENT_TIMESTAMP";
            $stmtSQL = " Update PEEORE set RERPID={$_POST[RERPID]},REJBCT={$_POST ['REJBCT']},RELNAM='{$_POST ['RELNAM']}',REFNAM='{$_POST ['REFNAM']}',REGNDR='{$_POST ['REGNDR']}',REETCT='{$_POST ['REETCT']}',REINCT='{$incEEO}',RELUPD=CURRENT_TIMESTAMP";
            $stmtSQL .= " Where REREID={$_POST['REREID']} ";
        }
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        // If row not added, set identity column and try again
        if (!$status && $maintenanceCode == "A") {
            Check_Identity_Column('PEEORE', 'REREID', $stmtSQL);
        }

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $_POST ['RELNAM'] . ', ' . $_POST ['REFNAM'], "", "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;fKey1=RERPID&amp;fVal1={$fromRPID}&amp;fDsc1=" . urlencode(trim($rowRPID[RPDESC])) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
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
    print "\n if (document.Chg.RECOMP.value ==\"\" || ";
    print "\n     document.Chg.REFACL.value ==\"\" || ";
    print "\n     document.Chg.REEMPL.value ==\"\" || ";
    print "\n     document.Chg.RERPID.value ==\"\" || ";
    print "\n     document.Chg.REJBCT.value ==\"\" || ";
    print "\n     document.Chg.REGNDR.value ==\"\" || ";
    print "\n     document.Chg.REETCT.value ==\"\" || ";
    print "\n     document.Chg.REINCT.value ==\"\"";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (editNum(document.Chg.RECOMP, 2, 0) && ";
    print "\n     editNum(document.Chg.REFACL, 4, 0) && ";
    print "\n     editNum(document.Chg.REEMPL, 5, 0)) ";
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
    $pageID = "EEO1REPORTEMPLOYEEMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    $stmtSQL = "";
    if ($maintenanceCode == "A") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL = " Select PEEORE.*, 
                    Case When REJBCT=1.100 then '1.1' 
                         When REJBCT=1.200 then '1.2'
                         When REJBCT=2.000 then '2'
                         When REJBCT=3.000 then '3'
                         When REJBCT=4.000 then '4'
                         When REJBCT=5.000 then '5'
                         When REJBCT=6.000 then '6'
                         When REJBCT=7.000 then '7'
                         When REJBCT=8.000 then '8'
                         When REJBCT=9.000 then '9'
                         Else char(REJBCT) end as JOBCAT 
                    From PEEORE Where REREID=$fromREID ";
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
        $row [REREID] = $_POST ['REREID'];
        $row [RERPID] = $_POST ['RERPID'];
        $row [REJBCT] = $_POST ['REJBCT'];
        $row [RECOMP] = $_POST ['RECOMP'];
        $row [REFACL] = $_POST ['REFACL'];
        $row [REEMPL] = $_POST ['REEMPL'];
        $row [REFNAM] = $_POST ['REFNAM'];
        $row [REGNDR] = $_POST ['REGNDR'];
        $row [REETCT] = $_POST ['REETCT'];
        $row [REINCT] = $_POST ['REINCT'];
        $row [REPBID] = $_POST ['REPBID'];
        $row [REW2B1] = $_POST ['REW2B1'];
        $row [REHRSW] = $_POST ['REHRSW'];
        $focusField = "REJBCT";
        if ($maintenanceCode == 'A' && $row[RERPID] == '') {
            $row[RERPID] = $fromRPID;
        }
    } else {
        $focusField = "REJBCT";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";
    print "\n <tr><td><input type=\"hidden\" name=\"RELUPD\" value=\"" . rtrim($row ['RELUPD']) . "\"></td></tr> ";
    print "\n <tr><td><input type=\"hidden\" name=\"REREID\" value=\"" . rtrim($row ['REREID']) . "\"></td></tr> ";

    $fieldDesc = RetValue("RPRPID=$row[RERPID]", "PEEORP", "RPDESC");
    $textOvr = SetTextOvr($Err_RERPID);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>EEO-1 Report</span></td> ";
    print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"RERPID\" value=\"" . rtrim($row['RERPID']) . "\" size=\"3\" maxlength=\"6\">";
    print "\n                             <a href=\"{$homeURL}{$phpPath}EEO1ReportSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=RERPID&amp;fldDesc=RERPIDDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
    print "\n     <span class=\"dspdesc\" id=\"RERPIDDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_RERPID);

    if (is_null($row['RECOMP']) || trim($row['RECOMP']) == "") {
        $row['RECOMP'] = 0;
    }
    if (is_null($row['REFACL']) || trim($row['REFACL']) == "") {
        $row['REFACL'] = 0;
    }
    $coFacDesc = RetValue("CFCOMP=$row[RECOMP] and CFFACL=$row[REFACL]", "HRCOFC", "CFNAME");
    $textOvr = SetTextOvr($Err_RECOMP);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company/Facility</span></td> ";
    if ($maintenanceCode == "A" || $maintenanceCode == "Z") {
        print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"RECOMP\" value=\"" . rtrim($row['RECOMP']) . "\" size=\"1\" maxlength=\"2\"> / <input type=\"text\"   name=\"REFACL\" value=\"" . rtrim($row['REFACL']) . "\" size=\"1\" maxlength=\"4\">";
        print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldEmpl=REEMPL&amp;fldEmplName=emplDesc&amp;fldCo=RECOMP&amp;fldFacl=REFACL&amp;fldCoName=RECOMPDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
        print "\n     <span class=\"dspdesc\" id=\"RECOMPDesc\">$coFacDesc</span></td>";
    } else {
        print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"RECOMP\" value=\"" . rtrim($row['RECOMP']) . "\">$row[RECOMP] / <input type=\"hidden\"   name=\"REFACL\" value=\"" . rtrim($row['REFACL']) . "\">$row[REFACL]";
        print "\n     <span class=\"dspdesc\">$coFacDesc</span></td>";
    }
    print "\n </tr> ";
    DspErrMsg($Err_RECOMP);


    $F_Name = Format_EmplName($row[REFNAM], $row[RELNAM], "", "", "", "D");
    $textOvr = SetTextOvr($Err_RECOMP);
    $textOvr = SetTextOvr($Err_REEMPL);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Employee</span></td>";
    if ($maintenanceCode == "A" || $maintenanceCode == "Z") {
        print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"REEMPL\" value=\"" . rtrim($row['REEMPL']) . "\" size=\"3\" maxlength=\"5\"> ";
        print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldEmpl=REEMPL&amp;fldEmplName=emplDesc&amp;fldCo=RECOMP&amp;fldFacl=REFACL&amp;fldCoName=RECOMPDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
        print "\n     <span class=\"dspdesc\" id=\"emplDesc\">$F_Name</span></td>";
    } else {
        print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"REEMPL\" value=\"" . rtrim($row['REEMPL']) . "\">$row[REEMPL]";
        print "\n     <span class=\"dspdesc\">$F_Name</span></td>";
    }
    print "\n </tr> ";
    DspErrMsg($Err_RECOMP);
    DspErrMsg($Err_REEMPL);

    $fieldDesc = RetValue("ODTYPE='Y' and ODCODE='{$row[JOBCAT]}'", "PECODE", "max(ODDESC)");
    $textOvr = SetTextOvr($Err_REJBCT);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Job Category</span></td>";
    print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"REJBCT\" value=\"" . rtrim($row [JOBCAT]) . "\" size=\"6\" maxlength=\"6\">";
    print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=REJBCT&amp;fldType=Y&amp;fldDesc=jobCatDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
    print "\n     <span class=\"dspdesc\" id=\"jobCatDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_REJBCT);

    Build_Fld_Entry("Gender", "REGNDR", "inputalph", "GENDER", "REGNDR", $row [REGNDR], $Err_REGNDR, "1", "1", "Y", "", "");
    Build_Fld_Entry("Ethnic Category", "REETCT", "inputalph", "ETHNICID", "REETCT", $row [REETCT], $Err_REETCT, "1", "1", "Y", "", "");
    Build_Fld_Entry("Include in EEO Count", "REINCT", "inputcode", "YORN", "REINCT", $row [REINCT], $Err_REINCT, "1", "1", "", "", "");
    // print "\n <tr><td class=\"dsphdr\">Pay Band ID</td><td class=\"inputnmbr\">{$row [REPBID]}</td></tr>";
    // Build_Fld_Entry("W2 Box 1 Wages", "REW2B1", "inputnbr", "", "REW2B1", $row [REW2B1], $Err_REW2B1, "13", "13", "Y", "", "");
    // Build_Fld_Entry("Annual Hours Worked", "REHRSW", "inputnmbr", "", "REHRSW", $row [REHRSW], $Err_REHRSW, "13", "13", "Y", "", "");

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