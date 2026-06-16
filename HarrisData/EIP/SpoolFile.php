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

$page_title    = "All Spooled Files for $userProfile";
$scriptName    = "SpoolFile.php";
$scriptVarBase = $genericVarBase;
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

if ($tag != "DELETEALL") {
	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);

	print "\n \n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'NewWindowOpen.php';
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
	print "\n function confirmDeleteAll() {return confirm(\"Delete All Spooled Files for $userProfile\");} \n";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "VIEWSPOOLLEDFILE";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1><h2 id=\"idAsOf\">" . date("l, F dS") . " at " . date("h:i:s A") . "</h2></td>";
	if ($formatToPrint != "Y") {
		print "\n <td class=\"toolbar\">";
		print "\n  <a onClick=\"return confirmDeleteAll()\" href=\"{$homeURL}{$phpPath}SpoolFile.php{$genericVarBase}&amp;tag=DELETEALL\">$deleteAllImageLrg</a>";
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td>";
	}
	print "\n </tr></table>";
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
	$status_array = array(Zero, Ready, Open, Closed, Save, Writer, Held, Message_Wait, Pending, Printing, Finished, Send, Defer);

	print "\n <table $contentTable>";
	print '<tr>';
	if ($formatToPrint != "Y") {
		print "    <td class=\"colhdr\">{$optionHeading}</td>";
	}
	print '    <td class="colhdr">File Name</td>';
	print '    <td class="colhdr">Job</td>';
	print '    <td class="colhdr">User</td>';
	print '    <td class="colhdr">Job Number</td>';
	print '    <td class="colhdr">Spool Number</td>';
	print '    <td class="colhdr">Device or<br>Queue</td>';
	print '    <td class="colhdr">Pages</td>';
	print '    <td class="colhdr">Status</td>';
	print '    <td class="colhdr">User<br>Data</td>';
	print '    <td class="colhdr">Creation<br>Date</td>';
	print '    <td class="colhdr">Creation<br>Time</td>';
	print '</tr>';
}

// $i5Authority = i5_adopt_authority($userProfile, $_SERVER['PHP_AUTH_PW'], $i5Connect);
// if (!$i5Authority) die("User Profile failed. Error number =".i5_errno()." msg=".i5_errormsg());

if ($tag == "DELETE") {
	$jobName	= $_GET["jobName"];
	$userName	= $_GET["userName"];
	$jobNbr 	= $_GET["jobNbr"];
	$spoolFileName	= $_GET["spoolFileName"];
	$spoolFileNumber	= $_GET["spoolFileNumber"];

	if (i5_command("DLTSPLF FILE($spoolFileName) JOB($jobNbr/$userName/$jobName) SPLNBR($spoolFileNumber)")) {
		$confMessage=Format_ConfMsg_Desc("D", "$spoolFileName", "$jobNbr/$userName/$jobName" , "", "", "", "");
	}
}

$spoolfile = $_GET["dspsplf"];
$wrksplfCommand = array(
"username" => $userProfile,
"outq" => "*ALL",
"userdata" => "*ALL"
);
$spoolFileConnect = i5_spool_list($wrksplfCommand, $i5Connect);
if (!$spoolFileConnect) die("<br>WrkSplF Command Execution Failed errno=".i5_errno()." msg=".i5_errormsg());

while ($spool_list = i5_spool_list_read($spoolFileConnect)) {
	if ($tag == "DELETEALL") {
		if (i5_command("DLTSPLF FILE($spool_list[SPLFNAME]) JOB($spool_list[JOBNBR]/$spool_list[USERNAME]/$spool_list[JOBNAME]) SPLNBR($spool_list[SPLFNBR])")) {
		}

	} else {
		$confirmDesc = Format_Confirm_Desc("File Name", $spool_list[SPLFNAME], "Job", $spool_list[JOBNAME], "Job Number", $spool_list[JOBNBR]);
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		$status = $status_array[$spool_list[SPLFSTAT]];
		//$spoolFileSizeK = $spool_list['SPLFSIZE'] * $spool_list['SPLFMULT'] / 1024;
		$spoolDate = Format_Date($spool_list['DATEOPEN'], "D");
		$spoolTime = EditHrsMinSec($spool_list['TIMEOPEN']);
		//$i5Connect->setToolkitServiceParams(array('plugSize'=>'5M')); // bigger size to handle large spool file
 		//$stringData = i5_spool_get_data($spool_list[SPLFNAME],$spool_list[JOBNAME],$spool_list[USERNAME],$spool_list[JOBNBR],$spool_list[SPLFNBR]);
		//$i5Connect->setToolkitServiceParams(array('plugSize'=>'512K')); // reset plug size to default
		if ($formatToPrint != "Y") {
			print "\n <td class=\"opticon\">";
			print "\n  <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}SpoolFile.php{$genericVarBase}&amp;tag=DELETE&amp;spoolFileName={$spool_list[SPLFNAME]}&amp;jobName={$spool_list[JOBNAME]}&amp;userName={$spool_list[USERNAME]}&amp;jobNbr={$spool_list[JOBNBR]}&amp;spoolFileNumber={$spool_list[SPLFNBR]}\">$deleteImageSml</a>";
			//if($spoolFileSizeK < 2500 && $stringData !== false) {
				print "\n  <a href=\"{$homeURL}{$phpPath}SpoolFileEmail.php{$genericVarBase}&amp;tag=REPORT&amp;spoolFileName={$spool_list[SPLFNAME]}&amp;jobName={$spool_list[JOBNAME]}&amp;userName={$spool_list[USERNAME]}&amp;jobNbr={$spool_list[JOBNBR]}&amp;spoolFileNumber={$spool_list[SPLFNBR]}&amp;userData={$spool_list[USERDATA]}\" onclick=\"$searchWinVar\">$emailSpoolFile</a>";
			//}
			print "</td>";
		}
		print "<td class=\"colalph\">";
		//if($spoolFileSizeK < 2500) {
			print "<a href=\"{$homeURL}{$phpPath}SpoolFileDisplay.php{$genericVarBase}&amp;spoolFileName={$spool_list[SPLFNAME]}&amp;jobName={$spool_list[JOBNAME]}&amp;userName={$spool_list[USERNAME]}&amp;jobNbr={$spool_list[JOBNBR]}&amp;spoolFileNumber={$spool_list[SPLFNBR]}\" target=\"_blank\" title=\"View Spool File\">{$spool_list[SPLFNAME]}</a>";
		//} else {
		//	print "$spool_list[SPLFNAME]";
		//}
		print "</td>";
		print "<td class=\"colalph\">{$spool_list[JOBNAME]}</td>";
		print "<td class=\"colalph\">{$spool_list[USERNAME]}</td>";
		print "<td class=\"colnmbr\">{$spool_list[JOBNBR]}</td>";
		print "<td class=\"colnmbr\">{$spool_list[SPLFNBR]}</td>";
		print "<td class=\"colalph\">{$spool_list[OUTQNAME]}</td>";
		print "<td class=\"colnmbr\">{$spool_list[PAGES]}</td>";
		print "<td class=\"colalph\">{$status}</td>";
		print "<td class=\"colalph\">{$spool_list[USERDATA]}</td>";
		print "<td class=\"coldate\">{$spoolDate}</td>";
		print "<td class=\"colalph\">{$spoolTime}</td>";
		print("</tr>");
	}
}

if ($tag == "DELETEALL") {
	print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . " \"> ";
	exit;
} else {

	print "</table>";
	print "$hrTagAttr";
	require_once 'Copyright.php';

	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "{$hrTagAttr} \n </body> \n </html>";

	i5_spool_list_close($spoolFileConnect) ||
	print ("<br><hr>FAIL : Failed to close spool list on : $i5_server_ip ");
}
  ?>
