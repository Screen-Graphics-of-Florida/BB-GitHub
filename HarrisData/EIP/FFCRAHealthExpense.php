<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$downLoadCsv = (isset($_GET['downLoadCsv'])) ? $_GET['downLoadCsv'] : null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "FFCRA Health Expense";
$scriptName = "FFCRAHealthExpense.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$pageSelectList = 'N';
$advanceSearch = 'N';
$dftOrderBy = [["CO", "A", "Company"],["FACL", "A", "Facility"],["EMPL", "A", "Employee"]];

if ($downLoadCsv) {
    downloadToCsv();
    exit();
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

require_once($docType);
print "\n
<html> <head> ";
require_once($headInclude);
print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
print "\n </script> ";
require_once($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "FFCRAHealthExpense";
if ($formatToPrint == "") {
    require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";
print "\n <table $contentTable>";
print "\n <colgroup><col width=\"75%\"><col width=\"20%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if (trim($FFCRA_QHPDeds) == '') {
    print "<tr class=\"error\"><td class=\"confMsg\">Qualified Health Plans (FFCRA_QHPDeds) have not been defined in<br> Configuration->Control Panel Plus->EIP Configuration->BaseFormat</td></tr>";
    print "\n </table> ";
    exit();
}

if ($formatToPrint != "Y") {
    print "\n <td class=\"toolbar\">";
    print "<a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;downLoadCsv=Y\" title=\"Download to CSV File\">{$downloadCsv}</a>";
    require_once 'FormatToPrint.php';
    $designIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgHelp.gif\" title=\"View FFCRA Payroll Tax Credit Documentation. User ID available from HarrisData Marketing is required.\" alt=\"Help\">";
    print "<a href=\"https://hdcommunity.atlassian.net/wiki/spaces/AOH/pages/312147969/FFCRA+Payroll+Tax+Credit\" target=\"_blank\">{$designIcon}</a>";
    print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";

require_once 'ConfMessageDisplay.php';

print $hrTagAttr;

if ($formatToPrint) {
    $dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
    if ($sequence == "Co") {
        $orby = [["CO", "A", "Company"],["FACL", "A", "Facility"],["EMPL", "A", "Employee"]];
    } elseif ($sequence == "Facl") {
        $orby = [["FACL", "A", "Facility"]];
    } elseif ($sequence == "Empl") {
        $orby = [["EMPL", "A", "Employee"]];
    } elseif ($sequence == "Dept") {
        $orby = [["DEPT", "A", "Department"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "Name") {
        $orby = [["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "StdHours") {
        $orby = [["STD_ANN_HRS", "A", "Std Annual Hours"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "DedNbr") {
        $orby = [["DEDNO", "A", "Deduction Number"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "DedNam") {
        $orby = [["DEDNM", "A", "Deduction Name"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "Covr") {
        $orby = [["COVR", "A", "Coverage"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "Plan") {
        $orby = [["PLAN", "A", "Plan"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "EffDate") {
        $orby = [["EFF_DATE", "A", "Effective Date"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "ExpDate") {
        $orby = [["EXP_DATE", "A", "Expire Date"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "CostMonthly") {
        $orby = [["ER_COST_MONTHLY", "A", "Employer Monthly Cost"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "CostAnn") {
        $orby = [["ER_COST_ANN", "A", "Employer Annual Cost"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "Hourly") {
        $orby = [["ER_CONTRIBUTION_HOURLY", "A", "Employer Contribution Hourly"], ["upper(RNAM)", "A", "Employee name"]];
    } elseif ($sequence == "Daily") {
        $orby = [["ER_CONTRIBUTION_DAILY", "A", "Employer Contribution Daily"], ["upper(RNAM)", "A", "Employee name"]];
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD") {
    $andOr = $_POST['andOr'];
    require_once 'WildCardClear.php';
    $returnValue = Build_WildCard("EDCOMP", "Company", $_POST['srchCo'], "", $_POST['operCo'], "N");
    $returnValue = Build_WildCard("EDFACL", "Facility", $_POST['srchFac'], "", $_POST['operFac'], "N");
    $returnValue = Build_WildCard("EDEMPL", "Employee", $_POST['srchEmpl'], "", $_POST['operEmpl'], "N");
    $returnValue = Build_WildCard("EMDEPT", "Department", $_POST['srchDept'], "U", $_POST['operDept'], "A");
    $returnValue = Build_WildCard("upper(EMRNAM)", "Employee Name", $_POST['srchName'], "U", $_POST['operName'], "A");
    $returnValue = Build_WildCard("EDDDNO", "Deduction Number", $_POST['srchDedNbr'], "", $_POST['operDedNbr'], "N");
    $returnValue = Build_WildCard("upper(HVDDNM)", "Deduction Name", $_POST['srchDedName'], "U", $_POST['operDedName'], "A");
    require_once 'WildCardUpdate.php';
}

require_once($docType);
print "\n <html> \n	<head>";
require_once($headInclude);
$formName = "Search";

print "\n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once($searchBanner);
print "\n <table $baseTable>";

$stmtSQL = loadHealthExpense();
$sql_Record_Count = 99999999999;
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
// echo $stmtSQL;

$qsOpt  = "\n <option value=\"EDCOMP|null|Company|N|\" title=\"Company\">Company";
$qsOpt .= "\n <option value=\"EDFACL|null|Facility|N|\" title=\"Facility\">Facility";
$qsOpt .= "\n <option value=\"EDEMPL|null|Employee|N|\" title=\"Employee\">Employee";
$qsOpt .= "\n <option value=\"EMDEPT|null|Deparment|A|U\" title=\"Deparment\">Deparment";
$qsOpt .= "\n <option value=\"EMRNAM|null|Employee Name|A|U\" title=\"Employee Name\">Employee Name";
$qsOpt .= "\n <option value=\"EDDDNO|null|Deduction Number|N|\" title=\"Deduction Number\">Deduction Number";
$qsOpt .= "\n <option value=\"HVDDNM|null|Deduction Name|A|U\" title=\"Deduction Name\">Deduction Name";
$qsOpt .= "\n <option value=\"CVCOVR|null|Coverage|A|U\" title=\"Coverage\">Coverage";
$qsOpt .= "\n <option value=\"CVPLAN|null|Plan|A|U\" title=\"Plan\">Plan";
print "<table $contentTable> <tr>";
print "<tr><th colspan=\"8\">";
require 'QuickSearchOption.php';
print "</th>";
print "</tr>";

$returnValue = OrderBy_Sort("CO");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Co\" title=\"Sequence By Company,Facility\">{$sortPoint}Co</a></th>";

$returnValue = OrderBy_Sort("FACL");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Facl\" title=\"Sequence By Facility\">{$sortPoint}Fac</a></th>";

$returnValue = OrderBy_Sort("EMPL");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Empl\" title=\"Sequence By Employee\">{$sortPoint}Employee</a></th>";

$returnValue = OrderBy_Sort("DEPT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Dept\" title=\"Sequence By Department\">{$sortPoint}Dept</a></th>";

$returnValue = OrderBy_Sort("RNAM");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name\">{$sortPoint}Name</a></th>";

$returnValue = OrderBy_Sort("STD_ANN_HRS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StdHours\" title=\"Sequence By Standard Annual Hours\">{$sortPoint}Std<br>Annual<br>Hours</a></th>";

$returnValue = OrderBy_Sort("DEDNO");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DedNbr\" title=\"Sequence By Deduction Number\">{$sortPoint}Deduction<br>Number</a></th>";

$returnValue = OrderBy_Sort("DEDNM");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DedNam\" title=\"Sequence By Deduction Name\">{$sortPoint}Deduction<br>Name</a></th>";

$returnValue = OrderBy_Sort("COVR");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Covr\" title=\"Sequence By Coverage\">{$sortPoint}Coverage</a></th>";

$returnValue = OrderBy_Sort("PLAN");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Plan\" title=\"Sequence By Plan\">{$sortPoint}Plan</a></th>";

$returnValue = OrderBy_Sort("EFF_DATE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EffDate\" title=\"Sequence By Effective Date\">{$sortPoint}Effective<br>Date</a></th>";

$returnValue = OrderBy_Sort("EXP_DATE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ExpDate\" title=\"Sequence By Expire Date\">{$sortPoint}Expire<br>Date</a></th>";

$returnValue = OrderBy_Sort("ER_COST_MONTHLY");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CostMonthly\" title=\"Sequence By Employer Monthly Cost\">{$sortPoint}Employer<br>Monthly Cost</a></th>";

$returnValue = OrderBy_Sort("ER_COST_ANN");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CostAnn\" title=\"Sequence By Employer Annual Cost\">{$sortPoint}Employer<br>Annual Cost</a></th>";

$returnValue = OrderBy_Sort("ER_CONTRIBUTION_HOURLY");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Hourly\" title=\"Sequence By Employer Contribution Hourly\">{$sortPoint}Employer<br>Contribution<br>Hourly</a></th>";

$returnValue = OrderBy_Sort("ER_CONTRIBUTION_DAILY");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Daily\" title=\"Sequence By Employer Contribution Daily\">{$sortPoint}Employer<br>Contribution<br>Daily</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\">";
    print "\n     <td class=\"colnmbr\">$row[CO]</td>";
    print "\n     <td class=\"colnmbr\">$row[FACL]</td>";
    print "\n     <td class=\"colnmbr\">$row[EMPL]</td>";
    print "\n     <td class=\"colalph\">$row[DEPT]</td> ";
    print "\n     <td class=\"colalph\">$row[RNAM]</td> ";
    print "\n     <td class=\"colnmbr\">$row[STD_ANN_HRS]</td>";
    print "\n     <td class=\"colnmbr\">$row[DEDNO]</td>";
    print "\n     <td class=\"colalph\">$row[DEDNM]</td> ";
    print "\n     <td class=\"colalph\">$row[COVR]</td> ";
    print "\n     <td class=\"colalph\">$row[PLAN]</td> ";
    print "\n     <td class=\"coldate\">$row[EFF_DATE]</td>";
    print "\n     <td class=\"coldate\">$row[EXP_DATE]</td>";
    $F_ER_MONTHLY = Format_Nbr($row[ER_COST_MONTHLY], '2', $cstEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_ER_MONTHLY</td>";
    $F_ER_ANN = Format_Nbr($row[ER_COST_ANN], '2', $cstEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_ER_ANN</td>";
    $F_ER_HOURLY = Format_Nbr($row[ER_CONTRIBUTION_HOURLY], '4', $cstEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_ER_HOURLY</td>";
    $F_ER_DAILY = Format_Nbr($row[ER_CONTRIBUTION_DAILY], '4', $cstEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_ER_DAILY</td>";
    print "\n </tr>";
    $startRow++;
    $rowCount++;
}
if ($rowCount == 0) {
    require 'NoRecordsFound.php';
}

print "</table>";
print $hrTagAttr;
require_once 'Copyright.php';

function downloadToCsv()
{
    global $i5Connect;

    // output headers so that the file is downloaded rather than displayed
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    $csvFile = "FFCRA_Health_Expense";
    header('Content-Disposition: attachment; filename="' . $csvFile . '.csv"');
    header('Content-Transfer-Encoding: binary');

    // open file pointer to standard output
    $file = fopen('php://output', 'w');

    // add BOM to fix UTF-8 in Excel
    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    ob_end_clean();
    $csvHdr = [];
    $csvHdr[] = "Company";
    $csvHdr[] = "Facility";
    $csvHdr[] = "Employee";
    $csvHdr[] = "Department";
    $csvHdr[] = "Employee Name";
    $csvHdr[] = "Std Annual Hours";
    $csvHdr[] = "Ded Nbr";
    $csvHdr[] = "Ded Name";
    $csvHdr[] = "Coverage";
    $csvHdr[] = "Plan";
    $csvHdr[] = "Effective Date";
    $csvHdr[] = "Expire Date";
    $csvHdr[] = "Employer Monthly Cost";
    $csvHdr[] = "Employer Annual Cost";
    $csvHdr[] = "Employer Contribution Hourly";
    $csvHdr[] = "Employer Contribution Daily";
    fputcsv($file, $csvHdr);

    $stmtSQL = loadHealthExpense();
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
    $startRow = 1;
    while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
        $csvDtl = [];
        $csvDtl[] = $row[CO];
        $csvDtl[] = $row[FACL];
        $csvDtl[] = $row[EMPL];
        $csvDtl[] = trim($row[DEPT]);
        $csvDtl[] = trim($row[RNAM]);
        $csvDtl[] = $row[STD_ANN_HRS];
        $csvDtl[] = $row[DEDNO];
        $csvDtl[] = trim($row[DEDNM]);
        $csvDtl[] = trim($row[COVR]);
        $csvDtl[] = trim($row[PLAN]);
        $csvDtl[] = $row[EFF_DATE];
        $csvDtl[] = $row[EXP_DATE];
        $csvDtl[] = $row[ER_COST_MONTHLY];
        $csvDtl[] = $row[ER_COST_ANN];
        $csvDtl[] = $row[ER_CONTRIBUTION_HOURLY];
        $csvDtl[] = $row[ER_CONTRIBUTION_DAILY];
        fputcsv($file, $csvDtl);
        $startRow++;
    }
    fclose($file);
    ob_end_clean();   //    the buffer and never prints or returns anything.
}

function loadHealthExpense()
{
    global $FFCRA_QHPDeds, $orderBy ,$wildCardSearch, $appendWildCard, $selectSQL;

    require 'stmtSQLClear.php';

    $stmtSQL = " Select 
                EDCOMP as CO,
                EDFACL as FACL,
                EDEMPL as EMPL,
                EMDEPT as DEPT,
                EMRNAM as RNAM,
                EMANHR as STD_ANN_HRS,
                EDDDNO as DEDNO,
                HVDDNM as DEDNM,
                CVCOVR as COVR,
                CVPLAN as PLAN,
                F_MAKEDATE(EDEFDT) as EFF_DATE,
                F_MAKEDATE(EDEXDT) as EXP_DATE,
                CVCSTM as ER_COST_MONTHLY,
                dec(round((CVCSTM * 12),2),11,2) as ER_COST_ANN,
                case when EMANHR = 0 then 0 else 
                dec(round((CVCSTM * 12) / EMANHR,4),11,4) end as ER_CONTRIBUTION_HOURLY,
                case when EMANHR = 0 then 0 else 
                dec(round((CVCSTM * 12 * 8) / EMANHR,4),11,4) end as ER_CONTRIBUTION_DAILY
            from HREDED ed
                join HRDEDM dedm on (ed.EDDDNO = dedm.HVDDNO)
                join CBCOVR covr on (covr.CVDEDN = dedm.HVDDNO and ed.EDCOMP = covr.CVCOMP and ed.EDFACL = covr.CVFACL)
                join HREMPL ee on (ee.EMCOMP = ed.EDCOMP and ee.EMFACL = ed.EDFACL and ee.EMEMPL = ed.EDEMPL)
                where HVDDNO in (" . $FFCRA_QHPDeds . ")
                    and ('2020-04-01' >= F_MAKEDATE(EDEFDT) or F_MAKEDATE(EDEFDT) is null)
                    and ('2020-12-31' <  F_MAKEDATE(EDEXDT) or F_MAKEDATE(EDEXDT) is null)
            ";
    if ($wildCardSearch != "" && $appendWildCard!="N"){$stmtSQL .= $wildCardSearch;}

    $orderBy = (trim($orderBy) == '') ? 'EDCOMP,EDFACL,EDEMPL' : $orderBy;
    $stmtSQL .= " Order By $orderBy ";
    return $stmtSQL;
}

?>
