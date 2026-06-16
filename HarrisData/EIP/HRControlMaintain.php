<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$backHome           = $_GET['backHome'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "H/R Control Maintenance";
$scriptName     = "HRControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPECTU_E";

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
	require_once 'CheckEnterChg.php';
	require_once 'CalendarInclude.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.reclaimResourceLev.value ==\"\" || ";
	print "\n     document.Chg.printMaintAudits.value ==\"\" || ";
	print "\n     document.Chg.printEntryAudits.value ==\"\" || ";
	print "\n     document.Chg.stmtCode.value ==\"\" || ";
	print "\n     document.Chg.frqOfPayment.value ==\"\" || ";
	print "\n     document.Chg.nameOfPlanAdm.value ==\"\" || ";
	print "\n     document.Chg.manualPrtGenNotice.value ==\"\" || ";
	print "\n     document.Chg.manualPrtContuaNotice.value ==\"\" || ";
	print "\n     document.Chg.manualPrtConverNotice.value ==\"\" || ";
	print "\n     document.Chg.autoAsgnEmpNum.value ==\"\" ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.futTransDateLimit, 3, 0) && ";
	print "\n     editNum(document.Chg.genNoticeEffDate, 6, 0) && ";
	print "\n     editNum(document.Chg.miscDate1, 6, 0) && ";
	print "\n     editNum(document.Chg.miscAmt1, 5, 2) && ";
	print "\n     editNum(document.Chg.termLetterNum, 4, 0) && ";
	print "\n     editNum(document.Chg.miscDate2, 6, 0) && ";
	print "\n     editNum(document.Chg.miscAmt2, 5, 2)) ";
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
	$pageID = "HRCONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = " Select * From PECTRL Where RRN(PECTRL)=1";
	require 'stmtSQLEnd.php';

	print "\n <a NAME=\"top\"></a> ";
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
			$Err_PERCLR=DecatErr_Field("@@rclr", "reclaimResourceLev");
			$Err_PEMNTA=DecatErr_Field("@@mnta", "printMaintAudits");
			$Err_PEENTA=DecatErr_Field("@@enta", "printEntryAudits");
			$Err_PEFTDL=DecatErr_Field("@@ftdl", "futTransDateLimit");
			$Err_PETRMC=DecatErr_Field("@@trmc", "termCode");
			$Err_PETRCT=DecatErr_Field("@@trct", "termLetterNum");
			$Err_PESTCD=DecatErr_Field("@@stcd", "stmtCode");
			$Err_PEPFRQ=DecatErr_Field("@@pfrq", "frqOfPayment");
			$Err_PEGNDT=DecatErr_Field("@@gndt", "genNoticeEffDate");
			$Err_PEPADM=DecatErr_Field("@@padm", "nameOfPlanAdm");
			$Err_PEMNGN=DecatErr_Field("@@mngn", "manualPrtGenNotice");
			$Err_PEMNCN=DecatErr_Field("@@mncn", "manualPrtContuaNotice");
			$Err_PEMNVN=DecatErr_Field("@@mnvn", "manualPrtConverNotice");
			$Err_PEAAEN=DecatErr_Field("@@aaen", "autoAsgnEmpNum");
			$Err_PEMSD1=DecatErr_Field("@@msd1", "miscDate1");
			$Err_PEMSA1=DecatErr_Field("@@msa1", "miscAmt1");
			$Err_PEMSD2=DecatErr_Field("@@msd2", "miscDate2");
			$Err_PEMSA2=DecatErr_Field("@@msa2", "miscAmt2");
			$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");
		}
		$row['PERCLR']=Decat_Field("@@rclr", $edtVar);
		$row['PEMNTA']=Decat_Field("@@mnta", $edtVar);
		$row['PEENTA']=Decat_Field("@@enta", $edtVar);
		$row['PEFTDL']=Decat_Field("@@ftdl", $edtVar);
		$row['PETRMC']=Decat_Field("@@trmc", $edtVar);
		$row['PETRCT']=Decat_Field("@@trct", $edtVar);
		$row['PESTCD']=Decat_Field("@@stcd", $edtVar);
		$row['PEPFRQ']=Decat_Field("@@pfrq", $edtVar);
		$row['PEGNDT']=Decat_Field("@@gndt", $edtVar);
		$row['PEPADM']=Decat_Field("@@padm", $edtVar);
		$row['PEMNGN']=Decat_Field("@@mngn", $edtVar);
		$row['PEMNCN']=Decat_Field("@@mncn", $edtVar);
		$row['PEMNVN']=Decat_Field("@@mnvn", $edtVar);
		$row['PEAAEN']=Decat_Field("@@aaen", $edtVar);
		$row['PEMSD1']=Decat_Field("@@msd1", $edtVar);
		$row['PEMSA1']=Decat_Field("@@msa1", $edtVar);
		$row['PEMSD2']=Decat_Field("@@msd2", $edtVar);
		$row['PEMSA2']=Decat_Field("@@msa2", $edtVar);
		$row['PETSTP']=Decat_Field("@@tstp", $edtVar);

	} else {
		$row['PEGNDT']=DateInputFromCYMD($row['PEGNDT']);
		$row['PEMSD1']=DateInputFromCYMD($row['PEMSD1']);
		$row['PEMSD2']=DateInputFromCYMD($row['PEMSD2']);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_TSTP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>.</span></td> ";
	print "\n     <td><input type=\"hidden\" name=\"timeStamp\" value=\"" . rtrim($row['PETSTP']) . "\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_TSTP);
	Build_DspFld("H/R Release Version",$HDPERL,"","A");
	Build_DspFld("H/R Library Level",$HDPELL,"","A");
	Build_Fld_Entry("Reclaim Resource Level","reclaimResourceLev","inputalph","RECLAIMLVL","CPRCLR",$row[PERCLR],$Err_PERCLR,"1","1","Y","","");
	Build_Fld_Entry("Auto Assign Employee Number","autoAsgnEmpNum","inputalph","EMPNBRASGN","PEAAEN",$row[PEAAEN],$Err_PEAAEN,"1","1","Y","","");
	Build_Fld_Entry("Print Maintenance Audits","printMaintAudits","inputalph","YORN","PEMNTA",$row[PEMNTA],$Err_PEMNTA,"1","1","Y","","");
	Build_Fld_Entry("Print Entry Audits","printEntryAudits","inputalph","YORN","PEENTA",$row[PEENTA],$Err_PEENTA,"1","1","Y","","");
	print "\n </table> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">COBRA Defaults</legend> ";
	print "\n <table $contentTable>";
	Build_Fld_Entry("Future Transaction Date Limit","futTransDateLimit","inputnmbr","","PEFTDL",$row[PEFTDL],$Err_PEFTDL,"1","3","Y","","");

	$fieldDesc=RetValue("CCCODE='$row[PETRMC]'", "CBCODE", "CCDESC");
	$textOvr=SetTextOvr($Err_PETRMC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Termination Code</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"termCode\" value=\"" . rtrim($row['PETRMC']) . "\" size=\"1\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CobraCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=termCode&amp;fldDesc=termCodeDesc&amp;forType=T\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"termCodeDesc\">" . trim($fieldDesc) . "</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_PETRMC);

	Build_Fld_Entry("Termination Letter Number","termLetterNum","inputnmbr","","PETRCT",$row[PETRCT],$Err_PETRCT,"1","4","","","");
	Build_Fld_Entry("Statement Code","stmtCode","inputalph","COBRASTCO","PESTCD",$row[PESTCD],$Err_PESTCD,"1","1","Y","","");
	Build_Fld_Entry("Frequency Of Payment","frqOfPayment","inputalph","COBRAPYFRQ","PEPFRQ",$row[PEPFRQ],$Err_PEPFRQ,"1","1","Y","","");
	Build_Fld_Entry("General Notice Effective Date","genNoticeEffDate","inputdate","Date",PEGNDT,$row[PEGNDT],$Err_PEGNDT,"","","Y","","");
	Build_Fld_Entry("Name Of Plan Administrator","nameOfPlanAdm","inputalph","","PEPADM",$row[PEPADM],$Err_PEPADM,"23","23","Y","","");
	Build_Fld_Entry("Manual Print General Notice","manualPrtGenNotice","inputalph","YORN","PEMNGN",$row[PEMNGN],$Err_PEMNGN,"1","1","Y","","");
	Build_Fld_Entry("Manual Print Continuation Notice","manualPrtContuaNotice","inputalph","YORN","PEMNCN",$row[PEMNCN],$Err_PEMNCN,"1","1","Y","","");
	Build_Fld_Entry("Manual Print Conversion Notice","manualPrtConverNotice","inputalph","YORN","PEMNVN",$row[PEMNVN],$Err_PEMNVN,"1","1","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Miscellaneous</legend> ";
	print "\n <table $contentTable>";

	print "\n <tr><td>&nbsp;</td><td class=\"colhdr\">Date</td><td class=\"colhdr\" colspan=\"2\">Amount</td></tr> ";

	$textOvr=SetTextOvr($Err_PEMSD1);
	$textOvr=SetTextOvr($Err_PEMSA1);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Miscellaneous 1</span></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"miscDate1\" value=\"" . rtrim($row['PEMSD1']) . "\"  size=\"6\" maxlength=\"6\">";
	print "\n  <a href=\"javascript:calWindow('miscDate1');\">$calendarImage &nbsp;</a></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"miscAmt1\" value=\"" . rtrim($row['PEMSA1']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PEMSD1);
	DspErrMsg($Err_PEMSA1);

	$textOvr=SetTextOvr($Err_PEMSD2);
	$textOvr=SetTextOvr($Err_PEMSA2);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Miscellaneous 2</span></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"miscDate2\" value=\"" . rtrim($row['PEMSD2']) . "\"  size=\"6\" maxlength=\"6\">";
	print "\n  <a href=\"javascript:calWindow('miscDate2');\"> $calendarImage &nbsp;</a></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"miscAmt2\" value=\"" . rtrim($row['PEMSA2']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PEMSD2);
	DspErrMsg($Err_PEMSA2);

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
	$edtVar= "";
	Concat_Field("@@rclr", $_POST['reclaimResourceLev']=strtoupper($_POST['reclaimResourceLev']));
	if (!isset($_POST['printMaintAudits'])) {$_POST['printMaintAudits']="N";} Concat_Field("@@mnta", $_POST['printMaintAudits']);
	if (!isset($_POST['printEntryAudits'])) {$_POST['printEntryAudits']="N";} Concat_Field("@@enta", $_POST['printEntryAudits']);
	Concat_Field("@@ftdl", $_POST['futTransDateLimit']);
	Concat_Field("@@trmc", $_POST['termCode']=strtoupper($_POST['termCode']));
	Concat_Field("@@trct", $_POST['termLetterNum']);
	Concat_Field("@@stcd", $_POST['stmtCode']=strtoupper($_POST['stmtCode']));
	Concat_Field("@@pfrq", $_POST['frqOfPayment']=strtoupper($_POST['frqOfPayment']));
	Concat_Field("@@gndt", $_POST['genNoticeEffDate']);
	Concat_Field("@@padm", $_POST['nameOfPlanAdm']=strtoupper($_POST['nameOfPlanAdm']));
	if (!isset($_POST['manualPrtGenNotice'])) {$_POST['manualPrtGenNotice']="N";} Concat_Field("@@mngn", $_POST['manualPrtGenNotice']);
	if (!isset($_POST['manualPrtContuaNotice'])) {$_POST['manualPrtContuaNotice']="N";} Concat_Field("@@mncn", $_POST['manualPrtContuaNotice']);
	if (!isset($_POST['manualPrtConverNotice'])) {$_POST['manualPrtConverNotice']="N";} Concat_Field("@@mnvn", $_POST['manualPrtConverNotice']);
	Concat_Field("@@aaen", $_POST['autoAsgnEmpNum']);
	Concat_Field("@@msd1", $_POST['miscDate1']);
	Concat_Field("@@msa1", $_POST['miscAmt1']);
	Concat_Field("@@msd2", $_POST['miscDate2']);
	Concat_Field("@@msa2", $_POST['miscAmt2']);
	Concat_Field("@@tstp", $_POST['timeStamp']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPECTU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc("C", "H/R Control", "", "", "", "", "");
		$includeName= "{$homePath}PEControl{$dataBaseID}.php";
		$fileName="PEControl{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HPECTL_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>