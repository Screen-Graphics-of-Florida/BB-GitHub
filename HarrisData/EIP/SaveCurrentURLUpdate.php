<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$currentURL = $_GET['currentURL'];

require_once 'GenericDirectCallVariables.php';

// Set Schema List
$returnValue=setLibraryList($profileHandle, $dataBaseID, $eID);
$profileHandle=$returnValue['profileHandle'];
$activeRole   =$returnValue['activeRole'];
$eID          =$returnValue['eID'];

require 'stmtSQLClear.php';
$stmtSQL = " Delete From SYEERR Where ERXHND='$profileHandle' ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

require 'stmtSQLClear.php';
$stmtSQL .= " Insert Into SYEERR (ERXHND,ERTYPE,EREERR,ERTSTP) ";
$stmtSQL .= " Values('$profileHandle','U','$currentURL',Current_Timestamp) ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
?>