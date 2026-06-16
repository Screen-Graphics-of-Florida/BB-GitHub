<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
if ($tag == 'Edit_Data') {
    $est = implode(",", $_POST[selEst]);
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}EEO1ReportCreateFileComp1.php{$genericVarBase}&amp;fromRPID={$est}\"> ";
    exit();
}
$fromRPID = $_GET ['fromRPID'];
$fromDate = RetValue("RPRPID={$fromRPID}", "PEEORP", "RPFRDT");
$from = substr($fromDate,0,4 ) . '-10-01';
$to = substr($fromDate,0,4 ) . '-12-31';

$page_title = "EEO-1 Report Generate File Selection";
$scriptName = "EEO1MultEstSelection.php";
$scriptVarBase = "{$genericVarBase}&amp;fromRPID=" . urlencode(trim($fromRPID));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=514";

require_once($docType);
print "\n <html> <head>";
require_once($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'Menu.js';
require_once 'CheckEnterChg.php';

print "\n function validate(chgForm) {";
print "\n if (document.Chg.selEst.value == \"\") {";
print "\n alert(\"You must select at least one EEO-1 Report\");";
print "\n return false}";
print "\n return true;";
print "\n }";
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "EEO1MULTESTSELECTION";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n     <td class=\"toolbar\">";
print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";
print "\n </td></tr></table>";

print "\n <table $contentTable> ";
print $hrTagAttr;

$stmtSQL = "Select RPRPID,RPDESC From PEEORP Where RPSTAT > 1 and RPFRDT>='{$from}' and RPTODT<='{$to}' Order By RPRPID";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
$startRow = 1;
print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
print "\n <tr><td class=\"colHdr\">Select EEO-1 Reports to include</td></tr>";
print "\n     <td><SELECT id=\"selEst\" NAME=\"selEst[]\" SIZE=\"9\" MULTIPLE> ";
while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
    if (strpos($V_J_JDAY, $FLDESC) !== false) {
        $schOption = "SELECTED";
    } else {
        $schOption = "";
    }
    print "\n <OPTION $schOption value=\"" . rtrim($row[RPRPID]) . "\">$row[RPDESC] ";
    $startRow++;
}
print "\n </SELECT></td> ";
print "\n </tr> ";

print "\n </table> ";
print "\n </form>";
print $hrTagAttr;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";

?>