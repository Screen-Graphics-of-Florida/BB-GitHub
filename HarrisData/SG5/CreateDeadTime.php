<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fldPlant		=	(isset($_GET['fldPlant']))			?	$_GET['fldPlant']		:	0;
$fldPltName		=	(isset($_GET['fldPltName']))		?	$_GET['fldPltName']		:	"";
$flddept		=	(isset($_GET['flddept']))			?	$_GET['flddept']		:	"";
$fldWC			=	(isset($_GET['fldWC']))				?	$_GET['fldWC']			:	"";
$fldDesc		=	(isset($_GET['fldDesc']))			?	$_GET['fldDesc']		:	"";

$backHome		=	(isset($_GET['backHome']))			?	$_GET['backHome']			:	null;
$errFound		=	(isset($_GET['errFound']))			?	$_GET['errFound']			:	null;
$wrnVar			=	(isset($_GET['wrnVar']))			?	$_GET['wrnVar']				:	null;
$reportSelType	=	(isset($_GET['reportSelType']))		?	$_GET['reportSelType']		:	"";
$jobSbmSched	=	(isset($_GET['jobSbmSched']))		?	$_GET['jobSbmSched']		:	null;
$resetSelectionFlag	=	(isset($_GET['resetSelectionFlag']))	?	$_GET['resetSelectionFlag']		:	null;
$rtvSelection	=	(isset($_GET['rtvSelection']))		?	$_GET['rtvSelection']		:	null;
$saveSelection	=	(isset($_GET['saveSelection']))		?	$_GET['saveSelection']		:	null;
$scheduleJobSwitch	=	(isset($_GET['scheduleJobSwitch']))		?	$_GET['scheduleJobSwitch']			:	null;
$selScheduleJob	=	(isset($_GET['selScheduleJob']))	?	$_GET['selScheduleJob']		:	null;
$submitSchedule	=	(isset($_GET['submitSchedule']))	?	$_GET['submitSchedule']		:	null;


require_once 'SetLibraryList.php';
require_once "ETControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';


$page_title            = "Create Dead Time";
$scriptName            = "CreateDeadTime.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CETCDT";
$submitEnvProgram      = "HETCDT";
$submitEnvPrinter      = "HETCDTPF";
$submitScheduleScript  = "";
$applicationID         = "ET";
$forPlant              = 0;

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
	print "\n   if (editNum(document.Chg.frSchd, 3, 0) ";
	print "\n    && editNum(document.Chg.toSchd, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frSchd,        document.Chg.toSchd,      document.Chg.operSchd, 3) ";
	print "\n    && editdate(document.Chg.frTranDate) ";
	print "\n    && editdate(document.Chg.toTranDate) ";
	print "\n    && editFromToOper(document.Chg.frTranDate,    document.Chg.toTranDate,  document.Chg.operTranDate, 'D') ";
	print "\n    && editNum(document.Chg.frComp, 2, 0) ";
	print "\n    && editNum(document.Chg.toComp, 2, 0) ";
	print "\n    && editFromToOper(document.Chg.frComp,    document.Chg.toComp,  document.Chg.operComp, 2) ";
	print "\n    && editNum(document.Chg.frFacl, 4, 0) ";
	print "\n    && editNum(document.Chg.toFacl, 4, 0) ";
	print "\n    && editFromToOper(document.Chg.frFacl,    document.Chg.toFacl,  document.Chg.operFacl, 4) ";
	print "\n    && editFromToOper(document.Chg.frHDpt,    document.Chg.toHDpt,  document.Chg.operHDpt, 'A') ";
	print "\n    && editNum(document.Chg.frEmpl, 5, 0) ";
	print "\n    && editNum(document.Chg.toEmpl, 5, 0) ";
	print "\n    && editFromToOper(document.Chg.frEmpl,    document.Chg.toEmpl,  document.Chg.operEmpl, 5) ";
	if ($HDMERL > 0)  {print "\n    && editNum(document.Chg.frPlnt, 3, 0) ";
	print "\n    && editNum(document.Chg.toPlnt, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frPlnt,    document.Chg.toPlnt,  document.Chg.operPlnt, 3) ";
	print "\n    && editFromToOper(document.Chg.frMDpt,    document.Chg.toMDpt,  document.Chg.operMDpt, 'A') ";
	print "\n    && editFromToOper(document.Chg.frWc,    document.Chg.toWc,  document.Chg.operWc, 'A') ";}
	print "\n    ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "CREATEDEADTIME";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			$Err_OPYC=DecatErr_Field("@@opyc", "OverridePayCode");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$OPYC=Decat_Field("@@opyc", $edtVar);

		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		if ($HDMERL > 0) {
			if (is_null($frPlnt) && is_null($toPlnt)) {
				$returnValue=RtvDftPlant();
				$forPlant = $returnValue['dftPltNumber'];
				$frPlnt = $forPlant;
				$toPlnt = $forPlant;
			}
		}

	}


	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	if ($wildCardDisplay != "") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#CurrentFilterCriteria\">Current Filter Criteria</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#RefineFilterCriteria\">Refine Filter Criteria</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#OverridePayCode\">Override Pay Code</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	print $hrTagAttr;

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

	require_once 'AdvSearchTopReport.php';

	$operNbr = "operSchd";
	print "\n <tr><td class=\"dsphdr\">Schedule</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frSchd\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ScheduleSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frSchd&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toSchd\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ScheduleSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toSchd&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Transaction Date","frTranDate","toTranDate","operTranDate","opersel_num2_short","D","6","6");

	$operNbr = "operComp";
	print "\n <tr><td class=\"dsphdr\">Company Number</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frComp\" size=\"2\" maxlength=\"2\"> ";
	if ($HDPERL>0 || $HDPRRL>0) {
	    print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frComp&amp;fldFac=frFacl&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	} else {	    
	    print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frComp&amp;fldFac=frFacl&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	}
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toComp\" size=\"2\" maxlength=\"2\">";
	if ($HDPERL>0 || $HDPRRL>0) {
    	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toComp&amp;fldFac=toFacl&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	} else {	    
    	print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toComp&amp;fldFac=toFacl&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	}
	print "\n </tr>";

	$operNbr = "operFacl";
	print "\n <tr><td class=\"dsphdr\">Facility Number</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frFacl\" size=\"4\" maxlength=\"4\"> ";
	print "\n                             </td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toFacl\" size=\"4\" maxlength=\"4\">";
	print "\n                             </td> ";
	print "\n </tr>";

	$operNbr = "operHDpt";
	print "\n <tr><td class=\"dsphdr\">Home Department</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	if ($HDPRRL > 0 or $HDPERL > 0) {
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frHDpt\" size=\"5\" maxlength=\"5\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DepartmentSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frHDpt&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toHDpt\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DepartmentSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toHDpt&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	}  else  {
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frHDpt\" size=\"5\" maxlength=\"5\"> ";
		print "\n                                </td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toHDpt\" size=\"5\" maxlength=\"5\"> ";
		print "\n                                </td> ";
	}
	print "\n </tr>";


	$operNbr = "operEmpl";
	print "\n <tr><td class=\"dsphdr\">Employee Number</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frEmpl\" size=\"5\" maxlength=\"5\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldEmpl=frEmpl&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toEmpl\" size=\"5\" maxlength=\"5\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldEmpl=toEmpl&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	if ($HDMERL > 0) {


		$operNbr = "operPlnt";
		print "\n <tr><td class=\"dsphdr\">Plant Number</td>";
		print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frPlnt\" value=\"$frPlnt\" size=\"3\" maxlength=\"3\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frPlnt&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toPlnt\" value=\"$toPlnt\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toPlnt&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td><INPUT TYPE=\"hidden\" ID=\"frPltName\" NAME=\"frPltName\"></td> ";
		print "\n     <td><INPUT TYPE=\"hidden\" ID=\"toPltName\" NAME=\"toPltName\"></td> ";
		print "\n </tr>";


		$forPlant = $frPlnt;


		$operNbr = "operMDpt";
		print "\n <tr><td class=\"dsphdr\">Manufacturing Department</td>";
		print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frMDpt\" size=\"5\" maxlength=\"5\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;tag=REPORT&amp;forPlant=$forPlant&amp;docName=Chg&amp;fldPlant=frPlnt&amp;fldPltName=frPltName&amp;flddept=frMDpt&amp;fldWC=frWc&amp;fldDesc=none \" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toMDpt\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;tag=REPORT&amp;forPlant=forPlant&amp;docName=Chg&amp;fldPlant=toPlnt&amp;fldPltName=toPltName&amp;flddept=toMDpt&amp;fldWC=toWc&amp;fldDesc=none \" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n </tr>";


		$operNbr = "operWc";
		print "\n <tr><td class=\"dsphdr\">Work Center</td>";
		print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frWc\" size=\"5\" maxlength=\"5\"> ";
		print "\n                            </td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toWc\" size=\"5\" maxlength=\"5\">";
		print "\n                            </td> ";
		print "\n </tr>";

	}

	print "\n </table> ";
	print "\n </fieldset> ";



	print "\n <a name=\"OverridePayCode\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Override Pay Code</legend> ";
	require 'TopOfForm.php'; 
	print "\n     <table $contentTable> ";

	$operNbr = "operOpyc";
	print "\n <tr><td class=\"dsphdr\">Override Pay Code</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"operOpyc\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=operOpyc&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";


	print "\n     </table> ";
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

	if ($_POST['updateSearch'] == "Y") {$_SESSION['gotoFilter']="Y";}

	$returnValue=Range_WildCard("EMSCHD", "Schedule", $_POST['frSchd'], $_POST['toSchd'], "", $_POST['operSchd'], "N");
	$returnValue=Range_WildCard("LBDATE", "Transaction Date", $_POST['frTranDate'], $_POST['toTranDate'], "", $_POST['operTranDate'], "I");
	$returnValue=Range_WildCard("EMCOMP", "Company Number", $_POST['frComp'], $_POST['toComp'], "", $_POST['operComp'], "N");
	$returnValue=Range_WildCard("EMFACL", "Facility Number", $_POST['frFacl'], $_POST['toFacl'], "", $_POST['operFacl'], "N");
	$returnValue=Range_WildCard("EMDEPT", "Home Department", $_POST['frHDpt'], $_POST['toHDpt'], "U", $_POST['operHDpt'], "A");
	$returnValue=Range_WildCard("EMEMPL", "Employee Number", $_POST['frEmpl'], $_POST['toEmpl'], "", $_POST['operEmpl'], "N");
	if ($HDMERL > 0) {
		$returnValue=Range_WildCard("EMPLNT", "Plant Number", $_POST['frPlnt'], $_POST['toPlnt'], "", $_POST['operPlnt'], "N");
		$returnValue=Range_WildCard("EMMDPT", "Manufacturing Department", $_POST['frMDpt'], $_POST['toMDpt'], "U", $_POST['operMDpt'], "A");
		$returnValue=Range_WildCard("EMWC", "Work Center", $_POST['frWc'], $_POST['toWc'], "U", $_POST['operWc'], "A");
	}
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);

	Concat_Field("@@opyc", strtoupper($_POST['operOpyc']));

	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HETCDS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
