<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = (isset($_GET['maintenanceCode']))  ? $_GET['maintenanceCode']  : "";
$errFound        = (isset($_GET['errFound']))         ? $_GET['errFound']         : "";
$fromDate 	     = (isset($_GET['fromDate']))         ? $_GET['fromDate']         : "";
$fromEmid 	     = (isset($_GET['fromEmid']))         ? $_GET['fromEmid']         : "0";
$fromSstr 	     = (isset($_GET['fromSstr']))         ? $_GET['fromSstr']         : "0";
$fromCo 	     = (isset($_GET['fromCo']))           ? $_GET['fromCo']           : "0";
$fromFac 	     = (isset($_GET['fromFac']))          ? $_GET['fromFac']          : "0";
$fromEmp 	     = (isset($_GET['fromEmp']))          ? $_GET['fromEmp']          : "0";

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once "ETControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Employee Lunch Maintenance";
$scriptName     = "EmployeeLunchMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;fromDate=" . urlencode(trim($fromDate)) . "&amp;fromEmid=" . urlencode(trim($fromEmid)) . "&amp;fromSstr=" . urlencode(trim($fromSstr)) . "&amp;fromCo=" . urlencode(trim($fromCo)) . "&amp;fromFac=" . urlencode(trim($fromFac)) . "&amp;fromEmp=" . urlencode(trim($fromEmp));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HETLNM_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=175";}

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
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'DateEdit.php';
	require_once 'UpperCase.php';
	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.emplCompany.value ==\"\" ";
	print "\n     || document.Chg.emplFacility.value ==\"\" ";
	print "\n     || document.Chg.emplNumber.value ==\"\" ";
	print "\n     || document.Chg.transDate.value ==\"\" ";
	print "\n     || document.Chg.schedStart.value ==\"\" ";
	print "\n     || document.Chg.schedStop.value ==\"\" ";
	if ($HDMERL>0) {
		print "\n     || document.Chg.plantNumber.value ==\"\" ";
	}
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.emplCompany, 2, 0) && ";
	print "\n     editNum(document.Chg.emplFacility, 4, 0) && ";
	print "\n     editNum(document.Chg.emplNumber, 5, 0) && ";
	print "\n     editdate(document.Chg.transDate) && ";
	print "\n     editNum(document.Chg.schedStart, 4, 0) && ";
	print "\n     editNum(document.Chg.schedStop, 4, 0) && ";
	if ($HDMERL>0) {
		print "\n     editNum(document.Chg.plantNumber, 3, 0) && ";
	}
	print "\n     editNum(document.Chg.groupNumber, 5, 0) && ";
	if ($TADSSF == 'Y') {
		print "\n     editNum(document.Chg.startTime, 6, 0) && ";
	} else{
		print "\n     editNum(document.Chg.startTime, 4, 0) && ";
	}
	print "\n     editdate(document.Chg.startDate) && ";
	if ($TADSSF == 'Y') {
		print "\n     editNum(document.Chg.stopTime, 6, 0) && ";
	} else{
		print "\n     editNum(document.Chg.stopTime, 4, 0) && ";
	}
	print "\n     editdate(document.Chg.stopDate))";
	print "\n return true;";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "EMPLOYEELUNCHMAINT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select a.*, b.EMLNAM, b.EMFNAM, b.EMMIDI, b.EMRNAM, b.EMTRCD ";
		$stmtSQL .= " From ETEMLN a Inner Join HREMPL b ";
		$stmtSQL .= " On b.EMCOMP=a.LNCO and b.EMFACL=a.LNFAC and b.EMEMPL=a.LNEMP ";
		$stmtSQL .= " Where a.LNDATE='$fromDate' ";
		$stmtSQL .= " and a.LNEMID=$fromEmid ";
		$stmtSQL .= " and a.LNSSTR=$fromSstr ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hettom_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hettom_OPT['sec_01'];
	$sec_02=$hettom_OPT['sec_02'];
	$sec_03=$hettom_OPT['sec_03'];
	$sec_04=$hettom_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "emplCompany";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_LNCO  =DecatErr_Field("@@co@@", "emplCompany");
			$Err_LNFAC =DecatErr_Field("@@fac@", "emplFacility");
			$Err_LNEMP =DecatErr_Field("@@emp@", "emplNumber");
			$Err_LNEMID=DecatErr_Field("@@emid", "employeeID");
			$Err_LNDATE=DecatErr_Field("@@date", "transDate");
			$Err_LNSSTR=DecatErr_Field("@@sstr", "schedStart");
			$Err_LNSSTP=DecatErr_Field("@@sstp", "schedStop");
			$Err_LNPLT =DecatErr_Field("@@plt@", "plantNumber");
			$Err_LNGRP =DecatErr_Field("@@grp@", "groupNumber");
			$Err_LNCMNT=DecatErr_Field("@@cmnt", "commentText");
			$Err_LNSTRT=DecatErr_Field("@@strt", "startTime");
			$Err_LNCALD=DecatErr_Field("@@cald", "startDate");
			$Err_LNSTOP=DecatErr_Field("@@stop", "stopTime");
			$Err_LNECAL=DecatErr_Field("@@ecal", "stopDate");
			$Err_LNSEEC=DecatErr_Field("@@seec", "startExcept");
			$Err_LNPEEC=DecatErr_Field("@@peec", "stopExcept");
			$Err_LNPAID=DecatErr_Field("@@paid", "paidTime");
			$Err_LNADJE=DecatErr_Field("@@adje", "adjustWork");
			$Err_LNLUBF=DecatErr_Field("@@lubf", "lunchFactor");
		}

		$row['LNCO']=Decat_Field("@@co@@", $edtVar);
		$row['LNFAC']=Decat_Field("@@fac@", $edtVar);
		$row['LNEMP']=Decat_Field("@@emp@", $edtVar);
		$row['LNEMID']=Decat_Field("@@emid", $edtVar);
		$row['LNDATE']=Decat_Field("@@date", $edtVar);
		$row['LNSSTR']=Decat_Field("@@sstr", $edtVar);
		$row['LNSSTP']=Decat_Field("@@sstp", $edtVar);
		$row['LNPLT']=Decat_Field("@@plt@", $edtVar);
		$row['LNGRP']=Decat_Field("@@grp@", $edtVar);
		$row['LNCMNT']=Decat_Field("@@cmnt", $edtVar);
		$row['LNSTRT']=Decat_Field("@@strt", $edtVar);
		$row['LNCALD']=Decat_Field("@@cald", $edtVar);
		$row['LNSTOP']=Decat_Field("@@stop", $edtVar);
		$row['LNECAL']=Decat_Field("@@ecal", $edtVar);
		$row['LNSEEC']=Decat_Field("@@seec", $edtVar);
		$row['LNPEEC']=Decat_Field("@@peec", $edtVar);
		$row['LNPAID']=Decat_Field("@@paid", $edtVar);
		$row['LNADJE']=Decat_Field("@@paid", $edtVar);
		$row['LNLUBF']=Decat_Field("@@lubf", $edtVar);
		$row['LNTSTP']=Decat_Field("@@tstp", $edtVar);

		if ($errFound == "" && $maintenanceCode == "A") {
			$row['LNPAID']="N";
			$row['LNADJE']="N";
		}

		$errFound = "";

	} else {
		$row['LNDATE']=DateInputFromISO($row['LNDATE']);
		$row['LNCALD']=DateInputFromISO($row['LNCALD']);
		$row['LNECAL']=DateInputFromISO($row['LNECAL']);
		$row['LNSTRT']=TimeInputFromHMS($row['LNSTRT'],$TADSSF);
		$row['LNSTOP']=TimeInputFromHMS($row['LNSTOP'],$TADSSF);
		$focusField = ($maintenanceCode=="Z") ? "emplCompany" : "schedStop";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['LNTSTP']) . "\"></td></tr>";
	print "\n     <tr><td><input type=\"hidden\" name=\"employeeID\" value=\"" . rtrim($row['LNEMID']) . "\"></td></tr>";

	if ($HDPERL>0 || $HDPRRL>0) {
		$coFacDesc=RetValue("CFCOMP={$row['LNCO']} and CFFACL={$row['LNFAC']}", "HRCOFC", "CFNAME");
	} else {
		$coFacDesc=RetValue("CFCO#={$row['LNCO']} and CFFAC#={$row['LNFAC']}", "HDCFAC", "CFCFNM");
	}
	$textOvr=SetTextOvr($Err_LNCO);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company/Facility</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"emplCompany\" value=\"{$row['LNCO']}\" size=\"2\" maxlength=\"2\"> / <input type=\"text\" name=\"emplFacility\" value=\"{$row['LNFAC']}\" size=\"4\" maxlength=\"4\">";
		if ($HDPERL>0 || $HDPRRL>0) {
			print "\n <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=emplCompany&amp;fldFac=emplFacility&amp;fldDesc=coFacDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
		} else {
			print "\n <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=emplCompany&amp;fldFac=emplFacility&amp;fldDesc=coFacDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
		}
		print "\n     <span class=\"dspdesc\" id=\"coFacDesc\">$coFacDesc</span>";
		print "\n </td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"emplCompany\" value=\"{$row['LNCO']}\">{$row['LNCO']} / <input type=\"hidden\" name=\"emplFacility\" value=\"{$row['LNFAC']}\">{$row['LNFAC']} $coFacDesc</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_LNCO);

	$name=Format_EmplName(trim($row['EMFNAM']),trim($row['EMLNAM']),trim($row['EMMIDI']),trim($row['EMRNAM']),trim($row['EMTRCD']),"H");
	$F_Name=Format_Quote($name);
	$textOvr=SetTextOvr($Err_LNEMP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Employee Number</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"emplNumber\" value=\"{$row['LNEMP']}\" size=\"5\" maxlength=\"5\">";
		print "\n     <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldEmpl=emplNumber&amp;fldEmplName=emplName&amp;fldCo=emplCompany&amp;fldFacl=emplFacility&amp;fldCoName=coFacDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"emplName\">$F_Name</span>";
		print "\n </td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"emplNumber\" value=\"{$row['LNEMP']}\">{$row['LNEMP']} $F_Name</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_LNEMP);

	$textOvr=SetTextOvr($Err_LNDATE);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Date</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputdate\"><input type=\"text\"   name=\"transDate\" value=\"{$row['LNDATE']}\" size=\"6\" maxlength=\"6\">";
		print "\n     <a href=\"javascript:calWindow('transDate');\"> $reqFieldChar $calendarImage</a> ";
		print "\n </td>";
	} else {
		$F_DATE=Format_Date(DateToCYMD($row['LNDATE']), "D") ;
		print "\n <td class=\"inputdate\"><input type=\"hidden\" name=\"transDate\" value=\"{$row['LNDATE']}\">$F_DATE</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_LNDATE);

	$textOvr=SetTextOvr($Err_LNSSTR);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Scheduled Start Time</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"schedStart\" value=\"{$row['LNSSTR']}\" size=\"5\" maxlength=\"4\"> $reqFieldChar </td>";
	} else {
		$F_SSTR=EditHrsMin($row['LNSSTR']);
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"schedStart\" value=\"{$row['LNSSTR']}\">$F_SSTR</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_LNSSTR);

	Build_Fld_Entry("Scheduled Stop Time","schedStop","inputnmbr","","LNSSTP",$row['LNSSTP'],$Err_LNSSTP,"5","4","Y","","");

	if ($HDMERL>0) {
		$plantDesc=RetValue("PLPLNT={$row['LNPLT']}", "HDPLNT", "PLNAME");
		$textOvr=SetTextOvr($Err_LNPLT);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Plant</span></td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"plantNumber\" value=\"{$row['LNPLT']}\" size=\"5\" maxlength=\"3\">";
		print "\n     <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=plantNumber&amp;fldDesc=plantDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"plantDesc\">$plantDesc</span>";
		print "\n </td>";
		DspErrMsg($Err_LNPLT);
	} else {
		print "\n <tr><td><input type=\"hidden\" name=\"plantNumber\" value=\"" . rtrim($row['LNPLT']) . "\"></td></tr>";
	}

	$groupDesc=RetValue("BBGRP#={$row['LNGRP']}", "HDGRPM", "BBDESC");
	$textOvr=SetTextOvr($Err_LNGRP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Group Number</span></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"groupNumber\" value=\"{$row['LNGRP']}\" size=\"5\" maxlength=\"5\">";
	print "\n     <a href=\"{$homeURL}{$phpPath}GroupNumberSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=groupNumber&amp;fldDesc=groupDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"groupDesc\">$groupDesc</span>";
	print "\n </td>";
	DspErrMsg($Err_LNGRP);

	Build_Fld_Entry("Comment","commentText","inputalph","","LNCMNT",$row['LNCMNT'],$Err_LNCMNT,"30","40","","","");
	$timeMax = ($TADSSF == 'Y') ? '6' : '4';
	Build_Fld_Entry("Start Time","startTime","inputnmbr","","LNSTRT",$row['LNSTRT'],$Err_LNSTRT,"6",$timeMax,"","","");
	Build_Fld_Entry("Start Date","startDate","inputdate","Date","LNCALD",$row['LNCALD'],$Err_LNCALD,"6","6","","","");
	Build_Fld_Entry("Stop Time","stopTime","inputnmbr","","LNSTOP",$row['LNSTOP'],$Err_LNSTOP,"6",$timeMax,"","","");
	Build_Fld_Entry("End Date","stopDate","inputdate","Date","LNECAL",$row['LNECAL'],$Err_LNECAL,"6","6","","","");

	$seecDesc=RetValue("EVTYPE='X' and EVCODE='{$row['LNSEEC']}'", "HDEVNT", "EVDESC");
	$textOvr=SetTextOvr($Err_LNSEEC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Start Time Exception Code</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"startExcept\" value=\"{$row['LNSEEC']}\" size=\"2\" maxlength=\"2\">";
	print "\n     <a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=startExcept&amp;fldDesc=seecDesc&amp;fldType=X\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"seecDesc\">$seecDesc</span>";
	print "\n </td>";
	DspErrMsg($Err_LNSEEC);

	$peecDesc=RetValue("EVTYPE='X' and EVCODE='{$row['LNPEEC']}'", "HDEVNT", "EVDESC");
	$textOvr=SetTextOvr($Err_LNPEEC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Stop Time Exception Code</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"stopExcept\" value=\"{$row['LNPEEC']}\" size=\"2\" maxlength=\"2\">";
	print "\n     <a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=stopExcept&amp;fldDesc=peecDesc&amp;fldType=X\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"peecDesc\">$peecDesc</span>";
	print "\n </td>";
	DspErrMsg($Err_LNPEEC);

	Build_Flag_Entry("Paid Time","paidTime","YORN","LNPAID",$row['LNPAID'],$Err_LNPAID,"1","1","","","");
	Build_Flag_Entry("Adjust Worked Hours","adjustWork","YORN","LNADJE",$row['LNADJE'],$Err_LNADJE,"1","1","","","");
	Build_Flag_Entry("Lunch Break Factoring","lunchFactor","LUNCHFACTR","LNLUBF",$row['LNLUBF'],$Err_LNLUBF,"1","1","","","");

	print "\n </table> ";

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
		$_POST['transDate']    =DateInputFromISO($fromDate);
		$_POST['employeeID']   =$fromEmid;
		$_POST['schedStart']   =$fromSstr;
		$_POST['emplCompany']  =$fromCo;
		$_POST['emplFacility'] =$fromFac;
		$_POST['emplNumber']   =$fromEmp;
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	Concat_Field("@@emid", $_POST['employeeID']);
	Concat_Field("@@co@@", $_POST['emplCompany']);
	Concat_Field("@@fac@", $_POST['emplFacility']);
	Concat_Field("@@emp@", $_POST['emplNumber']);
	Concat_Field("@@date", $_POST['transDate']);
	Concat_Field("@@sstr", $_POST['schedStart']);
	Concat_Field("@@sstp", $_POST['schedStop']);
	Concat_Field("@@plt@", $_POST['plantNumber']);
	Concat_Field("@@grp@", $_POST['groupNumber']);
	Concat_Field("@@cmnt", $_POST['commentText']);
	Concat_Field("@@strt", $_POST['startTime']);
	Concat_Field("@@cald", $_POST['startDate']);
	Concat_Field("@@stop", $_POST['stopTime']);
	Concat_Field("@@ecal", $_POST['stopDate']);
	$_POST['startExcept']=strtoupper($_POST['startExcept']);
	Concat_Field("@@seec", $_POST['startExcept']);
	$_POST['stopExcept']=strtoupper($_POST['stopExcept']);
	Concat_Field("@@peec", $_POST['stopExcept']);
	if (!isset($_POST['paidTime'])) {$_POST['paidTime']="N";}
	Concat_Field("@@paid", $_POST['paidTime']);
	if (!isset($_POST['adjustWork'])) {$_POST['adjustWork']="N";}
	Concat_Field("@@adje", $_POST['adjustWork']);
	$_POST['lunchFactor']=strtoupper($_POST['lunchFactor']);
	Concat_Field("@@lubf", $_POST['lunchFactor']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit_Handle("HETLNM_W", $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		$F_Name=Format_Quote(Ret_Format_EmplName($_POST['emplCompany'],$_POST['emplFacility'],$_POST['emplNumber'],"0","0","H"));
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, "Lunch for " . $F_Name, $_POST['emplNumber'], "On Date", $_POST['transDate'], "Scheduled To Start", $_POST['schedStart']);
		} else {
			$Err_LNCO=DecatErr_Field("@@co@@", "emplCompany");
			$confMessage=Format_ConfMsg_Desc("", "Lunch for " . $F_Name, $_POST['emplNumber'], "<br>$Err_LNCO", "", "", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>