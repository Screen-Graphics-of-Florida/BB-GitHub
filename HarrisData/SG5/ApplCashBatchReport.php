<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$backHome           = $_GET['backHome'];
$errFound           = $_GET['errFound'];
$jobSbmSched        = $_GET['jobSbmSched'];
$resetSelectionFlag = $_GET['resetSelectionFlag'];
$rtvSelection       = $_GET['rtvSelection'];
$saveSelection      = $_GET['saveSelection'];
$scheduleJobSwitch  = $_GET['scheduleJobSwitch'];
$selScheduleJob     = $_GET['selScheduleJob'];
$submitSchedule     = $_GET['submitSchedule'];

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$WFReview           = $_GET['WFReview'];
$wfInstance         = $_GET['wfInstance'];
$wfInstanceDate     = $_GET['wfInstanceDate'];
$wfWorkItem         = $_GET['wfWorkItem'];
$wfWorkItemSequence = $_GET['wfWorkItemSequence'];
$wfParticipantId    = $_GET['wfParticipantId'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title            = "Application of Cash Balance Report";
$scriptName            = "ApplCashBatchReport.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$submitNoSelection     = "N";
$submitCallProgram     = "CARCCR";
$submitEnvProgram      = "HARCCR";
$submitEnvPrinter      = "HARCCRPF";
$submitScheduleScript  = "ApplCashBatch.php";
$applicationID         = "AR";

if (is_null($tag)) {$tag="REPORT";}

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
	require_once 'NoFormValidate.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARBATCHREPORT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	require 'stmtSQLClear.php';
	$stmtSQL   .= " Select  BMBCHN,BMBCHD,BMBCHB,BMBCHT,BMPMTE,BMBCHS,BMDEPA,BMADJT,";
	$stmtSQL   .= " coalesce(BKBKNM,' ') as BKBKNM, ";
	$stmtSQL   .= " coalesce(a.FLDESC,' ') as FLDESC_BMBCHT,";
	$stmtSQL   .= " coalesce(b.FLDESC,' ') as FLDESC_BMPMTE,";
	$stmtSQL   .= " coalesce(c.FLDESC,' ') as FLDESC_BMBCHS ";
	$fileSQL   .= " ARPBCH ";
	$fileSQL   .= " left join HDBANK on BKBANK=BMBCHB ";
	$fileSQL   .= " left join SYFLAG a on (a.FLTYPE,a.FLVALU)=('ARBCHTYPE',BMBCHT) ";
	$fileSQL   .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTENTR',BMPMTE) ";
	$fileSQL   .= " left join SYFLAG c on (c.FLTYPE,c.FLVALU)=('ARBCHSTAT',BMBCHS) ";
	$selectSQL .= " (BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);	
	
	print "\n <table $contentTable>";

	$F_BMBCHD=Format_Code(Format_Date($row['BMBCHD'], "D"));
	print "\n <tr><td class=\"dsphdr\">Batch</td> ";
	print "\n     <td class=\"dspnmbr\">$row[BMBCHN] $F_BMBCHD</td> ";
	print "\n </tr> ";

	$F_BMBCHB=Format_Code($row['BMBCHB']);
	print "\n <tr><td class=\"dsphdr\">Bank</td> ";
	print "\n     <td class=\"dspalph\">$row[BKBKNM] $F_BMBCHB</td> ";
	print "\n </tr> ";

	$F_BMBCHT=Format_Code($row['BMBCHT']);
	print "\n <tr><td class=\"dsphdr\">Batch Type</td> ";
	print "\n     <td class=\"dspalph\">$row[FLDESC_BMBCHT] $F_BMBCHT</td> ";
	print "\n </tr> ";

	$F_BMPMTE=Format_Code($row['BMPMTE']);
	print "\n <tr><td class=\"dsphdr\">Payment Entry</td> ";
	print "\n     <td class=\"dspalph\">$row[FLDESC_BMPMTE] $F_BMPMTE</td> ";
	print "\n </tr> ";

	$F_BMBCHS=Format_Code($row['BMBCHS']);
	print "\n <tr><td class=\"dsphdr\">Status</td> ";
	print "\n     <td class=\"dspalph\">$row[FLDESC_BMBCHS] $F_BMBCHS</td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\">Deposit Total</td> ";
	print "\n     <td class=\"dspnmbr\">" . Format_Nbr ( $row['BMDEPA'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\">Other Total</td> ";
	print "\n     <td class=\"dspnmbr\">" . Format_Nbr ( $row['BMADJT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
	print "\n </tr> ";

	print "\n </table> ";

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\">";
	print "\n <table $contentTable>";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {$errVar=ErrVarErr($profileHandle, $errVar);}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);

		require 'ScheduleJobErr.php';    // Schedule Entries Errors
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	}
	print "\n </table> ";
	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	if ($submitSchedule == "S") {
		require 'SubmitScheduleBottom.php';
		print "\n $hrTagAttr ";
	} else {
		print "\n $hrTagAttr ";
	}
	print "\n </form>";
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	$edtVar= "";
	Concat_Field("@@rqst", "CALL $submitCallProgram PARM('BROWSER' '$userProfile' '$applicationID' '$fromBatchNumber' '$fromBatchDate' '$fromBatchBank')");
	Concat_Field("@@pgid", $submitEnvProgram);
	Concat_Field("@@prtf", $submitEnvPrinter);
	Concat_Field("@@pref", $submitApplPrefix);
	Concat_Field("@@apid", $applicationID);
	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Validate_Data($profileHandle,$dataBaseID,$submitSchedule,$errFound,$edtVar,$errVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];

	require 'SubmitScheduleUpdate.php';
}

function Validate_Data($profileHandle,$dataBaseID,$submitSchedule,$errFound,$edtVar,$errVar) {
	global $pgmLibrary, $i5Connect;
	if (is_null($submitSchedule )) $submitSchedule="";
	if (is_null($errFound ))       $errFound="";
	if (is_null($edtVar ))         $edtVar="";
	if (is_null($errVar ))         $errVar="";

	$pgmCall = array(
	array("Name"=>"profileHandle",  "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"submitSchedule", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"errFound",       "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",         "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar",         "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HSYSSS_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HSYSSS_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"  =>$profileHandle,
	"dataBaseID"     =>$dataBaseID,
	"submitSchedule" =>$submitSchedule,
	"errFound"       =>$errFound,
	"edtVar"         =>$edtVar,
	"errVar"         =>$errVar);

	$parmOut = array(
	"profileHandle"  =>"profileHandle",
	"dataBaseID"     =>"dataBaseID",
	"submitSchedule" =>"submitSchedule",
	"errFound"       =>"errFound",
	"edtVar"         =>"edtVar",
	"errVar"         =>"errVar");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data (HSYSSS_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['submitSchedule'] =$submitSchedule;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	return $returnValue;
}
?>										