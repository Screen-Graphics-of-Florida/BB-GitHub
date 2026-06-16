<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$vendorNumber = $_GET['vendorNumber'];
$vendorName = $_GET['vendorName'];
$purchaseOrderNumber = $_GET['purchaseOrderNumber'];
$poBusy = (isset($_GET ['poBusy'])) ? $_GET ['poBusy'] : null;

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "Update Required Dates";
$scriptName = "POUpdateRequiredDate.php";
$scriptVarBase = "{$genericVarBase}&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber)) . "&amp;vendorNumber=" . urlencode(trim($vendor)) . "&amp;vendorName=" . urlencode(trim($vendorName));

if ($tag == "MAINTAIN") {
    require_once($docType);
    print "\n <html> \n	<head>";
    require_once($headInclude);
    $formName = "Chg";

    print "\n \n <script TYPE=\"text/javascript\">";
    require_once 'CalendarInclude.php';
    require_once 'CheckEnterChg.php';
    require_once 'DateEdit.php';

    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.reqDate.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (editdate(document.Chg.reqDate) ";
    print "\n ) return true; ";
    print "\n } ";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";
    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    require_once($searchBanner);
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    print "\n <td class=\"content\">";
    print "\n <table $contentTable>";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td>";
    print "\n     <td class=\"toolbar\">";
    if (is_null($poBusy)) {
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
    }
    print "\n <a href=\"javascript:opener.location.href=opener.location.href; javascript:window.close()\">$cancelImageLrg</a> ";
    print "\n </td></tr></table>";

    print "\n <table $contentTable><tr><td>";
    Format_Header_URL("Vendor", $vendorName, $vendorNumber, "");
    Format_Header_URL("Purchase Order", $purchaseOrderNumber, "", "");
    print "\n </td></tr></table>";

    if (!is_null($poBusy)) {
        {
            print "\n <span class=\"error\"> &nbsp; &nbsp; Cannot update - Order is being maintained by another user</span>";
        }
    }
    print $searchhrTagAttr;

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$scriptVarBase}&amp;tag=Edit_Data\">";
    print "\n <table $contentTable>";
    Build_Fld_Entry("Required Date", "reqDate", "inputdate", "Date", "D1OPDT", $row[D1OPDT], $Err_D1OPDT, "6", "6", "", "", "");
    print "\n </table> ";

    print "\n </form>";
    print "$searchhrTagAttr";
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    require_once($searchTrailer);
    print "\n </body> \n </html>";
}

if ($tag == "Edit_Data") {
    $poBusy = RetValue("POPO=$purchaseOrderNumber", "POPOMS", "POBUSY");
    if ($poBusy == 'B') {
        print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;poBusy=Y\"> ";
        exit();
    }
    $reqDate = DateToCYMD($_POST['reqDate']);
    require 'stmtSQLClear.php';
    $stmtSQL = " Update POPOMD Set PDRQDT={$reqDate} Where PDPO=$purchaseOrderNumber and PDSTAT='O' and PDPOLT='' and PDPORL=0";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

    print "\n \n <script TYPE=\"text/javascript\">";
    print "\n opener.location=opener.location";
    print "\n window.close() \n";
    print "\n </script> \n";
    exit();
}
?>	
