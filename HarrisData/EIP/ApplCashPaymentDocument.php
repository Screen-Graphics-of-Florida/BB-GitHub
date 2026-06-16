<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$fromID             = $_GET['fromID'];
$fromType           = $_GET['fromType'];
$fromDocument       = $_GET['fromDocument'];
$paymentType        = $_GET['paymentType'];
$WFReview           = $_GET['WFReview'];
$wfInstance         = $_GET['wfInstance'];
$wfInstanceDate     = $_GET['wfInstanceDate'];
$wfWorkItem         = $_GET['wfWorkItem'];
$wfWorkItemSequence = $_GET['wfWorkItemSequence'];
$wfParticipantId    = $_GET['wfParticipantId'];

$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'ARPmtTypeInclude.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$CRPERN=RetValue("RRN(ARCTRL)=1", "ARCTRL", "CRDPER");
$ARPdBegDate=RetValue("PDPER#=$CRPERN", "HDPBED", "PDBDAT");

$page_title    = "Application of Cash: Document";
$scriptName    = "ApplCashPaymentDocument.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;paymentType=" . urlencode(trim($paymentType)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$maintVarBase  = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;paymentType=" . urlencode(trim($paymentType)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;

if ($tag == "REPORT") {
	$BMBCHT=RetValue("(BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank)", "ARPBCH", "BMBCHT");
	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n \n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	require_once 'NumEdit.php';
	require_once 'ShowHideSelCriteria.php';
	require_once 'UpperCase.php';

	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.documentNumber.value ==\"\" )";
	print "\n {alert(\"$reqFieldError\"); return false;} ";

	print "\n if ( ";
	if ($CRBBAL!="Y" || $BMBCHT=="D") {print "\n     editNum(document.Chg.PaymentAmount, 13, 2) && ";}
	print "\n     editNum(document.Chg.OtherAmount, 13, 2) ";
	print "\n ) ";
	print "\n return true;";
	print "\n }";

	// Open More Information
	print "\n function OpenMoreInfo() { ";
	print "\n   showSel('ShowMoreInfo'); ";
	print "\n } ";

	// Close More Information
	print "\n function CloseMoreInfo() { ";
	print "\n   hideSel('ShowMoreInfo'); ";
	print "\n } ";

	// Close and Clear More Information
	print "\n function CloseClearMoreInfo() { ";
	print "\n   document.getElementById('creditCardType').value = \"\"; ";
	print "\n   document.getElementById('creditCardTypeDesc').value = \"\"; ";
	print "\n   hideSel('ShowMoreInfo'); ";
	print "\n } ";

	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require ($searchBanner);
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	print "\n <td class=\"content\">";

	print "\n <table $contentTable> ";
	print "\n     <colgroup> <col width=\"80%\"><col width=\"15%\"> ";
	if ($fromDocument) {print "\n <tr><td><h1>Change Document</h1></td> ";}
	else               {print "\n <tr><td><h1>Enter Document</h1></td> ";}
	print "\n         <td class=\"toolbar\"> ";
	print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
	$medIcon = "Y";
	require_once 'HelpPage.php';
	if ($CRBBAL!="Y" || $BMBCHT=="D") {print "\n <a onClick=\"OpenMoreInfo();\">$orderCreditCardImage</a> ";}
	print "\n </td></tr></table> ";

	print $searchhrTagAttr;

	require 'ApplCashBatchRetInfoInclude.php';
	require 'ApplCashCustomerRetInfoInclude.php';

	print "\n <table $contentTable><tr> ";
	print "\n <td> ";
	print "\n <div>";
	print "\n     <table $contentTable> ";
	Format_Header_Hover("Batch", $fromBatchNumber, $F_fromBatchDate,"batchSelection");
	Format_Header("Bank", $bankName, $fromBatchBank);
	Format_Header_Hover($idText, $idName, $fromID,"payerSelection");
	if ($fromDocument) {Format_Header("Document", $fromDocument, "");}
	print "\n     </table> ";
	print "\n </div>";
	print "\n <div id=\"batchSelection\" class=\"moreInfo\">{$batchInfo}</div>";
	print "\n <div id=\"payerSelection\" class=\"moreInfo\">{$payerInfo}</div>";
	print "\n </td> ";
	print "\n </tr></table> ";

	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	if ($errFound != "") {
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$Err_CECHK   =DecatErr_Field("@@chk@", "documentNumber");
		$Err_CECAMT  =DecatErr_Field("@@camt", "PaymentAmount");
		$Err_CEJAMT  =DecatErr_Field("@@jamt", "OtherAmount");
		$Err_CECCTP  =DecatErr_Field("@@cctp", "creditCardType");

		$row['CECHK']  =Decat_Field("@@chk@", $edtVar);
		$row['CECAMT'] =Decat_Field("@@camt", $edtVar);
		$row['CEJAMT'] =Decat_Field("@@jamt", $edtVar);
		$row['CECCTP'] =Decat_Field("@@cctp", $edtVar);

		$errFound= "";
	} else {
		$focusField= "documentNumber";

		if (is_null($fromDocument)) {
			$row['CECHK']="";
		} else {
			require 'stmtSQLClear.php';
			$stmtSQL .= " Select CECHK,CECAMT,CEJAMT,CECCTP ";
			$fileSQL .= " ARDCEN ";
			$selectSQL .= "(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ";
			require 'stmtSQLSelect.php';
			require 'stmtSQLEnd.php';
			require 'stmtSQLTotalRows.php';
			$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
			$row = db2_fetch_assoc($sqlResult);
		}
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_CECHK);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Document ID</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"documentNumber\" value=\"" . trim($row['CECHK']) . "\" size=\"15\" maxlength=\"15\"> $reqFieldChar ";
	$searchImageAlt= (string) "<img border=\"{$imageBorder}\" src=\"{$homeURL}{$imagePath}smSearch.gif\" title=\"Search Document Entry\" alt=\"Search\">";
	print "\n                  <a href=\"{$homeURL}{$phpPath}ApplCashPaymentDocumentSearch.php{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;tag=REPORT&amp;docName=Chg&amp;fldDocNumber=documentNumber&amp;fldPaymentAmount=PaymentAmount&amp;fldOtherAmount=OtherAmount&amp;fldCCType=creditCardType&amp;fldCCTypeDesc=creditCardTypeDesc\" onclick=\"$searchWinVar\">$searchImageAlt</a> ";
	if ($BMBCHT=="D") {
		$searchImageAlt= (string) "<img border=\"{$imageBorder}\" src=\"{$homeURL}{$imagePath}smSearch.gif\" title=\"Search Deposit Entry\" alt=\"Search\">";
		print "\n                  <a href=\"{$homeURL}{$phpPath}ApplCashPaymentDepositEntrySearch.php{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;tag=REPORT&amp;docName=Chg&amp;fldDocNumber=documentNumber&amp;fldDocAmount=PaymentAmount\" onclick=\"$searchWinVar\">$searchImageAlt</a>";
	}
	print "\n  <input type=\"hidden\" name=\"mncd\" id=\"mncd\" value=\"\"> ";
	print "\n     </td> ";
	print "\n </tr> ";
	DspErrMsg($Err_CECHK);

	if ($CRBBAL!="Y" || $BMBCHT=="D") {
		$textOvr=SetTextOvr($Err_CECAMT);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Payment Amount</span></td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"PaymentAmount\" value=\"" . rtrim($row['CECAMT']) . "\" size=\"17\" maxlength=\"17\"></td> ";
		print "\n </tr> ";
		DspErrMsg($Err_CECAMT);
	}

	$textOvr=SetTextOvr($Err_CEJAMT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Other Amount</span></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"OtherAmount\" value=\"" . rtrim($row['CEJAMT']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_CEJAMT);

	print "\n </table>";

	print "\n <div id=\"ShowMoreInfo\" class=\"moreInfo\">";
	print "\n     <a onClick=\"CloseMoreInfo();\">$closeSelImage</a> ";
	print "\n     <a onClick=\"CloseClearMoreInfo();\">$closeClearImage</a> ";

	if ($CRBBAL!="Y" || $BMBCHT=="D") {
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n <legend class=\"legendTitle\">Credit Card ";
		print "\n </legend> ";
		print "\n <table $contentTable>";

		$fieldDesc=RetValue("OECCTP='$row[CECCTP]'", "OECCTM", "OECCDS");
		$textOvr=SetTextOvr($Err_CECCTP);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Credit Card Type</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"creditCardType\" id=\"creditCardType\" value=\"" . rtrim($row['CECCTP']) . "\" size=\"4\" maxlength=\"4\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CreditCardTypeSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=creditCardType&amp;fldDesc=creditCardTypeDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
		print "\n                             <span class=\"dspdesc\" id=\"creditCardTypeDesc\">$fieldDesc</span></td> ";
		print "\n </tr> ";
		DspErrMsg($Err_CECCTP);

		print "\n </table> ";
		print "\n </fieldset> ";
		print "\n </div>";
	}

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.$focusField.focus();";
	print "\n </script>";
	print "\n </form>";
	print "\n </table>";

	print $searchhrTagAttr;
	require ($searchTrailer);
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	if (is_null($paymentType)) {$paymentType="";}
	$_POST['documentNumber']=strtoupper($_POST['documentNumber']);
	$edtVar= "";
	Concat_Field("@@ptyp", $paymentType);
	Concat_Field("@@bchn", $fromBatchNumber);
	Concat_Field("@@bchd", $fromBatchDate);
	Concat_Field("@@bchb", $fromBatchBank);
	Concat_Field("@@type", $fromType);
	Concat_Field("@@id@@", $fromID);
	Concat_Field("@@frm1", $fromDocument);
	Concat_Field("@@mncd", $_POST['mncd']);
	Concat_Field("@@chk@", strtoupper($_POST['documentNumber']));
	Concat_Field("@@camt", $_POST['PaymentAmount']);
	Concat_Field("@@jamt", $_POST['OtherAmount']);
	Concat_Field("@@cctp", strtoupper($_POST['creditCardType']));
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARCED_D", $userProfile, $_POST['mncd'], $errFound, $edtVar, $errVar, $wrnVar);
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$paymentType=Decat_Field("@@ptyp", $edtVar);
		if     ($paymentType == "C") {$pymntPage=$C_CPPMTP;}
		elseif ($paymentType == "D") {$pymntPage=$D_CPPMTP;}
		elseif ($paymentType == "J") {$pymntPage=$J_CPPMTP;}
		elseif ($paymentType == "U") {$pymntPage=$U_CPPMTP;}
		elseif ($paymentType == "Y") {$pymntPage=$Y_CPPMTP;}
		elseif ($paymentType == "V") {$pymntPage=$V_CPPMTP;}
		else                         {$pymntPage=$R_CPPMTP;}
		$fromURL = "{$homeURL}{$phpPath}{$pymntPage}{$maintVarBase}&amp;tag=REPORT&amp;fromDocument=" . urlencode(trim($_POST['documentNumber']));
		$fromURL  = str_replace("amp;", "", $fromURL);
		print "\n <script TYPE=\"text/javascript\">";
		print "\n opener.location.href='$fromURL'";
		print "\n opener.focus();";
		print "\n window.close();";
		print "\n </script>";
		exit();

	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>
