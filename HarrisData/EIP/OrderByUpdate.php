<?php
$ordByVar = Set_OrderBy($orby);
require 'stmtSQLClear.php';
$stmtSQL = " Update SYLFLW Set LWOVAR='$ordByVar' Where $sylflwSQL With NC";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

$returnArray    = Get_OrderBy($ordByVar);
$orderBy        = $returnArray['orderBy'];
$orderByDisplay = $returnArray['orderByDisplay'];
?>