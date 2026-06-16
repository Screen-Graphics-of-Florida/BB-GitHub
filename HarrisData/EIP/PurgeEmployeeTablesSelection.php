<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$backHome           =   (isset($_GET['backHome']))           ?  $_GET['backHome']           :   null;
$errFound           =   (isset($_GET['errFound']))           ?  $_GET['errFound']           :   null;
$wrnVar             =   (isset($_GET['wrnVar']))             ?  $_GET['wrnVar']             :   null;
$reportSelType      =   (isset($_GET['reportSelType']))      ?  $_GET['reportSelType']      :   null;
$jobSbmSched        =   (isset($_GET['jobSbmSched']))        ?  $_GET['jobSbmSched']        :   null;
$resetSelectionFlag =   (isset($_GET['resetSelectionFlag'])) ?  $_GET['resetSelectionFlag'] :   null;
$rtvSelection       =   (isset($_GET['rtvSelection']))       ?  $_GET['rtvSelection']       :   null;
$saveSelection      =   (isset($_GET['saveSelection']))      ?  $_GET['saveSelection']      :   null;
$scheduleJobSwitch  =   (isset($_GET['scheduleJobSwitch']))  ?  $_GET['scheduleJobSwitch']  :   null;
$selScheduleJob     =   (isset($_GET['selScheduleJob']))     ?  $_GET['selScheduleJob']     :   null;
$submitSchedule     =   (isset($_GET['submitSchedule']))     ?  $_GET['submitSchedule']     :   null;
$lupd			    =	(isset($_GET['lupd']))			     ?	$_GET['lupd']			    :	" ";

require_once 'SetLibraryList.php';


require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'WildCardAcctInclude.php';

$page_title            = "Purge Employee Tables Selection";
$scriptName            = "PurgeEmployeeTablesSelection.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CHRPES";
$submitEnvProgram      = "HHRPGE";
$submitEnvPrinter      = "HHRPGEPF";
$submitScheduleScript  = "";
$applicationID         = "PE";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

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
	
	print "\n   if (editNum(document.Chg.frHRCo, 2, 0) ";
	print "\n    && editNum(document.Chg.toHRCo, 2, 0) ";
	print "\n    && editFromToOper(document.Chg.frHRCo,   document.Chg.toHRCo,   document.Chg.operHRCo, 2) ";
	print "\n    && editNum(document.Chg.frHREmpl, 9, 0) ";
	print "\n    && editNum(document.Chg.toHREmpl, 9, 0) ";
	print "\n    && editFromToOper(document.Chg.frHREmpl,   document.Chg.toHREmpl,   document.Chg.operHREmpl, 9) ";
	print "\n    && editNum(document.Chg.frCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.toCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.frFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.toFacility, 4, 0) ";
	print "\n    && editFromToOper2(document.Chg.frCompany,   document.Chg.frFacility,   document.Chg.toCompany, document.Chg.toFacility, document.Chg.operCompany, 2, 4) ";
	print "\n    && editNum(document.Chg.frEmpl, 5, 0) ";
	print "\n    && editNum(document.Chg.toEmpl, 5, 0) ";
	print "\n    && editFromToOper(document.Chg.frEmpl,   document.Chg.toEmpl,   document.Chg.operEmpl, 5) ";
	print "\n    && editdate(document.Chg.frTermDate) ";
	print "\n    && editdate(document.Chg.toTermDate) ";
	print "\n    && editFromToOper(document.Chg.frTermDate,    document.Chg.toTermDate,    document.Chg.operTermDate, 'D') ";
	print "\n    ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "PURGEEMPLOYEETABLESSELECTION";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="frHRCo";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);
			
			$Err_EMSG=DecatErr_Field("@@emsg", "errmsg");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$lupd		   =Decat_Field("@@lupd", $edtVar);

	
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		
	
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	if ($wildCardDisplay != "") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#CurrentFilterCriteria\">Current Filter Criteria</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#RefineFilterCriteria\">Refine Filter Criteria</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	print $hrTagAttr;

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

	
	if ($Err_EMSG != ' ') {
	print "\n     <table $contentTable> ";
	$textOvr=SetTextOvr($Err_EMSG);
	print "\n <tr><td class=\"legendTitle\"> </td></tr>";
	DspErrMsg($Err_EMSG);
	print "\n     </table> ";
	}
	
	if ($HDMERL > 0 || $HDPDRL > 0 || $HDETRL > 0) {
	print "\n <a name=\"ListONLY/Update\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\"></legend> ";
	print "\n     <table $contentTable> ";
	
	
	if ($lupd == " " || $lupd=="R" || $lupd=="N") {
		print "\n     <td class=\"inputcode\"><input name=\"lupd\" type=\"radio\" VALUE='R' CHECKED >Report Only &nbsp; <input  name=\"lupd\" type=\"radio\" VALUE='P' >Report and Purge</td> " ;
	} else {
		print "\n     <td class=\"inputcode\"><input name=\"lupd\" type=\"radio\" VALUE='R' >Report Only &nbsp; <input  name=\"lupd\" type=\"radio\" VALUE='P' CHECKED >Report and Purge </td> " ;
	}
	print "\n     </table> ";
	print "\n </fieldset> ";
	}
	
	print "\n <a name=\"IncludeWarning\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Warning</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	print "\n      <tr><td class=\"text\">The Purge Employee option will permanently remove data from various HarrisData tables for properly deleted/deactivated employees.</td></tr>";
	print "\n      <tr><td class=\"text\">&nbsp</td></tr>";
	print "\n      <tr><td class=\"text\">Prior to executing this option, refer to the documentation section Human Resources Information System -> Housekeeping -> Purge Employee for critical processing information. </td></tr>";
	print "\n     </table> ";
	print "\n </fieldset> ";

	
		
	require_once 'AdvSearchTopReport.php';

	
	
	$operNbr = "operHRCo";
	print "\n <tr><td class=\"dsphdr\">H/R Company</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frHRCo\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCompanySearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frHRCo&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toHRCo\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCompanySearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toHRCo&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";
	
	$operNbr = "operHREmpl";
	print "\n <tr><td class=\"dsphdr\">H/R Employee</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frHREmpl\" size=\"9\" maxlength=\"9\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frCompany&amp;fldFacl=frFacility&amp;fldHRCo=frHRCo&amp;fldHREmpl=frHREmpl&amp;fldEmplName=hrName&amp;fldCoName=hrCoDescription\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toHREmpl\" size=\"9\" maxlength=\"9\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFacl=toFacility&amp;fldHRCo=toHRCo&amp;fldHREmpl=toHREmpl&amp;fldEmplName=hrName&amp;fldCoName=hrCoDescription\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	$operNbr = "operCompany";
	print "\n <tr><td class=\"dsphdr\">P/R Company/Facility</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frCompany\" size=\"2\" maxlength=\"2\">";
	print "\n                           / <input type=\"text\" name=\"frFacility\" size=\"4\" maxlength=\"4\">";
	if ($HDPERL>0 || $HDPRRL>0) {
		print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frCompany&amp;fldFac=frFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	} else {
		print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frCompany&amp;fldFac=frFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		}
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toCompany\" size=\"2\" maxlength=\"2\">";
	print "\n                           / <input type=\"text\" name=\"toFacility\" size=\"4\" maxlength=\"4\">";
	if ($HDPERL>0 || $HDPRRL>0) {
		print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFac=toFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	} else {
		print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFac=toFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		}
	print "\n </tr>";
	
	$operNbr = "operEmpl";
	print "\n <tr><td class=\"dsphdr\">P/R Employee</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frEmpl\" size=\"5\" maxlength=\"5\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frCompany&amp;fldFacl=frFacility&amp;fldEmpl=frEmpl&amp;fldHRCo=frHRCo&amp;fldHREmpl=frHREmpl&amp;fldEmplName=prName&amp;fldCoName=prCoDescription\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toEmpl\" size=\"5\" maxlength=\"5\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFacl=toFacility&amp;fldEmpl=toEmpl&amp;fldHRCo=toHRCo&amp;fldHREmpl=toHREmpl&amp;fldEmplName=prName&amp;fldCoName=prCoDescription\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	
	Build_AdvSrch_Entry("Termination Date","frTermDate","toTermDate","operTermDate","opersel_num2_short","D","6","6");

	print "\n </table> ";
	print "\n </fieldset> ";

	
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
	if ($_SESSION['gotoFilter']=="Y") {
		$_SESSION['gotoFilter']="";
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n window.location.hash='CurrentFilterCriteria'; ";
		print "\n </script> ";
	}
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "WILDCARD" || $tag == "Edit_Data") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$errFound = "";
	
	if ($_POST['updateSearch'] == "Y") {$_SESSION['gotoFilter']="Y";}

	//if ($wildCardTemp == "" && $wildCardSearch == "") {
	//	$returnValue=Range_WildCard("EMACT", "ActivityCode", "D", "", "", "=", "A");
	//} 
	
	$returnValue=Range_WildCard("EMPECP", "HRCompany", $_POST['frHRCo'], $_POST['toHRCo'], "", $_POST['operHRCo'], "N");
	$returnValue=Range_WildCard("EMPEMP", "HREmployee", $_POST['frHREmpl'], $_POST['toHREmpl'], "", $_POST['operHREmpl'], "N");
	$returnValue=Range_WildCard_CoFac("EMCOMP", "EMFACL", "Company/Facility", $_POST['frCompany'], $_POST['frFacility'], $_POST['toCompany'], $_POST['toFacility'], $_POST['operCompany']);
	$returnValue=Range_WildCard("EMEMPL", "Employee", $_POST['frEmpl'], $_POST['toEmpl'], "", $_POST['operEmpl'], "N");
	$returnValue=Range_WildCard("EMTRDT", "Termination Date", $_POST['frTermDate'], $_POST['toTermDate'], "", $_POST['operTermDate'], "D");
	require_once 'WildCardUpdateReport.php';

	if ($lupd == "R" and ($HDMERL > 0 || $HDPDRL > 0 || $HDETRL > 0)) {
	$lupd="N";
	}
	
	$emsg= "";
	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);
	Concat_Field("@@lupd", $_POST['lupd']);
	Concat_Field("@@emsg", $emsg);
	
	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HHRPGE_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
