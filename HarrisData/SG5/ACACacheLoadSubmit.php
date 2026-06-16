<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound = $_GET ['errFound'];
$wrnVar = $_GET ['wrnVar'];
$fromACAEINID = $_GET ['fromACAEINID'];
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

$stmtSQL = " Select * From HRACAE Where EIEINID={$fromACAEINID} ";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$einRow = db2_fetch_assoc ( $sqlResult );

$page_title = "Load ACA Cache";
$scriptName = "ACACacheLoadSubmit.php";
$scriptVarBase = "{$genericVarBase}&amp;reportSelType=" . urlencode ( trim ( $reportSelType ) ) . "&amp;fromACAEINID=" . urlencode ( trim ( $fromACAEINID ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection = "Y";
$submitNoReset = "N";
$submitCallProgram = "HPREIN";
$submitEnvProgram = "HPREIN";
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
	require_once 'NoFormValidate.php';
	print "\n </script> \n";
	
	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ACALOADCACHESUBMIT";
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
	Build_DspFld ( "Tax Year", $einRow ['EITXYR'], "", "N" );
	Build_DspFld ( "Description", $einRow ['EIDESC'], "", "A" );
	Build_DspFld ( "EIN", $einRow ['EIEIN'], "", "A" );
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
	Concat_Field ( "@@ein@", $fromACAEINID );
	
	require 'ScheduleJobConcat.php'; // Schedule Entries Values
	$edtVar .= "}{";
	
	$returnValue = Selection_Edit_Handle ( "HPREIN_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar );
	$submitSchedule = $returnValue ['submitSchedule'];
	$errFound = $returnValue ['errFound'];
	$edtVar = $returnValue ['edtVar'];
	$errVar = $returnValue ['errVar'];
	$wrnVar = $returnValue ['wrnVar'];
	
	require 'SubmitScheduleUpdate.php';
	exit ();
}

?>	
