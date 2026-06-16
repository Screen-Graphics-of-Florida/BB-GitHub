<?php
if ($wildCardDisplay != "") {
	print "\n <fieldset class=\"legendBody\">";
	print "\n <legend class=\"legendTitle\">Current Search Criteria</legend>";
	print "\n <table $contentTable>";
	print "\n <colgroup>";
	print "\n <col width=\"99%\">";
	print "\n <col width=\"1%\">";
	print "\n <tr><td class=\"toolbar\"><td>&nbsp;</td><td><a href=\"{$wildCardResetURL}\">{$wildClearLrg}</a></td></tr>";
	print "\n <tr><td class=\"searchcriteria\">{$wildCardDisplay}</td></tr>";
	print "\n </table>";
	print "\n </fieldset>";
}
?>