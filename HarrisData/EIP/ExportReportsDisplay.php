<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$csvFile = $_GET ['csvFile'];
$page_title = "File " . $csvFile;
$scriptName = "ExportReportsDisplay.php";
ob_end_clean();

require_once($docType);
print "\n <html> <head>";
require_once($headInclude);
require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n <td class=\"toolbar\">";
print "<a href=\"javascript:window.close()\">{$closeImageMed}</a>";
print "</td></tr></table>";
print "$hrTagAttr";

print "\n <table $contentTable> <tr>";
$displayPath = $homePath . "Attachments/" . $dataBaseID . "/Reports/" . $userProfile . "/" . $csvFile;
$stream = fopen($displayPath, 'r');
while (($row = fgetcsv($stream)) !== false) {
    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\">";
    foreach ($row as $col) {
        print "\n <td class=\"colalph\">$col</td>";
    }
    print "\n </tr>";
    ob_end_clean();
}
print "\n </tr> </table>";
print "$hrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
print "</body> </html>";
fclose($stream);
?>