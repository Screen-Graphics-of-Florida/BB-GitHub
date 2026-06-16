<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$fromDepSrcCode     = $_GET['fromDepSrcCode'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Deposit Source Code Maintenance";
$scriptName     = "DepositSourceMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromDepSrcCode=" . urlencode(trim($fromDepSrcCode)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HARSRM_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=17";}

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

	print "\n function validate(chgForm) {";
	print "\n   if (document.Chg.depSrcCodeDesc.value ==\"\"";
	print "\n   ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n   return true;";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "DEPOSITSOURCEMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select BSSRCC, BSDESC ";
		$stmtSQL .= " From ARDSRC ";
		$stmtSQL .= " Where BSSRCC='$fromDepSrcCode' ";
	}
	require 'stmtSQLEnd.php';

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

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "depSrcCode";
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_BSSRCC=DecatErr_Field("@@srcc", "depSrcCode");
			$Err_BSDESC=DecatErr_Field("@@desc", "depSrcCodeDesc");
			$errFound= "";
		}
		$row['BSSRCC']=Decat_Field("@@srcc", $edtVar);
		$row['BSDESC']=Decat_Field("@@desc", $edtVar);

	} elseif ($maintenanceCode=="Z") {
		$focusField= "depSrcCode";
	} else {
		$focusField= "depSrcCodeDesc";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_BSSRCC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Deposit Source Code</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"depSrcCode\" value=\"" . rtrim($row['BSSRCC']) . "\" size=\"2\" maxlength=\"2\"></td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"depSrcCode\" value=\"" . rtrim($row['BSSRCC']) . "\">$row[BSSRCC]</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_BSSRCC);

	Build_Fld_Entry("Description","depSrcCodeDesc","inputalph","","BSDESC",$row[BSDESC],$Err_BSDESC,"15","15","Y","","");
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
	if ($maintenanceCode=="D" && is_null($_POST['depSrcCode'])) {
		$_POST['depSrcCode']    =$fromDepSrcCode;
		$_POST['depSrcCodeDesc']=RetValue("BSSRCC='$fromDepSrcCode'", "ARDSRC", "BSDESC");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	$_POST['depSrcCode']=strtoupper($_POST['depSrcCode']);  Concat_Field("@@srcc", $_POST['depSrcCode']);
	Concat_Field("@@desc", $_POST['depSrcCodeDesc']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARSRM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST['depSrcCodeDesc'], $_POST['depSrcCode'], "", "", "", "");
		} else {
			$Err_BSSRCC=DecatErr_Field("@@srcc", "depSrcCode");
			$confMessage=Format_ConfMsg_Desc("", $_POST['depSrcCodeDesc'], $_POST['depSrcCode'], "<br>$Err_BSSRCC", "", "", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;depSrcCode=" . urlencode(trim($_POST['depSrcCode'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>