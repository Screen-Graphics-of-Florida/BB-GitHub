<?php
if ($currentCriteria != "" || $currentSequence != "") {
	print "\n <table style=\"background-color : #FFFFEE; padding: 1px 1px 21; border : 2px solid #0066CC;\">";
	print "\n <tr><td class=\"colhdr\">Current Search Criteria</td><td class=\"colhdr\">Sequence By</td></tr>";
	print "\n <tr><td  valign=\"top\"class=\"colalph\">$currentCriteria</td><td valign=\"top\" class=\"colalph\">{$currentSequence}</td></tr>";
	print "\n </table>";
}
?>