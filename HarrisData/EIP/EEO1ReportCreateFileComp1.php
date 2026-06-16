<?php
require_once 'GetURLParm.php';
$fromRPID = $_GET ['fromRPID'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';
require_once($baseExportFile);

if (!isset($convertEEOJobCategory) || ($convertEEOJobCategory != 'N' && $convertEEOJobCategory != 'Y')) {
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}DisplayErrorMessage.php{$genericVarBase}&amp;errorMsg=EIP Configuration value for Convert EEO Job Category (convertEEOJobCategory) is not valid.\"> ";
    exit();
}

if (!isset($convertEEOEthnicCategory) || ($convertEEOEthnicCategory != 'N' && $convertEEOEthnicCategory != 'Y')) {
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}DisplayErrorMessage.php{$genericVarBase}&amp;errorMsg=EIP Configuration value for Convert EEO Ethnic Category (convertEEOEthnicCategory) is not valid.\"> ";
    exit();
}

// Get EEO-1 Report and Establishment
$stmtSQL = " Select * From PEEORP inner join PEESTB on RPESID=ESESID Where RPRPID in ({$fromRPID})
             Fetch First Row Only";
$sqlResult1 = db2_exec($i5Connect->getConnection(), $stmtSQL);
$eeo1First = db2_fetch_assoc($sqlResult1);
$payPeriod = substr($eeo1First[RPFRDT], 5, 2) . substr($eeo1First[RPFRDT], 8, 2) . substr($eeo1First[RPFRDT], 0, 4);
$payPeriod .= substr($eeo1First[RPTODT], 5, 2) . substr($eeo1First[RPTODT], 8, 2) . substr($eeo1First[RPTODT], 0, 4);

// Delimit columns with comma
const DELIMITER = ",";
// Record terminates with CRLF
const RECORD_TERM = "\r\n";

// Make sure the directory for the export files exists
$exportPath = "{$homePath}{$exportDirectory}{$dataBaseID}/";
if (!file_exists("$exportPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$exportPath\")'");
}
$exportFolder = 'EEO';
$wipPath = "{$exportPath}{$exportFolder}/";
if (!file_exists("$wipPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$wipPath\")'");
}

// Build transfer file name
$year = substr($eeo1First[RPFRDT], 0, 4);
$usid = trim($eeo1First[ESUSID]);
$fileName = "UploadFile{$year}{$usid}.csv";

// if Type 6 Report, only need Employee count
if ($eeo1First[ESSTAT] == 6) {
    $stmtSQL = "Select max(RERPID) as RERPID,count(*) as CNT
                from PEEORE Where RERPID = {$fromRPID} and REINCT='Y'";
} else {
    $stmtSQL = "Select RERPID,REJBCT,REGNDR,REETCT,count(*) as CNT
                from PEEORE Where RERPID in ({$fromRPID}) and REINCT='Y'
                Group By RERPID,REJBCT,REGNDR,REETCT
                Order By RERPID,REJBCT";
}

$sqlResult2 = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));
if ($sqlResult2) {
    // Open file
    try {
        $fileHandle = fopen($wipPath . $fileName, "w");
    } catch (\Throwable $e) {
        echo $e->getMessage();
    }
    $reportID = 0;
    $startRow = 1;
    $cnt = [];
    $record = [];
    while ($row = db2_fetch_assoc($sqlResult2, $startRow)) {
        if ($reportID != $row[RERPID]) {
            if ($reportID > 0) {
                addRow($cnt);
                $cnt = [];
                $record = [];
            }

            $reportID = $row[RERPID];
            $cnt = [];
            // Get EEO-1 Report and Establishment
            $stmtSQL = " Select * From PEEORP inner join PEESTB on RPESID=ESESID Where RPRPID={$row[RERPID]} ";
            $sqlResult3 = db2_exec($i5Connect->getConnection(), $stmtSQL);
            $eeo1Row = db2_fetch_assoc($sqlResult3);
            if ($eeo1Row[ESSTAT] == 6) {
                $record[] = trim($eeo1Row[ESUSID]);
                $record[] = trim($eeo1Row[ESNAME]);
                $record[] = $eeo1Row[ESUNIT];
                $record[] = trim($eeo1Row[ESADDR]);
                $record[] = trim($eeo1Row[ESCITY]);
                $record[] = trim($eeo1Row[ESST]);
                $record[] = trim($eeo1Row[ESZIP]);
                $record[] = $row[CNT];
                fputcsv($fileHandle, $record);
                $reportID = 0;
                break;
            }
        }
        $jobCat = ($convertEEOJobCategory == 'Y') ? jobCategory($row[REJBCT]) : $row[REJBCT];
        $jobCat = intval($jobCat);
        $ethnicCat = ($convertEEOEthnicCategory == 'Y') ? ethnicCategory($row[REETCT]) : $row[REETCT];
        $ethnicCat = intval($ethnicCat);
        $cnt[$jobCat][$ethnicCat][$row[REGNDR]] = $row[CNT];
        $startRow++;
    }
    if ($reportID > 0) {
        addRow($cnt);
    }

    fclose($fileHandle);
}

$confMessage = Format_ConfMsg_Desc("", "Confirm Create of CSV File for ", trim($eeo1Row[RPDESC]), "", "", "", "");
print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=514&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";

function addRow($cnt = [])
{
    global $fileHandle, $eeo1Row, $payPeriod;

    $record = [];
    $record[] = trim($eeo1Row[ESUSID]);
    $record[] = $eeo1Row[ESSTAT];
    $record[] = trim($eeo1Row[ESUSID]);
    $record[] = trim($eeo1Row[ESNAME]);
    $record[] = trim($eeo1Row[ESADDR]);
    $record[] = trim($eeo1Row[ESADR2]);
    $record[] = trim($eeo1Row[ESCITY]);
    $record[] = trim($eeo1Row[ESST]);
    $record[] = trim($eeo1Row[ESZIP]);
    $record[] = $eeo1Row[ESQB2C];
    $record[] = '';
    $record[] = '';
    $record[] = '';
    $record[] = trim($eeo1Row[ESDBN]);
    $record[] = trim($eeo1Row[ESCNTY]);
    $record[] = $payPeriod;
    $record[] = $eeo1Row[ESNAICS];
    $record[] = trim($eeo1Row[ESCOT]);
    $record[] = trim($eeo1Row[ESCON]);
    $record[] = trim($eeo1Row[ESCOP]);
    $record[] = trim($eeo1Row[ESCOE]);

    $sum = [];
    $tot = [];
    for ($i = 1; $i <= 10; $i++) {
        $tot[1] = (isset($cnt[$i][3]['M'])) ? $cnt[$i][3]['M'] : 0;
        $tot[2] = (isset($cnt[$i][3]['F'])) ? $cnt[$i][3]['F'] : 0;
        $tot[3] = (isset($cnt[$i][1]['M'])) ? $cnt[$i][1]['M'] : 0;
        $tot[4] = (isset($cnt[$i][2]['M'])) ? $cnt[$i][2]['M'] : 0;
        $tot[5] = (isset($cnt[$i][6]['M'])) ? $cnt[$i][6]['M'] : 0;
        $tot[6] = (isset($cnt[$i][4]['M'])) ? $cnt[$i][4]['M'] : 0;
        $tot[7] = (isset($cnt[$i][5]['M'])) ? $cnt[$i][5]['M'] : 0;
        $tot[8] = (isset($cnt[$i][7]['M'])) ? $cnt[$i][7]['M'] : 0;
        $tot[9] = (isset($cnt[$i][1]['F'])) ? $cnt[$i][1]['F'] : 0;
        $tot[10] = (isset($cnt[$i][2]['F'])) ? $cnt[$i][2]['F'] : 0;
        $tot[11] = (isset($cnt[$i][6]['F'])) ? $cnt[$i][6]['F'] : 0;
        $tot[12] = (isset($cnt[$i][4]['F'])) ? $cnt[$i][4]['F'] : 0;
        $tot[13] = (isset($cnt[$i][5]['F'])) ? $cnt[$i][5]['F'] : 0;
        $tot[14] = (isset($cnt[$i][7]['F'])) ? $cnt[$i][7]['F'] : 0;
        $total = 0;
        for ($r = 1; $r <= 14; $r++) {
            $sum[$r] += $tot[$r];
            $record[] = $tot[$r];
            $total += $tot[$r];
        }
        $record[] = $total;
    }
    $total = 0;
    for ($s = 1; $s <= 14; $s++) {
        $record[] = $sum[$s];
        $total += $sum[$s];
    }
    $record[] = $total;
    $record[] = $eeo1Row[ESEIN];

    fputcsv($fileHandle, $record);
}

function jobCategory($cat)
{
    $value = 0;
    if ($cat >= 2 && $cat <= 9) {
        $value = $cat + 1;
    } elseif ($cat == 1.1) {
        $value = 1;
    } elseif ($cat == 1.2) {
        $value = 2;
    }
    return $value;
}

function ethnicCategory($cat)
{
    $value = 0;
    switch ($cat) {
        case 0:
            $value = 1;
            break;
        case 1:
            $value = 2;
            break;
        case 2:
            $value = 6;
            break;
        case 3:
            $value = 4;
            break;
        case 4:
            $value = 5;
            break;
        case 5:
            $value = 3;
            break;
        case 6:
            $value = 7;
            break;
        case 7:
            $value = 7;
            break;
    }
    return $value;
}

?>