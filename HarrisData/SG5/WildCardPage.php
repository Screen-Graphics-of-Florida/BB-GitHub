<?php

if ($sql_Record_Count > $maxRows){  //  Assign Paging Values
	$totalPages = ($sql_Record_Count / $maxRows);
	$totalPages = ceil($totalPages);
} else {
	$totalPages = 1;
}

$page = round((($startRow - 1) / ( $maxRows) + 1));
$rowIndexNext = $startRow  + $maxRows;
print "\n <div class=\"page\">";
print "\n <div style=\"float:left;\">Page:";

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
print "\n </div>";

// Icon section
print "\n <div style=\"float:left; margin-left:2ex;\">";
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

if ($wildCardDisplay != "") {print "\n <a href=\"javascript:void+0\" onMouseOver=\"showSel('selData')\" onMouseOut=\"hideSel('selData')\">{$wildView}</a>";}

if ($allowSaveFilter != "N") {
	print "\n <a href=\"{$baseURL}&amp;chgSrch=D\">{$wildDftImage}</a>";
	print "\n <a href=\"{$homeURL}{$phpPath}FilterSelection.php{$genericVarBase}&amp;tag=REPORT&amp;fromTblID=" . urlencode($tblID) . "&amp;fromPagID=" . urlencode($pagID) . "&amp;fromScript=" . urlencode($scriptName) . "&amp;pageHeading1=" . urlencode($page_title) . "\" onclick=\" saveFilterURL();{$selectionWinVar}\">{$wildSetImage}</a>";
	if ($advanceSearch != "N") {print "\n <a href=\"{$baseURL}&amp;tag=MASTERSEARCH\">{$wildChgImage}</a>";}
}

if ($allowSaveFilter != "N" || $wildCardDisplay != "") {print "\n <a href=\"{$baseURL}&amp;tag=QSEARCH\">{$wildClearImage}</a>";}
print "\n </div>";

// Check Box Section
print "\n <div style=\"float:left; margin-left:2ex;\">";
if (isset($viewCheckBoxDef)) {require "ViewCheckBoxPage.php";}
if (isset($viewCheckBox))    {require "ViewCheckBox.php";}
print "\n </div>";
print "\n </div>";

// Reset float
print "\n <br style=\"clear:both;\"> ";

$workOrderBy = str_replace( ",", "<br>", $orderByDisplay) ;
print "<div id=\"selData\" class=\"searchHover\"><table $quickSearchTable><tr><td class=\"colhdr\">Search Criteria</td><td class=\"colhdr\">Sequence By</td></tr> <tr><td valign=\"top\" class=\"colalph\">{$wildCardDisplay}</td><td valign=\"top\" class=\"colalph\">{$workOrderBy}</td></tr></table></div>";

?>