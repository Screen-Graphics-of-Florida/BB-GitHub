<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound        = $_GET['errFound'];
$wrnVar          = $_GET['wrnVar'];

$prCompany       = $_GET['prCompany'];
$prFacility      = $_GET['prFacility'];
$prEmployee      = $_GET['prEmployee'];
$benefitCode     = $_GET['benefitCode'];
$planCode        = $_GET['planCode'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Employee FSA Maintenance";
$scriptName     = "EmployeeFSAMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;prCompany=" . urlencode(trim($prCompany)) . "&amp;prFacility=" . urlencode(trim($prFacility)) . "&amp;prEmployee=" . urlencode(trim($prEmployee)) . "&amp;benefitCode=" . urlencode(trim($benefitCode)) . "&amp;planCode=" . urlencode(trim($planCode)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPEFSM";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=95";}

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
	require_once 'CheckEnterChg.php';
	require_once 'CalendarInclude.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.fsaCovr.value ==\"\" || ";
	print "\n     document.Chg.fsaPlan.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.fsaComp, 2, 0) && ";
	print "\n     editNum(document.Chg.fsaFacl, 4, 0) && ";
	print "\n     editNum(document.Chg.fsaEmpl, 9, 0) && ";
	print "\n     editNum(document.Chg.annualContribs, 7, 2) && ";
	print "\n     editNum(document.Chg.ytdContribs, 7, 2) && ";
	print "\n     editNum(document.Chg.ytdPayments, 7, 2) && ";
	print "\n     editNum(document.Chg.priorYearContribs, 7, 2) && ";
	print "\n     editNum(document.Chg.priorYearPayments, 7, 2)) ";
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
	$pageID = "EMPLOYEEFSAMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From PEFSA ";
		$stmtSQL .= " Where FSCOMP=$prCompany and FSFACL=$prFacility and FSEMPL=$prEmployee and FSCOVR='$benefitCode' and FSPLAN='$planCode' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hpefsm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hpefsm_OPT['sec_01'];
	$sec_02=$hpefsm_OPT['sec_02'];
	$sec_03=$hpefsm_OPT['sec_03'];
	$sec_04=$hpefsm_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = i5_query($stmtSQL);
	$row = i5_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "fsaComp";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_FSCOMP=DecatErr_Field("@@comp", "fsaComp");
			$Err_FSFACL=DecatErr_Field("@@facl", "fsaFacl");
			$Err_FSEMPL=DecatErr_Field("@@empl", "fsaEmpl");
			$Err_FSCOVR=DecatErr_Field("@@covr", "fsaCovr");
			$Err_FSPLAN=DecatErr_Field("@@plan", "fsaPlan");
			$Err_FSANUL=DecatErr_Field("@@anul", "annualContribs");
			$Err_FSYTDC=DecatErr_Field("@@ytdc", "ytdContribs");
			$Err_FSYTDP=DecatErr_Field("@@ytdp", "ytdPayments");
			$Err_FSPYRC=DecatErr_Field("@@pyrc", "priorYearContribs");
			$Err_FSPYRP=DecatErr_Field("@@pyrp", "priorYearPayments");
			$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");
			$errFound= "";
		}
		$row['FSCOMP']=Decat_Field("@@comp", $edtVar);
		$row['FSFACL']=Decat_Field("@@facl", $edtVar);
		$row['FSEMPL']=Decat_Field("@@empl", $edtVar);
		$row['FSCOVR']=Decat_Field("@@covr", $edtVar);
		$row['FSPLAN']=Decat_Field("@@plan", $edtVar);
		$row['FSANUL']=Decat_Field("@@anul", $edtVar);
		$row['FSYTDC']=Decat_Field("@@ytdc", $edtVar);
		$row['FSYTDP']=Decat_Field("@@ytdp", $edtVar);
		$row['FSPYRC']=Decat_Field("@@pyrc", $edtVar);
		$row['FSPYRP']=Decat_Field("@@pyrp", $edtVar);
		$row['FSTSTP']=Decat_Field("@@tstp", $edtVar);

	} elseif ($maintenanceCode=="Z") {
		$focusField= "fsaComp";
	} else {
		$focusField= "annualContribs";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	
	$textOvr=SetTextOvr($Err_TSTP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>.</span></td>";
	print "\n     <td><input type=\"hidden\" name=\"timeStamp\" value=\"" . rtrim($row['FSTSTP']) . "\"></td></tr>";
	DspErrMsg($Err_TSTP);
	if (is_null($row['FSCOMP']) || trim($row['FSCOMP'])=="") {$row['FSCOMP']=0;}
	if (is_null($row['FSFACL']) || trim($row['FSFACL'])=="")  {$row['FSFACL']=0;}
	$coFacDesc=RetValue("CFCOMP=$row[FSCOMP] and CFFACL=$row[FSFACL]", "HRCOFC", "CFNAME");
	$textOvr=SetTextOvr($Err_FSCOMP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company/Facility Number</span></td> ";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"fsaComp\" value=\"" . rtrim($row['FSCOMP']) . "\" size=\"1\" maxlength=\"2\"> / <input type=\"text\"   name=\"fsaFacl\" value=\"" . rtrim($row['FSFACL']) . "\" size=\"1\" maxlength=\"4\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeBenefitsSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=fsaComp&amp;fldFacl=fsaFacl&amp;fldCoName=coNumDesc&amp;fldEmpl=fsaEmpl&amp;fldEmplName=emplDesc&amp;fldCovr=fsaCovr&amp;fldCovrDesc=covrDesc&amp;fldPlan=fsaPlan&amp;fldPlanDesc=planDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"coNumDesc\">$coFacDesc</span></td>";
	} else {
		print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"fsaComp\" value=\"" . rtrim($row['FSCOMP']) . "\">$row[FSCOMP] / <input type=\"hidden\"   name=\"fsaFacl\" value=\"" . rtrim($row['FSFACL']) . "\">$row[FSFACL]";
		print "\n     <span class=\"dspdesc\">$coFacDesc</span></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_FSCOMP);

	$lastName=RetValue("EMCOMP=$row[FSCOMP] and EMFACL=$row[FSFACL] and EMEMPL=$row[FSEMPL]", "HREMPL", "EMLNAM ");
	$firstName=RetValue("EMCOMP=$row[FSCOMP] and EMFACL=$row[FSFACL] and EMEMPL=$row[FSEMPL]", "HREMPL", "EMFNAM ");
	$middleIni=RetValue("EMCOMP=$row[FSCOMP] and EMFACL=$row[FSFACL] and EMEMPL=$row[FSEMPL]", "HREMPL", "EMMIDI ");
	$name=Format_EmplName(trim($firstName),trim($lastName),"$middleIni","","","");
	$F_Name=Format_Quote($name);
	$textOvr=SetTextOvr($Err_FSEMPL);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Employee Number</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fsaEmpl\" value=\"" . rtrim($row['FSEMPL']) . "\" size=\"9\" maxlength=\"9\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeBenefitsSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=fsaComp&amp;fldFacl=fsaFacl&amp;fldCoName=coNumDesc&amp;fldEmpl=fsaEmpl&amp;fldEmplName=emplDesc&amp;fldCovr=fsaCovr&amp;fldCovrDesc=covrDesc&amp;fldPlan=fsaPlan&amp;fldPlanDesc=planDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"emplDesc\">$F_Name</span></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"fsaEmpl\" value=\"" . rtrim($row['FSEMPL']) . "\">$row[FSEMPL]";
		print "\n     <span class=\"dspdesc\">$F_Name</span></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_FSEMPL);

	$covrDesc=RetValue("CVCOMP=$row[FSCOMP] and CVFACL=$row[FSFACL] and CVCOVR='$row[FSCOVR]'", "CBCOVR", "CVDESC ");
	$textOvr=SetTextOvr($Err_FSCOVR);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Benefit Code</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fsaCovr\" value=\"" . rtrim($row['FSCOVR']) . "\" size=\"3\" maxlength=\"5\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeBenefitsSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=fsaComp&amp;fldFacl=fsaFacl&amp;fldCoName=coNumDesc&amp;fldEmpl=fsaEmpl&amp;fldEmplName=emplDesc&amp;fldCovr=fsaCovr&amp;fldCovrDesc=covrDesc&amp;fldPlan=fsaPlan&amp;fldPlanDesc=planDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"covrDesc\">$covrDesc</span></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"fsaCovr\" value=\"" . rtrim($row['FSCOVR']) . "\">$row[FSCOVR]";
		print "\n     <span class=\"dspdesc\">$covrDesc</span></td>";
	}
	print "\n </tr> ";DspErrMsg($Err_FSCOVR);

	$planDesc=RetValue("CCTYPE='P' and CCCODE='$row[FSPLAN]'", "CBCODE", "CCDESC ");
	$textOvr=SetTextOvr($Err_FSPLAN);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Plan Code</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fsaPlan\" value=\"" . rtrim($row['FSPLAN']) . "\" size=\"3\" maxlength=\"5\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeBenefitsSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=fsaComp&amp;fldFacl=fsaFacl&amp;fldCoName=coNumDesc&amp;fldEmpl=fsaEmpl&amp;fldEmplName=emplDesc&amp;fldCovr=fsaCovr&amp;fldCovrDesc=covrDesc&amp;fldPlan=fsaPlan&amp;fldPlanDesc=planDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"planDesc\">$planDesc</span></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"fsaPlan\" value=\"" . rtrim($row['FSPLAN']) . "\">$row[FSPLAN]";
		print "\n     <span class=\"dspdesc\">$planDesc</span></td>";
	}
	print "\n </tr> ";DspErrMsg($Err_FSPLAN);

	$textOvr=SetTextOvr($Err_FSANUL);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Annual Contribution</span></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"annualContribs\" value=\"" . rtrim($row['FSANUL']) . "\"  size=\"10\" maxlength=\"10\">$reqFieldChar</td></tr>";
	DspErrMsg($Err_FSANUL);

	$textOvr=SetTextOvr($Err_FSYTDC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Year To Date Contributions</span></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"ytdContribs\" value=\"" . rtrim($row['FSYTDC']) . "\"  size=\"10\" maxlength=\"10\"></td></tr>";
	DspErrMsg($Err_FSYTDC);

	$textOvr=SetTextOvr($Err_FSYTDP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Year To Date Payments</span></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"ytdPayments\" value=\"" . rtrim($row['FSYTDP']) . "\"  size=\"10\" maxlength=\"10\"></td></tr>";
	DspErrMsg($Err_FSYTDP);

	$textOvr=SetTextOvr($Err_FSPYRC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Prior Year Contributions</span></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"priorYearContribs\" value=\"" . rtrim($row['FSPYRC']) . "\"  size=\"10\" maxlength=\"10\"></td></tr>";
	DspErrMsg($Err_FSPYRC);

	$textOvr=SetTextOvr($Err_FSPYRP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Prior Year Payments</span></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"priorYearPayments\" value=\"" . rtrim($row['FSPYRP']) . "\"  size=\"10\" maxlength=\"10\"></td></tr>";
	DspErrMsg($Err_FSPYRP);
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
	if ($maintenanceCode=="D" && is_null($_POST['fsaComp'])) {
		$_POST['fsaComp']    =$prCompany;
		$_POST['fsaFacl']    =$prFacility;
		$_POST['fsaEmpl']    =$prEmployee;
		$_POST['fsaCovr']    =$benefitCode;
		$_POST['fsaPlan']    =$planCode;
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@comp", $_POST['fsaComp']);
	Concat_Field("@@facl", $_POST['fsaFacl']);
	Concat_Field("@@empl", $_POST['fsaEmpl']);
	$_POST['fsaCovr']=strtoupper($_POST['fsaCovr']);  Concat_Field("@@covr", $_POST['fsaCovr']);
	$_POST['fsaPlan']=strtoupper($_POST['fsaPlan']);  Concat_Field("@@plan", $_POST['fsaPlan']);
	Concat_Field("@@anul", $_POST['annualContribs']);
	Concat_Field("@@ytdc", $_POST['ytdContribs']);
	Concat_Field("@@ytdp", $_POST['ytdPayments']);
	Concat_Field("@@pyrc", $_POST['priorYearContribs']);
	Concat_Field("@@pyrp", $_POST['priorYearPayments']);
	Concat_Field("@@tstp", $_POST['timeStamp']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPEFSM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, " Co/Fac: [$_POST[fsaComp] / $_POST[fsaFacl]] Empl: [$_POST[fsaEmpl]] Covr: [$_POST[fsaCovr]] Plan: [$_POST[fsaPlan]]", "", "", "", "", "");
		} else {
			$Err_FSCOMP=DecatErr_Field("@@comp", "fsaComp");
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, " Co/Fac: [$_POST[fsaComp] / $_POST[fsaFacl]] Empl: [$_POST[fsaEmpl]] Covr: [$_POST[fsaCovr]] Plan: [$_POST[fsaPlan]]", "", "", "", "", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;fsaComp=" . urlencode(trim($_POST['fsaComp'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}
?>	
