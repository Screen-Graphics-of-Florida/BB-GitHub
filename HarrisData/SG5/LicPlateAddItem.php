<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound = $_GET ['errFound'];
$fPO = $_GET ['fPO'];
$fLine = $_GET ['fLine'];
$fRel = $_GET ['fRel'];
$fLot = $_GET ['fLot'];
$fItem = $_GET ['fItem'];
$fWhs = $_GET ['fWhs'];
$fLoc = $_GET ['fLoc'];
$fSid = $_GET ['fSid'];
$fQty = $_GET ['fQty'];
if ($fPO > 0) {
	$maintenanceCode = "A";
} else {
	$maintenanceCode = "I";
}
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once "InventoryControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "POControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title = "Add Item";
$scriptName = "LicPlateAddItem.php";
$scriptVarBase = "{$genericVarBase}&amp;fPO=" . urlencode ( trim ( $fPO ) ) . "&amp;fLine=" . urlencode ( trim ( $fLine ) ) . "&amp;fRel=" . urlencode ( trim ( $fRel ) ) . "&amp;fLot=" . urlencode ( trim ( $fLot ) ) . "&amp;fItem=" . urlencode ( trim ( $fItem ) ) . "&amp;fWhs=" . urlencode ( trim ( $fWhs ) ) . "&amp;fLoc=" . urlencode ( trim ( $fLoc ) ) . "&amp;fSid=" . urlencode ( trim ( $fSid ) ) . "&amp;fQty=" . urlencode ( trim ( $fQty ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

$backURL = $_SESSION [$fromURL];
if ($backURL == "") {
	$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=434";
}

if ($tag == "Clear") {
	$_SESSION ['plateID'] = "";
	$_SESSION ['stkLoc'] = "";
	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}\"> ";
	exit ();
}

if ($tag == "Edit_Data") {
	$edtVar = "";
	Concat_Field ( "@@po@@", $fPO );
	Concat_Field ( "@@line", $fLine );
	Concat_Field ( "@@rel@", $fRel );
	Concat_Field ( "@@item", $fItem );
	Concat_Field ( "@@whs@", $fWhs );
	Concat_Field ( "@@lot@", $fLot );
	Concat_Field ( "@@qty@", $_POST ['addQty'] );
	Concat_Field ( "@@plid", $_POST ['plateID'] );
	$_POST ['stkLoc'] = strtoupper ( $_POST ['stkLoc'] );
	Concat_Field ( "@@sloc", $_POST ['stkLoc'] );
	Concat_Field ( "@@stkl", $CISTKL );
	Concat_Field ( "@@floc", $fLoc );
	Concat_Field ( "@@fsid", $fSid );
	Concat_Field ( "@@fqty", $fQty );
	$edtVar .= "}{";
	
	$returnValue = Maintain_Edit ( "HIVLPM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar );
	$maintenanceCode = $returnValue ['maintenanceCode'];
	$errFound = $returnValue ['errFound'];
	$edtVar = $returnValue ['edtVar'];
	$errVar = $returnValue ['errVar'];
	
	if ($errFound == "") {
		if ($maintenanceCode == "I") {
			$confMessage = "Confirm Add";
		} else {
			$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, "P/O", "$fPO", "", "", "", "" );
		}
		if ($_POST ['moreItems'] == "Y") {
			if ($maintenanceCode == "I") {
				$moreURL="{$homeURL}{$phpPath}LicPlateAdd.php{$scriptVarBase}&amp;tblID=444&amp;pagID=0&amp;firstTime=Y&amp;fLoc={$_POST['stkLoc']}&amp;itemNumber={$fItem}&amp;licPlate={$_POST['plateID']}";
				print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$moreURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
				exit ();
			}
			$_SESSION ['plateID'] = $_POST ['plateID'];
			$_SESSION ['stkLoc'] = $_POST ['stkLoc'];
		} else {
			$_SESSION ['plateID'] = "";
			$_SESSION ['stkLoc'] = "";
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	}
}

require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
require_once 'NumEdit.php';

print "\n function validate(chgForm) {";
print "\n   if (document.Chg.addQty.value ==\"\" ";
print "\n    || document.Chg.plateID.value ==\"\" ";
if ($CISTKL == "Y") {
	print "\n  || document.Chg.stkLoc.value ==\"\" ";
}
print "\n   ) {alert(\"$reqFieldError\"); return false;} ";

print "\n   if (document.Chg.addQty.value > $fQty)";
print "\n      {alert(\"Quantity cannot be greater than Quantity Remaining\"); return false;} ";
print "\n   if (editZero(document.Chg.addQty, 9, 4) ) ";
print "\n return true;";
print "\n }";

print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
if ($fPO > 0) {
	$stmtSQL = "";
	$stmtSQL .= " Select * ";
	$stmtSQL .= " From POPOMDV01 ";
	$stmtSQL .= " Where PDPO=$fPO and PDPOL=$fLine and PDPORL=$fRel and trim(LTLOT)='$fLot' ";
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc ( $sqlResult );
} else {
	$row ['LTLRCP'] = "";
	$row [PDITEM] = $fItem;
	$row [PDIMDS] = RetValue ( "IMITEM='$fItem'", "HDIMST", "IMIMDS" );
	$row [PDOVWH] = $fWhs;
}

if ($errFound != "") {
	$focusField = "";
	$edtVar = EdtVarErr ( $profileHandle, $edtVar );
	$errVar = ErrVarErr ( $profileHandle, $errVar );
	
	$Err_PDPO = DecatErr_Field ( "@@po@@", "poNumber" );
	$Err_LTLRCP = DecatErr_Field ( "@@qty@", "addQty" );
	$Err_LHID = DecatErr_Field ( "@@plid", "plateID" );
	$Err_LHSLID = DecatErr_Field ( "@@sloc", "stkLoc" );
	$errFound = "";
	
	$row ['LTLRCP'] = Decat_Field ( "@@qty@", $edtVar );
	$row ['LHID'] = Decat_Field ( "@@plid", $edtVar );
	$row ['LHSLID'] = Decat_Field ( "@@sloc", $edtVar );
} else {
	$focusField = "plateID";
	$row ['LHSLID'] = $fLoc;
	if ($CODFTO == "N" || $maintenanceCode == "I") {
		$focusField = "addQty";
		$row ['LTLRCP'] = "";
	}
}
print "\n <h1>$page_title</h1> ";
require_once 'RequiredField.php';

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode ( trim ( $maintenanceCode ) ) . "\" onSubmit=\"return false;\">";
print "\n <table $contentTable>";
if ($Err_PDPO != "") {
	print "\n <tr><td class=\"error\">$Err_PDPO</td></tr>";
}
print "\n <tr><td class=\"dsphdr\">Item Number</td><td class=\"dspalph\">$row[PDITEM] &nbsp; $row[PDIMDS]</td><td class=\"dsphdr\">&nbsp; Whs<td class=\"dspalph\">$row[PDOVWH]</td>";
print "\n     <td><input type=\"hidden\" name=\"moreItems\" value=\"\"></td></tr>";
if ($CILTUS == "Y" && $fLot != "") {
	print "\n <tr><td class=\"dsphdr\">Lot Number</td><td class=\"dspalph\">$fLot</td></tr>";
}

$textOvr = SetTextOvr ( $Err_LTLRCP );
print "\n <tr><td class=\"dsphdr\"><span $textOvr>Quantity</span></td> ";
Build_Fld_Entry ( "Quantity", "addQty", "inputnmbr", "", "LTLRCP", $row [LTLRCP], $Err_LTLRCP, "15", "15", "Y", "", "Y" );
$f_fQty = Format_Nbr ( $fQty, ($qtyNbrDec), ($qtyEditCode), "", "", "" );
print "\n <td class=\"dsphdr\">&nbsp; Quantity Remaining<td class=\"dspalph\">$f_fQty</td></tr>";
DspErrMsg ( $Err_LTLRCP );

if (isset ( $_SESSION ['plateID'] ) && $_SESSION ['plateID'] != "") {
	print "\n <tr><td class=\"dsphdr\">License Plate</td><td class=\"dspalph\"><input type=\"hidden\" name=\"plateID\" value=\"{$_SESSION['plateID']}\">{$_SESSION['plateID']}</td></tr>";
	if ($CISTKL == "Y") {
		print "\n <tr><td class=\"dsphdr\">Location</td><td class=\"dspalph\"><input type=\"hidden\" name=\"stkLoc\" value=\"{$_SESSION['stkLoc']}\">{$_SESSION['stkLoc']}</td></tr>";
	}
} else {
	Build_Fld_Entry ( "License Plate", "plateID", "inputalph", "", "LHID", $row [LHID], $Err_LHID, "50", "100", "Y", "", "" );
	if ($CISTKL == "Y") {
		Build_Fld_Entry ( "Location", "stkLoc", "inputalph", "", "LHSLID", $row [LHSLID], $Err_LHSLID, "20", "15", "Y", "", "" );
	}
}
print "\n </table> ";
print "\n <div>
            <ul class=\"toolbarLP\">
              <li class=\"optionLP\"><a href=\"javascript:check(document.Chg)\">Accept</a></li>
              <li class=\"optionLP\"><a onclick=\"document.Chg.moreItems.value='Y';\" href=\"javascript:check(document.Chg)\">Accept/More</a></li>";
if (isset ( $_SESSION ['plateID'] ) && $_SESSION ['plateID'] != "") {
	print "\n <li class=\"optionLP\"><a href=\"$scriptVarBase&amp;tag=Clear\">Back</a></li>";
} else {
	print "\n <li class=\"optionLP\"><a href=\"$backURL\">Back</a></li>";
}
print "\n </ul> </div>";

print "\n <div style=\"clear:both\"></div>";

print "\n <script TYPE=\"text/javascript\">";
print "\n document.Chg.$focusField.focus();";
print "\n </script>";
print "\n </form>";
print "\n </td> </tr> </table>";
print "</body> </html>";
?>