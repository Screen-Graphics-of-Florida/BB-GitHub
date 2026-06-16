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

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title            = "Change Attachment Owner";
$scriptName            = "ChangeAttachmentOwner.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$submitNoSelection     = "N";
$submitCallProgram     = "";
$submitEnvProgram      = "CSYCAO";
$submitEnvPrinter      = "";
$submitScheduleScript  = "ChangeAttachmentOwner.php";
$applicationID         = "SY";

if ($admin != "Y") {
	print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}ProgOptSecError.php{$genericVarBase}&amp;page_title=" . urlencode(trim($page_title)) . "\"> ";
	exit;
}

if ($tag != "Edit_Data") {
	$returnValue=Check_Authority($userProfile,$userAuthority);
	$userAuthority=$returnValue['userAuthority'];
	if (strpos($userAuthority, "*ALLOBJ") === false){
		$alertMessage = "Change Attachment Owner requires *ALLOBJ authority to run";
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;alertMessage=" . urlencode(trim($alertMessage)) . "\"> ";
		exit;
	}
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CalendarInclude.php';
	require_once 'DateEdit.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'CheckEnterChg.php';
	print "\n   function validate(chgForm) {";
	print "\n if (document.Chg.fromUser.value ==\"\" || ";
	print "\n     document.Chg.toUser.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n     return true;";
	print "\n   }";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "DATAENCRYPTION";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	print "\n   <div class=\"hdrdata\" style=\"border: 1px black solid; margin: 5px;\">";
	print "\n   <br>When a User Profile is deleted, any existing attachments assigned to that User Profile will also be deleted. <br>";
	print "\n     Run this option prior to deleting the User Profile to retain the attachments by changing owner.<br><br>";
	print "\n     The 'Current Owner' list contains all of the User Profiles that currently own attachment(s).<br>";
	print "\n     The 'New Owner' must be a valid User Profile. <br><br>";
	print "\n   </div>";

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\" onSubmit=\"return false;\">";
	print "\n <table $contentTable>";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_fromUser=DecatErr_Field("@@fusr", "fromUser");
			$Err_toUser=DecatErr_Field("@@tusr", "toUser");
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$fromUser=Decat_Field("@@fusr", $edtVar);
		$toUser=Decat_Field("@@tusr", $edtVar);
		require 'ScheduleJobErr.php';    // Schedule Entries Errors
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	}

	$stmtSQL = "Select distinct ATUSER From SYD2WA Order By ATUSER";
	$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
	$startRow = 1;
	print "\n <tr><td class=\"dsphdr\">Current Owner</td>";
	print "\n     <td><SELECT id=\"fromUser\" NAME=\"fromUser\" SIZE=\"1\"> ";
	while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
		if ($startRow === 1) {
			$sel = ($errFound != "") ? '' : 'SELECTED';
			print "\n <OPTION value=\"\" $sel>";
		}
		$sel = ($fromUser == rtrim($row['ATUSER'])) ? 'SELECTED' : '';
		print "\n <OPTION value=\"" . rtrim($row['ATUSER']) . "\" $sel>$row[ATUSER] ";
		$startRow++;
	}
	print "\n </SELECT>  $reqFieldChar</td>";
	print "\n </tr> ";

	$textOvr=SetTextOvr($Err_toUser);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>New Owner</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toUser\"  value=\"{$toUser}\"size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=toUser&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a> $reqFieldChar</td> ";
	print "\n </tr> ";
	DspErrMsg($Err_toUser);

	print "\n </table> ";
	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	if ($submitSchedule == "S") {
		require 'SubmitScheduleBottom.php';
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
	$edtVar= "";
	Concat_Field("@@fusr", strtoupper($_POST['fromUser']));
	Concat_Field("@@tusr", strtoupper($_POST['toUser']));
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

	$pgm = i5_program_prepare("HSYCAO_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HSYCAO_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

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
	if (!$ret) {die("<br>Validate_Data (HSYCAO_W) call errno=".i5_errno()." msg=".i5_errormsg());}

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