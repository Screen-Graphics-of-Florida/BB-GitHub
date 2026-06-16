<?php
if ($pageSelectList == "Y") {
	$countSQL = "*";
	if ($distinctSQL != "") {
		$distinctSQL = "char($distinctSQL)";
		$distinctSQL = strtr ( $distinctSQL, ",", ") concat char(" );
		$countSQL = "distinct $distinctSQL";
	}
	if (! $withSQL) {
		$sql_Record_Count = RetValue ( $selectSQL, $fileSQL, "count($countSQL)" );
	} else {
		$sql_Record_Count = RetValueWith ( $selectSQL, $fileSQL, $withSQL, "count($countSQL)" );
	}
} else {
	$sql_Record_Count = 99999999999;
}
?>