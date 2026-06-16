<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound = (isset ( $_GET ['errFound'] )) ? $_GET ['errFound'] : null;
$wrnVar = (isset ( $_GET ['wrnVar'] )) ? $_GET ['wrnVar'] : null;
$reportSelType = (isset ( $_GET ['reportSelType'] )) ? $_GET ['reportSelType'] : null;
$jobSbmSched = (isset ( $_GET ['jobSbmSched'] )) ? $_GET ['jobSbmSched'] : null;
$resetSelectionFlag = (isset ( $_GET ['resetSelectionFlag'] )) ? $_GET ['resetSelectionFlag'] : null;
$rtvSelection = (isset ( $_GET ['rtvSelection'] )) ? $_GET ['rtvSelection'] : null;
$saveSelection = (isset ( $_GET ['saveSelection'] )) ? $_GET ['saveSelection'] : null;
$scheduleJobSwitch = (isset ( $_GET ['scheduleJobSwitch'] )) ? $_GET ['scheduleJobSwitch'] : null;
$selScheduleJob = (isset ( $_GET ['selScheduleJob'] )) ? $_GET ['selScheduleJob'] : null;
$submitSchedule = (isset ( $_GET ['submitSchedule'] )) ? $_GET ['submitSchedule'] : null;

$fromACA1094CID = (isset ( $_GET ['fromACA1094CID'] )) ? $_GET ['fromACA1094CID'] : null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "ACA Cache To XML";
$scriptName = "ACACacheToXMLSubmit.php";
if ($fromACA1094CID === null) {
	$scriptVarBase = "{$genericVarBase}&amp;reportSelType=" . urlencode ( trim ( $reportSelType ) );
} else {
	$scriptVarBase = "{$genericVarBase}&amp;reportSelType=" . urlencode ( trim ( $reportSelType ) ) . "&amp;fromACA1094CID=" . urlencode ( trim ( $fromACA1094CID ) );
}
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection = "";
$submitCallProgram = "HPRACX";
$submitEnvProgram = "HPRACX";
$submitEnvPrinter = "";
$submitScheduleScript = "";
$applicationID = "PR";
$backURL = $_SESSION [$fromURL];

if (is_null ( $tag )) {
	$tag = "REPORT";
}

if ($tag == "REPORT") {
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
	print "\n if (document.Chg.TransCtrlCd.value ==\"\" ";
	print "\n    || document.Chg.acafile.value ==\"\" ";
	if ($fromACA1094CID === null) {
		print "\n || document.Chg.TaxYear.value ==\"\" ";
		print "\n || document.Chg.Corrected.value ==\"\" ";
	}
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	if ($fromACA1094CID === null) {
		print "\n if (editNum(document.Chg.TaxYear, 4, 0)) ";
	}
	print "\n return true;";
	print "\n }";
	print "\n </script> \n";
	
	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ACACACHETOXMLSUBMIT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "\n <table $contentTable> ";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>$page_title</h1></td> ";
	print "\n     <td class=\"toolbar\">";
	
	print "<a href=\"{$backURL}\" title=\"Back Home\">{$portalHome}</a>";
	print "\n <a href=\"javascript:check(document.Chg)\">$sbmSchdImage</a>";
	if ($submitNoSelection == "") {
		print "\n <a href=\"javascript:document.Chg.saveSelection.value='Y'; check(document.Chg)\">$sbmSchdSaveImage</a>";
		$reportCount = RetValue ( "DRD2WN<>' ' and DRD2WN='" . strtoupper ( $scriptName ) . "'", "SYD2WR", "count(*)" );
		if ($reportCount) {
			print "\n <a href=\"{$homeURL}{$cGIPath}ReportSelection.d2w/REPORT{$altVarBase}&amp;reportSelType=" . urlencode ( trim ( $reportSelType ) ) . "&amp;reportSelD2W=" . urlencode ( trim ( $scriptName ) ) . "&amp;reportSelUser=" . urlencode ( trim ( $userProfile ) ) . "&amp;rtvSelection=Y&amp;maintenanceCode=C\" onclick=\"{$searchWinVar}\">$sbmSchdRtvImage</a>";
		}
	}
	if ($allowScheduleJob != "N") {
		print "\n <a href=\"javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg)\">$sbmSchdParmImage</a>";
	}
	if ($submitNoReset == "") {
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;resetSelectionFlag=Y&amp;timeStamp=" . urlencode ( $_SERVER ['REQUEST_TIME'] ) . "\">$sbmSchdResetImage</a> ";
	}
	
	require_once 'HelpPage.php';
	print "</td></tr></table>";
	
	if ($fromACA1094CID !== null) {
		// ACA 1094C Cache Information
		$stmtSQL = " Select * From HRACC4 Where C4CACHID={$fromACA1094CID} ";
		$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		$aca1094CRow = db2_fetch_assoc ( $sqlResult );
		print "\n <table $contentTable style=\"float:left;\"> ";
		Format_Header ( "Tax Year", $aca1094CRow ['C4TXYR'], "" );
		Format_Header ( "Business", $aca1094CRow ['C4BNAM1'], "" );
		Format_Header ( "EIN", $aca1094CRow ['C4PYTIN'], "" );
		print "\n </table> ";
		print "\n <br style=\"clear:both;\"> ";
	}
	
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
	
	$focusField = "acafile";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField = "";
		$edtVar = EdtVarErr ( $profileHandle, $edtVar );
		if ($errFound != "") {
			$errVar = ErrVarErr ( $profileHandle, $errVar );
			$Err_ACAFILE = DecatErr_Field ( "@@acaf", "acafile" );
			require 'ScheduleJobErr.php'; // Schedule Entries Errors
		}
		$submitSchedule = Decat_Field ( "@@sbjb", $edtVar );
		
		$validationError = Decat_Field ( "@@verr", $errVar );
		if (trim ( $validationError ) == "Y") {
			$errorMsg = 'Errors found during validation.  Review the Create ACA XML Error Report.';
			print "\n <span class=\"error\">$errorMsg</span>";
		}
		$ACAFILE = Decat_Field ( "@@acaf", $edtVar );
		$TCC = Decat_Field ( "@@tcc@", $edtVar );
		if ($fromACA1094CID === null) {
			$TXYR = Decat_Field ( "@@txyr", $edtVar );
			$CORR = Decat_Field ( "@@corr", $edtVar );
		}
		require 'ScheduleJobValue.php'; // Schedule Entries Values
	} else {
		$ACAFILE = "";
		$TCC = "";
		$TXYR = "";
		$CORR = "";
	}
	
	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode ( trim ( $wrnVar ) ) . "\">";
	
	print "\n     <table $contentTable> ";
	
	// Test File Code
	Build_Flag_Entry ( "File Code", "acafile", "ACAFILECD", "acafile", $ACAFILE, $Err_ACAFILE, "1", "1", "Y", "", "" );
	
	// Transmitter Control Code
	print "\n         <tr><td class=\"dsphdr\">Transmitter Control Code</td> ";
	Build_Fld_Entry ( "", "TransCtrlCd", "inputalph", "", "", rtrim ( $TCC ), "", "5", "5", "Y", "", "Y" );
	print "\n </tr> ";
	
	if ($fromACA1094CID === null) {
		// Tax Year
		Build_Fld_Entry ( "Tax Year", "TaxYear", "inputnmbr", "", "TaxYear", $TXYR, "", "4", "4", "Y", "", "" );
		
		// Correction
		print "\n         <tr><td class=\"dsphdr\">Corrected</td> ";
		print "\n             <td class=\"inputcode\"><input name=\"Corrected\" type=\"radio\" VALUE='O'>Original &nbsp; <input  name=\"Corrected\" type=\"radio\" VALUE='C' >Corrected &nbsp; <input  name=\"Corrected\" type=\"radio\" VALUE='R' >Replacement</td> ";
		print "\n </tr> ";
	}
	print "\n     </table> ";
	
	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	print "\n $hrTagAttr ";
	
	if ($focusField != "") {
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n document.Chg.$focusField.focus(); ";
		print "\n </script> ";
	}
	print "\n </form>";
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit ();
}

if ($tag == "Edit_Data") {
	$edtVar = "";
	Concat_Field ( "@@user", $userProfile );
	Concat_Field ( "@@acaf", strtoupper ( $_POST ['acafile'] ) );
	Concat_Field ( "@@tcc@", $_POST ['TransCtrlCd'] );
	if ($fromACA1094CID === null) {
		Concat_Field ( "@@txyr", $_POST ['TaxYear'] );
		Concat_Field ( "@@corr", $_POST ['Corrected'] );
	} else {
		Concat_Field ( "@@id94", $fromACA1094CID );
	}
	
	require 'ScheduleJobConcat.php'; // Schedule Entries Values
	$edtVar .= "}{";
	
	$returnValue = Selection_Edit_Handle ( "HPRACX_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar );
	$submitSchedule = $returnValue ['submitSchedule'];
	$errFound = $returnValue ['errFound'];
	$edtVar = $returnValue ['edtVar'];
	$errVar = $returnValue ['errVar'];
	$wrnVar = $returnValue ['wrnVar'];
	
	require 'SubmitScheduleUpdate.php';
	exit ();
}

?>