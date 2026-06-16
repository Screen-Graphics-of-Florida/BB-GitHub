<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode  = $_GET['maintenanceCode'];
$errFound         = $_GET['errFound'];
$wrnVar           = $_GET['wrnVar'];
$fromScript       = $_GET['fromScript'];
$fromLevel        = $_GET['fromLevel'];
$level            = $_GET['level'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Vendor Pricing Level Maintenance";
$scriptName     = "VendorPricingLevelMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromLevel=" . urlencode(trim($fromLevel)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPOPLM_E";
$backURL="{$homeURL}{$phpPath}VendorPricingLevel.php{$scriptVarBase}&amp;tag=REPORT";

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
	require_once 'NumEdit.php';
	require_once 'VendorPricingLevelMaintainInclude.php';
	require_once 'Menu.js';
	require_once 'MultiChoiceSelectList2.php';
	require_once 'UpperCase.php';
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.levelDesc.value ==\"\" )";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.level, 2, 0)) ";
	print "\n return true;";
	print "\n }";

	print "\n function check(chgForm) {";
	print "\n if (validate(chgForm) && saveSel(chgForm.choiceBox,chgForm.choices)) chgForm.submit()";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "VENDORPRICINGLEVELMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From POVPLV ";
		$stmtSQL .= " Where VLPMLV=$fromLevel ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hpoplm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hpoplm_OPT['sec_01'];
	$sec_02=$hpoplm_OPT['sec_02'];
	$sec_03=$hpoplm_OPT['sec_03'];
	$sec_04=$hpoplm_OPT['sec_04'];
	if (sec_03 == "Y") {
		RtvFldDesc(VDPMLV==$fromLevel, "POVPDT", CHAR(COUNT(VDPMLV)), dtlCount);
		if ($dtlCount > 0) {
			$sec_03= "N";
		}
	}
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "level";
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_VLPMLV=DecatErr_Field("@@pmlv", "level");
			$Err_VLLVDS=DecatErr_Field("@@lvds", "levelDesc");
			$Err_VLWHSQ =DecatErr_Field("@@whsq", "whsSeq");
			$Err_VLITSQ =DecatErr_Field("@@itsq", "itemSeq");
			$Err_VLVNSQ =DecatErr_Field("@@vnsq", "vendSeq");
			$Err_VLCN=DecatErr_Field("@@cn@@", "contract");
			$Err_VLDL=DecatErr_Field("@@dl@@", "dollarAmt");
			$Err_VLUP =DecatErr_Field("@@up@@", "usePct");
			$Err_VLBP =DecatErr_Field("@@bp@@", "bracketAmt");
			$errFound= "";
		}

		$row['VLPMLV']=Decat_Field("@@pmlv", $edtVar);
		$row['VLLVDS']=Decat_Field("@@lvds", $edtVar);
		$row['VLWHSQ']=Decat_Field("@@whsq", $edtVar);
		$row['VLITSQ']=Decat_Field("@@itsq", $edtVar);
		$row['VLVNSQ']=Decat_Field("@@vnsq", $edtVar);
		$row['VLCN']=Decat_Field("@@cn@@", $edtVar);
		$row['VLDL']=Decat_Field("@@dl@@", $edtVar);
		$row['VLUP']=Decat_Field("@@up@@", $edtVar);
		$row['VLBP']=Decat_Field("@@bp@@", $edtVar);

	} else {
		if ($maintenanceCode == "Z") {
			$row['VLPMLV'] == "" ;
			$focusfield == 'fromLevel' ;

		} else   {
			$focusField= "levelDesc";
		}
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$contractChked=Field_Checked($row['VLCN'], "Y");
	$dollarAmtChked=Field_Checked($row['VLDL'], "Y");
	$usePercentChked=Field_Checked($row['VLUP'], "Y");
	$bracketAmtChked=Field_Checked($row['VLBP'], "Y");

	$textOvr=SetTextOvr($Err_VLPMLV);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Pricing Level</span></td>";
	if ($maintenanceCode == "C" || $maintenanceCode == "D") {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"level\" value=\"" . rtrim($row['VLPMLV']) . "\" size=\"5\" maxlength=\"2\">{$row['VLPMLV']}</td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"level\" value=\"" . rtrim($row['VLPMLV']) . "\" size=\"5\" maxlength=\"2\">$reqFieldChar</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_VLPMLV);

	$textOvr=SetTextOvr($Err_VLLVDS);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Description</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"levelDesc\" value=\"" . rtrim($row['VLLVDS']) . "\" size=\"30\" maxlength=\"30\">$reqFieldChar</td> ";
	print "\n </tr> ";
	DspErrMsg($Err_VLLVDS);
	print "\n </table> ";

	if ($dtlCount <= 0 || $maintenanceCode == "Z") {
		print "\n <div class=\"copr\" style=\"border: 1px black solid; margin: 5px;\"> ";
		print "\n To select a category, click on the Available Categories description. <br> ";
		print "\n To move a Selected Category, click on it and then select the up or down icon.<br> ";
		print "\n To remove a Selected Category, click on it and then select the delete icon.";
		print "\n </div>";
	}
	if ($Err_VLWHSQ != "") {
		print "\n <span class=\"error\" $textOvr>$Err_VLWHSQ</span> ";
	}

	print "\n     <table $contentTable><tr>";
	if ($dtlCount <= "0" || $maintenanceCode == "Z") {
		print "\n <td valign=\"top\" class=\"colhdr\"> ";
		print "\n                 Available Categories:  ";
		print "\n   <br><select name=\"available\" size=8 onChange=moveSelOver();></select> ";
		print "\n       <script TYPE=\"text/javascript\">loadAvailable(document.Chg.available);</script> ";
		print "\n  </td> ";
		print "\n  <td>&nbsp;</td> ";
	}
	print "\n  <td valign=\"top\" class=\"colhdr\"> ";
	print "\n                 Selected Categories:   ";
	print "\n    <br><select multiple name=\"choiceBox\" size=8></select> ";
	if ($row['VLWHSQ'] != 0 && $row['VLWHSQ'] != "") {
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n   loadOption(\"0\",document.Chg.choiceBox,$row[VLWHSQ]-1); ";
		print "\n </script> ";
	}
	if ($row['VLITSQ'] != 0 && $row['VLITSQ'] != "")  {
		print "\n <script TYPE=\"text/javascript\">  ";
		print "\n   loadOption(\"1\",document.Chg.choiceBox,$row[VLITSQ]-1); ";
		print "\n </script> ";
	}
	if ($row['VLVNSQ'] != 0 && $row['VLVNSQ'] != "") {
		print "\n <script TYPE=\"text/javascript\">  ";
		print "\n    loadOption(\"2\",document.Chg.choiceBox,$row[VLVNSQ]-1); ";
		print "\n </script> ";
	}

	print "\n </td> ";
	print "\n <td valign=\"middle\"> ";
	if ($dtlCount <= 0 || $maintenanceCode == "Z")  {
		print "\n $reqFieldChar<br><br> ";
		print "\n <a onClick=\"moveSelUp(document.Chg.choiceBox)\">$topOfFormImage</a><br><br> ";
		print "\n <a onClick=\"moveSelDown(document.Chg.choiceBox)\">$botOfFormImage</a><br><br>  ";
		print "\n <a onClick=\"removeSel(document.Chg.choiceBox)\">$deleteImageSml</a>  ";
	}
	print "\n <input name=\"choices\" type=\"hidden\"></td></tr>  ";
	print "\n </table> ";

	print "\n <fieldset><legend class=\"legendTitle\">Pricing Structure Definition</legend> ";
	print "\n <table $(contentTable)> ";
	SetTextOvr($Err_VLCN) ;
	print "\n <tr><td class=\"dsphdr\"><span ($textOvr)>Contract</span></td>  ";
	$contractDisabled = "" ;
	if ($dtlCount>0 && $maintenanceCode != "Z"){ ;
	$contractDisabled = "y" ;
	}
	print "\n <td class=\"inputalph\"> ";
	print "\n  <input name=\"contract\" type=\"checkbox\" $contractChked value=\"Y\" $contractDisabled ;> ";
	print "\n  <input name=\"contract2\" type=\"hidden\" value=\"(VLCN)\">  ";
	print "\n </td> ";
	print "\n </tr> ";
	DspErrMsg($Err_VLCN) ;

	SetTextOvr($Err_VLDL);
	print "\n <tr><td class=\"dsphdr\"><span ($textOvr)>Dollar Amount</span></td> ";
	print "\n <td class=\"inputalph\"><input name=\"dollarAmt\" type=\"checkbox\" $dollarAmtChked value=\"Y\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_VLDL);

	SetTextOvr($Err_VLUP);
	print "\n <tr><td class=\"dsphdr\"><span ($textOvr)>Use Percentages</span></td> ";
	print "\n <td class=\"inputalph\"><input name=\"usePct\" type=\"checkbox\" $usePercentChked value=\"Y\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_VLUP);

	SetTextOvr($Err_VLBP);
	print "\n <tr><td class=\"dsphdr\"><span ($textOvr)>Bracket By Quantity</span></td>  ";
	print "\n <td class=\"inputalph\"><input name=\"bracketAmt\" type=\"checkbox\" $bracketAmtChked value=\"Y\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_VLBP) ;

	print "\n <script TYPE=\"text/javascript\"> ";
	print "\n     document.Chg.$focusField.focus();</script> ";
	print "\n </table>";
	print "\n </fieldset>";

	require_once 'MaintainBottom.php';
	print $hrTagAttr ;
	require_once 'Copyright.php';
	print "\n </td></tr>";
	print "\n </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
}


if ($tag == "Edit_Data") {
	if ($maintenanceCode=="D" ) {
		$_POST['level']    =$fromLevel;
		$_POST['levelDesc']=RetValue("VLPMLV='$_POST[level]'", "POVPLV", "VLLVDS");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	if (is_null($_POST['level'])) {$_POST['level'] = $fromLevel;}

	$edtVar= "";
	Concat_Field("@@pmlv", $_POST['level']);
	Concat_Field("@@lvds", $_POST['levelDesc']);
	$edtVar .= "}{";
	$edtVar .= "{$_POST['choices']}";
	Concat_Field("@@cn@@", $_POST['contract']);
	Concat_Field("@@dl@@", $_POST['dollarAmt']);
	Concat_Field("@@up@@", $_POST['usePct']);
	Concat_Field("@@bp@@", $_POST['bracketAmt']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPOPLM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "$_POST[level] $_POST[levelDesc]", "" , "", "", "", "");
		if ($maintenanceCode != "D" && $fromScript != "") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}VendorPricingLevel.php{$scriptVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		} else {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}VendorPricingLevel.php{$scriptVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . " \"> ";
		}
	} elseif ($maintenanceCode == "D") {
		$Err_VLPMLV=DecatErr_Field("@@pmlv", "level");
		$confMessage=Format_ConfMsg_Desc("", "$_POST[level] $_POST[levelDesc]", "", "<br>$Err_VLPMLV", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}VendorPricingLevel.php{$scriptVarBase}&amp;tag=LOAD&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;level=" . urlencode(trim($_POST['level'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>