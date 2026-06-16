<?php
require_once 'GetURLParm.php';
require_once 'VarBase.php';
require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'edtVar.php';

$mfgOrder    = (isset($_GET['mfgOrder']))     ? strtoupper($_GET['mfgOrder']) : '';
$plantNumber = (isset($_GET ['plantNumber'])) ? strtoupper(str_pad($_GET ['plantNumber'], 3, "0", STR_PAD_LEFT)) : '';	
$backURL     = RetValue("ERXHND='$profileHandle' and ERTYPE='U'", "SYEERR", "EREERR");
$submitCallProgram = "HSIOPN";
$submitEnvProgram = "BROWSER";
$submitEnvPrinter = " ";
$submitSchedule = "N";
	
$edtVar= "";
Concat_Field("@@rqst", "CALL $submitCallProgram PARM('$plantNumber' '$mfgOrder')");
Concat_Field("@@pgid", $submitEnvProgram);
Concat_Field("@@prtf", $submitEnvPrinter);
Concat_Field("@@pref", $submitApplPrefix);
Concat_Field("@@apid", $applicationID);
require 'ScheduleJobConcat.php';   // Schedule Entries Values
$edtVar .= "}{";

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
if (!$ret) {
    $errMsg = "<br>Validate_Data (HSYSSS_W) call errno=".i5_errno()." msg=".i5_errormsg();
    die($errMsg);
}
	
$confMessage="Confirm Reopen of Plant " . ltrim($plantNumber, "0") . " Mfg Order " . $mfgOrder;
print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim("$confMessage")) . "\"> ";
?>