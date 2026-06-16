<?php
if (!$HDToolkitPath or trim($HDToolkitPath) == "") {
$origCWpath="/usr/local/zendsvr6/share/ToolKitAPI/CW/cw.php"; 
$new85CWpath="/usr/local/zendsvr6/var/libraries/PHP_Toolkit_for_IBMI_i/default/library/CW/cw.php"; 
if(file_exists($origCWpath)) {
require_once '/usr/local/zendsvr6/share/ToolKitAPI/CW/cw.php';
} else {
require_once '/usr/local/zendsvr6/var/libraries/PHP_Toolkit_for_IBMI_i/default/library/CW/cw.php';
}
} else {
require_once $HDToolkitPath;
}

// Direct Call Function Blocks
$uv_Sql         = "";
$userViewVar    = "";
$uv_FieldName   = "";
$uv_FieldValue  = "";

// Check For Existence Of Customer Invoice
function Check_Invoice ($profileHandle,$dataBaseID, $invoiceNumber, $customerNumber){
	global $pgmLibrary, $i5Connect;
	$invoiceFound     ="";
	if (!$i5Connect) die("<br>Check_Invoice Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle" , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"64"),
	array("Name"=>"dataBaseID"    , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"2"),
	array("Name"=>"invoiceNumber" , "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"7"),
	array("Name"=>"customerNumber", "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"7"),
	array("Name"=>"invoiceFound"  , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR,   "Length"=>"1"));

	$pgm = i5_program_prepare("HOECIV_W", $pgmCall);
	if (!$pgm) {die("<br>Check_Invoice Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle" =>$profileHandle,
	"dataBaseID"    =>$dataBaseID,
	"invoiceNumber" =>$invoiceNumber,
	"customerNumber"=>$customerNumber,
	"invoiceFound"  =>$invoiceFound
	);

	$parmOut = array(
	"invoiceFound"  =>"invoiceFound"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> Check_Invoice Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $invoiceFound;
}

// Check For Existence Of Vendor/Customer Item Comments
function Check_Item_Comments ($profileHandle,$dataBaseID,$itemNumber,$vcf = '',$customerNumber = 0,$documentType = '',$headerTrailer = ''){
	global $pgmLibrary, $i5Connect;
	$commentsFound     ="";
	if (!$i5Connect) die("<br>Check_Item_Comments Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle" , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"64"),
	array("Name"=>"dataBaseID"    , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"2"),
	array("Name"=>"itemNumber"    , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"15"),
	array("Name"=>"vcf"           , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"1"),
	array("Name"=>"customerNumber", "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"7"),
	array("Name"=>"documentType"  , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"3"),
	array("Name"=>"headerTrailer" , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"1"),
	array("Name"=>"commentsFound" , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR,   "Length"=>"1"));

	$pgm = i5_program_prepare("HHDCIC_W", $pgmCall);
	if (!$pgm) {die("<br>Check_Item_Comments Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle" =>$profileHandle,
	"dataBaseID"    =>$dataBaseID,
	"itemNumber"    =>$itemNumber,
	"vcf"           =>$vcf,
	"customerNumber"=>$customerNumber,
	"documentType"  =>$documentType,
	"headerTrailer" =>$headerTrailer,
	"commentsFound" =>$commentsFound
	);

	$parmOut = array(
	"commentsFound"  =>"commentsFound"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> Check_Item_Comments Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $commentsFound;
}

// Check User Authority
function Check_User_Authority (){
	global $pgmLibrary, $i5Connect;
	$userProfile = ($_SERVER['PHP_AUTH_USER']);
	$userAuthority = "";

	$pgmCall = array(
	array("Name"=>"userProfile", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"userAuthority", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"100"));

	$pgm = i5_program_prepare("CSYSPC", $pgmCall);
	if (!$pgm) {die("<br>Check Authority Program Prepare Failed errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"userProfile"=>$userProfile,
	"userAuthority"=>$userAuthority
	);

	$parmOut = array(
	"userProfile"=>"userProfile",
	"userAuthority"  =>"userAuthority"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> Check Authority Program call errno=".i5_errno()." msg=".i5_errormsg() . "");}

	$returnValue['userAuthority'] = $userAuthority;

	return $returnValue;
}

// Test User Profile Days To Expire
function Check_Pswd_Exp ($userProfile){
	global $pgmLibrary, $i5Connect;
	$userProfile = ($_SERVER['PHP_AUTH_USER']);
	$expDays     = 0;
	if (!$i5Connect) die("<br>Check Password Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"userProfile", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"expDays", "IO"=>I5_OUT, "Type"=>I5_TYPE_PACKED, "Length"=>"4"),
	array("Name"=>"msgID", "IO"=>I5_OUT, "Type"=>I5_TYPE_CHAR, "Length"=>"7"));

	$pgm = i5_program_prepare("HSYPWE", $pgmCall);
	if (!$pgm) {die("<br>Check Password Program Prepare Failed errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"userProfile"=>$userProfile
	);

	$parmOut = array(
	"expDays"=>"expDays",
	"msgID"  =>"msgID"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> Check Password Expiration Program call errno=".i5_errno()." msg=".i5_errormsg() . " Program:$pgm  In:$parmin");}

	$returnValue['expDays'] = $expDays;
	$returnValue['msgID']   = $msgID;

	return $returnValue;
}

// Concat User View
function Concat_UserView($uv_FieldName, $uv_FieldValue){
	global $userViewVar;
	if ($userViewVar == ""){$userViewVar =  "{$uv_FieldName}{$uv_FieldValue}";}
	else                   {$userViewVar .= "}{{$uv_FieldName}{$uv_FieldValue}";}
}

// Convert Data
function Convert_Data($e_type, $e_mode, $e_value){
	global $pgmLibrary, $i5Connect;

	if ($e_value) {
		$pgmCall = array(
		array("Name"=>"e_type", "IO"=>I5_INOUT,  "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
		array("Name"=>"e_mode", "IO"=>I5_INOUT,  "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
		array("Name"=>"e_value", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"30"));

		$pgm = i5_program_prepare("HSYDCV_W", $pgmCall);
		if (!$pgm) {die("<br>Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

		$parmIn = array(
		"e_type"=>$e_type,
		"e_mode"=>$e_mode,
		"e_value"=>$e_value);

		$parmOut = array(
		"e_type"=>"e_type",
		"e_mode"=>"e_mode",
		"e_value"=>"e_value");

		$ret = i5_program_call($pgm, $parmIn, $parmOut);
		if (function_exists('i5_output')) extract(i5_output());
		if (!$ret) {die("<br>Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}
	}
	return $e_value;
}

// Customer User View
function CustomerUserView ($profileHandle,$dataBaseID,$customerNumber,$testOvrFields){
	global $pgmLibrary, $i5Connect;
	$userProfile = ($_SERVER['PHP_AUTH_USER']);
	$userPass    ="";
	if (!$i5Connect) die("<br>CustomerUserView Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle" , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"64"),
	array("Name"=>"dataBaseID"    , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"2"),
	array("Name"=>"userPass"      , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR,   "Length"=>"1"),
	array("Name"=>"customerNumber", "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"7"),
	array("Name"=>"testOvrFields" , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"1"));

	$pgm = i5_program_prepare("HHDCUST_W", $pgmCall);
	if (!$pgm) {die("<br>CustomerUserView prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle" =>$profileHandle,
	"dataBaseID"    =>$dataBaseID,
	"userPass"      =>$userPass,
	"customerNumber"=>$customerNumber,
	"testOvrFields" =>$testOvrFields
	);

	$parmOut = array(
	"userPass"=>"userPass"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>CustomerUserView call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $userPass;
}

// Default zero
function Default_Zero ($inputField) {
	if (is_null($inputField) || trim($inputField)=="") {$inputField=0;}
	return $inputField;
}

// Delete User Handle
function deleteUserHandle ($profileHandle, $dataBaseID){
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Delete_User Handle Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle" , "IO"=>I5_INOUT,    "Type"=>I5_TYPE_CHAR,   "Length"=>"64"),
	array("Name"=>"dataBaseID"    , "IO"=>I5_INOUT,    "Type"=>I5_TYPE_CHAR,   "Length"=>"2"));

	$pgm = i5_program_prepare("HSYDHN_W", $pgmCall);
	if (!$pgm) {die("<br>Delete_User_Handle Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle" =>$profileHandle,
	"dataBaseID"    =>$dataBaseID
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"dataBaseID"   =>"dataBaseID"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> Delete_User_Handle Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}
}

// Employee User View
function EmployeeUserView ($profileHandle,$dataBaseID,$applicationID,$userPass,$prCompany,$prFacility,$prEmployee,$hrCompany,$hrEmployee){
	global $pgmLibrary, $i5Connect;
	//if (is_null($payer )) $payer=0;
	$userProfile = ($_SERVER['PHP_AUTH_USER']);
	$userPass    ="";
	if (!$i5Connect) die("<br>EmployeeUserView Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle"  , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"64"),
	array("Name"=>"dataBaseID"     , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"2"),
	array("Name"=>"applicationID"  , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"2"),
	array("Name"=>"userPass"       , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR,   "Length"=>"1"),
	array("Name"=>"prCompany"      , "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"2"),
	array("Name"=>"prFacility"     , "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"4"),
	array("Name"=>"prEmployee"     , "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"5"),
	array("Name"=>"hrCompany"      , "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"2"),
	array("Name"=>"hrEmployee"     , "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"9"));

	$pgm = i5_program_prepare("HHREMP_W", $pgmCall);
	if (!$pgm) {die("<br>EmployeeUserView prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"  =>$profileHandle,
	"dataBaseID"     =>$dataBaseID,
	"applicationID"  =>$applicationID,
	"userPass"       =>$userPass,
	"prCompany"      =>$prCompany,
	"prFacility"     =>$prFacility,
	"prEmployee"     =>$prEmployee,
	"hrCompany"      =>$hrCompany,
	"hrEmployee"     =>$hrEmployee
	);

	$parmOut = array(
	"userPass"=>"userPass"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>EmployeeUserView call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $userPass;
}

// Get Environment Overrides
function Env_Overrides ($userProfile,$applicationID,$envProgram,$envPrinter,$envJobName,$envJobDescription,$envJobQueue,$envOutQueue,$envError) {
	if (is_null($envProgram))       {$envProgram="";}
	if (is_null($envPrinter))       {$envPrinter="";}
	if (is_null($envJobName))       {$envJobName="";}
	if (is_null($envJobDescription)){$envJobDescription="";}
	if (is_null($envJobQueue))      {$envJobQueue="";}
	if (is_null($envOutQueue))      {$envOutQueue="";}
	if (is_null($envError))         {$envError="";}

	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Env_Overrides Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"userProfile"      , "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"applicationID"    , "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"envProgram"       , "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"envPrinter"       , "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"envJobName"       , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"envJobDescription", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"envJobQueue"      , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"envOutQueue"      , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"envError"         , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"75")
	);

	$pgm = i5_program_prepare("HSYSBS_W", $pgmCall);
	if (!$pgm) {die("<br>Env_Overrides Program (HSYSBS_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"userProfile"      =>$userProfile,
	"applicationID"    =>$applicationID,
	"envProgram"       =>$envProgram,
	"envPrinter"       =>$envPrinter,
	"envJobName"       =>$envJobName,
	"envJobDescription"=>$envJobDescription,
	"envJobQueue"      =>$envJobQueue,
	"envOutQueue"      =>$envOutQueue,
	"envError"         =>$envError
	);

	$parmOut = array(
	"envJobName"       =>"envJobName",
	"envJobDescription"=>"envJobDescription",
	"envJobQueue"      =>"envJobQueue",
	"envOutQueue"      =>"envOutQueue",
	"envError"         =>"envError"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Env_Overrides Program (HSYSBS_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['envJobName']        =$envJobName;
	$returnValue['envJobDescription'] =$envJobDescription;
	$returnValue['envJobQueue']       =$envJobQueue;
	$returnValue['envOutQueue']       =$envOutQueue;
	$returnValue['envError']          =$envError;
	return $returnValue;
}

// Item User View
function ItemUserView ($profileHandle,$dataBaseID,$itemNumber){
	global $pgmLibrary, $i5Connect;
	$userProfile = ($_SERVER['PHP_AUTH_USER']);
	$userPass    ="";
	if (!$i5Connect) die("<br>ItemUserView Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle" , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID"    , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"userPass"      , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"itemNumber"    , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"15"));

	$pgm = i5_program_prepare("HHDIMST_W", $pgmCall);
	if (!$pgm) {die("<br>ItemUserView prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle" =>$profileHandle,
	"dataBaseID"    =>$dataBaseID,
	"userPass"      =>$userPass,
	"itemNumber"    =>$itemNumber
	);

	$parmOut = array(
	"userPass"=>"userPass"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>ItemUserView call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $userPass;
}

// Maintenance Edit
function Maintain_Edit($pgmName = '',$userProfile = '',$maintenanceCode = '',$errFound = '',$edtVar = '',$errVar = '',$wrnVar = '') {
	global $pgmLibrary, $i5Connect;

	$pgmCall = array(
	array("Name"=>"userProfile",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"maintenanceCode", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"errFound",        "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"wrnVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("$pgmName", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data ($pgmName) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"userProfile"    =>$userProfile,
	"maintenanceCode"=>$maintenanceCode,
	"errFound"       =>$errFound,
	"edtVar"         =>$edtVar,
	"errVar"         =>$errVar,
	"wrnVar"         =>$wrnVar);

	$parmOut = array(
	"userProfile"    =>"userProfile",
	"maintenanceCode"=>"maintenanceCode",
	"errFound"       =>"errFound",
	"edtVar"         =>"edtVar",
	"errVar"         =>"errVar",
	"wrnVar"         =>"wrnVar");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data ($pgmName) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['userProfile']    =$userProfile;
	$returnValue['maintenanceCode']=$maintenanceCode;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	$returnValue['wrnVar']         =$wrnVar;
	return $returnValue;
}

// Maintenance Edit (passing Hex Handle)
function Maintain_Edit_Handle($pgmName = '',$profileHandle = '',$maintenanceCode = '',$errFound = '',$edtVar = '',$errVar = '',$wrnVar = '') {
	global $pgmLibrary, $i5Connect;

	$pgmCall = array(
	array("Name"=>"profileHandle",   "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"maintenanceCode", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"errFound",        "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"wrnVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("$pgmName", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data ($pgmName) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"  =>$profileHandle,
	"maintenanceCode"=>$maintenanceCode,
	"errFound"       =>$errFound,
	"edtVar"         =>$edtVar,
	"errVar"         =>$errVar,
	"wrnVar"         =>$wrnVar);

	$parmOut = array(
	"profileHandle"  =>"profileHandle",
	"maintenanceCode"=>"maintenanceCode",
	"errFound"       =>"errFound",
	"edtVar"         =>"edtVar",
	"errVar"         =>"errVar",
	"wrnVar"         =>"wrnVar");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data ($pgmName) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['profileHandle']  =$profileHandle;
	$returnValue['maintenanceCode']=$maintenanceCode;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	$returnValue['wrnVar']         =$wrnVar;
	return $returnValue;
}

// Payer User View
function PayerUserView ($profileHandle,$payer,$testOvrFields){
	global $pgmLibrary, $i5Connect;
	if (is_null($payer )) $payer=0;
	$userProfile = ($_SERVER['PHP_AUTH_USER']);
	$userPass    ="";
	if (!$i5Connect) die("<br>PayerUserView Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle" , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"64"),
	array("Name"=>"userPass"      , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR,   "Length"=>"1"),
	array("Name"=>"payer"         , "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"7"),
	array("Name"=>"testOvrFields" , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"1"));

	$pgm = i5_program_prepare("HARPYRH_W", $pgmCall);
	if (!$pgm) {die("<br>PayerUserView prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle" =>$profileHandle,
	"userPass"      =>$userPass,
	"payer"         =>$payer,
	"testOvrFields" =>$testOvrFields
	);

	$parmOut = array(
	"userPass"=>"userPass"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>PayerUserView call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $userPass;
}

// Program Option Security
function pgmOptSecurity($profileHandle, $dataBaseID, $programName){
	global $pgmLibrary, $i5Connect;
	if (is_null($sec_01)){$sec_01="";}
	if (is_null($sec_02)){$sec_02="";}
	if (is_null($sec_03)){$sec_03="";}
	if (is_null($sec_04)){$sec_04="";}
	if (is_null($sec_05)){$sec_05="";}
	if (is_null($sec_06)){$sec_06="";}
	if (is_null($sec_07)){$sec_07="";}
	if (is_null($sec_08)){$sec_08="";}
	if (is_null($sec_09)){$sec_09="";}
	if (is_null($sec_10)){$sec_10="";}
	if (is_null($sec_11)){$sec_11="";}
	if (is_null($sec_12)){$sec_12="";}
	if (is_null($sec_13)){$sec_13="";}
	if (is_null($sec_14)){$sec_14="";}
	if (is_null($sec_15)){$sec_15="";}

	if (!$i5Connect) die("<br>Program Option Security Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"programName", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"sec_01", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_02", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_03", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_04", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_05", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_06", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_07", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_08", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_09", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_10", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_11", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_12", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_13", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_14", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"sec_15", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1")
	);

	$pgm = i5_program_prepare("HSYPGM_W", $pgmCall);
	if (!$pgm) {die("<br>SessionDate Program Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"=>$profileHandle,
	"dataBaseID"=>$dataBaseID,
	"programName"=>$programName,
	"sec_01"=>$sec_01,
	"sec_02"=>$sec_02,
	"sec_03"=>$sec_03,
	"sec_04"=>$sec_04,
	"sec_05"=>$sec_05,
	"sec_06"=>$sec_06,
	"sec_07"=>$sec_07,
	"sec_08"=>$sec_08,
	"sec_09"=>$sec_09,
	"sec_10"=>$sec_10,
	"sec_11"=>$sec_11,
	"sec_12"=>$sec_12,
	"sec_13"=>$sec_13,
	"sec_14"=>$sec_14,
	"sec_15"=>$sec_15
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"dataBaseID"   =>"dataBaseID",
	"programName"  =>"programName",
	"sec_01"=>"sec_01",
	"sec_02"=>"sec_02",
	"sec_03"=>"sec_03",
	"sec_04"=>"sec_04",
	"sec_05"=>"sec_05",
	"sec_06"=>"sec_06",
	"sec_07"=>"sec_07",
	"sec_08"=>"sec_08",
	"sec_09"=>"sec_09",
	"sec_10"=>"sec_10",
	"sec_11"=>"sec_11",
	"sec_12"=>"sec_12",
	"sec_13"=>"sec_13",
	"sec_14"=>"sec_14",
	"sec_15"=>"sec_15"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Program Option Program call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['sec_01']=$sec_01;
	$returnValue['sec_02']=$sec_02;
	$returnValue['sec_03']=$sec_03;
	$returnValue['sec_04']=$sec_04;
	$returnValue['sec_05']=$sec_05;
	$returnValue['sec_06']=$sec_06;
	$returnValue['sec_07']=$sec_07;
	$returnValue['sec_08']=$sec_08;
	$returnValue['sec_09']=$sec_09;
	$returnValue['sec_10']=$sec_10;
	$returnValue['sec_11']=$sec_11;
	$returnValue['sec_12']=$sec_12;
	$returnValue['sec_13']=$sec_13;
	$returnValue['sec_14']=$sec_14;
	$returnValue['sec_15']=$sec_15;

	return $returnValue;
}

// Date Reformat Program (2 digit year)
function Reformat_Date ($dateIn, $fromFormat, $toFormat){
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Reformat Date Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"dateIn", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"7"),
	array("Name"=>"fromFormat", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"toFormat", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10")
	);

	$pgm = i5_program_prepare("CSYDTC_W", $pgmCall);
	if (!$pgm) {die("<br>Reformat_Date Program (CSYDTC_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"dateIn"=>$dateIn,
	"fromFormat"=>$fromFormat,
	"toFormat"=>$toFormat
	);

	$parmOut = array(
	"dateIn"=>"dateIn"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Reformat_Date Program (CSYDTC_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $dateIn;
}

// Date Reformat Program (4 digit year)
function Reformat_Date_4($dateIn, $fromFormat, $toFormat){
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Reformat Date Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"dateIn", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8"),
	array("Name"=>"fromFormat", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"toFormat", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10")
	);

	$pgm = i5_program_prepare("CSYDT4_W", $pgmCall);
	if (!$pgm) {die("<br>Reformat_Date Program (CSYDTC_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"dateIn"=>$dateIn,
	"fromFormat"=>$fromFormat,
	"toFormat"=>$toFormat
	);

	$parmOut = array(
	"dateIn"=>"dateIn"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Reformat_Date_4 Program (CSYDT4_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $dateIn;
}

// ISO Date Reformat Program
function Reformat_Date_ISO ($ISODate, $fromFormat, $toFormat){
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Reformat Date Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"ISODate", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"fromFormat", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"toFormat", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10")
	);

	$pgm = i5_program_prepare("CSYDTI_W", $pgmCall);
	if (!$pgm) {die("<br>Reformat_Date Program (CSYDTC_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"ISODate"=>$ISODate,
	"fromFormat"=>$fromFormat,
	"toFormat"=>$toFormat
	);

	$parmOut = array(
	"ISODate"=>"ISODate"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Reformat_Date_ISO Program (CSYDTI_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $ISODate;
}

// Period Reformat Program
function Reformat_Period($dateIn, $fromFormat) {
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Reformat Peroid Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"dateIn",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"5"),
	array("Name"=>"fromFormat", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10")
	);

	$pgm = i5_program_prepare("CSYPDC_W", $pgmCall);
	if (!$pgm) {die("<br>Reformat_Period Program (CSYPDC_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"dateIn"=>$dateIn,
	"fromFormat"=>$fromFormat
	);

	$parmOut = array(
	"dateIn"    =>"dateIn",
	"fromFormat"=>"fromFormat"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Reformat_Period Program (CSYPDC_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $dateIn;
}

// Retrieve Converted Column Value
function RetColValue ($profileHandle, $dataBaseID, $selectRecord, $fileName, $fieldName, $fieldMode){
	global $pgmLibrary, $i5Connect;
	$fieldVal     ="";
	if (!$i5Connect) die("<br>RetColValue Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"selectRecord"  , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"fileName"      , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"1000"),
	array("Name"=>"fieldName"     , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"500"),
	array("Name"=>"fieldMode"     , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"fieldVal"      , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR,   "Length"=>"32000"));

	$pgm = i5_program_prepare("HSYRFV_W", $pgmCall);
	if (!$pgm) {die("<br>RetColValue Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"selectRecord"  =>$selectRecord,
	"fileName"      =>$fileName,
	"fieldName"     =>$fieldName,
	"fieldMode"     =>$fieldMode,
	"fieldVal"      =>$fieldVal
	);

	$parmOut = array(
	"fieldVal"  =>"fieldVal"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> RetColValue Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $fieldVal;
}

// Retrieve Department Name
function RetDepNam($profileHandle, $dataBaseID, $depNbr){
	global $pgmLibrary, $i5Connect;
	$depName="";
	if (!$i5Connect) die("<br>RetDepNam Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR,   "Length"=>"64"),
	array("Name"=>"dataBaseID",    "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR,   "Length"=>"2"),
	array("Name"=>"depNbr",        "IO"=>I5_INOUT,"Type"=>I5_TYPE_PACKED, "Length"=>"7"),
	array("Name"=>"depName",       "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR,   "Length"=>"30"));

	$pgm = i5_program_prepare("HHRSDN_W", $pgmCall);
	if (!$pgm) {die("<br>RetDepNam Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle" =>$profileHandle,
	"dataBaseID"    =>$dataBaseID,
	"depNbr"        =>$depNbr,
	"depName"       =>$depName
	);

	$parmOut = array(
	"profileHandle" =>"profileHandle",
	"dataBaseID"    =>"dataBaseID",
	"depNbr"        =>"depNbr",
	"depName"       =>"depName"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> RetDepNam Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $depName;
}

// Retrieve Employee Info By ID
function RetEmpByID ($empID){
	global $i5Connect;

	require 'stmtSQLClear.php';
	$stmtSQL .= " Select  * ";
	$fileSQL .= " HREMPL ";
	$selectSQL .= " EMEMID=$empID ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$F_Name = Format_EmplName($row['EMFNAM'], $row['EMLNAM'], $row['EMMIDI'], $row['EMRNAM'], $row['EMTRCD'], "D");
	$returnValue['EMLNAM']=$row['EMLNAM'];
	$returnValue['EMFNAM']=$row['EMFNAM'];
	$returnValue['EMMIDI']=$row['EMMIDI'];
	$returnValue['EMRNAM']=$row['EMRNAM'];
	$returnValue['EMTRCD']=$row['EMTRCD'];
	$returnValue['F_Name']=$F_Name;
	return $returnValue;
}

// Retrieve Employee Name
function RetEmpNam($PRComp, $PRFACL, $PREmpl, $HRCo, $HREmpl){
	global $pgmLibrary, $i5Connect;
	$lastName = "";
	$firstName = "";
	$midInit = "";
	$reportName = "";
	$termCode = "";
	if (!$i5Connect) die("<br>RetEmpNam Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"prCompany",  "IO"=>I5_INOUT,"Type"=>I5_TYPE_PACKED,"Length"=>"2"),
	array("Name"=>"prFacility", "IO"=>I5_INOUT,"Type"=>I5_TYPE_PACKED,"Length"=>"4"),
	array("Name"=>"prEmployee", "IO"=>I5_INOUT,"Type"=>I5_TYPE_PACKED,"Length"=>"5"),
	array("Name"=>"hrCompany",  "IO"=>I5_INOUT,"Type"=>I5_TYPE_PACKED,"Length"=>"2"),
	array("Name"=>"hrEmployee", "IO"=>I5_INOUT,"Type"=>I5_TYPE_PACKED,"Length"=>"9"),
	array("Name"=>"lastName",   "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR,  "Length"=>"18"),
	array("Name"=>"firstName",  "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR,  "Length"=>"18"),
	array("Name"=>"midInit",    "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR,  "Length"=>"1"),
	array("Name"=>"reportName", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR,  "Length"=>"23"),
	array("Name"=>"termCode",   "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR,  "Length"=>"4"));

	$pgm = i5_program_prepare("HHRREN_W", $pgmCall);
	if (!$pgm) {die("<br>RetEmpNam Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"prCompany"     =>$PRComp,
	"prFacility"    =>$PRFACL,
	"prEmployee"    =>$PREmpl,
	"hrCompany"     =>$HRCo,
	"hrEmployee"    =>$HREmpl,
	"lastName"      =>$lastName,
	"firstName"     =>$firstName,
	"midInit"       =>$midInit,
	"reportName"    =>$reportName,
	"termCode"      =>$termCode
	);

	$parmOut = array(
	"prCompany"     =>"prCompany",
	"prFacility"    =>"prFacility",
	"prEmployee"    =>"prEmployee",
	"hrCompany"     =>"hrCompany",
	"hrEmployee"    =>"hrEmployee",
	"lastName"      =>"lastName",
	"firstName"     =>"firstName",
	"midInit"       =>"midInit",
	"reportName"    =>"reportName",
	"termCode"      =>"termCode"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> RetEmpNam Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	$returnValue['lastName']=$lastName;
	$returnValue['firstName'] =$firstName;
	$returnValue['midInit'] =$midInit;
	$returnValue['reportName'] =$reportName;
	$returnValue['termCode'] =$termCode;
	return $returnValue;
}

// Retrieve Order History Sequence Number For An Invoice
function RetHistorySeq ($profileHandle,$dataBaseID, $invoiceNumber){
	global $pgmLibrary, $i5Connect;
	$sequence     =0;
	if (!$i5Connect) die("<br>RetHistorySeq Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle" , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"64"),
	array("Name"=>"dataBaseID"    , "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR,   "Length"=>"2"),
	array("Name"=>"invoiceNumber" , "IO"=>I5_IN,    "Type"=>I5_TYPE_PACKED, "Length"=>"7"),
	array("Name"=>"sequence"      , "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"3"));

	$pgm = i5_program_prepare("HOERHS_W", $pgmCall);
	if (!$pgm) {die("<br>RetHistorySeq Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle" =>$profileHandle,
	"dataBaseID"    =>$dataBaseID,
	"invoiceNumber" =>$invoiceNumber,
	"sequence"      =>$sequence
	);

	$parmOut = array(
	"sequence"=>"sequence"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> RetHistorySeq Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $sequence;
}

// Retrieve Value
function RetValuea($selectRecord, $fileName, $fieldName) {
	global $i5Connect;

	$fieldDescription = "";

	$stmtSQL   = "Select " . $fieldName;
	$fileSQL   = $fileName;
	$selectSQL = $selectRecord;
	$appendWildCard = "N";
	$appendUserView = "N";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " For Fetch Only with NC";

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$fieldDescription = db2_fetch_array($sqlResult);
	return rtrim($fieldDescription[0]);
}

function RetValue($selectRecord, $fileName, $fieldName) {
	global $i5Connect;

	$fieldDescription = "";

	$stmtSQL   = "Select " . $fieldName;
	$fileSQL   = $fileName;
	$selectSQL = $selectRecord;
	$appendWildCard = "N";
	$appendUserView = "N";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " For Fetch Only with NC ";

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$fieldDescription = db2_fetch_array($sqlResult);

	return rtrim($fieldDescription[0]);
}

// Retrieve Value Using With having Multiple Values to return
function RetValueMult($selectRecord, $fileName, $withSQL, $colmRequests) {
	global $i5Connect;

	$fieldName='';  // Set to blank
	foreach ($colmRequests as $colmRequest) {
		$fieldName .=(empty($fieldName)) ? $colmRequest : ',' . $colmRequest;
	}
		
	$fieldDescription = "";

	$stmtSQL = " Select " . $fieldName;
	$fileSQL   = $fileName;
	$selectSQL = $selectRecord;
	$appendWildCard = "N";
	$appendUserView = "N";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " For Fetch Only with NC ";

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$fieldDescription = db2_fetch_array($sqlResult);

	return $fieldDescription;
}

// Retrieve Value Using With
function RetValueWith($selectRecord, $fileName, $withSQL, $fieldName) {
	global $i5Connect;

	$fieldDescription = "";

	$stmtSQL = " Select " . $fieldName;
	$fileSQL   = $fileName;
	$selectSQL = $selectRecord;
	$appendWildCard = "N";
	$appendUserView = "N";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " For Fetch Only with NC ";

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$fieldDescription = db2_fetch_array($sqlResult);

	return rtrim($fieldDescription[0]);
}

// Retrieve Table Schema RtvFileLib
function RtvFileLib($e_filename, $e_library){
	global $pgmLibrary, $i5Connect;

	$pgmCall = array(
	array("Name"=>"e_filename", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"e_library", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"));

	$pgm = i5_program_prepare("CSYLIB_W", $pgmCall);
	if (!$pgm) {die("<br>Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"e_fileName"=>$e_fileName,
	"e_library"=>$e_library);

	$parmOut = array(
	"e_fileName"=>"e_fileName",
	"e_library" =>"e_library"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	$returnValue['e_filename']=$e_filename;
	$returnValue['e_library'] =$e_library;
	return $returnvalue;
}

// Retrieve If Workflow Exception Exists
function RtvWFExcptExist($profileHandle,$programName,$vldPgmName,$vldCheck) {
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>RtvWFExcptExist Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());
	$vldExcptExist=" ";

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"programName"  , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"vldPgmName"   , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"vldCheck"     , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"vldExcptExist", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1")
	);

	$pgm = i5_program_prepare("HWFEXC_W", $pgmCall);
	if (!$pgm) {die("<br>RtvWFExcptExist Program (HWFEXC_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"=>$profileHandle,
	"programName"=>$programName,
	"vldPgmName"=>$vldPgmName,
	"vldCheck"=>$vldCheck,
	"vldExcptExist"=>$vldExcptExist
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"programName"  =>"programName",
	"vldPgmName"   =>"vldPgmName",
	"vldCheck"     =>"vldCheck",
	"vldExcptExist"=>"vldExcptExist"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>RtvWFExcptExist Program (HWFEXC_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $vldExcptExist;
}

// Retrieve Y/N Description
function RtvYNDesc ($ynValue) {
	if     ($ynValue == "Y") {$ynDesc= "Yes";}
	elseif ($ynValue == "N") {$ynDesc= "No";}
	return $ynDesc;
}

// Retrieve Default Plant Number
function RtvDftPlant  () {
	global $userProfile, $HDDPLT;

	$dftPltNumber=RetValue("USUSER='$userProfile'", "SYUSER", "char(USDPLT)");
	if ($dftPltNumber<="0") {
		$dftPltNumber = $HDDPLT;
	}
	$dftPltName=RetValue("PLPLNT=$dftPltNumber ", "HDPLNT", "PLNAME");

	$returnValue['dftPltNumber']=$dftPltNumber;
	$returnValue['dftPltName']=$dftPltName;
	return $returnValue;
}

// Selection Edit (passing Hex Handle)
function Selection_Edit_Handle($pgmName = '',$profileHandle = '',$dataBaseID = '',$submitSchedule = '',$errFound = '',$edtVar = '',$errVar = '',$wrnVar = '') {
	global $pgmLibrary, $i5Connect;

	$pgmCall = array(
	array("Name"=>"profileHandle",   "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID",      "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"submitSchedule",  "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"errFound",        "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"wrnVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("$pgmName", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data ($pgmName) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"  =>$profileHandle,
	"dataBaseID"     =>$dataBaseID,
	"submitSchedule"=>$submitSchedule,
	"errFound"       =>$errFound,
	"edtVar"         =>$edtVar,
	"errVar"         =>$errVar,
	"wrnVar"         =>$wrnVar);

	$parmOut = array(
	"profileHandle"  =>"profileHandle",
	"dataBaseID"     =>"dataBaseID",
	"submitSchedule" =>"submitSchedule",
	"errFound"       =>"errFound",
	"edtVar"         =>"edtVar",
	"errVar"         =>"errVar",
	"wrnVar"         =>"wrnVar");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data ($pgmName) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['profileHandle']  =$profileHandle;
	$returnValue['dataBaseID']     =$dataBaseID;
	$returnValue['submitSchedule'] =$submitSchedule;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	$returnValue['wrnVar']         =$wrnVar;
	return $returnValue;
}

// Session Date Formated
function SessionDate($profileHandle, $dataBaseID){
	global $pgmLibrary, $i5Connect;
	$sessionDateFormat="";

	if (!$i5Connect) die("<br>SessionDate Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle"    , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID"       , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"sessionDateFormat", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"50")
	);

	$pgm = i5_program_prepare("SSYDTE_W", $pgmCall);
	if (!$pgm) {die("<br>SessionDate Program (SSYDTE_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"=>$profileHandle,
	"databaseID"=>$dataBaseID,
	"sessionDateFormat"=>$sessionDateFormat
	);

	$parmOut = array(
	"profileHandle"    =>"profileHandle",
	"dataBaseID"       =>"dataBaseID",
	"sessionDateFormat"=>"sessionDateFormat"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>SessionDate Program (SSYDTE_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $sessionDateFormat;
}

// Set Schema List
function SetLibraryList($profileHandle, $dataBaseID, $eID){
	global $pgmLibrary, $i5Connect, $libraryList, $fromSelect, $allowMixedCasePasswords;
	// Force User and Password to Upper
	$_SERVER['PHP_AUTH_USER'] = strtoupper($_SERVER['PHP_AUTH_USER']);
	if ($allowMixedCasePasswords != "Y") {
		$_SERVER['PHP_AUTH_PW'] = strtoupper($_SERVER['PHP_AUTH_PW']);
	}
	$userProfile = $_SERVER['PHP_AUTH_USER'];

	if (is_null($eID))$eID="";
	$authHandle="";
	$profileHandle="";
	$hTTPToken="";
	$activeRole="";
	$error="";

	// Optionally re-use an existing database connection for your transport
	// If you specify a naming mode (i5/sql) in your connection, make sure they match.
	$namingMode = DB2_I5_NAMING_ON;
	$existingDb = db2_connect('*LOCAL', $_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'], array('i5_naming' => $namingMode));
	if (!$existingDb) {
		$existingDb = db2_connect('*LOCAL-*DEBUG', $_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'], array('i5_naming' => $namingMode));
	}	
	// Add to existing connection options
	$options[CW_EXISTING_TRANSPORT_CONN] = $existingDb;
	$options[CW_EXISTING_TRANSPORT_I5_NAMING] = $namingMode;
	
	$i5Connect = i5_connect("localhost", '', '', $options);
	if (!$i5Connect) die("Set Schema List Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	if(!i5_command("chglibl",array("libl"=>$libraryList),array(),$i5Connect)) die ("Could not change Library List" . i5_errormsg($i5connect));

	if     ($_GET['hdsKey'])               {$hdsKey=$_GET['hdsKey'];}
	elseif (isset($_SESSION['hds_pw'])) {$hdsKey = $_SESSION['hds_pw'];}
	else {
		$result = db2_exec($i5Connect->GetConnection(), "Select HUPSWD From $pgmLibrary/SYUHDS Where HUUSER='HDS' ");
		$row = db2_fetch_assoc($result);
		$hdsKey = trim($row['HUPSWD']);
		$_SESSION['hds_pw'] = $hdsKey;
	}
	$hdsPW=Conv_HDS("HDS", "D", "                              ");
	$hdsPW=substr($hdsPW, 0, 10);
	
	$x = 0;
	while (!$i5Authority) {
		$i5Authority = i5_adopt_authority('HDS', $hdsPW, $i5Connect);
		if ($i5Authority) break;
		if ($x == 5) die("HDS User Profile failed. Error number =".i5_errno()." msg=".i5_errormsg());
		$x++;
	}
	
	if ($eID != "") {
		if ($fromSelect == "Y") {
			$result = db2_exec($i5Connect->GetConnection(), "Select coalesce(USROLE, ' ') From SYUSER Where USUSER='$userProfile'");
			while ($row = db2_fetch_assoc($result)){
				$activeRole = trim($row['00001']);
				$stmtSQL = "Update SYHAND Set HNROLE='$activeRole' Where HNHAND='$eID' ";
				$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
			}
		}
		$result = db2_exec($i5Connect->GetConnection(), "Select coalesce(HNXHND, ' '), coalesce(HNROLE, ' '), coalesce(HNUSER, ' ') From SYHAND Where HNHAND='$eID'");
		while ($row = db2_fetch_assoc($result)){
			$activeRole = trim($row['00002']);
			$profileHandle = trim($row['00001']);
		}
	}

	if ($profileHandle == ''){
		$pgmCall = array(
		array("Name"=>"userProfile", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
		array("Name"=>"eID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32"),
		array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
		array("Name"=>"hTTPToken", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32"),
		array("Name"=>"activeRole", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10")
		);

		$pgm = i5_program_prepare("HSYHND_W", $pgmCall);
		if (!$pgm) {die("<br>Set Handle Program Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

		$parmIn = array(
		"userProfile"  =>$userProfile,
		"eID"          =>$eID,
		"profileHandle"=>$profileHandle,
		"hTTPToken"    =>$hTTPToken,
		"activeRole"   =>$activeRole
		);

		$parmOut = array(
		"userProfile"  =>"userProfile",
		"eID"          =>"eID",
		"profileHandle"=>"profileHandle",
		"hTTPToken"    =>"hTTPToken",
		"activeRole"   =>"activeRole"
		);

		$ret = i5_program_call($pgm, $parmIn, $parmOut);
		if (function_exists('i5_output')) extract(i5_output());
		if (!$ret) {die("<br> Set Handle Program Call errno=".i5_errno()." msg=".i5_errormsg());}
	}
	$returnValue['profileHandle'] = $profileHandle;
	$returnValue['activeRole']    = $activeRole;
	$returnValue['eID']           = $eID;
	$returnValue['error']         = $error;

	return $returnValue;
}

// test alternate library list functions
function SetLibraryLista($profileHandle, $dataBaseID, $eID){
	global $pgmLibrary, $i5Connect, $allowMixedCasePasswords;
	// Force User and Password to Upper
	$_SERVER['PHP_AUTH_USER'] = strtoupper($_SERVER['PHP_AUTH_USER']);
	if ($allowMixedCasePasswords != "Y") {
		$_SERVER['PHP_AUTH_PW'] = strtoupper($_SERVER['PHP_AUTH_PW']);
	}
	$userProfile = $_SERVER['PHP_AUTH_USER'];

	if (is_null($eID ))$eID="";
	if (is_null($authHandle ))$authHandle="";
	if (is_null($profileHandle )) $profileHandle="";
	if (is_null($hTTPToken))	$hTTPToken="";
	if (is_null($activeRole)) $activeRole="";
	if (is_null($error ))$error="";

	$options[I5_OPTIONS_INITLIBL]=$pgmLibrary;
	$i5Connect = i5_connect("localhost", $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $options);
	if (!$i5Connect) die("Set Schema List Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"userProfile", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"authHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"eID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32"),
	array("Name"=>"httpToken", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32"),
	array("Name"=>"activeRole", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"error", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"75")
	);

	$pgm = i5_program_prepare("HSYENA_W", $pgmCall);
	if (!$pgm) {die("<br>Set Schema List Program Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"	=>$profileHandle,
	"dataBaseID"	=>$dataBaseID,
	"userProfile"	=>$userProfile,
	"authHandle"	=>$authHandle,
	"eID"			=>$eID,
	"httpToken"		=>$hTTPToken,
	"activeRole"	=>$activeRole,
	"error"			=>$error
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"dataBaseID"   =>"dataBaseID",
	"userProfile"  =>"userProfile",
	"authHandle"   =>"authHandle",
	"eID"          =>"eID",
	"httpToken"    =>"hTTPToken",
	"activeRole"   =>"activeRole",
	"error"        =>"error"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> Set Schema List Program Call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['profileHandle'] = $profileHandle;
	$returnValue['activeRole']    = $activeRole;
	$returnValue['eID']           = $eID;
	$returnValue['error']         = $error;

	return $returnValue;
}

// Update using SQL
function SQL_Update($e_stmtSQL){
	global $pgmLibrary, $i5Connect;
	$e_status="";

	$pgmCall = array(
	array("Name"=>"e_stmtSQL", "IO"=>I5_IN,  "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"e_status" , "IO"=>I5_OUT, "Type"=>I5_TYPE_CHAR, "Length"=>"5"));

	$pgm = i5_program_prepare("HSYSQL_U", $pgmCall);
	if (!$pgm) {die("<br>Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"e_stmtSQL"=>$e_stmtSQL
	);

	$parmOut = array(
	"e_status" =>"e_status"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}

	return $e_status;
}

// System Date
function SystemDate ($systemDate){
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>SessionDate Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"systemDate", "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"7.0"),
	);

	$pgm = i5_program_prepare("HSYSDT_W", $pgmCall);
	if (!$pgm) {die("<br>SystemDate Program (HSYSDT_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"systemDate"=>$systemDate
	);

	$parmOut = array(
	"systemDate"=>"systemDate"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>SessionDate Program (SSYDTE_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $systemDate;
}

// User View SQL
function User_View($profileHandle, $dataBaseID, $userViewVar, $uv_Sql){
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>User_View Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"userViewVar", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"uv_Sql", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000")
	);

	$pgm = i5_program_prepare("HSYCU3_W", $pgmCall);
	if (!$pgm) {die("<br>User_View Program Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"=>$profileHandle,
	"dataBaseID"=>$dataBaseID,
	"userViewVar"=>$userViewVar,
	"uv_Sql"=>$uv_Sql
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"dataBaseID"   =>"dataBaseID",
	"userViewVar"  =>"userViewVar",
	"uv_Sql"       =>"uv_Sql",
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>User_View Program call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['uv_Sql']     =$uv_Sql;
	$returnValue['userViewVar']=$userViewVar;
	return $returnValue;
}

// Write Control Table
function Write_Control_File ($homePath, $fileName, $pgmName){
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>$pgmName Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"homePath", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"50"),
	array("Name"=>"fileName", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"50"));

	$pgm = i5_program_prepare($pgmName, $pgmCall);
	if (!$pgm) {die("<br>$pgmName Program prepare errno=".i5_errno()." msg=".i5_errormsg());}
	$parmIn = array(
	"homePath"=>$homePath,
	"fileName"=>$fileName
	);

	$parmOut = array(
	"homePath"=>"homePath",
	"fileName"=>"fileName"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>$pgmName Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}
}

/*

%{ Retrieve Column Description (With) %}
function  RetValueWith(IN    CHAR(5000)  withRecord,
CHAR(32000) selectRecord,
CHAR(1000)  fileName,
CHAR(500)   fieldName,
INOUT CHAR(32000) fieldDesc)
{ %EXEC {HSYRFW_W.PGM %}
%}

%{ Calculate Years Between Two Dates %}
function Calc_Years(IN    CHAR(64) profileHandle,
IN    CHAR(2)  dataBaseID,
IN    CHAR(7)  fromDate,
IN    CHAR(7)  toDate,
INOUT DEC(5,0) years)
{
%EXEC {HSYCYB_W.PGM %}
%}

%{ Calculate New ISO Date %}
function Calc_ISO_Date (IN    CHAR(64) profileHandle,
IN    CHAR(2)  dataBaseID,
INOUT CHAR(10) dateISO,
IN    CHAR(1)  addSub,
IN    CHAR(1)  mdyCode,
IN    DEC(3,0) incr)
{
%EXEC {HSYISO_W.PGM %}
%}

%{ Period Reformat Program %}
function Reformat_Period(INOUT CHAR(7) CYPPeriod,
IN    CHAR(10) fromFormat)
{
%EXEC {CSYPDC_W.PGM %}
%}

%{ Convert Period To 4 digit Year %}
function Period_CYP_YYYY (INOUT CHAR(10) dateIn)
{
%EXEC {HSYPD4_W.PGM %}
%}

%{ Get Release Version %}
function Release_Version (IN CHAR(64) profileHandle,
IN CHAR(2)  dataBaseID,
IN CHAR(2)  apid,
INOUT DEC(5,1) release,
INOUT DEC(3,0) libLev)
{  %EXEC {HSYAPP_W.PGM %}
%}

%{ Retrieve Error Message %}
function Ret_Error_Msg   (IN CHAR(64) profileHandle,
IN CHAR(2)  dataBaseID,
IN CHAR(7)  errorNumber,
INOUT CHAR(67) errorMessage)
{  %EXEC {HSYERR_W.PGM %}
%}

%{ Check Backup Table Prefix Exists %}
function checkFilePrefix (INOUT CHAR(4) filePrefix,
CHAR(1) prefixExists)
{
%EXEC {HHDCKF_W.PGM %}
%}

%{ Asset User View %}
function  AssetUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  UVSite,
DEC(12,0) UVAsset)
{ %EXEC {HFAASP_W.PGM %}
%}

%{ Employee User View %}
function  EmployeeUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(2)   applicationID,
CHAR(1)   userPass,
DEC(2,0)  UVCo,
DEC(4,0)  UVFac,
DEC(5,0)  UVPREmpl,
DEC(2,0)  UVHRCo,
DEC(9,0)  UVHREmpl)
{ %EXEC {HHREMP_W.PGM %}
%}

%{ Bank User View %}
function BankUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  bankNumber)
{ %EXEC {HHDBANK_W.PGM %}
%}

%{ Payroll Bank User View %}
function PrBankUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(2)   applicationID,
CHAR(1)   userPass,
DEC(3,0)  bankNumber)
{ %EXEC {HPRBANK_W.PGM %}
%}

function CoFacUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(2,0)  companyNumber,
DEC(4,0)  facilityNumber)
{ %EXEC {HHDCFAC_W.PGM %}
%}

function HrCoFacUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(2,0)  companyNumber,
DEC(4,0)  facilityNumber)
{ %EXEC {HHRCOFC_W.PGM %}
%}

%{ Item Plant User View %}
function  ItemPlantUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  plantNumber,
CHAR(15)  itemNumber)
{ %EXEC {HHDIPLT_W.PGM %}
%}

%{ Item Warehouse User View %}
function  ItemWarehouseUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  warehouseNumber,
CHAR(15)  itemNumber)
{ %EXEC {HHDIWHS_W.PGM %}
%}

%{ Kanban User View %}
function  KanbanUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  plantNumber,
CHAR(15)  itemNumber)
{ %EXEC {HHDKBMS_W.PGM %}
%}

%{ Location User View %}
function  LocationUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  locationNumber)
{ %EXEC {HHDLCTN_W.PGM %}
%}

%{ Lot User View %}
function  LotUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  warehouseNumber,
CHAR(15)  itemNumber)
{ %EXEC {HHDLOT_W.PGM %}
%}

%{ Plant User View %}
function  PlantUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  plantNumber)
{ %EXEC {HHDPLNT_W.PGM %}
%}

%{ Vendor User View %}
function  VendorUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(7,0)  vendorNumber)
{ %EXEC {HHDVEND_W.PGM %}
%}

%{ Warehouse User View %}
function WarehouseUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  warehouseNumber)
{ %EXEC {HHDWHSM_W.PGM %}
%}

%{ Work Center User View %}
function  WorkCenterUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  plantNumber,
CHAR(5)   department,
CHAR(5)   workCenter)
{ %EXEC {HHDMWCM_W.PGM %}
%}

%{ Salesman User View %}
function  SalesmanUserView(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(1)   userPass,
DEC(3,0)  salesmanNumber)
{ %EXEC {HHDSLSM_W.PGM %}
%}

%{ Workflow Task Request User View %}
function  WFWrkItmUserView(INOUT CHAR(64)    profileHandle,
CHAR(2)     dataBaseID,
CHAR(1)     userPass,
CHAR(32000) edtVar)
{ %EXEC {HWFUSV_W.PGM %}
%}

%{ Fill Available To Promise Work %}
function Fill_ATP_Work_File (INOUT CHAR(64)     profileHandle,
CHAR(2)      dataBaseID,
DECIMAL(3,0) plantNumber,
CHAR(15)     itemNumber)
{%EXEC {HMS230_W.PGM %}
%}


%{ Retrieve Quantity Available For Item/Whs %}
function Get_Qty_Avail (IN    CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
CHAR(15)  itemNumber,
DEC(3,0)  wareHouse,
INOUT DEC(13,4) qtyAvailable)
{%EXEC {HHDRAV_W.PGM %}
%}

%{ Retrieve Summarized Quantities For Item/Plant %}
function Get_Qty_ItemPlt (INOUT  CHAR(32000) edtVar)
{%EXEC {HHDSWQ_W.PGM %}
%}



%{ Retrieve Unit Costs %}
function Rtv_Unit_Cost (IN    DEC(3,0)  plantNumber,
CHAR(15)  itemNumber,
DEC(3,0)  wareHouse,
CHAR(15)  lotNumber,
INOUT DEC(13,5) totalCost,
DEC(13,5) cat1Cost,
DEC(13,5) cat2Cost,
DEC(13,5) cat3Cost,
DEC(13,5) cat4Cost,
DEC(13,5) cat5Cost)
{%EXEC {HHDRUC_W.PGM %}
%}

%{ Retrieve Customer Price For An Item %}
function Cust_Unit_Price (IN  CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
DEC(7,0)  vendCustNumber,
CHAR(15)  itemNumber,
INOUT DEC(13,5) unitPrice,
DEC(3,0)  wareHouse,
DEC(9,4)  piecesPerPricing)
{%EXEC {HOEPRC_W.PGM %}
%}

%{ Retrieve Vendor Price For An Item %}
function Vend_Unit_Price (IN  CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
DEC(7,0)  vendcustNumber,
CHAR(15)  itemNumber,
DEC(3,0)  wareHouse,
INOUT DEC(13,5) unitPrice)
{%EXEC {HPOPRC_W.PGM %}
%}

%{ Retrieve Order Type User Defined Column Descriptions %}
function Order_Type_UDF(INOUT CHAR(64) profileHandle,
CHAR(2)  dataBaseID,
CHAR(1)  orderType,
CHAR(23) dateOneDescripiton,
CHAR(23) dateTwoDescripiton,
CHAR(23) dateThreeDescripiton,
CHAR(23) uDFOneDescripiton,
CHAR(23) uDFTwoDescripiton,
CHAR(23) uDFThreeDescripiton,
CHAR(23) uDFFourDescripiton,
CHAR(23) uDFFiveDescripiton)
{
%EXEC {HHDOUP_W.PGM %}
%}
*/

/*
%{ Customer A/R Balance %}
function CustArBalance(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
DEC(7,0)  customerNumber,
CHAR(1)   arBalanceType,
CHAR(3)   arBalanceCurrency,
DEC(15,2) arBalanceAmount)
{
%EXEC {HHDARB_W.PGM %}
%}

%{ Customer A/R Aging %}
function LoadARAging(INOUT CHAR(64)  profileHandle,
DEC(7,0)  customerNumber)
{
%EXEC {HARAGI_W.PGM %}
%}

%{ Load A/P Aging %}
function LoadAPAging(INOUT CHAR(64)  profileHandle)
{
%EXEC {HAPAGI_W.PGM %}
%}

function RtvCustCont(IN    CHAR(64) profileHandle,
CHAR(2)  dataBaseID,
DEC(7,0) contactNumber,
OUT   CHAR(30) firstName,
CHAR(30) lastName,
CHAR(4)  salutation,
CHAR(30) companyName)
{
%EXEC {HCRRCN_W.PGM %}
%}

function RtvSuppCont(IN    CHAR(64) profileHandle,
CHAR(2)  dataBaseID,
DEC(7,0) contactNumber,
OUT   CHAR(30) firstName,
CHAR(30) lastName,
CHAR(4)  salutation,
CHAR(30) companyName)
{
%EXEC {HSRRCN_W.PGM %}
%}

%{ Return Plant Number From Warehouse Supply %}
function RetPltNbr(INOUT CHAR(64) profileHandle,
CHAR(2)  dataBaseID,
CHAR(10) subroutine,
DEC(3,0) warehouseNumber,
CHAR(15) itemNumber,
CHAR(3)  returnPlant)
{
%EXEC {SHDRWS_W.PGM %}
%}

%{ Return Warehouse Number From Warehouse Supply %}
function RetWhsNbr(INOUT CHAR(64) profileHandle,
CHAR(2)  dataBaseID,
CHAR(10) subroutine,
DEC(3,0) plantNumber,
CHAR(15) itemNumber,
CHAR(3)  returnWarehouse)
{
%EXEC {SHDRWS_W.PGM %}
%}

%{ Employee User View %}
function  RetEmpNam(IN    DEC(2,0)  PRComp,
DEC(4,0)  PRFACL,
DEC(5,0)  PREmpl,
DEC(2,0)  HRCo,
DEC(9,0)  HREmpl,
OUT   CHAR(18)  lastName,
CHAR(18)  firstName,
CHAR(1)   middleInitial,
CHAR(23)  reportName,
CHAR(4)   termCode)
{ %EXEC {HHRREN_W.PGM %}
%}

function  RetHrCompNam(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
DEC(2,0)  HRCo,
DEC(4,0)  HRFacl,
CHAR(30)  coFacName)
{ %EXEC {HHRCFN_W.PGM %}
%}


function  RetCalEvents(INOUT CHAR(64)    profileHandle,
CHAR(2)     dataBaseID,
CHAR(10)    loadProgram,
CHAR(4)     year,
CHAR(2)     month,
CHAR(1)     week,
CHAR(10)    startDate,
CHAR(10)    endDate,
CHAR(1)     reload,
CHAR(1)     pastDue,
CHAR(32000) searchVar,
CHAR(500)   searchDsp)
{ %EXEC {HSYCLE_W.PGM %}
%}

function  RetDayName(IN    CHAR(64) profileHandle,
CHAR(2)  dataBaseID,
CHAR(10) dateIn,
INOUT CHAR(9)  dayName)
{ %EXEC {HSYRDD_W.PGM %}
%}

function  RetPayCodeDesc(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
DEC(2,0)  PRComp,
DEC(4,0)  PRFacl,
CHAR(3)   PRCode,
CHAR(20)  prCodeDesc)
{ %EXEC {HHRPCD_W.PGM %}
%}

function  RetTotDed(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
DEC(11,0) CkRef,
DEC(9,2)  totDed)
{ %EXEC {HPRTVD_W.PGM %}
%}

function  RetHoursUnits(INOUT CHAR(64)  profileHandle,
CHAR(2)   dataBaseID,
DEC(11,0) CkRef,
CHAR(1)   Cksumt,
DEC(9,2)  CkHours,
DEC(9,2)  CkUnits,
DEC(9,2)  CkWkHr)
{ %EXEC {HPRHRU_W.PGM %}
%}

%{ Retrieve In Process Flag %}
function In_Process   (IN CHAR(64) profileHandle,
IN CHAR(2)  dataBaseID,
INOUT CHAR(1) InProc)
{  %EXEC {HPRCCF_W.PGM %}
%}

*/

function Retrieve_AcctJrnl_Data ($profileHandle,$dataBaseID,$coNumber,$facNumber,$accountNumber,$subAccount,$fromPer,$toPer,$incUnposted) {
	if (is_null($coNumber))      {$coNumber=0;}
	if (is_null($facNumber))     {$facNumber=0;}
	if (is_null($accountNumber)) {$accountNumber=0;}
	if (is_null($subAccount))    {$subAccount=0;}
	if (is_null($fromPer))       {$fromPer=0;}
	if (is_null($toPer))         {$toPer=0;}
	if (is_null($incUnposted))   {$incUnposted="N";}
	$acctName="";
	$coFacName="";
	$balanceIncome="";
	$currencyUnit="";
	$currencyType="";
	$beginBal=0;

	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Retrieve_AcctJrnl_Data Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID"   , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"coNumber"     , "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"2.0"),
	array("Name"=>"facNumber"    , "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"4.0"),
	array("Name"=>"accountNumber", "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"4.0"),
	array("Name"=>"subAccount"   , "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"4.0"),
	array("Name"=>"fromPer"      , "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"5.0"),
	array("Name"=>"toPer"        , "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"5.0"),
	array("Name"=>"incUnposted"  , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"acctName"     , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"30"),
	array("Name"=>"coFacName"    , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"30"),
	array("Name"=>"balanceIncome", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"13"),
	array("Name"=>"currencyUnit" , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8"),
	array("Name"=>"currencyType" , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"3"),
	array("Name"=>"beginBal"     , "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"15.2")
	);

	$pgm = i5_program_prepare("HGLDDA_W", $pgmCall);
	if (!$pgm) {die("<br>Retrieve_AcctJrnl_Data Program (HGLDDA_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle" =>$profileHandle,
	"dataBaseID"    =>$dataBaseID,
	"coNumber"      =>$coNumber,
	"facNumber"     =>$facNumber,
	"accountNumber" =>$accountNumber,
	"subAccount"    =>$subAccount,
	"fromPer"       =>$fromPer,
	"toPer"         =>$toPer,
	"incUnposted"   =>$incUnposted,
	"acctName"      =>$acctName,
	"coFacName"     =>$coFacName,
	"balanceIncome" =>$balanceIncome,
	"currencyUnit"  =>$currencyUnit,
	"currencyType"  =>$currencyType,
	"beginBal"      =>$beginBal
	);

	$parmOut = array(
	"profileHandle" =>"profileHandle",
	"dataBaseID"    =>"dataBaseID",
	"coNumber"      =>"coNumber",
	"facNumber"     =>"facNumber",
	"accountNumber" =>"accountNumber",
	"subAccount"    =>"subAccount",
	"fromPer"       =>"fromPer",
	"toPer"         =>"toPer",
	"incUnposted"   =>"incUnposted",
	"acctName"      =>"acctName",
	"coFacName"     =>"coFacName",
	"balanceIncome" =>"balanceIncome",
	"currencyUnit"  =>"currencyUnit",
	"currencyType"  =>"currencyType",
	"beginBal"      =>"beginBal"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Retrieve_AcctJrnl_Data Program (HGLDDA_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['acctName']      =$acctName;
	$returnValue['coFacName']     =$coFacName;
	$returnValue['balanceIncome'] =$balanceIncome;
	$returnValue['currencyUnit']  =$currencyUnit;
	$returnValue['currencyType']  =$currencyType;
	$returnValue['beginBal']      =$beginBal;
	return $returnValue;
}
// Convert Data
function Conv_HDS($e_type, $e_mode, $e_value){
	global $pgmLibrary, $i5Connect;

	$pgmCall = array(
	array("Name"=>"e_type", "IO"=>I5_INOUT,  "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"e_mode", "IO"=>I5_INOUT,  "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"e_value", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"30"));

	$pgm = i5_program_prepare("HSYDCV_O", $pgmCall);
	if (!$pgm) {die("<br>Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"e_type"=>$e_type,
	"e_mode"=>$e_mode,
	"e_value"=>$e_value);

	$parmOut = array(
	"e_type"=>"e_type",
	"e_mode"=>"e_mode",
	"e_value"=>"e_value");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}
	
	return $e_value;
}

function Rtv_Unit_Cost ($item,$whs,$lot = "",$plant = 0) {
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Retrieve Unit Costs Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"plant"		, "IO"=>I5_IN	, "Type"=>I5_TYPE_PACKED	, "Length"=>"3.0"),
	array("Name"=>"item"		, "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR		, "Length"=>"15"),
	array("Name"=>"whs"			, "IO"=>I5_IN   , "Type"=>I5_TYPE_PACKED	, "Length"=>"3.0"),
	array("Name"=>"lot" 		, "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR		, "Length"=>"15"),
	array("Name"=>"cost"		, "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED	, "Length"=>"13.5"),
	array("Name"=>"cat1Cost"	, "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED	, "Length"=>"13.5"),
	array("Name"=>"cat2Cost"	, "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED	, "Length"=>"13.5"),
	array("Name"=>"cat3Cost"	, "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED	, "Length"=>"13.5"),
	array("Name"=>"cat4Cost"	, "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED	, "Length"=>"13.5"),
	array("Name"=>"cat5Cost"	, "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED	, "Length"=>"13.5"),
	);

	$pgm = i5_program_prepare("HHDRUC_W", $pgmCall);
	if (!$pgm) {die("<br>Retrieve Unit Costs Program (HHDRUC_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"plant"	=>$plant,
	"item"	=>$item,
	"whs"	=>$whs,
	"lot"	=>$lot,
	);

	$parmOut = array(
	"cost"		=>"cost",
	"cat1Cost"	=>"cat1Cost",
	"cat2Cost"	=>"cat2Cost",
	"cat3Cost"	=>"cat3Cost",
	"cat4Cost"	=>"cat4Cost",
	"cat5Cost"	=>"cat5Cost"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Retrieve Unit Costs Program (HHDRUC_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['cost']		= $cost;
	$returnValue['cat1Cost']	= $cat1Cost;
	$returnValue['cat2Cost']	= $cat2Cost;
	$returnValue['cat3Cost']	= $cat3Cost;
	$returnValue['cat4Cost']	= $cat4Cost;
	$returnValue['cat5Cost']	= $cat5Cost;
	return $returnValue;
}

function Check_Identity_Column ($table,$column,$stmtSQL) {
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Check Identity Column Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$maxSQL = "Select max({$column}) + 1 as MAXID From {$table}";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $maxSQL );
	$row = db2_fetch_assoc ( $sqlResult );
	if (array_key_exists ( 'MAXID', $row )) {
		$maxSQL = "ALTER TABLE {$table} ALTER COLUMN {$column} RESTART WITH {$row['MAXID']}";
		$status = db2_exec ( $i5Connect->getConnection (), $maxSQL );
		if ($status) {
			$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		}
	}
	return;
}
?>