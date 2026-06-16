<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$fromCo             = $_GET['fromCo'];
$fromFac            = $_GET['fromFac'];
$fromDept           = $_GET['fromDept'];
$fromShift          = $_GET['fromShift'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Premium Rates Maintenance";
$scriptName     = "PremiumRatesMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;fromCo=" . urlencode(trim($fromCo)) . "&amp;fromFac=" . urlencode(trim($fromFac)) . "&amp;fromDept=" . urlencode(trim($fromDept)) . "&amp;fromShift=" . urlencode(trim($fromShift));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPRPRM_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=24";}

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
	require_once 'UpperCase.php';
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.applyPremToHourly.value ==\"\" || ";
	print "\n     document.Chg.applyPremToSalary.value ==\"\" || ";
	print "\n     document.Chg.shiftWorked.value ==\"\" || ";
	print "\n     document.Chg.procOTDTMult.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.coNum, 2, 0) && ";
	print "\n     editNum(document.Chg.facNum, 4, 0) && ";
	print "\n     editNum(document.Chg.premAmt, 3, 2) && ";
	print "\n     editNum(document.Chg.premPct, 3, 4)) ";
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
	$pageID = "PREMIUMRATESMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From PRPREM ";
		$stmtSQL .= " Where PPCOMP='$fromCo' and PPFACL='$fromFac' and PPDEPT='$fromDept' and PPSHFT='$fromShift' ";
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
			$focusField= "coNum";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_PPCOMP=DecatErr_Field("@@comp", "coNum");
			$Err_PPFACL=DecatErr_Field("@@facl", "facNum");
			$Err_PPDEPT=DecatErr_Field("@@dept", "dept");
			$Err_PPSHFT=DecatErr_Field("@@shft", "shiftWorked");
			$Err_PPDESC=DecatErr_Field("@@desc", "premDesc");
			$Err_PPHRLY=DecatErr_Field("@@hrly", "applyPremToHourly");
			$Err_PPSLRY=DecatErr_Field("@@slry", "applyPremToSalary");
			$Err_PPAMT =DecatErr_Field("@@amt@", "premAmt");
			$Err_PPPCT =DecatErr_Field("@@pct@", "premPct");
			$Err_PPOTM =DecatErr_Field("@@otm@", "procOTDTMult");
			$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");
			$errFound= "";
		}

		$row['PPCOMP']=Decat_Field("@@comp", $edtVar);
		$row['PPFACL']=Decat_Field("@@facl", $edtVar);
		$row['PPDEPT']=Decat_Field("@@dept", $edtVar);
		$row['PPSHFT']=Decat_Field("@@shft", $edtVar);
		$row['PPDESC']=Decat_Field("@@desc", $edtVar);
		$row['PPHRLY']=Decat_Field("@@hrly", $edtVar);
		$row['PPSLRY']=Decat_Field("@@slry", $edtVar);
		$row['PPAMT'] =Decat_Field("@@amt@", $edtVar);
		$row['PPPCT'] =Decat_Field("@@pct@", $edtVar);
		$row['PPOTM'] =Decat_Field("@@otm@", $edtVar);
		$row['PPTSTP']=Decat_Field("@@tstp", $edtVar);

	}	elseif ($maintenanceCode=="Z") {
		$focusField= "coNum";

	} else {
		$focusField= "premDesc";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_TSTP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>.</span></td>";
	print "\n     <td><input type=\"hidden\" name=\"timeStamp\" value=\"" . rtrim($row['PPTSTP']) . "\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_TSTP);
	
	if (is_null($row['PPCOMP']) || trim($row['PPCOMP'])=="") {$row['PPCOMP']=0;}
	if (is_null($row['PPFACL']) || trim($row['PPFACL'])=="")  {$row['PPFACL']=0;}
	$fieldDesc=RetValue("CFCOMP=$row[PPCOMP] and CFFACL=$row[PPFACL]", "HRCOFC", "CFNAME");
	$textOvr=SetTextOvr($Err_PPCOMP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company/Facility Number</span></td> ";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"coNum\" value=\"" . rtrim($row['PPCOMP']) . "\" size=\"1\" maxlength=\"2\"> / <input type=\"text\"   name=\"facNum\" value=\"" . rtrim($row['PPFACL']) . "\" size=\"1\" maxlength=\"4\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=coNum&amp;fldFac=facNum&amp;fldDesc=coNumDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"coNumDesc\">$fieldDesc</span></td>";
	} else {
		print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"coNum\" value=\"" . rtrim($row['PPCOMP']) . "\">$row[PPCOMP] / <input type=\"hidden\"   name=\"facNum\" value=\"" . rtrim($row['PPFACL']) . "\">$row[PPFACL]";
		print "\n     <span class=\"dspdesc\">$fieldDesc</span></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_PPCOMP);

	$fieldDesc=RetValue("EADEPT='$row[PPDEPT]'", "PREXAC", "EANAME");
	$textOvr=SetTextOvr($Err_PPDEPT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Department</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"dept\" value=\"" . rtrim($row['PPDEPT']) . "\" size=\"3\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DepartmentSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=dept&amp;fldDesc=deptDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"deptDesc\">$fieldDesc</span></td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\"   name=\"dept\" value=\"" . rtrim($row['PPDEPT']) . "\">$row[PPDEPT]";
		print "\n     <span class=\"dspdesc\" id=\"deptDesc\">$fieldDesc</span></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_PPDEPT);

	$textOvr=SetTextOvr($Err_PPSHFT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Shift Worked</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"shiftWorked\" value=\"" . rtrim($row['PPSHFT']) . "\" size=\"1\" maxlength=\"2\">$reqFieldChar</td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\"   name=\"shiftWorked\" value=\"" . rtrim($row['PPSHFT']) . "\">$row[PPSHFT]</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_PPSHFT);

	Build_Fld_Entry("Description","premDesc","inputalph","","PPDESC",$row[PPDESC],$Err_PPDESC,"25","20","","","");
	Build_Fld_Entry("Apply Premium To Hourly","applyPremToHourly","inputalph","YORN","PPHRLY",$row[PPHRLY],$Err_PPHRLY,"1","1","Y","","");
	Build_Fld_Entry("Apply Premium To Salary","applyPremToSalary","inputalph","YORN","PPSLRY",$row[PPSLRY],$Err_PPSLRY,"1","1","Y","","");
	Build_Fld_Entry("Premium Amount","premAmt","inputnmbr","","PPAMT",$row[PPAMT],$Err_PPAMT,"4","6","","","");
	Build_Fld_Entry("Premium Percent","premPct","inputnmbr","","PPPCT",$row[PPPCT],$Err_PPPCT,"4","8","","","");
	Build_Fld_Entry("Process OT/DT Multipliers","procOTDTMult","inputalph","YORN","PPOTM",$row[PPOTM],$Err_PPOTM,"1","1","Y","","");

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
	if ($maintenanceCode=="D" && is_null($_POST['coNum'])) {
		$_POST['coNum']        =$fromCo;
		$_POST['facNum']       =$fromFac;
		$_POST['dept']         =$fromDept;
		$_POST['shiftWorked']  =$fromShift;
		$_POST['premDesc']=RetValue("PPCOMP='$fromCo' and PPFACL='$fromFac' and PPDEPT='$fromDept' and PPSHFT='$fromShift'", "PRPREM", "PPDESC");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@comp", $_POST['coNum']);
	Concat_Field("@@facl", $_POST['facNum']);
	$_POST['dept']=strtoupper($_POST['dept']);  Concat_Field("@@dept", $_POST['dept']);
	$_POST['shiftWorked']=strtoupper($_POST['shiftWorked']);  Concat_Field("@@shft", $_POST['shiftWorked']);
	Concat_Field("@@desc", $_POST['premDesc']);
	if (!isset($_POST['applyPremToHourly'])) {$_POST['applyPremToHourly']="N";}
	Concat_Field("@@hrly", $_POST['applyPremToHourly']=strtoupper($_POST['applyPremToHourly']));
	if (!isset($_POST['applyPremToSalary'])) {$_POST['applyPremToSalary']="N";}
	Concat_Field("@@slry", $_POST['applyPremToSalary']=strtoupper($_POST['applyPremToSalary']));
	Concat_Field("@@amt@", $_POST['premAmt']);
	Concat_Field("@@pct@", $_POST['premPct']);
	if (!isset($_POST['procOTDTMult'])) {$_POST['procOTDTMult']="N";}
	Concat_Field("@@otm@", $_POST['procOTDTMult']=strtoupper($_POST['procOTDTMult']));
	Concat_Field("@@tstp", $_POST['timeStamp']);

	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPRPRM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$Err_PPCOMP=DecatErr_Field("@@comp", "coNum");

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "Co: [$_POST[coNum]] Fac: [$_POST[facNum]] Dept: [$_POST[dept]] Shift: [$_POST[shiftWorked]] Description: [$_POST[premDesc]]", "", "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif (($maintenanceCode == "D" || $maintenanceCode == "C") && $errFound != "" && $Err_PPCOMP != "") {
		$Err_PPCOMP=DecatErr_Field("@@comp", "coNum");
		$Err_PPFACL=DecatErr_Field("@@facl", "facNum");
		$Err_PPDEPT=DecatErr_Field("@@dept", "dept");
		$Err_PPSHFT=DecatErr_Field("@@shft", "shiftWorked");
		$Err_PPDESC=DecatErr_Field("@@desc", "premDesc");
		$confMessage=Format_ConfMsg_Desc("E", "Co: [$_POST[coNum]] Fac: [$_POST[facNum]] Dept: [$_POST[dept]] Shift: [$_POST[shiftWorked]] Description: [$_POST[premDesc]] <br>$Err_PPCOMP", "", "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;coNum=" . urlencode(trim($_POST['coNum'])) . "&amp;facNum=" . urlencode(trim($_POST['facNum'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

