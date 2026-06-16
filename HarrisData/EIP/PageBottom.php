<?php
if ($nextPrevPos != "1" && $formatToPrint == ""){
	print "\n <div class=\"pageBottom\"> \n";
	if ($rowIndexNext - $maxRows > $maxRows) {print "<a href=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;tag=INPUT&amp;startRow=" . urlencode($rowIndexNext-(2*$maxRows)) . "\">$previousImage</a> \n";}
	elseif ($sql_Record_Count > $maxRows) {print $nextPrevBlank;}
	if ($sql_Record_Count >= $rowIndexNext) {print "<a href=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;tag=INPUT&amp;startRow=" . urlencode($rowIndexNext) . "\">$nextImage</a>";}
	elseif ($sql_Record_Count > $maxRows) {print $nextPrevBlank;}
	print "\n </div> \n";
}
?>