<?php
session_start();
require_once 'IncludePath.php';
$baseVar = $_GET['baseVar'];
$eID     = $_GET['eID'];
$fromURL = "'from$eID'";
$retURL  = "'ret$eID'";
$portal  = $_GET['portal'];
$tag     = (isset($_GET['tag']))           ? $_GET['tag']           : null;

if ($baseVar == ""){
	$initPath = $_SERVER['SCRIPT_FILENAME'];
	$strPos = strripos($initPath, "/", 2) +1;
	$initPath = substr_replace($initPath, "", $strPos);
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
}

require_once ($baseVar);

$startRow 	   = (isset($_GET['startRow']))      ? $_GET['startRow']      : 1;
$sequence      = (isset($_GET['sequence']))      ? $_GET['sequence']      : null;
$formatToPrint = (isset($_GET['formatToPrint'])) ? $_GET['formatToPrint'] : null;
$chgSrch       = (isset($_GET['chgSrch']))       ? $_GET['chgSrch']       : null;
$tblID         = (isset($_GET['tblID']))         ? $_GET['tblID']         : 0;
$pagID         = (isset($_GET['pagID']))         ? $_GET['pagID']         : 0;
$confMessage   = (isset($_GET['confMessage']))   ? $_GET['confMessage']   : null;
?>