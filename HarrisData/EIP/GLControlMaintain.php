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

$page_title     = "G/L Control Maintenance";
$scriptName     = "GLControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HGLCTU_E";

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
	print "\n if (document.Chg.reclaimResourceLev.value ==\"\" ";
	print "\n  || document.Chg.varCalcCode.value ==\"\" ";
	print "\n  || document.Chg.curAcctPeriod.value ==\"\" ";
	print "\n  || document.Chg.bplAcctNumber.value ==\"\" ";
	print "\n  || document.Chg.retEarnAcctNumber.value ==\"\" ";
	print "\n  || document.Chg.qtrOneEndPeriod.value ==\"\" ";
	print "\n  || document.Chg.qtrTwoEndPeriod.value ==\"\" ";
	print "\n  || document.Chg.qtrThreeEndPeriod.value ==\"\" ";
	print "\n  || document.Chg.qtrFourEndPeriod.value ==\"\" ";
	if ($HDMCRL>0)  {
		print "\n  || document.getElementById('procMultiCurr').checked && document.Chg.glCurRateType.value ==\"\" ";
		print "\n  || document.getElementById('procMultiCurr').checked && document.Chg.budgetCurRateType.value ==\"\" ";
	}
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.curAcctPeriod, 4, 0) && ";
	print "\n     editNum(document.Chg.bplAcctNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.retEarnAcctNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.retEarnSubNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.qtrOneEndPeriod, 2, 0) && ";
	print "\n     editNum(document.Chg.qtrTwoEndPeriod, 2, 0) && ";
	print "\n     editNum(document.Chg.qtrThreeEndPeriod, 2, 0) && ";
	print "\n     editNum(document.Chg.qtrFourEndPeriod, 2, 0)) ";
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
	$pageID = "GLCONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From GLCTRL ";
		$stmtSQL .= " Where RRN(GLCTRL)=1 ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hglctu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hglctu_OPT['sec_01'];
	$sec_02=$hglctu_OPT['sec_02'];
	$sec_03="N";
	$sec_04="N";
	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#general\">General</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#accounting\">Accounting</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#transaction\">Transaction</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#allocation\">Allocation</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#quarter\">Quarter</a></td> ";
	if ($HDMCRL>"0") {print "\n <td class=\"quickLinkTabs\"><a href=\"#multiCurrency\">Multi-Currency</a></td> ";}
	print "\n   </tr> ";
	print "\n </table> ";

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
			$Err_CTRCLR=DecatErr_Field("@@rclr", "reclaimResourceLev");
			$Err_CTNAB =DecatErr_Field("@@nab@", "normAcctBal");
			$Err_CTCALV=DecatErr_Field("@@calv", "varCalcCode");
			$Err_CTGLCP=DecatErr_Field("@@glcp", "curAcctPeriod");
			$Err_CTBPLA=DecatErr_Field("@@bpla", "bplAcctNumber");
			$Err_CTRTEA=DecatErr_Field("@@rtea", "retEarnAcctNumber");
			$Err_CTRTES=DecatErr_Field("@@rtes", "retEarnSubNumber");
			$Err_CTCCLO=DecatErr_Field("@@cclo", "closeByCo");
			$Err_CTACJ =DecatErr_Field("@@acj@", "allowAcrossCoTrns");
			$Err_CTAFJ =DecatErr_Field("@@afj@", "allowAcrossFacTrns");
			$Err_CTGIBE=DecatErr_Field("@@gibe", "genIntCoBalTrans");
			$Err_CTALAL=DecatErr_Field("@@alal", "allowAcctAlloc");
			$Err_CTACAL=DecatErr_Field("@@acal", "allowAcrossCoAlloc");
			$Err_CTAFAL=DecatErr_Field("@@afal", "allowAcrossFacAlloc");
			$Err_CTQ1EP=DecatErr_Field("@@q1ep", "qtrOneEndPeriod");
			$Err_CTQ2EP=DecatErr_Field("@@q2ep", "qtrTwoEndPeriod");
			$Err_CTQ3EP=DecatErr_Field("@@q3ep", "qtrThreeEndPeriod");
			$Err_CTQ4EP=DecatErr_Field("@@q4ep", "qtrFourEndPeriod");
			if ($HDMCRL>0)  {
				$Err_CTPRMC=DecatErr_Field("@@prmc", "procMultiCurr");
				$Err_CTMCRT=DecatErr_Field("@@mcrt", "glCurRateType");
				$Err_CTBCRT=DecatErr_Field("@@bcrt", "budgetCurRateType");
			}
		}

		$row['CTRCLR']=Decat_Field("@@rclr", $edtVar);
		$row['CTNAB'] =Decat_Field("@@nab@", $edtVar);
		$row['CTCALV']=Decat_Field("@@calv", $edtVar);
		$row['CTGLCP']=Decat_Field("@@glcp", $edtVar);
		$row['CTBPLA']=Decat_Field("@@bpla", $edtVar);
		$row['CTRTEA']=Decat_Field("@@rtea", $edtVar);
		$row['CTRTES']=Decat_Field("@@rtes", $edtVar);
		$row['CTCCLO']=Decat_Field("@@cclo", $edtVar);
		$row['CTACJ'] =Decat_Field("@@acj@", $edtVar);
		$row['CTAFJ'] =Decat_Field("@@afj@", $edtVar);
		$row['CTGIBE']=Decat_Field("@@gibe", $edtVar);
		$row['CTALAL']=Decat_Field("@@alal", $edtVar);
		$row['CTACAL']=Decat_Field("@@acal", $edtVar);
		$row['CTAFAL']=Decat_Field("@@afal", $edtVar);
		$row['CTQ1EP']=Decat_Field("@@q1ep", $edtVar);
		$row['CTQ2EP']=Decat_Field("@@q2ep", $edtVar);
		$row['CTQ3EP']=Decat_Field("@@q3ep", $edtVar);
		$row['CTQ4EP']=Decat_Field("@@q4ep", $edtVar);
		if ($HDMCRL>0)  {
			$row['CTPRMC']=Decat_Field("@@prmc", $edtVar);
			$row['CTMCRT']=Decat_Field("@@mcrt", $edtVar);
			$row['CTBCRT']=Decat_Field("@@bcrt", $edtVar);
		}
	} else {
		$row['CTGLCP']=PeriodInputFromCYP($row['CTGLCP']);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	Build_DspFld("G/L Release Version",$HDGLRL,"","A");
	Build_DspFld("G/L Library Level",$HDGLLL,"","A");
	print "\n </table> ";

	print "\n <a name=\"general\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">General</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	Build_Fld_Entry("Reclaim Resource Level","reclaimResourceLev","inputalph","RECLAIMLVL","CTRCLR",$row[CTRCLR],$Err_CTRCLR,"1","1","Y","","");
	Build_Fld_Entry("Normal Account Balances","normAcctBal","inputalph","YORN","CTNAB",$row[CTNAB],$Err_CTNAB,"1","1","Y","","");
	Build_Fld_Entry("Variance Calculation Code","varCalcCode","inputalph","GLVARCALC","CTCALV",$row[CTCALV],$Err_CTCALV,"1","1","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"accounting\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Accounting</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_CTGLCP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Current Accounting Period</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"curAcctPeriod\" value=\"" . rtrim($row['CTGLCP']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;periodFld=curAcctPeriod\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_CTGLCP);

	$F_CTLPDC=Format_Date($row[CTLPDC], "H");
	$weekday = date('l', strtotime(Date_CYMD_ISO($row['CTLPDC'])));
	print "\n <tr><td class=\"dsphdr\">Last Period Close Date</td>";
	print "\n     <td class=\"dspalph\">$weekday $F_CTLPDC</td></tr>";

	Build_Fld_Entry("Beginning P & L Account Number","bplAcctNumber","inputnmbr","","CTBPLA",$row[CTBPLA],$Err_CTBPLA,"4","4","Y","","");

	$row['CTRTEA']=Default_Zero($row['CTRTEA']);
	$row['CTRTES']=Default_Zero($row['CTRTES']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CTRTEA],$row[CTRTES])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CTRTEA);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Retained Earnings Account</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"retEarnAcctNumber\" value=\"" . rtrim($row['CTRTEA']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"retEarnSubNumber\" value=\"" . rtrim($row['CTRTES']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=retEarnAcctNumber&amp;subFld=retEarnSubNumber&amp;descFld=retEarnAcctNumberDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"retEarnAcctNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CTRTEA);

	Build_Fld_Entry("Close By Company","closeByCo","inputalph","YORN","CTCCLO",$row[CTCCLO],$Err_CTCCLO,"1","1","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"transaction\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Transaction</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Allow Across Company Transactions","allowAcrossCoTrns","inputalph","YORN","CTACJ",$row[CTACJ],$Err_CTACJ,"1","1","Y","","");
	Build_Fld_Entry("Allow Across Facility Transactions","allowAcrossFacTrns","inputalph","YORN","CTAFJ",$row[CTAFJ],$Err_CTAFJ,"1","1","Y","","");
	Build_Fld_Entry("Generate Inter-Co Balancing Transactions","genIntCoBalTrans","inputalph","YORN","CTGIBE",$row[CTGIBE],$Err_CTGIBE,"1","1","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"allocation\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Allocation</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Allow Account Allocations","allowAcctAlloc","inputalph","YORN","CTALAL",$row[CTALAL],$Err_CTALAL,"1","1","Y","","");
	Build_Fld_Entry("Allow Across Company Allocations","allowAcrossCoAlloc","inputalph","YORN","CTACAL",$row[CTACAL],$Err_CTACAL,"1","1","Y","","");
	Build_Fld_Entry("Allow Across Facility Allocations","allowAcrossFacAlloc","inputalph","YORN","CTAFAL",$row[CTAFAL],$Err_CTAFAL,"1","1","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"quarter\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Quarter</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Quarter One Ending Period","qtrOneEndPeriod","inputnmbr","","CTQ1EP",$row[CTQ1EP],$Err_CTQ1EP,"2","2","Y","","");
	Build_Fld_Entry("Quarter Two Ending Period","qtrTwoEndPeriod","inputnmbr","","CTQ2EP",$row[CTQ2EP],$Err_CTQ2EP,"2","2","Y","","");
	Build_Fld_Entry("Quarter Three Ending Period","qtrThreeEndPeriod","inputnmbr","","CTQ3EP",$row[CTQ3EP],$Err_CTQ3EP,"2","2","Y","","");
	Build_Fld_Entry("Quarter Four Ending Period","qtrFourEndPeriod","inputnmbr","","CTQ4EP",$row[CTQ4EP],$Err_CTQ4EP,"2","2","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	// Multi-Currency Processing
	if ($HDMCRL>0)  {
		print "\n  <a name=\"multiCurrency\"></a> ";
		print "\n  <fieldset class=\"legendBody\"> ";
		print "\n  <legend class=\"legendTitle\">Multi-Currency</legend> ";
		require 'TopOfForm.php';
		print "\n <table $contentTable>";
		Build_Fld_Entry("Process Multi-Currency","procMultiCurr","inputalph","YORN","CTPRMC",$row[CTPRMC],$Err_CTPRMC,"1","1","Y","","");

		$fieldDesc=RetValue("RYTYPE='$row[CTMCRT]'", "HDRTYP", "RYDESC");
		$textOvr=SetTextOvr($Err_CTMCRT);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>G/L Currency Rate Type</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"glCurRateType\" value=\"" . rtrim($row['CTMCRT']) . "\" size=\"10\" maxlength=\"10\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyRateTypeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=glCurRateType&amp;fldDesc=glCurRateTypeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"glCurRateTypeDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_CTMCRT);

		$fieldDesc=RetValue("RYTYPE='$row[CTBCRT]'", "HDRTYP", "RYDESC");
		$textOvr=SetTextOvr($Err_CTBCRT);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Budget Currency Rate Type</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"budgetCurRateType\" value=\"" . rtrim($row['CTBCRT']) . "\" size=\"10\" maxlength=\"10\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyRateTypeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=budgetCurRateType&amp;fldDesc=budgetCurRateTypeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"budgetCurRateTypeDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_CTBCRT);
		print "\n </table> ";
		print "\n </fieldset> ";
	}

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
	if (!isset($_POST['normAcctBal'])) {$_POST['normAcctBal']="N";} Concat_Field("@@nab@", $_POST['normAcctBal']);
	Concat_Field("@@calv", $_POST['varCalcCode']);
	Concat_Field("@@glcp", $_POST['curAcctPeriod']);
	Concat_Field("@@bpla", $_POST['bplAcctNumber']);
	Concat_Field("@@rtea", $_POST['retEarnAcctNumber']);
	Concat_Field("@@rtes", $_POST['retEarnSubNumber']);
	if (!isset($_POST['closeByCo']))          {$_POST['closeByCo']="N";}           Concat_Field("@@cclo", $_POST['closeByCo']);
	if (!isset($_POST['allowAcrossCoTrns']))  {$_POST['allowAcrossCoTrns']="N";}   Concat_Field("@@acj@", $_POST['allowAcrossCoTrns']);
	if (!isset($_POST['allowAcrossFacTrns'])) {$_POST['allowAcrossFacTrns']="N";}  Concat_Field("@@afj@", $_POST['allowAcrossFacTrns']);
	if (!isset($_POST['genIntCoBalTrans']))   {$_POST['genIntCoBalTrans']="N";}    Concat_Field("@@gibe", $_POST['genIntCoBalTrans']);
	if (!isset($_POST['allowAcctAlloc']))     {$_POST['allowAcctAlloc']="N";}      Concat_Field("@@alal", $_POST['allowAcctAlloc']);
	if (!isset($_POST['allowAcrossCoAlloc'])) {$_POST['allowAcrossCoAlloc']="N";}  Concat_Field("@@acal", $_POST['allowAcrossCoAlloc']);
	if (!isset($_POST['allowAcrossFacAlloc'])){$_POST['allowAcrossFacAlloc']="N";} Concat_Field("@@afal", $_POST['allowAcrossFacAlloc']);
	Concat_Field("@@q1ep", $_POST['qtrOneEndPeriod']);
	Concat_Field("@@q2ep", $_POST['qtrTwoEndPeriod']);
	Concat_Field("@@q3ep", $_POST['qtrThreeEndPeriod']);
	Concat_Field("@@q4ep", $_POST['qtrFourEndPeriod']);
	if ($HDMCRL>0)  {
		if (!isset($_POST['procMultiCurr'])) {$_POST['procMultiCurr']="N";} Concat_Field("@@prmc", $_POST['procMultiCurr']);
		Concat_Field("@@mcrt", $_POST['glCurRateType']=strtoupper($_POST['glCurRateType']));
		Concat_Field("@@bcrt", $_POST['budgetCurRateType']=strtoupper($_POST['budgetCurRateType']));
	}
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HGLCTU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "", "", "", "", "", "");
		$fileName="GLControl{$dataBaseID}.php";
		$includeName= "{$homePath}{$fileName}";
		Write_Control_File($homePath, $fileName, "HGLCTL_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>