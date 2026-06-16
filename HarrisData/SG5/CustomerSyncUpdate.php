<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once('Zend/Loader/Autoloader.php');

$errFound = (isset ($_GET ['errFound'])) ? $_GET ['errFound'] : null;
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

$page_title = "Customer Synchronization Update";
$scriptName = "CustomerSyncUpdate.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$maintenanceCode = "";
$programName = "HHDCUP";
$program_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01 = $program_OPT['sec_01'];
$sec_02 = $program_OPT['sec_02'];
if ($sec_01 == "N" || $sec_02 == "N") {
    require_once 'ProgSecurityError.php';
    exit;
}

$backURL = $_SESSION [$fromURL];

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
    require_once 'NumEdit.php';
    print "\n function validate(chgForm) {";
    print "\n if (document.Chg.user.value ==\"\" ";
    print "\n    || document.Chg.password.value ==\"\" ";
    print "\n    || document.Chg.lastChange.value ==\"\" ";
    print "\n ) {alert(\"$reqFieldError\"); return false;} ";
    print "\n if (editdate(document.Chg.lastChange)) ";
    print "\n return true;";
    print "\n }";
    print "\n </script> \n";

    require_once($genericHead);
    print "\n </head>";
    print "\n <body $bodyTagAttr>";
    require_once 'Banner.php';
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "CUSTOMERSYNC";
    require_once 'MenuDisplay.php';
    print "\n <td class=\"content\">";
    print "\n <table $contentTable> ";
    print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
    print "\n <tr><td><h1>$page_title</h1></td> ";
    print "\n     <td class=\"toolbar\">";

    print "<a href=\"{$homeURL}{$phpPath}CustomerSync.php{$scriptVarBase}\" title=\"Back Home\">{$portalHome}</a>";
    print "\n <a href=\"javascript:check(document.Chg)\">$sbmSchdImage</a>";

    require_once 'HelpPage.php';
    print "</td></tr></table>";
    require_once 'ConfMessageDisplay.php';
    print $hrTagAttr;

    $focusField = "user";
    if ($errFound != "") {
        $scheduleJobSwitch = "";
        $focusField = "";
        $edtVar = EdtVarErr($profileHandle, $edtVar);
        if ($errFound != "") {
            $errVar = ErrVarErr($profileHandle, $errVar);
            $Err_USER = DecatErr_Field("@@user", "user");
            $Err_PASS = DecatErr_Field("@@pass", "password");
            $Err_LAST = DecatErr_Field("@@last", "lastChange");
            require 'ScheduleJobErr.php'; // Schedule Entries Errors
        }
        $submitSchedule = Decat_Field("@@sbjb", $edtVar);

        $user = Decat_Field("@@user", $edtVar);
        $pass = Decat_Field("@@pass", $edtVar);
        $lastChg = Decat_Field("@@last", $edtVar);
    } else {
        $user = "";
        $pass = "";
        $lastChg = "";
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

    // Last Change Date
    Build_Fld_Entry("Last Change Date", "lastChange", "inputdate", "Date", "", $lastChg, $Err_LAST, "6", "6", "Y", "", "");
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
    Concat_Field("@@last", $_POST ['lastChange']);
    $edtVar .= "}{";

    if (!loginAppsInHdERP($_POST ['user'], $_POST ['password'])) {
        $errVar = "}{@@userUser or Password are invalid}{";
        Concat_Field("@@user", "User, Password or AppsInHdERP URL are invalid");
        EdtVarErr($profileHandle, $edtVar);
        ErrVarErr($profileHandle, $errVar);
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;errFound=Y&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
        exit();
    }
    processSync($_POST ['lastChange']);

    $confMessage = "Confirm processing of Customer Synchronization";

    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}CustomerSync.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";

}

/**
 * Establishes authenticated session based on user/password/url in config
 *  Saves session object for later re-use
 *
 * @throws Exception                        When login fails
 */
function loginAppsInHdERP($user, $password, $lastChange)
{
    global $AppsInHdERP, $autoloader, $client;
    if ($autoloader == NULL) {
        $autoloader = Zend_Loader_Autoloader::getInstance();
    }

    $client = new Zend_Http_Client($AppsInHdERP, array(
        'timeout' => 60,
        'keepalive' => TRUE,
    ));
    $client->setCookieJar();
    $client->setUri($AppsInHdERP . '/login');
    $loginPost = '{"data":{"userid":"' . $user . '","password":"' . $password . '"}}';
    $response = $client->setRawData($loginPost, 'application/json')->request(Zend_Http_Client::POST);
    return (Zend_Http_Response::extractCode($response) == 200) ? true : false;
}

function processSync($lastChange)
{
    global $AppsInHdERP, $client, $edtVar, $homePath, $uploadDirectory, $dataBaseID, $userProfile, $maintenanceCode;
    $errDesc = '';

    $client->setUri($AppsInHdERP . '/Customer');
    $lastChangeISO = Date_MDY_ISO($lastChange);
    $params = '{"ACTION_METHOD":"customerSynchronization","LAST_CHANGE":"' . $lastChangeISO . '"}';
    $response = $client->setRawData($params, 'application/json')->request(Zend_Http_Client::POST);
    $results = json_decode($response->getBody(), true);

    $attachPath = "{$homePath}{$uploadDirectory}{$dataBaseID}/";
    if (!file_exists("$attachPath")) {
        exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$attachPath\")'");
    }
    $syncPath = "{$attachPath}CustomerSync/";
    if (!file_exists("$syncPath")) {
        exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$syncPath\")'");
    }
    $logPath = "{$syncPath}log/";
    if (!file_exists("$logPath")) {
        exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$logPath\")'");
    }
    $fileName = "Sync_log_" . $lastChangeISO . ' ' . i5_get_system_value("QTIME") . ".csv";

    $file = fopen($logPath . $fileName, 'w');
    if (!$file) {
        throw new Exception('Failed to open file ' . $logPath . $fileName);
    }

    ob_end_clean();

    $csvHdr = ['Action', 'Customer Name', 'Number', 'ERP ID', 'Last Updated', 'Message'];
    fputcsv($file, $csvHdr);
    $errCnt = 0;
    if (isset($results['data'])) {
        foreach ($results['data'] as $key => $data) {
            $cust = Get_Customer_Number($data['CUSTOMER_ID'], $data['ALTERNATE_KEY']);

            $edtVar = "";
            Concat_Field("@@mncd", $maintenanceCode);
            Concat_Field("@@cust", $cust);
            $blto = $data['BILLING_CUSTOMER_ID'];
            $blto = RetValue("EXID=$blto", "HDCERP", "coalesce(EXCUST,0)");
            Concat_Field("@@blto", $blto);
            Concat_Field("@@cna1", $data['NAME']);
            Concat_Field("@@cna2", $data['ADDRESS_ID__ADDRESS_LINE_1']);
            Concat_Field("@@cna3", $data['ADDRESS_ID__ADDRESS_LINE_2']);
            Concat_Field("@@cna4", $data['ADDRESS_ID__ADDRESS_LINE_3']);
            Concat_Field("@@ccty", $data['ADDRESS_ID__CITY']);
            Concat_Field("@@st@@", $data['ADDRESS_ID__ISO_SUBDIVISION_CODE']);
            Concat_Field("@@zip@", $data['ADDRESS_ID__POSTAL_CODE']);
            Concat_Field("@@ctry", $data['ADDRESS_ID__ISO_COUNTRY_CODE3']);
            Concat_Field("@@ccls", substr($data['CUSTOMER_CLASS_ID_ALTERNATE_KEY'] , 0 , 2));
            $loc = substr($data['BILLING_LOCATION_ID_ALTERNATE_KEY'] , 0 , 3);
            $loc = (is_numeric($loc)) ? $loc : '';
            Concat_Field("@@loc@", $loc);
            Concat_Field("@@phon", '');
            Concat_Field("@@ctrm", substr($data['TERMS_ID_ALTERNATE_KEY'] , 0 , 2));
            Concat_Field("@@crct", '');
            Concat_Field("@@ccrl", $data['CREDIT_LIMIT']);
            Concat_Field("@@curt", substr($data['CURRENCY_TYPE_ID_ALTERNATE_KEY'] , 0 , 3));
            $cstc = ($data['STATEMENT']) ? 'Y' : 'N';
            Concat_Field("@@cstc", $cstc);
            $cscc = ($data['SERVICE_CHARGE']) ? 'Y' : 'N';
            Concat_Field("@@cscc", $cscc);
            $slsm = substr($data['BILLING_LOCATION_ID_ALTERNATE_KEY'] , 0 , 3);
            $slsm = (is_numeric($slsm)) ? $slsm : '';
            Concat_Field("@@slsm", $slsm);
            Concat_Field("@@crgn", substr($data['REGION_ID_ALTERNATE_KEY'] , 0 , 5));
            Concat_Field("@@fax@", '');
            Concat_Field("@@hcbl", 0);
            $dfes = Date_FromISO_ToCYMD($data ['DATE_ESTABLISHED']);
            Concat_Field("@@dfes", $dfes);
            Concat_Field("@@mpty", $data['MANAGEMENT_PRIORITY']);
            Concat_Field("@@udf1", $data['UD_CHAR_ONE']);
            Concat_Field("@@udf2", $data['UD_CHAR_TWO']);
            Concat_Field("@@udf3", $data['UD_CHAR_THREE']);
            Concat_Field("@@udf4", $data['UD_CHAR_FOUR']);
            Concat_Field("@@udf5", $data['UD_CHAR_FIVE']);
            $ibpk = ($data['INVOICE_BY_PACKING_LIST']) ? 'Y' : 'N';
            Concat_Field("@@ibpk", $ibpk);
            Concat_Field("@@strq", 'N');
            Concat_Field("@@orty", substr($data['DEFAULT_ORDER_TYPE_ID_ALTERNATE_KEY'] , 0 , 1));
            $boal = ($data['ALLOW_BACKORDERS']) ? 'Y' : 'N';
            Concat_Field("@@boal", $boal);
            Concat_Field("@@sv@@", substr($data['SHIP_VIA_ID_ALTERNATE_KEY'] , 0 , 2));
            Concat_Field("@@ctxc", substr($data['TAX_CODE_ID_ALTERNATE_KEY'] , 0 , 1));
            Concat_Field("@@bcde", substr($data['BILLING_CODE_ID_ALTERNATE_KEY'] , 0 , 4));
            Concat_Field("@@imth", $data['INVOICING_METHOD']);
            Concat_Field("@@idos", $data['PRINT_SUMMARY']);
            $rref = ($data['REFERENCE_REQUIRED']) ? 'Y' : 'N';
            Concat_Field("@@rref", $rref);
            Concat_Field("@@moq@", $data['MINIMUM_QUANTITY']);
            Concat_Field("@@moa@", $data['MINIMUM_VALUE']);
            Concat_Field("@@mow@", $data['MINIMUM_WEIGHT']);
            $mwrn = ($data['MINIMUM_WARNING']) ? 'Y' : 'N';
            Concat_Field("@@mwrn", $mwrn);
            $acci = ($data['CREATE_ITEM']) ? 'Y' : 'N';
            Concat_Field("@@acci", $acci);
            $aovs = ($data['OVER_SHIPMENTS']) ? 'Y' : 'N';
            Concat_Field("@@aovs", $aovs);
            Concat_Field("@@trdy", $data['TRANSIT_DAYS']);

            $edtVar .= "}{";

            $returnValue = Synchronize_Customer($edtVar);
            $edtVar = $returnValue['edtVar'];
            $emsg = Decat_Field("@@emsg", $edtVar);
            $mncd = Decat_Field("@@mncd", $edtVar);
            $action = ($mncd == 'C') ? 'Updated' : 'Added';
            $dtlCsv = [$action, $data ['NAME'], $cust, $data['CUSTOMER_ID'], $data['LAST_UPDATED'], $emsg];

            fputcsv($file, $dtlCsv);
        }
    }

    fclose($file);

    $errDesc = ($errCnt > 0) ? ' (' . $errCnt . ' errors)' : $errDesc;
    $edtVar = "";
    Concat_Field("@@fold", "CUSTOMERSYNC");
    Concat_Field("@@vkey", "log");
    Concat_Field("@@desc", $lastChangeISO . $errDesc);
    Concat_Field("@@atnl", "{$uploadDirectory}{$dataBaseID}/CustomerSync/log/{$fileName}");
    Concat_Field("@@atns", $fileName);
    Concat_Field("@@user", $userProfile);
    Concat_Field("@@body", "");
    Concat_Field("@@dirl", "");
    Concat_Field("@@priv", "");
    Concat_Field("@@repl", "Y");
    Concat_Field("@@prg1", "");
    Concat_Field("@@prg2", "");
    Concat_Field("@@prg3", "");
    Concat_Field("@@prg4", "");
    Concat_Field("@@prg5", "");
    $edtVar .= "}{";

    $profileHandle = "";
    $maintenanceCode = "A";
    $errFound = "";
    $errVar = "";
    $wrnVar = "";
    $returnValue = Maintain_Edit("HSYATM_W", $profileHandle, $dataBaseID, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);

    return;
}

// Maintenance Edit
function Synchronize_Customer($edtVar, $maintenanceCode)
{
    global $pgmLibrary, $i5Connect, $userProfile;
    $pgmName = "HHDCUP_ERP";

    $errFound = '';
    $errVar = '';
    $wrnVar = '';
    $userEdtVar = '';

    $pgmCall = array(
        array("Name" => "userProfile", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "10"),
        array("Name" => "edtVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"));

    $pgm = i5_program_prepare("$pgmName", $pgmCall);
    if (!$pgm) {
        die("<br>Validate_Data ($pgmName) prepare error. Error Number=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = array(
        "userProfile" => $userProfile,
        "edtVar" => $edtVar);

    $parmOut = array(
        "userProfile" => "userProfile",
        "edtVar" => "edtVar");

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br>Validate_Data ($pgmName) call errno=" . i5_errno() . " msg=" . i5_errormsg());
    }
    $returnValue['edtVar'] = $edtVar;
    return $returnValue;
}


/**
 * @param $id
 * @param $alternateKey
 */
function Get_Customer_Number($id, $alternateKey = '')
{
    global $i5Connect, $maintenanceCode;
    $maintenanceCode = 'A';
    $nextCust = 0;
    $alternateKey = trim($alternateKey);
    $cust = RetValue("EXID=$id", "HDCERP", "coalesce(EXCUST,0)");
    if ($cust > 0) {
        $maintenanceCode = 'C';
        return $cust;
    }
    if ($alternateKey != '') {
        $nextCust = RetValue("CMCUST=$alternateKey", "HDCUST", "coalesce(CMCUST,0)");
    }
    if ($nextCust > 0) {
        $maintenanceCode = 'C';
    } else {
        $nextCust = RetValue("CMCUST>0", "HDCUST", "dec(max(coalesce(CMCUST,0))+1,7,0)");
    }
    $stmtSQL = " Insert Into HDCERP (EXID,EXCUST) Values($id,$nextCust) ";
    $row = db2_exec($i5Connect->getConnection(), $stmtSQL);
    return $nextCust;
}

?>