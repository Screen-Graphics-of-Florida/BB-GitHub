<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromCo             = $_GET['fromCo'];
$fromFac            = $_GET['fromFac'];
$fromDed            = $_GET['fromDed'];


require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "H/R Company/Facility Deduction Maintenance";
$scriptName     = "HRCoFacDedMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;fromCo=" . urlencode(trim($fromCo)) . "&amp;fromFac=" . urlencode(trim($fromFac)) . "&amp;fromDed=" . urlencode(trim($fromDed));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HHRCDM_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}HRCoFacDed.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . " \"> ";}

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
	print "\n if (editNum(document.Chg.coNum, 2, 0) && ";
	print "\n     editNum(document.Chg.facNum, 4, 0) && ";
	print "\n     editNum(document.Chg.dedNum, 3, 0) && ";
	print "\n     editNum(document.Chg.dedCyc, 1, 0) && ";
	print "\n     editNum(document.Chg.dedAcct, 4, 0) && ";
	print "\n     editNum(document.Chg.dedSub, 4, 0)) ";
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
	$pageID = "HRCOFACDEDMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From HRCDED ";
		$stmtSQL .= " Where CDCOMP=$fromCo and CDFACL=$fromFac and CDDDNO=$fromDed ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hhrcdm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hhrcdm_OPT['sec_01'];
	$sec_02=$hhrcdm_OPT['sec_02'];
	$sec_03=$hhrcdm_OPT['sec_03'];
	$sec_04=$hhrcdm_OPT['sec_04'];
	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "dedNum";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_CDCOMP=DecatErr_Field("@@comp", "coNum");
			$Err_CDFACL=DecatErr_Field("@@facl", "facNum");
			$Err_CDDDNO=DecatErr_Field("@@ddno", "dedNum");
			$Err_CDDCYC=DecatErr_Field("@@dcyc", "dedCyc");
			$Err_CDGLAC=DecatErr_Field("@@glac", "dedAcct");
			$Err_CDGLSB=DecatErr_Field("@@glsb", "dedSub");
			$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");
			$errFound= "";
		}

		$row['CDCOMP']=Decat_Field("@@comp", $edtVar);
		$row['CDFACL']=Decat_Field("@@facl", $edtVar);
		$row['CDDDNO']=Decat_Field("@@ddno", $edtVar);
		$row['CDDCYC']=Decat_Field("@@dcyc", $edtVar);
		$row['CDGLAC']=Decat_Field("@@glac", $edtVar);
		$row['CDGLSB']=Decat_Field("@@glsb", $edtVar);
		$row['CDTSTP']=Decat_Field("@@tstp", $edtVar);


	}	elseif ($maintenanceCode=="Z") {
		$focusField= "dedNum";

	} else {
		$focusField= "dedCyc";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_TSTP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>.</span></td>";
	print "\n     <td><input type=\"hidden\" name=\"timeStamp\" value=\"" . rtrim($row['CDTSTP']) . "\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_TSTP);
	
	if (is_null($row['CDCOMP']) || trim($row['CDCOMP'])=="") {$row['CDCOMP']=0;}
	if (is_null($row['CDFACL']) || trim($row['CDFACL'])=="")  {$row['CDFACL']=0;}
	$textOvr=SetTextOvr($Err_CDCOMP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company/Facility</span></td> ";
	if ($maintenanceCode=="A") {
		$coFacDesc=RetValue("CFCOMP=$fromCo and CFFACL=$fromFac", "HRCOFC", "CFNAME");
		print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"coNum\" value=\"" . rtrim($fromCo) . "\">$fromCo / <input type=\"hidden\"   name=\"facNum\" value=\"" . rtrim($fromFac) . "\">$fromFac";
	} else {
		$coFacDesc=RetValue("CFCOMP=$row[CDCOMP] and CFFACL=$row[CDFACL]", "HRCOFC", "CFNAME");
		print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"coNum\" value=\"" . rtrim($row['CDCOMP']) . "\">$row[CDCOMP] / <input type=\"hidden\"   name=\"facNum\" value=\"" . rtrim($row['CDFACL']) . "\">$row[CDFACL]";
	}

	print "\n     <span class=\"dspdesc\">$coFacDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CDCOMP);

	$textOvr=SetTextOvr($Err_CDDDNO);
	$fieldDesc=RetValue("HVDDNO=$row[CDDDNO]", "HRDEDM", "HVDDNM");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Deduction Number</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"dedNum\" value=\"" . rtrim($row['CDDDNO']) . "\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}VoluntaryDeductionSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=dedNum&amp;fldDesc=dedNumDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"dedNumDesc\">$fieldDesc</span></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"dedNum\" value=\"" . rtrim($row['CDDDNO']) . "\">$row[CDDDNO]";
		print "\n     <span class=\"dspdesc\">$fieldDesc</span></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_CDDDNO);

	$textOvr=SetTextOvr($Err_CDDCYC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Cycle</span></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"dedCyc\" value=\"" . rtrim($row['CDDCYC']) . "\"  size=\"1\" maxlength=\"1\"></td></tr>";
	DspErrMsg($Err_CDDCYC);

	if (is_null($row['CDGLAC']) || trim($row['CDGLAC'])=="") {$row['CDGLAC']=0;}
	if (is_null($row['CDGLSB']) || trim($row['CDGLSB'])=="")  {$row['CDGLSB']=0;}
	$fieldDesc=RetValue("CHACCT=$row[CDGLAC] and CHSUB=$row[CDGLSB]", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CDGLAC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>G/L Account Number</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"dedAcct\" value=\"" . rtrim($row['CDGLAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"dedSub\" value=\"" . rtrim($row['CDGLSB']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=dedAcct&amp;subFld=dedSub&amp;descFld=dedAcctDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"dedAcctDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CDGLAC);
	print "\n </table>";
	print "\n </form>";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.$focusField.focus();";
	print "\n </script>";
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
		$_POST['dedNum']       =$fromDed;
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";

	Concat_Field("@@comp", $_POST['coNum']);
	Concat_Field("@@facl", $_POST['facNum']);
	Concat_Field("@@ddno", $_POST['dedNum']);
	Concat_Field("@@dcyc", $_POST['dedCyc']);
	Concat_Field("@@glac", $_POST['dedAcct']);
	Concat_Field("@@glsb", $_POST['dedSub']);
	Concat_Field("@@tstp", $_POST['timeStamp']);

	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HHRCDM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];
	$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");

	
	if ($errFound == "") {
		$fieldDesc=RetValue("HVDDNO='$_POST[dedNum]'", "HRDEDM", "HVDDNM");
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "Deduction $fieldDesc", "$_POST[dedNum]", "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif (($maintenanceCode == "D" || $maintenanceCode == "C") && $errFound != ""&& $Err_TSTP != "") {
		$fieldDesc=RetValue("HVDDNO='$_POST[dedNum]'", "HRDEDM", "HVDDNM");
		$confMessage=Format_ConfMsg_Desc("E","Deduction $fieldDesc", "$_POST[dedNum]", "<br>$Err_TSTP", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;coNum=" . urlencode(trim($_POST['coNum'])) . "&amp;facNum=" . urlencode(trim($_POST['facNum'])) . "&amp;dedNum=" . urlencode(trim($_POST['dedNum'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>