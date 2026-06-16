<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'QuickLink.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "Journal Inquiry";
$scriptName = "JournalInquiry.php";
$scriptVarBase = "{$genericVarBase}";
$altScriptVarBase = "{$altVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$attachFolder = "JournalInquiry";
$programName = "JOURNALINQ";
$program_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_02 = $program_OPT['sec_02'];
$home = "{$homePath}{$uploadDirectory}{$dataBaseID}/{$attachFolder}/";

if ($tag == "DISPLAY") {
    $csvFile = $_GET['csvFile'];
    $filename = "{$home}{$csvFile}";
    $handle = fopen($filename, "r");
    require_once($docType);
    print "\n <html> ";
    print "\n    <body $bodyTagAttr> ";
    require_once($headInclude);
    print "\n <table $contentTable>";
    $desc = 'Journal Inquiry';
    print "\n <tr><td><h1>$desc</h1></td></tr>";
    print "\n <tr><td><h2>$csvFile</h2><td class='legendSubTitle' style=\"padding-left: 60px\">(Type: DL=Delete, PT/PX=Add, UB=Update Before, UP=Update After)</td></td></tr>";
    print "\n </table> ";

    print "\n <table $contentTable>";
    $csvcontents = fgetcsv($handle);
    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\"> ";
    $orderKey = 0;
    foreach ($csvcontents as $key => $headercolumn) {
        $orderKey = (trim($headercolumn) == 'Order Number') ? $key : $orderKey;
        $custKey = (trim($headercolumn) == 'Customer') ? $key : $custKey;
        print "\n <th class=\"colhdr\">$headercolumn</th>";
    }
    print "\n </tr>";
    while ($csvcontents = fgetcsv($handle)) {
        require 'SetRowClass.php';
        print "\n <tr class=\"$rowClass\"> ";
        foreach ($csvcontents as $key => $column) {
            if (preg_match('/^[0-9.-]+$/', $column)) {
                $colClass = "colnmbr";
            } else {
                $colClass = "colalph";
            }
            $linked = null;
            if ($key == $orderKey) {
                $order = RetValue("OEORD#={$column}", "OEORHD", "OEORD#");
                if ($order == $column) {
                    $linked = true;
                    print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrder.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode($column) . "\" title=\"View Order Detail\">$column</a></td>";
                }
            } elseif ($key == $custKey) {
                $cust = RetValue("CMCUST={$column}", "HDCUST", "CMCUST");
                if ($cust == $column) {
                    $linked = true;
                    print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode($column) . "\" title=\"View Customer\">$column</a></td>";
                }
            }
            if (is_null($linked)) {
                print "\n <td class=\"{$colClass}\">$column</td>";
            }
        }
        print "\n </tr>";
    }
    print "</table></body> </html>";
    fclose($handle);
    exit();
}

if ($tag == "DOWNLOAD") {
    $csvFile = $_GET['csvFile'];
    $attachShortName = str_replace(' ', '_', $csvFile);
    $csvFile = "{$home}{$csvFile}";
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment;filename = $attachShortName");
    header('Content-Transfer-Encoding: binary');
    ob_clean();
    readfile($csvFile);
    exit();
}

if ($tag == "DELETE") {
    $csvFile = $_GET['csvFile'];
    $csvFile = "{$home}{$csvFile}";
    unlink($csvFile);
    rmdir($home);
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$baseURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
    exit();
}

require_once($docType);
print "\n <html> \n	<head>";
$formName = "Search";
require_once($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "JOURNALINQUIRY";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y") {
    print "\n <td class=\"toolbar\">";
    require_once 'HelpPage.php';
    print "</td>";
}

$i = 0;
$arraycount = 0;
if (is_dir($home)) {
    if ($handle = opendir($home)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $path = "$home/$file";
                $base = explode('.', basename($path));
                $extension = array_pop($base);
                $filearray[filemtime($path) . $i] = $file;
                $i++;
            }
        }
    }
    closedir($handle);
}

print "\n </tr></table>";
require_once 'ConfMessageDisplay.php';
print $hrTagAttr;

print "<table $contentTable> <tr>";
if ($formatToPrint != "Y") {
    print "<th class=\"colhdr\">$optionHeading</th>";
}
Format_Column_Header("", "File Name");
Format_Column_Header("", "Created");

krsort($filearray);
reset($filearray);
while (list ($key, $val) = each($filearray)) {
    $noEntries = (strpos($val, 'NO JOURNAL ENTRIES FOUND') !== false) ? true : null;
    $maintainVar = "{$scriptVarBase}&amp;csvFile=" . urlencode(trim($val));
    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\">";
    if ($formatToPrint != "Y") {
        print "\n <td class=\"colalph\"> ";
        if (is_null($noEntries)) {
            print "<a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}{$maintainVar}&amp;tag=DOWNLOAD\">$downloadCsv</a>";
        }
        if ($sec_02 == "Y") {
            print "<a onClick=\"return confirmDelete('{$val}')\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}{$maintainVar}&amp;tag=DELETE\">$deleteImageSml</a>";
        }
        print "</td>";
    }
    if (is_null($noEntries)) {
        print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=DISPLAY&amp;csvFile=" . urlencode(trim($val)) . "\" onclick=\"{$inquiryWinVar}\" title=\"Click here to display file\">$val</a></td>";
    } else {
        print "\n <td class=\"colalph\">$val</td>";
    }
    print "\n <td class=\"colalph\">" . date('F d Y H:i:s.', filemtime($home . '/' . $val)) . "</td>";
    print "\n </tr>";
}

require_once 'XMLExport.php';
print "\n </table>";
$nextPrevPos = "1";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>?>