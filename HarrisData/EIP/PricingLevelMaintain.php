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
require_once "MCControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Customer Pricing Level Maintenance";
$scriptName     = "PricingLevelMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromLevel=" . urlencode(trim($fromLevel)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HOEPLM_E";
$backURL        = "{$homeURL}{$phpPath}PricingLevel.php{$scriptVarBase}";
$dtlCount 		= RetValue("PMPMLV=$fromLevel", "HDPRCD", "count(*)");

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
	require_once 'MultiChoiceSelectList2.php';
	require_once 'NumEdit.php';
	require_once 'PricingLevelMaintainInclude.php';
	require_once 'UpperCase.php';
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.level.value ==\"\" || ";
	print "\n     document.Chg.levelDesc.value ==\"\" )";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.level, 3, 0)) ";
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
	$pageID = "PRICINGLEVELMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From HDPRLC ";
		$stmtSQL .= " Where PVPMLV=$fromLevel ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hoeplm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hoeplm_OPT['sec_01'];
	$sec_02=$hoeplm_OPT['sec_02'];
	$sec_03=$hoeplm_OPT['sec_03'];
	$sec_04=$hoeplm_OPT['sec_04'];
	if ($sec_03 == "Y") {
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
			$Err_PVPMLV=DecatErr_Field("@@pmlv", "level");
			$Err_PVLVDS=DecatErr_Field("@@lvds", "levelDesc");
			$Err_PVWHSQ =DecatErr_Field("@@whsq", "whsSeq");
			$Err_PVITSQ =DecatErr_Field("@@itsq", "itemSeq");
			$Err_PVCSSQ =DecatErr_Field("@@cssq", "custSeq");
			$Err_PVCCSQ =DecatErr_Field("@@ccsq", "cclsSeq");
			$Err_PVPCSQ =DecatErr_Field("@@pcsq", "pclsSeq");
			$Err_PVRGSQ =DecatErr_Field("@@rgsq", "regnSeq");
			$Err_PVPGSQ =DecatErr_Field("@@pgsq", "pgrpSeq");
			$Err_PVCUSQ =DecatErr_Field("@@cusq", "currSeq");
			$Err_PVCN=DecatErr_Field("@@cn@@", "contract");
			$Err_PVLL=DecatErr_Field("@@ll@@", "listLess");
			$Err_PVCP=DecatErr_Field("@@cp@@", "costPlus");
			$Err_PVDL=DecatErr_Field("@@dl@@", "dollarAmt");
			$Err_PVUP=DecatErr_Field("@@up@@", "usePercent");
			$Err_PVBP=DecatErr_Field("@@bp@@", "bracketQty");
			$Err_PVBA=DecatErr_Field("@@ba@@", "bracketAmt");
			$Err_PVCOMM=DecatErr_Field("@@comm", "commissionable");
			$errFound= "";
		}

		$row['PVPMLV']=Decat_Field("@@pmlv", $edtVar);
		$row['PVLVDS']=Decat_Field("@@lvds", $edtVar);
		$row['PVWHSQ']=Decat_Field("@@whsq", $edtVar);
		$row['PVITSQ']=Decat_Field("@@itsq", $edtVar);
		$row['PVCSSQ']=Decat_Field("@@cssq", $edtVar);
		$row['PVCCSQ']=Decat_Field("@@ccsq", $edtVar);
		$row['PVPCSQ']=Decat_Field("@@pcsq", $edtVar);
		$row['PVRGSQ']=Decat_Field("@@rgsq", $edtVar);
		$row['PVPGSQ']=Decat_Field("@@pgsq", $edtVar);
		$row['PVCUSQ']=Decat_Field("@@cusq", $edtVar);
		$row['PVCN']=Decat_Field("@@cn@@", $edtVar);
		$row['PVLL']=Decat_Field("@@ll@@", $edtVar);
		$row['PVCP']=Decat_Field("@@cp@@", $edtVar);
		$row['PVDL']=Decat_Field("@@dl@@", $edtVar);
		$row['PVUP']=Decat_Field("@@up@@", $edtVar);
		$row['PVBP']=Decat_Field("@@bp@@", $edtVar);
		$row['PVBA']=Decat_Field("@@ba@@", $edtVar);
		$row['PVCOMM']=Decat_Field("@@comm", $edtVar);

	} else {
		if ($maintenanceCode == "Z") {
			$row['PVPMLV'] == "" ;
			$focusfield == 'level' ;

		} else   {
			$focusField= "levelDesc";
		}
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['PVTSTP']) . "\"></td></tr> ";

	$contractChked=Field_Checked($row['PVCN'], "Y");
	$listLessChked=Field_Checked($row['PVLL'], "Y");
	$costPlusChked=Field_Checked($row['PVCP'], "Y");
	$dollarAmtChked=Field_Checked($row['PVDL'], "Y");
	$usePercentChked=Field_Checked($row['PVUP'], "Y");
	$bracketQtyChked=Field_Checked($row['PVBP'], "Y");
	$bracketAmtChked=Field_Checked($row['PVBA'], "Y");

	$textOvr=SetTextOvr($Err_PVPMLV);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Pricing Level</span></td>";
	if ($maintenanceCode == "D") {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"level\" value=\"" . rtrim($row['PVPMLV']) . "\" size=\"5\" maxlength=\"3\">{$row['PVPMLV']}</td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"level\" value=\"" . rtrim($row['PVPMLV']) . "\" size=\"5\" maxlength=\"3\">$reqFieldChar</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_PVPMLV);

	$textOvr=SetTextOvr($Err_PVLVDS);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Description</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"levelDesc\" value=\"" . rtrim($row['PVLVDS']) . "\" size=\"30\" maxlength=\"30\">$reqFieldChar</td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PVLVDS);
	print "\n </table> ";

	if ($dtlCount <= 0 || $maintenanceCode == "Z") {
		print "\n <div class=\"copr\" style=\"border: 1px black solid; margin: 5px;\"> ";
		print "\n To select a category, click on the Available Categories description. <br> ";
		print "\n To move a Selected Category, click on it and then select the up or down icon.<br> ";
		print "\n To remove a Selected Category, click on it and then select the delete icon.";
		print "\n </div>";
	}
	if ($Err_PVWHSQ != "") {
		print "\n <span class=\"error\" $textOvr>$Err_PVWHSQ</span> ";
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
	if ($row['PVWHSQ'] != 0 && $row['PVWHSQ'] != "") {
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n   loadOption(\"0\",document.Chg.choiceBox,$row[PVWHSQ]-1); ";
		print "\n </script> ";
	}
	if ($row['PVITSQ'] != 0 && $row['PVITSQ'] != "")  {
		print "\n <script TYPE=\"text/javascript\">  ";
		print "\n   loadOption(\"1\",document.Chg.choiceBox,$row[PVITSQ]-1); ";
		print "\n </script> ";
	}
	if ($row['PVCSSQ'] != 0 && $row['PVCSSQ'] != "") {
		print "\n <script TYPE=\"text/javascript\">  ";
		print "\n    loadOption(\"2\",document.Chg.choiceBox,$row[PVCSSQ]-1); ";
		print "\n </script> ";
	}
	if ($row['PVCCSQ'] != 0 && $row['PVCCSQ'] != "") {
		print "\n <script TYPE=\"text/javascript\">  ";
		print "\n    loadOption(\"3\",document.Chg.choiceBox,$row[PVCCSQ]-1); ";
		print "\n </script> ";
	}
	if ($row['PVPCSQ'] != 0 && $row['PVPCSQ'] != "") {
		print "\n <script TYPE=\"text/javascript\">  ";
		print "\n    loadOption(\"4\",document.Chg.choiceBox,$row[PVPCSQ]-1); ";
		print "\n </script> ";
	}
	if ($row['PVRGSQ'] != 0 && $row['PVRGSQ'] != "") {
		print "\n <script TYPE=\"text/javascript\">  ";
		print "\n    loadOption(\"5\",document.Chg.choiceBox,$row[PVRGSQ]-1); ";
		print "\n </script> ";
	}
	if ($row['PVPGSQ'] != 0 && $row['PVPGSQ'] != "") {
		print "\n <script TYPE=\"text/javascript\">  ";
		print "\n    loadOption(\"6\",document.Chg.choiceBox,$row[PVPGSQ]-1); ";
		print "\n </script> ";
	}
	if ($row['PVCUSQ'] != 0 && $row['PVCUSQ'] != "") {
		print "\n <script TYPE=\"text/javascript\">  ";
		print "\n    loadOption(\"7\",document.Chg.choiceBox,$row[PVCUSQ]-1); ";
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
	print "\n <table $contentTable> ";

	SetTextOvr($Err_PVCN) ;
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Contract</span></td>  ";
	$contractDisabled = "" ;
	if ($dtlCount>0 && $maintenanceCode != "Z") {
		$contractDisabled = "disabled" ;
	}
	print "\n <td class=\"inputalph\"> ";
	print "\n  <input name=\"contract\" type=\"checkbox\" $contractChked value=\"Y\" $contractDisabled > ";
	print "\n  <input name=\"contract2\" type=\"hidden\" value=\"{$row['PVCN']}\">  ";
	print "\n </td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PVCN) ;

	SetTextOvr($Err_PVLL);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>List Less Amount</span></td> ";
	print "\n <td class=\"inputalph\"><input name=\"listLess\" type=\"checkbox\" $listLessChked value=\"Y\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PVLL);

	SetTextOvr($Err_PVCP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Cost Plus Amount</span></td> ";
	print "\n <td class=\"inputalph\"><input name=\"costPlus\" type=\"checkbox\" $costPlusChked value=\"Y\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PVCP);

	SetTextOvr($Err_PVDL);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Amount</span></td> ";
	print "\n <td class=\"inputalph\"><input name=\"dollarAmt\" type=\"checkbox\" $dollarAmtChked value=\"Y\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PVDL);

	SetTextOvr($Err_PVUP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Use Percentages</span></td> ";
	print "\n <td class=\"inputalph\"><input name=\"usePercent\" type=\"checkbox\" $usePercentChked value=\"Y\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PVUP);

	SetTextOvr($Err_PVBP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Bracket By Quantity</span></td>  ";
	print "\n <td class=\"inputalph\"><input name=\"bracketQty\" type=\"checkbox\" $bracketQtyChked value=\"Y\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PVBP) ;

	SetTextOvr($Err_PVBA);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Bracket By Amount</span></td>  ";
	print "\n <td class=\"inputalph\"><input name=\"bracketAmt\" type=\"checkbox\" $bracketAmtChked value=\"Y\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PVBA) ;

	Build_Flag_Entry('Commissionable',commissionable,'PRCLVLCOMM',commissionable,$row['PVCOMM'],$Err_PVCOMM,'1','1','','','');
	
	print "\n </table>";
	print "\n </fieldset>";
	print "\n <script TYPE=\"text/javascript\">";
	print "\n   document.Chg.$focusField.focus();";
	print "\n </script>";
	print "\n </form>";
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
		$_POST['levelDesc']=RetValue("PVPMLV='$_POST[level]'", "HDPRLC", "PVLVDS");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	if (is_null($_POST['level'])) {$_POST['level'] = $fromLevel;}

	$edtVar= "";
	Concat_Field("@@frm1", $fromLevel);
	Concat_Field("@@pmlv", $_POST['level']);
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	Concat_Field("@@lvds", $_POST['levelDesc']);
	$edtVar .= "}{";
	$edtVar .= "{$_POST['choices']}";
	if ($dtlCount>0 && $maintenanceCode != "Z") {
		Concat_Field("@@cn@@", $_POST['contract2']);
	} else {
		Concat_Field("@@cn@@", $_POST['contract']);
	}
	Concat_Field("@@ll@@", $_POST['listLess']);
	Concat_Field("@@cp@@", $_POST['costPlus']);
	Concat_Field("@@dl@@", $_POST['dollarAmt']);
	Concat_Field("@@up@@", $_POST['usePercent']);
	Concat_Field("@@bp@@", $_POST['bracketQty']);
	Concat_Field("@@ba@@", $_POST['bracketAmt']);
	Concat_Field("@@comm", $_POST['commissionable']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HOEPLM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "$_POST[level] $_POST[levelDesc]", "" , "", "", "", "");
		if ($maintenanceCode != "D" && $fromScript != "") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}PricingLevel.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		} else {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}PricingLevel.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . " \"> ";
		}
	} elseif ($maintenanceCode == "D") {
		$Err_PVPMLV=DecatErr_Field("@@pmlv", "level");
		$confMessage=Format_ConfMsg_Desc("", "$_POST[level] $_POST[levelDesc]", "", "<br>$Err_PVPMLV", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}PricingLevel.php{$scriptVarBase}&amp;tag=LOAD&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;level=" . urlencode(trim($_POST['level'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>