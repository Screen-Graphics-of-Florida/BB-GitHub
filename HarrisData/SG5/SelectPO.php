<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$tabID = (isset($_GET['tabID']))  ? $_GET['tabID'] : "REVIEW";
$vendorNumber = $_GET['vendorNumber'];
$vendorName   = $_GET['vendorName'];
$purchaseOrderNumber  = $_GET['purchaseOrderNumber'];
$orderSequence= $_GET['orderSequence'];
$lineNumber   = $_GET['lineNumber'];
$releaseNumber = $_GET['releaseNumber'];
$resetPrint = $_GET['resetPrint'];
$_SESSION['openPOFmt'] = $formatToPrint;
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

if ($resetPrint == 'Y') {
    $stmtSQL = " Update POPOMS Set POFL01='1' Where POPO={$purchaseOrderNumber}";
    $status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
}

if ($vendorNumber == '') {
    $vendorNumber=RetValue("POPO=$purchaseOrderNumber", "POPOMS", "coalesce(POVEND,0)");
    if ($vendorNumber == 0) {
        $vendorNumber=RetValue("PHPO=$purchaseOrderNumber and PHSEQ#=1", "POPOHH", "coalesce(PHVEND,0)");
    }
}
if ($vendorName == '') {
    $vendorName=RetValue("VMVEND=$vendorNumber", "HDVEND", "VMVNA1");
}

$page_title    = "Select PO";
$scriptName    = "SelectPO.php";
$scriptVarBase = "{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber));
$altScriptVarBase = "{$altVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber));
$baseURL="{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$programName   = "HPOPEM";
$hpopem_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$updOrderFlags=$hpopem_OPT['sec_06'];

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
require_once 'SelectPO.js';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "SELECTPURCHASEORDER";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
require_once 'SelectPOTabs.php';

if ($tabID == "REVIEW")         {print " <div id=\"poopenorder\"><img src=\"/images/ajax-loader.gif\" alt=\"Loading... \" class=\"loading\" onLoad=\"getPO('$baseVar', '$vendorNumber', '" . urlencode(trim($vendorName)) . "', '$purchaseOrderNumber')\"></div>";}
elseif ($tabID == "RECEIPTS")   {print " <div id=\"pohistory\"><img src=\"/images/ajax-loader.gif\" alt=\"Loading... \" class=\"loading\" onLoad=\"getPOHistory('$baseVar', '$vendorNumber', '" . urlencode(trim($vendorName)) . "', '$purchaseOrderNumber', '$orderSequence')\"></div>";}
elseif ($tabID == "LINE")       {print " <div id=\"poline\"><img src=\"/images/ajax-loader.gif\" alt=\"Loading... \" class=\"loading\" onLoad=\"getPOLine('$baseVar', '$vendorNumber', '" . urlencode(trim($vendorName)) . "', '$purchaseOrderNumber', '$lineNumber', '$releaseNumber')\"></div>";}
elseif ($tabID == "COMMENTS")   {print " <div id=\"pocomments\"><img src=\"/images/ajax-loader.gif\" alt=\"Loading... \" class=\"loading\" onLoad=\"getPOComments('$baseVar', '$vendorNumber', '" . urlencode(trim($vendorName)) . "', '$purchaseOrderNumber')\"></div>";}
elseif ($tabID == "HEADER")     {print " <div id=\"poheader\"><img src=\"/images/ajax-loader.gif\" alt=\"Loading... \" class=\"loading\" onLoad=\"getPOHeader('$baseVar', '$vendorNumber', '" . urlencode(trim($vendorName)) . "', '$purchaseOrderNumber')\"></div>";}
elseif ($tabID == "FLAGS")      {print " <div id=\"poflags\"><img src=\"/images/ajax-loader.gif\" alt=\"Loading... \" class=\"loading\" onLoad=\"getPOFlags('$baseVar', '$vendorNumber', '" . urlencode(trim($vendorName)) . "', '$purchaseOrderNumber')\"></div>";}

print "\n  </div></div>";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";

?>
