<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';
require_once 'WildCard.php';

$scriptName       = "MfgOrderUpdate.php";
$scriptUpdateName = "MfgOrderUpdateMaintain.php";
$scriptVarBase    = "{$genericVarBase}";
$altScriptVarBase = "{$altVarBase}";
$baseURL          = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$updateURL        = "{$homeURL}{$phpPath}{$scriptUpdateName}{$scriptVarBase}";
$currentURL       = "{$baseURL}&amp;tag=INPUT&amp;startRow=" . urlencode($startRow);
$nextPrevVar      = "{$scriptVarBase}";
$filterURL        = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$programName      = "HSI215";
$dspMaxRows       = $dspMaxRowsDft;
$prtMaxRows       = $prtMaxRowsDft;
$dftOrderBy       = [["OHPLT", "A", "Plant Number"],["OHORD", "A", "Manufacturing Order"]];
$pageSelectList   = "N";
$advanceSearch    = "Y";
$today            = date('mdy');
$page_title       = 'Manufacturing Order Update';

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
    require_once 'ProgSecurityError.php';
    exit;
}
$stmtSQL = " Select count(*) as CNT from HDMOTCQ ";
$cntResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$chkCount = db2_fetch_assoc($cntResult);
$rowsInProcess = $chkCount['CNT'];

$lvThresh = (($laborThreshold == "") ? "OHSTC='T' or OHSTC='A'" : "abs(LWPCT)<CAST($laborThreshold as INT)");
$mvThresh = (($materialThreshold == "") ? "OHSTC='T' or OHSTC='A'" : "abs(MWPCT)<CAST($materialThreshold as INT)");
$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("View:", "Final Tagged", $viewCheckBoxURL, "1", "0", "OHSTC='T' ","","1"),
    array("", "Material Under Threshold", $viewCheckBoxURL, "2", "0", $mvThresh,"","2"),
    array("", "Labor Under Threshold", $viewCheckBoxURL, "3", "0", $lvThresh,"","3"));
require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
    require_once ($docType);
    print "\n <html> <head>";
    $formName = "Search";
    require_once ($headInclude);
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'EditFromToAllJava.js';
    require_once 'Menu.js';
    require_once 'NumEdit.php';
    require_once 'CheckEnterSearch.php';
    require_once 'NoFormValidate.php';
    print "\n </script>";
    $scriptType = "L";    // L=List, S=Search, I=Inquiry
    $pageID = "";
    require_once 'AdvSearchTop.php';
    if ($forPlant == 0) {
        Build_AdvSrch_Entry("Plant","srchPlant","","operPlant","opersel_num_short","N","3","3");
    }
    Build_AdvSrch_Entry("Mfg Order","srchOrder","","operOrder","opersel_alph_short","A","9","9");
    Build_AdvSrch_Entry("Item","srchItem","","operItem","opersel_alph_short","A","15","15");
    Build_AdvSrch_Entry("Reference","srchReference","","operReference","opersel_alph_short","A","20","20");
    $focusField = "MfgOrder";
    require_once 'AdvSearchBottom.php';
}
$maxRows = $dspMaxRows;
if ($tag == "ORDERBY") {
    if     ($sequence == "Plant")             {$orby = array(array("OHPLT", "A", "Plant Number"),     array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Buyer")             {$orby = array(array("OHBAC", "A", "Buyer"),            array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "MfgOrder")          {$orby = array(array("OHORD", "A", "MfgOrder"),         array("OHPLT", "A", "Plant Number"));}
    elseif ($sequence == "Item Number")       {$orby = array(array("OHPN",  "A", "Item Number"),      array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Status")            {$orby = array(array("OHSTC", "A", "Order Status"),     array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Material Variance") {$orby = array(array("MWPCT", "A", "Material Variance"),array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Labor Variance")    {$orby = array(array("LWPCT", "A", "Labor Variance"),   array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Reference")         {$orby = array(array("OHRORD","A", "Reference"),        array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Order Qty")         {$orby = array(array("OHCQTY","A", "Order Qty"),        array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Qty Received")      {$orby = array(array("OHQTYR","A", "Qty Received"),     array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Order Date")        {$orby = array(array("OHORDT","A", "Order Date"),       array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Due Date")          {$orby = array(array("OHCDDT","A", "Due Date"),         array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "Last Activity")     {$orby = array(array("OHLADT","A", "Last Activity"),    array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    elseif ($sequence == "User Id")           {$orby = array(array("OHTSUS","A", "User Id"),          array("OHPLT", "A", "Plant Number"),array("OHORD", "A", "MfgOrder"));}
    require_once 'OrderByUpdate.php';
}
if ($tag == "QSEARCH") {require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
    $andOr = $_POST['andOr'];
    require_once 'WildCardClear.php';
    $returnValue=Range_WildCard("OHSTC",  "Status",            $_POST['frStatus'], $_POST['toStatus'], "U", $_POST['operStatus'],    "A");
    $returnValue=Range_WildCard("OHPLT",  "Plant",             $_POST['frPlant'],  $_POST['toPlant'],  "",  $_POST['operPlant'],     "N");
    $returnValue=Range_WildCard("OHBAC",  "Buyer/Analyst",     $_POST['frBAC'],    $_POST['toBAC'],    "",  $_POST['operBAC'],       "N");
    $returnValue=Range_WildCard("OHORD",  "Mfg Order",         $_POST['frOrder'],  $_POST['toOrder'],  "U", $_POST['operOrder'],     "A");
    $returnValue=Range_WildCard("OHPN",   "Item Number",       $_POST['frItem'],   $_POST['toItem'],   "U", $_POST['operItem'],      "A");
	$returnValue=Range_WildCard("OHSTC",  "Status",            $_POST['frStatus'], $_POST['toStatus'], "U", $_POST['operStatus'],    "A");
    $returnValue=Range_WildCard("MWPCT",  "Material Variance", $_POST['frMVar'],   $_POST['toMVar'],   "",  $_POST['operMVar'],      "N");
    $returnValue=Range_WildCard("LWPCT",  "Labor Variance",    $_POST['frLVar'],   $_POST['toLVar'],   "",  $_POST['operLVar'],      "N");
	$returnValue=Range_WildCard("OHRORD", "Reference",         $_POST['frRef'],    $_POST['toRef'],    "U", $_POST['operReference'], "A");
    $returnValue=Range_WildCard("OHCQTY", "Order Qty",         $_POST['frOQty'],   $_POST['toOQty'],   "",  $_POST['operOQty'],      "N");
    $returnValue=Range_WildCard("OHQTYR", "Qty Received",      $_POST['frRQty'],   $_POST['toRQty'],   "",  $_POST['operRQty'],      "N");
    $returnValue=Range_WildCard("OHORDT", "Order Date",        $_POST['frODate'],  $_POST['toODate'],  "U", $_POST['operODate'],     "D");
    $returnValue=Range_WildCard("OHCDDT", "Due Date",          $_POST['frDDate'],  $_POST['toDDate'],  "U", $_POST['operDDate'],     "D");
    $returnValue=Range_WildCard("OHLADT", "Last Activity",     $_POST['frADate'],  $_POST['toADate'],  "",  $_POST['operADate'],     "D");
    $returnValue=Range_WildCard("OHTSUS", "User Id",           $_POST['frUser'],   $_POST['toUser'],   "",  $_POST['operUser'],      "A");
    require_once 'WildCardUpdate.php';
}
if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}
require_once($docType);
print "\n <html> <head>";
require_once($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'Menu.js';
require_once 'CheckSel.js';
require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";
require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';        // Harrisdata Header
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "MFGORDERUPDATE";
require_once 'MenuDisplay.php';   // Sidebar Menu
print "\n <td class=\"content\">";
print "\n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n function clearWarnings(w) {";
print "\n     clearWarn = 'MWPCT' + w;";
print "\n     setWarnDisplay(clearWarn, 'transparent', 'Material Variance');";
print "\n     clearWarn = 'LWPCT' + w;";
print "\n     setWarnDisplay(clearWarn, 'transparent', 'Labor Variance');";
print "\n }";
print "\n function deselectAll(chgForm) {";
print "\n     document.getElementById('acceptButton').style.visibility = 'hidden';";
print "\n     for (var fx = 0; fx < chgForm.elements.length; fx++) {";
print "\n         if (chgForm.elements[fx].type == \"checkbox\") {";
print "\n             chgForm.elements[fx].checked = false;";
print "\n         }";
print "\n     }";
print "\n }";
print "\n function displayAccept(){";
print "\n     boxesChecked = document.querySelectorAll('input[name=\"selFT\"]:checked, [name=\"selCL\"]:checked').length;";
print "\n     if (boxesChecked==0){";
print "\n         document.getElementById('acceptButton').style.visibility = 'hidden';";
print "\n     } else {";
print "\n         document.getElementById('acceptButton').style.visibility = 'visible';";
print "\n     }";
print "\n }";
print "\n function Validate_Data(chgForm){";
print "\n     var dspRows = (chgForm.displayedRows.value ? chgForm.displayedRows.value : 0);";
print "\n     var allFT  = document.getElementsByName(\"selFT\");";
print "\n     var allCL  = document.getElementsByName(\"selCL\");";
print "\n     var edtVar = \"\";";
print "\n     chgForm.hasErrors.value = 'false'; ";
print "\n     for (var fx = 0; fx < dspRows; fx++){";
print "\n         var selUPD  = 'UPD' + fx;";
print "\n         if (document.getElementById(selUPD).value != \"\") { ";
print "\n             document.getElementById(selUPD).value=\"\";";
print "\n             setDisplay(fx, 'transparent', '');";
print "\n             if ((!allFT[fx].checked) && (!allCL[fx].checked)){ ";
print "\n                 var selWRN  = 'WRN' + fx;";
print "\n                 document.getElementById(selWRN).value = \"\";";
print "\n             }";
print "\n             clearWarnings(fx); ";
print "\n         }";
print "\n         if ((allFT[fx].checked) || (allCL[fx].checked)){ ";
print "\n             document.getElementById(selUPD).value = ((allFT[fx].checked) && (allCL[fx].checked)) ? 'B' : (allFT[fx].checked) ? 'T' : 'C';";
print "\n             var selPLT  = 'PLT' + fx;";
print "\n             var selORD  = 'ORD' + fx;";
print "\n             var uptp = document.getElementById(selUPD).value; ";
print "\n             var plnt = document.getElementById(selPLT).value; ";
print "\n             var mord = document.getElementById(selORD).value; ";
print "\n             edtVar = edtVar + \"@@uptp\" + uptp.replace(/\\s/g,\"\") + \"}{@@srow\" + fx + \"}{@@dorsS}{@@date" . $today . "}{@@plt@\" + plnt + \"}{@@mord\" + mord.replace(/\\s/g,\"\") + \"}{@@dbid" . $dataBaseID . "}{|\"; ";
print "\n         }";
print "\n     }";
print "\n     if (edtVar != \"\") { ";
print "\n         sendEditRequest(chgForm, edtVar); ";
print "\n     }";
print "\n }";
print "\n function sendEditRequest(chgForm, edtVar) {";
print "\n     var editTag = \"EDIT_DATA\"; ";
print "\n     var url = \"" . $homeURL . $phpPath . "MfgOrderUpdateMaintain.php" . $scriptVarBase . "&tag=\" + escape(editTag) + \"&edtVar=\" + escape(edtVar) + \"&dummy=\" + new Date().getTime(); ";
print "\n     request = new getXMLHTTPRequest(); ";
print "\n     request.open(\"GET\", url, false); ";
print "\n     request.send(); ";
print "\n     if (request.status == 200) {";
print "\n         parseResponse(chgForm, request.responseText);";
print "\n     } else {";
print "\n         alert(request.status + ' -- Error Processing Request');";
print "\n     }";
print "\n }";
print "\n function parseResponse(chgForm, responses) {";
print "\n     var response  = responses.split(\"|E|\");";
print "\n     var errorRows = response[1].split(\"|\");";
print "\n     if (errorRows != \"\") {";
print "\n         var allErrors = response[2].split(\"|\");";
print "\n         var allWarns  = response[3].split(\"|\");";
print "\n         for (var fx = 0; fx < errorRows.length-1; fx++) {";
print "\n             var errRow = parseInt(errorRows[fx]); ";
print "\n             var errMsg  = \"\";";
print "\n             var rowErrors = allErrors[fx].split(\"}\");";
print "\n             if (rowErrors != \"\"){";
print "\n                 chgForm.hasErrors.value = 'true';";
print "\n                 for (var i = 0; i < rowErrors.length; i++) {";
print "\n                     errMsg = errMsg.trimStart() + rowErrors[i].substring(7) + \"\\r\";";
print "\n                 }";
print "\n                 setDisplay(errRow, \"$errorBackground\", errMsg);";
print "\n             } else { ";
print "\n                 var selWRN  = 'WRN' + errRow;";
print "\n                 var mTitle = \"\";";
print "\n                 var lTitle = \"\";";
print "\n                 var dTitle  = \"\";";
print "\n                 var rowWarnings = allWarns[fx].split(\"}\");";
print "\n                 if (rowWarnings != \"\"){";
print "\n                     for (var i = 1; i < rowWarnings.length-1; i++) {";
print "\n                         var wrnFld = \"\";";
print "\n                         wrnFld = rowWarnings[i].substring(3,7);";
print "\n                         switch(wrnFld) { ";
print "\n                             case 'mpct': ";
print "\n                                 mTitle = mTitle.trimStart() + rowWarnings[i].substring(7) + \"\\r\";";
print "\n                                 break;";
print "\n                              case 'lpct': ";
print "\n                                  lTitle = lTitle.trimStart() + rowWarnings[i].substring(7) + \"\\r\";";
print "\n                                  break;";
print "\n                              default: ";
print "\n                                  dTitle = dTitle.trimStart() + rowWarnings[i].substring(7) + \"\\r\";";
print "\n                         }";
print "\n                     }";
print "\n                     if (mTitle != \"\") {";
print "\n                         setWarn = 'MWPCT' + errRow;";
print "\n                         setWarnDisplay(setWarn, 'yellow', mTitle);";
print "\n                     }";
print "\n                     if (lTitle != \"\") {";
print "\n                         setWarn = 'LWPCT' + errRow;";
print "\n                         setWarnDisplay(setWarn, 'yellow', lTitle);";
print "\n                     }";
print "\n                     if (dTitle != \"\") {";
print "\n                         setDisplay(errRow, 'yellow', dTitle);";
print "\n                     }";
print "\n                     if (document.getElementById(selWRN).value == \"\") {";
print "\n                         chgForm.hasErrors.value = 'true';";
print "\n                     }";
print "\n                     document.getElementById(selWRN).value = \"Y\";";
print "\n                 } else {";
print "\n                     document.getElementById(selWRN).value = \"\";";
print "\n                     clearWarnings(errRow);";
print "\n                 }";
print "\n             }";
print "\n         }";
print "\n     }";
print "\n     if (chgForm.hasErrors.value === 'false'){";
print "\n         chgForm.submit();";
print "\n     }";
print "\n }";
print "\n function selectAll(chgForm) {";
print "\n     document.getElementById('acceptButton').style.visibility = 'visible';";
print "\n     for (var fx = 0; fx < chgForm.elements.length; fx++) {";
print "\n         if (chgForm.elements[fx].type == \"checkbox\") {";
print "\n             chgForm.elements[fx].checked = true;";
print "\n         }";
print "\n     }";
print "\n }";
print "\n function setDisplay(thisRow, thisBackground, thisTitle){";
print "\n     var thisFT  = 'selFT' + thisRow;";
print "\n     var thisCL  = 'selCL' + thisRow;";
print "\n     var thisBox = document.getElementById(thisFT);";
print "\n     thisBox.parentNode.style.backgroundColor= thisBackground;";
print "\n     thisBox.parentNode.title = thisTitle;";
print "\n     thisBox = document.getElementById(thisCL);";
print "\n     thisBox.parentNode.style.backgroundColor= thisBackground;";
print "\n     thisBox.parentNode.title = thisTitle;";
print "\n }";
print "\n function setWarnDisplay(thisId, thisBackground, thisTitle){";
print "\n     var thisField = document.getElementById(thisId);";
print "\n     thisField.style.backgroundColor= thisBackground;";
print "\n     thisField.title = thisTitle;";
print "\n }";
print "\n </script> \n";
print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y") {
    $HSI215_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);  // Program Option Security
    print "\n <td class=\"toolbar\">";
    if ($HSI215_OPT['sec_02'] == "Y" || $HSI215_OPT['sec_03'] == "Y") {
        print "\n <a id=\"acceptButton\" style=\"visibility:hidden;\" href=\"javascript:Validate_Data(document.Chg)\" > $selectAcceptImage</a>";
        print "\n <a href=\"javascript:selectAll(document.Chg)\">$selectAllImage</a>";
        print "\n <a href=\"javascript:deselectAll(document.Chg)\">$deselectAllImage</a>";
    }
    $medIcon = "Y";
    require_once 'FormatToprint.php';
    require_once 'HelpPage.php';
    print "</td>";
}
print "\n </tr></table>";
require_once 'ConfMessageDisplay.php';
print $hrTagAttr;

$uv_PlantName = "OHPLT";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDMOHMV02.* ";
$fileSQL .= " HDMOHMV02 ";
$selectSQL .= "(OHSTC='A' or OHSTC='T')";
$viewCheckSQL = Build_CheckBoxSQL($viewCheckBoxDef, $viewCheckBox);
if ($viewCheckSQL != "") {
    $selectSQL .= " and " . $viewCheckSQL;
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

if ($formatToPrint == "") {
    $qsOpt = "";
    $qsOpt .= "\n <option value=\"OHPLT|null|Plant|N|\" title=\"Plant\">Plant";
    $qsOpt .= "\n <option value=\"OHBAC|null|Buyer|N|\" title=\"Buyer\">Buyer";
    $qsOpt .= "\n <option value=\"OHORD|null|Mfg Order|A|U\" title=\"Mfg Order\" SELECTED>Mfg Order";
    $qsOpt .= "\n <option value=\"OHPN|null|Item Number|A|U\" title=\"Item Number\">Item Number";
    $qsOpt .= "\n <option value=\"OHSTC|null|Status|A|U\" title=\"Order Status\">Status";
    $qsOpt .= "\n <option value=\"MWPCT|null|Material Variance|N|\" title=\"Material Variance\">Material Variance";
    $qsOpt .= "\n <option value=\"LWPCT|null|Labor Variance|N|\" title=\"Labor Variance\">Labor Variance";
    $qsOpt .= "\n <option value=\"OHRORD|null|Reference|A|U\" title=\"Reference\">Reference";
    $qsOpt .= "\n <option value=\"OHCQTY|null|Order Qty|N|\" title=\"Order Qty\">Order Qty";
    $qsOpt .= "\n <option value=\"OHQTYR|null|Qty Received|N|\" title=\"Qty Received\">Qty Received";
    $qsOpt .= "\n <option value=\"OHORDT|null|Order Date|D|\" title=\"Order Date\">Order Date";
    $qsOpt .= "\n <option value=\"OHCDDT|null|Due Date|D|\" title=\"Due Date\">Due Date";
    $qsOpt .= "\n <option value=\"OHLADT|null|Last Activity|D|\" title=\"Last Activity\">Last Activity";
    $qsOpt .= "\n <option value=\"OHTSUS|null|User Id|A|U\" title=\"User Id\">User Id";
    require 'QuickSearchOption.php';
}
print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$updateURL}&amp;tag=FIRST_LOAD&amp;backHome=" . urlencode($currentURL) . "\">";
print "\n <table $contentTable> <tr>";
if (($formatToPrint != "Y") && ($HSI215_OPT['sec_02'] == "Y")){
    print "<th class=\"colhdr\">Final Tag</th>";}
if (($formatToPrint != "Y") && ($HSI215_OPT['sec_03'] == "Y")){
    print "<th class=\"colhdr\">Close</th>";}
$returnValue = OrderBy_Sort("OHPLT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Plant\" title=\"Sequence By Plant\">{$sortPoint}Plant</a></th>";
$returnValue = OrderBy_Sort("OHBAC");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Buyer\" title=\"Sequence By Plant, Buyer, Mfg Order\">{$sortPoint}Buyer</a></th>";
$returnValue = OrderBy_Sort("OHORD");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=MfgOrder\" title=\"Sequence By Plant, Mfg Order\">{$sortPoint}Mfg Order</a></th>";
$returnValue = OrderBy_Sort("OHPN");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Item Number\" title=\"Sequence By Plant, Item\">{$sortPoint}Item Number</a></th>";
$returnValue = OrderBy_Sort("OHSTC");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Status\" title=\"Sequence By Plant, Status\">{$sortPoint}Status</a></th>";
$returnValue = OrderBy_Sort("MWPCT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Material Variance\" title=\"Sequence By Plant, Material Variance %\">{$sortPoint}Material Variance</a></th>";
$returnValue = OrderBy_Sort("LWPCT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Labor Variance\" title=\"Sequence By Plant, Labor Variance %\">{$sortPoint}Labor Variance</a></th>";
$returnValue = OrderBy_Sort("OHRORD");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Reference\" title=\"Sequence By Plant, Reference\">{$sortPoint}Reference</a></th>";
$returnValue = OrderBy_Sort("OHCQTY");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Order Qty\" title=\"Sequence By Plant, Order Quantity\">{$sortPoint}Order Qty</a></th>";
$returnValue = OrderBy_Sort("OHQTYR");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Qty Received\" title=\"Sequence By Plant, Quantity Received\">{$sortPoint}Qty Received</a></th>";
$returnValue = OrderBy_Sort("OHORDT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Order Date\" title=\"Sequence By Plant, Order Date\">{$sortPoint}Order Date</a></th>";
$returnValue = OrderBy_Sort("OHCDDT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Due Date\" title=\"Sequence By Plant, Due Date\">{$sortPoint}Due Date</a></th>";
$returnValue = OrderBy_Sort("OHLADT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Last Activity\" title=\"Sequence By Plant, Last Activity Date\">{$sortPoint}Last Activity</a></th>";
$returnValue = OrderBy_Sort("OHTSUS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User Id\" title=\"Sequence By Plant, User Id\">{$sortPoint}User Id</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\">";
    $hover_OHPLT = "View Plant - " . $row['OHPLT'] . "\n" . RetValue("PLPLNT=($row[OHPLT])", "HDPLNT", "PLNAME");
    $hover_OHBAC = RetValue("BMBUYR=($row[OHBAC])", "HDBUYR", "BMBNA1");
    $hover_OHPN = "View Item/Plant - " . trim($row['OHPN']) . "\n" . trim($row['IMIMDS']);
    $W_OQty  = Format_Nbr($row['OHCQTY'], $qtyNbrDec, $qtyEditCode, '', '', '');
    $W_RQty  = Format_Nbr($row['OHQTYR'], $qtyNbrDec, $qtyEditCode, '', '', '');
    $W_ODate = Format_Date_ISO($row['OHORDT'], 'D');
    $W_DDate = Format_Date_ISO($row['OHCDDT'], 'D');
    $W_ADate = Format_Date_ISO($row['OHLADT'], 'D');

    if ($formatToPrint != "Y") {
        if ($HSI215_OPT['sec_02'] == "Y") {
            $FTerr = "";
            $ttype = "FT";
            $FTColor = 'black';
            $FTTitle = "Final Tag in Process";
            if ($rowsInProcess > 0) {
                $FTerr = RetValue("(O8PLT,O8ORD,O8MTCD)=($row[OHPLT],'$row[OHORD]','$ttype')", "HDMOTCQ", "O8ERR");
                if ($FTerr == "Y") {$FTColor = 'red';$FTTitle = "Final Tag Errors";}
            }
            if ($FTerr != "") {
                print "\n <td class=\"colalph\"><span style=\"color: $FTColor\" title=\"$FTTitle\">$ttype</span></td>";
                print "\n <input id=\"selFT$rowCount\" name=\"selFT\" class=\"bigcheck\" type=\"hidden\"  value=''\">";
            } elseif ($row['OHSTC'] == "A") {
                print "\n <td><input id=\"selFT$rowCount\" name=\"selFT\" class=\"bigcheck\" type=\"checkbox\" 
                              onclick=\"displayAccept();\" value='Y'\" title=\"Final Tag\" $ftChecked ></td>";
            } else {
                print "\n <td><input id=\"selFT$rowCount\" name=\"selFT\" class=\"bigcheck\" type=\"hidden\"  value=''\"></td>";
            }
        } else {
            print "\n <input id=\"selFT$rowCount\" name=\"selFT\" class=\"bigcheck\" type=\"hidden\"  value=''\">";
        }
        if ($HSI215_OPT['sec_03'] == "Y") {
            $CLerr = "";
            $ttype = "CL";
            $CLColor = 'black';
            $CLTitle = "Close in Process";
            if ($rowsInProcess > 0) {
                $CLerr = RetValue("(O8PLT,O8ORD,O8MTCD)=($row[OHPLT],'$row[OHORD]','$ttype')", "HDMOTCQ", "O8ERR");
                if ($CLerr == "Y") {$CLColor = 'red';$CLTitle = "Close Errors";}
            }
            if ($CLerr != "") {
                print "\n <td class=\"colalph\"><span style=\"color: $CLColor\" title=\"$CLTitle\">$ttype</span></td>";
                print "\n <input id=\"selCL$rowCount\" name=\"selCL\" class=\"bigcheck\" type=\"hidden\"  value=''\">";
            } elseif ($FTerr != "") {
                print "\n <td><input id=\"selCL$rowCount\" name=\"selCL\" class=\"bigcheck\" type=\"hidden\"  value=''\"></td>";
            } else {
                print "\n <td><input id=\"selCL$rowCount\" name=\"selCL\" class=\"bigcheck\" type=\"checkbox\" 
                              onclick=\"displayAccept();\" value='Y'\" title=\"Close\" $clChecked ></td>";
            }
        } else {
            print "\n <input id=\"selCL$rowCount\" name=\"selCL\" class=\"bigcheck\" type=\"hidden\"  value=''\">";
        }
    }
    print "\n <td class=\"colnmbr\"> <input type=\"hidden\" name=\"PLT$rowCount\" id=\"PLT$rowCount\" value=\"{$row['OHPLT']}\"><a href=\"{$homeURL}{$cGIPath}PlantSelect.d2w/REPORT{$altVarBase}&amp;plantNumber={$row['OHPLT']}\" target=\"_blank\" title=\"$hover_OHPLT\">$row[OHPLT]</a></td>";
    print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$hover_OHBAC\">$row[OHBAC]</span></td>";
    print "\n <td class=\"colalph\"> <input type=\"hidden\" name=\"ORD$rowCount\" id=\"ORD$rowCount\" value=\"{$row['OHORD']}\"> <a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber={$row['OHPLT']}&amp;mfgOrder=" . urlencode(trim($row['OHORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[OHORD]</a></td> ";
    print "\n <td class=\"colalph\"> <a href=\"{$homeURL}{$cGIPath}ItemPlantSelect.d2w/REPORT{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['OHPN'])) . "&amp;plantNumber={$row['OHPLT']}\" target=\"_blank\" title=\"$hover_OHPN\">$row[OHPN]</a></td>";
    print "\n <td class=\"colalph\"> <span title=\"Order Status\">$row[OHSTC]</span></td>";
    $F_MWPCT = number_format($row['MWPCT'], 3);
    print "\n <td class=\"colnmbr\" id=\"MWPCT$rowCount\"><span title=\"Material Variance\">$F_MWPCT</span> </td>";
    $F_LWPCT = number_format($row['LWPCT'], 3);
    print "\n <td class=\"colnmbr\" id=\"LWPCT$rowCount\"><span title=\"Labor Variance\">$F_LWPCT</span> </td>";
    print "\n <td class=\"colalph\"> <span title=\"Reference\">$row[OHRORD]</span></td>";
    print "\n <td class=\"colnmbr\"> <span title=\"Order Qty\">$W_OQty</span></td>";
    print "\n <td class=\"colnmbr\"> <span title=\"Qty Received\">$W_RQty</span></td>";
    print "\n <td class=\"colalph\">$W_ODate</td> ";
    print "\n <td class=\"colalph\">$W_DDate</td> ";
    print "\n <td class=\"colalph\">$W_ADate</td> ";
    print "\n <td class=\"colalph\"> <span title=\"User Id\">$row[OHTSUS]</span></td>";
    print "\n <input type=\"hidden\" name=\"UPD$rowCount\" id=\"UPD$rowCount\" value=\"\">";
    print "\n <input type=\"hidden\" name=\"WRN$rowCount\" id=\"WRN$rowCount\" value=\"\">";
    print "\n </tr>";
    $startRow++;
    $rowCount++;
    if ($rowCount == $dspMaxRows) {break;}
}
print "\n <input type=\"hidden\" name=\"displayedRows\" value=\"$rowCount\">";
print "\n <input type=\"hidden\" name=\"hasErrors\" value=\"false\">";
if ($rowCount == 0) {
    require 'NoRecordsFound.php';
}
print "\n </table>";
print "\n </form>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print $hrTagAttr;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>

