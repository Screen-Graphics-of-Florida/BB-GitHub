<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = (isset($_GET['maintenanceCode']))  ? $_GET['maintenanceCode']  : "";
$errFound        = (isset($_GET['errFound']))         ? $_GET['errFound']         : "";
$fromEmid 	     = (isset($_GET['fromEmid']))         ? $_GET['fromEmid']         : 0;
$fromRwid 	     = (isset($_GET['fromRwid']))         ? $_GET['fromRwid']         : 0;
$pdwk			 = (isset($_GET['pdwk']))			  ?	$_GET['pdwk']			  :	"P";
$fromDate		 = (isset($_GET['fromDate']))		  ?	$_GET['fromDate']		  :	"";


require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once "ETControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'ETRetInfo.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Time Review Maintenance";
$scriptName     = "TimeReviewMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromEmid=" . urlencode(trim($fromEmid)) . "&amp;fromRwid=" . urlencode(trim($fromRwid)) . "&amp;pdwk=" . urlencode(trim($pdwk)) . "&amp;fromDate=" . urlencode(trim($fromDate)) . "&amp;startRow=" . urlencode(trim($startRow));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HETBLM";

$timeMax 		=	($TADSSF == 'Y')				? 	'6' 				: 	'4';
$hrsFmt			=	(isset($_SESSION['hrsFmt']))	?	$_SESSION['hrsFmt']	:	$TAHWKF;
if ($hrsFmt=='T' && $TADSSF=="Y") {
	$hoursFormat="(HHHMMSS)";
	$hoursMax="8";
	$hoursMaxJs="7";
} elseif ($hrsFmt=='T') {
	$hoursFormat="(HHHMM)";
	$hoursMax="6";
	$hoursMaxJs="5";
} else {
	$hoursFormat="(Decimal)";
	$hoursMax="10";
}

if ($backURL == "") {$backURL="{$homeURL}{$phpPath}TimeReviewInqEmp.php{$scriptVarBase}&amp;reportSelType=E&amp;tag=REPORT";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";

	$stmtSQL= "";
	$stmtSQL .= " Select * ";
	$stmtSQL .= " From ETBLWK04 ";
	$stmtSQL .= " Where BWXHND='$profileHandle' and BWRWID=$fromRwid ";
	require 'stmtSQLEnd.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$requiredFields = "";
	$editVariables =  "";

	if ($maintenanceCode=="S") {
		$requiredFields .= " document.Chg.strt.value == \"\" || ";
		$requiredFields .= " document.Chg.stop.value == \"\" || ";
		$requiredFields .= " document.Chg.code.value == \"\" ";
		$editVariables .= " editNum(document.Chg.strt, 4, 0) &&" ;
		$editVariables .= " editNum(document.Chg.stop, 4, 0) " ;
	} else {
		if ($HDMERL>0 && $maintenanceCode=="A") {
			$editVariables .= " editNum(document.Chg.plt, 3, 0)" ;
		}
		if ($row['BWTYPE']!="4") {
			if ($editVariables != "") {$editVariables .= " && " ;}
			$editVariables .= " editNum(document.Chg.str6, $timeMax, 0) &&" ;
			$editVariables .= " editNum(document.Chg.stp6, $timeMax, 0) " ;
		}
		if ($pdwk=="P" && $hrsFmt=="T") {
			if ($editVariables != "") {$editVariables .= " && " ;}
			$editVariables .= " editNum(document.Chg.phrs, $hoursMaxJs, 0) ";
		} elseif ($pdwk=="P") {
			if ($editVariables != "") {$editVariables .= " && " ;}
			$editVariables .= " editNum(document.Chg.phrs, 3, 6) ";
		} elseif ($hrsFmt=="T") {
			if ($editVariables != "") {$editVariables .= " && " ;}
			$editVariables .= " editNum(document.Chg.whrs, $hoursMaxJs, 0) ";
		} else {
			if ($editVariables != "") {$editVariables .= " && " ;}
			$editVariables .= " editNum(document.Chg.whrs, 3, 6) ";
		}
		if ($editVariables != "") {$editVariables .= " && " ;}
		$editVariables .= " editNum(document.Chg.shft, 1, 0) ";
		if ($row['BWTYPE']=="1" && $maintenanceCode=="L" || $row['BWTYPE']=="2" || $row['BWTYPE']=="3") {
			if ($editVariables != "") {$editVariables .= " && " ;}
			$editVariables .= " editNum(document.Chg.seqn, 3, 0) && ";
			$editVariables .= " editNum(document.Chg.qtyc, 9, 4) && ";
			$editVariables .= " editNum(document.Chg.qtys, 9, 4) ";
			if ($TADPRW=="Y") {
				if ($editVariables != "") {$editVariables .= " && " ;}
				$editVariables .= " editNum(document.Chg.rewk, 9, 4) ";
			}
		}
	}

	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	require_once 'CheckEnterChg.php';

	print "\n function validate(chgForm) {";
	if ($requiredFields) {
		print "\n if ($requiredFields) ";
		print "\n {alert(\"$reqFieldError\"); return false;} ";
	}
	print "\n if ($editVariables) ";
	print "\n return true; ";
	print "\n } ";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")} \n";
	require_once 'ShowHideSelCriteria.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "TIMEREVIEWMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";

	// Program Option Security
	$hetblm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hetblm_OPT['sec_01'];
	$sec_02=$hetblm_OPT['sec_02'];
	$sec_03=$hetblm_OPT['sec_03'];
	$sec_04=$hetblm_OPT['sec_04'];
	require_once 'MaintainTop.php';

	// Employee Information Section
	$employeeRetInfo = ETRetInfo_Employee($fromEmid);
	$employeeName    = $employeeRetInfo['employeeName'];
	$homeSchedule    = $employeeRetInfo['scheduleNum'];
	$homeSchedDesc   = $employeeRetInfo['scheduleDesc'];
	$homeShift       = $employeeRetInfo['shiftNumber'];
	$homeDept        = $employeeRetInfo['homeDept'];
	$homePlant       = $employeeRetInfo['homePlant'];
	$homeMfgDept     = $employeeRetInfo['homeMfgDept'];
	$homeMfgWc       = $employeeRetInfo['homeMfgWc'];
	$employeeInfo    = $employeeRetInfo['employeeInfo'];

	// Home Schedule Information Section
	$scheduleRetInfo = ETRetInfo_Schedule($fromEmid, $homeSchedule, $homeDept, 0, $homePlant, $homeMfgDept, $homeMfgWc, $row['BWDATE'], "H");
	$scheduleInfo    = $scheduleRetInfo['scheduleInfo'];

	print "\n <table $contentTable> ";
	Format_Header_Hover("Employee", $employeeName, "","employeeSelection");
	Format_Header_Hover("Home Schedule", $homeSchedDesc, $homeSchedule,"scheduleSelection");
	$F_MWDATE = Format_Date_ISO($row['BWDATE'], "D");
	$dateDesc =  date("l",strtotime($row['BWDATE']));
	if ($maintenanceCode!="S") {
		Format_Header("Work Date", $dateDesc, $F_MWDATE);
	} else {
		Format_Header("Date", $dateDesc, $F_MWDATE);
	}
	if ($row['BWSCHD'] !== $homeSchedule && $maintenanceCode!="A" && $maintenanceCode!="S" and trim($row['BWDTCL'])!="") {
		// Work Schedule Information Section
		$scheduleRetInfo  = ETRetInfo_Schedule($fromEmid, $row['BWSCHD'], $row['BWMDPT'], $row['BWGRP'], $row['BWPLT'], $row['BWMDPT'], $row['BWWC'], $row['BWDATE'], "W");
		$workScheduleInfo = $scheduleRetInfo['scheduleInfo'];
		$workSchedDesc=RetValue("SMSCHD={$row['BWSCHD']} and SMEFFS is null", "HDSCHM", "SMDESC");
		Format_Header_Hover("Work Schedule", $workSchedDesc, $row['BWSCHD'],"workScheduleSelection");
	}
	if ($row['BWGRP'] && $maintenanceCode=="C") {
		$workGroupDesc=RetValue("BBGRP#={$row['BWGRP']}", "HDGRPM", "BBDESC");
		Format_Header("Work Group", $workGroupDesc, $row['BWGRP']);
	}
	print "\n </table> ";

	// Hidden Divisions for Employee and Schedule
	print "\n <div id=\"employeeSelection\" class=\"moreInfo\">{$employeeInfo}</div>";
	print "\n <div id=\"scheduleSelection\" class=\"moreInfo\">{$scheduleInfo}</div>";
	if ($row['BWSCHD'] !== $homeSchedule && $maintenanceCode!="A" && $maintenanceCode!="S") {
		print "\n <div id=\"workScheduleSelection\" class=\"moreInfo\">{$workScheduleInfo}</div>";
	}

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	if ($errFound != "") {
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$wrnVar=WrnVarErr($profileHandle, $wrnVar);
		$Err_BWPLT =DecatErr_Field("@@plt@", "plt");
		$Err_BWDTCL=DecatErr_Field("@@dtcl", "dtcl");
		$Err_BWSTRT=DecatErr_Field("@@strt", "strt");
		$Err_BWSTOP=DecatErr_Field("@@stop", "stop");
		$Err_BWCODE1=DecatErr_Field("@@code", "code");
		$Err_BWSTR6=DecatErr_Field("@@str6", "str6");
		$Err_BWSEEC=DecatErr_Field("@@seec", "seec");
		$Err_BWSPYC=DecatErr_Field("@@spyc", "spyc");
		$Err_BWSTP6=DecatErr_Field("@@stp6", "stp6");
		$Err_BWPEEC=DecatErr_Field("@@peec", "peec");
		$Err_BWPPYC=DecatErr_Field("@@ppyc", "ppyc");
		$Err_BWREEC=DecatErr_Field("@@reec", "reec");
		$Err_BWRPYC=DecatErr_Field("@@rpyc", "rpyc");
		$Err_BWPHRS=DecatErr_Field("@@phrs", "phrs");
		$Err_BWWHRS=DecatErr_Field("@@whrs", "whrs");
		$Err_BWOPYC=DecatErr_Field("@@opyc", "opyc");
		$Err_BWSHFT=DecatErr_Field("@@shft", "shft");
		$Err_BWHOLD=DecatErr_Field("@@hold", "hold");
		$Err_BWDTRC=DecatErr_Field("@@dtrc", "dtrc");
		$Err_BWLCOD=DecatErr_Field("@@lcod", "lcod");
		$Err_BWMDPT=DecatErr_Field("@@mdpt", "dept");
		$Err_BWORD =DecatErr_Field("@@ord@", "ord");
		$Err_BWSEQN=DecatErr_Field("@@seqn", "seqn");
		$Err_BWQTYC=DecatErr_Field("@@qtyc", "qtyc");
		$Err_BWQTYS=DecatErr_Field("@@qtys", "qtys");
		$Err_BWSCRC=DecatErr_Field("@@scrc", "scrc");
		$Err_BWREWK=DecatErr_Field("@@rewk", "rewk");
		$Err_BWRWRC=DecatErr_Field("@@rwrc", "rwrc");
		$Err_BWOPRC=DecatErr_Field("@@oprc", "oprc");
		$Err_BWCMNT=DecatErr_Field("@@cmnt", "cmnt");

		$row['BWPLT'] =Decat_Field("@@plt@", $edtVar);
		$row['BWDTCL']=Decat_Field("@@dtcl", $edtVar);
		$row['BWSTRT']=Decat_Field("@@strt", $edtVar);
		$row['BWSTOP']=Decat_Field("@@stop", $edtVar);
		$row['BWCODE1']=Decat_Field("@@code", $edtVar);
		$row['BWSTR6']=Decat_Field("@@str6", $edtVar);
		$row['BWSEEC']=Decat_Field("@@seec", $edtVar);
		$row['BWSELV']=Decat_Field("@@selv", $edtVar);
		$row['BWSPYC']=Decat_Field("@@spyc", $edtVar);
		$row['BWSTP6']=Decat_Field("@@stp6", $edtVar);
		$row['BWPEEC']=Decat_Field("@@peec", $edtVar);
		$row['BWPELV']=Decat_Field("@@pelv", $edtVar);
		$row['BWPPYC']=Decat_Field("@@ppyc", $edtVar);
		$row['BWREEC']=Decat_Field("@@reec", $edtVar);
		$row['BWRELV']=Decat_Field("@@relv", $edtVar);
		$row['BWRPYC']=Decat_Field("@@rpyc", $edtVar);
		$row['BWPHRS']=Decat_Field("@@phrs", $edtVar);
		$row['BWWHRS']=Decat_Field("@@whrs", $edtVar);
		$row['BWOPYC']=Decat_Field("@@opyc", $edtVar);
		$row['BWSHFT']=Decat_Field("@@shft", $edtVar);
		$row['BWHOLD']=Decat_Field("@@hold", $edtVar);
		$row['BWDTRC']=Decat_Field("@@dtrc", $edtVar);
		$row['BWLCOD']=Decat_Field("@@lcod", $edtVar);
		$row['BWMDPT']=Decat_Field("@@mdpt", $edtVar);
		$row['BWWC']  =Decat_Field("@@wc@@", $edtVar);
		$row['BWORD'] =Decat_Field("@@ord@", $edtVar);
		$row['BWSEQN']=Decat_Field("@@seqn", $edtVar);
		$row['BWQTYC']=Decat_Field("@@qtyc", $edtVar);
		$row['BWQTYS']=Decat_Field("@@qtys", $edtVar);
		$row['BWSCRC']=Decat_Field("@@scrc", $edtVar);
		$row['BWREWK']=Decat_Field("@@rewk", $edtVar);
		$row['BWRWRC']=Decat_Field("@@rwrc", $edtVar);
		$row['BWOPRC']=Decat_Field("@@oprc", $edtVar);
		$row['BWCMNT']=Decat_Field("@@cmnt", $edtVar);

		$errFound = "";

	} elseif ($maintenanceCode=="A") {
		// Add a Shift Transaction
		$focusField = ($HDMERL>0) ? "plt" : "str6";
		$edtVar= "";
		$wrnVar= "";
		if ($HDMERL>0) {
			$row['BWPLT']  = $homePlant;
		}
		$row['BWDTCL'] = '10';
		$row['BWSTR6'] = '';
		$row['BWSEEC'] = '';
		$row['BWSELV'] = '';
		$row['BWSPYC'] = '';
		$row['BWSTP6'] = '';
		$row['BWPEEC'] = '';
		$row['BWPELV'] = '';
		$row['BWPPYC'] = '';
		$row['BWREEC'] = '';
		$row['BWRELV'] = '';
		$row['BWRPYC'] = '';
		$row['BWPHRS'] = '';
		$row['BWOPYC'] = '';
		$row['BWWHRS'] = '';
		$row['BWSHFT'] = $homeShift;
		$row['BWHOLD'] = '';
		$row['BWCMNT'] = '';
	} elseif ($maintenanceCode=="S") {
		// Add a Schedule Exception
		$focusField = "strt";
		$edtVar= "";
		$wrnVar= "";
		$row['BWDTCL']  = '';
		$row['BWCODE1'] = '';
	} elseif ($maintenanceCode=="L") {
		// Add a Labor Transaction
		$focusField = ($HDMERL>0) ? "plt" : "dtcl";
		$edtVar= "";
		$wrnVar= "";
		$row['BWDTCL'] = ($HDMERL>0) ? '35' : '55';
		$row['BWSTR6'] = '';
		$row['BWSEEC'] = '';
		$row['BWSELV'] = '';
		$row['BWSPYC'] = '';
		$row['BWSTP6'] = '';
		$row['BWPEEC'] = '';
		$row['BWPELV'] = '';
		$row['BWPPYC'] = '';
		$row['BWREEC'] = '';
		$row['BWRELV'] = '';
		$row['BWRPYC'] = '';
		$row['BWPHRS'] = '';
		$row['BWOPYC'] = '';
		$row['BWWHRS'] = '';
		$row['BWHOLD'] = '';
		$row['BWDTRC'] = '';
		$row['BWLCOD'] = '';
		$row['BWMDPT'] = ($HDMERL>0) ? $homeMfgDept : $homeDept;
		$row['BWWC']   = ($HDMERL>0) ? $homeMfgWc   : '';
		$row['BWORD']  = '';
		$row['BWSEQN'] = '';
		$row['BWQTYC'] = '';
		$row['BWQTYS'] = '';
		$row['BWSCRC'] = '';
		$row['BWREWK'] = '';
		$row['BWRWRC'] = '';
		$row['BWOPRC'] = '';
		$row['BWCMNT'] = '';
	} else {
		$wrnVar= "";
		if ($hrsFmt=='T') {
			$row['BWPHRS']=HoursInputFromHMS($row['BWPHRST'],$TADSSF);
			$row['BWWHRS']=HoursInputFromHMS($row['BWWHRST'],$TADSSF);
		}
		$row['BWSTR6']=TimeInputFromHMS($row['BWSTR6'],$TADSSF);
		if ($row['BWAORB']=='A' || $row['BWDTCL']=='15') {
			$row['BWSTP6']=TimeInputFromHMS($row['BWSTP6'],$TADSSF);
		} else {
			$row['BWSTP6']= '';
		}
		$focusField = (trim($row['BWDTCL'])!="" && ($row['BWTYPE']=='1' || $row['BWAORB']=='A' || $row['BWDTCL']=='15')) ? "str6" : "dtcl";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	print "\n   <tr><td><input type=\"hidden\" name=\"warning\" value=\"{$wrnVar}\"></td></tr>";
	print "\n   <tr><td><input type=\"hidden\" name=\"workDate\" value=\"{$row['BWDATE']}\"></td></tr>";

	// Add a Schedule Exception
	if ($maintenanceCode=="S") {

		Build_Fld_Entry("Start Time (HHMM)","strt","inputnmbr","","strt",$row['BWSTRT'],$Err_BWSTRT,"5","4","Y","","");
		Build_Fld_Entry("Stop Time (HHMM)","stop","inputnmbr","","stop",$row['BWSTOP'],$Err_BWSTOP,"5","4","Y","","");

		$fieldDesc=RetValue("EVTYPE='X' and EVCODE='{$row['BWCODE1']}'", "HDEVNT ", "EVDESC");
		$textOvr=SetTextOvr($Err_BWCODE1);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Event Code</span></td>";
		print "\n     <td class=\"inputalph\"><input name=\"code\" type=\"text\" value=\"{$row['BWCODE1']}\" size=\"5\" maxlength=\"2\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=code&amp;fldDesc=codeDesc&amp;fldType=E\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
		print "\n                             <span class=\"dspdesc\" id=\"codeDesc\">$fieldDesc</span>";
		print "\n     </td>";
		DspErrMsg($Err_BWCODE1);

	} else {
		// Maintain Shift or Labor Transaction
		if ($HDMERL>0) {
			$fieldDesc=RetValue("PLPLNT={$row['BWPLT']}", "HDPLNT ", "PLNAME");
			$textOvr=SetTextOvr($Err_BWPLT);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Plant Number</span></td>";
			if ($maintenanceCode=="A") {
				print "\n     <td class=\"inputnmbr\"><input name=\"plt\" type=\"text\" value=\"{$row['BWPLT']}\" size=\"5\" maxlength=\"3\">";
				print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=plt&amp;fldDesc=pltDesc\" onclick=\"$searchWinVar\"> $searchImage </a> ";
			} else {
				print "\n     <td class=\"inputnmbr\"><input name=\"plt\" type=\"hidden\" value=\"{$row['BWPLT']}\">{$row['BWPLT']}";
			}
			print "\n         <span class=\"dspdesc\" id=\"pltDesc\">$fieldDesc</span>";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWPLT);
		}

		if (trim($row['BWDTCL'])!="") {
			$fieldDesc=RetValue("DFCODE='{$row['BWDTCL']}'", "ETDCDF", "DFDESC");
			$textOvr=SetTextOvr($Err_BWDTCL);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Data Collection Code</span></td> ";
			if ($maintenanceCode=="A" || $maintenanceCode=="L" || $maintenanceCode=="C" && $row['BWAORB']=="B" && $row['BWTYPE']!="1") {
				print "\n     <td class=\"inputalph\"><input name=\"dtcl\" type=\"text\" value=\"{$row['BWDTCL']}\" size=\"5\" maxlength=\"2\">";
			} else {
				print "\n     <td class=\"inputalph\"><input name=\"dtcl\" type=\"hidden\" value=\"{$row['BWDTCL']}\">{$row['BWDTCL']}";
			}
			print "\n     <span class=\"dspdesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
		} elseif ($row['BWTYPE']=="4") {
			Build_DspFld("Dead Time","","","");
		}
		DspErrMsg($Err_BWDTCL);

		// Start Time, Stop Time, Exception Edit and Pay Codes
		print "\n </table> ";

		print "\n <table $contentTable>";
		print "\n   <tr>";
		print "\n     <th>&nbsp;</th>";
		if ($row['BWTYPE']!="4") {
			if ($TADSSF == "Y") {
				print "\n     <th class=\"grphdr\">Time<br>(HHMMSS)</th>";
			} else {
				print "\n     <th class=\"grphdr\">Time<br>(HHMM)</th>";
			}
		} else {
			print "\n     <th>&nbsp;</th>";

		}
		print "\n     <th class=\"grphdr\">Exception Code</th>";
		print "\n     <th class=\"grphdr\">Error Level</th>";
		print "\n     <th class=\"grphdr\">Pay Code</th>";
		print "\n   </tr>";

		if ($row['BWTYPE']!="4") {
			print "\n   <tr><td class=\"dsphdr\">Start</td>";
			Build_Fld_Entry("Start","str6","inputnmbr","","str6",$row['BWSTR6'],$Err_BWSTR6,"6",$timeMax,"","","Y");

			$fieldDesc=RetValue("EVTYPE='X' and EVCODE='{$row['BWSEEC']}'", "HDEVNT ", "EVDESC");
			$textOvr=SetTextOvr($Err_BWSEEC);
			print "\n     <td class=\"inputalph\"><input name=\"seec\" type=\"text\" value=\"{$row['BWSEEC']}\" size=\"5\" maxlength=\"2\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=seec&amp;fldDesc=seecDesc&amp;fldType=X\" onclick=\"$searchWinVar\"> $searchImage</a> ";
			print "\n                             <span class=\"dspdesc\" id=\"seecDesc\">$fieldDesc</span>";
			print "\n     </td>";

			print "\n     <td class=\"colcode\"><input name=\"selv\" type=\"hidden\" value=\"{$row['BWSELV']}\">{$row['BWSELV']}</td>";

			$fieldDesc=RetValue("C2COMP={$row['BWCOMP']} and C2FACL={$row['BWFACL']} and C2CODE='{$row['BWSPYC']}'", "PRCODE", "C2DESC");
			$textOvr=SetTextOvr($Err_BWSPYC);
			print "\n     <td class=\"inputalph\"><input name=\"spyc\" type=\"text\" value=\"{$row['BWSPYC']}\" size=\"5\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spyc&amp;fldDesc=spycDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
			print "\n                             <span class=\"dspdesc\" id=\"spycDesc\">$fieldDesc</span>";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWSTR6);
			DspErrMsg($Err_BWSEEC);
			DspErrMsg($Err_BWSPYC);

			print "\n   <tr><td class=\"dsphdr\">Stop</td>";
			Build_Fld_Entry("Stop","stp6","inputnmbr","","stp6",$row['BWSTP6'],$Err_BWSTP6,"6",$timeMax,"","","Y");

			$fieldDesc=RetValue("EVTYPE='X' and EVCODE='{$row['BWPEEC']}'", "HDEVNT ", "EVDESC");
			$textOvr=SetTextOvr($Err_BWPEEC);
			print "\n     <td class=\"inputalph\"><input name=\"peec\" type=\"text\" value=\"{$row['BWPEEC']}\" size=\"5\" maxlength=\"2\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=peec&amp;fldDesc=peecDesc&amp;fldType=X\" onclick=\"$searchWinVar\"> $searchImage</a> ";
			print "\n                             <span class=\"dspdesc\" id=\"peecDesc\">$fieldDesc</span>";
			print "\n     </td>";

			print "\n     <td class=\"colcode\"><input name=\"pelv\" type=\"hidden\" value=\"{$row['BWPELV']}\">{$row['BWPELV']}</td>";

			$fieldDesc=RetValue("C2COMP={$row['BWCOMP']} and C2FACL={$row['BWFACL']} and C2CODE='{$row['BWPPYC']}'", "PRCODE", "C2DESC");
			$textOvr=SetTextOvr($Err_BWPPYC);
			print "\n     <td class=\"inputalph\"><input name=\"ppyc\" type=\"text\" value=\"{$row['BWPPYC']}\" size=\"5\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=ppyc&amp;fldDesc=ppycDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
			print "\n                             <span class=\"dspdesc\" id=\"ppycDesc\">$fieldDesc</span>";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWSTP6);
			DspErrMsg($Err_BWPEEC);
			DspErrMsg($Err_BWPPYC);
		}

		print "\n   <tr><td class=\"dsphdr\">Record Edit</td>";
		print "\n       <td>&nbsp;</td>";

		$fieldDesc=RetValue("EVTYPE='X' and EVCODE='{$row['BWREEC']}'", "HDEVNT ", "EVDESC");
		$textOvr=SetTextOvr($Err_BWREEC);
		print "\n     <td class=\"inputalph\"><input name=\"reec\" type=\"text\" value=\"{$row['BWREEC']}\" size=\"5\" maxlength=\"2\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=reec&amp;fldDesc=reecDesc&amp;fldType=X\" onclick=\"$searchWinVar\"> $searchImage</a> ";
		print "\n                             <span class=\"dspdesc\" id=\"reecDesc\">$fieldDesc</span>";
		print "\n     </td>";

		print "\n     <td class=\"colcode\"><input name=\"relv\" type=\"hidden\" value=\"{$row['BWRELV']}\">{$row['BWRELV']}</td>";

		$fieldDesc=RetValue("C2COMP={$row['BWCOMP']} and C2FACL={$row['BWFACL']} and C2CODE='{$row['BWRPYC']}'", "PRCODE", "C2DESC");
		$textOvr=SetTextOvr($Err_BWRPYC);
		print "\n     <td class=\"inputalph\"><input name=\"rpyc\" type=\"text\" value=\"{$row['BWRPYC']}\" size=\"5\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=rpyc&amp;fldDesc=rpycDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
		print "\n                             <span class=\"dspdesc\" id=\"rpycDesc\">$fieldDesc</span>";
		print "\n     </td>";
		print "\n </tr> ";
		DspErrMsg($Err_BWREEC);
		DspErrMsg($Err_BWRPYC);
		print "\n </table>";

		print "\n <table $contentTable>";

		if ($pdwk=="P") {
			$textOvr=SetTextOvr($Err_BWPHRS);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Paid Hours $hoursFormat</span></td>";
			print "\n     <td class=\"inputnmbr\"><input name=\"phrs\" type=\"text\" value=\"{$row['BWPHRS']}\" size=\"10\" maxlength=\"$hoursMax\">";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWPHRS);

			$fieldDesc=RetValue("C2CODE='{$row['BWOPYC']}'", "PRCODE", "C2DESC");
			$textOvr=SetTextOvr($Err_BWOPYC);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Paid Hours Pay Code</span></td>";
			print "\n     <td class=\"inputalph\"><input name=\"opyc\" type=\"text\" value=\"{$row['BWOPYC']}\" size=\"5\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=opyc&amp;fldDesc=opycDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
			print "\n                             <span class=\"dspdesc\" id=\"opycDesc\">$fieldDesc</span>";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWOPYC);
		} else {
			$textOvr=SetTextOvr($Err_BWWHRS);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Worked Hours $hoursFormat</span></td>";
			print "\n     <td class=\"inputnmbr\"><input name=\"whrs\" type=\"text\" value=\"{$row['BWWHRS']}\" size=\"10\" maxlength=\"$hoursMax\">";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWWHRS);
		}

		Build_Flag_Entry("Shift","shft","SHIFTL","shft",$row['BWSHFT'],$Err_BWSHFT,"1","1","","","");

		Build_Flag_Entry("Hold","hold","LBHOLD","hold",$row['BWHOLD'],$Err_BWHOLD,"1","1","","","");

		if ($row['BWTYPE']=="1" && $maintenanceCode=="L" || $row['BWTYPE']=="2" || $row['BWTYPE']=="3") {
			$fieldDesc=RetValue("EVTYPE='I' and EVCODE='{$row['BWDTRC']}'", "HDEVNT ", "EVDESC");
			$textOvr=SetTextOvr($Err_BWDTRC);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Indirect/Downtime Code</span></td>";
			print "\n     <td class=\"inputalph\"><input name=\"dtrc\" type=\"text\" value=\"{$row['BWDTRC']}\" size=\"5\" maxlength=\"2\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=dtrc&amp;fldDesc=dtrcDesc&amp;fldType=I\" onclick=\"$searchWinVar\"> $searchImage</a> ";
			print "\n                             <span class=\"dspdesc\" id=\"dtrcDesc\">$fieldDesc</span>";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWDTRC);

			$fieldDesc=RetValue("LRLCOD='{$row['BWLCOD']}'", "SIMLRC ", "LRADES");
			$textOvr=SetTextOvr($Err_BWLCOD);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Labor Code</span></td>";
			print "\n     <td class=\"inputalph\"><input name=\"lcod\" type=\"text\" value=\"{$row['BWLCOD']}\" size=\"5\" maxlength=\"2\">";
			if ($HDMERL>0) {print "\n                             <a href=\"{$homeURL}{$phpPath}LaborCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=lcod&amp;fldDesc=lcodDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";}
			print "\n                             <span class=\"dspdesc\" id=\"lcodDesc\">$fieldDesc</span>";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWLCOD);

			if ($HDMERL>0) {
				$fieldDesc=RetValue("WCPLT={$row['BWPLT']} and WCDEPT='{$row['BWMDPT']}' and WCWC='{$row['BWWC']}'", "HDMWCM ", "WCDESC");
				$textOvr=SetTextOvr($Err_BWMDPT);
				print "\n <tr><td class=\"dsphdr\"><span $textOvr>Department/Work Center</span></td>";
				print "\n     <td class=\"inputalph\"><input name=\"dept\" type=\"text\" value=\"{$row['BWMDPT']}\" size=\"5\" maxlength=\"5\"> / <input name=\"workCenter\" type=\"text\" value=\"{$row['BWWC']}\" size=\"5\" maxlength=\"5\">";
				print "\n                             <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;tag=REPORT&amp;forPlant={$row['BWPLT']}&amp;docName=Chg&amp;fldPlant=&amp;fldPltName=&amp;flddept=dept&amp;fldWC=workCenter&amp;fldDesc=deptDesc\" onclick=\"$searchWinVar\"> $searchImage </a> ";
				print "\n                             <span class=\"dspdesc\" id=\"deptDesc\">$fieldDesc</span>";
				print "\n     </td>";
				print "\n </tr> ";
				DspErrMsg($Err_BWMDPT);
			} elseif (($HDPERL>0 || $HDPRRL>0)) {
				$fieldDesc=RetValue("EADEPT='{$row['BWMDPT']}'", "PREXAC ", "EANAME");
				$textOvr=SetTextOvr($Err_BWMDPT);
				print "\n <tr><td class=\"dsphdr\"><span $textOvr>Department</span></td>";
				print "\n     <td class=\"inputalph\"><input name=\"dept\" type=\"text\" value=\"{$row['BWMDPT']}\" size=\"5\" maxlength=\"5\">";
				print "\n                             <a href=\"{$homeURL}{$phpPath}DepartmentSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=dept&amp;fldDesc=deptDesc\" onclick=\"$searchWinVar\"> $searchImage </a> ";
				print "\n                             <span class=\"dspdesc\" id=\"deptDesc\">$fieldDesc</span>";
				print "\n     </td>";
				print "\n </tr> ";
				DspErrMsg($Err_BWMDPT);
			}

			$textOvr=SetTextOvr($Err_BWORD);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Order Number</span></td>";
			print "\n     <td class=\"inputalph\"><input name=\"ord\" type=\"text\" value=\"{$row['BWORD']}\" size=\"10\" maxlength=\"9\">";
			if ($HDMERL>0) {print "\n                             <a href=\"{$homeURL}{$phpPath}LaborInProcessSearch.php{$genericVarBase}&amp;forPlant=0&amp;docName=Chg&amp;fldorder=ord&amp;fldseqn=seqn\" onclick=\"$searchWinVar\"> $searchImage</a> ";}
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWORD);

			$textOvr=SetTextOvr($Err_BWSEQN);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Routing Sequence</span></td>";
			print "\n     <td class=\"inputnmbr\"><input name=\"seqn\" type=\"text\" value=\"{$row['BWSEQN']}\" size=\"5\" maxlength=\"3\">";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWSEQN);

			$textOvr=SetTextOvr($Err_BWQTYC);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Complete Quantity</span></td>";
			print "\n     <td class=\"inputnmbr\"><input name=\"qtyc\" type=\"text\" value=\"{$row['BWQTYC']}\" size=\"10\" maxlength=\"15\">";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWQTYC);

			$textOvr=SetTextOvr($Err_BWQTYS);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Scrap Quantity</span></td>";
			print "\n     <td class=\"inputnmbr\"><input name=\"qtys\" type=\"text\" value=\"{$row['BWQTYS']}\" size=\"10\" maxlength=\"15\">";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWQTYS);

			$fieldDesc=RetValue("TTTYPE='{$row['BWSCRC']}'", "HDTTYP", "TTDESC");
			$textOvr=SetTextOvr($Err_BWSCRC);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Scrap Reason</span></td>";
			print "\n     <td class=\"inputalph\"><input name=\"scrc\" type=\"text\" value=\"{$row['BWSCRC']}\" size=\"5\" maxlength=\"2\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}TransactionTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=scrc&amp;fldDesc=scrcDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
			print "\n                             <span class=\"dspdesc\" id=\"scrcDesc\">$fieldDesc</span>";
			print "\n     </td>";
			print "\n </tr> ";
			DspErrMsg($Err_BWSCRC);

			if ($TADPRW=="Y") {
				$textOvr=SetTextOvr($Err_BWREWK);
				print "\n <tr><td class=\"dsphdr\"><span $textOvr>Rework Quantity</span></td>";
				print "\n     <td class=\"inputnmbr\"><input name=\"rewk\" type=\"text\" value=\"{$row['BWREWK']}\" size=\"10\" maxlength=\"15\">";
				print "\n     </td>";
				print "\n </tr> ";
				DspErrMsg($Err_BWREWK);

				$fieldDesc=RetValue("TTTYPE='{$row['BWRWRC']}'", "HDTTYP", "TTDESC");
				$textOvr=SetTextOvr($Err_BWRWRC);
				print "\n <tr><td class=\"dsphdr\"><span $textOvr>Rework Reason</span></td>";
				print "\n     <td class=\"inputalph\"><input name=\"rwrc\" type=\"text\" value=\"{$row['BWRWRC']}\" size=\"5\" maxlength=\"2\">";
				print "\n                             <a href=\"{$homeURL}{$phpPath}TransactionTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=rwrc&amp;fldDesc=rwrcDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
				print "\n                             <span class=\"dspdesc\" id=\"rwrcDesc\">$fieldDesc</span>";
				print "\n     </td>";
				print "\n </tr> ";
				DspErrMsg($Err_BWRWRC);
			}

			if ($HDMERL>0) {
				Build_Flag_Entry("Operation Complete","oprc","BY","oprc",$row['BWOPRC'],$Err_BWOPRC,"1","1","","","");
			}
		}
		Build_Fld_Entry("Comment","cmnt","inputalph","","cmnt",$row['BWCMNT'],$Err_BWCMNT,"20","40","","","");
	}

	print "\n </table>";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.$focusField.focus();";
	print "\n </script>";
	print "\n </form>";
	require_once 'MaintainBottom.php';
	print $hrTagAttr;
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {

	if ($maintenanceCode=="D") {
		$_POST['fromEmid']	= $fromEmid;
		$_POST['fromRwid']	= $fromRwid;
		$_POST['pdwk']		= $pdwk;
		$_POST['workDate']	= $fromDate;
	}

	$wrnVar   = (isset($_POST['warning'])) ? $_POST['warning'] : "";
	$workDate = (isset($_POST['workDate'])) ? $_POST['workDate'] : "";

	$edtVar= "";
	Concat_Field("@@feid", $fromEmid);
	Concat_Field("@@frid", $fromRwid);
	Concat_Field("@@pdwk", $pdwk);
	Concat_Field("@@hfmt", $hrsFmt);

	if (isset($_POST['plt'])) {Concat_Field("@@plt@", $_POST['plt']);}
	if (isset($_POST['dtcl'])) {Concat_Field("@@dtcl", $_POST['dtcl']);}
	if (isset($_POST['strt'])) {Concat_Field("@@strt", $_POST['strt']);}
	if (isset($_POST['stop'])) {Concat_Field("@@stop", $_POST['stop']);}
	if (isset($_POST['code'])) {Concat_Field("@@code", strtoupper($_POST['code']));}
	if (isset($_POST['str6'])) {Concat_Field("@@str6", $_POST['str6']);}
	if (isset($_POST['seec'])) {Concat_Field("@@seec", strtoupper($_POST['seec']));}
	if (isset($_POST['selv'])) {Concat_Field("@@selv", strtoupper($_POST['selv']));}
	if (isset($_POST['spyc'])) {Concat_Field("@@spyc", strtoupper($_POST['spyc']));}
	if (isset($_POST['stp6'])) {Concat_Field("@@stp6", $_POST['stp6']);}
	if (isset($_POST['peec'])) {Concat_Field("@@peec", strtoupper($_POST['peec']));}
	if (isset($_POST['pelv'])) {Concat_Field("@@pelv", strtoupper($_POST['pelv']));}
	if (isset($_POST['ppyc'])) {Concat_Field("@@ppyc", strtoupper($_POST['ppyc']));}
	if (isset($_POST['reec'])) {Concat_Field("@@reec", strtoupper($_POST['reec']));}
	if (isset($_POST['relv'])) {Concat_Field("@@relv", strtoupper($_POST['relv']));}
	if (isset($_POST['rpyc'])) {Concat_Field("@@rpyc", strtoupper($_POST['rpyc']));}
	if (isset($_POST['phrs'])) {Concat_Field("@@phrs", $_POST['phrs']);}
	if (isset($_POST['whrs'])) {Concat_Field("@@whrs", $_POST['whrs']);}
	if (isset($_POST['opyc'])) {Concat_Field("@@opyc", strtoupper($_POST['opyc']));}
	if (isset($_POST['shft'])) {Concat_Field("@@shft", $_POST['shft']);}
	if (isset($_POST['hold'])) {Concat_Field("@@hold", strtoupper($_POST['hold']));}
	if (isset($_POST['dtrc'])) {Concat_Field("@@dtrc", strtoupper($_POST['dtrc']));}
	if (isset($_POST['lcod'])) {Concat_Field("@@lcod", strtoupper($_POST['lcod']));}
	if (isset($_POST['dept'])) {Concat_Field("@@mdpt", strtoupper($_POST['dept']));}
	if (isset($_POST['workCenter'])) {Concat_Field("@@wc@@", strtoupper($_POST['workCenter']));}
	if (isset($_POST['ord'])) {Concat_Field("@@ord@", strtoupper($_POST['ord']));}
	if (isset($_POST['seqn'])) {Concat_Field("@@seqn", $_POST['seqn']);}
	if (isset($_POST['qtyc'])) {Concat_Field("@@qtyc", $_POST['qtyc']);}
	if (isset($_POST['qtys'])) {Concat_Field("@@qtys", $_POST['qtys']);}
	if (isset($_POST['scrc'])) {Concat_Field("@@scrc", strtoupper($_POST['scrc']));}
	if (isset($_POST['rewk'])) {Concat_Field("@@rewk", $_POST['rewk']);}
	if (isset($_POST['rwrc'])) {Concat_Field("@@rwrc", strtoupper($_POST['rwrc']));}
	if (isset($_POST['oprc'])) {Concat_Field("@@oprc", $_POST['oprc']);}
	if (isset($_POST['cmnt'])) {Concat_Field("@@cmnt", $_POST['cmnt']);}

	$edtVar .= "}{";

	$returnValue=Maintain_Edit_Handle("HETBLM_W", $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "" || $errFound == "C") {
		$confMessage =Decat_Field("@@cmsg", $errVar);
		if ($workDate) {
			$F_MWDATE = Format_Code(trim(Format_Date_ISO($workDate, "D")));
			$dateDesc = date("l",strtotime($workDate));
			$confMessage .=  " on $dateDesc $F_MWDATE" ;
		}
//		$confMessage= str_replace("'", "&acute", $confMessage );
//		$confMessage= str_replace('"', "&quot", $confMessage);
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif ($maintenanceCode == "D") {
		$Err_BWDTCL=DecatErr_Field("@@dtcl", "dtcl");
		$confMessage=Format_ConfMsg_Desc("", $Err_BWDTCL, "", "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		WrnVarErr($profileHandle, $wrnVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>