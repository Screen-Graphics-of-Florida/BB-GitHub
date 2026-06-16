<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$xmlFile = $_GET['xmlFile'];
$processFile = (isset($_GET['processFile'])) ? $_GET['processFile'] : null;
$attachDesc = (isset($_GET['attachDesc'])) ? $_GET['attachDesc'] : '';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'QuickLink.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
$dftTypeInvalid = ['C', 'I', 'P', 'R'];

$scriptName = "SalesOrderImportValidateXML.php";
$scriptVarBase = "{$genericVarBase}&amp;xmlFile=" . urlencode($xmlFile);
$dspMaxRows =$prtMaxRowsDft;

if ($xmlFile == "SELECT") {
    $attachDesc = $_GET['attachDesc'];
    $filename = $homePath . $_GET['attachLongName'];
    $handle = fopen($filename, "r");

    require_once ($docType);
    print "\n <html> \n	<head>";
    require_once ($headInclude);
    require_once ($genericHead);
    print "\n </head>";
    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
    require_once ($searchBanner);
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    print "\n <td class=\"content\">";
    print "\n <table $contentTable> ";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>Sales Order Import</h1></td> ";
    print "\n     <td class=\"toolbar\">";
    print "<a href=\"{$homeURL}{$phpPath}SalesOrderImport.php{$genericVarBase}\" title=\"Back Home\">{$portalHome}</a>";
    print "</td></tr></table>";
    print "\n <table $contentTable>";
    Format_Header("File", $attachDesc, $processFile);
    print "\n </table> ";
    print $searchhrTagAttr;

    require 'stmtSQLClear.php';
    $stmtSQL = " Select SYD2WA.*, Coalesce(USDESC,' ') as USDESC ";
    $fileSQL = "  SYD2WA ";
    $fileSQL .= " left join SYUSER on ATUSER=USUSER ";
    $selectSQL = " ATFOLD<>' ' and ATFOLD='IMPORT' and ATVKEY='Configuration' and trim(upper(ATATNS)) like '%.XML'";
    require 'stmtSQLSelect.php';
    $stmtSQL .= " Order By ATTSTP desc,ATDESCU";
    require 'stmtSQLEnd.php';
    require 'stmtSQLTotalRows.php';
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

    print "\n <table $contentTable>";
    $rowCount = 0;
    $startRow = 1;
    while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
        if ($startRow == 1) {
            print "\n <tr><td>Select configuration file:</td></tr> ";
            print "\n <tr> ";
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
        print "\n     <td class=\"colalph\">$row[ATDESC]</td>";
        $longName = $homePath . trim($row['ATATNL']);
        $fileFound = file_exists($longName);
        if ($fileFound and $ext == "XML") {
            $maintainVar = "{$genericVarBase}&amp;xmlFile=" . urlencode(trim($row['ATATNS'])) . "&amp;processFile=" . $processFile . "&amp;attachDesc=" . $attachDesc;
            print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$maintainVar}\" title=\"Click here to select config file to use\">$row[ATATNS]</a></td>";
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
    print "</table>";

    print "$searchhrTagAttr";
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    require_once ($searchTrailer);
    print "\n </body> \n </html>";
    exit();
}

libxml_use_internal_errors(true);
$xml = new DOMDocument();
$xmlPath = $homePath . $uploadDirectory . $dataBaseID . '/Import/Configuration/' . $xmlFile;
$xml->load("{$xmlPath}");

if (!$xml->schemaValidate('SalesOrderImportConfig.xsd')) {
    $processFile = null;
    print "Sales Order Import configuration file ({$xmlFile}) is <b>NOT</b> valid &nbsp; <a href=\"{$homeURL}{$phpPath}SalesOrderImport.php{$genericVarBase}\" title=\"Back Home\">{$portalHome}</a><br>";
    print "$searchhrTagAttr";
    libxml_display_errors();
    libxml_clear_errors();
    print "$searchhrTagAttr";
    exit();
} else {
    $xml = simplexml_load_file($xmlPath) or die("Failed to load");
    $dft = $xml->defaults;

    $errors = null;
    $dftType = $dft->order_type;
    $fieldDesc = RetValue("OTAPID='OE' and OTOTCD='{$dftType}'", "HDOTYP", "coalesce(OTDESC,'')");
    if ($fieldDesc == '') {
        $errors = true;
        echo 'default->order_type value of ' . $dftType . ' is not valid <br>';
    } elseif (in_array($dftType, $dftTypeInvalid)) {
        $errors = true;
        echo 'default->order_type value of ' . $dftType . ' is not valid.  Cannot be C, I, P or R <br>';
    }
    $hold = trim($dft->hold_code->order);
    if ($hold != '') {
        $fieldDesc = RetValue("HCTYPE='O' and HCHLCD='{$dft->hold_code->order}'", "HDHLCD", "coalesce(HCDESC,'')");
        if ($fieldDesc == '') {
            $errors = true;
            echo 'default->hold_code->order value of ' . $dft->hold_code->order . ' is not valid <br>';
        }
    }

    $fieldDesc = RetValue("HCTYPE='O' and HCHLCD='{$dft->hold_code->error}'", "HDHLCD", "coalesce(HCDESC,'')");
    if ($fieldDesc == '') {
        $errors = true;
        echo 'default->hold_code->error value of ' . $dft->hold_code->error . ' is not valid <br><br>';
    }
    $reqOff = $dft->required_date_offset;

    $dftCust = $dft->customer;
    $fieldDesc = RetValue("CMCUST={$dftCust}", "HDCUST", "coalesce(CMCNA1,'')");
    if ($fieldDesc == '') {
        $errors = true;
        echo 'default->customer value of ' . $dftCust . ' not found in the Customer table <br><br>';
    }

    $nsItem = $dft->non_stock_item;
    $fieldDesc = RetValue("IMITEM='{$nsItem}'", "HDIMST", "coalesce(IMIMDS,'')");
    if ($fieldDesc == '') {
        $errors = true;
        echo 'default->non_stock_item value of ' . $nsItem . ' not found in the Item table <br><br>';
    }

    if ($errors) {
        echo $xmlFile . '<br>';
        echo 'All erors listed above must be corrected before the Sales Order Import can be processed';
        exit();
    }
}

if (!is_null($processFile)) {
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}SalesOrderImportProcess.php{$scriptVarBase}&amp;processFile=" . urlencode(trim($processFile)) . "&amp;attachDesc=" . urlencode(trim($attachDesc)) . "&amp;tag=PROCESS&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
} else {
    print "Sales Order Import configuration file ({$xmlFile}) is valid &nbsp; <a href=\"{$homeURL}{$phpPath}SalesOrderImport.php{$genericVarBase}\" title=\"Back Home\">{$portalHome}</a><br>";
}

function libxml_display_error($error)
{
    $return = "<br/>\n";
    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "<b>Warning $error->code</b>: ";
            break;
        case LIBXML_ERR_ERROR:
            $return .= "<b>Error $error->code</b>: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "<b>Fatal Error $error->code</b>: ";
            break;
    }
    $return .= trim($error->message);
    //if ($error->file) {
    //    $return .= " in <b>$error->file</b>";
    //}
    $return .= " on line <b>$error->line</b>\n";

    return $return;
}

function libxml_display_errors()
{
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
        print libxml_display_error($error);
    }
    libxml_clear_errors();
}

?>