<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$fromCode           = $_GET['fromCode'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Benefit Providers Maintenance";
$scriptName     = "HRBenefitProvidersMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromCode=" . urlencode(trim($fromCode)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPECRU_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=78";}

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
	print "\n if (document.Chg.provName.value ==\"\" || ";
	print "\n     document.Chg.provAdd1.value ==\"\" || ";
	print "\n     document.Chg.provCity.value ==\"\" || ";
	print "\n     document.Chg.provState.value ==\"\" || ";
	print "\n     document.Chg.provZip.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.provPhone, 10, 0)) ";
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
	$pageID = "BENEFITPROVIDERSMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select CRCODE, CRNAME, CRADD1, CRADD2, CRCITY, CRSTAT, CRZIP, CRPHON, CRTSTP ";
		$stmtSQL .= " From CBCARR ";
		$stmtSQL .= " Where CRCODE='$fromCode' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hpecru_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hpecru_OPT['sec_01'];
	$sec_02=$hpecru_OPT['sec_02'];
	$sec_03=$hpecru_OPT['sec_03'];
	$sec_04=$hpecru_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "provCode";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_CRCODE=DecatErr_Field("@@code", "provCode");
			$Err_CRNAME=DecatErr_Field("@@name", "provName");
			$Err_CRADD1=DecatErr_Field("@@add1", "provAdd1");
			$Err_CRADD2=DecatErr_Field("@@add2", "provAdd2");
			$Err_CRCITY=DecatErr_Field("@@city", "provCity");
			$Err_CRSTAT=DecatErr_Field("@@stat", "provState");
			$Err_CRZIP =DecatErr_Field("@@zip@", "provZip");
			$Err_CRPHON=DecatErr_Field("@@phon", "provPhone");
			$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");
			$errFound= "";
		}

		$row['CRCODE']=Decat_Field("@@code", $edtVar);
		$row['CRNAME']=Decat_Field("@@name", $edtVar);
		$row['CRADD1']=Decat_Field("@@add1", $edtVar);
		$row['CRADD2']=Decat_Field("@@add2", $edtVar);
		$row['CRCITY']=Decat_Field("@@city", $edtVar);
		$row['CRSTAT']=Decat_Field("@@stat", $edtVar);
		$row['CRZIP'] =Decat_Field("@@zip@", $edtVar);
		$row['CRPHON']=Decat_Field("@@phon", $edtVar);
		$row['CRTSTP']=Decat_Field("@@tstp", $edtVar);

	}	elseif ($maintenanceCode=="Z") {
		$row['CRCODE']="";
		$focusField= "provCode";

	} else {
		$focusField= "provName";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_TSTP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>.</span></td>";
	print "\n     <td><input type=\"hidden\" name=\"timeStamp\" value=\"" . rtrim($row['CRTSTP']) . "\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_TSTP);
	
	$textOvr=SetTextOvr($Err_CRCODE);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Provider Code</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"provCode\" value=\"" . rtrim($row['CRCODE']) . "\" size=\"2\" maxlength=\"2\">$reqFieldChar</td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"provCode\" value=\"" . rtrim($row['CRCODE']) . "\">$row[CRCODE]</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_CRCODE);
	Build_Fld_Entry("Provider Name","provName","inputalph","","CRNAME",$row[CRNAME],$Err_CRNAME,"30","30","Y","","");
	Build_Fld_Entry("Address Line 1","provAdd1","inputalph","","CRADD1",$row[CRADD1],$Err_CRADD1,"30","30","Y","","");
	Build_Fld_Entry("Address Line 2","provAdd2","inputalph","","CRADD2",$row[CRADD2],$Err_CRADD2,"30","30","","","");
	Build_Fld_Entry("City","provCity","inputalph","","CRCITY",$row[CRCITY],$Err_CRCITY,"16","16","Y","","");
	$textOvr=SetTextOvr($Err_CRSTAT);
	$fieldDesc=RetValue("STID='$row[CRSTAT]'", "HDSTID", "STDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>State</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"provState\" value=\"" . rtrim($row['CRSTAT']) . "\" size=\"1\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=provState&amp;fldDesc=provStateDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"provStateDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CRSTAT);
	Build_Fld_Entry("Zip","provZip","inputalph","","CRZIP",$row[CRZIP],$Err_CRZIP,"10","10","Y","","");
	Build_Fld_Entry("Phone Number","provPhone","inputnmbr","","CRPHON",$row[CRPHON],$Err_CRPHON,"10","10","Y","","");
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
	if ($maintenanceCode=="D" && is_null($_POST['provCode'])) {
		$_POST['provCode']    =$fromCode;
		$_POST['provName']=RetValue("CRCODE='$fromCode'", "CBCARR", "CRNAME");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	$_POST['provCode']=strtoupper($_POST['provCode']);  Concat_Field("@@code", $_POST['provCode']);
	Concat_Field("@@name", $_POST['provName']);
	Concat_Field("@@add1", $_POST['provAdd1']);
	Concat_Field("@@add2", $_POST['provAdd2']);
	Concat_Field("@@city", $_POST['provCity']);
	$_POST['provState']=strtoupper($_POST['provState']);  Concat_Field("@@stat", $_POST['provState']);
	Concat_Field("@@zip@", $_POST['provZip']);
	Concat_Field("@@phon", $_POST['provPhone']);
	Concat_Field("@@tstp", $_POST['timeStamp']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPECRU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$Err_CRCODE=DecatErr_Field("@@code", "provCode");

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "$_POST[provName]", "$_POST[provCode]", "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif (($maintenanceCode == "D" || $maintenanceCode == "C") && $errFound != "" && $Err_CRCODE != "") {
		$confMessage=Format_ConfMsg_Desc("E", "$_POST[provName]", "$_POST[provCode]", "<br>$Err_CRCODE", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;provCode=" . urlencode(trim($_POST['provCode'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}
?>	
