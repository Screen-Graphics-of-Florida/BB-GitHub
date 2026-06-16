<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';

$errorMsg = (isset($_GET['errorMsg']))  ? $_GET['errorMsg'] : 'No error message found';

require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
print "\n <script TYPE=\"text/javascript\">";
require_once 'Menu.js';
print "\n </script>";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "DISPLAYERRORMSG";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td></tr>";
print "\n <tr><td class=\"accessError\">$errorMsg</td></tr> ";
print "\n </table>";
print $hrTagAttr;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";

?>

