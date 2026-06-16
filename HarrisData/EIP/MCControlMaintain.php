<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$backHome           = $_GET['backHome'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "M/C Control Maintenance";
$scriptName     = "MCControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HMCCTU_E";

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
	require_once 'UpperCase.php';

	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.reclaimResourceLev.value ==\"\" || ";
	print "\n     document.Chg.processCurrencyTranslation.value ==\"\" || ";
	print "\n     document.Chg.baseDomesticCurrencyType.value ==\"\" || ";
	print "\n     document.Chg.unitedStatesCurrencyType.value ==\"\" ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";
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
	$pageID = "MCCONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = " Select * From MCCTRL Where RRN(MCCTRL)=1";
	require 'stmtSQLEnd.php';

	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField= "reclaimResourceLev";
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_MURCLR=DecatErr_Field("@@rclr", "reclaimResourceLev");
			$Err_MUPTFS=DecatErr_Field("@@ptfs", "processCurrencyTranslation");
			$Err_MUCURT=DecatErr_Field("@@curt", "baseDomesticCurrencyType");
			$Err_MUUSDC=DecatErr_Field("@@usdc", "unitedStatesCurrencyType");

		}
		$row['MURCLR']=Decat_Field("@@rclr", $edtVar);
		$row['MUPTFS']=Decat_Field("@@ptfs", $edtVar);
		$row['MUCURT']=Decat_Field("@@curt", $edtVar);
		$row['MUUSDC']=Decat_Field("@@usdc", $edtVar);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_CRTSTP);
	Build_DspFld("M/C Release Version",$HDMCRL,"","A");
	DspErrMsg($Err_CRTSTP);
	Build_DspFld("M/C Library Level",$HDMCLL,"","A");
	Build_Fld_Entry("Reclaim Resource Level","reclaimResourceLev","inputalph","RECLAIMLVL","MURCLR",$row[MURCLR],$Err_MURCLR,"1","1","Y","","");
	Build_Fld_Entry("Process Currency Translation","processCurrencyTranslation","inputalph","YORN","MUPTFS",$row[MUPTFS],$Err_MUPTFS,"1","1","Y","","");
	
	$fieldDesc=RetValue("CYTYPE='$row[MUCURT]'", "HDCTYP", "CYDESC");
	$txtOvr=SetTextOvr($Err_MUCURT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Base Domestic Currency Type</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"baseDomesticCurrencyType\" value=\"" . rtrim($row['MUCURT']) . "\" size=\"3\" maxlength=\"3\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=baseDomesticCurrencyType&amp;fldDesc=baseDomesticCurrencyTypeDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"baseDomesticCurrencyTypeDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_MUCURT);

	$txtOvr=SetTextOvr($Err_MUUSDC);
	$fieldDesc=RetValue("CYTYPE='$row[MUUSDC]'", "HDCTYP", "CYDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>United States Currency Type</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"unitedStatesCurrencyType\" value=\"" . rtrim($row['MUUSDC']) . "\" size=\"3\" maxlength=\"3\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=unitedStatesCurrencyType&amp;fldDesc=unitedStatesCurrencyTypeDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"unitedStatesCurrencyTypeDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_MUUSDC);
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
	$edtVar= "";
	Concat_Field("@@rclr", $_POST['reclaimResourceLev']=strtoupper($_POST['reclaimResourceLev']));
	if (!isset($_POST['processCurrencyTranslation'])) {$_POST['processCurrencyTranslation']="N";} Concat_Field("@@ptfs", $_POST['processCurrencyTranslation']);
	Concat_Field("@@curt", $_POST['baseDomesticCurrencyType']=strtoupper($_POST['baseDomesticCurrencyType']));
	Concat_Field("@@usdc", $_POST['unitedStatesCurrencyType']=strtoupper($_POST['unitedStatesCurrencyType']));
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HMCCTU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc("C", "M/C Control", "", "", "", "", "");
		$includeName= "{$homePath}MCControl{$dataBaseID}.php";
		$fileName="MCControl{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HMCCTL_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>