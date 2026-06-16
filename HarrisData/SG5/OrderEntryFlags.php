<?php
require_once 'GetURLParm.php';

$customerNumber = $_GET['customerNumber'];
$customername = $_GET['customername'];
$orderControlNumber = $_GET['orderControlNumber'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$dspMaxRows = 9999;
$page_title = "Order Entry Flags";
$scriptName = "OrderEntryFlags.php";
$scriptVarBase = "{$genericVarBase}&amp;customerNumber=" . urlencode(trim($customerNumber)) . "&amp;customername=" . urlencode(trim($customername)) . "&amp;orderControlNumber=" . urlencode(trim($orderControlNumber));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

$stmtSQL = " Select * From OEHDWK Where H1OCTL=$orderControlNumber";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$hdrwk = db2_fetch_assoc($sqlResult);
$h1oef = str_split($hdrwk['H1OEF']);
$flag = array_combine(range(1, count($h1oef)), array_values($h1oef));

if ($tag == "Edit_Data") {
    $after = array_replace($flag, $_POST);
    $oef = implode("", $after);
    $stmtSQL = " Update OEHDWK set H1OEF='{$oef}' Where H1OCTL=$orderControlNumber ";
    $status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );

    print "\n <script TYPE=\"text/javascript\">";
    print "\n   window.close();";
    print "\n </script>";
    exit();
}

require_once($docType);
print "\n <html> \n	<head>";
require_once($headInclude);
$formName = "Chg";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n     <td class=\"toolbar\">";
print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
print "\n <a href=\"javascript:window.close()\">$cancelImageMed</a>";
print "\n </td></tr></table>";

$orderShipped = false;
$lotItems = false;
if ($hdrwk['H1MNCD'] != 'A') {
    // Check if Order has been shipped
    $stmtSQL = " Select sum(O1QSTD+O1QSTC) as QTYS From OEDTWK Where O1OCTL=$orderControlNumber ";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $dtlRow = db2_fetch_assoc($sqlResult);
    if ($dtlRow['QTYS'] > 0) {
        $orderShipped = true;
    }
    // Check if Order has Lot Controlled Items
    $stmtSQL = " Select count(*) as LOTCNT From OEDTWK Where O1OCTL=$orderControlNumber and O1LLTC<>'N' ";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $dtlRow = db2_fetch_assoc($sqlResult);
    if ($dtlRow['LOTCNT'] > 0) {
        $lotItems = true;
    }
}

print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";

print "<table $contentTable> <tr>";
print "<th class=\"colhdr\">Description</th>";
print "<th class=\"colhdr\">Value</th>";
print "\n </tr>";

require 'stmtSQLClear.php';
$stmtSQL .= " Select a.FLVALU as FLVALU,a.FLDESC as FLDESC, b.FLDESC as FLVLDS, b.FLVALU as FLVLFG ";
$fileSQL .= " SYFLAG a inner join SYFLAG b on b.FLTYPE='OEFLAG'||a.FLVALU ";
$fileSQL .= "          inner join HDORFG c on a.FLVALU=FGFGFG and FGAPID='OE' and FGORTY='$hdrwk[H1ORTY]' ";
$selectSQL = " a.FLTYPE='OEFLAGDSC'";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By a.FLVALU";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

$startRow = 1;
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($saveFlag != $row['FLVALU']) {
        require 'SetRowClass.php';
    }
    if ($saveFlag == "" || $saveFlag != $row['FLVALU']) {
        $saveDesc = $row['FLDESC'];
        $saveFlag = $row['FLVALU'];
    }
    $row['OEFL01'] = 1;
    $flagID = trim($row['FLVALU']);
    $flagID = ltrim($flagID, "0");
    $flagSel = "";
    $flagClass = "colalph";
    $id = intval($saveFlag);
    if ($flag[$id] == trim($row['FLVLFG'])) {
        $flagSel = "CHECKED";
        $flagClass = "colvcat";
    }

    print "\n <tr class=\"$rowClass\">";
    print "\n     <td class=\"colalph\">$saveDesc</td>";
    $ids = [2,6,7];
    $disabled = ($orderShipped && in_array($id, $ids)) ? 'DISABLED' : '';
    // Disable Update Inventory if Not Add and Lot Controlled Item exist
    if ($id == 2 && $hdrwk['H1MNCD'] != 'A' && $lotItems) {
        $disabled = 'DISABLED';
    }
    // Disable Shipping and Shipping Method in EIP
    if ($id == 7 || $id == 9) {
        $disabled = 'DISABLED';
    }
    // Disable Instant Pick Ticket if Printed/Not Add or Order Type is 'Q' or 'X'
    if ($id == 8 && (trim($row['FLVLFG']) == '2' && $hdrwk['H1MNCD'] != 'A') || ($hdrwk['H1ORTY'] == 'Q' || $hdrwk['H1ORTY'] == 'X')) {
        $disabled = 'DISABLED';
    }
    $val = trim($row['FLVLFG']);
    print "\n     <td class=\"colalph\"><label class=\"$flagClass\"><input type=\"radio\" name=\"$flagID\" value=\"$val\" $flagSel $disabled>$row[FLVLDS]</label></td>";
    print "\n </tr>";
    $saveDesc = "&nbsp;";
    $startRow++;
    $rowCount++;
}


print "</table>";
print "\n </form>";

print "<table $contentTable><tr><td>";
print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
print "\n <a href=\"javascript:window.close()\">$cancelImageMed</a>";
print "\n </td></tr></table>";

require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';
?>
