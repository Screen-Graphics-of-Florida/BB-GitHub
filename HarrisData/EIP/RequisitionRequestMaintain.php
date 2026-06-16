<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$newRow             = $_GET['newRow'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$copyReq      = (isset($_GET['copyReq'])) ? strtoupper($_GET['copyReq']) : '';
$copyItem     = (isset($_GET['copyItem'])) ? strtoupper($_GET['copyItem']) : '';

$fromReqNumber      = (isset($_GET['fromReqNumber'])) ? strtoupper($_GET['fromReqNumber']) : '';
$fromItemNumber     = (isset($_GET['fromItemNumber'])) ? strtoupper($_GET['fromItemNumber']) : '';
$readyForApproval   = (isset($_GET['ready'])) ? strtoupper($_GET['ready']) : null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Requisition Request Maintenance";
$scriptName     = "RequisitionRequestMaintain.php";
$scriptVarBase  = "{$genericVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;fromReqNumber=" . urlencode(trim($fromReqNumber)) . "&amp;fromItemNumber=" . urlencode(trim($fromItemNumber));
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D&amp;fromReqNumber=" . urlencode(trim($fromReqNumber)) . "&amp;fromItemNumber=" . urlencode(trim($fromItemNumber));
$programName    = "HPORQR_W";
$attachFolder   = "Requisition";

$backURL=$_SESSION[$fromURL];
if ($backURL == "" || $maintenanceCode  == "D") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=502";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if (!is_null($readyForApproval)) {
    $ready = ($readyForApproval == "Y") ? 'Y' : '';
    $stmtSQL = " Update POREQR Set RQRFAP = '" . $ready . "' Where RQREQN='" . $fromReqNumber . "'";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $confMessage = "Confirm update of Ready For Approval for Requisition " . $fromReqNumber;
    print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
    exit();
}

// Program Option Security
$hporqr_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01=$hporqr_OPT['sec_01'];
$sec_02=$hporqr_OPT['sec_02'];
$sec_03=$hporqr_OPT['sec_03'];
$sec_04=$hporqr_OPT['sec_04'];
$accessCost=$hporqr_OPT['sec_06'];
$reqCost=$hporqr_OPT['sec_07'];
$reqProdClass=$hporqr_OPT['sec_08'];
$reqDesc=$hporqr_OPT['sec_09'];
$reqUDF1=$hporqr_OPT['sec_10'];
$reqUDF2=$hporqr_OPT['sec_11'];
$reqUDF3=$hporqr_OPT['sec_12'];
$reqUDF4=$hporqr_OPT['sec_13'];

if ($tag == "MAINTAIN" && ($newRow == "E" || ($maintenanceCode == "Z" || $maintenanceCode == "A" && ($fromReqNumber == '' || $fromItemNumber == '')))) {
    require_once ($docType);
    print "\n <html> <head>";
    require_once ($headInclude);
    $formName = "Chg";
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'Menu.js';

    require_once 'CheckEnterChg.php';
    require_once 'NumEdit.php';
    require_once 'UpperCase.php';

    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.itemNumber.value ==\"\" )";
    print "\n {alert(\"$reqFieldError\"); return false;} ";
    print "\n return true;";
    print "\n }";

    print "\n </script> \n";

    require_once ($genericHead);
    print "\n </head>";

    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "REQREQUESTMAINTAIN";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";

    require_once 'MaintainTop.php';
    require_once 'ConfMessageDisplay.php';
    print $hrTagAttr;
    require_once 'RequiredField.php';
    require_once 'ErrorDisplay.php';

    if ($errFound != "" || $newRow == "Y") {
        $edtVar=EdtVarErr($profileHandle, $edtVar);
        $errVar=ErrVarErr($profileHandle, $errVar);
        $Err_RQREQN=DecatErr_Field("@@reqn", "reqNumber");
        $Err_RQITEM=DecatErr_Field("@@item", "itemNumber");
		$fromReqNumber=Decat_Field("@@reqn", $edtVar);
		$fromItemNumber=Decat_Field("@@item", $edtVar);
    } else {
        $focusField = ($fromReqNumber != '') ? "itemNumber" : "reqNumber";
    }
    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
    print "\n <table $contentTable>";

    Build_Fld_Entry("Requisition Number","reqNumber","inputalph","","RQREQN",$fromReqNumber,$Err_RQREQN,"8","8","","","");
	$textOvr=SetTextOvr($Err_RQITEM);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Item Number</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"itemNumber\" value=\"" . $fromItemNumber . "\" size=\"15\" maxlength=\"15\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ItemSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=itemNumber&amp;fldDesc=itemDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"itemDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_RQITEM);
	print "\n <tr><td><input type=\"hidden\" name=\"newRow\" value=\"Y\"></td></tr>";
	print "\n </table> ";

    print "\n <script TYPE=\"text/javascript\">";
    print "\n document.Chg.$focusField.focus();";
    print "\n </script>";
    print "\n </form>";
    require_once 'MaintainBottom.php';
    print $hrTagAttr;
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    require_once 'Trailer.php';
    print "</body> </html>";
    exit;
}

elseif ($tag == "MAINTAIN") {

	$hdimstRow = array();
    $stmtSQL = " Select * From HDIMST Where IMITEM='$fromItemNumber' ";
    $sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
    $hdimstRow = db2_fetch_assoc ( $sqlResult );

    require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CheckEnterChg.php';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	require_once 'CalendarInclude.php';
	require_once 'DateEdit.php';

	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.reqNumber.value ==\"\" || ";
    if ($reqDesc == 'Y') {
        print "\n     document.Chg.desc.value ==\"\" || ";
    }
    if ($reqUDF1 == 'Y' && $poRequisitionAlpha1 != "") {
        print "\n     document.Chg.userAlpha1.value ==\"\" || ";
    }
    if ($reqUDF2 == 'Y' && $poRequisitionAlpha2 != "") {
        print "\n     document.Chg.userAlpha2.value ==\"\" || ";
    }
    if ($reqUDF3 == 'Y' && $poRequisitionAlpha3 != "") {
        print "\n     document.Chg.userAlpha3.value ==\"\" || ";
    }
    if ($reqUDF4 == 'Y' && $poRequisitionAlpha4 != "") {
        print "\n     document.Chg.userAlpha4.value ==\"\" || ";
    }
	print "\n     document.Chg.quantity.value ==\"\" || ";
	if ($hdimstRow[IMIMDS] == '') {
    	print "\n     document.Chg.itemDesc.value ==\"\" || ";
    	print "\n     document.Chg.uom.value ==\"\" || ";
        if ($reqProdClass == 'Y') {
            print "\n     document.Chg.prodClass.value ==\"\" || ";
        }
	}
	print "\n     document.Chg.whsNumber.value ==\"\" || ";
	print "\n     document.Chg.reqDate.value ==\"\" )";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editZero(document.Chg.quantity, 13, 4) && ";
    if ($hdimstRow[IMIMDS] == '' || $accessCost == 'Y') {
        if ($reqCost == 'Y' && $hdimstRow[IMIMDS] == '') {
            print "\n     editZero(document.Chg.cost, 13, 5) && ";
        } else {
            print "\n     editNum(document.Chg.cost, 13, 5) && ";
        }
    }
	print "\n     editdate(document.Chg.reqDate)) ";
	print "\n return true;";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
    print "\n function clearFields() {";
    print "\n document.getElementById('desc').value = '';";
    if ($hdimstRow[IMIMDS] == '' || $accessCost == 'Y') {
        print "\n document.getElementById('cost').value = '';";
    }
    print "\n document.getElementById('quantity').value = '';";
    print "\n document.getElementById('reqDate').value = '';";
    print "\n document.getElementById('whsNumber').value = '';";
    print "\n document.getElementById('whsDesc').innerHTML = '';";
    print "\n document.getElementById('buyerNumber').value = '';";
    print "\n document.getElementById('buyerName').innerHTML = '';";
    print "\n document.getElementById('vendorNumber').value = '';";
    print "\n document.getElementById('vendorName').innerHTML = '';";
    print "\n document.getElementById('mfgOrder').value = '';";
    print "\n document.getElementById('rtgSeq').value = '';";
    if ($hdimstRow[IMIMDS] == '') {
        print "\n document.getElementById('itemDesc').value = '';";
        print "\n document.getElementById('uom').value = '';";
        print "\n document.getElementById('uomDesc').innerHTML = '';";
        print "\n document.getElementById('prodClass').value = '';";
        print "\n document.getElementById('prodClassDesc').innerHTML = '';";
        print "\n document.getElementById('vendorItem').value = '';";
    }
    if ($poRequisitionAlpha1 != "") {
        print "\n document.getElementById('userAlpha1').value = '';";
    }
    if ($poRequisitionAlpha2 != "") {
        print "\n document.getElementById('userAlpha2').value = '';";
    }
    if ($poRequisitionAlpha3 != "") {
        print "\n document.getElementById('userAlpha3').value = '';";
    }
    if ($poRequisitionAlpha4 != "") {
        print "\n document.getElementById('userAlpha4').value = '';";
    }
    print "\n document.getElementById('comments').value = '';";
    print "\n document.getElementById('extCost').value = '';";
    print "\n }";

    print "\n function costChanged(cst) {";
    print "\n var qty = document.getElementById('quantity').value;";
    print "\n var cst = cst.value;";
    print "\n var ext = Math.round((qty * cst) * 100) / 100;";
    print "\n document.getElementById('extCost').value = ext;";
    print "\n }";

    print "\n function qtyChanged(qty) {";
    print "\n var qty = qty.value;";
    print "\n var cst = document.getElementById('cost').value;";
    print "\n var ext = Math.round((qty * cst) * 100) / 100;";
    print "\n document.getElementById('extCost').value = ext;";
    print "\n }";


    print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "REQREQUESTMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";

	$stmtSQL= "";
    if ($maintenanceCode == "A" && $copyItem != "") {
        $stmtSQL .= " Select * ";
        $stmtSQL .= " From POREQR ";
        $stmtSQL .= " Where RQREQN='$copyReq' and RQITEM='$copyItem'";
    } elseif ($maintenanceCode == "A" && $fromItemNumber == "") {
        require_once 'AddRecordSQL.php';
    } else {
        $stmtSQL .= " Select * ";
        $stmtSQL .= " From POREQR ";
        $stmtSQL .= " Where RQREQN='$fromReqNumber' and RQITEM='$fromItemNumber'";
    }

	require 'stmtSQLEnd.php';

	print $hrTagAttr;
    print "\n <table $contentTable>";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td>";
    print "\n     <td class=\"toolbar\">";
    if (($sec_01 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "Z")) || ($sec_02 != "N" && $maintenanceCode == "C") || ($maintenanceCode != "A" && $maintenanceCode != "C" && $maintenanceCode != "D" && $maintenanceCode != "Z")) {
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
    }
    if ($sec_01 != "N") {
        $addPlusImage= (string) "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgSqAcceptAll.gif\" title=\"Update Row and add another\" alt=\"Add+\">";
        print "\n <a onClick=\"javascript:document.getElementById('addMore').value='Y';\" href=\"javascript:check(document.Chg)\">$addPlusImage</a>";
    }
    if ($backURL != "") {print "\n <a href=\"$backURL\">$cancelImageMed</a>";}
    else                {print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";}

    if ($sec_03 != "N" && $maintenanceCode == "C") {print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";}
    if ($maintenanceCode == "A") {
        print "\n <a href=\"#\" onClick=\"confirm('Ok to clear fields?') && clearFields()\">$clearFormFields</a>";
    }

    $attachVarKey=trim($fromReqNumber) . '_' . trim($fromItemNumber);
    $attachForDesc="";
    $attachPrg1= "POREQR Where RQREQN='$fromReqNumber'' and RQITEM='$fromItemNumber'' ";
    $attachForDesc = 'Requisition Request';
    $srch = array(" ", "/");
    $attachVarKey = str_replace($srch, "+", $attachVarKey);
    print "\n <a href=\"{$homeURL}{$phpPath}Attachment.PHP{$scriptVarBase}&amp;attachFolder=" . urlencode($attachFolder) . "&amp;attachForDesc=" . urlencode($attachForDesc) . "&amp;attachVarKey=" . urlencode($attachVarKey) . "&amp;userProfile=" . urlencode($userProfile) . "&amp;attachPrg1=" . urlencode($attachPrg1) . "&amp;attachPrg2=" . urlencode($attachPrg2) . "&amp;attachPrg3=" . urlencode($attachPrg3) . "&amp;attachPrg4=" . urlencode($attachPrg4) . "&amp;attachPrg5=" . urlencode($attachPrg5) . "&amp;noRefresh=Y" . "\" onclick=\"$selectionWinVar\">$attachImageSml</a> ";

    $medIcon= "Y";
    require 'HelpPage.php';
    print "\n </td></tr></table>";

    require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || ($maintenanceCode=="A" && $copyItem == '')) {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "desc";
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_RQREQN=DecatErr_Field("@@reqn", "reqNumber");
			$Err_RQDESC=DecatErr_Field("@@desc", "desc");
			$Err_RQITEM=DecatErr_Field("@@item", "itemNumber");
			$Err_RQIMDS=DecatErr_Field("@@imds", "itemDesc");
			$Err_RQVITM=DecatErr_Field("@@vitm", "vendorItem");
			$Err_RQCOST=DecatErr_Field("@@cost", "cost");
			$Err_RQBUOM=DecatErr_Field("@@uom@", "uom");
			$Err_RQSQOR=DecatErr_Field("@@sqor", "quantity");
			$Err_RQRQDT=DecatErr_Field("@@rqdt", "reqDate");
			$Err_RQWHS=DecatErr_Field("@@whs@", "whsNumber");
			$Err_RQBUYR=DecatErr_Field("@@buyr", "buyerNumber");
			$Err_RQVEND=DecatErr_Field("@@vend", "vendorNumber");
            $Err_RQSVSV=DecatErr_Field("@@svsv", "shipVia");
			$Err_RQMORD=DecatErr_Field("@@mord", "mfgOrder");
			$Err_RQRSEQ=DecatErr_Field("@@rseq", "rtgSeq");
			$Err_RQPCLS=DecatErr_Field("@@pcls", "prodClass");
            $Err_RQUDA1=DecatErr_Field("@@uda1", "userAlpha1");
            $Err_RQUDA2=DecatErr_Field("@@uda2", "userAlpha2");
            $Err_RQUDA3=DecatErr_Field("@@uda3", "userAlpha3");
            $Err_RQUDA4=DecatErr_Field("@@uda4", "userAlpha4");
			$errFound= "";
		}

		$row['RQREQN']=Decat_Field("@@reqn", $edtVar);
		$row['RQDESC']=Decat_Field("@@desc", $edtVar);
		$row['RQITEM']=Decat_Field("@@item", $edtVar);
		$row['RQIMDS']=Decat_Field("@@imds", $edtVar);
		$row['RQVITM']=Decat_Field("@@vitm", $edtVar);
		$row['RQCOST']=Decat_Field("@@cost", $edtVar);
		$row['RQBUOM']=Decat_Field("@@uom@", $edtVar);
		$row['RQSQOR']=Decat_Field("@@sqor", $edtVar);
		$row['RQRQDT']=Decat_Field("@@rqdt", $edtVar);
		$row['RQWHS'] =Decat_Field("@@whs@", $edtVar);
		$row['RQBUYR']=Decat_Field("@@buyr", $edtVar);
		$row['RQVEND']=Decat_Field("@@vend", $edtVar);
        $row['RQSVSV']=Decat_Field("@@svsv", $edtVar);
		$row['RQMORD']=Decat_Field("@@mord", $edtVar);
		$row['RQRSEQ']=Decat_Field("@@rseq", $edtVar);
		$row['RQPCLS']=Decat_Field("@@pcls", $edtVar);
        $row['RQUDA1']=Decat_Field("@@uda1", $edtVar);
        $row['RQUDA2']=Decat_Field("@@uda2", $edtVar);
        $row['RQUDA3']=Decat_Field("@@uda3", $edtVar);
        $row['RQUDA4']=Decat_Field("@@uda4", $edtVar);
		$comments = $_SESSION[$eID]['comments'];
		
	} else {
		$focusField= "desc";
		$row['RQRQDT']=DateInputFromCYMD($row['RQRQDT']);
	    $comments = Requisition_Comments($fromReqNumber, $fromItemNumber, 'INT');
	}

    if ($maintenanceCode == "A" && $copyItem != "") {
        $row['RQSQOR']= 0;
    }

	print "\n \n <form class=\"formClass\" METHOD=POST id='Chg' NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	print "\n <tr><td class=\"dsphdr\">Requisition Number</td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"reqNumber\" value=\"" . $fromReqNumber . "\">" . $fromReqNumber . "</td>";
    print "\n     <td class=\"inputalph\"><input type=\"hidden\" id=\"addMore\"  name=\"addMore\" value=\"\"></td>";
    print "\n     <td class=\"inputalph\"><input type=\"hidden\" id=\"curReqDate\"  name=\"curReqDate\" value=\"$row[RQRQDT]\"></td>";
	print "\n </tr> ";
	$descReq = ($reqDesc == 'Y') ? 'Y' : '';
	Build_Fld_Entry("Description","desc","inputalph","","RQDESC",$row[RQDESC],$Err_RQDESC,"80","100",$descReq,"","");
	
	print "\n <tr><td class=\"dsphdr\">Item Number</td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"itemNumber\" value=\"" . $fromItemNumber . "\">" . $fromItemNumber . "</td>";
	print "\n </tr> ";
	if ($hdimstRow[IMIMDS] <> '') {
    	print "\n <tr><td class=\"dsphdr\">Item Description</td> ";
    	print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"itemDesc\" value=\"" . $hdimstRow[IMIMDS] . "\">" . $hdimstRow[IMIMDS] . "</td>";
        print "\n     <td class=\"inputalph\"><input type=\"hidden\"   name=\"stockItem\" value=\"Y\"></td>";
    	print "\n </tr> ";

		$fieldDesc=RetValue("UMUOM='$hdimstRow[IMUOMS]'", "HDUOM", "UMUMLD");
    	print "\n <tr><td class=\"dsphdr\">Stocking Unit Of Measure</td> ";
    	print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"uom\" value=\"" . $hdimstRow[IMUOMS] . "\">" . $fieldDesc . '  [' . $hdimstRow[IMUOMS] . "]</td>";
    	print "\n </tr> ";
		$fieldDesc=RetValue("PCPCLS='$hdimstRow[IMPCLS]'", "HDPCLS", "PCPCDS");
    	print "\n <tr><td class=\"dsphdr\">Product Class</td> ";
    	print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"pcls\" value=\"" . $hdimstRow[IMPCLS] . "\">" . $fieldDesc . '  [' . $hdimstRow[IMPCLS] . "]</td>";
    	print "\n </tr> ";
	} else {
        $textOvr=SetTextOvr($Err_RQIMDS);
        print "\n <tr><td class=\"dsphdr\">Item Description</td> ";
        print "\n     <td class=\"inputnmbr\"><input type=\"inputalph\" name=\"itemDesc\" value=\"" . htmlspecialchars($row[RQIMDS]) . "\" size=\"30\" maxlength=\"30\"> "  . $reqFieldChar . "</td>";
        print "\n </tr> ";
        DspErrMsg($Err_RQIMDS);
	    Build_Fld_Entry("Vendor Item Number","vendorItem","inputalph","","RQVITM",$row[RQVITM],$Err_RQVITM,"30","30","","","");

    	$fieldDesc=RetValue("UMUOM='$row[RQBUOM]'", "HDUOM", "UMUMLD");
    	$textOvr=SetTextOvr($Err_RQBUOM);
    	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Purchasing Unit Of Measure</span></td> ";
    	print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"uom\" name=\"uom\" value=\"" . rtrim($row['RQBUOM']) . "\" size=\"3\" maxlength=\"3\">";
    	print "\n                             <a href=\"{$homeURL}{$phpPath}UnitOfMeasureSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=uom&amp;fldDesc=uomDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
    	print "\n     <span class=\"dspdesc\" id=\"uomDesc\">$fieldDesc</span></td>";
    	print "\n </tr> ";
    	DspErrMsg($Err_RQBUOM);

        $prodClassReq = ($reqProdClass == 'Y') ? $reqFieldChar : '';
    	$fieldDesc=RetValue("PCPCLS='$row[RQPCLS]'", "HDPCLS", "PCPCDS");
    	$textOvr=SetTextOvr($Err_RQPCLS);
    	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Product Class</span></td> ";
    	print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"prodClass\" name=\"prodClass\" value=\"" . rtrim($row['RQPCLS']) . "\" size=\"4\" maxlength=\"4\">";
    	print "\n                             <a href=\"{$homeURL}{$phpPath}ProdClassSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=prodClass&amp;fldDesc=prodClassDesc\" onclick=\"$searchWinVar\">$prodClassReq $searchImage</a> ";
    	print "\n     <span class=\"dspdesc\" id=\"prodClassDesc\">$fieldDesc</span></td>";
    	print "\n </tr> ";
    	DspErrMsg($Err_RQPCLS);
	}

    $textOvr=SetTextOvr($Err_RQSQOR);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Quantity</span></td> ";
    print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"quantity\" name=\"quantity\" value=\"" . rtrim($row['RQSQOR']) . "\" size=\"15\" maxlength=\"15\" onBlur=\"javascript:qtyChanged(this);\">";
    print "\n {$reqFieldChar}</tr> ";
    DspErrMsg($Err_RQSQOR);

    if ($accessCost == 'Y' || $hdimstRow[IMIMDS] == '') {
        $costReq = ($reqCost == 'Y' && $hdimstRow[IMIMDS] == '') ? $reqFieldChar : '';
        $textOvr = SetTextOvr($Err_RQCOST);
        print "\n <tr><td class=\"dsphdr\"><span $textOvr>Cost</span></td> ";
        print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"cost\" name=\"cost\" value=\"" . rtrim($row['RQCOST']) . "\" size=\"15\" maxlength=\"15\" onBlur=\"javascript:costChanged(this);\"> {$costReq}";
        print "\n </tr> ";
        DspErrMsg($Err_RQCOST);

        $extCost = $row[RQSQOR] * $row[RQCOST];
        $F_extCost = Format_Nbr($extCost, "2", $cstEditCode, "Y", "", "");
        print "\n <tr><td class=\"dsphdr\">Extended Cost</td> ";
        print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"extCost\" id=\"extCost\" value=\"$F_extCost\" size=\"15\" disabled></td>";
        print "\n </tr> ";
    }

    Build_Fld_Entry("Required Date","reqDate","inputdate","Date","RQRQDT",$row[RQRQDT],$Err_RQRQDT,"6","6","Y","","");

	$fieldDesc=RetValue("WHWHS=$row[RQWHS]", "HDWHSM", "WHWHNM");
	$textOvr=SetTextOvr($Err_RQWHS);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Number</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"whsNumber\" name=\"whsNumber\" value=\"" . rtrim($row['RQWHS']) . "\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=whsNumber&amp;fldDesc=whsDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"whsDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_RQWHS);

	$fieldDesc=RetValue("VMVEND=$row[RQVEND]", "HDVEND", "VMVNA1");
	$textOvr=SetTextOvr($Err_RQVEND);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Vendor Number</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"vendorNumber\" name=\"vendorNumber\" value=\"" . rtrim($row['RQVEND']) . "\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=vendorNumber&amp;fldDesc=vendorName\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"vendorName\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_RQVEND);

    $fieldDesc = RetValue("SVSVSV='$row[RQSVSV]'", "HDSHPV", "SVSVDS");
    $textOvr = SetTextOvr($Err_RQSVSV);
    print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Ship Via</span></td> ";
    print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"shipVia\" value=\"" . rtrim($row['RQSVSV']) . "\" size=\"7\" maxlength=\"2\"> ";
    print "\n                                     <a href=\"{$homeURL}{$phpPath}ShipViaSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=shipVia&amp;fldDesc=shipViaDesc\" onclick=\"$searchWinVar\"> $searchImage</a>";
    print "\n     <span class=\"dspdesc\" id=\"shipViaDesc\">$fieldDesc</span></td>";
    print "\n         </tr> ";
    DspErrMsg($Err_RQSVSV);

    if ($poRequisitionAlpha1 != "") {
        $udf1Req = ($reqUDF1 == 'Y') ? 'Y' : '';
        Build_Fld_Entry($poRequisitionAlpha1,"userAlpha1","inputalph","","RQUDA1",$row[RQUDA1],$Err_RQUDA1,"80","100",$udf1Req,"","");
    }
    if ($poRequisitionAlpha2 != "") {
        $udf2Req = ($reqUDF2 == 'Y') ? 'Y' : '';
        Build_Fld_Entry($poRequisitionAlpha2,"userAlpha2","inputalph","","RQUDA2",$row[RQUDA2],$Err_RQUDA2,"80","100",$udf2Req,"","");
    }
    if ($poRequisitionAlpha3 != "") {
        $udf3Req = ($reqUDF3 == 'Y') ? 'Y' : '';
        Build_Fld_Entry($poRequisitionAlpha3,"userAlpha3","inputalph","","RQUDA3",$row[RQUDA3],$Err_RQUDA3,"80","100",$udf3Req,"","");
    }
    if ($poRequisitionAlpha4 != "") {
        $udf4Req = ($reqUDF4 == 'Y') ? 'Y' : '';
        Build_Fld_Entry($poRequisitionAlpha4,"userAlpha4","inputalph","","RQUDA4",$row[RQUDA4],$Err_RQUDA4,"80","100",$udf4Req,"","");
    }

    $fieldDesc=RetValue("BMBUYR=$row[RQBUYR]", "HDBUYR", "BMBNA1");
    $textOvr=SetTextOvr($Err_RQBUYR);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Buyer Number</span></td> ";
    print "\n     <td class=\"inputnmbr\"><input type=\"text\" id=\"buyerNumber\" name=\"buyerNumber\" value=\"" . rtrim($row['RQBUYR']) . "\" size=\"7\" maxlength=\"7\">";
    print "\n                             <a href=\"{$homeURL}{$phpPath}BuyerSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=buyerNumber&amp;fldDesc=buyerName\" onclick=\"$searchWinVar\">$searchImage</a> ";
    print "\n     <span class=\"dspdesc\" id=\"buyerName\">$fieldDesc</span></td>";
    print "\n </tr> ";
    DspErrMsg($Err_RQBUYR);

	if ($HDPDRL > 0) {
	    Build_Fld_Entry("Mfg Order Number","mfgOrder","inputalph","","RQMORD",$row[RQMORD],$Err_RQMORD,"7","9","","","");
	    Build_Fld_Entry("Routing Sequence","rtgSeq","inputnmbr","","RQRSEQ",$row[RQRSEQ],$Err_RQRSEQ,"3","3","","","");
	}

	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Comments</span></td> ";
    print "\n     <td class=\"inputcmt\">";
	if ($comments != '') {
	    print "\n     <textarea id=\"comments\" name=\"comments\" ROWS=20 COLS=60 WRAP=\"hard\">{$comments}</textarea>";
    } else {
        print "\n     <textarea id=\"comments\" name=\"comments\" ROWS=20 COLS=60 WRAP=\"hard\"></textarea>";
    }
	print "\n </td></tr> ";
	
	
	print "\n </table> ";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.$focusField.focus();";
	print "\n </script>";
	print "\n </form>";

    print "\n <table $contentTable>";
    print "\n     <td class=\"toolbar\">";
    if (($sec_01 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "Z")) || ($sec_02 != "N" && $maintenanceCode == "C") || ($maintenanceCode != "A" && $maintenanceCode != "C" && $maintenanceCode != "D" && $maintenanceCode != "Z")) {
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
    }
    if ($sec_01 != "N") {
        $addPlusImage= (string) "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgSqAcceptAll.gif\" title=\"Update Row and add another\" alt=\"Add+\">";
        print "\n <a onClick=\"javascript:document.getElementById('addMore').value='Y';\" href=\"javascript:check(document.Chg)\">$addPlusImage</a>";
    }
    if ($backURL != "") {print "\n <a href=\"$backURL\">$cancelImageMed</a>";}
    else                {print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";}

    if ($sec_03 != "N" && $maintenanceCode == "C") {print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";}
    if ($maintenanceCode == "A") {
        print "\n <a href=\"#\" onClick=\"confirm('Ok to clear fields?') && clearFields()\">$clearFormFields</a>";
    }
    print "\n <a href=\"{$homeURL}{$phpPath}Attachment.PHP{$scriptVarBase}&amp;attachFolder=" . urlencode($attachFolder) . "&amp;attachForDesc=" . urlencode($attachForDesc) . "&amp;attachVarKey=" . urlencode($attachVarKey) . "&amp;userProfile=" . urlencode($userProfile) . "&amp;attachPrg1=" . urlencode($attachPrg1) . "&amp;attachPrg2=" . urlencode($attachPrg2) . "&amp;attachPrg3=" . urlencode($attachPrg3) . "&amp;attachPrg4=" . urlencode($attachPrg4) . "&amp;attachPrg5=" . urlencode($attachPrg5) . "&amp;noRefresh=Y" . "\" onclick=\"$selectionWinVar\">$attachImageSml</a> ";
    $medIcon= "Y";
    require 'HelpPage.php';
    print "\n </td></tr></table>";
	print $hrTagAttr;
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
    $copyReq = '';
    $copyItem = '';
	if ($maintenanceCode == "Z") {
	    $maintenanceCode= "A";
	    $copyReq = $fromReqNumber;
	    $copyItem = $fromItemNumber;
	}

	if ($maintenanceCode == "D") {
    	$_POST[reqNumber]   = (isset($_GET['fromReqNumber'])) ? strtoupper($_GET['fromReqNumber']) : $_POST[reqNumber];
    	$_POST['itemNumber'] = (isset($_GET['fromItemNumber'])) ? strtoupper($_GET['fromItemNumber']) : $_POST['itemNumber'];
	}

	$newRow = (isset($_POST['newRow'])) ? $_POST['newRow'] : null;
	$comments = (isset($_POST['comments'])) ? $_POST['comments'] : '';
	$desc = (isset($_POST['desc'])) ? $_POST['desc'] : '';
	$itemDesc = (isset($_POST['itemDesc'])) ? $_POST['itemDesc'] : '';
	$vendorItem = (isset($_POST['vendorItem'])) ? $_POST['vendorItem'] : '';
	$pcls = (isset($_POST['prodClass'])) ? $_POST['prodClass'] : '';
	$uom = (isset($_POST['uom'])) ? $_POST['uom'] : '';
	$qty = (isset($_POST['quantity'])) ? $_POST['quantity'] : 0;
	$reqDate = (isset($_POST['reqDate'])) ? $_POST['reqDate'] : 0;
	$whs = (isset($_POST['whsNumber'])) ? $_POST['whsNumber'] : 0;
	$buyer = (isset($_POST['buyerNumber'])) ? $_POST['buyerNumber'] : 0;
	$vendor = (isset($_POST['vendorNumber'])) ? $_POST['vendorNumber'] : 0;
    $shipVia = (isset($_POST['shipVia'])) ? strtoupper($_POST['shipVia']) : '';
	$mfgOrder = (isset($_POST['mfgOrder'])) ? strtoupper($_POST['mfgOrder']) : '';
	$rtgSeq = (isset($_POST['rtgSeq'])) ? $_POST['rtgSeq'] : 0;
	$edtVar= "";
	Concat_Field("@@reqn", strtoupper($_POST['reqNumber']));
	Concat_Field("@@item", strtoupper($_POST['itemNumber']));
	Concat_Field("@@desc", $desc);
	Concat_Field("@@imds", $itemDesc);
	Concat_Field("@@vitm", $vendorItem);
	Concat_Field("@@uom@", strtoupper($uom));
	Concat_Field("@@pcls", strtoupper($pcls));
	if (isset($_POST['cost'])) {
	    Concat_Field("@@cost", $_POST['cost']);
	}
	Concat_Field("@@sqor", $qty);
	Concat_Field("@@rqdt", $reqDate);
	Concat_Field("@@whs@", $whs);
	Concat_Field("@@buyr", $buyer);
	Concat_Field("@@vend", $vendor);
    Concat_Field("@@svsv", $shipVia);
	Concat_Field("@@mord", $mfgOrder);
	Concat_Field("@@rseq", $rtgSeq);
    Concat_Field("@@uda1", strtoupper($_POST['userAlpha1']));
    Concat_Field("@@uda2", strtoupper($_POST['userAlpha2']));
    Concat_Field("@@uda3", strtoupper($_POST['userAlpha3']));
    Concat_Field("@@uda4", strtoupper($_POST['userAlpha4']));
    Concat_Field("@@acst", $accessCost);
	$edtVar .= "}{";

	if ($newRow == "Y") {
	    $maintenanceCode = "V";
	}

	// If Required Date changed, clear warning parm
    if ($wrnVar == "Y") {
        if ($reqDate == $_POST['curReqDate'] && ($_POST['stockItem'] != 'Y' || $accessCost != 'Y' || ($_POST['stockItem'] == 'Y' && $_POST['cost'] != '' && $accessCost == 'Y'))) {
            $wrnVar = 'N';
        } else {
            $wrnVar = '';
        }
    }
	$returnValue=Requisition_Edit("HPORQR_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar, $comments);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];
	$_SESSION[$eID]['comments'] = $comments;
	if ($maintenanceCode == "V") {
	    $maintenanceCode = "A";
	}

	if (is_null($newRow) && ($errFound == "" || $maintenanceCode == "D")) {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST[reqNumber], $_POST[itemNumber], "", "", "", "");
		} else {
			$Err_RQREQN=DecatErr_Field("@@reqn", "reqNumber");
			$confMessage=Format_ConfMsg_Desc("", $_POST[reqNumber], $_POST[itemNumber], "", "", "<br>$Err_RQREQN", "");
		}
		if ($_POST['addMore'] == 'Y') {
            $reqNumber=Decat_Field("@@reqn", $edtVar);
            print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;fromReqNumber=" . urlencode(trim($reqNumber)) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "&amp;maintenanceCode=A" . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
        } else {
            print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        }
	} else {
	    if ($errFound == "Y" && $newRow == "Y") {
	        $newRow = "E";
	    }
	    $reqNumber=Decat_Field("@@reqn", $edtVar);
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;copyReq=" . urlencode(trim($copyReq)) . "&amp;copyItem=" . urlencode(trim($copyItem)) . "&amp;fromReqNumber=" . urlencode(trim($reqNumber)) . "&amp;fromItemNumber=" . urlencode(trim($_POST[itemNumber])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;newRow=" . urlencode(trim($newRow)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

// Maintenance Edit
function Requisition_Edit($pgmName,$userProfile,$maintenanceCode,$errFound,$edtVar,$errVar,$wrnVar,$cmtVar) {
    global $pgmLibrary, $i5Connect;
    if (is_null($errFound )) $errFound="";
    if (is_null($edtVar ))   $edtVar="";
    if (is_null($errVar ))   $errVar="";
    if (is_null($wrnVar ))   $wrnVar="";
    if (is_null($cmtVar ))   $cmtVar="";

    $pgmCall = array(
        array("Name"=>"userProfile",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
        array("Name"=>"maintenanceCode", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
        array("Name"=>"errFound",        "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
        array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
        array("Name"=>"errVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
        array("Name"=>"wrnVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
        array("Name"=>"cmtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

    $pgm = i5_program_prepare("$pgmName", $pgmCall);
    if (!$pgm) {die("<br>Validate_Data ($pgmName) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

    $parmIn = array(
        "userProfile"    =>$userProfile,
        "maintenanceCode"=>$maintenanceCode,
        "errFound"       =>$errFound,
        "edtVar"         =>$edtVar,
        "errVar"         =>$errVar,
        "wrnVar"         =>$wrnVar,
        "cmtVar"         =>$cmtVar);

    $parmOut = array(
        "userProfile"    =>"userProfile",
        "maintenanceCode"=>"maintenanceCode",
        "errFound"       =>"errFound",
        "edtVar"         =>"edtVar",
        "errVar"         =>"errVar",
        "wrnVar"         =>"wrnVar",
        "cmtVar"         =>"cmtVar");

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {die("<br>Validate_Data ($pgmName) call errno=".i5_errno()." msg=".i5_errormsg());}

    $returnValue['userProfile']    =$userProfile;
    $returnValue['maintenanceCode']=$maintenanceCode;
    $returnValue['errFound']       =$errFound;
    $returnValue['edtVar']         =$edtVar;
    $returnValue['errVar']         =$errVar;
    $returnValue['wrnVar']         =$wrnVar;
    $returnValue['cmtVar']         =$cmtVar;
    return $returnValue;
}

// Check For Existence Of Vendor/Customer Item Comments
function Requisition_Comments ($reqNumber = null,$itemNumber = null,$documentType = null){
    global $pgmLibrary, $i5Connect;
    if (is_null($reqNumber))     {$reqNumber="";}
    if (is_null($itemNumber))    {$itemNumber="";}
    if (is_null($documentType))  {$documentType="INT";}
    $comments     ="";
    if (!$i5Connect) die("<br>Requisition_Comments Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

    $pgmCall = array(
        array("Name"=>"reqNumber"     , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"8"),
        array("Name"=>"itemNumber"    , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"15"),
        array("Name"=>"documentType"  , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"3"),
        array("Name"=>"comments"      , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR,   "Length"=>"32000"));

    $pgm = i5_program_prepare("HPORCM_P", $pgmCall);
    if (!$pgm) {die("<br>Requisition_Comments Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

    $parmIn = array(
        "reqNumber"     =>$reqNumber,
        "itemNumber"    =>$itemNumber,
        "documentType"  =>$documentType,
        "comments"      =>$comments
    );

    $parmOut = array(
        "comments"  =>"comments"
    );
    $ret = i5_program_call($pgm, $parmIn, $parmOut);
   
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {die("<br> Requisition_Comments Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

    return $comments;
}

?>