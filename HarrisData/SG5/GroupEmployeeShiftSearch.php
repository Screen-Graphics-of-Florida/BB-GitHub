<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$forDate = $_GET['forDate'];
$docName = $_GET['docName'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once "ETControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "Employee Search";
$scriptName = "GroupEmployeeShiftSearch.php";
$scriptVarBase = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;forDate=" . urlencode(trim($forDate));
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$dftOrderBy = array(
    array(
        "EMLNAMU",
        "A",
        "Last Name"
    ),
    array(
        "EMFNAMU",
        "A",
        "First Name"
    )
);

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
    require_once ($docType);
    print "\n <html> <head>";
    $formName = "Search";
    require_once ($headInclude);
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'NumEdit.php';
    require_once 'CheckEnterSearch.php';
    require_once 'NoFormValidate.php';
    print "\n </script>";
    
    $scriptType = "S"; // L=List, S=Search, I=Inquiry
    $pageID = "";
    require_once 'AdvSearchTop.php';
    
    Build_AdvSrch_Entry("Last Name", "srchLast", "", "operLast", "opersel_alph_short", "A", "18", "18");
    Build_AdvSrch_Entry("First Name", "srchFirst", "", "operFirst", "opersel_alph_short", "A", "18", "18");
    
    $focusField = "srchLast";
    require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
    if ($sequence == "LastName") {
        $orby = array(
            array(
                "EMLNAMU",
                "A",
                "Last Name"
            ),
            array(
                "EMFNAMU",
                "A",
                "First Name"
            )
        );
    } elseif ($sequence == "FirstName") {
        $orby = array(
            array(
                "EMFNAMU",
                "A",
                "First Name"
            ),
            array(
                "EMLNAMU",
                "A",
                "Last Name"
            )
        );
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD") {
    $andOr = $_POST['andOr'];
    require_once 'WildCardClear.php';
    $returnValue = Build_WildCard("EMLNAMU", "Last Name", $_POST['srchLast'], "U", $_POST['operLast'], "A");
    $returnValue = Build_WildCard("EMFNAMU", "First Name", $_POST['srchFirst'], "U", $_POST['operFirst'], "A");
    require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
?>
function selectEmpl(selDate,selEmid,selShft,selRecs) {
	<?php print "\n var url =\"{$homeURL}{$phpPath}GroupLaborAdjEmpUpdate.php?baseVar=" . urlencode($baseVar) . "&eID=" . urlencode($eID) . "\"; \n"; ?>
    	url += "&maintCd=A";
    	url += "&transDate=" + escape(selDate);
    	url += "&emid=" + escape(selEmid);
    	url += "&shft=" + escape(selShft);
    	url += "&recs=" + escape(selRecs);
    	url += "&dummy=" + new Date().getTime();
	var ajaxRequest = new ajaxObject(url,empSelectResponse);
		ajaxRequest.update();
}

<?php
print "\n function empSelectResponse(responseText, responseStatus) {";
print "\n    if (responseStatus==200) {";
print "\n	     if (window.opener.document.$docName.redisplay) {window.opener.document.$docName.redisplay.value = 'Y';}";
print "\n        else if (window.opener.document.getElementById('redisplay')) {window.opener.document.getElementById('redisplay').innerHTML = 'Y';}";
print "\n        window.opener.document.$docName.submit();";
print "\n    } else  {";
print "\n        alert(responseStatus + \" -- Error Selecting Employee\");";
print "\n    }";
print "\n    window.close();";
print "\n }";
require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

require 'UserViewEmpl.php';
$uv_ScheduleName = "EMSCHD";
$uv_GroupName = "EMHGRP";
if ($HDMERL > 0) {
    $uv_PlantName = "EMPLNT";
    $uv_MfgDepartmentName = "EMMDPT";
    $uv_WorkCenterName = "EMWC";
}
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HREMPL.*, LBSTRT, LBSTOP, LBSHFT, LBRECS ";
$fileSQL .= " HREMPL inner join SIMLBP on LBCO=EMCOMP and LBFAC=EMFACL and LBEMP=EMEMPL ";
$selectSQL .= " EMACT=' ' and EMTRCD = ' ' and LBDATE='$forDate' and LBDTCL='15' ";
$selectSQL .= " and not exists (Select WAEMID from ETGLAW Where WAXHND='$profileHandle' and WAEMID=HREMPL.EMEMID) ";
$selectSQL .= " and not exists (Select EHEMID from HDMECH Where EHEMID=HREMPL.EMEMID and EHDATE='$forDate' and EHTRAN='10') ";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';

// echo $stmtSQL;

require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));

$qsOpt = "\n <option value=\"EMLNAMU|null|Last Name|A|U\" title=\"Last Name\" SELECTED>Last Name";
$qsOpt .= "\n <option value=\"EMFNAMU|null|First Name|A|U\" title=\"First Name\">First Name";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue = OrderBy_Sort("EMLNAMU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LastName\" title=\"Sequence By Last Name, Co/Fac, Employee\">{$sortPoint}Last Name</a></th>";
$returnValue = OrderBy_Sort("EMFNAMU");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=FirstName\" title=\"Sequence By First Name, Last Name, Co/Fac, Employee\">{$sortPoint}First Name</a></th>";
print "\n <th class=\"colhdr\">Shift Start</th>";
print "\n <th class=\"colhdr\">Shift Stop</th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    require 'SetRowClass.php';
    if ($TADSSF == 'Y') {
        $F_LBSTRT = EditHrsMinSec(TimeInputFromHMS($row['LBSTRT'], $TADSSF));
        $F_LBSTOP = EditHrsMinSec(TimeInputFromHMS($row['LBSTOP'], $TADSSF));
    } else {
        $F_LBSTRT = EditHrsMin(TimeInputFromHMS($row['LBSTRT'], $TADSSF));
        $F_LBSTOP = EditHrsMin(TimeInputFromHMS($row['LBSTOP'], $TADSSF));
    }
    print "\n <tr class=\"$rowClass\">";
    print "\n     <td class=\"colalph\"><a href=\"javascript:selectEmpl('{$forDate}',{$row['EMEMID']},{$row['LBSHFT']},{$row['LBRECS']});\" title=\"Select Employee\">{$row[EMLNAM]}</a></td> ";
    print "\n     <td class=\"colalph\">$row[EMFNAM]</td>";
    print "\n     <td class=\"colnmbr\">$F_LBSTRT</td>";
    print "\n     <td class=\"colnmbr\">$F_LBSTOP</td>";
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
print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
