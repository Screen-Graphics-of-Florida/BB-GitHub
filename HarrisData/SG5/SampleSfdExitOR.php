<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$plantNumber         = $_GET['plantNumber'];
$mfgOrder            = $_GET['mfgOrder'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'VarBase.php';

$page_title     = "Sample Shop Floor Dispatch Mfg Order Receipt Exit";
$scriptName     = "SampleSfdExitOR.php";
$scriptVarBase  = "{$genericVarBase}&amp;plantNumber=" . urlencode(trim($plantNumber)) . "&amp;mfgOrder=" . urlencode(trim($mfgOrder)) . "&amp;touchScreen=Y";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require $inquiryBanner;
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

$stmtSQL = " Select * From HDMOHM Where OHPLT=$plantNumber and OHORD='$mfgOrder'";
require 'stmtSQLEnd.php';

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n <td>&nbsp;</td>";
print "\n <td class=\"toolbar\"> ";
require_once 'HelpPage.php';
require 'CloseWindow.php';
print "\n </td> ";
print "\n </tr> </table> ";

print $hrTagAttr;

$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
$row = db2_fetch_assoc($sqlResult);

print "\n <table $contentTable> <tr>";

$plantName=RetValue("PLPLNT=$plantNumber", "HDPLNT", "PLNAME");
print "\n <tr><td class=\"dsphdr\">Plant</td> ";
print "\n     <td class=\"dspnmbr\">$plantNumber</td>";
print "\n     <td class=\"dspalph\">$plantName</td>";
print "\n </tr> ";

print "\n <tr><td class=\"dsphdr\">Mfg Order</td> ";
print "\n     <td class=\"dspalph\">$mfgOrder</td>";
print "\n     <td class=\"dspalph\">&nbsp;</td>";
print "\n </tr> ";

print "\n </table>";
print $hrTagAttr;
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
print "\n </body> \n </html>";
?>	
