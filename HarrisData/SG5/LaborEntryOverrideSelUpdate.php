<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$mfgOrder		= $_GET['mfgOrder'];
$seqNumber		= $_GET['seqNumber'];
$selFlag		= $_GET['selFlag'];
$responseInfo   = "";

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';

if ($selFlag == 'N') {$selFlag = ' ';}

$stmtSQL = " Update SILOPW Set WOOSEL='$selFlag' ";
$stmtSQL .= " Where WOXHND='$profileHandle' and WOMORD='$mfgOrder' and WOSEQN=$seqNumber ";
 
// $debugFile = '/tmp/response60125.txt';
// file_put_contents ( $debugFile, ' ; stmtSQL=' . $stmtSQL, FILE_APPEND );

$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );

if (! $status) {
}
  
  ?>