<?php

$cx = $_GET['chgBox'];
if ($viewCheckBox[$cx]) {
	$viewCheckBox[$cx] = "0";
} else {
	$viewCheckBox[$cx] = "1";
}
$viewCheckBoxString = Set_CheckBox($viewCheckBoxDef,$viewCheckBox);

require 'stmtSQLClear.php';
$stmtSQL = " Update SYLFLW Set LWCVAR='$viewCheckBoxString' Where $sylflwSQL With NC";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
?>