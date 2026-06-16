<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound = $_GET ['errFound'];
$wrnVar = $_GET ['wrnVar'];
$fromACA1094CID = $_GET ['fromACA1094CID'];
$fromACA1095CID = $_GET ['fromACA1095CID'];
$jobSbmSched = $_GET ['jobSbmSched'];
$resetSelectionFlag = $_GET ['resetSelectionFlag'];
$rtvSelection = $_GET ['rtvSelection'];
$saveSelection = $_GET ['saveSelection'];
$scheduleJobSwitch = $_GET ['scheduleJobSwitch'];
$selScheduleJob = $_GET ['selScheduleJob'];
$submitSchedule = $_GET ['submitSchedule'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'VarBase.php';

$stmtSQL = " Select * From HRACC4 Where C4CACHID={$fromACA1094CID} ";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$aca1094CRow = db2_fetch_assoc ( $sqlResult );

if ($fromACA1095CID > 0) {
	$stmtSQL = " Select * From HRACC5 Where C5CACHID={$fromACA1095CID} ";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$aca1095CRow = db2_fetch_assoc ( $sqlResult );
}

$page_title = "ACA 1095C Report";
$scriptName = "ACA1095CReport.php";
$scriptVarBase = "{$genericVarBase}&amp;reportSelType=" . urlencode ( trim ( $reportSelType ) ) . "&amp;fromACA1094CID=" . urlencode ( trim ( $fromACA1094CID ) ) . "&amp;fromACA1095CID=" . urlencode ( trim ( $fromACA1095CID ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection = "Y";
$submitNoReset = "N";
$submitCallProgram = "CPR1095";
$submitEnvProgram = "HPR1095";
$submitEnvPrinter = "HPR1095PF";
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
	require_once 'NoFormValidate.php';
	print "\n </script> \n";
	
	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ACA1095CREPORT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "\n <table $contentTable> ";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>$page_title</h1></td> ";
	print "\n     <td class=\"toolbar\">";
	
	print "<a href=\"{$backURL}\" title=\"Back Home\">{$portalHome}</a>";
	print "\n <a href=\"javascript:check(document.Chg)\">$sbmSchdImage</a>";
	if ($allowScheduleJob != "N") {
		print "\n <a href=\"javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg)\">$sbmSchdParmImage</a>";
	}
	
	require_once 'HelpPage.php';
	print "</td></tr></table>";
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;
	
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField = "";
		$edtVar = EdtVarErr ( $profileHandle, $edtVar );
		if ($errFound != "") {
			$errVar = ErrVarErr ( $profileHandle, $errVar );
			require 'ScheduleJobErr.php'; // Schedule Entries Errors
		}
		$submitSchedule = Decat_Field ( "@@sbjb", $edtVar );
		require 'ScheduleJobValue.php'; // Schedule Entries Values
	}
	
	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode ( trim ( $wrnVar ) ) . "\">";
	
	print "\n     <table $contentTable> ";
	Build_DspFld ( "Tax Year", $aca1094CRow ['C4TXYR'], "", "N" );
	Build_DspFld ( "Business", $aca1094CRow ['C4BNAM1'], "", "A" );
	Build_DspFld ( "EIN", $aca1094CRow ['C4PYTIN'], "", "A" );
	
	if ($fromACA1095CID > 0) {
		print "\n <tr><td>&nbsp;</td></tr> ";
		$name = $aca1095CRow ['C5EFNAM'] . ' ' . $aca1095CRow ['C5ELNAM'];
		Build_DspFld ( "Employee", $name, "", "A" );
	}
	if ($aca1094CRow['C4TXYR'] < 2019) {
	    print "\n <tr><td>&nbsp;</td></tr> ";
	    Build_Fld_Entry ( "Print on Self Mailer", "selfMailer", "inputalph", "BY", "selfMailer", "", "", "1", "1", "", "", "" );
	}
	print "\n     </table> ";
	
	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	print "\n $hrTagAttr ";
	print "\n </form>";
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit ();
}

if ($tag == "Edit_Data") {
	$edtVar = "";
	Concat_Field ( "@@id94", $fromACA1094CID );
	Concat_Field ( "@@id95", $fromACA1095CID );
	$self = ($_POST ['selfMailer'] == 'Y') ? 'Y' : '';
	Concat_Field ( "@@self", $self);
	Concat_Field ( "@@txyr", $aca1094CRow['C4TXYR']);
	
	require 'ScheduleJobConcat.php'; // Schedule Entries Values
	$edtVar .= "}{";
	
	$returnValue = Selection_Edit_Handle ( "HPRACA_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar );
	$submitSchedule = $returnValue ['submitSchedule'];
	$errFound = $returnValue ['errFound'];
	$edtVar = $returnValue ['edtVar'];
	$errVar = $returnValue ['errVar'];
	$wrnVar = $returnValue ['wrnVar'];
	
	require 'SubmitScheduleUpdate.php';
	exit ();
}

?>	
