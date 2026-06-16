<?php
require_once 'GetURLParm.php';
$maintenanceCode = $_GET ['maintenanceCode'];

require_once 'CopyrightBanner.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'QuickLink.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once($baseExportFile);

$page_title = "Reports in CSV for " . $profileName;
$scriptName = "ExportReports.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$reportsDir = "Attachments/" . $dataBaseID . "/Reports/" . $userProfile . "/";

if (is_null($tag)) {
    $tag = "REPORT";
}
if (($tag == "REPORT") || ($tag == "DOWNLOAD")) {
    require_once($docType);
    print "\n <html> \n	<head>";
    $formName = "Search";
    require_once($headInclude);

    print "\n \n <script TYPE=\"text/javascript\">";
    require_once 'Menu.js';
    print "\n function confirmDelete(file) {return confirm(\"Confirm Delete of File:\" + \"\\n\" + \"\\n\" + file);} \n";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";
    print "\n <body $bodyTagAttr>";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "EXPORTREPORTS";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
    print "\n <tr><td><h1>$page_title</h1></td>";

    print "\n <td class=\"toolbar\">";
    require_once 'HelpPage.php';
    print "</td>";

    $i = 0;
    $arraycount = 0;
    $exportPath = "{$homePath}/{$reportsDir}";
    if (is_dir($exportPath)) {
        if ($handle = opendir($exportPath)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $path = "$exportPath/$file";
                    $base = explode('.', basename($path));
                    $extension = array_pop($base);
                    $file_list[] = array('mtime' => date('F d Y H:i:s', filemtime($path)), 'name' => $file, 'size' => filesize($path));
                }
            }
        }
        closedir($handle);
    }
    print "\n </tr></table>";
    require_once 'ConfMessageDisplay.php';
    print $hrTagAttr;
    print "<table $contentTable> <tr>";
    print "<th class=\"colhdr\">$optionHeading</th>";
    Format_Column_Header("", "File Name");
    Format_Column_Header("", "Date Modified");

    // Sort $file_list
    array_multisort($file_list, SORT_DESC, array_keys($file_list));
    foreach ($file_list as $file) {
        require 'SetRowClass.php';
        print "\n <tr class=\"$rowClass\">";
        print "\n <td class=\"opticon\"> ";
	    $val = $file['name'];
        print "\n <a onClick=\"return confirmDelete('$val')\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;csvFile=" . urlencode(trim($val)) . "&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
        print "\n <a href=\"{$homePath}{$reportsDir}$val\" target=\"_blank\" title=\"Download File\">$downloadCsv</a>";
	    print "</td>";
	    if (substr($val, -4) == ".csv") {
            print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}ExportReportsDisplay.php{$scriptVarBase}&amp;backURL=". urlencode(trim($baseURL)) . "\&amp;csvFile=" . urlencode(trim($val)) . "\" target=\"_blank\" onClick=\"$workloadWinVar\" title=\"View File\">$val</a></td>";
        } else {
	        print "\n <td class=\"colalph\"><type=\"hidden\" title=\"View File\">$val</td>";
        }
        print "\n <td class=\"colalph\">{$file['mtime']}</td>";
        print "\n </tr>";
    }
    print "\n </table>";
    $nextPrevPos = "1";
    require_once 'PageBottom.php';
    require_once 'WildCardPrint.php';
    print "$hrTagAttr";
    require_once 'Copyright.php';

    print "\n </td> </tr> </table>";
    require_once 'Trailer.php';
    print "\n </body> \n </html>";
    exit();
}

if ($tag == "Edit_Data") {
    $tmpFile = '/tmp/sftp_' . $userProfile . '_transfer.sftp';
    $from = 'put ' . $homepath . '/Attachments/' . $dataBaseID . '/Reports/' . $userProfile . '/' . $_GET ['csvFile'];
    $to = '/var/www/AppsInHD/Database/DB' . strtoupper($AppsInHd_FTP_DB) . '/importdata/' . $_GET ['csvFile'];
    file_put_contents($tmpFile, $from . ' ' . $to);
    $edtVar = "";
    $user = 'hd_' . strtolower($AppsInHd_FTP_DB) . '_db';
    Concat_Field("@@mncd", $maintenanceCode);
    Concat_Field("@@user", $user);
    Concat_Field("@@host", $AppsInHd_FTP_Server);
    Concat_Field("@@temp", $tmpFile);
    if ($maintenanceCode == 'D') {
        $exportPath = "{$homePath}/{$reportsDir}{$_GET ['csvFile']}";
        Concat_Field("@@file", $exportPath);
    } 
}

    require 'ScheduleJobConcat.php';   // Schedule Entries Values
    $edtVar .= "}{";

    $returnValue = Selection_Edit_Handle("HSYFTP_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
    $submitSchedule = $returnValue['submitSchedule'];
    $errFound = $returnValue['errFound'];
    $edtVar = $returnValue['edtVar'];
    $errVar = $returnValue['errVar'];
    $wrnVar = $returnValue['wrnVar'];

    require 'SubmitScheduleUpdate.php';
    $confMessage = Format_ConfMsg_Desc("", 'Confirm Submit of Delete for ' . $_GET ['csvFile'], "", "", "", "", "");
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "&amp;tag=REPORT&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
?>