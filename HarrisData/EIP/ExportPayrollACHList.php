<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$bankFile = $_GET['bankFile'];
$saveBAXSLT = $_GET['saveBAXSLT'];
$payDate = $_GET['payDate'];

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
require_once ($baseExportFile);

$page_title = "Payroll ACH";
$scriptName = "ExportPayrollACHList.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$attachFolder = "ExportPayrollACH";

if (is_null($tag)) {
    $tag = "REPORT";
}

if ($tag == "REPORT" || $tag == "EXPORT") {
    if ($tag != "EXPORT") {
        require_once ($docType);
        print "\n <html> \n	<head>";
        $formName = "Search";
        require_once ($headInclude);

        print "\n \n <script TYPE=\"text/javascript\">";
        require_once 'Menu.js';
        print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
        print "\n function confirmTransform() {return confirm(\"OK to transform?\");} \n";
        print "\n </script> \n";

        require_once ($genericHead);
        print "\n </head>";
        print "\n <body $bodyTagAttr>";
        require_once 'Banner.php';
        print "\n <table $baseTable>";
        print "\n <tr valign=\"top\">";
        $pageID = "APPLCASHBATCH";
        require_once 'MenuDisplay.php';
        print "\n <td class=\"content\">";
        print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
        print "\n <tr><td><h1>$page_title</h1></td>";

        if ($formatToPrint != "Y") {
            print "\n <td class=\"toolbar\">";
            require_once 'XMLFormat.php';
            require_once 'FormatToprint.php';
            require_once 'HelpPage.php';
            print "</td>";
        }
    }

//    $i5Authority = i5_adopt_authority($userProfile, $_SERVER['PHP_AUTH_PW'], $i5Connect);
//    if (! $i5Authority)
//        die("User Profile failed. Error number =" . i5_errno() . " msg=" . i5_errormsg());

    $i = 0;
    $arraycount = 0;
    $home = "{$homePath}{$exportDirectory}{$dataBaseID}/{$prACHDirectory}";

    $dircheck = "{$homePath}{$exportDirectory}{$dataBaseID}/{$prACHDirectory}";
    if (is_dir($dircheck)) {
        if ($handle = opendir($home)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $path = "$home/$file";
                    $base = explode('.', basename($path));
                    $extension = array_pop($base);
                    $filearray[$i] = $file;
                    $i ++;
                }
            }
        }
        closedir($handle);
    }
    if ($tag != "EXPORT") {
        print "\n </tr></table>";
        print $hrTagAttr;
    }

    if ($tag != "EXPORT") {
        print "<table $contentTable> <tr>";
        if ($formatToPrint != "Y") {
            print "<th class=\"colhdr\">$optionHeading</th>";
        }
        Format_Column_Header("", "File Name");
        Format_Column_Header("", "Pay Date");
        Format_Column_Header("", "Date Modified");
        Format_Column_Header("", "XSL Transformation");
    }

    if ($tag == "EXPORT") {
        $xmlListName = "ExportPayrollACHList";
        require_once 'XMLInit.php';
    }

    sort($filearray);
    reset($filearray);
    while (list ($key, $val) = each($filearray)) {
        if (stripos($val, ".xml")) {
            $xml = simplexml_load_file("{$homePath}{$exportDirectory}{$dataBaseID}/{$prACHDirectory}{$val}");
            $result = $xml->xpath('//bank'); // returns an array with one element
            $bankFile = (string) strtoupper($result[0]->ach_transmission_file);
            $saveBAXSLT = (string) strtoupper($result[0]->ach_XSLT_file);
            $result = $xml->xpath('//batch'); // returns an array with one element
            $payDate = (string) strtoupper($result[0]->check_date);
        } else {
            $bankFile = "";
            $saveBAXSLT = "";
            $payDate = "";
        }

        if ($tag == "EXPORT") {
            $xmlID = $xmlDoc->createElement(ExportPayrollACH);
            $xmlRoot->appendChild($xmlID);
            $xmlTag = $xmlID->appendChild($xmlDoc->createElement("FileName"));
            $xmlTag->appendChild($xmlDoc->createTextNode($val));
            $xmlTag = $xmlID->appendChild($xmlDoc->createElement("PayDate"));
            $xmlTag->appendChild($xmlDoc->createTextNode($payDate));
            $xmlTag = $xmlID->appendChild($xmlDoc->createElement("DateModified"));
            $xmlTag->appendChild($xmlDoc->createTextNode(date('F d Y H:i:s.', filemtime($homePath . $exportDirectory . $dataBaseID . "/" . $prACHDirectory . $val))));
            $xmlTag = $xmlID->appendChild($xmlDoc->createElement("XSLTTransformation"));
            $xmlTag->appendChild($xmlDoc->createTextNode($saveBAXSLT));
        } else {

            $maintainVar = "{$scriptVarBase}&amp;bankFile=" . urlencode(trim($bankFile)) . "&amp;saveBAXSLT=" . urlencode(trim($saveBAXSLT));

            require 'SetRowClass.php';
            print "\n <tr class=\"$rowClass\">";
            if ($formatToPrint != "Y") {
                print "\n <td class=\"colalph\"> ";
                if ($saveBAXSLT != "") {
                    print "<a onClick=\"return confirmTransform()\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}{$maintainVar}&amp;tag=Edit_Data\">$xmlTransformImageSml</a>";
                } else {
                    print "&nbsp";
                }
                print "</td>";
            }
            print "\n <td class=\"colalph\"><a href=\"{$homePath}{$exportDirectory}{$dataBaseID}/{$prACHDirectory}$val\" target=\"_blank\" title=\"View File\">$val</a></td>";
            print "\n <td class=\"colalph\">$payDate</td>";
            print "\n <td class=\"colalph\">" . date('F d Y H:i:s.', filemtime($homePath . $exportDirectory . $dataBaseID . "/" . $prACHDirectory . $val)) . "</td>";
            print "\n <td class=\"colalph\">$saveBAXSLT</td>";
            print "\n </tr>";
        }
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
    exit();
}

if ($tag == "Edit_Data") {
//    $i5Authority = i5_adopt_authority($userProfile, $_SERVER['PHP_AUTH_PW'], $i5Connect);
//    if (! $i5Authority)
//        die("User Profile failed. Error number =" . i5_errno() . " msg=" . i5_errormsg());
        // Load XML input file
    $xml = new DOMDocument();
    $xml->load("{$exportDirectory}{$dataBaseID}/{$prACHDirectory}{$bankFile}.xml");

    if (! $xml->schemaValidate('ExportPayrollACH.xsd')) {
        print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
        libxml_display_errors();
        $exportXSL = "N";
    } else {
        require "ExportPayrollXSLInclude.php";
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
    }
}

?>