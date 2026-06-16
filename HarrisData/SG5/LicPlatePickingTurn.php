<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once "InventoryControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title = "&nbsp; Picking Turnaround";
$scriptName = "LicPlatePickingTurn.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$maintainVar = "{$genericVarBase}&amp;tag=MAINTAIN";
$maintenanceCode = "";

if ($tag == "Edit_Data") {
	$errMsg == "";
	$status = RetValue ( "IHTURN={$_POST['turnaround']}", "OEORHP", "coalesce(IHSTAT,'')" );
	if ($status == '') {
		$errMsg = "Turnaround not found";
	} elseif ($status != 'L' && $status != 'P') {
		$errMsg = "Turnaround Status of " . $status . " is not valid for confirmation";
	}
	
	if ($errMsg == "") {
		if ($_POST ['option'] == "V") {
			$stmtSQL = " Select * From IVLPALV01 ";
			$stmtSQL .= " Where IHTURN={$_POST['turnaround']} ";
			require 'stmtSQLEnd.php';
			$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
			$row = db2_fetch_assoc ( $sqlResult );
			print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=476&amp;nMenu=Y&amp;fKey1=IHTURN&amp;fVal1=" . urlencode ( trim ( $_POST['turnaround'] ) ) . "&amp;fKey2=IHORD&amp;fVal2=" . urlencode ( trim ( $row ['IHORD'] ) ) . "&amp;fKey3=STNAME&amp;fVal3=" . urlencode ( trim ( $row ['STNAME'] ) ) . "\"> ";
		} else {
			print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}LicPlatePickingConfirm.php{$maintainVar}&amp;turnaround=" . urlencode ( trim ($_POST['turnaround'] ) ) . "\"> ";
		}
		exit ();
	} else {
		$turnaround = $_POST ['turnaround'];
	}
}

require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
print "\n function validate(chgForm) {";
print "\n   if (document.Chg.turnaround.value ==\"\") ";
print "\n     {alert(\"Turnaround must be entered\"); return false;} ";
print "\n   return true;";
print "\n }";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";

print "\n <h1>{$page_title}</h1>";
require_once 'ConfMessageDisplay.php';

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
print "\n <div style=\"clear:both\"></div><br>";
print "\n <table $contentTable>";

print "\n <tr><td class=\"dsphdr\"><span $textOvr>&nbsp; &nbsp; Turnaround</span></td>";
print "\n  <td class=\"inputnmbr\"><input type=\"text\" name=\"turnaround\" value=\"$turnaround\" size=\"15\" maxlength=\"9\"></td></tr>";
if ($errMsg != '')
	DspErrMsg ( $errMsg );

print "\n  <tr><td><input type=\"hidden\" id=\"option\" name=\"option\" value=\"\"></td></tr>";

print "\n </table></form>";

print "\n <table $contentTable>";
print "\n <tr><td>";
print "\n <div><ul class=\"toolbarLP\">";
print "\n <li class=\"optionLP\"><a onClick=\"javascript:document.getElementById('option').value='A';\" href=\"javascript:check(document.Chg)\">Accept</a></li>";
print "\n <li class=\"optionLP\"><a onClick=\"javascript:document.getElementById('option').value='V';\" href=\"javascript:check(document.Chg)\">View</a></li>";
print "\n </ul></div>";
print "\n </td></tr>";
print "\n </table>";

print "\n <script TYPE=\"text/javascript\">";
print "\n document.Chg.turnaround.focus();";
print "\n </script>";
print "</body> </html>";
?>
