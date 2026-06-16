<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromScript         = $_GET['fromScript'];
$fromReverseReason  = $_GET['fromReverseReason'];
$reverseReason      = $_GET['reverseReason'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Reversal Reason Maintenance";
$scriptName     = "ARReverseReasonMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromReverseReason=" . urlencode(trim($fromReverseReason)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HARRSM_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=6";}

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
	print "\n   if (document.Chg.reverseReason.value ==\"\" ";
	print "\n    || document.Chg.reasonDesc.value ==\"\" ";
	print "\n   ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n   if (editNum(document.Chg.rating, 2, 0) ";
	print "\n   ) return true;";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARREVERSEREASONMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select RVRSCD,RVDESC,RVRATE,RVTSTP ";
		$stmtSQL .= " From ARRVRS ";
		$stmtSQL .= " Where RVRSCD='$fromReverseReason' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$harrsm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harrsm_OPT['sec_01'];
	$sec_02=$harrsm_OPT['sec_02'];
	$sec_03=$harrsm_OPT['sec_03'];
	$sec_04=$harrsm_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "reverseReason";
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_RVRSCD=DecatErr_Field("@@rscd", "reverseReason");
			$Err_RVDESC=DecatErr_Field("@@desc", "reasonDesc");
			$Err_RVRATE=DecatErr_Field("@@rate", "rating");
			$errFound= "";
		}

		$row['RVRSCD']=Decat_Field("@@rscd", $edtVar);
		$row['RVDESC']=Decat_Field("@@desc", $edtVar);
		$row['RVRATE']=Decat_Field("@@rate", $edtVar);
		$row['RVTSTP']=Decat_Field("@@tstp", $edtVar);

	} else {
		$focusField= "reasonDesc";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['RVTSTP']) . "\"></td></tr> ";
	$textOvr=SetTextOvr($Err_RVRSCD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Reason</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"reverseReason\" value=\"" . rtrim($row['RVRSCD']) . "\" size=\"4\" maxlength=\"4\"> $reqFieldChar</td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"reverseReason\" value=\"" . rtrim($row['RVRSCD']) . "\">$row[RVRSCD]</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_RVRSCD);

	Build_Fld_Entry("Description","reasonDesc","inputalph","","RVDESC",$row[RVDESC],$Err_RVDESC,"30","30","Y","","");
	Build_Fld_Entry("Rating","rating","inputnmbr","","RVRATE",$row[RVRATE],$Err_RVRATE,"2","2","","","");

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
	if ($maintenanceCode=="D" && is_null($_POST['reverseReason'])) {
		$_POST['reverseReason']=$fromReverseReason;
		$_POST['reasonDesc']=RetValue("RVRSCD='$_POST[reverseReason]'", "ARRVRS", "RVDESC");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@frm1", $fromReverseReason);
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	$_POST['reverseReason']=strtoupper($_POST['reverseReason']);  Concat_Field("@@rscd", $_POST['reverseReason']);
	Concat_Field("@@desc", $_POST['reasonDesc']);
	Concat_Field("@@rate", $_POST['rating']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARRSM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST[reasonDesc], $_POST[reverseReason], "", "", "", "");
		} else {
			$Err_RVRSCD=DecatErr_Field("@@rscd", "reverseReason");
			$confMessage=Format_ConfMsg_Desc("", $_POST[reasonDesc], $_POST[reverseReason], "", "", "<br>$Err_RVRSCD", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;reverseReason=" . urlencode(trim($_POST['reverseReason'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}
?>
