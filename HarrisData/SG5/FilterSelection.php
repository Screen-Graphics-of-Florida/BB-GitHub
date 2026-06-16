<?php
$saveImageTitle = "Save Current Search Criteria to Filter";
$smSTitle = "Schedule CSV Download";
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromType = (isset($_GET['fromType'])) ? $_GET['fromType'] : "";
$fromCSV = (isset($_GET['fromCSV'])) ? $_GET['fromCSV'] : "N";
$fromTblID = (isset($_GET['fromTblID'])) ? $_GET['fromTblID'] : 0;
$fromPagID = (isset($_GET['fromPagID'])) ? $_GET['fromPagID'] : 0;
$fromScript = (isset($_GET['fromScript'])) ? strtoupper($_GET['fromScript']) : "";
$pageHeading1 = (isset($_GET['pageHeading1'])) ? $_GET['pageHeading1'] : "";
$role = (isset($_GET['role'])) ? $_GET['role'] : "";
$user = (isset($_GET['user'])) ? $_GET['user'] : "";
$filterID = (isset($_GET['filterID'])) ? $_GET['filterID'] : 0;
$seqID = (isset($_GET['seqID'])) ? $_GET['seqID'] : 0;
$sylMaxSeq = (isset($_GET['sylMaxSeq'])) ? $_GET['sylMaxSeq'] : 0;

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "Filter Selection";
$scriptName = "FilterSelection.php";
$scriptVarBase = "{$genericVarBase}&amp;fromCSV=" . urlencode($fromCSV) . "&amp;fromType=" . urlencode($fromType) . "&amp;fromScript=" . urlencode($fromScript) . "&amp;fromTblID=" . urlencode($fromTblID) . "&amp;fromPagID=" . urlencode($fromPagID) . "&amp;pageHeading1=" . urlencode($pageHeading1) . "&amp;sylMaxSeq=" . urlencode($sylMaxSeq);
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$popUpWin = "Y";
$advanceSearch = "N";
$allowSaveFilter = "N";
$dftOrderBy = array(
    array(
        "USDESCU",
        "D",
        "User"
    ),
    array(
        "LFROLE",
        "D",
        "Role"
    ),
    array(
        "LFNAMEU",
        "A",
        "Filter"
    )
);
require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "SELECT") {
    require 'stmtSQLClear.php';
    $stmtSQL .= " Delete From SYLFLW Where LWXHND='$profileHandle' and LWSCRNU='$fromScript' and LWTBID=$fromTblID and LWPGID=$fromPagID ";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

    require 'stmtSQLClear.php';
    $stmtSQL .= " Insert Into SYLFLW (LWXHND,LWSCRNU,LWTBID,LWPGID,LWFLID,LWSEQ,LWNAME,LWFVAR,LWOVAR,LWCVAR) ";
    $stmtSQL .= " Select '$profileHandle',LFSCRNU,LFTBID,LFPGID,LFFLID,LFSEQ,LFNAME,LFFVAR,LFOVAR,LFCVAR ";
    $stmtSQL .= " From SYLFLT Where ";
    $stmtSQL .= " LFSCRNU='$fromScript' and LFTBID=$fromTblID and LFPGID=$fromPagID ";
    $stmtSQL .= " and LFROLE='$role' and LFUSER='$user' and LFFLID=$filterID ";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

    $fromURL = RetValue("ERXHND='$profileHandle'  and ERTYPE='U'", "SYEERR", "EREERR");
    if ($fromURL != "") {
        $fromURL = "{$homeURL}{$phpPath}{$fromURL}";
    } else {
        $fromURL = "opener.location.href";
    }

    $fromURL = str_replace("amp;", "", $fromURL);
    print "\n <script TYPE=\"text/javascript\">";
    print "\n opener.location.href='$fromURL'";
    print "\n opener.focus();";
    print "\n window.close();";
    print "\n </script>";
    exit();
}

if ($formatToPrint != "") {
    $dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
    if ($sequence == "role") {
        $orby = array(
            array(
                "LFROLE",
                "A",
                "Role"
            ),
            array(
                "LFNAMEU",
                "A",
                "Filter"
            )
        );
    } elseif ($sequence == "user") {
        $orby = array(
            array(
                "USDESCU",
                "A",
                "User"
            ),
            array(
                "LFNAMEU",
                "A",
                "Filter"
            )
        );
    } elseif ($sequence == "name") {
        $orby = array(
            array(
                "LFNAMEU",
                "A",
                "Filter"
            )
        );
    } elseif ($sequence == "dflt") {
        $orby = array(
            array(
                "LFDFLT",
                "A",
                "Default"
            ),
            array(
                "LFNAMEU",
                "A",
                "Filter"
            )
        );
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    require_once 'QuickSearch.php';
}

require_once ($docType);
print "\n <html> 	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onBlur=\"clearConfMessage()\" onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

require 'stmtSQLClear.php';
$stmtSQL .= " Select SYLFLT.*, coalesce(USDESC,' ') as USDESC, coalesce(USDESCU,' ') as USDESCU ";
$fileSQL .= " SYLFLT left join SYUSER on LFUSER=USUSER ";
$selectSQL = " LFSCRNU='$fromScript' and LFTBID=$fromTblID and LFPGID=$fromPagID and LFSEQ=0";
if ($admin != "Y") {
    $selectSQL .= " and ((LFROLE='$activeRole' or LFROLE=' ')  and (LFUSER='$userProfile' or LFUSER=' '))";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td>";
if ($formatToPrint != "Y") {
    // Program Option Security
    $hsylfl_OPT = pgmOptSecurity($profileHandle, $dataBaseID, "HSYLFL");

    print "\n <td class=\"toolbar\">";
    if ($hsylfl_OPT['sec_01'] == "Y") {
        print "\n <a href=\"{$homeURL}{$phpPath}FilterSelectionMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a>";
    }

    require_once 'FormatToprint.php';
    require_once 'HelpPage.php';
    print "</td>";
}

print "\n </tr>";
print "\n <tr><td><h2>$pageHeading1</h2></td></tr>";
print "\n </table>";
print "<div class=\"confMsg\">$confMessage</div>";
print $hrTagAttr;	

print "\n \n <script TYPE=\"text/javascript\">";
print "\n window.receiveConfMessage = function(msg) {";
print "\n     document.getElementsByClassName('confMsg')[0].textContent = msg;";
print "\n }";
print "\n function clearConfMessage() {";
print "\n     document.getElementsByClassName('confMsg')[0].textContent = '';";
print "\n }";
print "\n </script> \n";  

$workSQL = "LWXHND='$profileHandle' and LWSCRNU='$fromScript' and LWTBID=$fromTblID and LWPGID=$fromPagID and LWSEQ=0";
$filterVar = RetValue("$workSQL", "SYLFLW", "LWFVAR");
$ordByVar = RetValue("$workSQL", "SYLFLW", "LWOVAR");
$currentCriteria = Decat_Field("@@fild", $filterVar);
$currentCriteria = html_entity_decode($currentCriteria);
$returnArray = Get_OrderBy($ordByVar);
$currentSequence = str_replace(",", "<br>", $returnArray['orderByDisplay']);

require 'SearchCriteria.php';

if ($formatToPrint == "") {
    $qsOpt = "\n <option value=\"LFNAMEU|null|Filter Name|A|U\" title=\"Filter Name\" SELECTED>Filter Name";
    if ($admin == "Y") {
        $qsOpt .= "\n <option value=\"LFROLE|null|Role|A|U\" title=\"Role\">Role";
        $qsOpt .= "\n <option value=\"USDESCU|null|User|A|U\" title=\"User\">User";
    }
    require 'QuickSearchOption.php';
}

print "<table $contentTable><tr>";
if ($formatToPrint != "Y") {
    print "<th class=\"colhdr\">$optionHeading</th>";
}
$returnValue = OrderBy_Sort("LFNAMEU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=name\" title=\"Sequence By Filter Name\">{$sortPoint}Filter Name</a></th>";
$returnValue = OrderBy_Sort("LFROLE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=role\"  title=\"Sequence By Role, Filter Name\">{$sortPoint}Role</a></th>";
$returnValue = OrderBy_Sort("USDESCU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=user\"    title=\"Sequence By User, Filter Name\">{$sortPoint}User</a></th>";
$returnValue = OrderBy_Sort("LFDFLT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=dflt\"    title=\"Sequence By Default, Filter Name\">{$sortPoint}Dft</a></th>";
print "\n <th class=\"colhdr\">Criteria</th>";
print "\n <th class=\"colhdr\">Sequence By</th></tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }

    $returnArray = Get_OrderBy($row['LFOVAR']);
    $F_orderByDisplay = str_replace(",", "<br>", $returnArray['orderByDisplay']);
    $F_orderByDisplay = html_entity_decode($F_orderByDisplay);

    $F_filterBy = Decat_Field("@@fild", $row['LFFVAR']);
    $F_filterBy = html_entity_decode($F_filterBy);
    if ($row['LFDFLT'] == "Y") {
        $dftImage = $checkImage;
    } else {
        $dftImage = "";
    }

    require 'SetRowClass.php';
    $confirmDesc = Format_Confirm_Desc("Role", $row['LFROLE'], "User", $row['USDESC'], "Filter Name", $row['LFNAME']);
    $maintainVar = "{$scriptVarBase}&amp;filterID=" . urlencode($row['LFFLID']) . "&amp;filterName=" . urlencode($row['LFNAME']) . "&amp;role=" . urlencode($row['LFROLE']) . "&amp;user=" . urlencode($row['LFUSER']);
    print "\n <tr class=\"$rowClass\" valign=\"top\">";
    if ($formatToPrint != "Y") {
        if ((trim($row['LFUSER'])) == $userProfile || $admin == "Y") {
            print "\n <td class=\"opticon\">";
            print "\n <a href=\"{$homeURL}{$phpPath}FilterSelectionMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageSml</a>";
            print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}FilterSelectionMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
            print "\n <a href=\"{$homeURL}{$phpPath}FilterSelectionMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=S\">$saveImageSml</a>";
//            if ((strpos($fromScript, 'HDLIST.PHP') !== false) && ($fromCSV !== "N") && ($fromType == "L")) {
            if ((strpos($fromScript, 'HDLIST.PHP') !== false) && ($fromCSV !== "N")) {
                print "\n <a href=\"{$homeURL}{$phpPath}hdListCsvDownloadSelection.php{$maintainVar}\" onClick=\"$workloadWinVar\">$smS</a>";
            }
            print "</td>";
        } else {
            print "<td>&nbsp;</td>";
        }
    }
    print "\n <td class=\"colalph\"><a href=\"{$baseURL}&amp;tag=SELECT&amp;filterID=" . urlencode($row['LFFLID']) . "&amp;role=" . urlencode($row['LFROLE']) . "&amp;user=" . urlencode($row['LFUSER']) . "\" title=\"Click here to select this filter\">{$row['LFNAME']}</a></td>";
    print "\n <td class=\"colalph\">{$row['LFROLE']}</td>";
    print "\n <td class=\"colalph\">{$row['USDESC']}</td>";
    print "\n <td class=\"colcode\">{$dftImage}</td>";
    print "\n <td class=\"colalph\">$F_filterBy</td>";
    print "\n <td class=\"colalph\">$F_orderByDisplay</td>";
    print "\n </tr>";

    $startRow ++;
    $rowCount ++;
}

if ($rowCount == 0) {
    require 'NoRecordsFound.php';
}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';
print "</td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";

?>