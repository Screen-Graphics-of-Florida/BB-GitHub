<?php

require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'SetLibraryList.php';
require_once 'GetURLParm.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Messages for $profileName [$userProfile]";
$scriptName = "UserMessage.php";
$scriptVarBase = $genericVarBase;
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

$msgKey = '';
if ($tag == "DELETE") {
    $msgKey = $_GET["msgKey"];
} elseif ($tag == "DELETEALL") {
    $msgKey = 'ALL';
}
$edtVar = GetMessages($userProfile, $msgKey);
$errorMessage = Decat_Field("@@emsg", $edtVar);

if ($tag == "DELETE" || $tag == "DELETEALL") {
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . " \"> ";
    exit;
}

require_once($docType);
print "\n <html> \n	<head>";
require_once($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n function confirmDeleteAll() {return confirm(\"Delete All Messages for $userProfile\");} \n";
print "\n </script> \n";

require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "VIEWSPOOLLEDFILE";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td>";
if ($formatToPrint != "Y") {
    print "\n <td class=\"toolbar\">";
    if ($errorMessage != 'Y' && trim($edtVar) != '') {
        print "\n  <a onClick=\"return confirmDeleteAll()\" href=\"{$homeURL}{$phpPath}{$scriptName}{$genericVarBase}&amp;tag=DELETEALL\">$deleteAllImageLrg</a>";
    }
    print "</td>";
}
print "\n </tr></table>";
require_once 'ConfMessageDisplay.php';
print $hrTagAttr;

print "\n <table $contentTable>";
print '<tr>';
if ($formatToPrint != "Y" && $errorMessage != 'Y' && trim($edtVar) != '') {
    print "    <td class=\"colhdr\">{$optionHeading}</td>";
}
print '<td class="colhdr">Message</td>';
print '</tr>';

if ($errorMessage == 'Y') {
    print "<tr><td class=\"error\">Message queue is locked by another job</td></tr>";
} elseif (trim($edtVar) == '') {
    print "<tr><td class=\"colaph\">No messages found</td></tr>";
} else {
    for ($i = 1; $i < 100; $i++) {
        $cnt = str_pad($i, 3, '0', STR_PAD_LEFT);
        $fld = '@@m' . $cnt;
        $message = Decat_Field($fld, $edtVar);
        if (trim($message) == '') {
            break;
        }
        $fld = '@@k' . $cnt;
        $msgKey = Decat_Field($fld, $edtVar);
        require 'SetRowClass.php';
        print "<tr class=\"$rowClass\">";
        print "\n <td class=\"opticon\"><a onClick=\"return confirmDelete('$message')\" href=\"{$homeURL}{$phpPath}UserMessage.php{$genericVarBase}&amp;tag=DELETE&amp;msgKey={$msgKey}\">$deleteImageSml</a></td>";
        print "<td class=\"colaph\">$message</td></tr>";
    }
}

print "</table>";
print "$hrTagAttr";
require_once 'Copyright.php';
//require_once 'Trailer.php';
print "\n </body> \n </html>";

function GetMessages($userProfile, $msgKey)
{
    global $pgmLibrary, $i5Connect;
    $edtVar = "";

    if (!$i5Connect) die("<br>Connection Failed. Error number =" . i5_errno() . " msg=" . i5_errormsg());

    $pgmCall = array(
        array("Name" => "userProfile", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "10"),
        array("Name" => "msgKey", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "4"),
        array("Name" => "edtVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000")
    );

    $pgm = i5_program_prepare("CSYUMS", $pgmCall);
    if (!$pgm) {
        die("<br>Program (CSYUMS) Prepare error. Error Number=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = array(
        "userProfile" => $userProfile,
        "msgKey" => $msgKey,
        "edtVar" => $edtVar
    );

    $parmOut = array(
        "edtVar" => "edtVar"
    );

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br>Program (CSYUMS) call errno=" . i5_errno() . " msg=" . i5_errormsg());
    }

    return $edtVar;
}

?>
