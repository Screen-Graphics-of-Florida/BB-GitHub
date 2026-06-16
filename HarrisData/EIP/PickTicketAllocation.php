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

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title            = "Pick Ticket Allocation";
$scriptName            = "PickTicketAllocation.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "COEPAS";
$submitEnvProgram      = "HOEPAS";
$submitEnvPrinter      = "";
$submitScheduleScript  = "";
$applicationID         = "OE";

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
	print "\n   if (editNum(document.Chg.frWhs, 3, 0) ";
	print "\n    && editNum(document.Chg.toWhs, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frItem, document.Chg.toItem, document.Chg.operItem, 'A') ";
	print "\n    && editFromToOper(document.Chg.frWhs, document.Chg.toWhs, document.Chg.operWhs, 3) ";
	print "\n    && editFromToOper(document.Chg.frProdClass, document.Chg.toProdClass, document.Chg.operProdClass, 'A') ";
	print "\n    && editFromToOper(document.Chg.frProdGroup, document.Chg.toProdGroup, document.Chg.operProdGroup, 'A') ";
	print "\n    && editdate(document.Chg.frReqDate) ";
	print "\n    && editdate(document.Chg.toReqDate) ";
	print "\n    && editFromToOper(document.Chg.frReqDate,    document.Chg.toReqDate,    document.Chg.operReqDate, 'D') ";
	print "\n       ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "PICKTICKETALLOC";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="DistPeriod";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$DPER="";
		$TOTL="N";
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	if ($wildCardDisplay != "") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#CurrentFilterCriteria\">Current Filter Criteria</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#RefineFilterCriteria\">Refine Filter Criteria</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	print $hrTagAttr;

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

	require_once 'AdvSearchTopReport.php';

	$operNbr = "operItem";
	print "\n <tr><td class=\"dsphdr\">Item Number</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frItem\" size=\"15\" maxlength=\"15\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ItemSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frItem&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toItem\" size=\"15\" maxlength=\"15\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ItemSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toItem&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operWhs";
	print "\n <tr><td class=\"dsphdr\">Warehouse</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frWhs\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frWhs&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toWhs\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toWhs&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	$operNbr = "operProdClass";
	print "\n <tr><td class=\"dsphdr\">Product Class</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frProdClass\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ProdClassSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frProdClass&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toProdClass\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ProdClassSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toProdClass&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operProdGroup";
	print "\n <tr><td class=\"dsphdr\">Product Group</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frProdGroup\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ProductGroupSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frProdGroup&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toProdGroup\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ProductGroupSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toProdGroup&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Required Date","frReqDate","toReqDate","operReqDate","opersel_num2_short","D","6","6");

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

	if ($_POST['updateSearch'] == "Y") {$_SESSION['gotoFilter']="Y";}

	$returnValue=Range_WildCard("ODITEM", "Item Number", $_POST['frItem'], $_POST['toItem'], "U", $_POST['operItem'], "A");
	$returnValue=Range_WildCard("ODWH", "Warehouse", $_POST['frWhs'], $_POST['toWhs'], "", $_POST['operWhs'], "N");
	$returnValue=Range_WildCard("ODPCLS", "Product Class", $_POST['frProdClass'], $_POST['toProdClass'], "U", $_POST['operProdClass'], "A");
	$returnValue=Range_WildCard("ODPGRP", "Product Group", $_POST['frProdGroup'], $_POST['toProdGroup'], "U", $_POST['operProdGroup'], "A");
	$returnValue=Range_WildCard("ODRQDT", "Required Date", $_POST['frReqDate'], $_POST['toReqDate'], "", $_POST['operReqDate'], "D");

	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);

	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HOEPAS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
