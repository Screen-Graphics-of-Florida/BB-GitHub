<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromTblID          = (isset($_GET['fromTblID'])) ? $_GET['fromTblID'] : 0;
$fromPagID          = (isset($_GET['fromPagID'])) ? $_GET['fromPagID'] : 0;
$fromScript         = (isset($_GET['fromScript'])) ? strtoupper($_GET['fromScript']) : "";
$pageHeading1       = (isset($_GET['pageHeading1'])) ? $_GET['pageHeading1'] : "";
$role               = (isset($_GET['role'])) ? $_GET['role'] : "";
$user               = (isset($_GET['user'])) ? $_GET['user'] : "";
$filterID           = (isset($_GET['filterID'])) ? $_GET['filterID'] : 0;
$filterName         = (isset($_GET['filterName'])) ? $_GET['filterName'] : 0;
$errFound           = (isset ($_GET ['errFound'])) ? $_GET ['errFound'] : null;
$jobSbmSched        = (isset ( $_GET ['jobSbmSched'] )) ? $_GET ['jobSbmSched'] : null;
$resetSelectionFlag = (isset ( $_GET ['resetSelectionFlag'] )) ? $_GET ['resetSelectionFlag'] : null;
$rtvSelection       = (isset ( $_GET ['rtvSelection'] )) ? $_GET ['rtvSelection'] : null;
$saveSelection      = (isset ( $_GET ['saveSelection'] )) ? $_GET ['saveSelection'] : null;
$scheduleJobSwitch  = (isset ( $_GET ['scheduleJobSwitch'] )) ? $_GET ['scheduleJobSwitch'] : null;
$selScheduleJob     = (isset ( $_GET ['selScheduleJob'] )) ? $_GET ['selScheduleJob'] : null;
$submitSchedule     = (isset ( $_GET ['submitSchedule'] )) ? $_GET ['submitSchedule'] : null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
$page_title = "Download to CSV Selection";
$scriptName = "hdListCsvDownloadSelection.php";
$scriptVarBase = "{$genericVarBase}&amp;fromScript=" . urlencode($fromScript) . "&amp;fromTblID=" . urlencode($fromTblID) . "&amp;fromPagID=" . urlencode($fromPagID) . "&amp;pageHeading1=" . urlencode($pageHeading1) . "&amp;sylMaxSeq=" . urlencode($sylMaxSeq)
. "&amp;filterID=" . urlencode($filterID) . "&amp;filterName=" . urlencode($filterName) . "&amp;role=" . urlencode($role) . "&amp;user=" . urlencode($user);
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$popUpWin = "Y";
$submitNoSelection = "Y";
$submitNoReset = "Y";
$submitCallProgram = "HSYLWD";
$submitEnvProgram = "HSYLWD";
$submitEnvPrinter = "";
$submitScheduleScript = "FilterSelection.php";
$applicationID = "";

require_once($docType);
print "\n <html> <head>";
require_once($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'CalendarInclude.php';
require_once 'CheckEnterChg.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once 'Banner.php';

print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "HDLISTCSVDOWNLOADSELECTION";
print "\n <td class=\"content\">";
print "\n <table $contentTable> ";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td> ";
print "\n     <td class=\"toolbar\">";

print "<a href=\"javascript:window.close(); exit();\" title=\"Back Home\">{$portalHome}</a>";
print "\n <a href=\"javascript:check(document.Chg);\">$sbmSchdImage</a>";
if ($allowScheduleJob != "N") {
    print "\n <a href=\"javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg);\">$sbmSchdParmImage</a>";
}
if (file_exists('R15.0_List_Widget_Schedule_Download.pdf')) {
    $designIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgHelp.gif\" title=\"View List Widget Download CSV Design\" alt=\"Help\">";
    print "<a href=\"R15.0_List_Widget_Schedule_Download.pdf\" target=\"_blank\">{$designIcon}</a>";
}
require_once 'HelpPage.php';
print "</td></tr></table>";
require_once 'ConfMessageDisplay.php';
print $hrTagAttr;

print "\n <table>";
Format_Header ( "Filter Name", $filterName, "" );
$userName = ($user !== '') ? RetValue("USUSER='{$user}'", "SYUSER", "USDESC") : '';
Format_Header ( "User Profile", $userName, $user );
$roleDesc = ($role !== '') ? RetValue("RMROLE='{$role}'", "SYROLM", "RMDESC") : '';
Format_Header ( "Role", $roleDesc, $role );
print "\n </table>";
if ($errFound != "" || $scheduleJobSwitch == "Y") {
    $scheduleJobSwitch = "";
    $focusField = "";
    $edtVar = EdtVarErr($profileHandle, $edtVar);
    if ($errFound != "") {
        $errVar = ErrVarErr($profileHandle, $errVar);
        require 'ScheduleJobErr.php'; // Schedule Entries Errors
    }
    $submitSchedule = Decat_Field("@@sbjb", $edtVar);
    require 'ScheduleJobValue.php'; // Schedule Entries Values
}

print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\">";
$envProgram = $submitEnvProgram;
$envPrinter = $submitEnvPrinter;
include 'ScheduleJobDownload.php';
if ($submitSchedule == "S") {
     require 'SubmitScheduleBottom.php';
}
print $hrTagAttr;

print "\n </form>";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";

if ($tag == "Edit_Data") {
    $submitCallProgram = "HSYLWD";
    $edtVar= "";
    Concat_Field("@@rqst", "CALL $submitCallProgram PARM('$userProfile' '$dataBaseID' '$fromTblID' '$fromPagID' '$filterID' '$user' '$role')");
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
    exit;
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