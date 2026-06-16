<?php

function Rtv_Price_By_Cust_Item_Qty($profileHandle, $edtVar) {
	global $pgmLibrary,$i5connect;
	
	$pgmCall = array(
	array("Name"=>"profilehandle",  "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"edtVar", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR,   "Length"=>"32000"));
	
	$pgm = i5_program_prepare("HOEPRC_WSG", $pgmCall);
	if (!$pgm) {die("<br>Rtv_Price_By_Cust_Item_Qty (HOEPRC_WSG) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
 	"profilehandle"=>$profileHandle,
	"edtVar"=>$edtVar);


	$parmOut = array(
	"edtVar"=>"edtVar"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Rtv_Price_By_Cust_Item_Qty (HOEPRC_WSG) call errno=".i5_errno()." msg=".i5_errormsg());}

     return $edtVar;
}

?>
