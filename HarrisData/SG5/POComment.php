<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$vendorNumber = (isset($_GET['vendorNumber'])) ? $_GET['vendorNumber'] : 0;
$vendorName = (isset($_GET['vendorName'])) ? $_GET['vendorName'] : '';
$purchaseOrder = (isset($_GET['purchaseOrder'])) ? $_GET['purchaseOrder'] : 0;
$orderControl = (isset($_GET['orderControl'])) ? $_GET['orderControl'] : 0;
$itemNumber = (isset($_GET['itemNumber'])) ? $_GET['itemNumber'] : '';
$itemDesc = (isset($_GET['itemDesc'])) ? $_GET['itemDesc'] : '';
$cmtLine = (isset($_GET['cmtLine'])) ? $_GET['cmtLine'] : 0;

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "PO Comments";
$scriptName = "POComment.php";
$scriptVarBase = "{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrder=" . urlencode(trim($purchaseOrder)) . "&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;itemNumber=" . urlencode(trim($itemNumber)) . "&amp;itemDesc=" . urlencode(trim($itemDesc)) . "&amp;cmtLine=" . urlencode(trim($cmtLine));
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $prtMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;


require_once($docType);
print "\n <html> \n	<head>";
require_once($headInclude);
$formName = "Chg";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
print "\n function confirmCancel(text) {return confirm(\"Confirm Cancel of Picked Quantities\")}";
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
print "\n <table $contentTable> ";
print "\n     <colgroup> <col width=\"60%\"><col width=\"35%\"> ";
print "\n     <tr><td><h1>$page_title</h1></td> ";
print "\n         <td class=\"toolbar\"> ";
print "\n <a href=\"javascript:opener.location.href=opener.location.href; javascript:window.close()\">$closeImageMed</a> ";
print "\n </td></tr></table> ";

print "\n <table $contentTable>";
Format_Header_URL("Vendor", $vendorName, $vendorNumber, "");
if ($purchaseOrder > 0) {
    Format_Header_URL("Order", $purchaseOrder, "", "");
}
if ($cmtLine > 0) {
    Format_Header_URL("Line", $cmtLine, "", "");
    Format_Header_URL("Item", $itemDesc, $itemNumber, "");
}
print "\n </table> ";
print $hrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .= " Select distinct DODOCT, DODESC, upper(DODESC) as DODESCU  ";
$fileSQL .= " HDDOCT ";
$selectSQL = "(DOAPID='PO' or DOAPID='  ') ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By DODOCT ";
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

print "<table $contentTable><tr>";
if ($cmtLine > 0 && $cmtLine < 999) {
    print "\n <th class=\"colhdr\">Detail</th>";
} else {
    print "\n <th class=\"colhdr\">Order<br>Header</th>";
    print "\n <th class=\"colhdr\">Order<br>Trailer</th>";
}
print "\n <th class=\"colhdr\">Document</th>";
print "\n <th class=\"colhdr\">Description</th>";
if ($cmtLine > 0 && $cmtLine < 999) {
    print "\n <th class=\"colhdr\">Vendor<br>Item</th>";
    print "\n <th class=\"colhdr\">Extended<br>Description</th>";
} else {
    print "\n <th class=\"colhdr\">Vendor<br>Header</th>";
    print "\n <th class=\"colhdr\">Vendor<br>Trailer</th>";
    print "\n <th class=\"colhdr\">Document<br>Header</th>";
    print "\n <th class=\"colhdr\">Document<br>Trailer</th>";
}
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    $doct = trim($row[DODOCT]);
    if ($cmtLine > 0 && $cmtLine < 999) {
        if ($purchaseOrder > 0) {
            $dtlCnt = RetValue("OCORD#=$purchaseOrder and OCORL#=$cmtLine and OCDOCT='$doct'", "POOCMT", "char(count(*))");
        } else {
            $dtlCnt = RetValue("CWOCTL=$orderControl and CWLINE=$cmtLine and CWDOCT='$doct'", "POCMTW", "char(count(*))");
        }
        $viCnt = RetValue("VCVCF='V' and VCNMBR=$vendorNumber and VCITEM='$itemNumber' and VCDOCT='$doct'", "HDVCIT inner join HDVCIC on VCID=ICIC", "char(count(*))");
        $ixCnt = RetValue("IXITEM='$itemNumber' and IXDOCT='$doct'", "HDIMXD", "char(count(*))");
    } else {
        if ($purchaseOrder > 0) {
            $hdrCnt = RetValue("OCORD#=$purchaseOrder and OCORL#=000 and OCDOCT='$doct'", "POOCMT", "char(count(*))");
            $trlCnt = RetValue("OCORD#=$purchaseOrder and OCORL#=999 and OCDOCT='$doct'", "POOCMT", "char(count(*))");
        } else {
            $hdrCnt = RetValue("CWOCTL=$orderControl and CWLINE=000 and CWDOCT='$doct'", "POCMTW", "char(count(*))");
            $trlCnt = RetValue("CWOCTL=$orderControl and CWLINE=999 and CWDOCT='$doct'", "POCMTW", "char(count(*))");
        }
        $vhCnt = RetValue("CXVCF='V' and CXNMBR=$vendorNumber and CXDOCT='$doct' and CXHT='H'", "HDCCMT", "char(count(*))");
        $vtCnt = RetValue("CXVCF='V' and CXNMBR=$vendorNumber and CXDOCT='$doct' and CXHT='T'", "HDCCMT", "char(count(*))");
        $dhCnt = RetValue("(DCAPID='PO' or DCAPID=' ') and DCDOCT='$doct' and DCHT='H'", "HDDCMT", "char(count(*))");
        $dtCnt = RetValue("(DCAPID='PO' or DCAPID=' ') and DCDOCT='$doct' and DCHT='T'", "HDDCMT", "char(count(*))");
    }

    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\">";
    $maintainVar = "{$scriptVarBase}&amp;doct=" . trim($doct) . "&amp;tag=MAINTAIN";
    $cmtTable = ($purchaseOrder > 0) ? 'POOCMT' : 'POCMTW';
    if ($cmtLine > 0 && $cmtLine < 999) {
        $image = setImage($dtlCnt);
        print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}POCommentMaintain.php{$maintainVar}&amp;fromTable={$cmtTable}&amp;cmtLine={$cmtLine}\">$image</a></td>";
    } else {
        $image = setImage($hdrCnt);
        print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}POCommentMaintain.php{$maintainVar}&amp;fromTable={$cmtTable}&amp;cmtLine=000\">$image</a></td>";
        $image = setImage($trlCnt);
        print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}POCommentMaintain.php{$maintainVar}&amp;fromTable={$cmtTable}&amp;cmtLine=999\">$image</a></td>";
    }
    print "\n     <td class=\"colalph\">$doct</td> ";
    print "\n     <td class=\"colalph\">$row[DODESC]</td> ";
    if ($cmtLine > 0 && $cmtLine < 999) {
        $image = setImage($viCnt);
        print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}POCommentMaintain.php{$maintainVar}&amp;fromTable=HDVCIC&amp;cmtLine={$cmtLine}\">$image</a></td>";
        $image = setImage($ixCnt);
        print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}POCommentMaintain.php{$maintainVar}&amp;fromTable=HDIMXD&amp;cmtLine={$cmtLine}\">$image</a></td>";
    } else {
        $image = setImage($vhCnt);
        print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}POCommentMaintain.php{$maintainVar}&amp;fromTable=HDCCMT&amp;cmtLine=000\">$image</a></td>";
        $image = setImage($vtCnt);
        print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}POCommentMaintain.php{$maintainVar}&amp;fromTable=HDCCMT&amp;cmtLine=999\">$image</a></td>";
        $image = setImage($dhCnt);
        print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}POCommentMaintain.php{$maintainVar}&amp;fromTable=HDDCMT&amp;cmtLine=000\">$image</a></td>";
        $image = setImage($dtCnt);
        print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}POCommentMaintain.php{$maintainVar}&amp;fromTable=HDDCMT&amp;cmtLine=999\">$image</a></td>";
    }
    print "\n </tr>";
    $startRow++;
    $rowCount++;
}
if ($rowCount == 0) {
    require 'NoRecordsFound.php';
}
print "</table>";

print "$hrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
print "\n </body> \n </html>";

function setImage($cnt)
{
    global $commentExistImage, $commentImage;
    return ($cnt > 0) ? $commentExistImage : $commentImage;
}

?>
