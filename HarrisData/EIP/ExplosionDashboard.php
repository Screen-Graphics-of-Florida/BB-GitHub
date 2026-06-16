<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$itemNumber = $_GET['itemNumber'];
$plantNumber = $_GET['plantNumber'];
$fDate = (isset($_POST['fDate'])) ? $_POST['fDate'] : null;
$fDate = (is_null($fDate)) ? $_GET['fDate'] : $fDate;
$tDate = (isset($_POST['tDate'])) ? $_POST['tDate'] : null;
$tDate = (is_null($tDate)) ? $_GET['tDate'] : $tDate;
$reqQty = (isset($_POST['reqQty'])) ? $_POST['reqQty'] : null;
$reqQty = (is_null($reqQty)) ? $_GET['reqQty'] : $reqQty;
$vendorPassed = (isset($itemNumber) && trim($itemNumber) != '') ? true : null;
$toggle = (isset($_GET['toggle'])) ? $_GET['toggle'] : null;
$downLoadCsv = (isset($_GET['downLoadCsv'])) ? $_GET['downLoadCsv'] : null;
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

$dftAAU = (isset($_GET['dftAAU'])) ? $_GET['dftAAU'] : null;
if ($dftAAU) {
    $reqQty = $_GET['dftAAU'];
    if ($reqQty == 1) {
        $fDate = '';
        $tDate = '';
    } else {
        $fDate = '0101' . date("y");
        $tDate = date("mdy");
    }
    $tag = "Edit_Data";
}

if ($tag == "Edit_Data") {
    $data = Update_Work_File($fDate, $tDate, $reqQty);
    $edtVar = $data['edtVar'];
    $reqQty = Decat_Field("@@rqty", $edtVar);
}

$page_title = "Explosion Dashboard";
$scriptName = "ExplosionDashboard.php";
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

$stmtSQL = " Select * From HDPLNT Where PLPLNT=$plantNumber ";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$plantRow = db2_fetch_assoc ( $sqlResult );
$plantName = $plantRow['PLNAME'];

$itemDesc = RetValue("IMITEM='{$itemNumber}'", "HDIMST", "IMIMDS");
$itemPltQty = Get_Qty_ItemPlant();
$edtVar = $itemPltQty['edtVar'];
$OnHand = Decat_Field("@@ohqt", $edtVar);
$qtyOnHand = Format_Nbr($OnHand, $qtyNbrDec, $qtyEditCode, '', '', '');
$qtyAAU = Decat_Field("@@aau@", $edtVar);
$F_qtyAAU = Format_Nbr($qtyAAU, $qtyNbrDec, $qtyEditCode, '', '', '');
$qtyFlrStk = Decat_Field("@@ohfs", $edtVar);
$qtyFlrStk = Format_Nbr($qtyFlrStk, $qtyNbrDec, $qtyEditCode, '', '', '');
$qtyAval = Decat_Field("@@aval", $edtVar);
$qtyAval = Format_Nbr($qtyAval, $qtyNbrDec, $qtyEditCode, '', '', '');
$pegReq = RetValue("PGPPLT={$plantNumber} and PGCPN='{$itemNumber}'", "MRMPGM", "coalesce(sum(pgqpeg),0)");
$pegReq = Format_Nbr($pegReq, $qtyNbrDec, $qtyEditCode, '', '', '');
$returnValue = Rtv_Unit_Cost($itemNumber, "", "", $plantNumber);
$Cost = $returnValue['cost'];
$parentCost = Format_Nbr($Cost, $cstNbrDec, $cstEditCode, '', '', '');
$extParentCost = Format_Nbr($Cost * $OnHand, $cstNbrDec, $cstEditCode, '', '', '');

$prog_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $prog_OPT['sec_01'];


$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = [["View:", "Level 1", $viewCheckBoxURL, "1", "0"], ["", "With Total Usage", $viewCheckBoxURL, "2", "0"]];

require_once 'FilterInit.php';
require_once 'FilterDefault.php';
if ($downLoadCsv) {
    downloadToCsv();
    exit();
}

require_once($docType);
print "\n
<html> <head> ";
require_once($headInclude);

$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
require_once 'CalendarInclude.php';
require_once 'CheckEnterChg.php';
require_once 'DateEdit.php';
require_once 'NumEdit.php';

print "\n function validate(chgForm) {";
print "\n if (editdate(document.Chg.fDate) ";
print "\n    && editdate(document.Chg.tDate)) ";
print "\n return true;";
print "\n }";
print "\n </script> ";

require_once($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "ExplosionDashboard";
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
    $whereUsed = str_replace('View Dashboard', 'View Where Used Dashboard', $dashboard);
    print "\n <a href=\"{$homeURL}{$phpPath}WhereUsedDashboard.php{$genericVarBase}&amp;loadFile=Y&amp;itemNumber={$itemNumber}&amp;plantNumber={$plantNumber}\" title=\"Where Used Dashboard\">$whereUsed &nbsp;</a>";
    print "<a href=\"{$baseURL}&amp;toggle=$viewHide\">$chartImg</a>";
    if ($itemNumber) {
        print "<a href=\"{$homeURL}{$cGIPath}ItemPlantSelect.d2w/REPORT{$altVarBase}&amp;plantNumber={$plantNumber}&amp;itemNumber={$itemNumber}\" title=\"View Item/Plant\">&nbsp; {$portalHome}</a>";
    }
    print "<a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;downLoadCsv=Y\" title=\"Download to CSV File\">&nbsp; {$downloadCsv}</a>";
    require_once 'FormatToPrint.php';
    if (file_exists('R15.0_Explosion_Dashboard.pdf')) {
        $designIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgHelp.gif\" title=\"View Explosion Dashboard Design\" alt=\"Help\">";
        print "<a href=\"R15.0_Explosion_Dashboard.pdf\" target=\"_blank\">&nbsp; {$designIcon}</a>";
    }
    print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";
print "\n <table $contentTable><colgroup><col width=\"40%\"><col width=\"30%\"><col width=\"30%\">";
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
print "\n <tr><td class=\"hdrtitl\">Anticipated Annual Usage: </td><td class=\"hdrdata\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;dftAAU={$qtyAAU}\" title=\"Default Anticipated Annual Usage to Quantity Required\">$F_qtyAAU</a></td></tr>";
Format_Header("Pegged Requirements", $pegReq, '');
print "\n </table></td>";
print "\n </tr></table> ";

require_once 'ConfMessageDisplay.php';

print $hrTagAttr;
$focusField = "fDate";
if ($errFound != "") {
    $focusField = "";
    if ($errFound != "") {
        $errVar = ErrVarErr($profileHandle, $errVar);
        $Err_fDate = DecatErr_Field("@@fdat", "fDate");
        $Err_tDate = DecatErr_Field("@@tdat", "tDate");
        $Err_reqQty = DecatErr_Field("@@rqty", "reqQty");
    }
    $fDate = Decat_Field("@@fdat", $edtVar);
    $tDate = Decat_Field("@@tdat", $edtVar);
    $reqQty = Decat_Field("@@rqty", $edtVar);
} else {
    $ACAFILE = "";
}

print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\">";
print "\n     <table $contentTable> ";
print '<tr>';
print '    <td class="colhdr">Usage From</td>';
print '    <td class="colhdr">Usage To</td>';
print '    <td class="colhdr">Quantity Required</td>';
print '</tr>';
print '<tr>';
Build_Fld_Entry("Usage From", "fDate", "inputdate", "Date", "", $fDate, $Err_fDate, "6", "6", "", "", "Y");
Build_Fld_Entry("Usage To", "tDate", "inputdate", "Date", "", $tDate, $Err_tDate, "6", "6", "", "", "Y");
Build_Fld_Entry("Quantity Required", "reqQty", "inputnmbr", "", "", $reqQty, $Err_reqQty, "12", "10", "", "", "Y");
print "\n <td><a href=\"javascript:check(document.Chg)\">$acceptImageMed</a></td>";
print "\n <td><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;dftAAU=1\"><img border=\"0\" src=\"{$homeURL}{$imagePath}lgSqStop.gif\" title=\"Reset Quantity Required\" alt=\"Reset\"></a></td>";
print '</tr>';
print "\n     </table> ";

if ($focusField != "") {
    print "\n <script TYPE=\"text/javascript\"> ";
    print "\n document.Chg.$focusField.focus(); ";
    print "\n </script> ";
}
print "\n </form>";

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
        $orby = [["PZSEQN", "A", "Routing Sequence"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "Comp") {
        $orby = [["PZCPN", "A", "Component"]];
    } elseif ($sequence == "Desc") {
        $orby = [["upper(PZIMDS)", "A", "Description"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "StockUOM") {
        $orby = [["PZUOMS", "A", "Stocking UOM"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "ProdClass") {
        $orby = [["IMPCLS", "A", "Product Class"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "VendorName") {
        $orby = [["VMVNA1", "A", "Primary Vendor"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "PartType") {
        $orby = [["PZPTYP", "A", "Part Type"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "QtyPer") {
        $orby = [["PZQPER", "A", "Quantity Per"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "QtyReq") {
        $orby = [["PZXPER", "A", "Quantity Required"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "TotalUsage") {
        $orby = [["PZQTOT", "A", "Total Usage"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "PctTotal") {
        $orby = [["PZQPCT", "A", "Percent of Total"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "QtyOnHand") {
        $orby = [["PZOHQT", "A", "Quantity On Hand"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "QtyAvail") {
        $orby = [["PZAVQT", "A", "Quantity Available"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "OnOrder") {
        $orby = [["ONORDER", "A", "On Order"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "Cost") {
        $orby = [["PZTUCC", "A", "Cost"], ["PZCPN", "A", "Component"]];
    } elseif ($sequence == "ExtCost") {
        $orby = [["PZEXTC", "A", "Extended Cost"], ["PZCPN", "A", "Component"]];
    } elseif (trim($plantRow['PLPAS1']) != '' && $sequence == "UserAlpha1") {
        $orby = [["IPUDA1", "A", trim($plantRow['PLPAS1'])], ["PZCPN", "A", "Component"]];
    } elseif (trim($plantRow['PLPAS2']) != '' && $sequence == "UserAlpha2") {
        $orby = [["IPUDA2", "A", trim($plantRow['PLPAS1'])], ["PZCPN", "A", "Component"]];
    } elseif (trim($plantRow['PLPAS3']) != '' && $sequence == "UserAlpha3") {
        $orby = [["IPUDA3", "A", trim($plantRow['PLPAS1'])], ["PZCPN", "A", "Component"]];
    } elseif (trim($plantRow['PLPNS1']) != '' && $sequence == "UserNbr1") {
        $orby = [["IPUDN1", "A", trim($plantRow['PLPNS1'])], ["PZCPN", "A", "Component"]];
    } elseif (trim($plantRow['PLPNS2']) != '' && $sequence == "UserNbr2") {
        $orby = [["IPUDN2", "A", trim($plantRow['PLPNS1'])], ["PZCPN", "A", "Component"]];
    } elseif (trim($plantRow['PLPNS3']) != '' && $sequence == "UserNbr3") {
        $orby = [["IPUDN3", "A", trim($plantRow['PLPNS1'])], ["PZCPN", "A", "Component"]];
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
    $returnValue = Build_WildCard("PZCPN", "Component", $_POST['srchComp'], "U", $_POST['operComp'], "A");
    $returnValue = Build_WildCard("upper(PZIMDS)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
    $returnValue = Build_WildCard("IMPCLS", "Product Class", $_POST['srchProdClass'], "U", $_POST['operProdClass'], "A");
    $returnValue = Build_WildCard("upper(VMVNA1)", "Primary Vendor", $_POST['srchVendorName'], "", $_POST['operVendorName'], "A");
    $returnValue = Build_WildCard("PZUOMS", "Stocking UOM", $_POST['srchStockUOM'], "U", $_POST['operStockUOM'], "A");
    if ($sec_01 == 'Y') {
        $returnValue = Build_WildCard("PZTUCC", "Cost", $_POST['srchTUCC'], "", $_POST['operTUCC'], "N");
        $returnValue = Build_WildCard("PZEXTC", "Extended Cost", $_POST['srchEXTC'], "", $_POST['operEXTC'], "N");
    }
    if (trim($plantRow['PLPAS1']) != '') {    $returnValue = Build_WildCard("IPUDA1", trim($plantRow['PLPAS1']), $_POST['srchUserAlpha1'], "U", $_POST['operUserAlpha1'], "A");}
    if (trim($plantRow['PLPAS2']) != '') {    $returnValue = Build_WildCard("IPUDA2", trim($plantRow['PLPAS2']), $_POST['srchUserAlpha2'], "U", $_POST['operUserAlpha2'], "A");}
    if (trim($plantRow['PLPAS3']) != '') {    $returnValue = Build_WildCard("IPUDA3", trim($plantRow['PLPAS3']), $_POST['srchUserAlpha3'], "U", $_POST['operUserAlpha3'], "A");}
    if (trim($plantRow['PLPNS1']) != '') {    $returnValue = Build_WildCard("IPUDN1", trim($plantRow['PLPNS1']), $_POST['srchUserNbr1'], "U", $_POST['operUserNbr1'], "N");}
    if (trim($plantRow['PLPNS2']) != '') {    $returnValue = Build_WildCard("IPUDN2", trim($plantRow['PLPNS2']), $_POST['srchUserNbr2'], "U", $_POST['operUserNbr2'], "N");}
    if (trim($plantRow['PLPNS3']) != '') {    $returnValue = Build_WildCard("IPUDN3", trim($plantRow['PLPNS3']), $_POST['srchUserNbr3'], "U", $_POST['operUserNbr3'], "N");}
    require_once 'WildCardUpdate.php';
}

$saveStartRow = $startRow;
require_once 'ExplosionDashboardCharts.php';
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
$stmtSQL = "Select PDM099.*, coalesce(IMPCLS,'') as IMPCLS, coalesce(PCPCDS,'') as PCPCDS, coalesce(IPVEND,0) as IPVEND, coalesce(VMVNA1,'') as VMVNA1,
            coalesce(IPBAC,'') as IPBAC, coalesce(BMBNA1,'') as BMBNA1, coalesce(IPITC,'') as IPITC, coalesce(ITDESC,'') as ITDESC, 
            coalesce(IPAAU,0) as IPAAU, coalesce(IPSHAU,0) as IPSHAU,
            coalesce(IPUDA1,'') as IPUDA1,coalesce(IPUDA2,'') as IPUDA2,coalesce(IPUDA3,'') as IPUDA3,
            coalesce(IPUDN1,0) as IPUDN1,coalesce(IPUDN2,0) as IPUDN2,coalesce(IPUDN3,0) as IPUDN3,
            coalesce(MOCOUNT,0) as MOCOUNT,coalesce(POCOUNT,0) as POCOUNT,
            Case When coalesce(MOCOUNT,0)>0 Then 'Y'
                 When coalesce(POCOUNT,0)>0 Then 'Y'
                 Else ' ' End as ONORDER";
$fileSQL = "PDM099 left join HDIMST on PZCPN=IMITEM
                   left join HDIPLT on PZCPN=IPITEM and PZPLT=IPPLT
                   left join HDVEND on IPVEND=VMVEND
                   left join HDBUYR on IPBAC=BMBUYR
                   left join HDPCLS on IMPCLS=PCPCLS
                   left join HDITYP on IPITC=ITITC
                   left join (Select b.PDITEM, count(*) as POCOUNT from POPOMS a inner join POPOMD b on a.POPO=b.PDPO
                              Where a.POSTAT='O' and b.PDSTAT='O' and b.PDPOLT<>'B' Group by b.PDITEM) z on z.PDITEM=PDM099.PZCPN
                   left join (Select c.OHPN, count(*) as MOCOUNT from HDMOHM c Where OHSTC<>'C' Group by c.OHPN) y on y.OHPN=PDM099.PZCPN";
$selectSQL = "PZXHND='{$profileHandle}' ";
if ($viewCheckBox[0]) {
    $selectSQL .= " and PZLVL = '1' ";
}
if ($viewCheckBox[1]) {
    $selectSQL .= " and PZQTOT > 0 ";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
// echo $stmtSQL;

$qsOpt = "\n <option value=\"PZLVL|null|Level|A|U\" title=\"Level\">Level";
$qsOpt .= "\n <option value=\"PZSEQN|null|Routing Seq|N|\" title=\"Routing Sequence\">Routing Seq";
$qsOpt .= "\n <option value=\"PZCPN|null|Component|A|U\" title=\"Component\" SELECTED>Component";
$qsOpt .= "\n <option value=\"PZIMDS|null|Description|A|U\" title=\"Description\">Description";
$qsOpt .= "\n <option value=\"PZUOMS|null|Stocking UOM|A|U\" title=\"Stocking UOM\">Stocking UOM";
$qsOpt .= "\n <option value=\"IMPCLS|null|Product Class|A|U\" title=\"Product Class\">Product Class";
$qsOpt .= "\n <option value=\"upper(VMVNA1)|null|Primary Vendor|A|U\" title=\"Primary Vendor\">Primary Vendor";
$qsOpt .= "\n <option value=\"PZPTYP|null|Part Type|A|U\" title=\"Part Type\">Part Type";
$qsOpt .= "\n <option value=\"PZQPER|null|Quantity Per|N|\" title=\"Quantity Per\">Quantity Per";
$qsOpt .= "\n <option value=\"PZXPER|null|Quantity Required|N\" title=\"Quantity Required\">Quantity Required";
$qsOpt .= "\n <option value=\"PZQTOT|null|Total Usage|N\" title=\"Total Usage\">Total Usage";
$qsOpt .= "\n <option value=\"PZQPCT|null|Percent of Total|N\" title=\"Percent of Total\">Percent of Total";
$qsOpt .= "\n <option value=\"PZOHQT|null|Quantity On Hand|N\" title=\"Quantity On Hand\">Quantity On Hand";
$qsOpt .= "\n <option value=\"PZAVQT|null|Quantity Available|N\" title=\"Quantity Available\">Quantity Available";
$qsOpt .= "\n <option value=\"ONORDER|null|On Order|A|U\" title=\"On Order\">On Order";
if ($sec_01 == 'Y') {
    $qsOpt .= "\n <option value=\"PZTUCC|null|Cost|N\" title=\"Cost\">Cost";
    $qsOpt .= "\n <option value=\"PZEXTC|null|Extended Cost|N\" title=\"Extended Cost\">Extended Cost";
}
if (trim($plantRow['PLPAS1']) != '') {$qsOpt .= "\n <option value=\"IPUDA1|null|$plantRow[PLPAS1]|A|U\" title=\"$plantRow[PLPAS1]\">$plantRow[PLPAS1]";}
if (trim($plantRow['PLPAS2']) != '') {$qsOpt .= "\n <option value=\"IPUDA2|null|$plantRow[PLPAS2]|A|U\" title=\"$plantRow[PLPAS2]\">$plantRow[PLPAS2]";}
if (trim($plantRow['PLPAS3']) != '') {$qsOpt .= "\n <option value=\"IPUDA3|null|$plantRow[PLPAS3]|A|U\" title=\"$plantRow[PLPAS3]\">$plantRow[PLPAS3]";}
if (trim($plantRow['PLPNS1']) != '') {$qsOpt .= "\n <option value=\"IPUDN1|null|$plantRow[PLPNS1]|A|U\" title=\"$plantRow[PLPNS1]\">$plantRow[PLPNS1]";}
if (trim($plantRow['PLPNS2']) != '') {$qsOpt .= "\n <option value=\"IPUDN2|null|$plantRow[PLPNS2]|A|U\" title=\"$plantRow[PLPNS2]\">$plantRow[PLPNS2]";}
if (trim($plantRow['PLPNS3']) != '') {$qsOpt .= "\n <option value=\"IPUDN3|null|$plantRow[PLPNS3]|A|U\" title=\"$plantRow[PLPNS3]\">$plantRow[PLPNS3]";}

print "<table $contentTable> <tr>";
print "<tr><th colspan=\"10\">";
require 'QuickSearchOption.php';
print "</th></tr>";

$returnValue = OrderBy_Sort("PZRSEQ");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Level\" title=\"Sequence By Level\">{$sortPoint}Level</a></th>";

$returnValue = OrderBy_Sort("PZSEQN");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=RtgSeq\" title=\"Sequence By Routing Sequence\">{$sortPoint}Routing<br>Seq</a></th>";

$returnValue = OrderBy_Sort("PZCPN");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Comp\" title=\"Sequence By Component\">{$sortPoint}Component</a></th>";

$returnValue = OrderBy_Sort("PZIMDS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\" title=\"Sequence By Description, Component\">{$sortPoint}Description</a></th>";

$returnValue = OrderBy_Sort("PZUOMS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StockUOM\" title=\"Sequence By Stocking UOM, Item\">{$sortPoint}Stock<br>UOM</a></th>";

$returnValue = OrderBy_Sort("IMPCLS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ProdClass\" title=\"Sequence By Product Class, Item\">{$sortPoint}Prod<br>Class</a></th>";

$returnValue = OrderBy_Sort("VMVNA1");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=VendorName\" title=\"Sequence By Primary Vendor, Item\">{$sortPoint}Primary<br>Vendor</a></th>";

$returnValue = OrderBy_Sort("PZPTYP");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PartType\" title=\"Sequence By Part Type, Item\">{$sortPoint}Part<br>Type</a></th>";

$returnValue = OrderBy_Sort("PZQPER");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyPer\" title=\"Sequence By Quantity Per, Item\">{$sortPoint}Quantity<br>Per</a></th>";

$returnValue = OrderBy_Sort("PZXPER");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyReq\" title=\"Sequence By Quantity Required, Item\">{$sortPoint}Quantity<br>Required</a></th>";

$returnValue = OrderBy_Sort("PZQTOT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TotalUsage\" title=\"Sequence By Total Usage, Item\">{$sortPoint}Total<br>Usage</a></th>";

$returnValue = OrderBy_Sort("PZQPCT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PctTotal\" title=\"Sequence By Percent of Total, Item\">{$sortPoint}Percent of<br>Total</a></th>";

$returnValue = OrderBy_Sort("PZOHQT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyOnHand\" title=\"Sequence By Quantity On Hand, Item\">{$sortPoint}Quantity<br>On Hand</a></th>";

$returnValue = OrderBy_Sort("PZAVQT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyAvail\" title=\"Sequence By Quantity Available, Item\">{$sortPoint}Quantity<br>Available</a></th>";

$returnValue = OrderBy_Sort("ONORDER");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OnOrder\" title=\"Sequence By On Order, Item\">{$sortPoint}On<br>Order</a></th>";

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
if (trim($plantRow['PLPAS1']) != '') {
    $returnValue = OrderBy_Sort("IPUDA1");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=UserAlpha1\" title=\"Sequence By {$plantRow['PLPAS1']}, Item\">{$sortPoint}{$plantRow['PLPAS1']}</a></th>";
}
if (trim($plantRow['PLPAS2']) != '') {
    $returnValue = OrderBy_Sort("IPUDA2");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=UserAlpha2\" title=\"Sequence By {$plantRow['PLPAS2']}, Item\">{$sortPoint}{$plantRow['PLPAS2']}</a></th>";
}
if (trim($plantRow['PLPAS3']) != '') {
    $returnValue = OrderBy_Sort("IPUDA3");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=UserAlpha3\" title=\"Sequence By {$plantRow['PLPAS3']}, Item\">{$sortPoint}{$plantRow['PLPAS3']}</a></th>";
}
if (trim($plantRow['PLPNS1']) != '') {
    $returnValue = OrderBy_Sort("IPUDN1");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=UserNbr1\" title=\"Sequence By {$plantRow['PLPNS1']}, Item\">{$sortPoint}{$plantRow['PLPNS1']}</a></th>";
}
if (trim($plantRow['PLPNS2']) != '') {
    $returnValue = OrderBy_Sort("IPUDN2");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=UserNbr2\" title=\"Sequence By {$plantRow['PLPNS2']}, Item\">{$sortPoint}{$plantRow['PLPNS2']}</a></th>";
}
if (trim($plantRow['PLPNS3']) != '') {
    $returnValue = OrderBy_Sort("IPUDN3");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=UserNbr3\" title=\"Sequence By {$plantRow['PLPNS3']}, Item\">{$sortPoint}{$plantRow['PLPNS3']}</a></th>";
}

print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    require 'SetRowClass.php';
    $F_Desc = Format_Quote($row['PZIMDS']);
    print "\n <tr class=\"$rowClass\">";
    $cpn = trim($row[PZCPN]);
    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}WhereUsedDashboard.php{$genericVarBase}&amp;loadFile=Y&amp;itemNumber={$cpn}&amp;plantNumber={$plantNumber}\" title=\"Indented Where Used Dashboard\">$row[PZLVL]</a></td> ";
    print "\n     <td class=\"colnmbr\">$row[PZSEQN]</td>";
    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PZCPN]}&amp;itemDescription={$F_Desc}\" title=\"View Item\">$row[PZCPN]</a></td> ";
    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemPlantSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PZCPN]}&amp;plantNumber={$plantNumber}\" title=\"View Item/Plant\">$row[PZIMDS]</a></td> ";
    $uomDesc = RetValue("UMUOM='$row[PZUOMS]'", "HDUOM", "UMUMLD");
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$uomDesc}\">$row[PZUOMS]</span></td>";
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$row[PCPCDS]}\">$row[IMPCLS]</span></td>";
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$row[IPVEND]}\">$row[VMVNA1]</span></td>";
    $partTypeDesc = RetValue("FLTYPE='PARTTYPE' and FLVALU='$row[PZPTYP]'", "SYFLAG", "FLDESC");
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$partTypeDesc}\">$row[PZPTYP]</span></td>";
    $F_PZQPER = Format_Nbr($row[PZQPER], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_PZQPER</td>";
    $F_PZXPER = Format_Nbr($row[PZXPER], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_PZXPER</td>";
    $F_PZQTOT = Format_Nbr($row[PZQTOT], $qtyNbrDec, $qtyEditCode, '', '', '');
    $qtyDesc = "Sold + Issued ($row[PZQTOT] = {$row[PZQSLD]} + {$row[PZQISS]})";
    print "\n     <td class=\"colnmbr\"><span onmouseover=\"this.style.cursor = 'help';\" title=\"{$qtyDesc}\">$F_PZQTOT</span></td>";
    $pctTotal = Format_Nbr($row[PZQPCT], '2', $pctEditCode, '', '', '%');
    $pctDesc = "Required / Total Usage ($row[PZQPCT] = {$row[PZXPER]} / {$row[PZQTOT]})";
    print "\n     <td class=\"colnmbr\"><span onmouseover=\"this.style.cursor = 'help';\" title=\"{$pctDesc}\">$pctTotal</span></td>";
    $F_PZOHQT = Format_Nbr($row[PZOHQT], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}HdList.php{$genericVarBase}&amp;tblID=506&amp;fKey1=IMITEM&amp;fVal1={$cpn}\" title=\"View Stock Variance\">$F_PZOHQT</a></td> ";
    $qtyClass = ($row['PZAVQT'] < $row['PZXPER']) ? 'oepriceover' : 'colnmbr';
    $F_PZAVQT = Format_Nbr($row[PZAVQT], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"$qtyClass\"><a href=\"{$homeURL}{$cGIPath}AvailableToPromise.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[PZCPN]}&amp;plantNumber={$plantNumber}\" title=\"View Available To Promise\">$F_PZAVQT</a></td> ";
    if ($row['MOCOUNT'] != 0) {
        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}HdList.php{$genericVarBase}&amp;tblID=445&amp;fKey1=OHPLT&amp;fVal1={$plantNumber}&amp;fKey2=OHPN&amp;fVal2={$cpn}\" title=\"View Manufacturing Order\">$row[ONORDER]</a></td> ";
    } elseif ($row['POCOUNT'] != 0) {
        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}HdList.php{$genericVarBase}&amp;tblID=186&amp;fKey1=PDITEM&amp;fVal1={$cpn}\" title=\"View All Open Items\">$row[ONORDER]</a></td> ";
    } else {
        print "\n     <td class=\"colalph\">$row[ONORDER]</td> ";
    }
    if ($sec_01 == 'Y') {
        $F_PZTUCC = Format_Nbr($row[PZTUCC], $cstNbrDec, $cstEditCode, '', '', '');
        if ($row[PZPTYP] == 'M' || $row[PZPTYP] == 'B') {
            print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}CostSimulatorDisplay.d2w/BUILD{$altVarBase}&amp;itemNumber={$row[PZCPN]}&amp;fromPlant={$plantNumber}&amp;plantNumber={$plantNumber}\" title=\"Cost Simulator\">$F_PZTUCC</a></td> ";
        } else {
            print "\n     <td class=\"colnmbr\">$F_PZTUCC</td> ";
        }
        $F_PZEXTC = Format_Nbr($row[PZEXTC], $amtNbrDec, $amtEditCode, '', '', '');
        $costDesc = "Quantity On Hand * Cost ($row[PZEXTC] = {$row[PZOHQT]} * {$row[PZTUCC]})";
        print "\n     <td class=\"colnmbr\"><span onmouseover=\"this.style.cursor = 'help';\" title=\"{$costDesc}\">$F_PZEXTC</span></td>";
    }
    if (trim($plantRow['PLPAS1']) != '') {print "\n <td class=\"colalph\">$row[IPUDA1]</td>";}
    if (trim($plantRow['PLPAS2']) != '') {print "\n <td class=\"colalph\">$row[IPUDA2]</td>";}
    if (trim($plantRow['PLPAS3']) != '') {print "\n <td class=\"colalph\">$row[IPUDA3]</td>";}
    if (trim($plantRow['PLPNS1']) != '') {print "\n <td class=\"colnmbr\">$row[IPUDN1]</td>";}
    if (trim($plantRow['PLPNS2']) != '') {print "\n <td class=\"colnmbr\">$row[IPUDN2]</td>";}
    if (trim($plantRow['PLPNS3']) != '') {print "\n <td class=\"colnmbr\">$row[IPUDN3]</td>";}
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

function Update_Work_File($fDate = null, $tDate = null, $reqQty = null)
{
    global $profileHandle, $plantNumber, $itemNumber;
    $edtVar = "@@plt@{$plantNumber}";
    $edtVar .= "}{@@item{$itemNumber}";
    $reqQty = (!is_null($reqQty)) ? $reqQty : 1;
    $edtVar .= "}{@@rqty{$reqQty}";
    if (!is_null($fDate)) {
        $edtVar .= "}{@@fdat{$fDate}";
    }
    if (!is_null($tDate)) {
        $edtVar .= "}{@@tdat{$tDate}";
    }
    $edtVar .= "}{";

    $pgmName = 'HSI255_W';
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
    global $i5Connect, $profileHandle, $wildCardSearch, $viewCheckBox, $itemNumber, $plantNumber, $parentCost, $extParentCost, $itemDesc, $qtyOnHand, $qtyAAU, $qtyAval, $qtyFlrStk, $pegReq, $sec_01, $plantRow;
    // output headers so that the file is downloaded rather than displayed
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    $csvFile = "Indented_Explosion_{$plantNumber}_{$itemNumber}";
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
    $csvHdr[] = "Parent Item";
    $csvHdr[] = "Parent Desc";
    if ($sec_01 == 'Y') {
        $csvHdr[] = "Parent Cost";
        $csvHdr[] = "Parent Extended Cost";
    }
    $csvHdr[] = "Parent Qty On Hand";
    $csvHdr[] = "Parent AAU";
    $csvHdr[] = "Parent Qty Avail";
    $csvHdr[] = "Parent Qty Floorstock";
    $csvHdr[] = "Parent Pegged Req";
    $csvHdr[] = "From Date";
    $csvHdr[] = "To Date";
    $csvHdr[] = "Quantity Required";
    $csvHdr[] = "Level";
    $csvHdr[] = "Routing Seq";
    $csvHdr[] = "Component Item";
    $csvHdr[] = "Component Desc";
    $csvHdr[] = "Stock UOM";
    $csvHdr[] = "Part Type";
    $csvHdr[] = "Product Class";
    $csvHdr[] = "Product Class Description";
    $csvHdr[] = "Buyer Analyst Code";
    $csvHdr[] = "Buyer Analyst Name";
    $csvHdr[] = "Inventory Type";
    $csvHdr[] = "Inventory Type Description";
    $csvHdr[] = "Anticipated Annual Usage";
    $csvHdr[] = "Sales History Annual Usage";
    $csvHdr[] = "Primary Vendor Number";
    $csvHdr[] = "Primary Vendor Name";
    $csvHdr[] = "Backflush";
    $csvHdr[] = "Order Policy";
    $csvHdr[] = "Lead Time Days";
    $csvHdr[] = "Lead Time Code";
    $csvHdr[] = "Quantity Per";
    $csvHdr[] = "Ext Quantity Per";
    $csvHdr[] = "OnHand Quantity";
    $csvHdr[] = "OnHand Floorstock";
    $csvHdr[] = "OnHand Receiving";
    $csvHdr[] = "Qty Held In Stock";
    $csvHdr[] = "Quantity On Order";
    $csvHdr[] = "Qty Committed Mfg";
    $csvHdr[] = "Available Quantity";
    $csvHdr[] = "Quantity Sold";
    $csvHdr[] = "Quantity Issued";
    $csvHdr[] = "Total Usage";
    $csvHdr[] = "Percent of Total";
    if ($sec_01 == 'Y') {
        $csvHdr[] = "Cost";
        $csvHdr[] = "Extended Cost";
    }
    $csvHdr[] = "Component Disposition";
    $csvHdr[] = "Inventory Use-Up Date";
    $csvHdr[] = "Accounting Lot Size";
    $csvHdr[] = "Fixed Order Quantity";
    $csvHdr[] = "Minimum Order Quantity";
    $csvHdr[] = "Maximum Order Quantity";
    $csvHdr[] = "Multiple Order Quantity";
    $csvHdr[] = "Safety Stock Quantity";
    if (trim($plantRow['PLPAS1']) != '') {$csvHdr[] = $plantRow['PLPAS1'];}
    if (trim($plantRow['PLPAS2']) != '') {$csvHdr[] = $plantRow['PLPAS2'];}
    if (trim($plantRow['PLPAS3']) != '') {$csvHdr[] = $plantRow['PLPAS3'];}
    if (trim($plantRow['PLPNS1']) != '') {$csvHdr[] = $plantRow['PLPNS1'];}
    if (trim($plantRow['PLPNS2']) != '') {$csvHdr[] = $plantRow['PLPNS2'];}
    if (trim($plantRow['PLPNS3']) != '') {$csvHdr[] = $plantRow['PLPNS3'];}
    fputcsv($file, $csvHdr);

    require 'stmtSQLClear.php';
    $stmtSQL = "Select PDM099.*, coalesce(IMPCLS,'') as IMPCLS, coalesce(PCPCDS,'') as PCPCDS, coalesce(IPVEND,0) as IPVEND, coalesce(VMVNA1,'') as VMVNA1,
            coalesce(IPBAC,'') as IPBAC, coalesce(BMBNA1,'') as BMBNA1, coalesce(IPITC,'') as IPITC, coalesce(ITDESC,'') as ITDESC, 
            coalesce(IPAAU,0) as IPAAU, coalesce(IPSHAU,0) as IPSHAU,
            coalesce(IPUDA1,'') as IPUDA1,coalesce(IPUDA2,'') as IPUDA2,coalesce(IPUDA3,'') as IPUDA3,
            coalesce(IPUDN1,0) as IPUDN1,coalesce(IPUDN2,0) as IPUDN2,coalesce(IPUDN3,0) as IPUDN3";
    $fileSQL = "PDM099 left join HDIMST on PZCPN=IMITEM
                   left join HDIPLT on PZCPN=IPITEM and PZPLT=IPPLT
                   left join HDVEND on IPVEND=VMVEND
                   left join HDBUYR on IPBAC=BMBUYR
                   left join HDPCLS on IMPCLS=PCPCLS
                   left join HDITYP on IPITC=ITITC";
    $selectSQL = "PZXHND = '{$profileHandle}' ";
    if ($viewCheckBox[0]) {
        $selectSQL .= " and PZLVL = '1' ";
    }
    if ($viewCheckBox[1]) {
        $selectSQL .= " and PZQTOT > 0 ";
    }
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
        $csvDtl[] = trim($row[PZPN]);
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
        $csvDtl[] = ($row[PZFDAT] > 0) ? Date_CYMD_ISO($row[PZFDAT]) : $row[PZFDAT];
        $csvDtl[] = ($row[PZTDAT] > 0) ? Date_CYMD_ISO($row[PZTDAT]) : $row[PZTDAT];
        $csvDtl[] = $row[PZQREQ];
        $csvDtl[] = trim($row[PZLVL]);
        $csvDtl[] = $row[PZSEQN];
        $csvDtl[] = trim($row[PZCPN]);
        $csvDtl[] = trim($row[PZIMDS]);
        $csvDtl[] = $row[PZUOMS];
        $csvDtl[] = $row[PZPTYP];
        $csvDtl[] = $row[IMPCLS];
        $csvDtl[] = $row[PCPCDS];
        $csvDtl[] = $row[IPBAC];
        $csvDtl[] = $row[BMBNA1];
        $csvDtl[] = $row[IPITC];
        $csvDtl[] = $row[ITDESC];
        $csvDtl[] = $row[IPAAU];
        $csvDtl[] = $row[IPSHAU];
        $csvDtl[] = $row[IPVEND];
        $csvDtl[] = $row[VMVNA1];
        $csvDtl[] = $row[PZSTWC];
        $csvDtl[] = $row[PZOPC];
        $csvDtl[] = $row[PZLTDY];
        $csvDtl[] = $row[PZLTC];
        $csvDtl[] = $row[PZQPER];
        $csvDtl[] = $row[PZXPER];
        $csvDtl[] = $row[PZOHQT];
        $csvDtl[] = $row[PZOHFS];
        $csvDtl[] = $row[PZOHRC];
        $csvDtl[] = $row[PZQHSR];
        $csvDtl[] = $row[PZQOO];
        $csvDtl[] = $row[PZCMTO];
        $csvDtl[] = $row[PZAVQT];
        $csvDtl[] = $row[PZQSLD];
        $csvDtl[] = $row[PZQISS];
        $csvDtl[] = $row[PZQTOT];
        $csvDtl[] = $row[PZQPCT];
        if ($sec_01 == 'Y') {
            $csvDtl[] = $row[PZTUCC];
            $csvDtl[] = $row[PZEXTC];
        }
        $csvDtl[] = $row[PZECDP];
        $csvDtl[] = ($row[PZIUPD] != '0001-01-01') ? $row[PZIUPD] : '';
        $csvDtl[] = $row[PZALTS];
        $csvDtl[] = $row[PZFOQ];
        $csvDtl[] = $row[PZMNQ];
        $csvDtl[] = $row[PZMXQ];
        $csvDtl[] = $row[PZMUQ];
        $csvDtl[] = $row[PZSSQ];
        if (trim($plantRow['PLPAS1']) != '') {$csvDtl[] = $row[IPUDA1];}
        if (trim($plantRow['PLPAS2']) != '') {$csvDtl[] = $row[IPUDA2];}
        if (trim($plantRow['PLPAS3']) != '') {$csvDtl[] = $row[IPUDA3];}
        if (trim($plantRow['PLPNS1']) != '') {$csvDtl[] = $row[IPUDN1];}
        if (trim($plantRow['PLPNS2']) != '') {$csvDtl[] = $row[IPUDN2];}
        if (trim($plantRow['PLPNS3']) != '') {$csvDtl[] = $row[IPUDN3];}
        fputcsv($file, $csvDtl);
        $startRow++;
    }
    fclose($file);
    ob_end_clean();   //    the buffer and never prints or returns anything.
}

?>
