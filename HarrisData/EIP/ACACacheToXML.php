<?php
ini_set ( 'display_errors', 1 );
ini_set ( 'log_errors', 1 );
ini_set ( 'report_memleaks', 1 );

$Arg = explode ( "::", $argv [1] );
$_GET ['baseVar'] = $Arg [0];
$acaFileCd = trim ( $Arg [1] );
$transCtrlCd = trim ( $Arg [2] );
$fromACA1094CID = trim ( $Arg [3] );
$taxYear = trim ( $Arg [4] );
$corr = trim ( $Arg [5] );

require_once 'GetURLParm.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

// Set Global Variables
$attachFolder = 'ACA';

$exportPath = ( string ) "Export/";
// Make sure the working directory for the xml files exists
$attachPath = "{$homePath}{$exportPath}{$dataBaseID}/";
if (! file_exists ( "$attachPath" )) {
	exec ( "/QOpenSys/usr/bin/system 'MKDIR DIR(\"$attachPath\")'" );
}
$acaPath = "{$attachPath}{$attachFolder}/";
if (! file_exists ( "$acaPath" )) {
	exec ( "/QOpenSys/usr/bin/system 'MKDIR DIR(\"$acaPath\")'" );
}

// Retrieve source data array
require_once 'ACACacheRetrieve.php';
// At least one ACA 1094C Cache row must be selected to continue
if (! isset ( $sourceData ['TaxReportEmployer'] [0] )) {
	exit ();
}

// Set the report data
$reportData = array ();
$reportData ['TAX_YEAR'] = $sourceData ['TaxReportEmployer'] [0] ['TAX_YEAR'];
$reportData ['ACA_TRANSMISSION_TYPE'] = $corr;
if (! empty ( $fromACA1094CID )) {
	if ($sourceData ['TaxReportEmployer'] [0] ['CORRECTED'] == '4' || $sourceData ['TaxReportEmployer'] [0] ['CORRECTED'] == '5') {
		$reportData ['ACA_TRANSMISSION_TYPE'] = 'C';
	} else {
		$reportData ['ACA_TRANSMISSION_TYPE'] = $sourceData ['TaxReportEmployer'] [0] ['CORRECTED'];
	}
}
$reportData ['ORIGINAL_RECEIPT_ID'] = '';
if ($reportData ['ACA_TRANSMISSION_TYPE'] == 'R' && empty ( $fromACA1094CID )) {
	$pieces = explode ( '|', $sourceData ['TaxReportEmployer'] [0] ['CORRECTED_SUBMISSION_ID'] );
	$reportData ['ORIGINAL_RECEIPT_ID'] = $pieces [0];
	foreach ( $sourceData ['TaxReportEmployer'] as $key => $value ) {
		$sourceData ['TaxReportEmployer'] [$key] ['CORRECTED_SUBMISSION_ID'] = '';
	}
}
$reportData ['testfilecode'] = 'P';
if (isset ( $acaFileCd )) {
	$reportData ['testfilecode'] = $acaFileCd;
}

// Create instance of writer
require_once 'ACACacheToXMLWriter.php';
try {
	$writer = new ACACacheToXMLWriter ( $reportData, $acaPath );
} catch ( Exception $e ) {
	exit ();
}

// Create instance of driver
require_once 'ACACacheToXMLDriver.php';
$driver = new ACACacheToXMLDriver ( $i5Connect );

// Inject the writer object into the driver
$driver->setReport ( $writer );

// Inject the source data array into the driver
$driver->setSourceData ( $sourceData );

// Create the xml files
try {
	$driver->createFile ();
} catch ( Exception $e ) {
	exit ();
}

?>