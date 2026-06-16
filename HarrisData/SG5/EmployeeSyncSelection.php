<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once('Zend/Loader/Autoloader.php');

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

$page_title = "Employee Synchronization Selection";
$scriptName = "EmployeeSyncSelection.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection = "";
$submitCallProgram = "HETESP";
$submitEnvProgram = "HETESP";
$submitEnvPrinter = "";
$submitScheduleScript = "EmployeeSync.php";
$applicationID = "ET";
$programName = "HETEMU";
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
    print "\n if (document.Chg.user.value ==\"\" ";
    print "\n    || document.Chg.password.value ==\"\" ";
    print "\n    || document.Chg.days.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
    print "\n return true;";
    print "\n }";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";
    print "\n <body $bodyTagAttr>";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "EMPLOYEESYNC";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    print "\n <table $contentTable> ";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td> ";
    print "\n     <td class=\"toolbar\">";

    print "<a href=\"{$homeURL}{$phpPath}EmployeeSync.php{$scriptVarBase}\" title=\"Back Home\">{$portalHome}</a>";
    print "\n <a href=\"javascript:check(document.Chg)\">$sbmSchdImage</a>";
    if ($allowScheduleJob != "N") {
        print "\n <a href=\"javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg)\">$sbmSchdParmImage</a>";
    }

    require_once 'HelpPage.php';
    print "</td></tr></table>";
    require_once 'ConfMessageDisplay.php';
    print $hrTagAttr;

    $focusField = "user";
    if ($errFound != "" || $scheduleJobSwitch == "Y") {
        $scheduleJobSwitch = "";
        $focusField = "";
        $edtVar = EdtVarErr($profileHandle, $edtVar);
        if ($errFound != "") {
            $errVar = ErrVarErr($profileHandle, $errVar);
            $Err_USER = DecatErr_Field("@@user", "user");
            $Err_PASS = DecatErr_Field("@@pass", "password");
            $Err_LAST = DecatErr_Field("@@days", "days");
            require 'ScheduleJobErr.php'; // Schedule Entries Errors
        }
        $submitSchedule = Decat_Field("@@sbjb", $edtVar);

        $user = Decat_Field("@@user", $edtVar);
        $pass = Decat_Field("@@pass", $edtVar);
        $days = Decat_Field("@@days", $edtVar);
        require 'ScheduleJobValue.php'; // Schedule Entries Values
    } else {
        $user = "";
        $pass = "";
        $days = "";
    }

    print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

    print "\n     <table $contentTable> ";

    // User
    Build_Fld_Entry("User", "user", "inputalph", "", "", $user, $Err_USER, "20", "128", "Y", "", "");

    // Password
    $textOvr = SetTextOvr($Err_PASS);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Password</span></td> ";
    print "\n     <td class=\"inputnmbr\"><input type=\"password\"   name=\"password\" value=\"" . rtrim($pass) . "\" size=\"20\" maxlength=\"128\">$reqFieldChar";
    print "\n </tr> ";
    DspErrMsg($Err_PASS);

    // Days 
    $textOvr = SetTextOvr($Err_DAYS);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Days Since Last Change</span></td> ";
    print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"days\" value=\"" . rtrim($days) . "\" size=\"3\" maxlength=\"3\">$reqFieldChar</td>";
    print "\n </tr> ";
    DspErrMsg($Err_DAYS);
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
    Concat_Field("@@user", $_POST ['user']);
    Concat_Field("@@pass", $_POST ['password']);
    Concat_Field("@@days", $_POST ['days']);

    if (!loginAppsInHd($_POST ['user'], $_POST ['password'])) {

        $errVar = "}{@@userUser or Password are invalid}{";
        Concat_Field("@@user", "User, Password or AppsInHd URL are invalid");
        $edtVar .= "}{";
        EdtVarErr($profileHandle, $edtVar);
        ErrVarErr($profileHandle, $errVar);
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;errFound=Y&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
        exit();
    }
    if ($errFound == "" && $_POST ['selScheduleJob'] == "") {
        $eUser = encrypt_decrypt('encrypt', $_POST ['user']);
        Concat_Field("@@eusr", $eUser);
        $ePass = encrypt_decrypt('encrypt', $_POST ['password']);
        Concat_Field("@@epas", $ePass);
    }
    require 'ScheduleJobConcat.php'; // Schedule Entries Values
    $edtVar .= "}{";

    $returnValue = Selection_Edit_Handle ( "HETESP_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar );
    $submitSchedule = $returnValue ['submitSchedule'];
    $errFound = $returnValue ['errFound'];
    $edtVar = $returnValue ['edtVar'];
    $errVar = $returnValue ['errVar'];
    $wrnVar = $returnValue ['wrnVar'];

    require 'SubmitScheduleUpdate.php';
    exit ();

    $confMessage="Confirm processing of Employee Synchronization";

    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}EmployeeSync.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";

}

/**
 * Establishes authenticated session based on user/password/url in config
 *  Saves session object for later re-use
 *
 * @throws Exception                        When login fails
 */
function loginAppsInHd($user, $password)
{
    global $AppsInHd, $autoloader, $client;
    if ($autoloader == NULL) {
        $autoloader = Zend_Loader_Autoloader::getInstance();
    }
    $config = array(
        'adapter'     => 'Zend_Http_Client_Adapter_Curl',
        'curloptions' => [CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false]
    );
    $client = new Zend_Http_Client($AppsInHd, $config);
    $client->setCookieJar();
    $client->setUri($AppsInHd . '/login');
    $loginPost = '{"data":{"userid":"' . $user . '","password":"' . $password . '"}}';
    $response = $client->setRawData($loginPost, 'application/json')->request(Zend_Http_Client::POST);
    return (Zend_Http_Response::extractCode($response) == 200) ? true : false;
}

?>