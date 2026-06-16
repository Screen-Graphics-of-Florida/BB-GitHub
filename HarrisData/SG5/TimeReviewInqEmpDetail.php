<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';


$reportSelType      =	(isset($_GET['reportSelType']))	     ?	$_GET['reportSelType']	    :	null;
$fromEmid           =	(isset($_GET['fromEmid']))	         ?	$_GET['fromEmid']	        :	0;
$fromRwid           =	(isset($_GET['fromRwid']))	         ?	$_GET['fromRwid']	        :	0;
$fromDate        	=	(isset($_GET['fromDate']))	    	 ?	$_GET['fromDate']	        :	null;
$fromType        	=	(isset($_GET['fromType']))	    	 ?	$_GET['fromType']	        :	null;
$fromCald        	=	(isset($_GET['fromCald']))	    	 ?	$_GET['fromCald']	        :	null;
$fromStrt        	=	(isset($_GET['fromStrt']))	    	 ?	$_GET['fromStrt']	        :	0;
$fromMdpt        	=	(isset($_GET['fromMdpt']))	    	 ?	$_GET['fromMdpt']	        :	"";
$fromWc	        	=	(isset($_GET['fromWc']))	    	 ?	$_GET['fromWc']	       	 	:	"";
$fromOrd        	=	(isset($_GET['fromOrd']))	    	 ?	$_GET['fromOrd']	        :	"";
$fromSeqn        	=	(isset($_GET['fromSeqn']))	    	 ?	$_GET['fromSeqn']	        :	0;
$fromShft        	=	(isset($_GET['fromShft']))	    	 ?	$_GET['fromShft']	        :	0;
$fromRecs        	=	(isset($_GET['fromRecs']))	    	 ?	$_GET['fromRecs']	        :	0;
$fromSctl        	=	(isset($_GET['fromSctl']))	    	 ?	$_GET['fromSctl']	        :	0;
$pdwk      			=	(isset($_GET['pdwk']))	     		 ?	$_GET['pdwk']	    		:	null;

$hfmt  				=	(isset($_SESSION['hrsFmt']))	 	 ?	$_SESSION['hrsFmt']			:	$TAHWKF;

require_once 'SetLibraryList.php';
require_once "ETControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'ETRetInfo.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'WildCardAcctInclude.php';

$page_title            = "Time Review Inquiry: Detail";
$scriptName            = "TimeReviewInqEmpDetail.php";
$scriptVarBase         = "{$genericVarBase}&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$filterURL    		   = "{$scriptName}{$scriptVarBase}";
$submitCallProgram     = "HETBLI";
$submitEnvProgram      = "HETBLI";
$submitEnvPrinter      = "HETBLIPF";
$submitScheduleScript  = "";
$applicationID         = "ET";
$dspMaxRows            = 500;


//$stmtSQLd .= " and BWTYPE='$fromType' and BWCALD='$fromCald' and BWSTRT = $fromStrt ";

$rowCount = 0;
$stmtSQL = "";
$stmtSQL .= " Select * from ETBLWK04   ";
$stmtSQL .= " left join HREMPL on EMEMID=BWEMID ";
$stmtSQL .= " Where BWEMID=$fromEmid and BWXHND='$profileHandle' and BWDATE = '$fromDate'";
$stmtSQL .= " For Fetch Only with NC Optimize For $dspMaxRows Rows ";

$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

while ($rowd = db2_fetch_assoc($sqlResult)){
	if ($rowCount >= $dspMaxRows) {break;}

	if ($rowd['BWTYPE'] == '0') {
		$wrk0Bwcomp = $rowd['BWCOMP'];
		$wrk0Bwfacl = $rowd['BWFACL'];
		$wrk0Bwempl = $rowd['BWEMPL'];
		$wrk0Bwdate = $rowd['BWDATE'];
		$wrk0Bwcald = $rowd['BWCALD'];
		$wrk0Bwstrt = $rowd['BWSTRT'];
		$wrk0Bwstop = $rowd['BWSTOP'];
		$wrk0Bwehrs = $rowd['BWEHRS'];
		$wrk0Bwehrst = $rowd['BWEHRST'];
		$wrk0Bwseec = $rowd['BWSEEC'];
		$wrk0Bwselv = $rowd['BWSELV'];
		$wrk0Bwspyc = $rowd['BWSPYC'];
		$wrk0Bwpeec = $rowd['BWPEEC'];
		$wrk0Bwpelv = $rowd['BWPELV'];
		$wrk0Bwppyc = $rowd['BWPPYC'];
		$wrk0Bwreec = $rowd['BWREEC'];
		$wrk0Bwrelv = $rowd['BWRELV'];
		$wrk0Bwrpyc = $rowd['BWRPYC'];
		$wrk0Bwgrp 	= $rowd['BWGRP'];
	} elseif ($rowd['BWTYPE'] =='1' && $rowd['BWSCTL'] == $fromSctl) {
		$wrk1Bwdate = $rowd['BWDATE'];
		$wrk1Bwschd = $rowd['BWSCHD'];
		$wrk1Bwcald = $rowd['BWCALD'];
		$wrk1Bwstrt = $rowd['BWSTR6'];
		$wrk1Bwstop = $rowd['BWSTP6'];
		$wrk1Bwehrs = $rowd['BWEHRS'];
		$wrk1Bwehrst = $rowd['BWEHRST'];
		$wrk1Bwphrs = $rowd['BWPHRS'];
		$wrk1Bwphrst = $rowd['BWPHRST'];
		$wrk1Bwwhrs = $rowd['BWWHRS'];
		$wrk1Bwwhrst = $rowd['BWWHRST'];
		$wrk1Bwdtcl = $rowd['BWDTCL'];
		$wrk1Bwmdpt = $rowd['BWMDPT'];
		$wrk1Bwplt = $rowd['BWPLT'];
		$wrk1Bwwc = $rowd['BWWC'];
		$wrk1Bwgrp = $rowd['BWGRP'];
		$wrk1Bword = $rowd['BWORD'];
		$wrk1Bwseec = $rowd['BWSEEC'];
		$wrk1Bwselv = $rowd['BWSELV'];
		$wrk1Bwspyc = $rowd['BWSPYC'];
		$wrk1Bwpeec = $rowd['BWPEEC'];
		$wrk1Bwpelv = $rowd['BWPELV'];
		$wrk1Bwppyc = $rowd['BWPPYC'];
		$wrk1Bwreec = $rowd['BWREEC'];
		$wrk1Bwrelv = $rowd['BWRELV'];
		$wrk1Bwrpyc = $rowd['BWRPYC'];
		$wrk1Bwseqn = $rowd['BWSEQN'];
		$wrk1Bwshft = $rowd['BWSHFT'];
		$wrk1Bwrecs = $rowd['BWRECS'];
	} elseif (($rowd['BWTYPE'] =='2' || $rowd['BWTYPE'] =='3') && $rowd['BWRWID'] == $fromRwid) {
		$wrk2Bwdate = $rowd['BWDATE'];
		$wrk2Bwschd = $rowd['BWSCHD'];
		$wrk2Bwcald = $rowd['BWCALD'];
		$wrk2Bwstrt = $rowd['BWSTR6'];
		$wrk2Bwstop = $rowd['BWSTP6'];
		$wrk2Bwehrs = $rowd['BWEHRS'];
		$wrk2Bwehrst = $rowd['BWEHRST'];
		$wrk2Bwwhrs = $rowd['BWWHRS'];
		$wrk2Bwwhrst = $rowd['BWWHRST'];
		$wrk2Bwphrs = $rowd['BWPHRS'];
		$wrk2Bwphrst = $rowd['BWPHRST'];
		$wrk2Bwdtcl = $rowd['BWDTCL'];
		$wrk2Bwmdpt = $rowd['BWMDPT'];
		$wrk2Bwplt = $rowd['BWPLT'];
		$wrk2Bwwc = $rowd['BWWC'];
		$wrk2Bwgrp = $rowd['BWGRP'];
		$wrk2Bwdtrc = $rowd['BWDTRC'];
		$wrk2Bword = $rowd['BWORD'];
		$wrk2Bwseqn = $rowd['BWSEQN'];
		$wrk2Bwlcod = $rowd['BWLCOD'];
		$wrk2Bwseec = $rowd['BWSEEC'];
		$wrk2Bwselv = $rowd['BWSELV'];
		$wrk2Bwspyc = $rowd['BWSPYC'];
		$wrk2Bwpeec = $rowd['BWPEEC'];
		$wrk2Bwpelv = $rowd['BWPELV'];
		$wrk2Bwppyc = $rowd['BWPPYC'];
		$wrk2Bwreec = $rowd['BWREEC'];
		$wrk2Bwrelv = $rowd['BWRELV'];
		$wrk2Bwrpyc = $rowd['BWRPYC'];
		$wrk2Bwqtyc = $rowd['BWQTYC'];
		$wrk2Bwqtys = $rowd['BWQTYS'];
		$wrk2Bwscrc = $rowd['BWSCRC'];
		$wrk2Bwrewk = $rowd['BWREWK'];
		$wrk2Bwrwrc = $rowd['BWRWRC'];
		$wrk2Bwcmnt = $rowd['BWCMNT'];
		$wrk2Bwshft = $rowd['BWSHFT'];
		$wrk2Bwrecs = $rowd['BWRECS'];
	} elseif ($rowd['BWTYPE'] =='4' && $rowd['BWRWID'] == $fromRwid) {
		$wrk4Bwdate = $rowd['BWDATE'];
		$wrk4Bwschd = $rowd['BWSCHD'];
		$wrk4Bwcald = $rowd['BWCALD'];
		$wrk4Bwstrt = $rowd['BWSTR6'];
		$wrk4Bwstop = $rowd['BWSTP6'];
		$wrk4Bwehrs = $rowd['BWEHRS'];
		$wrk4Bwehrst = $rowd['BWEHRST'];
		$wrk4Bwdtcl = $rowd['BWDTCL'];
		$wrk4Bwmdpt = $rowd['BWMDPT'];
		$wrk4Bwwc 	= $rowd['BWWC'];
		$wrk4Bwgrp 	= $rowd['BWGRP'];
		$wrk4Bwdtrc = $rowd['BWDTRC'];
		$wrk4Bword 	= $rowd['BWORD'];
		$wrk4Bwseec = $rowd['BWSEEC'];
		$wrk4Bwselv = $rowd['BWSELV'];
		$wrk4Bwspyc = $rowd['BWSPYC'];
		$wrk4Bwpeec = $rowd['BWPEEC'];
		$wrk4Bwpelv = $rowd['BWPELV'];
		$wrk4Bwppyc = $rowd['BWPPYC'];
		$wrk4Bwreec = $rowd['BWREEC'];
		$wrk4Bwrelv = $rowd['BWRELV'];
		$wrk4Bwrpyc = $rowd['BWRPYC'];
		$wrk4Bwseqn = $rowd['BWSEQN'];
		$wrk4Bwshft = $rowd['BWSHFT'];
		$wrk4Bwrecs = $rowd['BWRECS'];
	} elseif ($rowd['BWTYPE'] =='5') {
		$wrk5Bwscvr = $rowd['BWSCVR'];
		$wrk5Bwscvrt = $rowd['BWSCVRT'];
	}

	$rowCount ++;

}

if ($tag == "REPORT") {

	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);
	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n var optionWin;";
	require_once 'CheckSel.js';
	require_once 'Menu.js';

	require_once 'NoFormValidate.php';
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
	$pageID = "TIMEREVIEWINQEMPDETAIL";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\" nowrap>";

	// Employee Information Section
	$employeeRetInfo = ETRetInfo_Employee($fromEmid);
	$employeeName    = $employeeRetInfo['employeeName'];
	$scheduleNum     = $employeeRetInfo['scheduleNum'];
	$scheduleDesc    = $employeeRetInfo['scheduleDesc'];
	$employeeInfo    = $employeeRetInfo['employeeInfo'];
	$fromHmWk	     = "H";
	$scheduleRetInfo = ETRetInfo_Schedule($fromEmid, $scheduleNum, $fromDept, $fromGrp, $fromPlt, $fromDbpt, $fromWc, $wrk0Bwdate, $fromHmWk);
	$scheduleInfo    = $scheduleRetInfo['scheduleInfo'];
	print "\n <table $contentTable style=\"float:left;\"> ";
	Format_Header_Hover("Employee", $employeeName, "","employeeSelection");
	//Format_Header("Schedule", $scheduleDesc, $scheduleNum);
	Format_Header_Hover("Schedule", $scheduleDesc, $scheduleNum,"scheduleSelection");
	$F_BWDATE=Format_Date_ISO($wrk0Bwdate, "D");
	$dateDesc =  date("l",strtotime($wrk0Bwdate));
	//Build_DspFld("Work Date:","$F_BWDATE  $dateDesc","","A");
	Format_Header("Work Date", $F_BWDATE, $dateDesc);
	print "\n </table> ";

	// Page Title
	print "\n <h1 style=\"float:left; margin-left:5ex; margin-right:5ex;\">$page_title</h1>";

	// Banner Icon Section
	print "\n <div class=\"colicon\" style=\"float:left;\"> ";
	if ($backURL != "") {print "\n <a href=\"$backURL\">$cancelImageMed</a>";}
	else                {print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";}

	$medIcon = "Y";
	require_once 'HelpPage.php';
	print "\n </div> ";

	// Reset float
	print "\n <br style=\"clear:both;\"> ";

	// Hidden Divisions for Employee and Schedule
	print "\n <div id=\"employeeSelection\" class=\"moreInfo\">{$employeeInfo}</div>";
	print "\n <div id=\"scheduleSelection\" class=\"moreInfo\">{$scheduleInfo}</div>";
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;

	print "\n <div id=\"PaidWorked\">";
	print "\n <table $contentTable> ";
	print "\n <tr>";
	print "\n     <td class=\"hdrtitl\">&nbsp;</td> " ;
	print "\n</tr>";
	print "\n <tr>";
	if ($pdwk == 'P') {
		print "\n     <td class=\"hdrtitl\">Paid Hours</td> " ;
	} else {
		print "\n     <td class=\"hdrtitl\">Worked Hours</td> " ;
	}
	print "\n </tr>";
	print "\n <tr>";
	print "\n     <td class=\"hdrtitl\">&nbsp;</td> " ;
	print "\n </tr>";
	print "\n </table> ";
	print "\n </div>";

	print "\n <div id=\"SelectFormat\">";
	print "\n <table $contentTable> ";

	print "\n     <tr><td class=\"dsphdr\">Display Hours Format: </td>";
	if ($hfmt == "D") {
		print "\n     <td class=\"dspalph\">Decimal</td>";
	} else {
		print "\n     <td class=\"dspalph\">Time</td>";
	}
	print "\n     </tr> " ;

	print "\n     <tr><td class=\"dsphdr\">Employee/Actual Worked: </td>";
	if ($reportSelType == "E") {
		print "\n     <td class=\"dspalph\">Employee</td>";
	} else {
		print "\n     <td class=\"dspalph\">Actual Worked</td>";
	}
	print "\n     </tr> " ;

	print "\n </table> ";
	print "\n </div>";

	$desc = "";
	if ($fromType == '1') {
		$desc = "Shift";
	} elseif ($fromType == '2') {
		$desc = "Employee";
	} elseif ($fromType == '3') {
		$desc = "Adjustment";
	} elseif ($fromType == '4') {
		$desc = "Dead Time";
	}

	// Detail (hidden DIV)
	print "\n <a name=\"Detail\"></a> ";
	print "\n <div id=\"showDetail\">";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">$desc Detail ";
	Print "\n </legend> ";
	//require 'TopOfForm.php';
	print "\n <table $contentTable> ";

	if ($HDMERL > 0 ) {
		$F_BWPLT=Format_Code($wrk1Bwplt);
		$pltDesc = RetValue("PLPLNT=$wrk1Bwplt ", "HDPLNT", "PLNAME");
		Build_DspFld("Plant:","$pltDesc   $F_BWPLT","","A");
	}
	//$schdDesc = RetValue("SMSCHD=$wrk1Bwschd ", "HDSCHM", "SMDESC");
	//$F_BWSCHD=Format_Code($wrk1Bwschd);
	//Build_DspFld("Schedule:","$schdDesc   $wrk1Bwschd","","A");
	$fromHmWk	     = "W";
	if ($fromType == '1') {
		if ($HDMERL > 0 ) {
			$scheduleRetInfo = ETRetInfo_Schedule($fromEmid, $wrk1Bwschd, $fromDept, $wrk1Bwgrp, $wrk1Bwplt, $wrk1Bwmdpt, $wrk1Bwwc, $wrk1Bwdate, $fromHmWk);
		} else {
			$scheduleRetInfo = ETRetInfo_Schedule($fromEmid, $wrk1Bwschd, $fromDept, $wrk1Bwgrp, $wrk1Bwplt, $wrk1Bwmdpt, $wrk1Bwwc, $wrk1Bwdate, $fromHmWk);
		}
	} elseif ($fromType == '2' || $fromType == '3') {
		if ($HDMERL > 0 ) {
			$scheduleRetInfo = ETRetInfo_Schedule($fromEmid, $wrk2Bwschd, $fromDept, $wrk2Bwgrp, $wrk2Bwplt, $wrk2Bwmdpt, $wrk2Bwwc, $wrk2Bwdate, $fromHmWk);
		} else {
			$scheduleRetInfo = ETRetInfo_Schedule($fromEmid, $wrk2Bwschd, $fromDept, $wrk2Bwgrp, $wrk2Bwplt, $wrk2Bwmdpt, $wrk2Bwwc, $wrk2Bwdate, $fromHmWk);
		}
	} elseif ($fromType == '4') {
		if ($HDMERL > 0 ) {
			$scheduleRetInfo = ETRetInfo_Schedule($fromEmid, $wrk4Bwschd, $fromDept, $wrk4Bwgrp, $wrk4Bwplt, $wrk4Bwmdpt, $wrk4Bwwc, $wrk4Bwdate, $fromHmWk);
		} else {
			$scheduleRetInfo = ETRetInfo_Schedule($fromEmid, $wrk4Bwschd, $fromDept, $wrk4Bwgrp, $wrk4Bwplt, $wrk4Bwmdpt, $wrk4Bwwc, $wrk4Bwdate, $fromHmWk);
		}
	}
	$sdcheduleWInfo    = $scheduleRetInfo['scheduleInfo'];
	if ($fromType == '1') {
		$schdDesc = RetValue("SMSCHD=$wrk1Bwschd ", "HDSCHM", "SMDESC");
		Format_Detail_Hover("Schedule", $schdDesc, $wrk1Bwschd,"scheduleWorkedSelection");
	} elseif ($fromType == '2' || $fromType == '3') {
		$schdDesc = RetValue("SMSCHD=$wrk2Bwschd ", "HDSCHM", "SMDESC");
		Format_Detail_Hover("Schedule", $schdDesc, $wrk2Bwschd,"scheduleWorkedSelection");
	} elseif ($fromType == '4') {
		$schdDesc = RetValue("SMSCHD=$wrk4Bwschd ", "HDSCHM", "SMDESC");
		Format_Detail_Hover("Schedule", $schdDesc, $wrk4Bwschd,"scheduleWorkedSelection");
	}
	print "\n <tr><td>";
	print "\n <div id=\"scheduleWorkedSelection\" class=\"moreInfo\">{$sdcheduleWInfo}</div>";
	print "\n </td></tr>";

	if ($HDPERL>0 || $HDPRRL>0) {
		if ($wrk0Bwcomp>0 || $wrk0Bwfacl>0) {$F_coFac=Format_HRCoFac($wrk0Bwcomp,$wrk0Bwfacl,'F');}
		$cofacDesc = RetValue("CFCOMP=$wrk0Bwcomp and CFFACL=$wrk0Bwfacl ", "HRCOFC", "CFNAME");
	} else {
		if ($wrk0Bwcomp>0 || $wrk0Bwfacl>0) {$F_coFac=Format_CoFac($wrk0Bwcomp,$wrk0Bwfacl,'F');}
		$cofacDesc = RetValue("CFCO#=$wrk0Bwcomp and CFFAC#=$wrk0Bwfacl", "HDCFAC", "CFCFNM");
	}
	Build_DspFld("Co/Fac:","$cofacDesc $F_coFac","","A");

	print "\n </table> ";

	// StartStop(hidden DIV)
	print "\n <a name=\"StartStop\"></a>";
	print "\n <div id=\"showStartStop\">";
	print "\n <fieldset class=\"legendBody\">";
	//require 'TopOfForm.php';
	print "\n <table $contentTable> ";

	print "\n <tr>";
	print "\n <td class=\"colhdr\" >&nbsp;</td>";
	print "\n <td class=\"colhdr\" >Start</td>";
	print "\n <td class=\"colhdr\" >Stop</td>";
	print "\n <td class=\"colhdr\" >Hours</td>";
	if ($fromType == '1') {
		print "\n <td class=\"colhdr\" >Variance</td>";
	} else {
		print "\n <td class=\"colhdr\" >&nbsp;</td>";
	}
	if ($fromType >= '2' || $wrk1Bwreec != "") {
		print "\n <td class=\"colhdr\" >Exception<br>Code</td>";
		print "\n <td class=\"colhdr\" >Description</td>";
		print "\n <td class=\"colhdr\" >Error<br>Level</td>";
		print "\n <td class=\"colhdr\" >Pay<br>Code</td>";
	}
	print "\n </tr>";

	print "\n <tr>";
	print "\n <td class=\"dsphdr\" >Schedule:</td>";
	$F_wrk0Bwstrt=EditHrsMin($wrk0Bwstrt);
	$F_wrk0Bwstop=EditHrsMin($wrk0Bwstop);
	if ($hfmt == 'D') {
		$F0_scheduledHours = Format_Nbr($wrk0Bwehrs, '2', $hrsEditCode, 'Y', $hrsBeforeChar, $hrsAfterChar);
		$F_scheduleVariance = Format_Nbr($wrk5Bwscvr, '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
	} else {
		$F0_scheduledHours = EditHrsMin(substr($wrk0Bwehrst,0,(strlen($wrk0Bwehrst)-2)));
		if ($TADSSF == "Y") {
			$F_scheduleVariance = EditHrsMinSec($wrk5Bwscvrt);
		} else {
			$F_scheduleVariance = EditHrsMin(substr($wrk5Bwscvrt,0,(strlen($wrk5Bwscvrt)-2)));
		}
	}
	print "\n <td class=\"colalph\" >$F_wrk0Bwstrt</td>";
	print "\n <td class=\"colalph\" >$F_wrk0Bwstop</td>";
	print "\n <td class=\"colalph\">$F0_scheduledHours</td>";
	if ($fromType == '1') {
		print "\n <td class=\"colalph\">$F_scheduleVariance</td>";
	} else {
		print "\n <td class=\"colalph\" >&nbsp;</td>";
	}
	if ($fromType == '4') {
		$descBwseec = RetValue("EVTYPE='X' and EVCODE='$wrk4Bwseec' ", "HDEVNT", "EVDESC");
		print "\n <td class=\"colicon\">$wrk4Bwseec</td>";
		print "\n <td class=\"colalph\">$descBwseec</td>";
		print "\n <td class=\"colnmbr\">$wrk4Bwselv</td>";
		print "\n <td class=\"colnmbr\">$wrk4Bwspyc</td>";
	} elseif ($fromType == '2' || $fromType == '3') {
		$descBwseec = RetValue("EVTYPE='X' and EVCODE='$wrk2Bwseec' ", "HDEVNT", "EVDESC");
		print "\n <td class=\"colicon\">$wrk2Bwseec</td>";
		print "\n <td class=\"colalph\">$descBwseec</td>";
		print "\n <td class=\"colnmbr\">$wrk2Bwselv</td>";
		print "\n <td class=\"colnmbr\">$wrk2Bwspyc</td>";
	} elseif ($fromType == '1') {
		$descBwseec = RetValue("EVTYPE='X' and EVCODE='$wrk1Bwseec' ", "HDEVNT", "EVDESC");
		print "\n <td class=\"colicon\">$wrk1Bwseec</td>";
		print "\n <td class=\"colalph\">$descBwseec</td>";
		print "\n <td class=\"colnmbr\">$wrk1Bwselv</td>";
		print "\n <td class=\"colnmbr\">$wrk1Bwspyc</td>";
	}
	print "\n </tr>";

	if ($fromType >= '1') {
		if ($TADSSF == "Y") {
			$F_BWSTRT = EditHrsMinSec($wrk1Bwstrt);
			$F_BWSTOP = EditHrsMinSec($wrk1Bwstop);
		} else {
			$F_BWSTRT = EditHrsMin(substr($wrk1Bwstrt,0,(strlen($wrk1Bwstrt)-2)));
			$F_BWSTOP = EditHrsMin(substr($wrk1Bwstop,0,(strlen($wrk1Bwstop)-2)));
		}
		if ($hfmt == 'D') {
			if ($pdwk == 'P') {
				$F_Hours = Format_Nbr($wrk1Bwphrs, '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			} else {
				$F_Hours = Format_Nbr($wrk1Bwwhrs, '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			}
		} else {
			if ($pdwk == 'P' && $TADSSF == "Y") {
				$F_Hours = EditHrsMinSec($wrk1Bwphrst);
			} elseif ($pdwk == 'P') {
				$F_Hours    = EditHrsMin(substr($wrk1Bwphrst,0,(strlen($wrk1Bwphrst)-2)));
			} elseif ($TADSSF == "Y") {
				$F_Hours = EditHrsMinSec($wrk1Bwwhrst);
			} else {
				$F_Hours = EditHrsMin(substr($wrk1Bwwhrst,0,(strlen($wrk1Bwwhrst)-2)));
			}
		}
		print "\n <tr>";
		print "\n <td class=\"dsphdr\" >Shift:</td>";
		print "\n <td class=\"colalph\" >$F_BWSTRT</td>";
		print "\n <td class=\"colalph\" >$F_BWSTOP</td>";
		print "\n <td class=\"colalph\">$F_Hours</td>";
	}
	print "\n <td class=\"colnmbr\" colspan = 1 >&nbsp;</td>";
	if ($fromType == '4') {
		$descBwpeec = RetValue("EVTYPE='X' and EVCODE='$wrk4Bwpeec' ", "HDEVNT", "EVDESC");
		print "\n <td class=\"colicon\">$wrk4Bwpeec</td>";
		print "\n <td class=\"colalph\">$descBwpeec</td>";
		print "\n <td class=\"colnmbr\">$wrk4Bwpelv</td>";
		print "\n <td class=\"colnmbr\">$wrk4Bwppyc</td>";
	} elseif ($fromType == '2' || $fromType == '3') {
		$descBwpeec = RetValue("EVTYPE='X' and EVCODE='$wrk2Bwpeec' ", "HDEVNT", "EVDESC");
		print "\n <td class=\"colicon\">$wrk2Bwpeec</td>";
		print "\n <td class=\"colalph\">$descBwpeec</td>";
		print "\n <td class=\"colnmbr\">$wrk2Bwpelv</td>";
		print "\n <td class=\"colnmbr\">$wrk2Bwppyc</td>";
	} elseif ($fromType == '1') {
		$descBwpeec = RetValue("EVTYPE='X' and EVCODE='$wrk1Bwpeec' ", "HDEVNT", "EVDESC");
		print "\n <td class=\"colicon\">$wrk1Bwpeec</td>";
		print "\n <td class=\"colalph\">$descBwpeec</td>";
		print "\n <td class=\"colnmbr\">$wrk1Bwpelv</td>";
		print "\n <td class=\"colnmbr\">$wrk1Bwppyc</td>";
	}
	print "\n </tr>";

	if ($fromType == '1') {
		print "\n <tr>";
		print "\n <td class=\"colnmbr\" colspan = 5>&nbsp;</td>";
		$descBwreec = RetValue("EVTYPE='X' and EVCODE='$wrk1Bwreec' ", "HDEVNT", "EVDESC");
		print "\n <td class=\"colicon\">$wrk1Bwreec</td>";
		print "\n <td class=\"colalph\">$descBwreec</td>";
		print "\n <td class=\"colnmbr\">$wrk1Bwrelv</td>";
		print "\n <td class=\"colnmbr\">$wrk1Bwrpyc</td>";
		print "\n </tr>";
	}

	if ($fromType == '2' || $fromType == '3') {
		if ($TADSSF == "Y") {
			$F_BWSTRT = EditHrsMinSec($wrk2Bwstrt);
			$F_BWSTOP = EditHrsMinSec($wrk2Bwstop);
		} else {
			$F_BWSTRT = EditHrsMin(substr($wrk2Bwstrt,0,(strlen($wrk2Bwstrt)-2)));
			$F_BWSTOP = EditHrsMin(substr($wrk2Bwstop,0,(strlen($wrk2Bwstop)-2)));
		}
		if ($hfmt == 'D') {
			if ($pdwk == 'P') {
				$F_Hours = Format_Nbr($wrk2Bwphrs, '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			} else {
				$F_Hours = Format_Nbr($wrk2Bwwhrs, '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
			}
		} else {
			if ($pdwk == 'P' && $TADSSF == "Y") {
				$F_Hours = EditHrsMinSec($wrk2Bwphrst);
			} elseif ($pdwk == 'P') {
				$F_Hours    = EditHrsMin(substr($wrk2Bwphrst,0,(strlen($wrk2Bwphrst)-2)));
			} elseif ($TADSSF == "Y") {
				$F_Hours = EditHrsMinSec($wrk2Bwwhrst);
			} else {
				$F_Hours = EditHrsMin(substr($wrk2Bwwhrst,0,(strlen($wrk2Bwwhrst)-2)));
			}
		}
		print "\n <tr>";
		print "\n <td class=\"dsphdr\" >Employee:</td>";
		print "\n <td class=\"colalph\" >$F_BWSTRT</td>";
		print "\n <td class=\"colalph\" >$F_BWSTOP</td>";
		print "\n <td class=\"colalph\">$F_Hours</td>";
		print "\n <td class=\"colnmbr\" colspan = 1 >&nbsp;</td>";
		$descBwreec = RetValue("EVTYPE='X' and EVCODE='$wrk2Bwreec' ", "HDEVNT", "EVDESC");
		print "\n <td class=\"colicon\">$wrk2Bwreec</td>";
		print "\n <td class=\"colalph\">$descBwreec</td>";
		print "\n <td class=\"colnmbr\">$wrk2Bwrelv</td>";
		print "\n <td class=\"colnmbr\">$wrk2Bwrpyc</td>";
		print "\n </tr>";
	}

	if ($fromType == '4') {
		if ($TADSSF == "Y") {
			$F_BWSTRT = EditHrsMinSec($wrk4Bwstrt);
			$F_BWSTOP = EditHrsMinSec($wrk4Bwstop);
		} else {
			$F_BWSTRT = EditHrsMin(substr($wrk4Bwstrt,0,(strlen($wrk4Bwstrt)-2)));
			$F_BWSTOP = EditHrsMin(substr($wrk4Bwstop,0,(strlen($wrk4Bwstop)-2)));
		}
		if ($hfmt == 'D') {
			$F_Hours = Format_Nbr($wrk4Bwehrs, '6', $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar);
		} else {
			if ($TADSSF == "Y") {
				$F_Hours = EditHrsMinSec($wrk4Bwehrst);
			} else {
				$F_Hours = EditHrsMin(substr($wrk4Bwehrst,0,(strlen($wrk4Bwehrst)-2)));
			}
		}
		print "\n <td class=\"dsphdr\" >Employee:</td>";
		print "\n <td class=\"colalph\" >$F_BWSTRT</td>";
		print "\n <td class=\"colalph\" >$F_BWSTOP</td>";
		print "\n <td class=\"colalph\">$F_Hours</td>";
		print "\n <td class=\"colnmbr\" colspan = 1 >&nbsp;</td>";
		$descBwreec = RetValue("EVTYPE='X' and EVCODE='$wrk4Bwreec' ", "HDEVNT", "EVDESC");
		print "\n <td class=\"colicon\">$wrk4Bwreec</td>";
		print "\n <td class=\"colalph\">$descBwreec</td>";
		print "\n <td class=\"colnmbr\">$wrk4Bwrelv</td>";
		print "\n <td class=\"colnmbr\">$wrk4Bwrpyc</td>";
		print "\n </tr>";
	}

	print "\n </table> ";
	print "\n </fieldset> ";
	print "\n </div>";



	if ($fromType != '4') {
		// Guts(hidden DIV)
		print "\n <a name=\"Guts\"></a> ";
		print "\n <div id=\"showGuts\">";
		print "\n <fieldset class=\"legendBody\"> ";
		//require 'TopOfForm.php';
		print "\n <table $contentTable> ";

		if ($fromType == '2' || $fromType == '3') {
			print "\n <tr>";
			print "\n <td class=\"dsphdr\" >Order Number:</td>";
			print "\n <td class=\"colalph\" >$wrk2Bword</td>";
			print "\n </tr>";
			print "\n <tr>";
			print "\n <td class=\"dsphdr\" >Routing Sequence:</td>";
			print "\n <td class=\"colalph\" >$wrk2Bwseqn</td>";
			print "\n </tr>";
			print "\n <tr>";
			if ($HDMERL > 0 ) {
				$descBwmdptwc = RetValue("WCPLT='$wrk2Bwplt' and WCDEPT='$wrk2Bwmdpt' and WCWC='$wrk2Bwwc'", "HDMWCM", "WCDESC");
				print "\n <td class=\"dsphdr\" >Department/Work Center:</td>";
				print "\n <td class=\"colalph\" >$wrk2Bwmdpt / $wrk2Bwwc</td>";
			} elseif ($HDPRRL > 0 || $HDPERL > 0) {
				$descBwmdptwc = RetValue("EADEPT='$wrk2Bwmdpt'", "PREXAC", "EANAME");
				print "\n <td class=\"dsphdr\" >Department:</td>";
				print "\n <td class=\"colalph\" >$wrk2Bwmdpt</td>";
			}
			print "\n <td class=\"colalph\" >$descBwmdptwc</td>";
			print "\n </tr>";
			print "\n <tr>";
			if ($fromType == '1' ) {
				$descBwdtcl = RetValue("DFCODE='$wrk1Bwdtcl' ", "ETDCDF", "DFDESC");
				print "\n <td class=\"dsphdr\" >Data Collection Code:</td>";
				print "\n <td class=\"colalph\" >$wrk1Bwdtcl</td>";
				print "\n <td class=\"colalph\" >$descBwdtcl</td>";
			} elseif ($fromType == '2' || $fromType == '3') {
				$descBwdtcl = RetValue("DFCODE='$wrk2Bwdtcl' ", "ETDCDF", "DFDESC");
				print "\n <td class=\"dsphdr\" >Data Collection Code:</td>";
				print "\n <td class=\"colalph\" >$wrk2Bwdtcl</td>";
				print "\n <td class=\"colalph\" >$descBwdtcl</td>";
			} elseif ($fromType == '4') {
				$descBwdtcl = RetValue("DFCODE='$wrk4Bwdtcl' ", "ETDCDF", "DFDESC");
				print "\n <td class=\"dsphdr\" >Data Collection Code:</td>";
				print "\n <td class=\"colalph\" >$wrk4Bwdtcl</td>";
				print "\n <td class=\"colalph\" >$descBwdtcl</td>";
			}
			print "\n </tr>";

			if ($wrk2Bwdtcl == '55' ||$wrk2Bwdtcl =='50') {
				if ($wrk2Bwdtrc != ""){
					print "\n <tr>";
					$descBwdtrc = RetValue("EVTYPE='I' and EVCODE='$wrk2Bwdtrc' ", "HDEVNT", "EVDESC");
					print "\n <td class=\"dsphdr\" >     Indirect/Downtime Code:</td>";
					print "\n <td class=\"colalph\" >$wrk2Bwdtrc</td>";
					print "\n <td class=\"colalph\" >$descBwdtrc</td>";
					print "\n </tr>";
				}
			}
			if ($HDMERL > 0 ) {
				print "\n <tr>";
				$descBwlcod = RetValue("LRLCOD='$wrk2Bwlcod' ", "SIMLRC", "LRADES");
				print "\n <td class=\"dsphdr\" >Labor Code:</td>";
				print "\n <td class=\"colalph\" >$wrk2Bwlcod</td>";
				print "\n <td class=\"colalph\" >$descBwlcod</td>";
				print "\n </tr>";
			}
			print "\n <tr>";
			print "\n <td class=\"dsphdr\" >Completed Pieces:</td>";
			print "\n <td class=\"colalph\" >$wrk2Bwqtyc</td>";
			print "\n </tr>";
			if ($HDMERL > 0 ) {
				print "\n <tr>";
				$descBwscrc = RetValue("TTTYPE='$wrk2Bwscrc' ", "HDTTYP", "TTDESC");
				print "\n <td class=\"dsphdr\" >Scrap Pieces/Code:</td>";
				print "\n <td class=\"colalph\" >$wrk2Bwqtys</td>";
				print "\n <td class=\"colalph\" >$wrk2Bwscrc</td>";
				print "\n <td class=\"colalph\" >$descBwscrc</td>";
				print "\n </tr>";
			}
			if ($wrk2Bwrwrc != "" ) {
				print "\n <tr>";
				$descBwrwrc = RetValue("TTTYPE='$wrk2Bwrwrc' ", "HDTTYP", "TTDESC");
				print "\n <td class=\"dsphdr\" >Scrap Pieces/Code:</td>";
				print "\n <td class=\"colalph\" >$wrk2Bwrewk</td>";
				print "\n <td class=\"colalph\" >$wrk2Bwrwrc</td>";
				print "\n <td class=\"colalph\" >$descBwrwrc</td>";
				print "\n </tr>";
			}
		} else {
			print "\n <tr>";
			if ($fromType == '1') {
				$descBwdtcl = RetValue("DFCODE='$wrk1Bwdtcl' ", "ETDCDF", "DFDESC");
				print "\n <td class=\"dsphdr\" >Data Collection Code:</td>";
				print "\n <td class=\"colalph\" >$wrk1Bwdtcl</td>";
				print "\n <td class=\"colalph\" >$descBwdtcl</td>";
			} elseif ($fromType == '2' || $fromType == '3') {
				$descBwdtcl = RetValue("DFCODE='$wrk2Bwdtcl' ", "ETDCDF", "DFDESC");
				print "\n <td class=\"dsphdr\" >Data Collection Code:</td>";
				print "\n <td class=\"colalph\" >$wrk2Bwdtcl</td>";
				print "\n <td class=\"colalph\" >$descBwdtcl</td>";
			} elseif ($fromType == '4') {
				$descBwdtcl = RetValue("DFCODE='$wrk4Bwdtcl' ", "ETDCDF", "DFDESC");
				print "\n <td class=\"dsphdr\" >Data Collection Code:</td>";
				print "\n <td class=\"colalph\" >$wrk4Bwdtcl</td>";
				print "\n <td class=\"colalph\" >$descBwdtcl</td>";
			}
			print "\n </tr>";
		}

		print "\n </table> ";
		print "\n </fieldset> ";
		print "\n </div>";
	}



	// Comment(hidden DIV)
	print "\n <a name=\"Comment\"></a> ";
	print "\n <div id=\"showComment\">";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Comment ";
	Print "\n </legend> ";
	//require 'TopOfForm.php';
	print "\n <table $contentTable> ";

	print "\n <tr>";
	//print "\n <td class=\"dsphdr\" >Comment:</td>";
	if ($fromType == "1"){
		print "\n <td class=\"colalph\" >$wrk1Bwcmnt</td>";
	} elseif ($fromType == '2' || $fromType == '3'){
		print "\n <td class=\"colalph\" >$wrk2Bwcmnt</td>";
	} elseif ($fromType == '4') {
		print "\n <td class=\"colalph\" >$wrk4Bwcmnt</td>";
	}
	print "\n </tr>";
	print "\n </table> ";
	print "\n </fieldset> ";
	print "\n </div>";

	print "\n </fieldset> ";
	print "\n </div>";
	print "\n </td>";
	print "\n </tr>";
	print "\n </table>";
	print "\n </body>";
	print "\n </html>";
}
?>	
