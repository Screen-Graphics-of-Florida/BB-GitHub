<?php

require_once 'CopyrightBanner.php';

// obtain URL parameters
$baseVar = $_GET['baseVar'];
$eID     = $_GET['eID'];
if (is_null($baseVar)){$baseVar = '';}
if (is_null($eID)){$eID = '';}

// Assign or default base configuration table

if ($baseVar == ""){
	if (file_exists('BaseConfig.xml')) {
		$xml = simplexml_load_file('BaseConfig.xml');
		$host=$_SERVER['HTTP_HOST'];
		$port="";
		$strPos=strPos($host, ":");
		if ($strPos > 0){
			$port=substr($host, ($strPos +1), (strlen($host)) - ($strPos));
		}
		$wrkPort  = $xml->xpath("port[@id='" . trim(strtoupper($port)) . "']");
		$baseVar = (string) $wrkPort[0];
	}
	else {$baseVar = "BaseConfiguration.php";}
}

	require_once ($baseVar);
	require_once 'GenericDirectCallVariables.php';

	$userProfile = $_SERVER['PHP_AUTH_USER'];
	$returnValue=setLibraryList($profileHandle, $dataBaseID, $eID);
	$profileHandle=$returnValue['profileHandle'];
	$activeRole   =$returnValue['activeRole'];
	$eID          =$returnValue['eID'];

	print "<meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}SignonSelect.php?baseVar=" . urlencode($baseVar) . "&amp;eID=" . urlencode($eID) . "\">";

?>