<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];
$fromACA1094CID = $_GET ['fromACA1094CID'];
$fromACA1095CID = $_GET ['fromACA1095CID'];

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

$page_title = "ACA 1095C Cache Maintenance";
$scriptName = "ACA1095CMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromACA1095CID=" . urlencode ( trim ( $fromACA1095CID ) ) . "&amp;fromACA1094CID=" . urlencode ( trim ( $fromACA1094CID ) ) . "&amp;fKey1=C5CAID94&amp;fVal1=" . urlencode ( trim ( $fromACA1094CID ) ) . "&amp;fDsc1=" . urlencode ( trim ( $aca1094CRow ['C4BNAM1'] ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=482";
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
		$lastUpdatedCurrent = RetValue ( "C5CACHID={$fromACA1095CID}", "HRACC5", "C5LUPD" );
		if ($lastUpdatedCurrent != $_POST ['C5LUPD']) {
			$errMsg = "Row has been previously updated";
		}
	} elseif ($maintenanceCode == "D") {
		$desc = RetValue ( "C5CACHID={$fromACA1095CID}", "HRACC5", "C5ELNAM" );
		
		$stmtSQL = " Delete From HRACC5 Where C5CACHID=" . $fromACA1095CID;
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$stmtSQL = " Delete From HRACC2 Where not exists (Select * from HRACC5 Where C2CAID5=C5CACHID)";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $desc, "", "", "", "", "" );
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	}
	
	if (! $errMsg && $maintenanceCode != "D") {
		if ($_POST ['C5ESSOC'] != '' && preg_match_all ( "/[0-9]/", $_POST ['C5ESSOC'] ) < 9) {
			$Err_C5ESSOC = "Social Security Number must be 9 digits";
			$errMsg = 'Please correct all errors';
		}
		$_POST ['C5TINRT'] = strtoupper ( $_POST ['C5TINRT'] );
		if ($_POST ['C5TINRT'] != 'INDIVIDUAL_TIN') {
			$Err_C5TINRT = "TIN Request Type must be INDIVIDUAL_TIN";
			$errMsg = 'Please correct all errors';
		}
		$_POST ['C5STATE'] = strtoupper ( $_POST ['C5STATE'] );
		$fieldDesc = RetValue ( "STID='{$_POST ['C5STATE']}'", "HDSTID", "STDESC" );
		if ($fieldDesc == '') {
			$Err_C5STATE = "Invalid State ID";
			$errMsg = 'Please correct all errors';
		}
		if (preg_match_all ( "/[0-9]/", $_POST ['C5PSTCD'] ) < 5) {
			$Err_C5PSTCD = "Postal Code must be at least 5 digits";
			$errMsg = 'Please correct all errors';
		}
		if (trim($_POST['C5EEAGE']) != '' && ($_POST['C5EEAGE'] < '1' || $_POST['C5EEAGE'] > '120')) {
		    $Err_C5EEAGE = "Employee Age range is from 1 to 120";
		    $errMsg = 'Please correct all errors';
		}
		if (($_POST['C5PSMO'] < '1' || $_POST['C5PSMO'] > '12')) {
		    $Err_C5PSMO = "Plan Start Month must be in the range from 1 to 12";
		    $errMsg = 'Please correct all errors';
		    
		}
		$elementName = 'C5CVCD';
		for($mm = 1; $mm <= 12; $mm ++) {
			$mm2 = substr ( sprintf ( "%'.02d\n", $mm ), 0, 2 );
			$elementMonth = $elementName . $mm2;
			if ($_POST [$elementMonth] != '') {
				$fieldDesc = RetValue ( "FLTYPE='ACACOVCDE' and FLVALU='{$_POST [$elementMonth]}'", "SYFLAG", "FLDESC" );
				if ($fieldDesc == '') {
					$cov = 'Err_' . $elementMonth;
					$$cov = "Invalid Coverage Code";
					$errMsg = 'Please correct all errors';
				}
			}
		}
		$elementName = 'C5ZIP';
		$elementMonth = $elementName . 'A';
		if ($_POST [$elementMonth] != '' && preg_match_all ( "/[0-9]/", $_POST [$elementMonth] ) < 5) {
		    $cov = 'Err_' . $elementMonth;
		    $$cov = "Zip Code must be at least 5 digits";
		    $errMsg = 'Please correct all errors';
		    
		}
		for($mm = 1; $mm <= 12; $mm ++) {
		    $mm2 = substr ( sprintf ( "%'.02d\n", $mm ), 0, 2 );
		    $elementMonth = $elementName . $mm2;
		    if ($_POST [$elementMonth] != '' && preg_match_all ( "/[0-9]/", $_POST [$elementMonth] ) < 5) {
		        $cov = 'Err_' . $elementMonth;
		        $$cov = "Zip Code must be at least 5 digits";
		        $errMsg = 'Please correct all errors';
		        
		    }
		}
		
	}
	
	if (! $errMsg) {
		$C5RECID = ($_POST ['C5RECID'] == '') ? 0 : $_POST ['C5RECID'];
		$_POST ['C5EEAGE'] = ($_POST ['C5EEAGE'] == '') ? 0 : $_POST ['C5EEAGE'];
		$_POST ['C5PSMO'] = ($_POST ['C5PSMO'] == '') ? 0 : $_POST ['C5PSMO'];
		$C5CVIND = ($_POST [C5CVIND] == 'Y') ? 1 : 0;
		$_POST [C5EMSHA] = ($_POST [C5EMSHA] == '') ? 0 : $_POST [C5EMSHA];
		$_POST [C5EMSH01] = ($_POST [C5EMSH01] == '') ? 0 : $_POST [C5EMSH01];
		$_POST [C5EMSH02] = ($_POST [C5EMSH02] == '') ? 0 : $_POST [C5EMSH02];
		$_POST [C5EMSH03] = ($_POST [C5EMSH03] == '') ? 0 : $_POST [C5EMSH03];
		$_POST [C5EMSH04] = ($_POST [C5EMSH04] == '') ? 0 : $_POST [C5EMSH04];
		$_POST [C5EMSH05] = ($_POST [C5EMSH05] == '') ? 0 : $_POST [C5EMSH05];
		$_POST [C5EMSH06] = ($_POST [C5EMSH06] == '') ? 0 : $_POST [C5EMSH06];
		$_POST [C5EMSH07] = ($_POST [C5EMSH07] == '') ? 0 : $_POST [C5EMSH07];
		$_POST [C5EMSH08] = ($_POST [C5EMSH08] == '') ? 0 : $_POST [C5EMSH08];
		$_POST [C5EMSH09] = ($_POST [C5EMSH09] == '') ? 0 : $_POST [C5EMSH09];
		$_POST [C5EMSH10] = ($_POST [C5EMSH10] == '') ? 0 : $_POST [C5EMSH10];
		$_POST [C5EMSH11] = ($_POST [C5EMSH11] == '') ? 0 : $_POST [C5EMSH11];
		$_POST [C5EMSH12] = ($_POST [C5EMSH12] == '') ? 0 : $_POST [C5EMSH12];
		if ($maintenanceCode == "A") {
			$stmtSQL = " Insert Into HRACC5 
					    (C5CAID94,C5RECID,C5CURID,C5EFNAM,C5EMNAM,C5ELNAM,C5ESSOC,C5TINRT,C5ADDR1,C5ADDR2,C5CITY,C5STATE,C5PSTCD,C5EEAGE,C5PSMO,C5CVCDA,
			             C5CVCD01,C5CVCD02,C5CVCD03,C5CVCD04,C5CVCD05,C5CVCD06,C5CVCD07,C5CVCD08,C5CVCD09,C5CVCD10,
			             C5CVCD11,C5CVCD12,C5EMSHA,C5EMSH01,C5EMSH02,C5EMSH03,C5EMSH04,C5EMSH05,C5EMSH06,C5EMSH07,
			             C5EMSH08,C5EMSH09,C5EMSH10,C5EMSH11,C5EMSH12,C5SHBRA,C5SHBR01,C5SHBR02,C5SHBR03,C5SHBR04,
			             C5SHBR05,C5SHBR06,C5SHBR07,C5SHBR08,C5SHBR09,C5SHBR10,C5SHBR11,C5SHBR12,C5ZIPA,C5ZIP01,
                         C5ZIP02,C5ZIP03,C5ZIP04,C5ZIP05,C5ZIP06,C5ZIP07,C5ZIP08,C5ZIP09,C5ZIP10,C5ZIP11,C5ZIP12,C5CVIND) ";
			
			$stmtSQL .= " Values ({$fromACA1094CID},{$C5RECID},'{$_POST ['C5CURID']}','{$_POST ['C5EFNAM']}','{$_POST ['C5EMNAM']}','{$_POST ['C5ELNAM']}','{$_POST ['C5ESSOC']}','{$_POST ['C5TINRT']}',
			                     '{$_POST ['C5ADDR1']}','{$_POST ['C5ADDR2']}','{$_POST ['C5CITY']}','{$_POST ['C5STATE']}','{$_POST ['C5PSTCD']}','{$_POST['C5EEAGE']}','{$_POST['C5PSMO']}','{$_POST ['C5CVCDA']}',
			                     '{$_POST ['C5CVCD01']}','{$_POST ['C5CVCD02']}','{$_POST ['C5CVCD03']}','{$_POST ['C5CVCD04']}','{$_POST ['C5CVCD05']}','{$_POST ['C5CVCD06']}',
			                     '{$_POST ['C5CVCD07']}','{$_POST ['C5CVCD08']}','{$_POST ['C5CVCD09']}','{$_POST ['C5CVCD10']}','{$_POST ['C5CVCD11']}','{$_POST ['C5CVCD12']}',
					              {$_POST ['C5EMSHA']},{$_POST ['C5EMSH01']},{$_POST ['C5EMSH02']},{$_POST ['C5EMSH03']},{$_POST ['C5EMSH04']},{$_POST ['C5EMSH05']},{$_POST ['C5EMSH06']},
					              {$_POST ['C5EMSH07']},{$_POST ['C5EMSH08']},{$_POST ['C5EMSH09']},{$_POST ['C5EMSH10']},{$_POST ['C5EMSH11']},{$_POST ['C5EMSH12']},
					             '{$_POST ['C5SHBRA']}','{$_POST ['C5SHBR01']}','{$_POST ['C5SHBR02']}','{$_POST ['C5SHBR03']}','{$_POST ['C5SHBR04']}','{$_POST ['C5SHBR05']}','{$_POST ['C5SHBR06']}',
					             '{$_POST ['C5SHBR07']}','{$_POST ['C5SHBR08']}','{$_POST ['C5SHBR09']}','{$_POST ['C5SHBR10']}','{$_POST ['C5SHBR11']}','{$_POST ['C5SHBR12']}',
					             '{$_POST['C5ZIPA']}','{$_POST['C5ZIP01']}','{$_POST['C5ZIP02']}','{$_POST['C5ZIP03']}','{$_POST['C5ZIP04']}','{$_POST['C5ZIP05']}','{$_POST['C5ZIP06']}',
					             '{$_POST['C5ZIP07']}','{$_POST['C5ZIP08']}','{$_POST['C5ZIP09']}','{$_POST['C5ZIP10']}','{$_POST['C5ZIP11']}','{$_POST['C5ZIP12']}',{$C5CVIND}) ";
		} else {
			$stmtSQL = " Update HRACC5 set C5RECID={$C5RECID},C5CURID='{$_POST ['C5CURID']}',C5EFNAM='{$_POST ['C5EFNAM']}',C5EMNAM='{$_POST ['C5EMNAM']}',C5ELNAM='{$_POST ['C5ELNAM']}',C5ESSOC='{$_POST ['C5ESSOC']}',C5TINRT='{$_POST ['C5TINRT']}',
			                     C5ADDR1='{$_POST ['C5ADDR1']}',C5ADDR2='{$_POST ['C5ADDR2']}',C5CITY='{$_POST ['C5CITY']}',C5STATE='{$_POST ['C5STATE']}',C5PSTCD='{$_POST ['C5PSTCD']}',C5EEAGE='{$_POST['C5EEAGE']}',C5PSMO='{$_POST['C5PSMO']}',C5CVCDA='{$_POST ['C5CVCDA']}',
			                     C5CVCD01='{$_POST ['C5CVCD01']}',C5CVCD02='{$_POST ['C5CVCD02']}',C5CVCD03='{$_POST ['C5CVCD03']}',C5CVCD04='{$_POST ['C5CVCD04']}',C5CVCD05='{$_POST ['C5CVCD05']}',C5CVCD06='{$_POST ['C5CVCD06']}',
			                     C5CVCD07='{$_POST ['C5CVCD07']}',C5CVCD08='{$_POST ['C5CVCD08']}',C5CVCD09='{$_POST ['C5CVCD09']}',C5CVCD10='{$_POST ['C5CVCD10']}',C5CVCD11='{$_POST ['C5CVCD11']}',C5CVCD12='{$_POST ['C5CVCD12']}',
					             C5EMSHA={$_POST ['C5EMSHA']},C5EMSH01={$_POST ['C5EMSH01']},C5EMSH02={$_POST ['C5EMSH02']},C5EMSH03={$_POST ['C5EMSH03']},C5EMSH04={$_POST ['C5EMSH04']},C5EMSH05={$_POST ['C5EMSH05']},C5EMSH06={$_POST ['C5EMSH06']},
					             C5EMSH07={$_POST ['C5EMSH07']},C5EMSH08={$_POST ['C5EMSH08']},C5EMSH09={$_POST ['C5EMSH09']},C5EMSH10={$_POST ['C5EMSH10']},C5EMSH11={$_POST ['C5EMSH11']},C5EMSH12={$_POST ['C5EMSH12']},
					             C5SHBRA='{$_POST ['C5SHBRA']}',C5SHBR01='{$_POST ['C5SHBR01']}',C5SHBR02='{$_POST ['C5SHBR02']}',C5SHBR03='{$_POST ['C5SHBR03']}',C5SHBR04='{$_POST ['C5SHBR04']}',C5SHBR05='{$_POST ['C5SHBR05']}',C5SHBR06='{$_POST ['C5SHBR06']}',
					             C5SHBR07='{$_POST ['C5SHBR07']}',C5SHBR08='{$_POST ['C5SHBR08']}',C5SHBR09='{$_POST ['C5SHBR09']}',C5SHBR10='{$_POST ['C5SHBR10']}',C5SHBR11='{$_POST ['C5SHBR11']}',C5SHBR12='{$_POST ['C5SHBR12']}',
                                 C5ZIPA='{$_POST['C5ZIPA']}',C5ZIP01='{$_POST['C5ZIP01']}',C5ZIP02='{$_POST['C5ZIP02']}',C5ZIP03='{$_POST['C5ZIP03']}',C5ZIP04='{$_POST['C5ZIP04']}',C5ZIP05='{$_POST['C5ZIP05']}',C5ZIP06='{$_POST['C5ZIP06']}',
                                 C5ZIP07='{$_POST['C5ZIP07']}',C5ZIP08='{$_POST['C5ZIP08']}',C5ZIP09='{$_POST['C5ZIP09']}',C5ZIP10='{$_POST['C5ZIP10']}',C5ZIP11='{$_POST['C5ZIP11']}',C5ZIP12='{$_POST['C5ZIP12']}',
                                 C5CVIND={$C5CVIND} ";
			$stmtSQL .= " Where C5CACHID={$_POST['C5CACHID']} ";
		}
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		// If row not added, set identity column and try again
		if (! $status && $maintenanceCode == "A") {
			Check_Identity_Column ( 'HRACC5', 'C5CACHID', $stmtSQL );
		}
		
		if ($maintenanceCode == "A") {
			add1095CCacheDep ( $_POST );
		}
		
		$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $_POST ['C5ELNAM'], "", "", "", "", "" );
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
	print "\n if (document.Chg.C5EFNAM.value ==\"\" || ";
	print "\n     document.Chg.C5ELNAM.value ==\"\" || ";
	print "\n     document.Chg.C5TINRT.value ==\"\" || ";
	print "\n     document.Chg.C5ADDR1.value ==\"\" || ";
	print "\n     document.Chg.C5CITY.value ==\"\" || ";
	print "\n     document.Chg.C5STATE.value ==\"\" || ";
	print "\n     document.Chg.C5PSTCD.value ==\"\" || ";
	print "\n     document.Chg.C5PSMO.value ==\"\" ";
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
	$pageID = "ACA1095CMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL = " Select *  From HRACC5 Where C5CACHID=$fromACA1095CID ";
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
		$row [C5RECID] = $_POST ['C5RECID'];
		$row [C5CURID] = $_POST ['C5CURID'];
		$row [C5EFNAM] = $_POST ['C5EFNAM'];
		$row [C5EMNAM] = $_POST ['C5EMNAM'];
		$row [C5ELNAM] = $_POST ['C5ELNAM'];
		$row [C5ESSOC] = $_POST ['C5ESSOC'];
		$row [C5TINRT] = $_POST ['C5TINRT'];
		$row [C5ADDR1] = $_POST ['C5ADDR1'];
		$row [C5ADDR2] = $_POST ['C5ADDR2'];
		$row [C5CITY] = $_POST ['C5CITY'];
		$row [C5STATE] = $_POST ['C5STATE'];
		$row [C5PSTCD] = $_POST ['C5PSTCD'];
		$row ['C5EEAGE'] = $_POST ['C5EEAGE'];
		$row ['C5PSMO'] = $_POST ['C5PSMO'];
		$row [C5CVCDA] = $_POST ['C5CVCDA'];
		$row [C5CVCD01] = $_POST ['C5CVCD01'];
		$row [C5CVCD02] = $_POST ['C5CVCD02'];
		$row [C5CVCD03] = $_POST ['C5CVCD03'];
		$row [C5CVCD04] = $_POST ['C5CVCD04'];
		$row [C5CVCD05] = $_POST ['C5CVCD05'];
		$row [C5CVCD06] = $_POST ['C5CVCD06'];
		$row [C5CVCD07] = $_POST ['C5CVCD07'];
		$row [C5CVCD08] = $_POST ['C5CVCD08'];
		$row [C5CVCD09] = $_POST ['C5CVCD09'];
		$row [C5CVCD10] = $_POST ['C5CVCD10'];
		$row [C5CVCD11] = $_POST ['C5CVCD11'];
		$row [C5CVCD12] = $_POST ['C5CVCD12'];
		$row [C5EMSHA] = $_POST ['C5EMSHA'];
		$row [C5EMSH01] = $_POST ['C5EMSH01'];
		$row [C5EMSH02] = $_POST ['C5EMSH02'];
		$row [C5EMSH03] = $_POST ['C5EMSH03'];
		$row [C5EMSH04] = $_POST ['C5EMSH04'];
		$row [C5EMSH05] = $_POST ['C5EMSH05'];
		$row [C5EMSH06] = $_POST ['C5EMSH06'];
		$row [C5EMSH07] = $_POST ['C5EMSH07'];
		$row [C5EMSH08] = $_POST ['C5EMSH08'];
		$row [C5EMSH09] = $_POST ['C5EMSH09'];
		$row [C5EMSH10] = $_POST ['C5EMSH10'];
		$row [C5EMSH11] = $_POST ['C5EMSH11'];
		$row [C5EMSH12] = $_POST ['C5EMSH12'];
		$row [C5SHBRA] = $_POST ['C5SHBRA'];
		$row [C5SHBR01] = $_POST ['C5SHBR01'];
		$row [C5SHBR02] = $_POST ['C5SHBR02'];
		$row [C5SHBR03] = $_POST ['C5SHBR03'];
		$row [C5SHBR04] = $_POST ['C5SHBR04'];
		$row [C5SHBR05] = $_POST ['C5SHBR05'];
		$row [C5SHBR06] = $_POST ['C5SHBR06'];
		$row [C5SHBR07] = $_POST ['C5SHBR07'];
		$row [C5SHBR08] = $_POST ['C5SHBR08'];
		$row [C5SHBR09] = $_POST ['C5SHBR09'];
		$row [C5SHBR10] = $_POST ['C5SHBR10'];
		$row [C5SHBR11] = $_POST ['C5SHBR11'];
		$row [C5SHBR12] = $_POST ['C5SHBR12'];
		$row ['C5ZIPA'] = $_POST ['C5ZIPA'];
		$row ['C5ZIP01'] = $_POST ['C5ZIP01'];
		$row ['C5ZIP02'] = $_POST ['C5ZIP02'];
		$row ['C5ZIP03'] = $_POST ['C5ZIP03'];
		$row ['C5ZIP04'] = $_POST ['C5ZIP04'];
		$row ['C5ZIP05'] = $_POST ['C5ZIP05'];
		$row ['C5ZIP06'] = $_POST ['C5ZIP06'];
		$row ['C5ZIP07'] = $_POST ['C5ZIP07'];
		$row ['C5ZIP08'] = $_POST ['C5ZIP08'];
		$row ['C5ZIP09'] = $_POST ['C5ZIP09'];
		$row ['C5ZIP10'] = $_POST ['C5ZIP10'];
		$row ['C5ZIP11'] = $_POST ['C5ZIP11'];
		$row ['C5ZIP12'] = $_POST ['C5ZIP12'];
		$row [C5CVIND] = $_POST ['C5CVIND'];
		$focusField = "C5EFNAM";
	} else {
	    $row['C5EEAGE'] = ($row['C5EEAGE'] == 0) ? '' : $row['C5EEAGE'];
	    $row['C5PSMO'] = ($row['C5PSMO'] == 0) ? '' : $row['C5PSMO'];
		$row [C5CVIND] = ($row [C5CVIND] == 1) ? 'Y' : '';
		$focusField = "C5EFNAM";
	}
	
	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode ( trim ( $maintenanceCode ) ) . "\">";
	print "\n <table $contentTable>";
	print "\n <tr><td><input type=\"hidden\" name=\"C5LUPD\" value=\"" . rtrim ( $row ['C5LUPD'] ) . "\"></td></tr> ";
	print "\n <tr><td><input type=\"hidden\" name=\"C5CACHID\" value=\"" . rtrim ( $row ['C5CACHID'] ) . "\"></td></tr> ";
	
	Build_Fld_Entry ( "Record ID", "C5RECID", "inputnmbr", "", "C5RECID", $row [C5RECID], $Err_C5RECID, "15", "15", "", "", "" );
	// Build_Fld_Entry ( "Corrected Unique Record ID", "C5CURID", "inputalph", "", "C5CURID", $row [C5CURID], $Err_C5CURID, "64", "64", "", "", "" );
	// print "\n <tr><td class=\"dsphdr\">Corrected Unique Record ID</td><td class=\"dspalph\">{$row [C5CURID]}</td></tr>";
	print "\n <tr><td class=\"dsphdr\">Corrected Unique Record ID</td><td><input type=\"hidden\" name=\"C5CURID\" value=\"" . rtrim ( $row ['C5CURID'] ) . "\">{$row [C5CURID]}</td></tr>";
	Build_Fld_Entry ( "First Name", "C5EFNAM", "inputalph", "", "C5EFNAM", $row [C5EFNAM], $Err_C5EFNAM, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Middle Name", "C5EMNAM", "inputalph", "", "C5EMNAM", $row [C5EMNAM], $Err_C5EMNAM, "64", "64", "", "", "" );
	Build_Fld_Entry ( "Last Name", "C5ELNAM", "inputalph", "", "C5ELNAM", $row [C5ELNAM], $Err_C5ELNAM, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Social Security Number", "C5ESSOC", "inputalph", "", "C5ESSOC", $row [C5ESSOC], $Err_C5ESSOC, "9", "9", "", "", "" );
	Build_Fld_Entry ( "TIN Request Type", "C5TINRT", "inputalph", "", "C5TINRT", $row [C5TINRT], $Err_C5TINRT, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Address Line 1", "C5ADDR1", "inputalph", "", "C5ADDR1", $row [C5ADDR1], $Err_C5ADDR1, "64", "64", "Y", "", "" );
	Build_Fld_Entry ( "Address Line 2", "C5ADDR2", "inputalph", "", "C5ADDR2", $row [C5ADDR2], $Err_C5ADDR2, "64", "64", "", "", "" );
	Build_Fld_Entry ( "City", "C5CITY", "inputalph", "", "C5CITY", $row [C5CITY], $Err_C5CITY, "64", "64", "Y", "", "" );
	// State
	$fieldDesc = RetValue ( "STID='$row[C5STATE]'", "HDSTID", "STDESC" );
	$textOvr = SetTextOvr ( $Err_C5STATE );
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>State ID</span></td>";
	print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"C5STATE\" value=\"" . rtrim ( $row ['C5STATE'] ) . "\" size=\"3\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=C5STATE&amp;fldDesc=C5STATEDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"C5STATEDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg ( $Err_C5STATE );
	
	Build_Fld_Entry ( "Postal Code", "C5PSTCD", "inputalph", "", "C5PSTCD", $row [C5PSTCD], $Err_C5PSTCD, "10", "10", "Y", "", "" );
	Build_Fld_Entry ( "Employee Age", "C5EEAGE", "inputnmbr", "", "C5EEAGE", $row ['C5EEAGE'], $Err_C5EEAGE, "3", "3", "", "", "" );
	Build_Fld_Entry ( "Plan Start Month", "C5PSMO", "inputnmbr", "", "C5PSMO", $row ['C5PSMO'], $Err_C5PSMO, "2", "2", "Y", "", "" );
	Build_Fld_Entry ( "Coverage Code All 12 Months", "C5CVCDA", "inputalph", "ACACOVCDE", "C5CVCDA", $row [C5CVCDA], $Err_C5CVCDA, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - January", "C5CVCD01", "inputalph", "ACACOVCDE", "C5CVCD01", $row [C5CVCD01], $Err_C5CVCD01, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - February", "C5CVCD02", "inputalph", "ACACOVCDE", "C5CVCD02", $row [C5CVCD02], $Err_C5CVCD02, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - March", "C5CVCD03", "inputalph", "ACACOVCDE", "C5CVCD03", $row [C5CVCD03], $Err_C5CVCD03, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - April", "C5CVCD04", "inputalph", "ACACOVCDE", "C5CVCD04", $row [C5CVCD04], $Err_C5CVCD04, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - May", "C5CVCD05", "inputalph", "ACACOVCDE", "C5CVCD05", $row [C5CVCD05], $Err_C5CVCD05, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - June", "C5CVCD06", "inputalph", "ACACOVCDE", "C5CVCD06", $row [C5CVCD06], $Err_C5CVCD06, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - July", "C5CVCD07", "inputalph", "ACACOVCDE", "C5CVCD07", $row [C5CVCD07], $Err_C5CVCD07, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - August", "C5CVCD08", "inputalph", "ACACOVCDE", "C5CVCD08", $row [C5CVCD08], $Err_C5CVCD08, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - September", "C5CVCD09", "inputalph", "ACACOVCDE", "C5CVCD09", $row [C5CVCD09], $Err_C5CVCD09, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - October", "C5CVCD10", "inputalph", "ACACOVCDE", "C5CVCD10", $row [C5CVCD10], $Err_C5CVCD10, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - November", "C5CVCD11", "inputalph", "ACACOVCDE", "C5CVCD11", $row [C5CVCD11], $Err_C5CVCD11, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Coverage Code - December", "C5CVCD12", "inputalph", "ACACOVCDE", "C5CVCD12", $row [C5CVCD12], $Err_C5CVCD12, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Employee Share All 12 Months", "C5EMSHA", "inputnmbr", "", "C5EMSHA", $row [C5EMSHA], $Err_C5EMSHA, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - January", "C5EMSH01", "inputnmbr", "", "C5EMSH01", $row [C5EMSH01], $Err_C5EMSH01, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - February", "C5EMSH02", "inputnmbr", "", "C5EMSH02", $row [C5EMSH02], $Err_C5EMSH02, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - March", "C5EMSH03", "inputnmbr", "", "C5EMSH03", $row [C5EMSH03], $Err_C5EMSH03, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - April", "C5EMSH04", "inputnmbr", "", "C5EMSH04", $row [C5EMSH04], $Err_C5EMSH04, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - May", "C5EMSH05", "inputnmbr", "", "C5EMSH05", $row [C5EMSH05], $Err_C5EMSH05, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - June", "C5EMSH06", "inputnmbr", "", "C5EMSH06", $row [C5EMSH06], $Err_C5EMSH06, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - July", "C5EMSH07", "inputnmbr", "", "C5EMSH07", $row [C5EMSH07], $Err_C5EMSH07, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - August", "C5EMSH08", "inputnmbr", "", "C5EMSH08", $row [C5EMSH08], $Err_C5EMSH08, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - September", "C5EMSH09", "inputnmbr", "", "C5EMSH09", $row [C5EMSH09], $Err_C5EMSH09, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - October", "C5EMSH10", "inputnmbr", "", "C5EMSH10", $row [C5EMSH10], $Err_C5EMSH10, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - November", "C5EMSH11", "inputnmbr", "", "C5EMSH11", $row [C5EMSH11], $Err_C5EMSH11, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Employee Share - December", "C5EMSH12", "inputnmbr", "", "C5EMSH12", $row [C5EMSH12], $Err_C5EMSH12, "15", "15", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor All 12 Months", "C5SHBRA", "inputalph", "", "C5SHBRA", $row [C5SHBRA], $Err_C5SHBRA, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - January", "C5SHBR01", "inputalph", "", "C5SHBR01", $row [C5SHBR01], $Err_C5SHBR01, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - February", "C5SHBR02", "inputalph", "", "C5SHBR02", $row [C5SHBR02], $Err_C5SHBR02, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - March", "C5SHBR03", "inputalph", "", "C5SHBR03", $row [C5SHBR03], $Err_C5SHBR03, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - April", "C5SHBR04", "inputalph", "", "C5SHBR04", $row [C5SHBR04], $Err_C5SHBR04, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - May", "C5SHBR05", "inputalph", "", "C5SHBR05", $row [C5SHBR05], $Err_C5SHBR05, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - June", "C5SHBR06", "inputalph", "", "C5SHBR06", $row [C5SHBR06], $Err_C5SHBR06, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - July", "C5SHBR07", "inputalph", "", "C5SHBR07", $row [C5SHBR07], $Err_C5SHBR07, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - August", "C5SHBR08", "inputalph", "", "C5SHBR08", $row [C5SHBR08], $Err_C5SHBR08, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - September", "C5SHBR09", "inputalph", "", "C5SHBR09", $row [C5SHBR09], $Err_C5SHBR09, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - October", "C5SHBR10", "inputalph", "", "C5SHBR10", $row [C5SHBR10], $Err_C5SHBR10, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - November", "C5SHBR11", "inputalph", "", "C5SHBR11", $row [C5SHBR11], $Err_C5SHBR11, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Section 4980H Safe Harbor - December", "C5SHBR12", "inputalph", "", "C5SHBR12", $row [C5SHBR12], $Err_C5SHBR12, "2", "2", "", "", "" );
	Build_Fld_Entry ( "Zip Code All 12 Months", "C5ZIPA", "inputalph", "", "C5ZIPA", $row ['C5ZIPA'], $Err_C5ZIPA, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - January", "C5ZIP01", "inputalph", "", "C5ZIP01", $row ['C5ZIP01'], $Err_C5ZIP01, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - February", "C5ZIP02", "inputalph", "", "C5ZIP02", $row ['C5ZIP02'], $Err_C5ZIP02, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - March", "C5ZIP03", "inputalph", "", "C5ZIP03", $row ['C5ZIP03'], $Err_C5ZIP03, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - April", "C5ZIP04", "inputalph", "", "C5ZIP04", $row ['C5ZIP04'], $Err_C5ZIP04, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - May", "C5ZIP05", "inputalph", "", "C5ZIP05", $row ['C5ZIP05'], $Err_C5ZIP05, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - June", "C5ZIP06", "inputalph", "", "C5ZIP06", $row ['C5ZIP06'], $Err_C5ZIP06, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - July", "C5ZIP07", "inputalph", "", "C5ZIP07", $row ['C5ZIP07'], $Err_C5ZIP07, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - August", "C5ZIP08", "inputalph", "", "C5ZIP08", $row ['C5ZIP08'], $Err_C5ZIP08, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - September", "C5ZIP09", "inputalph", "", "C5ZIP09", $row ['C5ZIP09'], $Err_C5ZIP09, "5", "5", "", "", "" );
    Build_Fld_Entry ( "Zip Code - October", "C5ZIP10", "inputalph", "", "C5ZIP10", $row ['C5ZIP10'], $Err_C5ZIP10, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - November", "C5ZIP11", "inputalph", "", "C5ZIP11", $row ['C5ZIP11'], $Err_C5ZIP11, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Zip Code - December", "C5ZIP12", "inputalph", "", "C5ZIP12", $row ['C5ZIP12'], $Err_C5ZIP12, "5", "5", "", "", "" );
	Build_Fld_Entry ( "Covered Individual", "C5CVIND", "inputalph", "BY", "C5CVIND", $row [C5CVIND], $Err_C5CVIND, "1", "1", "", "", "" );
	
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

/**
 * Add Affordable Care Act 1095 Cache Dependent row
 *
 * @param array $data Array of Affordable Care Act 1095C Cache Dependent to be added
 */
function add1095CCacheDep(array $data) {
	global $i5Connect;
	
	// Load Employee into Affordable Care Act 1095 Cache Dependent
	$data = array ();
	$data ['C2CAID5'] = $aca1095CCacheRow ['C5CACHID'];
	$data ['C2DFNAM'] = $aca1095CCacheRow ['C5EFNAM'];
	$data ['C2DMNAM'] = $aca1095CCacheRow ['C5EMNAM'];
	$data ['C2DLNAM'] = $aca1095CCacheRow ['C5ELNAM'];
	$data ['C2DSSOC'] = $aca1095CCacheRow ['C5ESSOC'];
	$data ['C2TINRT'] = $aca1095CCacheRow ['C5TINRT'];
	
	$data ['C2CVCDA'] = '';
	$elementName = 'C2CVCD';
	$elementName2 = 'C5CVCD';
	for($mm = 1; $mm <= 12; $mm ++) {
		$mm2 = substr ( sprintf ( "%'.02d\n", $mm ), 0, 2 );
		$elementMonth = $elementName . $mm2;
		$elementMonth2 = $elementName2 . $mm2;
		$data [$elementMonth] = $aca1095CCacheRow [$elementMonth2];
	}
	
	// Set the Coverage Code ALL 12 Months to January Coverage Code
	$data ['C2CVCDA'] = $data ['C2CVCD01'];
	$elementName = 'C2CVCD';
	for($mm = 2; $mm <= 12; $mm ++) {
		$mm2 = substr ( sprintf ( "%'.02d\n", $mm ), 0, 2 );
		$elementName2 = $elementName . $mm2;
		// If any months Coverage Code is different, remove the Coverage Code ALL 12 Months
		if ($data [$elementName2] != $data ['C2CVCD01']) {
			$data ['C2CVCDA'] = '';
			break;
		}
	}
	
	// Load Affordable Care Act 1095C Dependent Cache
	$stmtSQL = " Insert Into HRACC2 (C2CAID5,C2DFNAM,C2DMNAM,C2DLNAM,C2DSUFF,
			                         C2DSSOC,C2TINRT,C2CVCDA,C2CVCD01,C2CVCD02,C2CVCD03,
			                         C2CVCD04,C2CVCD05,C2CVCD06,C2CVCD07,C2CVCD08,C2CVCD09,
			                         C2CVCD10,C2CVCD11,C2CVCD12) ";
	$stmtSQL .= " Values ({$data ['C2CAID5']},'{$data ['C2DFNAM']}','{$data ['C2DMNAM']}','{$data ['C2DLNAM']}','{$data ['C2DSUFF']}',
	'{$data ['C2DSSOC']}','{$data ['C2TINRT']}','{$data ['C2CVCDA']}','{$data ['C2CVCD01']}','{$data ['C2CVCD02']}','{$data ['C2CVCD03']}',
	'{$data ['C2CVCD04']}','{$data ['C2CVCD05']}','{$data ['C2CVCD06']}','{$data ['C2CVCD07']}','{$data ['C2CVCD08']}','{$data ['C2CVCD09']}',
	'{$data ['C2CVCD10']}','{$data ['C2CVCD11']}','{$data ['C2CVCD12']}') ";
	$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	
	// If row not added, set identity column and try again
	if (! $status) {
		Check_Identity_Column ( 'HRACC2', 'C2DCID5', $stmtSQL );
	}
}

?>