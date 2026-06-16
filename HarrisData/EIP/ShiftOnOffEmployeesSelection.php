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
require_once "ETControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'WildCardAcctInclude.php';

$page_title            = "Shift On/Off Employees Selection";
$scriptName            = "ShiftOnOffEmployeesSelection.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CETSOF";
$submitEnvProgram      = "HETSOF";
$submitEnvPrinter      = "";
$submitScheduleScript  = "";
$applicationID         = "ET";

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
	print "\n   if (document.Chg.dtcl.value == \"\"  ";
	print "\n      ) {alert(\"Must make a selection\"); return false;} ";

	print "\n   if (editNum(document.Chg.fsch, 3, 0) ";
	print "\n    && editNum(document.Chg.tsch, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.fsch,        document.Chg.tsch,      document.Chg.osch, 3) ";
	print "\n    && editNum(document.Chg.fcmp, 2, 0) ";
	print "\n    && editNum(document.Chg.tcmp, 2, 0) ";
	print "\n    && editNum(document.Chg.ffac, 4, 0) ";
	print "\n    && editNum(document.Chg.tfac, 4, 0) ";
	print "\n    && editFromToOper2(document.Chg.fcmp,    document.Chg.ffac, document.Chg.tcmp,    document.Chg.tfac,  document.Chg.ocmp, 2, 4) ";
	print "\n    && editFromToOper(document.Chg.fhdp,    document.Chg.thdp,  document.Chg.ohdp, 'A') ";
	print "\n    && editNum(document.Chg.fgrp, 5, 0) ";
	print "\n    && editNum(document.Chg.tgrp, 5, 0) ";
	print "\n    && editFromToOper(document.Chg.fgrp,    document.Chg.tgrp,  document.Chg.ogrp, 5) ";
	print "\n    && editNum(document.Chg.femp, 5, 0) ";
	print "\n    && editNum(document.Chg.temp, 5, 0) ";
	print "\n    && editFromToOper(document.Chg.femp,    document.Chg.temp,  document.Chg.oemp, 5) ";
	if ($HDMERL > 0)  {print "\n    && editNum(document.Chg.fplt, 3, 0) ";
		print "\n    && editNum(document.Chg.tplt, 3, 0) ";
		print "\n    && editFromToOper(document.Chg.fplt,    document.Chg.tplt,  document.Chg.oplt, 3) ";
		print "\n    && editFromToOper(document.Chg.fmdp,    document.Chg.tmdp,  document.Chg.omdp, 'A') ";
		print "\n    && editFromToOper(document.Chg.fwc,    document.Chg.twc,  document.Chg.owc, 'A') ";}
	print "\n    ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "SHIFTONOFFEMPLOYEES";
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

			$Err_DTCL=DecatErr_Field("@@dtcl", "DataCollectionCode");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$dtcl=Decat_Field("@@dtcl", $edtVar);

		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		if ($HDMERL > 0) {
			if (is_null($fplt) && is_null($tplt)) {
				$returnValue=RtvDftPlant();
				$forPlant = $returnValue['dftPltNumber'];
				if ($forPlant != '000') {
					$fplt = $forPlant;
					$tplt = $forPlant;
				}
			}
		}

	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	if ($wildCardDisplay != "") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#CurrentFilterCriteria\">Current Filter Criteria</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#Select\">Select</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#RefineFilterCriteria\">Refine Filter Criteria</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	print $hrTagAttr;

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

	print "\n <a id=\"Select\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Select</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	print "\n     <tr><td class=\"dsphdr\">Shift</td>";
	if ($dtcl == "10") {
		print "\n     <td class=\"inputcode\"><input name=\"dtcl\" type=\"radio\" VALUE='10' CHECKED >On &nbsp; <input  name=\"dtcl\" type=\"radio\" VALUE='15' >Off</td> " ;
	} elseif ($dtcl == "15") {
		print "\n     <td class=\"inputcode\"><input name=\"dtcl\" type=\"radio\" VALUE='10' >On &nbsp; <input  name=\"dtcl\" type=\"radio\" VALUE='15' CHECKED >Off </td> " ;
	} elseif ($dtcl== "") {
		print "\n     <td class=\"inputcode\"><input name=\"dtcl\" type=\"radio\" VALUE='10' >On &nbsp; <input  name=\"dtcl\" type=\"radio\" VALUE='15'  >Off </td> " ;
	}
	print "\n   </tr> " ;	print "\n     </table> ";
	print "\n </fieldset> ";

	require_once 'AdvSearchTopReport.php';

	$operNbr = "osch";
	print "\n <tr><td class=\"dsphdr\">Schedule</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fsch\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ScheduleSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=fsch&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"tsch\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ScheduleSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=tsch&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	$operNbr = "ocmp";
	print "\n <tr><td class=\"dsphdr\">Company/Facility</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fcmp\" size=\"2\" maxlength=\"2\">";
	print "\n                           / <input type=\"text\" name=\"ffac\" size=\"4\" maxlength=\"4\">";
	if ($HDPERL>0 || $HDPRRL>0) {
		print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=fcmp&amp;fldFac=ffac&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	} else {
		print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=fcmp&amp;fldFac=ffac&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	}
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"tcmp\" size=\"2\" maxlength=\"2\">";
	print "\n                           / <input type=\"text\" name=\"tfac\" size=\"4\" maxlength=\"4\">";
	if ($HDPERL>0 || $HDPRRL>0) {
		print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=tcmp&amp;fldFac=tfac&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	} else {
		print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=tcmp&amp;fldFac=tfac&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	}
	print "\n </tr>";

	$operNbr = "ohdp";
	print "\n <tr><td class=\"dsphdr\">Home Department</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	if ($HDPRRL > 0 or $HDPERL > 0) {
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fhdp\" size=\"5\" maxlength=\"5\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DepartmentSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=fhdp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"thdp\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DepartmentSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=thdp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	}  else  {
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fhdp\" size=\"5\" maxlength=\"5\"> ";
		print "\n                                </td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"thdp\" size=\"5\" maxlength=\"5\"> ";
		print "\n                                </td> ";
	}
	print "\n </tr>";

	$operNbr = "ogrp";
	print "\n <tr><td class=\"dsphdr\">Group Number</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fgrp\" size=\"5\" maxlength=\"5\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}GroupNumberSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=fgrp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"tgrp\" size=\"5\" maxlength=\"5\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}GroupNumberSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=tgrp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	$operNbr = "oemp";
	print "\n <tr><td class=\"dsphdr\">Employee Number</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"femp\" size=\"5\" maxlength=\"5\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldEmpl=femp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"temp\" size=\"5\" maxlength=\"5\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldEmpl=temp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	if ($HDMERL > 0) {

		$operNbr = "oplt";
		print "\n <tr><td class=\"dsphdr\">Plant Number</td>";
		print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fplt\" value=\"$fplt\" size=\"3\" maxlength=\"3\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=fplt&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"tplt\" value=\"$tplt\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=tplt&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td><INPUT TYPE=\"hidden\" ID=\"frPltName\" NAME=\"frPltName\"></td> ";
		print "\n     <td><INPUT TYPE=\"hidden\" ID=\"toPltName\" NAME=\"toPltName\"></td> ";
		print "\n </tr>";

		$forPlant = $fplt;

		$operNbr = "omdp";
		print "\n <tr><td class=\"dsphdr\">Manufacturing Department</td>";
		print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fmdp\" size=\"5\" maxlength=\"5\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;tag=REPORT&amp;forPlant=$forPlant&amp;docName=Chg&amp;fldPlant=fplt&amp;fldPltName=frPltName&amp;flddept=fmdp&amp;fldWC=fwc&amp;fldDesc=none \" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"tmdp\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;tag=REPORT&amp;forPlant=$forPlant&amp;docName=Chg&amp;fldPlant=tplt&amp;fldPltName=toPltName&amp;flddept=tmdp&amp;fldWC=twc&amp;fldDesc=none \" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n </tr>";

		$operNbr = "owc";
		print "\n <tr><td class=\"dsphdr\">Work Center</td>";
		print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fwc\" size=\"5\" maxlength=\"5\"> ";
		print "\n                            </td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"twc\" size=\"5\" maxlength=\"5\">";
		print "\n                            </td> ";
		print "\n </tr>";
	}

	print "\n </table> ";
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

	$returnValue=Range_WildCard("EMSCHD", "Schedule", $_POST['fsch'], $_POST['tsch'], "", $_POST['osch'], "N");
	$returnValue=Range_WildCard_CoFac("EMCOMP", "EMFACL", "Company/Facility", $_POST['fcmp'], $_POST['ffac'], $_POST['tcmp'], $_POST['tfac'], $_POST['ocmp']);
	$returnValue=Range_WildCard("EMDEPT", "Home Department", $_POST['fhdp'], $_POST['thdp'], "U", $_POST['ohdp'], "A");
	$returnValue=Range_WildCard("EMHGRP", "Group Number", $_POST['fgrp'], $_POST['tgrp'], "", $_POST['ogrp'], "N");
	$returnValue=Range_WildCard("EMEMPL", "Employee Number", $_POST['femp'], $_POST['temp'], "", $_POST['oemp'], "N");
	if ($HDMERL > 0) {
		$returnValue=Range_WildCard("EMPLNT", "Plant Number", $_POST['fplt'], $_POST['tplt'], "", $_POST['oplt'], "N");
		$returnValue=Range_WildCard("EMMDPT", "Manufacturing Department", $_POST['fmdp'], $_POST['tmdp'], "U", $_POST['omdp'], "A");
		$returnValue=Range_WildCard("EMWC", "Work Center", $_POST['fwc'], $_POST['twc'], "U", $_POST['owc'], "A");
	}
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);

	Concat_Field("@@dtcl", $_POST['dtcl']);

	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HETSOF_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}
?>	
