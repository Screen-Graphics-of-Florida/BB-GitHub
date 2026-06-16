<?php

if ($formatToPrint == ""){
	if ($dspMaxRows == ""){$dspMaxRows = "10";}
	$stmtSQL .= " For Fetch Only with NC Optimize For $dspMaxRows Rows ";
}else{
	if ($prtMaxRows == ""){$prtMaxRows = "All";}
	$stmtSQL .= " For Fetch Only with NC Optimize For $prtMaxRows Rows ";
}
?>