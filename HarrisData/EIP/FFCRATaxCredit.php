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

$page_title = "FFCRA Tax Credit";
$scriptName = "FFCRATaxCredit.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$pageSelectList = 'N';
$advanceSearch = 'N';
$dftOrderBy = [["COMPANY", "A", "Company"],["FACILITY", "A", "Facility"],["EMPLOYEE_NUMBER", "A", "Employee"]];

if ($downLoadCsv) {
    downloadToCsv();
    exit();
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

require_once ($docType);
print "\n
<html> <head> ";
require_once ($headInclude);
print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
print "\n </script> ";
require_once ($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "FFCRATaxCredit";
if ($formatToPrint == "") {
    require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";
print "\n <table $contentTable>";
print "\n <colgroup><col width=\"75%\"><col width=\"20%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if (trim($FFCRA_PayCodes) == '') {
    print "<tr class=\"error\"><td class=\"confMsg\">Qualified Pay Codes (FFCRA_PayCodes) have not been defined in<br> Configuration->Control Panel Plus->EIP Configuration->BaseFormat</td></tr>";
    print "\n </table> ";
    exit();
} elseif (trim($FFCRA_QHPDeds) == '') {
    print "<tr class=\"error\"><td class=\"confMsg\">Qualified Health Plans (FFCRA_QHPDeds) have not been defined in<br> Configuration->Control Panel Plus->EIP Configuration->BaseFormat</td></tr>";
    print "\n </table> ";
    exit();
} elseif ($formatToPrint != "Y") {
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
    if ($sequence == "Qtr") {
        $orby = [["QUARTER", "A", "Quarter"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Line") {
        $orby = [["LINE", "A", "Line"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Est") {
        $orby = [["ESTIMATED_AMOUNT", "A", "Estimated Credit Amount"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Pref") {
        $orby = [["PREF", "A", "Processing Reference Number"],["upper(REPORT_NAME)", "A", "Employee name"]];

    } elseif ($sequence == "Seq") {
        $orby = [["SEQ", "A", "Sequence Number"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Pped") {
        $orby = [["PAY_PERIOD_END", "A", "Pay Period End Date"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Chkd") {
        $orby = [["CHECK_DATE", "A", "Check Date"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Co") {
        $orby = [["COMPANY", "A", "Company"],["FACILITY", "A", "Facility"],["EMPLOYEE_NUMBER", "A", "Employee"]];

    } elseif ($sequence == "Facl") {
        $orby = [["FACILITY", "A", "Facility"]];
    } elseif ($sequence == "Empl") {
        $orby = [["EMPLOYEE_NUMBER", "A", "Employee"]];
    } elseif ($sequence == "Dept") {
        $orby = [["DEPT", "A", "Home Department"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Name") {
        $orby = [["upper(REPORT_NAME)", "A", "Employee name"]];

    } elseif ($sequence == "StdHours") {
        $orby = [["STD_ANN_HRS", "A", "Std Annual Hours"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Wrkd") {
        $orby = [["DATE_WORKED", "A", "Date Worked"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Code") {
        $orby = [["PAY_CODE", "A", "Pay Code"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Hrsw") {
        $orby = [["HOURS_WORKED", "A", "Hours Worked"],["upper(REPORT_NAME)", "A", "Employee name"]];

    } elseif ($sequence == "Unit") {
        $orby = [["UNIT_RATE", "A", "Unit Rate"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Gross") {
        $orby = [["GROSS_PAY", "A", "Gross Pay"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Ermr") {
        $orby = [["ER_MEDICARE_RATE", "A", "Employer Medicare Rate"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Ermt") {
        $orby = [["ER_MEDICARE_TAX", "A", "Employer Medicare Tax"],["upper(REPORT_NAME)", "A", "Employee name"]];
    
    } elseif ($sequence == "CostAnn") {
        $orby = [["QHPE_ANNUAL", "A", "Employer Annual Cost"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Hourly") {
        $orby = [["ER_QHPE_HOURLY", "A", "Employer Contribution Hourly"],["upper(REPORT_NAME)", "A", "Employee name"]];
    } elseif ($sequence == "Daily") {
        $orby = [["ER_QHPE_PRO_RATA", "A", "Employer Contribution Pro Rata"],["upper(REPORT_NAME)", "A", "Employee name"]];
    }
    require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
    require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD") {
    $andOr = $_POST['andOr'];
    require_once 'WildCardClear.php';
    $returnValue = Build_WildCard("EHPPDT", "Pay Period End Date", $_POST['srchPpe'], "", $_POST['operPpe'], "D");
    $returnValue = Build_WildCard("EHCKDT", "Check Date", $_POST['srchCkd'], "", $_POST['operCke'], "D");
    $returnValue = Build_WildCard("SHCOMP", "Company", $_POST['srchCo'], "", $_POST['operCo'], "N");
    $returnValue = Build_WildCard("SHFACL", "Facility", $_POST['srchFac'], "", $_POST['operFac'], "N");
    $returnValue = Build_WildCard("SHEMPL", "Employee", $_POST['srchEmpl'], "", $_POST['operEmpl'], "N");
    $returnValue = Build_WildCard("EMDEPT", "Home Department", $_POST['srchDept'], "U", $_POST['operDept'], "A");
    $returnValue = Build_WildCard("EMRNAMU", "Employee Name", $_POST['srchName'], "U", $_POST['operName'], "A");
    $returnValue = Build_WildCard("SHCODE", "Pay Code", $_POST['srchCode'], "U", $_POST['operCode'], "A");
    $returnValue = Build_WildCard("SHHOUR", "Hours Worked", $_POST['srchHrs'], "", $_POST['operHrs'], "N");
    require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CalendarInclude.php';
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

$stmtSQL = loadTaxCredit();
$sql_Record_Count = 99999999999;
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
// echo $stmtSQL;
$qsOpt = "\n <option value=\"EHPPDT|DATE|Pay Period End Date|D|\" title=\"Pay Period End Date\">Pay Period End Date";
$qsOpt .= "\n <option value=\"EHCKDT|DATE|Check Date|D|\" title=\"Check Date\">Check Date";
$qsOpt .= "\n <option value=\"SHCOMP|null|Company|N|\" title=\"Company\">Company";
$qsOpt .= "\n <option value=\"SHFACL|null|Facility|N|\" title=\"Facility\">Facility";
$qsOpt .= "\n <option value=\"SHEMPL|null|Employee|N|\" title=\"Employee\">Employee";
$qsOpt .= "\n <option value=\"EMDEPT|null|Home Deparment|A|U\" title=\"Home Deparment\">Home Deparment";
$qsOpt .= "\n <option value=\"EMRNAMU|null|Employee Name|A|U\" title=\"Employee Name\">Employee Name";
$qsOpt .= "\n <option value=\"SHCODE|null|Pay Code|A|U\" title=\"Pay Code\">Pay Code";
$qsOpt .= "\n <option value=\"SHHOUR|null|Hours Worked|N|\" title=\"Hours Worked\">Hours Worked";
print "<table $contentTable> <tr>";
print "<tr><th colspan=\"11\">";
require 'QuickSearchOption.php';
print "</th>";
print "</tr>";

$returnValue = OrderBy_Sort("QUARTER");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Qtr\" title=\"Sequence By Quarter\">{$sortPoint}Quarter</a></th>";

$returnValue = OrderBy_Sort("ESTIMATED_AMOUNT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Est\" title=\"Sequence By Estimated Credit Amount\">{$sortPoint}Estimated<br>Credit<br>Amount</a></th>";

$returnValue = OrderBy_Sort("PAY_PERIOD_END");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Pped\" title=\"Sequence By Pay Period End Date\">{$sortPoint}Pay Period<br>End Date</a></th>";

$returnValue = OrderBy_Sort("CHECK_DATE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Chkd\" title=\"Sequence By Check Date\">{$sortPoint}Check<br>Date</a></th>";

$returnValue = OrderBy_Sort("COMPANY");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Co\" title=\"Sequence By Company,Facility\">{$sortPoint}Co</a></th>";

$returnValue = OrderBy_Sort("FACILITY");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Facl\" title=\"Sequence By Facility\">{$sortPoint}Fac</a></th>";

$returnValue = OrderBy_Sort("EMPLOYEE_NUMBER");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Empl\" title=\"Sequence By Employee\">{$sortPoint}Employee</a></th>";

$returnValue = OrderBy_Sort("DEPT");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Dept\" title=\"Sequence By Home Department\">{$sortPoint}Home<br>Dept</a></th>";

$returnValue = OrderBy_Sort("REPORT_NAME");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name\">{$sortPoint}Name</a></th>";

$returnValue = OrderBy_Sort("STD_ANN_HRS");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StdHours\" title=\"Sequence By Standard Annual Hours\">{$sortPoint}Std<br>Annual<br>Hours</a></th>";

$returnValue = OrderBy_Sort("DATE_WORKED");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Wrkd\" title=\"Sequence By Date Worked\">{$sortPoint}Date<br>Worked</a></th>";

$returnValue = OrderBy_Sort("PAY_CODE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Code\" title=\"Sequence By Pay Code\">{$sortPoint}Pay<br>Code</a></th>";

$returnValue = OrderBy_Sort("HOURS_WORKED");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Hrsw\" title=\"Sequence By Hours Worked\">{$sortPoint}Hours<br>Worked</a></th>";

$returnValue = OrderBy_Sort("UNIT_RATE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Unit\" title=\"Sequence By Unit Rate\">{$sortPoint}Unit<br>Rate</a></th>";

$returnValue = OrderBy_Sort("GROSS_PAY");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Gross\" title=\"Sequence By Gross Pay\">{$sortPoint}Gross<br>Pay</a></th>";

$returnValue = OrderBy_Sort("ER_MEDICARE_RATE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Ermr\" title=\"Sequence By Employer Medicare Rate\">{$sortPoint}Employer<br>Medicare Rate</a></th>";

$returnValue = OrderBy_Sort("ER_MEDICARE_TAX");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Ermt\" title=\"Sequence By Employer Medicare Tax\">{$sortPoint}Employer<br>Medicare Tax</a></th>";

$returnValue = OrderBy_Sort("QHPE_ANNUAL");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CostAnn\" title=\"Sequence By Employer Annual Cost\">{$sortPoint}Employer<br>Annual Cost</a></th>";

$returnValue = OrderBy_Sort("ER_QHPE_HOURLY");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Hourly\" title=\"Sequence By Employer Contribution Hourly\">{$sortPoint}Employer<br>Contribution<br>Hourly</a></th>";

$returnValue = OrderBy_Sort("ER_QHPE_PRO_RATA");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Daily\" title=\"Sequence By Employer Contribution Pro Rata\">{$sortPoint}Employer<br>Contribution<br>Pro Rata</a></th>";

$returnValue = OrderBy_Sort("LINE");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Line\" title=\"Sequence By Line\">{$sortPoint}Line</a></th>";

$returnValue = OrderBy_Sort("PREF");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Pref\" title=\"Sequence By Processing Reference Number\">{$sortPoint}Processing<br>Reference<br>Number</a></th>";

$returnValue = OrderBy_Sort("SEQ");
$sortVar = $returnValue['sortedBy'];
$sortPoint = $returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Seq\" title=\"Sequence By Sequence Number\">{$sortPoint}Sequence<br>Number</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if ($rowCount >= $dspMaxRows) {
        break;
    }
    require 'SetRowClass.php';
    print "\n <tr class=\"$rowClass\">";
    print "\n     <td class=\"colalph\">{$row['QUARTER']}</td>";
    print "\n     <td class=\"colnmbr\">{$row['ESTIMATED_AMOUNT']}</td>";
    print "\n     <td class=\"coldate\">{$row['PAY_PERIOD_END']}</td>";
    print "\n     <td class=\"coldate\">{$row['CHECK_DATE']}</td>";
    print "\n     <td class=\"colnmbr\">{$row['COMPANY']}</td>";
    print "\n     <td class=\"colnmbr\">{$row['FACILITY']}</td>";
    print "\n     <td class=\"colnmbr\">{$row['EMPLOYEE_NUMBER']}</td>";
    print "\n     <td class=\"colalph\">{$row['DEPT']}</td> ";
    print "\n     <td class=\"colalph\">{$row['REPORT_NAME']}</td> ";
    print "\n     <td class=\"colnmbr\">{$row['STD_ANN_HRS']}</td>";
    print "\n     <td class=\"coldate\">{$row['DATE_WORKED']}</td>";
    print "\n     <td class=\"colalph\">{$row['PAY_CODE']}</td> ";
    print "\n     <td class=\"colnmbr\">{$row['HOURS_WORKED']}</td> ";
    print "\n     <td class=\"colnmbr\">{$row['UNIT_RATE']}</td> ";
    print "\n     <td class=\"colnmbr\">{$row['GROSS_PAY']}</td>";
    $F_ER_MEDICARE_RATE = Format_Nbr($row['ER_MEDICARE_RATE'], '4', $cstEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_ER_MEDICARE_RATE</td>";
    $F_ER_MEDICARE_TAX = Format_Nbr($row['ER_MEDICARE_TAX'], '2', $cstEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_ER_MEDICARE_TAX</td>";
    $F_ER_ANN = Format_Nbr($row['QHPE_ANNUAL'], '2', $cstEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_ER_ANN</td>";
    $F_ER_HOURLY = Format_Nbr($row['ER_QHPE_HOURLY'], '4', $cstEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_ER_HOURLY</td>";
    $F_ER_DAILY = Format_Nbr($row['ER_QHPE_PRO_RATA'], '2', $cstEditCode, '', '', '');
    print "\n     <td class=\"colnmbr\">$F_ER_DAILY</td>";
    print "\n     <td class=\"colnmbr\">{$row['LINE']}</td>";
    print "\n     <td class=\"colnmbr\">{$row['PREF']}</td>";
    print "\n     <td class=\"colnmbr\">{$row['SEQ']}</td>";
    print "\n </tr>";
    $startRow ++;
    $rowCount ++;
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
    $csvFile = "FFCRA_Tax_Credit";
    header('Content-Disposition: attachment; filename="' . $csvFile . '.csv"');
    header('Content-Transfer-Encoding: binary');
    
    // open file pointer to standard output
    $file = fopen('php://output', 'w');
    
    // add BOM to fix UTF-8 in Excel
    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    ob_end_clean();
    $csvHdr = [];
    $csvHdr[] = "Quarter";
    $csvHdr[] = "Estimated Credit Amount";
    $csvHdr[] = "Pay Period End Date";
    $csvHdr[] = "Check Date";
    $csvHdr[] = "Company";
    $csvHdr[] = "Facility";
    $csvHdr[] = "Employee";
    $csvHdr[] = "Home Department";
    $csvHdr[] = "Employee Name";
    $csvHdr[] = "Std Annual Hours";
    $csvHdr[] = "Date Worked";
    $csvHdr[] = "Pay Code";
    $csvHdr[] = "Hours Worked";
    $csvHdr[] = "Unit Rate";
    $csvHdr[] = "Gross Pay";
    $csvHdr[] = "Employer Medicare Rate";
    $csvHdr[] = "Employer Medicare Tax";
    $csvHdr[] = "Employer Annual Cost";
    $csvHdr[] = "Employer Contribution Hourly";
    $csvHdr[] = "Employer Contribution Pro Rata";
    $csvHdr[] = "Line";
    $csvHdr[] = "Processing Reference Number";
    $csvHdr[] = "Sequence Number";
    fputcsv($file, $csvHdr);
    
    $stmtSQL = loadTaxCredit();
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, [
        'cursor' => DB2_SCROLLABLE
    ]);
    $startRow = 1;
    while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
        $csvDtl = [];
        $csvDtl[] = $row['QUARTER'];
        $csvDtl[] = $row['ESTIMATED_AMOUNT'];
        $csvDtl[] = $row['PAY_PERIOD_END'];
        $csvDtl[] = $row['CHECK_DATE'];
        $csvDtl[] = $row['COMPANY'];
        $csvDtl[] = $row['FACILITY'];
        $csvDtl[] = $row['EMPLOYEE_NUMBER'];
        $csvDtl[] = trim($row['DEPT']);
        $csvDtl[] = trim($row['REPORT_NAME']);
        $csvDtl[] = $row['STD_ANN_HRS'];
        $csvDtl[] = $row['DATE_WORKED'];
        $csvDtl[] = trim($row['PAY_CODE']);
        $csvDtl[] = $row['HOURS_WORKED'];
        $csvDtl[] = $row['UNIT_RATE'];
        $csvDtl[] = $row['GROSS_PAY'];
        $csvDtl[] = $row['ER_MEDICARE_RATE'];
        $csvDtl[] = $row['ER_MEDICARE_TAX'];
        $csvDtl[] = $row['QHPE_ANNUAL'];
        $csvDtl[] = $row['ER_QHPE_HOURLY'];
        $csvDtl[] = $row['ER_QHPE_PRO_RATA'];
        $csvDtl[] = $row['LINE'];
        $csvDtl[] = $row['PREF'];
        $csvDtl[] = $row['SEQ'];
        fputcsv($file, $csvDtl);
        $startRow ++;
    }
    fclose($file);
    ob_end_clean(); // the buffer and never prints or returns anything.
}

function loadTaxCredit()
{
    global $FFCRA_PayCodes, $FFCRA_PayCodes_Line2, $FFCRA_QHPDeds, $orderBy, $wildCardSearch, $appendWildCard, $selectSQL;

    $arr = explode(', ', $FFCRA_PayCodes);
    $FFCRA_PayCodes = "'" . implode ( "', '", $arr ) . "'";
    $arr = explode(', ', $FFCRA_PayCodes_Line2);
    $FFCRA_PayCodes_Line2 = "'" . implode ( "', '", $arr ) . "'";
    
    require 'stmtSQLClear.php';
    $stmtSQL = " Select 
    YEAR(F_MAKEDATE(EHCKDT)) || '-' || QUARTER(F_MAKEDATE(EHCKDT)) as QUARTER,
    case when SHCODE in ({$FFCRA_PayCodes_Line2}) then 2 else 3 end as LINE,
    case when EMANHR = 0 then 0 else
    dec(SHEARN + round(SHEARN * 0.0145,2)
        + ifnull(round(SHHOUR * ER_COST_ANN / EMANHR, 2), 0), 11, 2)
    end as ESTIMATED_AMOUNT,
    SHPREF as PREF,
    SHSEQ as SEQ,
    F_MAKEDATE(EHPPDT) as PAY_PERIOD_END,
    F_MAKEDATE(EHCKDT) as CHECK_DATE,
    SHCOMP as COMPANY,
    SHFACL as FACILITY,
    EMDEPT as DEPT,
    SHEMPL as EMPLOYEE_NUMBER,
    EMEMID as EMPLOYEE_ID,
    EMRNAM as REPORT_NAME,
    EMANHR as STD_ANN_HRS,
    F_MAKEDATE(SHDTWK) as DATE_WORKED,
    SHCODE as PAY_CODE,
    SHHOUR as HOURS_WORKED,
    SHOVRT as UNIT_RATE,
    SHEARN as GROSS_PAY,
    0.0145 as ER_MEDICARE_RATE,
    dec(round(SHEARN * 0.0145,2), 11, 2) as ER_MEDICARE_TAX,
    ifnull(ER_COST_ANN, 0) as QHPE_ANNUAL,
    case when EMANHR = 0 then 0 else 
    dec(ifnull(round(ER_COST_ANN / EMANHR,4), 0), 11, 4) end as ER_QHPE_HOURLY,
    case when EMANHR = 0 then 0 else
    dec(ifnull(round(SHHOUR * ER_COST_ANN / EMANHR, 2), 0), 11, 2) end as ER_QHPE_PRO_RATA
from PRCKHS chk 
    join PRDTHS tran on (chk.EHPREF = tran.SHPREF)
    join HREMPL ee on (ee.EMCOMP = chk.EHCOMP and ee.EMFACL = chk.EHFACL and ee.EMEMPL = chk.EHEMPL)
    left outer join (
		Select 
		    EDCOMP as CO,
		    EDFACL as FACL,
		    EDEMPL as EMPL,
		    EMDEPT as DEPT,
		    EMRNAM as NAME,
		    EMANHR as STD_ANN_HRS,
		    EDDDNO as DED#,
		    HVDDNM as DEDNM,
		    CVCOVR as COVR,
		    CVPLAN as PLAN,
		    F_MAKEDATE(EDEFDT) as EFF_DATE,
		    F_MAKEDATE(EDEXDT) as EXP_DATE,
		    CVCSTM as ER_COST_MONTHLY,
		    dec(round((CVCSTM * 12),2),11,2) as ER_COST_ANN
		from HREDED ed
		    join HRDEDM dedm on (ed.EDDDNO = dedm.HVDDNO)
		    join CBCOVR covr on (covr.CVDEDN = dedm.HVDDNO and ed.EDCOMP = covr.CVCOMP and ed.EDFACL = covr.CVFACL)
		    join HREMPL ee on (ee.EMCOMP = ed.EDCOMP and ee.EMFACL = ed.EDFACL and ee.EMEMPL = ed.EDEMPL)
		    where HVDDNO in ({$FFCRA_QHPDeds})    
    ) qhpe on (qhpe.CO = chk.EHCOMP and qhpe.FACL = chk.EHFACL and qhpe.EMPL = chk.EHEMPL 
        and (F_MAKEDATE(SHDTWK) >= qhpe.EFF_DATE or qhpe.EFF_DATE is null)
        and (F_MAKEDATE(SHDTWK) <  qhpe.EXP_DATE or qhpe.EXP_DATE is null))
where SHCODE in ({$FFCRA_PayCodes}) 
";
    if ($wildCardSearch != "" && $appendWildCard != "N") {
        $stmtSQL .= $wildCardSearch;
    }
    
    $orderBy = (trim($orderBy) == '') ? 'QUARTER,LINE,SHCOMP,SHFACL,SHEMPL' : $orderBy;
    $stmtSQL .= " Order By $orderBy ";
    return $stmtSQL;
}

?>
