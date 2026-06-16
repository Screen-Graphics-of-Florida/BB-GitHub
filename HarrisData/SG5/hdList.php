<?php
// error_reporting(E_ALL ^ E_NOTICE);
$wildViewTitle = "";
require_once 'GetURLParm.php';
require_once 'SetLibraryList.php';
require_once 'hdListInclude.php';
if (!$downloadToCsv) {
    require_once 'CopyrightBanner.php';
}
if ($formatToPrint || $tag == "EXPORT" || $downloadToCsv)
    ini_set('max_execution_time', 1800); // 300 seconds = 5 minutes

$page_title = $pageHeading1;
$meta_title = $pageHeading1;
$scriptName = "hdList.php";
$scriptVarBase = "{$genericVarBase}&amp;tblID=" . urlencode($tblID) . "&amp;pagID=" . urlencode($pagID);

if ($nMenu) {
    $scriptVarBase .= "&amp;nMenu=" . urlencode($nMenu);
}
if ($fRel) {
    $scriptVarBase .= "&amp;fRel=" . urlencode($fRel);
}
if ($fKey1) {
    $scriptVarBase .= "&amp;fKey1=" . urlencode($fKey1) . "&amp;fVal1=" . urlencode($fVal1);
}
if ($fKey2) {
    $scriptVarBase .= "&amp;fKey2=" . urlencode($fKey2) . "&amp;fVal2=" . urlencode($fVal2);
}
if ($fKey3) {
    $scriptVarBase .= "&amp;fKey3=" . urlencode($fKey3) . "&amp;fVal3=" . urlencode($fVal3);
}
if ($fKey4) {
    $scriptVarBase .= "&amp;fKey4=" . urlencode($fKey4) . "&amp;fVal4=" . urlencode($fVal4);
}
if ($fKey5) {
    $scriptVarBase .= "&amp;fKey5=" . urlencode($fKey5) . "&amp;fVal5=" . urlencode($fVal5);
}
if ($fDsc1) {
    $scriptVarBase .= "&amp;fDsc1=" . urlencode($fDsc1);
}
if ($fDsc2) {
    $scriptVarBase .= "&amp;fDsc2=" . urlencode($fDsc2);
}
if ($fDsc3) {
    $scriptVarBase .= "&amp;fDsc3=" . urlencode($fDsc3);
}
if ($fDsc4) {
    $scriptVarBase .= "&amp;fDsc4=" . urlencode($fDsc4);
}
if ($fDsc5) {
    $scriptVarBase .= "&amp;fDsc5=" . urlencode($fDsc5);
}

$nextPrevVar = $scriptVarBase;
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}";
$dspMaxRows = (int)$dspMaxRowsDft;
$prtMaxRows = (int)$prtMaxRowsDft;
$currentURL = urlSelfBuild(null, null, null, null);
$_SESSION[$fromURL] = $currentURL;
$qsNameID = "'qsName$eID'";
$qsOperID = "'qsOper$eID'";
$qsValueID = "'qsValue$eID'";
$uvID = "'uv$eID'";
$replaceValues = array(
    "&lt;br&gt;",
    "<br>"
);

$linkB_count = 0;
$linkB_sort = null;
$linkC_count = 0;
$linkC_sort = null;
$linkD_count = 0;
$linkD_sort = null;
$linkT_count = 0;
$linkT_sort = null;
if ($hdDocsetLink && $hdListLink) {
    $linkB_count = count($hdDocsetLink->xpath("linkid[type='B']"));
    if ($linkB_count > 0) {
        $linkB_sort = Link_Sort('B', $hdList_OPT);
        if (!$linkB_sort) {
            $linkB_count = 0;
        }
    }
    $linkC_count = count($hdDocsetLink->xpath("linkid[type='C']"));
    if ($linkC_count > 0) {
        $linkC_sort = Link_Sort('C', $hdList_OPT);
        if (!$linkC_sort) {
            $linkC_count = 0;
        }
    }
    $linkD_count = count($hdDocsetLink->xpath("linkid[type='D']"));
    if ($linkD_count > 0) {
        $linkD_sort = Link_Sort('D', $hdList_OPT);
        if (!$linkD_sort) {
            $linkD_count = 0;
        }
    }
    $linkT_count = count($hdDocsetLink->xpath("linkid[type='T']"));
    if ($linkT_count > 0) {
        $linkT_sort = Link_Sort('T', $hdList_OPT);
        if (!$linkT_sort) {
            $linkT_count = 0;
        }
    }
}
// $uv_count = count($hdDocsetRow->xpath("col[user_view_col<>' ']"));

if ($hdListFilter) {
    $viewCheckBoxDef = Rtv_Filter();
}
if ($ovrRowsPerPage) {
    $dspMaxRows = $ovrRowsPerPage;
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
    $editVariables = "";
    foreach ($srchCol as $colName) {
        Get_Column();
        $colName = str_replace("#", "_", $colName);
        if ($coldType == "NUMERIC" || $coldType == "DATE") {
            if ($editVariables) {
                $editVariables .= " &&";
            }
            if ($colFormat == "CYMD" || $colFormat == "ISODATE") {
                $editVariables .= " filterToday(document.Search.srch$colName) || editdate(document.Search.srch$colName,'$colHeading') ";
            } else {
                $colDigit = $colSize - $colDecm;
                $editVariables .= " editNum(document.Search.srch$colName,$colDigit,$colDecm,'$colHeading') ";
            }
        }
    }
    // Generate basic page structure
    require_once($docType);
    print "\n <html> \n	<head>";
    $formName = "Search";
    require_once($headInclude);

    print "\n \n <script TYPE=\"text/javascript\">";
    require_once 'AJAXRequest.js';
    require_once 'CalendarInclude.php';
    require_once 'CheckEnterSearch.php';
    require_once 'FilterToday.js';
    require_once 'UpperCase.php';
    require_once 'DateEdit.php';
    require_once 'Menu.js';
    require_once 'NumEdit.php';
    require_once 'SaveCurrentURL.php';

    if (!$editVariables) {
        require_once 'NoFormValidate.php';
    } else {
        print "\n function validate(searchForm) {";
        print "\n   if ($editVariables){";
        print "\n   return true;}";
        print "\n } \n";
    }
    print "\n </script> \n";

    $scriptType = "L"; // L=List, S=Search, I=Inquiry
    $pageID = "HDLIST";
    require_once 'AdvSearchTop.php';

    $focusField = "";
    foreach ($srchCol as $colName) {
        Get_Column();
        $colHeading = str_replace("<br>", " ", $colHeading);
        $maxWidth = ($coldType == "DATE" || $colFormat == "CYMD" || $colFormat == "ISODATE" || $colFormat == "TIMESTAMP") ? "40" : $colSize;
        if ($colDecm > 0)
            $maxWidth++;
        $colName = str_replace("#", "_", $colName);
        if (!$focusField) {
            $focusField = "srch$colName";
        }
        $colWidth = ($maxWidth > "12") ? "12" : $maxWidth;
        print "\n <tr><td class=\"dsphdr\">$colHeading</td>";
        $operNbr = "oper$colName";
        $selEqual = "";
        if ($colFormat == "PHONE") {
            print "\n <td>&nbsp;</td>";
            print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"srch$colName\" size=\"$colWidth\" maxlength=\"$maxWidth\"></td>";
        } elseif ($colfType != "") {
            if ($coldType == "NUMERIC") {
                print "\n <td>";
                require "opersel_num_short.php";
                print "</td>";
            } else {
                print "\n <td>";
                require "opersel_alph_short.php";
                print "</td>";
            }
            print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srch$colName\" id=\"srch$colName\" value=\"" . rtrim($fldValue) . "\" size=\"$colWidth\" maxlength=\"$maxWidth\"> ";
            print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Search&amp;flagType=$colfType&amp;flagSrchHdr=" . urlencode($colHeading) . "&amp;fldName=srch{$colName}&amp;fldDesc=srch{$colName}Desc\" onclick=\"$searchWinVar\"> $searchImage</a>";
            print "\n     <span class=\"dspdesc\" id=\"srch{$colName}Desc\">" . trim($fieldDesc) . "</span></td>";
        } elseif ($coldType == "NUMERIC" || $coldType == "DATE") {
            if ($colFormat == "CYMD" || $colFormat == "ISODATE") {
                print "\n <td>";
                require "opersel_num_short.php";
                print "</td>";
            } else {
                $selEqual = "SELECTED";
                print "\n <td>";
                require "opersel_alph_short.php";
                print "</td>";
            }
            print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"srch$colName\" id=\"srch$colName\" size=\"$colWidth\" maxlength=\"$maxWidth\">";
            if ($colFormat == "CYMD" || $colFormat == "ISODATE") {
                print "\n <a href=\"javascript:calWindow('srch$colName');\">$calendarImage</a>";
            }
            print "\n </td>";
        } else {
            print "\n <td>";
            require "opersel_alph_short.php";
            print "</td>";
            print "\n <td class=\"inputalph\"><input type=\"text\" name=\"srch$colName\" size=\"$colWidth\" maxlength=\"$maxWidth\"></td>";
        }
        print "\n </tr>";
    }
    print "\n </table>";
    print "\n <a href={$baseURL}&amp;tag=MASTERSEARCH&amp;chgSrch=D>$wildDftLrg</a>";
    print "\n <a href=\"javascript:document.Search.updateSearch.value='Y'; check(document.Search)\">$addToImage</a>";
    print "\n </fieldset>";

    print "\n <script TYPE=\"text/javascript\">";
    print "\n     document.Search.$focusField.focus();";
    print "\n </script>";
    print "\n </form>";
    print $hrTagAttr;
    print "\n <div class=\"copr\"> &copy; Copyright " . date("Y") . " HarrisData &nbsp; &nbsp;" . date("l F dS Y");
    require_once 'CurrentTime.php';
    $roleDesc = RetValue("RMROLE='{$activeRole}'", "SYROLM", "RMDESC");
    print " &nbsp; &nbsp; User: <span title=\"$userProfile\">$profileName</span> &nbsp; &nbsp; Role: <span title=\"$activeRole\">$roleDesc</span>";
    print "\n </div>";

    if ($formatToPrint == "" || $formatToPrint == "N") {
        print "\n <div class=\"noPrint\">";
        if ($linkT_count > 0) {
            dspLinks($linkT_sort);
        }
        require_once 'HelpBook.php';
        if ($allowSecInq == "Y") {
            print "\n <a href=\"{$homeURL}{$phpPath}SecurityInquiry.php{$scriptVarBase}&amp;tableDesc=" . urlencode($pageHeading1) . "&amp;tag=REPORT\"  onclick=\"$searchWinVar\">$securityInqImage</a>";
        }
        if ($admin == "Y") {
            print "\n <a href=\"{$homeURL}{$phpPath}Page.php{$scriptVarBase}&amp;tableDesc=" . urlencode($pageHeading1) . "&amp;tag=REPORT\" target=\"_blank\">$pageSelect</a>";
        }
        print "\n </div>";
    }
    print "\n </td> </tr> </table>";
    require_once 'Trailer.php';
    print "</body> </html>";
    exit();
}

if ($formatToPrint || $downloadToCsv) {
    $dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

// If no Order By, default to 1st column displayed
if (!$orderBy) {
    $tag = "ORDERBY";
    $_GET['sequence'] = $dspCol[0];
    $col = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($dspCol[0])) . "']");
    $colRel1 = (string)$col[0]->related_column_1;
    if ($colRel1) {
        $_GET['sequence'] = $colRel1;
    }
}

if ($tag == "ORDERBY") {
    $colName = $_GET['sequence'];
    $listCol = $hdListRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
    $col = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
    if ($listCol[0]->label) {
        $colHeading = (string)$listCol[0]->label;
    } else {
        $colHeading = (string)$col[0]->label;
    }
    $colHeading = Format_Heading($colHeading);
    $colRelID = (string)$col[0]->related_column_ID;
    $sortFld2 = null;
    $sortFld3 = null;
    $sortFld4 = null;
    $sortFld5 = null;
    if ($colRelID) {
        $colRel = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colRelID)) . "']");
        $colRel2 = (string)$colRel[0]->related_column_2;
        $sortFld2 = "({$colRel2})";
        $colRel3 = (string)$colRel[0]->related_column_3;
        if ($colRel3) {
            $sortFld3 = "({$colRel3})";
        }
        $colRel4 = (string)$colRel[0]->related_column_4;
        if ($colRel4) {
            $sortFld4 = "({$colRel4})";
        }
        $colRel5 = (string)$colRel[0]->related_column_5;
        if ($colRel5) {
            $sortFld5 = "({$colRel5})";
        }
    }
    if ($row[0]->label) {
        $colHeading = (string)$row[0]->label;
    } else {
        $colHeading = (string)$col[0]->label;
    }
    $colHeading = Format_Heading($colHeading);
    $colAltSort = (string)$col[0]->alt_sort;
    if (trim($colAltSort) == 'UPPER') {
        $colAltSort = "upper($colName)";
    }
    $sortFld = ($colAltSort) ? $colAltSort : $colName;
    $sortFld = "({$sortFld})";
    $colHeading = ($colRelID) ? $colRelDesc : $colHeading;
    if ($sortFld5) {
        $orby = array(
            array(
                $sortFld,
                "A",
                $colHeading
            ),
            array(
                $sortFld2,
                "A",
                ""
            ),
            array(
                $sortFld3,
                "A",
                ""
            ),
            array(
                $sortFld4,
                "A",
                ""
            ),
            array(
                $sortFld5,
                "A",
                ""
            )
        );
    } elseif ($sortFld4) {
        $orby = array(
            array(
                $sortFld,
                "A",
                $colHeading
            ),
            array(
                $sortFld2,
                "A",
                ""
            ),
            array(
                $sortFld3,
                "A",
                ""
            ),
            array(
                $sortFld4,
                "A",
                ""
            )
        );
    } elseif ($sortFld3) {
        $orby = array(
            array(
                $sortFld,
                "A",
                $colHeading
            ),
            array(
                $sortFld2,
                "A",
                ""
            ),
            array(
                $sortFld3,
                "A",
                ""
            )
        );
    } elseif ($sortFld2) {
        $orby = array(
            array(
                $sortFld,
                "A",
                $colHeading
            ),
            array(
                $sortFld2,
                "A",
                ""
            )
        );
    } else {
        $orby = array(
            array(
                $sortFld,
                "A",
                $colHeading
            )
        );
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    if (!isset($_POST['qsOper']) && isset($_GET['qsName'])) {
        $_POST['qsName'] = $_GET['qsName'];
        $_POST['qsOper'] = $_GET['qsOper'];
        $_POST['qsValue'] = $_GET['qsValue'];
        $_POST['andOr'] = "and";
        if (isset($_GET['qsClear'])) {
            $_POST['andOr'] = "clear";
            unset($_SESSION[$qsNameID]);
            unset($_SESSION[$qsOperID]);
            unset($_SESSION[$qsValueID]);
        }
    }
    if (is_null($_SESSION[$qsNameID]) || $_SESSION[$qsNameID] != $_POST['qsName'] || $_SESSION[$qsOperID] != $_POST['qsOper'] || $_SESSION[$qsValueID] != $_POST['qsValue']) {
        $_SESSION[$qsNameID] = $_POST['qsName'];
        $_SESSION[$qsOperID] = $_POST['qsOper'];
        $_SESSION[$qsValueID] = $_POST['qsValue'];
        $andOr = $_POST['andOr'];
        if ($andOr == "clear") {
            $andOr = null;
        }
        require_once 'WildCardClear.php';
        $col_srch = explode('|', $_POST['qsName']);
        $colName = $col_srch[0];
        Get_Column();
        $colAltSort = (string)$col[0]->alt_sort;
        if (trim($colAltSort) == 'UPPER') {
            $colAltSort = "upper($colName)";
        }
        $selName = ($colAltSort) ? $colAltSort : $colName;
        $colName = str_replace("#", "_", $colName);
        $value = $_POST["qsValue"];
        $oper = trim($_POST["qsOper"]);
        if ($oper == "") {
            if ($coldType == "CHAR" || $coldType == "VARCHAR" || $coldType == "TIMESTMP") {
                $oper = "LIKE";
            } else {
                $oper = "=";
            }
        } elseif (($oper == "LIKE" || $oper == "NOT LIKE") && $coldType == "NUMERIC") {
            $coldType = "CHAR";
        }
        if ($colFormat == "PHONE" && ($oper == "LIKE" || $oper == "NOT LIKE")) {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "U", $oper, "A");
        } elseif ($colFormat == "PHONE" && $coldType == "CHAR") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", "", "PA");
        } elseif ($colFormat == "PHONE") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", "", "P");
        } elseif ($colFormat == "ISODATE" && $coldftVal == "NULL") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "IN");
        } elseif ($colFormat == "ISODATE") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "I");
        } elseif ($colFormat == "CYMD") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "D");
        } elseif ($colFormat == "CYR") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "CYR");
        } elseif ($colFormat == "PERIOD") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "DP");
        } elseif ($colFormat == "PERCENT") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "PCT");
        } elseif ($colFormat == "TIMEHHDD") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, $colFormat);
        } elseif ($colFormat == "TIMESTAMP") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, $colFormat);
        } elseif ($coldType == "NUMERIC") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "N");
        } else {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "U", $oper, "A");
        }
        require_once 'WildCardUpdate.php';
    }
}

if ($tag == "WILDCARD") {
    $andOr = $_POST['andOr'];
    require_once 'WildCardClear.php';

    foreach ($srchCol as $colName) {
        Get_Column();
        $colAltSort = (string)$col[0]->alt_sort;
        if (trim($colAltSort) == 'UPPER') {
            $colAltSort = "upper($colName)";
        }
        $selName = ($colAltSort) ? $colAltSort : $colName;
        $colName = str_replace("#", "_", $colName);
        $value = $_POST["srch$colName"];
        $oper = $_POST["oper$colName"];
        if (($oper == "LIKE" || $oper == "NOT LIKE") && $coldType == "NUMERIC") {
            $coldType = "CHAR";
        }
        if ($colFormat == "PHONE" && $coldType == "CHAR") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", "", "PA");
        } elseif ($colFormat == "PHONE") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", "", "P");
        } elseif ($colFormat == "ISODATE" && $coldftVal == "NULL") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "IN");
        } elseif ($colFormat == "ISODATE") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "I");
        } elseif ($colFormat == "CYMD") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "D");
        } elseif ($colFormat == "CYR") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "CYR");
        } elseif ($colFormat == "PERIOD") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "DP");
        } elseif ($colFormat == "PERCENT") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "PCT");
        } elseif ($colFormat == "TIMEHHDD") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, $colFormat);
        } elseif ($colFormat == "TIMESTAMP") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, $colFormat);
        } elseif ($coldType == "NUMERIC") {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "", $oper, "N");
        } else {
            $returnValue = Build_WildCard($selName, $colHeading, $value, "U", $oper, "A");
        }
    }
    require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {
    require "ViewCheckBoxUpdate.php";
}

if ($downloadToCsv) {
    $csvFile = $pageHeading1;
    $csvFile = preg_replace('/[^0-9a-zA-Z-_ ]/', "", $csvFile);

    if ($downloadToCsv != 'Y') {
        $attachPath = "{$homePath}{$uploadDirectory}{$dataBaseID}/";
        if (!file_exists("$attachPath")) {
            exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$attachPath\")'");
        }
        $folderPath = "{$attachPath}Reports/";
        if (!file_exists("$folderPath")) {
            exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$folderPath\")'");
        }
        $userPath = "{$folderPath}{$downloadToCsv}/";
        if (!file_exists("$userPath")) {
            exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$userPath\")'");
        }
        $fileName = $csvFile . '_' . $filterName . '_' . $_SERVER['REQUEST_TIME'] . ".csv";
        $file = fopen($userPath . $fileName, 'w');

    } else {
        // output headers so that the file is downloaded rather than displayed
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $csvFile . '.csv"');
        header('Content-Transfer-Encoding: binary');
        $file = fopen('php://output', 'w');
    }

    // add BOM to fix UTF-8 in Excel
    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    ob_end_clean();
}

if ($tag != "EXPORT" && !$downloadToCsv) {
    require_once($docType);
    print "\n <html> \n	<head>";
    $formName = "Search";
    require_once($headInclude);
    print "\n \n <script TYPE=\"text/javascript\">";
    require_once 'AJAXRequest.js';
    require_once 'SaveCurrentURL.php';
    print "\n function confirmOpt(msg,text) {return confirm(msg + \"\\n\" + \"\\n\" + text);} \n";
    require_once 'CalendarInclude.php';
    require_once 'DateEdit.php';
    if (!$formatToPrint)
        require_once 'Menu.js';
    require_once 'NumEdit.php';
    require_once 'CheckEnterSearch.php';
    if (!$editVariables) {
        require_once 'NoFormValidate.php';
    } else {
        print "\n function validate(searchForm) {";
        print "\n   if ($editVariables){";
        print "\n   return true;}";
        print "\n } \n";
    }
    require_once 'ShowHideSelCriteria.php';
    require_once 'CheckSel.js';
    print "\n function ClipBoard(text) {";
    print "\n   holdtext.innerText = text; ";
    print "\n   Copied = holdtext.createTextRange(); ";
    print "\n   Copied.execCommand(\"Copy\"); ";
    print "\n } \n";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";
    print "\n <body $bodyTagAttr>";
    if ($nMenu) {
        require_once($searchBanner);
    } else {
        require_once 'Banner.php';
    }
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = (string)$hdDocsetTable->name;
    if (!$nMenu) {
        require_once 'MenuDisplay.php';
    }
    print "\n <td class=\"content\">";
    print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr>";
    print "<td>";
    print "<h1>$pageHeading1</h1>";
    if ($pageHeading2) {
        print "<h2>$pageHeading2</h2>";
    }
    print "</td>";
    if (!$formatToPrint) {
        print "\n <td class=\"toolbar\">";
        if ($linkB_count > 0) {
            dspLinks($linkB_sort);
        }

        // XML
        if ($hdListTable->export) {
            $url = urlSelfBuild("EXPORT", 1, null, null);
            print " \n <a href=\"$url\" target=\"_blank\">$xMLFormatDesc</a>";
        }
        // Format To Print
        if (!isset($hdListTable->printer) || $hdListTable->printer == 'Y') {
            $url = urlSelfBuild(null, 1, null, "Y");
            print "\n <a href=\"$url\" target=\"_blank\">$formatPrintDesc</a>";
        }

        // Download CSV
        if (!isset($hdListTable->csv) || $hdListTable->csv == 'Y') {
            $url = urlSelfBuild(null, 1, null, null, "Y");
            print "\n <a href=\"$url\" target=\"_blank\">$downloadCsv</a>";
        }

        // Page Format Selection
        // $pageCount = RetValue("(PDTYPE=' ' or PDTYPE='L') and (PDROLE='$activeRole' or PDROLE=' ') and (PDUSER='$userProfile' or PDUSER=' ') and PDTBID=$tblID", "SYDSGN", "char(count(*))");
        // if ($pageCount>1) {print "\n <a href=\"{$homeURL}{$phpPath}PageSelect.php{$genericVarBase}&amp;fromTblID=" . urlencode($tblID) . "\" onclick=\"$searchWinVar\">$pageSelect</a>";}

        require_once 'HelpPage.php';
        print "\n </td>";
    }
    print "\n </tr></table>";
    if ($fKey1 && $_SESSION[$fKey1]) {
        print "<table $contentTable>";
        print $_SESSION[$fKey1];
        if ($fKey2 && $_SESSION[$fKey2])
            print $_SESSION[$fKey2];
        if ($fKey3 && $_SESSION[$fKey3])
            print $_SESSION[$fKey3];
        if ($fKey4 && $_SESSION[$fKey4])
            print $_SESSION[$fKey4];
        if ($fKey5 && $_SESSION[$fKey5])
            print $_SESSION[$fKey5];
        print "\n </table>";
    }
    require_once 'ConfMessageDisplay.php';
    print $hrTagAttr;
}

// Build SQL Statement
$sqlSelect = "SELECT ";
// Add columns
$sqlSelect .= " * ";
// Add table/view
$sqlFrom = " From $sqlView ";

// User View
// if ($uv_count>0) {
if (!isset($_SESSION[$uvID])) {
    $userViewCnt = RetValue("UVUSER='{$userProfile}'", "SYUVSF", "char(count(*))");
    if ($userViewCnt == 0) {
        $userViewCnt = RetValue("CUUSER='{$userProfile}'", "SYCUSF inner join SYUVSF on UVUSER=CUCUSR", "char(count(*))");
    }
    $_SESSION[$uvID] = $userViewCnt;
}
if ($HDRUVP == 'Y' || $HDRUVP == 'L' && $_SESSION[$uvID] > 0) {
    foreach ($hdDocsetRow->col as $col) {
        $colUvsn = trim($col->user_view_col);
        if ($colUvsn) {
            $colName = trim(strtoupper($col['id']));
            $uvName = RetValue("UFFLDN='{$colUvsn}'", "SYUFLD", "UFSCRN");
            $uvName = str_replace("#", "_", $uvName);
            $uvVarNm = 'uv_' . $uvName;
            $$uvVarNm = $colName;
        }
    }
    require 'UserView.php';
    if ($uv_Sql) {
        if ($sqlWhere) {
            $sqlWhere .= " and $uv_Sql";
        } else {
            $sqlWhere = "Where $uv_Sql";
        }
    }
}

// Checkbox
if ($hdListFilter) {
    $viewCheckSQL = Build_CheckBoxSQL($viewCheckBoxDef, $viewCheckBox);
    if ($viewCheckSQL) {
        if ($sqlWhere) {
            $sqlWhere .= " and $viewCheckSQL";
        } else {
            $sqlWhere = "Where $viewCheckSQL";
        }
    }
}

if ($wildCardSearch && $sqlWhere) {
    $sqlWhere .= " $wildCardSearch";
} elseif ($wildCardSearch) {
    $sqlWhere = "Where 1=1 $wildCardSearch";
}

// Order By Clause
$sqlOrderBy = " Order By $orderBy";
$stmtSQL = $sqlSelect . $sqlFrom;
if ($sqlWhere) {
    $stmtSQL .= $sqlWhere;
}
if ($sqlOrderBy) {
    $stmtSQL .= $sqlOrderBy;
}
// if ($sqlGroupBy) {$stmtSQL .= $sqlGroupBy;}
if (!$pagingSelectList) {
    $sql_Record_Count = 999999;
    if (!$formatToPrint && !$downloadToCsv && $tag != "EXPORT") {
        $fetchRows = $dspMaxRows + $startRow;
    } else {
        $fetchRows = $prtMaxRows;
    }
    $stmtSQL .= " Fetch First $fetchRows Rows Only ";
} elseif ($formatToPrint == "" && !$downloadToCsv) {
    $stmtSQL .= " For Fetch Only with NC Optimize For $dspMaxRows Rows ";
} else {
    $stmtSQL .= " For Fetch Only with NC Optimize For $prtMaxRows Rows ";
}
if ($debug)
    print $stmtSQL;
// stmtSQLTotalRows.php
if ($pagingSelectList && !$downloadToCsv) {
    $countSQL = "*";
    $whereValue = str_replace("Where", "", $sqlWhere);
    $sql_Record_Count = RetValue("$whereValue", "$sqlView", "char(count($countSQL))");
    $saveSQL = $stmtSQL;
    if (!$sql_Record_Count) {
        $stmtSQL = "Select FLTYPE From SYFLAG Where FLTYPE='NoRecFnd'";
        require 'stmtSQLEnd.php';
    }
}
$_SESSION[$eid] = $stmtSQL;
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));

if ($tag != "EXPORT") {
    if (!$formatToPrint && !$downloadToCsv) {
        print "<table $contentTable><tr>";
        print "<td class=\"page\">";
        if ($pagingSelectList) {
            if ($sql_Record_Count > $maxRows) {
                $totalPages = ($sql_Record_Count / $maxRows);
                $totalPages = ceil($totalPages);
            } else {
                $totalPages = 1;
            }
        }
        $page = round((($startRow - 1) / ($maxRows) + 1));
        $rowIndexNext = $startRow + $maxRows;
        print "\n Page:";
        if (($sql_Record_Count > $maxRows) && ($pagingSelectList)) {
            $loop = $sql_Record_Count / $maxRows;
            $cnt = 1;
            print "\n <select class=\"page\" name=\"goToPage\" id=\"goToPage\" onChange=\"goToPage(this.options[this.selectedIndex].value)\">";
            $url = urlSelfBuild(null, 1, null, null);
            print "\n <option value=\"" . rtrim($url) . "\">1";
            while ($cnt < $loop) {
                $pageValue = ($cnt * $maxRows);
                ++$pageValue;
                ++$cnt;
                $url = urlSelfBuild(null, $pageValue, null, null);
                if ($cnt == $page) {
                    print "\n <option value=\"" . rtrim($url) . "\" SELECTED>{$cnt}";
                } else {
                    print "\n <option value=\"" . rtrim($url) . "\">{$cnt}";
                }
            }
            print "\n </select>";
        } else {
            print "\n $page &nbsp;";
        }

        if ($pagingSelectList) {
            print " of $totalPages";
        } else {
            $row = db2_fetch_assoc($sqlResult, $rowIndexNext);
            if ($row == '')
                $sql_Record_Count = 0;
        }
        if ($nextPrevPos != "2") {
            if ($startRow > $maxRows) {
                $url = urlSelfBuild(null, ($startRow - $maxRows), null, null);
                print "\n <a href=\"$url\">{$previousImage}</a>";
            } elseif ($sql_Record_Count > $maxRows) {
                print "\n {$nextPrevBlank}";
            }
            if ($sql_Record_Count >= $rowIndexNext) {
                $url = urlSelfBuild(null, $rowIndexNext, null, null);
                print "\n <a href=\"$url\">{$nextImage}</a>";
            } elseif ($sql_Record_Count > $maxRows) {
                print "\n {$nextPrevBlank}";
            }
        }

        if ($allowSaveFilter != "N") {
            if ($wildCardDisplay != "") {
                print "\n <a href=\"javascript:void%200\" onMouseOver=\"showSel('selData')\" onMouseOut=\"hideSel('selData')\">{$wildView}</a>";
            }
            $fromCSV  = $hdListTable->csv;
            $url = urlSelfBuild(null, 1, null, null);
            print "\n <a href=\"{$url}&amp;chgSrch=D\">{$wildDftImage}</a>";
            print "\n <a href=\"{$homeURL}{$phpPath}FilterSelection.php{$genericVarBase}&amp;tag=REPORT&amp;fromCSV=" . urlencode($fromCSV) . "&amp;fromTblID=" . urlencode($tblID) . "&amp;fromPagID=" . urlencode($pagID) . "&amp;fromScript=" . urlencode($scriptName) . "&amp;pageHeading1=" . urlencode($pageHeading1) . "\" onclick=\" saveFilterURL();{$selectionWinVar}\">{$wildSetImage}</a>";
            print "\n <a href=\"{$homeURL}{$phpPath}FilterOrderBy.php{$scriptVarBase}&amp;fromScript=" . urlencode($scriptName) . "\" onclick=\"{$searchWinVar}\">{$wildSortImage}</a>";
            $url = urlSelfBuild("MASTERSEARCH", 1, null, null);
            print "\n <a href=\"$url\">{$wildChgImage}</a>";
            $url = urlSelfBuild("QSEARCH", 1, null, null);
            print "\n <a href=\"{$url}\">{$wildClearImage}</a>";
        }

        print "</td><td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>";
        print "\n <td class=\"page\">";
        $url = urlSelfBuild("QSEARCH", 1, null, null);
        print "\n \n <form class=\"formClass\" method=POST name=\"Search\" action=\"$url\">";
        print "\n &nbsp; Search:";
        if ($wildCardDisplay != "") {
            print "\n <select class=\"page\" NAME=\"andOr\" SIZE=1>";
            print "\n <option $a1 VALUE=\"clear\">";
            print "\n <option $a2 VALUE=\"and\">and";
            print "\n <option $a3 VALUE=\"or\">or";
            print "\n </select>";
        }
        $selID = "";
        print "\n <select class=\"page\" name=\"qsName\" onChange=\"checkSel(this.options[this.selectedIndex].value);\">";
        print "\n <option value=\"\">";
        $selDft = "selected=\"selected\"";
        foreach ($srchCol as $colName) {
            Get_Column();
            $colHeading = str_replace('&lt;br&gt;', ' ', $colHeading);
            $colHeading = str_replace("<br>", " ", $colHeading);
            $colID = (string)$col[0]['id'];
            if ($coldType == "DATE" || $colFormat == "CYMD" || $colFormat == "ISODATE") {
                $colID .= "|DATE";
            } else {
                $colID .= "|$colfType|$colHeading";
            }
            // if (strlen($colHeading)>20) {}
            if ($colName == $dftSrchColumn) {
                $selDft = "selected=\"selected\"";
                $selID = $colID;
            } else {
                $selDft = "";
            }
            print "\n <option value=\"{$colID}\" title=\"$colHeading\" $selDft>$colHeading";
        }
        print "\n </select>";

        print "\n <select class=\"page\" NAME=\"qsOper\" SIZE=1 style=\"width: 5em\">
		            <option value=\"\">
		            <option $S1 VALUE=\"LIKE\" title=\"Like\">Like
		            <option $S8 VALUE=\"NOT LIKE\" title=\"Not Like\">Not Like
		            <option $S2 VALUE=\"=\"    title=\"Equal\">=
		            <option $S3 VALUE=\"<>\"   title=\"Not Equal\">Not=
		            <option $S4 VALUE=\"<\"    title=\"Less Than\"><
		            <option $S5 VALUE=\"<=\"   title=\"Less Than or Equal\"><=
		            <option $S6 VALUE=\">\"    title=\"Greater Than\">>
		            <option $S7 VALUE=\">=\"   title=\"Greater Than or Equal\">>=
		          </select>";

        $boxSize = (int) $listWidgetSearchBoxSize;
        $boxSize = ($boxSize < 10 || $boxSize > 100) ? 20 : $boxSize;
        print "\n <input class=\"page\" type=\"text\" name=\"qsValue\" size=\"$boxSize\" maxlength=\"$boxSize\">";
        print "\n <a href=\"javascript:check(document.Search)\">$goSearchImage</a>";
        print "\n <a id=\"calSrch\" style=\"position: absolute; visibility: hidden;\" href=\"javascript:calWindow('qsValue');\"> $calendarImage</a>";
        print "\n <span id=\"fsURL\"><a id=\"flagSrch\" style=\"position: absolute; visibility: hidden;\"  href=\"#\"> </a></span>";
        print "\n <script TYPE=\"text/javascript\">";
        print "\n document.Search.qsValue.focus();";
        if ($selID != "") {
            print "\n checkSel('$selID');";
            $selID = "";
        }
        print "\n </script>";
        print "\n </form>";
        print "\n </td>";
        print "\n </tr>";

        if ($hdListFilter) {
            print "\n <tr><td class=\"checkBox\" colspan=\"3\">";
            print "\n View:";
            require 'ViewCheckBoxPage.php';
            print "\n </td></tr>";
        }
        print "\n </table>";
        if ($wildCardDisplay) {
            $workOrderBy = str_replace(",", "<br>", $orderByDisplay);
            $workOrderBy = html_entity_decode($workOrderBy);
            $wildCardDisplay = html_entity_decode($wildCardDisplay);
            print "\n <div id=\"selData\" class=\"searchHover\"><table $quickSearchTable><tr><td class=\"colhdr\">Search Criteria</td><td class=\"colhdr\">Sequence By</td></tr> <tr><td valign=\"top\" class=\"colalph\">{$wildCardDisplay}</td><td valign=\"top\" class=\"colalph\">{$workOrderBy}</td></tr></table></div>";
        }
    }

    if (!$downloadToCsv) {
        print "\n <table $contentTable><tr>";
        if ($linkD_count > 0 && !$formatToPrint) {
            print "<th class=\"colhdr\">$optionHeading</th>";
        }
    }

    $seq = 0;
    $csvHdr = [];
    foreach ($dspCol as $colName) {
        $listCol = $hdListRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
        $col = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
        if ($listCol[0]->colheading) {
            $colHeading = (string)$listCol[0]->colheading;
        } else {
            $colHeading = (string)$col[0]->colheading;
        }
        $colHeading = Format_Heading(trim($colHeading));
        $colRel1 = (string)$col[0]->related_column_1;
        if ($colRel1) {
            $col = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colRel1)) . "']");
            $colName = $colRel1;
        }
        if ($downloadToCsv) {
            $colHeading = str_replace($replaceValues, " ", $colHeading);
            $csvHdr[] = $colHeading;
        } else {
            dspColHeadings($col, $seq, $colName, $colHeading);
        }
        $seq++;
    }
    if ($downloadToCsv) {
        fputcsv($file, $csvHdr);
    } else {
        print "</tr>";
    }
}

if ($tag == "EXPORT") {
    $xmlRootSfx = "Document";
    $xmlNodeSfx = "Row";
    $xmlContainer = str_replace(array(
        ' ',
        '#'
    ), '', trim($xmlContainer));
    $xmlListName = $xmlContainer . $xmlRootSfx;
    $xmlNodeName = $xmlContainer . $xmlNodeSfx;
    require_once 'XMLInit.php';
}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }

    if ($tag == "EXPORT") {
        $dspTot = null; // Don't display Totals for XML Export
        $xmlID = $xmlDoc->createElement($xmlNodeName);
        $xmlRoot->appendChild($xmlID);
        foreach ($dspCol as $colName) {
            Get_Column();
            $colRel1 = (string)$col[0]->related_column_1;
            $colData = trim($row[$colName]);
            if ($colRel1)
                $colData = Format_Related();
            $fmtHeading = str_replace(array(
                ' ',
                '#',
                '/',
                '<br>',
                '&lt;br&gt;',
                ':',
                '%',
                '(',
                ')',
                '!',
                '@',
                '^',
                '*',
                '+',
                '=',
                '{',
                '}',
                '[',
                ']',
                '$',
                '%',
                '&',
                '~',
                '`',
                '|',
                '?'
            ), '', $colHeading);
            $firstChar = substr($fmtHeading, 0, 1);
            if ($firstChar >= '0' && $firstChar <= '9') {
                $fmtHeading = '_' . $fmtHeading;
            }
            $fmtValue = $colData;
            if ($coldType == "NUMERIC" || $coldType == "DATE") {
                if ($colFormat == "CYMD") {
                    $fmtValue = Format_Date($colData, "D");
                } elseif ($colFormat == "ISODATE") {
                    $fmtValue = Format_Date_ISO($colData, "D");
                }
            }
            $xmlTag = $xmlID->appendChild($xmlDoc->createElement($fmtHeading));
            $xmlTag->appendChild($xmlDoc->createTextNode($fmtValue));
        }
        $startRow++;
        $rowCount++;
        continue;
    }
    if (!$downloadToCsv) {
        require 'SetRowClass.php';
        print "\n <tr class=\"$rowClass\">";
    }
    if ($linkD_count > 0 && !$formatToPrint && !$downloadToCsv) {
        print "\n <td class=\"opticon\">";
        $saveDSEQ = "";
        foreach ($linkD_sort as $linkID) {
            $col = $hdDocsetLink->xpath("linkid[@id='" . trim(strtoupper($linkID)) . "']");
            $colCond = (string)trim($col[0]->condition_criteria);
            if ($colCond) {
                $colCond = urldecode($colCond);
                while (strpos($colCond, "@@parm") !== false) {
                    $colCond = str_replace("\"", "'", $colCond);
                    $parmName = Decat_Parm($colCond);
                    if ($parmName == "") {
                        break;
                    }
                    $colCond = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colCond);
                }
                eval("\$testCond = " . trim($colCond) . ";");
                if ((int)$testCond == false) {
                    continue;
                }
            }
            $optPGOV = (string)$col[0]->pgm_opt_program_override;
            $optPOPT = (string)$col[0]->pgm_opt_sequence;
            $optDSEQ = (string)$col[0]->display_sequence;

            if ($optDSEQ != $saveDSEQ) {
                $optPOPT = str_pad($optPOPT, 2, "0", STR_PAD_LEFT);
                if ($optPGOV) {
                    $ovp_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $optPGOV);
                }
                if ($optPOPT == "00" || (!$optPGOV && $hdList_OPT["sec_$optPOPT"] == "Y") || ($optPGOV && $ovp_OPT["sec_$optPOPT"] == "Y")) {
                    $optFILN = (string)$col[0]->link_table;
                    $optSELC = (string)$col[0]->link_criteria;
                    $rcdCnt = 1;
                    if ($optSELC != "") {
                        while (strpos($optSELC, "@@parm") !== false) {
                            $parmName = Decat_Parm($optSELC);
                            if ($parmName == "") {
                                break;
                            }
                            $optSELC = str_replace("@@parm[$parmName]", trim($row[$parmName]), $optSELC);
                        }
                        while (strpos($optSELC, "@@global") !== false) {
                            $parmName = Decat_Global($optSELC);
                            if ($parmName == "") {
                                break;
                            }
                            $optSELC = str_replace("@@global[$parmName]", trim($GLOBALS[$parmName]), $optSELC);
                        }

                        if ($optFILN) {
                            $optSELC = str_replace('"', "'", $optSELC);
                            $rcdCnt = RetValue("$optSELC", "$optFILN", "count(*)");
                        } else {
                            $optSELC = str_replace(" and ", " && ", $optSELC);
                            $optSELC = str_replace(" or ", " || ", $optSELC);
                            eval("\$testCond = " . trim($optSELC) . ";");
                            if ((int)$testCond == false) {
                                $rcdCnt = 0;
                            }
                        }
                    }
                    if ($rcdCnt) {
                        $saveDSEQ = $optDSEQ;
                        $optD2WN = (string)$col[0]->script_name;
                        $optTITL = (string)$col[0]->link_title;
                        $optTITL = str_replace("<br>", " ", $optTITL);
                        $optTRGT = (string)$col[0]->link_target;
                        $optURL = (string)$col[0]->link_URL;
                        $optURL = str_replace("&", "&amp;", $optURL);
                        $optPARM = (string)$col[0]->link_parm;
                        if (trim($col[0]->image) != '') {
                            $optIMG = (string)$col[0]->image;
                        } else {
                            $optIMG = (string)$col[0]->link_image;
                        }
                        if (trim($optIMG) == "") {
                            $optIMG = (string)$col[0]->image;
                        }
                        $optIPTH = (string)$col[0]->imagepath;

                        while (strpos($optPARM, "@@parm") !== false) {
                            $parmName = Decat_Field("@@parm", $optPARM);
                            if ($parmName == "") {
                                break;
                            }
                            $parmValue = rtrim($row[$parmName]);
                            $optPARM = str_replace("@@parm$parmName}{", urlencode($parmValue), $optPARM);
                        }

                        $confirmDel = "";
                        if (strpos($optURL, "@@confirm") !== false) {
                            $confMsg = "";
                            if (strpos($optURL, "@@confirmDelete") !== false) {
                                $confMsg = "$delRecordConf";
                                $optURL = str_replace("@@confirmDelete", "", $optURL);
                            } elseif (strpos($optURL, "@@confirmDeactivate") !== false) {
                                $confMsg = "Confirm Deactivate";
                                $optURL = str_replace("@@confirmDeactivate", "", $optURL);
                            } elseif (strpos($optURL, "@@confirmPublish") !== false) {
                                $confMsg = "Confirm Publish of";
                                $optURL = str_replace("@@confirmPublish", "", $optURL);
                            } elseif (strpos($optURL, "@@confirm=") !== false) {
                                $confStart = strpos($optURL, "@@confirm=") + 10;
                                $confEnd = strpos($optURL, "@@", $confStart + 1);
                                $confLen = strpos($optURL, "@@", $confStart + 1) - $confStart;
                                $confMsg = substr($optURL, $confStart, $confLen);
                                $optURL = substr($optURL, $confEnd);
                            }
                            $confirmDesc = "";
                            $columns = 0;
                            foreach ($hdListRow->col as $listCol) {
                                $colName = trim($listCol['id']);
                                Get_Column();
                                $colData = trim($row[$colName]);
                                $colRel1 = (string)$col[0]->related_column_1;
                                if ($colRel1)
                                    $colData = Format_Related();
                                $fmtValue = $colData;
                                $fmtValue = str_replace(array(
                                    "'",
                                    '"',
                                    "&"
                                ), array(
                                    "&acute;",
                                    "&quot;",
                                    "&amp;"
                                ), $colData);
                                $f_fmtValue = Format_Code(trim($fmtValue));
                                $confirmDesc .= $colHeading . ' ' . $f_fmtValue . '\n';
                                $columns++;
                                if ($columns == 4) {
                                    break;
                                }
                                $confirmDel = "return confirmOpt('$confMsg','$confirmDesc');";
                            }
                        }

                        $imageWindow = "";
                        if (strpos($optURL, "@@imageDisplay") !== false) {
                            $optURL = str_replace("@@imageDisplay", "", $optURL);
                            while (strpos($optIPTH, "@@global") !== false) {
                                $parmName = Decat_Global($optIPTH);
                                if ($parmName == "") {
                                    break;
                                }
                                $optIPTH = str_replace("@@global[$parmName]", trim($GLOBALS[$parmName]), $optIPTH);
                            }
                            while (strpos($optIPTH, "@@parm") !== false) {
                                $parmName = Decat_Parm($optIPTH);
                                if ($parmName == "") {
                                    break;
                                }
                                // $savIPTH = str_replace("@@parm[$parmName]", urlencode(trim($row[$parmName])), $optIPTH);
                                $savIPTH = str_replace("@@parm[$parmName]", trim($row[$parmName]), $optIPTH);
                                $optIPTH = '../' . $savIPTH;
                                $optIPTH2 = './' . $savIPTH;
                            }
                            if ((file_exists("{$optIPTH}") && strpos($optIPTH, ".") !== false) || (file_exists("{$optIPTH2}") && strpos($optIPTH2, ".") !== false)) {
                                $optPARM .= "&imageDisplayPath={$homeURL}{$homePath}{$savIPTH}";
                                $imageWindow = $imageWinVar;
                            } else {
                                $optD2WN = "";
                                $optURL = "";
                            }
                        }

                        $maintainVar = "";
                        if ($optD2WN || $optURL) {
                            $optURLSearch = array();
                            $optURLSearch[] = "@@homeURL";
                            $optURLSearch[] = (strpos($optURL, "@@phpPath") !== false) ? "@@phpPath" : "@@cGIPath";
                            $optURLSearch[] = "@@optionD2W";
                            $optURLReplace = array();
                            $optURLReplace[] = $homeURL;
                            $optURLReplace[] = (strpos($optURL, "@@phpPath") !== false) ? $phpPath : $cGIPath;
                            $optURLReplace[] = $optD2WN;
                            $optURLParm = array();
                            $optURLParm['baseVar'] = (strpos($optURL, "@@phpPath") !== false) ? $baseVar : $altBaseVar;
                            $optURLParm['portal'] = $portal;
                            $optURLParm['eID'] = $eID;
                            if (strpos($optPARM, "tblID") == false && strpos($optURL, "tblID") == false) {
                                $optURLParm['tblID'] = $tblID;
                                $optURLParm['pagID'] = $pagID;
                            }
                            /*
                             * if (strpos($optPARM, "fKey1") == true) {
                             * $optURLParm['fTblID'] = $tblID;
                             * $optURLParm['fTitle'] = $pageHeading1;
                             * $optURLParm['fRow'] = $startRow;
                             * }
                             */
                            $optURL = str_replace($optURLSearch, $optURLReplace, $optURL);
                            $optURL .= (strpos($optURL, "?") !== false) ? "&amp;" : "?";
                            $optURL .= http_build_query($optURLParm, '', '&amp;');
                            $optPARM = str_replace("&", "&amp;", $optPARM);
                            $optURL .= $optPARM;
                            $optURL .= $maintainVar;
                            if ($optTRGT != "") {
                                if (strtoupper($optTRGT) == "COMMENT") {
                                    $tgt = " onclick=\"$commentWinVar\" ";
                                } elseif (strtoupper($optTRGT) == "INQUIRY") {
                                    $tgt = " onclick=\"$inquiryWinVar\"";
                                } elseif (strtoupper($optTRGT) == "INVOICE") {
                                    $tgt = " onclick=\"$invoiceWinVar\"";
                                } else {
                                    $tgt = " target=\"$optTRGT\"";
                                }
                                print "\n <a href=\"$optURL\" title=\"$optTITL\" $tgt>${$optIMG}</a>";
                            } else {
                                print "\n <a onClick=\"saveCurrentURL();{$confirmDel}{$imageWindow}\" href=\"$optURL\" title=\"$optTITL\">${$optIMG}</a>";
                            }
                        }
                    }
                }
            }
        }
        print "\n </td>";
    }

    $savName = "";
    $savRelID = "";
    $csvDtl = [];
    foreach ($dspCol as $colName) {
        $listCol = $hdListRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
        $colDisplay = (string)trim(strtoupper($listCol[0]->length));
        $colOpr = (string)trim(strtoupper($listCol[0]->operand));
        $colCmp = (string)trim(strtoupper($listCol[0]->compare));
        $savCmp = null;
        $colClr = (string)trim(strtoupper($listCol[0]->color));

        if ($colName != $savName) {
            Get_Column();
            $colHeading = str_replace('&lt;br&gt;', ' ', $colHeading);
            $colHeading = str_replace("<br>", " ", $colHeading);
            $colRel1 = (string)$col[0]->related_column_1;
            $colData = trim($row[$colName]);
			$saveData = trim($row[$colName]);
            $relTable = (string)$col[0]->ref_table;
            $relColumn = (string)$col[0]->ref_column;
            $relSelc = (string)$col[0]->ref_criteria;
            if (!$downloadToCsv) {
                $toolTip = "";
                if ($relTable != "" && $relSelc != "") {
                    while (strpos($relSelc, "@@parm") !== false) {
                        $parmName = Decat_Parm($relSelc);
                        if ($parmName == "") {
                            break;
                        }
                        $relSelc = str_replace("@@parm[$parmName]", trim($row[$parmName]), $relSelc);
                    }
                    $toolTip = RetValue("$relSelc", "$relTable", "$relColumn");
                }
            }
            if ($colRel1)
                $colData = Format_Related();

            $savName = $colName;
            $savRelID = $colRelID;
            if ($downloadToCsv) {
                if ($colFormat == "CYMD") {
                    $colData = ($colData != "0") ? Date_CYMD_ISO($colData) : '';
                } elseif ($colFormat == "PERIOD") {
                    $colData = ($colData != "0") ? PeriodFromCYP($colData) : '';
                } elseif ($colFormat == "PERCENT") {
                    $colData = $colData * 100;
                }
                $csvDtl[] = $colData;
                continue;
            }

            // Style column by data type
            if ($colfType != '') {
                $selData = $colData;
                if ($coldType == "NUMERIC" && $colSize > 1) {
                    $selData = str_pad($colData, $colSize, "0", STR_PAD_LEFT);
                }
                $toolTip = RetValue("FLTYPE='$colfType' and FLVALU='$selData'", "SYFLAG", "FLDESC");
            } elseif ($colFormat == "CYMD" || $colFormat == "ISODATE") {
                if ($colFormat == "CYMD") {
                    $wrkDate = Date_CYMD_ISO($colData);
                    $saveData = $wrkDate;
                } else {
                    $wrkDate = $colData;
                }
                $toolTip = date('l F dS Y', strtotime($wrkDate));
                if (stripos($colCmp, 'TODAY') !== false) {
                    $savCmp = $colCmp;
                    $offset = substr($colCmp, stripos($colCmp, 'TODAY') + 6);
                    $colCmp = date('Y-m-d', strtotime($offset));
                }
            }
            $fmtValue = $colData;
            $cssClass = "colalph";
            $dftDec = (string)$col[0]->decimal;
            formatColumn($cssClass, $fmtValue, $coldType, $colFormat, $colSize, $colDecm, $dftDec, $colDisplay);
            if ($fmtValue == "") {
                $fmtValue = "&nbsp;";
            }
            if ($colFormat == "CREDITCARD") {
                $colData = "";
            }

            $optIMG = null;
            $colLink = false;
            $condErr = false;
            $rcdCnt = 0;
            if ($colRel1) {
                $linkCol = $colRel1;
            } else {
                $linkCol = $colName;
            }
            if (!$formatToPrint) {
                $linkColSort = $linkC_sort[$linkCol];
                if ($linkColSort) {
                    ksort($linkColSort);
                    foreach ($linkColSort as $linkID) {
                        if ($rcdCnt > 0) {
                            break;
                        }
                        foreach ($hdDocsetLink->xpath("linkid[@id='" . trim($linkID) . "']") as $link_data) {
                            $optURL = (string)$link_data->link_URL;
                            $optD2WN = (string)$link_data->script_name;
                            $optTITL = (string)$link_data->link_title;
                            $optIMG = (string)$link_data->link_image;
                            if (trim($optIMG) == "") {
                                $optIMG = (string)$link_data->image;
                            }
                            $optTRGT = (string)$link_data->link_target;
                            $optPARM = (string)$link_data->link_parm;
                            $optFILN = (string)$link_data->link_table;
                            $optSELC = (string)$link_data->link_criteria;
                            $colCond = (string)$link_data->condition_criteria;
                            if ($colCond) {
                                $colCond = urldecode($colCond);
                                while (strpos($colCond, "@@parm") !== false) {
                                    $colCond = str_replace("\"", "'", $colCond);
                                    $parmName = Decat_Parm($colCond);
                                    if ($parmName == "") {
                                        break;
                                    }
                                    $colCond = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colCond);
                                }
                                eval("\$testCond = " . trim($colCond) . ";");
                                if ((int)$testCond == false) {
                                    $condErr = true;
                                }
                            }
                            if (trim($optURL) == "" && strpos($optD2WN, "@@parm") !== false) {
                                while (strpos($optD2WN, "@@parm") !== false) {
                                    $parmName = Decat_Parm($optD2WN);
                                    if ($parmName == "") {
                                        break;
                                    }
                                    $optD2WN = str_replace("@@parm[$parmName]", trim($row[$parmName]), $optD2WN);
                                }
                                $optURL = $optD2WN;
                                $optD2WN = "";
                                if (trim($optTRGT) == "") {
                                    $optTRGT = "INQUIRY";
                                }
                            }
                            if (!$condErr) {
                                $rcdCnt = 1;
                                if ($optSELC != "") {
                                    while (strpos($optSELC, "@@parm") !== false) {
                                        $parmName = Decat_Parm($optSELC);
                                        if ($parmName == "") {
                                            break;
                                        }
                                        $parmValue = str_replace("'", "", trim($row[$parmName]));
                                        $optSELC = str_replace("@@parm[$parmName]", $parmValue, $optSELC);
                                    }
                                    if ($optFILN) {
                                        $rcdCnt = RetValue("$optSELC", "$optFILN", "char(count(*))");
                                    } else {
                                        $optSELC = str_replace(" and ", " && ", $optSELC);
                                        $optSELC = str_replace(" or ", " || ", $optSELC);
                                        $optSELC = str_replace("&lt;", "<", $optSELC);
                                        $optSELC = str_replace("&gt;", ">", $optSELC);
                                        eval("\$testCond = " . trim($optSELC) . ";");
                                        if ((int)$testCond == false) {
                                            $rcdCnt = 0;
                                        }
                                    }
                                }
                            }

                            if ($rcdCnt) {
                                $colLink = true;
                                if ($optTRGT == "") {
                                    $tgt = "";
                                } elseif (strtoupper($optTRGT) == "COMMENT") {
                                    $tgt = " onclick=\"$commentWinVar\" ";
                                } elseif (strtoupper($optTRGT) == "INQUIRY") {
                                    $tgt = " onclick=\"$inquiryWinVar\" ";
                                } elseif (strtoupper($optTRGT) == "INVOICE") {
                                    $tgt = " onclick=\"$invoiceWinVar\" ";
                                } elseif ($optTRGT != "") {
                                    $relProp = (strtoupper($optTRGT) == "_BLANK") ? 'rel="opener"' : '';
                                    $tgt = " target=\"$optTRGT\" $relProp ";
                                }

                                while (strpos($optPARM, "@@parm") !== false) {
                                    $parmName = Decat_Field("@@parm", $optPARM);
                                    if ($parmName == "") {
                                        break;
                                    }
                                    $optPARM = str_replace("@@parm$parmName}{", urlencode(trim($row[$parmName])), $optPARM);
                                }
                                break;
                            } elseif ($optIMG) {
                                $fmtValue = "";
                            }
                        }
                    }
                }
            }
            if ($colFormat != '') {
                if (!$toolTip && $colFormat != "SSN") {
                    $toolTip = $colData;
                }
                $colData = $fmtValue;
            }
            if ($optIMG) {
                $cssClass = "colcode";
            }
            $colTitle = str_replace("&nbsp;", " ", $colData);
            $bgColor = formatColor($colOpr, $colCmp, $colClr, $saveData);
            if ($bgColor !== '') {
                $toolTip .= formatTooltip($colOpr, $colCmp, $colHeading, $savCmp);
            }
            print "\n <td class=\"$cssClass\"" . $bgColor . " title=\"" . htmlentities($colTitle, ENT_QUOTES) . "\">";
            if ($colLink) {
                $optURLSearch = array();
                $optURLSearch[] = "@@homeURL";
                $optURLSearch[] = (strpos($optURL, "@@phpPath") !== false) ? "@@phpPath" : "@@cGIPath";
                $optURLSearch[] = "@@optionD2W";
                $optURLReplace = array();
                $optURLReplace[] = $homeURL;
                $optURLReplace[] = (strpos($optURL, "@@phpPath") !== false) ? $phpPath : $cGIPath;
                $optURLReplace[] = $optD2WN;
                $optURLParm = array();
                $optURLParm['baseVar'] = (strpos($optURL, "@@phpPath") !== false) ? $baseVar : $altBaseVar;
                $optURLParm['portal'] = $portal;
                $optURLParm['eID'] = $eID;
                $optURL = str_replace($optURLSearch, $optURLReplace, $optURL);
                $optURL .= (strpos($optURL, "?") !== false) ? "&amp;" : "?";
                $optURL .= http_build_query($optURLParm, '', '&amp;');
                $optPARM = str_replace("&", "&amp;", $optPARM);
                $optURL .= $optPARM;
                if ($optIMG) {
                    $fmtValue = ${$optIMG};
                }
                if ($toolTip) {
                    $moreInfo = "\n$toolTip";
                } else {
                    $moreInfo = "";
                }
                $moreInfo = htmlentities($moreInfo, ENT_QUOTES);
                $colData = htmlentities($colData, ENT_QUOTES);
                if (trim(htmlentities($moreInfo, ENT_QUOTES)) == trim($colData))
                    $moreInfo = "";
                $colData = str_replace("&amp;nbsp;", " ", $colData);
                $colData = str_replace("&amp;#039;", "&acute;", $colData);
                if ($tgt == "") {
                    print "\n <a onClick=\"saveCurrentURL();\" href=\"$optURL\" title=\"$optTITL - $colData $moreInfo\">$fmtValue</a>";
                } else {
                    print "\n <a href=\"$optURL\" title=\"$optTITL - $colData $moreInfo\" $tgt>$fmtValue</a>";
                }
            } elseif ($toolTip) {
                print "<span $helpCursor title=\"$toolTip\">$fmtValue</span>";
            } elseif (preg_match('/\R/', $fmtValue)) {
                print "<pre>$fmtValue</pre>";
            } else {
                print "$fmtValue";
            }
            print "</td>";
        }
    }

    if ($downloadToCsv) {
        fputcsv($file, $csvDtl);
    } else {
        print "</tr>";
    }
    $startRow++;
    $rowCount++;
}

if ($downloadToCsv) {
    fclose($file);
    exit();
}

if ($dspTot) {
    require 'SetRowClass.php';
    print "\n <tr>";
    if ($linkD_count > 0 && !$formatToPrint) {
        print "<td>&nbsp;</td>";
    }
    $sumSel = "";
    foreach ($dspTot as $colName) {
        if ($sumSel) {
            $sumSel .= ", ";
        }
        $sumSel .= "sum($colName) as $colName";
    }
    $newSQL = "SELECT " . $sumSel . substr($stmtSQL, 9);
    $obPOS = strpos(strtoupper($newSQL), "ORDER BY");
    if ($obPOS)
        $newSQL = substr($newSQL, 0, $obPOS - 1);
    $sqlResult = db2_exec($i5Connect->getConnection(), $newSQL);
    $row = db2_fetch_assoc($sqlResult);
    foreach ($dspCol as $colName) {
        $listCol = $hdListRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
        if ($listCol[0]->total) {
            $colData = trim($row[$colName]);
            Get_Column();
            $dftDec = (string)$col[0]->decimal;
            formatColumn($cssClass, $colData, $coldType, $colFormat, $colSize, $colDecm, $dftDec, $colDisplay);
            print "\n <td class=\"coltotal\">";
            print "$colData";
            print "</td>";
        } else {
            print "<td>&nbsp;</td>";
        }
    }
    print "</tr>";
}

require_once 'XMLExport.php';
if ($rowCount == 0) {
    require 'NoRecordsFound.php';
}
print "</table>";

// PageBottom.php
if ($rowCount > 0) {
    if ($nextPrevPos != "1" && !$formatToPrint) {
        print "\n <div class=\"pageBottom\"> \n";
        if (($rowIndexNext - $maxRows) > $maxRows) {
            $url = urlSelfBuild(null, ($rowIndexNext - (2 * $maxRows)), null, null);
            print "\n <a href=\"$url\">{$previousImage}</a>";
        } elseif ($sql_Record_Count > $maxRows) {
            print $nextPrevBlank;
        }
        if ($sql_Record_Count >= $rowIndexNext) {
            $url = urlSelfBuild(null, $rowIndexNext, null, null);
            print "\n <a href=\"$url\">{$nextImage}</a>";
        } elseif ($sql_Record_Count > $maxRows) {
            print $nextPrevBlank;
        }
        print "\n </div> \n";
    }
    require_once 'WildCardprint.php';
}

print "$hrTagAttr";
print "\n <div class=\"copr\"> &copy; Copyright " . date("Y") . " HarrisData &nbsp; &nbsp;" . date("l F dS Y");
require_once 'CurrentTime.php';
$roleDesc = RetValue("RMROLE='{$activeRole}'", "SYROLM", "RMDESC");
print " &nbsp; &nbsp; User: <span title=\"$userProfile\">$profileName</span> &nbsp; &nbsp; Role: <span title=\"$activeRole\">$roleDesc</span>";
if ($pagingSelectList) {
    $F_count = Format_Nbr($sql_Record_Count, "0", "1", "0", "", "");
    if ($admin == "Y") {
        print "<TEXTAREA ID=\"holdtext\" STYLE=\"display:none;\" rows=\"50\" cols=\"200\"> </TEXTAREA>";
        $F_stmtSQL = str_replace("'", "\'", $saveSQL);
        print " &nbsp; &nbsp; Total Rows: <span  ID=\"copytext\" onClick=\"ClipBoard('$F_stmtSQL');\" title=\"$saveSQL\">$F_count</span>";
    } else {
        print " &nbsp; &nbsp; Total Rows: $F_count";
    }
}
print "\n </div>";

if ($formatToPrint == "" || $formatToPrint == "N") {
    print "\n <div class=\"noPrint\">";
    if ($linkT_count > 0) {
        dspLinks($linkT_sort);
    }
    require_once 'HelpBook.php';
    if ($allowSecInq == "Y") {
        print "\n <a href=\"{$homeURL}{$phpPath}SecurityInquiry.php{$scriptVarBase}&amp;tableDesc=" . urlencode($pageHeading1) . "&amp;tag=REPORT\"  onclick=\"$searchWinVar\">$securityInqImage</a>";
    }
    if ($admin == "Y") {
        print "\n <a href=\"{$homeURL}{$phpPath}Page.php{$scriptVarBase}&amp;tableDesc=" . urlencode($pageHeading1) . "&amp;tag=REPORT\" target=\"_blank\">$pageSelect</a>";
    }
    print "\n </div>";
}

print "\n </td> </tr> </table>";
if ($nMenu) {
    require_once($searchTrailer);
} else {
    require_once 'Trailer.php';
}
print "\n </body> \n </html>";
?>