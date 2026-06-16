<?php

$program_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01=$program_OPT['sec_01'];
$sec_02=$program_OPT['sec_02'];
$sec_03=$program_OPT['sec_03'];
$sec_04=$program_OPT['sec_04'];

if (($sec_02=="N" && $sec_03=="N" && ($maintenanceCode!="A" && $maintenanceCode!="Z")) || ($sec_01=="N" && $maintenanceCode=="A") || (($sec_01=="N" || $sec_04 == "N") && $maintenanceCode=="Z")) {
	$pgmOptAuth = "F";
} else {$pgmOptAuth = ""; }

?>