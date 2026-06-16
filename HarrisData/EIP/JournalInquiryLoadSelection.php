<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$backHome = $_GET['backHome'];
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

$page_title = "Load Journal Inquiry";
$scriptName = "JournalInquiryLoadSelection.php";
$scriptVarBase = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection = "Y";
$submitCallProgram = "HHDJIL";
$submitEnvProgram = "HHDJIL";
$submitEnvPrinter = "";
$submitScheduleScript = "";
$applicationID = "SY";
$programName = "JOURNALINQ";
$program_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $program_OPT['sec_01'];
if ($sec_01 == "N") {
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
    require_once 'NumEdit.php';
    require_once 'DateEdit.php';
    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.tbl.value ==\"\" ";
    print "\n    || document.Chg.days.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (editZero(document.Chg.days, 4, 0)) ";
    print "\n return true;";
    print "\n }";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";
    print "\n <body $bodyTagAttr>";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "LOADJOURNAL";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    print "\n <table $contentTable> ";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td> ";
    print "\n     <td class=\"toolbar\">";

    if ($backHome != $scriptName && $backHome != "" && $backHome != "@@backHome") {
        if (strpos(strtoupper($backHome), ".D2W") !== false) {print "<a href=\"{$homeURL}{$cGIPath}{$backHome}{$altVarBase}\" title=\"Back Home\">{$portalHome}</a>";}
        else {print "<a href=\"{$homeURL}{$phpPath}{$backHome}{$scriptVarBase}\" title=\"Back Home\">{$portalHome}</a>";}
    }
    print "\n <a href=\"javascript:check(document.Chg)\">$sbmSchdImage</a>";
    if ($allowScheduleJob != "N"){print "\n <a href=\"javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg)\">$sbmSchdParmImage</a>";}
    if ($submitNoReset=="") {print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;resetSelectionFlag=Y&amp;timeStamp=". urlencode($_SERVER['REQUEST_TIME']) . "\">$sbmSchdResetImage</a> ";}
    if (file_exists('R15.0_Journal_Inquiry.pdf')) {
        $designIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgHelp.gif\" title=\"View Journal Inquiry Design\" alt=\"Help\">";
        print "<a href=\"R15.0_Journal_Inquiry.pdf\" target=\"_blank\">{$designIcon}</a>";
    }
    require_once 'HelpPage.php';
    print "</td></tr></table>";

    require 'ConfMessageDisplayNoTable.php';
    print $hrTagAttr;

    $focusField = "tbl";
    if ($errFound != "" || $scheduleJobSwitch == "Y") {
        $scheduleJobSwitch = "";
        $focusField = "";
        $edtVar = EdtVarErr($profileHandle, $edtVar);
        if ($errFound != "") {
            $errVar = ErrVarErr($profileHandle, $errVar);
            $Err_TBL = DecatErr_Field("@@tbl@", "tbl");
            $Err_DAYS = DecatErr_Field("@@tdat", "days");
            require 'ScheduleJobErr.php'; // Schedule Entries Errors
        }
        $submitSchedule = Decat_Field("@@sbjb", $edtVar);

        $tbl = Decat_Field("@@tbl@", $edtVar);
        $days = Decat_Field("@@days", $edtVar);
        require 'ScheduleJobValue.php'; // Schedule Entries Values
    } else {
        $user = "";
        $pass = "";
        $days = "";
    }

    print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

    print "\n     <table $contentTable> ";

    Build_Fld_Entry("Table Name", "tbl", "inputalph", "", "", $tbl, $Err_TBL, "10", "10", "Y", "", "");
    Build_Fld_Entry("Number of Days", "days", "inputnmbr", "", "", $days, $Err_DAYS, "4", "4", "Y", "", "");
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
    Concat_Field("@@tbl@", strtoupper($_POST ['tbl']));
    Concat_Field("@@days", $_POST ['days']);
    require 'ScheduleJobConcat.php'; // Schedule Entries Values
    $edtVar .= "}{";

    $returnValue = Selection_Edit_Handle ( "HHDJIL_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar );
    $submitSchedule = $returnValue ['submitSchedule'];
    $errFound = $returnValue ['errFound'];
    $edtVar = $returnValue ['edtVar'];
    $errVar = $returnValue ['errVar'];
    $wrnVar = $returnValue ['wrnVar'];

    require 'SubmitScheduleUpdate.php';
    exit ();
}
?>