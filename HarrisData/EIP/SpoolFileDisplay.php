 <?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';

$page_title = "Display Spool File";
$scriptName = "SpoolFileDisplay.php";
$formatToPrint = "Y";

$jobName = $_GET["jobName"];
$userName = $_GET["userName"];
$jobNbr = $_GET["jobNbr"];
$spoolFileName = $_GET["spoolFileName"];
$spoolFileNumber = $_GET["spoolFileNumber"];
$tempFile = $homePath . 'Trace/' . trim($jobNbr) . trim($spoolFileName) . '.txt';
$i5Authority = i5_adopt_authority('HDS', $hdsPW, $i5Connect);
if (! $i5Authority)
    die("User Profile failed. Error number =" . i5_errno() . " msg=" . i5_errormsg());
i5_spool_get_data($spoolFileName, $jobName, $userName, $jobNbr, $spoolFileNumber, $tempFile);
$report = file_get_contents($tempFile);
unlink($tempFile);

if ($report) {
    require_once ($docType);
    print "\n <html> \n	<head>";
    require_once ($headInclude);
    
    require_once ($genericHead);
    print "\n </head>";
    print "\n <body $bodyTagAttr";
    require_once 'Banner.php';
    print "\n <table $basetable>";
    print "\n <tr valign=\"top\">";
    print "\n <td>";
    print "<pre>" . $report . "</pre>";

    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    print "\n </body> \n </html>";
} else {
	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n alert (\"Spool file could not be displayed\"); \n";
	print "\n window.close() \n";
	print "\n </script> \n";
}

?>