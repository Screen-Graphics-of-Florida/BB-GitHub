<?php

if (($errFound == "" && $saveSelection == "Y") || $rtvSelection == "Y") {
    $errFound = "Y";
    EdtVarErr($profileHandle, $edtVar);
    ErrVarErr($profileHandle, $typeReset);
    $_SESSION['saveSel']="Y";
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;errFound=" . urlencode($errFound) . "&amp;wrnVar=" . urlencode($wrnVar) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . "\"> ";
    print "\n <script TYPE=\"text/javascript\"> ";
    require_once 'NewWindowOpen.php';
    $wrkVar  = str_replace("amp;", "&", $altVarBase);
    print "\n NewWindow('{$homeURL}{$cGIPath}ReportSelection.d2w/REPORT{$wrkVar}&reportSelType=" . urlencode(trim($reportSelType)) . "&reportSelD2W=" . urlencode($scriptName) . "&reportDesc=" . urlencode($page_title) . "&reportSelUser=" . urlencode($userProfile) . "&rtvSelection=" . urlencode($rtvSelection) . "&maintenanceCode=C','search_win','$searchWinPctH','$searchWinPctW','$searchWinSB','$searchWinRZ','$searchWinTB','$searchWinMB','$searchWinST'); ";
    print "\n </script> ";

} elseif ($errFound == "" && $saveSelection != "S") {
    EdtVarErr($profileHandle, $edtVar);
    ErrVarErr($profileHandle, $typeReset);
    if ($submitSchedule != "S") {$submitSchedule = "M";}

    require 'SubmitScheduleMessage.php';
    if ($submitScheduleScript == "FilterSelection.php") {
        print "\n <script TYPE=\"text/javascript\">";
	print "\n window.opener.receiveConfMessage(\"$confMessage\");";
	print "\n window.opener.focus();";
	print "\n window.close();";
        print "\n </script> ";
    } elseif ($submitScheduleScript != "") {
        print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$submitScheduleScript}{$scriptVarBase}&amp;tag=REPORT&amp;confMessage=". urlencode($confMessage) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . "\"> ";
    } else {
        print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;jobSbmSched=Y&amp;scheduleJobSwitch=Y&amp;confMessage=". urlencode($confMessage) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . "\"> ";
    }

} else {
    if ($saveSelection == "S" && $errFound == "") {ErrVarErr($profileHandle, $typeReset);}

    EdtVarErr($profileHandle, $edtVar);
    ErrVarErr($profileHandle, $errVar);
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL=${$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;errFound=" . urlencode($errFound) . "&amp;wrnVar=" . urlencode($wrnVar) . "&amp;scheduleJobSwitch=Y&amp;timeStamp=" .  urlencode($_SERVER['REQUEST_TIME']) . "\"> ";
}
?>