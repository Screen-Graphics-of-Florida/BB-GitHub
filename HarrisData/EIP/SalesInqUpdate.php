<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once "GenericDirectCallVariables.php";
require_once "setLibraryList.php";
require_once "VarBase.php";

$allUsers              = (isset($_GET['allUsers'])) ? $_GET['allUsers'] : "";
$deleteUser            = (isset($_GET['deleteUser'])) ? $_GET['deleteUser'] : "";
$downloadProfileHandle = (isset($_GET['downloadProfileHandle'])) ? $_GET['downloadProfileHandle'] : "";
$selectCriteria        = (isset($_GET['selectCriteria'])) ? $_GET['selectCriteria'] : "";
$selectDesc            = (isset($_GET['selectDesc'])) ? $_GET['selectDesc'] : "";
$selectGroupBy         = (isset($_GET['selectGroupBy'])) ? $_GET['selectGroupBy'] : "";
$selectSequence        = (isset($_GET['selectSequence'])) ? $_GET['selectSequence'] : 0;
$selectUser            = (isset($_GET['selectUser'])) ? $_GET['selectUser'] : "";
$totalByAmt            = (isset($_GET['totalByAmt'])) ? $_GET['totalByAmt'] : "N";
$totalByCnt            = (isset($_GET['totalByCnt'])) ? $_GET['totalByCnt'] : "N";
$totalByQty            = (isset($_GET['totalByQty'])) ? $_GET['totalByQty'] : "N";
$selectTotals          = (isset($_GET['selectTotals'])) ? $_GET['selectTotals'] : $totalByQty . $totalByAmt . $totalByCnt;
$page_title    = "Sales Inquiry Selection Update";
$scriptName    = "SalesInqUpdate.php";
$scriptVarBase = "{$genericVarBase}&amp;allUsers=" . urlencode($allUsers) . "&amp;selectUser=" . urlencode($selectUser) . "&amp;selectCriteria=" . urlencode($selectCriteria) . "&amp;selectDesc=" . urlencode($selectDesc) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectTotals=" . urlencode($selectTotals);
$selectName    = "SALESINQ";

if ($tag == "DISPLAY") {
    $maintCode = "R";
    $returnValue = Update_User_Selection($profileHandle,$dataBaseID,$maintCode,$userProfile,$selectName,$selectSequence,$selectDesc,$selectTotals,$selectGroupBy,$selectCriteria,$deleteUser);
    $selectName     = $returnValue['selectName'];
    $selectSequence = $returnValue['selectSequence'];
    $selectDesc     = $returnValue['selectDesc'];      
    $selectTotals   = $returnValue['selectTotals'];   
    $selectGroupBy  = $returnValue['selectGroupBy'];   
    $selectCriteria = $returnValue['selectCriteria'];  
    require_once ($docType);
    print "\n <html> <head> ";
    require_once ($headInclude);
    print "\n <script TYPE=\"text/javascript\"> ";
    require_once "CheckEnterChg.php";
    print "\n function validate(chgForm) { ";
    print "\n     if (document.Chg.selectDesc.value ==\"\") ";
    print "\n         {alert('Please enter a description'); return false;} ";
    print "\n     return true; ";
    print "\n } ";
    require_once "NumEdit.php";
    print "\n </script> ";
    require_once ($genericHead);
    print "\n </head> ";
    print "\n <body $bodyTagAttr onLoad=\"window.focus()\" onKeyPress=\"checkEnterChg()\"> ";
    print "\n <table $contentTable> ";
    print "\n <tr><td><h1>$page_title</h1></td> ";
    print "\n <td class=\"toolbar\"> ";
    print "\n <a href=\"javascript:check(document.Chg)\">$selectAcceptImage</a> ";
    print "\n </td> </tr> </table> ";
    print $hrTagAttr;
    print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=UPDATE\"> ";
    print "\n <table $contentTable> ";
    print "\n <tr><td class=\"dsphdr\">Description</td> ";
    print "\n <td class=\"inputalph\"><input name=\"selectDesc\" value=\"$selectDesc\" type=\"text\" size=\"50\" maxlength=\"50\"></td> ";
    print "\n </tr> </table> <form> ";
    print $hrTagAttr;
    print "\n </body> </html> ";
}
if ($tag == "UPDATE") {
    $maintCode = "U";
    $selectDesc = $_POST['selectDesc'];
    $returnValue = Update_User_Selection($profileHandle,$dataBaseID,$maintCode,$userProfile,$selectName,$selectSequence,$selectDesc,$selectTotals,$selectGroupBy,$selectCriteria,$deleteUser);
    $selectName     = $returnValue['selectName'];
    $selectSequence = $returnValue['selectSequence'];
    $selectDesc     = $returnValue['selectDesc'];      
    $selectTotals   = $returnValue['selectTotals'];   
    $selectGroupBy  = $returnValue['selectGroupBy'];   
    $selectCriteria = $returnValue['selectCriteria'];  
    print "\n <script TYPE=\"text/javascript\"> ";
    print "\n window.close(); ";
    print "\n </script> ";
}
?>