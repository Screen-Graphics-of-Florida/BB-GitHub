<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = (isset($_GET['maintenanceCode']))  ? $_GET['maintenanceCode']  : "";
$errFound        = (isset($_GET['errFound']))         ? $_GET['errFound']         : "";
$fromSchd 	     = (isset($_GET['fromSchd']))         ? $_GET['fromSchd']         : 0;
$fromDycd 	     = (isset($_GET['fromDycd']))         ? $_GET['fromDycd']         : "";
$fromStrt 	     = (isset($_GET['fromStrt']))         ? $_GET['fromStrt']         : 0;
$fromEffs        = (isset($_GET['fromEffs']))         ? $_GET['fromEffs']         : null;

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Time Off Maintenance";
$scriptName     = "TimeOffMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;fromSchd=" . urlencode(trim($fromSchd)) . "&amp;fromDycd=" . urlencode(trim($fromDycd)) . "&amp;fromStrt=" . urlencode(trim($fromStrt)) . "&amp;fromEffs=" . urlencode(trim($fromEffs));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HETTOM";
$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=167";}

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
	print "\n if (document.Chg.timeOffDesc.value ==\"\" )";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.stopTime, 4, 0) && ";
	print "\n     editdate(document.Chg.effEnd))";	
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
	$pageID = "TIMEOFFMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From HDTMOF ";
		$stmtSQL .= " Where TOSCHD=$fromSchd ";
		$stmtSQL .= " and TODYCD='$fromDycd' ";
		$stmtSQL .= " and TOSTRT=$fromStrt ";
		if  (!$fromEffs) {
			$stmtSQL .= " and TOEFFS is null ";
		}  else  {
			$stmtSQL .= " and TOEFFS='$fromEffs' ";
		}
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
			$focusField= "schedule";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_TOSCHD=DecatErr_Field("@@schd", "schedule");
			$Err_TODYCD=DecatErr_Field("@@dycd", "dayCode");
			$Err_TOSTRT=DecatErr_Field("@@strt", "startTime");
			$Err_TOEFFS=DecatErr_Field("@@effs", "effStart");
			$Err_TODESC=DecatErr_Field("@@desc", "timeOffDesc");
			$Err_TOSTOP=DecatErr_Field("@@stop", "stopTime");
			$Err_TOEFFE=DecatErr_Field("@@effe", "effEnd");
			$Err_TOPAID=DecatErr_Field("@@paid", "paidTime");
			$Err_TOADJE=DecatErr_Field("@@adje", "adjustWork");
			$Err_TOLUBK=DecatErr_Field("@@lubk", "lunchBreak");
			$Err_TOLUBF=DecatErr_Field("@@lubf", "lunchFactor");
		}

		$row['TOSCHD']=Decat_Field("@@schd", $edtVar);
		$row['TODYCD']=Decat_Field("@@dycd", $edtVar);
		$row['TOSTRT']=Decat_Field("@@strt", $edtVar);
		$row['TOEFFS']=Decat_Field("@@effs", $edtVar);
		$row['TODESC']=Decat_Field("@@desc", $edtVar);
		$row['TOSTOP']=Decat_Field("@@stop", $edtVar);
		$row['TOEFFE']=Decat_Field("@@effe", $edtVar);
		$row['TOPAID']=Decat_Field("@@paid", $edtVar);
		$row['TOADJE']=Decat_Field("@@adje", $edtVar);
		$row['TOLUBK']=Decat_Field("@@lubk", $edtVar);
		$row['TOLUBF']=Decat_Field("@@lubf", $edtVar);
		$row['TOELAP']=Decat_Field("@@elap", $edtVar);
		
		if ($errFound == "" && $maintenanceCode == "A") {
			$row['TOPAID']="N";
			$row['TOADJE']="N";
		}

		$errFound = "";		

	} else {
		$row['TOSTRT']=TimeInputFromDec($row['TOSTRT']);
		$row['TOEFFS']=DateInputFromISO($row['TOEFFS']);
		$row['TOSTOP']=TimeInputFromDec($row['TOSTOP']);
		$row['TOEFFE']=DateInputFromISO($row['TOEFFE']);		
		$focusField = ($maintenanceCode=="Z") ? "schedule" : "timeOffDesc";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	
    $fieldDesc=RetValue("SMSCHD={$row['TOSCHD']} and SMEFFS is NULL", "HDSCHM ", "SMDESC");
	$textOvr=SetTextOvr($Err_TOSCHD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Schedule</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"schedule\" value=\"{$row['TOSCHD']}\" size=\"5\" maxlength=\"3\">";
		print "\n     <a href=\"{$homeURL}{$phpPath}ScheduleSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=schedule&amp;fldDesc=scheduleDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"scheduleDesc\">$fieldDesc</span>";		
		print "\n </td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"schedule\" value=\"{$row['TOSCHD']}\">{$row['TOSCHD']} $fieldDesc</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_TOSCHD);

	$fieldDesc=RetValue("FLTYPE='DAYOFWEEK' and FLVALU='{$row['TODYCD']}'", "SYFLAG", "FLDESC");
	$textOvr=SetTextOvr($Err_TODYCD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Day Of Week</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"dayCode\" value=\"{$row['TODYCD']}\" size=\"5\" maxlength=\"1\">";
		print "\n     <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=DAYOFWEEK&amp;flagSrchHdr=". urlencode('Day Of Week') . "&amp;fldName=dayCode&amp;fldDesc=dayCodeDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"dayCodeDesc\">$fieldDesc</span>";		
		print "\n </td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"dayCode\" value=\"{$row['TODYCD']}\">{$row['TODYCD']} $fieldDesc</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_TODYCD);
	
	
	$textOvr=SetTextOvr($Err_TOSTRT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Start Time</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"startTime\" value=\"{$row['TOSTRT']}\" size=\"5\" maxlength=\"4\"> $reqFieldChar </td>";
	} else {
		$F_STRT=EditHrsMin($row['TOSTRT']);
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"startTime\" value=\"{$row['TOSTRT']}\">$F_STRT</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_TOSTRT);	
	

	$textOvr=SetTextOvr($Err_TOEFFS);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Effectivity Start Date</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputdate\"><input type=\"text\"   name=\"effStart\" value=\"{$row['TOEFFS']}\" size=\"6\" maxlength=\"6\">";
		print "\n     <a href=\"javascript:calWindow('effStart');\">$calendarImage</a> ";
		print "\n </td>";
	} else {
		$F_EFFS=Format_Date(DateToCYMD($row['TOEFFS']), "D") ;
		print "\n <td class=\"inputdate\"><input type=\"hidden\" name=\"effStart\" value=\"{$row['TOEFFS']}\">$F_EFFS</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_TOEFFS);
	
	Build_Fld_Entry("Description","timeOffDesc","inputalph","","TODESC",$row['TODESC'],$Err_TODESC,"10","10","Y","","");
	Build_Fld_Entry("Stop Time","stopTime","inputnmbr","","TOSTOP",$row['TOSTOP'],$Err_TOSTOP,"5","4","Y","","");
	Build_Fld_Entry("Effectivity End Date","effEnd","inputdate","Date","TOEFFE",$row['TOEFFE'],$Err_TOEFFE,"6","6","","","");
	Build_Flag_Entry("Paid Time","paidTime","YORN","TOPAID",$row['TOPAID'],$Err_TOPAID,"1","1","","","");
	Build_Flag_Entry("Adjust Worked Hours","adjustWork","YORN","TOADJE",$row['TOADJE'],$Err_TOADJE,"1","1","","","");
	Build_Flag_Entry("Lunch Break","lunchBreak","BY","TOLUBK",$row['TOLUBK'],$Err_TOLUBK,"1","1","","","");
	Build_Flag_Entry("Lunch Break Factoring","lunchFactor","LUNCHFACTR","TOLUBF",$row['TOLUBF'],$Err_TOLUBF,"1","1","","","");
	Build_DspFld("Elapsed Time",$row['TOELAP'],"","N");
	
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
		$_POST['schedule']    =$fromSchd;
		$_POST['scheduleDesc']=RetValue("SMSCHD=$fromSchd and SMEFFS is NULL", "HDSCHM ", "SMDESC");
		$_POST['dayCode']     =$fromDycd;
		$_POST['dayCodeDesc'] =RetValue("FLTYPE='DAYOFWEEK' and FLVALU='$fromDycd'", "SYFLAG", "FLDESC");
		$_POST['startTime']   =TimeInputFromDec($fromStrt);
		$_POST['effStart']    =DateInputFromISO($fromEffs);
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@schd", $_POST['schedule']);
	$_POST['dayCode']=strtoupper($_POST['dayCode']);
	Concat_Field("@@dycd", $_POST['dayCode']);
	Concat_Field("@@strt", $_POST['startTime']);
	Concat_Field("@@effs", $_POST['effStart']);
	Concat_Field("@@desc", $_POST['timeOffDesc']);
	Concat_Field("@@stop", $_POST['stopTime']);
	Concat_Field("@@effe", $_POST['effEnd']);
	if (!isset($_POST['paidTime'])) {$_POST['paidTime']="N";}
	Concat_Field("@@paid", $_POST['paidTime']);
	if (!isset($_POST['adjustWork'])) {$_POST['adjustWork']="N";}
	Concat_Field("@@adje", $_POST['adjustWork']);
	Concat_Field("@@lubk", $_POST['lunchBreak']);
	$_POST['lunchFactor']=strtoupper($_POST['lunchFactor']);
	Concat_Field("@@lubf", $_POST['lunchFactor']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HETTOM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$_POST['dayCodeDesc'] =RetValue("FLTYPE='DAYOFWEEK' and FLVALU='{$_POST['dayCode']}'", "SYFLAG", "FLDESC");
			if ($_POST['dayCode'] != "") {$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST['timeOffDesc'], $_POST['schedule'], $_POST['dayCodeDesc'], $_POST['dayCode'], "Start Time: {$_POST['startTime']}", "Effectivity Start Date: {$_POST['effStart']}");}
		    else                         {$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST['timeOffDesc'], $_POST['schedule'], "Start Time: {$_POST['startTime']}", "Effectivity Start Date: {$_POST['effStart']}", "", "");}		
		} else {
			$Err_TOSCHD=DecatErr_Field("@@schd", "schedule");
			$confMessage=Format_ConfMsg_Desc("", $_POST['timeOffDesc'], $_POST['schedule'], "<br>$Err_TOSCHD", "", "", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>