<?php
require_once 'GetURLParm.php';
if ($tag != "DOWNLOAD") {
    require_once 'CopyrightBanner.php';
}
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'QuickLink.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'XMLValidateInclude.php';
require_once($baseExportFile);

$page_title = "EEO Reporting";
$scriptName = "ExportEEOList.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$attachFolder = "EEO";
$eeoDirectory = "EEO/";

if (is_null($tag)) {
    $tag = "REPORT";
}

if ($tag == "DOWNLOAD") {
    $eeofile = $_GET ['eeoFile'];
    $file = "{$homePath}{$exportDirectory}{$dataBaseID}/{$eeoDirectory}{$eeofile}";
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');//change your extension of your files
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
    }
}

if ($tag == "DELETE") {
    $eeofile = $_GET ['eeoFile'];
    $file = "{$homePath}{$exportDirectory}{$dataBaseID}/{$eeoDirectory}{$eeofile}";
    if (file_exists($file)) {
        $submitSchedule = "";
        $edtVar = "";
        Concat_Field("@@mncd", "D");
        Concat_Field("@@file", $file);
        $edtVar .= "}{";
        $returnValue = Selection_Edit_Handle("HSYFTP_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
        $confMessage = 'Confirm Delete of ' . $eeofile;
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$baseURL}&tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        exit ();
    }
}

require_once($docType);
print "\n <html> \n	<head>";
$formName = "Search";
require_once($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'Menu.js';
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "EEO1REPORT";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y") {
    print "\n <td class=\"toolbar\">";
    require_once 'FormatToprint.php';
    require_once 'HelpPage.php';
    print "</td>";
}

//$i5Authority = i5_adopt_authority($userProfile, $_SERVER['PHP_AUTH_PW'], $i5Connect);
//if (!$i5Authority)
//    die("User Profile failed. Error number =" . i5_errno() . " msg=" . i5_errormsg());

$i = 0;
$arraycount = 0;
$home = "{$homePath}{$exportDirectory}{$dataBaseID}/{$eeoDirectory}";
if (is_dir($home)) {
    if ($handle = opendir($home)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $path = "$home/$file";
                $base = explode('.', basename($path));
                $extension = array_pop($base);
                $filearray[$i] = $file;
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
Format_Column_Header("", "Date Modified");

sort($filearray);
reset($filearray);
while (list ($key, $val) = each($filearray)) {
    $maintainVar = "{$scriptVarBase}&amp;eeoFile=" . urlencode(trim($val));

    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\">";
    if ($formatToPrint != "Y") {
        print "\n <td class=\"colalph\"> ";
        print "<a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}{$maintainVar}&amp;tag=DOWNLOAD\">$downloadCsv</a>";
        print "<a onClick=\"return confirmDelete('{$val}')\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}{$maintainVar}&amp;tag=DELETE\">$deleteImageSml</a>";
        print "</td>";
    }
    print "\n <td class=\"colalph\"><a href=\"{$homePath}{$exportDirectory}{$dataBaseID}/{$eeoDirectory}$val\" target=\"_blank\" title=\"View File\">$val</a></td>";
    print "\n <td class=\"colalph\">" . date('F d Y H:i:s.', filemtime($homePath . $exportDirectory . $dataBaseID . "/" . $eeoDirectory . $val)) . "</td>";
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
?>