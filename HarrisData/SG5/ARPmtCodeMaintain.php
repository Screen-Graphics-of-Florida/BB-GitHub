<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromPmtCode        = $_GET['fromPmtCode'];
$pmtCode            = $_GET['pmtCode'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Payment Code Maintenance";
$scriptName     = "ARPmtCodeMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromPmtCode=" . urlencode(trim($fromPmtCode));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HARPYU_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "" || $maintenanceCode  == "D") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=5";}

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
	print "\n if (document.Chg.pmtCodeDesc.value ==\"\" )";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.acctNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.subNumber, 4, 0)) ";
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
	$pageID = "ARPMTCODEMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select PYPYCD, PYPYDS, PYSTDS, PYACCT, PYSUB, PYTSTP ";
		$stmtSQL .= " From ARPYCD ";
		$stmtSQL .= " Where PYPYCD='$fromPmtCode' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$harpyu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harpyu_OPT['sec_01'];
	$sec_02=$harpyu_OPT['sec_02'];
	$sec_03=$harpyu_OPT['sec_03'];
	$sec_04=$harpyu_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "pmtCode";
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_PYPYCD=DecatErr_Field("@@pycd", "pmtCode");
			$Err_PYPYDS=DecatErr_Field("@@pyds", "pmtCodeDesc");
			$Err_PYSTDS=DecatErr_Field("@@stds", "stmtDesc");
			$Err_PYACCT=DecatErr_Field("@@acct", "acctNumber");
			$Err_PYSUB =DecatErr_Field("@@sub@", "subNumber");
			$errFound= "";
		}

		$row['PYPYCD']=Decat_Field("@@pycd", $edtVar);
		$row['PYPYDS']=Decat_Field("@@pyds", $edtVar);
		$row['PYSTDS']=Decat_Field("@@stds", $edtVar);
		$row['PYACCT']=Decat_Field("@@acct", $edtVar);
		$row['PYSUB'] =Decat_Field("@@sub@", $edtVar);
		$row['PYTSTP']=Decat_Field("@@tstp", $edtVar);

	} else {
		$focusField= "pmtCodeDesc";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['PYTSTP']) . "\"></td></tr> ";

	$textOvr=SetTextOvr($Err_PYPYCD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Payment Code</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"pmtCode\" value=\"" . rtrim($row['PYPYCD']) . "\" size=\"1\" maxlength=\"1\"></td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"pmtCode\" value=\"" . rtrim($row['PYPYCD']) . "\">$row[PYPYCD]</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_PYPYCD);

	Build_Fld_Entry("Description","pmtCodeDesc","inputalph","","PYPYDS",$row[PYPYDS],$Err_PYPYDS,"30","30","Y","","");
	Build_Fld_Entry("Statement Description","stmtDesc","inputalph","","PYSTDS",$row[PYSTDS],$Err_PYSTDS,"10","10","","","");

	$row['PYACCT']=Default_Zero($row['PYACCT']);
	$row['PYSUB']=Default_Zero($row['PYSUB']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[PYACCT],$row[PYSUB])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_PYACCT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Account</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"acctNumber\" value=\"" . rtrim($row['PYACCT']) . "\" size=\"2\" maxlength=\"4\"> - <input type=\"text\"   name=\"subNumber\" value=\"" . rtrim($row['PYSUB']) . "\" size=\"2\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=acctNumber&amp;subFld=subNumber&amp;descFld=acctNumberDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"acctNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_PYACCT);

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
	if ($maintenanceCode=="D" && is_null($_POST['pmtCode'])) {
		$_POST['pmtCode']    =$fromPmtCode;
		$_POST['pmtCodeDesc']=RetValue("PYPYCD='$_POST[pmtCode]'", "ARPYCD", "PYPYDS");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@frm1", $fromPmtCode);
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	$_POST['pmtCode']=strtoupper($_POST['pmtCode']);  Concat_Field("@@pycd", $_POST['pmtCode']);
	Concat_Field("@@pyds", $_POST['pmtCodeDesc']);
	Concat_Field("@@stds", $_POST['stmtDesc']);
	Concat_Field("@@acct", $_POST['acctNumber']);
	Concat_Field("@@sub@", $_POST['subNumber']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARPYU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST[pmtCodeDesc], $_POST[pmtCode], "", "", "", "");
		} else {
			$Err_PYPYCD=DecatErr_Field("@@pycd", "pmtCode");
			$confMessage=Format_ConfMsg_Desc("", $_POST[pmtCodeDesc], $_POST[pmtCode], "", "", "<br>$Err_PYPYCD", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;pmtCode=" . urlencode(trim($_POST['pmtCode'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>