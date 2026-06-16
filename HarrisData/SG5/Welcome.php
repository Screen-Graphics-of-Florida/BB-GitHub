<?php
require_once 'GetURLParm.php';
$alertMessage  = (isset($_GET['alertMessage']))  ? $_GET['alertMessage']  : null;
require_once 'CopyrightBanner.php';

require_once ($baseVar);
require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
$page_title   = "Sign On";
$scriptName   = "Signon.php";
$maxRows      = "999";
$fileExists   =  "";
$expDays      = "";
$portal       = "";
$pageID       = "";

$checkExp=Check_Pswd_Exp($userProfile);

require_once 'Menu.php';
require_once ($docType);
print  "<html> <head>";
require_once ($headInclude);
print "\n <script TYPE=\"text/javascript\">";
require_once 'NewWindowOpen.php';
require_once 'Menu.js';
print "\n </script>";
require_once ($genericHead);
print  "\n </head> <body $bodyTagAttr>";
require_once 'Banner.php';
print  "\n <table $baseTable>  <tr valign=\"top\">";
$formatToPrint = "";
$pageID = "";
require_once 'MenuDisplay.php';
print  "\n <td class=\"content\">";
require_once ($welcomeContent);
print  "</td> </tr> </table>";
require_once 'Trailer.php';
if ($alertMessage){
	print "\n <script>alert(\"$alertMessage\")</script>";
	$alertMessage = "";
}
print  "</body> </html>";

?>