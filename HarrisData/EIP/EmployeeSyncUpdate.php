<?php
ini_set('display_errors', 0);
ini_set('log_errors', 0);
ini_set('report_memleaks', 0);
chdir(dirname($_SERVER['argv'][0]));

$Arg = explode("::", $_SERVER['argv'][1]);
$_GET['baseVar'] = $Arg[0];
$_SERVER['PHP_AUTH_PW'] = $Arg[1];
$_SERVER['PHP_AUTH_USER'] = "HDS";

require_once 'GetURLParm.php';
require_once('Zend/Loader/Autoloader.php');

$errFound = (isset ($_GET ['errFound'])) ? $_GET ['errFound'] : null;
$autoloader = null;
$client = null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$page_title = "Employee Synchronization Update";
$scriptName = "EmployeeSyncUpdate.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

if ($autoloader == NULL) {
    $autoloader = Zend_Loader_Autoloader::getInstance();
}

$user = encrypt_decrypt('decrypt', $Arg[2]);
$hd5User = $Arg[3];
$password = encrypt_decrypt('decrypt', $Arg[4]);
$days = $Arg[5];

$config = array(
    'adapter' => 'Zend_Http_Client_Adapter_Curl',
    'curloptions' => [CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_TIMEOUT => 3000]
);
$client = new Zend_Http_Client($AppsInHd, $config);
$client->setCookieJar();
$client->setUri($AppsInHd . '/login');
$loginPost = '{"data":{"userid":"' . $user . '","password":"' . $password . '"}}';
$response = $client->setRawData($loginPost, 'application/json')->request(Zend_Http_Client::POST);
if (Zend_Http_Response::extractCode($response) != 200) {
    exit();
}

$errDesc = '';
$client->setUri($AppsInHd . '/Employee');
$stmtSQL = " Select current_timestamp - " . $days . " days as CHGDATE From SYSIBM.SYSDUMMY1 ";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

$lastChangeISO = substr($row['CHGDATE'], 0, 10);
$params = '{"ACTION_METHOD":"employeeSynchronization","LAST_CHANGE":"' . $lastChangeISO . '"}';
$response = $client->setRawData($params, 'application/json')->request(Zend_Http_Client::POST);
$results = json_decode($response->getBody(), true);

$attachPath = "{$homePath}{$uploadDirectory}{$dataBaseID}/";
if (!file_exists("$attachPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$attachPath\")'");
}
$acaPath = "{$attachPath}EmployeeSync/";
if (!file_exists("$acaPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$acaPath\")'");
}
$logPath = "{$acaPath}log/";
if (!file_exists("$logPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$logPath\")'");
}
$fileName = "Sync_log_" . $lastChangeISO . ' ' . i5_get_system_value("QTIME") . ".csv";

$file = fopen($logPath . $fileName, 'w');
if (!$file) {
    throw new Exception('Failed to open file ' . $logPath . $fileName);
}

ob_end_clean();

$csvHdr = ['Action', 'Last Name', 'First Name', 'Report Name', 'Co', 'Fac', 'Employee',
    'Clock', 'Shift', 'Pay Type', 'Termination Date', 'Error Message'];
fputcsv($file, $csvHdr);
$errCnt = 0;
if (isset($results['data'])) {
    foreach ($results['data'] as $key => $data) {
        $edtVar = "";
        if (is_null($data ['CLOCK_NUMBER'])) {
            $data ['CLOCK_NUMBER'] = '';
        }
        Concat_Field("@@clck", $data ['CLOCK_NUMBER']);
        Concat_Field("@@rnam", $data ['NAME_ID__LEGAL_NAME']);
        Concat_Field("@@fnam", $data ['NAME_ID__GIVEN_NAME']);
        Concat_Field("@@lnam", $data ['NAME_ID__FAMILY_NAME']);
        Concat_Field("@@prfx", $data ['USER_DEFINED_CHAR_01']);
        Concat_Field("@@stat", $data ['EMPLOYEE_STATUS_ID__EMPLOYEE_STATUS']);
        Concat_Field("@@payt", $data ['DERIVED_WAGE_TYPE']);
        $term = ($data ['EMPLOYEE_STATUS_ID__EMPLOYEE_STATUS'] == 'TERM')
            ? Date_FromISO_ToCYMD($data ['EMPLOYEE_STATUS_ID__TERMINATION_DATE']) : 0;
        Concat_Field("@@term", $term);
        Concat_Field("@@schd", $data ['SHIFT_ALTERNATE_KEY']);
        $edtVar .= "}{";

        $returnValue = Synchronize_Employee($edtVar);
        $edtVar = $returnValue['edtVar'];

        $emsg = Decat_Field("@@emsg", $edtVar);
        $mncd = Decat_Field("@@mncd", $edtVar);
        $action = ($mncd == 'C') ? 'Updated' : 'Added';
        $co = Decat_Field("@@co@@", $edtVar);
        $fac = Decat_Field("@@fac@", $edtVar);
        $empl = Decat_Field("@@empl", $edtVar);

        if ($mncd == 'A' || $mncd == 'C') {
            $dtlCsv = [$action, $data ['NAME_ID__FAMILY_NAME'], $data ['NAME_ID__GIVEN_NAME'],
                $data ['NAME_ID__LEGAL_NAME'], $co, $fac, $empl, $data ['CLOCK_NUMBER'],
                $data ['SHIFT_ALTERNATE_KEY'], $data ['DERIVED_WAGE_TYPE'],
                $term, $emsg];
        } else {
            $dtlCsv = ["Error", $data ['NAME_ID__FAMILY_NAME'], $data ['NAME_ID__GIVEN_NAME'],
                $data ['NAME_ID__LEGAL_NAME'], $co, $fac, '', $data ['CLOCK_NUMBER'],
                $data ['SHIFT_ALTERNATE_KEY'], $data ['DERIVED_WAGE_TYPE'],
                $term, $emsg];
        }
        if (trim($emsg) !='') $errCnt++;
        fputcsv($file, $dtlCsv);
    }
}

fclose($file);

$errDesc = ($errCnt > 0) ? ' (' . $errCnt . ' errors)' : $errDesc;
$edtVar = "";
Concat_Field("@@fold", "EMPLOYEESYNC");
Concat_Field("@@vkey", "log");
Concat_Field("@@desc", $lastChangeISO . $errDesc);
Concat_Field("@@atnl", "{$uploadDirectory}{$dataBaseID}/EmployeeSync/log/{$fileName}");
Concat_Field("@@atns", $fileName);
Concat_Field("@@user", $hd5User);
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

// Maintenance Edit
function Synchronize_Employee($edtVar)
{
    global $pgmLibrary, $i5Connect, $userProfile;
    $pgmName = "HETESY_W";

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

?>