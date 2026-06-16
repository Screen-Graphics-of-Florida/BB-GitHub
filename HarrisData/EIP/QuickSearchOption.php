<?php
if ($touchScreen == "Y") {
    $pageSelectList = "N";
    $allowSaveFilter = "N";
    $advanceSearch = "N";
}

print "<table $contentTable><colgroup><col width=\"40%\"><col width=\"55%\"> \n <tr>";
print "<td class=\"page\">";
if ($pageSelectList == "Y") {
    if ($sql_Record_Count > $maxRows) { // Assign Paging Values
        $totalPages = ($sql_Record_Count / $maxRows);
        $totalPages = ceil($totalPages);
    } else {
        $totalPages = 1;
    }
}

$page = round((($startRow - 1) / ($maxRows) + 1));
$rowIndexNext = $startRow + $maxRows;
print "\n Page:";

if (($sql_Record_Count > $maxRows) && ($pageSelectList == "Y")) {
    $loop = $sql_Record_Count / $maxRows;
    $cnt = 1;
    print "\n <select class=\"page\" name=\"goToPage\" id=\"goToPage\" onChange=\"goToPage(this.options[this.selectedIndex].value)\">";
    print "\n <option value=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;tag=INPUT&amp;startRow=1\">1";
    while ($cnt < $loop) {
        $pageValue = ($cnt * $maxRows);
        ++ $pageValue;
        ++ $cnt;
        if ($cnt == $page) {
            print "\n <option value=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;tag=INPUT&amp;startRow=" . urlencode($pageValue) . "\" SELECTED>{$cnt}";
        } else {
            print "\n <option value=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}&amp;tag=INPUT&amp;startRow=" . urlencode($pageValue) . "\">{$cnt}";
        }
    }
    print "\n </select>";
} else {
    print "\n $page";
}
if ($pageSelectList == "Y") {
    print " of $totalPages";
} else {
    $row = db2_fetch_assoc($sqlResult, $rowIndexNext);
    if ($row == '') {
        $sql_Record_Count = 0;
    }
}

// Icon section
if (($nextPrevPos != 2) && ($nextPrevVar != "")) {
    if ($startRow > $maxRows) {
        print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}{$searchVarBase}&amp;tag=INPUT&amp;startRow=" . urlencode($startRow - $maxRows) . "\">{$previousImage}</a>";
    } elseif ($sql_Record_Count > $maxRows) {
        print "\n {$nextPrevBlank}";
    }
    if ($sql_Record_Count >= $rowIndexNext) {
        print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$nextPrevVar}{$searchVarBase}&amp;tag=INPUT&amp;startRow=" . urlencode($rowIndexNext) . "\">{$nextImage}</a>";
    } elseif ($sql_Record_Count > $maxRows) {
        print "\n {$nextPrevBlank}";
    }
}

if ($wildCardDisplay != "") {
    print "\n <a href=\"javascript:void+0\" onMouseOver=\"showSel('selData')\" onMouseOut=\"hideSel('selData')\">{$wildView}</a>";
}

if ($allowSaveFilter != "N") {
    print "\n <a href=\"{$baseURL}&amp;chgSrch=D\">{$wildDftImage}</a>";
    print "\n <a href=\"{$homeURL}{$phpPath}FilterSelection.php{$genericVarBase}&amp;tag=REPORT&amp;fromTblID=" . urlencode($tblID) . "&amp;fromPagID=" . urlencode($pagID) . "&amp;fromScript=" . urlencode($scriptName) . "&amp;pageHeading1=" . urlencode($page_title) . "\" onclick=\" saveFilterURL(); {$selectionWinVar}\">{$wildSetImage}</a>";
    if ($advanceSearch != "N") {
        print "\n <a href=\"{$baseURL}&amp;tag=MASTERSEARCH\">{$wildChgImage}</a>";
    }
}

if ($allowSaveFilter != "N" || $wildCardDisplay != "") {
    print "\n <a href=\"{$baseURL}&amp;tag=QSEARCH\">{$wildClearImage}</a>";
}

// Reset float
if ($qsOpt) {
    print "\n <br style=\"clear:both;\"> ";

    $workOrderBy = str_replace(",", "<br>", $orderByDisplay);
    print "<div id=\"selData\" class=\"searchHover\"><table $quickSearchTable><tr><td class=\"colhdr\">Search Criteria</td><td class=\"colhdr\">Sequence By</td></tr> <tr><td valign=\"top\" class=\"colalph\">{$wildCardDisplay}</td><td valign=\"top\" class=\"colalph\">{$workOrderBy}</td></tr></table></div>";
}
print "</td>";

if ($qsOpt) {
    print "\n <td class=\"page\">";
    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Search\" onSubmit=\"return validate(document.Search)\" ACTION=\"{$baseURL}&amp;tag=QSEARCH\">";
    print "\n &nbsp; Search:";
    if ($wildCardDisplay != "") {
        print "\n <select class=\"page\" NAME=\"andOr\" SIZE=1>";
        print "\n <option $a1 VALUE=\"clear\">";
        print "\n <option $a2 VALUE=\"and\">and";
        print "\n <option $a3 VALUE=\"or\">or";
        print "\n </select>";
    }
    print "\n <select class=\"page\" name=\"qsName\" onChange=\"checkSel(this.options[this.selectedIndex].value);\">";
    print "\n <option value=\"\">";
    print "\n $qsOpt";
    print "\n </select>";

    print "\n <select class=\"page\" NAME=\"qsOper\" SIZE=1 style=\"width: 5em;\">";
    print "\n <option value=\"\">";
    print "\n <option $S1 VALUE=\"LIKE\" title=\"Like\">Like";
    print "\n <option $S8 VALUE=\"NOT LIKE\" title=\"Not Like\">Not Like";
    print "\n <option $S2 VALUE=\"=\" title=\"Equal\">=";
    print "\n <option $S3 VALUE=\"<>\" title=\"Not Equal\">Not=";
    print "\n <option $S4 VALUE=\"<\" title=\"Less Than\"><";
    print "\n <option $S5 VALUE=\"<=\" title=\"Less Than or Equal\"><=";
    print "\n <option $S6 VALUE=\">\" title=\"Greater Than\">>";
    print "\n <option $S7 VALUE=\">=\" title=\"Greater Than or Equal\">>=";
    print "\n </select>";

    if ($touchScreen == "Y") {
        print "\n <input class=\"page\" type=\"text\" class=\"input\" id=\"qsValue\" onfocus=\"kbActive(this, 'keyboard');\" name=\"qsValue\" size=\"10\" maxlength=\"50\">";
        print "\n <input type=\"hidden\" id=\"submitSearch\">";
    } else {
        print "\n <input class=\"page\" type=\"text\" name=\"qsValue\" size=\"20\" maxlength=\"50\">";
    }
    print "\n <a href=\"javascript:check(document.Search)\">$goSearchImage</a>";
    print "\n <a id=\"calSrch\" style=\"position: absolute; visibility: hidden;\" href=\"javascript:calWindow('qsValue');\"> $calendarImage</a>";
    print "\n <span id=\"fsURL\"><a id=\"flagSrch\" style=\"position: absolute; visibility: hidden;\"  href=\"#\"> </a></span>";
    if ($touchScreen != "Y") {
        print "\n <script TYPE=\"text/javascript\">";
        print "\n document.Search.qsValue.focus();";
        print "\n </script>";
    }
    print "\n </form>";
    print "\n </td>";
    if ($checkBoxAfterQuickSearch == 'Y' && ($viewCheckBoxDef || $viewCheckBox)) {
        print "\n <td class=\"page\"> &nbsp;";
        if (isset($viewCheckBoxDef)) {
            require "ViewCheckBoxPage.php";
        }
        if (isset($viewCheckBox)) {
            require "ViewCheckBox.php";
        }
        print "\n \n </td>";
    }
}
print "\n </tr>";

// Check Box Section
if ($checkBoxAfterQuickSearch != 'Y' && ($viewCheckBoxDef || $viewCheckBox)) {
    print "\n <tr><td class=\"page\">";
    if (isset($viewCheckBoxDef)) {
        require "ViewCheckBoxPage.php";
    }
    if (isset($viewCheckBox)) {
        require "ViewCheckBox.php";
    }
    print "\n \n </td></tr>";
}
print "\n </table>";
?>