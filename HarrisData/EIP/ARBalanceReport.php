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

require_once "ARControl$dataBaseID.php";
require_once "MCControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title            = "A/R Balance Report";
$scriptName            = "ARBalanceReport.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CARBLP";
if ($HDMCRL>0 && $CRPRMC=="Y") {
	$submitEnvProgram="HHDBLM";
	$submitEnvPrinter="HHDBLMPF";
} else {
	$submitEnvProgram="HHDBAL";
	$submitEnvPrinter="HHDBALPF";
}
$submitScheduleScript  = "";
$applicationID         = "AR";

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
	if ($HDMCRL>0 && $CRPRMC=="Y") {
		print "\n if (document.Chg.BaseCurrency.value ==\"\" ";
		print "\n )";
		print "\n {alert(\"$reqFieldError\"); return false;} ";
	}
	print "\n   if (editNum(document.Chg.DistPeriod, 4, 0) ";
	print "\n    && editNum(document.Chg.frLocation, 3, 0) ";
	print "\n    && editNum(document.Chg.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frLocation, document.Chg.toLocation, document.Chg.operLocation, 3) ";
	if ($HDMCRL>0 && $CRPRMC=="Y") {print "\n    && editFromToOper(document.Chg.frInvCurType, document.Chg.toInvCurType, document.Chg.operInvCurType, 'A') ";}
	if ($HDMCRL>0 && $CRPRMC=="Y") {print "\n    && editFromToOper(document.Chg.frDomCurType, document.Chg.toDomCurType, document.Chg.operDomCurType, 'A') ";}
	print "\n       ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARBALANCEREPORT";
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
			$Err_DPER=DecatErr_Field("@@dper", "DistPeriod");
			$Err_TOTL=DecatErr_Field("@@totl", "TotalOnly");
			if ($HDMCRL>0 && $CRPRMC=="Y") {$Err_BASE=DecatErr_Field("@@base", "BaseCurrency");}

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$DPER=Decat_Field("@@dper", $edtVar);
		$TOTL=Decat_Field("@@totl", $edtVar);
		if ($HDMCRL>0 && $CRPRMC=="Y") {$BASE=Decat_Field("@@base", $edtVar);}
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$DPER="";
		$TOTL="N";
		if ($HDMCRL>0 && $CRPRMC=="Y") {$BASE=$MUCURT;}
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#PrintOption\">Print Option</a></td> ";
	if ($wildCardDisplay != "") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#CurrentFilterCriteria\">Current Filter Criteria</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#RefineFilterCriteria\">Refine Filter Criteria</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	print $hrTagAttr;

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

	print "\n <a name=\"PrintOption\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Print Option</legend> ";
	require 'TopOfForm.php'; 
	print "\n     <table $contentTable> ";

	// Distribution Period
	$textOvr=SetTextOvr($Err_DPER);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Distribution Period</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"DistPeriod\" value=\"" . rtrim($DPER) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;docName=Chg&amp;periodFld=DistPeriod\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_DPER);

	// Base Currency Type
	if ($HDMCRL>0 && $CRPRMC=="Y") {
		$fieldDesc=RetValue("CYTYPE='$BASE'", "HDCTYP", "CYDESC");
		$textOvr=SetTextOvr($Err_BASE);
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Base Currency Type</span></td> ";
		print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"BaseCurrency\" value=\"" . rtrim($BASE) . "\" size=\"3\" maxlength=\"3\"> ";
		print "\n                                     <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=BaseCurrency&amp;fldDesc=BaseCurrencyDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
		print "\n                                     <span class=\"dspdesc\" id=\"BaseCurrencyDesc\">$fieldDesc</span></td>";
		print "\n         </tr> ";
		DspErrMsg($Err_BASE);
	}
	
	Build_Fld_Entry("Print Total Page Only","TotalOnly","inputalph","YORN","TOTL",$TOTL,$Err_TOTL,"1","1","","","");

	print "\n     </table> ";
	print "\n </fieldset> ";

	require_once 'AdvSearchTopReport.php';

	$operNbr = "operLocation";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	if ($HDMCRL>0 && $CRPRMC=="Y") {
		$operNbr = "operInvCurType";
		print "\n <tr><td class=\"dsphdr\">Invoice Currency Type</td>";
		print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frInvCurType\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frInvCurType&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toInvCurType\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toInvCurType&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		print "\n </tr>";

		$operNbr = "operDomCurType";
		print "\n <tr><td class=\"dsphdr\">Domestic Currency Type</td>";
		print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frDomCurType\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frDomCurType&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toDomCurType\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toDomCurType&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		print "\n </tr>";
	}
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

	$returnValue=Range_WildCard("IVLOC", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	if ($HDMCRL>0 && $CRPRMC=="Y") {
		$returnValue=Range_WildCard("IVCURT", "Invoice Currency Type", $_POST['frInvCurType'], $_POST['toInvCurType'], "U", $_POST['operInvCurType'], "A");
		$returnValue=Range_WildCard("IVCURD", "Domestic Currency Type", $_POST['frDomCurType'], $_POST['toDomCurType'], "U", $_POST['operDomCurType'], "A");
	}
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);
	Concat_Field("@@dper", $_POST['DistPeriod']);
	if (!isset($_POST['TotalOnly'])) {$_POST['TotalOnly']="N";}  Concat_Field("@@totl", $_POST['TotalOnly']);
	if ($HDMCRL>0 && $CRPRMC=="Y") {Concat_Field("@@base", strtoupper($_POST['BaseCurrency']));}

	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HARBLS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
