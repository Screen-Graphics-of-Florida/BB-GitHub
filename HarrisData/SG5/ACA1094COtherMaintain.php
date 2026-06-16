<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromACA1094CID = $_GET ['fromACA1094CID'];
$fromC3ID = $_GET ['fromC3ID'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$stmtSQL = " Select C4BNAM1 From HRACC4 Where C4CACHID=$fromACA1094CID ";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$aca1094CRow = db2_fetch_assoc ( $sqlResult );

$page_title = "ACA 1094C Cache Other ALE Members Maintenance";
$scriptName = "ACA1094COtherMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromC3ID=" . urlencode ( trim ( $fromC3ID ) ) . "&amp;fromACA1094CID=" . urlencode ( trim ( $fromACA1094CID ) ) . "&amp;fKey1=C3CAID94&amp;fVal1=" . urlencode ( trim ( $fromACA1094CID ) ) . "&amp;fDsc1=" . urlencode ( trim ( $aca1094CRow ['C4BNAM1'] ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=486";
require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth == "F") {
	require_once 'ProgSecurityError.php';
	exit ();
}

if ($tag == "Edit_Data") {
	$errMsg = NULL;
	if ($maintenanceCode == "Z") {
		$maintenanceCode = "A";
	}
	
	if ($maintenanceCode == "A") {
	} elseif ($maintenanceCode == "C") {
		$lastUpdatedCurrent = RetValue ( "C3CACHID={$fromC3ID}", "HRACC3", "C3LUPD" );
		if ($lastUpdatedCurrent != $_POST ['C3LUPD']) {
			$errMsg = "Row has been previously updated";
		}
	} elseif ($maintenanceCode == "D") {
		$desc = RetValue ( "C3CACHID={$fromC3ID}", "HRACC3", "C3BNAM1" );
		
		$stmtSQL = " Delete From HRACC3 Where C3CACHID=" . $fromC3ID;
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $desc, "", "", "", "", "" );
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	}
	
	if (! $errMsg && $maintenanceCode != "D") {
		if (preg_match_all ( "/[0-9]/", $_POST ['C3PYTIN'] ) < 9) {
			$Err_C4PYTIN = "Payer TIN must be at least 9 digits";
			$errMsg = 'Please correct all errors';
		}
		$_POST ['C3TINRT'] = strtoupper ( $_POST ['C3TINRT'] );
		if ($_POST ['C3TINRT'] != 'BUSINESS_TIN') {
			$Err_C3TINRT = "TIN Request Type must be BUSINESS_TIN";
			$errMsg = 'Please correct all errors';
		}
	}
	
	if (! $errMsg) {
		if ($maintenanceCode == "A") {
			$stmtSQL = " Insert Into HRACC3 (C3CAID94,C3BNAM1,C3BNAM2,C3PYTIN,C3BNAMC,C3TINRT) ";
			$stmtSQL .= " Values ({$fromACA1094CID},'{$_POST ['C3BNAM1']}','{$_POST ['C3BNAM2']}','{$_POST ['C3PYTIN']}','{$_POST ['C3BNAMC']}','{$_POST ['C3TINRT']}') ";
		} else {
			$stmtSQL = " Update HRACC3 set C3BNAM1='{$_POST ['C3BNAM1']}',C3BNAM2='{$_POST ['C3BNAM2']}',C3PYTIN='{$_POST ['C3PYTIN']}',C3BNAMC='{$_POST ['C3BNAMC']}',C3TINRT='{$_POST ['C3TINRT']}'";
			$stmtSQL .= " Where C3CACHID={$_POST['C3CACHID']} ";
		}
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		// If row not added, set identity column and try again
		if (! $status && $maintenanceCode == "A") {
			Check_Identity_Column ( 'HRACC3', 'C3CACHID', $stmtSQL );
		}
		
		$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $_POST ['C3BNAM1'], "", "", "", "", "" );
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
	}
}

if ($tag == "MAINTAIN" || $errMsg) {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.C3BNAM1.value ==\"\" || ";
	print "\n     document.Chg.C3PYTIN.value ==\"\" || ";
	print "\n     document.Chg.C3TINRT.value ==\"\" ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n return true;";
	print "\n }";
	
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";
	
	require_once ($genericHead);
	print "\n </head>";
	
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ACA1094CMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL = " Select *  From HRACC3 Where C3CACHID=$fromC3ID ";
	}
	require 'stmtSQLEnd.php';
	
	// Program Option Security
	$prog_OPT = pgmOptSecurity ( $profileHandle, $dataBaseID, $programName );
	$sec_01 = $prog_OPT ['sec_01'];
	$sec_02 = $prog_OPT ['sec_02'];
	$sec_03 = $prog_OPT ['sec_03'];
	$sec_04 = $prog_OPT ['sec_04'];
	require_once 'MaintainTop.php';
	
	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';
	if ($errMsg != '') {
		print "\n <span class=\"error\" $textOvr>$errMsg</span>";
	}
	
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc ( $sqlResult );
	
	if ($maintenanceCode == "A" || $errMsg) {
		$row [C3BNAM1] = $_POST ['C3BNAM1'];
		$row [C3BNAM2] = $_POST ['C3BNAM2'];
		$row [C3PYTIN] = $_POST ['C3PYTIN'];
		$row [C3BNAMC] = $_POST ['C3BNAMC'];
		$row [C3TINRT] = $_POST ['C3TINRT'];
		$focusField = "C3BNAM1";
	} else {
		$focusField = "C3BNAM1";
	}
	
	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode ( trim ( $maintenanceCode ) ) . "\">";
	print "\n <table $contentTable>";
	print "\n <tr><td><input type=\"hidden\" name=\"C3LUPD\" value=\"" . rtrim ( $row ['C3LUPD'] ) . "\"></td></tr> ";
	print "\n <tr><td><input type=\"hidden\" name=\"C3CACHID\" value=\"" . rtrim ( $row ['C3CACHID'] ) . "\"></td></tr> ";
	
	Build_Fld_Entry ( "Business Name 1", "C3BNAM1", "inputalph", "", "C3BNAM1", $row [C3BNAM1], $Err_C3BNAM1, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Business Name 2", "C3BNAM2", "inputalph", "", "C3BNAM2", $row [C3BNAM2], $Err_C3BNAM2, "64", "64", "", "", "" );
	Build_Fld_Entry ( "Payer TIN", "C3PYTIN", "inputalph", "", "C3PYTIN", $row [C3PYTIN], $Err_C3PYTIN, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Business Name Control", "C3BNAMC", "inputalph", "", "C3BNAMC", $row [C3BNAMC], $Err_C3BNAMC, "64", "64", "", "", "" );
	Build_Fld_Entry ( "TIN Request Type", "C3TINRT", "inputalph", "", "C3TINRT", $row [C3TINRT], $Err_C3TINRT, "64", "64", "Y", "", "" );
	
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
	exit ();
}

?>