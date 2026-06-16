<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$fromLocCode        = $_GET['fromLocCode'];
$fromCurType        = $_GET['fromCurType'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Service Charge Maintenance";
$scriptName     = "ServiceChargeMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromLocCode=" . urlencode(trim($fromLocCode)) . "&amp;fromCurType=" . urlencode(trim($fromCurType));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HARSCU_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=21";}

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
	print "\n if ( ";
	if ($HDMCRL>0 && $CRPRMC=="Y")  {
		print "document.Chg.currType.value ==\"\" || ";
	}
	print "\n  document.Chg.upCurrLimit1.value ==\"\" || ";
	print "\n  document.Chg.upCurrLimit1.value ==\"\") ";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.chargeAftNumberOfDays, 3, 0) && ";
	print "\n     editNum(document.Chg.minCharge, 5, 2) && ";
	print "\n     editNum(document.Chg.upCurrLimit1, 13, 2) && ";
	print "\n     editNum(document.Chg.percent1, 2, 3) && ";
	print "\n     editNum(document.Chg.upCurrLimit2, 13, 2) && ";
	print "\n     editNum(document.Chg.percent2, 2, 3) && ";
	print "\n     editNum(document.Chg.upCurrLimit3, 13, 2) && ";
	print "\n     editNum(document.Chg.percent3, 2, 3)) ";
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
	$pageID = "SERVICECHARGEMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select SGLCCD,SGCTYP,SGMNCR,SGDAYS,SGPCT1,SGPCT2,SGPCT3 ";
		$stmtSQL .= "       ,Char(SGULT1) as SGULT1,Char(SGULT2) as SGULT2,Char(SGULT3) as SGULT3 ";
		$stmtSQL .= " From ARSCHG ";
		$stmtSQL .= " Where (SGLCCD,SGCTYP)=('$fromLocCode','$fromCurType') ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$harscu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harscu_OPT['sec_01'];
	$sec_02=$harscu_OPT['sec_02'];
	$sec_03=$harscu_OPT['sec_03'];
	$sec_04=$harscu_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "state";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_SGLCCD=DecatErr_Field("@@lccd", "localCode");
			$Err_SGSTID=DecatErr_Field("@@stid", "state");
			$Err_SGCTRY=DecatErr_Field("@@ctry", "country");
			$Err_SGCTYP=DecatErr_Field("@@ctyp", "currType");
			$Err_SGMNCR=DecatErr_Field("@@mncr", "minCharge");
			$Err_SGDAYS=DecatErr_Field("@@days", "chargeAftNumberOfDays");
			$Err_SGULT1=DecatErr_Field("@@ult1", "upCurrLimit1");
			$Err_SGULT2=DecatErr_Field("@@ult2", "upCurrLimit2");
			$Err_SGULT3=DecatErr_Field("@@ult3", "upCurrLimit3");
			$Err_SGPCT1=DecatErr_Field("@@pct1", "percent1");
			$Err_SGPCT2=DecatErr_Field("@@pct2", "percent2");
			$Err_SGPCT3=DecatErr_Field("@@pct3", "percent3");
			$errFound= "";
		}

		$row['SGLCCD']=Decat_Field("@@lccd", $edtVar);
		$row['SGSTID']=Decat_Field("@@stid", $edtVar);
		$row['SGCTRY']=Decat_Field("@@ctry", $edtVar);
		$row['SGCTYP']=Decat_Field("@@ctyp", $edtVar);
		$row['SGMNCR']=Decat_Field("@@mncr", $edtVar);
		$row['SGDAYS']=Decat_Field("@@days", $edtVar);
		$row['SGULT1']=Decat_Field("@@ult1", $edtVar);
		$row['SGULT2']=Decat_Field("@@ult2", $edtVar);
		$row['SGULT3']=Decat_Field("@@ult3", $edtVar);
		$row['SGPCT1']=Decat_Field("@@pct1", $edtVar);
		$row['SGPCT2']=Decat_Field("@@pct2", $edtVar);
		$row['SGPCT3']=Decat_Field("@@pct3", $edtVar);
		$row['SGTSTP']=Decat_Field("@@@tstp", $edtVar);

	}	elseif ($maintenanceCode=="Z") {
		$focusField= "state";
		$recCount=RetValue("CNCTCD='$row[SGLCCD]'", "HDCTRY", "Char(Count(*))");
		if ($recCount>0) {$row['SGCTRY']=$row['SGLCCD'];}
		else             {$row['SGSTID']=$row['SGLCCD'];}

	} else {
		$focusField= "chargeAftNumberOfDays";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	print "\n <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['SGTSTP']) . "\"></td></tr> ";

	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		// state
		$fieldDesc=RetValue("STID='$row[SGSTID]'", "HDSTID", "STDESC");
		$textOvr=SetTextOvr($Err_SGSTID);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>State ID</span></td>";
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"state\" value=\"" . rtrim($row['SGSTID']) . "\" size=\"3\" maxlength=\"2\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=state&amp;fldDesc=stateDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"stateDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_SGSTID);

		// country
		$fieldDesc=RetValue("CNCTCD='$row[SGCTRY]'", "HDCTRY", "CNCDES");
		$textOvr=SetTextOvr($Err_SGCTRY);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>-OR- Country Code</span></td>";
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"country\" value=\"" . rtrim($row['SGCTRY']) . "\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CountrySearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=country&amp;fldDesc=countryDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"countryDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_SGCTRY);
	} else {
		// locality code
		$fieldDesc=RetValue("CNCTCD='$row[SGLCCD]'", "HDCTRY", "CNCDES");
		if ($fieldDesc) {
			$fldHdg = "Country Code";
		} else {
			$fieldDesc=RetValue("STID='$row[SGLCCD]'", "HDSTID", "STDESC");
			$fldHdg = "State ID";
		}

		$textOvr=SetTextOvr($Err_SGLCCD);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>$fldHdg</span></td>";
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"localCode\" value=\"" . rtrim($row['SGLCCD']) . "\">$row[SGLCCD] $fieldDesc</td>";
		print "\n </tr> ";
		DspErrMsg($Err_SGLCCD);
	}

	// Multi-Currency Processing
	if ($HDMCRL>0 && $CRPRMC=="Y")  {
		$fieldDesc=RetValue("CYTYPE='$row[SGCTYP]'", "HDCTYP", "CYDESC");
		$textOvr=SetTextOvr($Err_SGCTYP);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Currency Type</span></td>";
		if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
			print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"currType\" value=\"" . rtrim($row['SGCTYP']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                         <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=currType&amp;fldDesc=currTypeDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
			print "\n                         <span class=\"dspdesc\" id=\"currTypeDesc\">$fieldDesc</span></td>";
		} else {
			print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"currType\" value=\"" . rtrim($row['SGCTYP']) . "\">$row[SGCTYP] $fieldDesc</td>";
		}
		print "\n </tr> ";
		DspErrMsg($Err_SGCTYP);
	}

	Build_Fld_Entry("Charge After Number Of Days","chargeAftNumberOfDays","inputnmbr","","SGDAYS",$row[SGDAYS],$Err_SGDAYS,"3","3","","","");
	Build_Fld_Entry("Minimum Charge","minCharge","inputnmbr","","SGMNCR",$row[SGMNCR],$Err_SGMNCR,"9","9","","","");

	print "\n <tr><td>&nbsp;</td></tr> ";
	print "\n </table> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Upper Currency</legend> ";
	print "\n <table $contentTable>";

	print "\n <tr><td>&nbsp;</td> ";
	print "\n      <td class=\"colhdr\">Amount</td>";
	print "\n      <td class=\"colhdr\">Percentage</td></tr> ";

	$textOvr=SetTextOvr($Err_SGULT1); if ($textOvr == "") {$textOvr=SetTextOvr($Err_SGPCT1);}
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Limit 1</span></td>";
	Build_Fld_Entry("","upCurrLimit1","inputnmbr","","SGULT1",$row[SGULT1],$Err_SGULT1,"20","17","Y","","Y");
	Build_Fld_Entry("","percent1","inputnmbr","","SGPCT1",$row[SGPCT1],$Err_SGPCT1,"10","6","Y","","Y");
	print "\n </tr> ";
	DspErrMsg($Err_SGULT1);
	DspErrMsg($Err_SGPCT1);

	$textOvr=SetTextOvr($Err_SGULT2); if ($textOvr == "") {$textOvr=SetTextOvr($Err_SGPCT2);}
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Limit 2</span></td>";
	Build_Fld_Entry("","upCurrLimit2","inputnmbr","","SGULT2",$row[SGULT2],$Err_SGULT2,"20","17","","","Y");
	Build_Fld_Entry("","percent2","inputnmbr","","SGPCT2",$row[SGPCT2],$Err_SGPCT2,"10","6","","","Y");
	print "\n </tr> ";
	DspErrMsg($Err_SGULT2);
	DspErrMsg($Err_SGPCT2);

	$textOvr=SetTextOvr($Err_SGULT3); if ($textOvr == "") {$textOvr=SetTextOvr($Err_SGPCT3);}
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Limit 3</span></td>";
	Build_Fld_Entry("","upCurrLimit3","inputnmbr","","SGULT3",$row[SGULT3],$Err_SGULT3,"20","17","","","Y");
	Build_Fld_Entry("","percent3","inputnmbr","","SGPCT3",$row[SGPCT3],$Err_SGPCT3,"10","6","","","Y");
	print "\n </tr> ";
	DspErrMsg($Err_SGULT3);
	DspErrMsg($Err_SGPCT3);

	print "\n </table> ";
	print "\n </fieldset> ";

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
	if ($maintenanceCode=="D" && is_null($_POST['localCode'])) {
		$_POST['localCode']    =$fromLocCode;
		$_POST['currType']     =$fromCurType;
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	if ($maintenanceCode=="A") {
		Concat_Field("@@stid", strtoupper($_POST['state']));
		Concat_Field("@@ctry", strtoupper($_POST['country']));
		if ($_POST['country'] != "") {$_POST['localCode']=strtoupper($_POST['country']);}
		else                         {$_POST['localCode']=strtoupper($_POST['state']);}
	}
	Concat_Field("@@lccd", strtoupper($_POST['localCode']));
	if ($HDMCRL>0 && $CRPRMC=="Y")  {Concat_Field("@@ctyp", strtoupper($_POST['currType']));}
	Concat_Field("@@mncr", $_POST['minCharge']);
	Concat_Field("@@days", $_POST['chargeAftNumberOfDays']);
	Concat_Field("@@ult1", $_POST['upCurrLimit1']);
	Concat_Field("@@ult2", $_POST['upCurrLimit2']);
	Concat_Field("@@ult3", $_POST['upCurrLimit3']);
	Concat_Field("@@pct1", $_POST['percent1']);
	Concat_Field("@@pct2", $_POST['percent2']);
	Concat_Field("@@pct3", $_POST['percent3']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARSCU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST['localCode'], $_POST['currType'], "", "", "", "");
		} else {
			$Err_SGLCCD=DecatErr_Field("@@lccd", "localCode");
			$Err_SGCTYP=DecatErr_Field("@@ctyp", "currType");
			$confMessage=Format_ConfMsg_Desc("", $_POST['localCode'], $_POST['currType'], "<br>$Err_SGLCCD", "<br>$Err_SGCTYP", "", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;localCode=" . urlencode(trim($_POST['localCode'])) . "&amp;currType=" . urlencode(trim($_POST['currType'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>