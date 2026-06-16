<?php
require_once 'GetURLParm.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$backURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=481";

// Load Affordable Care Act 1094C Cache
$stmtSQL = " Insert Into HRACC4 (C4TXYR,C4BNAM1,C4PYTIN,C4TINRT,C4ADDR1,C4CITY,C4STATE,C4PSTCD,C4CFNAM,C4CLNAM,C4CPHON,
		                         C4ATCNT,C4ATRAN,C4MBCNT,C4OFFM98,C4MC12M,C4EACTA) ";
$stmtSQL .= " Values (2016, 'Carrtestseven', '000000710', 'BUSINESS_TIN', '109 Cypress Cove', 'Wimberley', 'TX', '78676', 'Carla', 'Hayes', '5551552899', 
		              1, 1, 103, 1, 1, 103)";
$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );

// Get ID of Affordable Care Act 1094C Cache
$stmtSQL = " Select max(C4CACHID) as C4CACHID From HRACC4 Where C4PYTIN='000000710' ";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$row = db2_fetch_assoc ( $sqlResult );
$aca1094CCacheId = $row ['C4CACHID'];

// Load Affordable Care Act 1095C Cache
$stmtSQL = " Insert Into HRACC5
			    (C5CAID94,C5EFNAM,C5ELNAM,C5TINRT,C5ESSOC,C5ADDR1,C5CITY,C5STATE,C5PSTCD,C5CVCDA,C5EMSHA,C5SHBRA) ";

$stmtSQL .= " Values({$aca1094CCacheId}, 'Scarlett', 'Camen', 'INDIVIDUAL_TIN', '000000701', '420 Falcon Lane', 'San Juan Capistrano', 'CA', '92693', '1C', 115.00, '2C') ";
$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$backURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=481";
$maintenanceCode = 'A';
$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, 'Test EIN', "", "", "", "", "" );
print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";

?>	
