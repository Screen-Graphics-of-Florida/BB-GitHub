<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = (isset($_GET['maintenanceCode']))  ? $_GET['maintenanceCode']  : "";
$errFound        = (isset($_GET['errFound']))         ? $_GET['errFound']         : "";
$fromSchd 	     = (isset($_GET['fromSchd']))         ? $_GET['fromSchd']         : 0;
$fromEffs        = (isset($_GET['fromEffs']))         ? $_GET['fromEffs']         : null;

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Schedule Definition Maintenance";
$scriptName     = "ScheduleDefinitionMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;fromSchd=" . urlencode(trim($fromSchd)) . "&amp;fromEffs=" . urlencode(trim($fromEffs));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HETSCM";
$dspMaxRows     = 1;

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=79";}

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
	require_once 'DateEdit.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.schdDesc.value ==\"\" ) ";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.schd, 3, 0) && ";
	print "\n     editNum(document.Chg.gpbs, 3, 0) && ";
	print "\n     editNum(document.Chg.gpas, 3, 0) && ";
	print "\n     editNum(document.Chg.gpbe, 3, 0) && ";
	print "\n     editNum(document.Chg.gpae, 3, 0) && ";
	print "\n     editNum(document.Chg.rvbs, 1, 2) && ";
	print "\n     editNum(document.Chg.rvas, 1, 2) && ";
	print "\n     editNum(document.Chg.rvbe, 1, 2) && ";
	print "\n     editNum(document.Chg.rvae, 1, 2) && ";
	print "\n     editNum(document.Chg.rsbs, 3, 0) && ";
	print "\n     editNum(document.Chg.rsas, 3, 0) && ";
	print "\n     editNum(document.Chg.rsbe, 3, 0) && ";
	print "\n     editNum(document.Chg.rsae, 3, 0) && ";
	print "\n     editNum(document.Chg.lgbs, 3, 0) && ";
	print "\n     editNum(document.Chg.lgas, 3, 0) && ";
	print "\n     editNum(document.Chg.lgbe, 3, 0) && ";
	print "\n     editNum(document.Chg.lgae, 3, 0) && ";
	print "\n     editNum(document.Chg.str1, 4, 0) && ";
	print "\n     editNum(document.Chg.str2, 4, 0) && ";
	print "\n     editNum(document.Chg.str3, 4, 0) && ";
	print "\n     editNum(document.Chg.str4, 4, 0) && ";
	print "\n     editNum(document.Chg.str5, 4, 0) && ";
	print "\n     editNum(document.Chg.str6, 4, 0) && ";
	print "\n     editNum(document.Chg.str7, 4, 0) && ";
	print "\n     editNum(document.Chg.sto1, 4, 0) && ";
	print "\n     editNum(document.Chg.sto2, 4, 0) && ";
	print "\n     editNum(document.Chg.sto3, 4, 0) && ";
	print "\n     editNum(document.Chg.sto4, 4, 0) && ";
	print "\n     editNum(document.Chg.sto5, 4, 0) && ";
	print "\n     editNum(document.Chg.sto6, 4, 0) && ";
	print "\n     editNum(document.Chg.sto7, 4, 0)) ";
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
	$pageID = "SCHEDULEDEFINITIONMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($fromEffs=="" || $fromEffs==0) {$fromEffs=null;}
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From HDSCHM ";
		if  (!$fromEffs) {
			$stmtSQL .= " Where SMSCHD=$fromSchd and SMEFFS is null ";
		}  else  {
			$stmtSQL .= " Where SMSCHD=$fromSchd and SMEFFS='$fromEffs' ";
		}
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hprprm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hprprm_OPT['sec_01'];
	$sec_02=$hprprm_OPT['sec_02'];
	$sec_03=$hprprm_OPT['sec_03'];
	$sec_04=$hprprm_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "schd";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_SMSCHD=DecatErr_Field("@@schd", "schd");
			$Err_SMDESC=DecatErr_Field("@@desc", "schdDesc");
			$Err_SMEFFS=DecatErr_Field("@@effs", "effs");
			$Err_SMEFFE=DecatErr_Field("@@effe", "effe");
			$Err_SMTIMZ=DecatErr_Field("@@timz", "timz");
			$Err_SMSHFT=DecatErr_Field("@@shft", "shft");
			$Err_SMGPBS=DecatErr_Field("@@gpbs", "gpbs");
			$Err_SMGPAS=DecatErr_Field("@@gpas", "gpas");
			$Err_SMGPBE=DecatErr_Field("@@gpbe", "gpbe");
			$Err_SMGPAE=DecatErr_Field("@@gpae", "gpae");
			$Err_SMRTBS=DecatErr_Field("@@rtbs", "rtbs");
			$Err_SMRTAS=DecatErr_Field("@@rtas", "rtas");
			$Err_SMRTBE=DecatErr_Field("@@rtbe", "rtbe");
			$Err_SMRTAE=DecatErr_Field("@@rtae", "rtae");
			$Err_SMPEBS=DecatErr_Field("@@pebs", "pebs");
			$Err_SMPEAS=DecatErr_Field("@@peas", "peas");
			$Err_SMPEBE=DecatErr_Field("@@pebe", "pebe");
			$Err_SMPEAE=DecatErr_Field("@@peae", "peae");
			$Err_SMRVBS=DecatErr_Field("@@rvbs", "rvbs");
			$Err_SMRVAS=DecatErr_Field("@@rvas", "rvas");
			$Err_SMRVBE=DecatErr_Field("@@rvbe", "rvbe");
			$Err_SMRVAE=DecatErr_Field("@@rvae", "rvae");
			$Err_SMRSBS=DecatErr_Field("@@rsbs", "rsbs");
			$Err_SMRSAS=DecatErr_Field("@@rsas", "rsas");
			$Err_SMRSBE=DecatErr_Field("@@rsbe", "rsbe");
			$Err_SMRSAE=DecatErr_Field("@@rsae", "rsae");
			$Err_SMWKDB=DecatErr_Field("@@wkdb", "wkdb");
			$Err_SMRRTS=DecatErr_Field("@@rrts", "rrts");
			$Err_SMRLTS=DecatErr_Field("@@rlts", "rlts");
			$Err_SMLGBS=DecatErr_Field("@@lgbs", "lgbs");
			$Err_SMLGAS=DecatErr_Field("@@lgas", "lgas");
			$Err_SMLGBE=DecatErr_Field("@@lgbe", "lgbe");
			$Err_SMLGAE=DecatErr_Field("@@lgae", "lgae");

			$Err_cod1=DecatErr_Field("@@cod1", "cod1");
			$Err_str1=DecatErr_Field("@@str1", "str1");
			$Err_sto1=DecatErr_Field("@@sto1", "sto1");
			$Err_pyc1=DecatErr_Field("@@pyc1", "pyc1");
			$Err_cod2=DecatErr_Field("@@cod2", "cod2");
			$Err_str2=DecatErr_Field("@@str2", "str2");
			$Err_sto2=DecatErr_Field("@@sto2", "sto2");
			$Err_pyc2=DecatErr_Field("@@pyc2", "pyc2");
			$Err_cod3=DecatErr_Field("@@cod3", "cod3");
			$Err_str3=DecatErr_Field("@@str3", "str3");
			$Err_sto3=DecatErr_Field("@@sto3", "sto3");
			$Err_pyc3=DecatErr_Field("@@pyc3", "pyc3");
			$Err_cod4=DecatErr_Field("@@cod4", "cod4");
			$Err_str4=DecatErr_Field("@@str4", "str4");
			$Err_sto4=DecatErr_Field("@@sto4", "sto4");
			$Err_pyc4=DecatErr_Field("@@pyc4", "pyc4");
			$Err_cod5=DecatErr_Field("@@cod5", "cod5");
			$Err_str5=DecatErr_Field("@@str5", "str5");
			$Err_sto5=DecatErr_Field("@@sto5", "sto5");
			$Err_pyc5=DecatErr_Field("@@pyc5", "pyc5");
			$Err_cod6=DecatErr_Field("@@cod6", "cod6");
			$Err_str6=DecatErr_Field("@@str6", "str6");
			$Err_sto6=DecatErr_Field("@@sto6", "sto6");
			$Err_pyc6=DecatErr_Field("@@pyc6", "pyc6");
			$Err_cod7=DecatErr_Field("@@cod7", "cod7");
			$Err_str7=DecatErr_Field("@@str7", "str7");
			$Err_sto7=DecatErr_Field("@@sto7", "sto7");
			$Err_pyc7=DecatErr_Field("@@pyc7", "pyc7");
		}

		$row['SMSCHD']=Decat_Field("@@schd", $edtVar);
		$row['SMDESC']=Decat_Field("@@desc", $edtVar);
		$row['SMEFFS']=Decat_Field("@@effs", $edtVar);
		$row['SMEFFE']=Decat_Field("@@effe", $edtVar);
		$row['SMTIMZ']=Decat_Field("@@timz", $edtVar);
		$row['SMSHFT']=Decat_Field("@@shft", $edtVar);
		$row['SMGPBS']=Decat_Field("@@gpbs", $edtVar);
		$row['SMGPAS']=Decat_Field("@@gpas", $edtVar);
		$row['SMGPBE']=Decat_Field("@@gpbe", $edtVar);
		$row['SMGPAE']=Decat_Field("@@gpae", $edtVar);
		$row['SMRTBS']=Decat_Field("@@rtbs", $edtVar);
		$row['SMRTAS']=Decat_Field("@@rtas", $edtVar);
		$row['SMRTBE']=Decat_Field("@@rtbe", $edtVar);
		$row['SMRTAE']=Decat_Field("@@rtae", $edtVar);
		$row['SMPEBS']=Decat_Field("@@pebs", $edtVar);
		$row['SMPEAS']=Decat_Field("@@peas", $edtVar);
		$row['SMPEBE']=Decat_Field("@@pebe", $edtVar);
		$row['SMPEAE']=Decat_Field("@@peae", $edtVar);
		$row['SMRVBS']=Decat_Field("@@rvbs", $edtVar);
		$row['SMRVAS']=Decat_Field("@@rvas", $edtVar);
		$row['SMRVBE']=Decat_Field("@@rvbe", $edtVar);
		$row['SMRVAE']=Decat_Field("@@rvae", $edtVar);
		$row['SMRSBS']=Decat_Field("@@rsbs", $edtVar);
		$row['SMRSAS']=Decat_Field("@@rsas", $edtVar);
		$row['SMRSBE']=Decat_Field("@@rsbe", $edtVar);
		$row['SMRSAE']=Decat_Field("@@rsae", $edtVar);
		$row['SMWKDB']=Decat_Field("@@wkdb", $edtVar);
		$row['SMRRTS']=Decat_Field("@@rrts", $edtVar);
		$row['SMRLTS']=Decat_Field("@@rlts", $edtVar);
		$row['SMLGBS']=Decat_Field("@@lgbs", $edtVar);
		$row['SMLGAS']=Decat_Field("@@lgas", $edtVar);
		$row['SMLGBE']=Decat_Field("@@lgbe", $edtVar);
		$row['SMLGAE']=Decat_Field("@@lgae", $edtVar);

		for ($i = 0; $i < 7; $i++)  {
			$j = $i+1;
			$mdCol[$i] = array($j, Decat_Field("@@dyc$j", $edtVar), Decat_Field("@@cod$j", $edtVar), Decat_Field("@@str$j", $edtVar), Decat_Field("@@sto$j", $edtVar), Decat_Field("@@pyc$j", $edtVar));
		}

		if ($errFound == "" && $maintenanceCode == "A") {
			$row['SMWKDB']="S";
			$row['SMPEBS']="Y";
			$row['SMPEBE']="Y";
			$row['SMPEAS']="Y";
			$row['SMPEAE']="Y";

			$mdCol = array(array(1,'S','NW',' ',' ',' '), array(2,'M','WK',' ',' ',' '), array(3,'T','WK',' ',' ',' '), array(4,'W','WK',' ',' ',' '), array(5,'H','WK',' ',' ',' '), array(6,'F','WK',' ',' ',' '), array(7,'A','NW',' ',' ',' ') );
		}

		$errFound = "";

	} else {
		$row['SMEFFS']=DateInputFromISO($row['SMEFFS']);
		$row['SMEFFE']=DateInputFromISO($row['SMEFFE']);
		$focusField = ($maintenanceCode=="Z") ? "schd" : "schdDesc";

		$dspMaxRows     = 7;
		if (is_null($fromEffs) || $fromEffs=="") {
			$WKEFFS=0;
			$fromEffs=null;
		}  else  {
			$WKEFFS=Date_FromISO_ToCYMD($fromEffs);
		}
		$stmtSQL= "";
		$stmtSQL .= " Select HDSCHD.*, ";
		$stmtSQL .= " case";
		$stmtSQL .= " when SDDYCD='S' then 1 when SDDYCD='M' then 2 when SDDYCD='T' then 3 when SDDYCD='W' then 4 when SDDYCD='H' then 5 when SDDYCD='F' then 6 when SDDYCD='A' then 7 ";
		$stmtSQL .= " end ";
		$stmtSQL .= " as dayord ";
		$stmtSQL .= " From HDSCHD ";
		$stmtSQL .= " Where SDSCHD=$fromSchd and SDEFFS=$WKEFFS ";
		$stmtSQL .= " order by dayord";

		require 'stmtSQLEnd.php';
		$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

		$rowCount=0;
		while ($row1 = db2_fetch_assoc($sqlResult)){
			if ($rowCount >= $dspMaxRows) {break;}
			$mdCol[$rowCount] = array($row1['DAYORD'], $row1['SDDYCD'], $row1['SDCODE'], TimeInputFromDec($row1['SDSTRT']), TimeInputFromDec($row1['SDSTOP']), $row1['SDPYCD']);
			$rowCount=$rowCount+1;
		}
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_SMSCHD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Schedule</span></td> ";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"schd\" value=\"" . rtrim($row['SMSCHD']) . "\" size=\"1\" maxlength=\"3\"> $reqFieldChar </td>";
	} else {
		print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"schd\" value=\"" . rtrim($row['SMSCHD']) . "\">$row[SMSCHD] </td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_SMSCHD);

	$F_EFFS=Format_Date(DateToCYMD($row['SMEFFS']), "D") ;
	$textOvr=SetTextOvr($Err_SMEFFS);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Effectivity Start Date</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputdate\"><input type=\"text\"   name=\"effs\" value=\"" . rtrim($row['SMEFFS']) . "\" size=\"8\" maxlength=\"10\">";
		print "\n                              <a href=\"javascript:calWindow('effs');\">$calendarImage</a> ";
		print "\n     </td>";
	} else {
		print "\n <td class=\"inputdate\"><input type=\"hidden\"   name=\"effs\" value=\"" . rtrim($row['SMEFFS']) . "\">$F_EFFS</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_SMEFFS);

	$F_EFFE=Format_Date(DateToCYMD($row['SMEFFE']), "D") ;
	$textOvr=SetTextOvr($Err_SMEFFE);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Effectivity End Date</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z" || $row['SMEFFS']!="") {
		print "\n <td class=\"inputdate\"><input type=\"text\"   name=\"effe\" value=\"" . rtrim($row['SMEFFE']) . "\" size=\"8\" maxlength=\"10\">";
		print "\n                              <a href=\"javascript:calWindow('effe');\">$calendarImage</a> ";
		print "\n     </td>";
	} else {
		print "\n <td class=\"inputdate\"><input type=\"hidden\"   name=\"effe\" value=\"" . rtrim($row['SMEFFE']) . "\">$F_EFFE</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_SMEFFE);

	$textOvr=SetTextOvr($Err_SMDESC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Description</span></td>";
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"schdDesc\" value=\"" . rtrim($row['SMDESC']) . "\" size=\"25\" maxlength=\"20\"> $reqFieldChar";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\"   name=\"schdDesc\" value=\"" . rtrim($row['SMDESC']) . "\">$row[SMDESC]</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_SMDESC);

	Build_Flag_Entry("Shift","shft","SHIFTL","SMSHFT",$row[SMSHFT],$Err_SMSHFT,"1","1","","","");
	Build_Flag_Entry("Work Day Based On","wkdb","SMWKDB","SMWKDB",$row[SMWKDB],$Err_SMWKDB,"1","1","Y","","");
	Build_Flag_Entry("Round Run Time To Shift","rrts","YORN","SMRRTS",$row[SMRRTS],$Err_SMRRTS,"1","1","","","");
	Build_Flag_Entry("Round Lunch Time To Schedule","rlts","YORN","SMRLTS",$row[SMRLTS],$Err_SMRLTS,"1","1","","","");
	print "\n </table>";

	print "\n <table $contentTable>";
	print "\n   <tr><th></th>";
	print "\n   <th class=\"grphdr\" colspan=\"2\">Shift Start</th>";
	print "\n   <th class=\"grphdr\" colspan=\"2\">Shift End</th>";
	print "\n   <th>&nbsp;</th> ";
	print "\n </tr>";

	print "\n   <tr><th>&nbsp;</th>";
	print "\n   <th class=\"colhdr\">   Early  </th>";
	print "\n   <th class=\"colhdr\">   Late   </th>";
	print "\n   <th class=\"colhdr\">   Early  </th>";
	print "\n   <th class=\"colhdr\">   Late   </th>";
	print "\n   <th>&nbsp;</th> ";
	print "\n </tr>";

	print "\n <tr>";
	$textOvr=SetTextOvr($Err_SMGPBS);
	print "\n <td class=\"dsphdr\"><span $textOvr>Grace Period (Minutes)</span></td>";
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"gpbs\" value=\"" . rtrim($row['SMGPBS']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"gpbs\" value=\"" . rtrim($row['SMGPBS']) . "\">$row[SMGPBS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMGPAS);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"gpas\" value=\"" . rtrim($row['SMGPAS']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"gpas\" value=\"" . rtrim($row['SMGPAS']) . "\">$row[SMGPAS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMGPBE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"gpbe\" value=\"" . rtrim($row['SMGPBE']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"gpbe\" value=\"" . rtrim($row['SMGPBE']) . "\">$row[SMGPBE]</td>";
	}
	$textOvr=SetTextOvr($Err_SMGPAE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"gpae\" value=\"" . rtrim($row['SMGPAE']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"gpae\" value=\"" . rtrim($row['SMGPAE']) . "\">$row[SMGPAE]</td>";
	}
	DspErrMsg($Err_SMGPBS);
	DspErrMsg($Err_SMGPAS);
	DspErrMsg($Err_SMGPBE);
	DspErrMsg($Err_SMGPAE);
	print "\n   <td>&nbsp;</td> ";
	print "\n </tr>";

	print "\n <tr>";
	$textOvr=SetTextOvr($Err_SMRTBS);
	print "\n <td class=\"dsphdr\"><span $textOvr>Round To Schedule</span></td>";
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		$fldChecked=Field_Checked($row[SMRTBS],"Y");
		print "\n <td class=\"colcode\"><input type=\"checkbox\"   name=\"rtbs\" value=\"Y\"  $fldChecked></td>";
	} else {
		print "\n <td class=\"colcode\"><input type=\"hidden\"   name=\"rtbs\" value=\"" . rtrim($row['SMRTBS']) . "\">$row[SMRTBS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMRTAS);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		$fldChecked=Field_Checked($row[SMRTAS],"Y");
		print "\n <td class=\"colcode\"><input type=\"checkbox\"   name=\"rtas\" value=\"Y\"  $fldChecked></td>";
	} else {
		print "\n <td class=\"colcode\"><input type=\"hidden\"   name=\"rtas\" value=\"" . rtrim($row['SMRTAS']) . "\">$row[SMRTAS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMRTBE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		$fldChecked=Field_Checked($row[SMRTBE],"Y");
		print "\n <td class=\"colcode\"><input type=\"checkbox\"   name=\"rtbe\" value=\"Y\"  $fldChecked></td>";
	} else {
		print "\n <td class=\"colcode\"><input type=\"hidden\"   name=\"rtbe\" value=\"" . rtrim($row['SMRTBE']) . "\">$row[SMRTBE]</td>";
	}
	$textOvr=SetTextOvr($Err_SMRTAE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		$fldChecked=Field_Checked($row[SMRTAE],"Y");
		print "\n <td class=\"colcode\"><input type=\"checkbox\"   name=\"rtae\" value=\"Y\"  $fldChecked></td>";
	} else {
		print "\n <td class=\"colcode\"><input type=\"hidden\"   name=\"rtae\" value=\"" . rtrim($row['SMRTAE']) . "\">$row[SMRTAE]</td>";
	}
	DspErrMsg($Err_SMRTBS);
	DspErrMsg($Err_SMRTAS);
	DspErrMsg($Err_SMRTBE);
	DspErrMsg($Err_SMRTAE);
	print "\n   <td>&nbsp;</td> ";
	print "\n </tr>";

	print "\n <tr>";
	$textOvr=SetTextOvr($Err_SMPEBS);
	print "\n <td class=\"dsphdr\"><span $textOvr>Beyond Grace Period Exception</span></td>";
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		$fldChecked=Field_Checked($row[SMPEBS],"Y");
		print "\n <td class=\"colcode\"><input type=\"checkbox\"   name=\"pebs\" value=\"Y\"  $fldChecked></td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\"   name=\"pebs\" value=\"" . rtrim($row['SMPEBS']) . "\">$row[SMPEBS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMPEAS);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		$fldChecked=Field_Checked($row[SMPEAS],"Y");
		print "\n <td class=\"colcode\"><input type=\"checkbox\"   name=\"peas\" value=\"Y\"  $fldChecked></td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\"   name=\"peas\" value=\"" . rtrim($row['SMPEAS']) . "\">$row[SMPEAS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMPEBE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		$fldChecked=Field_Checked($row[SMPEBE],"Y");
		print "\n <td class=\"colcode\"><input type=\"checkbox\"   name=\"pebe\" value=\"Y\"  $fldChecked></td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\"   name=\"pebe\" value=\"" . rtrim($row['SMPEBE']) . "\">$row[SMPEBE]</td>";
	}
	$textOvr=SetTextOvr($Err_SMPEAE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		$fldChecked=Field_Checked($row[SMPEAE],"Y");
		print "\n <td class=\"colcode\"><input type=\"checkbox\"   name=\"peae\" value=\"Y\"  $fldChecked></td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\"   name=\"peae\" value=\"" . rtrim($row['SMPEAE']) . "\">$row[SMPEAE]</td>";
	}
	DspErrMsg($Err_SMPEBS);
	DspErrMsg($Err_SMPEAS);
	DspErrMsg($Err_SMPEBE);
	DspErrMsg($Err_SMPEAE);
	print "\n   <td>&nbsp;</td> ";
	print "\n </tr>";

	print "\n <tr>";
	$textOvr=SetTextOvr($Err_SMRVBS);
	print "\n <td class=\"dsphdr\"><span $textOvr>Round To Value</span></td>";
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inpunmbr\"><input type=\"text\"   name=\"rvbs\" value=\"" . rtrim($row['SMRVBS']) . "\" size=\"3\" maxlength=\"3\"><a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=ROUNDTOVAL&amp;flagSrchHdr=Round To Value&amp;fldName=rvbs\" onclick=\"$searchWinVar\"> $searchImage &nbsp;</a></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"rvbs\" value=\"" . rtrim($row['SMRVBS']) . "\">$row[SMRVBS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMRVAS);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"rvas\" value=\"" . rtrim($row['SMRVAS']) . "\" size=\"3\" maxlength=\"3\"><a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=ROUNDTOVAL&amp;flagSrchHdr=Round To Value&amp;fldName=rvas\" onclick=\"$searchWinVar\"> $searchImage &nbsp;</a></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"rvas\" value=\"" . rtrim($row['SMRVAS']) . "\">$row[SMRVAS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMRVBE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"rvbe\" value=\"" . rtrim($row['SMRVBE']) . "\" size=\"3\" maxlength=\"3\"><a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=ROUNDTOVAL&amp;flagSrchHdr=Round To Value&amp;fldName=rvbe\" onclick=\"$searchWinVar\"> $searchImage &nbsp;</a></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"rvbe\" value=\"" . rtrim($row['SMRVBE']) . "\">$row[SMRVBE]</td>";
	}
	$textOvr=SetTextOvr($Err_SMRVAE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"rvae\" value=\"" . rtrim($row['SMRVAE']) . "\" size=\"3\" maxlength=\"3\"><a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=ROUNDTOVAL&amp;flagSrchHdr=Round To Value&amp;fldName=rvae\" onclick=\"$searchWinVar\"> $searchImage &nbsp;</a></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"rvae\" value=\"" . rtrim($row['SMRVAE']) . "\">$row[SMRVAE]</td>";
	}
	DspErrMsg($Err_SMRVBS);
	DspErrMsg($Err_SMRVAS);
	DspErrMsg($Err_SMRVBE);
	DspErrMsg($Err_SMRVAE);
	print "\n   <td>&nbsp;</td> ";
	print "\n </tr>";

	print "\n <tr>";
	$textOvr=SetTextOvr($Err_SMRSBS);
	print "\n <td class=\"dsphdr\"><span $textOvr>Rounding Split (Minutes)</span></td>";
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"rsbs\" value=\"" . rtrim($row['SMRSBS']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"rsbs\" value=\"" . rtrim($row['SMRSBS']) . "\">$row[SMRSBS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMRSAS);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"rsas\" value=\"" . rtrim($row['SMRSAS']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"rsas\" value=\"" . rtrim($row['SMRSAS']) . "\">$row[SMRSAS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMRSBE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"rsbe\" value=\"" . rtrim($row['SMRSBE']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"rsbe\" value=\"" . rtrim($row['SMRSBE']) . "\">$row[SMRSBE]</td>";
	}
	$textOvr=SetTextOvr($Err_SMRSAE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"rsae\" value=\"" . rtrim($row['SMRSAE']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"rsae\" value=\"" . rtrim($row['SMRSAE']) . "\">$row[SMRSAE]</td>";
	}
	DspErrMsg($Err_SMRSBS);
	DspErrMsg($Err_SMRSAS);
	DspErrMsg($Err_SMRSBE);
	DspErrMsg($Err_SMRSAE);
	print "\n   <td>&nbsp;</td> ";
	print "\n </tr>";

	print "\n <tr>";
	$textOvr=SetTextOvr($Err_SMLGBS);
	print "\n <td class=\"dsphdr\"><span $textOvr>Lunch Grace Period (Minutes)</span></td>";
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"lgbs\" value=\"" . rtrim($row['SMLGBS']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"lgbs\" value=\"" . rtrim($row['SMLGBS']) . "\">$row[SMLGBS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMLGAS);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"lgas\" value=\"" . rtrim($row['SMLGAS']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"lgas\" value=\"" . rtrim($row['SMLGAS']) . "\">$row[SMLGAS]</td>";
	}
	$textOvr=SetTextOvr($Err_SMLGBE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"lgbe\" value=\"" . rtrim($row['SMLGBE']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"lgbe\" value=\"" . rtrim($row['SMLGBE']) . "\">$row[SMLGBE]</td>";
	}
	$textOvr=SetTextOvr($Err_SMLGAE);
	if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"lgae\" value=\"" . rtrim($row['SMLGAE']) . "\" size=\"3\" maxlength=\"3\"></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"lgae\" value=\"" . rtrim($row['SMLGAE']) . "\">$row[SMLGAE]</td>";
	}
	DspErrMsg($Err_SMLGBS);
	DspErrMsg($Err_SMLGAS);
	DspErrMsg($Err_SMLGBE);
	DspErrMsg($Err_SMLGAE);
	print "\n   <td>&nbsp;</td> ";
	print "\n </tr>";
	print "\n </table>";

	print "\n <fieldset><legend class=\"legendTitle\">Schedule Definition Detail</legend> ";
	print "\n <table $contentTable>";
	print "\n   <tr><th>&nbsp;</th>";
	print "\n   <th class=\"colhdr\" colspan=\"2\">ET Code</th>";
	print "\n   <th class=\"colhdr\" colspan=\"1\">   Start  </th>";
	print "\n   <th class=\"colhdr\" colspan=\"1\">   Stop   </th>";
	print "\n   <th class=\"colhdr\" colspan=\"2\">Pay Code</th>";
	print "\n   <th>&nbsp;</th> ";
	print "\n </tr>";

	$i = 1;
	foreach ($mdCol as $mdFld)  {
		$curDyc[$i]   = trim($mdFld['1']);
		$curCod[$i]   = trim($mdFld['2']);
		$curStr[$i]   = trim($mdFld['3']);
		$curSto[$i]   = trim($mdFld['4']);
		$curPyc[$i]   = trim($mdFld['5']);

		$dayName="";
		if ($curDyc[$i] == "S") {$dayName='Sunday';}
		if ($curDyc[$i] == "M") {$dayName='Monday';}
		if ($curDyc[$i] == "T") {$dayName='Tuesday';}
		if ($curDyc[$i] == "W") {$dayName='Wednesday';}
		if ($curDyc[$i] == "H") {$dayName='Thursday';}
		if ($curDyc[$i] == "F") {$dayName='Friday';}
		if ($curDyc[$i] == "A") {$dayName='Saturday';}

		print "\n <tr>";
		print "\n <td class=\"dsphdr\"><span $textOvr>$dayName</span></td>";
		$focusField="cod1";
		if ($maintenanceCode=="C" || $maintenanceCode=="A" || $maintenanceCode=="Z") {
			if ($i==1)  {
				$textOvr=SetTextOvr($Err_cod1);
			}  elseif ($i==2)  {
				$textOvr=SetTextOvr($Err_cod2);
			}  elseif ($i==3)  {
				$textOvr=SetTextOvr($Err_cod3);
			}  elseif ($i==4)  {
				$textOvr=SetTextOvr($Err_cod4);
			}  elseif ($i==5)  {
				$textOvr=SetTextOvr($Err_cod5);
			}  elseif ($i==6)  {
				$textOvr=SetTextOvr($Err_cod6);
			}  elseif ($i==7)  {
				$textOvr=SetTextOvr($Err_cod7);
			}
			$des[$i]=RetValue("EVCODE = '$curCod[$i]' ", "HDEVNT", "EVDESC") ;
			print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"cod$i\" value=\"" . rtrim($curCod[$i]) . "\" size=\"2\" maxlength=\"2\"> $reqFieldChar";
			print "\n  					<a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=cod$i&amp;fldDesc=des$i&amp;fldType=E\" onclick=\"$searchWinVar\">$searchImage</a></td>";
			print "\n <td class=\"dspalph\"><input type=\"hidden\"   name=\"des$i\" value=\"" . rtrim($des[$i]) . "\"> &nbsp; $des[$i]</td>";

			if ($i==1)  {
				$textOvr=SetTextOvr($Err_str1);
			}  elseif ($i==2)  {
				$textOvr=SetTextOvr($Err_str2);
			}  elseif ($i==3)  {
				$textOvr=SetTextOvr($Err_str3);
			}  elseif ($i==4)  {
				$textOvr=SetTextOvr($Err_str4);
			}  elseif ($i==5)  {
				$textOvr=SetTextOvr($Err_str5);
			}  elseif ($i==6)  {
				$textOvr=SetTextOvr($Err_str6);
			}  elseif ($i==7)  {
				$textOvr=SetTextOvr($Err_str7);
			}
			print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"str$i\" value=\"" . rtrim($curStr[$i]) . "\" size=\"4\" maxlength=\"4\"></td> ";

			if ($i==1)  {
				$textOvr=SetTextOvr($Err_sto1);
			}  elseif ($i==2)  {
				$textOvr=SetTextOvr($Err_sto2);
			}  elseif ($i==3)  {
				$textOvr=SetTextOvr($Err_sto3);
			}  elseif ($i==4)  {
				$textOvr=SetTextOvr($Err_sto4);
			}  elseif ($i==5)  {
				$textOvr=SetTextOvr($Err_sto5);
			}  elseif ($i==6)  {
				$textOvr=SetTextOvr($Err_sto6);
			}  elseif ($i==7)  {
				$textOvr=SetTextOvr($Err_sto7);
			}
			print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"sto$i\" value=\"" . rtrim($curSto[$i]) . "\" size=\"4\" maxlength=\"4\"></td> ";

			if ($i==1)  {
				$textOvr=SetTextOvr($Err_pyc1);
			}  elseif ($i==2)  {
				$textOvr=SetTextOvr($Err_pyc2);
			}  elseif ($i==3)  {
				$textOvr=SetTextOvr($Err_pyc3);
			}  elseif ($i==4)  {
				$textOvr=SetTextOvr($Err_pyc4);
			}  elseif ($i==5)  {
				$textOvr=SetTextOvr($Err_pyc5);
			}  elseif ($i==6)  {
				$textOvr=SetTextOvr($Err_pyc6);
			}  elseif ($i==7)  {
				$textOvr=SetTextOvr($Err_pyc7);
			}
			$payDes[$i] = RetValue("C2CODE = '$curPyc[$i]' ", "PRCODE", "C2DESC") ;
			print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"pyc$i\" value=\"" . rtrim($curPyc[$i]) . "\" size=\"3\" maxlength=\"3\">";
			print "\n  					<a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=pyc$i&amp;fldDesc=payDes$i\" onclick=\"$searchWinVar\">$searchImage &nbsp; </a></td>";
			print "\n <td class=\"dspalph\"><input type=\"hidden\"   name=\"payDes$i\" value=\"" . rtrim($payDes[$i]) . "\"> &nbsp; $payDes[$i]</td>";

			if ($i==1)  {
				DspErrMsg($Err_cod1);
				DspErrMsg($Err_str1);
				DspErrMsg($Err_sto1);
				DspErrMsg($Err_pyc1);
			}  elseif ($i==2)  {
				DspErrMsg($Err_cod2);
				DspErrMsg($Err_str2);
				DspErrMsg($Err_sto2);
				DspErrMsg($Err_pyc2);
			}  elseif ($i==3)  {
				DspErrMsg($Err_cod3);
				DspErrMsg($Err_str3);
				DspErrMsg($Err_sto3);
				DspErrMsg($Err_pyc3);
			}  elseif ($i==4)  {
				DspErrMsg($Err_cod4);
				DspErrMsg($Err_str4);
				DspErrMsg($Err_sto4);
				DspErrMsg($Err_pyc4);
			}  elseif ($i==5)  {
				DspErrMsg($Err_cod5);
				DspErrMsg($Err_str5);
				DspErrMsg($Err_sto5);
				DspErrMsg($Err_pyc5);
			}  elseif ($i==6)  {
				DspErrMsg($Err_cod6);
				DspErrMsg($Err_str6);
				DspErrMsg($Err_sto6);
				DspErrMsg($Err_pyc6);
			}  elseif ($i==7)  {
				DspErrMsg($Err_cod7);
				DspErrMsg($Err_str7);
				DspErrMsg($Err_sto7);
				DspErrMsg($Err_pyc7);
			}
		}
		print "\n </tr>";
		$i = $i + 1;
	}

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
	if ($maintenanceCode=="D") {
		$_POST['schd']        =$fromSchd;
		$_POST['effs']        =DateInputFromISO($fromEffs);
		if  (!$fromEffs) {
			$_POST['schdDesc']=RetValue("SMSCHD='$fromSchd' and SMEFFS is null", "HDSCHM", "SMDESC");
		}  else  {
			$_POST['schdDesc']=RetValue("SMSCHD='$fromSchd' and SMEFFS='$fromEffs'", "HDSCHM", "SMDESC");
		}
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@schd", $_POST['schd']);
	Concat_Field("@@effs", $_POST['effs']);
	Concat_Field("@@effe", $_POST['effe']);
	Concat_Field("@@desc", $_POST['schdDesc']);
	Concat_Field("@@shft", $_POST['shft']);
	Concat_Field("@@timz", $_POST['timz']);
	Concat_Field("@@wkdb", $_POST['wkdb']);
	Concat_Field("@@rrts", $_POST['rrts']);
	Concat_Field("@@rlts", $_POST['rlts']);
	Concat_Field("@@gpbs", $_POST['gpbs']);
	Concat_Field("@@gpas", $_POST['gpas']);
	Concat_Field("@@gpbe", $_POST['gpbe']);
	Concat_Field("@@gpae", $_POST['gpae']);
	if (!isset($_POST['rtbs'])) {$_POST['rtbs']="N";}
	Concat_Field("@@rtbs", $_POST['rtbs']);
	if (!isset($_POST['rtas'])) {$_POST['rtas']="N";}
	Concat_Field("@@rtas", $_POST['rtas']);
	if (!isset($_POST['rtbe'])) {$_POST['rtbe']="N";}
	Concat_Field("@@rtbe", $_POST['rtbe']);
	if (!isset($_POST['rtae'])) {$_POST['rtae']="N";}
	Concat_Field("@@rtae", $_POST['rtae']);
	if (!isset($_POST['pebs'])) {$_POST['pebs']="N";}
	Concat_Field("@@pebs", $_POST['pebs']);
	if (!isset($_POST['peas'])) {$_POST['peas']="N";}
	Concat_Field("@@peas", $_POST['peas']);
	if (!isset($_POST['pebe'])) {$_POST['pebe']="N";}
	Concat_Field("@@pebe", $_POST['pebe']);
	if (!isset($_POST['peae'])) {$_POST['peae']="N";}
	Concat_Field("@@peae", $_POST['peae']);
	Concat_Field("@@rvbs", $_POST['rvbs']);
	Concat_Field("@@rvas", $_POST['rvas']);
	Concat_Field("@@rvbe", $_POST['rvbe']);
	Concat_Field("@@rvae", $_POST['rvae']);
	Concat_Field("@@rsbs", $_POST['rsbs']);
	Concat_Field("@@rsas", $_POST['rsas']);
	Concat_Field("@@rsbe", $_POST['rsbe']);
	Concat_Field("@@rsae", $_POST['rsae']);
	Concat_Field("@@lgbs", $_POST['lgbs']);
	Concat_Field("@@lgas", $_POST['lgas']);
	Concat_Field("@@lgbe", $_POST['lgbe']);
	Concat_Field("@@lgae", $_POST['lgae']);

	for ($i = 1; $i < 8; $i++)  {
		if ($i == 1) {
			Concat_Field("@@dyc1", "S");
			$_POST['cod1']=strtoupper ($_POST['cod1']) ;
			Concat_Field("@@cod1", $_POST['cod1']) ;
			Concat_Field("@@str1", $_POST['str1']) ;
			Concat_Field("@@sto1", $_POST['sto1']) ;
			Concat_Field("@@pyc1", strtoupper($_POST['pyc1'])) ;
		}
		if ($i == 2) {
			Concat_Field("@@dyc2", "M");
			$_POST['cod2']=strtoupper ($_POST['cod2']) ;
			Concat_Field("@@cod2", $_POST['cod2']) ;
			Concat_Field("@@str2", $_POST['str2']) ;
			Concat_Field("@@sto2", $_POST['sto2']) ;
			Concat_Field("@@pyc2", strtoupper($_POST['pyc2'])) ;
		}
		if ($i == 3) {
			Concat_Field("@@dyc3", "T");
			$_POST['cod3']=strtoupper ($_POST['cod3']) ;
			Concat_Field("@@cod3", $_POST['cod3']) ;
			Concat_Field("@@str3", $_POST['str3']) ;
			Concat_Field("@@sto3", $_POST['sto3']) ;
			Concat_Field("@@pyc3", strtoupper($_POST['pyc3'])) ;
		}
		if ($i == 4) {
			Concat_Field("@@dyc4", "W");
			$_POST['cod4']=strtoupper ($_POST['cod4']) ;
			Concat_Field("@@cod4", $_POST['cod4']) ;
			Concat_Field("@@str4", $_POST['str4']) ;
			Concat_Field("@@sto4", $_POST['sto4']) ;
			Concat_Field("@@pyc4", strtoupper($_POST['pyc4'])) ;
		}
		if ($i == 5) {
			Concat_Field("@@dyc5", "H");
			$_POST['cod5']=strtoupper ($_POST['cod5']) ;
			Concat_Field("@@cod5", $_POST['cod5']) ;
			Concat_Field("@@str5", $_POST['str5']) ;
			Concat_Field("@@sto5", $_POST['sto5']) ;
			Concat_Field("@@pyc5", strtoupper($_POST['pyc5'])) ;
		}
		if ($i == 6) {
			Concat_Field("@@dyc6", "F");
			$_POST['cod6']=strtoupper ($_POST['cod6']) ;
			Concat_Field("@@cod6", $_POST['cod6']) ;
			Concat_Field("@@str6", $_POST['str6']) ;
			Concat_Field("@@sto6", $_POST['sto6']) ;
			Concat_Field("@@pyc6", strtoupper($_POST['pyc6'])) ;
		}
		if ($i == 7) {
			Concat_Field("@@dyc7", "A");
			$_POST['cod7']=strtoupper ($_POST['cod7']) ;
			Concat_Field("@@cod7", $_POST['cod7']) ;
			Concat_Field("@@str7", $_POST['str7']) ;
			Concat_Field("@@sto7", $_POST['sto7']) ;
			Concat_Field("@@pyc7", strtoupper($_POST['pyc7'])) ;
		}
	}

	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HETSCM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST[schdDesc], $_POST[schd], "Effectivity Start Date: $_POST[effs]", "", "", "");
		} else {
			$Err_SMSCHD=DecatErr_Field("@@schd", "schd");
			if ($Err_SMSCHD != "") {$Err_SMSCHD="<br>".$Err_SMSCHD;}
			$Err_SMEFFS=DecatErr_Field("@@effs", "effs");
			if ($Err_SMEFFS != "") {$Err_SMEFFS="<br>".$Err_SMEFFS;}
			$confMessage=Format_ConfMsg_Desc("", $_POST[schdDesc], $_POST[schd], "Effectivity Start Date: $_POST[effs]", "", $Err_SMSCHD, $Err_SMEFFS);
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;schd=" . urlencode(trim($_POST['schd'])) . "&amp;effs=" . urlencode(trim($_POST['effs'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}
?>