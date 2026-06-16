<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'ARPmtTypeInclude.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromPmtCode        = $_GET['fromPmtCode'];
$pmtSubCode         = $_GET['pmtSubCode'];
$fromScript         = $_GET['fromScript'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Payment Sub Code Maintenance";
$scriptName     = "ARPmtSubCodeMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromPmtCode=" . urlencode(trim($fromPmtCode)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D&amp;pmtSubCode=" . urlencode(trim($pmtSubCode)) . "&amp;pmtSubCodeDesc=" . urlencode(trim($pmtSubCodeDesc));
$programName    = "HARPSM_E";
$backURL="{$homeURL}{$phpPath}ARPmtSubCode.php{$scriptVarBase}&amp;tag=REPORT";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

$PYTYPE=RetValue("PYPYCD='$fromPmtCode'", "ARPYCD", "PYTYPE");
if     ($PYTYPE=="C") {$CPCFOV=$C_CPCFOV; $CPACOV=$C_CPACOV; $CPCSAC=$C_CPCSAC; $CPARAC=$C_CPARAC; $CPOFAC=$C_CPOFAC;}
elseif ($PYTYPE=="D") {$CPCFOV=$D_CPCFOV; $CPACOV=$D_CPACOV; $CPCSAC=$D_CPCSAC; $CPARAC=$D_CPARAC; $CPOFAC=$D_CPOFAC;}
elseif ($PYTYPE=="J") {$CPCFOV=$J_CPCFOV; $CPACOV=$J_CPACOV; $CPCSAC=$J_CPCSAC; $CPARAC=$J_CPARAC; $CPOFAC=$J_CPOFAC;}
elseif ($PYTYPE=="M") {$CPCFOV=$M_CPCFOV; $CPACOV=$M_CPACOV; $CPCSAC=$M_CPCSAC; $CPARAC=$M_CPARAC; $CPOFAC=$M_CPOFAC;}
elseif ($PYTYPE=="U") {$CPCFOV=$U_CPCFOV; $CPACOV=$U_CPACOV; $CPCSAC=$U_CPCSAC; $CPARAC=$U_CPARAC; $CPOFAC=$U_CPOFAC;}
elseif ($PYTYPE=="Y") {$CPCFOV=$Y_CPCFOV; $CPACOV=$Y_CPACOV; $CPCSAC=$Y_CPCSAC; $CPARAC=$Y_CPARAC; $CPOFAC=$Y_CPOFAC;}
else                  {$CPCFOV="N"; $CPACOV="N"; $CPCSAC="N"; $CPARAC="N"; $CPOFAC="N";}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";

	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';

	print "\n function validate(chgForm) {";
	print "\n   if ( ";
	if ($CPCSAC=="Y") {
		print "\n       editNum(document.Chg.cashAcct, 4, 0) && ";
		print "\n       editNum(document.Chg.cashSubacct, 4, 0) && ";
	}
	if ($CPARAC=="Y") {
		print "\n       editNum(document.Chg.arAcct, 4, 0) && ";
		print "\n       editNum(document.Chg.arSubacct, 4, 0) && ";
	}
	if ($CPOFAC=="Y") {
		print "\n       editNum(document.Chg.offCo, 2, 0) && ";
		print "\n       editNum(document.Chg.offFac, 4, 0) && ";
		print "\n       editNum(document.Chg.offAcct, 4, 0) && ";
		print "\n       editNum(document.Chg.offSubacct, 4, 0) && ";
	}
	print "\n       editdate(document.Chg.dateDeactivated)) ";
	print "\n       return true; ";
	print "\n } ";
	print "\n function confirmDelete() {return confirm(\"$delRecordConf\")} ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARPMTSUBCODEMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";

	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * From ARPYSB ";
		$stmtSQL .= " Where PSSBCD='$pmtSubCode' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$harpsm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harpsm_OPT['sec_01'];
	$sec_02=$harpsm_OPT['sec_02'];
	if (trim($pmtSubCode)==trim($fromPmtCode)) {$sec_03="N";} else {$sec_03=$harpsm_OPT['sec_03'];}  // Cannot delete
	$sec_04=$harpsm_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print "\n <table $contentTable> ";
	$PYPYDS=RetValue("PYPYCD='$fromPmtCode'", "ARPYCD", "PYPYDS");
	@Format_Header("Payment Code", $PYPYDS, $fromPmtCode);
	print "\n </table> ";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField= "pmtSubCode";
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
		} else {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_PSSBCD=DecatErr_Field("@@sbcd", "pmtSubCode");
			$Err_PSDESC=DecatErr_Field("@@desc", "pmtSubCodeDesc");
			$Err_PSPYCD=DecatErr_Field("@@pycd", "paymentCode");
			$Err_PSCFOV=DecatErr_Field("@@cfov", "allowCoFacOvr");
			$Err_PSACOV=DecatErr_Field("@@acov", "allowAcctOvr");
			$Err_PSSTDS=DecatErr_Field("@@stds", "stmtDesc");
			$Err_PSCSAC=DecatErr_Field("@@csac", "cashAcct");
			$Err_PSARAC=DecatErr_Field("@@arac", "arAcct");
			$Err_PSOFCO=DecatErr_Field("@@ofco", "offCo");
			$Err_PSOFAC=DecatErr_Field("@@ofac", "offAcct");
			$Err_PSDTDE=DecatErr_Field("@@dtde", "dateDeactivated");
		}

		$row['BMBCHN']=Decat_Field("@@bchn", $edtVar);
		$row['PSSBCD']=Decat_Field("@@sbcd", $edtVar);
		$row['PSDESC']=Decat_Field("@@desc", $edtVar);
		$row['PSPYCD']=Decat_Field("@@pycd", $edtVar);
		$row['PSCFOV']=Decat_Field("@@cfov", $edtVar);
		$row['PSACOV']=Decat_Field("@@acov", $edtVar);
		$row['PSSTDS']=Decat_Field("@@stds", $edtVar);
		$row['PSCSAC']=Decat_Field("@@csac", $edtVar);
		$row['PSCSSB']=Decat_Field("@@cssb", $edtVar);
		$row['PSARAC']=Decat_Field("@@arac", $edtVar);
		$row['PSARSB']=Decat_Field("@@arsb", $edtVar);
		$row['PSOFCO']=Decat_Field("@@ofco", $edtVar);
		$row['PSOFFC']=Decat_Field("@@offc", $edtVar);
		$row['PSOFAC']=Decat_Field("@@ofac", $edtVar);
		$row['PSOFSB']=Decat_Field("@@ofsb", $edtVar);
		$row['PSDTDE']=Decat_Field("@@dtde", $edtVar);
		$row['PSTSTP']=Decat_Field("@@tstp", $edtVar);

		if ($errFound == "" && $maintenanceCode == "A") {
			$row['PSCSAC']=0;
			$row['PSCSSB']=0;
			$row['PSARAC']=0;
			$row['PSARSB']=0;
			$row['PSOFAC']=0;
			$row['PSOFSB']=0;
			$focusField= "pmtSubCode";
		}
		$errFound= "";
	} else {
		$row['PSDTDE']=DateInputFromISO($row['PSDTDE']);
		if ($maintenanceCode != "A" && $maintenanceCode != "Z") {$focusField="pmtSubCodeDesc";}
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . trim($row['PSTSTP']) . "\"></td></tr> ";
	print "\n     <tr><td><input type=\"hidden\" name=\"paymentCode\" value=\"$fromPmtCode\"></td></tr> ";

	$textOvr=SetTextOvr($Err_PSSBCD);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Payment Sub Code</span></td> ";
	if ($maintenanceCode == "A" || $maintenanceCode == "Z") {
		print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"pmtSubCode\" value=\"" . trim($row['PSSBCD']) . "\" size=\"4\" maxlength=\"4\"></td> ";
	} else {
		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"pmtSubCode\" value=\"" . trim($row['PSSBCD']) . "\">$row[PSSBCD]</td> ";
	}
	print "\n     </tr> ";
	DspErrMsg($Err_PSSBCD);

	Build_Fld_Entry("Description","pmtSubCodeDesc","inputalph","","PSDESC",$row[PSDESC],$Err_PSDESC,"30","30","","","");
	Build_Fld_Entry("Statement Description","stmtDesc","inputalph","","PSSTDS",$row[PSSTDS],$Err_PSSTDS,"10","10","","","");

	if ($CPCFOV=="Y") {Build_Fld_Entry("Allow Company/Facility Override","allowCoFacOvr","inputalph","YORN","PSCFOV",$row[PSCFOV],$Err_PSCFOV,"1","1","","","");}
	if ($CPACOV=="Y") {Build_Fld_Entry("Allow Account Override","allowAcctOvr","inputalph","YORN","PSACOV",$row[PSACOV],$Err_PSACOV,"1","1","","","");}

	if ($CPCSAC=="Y") {
		$row['PSCSAC']=Default_Zero($row['PSCSAC']);  $row['PSCSSB']=Default_Zero($row['PSCSSB']);
		$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[PSCSAC],$row[PSCSSB])", "HDCHRT", "CHCHDS");
		$textOvr=SetTextOvr($Err_PSCSAC);
		print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Cash Account</span></td> ";
		print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"cashAcct\" value=\"" . trim($row['PSCSAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
		print "\n                                 <input type=\"text\" name=\"cashSubacct\" value=\"" . trim($row['PSCSSB']) . "\" size=\"4\" maxlength=\"4\"> ";
		print "\n                                 <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=cashAcct&amp;subFld=cashSubacct&amp;descFld=cashAcctDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
		print "\n                                 <span class=\"dspdesc\" id=\"cashAcctDesc\">$fieldDesc</span></td> ";
		print "\n     </tr> ";
		DspErrMsg($Err_PSCSAC);
	}

	if ($CPARAC=="Y") {
		$row['PSARAC']=Default_Zero($row['PSARAC']);  $row['PSARSB']=Default_Zero($row['PSARSB']);
		$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[PSARAC],=$row[PSARSB])", "HDCHRT", "CHCHDS");
		$textOvr=SetTextOvr($Err_PSARAC);
		print "\n     <tr><td class=\"dsphdr\"><span $textOvr>A/R Account</span></td> ";
		print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"arAcct\" value=\"" . trim($row['PSARAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
		print "\n                                 <input type=\"text\" name=\"arSubacct\" value=\"" . trim($row['PSARSB']) . "\" size=\"4\" maxlength=\"4\"> ";
		print "\n                                 <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=arAcct&amp;subFld=arSubacct&amp;descFld=arAcctDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
		print "\n                                 <span class=\"dspdesc\" id=\"arAcctDesc\">$fieldDesc</span></td> ";
		print "\n     </tr> ";
		DspErrMsg($Err_PSARAC);
	}

	if ($CPOFAC=="Y") {
		$row['PSOFCO']=Default_Zero($row['PSOFCO']);  $row['PSOFFC']=Default_Zero($row['PSOFFC']);
		$fieldDesc=RetValue("(CFCO#,CFFAC#)=($row[PSOFCO],$row[PSOFFC])", "HDCFAC", "CFCFNM");
		$textOvr=SetTextOvr($Err_PSOFCO);
		print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Offset Company/Facility</span></td> ";
		print "\n         <td class=\"inputnmbr\"><input name=\"offCo\" value=\"" . trim($row['PSOFCO']) . "\" type=\"text\" size=\"4\" maxlength=\"2\"> / ";
		print "\n                                 <input name=\"offFac\" value=\"" . trim($row['PSOFFC']) . "\" type=\"text\" size=\"4\" maxlength=\"4\"> ";
		print "\n                                 <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=offCo&amp;fldFac=offFac&amp;fldDesc=coFacDesc\" onclick=\"$searchWinVar\"> $searchImage </a> ";
		print "\n                                 <span class=\"dspdesc\" id=\"coFacDesc\">$fieldDesc</span></td> ";
		print "\n     </tr> ";
		DspErrMsg($Err_PSOFCO);

		$row['PSOFAC']=Default_Zero($row['PSOFAC']);  $row['PSOFSB']=Default_Zero($row['PSOFSB']);
		$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[PSOFAC],$row[PSOFSB])", "HDCHRT", "CHCHDS");
		$textOvr=SetTextOvr($Err_PSOFAC);
		print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Offset Account</span></td> ";
		print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"offAcct\" value=\"" . trim($row['PSOFAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
		print "\n                                 <input type=\"text\" name=\"offSubacct\" value=\"" . trim($row['PSOFSB']) . "\" size=\"4\" maxlength=\"4\"> ";
		print "\n                                 <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=offAcct&amp;subFld=offSubacct&amp;descFld=offAcctDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
		print "\n                                 <span class=\"dspdesc\" id=\"offAcctDesc\">$fieldDesc</span></td> ";
		print "\n     </tr> ";
		DspErrMsg($Err_PSOFAC);
	}

	Build_Fld_Entry("Date Deactivated","dateDeactivated","inputdate","Date","PSDTDE",$row[PSDTDE],$Err_PSDTDE,"6","6","","","");
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
	if ($maintenanceCode=="D" && is_null($_POST['pmtSubCode'])) {
		$_POST['pmtSubCode']    =$pmtSubCode;
		$_POST['pmtSubCodeDesc']=RetValue("PSSBCD='$_POST[pmtSubCode]'", "ARPYSB", "PSDESC");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	Concat_Field("@@sbcd", strtoupper($_POST['pmtSubCode']));
	Concat_Field("@@desc", $_POST['pmtSubCodeDesc']);
	Concat_Field("@@pycd", strtoupper($_POST['paymentCode']));
	if ($CPCFOV=="Y") {if (!isset($_POST['allowCoFacOvr'])) {$_POST['allowCoFacOvr']="N";} Concat_Field("@@cfov", strtoupper($_POST['allowCoFacOvr']));}
	if ($CPACOV=="Y") {if (!isset($_POST['allowAcctOvr'])) {$_POST['allowAcctOvr']="N";} Concat_Field("@@acov", strtoupper($_POST['allowAcctOvr']));}
	Concat_Field("@@stds", $_POST['stmtDesc']);
	if ($CPCSAC=="Y") {
		Concat_Field("@@csac", $_POST['cashAcct']);
		Concat_Field("@@cssb", $_POST['cashSubacct']);
	}
	if ($CPARAC=="Y") {
		Concat_Field("@@arac", $_POST['arAcct']);
		Concat_Field("@@arsb", $_POST['arSubacct']);
	}
	if ($CPOFAC=="Y") {
		Concat_Field("@@ofco", $_POST['offCo']);
		Concat_Field("@@offc", $_POST['offFac']);
		Concat_Field("@@ofac", $_POST['offAcct']);
		Concat_Field("@@ofsb", $_POST['offSubacct']);
	}
	Concat_Field("@@dtde", $_POST['dateDeactivated']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARPSM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		if ($_POST['pmtSubCodeDesc'] == "") {$_POST['pmtSubCodeDesc']=Decat_Field("@@desc", $edtVar);}
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST['pmtSubCodeDesc'], $_POST['pmtSubCode'], "" , "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;fromPmtCode=" . urlencode(trim($fromPmtCode)) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif ($maintenanceCode == "D") {
		$Err_PSSBCD=DecatErr_Field("@@sbcd", "pmtSubCode");
		$confMessage=Format_ConfMsg_Desc("", $_POST['pmtSubCodeDesc'], $_POST['pmtSubCode'], "<br>$Err_PSSBCD", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;fromPmtCode=" . urlencode(trim($fromPmtCode)) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;pmtSubCode=" . urlencode(trim($_POST['pmtSubCode'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>
