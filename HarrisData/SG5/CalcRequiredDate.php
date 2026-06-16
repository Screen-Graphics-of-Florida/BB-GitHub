<?php
require_once 'GetURLParm.php';

$docName      = $_GET['docName'];
$fldName      = $_GET['fldName'];
$fldDesc      = $_GET['fldDesc'];
$vendor       = $_GET['vendor'];
$whs          = $_GET['whs'];
$item         = $_GET['item'];

require_once 'SetLibraryList.php';

$reqDate = CalcReqDate();

print "\n \n <script TYPE=\"text/javascript\">";
print "\n   window.opener.document.$docName.$fldName.value = '$reqDate'; ";
print "\n   window.opener.document.$docName.$fldName.focus(); ";
print "\n   window.close(); ";
print "\n </script> \n";

function CalcReqDate (){
	global $i5Connect, $vendor, $whs, $item, $poCheckVendorLeadTime;
	$reqDate = 0;
	if (!$i5Connect) die("<br>RetColValue Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
		array("Name"=>"vendor"        , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"7"),
		array("Name"=>"item"          , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"15"),
		array("Name"=>"whs"           , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"3"),
		array("Name"=>"useVendor"     , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
		array("Name"=>"reqDate"       , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"6"));

	$pgm = i5_program_prepare("HHDCRD_W", $pgmCall);
	if (!$pgm) {die("<br>CalcReqDate Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
		"vendor"    =>$vendor,
		"item"      =>$item,
		"whs"       =>$whs,
		"useVendor" =>$poCheckVendorLeadTime,
		"reqDate"   =>$reqDate
	);

	$parmOut = array(
		"reqDate"  =>"reqDate"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> CalcReqDate Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmIn");}
	$reqDate6 = str_pad($reqDate,6,0,STR_PAD_LEFT);
	return $reqDate6;
}

?>
