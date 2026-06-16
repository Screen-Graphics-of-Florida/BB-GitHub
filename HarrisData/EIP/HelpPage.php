<?php

$helpName = ($tblID == 0 || !$docScript) ? $scriptName : $docScript;
if ($helpName != "" && $formatToPrint !="Y"){
	if ($helpName == "SubmitJob.php") {$helpDocument = RetValue("DRPGNM='$submitEnvProgram'", "SYDOCR", "DRPAGE");}
	else                              {$helpDocument = RetValue("DRPGNM='$helpName$helpExt'", "SYDOCR", "DRPAGE");}

	if ($helpDocument  != ""){
		$helpPath = trim($helpPath);
		$helpDocument = trim($helpDocument);
		$docPath = "{$helpPath}{$helpDocument}";
		$fileFound = file_exists("{$helpPath}{$helpDocument}");
		if ($fileFound){
			if ($medIcon=="Y") {print "<a href=\"$docPath\" onclick=\"$helpWinVar\"> $helpPageImageMed</a>";}
			else {print "<a href=\"$docPath\" onclick=\"$helpWinVar\"> $helpPageImageLrg</a>";}
		}
	}
}
?>