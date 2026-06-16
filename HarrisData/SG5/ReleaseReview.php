<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
//require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "Release/Review";
$scriptName = "ReleaseReview.php";
$scriptVarBase = "{$genericVarBase}";
$altScriptVarBase = "{$altVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$dftOrderBy = [["PLPN", "A", "Item Number"],["PLSDAT", "A", "Start Date"]];
$programName = "HMR440_W";
$pageSelectList = 'N';
$advanceSearch = 'N';

if ($tag == "Update_Data") {
    $maintenanceCode = 'A';

    $edtVar = "";
    $selected = 0;

    for($i = 1; $i <= $_POST ['displayedRows']; $i ++) {
        $select = "selRec$i";
        if (!isset($_POST[$select])) continue;
        $selected ++;
        $s = str_pad( $selected, 4, '0', STR_PAD_LEFT);
        $x = str_pad ( $i, 4, '0', STR_PAD_LEFT );
        $PL = "PL$x";
        $TY = "TY$x";
        $PN = "PN$x";
        $DT = "DT$x";
        $SQ = "SQ$x";
        Concat_Field ( "PL$s", $_POST [$PL] );
        Concat_Field ( "TY$s", $_POST [$TY] );
        Concat_Field ( "PN$s", $_POST [$PN] );
        Concat_Field ( "DT$s", $_POST [$DT] );
        Concat_Field ( "SQ$s", $_POST [$SQ] );
    }
    Concat_Field ( "@@rows", $selected );
    $edtVar .= "}{";

    $returnValue=Maintain_Edit_Handle($programName, $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
    $maintenanceCode=$returnValue['maintenanceCode'];
    $errFound       =$returnValue['errFound'];
    $edtVar         =$returnValue['edtVar'];
    $errVar         =$returnValue['errVar'];
    $wrnVar         =$returnValue['wrnVar'];

    if ($errFound == "") {
        $plantName = RetValue("PLPLNT=$_POST[$PL]", "HDPLNT", "PLNAME");
        $fromMo=Decat_Field("@@frmo", $edtVar);
        $thruMo=Decat_Field("@@tomo", $edtVar);
        if ($fromMo == $thruMo) {
            $confMessage = "Confirm Add Of ";
            $F_confirmData1 = Format_Code(trim($fromMo));
            $confirmDesc1 = "<a href=\"{$homeURL}{$phpPath}hdlist.php{$genericVarBase}&amp;tblID=445&amp;fKey1=OHPLT&amp;fVal1={$_POST[$PL]}&amp;fKey2=OHORD&amp;fVal2={$fromMo}\" title=\"View Manufacturing Order\">Mfg Order";
            $confMessage.= "$confirmDesc1 $F_confirmData1";
            $F_confirmData2 = Format_Code(trim($_POST[$PL]));
            $confirmDesc2 = "</a> in " . $plantName;
            $confMessage .= " $confirmDesc2 $F_confirmData2";
        } else {
            $confMessage = "Confirm Add Of ";
            $F_confirmData1 = Format_Code(trim("$fromMo - $thruMo"));
            $confirmDesc1 = "<a href=\"{$homeURL}{$cGIPath}MfgOrderPrint.d2w/REPORT{$altVarBase}&amp;reportSelType=R&amp;fromPlant={$_POST[$PL]}&amp;fromMO={$fromMo}&amp;thruMO={$thruMo}&amp;fromUser={$userProfile}\" title=\"View Manufacturing Order Print\">Mfg Orders";
            $confMessage.= "$confirmDesc1 $F_confirmData1";
            $F_confirmData2 = Format_Code(trim($_POST[$PL]));
            $confirmDesc2 = "</a> in " . $plantName;
            $confMessage .= " $confirmDesc2 $F_confirmData2";
        }
     }
}

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = [["", "Show Shortages", $viewCheckBoxURL, "1", "0"]];

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
    if ($sequence == "Item") {
        $orby = [["PLPN", "A", "Item Number"]];
    } elseif ($sequence == "Desc") {
        $orby = [["upper(IMIMDS)", "A", "Item Description"]];
    } elseif ($sequence == "Pqty") {
        $orby = [["PLPQTY", "A", "Planned Order Qty"]];
    } elseif ($sequence == "Short") {
        $orby = [["PLCPNS", "A", "Component Shortage"]];
    } elseif ($sequence == "Start") {
        $orby = [["PLSDAT", "A", "Start Date"]];
    } elseif ($sequence == "Due") {
        $orby = [["PLDAT", "A", "Due Date"]];
    } elseif ($sequence == "Action") {
        $orby = [["PLOACT", "A", "Order Action Code"]];
    } elseif ($sequence == "Message") {
        $orby = [["PLCODE", "A", "Message Code"]];
    } elseif ($sequence == "Type") {
        $orby = [["PLTYPE", "A", "Row Type"]];
    } elseif ($sequence == "Sord") {
        $orby = [["PLORD#", "A", "Sales Order Number"]];
    } elseif ($sequence == "Sline") {
        $orby = [["PLORL#", "A", "Sales Line Number"]];
    } elseif ($sequence == "Buyer") {
        $orby = [["PLBAC", "A", "Buyer Analyst"]];
    } elseif ($sequence == "Bname") {
        $orby = [["upper(BMBNA1)", "A", "Buyer/Analyst Name"]];
    } elseif ($sequence == "Mord") {
        $orby = [["PLMORD", "A", "Mfg Order Number"]];
    } elseif ($sequence == "Reference") {
        $orby = [["PLRORD", "A", "Reference Number"]];
    } elseif ($sequence == "Plant") {
        $orby = [["PLPLT", "A", "Plant"]];
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    require_once 'QuickSearch.php';
}

if (isset($_GET['chgBox'])) {
    require "ViewCheckBoxUpdate.php";
}

require_once($docType);
print "\n <html> \n	<head>";
require_once($headInclude);
$formName = "Search";

print "\n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'DateEdit.php';
require_once 'Menu.js';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
?>
function checkChg(chgForm) {
    if (validateChg(chgForm))
        chgForm.submit();
}

function validateChg(form) {
    var rows = (form.displayedRows.value ? form.displayedRows.value : 0);
    var selRows = 0;
    for (var rx = 1; rx <= rows; rx++) {
        var selFld = "selRec" + rx;
        if (form[selFld].checked) {
            selRows++;
            var suffix = rx.toString();
            while (suffix.length < 4) {
                suffix = "0" + suffix;
            }
            var elemCS = "CS" + suffix;
            if (form[elemCS].value == "Y") {
                return confirm("One or more selected items have component shortages. Do you want to force release?");
            }
        }
    }
    if (selRows == 0) {alert("No items selected."); return false;}
    return true;
}

function selectAll(form) {
    for (var fx = 0; fx < form.elements.length; fx++) {
        if (form.elements[fx].type == "checkbox") {
            form.elements[fx].checked = true;
        }
    }
}

function clearAll(form) {
    for (var fx = 0; fx < form.elements.length; fx++) {
        if (form.elements[fx].type == "checkbox") {
            form.elements[fx].checked = false;
        }
    }
}
<?php
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "RELEASEREVIEW";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

print "\n <td class=\"toolbar\">";
print "\n   <a href=\"javascript:checkChg(document.Chg)\">$selectAcceptImage</a>";
print "\n   <a href=\"javascript:selectAll(document.Chg)\">&nbsp; $selectAllImage</a>";
print "\n   <a href=\"javascript:clearAll(document.Chg)\">&nbsp; $selectClearImage</a>";
print "\n   <a href=\"{$homeURL}{$cGIPath}MFGMRP.d2w/REPORT{$altVarBase}\" title=\"MRP\">&nbsp; {$cancelImageMed}</a>";
print "\n </td>";

print "\n </tr></table>";
require_once 'ConfMessageDisplay.php';
print $hrTagAttr;

$uv_PlantName ="PLPLT";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL = "Select HDMPLM.*, coalesce(IMIMDS,'') as IMIMDS, coalesce(BMBNA1,'') as BMBNA1";
$fileSQL = "HDMPLM ";
$fileSQL .= " left join HDIMST on PLPN=IMITEM";
$fileSQL .= " left join HDBUYR on PLBAC=BMBUYR";
$selectSQL = " PLSELC = 'Y' and PLPTYP = 'M' and PLTYPE in ('P','R') ";
if (!$viewCheckBox[0]) {
    $selectSQL .= " and PLCPNS <> 'Y' ";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
//$pageSelectList = "Y";
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
// echo $stmtSQL;
// echo $sql_Record_Count;

$qsOpt = "\n <option value=\"PLPN|null|Item Number|A|U\" title=\"Item Number\" SELECTED>Item Number";
$qsOpt .= "\n <option value=\"upper(IMIMDS)|null|Item Description|A|U\" title=\"Item Description\">Item Description";
$qsOpt .= "\n <option value=\"PLPQTY|null|Planned Order Quantity|N|\" title=\"Planned Order Quantity\">Planned Order Quantity";
$qsOpt .= "\n <option value=\"PLCPNS|null|Component Shortage|A|U\" title=\"Component Shortage\">Component Shortage";
$qsOpt .= "\n <option value=\"PLSDAT|DATE|Start Date|I|\" title=\"Start Date\">Start Date";
$qsOpt .= "\n <option value=\"PLDAT|DATE|Due Date|I|\" title=\"Due Date\">Due Date";
$qsOpt .= "\n <option value=\"PLOACT|null|Order Action Code|A|U\" title=\"Order Action Code\">Order Action Code";
$qsOpt .= "\n <option value=\"PLCODE|null|Msg Cde|A|U\" title=\"Msg Cde\">Msg Cde";
$qsOpt .= "\n <option value=\"PLTYPE|null|Row Type|A|U\" title=\"Row Type\">Row Type";
$qsOpt .= "\n <option value=\"PLORD#|null|Sales Order Number|N|\" title=\"Sales Order Number\">Sales Order Number";
$qsOpt .= "\n <option value=\"PLORL#|null|Sales Line Number|N|\" title=\"Sales Line Number\">Sales Line Number";
$qsOpt .= "\n <option value=\"PLBAC|null|Buyer Analyst|N|\" title=\"Buyer Analyst\">Buyer Analyst";
$qsOpt .= "\n <option value=\"upper(BMBNA1)|null|Buyer/Analyst Name|A|U\" title=\"Buyer/Analyst Name\">Buyer/Analyst Name";
$qsOpt .= "\n <option value=\"upper(PLMORD)|null|Mfg Order Number|A|U\" title=\"Mfg Order Number\">Mfg Order Number";
$qsOpt .= "\n <option value=\"upper(PLRORD)|null|Reference Number|A|U\" title=\"Reference Number\">Reference Number";
$qsOpt .= "\n <option value=\"PLPLT|null|Plant|N|\" title=\"Plant\">Plant";
require 'QuickSearchOption.php';

print "\n \n <form class=\"formClass\" METHOD=\"post\" name=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Update_Data\">";

print "<table $contentTable> <tr>";
print "\n <th class=\"colhdr\">Select</th>";

$returnValue = OrderBy_Sort("PLPN");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Item\" title=\"Sequence By Item\">{$sortPoint}Item Number</a></th>";

$returnValue = OrderBy_Sort("IMIMDS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\" title=\"Sequence By Description\">{$sortPoint}Item Description</a></th>";

$returnValue = OrderBy_Sort("PLPQTY");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Pqty\" title=\"Sequence By Quantity\">{$sortPoint}Planned<br>Order Quantity</a></th>";

$returnValue = OrderBy_Sort("PLCPNS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Short\" title=\"Sequence By Shortage\">{$sortPoint}Component<br>Shortage</a></th>";

$returnValue = OrderBy_Sort("PLSDAT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Start\" title=\"Sequence By Start Date\">{$sortPoint}Start Date</a></th>";

$returnValue = OrderBy_Sort("PLDAT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Due\" title=\"Sequence By Due Date\">{$sortPoint}Due Date</a></th>";

$returnValue = OrderBy_Sort("PLOACT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Action\" title=\"Sequence By Order Action Code\">{$sortPoint}Order<br>Action<br>Code</a></th>";

$returnValue = OrderBy_Sort("PLCODE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Message\" title=\"Sequence By Message Code\">{$sortPoint}Msg<br>Cde</a></th>";

$returnValue = OrderBy_Sort("PLTYPE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Type\" title=\"Sequence By Row Type\">{$sortPoint}Row<br>Type</a></th>";

$returnValue = OrderBy_Sort("PLORD#");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Sord\" title=\"Sequence By Sales Order\">{$sortPoint}Sales<br>Order<br>Number</a></th>";

$returnValue = OrderBy_Sort("PLORL#");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Sline\" title=\"Sequence By Sales Line\">{$sortPoint}Sales<br>Line<br>Number</a></th>";

$returnValue = OrderBy_Sort("PLBAC");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Buyer\" title=\"Sequence By Buyer Analyst\">{$sortPoint}Buyer<br>Analyst</a></th>";

$returnValue = OrderBy_Sort("BMBNA1");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Bname\" title=\"Sequence By Buyer Analyst Name\">{$sortPoint}Buyer/Analyst<br>Name</a></th>";

$returnValue = OrderBy_Sort("PLMORD");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Mord\" title=\"Sequence By Mfg Order\">{$sortPoint}Mfg<br>Order<br>Number</a></th>";

$returnValue = OrderBy_Sort("PLRORD");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Reference\" title=\"Sequence By Reference\">{$sortPoint}Reference<br>Number</a></th>";

$returnValue = OrderBy_Sort("PLPLT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Plant\" title=\"Sequence By Plant\">{$sortPoint}Plant</a></th>";

print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    $rowCount ++;
    $startRow ++;

    require 'SetRowClass.php';

    $recNbr = $rowCount;
    $recNbr = str_pad ( $recNbr, 4, '0', STR_PAD_LEFT );

    print "\n <tr class=\"$rowClass\">";
    print "\n     <td><input id=\"selRec$rowCount\" name=\"selRec$rowCount\" class=\"bigcheck\" type=\"checkbox\"  value=\"Y\" $orderChecked >";
    $T_PLPN = trim($row['PLPN']);
    print "\n     <td class=\"colalph\"><input type=\"hidden\" name=\"PN$recNbr\" value=\"{$row['PLPN']}\"><a href=\"{$homeURL}{$phpPath}hdlist.php{$genericVarBase}&amp;tblID=180&amp;fKey1=DTITEM&amp;fVal1={$T_PLPN}&amp;fKey2=DTPLT&amp;fVal2={$row['PLPLT']}\" title=\"View Transaction History\">{$row['PLPN']}</a></td> ";
    print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemPlantSelect.d2w/REPORT{$altVarBase}&amp;itemNumber={$T_PLPN}&amp;plantNumber={$row['PLPLT']}\" title=\"View Item/Plant\">{$row['IMIMDS']}</a></td> ";
    $F_PLPQTY = Format_Nbr($row['PLPQTY'], $qtyNbrDec, $qtyEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}HdList.php{$genericVarBase}&amp;tblID=522&amp;fKey1=PGPPLT&amp;fVal1={$row['PLPLT']}&amp;fKey2=PGCPN&amp;fVal2={$T_PLPN}\" title=\"View Pegged Requirements\">$F_PLPQTY</a></td> ";
    if ($row['PLCPNS'] == 'Y') {
        print "\n     <td class=\"colalph\"><input type=\"hidden\" name=\"CS$recNbr\" value=\"{$row['PLCPNS']}\"><a href=\"{$homeURL}{$phpPath}ExplosionDashboard.php{$genericVarBase}&amp;tag=Edit_Data&amp;itemNumber={$T_PLPN}&amp;plantNumber={$row['PLPLT']}&amp;reqQty={$row['PLPQTY']}\" title=\"Explosion Dashboard\">{$row['PLCPNS']}</a></td> ";
    } else {
        print "\n     <td class=\"colalph\"><input type=\"hidden\" name=\"CS$recNbr\" value=\"{$row['PLCPNS']}\">{$row['PLCPNS']}</td> ";
    }
    $F_PLSDAT = Format_Date_ISO($row['PLSDAT'], 'D');
    print "\n     <td class=\"colalph\">$F_PLSDAT</td> ";
    $F_PLDAT = Format_Date_ISO($row['PLDAT'], 'D');
    print "\n     <td class=\"colalph\"><input type=\"hidden\" name=\"DT$recNbr\" value=\"{$row['PLDAT']}\"><a href=\"{$homeURL}{$phpPath}HdList.php{$genericVarBase}&amp;tblID=184&amp;fKey1=ODITEM&amp;fVal1={$T_PLPN}\" title=\"View All Open Items\">$F_PLDAT</a></td> ";
    print "\n     <td class=\"colalph\">{$row['PLOACT']}</td> ";
    print "\n     <td class=\"colalph\">{$row['PLCODE']}</td> ";
    print "\n     <td class=\"colalph\"><input type=\"hidden\" name=\"TY$recNbr\" value=\"{$row['PLTYPE']}\">{$row['PLTYPE']}</td> ";
    print "\n     <td class=\"colnmbr\">{$row['PLORD#']}</td> ";
    print "\n     <td class=\"colnmbr\">{$row['PLORL#']}</td> ";
    print "\n     <td class=\"colnmbr\">{$row['PLBAC']}</td> ";
    print "\n     <td class=\"colalph\">{$row['BMBNA1']}</td> ";
    print "\n     <td class=\"colalph\">{$row['PLMORD']}</td> ";
    print "\n     <td class=\"colalph\">{$row['PLRORD']}</td> ";
    print "\n     <td class=\"colnmbr\"><input type=\"hidden\" name=\"PL$recNbr\" value=\"{$row['PLPLT']}\"><a href=\"{$homeURL}{$cGIPath}PlantSelect.d2w/REPORT{$altVarBase}&amp;plantNumber={$row['PLPLT']}\" title=\"View Plant\">{$row['PLPLT']}</a></td> ";
    print "\n     <td class=\"colnmbr\"><input type=\"hidden\" name=\"SQ$recNbr\" value=\"{$row['PLSEQN']}\"></td> ";
    print "\n </tr>";
}
print "\n <tr><td><input type=\"hidden\" name=\"displayedRows\" value=\"$rowCount\"></td></tr>";

if ($rowCount == 0) {
    require 'NoRecordsFound.php';
}

print "</table>";
print $hrTagAttr;
require_once 'Copyright.php';

?>
