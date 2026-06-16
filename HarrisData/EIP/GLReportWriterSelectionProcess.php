<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$reportSelType      = $_GET['reportSelType'];
$jobSbmSched        = $_GET['jobSbmSched'];
$resetSelectionFlag = $_GET['resetSelectionFlag'];
$rtvSelection       = $_GET['rtvSelection'];
$saveSelection      = $_GET['saveSelection'];
$scheduleJobSwitch  = $_GET['scheduleJobSwitch'];
$selScheduleJob     = $_GET['selScheduleJob'];
$submitSchedule     = $_GET['submitSchedule'];

$fromRwSelection    = $_GET['fromRwSelection'];

$backHome           = "hdList.php";
$tblID              = "178";

require_once 'SetLibraryList.php';

require_once "GLControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title            = "Process G/L Report Writer";
$scriptName            = "GLReportWriterSelectionProcess.php";
$scriptVarBase         = "{$genericVarBase}&amp;fromRwSelection=" . urlencode(trim($fromRwSelection)) . "&amp;reportSelType=" . urlencode(trim($reportSelType)) . "&amp;tblID=" . urlencode(trim($tblID));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CGLRWP";
$submitEnvProgram      = "CGLRWP";
$submitEnvPrinter      = "";
$submitScheduleScript  = "";
$applicationID         = "GL";
$submitNoSelection     = "N";

if (is_null($tag)) {$tag="REPORT";}

if ($tag == "REPORT") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CheckEnterChg.php';
	require_once 'NoFormValidate.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "GLREPORTWRITERSELECT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	$stmtSQL .= " Select GIGRSD,GIOOPT,GIFILE,GILIBR ";
	$stmtSQL .= " From GLWRSM ";
	$stmtSQL .= " Where GIGRSN='$fromRwSelection' ";
	require 'stmtSQLEnd.php';
	require 'SubmitScheduleTop.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	print "\n <table $contentTable> ";
	Format_Header("Selection Name", $row['GIGRSD'], $fromRwSelection);
	$FLDESC=RetValue("(FLTYPE,FLVALU)=('RWOUTPUT','$row[GIOOPT]') ", "SYFLAG", "FLDESC");
	Format_Header("Output Type", $FLDESC, $row['GIOOPT']);
	print "\n </table> ";

	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;
	require_once 'ErrorDisplay.php';

	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_GIGRSN=DecatErr_Field("@@grsn", "fromRwSelection");
			$Err_GIFILE=DecatErr_Field("@@file", "rwTable");
			$Err_GILIBR=DecatErr_Field("@@libr", "rwSchema");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$row['GIFILE']=Decat_Field("@@file", $edtVar);
		$row['GILIBR']=Decat_Field("@@libr", $edtVar);
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	}

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";
	print "\n     <table $contentTable> ";
	DspErrMsg($Err_GIGRSN);

	if ($row['GIOOPT']=="F" || $row['GIOOPT']=="S") {
		Build_Fld_Entry("Table","rwTable","inputalph","","GIFILE",$row['GIFILE'],$Err_GIFILE,"10","10","","","");
		Build_Fld_Entry("Schema","rwSchema","inputalph","","GILIBR",$row['GILIBR'],$Err_GILIBR,"10","10","","","");
	}
	print "\n     </table> ";
	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	require 'SubmitScheduleBottom.php';
	print "\n $hrTagAttr ";

	if ($focusField !="") {
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n document.Chg.$focusField.focus(); ";
		print "\n </script> ";
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
	Concat_Field("@@grsn", $fromRwSelection);

	if (!isset($_POST['rwTable'])) {$_POST['rwTable']="";}  Concat_Field("@@file", strtoupper($_POST['rwTable']));
	if (!isset($_POST['rwSchema'])) {$_POST['rwSchema']="";}  Concat_Field("@@libr", strtoupper($_POST['rwSchema']));
	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HGLRWS_WP", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	

	
