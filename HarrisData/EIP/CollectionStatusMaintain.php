<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$fromCollStsCode    = $_GET['fromCollStsCode'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Collection Status Maintenance";
$scriptName     = "CollectionStatusMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromCollStsCode=" . urlencode(trim($fromCollStsCode)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HARCSM_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=18";}

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
	print "\n   if (document.Chg.collStsCode.value ==\"\" ";
	print "\n    || document.Chg.collStsDesc.value ==\"\" ";
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
	$pageID = "COLLECTIONSTATUSMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select CSSTCD, CSDESC, CSALPH ";
		$stmtSQL .= " From ARCSTM ";
		$stmtSQL .= " Where CSSTCD='$fromCollStsCode' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$harcsm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harcsm_OPT['sec_01'];
	$sec_02=$harcsm_OPT['sec_02'];
	$sec_03=$harcsm_OPT['sec_03'];
	$sec_04=$harcsm_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "collStsCode";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_CSSTCD=DecatErr_Field("@@stcd", "collStsCode");
			$Err_CSDESC=DecatErr_Field("@@desc", "collStsDesc");
			$Err_CSALPH=DecatErr_Field("@@alph", "collStsAlphaSeq");
			$errFound= "";
		}

		$row['CSSTCD']=Decat_Field("@@stcd", $edtVar);
		$row['CSALPH']=Decat_Field("@@alph", $edtVar);
		$row['CSDESC']=Decat_Field("@@desc", $edtVar);

	}	elseif ($maintenanceCode=="Z") {
		$focusField= "collStsCode";

	} else {
		$focusField= "collStsDesc";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_CSSTCD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Collection Status</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"collStsCode\" value=\"" . rtrim($row['CSSTCD']) . "\" size=\"2\" maxlength=\"2\"> $reqFieldChar</td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"collStsCode\" value=\"" . rtrim($row['CSSTCD']) . "\">$row[CSSTCD]</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_CSSTCD);

	Build_Fld_Entry("Description","collStsDesc","inputalph","","CSDESC",$row[CSDESC],$Err_CSDESC,"30","30","Y","","");
	Build_Fld_Entry("Alpha Sequence","collStsAlphaSeq","inputalph","","CSALPH",$row[CSALPH],$Err_CSALPH,"4","4","","","");
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
	if ($maintenanceCode=="D" && is_null($_POST['collStsCode'])) {
		$_POST['collStsCode']    =$fromCollStsCode;
		$_POST['collStsDesc']=RetValue("CSSTCD='$fromCollStsCode'", "ARCSTM", "CSDESC");
		$_POST['collStsAlphaSeq']=RetValue("CSSTCD='$fromCollStsCode'", "ARCSTM", "CSALPH");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	$_POST['collStsCode']=strtoupper($_POST['collStsCode']);  Concat_Field("@@stcd", $_POST['collStsCode']);
	Concat_Field("@@desc", $_POST['collStsDesc']);
	Concat_Field("@@alph", $_POST['collStsAlphaSeq']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARCSM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, "$_POST[collStsDesc]", "$_POST[collStsCode]", "", "", "", "");
		} else {
			$Err_CSSTCD=DecatErr_Field("@@stcd", "collStsCode");
			$confMessage=Format_ConfMsg_Desc("", "$_POST[collStsDesc]", "$_POST[collStsCode]", "<br>$Err_CSSTCD", "", "", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;collStsCode=" . urlencode(trim($_POST['collStsCode'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>