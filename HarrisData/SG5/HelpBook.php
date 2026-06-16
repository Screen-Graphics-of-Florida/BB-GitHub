<?php

$helpName = ($tblID == 0) ? $scriptName : $docScript;
if (!$helpName) $helpName = $scriptName;
if ($helpName){
	if ($accessDoc == "Y"){
		if ($helpName == "SubmitJob.php"){$helpDocument=RetValue("DRPGNM='{$submitEnvProgram}'", "SYDOCR", "DRBOOK");}
		else                               {$helpDocument=RetValue("DRPGNM='{$helpName}{$helpExt}'", "SYDOCR", "DRBOOK");}
		if ($helpDocument  != ""){
			$helpPath = trim($helpPath);
			$helpDocument = trim($helpDocument);
			$docPath = "{$helpPath}{$helpDocument}";
			$fileFound = file_exists("{$helpPath}{$helpDocument}");
			if ($fileFound){print "<a href=\"$docPath\" onclick=\"$helpWinVar\">$helpBookImageLrg </a>";}
		}
	}
}
?>