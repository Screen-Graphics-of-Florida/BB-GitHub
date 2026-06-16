<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$poNumber = (isset($_GET['poNumber'])) ? $_GET['poNumber'] : null;

$page_title = "Reset Purchase Order Busy Flag";
$scriptName = "ResetPOBusyFlag.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$dftOrderBy = array(array("POPO", "A", "Purchase Order"));
$programName = "";
$advanceSearch = "N";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != "") {
    $dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

if ($tag == "RESETPO" && !is_null($poNumber)) {
    require 'stmtSQLClear.php';
    $stmtSQL = " Update POPOMS Set POBUSY=' ' Where POPO=$poNumber";
    $status = db2_exec($i5Connect->getConnection (), $stmtSQL);
    $confMessage = "Purchase Order {$poNumber} busy flag has been reset";
    $tag = "";
}


if ($tag == "ORDERBY") {
    if ($sequence == "PO") {
        $orby = array(array("POPO", "A", "Purchase Order"));
    } elseif ($sequence == "Vendor") {
        $orby = array(array("POVEND", "A", "Vendor Number"), array("POPO", "A", "Purchase Order"));
    } elseif ($sequence == "Name") {
        $orby = array(array("VN_VMVNA1U", "A", "Vendor Name"), array("POPO", "A", "Purchase Order"));
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD") {
    $andOr = $_POST['andOr'];
    require_once 'WildCardClear.php';
    $returnValue = Build_WildCard("POPO", "Purchase Order", $_POST['srchPO'], "", $_POST['operPO'], "A");
    $returnValue = Build_WildCard("POVEND", "Vendor Number", $_POST['srchVend'], "", $_POST['operVend'], "A");
    $returnValue = Build_WildCard("VN_VMVNA1U", "Vendor Name", $_POST['srchName'], "U", $_POST['operName'], "A");
    require_once 'WildCardUpdate.php';
}

require_once($docType);
print "\n <html> \n	<head>";
require_once($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';
require_once 'Menu.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function confirmReset(text) {return confirm(\"Confirm Reset of Busy Flag for:\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "RESETPOBUSYFLAG";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y") {
    print "\n <td class=\"toolbar\">";
    require_once 'FormatToprint.php';
    require_once 'HelpPage.php';
    print "</td>";
}

print "\n </tr></table>";
require_once 'ConfMessageDisplay.php';
print $hrTagAttr;

require 'stmtSQLClear.php';
$distinctSQL = "PUUSER||PUTYPE ";
$stmtSQL .= " Select * ";
$fileSQL .= " POPOMS09 ";
$selectSQL = "POBUSY = 'B' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

if ($formatToPrint == "") {
    $qsOpt = "";
    $qsOpt .= "\n <option value=\"POPO|null|Purchase Order|N|\" title=\"Purchase Order\" SELECTED>Purchase Order";
    $qsOpt .= "\n <option value=\"POVEND|null|Vendor Number|N|\" title=\"Vendor Number\">Vendor Number";
    $qsOpt .= "\n <option value=\"VN_VMVNA1U|null|Vendor Name|A|U\" title=\"Vendor Name\">Vendor Name";
    require 'QuickSearchOption.php';
}

print "<table $contentTable> <tr>";
$returnValue = OrderBy_Sort("POPO");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PO\" title=\"Sequence By Purchase Order\">{$sortPoint}Purchase<br>Order</a></th>";
$returnValue = OrderBy_Sort("POVEND");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Vendor\" title=\"Sequence By Vendor Number,Purchase Order\">{$sortPoint}Vendor<br>Number</a></th>";
$returnValue = OrderBy_Sort("VN_VMVNA1U");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=VendorName\" title=\"Sequence By Vendor Name,Purchase Order\">{$sortPoint}Vendor Name</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\">";
    $confirmDesc = Format_Confirm_Desc("Purchase Order " . $row['POPO'], "", $row[VN_VMVNA1], "", "", "");
    print "\n <td class=\"colnmbr\"><a onClick=\"return confirmReset('$confirmDesc')\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;poNumber=" . urlencode(trim($row['POPO'])) . "&amp;tag=RESETPO\">$row[POPO]</a></td>";
    print "\n <td class=\"colnmbr\">$row[POVEND]</td>";
    print "\n <td class=\"colalph\">$row[VN_VMVNA1]</td>";
    print "\n </tr>";

    $startRow++;
    $rowCount++;
}

if ($rowCount == 0) {
    require 'NoRecordsFound.php';
}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>