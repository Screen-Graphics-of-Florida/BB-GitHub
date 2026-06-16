<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$backHome           = $_GET['backHome'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "F/A Control Maintenance";
$scriptName     = "FAControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HFACTU_E";

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

	require_once 'CheckEnterChg.php';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';

	print "\n
	   function validate(chgForm) {
	      if (document.Chg.currentAcctPeriod.value==''
           || document.Chg.reclaimResourceLev.value==''
	       || document.Chg.glFeedDetail.value==''
	       || document.Chg.midquarterSchedule.value==''
           || document.Chg.glfeedSchedule.value==''
	       || document.Chg.taxreportSchedule.value=='')
	      {alert(\"$reqFieldError\");
	       return false;
	      };
	      if (editNum(document.Chg.currentAcctPeriod, 4, 0)
	       && editNum(document.Chg.midquarterSchedule, 2, 0)
	       && editNum(document.Chg.glfeedSchedule, 2, 0)
	       && editNum(document.Chg.taxreportSchedule, 2, 0)
	      ) {return true;};
	   }";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n</script>\n";
	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "FACONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL="";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL="Select * From FACTRL Where RRN(FACTRL)=1 ";
	}
	require 'stmtSQLEnd.php';

	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField="currentAcctPeriod";
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_ACURPD=DecatErr_Field("@@urpd","currentAcctPeriod");
			$Err_ACRCLR=DecatErr_Field("@@rclr","reclaimResourceLev");
			$Err_ACNODP=DecatErr_Field("@@nopd","calcdepreciation");
			$Err_ACGLTR=DecatErr_Field("@@gltr","glFeedDetail");
			$Err_ACGLRP=DecatErr_Field("@@glrp","feedtogl");
			$Err_ACMSCH=DecatErr_Field("@@msch","midquarterSchedule");
			$Err_ACBSCH=DecatErr_Field("@@bsch","glfeedSchedule");
			$Err_ACTSCH=DecatErr_Field("@@tsch","taxreportSchedule");
			$Err_ACDIFC=DecatErr_Field("@@difc","calcdiff");
			$Err_ACALIB=DecatErr_Field("@@alib","anticipatedDB");
			$Err_ACTSTP=DecatErr_Field("@@tstp","originalTimeStamp");
		}
		$row['ACURPD']=Decat_Field("@@urpd",$edtVar);
		$row['ACRCLR']=Decat_Field("@@rclr",$edtVar);
		$row['ACNODP']=Decat_Field("@@nopd",$edtVar);
		$row['ACGLTR']=Decat_Field("@@gltr",$edtVar);
		$row['ACGLRP']=Decat_Field("@@glrp",$edtVar);
		$row['ACMSCH']=Decat_Field("@@msch",$edtVar);
		$row['ACBSCH']=Decat_Field("@@bsch",$edtVar);
		$row['ACTSCH']=Decat_Field("@@tsch",$edtVar);
		$row['ACDIFC']=Decat_Field("@@difc",$edtVar);
		$row['ACALIB']=Decat_Field("@@alib",$edtVar);
		$row['ACTSTP']=Decat_Field("@@tstp", $edtVar);
	} else {
		$row['ACURPD']=PeriodInputFromCYP($row['ACURPD']);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_ACTSTP);
	Build_DspFld("F/A Release Version",$HDFARL,"","A");
	DspErrMsg($Err_ACTSTP);
	Build_DspFld("F/A Library Level",$HDFALL,"","A");
	print "\n <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['ACTSTP']) . "\"></td></tr> ";

	$textOvr=SetTextOvr($Err_ACURPD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Current Accounting Period</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"currentAcctPeriod\" value=\"" . rtrim($row['ACURPD']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n      <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;periodFld=currentAcctPeriod\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n </tr> ";
	DspErrMsg($Err_ACURPD);

	Build_Fld_Entry("Reclaim Resource Level","reclaimResourceLev","inputalph","RECLAIMLVL","ACRCLR",$row[ACRCLR],$Err_ACRCLR,"1","1","Y","","");
	Build_Fld_Entry("Calculate Depreciation On Annual System","calcdepreciation","inputalph","YORN","ACNODP",$row[ACNODP],$Err_ACNODP,"1","1","Y","","");
	Build_Fld_Entry("G/L Distribution Feed Detail Or Summary","glFeedDetail","inputalph","DORS","ACGLTR",$row[ACGLTR],$Err_ACGLTR,"1","1","Y","","");
	Build_Fld_Entry("Feed F/A To G/L","feedtogl","inputalph","YORN","ACGLRP",$row[ACGLRP],$Err_ACGLRP,"1","1","Y","","");

	$fielddesc=RetValue("(SMSCHD=$row[ACMSCH])", "FASCHD", "SMDESC");
	$textOvr=SetTextOvr($Err_ACMSCH);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Schedule For Midquarter Test</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"midquarterSchedule\" value=\"" . rtrim($row['ACMSCH']) . "\" size=\"2\" maxlength=\"2\">";
	print "\n      <a href=\"{$homeURL}{$phpPath}FAScheduleSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=midquarterSchedule&amp;fldDesc=midquarterSchedule_desc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"midquarterSchedule_desc\">$fielddesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_ACMSCH);

	$fielddesc=RetValue("(SMSCHD=$row[ACBSCH])", "FASCHD", "SMDESC");
	$textOvr=SetTextOvr($Err_ACBSCH);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Schedule To Feed To G/L</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"glfeedSchedule\" value=\"" . rtrim($row['ACBSCH']) . "\" size=\"2\" maxlength=\"2\">";
	print "\n      <a href=\"{$homeURL}{$phpPath}FAScheduleSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=glfeedSchedule&amp;fldDesc=glfeedSchedule_desc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"glfeedSchedule_desc\">$fielddesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_ACBSCH);

	$fielddesc=RetValue("(SMSCHD=$row[ACTSCH])", "FASCHD", "SMDESC");
	$textOvr=SetTextOvr($Err_ACTSCH);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Schedule For Tax Reports</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"taxreportSchedule\" value=\"" . rtrim($row['ACTSCH']) . "\" size=\"2\" maxlength=\"2\">";
	print "\n      <a href=\"{$homeURL}{$phpPath}FAScheduleSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=taxreportSchedule&amp;fldDesc=taxreportSchedule_desc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"taxreportSchedule_desc\">$fielddesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_ACTSCH);

	Build_Fld_Entry("Calculate Differential","calcdiff","inputalph","YORN","ACDIFC",$row[ACDIFC],$Err_ACDIFC,"1","1","Y","","");
	Build_Fld_Entry("Anticipated Database Prefix","anticipatedDB","inputalph","","ACALIB",$row[ACALIB],$Err_ACALIB,"2","2","N","","");

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
	print "</body></html>";
	exit;
}

if ($tag=="Edit_Data") {
	$edtVar="";
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	Concat_Field("@@urpd", $_POST['currentAcctPeriod']);
	Concat_Field("@@rclr", $_POST['reclaimResourceLev']=strtoupper($_POST['reclaimResourceLev']));
	if (!isset($_POST['calcdepreciation'])) {$_POST['calcdepreciation']="N";} Concat_Field("@@nopd", $_POST['calcdepreciation']);
	Concat_Field("@@gltr", $_POST['glFeedDetail']=strtoupper($_POST['glFeedDetail']));
	if (!isset($_POST['feedtogl'])) {$_POST['feedtogl']="N";} Concat_Field("@@glrp", $_POST['feedtogl']);
	Concat_Field("@@msch", $_POST['midquarterSchedule']);
	Concat_Field("@@bsch", $_POST['glfeedSchedule']);
	Concat_Field("@@tsch", $_POST['taxreportSchedule']);
	if (!isset($_POST['calcdiff'])) {$_POST['calcdiff']="N";} Concat_Field("@@difc", $_POST['calcdiff']);
	Concat_Field("@@alib", $_POST['anticipatedDB']=strtoupper($_POST['anticipatedDB']));

	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HFACTU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound=$returnValue['errFound'];
	$edtVar=$returnValue['edtVar'];
	$errVar=$returnValue['errVar'];
	$wrnVar=$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc("C", "F/A Control", "", "", "", "", "");
		$includeName= "{$homePath}FAControl{$dataBaseID}.php";
		$fileName="FAControl{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HFACTL_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>