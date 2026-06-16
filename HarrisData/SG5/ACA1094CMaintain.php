<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromACA1094CID = $_GET ['fromACA1094CID'];
$correction = (isset ( $_GET ['correction'] )) ? $_GET ['correction'] : 'N';
if ($correction == 'Y') {
	$C4CORCID = $fromACA1094CID;
} else {
	$C4CORCID = 0;
}
$fKey1 = $_GET ['fKey1'];
$fVal1 = $_GET ['fVal1'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "ACA 1094C Cache Maintenance";
$scriptName = "ACA1094CMaintain.php";
$fDsc1 = RetValue ( "EIEINID={$fVal1}", "HRACAE", "coalesce(EIEIN,'')" );
$scriptVarBase = "{$genericVarBase}&amp;fromACA1094CID=" . urlencode ( trim ( $fromACA1094CID ) ) . "&amp;fKey1=" . urlencode ( trim ( $fKey1 ) ) . "&amp;fVal1=" . urlencode ( trim ( $fVal1 ) ) . "&amp;fDsc1=" . urlencode ( trim ( $fDsc1 ) ) . "&amp;correction=" . urlencode ( trim ( $correction ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=481";
$byColumns = array ('C4ATRAN', 'C4AAGRP', 'C4QOFFM', 'C4QOFFTR', 'C44980TR', 'C4OFFM98', 'C4MC12M', 'C4QCOV01', 'C4QCOV02', 'C4QCOV03', 'C4QCOV04', 'C4QCOV05', 'C4QCOV06', 'C4QCOV07', 'C4QCOV08', 'C4QCOV09', 'C4QCOV10', 'C4QCOV11', 'C4QCOV12', 'C4AGRPA', 'C4AGRP01', 'C4AGRP02', 'C4AGRP03', 'C4AGRP04', 'C4AGRP05', 'C4AGRP06', 'C4AGRP07', 'C4AGRP08', 'C4AGRP09', 'C4AGRP10', 'C4AGRP11', 'C4AGRP12' );
$countColumns = array ('C4ATCNT', 'C4MBCNT', 'C4FACT01', 'C4FACT02', 'C4FACT03', 'C4FACT04', 'C4FACT05', 'C4FACT06', 'C4FACT07', 'C4FACT08', 'C4FACT09', 'C4FACT10', 'C4FACT11', 'C4FACT12', 'C4EACTA', 'C4EACT01', 'C4EACT02', 'C4EACT03', 'C4EACT04', 'C4EACT05', 'C4EACT06', 'C4EACT07', 'C4EACT08', 'C4EACT09', 'C4EACT10', 'C4EACT11', 'C4EACT12' );
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
		$lastUpdatedCurrent = RetValue ( "C4CACHID={$fromACA1094CID}", "HRACC4", "C4LUPD" );
		if ($lastUpdatedCurrent != $_POST ['C4LUPD']) {
			$errMsg = "Row has been previously updated";
		}
	} elseif ($maintenanceCode == "D") {
		$desc = RetValue ( "C4CACHID={$fromACA1094CID}", "HRACC4", "C4BNAM1" );
		
		$stmtSQL = " Delete From HRACC4 Where C4CACHID=" . $fromACA1094CID;
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$stmtSQL = " Delete From HRACC3 Where not exists (Select * from HRACC4 Where C3CAID94=C4CACHID)";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$stmtSQL = " Delete From HRACC5 Where not exists (Select * from HRACC4 Where C5CAID94=C4CACHID)";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$stmtSQL = " Delete From HRACC2 Where not exists (Select * from HRACC5 Where C2CAID5=C5CACHID)";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $desc, "", "", "", "", "" );
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	}
	
	if (! $errMsg && $maintenanceCode != "D") {
		$_POST ['C4CORR'] = strtoupper ( $_POST ['C4CORR'] );
		$fieldDesc = RetValue ( "FLTYPE='ACACORR' and FLVALU='{$_POST ['C4CORR']}'", "SYFLAG", "FLDESC" );
		if ($fieldDesc == '') {
			$Err_C4CORR = "Invalid Corrected";
			$errMsg = 'Please correct all errors';
		} elseif ($_POST ['C4CORCID'] > 0 && $_POST ['C4CORR'] == 'O') {
			$Err_C4CORR = "Original not valid for a correction";
			$errMsg = 'Please correct all errors';
		}
		if (preg_match_all ( "/[0-9]/", $_POST ['C4PYTIN'] ) < 9) {
			$Err_C4PYTIN = "Payer TIN must be at least 9 digits";
			$errMsg = 'Please correct all errors';
		}
		$_POST ['C4TINRT'] = strtoupper ( $_POST ['C4TINRT'] );
		if ($_POST ['C4TINRT'] != 'BUSINESS_TIN') {
			$Err_C4TINRT = "TIN Request Type must be BUSINESS_TIN";
			$errMsg = 'Please correct all errors';
		}
		$_POST ['C4STATE'] = strtoupper ( $_POST ['C4STATE'] );
		$fieldDesc = RetValue ( "STID='{$_POST ['C4STATE']}'", "HDSTID", "STDESC" );
		if ($fieldDesc == '') {
			$Err_C4STATE = "Invalid State ID";
			$errMsg = 'Please correct all errors';
		}
		if (preg_match_all ( "/[0-9]/", $_POST ['C4PSTCD'] ) < 5) {
			$Err_C4PSTCD = "Postal Code must be at least 5 digits";
			$errMsg = 'Please correct all errors';
		}
	}
	
	if (! $errMsg) {
		$C4SUBMID = ($_POST ['C4SUBMID'] == '') ? 0 : $_POST ['C4SUBMID'];
		$reportData = array ();
		foreach ( $byColumns as $value ) {
			$reportData [$value] = ($_POST [$value] == 'Y') ? 1 : 0;
		}
		foreach ( $countColumns as $value ) {
			$_POST [$value] = ($_POST [$value] == '') ? 0 : $_POST [$value];
		}
		while ( strlen ( $_POST ['C4SIGDT'] ) < 7 ) {
			$_POST ['C4SIGDT'] = "0{$_POST ['C4SIGDT']}";
		}
		$iso_C4SIGDT = ($_POST ['C4SIGDT'] != '0000000') ? "'" . Reformat_Date_ISO ( $_POST ['C4SIGDT'], "*MDY", "*ISO" ) . "'" : 'NULL';
		
		// Qualifying Offer Method Trans Relief is only valid for 2015
		if ($_POST ['C4TXYR'] != 2015) {
			$reportData ['C4QOFFTR'] = 0;
		}
		// Section 4980H Transition Relief is only valid for 2015 or 2016
		if ($_POST ['C4TXYR'] != 2015 && $_POST ['C4TXYR'] != 2016) {
			$reportData ['C44980TR'] = 0;
			$_POST ['C44980TA'] = ' ';
			$_POST ['C44980T01'] = ' ';
			$_POST ['C44980T02'] = ' ';
			$_POST ['C44980T03'] = ' ';
			$_POST ['C44980T04'] = ' ';
			$_POST ['C44980T05'] = ' ';
			$_POST ['C44980T06'] = ' ';
			$_POST ['C44980T07'] = ' ';
			$_POST ['C44980T08'] = ' ';
			$_POST ['C44980T09'] = ' ';
			$_POST ['C44980T10'] = ' ';
			$_POST ['C44980T11'] = ' ';
			$_POST ['C44980T12'] = ' ';
		}
		
		if ($maintenanceCode == "A") {
			$stmtSQL = "Select * from new table (
			             Insert Into HRACC4 (C4CORCID,C4TXYR,C4RCPTID,C4SUBMID,C4CORR,C4CSUBID,C4BNAM1,C4BNAM2,C4PYTIN,C4BNAMC,C4TINRT,C4ADDR1,C4ADDR2,C4CITY,C4STATE,C4PSTCD,C4CFNAM,
					     C4CMNAM,C4CLNAM,C4CSUFF,C4CPHON,C4ATCNT,C4ATRAN,C4MBCNT,C4AAGRP,C4QOFFM,C4QOFFTR,C44980TR,C4OFFM98,C4JSPIN,C4CTITL,C4SIGDT,C4MC12M,C4QCOV01,
					     C4QCOV02,C4QCOV03,C4QCOV04,C4QCOV05,C4QCOV06,C4QCOV07,C4QCOV08,C4QCOV09,C4QCOV10,C4QCOV11,C4QCOV12,C4FACT01,C4FACT02,C4FACT03,C4FACT04,C4FACT05,
					     C4FACT06,C4FACT07,C4FACT08,C4FACT09,C4FACT10,C4FACT11,C4FACT12,C4EACTA,C4EACT01,C4EACT02,C4EACT03,C4EACT04,C4EACT05,C4EACT06,C4EACT07,C4EACT08,
					     C4EACT09,C4EACT10,C4EACT11,C4EACT12,C4AGRPA,C4AGRP01,C4AGRP02,C4AGRP03,C4AGRP04,C4AGRP05,C4AGRP06,C4AGRP07,C4AGRP08,C4AGRP09,C4AGRP10,C4AGRP11,
					     C4AGRP12,C44980TA,C44980T01,C44980T02,C44980T03,C44980T04,C44980T05,C44980T06,C44980T07,C44980T08,C44980T09,C44980T10,C44980T11,C44980T12) ";
			$stmtSQL .= " Values ($C4CORCID, {$_POST ['C4TXYR']},'{$_POST ['C4RCPTID']}',{$C4SUBMID},'{$_POST ['C4CORR']}','{$_POST ['C4CSUBID']}','{$_POST ['C4BNAM1']}','{$_POST ['C4BNAM2']}','{$_POST ['C4PYTIN']}','{$_POST ['C4BNAMC']}','{$_POST ['C4TINRT']}','{$_POST ['C4ADDR1']}','{$_POST ['C4ADDR2']}','{$_POST ['C4CITY']}','{$_POST ['C4STATE']}','{$_POST ['C4PSTCD']}','{$_POST ['C4CFNAM']}',
					     '{$_POST ['C4CMNAM']}','{$_POST ['C4CLNAM']}','{$_POST ['C4CSUFF']}','{$_POST ['C4CPHON']}',{$_POST ['C4ATCNT']},{$reportData ['C4ATRAN']},{$_POST ['C4MBCNT']},{$reportData ['C4AAGRP']},{$reportData ['C4QOFFM']},{$reportData ['C4QOFFTR']},{$reportData ['C44980TR']},{$reportData ['C4OFFM98']},'{$_POST ['C4JSPIN']}','{$_POST ['C4CTITL']}',{$iso_C4SIGDT},{$reportData ['C4MC12M']},{$reportData ['C4QCOV01']},
					     {$reportData ['C4QCOV02']},{$reportData ['C4QCOV03']},{$reportData ['C4QCOV04']},{$reportData ['C4QCOV05']},{$reportData ['C4QCOV06']},{$reportData ['C4QCOV07']},{$reportData ['C4QCOV08']},{$reportData ['C4QCOV09']},{$reportData ['C4QCOV10']},{$reportData ['C4QCOV11']},{$reportData ['C4QCOV12']},{$_POST ['C4FACT01']},{$_POST ['C4FACT02']},{$_POST ['C4FACT03']},{$_POST ['C4FACT04']},{$_POST ['C4FACT05']},
					     {$_POST ['C4FACT06']},{$_POST ['C4FACT07']},{$_POST ['C4FACT08']},{$_POST ['C4FACT09']},{$_POST ['C4FACT10']},{$_POST ['C4FACT11']},{$_POST ['C4FACT12']},{$_POST ['C4EACTA']},{$_POST ['C4EACT01']},{$_POST ['C4EACT02']},{$_POST ['C4EACT03']},{$_POST ['C4EACT04']},{$_POST ['C4EACT05']},{$_POST ['C4EACT06']},{$_POST ['C4EACT07']},{$_POST ['C4EACT08']},
					     {$_POST ['C4EACT09']},{$_POST ['C4EACT10']},{$_POST ['C4EACT11']},{$_POST ['C4EACT12']},{$reportData ['C4AGRPA']},{$reportData ['C4AGRP01']},{$reportData ['C4AGRP02']},{$reportData ['C4AGRP03']},{$reportData ['C4AGRP04']},{$reportData ['C4AGRP05']},{$reportData ['C4AGRP06']},{$reportData ['C4AGRP07']},{$reportData ['C4AGRP08']},{$reportData ['C4AGRP09']},{$reportData ['C4AGRP10']},{$reportData ['C4AGRP11']},
					     {$reportData ['C4AGRP12']},'{$_POST ['C44980TA']}','{$_POST ['C44980T01']}','{$_POST ['C44980T02']}','{$_POST ['C44980T03']}','{$_POST ['C44980T04']}','{$_POST ['C44980T05']}','{$_POST ['C44980T06']}','{$_POST ['C44980T07']}','{$_POST ['C44980T08']}','{$_POST ['C44980T09']}','{$_POST ['C44980T10']}','{$_POST ['C44980T11']}','{$_POST ['C44980T12']}'))";
		} else {
			$stmtSQL = " Update HRACC4 set C4TXYR={$_POST ['C4TXYR']},C4RCPTID='{$_POST ['C4RCPTID']}',C4SUBMID={$C4SUBMID},C4CORR='{$_POST ['C4CORR']}',C4BNAM1='{$_POST ['C4BNAM1']}',C4BNAM2='{$_POST ['C4BNAM2']}',C4PYTIN='{$_POST ['C4PYTIN']}',C4BNAMC='{$_POST ['C4BNAMC']}',C4TINRT='{$_POST ['C4TINRT']}',C4ADDR1='{$_POST ['C4ADDR1']}',C4ADDR2='{$_POST ['C4ADDR2']}',C4CITY='{$_POST ['C4CITY']}',C4STATE='{$_POST ['C4STATE']}',C4PSTCD='{$_POST ['C4PSTCD']}',C4CFNAM='{$_POST ['C4CFNAM']}',
					     C4CMNAM='{$_POST ['C4CMNAM']}',C4CLNAM='{$_POST ['C4CLNAM']}',C4CSUFF='{$_POST ['C4CSUFF']}',C4CPHON='{$_POST ['C4CPHON']}',C4ATCNT={$_POST ['C4ATCNT']},C4ATRAN={$reportData ['C4ATRAN']},C4MBCNT={$_POST ['C4MBCNT']},C4AAGRP={$reportData ['C4AAGRP']},C4QOFFM={$reportData ['C4QOFFM']},C4QOFFTR={$reportData ['C4QOFFTR']},C44980TR={$reportData ['C44980TR']},C4OFFM98={$reportData ['C4OFFM98']},C4JSPIN='{$_POST ['C4JSPIN']}',C4CTITL='{$_POST ['C4CTITL']}',C4SIGDT={$iso_C4SIGDT},C4MC12M={$reportData ['C4MC12M']},C4QCOV01={$reportData ['C4QCOV01']},
					     C4QCOV02={$reportData ['C4QCOV02']},C4QCOV03={$reportData ['C4QCOV03']},C4QCOV04={$reportData ['C4QCOV04']},C4QCOV05={$reportData ['C4QCOV05']},C4QCOV06={$reportData ['C4QCOV06']},C4QCOV07={$reportData ['C4QCOV07']},C4QCOV08={$reportData ['C4QCOV08']},C4QCOV09={$reportData ['C4QCOV09']},C4QCOV10={$reportData ['C4QCOV10']},C4QCOV11={$reportData ['C4QCOV11']},C4QCOV12={$reportData ['C4QCOV12']},C4FACT01={$_POST ['C4FACT01']},C4FACT02={$_POST ['C4FACT02']},C4FACT03={$_POST ['C4FACT03']},C4FACT04={$_POST ['C4FACT04']},C4FACT05={$_POST ['C4FACT05']},
					     C4FACT06={$_POST ['C4FACT06']},C4FACT07={$_POST ['C4FACT07']},C4FACT08={$_POST ['C4FACT08']},C4FACT09={$_POST ['C4FACT09']},C4FACT10={$_POST ['C4FACT10']},C4FACT11={$_POST ['C4FACT11']},C4FACT12={$_POST ['C4FACT12']},C4EACTA={$_POST ['C4EACTA']},C4EACT01={$_POST ['C4EACT01']},C4EACT02={$_POST ['C4EACT02']},C4EACT03={$_POST ['C4EACT03']},C4EACT04={$_POST ['C4EACT04']},C4EACT05={$_POST ['C4EACT05']},C4EACT06={$_POST ['C4EACT06']},C4EACT07={$_POST ['C4EACT07']},C4EACT08={$_POST ['C4EACT08']},
					     C4EACT09={$_POST ['C4EACT09']},C4EACT10={$_POST ['C4EACT10']},C4EACT11={$_POST ['C4EACT11']},C4EACT12={$_POST ['C4EACT12']},C4AGRPA={$reportData ['C4AGRPA']},C4AGRP01={$reportData ['C4AGRP01']},C4AGRP02={$reportData ['C4AGRP02']},C4AGRP03={$reportData ['C4AGRP03']},C4AGRP04={$reportData ['C4AGRP04']},C4AGRP05={$reportData ['C4AGRP05']},C4AGRP06={$reportData ['C4AGRP06']},C4AGRP07={$reportData ['C4AGRP07']},C4AGRP08={$reportData ['C4AGRP08']},C4AGRP09={$reportData ['C4AGRP09']},C4AGRP10={$reportData ['C4AGRP10']},C4AGRP11={$reportData ['C4AGRP11']},
					     C4AGRP12={$reportData ['C4AGRP12']},C44980TA='{$_POST ['C44980TA']}',C44980T01='{$_POST ['C44980T01']}',C44980T02='{$_POST ['C44980T02']}',C44980T03='{$_POST ['C44980T03']}',C44980T04='{$_POST ['C44980T04']}',C44980T05='{$_POST ['C44980T05']}',C44980T06='{$_POST ['C44980T06']}',C44980T07='{$_POST ['C44980T07']}',C44980T08='{$_POST ['C44980T08']}',C44980T09='{$_POST ['C44980T09']}',C44980T10='{$_POST ['C44980T10']}',C44980T11='{$_POST ['C44980T11']}',C44980T12='{$_POST ['C44980T12']}' ";
			$stmtSQL .= " Where C4CACHID={$_POST['C4CACHID']} ";
		}
		$sqlResult4 = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		// If row not added, set identity column and try again
		if (! $sqlResult4 && $maintenanceCode == "A") {
			$sqlResult4 = Check_Identity ( 'HRACC4', 'C4CACHID', $stmtSQL );
		}
		$row4 = db2_fetch_assoc ( $sqlResult4 );
		
		// If row not added, set identity column and try again
		if ($correction == 'Y' && $maintenanceCode == "A") {
			// Copy Other Members
			$stmtSQL = "Select * from new table (
			             Insert Into HRACC3
			                    (C3CAID94,C3BNAM1,C3BNAM2,C3PYTIN,C3BNAMC,C3TINRT) 
			             Select 
			                    {$row4['C4CACHID']},C3BNAM1,C3BNAM2,C3PYTIN,C3BNAMC,C3TINRT
			             From HRACC3 a Where a.C3CAID94={$C4CORCID})";
			$sqlResult3 = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
			
			// If row not added, set identity column and try again
			if (! $sqlResult3) {
				$sqlResult3 = Check_Identity ( 'HRACC3', 'C3CACHID', $stmtSQL );
			}
		}
		
		$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $_POST ['C4BNAM1'], "", "", "", "", "" );
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
	print "\n if (document.Chg.C4TXYR.value ==\"\" || ";
	print "\n     document.Chg.C4CORR.value ==\"\" || ";
	print "\n     document.Chg.C4BNAM1.value ==\"\" || ";
	print "\n     document.Chg.C4PYTIN.value ==\"\" || ";
	print "\n     document.Chg.C4TINRT.value ==\"\" || ";
	print "\n     document.Chg.C4ADDR1.value ==\"\" || ";
	print "\n     document.Chg.C4CITY.value ==\"\" || ";
	print "\n     document.Chg.C4STATE.value ==\"\" || ";
	print "\n     document.Chg.C4PSTCD.value ==\"\" || ";
	print "\n     document.Chg.C4CFNAM.value ==\"\" || ";
	print "\n     document.Chg.C4CLNAM.value ==\"\" || ";
    print "\n     document.Chg.C4CPHON.value ==\"\" ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.C4TXYR, 4, 0)) ";
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
		$stmtSQL = " Select *  From HRACC4 Where C4CACHID=$fromACA1094CID ";
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
		$row [C4TXYR] = $_POST ['C4TXYR'];
		$row [C4RCPTID] = $_POST ['C4RCPTID'];
		$row [C4SUBMID] = $_POST ['C4SUBMID'];
		$row [C4CORR] = $_POST ['C4CORR'];
		$row [C4CSUBID] = $_POST ['C4CSUBID'];
		$row [C4BNAM1] = $_POST ['C4BNAM1'];
		$row [C4BNAM2] = $_POST ['C4BNAM2'];
		$row [C4PYTIN] = $_POST ['C4PYTIN'];
		$row [C4BNAMC] = $_POST ['C4BNAMC'];
		$row [C4TINRT] = $_POST ['C4TINRT'];
		$row [C4ADDR1] = $_POST ['C4ADDR1'];
		$row [C4ADDR2] = $_POST ['C4ADDR2'];
		$row [C4CITY] = $_POST ['C4CITY'];
		$row [C4STATE] = $_POST ['C4STATE'];
		$row [C4PSTCD] = $_POST ['C4PSTCD'];
		$row [C4CFNAM] = $_POST ['C4CFNAM'];
		$row [C4CMNAM] = $_POST ['C4CMNAM'];
		$row [C4CLNAM] = $_POST ['C4CLNAM'];
		$row [C4CSUFF] = $_POST ['C4CSUFF'];
		$row [C4CPHON] = $_POST ['C4CPHON'];
		$row [C4ATCNT] = $_POST ['C4ATCNT'];
		$row [C4ATRAN] = $_POST ['C4ATRAN'];
		$row [C4MBCNT] = $_POST ['C4MBCNT'];
		$row [C4AAGRP] = $_POST ['C4AAGRP'];
		$row [C4QOFFM] = $_POST ['C4QOFFM'];
		$row [C4QOFFTR] = $_POST ['C4QOFFTR'];
		$row [C44980TR] = $_POST ['C44980TR'];
		$row [C4OFFM98] = $_POST ['C4OFFM98'];
		$row [C4JSPIN] = $_POST ['C4JSPIN'];
		$row [C4CTITL] = $_POST ['C4CTITL'];
		$row [C4SIGDT] = $_POST ['C4SIGDT'];
		$row [C4MC12M] = $_POST ['C4MC12M'];
		$row [C4QCOV01] = $_POST ['C4QCOV01'];
		$row [C4QCOV02] = $_POST ['C4QCOV02'];
		$row [C4QCOV03] = $_POST ['C4QCOV03'];
		$row [C4QCOV04] = $_POST ['C4QCOV04'];
		$row [C4QCOV05] = $_POST ['C4QCOV05'];
		$row [C4QCOV06] = $_POST ['C4QCOV06'];
		$row [C4QCOV07] = $_POST ['C4QCOV07'];
		$row [C4QCOV08] = $_POST ['C4QCOV08'];
		$row [C4QCOV09] = $_POST ['C4QCOV09'];
		$row [C4QCOV10] = $_POST ['C4QCOV10'];
		$row [C4QCOV11] = $_POST ['C4QCOV11'];
		$row [C4QCOV12] = $_POST ['C4QCOV12'];
		$row [C4FACT01] = $_POST ['C4FACT01'];
		$row [C4FACT02] = $_POST ['C4FACT02'];
		$row [C4FACT03] = $_POST ['C4FACT03'];
		$row [C4FACT04] = $_POST ['C4FACT04'];
		$row [C4FACT05] = $_POST ['C4FACT05'];
		$row [C4FACT06] = $_POST ['C4FACT06'];
		$row [C4FACT07] = $_POST ['C4FACT07'];
		$row [C4FACT08] = $_POST ['C4FACT08'];
		$row [C4FACT09] = $_POST ['C4FACT09'];
		$row [C4FACT10] = $_POST ['C4FACT10'];
		$row [C4FACT11] = $_POST ['C4FACT11'];
		$row [C4FACT12] = $_POST ['C4FACT12'];
		$row [C4EACTA] = $_POST ['C4EACTA'];
		$row [C4EACT01] = $_POST ['C4EACT01'];
		$row [C4EACT02] = $_POST ['C4EACT02'];
		$row [C4EACT03] = $_POST ['C4EACT03'];
		$row [C4EACT04] = $_POST ['C4EACT04'];
		$row [C4EACT05] = $_POST ['C4EACT05'];
		$row [C4EACT06] = $_POST ['C4EACT06'];
		$row [C4EACT07] = $_POST ['C4EACT07'];
		$row [C4EACT08] = $_POST ['C4EACT08'];
		$row [C4EACT09] = $_POST ['C4EACT09'];
		$row [C4EACT10] = $_POST ['C4EACT10'];
		$row [C4EACT11] = $_POST ['C4EACT11'];
		$row [C4EACT12] = $_POST ['C4EACT12'];
		$row [C4AGRPA] = $_POST ['C4AGRPA'];
		$row [C4AGRP01] = $_POST ['C4AGRP01'];
		$row [C4AGRP02] = $_POST ['C4AGRP02'];
		$row [C4AGRP03] = $_POST ['C4AGRP03'];
		$row [C4AGRP04] = $_POST ['C4AGRP04'];
		$row [C4AGRP05] = $_POST ['C4AGRP05'];
		$row [C4AGRP06] = $_POST ['C4AGRP06'];
		$row [C4AGRP07] = $_POST ['C4AGRP07'];
		$row [C4AGRP08] = $_POST ['C4AGRP08'];
		$row [C4AGRP09] = $_POST ['C4AGRP09'];
		$row [C4AGRP10] = $_POST ['C4AGRP10'];
		$row [C4AGRP11] = $_POST ['C4AGRP11'];
		$row [C4AGRP12] = $_POST ['C4AGRP12'];
		$row [C44980TA] = $_POST ['C44980TA'];
		$row [C44980T01] = $_POST ['C44980T01'];
		$row [C44980T02] = $_POST ['C44980T02'];
		$row [C44980T03] = $_POST ['C44980T03'];
		$row [C44980T04] = $_POST ['C44980T04'];
		$row [C44980T05] = $_POST ['C44980T05'];
		$row [C44980T06] = $_POST ['C44980T06'];
		$row [C44980T07] = $_POST ['C44980T07'];
		$row [C44980T08] = $_POST ['C44980T08'];
		$row [C44980T09] = $_POST ['C44980T09'];
		$row [C44980T10] = $_POST ['C44980T10'];
		$row [C44980T11] = $_POST ['C44980T11'];
		$row [C44980T12] = $_POST ['C44980T12'];
		$focusField = "C4TXYR";
	} else {
		$C4CORCID = $row [C4CORCID];
		if ($maintenanceCode == "Z" && $correction == 'Y') {
			$row [C4CSUBID] = trim ( $row [C4RCPTID] ) . '|' . trim ( $row [C4SUBMID] );
			$row [C4RCPTID] = '';
			$row [C4SUBMID] = 0;
			$row [C4CORR] = '';
		}
		$row [C4SIGDT] = substr ( $row [C4SIGDT], 5, 2 ) . substr ( $row [C4SIGDT], 8, 2 ) . substr ( $row [C4SIGDT], 2, 2 );
		foreach ( $byColumns as $value ) {
			$row [$value] = ($row [$value] == 1) ? 'Y' : '';
		}
		$focusField = "C4TXYR";
	}
	
	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode ( trim ( $maintenanceCode ) ) . "\">";
	print "\n <table $contentTable>";
	print "\n <tr><td><input type=\"hidden\" name=\"C4LUPD\" value=\"" . rtrim ( $row ['C4LUPD'] ) . "\"></td></tr> ";
	print "\n <tr><td><input type=\"hidden\" name=\"C4CACHID\" value=\"" . rtrim ( $row ['C4CACHID'] ) . "\"></td></tr> ";
	print "\n <tr><td><input type=\"hidden\" name=\"C4CORCID\" value=\"" . rtrim ( $C4CORCID ) . "\"></td></tr> ";
	
	Build_Fld_Entry ( "Tax Year", "C4TXYR", "inputnmbr", "", "C4TXYR", $row [C4TXYR], $Err_C4TXYR, "4", "4", "Y", "", "" );
	Build_Fld_Entry ( "Receipt ID", "C4RCPTID", "inputalph", "", "C4RCPTID", $row [C4RCPTID], $Err_C4RCPTID, "64", "64", "", "", "" );
	Build_Fld_Entry ( "Submission ID", "C4SUBMID", "inputnmbr", "", "C4SUBMID", $row [C4SUBMID], $Err_C4SUBMID, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Corrected", "C4CORR", "inputalph", "ACACORR", "C4CORR", $row [C4CORR], $Err_C4CORR, "1", "1", "Y", "", "" );
	print "\n <tr><td><input type=\"hidden\" name=\"C4CSUBID\" value=\"" . rtrim ( $row [C4CSUBID] ) . "\"></td></tr> ";
	print "\n <tr><td class=\"dsphdr\">Corrected Unique Submission ID</td><td class=\"dspalph\">{$row [C4CSUBID]}</td></tr>";
	print "\n <tr><td class=\"dsphdr\">Unique Submission ID</td><td class=\"dspalph\">{$row [C4TRANID]}</td></tr>";
	Build_Fld_Entry ( "Business Name 1", "C4BNAM1", "inputalph", "", "C4BNAM1", $row [C4BNAM1], $Err_C4BNAM1, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Business Name 2", "C4BNAM2", "inputalph", "", "C4BNAM2", $row [C4BNAM2], $Err_C4BNAM2, "64", "64", "", "", "" );
	Build_Fld_Entry ( "Payer TIN", "C4PYTIN", "inputalph", "", "C4PYTIN", $row [C4PYTIN], $Err_C4PYTIN, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Business Name Control", "C4BNAMC", "inputalph", "", "C4BNAMC", $row [C4BNAMC], $Err_C4BNAMC, "64", "64", "", "", "" );
	Build_Fld_Entry ( "TIN Request Type", "C4TINRT", "inputalph", "", "C4TINRT", $row [C4TINRT], $Err_C4TINRT, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Address Line 1", "C4ADDR1", "inputalph", "", "C4ADDR1", $row [C4ADDR1], $Err_C4ADDR1, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Address Line 2", "C4ADDR2", "inputalph", "", "C4ADDR2", $row [C4ADDR2], $Err_C4ADDR2, "64", "64", "", "", "" );
	Build_Fld_Entry ( "City", "C4CITY", "inputalph", "", "C4CITY", $row [C4CITY], $Err_C4CITY, "30", "30", "Y", "", "" );
	// State
	$fieldDesc = RetValue ( "STID='$row[C4STATE]'", "HDSTID", "STDESC" );
	$textOvr = SetTextOvr ( $Err_C4STATE );
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>State ID</span></td>";
	print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"C4STATE\" value=\"" . rtrim ( $row ['C4STATE'] ) . "\" size=\"3\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=C4STATE&amp;fldDesc=stateDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"stateDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg ( $Err_C4STATE );
	
	Build_Fld_Entry ( "Postal Code", "C4PSTCD", "inputalph", "", "C4PSTCD", $row [C4PSTCD], $Err_C4PSTCD, "10", "10", "Y", "", "" );
	Build_Fld_Entry ( "Contact First Name", "C4CFNAM", "inputalph", "", "C4CFNAM", $row [C4CFNAM], $Err_C4CFNAM, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Contact Middle Initial", "C4CMNAM", "inputalph", "", "C4CMNAM", $row [C4CMNAM], $Err_C4CMNAM, "64", "64", "", "", "" );
	Build_Fld_Entry ( "Contact Last Name", "C4CLNAM", "inputalph", "", "C4CLNAM", $row [C4CLNAM], $Err_C4CLNAM, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Contact Suffix", "C4CSUFF", "inputalph", "", "C4CSUFF", $row [C4CSUFF], $Err_C4CSUFF, "10", "10", "", "", "" );
	Build_Fld_Entry ( "Contact Phone", "C4CPHON", "inputalph", "", "C4CPHON", $row [C4CPHON], $Err_C4CPHON, "20", "20", "Y", "", "" );
	Build_Fld_Entry ( "Form 1095 Attached Count", "C4ATCNT", "inputnmbr", "", "C4ATCNT", $row [C4ATCNT], $Err_C4ATCNT, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Authoritative Transmitter", "C4ATRAN", "inputalph", "BY", "C4ATRAN", $row [C4ATRAN], $Err_C4ATRAN, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Form 1095C ALE Count", "C4MBCNT", "inputnmbr", "", "C4MBCNT", $row [C4MBCNT], $Err_C4MBCNT, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Aggregated ALE Group", "C4AAGRP", "inputalph", "BY", "C4AAGRP", $row [C4AAGRP], $Err_C4AAGRP, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer Method", "C4QOFFM", "inputalph", "BY", "C4QOFFM", $row [C4QOFFM], $Err_C4QOFFM, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Offer Method Trans Relief", "C4QOFFTR", "inputalph", "BY", "C4QOFFTR", $row [C4QOFFTR], $Err_C4QOFFTR, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Transition Relief", "C44980TR", "inputalph", "BY", "C44980TR", $row [C44980TR], $Err_C44980TR, "1", "1", "", "", "" );
	Build_Fld_Entry ( "98% Offer Method", "C4OFFM98", "inputalph", "BY", "C4OFFM98", $row [C4OFFM98], $Err_C4OFFM98, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Jurat Signature PIN", "C4JSPIN", "inputalph", "", "C4JSPIN", $row [C4JSPIN], $Err_C4JSPIN, "20", "20", "", "", "" );
	Build_Fld_Entry ( "Contact Person Title", "C4CTITL", "inputalph", "", "C4CTITL", $row [C4CTITL], $Err_C4CTITL, "20", "20", "", "", "" );
	Build_Fld_Entry ( "Signature Date", "C4SIGDT", "inputdate", "Date", "C4SIGDT", $row [C4SIGDT], $Err_C4SIGDT, "6", "6", "", "", "" );
	Build_Fld_Entry ( "Minimum Essential Coverage 12 Months", "C4MC12M", "inputalph", "BY", "C4MC12M", $row [C4MC12M], $Err_C4MC12M, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - January", "C4QCOV01", "inputalph", "BY", "C4QCOV01", $row [C4QCOV01], $Err_C4QCOV01, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - February", "C4QCOV02", "inputalph", "BY", "C4QCOV02", $row [C4QCOV02], $Err_C4QCOV02, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - March", "C4QCOV03", "inputalph", "BY", "C4QCOV03", $row [C4QCOV03], $Err_C4QCOV03, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - April", "C4QCOV04", "inputalph", "BY", "C4QCOV04", $row [C4QCOV04], $Err_C4QCOV04, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - May", "C4QCOV05", "inputalph", "BY", "C4QCOV05", $row [C4QCOV05], $Err_C4QCOV05, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - June", "C4QCOV06", "inputalph", "BY", "C4QCOV06", $row [C4QCOV06], $Err_C4QCOV06, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - July", "C4QCOV07", "inputalph", "BY", "C4QCOV07", $row [C4QCOV07], $Err_C4QCOV07, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - August", "C4QCOV08", "inputalph", "BY", "C4QCOV08", $row [C4QCOV08], $Err_C4QCOV08, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - September", "C4QCOV09", "inputalph", "BY", "C4QCOV09", $row [C4QCOV09], $Err_C4QCOV09, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - October", "C4QCOV10", "inputalph", "BY", "C4QCOV10", $row [C4QCOV10], $Err_C4QCOV10, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - November", "C4QCOV11", "inputalph", "BY", "C4QCOV11", $row [C4QCOV11], $Err_C4QCOV11, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Qualifying Coverage - December", "C4QCOV12", "inputalph", "BY", "C4QCOV12", $row [C4QCOV12], $Err_C4QCOV12, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - January", "C4FACT01", "inputnmbr", "", "C4FACT01", $row [C4FACT01], $Err_C4FACT01, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - February", "C4FACT02", "inputnmbr", "", "C4FACT02", $row [C4FACT02], $Err_C4FACT02, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - March", "C4FACT03", "inputnmbr", "", "C4FACT03", $row [C4FACT03], $Err_C4FACT03, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - April", "C4FACT04", "inputnmbr", "", "C4FACT04", $row [C4FACT04], $Err_C4FACT04, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - May", "C4FACT05", "inputnmbr", "", "C4FACT05", $row [C4FACT05], $Err_C4FACT05, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - June", "C4FACT06", "inputnmbr", "", "C4FACT06", $row [C4FACT06], $Err_C4FACT06, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - July", "C4FACT07", "inputnmbr", "", "C4FACT07", $row [C4FACT07], $Err_C4FACT07, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - August", "C4FACT08", "inputnmbr", "", "C4FACT08", $row [C4FACT08], $Err_C4FACT08, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - September", "C4FACT09", "inputnmbr", "", "C4FACT09", $row [C4FACT09], $Err_C4FACT09, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - October", "C4FACT10", "inputnmbr", "", "C4FACT10", $row [C4FACT10], $Err_C4FACT10, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - November", "C4FACT11", "inputnmbr", "", "C4FACT11", $row [C4FACT11], $Err_C4FACT11, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Full Time Count ALE - December", "C4FACT12", "inputnmbr", "", "C4FACT12", $row [C4FACT12], $Err_C4FACT12, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE All 12 Months", "C4EACTA", "inputnmbr", "", "C4EACTA", $row [C4EACTA], $Err_C4EACTA, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - January", "C4EACT01", "inputnmbr", "", "C4EACT01", $row [C4EACT01], $Err_C4EACT01, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - February", "C4EACT02", "inputnmbr", "", "C4EACT02", $row [C4EACT02], $Err_C4EACT02, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - March", "C4EACT03", "inputnmbr", "", "C4EACT03", $row [C4EACT03], $Err_C4EACT03, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - April", "C4EACT04", "inputnmbr", "", "C4EACT04", $row [C4EACT04], $Err_C4EACT04, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - May", "C4EACT05", "inputnmbr", "", "C4EACT05", $row [C4EACT05], $Err_C4EACT05, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - June", "C4EACT06", "inputnmbr", "", "C4EACT06", $row [C4EACT06], $Err_C4EACT06, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - July", "C4EACT07", "inputnmbr", "", "C4EACT07", $row [C4EACT07], $Err_C4EACT07, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - August", "C4EACT08", "inputnmbr", "", "C4EACT08", $row [C4EACT08], $Err_C4EACT08, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - September", "C4EACT09", "inputnmbr", "", "C4EACT09", $row [C4EACT09], $Err_C4EACT09, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - October", "C4EACT10", "inputnmbr", "", "C4EACT10", $row [C4EACT10], $Err_C4EACT10, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - November", "C4EACT11", "inputnmbr", "", "C4EACT11", $row [C4EACT11], $Err_C4EACT11, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Count ALE - December", "C4EACT12", "inputnmbr", "", "C4EACT12", $row [C4EACT12], $Err_C4EACT12, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group All 12 Months", "C4AGRPA", "inputalph", "BY", "C4AGRPA", $row [C4AGRPA], $Err_C4AGRPA, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - January", "C4AGRP01", "inputalph", "BY", "C4AGRP01", $row [C4AGRP01], $Err_C4AGRP01, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - February", "C4AGRP02", "inputalph", "BY", "C4AGRP02", $row [C4AGRP02], $Err_C4AGRP02, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - March", "C4AGRP03", "inputalph", "BY", "C4AGRP03", $row [C4AGRP03], $Err_C4AGRP03, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - April", "C4AGRP04", "inputalph", "BY", "C4AGRP04", $row [C4AGRP04], $Err_C4AGRP04, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - May", "C4AGRP05", "inputalph", "BY", "C4AGRP05", $row [C4AGRP05], $Err_C4AGRP05, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - June", "C4AGRP06", "inputalph", "BY", "C4AGRP06", $row [C4AGRP06], $Err_C4AGRP06, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - July", "C4AGRP07", "inputalph", "BY", "C4AGRP07", $row [C4AGRP07], $Err_C4AGRP07, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - August", "C4AGRP08", "inputalph", "BY", "C4AGRP08", $row [C4AGRP08], $Err_C4AGRP08, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - September", "C4AGRP09", "inputalph", "BY", "C4AGRP09", $row [C4AGRP09], $Err_C4AGRP09, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - October", "C4AGRP10", "inputalph", "BY", "C4AGRP10", $row [C4AGRP10], $Err_C4AGRP10, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - November", "C4AGRP11", "inputalph", "BY", "C4AGRP11", $row [C4AGRP11], $Err_C4AGRP11, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Aggregated Group - December", "C4AGRP12", "inputalph", "BY", "C4AGRP12", $row [C4AGRP12], $Err_C4AGRP12, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief All 12 Months", "C44980TA", "inputalph", "ACATRANREL", "C44980TA", $row [C44980TA], $Err_C44980TA, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - January", "C44980T01", "inputalph", "ACATRANREL", "C44980T01", $row [C44980T01], $Err_C44980T01, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - February", "C44980T02", "inputalph", "ACATRANREL", "C44980T02", $row [C44980T02], $Err_C44980T02, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - March", "C44980T03", "inputalph", "ACATRANREL", "C44980T03", $row [C44980T03], $Err_C44980T03, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - April", "C44980T04", "inputalph", "ACATRANREL", "C44980T04", $row [C44980T04], $Err_C44980T04, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - May", "C44980T05", "inputalph", "ACATRANREL", "C44980T05", $row [C44980T05], $Err_C44980T05, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - June", "C44980T06", "inputalph", "ACATRANREL", "C44980T06", $row [C44980T06], $Err_C44980T06, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - July", "C44980T07", "inputalph", "ACATRANREL", "C44980T07", $row [C44980T07], $Err_C44980T07, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - August", "C44980T08", "inputalph", "ACATRANREL", "C44980T08", $row [C44980T08], $Err_C44980T08, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - September", "C44980T09", "inputalph", "ACATRANREL", "C44980T09", $row [C44980T09], $Err_C44980T09, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - October", "C44980T10", "inputalph", "ACATRANREL", "C44980T10", $row [C44980T10], $Err_C44980T10, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - November", "C44980T11", "inputalph", "ACATRANREL", "C44980T11", $row [C44980T11], $Err_C44980T11, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Trans Relief - December", "C44980T12", "inputalph", "ACATRANREL", "C44980T12", $row [C44980T12], $Err_C44980T12, "1", "1", "", "", "" );
	
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

function Check_Identity($table, $column, $stmtSQL) {
	global $pgmLibrary, $i5Connect;
	if (! $i5Connect)
		die ( "<br>Check Identity Column Connection Failed. Error number =" . i5_errno () . " msg=" . i5_errormsg () );
	
	$maxSQL = "Select max({$column}) + 1 as MAXID From {$table}";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $maxSQL );
	$row = db2_fetch_assoc ( $sqlResult );
	if (array_key_exists ( 'MAXID', $row )) {
		$maxSQL = "ALTER TABLE {$table} ALTER COLUMN {$column} RESTART WITH {$row['MAXID']}";
		$status = db2_exec ( $i5Connect->getConnection (), $maxSQL );
		if ($status) {
			$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		}
	}
	return $status;
}

?>