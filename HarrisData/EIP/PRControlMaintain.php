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

$page_title     = "P/R Control Maintenance";
$scriptName     = "PRControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPRCTU";

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
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	print "\n function validate(chgForm) {";
	print "\n     if (document.Chg.payEntryFormat.value ==\"\" || ";
	print "\n         document.Chg.reclaimResourceLevel.value ==\"\" || ";
	print "\n         document.Chg.autoAssign.value ==\"\") ";
	print "\n         {alert(\"$reqFieldError\"); return false;} ";

	print "\n     if (editNum(document.Chg.grossPct, 2, 3) && ";
	print "\n         editNum(document.Chg.vendorFICA, 7, 0) && ";
	print "\n         editNum(document.Chg.vendorFUI, 7, 0) && ";
	print "\n         editNum(document.Chg.vendorSIT, 7, 0) && ";
	print "\n         editNum(document.Chg.vendorSUI, 7, 0) && ";
	print "\n         editNum(document.Chg.vendorSDI, 7, 0) && ";
	print "\n         editNum(document.Chg.vendorSWC, 7, 0) && ";
	print "\n         editNum(document.Chg.vendorLIT, 7, 0)) ";
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
	$pageID = "PRCONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = " Select * From PRCTRL Where RRN(PRCTRL)=1";
	require 'stmtSQLEnd.php';

	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#general\">General</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#interCo\">Inter-Co/Dept</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#userDefined\">User Defined</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#taxLiab\">Tax Liability Defaults</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField= "reclaimResourceLevel";
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_PRTSTP=DecatErr_Field("@@tstp", "origTimestamp");
			$Err_PRCKPG=DecatErr_Field("@@ckpg", "checkPrintProg");
			$Err_PRTETM=DecatErr_Field("@@tetm", "payEntryFormat");
			$Err_PRMNTA=DecatErr_Field("@@mnta", "prtMaintAudit");
			$Err_PRENTA=DecatErr_Field("@@enta", "prtEntryAudit");
			$Err_PRDPER=DecatErr_Field("@@dper", "distPeriod");
			$Err_PRRCLR=DecatErr_Field("@@rclr", "reclaimResourceLevel");
			$Err_PRDDAV=DecatErr_Field("@@ddav", "dirDepAdvice");
			$Err_PRMYHS=DecatErr_Field("@@myhs", "retMultYrsHist");
			$Err_PRICWG=DecatErr_Field("@@icwg", "interCoWages");
			$Err_PRICFI=DecatErr_Field("@@icfi", "interCoFICA");
			$Err_PRICFU=DecatErr_Field("@@icfu", "interCoFUI");
			$Err_PRICSU=DecatErr_Field("@@icsu", "interCoSUI");
			$Err_PRICSD=DecatErr_Field("@@icsd", "interCoSDI");
			$Err_PRICSW=DecatErr_Field("@@icsw", "interCoSWC");
			$Err_PRICLI=DecatErr_Field("@@icli", "interCoLIT");
			$Err_PRLB  =DecatErr_Field("@@lb@@", "consRptDB");
			$Err_PRGPAP=DecatErr_Field("@@gpap", "grossPct");
			$Err_PRAAEN=DecatErr_Field("@@aaen", "autoAssign");
			$Err_PRU1DS=DecatErr_Field("@@u1ds", "userDefNumDsc1");
			$Err_PRU1HD=DecatErr_Field("@@u1hd", "userDefNumHdg1");
			$Err_PRU2DS=DecatErr_Field("@@u2ds", "userDefNumDsc2");
			$Err_PRU2HD=DecatErr_Field("@@u2hd", "userDefNumHdg2");
			$Err_PRU3DS=DecatErr_Field("@@u3ds", "userDefNumDsc3");
			$Err_PRU3HD=DecatErr_Field("@@u3hd", "userDefNumHdg3");
			$Err_PRU4DS=DecatErr_Field("@@u4ds", "userDefAlpDsc4");
			$Err_PRU4HD=DecatErr_Field("@@u4hd", "userDefAlpHdg4");
			$Err_PRU5DS=DecatErr_Field("@@u5ds", "userDefAlpDsc5");
			$Err_PRU5HD=DecatErr_Field("@@u5hd", "userDefAlpHdg5");
			$Err_PRU6DS=DecatErr_Field("@@u6ds", "userDefAlpDsc6");
			$Err_PRU6HD=DecatErr_Field("@@u6hd", "userDefAlpHdg6");
			$Err_PRXFIT=DecatErr_Field("@@xfit", "expFICA");
			$Err_PRXFUI=DecatErr_Field("@@xfui", "expFUI");
			$Err_PRXSIT=DecatErr_Field("@@xsit", "expSIT");
			$Err_PRXSUI=DecatErr_Field("@@xsui", "expSUI");
			$Err_PRXSDI=DecatErr_Field("@@xsdi", "expSDI");
			$Err_PRXSWC=DecatErr_Field("@@xswc", "expSWC");
			$Err_PRXLIT=DecatErr_Field("@@xlit", "expLIT");
			$Err_PRAFIT=DecatErr_Field("@@afit", "autoSelFICA");
			$Err_PRAFUI=DecatErr_Field("@@afui", "autoSelFUI");
			$Err_PRASIT=DecatErr_Field("@@asit", "autoSelSIT");
			$Err_PRASUI=DecatErr_Field("@@asui", "autoSelSUI");
			$Err_PRASDI=DecatErr_Field("@@asdi", "autoSelSDI");
			$Err_PRASWC=DecatErr_Field("@@aswc", "autoSelSWC");
			$Err_PRALIT=DecatErr_Field("@@alit", "autoSelLIT");
			$Err_PRVFIT=DecatErr_Field("@@vfit", "vendorFICA");
			$Err_PRVFUI=DecatErr_Field("@@vfui", "vendorFUI");
			$Err_PRVSIT=DecatErr_Field("@@vsit", "vendorSIT");
			$Err_PRVSUI=DecatErr_Field("@@vsui", "vendorSUI");
			$Err_PRVSDI=DecatErr_Field("@@vsdi", "vendorSDI");
			$Err_PRVSWC=DecatErr_Field("@@vswc", "vendorSWC");
			$Err_PRVLIT=DecatErr_Field("@@vlit", "vendorLIT");
			$Err_PREFIT=DecatErr_Field("@@efit", "eftFICA");
			$Err_PREFUI=DecatErr_Field("@@efui", "eftFUI");
			$Err_PRESIT=DecatErr_Field("@@esit", "eftSIT");
			$Err_PRESUI=DecatErr_Field("@@esui", "eftSUI");
			$Err_PRESDI=DecatErr_Field("@@esdi", "eftSDI");
			$Err_PRESWC=DecatErr_Field("@@eswc", "eftSWC");
			$Err_PRELIT=DecatErr_Field("@@elit", "eftLIT");
		}
		$row['PRTSTP']=Decat_Field("@@tstp", $edtVar);
		$row['PRTSTP']=Decat_Field("@@tstp", $edtVar);
		$row['PRCKPG']=Decat_Field("@@ckpg", $edtVar);
		$row['PRTETM']=Decat_Field("@@tetm", $edtVar);
		$row['PRMNTA']=Decat_Field("@@mnta", $edtVar);
		$row['PRENTA']=Decat_Field("@@enta", $edtVar);
		$row['PRDPER']=Decat_Field("@@dper", $edtVar);
		$row['PRRCLR']=Decat_Field("@@rclr", $edtVar);
		$row['PRDDAV']=Decat_Field("@@ddav", $edtVar);
		$row['PRMYHS']=Decat_Field("@@myhs", $edtVar);
		$row['PRICWG']=Decat_Field("@@icwg", $edtVar);
		$row['PRICFI']=Decat_Field("@@icfi", $edtVar);
		$row['PRICFU']=Decat_Field("@@icfu", $edtVar);
		$row['PRICSU']=Decat_Field("@@icsu", $edtVar);
		$row['PRICSD']=Decat_Field("@@icsd", $edtVar);
		$row['PRICSW']=Decat_Field("@@icsw", $edtVar);
		$row['PRICLI']=Decat_Field("@@icli", $edtVar);
		$row['PRLB']  =Decat_Field("@@lb@@", $edtVar);
		$row['PRGPAP']=Decat_Field("@@gpap", $edtVar);
		$row['PRAAEN']=Decat_Field("@@aaen", $edtVar);
		$row['PRU1DS']=Decat_Field("@@u1ds", $edtVar);
		$row['PRU1HD']=Decat_Field("@@u1hd", $edtVar);
		$row['PRU2DS']=Decat_Field("@@u2ds", $edtVar);
		$row['PRU2HD']=Decat_Field("@@u2hd", $edtVar);
		$row['PRU3DS']=Decat_Field("@@u3ds", $edtVar);
		$row['PRU3HD']=Decat_Field("@@u3hd", $edtVar);
		$row['PRU4DS']=Decat_Field("@@u4ds", $edtVar);
		$row['PRU4HD']=Decat_Field("@@u4hd", $edtVar);
		$row['PRU5DS']=Decat_Field("@@u5ds", $edtVar);
		$row['PRU5HD']=Decat_Field("@@u5hd", $edtVar);
		$row['PRU6DS']=Decat_Field("@@u6ds", $edtVar);
		$row['PRU6HD']=Decat_Field("@@u6hd", $edtVar);
		$row['PRXFIT']=Decat_Field("@@xfit", $edtVar);
		$row['PRXFUI']=Decat_Field("@@xfui", $edtVar);
		$row['PRXSIT']=Decat_Field("@@xsit", $edtVar);
		$row['PRXSUI']=Decat_Field("@@xsui", $edtVar);
		$row['PRXSDI']=Decat_Field("@@xsdi", $edtVar);
		$row['PRXSWC']=Decat_Field("@@xswc", $edtVar);
		$row['PRXLIT']=Decat_Field("@@xlit", $edtVar);
		$row['PRAFIT']=Decat_Field("@@afit", $edtVar);
		$row['PRAFUI']=Decat_Field("@@afui", $edtVar);
		$row['PRASIT']=Decat_Field("@@asit", $edtVar);
		$row['PRASUI']=Decat_Field("@@asui", $edtVar);
		$row['PRASDI']=Decat_Field("@@asdi", $edtVar);
		$row['PRASWC']=Decat_Field("@@aswc", $edtVar);
		$row['PRALIT']=Decat_Field("@@alit", $edtVar);
		$row['PRVFIT']=Decat_Field("@@vfit", $edtVar);
		$row['PRVFUI']=Decat_Field("@@vfui", $edtVar);
		$row['PRVSIT']=Decat_Field("@@vsit", $edtVar);
		$row['PRVSUI']=Decat_Field("@@vsui", $edtVar);
		$row['PRVSDI']=Decat_Field("@@vsdi", $edtVar);
		$row['PRVSWC']=Decat_Field("@@vswc", $edtVar);
		$row['PRVLIT']=Decat_Field("@@vlit", $edtVar);
		$row['PREFIT']=Decat_Field("@@efit", $edtVar);
		$row['PREFUI']=Decat_Field("@@efui", $edtVar);
		$row['PRESIT']=Decat_Field("@@esit", $edtVar);
		$row['PRESUI']=Decat_Field("@@esui", $edtVar);
		$row['PRESDI']=Decat_Field("@@esdi", $edtVar);
		$row['PRESWC']=Decat_Field("@@eswc", $edtVar);
		$row['PRELIT']=Decat_Field("@@elit", $edtVar);
	} else {
		$row['PRDPER']=PeriodInputFromCYP($row['PRDPER']);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_PRTSTP);
	Build_DspFld("P/R Release Version",$HDPRRL,"","A");
	DspErrMsg($Err_PRTSTP);
	Build_DspFld("P/R Library Level",$HDPRLL,"","A");
	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['PRTSTP']) . "\"></td></tr> ";
	print "\n </table>";

	print "\n <a name=\"general\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">General</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Check Printing Program Name","checkPrintProg","inputalph","","PRCKPG",$row[PRCKPG],$Err_PRCKPG,"10","10","","","");
	Build_Fld_Entry("Pay Transaction Entry Time Format","payEntryFormat","inputalph","STDMILITRY","PRTETM",$row[PRTETM],$Err_PRTETM,"1","1","Y","","");
	Build_Fld_Entry("Print Table Maintenance Audits","prtMaintAudit","inputalph","YORN","PRMNTA",$row[PRMNTA],$Err_PRMNTA,"1","1","Y","","");
	Build_Fld_Entry("Print Pay Transaction Entry Audits","prtEntryAudit","inputalph","YORN","PRENTA",$row[PRENTA],$Err_PRENTA,"1","1","Y","","");
	if ($HDGLRL==0) {
		$textOvr=SetTextOvr($Err_PRDPER);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>G/L Distribution Period</span></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"distPeriod\" value=\"" . rtrim($row['PRDPER']) . "\" size=\"4\" maxlength=\"4\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;periodFld=distPeriod\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
		print "\n </tr> ";
		DspErrMsg($Err_PRDPER);
	}
	Build_Fld_Entry("Reclaim Resource Level","reclaimResourceLevel","inputalph","RECLAIMLVL","PRRCLR",$row[PRRCLR],$Err_PRRCLR,"1","1","Y","","");
	Build_Fld_Entry("Direct Deposit Advice","dirDepAdvice","inputalph","DIRDEPAD","PRDDAV",$row[PRDDAV],$Err_PRDDAV,"1","1","Y","","");
	Build_Fld_Entry("Retain Multiple Years of History","retMultYrsHist","inputalph","YORN","PRMYHS",$row[PRMYHS],$Err_PRMYHS,"1","1","","","");
	Build_Fld_Entry("Consolidated Reporting Database","consRptDB","inputalph","","PRLB",$row[PRLB],$Err_PRLB,"10","10","","","");
	Build_Fld_Entry("Employee % Of Gross After Pre-Tax","grossPct","inputalph","","PRGPAP",$row[PRGPAP],$Err_PRGPAP,"6","6","","","");
	Build_Fld_Entry("Auto Assign Employee Number","autoAssign","inputalph","AUTOASGNEN","PRAAEN",$row[PRAAEN],$Err_PRAAEN,"1","1","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"interCo\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Inter-Co/Dept</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Process Inter-Co/Dept Wages","interCoWages","inputalph","YORN","PRICWG",$row[PRICWG],$Err_PRICWG,"1","1","Y","","");
	Build_Fld_Entry("Process Inter-Co/Dept FICA","interCoFICA","inputalph","YORN","PRICFI",$row[PRICFI],$Err_PRICFI,"1","1","Y","","");
	Build_Fld_Entry("Process Inter-Co/Dept FUI","interCoFUI","inputalph","YORN","PRICFU",$row[PRICFU],$Err_PRICFU,"1","1","Y","","");
	Build_Fld_Entry("Process Inter-Co/Dept SUI","interCoSUI","inputalph","YORN","PRICSU",$row[PRICSU],$Err_PRICSU,"1","1","Y","","");
	Build_Fld_Entry("Process Inter-Co/Dept SDI","interCoSDI","inputalph","YORN","PRICSD",$row[PRICSD],$Err_PRICSD,"1","1","Y","","");
	Build_Fld_Entry("Process Inter-Co/Dept SWC","interCoSWC","inputalph","YORN","PRICSW",$row[PRICSW],$Err_PRICSW,"1","1","Y","","");
	Build_Fld_Entry("Process Inter-Co/Dept LIT","interCoLIT","inputalph","YORN","PRICLI",$row[PRICLI],$Err_PRICLI,"1","1","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"userDefined\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">User Defined</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	print "\n     <tr><td>&nbsp;</td> ";
	print "\n         <td class=\"colhdr\">Description</td> ";
	print "\n         <td class=\"colhdr\">Report Heading</td> ";
	print "\n     </tr> ";

	$textOvr=SetTextOvr($Err_PRU1DS);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>User Defined Numeric Column 1</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefNumDsc1\" value=\"" . rtrim($row['PRU1DS']) . "\" size=\"25\" maxlength=\"25\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefNumHdg1\" value=\"" . rtrim($row['PRU1HD']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_PRU1DS);

	$textOvr=SetTextOvr($Err_PRU2DS);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>User Defined Numeric Column 2</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefNumDsc2\" value=\"" . rtrim($row['PRU2DS']) . "\" size=\"25\" maxlength=\"25\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefNumHdg2\" value=\"" . rtrim($row['PRU2HD']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_PRU2DS);

	$textOvr=SetTextOvr($Err_PRU3DS);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>User Defined Numeric Column 3</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefNumDsc3\" value=\"" . rtrim($row['PRU3DS']) . "\" size=\"25\" maxlength=\"25\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefNumHdg3\" value=\"" . rtrim($row['PRU3HD']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_PRU3DS);

	$textOvr=SetTextOvr($Err_PRU4DS);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>User Defined Alpha Column 1</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefAlpDsc1\" value=\"" . rtrim($row['PRU4DS']) . "\" size=\"25\" maxlength=\"25\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefAlpHdg1\" value=\"" . rtrim($row['PRU4HD']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_PRU4DS);

	$textOvr=SetTextOvr($Err_PRU5DS);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>User Defined Alpha Column 2</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefAlpDsc2\" value=\"" . rtrim($row['PRU5DS']) . "\" size=\"25\" maxlength=\"25\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefAlpHdg2\" value=\"" . rtrim($row['PRU5HD']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_PRU5DS);

	$textOvr=SetTextOvr($Err_PRU6DS);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>User Defined Alpha Column 3</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefAlpDsc3\" value=\"" . rtrim($row['PRU6DS']) . "\" size=\"25\" maxlength=\"25\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"userDefAlpHdg3\" value=\"" . rtrim($row['PRU6HD']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_PRU6DS);

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"taxLiab\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Tax Liability Defaults</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	print "\n     <tr><td>&nbsp;</td> ";
	print "\n         <td class=\"colhdr\">Export<br>Tax Data</td> ";
	print "\n         <td class=\"colhdr\">Auto Select<br>For Payment</td> ";
	print "\n         <td class=\"colhdr\">Vendor Number</td> ";
	print "\n         <td class=\"colhdr\">Electronic<br>Funds Transfer</td> ";
	print "\n     </tr> ";

	print "\n     <tr><td class=\"dsphdr\">Federal/FICA Tax</td> ";
	Build_Fld_Entry("","expFICA","colcode","YORN","PRXFIT",$row[PRXFIT],$Err_PRXFIT,"1","1","Y","","Y");
	Build_Fld_Entry("","autoSelFICA","colcode","YORN","PRAFIT",$row[PRAFIT],$Err_PRAFIT,"1","1","Y","","Y");
	$fieldDesc=RetValue("VMVEND=$row[PRVFIT]", "HDVEND", "VMVNA1");
	print "\n  <td class=\"inputalph\"><input type=\"text\"  name=\"vendorFICA\" value=\"" . rtrim($row['PRVFIT']) . "\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=vendorFICA&amp;fldDesc=vendorFICADesc\" onclick=\"$searchWinVar\"> $searchImage</a><span class=\"dspdesc\" id=\"vendorFICADesc\">$fieldDesc</span></td>";
	Build_Fld_Entry("","eftFICA","colcode","YORN","PREFIT",$row[PREFIT],$Err_PREFIT,"1","1","Y","","Y");
	print "\n     </tr> ";
	DspErrMsg($Err_PRVFIT);

	print "\n     <tr><td class=\"dsphdr\">FUI</td> ";
	Build_Fld_Entry("","expFUI","colcode","YORN","PRXFUI",$row[PRXFUI],$Err_PRXFUI,"1","1","Y","","Y");
	Build_Fld_Entry("","autoSelFUI","colcode","YORN","PRAFUI",$row[PRAFUI],$Err_PRAFUI,"1","1","Y","","Y");
	$fieldDesc=RetValue("VMVEND=$row[PRVFUI]", "HDVEND", "VMVNA1");
	print "\n  <td class=\"inputalph\"><input type=\"text\"  name=\"vendorFUI\" value=\"" . rtrim($row['PRVFUI']) . "\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=vendorFUI&amp;fldDesc=vendorFUIDesc\" onclick=\"$searchWinVar\"> $searchImage</a><span class=\"dspdesc\" id=\"vendorFUIDesc\">$fieldDesc</span></td>";
	Build_Fld_Entry("","eftFUI","colcode","YORN","PREFUI",$row[PREFUI],$Err_PREFUI,"1","1","Y","","Y");
	print "\n     </tr> ";
	DspErrMsg($Err_PRVFUI);

	print "\n     <tr><td class=\"dsphdr\">State Tax</td> ";
	Build_Fld_Entry("","expSIT","colcode","YORN","PRXSIT",$row[PRXSIT],$Err_PRXSIT,"1","1","Y","","Y");
	Build_Fld_Entry("","autoSelSIT","colcode","YORN","PRASIT",$row[PRASIT],$Err_PRASIT,"1","1","Y","","Y");
	$fieldDesc=RetValue("VMVEND=$row[PRVSIT]", "HDVEND", "VMVNA1");
	print "\n  <td class=\"inputalph\"><input type=\"text\"  name=\"vendorSIT\" value=\"" . rtrim($row['PRVSIT']) . "\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=vendorSIT&amp;fldDesc=vendorSITDesc\" onclick=\"$searchWinVar\"> $searchImage</a><span class=\"dspdesc\" id=\"vendorSITDesc\">$fieldDesc</span></td>";
	Build_Fld_Entry("","eftSIT","colcode","YORN","PRESIT",$row[PRESIT],$Err_PRESIT,"1","1","Y","","Y");
	print "\n     </tr> ";
	DspErrMsg($Err_PRVSIT);

	print "\n     <tr><td class=\"dsphdr\">SUI/SUI Surtax</td> ";
	Build_Fld_Entry("","expSUI","colcode","YORN","PRXSUI",$row[PRXSUI],$Err_PRXSUI,"1","1","Y","","Y");
	Build_Fld_Entry("","autoSelSUI","colcode","YORN","PRASUI",$row[PRASUI],$Err_PRASUI,"1","1","Y","","Y");
	$fieldDesc=RetValue("VMVEND=$row[PRVSUI]", "HDVEND", "VMVNA1");
	print "\n  <td class=\"inputalph\"><input type=\"text\"  name=\"vendorSUI\" value=\"" . rtrim($row['PRVSUI']) . "\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=vendorSUI&amp;fldDesc=vendorSUIDesc\" onclick=\"$searchWinVar\"> $searchImage</a><span class=\"dspdesc\" id=\"vendorSUIDesc\">$fieldDesc</span></td>";
	Build_Fld_Entry("","eftSUI","colcode","YORN","PRESUI",$row[PRESUI],$Err_PRESUI,"1","1","Y","","Y");
	print "\n     </tr> ";
	DspErrMsg($Err_PRVSUI);

	print "\n     <tr><td class=\"dsphdr\">SDI</td> ";
	Build_Fld_Entry("","expSDI","colcode","YORN","PRXSDI",$row[PRXSDI],$Err_PRXSDI,"1","1","Y","","Y");
	Build_Fld_Entry("","autoSelSDI","colcode","YORN","PRASDI",$row[PRASDI],$Err_PRASDI,"1","1","Y","","Y");
	$fieldDesc=RetValue("VMVEND=$row[PRVSDI]", "HDVEND", "VMVNA1");
	print "\n  <td class=\"inputalph\"><input type=\"text\"  name=\"vendorSDI\" value=\"" . rtrim($row['PRVSDI']) . "\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=vendorSDI&amp;fldDesc=vendorSDIDesc\" onclick=\"$searchWinVar\"> $searchImage</a><span class=\"dspdesc\" id=\"vendorSDIDesc\">$fieldDesc</span></td>";
	Build_Fld_Entry("","eftSDI","colcode","YORN","PRESDI",$row[PRESDI],$Err_PRESDI,"1","1","Y","","Y");
	print "\n     </tr> ";
	DspErrMsg($Err_PRVSDI);

	print "\n     <tr><td class=\"dsphdr\">SWC</td> ";
	Build_Fld_Entry("","expSWC","colcode","YORN","PRXSWC",$row[PRXSWC],$Err_PRXSWC,"1","1","Y","","Y");
	Build_Fld_Entry("","autoSelSWC","colcode","YORN","PRASWC",$row[PRASWC],$Err_PRASWC,"1","1","Y","","Y");
	$fieldDesc=RetValue("VMVEND=$row[PRVSWC]", "HDVEND", "VMVNA1");
	print "\n  <td class=\"inputalph\"><input type=\"text\"  name=\"vendorSWC\" value=\"" . rtrim($row['PRVSWC']) . "\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=vendorSWC&amp;fldDesc=vendorSWCDesc\" onclick=\"$searchWinVar\"> $searchImage</a><span class=\"dspdesc\" id=\"vendorSWCDesc\">$fieldDesc</span></td>";
	Build_Fld_Entry("","eftSWC","colcode","YORN","PRESWC",$row[PRESWC],$Err_PRESWC,"1","1","Y","","Y");
	print "\n     </tr> ";
	DspErrMsg($Err_PRVSWC);

	print "\n     <tr><td class=\"dsphdr\">Local Tax</td> ";
	Build_Fld_Entry("","expLIT","colcode","YORN","PRXLIT",$row[PRXLIT],$Err_PRXLIT,"1","1","Y","","Y");
	Build_Fld_Entry("","autoSelLIT","colcode","YORN","PRALIT",$row[PRALIT],$Err_PRALIT,"1","1","Y","","Y");
	$fieldDesc=RetValue("VMVEND=$row[PRVLIT]", "HDVEND", "VMVNA1");
	print "\n  <td class=\"inputalph\"><input type=\"text\"  name=\"vendorLIT\" value=\"" . rtrim($row['PRVLIT']) . "\" size=\"7\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=vendorLIT&amp;fldDesc=vendorLITDesc\" onclick=\"$searchWinVar\"> $searchImage</a><span class=\"dspdesc\" id=\"vendorLITDesc\">$fieldDesc</span></td>";
	Build_Fld_Entry("","eftLIT","colcode","YORN","PRELIT",$row[PRELIT],$Err_PRELIT,"1","1","Y","","Y");
	print "\n     </tr> ";
	DspErrMsg($Err_PRVLIT);

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

	Concat_Field("@@ckpg", strtoupper($_POST['checkPrintProg']));
	if (!isset($_POST['payEntryFormat'])) {$_POST['payEntryFormat']="N";} Concat_Field("@@tetm", $_POST['payEntryFormat']);
	if (!isset($_POST['prtMaintAudit']))  {$_POST['prtMaintAudit']="N";}  Concat_Field("@@mnta", $_POST['prtMaintAudit']);
	if (!isset($_POST['prtEntryAudit']))  {$_POST['prtEntryAudit']="N";}  Concat_Field("@@enta", $_POST['prtEntryAudit']);
	Concat_Field("@@dper", $_POST['distPeriod']);
	Concat_Field("@@rclr", $_POST['reclaimResourceLevel']);
	Concat_Field("@@ddav", strtoupper($_POST['dirDepAdvice']));
	if (!isset($_POST['retMultYrsHist'])) {$_POST['retMultYrsHist']="N";} Concat_Field("@@myhs", $_POST['retMultYrsHist']);
	if (!isset($_POST['interCoWages']))   {$_POST['interCoWages']="N";}   Concat_Field("@@icwg", $_POST['interCoWages']);
	if (!isset($_POST['interCoFICA']))    {$_POST['interCoFICA']="N";}    Concat_Field("@@icfi", $_POST['interCoFICA']);
	if (!isset($_POST['interCoFUI']))     {$_POST['interCoFUI']="N";}     Concat_Field("@@icfu", $_POST['interCoFUI']);
	if (!isset($_POST['interCoSUI']))     {$_POST['interCoSUI']="N";}     Concat_Field("@@icsu", $_POST['interCoSUI']);
	if (!isset($_POST['interCoSDI']))     {$_POST['interCoSDI']="N";}     Concat_Field("@@icsd", $_POST['interCoSDI']);
	if (!isset($_POST['interCoSWC']))     {$_POST['interCoSWC']="N";}     Concat_Field("@@icsw", $_POST['interCoSWC']);
	if (!isset($_POST['interCoLIT']))     {$_POST['interCoLIT']="N";}     Concat_Field("@@icli", $_POST['interCoLIT']);
	Concat_Field("@@lb@@", strtoupper($_POST['consRptDB']));
	Concat_Field("@@gpap", $_POST['grossPct']);
	if (!isset($_POST['autoAssign']))     {$_POST['autoAssign']="N";}     Concat_Field("@@aaen", $_POST['autoAssign']);
	Concat_Field("@@u1ds", $_POST['userDefNumDsc1']);
	Concat_Field("@@u1hd", $_POST['userDefNumHdg1']);
	Concat_Field("@@u2ds", $_POST['userDefNumDsc2']);
	Concat_Field("@@u2hd", $_POST['userDefNumHdg2']);
	Concat_Field("@@u3ds", $_POST['userDefNumDsc3']);
	Concat_Field("@@u3hd", $_POST['userDefNumHdg3']);
	Concat_Field("@@u4ds", $_POST['userDefAlpDsc1']);
	Concat_Field("@@u4hd", $_POST['userDefAlpHdg1']);
	Concat_Field("@@u5ds", $_POST['userDefAlpDsc2']);
	Concat_Field("@@u5hd", $_POST['userDefAlpHdg2']);
	Concat_Field("@@u6ds", $_POST['userDefAlpDsc3']);
	Concat_Field("@@u6hd", $_POST['userDefAlpHdg3']);
	if (!isset($_POST['expFICA']))     {$_POST['expFICA']="N";}     Concat_Field("@@xfit", $_POST['expFICA']);
	if (!isset($_POST['expFUI']))      {$_POST['expFUI']="N";}      Concat_Field("@@xfui", $_POST['expFUI']);
	if (!isset($_POST['expSIT']))      {$_POST['expSIT']="N";}      Concat_Field("@@xsit", $_POST['expSIT']);
	if (!isset($_POST['expSUI']))      {$_POST['expSUI']="N";}      Concat_Field("@@xsui", $_POST['expSUI']);
	if (!isset($_POST['expSDI']))      {$_POST['expSDI']="N";}      Concat_Field("@@xsdi", $_POST['expSDI']);
	if (!isset($_POST['expSWC']))      {$_POST['expSWC']="N";}      Concat_Field("@@xswc", $_POST['expSWC']);
	if (!isset($_POST['expLIT']))      {$_POST['expLIT']="N";}      Concat_Field("@@xlit", $_POST['expLIT']);
	if (!isset($_POST['autoSelFICA'])) {$_POST['autoSelFICA']="N";} Concat_Field("@@afit", $_POST['autoSelFICA']);
	if (!isset($_POST['autoSelFUI']))  {$_POST['autoSelFUI']="N";}  Concat_Field("@@afui", $_POST['autoSelFUI']);
	if (!isset($_POST['autoSelSIT']))  {$_POST['autoSelSIT']="N";}  Concat_Field("@@asit", $_POST['autoSelSIT']);
	if (!isset($_POST['autoSelSUI']))  {$_POST['autoSelSUI']="N";}  Concat_Field("@@asui", $_POST['autoSelSUI']);
	if (!isset($_POST['autoSelSDI']))  {$_POST['autoSelSDI']="N";}  Concat_Field("@@asdi", $_POST['autoSelSDI']);
	if (!isset($_POST['autoSelSWC']))  {$_POST['autoSelSWC']="N";}  Concat_Field("@@aswc", $_POST['autoSelSWC']);
	if (!isset($_POST['autoSelLIT']))  {$_POST['autoSelLIT']="N";}  Concat_Field("@@alit", $_POST['autoSelLIT']);
	Concat_Field("@@vfit", $_POST['vendorFICA']);
	Concat_Field("@@vfui", $_POST['vendorFUI']);
	Concat_Field("@@vsit", $_POST['vendorSIT']);
	Concat_Field("@@vsui", $_POST['vendorSUI']);
	Concat_Field("@@vsdi", $_POST['vendorSDI']);
	Concat_Field("@@vswc", $_POST['vendorSWC']);
	Concat_Field("@@vlit", $_POST['vendorLIT']);
	if (!isset($_POST['eftFICA'])) {$_POST['eftFICA']="N";}  Concat_Field("@@efit", $_POST['eftFICA']);
	if (!isset($_POST['eftFUI']))  {$_POST['eftFUI']="N";}   Concat_Field("@@efui", $_POST['eftFUI']);
	if (!isset($_POST['eftSIT']))  {$_POST['eftSIT']="N";}   Concat_Field("@@esit", $_POST['eftSIT']);
	if (!isset($_POST['eftSUI']))  {$_POST['eftSUI']="N";}   Concat_Field("@@esui", $_POST['eftSUI']);
	if (!isset($_POST['eftSDI']))  {$_POST['eftSDI']="N";}   Concat_Field("@@esdi", $_POST['eftSDI']);
	if (!isset($_POST['eftSWC']))  {$_POST['eftSWC']="N";}   Concat_Field("@@eswc", $_POST['eftSWC']);
	if (!isset($_POST['eftLIT']))  {$_POST['eftLIT']="N";}   Concat_Field("@@elit", $_POST['eftLIT']);
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPRCTU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc("C", "P/R Control", "", "", "", "", "");
		$includeName= "{$homePath}PRControl{$dataBaseID}.php";
		$fileName="PRControl{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HPRCTL_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}
?>