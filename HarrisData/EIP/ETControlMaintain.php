<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$backHome           = $_GET['backHome'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "E/T Control Maintenance";
$scriptName     = "ETControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HETCTU_E";

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if  (document.Chg.summarize.value ==\"\" || ";
	print "\n     document.Chg.deadTimePayCd.value ==\"\" || ";
	print "\n     document.Chg.reclaimResourceLevel.value ==\"\" || ";
	print "\n     document.Chg.badgeFmt.value ==\"\" || ";
	print "\n     document.Chg.hoursWorkedFmt.value ==\"\" || ";
	print "\n     document.Chg.numberETDtaqJobs.value ==\"\" ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";

	print "\n if (editNum(document.Chg.lastCalendarNumberUsed, 7, 0) && ";
	print "\n     editNum(document.Chg.numberETDtaqJobs, 2, 0) && ";
	print "\n     editNum(document.Chg.userDefFld192, 7, 2) && ";
	print "\n     editNum(document.Chg.userDefFld292, 7, 2) && ";
	print "\n     editNum(document.Chg.userDefFld392, 7, 2)) ";
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
	$pageID = "ETCONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From ETCTRL ";
		$stmtSQL .= " Where RRN(ETCTRL)=1 ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$sec_01="N";
	$sec_02="Y";
	$sec_03="N";
	$sec_04="N";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField= "lastCalendarNumberUsed";
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_TADSEQ=DecatErr_Field("@@dseq", "lastDataCollectionSeqUsed");
			$Err_TACALN=DecatErr_Field("@@caln", "lastCalendarNumberUsed");
			$Err_TARTDH=DecatErr_Field("@@rtdh", "retainDetailHistory");
			$Err_TARDQR=DecatErr_Field("@@rdqr", "retainDtaqRecords");
			$Err_TAFDOR=DecatErr_Field("@@fdor", "feedOrdNoToJobNo");
			$Err_TASUMD=DecatErr_Field("@@sumd", "summarize");
			$Err_TARBOO=DecatErr_Field("@@rboo", "requireBothOnOffTrans");
			$Err_TADTPC=DecatErr_Field("@@dtpc", "deadTimePayCd");
			$Err_TACDEP=DecatErr_Field("@@cdep", "createDeadTimeExitPgm");
			$Err_TADTAC=DecatErr_Field("@@dtac", "calcDeadTimeAtShiftEnd");
			$Err_TAFPEP=DecatErr_Field("@@fpep", "feedPayrollExitPgm");
			$Err_TARCLR=DecatErr_Field("@@rclr", "reclainResourceLevel");
			$Err_TAALRT=DecatErr_Field("@@alrt", "allowResetTime");
			$Err_TANMDQ=DecatErr_Field("@@nmdq", "numberETDtaqJobs");
			$Err_TAU1DS=DecatErr_Field("@@u1ds", "userDefNumScreenDesc1");
			$Err_TAU1H1=DecatErr_Field("@@u1h1", "userDefNumHead11");
			$Err_TAU1H2=DecatErr_Field("@@u1h2", "userDefNumHead12");
			$Err_TAU2DS=DecatErr_Field("@@u2ds", "userDefNumScreenDesc2");
			$Err_TAU2H1=DecatErr_Field("@@u2h1", "userDefNumHead21");
			$Err_TAU2H2=DecatErr_Field("@@u2h2", "userDefNumHead22");
			$Err_TAU3DS=DecatErr_Field("@@u3ds", "userDefNumScreenDesc3");
			$Err_TAU3H1=DecatErr_Field("@@u3h1", "userDefNumHead31");
			$Err_TAU3H2=DecatErr_Field("@@u3h2", "userDefNumHead32");
			$Err_TAU4DS=DecatErr_Field("@@u4ds", "userDefAlphScreenDesc4");
			$Err_TAU4H1=DecatErr_Field("@@u4h1", "userDefAlphHead41");
			$Err_TAU4H2=DecatErr_Field("@@u4h2", "userDefAlphHead42");
			$Err_TAU5DS=DecatErr_Field("@@u5ds", "userDefAlphScreenDesc5");
			$Err_TAU5H1=DecatErr_Field("@@u5h1", "userDefAlphHead51");
			$Err_TAU5H2=DecatErr_Field("@@u5h2", "userDefAlphHead52");
			$Err_TAU6DS=DecatErr_Field("@@u6ds", "userDefAlphScreenDesc6");
			$Err_TAU6H1=DecatErr_Field("@@u6h1", "userDefAlphHead61");
			$Err_TAU6H2=DecatErr_Field("@@u6h2", "userDefAlphHead62");
			$Err_TAUSR1=DecatErr_Field("@@usr1", "userDefFld192");
			$Err_TAUSR2=DecatErr_Field("@@usr2", "userDefFld292");
			$Err_TAUSR3=DecatErr_Field("@@usr3", "userDefFld392");
			$Err_TAUSR4=DecatErr_Field("@@usr4", "userDefFld415");
			$Err_TAUSR5=DecatErr_Field("@@usr5", "userDefFld515");
			$Err_TAUSR6=DecatErr_Field("@@usr6", "userDefFld615");
			$Err_TABDGF=DecatErr_Field("@@bdgf", "badgeFmt");
			$Err_TADPRW=DecatErr_Field("@@dprw", "displayPiecesToReworkFld");
			$Err_TAHWKF=DecatErr_Field("@@hwkf", "hoursWorkedFmt");
			$Err_TADSSF=DecatErr_Field("@@dssf", "displaySecondsFld");
			$Err_TAHLDH=DecatErr_Field("@@hldh", "placeHaltErrorsOnHold");
			$Err_TASUPA=DecatErr_Field("@@supa", "supervisorApprovalCd");
			$Err_TALTFM=DecatErr_Field("@@ltfm", "lunchTimeFmt");
			$Err_TAHWVL=DecatErr_Field("@@hwvl", "useWorkedValues");
			$Err_TAWGPS=DecatErr_Field("@@wgps", "useWorkedGroupSched");
			$errFound= "";
		}

		$row['TADSEQ']=Decat_Field("@@dseq", $edtVar);
		$row['TACALN']=Decat_Field("@@caln", $edtVar);
		$row['TARTDH']=Decat_Field("@@rtdh", $edtVar);
		$row['TARDQR']=Decat_Field("@@rdqr", $edtVar);
		$row['TAFDOR']=Decat_Field("@@fdor", $edtVar);
		$row['TASUMD']=Decat_Field("@@sumd", $edtVar);
		$row['TARBOO']=Decat_Field("@@rboo", $edtVar);
		$row['TADTPC']=Decat_Field("@@dtpc", $edtVar);
		$row['TACDEP']=Decat_Field("@@cdep", $edtVar);
		$row['TADTAC']=Decat_Field("@@dtac", $edtVar);
		$row['TAFPEP']=Decat_Field("@@fpep", $edtVar);
		$row['TARCLR']=Decat_Field("@@rclr", $edtVar);
		$row['TAALRT']=Decat_Field("@@alrt", $edtVar);
		$row['TANMDQ']=Decat_Field("@@nmdq", $edtVar);
		$row['TAU1DS']=Decat_Field("@@u1ds", $edtVar);
		$row['TAU1H1']=Decat_Field("@@u1h1", $edtVar);
		$row['TAU1H2']=Decat_Field("@@u1h2", $edtVar);
		$row['TAU2DS']=Decat_Field("@@u2ds", $edtVar);
		$row['TAU2H1']=Decat_Field("@@u2h1", $edtVar);
		$row['TAU2H2']=Decat_Field("@@u2h2", $edtVar);
		$row['TAU3DS']=Decat_Field("@@u3ds", $edtVar);
		$row['TAU3H1']=Decat_Field("@@u3h1", $edtVar);
		$row['TAU3H2']=Decat_Field("@@u3h2", $edtVar);
		$row['TAU4DS']=Decat_Field("@@u4ds", $edtVar);
		$row['TAU4H1']=Decat_Field("@@u4h1", $edtVar);
		$row['TAU4H2']=Decat_Field("@@u4h2", $edtVar);
		$row['TAU5DS']=Decat_Field("@@u5ds", $edtVar);
		$row['TAU5H1']=Decat_Field("@@u5h1", $edtVar);
		$row['TAU5H2']=Decat_Field("@@u5h2", $edtVar);
		$row['TAU6DS']=Decat_Field("@@u6ds", $edtVar);
		$row['TAU6H1']=Decat_Field("@@u6h1", $edtVar);
		$row['TAU6H2']=Decat_Field("@@u6h2", $edtVar);
		$row['TAUSR1']=Decat_Field("@@usr1", $edtVar);
		$row['TAUSR2']=Decat_Field("@@usr2", $edtVar);
		$row['TAUSR3']=Decat_Field("@@usr3", $edtVar);
		$row['TAUSR4']=Decat_Field("@@usr4", $edtVar);
		$row['TAUSR5']=Decat_Field("@@usr5", $edtVar);
		$row['TAUSR6']=Decat_Field("@@usr6", $edtVar);
		$row['TABDGF']=Decat_Field("@@bdgf", $edtVar);
		$row['TADPRW']=Decat_Field("@@dprw", $edtVar);
		$row['TAHWKF']=Decat_Field("@@hwkf", $edtVar);
		$row['TADSSF']=Decat_Field("@@dssf", $edtVar);
		$row['TAHLDH']=Decat_Field("@@hldh", $edtVar);
		$row['TASUPA']=Decat_Field("@@supa", $edtVar);
		$row['TALTFM']=Decat_Field("@@ltfm", $edtVar);
		$row['TAHWVL']=Decat_Field("@@hwvl", $edtVar);
		$row['TAWGPS']=Decat_Field("@@wgps", $edtVar);

	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";


	if (is_null($row['TACALN']) || trim($row['TACALN'])=="") {$row['TACALN']=1;}
	if (is_null($row['TARCLR']) || trim($row['TARCLR'])=="") {$row['TARCLR']="O";}
	if (is_null($row['TARTDH']) || trim($row['TARTDH'])=="") {$row['TARTDH']="N";}
	if (is_null($row['TARDQR']) || trim($row['TARDQR'])=="") {$row['TARDQR']="N";}
	if (is_null($row['TAALRT']) || trim($row['TAALRT'])=="") {$row['TAALRT']="N";}
	if (is_null($row['TAFDOR']) || trim($row['TAFDOR'])=="") {$row['TAFDOR']="N";}
	if (is_null($row['TASUMD']) || trim($row['TASUMD'])=="") {$row['TASUMD']="N";}
	if (is_null($row['TARBOO']) || trim($row['TARBOO'])=="") {$row['TARBOO']="N";}
	if (is_null($row['TABDGF']) || trim($row['TABDGF'])=="") {$row['TABDGF']="E";}
	if (is_null($row['TADPRW']) || trim($row['TADPRW'])=="") {$row['TADPRW']="N";}
	if (is_null($row['TAHWKF']) || trim($row['TAHWKF'])=="") {$row['TAHWKF']="D";}
	if (is_null($row['TADSSF']) || trim($row['TADSSF'])=="") {$row['TADSSF']="N";}
	if (is_null($row['TANMDQ']) || trim($row['TANMDQ'])=="") {$row['TANMDQ']=1;}


	Build_DspFld("E/T Release Version",$HDTARL,"","A");
	Build_DspFld("E/T Library Level",$HDTALL,"","A");

	Build_Flag_Entry("Last Calendar Number Used","lastCalendarNumberUsed","","TACALN",$row[TACALN],$Err_TACALN,"1","7","","","");
	Build_Flag_Entry("Retain Detail History","retainDetailHistory","YORN","TARTDH",$row[TARTDH],$Err_TARTDH,"1","1","N","","");
	Build_Flag_Entry("Retain Data Queue Records","retainDtaqRecords","YORN","TARDQR",$row[TARDQR],$Err_TARDQR,"1","1","N","","");
	Build_Flag_Entry("Allow Reset Time","allowResetTime","YORN","TAALRT",$row[TAALRT],$Err_TAALRT,"1","1","N","","");
	Build_Flag_Entry("Feed Order Number To Job Number","feedOrdNoToJobNo","YORN","TAFDOR",$row[TAFDOR],$Err_TAFDOR,"1","1","N","","");
	Build_Flag_Entry("Summarize","summarize","SUMMARIZEL","TASUMD",$row[TASUMD],$Err_TASUMD,"1","1","Y","","");
	Build_Flag_Entry("Require Both On/Off Transactions","requireBothOnOffTrans","YORN","TARBOO",$row[TARBOO],$Err_TARBOO,"1","1","N","","");
	Build_Flag_Entry("Calculate Dead Time At Shift End","calcDeadTimeAtShiftEnd","YORN","TADTAC",$row[TADTAC],$Err_TADTAC,"1","1","N","","");

	$fieldDesc=RetValue("C2CODE='$row[TADTPC]'", "PRCODE", "C2DESC");
	$textOvr=SetTextOvr($Err_TADTPC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Dead Time Pay Code</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"deadTimePayCd\" value=\"" . rtrim($row['TADTPC']) . "\" size=\"1\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=deadTimePayCd&amp;fldDesc=deadTimePayCdDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"deadTimePayCdDesc\">$fieldDesc</span>";

	print "\n </tr> ";
	DspErrMsg($Err_TADTPC);

	Build_Flag_Entry("Create Dead Time Exit Program","createDeadTimeExitPgm","","TACDEP",$row[TACDEP],$Err_TACDEP,"10","10","","","");
	Build_Flag_Entry("Reclaim Resource Level","reclaimResourceLevel","RECLAIMLVL","TARCLR",$row[TARCLR],$Err_TARCLR,"1","1","Y","","");
	Build_Flag_Entry("Badge Format","badgeFmt","BADGEFMT","TABDGF",$row[TABDGF],$Err_TABDGF,"1","1","Y","","");
	Build_Flag_Entry("Display Pieces To Rework Field","displayPiecesToReworkFld","YORN","TADPRW",$row[TADPRW],$Err_TADPRW,"1","1","N","","");
	Build_Flag_Entry("Hours Worked Format","hoursWorkedFmt","HOURWRKFMT","TAHWKF",$row[TAHWKF],$Err_TAHWKF,"1","1","Y","","");
	Build_Flag_Entry("Display Seconds Field","displaySecondsFld","YORN","TADSSF",$row[TADSSF],$Err_TADSSF,"1","1","N","","");
	Build_Flag_Entry("Place Halt Errors On Hold","placeHaltErrorsOnHold","YORN","TAHLDH",$row[TAHLDH],$Err_TAHLDH,"1","1","Y","","");

	$fieldDesc=RetValue("EVTYPE='X' and EVCODE='$row[TASUPA]'", "HDEVNT", "EVDESC");
	$textOvr=SetTextOvr($Err_TASUPA);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Supervisor Approval Code</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"supervisorApprovalCd\" value=\"" . rtrim($row['TASUPA']) . "\" size=\"1\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=supervisorApprovalCd&amp;fldDesc=supervisorApprovalCdDesc&amp;fldType=X\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"supervisorApprovalCdDesc\">$fieldDesc</span>";
	print "\n </tr> ";
	DspErrMsg($Err_TASUPA);

	Build_Flag_Entry("Use Worked Group Schedule","useWorkedGroupSched","YORN","TAWGPS",$row[TAWGPS],$Err_TAWGPS,"1","1","N","","");
	Build_Flag_Entry("Use Worked Values","useWorkedValues","USEWRKVAL","TAHWVL",$row[TAHWVL],$Err_TAHWVL,"1","1","N","","");
	Build_Flag_Entry("Lunch Time Format","lunchTimeFmt","LUNCHTMFMT","TALTFM",$row[TALTFM],$Err_TALTFM,"1","1","N","","");
	Build_Flag_Entry("Number Of ET Data Queue Processing Jobs","numberETDtaqJobs","","TANMDQ",$row[TANMDQ],$Err_TANMDQ,"1","2","Y","","");
	print "\n </table> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">User Defined</legend>";
	print "\n <table $contentTable>";
	print "\n <tr>";
	print "\n   <th>&nbsp;</th>";
	print "\n   <th class=\"colhdr\">Screen Description</th>";
	print "\n   <th class=\"colhdr\">Report Heading 1</th>";
	print "\n   <th class=\"colhdr\">Report Heading 2</th>";
	print "\n   <th class=\"colhdr\">Default Value</th>";
	print "\n </tr>";

	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Numeric 1 </span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefNumScreenDesc1\" value=\"" . rtrim($row['TAU1DS']) . "\" size=\"30\" maxlength=\"25\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefNumHead11\" value=\"" . rtrim($row['TAU1H1']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefNumHead12\" value=\"" . rtrim($row['TAU1H2']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"userDefFld192\" value=\"" . rtrim($row['TAUSR1']) . "\" size=\"15\" maxlength=\"11\"></td> ";
	print "\n </tr> ";


	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Numeric 2 </span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefNumScreenDesc2\" value=\"" . rtrim($row['TAU2DS']) . "\" size=\"30\" maxlength=\"25\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefNumHead21\" value=\"" . rtrim($row['TAU2H1']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefNumHead22\" value=\"" . rtrim($row['TAU2H2']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"userDefFld292\" value=\"" . rtrim($row['TAUSR2']) . "\" size=\"15\" maxlength=\"11\"></td> ";
	print "\n </tr> ";


	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Numeric 3 </span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefNumScreenDesc3\" value=\"" . rtrim($row['TAU3DS']) . "\" size=\"30\" maxlength=\"25\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefNumHead31\" value=\"" . rtrim($row['TAU3H1']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefNumHead32\" value=\"" . rtrim($row['TAU3H2']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"userDefFld392\" value=\"" . rtrim($row['TAUSR3']) . "\" size=\"15\" maxlength=\"11\"></td> ";
	print "\n </tr> ";


	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Alpha 1 </span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefAlphScreenDesc4\" value=\"" . rtrim($row['TAU4DS']) . "\" size=\"30\" maxlength=\"25\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefAlphHead41\" value=\"" . rtrim($row['TAU4H1']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefAlphHead42\" value=\"" . rtrim($row['TAU4H2']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefFld415\" value=\"" . rtrim($row['TAUSR4']) . "\" size=\"15\" maxlength=\"15\"></td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Alpha 2 </span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefAlphScreenDesc5\" value=\"" . rtrim($row['TAU5DS']) . "\" size=\"30\" maxlength=\"25\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefAlphHead51\" value=\"" . rtrim($row['TAU5H1']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefAlphHead52\" value=\"" . rtrim($row['TAU5H2']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefFld515\" value=\"" . rtrim($row['TAUSR5']) . "\" size=\"15\" maxlength=\"15\"></td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Alpha 3 </span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefAlphScreenDesc6\" value=\"" . rtrim($row['TAU6DS']) . "\" size=\"30\" maxlength=\"25\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefAlphHead61\" value=\"" . rtrim($row['TAU6H1']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefAlphHead62\" value=\"" . rtrim($row['TAU6H2']) . "\" size=\"15\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"userDefFld615\" value=\"" . rtrim($row['TAUSR6']) . "\" size=\"15\" maxlength=\"15\"></td> ";
	print "\n </tr> ";
	print "\n </table></fieldset>";


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
	$edtVar= "";

	Concat_Field("@@dseq", $_POST['lastDataCollectionSeqUsed']);
	Concat_Field("@@caln", $_POST['lastCalendarNumberUsed']);
	if (!isset($_POST['retainDetailHistory'])) {$_POST['retainDetailHistory']="Y";}
	Concat_Field("@@rtdh", $_POST['retainDetailHistory']);
	if (!isset($_POST['retainDtaqRecords'])) {$_POST['retainDtaqRecords']="N";}
	Concat_Field("@@rdqr", $_POST['retainDtaqRecords']);
	if (!isset($_POST['allowResetTime'])) {$_POST['allowResetTime']="N";}
	Concat_Field("@@alrt", $_POST['allowResetTime']);
	if (!isset($_POST['feedOrdNoToJobNo'])) {$_POST['feedOrdNoToJobNo']="N";}
	Concat_Field("@@fdor", $_POST['feedOrdNoToJobNo']);
	Concat_Field("@@sumd", strtoupper($_POST['summarize']));
	if (!isset($_POST['reqBothOnOffTrans'])) {$_POST['reqBothOnOffTrans']="N";}
	Concat_Field("@@rboo", $_POST['reqBothOnOffTrans']);
	if (!isset($_POST['calcDeadTimeAtShiftEnd'])) {$_POST['calcDeadTimeAtShiftEnd']=" ";}
	Concat_Field("@@dtac", $_POST['calcDeadTimeAtShiftEnd']);
	Concat_Field("@@dtpc", strtoupper($_POST['deadTimePayCd']));
	Concat_Field("@@cdep", $_POST['createDeadTimeExitPgm']);
	Concat_Field("@@rclr", strtoupper($_POST['reclaimResourceLevel']));
	Concat_Field("@@bdgf", strtoupper($_POST['badgeFmt']));
	if (!isset($_POST['displayPiecesToReworkFld'])) {$_POST['displayPiecesToReworkFld']="N";}
	Concat_Field("@@dprw", $_POST['displayPiecesToReworkFld']);
	if (!isset($_POST['hoursWorkedFmt'])) {$_POST['hoursWorkedFmt']="N";}
	Concat_Field("@@hwkf", strtoupper($_POST['hoursWorkedFmt']));
	if (!isset($_POST['displaySecondsFld'])) {$_POST['displaySecondsFld']="N";}
	Concat_Field("@@dssf", $_POST['displaySecondsFld']);
	if (!isset($_POST['placeHldtErrorsOnHold'])) {$_POST['placeHaltErrorsOnHold']="N";}
	Concat_Field("@@hldh", $_POST['placeHaltErrorsOnHold']);
	Concat_Field("@@supa", strtoupper($_POST['supervisorApprovalCd']));
	if (!isset($_POST['useWorkedGroupSched'])) {$_POST['useWorkedGroupSched']=" ";}
	Concat_Field("@@wgps", $_POST['useWorkedGroupSched']);
	if (!isset($_POST['useWorkedValues'])) {$_POST['useWorkedValues']=" ";}
	Concat_Field("@@hwvl", strtoupper($_POST['useWorkedValues']));
	if (!isset($_POST['lunchTimeFmt'])) {$_POST['lunchTimeFmt']=" ";}
	Concat_Field("@@ltfm", $_POST['lunchTimeFmt']);
	if (!isset($_POST['numberETDtaqJobs'])) {$_POST['numberETDtaqJobs']=1;}
	Concat_Field("@@nmdq", $_POST['numberETDtaqJobs']);
	Concat_Field("@@fpep", $_POST['feedPayrollExitPgm']);

	Concat_Field("@@u1ds", $_POST['userDefNumScreenDesc1']);
	Concat_Field("@@u1h1", $_POST['userDefNumHead11']);
	Concat_Field("@@u1h2", $_POST['userDefNumHead12']);
	Concat_Field("@@u2ds", $_POST['userDefNumScreenDesc2']);
	Concat_Field("@@u2h1", $_POST['userDefNumHead21']);
	Concat_Field("@@u2h2", $_POST['userDefNumHead22']);
	Concat_Field("@@u3ds", $_POST['userDefNumScreenDesc3']);
	Concat_Field("@@u3h1", $_POST['userDefNumHead31']);
	Concat_Field("@@u3h2", $_POST['userDefNumHead32']);
	Concat_Field("@@u4ds", $_POST['userDefAlphScreenDesc4']);
	Concat_Field("@@u4h1", $_POST['userDefAlphHead41']);
	Concat_Field("@@u4h2", $_POST['userDefAlphHead42']);
	Concat_Field("@@u5ds", $_POST['userDefAlphScreenDesc5']);
	Concat_Field("@@u5h1", $_POST['userDefAlphHead51']);
	Concat_Field("@@u5h2", $_POST['userDefAlphHead52']);
	Concat_Field("@@u6ds", $_POST['userDefAlphScreenDesc6']);
	Concat_Field("@@u6h1", $_POST['userDefAlphHead61']);
	Concat_Field("@@u6h2", $_POST['userDefAlphHead62']);
	Concat_Field("@@usr1", $_POST['userDefFld192']);
	Concat_Field("@@usr2", $_POST['userDefFld292']);
	Concat_Field("@@usr3", $_POST['userDefFld392']);
	Concat_Field("@@usr4", $_POST['userDefFld415']);
	Concat_Field("@@usr5", $_POST['userDefFld515']);
	Concat_Field("@@usr6", $_POST['userDefFld615']);

	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HETCTU_W",$userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$maintenanceCode="C";
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "E/T Control", "", "", "", "", "");
		$fileName="ETControl{$dataBaseID}.php";
		$includeName= "{$homePath}{$fileName}";
		Write_Control_File($homePath, $fileName, "HETCTU_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>