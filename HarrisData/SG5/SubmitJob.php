<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$submitPageTitle      = $_GET['submitPageTitle'];
$submitCallProgram    = $_GET['submitCallProgram'];
$submitEnvProgram     = $_GET['submitEnvProgram'];
$submitEnvPrinter     = $_GET['submitEnvPrinter'];
$submitScheduleScript = $_GET['submitScheduleScript'];
$applicationID        = $_GET['applicationID'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'GenericDirectCallVariables.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

require 'SubmitScheduleVar.php';

$page_title              = "";
$scriptName              = "SubmitJob.php";
$scriptVarBase           = "{$genericVarBase}&amp;submitPageTitle=" . urlencode($submitPageTitle) . "&amp;submitCallProgram=" . urlencode($submitCallProgram) . "&amp;submitEnvProgram=" . urlencode($submitEnvProgram) . "&amp;submitEnvPrinter=" . urlencode($submitEnvPrinter) . "&amp;submitScheduleScript=" . urlencode($submitScheduleScript) . "&amp;applicationID=" . urlencode($applicationID) . "&amp;backHome=" . urlencode($backHome);
$DATABASE                = "*LOCAL";
$submitNoSelection       = "N";
$submitNoReset           = "N";

if ($tag == "SUBMIT") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CheckEnterChg.php';
	require_once 'NoFormValidate.php';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "SUBMITNOSELECTION";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require_once 'SubmitScheduleTop.php';

	print $hrTagAttr;
	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	if (errFound != "" || scheduleJobSwitch == "Y"){
		$scheduleJobSwitch = "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$submitSchedule = Decat_Field("@@sbjb");

		require 'ScheduleJobErr.php';  // Schedule Entries Errors
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	}

	print "\n </table> ";

	$envProgram =  $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	if ($submitSchedule == "S"){
		require 'SubmitScheduleBottom.php';
		print "$hrTagAttr";
	}
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	$edtVar= "";
	$edtVar = "";
	Concat_Field("@@rqst", "CALL $submitCallProgram PARM('BROWSER' '$userProfile' '$applicationID')");
	Concat_Field("@@pgid", $submitEnvProgram);
	Concat_Field("@@prtf", $submitEnvPrinter);
	Concat_Field("@@pref", $submitApplPrefix);
	Concat_Field("@@apid", $applicationID);
	require 'ScheduleJobConcat.php';    // Schedule Entries Values
	$edtVar .= "}{{$edtVar}";
	Validate_Data($profileHandle, $dataBaseID,$submitSchedule, $errFound, $edtVar, $errVar);

	require 'SubmitScheduleUpdate.php';
}

FUNCTION Validate_Data($profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar){

	$userProfile = urlencode($_SERVER['PHP_AUTH_USER']);

	if (!$i5Connect) die("<br>Submit Job Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"userProfile", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"submitSchedule", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"errFound", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	);

	$pgm = i5_program_prepare("HSYSSS_W", $pgmCall);

	if (!$pgm) {
		die("<br> HSYSSS from submitJob Program prepare errno=".i5_errno()." msg=".i5_errormsg());
	}
	$parmIn = array(
	"profileHandle"=>$profileHandle,
	"dataBaseID"=>$dataBaseID,
	"userProfile"=>$userProfile,
	"userCustomer"=>$submitSchedule,
	"userSalesman"=>$errFound,
	"userBadge"=>$edtVar,
	"userCatalog"=>$errVar,
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"dataBaseID"   =>"dataBaseID",
	"userProfile"  =>"userProfile",
	"userCustomer" =>"submitSchedule",
	"userVendor"   =>"errFound",
	"userSalesman" =>"edtVar",
	"userBadge"    =>"errVar",
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	
	if (!$ret) {
		die("<br>HSYSCC in Schedule Job Program call errno=".i5_errno()." msg=".i5_errormsg() );
	}
	i5_close($i5Connect);
}

?>
