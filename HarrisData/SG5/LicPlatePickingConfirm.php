<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$turnaround = (isset ( $_GET ['turnaround'] )) ? $_GET ['turnaround'] : 0;
$licPlate = (isset ( $_GET ['defaultPlate'] )) ? $_GET ['defaultPlate'] : $_POST ['licPlate'];
$_POST ['option'] = ($_POST ['option'] == '') ? 'A' : $_POST ['option'];

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once "InventoryControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title = "&nbsp; Picking Confirmation";
$scriptName = "LicPlatePickingConfirm.php";
$scriptVarBase = "{$genericVarBase}&amp;turnaround=" . urlencode ( trim ( $turnaround ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$_SESSION [$retURL] = $baseURL;
$maintainVar = "{$genericVarBase}&amp;tag=MAINTAIN";
$maintenanceCode = "";

if ($tag == "Edit_Data") {
	$errMsg == "";
	if ($_POST ['option'] == 'A') {
		$licPlateID = RetValue ( "IHTURN={$turnaround} and LAID='{$licPlate}'", "IVLPALV02", "coalesce(LAID,'')" );
	} else {
		$licPlateID = RetValue ( "LHID='{$_POST['licPlate']}'", "IVLPHD", "coalesce(LHID,'')" );
	}
	if ($licPlateID == '') {
		$errMsg = "License Plate not found";
	} elseif ($_POST ['option'] == 'V') {
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=435&amp;nMenu=Y&amp;fKey1=LHID&amp;fVal1=" . urlencode ( trim ( $_POST ['licPlate'] ) ) . "\"> ";
		exit ();
	} elseif ($_POST ['option'] == 'A' || $licPlateID != '') {
		$stmtSQL = " Select * From IVLPALV02 ";
		$stmtSQL .= " Where LAID='{$_POST['licPlate']}' and IHTURN={$turnaround} ";
		require 'stmtSQLEnd.php';
		$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
		$values = NULL;
		while ( $allocRow = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
			if (! array_key_exists ( 'CONFIRM', $allocRow )) {
				$errMsg = "License Plate not allocated to this order";
				break;
			} elseif ($allocRow ['CONFIRM'] == 'Y') {
				$errMsg = "{$_POST['licPlate']} is already confirmed ";
				break;
			} else {
				$values .= ($values) ? ',' : 'Values';
				$values .= " ($turnaround,'{$_POST['licPlate']}',{$allocRow['LAORD']},{$allocRow['LALINE']},{$allocRow['LABLN']},'$userProfile') ";
			}
			$startRow ++;
		}
		if ($values) {
			$stmtSQL = " Insert Into IVLPPV (LPTURN,LPID,LPORD,LPORL,LPBLN,LPUSER) ";
			$stmtSQL .= $values;
			$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
			$errMsg = "Confirm {$_POST['licPlate']}";
		}
		$_POST ['licPlate'] = '';
	}
	
	if ($errMsg == "") {
		if ($_POST ['option'] == "V") {
			$orderNumber = RetValue ( "IHTURN={$_POST['licPlate']}", "IVLPALV02", "IHORD" );
			$shipToName = RetValue ( "IHTURN={$_POST['licPlate']}", "IVLPALV02", "STNAME" );
			print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=476&amp;nMenu=Y&amp;fKey1=IHTURN&amp;fVal1=" . urlencode ( trim ( $_POST ['licPlate'] ) ) . "&amp;fKey2=IHORD&amp;fVal2=" . $orderNumber . "&amp;fKey3=STNAME&amp;fVal3=" . $shipToName . "\"> ";
		} else {
			print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}LicPlateAllocation.php{$maintainVar}&amp;licPlate=" . urlencode ( trim ( $_POST ['licPlate'] ) ) . "&amp;maintenanceCode=" . urlencode ( trim ( $_POST ['option'] ) ) . "\"> ";
		}
		exit ();
	} else {
		$licPlate = $_POST ['licPlate'];
	}
}

require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
print "\n function validate(chgForm) {";
print "\n   if (document.Chg.licPlate.value ==\"\") ";
print "\n     {alert(\"License Plate must be entered\"); return false;} ";
print "\n   return true;";
print "\n }";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";

print "\n <h1>{$page_title}</h1>";
require_once 'ConfMessageDisplay.php';

$stmtSQL = " Select * From IVLPALV01 ";
$stmtSQL .= " Where IHTURN={$turnaround} ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$row = db2_fetch_assoc ( $sqlResult );

print "\n <div style=\"float:left\">";
print "\n <table $contentTable>";
print "\n <tr><td class=\"hdrtitl\">Turnaround</td><td class=\"hdrdata\">{$turnaround}</td></tr>";
print "\n <tr><td class=\"hdrtitl\">Ship-To</td><td class=\"hdrdata\">{$row['STNAME']}</td></tr>";
print "\n <tr><td class=\"hdrtitl\">Ship Via</td><td class=\"hdrdata\">{$row['SVSVDS']}</td></tr>";
print "\n <tr><td class=\"hdrtitl\">Order Number</td><td class=\"hdrdata\">{$row['IHORD']}</td>";
print "\n </table>";
print "\n </div>";

$stmtSQL = " Select * From IVLPALV01 ";
$stmtSQL .= " Where IHTURN={$turnaround} ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$row = db2_fetch_assoc ( $sqlResult );

if ($row ['ALOCNT'] > $row ['CNFCNT']) {
	$red = "style=\"background: red;\"";
} else {
	$red = '';
}

print "\n <div style=\"float:left;\"><ul>";
print "\n <li class=\"optionLP\"><a href={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=476&amp;nMenu=Y&amp;fKey1=IHTURN&amp;fVal1=" . urlencode ( trim ( $turnaround ) ) . "&amp;fKey2=IHORD&amp;fVal2=" . urlencode ( trim ( $row ['IHORD'] ) ) . "&amp;fKey3=STNAME&amp;fVal3=" . urlencode ( trim ( $row ['STNAME'] ) ) . ">Allocated</a></li><b>{$row['ALOCNT']}</b>";
print "\n <li style=\"display:block\">&nbsp;</li>";
print "\n <li class=\"optionLP\" $red ><a href={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=476&amp;nMenu=Y&amp;fKey1=IHTURN&amp;fVal1=" . urlencode ( trim ( $turnaround ) ) . "&amp;fKey2=IHORD&amp;fVal2=" . urlencode ( trim ( $row ['IHORD'] ) ) . "&amp;fKey3=STNAME&amp;fVal3=" . urlencode ( trim ( $row ['STNAME'] ) ) . ">Confirmed</a></li><b>{$row['CNFCNT']}</b>";
print "\n </ul></div>";

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
print "\n <div style=\"clear:both\"></div><br>";
print "\n <table $contentTable>";

print "\n <tr><td class=\"hdrtitl\"><span $textOvr>License Plate</span>&nbsp;</td>";
print "\n  <td class=\"inputnmbr\"><input type=\"text\" name=\"licPlate\" value=\"$licPlate\" size=\"25\" maxlength=\"100\"></td></tr>";
if ($errMsg != '')
	DspErrMsg ( $errMsg );

print "\n  <tr><td><input type=\"hidden\" id=\"option\" name=\"option\" value=\"\"></td></tr>";

print "\n </table></form>";

print "\n <table $contentTable>";
print "\n <tr><td>";
print "\n <div><ul class=\"toolbarLP\">";
print "\n <li class=\"optionLP\"><a onClick=\"javascript:document.getElementById('option').value='A';\" href=\"javascript:check(document.Chg)\">Accept</a></li>";
print "\n <li class=\"optionLP\"><a onClick=\"javascript:document.getElementById('option').value='V';\" href=\"javascript:check(document.Chg)\">View</a></li>";
print "\n <li class=\"optionLP\"><a href=\"{$homeURL}{$phpPath}LicPlatePickingTurn.php{$scriptVarBase}\">Turnaround</a></li>";
print "\n </ul></div>";
print "\n </td></tr>";
print "\n </table>";

print "\n <script TYPE=\"text/javascript\">";
print "\n document.Chg.licPlate.focus();";
print "\n </script>";
print "</body> </html>";
?>
