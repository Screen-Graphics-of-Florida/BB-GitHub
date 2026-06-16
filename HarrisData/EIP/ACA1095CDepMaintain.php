<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromACA1095DID = $_GET ['fromACA1095DID'];
$fromACA1095CID = $_GET ['fromACA1095CID'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$stmtSQL = " Select * From HRACC5 Where C5CACHID=$fromACA1095CID ";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$aca1095CRow = db2_fetch_assoc ( $sqlResult );

$page_title = "ACA 1095C Cache Dependent Maintenance";
$scriptName = "ACA1095CDepMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromACA1095DID=" . urlencode ( trim ( $fromACA1095DID ) ) . "&amp;fromACA1095CID=" . urlencode ( trim ( $fromACA1095CID ) ) . "&amp;fKey1=C2CAID5&amp;fVal1=" . urlencode ( trim ( $aca1095CRow ['C5CACHID'] ) ) . "&amp;fDsc1=" . urlencode ( trim ( $aca1095CRow ['C5ELNAM'] ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=483";
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
		$lastUpdatedCurrent = RetValue ( "C2DCID5={$fromACA1095DID}", "HRACC2", "C2LUPD" );
		if ($lastUpdatedCurrent != $_POST ['C2LUPD']) {
			$errMsg = "Row has been previously updated";
		}
	} elseif ($maintenanceCode == "D") {
		$desc = RetValue ( "C2DCID5={$fromACA1095DID}", "HRACC2", "C2DLNAM" );
		
		$stmtSQL = " Delete From HRACC2 Where C2DCID5=" . $fromACA1095DID;
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $desc, "", "", "", "", "" );
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	}
	
	if (! $errMsg && $maintenanceCode != "D") {
		$_POST ['C2TINRT'] = strtoupper ( $_POST ['C2TINRT'] );
		if ($_POST ['C2DSSOC'] != '') {
			if (preg_match_all ( "/[0-9]/", $_POST ['C2DSSOC'] ) < 9) {
				$Err_C2DSSOC = "Social Security Number must be 9 digits";
				$errMsg = 'Please correct all errors';
			}
			if ($_POST ['C2TINRT'] != 'INDIVIDUAL_TIN') {
				$Err_C2TINRT = "TIN Request Type must be INDIVIDUAL_TIN";
				$errMsg = 'Please correct all errors';
			}
		}
	}
	
	if (! $errMsg) {
		while ( strlen ( $_POST ['C2DDOB'] ) < 7 ) {
			$_POST ['C2DDOB'] = "0{$_POST ['C2DDOB']}";
		}
		$iso_C2DDOB = ($_POST ['C2DDOB'] != '0000000') ? "'" . Reformat_Date_ISO ( $_POST ['C2DDOB'], "*MDY", "*ISO" ) . "'" : 'NULL';
		$C2CVCDA = ($_POST ['C2CVCDA'] == 'Y') ? 1 : 0;
		$C2CVCD01 = ($_POST ['C2CVCD01'] == 'Y') ? 1 : 0;
		$C2CVCD02 = ($_POST ['C2CVCD02'] == 'Y') ? 1 : 0;
		$C2CVCD03 = ($_POST ['C2CVCD03'] == 'Y') ? 1 : 0;
		$C2CVCD04 = ($_POST ['C2CVCD04'] == 'Y') ? 1 : 0;
		$C2CVCD05 = ($_POST ['C2CVCD05'] == 'Y') ? 1 : 0;
		$C2CVCD06 = ($_POST ['C2CVCD06'] == 'Y') ? 1 : 0;
		$C2CVCD07 = ($_POST ['C2CVCD07'] == 'Y') ? 1 : 0;
		$C2CVCD08 = ($_POST ['C2CVCD08'] == 'Y') ? 1 : 0;
		$C2CVCD09 = ($_POST ['C2CVCD09'] == 'Y') ? 1 : 0;
		$C2CVCD10 = ($_POST ['C2CVCD10'] == 'Y') ? 1 : 0;
		$C2CVCD11 = ($_POST ['C2CVCD11'] == 'Y') ? 1 : 0;
		$C2CVCD12 = ($_POST ['C2CVCD12'] == 'Y') ? 1 : 0;
		
		if ($maintenanceCode == "A") {
			$stmtSQL = " Insert Into HRACC2 
					    (C2CAID5,C2DFNAM,C2DMNAM,C2DLNAM,C2DSUFF,C2DSSOC,C2TINRT,C2DDOB,C2CVCDA,
					     C2CVCD01,C2CVCD02,C2CVCD03,C2CVCD04,C2CVCD05,C2CVCD06,C2CVCD07,
					     C2CVCD08,C2CVCD09,C2CVCD10,C2CVCD11,C2CVCD12) ";
			
			$stmtSQL .= " Values ({$fromACA1095CID},'{$_POST ['C2DFNAM']}','{$_POST ['C2DMNAM']}','{$_POST ['C2DLNAM']}','{$_POST ['C2DSUFF']}','{$_POST ['C2DSSOC']}','{$_POST ['C2TINRT']}',{$iso_C2DDOB},{$C2CVCDA},
								  {$C2CVCD01},{$C2CVCD02},{$C2CVCD03},{$C2CVCD04},{$C2CVCD05},{$C2CVCD06},{$C2CVCD07},
			                      {$C2CVCD08},{$C2CVCD09},{$C2CVCD10},{$C2CVCD11},{$C2CVCD12}) ";
		} else {
			$stmtSQL = " Update HRACC2 set C2DFNAM='{$_POST ['C2DFNAM']}',C2DMNAM='{$_POST ['C2DMNAM']}',C2DLNAM='{$_POST ['C2DLNAM']}',C2DSUFF='{$_POST ['C2DSUFF']}',C2DSSOC='{$_POST ['C2DSSOC']}',C2TINRT='{$_POST ['C2TINRT']}',C2DDOB={$iso_C2DDOB},C2CVCDA={$C2CVCDA},
			                     C2CVCD01={$C2CVCD01},C2CVCD02={$C2CVCD02},C2CVCD03={$C2CVCD03},C2CVCD04={$C2CVCD04},C2CVCD05={$C2CVCD05},C2CVCD06={$C2CVCD06},
			                     C2CVCD07={$C2CVCD07},C2CVCD08={$C2CVCD08},C2CVCD09={$C2CVCD09},C2CVCD10={$C2CVCD10},C2CVCD11={$C2CVCD11},C2CVCD12={$C2CVCD12} ";
			$stmtSQL .= " Where C2DCID5={$_POST['C2DCID5']} ";
		}
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		// If row not added, set identity column and try again
		if (! $status && $maintenanceCode == "A") {
			Check_Identity_Column ( 'HRACC2', 'C2DCID5', $stmtSQL );
		}
		
		$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $_POST ['C2DLNAM'], "", "", "", "", "" );
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
	print "\n if (document.Chg.C2DFNAM.value ==\"\" || ";
	print "\n     document.Chg.C2DLNAM.value ==\"\" ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editdate(document.Chg.C2DDOB)) ";
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
	$pageID = "ACA1095CMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL = " Select *  From HRACC2 Where C2DCID5=$fromACA1095DID ";
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
		$row [C2DFNAM] = $_POST ['C2DFNAM'];
		$row [C2DMNAM] = $_POST ['C2DMNAM'];
		$row [C2DLNAM] = $_POST ['C2DLNAM'];
		$row [C2DSUFF] = $_POST ['C2DSUFF'];
		$row [C2DSSOC] = $_POST ['C2DSSOC'];
		$row [C2TINRT] = $_POST ['C2TINRT'];
		$row [C2DDOB] = $_POST ['C2DDOB'];
		$row [C2CVCDA] = $_POST ['C2CVCDA'];
		$row [C2CVCD01] = $_POST ['C2CVCD01'];
		$row [C2CVCD02] = $_POST ['C2CVCD02'];
		$row [C2CVCD03] = $_POST ['C2CVCD03'];
		$row [C2CVCD04] = $_POST ['C2CVCD04'];
		$row [C2CVCD05] = $_POST ['C2CVCD05'];
		$row [C2CVCD06] = $_POST ['C2CVCD06'];
		$row [C2CVCD07] = $_POST ['C2CVCD07'];
		$row [C2CVCD08] = $_POST ['C2CVCD08'];
		$row [C2CVCD09] = $_POST ['C2CVCD09'];
		$row [C2CVCD10] = $_POST ['C2CVCD10'];
		$row [C2CVCD11] = $_POST ['C2CVCD11'];
		$row [C2CVCD12] = $_POST ['C2CVCD12'];
		$focusField = "C2DFNAM";
	} else {
		$row [C2DDOB] = substr ( $row [C2DDOB], 5, 2 ) . substr ( $row [C2DDOB], 8, 2 ) . substr ( $row [C2DDOB], 2, 2 );
		$row [C2CVCDA] = ($row [C2CVCDA] == 1) ? 'Y' : '';
		$row [C2CVCD01] = ($row [C2CVCD01] == 1) ? 'Y' : '';
		$row [C2CVCD02] = ($row [C2CVCD02] == 1) ? 'Y' : '';
		$row [C2CVCD03] = ($row [C2CVCD03] == 1) ? 'Y' : '';
		$row [C2CVCD04] = ($row [C2CVCD04] == 1) ? 'Y' : '';
		$row [C2CVCD05] = ($row [C2CVCD05] == 1) ? 'Y' : '';
		$row [C2CVCD06] = ($row [C2CVCD06] == 1) ? 'Y' : '';
		$row [C2CVCD07] = ($row [C2CVCD07] == 1) ? 'Y' : '';
		$row [C2CVCD08] = ($row [C2CVCD08] == 1) ? 'Y' : '';
		$row [C2CVCD09] = ($row [C2CVCD09] == 1) ? 'Y' : '';
		$row [C2CVCD10] = ($row [C2CVCD10] == 1) ? 'Y' : '';
		$row [C2CVCD11] = ($row [C2CVCD11] == 1) ? 'Y' : '';
		$row [C2CVCD12] = ($row [C2CVCD12] == 1) ? 'Y' : '';
		$focusField = "C2DFNAM";
	}
	
	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode ( trim ( $maintenanceCode ) ) . "\">";
	print "\n <table $contentTable>";
	print "\n <tr><td><input type=\"hidden\" name=\"C2LUPD\" value=\"" . rtrim ( $row ['C2LUPD'] ) . "\"></td></tr> ";
	print "\n <tr><td><input type=\"hidden\" name=\"C2DCID5\" value=\"" . rtrim ( $row ['C2DCID5'] ) . "\"></td></tr> ";
	
	Build_Fld_Entry ( "First Name", "C2DFNAM", "inputalph", "", "C2DFNAM", $row [C2DFNAM], $Err_C2DFNAM, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Middle Name", "C2DMNAM", "inputalph", "", "C2DMNAM", $row [C2DMNAM], $Err_C2DMNAM, "64", "64", "", "", "" );
	Build_Fld_Entry ( "Last Name", "C2DLNAM", "inputalph", "", "C2DLNAM", $row [C2DLNAM], $Err_C2DLNAM, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Suffix", "C2DSUFF", "inputalph", "", "C2DSUFF", $row [C2DSUFF], $Err_C2DSUFF, "64", "64", "", "", "" );
	Build_Fld_Entry ( "TIN Request Type", "C2TINRT", "inputalph", "", "C2TINRT", $row [C2TINRT], $Err_C2TINRT, "64", "64", "", "", "" );
	Build_Fld_Entry ( "Social Security Number", "C2DSSOC", "inputalph", "", "C2DSSOC", $row [C2DSSOC], $Err_C2DSSOC, "9", "9", "", "", "" );
	Build_Fld_Entry ( "Date of Birth", "C2DDOB", "inputdate", "Date", "C2DDOB", $row [C2DDOB], $Err_C2DDOB, "6", "6", "", "", "" );
	Build_Fld_Entry ( "Coverage All 12 Months", "C2CVCDA", "inputalph", "BY", "C2CVCDA", $row [C2CVCDA], $Err_C2CVCDA, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - January", "C2CVCD01", "inputalph", "BY", "C2CVCD01", $row [C2CVCD01], $Err_C2CVCD01, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - February", "C2CVCD02", "inputalph", "BY", "C2CVCD02", $row [C2CVCD02], $Err_C2CVCD02, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - March", "C2CVCD03", "inputalph", "BY", "C2CVCD03", $row [C2CVCD03], $Err_C2CVCD03, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - April", "C2CVCD04", "inputalph", "BY", "C2CVCD04", $row [C2CVCD04], $Err_C2CVCD04, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - May", "C2CVCD05", "inputalph", "BY", "C2CVCD05", $row [C2CVCD05], $Err_C2CVCD05, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - June", "C2CVCD06", "inputalph", "BY", "C2CVCD06", $row [C2CVCD06], $Err_C2CVCD06, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - July", "C2CVCD07", "inputalph", "BY", "C2CVCD07", $row [C2CVCD07], $Err_C2CVCD07, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - August", "C2CVCD08", "inputalph", "BY", "C2CVCD08", $row [C2CVCD08], $Err_C2CVCD08, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - September", "C2CVCD09", "inputalph", "BY", "C2CVCD09", $row [C2CVCD09], $Err_C2CVCD09, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - October", "C2CVCD10", "inputalph", "BY", "C2CVCD10", $row [C2CVCD10], $Err_C2CVCD10, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - November", "C2CVCD11", "inputalph", "BY", "C2CVCD11", $row [C2CVCD11], $Err_C2CVCD11, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Coverage - December", "C2CVCD12", "inputalph", "BY", "C2CVCD12", $row [C2CVCD12], $Err_C2CVCD12, "1", "1", "", "", "" );
	
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