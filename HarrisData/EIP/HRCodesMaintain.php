<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$copyAll         = $_GET['copyAll'];
$errFound        = $_GET['errFound'];
$fromComp        = $_GET['fromComp'];
$fromType        = $_GET['fromType'];
$fromCode        = $_GET['fromCode'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "H/R Codes Maintenance";
$scriptName     = "HRCodesMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;fromComp=" . urlencode(trim($fromComp)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromCode=" . urlencode(trim($fromCode)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;copyAll=N&amp;maintenanceCode=D";
$programName    = "HPEPCU_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=22";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "COPYALL") {
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
	print "\n if (editNum(document.Chg.comp, 2, 0) && ";
	print "\n     editNum(document.Chg.toco, 2, 0)) ";
	print "\n return true;";
	print "\n }";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "HRCODESMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	$maintenanceCode == "Z";
	$copyAll == "Y";

	// Program Option Security
	$harsrm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harsrm_OPT['sec_01'];
	$sec_02=$harsrm_OPT['sec_02'];
	$sec_03=$harsrm_OPT['sec_03'];
	$sec_04=$harsrm_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	if ($errFound != "") {
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$Err_ODCOMP=DecatErr_Field("@@comp", "comp");
		$Err_ODTYPE=DecatErr_Field("@@type", "type");
		$Err_ODTOCO=DecatErr_Field("@@toco", "toco");
		$Err_ODCPYA=DecatErr_Field("@@cpya", "cpya");
		$errFound= "";

		$fromComp=Decat_Field("@@comp", $edtVar);
		$fromType=Decat_Field("@@type", $edtVar);
		$row['ODTOCO']=Decat_Field("@@toco", $edtVar);
		$row['ODCPYA']=Decat_Field("@@cpya", $edtVar);
	}  elseif ($errFound = "") {
		$focusField= "comp";
	}

	$focusField= "comp";

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;copyAll=Y&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$row['ODCOMP'] = $fromComp;
	$textOvr=SetTextOvr($Err_ODCOMP);
	$fieldDesc=RetValue("CFCOMP='$row[ODCOMP]'", "HRCOFC", "CFNAME");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>From Company</span></td>";
	print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"comp\" value=\"" . rtrim($row['ODCOMP']) . "\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCompanySearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=comp&amp;fldDesc=compDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"compDesc\">$fldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_ODCOMP);

	$row['ODTYPE'] = $fromType;
	$textOvr=SetTextOvr($Err_ODTYPE);
	$fieldDesc=RetValue("FLTYPE='HRCODETYPE' and FLVALU='$row[ODTYPE]'", "SYFLAG", "FLDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>From Type Of Code</span></td>";
	print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"type\" value=\"" . rtrim($row['ODTYPE']) . "\" size=\"2\" maxlength=\"1\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=HRCODETYPE&amp;flagSrchHdr=". urlencode("Code Type") . "&amp;fldName=type&amp;fldDesc=typeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"typeDesc\">$fieldDesc</span></td>";
	print "\n <td class=\"dsphdr\">&nbsp; (Leave Blank To Copy All)</td>";
	print "\n </tr> ";
	DspErrMsg($Err_ODTYPE);

	$textOvr=SetTextOvr($Err_ODTOCO);
	$fieldDesc=RetValue("CFCOMP='$ODTOCO'", "HRCOFC", "CFNAME");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>To Company</span></td>";
	print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"toco\" value=\"" . rtrim($row['ODTOCO']) . "\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCompanySearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=toco&amp;fldDesc=tocoDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"tocoDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_ODTOCO);

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
	print "\n if (document.Chg.desc.value ==\"\" ";
	if ($fromType == "J") {
		print "\n || document.Chg.eeoCat.value ==\"\" ";
	}
	print "\n )";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.comp, 2, 0) ";
	if ($fromType == "4") {
		print "\n  && editNum(document.Chg.lowSal, 7, 0) ";
		print "\n  && editNum(document.Chg.midSal, 7, 0) ";
		print "\n  && editNum(document.Chg.highSal, 7, 0) ";
	}
	print "\n ) return true;";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "HRCODESMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From PECODE ";
		$stmtSQL .= " Where ODCOMP='$fromComp' and ODTYPE='$fromType' and ODCODE='$fromCode' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$harsrm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harsrm_OPT['sec_01'];
	$sec_02=$harsrm_OPT['sec_02'];
	$sec_03=$harsrm_OPT['sec_03'];
	$sec_04=$harsrm_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print "\n <table $contentTable> ";
	$typeDesc=RetValue("FLTYPE='HRCODETYPE' and FLVALU='$fromType' ", "SYFLAG", "FLDESC");
	Format_Header("Type Of Code", $typeDesc, $fromType);
	print "\n </table> ";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "comp";
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_ODCOMP=DecatErr_Field("@@comp", "comp");
			$Err_ODTYPE=DecatErr_Field("@@type", "type");
			$Err_ODCODE=DecatErr_Field("@@code", "code");
			$Err_ODDESC=DecatErr_Field("@@desc", "desc");
			$Err_ODTOCO=DecatErr_Field("@@toco", "toco");
			$Err_ODCPYA=DecatErr_Field("@@cpya", "cpya");
			$Err_ODPGRD=DecatErr_Field("@@pgrd", "pgrd");
			$Err_ODJEEO=DecatErr_Field("@@jeeo", "jeeo");
			$Err_ODLOWR=DecatErr_Field("@@lowr", "lowr");
			$Err_ODMIDR=DecatErr_Field("@@midr", "midr");
			$Err_ODHIGR=DecatErr_Field("@@higr", "higr");
			$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");
			$errFound= "";
		}
		$row['ODCOMP']=Decat_Field("@@comp", $edtVar);
		$row['ODTYPE']=Decat_Field("@@type", $edtVar);
		$row['ODCODE']=Decat_Field("@@code", $edtVar);
		$row['ODDESC']=Decat_Field("@@desc", $edtVar);
		$row['ODTOCO']=Decat_Field("@@toco", $edtVar);
		$row['ODCPYA']=Decat_Field("@@cpya", $edtVar);
		$row['ODPGRD']=Decat_Field("@@pgrd", $edtVar);
		$row['ODJEEO']=Decat_Field("@@jeeo", $edtVar);
		$row['ODLOWR']=Decat_Field("@@lowr", $edtVar);
		$row['ODMIDR']=Decat_Field("@@midr", $edtVar);
		$row['ODHIGR']=Decat_Field("@@higr", $edtVar);
		$row['ODTSTP']=Decat_Field("@@tstp", $edtVar);
		
	} elseif ($maintenanceCode=="Z") {
		$focusField= "code";
	} else {
		$focusField= "desc";
	}

	$row[ODCPYA]=$copyAll;

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;copyAll=N&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_TSTP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>.</span></td>";
	print "\n     <td><input type=\"hidden\" name=\"timeStamp\" value=\"" . rtrim($row['ODTSTP']) . "\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_TSTP);
	
	$row['ODTOCO'] = $fromComp;
	$textOvr=SetTextOvr($Err_ODCOMP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company Number</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		if ($row['ODCOMP'] == 0) {$row['ODCOMP'] = $fromComp;}
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"comp\" value=\"" . rtrim($row['ODCOMP']) . "\" size=\"2\" maxlength=\"2\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}HRCompanySearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=comp&amp;fldDesc=compDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"compDesc\">$fldDesc</span></td>";
	} else {
		$fieldDesc=RetValue("CFCOMP=$fromComp and CFFACL=0", "HRCOFC", "CFNAME");
		$F_fromComp=Format_Code($fromComp);
		print "\n <td class=\"dspnmbr\"><input type=\"hidden\" name=\"comp\" value=\"" . rtrim($fromComp) . "\">$fieldDesc $F_fromComp</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_ODCOMP);


	$row['ODFRCD'] = $fromCode;
	$textOvr=SetTextOvr($Err_ODCODE);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Code</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"code\" value=\"" . rtrim($row['ODCODE']) . "\" size=\"2\" maxlength=\"4\"></td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"code\" value=\"" . rtrim($row['ODCODE']) . "\">$row[ODCODE]</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_ODCODE);

	Build_Fld_Entry("Description","desc","inputalph","","ODDESC",$row[ODDESC],$Err_ODDESC,"25","20","Y","","");

	if ($fromType == "J") {
		$textOvr=SetTextOvr($Err_ODJEEO);
		$fieldDesc=RetValue("ODTYPE='Y' and ODCODE='$row[ODJEEO]'", "PECODE", "ODDESC");
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>EEO-1 Job Category</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"eeoCat\" value=\"" . rtrim($row['ODJEEO']) . "\" size=\"3\" maxlength=\"6\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=eeoCat&amp;fldType=Y&amp;fldDesc=eeoCatDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"eeoCatDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_ODJEEO);

		$textOvr=SetTextOvr($Err_ODPGRD);
		$fieldDesc=RetValue("ODTYPE='4' and ODCODE='$row[ODPGRD]'", "PECODE", "ODDESC");
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Pay Grade</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"payGrade\" value=\"" . rtrim($row['ODPGRD']) . "\" size=\"3\" maxlength=\"6\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=payGrade&amp;fldType=4&amp;fldDesc=payGradeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"payGradeDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_ODPGRD);
	} elseif ($fromType == "4") {
		Build_Fld_Entry("Low Salary Range","lowSal","inputnmbr","","ODLOWR",$row[ODLOWR],$Err_ODLOWR,"7","7","","","");
		Build_Fld_Entry("Mid Salary Range","midSal","inputnmbr","","ODMIDR",$row[ODMIDR],$Err_ODMIDR,"7","7","","","");
		Build_Fld_Entry("High Salary Range","highSal","inputnmbr","","ODHIGR",$row[ODHIGR],$Err_ODHIGR,"7","7","","","");
	}

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
	if ($maintenanceCode=="D" && is_null($_POST['code'])) {
		$_POST['comp']    =$fromComp;
		$_POST['type']    =$fromType;
		$_POST['code']    =$fromCode;
		$_POST['desc']=RetValue("ODCOMP='$fromComp' and ODTYPE='$fromType' and ODCODE='$fromCode'", "PECODE", "ODDESC");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@comp", $_POST['comp']);
	if ($fromType == "") {
		$_POST['type']=strtoupper($_POST['type']);  Concat_Field("@@type", $_POST['type']);
	}  else {
		Concat_Field("@@type", $fromType);
	}
	$_POST['code']=strtoupper($_POST['code']);  Concat_Field("@@code", $_POST['code']);
	Concat_Field("@@desc", $_POST['desc']);
	Concat_Field("@@jeeo", $_POST['eeoCat']);
	Concat_Field("@@pgrd", $_POST['payGrade']);
	Concat_Field("@@lowr", $_POST['lowSal']);
	Concat_Field("@@midr", $_POST['midSal']);
	Concat_Field("@@higr", $_POST['highSal']);
	Concat_Field("@@toco", $_POST['toco']);
	Concat_Field("@@cpya", $copyAll);
	Concat_Field("@@tstp", $_POST['timeStamp']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPEPCU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];
	$Err_ODCODE=DecatErr_Field("@@code", "code");

	if ($errFound == "") {

		if ($copyAll == "Y") {
			if ($_POST['type'] == "") {$desc="All";}
			else {$desc=RetValue("FLTYPE='HRCODETYPE' and FLVALU='$_POST[type]' ", "SYFLAG", "FLDESC");}
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, "Company", "$_POST[toco]", "Type", "$fromType", "$desc", "$_POST[type]");
		}
		else {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, "Company", "$_POST[comp]", "Type", "$fromType", "$_POST[desc]", "$_POST[code]");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif (($maintenanceCode == "D" || $maintenanceCode == "C") && $errFound != "" && $Err_ODCODE != "") {
		$Err_ODCOMP=DecatErr_Field("@@comp", "comp");
		$Err_ODTYPE=DecatErr_Field("@@type", "type");
		$Err_ODCODE=DecatErr_Field("@@code", "code");
		$Err_ODDESC=DecatErr_Field("@@desc", "desc");
		$confMessage=Format_ConfMsg_Desc("E", "Company", "$_POST[comp]", "Type", "$fromType", "<br>$Err_ODCODE", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		if ($copyAll == "Y") {$tagVar="COPYALL";} else {$tagVar="MAINTAIN";}
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag={$tagVar}&amp;copyAll=" . urlencode(trim($$copyAll)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>