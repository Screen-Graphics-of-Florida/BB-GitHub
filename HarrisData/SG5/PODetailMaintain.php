<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound = $_GET['errFound'];
$wrnVar = $_GET['wrnVar'];
$vendorNumber = $_GET['vendorNumber'];
$orderControl = (isset($_GET['orderControl']) && $_GET['orderControl'] > 0) ? $_GET['orderControl'] : 0;
$purchaseOrder = (isset($_GET['purchaseOrderNumber'])) ? $_GET['purchaseOrderNumber'] : 0;
$lineNumber = (isset($_GET['lineNumber'])) ? $_GET['lineNumber'] : 0;
$noMenu = (isset($_GET ['noMenu'])) ? $_GET ['noMenu'] : '';
$fromType = (isset($_GET['fromType'])) ? $_GET['fromType'] : '';
$fromReqNbr = (isset($_GET['fromReqNbr'])) ? $_GET['fromReqNbr'] : '';
$fromItem = (isset($_GET['fromItem'])) ? $_GET['fromItem'] : '';
$fromItemDesc = (isset($_GET['fromItemDesc'])) ? $_GET['fromItemDesc'] : '';
$plantNumber = (isset($_GET['plantNumber'])) ? $_GET['plantNumber'] : 0;
$seqNbr = (isset($_GET['seqNbr'])) ? $_GET['seqNbr'] : 0;
$dueDate = (isset($_GET['dueDate'])) ? $_GET['dueDate'] : 0;
$orderQty = (isset($_GET['orderQty'])) ? $_GET['orderQty'] : 0;
$buyer = (isset($_GET['buyer'])) ? $_GET['buyer'] : 0;
$poBusy = (isset($_GET['poBusy'])) ? $_GET['poBusy'] : null;
$reqFilter = (isset($_GET['reqFilter'])) ? $_GET['reqFilter'] : null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'POUserDefinedInclude.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';

$programName = "HPOPEM";
$hpopem_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $hpopem_OPT['sec_01'];
$sec_02 = $hpopem_OPT['sec_02'];
$sec_03 = $hpopem_OPT['sec_04'];
if ($purchaseOrder > 0) {
    $sec_03 = 'N';
}
if (!is_null($poBusy)) {
    $sec_01 = 'N';
    $sec_02 = 'N';
    $sec_03 = 'N';
}

if ($orderControl == 0 && $purchaseOrder == 0) {
    $_SESSION ['po_setHdr'] = 'Y';
    $_SESSION ['po_type'] = $fromType;
    $_SESSION ['po_reqNbr'] = $fromReqNbr;
    $_SESSION ['po_item'] = $fromItem;
    $_SESSION ['po_itemDesc'] = $fromItemDesc;
    $_SESSION ['po_plant'] = $plantNumber;
    $_SESSION ['po_seqNbr'] = $seqNbr;
    $_SESSION ['po_dueDate'] = $dueDate;
    $_SESSION ['po_buyer'] = $buyer;
    $_SESSION ['po_orderQty'] = $orderQty;
    $_SESSION ['po_addItem'] = (isset($_POST['addItem'])) ? $_POST['addItem'] : null;
    $_SESSION ['po_addQty'] = (isset($_POST['addQty'])) ? $_POST['addQty'] : null;
    $_SESSION ['po_mncd'] = $maintenanceCode;
    if (!is_null($reqFilter)) {
        $_SESSION ['po_reqFilter'] = $reqFilter;
    }

    if ($maintenanceCode == "M") {
        $_SESSION["returnURL"] = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;portal=MFGMGMT&amp;tblID=460&amp;tag=QSEARCH";
    }
    $orderControl = RetValue("H1USER='$userProfile'", "POHDRW", "coalesce(H1OCTL,0)");
    if ($orderControl > 0) {
        $tag = 'Edit_Data';
    } else {
        print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}POHeaderMaintain.php{$genericVarBase}&amp;maintenanceCode=A&amp;tag=MAINTAIN&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "\">";
        exit();
    }
}

$backURL = $_SESSION[$fromURL];
if ($backURL == "") {
    $backURL = "{$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}";
}

$page_title = "PO Item Maintenance";
$scriptName = "PODetailMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrder)) . "&amp;lineNumber=" . urlencode(trim($lineNumber)) . "&amp;noMenu=" . urlencode(trim($noMenu));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D&amp;fromType={$fromType}&amp;fromReqNbr={$fromReqNbr}&amp;fromItem={$fromItem}&amp;startRow=" . urlencode(trim($startRow));

$oeNumber = 0;
if ($CEOEPO != "N" && $row[PDOEPO] == 'Y') {
    $oeNumber = RetValue("ODPO#=$purchaseOrder and ODPOL#=$lineNumber and ODPORL=0", "OEORDT inner join OEORHD on ODORD#=OEORD#", "ODORD#");
}

if ($tag == "MAINTAIN") {
    require_once($docType);
    print "\n <html> <head>";
    require_once($headInclude);
    $formName = "Chg";
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'Menu.js';

    require_once 'CalendarInclude.php';
    require_once 'CheckEnterChg.php';
    require_once 'DateEdit.php';
    require_once 'NumEdit.php';
    require_once 'SaveCurrentURL.php';
    require_once 'DateEdit.php';
    require_once 'UpperCase.php';

    print "\n 
    function CalcDateWindow(winURL, winName) {
        winprops = 'height=1,width=1,top=1,left=1,scrollbars=no,resizable=no,toolbar=no,menubar=no';
        win = window.open(winURL, winName, winprops)
    }
    function wrnOEPO(){
      alert ('Warning - change will remove link to O/E Order');
    }";

    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.itemNumber.value ==\"\" || ";
    print "\n     document.Chg.warehouse.value ==\"\" || ";
    print "\n     document.Chg.qtyOrdered.value ==\"\" ";
    if ($maintenanceCode == 'N') {
        print "\n || document.Chg.prodClass.value ==\"\" ";
        print "\n || document.Chg.buom.value ==\"\" ";
        print "\n || document.Chg.itemDesc.value ==\"\" ";
    }
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";

    print "\n if (editZero(document.Chg.qtyOrdered, 13, 4) && ";
    print "\n     editNum(document.Chg.warehouse, 3, 0) && ";
    print "\n     editNum(document.Chg.piecesPer, 9, 4) && ";
    print "\n     editdate(document.Chg.reqDate) ";
    if ($poDetailDate1 != '') {
        print "\n  && editdate(document.Chg.ud1Date) ";
    }
    if ($poDetailDate2 != '') {
        print "\n  && editdate(document.Chg.ud2Date) ";
    }
    if ($poDetailDate3 != '') {
        print "\n  && editdate(document.Chg.ud3Date) ";
    }
    print "\n ) return true; ";
    print "\n } ";

    print "\n  function confirmDelete() {return confirm(\"$delRecordConf\")} ";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";

    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    if ($purchaseOrder == 0) {
        require_once 'Banner.php';
    }
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    if ($purchaseOrder == 0) {
        $pageID = "PODETAILMAINTAIN";
        require_once 'MenuDisplay.php';
    }
    print "\n <td class=\"content\">";

    if ($purchaseOrder > 0) {
        $stmtSQL = " Select POVEND as H1VEND,POTYPE as H1TYPE,PORQDT as H1RQDT,POWHS as H1WHS From POPOMS Where POPO={$purchaseOrder} Fetch First Row Only";
        $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
        $hdrRow = db2_fetch_assoc($sqlResult);

        $stmtSQL = " Select POPOMD.*,0 as D1OCTL,PDPO as D1PO,'' as D1TYPE,'' as D1REQN,PDITEM as D1ITEM,
                      '' as D1SELC,PDPOEC as D1POEC,PDOVWH as D1OVWH,PDIMDS as D1IMDS,PDSUOM as D1SUOM,
                      PDBUOM as D1BUOM,PDPCPB as D1PCPB,PDPCLS as D1PCLS,PDDSCC as D1DSCC,PDQTOR as D1QTOR,
                      PDBOAL as D1BOAL,PDTXCD as D1TXCD,PDRQDT as D1RQDT,PDVDSN as D1VDSN,PDRORD as D1RORD,
                      PDDSCF as D1DSCF,PDECN as D1ECN,PDUCC as D1UCC,PDOPDT as D1OPDT,PDCPDT as D1CPDT,
                      PDLADT as D1LADT,(PDQRST+PDQRRT+PDQRVT+PDQRFT)/PDPCPB as QTYRCV,
                      PDQTOR-(PDQRST+PDQRRT+PDQRFT) as PDOPEN, (PDQTOR*PDDSCC)/PDPCPB as PDEXTC";
        $stmtSQL .= " From POPOMD ";
        $stmtSQL .= " Where PDPO=$purchaseOrder and PDPOL#=$lineNumber and PDPORL=0";
    } else {
        $stmtSQL = " Select * From POHDRW Where H1OCTL={$orderControl} Fetch First Row Only";
        $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
        $hdrRow = db2_fetch_assoc($sqlResult);

        $stmtSQL = "";
        $stmtSQL .= " Select * ";
        $stmtSQL .= " From PODTLW ";
        $stmtSQL .= " Where D1OCTL=$orderControl and D1TYPE='$fromType' and D1REQN='$fromReqNbr' and D1ITEM='$fromItem' ";
    }
    require 'stmtSQLEnd.php';
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

	$udCol = Rtv_PO_UserDefined_Columns('POOUMD', $vendorNumber, $hdrRow['H1TYPE'], $row['D1PCLS']);
	$userDefDtlCnt = (!empty($udCol)) ? count($udCol) : 0;

    print "\n <table $contentTable>";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td>";
    print "\n     <td class=\"toolbar\">";
    if (($sec_01 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "Z")) || ($sec_02 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "C")) || ($maintenanceCode != "A" && $maintenanceCode != "C" && $maintenanceCode != "D" && $maintenanceCode != "Z")) {
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
    }

    if ($noMenu == "Y") {
        print "\n <a href=\"javascript:opener.location.href=opener.location.href; javascript:window.close()\">$closeImageMed</a> ";
    } elseif ($backURL != "") {
        print "\n <a href=\"$backURL\">$cancelImageMed</a>";
    } else {
        print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";
    }

    if ($sec_03 != "N" && $maintenanceCode == "C") {
        print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";
    }
	
	if ($userDefDtlCnt > 0) {
		$smU = "<img border=\"0\" src=\"{$homeURL}{$imagePath}smU.gif\" title=\"Update User-Defined\" alt=\"U\">";
		print "\n <div class=\"quickLinksTop\" align='bottom'><a href=\"{$homeURL}{$phpPath}POUserDefinedMaintain.php{$scriptVarBase}&amp;udTable=POOUMD&amp;tag=MAINTAIN" . "&amp;fromPO=" . urlencode(trim($purchaseOrder)) .  "&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;fromLine=" . trim($lineNumber) . "&amp;fromItem=" . trim($row[D1ITEM]) . "&amp;fromItemDesc=" . urlencode(trim($row[D1IMDS])) . "\"  onclick = \"$inquiryWinVar\">$smU</a></div>";
	}
	
    $medIcon = "Y";
    require 'HelpPage.php';
    print "\n </td></tr></table>";

    $vendorName = RetValue("VMVEND=$hdrRow[H1VEND]", "HDVEND", "VMVNA1");
    print "\n <table $contentTable><tr><td>";
    Format_Header_URL("Vendor", $vendorName, $hdrRow[H1VEND], "");
    if ($purchaseOrder > 0) {
        $lineDesc = ($lineNumber > 0) ? '/ ' . $lineNumber : '';
        Format_Header_URL("Order / Line", "$purchaseOrder $lineDesc", "", "");
    }
    if ($CEOEPO != "N" && $row[PDOEPO] == 'Y') {
        $oeNumber = RetValue("ODPO#=$purchaseOrder and ODPOL#=$lineNumber and ODPORL=0", "OEORDT inner join OEORHD on ODORD#=OEORD#", "ODORD#");
        if ($oeNumber > 0) {
            Format_Header_URL("O/E Order", "$oeNumber", "", "");
        }
    }
    print "\n </td></tr></table>";

    print $hrTagAttr;
    require_once 'RequiredField.php';
    if (!is_null($poBusy)) {
        print "\n <span class=\"error\" $textOvr> &nbsp; &nbsp; Cannot update - Order is being maintained by another user</span>";
    } else {
        require_once 'ErrorDisplay.php';
    }

    if ($errFound != "" || $maintenanceCode == "A" || $maintenanceCode == "N") {
        if ($errFound == "" && ($maintenanceCode == "A" || $maintenanceCode == "N")) {
            $edtVar = "";
            if ($maintenanceCode == "N") {
                $row['D1RQDT'] = DateInputFromCYMD($hdrRow[H1RQDT]);
                $row['D1BOAL'] = 'Y';
                $row['D1TXCD'] = 'N';
                $row['D1DSCF'] = 'N';
                $row['D1UCC'] = 'N';
                $row['D1POEC'] = 'N';
                $focusField = "itemNumber";
            }
            if ($maintenanceCode == "A" && $purchaseOrder > 0) {
                $row['D1POEC'] = 'S';
                $row['D1OVWH'] = $hdrRow[H1WHS];
                $row['D1RQDT'] = DateInputFromCYMD($hdrRow[H1RQDT]);
                $focusField = "itemNumber";
            }
        } elseif ($errFound != "") {
            $focusField = "";
            $edtVar = EdtVarErr($profileHandle, $edtVar);
            $errVar = ErrVarErr($profileHandle, $errVar);

            $Err_D1ITEM = DecatErr_Field("@@item", "itemNumber");
            $Err_D1IMDS = DecatErr_Field("@@imds", "itemDesc");
            $Err_D1SUOM = DecatErr_Field("@@suom", "suom");
            $Err_D1BUOM = DecatErr_Field("@@buom", "buom");
            $Err_D1PCPB = DecatErr_Field("@@pcpb", "piecesPer");
            $Err_D1OVWH = DecatErr_Field("@@whs@", "warehouse");
            $Err_D1POEC = DecatErr_Field("@@poec", "entryCode");
            $Err_D1PCLS = DecatErr_Field("@@pcls", "pcls");
            $Err_D1QTOR = DecatErr_Field("@@qtor", "qtyOrdered");
            $Err_D1DSCC = DecatErr_Field("@@cost", "cost");
            $Err_D1RQDT = DecatErr_Field("@@rqdt", "reqDate");
            $Err_D1BOAL = DecatErr_Field("@@boal", "boAllowed");
            $Err_D1TXCD = DecatErr_Field("@@txcd", "taxable");
            $Err_D1VDSN = DecatErr_Field("@@vdsn", "vendorItem");
            $Err_D1RORD = DecatErr_Field("@@rord", "refOrder");
            $Err_D1DSCF = DecatErr_Field("@@dscf", "discAllowed");
            $Err_D1ECN = DecatErr_Field("@@ecn@", "engChgNotice");
            $Err_D1UCC = DecatErr_Field("@@ucc@", "updCurCost");

            $row['D1ITEM'] = Decat_Field("@@item", $edtVar);
            $row['D1IMDS'] = Decat_Field("@@imds", $edtVar);
            $row['D1SUOM'] = Decat_Field("@@suom", $edtVar);
            $row['D1BUOM'] = Decat_Field("@@buom", $edtVar);
            $row['D1PCPB'] = Decat_Field("@@pcpb", $edtVar);
            $row['D1OVWH'] = Decat_Field("@@whs@", $edtVar);
            $row['D1POEC'] = Decat_Field("@@poec", $edtVar);
            $row['D1PCLS'] = Decat_Field("@@pcls", $edtVar);
            $row['D1QTOR'] = Decat_Field("@@qty@", $edtVar);
            $row['D1DSCC'] = Decat_Field("@@cost", $edtVar);
            $row['D1RQDT'] = Decat_Field("@@rqdt", $edtVar);
            $row['D1OPDT'] = Decat_Field("@@opdt", $edtVar);
            $row['D1CPDT'] = Decat_Field("@@cpdt", $edtVar);
            $row['D1LADT'] = Decat_Field("@@ladt", $edtVar);
            $row['D1BOAL'] = Decat_Field("@@boal", $edtVar);
            $row['D1TXCD'] = Decat_Field("@@txcd", $edtVar);
            $row['D1VDSN'] = Decat_Field("@@vdsn", $edtVar);
            $row['D1RORD'] = Decat_Field("@@rord", $edtVar);
            $row['D1DSCF'] = Decat_Field("@@dscf", $edtVar);
            $row['D1ECN'] = Decat_Field("@@ecn@", $edtVar);
            $row['D1UCC'] = Decat_Field("@@ucc@", $edtVar);
        }
    } else {
        $row['D1RQDT'] = DateInputFromCYMD($row['D1RQDT']);
        $row['D1OPDT'] = DateInputFromCYMD($row['D1OPDT']);
        $row['D1CPDT'] = DateInputFromCYMD($row['D1CPDT']);
        $row['D1LADT'] = DateInputFromCYMD($row['D1LADT']);
        $focusField = "qtyOrdered";
    }

    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;fromType={$fromType}&amp;fromReqNbr={$fromReqNbr}&amp;fromItem={$fromItem}&amp;startRow={$startRow}&amp;tag=Edit_Data&amp;maintenanceCode={$maintenanceCode}\">";
    print "\n <table $contentTable>";

    if ($maintenanceCode == 'A' || $maintenanceCode == 'N' || ($row[D1POEC] == "N" && $row[QTYRCV] == 0)) {
        if ($maintenanceCode == 'A') {
            $textOvr = SetTextOvr($Err_D1ITEM);
            print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Item Number</span></td> ";
            print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"itemNumber\" value=\"" . trim($row['D1ITEM']) . "\" size=\"15\" maxlength=\"15\"> ";
            print "\n                              <a href=\"{$homeURL}{$phpPath}ItemSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=itemNumber&amp;fldDesc=itemDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
            print "\n                              <span class=\"dspdesc\" id=\"itemDesc\">$fieldDesc</span></td>";
            print "\n  </tr> ";
            DspErrMsg($Err_D1ITEM);
        } else {
            Build_Fld_Entry('Item Number', "itemNumber", "inputalph", "", "D1ITEM", $row[D1ITEM], $Err_D1ITEM, "15", "15", "Y", "", "");
            print "\n  <tr><td><input type=\"hidden\" name=\"updItem\" value=\"Y\"></td></tr> ";
        }
    } else {
        Build_DspFld('Item Number', trim($row[D1ITEM]) . '', '', 'A');
        print "\n  <tr><td><input type=\"hidden\" name=\"itemNumber\" value=\"{$row[D1ITEM]}\"></td></tr> ";
    }

    $required = ($maintenanceCode == 'N') ? 'Y' : '';
    Build_Fld_Entry('Description', "itemDesc", "inputalph", "", "D1IMDS", htmlspecialchars($row[D1IMDS]), $Err_D1IMDS, "30", "30", $required, "", "");

    $fieldDesc = RetValue("WHWHS=$row[D1OVWH]", "HDWHSM", "WHWHNM");
    if ($row[QTYRCV] == 0) {
        $onChg = '';
        if ($oeNumber > 0) {
            $onChg = 'onChange="wrnOEPO()"';
        }
        $textOvr = SetTextOvr($Err_D1OVWH);
        print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Number</span></td> ";
        print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"warehouse\" {$onChg} value=\"" . trim($row['D1OVWH']) . "\" size=\"6\" maxlength=\"3\"> ";
        print "\n                              <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=warehouse&amp;fldDesc=warehouseName\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
        print "\n                              <span class=\"dspdesc\" id=\"warehouseName\">$fieldDesc</span></td>";
        print "\n  </tr> ";
        DspErrMsg($Err_D1OVWH);
    } else {
        $fieldDesc .= '  [' . trim($row[D1OVWH]) . ']';
        Build_DspFld('Warehouse Number', $fieldDesc, '', '', 'A');
        print "\n  <tr><td><input type=\"hidden\" name=\"warehouse\" value=\"{$row[D1OVWH]}\"></td></tr> ";
    }

    $onChg = '';
    if ($oeNumber > 0) {
        $onChg = 'onChange="wrnOEPO()"';
    }
    $textOvr = SetTextOvr($Err_D1QTOR);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Order Quantity</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"qtyOrdered\" {$onChg} value=\"" . trim($row['D1QTOR']) . "\" size=\"15\" maxlength=\"15\"> $reqFieldChar ";

    if ($purchaseOrder > 0 && $row[QTYRCV] > 0) {
        print "\n  <td>&nbsp;</td><td rowspan=\"10\"> ";
        print "\n <fieldset><legend class=\"legendTitle\">Quantities</legend> ";
        print "\n <table class=\"contenttable\" float=\"right\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" summary=\"contenttable\">";
        print "<table $contentTable>";
        $F_PDQTOR = Format_Nbr($row['PDQTOR'], $qtyNbrDec, $qtyEditCode, "", "", "");
        print "\n <tr><td class=\"dsphdr\">Ordered</td><td class=\"colnmbr\">$F_PDQTOR</td></tr>";
        $F_PDOPEN = Format_Nbr($row['PDOPEN'], $qtyNbrDec, $qtyEditCode, "", "", "");
        print "\n <tr><td class=\"dsphdr\">Open</td><td class=\"colnmbr\">$F_PDOPEN</td></tr>";
        if ($row['PDQRST'] != 0) {
            $F_PDQRST = Format_Nbr($row['PDQRST'], $qtyNbrDec, $qtyEditCode, "", "", "");
            print "\n <tr><td class=\"dsphdr\">To Stock</td><td class=\"colnmbr\">$F_PDQRST</td></tr>";
        }
        if ($row['PDQRRT'] != 0) {
            $F_PDQRRT = Format_Nbr($row['PDQRRT'], $qtyNbrDec, $qtyEditCode, "", "", "");
            print "\n <tr><td class=\"dsphdr\">To Receiving</td><td class=\"colnmbr\">$F_PDQRRT</td></tr>";
        }
        if ($row['PDQHRT'] != 0) {
            $F_PDQHRT = Format_Nbr($row['PDQHRT'], $qtyNbrDec, $qtyEditCode, "", "", "");
            print "\n <tr><td class=\"dsphdr\">Held In Receiving</td><td class=\"colnmbr\">$F_PDQHRT</td></tr>";
        }
        if ($row['PDQRVT'] != 0) {
            $F_PDQRVT = Format_Nbr($row['PDQRVT'], $qtyNbrDec, $qtyEditCode, "", "", "");
            print "\n <tr><td class=\"dsphdr\">Returned To Vendor</td><td class=\"colnmbr\">$F_PDQRVT</td></tr>";
        }
        if ($row['PDQRFT'] != 0) {
            $F_PDQRFT = Format_Nbr($row['PDQRFT'], $qtyNbrDec, $qtyEditCode, "", "", "");
            print "\n <tr><td class=\"dsphdr\">To Floorstock</td><td class=\"colnmbr\">$F_PDQRFT</td></tr>";
        }
        print "\n </table>";
        print "\n </fieldset></td>";
    }
    print "\n  </tr> ";
    DspErrMsg($Err_D1QTOR);

    if ($hpopem_OPT['sec_07'] != 'N') {
        $textOvr = SetTextOvr($Err_D1DSCC);
        print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Cost</span></td> ";
        print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"cost\" value=\"" . trim($row['D1DSCC']) . "\" size=\"15\" maxlength=\"15\"> ";
        print "\n  </tr> ";
        DspErrMsg($Err_D1DSCC);
    } else {
        Build_DspFld('Cost', $row['D1DSCC'], '', 'N');
    }

    if ($purchaseOrder > 0 && $maintenanceCode == 'C') {
        $F_PDEXTC = Format_Nbr($row['PDEXTC'], "2", $prcEditCode, "", "", "");
        Build_DspFld("Extended Cost", $F_PDEXTC);
    }

    $applyLeadTime = "<img border=\"0\" src=\"{$homeURL}{$imagePath}smL.gif\" title=\"Set Required Date to current date plus Lead Time\" alt=\"Lead Time\">";
    $textOvr = SetTextOvr($Err_D1RQDT);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Required Date</span></td> ";
    print "\n <td class=\"inputdate\"><input type=\"text\" name=\"reqDate\" id=\"reqDate\" value=\"" . rtrim($row[D1RQDT]) . "\" size=\"6\" maxlength=\"6\">";
    print "\n                         <a href=\"javascript:calWindow('reqDate');\"> $calendarImage</a>";
    if ($row[D1POEC] != "N" || $maintenanceCode == "N") {
        print "\n <a href=\"{$homeURL}{$phpPath}CalcRequiredDate.php{$genericVarBase}&amp;docName=Chg&amp;fldName=reqDate&amp;vendor={$hdrRow[H1VEND]}&amp;item=" . trim($row[D1ITEM]) . "&amp;whs={$row[D1OVWH]}\" onclick=\"CalcDateWindow(this.href,'calc_win'); return false;\">&nbsp; $applyLeadTime</a> ";
    }
    print "\n  </td></tr> ";
    DspErrMsg($Err_D1RQDT);

    $fieldDesc = RetValue("UMUOM='$row[D1SUOM]'", "HDUOM", "UMUMLD");
    $textOvr = SetTextOvr($Err_D1SUOM);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Stocking U/M</span></td> ";
    print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"suom\" value=\"" . rtrim($row['D1SUOM']) . "\" size=\"3\" maxlength=\"3\">";
    print "\n                             <a href=\"{$homeURL}{$phpPath}UnitOfMeasureSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=suom&amp;fldDesc=buomDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
    print "\n     <span class=\"dspdesc\" id=\"suomDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_D1SUOM);

    $req = ($maintenanceCode == 'N') ? $reqFieldChar : '';
    $fieldDesc = RetValue("UMUOM='$row[D1BUOM]'", "HDUOM", "UMUMLD");
    $textOvr = SetTextOvr($Err_D1BUOM);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Purchasing U/M</span></td> ";
    print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"buom\" value=\"" . rtrim($row['D1BUOM']) . "\" size=\"3\" maxlength=\"3\">";
    print "\n                             <a href=\"{$homeURL}{$phpPath}UnitOfMeasureSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=buom&amp;fldDesc=buomDesc\" onclick=\"$searchWinVar\">$req $searchImage</a> ";
    print "\n     <span class=\"dspdesc\" id=\"buomDesc\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_D1BUOM);

    if ($row[D1POEC] == 'N' && $row[D1PCPB] == 0) {
        $row[D1PCPB] = 1;
    }
    Build_Fld_Entry("Pieces Per Stocking U/M", "piecesPer", "inputnmbr", "", "D1PCPB", $row[D1PCPB], $Err_D1PCPB, "13", "13", "", "", "");

    if ($row[D1POEC] != "N" || $maintenanceCode == "N" || $maintenanceCode == "A") {
        $fieldDesc = RetValue("FLTYPE='OEENTCODE' and FLVALU='{$row[D1POEC]}'", "SYFLAG", "FLDESC");
        $textOvr = SetTextOvr($Err_D1POEC);
        print "\n <tr><td class=\"dsphdr\"><span $textOvr>Entry Code</span></td> ";
        print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"entryCode\" value=\"" . $row [D1POEC] . "\" size=\"3\" maxlength=\"1\">";
        print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}}&amp;docName=Chg&amp;fldName=entryCode&amp;fldDesc=entryCodedesc&amp;flagType=OEENTCODE&amp;flagSrchHdr=Entry Code\" onclick=\"$searchWinVar\">$searchImage</a> ";
        print "\n     <span class=\"dspdesc\" id=\"entryCodedesc\">$fieldDesc</span></td>";
        print "\n </tr> ";
        DspErrMsg($Err_D1POEC);
    } else {
        $fieldDesc = RetValue("FLTYPE='OEENTCODE' and FLVALU='{$row[D1POEC]}'", "SYFLAG", "FLDESC");
        Build_DspFld('Entry Code', trim($fieldDesc), $row[D1POEC], 'A');
        print "\n  <tr><td><input type=\"hidden\" name=\"entryCode\" value=\"{$row[D1POEC]}\"></td></tr> ";
    }

    $fieldDesc = RetValue("PCPCLS='$row[D1PCLS]'", "HDPCLS", "PCPCDS");
    $textOvr = SetTextOvr($Err_D1PCLS);
    print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Product Class</span></td> ";
    print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"prodClass\" value=\"" . trim($row['D1PCLS']) . "\" size=\"6\" maxlength=\"4\"> ";
    print "\n                              <a href=\"{$homeURL}{$phpPath}ProdClassSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=prodClass&amp;fldDesc=prodClassDesc\" onclick=\"$searchWinVar\">$req $searchImage</a> ";
    print "\n                              <span class=\"dspdesc\" id=\"prodClassDesc\">$fieldDesc</span></td>";
    print "\n  </tr> ";
    DspErrMsg($Err_D1PCLS);

    if ($poDetailDate1 != '') {
        Build_Fld_Entry($poDetailDate1, "ud1Date", "inputdate", "Date", "D1OPDT", $row[D1OPDT], $Err_D1OPDT, "6", "6", "", "", "");
    }
    if ($poDetailDate2 != '') {
        Build_Fld_Entry($poDetailDate2, "ud2Date", "inputdate", "Date", "D1CPDT", $row[D1CPDT], $Err_D1CPDT, "6", "6", "", "", "");
    }
    if ($poDetailDate3 != '') {
        Build_Fld_Entry($poDetailDate3, "ud3Date", "inputdate", "Date", "D1LADT", $row[D1LADT], $Err_D1LADT, "6", "6", "", "", "");
    }

    Build_Fld_Entry("Discount Allowed", "discAllowed", "inputalph", "YORN", "D1DSCF", $row[D1DSCF], $Err_D1DSCF, "1", "1", "", "", "");
    Build_Fld_Entry("Backorder Allowed", "boAllowed", "inputalph", "YORN", "D1BOAL", $row[D1BOAL], $Err_D1BOAL, "1", "1", "", "", "");
    Build_Fld_Entry("Taxable", "taxable", "inputalph", "YORN", "D1TXCD", $row[D1TXCD], $Err_D1TXCD, "1", "1", "Y", "", "");
    Build_Fld_Entry("Vendor Item", "vendorItem", "inputalph", "", "D1VDSN", $row[D1VDSN], $Err_D1VDSN, "30", "30", "", "", "");
    Build_Fld_Entry("Reference Order", "refOrder", "inputalph", "", "D1RORD", $row[D1RORD], $Err_D1RORD, "20", "20", "", "", "");
    Build_Fld_Entry("Update Current Cost", "updCurCost", "inputalph", "YORN", "D1UCC", $row[D1UCC], $Err_D1UCC, "1", "1", "", "", "");
    Build_Fld_Entry("Eng Change Notice", "engChgNotice", "inputalph", "", "D1ECN", $row[D1ECN], $Err_D1ECN, "8", "8", "", "", "");

    print "\n </table> ";

    print "\n <script TYPE=\"text/javascript\">";
    print "\n document.Chg.$focusField.focus();";
    print "\n </script>";
    print "\n </form>";

    print "\n <table $contentTable>";
    if (($sec_01 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "Z")) || ($sec_02 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "C")) || ($maintenanceCode != "A" && $maintenanceCode != "C" && $maintenanceCode != "D" && $maintenanceCode != "Z")) {
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed </a>";
    }

    if ($noMenu == "Y") {
        print "\n <a href=\"javascript:opener.location.href=opener.location.href; javascript:window.close()\">$closeImageMed </a> ";
    } elseif ($backURL != "") {
        print "\n <a href=\"$backURL\">$cancelImageMed </a>";
    } else {
        print "\n <a href=\"javascript:history.back()\">$cancelImageMed </a>";
    }

    if ($sec_03 != "N" && $maintenanceCode == "C") {
        print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";
    }
	
	if ($userDefDtlCnt > 0) {
		$smU = "<img border=\"0\" src=\"{$homeURL}{$imagePath}smU.gif\" title=\"Update User-Defined\" alt=\"U\">";
		print "\n <a href=\"{$homeURL}{$phpPath}POUserDefinedMaintain.php{$scriptVarBase}&amp;udTable=POOUMD&amp;tag=MAINTAIN" . "&amp;fromPO=" . urlencode(trim($purchaseOrder)) .  "&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;fromLine=" . trim($lineNumber) . "&amp;fromItem=" . trim($row[D1ITEM]) . "&amp;fromItemDesc=" . urlencode(trim($row[D1IMDS])) . "\"  onclick = \"$inquiryWinVar\">$smU</a>";
	}

    $medIcon = "Y";
    require 'HelpPage.php';
    print "\n </td></tr></table>";
    print $hrTagAttr;
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    if ($purchaseOrder == 0) {
        require_once 'Trailer.php';
    }
    print "</body> </html>";
    exit();
}

if ($tag == "Edit_Data") {
    if ($_SESSION ['po_setHdr'] == 'Y') {
        $maintenanceCode = $_SESSION ['po_mncd'];
        if ($maintenanceCode == 'I') {
            $_POST['addItem'] = $_SESSION ['po_addItem'];
            $_POST['addQty'] = $_SESSION ['po_addQty'];
        } elseif ($maintenanceCode == 'M') {
            $_POST['addItem'] = $_SESSION ['po_item'];
            $_POST['addQty'] = $_SESSION ['po_orderQty'];
            $maintenanceCode = 'I';
            $stmtSQL = " Delete From HDMPLM Where PLPLT={$_SESSION['po_plant']} and PLTYPE='{$_SESSION ['po_type']}' 
                         and PLPN='{$_SESSION ['po_item']}' and PLDAT='{$_SESSION ['po_dueDate']}' and 
                         PLSEQN={$_SESSION ['po_seqNbr']} ";
            $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
        }  else {
            $fromType = $_SESSION ['po_type'];
            $fromReqNbr= $_SESSION ['po_reqNbr'];
            $fromItem = $_SESSION ['po_item'];
        }
        unset ($_SESSION ['po_setHdr']);
    }

    if ($maintenanceCode == 'A') {
        $job = RetValue("PSTYPE='$fromType' and PSREQN='$fromReqNbr' and PSITEM='$fromItem'", "POSGPO", "PSJOB");
        if ($job > 0) {
            $user = RetValue("H1OCTL=$job", "POHDRW inner join SYUSER on H1USER=USUSER", "USDESC");
            $errorHdr = "Item " . trim($fromItemDesc) . ' [' . trim($fromItem) . "] has been selected by {$user}";
            print "<meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;errorHdr=" . urlencode(trim($errorHdr)) . "\"> ";
            exit();
        }
    }

    $edtVar = "";
    Concat_Field("@@octl", $orderControl);
    Concat_Field("@@popo", $purchaseOrder);
    Concat_Field("@@line", $lineNumber);
    if ($maintenanceCode == 'C' || $maintenanceCode == 'N' || ($maintenanceCode == 'A' && $purchaseOrder > 0)) {
        if ($_POST['updItem'] == 'Y') {
            Concat_Field("@@uitm", strtoupper($_POST['itemNumber']));
        }
        if ($maintenanceCode == 'N' || $maintenanceCode == 'A' || $purchaseOrder > 0) {
            Concat_Field("@@item", strtoupper($_POST['itemNumber']));
        } else {
            Concat_Field("@@type", $fromType);
            Concat_Field("@@reqn", $fromReqNbr);
            Concat_Field("@@item", $fromItem);
        }
        Concat_Field("@@imds", $_POST['itemDesc']);
        Concat_Field("@@whs@", $_POST['warehouse']);
        if (isset($_POST['entryCode']) && $_POST['entryCode'] == 'N' && trim($_POST['suom']) == '') {
            $_POST['suom'] = $_POST['buom'];
        }
        Concat_Field("@@suom", strtoupper($_POST['suom']));
        Concat_Field("@@buom", strtoupper($_POST['buom']));
        Concat_Field("@@pcpb", $_POST['piecesPer']);
        Concat_Field("@@pcls", strtoupper($_POST['prodClass']));
        if (isset($_POST['entryCode'])) {
            Concat_Field("@@poec", strtoupper($_POST['entryCode']));
        }
        Concat_Field("@@rqdt", $_POST['reqDate']);
        if ($poDetailDate1 != '') {
            Concat_Field("@@opdt", $_POST['ud1Date']);
        }
        if ($poDetailDate2 != '') {
            Concat_Field("@@cpdt", $_POST['ud2Date']);
        }
        if ($poDetailDate3 != '') {
            Concat_Field("@@ladt", $_POST['ud3Date']);
        }
        Concat_Field("@@cost", $_POST['cost']);
        Concat_Field("@@qty@", $_POST['qtyOrdered']);
        if (!isset($_POST['boAllowed'])) {
            $_POST['boAllowed'] = "N";
        }
        Concat_Field("@@boal", $_POST['boAllowed']);
        if (!isset($_POST['taxable'])) {
            $_POST['taxable'] = "N";
        }
        Concat_Field("@@txcd", $_POST['taxable']);
        if (!isset($_POST['discAllowed'])) {
            $_POST['discAllowed'] = "N";
        }
        Concat_Field("@@dscf", $_POST['discAllowed']);
        if (!isset($_POST['updCurCost'])) {
            $_POST['updCurCost'] = "N";
        }
        Concat_Field("@@ucc@", $_POST['updCurCost']);
        Concat_Field("@@vdsn", $_POST['vendorItem']);
        Concat_Field("@@rord", $_POST['refOrder']);
        Concat_Field("@@ecn@", $_POST['engChgNotice']);
    } elseif ($maintenanceCode == 'I') {
        Concat_Field("@@item", strtoupper($_POST['addItem']));
        Concat_Field("@@qty@", $_POST['addQty']);
    } else {
        Concat_Field("@@type", $fromType);
        Concat_Field("@@reqn", $fromReqNbr);
        Concat_Field("@@item", $fromItem);
    }
    if ($_SESSION ['po_reqFilter'] == 'Y') {
        Concat_Field("@@freq", 'Y');
    }
    $edtVar .= "}{";

    $poBusy = null;
    if ($purchaseOrder > 0) {
        $busy = RetValue("POPO=$purchaseOrder", "POPOMS", "POBUSY");
        if ($busy == 'B') {
            $poBusy = 'Y';
            $errFound = 'Y';
        } else {
            $fromBQty = RetValue("PDPO=$purchaseOrder and PDPOL#=$lineNumber and PDPORL=0", "POPOMD", "PDQTOR");
        }
    }

    if ($errFound == "") {
        // echo $edtVar;
        // exit();
        $returnValue = Maintain_Edit("HPODTL_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
        $maintenanceCode = $returnValue['maintenanceCode'];
        $errFound = $returnValue['errFound'];
        $edtVar = $returnValue['edtVar'];
        $errVar = $returnValue['errVar'];
    }

    if ($errFound == "") {
        if ($purchaseOrder > 0) {
            $blkLine = RetValue("PDPO=$purchaseOrder and PDPOL#=$lineNumber and PDPORL=0", "POPOMD", "PDPOLT");
            if ($blkLine == 'B') {
                $toBQty = RetValue("PDPO=$purchaseOrder and PDPOL#=$lineNumber and PDPORL=0", "POPOMD", "PDQTOR");
                if ($fromBQty != $toBQty) {
                    print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}POBlanketMaintain.php{$scriptVarBase}&amp;noMenu=Y\">";
                    exit();
                }
            }
            print "\n <script TYPE=\"text/javascript\">";
            print "\n     opener.location.href=opener.location.href;";
            print "\n     window.close();";
            print "\n </script>";
        } elseif ($maintenanceCode == 'D' && $fromItem == '') {
            if (isset($_SESSION["returnURL"])) {
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$_SESSION["returnURL"]}\">";
            } else {
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}&amp;tag=QSEARCH&amp;startRow=1\"> ";
            }
        } else {
            if ($orderControl == '') {
                $orderControl = Decat_Field("@@octl", $edtVar);
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;startRow=" . urlencode(trim($startRow)) . "\"> ";
            } else {
                $filter = ($_SESSION ['po_reqFilter'] == 'Y') ? '&amp;selItems=Y' : '';
                print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}CreatePOItems.php{$genericVarBase}&amp;orderControl=" . urlencode(trim($orderControl)) . $filter . "&amp;startRow=1\"> ";
            }
        }
    } elseif (is_null($poBusy) && ($maintenanceCode == "A" && $purchaseOrder == 0) || $maintenanceCode == "I") {
        $errorHdr = DecatErr_Field("@@hdr@", "header");
        $errorItem = DecatErr_Field("@@item", "item");
        print "<meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;errorHdr=" . urlencode(trim($errorHdr)) . "&amp;errorItem=" . urlencode(trim($errorItem)) . "\"> ";
    } else {
        EdtVarErr($profileHandle, $edtVar);
        ErrVarErr($profileHandle, $errVar);
        $busyErr = (!is_null($poBusy)) ? '&amp;poBusy=Y' : '';
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;fromType={$fromType}&amp;fromReqNbr={$fromReqNbr}&amp;fromItem={$fromItem}&amp;startRow=" . urlencode(trim($startRow)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "{$busyErr}&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
    }
}

?>
