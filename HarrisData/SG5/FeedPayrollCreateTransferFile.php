<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('report_memleaks', 1);

$Arg = explode("::", $argv[1]);
$_GET['baseVar'] = $Arg[0];
$hexHandle = $Arg[1];

require_once 'GetURLParm.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';
require_once ($baseExportFile);

// Delimit columns with comma
const DELIMITER = ",";
// Record terminates with CRLF
const RECORD_TERM = "\r\n";

// Make sure the directory for the export files exists
$exportPath = "{$homePath}{$exportDirectory}{$dataBaseID}/";
if (! file_exists("$exportPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$exportPath\")'");
}
$exportFolder = 'PayTransWIP';
$wipPath = "{$exportPath}{$exportFolder}/";
if (! file_exists("$wipPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$wipPath\")'");
}

// Build transfer file name
$fileName = 'PayTransWIP_' . date('YmdHis') . '.csv';

// Retrieve Labor Processing Payroll Interface for AppsInHD data
$stmtSQL = 'Select *';
$stmtSQL .= ' From SIMLBPWK';
$stmtSQL .= " Where PWXHND='{$hexHandle}'";
$stmtSQL .= ' Order by PWCO,PWFAC,PWEMP,PWDATE';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));
if ($sqlResult) {
    // Open file and Write header
    $fileHandle = fopen($wipPath . $fileName, "w");
    if (! $fileHandle) {}
    $record = [
        'EMPLOYEE_ID_X_SECONDARY_KEY_CLOCK_NUMBER',
        'TRANSACTION_DATE',
        'PAY_CODE',
        'END_DATE',
        'HOURS_WORKED',
        'PIECES',
        'UNIT_RATE',
        'SHIFT_REFERENCE_X_ALTERNATE_KEY',
        'DESCRIPTION'
    ];
    $success = fputcsv($fileHandle, $record);
    if ($success === false) {}
    
    $startRow = 1;
    while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
        $record = [];
        // Badge Number
        $record[] = trim($row['PWCLCK']);
        // Transaction Date
        $record[] = $row['PWDATE'];
        // Pay Code
        $record[] = trim($row['PWOPYC']);
        // End Date
        $end = DateTime::createFromFormat('Y-m-d-H.i.s.u', $row['PWEND']);
        $record[] = $end->format('Y-m-d H:i:s');
        // Hours
        $record[] = $row['PWPHRS'];
        
        if ($row['PWQTY'] > '0') {
            // Pieces
            $record[] = $row['PWQTY'];
            // Rate
            $record[] = $row['PWORTE'];
        } else {
            // Pieces
            $record[] = 0;
            // Rate - the hourly unit rate will default in AppsInHD HRIS
            $record[] = '';
        }
        // Schedule
        $record[] = $row['PWSCHD'];
        // Description
        $record[] = $row['PWDESC'];
        
        $success = fputcsv($fileHandle, $record);
        if ($success === false) {}
        
        $startRow ++;
    }
    
    fclose($fileHandle);
}

$stmtSQL = " Delete From SIMLBPWK Where PWXHND='{$hexHandle}'";
$status = db2_exec($i5Connect->getConnection(), $stmtSQL);

?>