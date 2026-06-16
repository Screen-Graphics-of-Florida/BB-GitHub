<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$turnaround = $_GET ['turnaround'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Update Freight/Special Charge";
$scriptName = "SelectUpdateFreightMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;turnaround=" . urlencode(trim($turnaround));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$programName = "";

if ($tag == "Edit_Data") {
    $stmtSQL = " Update OEORHP Set IHOFRT=$_POST[IHOFRT],IHOMSG=$_POST[IHOMSG]";
    $stmtSQL .= " Where IHTURN={$turnaround} ";

    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

    print "\n <script TYPE=\"text/javascript\">";
    print "\n     opener.location.href=opener.location.href;";
    print "\n     window.close();";
    print "\n </script>";
}

if ($tag == "MAINTAIN" || $errMsg) {
    require_once($docType);
    print "\n <html> <head>";
    require_once($headInclude);
    $formName = "Chg";
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'CheckEnterChg.php';
    require_once 'NoFormValidate.php';
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";

    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "SELECTUPDATEFREIGHTMAINTAIN";
    print "\n <td class=\"content\">";
    $stmtSQL = " Select a.*, coalesce(LFPO,'') as LFPO, coalesce(LFSEQ,'') as LFSEQ, coalesce(LFFRT,'') as LFFRT  
                 From OEORHP a left join OELIPF b on b.LFTURN=a.IHTURN Where a.IHTURN=$turnaround ";
    require 'stmtSQLEnd.php';

    print "\n <table $contentTable>";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td>";
    print "\n     <td class=\"toolbar\">";
    print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
    require 'CloseWindow.php';
    print "\n </td></tr></table>";

    print $hrTagAttr;
    require_once 'ErrorDisplay.php';
    if ($errMsg != '') {
        print "\n <span class=\"error\" $textOvr>$errMsg</span>";
    }

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    print "\n <table $contentTable> ";
    $billToName = RetValue("CMCUST={$row['IHBLTO']}", "HDCUST", "CMCNA1");
    Format_Header("Customer", $billToName, $row['IHBLTO']);
    Format_Header("Turnaround", $row['IHTURN'], "");
    Format_Header("Order Number", $row['IHORD#'], "");
    Format_Header("PO Number", $row['LFPO'], "");
    $F_LFFRT = Format_Nbr ( $row [LFFRT], $amtNbrDec, $amtEditCode, "", "", "" );
    Format_Header("PO Freight", $F_LFFRT, "");
    print "\n </table> ";
    print $hrTagAttr;

    $focusField = "IHOFRT";

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
    print "\n <table $contentTable>";

    Build_Fld_Entry("Freight", "IHOFRT", "inputnbr", "", "IHOFRT", $row [IHOFRT], $Err_IHOFRT, "15", "15", "", "", "");
    Build_Fld_Entry("Special Charges", "IHOMSG", "inputnbr", "", "IHOMSG", $row [IHOMSG], $Err_IHOMSG, "15", "15", "", "", "");

    print "\n <script TYPE=\"text/javascript\">";
    print "\n document.Chg.$focusField.focus();";
    print "\n </script>";
    print "\n </form>";
    print "</table>";
    print $hrTagAttr;
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    print "</body> </html>";
}

?>