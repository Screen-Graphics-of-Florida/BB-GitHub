<?php
require_once 'GetURLParm.php';
require_once 'setLibraryList.php';
require_once 'VarBase.php';
require_once "ARControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'ErrorNoWarning.php';
require_once 'GenericDirectCallVariables.php';
require_once "InventoryControl$dataBaseID.php";

$selectSequence = (isset($_GET['selectSequence'])) ? $_GET['selectSequence'] : "";
$page_title     = 'Sales Inquiry Selection Criteria';
$selectName     = 'SALESINQ';

require_once 'OperSel_Functions.php';
require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onLoad=\"window.focus()\" onBlur=\"window.close()\">";
print "\n <table $contentTable> <tr>";
print "\n <tr><td><h1>$page_title</h1></td> ";
print "\n <td class=\"toolbar\">";
require 'CloseWindow.php';
print "\n </td> </tr> </table> ";
print $hrTagAttr;
$maintCode = 'R';
$returnValue = Retrieve_User_Selection($profileHandle, $dataBaseID, $maintCode, $userProfile, $selectName, $selectSequence, $selectDesc, $selectTotals, $selectGroupBy, $selectCriteria);
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

print "\n <table $contentTable> <tr>";
print "\n <tr><td class=\"dsphdr\">Description</td>";
print "\n     <td class=\"dspalph\">$selectDesc</td></tr>";
$dspTotalsBy = " ";
if (substr($selectTotals,0,1) == "Y") {
    $dspTotalsBy .= "Quantity&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
}
if (substr($selectTotals,1,1) == "Y") {
    $dspTotalsBy .= "Amount&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
}   
if (substr($selectTotals,2,1) == "Y") {
    $dspTotalsBy .= "Count";
}  
$dspTotalsBy = ltrim($dspTotalsBy);
if ($dspTotalsBy != "") {
    print "\n <tr ><td class=\"dsphdr\" > Display Totals By </td>";
    print "\n <td class=\"dspalph\">$dspTotalsBy";
    print "\n </td ></tr>";
}
print "\n </table> <br>";
print "\n <table $contentTable>";
print "\n <tr><td>&nbsp;</td> ";
print "\n <td class=\"colhdr\">Operand</td>";
print "\n <td class=\"colhdr\">From</td>";
print "\n <td class=\"colhdr\">&nbsp; To &nbsp;</td>";
print "\n <td class=\"colhdr\">Group<br>By</td>";
print "\n </tr>";
$buildArray = array();
$numElements = 0;
$buildArray = Build_DspField("DHITEM", "Item Number", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHIMDS", "Item Description", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHBLTO", "Bill-To Number", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHSHTO", "Ship-To Number", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMCNA1", "Ship-To Name", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMCNA2", "Address 1", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMCNA3", "Address 2", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMCNA4", "Address 3", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMCCTY", "City", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMST",   "State", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMZIP",  "Zip Code", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHDTLI", "Invoice Date", "D", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("@@LIYR", "Invoice Year", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("@@LIQT", "Invoice Quarter", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("@@LIMO", "Invoice Month", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("@@LIWK", "Invoice Week", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHPCLS", "Product Class", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHPGRP", "Product Group", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMITC",  "Inventory Type", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("HHDOTS", "Shipped Date", "D", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("@@SDYR", "Shipped Year", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("@@SDQT", "Shipped Quarter", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("@@SDMO", "Shipped Month", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("@@SDWK", "Shipped Week", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHSLPR", "Selling Price", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHQSTC", "Quantity Sold", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("HHSLSM", "Salesman Number", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMCCLS", "Customer Class", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMCRGN", "Region", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHLOC#", "Location Number", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHWH",   "Warehouse Number", "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHSVSV", "Ship Via Code", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("DHSVDS", "Ship Via Description", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("HHORTY", "Order Type", "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMUDF1", $CRSCR1, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMUDF2", $CRSCR2, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMUDF3", $CRSCR3, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMUDF4", $CRSCR4, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("CMUDF5", $CRSCR5, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDA1", $CIIAS1, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDA2", $CIIAS2, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDA3", $CIIAS3, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDA4", $CIIAS4, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDA5", $CIIAS5, "A", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDN1", $CIINS1, "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDN2", $CIINS2, "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDN3", $CIINS3, "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDN4", $CIINS4, "N", $selectGroupBy, $selectCriteria, $buildArray);
$buildArray = Build_DspField("IMUDN5", $CIINS5, "N", $selectGroupBy, $selectCriteria, $buildArray);

usort($buildArray, function($a, $b) {
    $thisGroup = $a['seqField'] - $b['seqField'];
    if ($thisGroup === 0) {
        return strcmp($a['fieldDesc'], $b['fieldDesc']);
    }
    return $thisGroup;
});    	

for ($i=0; $i<$numElements; $i++) {
    if ($buildArray[$i]['seqField'] == 0) {$buildArray[$i]['seqField'] = "";}
    print "\n <tr><td class=\"dsphdr\">{$buildArray[$i]['fieldDesc']}</td> ";
    print "\n <td class=\"dspalph\">{$buildArray[$i]['operDesc']}</td> ";
    print "\n <td class=\"dspalph\"> &nbsp; {$buildArray[$i]['fromField']}</td> ";
    print "\n <td class=\"dspalph\"> &nbsp; {$buildArray[$i]['toField']}</td> ";
    print "\n <td class=\"dspcode\">{$buildArray[$i]['seqField']}</td> ";
    print "\n </tr> ";    
}
print "\n </table>";
print $hrTagAttr;
print "\n </body> </html>";
?>