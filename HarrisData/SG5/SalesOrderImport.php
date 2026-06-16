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

$page_title = "Sales Order Import";
$scriptName = "SalesOrderImport.php";
$scriptVarBase = "{$genericVarBase}";
$altScriptVarBase = "{$altVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$attachFolder = "Import";
$programName = "IMPORT";
$quickLinkByUser = "Y";

if ($tag == "DISPLAY") {
    $attachDesc = $_GET['attachDesc'];
    $filename = $homePath . $_GET['attachLongName'];
    $fromLog = $_GET['fromLog'];
    $handle = fopen($filename, "r");
    require_once($docType);
    print "\n <html> ";
    print "\n    <body $bodyTagAttr> ";
    require_once($headInclude);
    print "\n <table $contentTable>";
    $desc = ($fromLog == 'Y') ? 'Import Log' : 'Imports To Process';
    print "\n <tr><td><h1>$desc</h1></td></tr>";
    print "\n <tr><td><h2>$attachDesc</h2></td></tr>";
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
    $attachShortName = $_GET['attachShortName'];
    $csvFile = $_GET['csvFile'];
    $csvFile = "{$homePath}{$csvFile}";
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    $attachShortName = str_replace(' ', '_', $attachShortName);
    header("Content-Disposition: attachment;filename = $attachShortName");
    header('Content-Transfer-Encoding: binary');
    ob_clean();
    readfile($csvFile);
    exit();
}

if ($tag == "DELETE") {
    $fromImport = (isset($_GET['fromImport'])) ? $_GET['fromImport'] : null;
    $attachFolderU = $_GET['attachFolderU'];
    $attachVarKey = $_GET['attachVarKey'];
    $attachLongName = $_GET['attachLongName'];
    $attachShortName = $_GET['attachShortName'];
    $attachDesc = $_GET['attachDesc'];
    $filePath = $homePath . $attachLongName;
    unlink($filePath);
    $attachPath = "{$homePath}{$uploadDirectory}{$dataBaseID}/{$attachFolder}/{$attachVarKey}/";
    rmdir($attachPath);
    require 'stmtSQLClear.php';
    $stmtSQL .= " Delete From SYD2WA Where ATFOLD='$attachFolderU' and ATVKEY='$attachVarKey' and ATATNS='$attachShortName' ";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
    if (!is_null($fromImport)) {
        $confMessage = "Confirm Import of Sales Orders for {$attachShortName}";
    } else {
        $maintenanceCode = "D";
        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $attachDesc, $attachShortName, "", "", "", "");
    }
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$baseURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
    exit();
}

require_once($docType);
print "\n <html> <head> ";
require_once($headInclude);

print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n function confirmProcess(text) {return confirm(\"$procRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> ";

require_once($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "SalesOrderImport";
if ($formatToPrint == "") {
    require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";

require_once 'QuickLinkTable.php'; // QuickLink Table
require_once 'QuickLinkByUser.php'; // QuickLink By User

// Program Option Security
$import_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $import_OPT['sec_01'];
$sec_02 = $import_OPT['sec_02'];
$sec_03 = $import_OPT['sec_03'];
$sec_04 = $import_OPT['sec_04'];

// Remove Configuration if not authorized
if ($sec_04 != "Y") {
    foreach ($quicklinkSeqTable as $key => $quickRow) {
        $link = trim(strtolower($quickRow[QDQLNKU]));
        if ($link == 'configuration') {
            unset($quicklinkSeqTable[$key]);
        }
    }
}

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

$attachVarKey = 'ImportsToProcess';
$attachForDesc = "";
$attachPrg1 = "";
if ($formatToPrint != "Y") {
    $maintainVar = "{$altScriptVarBase}&amp;fromScript=$scriptName";
    print "\n <td class=\"toolbar\">";
    if ($sec_01 == "Y") {
        $attachForDesc = 'Imports To Process';
        require_once 'AttachmentInclude.php';
    }
    if ($sec_04 == "Y") {
        $attachForDesc = 'Configuration';
        $xMLConfig = (string)"<img border=\"0\" src=\"{$homeURL}{$imagePath}lgXML.gif\" title=\"Click here attach Configuration XML\" alt=\"XML Config\">";
        print "\n <a href=\"{$homeURL}{$phpPath}Attachment.PHP{$scriptVarBase}&amp;attachFolder=" . urlencode($attachFolder) . "&amp;attachForDesc=" . urlencode($attachForDesc) . "&amp;attachVarKey=Configuration&amp;userProfile=" . urlencode($userProfile) . "&amp;attachPrg1=" . urlencode($attachPrg1) . "&amp;attachPrg2=" . urlencode($attachPrg2) . "&amp;attachPrg3=" . urlencode($attachPrg3) . "&amp;attachPrg4=" . urlencode($attachPrg4) . "&amp;attachPrg5=" . urlencode($attachPrg5) . "\" onclick=\"$selectionWinVar\">$xMLConfig</a> ";
    }
    if (file_exists('R15.0_Sales_Order_Import.pdf')) {
        $designIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgHelp.gif\" title=\"View Sales Order Import Design\" alt=\"Help\">";
        print "<a href=\"R15.0_Sales_Order_Import.pdf\" target=\"_blank\">{$designIcon}</a>";
    }
    print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";
require_once 'ConfMessageDisplay.php';

print $hrTagAttr;
require_once 'QuickLinkDisplay.php';

$configCount = RetValue("ATFOLD='IMPORT' and ATVKEY='Configuration' and trim(upper(ATATNS)) like '%.XML'", "SYD2WA", "char(count(*))");

$delImage = (string)"<img align=\"right\" border=\"0\" src=\"{$homeURL}{$imagePath}smDelete.gif\" title=\"Remove row\" alt=\"Remove\">";
$x = 1;
foreach ($quicklinkSeqTable as $quickRow) {
    if ($x <= $quicklinkCount) {
        require 'QuickLinkBegLoop.php'; // Quicklink Begin
        if ($qLinkPos !== false) {

            if ($quicklinkRef == "importopen" || $quickLinksInUse == "N") {
                $attachFolderU = strtoupper($attachFolder);
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                $stmtSQL = " Select SYD2WA.*, Coalesce(USDESC,' ') as USDESC ";
                $fileSQL = "  SYD2WA ";
                $fileSQL .= " left join SYUSER on ATUSER=USUSER ";
                $selectSQL = " ATFOLD<>' ' and ATFOLD='{$attachFolderU}' and ATVKEY='{$attachVarKey}'";
                $selectSQL .= "  and (ATUSER='{$userProfile}' or ATPRIV=' ' or '$admin' ='Y')";
                require 'stmtSQLSelect.php';
                $orderBy = "ATTSTP desc,ATDESCU";
                $stmtSQL .= " Order By $orderBy";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

                if ($quickLinksInUse != "N") {
                    print "\n <a name=\"importopen\"></a> ";
                    require 'QuickLinkTopOfForm.php';
                } else {
                    print "\n <fieldset class=\"legendBody\"> ";
                    print "\n     <legend class=\"legendTitle\">Imports To Process</legend> ";
                }

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        if ($sec_02 == "Y" || $sec_03 == "Y") {
                            Format_Column_Header("", "Opt");
                        }
                        Format_Column_Header("ATDESCU", "Description");
                        Format_Column_Header("ATATNSU", "Attachment Name");
                        Format_Column_Header("ATUSER", "User");
                        Format_Column_Header("date(ATTSTP)", "Date");
                        Format_Column_Header("time(ATTSTP)", "Time");
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }

                    $attDate = TimeStamp_CYMD($row[ATTSTP]);
                    $attDate = Format_Date($attDate, "D");
                    $attTime = TimeStamp_TIME($row[ATTSTP]);
                    $attTime = EditHrsMinSec($attTime);
                    $ext = trim(strtoupper(pathinfo($row['ATATNL'], PATHINFO_EXTENSION)));
                    require 'SetRowClass.php';
                    print "\n <tr class=\"$rowClass\"> ";
                    if ($sec_02 == "Y" || $sec_03 == "Y") {
                        print "\n <td class=\"colicon\">";
                        if ($sec_02 == "Y" && ($ext == "CSV") && $configCount > "0") {
                            if ($configCount > "1") {
                                $xmlFile = 'SELECT';
                            } else {
                                $xmlFile = RetValue("ATFOLD='IMPORT' and ATVKEY='Configuration' and trim(upper(ATATNS)) like '%.XML'", "SYD2WA", "ATATNS");
                            }
                            $maintainVar = "{$scriptVarBase}&amp;xmlFile={$xmlFile}&amp;processFile=" . urlencode(trim($row['ATATNS'])) . "&amp;attachDesc=" . urlencode(trim($row['ATDESC']));
                            $confirmDesc = Format_Confirm_Desc("Process Import for:", "", trim($row['ATDESC']), "", "", "");
                            $procOrder = (string)"<img border=\"0\" src=\"{$homeURL}{$imagePath}smOrder.gif\" title=\"Click here to process import into Sales Orders\" alt=\"Process\">";
                            print "\n <a onClick=\"return confirmProcess('$confirmDesc')\" href=\"{$homeURL}{$phpPath}SalesOrderImportValidateXML.php{$maintainVar}\" onclick=\"{$inquiryWinVar}\" >$procOrder</a>";
                        }
                        if ($sec_03 == "Y") {
                            $maintainVar = "{$scriptVarBase}&amp;attachFolderU=" . urlencode(trim($row['ATFOLD'])) . "&amp;attachVarKey=ImportsToProcess&amp;attachShortName=" . urlencode(trim($row['ATATNS'])) . "&amp;attachLongName=" . urlencode(trim($row['ATATNL'])) . "&amp;attachDesc=" . urlencode(trim($row['ATDESC'])) . "&amp;bodyFile=" . urlencode($row['ATBODY']) . "&amp;attachPrivate=" . urlencode($row['ATPRIV']) . "&amp;directLink=" . urlencode($row['ATDIRL']) . "&amp;attachUser=" . urlencode(trim($row['ATUSER']));
                            $confirmDesc = Format_Confirm_Desc("Imports To Process:", "", trim($row['ATDESC']), "", "", "");
                            print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}{$scriptName}{$maintainVar}&amp;tag=DELETE\">$delImage</a>";
                        }
                        print "\n </td>";
                    }
                    print "\n     <td class=\"colalph\">$row[ATDESC]</td>";
                    $longName = $homePath . trim($row['ATATNL']);
                    $fileFound = file_exists($longName);
                    if ($fileFound && $ext == "CSV") {
                        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}SalesOrderImport.php{$scriptVarBase}&amp;tag=DISPLAY&amp;fromLog=N&amp;attachDesc=" . urlencode(trim($row['ATDESC'])) . "&amp;attachLongName=" . urlencode(trim($row['ATATNL'])) . "\" onclick=\"{$inquiryWinVar}\" title=\"Click here to display file\">$row[ATATNS]</a></td>";
                    } else {
                        print "\n     <td class=\"colalph\">$row[ATATNS]</td>";
                    }
                    print "\n     <td class=\"colalph\">$row[USDESC]</td>";
                    print "\n <td class=\"colalph\">$attDate</td>";
                    print "\n <td class=\"colalph\">$attTime</td>";
                    print "\n </tr>";

                    $startRow++;
                    $rowCount++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table>";
                print "\n </fieldset>";
                print "\n <br>";
            }

            if ($quicklinkRef == "importlog" || $quickLinksInUse == "N") {
                $attachFolderU = strtoupper($attachFolder);
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                $stmtSQL = " Select SYD2WA.*, Coalesce(USDESC,' ') as USDESC ";
                $fileSQL = "  SYD2WA ";
                $fileSQL .= " left join SYUSER on ATUSER=USUSER ";
                $selectSQL = " ATFOLD<>' ' and ATFOLD='{$attachFolderU}' and ATVKEY='ImportLog'";
                require 'stmtSQLSelect.php';
                $orderBy = "ATTSTP desc,ATDESCU";
                $stmtSQL .= " Order By $orderBy";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

                if ($quickLinksInUse != "N") {
                    print "\n <a name=\"importLog\"></a> ";
                    require 'QuickLinkTopOfForm.php';
                } else {
                    print "\n <fieldset class=\"legendBody\"> ";
                    print "\n     <legend class=\"legendTitle\">Import Log</legend> ";
                }

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        if ($sec_03 == "Y") {
                            Format_Column_Header("", "Opt");
                        }
                        Format_Column_Header("ATDESCU", "Description");
                        Format_Column_Header("ATATNSU", "Attachment Name");
                        Format_Column_Header("ATUSER", "User");
                        Format_Column_Header("date(ATTSTP)", "Date");
                        Format_Column_Header("time(ATTSTP)", "Time");
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }

                    $attDate = TimeStamp_CYMD($row[ATTSTP]);
                    $attDate = Format_Date($attDate, "D");
                    $attTime = TimeStamp_TIME($row[ATTSTP]);
                    $attTime = EditHrsMinSec($attTime);
                    require 'SetRowClass.php';
                    print "\n <tr class=\"$rowClass\"> ";
                    $longName = $homePath . trim($row['ATATNL']);
                    $fileFound = file_exists($longName);
                    if ($fileFound || $sec_03 == "Y") {
                        print "\n <td class=\"colicon\">";
                    }
                    if ($fileFound) {
                        $maintainVar = "{$scriptVarBase}&amp;csvFile=" . urlencode(trim($row['ATATNL'])) . "&amp;attachShortName=" . urlencode(trim($row['ATATNS']));
                        print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$maintainVar}&amp;tag=DOWNLOAD\" title=\"Click here to download\">$downloadCsv</a>";
                    }
                    if ($sec_03 == "Y") {
                        $maintainVar = "{$scriptVarBase}&amp;attachFolderU=" . urlencode(trim($row['ATFOLD'])) . "&amp;attachVarKey=ImportLog&amp;attachShortName=" . urlencode(trim($row['ATATNS'])) . "&amp;attachLongName=" . urlencode(trim($row['ATATNL'])) . "&amp;attachDesc=" . urlencode(trim($row['ATDESC'])) . "&amp;bodyFile=" . urlencode($row['ATBODY']) . "&amp;attachPrivate=" . urlencode($row['ATPRIV']) . "&amp;directLink=" . urlencode($row['ATDIRL']) . "&amp;attachUser=" . urlencode(trim($row['ATUSER']));
                        $confirmDesc = Format_Confirm_Desc("Import Log:", "", trim($row['ATDESC']), "", "", "");
                        print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}{$scriptName}{$maintainVar}&amp;tag=DELETE\">$delImage</a>";
                    }
                    if ($fileFound || $sec_03 == "Y") {
                        print "\n </td>";
                    }
                    print "\n     <td class=\"colalph\">$row[ATDESC]</td>";
                    if ($fileFound) {
                        print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}SalesOrderImport.php{$scriptVarBase}&amp;tag=DISPLAY&amp;fromLog=Y&amp;attachDesc=" . urlencode(trim($row['ATDESC'])) . "&amp;attachLongName=" . urlencode(trim($row['ATATNL'])) . "\" target=\"_blank\" title=\"Click here to display file\">$row[ATATNS]</a></td>";
                    } else {
                        print "\n     <td class=\"colalph\">$row[ATATNS]</td>";
                    }
                    print "\n     <td class=\"colalph\">$row[USDESC]</td>";
                    print "\n <td class=\"colalph\">$attDate</td>";
                    print "\n <td class=\"colalph\">$attTime</td>";
                    print "\n </tr>";

                    $startRow++;
                    $rowCount++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table>";
                print "\n </fieldset> ";
                print "\n <br>";
            }

            if ($quicklinkRef == "configuration" || $quickLinksInUse == "N") {
                $attachFolderU = strtoupper($attachFolder);
                require 'QuickLinkClear.php';
                require 'stmtSQLClear.php';
                $stmtSQL = " Select SYD2WA.*, Coalesce(USDESC,' ') as USDESC ";
                $fileSQL = "  SYD2WA ";
                $fileSQL .= " left join SYUSER on ATUSER=USUSER ";
                $selectSQL = " ATFOLD<>' ' and ATFOLD='{$attachFolderU}' and ATVKEY='Configuration'";
                require 'stmtSQLSelect.php';
                $orderBy = "ATTSTP desc,ATDESCU";
                $stmtSQL .= " Order By $orderBy";
                require 'stmtSQLEnd.php';
                require 'stmtSQLTotalRows.php';
                $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

                if ($quickLinksInUse != "N") {
                    print "\n <a name=\"importLog\"></a> ";
                    require 'QuickLinkTopOfForm.php';
                } else {
                    print "\n <fieldset class=\"legendBody\"> ";
                    print "\n     <legend class=\"legendTitle\">Import Log</legend> ";
                }

                print "\n <table $contentTable>";

                $rowCount = 0;
                $startRow = 1;
                while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
                    if ($startRow == 1) {
                        print "\n <tr> ";
                        if ($sec_03 == "Y") {
                            Format_Column_Header("", "Opt");
                        }
                        Format_Column_Header("ATDESCU", "Description");
                        Format_Column_Header("ATATNSU", "Attachment Name");
                        Format_Column_Header("ATUSER", "User");
                        Format_Column_Header("date(ATTSTP)", "Date");
                        Format_Column_Header("time(ATTSTP)", "Time");
                        print "\n </tr> ";
                    }

                    if ($rowCount >= $dspMaxRows) {
                        break;
                    }

                    $attDate = TimeStamp_CYMD($row[ATTSTP]);
                    $attDate = Format_Date($attDate, "D");
                    $attTime = TimeStamp_TIME($row[ATTSTP]);
                    $attTime = EditHrsMinSec($attTime);
                    $ext = trim(strtoupper(pathinfo($row['ATATNL'], PATHINFO_EXTENSION)));
                    require 'SetRowClass.php';
                    print "\n <tr class=\"$rowClass\"> ";
                    if ($sec_03 == "Y" || $ext == "XML") {
                        print "\n <td class=\"colicon\">";
                    }
                    if ($ext == "XML") {
                        $xmlValid = (string)"<img border=\"0\" src=\"{$homeURL}{$imagePath}smXML.gif\" title=\"Click here to validate the Configuration XML\" alt=\"XML\">";
                        print "\n <a href=\"{$homeURL}{$phpPath}SalesOrderImportValidateXML.php{$scriptVarBase}&amp;xmlFile=" . urlencode(trim($row['ATATNS'])) . "\">$xmlValid</a>";
                    }
                    if ($sec_03 == "Y") {
                        $maintainVar = "{$scriptVarBase}&amp;attachFolderU=" . urlencode(trim($row['ATFOLD'])) . "&amp;attachVarKey=Configuration&amp;attachShortName=" . urlencode(trim($row['ATATNS'])) . "&amp;attachLongName=" . urlencode(trim($row['ATATNL'])) . "&amp;attachDesc=" . urlencode(trim($row['ATDESC'])) . "&amp;bodyFile=" . urlencode($row['ATBODY']) . "&amp;attachPrivate=" . urlencode($row['ATPRIV']) . "&amp;directLink=" . urlencode($row['ATDIRL']) . "&amp;attachUser=" . urlencode(trim($row['ATUSER']));
                        $confirmDesc = Format_Confirm_Desc("Configuration:", "", trim($row['ATDESC']), "", "", "");
                        print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}{$scriptName}{$maintainVar}&amp;tag=DELETE\">$delImage</a>";
                    }
                    if ($sec_03 == "Y" || $ext == "XML") {
                        print "</td>";
                    }
                    print "\n     <td class=\"colalph\">$row[ATDESC]</td>";
                    $longName = $homePath . trim($row['ATATNL']);
                    $fileFound = file_exists($longName);
                    if ($fileFound and $ext == "XML") {
                        print "\n     <td class=\"colalph\"><a href=\"{$longName}\" target=_blank title=\"Click here to view attachment\">$row[ATATNS]</a></td>";
                    } else {
                        print "\n     <td class=\"colalph\">$row[ATATNS]</td>";
                    }
                    print "\n     <td class=\"colalph\">$row[USDESC]</td>";
                    print "\n <td class=\"colalph\">$attDate</td>";
                    print "\n <td class=\"colalph\">$attTime</td>";
                    print "\n </tr>";

                    $startRow++;
                    $rowCount++;
                }
                if ($rowCount == 0) {
                    require 'QuickLinkNoInfoMsg.php';
                }

                print "\n </table> ";
                print "\n </fieldset> ";
            }
        }
        require 'QuickLinkEndLoop.php'; // Quicklink End
    }
}

print $hrTagAttr;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";
?>