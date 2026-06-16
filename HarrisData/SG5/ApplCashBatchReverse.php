<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$jobSbmSched        = $_GET['jobSbmSched'];
$resetSelectionFlag = $_GET['resetSelectionFlag'];
$rtvSelection       = $_GET['rtvSelection'];
$saveSelection      = $_GET['saveSelection'];
$scheduleJobSwitch  = $_GET['scheduleJobSwitch'];
$selScheduleJob     = $_GET['selScheduleJob'];
$submitSchedule     = $_GET['submitSchedule'];

$fromScript         = $_GET['fromScript'];
$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$CRPERN=RetValue("RRN(ARCTRL)=1", "ARCTRL", "CRDPER");
$ARPdBegDate=RetValue("PDPER#=$CRPERN", "HDPBED", "PDBDAT");

$page_title            = "Reverse Batch Payments";
$scriptName            = "ApplCashBatchReverse.php";
$scriptVarBase         = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$programName           = "HARABH_E";
$submitNoSelection     = "N";
$submitCallProgram     = "CARRBH_U";
$submitEnvProgram      = "CARRBH_U";
$submitEnvPrinter      = "";
$submitScheduleScript  = "ApplCashBatch.php";
$applicationID         = "AR";

$harabh_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
if ($harabh_OPT['sec_06']=="N") {
	require_once 'ProgSecurityError.php';
	exit;
}
require 'ApplCashBatchRetInfoInclude.php';
$BMBCHT=$row['BMBCHT'];

if ($tag == "REPORT") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'ShowHideSelCriteria.php';
	require_once 'UpperCase.php';

	print "\n function validate(chgForm) {";
	print "\n if   (document.Chg.reasonCode.value == \"\" ";
	if ($fromBatchDate<$ARPdBegDate) {
		print "\n     || document.Chg.reversalBatchDate.value ==\"\" ";
	} else {
		print "\n     || document.Chg.reuseOriginal[0].checked==false && document.Chg.reuseOriginal[1].checked==false ";
		print "\n     || document.Chg.reuseOriginal[0].checked==true  && document.Chg.reversalBatchDate.value ==\"\" ";
	}
	if ($CRABCH == "N") {
		if ($fromBatchDate<$ARPdBegDate) {print "\n || document.Chg.reversalBatchNumber.value ==\"\" ";}
		else                             {print "\n || document.Chg.reuseOriginal[0].checked==true && document.Chg.reversalBatchNumber.value ==\"\" ";}
	}
	if ($BMBCHT!="D" || $CRBBAL!="Y") {
		print "\n     || document.Chg.regenerate[0].checked==false && document.Chg.regenerate[1].checked==false ";
		print "\n     || document.Chg.regenerate[1].checked==true && document.Chg.regenBatchBank.value ==\"\" ";
		print "\n     || document.Chg.regenerate[1].checked==true && document.Chg.regenBatchDate.value ==\"\" ";
		if ($CRABCH == "N") {print "\n     || document.Chg.regenerate[1].checked==true && document.Chg.regenBatchNumber.value ==\"\" ";}
	}
	print "\n   )";
	print "\n   {alert(\"$reqFieldError\"); return false;} ";
	print "\n   if (editNum(document.Chg.reversalBatchNumber, 4, 0) ";
	print "\n    && editdate(document.Chg.reversalBatchDate) ";
	if ($BMBCHT!="D" || $CRBBAL!="Y") {
		print "\n    && editNum(document.Chg.regenBatchNumber, 4, 0) ";
		print "\n    && editdate(document.Chg.regenBatchDate) ";
		print "\n    && editNum(document.Chg.regenBatchBank, 2, 0) ";
	}
	print "\n   ) ";
	print "\n   return true;";
	print "\n }";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARBATCHREVERSE";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$backHome="ApplCashBatch.php";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';

	print "\n <table $contentTable><tr> ";
	print "\n <td> ";
	print "\n <div>";
	print "\n     <table $contentTable> ";
	Format_Header_Hover("Batch", $fromBatchNumber, $F_fromBatchDate,"batchSelection");
	Format_Header("Bank", $bankName, $fromBatchBank);
	print "\n     </table> ";
	print "\n </div>";
	print "\n <div id=\"batchSelection\" class=\"moreInfo\">{$batchInfo}</div>";
	print "\n </td> ";
	print "\n </tr></table> ";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
 	 	    $errVar=ErrVarErr($profileHandle, $errVar);
		    $Err_RGRSCD=DecatErr_Field("@@rscd", "reasonCode");
		    $Err_RGRVOR=DecatErr_Field("@@rvor", "reuseOriginal");
		    $Err_RGRVNR=DecatErr_Field("@@rvnr", "reversalBatchNumber");
		    $Err_RGRVDT=DecatErr_Field("@@rvdt", "reversalBatchDate");
		    $Err_RGRGRG=DecatErr_Field("@@rgrg", "regenerate");
		    $Err_RGRGNR=DecatErr_Field("@@rgnr", "regenBatchNumber");
		    $Err_RGRGDT=DecatErr_Field("@@rgdt", "regenBatchDate");
		    $Err_RGRGBK=DecatErr_Field("@@rgbk", "regenBatchBank");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$RGRSCD=Decat_Field("@@rscd", $edtVar);
		$RGRVOR=Decat_Field("@@rvor", $edtVar);
		$RGRVNR=Decat_Field("@@rvnr", $edtVar);
		$RGRVDT=Decat_Field("@@rvdt", $edtVar);
		$RGRGRG=Decat_Field("@@rgrg", $edtVar);
		$RGRGNR=Decat_Field("@@rgnr", $edtVar);
		$RGRGDT=Decat_Field("@@rgdt", $edtVar);
		$RGRGBK=Decat_Field("@@rgbk", $edtVar);

		if ($RGRGRG == "N") {
			$noRGRGRG="CHECKED";
			$yesRGRGRG="";
		} elseif ($RGRGRG == "Y") {
			$noRGRGRG="";
			$yesRGRGRG="CHECKED";
		} else {
			$noRGRGRG="";
			$yesRGRGRG="";
		}
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$focusField= "reasonCode";
		$RGRSCD="";
		$RGRVOR="";
		$RGRVNR="";
		$RGRVDT="";
		$RGRGRG="";
		$RGRGNR="";
		$RGRGDT="";
		$RGRGBK="";
		$noRGRGRG="";
		$yesRGRGRG="";
	}
	$disabledRGRVOW="";
	if ($fromBatchDate<$ARPdBegDate) {
		$noRGRVOR="CHECKED";
		$yesRGRVOR="";
		$disabledRGRVOW="DISABLED";
	} elseif ($RGRVOR == "N") {
		$noRGRVOR="CHECKED";
		$yesRGRVOR="";
	} elseif ($RGRVOR == "Y")  {
		$noRGRVOR="";
		$yesRGRVOR="CHECKED";
	} else {
		$noRGRVOR="";
		$yesRGRVOR="";
	}

	print "\n <div> ";

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Reversal Payment</legend> ";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_RGRSCD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Reason Code</span></td>";
	print "\n <td class=\"inputalph\"><input type=\"text\" name=\"reasonCode\" value=\"" . rtrim($RGRSCD) . "\" size=\"6\" maxlength=\"4\">";
	print "\n                         <a href=\"{$homeURL}{$phpPath}ARReverseReasonSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=reasonCode&amp;fldDesc=reasonCodeDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n                         <span class=\"dspdesc\" id=\"reasonCodeDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_RGRSCD);

	$textOvr=SetTextOvr($Err_RGRVOR);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Use Original Batch</span></td>";
	if ($fromBatchDate<$ARPdBegDate) {
		print "\n     <td class=\"inputalph\"><input type=\"radio\" value=\"N\" $noRGRVOR $disabledRGRVOW>No";
		print "\n                             <input type=\"radio\" value=\"Y\" $yesRGRVOR $disabledRGRVOW>Yes";
		print "\n                             <input type=\"hidden\" name=\"reuseOriginal\" value=\"N\"> </td>";
	} else {
		print "\n     <td class=\"inputalph\"><input type=\"radio\" name=\"reuseOriginal\" value=\"N\" $noRGRVOR $disabledRGRVOW>No";
		print "\n                             <input type=\"radio\" name=\"reuseOriginal\" value=\"Y\" $yesRGRVOR $disabledRGRVOW>Yes $reqFieldChar </td>";
	}
	print "\n </tr>";
	DspErrMsg($Err_RGRVOR);

	$textOvr=SetTextOvr($Err_RGRVNR);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Batch</span></td>";
	if ($CRABCH == "Y") {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"reversalBatchNumber\" value=\"" . rtrim($RGRVNR) . "\">Auto Assigned</td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"reversalBatchNumber\" value=\"" . rtrim($RGRVNR) . "\" size=\"6\" maxlength=\"4\">";
		print "\n                         <a href=\"{$homeURL}{$phpPath}ARBatchSearch.php{$genericVarBase}&amp;tag=ENTRY&amp;docName=Chg&amp;specificBank=" . urlencode($fromBatchBank) . "&amp;fldBatch=reversalBatchNumber&amp;fldDate=reversalBatchDate\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	}
	print "\n </tr> ";
	DspErrMsg($Err_RGRVNR);

	$textOvr=SetTextOvr($Err_RGRVDT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Batch Date</span></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"reversalBatchDate\" value=\"" . rtrim($RGRVDT) . "\" size=\"6\" maxlength=\"6\"> ";
	print "\n                             <a href=\"javascript:calWindow('reversalBatchDate');\">$calendarImage</a></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_RGRVDT);

	print "\n </table>";
	print "\n </fieldset> ";

	if ($BMBCHT!="D" || $CRBBAL!="Y") {
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n <legend class=\"legendTitle\">Correcting Payment</legend> ";
		print "\n <table $contentTable>";

		$textOvr=SetTextOvr($Err_RGRGRG);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Regenerate</span></td>";
		print "\n     <td class=\"inputalph\"><input type=\"radio\" name=\"regenerate\" value=\"N\" $noRGRGRG>No";
		print "\n                             <input type=\"radio\" name=\"regenerate\" value=\"Y\" $yesRGRGRG>Yes $reqFieldChar </td>";
		print "\n </tr>";
		DspErrMsg($Err_RGRGRG);

		if ($RGRGBK>0) {$fieldDesc=RetValue("BKBANK=$RGRGBK", "HDBANK", "BKBKNM");}
		else           {$fieldDesc="";}
		$textOvr=SetTextOvr($Err_RGRGBK);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Bank</span></td> ";
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"regenBatchBank\" value=\"" . rtrim($RGRGBK) . "\" size=\"6\" maxlength=\"2\"> ";
		print "\n                         <a href=\"{$homeURL}{$phpPath}BankSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=regenBatchBank&amp;fldDesc=regenBatchBankDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
		print "\n                         <span class=\"dspdesc\" id=\"regenBatchBankDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_RGRGBK);

		$textOvr=SetTextOvr($Err_RGRGNR);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Batch</span></td>";
		if ($CRABCH == "Y") {
			print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"regenBatchNumber\" value=\"" . rtrim($RGRGNR) . "\">Auto Assigned</td>";
		} else {
			print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"regenBatchNumber\" value=\"" . rtrim($RGRGNR) . "\" size=\"6\" maxlength=\"4\">";
			print "\n                         <a href=\"{$homeURL}{$phpPath}ARBatchSearch.php{$genericVarBase}&amp;tag=ENTRY&amp;docName=Chg&amp;fldBatch=regenBatchNumber&amp;fldDate=regenBatchDate&amp;fldBank=regenBatchBank\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		}
		print "\n </tr> ";
		DspErrMsg($Err_RGRGNR);

		$textOvr=SetTextOvr($Err_RGRGDT);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Batch Date</span></td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"regenBatchDate\" value=\"" . rtrim($RGRGDT) . "\" size=\"6\" maxlength=\"6\"> ";
		print "\n                             <a href=\"javascript:calWindow('regenBatchDate');\">$calendarImage</a></td> ";
		print "\n </tr> ";
		DspErrMsg($Err_RGRGDT);

		print "\n </table> ";
		print "\n </fieldset> ";
	}
	
	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	require 'SubmitScheduleBottom.php';
	print "\n $hrTagAttr ";
	
	if ($focusField !="") {
		print "\n <script TYPE=\"text/javascript\">";
		print "\n document.Chg.$focusField.focus();";
		print "\n </script>";
	}
	print "\n </form>";
	print "\n </div>";

	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	$edtVar= "";
	Concat_Field("@@frm1", $fromBatchNumber);
	Concat_Field("@@frm2", $fromBatchDate);
	Concat_Field("@@frm3", $fromBatchBank);
	Concat_Field("@@rscd", strtoupper($_POST['reasonCode']));
	Concat_Field("@@rvor", strtoupper($_POST['reuseOriginal']));
	Concat_Field("@@rvnr", $_POST['reversalBatchNumber']);
	Concat_Field("@@rvdt", $_POST['reversalBatchDate']);
	Concat_Field("@@rgrg", strtoupper($_POST['regenerate']));
	if ($BMBCHT!="D" || $CRBBAL!="Y") {
		Concat_Field("@@rgbk", $_POST['regenBatchBank']);
		Concat_Field("@@rgnr", $_POST['regenBatchNumber']);
		Concat_Field("@@rgdt", $_POST['regenBatchDate']);
	}
	Concat_Field("@@pgid", $submitEnvProgram);
	Concat_Field("@@prtf", $submitEnvPrinter);
	Concat_Field("@@pref", $submitApplPrefix);
	Concat_Field("@@apid", $applicationID);
	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Validate_Data($userProfile,$submitSchedule,$errFound,$edtVar,$errVar,$wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
}

function Validate_Data($userProfile,$submitSchedule,$errFound,$edtVar,$errVar,$wrnVar) {
	global $pgmLibrary, $i5Connect;
	if (is_null($submitSchedule )) $submitSchedule="";
	if (is_null($errFound ))       $errFound="";
	if (is_null($edtVar ))         $edtVar="";
	if (is_null($errVar ))         $errVar="";
	if (is_null($wrnVar ))         $wrnVar="";

	$pgmCall = array(
	array("Name"=>"userProfile",    "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"submitSchedule", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"errFound",       "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",         "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar",         "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"wrnVar",         "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HARRBH_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HARRBH_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"userProfile"    =>$userProfile,
	"submitSchedule" =>$submitSchedule,
	"errFound"       =>$errFound,
	"edtVar"         =>$edtVar,
	"errVar"         =>$errVar,
	"wrnVar"         =>$wrnVar);

	$parmOut = array(
	"userProfile"    =>"userProfile",
	"submitSchedule" =>"submitSchedule",
	"errFound"       =>"errFound",
	"edtVar"         =>"edtVar",
	"errVar"         =>"errVar",
	"wrnVar"         =>"wrnVar");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data (HARRBH_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['userProfile']    =$userProfile;
	$returnValue['submitSchedule'] =$submitSchedule;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	$returnValue['wrnVar']         =$wrnVar;
	return $returnValue;
}

?>