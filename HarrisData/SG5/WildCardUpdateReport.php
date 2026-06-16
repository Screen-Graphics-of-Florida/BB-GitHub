<?php
$updateSearch    = $_POST['updateSearch'];
$wildCardTemp    = $returnValue['wildCardTemp'];
$wildDisplayTemp = $returnValue['wildDisplayTemp'];
$wildCardSearch  = $returnValue['wildCardSearch'];
$filterSearchVar = "{$baseURL}&amp;tag=REPORT&amp;scheduleJobSwitch=Y";

if ($wildCardTemp != ""){
	$wildCardSearch  .= $wildCardTemp;
	$wildCardSearch  .= "))";
	$wildCardDisplay .= $wildDisplayTemp;
}

if ($updateSearch == "C") {
	$wildCardSearch = "";
	$wildCardDisplay = "";
}

require 'stmtSQLClear.php';
$workSearch = $wildCardSearch;
$workSearch = str_replace("'", "''", $workSearch);
$wildCardDisplay = str_replace("'", "''", $wildCardDisplay);
$updSQL     = "@@filv". trim($workSearch) . "}{@@fild" . trim($wildCardDisplay) . "}{";
$stmtSQL    = " Update SYLFLW Set LWFVAR='$updSQL' Where $sylflwSQL With NC";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

?>