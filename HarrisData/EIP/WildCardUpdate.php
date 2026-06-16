<?php
$updateSearch = $_POST['updateSearch'];
$wildCardTemp = $returnValue['wildCardTemp'];
$wildDisplayTemp = $returnValue['wildDisplayTemp'];
$wildCardSearch = $returnValue['wildCardSearch'];
$masterSearchVar = "{$baseURL}&amp;tag=MASTERSEARCH";

if ($wildCardTemp != "") {
    $wildCardSearch .= $wildCardTemp;
    $wildCardSearch .= "))";
    $wildCardDisplay .= $wildDisplayTemp;
}

if ($chgSrch == "C") {
    $_SESSION['qsName'] = null;
    $wildCardSearch = "";
    $wildCardDisplay = "";
}

require 'stmtSQLClear.php';
$workSearch = $wildCardSearch;
$workSearch = str_replace("'", "''", $workSearch);
$wildCardDisplay = str_replace("'", "''", $wildCardDisplay);
$updSQL = "@@filv" . trim($workSearch) . "}{@@fild" . trim($wildCardDisplay) . "}{";
$stmtSQL = " Update SYLFLW Set LWFVAR='$updSQL' Where $sylflwSQL With NC";
$status = db2_exec($i5Connect->getConnection(), $stmtSQL);

if ($updateSearch != 'Y') {
    require_once 'WildCardDate.php';
}

if ($updateSearch == "Y" || $chgSrch == "C" || $chgSrch == "D") {
    print "<meta http-equiv=\"refresh\" content=\"0; URL={$masterSearchVar}\">";
    exit();
} else {
    $maxRows = $dspMaxRows;
}
?>