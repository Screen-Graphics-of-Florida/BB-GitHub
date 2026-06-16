<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromUser = $_GET ['fromUser'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Requisition Approval Level Maintenance";
$scriptName = "RequisitionApprovalLevelMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromUser=" . urlencode(trim($fromUser));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "PORQAL";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=520";
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
        $lastUpdatedCurrent = RetValue("ALUSER='{$fromUser}'", "PORQAL", "ALTSTP");
        if ($lastUpdatedCurrent != $_POST ['ALTSTP']) {
            $errMsg = "Row has been previously updated";
        }
    } elseif ($maintenanceCode == "D") {
        $desc = RetValue("USUSER='{$fromUser}'", "SYUSER", "USDESC");

        $stmtSQL = " Delete From PORQAL Where ALUSER='{$fromUser}'";
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, "User: " . $desc, "{$fromUser}", "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        exit ();
    }

    if (!$errMsg && $maintenanceCode != "D") {

        $_POST['user'] = strtoupper($_POST['user']);
        $userDesc = RetValue("USUSER='{$_POST['user']}'", "SYUSER", "USDESC");
        if ($userDesc == '' && $_POST['user'] != '*DFT') {
            $Err_ALUSER = "Invalid User";
            $errMsg = 'Please correct all errors';
        }
        if ($maintenanceCode == "A") {
            $aprUser = RetValue("ALUSER='{$_POST['user']}'", "PORQAL", "ALUSER");
            if ($_POST['user'] == $aprUser) {
                $Err_ALUSER = "User already exists";
                $errMsg = 'Please correct all errors';
            }
        }

        if ($_POST['manager'] != '') {
            $_POST['manager'] = strtoupper($_POST['manager']);
            $mgrDesc = RetValue("USUSER='{$_POST['manager']}'", "SYUSER", "USDESC");
            if ($mgrDesc == '') {
                $Err_ALMGR = "Invalid Manager";
                $errMsg = 'Please correct all errors';
            }
        }
    }

    if (!$errMsg) {
        $dept = strtoupper($_POST ['ALDEPT']);
        if ($maintenanceCode == "A") {
            $stmtSQL = " Insert Into PORQAL (ALUSER,ALUEML,ALLMT,ALMGR,ALMEML1,ALMEML2,ALMEML3,ALAPEM,ALDEPT,ALTSTP,ALTSUS)";
            $stmtSQL .= " Values ('{$_POST ['user']}','{$_POST ['ALUEML']}',{$_POST ['ALLMT']},'{$_POST ['manager']}','{$_POST ['ALMEML1']}','{$_POST ['ALMEML2']}','{$_POST ['ALMEML3']}','{$_POST ['ALAPEM']}','{$dept}',CURRENT_TIMESTAMP,'{$userProfile}') ";
        } else {
            $stmtSQL = " Update PORQAL set ALUEML='{$_POST ['ALUEML']}',ALLMT={$_POST ['ALLMT']},ALMGR='{$_POST ['manager']}',ALMEML1='{$_POST ['ALMEML1']}',ALMEML2='{$_POST ['ALMEML2']}',ALMEML3='{$_POST ['ALMEML3']}',ALAPEM='{$_POST ['ALAPEM']}',ALDEPT='{$dept}',ALTSTP=CURRENT_TIMESTAMP,ALTSUS='{$userProfile}'";
            $stmtSQL .= " Where ALUSER='{$_POST['user']}'";
        }
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, "User: " . $userDesc, $_POST ['ALUSER'], "", "", "", "");
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
    print "\n if (document.Chg.user.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
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
    $pageID = "REQAPPROVALMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    $stmtSQL = "";
    if ($maintenanceCode == "A") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL = " Select *  From PORQAL Where ALUSER='{$fromUser}' ";
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
        $row [ALUSER] = $_POST ['user'];
        $row [ALUEML] = $_POST ['ALUEML'];
        $row [ALLMT] = $_POST ['ALLMT'];
        $row [ALMGR] = $_POST ['manager'];
        $row [ALMEML1] = $_POST ['ALMEML1'];
        $row [ALMEML2] = $_POST ['ALMEML2'];
        $row [ALMEML3] = $_POST ['ALMEML3'];
        $row [ALAPEM] = $_POST ['ALAPEM'];
        $row [ALDEPT] = $_POST ['ALDEPT'];
        $focusField = "ALUSER";
    } else {
        $focusField = "ALUEML";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";
    print "\n <tr><td><input type=\"hidden\" name=\"ALTSTP\" value=\"" . rtrim($row ['ALTSTP']) . "\"></td></tr> ";
    print "\n <tr><td><input type=\"hidden\" name=\"ALUSER\" value=\"" . rtrim($row ['ALUSER']) . "\"></td></tr> ";

    $fieldDesc=RetValue("USUSER='$row[ALUSER]'", "SYUSER", "USDESC");
    $textOvr=SetTextOvr($Err_ALUSER);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>User</span></td> ";
    if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
        print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"user\" value=\"" . rtrim($row['ALUSER']) . "\" size=\"10\" maxlength=\"10\"> ";
        print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=user&amp;descFld=userName\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
        print "\n     <span class=\"dspdesc\" id=\"userName\">$fieldDesc</span></td>";
    } else {
        $user = trim($row[ALUSER]);
        print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"user\" value=\"" . rtrim($row['ALUSER']) . "\">$fieldDesc  [{$user}]</td>";
    }
    print "\n </tr> ";
    DspErrMsg($Err_ALUSER);

    Build_Fld_Entry("User E-Mail", "ALUEML", "inputalph", "", "ALUEML", $row [ALUEML], $Err_ALUEML, "50", "256", "", "", "");
    Build_Fld_Entry("Approval Limit", "ALLMT", "inputnbr", "", "ALLMT", $row [ALLMT], $Err_ALLMT, "15", "15", "", "", "");

    $fieldDesc=RetValue("USUSER='$row[ALMGR]'", "SYUSER", "USDESC");
    $textOvr=SetTextOvr($Err_ALMGR);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Manager</span></td> ";
    print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"manager\" value=\"" . rtrim($row['ALMGR']) . "\" size=\"10\" maxlength=\"10\"> ";
    print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=manager&amp;descFld=mgrName\" onclick=\"$searchWinVar\">$searchImage</a>";
    print "\n     <span class=\"dspdesc\" id=\"mgrName\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_ALMGR);

    Build_Fld_Entry("Manager E-Mail 1", "ALMEML1", "inputalph", "", "ALMEML1", $row [ALMEML1], $Err_ALMEML1, "50", "256", "", "", "");
    Build_Fld_Entry("Manager E-Mail 2", "ALMEML2", "inputalph", "", "ALMEML2", $row [ALMEML2], $Err_ALMEML2, "50", "256", "", "", "");
    Build_Fld_Entry("Manager E-Mail 3", "ALMEML3", "inputalph", "", "ALMEML3", $row [ALMEML3], $Err_ALMEML3, "50", "256", "", "", "");
    Build_Fld_Entry("Send Approval E-Mail", "ALAPEM", "inputcode", "YORN", "ALAPEM", $row [ALAPEM], $Err_ALAPEM, "1", "1", "", "", "");
    Build_Fld_Entry("Department", "ALDEPT", "inputalph", "", "ALDEPT", $row [ALDEPT], $Err_ALDEPT, "10", "10", "", "", "");

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
}

?>