<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$edtVar        = $_GET['amp;edtVar'];
$responseInfo  = "";

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';

if (is_null($edtVar ))   $edtVar="";

$pgmCall = array(
array("Name"=>"userProfile",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
array("Name"=>"responseInfo",    "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

$pgm = i5_program_prepare("HARPYM_C", $pgmCall);
if (!$pgm) {die("<br>UpdARPYRC (HARPYM_C) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

$parmIn = array(
"userProfile"    =>$userProfile,
"edtVar"         =>$edtVar,
"responseInfo"   =>$responseInfo);

$parmOut = array(
"userProfile"    =>"userProfile",
"edtVar"         =>"edtVar",
"responseInfo"   =>"responseInfo");

$ret = i5_program_call($pgm, $parmIn, $parmOut);
if (function_exists('i5_output')) extract(i5_output());
if (!$ret) {die("<br>UpdARPYRC (HARPYM_C) call errno=".i5_errno()." msg=".i5_errormsg());}

print "|$responseInfo|";

?>