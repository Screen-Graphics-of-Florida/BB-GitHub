<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromScript         = $_GET['fromScript'];
$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$WFReview           = $_GET['WFReview'];
$wfInstance         = $_GET['wfInstance'];
$wfInstanceDate     = $_GET['wfInstanceDate'];
$wfWorkItem         = $_GET['wfWorkItem'];
$wfWorkItemSequence = $_GET['wfWorkItemSequence'];
$wfParticipantId    = $_GET['wfParticipantId'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Application of Cash: Batch Maintenance";
$scriptName     = "ApplCashBatchMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$cancelWFURL    = "{$baseURL}&amp;tag=CancelWF";
$updateVarBase  = "{$genericVarBase}&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$programName    = "HARABH_E";
$vldPgmName     = "HARABH_E";
if ($WFReview=="Y") {
	$backURL="{$homeURL}{$cGIPath}WFWorkList.d2w/REPORT{$altVarBase}";
} elseif ($maintenanceCode == "D" || $fromScript != "") {
	$backURL="{$homeURL}{$phpPath}ApplCashBatch.php{$scriptVarBase}&amp;tag=REPORT";
} else {
	$backURL="{$homeURL}{$phpPath}ApplCashBatchSelect.php{$scriptVarBase}&amp;tag=REPORT&amp;batchNumber=" . urlencode(trim($_POST['batchNumber'])) . "&amp;batchDate=" . urlencode(trim($_POST['batchDate'])) . "&amp;batchBank=" . urlencode(trim($_POST['batchBank'])) . "\"";
}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "CANCELWF"){
	$RecCount=RetValue("(WIINST,WIINDT,WIWITM,WIWISQ)=($wfInstance,$wfInstanceDate,$wfWorkItem,$wfWorkItemSequence)", "WFWITM", "Count(*)");
	if ($RecCount) {
		$setFlag= "";
		$edtVar = "";
		Concat_Field("@@inst", $wfInstance);
		Concat_Field("@@indt", $wfInstanceDate);
		Concat_Field("@@witm", $wfWorkItem);
		Concat_Field("@@wisq", $wfWorkItemSequence);
		Concat_Field("@@ptid", $wfParticipantId);
		$edtVar .= "}{";
		SetApprove_Data($profileHandle, $dataBaseID, $setFlag, $edtVar);
	}
	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}WFWorkList.d2w/REPORT{$altVarBase}\"> ";
	exit;
}

if ($tag == "WFREVIEW"){
	$setFlag= "Y";
	$edtVar= "";
	Concat_Field("@@inst", $wfInstance);
	Concat_Field("@@indt", $wfInstanceDate);
	Concat_Field("@@witm", $wfWorkItem);
	Concat_Field("@@wisq", $wfWorkItemSequence);
	Concat_Field("@@ptid", $wfParticipantId);
	$edtVar .= "}{";
	SetApprove_Data($profileHandle, $dataBaseID, $setFlag, $edtVar);

	$edtVar=RetValue("(ADINST,ADINDT)=($wfInstance,$wfInstanceDate)", "WFDTAA", "ADVAR");
	$WFReview= "Y";
	$maintenanceCode=Decat_Field("@@mncd", $edtVar);
	$fromBatchNumber=Decat_Field("@@bchn", $edtVar);
	$fromBatchDate  =Decat_Field("@@bchd", $edtVar);
	$fromBatchBank  =Decat_Field("@@bchb", $edtVar);
}

if ($tag == "MAINTAIN" || $tag == "WFREVIEW") {
	$vldExcptExist=RtvWFExcptExist($profileHandle, $programName, $vldPgmName, "E");
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
	require_once 'UpperCase.php';

	print "\n function validate(chgForm) {";
	if ($vldExcptExist=="N") {
		print "\n if (document.Chg.batchDate.value ==\"\" || ";
		print "\n     document.Chg.batchBank.value ==\"\" ";
		if ($CRABCH == "N") {
			print "\n || document.Chg.batchNumber.value ==\"\" ";
		}
		print "\n )";
		print "\n {alert(\"$reqFieldError\"); return false;} ";
	}
	print "\n if (editNum(document.Chg.batchNumber, 4, 0) && ";
	print "\n     editdate(document.Chg.batchDate) && ";
	print "\n     editNum(document.Chg.batchBank, 2, 0) && ";
	print "\n     editNum(document.Chg.depositTotal, 13, 2) && ";
	print "\n     editNum(document.Chg.otherTotal, 13, 2)) ";
	print "\n return true;";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n function confirmCancelWF(text) {return confirm(\"Cancel Processing of Work Item\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARBATCHMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select  BMBCHN, BMBCHD, BMBCHB, BMBCHT, BMPMTE, ";
		$stmtSQL .= " BMDEPA, BMDEPE, BMDEPP, ";
		$stmtSQL .= " BMADJT, BMADJE, BMADJP, BMTSTP, ";
		$stmtSQL .= " Case When (Select Count(*) from ARYPTD Where (YPBCH,YPBDAT,YPBANK)=(z.BMBCHN,z.BMBCHD,z.BMBCHB))>0 Then 'Y' Else ' ' End as ARYPTDREC ";
		$stmtSQL .= " From ARPBCH z ";
		$stmtSQL .= " Where (BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$harabh_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harabh_OPT['sec_01'];
	$sec_02=$harabh_OPT['sec_02'];
	$sec_03=$harabh_OPT['sec_03'];
	$sec_04="N";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $WFReview=="Y" || $maintenanceCode=="A") {
		if ($errFound == "" && $WFReview == "" && $maintenanceCode=="A") {
			$edtVar= "";
			if ($CRABCH == "Y") {$focusField= "batchDate";}
			else                {$focusField= "batchNumber";}
		} elseif ($errFound == "" && $WFReview == "Y") {
			if ($CRABCH == "Y") {$focusField= "batchDate";}
			else                {$focusField= "batchNumber";}
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_BMBCHN=DecatErr_Field("@@bchn", "batchNumber");
			$Err_BMBCHD=DecatErr_Field("@@bchd", "batchDate");
			$Err_BMBCHB=DecatErr_Field("@@bchb", "batchBank");
			$Err_BMDEPA=DecatErr_Field("@@depa", "depositTotal");
			$Err_BMADJT=DecatErr_Field("@@adjt", "otherTotal");
		}

		$row['BMBCHN']=Decat_Field("@@bchn", $edtVar);
		$row['BMBCHD']=Decat_Field("@@bchd", $edtVar);
		$F_BMBCHD=Format_Date(DateToCYMD($row['BMBCHD']), "D");
		$row['BMBCHB']=Decat_Field("@@bchb", $edtVar);
		$row['BMDEPA']=Decat_Field("@@depa", $edtVar);
		$row['BMADJT']=Decat_Field("@@adjt", $edtVar);
		$row['BMTSTP']=Decat_Field("@@tstp", $edtVar);

		if ($errFound == "" && $WFReview == "Y") {
			$F_BMBCHD=Format_Date($row['BMBCHD'], "D");
			$row['BMBCHD']=DateInputFromCYMD($row['BMBCHD']);
		} elseif ($errFound == "" && $WFReview == "" && $maintenanceCode=="A") {
			if ($CRABCH == "Y") {$focusField= "batchDate";}
			else                {$focusField= "batchNumber";}
			$row['BMBCHB']= "0";
		}
		$errFound= "";
	} else {
		$F_BMBCHD=Format_Date($row['BMBCHD'], "D");
		$row['BMBCHD']=DateInputFromCYMD($row['BMBCHD']);
		$focusField= "depositTotal";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['BMTSTP']) . "\"></td></tr> ";

	// Batch
	$row['BMBCHN']=Default_Zero($row['BMBCHN']);
	$textOvr=SetTextOvr($Err_BMBCHN);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Batch</span></td>";
	if ($maintenanceCode == "A" && $CRABCH == "Y" && $WFReview == "") {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"batchNumber\" value=\"" . rtrim($row['BMBCHN']) . "\"></td>";
	} elseif ($CRABCH == "Y" || $row['BMBCHT']=="D" || $row['BMPMTE']=="C" || $maintenanceCode=="D" || $row['ARYPTDREC']=="Y") {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"batchNumber\" value=\"" . rtrim($row['BMBCHN']) . "\">$row[BMBCHN]</td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"batchNumber\" value=\"" . rtrim($row['BMBCHN']) . "\" size=\"6\" maxlength=\"4\">";
		print "\n                         <a href=\"{$homeURL}{$phpPath}ARBatchSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldBatch=batchNumber&amp;fldDate=batchDate&amp;fldBank=batchBank\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a></td> ";
	}
	print "\n </tr> ";
	DspErrMsg($Err_BMBCHN);

	// Batch Date
	$row['BMBCHD']=Default_Zero($row['BMBCHD']);
	$textOvr=SetTextOvr($Err_BMBCHD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Batch Date</span></td>";
	if ($row['BMBCHT']=="D" || $row['BMPMTE']=="C" || $maintenanceCode=="D" || $row['ARYPTDREC']=="Y") {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"batchDate\" value=\"" . rtrim($row['BMBCHD']) . "\">$F_BMBCHD</td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"batchDate\" value=\"" . rtrim($row['BMBCHD']) . "\" size=\"6\" maxlength=\"6\"> ";
		print "\n                         <a href=\"javascript:calWindow('batchDate');\"> $reqFieldChar $calendarImage</a></td> ";
	}
	print "\n </tr> ";
	DspErrMsg($Err_BMBCHD);

	// Bank
	$row['BMBCHB']=Default_Zero($row['BMBCHB']);
	$fieldDesc=RetValue("BKBANK=$row[BMBCHB]", "HDBANK", "BKBKNM");
	$textOvr=SetTextOvr($Err_BMBCHB);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Bank</span></td> ";
	if ($row['BMBCHT']=="D" || $row['BMPMTE']=="C" || $maintenanceCode=="D" || $row['ARYPTDREC']=="Y") {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"batchBank\" value=\"" . rtrim($row['BMBCHB']) . "\">$row[BMBCHB]</td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"batchBank\" value=\"" . rtrim($row['BMBCHB']) . "\" size=\"6\" maxlength=\"2\"> ";
		print "\n                         <a href=\"{$homeURL}{$phpPath}BankSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=batchBank&amp;fldDesc=batchBankDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
		print "\n                         <span class=\"dspdesc\" id=\"batchBankDesc\">$fieldDesc</span></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_BMBCHB);

	print "\n </table> ";

	print "\n <table $contentTable>";
	print "\n <tr><td class=\"dsphdr\">&nbsp;</td> ";
	print "\n     <td class=\"colhdr\">Total</td> ";
	print "\n     <td class=\"colhdr\">Posted</td> ";
	print "\n     <td class=\"colhdr\">Pending</td> ";
	print "\n     <td class=\"colhdr\">Variance</td> ";
	print "\n </tr> ";

	if ($CRBBAL=="Y" && $row['BMBCHT']!="D" && $row['BMDEPA']!=$row['BMDEPP'] + $row['BMDEPE']) {
		$row['BMDEPA']=$row['BMDEPP'] + $row['BMDEPE'];
		$Err_BMDEPA=Rtv_Error_Desc ('HAR0071');
	}
	$row['BMDEPA']=Default_Zero($row['BMDEPA']);
	$result=$row['BMDEPA'] - ($row['BMDEPP'] + $row['BMDEPE']);
	$textOvr=SetTextOvr($Err_BMDEPA);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Deposit</span></td> ";
	if ($CRBBAL=="Y") {
		print "\n     <td class=\"inputnmbr\"><input type=\"hidden\" name=\"depositTotal\" value=\"" . rtrim($row['BMDEPA']) . "\">" . number_format($row['BMDEPA'],2) . "</td>";
	} else {
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"depositTotal\" value=\"" . rtrim($row['BMDEPA']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	}
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMDEPP'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMDEPE'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($result,2) . "</td> ";
	print "\n </tr> ";
	DspErrMsg($Err_BMDEPA);

	$row['BMADJT']=Default_Zero($row['BMADJT']);
	$result=$row['BMADJT'] - ($row['BMADJP'] + $row['BMADJE']);
	$textOvr=SetTextOvr($Err_BMADJT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Other</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"otherTotal\" value=\"" . rtrim($row['BMADJT']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMADJP'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMADJE'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($result,2) . "</td> ";
	print "\n </tr> ";
	DspErrMsg($Err_BMADJT);

	print "\n </table> ";

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
	if ($maintenanceCode=="D" && is_null($_POST['batchNumber'])) {
		$_POST['batchNumber']    =$fromBatchNumber;
		$_POST['batchDate']      =DateInputFromCYMD($fromBatchDate);
		$_POST['batchBank']      =$fromBatchBank;
	}

	$edtVar= "";
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	Concat_Field("@@wfid", $wfInstance);
	Concat_Field("@@wfdt", $wfInstanceDate);
	Concat_Field("@@wfcn", $wfWorkItem);
	Concat_Field("@@wfsq", $wfWorkItemSequence);
	Concat_Field("@@wfpt", $wfParticipantId);

	Concat_Field("@@frm1", $fromBatchNumber);
	Concat_Field("@@frm2", $fromBatchDate);
	Concat_Field("@@frm3", $fromBatchBank);
	Concat_Field("@@bchn", $_POST['batchNumber']);
	Concat_Field("@@bchd", $_POST['batchDate']);
	Concat_Field("@@bchb", $_POST['batchBank']);
	Concat_Field("@@depa", $_POST['depositTotal']);
	Concat_Field("@@adjt", $_POST['otherTotal']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARABH_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	$F_BMBCHD=Format_Date(DateToCYMD($_POST[batchDate]), "D");
	if ($errFound == "") {
		if ($maintenanceCode == "A" && ($_POST['batchNumber']=="0" || $_POST['batchNumber']=="")) {
			$_POST['batchNumber']=Decat_Field("@@bchn", $edtVar);
		}
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST[batchNumber], $F_BMBCHD, $_POST[batchBank], "", "", "");
		if ($WFReview=="Y") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}WFWorkList.d2w/REPORT{$altVarBase}\"> ";
		} elseif ($maintenanceCode == "D" || $fromScript != "") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}ApplCashBatch.php{$scriptVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		} else {
			$F_BMBCHD=DateToCYMD($_POST['batchDate']);
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}ApplCashBatchSelect.php{$updateVarBase}&amp;tag=REPORT&amp;batchNumber=" . urlencode(trim($_POST['batchNumber'])) . "&amp;batchDate=" . urlencode(trim($_POST['batchDate'])) . "&amp;batchBank=" . urlencode(trim($_POST['batchBank'])) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "&amp;fromBatchNumber=" . urlencode(trim($_POST['batchNumber'])) . "&amp;fromBatchDate=" . urlencode(trim($F_BMBCHD)) . "&amp;fromBatchBank=" . urlencode(trim($_POST['batchBank'])) . " \"> ";
		}
	} elseif ($maintenanceCode == "D") {
		$Err_BMBCHN=DecatErr_Field("@@bchn", "batchNumber");
		$confMessage=Format_ConfMsg_Desc("", $_POST[batchNumber], $F_BMBCHD, $_POST[batchBank], "", "<br>$Err_BMBCHN", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}ApplCashBatch.php{$scriptVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;batchNumber=" . urlencode(trim($_POST['batchNumber'])) . "&amp;batchDate=" . urlencode(trim($_POST['batchDate'])) . "&amp;batchBank=" . urlencode(trim($_POST['batchBank'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

function SetApprove_Data($profileHandle,$dataBaseID,$approveCode,$edtVar) {
	global $pgmLibrary, $i5Connect;

	$pgmCall = array(
	array("Name"=>"profileHandle",   "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID",      "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"approveCode",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HWFWIA_S", $pgmCall);
	if (!$pgm) {die("<br>SetApprove_Data (HWFWIA_S) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"  =>$profileHandle,
	"dataBaseID"     =>$dataBaseID,
	"approveCode"    =>$approveCode,
	"edtVar"         =>$edtVar);

	$parmOut = array(
	"profileHandle"  =>"profileHandle",
	"dataBaseID"     =>"dataBaseID",
	"approveCode"    =>"approveCode",
	"edtVar"         =>"edtVar");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>SetApprove_Data (HWFWIA_S) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['profileHandle']  =$profileHandle;
	$returnValue['dataBaseID']     =$dataBaseID;
	$returnValue['approveCode']    =$approveCode;
	$returnValue['edtVar']         =$edtVar;
	return $returnValue;
}

?>