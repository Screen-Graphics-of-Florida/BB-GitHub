<?php
require_once 'GetURLParm.php';
require_once 'BaseFormat.php';
require_once "EdtVar.php";
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'ErrorNoWarning.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$allUsers              = (isset($_GET['allUsers'])) ? $_GET['allUsers'] : "";
$selectUser            = (isset($_GET['selectUser'])) ? $_GET['selectUser'] : "";
$downloadProfileHandle = (isset($_GET['downloadProfileHandle'])) ? $_GET['downloadProfileHandle'] : "";
$formatToPrint         = (isset($_GET['formatToPrint'])) ? $_GET['formatToPrint'] : "";
$groupBy               = (isset($_GET['groupBy'])) ? $_GET['groupBy'] : '';
$groupCnt              = (isset($_GET['groupCnt'])) ? $_GET['groupCnt'] : 0;
$grpFldDsc             = (isset($_GET['grpFldDsc'])) ? $_GET['grpFldDsc'] : '';
$grpFldHdr             = (isset($_GET['grpFldHdr'])) ? $_GET['grpFldHdr'] : '';
$grpFldLvl             = (isset($_GET['grpFldLvl'])) ? $_GET['grpFldLvl'] : 0;
$grpFldTyp             = (isset($_GET['grpFldTyp'])) ? $_GET['grpFldTyp'] : "";
$grpFldVal             = (isset($_GET['grpFldVal'])) ? $_GET['grpFldVal'] : "";
$orderBy               = (isset($_GET['orderBy'])) ? $_GET['orderBy'] : '';
$rptMaxRows            = (isset($_GET['rptMaxRows'])) ? $_GET['rptMaxRows'] : 10;
$selectCriteria        = (isset($_GET['selectCriteria'])) ? $_GET['selectCriteria'] : "";
$selectDesc            = (isset($_GET['selectDesc'])) ? $_GET['selectDesc'] : "";
$selectGroup           = (isset($_GET['selectGroup'])) ? $_GET['selectGroup'] : "";
$selectGroupBy         = (isset($_GET['selectGroupBy'])) ? $_GET['selectGroupBy'] : "";
$selectGroupSQL        = (isset($_GET['selectGroupSQL'])) ? $_GET['selectGroupSQL'] : "";
$selectGroupWork       = (isset($_GET['selectGroupWork'])) ? $_GET['selectGroupWork'] : "";
$selectRecSQL          = (isset($_GET['selectRecSQL'])) ? $_GET['selectRecSQL'] : "";
$selectSequence        = (isset($_GET['selectSequence'])) ? $_GET['selectSequence'] : 0;
$selectTotals          = (isset($_GET['selectTotals'])) ? $_GET['selectTotals'] : "";
$totalByAmt            = (isset($_GET['totalByAmt'])) ? $_GET['totalByAmt'] : "N";
$totalByCnt            = (isset($_GET['totalByCnt'])) ? $_GET['totalByCnt'] : "N";
$totalByQty            = (isset($_GET['totalByQty'])) ? $_GET['totalByQty'] : "N";
$sequence              = (isset($_GET['sequence'])) ? $_GET['sequence'] : "";
$startRow              = (isset($_GET['startRow'])) ? $_GET['startRow'] : 1;
$userSQL               = (isset($_GET['userSQL'])) ? $_GET['userSQL'] : '';

$page_title      = 'Sales Inquiry';
$scriptName      = 'SalesInqDisplay.php';
$scriptVarBase   = "{$genericVarBase}&amp;allUsers=" . urlencode($allUsers) . "&amp;selectUser=" . urlencode($selectUser) . "&amp;userSQL=" . urlencode($userSQL) . "&amp;downloadProfileHandle=" . urlencode($downloadProfileHandle);
$currentURL      = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=PROCESS&amp;startRow=" . urlencode($startRow);
$dspMaxRows      = $dspMaxRowsDft;
$prtMaxRows      = $prtMaxRowsDft;
$custType        = 'S';
$selectName      = 'SALESINQ';
$dftOrderBy      = "DHORD# DESC, DHSEQ#";

if ($tag == "PROCESS") {
    if ($downloadProfileHandle == "") {
        $downloadProfileHandle = $profileHandle;
    } else {
        $profileHandle = $downloadProfileHandle;
    }

    if ($grpFldTyp != "") {
        $selectGroupSQL = Retrieve_Group_Desc($profileHandle, $dataBaseID, $selectName, $selectSequence, $grpFldLvl, $grpFldTyp, $grpFldHdr, $grpFldVal, $grpFldDsc, $selectGroup, $selectGroupSQL);
    } else {
        $selectGroupSQL = "";
    }
    $maintCode = "R";
    $returnValue = Retrieve_User_Selection($profileHandle, $dataBaseID, $maintCode, $userProfile, $selectName, $selectSequence, $selectDesc, $selectTotals, $selectGroupBy, $selectCriteria);
    $profileHandle  = $returnValue['profileHandle'];
    $dataBaseID     = $returnValue['dataBaseID'];
    $maintCode      = $returnValue['maintCode'];
    $userProfile    = $returnValue['userProfile'];
    $selectName     = $returnValue['selectName'];
    $selectSequence = $returnValue['selectSequence'];
    $selectDesc     = $returnValue['selectDesc'];
    $selectTotals   = $returnValue['selectTotals'];
    $selectGroupBy  = $returnValue['selectGroupBy'];
    $selectCriteria = $returnValue['selectCriteria'];
    $totalByAmt = "";
    $totalByQty = "";
    $totalByCnt = "";
    if (substr($selectTotals, 0, 1) == "Y") {
        $totalByQty = "Y";
    }
    if (substr($selectTotals, 1, 1) == "Y") {
        $totalByAmt = "Y";
    }
    if (substr($selectTotals, 2, 1) == "Y") {
        $totalByCnt = "Y";
    }
    
    require_once "OperSel_Functions.php";
    $selectRecSQL = "WHERE DHSEQ# >0 ";
    $selectGroupWork = "";
    $groupBy = "";
    $returnValue = Build_SQL("DHITEM", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHIMDS", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHBLTO", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHSHTO", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMCNA1U", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMCNA2U", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMCNA3U", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMCNA4U", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMCCTYU", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMST", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMZIP", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHDTLI", "D", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("@@LIYR", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("@@LIQT", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("@@LIMO", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("@@LIWK", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHPCLS", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHPGRP", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMITC", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("HHDOTS", "D", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("@@SDYR", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("@@SDQT", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("@@SDMO", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("@@SDWK", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHSLPR", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHQSTC", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("HHSLSM", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMCCLS", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy']; 
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMCRGN", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHLOC#", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHWH", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHSVSV", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("DHSVDS", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("HHORTY", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMUDF1", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMUDF2", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMUDF3", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMUDF4", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("CMUDF5", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDA1", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDA2", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDA3", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDA4", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDA5", "A", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDN1", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDN2", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDN3", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDN4", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];
    $returnValue = Build_SQL("IMUDN5", "N", $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL);
    $groupBy = $returnValue['groupBy'];
    $selectRecSQL = $returnValue['selectRecSQL'];
    $selectGroupWork = $returnValue['selectGroupWork'];  

    if ($formatToPrint != "") {
        $rptMaxRows = $prtMaxRows;
    } else {
        $rptMaxRows = $dspMaxRows;
    }	
    if ($orderBy == "") {
        $orderBy = $dftOrderBy;
    }
}

if ($tag == "ORDERBY") {
    if     ($sequence == "ShipToNumber")  {$orby = array(array("DHSHTO", "A", "Ship-To Number"), array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    elseif ($sequence == "ShipToName")    {$orby = array(array("CMCNA1U", "A", "Ship-To Name"), array("DHSHTO", "A", "Ship-To Number"), array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    elseif ($sequence == "OrderNumber")   {$orby = array(array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    elseif ($sequence == "SeqNumber")     {$orby = array(array("DHSEQ#", "A", "Sequence"), array("DHORD#", "A", "Order Number"));}
    elseif ($sequence == "ItemNumber")    {$orby = array(array("DHITEM", "A", "Item Number"), array("DHORD#", "A", "Order Number"));}
    elseif ($sequence == "ItemDesc")      {$orby = array(array("DHIMDS", "A", "Item Description"), array("DHITEM", "A", "Item Number"), array("DHORD#", "A", "Order Number"));}
    elseif ($sequence == "InvoiceNumber") {$orby = array(array("HHLIV#", "A", "Invoice Number"), array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    elseif ($sequence == "InvoiceDate")   {$orby = array(array("DHDTLI", "A", "Invoice Date"), array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    elseif ($sequence == "Price")         {$orby = array(array("DHSLPR", "A", "Price"), array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    elseif ($sequence == "Quantity")      {$orby = array(array("DHQSTC", "A", "Quantity"), array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    elseif ($sequence == "Cost")          {$orby = array(array("SUMCST", "A", "Cost"), array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    elseif ($sequence == "MarginAmt")     {$orby = array(array("MARGINAMT", "A", "Margin Amount"), array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    elseif ($sequence == "MarginPct")     {$orby = array(array("MARGINPCT", "A", "Margin Percent"), array("DHORD#", "A", "Order Number"), array("DHSEQ#", "A", "Sequence"));}
    require_once 'OrderByUpdate.php';
}    

if ($startRow > 1) {
    $prevStartRow = $startRow - $rptMaxRows;
}
require_once ($docType);
print "\n <html> <head> ";
require_once ($headInclude);
print "\n <script TYPE=\"text/javascript\"> ";
require_once "AJAXRequest.js";
require_once "SaveCurrentURL.php";
require_once "Menu.js";
require_once "NumEdit.php";
require_once "NewWindowOpen.php";
print "\n </script> ";
require_once ($genericHead);
print "\n </head> ";
print "\n <body $bodyTagAttr> ";
require_once "Banner.php";
print "\n <table $baseTable> ";
print "\n <tr valign=\"top\"> ";
$pageID = "SALESINQ";
require_once 'MenuDisplay.php';	
print "\n <td class=\"content\"> ";

$uv_BillingLocationName = "HHLOC#";
$uv_SalesmanName = "HHSLSM";
$uv_OEOrderTypeName = "HHORTY";
$uv_BankName = "HHBANK";
$uv_ShipToName = "DHSHTO";
$uv_ProductClassName = "DHPCLS";
$uv_ProductGroupName = "DHPGRP";
$uv_WarehouseName = "DHWH";
require_once "Userview.php";

print "\n <table $contentTable> <colgroup> <col width=\"80%\"> <col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td> ";
$selectVars = "&amp;groupBy=" . urlencode(trim($groupBy)) . "&amp;groupCnt=" . urlencode($groupCnt) . "&amp;orderBy=" . urlencode($orderBy) . "&amp;selectCriteria=" . urlencode($selectCriteria) . "&amp;selectDesc=" . urlencode($selectDesc) . "&amp;selectGroup=" . urlencode($selectGroup) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;selectGroupSQL=" . urlencode($selectGroupSQL) . "&amp;selectGroupWork=" . urlencode($selectGroupWork) . "&amp;selectRecSQL=" . urlencode($selectRecSQL) . "&amp;selectSequence=" . urlencode($selectSequence) . "&amp;totalByAmt=" . urlencode($totalByAmt) . "&amp;totalByCnt=" . urlencode($totalByCnt). "&amp;totalByQty=" . urlencode($totalByQty);
if ($formatToPrint != "Y") {
    print "\n <td class=\"toolbar\"> ";
    print "\n <a href=\"{$homeURL}{$phpPath}SalesInqSelect.php{$scriptVarBase}{$selectVars}&amp;tag=RETRIEVE\">$previousImage</a> ";
    if ($userSQL == "") {
        print "\n <a href=\"{$homeURL}{$phpPath}SalesInqUpdate.php{$scriptVarBase}{$selectVars}&amp;tag=DISPLAY&amp;userSQL=" . urlencode($userSQL) . "\" onclick=\"$selectionWinVar\">$selectAcceptImage</a> ";
    }
    print "\n <a href=\"{$homeURL}{$phpPath}SalesInqCriteria.php{$genericVarBase}&amp;selectSequence=" . urlencode($selectSequence) . "\" onclick=\"$selectionWinVar\">$selectCriteriaImage</a> ";
    print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}{$selectVars}&amp;tag=PROCESS&amp;grpFldDsc=" . urlencode($grpFldDsc) . "&amp;grpFldHdr=" . urlencode($grpFldHdr) . "&amp;grpFldLvl=" . urlencode($grpFldLvl) . "&amp;grpFldTyp=formatPrt&amp;grpFldVal=" . urlencode($grpFldVal) . "&amp;formatToPrint=Y\" target=_blank>$formatPrintMed</a> ";
    $medIcon = "Y";
    require_once "HelpPage.php";
    print "\n </td> ";
}
print "\n </tr> </table> ";
print "\n <table $contentTable> ";
if ($selectUser == "") {
    $selectUser = $userProfile;
} 
Format_Header("User Selection",$selectUser,$selectDesc);
print "\n </table> ";
print $hrTagAttr;

if ($selectGroupSQL != "") {
    $stmtSQL = "";
    $stmtSQL = " SELECT * FROM SYUSLG WHERE SGXHND='$downloadProfileHandle' and SGSEQ#=$selectSequence ";
    $stmtSQL .= " ORDER BY SGGBLV ";
    require 'stmtSQLEnd.php';
    $groupStartRow = 1;
    $groupResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
    print "\n <table $contentTable> ";
    print "\n <tr><th class=\"colhdr\">Group By</th> <th class=\"colhdr\">Value</th> <th class=\"colhdr\">Description</th></tr>";
    while ($groupRow = db2_fetch_assoc($groupResult, $groupStartRow)) {
        print "\n <tr> ";
	if ($formatToPrint != "Y") {
            print "\n <td class=\"dspalph\"><a href=\"{$homeURL}{$phpPath}SalesInqGroupBy.php{$scriptVarBase}&amp;tag=PROCESS&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectGroup=" . urlencode($groupRow['SGSQLS']) . "&amp;grpFldDsc=" . urlencode($groupRow['SGFDSC']) . "&amp;grpFldHdr=" . urlencode($groupRow['SGFHDR']) . "&amp;grpFldTyp=goToLevel&amp;grpFldVal=" . urlencode($groupRow['SGFVAL']) . "&amp;grpFldLvl=" . urlencode($groupRow['SGGBLV'] + 1) . "&amp;timeStamp=" . date("Y-m-d-H.i.s.u") . "\">$groupRow[SGFHDR]</a></td> ";
        } else {
            print "\n <td class=\"dspalph\">$groupRow[SGFHDR]</td> ";
        }	
	print "\n <td class=\"dspcode\">$groupRow[SGFVAL]</td> ";
        print "\n <td class=\"dspalph\">$groupRow[SGFDSC]</td>";
        print "\n </tr> ";
        $groupStartRow++;
    }
    print "\n </table> ";
}

$selectGroupSQL = str_replace('@@LIYR', 'year(F_MAKEDATE(DHDTLI))', $selectGroupSQL);
$selectGroupSQL = str_replace('@@LIQT', 'quarter(F_MAKEDATE(DHDTLI))', $selectGroupSQL);
$selectGroupSQL = str_replace('@@LIMO', 'month(F_MAKEDATE(DHDTLI))', $selectGroupSQL);
$selectGroupSQL = str_replace('@@LIWK', 'week(F_MAKEDATE(DHDTLI))', $selectGroupSQL);
$selectGroupSQL = str_replace('@@SDYR', 'year(F_MAKEDATE(HHDOTS))', $selectGroupSQL);
$selectGroupSQL = str_replace('@@SDQT', 'quarter(F_MAKEDATE(HHDOTS))', $selectGroupSQL);
$selectGroupSQL = str_replace('@@SDMO', 'month(F_MAKEDATE(HHDOTS))', $selectGroupSQL);
$selectGroupSQL = str_replace('@@SDWK', 'week(F_MAKEDATE(HHDOTS))', $selectGroupSQL);

$stmtSQL = "";
$stmtSQL = "WITH TOTAL(TOTQTY,TOTAMT,TOTCST,TOTMAMT,TOTMPCT,TOTCNT) AS ";
$stmtSQL .= " (SELECT sumqty, sumamt, sumcst, sumamt-sumcst as marginamt, case when sumamt = 0 then 0 else dec(((sumamt-sumcst)/sumamt),15,5)*100 end as marginpct, sumcnt ";
$stmtSQL .= " From Table (SELECT sum(DHQSTC) as sumqty, dec(sum(round((DHQSTC*DHSLPR),2)/DHORUF),15,2) as sumamt, dec(sum(DHQSTC*DHCOST),15,5) as sumcst, count(*) as sumcnt ";
$stmtSQL .= " From HDCUST inner join OEORDH on CMCUST=DHSHTO ";
$stmtSQL .= "             inner join OEORHH on HHORD#=DHORD# and HHSEQ#=DHSEQ#";
$stmtSQL .= "             left  join HDIMST on IMITEM=DHITEM ";
$stmtSQL .= " {$selectRecSQL}";
if ($selectGroupSQL != "") {
    $stmtSQL .= " and ({$selectGroupSQL})";
}
if ($uv_Sql != "") {
    $stmtSQL .= " and ({$uv_Sql})";
}
if ($userSQL != "") {
    $stmtSQL .= " and ({$userSQL})";
}
$stmtSQL .= ") as TempA) ";

$stmtSQL .= " Select CMCNA1, CMCNA1U, CMCNA2, CMCNA2U, CMCNA3, CMCNA3U, CMCNA4, CMCNA4U, CMCCTY, CMCCTYU, CMST, CMZIP, CMCCLS, CMCRGN, ";
$stmtSQL .= " HHLOC#, HHSLSM, HHORTY, HHBANK, HHLIV#,";
$stmtSQL .= " DHORD#, DHSEQ#, DHDTLI, DHSHTO, DHBLTO, DHITEM, DHIMDS,";
$stmtSQL .= " DHQSTC, DHSLPR, DHORUF, DHPCLS, DHPGRP, DHWH, DHOREC, DHLOC#,";
$stmtSQL .= " SUMAMT, SUMAMT-SUMCST as MARGINAMT, case when SUMAMT = 0 then 0 else dec(((SUMAMT-SUMCST)/SUMAMT),15,5)*100 end as MARGINPCT, Temp.* ";
$stmtSQL .= " From Table (Select CMCNA1, CMCNA1U, CMCNA2, CMCNA2U, CMCNA3, CMCNA3U, CMCNA4, CMCNA4U, CMCCTY, CMCCTYU, CMST, CMZIP, CMCCLS, CMCRGN, ";
$stmtSQL .= " HHLOC#, HHSLSM, HHORTY, HHBANK, HHLIV#,";
$stmtSQL .= " DHORD#, DHSEQ#, DHDTLI, year(F_MAKEDATE(DHDTLI)) as LIYEAR, quarter(F_MAKEDATE(DHDTLI)) as LIQTR, month(F_MAKEDATE(DHDTLI)) as LIMNTH, week(F_MAKEDATE(DHDTLI)) as LIWEEK, DHSHTO, DHBLTO, DHITEM, DHIMDS,";
$stmtSQL .= " year(F_MAKEDATE(HHDOTS)) as SDYEAR, quarter(F_MAKEDATE(HHDOTS)) as SDQTR, month(F_MAKEDATE(HHDOTS)) as SDMNTH, week(F_MAKEDATE(HHDOTS)) as SDWEEK,";
$stmtSQL .= " DHQSTC, DHSLPR, DHORUF, DHPCLS, DHPGRP, DHWH, DHOREC, DHLOC#,";
$stmtSQL .= " dec(round((DHQSTC*DHSLPR),2)/DHORUF,15,2) as sumamt, dec(DHQSTC*DHCOST,15,5) as sumcst, ";
$stmtSQL .= " TOTAL.TOTQTY, TOTAL.TOTAMT, TOTAL.TOTCST, TOTAL.TOTMAMT, TOTAL.TOTMPCT, TOTAL.TOTCNT";
$stmtSQL .= " From HDCUST inner join OEORDH on CMCUST=DHSHTO ";
$stmtSQL .= "             inner join OEORHH on HHORD#=DHORD# and HHSEQ#=DHSEQ#";
$stmtSQL .= "             left  join HDIMST on IMITEM=DHITEM ";
$stmtSQL .= " , TOTAL {$selectRecSQL} ";
if ($selectGroupSQL != "") {
    $stmtSQL .= " and ({$selectGroupSQL})";
}
if ($uv_Sql != "") {
    $stmtSQL .= " and ({$uv_Sql})";
}
if ($userSQL != "") {
    $stmtSQL .= " and ({$userSQL})";
}
$stmtSQL .= " ) as Temp ";
$stmtSQL .= " Order By {$orderBy} ";
require_once "stmtSQLEnd.php";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
$displayMargin = RetValue("SPUSER='$userProfile' and SPPGID='SALESINQ'", "SYPGMS", "SPOP01");
$totalRows = 1;
while ($row = db2_fetch_assoc($sqlResult, $totalRows)) {
    $totalRows ++;
}
$totalRows -= 1;
if ($totalRows > $rptMaxRows) {
    $totalPages = floor($totalRows / $rptMaxRows);
    if (($totalRows % $rptMaxRows) > 0) {
        $totalPages += 1;
    }
} else {
    $totalPages = 1;
}

$rowIndexNext = $startRow + $rptMaxRows;
$page = (($startRow - 1) / $rptMaxRows) +1;
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $rptMaxRows) {break;}
    if ($rowCount == 0) {
        if ($groupByHdr != "") {
            print "\n <table $contentTable> ";
            $dspVar = $groupByHdr;
            $selectGroupSQL = Retrieve_Group_Desc($profileHandle, $dataBaseID, $selectName, $dspVar);
            print "\n $dspVar";
            print "\n </table> ";
        }
        require_once "ConfMessageDisplay.php";

        print "\n <div class=\"page\" > <br> ";
        if ($formatToPrint == "") {
            print "\n Page: $page of $totalPages ";
	    $nextPrevVar = "{$genericVarBase}&amp;tag=DISPLAY&amp;orderBy=" . urlencode($orderBy) . "&amp;allUsers=" . urlencode($allUsers) . "&amp;userSQL=" . urlencode($userSQL) . "&amp;selectGroup=" . urlencode($selectGroupSave) . "&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectRecSQL=" . urlencode($selectRecSQL) . "&amp;selectGroupWork=" . urlencode($selectGroupWork) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;selectGroupSQL=" . urlencode($selectGroupSQL) . "&amp;selectDesc=" . urlencode($selectDesc) . "&amp;groupBy=" . urlencode(trim($groupBy)) . "&amp;groupCnt=" . urlencode($groupCnt) . "&amp;totalByQty=" . urlencode($totalByQty) . "&amp;totalByAmt=" . urlencode($totalByAmt) . "&amp;totalByCnt=" . urlencode($totalByCnt) . "&amp;grpFldLvl=" . urlencode($grpFldLvl) . "&amp;downloadProfileHandle=" . urlencode($downloadProfileHandle);
            if ($startRow > $rptMaxRows) {
                print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$genericVarBase}{$nextPrevVar}&amp;startRow=" . urlencode($prevStartRow) . "&amp;rptMaxRows=" . urlencode($rptMaxRows) . "\">$previousImage</a> ";
            } elseif ($totalRows > $rptMaxRows) {
                print $nextPrevBlank;
            }
            if ($totalRows >= $rowIndexNext) {
                print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$genericVarBase}{$nextPrevVar}&amp;startRow=" . urlencode($rowIndexNext) . "&amp;rptMaxRows=" . urlencode($rptMaxRows) . "\">$nextImage</a>";
            } elseif ($totalRows > $rptMaxRows) {
                print $nextPrevBlank;
            }
        }
        $TOTQTY  = $row['TOTQTY'];
        $TOTAMT  = $row['TOTAMT'];
        $TOTCST  = $row['TOTCST'];
        $TOTMAMT = $row['TOTMAMT'];
        $TOTMPCT = $row['TOTMPCT'];
        print "\n </div> ";

        print "<table $contentTable> <tr>";
        $orderByVar   = "{$scriptVarBase}&amp;tag=ORDERBY&amp;formatToPrint=" . urlencode($formatToPrint) . "&amp;orderBy=" . urlencode($orderBy) . "&amp;rptMaxRows=" . urlencode($rptMaxRows) . "&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectRecSQL=" . urlencode($selectRecSQL) . "&amp;selectGroupWork=" . urlencode($selectGroupWork) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;selectGroupSQL=" . urlencode($selectGroupSQL) . "&amp;groupBy=" . urlencode(trim($groupBy)) . "&amp;groupCnt=" . urlencode($groupCnt) . "&amp;totalByQty=" . urlencode($totalByQty) . "&amp;totalByAmt=" . urlencode($totalByAmt) . "&amp;totalByCnt=" . urlencode($totalByCnt) . "&amp;grpFldLvl=" . urlencode($grpFldLvl);
        $returnValue = OrderBy_Sort("DHSHTO");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipToNumber\" title=\"Sequence By Ship-To Number, Order Number, Sequence\">{$sortPoint}Customer<br>Number</a></th> ";
        $returnValue = OrderBy_Sort("CMCNA1U");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipToName\" title=\"Sequence By Ship-To Name, Ship-To Number, Order Number, Sequence\">{$sortPoint}Name</a></th> ";
        $returnValue = OrderBy_Sort("DHORD#");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=OrderNumber\" title=\"Sequence By Order Number, Sequence\">{$sortPoint}Order<br>Number</a></th> ";
        $returnValue = OrderBy_Sort("DHSEQ#");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=SeqNumber\" title=\"Sequence By Sequence, Order Number\">{$sortPoint}Seq</a></th> ";
        $returnValue = OrderBy_Sort("DHITEM");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemNumber\" title=\"Sequence By Item Number, Order Number\">{$sortPoint}Item<br>Number</a></th> ";
        $returnValue = OrderBy_Sort("DHIMDS");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemDesc\" title=\"Sequence By Item Description, Item Number, Order Number\">{$sortPoint}Item<br>Description</a></th> ";
        $returnValue = OrderBy_Sort("HHLIV#");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=InvoiceNumber\" title=\"Sequence By Invoice Number, Order Number, Sequence\">{$sortPoint}Invoice <br>Number</a></th> ";
        $returnValue = OrderBy_Sort("DHDTLI");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=InvoiceDate\" title=\"Sequence By Invoice Date, Order Number, Sequence\">{$sortPoint}Invoice <br> Date</a></th> ";
        $returnValue = OrderBy_Sort("DHSLPR");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Price\" title=\"Sequence By Price, Order Number, Sequence\">{$sortPoint}Price</a></th> ";
        $returnValue = OrderBy_Sort("DHQSTC");
        $sortVar = $returnValue ['sortedBy'];
        $sortPoint = $returnValue ['sortPoint'];
        print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Quantity\" title=\"Sequence By Quantity, Order Number, Sequence\">{$sortPoint}Quantity</a></th> ";
        print "\n <th class=\"colhdr\">Extended<br>Price</th> ";
        if ($displayMargin == "Y") {
            $returnValue = OrderBy_Sort("SUMCST");
            $sortVar = $returnValue ['sortedBy'];
            $sortPoint = $returnValue ['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Cost\" title=\"Sequence By Cost\">{$sortPoint}Cost</a></th> ";
            $returnValue = OrderBy_Sort("MARGINAMT");
            $sortVar = $returnValue ['sortedBy'];
            $sortPoint = $returnValue ['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=MarginAmt\" title=\"Sequence By Margin Amount\">{$sortPoint}Margin<br>Amount</a></th> ";
            $returnValue = OrderBy_Sort("MARGINPCT");
            $sortVar = $returnValue ['sortedBy'];
            $sortPoint = $returnValue ['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=MarginPct\" title=\"Sequence By Margin Percent\">{$sortPoint}Margin<br>Percent</a></th> ";
        }
        print "\n </tr> ";
    }
    $DTLI = $row['DHDTLI'];
    require "SetRowClass.php";
    print "\n <tr class=\"$rowClass\"> ";
    $extendedPrice = Format_Nbr(($row['DHQSTC'] * $row['DHSLPR']) / $row['DHORUF'],2,$amtEditCode,"Y","","");
    $F_DHSLPR = Format_Nbr($row['DHSLPR'],$prcNbrDec,$amtEditCode,"","","");
    $F_DHQSTC = Format_Nbr($row['DHQSTC'],$qtyNbrDec,$qtyEditCode,"","","");
    $F_DHDTLI = Format_Date($row['DHDTLI'],"D");
    $invoiceFound = Check_Invoice($profileHandle,$dataBaseID,$row['HHLIV#'],$row['DHBLTO']);
    print "\n <td class=\"colnmbr\">$row[DHSHTO]</td> ";
    if ($formatToPrint != "Y") {
        print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerInquiry.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['DHSHTO'])) . "\" onclick=\"{$inquiryWinVar}\" title=\"Customer Quickview\">$row[CMCNA1]</a></td> "; 
        print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;customerNumber={$row['DHSHTO']}&amp;ShipToName=" . urlencode(trim($row['CMCNA1'])) . "&amp;orderNumber=" . urlencode(trim($row['DHORD#'])) . "&amp;orderSequence={$row['DHSEQ#']}\" onclick=\"saveCurrentURL()\" title=\"View Order History Detail\">{$row['DHORD#']}</a></td> ";
    } else {
        print "\n <td class=\"colalph\">$row[CMCNA1]</td> "; 
        print "\n <td class=\"colnmbr\">{$row['DHORD#']}</td> ";
    }
    print "\n <td class=\"colnmbr\">{$row['DHSEQ#']}</td> ";
    print "\n <td class=\"colalph\">$row[DHITEM]</td> ";
    if ($row['DHOREC'] != "N") {
        if ($formatToPrint != "Y") {
            print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}iteminquiry.d2w/DISPLAY{$altVarBase}&amp;itemNumber=" . urlencode($row['DHITEM']) . "\" onclick=\"{$inquiryWinVar}\" title=\"Item Quickview\">$row[DHIMDS]</a></td> ";
        } else {
            print "\n <td class=\"colalph\">$row[DHIMDS]</td> ";
        }
    }	
    if (($formatToPrint != "Y") && ($invoiceFound == "Y")) {
        print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode($row['DHBLTO']) . "&amp;invoiceNumber=" . urlencode($row['HHLIV#']) . "&amp;invoiceDate=" . urlencode($DTLI) . "&amp;formatToPrint=Y\" onclick=\"{$invoiceWinVar}\" title=\"Invoice Quickview\">{$row['HHLIV#']}</a></td> ";
    } else {
        print "\n <td class=\"colnmbr\">{$row['HHLIV#']}</td> ";
    }
    print "\n <td class=\"coldate\">$F_DHDTLI</td> ";
    print "\n <td class=\"coldate\">$F_DHSLPR</td> ";
    print "\n <td class=\"colnmbr\">$F_DHQSTC</td> ";
    print "\n <td class=\"colnmbr\">$extendedPrice</td> ";
    if ($displayMargin == "Y") {
        $F_SUMCST = Format_Nbr($row['SUMCST'], $cstNbrDec, $cstEditCode, "", "", "");
        print "\n <td class=\"colnmbr\">$F_SUMCST</td> ";
        $F_MARGINAMT = Format_Nbr($row['MARGINAMT'], $amtNbrDec, $amtEditCode, "", "", "");
        print "\n <td class=\"colnmbr\">$F_MARGINAMT</td> ";
        $F_MARGINPCT = Format_Nbr($row['MARGINPCT'], $pctNbrDec, $pctEditCode, "", "", "");
        print "\n <td class=\"colnmbr\">$F_MARGINPCT</td> ";
    }
    print "\n </tr> ";
    $startRow ++;
    $rowCount ++;
}
print "\n <tr> ";
if ($totalByQty == "Y" || $totalByAmt == "Y" || $totalByCnt == "Y") {
    print "\n <td colspan=\"9\">&nbsp;</td> ";
}
if ($totalByQty == "Y") {
    $F_TOTQTY = Format_Nbr($TOTQTY, $qtyNbrDec, $qtyEditCode, "", "", "");
    print "\n <td class=\"coltotal\">$F_TOTQTY</td> ";
} else {
    print "\n <td>&nbsp;</td> ";
}
if ($totalByAmt == "Y") {
    $F_TOTAMT = Format_Nbr($TOTAMT, 2, $amtEditCode, "", "", "");
    print "\n <td class=\"coltotal\">$F_TOTAMT</td> ";
    if ($displayMargin == "Y") {
        $F_TOTCST = Format_Nbr($TOTCST, $cstNbrDec, $cstEditCode, "", "", "");
        print "\n <td class=\"coltotal\">$F_TOTCST</td> ";
        $F_TOTMAMT = Format_Nbr($TOTMAMT, $amtNbrDec, $amtEditCode, "", "", "");
        print "\n <td class=\"coltotal\">$F_TOTMAMT</td> ";
        $F_TOTMPCT = Format_Nbr($TOTMPCT, $pctNbrDec, $pctEditCode, "", "", "");
        print "\n <td class=\"coltotal\">$F_TOTMPCT</td> ";
    }
}
print "\n </tr> </table> ";
print $hrTagAttr;
require_once "Copyright.php";
print "\n </td> </tr> </table> ";
require_once "Trailer.php";
print "\n </body>  </html> ";
?>