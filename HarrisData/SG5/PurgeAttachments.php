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

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title            = "Purge Attachments";
$scriptName            = "PurgeAttachments.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$submitNoSelection     = "N";
$submitCallProgram     = "";
$submitEnvProgram      = "HSYPGA";
$submitEnvPrinter      = "";
$submitScheduleScript  = "PurgeAttachments.php";
$applicationID         = "AR";

if ($tag == "REPORT") {
	$returnValue=Check_Authority($userProfile,$userAuthority);
	$userAuthority=$returnValue['userAuthority'];
	if (strpos($userAuthority, "*ALLOBJ") === false){
		$alertMessage = "Purge Attachments requires *ALLOBJ authority to run";
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;alertMessage=" . urlencode(trim($alertMessage)) . "\"> ";
		exit;
	}
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

	print "\n   function validate(chgForm) {";
	print "\n     if (document.Chg.byCriteria.checked == false && document.Chg.byDate.checked == false)";
	print "\n        {alert(\"You must select at least one Purge Based On option\"); return false;}";
	print "\n     if (document.Chg.byDate.checked && document.Chg.purgeDate.value == \"\")";
	print "\n        {alert(\"Date is required when Purge Based On Date selected\"); return false;}";
	print "\n     if (editNum(document.Chg.purgeDate, 6, 0) &&";
	print "\n         editdate(document.Chg.purgeDate))";
	print "\n     return true;";
	print "\n   }";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "PURGEATTACHMENTS";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\" onSubmit=\"return false;\">";
	print "\n <table $contentTable>";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {$errVar=ErrVarErr($profileHandle, $errVar);}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$V_byCriteria=Decat_Field("@@bypc", $edtVar);
		$byCriteriaChecked=Field_Checked($V_byCriteria, "Y");
		$V_byDate=Decat_Field("@@bydt", $edtVar);
		$byDateChecked=Field_Checked($V_byDate, "Y");
		$V_purgeDate=Decat_Field("@@prgd", $edtVar);
		$V_purgeDate=DateFromISO($V_purgeDate);

		require 'ScheduleJobErr.php';    // Schedule Entries Errors
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	}
	print "\n <tr><td class=\"dsphdr\">Based On:</td> ";
	print "\n     <td class=\"inputcode\"><input name=\"byCriteria\" type=\"checkbox\" VALUE='Y' {$byCriteriaChecked}>Purge Criteria</td> ";
	print "\n </tr> ";
	print "\n <tr><td>&nbsp;</td> ";
	print "\n     <td class=\"inputcode\"><input name=\"byDate\" type=\"checkbox\" VALUE='Y'{$byDateChecked}>Date ";
	print "\n                           <input type=\"text\" name=\"purgeDate\" size=\"6\" maxlength=\"6\" value=\"" . rtrim($V_purgeDate) . "\"> ";
	print "\n                           <a href=\"javascript:calWindow('purgeDate');\">$calendarImage</a> ";
	print "\n     </td> ";
	print "\n </tr> ";
	print "\n </table> ";
	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	if ($submitSchedule == "S") {
		require 'SubmitScheduleBottom.php';
		print "\n $hrTagAttr ";
	}
	print "\n $hrTagAttr ";
	print "\n </form>";
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	$purgeDateISO = "";
	if ($_POST['byDate'] == 'Y') {$purgeDateISO=Date_To_ISO($_POST['purgeDate']);}
	$edtVar= "";
	Concat_Field("@@netd", $homePath);
	Concat_Field("@@ufil", $uploadDirectory);
	Concat_Field("@@bypc", $_POST['byCriteria']);
	Concat_Field("@@bydt", $_POST['byDate']);
	Concat_Field("@@prgd", $purgeDateISO);
	Concat_Field("@@dbid", $dataBaseID);
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

	$pgm = i5_program_prepare("HSYPGA_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HSYPGA_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

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
	if (!$ret) {die("<br>Validate_Data (HSYPGA_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['submitSchedule'] =$submitSchedule;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	return $returnValue;
}

function Check_Authority($userProfile,$userAuthority) {
	global $pgmLibrary, $i5Connect;
	if (is_null($userAuthority )) $userAuthority="";

	$pgmCall = array(
	array("Name"=>"userProfile",   "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"userAuthority", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"100"));

	$pgm = i5_program_prepare("CSYSPC", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (CSYSPC) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"userProfile"    =>$userProfile,
	"userAuthority"  =>$userAuthority);

	$parmOut = array(
	"userProfile"   =>"userProfile",
	"userAuthority" =>"userAuthority");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data (CSYSPC) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['userAuthority'] =$userAuthority;
	return $returnValue;
}
?>										