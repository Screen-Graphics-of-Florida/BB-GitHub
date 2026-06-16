<?php
require_once 'GetURLParm.php';
require_once "CopyrightBanner.php";

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
$selectDesc            = (isset($_GET['selectDesc'])) ? $_GET['selectDesc'] : "";
$selectGroup           = (isset($_GET['selectGroup'])) ? $_GET['selectGroup'] : "";
$selectGroupBy         = (isset($_GET['selectGroupBy'])) ? $_GET['selectGroupBy'] : "";
$selectGroupSQL        = (isset($_GET['selectGroupSQL'])) ? $_GET['selectGroupSQL'] : "";
$selectGroupWork       = (isset($_GET['selectGroupWork'])) ? $_GET['selectGroupWork'] : "";
$selectRecSQL          = (isset($_GET['selectRecSQL'])) ? $_GET['selectRecSQL'] : "";
$selectSequence        = (isset($_GET['selectSequence'])) ? $_GET['selectSequence'] : 0;
$sequence              = (isset($_GET['sequence'])) ? $_GET['sequence'] : "";
$startRow              = (isset($_GET['startRow'])) ? $_GET['startRow'] : 1;
$selectTotals          = (isset($_GET['selectTotals'])) ? $_GET['selectTotals'] : "";
$totalByAmt            = (isset($_GET['totalByAmt'])) ? $_GET['totalByAmt'] : "N";
$totalByCnt            = (isset($_GET['totalByCnt'])) ? $_GET['totalByCnt'] : "N";
$totalByQty            = (isset($_GET['totalByQty'])) ? $_GET['totalByQty'] : "N";
$userSQL               = (isset($_GET['userSQL'])) ? $_GET['userSQL'] : '';

require_once 'SetLibraryList.php';
require_once "EditRoutines.php";
require_once "EdtVar.php";
require_once "GenericDirectCallVariables.php";
require_once "Menu.php";
require_once "NewWindowVariables.php";
require_once "ARControl$dataBaseID.php";
require_once "InventoryControl$dataBaseID.php";
require_once "VarBase.php";
require_once "WildCard.php";

$page_title      = "Sales Inquiry";
$scriptName      = "SalesInqGroupBy.php";
$scriptVarBase   = "{$genericVarBase}&amp;allUsers=" . urlencode($allUsers) . "&amp;selectUser=" . urlencode($selectUser) . "&amp;userSQL=" . urlencode($userSQL) . "&amp;downloadProfileHandle=" . urlencode($downloadProfileHandle);
$dspMaxRows      = $dspMaxRowsDft;
$prtMaxRows      = $prtMaxRowsDft;
$selectName      = "SALESINQ";

if ($tag == "ORDERBY") {
    $orderBy = trim($orderBy);
    if     ($sequence == "BillToNumber")    {$orby = array(array("DHBLTO","A","Bill-To Number"));}
    elseif ($sequence == "ShipToNumber")    {$orby = array(array("DHSHTO","A","Ship-To Number"));}
    elseif ($sequence == "ShipToName")      {$orby = array(array("CMCNA1U","A","Ship-To Name"));}
    elseif ($sequence == "Address1")        {$orby = array(array("CMCNA2U","A","Address 1"));}
    elseif ($sequence == "Address2")        {$orby = array(array("CMCNA3U","A","Address 2"));}
    elseif ($sequence == "Address3")        {$orby = array(array("CMCNA4U","A","Address 3"));}
    elseif ($sequence == "City")            {$orby = array(array("CMCCTYU","A","City"));}
    elseif ($sequence == "State")           {$orby = array(array("CMST","A","State"));}
    elseif ($sequence == "Zip")             {$orby = array(array("CMZIP","A","Zip Code"));}
    elseif ($sequence == "OrderNumber")     {$orby = array(array("DHORD#","A","Order Number"));}
    elseif ($sequence == "ItemNumber")      {$orby = array(array("DHITEM","D","Item Number"));}
    elseif ($sequence == "ItemDesc")        {$orby = array(array("DHIMDS","A","Item Description"));}
    elseif ($sequence == "InvoiceDate")     {$orby = array(array("DHDTLI","A","Invoice Date"));}
    elseif ($sequence == "InvoiceYear")     {$orby = array(array("LIYEAR","A","Invoice Year"));}
    elseif ($sequence == "InvoiceQuarter")  {$orby = array(array("LIQTR","A","Invoice Quarter"));}
    elseif ($sequence == "InvoiceMonth")    {$orby = array(array("LIMNTH","A","Invoice Month"));}
    elseif ($sequence == "InvoiceWeek")     {$orby = array(array("LIWEEK","A","Invoice Week"));}
    elseif ($sequence == "ProductClass")    {$orby = array(array("DHPCLS","A","Product Class"));}
    elseif ($sequence == "ProductGroup")    {$orby = array(array("DHPGRP","A","Product Group"));}
    elseif ($sequence == "InvType")         {$orby = array(array("IMITC","A","Invoice Type"));}
    elseif ($sequence == "ShipDate")        {$orby = array(array("HHDOTS","A","Shipped Date"));}
    elseif ($sequence == "SellingPrice")    {$orby = array(array("DHSLPR","A","Selling Price"));}
    elseif ($sequence == "QuantitySold")    {$orby = array(array("DHQSTC","A","Quantity Sold"));}
    elseif ($sequence == "SalesmanNumber")  {$orby = array(array("HHSLSM","A","Salesman Number"));}
    elseif ($sequence == "CustomerClass")   {$orby = array(array("CMCCLS","A","Customer Class"));}
    elseif ($sequence == "Region")          {$orby = array(array("CMCRGN","A","Region"));}
    elseif ($sequence == "Location")        {$orby = array(array("DHLOC#","A","Location Number"));}
    elseif ($sequence == "WarehouseNumber") {$orby = array(array("DHWH","A","Warehouse Number"));}
    elseif ($sequence == "ShipViaCode")     {$orby = array(array("DHSVSV","A","Ship Via Code"));}
    elseif ($sequence == "ShipViaDesc")     {$orby = array(array("DHSVDS","A","Ship Via Description"));}
    elseif ($sequence == "OrderType")       {$orby = array(array("HHORTY","A","Order Type"));}
    elseif ($sequence == "CustUDA1")        {$orby = array(array("CMUDF1","A","$row[CRSCR1]"));}
    elseif ($sequence == "CustUDA2")        {$orby = array(array("CMUDF2","A","$row[CRSCR2]"));}
    elseif ($sequence == "CustUDA3")        {$orby = array(array("CMUDF3","A","$row[CRSCR3]"));}
    elseif ($sequence == "CustUDA4")        {$orby = array(array("CMUDF4","A","$row[CRSCR4]"));}
    elseif ($sequence == "CustUDA5")        {$orby = array(array("CMUDF5","A","$row[CRSCR5]"));}
    elseif ($sequence == "ItemUDA1")        {$orby = array(array("IMUDA1","A","$row[CIIAS1]"));}
    elseif ($sequence == "ItemUDA2")        {$orby = array(array("IMUDA2","A","$row[CIIAS2]"));}
    elseif ($sequence == "ItemUDA3")        {$orby = array(array("IMUDA3","A","$row[CIIAS3]"));}
    elseif ($sequence == "ItemUDA4")        {$orby = array(array("IMUDA4","A","$row[CIIAS4]"));}
    elseif ($sequence == "ItemUDA5")        {$orby = array(array("IMUDA5","A","$row[CIIAS5]"));}
    elseif ($sequence == "ItemUDN1")        {$orby = array(array("IMUDN1","A","$row[CIINS1]"));}
    elseif ($sequence == "ItemUDN2")        {$orby = array(array("IMUDN2","A","$row[CIINS2]"));}
    elseif ($sequence == "ItemUDN3")        {$orby = array(array("IMUDN3","A","$row[CIINS3]"));}
    elseif ($sequence == "ItemUDN4")        {$orby = array(array("IMUDN4","A","$row[CIINS4]"));}
    elseif ($sequence == "ItemUDN5")        {$orby = array(array("IMUDN5","A","$row[CIINS5]"));}
    elseif ($sequence == "TotalQty")        {$orby = array(array("SUMQTY","A","Quantity"));}
    elseif ($sequence == "TotalAmt")        {$orby = array(array("SUMAMT","A","Amount"));}
    elseif ($sequence == "TotalCnt")        {$orby = array(array("SUMCNT","A","Count"));}
    elseif ($sequence == "Cost")            {$orby = array(array("SUMCST","A","Cost"));}
    elseif ($sequence == "MarginAmt")       {$orby = array(array("MARGINAMT","A","Margin Amount"));}
    elseif ($sequence == "MarginPct")       {$orby = array(array("MARGINPCT","A","Margin Percent"));}
    require_once 'OrderByUpdate.php';
}
if ($tag == "PROCESS") {
    if ($downloadProfileHandle == "") {
        $downloadProfileHandle = $profileHandle;
    } else {
        $profileHandle = $downloadProfileHandle;
    }
    if ($grpFldTyp != "") {
        $selectGroupSQL = Retrieve_Group_Desc($profileHandle,$dataBaseID,$selectName,$selectSequence,$grpFldLvl,$grpFldTyp,$grpFldHdr,$grpFldVal,$grpFldDsc,$selectGroup,$selectGroupSQL);
	if ($grpFldTyp == "goToLevel") {
            $grpFldLvl -= 1;
        }
    } else {
        $selectGroupSQL = "";
    }
    $maintCode = "R";
    $returnValue = Retrieve_User_Selection($profileHandle,$dataBaseID,$maintCode,$userProfile,$selectName,$selectSequence,$selectDesc,$selectTotals,$selectGroupBy,$selectCriteria);
    $profileHandle  = $returnValue['profileHandle'];
    $dataBaseID     = $returnValue['dataBaseID'] ;
    $maintCode      = $returnValue['maintCode']  ;
    $userProfile    = $returnValue['userProfile'] ;
    $selectName     = $returnValue['selectName'];
    $selectSequence = $returnValue['selectSequence'];
    $selectDesc     = $returnValue['selectDesc'] ;
    $selectTotals   = $returnValue['selectTotals'];
    $selectGroupBy  = $returnValue['selectGroupBy'];
    $selectCriteria = $returnValue['selectCriteria'];
    $groupCnt = 0;
    $x = 0;
    while (($x <= 500) && (substr($selectGroupBy,$x,2) == "@@")) {
        $groupCnt += 1;
        $x += 9;
    }
    if (substr($selectTotals,0,1) == "Y") {
        $totalByQty = "Y";
    }
    if (substr($selectTotals,1,1) == "Y") {
        $totalByAmt = "Y";
    }
    if (substr($selectTotals,2,1) == "Y") {
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

    if ($formatToPrint == "Y") {
        $rptMaxRows = $prtMaxRows;
    } else {
        $commaPos = strpos($groupBy,",");
        if ($commaPos === false) {
            $orderBy = trim($groupBy);
        } else {
            while ($commaPos !== false) {
                $lastComma = $commaPos;
                $commaPos = strpos($groupBy,",", $lastComma + 1);
            }
            $orderBy = trim(substr($groupBy, $lastComma + 1));
        }
        $rptMaxRows = $dspMaxRows;
    }
}
if ($startRow > 1) {
    $prevStartRow = $startRow - $rptMaxRows;
} 
require_once ($docType);
print "\n <html> <head> ";
require_once ($headInclude);
print "\n <script TYPE=\"text/javascript\"> ";
require_once 'Menu.js';
require_once 'NumEdit.php';
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
$uv_ProdInventoryTypeName = "IMITC  ";
$uv_WarehouseName = "DHWH  ";
require_once "Userview.php";

print "\n <table $contentTable> ";
print "\n <colgroup> <col width=\"80%\"> <col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td> ";
$selectVars = "&amp;groupBy=" . urlencode(trim($groupBy)) . "&amp;groupCnt=" . urlencode($groupCnt) . "&amp;orderBy=" . urlencode($orderBy) . "&amp;selectCriteria=" . urlencode($selectCriteria) . "&amp;selectDesc=" . urlencode($selectDesc) . "&amp;selectGroup=" . urlencode($selectGroup) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;selectGroupSQL=" . urlencode($selectGroupSQL) . "&amp;selectGroupWork=" . urlencode($selectGroupWork) . "&amp;selectRecSQL=" . urlencode($selectRecSQL) . "&amp;selectSequence=" . urlencode($selectSequence) . "&amp;totalByAmt=" . urlencode($totalByAmt) . "&amp;totalByCnt=" . urlencode($totalByCnt). "&amp;totalByQty=" . urlencode($totalByQty);
if ($formatToPrint != "Y") {
    print "\n <td class=\"toolbar\"> <a href=\"{$homeURL}{$phpPath}SalesInqSelect.php{$scriptVarBase}{$selectVars}&amp;tag=RETRIEVE\">$previousImage</a> ";
    if ($userSQL == "") {
        print "\n <a href=\"{$homeURL}{$phpPath}SalesInqUpdate.php{$scriptVarBase}{$selectVars}&amp;tag=DISPLAY\" onclick=\"{$selectionWinVar}\">$selectAcceptImage</a> ";
    }
    print "\n <a href=\"{$homeURL}{$phpPath}SalesInqCriteria.php{$genericVarBase}&amp;selectSequence=" . urlencode($selectSequence) ."\" onclick=\"{$selectionWinVar}\">$selectCriteriaImage</a> ";
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

$groupStartRow = 1;
if ($selectGroupSQL != "") {
    $stmtSQL = "";
    $stmtSQL = " SELECT * FROM SYUSLG WHERE SGXHND='$downloadProfileHandle' and SGSEQ#=$selectSequence ";
    $stmtSQL .= " ORDER BY SGGBLV ";
    require_once "stmtSQLEnd.php";
    $groupResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));
    print "\n <table $contentTable> ";
    print "\n <tr><th class=\"colhdr\">Group By</th> <th class=\"colhdr\">Value</th> <th class=\"colhdr\">Description</th> </tr> ";
    while ($groupRow = db2_fetch_assoc($groupResult, $groupStartRow)) {
        print "\n <tr> ";
        if ($formatToPrint != "Y") {
            print "\n <td class=\"dspalph\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=PROCESS&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectGroup=" . urlencode($groupRow['SGSQLS']) . "&amp;grpFldDsc=" . urlencode($groupRow['SGFDSC']) . "&amp;grpFldHdr=" . urlencode($groupRow['SGFHDR']) . "&amp;grpFldTyp=goToLevel&amp;grpFldVal=" . urlencode($groupRow['SGFVAL']) . "&amp;grpFldLvl=" . urlencode($groupRow['SGGBLV'] + 1) . "&amp;timeStamp=" . date("Y-m-d-H.i.s.u") . "\">$groupRow[SGFHDR]</a></td> ";
        } else {
            print "\n <td class=\"dspalph\">$groupRow[SGFHDR]</td> ";
        }
        print "\n <td class=\"dspcode\">$groupRow[SGFVAL]</td> ";
        print "\n <td class=\"dspalph\">$groupRow[SGFDSC]</td> ";
        print "\n </tr> ";
	$groupStartRow ++;
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
 
$groupBy1 = $groupBy;
$groupBy1 = str_replace('@@LIYR', 'year(F_MAKEDATE(DHDTLI)) as LIYEAR',$groupBy1);
$groupBy1 = str_replace('@@LIQT', 'quarter(F_MAKEDATE(DHDTLI)) as LIQTR',$groupBy1);
$groupBy1 = str_replace('@@LIMO', 'month(F_MAKEDATE(DHDTLI)) as LIMNTH',$groupBy1);
$groupBy1 = str_replace('@@LIWK', 'week(F_MAKEDATE(DHDTLI)) as LIWEEK',$groupBy1);
$groupBy1 = str_replace('@@SDYR', 'year(F_MAKEDATE(HHDOTS)) as SDYEAR',$groupBy1);
$groupBy1 = str_replace('@@SDQT', 'quarter(F_MAKEDATE(HHDOTS)) as SDQTR',$groupBy1);
$groupBy1 = str_replace('@@SDMO', 'month(F_MAKEDATE(HHDOTS)) as SDMNTH',$groupBy1);
$groupBy1 = str_replace('@@SDWK', 'week(F_MAKEDATE(HHDOTS)) as SDWEEK',$groupBy1);

$groupBy2 = $groupBy;
$groupBy2 = str_replace('@@LIYR', 'year(F_MAKEDATE(DHDTLI))',$groupBy2);
$groupBy2 = str_replace('@@LIQT', 'quarter(F_MAKEDATE(DHDTLI))',$groupBy2);
$groupBy2 = str_replace('@@LIMO', 'month(F_MAKEDATE(DHDTLI))',$groupBy2);
$groupBy2 = str_replace('@@LIWK', 'week(F_MAKEDATE(DHDTLI))',$groupBy2);
$groupBy2 = str_replace('@@SDYR', 'year(F_MAKEDATE(HHDOTS))',$groupBy2);
$groupBy2 = str_replace('@@SDQT', 'quarter(F_MAKEDATE(HHDOTS))',$groupBy2);
$groupBy2 = str_replace('@@SDMO', 'month(F_MAKEDATE(HHDOTS))',$groupBy2);
$groupBy2 = str_replace('@@SDWK', 'week(F_MAKEDATE(HHDOTS))',$groupBy2);

$groupBy3 = $groupBy;
$groupBy3 = str_replace('@@LIYR', 'LIYEAR',$groupBy3);
$groupBy3 = str_replace('@@LIQT', 'LIQTR',$groupBy3);
$groupBy3 = str_replace('@@LIMO', 'LIMNTH',$groupBy3);
$groupBy3 = str_replace('@@LIWK', 'LIWEEK',$groupBy3);
$groupBy3 = str_replace('@@SDYR', 'SDYEAR',$groupBy3);
$groupBy3 = str_replace('@@SDQT', 'SDQTR',$groupBy3);
$groupBy3 = str_replace('@@SDMO', 'SDMNTH',$groupBy3);
$groupBy3 = str_replace('@@SDWK', 'SDWEEK',$groupBy3);

$orderBy = str_replace('@@LIYR', 'LIYEAR',$orderBy);
$orderBy = str_replace('@@LIQT', 'LIQTR',$orderBy);
$orderBy = str_replace('@@LIMO', 'LIMNTH',$orderBy);
$orderBy = str_replace('@@LIWK', 'LIWEEK',$orderBy);
$orderBy = str_replace('@@SDYR', 'SDYEAR',$orderBy);
$orderBy = str_replace('@@SDQT', 'SDQTR',$orderBy);
$orderBy = str_replace('@@SDMO', 'SDMNTH',$orderBy);
$orderBy = str_replace('@@SDWK', 'SDWEEK',$orderBy);

$stmtSQL .= " SELECT {$groupBy3}, SUMAMT, SUMAMT-SUMCST as MARGINAMT, case when SUMAMT = 0 then 0 else dec(((SUMAMT-SUMCST)/SUMAMT),15,5)*100 end as MARGINPCT, Temp.* ";
$stmtSQL .= " From Table (SELECT {$groupBy1}, sum(DHQSTC) as sumqty, dec(sum(round((DHQSTC*DHSLPR),2)/DHORUF),15,2) as sumamt, dec(sum(DHQSTC*DHCOST),15,5) as sumcst, ";
$stmtSQL .= "             count(*) as sumcnt, TOTAL.TOTQTY, TOTAL.TOTAMT, TOTAL.TOTCST, TOTAL.TOTMAMT, TOTAL.TOTMPCT, TOTAL.TOTCNT";
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
$stmtSQL .= " GROUP BY {$groupBy2}, TOTAL.TOTQTY, TOTAL.TOTAMT, TOTAL.TOTCST, TOTAL.TOTMAMT, TOTAL.TOTMPCT, TOTAL.TOTCNT) as Temp ";
$stmtSQL .= " ORDER BY {$orderBy} ";
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
$page = (($startRow - 1) / $rptMaxRows) + 1;
$selectGroupSave = $selectGroup;
if ($totalRows == 0) {
    print "\n <table $contentTable> ";
    print "\n <tr><td class=\"confMsg\" colspan=\"10\">No Data Found For Selection Criteria</td></tr> ";
    print "\n </table> ";
    print $hrTagAttr;
    require_once "Copyright.php";
}
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $rptMaxRows) {break;}
    $wrkCnt = 1;
    $selectGroup = $selectGroupWork;
    $x = 0;
    while ($x <= 500 && substr($selectGroupBy, $x) != "" && $wrkCnt <= $grpFldLvl) {
        $fieldName = trim(substr($selectGroupBy, $x, 9));
        $wrkCnt += 1;
        $grpFldHdr = "";
        $grpFldTyp = "";
        $grpFldVal = "";
        $grpFldDsc = "";
        if ($fieldName == "@@DHITEM") {
            $selectGroup = str_replace("@@DHITEM", "$row[DHITEM]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Item Number";
            $grpFldVal = "$row[DHITEM]";
            $itemDescription = RetValue("IMITEM='$row[DHITEM]'", "HDIMST", "IMIMDS");
            if ($itemDescription == "") {
                $grpFldDsc = "Non-stock item";
            } else {
                $grpFldDsc = $itemDescription;
            }
        } elseif ($fieldName == "@@DHIMDS") {
            $selectGroup = str_replace("@@DHIMDS", "$row[DHIMDS]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Item Description";
            $grpFldVal = $row['DHIMDS'];
        } elseif ($fieldName == "@@DHBLTO") {
            $selectGroup = str_replace("@@DHBLTO", "$row[DHBLTO]", $selectGroup);
            $grpFldTyp = "Num7_0";
            $grpFldHdr = "Bill-To Number";
            $grpFldVal = $row['DHBLTO'];
            $billToName = RetValue("CMCUST=$row[DHBLTO]", "HDCUST", "CMCNA1");
            $grpFldDsc = $billToName;
        } elseif ($fieldName == "@@DHSHTO") {
            $selectGroup = str_replace("@@DHSHTO", "$row[DHSHTO]", $selectGroup);
            $grpFldTyp = "Num7_0";
            $grpFldHdr = "Ship-To Number";
            $grpFldVal = $row['DHSHTO'];
            $ShipToName = RetValue("CMCUST=$row[DHSHTO]", "HDCUST", "CMCNA1");
            $grpFldDsc = $ShipToName;
        } elseif ($fieldName == "@@CMCNA1U") {
            $selectGroup = str_replace("@@CMCNA1U", "$row[CMCNA1U]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Ship-To Name";
            $grpFldVal = $row['CMCNA1U'];
        } elseif ($fieldName == "@@CMCNA2U") {
            $selectGroup = str_replace("@@CMCNA2U", "$row[CMCNA2U]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Address 1";
            $grpFldVal = $row['CMCNA2U'];
        } elseif ($fieldName == "@@CMCNA3U") {
            $selectGroup = str_replace("@@CMCNA3U", "$row[CMCNA3U]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Address 2";
            $grpFldVal = $row['CMCNA3U'];
        } elseif ($fieldName == "@@CMCNA4U") {
            $selectGroup = str_replace("@@CMCNA4U", "$row[CMCNA4U]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Address 3";
            $grpFldVal = $row['CMCNA4U'];
        } elseif ($fieldName == "@@CMCCTYU") {
            $selectGroup = str_replace("@@CMCCTYU", "$row[CMCCTYU]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "City";
            $grpFldVal = $row['CMCCTYU'];
        } elseif ($fieldName == "@@CMST") {
            $selectGroup = str_replace("@@CMST", "$row[CMST]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "State";
            $grpFldVal = $row['CMST'];
            $stateDesc = RetValue("STID='$row[CMST]'", "HDSTID", "STDESC");
            $grpFldDsc = $stateDesc;
        } elseif ($fieldName == "@@CMZIP") {
            $selectGroup = str_replace("@@CMZIP", "$row[CMZIP]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Zip Code";
            $grpFldVal = $row['CMZIP'];
        } elseif ($fieldName == "@@DHDTLI") {
            $selectGroup = str_replace("@@DHDTLI", "$row[DHDTLI]", $selectGroup);
            $grpFldTyp = "Date";
            $grpFldHdr = "Invoice Date";
            $grpFldVal = $row['DHDTLI'];
        } elseif ($fieldName == "@@@@LIYR") {
            $selectGroup = str_replace("@@@@LIYR", "$row[LIYEAR]", $selectGroup);
            $grpFldTyp = "Num4_0";
            $grpFldHdr = "Invoice Year";
            $grpFldVal = $row['LIYEAR'];
        } elseif ($fieldName == "@@@@LIQT") {
            $selectGroup = str_replace("@@@@LIQT", "$row[LIQTR]", $selectGroup);
            $grpFldTyp = "Num4_0";
            $grpFldHdr = "Invoice Quarter";
            $grpFldVal = $row['LIQTR'];
        } elseif ($fieldName == "@@@@LIMO") {
            $selectGroup = str_replace("@@@@LIMO", "$row[LIMNTH]", $selectGroup);
            $grpFldTyp = "Num4_0";
            $grpFldHdr = "Invoice Month";
            $grpFldVal = $row['LIMNTH'];
        } elseif ($fieldName == "@@@@LIWK") {
            $selectGroup = str_replace("@@@@LIWK", "$row[LIWEEK]", $selectGroup);
            $grpFldTyp = "Num4_0";
            $grpFldHdr = "Invoice Week";
            $grpFldVal = $row['LIWEEK'];
        } elseif ($fieldName == "@@DHPCLS") {
            $selectGroup = str_replace("@@DHPCLS", "$row[DHPCLS]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Product Class";
            $grpFldVal = $row['DHPCLS'];
            $prodClassDesc = RetValue("PCPCLS='$row[DHPCLS]'", "HDPCLS", "PCPCDS");
            $grpFldDsc = $prodClassDesc;
        } elseif ($fieldName == "@@DHPGRP") {
            $selectGroup = str_replace("@@DHPGRP", "$row[DHPGRP]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Product Group";
            $grpFldVal = $row['DHPGRP'];
            $prodGroupDesc = RetValue("PGPGRP='$row[DHPGRP]'", "HDPRGM", "PGDESC");
            $grpFldDsc = $prodGroupDesc;
        } elseif ($fieldName == "@@IMITC") {
            $selectGroup = str_replace("@@IMITC", "$row[IMITC]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Inventory Type";
            $grpFldVal = $row['IMITC'];
            $invTypeDesc = RetValue("ITITC='$row[IMITC]'", "HDITYP", "ITDESC");
            $grpFldDsc = $invTypeDesc;
        } elseif ($fieldName == "@@HHDOTS") {
            $selectGroup = str_replace("@@HHDOTS", "$row[HHDOTS]", $selectGroup);
            $grpFldTyp = "Date";
            $grpFldHdr = "Shipped Date";
            $grpFldVal = $row['HHDOTS'];
        } elseif ($fieldName == "@@@@SDYR") {
            $selectGroup = str_replace("@@@@SDYR", "$row[SDYEAR]", $selectGroup);
            $grpFldTyp = "Num4_0";
            $grpFldHdr = "Shipped Year";
            $grpFldVal = $row['SDYEAR'];
        } elseif ($fieldName == "@@@@SDQT") {
            $selectGroup = str_replace("@@@@SDQT", "$row[SDQTR]", $selectGroup);
            $grpFldTyp = "Num4_0";
            $grpFldHdr = "Shipped Quarter";
            $grpFldVal = $row['SDQTR'];
        } elseif ($fieldName == "@@@@SDMO") {
            $selectGroup = str_replace("@@@@SDMO", "$row[SDMNTH]", $selectGroup);
            $grpFldTyp = "Num4_0";
            $grpFldHdr = "Shipped Month";
            $grpFldVal = $row['SDMNTH'];
        } elseif ($fieldName == "@@@@SDWK") {
            $selectGroup = str_replace("@@@@SDWK", "$row[SDWEEK]", $selectGroup);
            $grpFldTyp = "Num4_0";
            $grpFldHdr = "Shipped Week";
            $grpFldVal = $row['SDWEEK'];
        } elseif ($fieldName == "@@DHSLPR") {
            $selectGroup = str_replace("@@DHSLPR", "$row[DHSLPR]", $selectGroup);
            $grpFldTyp = "Num13_5";
            $grpFldHdr = "Selling Price";
            $grpFldVal = $row['DHSLPR'];
        } elseif ($fieldName == "@@DHQSTC") {
            $selectGroup = str_replace("@@DHQSTC", "$row[DHQSTC]", $selectGroup);
            $grpFldTyp = "Num13_4";
            $grpFldHdr = "Quantity Sold";
            $grpFldVal = $row['DHQSTC'];
        } elseif ($fieldName == "@@HHSLSM") {
            $selectGroup = str_replace("@@HHSLSM", "$row[HHSLSM]", $selectGroup);
            $grpFldTyp = "Num4_0";
            $grpFldHdr = "Salesman Number";
            $grpFldVal = $row['HHSLSM'];
            $salesmanName = RetValue("SMSLSM=$row[HHSLSM]", "HDSLSM", "SMSNA1");
            $grpFldDsc = $salesmanName;
        } elseif ($fieldName == "@@CMCCLS") {
            $selectGroup = str_replace("@@CMCCLS", "$row[CMCCLS]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Customer Class";
            $grpFldVal = $row['CMCCLS'];
            $customerClassDesc = RetValue("CCCCLS='$row[CMCCLS]'", "HDCCLS", "CCCCDS");
            $grpFldDsc = $customerClassDesc;
        } elseif ($fieldName == "@@CMCRGN") {
            $selectGroup = str_replace("@@CMCRGN", "$row[CMCRGN]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Region";
            $grpFldVal = $row['CMCRGN'];
            $regionDesc = RetValue("RGCRGN='$row[CMCRGN]'", "HDCRGN", "RGCRDS");
            $grpFldDsc = $regionDesc;
        } elseif ($fieldName == "@@DHLOC#") {
            $selectGroup = str_replace("@@DHLOC#", $row['DHLOC#'], $selectGroup);
            $grpFldTyp = "Num3_0";
            $grpFldHdr = "Location Number";
            $grpFldVal = $row['DHLOC#'];
            $locationName = RetValue("LOLOC#={$row['DHLOC#']}", "HDLCTN", "LOLNA1");
            $grpFldDsc = $locationName;
        } elseif ($fieldName == "@@DHWH") {
            $selectGroup = str_replace("@@DHWH", "$row[DHWH]", $selectGroup);
            $grpFldTyp = "Num3_0";
            $grpFldHdr = "Warehouse Number";
            $grpFldVal = $row['DHWH'];
            $warehouseName = RetValue("WHWHS=$row[DHWH]", "HDWHSM", "WHWHNM");
            $grpFldDsc = $warehouseName;
        } elseif ($fieldName == "@@DHSVSV") {
            $selectGroup = str_replace("@@DHSVSV", "$row[DHSVSV]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Ship Via Code";
            $grpFldVal = $row['DHSVSV'];
            $shipViaDesc = RetValue("SVSVSV='$row[DHSVSV]'", "HDSHPV", "SVSVDS");
            $grpFldDsc = $shipViaDesc;
        } elseif ($fieldName == "@@DHSVDS") {
            $selectGroup = str_replace("@@DHSVDS", "$row[DHSVDS]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Ship Via Description";
            $grpFldVal = $row['DHSVDS'];
        } elseif ($fieldName == "@@HHORTY") {
            $selectGroup = str_replace("@@HHORTY", "$row[HHORTY]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = "Order Type";
            $grpFldVal = $row['HHORTY'];
            $orderTypeDesc = RetValue("OTAPID='OE' and OTOTCD='$row[HHORTY]'", "HDOTYP", "OTDESC");
            $grpFldDsc = $orderTypeDesc;
        } elseif ($fieldName == "@@CMUDF1") {
            $selectGroup = str_replace("@@CMUDF1", "$row[CMUDF1]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CRSCR1'];
            $grpFldVal = $row['CMUDF1'];
        } elseif ($fieldName == "@@CMUDF2") {
            $selectGroup = str_replace("@@CMUDF2", "$row[CMUDF2]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CRSCR2'];
            $grpFldVal = $row['CMUDF2'];
        } elseif ($fieldName == "@@CMUDF3") {
            $selectGroup = str_replace("@@CMUDF3", "$row[CMUDF3]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CRSCR3'];
            $grpFldVal = $row['CMUDF3'];
        } elseif ($fieldName == "@@CMUDF4") {
            $selectGroup = str_replace("@@CMUDF4", "$row[CMUDF4]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CRSCR4'];
            $grpFldVal = $row['CMUDF4'];
        } elseif ($fieldName == "@@CMUDF5") {
            $selectGroup = str_replace("@@CMUDF5", "$row[CMUDF5]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CRSCR5'];
            $grpFldVal = $row['CMUDF5'];
        } elseif ($fieldName == "@@IMUDA1") {
            $selectGroup = str_replace("@@IMUDA1", "$row[IMUDA1]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CIIAS1'];
            $grpFldVal = $row['IMUDA1'];
        } elseif ($fieldName == "@@IMUDA2") {
            $selectGroup = str_replace("@@IMUDA2", "$row[IMUDA2]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CIIAS2'];
            $grpFldVal = $row['IMUDA2'];
        } elseif ($fieldName == "@@IMUDA3") {
            $selectGroup = str_replace("@@IMUDA3", "$row[IMUDA3]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CIIAS3'];
            $grpFldVal = $row['IMUDA3'];
        } elseif ($fieldName == "@@IMUDA4") {
            $selectGroup = str_replace("@@IMUDA4", "$row[IMUDA4]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CIIAS4'];
            $grpFldVal = $row['IMUDA4'];
        } elseif ($fieldName == "@@IMUDA5") {
            $selectGroup = str_replace("@@IMUDA5", "$row[IMUDA5]", $selectGroup);
            $grpFldTyp = "Alpha";
            $grpFldHdr = $row['CIIAS5'];
            $grpFldVal = $row['IMUDA5'];
        } elseif ($fieldName == "@@IMUDN1") {
            $selectGroup = str_replace("@@IMUDN1", "$row[IMUDN1]", $selectGroup);
            $grpFldTyp = "Num13_5";
            $grpFldHdr = $row['CIINS1'];
            $grpFldVal = $row['IMUDN1'];
        } elseif ($fieldName == "@@IMUDN2") {
            $selectGroup = str_replace("@@IMUDN2", "$row[IMUDN2]", $selectGroup);
            $grpFldTyp = "Num13_5";
            $grpFldHdr = $row['CIINS2'];
            $grpFldVal = $row['IMUDN2'];
        } elseif ($fieldName == "@@IMUDN3") {
            $selectGroup = str_replace("@@IMUDN3", "$row[IMUDN3]", $selectGroup);
            $grpFldTyp = "Num13_5";
            $grpFldHdr = $row['CIINS3'];
            $grpFldVal = $row['IMUDN3'];
        } elseif ($fieldName == "@@IMUDN4") {
            $selectGroup = str_replace("@@IMUDN4", "$row[IMUDN4]", $selectGroup);
            $grpFldTyp = "Num13_5";
            $grpFldHdr = $row['CIINS4'];
            $grpFldVal = $row['IMUDN4'];
        } elseif ($fieldName == "@@IMUDN5") {
            $selectGroup = str_replace("@@IMUDN5", "$row[IMUDN5]", $selectGroup);
            $grpFldTyp = "Num13_5";
            $grpFldHdr = $row['CIINS5'];
            $grpFldVal = $row['IMUDN5'];
        }
        $x += 9;
    }
    if ($rowCount == 0) {
        print "\n <div class=\"page\"> <br> ";
        if ($formatToPrint !== "Y") {
            print "\n Page: $page of $totalPages";
            $nextPrevVar = "{$scriptVarBase}&amp;tag=DISPLAY&amp;selectDesc=" . urlencode($selectDesc) . "&amp;orderBy=" . urlencode($orderBy) . "&amp;selectGroup=" . urlencode($selectGroupSave) . "&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectRecSQL=" . urlencode($selectRecSQL) . "&amp;selectGroupWork=" . urlencode($selectGroupWork) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;selectGroupSQL=" . urlencode($selectGroupSQL) . "&amp;groupBy=" . urlencode(trim($groupBy)) . "&amp;groupCnt=" . urlencode($groupCnt) . "&amp;totalByQty=" . urlencode($totalByQty) . "&amp;totalByAmt=" . urlencode($totalByAmt) . "&amp;totalByCnt=" . urlencode($totalByCnt) . "&amp;grpFldLvl=" . urlencode($grpFldLvl);
            if ($startRow > $rptMaxRows) {
                print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;startRow=$prevStartRow&amp;rptMaxRows=" . urlencode($rptMaxRows) . "\">$previousImage</a> ";
            } elseif ($totalRows > $rptMaxRows) {
                print $nextPrevBlank;
            }
            if ($totalRows >= $rowIndexNext) {
                print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;startRow=$rowIndexNext&amp;rptMaxRows=" . urlencode($rptMaxRows) . "\">$nextImage</a>";
            } elseif ($totalRows > $rptMaxRows) {
                print $nextPrevBlank;
            }
        }
        $TOTQTY  = $row['TOTQTY'];
        $TOTAMT  = $row['TOTAMT'];
        $TOTCST  = $row['TOTCST'];
        $TOTMAMT = $row['TOTMAMT'];
        $TOTMPCT = $row['TOTMPCT'];
        $TOTCNT  = $row['TOTCNT'];
        print "\n </div> ";

        print "\n <table $contentTable> ";
        print "\n <tr> ";
        if ($formatToPrint != "Y") {
            print "\n <th>&nbsp;</th> ";
        }
        $wrkCnt = 1;
        $x = 0;
        while ($x <= 500 && substr($selectGroupBy, $x) != "" && $wrkCnt <= $grpFldLvl) {
            $fieldName = trim(substr($selectGroupBy,$x,9));
            if ($wrkCnt == $grpFldLvl) {
                $orderByVar   = "{$scriptVarBase}&amp;tag=ORDERBY&amp;formatToPrint=" . urlencode($formatToPrint) . "&amp;orderBy=" . urlencode($orderBy) . "&amp;rptMaxRows=" . urlencode($rptMaxRows) . "&amp;selectDesc=" . urlencode($selectDesc) . "&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectRecSQL=" . urlencode($selectRecSQL) . "&amp;selectGroupWork=" . urlencode($selectGroupWork) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;selectGroupSQL=" . urlencode($selectGroupSQL) . "&amp;groupBy=" . urlencode(trim($groupBy)) . "&amp;groupCnt=" . urlencode($groupCnt) . "&amp;totalByQty=" . urlencode($totalByQty) . "&amp;totalByAmt=" . urlencode($totalByAmt) . "&amp;totalByCnt=" . urlencode($totalByCnt) . "&amp;grpFldLvl=" . urlencode($grpFldLvl);
                if ($fieldName == "@@DHBLTO") {
                    $returnValue = OrderBy_Sort("DHBLTO");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=BillToNumber\" title=\"Sequence By Bill-To Number\">{$sortPoint}Bill-To<br>Number</a></th> ";
                    print "\n <th class=\"colhdr\">Bill-To Name</th> ";
                } elseif ($fieldName == "@@DHSHTO") {
                    $returnValue = OrderBy_Sort("DHSHTO");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipToNumber\" title=\"Sequence By Ship-To Number\">{$sortPoint}Ship-To<br>Number</a></th> ";
                    print "\n <th class=\"colhdr\">Ship-To Name</th> ";
                } elseif ($fieldName == "@@CMCNA1U") {
                    $returnValue = OrderBy_Sort("CMCNA1U");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipToName\" title=\"Sequence By Ship-To Name\">{$sortPoint}Name</a></th> ";
                } elseif ($fieldName == "@@CMCNA2U") {
                    $returnValue = OrderBy_Sort("CMCNA2U");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Address1\" title=\"Sequence By Address 1\">{$sortPoint}Address 1</a></th> ";
                } elseif ($fieldName == "@@CMCNA3U") {
                    $returnValue = OrderBy_Sort("CMCNA3U");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Address2\" title=\"Sequence By Address 2\">{$sortPoint}Address 2</a></th> ";
                } elseif ($fieldName == "@@CMCNA4U") {
                    $returnValue = OrderBy_Sort("CMCNA4U");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Address3\" title=\"Sequence By Address 3\">{$sortPoint}Address 3</a></th> ";
                } elseif ($fieldName == "@@CMCCTYU") {
                    $returnValue = OrderBy_Sort("CMCCTYU");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=City\" title=\"Sequence By City\">{$sortPoint}City</a></th> ";
                } elseif ($fieldName == "@@CMST") {
                    $returnValue = OrderBy_Sort("CMST");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=State\" title=\"Sequence By State\">{$sortPoint}State</a></th> ";
                    print "\n <th class=\"colhdr\">Description</th> ";
                } elseif ($fieldName == "@@CMZIP") {
                    $returnValue = OrderBy_Sort("CMZIP");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Zip\" title=\"Sequence By Zip Code\">{$sortPoint}Zip Code</a></th> ";
                } elseif ($fieldName == "@@DHORD#") {
                    $returnValue = OrderBy_Sort("DHORD#");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=OrderNumber\" title=\"Sequence By Order Number\">{$sortPoint}Order<br>Number</a></th> ";
                } elseif ($fieldName == "@@DHITEM") {
                    $returnValue = OrderBy_Sort("DHITEM");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemNumber\" title=\"Sequence By Item Number\">{$sortPoint}Item<br>Number</a></th> ";
                    print "\n <th class=\"colhdr\">Description</th> ";
                } elseif ($fieldName == "@@DHIMDS") {
                    $returnValue = OrderBy_Sort("DHIMDS");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemDesc\" title=\"Sequence By Item Description\">{$sortPoint}Item<br>Description</a></th> ";
                } elseif ($fieldName == "@@DHDTLI") {
                    $returnValue = OrderBy_Sort("DHDTLI");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=InvoiceDate\" title=\"Sequence By Invoice Date\">{$sortPoint}Invoice <br> Date</a></th> ";
                } elseif ($fieldName == "@@@@LIYR") {
                    $returnValue = OrderBy_Sort("LIYEAR");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=InvoiceYear\" title=\"Sequence By Invoice Year\">{$sortPoint}Invoice <br> Year</a></th> ";
                } elseif ($fieldName == "@@@@LIQT") {
                    $returnValue = OrderBy_Sort("LIQTR");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=InvoiceQuarter\" title=\"Sequence By Invoice Quarter\">{$sortPoint}Invoice <br> Quarter</a></th> ";
                } elseif ($fieldName == "@@@@LIMO") {
                    $returnValue = OrderBy_Sort("LIMNTH");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=InvoiceMonth\" title=\"Sequence By Invoice Month\">{$sortPoint}Invoice <br> Month</a></th> ";
                } elseif ($fieldName == "@@@@LIWK") {
                    $returnValue = OrderBy_Sort("LIWEEK");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=InvoiceWeek\" title=\"Sequence By Invoice Week\">{$sortPoint}Invoice <br> Week</a></th> ";
                } elseif ($fieldName == "@@DHPCLS") {
                    $returnValue = OrderBy_Sort("DHPCLS");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ProductClass\" title=\"Sequence By Product Class\">{$sortPoint}Product <br> Class</a></th> ";
                    print "\n <th class=\"colhdr\">Description</th> ";
                } elseif ($fieldName == "@@DHPGRP") {
                    $returnValue = OrderBy_Sort("DHPGRP");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ProductGroup\" title=\"Sequence By Product Group\">{$sortPoint}Product <br> Group</a></th> ";
                    print "\n <th class=\"colhdr\">Description</th> ";
                } elseif ($fieldName == "@@IMITC") {
                    $returnValue = OrderBy_Sort("IMITC");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=InvType\" title=\"Sequence By Inventory Type\">{$sortPoint}Inventory <br> Type</a></th> ";
                    print "\n <th class=\"colhdr\">Description</th> ";
                } elseif ($fieldName == "@@HHDOTS") {
                    $returnValue = OrderBy_Sort("HHDOTS");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipDate\" title=\"Sequence By Shipped Date\">{$sortPoint}Shipped <br> Date</a></th> ";
                } elseif ($fieldName == "@@@@SDYR") {
                    $returnValue = OrderBy_Sort("SDYEAR");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipYear\" title=\"Sequence By Shipped Year\">{$sortPoint}Shipped <br> Year</a></th> ";
                } elseif ($fieldName == "@@@@SDQT") {
                    $returnValue = OrderBy_Sort("SDQTR");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipQuarter\" title=\"Sequence By Shipped Quarter\">{$sortPoint}Shipped <br> Quarter</a></th> ";
                } elseif ($fieldName == "@@@@SDMO") {
                    $returnValue = OrderBy_Sort("SDMNTH");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipMonth\" title=\"Sequence By Shipped Month\">{$sortPoint}Shipped <br> Month</a></th> ";
                } elseif ($fieldName == "@@@@SDWK") {
                    $returnValue = OrderBy_Sort("SDWEEK");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipWeek\" title=\"Sequence By Shipped Week\">{$sortPoint}Shipped <br> Week</a></th> ";
                } elseif ($fieldName == "@@DHSLPR") {
                    $returnValue = OrderBy_Sort("DHSLPR");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=SellingPrice\" title=\"Sequence By Selling Price\">{$sortPoint}Selling <br> Price</a></th> ";
                } elseif ($fieldName == "@@DHQSTC") {
                    $returnValue = OrderBy_Sort("DHQSTC");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=QuantitySold\" title=\"Sequence By Quantity Sold\">{$sortPoint}Quantity <br> Sold</a></th> ";
                } elseif ($fieldName == "@@HHSLSM") {
                    $returnValue = OrderBy_Sort("HHSLSM");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=SalesmanNumber\" title=\"Sequence By Salesman Number\">{$sortPoint}Salesman <br> Number</a></th> ";
                    print "\n <th class=\"colhdr\">Name</th> ";
                } elseif ($fieldName == "@@CMCCLS") {
                    $returnValue = OrderBy_Sort("CMCCLS");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=CustomerClass\" title=\"Sequence By Customer Class\">{$sortPoint}Customer <br> Class</a></th> ";
                    print "\n <th class=\"colhdr\">Description</th> ";
                } elseif ($fieldName == "@@CMCRGN") {
                    $returnValue = OrderBy_Sort("CMCRGN");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Region\" title=\"Sequence By Region\">{$sortPoint}Region</a></th> ";
                    print "\n <th class=\"colhdr\">Description</th> ";
                } elseif ($fieldName == "@@DHLOC#") {
                    $returnValue = OrderBy_Sort("DHLOC#");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Location\" title=\"Sequence By Location Number\">{$sortPoint}Location <br> Number</a></th> ";
                    print "\n <th class=\"colhdr\">Name</th> ";
                } elseif ($fieldName == "@@DHWH") {
                    $returnValue = OrderBy_Sort("DHWH");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=WarehouseNumber\" title=\"Sequence By Warehouse Number\">{$sortPoint}Whs</a></th> ";
                    print "\n <th class=\"colhdr\">Name</th> ";
                } elseif ($fieldName == "@@DHSVSV") {
                    $returnValue = OrderBy_Sort("DHSVSV");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipViaCode\" title=\"Sequence By Ship Via Code\">{$sortPoint}Ship Via <br> Code</a></th> ";
                    print "\n <th class=\"colhdr\">Description</th> ";
                } elseif ($fieldName == "@@DHSVDS") {
                    $returnValue = OrderBy_Sort("DHSVDS");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ShipViaDesc\" title=\"Sequence By Ship Via Description\">{$sortPoint}Ship Via <br> Description</a></th> ";
                } elseif ($fieldName == "@@HHORTY") {
                    $returnValue = OrderBy_Sort("HHORTY");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=OrderType\" title=\"Sequence By Order Type\">{$sortPoint}Order<br>Type</a></th> ";
                    print "\n <th class=\"colhdr\">Description</th> ";
                } elseif ($fieldName == "@@CMUDF1") {
                    $returnValue = OrderBy_Sort("CMUDF1");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=CustUDA1\" title=\"Sequence By $row[CRSCR1]\">{$sortPoint}$row[CRSCR1]</a></th> ";
                } elseif ($fieldName == "@@CMUDF2") {
                    $returnValue = OrderBy_Sort("CMUDF2");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=CustUDA2\" title=\"Sequence By $row[CRSCR2]\">{$sortPoint}$row[CRSCR2]</a></th> ";
                } elseif ($fieldName == "@@CMUDF3") {
                    $returnValue = OrderBy_Sort("CMUDF3");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=CustUDA3\" title=\"Sequence By $row[CRSCR3]\">{$sortPoint}$row[CRSCR3]</a></th> ";
                } elseif ($fieldName == "@@CMUDF4") {
                    $returnValue = OrderBy_Sort("CMUDF4");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=CustUDA4\" title=\"Sequence By $row[CRSCR4]\">{$sortPoint}$row[CRSCR4]</a></th> ";
                } elseif ($fieldName == "@@CMUDF5") {
                    $returnValue = OrderBy_Sort("CMUDF5");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=CustUDA5\" title=\"Sequence By $row[CRSCR5]\">{$sortPoint}$row[CRSCR5]</a></th> ";
                } elseif ($fieldName == "@@IMUDA1") {
                    $returnValue = OrderBy_Sort("IMUDA1");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDA1\" title=\"Sequence By $row[CIIAS1]\">{$sortPoint}$row[CIIAS1]</a></th> ";
                } elseif ($fieldName == "@@IMUDA2") {
                    $returnValue = OrderBy_Sort("IMUDA2");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDA2\" title=\"Sequence By $row[CIIAS2]\">{$sortPoint}$row[CIIAS2]</a></th> ";
                } elseif ($fieldName == "@@IMUDA3") {
                    $returnValue = OrderBy_Sort("IMUDA3");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDA3\" title=\"Sequence By $row[CIIAS3]\">{$sortPoint}$row[CIIAS3]</a></th> ";
                } elseif ($fieldName == "@@IMUDA4") {
                    $returnValue = OrderBy_Sort("IMUDA4");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDA4\" title=\"Sequence By $row[CIIAS4]\">{$sortPoint}$row[CIIAS4]</a></th> ";
                } elseif ($fieldName == "@@IMUDA5") {
                    $returnValue = OrderBy_Sort("IMUDA5");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDA5\" title=\"Sequence By $row[CIIAS5]\">{$sortPoint}$row[CIIAS5]</a></th> ";
                } elseif ($fieldName == "@@IMUDN1") {
                    $returnValue = OrderBy_Sort("IMUDN1");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDN1\" title=\"Sequence By $row[CIINS1]\">{$sortPoint}$row[CIINS1]</a></th> ";
                } elseif ($fieldName == "@@IMUDN2") {
                    $returnValue = OrderBy_Sort("IMUDN2");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDN2\" title=\"Sequence By $row[CIINS2]\">{$sortPoint}$row[CIINS2]</a></th> ";
                } elseif ($fieldName == "@@IMUDN3") {
                    $returnValue = OrderBy_Sort("IMUDN3");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDN3\" title=\"Sequence By $row[CIINS3]\">{$sortPoint}$row[CIINS3]</a></th> ";
                } elseif ($fieldName == "@@IMUDN4") {
                    $returnValue = OrderBy_Sort("IMUDN4");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDN4\" title=\"Sequence By $row[CIINS4]\">{$sortPoint}$row[CIINS4]</a></th> ";
                } elseif ($fieldName == "@@IMUDN5") {
                    $returnValue = OrderBy_Sort("IMUDN5");
                    $sortVar     = $returnValue ['sortedBy'];
                    $sortPoint   = $returnValue ['sortPoint'];
                    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=ItemUDN5\" title=\"Sequence By $row[CIINS5]\">{$sortPoint}$row[CIINS5]</a></th> ";
                }
            }
            $wrkCnt += 1;
            $x += 9;
        }

        if ($totalByQty == "Y") {
            $returnValue = OrderBy_Sort("SUMQTY");
            $sortVar     = $returnValue ['sortedBy'];
            $sortPoint   = $returnValue ['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=TotalQty\" title=\"Sequence By Quantity\">{$sortPoint}Quantity</a></th> ";
            print "\n <th class=\"colhdr\">Percent<br>Of Total</th> ";
        }
        if ($totalByAmt == "Y") {
            $returnValue = OrderBy_Sort("SUMAMT");
            $sortVar     = $returnValue ['sortedBy'];
            $sortPoint   = $returnValue ['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=TotalAmt\" title=\"Sequence By Amount\">{$sortPoint}Amount</a></th> ";
            print "\n <th class=\"colhdr\">Percent<br>Of Total</th> ";
        }
        if ($totalByAmt == "Y" && $displayMargin == "Y") {
            $returnValue = OrderBy_Sort("SUMCST");
            $sortVar     = $returnValue ['sortedBy'];
            $sortPoint   = $returnValue ['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=Cost\" title=\"Sequence By Cost\">{$sortPoint}Cost</a></th> ";
            $returnValue = OrderBy_Sort("MARGINAMT");
            $sortVar     = $returnValue ['sortedBy'];
            $sortPoint   = $returnValue ['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=MarginAmt\" title=\"Sequence By Margin Amount\">{$sortPoint}Margin<br>Amount</a></th> ";
            $returnValue = OrderBy_Sort("MARGINPCT");
            $sortVar     = $returnValue ['sortedBy'];
            $sortPoint   = $returnValue ['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=MarginPct\" title=\"Sequence By Margin Percent\">{$sortPoint}Margin<br>Percent</a></th> ";
        }
        if ($totalByCnt == "Y") {
            $returnValue = OrderBy_Sort("SUMCNT");
            $sortVar     = $returnValue ['sortedBy'];
            $sortPoint   = $returnValue ['sortPoint'];
            print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;sequence=TotalCnt\" title=\"Sequence By Count\">{$sortPoint}Count</a></th> ";
        }
        print "\n </tr> ";
    }
    require "SetRowClass.php";
    print "\n <tr class=\"$rowClass\"> ";
    if ($formatToPrint != "Y") {
        if ($grpFldLvl == $groupCnt) {
            print "\n <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}SalesInqDisplay.php{$scriptVarBase}&amp;tag=PROCESS&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectGroup=" . urlencode(trim($selectGroup)) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;grpFldTyp=" . urlencode($grpFldTyp) . "&amp;grpFldHdr=" . urlencode(trim($grpFldHdr)) . "&amp;grpFldVal=" . urlencode(trim($grpFldVal)) . "&amp;grpFldDsc=" . urlencode(trim($grpFldDsc)) . "&amp;grpFldLvl=" . urlencode($grpFldLvl + 1) . "&amp;timeStamp=" . date("Y-m-d-H.i.s.u") . "\" title=\"View Detail\">$selectImage</a></td> ";
        } else {
            print "\n <td class=\"colicon\" ><a href = \"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=PROCESS&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectGroup=" . urlencode($selectGroup) . "&amp;grpFldTyp=" . urlencode($grpFldTyp) . "&amp;grpFldHdr=" . urlencode(trim($grpFldHdr)) . "&amp;grpFldVal=" . urlencode(trim($grpFldVal)) . "&amp;grpFldDsc=" . urlencode(trim($grpFldDsc)) . "&amp;grpFldLvl=" . urlencode($grpFldLvl + 1) . "&amp;timeStamp=" . date("Y-m-d-H.i.s.u") . "\" title=\"View Detail\" > $selectImage</a ></td> ";
        }
    }
    $colCnt = 1;
    $wrkCnt = 1;
    $x = 0;
    while (($x <= 500) && (substr($selectGroupBy,$x) != "") && ($wrkCnt <= $grpFldLvl)) {
        $fieldName = trim(substr($selectGroupBy,$x,9));
        if ($wrkCnt == $grpFldLvl) {
            if ($fieldName == "@@DHBLTO") {
                print "\n <td class=\"colnmbr\" >$row[DHBLTO]</td > ";
                print "\n <td class=\"colalph\" >$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@DHSHTO") {
                print "\n <td class=\"colnmbr\">$row[DHSHTO]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@CMCNA1U") {
                print "\n <td class=\"colalph\">$row[CMCNA1U]</td> ";
            } elseif ($fieldName == "@@CMCNA2U") {
                print "\n <td class=\"colalph\">$row[CMCNA2U]</td> ";
            } elseif ($fieldName == "@@CMCNA3U") {
                print "\n <td class=\"colalph\">$row[CMCNA3U]</td> ";
            } elseif ($fieldName == "@@CMCNA4U") {
                print "\n <td class=\"colalph\">$row[CMCNA4U]</td> ";
            } elseif ($fieldName == "@@CMCCTYU") {
                print "\n <td class=\"colalph\">$row[CMCCTYU]</td> ";
            } elseif ($fieldName == "@@CMST") {
                print "\n <td class=\"colalph\">$row[CMST]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@CMZIP") {
                print "\n <td class=\"colalph\">$row[CMZIP]</td> ";
            } elseif ($fieldName == "@@DHORD#") {
                print "\n <td class=\"colnmbr\">{$row['DHORD#']}</a></td> ";
            } elseif ($fieldName == "@@DHITEM") {
                print "\n <td class=\"colalph\"> $row[DHITEM]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@DHIMDS") {
                print "\n <td class=\"colalph\">$row[DHIMDS]</td> ";
            } elseif ($fieldName == "@@DHDTLI") {
                $F_DHDTLI = Format_Date($row['DHDTLI'],"D");
                print "\n <td class=\"coldate\">$F_DHDTLI</td> ";
            } elseif ($fieldName == "@@@@LIYR") {
                print "\n <td class=\"colnmbr\">$row[LIYEAR]</td> ";
            } elseif ($fieldName == "@@@@LIQT") {
                print "\n <td class=\"colnmbr\">$row[LIQTR]</td> ";
            } elseif ($fieldName == "@@@@LIMO") {
                print "\n <td class=\"colnmbr\">$row[LIMNTH]</td> ";
            } elseif ($fieldName == "@@@@LIWK") {
                print "\n <td class=\"colnmbr\">$row[LIWEEK]</td> ";
            } elseif ($fieldName == "@@DHPCLS") {
                print "\n <td class=\"colcode\">$row[DHPCLS]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@DHPGRP") {
                print "\n <td class=\"colcode\">$row[DHPGRP]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@IMITC") {
                print "\n <td class=\"colcode\">$row[IMITC]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@HHDOTS") {
                $F_HHDOTS = Format_Date($row['HHDOTS'],"D");
                print "\n <td class=\"coldate\">$F_HHDOTS</td> ";
            } elseif ($fieldName == "@@@@SDYR") {
                print "\n <td class=\"colnmbr\">$row[SDYEAR]</td> ";
            } elseif ($fieldName == "@@@@SDQT") {
                print "\n <td class=\"colnmbr\">$row[SDQTR]</td> ";
            } elseif ($fieldName == "@@@@SDMO") {
                print "\n <td class=\"colnmbr\">$row[SDMNTH]</td> ";
            } elseif ($fieldName == "@@@@SDWK") {
                print "\n <td class=\"colnmbr\">$row[SDWEEK]</td> ";
            } elseif ($fieldName == "@@DHSLPR") {
                $F_DHSLPR = Format_Nbr($row['DHSLPR'],$prcNbrDec,$amtEditCode,"","","");
                print "\n <td class=\"colnmbr\">$F_DHSLPR</td> ";
            } elseif ($fieldName == "@@DHQSTC") {
                $F_DHQSTC = Format_Nbr($row['DHQSTC'],$qtyNbrDec,$qtyEditCode,"","","");
                print "\n <td class=\"colnmbr\">$F_DHQSTC</td> ";
            } elseif ($fieldName == "@@HHSLSM") {
                print "\n <td class=\"colnmbr\">$row[HHSLSM]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@CMCCLS") {
                print "\n <td class=\"colalph\">$row[CMCCLS]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@CMCRGN") {
                print "\n <td class=\"colalph\">$row[CMCRGN]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@DHLOC#") {
                print "\n <td class=\"colnmbr\">$'row[DHLOC#]'</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@DHWH") {
                print "\n <td class=\"colnmbr\">$row[DHWH]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@DHSVSV") {
                print "\n <td class=\"colcode\">$row[DHSVSV]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@DHSVDS") {
                print "\n <td class=\"colalph\">$row[DHSVDS]</td> ";
            } elseif ($fieldName == "@@HHORTY") {
                print "\n <td class=\"colcode\">$row[HHORTY]</td> ";
                print "\n <td class=\"colalph\">$grpFldDsc</td> ";
                $colCnt = 2;
            } elseif ($fieldName == "@@CMUDF1") {
                print "\n <td class=\"colalph\">$row[CMUDF1]</td> ";
            } elseif ($fieldName == "@@CMUDF2") {
                print "\n <td class=\"colalph\">$row[CMUDF2]</td> ";
            } elseif ($fieldName == "@@CMUDF3") {
                print "\n <td class=\"colalph\">$row[CMUDF3]</td> ";
            } elseif ($fieldName == "@@CMUDF4") {
                print "\n <td class=\"colalph\">$row[CMUDF4]</td> ";
            } elseif ($fieldName == "@@CMUDF5") {
                print "\n <td class=\"colalph\">$row[CMUDF5]</td> ";
            } elseif ($fieldName == "@@IMUDA1") {
                print "\n <td class=\"colalph\">$row[IMUDA1]</td> ";
            } elseif ($fieldName == "@@IMUDA2") {
                print "\n <td class=\"colalph\">$row[IMUDA2]</td> ";
            } elseif ($fieldName == "@@IMUDA3") {
                print "\n <td class=\"colalph\">$row[IMUDA3]</td> ";
            } elseif ($fieldName == "@@IMUDA4") {
                print "\n <td class=\"colalph\">$row[IMUDA4]</td> ";
            } elseif ($fieldName == "@@IMUDA5") {
                print "\n <td class=\"colalph\">$row[IMUDA5]</td> ";
            } elseif ($fieldName == "@@IMUDN1") {
                print "\n <td class=\"colnmbr\">$row[IMUDN1]</td> ";
            } elseif ($fieldName == "@@IMUDN2") {
                print "\n <td class=\"colnmbr\">$row[IMUDN2]</td> ";
            } elseif ($fieldName == "@@IMUDN3") {
                print "\n <td class=\"colnmbr\">$row[IMUDN3]</td> ";
            } elseif ($fieldName == "@@IMUDN4") {
                print "\n <td class=\"colnmbr\">$row[IMUDN4]</td> ";
            } elseif ($fieldName == "@@IMUDN5") {
                print "\n <td class=\"colnmbr\">$row[IMUDN5]</td> ";
            }
        }
        $wrkCnt += 1;
        $x += 9;
    }
    if ($totalByQty == "Y") {
        $F_SUMQTY = Format_Nbr($row['SUMQTY'],$qtyNbrDec,$qtyEditCode,"","","");
        $qtyPercent = Format_Nbr(($row['SUMQTY'] / $row['TOTQTY']) * 100,4,$pctEditCode,"Y","","");
        print "\n <td class=\"colnmbr\">$F_SUMQTY</td> ";
        print "\n <td class=\"colnmbr\">$qtyPercent</td> ";
    }
    if ($totalByAmt == "Y") {
        $F_SUMAMT = Format_Nbr($row['SUMAMT'],2,$amtEditCode,"","","");
        $amtPercent = Format_Nbr(($row['SUMAMT'] / $row['TOTAMT']) * 100,4,$pctEditCode,"Y","","");
        print "\n <td class=\"colnmbr\">$F_SUMAMT</td> ";
        print "\n <td class=\"colnmbr\">$amtPercent</td> ";
    }
    if ($totalByAmt == "Y" && $displayMargin == "Y") {
        $F_SUMCST = Format_Nbr($row['SUMCST'],$cstNbrDec,$cstEditCode,"","","");
        print "\n <td class=\"colnmbr\">$F_SUMCST</td> ";
        $F_MARGINAMT = Format_Nbr($row['MARGINAMT'],$amtNbrDec,$amtEditCode,"","","");
        print "\n <td class=\"colnmbr\">$F_MARGINAMT</td> ";
        $F_MARGINPCT = Format_Nbr($row['MARGINPCT'],$pctNbrDec,$pctEditCode,"","","");
        print "\n <td class=\"colnmbr\">$F_MARGINPCT</td> ";
    }
    if ($totalByCnt == "Y") {
        $F_SUMCNT = Format_Nbr($row['SUMCNT'],"0","1","","","");
        print "\n <td class=\"colnmbr\">$F_SUMCNT</td> ";
    }
    print "\n </tr> ";
    $startRow ++;
    $rowCount ++;
}
print "\n <tr> ";
if ($totalRows > 0) {
    if ($totalByQty == "Y" || $totalByAmt == "Y" || $totalByCnt == "Y") {
        if ($formatToPrint != "Y") {
            $colCnt += 1;
        }
        print "\n <td colspan=\"$colCnt\">&nbsp;</td> ";
    }
    if ($totalByQty == "Y") {
        $F_TOTQTY = Format_Nbr($TOTQTY,$qtyNbrDec,$qtyEditCode,"","","");
        print "\n <td class=\"coltotal\">$F_TOTQTY</td> ";
        print "\n <td>&nbsp;</td> ";
    }
    if ($totalByAmt == "Y") {
        $F_TOTAMT = Format_Nbr($TOTAMT,2,$amtEditCode,"","","");
        print "\n <td class=\"coltotal\">$F_TOTAMT</td> ";
        print "\n <td>&nbsp;</td> ";
        if ($displayMargin == "Y") {
            $F_TOTCST = Format_Nbr($TOTCST,$cstNbrDec,$cstEditCode,"","","");
            print "\n <td class=\"coltotal\">$F_TOTCST</td> ";
            $F_TOTMAMT = Format_Nbr($TOTMAMT,$amtNbrDec,$amtEditCode,"","","");
            print "\n <td class=\"coltotal\">$F_TOTMAMT</td> ";
            $F_TOTMPCT = Format_Nbr($TOTMPCT,$pctNbrDec,$pctEditCode,"","","");
            print "\n <td class=\"coltotal\">$F_TOTMPCT</td> ";
        }
    }
    if ($totalByCnt == "Y") {
        $F_TOTCNT = Format_Nbr($TOTCNT,0,"1","","","");
        print "\n <td class=\"coltotal\">$F_TOTCNT</td> ";
    } else {
        print "\n <td >&nbsp; </td > ";
    }
}
print "</table>";
if ($totalRows > 0) {
    print $hrTagAttr;
    require_once "Copyright.php";
}
print "\n </td> </tr> </table>";
require_once "Trailer.php";
print "\n </body> </html> ";

?>