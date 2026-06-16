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

$errFound = (isset ($_GET ['errFound'])) ? $_GET ['errFound'] : null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$page_title = "Washington Leave Update";
$scriptName = "WashingtonLeaveUpdate.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

$user = $Arg[2];
$quarter = $Arg[3];
$year = $Arg[4];

$attachPath = "{$homePath}{$uploadDirectory}{$dataBaseID}/";
if (!file_exists("$attachPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$attachPath\")'");
}
$folderPath = "{$attachPath}WashingtonLeave/";
if (!file_exists("$folderPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$folderPath\")'");
}
$logPath = "{$folderPath}log/";
if (!file_exists("$logPath")) {
    exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$logPath\")'");
}

$yr = substr($year, 2, 2);
switch ($quarter) {
    case 1:
        $fmd = '0101';
        $tmd = '0331';
        break;
    case 2:
        $fmd = '0401';
        $tmd = '0630';
        break;
    case 3:
        $fmd = '0701';
        $tmd = '0930';
        break;
    case 4:
        $fmd = '1001';
        $tmd = '1231';
        break;
}
$from = "1" . $yr . $fmd;
$to = "1" . $yr . $tmd;
$stmtSQL = "Select YDEIN, EMEMID, EMLNAM, EMFNAM, EMMIDI, 
            ceiling(sum(EHREGH + EHVACH + EHHOLH + EHSICH + EHOVTH + EHDBLH + EHOTHH)) as HOURS, sum(EHRSP) as WAGES 
            From HREMPL join PRCKHS on EHCOMP=EMCOMP and EHFACL=EMFACL and EHEMPL=EMEMPL 
                        join PRYDFL on YDCOMP=EMCOMP and YDFACL=EMFACL and YDEIN>0
            Where EHCKDT between " . $from . " and " . $to . " and EMSTAT='WA'
            Group By YDEIN, EMCOMP, EMFACL, EMEMPL, EMLNAM, EMFNAM, EMMIDI, EMEMID 
            Order By YDEIN, EMCOMP, EMFACL, EMEMPL";
$result = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

$nextRow = 1;
$saveEIN = null;
while ($row = db2_fetch_assoc($result, $nextRow)) {
    if (is_null($saveEIN) || $saveEIN != $row[YDEIN]) {
        if (!is_null($saveEIN)) {
            fclose($file);
            Add_Attach();
        }
        $saveEIN = $row[YDEIN];
        $fileName = "Washington Leave " . $year . " Q" . $quarter . " " . $row[YDEIN] . " " . i5_get_system_value("QTIME") . ".csv";
        $file = fopen($logPath . $fileName, 'w');
        if (!$file) {
            throw new Exception('Failed to open file ' . $logPath . $fileName);
        }
        ob_end_clean();
    }
    $ssn = RetColValue($profileHandle, $dataBaseID, "EMEMID=$row[EMEMID]", "HREMPL", "EMSSNO", "D");
    $line = [$ssn, trim($row[EMLNAM]), trim($row[EMFNAM]), trim($row[EMMIDI]), $row[HOURS], $row[WAGES]];
    fputcsv($file, $line);
    $nextRow++;
}

if (!is_null($saveEIN)) {
    fclose($file);
    Add_Attach();
}

function Add_Attach()
{
    global $edtVar, $year, $quarter, $uploadDirectory, $dataBaseID, $fileName, $user, $saveEIN;
    $edtVar = "";
    Concat_Field("@@fold", "WASHINGTONLEAVE");
    Concat_Field("@@vkey", "log");
    Concat_Field("@@desc", $year . ' Q' . $quarter . ' ' . $saveEIN);
    Concat_Field("@@atnl", "{$uploadDirectory}{$dataBaseID}/WashingtonLeave/log/{$fileName}");
    Concat_Field("@@atns", $fileName);
    Concat_Field("@@user", $user);
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
    Maintain_Edit("HSYATM_W", $profileHandle, $dataBaseID, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
}

return;

?>