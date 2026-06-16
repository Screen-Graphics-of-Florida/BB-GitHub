<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound = (isset ($_GET ['errFound'])) ? $_GET ['errFound'] : null;
$jobSbmSched = (isset ( $_GET ['jobSbmSched'] )) ? $_GET ['jobSbmSched'] : null;
$resetSelectionFlag = (isset ( $_GET ['resetSelectionFlag'] )) ? $_GET ['resetSelectionFlag'] : null;
$rtvSelection = (isset ( $_GET ['rtvSelection'] )) ? $_GET ['rtvSelection'] : null;
$saveSelection = (isset ( $_GET ['saveSelection'] )) ? $_GET ['saveSelection'] : null;
$scheduleJobSwitch = (isset ( $_GET ['scheduleJobSwitch'] )) ? $_GET ['scheduleJobSwitch'] : null;
$selScheduleJob = (isset ( $_GET ['selScheduleJob'] )) ? $_GET ['selScheduleJob'] : null;
$submitSchedule = (isset ( $_GET ['submitSchedule'] )) ? $_GET ['submitSchedule'] : null;
$autoloader = null;
$client = null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Washington Leave Selection";
$scriptName = "WashingtonLeaveSelection.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection = "";
$submitCallProgram = "HPRWAL";
$submitEnvProgram = "HPRWAL";
$submitEnvPrinter = "";
$submitScheduleScript = "WashingtonLeave.php";
$applicationID = "ET";
$programName = "WALEAVE";
$program_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $program_OPT['sec_01'];
$sec_02 = $program_OPT['sec_02'];
if ($sec_01 == "N" || $sec_02 == "N") {
    require_once 'ProgSecurityError.php';
    exit;
}

if (is_null($tag)) {
    $tag = "REPORT";
}

if ($tag == "REPORT") {
    require_once($docType);
    print "\n <html> <head>";
    require_once($headInclude);
    $formName = "Chg";
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'Menu.js';
    require_once 'CalendarInclude.php';
    require_once 'CheckEnterChg.php';
    require_once 'DateEdit.php';
    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.qtr.value ==\"\" ";
    print "\n    || document.Chg.year.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (document.Chg.qtr.value !=\"1\" ";
    print "\n    && document.Chg.qtr.value !=\"2\" ";
    print "\n    && document.Chg.qtr.value !=\"3\" ";
    print "\n    && document.Chg.qtr.value !=\"4\" ";
    print "\n ) {alert(\"Quarter must be 1,2,3 or 4\"); return false;} ";
    print "\n if (document.Chg.year.value.length !=\"4\" ";
    print "\n ) {alert(\"Please enter a valid 4 digit year.\"); return false;} ";
    print "\n return true;";
    print "\n }";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";
    print "\n <body $bodyTagAttr>";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "WASHINGTONLEAVE";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    print "\n <table $contentTable> ";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td> ";
    print "\n     <td class=\"toolbar\">";

    print "<a href=\"{$homeURL}{$phpPath}WashingtonLeave.php{$scriptVarBase}\" title=\"Back Home\">{$portalHome}</a>";
    print "\n <a href=\"javascript:check(document.Chg)\">$sbmSchdImage</a>";
    if ($allowScheduleJob != "N") {
        print "\n <a href=\"javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg)\">$sbmSchdParmImage</a>";
    }

    require_once 'HelpPage.php';
    print "</td></tr></table>";
    require_once 'ConfMessageDisplay.php';
    print $hrTagAttr;

    $focusField = "qtr";
    if ($errFound != "" || $scheduleJobSwitch == "Y") {
        $scheduleJobSwitch = "";
        $focusField = "";
        $edtVar = EdtVarErr($profileHandle, $edtVar);
        if ($errFound != "") {
            $errVar = ErrVarErr($profileHandle, $errVar);
            $Err_QTR = DecatErr_Field("@@qtr@", "qtr");
            $Err_YEAR = DecatErr_Field("@@tdat", "year");
            require 'ScheduleJobErr.php'; // Schedule Entries Errors
        }
        $submitSchedule = Decat_Field("@@sbjb", $edtVar);

        $qtr = Decat_Field("@@qtr@", $edtVar);
        $year = Decat_Field("@@year", $edtVar);
        require 'ScheduleJobValue.php'; // Schedule Entries Values
    } else {
        $user = "";
        $pass = "";
        $days = "";
    }

    print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

    print "\n     <table $contentTable> ";

    Build_Fld_Entry("Quarter", "qtr", "inputnmbr", "", "", $qtr, $Err_QTR, "1", "1", "Y", "", "");
    Build_Fld_Entry("Year", "year", "inputnmbr", "", "", $year, $Err_YEAR, "4", "4", "Y", "", "");
    print "\n     </table> ";

    $envProgram = $submitEnvProgram;
    $envPrinter = $submitEnvPrinter;
    require 'ScheduleJob.php';
    print "\n $hrTagAttr ";

    if ($focusField != "") {
        print "\n <script TYPE=\"text/javascript\"> ";
        print "\n document.Chg.$focusField.focus(); ";
        print "\n </script> ";
    }
    print "\n </form>";
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    require_once 'Trailer.php';
    print "</body> </html>";
    exit ();
}

if ($tag == "Edit_Data") {

    $edtVar = "";
    Concat_Field("@@qtr@", $_POST ['qtr']);
    Concat_Field("@@year", $_POST ['year']);
    require 'ScheduleJobConcat.php'; // Schedule Entries Values
    $edtVar .= "}{";

    $returnValue = Selection_Edit_Handle ( "HPRWAL_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar );
    $submitSchedule = $returnValue ['submitSchedule'];
    $errFound = $returnValue ['errFound'];
    $edtVar = $returnValue ['edtVar'];
    $errVar = $returnValue ['errVar'];
    $wrnVar = $returnValue ['wrnVar'];

    require 'SubmitScheduleUpdate.php';
    exit ();

    $confMessage="Confirm processing of Washington Leave";

    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}WashingtonLeave.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";

}
?>