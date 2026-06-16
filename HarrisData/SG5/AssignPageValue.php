<?php
$incFileName = __FILE__;
include 'IncludeBanner.php';

if ($sql_Record_Count > $maxRows){  //  Assign Paging Values
	$totalPages = ($sql_Record_Count / $maxRows);
	$totalPages = ceil($totalPages);
} else {
	$totalPages = 1;
}

$page = round((($startRow - 1) / ( $maxRows) + 1));
$rowIndexNext = $startRow  + $maxRows;
print "\n <div class=\"page\">Page:";

if (($sql_Record_Count > $maxRows) && ($pageSelectList == "Y")){
	$loop =  $sql_Record_Count / $maxRows;
	$cnt =  1;
	print "\n <select class=\"page\" name=\"goToPage\" id=\"goToPage\" onChange=\"goToPage(this.options[this.selectedIndex].value)\">";
	print "\n <option value=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;tag=INPUT&amp;startRow=1\">1";
	while ($cnt <  $loop){
		$pageValue = ($cnt * $maxRows);
		++$pageValue;
		++$cnt;
		if ($cnt ==$page){
			print "\n <option value=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;tag=INPUT&amp;startRow=" . urlencode($pageValue) . "\" SELECTED>{$cnt}";
		} else{
			print "\n <option value=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;tag=INPUT&amp;startRow=" . urlencode($pageValue) . "\">{$cnt}" ;
		}
	}
	print "\n </select>";
} else {
	print "\n $page";
}
print " of $totalPages";
if (($nextPrevPos != 2 )&& ($nextPrevVar != "")){
	if ($startRow > $maxRows){
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}{$searchVarBase}&amp;tag=INPUT&amp;startRow=" . urlencode($startRow-$maxRows) . "\">{$previousImage}</a>";
	}elseif ($sql_Record_Count > $maxRows){
		print "\n {$nextPrevBlank}";
	}
	if ($sql_Record_Count >= $rowIndexNext){
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}{$searchVarBase}&amp;tag=INPUT&amp;startRow=" . urlencode($rowIndexNext) . "\">{$nextImage}</a>";
	}elseif ($sql_Record_Count > $maxRows){
		print "\n {$nextPrevBlank}";
	}
}

if (isset($viewCheckBoxDef)) {
	include "ViewCheckBoxPage.php";
}

print "</div>";
$workOrderBy = str_replace( ",", "<br>", $orderByDisplay) ;
print "<div id=\"selData\" class=\"searchHover\"><table $quickSearchTable><tr><td class=\"colhdr\">Search Criteria</td><td class=\"colhdr\">Sequence By</td></tr> <tr><td valign=\"top\" class=\"colalph\">{$wildCardDisplay}</td><td valign=\"top\" class=\"colalph\">{$workOrderBy}</td></tr></table></div>";

?>