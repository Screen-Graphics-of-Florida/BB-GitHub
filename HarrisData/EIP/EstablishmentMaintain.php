<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromESID = $_GET ['fromESID'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Establishment Maintenance";
$scriptName = "EstablishmentMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromESID=" . urlencode(trim($fromESID));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "ESTABLISH";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=512";
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
        $lastUpdatedCurrent = RetValue("ESESID={$fromESID}", "PEESTB", "ESLUPD");
        if ($lastUpdatedCurrent != $_POST ['ESLUPD']) {
            $errMsg = "Row has been previously updated";
        }
    } elseif ($maintenanceCode == "D") {
        $desc = RetValue("ESESID={$fromESID}", "PEESTB", "ESNAME");

        $stmtSQL = " Delete From PEESTB Where ESESID=" . $fromESID;
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $desc, "", "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        exit ();
    }

    if (!$errMsg && $maintenanceCode != "D") {
        $_POST[ESST] = strtoupper($_POST[ESST]);
        $stateDesc = RetValue ( "STID='$_POST[ESST]'", "HDSTID", "STDESC" );
        if ($stateDesc == '') {
            $Err_ESST = "Invalid State";
            $errMsg = 'Please correct all errors';
        }

        $flagDesc = RetValue ( "FLTYPE='EEOSTATUS' and FLVALU='$_POST[ESSTAT]'", "SYFLAG", "FLDESC" );
        if ($flagDesc == '') {
            $Err_ESSTAT = "Invalid Status";
            $errMsg = 'Please correct all errors';
        }

        $flagDesc = RetValue ( "FLTYPE='EEOYN' and FLVALU='$_POST[ESQB2C]'", "SYFLAG", "FLDESC" );
        if ($flagDesc == '') {
            $Err_ESQB2C = "Invalid Entry - must be 1 for Yes or 2 for No";
            $errMsg = 'Please correct all errors';
        }
    }

    if (!$errMsg) {
        if ($maintenanceCode == "A") {
            $stmtSQL = " Insert Into PEESTB (ESSTAT, ESUSID, ESUNIT, ESEIN, ESNAME, ESADDR, ESADR2, ESCITY, ESST, ESZIP, ESCNTY, ESNAICS, ESQB2C, ESDBN, ESCOT, ESCON, ESCOP, ESCOE)";
            $stmtSQL .= " Values ({$_POST ['ESSTAT']},'{$_POST ['ESUSID']}','{$_POST ['ESUNIT']}',{$_POST ['ESEIN']},'{$_POST ['ESNAME']}','{$_POST ['ESADDR']}','{$_POST ['ESADR2']}','{$_POST ['ESCITY']}','{$_POST ['ESST']}',{$_POST ['ESZIP']},'{$_POST ['ESCNTY']}',{$_POST ['ESNAICS']},{$_POST ['ESQB2C']},'{$_POST ['ESDBN']}','{$_POST ['ESCOT']}','{$_POST ['ESCON']}','{$_POST ['ESCOP']}','{$_POST ['ESCOE']}') ";
        } else {
            $stmtSQL = " Update PEESTB set ESSTAT={$_POST ['ESSTAT']},ESUSID='{$_POST ['ESUSID']}',ESUNIT='{$_POST ['ESUNIT']}',ESEIN={$_POST ['ESEIN']},ESNAME='{$_POST ['ESNAME']}',ESADDR='{$_POST ['ESADDR']}',ESADR2='{$_POST ['ESADR2']}',ESCITY='{$_POST ['ESCITY']}',ESST='{$_POST ['ESST']}',ESZIP={$_POST ['ESZIP']},ESCNTY='{$_POST ['ESCNTY']}',ESNAICS={$_POST ['ESNAICS']},ESQB2C={$_POST ['ESQB2C']},ESDBN='{$_POST ['ESDBN']}',ESCOT='{$_POST ['ESCOT']}',ESCON='{$_POST ['ESCON']}',ESCOP='{$_POST ['ESCOP']}',ESCOE='{$_POST ['ESCOE']}',ESLUPD=CURRENT_TIMESTAMP";
            $stmtSQL .= " Where ESESID={$_POST['ESESID']} ";
        }
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        // If row not added, set identity column and try again
        if (!$status && $maintenanceCode == "A") {
            Check_Identity_Column('PEESTB', 'ESESID', $stmtSQL);
        }

        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $_POST ['ESNAME'], "", "", "", "", "");
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
    print "\n if (document.Chg.ESNAME.value ==\"\" || ";
    print "\n     document.Chg.ESADDR.value ==\"\" || ";
    print "\n     document.Chg.ESUSID.value ==\"\" || ";
    print "\n     document.Chg.ESSTAT.value ==\"\" || ";
    print "\n     document.Chg.ESCITY.value ==\"\" || ";
    print "\n     document.Chg.ESST.value ==\"\" || ";
    print "\n     document.Chg.ESZIP.value ==\"\" || ";
    print "\n     document.Chg.ESCNTY.value ==\"\" || ";
    print "\n     document.Chg.ESNAICS.value ==\"\" || ";
    print "\n     document.Chg.ESQB2C.value ==\"\"";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (document.Chg.ESEIN.value.length < 9) {alert(\"Federal EIN must be 9 digits\"); return false;} ";
    print "\n if (editNum(document.Chg.ESEIN, 9, 0)) ";
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
    $pageID = "ESTABLISHMENTMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    $stmtSQL = "";
    if ($maintenanceCode == "A") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL = " Select *  From PEESTB Where ESESID=$fromESID ";
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
        $row [ESESID] = $_POST ['ESESID'];
        $row [ESSTAT] = $_POST ['ESSTAT'];
        $row [ESUSID] = $_POST ['ESUSID'];
        $row [ESUNIT] = $_POST ['ESUNIT'];
        $row [ESEIN] = $_POST ['ESEIN'];
        $row [ESNAME] = $_POST ['ESNAME'];
        $row [ESADDR] = $_POST ['ESADDR'];
        $row [ESADR2] = $_POST ['ESADR2'];
        $row [ESCITY] = $_POST ['ESCITY'];
        $row [ESADR2] = $_POST ['ESADR2'];
        $row [ESST] = $_POST ['ESST'];
        $row [ESZIP] = $_POST ['ESZIP'];
        $row [ESCNTY] = $_POST ['ESCNTY'];
        $row [ESNAICS] = $_POST ['ESNAICS'];
        $row [ESQB2C] = $_POST ['ESQB2C'];
        $row [ESDBN] = $_POST ['ESDBN'];
        $row [ESCOT] = $_POST ['ESCOT'];
        $row [ESCON] = $_POST ['ESCON'];
        $row [ESCOP] = $_POST ['ESCOP'];
        $row [ESCOE] = $_POST ['ESCOE'];
        $focusField = "ESESID";
    } else {
        $focusField = "ESESID";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";
    print "\n <tr><td><input type=\"hidden\" name=\"ESLUPD\" value=\"" . rtrim($row ['ESLUPD']) . "\"></td></tr> ";
    print "\n <tr><td><input type=\"hidden\" name=\"ESESID\" value=\"" . rtrim($row ['ESESID']) . "\"></td></tr> ";

    Build_Fld_Entry("Name", "ESNAME", "inputalph", "", "ESNAME", $row [ESNAME], $Err_ESNAME, "35", "35", "Y", "", "");
    Build_Fld_Entry("Address", "ESADDR", "inputalph", "", "ESADDR", $row [ESADDR], $Err_ESADDR, "46", "46", "Y", "", "");
    Build_Fld_Entry("Address 2", "ESADR2", "inputalph", "", "ESADR2", $row [ESADR2], $Err_ESADR2, "25", "25", "", "", "");
    Build_Fld_Entry("City", "ESCITY", "inputalph", "", "ESCITY", $row [ESCITY], $Err_ESCITY, "28", "28", "Y", "", "");

    // State
    $fieldDesc = RetValue ( "STID='$row[ESST]'", "HDSTID", "STDESC" );
    $textOvr = SetTextOvr ( $Err_ESST );
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>State</span></td>";
    print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"ESST\" value=\"" . rtrim ( $row ['ESST'] ) . "\" size=\"3\" maxlength=\"2\">";
    print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=ESST&amp;fldDesc=stateDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
    print "\n     <span class=\"dspdesc\" id=\"stateDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg ( $Err_ESST );

    Build_Fld_Entry("Zip Code", "ESZIP", "inputnbr", "", "ESZIP", $row [ESZIP], $Err_ESZIP, "5", "5", "Y", "", "");
    Build_Fld_Entry("County", "ESCNTY", "inputalph", "", "ESCNTY", $row [ESCNTY], $Err_ESCNTY, "28", "28", "Y", "", "");
    Build_Fld_Entry("Status", "ESSTAT", "inputnbr", "EEOSTATUS", "ESSTAT", $row [ESSTAT], $Err_ESSTAT, "1", "1", "Y", "", "");
    Build_Fld_Entry("User ID", "ESUSID", "inputalph", "", "ESUSID", $row [ESUSID], $Err_ESUSID, "8", "8", "Y", "", "");
    Build_Fld_Entry("Unit Number", "ESUNIT", "inputalph", "", "ESUNIT", $row [ESUNIT], $Err_ESUNIT, "7", "7", "", "", "");
    Build_Fld_Entry("Federal EIN", "ESEIN", "inputnbr", "", "ESEIN", $row [ESEIN], $Err_ESEIN, "9", "9", "Y", "", "");
    Build_Fld_Entry("NAICS Code", "ESNAICS", "inputnbr", "", "ESNAICS", $row [ESNAICS], $Err_ESNAICS, "6", "6", "Y", "", "");
    Build_Fld_Entry("Dun & Bradstreet Number", "ESDBN", "inputalph", "", "ESDBN", $row [ESDBN], $Err_ESDBN, "9", "9", "", "", "");
    Build_Fld_Entry("EEO-1 Report Filed Last Year (B2C)?", "ESQB2C", "inputnbr", "EEOYN", "ESQB2C", $row [ESQB2C], $Err_ESQB2C, "1", "1", "Y", "", "");

    print "\n <tr><td colspan='5'><fieldset class=\"legendBody\"> ";
    print "\n <legend class=\"legendTitle\">Certifying Official";
    print "\n </legend> ";
    print "\n <table $contentTable>";

    Build_Fld_Entry("Title", "ESCOT", "inputalph", "", "ESCOT", $row[ESCOT], $Err_ESCOT, "35", "35", "", "", "");
    Build_Fld_Entry("Name", "ESCON", "inputalph", "", "ESCON", $row[ESCON], $Err_ESCON, "35", "35", "", "", "");
    Build_Fld_Entry("Phone", "ESCOP", "inputalph", "", "ESCOP", $row[ESCOP], $Err_ESCOP, "10", "10", "", "", "");
    Build_Fld_Entry("Email", "ESCOE", "inputalph", "", "ESCOE", $row[ESCOE], $Err_ESCOE, "40", "40", "", "", "");

    print "\n </table> ";
    print "\n </fieldset></td></tr>";
    print "\n <tr><td>&nbsp;</td></tr>";

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