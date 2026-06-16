<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$vendorNumber = $_GET['vendorNumber'];
$vendorPassed = (isset($vendorNumber) && trim($vendorNumber) != '') ? true : null;
$fromItem = (isset($_GET['fromItem'])) ? $_GET['fromItem'] : null;
$toggle = (isset($_GET['toggle'])) ? $_GET['toggle'] : null;

if (! isset($_SESSION['viewCharts'])) {
    $_SESSION['viewCharts'] = 'Y';
}
$toggleChart = (! is_null($toggle) && $_SESSION[viewCharts] != $toggle) ? true : null;
if (! is_null($toggleChart)) {
    $_SESSION['viewCharts'] = ($_SESSION['viewCharts'] == 'Y') ? 'N' : 'Y';
}

$displayChart = ($_SESSION['viewCharts'] == 'Y') ? 'inline-block' : 'none';
$viewHide = ($_SESSION['viewCharts'] == 'Y') ? 'N' : 'Y';
$chartImg = ($_SESSION['viewCharts'] == 'Y') ? $barChartHide : $barChartView;

if ($fromItem) {
    $_POST['qsOper'] = '=';
    $_POST['qsValue'] = $fromItem;
    $_POST['qsName'] = 'IMITEM|null|Item Number|A|U';
    $tag = 'QSEARCH';
}

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

$page_title = "Vendor Dashboard";
$scriptName = "VendorDashboard.php";
$scriptVarBase = "{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber));
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "HHDVDU";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$pageSelectList = 'N';
$advanceSearch = 'N';
$dftOrderBy = array(
    array(
        "IMIMDSU",
        "A",
        "Desc"
    ),
    array(
        "IMITEM",
        "A",
        "Number"
    )
);

// $userPass = VendorUserView($profileHandle, $vendorNumber, "Y");
if ($userPass == "N") {
    require_once 'UserViewErrorInclude.php';
    exit();
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($vendorPassed) {
    $stmtSQL = " Select * From HDVENDV01 Where VMVEND={$vendorNumber} Fetch First Row Only";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $vendorRow = db2_fetch_assoc($sqlResult);
    $vendorName = $vendorRow['VMVNA1'];
}

require_once ($docType);
print "\n
<html> <head> ";
$title = "$vendorNumber $vendorName";
require_once ($headInclude);

print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
print "\n </script> ";

require_once ($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "VENDORDASHBOARD";
if ($formatToPrint == "") {
    require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";

// Program Option Security
$hhdvdu_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $hhdvdu_OPT['sec_01'];
$sec_02 = $hhdvdu_OPT['sec_02'];
$sec_03 = $hhdvdu_OPT['sec_03'];
$sec_04 = $hhdvdu_OPT['sec_04'];
print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y") {
    $maintainVar = "{$scriptVarBase}&amp;fromScript=$scriptName";
    print "\n <td class=\"toolbar\">";
    print "<a href=\"{$baseURL}&amp;toggle=$viewHide\">$chartImg</a>";
    if ($vendorPassed) {
        print "<a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber={$vendorNumber}\" title=\"View Vendor\">&nbsp; {$portalHome}</a>";
    }
    require_once 'FormatToPrint.php';
    require_once 'HelpPage.php';
    print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";
require_once 'ConfMessageDisplay.php';

if ($vendorPassed) {
    print $hrTagAttr;

    print "\n <table $contentTable><colgroup><col width=\"20%\"><col width=\"30%\"><col width=\"30%\">";
    if (isset($vendorNumber)) {
        print "\n <tr><td class=\"hdrdata\" rowspan=\"5\">{$vendorRow['VMVNA1']}<br>";
        if (trim($vendorRow['VMVNA2']) != "") {
            print "\n {$vendorRow['VMVNA2']}<br>";
        }
        if (trim($vendorRow['VMVNA3']) != "") {
            print "\n {$vendorRow['VMVNA3']}<br>";
        }
        if (trim($vendorRow['VMVNA4']) != "") {
            print "\n {$vendorRow['VMVNA4']}<br>";
        }
        $city = trim($vendorRow['VMVCTY']);
        print "\n {$city}, {$vendorRow['VMST']} {$vendorRow['VMZIP']}";
        if (trim($vendorRow['VMCTRY']) != $HDCTCD) {
            $fieldDesc = RetValue("CNCTCD='$vendorRow[VMCTRY]'", "HDCTRY", "CNCDES");
            print "\n <br>{$fieldDesc}";
        }
        print "\n </td>";
        print "\n <td class=\"dsphdr\">Vendor Number: </td><td class=\"dspnmbr\">$vendorNumber</td>";
        $openTotal = Format_Nbr($vendorRow[OPENTOTAL], '2', $amtEditCode, '', '$', '');
        print "\n <td class=\"dsphdr\">Total Open:</td><td class=\"dspnmbr\">$openTotal</td>";
        print "\n </tr>";

        print "\n <tr><td>&nbsp;</td><td>&nbsp;</td>";
        $pastTotal = Format_Nbr($vendorRow[PASTTOTAL], '2', $amtEditCode, '', '$', '');
        print "\n <td class=\"dsphdr\">Past Due Total:</td><td class=\"dspnmbr\">$pastTotal</td>";
        print "\n </tr>";

        print "\n <tr>";
        if ($vendorRow[VMPHON] > "0") {
            $phone = EditPhoneNumber($vendorRow[VMPHON]);
            print "\n <td class=\"dsphdr\">Phone Number: </td>";
            print "\n <td class=\"dspnmbr\">$phone</td>";
        } else {
            print "\n <td>&nbsp;</td><td>&nbsp;</td>";
        }
        $pastDuePct = Format_Nbr($vendorRow[PASTPCT], '2', $pctEditCode, '', '', '%');
        print "\n <td class=\"dsphdr\">Percent Past Due:</td><td class=\"dspnmbr\">$pastDuePct</td>";
        print "\n </tr>";
    }
    print "\n </table> ";
}

if ($viewHide == 'N') {
    print $hrTagAttr;
}
if ($formatToPrint) {
    $dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
    if ($sequence == "Number") {
        $orby = array(
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    } elseif ($sequence == "Desc") {
        $orby = array(
            array(
                "IMIMDSU",
                "A",
                "Desc"
            ),
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    } elseif ($sequence == "ProdClass") {
        $orby = array(
            array(
                "IMPCLS",
                "A",
                "Product Class"
            ),
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    } elseif ($sequence == "StockUOM") {
        $orby = array(
            array(
                "IMUOMS",
                "A",
                "Stocking UOM"
            ),
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    } elseif ($sequence == "PartClass") {
        $orby = array(
            array(
                "IPCLAS",
                "A",
                "Part Class"
            ),
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    } elseif ($sequence == "CURYTDU") {
        $orby = array(
            array(
                "CURYTDU",
                "A",
                "YTD Usage"
            ),
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    } elseif ($sequence == "CURYTDU") {
        $orby = array(
            array(
                "CURYTDU",
                "A",
                "YTD Usage"
            ),
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    } elseif ($sequence == "LASTYTDU") {
        $orby = array(
            array(
                "LASTYTDU",
                "A",
                "Last Year YTD Usage"
            ),
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    } elseif ($sequence == "LASTTOTU") {
        $orby = array(
            array(
                "LASTTOTU",
                "A",
                "Last Year Total Usage"
            ),
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    } elseif ($sequence == "QtyOnHand") {
        $orby = array(
            array(
                "QTYONHAND",
                "A",
                "Quantity On Hand"
            ),
            array(
                "IMITEM",
                "A",
                "Number"
            )
        );
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD") {
    $andOr = $_POST['andOr'];
    require_once 'WildCardClear.php';
    $returnValue = Build_WildCard("max(IMITEM)", "Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "A");
    $returnValue = Build_WildCard("(IMIMDSU)", "Desc", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
    $returnValue = Build_WildCard("IMPCLS", "Product Class", $_POST['srchProdClass'], "U", $_POST['operProdClass'], "A");
    $returnValue = Build_WildCard("IMUOMS", "Stocking UOM", $_POST['srchStockUOM'], "U", $_POST['operStockUOM'], "A");
    require_once 'WildCardUpdate.php';
}

$saveStartRow = $startRow;
require_once 'VendorDashboardCharts.php';
$startRow = $saveStartRow;

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";

$uv_ProductClassName = "IMPCLS";
$uv_ProductInventoryTypeName = "IMITC";
$uv_ProductPartTypeName = "IMPTYP";
require 'UserView.php';

$cyr = '1' . date("y");
require 'stmtSQLClear.php';

$todayCYMD = DateTodayCYMD();
$todayISO = Date_CYMD_ISO($todayCYMD);

$open1 = '0';
$open2 = '0';
$open3 = '0';
$open4 = '0';
$openFuture = '0';
$sales1 = '0';
$sales2 = '0';
$sales3 = '0';
$sales4 = '0';
$salesFuture = '0';
$sched1 = '0';
$sched2 = '0';
$sched3 = '0';
$sched4 = '0';
$schedFuture = '0';
$days1hdr = null;
$days2hdr = null;
$days3hdr = null;
$days4hdr = null;
$futurehdr = null;
$totalhdr = null;
$hdrCnt = 1;

if (intval($vendorDashboardTrendingDays1) > 0) {
    $hdrCnt ++;
    $days1hdr = '0 - ' . intval($vendorDashboardTrendingDays1) . '<br> Days';
    $inc = '+' . intval($vendorDashboardTrendingDays1) . ' days';
    $date1 = date('Y-m-d', strtotime($inc));
    $date1CYMD = Date_FromISO_ToCYMD($date1);
    $dateFuture = $date1CYMD;
    $date1ISO = Date_CYMD_ISO($date1CYMD);
    $open1 = "SUM(CASE WHEN PDRQDT between $todayCYMD and $date1CYMD THEN PDQTOR-(PDQRST+PDQRRT+PDQRFT) ELSE 0 END)";
    $sales1 = "SUM(CASE WHEN ODRQDT between $todayCYMD and $date1CYMD THEN ODQORD-ODQSTD-ODQSTC-ODQOPK-ODQOBO ELSE 0 END)";
    $sched1 = "SUM(CASE WHEN PGDAT between '$todayISO' and '$date1ISO' THEN PGQPEG ELSE 0 END)";
    $openFuture = "SUM(CASE WHEN PDRQDT > $date1CYMD THEN PDQTOR-(PDQRST+PDQRRT+PDQRFT) ELSE 0 END)";
    $schedFuture = "SUM(CASE WHEN PGDAT > '$date1ISO' THEN PGQPEG ELSE 0 END)";

    if (intval($vendorDashboardTrendingDays2) > 0) {
        $hdrCnt ++;
        $days2hdr = intval($vendorDashboardTrendingDays1) + 1 . ' - ' . intval($vendorDashboardTrendingDays2) . '<br> Days';
        $inc = '+' . intval($vendorDashboardTrendingDays1) + 1 . ' days';
        $date1 = date('Y-m-d', strtotime($inc));
        $date11CYMD = Date_FromISO_ToCYMD($date1);
        $inc = '+' . intval($vendorDashboardTrendingDays2) . ' days';
        $date2 = date('Y-m-d', strtotime($inc));
        $date2CYMD = Date_FromISO_ToCYMD($date2);
        $dateFuture = $date2CYMD;
        $date2ISO = Date_CYMD_ISO($date2CYMD);
        $open2 = "SUM(CASE WHEN PDRQDT between $date11CYMD and $date2CYMD THEN PDQTOR-(PDQRST+PDQRRT+PDQRFT) ELSE 0 END)";
        $sales2 = "SUM(CASE WHEN ODRQDT between $date11CYMD and $date2CYMD THEN ODQORD-ODQSTD-ODQSTC-ODQOPK-ODQOBO ELSE 0 END)";
        $sched2 = "SUM(CASE WHEN PGDAT between '$date1ISO' and '$date2ISO' THEN PGQPEG ELSE 0 END)";
        $openFuture = "SUM(CASE WHEN PDRQDT > $date2CYMD THEN PDQTOR-(PDQRST+PDQRRT+PDQRFT) ELSE 0 END)";
        $salesFuture = "SUM(CASE WHEN ODRQDT > $date2CYMD THEN ODQORD-ODQSTD-ODQSTC-ODQOPK-ODQOBO ELSE 0 END)";
        $schedFuture = "SUM(CASE WHEN PGDAT > '$date2ISO' THEN PGQPEG ELSE 0 END)";

        if (intval($vendorDashboardTrendingDays3) > 0) {
            $hdrCnt ++;
            $days3hdr = intval($vendorDashboardTrendingDays2) + 1 . ' - ' . intval($vendorDashboardTrendingDays3) . '<br> Days';
            $inc = '+' . intval($vendorDashboardTrendingDays2) + 1 . ' days';
            $date2 = date('Y-m-d', strtotime($inc));
            $date21CYMD = Date_FromISO_ToCYMD($date2);
            $inc = '+' . intval($vendorDashboardTrendingDays3) . ' days';
            $date3 = date('Y-m-d', strtotime($inc));
            $date3CYMD = Date_FromISO_ToCYMD($date3);
            $dateFuture = $date3CYMD;
            $date3ISO = Date_CYMD_ISO($date3CYMD);
            $open3 = "SUM(CASE WHEN PDRQDT between $date21CYMD and $date3CYMD THEN PDQTOR-(PDQRST+PDQRRT+PDQRFT) ELSE 0 END)";
            $sales3 = "SUM(CASE WHEN ODRQDT between $date21CYMD and $date3CYMD THEN ODQORD-ODQSTD-ODQSTC-ODQOPK-ODQOBO ELSE 0 END)";
            $sched3 = "SUM(CASE WHEN PGDAT between '$date2ISO' and '$date3ISO' THEN PGQPEG ELSE 0 END)";
            $openFuture = "SUM(CASE WHEN PDRQDT > $date3CYMD THEN PDQTOR-(PDQRST+PDQRRT+PDQRFT) ELSE 0 END)";
            $salesFuture = "SUM(CASE WHEN ODRQDT > $date3CYMD THEN ODQORD-ODQSTD-ODQSTC-ODQOPK-ODQOBO ELSE 0 END)";
            $schedFuture = "SUM(CASE WHEN PGDAT > '$date3ISO' THEN PGQPEG ELSE 0 END)";

            if (intval($vendorDashboardTrendingDays4) > 0) {
                $hdrCnt ++;
                $days4hdr = intval($vendorDashboardTrendingDays3) + 1 . ' - ' . intval($vendorDashboardTrendingDays4) . '<br> Days';
                $inc = '+' . intval($vendorDashboardTrendingDays3) + 1 . ' days';
                $date3 = date('Y-m-d', strtotime($inc));
                $date31CYMD = Date_FromISO_ToCYMD($date3);
                $inc = '+' . intval($vendorDashboardTrendingDays4) . ' days';
                $date4 = date('Y-m-d', strtotime($inc));
                $date4CYMD = Date_FromISO_ToCYMD($date4);
                $dateFuture = $date4CYMD;
                $date4ISO = Date_CYMD_ISO($date4CYMD);
                $open4 = "SUM(CASE WHEN PDRQDT between $date31CYMD and $date4CYMD THEN PDQTOR-(PDQRST+PDQRRT+PDQRFT) ELSE 0 END)";
                $sales4 = "SUM(CASE WHEN ODRQDT between $date31CYMD and $date4CYMD THEN ODQORD-ODQSTD-ODQSTC-ODQOPK-ODQOBO ELSE 0 END)";
                $sched4 = "SUM(CASE WHEN PGDAT between '$date3ISO' and '$date4ISO' THEN PGQPEG ELSE 0 END)";
                $openFuture = "SUM(CASE WHEN PDRQDT > $date4CYMD THEN PDQTOR-(PDQRST+PDQRRT+PDQRFT) ELSE 0 END)";
                $salesFuture = "SUM(CASE WHEN ODRQDT > $date4CYMD THEN ODQORD-ODQSTD-ODQSTC-ODQOPK-ODQOBO ELSE 0 END)";
                $schedFuture = "SUM(CASE WHEN PGDAT > '$date4ISO' THEN PGQPEG ELSE 0 END)";
            }
        }
    }
}

$mrpNetable = ($HDPDRL > 0) ? " inner join HDWHSM on IWWHS=WHWHS and WHMRPN='Y'" : "";
$mrpNetable2 = ($HDPDRL > 0) ? " inner join HDWHSM on PDOVWH=WHWHS and WHMRPN='Y'" : "";
$vendorItemsOnly = (isset($vendorNumber) && trim($vendorNumber) != '') ? " inner join HDVCIT on IMITEM=VCITEM and VCVNCS=$vendorNumber and VCVCF='V' " : "";
$userViewSQL = ($uv_Sql != "" && $appendUserView != "N") ? " and ($uv_Sql)" : "";
$vendorItem = ($vendorPassed) ? "max(VCVCIT) " : "'' ";
$curFrom = '1' . date("y") . '0101';
$curTo = DateTodayCYMD();
$lastFrom = $curFrom - 10000;
$lastTo = $curTo - 10000;
$totalTo = '1' . date("y") . '1231';
$totalTo = $totalTo - 10000;

$stmtSQL = "Select IMITEM,IMIMDS,IMIMDSU,VCVCIT,IMPCLS,IMUOMS,coalesce(IPCLAS,'') as IPCLAS,QTYONHAND+coalesce(FLOORSTOCK,0) as QTYONHAND,coalesce(PLANTCYTDU,0)+coalesce(WHSCYTDU,0) as CURYTDU,coalesce(PLANTLYTDU,0)+coalesce(WHSLYTDU,0) as LASTYTDU,coalesce(PLANTLTOTU,0)+coalesce(WHSLTOTU,0) as LASTTOTU,
                   coalesce(OPENPASTDUE,0) as OPENPASTDUE,coalesce(OPEN1,0) as OPEN1,coalesce(OPEN2,0) as OPEN2,coalesce(OPEN3,0) as OPEN3,coalesce(OPEN4,0) as OPEN4,coalesce(OPENFUTURE,0) as OPENFUTURE,coalesce(OPENTOTAL,0) as OPENTOTAL,
                   coalesce(SCHEDPASTDUE,0) as SCHEDPASTDUE,coalesce(SCHED1,0) as SCHED1,coalesce(SCHED2,0) as SCHED2,coalesce(SCHED3,0) as SCHED3,coalesce(SCHED4,0) as SCHED4,coalesce(SCHEDFUTURE,0) as SCHEDFUTURE,coalesce(SCHEDTOTAL,0) as SCHEDTOTAL
                   From (Select max(IMITEM)as IMITEM,max(IMIMDS) as IMIMDS,$vendorItem as VCVCIT,max(IMIMDSU) as IMIMDSU,max(IMPCLS) as IMPCLS,max(IMUOMS) as IMUOMS,sum(IWOHQT) as QTYONHAND
                   From HDIMST inner join HDIWHS on IMITEM=IWITEM $mrpNetable $vendorItemsOnly
                   group by IMITEM) OnHand
            left join (
            Select PDITEM
                , sum(CASE WHEN PDRQDT < $todayCYMD THEN PDQTOR-(PDQRST+PDQRRT+PDQRFT) ELSE 0 END) AS OPENPASTDUE
                , $open1 AS OPEN1
                , $open2 AS OPEN2
                , $open3 AS OPEN3
                , $open4 AS OPEN4
                , $openFuture AS OPENFUTURE
                , sum(PDQTOR-(PDQRST+PDQRRT+PDQRFT)) AS OPENTOTAL
               FROM POPOMD $mrpNetable2 WHERE PDSTAT='O' and PDPOLT='' and (PDQTOR-(PDQRST+PDQRRT+PDQRFT)) > 0
               GROUP BY PDITEM

            ) as OnOrder on PDITEM=IMITEM";

$mrpCount = ($HDPDRL > 0) ? RetValue("PGPPLT>0", "MRMPGM", "char(count(*))") : 0;

if ($mrpCount > 0) {
    $stmtSQL .= " left join (
                    Select PGCPN
                    , sum(CASE WHEN PGDAT < '$todayISO' THEN PGQPEG ELSE 0 END) AS SCHEDPASTDUE
                    , $sched1 AS SCHED1
                    , $sched2 AS SCHED2
                    , $sched3 AS SCHED3
                    , $sched4 AS SCHED4
                    , $schedFuture AS SCHEDFUTURE
                    , SUM(PGQPEG) AS SCHEDTOTAL
                    FROM MRMPGM
                    GROUP BY PGCPN

                ) as Schedule on PGCPN=IMITEM
                left join (Select IPITEM, sum(IPOHFS) AS FLOORSTOCK, max(IPCLAS) as IPCLAS FROM HDIPLT GROUP BY IPITEM) as Floor on IPITEM=IMITEM
                left join (Select PHITEM, sum(CASE WHEN PDBDAT >= {$curFrom} and PDBDAT <= {$curTo} THEN PHYTDU ELSE 0 END) AS PLANTCYTDU
                                        , sum(CASE WHEN PDBDAT >= {$lastFrom} and PDBDAT <= {$lastTo} THEN PHYTDU ELSE 0 END) AS PLANTLYTDU
                                        , sum(CASE WHEN PDBDAT >= {$lastFrom} and PDBDAT <= {$totalTo} THEN PHYTDU ELSE 0 END) AS PLANTLTOTU
                           FROM HDIPLH inner join HDPBED on PHPERD=PDPER# GROUP BY PHITEM) as ItemPltHist on PHITEM=IMITEM";
} else {
    $stmtSQL .= " left join (
    Select ODITEM
    , sum(CASE WHEN ODRQDT < $todayCYMD THEN ODQORD-ODQSTD-ODQSTC-ODQOPK-ODQOBO ELSE 0 END) AS SCHEDPASTDUE
    , $sales1 AS SCHED1
    , $sales2 AS SCHED2
    , $sales3 AS SCHED3
    , $sales4 AS SCHED4
    , $salesFuture AS SCHEDFUTURE
    , SUM(ODQORD-ODQSTD-ODQSTC-ODQOPK-ODQOBO) AS SCHEDTOTAL
    , 0 as FLOORSTOCK
    , '' as IPCLAS
    , 0 as PLANTCYTDU
    , 0 as PLANTLYTDU
    , 0 as PLANTLTOTU
    FROM OEORDT
    GROUP BY ODITEM

    ) as SalesOrder on ODITEM=IMITEM";
}

$stmtSQL .= "
            left join (Select IHITEM, sum(CASE WHEN PDBDAT >= {$curFrom} and PDBDAT <= {$curTo} THEN IHQTYI+IHQKTI+IHQSLD ELSE 0 END) AS WHSCYTDU
                                    , sum(CASE WHEN PDBDAT >= {$lastFrom} and PDBDAT <= {$lastTo} THEN IHQTYI+IHQKTI+IHQSLD ELSE 0 END) AS WHSLYTDU
                                    , sum(CASE WHEN PDBDAT >= {$lastFrom} and PDBDAT <= {$totalTo} THEN IHQTYI+IHQKTI+IHQSLD ELSE 0 END) AS WHSLTOTU
                       FROM HDISLH inner join HDPBED on IHPERD=PDPER# GROUP BY IHITEM) as ItemSalesHist on IHITEM=IMITEM
            Where IMITEM<>' ' $wildCardSearch $userViewSQL ";

$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
$sql_Record_Count = 99999999999;
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));
// echo $stmtSQL;
$qsOpt = "\n <option value=\"IMITEM|null|Item Number|A|U\" title=\"Item Number\" SELECTED>Item Number";
$qsOpt .= "\n <option value=\"IMIMDSU|null|Description|A|U\" title=\"Description\">Description";
$qsOpt .= "\n <option value=\"IMPCLS|null|Product Class|A|U\" title=\"Product Class\">Product Class";
$qsOpt .= "\n <option value=\"IMUOMS|null|Stocking UOM|A|U\" title=\"Stocking UOM\">Stocking UOM";
if ($HDPDRL > 0) {
    $qsOpt .= "\n <option value=\"IPCLAS|null|Part Class|A|U\" title=\"Part Class\">Part Class";
}

print "<table $contentTable> <tr>";

if ($vendorDashboardTrendingFuture == 'Y') {
    $hdrCnt ++;
}
if ($vendorDashboardTrendingTotal == 'Y') {
    $hdrCnt ++;
}
if ($HDPDRL > 0) {
    $colspan = ($vendorPassed) ? 10 : 9;
} else {
    $colspan = ($vendorPassed) ? 9 : 8;
}
print "<tr><th colspan=\"$colspan\">";
require 'QuickSearchOption.php';
print "</th>";
print "<th  class=\"colhdr\" colspan=\"$hdrCnt\">On Order</th>";
print "<th  class=\"colhdr\" colspan=\"$hdrCnt\">Demand</th>";
print "<th  class=\"colhdr\" colspan=\"$hdrCnt\">Balance</th>";
print "</tr>";

$returnValue = OrderBy_Sort("IMITEM");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Item\">{$sortPoint}Item Number</a></th>";
$returnValue = OrderBy_Sort("IMIMDSU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\" title=\"Sequence By Description, Item\">{$sortPoint}Description</a></th>";
if ($vendorPassed) {
    print "\n <th class=\"colhdr\">Vendor<br>Item Number</th>";
}
$returnValue = OrderBy_Sort("IMPCLS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ProdClass\" title=\"Sequence By Product Class, Item\">{$sortPoint}Product<br>Class</a></th>";
$returnValue = OrderBy_Sort("IMUOMS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StockUOM\" title=\"Sequence By Stocking UOM, Item\">{$sortPoint}Stock<br>UOM</a></th>";
if ($HDPDRL > 0) {
    $returnValue = OrderBy_Sort("IPCLAS");
    $sortVar = $returnValue['sortedBy'];
    $sortPoint = $returnValue['sortPoint'];
    print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PartClass\" title=\"Sequence By Part Class, Item\">{$sortPoint}Part<br>Class</a></th>";
}
$returnValue = OrderBy_Sort("CURYTDU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CURYTDU\" title=\"Sequence By Current YTD Usage, Item\">{$sortPoint}YTD<br>Usage</a></th>";
$returnValue = OrderBy_Sort("LASTYTDU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LASTYTDU\" title=\"Sequence By Last YTD Usage, Item\">{$sortPoint}Last Year<br>YTD Usage</a></th>";
$returnValue = OrderBy_Sort("LASTTOTU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LASTTOTU\" title=\"Sequence By Last Year Total Usage, Item\">{$sortPoint}Last Year<br>Total Usage</a></th>";
$returnValue = OrderBy_Sort("QTYONHAND");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyOnHand\" title=\"Sequence By Quantity On Hand, Item\">{$sortPoint}Quantity<br>On Hand</a></th>";
print "\n <th class=\"colhdr\">Past<br>Due</th>";
if ($days1hdr) {
    $toolTip = DateFromCYMD($todayCYMD) . ' thru ' . DateFromCYMD($date1CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days1hdr</span></th>";
}
if ($days2hdr) {
    $toolTip = DateFromCYMD($date11CYMD) . ' thru ' . DateFromCYMD($date2CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days2hdr</span></th>";
}
if ($days3hdr) {
    $toolTip = DateFromCYMD($date21CYMD) . ' thru ' . DateFromCYMD($date3CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days3hdr</span></th>";
}
if ($days4hdr) {
    $toolTip = DateFromCYMD($date31CYMD) . ' thru ' . DateFromCYMD($date4CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days4hdr</span></th>";
}
if ($vendorDashboardTrendingFuture == 'Y') {
    $toolTip = 'Greater than ' . DateFromCYMD($dateFuture);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">Future</span></th>";
}
if ($vendorDashboardTrendingTotal == 'Y') {
    print "\n <th class=\"colhdr\">Total</th>";
}
print "\n <th class=\"colhdr\">Past<br>Due</th>";
if ($days1hdr) {
    $toolTip = DateFromCYMD($todayCYMD) . ' thru ' . DateFromCYMD($date1CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days1hdr</span></th>";
}
if ($days2hdr) {
    $toolTip = DateFromCYMD($date11CYMD) . ' thru ' . DateFromCYMD($date2CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days2hdr</span></th>";
}
if ($days3hdr) {
    $toolTip = DateFromCYMD($date21CYMD) . ' thru ' . DateFromCYMD($date3CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days3hdr</span></th>";
}
if ($days4hdr) {
    $toolTip = DateFromCYMD($date31CYMD) . ' thru ' . DateFromCYMD($date4CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days4hdr</span></th>";
}
if ($vendorDashboardTrendingFuture == 'Y') {
    $toolTip = 'Greater than ' . DateFromCYMD($dateFuture);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">Future</span></th>";
}
if ($vendorDashboardTrendingTotal == 'Y') {
    print "\n <th class=\"colhdr\">Total</th>";
}
print "\n <th class=\"colhdr\">Past<br>Due</th>";
if ($days1hdr) {
    $toolTip = DateFromCYMD($todayCYMD) . ' thru ' . DateFromCYMD($date1CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days1hdr</span></th>";
}
if ($days2hdr) {
    $toolTip = DateFromCYMD($date11CYMD) . ' thru ' . DateFromCYMD($date2CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days2hdr</span></th>";
}
if ($days3hdr) {
    $toolTip = DateFromCYMD($date21CYMD) . ' thru ' . DateFromCYMD($date3CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days3hdr</span></th>";
}
if ($days4hdr) {
    $toolTip = DateFromCYMD($date31CYMD) . ' thru ' . DateFromCYMD($date4CYMD);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">$days4hdr</span></th>";
}
if ($vendorDashboardTrendingFuture == 'Y') {
    $toolTip = 'Greater than ' . DateFromCYMD($dateFuture);
    print "\n <th class=\"colhdr\"><span $helpCursor title=\"$toolTip\">Future</span></th>";
}
if ($vendorDashboardTrendingTotal == 'Y') {
    print "\n <th class=\"colhdr\">Total</th>";
}
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    require 'SetRowClass.php';
    $F_Desc = Format_Quote($row['IMIMDS']);
    print "\n <tr class=\"$rowClass\">";
    print "\n     <td class=\"colalph\"><a href=\"{$baseURL}&amp;fromItem=$row[IMITEM]\" title=\"Filter by Item Number\">$row[IMITEM]</a></td>";
    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$row[IMITEM]}&amp;itemDescription={$F_Desc}\" title=\"View Item\">$F_Desc</a></td> ";
    if ($vendorPassed) {
        print "\n     <td class=\"colalph\">$row[VCVCIT]</td>";
    }
    $pclsDesc = RetValue("PCPCLS='$row[IMPCLS]'", "HDPCLS", "PCPCDS");
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$pclsDesc}\">$row[IMPCLS]</span></td>";
    $uomDesc = RetValue("UMUOM='$row[IMUOMS]'", "HDUOM", "UMUMLD");
    print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$uomDesc}\">$row[IMUOMS]</span></td>";
    if ($HDPDRL > 0) {
        $partClassDesc = RetValue("ICCLAS='$row[IPCLAS]'", "HDMICM", "ICDESC");
        print "\n     <td class=\"colalph\"><span onmouseover=\"this.style.cursor='help';\" title=\"{$partClassDesc}\">$row[IPCLAS]</span></td>";
    }
    $F_CURYTDU = Format_Nbr($row[CURYTDU], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_CURYTDU</td>";
    $F_LASTYTDU = Format_Nbr($row[LASTYTDU], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_LASTYTDU</td>";
    $F_LASTTOTU = Format_Nbr($row[LASTTOTU], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_LASTTOTU</td>";
    $F_QTYONHAND = Format_Nbr($row[QTYONHAND], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_QTYONHAND</td>";
    $F_OPENPASTDUE = Format_Nbr($row[OPENPASTDUE], $qtyNbrDec, $qtyEditCode, '', '', '');

    // $oper = '<';
    // $value = DateInputFromCYMD($date1CYMD);
    // print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=186&amp;fKey1=PDITEM&amp;fVal1={$item}&amp;fDsc1={$F_Desc}&amp;tag=QSEARCH&andOr=clear&amp;qsName=PDRQDT&amp;qsOper={$oper}&amp;qsValue={$value}\" title=\"View Open Items\">$F_OPENPASTDUE</a></td>";

    print "\n     <td class=\"colnmbr\">$F_OPENPASTDUE</td>";
    if ($days1hdr) {
        $F_OPEN1 = Format_Nbr($row[OPEN1], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_OPEN1</td>";
    }
    if ($days2hdr) {
        $F_OPEN2 = Format_Nbr($row[OPEN2], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_OPEN2</td>";
    }
    if ($days3hdr) {
        $F_OPEN3 = Format_Nbr($row[OPEN3], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_OPEN3</td>";
    }
    if ($days4hdr) {
        $F_OPEN4 = Format_Nbr($row[OPEN4], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_OPEN4</td>";
    }
    if ($vendorDashboardTrendingFuture == 'Y') {
        $F_OPENFUTURE = Format_Nbr($row[OPENFUTURE], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_OPENFUTURE</td>";
    }
    if ($vendorDashboardTrendingTotal == 'Y') {
        $F_OPENTOTAL = Format_Nbr($row[OPENTOTAL], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_OPENTOTAL</td>";
    }
    $F_SCHEDPASTDUE = Format_Nbr($row[SCHEDPASTDUE], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_SCHEDPASTDUE</td>";
    if ($days1hdr) {
        $F_SCHED1 = Format_Nbr($row[SCHED1], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_SCHED1</td>";
    }
    if ($days2hdr) {
        $F_SCHED2 = Format_Nbr($row[SCHED2], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_SCHED2</td>";
    }
    if ($days3hdr) {
        $F_SCHED3 = Format_Nbr($row[SCHED3], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_SCHED3</td>";
    }
    if ($days4hdr) {
        $F_SCHED4 = Format_Nbr($row[SCHED4], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_SCHED4</td>";
    }
    if ($vendorDashboardTrendingFuture == 'Y') {
        $F_SCHEDFUTURE = Format_Nbr($row[SCHEDFUTURE], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_SCHEDFUTURE</td>";
    }
    if ($vendorDashboardTrendingTotal == 'Y') {
        $F_SCHEDTOTAL = Format_Nbr($row[SCHEDTOTAL], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_SCHEDTOTAL</td>";
    }

    $row[VARPASTDUE] = $row[QTYONHAND] + $row[FLOORSTOCK] + $row[OPENPASTDUE] - $row[SCHEDPASTDUE];
    $row[VAR1] = $row[VARPASTDUE] + $row[OPEN1] - $row[SCHED1];
    $row[VAR2] = $row[VAR1] + $row[OPEN2] - $row[SCHED2];
    $row[VAR3] = $row[VAR2] + $row[OPEN3] - $row[SCHED3];
    $row[VAR4] = $row[VAR3] + $row[OPEN4] - $row[SCHED4];
    $row[VARFUTURE] = $row[VAR4] + $row[OPENFUTURE] - $row[SCHEDFUTURE];
    $row[VARTOTAL] = $row[QTYONHAND] + $row[OPENTOTAL] - $row[SCHEDTOTAL];

    $F_VARPASTDUE = Format_Nbr($row[VARPASTDUE], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_VARPASTDUE</td>";
    if ($days1hdr) {
        $F_VAR1 = Format_Nbr($row[VAR1], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_VAR1</td>";
    }
    if ($days2hdr) {
        $F_VAR2 = Format_Nbr($row[VAR2], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_VAR2</td>";
    }
    if ($days3hdr) {
        $F_VAR3 = Format_Nbr($row[VAR3], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_VAR3</td>";
    }
    if ($days4hdr) {
        $F_VAR4 = Format_Nbr($row[VAR4], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_VAR4</td>";
    }
    if ($vendorDashboardTrendingFuture == 'Y') {
        $F_VARFUTURE = Format_Nbr($row[VARFUTURE], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_VARFUTURE</td>";
    }
    if ($vendorDashboardTrendingTotal == 'Y') {
        $F_VARTOTAL = Format_Nbr($row[VARTOTAL], $qtyNbrDec, $qtyEditCode, '', '', '');
        print "\n     <td class=\"colnmbr\">$F_VARTOTAL</td>";
    }
    print "\n </tr>";
    $startRow ++;
    $rowCount ++;
}
if ($rowCount == 0) {
    require 'NoRecordsFound.php';
}
print "</table>";
print $hrTagAttr;
require_once 'Copyright.php';

?>
