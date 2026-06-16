<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$from1099Code       = $_GET['from1099Code'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "1099 Description Maintenance";
$scriptName     = "1099DescMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;from1099Code=" . urlencode(trim($from1099Code)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HAPP99_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=20";}

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
	print "\n if (document.Chg.ten99Code.value ==\"\" || ";
	print "\n     document.Chg.ten99Desc.value ==\"\" )";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
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
	$pageID = "1099DESCRIPTIONMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select PTPTCD, PTTPDS, PTALPH, PTFOR# as PTFORM, PTBOX# as PTBOX ";
		$stmtSQL .= " From APP109 ";
		$stmtSQL .= " Where PTPTCD='$from1099Code' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$happ99_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$happ99_OPT['sec_01'];
	$sec_02=$happ99_OPT['sec_02'];
	$sec_03=$happ99_OPT['sec_03'];
	$sec_04=$happ99_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc($sqlResult);
	
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "ten99Code";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_PTPTCD=DecatErr_Field("@@ptcd", "ten99Code");
			$Err_PTTPDS=DecatErr_Field("@@tpds", "ten99Desc");
			$Err_PTALPH=DecatErr_Field("@@alph", "ten99AlphaSeq");
			$Err_PTFORM=DecatErr_Field("@@form", "ten99Form");
			$Err_PTBOX =DecatErr_Field("@@box@", "ten99Box");
			$errFound= "";
		}

		$row['PTPTCD']=Decat_Field("@@ptcd", $edtVar);
		$row['PTTPDS']=Decat_Field("@@tpds", $edtVar);
		$row['PTALPH']=Decat_Field("@@alph", $edtVar);
		$row['PTFORM']=Decat_Field("@@form", $edtVar);
		$row['PTBOX']=Decat_Field("@@box@", $edtVar);

	}	elseif ($maintenanceCode=="Z") {
		$focusField= "ten99Code";

	} else {
		$focusField= "ten99Desc";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_PTPTCD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>1099 Code</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"ten99Code\" value=\"" . rtrim($row['PTPTCD']) . "\" size=\"2\" maxlength=\"2\"> $reqFieldChar</td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"ten99Code\" value=\"" . rtrim($row['PTPTCD']) . "\">$row[PTPTCD]</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_PTPTCD);

	Build_Fld_Entry("Description","ten99Desc","inputalph","","PTTPDS",$row[PTTPDS],$Err_PTTPDS,"25","25","Y","","");

	$fieldDesc=RetValue("(BXFOR#,BXBOX#)=('$row[PTFORM]','$row[PTBOX]')", "APFBOX", "BXDESC");
	$textOvr=SetTextOvr($Err_PTFORM);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>IRS Forms Number</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"ten99Form\" value=\"" . rtrim($row['PTFORM']) . "\" size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}IRSFormsBoxNumberSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldForm=ten99Form&amp;fldBox=ten99Box&amp;fldDesc=ten99FormDesc\" onclick=\"$searchWinVar\"> $searchImage </a> ";
	print "\n     <span class=\"dspdesc\" id=\"ten99FormDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_PTFORM);

	$textOvr=SetTextOvr($Err_PTBOX);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Box Number</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"ten99Box\" value=\"" . rtrim($row['PTBOX']) . "\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}IRSFormsBoxNumberSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldForm=ten99Form&amp;fldBox=ten99Box&amp;fldDesc=ten99FormDesc\" onclick=\"$searchWinVar\"> $searchImage </a></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PTBOX);

	Build_Fld_Entry("Alpha Sequence","ten99AlphaSeq","inputalph","","PTALPH",$row[PTALPH],$Err_PTALPH,"3","4","","","");

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
	if ($maintenanceCode=="D" && is_null($_POST['ten99Code'])) {
		$_POST['ten99Code']=$from1099Code;
		$_POST['ten99Desc']=RetValue("PTPTCD='$from1099Code'", "APP109", "PTTPDS");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@ptcd", strtoupper($_POST['ten99Code']));
	Concat_Field("@@tpds", $_POST['ten99Desc']);
	Concat_Field("@@alph", strtoupper($_POST['ten99AlphaSeq']));
	Concat_Field("@@form", strtoupper($_POST['ten99Form']));
	Concat_Field("@@box@", strtoupper($_POST['ten99Box']));
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HAPP99_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST[ten99Desc], $_POST[ten99Code], "", "", "", "");
		} else {
			$Err_PTPTCD=DecatErr_Field("@@ptcd", "ten99Code");
			$confMessage=Format_ConfMsg_Desc("", $_POST[ten99Desc], $_POST[ten99Code], "<br>$Err_PTPTCD", "", "", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;ten99Code=" . urlencode(trim($_POST['ten99Code'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>