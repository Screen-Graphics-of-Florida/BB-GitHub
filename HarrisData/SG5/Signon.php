<?php

$initPath = $_SERVER['SCRIPT_FILENAME'];
$strPos = strripos($initPath, "/", 2) +1;
$initPath = substr_replace($initPath, "", $strPos);
require_once 'IncludePath.php';
require_once 'CopyrightBanner.php';

$baseVar     = (isset($_GET['baseVar']))     ? $_GET['baseVar'] : "";
$eID         = (isset($_GET['eID']))         ? $_GET['eID']        : null;
$fromSelect  = (isset($_GET['fromSelect']))  ? $_GET['fromSelect'] : "";
$newSession  = (isset($_GET['newSession']))  ? $_GET['newSession'] : "";

if ($newSession == "Y"){$eID = null;}

$firstTime="";
if (is_null($eID) || $fromSelect == "Y"){$firstTime="Y";}

if ($baseVar == ""){
	if (file_exists("{$initPath}/BaseConfig.xml")) {
		$xml = simplexml_load_file('BaseConfig.xml');
		$host=$_SERVER['HTTP_HOST'];
		$port="";
		$strPos=strPos($host, ":");
		if ($strPos > 0){$port=substr($host, ($strPos +1), (strlen($host)) - ($strPos));}
		else            {$port="80";}
		$wrkPort  = $xml->xpath("port[@id='" . trim(strtoupper($port)) . "']");
		$baseVar = (string) $wrkPort[0];
			}
	else {$baseVar = "BaseConfiguration.php";}
;}

require_once ($baseVar);
require_once 'GenericDirectCallVariables.php';

if (strlen($_SERVER['PHP_AUTH_USER']) > 10) {
    $_SERVER['PHP_AUTH_USER'] = substr($_SERVER['PHP_AUTH_USER'], 0 , 10);
}

$userProfile = $_SERVER['PHP_AUTH_USER'];
$returnValue=setLibraryList($profileHandle, $dataBaseID, $eID);
$profileHandle=$returnValue['profileHandle'];
$activeRole   =$returnValue['activeRole'];
$eID          =$returnValue['eID'];

if ($firstTime == "Y"){
	$fileName="APControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HAPCTL_I");}

	$fileName="ARControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HARCTL_I");}

	$fileName="ETControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HETCTU_I");}

	$fileName="FAControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HFACTL_I");}

	$fileName="GLControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HGLCTL_I");}

	$fileName="InventoryControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HIVCTL_I");}

	$fileName="MCControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HMCCTL_I");}

	$fileName="OEControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HOECTL_I");}

	$fileName="PEControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HPECTL_I");}

	$fileName="POControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HPOCTL_I");}

	$fileName="PRControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HPRCTL_I");}

	$fileName="SystemControl{$dataBaseID}.php";
	$includeName= "{$homePath}{$fileName}";
	if (!file_exists($includeName)) {Write_Control_File($homePath, $fileName, "HSYCTL_I");}
}

if ($newSession == "Y") {
	print "<meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}Welcome.php?baseVar=" . urlencode($baseVar) . "&amp;eID=" . urlencode($eID) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
} else {
	print "<meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$cGIPath}SignonSetup.d2w/SETUP?baseVar=" . urlencode($altBaseVar) . "&amp;eID=" . urlencode($eID) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
}
?>