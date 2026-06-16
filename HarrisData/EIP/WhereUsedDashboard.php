<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$itemNumber = $_GET['itemNumber'];
$plantNumber = $_GET['plantNumber'];
$vendorPassed = (isset($itemNumber) && trim($itemNumber) != '') ? true : null;
$toggle = (isset($_GET['toggle'])) ? $_GET['toggle'] : null;
$downLoadCsv = (isset($_GET['downLoadCsv'])) ? $_GET['downLoadCsv'] : null;
$loadFile = (isset($_GET['loadFile'])) ? $_GET['loadFile'] : null;
$checkBoxAfterQuickSearch = 'Y';

if (!isset($_SESSION['viewCharts'])) {
    $_SESSION['viewCharts'] = 'Y';
}
$toggleChart = (!is_null($toggle) && $_SESSION[viewCharts] != $toggle) ? true : null;
if (!is_null($toggleChart)) {
    $_SESSION['viewCharts'] = ($_SESSION['viewCharts'] == 'Y') ? 'N' : 'Y';
}

$displayChart = ($_SESSION['viewCharts'] == 'Y') ? 'inline-block' : 'none';
$viewHide = ($_SESSION['viewCharts'] == 'Y') ? 'N' : 'Y';
$chartImg = ($_SESSION['viewCharts'] == 'Y') ? $barChartHide : $barChartView;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$fDays = [];
$tDays = [];
$fDates = [];
$tDates = [];
$today = date("Y-m-d");
$fytu = '1' . date("y") . '0101';
$tytu = Date_FromISO_ToCYMD($today);
$flyu = '1' . date("y") - 1 . '0101';
$tlyu = $tytu - 10000;
$flyt = '1' . date("y") - 1 . '0101';
$tlyt = substr($flyu, 1, 3) . '1231';
$testVar = null;
$bucketIncr = ($whereUsedIncrement == 'D' || $whereUsedIncrement == 'M') ? $whereUsedIncrement : null;

if (!is_null($bucketIncr)) {
    for ($i = 1; $i <= 12; $i++) {
        $nbr = str_pad($i, 2, "0", STR_PAD_LEFT);
        $var = 'whereUsedBucket' . $nbr;
        if (trim($$var) == '' || !is_null($testVar) && $testVar >= intval($$var)) break;
        $testVar = intval($$var);
        $incr = ($bucketIncr == 'D') ? ' days' : ' months';
        $from = date("Y-m-d", strtotime("$today -{$$var} $incr"));
        $fDates[$i] = Date_FromISO_ToCYMD($from);
        if ($i == 1) {
            $fDays[$i] = 0;
            $tDays[$i] = $$var;
            $to = Date_FromISO_ToCYMD($today);
        } else {
            $fDays[$i] = $tDays[$i - 1] + 1;
            $tDays[$i] = $$var;
            $iso = Date_CYMD_ISO($fDates[$i - 1]);
            $to = date("Y-m-d", strtotime("$iso -1 $incr"));
            $to = Date_FromISO_ToCYMD($to);
        }
        $tDates[$i] = $to;
    }
}

if ($loadFile == "Y") {
    $edtVar = '';
    Concat_Field("@@plt@", $plantNumber);
    Concat_Field("@@item", $itemNumber);
    Concat_Field("@@fytu", $fytu);
    Concat_Field("@@tytu", $tytu);
    Concat_Field("@@flyu", $flyu);
    Concat_Field("@@tlyu", $tlyu);
    Concat_Field("@@flyt", $flyt);
    Concat_Field("@@tlyt", $tlyt);
    for ($i = 1; $i <= 12; $i++) {
        if (isset($fDates[$i])) {
            $nbr = str_pad($i, 2, "0", STR_PAD_LEFT);
            Concat_Field("@@fd" . $nbr, $fDates[$i]);
            Concat_Field("@@td" . $nbr, $tDates[$i]);
        }
    }
    $data = Update_Work_File($edtVar);
}

$page_title = "Where Used Dashboard";
$scriptName = "WhereUsedDashboard.php";
$scriptVarBase = "{$genericVarBase}&amp;itemNumber=" . urlencode(trim($itemNumber)) . "&amp;plantNumber=" . urlencode(trim($plantNumber)) . "&amp;fDate=" . urlencode(trim($fDate)) . "&amp;tDate=" . urlencode(trim($tDate)) . "&amp;reqQty=" . urlencode(trim($reqQty));
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "ITEMCOST";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$pageSelectList = 'N';
$advanceSearch = 'N';
$dftOrderBy = [["PZRSEQ", "A", "Level"]];

$chartTotal = [];
$chartIssues = [];
$chartSales = [];
$curYrTotal = 0;
$lastYrTotal = 0;
GetChartData();
$curYrTotal = $chartTotal[YEAR1];
$lastYrTotal = $chartTotal[YEAR2];
$F_curYrTotal = Format_Nbr($curYrTotal, $qtyNbrDec, $qtyEditCode, '', '', '');
$F_lastYrTotal = Format_Nbr($lastYrTotal, $qtyNbrDec, $qtyEditCode, '', '', '');
$lastYrIssues = $chartIssues[YEAR2];
$lastYrSales = $chartSales[YEAR2];
$lastYrYTDTotal = $lastYrIssues + $lastYrSales;
$F_lastYrYTDTotal = Format_Nbr($lastYrYTDTotal, $qtyNbrDec, $qtyEditCode, '', '', '');

$plantName = RetValue("PLPLNT={$plantNumber}", "HDPLNT", "PLNAME");
$itemDesc = RetValue("IMITEM='{$itemNumber}'", "HDIMST", "IMIMDS");
$itemPltQty = Get_Qty_ItemPlant();
$edtVar = $itemPltQty['edtVar'];
$OnHand = Decat_Field("@@ohqt", $edtVar);
$qtyOnHand = Format_Nbr($OnHand, $qtyNbrDec, $qtyEditCode, '', '', '');
$qtyAAU = Decat_Field("@@aau@", $edtVar);
$qtyAAU = Format_Nbr($qtyAAU, $qtyNbrDec, $qtyEditCode, '', '', '');
$qtyFlrStk = Decat_Field("@@ohfs", $edtVar);
$qtyFlrStk = Format_Nbr($qtyFlrStk, $qtyNbrDec, $qtyEditCode, '', '', '');
$qtyAval = Decat_Field("@@aval", $edtVar);
$qtyAval = Format_Nbr($qtyAval, $qtyNbrDec, $qtyEditCode, '', '', '');
$pegReq = RetValue("PGPPLT={$plantNumber} and PGCPN='{$itemNumber}'", "MRMPGM", "coalesce(sum(pgqpeg),0)");
$pegReq = Format_Nbr($pegReq, $qtyNbrDec, $qtyEditCode, '', '', '');
$returnValue = Rtv_Unit_Cost($itemNumber, "", "", $plantNumber);
$cost = $returnValue['cost'];
$parentCost = Format_Nbr($cost, $cstNbrDec, $cstEditCode, '', '', '');
$extParentCost = Format_Nbr($cost * $OnHand, $cstNbrDec, $cstEditCode, '', '', '');

// Program Option Security
$prog_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $prog_OPT['sec_01'];

if ($downLoadCsv) {
    downloadToCsv();
    exit();
}

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = [["View:", "Level 1", $viewCheckBoxURL, "1", "0"], ["", "Active Only", $viewCheckBoxURL, "2", "1"]];

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

require_once($docType);
print "\n
<html> <head> ";
require_once($headInclude);
print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
print "\n </script> ";
require_once($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "WhereUsedDashboard";
if ($formatToPrint == "") {
    require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";
print "\n <table $contentTable>";
print "\n <colgroup><col width=\"75%\"><col width=\"20%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y") {
    $maintainVar = "{$scriptVarBase}&amp;fromScript=$scriptName";
    print "\n <td class=\"toolbar\">";
    $whereUsed = str_replace('View Dashboard', 'View Explosion Dashboard', $dashboard);
    print "\n <a href=\"{$homeURL}{$phpPath}ExplosionDashboard.php{$genericVarBase}&amp;tag=Edit_Data&amp;itemNumber={$itemNumber}&amp;plantNumber={$plantNumber}\" title=\"Where Used Dashboard\">$whereUsed &nbsp;</a>";
    print "<a href=\"{$baseURL}&amp;toggle=$viewHide\">$chartImg</a>";
    if ($itemNumber) {
        print "<a href=\"{$homeURL}{$cGIPath}ItemPlantSelect.d2w/REPORT{$altVarBase}&amp;plantNumber={$plantNumber}&amp;itemNumber={$itemNumber}\" title=\"View Item/Plant\">&nbsp; {$portalHome}</a>";
    }
    print "<a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;downLoadCsv=Y\" title=\"Download to CSV File\">&nbsp; {$downloadCsv}</a>";
    require_once 'FormatToPrint.php';
    if (file_exists('R15.0_Where_Used_Dashboard.pdf')) {
        $designIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgHelp.gif\" title=\"View Where Used Dashboard Design\" alt=\"Help\">";
        print "<a href=\"R15.0_Where_Used_Dashboard.pdf\" target=\"_blank\">&nbsp; {$designIcon}</a>";
    }
    print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";
print "\n <table $contentTable><colgroup><col width=\"30%\"><col width=\"30%\"><col width=\"30%\">";
print "\n <tr>";
print "\n <td><table $contentTable>";
print "<tr><td class=\"hdrtitl\">Plant: </td><td class=\"hdrdata\"><a href=\"{$homeURL}{$cGIPath}PlantSelect.d2w/REPORT{$altVarBase}&amp;plantNumber={$plantNumber}\" title=\"View Plant\">$plantName &nbsp; [{$plantNumber}]</a>";
print "<tr><td class=\"hdrtitl\">Item: </td><td class=\"hdrdata\"><a href=\"{$homeURL}{$cGIPath}ItemPlantSelect.d2w/REPORT{$altVarBase}&amp;plantNumber={$plantNumber}&amp;itemNumber={$itemNumber}\" title=\"View Item/Plant\">$itemDesc &nbsp; [{$itemNumber}]</a></td></tr>";
print "\n </table></td>";

print "\n <td><table $contentTable>";
print "\n <tr><td class=\"hdrtitl\">Quantity On Hand: </td><td class=\"hdrdata\"><a href=\"{$homeURL}{$phpPath}hdlist.php{$scriptVarBase}&amp;tblID=506&amp;fKey1=IMITEM&amp;fVal1={$itemNumber}\" title=\"View Stock Variance\">$qtyOnHand</a></td></tr>";
Format_Header("On Hand Floorstock", $qtyFlrStk, '');
print "\n <tr><td class=\"hdrtitl\">Quantity Available: </td><td class=\"hdrdata\"><a href=\"{$homeURL}{$cGIPath}AvailableToPromise.d2w/REPORT{$altVarBase}&amp;itemNumber={$itemNumber}&amp;plantNumber={$plantNumber}\" title=\"View Available To Promise\">$qtyAval</a></td></tr>";
print "\n </table></td>";

print "\n <td><table $contentTable>";
if ($sec_01 == 'Y') {
    $partType = RetValue("IPITEM='{$itemNumber}' AND IPPLT={$plantNumber}", "HDIPLT", "IPPTYP");
    if ($partType == 'M' || $partType == 'B') {
        print "\n <tr><td class=\"hdrtitl\">Cost/Extended: </td><td class=\"hdrdata\"><a href=\"{$homeURL}{$cGIPath}CostSimulatorDisplay.d2w/BUILD{$altVarBase}&amp;itemNumber={$itemNumber}&amp;fromPlant={$plantNumber}&amp;plantNumber={$plantNumber}\" title=\"Cost Simulator\">{$parentCost} / {$extParentCost}</a></td></tr>";
    } else {
        print "\n <tr><td class=\"hdrtitl\">Cost/Extended: </td><td class=\"hdrdata\">{$parentCost} / {$extParentCost}</td></tr>";
    }
}
Format_Header("Anticipated Annual Usage", $qtyAAU, '');
Format_Header("Pegged Requirements", $pegReq, '');
print "\n </table></td>";
print "\n </tr></table> ";

require_once 'ConfMessageDisplay.php';

if ($viewHide == 'N') {
    print $hrTagAttr;
}
if ($formatToPrint) {
    $dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
    if ($sequence == "Level") {
        $orby = [["PZRSEQ", "A", "Level"]];
    } elseif ($sequence == "RtgSeq") {
        $orby = [["PZSEQN", "A", "Routing Sequence"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "Item") {
        $orby = [["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "Desc") {
        $orby = [["upper(PZIMDS)", "A", "Description"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "Inactive") {
        $orby = [["PZIMAC", "A", "Inactive"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "StockUOM") {
        $orby = [["PZUOMS", "A", "Stocking UOM"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PartType") {
        $orby = [["PZPTYP", "A", "Part Type"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "BackFlush") {
        $orby = [["PZSTWC", "A", "Backflush Code"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "QtyPer") {
        $orby = [["PZQPER", "A", "Quantity Per"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "YTDUsage") {
        $orby = [["PZYTDU", "A", "YTD Usage"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "LastUsage") {
        $orby = [["PZYTDL", "A", "Last Year YTD Usage"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "LastTotalUsage") {
        $orby = [["PZTOTU", "A", "Last Year Total Usage"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "OnHand") {
        $orby = [["PZOHQT", "A", "On Hand Quantity"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "Avail") {
        $orby = [["PZAVQT", "A", "Quantity Available"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "Cost") {
        $orby = [["PZTUCC", "A", "Cost"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "ExtCost") {
        $orby = [["PZEXTC", "A", "Extended Cost"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD01") {
        $orby = [["PZUD01", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT01") {
        $orby = [["PZUT01", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD02") {
        $orby = [["PZUD02", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT02") {
        $orby = [["PZUT02", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD03") {
        $orby = [["PZUD03", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT03") {
        $orby = [["PZUT03", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD04") {
        $orby = [["PZUD04", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT04") {
        $orby = [["PZUT04", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD05") {
        $orby = [["PZUD05", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT05") {
        $orby = [["PZUT05", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD06") {
        $orby = [["PZUD06", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT06") {
        $orby = [["PZUT06", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD07") {
        $orby = [["PZUD07", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT07") {
        $orby = [["PZUT07", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD08") {
        $orby = [["PZUD08", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT08") {
        $orby = [["PZUT08", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD09") {
        $orby = [["PZUD09", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT09") {
        $orby = [["PZUT09", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD19") {
        $orby = [["PZUD19", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT19") {
        $orby = [["PZUT19", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD11") {
        $orby = [["PZUD11", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT11") {
        $orby = [["PZUT11", "A", "Total"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUD12") {
        $orby = [["PZUD12", "A", "Dependent"], ["PZPN", "A", "Item Number"]];
    } elseif ($sequence == "PZUT12") {
        $orby = [["PZUT12", "A", "Total"], ["PZPN", "A", "Item Number"]];
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD") {
    $andOr = $_POST['andOr'];
    require_once 'WildCardClear.php';
    $returnValue = Build_WildCard("PZLVL", "Level", $_POST['srchLevel'], "U", $_POST['operLevel'], "A");
    $returnValue = Build_WildCard("PZSEQN", "Routing Sequence", $_POST['srchRSEQ'], "", $_POST['operRSEQ'], "N");
    $returnValue = Build_WildCard("PZPN", "Item Number", $_POST['srchItem'], "U", $_POST['operItem'], "A");
    $returnValue = Build_WildCard("upper(PZIMDS)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
    $returnValue = Build_WildCard("PZUOMS", "Stocking UOM", $_POST['srchStockUOM'], "U", $_POST['operStockUOM'], "A");
    $returnValue = Build_WildCard("PZPTYP", "Part Type", $_POST['srchPartType'], "U", $_POST['operPartType'], "A");
    $returnValue = Build_WildCard("PZSTWC", "Backflush Code", $_POST['srchBackFlush'], "U", $_POST['operBackFlush'], "A");
    $returnValue = Build_WildCard("PZQPER", "Quantity Per", $_POST['srchQPER'], "", $_POST['operQPER'], "N");
    $returnValue = Build_WildCard("PZYTDU", "YTD Usage", $_POST['srchYTDU'], "", $_POST['operYTDU'], "N");
    $returnValue = Build_WildCard("PZYTDL", "Last Year YTD Usage", $_POST['srchYTDL'], "", $_POST['operYTDL'], "N");
    $returnValue = Build_WildCard("PZTOTU", "Last Year Total Usage", $_POST['srchTOTU'], "", $_POST['operTOTU'], "N");
    $returnValue = Build_WildCard("PZOHQT", "On Hand Quantity", $_POST['srchOHQT'], "", $_POST['operOHQT'], "N");
    $returnValue = Build_WildCard("PZAVQT", "Quantity Available", $_POST['srchAVQT'], "", $_POST['operAVQT'], "N");
    if ($sec_01 == 'Y') {
        $returnValue = Build_WildCard("PZTUCC", "Cost", $_POST['srchTUCC'], "", $_POST['operTUCC'], "N");
        $returnValue = Build_WildCard("PZEXTC", "Extended Cost", $_POST['srchEXTC'], "", $_POST['operEXTC'], "N");
    }
    if (!is_null($bucketIncr)) {
        for ($i = 1; $i <= 12; $i++) {
            if (isset($fDates[$i])) {
                $nbr = str_pad($i, 2, "0", STR_PAD_LEFT);
                $incrDesc = ($bucketIncr == 'D') ? ' Days' : ' Months';
                $days = '(' . $fDays[$i] . ' - ' . $tDays[$i] . $incrDesc . ')';
                $dQty = 'PZUD' . $nbr;
                $srch = 'srch' . $dQty;
                $oper = 'oper' . $dQty;
                $returnValue = Build_WildCard($dQty, "Dependent ({$days})", $_POST[$srch], "", $_POST[$oper], "N");
                $tQty = 'PZUT' . $nbr;
                $srch = 'srch' . $tQty;
                $oper = 'oper' . $tQty;
                $returnValue = Build_WildCard($tQty, "Total ({$days})", $_POST[$srch], "", $_POST[$oper], "N");
            }
        }
    }
    $returnValue = Build_WildCard("PZIMAC", "Status", $_POST['srchIMAC'], "U", $_POST['operIMAC'], "A");
    require_once 'WildCardUpdate.php';
}

$saveStartRow = $startRow;
require_once 'WhereUsedDashboardCharts.php';
$startRow = $saveStartRow;

if (isset($_GET['chgBox'])) {
    require "ViewCheckBoxUpdate.php";
}

require_once($docType);
print "\n <html> \n	<head>";
require_once($headInclude);
$formName = "Search";

print "\n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once($searchBanner);
print "\n <table $baseTable>";

require 'stmtSQLClear.php';
$stmtSQL = "Select * ";
$fileSQL = "PDM109";
$selectSQL = "PZXHND='{$profileHandle}' and PZCPN='{$itemNumber}'";
if ($viewCheckBox[0]) {
    $selectSQL .= " and PZLVL = '1' ";
}
if ($viewCheckBox[1]) {
    $selectSQL .= " and PZIMAC <> 'I' ";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
// echo $stmtSQL;

$qsOpt = "\n <option value=\"PZLVL|null|Level|A|U\" title=\"Level\">Level";
$qsOpt .= "\n <option value=\"PZSEQN|null|Routing Seq|N|\" title=\"Routing Sequence\">Routing Seq";
$qsOpt .= "\n <option value=\"PZPN|null|Item Number|A|U\" title=\"Item Number\" SELECTED>Item Number";
$qsOpt .= "\n <option value=\"PZIMDS|null|Description|A|U\" title=\"Description\">Description";
$qsOpt .= "\n <option value=\"PZUOMS|null|Stocking UOM|A|U\" title=\"Stocking UOM\">Stocking UOM";
$qsOpt .= "\n <option value=\"PZPTYP|null|Part Type|A|U\" title=\"Part Type\">Part Type";
$qsOpt .= "\n <option value=\"PZSTWC|null|Backflush Code|A|U\" title=\"Backflush Code\">Backflush Code";
$qsOpt .= "\n <option value=\"PZQPER|null|Quantity Per|N|\" title=\"Quantity Per\">Quantity Per";
$qsOpt .= "\n <option value=\"PZYTDU|null|YTD Usage|N|\" title=\"YTD Usage\">YTD Usage";
$qsOpt .= "\n <option value=\"PZYTDL|null|Last Year YTD Usage|N|\" title=\"Last Year YTD Usage\">Last Year YTD Usage";
$qsOpt .= "\n <option value=\"PZTOTU|null|Last Year Total Usage|N|\" title=\"Last Year Total Usage\">Last Year Total Usage";
if (!is_null($bucketIncr)) {
    for ($i = 1; $i <= 12; $i++) {
        if (isset($fDates[$i])) {
            $nbr = str_pad($i, 2, "0", STR_PAD_LEFT);
            $dQty = 'PZUD' . $nbr;
            $tQty = 'PZUT' . $nbr;
            $incrDesc = ($bucketIncr == 'D') ? ' Days' : ' Months';
            $days = '(' . $fDays[$i] . ' - ' . $tDays[$i] . $incrDesc . ')';
            $qsOpt .= "\n <option value=\"{$dQty}|null|Dependent {$days}|N|\" title=\"Dependent {$days}\">Dependent {$days}";
            $qsOpt .= "\n <option value=\"{$tQty}|null|Total {$days}|N|\" title=\"Total {$days}\">Total {$days}";
        }
    }
}
$qsOpt .= "\n <option value=\"PZOHQT|null|On Hand Quantity|N|\" title=\"On Hand Quantity\">On Hand Quantity";
$qsOpt .= "\n <option value=\"PZAVQT|null|Quantity Available|N|\" title=\"Quantity Available\">Quantity Available";
if ($sec_01 == 'Y') {
    $qsOpt .= "\n <option value=\"PZTUCC|null|Cost|N\" title=\"Cost\">Cost";
    $qsOpt .= "\n <option value=\"PZEXTC|null|Extended Cost|N\" title=\"Extended Cost\">Extended Cost";
}
$qsOpt .= "\n <option value=\"PZIMAC|null|Status|A|U\" title=\"Status\">Status";
print "<table $contentTable> <tr>";
print "<tr><th colspan=\"11\">";
require 'QuickSearchOption.php';
print "</th>";

if (!is_null($bucketIncr)) {
    for ($i = 1; $i <= 12; $i++) {
        if (isset($fDates[$i])) {
            $incrDesc = ($bucketIncr == 'D') ? ' Days' : ' Months';
            $days = $fDays[$i] . ' - ' . $tDays[$i] . $incrDesc . '';
            $desc = DateFromCYMD($fDates[$i]) . ' to ' . DateFromCYMD($tDates[$i]);
            print "\n     <td class=\"colhdr\"  colspan=\"2\" style='border-bottom:1pt solid white'><span onmouseover=\"this.style.cursor='help';\" title=\"{$desc}\">$days</span></td>";
        }
    }
}
print "</tr>";

$returnValue = OrderBy_Sort("PZRSEQ");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Level\" title=\"Sequence By Level\">{$sortPoint}Level</a></th>";

$returnValue = OrderBy_Sort("PZSEQN");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=RtgSeq\" title=\"Sequence By Routing Sequence\">{$sortPoint}Routing<br>Seq</a></th>";

$returnValue = OrderBy_Sort("PZPN");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Item\" title=\"Sequence By Item Number\">{$sortPoint}Item Number</a></th>";

$returnValue = OrderBy_Sort("PZIMDS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\" title=\"Sequence By Description, Component\">{$sortPoint}Description</a></th>";

$returnValue = OrderBy_Sort("PZUOMS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StockUOM\" title=\"Sequence By Stocking UOM, Item\">{$sortPoint}Stock<br>UOM</a></th>";

$returnValue = OrderBy_Sort("PZPTYP");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PartType\" title=\"Sequence By Part Type, Item\">{$sortPoint}Part<br>Type</a></th>";

$returnValue = OrderBy_Sort("PZSTWC");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BackFlush\" title=\"Sequence By Backflush Code, Item\">{$sortPoint}Bkf<br>Cde</a></th>";

$returnValue = OrderBy_Sort("PZQPER");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyPer\" title=\"Sequence By Quantity Per, Item\">{$sortPoint}Quantity<br>Per</a></th>";

$returnValue = OrderBy_Sort("PZYTDU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=YTDUsage\" title=\"Sequence By YTD Usage, Item\">{$sortPoint}YTD<br>Usage</a></th>";

$returnValue = OrderBy_Sort("PZYTDL");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LastUsage\" title=\"Sequence By Last Year YTD Usage, Item\">{$sortPoint}Last Year<br>YTD Usage</a></th>";

$returnValue = OrderBy_Sort("PZTOTU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LastTotalUsage\" title=\"Sequence By Last Year Total Usage, Item\">{$sortPoint}Last Year<br>Total Usage</a></th>";

if (!is_null($bucketIncr)) {
    for ($i = 1; $i <= 12; $i++) {
        if (isset($fDates[$i])) {
            $nbr = str_pad($i, 2, "0", STR_PAD_LEFT);
            $dQty = 'PZUD' . $nbr;
            $tQty = 'PZUT' . $nbr;

            $returnValue = OrderBy_Sort($dQty);
            $sortVar = $returnValue['sortedBy'];
            $sortPoint = $returnValue['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence={$dQty}\" title=\"Sequence By Dependent, Item\">{$sortPoint}Dependent</a></th>";

            $returnValue = OrderBy_Sort($tQty);
            $sortVar = $returnValue['sortedBy'];
            $sortPoint = $returnValue['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence={$tQty}\" title=\"Sequence By Total, Item\">{$sortPoint}Total</a></th>";
        }
    }
}

$returnValue = OrderBy_Sort("PZOHQT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OnHand\" title=\"Sequence By On Hand Quantity, Item\">{$sortPoint}On Hand<br>Quantity</a></th>";

$returnValue = OrderBy_Sort("PZAVQT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Avail\" title=\"Sequence By Quantity Available, Item\">{$sortPoint}Quantity<br>Available</a></th>";

if ($sec_01 == 'Y') {
    $returnValue = OrderBy_Sort("PZTUCC");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Cost\" title=\"Sequence By Cost, Item\">{$sortPoint}Cost</a></th>";

    $returnValue = OrderBy_Sort("PZEXTC");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ExtCost\" title=\"Sequence By Extended Cost, Item\">{$sortPoint}Extended<br>Cost</a></th>";
}

$returnValue = OrderBy_Sort("PZIMAC");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Inactive\" title=\"Sequence By Inactive, Item\">{$sortPoint}Status</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    require 'SetRowClass.php';
    $F_Desc = Format_Quote($row['PZIMDS']);
    print "\n <tr class=\"$rowClass\">";
    $pn = trim($row[PZPN]);
    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}ExplosionDashboard.php{$genericVarBase}&amp;tag=Edit_Data&amp;itemNumber={$pn}&amp;plantNumber={$plantNumber}\" title=\"Indented Explosion Dashboard\">$row[PZLVL]</a></td> ";
    print "\n     <td class=\"colnmbr\">$row[PZSEQN]</td>";
    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PZPN]}&amp;itemDescription={$F_Desc}\" title=\"View Item\">$row[PZPN]</a></td> ";
    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemPlantSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PZPN]}&amp;plantNumber={$plantNumber}\" title=\"View Item/Plant\">$row[PZIMDS]</a></td> ";
    $uomDesc = RetValue("UMUOM='$row[PZUOMS]'", "HDUOM", "UMUMLD");
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$uomDesc}\">$row[PZUOMS]</span></td>";
    $partTypeDesc = RetValue("FLTYPE='PARTTYPE' and FLVALU='$row[PZPTYP]'", "SYFLAG", "FLDESC");
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$partTypeDesc}\">$row[PZPTYP]</span></td>";
    $backflushDesc = RetValue("FLTYPE='BACKFLUSH' and FLVALU='$row[PZSTWC]'", "SYFLAG", "FLDESC");
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$backflushDesc}\">$row[PZSTWC]</span></td>";
    $F_PZQPER = Format_Nbr($row[PZQPER], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_PZQPER</td>";
    $F_PZYTDU = Format_Nbr($row[PZYTDU], $qtyNbrDec, $qtyEditCode, '', '', '');
    $pct = ($curYrTotal > 0) ? ($row[PZYTDU] / $curYrTotal) * 100 : 0;
    $F_pctTot = number_format($pct, 2);
    $pctTotDesc = "Percent of Total = {$F_pctTot}  ( {$F_PZYTDU}/ {$F_curYrTotal})";
    print "\n     <td class=\"colnmbr\"><span onmouseover=\"this.style.cursor = 'help';\" title=\"{$pctTotDesc}\">$F_PZYTDU</span></td>";
    $F_PZYTDL = Format_Nbr($row[PZYTDL], $qtyNbrDec, $qtyEditCode, '', '', '');
    $pct = ($lastYrYTDTotal > 0) ? ($row[PZYTDL] / $lastYrYTDTotal) * 100 : 0;
    $F_pctTot = number_format($pct, 2);
    $pctTotDesc = "Percent of Total = {$F_pctTot}  ( {$F_PZYTDL}/ {$F_lastYrYTDTotal})";
    print "\n     <td class=\"colnmbr\"><span onmouseover=\"this.style.cursor = 'help';\" title=\"{$pctTotDesc}\">$F_PZYTDL</span></td>";
    $F_PZTOTU = Format_Nbr($row[PZTOTU], $qtyNbrDec, $qtyEditCode, '', '', '');
    $pct = ($lastYrTotal > 0) ? ($row[PZTOTU] / $lastYrTotal) * 100 : 0;
    $F_pctTot = number_format($pct, 2);
    $pctTotDesc = "Percent of Total = {$F_pctTot}  ( {$F_PZTOTU}/ {$F_lastYrTotal})";
    print "\n     <td class=\"colnmbr\"><span onmouseover=\"this.style.cursor = 'help';\" title=\"{$pctTotDesc}\">$F_PZTOTU</span></td>";

    if (!is_null($bucketIncr)) {
        for ($i = 1; $i <= 12; $i++) {
            if (isset($fDates[$i])) {
                $nbr = str_pad($i, 2, "0", STR_PAD_LEFT);
                $dQty = 'PZUD' . $nbr;
                $F_dQty = Format_Nbr($row[$dQty], $qtyNbrDec, $qtyEditCode, '', '', '');
                $tQty = 'PZUT' . $nbr;
                $F_tQty = Format_Nbr($row[$tQty], $qtyNbrDec, $qtyEditCode, '', '', '');
                $pctTot = 'PZUP' . $nbr;
                $F_pctTot = Format_Nbr($row[$pctTot], $pctNbrDec, $pctEditCode, '', '', '');
                $pctTotDesc = 'Percent of Total = ' . $F_pctTot;
                print "\n     <td class=\"colnmbr\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$pctTotDesc}\">$F_dQty</span></td>";
                print "\n     <td class=\"colnmbr\">$F_tQty</td>";
            }
        }
    }

    $F_PZOHQT = Format_Nbr($row[PZOHQT], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}HdList.php{$genericVarBase}&amp;tblID=506&amp;fKey1=IMITEM&amp;fVal1={$row[PZPN]}\" title=\"View Stock Variance\">$F_PZOHQT</a></td> ";
    $F_PZAVQT = Format_Nbr($row[PZAVQT], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}AvailableToPromise.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PZPN]}&amp;plantNumber={$plantNumber}\" title=\"View Available To Promise\">$F_PZAVQT</a></td> ";

    if ($sec_01 == 'Y') {
        $F_PZTUCC = Format_Nbr($row[PZTUCC], $cstNbrDec, $cstEditCode, '', '', '');
        if ($row[PZPTYP] == 'M' || $row[PZPTYP] == 'B') {
            print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}CostSimulatorDisplay.d2w/BUILD{$altVarBase}&amp;itemNumber={$row[PZPN]}&amp;fromPlant={$plantNumber}&amp;plantNumber={$plantNumber}\" title=\"Cost Simulator\">$F_PZTUCC</a></td> ";
        } else {
            print "\n     <td class=\"colnmbr\">$F_PZTUCC</td> ";
        }
        $F_PZEXTC = Format_Nbr($row[PZEXTC], $amtNbrDec, $amtEditCode, '', '', '');
        $costDesc = "Quantity On Hand * Cost ($row[PZEXTC] = {$row[PZOHQT]} * {$row[PZTUCC]})";
        print "\n     <td class=\"colnmbr\"><span onmouseover=\"this.style.cursor = 'help';\" title=\"{$costDesc}\">$F_PZEXTC</span></td>";
    }
    $inactive = ($row[PZUOMS] == 'I') ? 'Inactive' : 'Active';
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$inactive}\">$row[PZIMAC]</span></td>";
    print "\n </tr>";
    $startRow++;
    $rowCount++;
}
if ($rowCount == 0) {
    require 'NoRecordsFound.php';
}

print "</table>";
print $hrTagAttr;
require_once 'Copyright.php';

function Update_Work_File($edtVar)
{
    global $profileHandle;
    $edtVar .= "}{";

    $pgmName = 'HPD109_W';
    $pgmCall = [["Name" => "profileHandle", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "64"],
        ["Name" => "edtVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"]];
    $pgm = i5_program_prepare("$pgmName", $pgmCall);
    if (!$pgm) {
        die("<br>Validate_Data ($pgmName) prepare error. Error Number=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = ["profileHandle" => $profileHandle, "edtVar" => $edtVar];
    $parmOut = ["profileHandle" => "profileHandle", "edtVar" => "edtVar"];

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br>Update Work File ($pgmName) call errno=" . i5_errno() . " msg=" . i5_errormsg());
    }
    $returnValue['edtVar'] = $edtVar;
    return $returnValue;
}

function Get_Qty_ItemPlant()
{
    global $HDPRRL, $plantNumber, $itemNumber;
    $edtVar = "@@pdrl{$HDPRRL}";
    $edtVar .= "}{@@plt@{$plantNumber}";
    $edtVar .= "}{@@item{$itemNumber}";
    $edtVar .= "}{";

    $pgmName = 'HHDSWQ_W';
    $pgmCall = [["Name" => "edtVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"]];
    $pgm = i5_program_prepare("$pgmName", $pgmCall);
    if (!$pgm) {
        die("<br>Validate_Data ($pgmName) prepare error. Error Number=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = ["edtVar" => $edtVar];
    $parmOut = ["edtVar" => "edtVar"];

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br>Get Quatity Item/Plant ($pgmName) call errno=" . i5_errno() . " msg=" . i5_errormsg());
    }
    $returnValue['edtVar'] = $edtVar;
    return $returnValue;
}

function downloadToCsv()
{
    global $i5Connect, $profileHandle, $itemNumber, $plantNumber, $parentCost, $extParentCost, $itemDesc,
           $qtyOnHand, $qtyAAU, $qtyAval, $qtyFlrStk, $pegReq, $sec_01, $fDays, $fDates, $tDays, $bucketIncr,
           $curYrTotal, $lastYrTotal, $lastYrYTDTotal;

    // output headers so that the file is downloaded rather than displayed
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    $csvFile = "Where_Used_{$plantNumber}_{$itemNumber}";
    header('Content-Disposition: attachment; filename="' . $csvFile . '.csv"');
    header('Content-Transfer-Encoding: binary');

    // open file pointer to standard output
    $file = fopen('php://output', 'w');

    // add BOM to fix UTF-8 in Excel
    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    ob_end_clean();
    $csvHdr = [];
    $csvHdr[] = "Row Seq";
    $csvHdr[] = "Plant";
    $csvHdr[] = "Component Item";
    $csvHdr[] = "Component Desc";
    if ($sec_01 == 'Y') {
        $csvHdr[] = "Component Cost";
        $csvHdr[] = "Component Extended Cost";
    }
    $csvHdr[] = "Component Qty On Hand";
    $csvHdr[] = "Component AAU";
    $csvHdr[] = "Component Qty Avail";
    $csvHdr[] = "Component Qty Floorstock";
    $csvHdr[] = "Component Pegged Req";
    $csvHdr[] = "Level";
    $csvHdr[] = "Routing Seq";
    $csvHdr[] = "Item Number";
    $csvHdr[] = "Description";
    $csvHdr[] = "Stock UOM";
    $csvHdr[] = "Part Type";
    $csvHdr[] = "Backflush";
    $csvHdr[] = "Order Policy";
    $csvHdr[] = "Lead Time Days";
    $csvHdr[] = "Lead Time Code";
    $csvHdr[] = "Quantity Per";
    $csvHdr[] = "YTD Usage";
    $csvHdr[] = "YTD Usage (Percent of Total)";
    $csvHdr[] = "Last Year YTD Usage";
    $csvHdr[] = "Last Year YTD Usage (Percent of Total)";
    $csvHdr[] = "Last Year Total Usage";
    $csvHdr[] = "Last Year Total Usage (Percent of Total)";
    $csvHdr[] = "OnHand Quantity";
    $csvHdr[] = "OnHand Floorstock";
    $csvHdr[] = "OnHand Receiving";
    $csvHdr[] = "Qty Held In Stock";
    $csvHdr[] = "Quantity On Order";
    $csvHdr[] = "Qty Committed Mfg";
    $csvHdr[] = "Available Quantity";
    if (!is_null($bucketIncr)) {
        for ($i = 1; $i <= 12; $i++) {
            if (isset($fDates[$i])) {
                $csvHdr[] = 'Dependent (' . $fDays[$i] . ' to ' . $tDays[$i] . ' Days)';
                $csvHdr[] = 'Total (' . $fDays[$i] . ' to ' . $tDays[$i] . ' Days)';
                $csvHdr[] = 'Percent of Total (' . $fDays[$i] . ' to ' . $tDays[$i] . ' Days)';
            }
        }
    }
    if ($sec_01 == 'Y') {
        $csvHdr[] = "Cost";
        $csvHdr[] = "Extended Cost";
    }
    $csvHdr[] = "Inactive";
    $csvHdr[] = "Component Disposition";
    $csvHdr[] = "Inventory Use-Up Date";
    $csvHdr[] = "Accounting Lot Size";
    $csvHdr[] = "Fixed Order Quantity";
    $csvHdr[] = "Minimum Order Quantity";
    $csvHdr[] = "Maximum Order Quantity";
    $csvHdr[] = "Multiple Order Quantity";
    $csvHdr[] = "Safety Stock Quantity";
    fputcsv($file, $csvHdr);

    require 'stmtSQLClear.php';
    $stmtSQL = "Select * ";
    $fileSQL = "PDM109";
    $selectSQL = "PZXHND='{$profileHandle}' and PZCPN='{$itemNumber}'";
    require 'stmtSQLSelect.php';
    $stmtSQL .= " Order By PZRSEQ ";
    require 'stmtSQLEnd.php';
    require 'stmtSQLTotalRows.php';
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
    $startRow = 1;
    while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
        $csvDtl = [];
        $csvDtl[] = $row[PZRSEQ];
        $csvDtl[] = $row[PZPLT];
        $csvDtl[] = trim($row[PZCPN]);
        $csvDtl[] = trim($itemDesc);
        if ($sec_01 == 'Y') {
            $csvDtl[] = str_replace("&nbsp;", "", $parentCost);
            $csvDtl[] = str_replace("&nbsp;", "", $extParentCost);
        }
        $csvDtl[] = str_replace("&nbsp;", "", $qtyOnHand);
        $csvDtl[] = str_replace("&nbsp;", "", $qtyAAU);
        $csvDtl[] = str_replace("&nbsp;", "", $qtyAval);
        $csvDtl[] = str_replace("&nbsp;", "", $qtyFlrStk);
        $csvDtl[] = str_replace("&nbsp;", "", $pegReq);
        $csvDtl[] = trim($row[PZLVL]);
        $csvDtl[] = $row[PZSEQN];
        $csvDtl[] = trim($row[PZPN]);
        $csvDtl[] = trim($row[PZIMDS]);
        $csvDtl[] = $row[PZUOMS];
        $csvDtl[] = $row[PZPTYP];
        $csvDtl[] = $row[PZSTWC];
        $csvDtl[] = $row[PZOPC];
        $csvDtl[] = $row[PZLTDY];
        $csvDtl[] = $row[PZLTC];
        $csvDtl[] = $row[PZQPER];
        $csvDtl[] = $row[PZYTDU];
        $csvDtl[] = ($curYrTotal > 0) ? number_format(($row[PZYTDU] / $curYrTotal) * 100,2) : 0;
        $csvDtl[] = $row[PZYTDL];
        $csvDtl[] = ($lastYrYTDTotal > 0) ? number_format(($row[PZYTDL] / $lastYrYTDTotal) * 100,2) : 0;
        $csvDtl[] = $row[PZTOTU];
        $csvDtl[] = ($lastYrTotal > 0) ? number_format(($row[PZTOTU] / $lastYrTotal) * 100,2) : 0;
        $csvDtl[] = $row[PZOHQT];
        $csvDtl[] = $row[PZOHFS];
        $csvDtl[] = $row[PZOHRC];
        $csvDtl[] = $row[PZQHSR];
        $csvDtl[] = $row[PZQOO];
        $csvDtl[] = $row[PZCMTO];
        $csvDtl[] = $row[PZAVQT];
        if (!is_null($bucketIncr)) {
            for ($i = 1; $i <= 12; $i++) {
                if (isset($fDates[$i])) {
                    $nbr = str_pad($i, 2, "0", STR_PAD_LEFT);
                    $dQty = 'PZUD' . $nbr;
                    $csvDtl[] = $row[$dQty];
                    $tQty = 'PZUT' . $nbr;
                    $csvDtl[] = $row[$tQty];
                    $pctTot = 'PZUP' . $nbr;
                    $csvDtl[] = $row[$pctTot];
                }
            }
        }
        if ($sec_01 == 'Y') {
            $csvDtl[] = $row[PZTUCC];
            $csvDtl[] = $row[PZEXTC];
        }
        $csvDtl[] = $row[PZIMAC];
        $csvDtl[] = $row[PZECDP];
        $csvDtl[] = ($row[PZIUPD] != '0001-01-01') ? $row[PZIUPD] : '';
        $csvDtl[] = $row[PZALTS];
        $csvDtl[] = $row[PZFOQ];
        $csvDtl[] = $row[PZMNQ];
        $csvDtl[] = $row[PZMXQ];
        $csvDtl[] = $row[PZMUQ];
        $csvDtl[] = $row[PZSSQ];
        fputcsv($file, $csvDtl);
        $startRow++;
    }
    fclose($file);
    ob_end_clean();   //    the buffer and never prints or returns anything.
}

// Session Date Formated
function GetChartData()
{
    global $i5Connect, $itemNumber, $plantNumber, $chartTotal, $chartIssues, $chartSales;

    require 'stmtSQLClear.php';
    $year1From = '1' . date("y") . '0101';
    $year1To = '1' . date("y") . '1231';
    $year2From = $year1From - 10000;
    $year2To = $year1To - 10000;
    $year3From = $year2From - 10000;
    $year3To = $year2To - 10000;

    $stmtSQL = " Select SUM(CASE WHEN DTTRDT between $year1From and $year1To THEN DTQTY ELSE 0 END) AS Year1
                   ,SUM(CASE WHEN DTTRDT between $year2From and $year2To THEN DTQTY ELSE 0 END) AS Year2
                   ,SUM(CASE WHEN DTTRDT between $year3From and $year3To THEN DTQTY ELSE 0 END) AS Year3
             From HDDTRN 
             Where DTITEM='{$itemNumber}' and DTPLT={$plantNumber} and DTIVTT in ('SLOE','ISIN','IFIN','KIOU')";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
    $chartTotal = db2_fetch_assoc($sqlResult);
    $chartTotal = (is_array($chartTotal)) ? $chartTotal : [];

    require 'stmtSQLClear.php';
    $year1From = '1' . date("y") . '0101';
    $year1To = DateTodayCYMD();
    $year2From = $year1From - 10000;
    $year2To = $year1To - 10000;
    $year3From = $year2From - 10000;
    $year3To = $year2To - 10000;
    $stmtSQL = " Select SUM(CASE WHEN DTTRDT between $year1From and $year1To THEN DTQTY ELSE 0 END) AS Year1
                   ,SUM(CASE WHEN DTTRDT between $year2From and $year2To THEN DTQTY ELSE 0 END) AS Year2
                   ,SUM(CASE WHEN DTTRDT between $year3From and $year3To THEN DTQTY ELSE 0 END) AS Year3
             From HDDTRN 
             Where DTITEM='{$itemNumber}' and DTPLT={$plantNumber} and DTIVTT in ('ISIN','IFIN','KIOU')";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
    $chartIssues = db2_fetch_assoc($sqlResult);
    $chartIssues = (is_array($chartIssues)) ? $chartIssues : [];

    require 'stmtSQLClear.php';
    $stmtSQL = " Select SUM(CASE WHEN DTTRDT between $year1From and $year1To THEN DTQTY ELSE 0 END) AS Year1
                   ,SUM(CASE WHEN DTTRDT between $year2From and $year2To THEN DTQTY ELSE 0 END) AS Year2
                   ,SUM(CASE WHEN DTTRDT between $year3From and $year3To THEN DTQTY ELSE 0 END) AS Year3
             From HDDTRN 
             Where DTITEM='{$itemNumber}' and DTPLT={$plantNumber} and DTIVTT in ('SLOE')";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
    $chartSales = db2_fetch_assoc($sqlResult);
    $chartSales = (is_array($chartSales)) ? $chartSales : [];
}

?>
