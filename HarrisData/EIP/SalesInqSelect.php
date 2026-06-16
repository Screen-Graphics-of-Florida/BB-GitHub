<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "ARControl$dataBaseID.php";
require_once "InventoryControl$dataBaseID.php";
require_once 'VarBase.php';

$maintenanceCode       = (isset($_GET['maintenanceCode'])) ? $_GET['maintenanceCode'] : "";
$allUsers              = (isset($_GET['allUsers'])) ? $_GET['allUsers'] : "";
$selectUser            = (isset($_GET['selectUser'])) ? $_GET['selectUser'] : "";
$downloadProfileHandle = (isset($_GET['downloadProfileHandle'])) ? $_GET['downloadProfileHandle'] : "";
$formatToPrint         = (isset($_GET['formatToPrint'])) ? $_GET['formatToPrint'] : "";
$grpFldLvl             = (isset($_GET['grpFldLvl'])) ? $_GET['grpFldLvl'] : 0;
$selectCriteria        = (isset($_GET['selectCriteria'])) ? $_GET['selectCriteria'] : "";
$selectDesc            = (isset($_GET['selectDesc'])) ? $_GET['selectDesc'] : "";
$selectGroupBy         = (isset($_GET['selectGroupBy'])) ? $_GET['selectGroupBy'] : "";
$selectSequence        = (isset($_GET['selectSequence'])) ? $_GET['selectSequence'] : 0;
$selectTotals          = (isset($_GET['selectTotals'])) ? $_GET['selectTotals'] : 0;
$userSQL               = (isset($_GET['userSQL'])) ? $_GET['userSQL'] : '';
$deleteUser            = (isset($_GET['deleteUser'])) ? $_GET['deleteUser'] : "";

$page_title         = "Sales Inquiry Selection";
$scriptName         = "SalesInqSelect.php";
$scriptVarBase      = "{$genericVarBase}&amp;allUsers=" . urlencode($allUsers) . "&amp;selectUser=" . urlencode($selectUser);
$programName        = "HSYUSL_W";
$pageID             = "SALESINQSELECT";
$selectName         = "SALESINQ";
require_once 'OperSel_Functions.php';

if ($tag == "DISPLAY") {
    $maintCode = "S";
    $returnValue = Update_User_Selection($profileHandle,$dataBaseID,$maintCode,$selectUser,$selectName,$selectSequence,$selectDesc,$selectTotals,$selectGroupBy,$selectCriteria,$deleteUser);
    $selectName     = $returnValue['selectName'];
    $selectSequence = $returnValue['selectSequence'];
    $selectDesc     = $returnValue['selectDesc'];      
    $selectTotals   = $returnValue['selectTotals'];   
    $selectGroupBy  = $returnValue['selectGroupBy'];   
    $selectCriteria = $returnValue['selectCriteria'];  
    if ($selectGroupBy != "") {
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}SalesInqGroupBy.php{$scriptVarBase}&amp;tag=PROCESS&amp;downloadProfileHandle=" . urlencode($profileHandle) . "&amp;selectSequence=" . urlencode($selectSequence) . "&amp;userSQL=" . urlencode($userSQL) . "&amp;grpFldLvl=1&amp;timeStamp=" . urlencode(date("Y-m-d-H.i.s.u") ) . "\">";
    } else {
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}SalesInqDisplay.php{$scriptVarBase}&amp;tag=PROCESS&amp;downloadProfileHandle=" . urlencode($profileHandle) . "&amp;selectSequence=". urlencode($selectSequence) . "&amp;userSQL=" . urlencode($userSQL) . "&amp;timeStamp=" . urlencode(date("Y-m-d-H.i.s.u") ) . "\">";
    }
}

if ($tag == "MAINTAIN") {
    $prog_OPT = pgmOptSecurity ($profileHandle,$dataBaseID,$programName);
    $sec_01 = $prog_OPT ['sec_01'];
    $sec_02 = $prog_OPT ['sec_02'];
    if (($sec_02 == "N" && $maintenanceCode != "A") || ($sec_01 == "N" && $maintenanceCode == "A")) {
        require_once 'ProgSecurityError.php';
    } else {
        $tag = "SELECT";
    }
}

if ($tag == "SELECT") {
    $selectGroupBy = "";
    $selectCriteria = "";
    if ($selectDesc != "") {
        $maintCode = "S";
        $returnValue = Update_User_Selection($profileHandle,$dataBaseID,$maintCode,$selectUser,$selectName,$selectSequence,$selectDesc,$selectTotals,$selectGroupBy,$selectCriteria,$deleteUser);
        $selectName     = $returnValue['selectName'];
        $selectSequence = $returnValue['selectSequence'];
        $selectDesc     = $returnValue['selectDesc'] ;
        $selectTotals   = $returnValue['selectTotals'];
        $selectGroupBy  = $returnValue['selectGroupBy'];
        $selectCriteria = $returnValue['selectCriteria'];
    }
    $tag = "REPORT";
}

if ($tag == "RETRIEVE") {
    $selectGroupBy = "";
    $selectCriteria = "";
    $maintCode =  "R";
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
    $tag = "REPORT";
}

if ($tag == "REPORT") {
    require_once ($docType);
    print "\n <html> \n <head>";
    require_once ($headInclude);
    $formName = "Chg";
    print "\n <script TYPE=\"text/javascript\">";
    print "\n function confirmReset() { ";
    print "\n     if (confirm(\"Confirm Reset Of Form\")) { ";
    print "\n         document.Chg.reset();";
    print "\n     } ";
    print "\n     return true;";
    print "\n }";
    print "\n function confirmClear() {return confirm(\"Confirm Clear Of Form\");}";
    require_once 'DateEdit.php';
    require_once 'Menu.js';
    require_once 'NumEdit.php';
    require_once 'UpperCase.php';
    require_once 'CheckEnterChg.php';
    require_once 'CalendarInclude.php';

    print "\n function validate(chgForm) { ";
    print "\n     if (editNum(document.Chg.groupItemNbr, 2, 0) && ";
    print "\n         editNum(document.Chg.groupItemDesc, 2, 0) && ";
    print "\n         editNum(document.Chg.fromBillToNbr, 7, 0) && ";
    print "\n         editNum(document.Chg.toBillToNbr, 7, 0) && ";
    print "\n         editNum(document.Chg.groupBillToNbr, 2, 0) && ";
    print "\n         editNum(document.Chg.fromShipToNbr, 7, 0) && ";
    print "\n         editNum(document.Chg.toShipToNbr, 7, 0) && ";
    print "\n         editNum(document.Chg.groupShipToNbr, 2, 0) && ";
    print "\n         editNum(document.Chg.groupShipToName, 2, 0) && ";
    print "\n         editNum(document.Chg.groupAddress1, 2, 0) && ";
    print "\n         editNum(document.Chg.groupAddress2, 2, 0) && ";
    print "\n         editNum(document.Chg.groupAddress3, 2, 0) && ";
    print "\n         editNum(document.Chg.groupCity, 2, 0) && ";
    print "\n         editNum(document.Chg.groupState, 2, 0) && ";
    print "\n         editNum(document.Chg.groupZip, 2, 0) && ";
    print "\n         editNum(document.Chg.groupInvDate, 2, 0) && ";
    print "\n         editNum(document.Chg.groupProdClass, 2, 0) && ";
    print "\n         editNum(document.Chg.groupProdGroup, 2, 0) && ";
    print "\n         editNum(document.Chg.groupInvType, 2, 0) && ";
    print "\n         editNum(document.Chg.groupShipDate, 2, 0) && ";
    print "\n         editNum(document.Chg.fromSellPrice, 8, 5) && ";
    print "\n         editNum(document.Chg.toSellPrice, 8, 5) && ";
    print "\n         editNum(document.Chg.groupSellPrice, 2, 0) && ";
    print "\n         editNum(document.Chg.fromQtySold, 9, 4) && ";
    print "\n         editNum(document.Chg.toQtySold, 9, 4) && ";
    print "\n         editNum(document.Chg.groupQtySold, 2, 0) && ";
    print "\n         editNum(document.Chg.fromSalesman, 3, 0) && ";
    print "\n         editNum(document.Chg.toSalesman, 3, 0) && ";
    print "\n         editNum(document.Chg.groupSalesman, 2, 0) && ";
    print "\n         editNum(document.Chg.groupCustClass, 2, 0) && ";
    print "\n         editNum(document.Chg.groupRegion, 2, 0) && ";
    print "\n         editNum(document.Chg.fromLoc, 3, 0) && ";
    print "\n         editNum(document.Chg.toLoc, 3, 0) && ";
    print "\n         editNum(document.Chg.groupLoc, 2, 0) && ";
    print "\n         editNum(document.Chg.fromWhs, 3, 0) && ";
    print "\n         editNum(document.Chg.toWhs, 3, 0) && ";
    print "\n         editNum(document.Chg.groupWhs, 2, 0) && ";
    print "\n         editNum(document.Chg.groupShipViaCode, 2, 0) && ";
    print "\n         editNum(document.Chg.groupShipViaDesc, 2, 0) && ";
    print "\n         editNum(document.Chg.groupOrderType, 2, 0) ";
    if ($CRSCR1 != "") {print "\n && editNum(document.Chg.groupCustUDA1, 2, 0) ";}
    if ($CRSCR2 != "") {print "\n && editNum(document.Chg.groupCustUDA2, 2, 0) ";}
    if ($CRSCR3 != "") {print "\n && editNum(document.Chg.groupCustUDA3, 2, 0) ";}
    if ($CRSCR4 != "") {print "\n && editNum(document.Chg.groupCustUDA4, 2, 0) ";}
    if ($CRSCR5 != "") {print "\n && editNum(document.Chg.groupCustUDA5, 2, 0) ";}
    if ($CIIAS1 != "") {print "\n && editNum(document.Chg.groupItemUDA1, 2, 0) ";}
    if ($CIIAS2 != "") {print "\n && editNum(document.Chg.groupItemUDA2, 2, 0) ";}
    if ($CIIAS3 != "") {print "\n && editNum(document.Chg.groupItemUDA3, 2, 0) ";}
    if ($CIIAS4 != "") {print "\n && editNum(document.Chg.groupItemUDA4, 2, 0) ";}
    if ($CIIAS5 != "") {print "\n && editNum(document.Chg.groupItemUDA5, 2, 0) ";}
    if ($CIINS1 != "") {print "\n && editNum(document.Chg.groupItemUDN1, 2, 0) ";}
    if ($CIINS2 != "") {print "\n && editNum(document.Chg.groupItemUDN2, 2, 0) ";}
    if ($CIINS3 != "") {print "\n && editNum(document.Chg.groupItemUDN3, 2, 0) ";}
    if ($CIINS4 != "") {print "\n && editNum(document.Chg.groupItemUDN4, 2, 0) ";}
    if ($CIINS5 != "") {print "\n && editNum(document.Chg.groupItemUDN5, 2, 0) ";}
    print "\n     ) ";
    print "\n         return true; ";
    print "\n } ";
    print "\n </script> \n";

    require_once ($genericHead);
    print "\n </head> ";
    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
    print "\n <tr><td><h1>$page_title</h1></td>";
    print "\n <td>&nbsp;</td> ";
    print "\n <td class=\"toolbar\">";
    if ($allUsers == "") {
        print "\n     <a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;fKey1=SLUSER&amp;fVal1=" . urlencode($userProfile) . "&amp;tblID=528\">$previousImage</a> ";
    } else {
        print "\n     <a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=529\">$previousImage</a> ";
    }
    print "\n     <a href=\"javascript:check(document.Chg)\">$selectAcceptImage</a> ";
    print "\n     <a onClick=\"return confirmReset()\">$selectResetImage</a> ";
    print "\n     <a onClick=\"return confirmClear()\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=SELECT&amp;selectUser=" . urlencode($selectUser) . "\">$selectClearImage</a> ";
    $medIcon = "Y";
    require_once 'HelpPage.php';
    print "\n </td> </tr> </table> ";
    print $hrTagAttr;
    print "\n \n <form class=\"formClass\" METHOD=\"POST\" NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=PROCESS&amp;selectSequence=" . urlencode($selectSequence) . "&amp;timeStamp=" . date("Y-m-d-H.i.s.u") ."\">";
    print "\n <table $contentTable> <colgroup> <col width=\"160*\"> </colgroup>";
    print "\n <tr><td class=\"dsphdr\">Description</td>";
    print "\n <td class=\"inputalph\"><input name=\"selectDesc\" type=\"text\" value=\"$selectDesc\" size=\"50\" maxlength=\"50\"></td>";
    print "\n </tr> ";

    $checkedByQty = "";
    $checkedByAmt = "";
    $checkedByCnt = "";
    if (substr($selectTotals,0,1) == "Y") {
        $checkedByQty = "CHECKED";
    }
    if (substr($selectTotals,1,1) == "Y") {
        $checkedByAmt = "CHECKED";
    }
    if (substr($selectTotals,2,1) == "Y") {
        $checkedByCnt = "CHECKED";
    }

    print "\n <tr><td class=\"dsphdr\">Display Totals By </td> ";
    print "\n <td class=\"dspcode\"><input name=\"totalByQty\" type=\"checkbox\" $checkedByQty VALUE=\"Y\">Quantity";
    print "\n <input name=\"totalByAmt\" type=\"checkbox\" $checkedByAmt VALUE=\"Y\">Amount";
    print "\n <input name=\"totalByCnt\" type=\"checkbox\" $checkedByCnt VALUE=\"Y\">Count";
    print "\n </td> </tr> </table> ";
    print "\n <table $contentTable> ";
    print "\n <tr><td>&nbsp;</td> ";
    print "\n <td class=\"colhdr\">Operand</td> ";
    print "\n <td class=\"colhdr\">From</td> ";
    print "\n <td class=\"colhdr\">To</td> ";
    print "\n <td class=\"colhdr\">Group<br>By</td> </tr> ";
    $returnValue = Retrieve_Field("DHITEM", $selectGroupBy, $selectCriteria);
    $operNbr = "operItemNbr";
    print "\n <tr><td class=\"dsphdr\">Item Number</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromItemNbr\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"><a href=\"{$homeURL}{$phpPath}ItemSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=" . urlencode($fromItemNbr) . "&amp;fldDesc=" . urlencode($fromItemDescH) . "\" onclick=\"$searchWinVar\"> $searchImage &nbsp; </a><input name=\"fromItemDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toItemNbr\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"><a href=\"{$homeURL}{$phpPath}ItemSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=" . urlencode($toItemNbr) . "&amp;fldDesc=" . urlencode($toItemDescH) . "\" onclick=\"$searchWinVar\"> $searchImage &nbsp; </a><input name=\"toItemDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemNbr\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
 
    $returnValue = Retrieve_Field("DHIMDS", $selectGroupBy, $selectCriteria);
    $operNbr = "operItemDesc";
    print "\n <tr><td class=\"dsphdr\">Item Description</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromItemDesc\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"30\"><a href=\"{$homeURL}{$phpPath}ItemSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromItemNbrH&amp;fldDesc=fromItemDesc\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromItemNbrH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toItemDesc\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"30\"><a href=\"{$homeURL}{$phpPath}ItemSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toItemNbrH&amp;fldDesc=toItemDesc\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toItemNbrH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemDesc\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";

    $returnValue = Retrieve_Field("DHBLTO", $selectGroupBy, $selectCriteria);
    $operNbr = "operBillToNbr";
    print "\n <tr><td class=\"dsphdr\">Bill-To Number</td> ";
    print "\n <td> ";
    require 'opersel_num.php';
    print "\n </td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"fromBillToNbr\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromBillToNbr&amp;fldDesc=fromBillToNbrNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromBillToNbrNameH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"toBillToNbr\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toBillToNbr&amp;fldDesc=toBillToNbrNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toBillToNbrNameH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupBillToNbr\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";

    $returnValue = Retrieve_Field("DHSHTO", $selectGroupBy, $selectCriteria);
    $operNbr = "operShipToNbr";
    print "\n <tr><td class=\"dsphdr\">Ship-To Number</td> ";
    print "\n <td> ";
    require 'opersel_num.php';
    print "\n </td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"fromShipToNbr\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromShipToNbr&amp;fldDesc=fromShipToNbrNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromShipToNbrNameH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"toShipToNbr\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toShipToNbr&amp;fldDesc=toShipToNbrNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toShipToNbrNameH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupShipToNbr\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";

    $returnValue = Retrieve_Field("CMCNA1U", $selectGroupBy, $selectCriteria);
    $operNbr = "operShipToName";
    print "\n <tr><td class=\"dsphdr\">Ship-To Name</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromShipToName\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"26\"><a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromShipToNbrH&amp;fldDesc=fromShipToName\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromShipToNbrH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toShipToName\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"26\"><a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toShipToNbrH&amp;fldDesc=toShipToName\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toShipToNbrH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupShipToName\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";

    $returnValue = Retrieve_Field("CMCNA2U", $selectGroupBy, $selectCriteria);
    $operNbr = "operAddress1";
    print "\n <tr><td class=\"dsphdr\">Address 1</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromAddress1\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"26\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toAddress1\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"26\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupAddress1\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";

    $returnValue = Retrieve_Field("CMCNA3U", $selectGroupBy, $selectCriteria);
    $operNbr = "operAddress2";
    print "\n <tr><td class=\"dsphdr\">Address 2</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromAddress2\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"26\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toAddress2\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"26\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupAddress2\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("CMCNA4U", $selectGroupBy, $selectCriteria);
    $operNbr = "operAddress3";
    print "\n <tr><td class=\"dsphdr\">Address 3</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromAddress3\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"19\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toAddress3\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"19\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupAddress3\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("CMCCTYU", $selectGroupBy, $selectCriteria);
    $operNbr = "operCity";
    print "\n <tr><td class=\"dsphdr\">City</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromCity\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"26\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toCity\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"26\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupCity\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("CMST", $selectGroupBy, $selectCriteria);
    $operNbr = "operState";
    print "\n <tr><td class=\"dsphdr\">State</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromState\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"2\" maxlength=\"2\"><a href=\"{$homeURL}{$phpPath}StateSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromState&amp;fldDesc=fromStateDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromStateDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toState\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"2\" maxlength=\"2\"><a href=\"{$homeURL}{$phpPath}StateSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toState&amp;fldDesc=toStateDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toStateDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupState\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("CMZIP", $selectGroupBy, $selectCriteria);
    $operNbr = "operZip";
    print "\n <tr><td class=\"dsphdr\">Zip Code</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromZip\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"13\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toZip\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"13\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupZip\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("DHDTLI", $selectGroupBy, $selectCriteria);
    $operNbr = "operInvDate";
    print "\n <tr><td class=\"dsphdr\">Invoice Date</td> ";
    print "\n <td> ";
    require 'opersel_num.php';
    print "\n </td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"fromInvDate\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"30\"> <a href=\"javascript:calWindow('fromInvDate');\">$calendarImage</a></td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"toInvDate\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"30\"> <a href=\"javascript:calWindow('toInvDate');\">$calendarImage</a></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupInvDate\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("@@LIYR", $selectGroupBy, $selectCriteria);
    print "\n <tr><td class=\"dsphdr\">Invoice Year</td> ";
    print "\n <td class=\"inputalph\"><input name=\"operInvYear\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromInvYear\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toInvYear\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupInvYear\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("@@LIQT", $selectGroupBy, $selectCriteria);
    print "\n <tr><td class=\"dsphdr\">Invoice Quarter</td> ";
    print "\n <td class=\"inputalph\"><input name=\"operInvQtr\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromInvQtr\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toInvQtr\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupInvQtr\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("@@LIMO", $selectGroupBy, $selectCriteria);
    print "\n <tr><td class=\"dsphdr\">Invoice Month</td> ";
    print "\n <td class=\"inputalph\"><input name=\"operInvMnth\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromInvMnth\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toInvMnth\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupInvMnth\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("@@LIWK", $selectGroupBy, $selectCriteria);
    print "\n <tr><td class=\"dsphdr\">Invoice Week</td> ";
    print "\n <td class=\"inputalph\"><input name=\"operInvWeek\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromInvWeek\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toInvWeek\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupInvWeek\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("DHPCLS", $selectGroupBy, $selectCriteria);
    $operNbr = "operProdClass";
    print "\n <tr><td class=\"dsphdr\">Product Class</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromProdClass\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"6\" maxlength=\"4\"><a href=\"{$homeURL}{$cGIPath}prodclasssearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=fromProdClass&amp;fldDesc=fromProdClassDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromProdClassDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toProdClass\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"6\" maxlength=\"4\"><a href=\"{$homeURL}{$cGIPath}prodclasssearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=toProdClass&amp;fldDesc=toProdClassDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toProdClassDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupProdClass\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("DHPGRP", $selectGroupBy, $selectCriteria);
    $operNbr = "operProdGroup";
    print "\n <tr><td class=\"dsphdr\">Product Group</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromProdGroup\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"6\" maxlength=\"4\"><a href=\"{$homeURL}{$cGIPath}productgroupsearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=fromProdGroup&amp;fldDesc=fromProdGroupDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromProdGroupDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toProdGroup\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"6\" maxlength=\"4\"><a href=\"{$homeURL}{$cGIPath}productgroupsearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=toProdGroup&amp;fldDesc=toProdGroupDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toProdGroupDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupProdGroup\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("IMITC", $selectGroupBy, $selectCriteria);
    $operNbr = "operInvType";
    print "\n <tr><td class=\"dsphdr\">Inventory Type</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromInvType\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"6\" maxlength=\"4\"><a href=\"{$homeURL}{$cGIPath}invtypesearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=fromInvType&amp;fldDesc=fromInvTypeDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromInvTypeDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toInvType\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"6\" maxlength=\"4\"><a href=\"{$homeURL}{$cGIPath}invtypesearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=toInvType&amp;fldDesc=toInvTypeDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toInvTypeDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupInvType\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("HHDOTS", $selectGroupBy, $selectCriteria);
    $operNbr = "operShipDate";
    print "\n <tr><td class=\"dsphdr\">Shipped Date</td> ";
    print "\n <td> ";
    require 'opersel_num.php';
    print "\n </td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"fromShipDate\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"30\"> <a href=\"javascript:calWindow('fromShipDate');\">$calendarImage</a></td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"toShipDate\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"30\"> <a href=\"javascript:calWindow('toShipDate');\">$calendarImage</a></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupShipDate\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("@@SDYR", $selectGroupBy, $selectCriteria);
    print "\n <tr><td class=\"dsphdr\">Shipped Year</td> ";
    print "\n <td class=\"inputalph\"><input name=\"operShipYear\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromShipYear\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toShipYear\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupShipYear\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("@@SDQT", $selectGroupBy, $selectCriteria);
    print "\n <tr><td class=\"dsphdr\">Shipped Quarter</td> ";
    print "\n <td class=\"inputalph\"><input name=\"operShipQtr\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromShipQtr\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toShipQtr\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupShipQtr\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("@@SDMO", $selectGroupBy, $selectCriteria);
    print "\n <tr><td class=\"dsphdr\">Shipped Month</td> ";
    print "\n <td class=\"inputalph\"><input name=\"operShipMnth\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromShipMnth\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toShipMnth\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupShipMnth\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("@@SDWK", $selectGroupBy, $selectCriteria);
    print "\n <tr><td class=\"dsphdr\">Shipped Week</td> ";
    print "\n <td class=\"inputalph\"><input name=\"operShipWeek\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromShipWeek\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toShipWeek\" type=\"hidden\" value=\"\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupShipWeek\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("DHSLPR", $selectGroupBy, $selectCriteria);
    $operNbr = "operSellPrice";
    print "\n <tr><td class=\"dsphdr\">Selling Price</td> ";
    print "\n <td> ";
    require 'opersel_num.php';
    print "\n </td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"fromSellPrice\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"toSellPrice\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupSellPrice\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("DHQSTC", $selectGroupBy, $selectCriteria);
    $operNbr = "operQtySold";
    print "\n <tr><td class=\"dsphdr\">Quantity Sold</td> ";
    print "\n <td> ";
    require 'opersel_num.php';
    print "\n </td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"fromQtySold\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"toQtySold\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupQtySold\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("HHSLSM", $selectGroupBy, $selectCriteria);
    $operNbr = "operSalesman";
    print "\n <tr><td class=\"dsphdr\">Salesman Number</td> ";
    print "\n <td> ";
    require 'opersel_num.php';
    print "\n </td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"fromSalesman\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"5\" maxlength=\"3\"> <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromSalesman&amp;fldDesc=fromSalesmanNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromSalesmanNameH\" type=\"hidden\" size=\"30\" maxlength=\"30\"></td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"toSalesman\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"5\" maxlength=\"3\"> <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toSalesman&amp;fldDesc=toSalesmanNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toSalesmanNameH\" type=\"hidden\" size=\"30\" maxlength=\"30\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupSalesman\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("CMCCLS", $selectGroupBy, $selectCriteria);
    $operNbr = "operCustClass";
    print "\n <tr><td class=\"dsphdr\">Customer Class</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromCustClass\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"5\" maxlength=\"2\"> <a href=\"{$homeURL}{$phpPath}CustomerClassSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromCustClass&amp;fldDesc=fromCustClassDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromCustClassDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toCustClass\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"5\" maxlength=\"2\"> <a href=\"{$homeURL}{$phpPath}CustomerClassSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toCustClass&amp;fldDesc=toCustClassDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toCustClassDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupCustClass\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("CMCRGN", $selectGroupBy, $selectCriteria);
    $operNbr = "operRegion";
    print "\n <tr><td class=\"dsphdr\">Region</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromRegion\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"5\" maxlength=\"5\"> <a href=\"{$homeURL}{$phpPath}RegionSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromRegion&amp;fldDesc=fromRegionDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromRegionDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toRegion\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"5\" maxlength=\"5\"> <a href=\"{$homeURL}{$phpPath}RegionSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toRegion&amp;fldDesc=toRegionDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toRegionDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupRegion\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("DHLOC#", $selectGroupBy, $selectCriteria);
    $operNbr = "operLoc";
    print "\n <tr><td class=\"dsphdr\">Location Number</td> ";
    print "\n <td> ";
    require 'opersel_num.php';
    print "\n </td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"fromLoc\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"5\" maxlength=\"3\"> <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromLoc&amp;fldDesc=fromLocNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromLocNameH\" type=\"hidden\" size=\"30\" maxlength=\"30\"></td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"toLoc\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"5\" maxlength=\"3\"> <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toLoc&amp;fldDesc=toLocNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toLocNameH\" type=\"hidden\" size=\"30\" maxlength=\"30\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupLoc\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("DHWH", $selectGroupBy, $selectCriteria);
    $operNbr = "operWhs";
    print "\n <tr><td class=\"dsphdr\">Warehouse Number</td> ";
    print "\n <td> ";
    require 'opersel_num.php';
    print "\n </td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"fromWhs\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"5\" maxlength=\"3\"> <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromWhs&amp;fldDesc=fromWhsNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromWhsNameH\" type=\"hidden\" size=\"30\" maxlength=\"30\"></td> ";
    print "\n <td class=\"inputnmbr\"><input name=\"toWhs\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"5\" maxlength=\"3\"> <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toWhs&amp;fldDesc=toWhsNameH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toWhsNameH\" type=\"hidden\" size=\"30\" maxlength=\"30\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupWhs\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("HHORTY", $selectGroupBy, $selectCriteria);
    $operNbr = "operOrderType";
    print "\n <tr><td class=\"dsphdr\">Order Type</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromOrderType\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"5\" maxlength=\"1\"> <a href=\"{$homeURL}{$cGIPath}ordertypesearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=fromOrderType&amp;fldDesc=fromOrderTypeDescH&amp;appID=OE\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromOrderTypeDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toOrderType\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"5\" maxlength=\"1\"> <a href=\"{$homeURL}{$cGIPath}ordertypesearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=toOrderType&amp;fldDesc=toOrderTypeDescH&amp;appID=OE\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toOrderTypeDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupOrderType\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("DHSVSV", $selectGroupBy, $selectCriteria);
    $operNbr = "operShipViaCode";
    print "\n <tr><td class=\"dsphdr\">Ship Via Code</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromShipViaCode\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"5\" maxlength=\"2\"> <a href=\"{$homeURL}{$phpPath}ShipViaSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromShipViaCode&amp;fldDesc=fromShipViaCodeDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromShipViaCodeDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toShipViaCode\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"5\" maxlength=\"2\"> <a href=\"{$homeURL}{$phpPath}ShipViaSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toShipViaCode&amp;fldDesc=toShipViaCodeDescH\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toShipViaCodeDescH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupShipViaCode\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    $returnValue = Retrieve_Field("DHSVDS", $selectGroupBy, $selectCriteria);
    $operNbr = "operShipViaDesc";
    print "\n <tr><td class=\"dsphdr\">Ship Via Description</td> ";
    print "\n <td> ";
    require 'opersel_alph.php';
    print "\n </td> ";
    print "\n <td class=\"inputalph\"><input name=\"fromShipViaDesc\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"> <a href=\"{$homeURL}{$phpPath}ShipViaSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=fromShipViaCodeH&amp;fldDesc=fromShipViaDesc\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"fromShipViaCodeH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputalph\"><input name=\"toShipViaDesc\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"> <a href=\"{$homeURL}{$phpPath}ShipViaSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=toShipViaCodeH&amp;fldDesc=toShipViaDesc\" onclick=\"$searchWinVar\"> $searchImage </a><input name=\"toShipViaCodeH\" type=\"hidden\"></td> ";
    print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupShipViaDesc\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
    print "\n </tr> ";
    if ($CRSCR1 != "") {
        $returnValue = Retrieve_Field("CMUDF1", $selectGroupBy, $selectCriteria);
        $operNbr = "operCustUDA1";
        print "\n <tr><td class=\"dsphdr\">$CRSCR1</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromCustUDA1\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toCustUDA1\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupCustUDA1\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CRSCR2 != "") {
        $returnValue = Retrieve_Field("CMUDF2", $selectGroupBy, $selectCriteria);
        $operNbr = "operCustUDA2";
        print "\n <tr><td class=\"dsphdr\">$CRSCR2</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromCustUDA2\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toCustUDA2\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupCustUDA2\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CRSCR3 != "") {
        $returnValue = Retrieve_Field("CMUDF3", $selectGroupBy, $selectCriteria);
        $operNbr = "operCustUDA3";
        print "\n <tr><td class=\"dsphdr\">$CRSCR3</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromCustUDA3\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toCustUDA3\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupCustUDA3\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CRSCR4 != "") {
        $returnValue = Retrieve_Field("CMUDF4", $selectGroupBy, $selectCriteria);
        $operNbr = "operCustUDA4";
        print "\n <tr><td class=\"dsphdr\">$CRSCR4</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromCustUDA4\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toCustUDA4\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupCustUDA4\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CRSCR5 != "") {
        $returnValue = Retrieve_Field("CMUDF5", $selectGroupBy, $selectCriteria);
        $operNbr = "operCustUDA5";
        print "\n <tr><td class=\"dsphdr\">$CRSCR5</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromCustUDA5\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toCustUDA5\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupCustUDA5\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIIAS1 != "") {
        $returnValue = Retrieve_Field("IMUDA1", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDA1";
        print "\n <tr><td class=\"dsphdr\">$CIIAS1</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromItemUDA1\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toItemUDA1\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDA1\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIIAS2 != "") {
        $returnValue = Retrieve_Field("IMUDA2", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDA2";
        print "\n <tr><td class=\"dsphdr\">$CIIAS2</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromItemUDA2\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toItemUDA2\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDA2\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIIAS3 != "") {
        $returnValue = Retrieve_Field("IMUDA3", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDA3";
        print "\n <tr><td class=\"dsphdr\">$CIIAS3</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromItemUDA3\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toItemUDA3\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDA3\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIIAS4 != "") {
        $returnValue = Retrieve_Field("IMUDA4", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDA4";
        print "\n <tr><td class=\"dsphdr\">$CIIAS4</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromItemUDA4\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toItemUDA4\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDA4\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIIAS5 != "") {
        $returnValue = Retrieve_Field("IMUDA5", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDA5";
        print "\n <tr><td class=\"dsphdr\">$CIIAS5</td> ";
        print "\n <td> ";
        require 'opersel_alph.php';
        print "\n </td> ";
        print "\n <td class=\"inputalph\"><input name=\"fromItemUDA5\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputalph\"><input name=\"toItemUDA5\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDA5\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIINS1 != "") {
        $returnValue = Retrieve_Field("IMUDN1", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDN1";
        print "\n <tr><td class=\"dsphdr\">$CIINS1</td> ";
        print "\n <td> ";
        require 'opersel_num.php';
        print "\n </td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"fromItemUDN1\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"toItemUDN1\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDN1\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIINS2 != "") {
        $returnValue = Retrieve_Field("IMUDN2", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDN2";
        print "\n <tr><td class=\"dsphdr\">$CIINS2</td> ";
        print "\n <td> ";
        require 'opersel_num.php';
        print "\n </td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"fromItemUDN2\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"toItemUDN2\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDN2\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIINS3 != "") {
        $returnValue = Retrieve_Field("IMUDN3", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDN3";
        print "\n <tr><td class=\"dsphdr\">$CIINS3</td> ";
        print "\n <td> ";
        require 'opersel_num.php';
        print "\n </td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"fromItemUDN3\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"toItemUDN3\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDN3\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIINS4 != "") {
        $returnValue = Retrieve_Field("IMUDN4", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDN4";
        print "\n <tr><td class=\"dsphdr\">$CIINS4</td> ";
        print "\n <td> ";
        require 'opersel_num.php';
        print "\n </td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"fromItemUDN4\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"toItemUDN4\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDN4\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    if ($CIINS5 != "") {
        $returnValue = Retrieve_Field("IMUDN5", $selectGroupBy, $selectCriteria);
        $operNbr = "operItemUDN5";
        print "\n <tr><td class=\"dsphdr\">$CIINS5</td> ";
        print "\n <td> ";
        require 'opersel_num.php';
        print "\n </td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"fromItemUDN5\" type=\"text\" value=\"{$returnValue['fromField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input name=\"toItemUDN5\" type=\"text\" value=\"{$returnValue['toField']}\" size=\"15\" maxlength=\"15\"></td> ";
        print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"groupItemUDN5\" value=\"{$returnValue['seqField']}\" size=\"2\" maxlength=\"2\"></td> ";
        print "\n </tr> ";
    }
    print "\n </table>";
    print "\n <table $contentTable> ";
    print "\n <tr> <td class=\"toolbar\">";
    if ($allUsers == "") {
        print "\n     <a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;fKey1=SLUSER&amp;fVal1=" . urlencode($userProfile) . "&amp;tblID=528\">$previousImage</a> ";
    } else {
        print "\n     <a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=529\">$previousImage</a> ";
    }
    print "\n     <a href=\"javascript:check(document.Chg)\">$selectAcceptImage</a> ";
    print "\n     <a onClick=\"return confirmReset()\">$selectResetImage</a> ";
    print "\n     <a onClick=\"return confirmClear()\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=SELECT&amp;selectUser=" . urlencode($selectUser) . "\">$selectClearImage</a> ";
    $medIcon = "Y";
    require_once 'HelpPage.php';
    print "\n </td> </tr> </table> ";
    print "\n </form>";
    print $hrTagAttr;
    require_once 'Copyright.php';
    print "\n </td> </tr> </table> ";
    require_once 'trailer.php';
    print "\n </body> </html> ";
}

if (($tag == "Edit_Data") && ($maintenanceCode == "D")) {
    $prog_OPT = pgmOptSecurity ($profileHandle,$dataBaseID,$programName);
    $sec_03 = $prog_OPT ['sec_03'];
    if ($sec_03 == "N") { 
        require_once 'ProgSecurityError.php';
    }
    $maintCode = "D";
    $confMessage = Update_User_Selection ($profileHandle, $dataBaseID, $maintCode, $selectUser, $selectName, $selectSequence, $selectDesc, $selectTotals, $selectGroupBy, $selectCriteria, $deleteUser);
    if ($allUsers == "") {
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;allUsers=&amp;fKey1=SLUSER&amp;fVal1=" . urlencode($userProfile) . "&amp;tblID=528&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
    } else {
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;allUsers=Y&amp;tblID=529&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
    }
}

if ($tag == "PROCESS") {
    $selectDesc   = $_POST['selectDesc'];
    $totalByAmt   = (isset($_POST['totalByAmt'])) ? $_POST['totalByAmt'] : "N";
    $totalByCnt   = (isset($_POST['totalByCnt'])) ? $_POST['totalByCnt'] : "N";
    $totalByQty   = (isset($_POST['totalByQty'])) ? $_POST['totalByQty'] : "N";
    $selectTotals = $totalByQty . $totalByAmt . $totalByCnt;

    $operItemNbr      = strtoupper($_POST['operItemNbr']);
    $fromItemNbr      = strtoupper($_POST['fromItemNbr']);
    $toItemNbr        = strtoupper($_POST['toItemNbr']);  
    $groupItemNbr     = trim($_POST['groupItemNbr']);

    $operItemDesc     = strtoupper($_POST['operItemDesc']);
    $fromItemDesc     = strtoupper($_POST['fromItemDesc']);
    $toItemDesc       = strtoupper($_POST['toItemDesc']);
    $groupItemDesc    = trim($_POST['groupItemDesc']);

    $operBillToNbr    = strtoupper($_POST['operBillToNbr']);
    $fromBillToNbr    = trim($_POST['fromBillToNbr']);
    $toBillToNbr      = trim($_POST['toBillToNbr']);
    $groupBillToNbr   = trim($_POST['groupBillToNbr']);
    
    $operShipToNbr    = strtoupper($_POST['operShipToNbr']);
    $fromShipToNbr    = trim($_POST['fromShipToNbr']);
    $toShipToNbr      = trim($_POST['toShipToNbr']);
    $groupShipToNbr   = trim($_POST['groupShipToNbr']);

    $operShipToName   = strtoupper($_POST['operShipToName']);
    $fromShipToName   = strtoupper($_POST['fromShipToName']);
    $toShipToName     = strtoupper($_POST['toShipToName']);
    $groupShipToName  = trim($_POST['groupShipToName']);
    
    $operAddress1     = strtoupper($_POST['operAddress1']);
    $fromAddress1     = strtoupper($_POST['fromAddress1']);
    $toAddress1       = strtoupper($_POST['toAddress1']);
    $groupAddress1    = trim($_POST['groupAddress1']);

    $operAddress2     = strtoupper($_POST['operAddress2']);
    $fromAddress2     = strtoupper($_POST['fromAddress2']);
    $toAddress2       = strtoupper($_POST['toAddress2']);
    $groupAddress2    = trim($_POST['groupAddress2']);

    $operAddress3     = strtoupper($_POST['operAddress3']);
    $fromAddress3     = strtoupper($_POST['fromAddress3']);
    $toAddress3       = strtoupper($_POST['toAddress3']);
    $groupAddress3    = trim($_POST['groupAddress3']);

    $operCity         = strtoupper($_POST['operCity']);
    $fromCity         = strtoupper($_POST['fromCity']);
    $toCity           = strtoupper($_POST['toCity']);
    $groupCity        = trim($_POST['groupCity']);
    
    $operState        = strtoupper($_POST['operState']);
    $fromState        = strtoupper($_POST['fromState']);
    $toState          = strtoupper($_POST['toState']);
    $groupState       = trim($_POST['groupState']);
    
    $operZip          = strtoupper($_POST['operZip']);
    $fromZip          = strtoupper($_POST['fromZip']);
    $toZip            = strtoupper($_POST['toZip']);
    $groupZip         = trim($_POST['groupZip']);
    
    $operInvDate      = strtoupper($_POST['operInvDate']);
    $fromInvDate      = strtoupper($_POST['fromInvDate']);
    $toInvDate        = strtoupper($_POST['toInvDate']);
    $groupInvDate     = trim($_POST['groupInvDate']);
    
    $groupInvYear     = trim($_POST['groupInvYear']);
    $groupInvQtr      = trim($_POST['groupInvQtr']);
    $groupInvMnth     = trim($_POST['groupInvMnth']);
    $groupInvWeek     = trim($_POST['groupInvWeek']);
    
    $operProdClass    = strtoupper($_POST['operProdClass']);
    $fromProdClass    = strtoupper($_POST['fromProdClass']);
    $toProdClass      = strtoupper($_POST['toProdClass']);
    $groupProdClass   = trim($_POST['groupProdClass']);
    
    $operProdGroup    = strtoupper($_POST['operProdGroup']);
    $fromProdGroup    = strtoupper($_POST['fromProdGroup']);
    $toProdGroup      = strtoupper($_POST['toProdGroup']);
    $groupProdGroup   = trim($_POST['groupProdGroup']);
    
    $operInvType      = strtoupper($_POST['operInvType']);
    $fromInvType      = strtoupper($_POST['fromInvType']);
    $toInvType        = strtoupper($_POST['toInvType']);
    $groupInvType     = trim($_POST['groupInvType']);
    
    $operShipDate     = strtoupper($_POST['operShipDate']);
    $fromShipDate      = strtoupper($_POST['fromShipDate']);
    $toShipDate        = strtoupper($_POST['toShipDate']);
    $groupShipDate    = trim($_POST['groupShipDate']);
    
    $groupShipYear    = trim($_POST['groupShipYear']);
    $groupShipQtr     = trim($_POST['groupShipQtr']);
    $groupShipMnth    = trim($_POST['groupShipMnth']);
    $groupShipWeek    = trim($_POST['groupShipWeek']);
    
    $operSellPrice    = strtoupper($_POST['operSellPrice']);
    $fromSellPrice    = trim($_POST['fromSellPrice']);
    $toSellPrice      = trim($_POST['toSellPrice']);
    $groupSellPrice   = trim($_POST['groupSellPrice']);
    
    $operQtySold      = strtoupper($_POST['operQtySold']);
    $fromQtySold      = trim($_POST['fromQtySold']);
    $toQtySold        = trim($_POST['toQtySold']);
    $groupQtySold     = trim($_POST['groupQtySold']);
    
    $operSalesman     = strtoupper($_POST['operSalesman']);
    $fromSalesman     = trim($_POST['fromSalesman']);
    $toSalesman       = trim($_POST['toSalesman']);
    $groupSalesman    = trim($_POST['groupSalesman']);
    
    $operCustClass    = strtoupper($_POST['operCustClass']);
    $fromCustClass    = strtoupper($_POST['fromCustClass']);
    $toCustClass      = strtoupper($_POST['toCustClass']);
    $groupCustClass   = trim($_POST['groupCustClass']);
    
    $operRegion       = strtoupper($_POST['operRegion']);
    $fromRegion       = strtoupper($_POST['fromRegion']);
    $toRegion         = strtoupper($_POST['toRegion']);
    $groupRegion      = trim($_POST['groupRegion']);
    
    $operLoc          = strtoupper($_POST['operLoc']);
    $fromLoc          = trim($_POST['fromLoc']);
    $toLoc            = trim($_POST['toLoc']);
    $groupLoc         = trim($_POST['groupLoc']);
    
    $operWhs          = strtoupper($_POST['operWhs']);
    $fromWhs          = trim($_POST['fromWhs']);
    $toWhs            = trim($_POST['toWhs']);
    $groupWhs         = trim($_POST['groupWhs']);
    
    $operShipViaCode  = strtoupper($_POST['operShipViaCode']);
    $fromShipViaCode  = strtoupper($_POST['fromShipViaCode']);
    $toShipViaCode    = strtoupper($_POST['toShipViaCode']);
    $groupShipViaCode = trim($_POST['groupShipViaCode']);
    
    $operShipViaDesc  = strtoupper($_POST['operShipViaDesc']);
    $fromShipViaDesc  = strtoupper($_POST['fromShipViaDesc']);
    $toShipViaDesc    = strtoupper($_POST['toShipViaDesc']);
    $groupShipViaDesc = trim($_POST['groupShipViaDesc']);
    
    $operOrderType    = strtoupper($_POST['operOrderType']);
    $fromOrderType    = strtoupper($_POST['fromOrderType']);
    $toOrderType      = strtoupper($_POST['toOrderType']);
    $groupOrderType   = trim($_POST['groupOrderType']);
    
    $operCustUDA1     = strtoupper($_POST['operCustUDA1']);
    $fromCustUDA1     = strtoupper($_POST['fromCustUDA1']);
    $toCustUDA1       = strtoupper($_POST['toCustUDA1']);
    $groupCustUDA1    = trim($_POST['groupCustUDA1']);
    
    $operCustUDA2     = strtoupper($_POST['operCustUDA2']);
    $fromCustUDA2     = strtoupper($_POST['fromCustUDA2']);
    $toCustUDA2       = strtoupper($_POST['toCustUDA2']);
    $groupCustUDA2    = trim($_POST['groupCustUDA2']);
    
    $operCustUDA3     = strtoupper($_POST['operCustUDA3']);
    $fromCustUDA3     = strtoupper($_POST['fromCustUDA3']);
    $toCustUDA3       = strtoupper($_POST['toCustUDA3']);
    $groupCustUDA3    = trim($_POST['groupCustUDA3']);
    
    $operCustUDA4     = strtoupper($_POST['operCustUDA4']);
    $fromCustUDA4     = strtoupper($_POST['fromCustUDA4']);
    $toCustUDA4       = strtoupper($_POST['toCustUDA4']);
    $groupCustUDA4    = trim($_POST['groupCustUDA4']);
    
    $operCustUDA5     = strtoupper($_POST['operCustUDA5']);
    $fromCustUDA5     = strtoupper($_POST['fromCustUDA5']);
    $toCustUDA5       = strtoupper($_POST['toCustUDA5']);
    $groupCustUDA5    = trim($_POST['groupCustUDA5']);
    
    $operItemUDA1     = strtoupper($_POST['operItemUDA1']);
    $fromItemUDA1     = strtoupper($_POST['fromItemUDA1']);
    $toItemUDA1       = strtoupper($_POST['toItemUDA1']);
    $groupItemUDA1    = trim($_POST['groupItemUDA1']);
    
    $operItemUDA2     = strtoupper($_POST['operItemUDA2']);
    $fromItemUDA2     = strtoupper($_POST['fromItemUDA2']);
    $toItemUDA2       = strtoupper($_POST['toItemUDA2']);
    $groupItemUDA2    = trim($_POST['groupItemUDA2']);
    
    $operItemUDA3     = strtoupper($_POST['operItemUDA3']);
    $fromItemUDA3     = strtoupper($_POST['fromItemUDA3']);
    $toItemUDA3       = strtoupper($_POST['toItemUDA3']);
    $groupItemUDA3    = trim($_POST['groupItemUDA3']);
    
    $operItemUDA4     = strtoupper($_POST['operItemUDA4']);
    $fromItemUDA4     = strtoupper($_POST['fromItemUDA4']);
    $toItemUDA4       = strtoupper($_POST['toItemUDA4']);
    $groupItemUDA4    = trim($_POST['groupItemUDA4']);
    
    $operItemUDA5     = strtoupper($_POST['operItemUDA5']);
    $fromItemUDA5     = strtoupper($_POST['fromItemUDA5']);
    $toItemUDA5       = strtoupper($_POST['toItemUDA5']);
    $groupItemUDA5    = trim($_POST['groupItemUDA5']);
    
    $operItemUDN1     = strtoupper($_POST['operItemUDN1']);
    $fromItemUDN1     = trim($_POST['fromItemUDN1']);
    $toItemUDN1       = trim($_POST['toItemUDN1']);
    $groupItemUDN1    = trim($_POST['groupItemUDN1']);
    
    $operItemUDN2     = strtoupper($_POST['operItemUDN2']);
    $fromItemUDN2     = trim($_POST['fromItemUDN2']);
    $toItemUDN2       = trim($_POST['toItemUDN2']);
    $groupItemUDN2    = trim($_POST['groupItemUDN2']);
    
    $operItemUDN3     = strtoupper($_POST['operItemUDN3']);
    $fromItemUDN3     = trim($_POST['fromItemUDN3']);
    $toItemUDN3       = trim($_POST['toItemUDN3']);
    $groupItemUDN3    = trim($_POST['groupItemUDN3']);
    
    $operItemUDN4     = strtoupper($_POST['operItemUDN4']);
    $fromItemUDN4     = trim($_POST['fromItemUDN4']);
    $toItemUDN4       = trim($_POST['toItemUDN4']);
    $groupItemUDN4    = trim($_POST['groupItemUDN4']);
    
    $operItemUDN5     = strtoupper($_POST['operItemUDN5']);
    $fromItemUDN5     = trim($_POST['fromItemUDN5']);
    $toItemUDN5       = trim($_POST['toItemUDN5']);
    $groupItemUDN5    = trim($_POST['groupItemUDN5']);
 
    $buildSelect['selectGroupBy'] = " ";
    $x = 1;
    while ($x <= 500) {
        $buildSelect['selectGroupBy'] .= " ";
        $x++;
    }
    $buildSelect['selectCriteria'] = "";
    $buildSelect = Build_Select("DHITEM ",$operItemNbr,$fromItemNbr,$toItemNbr,$groupItemNbr,$buildSelect);
    $buildSelect = Build_Select("DHIMDS ",$operItemDesc,$fromItemDesc,$toItemDesc,$groupItemDesc,$buildSelect);
    $buildSelect = Build_Select("DHBLTO ",$operBillToNbr,$fromBillToNbr,$toBillToNbr,$groupBillToNbr,$buildSelect);
    $buildSelect = Build_Select("DHSHTO ",$operShipToNbr,$fromShipToNbr,$toShipToNbr,$groupShipToNbr,$buildSelect);
    $buildSelect = Build_Select("CMCNA1U",$operShipToName,$fromShipToName,$toShipToName,$groupShipToName,$buildSelect);
    $buildSelect = Build_Select("CMCNA2U",$operAddress1,$fromAddress1,$toAddress1,$groupAddress1,$buildSelect);
    $buildSelect = Build_Select("CMCNA3U",$operAddress2,$fromAddress2,$toAddress2,$groupAddress2,$buildSelect);
    $buildSelect = Build_Select("CMCNA4U",$operAddress3,$fromAddress3,$toAddress3,$groupAddress3,$buildSelect);
    $buildSelect = Build_Select("CMCCTYU",$operCity,$fromCity,$toCity,$groupCity,$buildSelect);
    $buildSelect = Build_Select("CMST   ",$operState,$fromState,$toState,$groupState,$buildSelect);
    $buildSelect = Build_Select("CMZIP  ",$operZip,$fromZip,$toZip,$groupZip,$buildSelect);
    $buildSelect = Build_Select("DHDTLI ",$operInvDate,$fromInvDate,$toInvDate,$groupInvDate,$buildSelect);
    $buildSelect = Build_Select("@@LIYR ","","","",$groupInvYear,$buildSelect);
    $buildSelect = Build_Select("@@LIQT ","","","",$groupInvQtr,$buildSelect);
    $buildSelect = Build_Select("@@LIMO ","","","",$groupInvMnth,$buildSelect);
    $buildSelect = Build_Select("@@LIWK ","","","",$groupInvWeek,$buildSelect);
    $buildSelect = Build_Select("DHPCLS ",$operProdClass,$fromProdClass,$toProdClass,$groupProdClass,$buildSelect);
    $buildSelect = Build_Select("DHPGRP ",$operProdGroup,$fromProdGroup,$toProdGroup,$groupProdGroup,$buildSelect);
    $buildSelect = Build_Select("IMITC  ",$operInvType,$fromInvType,$toInvType,$groupInvType,$buildSelect);
    $buildSelect = Build_Select("HHDOTS ",$operShipDate,$fromShipDate,$toShipDate,$groupShipDate,$buildSelect);
    $buildSelect = Build_Select("@@SDYR ","","","",$groupShipYear,$buildSelect);
    $buildSelect = Build_Select("@@SDQT ","","","",$groupShipQtr,$buildSelect);
    $buildSelect = Build_Select("@@SDMO ","","","",$groupShipMnth,$buildSelect);
    $buildSelect = Build_Select("@@SDWK ","","","",$groupShipWeek,$buildSelect);
    $buildSelect = Build_Select("DHSLPR ",$operSellPrice,$fromSellPrice,$toSellPrice,$groupSellPrice,$buildSelect);
    $buildSelect = Build_Select("DHQSTC ",$operQtySold,$fromQtySold,$toQtySold,$groupQtySold,$buildSelect);
    $buildSelect = Build_Select("HHSLSM ",$operSalesman,$fromSalesman,$toSalesman,$groupSalesman,$buildSelect);
    $buildSelect = Build_Select("CMCCLS ",$operCustClass,$fromCustClass,$toCustClass,$groupCustClass,$buildSelect);
    $buildSelect = Build_Select("CMCRGN ",$operRegion,$fromRegion,$toRegion,$groupRegion,$buildSelect);
    $buildSelect = Build_Select("DHLOC# ",$operLoc,$fromLoc,$toLoc,$groupLoc,$buildSelect);
    $buildSelect = Build_Select("DHWH   ",$operWhs,$fromWhs,$toWhs,$groupWhs,$buildSelect);
    $buildSelect = Build_Select("DHSVSV ",$operShipViaCode,$fromShipViaCode,$toShipViaCode,$groupShipViaCode,$buildSelect);
    $buildSelect = Build_Select("DHSVDS ",$operShipViaDesc,$fromShipViaDesc,$toShipViaDesc,$groupShipViaDesc,$buildSelect);
    $buildSelect = Build_Select("HHORTY ",$operOrderType,$fromOrderType,$toOrderType,$groupOrderType,$buildSelect);
    $buildSelect = Build_Select("CMUDF1 ",$operCustUDA1,$fromCustUDA1,$toCustUDA1,$groupCustUDA1,$buildSelect);
    $buildSelect = Build_Select("CMUDF2 ",$operCustUDA2,$fromCustUDA2,$toCustUDA2,$groupCustUDA2,$buildSelect);
    $buildSelect = Build_Select("CMUDF3 ",$operCustUDA3,$fromCustUDA3,$toCustUDA3,$groupCustUDA3,$buildSelect);
    $buildSelect = Build_Select("CMUDF4 ",$operCustUDA4,$fromCustUDA4,$toCustUDA4,$groupCustUDA4,$buildSelect);
    $buildSelect = Build_Select("CMUDF5 ",$operCustUDA5,$fromCustUDA5,$toCustUDA5,$groupCustUDA5,$buildSelect);
    $buildSelect = Build_Select("IMUDA1 ",$operItemUDA1,$fromItemUDA1,$toItemUDA1,$groupItemUDA1,$buildSelect);
    $buildSelect = Build_Select("IMUDA2 ",$operItemUDA2,$fromItemUDA2,$toItemUDA2,$groupItemUDA2,$buildSelect);
    $buildSelect = Build_Select("IMUDA3 ",$operItemUDA3,$fromItemUDA3,$toItemUDA3,$groupItemUDA3,$buildSelect);
    $buildSelect = Build_Select("IMUDA4 ",$operItemUDA4,$fromItemUDA4,$toItemUDA4,$groupItemUDA4,$buildSelect);
    $buildSelect = Build_Select("IMUDA5 ",$operItemUDA5,$fromItemUDA5,$toItemUDA5,$groupItemUDA5,$buildSelect);
    $buildSelect = Build_Select("IMUDN1 ",$operItemUDN1,$fromItemUDN1,$toItemUDN1,$groupItemUDN1,$buildSelect);
    $buildSelect = Build_Select("IMUDN2 ",$operItemUDN2,$fromItemUDN2,$toItemUDN2,$groupItemUDN2,$buildSelect);
    $buildSelect = Build_Select("IMUDN3 ",$operItemUDN3,$fromItemUDN3,$toItemUDN3,$groupItemUDN3,$buildSelect);
    $buildSelect = Build_Select("IMUDN4 ",$operItemUDN4,$fromItemUDN4,$toItemUDN4,$groupItemUDN4,$buildSelect);
    $buildSelect = Build_Select("IMUDN5 ",$operItemUDN5,$fromItemUDN5,$toItemUDN5,$groupItemUDN5,$buildSelect);
    $selectCriteria = $buildSelect['selectCriteria'];
    $selectGroupBy  = $buildSelect['selectGroupBy']; 
    $selectCriteria .= "@@END";

    $maintCode = "A";   
    $returnValue = Update_User_Selection($profileHandle, $dataBaseID, $maintCode, $userProfile, $selectName, $selectSequence, $selectDesc, $selectTotals, $selectGroupBy, $selectCriteria,$deleteUser);
    $selectName     = $returnValue['selectName'];
    $selectSequence = $returnValue['selectSequence'];
    $selectDesc     = $returnValue['selectDesc'] ;
    $selectTotals   = $returnValue['selectTotals'];
    $selectGroupBy  = $returnValue['selectGroupBy'];
    $selectCriteria = $returnValue['selectCriteria'];

    if ($selectGroupBy != "") {
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}SalesInqGroupBy.php{$scriptVarBase}&amp;tag=PROCESS&amp;downloadProfileHandle=" . urlencode($profileHandle) . "&amp;selectCriteria=" . urlencode($selectCriteria) . "&amp;selectDesc=" . urlencode($selectDesc) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;selectSequence=" . urlencode($selectSequence) . "&amp;selectTotals=" . urlencode($selectTotals) . "&amp;userSQL=" . urlencode($userSQL) . "&amp;grpFldLvl=1&amp;timeStamp=" . urlencode(date("Y-m-d-H.i.s.u") ) . "\">";
    } else {
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}SalesInqDisplay.php{$scriptVarBase}&amp;tag=PROCESS&amp;downloadProfileHandle=" . urlencode($profileHandle) . "&amp;selectCriteria=" . urlencode($selectCriteria) . "&amp;selectDesc=" . urlencode($selectDesc) . "&amp;selectGroupBy=" . urlencode($selectGroupBy) . "&amp;selectSequence=". urlencode($selectSequence) . "&amp;selectTotals=" . urlencode($selectTotals) . "&amp;userSQL=" . urlencode($userSQL) . "&amp;timeStamp=" . urlencode(date("Y-m-d-H.i.s.u") ) . "\">";
    }
}
?>