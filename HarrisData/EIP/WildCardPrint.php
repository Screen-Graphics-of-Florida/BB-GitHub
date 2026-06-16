<?php
if ($formatToPrint != "" && $_GET['hideSelectCriteria'] != "Y") {
	$filterVar = RetValue("$sylflwSQL", "SYLFLW", "LWFVAR");
	$wildPrint = Decat_Field("@@fild", $filterVar);
	if ($wildPrint != ""){
		print "\n <fieldset class=\"legendTitle\">";
		print "\n <legend class=\"searchcriteria\">Search Criteria <a href=\"{$baseURL}&amp;formatToPrint=Y&amp;hideSelectCriteria=Y\">{$closeSelImage}</a></legend>";
		print "\n <table $contentTable>";
		print "\n <tr><td class=\"searchcriteria\">$wildPrint</td></tr>";
		print "\n </table>";
		print "\n </fieldset>";
	}
}
?>