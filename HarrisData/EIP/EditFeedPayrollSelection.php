<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$backHome           = $_GET['backHome'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$reportSelType      = $_GET['reportSelType'];
$jobSbmSched        = $_GET['jobSbmSched'];
$resetSelectionFlag = $_GET['resetSelectionFlag'];
$rtvSelection       = $_GET['rtvSelection'];
$saveSelection      = $_GET['saveSelection'];
$scheduleJobSwitch  = $_GET['scheduleJobSwitch'];
$selScheduleJob     = $_GET['selScheduleJob'];
$submitSchedule     = $_GET['submitSchedule'];
$chkExpAuth = (isset($_GET['chkExpAuth'])) ? $_GET['chkExpAuth'] : null;

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once ($baseExportFile);

$page_title            = "Edit/Feed Payroll Selection";
$scriptName            = "EditFeedPayrollSelection.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CETFPR";
$submitEnvProgram      = "CETFPR";
$submitEnvPrinter      = "";
$submitScheduleScript  = "";
$applicationID         = "ET";

if (is_null($tag)) {$tag="REPORT";}

$exportError = null;
if (!is_null($chkExpAuth)) {
	$exportPath = "{$homePath}{$exportDirectory}{$dataBaseID}/";
	if (!file_exists("$exportPath")) {
		exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$exportPath\")'");
	}
	$exportFolder = 'PayTransWIP';
	$wipPath = "{$exportPath}{$exportFolder}/";
	if (!file_exists("$wipPath")) {
		exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$wipPath\")'");
	}

	$fileName = 'PayTransWIP_' . date('YmdHis') . $userProfile . '.csv';
	$fileHandle = fopen($wipPath . $fileName, "w");
	if ($fileHandle) {
		$file = $wipPath . $fileName;
		unlink($file);
	} else {
		$exportError = 'Y';
	}
}
if ($tag == "REPORT") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';
	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(chgForm) { ";
	print "\n if (document.Chg.printFR.checked ===false && ";
	print "\n     document.Chg.feedPA.checked ===false && ";
	print "\n     document.Chg.feedSE.checked ===false ";
	print "\n )";
	print "\n {alert(\"At least one Edit/Feed Option must be selected.\"); return false;} ";
	print "\n if (document.Chg.printFR.checked ===false && ";
	print "\n     document.Chg.feedPA.checked ===false && ";
	print "\n     document.Chg.feedSE.checked ===true ";
	print "\n )";
	print "\n {alert(\"Feed Schedule Exception requires either Print Feed Reports or Feed Payroll/Attendance.\"); return false;} ";
	print "\n   if (editdate(document.Chg.frPayPeriod) ";
	print "\n    && editdate(document.Chg.toPayPeriod) ";
	print "\n    && editNum(document.Chg.frSchedule, 3, 0) ";
	print "\n    && editNum(document.Chg.toSchedule, 3, 0) ";
	print "\n    && editNum(document.Chg.frCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.toCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.frFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.toFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.frEmployee, 5, 0) ";
	print "\n    && editNum(document.Chg.toEmployee, 5, 0) ";
	print "\n    && editNum(document.Chg.f1WK, 2, 1) ";
	print "\n    && editNum(document.Chg.f1ED, 3, 0) ";
	print "\n    && editNum(document.Chg.f2WK, 2, 1) ";
	print "\n    && editNum(document.Chg.f2ED, 3, 0) ";
	print "\n    && editNum(document.Chg.f3WK, 2, 1) ";
	print "\n    && editNum(document.Chg.f3ED, 3, 0) ";
	print "\n    && editNum(document.Chg.f4WK, 2, 1) ";
	print "\n    && editNum(document.Chg.f4ED, 2, 0) ";
	print "\n    && editFromToAll(document.Chg.frSchedule, document.Chg.toSchedule, document.Chg.allSchedule, 3) ";
	print "\n    && editFromToAll(document.Chg.frCompany, document.Chg.toCompany, document.Chg.allCompany, 2) ";
	print "\n    && editFromToAll(document.Chg.frFacility, document.Chg.toFacility, document.Chg.allFacility, 4) ";
	print "\n    && editFromToAll(document.Chg.frLaborCode, document.Chg.toLaborCode, document.Chg.allLaborCode, 2) ";
	print "\n    && editFromToAll(document.Chg.frDept, document.Chg.toDept, document.Chg.allDept, 5) ";
	print "\n    && editFromToAll(document.Chg.frEmployee, document.Chg.toEmployee, document.Chg.allEmployee, 5) ";
	print "\n    ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "EDITFEEDPAYROLL";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	if (is_null($exportError)) {
		require 'SubmitScheduleTop.php';
	} else {
		print "\n <table $contentTable> ";
		print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
		print "\n <tr><td><h1>$page_title</h1></td> ";
		print "</td></tr></table>";
	}
	require 'ConfMessageDisplayNoTable.php';
	if (!is_null($exportError)) {print "\n <span class=\"error\" $textOvr> &nbsp; &nbsp; You are not authorized. Export Authority is required for this option.</span>";}
	print $hrTagAttr;

	$focusField="printFR";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			$Err_PFR=DecatErr_Field("@@ppfr", "printFR");
			$Err_FPA=DecatErr_Field("@@pfpa", "feedPA");
			$Err_FSE=DecatErr_Field("@@pfse", "feedSE");

			$Err_FSCD=DecatErr_Field("@@fscd", "frSchedule");
			$Err_TSCD=DecatErr_Field("@@tscd", "toSchedule");
			$Err_ASCD=DecatErr_Field("@@ascd", "allSchedule");

			$Err_FPER=DecatErr_Field("@@fper", "frPayPeriod");
			$Err_TPER=DecatErr_Field("@@tper", "toPayPeriod");
			$Err_APER=DecatErr_Field("@@aper", "allPayPeriod");

			$Err_FCO=DecatErr_Field("@@fco@", "frCompany");
			$Err_TCO=DecatErr_Field("@@tco@", "toCompany");
			$Err_ACO=DecatErr_Field("@@aco@", "allCompany");

			$Err_FFAC=DecatErr_Field("@@ffac", "frFacility");
			$Err_TFAC=DecatErr_Field("@@tfac", "toFacility");
			$Err_AFAC=DecatErr_Field("@@afac", "allFacility");

			$Err_FLCD=DecatErr_Field("@@flcd", "frLaborCode");
			$Err_TLCD=DecatErr_Field("@@tlcd", "toLaborCode");
			$Err_ALCD=DecatErr_Field("@@alcd", "allLaborCode");

			$Err_FDEP=DecatErr_Field("@@fdep", "frDept");
			$Err_TDEP=DecatErr_Field("@@tdep", "toDept");
			$Err_ADEP=DecatErr_Field("@@adep", "allDept");

			$Err_FEMP=DecatErr_Field("@@femp", "frEmployee");
			$Err_TEMP=DecatErr_Field("@@temp", "toEmployee");
			$Err_AEMP=DecatErr_Field("@@aemp", "allEmployee");

			$Err_F1WK=DecatErr_Field("@@f1WK", "f1WK");
			$Err_F1ED=DecatErr_Field("@@f1ED", "f1ED");
			$Err_F2WK=DecatErr_Field("@@f2WK", "f2WK");
			$Err_F2ED=DecatErr_Field("@@f2ED", "f2ED");
			$Err_F3WK=DecatErr_Field("@@f3WK", "f3WK");
			$Err_F3ED=DecatErr_Field("@@f3ED", "f3ED");
			$Err_F4WK=DecatErr_Field("@@f4WK", "f4WK");
			$Err_F4ED=DecatErr_Field("@@f4ED", "f4ED");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);

		$PFR=Decat_Field("@@ppfr", $edtVar);
		$PFR = ($PFR == '1') ? 'Y' : 'N';
		$FPA=Decat_Field("@@pfpa", $edtVar);
		$FPA = ($FPA == '1') ? 'Y' : 'N';
		$FSE=Decat_Field("@@pfse", $edtVar);
		$FSE = ($FSE == '1') ? 'Y' : 'N';

		$FSCD=Decat_Field("@@fscd", $edtVar);
		$TSCD=Decat_Field("@@tscd", $edtVar);
		$ASCD=Decat_Field("@@ascd", $edtVar);;

		$FPER=Decat_Field("@@fper", $edtVar);
		$TPER=Decat_Field("@@tper", $edtVar);
		$APER=Decat_Field("@@aper", $edtVar);;

		$FCO=Decat_Field("@@fco@", $edtVar);
		$TCO=Decat_Field("@@tco@", $edtVar);
		$ACO=Decat_Field("@@aco@", $edtVar);

		$FFAC=Decat_Field("@@ffac", $edtVar);
		$TFAC=Decat_Field("@@tfac", $edtVar);
		$AFAC=Decat_Field("@@afac", $edtVar);

		$FLCD=Decat_Field("@@flcd", $edtVar);
		$TLCD=Decat_Field("@@tlcd", $edtVar);
		$ALCD=Decat_Field("@@alcd", $edtVar);

		$FDEP=Decat_Field("@@fdep", $edtVar);
		$TDEP=Decat_Field("@@tdep", $edtVar);
		$ADEP=Decat_Field("@@adep", $edtVar);

		$FEMP=Decat_Field("@@femp", $edtVar);
		$TEMP=Decat_Field("@@temp", $edtVar);
		$AEMP=Decat_Field("@@aemp", $edtVar);

		$f1WK=Decat_Field("@@f1WK", $edtVar);
		$f1ED=Decat_Field("@@f1ED", $edtVar);
		$f2WK=Decat_Field("@@f2WK", $edtVar);
		$f2ED=Decat_Field("@@f2ED", $edtVar);
		$f3WK=Decat_Field("@@f3WK", $edtVar);
		$f3ED=Decat_Field("@@f3ED", $edtVar);
		$f4WK=Decat_Field("@@f4WK", $edtVar);
		$f4ED=Decat_Field("@@f4ED", $edtVar);
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$PFR="Y";
		$FPA="N";
		$FSE="N";

		$ASCD = "ALL";
		$TPER = date('mdy');
		$ALCD = "ALL";
		$ACO = "ALL";
		$AFAC = "ALL";
		$ADEP = "ALL";
		$AEMP = "ALL";

		$f1WK=1.0;
		$f1ED=5;
		$f2WK=2.0;
		$f2ED=10;
		$f3WK=3.0;
		$f3ED=11;
		$f4WK=4.0;
		$f4ED=22;
	}

	if ($ASCD == "ALL") {$checked_ASCD="CHECKED";} else {$checked_ASCD="";}
	if ($ALCD == "ALL") {$checked_ALCD="CHECKED";} else {$checked_ALCD="";}
	if ($ACO == "ALL")  {$checked_ACO="CHECKED";} else {$checked_AACO="";}
	if ($AFAC == "ALL") {$checked_AFAC="CHECKED";} else {$checked_AFAC="";}
	if ($ADEP == "ALL") {$checked_ADEP="CHECKED";} else {$checked_ADEP="";}
	if ($ASUB == "ALL") {$checked_ASUB="CHECKED";} else {$checked_ASUB="";}
	if ($AEMP == "ALL") {$checked_AEMP="CHECKED";} else {$checked_AEMP="";}

	if (is_null($exportError)) {
		print "\n <table $quickLinkTable> ";
		print "\n   <tr> ";
		print "\n       <td class=\"quickLinkTabs\"><a href=\"#PrintOption\">Edit/Feed Options</a></td> ";
		print "\n       <td class=\"quickLinkTabs\"><a href=\"#FromToAll\">From/To/All</a></td> ";
		print "\n       <td class=\"quickLinkTabs\"><a href=\"#Specific\">Credit Service Weeks</a></td> ";
		print "\n   </tr> ";
		print "\n </table> ";
		print $hrTagAttr;

		print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

		print "\n <a name=\"PrintOption\"></a> ";
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n     <legend class=\"legendTitle\">Edit/Feed Options</legend> ";
		require 'TopOfForm.php';
		print "\n     <table $contentTable> ";
		Build_Fld_Entry("Print Feed Reports", "printFR", "inputalph", "YORN", "PFR", $PFR, $Err_PFR, "1", "1", "", "", "");
		Build_Fld_Entry("Feed Payroll/Attendance", "feedPA", "inputalph", "YORN", "FPA", $FPA, $Err_FPA, "1", "1", "", "", "");
		Build_Fld_Entry("Feed Schedule Exceptions", "feedSE", "inputalph", "YORN", "FSE", $FSE, $Err_FSE, "1", "1", "", "", "");
		print "\n     </table> ";
		print "\n </fieldset> ";

		print "\n <a name=\"FromToAll\"></a> ";
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n     <legend class=\"legendTitle\">From/To/All</legend> ";
		require 'TopOfForm.php';
		print "\n     <table $contentTable> ";

		print "\n         <tr><td>&nbsp;</td> ";
		print "\n             <td class=\"colhdr\">From</td> ";
		print "\n             <td class=\"colhdr\">To</td> ";
		print "\n             <td class=\"colhdr\">All</td> ";
		print "\n         </tr> ";

		$textOvr = SetTextOvr($Err_FPER);
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_TSCD);
		}
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_ASCD);
		}
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Schedule</td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frSchedule\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}ScheduleSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frSchedule&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toSchedule\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}ScheduleSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toSchedule&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allSchedule\" value=\"ALL\" $checked_ASCD onClick=\"if (this.checked) this.form.frSchedule.value='', this.form.toSchedule.value='';\"></td> ";
		print "\n </tr>";

		$textOvr = SetTextOvr($Err_FPER);
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_TPER);
		}
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_APER);
		}
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Pay Period</span></td> ";
		Build_Fld_Entry("", "frPayPeriod", "inputnmbr", "Date", "", $FPER, $Err_FPER, "6", "6", "", "", "Y");
		Build_Fld_Entry("", "toPayPeriod", "inputnmbr", "Date", "", $TPER, $Err_TPER, "6", "6", "", "", "Y");
		print "\n         </tr> ";
		DspErrMsg($Err_FPER);
		DspErrMsg($Err_TPER);
		DspErrMsg($Err_APER);

		$textOvr = SetTextOvr($Err_FSUB);
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_TLCD);
		}
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_ALCD);
		}
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Labor Code</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frLaborCode\" value=\"$FLCD\" size=\"2\" maxlength=\"2\"></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toLaborCode\" value=\"$TLCD\" size=\"2\" maxlength=\"2\"></td> ";
		print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allLaborCode\" value=\"ALL\" $checked_ALCD onClick=\"if (this.checked) this.form.frLaborCode.value='', this.form.toLaborCode.value='';\"></td> ";
		print "\n         </tr> ";
		DspErrMsg($Err_FLCD);
		DspErrMsg($Err_TLCD);
		DspErrMsg($Err_ALCD);

		$textOvr = SetTextOvr($Err_FCO);
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_TCO);
		}
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_ACO);
		}
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Company Number</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frCompany\" value=\"$FCO\" size=\"2\" maxlength=\"2\">";
		print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frCompany&amp;fldFac=frFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toCompany\" value=\"$TCO\" size=\"2\" maxlength=\"2\">";
		print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFac=toFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allCompany\" value=\"ALL\" $checked_ACO onClick=\"if (this.checked) this.form.frCompany.value='', this.form.toCompany.value='';\"></td> ";
		print "\n         </tr> ";
		DspErrMsg($Err_FCO);
		DspErrMsg($Err_TCO);
		DspErrMsg($Err_ACO);

		$textOvr = SetTextOvr($Err_FFAC);
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_TFAC);
		}
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_AFAC);
		}
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Facility Number</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frFacility\" value=\"$FFAC\" size=\"4\" maxlength=\"4\"></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toFacility\" value=\"$TFAC\" size=\"4\" maxlength=\"4\"></td> ";
		print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allFacility\" value=\"ALL\" $checked_AFAC onClick=\"if (this.checked) this.form.frFacility.value='', this.form.toFacility.value='';\"></td> ";
		print "\n         </tr> ";
		DspErrMsg($Err_FFAC);
		DspErrMsg($Err_TFAC);
		DspErrMsg($Err_AFAC);

		$textOvr = SetTextOvr($Err_FDEP);
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_TDEP);
		}
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_ADEP);
		}
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Home Department</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frDept\" value=\"$FDEP\" size=\"5\" maxlength=\"5\">";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toDept\" value=\"$TDEP\" size=\"5\" maxlength=\"5\">";
		print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allDept\" value=\"ALL\" $checked_ADEP onClick=\"if (this.checked) this.form.frDept.value='', this.form.toDept.value='';\"></td> ";
		print "\n         </tr> ";
		DspErrMsg($Err_FDEP);
		DspErrMsg($Err_TDEP);
		DspErrMsg($Err_ADEP);

		$textOvr = SetTextOvr($Err_FEMP);
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_TEMP);
		}
		if ($textOvr == "") {
			$textOvr = SetTextOvr($Err_AEMP);
		}
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Employee Number</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frEmployee\" value=\"$FEMP\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;forHRCo=&amp;fldHREmpl=frEmployee&amp;fldEmplName=none\" onclick=\"$searchWinVar\">$searchImage</a></td>";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toEmployee\" value=\"$TEMP\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;forHRCo=&amp;fldHREmpl=toEmployee&amp;fldEmplName=none\" onclick=\"$searchWinVar\">$searchImage</a></td>";
		print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allEmployee\" value=\"ALL\" $checked_AEMP onClick=\"if (this.checked) this.form.frEmployee.value='', this.form.toEmployee.value='';\"></td> ";
		print "\n         </tr> ";
		DspErrMsg($Err_FEMP);
		DspErrMsg($Err_TEMP);
		DspErrMsg($Err_AEMP);

		print "\n </table> ";
		print "\n </fieldset> ";

		print "\n <a name=\"Specific\"></a> ";
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n     <legend class=\"legendTitle\">Credit Service Weeks</legend> ";
		require 'TopOfForm.php';
		print "\n     <table $contentTable> ";

		print "\n         <tr><td>&nbsp;</td> ";
		print "\n             <td class=\"colhdr\">Credit<br>Service<br>Weeks</td> ";
		print "\n             <td class=\"colhdr\">Equivalent<br>Days</td> ";
		print "\n         </tr> ";

		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Weekly</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"f1WK\" value=\"$f1WK\" size=\"4\" maxlength=\"4\">";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"f1ED\" value=\"$f1ED\" size=\"4\" maxlength=\"3\">";
		print "\n         </tr> ";

		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Bi-Weekly</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"f2WK\" value=\"$f2WK\" size=\"4\" maxlength=\"4\">";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"f2ED\" value=\"$f2ED\" size=\"4\" maxlength=\"3\">";
		print "\n         </tr> ";

		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Semi-Monthly</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"f3WK\" value=\"$f3WK\" size=\"4\" maxlength=\"4\">";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"f3ED\" value=\"$f3ED\" size=\"4\" maxlength=\"3\">";
		print "\n         </tr> ";

		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Monthly</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"f4WK\" value=\"$f4WK\" size=\"4\" maxlength=\"4\">";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"f4ED\" value=\"$f4ED\" size=\"4\" maxlength=\"3\">";
		print "\n         </tr> ";

		print "\n     </table> ";
		print "\n </fieldset> ";

		$envProgram = $submitEnvProgram;
		$envPrinter = $submitEnvPrinter;
		require 'ScheduleJob.php';
		require 'SubmitScheduleBottom.php';
		print "\n $hrTagAttr ";

		if ($focusField != "") {
			print "\n <script TYPE=\"text/javascript\"> ";
			print "\n document.Chg.$focusField.focus(); ";
			print "\n </script> ";
		}
		print "\n </form>";
	}
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	$edtVar= "";
	$ppfr = '1';
	$pfpa = '1';
	$pfse = '1';
	if (!isset($_POST['printFR'])) {$ppfr='0';}  Concat_Field("@@ppfr", $ppfr);
	if (!isset($_POST['feedPA'])) {$pfpa='0';}  Concat_Field("@@pfpa", $pfpa);
	if (!isset($_POST['feedSE'])) {$pfse='0';}  Concat_Field("@@pfse", $pfse);

	Concat_Field("@@fscd", $_POST['frSchedule']);
	Concat_Field("@@tscd", $_POST['toSchedule']);
	Concat_Field("@@ascd", $_POST['allSchedule']);

	Concat_Field("@@fper", $_POST['frPayPeriod']);
	Concat_Field("@@tper", $_POST['toPayPeriod']);

	Concat_Field("@@fco@", $_POST['frCompany']);
	Concat_Field("@@tco@", $_POST['toCompany']);
	Concat_Field("@@aco@", $_POST['allCompany']);

	Concat_Field("@@ffac", $_POST['frFacility']);
	Concat_Field("@@tfac", $_POST['toFacility']);
	Concat_Field("@@afac", $_POST['allFacility']);

	Concat_Field("@@flcd", $_POST['frLaborCode']);
	Concat_Field("@@tlcd", $_POST['toLaborCode']);
	Concat_Field("@@alcd", $_POST['allLaborCode']);

	Concat_Field("@@fdep", $_POST['frDept']);
	Concat_Field("@@tdep", $_POST['toDept']);
	Concat_Field("@@adep", $_POST['allDept']);

	Concat_Field("@@femp", $_POST['frEmployee']);
	Concat_Field("@@temp", $_POST['toEmployee']);
	Concat_Field("@@aemp", $_POST['allEmployee']);

	Concat_Field("@@f1WK", $_POST['f1WK']);
	Concat_Field("@@f1ED", $_POST['f1ED']);
	Concat_Field("@@f2WK", $_POST['f2WK']);
	Concat_Field("@@f2ED", $_POST['f2ED']);
	Concat_Field("@@f3WK", $_POST['f3WK']);
	Concat_Field("@@f3ED", $_POST['f3ED']);
	Concat_Field("@@f4WK", $_POST['f4WK']);
	Concat_Field("@@f4ED", $_POST['f4ED']);

	Concat_Field("@@user", $userProfile);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";
	//echo $edtVar;
	//exit();

	$returnValue=Selection_Edit_Handle("HETFPS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}
?>	
