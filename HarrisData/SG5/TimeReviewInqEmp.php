<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$reportSelType		=	(isset($_GET['reportSelType']))		 ?	$_GET['reportSelType']		:	null;
$fromEmid			=	(isset($_GET['fromEmid']))			 ?	$_GET['fromEmid']			:	0;
$pdwk				=	(isset($_GET['pdwk']))			 	 ?	$_GET['pdwk']				:	'P';

require_once "ETControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'ETRetInfo.php';
require_once 'Menu.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$page_title     = "Time Review Inquiry (Employee)";
$scriptName     = "TimeReviewInqEmp.php";
$scriptVarBase  = "{$genericVarBase}&amp;reportSelType=" . urlencode(trim($reportSelType));
$nextPrevVar    = "{$scriptVarBase}&amp;fromEmid=" . urlencode(trim($fromEmid));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;fromEmid=" . urlencode(trim($fromEmid));
$rowIndexCurr	= $startRow;
$dspMaxRows     = 7;
$prtMaxRows     = 500;
$maxRows =7;

$programName   = "HETBLM";
$hfmt  =	(isset($_SESSION['hrsFmt']))	 ?	$_SESSION['hrsFmt']	:	$TAHWKF;
//$pdwk  =	(isset($_SESSION['pdwk']))		 ?	$_SESSION['pdwk']	:	'P';


$_SESSION[$retURL] = $baseURL;
$backURL		=	$_SESSION[$fromURL];
if ($backURL == "") {$backURL = "{$homeURL}{$phpPath}TimeReviewPrompt.php{$genericVarBase}&reportSelType=E&tag=REPORT";}

require 'stmtSQLClear.php';
$stmtSQL .= " Select a.*, b.EMLNAM, b.EMFNAM, b.EMMIDI, b.EMRNAM, b.EMTRCD ";
$stmtSQL .= " From ETBLWK04 a Inner Join HREMPL b ";
$stmtSQL .= " On b.EMCOMP=a.BWCOMP and b.EMFACL=a.BWFACL and b.EMEMPL=a.BWEMPL ";
$stmtSQL .= " and a.BWEMID=$fromEmid ";
$stmtSQL .= " and a.BWXHND='$profileHandle' ";
$stmtSQL .= " and a.BWTYPE='6' ";

require 'stmtSQLEnd.php';

$sqlResult6 = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row6 = db2_fetch_assoc($sqlResult6);

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
print "\n \n <script TYPE=\"text/javascript\">";
print "\n var optionWin;";
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';
require_once 'Menu.js';

require_once 'CheckEnterAjax.php';
require_once 'CheckEnterSearch.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
require_once 'StringTrimJavaScript.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "TIMEREVIEWINQEMP";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

// Program Option Security
$hetblm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
if ($TASUPA != "") {
	$hetbli_OPT=pgmOptSecurity($profileHandle, $dataBaseID, "HETBLI");
	if ($hetbli_OPT['sec_01'] == "Y") {
		$approvalRequired=RetValue("a.BWXHND='$profileHandle' and a.BWEMID=$fromEmid and a.BWTYPE>='1' and a.BWTYPE<='4' and a.BWAORB='A' and a.BWREEC= (Select coalesce(nullif(PLSUPA,' '),TASUPA) From ETCTRL left join HDPLNT on PLPLNT=a.BWPLT Where RRN(ETCTRL)=1)", "ETBLWK a", "count(*)");
	}
}

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n     <td class=\"toolbar\">";
if ($hetbli_OPT['sec_01'] == "Y" && $approvalRequired) {print "\n <a href=\"{$homeURL}{$phpPath}SupervisorApproval.php{$nextPrevVar}\">$supervisorApproval</a>";}
if ($confMessage != ""){print "\n <a href=\"{$homeURL}{$phpPath}TimeReviewPrompt.php{$scriptVarBase}&amp;fromEmid=$fromEmid&tag=REFRESH&amp;pdwk=$pdwk&amp;useSession=Y\">$reloadImage</a>";}
if ($backURL != "") {print "\n <a href=\"$backURL\">$cancelImageMed</a>";}
else                {print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";}

$medIcon= "Y";
require 'HelpPage.php';
print "\n </td></tr></table>";

// Employee Information Section
$employeeRetInfo = ETRetInfo_Employee($fromEmid);
$employeeName    = $employeeRetInfo['employeeName'];
$scheduleNum     = $employeeRetInfo['scheduleNum'];
$scheduleDesc    = $employeeRetInfo['scheduleDesc'];
$employeeInfo    = $employeeRetInfo['employeeInfo'];
$fromHmWk	     = "H";
$wrkDate	     = date('Y-m-d');
$scheduleRetInfo = ETRetInfo_Schedule($fromEmid, $scheduleNum, $fromDept, $fromGrp, $fromPlt, $fromDbpt, $fromWc, $wrkDate, $fromHmWk);
$scheduleInfo    = $scheduleRetInfo['scheduleInfo'];
print "\n <table $contentTable style=\"float:left;\"> ";
Format_Header_Hover("Employee", $employeeName, "","employeeSelection");
Format_Header_Hover("Schedule", $scheduleDesc, $scheduleNum,"scheduleSelection");

print "\n </table> ";

print "\n <br style=\"clear:both;\"> ";

// Hidden Divisions for Employee and Schedule
print "\n <div id=\"employeeSelection\" class=\"moreInfo\">{$employeeInfo}</div>";
print "\n <div id=\"scheduleSelection\" class=\"moreInfo\">{$scheduleInfo}</div>";

require_once 'ConfMessageDisplay.php';
print $hrTagAttr;

print "\n <table $contentTable> <tr>";
$pChecked=Field_Checked($pdwk,"P");
$wChecked=Field_Checked($pdwk,"W");
print "\n     <td class=\"inputcode\"><input name=\"pdwk\" type=\"radio\" $pChecked onClick=\"window.location.href='{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;fromEmid=$fromEmid&amp;pdwk=P'\">Paid   &nbsp; <input  name=\"pdwk\" type=\"radio\" $wChecked onClick= \"window.location.href='{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;fromEmid=$fromEmid&amp;pdwk=W'\">Worked</td> " ;
print "\n</tr>";
print "\n </table> ";

print "\n <div id=\"EmployeeTotal\">";
print "\n <fieldset class=\"legendBody\"> ";
print "\n     <legend class=\"legendTitle\">Employee Total</legend> ";
print "\n <table $contentTable> <tr>";
print "\n <th class=\"colhdr\" >Scheduled<br>Hours</th>";
print "\n <th class=\"colhdr\" >Schedule<br>Variance</th>";
if ($pdwk == 'P') {
	print "\n <th class=\"colhdr\" >Shift<br>Paid</th>";
} else {
	print "\n <th class=\"colhdr\" >Shift<br>Worked</th>";
}
print "\n <th class=\"colhdr\" >Shift<br>Variance</th>";
if ($pdwk == 'P') {
	print "\n <th class=\"colhdr\" >Employee<br>Paid</th>";
} else {
	print "\n <th class=\"colhdr\" >Employee<br>Worked</th>";
}
print "\n</tr>";

print "\n<tr>";
if ($hfmt == 'D') {
	$F6_scheduledHours   = Format_Nbr($row6['BWTSCHRS'], '2', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
	$F6_scheduleVariance = Format_Nbr($row6['BWSCVR'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
	if ($pdwk == 'P') {
		$F6_shiftHours    = Format_Nbr($row6['BWTSHRSP'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		$F6_shiftVariance = Format_Nbr($row6['BWSHVP'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		$F6_employeeHours = Format_Nbr($row6['BWTRHRSP'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
	} else {
		$F6_shiftHours    = Format_Nbr($row6['BWTSHRSW'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		$F6_shiftVariance = Format_Nbr($row6['BWSHVW'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		$F6_employeeHours = Format_Nbr($row6['BWTRHRSW'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
	}
} else {
	$F6_scheduledHours = EditHrsMin($row6['BWTSCHT']);
	if ($TADSSF == "Y") {
		$F6_scheduleVariance = EditHrsMinSec($row6['BWSCVRT']);
	} else {
		$F6_scheduleVariance = EditHrsMin(substr($row6['BWSCVRT'],0,(strlen($row6['BWSCVRT'])-2)));
	}
	if ($pdwk == 'P' && $TADSSF == "Y") {
		$F6_shiftHours    = EditHrsMinSec($row6['BWTSHPT']);
		$F6_shiftVariance = EditHrsMinSec($row6['BWSHVPT']);
		$F6_employeeHours = EditHrsMinSec($row6['BWTRHPT']);
	} elseif ($pdwk == 'P') {
		$F6_shiftHours    = EditHrsMin(substr($row6['BWTSHPT'],0,(strlen($row6['BWTSHPT'])-2)));
		$F6_shiftVariance = EditHrsMin(substr($row6['BWSHVPT'],0,(strlen($row6['BWSHVPT'])-2)));
		$F6_employeeHours = EditHrsMin(substr($row6['BWTRHPT'],0,(strlen($row6['BWTRHPT'])-2)));
	} elseif ($TADSSF == "Y") {
		$F6_shiftHours    = EditHrsMinSec($row6['BWTSHWT']);
		$F6_shiftVariance = EditHrsMinSec($row6['BWSHVWT']);
		$F6_employeeHours = EditHrsMinSec($row6['BWTRHWT']);
	} else {
		$F6_shiftHours    = EditHrsMin(substr($row6['BWTSHWT'],0,(strlen($row6['BWTSHWT'])-2)));
		$F6_shiftVariance = EditHrsMin(substr($row6['BWSHVWT'],0,(strlen($row6['BWSHVWT'])-2)));
		$F6_employeeHours = EditHrsMin(substr($row6['BWTRHWT'],0,(strlen($row6['BWTRHWT'])-2)));
	}
}
print "\n <td class=\"colnmbr\">$F6_scheduledHours</td>";
print "\n <td class=\"coltotvar\">$F6_scheduleVariance</td>";
print "\n <td class=\"colnmbr\">$F6_shiftHours</td>";
print "\n <td class=\"coltotvar\">$F6_shiftVariance</td>";
print "\n <td class=\"colnmbr\">$F6_employeeHours</td>";
print "\n</tr>";
print "\n </table> ";
print "\n     </fieldset> ";
print "\n     </div>";

print "\n <div id=\"Detail\">";
print "\n <fieldset class=\"legendBody\"> ";
print "\n     <legend class=\"legendTitle\">Detail</legend> ";
require 'stmtSQLClear.php';
$stmtSQL .= " Select *";
$fileSQL .= " ETBLWK ";
$selectSQL .= " (BWEMID='$fromEmid' and BWXHND='$profileHandle' and BWTYPE='0') ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By BWDATE,BWSCTL,BWTYPE";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$pageSelectList = "N";
$allowSaveFilter = "N";
$advanceSearch = "N";
require 'QuickSearchOption.php';
print "\n <table $contentTable> <tr>";

if ($hetblm_OPT['sec_01'] == "Y"  || $hetblm_OPT['sec_02'] == "Y" || $hetblm_OPT['sec_03'] == "Y" || $hetblm_OPT['sec_05'] == "Y" || $hetblm_OPT['sec_06'] == "Y" || $hetblm_OPT['sec_07'] == "Y") {
	print "\n <th class=\"colhdr\" colspan=1>&nbsp;</th>";
}

print "\n <th class=\"colhdr\" colspan=1>&nbsp;</th>";
print "\n <th class=\"colhdr\" colspan=1>&nbsp;</th>";
print "\n <th class=\"colhdr\" colspan=1>&nbsp;</th>";
print "\n <th class=\"colhdr\" colspan=1>&nbsp;</th>";
print "\n <th class=\"colhdr\" colspan=3>Schedule</th>";
print "\n <th class=\"colhdr\" colspan=3>Shift</th>";
print "\n <th class=\"colhdr\" colspan=3>Employee</th>";
print "\n <th class=\"colhdr\">Exception</th>";
print "\n</tr>";
print "\n<tr>";
print "\n <th class=\"colhdr\">Opt</th>";
print "\n <th class=\"colhdr\">Work Date</th>";
print "\n <th class=\"colhdr\">Description</th>";
print "\n <th class=\"colhdr\">Group</th>";
print "\n <th class=\"colhdr\">Schedule</th>";
print "\n <th class=\"colhdr\">Start</th>";
print "\n <th class=\"colhdr\">Stop</th>";
print "\n <th class=\"colhdr\">Hours</th>";
print "\n <th class=\"colhdr\">Start</th>";
print "\n <th class=\"colhdr\">Stop</th>";
if ($pdwk == 'P') {
	print "\n <th class=\"colhdr\">Paid</th>";
} else {
	print "\n <th class=\"colhdr\">Worked</th>";
}
print "\n <th class=\"colhdr\">Start</th>";
print "\n <th class=\"colhdr\">Stop</th>";
if ($pdwk == 'P') {
	print "\n <th class=\"colhdr\">Paid</th>";
} else {
	print "\n <th class=\"colhdr\">Worked</th>";
}
print "\n <th class=\"colhdr\">Code</th>";
print "\n </tr>";

$saveDate = "";
require 'stmtSQLClear.php';
$stmtSQL .= " Select *";
$fileSQL .= " ETBLWK04 ";
$selectSQL .= " (BWEMID='$fromEmid' and BWXHND='$profileHandle' and BWTYPE = '0') ";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By BWDATE,BWSCTL,BWTYPE";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResultx = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$rowCountD = 0;
while ($row0 = db2_fetch_assoc($sqlResultx, $startRow)){
	if ($rowCountD >= $dspMaxRows) {break;}
	$F_DSTP=Format_Date_ISO($row0['BWDATE'], "D");
	$F_BWSTRT = EditHrsMin($row0['BWSTRT']);
	$F_BWSTOP = EditHrsMin($row0['BWSTOP']);
	if ($hfmt == 'D') {
		$F0_scheduledHours = Format_Nbr($row0[BWEHRS], '2', $hrsEditCode, 'Y', $hrsBeforeChar, $hrsAfterChar);
	} else {
		$F0_scheduledHours = EditHrsMin(substr($row0['BWEHRST'],0,(strlen($row0['BWEHRST'])-2)));
	}
	require  'SetRowClass.php';

	print "\n <tr class=\"$rowClass\" style=\"border-top : 1px solid black\" colspan=\"12\">";
	// Maintenance Options
	print "\n <td class=\"opticon\">";
	$maintainVar =  $scriptVarBase . "&amp;fromEmid=" . urlencode(trim($fromEmid)) . "&amp;fromRwid=" . urlencode(trim($row0['BWRWID'])) . "&amp;pdwk=" . urlencode(trim($pdwk)) . "&amp;startRow=" . urlencode(trim($rowIndexCurr));
	$foundShiftOn=RetValue("EHEMID=$fromEmid and EHDATE='{$row0['BWDATE']}' and EHTRAN='10'", "HDMECH", "count(*)");
	if ($hetblm_OPT['sec_01'] == "Y" && $foundShiftOn==0) {
		print "\n <a href=\"{$homeURL}{$phpPath}TimeReviewMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addShiftTransaction</a>";
	}
	if ($hetblm_OPT['sec_05'] == "Y") {
		print "\n <a href=\"{$homeURL}{$phpPath}TimeReviewMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=S\">$addScheduleException</a>";
	}
	if ($hetblm_OPT['sec_07'] == "Y" && $TALTFM != "") {
		print "\n <a href=\"{$homeURL}{$phpPath}hdlist.php{$scriptVarBase}&amp;tblID=175&amp;fKey1=LNEMID&amp;fVal1=$fromEmid&amp;fKey2=LNDATE&amp;fVal2=$row0[BWDATE] \">$lunchsml</a>";
	}
	print "\n </td>";
	print "\n <td class=\"coldate\">$F_DSTP</td>";
	print "\n <td class=\"colalph\">Schedule</td>";
	print "\n <td class=\"colnmbr\">&nbsp;</td>";
	print "\n <td class=\"colnmbr\">$row0[EMSCHD]</td>";
	print "\n <td class=\"colnmbr\">$F_BWSTRT</td>";
	print "\n <td class=\"colnmbr\">$F_BWSTOP</td>";
	print "\n <td class=\"colnmbr\">$F0_scheduledHours</td>";
	print "\n <td class=\"colnmbr\" colspan=6>&nbsp;</td>";
	print "\n <td class=\"colcode\">$row0[BWEDEX]</td>";
	print "\n</tr>";

	// loop the shift(1) and transaction(2-4) records that correspond to the schedule record(0)
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select *";
	$fileSQL .= " ETBLWK04 ";
	$selectSQL .= " (BWEMID='$fromEmid' and BWXHND='$profileHandle' and BWTYPE >= '1' and BWTYPE <= '4' and BWDATE = '$row0[BWDATE]') ";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By BWDATE,BWSCTL,BWTYPE";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult)){
		if ($saveDate == "" ) {
			$saveDate = $row['BWDATE'];
		}
		if ($row['BWTYPE'] > '1' and $row['BWTYPE'] < '5' and $shiftRec != "Y" ) {
			print "\n <tr class=\"$rowClass\" style=\"border-top : 1px solid black\">";
			print "\n <td class=\"colalph\"></td>";
			if ($printDate != $saveDate) {
				print "\n <td class=\"coldate\">$F_DSTP</td>";
				$printDate = $saveDate;
			} else {
				print "\n <td class=\"coldate\"></td>";
			}
			print "\n <td class=\"colnmbr\" colspan = 4></td>";
			print "\n <td class=\"colalph\" colspan = 3>****Error - No Shift Row</td>";
			print "\n <td class=\"colnmbr\" colspan = 4></td>";
			print "\n </tr>";
			$shiftRec = "Y";
		}

		print "\n <tr class=\"$rowClass\">";
		// Maintenance Options
		print "\n <td class=\"opticon\">";
		$maintainVar =  $scriptVarBase . "&amp;fromEmid=" . urlencode(trim($fromEmid)) . "&amp;fromRwid=" . urlencode(trim($row['BWRWID'])) . "&amp;pdwk=" . urlencode(trim($pdwk)) . "&amp;fromDate=" . urlencode(trim($row['BWDATE'])) . "&amp;startRow=" . urlencode(trim($rowIndexCurr));
		if ($row['BWTYPE'] == '1') {
			if ($hetblm_OPT['sec_06'] == "Y") {
				print "\n <a href=\"{$homeURL}{$phpPath}TimeReviewMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=L\">$addLaborTransaction</a>";
			}
			if ($hetblm_OPT['sec_02'] == "Y") {
				print "\n <a href=\"{$homeURL}{$phpPath}TimeReviewMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageSml</a>";
			}
			$foundDetail=RetValue("LBEMID=$fromEmid and LBDATE='{$row['BWDATE']}' and LBSCTL={$row['BWSCTL']} and LBDTCL<>'15'", "SIMLBP", "count(*)");
			if ($row['BWAORB'] == 'B' && $foundDetail==0) {
				$foundDetail=RetValue("EHEMID=$fromEmid and EHDATE='{$row['BWDATE']}' and EHSCTL={$row['BWSCTL']} and EHTRAN<>'10'", "HDMECH", "count(*)");
			}
			if ($hetblm_OPT['sec_03'] == "Y" && $foundDetail==0) {
				$confirmDesc = Format_Confirm_Desc("Data Collection Code $row[BWDTCL]", "$F_DSTP", "", "", "", "");
				print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}TimeReviewMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
			}
		} else {
			if ($hetblm_OPT['sec_02'] == "Y") {
				print "\n <a href=\"{$homeURL}{$phpPath}TimeReviewMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageSml</a>";
			}
			if ($hetblm_OPT['sec_03'] == "Y") {
				$confirmDesc = Format_Confirm_Desc("Data Collection Code $row[BWDTCL]", "$F_DSTP", "", "", "", "");
				print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}TimeReviewMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
			}
		}
		print "\n </td>";

		if ($printDate != $saveDate and $row['BWTYPE'] != '6')  {
			print "\n <td class=\"coldate\"></td>";
			$printDate = $saveDate;
		} else {
			print "\n <td class=\"coldate\"></td>";
		}

		if ($TADSSF == "Y") {
			$F_BWSTRT = EditHrsMinSec($row['BWSTR6']);
			$F_BWSTOP = EditHrsMinSec($row['BWSTP6']);
		} else {
			$F_BWSTRT = EditHrsMin(substr($row['BWSTR6'],0,(strlen($row['BWSTR6'])-2)));
			$F_BWSTOP = EditHrsMin(substr($row['BWSTP6'],0,(strlen($row['BWSTP6'])-2)));
		}
		if ($hfmt == 'D') {
			if ($pdwk == 'P') {
				$F_Hours = Format_Nbr($row['BWPHRS'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			} else {
				$F_Hours = Format_Nbr($row['BWWHRS'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			}
		} else {
			if ($pdwk == 'P' && $TADSSF == "Y") {
				$F_Hours = EditHrsMinSec($row['BWPHRST']);
			} elseif ($pdwk == 'P') {
				$F_Hours    = EditHrsMin(substr($row['BWPHRST'],0,(strlen($row['BWPHRST'])-2)));
			} elseif ($TADSSF == "Y") {
				$F_Hours = EditHrsMinSec($row['BWWHRST']);
			} else {
				$F_Hours = EditHrsMin(substr($row['BWWHRST'],0,(strlen($row['BWWHRST'])-2)));
			}
		}

		if  ($row['BWTYPE'] == '1')   {
			print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}TimeReviewInqEmpDetail.php{$maintainVar}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($scriptName)) . "&amp;fromDate=" . urlencode(trim($row['BWDATE'])) . "&amp;fromSctl=" . urlencode(trim($row[BWSCTL])) . "&amp;fromType=" . urlencode(trim($row[BWTYPE])) . "\" title=\"Employee Detail (Shift)\">Shift</a></td>";
			print "\n <td class=\"colnmbr\">$row[BWGRP]</td>";
			print "\n <td class=\"colnmbr\">$row[BWSCHD]</td>";
			print "\n <td class=\"colnmbr\" colspan = 3></td>";
			print "\n <td class=\"colnmbr\">$F_BWSTRT</td>";
			print "\n <td class=\"colnmbr\">$F_BWSTOP</td>";
			print "\n <td class=\"colnmbr\">$F_Hours</td>";
			print "\n <td class=\"colnmbr\" colspan = 3></td>";
			print "\n <td class=\"colcode\">$row[BWEDEX]</td>";
			$shiftRec = "Y";

		} elseif  ($row['BWTYPE'] == '2')   {
			print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}TimeReviewInqEmpDetail.php{$maintainVar}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($scriptName)) . "&amp;fromDate=" . urlencode(trim($row['BWDATE'])) . "&amp;fromSctl=" . urlencode(trim($row[BWSCTL])) . "&amp;fromType=" . urlencode(trim($row[BWTYPE])) . "\" title=\"Employee Detail (Run)\">Employee</a></td>";
			print "\n <td class=\"colnmbr\">$row[BWGRP]</td>";
			print "\n <td class=\"colnmbr\">$row[BWSCHD]</td>";
			if ($HDMERL > 0 ) {
				print "\n <td class=\"colalph\" colspan = 1>DC-$row[BWDTCL]     LC-$row[BWLCOD]</td>";
			} else {
				print "\n <td class=\"colalph\" colspan = 1>DC-$row[BWDTCL]</td>";
			}
			if (trim($row[BWMDPT]) != "") {
				if ($HDMERL > 0) {
					print "\n <td class=\"colalph\" colspan = 3>Dept/WC-$row[BWMDPT] / $row[BWWC]</td>";
				} else {
					print "\n <td class=\"colalph\" colspan = 3>Dept-$row[BWMDPT]</td>";
				}
			}
			if (trim($row[BWORD]) != "") {
				print "\n <td class=\"colalph\" colspan = 2>Ord/Seq $row[BWORD] / $row[BWSEQN]</td>";
			} else {
				if (($row[BWDTCL] == "55" || $row[BWDTCL] == "50") and trim($row[BWDTRC]) != "") {
					print "\n <td class=\"colnmbr\" colspan = 2>Indirect/Downtime Code- $row[BWDTRC]</td>";
				} else {
					print "\n <td class=\"colnmbr\" colspan = 2></td>";
				}
			}
			print "\n <td class=\"colnmbr\">$F_BWSTRT</td>";
			print "\n <td class=\"colnmbr\">$F_BWSTOP</td>";
			print "\n <td class=\"colnmbr\">$F_Hours</td>";
			print "\n <td class=\"colcode\">$row[BWEDEX]</td>";

		} elseif  ($row[BWTYPE] == '3')   {
			print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}TimeReviewInqEmpDetail.php{$maintainVar}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($scriptName)) . "&amp;fromDate=" . urlencode(trim($row['BWDATE'])) . "&amp;fromSctl=" . urlencode(trim($row[BWSCTL])) . "&amp;fromType=" . urlencode(trim($row[BWTYPE])) . "\" title=\"Employee Detail (Adjustment)\">Adjustment</a></td>";
			print "\n <td class=\"colnmbr\">$row[BWGRP]</td>";
			print "\n <td class=\"colnmbr\">$row[BWSCHD]</td>";
			if ($HDMERL > 0 ) {
				print "\n <td class=\"colalph\" colspan = 1>DC-$row[BWDTCL]     LC-$row[BWLCOD]</td>";
			} else {
				print "\n <td class=\"colalph\" colspan = 1>DC-$row[BWDTCL]</td>";
			}
			if (trim($row[BWMDPT]) != "") {
				if ($HDMERL > 0) {
					print "\n <td class=\"colalph\" colspan = 3>Dept/WC-$row[BWMDPT] / $row[BWWC]</td>";
				} else {
					print "\n <td class=\"colalph\" colspan = 3>Dept-$row[BWMDPT]</td>";
				}
			}
			if (trim($row[BWORD]) != "") {
				print "\n <td class=\"colalph\" colspan = 2>Ord/Seq $row[BWORD] / $row[BWSEQN]</td>";
			} else {
				if (($row[BWDTCL] == "55" || $row[BWDTCL] == "50") and trim($row[BWDTRC]) != "") {
					print "\n <td class=\"colnmbr\" colspan = 2>Indirect/Downtime Code- $row[BWDTRC]</td>";
				} else {
					print "\n <td class=\"colnmbr\" colspan = 2></td>";
				}
			}
			print "\n <td class=\"colnmbr\">$F_BWSTRT</td>";
			print "\n <td class=\"colnmbr\">$F_BWSTOP</td>";
			print "\n <td class=\"colnmbr\">$F_Hours</td>";
			print "\n <td class=\"colcode\">$row[BWEDEX]</td>";

		} elseif  ($row[BWTYPE] == '4')   {
			print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}TimeReviewInqEmpDetail.php{$maintainVar}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($scriptName)) . "&amp;fromDate=" . urlencode(trim($row['BWDATE'])) . "&amp;fromSctl=" . urlencode(trim($row[BWSCTL])) . "&amp;fromType=" . urlencode(trim($row[BWTYPE])) . "\" title=\"Employee Detail (Dead Time)\">Dead Time</a></td>";
			print "\n <td class=\"colnmbr\">$row[BWGRP]</td>";
			print "\n <td class=\"colnmbr\">$row[BWSCHD]</td>";
			print "\n <td class=\"colnmbr\" colspan = 6></td>";
			print "\n <td class=\"colnmbr\" colspan = 2>Dead Time</td>";
			print "\n <td class=\"colnmbr\">$F_Hours</td>";
			print "\n <td class=\"colcode\">$row[BWEDEX]</td>";
		}
		print "\n</tr>";

		$rowCount ++;
		$saverowCount ++;
	}


	$rowCounthold = $rowCount;
	$rowCount = 0;
	$stmtSQL5 = "";
	$stmtSQL5 .= " Select * from ETBLWK04 f Where f.BWEMID=$fromEmid and f.BWXHND='$profileHandle' ";
	$stmtSQL5 .= " and f.BWDATE='$row0[BWDATE]' and f.BWTYPE='5' ";
	$stmtSQL5 .= " For Fetch Only with NC Optimize For $dspMaxRows Rows ";

	$sqlResult5 = db2_exec($i5Connect->getConnection (), $stmtSQL5);
	$row5 = db2_fetch_assoc($sqlResult5);

	$rowCount = $rowCounthold;
	if ($hfmt == 'D') {
		$F_scheduleVariance = Format_Nbr($row5['BWSCVR'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		if ($pdwk == 'P') {
			$F_shiftHours    = Format_Nbr($row5['BWTSHRSP'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			$F_shiftVariance = Format_Nbr($row5['BWSHVP'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			$F_employeeHours = Format_Nbr($row5['BWTRHRSP'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		} else {
			$F_shiftHours    = Format_Nbr($row5['BWTSHRSW'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			$F_shiftVariance = Format_Nbr($row5['BWSHVW'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			$F_employeeHours = Format_Nbr($row5['BWTRHRSW'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		}
	} else {
		if ($TADSSF == "Y") {
			$F_scheduleVariance = EditHrsMinSec($row5['BWSCVRT']);
		} else {
			$F_scheduleVariance = EditHrsMin(substr($row5['BWSCVRT'],0,(strlen($row5['BWSCVRT'])-2)));
		}
		if ($pdwk == 'P' && $TADSSF == "Y") {
			$F_shiftHours    = EditHrsMinSec($row5['BWTSHPT']);
			$F_shiftVariance = EditHrsMinSec($row5['BWSHVPT']);
			$F_employeeHours = EditHrsMinSec($row5['BWTRHPT']);
		} elseif ($pdwk == 'P') {
			$F_shiftHours    = EditHrsMin(substr($row5['BWTSHPT'],0,(strlen($row5['BWTSHPT'])-2)));
			$F_shiftVariance = EditHrsMin(substr($row5['BWSHVPT'],0,(strlen($row5['BWSHVPT'])-2)));
			$F_employeeHours = EditHrsMin(substr($row5['BWTRHPT'],0,(strlen($row5['BWTRHPT'])-2)));
		} elseif ($TADSSF == "Y") {
			$F_shiftHours    = EditHrsMinSec($row5['BWTSHWT']);
			$F_shiftVariance = EditHrsMinSec($row5['BWSHVWT']);
			$F_employeeHours = EditHrsMinSec($row5['BWTRHWT']);
		} else {
			$F_shiftHours    = EditHrsMin(substr($row5['BWTSHWT'],0,(strlen($row5['BWTSHWT'])-2)));
			$F_shiftVariance = EditHrsMin(substr($row5['BWSHVWT'],0,(strlen($row5['BWSHVWT'])-2)));
			$F_employeeHours = EditHrsMin(substr($row5['BWTRHWT'],0,(strlen($row5['BWTRHWT'])-2)));
		}
	}
	if (($saverowCount > 1 and $row0['BWEHRS'] > 0) || $row5['BWSCVR'] != 0 || $row5['BWSHVP'] != 0 || $row5['BWSHVW'] != 0) {
		print "\n <tr class=\"$rowClass\">";
		print "\n <td class=\"colalph\"></td>";
		print "\n <td class=\"colalph\"></td>";
		print "\n <td class=\"dsptothdr\">Totals $F_DSTP</td>";
		print "\n <td class=\"dsptotalph\" colspan = 5>&nbsp;</td>";
		if ($row5['BWSCVR'] != 0) {
			print "\n <td class=\"dsptotvarhdr\">Sched Var</td>";
			print "\n <td class=\"dsptotvar\">$F_scheduleVariance</td>";
		} else {
			print "\n <td class=\"dsptothdr\" colspan = 2>&nbsp;</td>";
		}
		print "\n <td class=\"dsptotnmbr\">$F_shiftHours</td>";
		if (( $pdwk == 'P' && $row5['BWSHVP'] != 0) || ( $pdwk == 'W' && $row5['BWSHVW'] != 0)){
			print "\n <td class=\"dsptotvarhdr\">Shift Var</td>";
			print "\n <td class=\"dsptotvar\">$F_shiftVariance</td>";
		} else {
			print "\n <td class=\"dsptotnmbr\" colspan = 2>&nbsp;</td>";
		}
		print "\n <td class=\"dsptotnmbr\">$F_employeeHours</td>";
		print "\n <td class=\"dsptotnmbr\" colspan = 4>&nbsp;</td>";
		print "\n </tr>";

		$saverowCount = 0;
		$rowCount = $rowCounthold;
		$saveDate = $row['BWDATE'];
	}

	if ($row0['BWTYPE'] == '0') {
		$rowCountD ++;
	}
	$startRow ++;
	$saverowCount ++;
}

if ($rowCount != 0) {
	$rowCounthold = $rowCount;
	$startRow ++;
	$rowCount = 0;
	$stmtSQL5 = "";
	$stmtSQL5 .= " Select * from ETBLWK04 f Where f.BWEMID=$fromEmid and f.BWXHND='$profileHandle' ";
	$stmtSQL5 .= " and f.BWDATE='$saveDate' and f.BWTYPE='5' ";
	$stmtSQL5 .= " For Fetch Only with NC Optimize For $dspMaxRows Rows ";

	$sqlResult5 = db2_exec($i5Connect->getConnection (), $stmtSQL5);
	$row5 = db2_fetch_assoc($sqlResult5);

	$rowCount = $rowCounthold;
	if ($hfmt == 'D') {
		$F_scheduleVariance = Format_Nbr($row5['BWSCVR'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		if ($pdwk == 'P') {
			$F_shiftHours    = Format_Nbr($row5['BWTSHRSP'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			$F_shiftVariance = Format_Nbr($row5['BWSHVP'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			$F_employeeHours = Format_Nbr($row5['BWTRHRSP'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		} else {
			$F_shiftHours    = Format_Nbr($row5['BWTSHRSW'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			$F_shiftVariance = Format_Nbr($row5['BWSHVW'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			$F_employeeHours = Format_Nbr($row5['BWTRHRSW'], '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		}
	} else {
		if ($TADSSF == "Y") {
			$F_scheduleVariance = EditHrsMinSec($row5['BWSCVRT']);
		} else {
			$F_scheduleVariance = EditHrsMin(substr($row5['BWSCVRT'],0,(strlen($row5['BWSCVRT'])-2)));
		}
		if ($pdwk == 'P' && $TADSSF == "Y") {
			$F_shiftHours    = EditHrsMinSec($row5['BWTSHPT']);
			$F_shiftVariance = EditHrsMinSec($row5['BWSHVPT']);
			$F_employeeHours = EditHrsMinSec($row5['BWTRHPT']);
		} elseif ($pdwk == 'P') {
			$F_shiftHours    = EditHrsMin(substr($row5['BWTSHPT'],0,(strlen($row5['BWTSHPT'])-2)));
			$F_shiftVariance = EditHrsMin(substr($row5['BWSHVPT'],0,(strlen($row5['BWSHVPT'])-2)));
			$F_employeeHours = EditHrsMin(substr($row5['BWTRHPT'],0,(strlen($row5['BWTRHPT'])-2)));
		} elseif ($TADSSF == "Y") {
			$F_shiftHours    = EditHrsMinSec($row5['BWTSHWT']);
			$F_shiftVariance = EditHrsMinSec($row5['BWSHVWT']);
			$F_employeeHours = EditHrsMinSec($row5['BWTRHWT']);
		} else {
			$F_shiftHours    = EditHrsMin(substr($row5['BWTSHWT'],0,(strlen($row5['BWTSHWT'])-2)));
			$F_shiftVariance = EditHrsMin(substr($row5['BWSHVWT'],0,(strlen($row5['BWSHVWT'])-2)));
			$F_employeeHours = EditHrsMin(substr($row5['BWTRHWT'],0,(strlen($row5['BWTRHWT'])-2)));
		}
	}
	if ($saverowCount > 1 || $row5['BWSCVR'] != 0 || $row5['BWSHVP'] != 0 || $row5['BWSHVW'] != 0){
		print "\n <tr class=\"$rowClass\">";
		print "\n <td class=\"colalph\"></td>";
		print "\n <td class=\"colalph\"></td>";
		print "\n <td class=\"dsptothdr\">Totals $F_DSTP</td>";
		print "\n <td class=\"dsptptalph\"colspan = 5>&nbsp;</td>";
		if ($row5['BWSCVR'] != 0) {
			print "\n <td class=\"dsptotvarhdr\">Sched Var</td>";
			print "\n <td class=\"dsptotvar\">$F_scheduleVariance</td>";
		} else {
			print "\n <td class=\"dsptothdr\" colspan = 2>&nbsp;</td>";
		}
		print "\n <td class=\"dsptotnmbr\">$F_shiftHours</td>";
		if (( $pdwk == 'P' && $row5['BWSHVP'] != 0) || ( $pdwk == 'W' && $row5['BWSHVW'] != 0)) {
			print "\n <td class=\"dsptotvarhdr\">Shift Var</td>";
			print "\n <td class=\"dsptotvar\">$F_shiftVariance</td>";
		} else {
			print "\n <td class=\"dsptotnmbr\" colspan = 2>&nbsp;</td>";
		}
		print "\n <td class=\"dsptotnmbr\">$F_employeeHours</td>";
		print "\n <td class=\"dsptotnmbr\" colspan = 4>&nbsp;</td>";
		print "\n </tr>";

	}
	$saverowCount = 0;
	$rowCount = $rowCounthold;
	$saveDate = $row['BWDATE'];
}

print "\n </table>";
print "\n     </fieldset> ";
print "\n     </div>";
print $inquiryhrTagAttr;
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require $inquiryTrailer;
print "\n </body> \n </html>";
?>	
