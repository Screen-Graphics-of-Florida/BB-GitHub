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

require_once 'SetLibraryList.php';

require_once "GLControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title            = "Account Allocation List";
$scriptName            = "GLAccountALlocationList.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CGLALL";
$submitEnvProgram      = "HGLALL";
$submitEnvPrinter      = "QSYSPRT";
$submitScheduleScript  = "";
$applicationID         = "GL";

if (is_null($tag)) {$tag="REPORT";}

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
	print "\n   if (editNum(document.Chg.frAlcFrCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.frAlcFrFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.toAlcFrCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.toAlcFrFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.frAlcFrAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.frAlcFrSubAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.toAlcFrAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.toAlcFrSubAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.frAlcFrLevel, 2, 0) ";
	print "\n    && editNum(document.Chg.toAlcFrLevel, 2, 0) ";
	print "\n    && editNum(document.Chg.frAlcToCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.frAlcToFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.toAlcToCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.toAlcToFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.frAlcToAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.frAlcToSubAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.toAlcToAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.toAlcToSubAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.frAlcPcAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.frAlcPcSubAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.toAlcPcAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.toAlcPcSubAcct, 4, 0) ";
	print "\n    && editFromToAll2(document.Chg.frAlcFrCompany, document.Chg.frAlcFrFacility, document.Chg.toAlcFrCompany, document.Chg.toAlcFrFacility, document.Chg.allAlcFrCoFac, 2 , 4) ";
	print "\n    && editFromToAll2(document.Chg.frAlcFrAccount, document.Chg.frAlcFrSubAcct, document.Chg.toAlcFrAccount, document.Chg.toAlcFrSubAcct, document.Chg.allAlcFrAcct, 4 , 4) ";
	print "\n    && editFromToAll(document.Chg.frAlcFrLevel,  document.Chg.toAlcFrLevel,  document.Chg.allAlcFrLevel, 2) ";
	print "\n    && editFromToAll2(document.Chg.frAlcToCompany, document.Chg.frAlcToFacility, document.Chg.toAlcToCompany, document.Chg.toAlcToFacility, document.Chg.allAlcToCoFac, 2 , 4) ";
	print "\n    && editFromToAll2(document.Chg.frAlcToAccount, document.Chg.frAlcToSubAcct, document.Chg.toAlcToAccount, document.Chg.toAlcToSubAcct, document.Chg.allAlcToAcct, 4 , 4) ";
	print "\n    && editFromToAll2(document.Chg.frAlcPcAccount, document.Chg.frAlcPcSubAcct, document.Chg.toAlcPcAccount, document.Chg.toAlcPcSubAcct, document.Chg.allAlcPcAcct, 4 , 4) ";
	print "\n    ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "GLACCOUNTALLOCATION";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="frAlcFrCompany";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			$Err_FFCO=DecatErr_Field("@@ffco", "frAlcFrCompany");
			$Err_FFFC=DecatErr_Field("@@fffc", "frAlcFrFacility");
			$Err_TFCO=DecatErr_Field("@@tfco", "toAlcFrCompany");
			$Err_TFFC=DecatErr_Field("@@tffc", "toAlcFrFacility");
			$Err_AFCF=DecatErr_Field("@@afcf", "allAlcFrCoFac");

			$Err_FFAC=DecatErr_Field("@@ffac", "frAlcFrAccount");
			$Err_FFSB=DecatErr_Field("@@ffsb", "frAlcFrSubAcct");
			$Err_TFAC=DecatErr_Field("@@tfac", "toAlcFrAccount");
			$Err_TFSB=DecatErr_Field("@@tfsb", "toAlcFrSubAcct");
			$Err_AFAC=DecatErr_Field("@@afac", "allAlcFrAcct");

			$Err_FFLV=DecatErr_Field("@@fflv", "frAlcFrLevel");
			$Err_TFLV=DecatErr_Field("@@tflv", "toAlcFrLevel");
			$Err_AFLV=DecatErr_Field("@@aflv", "allAlcFrLevel");

			$Err_FTCO=DecatErr_Field("@@ftco", "frAlcToCompany");
			$Err_FTFC=DecatErr_Field("@@ftfc", "frAlcToFacility");
			$Err_TTCO=DecatErr_Field("@@ttco", "toAlcToCompany");
			$Err_TTFC=DecatErr_Field("@@ttfc", "toAlcToFacility");
			$Err_ATCF=DecatErr_Field("@@atcf", "allAlcToCoFac");

			$Err_FTAC=DecatErr_Field("@@ftac", "frAlcToAccount");
			$Err_FTSB=DecatErr_Field("@@ftsb", "frAlcToSubAcct");
			$Err_TTAC=DecatErr_Field("@@ttac", "toAlcToAccount");
			$Err_TTSB=DecatErr_Field("@@ttsb", "toAlcToSubAcct");
			$Err_ATAC=DecatErr_Field("@@atac", "allAlcToAcct");

			$Err_FPAC=DecatErr_Field("@@fpac", "frAlcPcAccount");
			$Err_FPSB=DecatErr_Field("@@fpsb", "frAlcPcSubAcct");
			$Err_TPAC=DecatErr_Field("@@tpac", "toAlcPcAccount");
			$Err_TPSB=DecatErr_Field("@@tpsb", "toAlcPcSubAcct");
			$Err_APAC=DecatErr_Field("@@apac", "allAlcPcAcct");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);

		$FFCO=Decat_Field("@@ffco", $edtVar);
		$FFFC=Decat_Field("@@fffc", $edtVar);
		$TFCO=Decat_Field("@@tfco", $edtVar);
		$TFFC=Decat_Field("@@tffc", $edtVar);
		$AFCF=Decat_Field("@@afcf", $edtVar);

		$FFAC=Decat_Field("@@ffac", $edtVar);
		$FFSB=Decat_Field("@@ffsb", $edtVar);
		$TFAC=Decat_Field("@@tfac", $edtVar);
		$TFSB=Decat_Field("@@tfsb", $edtVar);
		$AFAC=Decat_Field("@@afac", $edtVar);

		$FFLV=Decat_Field("@@fflv", $edtVar);
		$TFLV=Decat_Field("@@tflv", $edtVar);
		$AFLV=Decat_Field("@@aflv", $edtVar);

		$FTCO=Decat_Field("@@ftco", $edtVar);
		$FTFC=Decat_Field("@@ftfc", $edtVar);
		$TTCO=Decat_Field("@@ttco", $edtVar);
		$TTFC=Decat_Field("@@ttfc", $edtVar);
		$ATCF=Decat_Field("@@atcf", $edtVar);

		$FTAC=Decat_Field("@@ftac", $edtVar);
		$FTSB=Decat_Field("@@ftsb", $edtVar);
		$TTAC=Decat_Field("@@ttac", $edtVar);
		$TTSB=Decat_Field("@@ttsb", $edtVar);
		$ATAC=Decat_Field("@@atac", $edtVar);

		$FPAC=Decat_Field("@@fpac", $edtVar);
		$FPSB=Decat_Field("@@fpsb", $edtVar);
		$TPAC=Decat_Field("@@tpac", $edtVar);
		$TPSB=Decat_Field("@@tpsb", $edtVar);
		$APAC=Decat_Field("@@apac", $edtVar);
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$AFCF = "ALL";
		$AFAC = "ALL";
		$AFLV = "ALL";
		$ATCF = "ALL";
		$ATAC = "ALL";
		$APAC = "ALL";
	}
	if ($AFCF == "ALL") {$checked_AFCF="CHECKED";} else {$checked_AFCF="";}
	if ($AFAC == "ALL") {$checked_AFAC="CHECKED";} else {$checked_AFAC="";}
	if ($AFLV == "ALL") {$checked_AFLV="CHECKED";} else {$checked_AFLV="";}
	if ($ATCF == "ALL") {$checked_ATCF="CHECKED";} else {$checked_ATCF="";}
	if ($ATAC == "ALL") {$checked_ATAC="CHECKED";} else {$checked_ATAC="";}
	if ($APAC == "ALL") {$checked_APAC="CHECKED";} else {$checked_APAC="";}

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";
	print "\n     <table $contentTable> ";

	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">From</td> ";
	print "\n             <td class=\"colhdr\">To</td> ";
	print "\n             <td class=\"colhdr\">All</td> ";
	print "\n         </tr> ";

	print "\n         <tr><td class=\"colhdr\">Allocate From:</td></tr> ";

	$textOvr=SetTextOvr($Err_FFCO);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_FFFC); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TFCO); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TFFC); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_AFCF); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Company/Facility</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frAlcFrCompany\" value=\"$FFCO\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"frAlcFrFacility\" value=\"$FFFC\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frAlcFrCompany&amp;fldFac=frAlcFrFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toAlcFrCompany\" value=\"$TFCO\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"toAlcFrFacility\" value=\"$TFFC\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toAlcFrCompany&amp;fldFac=toAlcFrFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allAlcFrCoFac\" value=\"ALL\" $checked_AFCF onClick=\"if (this.checked) this.form.frAlcFrCompany.value='', this.form.frAlcFrFacility.value='', this.form.toAlcFrCompany.value='', this.form.toAlcFrFacility.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FFCO);
	DspErrMsg($Err_FFFC);
	DspErrMsg($Err_TFCO);
	DspErrMsg($Err_TFFC);
	DspErrMsg($Err_AFCF);

	$textOvr=SetTextOvr($Err_FFAC);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_FFSB); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TFAC); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TFSB); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_AFAC); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Account</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frAlcFrAccount\" value=\"$FFAC\" size=\"4\" maxlength=\"4\">";
	print "\n                                   - <input type=\"text\" name=\"frAlcFrSubAcct\" value=\"$FFSB\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=frAlcFrAccount&amp;subFld=frAlcFrSubAcct&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toAlcFrAccount\" value=\"$TFAC\" size=\"4\" maxlength=\"4\">";
	print "\n                                   - <input type=\"text\" name=\"toAlcFrSubAcct\" value=\"$TFSB\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=toAlcFrAccount&amp;subFld=toAlcFrSubAcct&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allAlcFrAcct\" value=\"ALL\" $checked_AFAC onClick=\"if (this.checked) this.form.frAlcFrAccount.value='', this.form.frAlcFrSubAcct.value='', this.form.toAlcFrAccount.value='', this.form.toAlcFrSubAcct.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FFAC);
	DspErrMsg($Err_FFSB);
	DspErrMsg($Err_TFAC);
	DspErrMsg($Err_TFSB);
	DspErrMsg($Err_AFAC);

	$textOvr=SetTextOvr($Err_FFLV);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TFLV); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_AFLV); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Level</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frAlcFrLevel\" value=\"$FFLV\" size=\"2\" maxlength=\"2\"></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toAlcFrLevel\" value=\"$TFLV\" size=\"2\" maxlength=\"2\"></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allAlcFrLevel\" value=\"ALL\" $checked_AFLV onClick=\"if (this.checked) this.form.frAlcFrLevel.value='', this.form.toAlcFrLevel.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FFLV);
	DspErrMsg($Err_TFLV);
	DspErrMsg($Err_AFLV);

	print "\n         <tr><td class=\"colhdr\">Allocate To:</td></tr> ";

	$textOvr=SetTextOvr($Err_FTCO);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_FTFC); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TTCO); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TTFC); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_ATCF); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Company/Facility</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frAlcToCompany\" value=\"$FTCO\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"frAlcToFacility\" value=\"$FTFC\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frAlcToCompany&amp;fldFac=frAlcToFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toAlcToCompany\" value=\"$TTCO\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"toAlcToFacility\" value=\"$TTFC\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toAlcToCompany&amp;fldFac=toAlcToFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allAlcToCoFac\" value=\"ALL\" $checked_ATCF onClick=\"if (this.checked) this.form.frAlcToCompany.value='', this.form.frAlcToFacility.value='', this.form.toAlcToCompany.value='', this.form.toAlcToFacility.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FTCO);
	DspErrMsg($Err_FTFC);
	DspErrMsg($Err_TTCO);
	DspErrMsg($Err_TTFC);
	DspErrMsg($Err_ATCF);

	$textOvr=SetTextOvr($Err_FTAC);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_FTSB); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TTAC); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TTSB); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_ATAC); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Account</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frAlcToAccount\" value=\"$FTAC\" size=\"4\" maxlength=\"4\">";
	print "\n                                   - <input type=\"text\" name=\"frAlcToSubAcct\" value=\"$FTSB\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=frAlcToAccount&amp;subFld=frAlcToSubAcct&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toAlcToAccount\" value=\"$TTAC\" size=\"4\" maxlength=\"4\">";
	print "\n                                   - <input type=\"text\" name=\"toAlcToSubAcct\" value=\"$TTSB\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=toAlcToAccount&amp;subFld=toAlcToSubAcct&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allAlcToAcct\" value=\"ALL\" $checked_ATAC onClick=\"if (this.checked) this.form.frAlcToAccount.value='', this.form.frAlcToSubAcct.value='', this.form.toAlcToAccount.value='', this.form.toAlcToSubAcct.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FTAC);
	DspErrMsg($Err_FTSB);
	DspErrMsg($Err_TTAC);
	DspErrMsg($Err_TTSB);
	DspErrMsg($Err_ATAC);

	print "\n         <tr><td class=\"colhdr\">Calculate Percent Based On:</td></tr> ";

	$textOvr=SetTextOvr($Err_FPAC);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_FPSB); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TPAC); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TPSB); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_APAC); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Account</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frAlcPcAccount\" value=\"$FPAC\" size=\"4\" maxlength=\"4\">";
	print "\n                                   - <input type=\"text\" name=\"frAlcPcSubAcct\" value=\"$FPSB\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=frAlcPcAccount&amp;subFld=frAlcPcSubAcct&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toAlcPcAccount\" value=\"$TPAC\" size=\"4\" maxlength=\"4\">";
	print "\n                                   - <input type=\"text\" name=\"toAlcPcSubAcct\" value=\"$TPSB\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=toAlcPcAccount&amp;subFld=toAlcPcSubAcct&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allAlcPcAcct\" value=\"ALL\" $checked_APAC onClick=\"if (this.checked) this.form.frAlcPcAccount.value='', this.form.frAlcPcSubAcct.value='', this.form.toAlcPcAccount.value='', this.form.toAlcPcSubAcct.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FPAC);
	DspErrMsg($Err_FPSB);
	DspErrMsg($Err_TPAC);
	DspErrMsg($Err_TPSB);
	DspErrMsg($Err_APAC);

	print "\n     </table> ";

	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	require 'SubmitScheduleBottom.php';
	print "\n $hrTagAttr ";

	if ($focusField !="") {
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n document.Chg.$focusField.focus(); ";
		print "\n </script> ";
	}
	print "\n </form>";
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}


if ($tag == "Edit_Data") {

	$edtVar= "";

	Concat_Field("@@idcr", strtoupper($_POST['currencyID']));
	Concat_Field("@@ffco", $_POST['frAlcFrCompany']);
	Concat_Field("@@fffc", $_POST['frAlcFrFacility']);
	Concat_Field("@@tfco", $_POST['toAlcFrCompany']);
	Concat_Field("@@tffc", $_POST['toAlcFrFacility']);
	if (!isset($_POST['allAlcFrCoFac'])) {$_POST['allAlcFrCoFac']="";}  Concat_Field("@@afcf", $_POST['allAlcFrCoFac']);

	Concat_Field("@@ffac", $_POST['frAlcFrAccount']);
	Concat_Field("@@ffsb", $_POST['frAlcFrSubAcct']);
	Concat_Field("@@tfac", $_POST['toAlcFrAccount']);
	Concat_Field("@@tfsb", $_POST['toAlcFrSubAcct']);
	if (!isset($_POST['allAlcFrAcct'])) {$_POST['allAlcFrAcct']="";}  Concat_Field("@@afac", $_POST['allAlcFrAcct']);

	Concat_Field("@@fflv", $_POST['frAlcFrLevel']);
	Concat_Field("@@tflv", $_POST['toAlcFrLevel']);
	if (!isset($_POST['allAlcFrLevel'])) {$_POST['allAlcFrLevel']="";}  Concat_Field("@@aflv", $_POST['allAlcFrLevel']);

	Concat_Field("@@ftco", $_POST['frAlcToCompany']);
	Concat_Field("@@ftfc", $_POST['frAlcToFacility']);
	Concat_Field("@@ttco", $_POST['toAlcToCompany']);
	Concat_Field("@@ttfc", $_POST['toAlcToFacility']);
	if (!isset($_POST['allAlcToCoFac'])) {$_POST['allAlcToCoFac']="";}  Concat_Field("@@atcf", $_POST['allAlcToCoFac']);

	Concat_Field("@@ftac", $_POST['frAlcToAccount']);
	Concat_Field("@@ftsb", $_POST['frAlcToSubAcct']);
	Concat_Field("@@ttac", $_POST['toAlcToAccount']);
	Concat_Field("@@ttsb", $_POST['toAlcToSubAcct']);
	if (!isset($_POST['allAlcToAcct'])) {$_POST['allAlcToAcct']="";}  Concat_Field("@@atac", $_POST['allAlcToAcct']);

	Concat_Field("@@fpac", $_POST['frAlcPcAccount']);
	Concat_Field("@@fpsb", $_POST['frAlcPcSubAcct']);
	Concat_Field("@@tpac", $_POST['toAlcPcAccount']);
	Concat_Field("@@tpsb", $_POST['toAlcPcSubAcct']);
	if (!isset($_POST['allAlcPcAcct'])) {$_POST['allAlcPcAcct']="";}  Concat_Field("@@apac", $_POST['allAlcPcAcct']);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HGLALS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
