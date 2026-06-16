<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromACAEINID = $_GET ['fromACAEINID'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "ACA EIN Maintenance";
$scriptName = "ACAEINMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromACAEINID=" . urlencode ( trim ( $fromACAEINID ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=479";

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
	
	if ($maintenanceCode == "C") {
		$lastUpdatedCurrent = RetValue ( "EIEINID={$fromACAEINID}", "HRACAE", "EILUPD" );
		if ($lastUpdatedCurrent != $_POST ['lastUpdated']) {
			$errMsg = "Row has been previously updated";
		}
	}
	
	if ($maintenanceCode == "A") {
		$einExists = RetValue ( "EIEIN='{$_POST ['ein']}' and EITXYR={$_POST ['taxYear']}", "HRACAE", "EIEINID" );
		if ($einExists > 0) {
			$Err_EIEIN = "EIN / Tax Year combination already exist. " . $coFacEIN;
			$errMsg = "Please correct errors";
		}
	}
	
	if (! $errMsg && $maintenanceCode != "D") {
		// Get Federal Tax Defaults
		$stmtSQL = " Select * From PRYDFL Where YDCOMP={$_POST ['coNum']} and (YDFACL={$_POST ['facNum']} or YDFACL=0)
			         Order By YDFACL desc
			         Fetch First Row Only";
		$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		$coFacRow = db2_fetch_assoc ( $sqlResult );
		if ($coFacRow ['YDNAME'] == "") {
			$Err_EICO = "Invalid Default From Co/Fac Number";
			$errMsg = "Please correct errors";
		} else {
			if ($coFacRow ['YDEIN'] != $_POST ['ein']) {
				$Err_EIEIN = "EIN must match the Default From Co/Fac EIN " . $coFacEIN;
				$errMsg = "Please correct errors";
			}
		}
		
		$fieldDesc = RetValue ( "FLTYPE='ACAALE' and FLVALU='{$_POST ['EIALEST']}'", "SYFLAG", "FLDESC" );
		if ($fieldDesc == '') {
			$Err_EIALEST = "Invalid Applicable Large Employer Status";
			$errMsg = 'Please correct all errors';
		}
		
		$fieldDesc = RetValue ( "FLTYPE='ACAAFFTYPE' and FLVALU='{$_POST ['EIAFFTP']}'", "SYFLAG", "FLDESC" );
		if ($fieldDesc == '') {
			$Err_EIAFFTP = "Invalid Affordability Safe Harbor Type";
			$errMsg = 'Please correct all errors';
		}
	}
	
	if (! $errMsg) {
		$EIATRAN = ($_POST ['EIATRAN'] == 'Y') ? 1 : 0;
		$EIAAGRP = ($_POST ['EIAAGRP'] == 'Y') ? 1 : 0;
		$EIQOFFA = ($_POST ['EIQOFFA'] == 'Y') ? 1 : 0;
		$EIQOFF01 = ($_POST ['EIQOFF01'] == 'Y') ? 1 : 0;
		$EIQOFF02 = ($_POST ['EIQOFF02'] == 'Y') ? 1 : 0;
		$EIQOFF03 = ($_POST ['EIQOFF03'] == 'Y') ? 1 : 0;
		$EIQOFF04 = ($_POST ['EIQOFF04'] == 'Y') ? 1 : 0;
		$EIQOFF05 = ($_POST ['EIQOFF05'] == 'Y') ? 1 : 0;
		$EIQOFF06 = ($_POST ['EIQOFF06'] == 'Y') ? 1 : 0;
		$EIQOFF07 = ($_POST ['EIQOFF07'] == 'Y') ? 1 : 0;
		$EIQOFF08 = ($_POST ['EIQOFF08'] == 'Y') ? 1 : 0;
		$EIQOFF09 = ($_POST ['EIQOFF09'] == 'Y') ? 1 : 0;
		$EIQOFF10 = ($_POST ['EIQOFF10'] == 'Y') ? 1 : 0;
		$EIQOFF11 = ($_POST ['EIQOFF11'] == 'Y') ? 1 : 0;
		$EIQOFF12 = ($_POST ['EIQOFF12'] == 'Y') ? 1 : 0;
		$EIQOFFM = ($_POST ['EIQOFFM'] == 'Y') ? 1 : 0;
		// Section 4980H Transition Relief is only valid for 2015 or 2016
		if ($_POST ['taxYear'] != 2015 && $_POST ['taxYear'] != 2016) {
			$_POST ['EI498001'] = ' ';
			$_POST ['EI498002'] = ' ';
			$_POST ['EI498003'] = ' ';
			$_POST ['EI498004'] = ' ';
			$_POST ['EI498005'] = ' ';
			$_POST ['EI498006'] = ' ';
			$_POST ['EI498007'] = ' ';
			$_POST ['EI498008'] = ' ';
			$_POST ['EI498009'] = ' ';
			$_POST ['EI498010'] = ' ';
			$_POST ['EI498011'] = ' ';
			$_POST ['EI498012'] = ' ';
		}
		// Qualifying Offer Method Trans Relief is only valid for 2015
		$EIQOFFTR = ($_POST ['EIQOFFTR'] == 'Y' && $_POST ['taxYear'] == 2015) ? 1 : 0;
		// Section 4980H Transition Relief is only valid for 2015 or 2016
		$EI4980TR = ($_POST ['EI4980TR'] == 'Y' && ($_POST ['taxYear'] == 2015 || $_POST ['taxYear'] == 2016)) ? 1 : 0;
		$EIOFFM98 = ($_POST ['EIOFFM98'] == 'Y') ? 1 : 0;
		$_POST ['EIFPLAA'] = ($_POST ['EIFPLAA'] == '') ? 0 : $_POST ['EIFPLAA'];
		
		if ($maintenanceCode == "A") {
			$stmtSQL = " Insert Into HRACAE (EIEIN,EITXYR,EIDESC,EICO,EIFAC,EIALEST,EIAFFTP,EIFPLAA,EIATRAN,EIAAGRP,EIQOFFA,
					EIQOFF01,EIQOFF02,EIQOFF03,EIQOFF04,EIQOFF05,EIQOFF06,EIQOFF07,EIQOFF08,EIQOFF09,EIQOFF10,EIQOFF11,EIQOFF12,
					EI498001,EI498002,EI498003,EI498004,EI498005,EI498006,EI498007,EI498008,EI498009,EI498010,EI498011,EI498012,EIQOFFM,EIQOFFTR,EI4980TR,EIOFFM98,EIALTKY) ";
			$stmtSQL .= " Values ('{$_POST ['ein']}',{$_POST ['taxYear']},'{$_POST ['einDesc']}',{$_POST ['coNum']},{$_POST ['facNum']},'{$_POST ['EIALEST']}','{$_POST ['EIAFFTP']}',{$_POST ['EIFPLAA']},{$EIATRAN},{$EIAAGRP},{$EIQOFFA},
					{$EIQOFF01},{$EIQOFF02},{$EIQOFF03},{$EIQOFF04},{$EIQOFF05},{$EIQOFF06},{$EIQOFF07},{$EIQOFF08},{$EIQOFF09},{$EIQOFF10},{$EIQOFF11},{$EIQOFF12},
					'{$_POST['EI498001']}','{$_POST['EI498002']}','{$_POST['EI498003']}','{$_POST['EI498004']}','{$_POST['EI498005']}','{$_POST['EI498006']}','{$_POST['EI498007']}',
					'{$_POST['EI498008']}','{$_POST['EI498009']}','{$_POST['EI498010']}','{$_POST['EI498011']}','{$_POST['EI498012']}',{$EIQOFFM},{$EIQOFFTR},{$EI4980TR},{$EIOFFM98},'{$_POST['altKey']}') ";
		} elseif ($maintenanceCode == "D") {
			$_POST ['einDesc'] = RetValue ( "EIEINID={$fromACAEINID}", "HRACAE", "coalesce(EIDESC,'')" );
			$stmtSQL = " Delete From HRACAE Where EIEINID={$fromACAEINID} ";
		} else {
			$stmtSQL = " Update HRACAE set EIEIN='{$_POST ['ein']}',EITXYR={$_POST ['taxYear']},EIDESC='{$_POST ['einDesc']}',EICO={$_POST ['coNum']},EIFAC={$_POST ['facNum']},EIALEST='{$_POST ['EIALEST']}',EIAFFTP='{$_POST ['EIAFFTP']}',EIFPLAA={$_POST ['EIFPLAA']},EIATRAN={$EIATRAN},EIAAGRP={$EIAAGRP},EIQOFFA={$EIQOFFA},
					EIQOFF01={$EIQOFF01},EIQOFF02={$EIQOFF02},EIQOFF03={$EIQOFF03},EIQOFF04={$EIQOFF04},EIQOFF05={$EIQOFF05},EIQOFF06={$EIQOFF06},EIQOFF07={$EIQOFF07},EIQOFF08={$EIQOFF08},EIQOFF09={$EIQOFF09},EIQOFF10={$EIQOFF10},EIQOFF11={$EIQOFF11},EIQOFF12={$EIQOFF12},
					EI498001='{$_POST['EI498001']}',EI498002='{$_POST['EI498002']}',EI498003='{$_POST['EI498003']}',EI498004='{$_POST['EI498004']}',EI498005='{$_POST['EI498005']}',EI498006='{$_POST['EI498006']}',EI498007='{$_POST['EI498007']}',EI498008='{$_POST['EI498008']}',EI498009='{$_POST['EI498009']}',EI498010='{$_POST['EI498010']}',EI498011='{$_POST['EI498011']}',EI498012='{$_POST['EI498012']}',EIQOFFM={$EIQOFFM},EIQOFFTR={$EIQOFFTR},EI4980TR={$EI4980TR},EIOFFM98={$EIOFFM98},EIALTKY='{$_POST['altKey']}' ";
			$stmtSQL .= " Where EIEINID={$_POST['einID']} ";
		}
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		// If row not added, set identity column and try again
		if (! $status && $maintenanceCode == "A") {
			Check_Identity_Column ( 'HRACAE', 'EIEINID', $stmtSQL );
		}
		
		$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $_POST ['einDesc'], "", "", "", "", "" );
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
	require_once 'CheckEnterChg.php';
	require_once 'NumEdit.php';
	
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.ein.value ==\"\" || ";
	print "\n     document.Chg.taxYear.value ==\"\" || ";
	print "\n     document.Chg.EIAFFTP.value ==\"\" || ";
	print "\n     document.Chg.einDesc.value ==\"\" || ";
	print "\n     document.Chg.coNum.value ==\"\" ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.taxYear, 4, 0) ";
	print "\n  && editNum(document.Chg.EIFPLAA, 11, 2) ";
	print "\n   ) return true;";
	print "\n }";
	
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";
	
	require_once ($genericHead);
	print "\n </head>";
	
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ACAEINMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From HRACAE ";
		$stmtSQL .= " Where EIEINID=$fromACAEINID ";
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
		$row [EIEIN] = $_POST ['ein'];
		$row [EITXYR] = $_POST ['taxYear'];
		$row [EIDESC] = $_POST ['einDesc'];
		$row [EICO] = $_POST ['coNum'];
		$row [EIFAC] = $_POST ['facNum'];
		$row [EIALEST] = $_POST ['EIALEST'];
		$row [EIAFFTP] = $_POST ['EIAFFTP'];
		$row [EIFPLAA] = $_POST ['EIFPLAA'];
		$row [EIATRAN] = $_POST ['EIATRAN'];
		$row [EIAAGRP] = $_POST ['EIAAGRP'];
		$row [EIQOFFA] = $_POST ['EIQOFFA'];
		$row [EIQOFF01] = $_POST ['EIQOFF01'];
		$row [EIQOFF02] = $_POST ['EIQOFF02'];
		$row [EIQOFF03] = $_POST ['EIQOFF03'];
		$row [EIQOFF04] = $_POST ['EIQOFF04'];
		$row [EIQOFF05] = $_POST ['EIQOFF05'];
		$row [EIQOFF06] = $_POST ['EIQOFF06'];
		$row [EIQOFF07] = $_POST ['EIQOFF07'];
		$row [EIQOFF08] = $_POST ['EIQOFF08'];
		$row [EIQOFF09] = $_POST ['EIQOFF09'];
		$row [EIQOFF10] = $_POST ['EIQOFF10'];
		$row [EIQOFF11] = $_POST ['EIQOFF11'];
		$row [EIQOFF12] = $_POST ['EIQOFF12'];
		$row [EI498001] = $_POST ['EI498001'];
		$row [EI498002] = $_POST ['EI498002'];
		$row [EI498003] = $_POST ['EI498003'];
		$row [EI498004] = $_POST ['EI498004'];
		$row [EI498005] = $_POST ['EI498005'];
		$row [EI498006] = $_POST ['EI498006'];
		$row [EI498007] = $_POST ['EI498007'];
		$row [EI498008] = $_POST ['EI498008'];
		$row [EI498009] = $_POST ['EI498009'];
		$row [EI498010] = $_POST ['EI498010'];
		$row [EI498011] = $_POST ['EI498011'];
		$row [EI498012] = $_POST ['EI498012'];
		$row [EIQOFFM] = $_POST ['EIQOFFM'];
		$row [EIQOFFTR] = $_POST ['EIQOFFTR'];
		$row [EI4980TR] = $_POST ['EI4980TR'];
		$row [EIOFFM98] = $_POST ['EIOFFM98'];
		$row [EIALTKY] = $_POST ['altKey'];
		$focusField = "ein";
	} else {
		$row [EIATRAN] = ($row [EIATRAN] == 1) ? 'Y' : '';
		$row [EIAAGRP] = ($row [EIAAGRP] == 1) ? 'Y' : '';
		$row [EIQOFFA] = ($row [EIQOFFA] == 1) ? 'Y' : '';
		$row [EIQOFF01] = ($row [EIQOFF01] == 1) ? 'Y' : '';
		$row [EIQOFF02] = ($row [EIQOFF02] == 1) ? 'Y' : '';
		$row [EIQOFF03] = ($row [EIQOFF03] == 1) ? 'Y' : '';
		$row [EIQOFF04] = ($row [EIQOFF04] == 1) ? 'Y' : '';
		$row [EIQOFF05] = ($row [EIQOFF05] == 1) ? 'Y' : '';
		$row [EIQOFF06] = ($row [EIQOFF06] == 1) ? 'Y' : '';
		$row [EIQOFF07] = ($row [EIQOFF07] == 1) ? 'Y' : '';
		$row [EIQOFF08] = ($row [EIQOFF08] == 1) ? 'Y' : '';
		$row [EIQOFF09] = ($row [EIQOFF09] == 1) ? 'Y' : '';
		$row [EIQOFF10] = ($row [EIQOFF10] == 1) ? 'Y' : '';
		$row [EIQOFF11] = ($row [EIQOFF11] == 1) ? 'Y' : '';
		$row [EIQOFF12] = ($row [EIQOFF12] == 1) ? 'Y' : '';
		$row [EIQOFFM] = ($row [EIQOFFM] == 1) ? 'Y' : '';
		$row [EIQOFFTR] = ($row [EIQOFFTR] == 1) ? 'Y' : '';
		$row [EI4980TR] = ($row [EI4980TR] == 1) ? 'Y' : '';
		$row [EIOFFM98] = ($row [EIOFFM98] == 1) ? 'Y' : '';
		$focusField = "ein";
	}
	
	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode ( trim ( $maintenanceCode ) ) . "\">";
	print "\n <table $contentTable>";
	print "\n <tr><td><input type=\"hidden\" name=\"lastUpdated\" value=\"" . rtrim ( $row ['EILUPD'] ) . "\"></td></tr> ";
	print "\n <tr><td><input type=\"hidden\" name=\"einID\" value=\"" . rtrim ( $row ['EIEINID'] ) . "\"></td></tr> ";
	
	Build_Fld_Entry ( "EIN", "ein", "inputalph", "", "EIEIN", $row [EIEIN], $Err_EIEIN, "50", "64", "Y", "", "" );
	Build_Fld_Entry ( "Tax Year", "taxYear", "inputnmbr", "", "EITXYR", $row [EITXYR], $Err_EITXYR, "4", "4", "Y", "", "" );
	Build_Fld_Entry ( "Description", "einDesc", "inputalph", "", "EIDESC", $row [EIDESC], $Err_EIDESC, "50", "50", "Y", "", "" );
	
	if (is_null ( $row ['EICO'] ) || trim ( $row ['EICO'] ) == "") {
		$row ['EICO'] = 0;
	}
	if (is_null ( $row ['EIFAC'] ) || trim ( $row ['EIFAC'] ) == "") {
		$row ['EIFAC'] = 0;
	}
	$fieldDesc = RetValue ( "CFCOMP=$row[EICO] and CFFACL=$row[EIFAC]", "HRCOFC", "CFNAME" );
	$textOvr = SetTextOvr ( $Err_EICO );
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Default From Co/Fac Number</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"coNum\" value=\"" . rtrim ( $row ['EICO'] ) . "\" size=\"1\" maxlength=\"2\"> / <input type=\"text\"   name=\"facNum\" value=\"" . rtrim ( $row ['EIFAC'] ) . "\" size=\"1\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=coNum&amp;fldFac=facNum&amp;fldDesc=coNumDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"coNumDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg ( $Err_EICO );
	
	Build_Fld_Entry ( "Applicable Large Employer Status", "EIALEST", "inputalph", "ACAALE", "EIALEST", $row [EIALEST], $Err_EIALEST, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Affordability Safe Harbor Type", "EIAFFTP", "inputalph", "ACAAFFTYPE", "EIAFFTP", $row [EIAFFTP], $Err_EIAFFTP, "1", "1", "Y", "", "" );
	Build_Fld_Entry ( "FPL Monthly Affordability Amount", "EIFPLAA", "inputnmbr", "", "EIFPLAA", $row [EIFPLAA], $Err_EIFPLAA, "10", "10", "", "", "" );
	Build_Fld_Entry ( "Authoritative Transmitter", "EIATRAN", "inputalph", "BY", "EIATRAN", $row [EIATRAN], $Err_EIATRAN, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated ALE Group", "EIAAGRP", "inputalph", "BY", "EIAAGRP", $row [EIAAGRP], $Err_EIAAGRP, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer All 12 Months", "EIQOFFA", "inputalph", "BY", "EIQOFFA", $row [EIQOFFA], $Err_EIQOFFA, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - January", "EIQOFF01", "inputalph", "BY", "EIQOFF01", $row [EIQOFF01], $Err_EIQOFF01, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - February", "EIQOFF02", "inputalph", "BY", "EIQOFF02", $row [EIQOFF02], $Err_EIQOFF02, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - March", "EIQOFF03", "inputalph", "BY", "EIQOFF03", $row [EIQOFF03], $Err_EIQOFF03, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - April", "EIQOFF04", "inputalph", "BY", "EIQOFF04", $row [EIQOFF04], $Err_EIQOFF04, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - May", "EIQOFF05", "inputalph", "BY", "EIQOFF05", $row [EIQOFF05], $Err_EIQOFF05, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - June", "EIQOFF06", "inputalph", "BY", "EIQOFF06", $row [EIQOFF06], $Err_EIQOFF06, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - July", "EIQOFF07", "inputalph", "BY", "EIQOFF07", $row [EIQOFF07], $Err_EIQOFF07, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - August", "EIQOFF08", "inputalph", "BY", "EIQOFF08", $row [EIQOFF08], $Err_EIQOFF08, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - September", "EIQOFF09", "inputalph", "BY", "EIQOFF09", $row [EIQOFF09], $Err_EIQOFF09, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - October", "EIQOFF10", "inputalph", "BY", "EIQOFF10", $row [EIQOFF10], $Err_EIQOFF10, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - November", "EIQOFF11", "inputalph", "BY", "EIQOFF11", $row [EIQOFF11], $Err_EIQOFF11, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer - December", "EIQOFF12", "inputalph", "BY", "EIQOFF12", $row [EIQOFF12], $Err_EIQOFF12, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - January", "EI498001", "inputalph", "ACATRANREL", "EI498001", $row [EI498001], $Err_EI498001, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - February", "EI498002", "inputalph", "ACATRANREL", "EI498002", $row [EI498002], $Err_EI498002, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - March", "EI498003", "inputalph", "ACATRANREL", "EI498003", $row [EI498003], $Err_EI498003, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - April", "EI498004", "inputalph", "ACATRANREL", "EI498004", $row [EI498004], $Err_EI498004, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - May", "EI498005", "inputalph", "ACATRANREL", "EI498005", $row [EI498005], $Err_EI498005, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - June", "EI498006", "inputalph", "ACATRANREL", "EI498006", $row [EI498006], $Err_EI498006, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - July", "EI498007", "inputalph", "ACATRANREL", "EI498007", $row [EI498007], $Err_EI498007, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - August", "EI498008", "inputalph", "ACATRANREL", "EI498008", $row [EI498008], $Err_EI498008, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - September", "EI498009", "inputalph", "ACATRANREL", "EI498009", $row [EI498009], $Err_EI498009, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - October", "EI498010", "inputalph", "ACATRANREL", "EI498010", $row [EI498010], $Err_EI498010, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - November", "EI498011", "inputalph", "ACATRANREL", "EI498011", $row [EI498011], $Err_EI498011, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H - December", "EI498012", "inputalph", "ACATRANREL", "EI498012", $row [EI498012], $Err_EI498012, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer Method", "EIQOFFM", "inputalph", "BY", "EIQOFFM", $row [EIQOFFM], $Err_EIQOFFM, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer Method Trans Relief", "EIQOFFTR", "inputalph", "BY", "EIQOFFTR", $row [EIQOFFTR], $Err_EIQOFFTR, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Transition Relief", "EI4980TR", "inputalph", "BY", "EI4980TR", $row [EI4980TR], $Err_EI4980TR, "1", "1", "", "", "" );
	Build_Fld_Entry ( "98% Offer Method", "EIOFFM98", "inputalph", "BY", "EIOFFM98", $row [EIOFFM98], $Err_EIOFFM98, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Alternate Key", "altKey", "inputalph", "", "EIALTKY", $row [EIALTKY], $Err_EIALTKY, "50", "128", "", "", "" );
	
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