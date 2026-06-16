<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = (isset ( $_GET ['maintenanceCode'] )) ? $_GET ['maintenanceCode'] : 'C';
$errFound = $_GET ['errFound'];
$fID = $_GET ['fID'];
$fItem = $_GET ['fItem'];
$fLot = $_GET ['fLot'];
$fLoc = $_GET ['fLoc'];
$fSid = $_GET ['fSid'];
$fQty = $_GET ['fQty'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once "InventoryControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

if ($maintenanceCode == "P") {
	$page_title = "Partial Finalize";
	$fQtyDesc = "Quantity Remaining";
	$qtyDesc = "";
} else {
	$page_title = "Change Quantity";
	$fQtyDesc = "Current Quantity";
	$qtyDesc = "New";
}
$scriptName = "LicPlateChgQty.php";
$scriptVarBase = "{$genericVarBase}&amp;fID=" . urlencode ( trim ( $fID ) ) . "&amp;fItem=" . urlencode ( trim ( $fItem ) ) . "&amp;fLot=" . urlencode ( trim ( $fLot ) ) . "&amp;fLoc=" . urlencode ( trim ( $fLoc ) ) . "&amp;fSid=" . urlencode ( trim ( $fSid ) ) . "&amp;fQty=" . urlencode ( trim ( $fQty ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

$backURL = $_SESSION [$fromURL];
if ($backURL == "" && $maintenanceCode == "P") {
	$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=444";
} elseif ($backURL == "") {
	$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=434";
}

if ($tag == "Edit_Data") {
	$edtVar = "";
	Concat_Field ( "@@plid", $fID );
	Concat_Field ( "@@item", $fItem );
	Concat_Field ( "@@lot@", $fLot );
	Concat_Field ( "@@qty@", $_POST ['newQty'] );
	$_POST ['stkLoc'] = strtoupper ( $_POST ['stkLoc'] );
	Concat_Field ( "@@sloc", $_POST ['stkLoc'] );
	Concat_Field ( "@@stkl", $CISTKL );
	Concat_Field ( "@@floc", $fLoc );
	Concat_Field ( "@@fsid", $fSid );
	Concat_Field ( "@@fqty", $fQty );
	Concat_Field ( "@@whs@", $_POST ['whsNumber'] );
	$rsnc = (isset ( $_POST ['reasonCode'] )) ? $_POST ['reasonCode'] : '';
	Concat_Field ( "@@rsnc", strtoupper ($rsnc) );
	$rsnd = (isset ( $_POST ['reasonDesc'] )) ? $_POST ['reasonDesc'] : '';
	Concat_Field ( "@@rsnd", $rsnd );
	
	$edtVar .= "}{";
	
	$returnValue = Maintain_Edit ( "HIVLPM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar );
	$maintenanceCode = $returnValue ['maintenanceCode'];
	$errFound = $returnValue ['errFound'];
	$edtVar = $returnValue ['edtVar'];
	$errVar = $returnValue ['errVar'];
	
	if ($errFound == "") {
		if ($maintenanceCode == "P") {
			$confMessage = "Confirm Update of License Plate " . $fID;
		} else {
			$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, "License Plate", "$fID", "", "", "", "" );
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
print "\n   if (document.Chg.newQty.value ==\"\" $allocVld";
print "\n   ) {alert(\"$reqFieldError\"); return false;} ";

$allocQty = 0;
if ($licPlateAllocation == 'Y') {
	$allocQty = RetValue ( "LAID='$fID' and LAITEM='$fItem' and trim(LALOTN)='$fLot' and LASID=$fSid", "IVLPAL", "sum(coalesce(LAQTY,0))" );
	if ($allocQty == '') {
		$allocQty = 0;
	}
	if ($allocQty) {
		print "\n   if (document.Chg.newQty.value < $allocQty)
	                {alert(\"Quantity cannot be less than Quantity Allocated\"); return false;} ";
	}
}
if ($maintenanceCode == "P") {
	print "\n   if (document.Chg.newQty.value > $fQty)  
	               {alert(\"Quantity cannot be greater than Quantity Remaining\"); return false;} ";
	print "\n   if (editZero(document.Chg.newQty, 9, 4) ) ";
} else {
	print "\n   if (editNum(document.Chg.newQty, 9, 4) ) ";
}
print "\n return true;";
print "\n }";

print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
$stmtSQL = "";
$stmtSQL .= " Select * ";
$stmtSQL .= " From IVLPHDV01 ";
$stmtSQL .= " Where LHID='$fID' and LDITEM='$fItem' and trim(LDLOT)='$fLot' ";
require 'stmtSQLEnd.php';

$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$row = db2_fetch_assoc ( $sqlResult );

if ($errFound != "") {
	$focusField = "";
	$Err_LDQTY = DecatErr_Field ( "@@qty@", "newQty" );
	$Err_LHID = DecatErr_Field ( "@@plid", "plateID" );
	$Err_SLLOC = DecatErr_Field ( "@@sloc", "stkLoc" );
	$Err_LHWHS = DecatErr_Field ( "@@whs@", "whsNumber" );
	$Err_RSNC = DecatErr_Field ( "@@rsnc", "reasonCode" );
	$Err_RSND = DecatErr_Field ( "@@rsnd", "reasonDesc" );
	$errFound = "";
	
	$row ['LHID'] = Decat_Field ( "@@plid", $edtVar );
	$row ['LDITEM'] = Decat_Field ( "@@item", $edtVar );
	$row ['SLLOC'] = Decat_Field ( "@@sloc", $edtVar );
	$row ['LHWHS'] = Decat_Field ( "@@whs@", $edtVar );
	$row ['RSNC'] = Decat_Field ( "@@rsnc", $edtVar );
	$row ['RSND'] = Decat_Field ( "@@rsnd", $edtVar );
	$newQty = Decat_Field ( "@@qty@", $edtVar );
} else {
	$newQty = "";
	$focusField = "newQty";
	$row ['RSNC'] = "";
	$row ['RSND'] = "";
}
print "\n <h1>$page_title</h1> ";

if ($licPlateAllocation != 'Y' || ($licPlateAllocation == 'Y' && $licPlateChgQtyIfAlloc == 'Y')) {
	require_once 'RequiredField.php';
}
print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode ( trim ( $maintenanceCode ) ) . "\" onSubmit=\"return false;\">";
print "\n <table $contentTable>";
print "\n <tr><td class=\"dsphdr\">License Plate</td><td class=\"dspalph\"><input type=\"hidden\" name=\"plateID\" value=\"{$row[LHID]}\">{$row[LHID]}</td></tr>";
if ($CISTKL == "Y" && $maintenanceCode != "P") {
	print "\n <tr><td class=\"dsphdr\">Location</td><td class=\"dspalph\"><input type=\"hidden\" name=\"stkLoc\" value=\"{$row[SLLOC]}\">{$row[SLLOC]}</td></tr>";
}
print "\n <tr><td class=\"dsphdr\">Item Number</td><td class=\"dspalph\"><input type=\"hidden\" name=\"item\" value=\"{$row[LDITEM]}\">$row[LDITEM] &nbsp; $row[IMIMDS]</td>";
if ($maintenanceCode != "P") {
	print "\n <td class=\"dsphdr\">&nbsp; Whs<td class=\"dspalph\"><input type=\"hidden\" name=\"whsNumber\" value=\"{$row[LHWHS]}\">$row[LHWHS]</td>";
}
print "\n </tr>";
if ($CILTUS == "Y" && $fLot != "") {
	print "\n <tr><td class=\"dsphdr\">Lot Number</td><td class=\"dspalph\"><input type=\"hidden\" name=\"item\" value=\"{$row[LDLOT]}\">$row[LDLOT]</td></tr>";
}
if ($maintenanceCode != "P") {
	$F_LDQTY = Format_Nbr ( $row ['LDQTY'], "{$qtyNbrDec}", ($qtyEditCode), "", "", "" );
	print "\n <tr><td class=\"dsphdr\">Current Quantity</td><td class=\"dspalph\">{$F_LDQTY}</td></tr>";
}
if ($licPlateAllocation != 'Y' || ($licPlateAllocation == 'Y' && ($licPlateChgQtyIfAlloc == 'Y' || $allocQty == 0))) {
	$textOvr = SetTextOvr ( $Err_LDQTY );
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>$qtyDesc Quantity</span></td> ";
	Build_Fld_Entry ( "$qtyDesc Quantity", "newQty", "inputnmbr", "", "LDQTY", $newQty, $Err_LDQTY, "15", "15", "Y", "", "Y" );
}
if ($maintenanceCode == "C") {
	Build_Fld_Entry ( "Reason Code", "reasonCode", "inputalph", "", "RSNC", $row ['RSNC'], $Err_RSNC, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Description", "reasonDesc", "inputalph", "", "RSND", $row ['RSND'], $Err_RSND, "25", "25", "", "", "" );
}
if ($maintenanceCode == "P") {
	$f_fQty = Format_Nbr ( $fQty, ($qtyNbrDec), ($qtyEditCode), "", "", "" );
	print "\n <td class=\"dsphdr\">&nbsp; Quantity Remaining<td class=\"dspalph\">$f_fQty</td>";
}
print "\n </tr>";
DspErrMsg ( $Err_LDQTY );

if ($CISTKL == "Y" && $maintenanceCode == "P") {
	Build_Fld_Entry ( "Location", "stkLoc", "inputalph", "", "SLLOC", $row [SLLOC], $Err_SLLOC, "20", "15", "Y", "", "" );
}
if ($maintenanceCode == "P") {
	Build_Fld_Entry ( "Warehouse", "whsNumber", "inputnmbr", "", "LHWHS", $row [LHWHS], $Err_LHWHS, "3", "3", "Y", "", "" );
}
print "\n </table> ";

if ($licPlateAllocation == 'Y' && $allocQty) {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select * ";
	$fileSQL .= " IVLPAL ";
	$selectSQL="LAID='$fID' and LAITEM='$fItem' and trim(LALOTN)='$fLot' and LASID=$fSid ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	if ($licPlateChgQtyIfAlloc != 'Y') {
		print "\n <div><h3>Quantity cannot be changed when Quantity Allocated exists</h3></div>";
	}
	print "\n <p><table $contentTable> ";
	print "\n <tr><th class=\"colhdr\">Order<br>Number</th>
	          <th class=\"colhdr\">Line</th>
	          <th class=\"colhdr\">Rel</th>
		      <th class=\"colhdr\">Quantity<br>Allocated</th><tr>";
	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		require  'SetRowClass.php';
		$F_allocQty = Format_Nbr ( $row[LAQTY], "{$qtyNbrDec}", ($qtyEditCode), "", "", "" );
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[LAORD]</td>";
		print "\n     <td class=\"colnmbr\">$row[LALINE]</td>";
		print "\n     <td class=\"colnmbr\">$row[LABLN]</td>";
		print "\n     <td class=\"colnmbr\">$F_allocQty</td>";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	print "\n </table>";
}

print "\n <div>
            <ul class=\"toolbarLP\">";
if ($licPlateAllocation != 'Y' || ($licPlateAllocation == 'Y' && ($licPlateChgQtyIfAlloc == 'Y' || $allocQty == 0))) {
	print "\n     <li class=\"optionLP\"><a href=\"javascript:check(document.Chg)\">Accept</a></li>";
}
print "\n     <li class=\"optionLP\"><a href=\"$backURL\">Back</a></li>
            </ul>
          </div>";

print "\n <div style=\"clear:both\"></div>";

print "\n <script TYPE=\"text/javascript\">";
print "\n document.Chg.$focusField.focus();";
print "\n </script>";
print "\n </form>";
print "\n </td> </tr> </table>";

print "</body> </html>";
?>