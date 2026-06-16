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

// Get EEO-1 Report and Establishment
$stmtSQL = " Select * From PEEORP inner join PEESTB on RPESID=ESESID Where RPRPID in ({$fromRPID})
             Fetch First Row Only";
$sqlResult1 = db2_exec($i5Connect->getConnection(), $stmtSQL);
$eeo1First = db2_fetch_assoc($sqlResult1);

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
$fileName = "UploadFile{$year}{$eeo1First[ESUSID]}.csv";

// if Type 6 Report, only need Employee count
if ($eeo1First[ESSTAT] == 6) {
    $stmtSQL = "Select max(RERPID) as RERPID,count(*) as CNT
                from PEEORE Where RERPID = {$fromRPID} and REINCT='Y'";
} else {
    $stmtSQL = "Select RERPID,REJBCT,REGNDR,REETCT,REPBID,count(*) as CNT,sum(REHRSW) as HOURS
                from PEEORE Where RERPID in ({$fromRPID}) and REINCT='Y'
                Group By RERPID,REJBCT,REGNDR,REETCT,REPBID
                Order By RERPID,REJBCT";
}

$sqlResult2 = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));
if ($sqlResult2) {
    // Open file and Write header
    $fileHandle = fopen($wipPath . $fileName, "w");
    if (!$fileHandle) {
    }
    $record = [
        'USERID',
        'STATUSCODE',
        'UNITNUMBER',
        'UNITNAME',
        'UNITADDRESS',
        'UNITADDRESS2',
        'CITY',
        'STATE',
        'ZIPCODE',
        'COUNTYNAME',
        'FEIN',
        'NAICSCODE',
        'QUESTIONB2C',
        'QUESTIOND2',
        'JOBCATEGORY',
        'RACEETHNICITYGENDER',
        'ANNUALSALARY',
        'TOTALEMPLOYEES',
        'TOTALHOURS'
    ];
    $success = fputcsv($fileHandle, $record);
    if ($success === false) {
    }

    $reportID = 0;
    $startRow = 1;
    while ($row = db2_fetch_assoc($sqlResult2, $startRow)) {
        if ($reportID != $row[RERPID]) {
            $reportID = $row[RERPID];
            // Get EEO-1 Report and Establishment
            $stmtSQL = " Select * From PEEORP inner join PEESTB on RPESID=ESESID Where RPRPID={$row[RERPID]} ";
            $sqlResult3 = db2_exec($i5Connect->getConnection(), $stmtSQL);
            $eeo1Row = db2_fetch_assoc($sqlResult3);

        }
        $record = [];
        $record[] = trim($eeo1Row[ESUSID]);
        $record[] = $eeo1Row[ESSTAT];
        $record[] = $eeo1Row[ESUNIT];
        $record[] = trim($eeo1Row[ESNAME]);
        $record[] = trim($eeo1Row[ESADDR]);
        $record[] = trim($eeo1Row[ESADR2]);
        $record[] = trim($eeo1Row[ESCITY]);
        $record[] = trim($eeo1Row[ESST]);
        $record[] = trim($eeo1Row[ESZIP]);
        $record[] = trim($eeo1Row[ESCNTY]);
        $record[] = $eeo1Row[ESEIN];
        $record[] = $eeo1Row[ESNAICS];
        $record[] = $eeo1Row[ESQB2C];
        $record[] = $eeo1Row[ESQD2];
        if ($eeo1Row[ESSTAT] == 6) {
            $record[] = '99';
            $record[] = 'Z';
            $record[] = '99';
            $record[] = $row[CNT];
            $record[] = '-3';
        } else {
            $jobCat = ($convertEEOJobCategory == 'Y') ? jobCategory($row[REJBCT]) : $row[REJBCT];
            $record[] = intval($jobCat);
            $record[] = ethnicity($row[REGNDR], $row[REETCT]);
            $record[] = $row[REPBID];
            $record[] = $row[CNT];
            $record[] = intval($row[HOURS]);
        }
        $success = fputcsv($fileHandle, $record);
        $startRow++;
    }

    fclose($fileHandle);
}

$confMessage = Format_ConfMsg_Desc("", "Confirm Create of CSV File for ", trim($eeo1Row[RPDESC]), "", "", "", "");
print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=514&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";

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

function ethnicity($gender, $race)
{
    $value = '';
    if ($gender == 'M') {
        switch ($race) {
            case 0:
                $value = 'C';
                break;
            case 1:
                $value = 'D';
                break;
            case 2:
                $value = 'E';
                break;
            case 3:
                $value = 'F';
                break;
            case 4:
                $value = 'G';
                break;
            case 5:
                $value = 'A';
                break;
            case 6:
                $value = 'H';
                break;
            case 7:
                $value = 'H';
                break;
        }
    } elseif ($gender == 'F') {
        switch ($race) {
            case 0:
                $value = 'I';
                break;
            case 1:
                $value = 'J';
                break;
            case 2:
                $value = 'K';
                break;
            case 3:
                $value = 'L';
                break;
            case 4:
                $value = 'M';
                break;
            case 5:
                $value = 'B';
                break;
            case 6:
                $value = 'N';
                break;
            case 7:
                $value = 'N';
                break;
        }
    }
    return $value;
}

?>