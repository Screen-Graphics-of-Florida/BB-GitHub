<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintCd = $_GET['maintCd'];
$transDate = $_GET['transDate'];
$emid = $_GET['emid'];
$shft = $_GET['shft'];
$recs = $_GET['recs'];
$responseInfo = "";

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';

if ($maintCd == 'D') {
    $stmtSQL = " Delete From ETGLAW Where WAXHND='$profileHandle' 
                 and WADATE='$transDate' and WAEMID=$emid and WASHFT=$shft and WARECS=$recs";
} elseif ($maintCd == 'A') {
    $stmtSQL = " Insert Into ETGLAW
    (WAXHND,WADATE,WAEMID,WASHFT,WARECS,WASTRT,WASTOP,
    WALNAM,WAFNAM,WAMIDI,WACO,WAFAC,WAEMP,WASCTL,WASCHD)
    Select '$profileHandle',LBDATE,LBEMID,LBSHFT,LBRECS,LBSTRT,LBSTOP,
    EMLNAM,EMFNAM,EMMIDI,LBCO,LBFAC,LBEMP,LBSCTL,LBSCHD
    From HREMPL inner join SIMLBP on LBCO=EMCOMP and LBFAC=EMFACL and LBEMP=EMEMPL
    Where LBDATE='$transDate' and LBEMID=$emid and LBSHFT=$shft and LBRECS=$recs ";
}

// $debugFile = '/tmp/response60125.txt';
// file_put_contents ( $debugFile, ' ; stmtSQL=' . $stmtSQL, FILE_APPEND );

$status = db2_exec($i5Connect->getConnection(), $stmtSQL);

if (! $status) {}

?>