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
require_once 'VarBase.php';

$page_title = "Sales Order Import Process";
$scriptName = "SalesOrderImportProcess.php";
$scriptVarBase = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

$keyCol = null;
$shipToCol = ['H1SHTO' => '@@shto', 'name' => '@@stnm', 'address_1' => '@@sta1', 'address_2' => '@@sta2', 'address_3' => '@@sta3', 'city' => '@@stct', 'state' => '@@stst', 'zip_code' => '@@stzp', 'phone_number' => '@@stph'];
$billToCol = ['H1BLTO' => '@@blto', 'name' => '@@btnm', 'address_1' => '@@bta1', 'address_2' => '@@bta2', 'address_3' => '@@bta3', 'city' => '@@btct', 'state' => '@@btst', 'zip_code' => '@@btzp', 'phone_number' => '@@btph'];
$dropShipCol = ['H1DSHP' => '@@dshp', 'name' => '@@dsnm', 'address_1' => '@@dsa1', 'address_2' => '@@dsa2', 'address_3' => '@@dsa3', 'city' => '@@dsct', 'state' => '@@dsst', 'zip_code' => '@@dszp', 'phone_number' => '@@dsph'];
$hdrCol = ['H1ORTY' => '@@orty', 'H1CTRM' => '@@ctrm', 'H1FRT' => '@@frt@', 'H1CHRG' => '@@chrg', 'H1LOC' => '@@loc@', 'H1BDTE' => '@@bdte', 'H1RQDT' => '@@rqdt', 'H1DTE2' => '@@dte2', 'H1DTE3' => '@@dte3', 'H1DTE4' => '@@dte4', 'H1ATTN' => '@@attn', 'H1ORRF' => '@@orrf', 'H1STOR' => '@@stor', 'H1UCA1' => '@@uca1', 'H1UCA2' => '@@uca2', 'H1UCA3' => '@@uca3', 'H1UCA4' => '@@uca4', 'H1UCA5' => '@@uca5', 'H1UDF1' => '@@udf1', 'H1UDF2' => '@@udf2', 'H1UDF3' => '@@udf3', 'H1UDF4' => '@@udf4', 'H1UDF5' => '@@udf5', 'H1UDF6' => '@@udf6', 'H1UDF7' => '@@udf7', 'H1UDF8' => '@@udf8', 'H1UDF9' => '@@udf9', 'H1UDF0' => '@@udf0'];
$dtlCol = ['O1RQDT' => '@@rqdt', 'O1ITEM' => '@@item', 'O1IMDS' => '@@imds', 'O1ORCS' => '@@orcs', 'O1PCLS' => '@@pcls', 'O1WH' => '@@wh@@', 'O1QORD' => '@@qord', 'O1SLPR' => '@@slpr', 'O1TAXC' => '@@taxc', 'O1UDF1' => '@@uda1', 'O1UDF2' => '@@uda2', 'O1UDF3' => '@@uda3', 'O1UDF4' => '@@uda4', 'O1UDF5' => '@@uda5', 'O1UDF6' => '@@uda6', 'O1UDF7' => '@@uda7', 'O1UDF8' => '@@uda8', 'O1UDF9' => '@@uda9', 'O1UDF0' => '@@uda0', 'O1UDN1' => '@@udn1', 'O1UDN2' => '@@udn2', 'O1UDN3' => '@@udn3', 'O1UDN4' => '@@udn4', 'O1UDN5' => '@@udn5'];
$colFmt = [
    'name' => ['fmt' => null, 'len' => 26],
    'address_1' => ['fmt' => null, 'len' => 26],
    'address_2' => ['fmt' => null, 'len' => 26],
    'address_3' => ['fmt' => null, 'len' => 19],
    'city' => ['fmt' => null, 'len' => 26],
    'state' => ['fmt' => null, 'len' => 2],
    'zip_code' => ['fmt' => '[^0-9-]', 'len' => 13],
    'phone_number' => ['fmt' => '[^0-9]', 'len' => 11],
    'H1BLTO' => ['fmt' => '[^0-9]', 'len' => 7],
    'H1SHTO' => ['fmt' => '[^0-9]', 'len' => 7],
    'H1DSHP' => ['fmt' => '[^0-9]', 'len' => 7],
    'H1ORTY' => ['fmt' => null, 'len' => 1],
    'H1CTRM' => ['fmt' => null, 'len' => 2],
    'H1FRT' => ['fmt' => '[^0-9]-.', 'len' => 12],
    'H1CHRG' => ['fmt' => '[^0-9]-.', 'len' => 12],
    'H1LOC' => ['fmt' => '[^0-9]', 'len' => 3],
    'H1BDTE' => ['fmt' => '[^0-9]', 'len' => 6],
    'H1RQDT' => ['fmt' => '[^0-9]', 'len' => 6],
    'H1DTE2' => ['fmt' => '[^0-9]', 'len' => 6],
    'H1DTE3' => ['fmt' => '[^0-9]', 'len' => 6],
    'H1DTE4' => ['fmt' => '[^0-9]', 'len' => 6],
    'H1ATTN' => ['fmt' => null, 'len' => 22],
    'H1ORRF' => ['fmt' => null, 'len' => 22],
    'H1STOR' => ['fmt' => null, 'len' => 20],
    'H1UCA1' => ['fmt' => null, 'len' => 2],
    'H1UCA2' => ['fmt' => null, 'len' => 2],
    'H1UCA3' => ['fmt' => null, 'len' => 2],
    'H1UCA4' => ['fmt' => null, 'len' => 2],
    'H1UCA5' => ['fmt' => null, 'len' => 2],
    'H1UDF1' => ['fmt' => null, 'len' => 15],
    'H1UDF1' => ['fmt' => null, 'len' => 15],
    'H1UDF2' => ['fmt' => null, 'len' => 15],
    'H1UDF3' => ['fmt' => null, 'len' => 15],
    'H1UDF4' => ['fmt' => null, 'len' => 15],
    'H1UDF5' => ['fmt' => null, 'len' => 15],
    'H1UDF6' => ['fmt' => null, 'len' => 15],
    'H1UDF7' => ['fmt' => null, 'len' => 15],
    'H1UDF8' => ['fmt' => null, 'len' => 15],
    'H1UDF9' => ['fmt' => null, 'len' => 15],
    'H1UDF0' => ['fmt' => null, 'len' => 15],
    'O1RQDT' => ['fmt' => '[^0-9]', 'len' => 6],
    'O1ITEM' => ['fmt' => null, 'len' => 15],
    'O1IMDS' => ['fmt' => '[^a-zA-Z0-9 \-_]', 'len' => 30],
    'O1ORCS' => ['fmt' => null, 'len' => 30],
    'O1PCLS' => ['fmt' => null, 'len' => 4],
    'O1WH' => ['fmt' => '[^0-9]', 'len' => 3],
    'O1QORD' => ['fmt' => '[^0-9]-.', 'len' => 15],
    'O1SLPR' => ['fmt' => '[^0-9]-.', 'len' => 15],
    'O1TAXC' => ['fmt' => null, 'len' => 1],
    'O1UDF1' => ['fmt' => null, 'len' => 15],
    'O1UDF2' => ['fmt' => null, 'len' => 15],
    'O1UDF3' => ['fmt' => null, 'len' => 15],
    'O1UDF4' => ['fmt' => null, 'len' => 15],
    'O1UDF5' => ['fmt' => null, 'len' => 15],
    'O1UDF6' => ['fmt' => null, 'len' => 30],
    'O1UDF7' => ['fmt' => null, 'len' => 30],
    'O1UDF8' => ['fmt' => null, 'len' => 30],
    'O1UDF9' => ['fmt' => null, 'len' => 30],
    'O1UDF0' => ['fmt' => null, 'len' => 30],
    'O1UDN1' => ['fmt' => '[^0-9]-.', 'len' => 15],
    'O1UDN2' => ['fmt' => '[^0-9]-.', 'len' => 15],
    'O1UDN3' => ['fmt' => '[^0-9]-.', 'len' => 15],
    'O1UDN4' => ['fmt' => '[^0-9]-.', 'len' => 15],
    'O1UDN5' => ['fmt' => '[^0-9]-.', 'len' => 15],
];

$importTotalColumn = null;
$importTotal = null;
$emailColumn = null;
$emailColumnKey = null;
$billTo = [];
$shipTo = [];
$dropShip = [];
$headers = [];
$hdrDft = [];
$hdrFmt = [];
$detail = [];
$dtlDft = [];
$dtlFmt = [];
$edtVar = '';

$xml = new DOMDocument();
$xmlPath = $homePath . $uploadDirectory . $dataBaseID . '/Import/Configuration/' . $xmlFile;
$xml->load("{$xmlPath}");
$xml = simplexml_load_file($xmlPath) or die("Failed to load");

$acceptPrices = ($xml->processing->accept_prices == 'true') ? 'Y' : null;
$logTrans = ($xml->processing->log_transactions == 'true') ? true : null;
$upperAlpha = ($xml->processing->uppercase_alpha == 'true') ? true : null;

$longPath = $uploadDirectory . $dataBaseID . '/Import/ImportsToProcess/' . $processFile;
$procFile = $homePath . $uploadDirectory . $dataBaseID . '/Import/ImportsToProcess/' . $processFile;
$handle = fopen("$procFile", "r");
$hdrRow = fgetcsv($handle);
Get_Column_Names();

if ($logTrans) {
    $logFile = Create_Import_Log();
}
require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
print "\n <table $contentTable> ";
print "\n <tr><td><h1>Processing Sales Order Import</h1></td> ";
print "\n </tr></table>";
print "\n <table $contentTable>";
Format_Header("File", $attachDesc, $processFile);
print "\n <tr><td><img src=\"/images/ajax-loader.gif\" alt=\"Loading... \" class=\"loading\"></td></tr>";
print "\n </table>";
// Send output to browser immediately
flush();
// Sleep one second so we can see the delay
sleep(1);

$keyValue = null;
$saveValue = null;
$hdrData = null;
while (($data = fgetcsv($handle)) !== FALSE) {
    $keyValue = $data[$keyCol];

    if ($saveValue != $keyValue) {
        if (!is_null($saveValue)) {
            Complete_Order();
        }
        $hdrData = Load_Header_Row($data);
        $orderControlNumber = Decat_Field("@@octl", $hdrData);
        $shipToNumber = Decat_Field("@@shto", $hdrData);
        $dftWhs = RetValue("CMCUST={$shipToNumber}", "HDCUST", "coalesce(CMWH#,0)");
        $saveValue = $keyValue;
    }

    $dtlData = Load_Detail_Row($data);
}

if (!is_null($saveValue)) {
    Complete_Order();
}

fclose($handle);
if ($logTrans) {
    fclose($logFile);
}
ob_end_clean();   //    the buffer and never prints or returns anything.

$maintainVar = "{$genericVarBase}&amp;attachFolderU=IMPORT&amp;attachVarKey=ImportsToProcess&amp;attachLongName=" . urlencode(trim($longPath)) . "&amp;attachShortName=" . urlencode(trim($processFile));
print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}SalesOrderImport.php{$maintainVar}&amp;tag=DELETE&amp;fromImport=Y\"> ";

function Complete_Order()
{
    global $hdrData, $logTrans;

    $mncd = 'T';  // Update Order Totals
    $edtVar = Process_Import($mncd, $hdrData);
    if ($logTrans) {
        Add_Import_Log();
    }
    $mncd = 'P';  // Add Order to Data Queue
    $edtVar = Process_Import($mncd, $edtVar);
}

function Load_Detail_Row($data)
{
    global $detail, $acceptPrices, $dtlCol, $dtlDft, $dtlFmt, $colFmt, $orderControlNumber, $dftWhs, $xml;

    $edtVar = "";
    $edtVar = Concat_EdtVar("@@octl", $orderControlNumber, $edtVar);
    $edtVar = Concat_EdtVar("@@dwhs", $dftWhs, $edtVar);
    $custIem = ($xml->processing->use_customer_item == 'true') ? 'Y' : 'N';
    $edtVar = Concat_EdtVar("@@citm", $custIem, $edtVar);
    $dftItem = $xml->defaults->non_stock_item;
    $edtVar = Concat_EdtVar("@@ditm", $dftItem, $edtVar);
    $errHold = $xml->defaults->hold_code->error;
    $edtVar = Concat_EdtVar("@@herr", $errHold, $edtVar);

    foreach ($dtlCol as $key => $colName) {
        // Don't pass Selling Price if Accept Prices is false
        if (is_null($acceptPrices) && $key == 'O1SLPR') {
            continue;
        }
        if (array_key_exists($key, $detail) || array_key_exists($key, $dtlDft)) {
            $value = $data[$detail[$key]];
            if (trim($value) == '' && array_key_exists($key, $dtlDft)) {
                $value = $dtlDft[$key];
            }
            if (array_key_exists($key, $colFmt)) {
                $value = Format_Import_Value($key, $value);
            }
            if (array_key_exists($key, $dtlFmt) && trim($dtlFmt[$key]) == 'timestamp') {
                $conv = strtotime($value);
                $value = date('mdy', $conv);
            }
            $edtVar = Concat_EdtVar($colName, $value, $edtVar);
        }
    }

    $edtVar .= "}{";
    $mncd = 'D';

    $edtVar = Process_Import($mncd,$edtVar);
    return;
}

function Load_Header_Row($data)
{
    global $billTo, $billToCol, $shipTo, $shipToCol, $dropShip, $dropShipCol, $headers, $hdrCol, $hdrDft, $hdrFmt, $colFmt, $dataBaseID, $xml, $importTotalColumn, $emailColumnKey;

    $edtVar = "";

    foreach ($billTo as $key => &$val) {
        $colName = $billToCol[$key];
        $value = $data[$val];
        if (array_key_exists($key, $colFmt)) {
            $value = Format_Import_Value($key, $value);
        }
        $edtVar = Concat_EdtVar($colName, $value, $edtVar);
    }

    foreach ($shipTo as $key => &$val) {
        $colName = $shipToCol[$key];
        $value = $data[$val];
        if (array_key_exists($key, $colFmt)) {
            $value = Format_Import_Value($key, $value);
        }
        $edtVar = Concat_EdtVar($colName, $value, $edtVar);
    }

    foreach ($dropShip as $key => &$val) {
        $colName = $dropShipCol[$key];
        $value = $data[$val];
        if (array_key_exists($key, $colFmt)) {
            $value = Format_Import_Value($key, $value);
        }
        $edtVar = Concat_EdtVar($colName, $value, $edtVar);
    }

    $copyCust = ($xml->processing->copy_default_customer == 'true') ? 'Y' : 'N';
    $edtVar = Concat_EdtVar("@@cpyc", $copyCust, $edtVar);
    $match = ($xml->processing->address_match == 'true') ? 'Y' : 'N';
    $edtVar = Concat_EdtVar("@@madr", $match, $edtVar);
    $match = ($xml->processing->phone_match == 'true') ? 'Y' : 'N';
    $edtVar = Concat_EdtVar("@@mphn", $match, $edtVar);
    $dftCust = $xml->defaults->customer;
    $edtVar = Concat_EdtVar("@@dcus", $dftCust, $edtVar);
    $edtVar = Concat_EdtVar("@@dbid", $dataBaseID, $edtVar);
    $key = 'H1ORTY';
    if (array_key_exists($key, $headers) && trim($data[$headers[$key]] != '')) {
        $orty = $data[$headers[$key]];
    } else {
        $orty = $xml->defaults->order_type;
    }
    $edtVar = Concat_EdtVar("@@dtyp", $orty, $edtVar);
    $edtVar .= "}{";
    $mncd = 'S';
    $edtVar = Process_Import($mncd, $edtVar);

    foreach ($hdrCol as $key => $colName) {
        if (array_key_exists($key, $headers) || array_key_exists($key, $hdrDft)) {
            $value = $data[$headers[$key]];
            if (trim($value) == '' && array_key_exists($key, $hdrDft)) {
                $value = $hdrDft[$key];
            }
            if (array_key_exists($key, $colFmt)) {
                $value = Format_Import_Value($key, $value);
            }
            if (array_key_exists($key, $hdrFmt) && trim($hdrFmt[$key]) == 'timestamp') {
                $conv = strtotime($value);
                $value = date('mdy', $conv);
            }
            $edtVar = Concat_EdtVar($colName, $value, $edtVar);
        }
    }

    $mncd = 'H';
    $dateOffset = $xml->defaults->required_date_offset;
    if (trim($dateOffset != '')) {
        $reqDate = date('mdy', strtotime($dateOffset));
        $edtVar = Concat_EdtVar("@@clrd", $reqDate, $edtVar);
    }

    $orderHold = $xml->defaults->hold_code->order;
    if (trim($orderHold) != '') {
        $edtVar = Concat_EdtVar("@@hlcd", $orderHold, $edtVar);
    }
    $errHold = $xml->defaults->hold_code->error;
    $edtVar = Concat_EdtVar("@@herr", $errHold, $edtVar);
    if (!is_null($importTotalColumn)) {
        $edtVar = Concat_EdtVar("@@itot", $data[$importTotalColumn], $edtVar);
    }

    if (!is_null($emailColumnKey) && array_key_exists($emailColumnKey, $data)) {
        $doc = $xml->email->documents;
        $updFax = ($xml->email->update_customer == 'true') ? 'Y' : 'N';
        $edtVar = Concat_EdtVar("@@emal", trim($data[$emailColumnKey]), $edtVar);
        $edtVar = Concat_EdtVar("@@eupd", $updFax, $edtVar);
        if ($doc->acknowledgment == 'true') {
            $edtVar = Concat_EdtVar("@@eack", 'Y', $edtVar);
        }
        if ($doc->invoice == 'true') {
            $edtVar = Concat_EdtVar("@@einv", 'Y', $edtVar);
        }
        if ($doc->pick_ticket == 'true') {
            $edtVar = Concat_EdtVar("@@epic", 'Y', $edtVar);
        }
        if ($doc->packing_list == 'true') {
            $edtVar = Concat_EdtVar("@@epkl", 'Y', $edtVar);
        }
    }
    $edtVar .= "}{";
    $edtVar = Process_Import($mncd, $edtVar);

    return $edtVar;
}

function Process_Import($mncd, $edtVar)
{
    global $userProfile, $profileHandle;
    $errFound = '';
    $errVar = '';
    $wrnVar = '';
    $wrnVar = Concat_EdtVar("@@xhnd", $profileHandle, $wrnVar);
    $wrnVar .= "}{";
    $returnValue = Maintain_Edit("HOESOI_W", $userProfile, $mncd, $errFound, $edtVar, $errVar, $wrnVar);
    return $returnValue['edtVar'];
}

function Format_Import_Value($key, $value)
{
    global $colFmt, $upperAlpha;
    if (!is_null($colFmt[$key]['fmt'])) {
        $pattern = '/' . $colFmt[$key]['fmt'] . '/';
        $value = preg_replace($pattern, '', $value);
    }
    if ($upperAlpha || $key == 'O1IMDS') {
        $value = strtoupper($value);
    }
    return substr($value, 0, $colFmt[$key]['len']);
}

function Get_Column_Names()
{
    global $homeURL, $phpPath, $genericVarBase, $portalHome, $xmlFile, $processFile, $keyCol, $hdrRow, $xml, $shipToCol, $dropShipCol,
           $billToCol, $hdrCol, $dtlCol, $billTo, $shipTo, $dropShip, $headers, $hdrDft, $detail, $dtlDft, $hdrFmt, $dtlFmt,
           $importTotalColumn, $emailColumn, $emailColumnKey;

    $importTotalColumn = (trim($xml->processing->import_total_column) != '') ? $xml->processing->import_total_column : null;
    $emailColumn = (trim($xml->email->import_column) != '') ? $xml->email->import_column : null;
    if (!is_null($emailColumn)) {
        $doc = $xml->email->documents;
        if (trim($doc->acknowledgment) == 'false' && trim($doc->invoice) == 'false' && trim($doc->pick_ticket) == 'false' && trim($doc->packing_list) == 'false') {
            $emailColumn = null;
        }
    }

    $keyColumn = $xml->processing->key_column;
    foreach ($hdrRow as $key => &$value) {
        if (strtoupper(trim($value)) == strtoupper($keyColumn)) {
            $keyCol = $key;
        } elseif (strtoupper(trim($value)) == strtoupper($importTotalColumn)) {
            $importTotalColumn = $key;
        } elseif (strtoupper(trim($value)) == strtoupper($emailColumn)) {
            $emailColumnKey = $key;
        }
    }

    if (is_null($keyCol)) {
        echo "Key Column ({$keyColumn}) defined in {$xmlFile} was not found in the Import File ({$processFile}) &nbsp; <a href=\"{$homeURL}{$phpPath}SalesOrderImport.php{$genericVarBase}\" title=\"Back Home\">{$portalHome}</a><br>";
        exit();
    }

    $stCol = $xml->address_columns->ship_to;
    foreach ($shipToCol as $col => $val) {
        $colName = strtoupper(trim($stCol->$col));
        if ($colName != '') {
            foreach ($hdrRow as $key => &$value) {
                if (strtoupper(trim($value)) == $colName) {
                    $shipTo [$col] = $key;
                }
            }
        }
    }

    $btCol = $xml->address_columns->bill_to;
    foreach ($billToCol as $col => $val) {
        $colName = strtoupper(trim($btCol->$col));
        if ($colName != '') {
            foreach ($hdrRow as $key => &$value) {
                if (strtoupper(trim($value)) == $colName) {
                    $billTo [$col] = $key;
                }
            }
        }
    }

    $dsCol = $xml->address_columns->drop_ship;
    foreach ($dropShipCol as $col => $val) {
        $colName = strtoupper(trim($dsCol->$col));
        if ($colName != '') {
            foreach ($hdrRow as $key => &$value) {
                if (strtoupper(trim($value)) == $colName) {
                    $dropShip [$col] = $key;
                }
            }
        }
    }

    $hdr = $xml->header_columns;
    foreach ($hdrCol as $col => $val) {
        $colName = strtoupper(trim($hdr->$col->import_column));
        if ($colName != '') {
            foreach ($hdrRow as $key => &$value) {
                if (strtoupper(trim($value)) == $colName) {
                    $headers [$col] = $key;
                }
            }
        }
        if (trim($hdr->$col->default_value) != '') {
            $hdrDft [$col] = (string)$hdr->$col->default_value;
        } elseif (trim($hdr->$col->format) != '') {
            $hdrFmt [$col] = (string)$hdr->$col->format;
        }
    }

    $dtl = $xml->detail_columns;
    foreach ($dtlCol as $col => $val) {
        $colName = strtoupper(trim($dtl->$col->import_column));
        if ($colName != '') {
            foreach ($hdrRow as $key => &$value) {
                if (strtoupper(trim($value)) == $colName) {
                    $detail [$col] = $key;
                }
            }
        }
        if (trim($dtl->$col->default_value) != '') {
            $dtlDft [$col] = (string)$dtl->$col->default_value;
        } elseif (trim($dtl->$col->format) != '') {
            $dtlFmt [$col] = (string)$dtl->$col->format;
        }
    }
}

function Create_Import_Log()
{
    global $i5Connect, $homePath, $processFile, $userProfile, $uploadDirectory, $dataBaseID;

    $importPath = "{$homePath}{$uploadDirectory}{$dataBaseID}/Import/";
    if (! file_exists("$importPath")) {
        exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$importPath\")'");
    }
    $importLogPath = "{$importPath}ImportLog/";
    if (! file_exists("$importLogPath")) {
        exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$importLogPath\")'");
    }

    $logFile = "{$uploadDirectory}{$dataBaseID}/Import/ImportLog/{$processFile}";
    $logPath = "{$homePath}{$logFile}";
    $file = fopen($logPath, 'w');

    // add BOM to fix UTF-8 in Excel
    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    ob_end_clean();
    $csvHdr = [];
    $csvHdr[] = "Reference";
    $csvHdr[] = "Order Number";
    $csvHdr[] = "Customer";
    $csvHdr[] = "Name";
    $csvHdr[] = "Hold Code";
    $csvHdr[] = "Terms";
    $csvHdr[] = "Sub Total";
    $csvHdr[] = "Tax";
    $csvHdr[] = "Freight";
    $csvHdr[] = "Special Charge";
    $csvHdr[] = "Order Total";
    $csvHdr[] = "Line Count";
    $csvHdr[] = "Drop Ship";
    $csvHdr[] = "Name";
    fputcsv($file, $csvHdr);

    $desc = RetValue("ATFOLD='IMPORT' and ATVKEY='ImportsToProcess' and ATATNS='{$processFile}'", "SYD2WA", "coalesce(ATDESC,'')");
    $logCnt = RetValue("ATFOLD='IMPORT' and ATVKEY='ImportLog' and ATATNS='{$processFile}'", "SYD2WA", "char(count(*))");
    if ($logCnt > "0") {
        $stmtSQL = "Update SYD2WA Set ATDESC='{$desc}',ATUSER='{$userProfile}',ATTSTP=CURRENT_TIMESTAMP
			        Where ATFOLD='IMPORT' and ATVKEY='ImportLog' and ATATNS='{$processFile}'";
    } else {
        $stmtSQL = "Insert Into SYD2WA
			    (ATFOLD,ATVKEY,ATDESC,ATATNS,ATATNL,ATUSER) 
			    Values ('IMPORT','ImportLog','{$desc}','{$processFile}','{$logFile}','{$userProfile}')";
    }
    db2_exec($i5Connect->getConnection(), $stmtSQL);
    return $file;
}

function Add_Import_Log()
{
    global $i5Connect, $logFile, $orderControlNumber;

    require 'stmtSQLClear.php';
    $stmtSQL = "Select * ";
    $fileSQL = "OEHDWK";
    $selectSQL = "H1OCTL = {$orderControlNumber} ";
    require 'stmtSQLSelect.php';
    require 'stmtSQLEnd.php';
    require 'stmtSQLTotalRows.php';
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
    $row = db2_fetch_assoc($sqlResult);
    $name = RetValue("CMCUST={$row[H1SHTO]}", "HDCUST", "coalesce(CMCNA1,'')");
    $dropShipName = RetValue("DSVCF='C' and DSVNCS={$row[H1SHTO]} and DSNMBR={$row[H1DSHP]}", "HDDSHP", "coalesce(DSNAME,'')");
    $lineCnt = RetValue("O1OCTL={$orderControlNumber}", "OEDTWK", "char(count(*))");

    $csvDtl = [];
    $csvDtl[] = trim($row[H1ORRF]);
    $csvDtl[] = $row['H1ORD#'];
    $csvDtl[] = trim($row[H1SHTO]);
    $csvDtl[] = trim($name);
    $csvDtl[] = trim($row[H1HOLD]);
    $csvDtl[] = trim($row[H1CTRM]);
    $tax = $row[H1STTA] + $row[H1CNTA] + $row[H1LC1A];
    $subTot = $row[H1OTOT] - $row[H1FRT] - $row[H1CHRG] - $tax;
    $csvDtl[] = $subTot;
    $csvDtl[] = $tax;
    $csvDtl[] = $row[H1FRT];
    $csvDtl[] = $row[H1CHRG];
    $csvDtl[] = $row[H1OTOT];
    $csvDtl[] = $lineCnt;
    $csvDtl[] = trim($row[H1DSHP]);
    $csvDtl[] = trim($dropShipName);
    fputcsv($logFile, $csvDtl);
}

function Concat_EdtVar ($fieldName, $fieldValue, $edtVar) {
    if ($edtVar == "") {$edtVar="{$fieldName}{$fieldValue}";}
    else               {$edtVar.="}{{$fieldName}{$fieldValue}";}
    return $edtVar;
}
?>
