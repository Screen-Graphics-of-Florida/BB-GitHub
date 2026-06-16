<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$backHome             = $_GET['backHome'];
$procOption           = $_GET['procOption'];
$procDesc             = $_GET['procDesc'];
$submitPageTitle      = $_GET['submitPageTitle'];
$submitCallProgram    = $_GET['submitCallProgram'];
$submitEnvProgram     = $_GET['submitEnvProgram'];
$submitEnvPrinter     = $_GET['submitEnvPrinter'];
$submitScheduleScript = $_GET['submitScheduleScript'];
$applicationID        = $_GET['applicationID'];

require($baseVar);
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$scriptName    = "FaxDataQueue.php";
$scriptVarBase = "{$altVarBase}&amp;submitPageTitle=" . urlencode($submitPageTitle) . "&amp;submitCallProgram=" . urlencode($submitCallProgram) . "&amp;submitEnvProgram=" . urlencode($submitEnvProgram) . "&amp;submitEnvPrinter=" . urlencode($submitEnvPrinter) . "&amp;submitScheduleScript=" . urlencode($submitScheduleScript) . "&amp;apid=SY&amp;backHome=" . urlencode($backHome);

$jobInProcess=RetValue("SPPREF='FXDQ' and SPAPID='SY'", "SYAPFF", "SPJOBA");
$alertMessage="";
if (($procOption == "S" || $procOption == "R")  && $jobInProcess == "Y") {
	if ($procOption == "S") {$alertMessage=Rtv_Error_Desc("HHD0435");}
	else {$alertMessage=Rtv_Error_Desc("HHD0436");}
} elseif ($procOption == "E" && $jobInProcess != "Y") {
	$alertMessage=Rtv_Error_Desc("HHD0433");
}

if ($alertMessage != "") {
	print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;alertMessage=" . urlencode($alertMessage) . "\">";
} else {
	print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$cGIPath}SubmitJob.d2w/REPORT{$scriptVarBase}\">";
}
?>
