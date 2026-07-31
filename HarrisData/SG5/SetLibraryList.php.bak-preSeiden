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

if (strlen($_SERVER['PHP_AUTH_USER']) > 10) {
    $_SERVER['PHP_AUTH_USER'] = substr($_SERVER['PHP_AUTH_USER'], 0 , 10);
}

$_SERVER['PHP_AUTH_USER'] = strtoupper($_SERVER['PHP_AUTH_USER']);
if ($allowMixedCasePasswords != "Y") {
	$_SERVER['PHP_AUTH_PW'] = strtoupper($_SERVER['PHP_AUTH_PW']);
}
$userProfile = $_SERVER['PHP_AUTH_USER'];


if (is_null($eID))$eID="";
$hTTPToken="";
$activeRole="";
$profileHandle="";

// Optionally re-use an existing database connection for your transport
// If you specify a naming mode (i5/sql) in your connection, make sure they match.
$namingMode = DB2_I5_NAMING_ON;
//logThis('before db2_connect');
$existingDb = db2_connect('*LOCAL', $_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'], array('i5_naming' => $namingMode));
if (!$existingDb) {
	$existingDb = db2_connect('*LOCAL-*DEBUG', $_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'], array('i5_naming' => $namingMode));
}

	//logThis('after db2_connect: db object=' . var_export($existingDb, true));
// Add to existing connection options
$options[CW_EXISTING_TRANSPORT_CONN] = $existingDb;
$options[CW_EXISTING_TRANSPORT_I5_NAMING] = $namingMode;

$i5Connect = i5_connect("localhost", '', '', $options);
if (!$i5Connect) die("Set Library List Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

if(!i5_command("chglibl",array("libl"=>$libraryList),array(),$i5Connect)) die ("Invalid User Setup " . i5_errormsg($i5connect));

if ($_GET['hdsKey'])                {$hdsKey=$_GET['hdsKey'];}
elseif (isset($_SESSION['hds_pw'])) {$hdsKey = $_SESSION['hds_pw'];}
else {
	$result = db2_exec($i5Connect->GetConnection(), "Select HUPSWD From $pgmLibrary/SYUHDS Where HUUSER='HDS' ");
	$row = db2_fetch_assoc($result);
	$hdsKey = trim($row['HUPSWD']);
	$_SESSION['hds_pw'] = $hdsKey;
}

$hdsPW=Convert_HDS("HDS", "D", '');
$hdsPW=substr($hdsPW, 0, 10);
$x = 0;
while (!$i5Authority) {
	$i5Authority = i5_adopt_authority('HDS', $hdsPW, $i5Connect);
	if ($i5Authority) break;
	if ($x == 5) die("HDS User Profile failed. Error number =".i5_errno()." msg=".i5_errormsg());
	$x++;
}

$eUser = $userProfile;
if ($eID != "") {
	$result = db2_exec($i5Connect->GetConnection(),"Select coalesce(HNXHND, ' ') as HNXHND, coalesce(HNROLE, ' ') as HNROLE, SYUSER.* From SYHAND inner join SYUSER on HNUSER=USUSER Where HNHAND='$eID' ");
	$row = db2_fetch_assoc($result);
	$eUser = trim($row['USUSER']);
	if ($eUser == $userProfile) {
		$activeRole    = trim($row['HNROLE']);
		$profileHandle = trim($row['HNXHND']);
		$profileName   = trim($row['USDESC']);
		$admin         = trim($row['USADMN']);
		$allowSecInq   = trim($row['USAASI']);
		$accessDoc     = trim($row['USADOC']);
		$dftPltNumber  = trim($row['USDPLT']);
		$newsLink      = trim($row['USNEWS']);
		$userEmail     = trim($row['USEMAL']);
	} else {
		$eUser = "";
		$eID = "";
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
	"userProfile" 	=>$userProfile,
	"eID"         	=>$eID,
	"profileHandle" =>$profileHandle,
	"hTTPToken"  	=>$hTTPToken,
	"activeRole"  	=>$activeRole
	);

	$parmOut = array(
	"userProfile"  	=>"userProfile",
	"eID"          	=>"eID",
	"profileHandle" =>"profileHandle",
	"hTTPToken"    	=>"hTTPToken",
	"activeRole"   	=>"activeRole"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> Set Handle Program Call errno=".i5_errno()." msg=".i5_errormsg());}
}


if ($eUser != $userProfile) {
	$result = db2_exec($i5Connect->GetConnection(),"Select coalesce(HNXHND, ' ') as HNXHND, coalesce(HNROLE, ' ') as HNROLE, SYUSER.* From SYHAND inner join SYUSER on HNUSER=USUSER Where HNHAND='$eID' ");
	$row = db2_fetch_assoc($result);
	$activeRole    = trim($row['HNROLE']);
	$profileHandle = trim($row['HNXHND']);
	$profileName   = trim($row['USDESC']);
	$admin         = trim($row['USADMN']);
	$allowSecInq   = trim($row['USAASI']);
	$accessDoc     = trim($row['USADOC']);
	$dftPltNumber  = trim($row['USDPLT']);
	$newsLink      = trim($row['USNEWS']);
	$userEmail     = trim($row['USEMAL']);
}

// Convert Data
function Convert_HDS($e_type, $e_mode, $e_value){
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
?>