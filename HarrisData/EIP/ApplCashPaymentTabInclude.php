<?php
require 'ApplCashBatchRetInfoInclude.php';
require 'ApplCashCustomerRetInfoInclude.php';
require_once 'ARPmtTypeInclude.php';

// Batch Information Section
print "\n <table $contentTable style=\"float:left;\"> ";
Format_Header_Hover ( "Batch", $fromBatchNumber, $F_fromBatchDate, "batchSelection" );
Format_Header ( "Bank", $bankName, $fromBatchBank );
Format_Header_Hover ( $idText, $idName, $fromID, "payerSelection" );
Format_Header_URL ( "Document", $fromDocument, "", "{$homeURL}{$phpPath}ApplCashPaymentDocument.php{$scriptVarBase}&amp;tag=REPORT&amp;paymentType=" . urlencode ( $paymentType ) . "\" onclick=\"$arDocumentWinVar" );
print "\n </table> ";

// Page Title
print "\n <h1 style=\"float:left; margin-left:5ex; margin-right:5ex;\">$page_title</h1>";

// Page Icon Section
if ($paymentType == "C") {
	$pymntTitle = $C_CPDESC;
	$pymntPage = $C_CPPMTP;
	$errorPage = $C_CPQUKP;
} elseif ($paymentType == "D") {
	$pymntTitle = $D_CPDESC;
	$pymntPage = $D_CPPMTP;
	$errorPage = $D_CPQUKP;
} elseif ($paymentType == "J") {
	$pymntTitle = $J_CPDESC;
	$pymntPage = $J_CPPMTP;
	$errorPage = $J_CPQUKP;
} elseif ($paymentType == "U") {
	$pymntTitle = $U_CPDESC;
	$pymntPage = $U_CPPMTP;
	$errorPage = "";
} elseif ($paymentType == "Y") {
	$pymntTitle = $Y_CPDESC;
	$pymntPage = $Y_CPPMTP;
	$errorPage = $Y_CPQUKP;
}

print "\n <div class=\"toolbar\"> ";
if ($fromType == "C") {
	print "\n <a href=\"{$homeURL}{$phpPath}ApplCashCustomer.php{$scriptVarBase}\" title=\"Back Home\">$portalHomeMed</a> ";
} else {
	print "\n <a href=\"{$homeURL}{$phpPath}ApplCashPayer.php{$scriptVarBase}\" title=\"Back Home\">$portalHomeMed</a> ";
}
print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT\">$reloadImage</a> ";

$medIcon = "Y";
require_once 'HelpPage.php';

print "<br>";
$pmtError = RetValue ( "(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "','$paymentType','$paymentID') ", "ARPYER", "Count(*)" );
if ($tabID == "COLUMN") {
	print "\n <a onClick=\"dfltColumn();\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=DEFAULT\">$wildDftImage</a>";
	print "\n <a onClick=\"saveColumn();\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT\">$wildSetImage</a>";
} else {
	// Remove until add code to edit old document print "\n <a href=\"{$homeURL}{$phpPath}ApplCashPaymentDocument.php{$genericVarBase}&amp;tag=REPORT&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId)) . "&amp;columnDisplay" . $columnDisplay . "&amp;paymentType=" . urlencode(trim($paymentType)) . "&amp;harcedProgram=HARCED_P{$paymentType}\" onclick=\"$arDocumentWinVar\">$applCashDocAddImage</a>";
	print "\n <span class=\"dspalph\"><input type=\"checkbox\" name=\"SKIP\" id=\"SKIP\" value='Y'  title=\"Omit Validation\" onClick=\"SaveSkipValue()\">Omit Validation</span>";
	print "\n <a href=\"#\" onClick=\"ARReleaseEntry();\">$applCashValidateImage</a>";
}
print "\n </div> ";

// Reset float
print "\n <br style=\"clear:both;\"> ";

// Hidden Divisions for Batch and Payer
print "\n <div id=\"batchSelection\" class=\"moreInfo\">{$batchInfo}</div>";
print "\n <div id=\"payerSelection\" class=\"moreInfo\">{$payerInfo}</div>";

// Document Status Section
if ($tabID != "COLUMN") {
	print "\n <table class=\"contenttable\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\">";
	print "\n <tr>";
	require 'ApplCashDocInclude.php';
	print "\n </tr></table>";
}

// //////////////////////////////////////////////////////////////////////////
// Payment Code Section
// //////////////////////////////////////////////////////////////////////////
if ($tabID != "REVIEW" && $tabID != "REVERSE") {
	print "\n \n <form class=\"formClass\" action=\"\" METHOD=POST NAME=\"enter\">";
	$errVar = ErrVarErr ( $profileHandle, $errVar );
	$Err_CESBCD = DecatErr_Field ( "@@sbcd", "subCode" );
	if ($paymentType == "C") {
		$CESBCD = RetValue ( "(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "') ", "ARDCEN", "CECSBC" );
	} elseif ($paymentType == "D") {
		$CESBCD = RetValue ( "(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "') ", "ARDCEN", "CEDSBC" );
	} elseif ($paymentType == "J") {
		$CESBCD = RetValue ( "(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "') ", "ARDCEN", "CEJSBC" );
	} elseif ($paymentType == "U") {
		$CESBCD = RetValue ( "(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "') ", "ARDCEN", "CEUSBC" );
	} elseif ($paymentType == "Y") {
		$CESBCD = RetValue ( "(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "') ", "ARDCEN", "CEYSBC" );
	}
	$FldStyle = "";
	if (($paymentType == "C" || $paymentType == "Y") && trim ( $Err_CESBCD ) == "") {
		$CESBCD_ERROR = RetValue ( "(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID,PRCOLM)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "','$paymentType','$paymentID','PESBCD') ", "ARPYER inner join HDERROR on ERER#=PRERR", "trim(Max(Coalesce(ERERDS,PRERR,' ')))" );
		if (trim ( $CESBCD_ERROR ) != "") {
			$FldStyle = "style=\"background-color: $errorBackground\" title=\"$CESBCD_ERROR\"  ";
		}
	}
	if (trim ( $Err_CESBCD ) != "") {
		$edtVar = EdtVarErr ( $profileHandle, $edtVar );
		$row ['CEBCHN'] = Decat_Field ( "@@bchn", $edtVar );
		$row ['CEBCHD'] = Decat_Field ( "@@bchd", $edtVar );
		$row ['CEBCHB'] = Decat_Field ( "@@bchb", $edtVar );
		$row ['CETYPE'] = Decat_Field ( "@@type", $edtVar );
		$row ['CEID'] = Decat_Field ( "@@id@@", $edtVar );
		$row ['CECHK'] = Decat_Field ( "@@chk@", $edtVar );
		$row ['CEPTYP'] = Decat_Field ( "@@ptyp", $edtVar );
		if ($FldStyle == "" && trim ( $row ['CEBCHN'] ) != trim ( $fromBatchNumber ) || trim ( $row ['CEBCHD'] ) != trim ( $fromBatchDate ) || trim ( $row ['CEBCHB'] ) != trim ( $fromBatchBank ) || trim ( $row ['CETYPE'] ) != trim ( $fromType ) || trim ( $row ['CEID'] ) != trim ( $fromID ) || trim ( $row ['CEPTYP'] ) != trim ( $paymentType )) {
			$Err_CESBCD = "";
		} else {
			$row ['CESBCD'] = Decat_Field ( "@@sbcd", $edtVar );
		}
	}
	if (is_null ( $row ['CESBCD'] )) {
		$row ['CESBCD'] = $CESBCD;
	}
	$returnURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
	if ($paymentType == "C" || trim ( $row ['CESBCD'] ) != "") {
		$fieldDesc = RetValue ( "PSSBCD='$row[CESBCD]'", "ARPYSB", "PSDESC" );
	} else {
		$fieldDesc = "";
	}
	print "\n <span class=\"dsphdr\">Payment Code</span>";
	print "\n     <span class=\"inputalph\"><input type=\"text\" name=\"subCode\" id=\"subCode\" value=\"" . rtrim ( $row ['CESBCD'] ) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editSubCode()\"> ";
	print "\n                               <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$scriptVarBase}&amp;docName=enter&amp;fldName=subCode&amp;fldDesc=subCodeDesc&amp;specificBatchType={$BMBCHT}&amp;specificPmtType=$paymentType\" onclick=\"$searchWinVar\">$searchImage</a> </span> ";
	print "\n     <span class=\"dspdesc\" id=\"subCodeDesc\">$fieldDesc</span> ";
	print "\n </form>";
}

// //////////////////////////////////////////////////////////////////////////
// Reverse Reason Section
// //////////////////////////////////////////////////////////////////////////
if ($tabID == "REVERSE") {
	print "\n \n <form class=\"formClass\" action=\"\" METHOD=POST NAME=\"enter\">";
	$errVar = ErrVarErr ( $profileHandle, $errVar );
	$Err_CERSCD = DecatErr_Field ( "@@rscd", "rvsReason" );
	$CERSCD = RetValue ( "(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "') ", "ARDCEN", "CERSCD" );
	$FldStyle = "";
	if (trim ( $Err_CERSCD ) != "") {
		$edtVar = EdtVarErr ( $profileHandle, $edtVar );
		$row ['CEBCHN'] = Decat_Field ( "@@bchn", $edtVar );
		$row ['CEBCHD'] = Decat_Field ( "@@bchd", $edtVar );
		$row ['CEBCHB'] = Decat_Field ( "@@bchb", $edtVar );
		$row ['CETYPE'] = Decat_Field ( "@@type", $edtVar );
		$row ['CEID'] = Decat_Field ( "@@id@@", $edtVar );
		$row ['CECHK'] = Decat_Field ( "@@chk@", $edtVar );
		if ($FldStyle == "" && trim ( $row ['CEBCHN'] ) != trim ( $fromBatchNumber ) || trim ( $row ['CEBCHD'] ) != trim ( $fromBatchDate ) || trim ( $row ['CEBCHB'] ) != trim ( $fromBatchBank ) || trim ( $row ['CETYPE'] ) != trim ( $fromType ) || trim ( $row ['CEID'] ) != trim ( $fromID )) {
			$Err_CERSCD = "";
		} else {
			$row ['CERSCD'] = Decat_Field ( "@@rscd", $edtVar );
		}
	}
	if (is_null ( $row ['CERSCD'] )) {
		$row ['CERSCD'] = $CERSCD;
	}
	$returnURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
	if (trim ( $row ['CERSCD'] ) != "") {
		$fieldDesc = RetValue ( "RVRSCD='$row[CERSCD]'", "ARRVRS", "RVDESC" );
	} else {
		$fieldDesc = "";
	}
	print "\n <span class=\"dsphdr\">Reverse Reason</span>";
	print "\n     <span class=\"inputalph\"><input type=\"text\" name=\"rvsReason\" id=\"rvsReason\" value=\"" . rtrim ( $row ['CERSCD'] ) . "\" size=\"4\" maxlength=\"4\" $FldStyle onBlur=\"editRvsReason()\"> ";
	print "\n                               <a href=\"{$homeURL}{$phpPath}ARReverseReasonSearch.php{$scriptVarBase}&amp;docName=enter&amp;fldName=rvsReason&amp;fldDesc=rvsReasonDesc\" onclick=\"$searchWinVar\">$searchImage</a> </span> ";
	print "\n     <span class=\"dspdesc\" id=\"rvsReasonDesc\">$fieldDesc</span> ";
	print "\n </form>";
}
// //////////////////////////////////////////////////////////////////////////
// Tab Section
// //////////////////////////////////////////////////////////////////////////
print "\n <table $contentTable><tr> ";
print "\n <td> ";

print "\n <div id=\"header\"> ";
print "\n <ul id=\"primary\"> ";

if ($tabID != "REVIEW" && $tabID != "REVERSE") {
	if ($tabID == "PAYMENT") {
		print "\n <li><span>$pymntTitle</span></li> ";
	} else {
		print "\n <li><a href=\"{$homeURL}{$phpPath}{$pymntPage}{$scriptVarBase}&amp;tag=REPORT\" title=\"Enter " . $pymntTitle . "\">$pymntTitle</a></li> ";
	}
}

if ($tabID == "ERRORS") {
	print "\n <li><span>Errors</span></li> ";
} elseif ($tabID != "REVIEW" && $tabID != "REVERSE" && $errorPage != "") {
	if ($pmtError > 0) {
		print "\n <ul id=\"reqtab\"> ";
	}
	print "\n <li><a href=\"{$homeURL}{$phpPath}{$errorPage}{$scriptVarBase}&amp;tag=REPORT\" title=\"Fix Errors for " . $pymntTitle . "\">Errors</a></li> ";
	if ($pmtError > 0) {
		print "\n </ul> ";
	}
}

if ($tabID == "COLUMN") {
	print "\n <li><span>Column</span></li> ";
} elseif ($tabID != "REVIEW" && $tabID != "REVERSE") {
	if ($rvsError == "Y") {
		print "\n <ul id=\"reqtab\"> ";
	}
	print "\n <li><a href=\"{$homeURL}{$phpPath}ApplCashPaymentColumn.php{$scriptVarBase}&amp;tag=REPORT\" title=\"Set Up Column for " . $pymntTitle . "\">Column</a></li> ";
	if ($rvsError == "Y") {
		print "\n </ul> ";
	}
}
if ($CRBBAL == "Y") {
	$BMBCHT = RetValue ( "(BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank)", "ARPBCH", "BMBCHT" );
} else {
	$BMBCHT = "";
}
if ($paymentType != "C" && $harced_OPT ['sec_01'] == "Y" && ($CRBBAL != "Y" || $BMBCHT == "D")) {
	$CPmtError = RetValue ( "(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "','C','$paymentID') ", "ARPYER", "Count(*)" );
	if ($CPmtError > 0) {
		print "\n <ul id=\"reqtab\"> ";
	}
	print "\n <li><a href=\"{$homeURL}{$phpPath}{$C_CPPMTP}{$scriptVarBase}&amp;tag=REPORT\" title=\"Enter " . trim ( $C_CPDESC ) . "\">$C_CPDESC</a></li> ";
	if ($CPmtError > 0) {
		print "\n </ul> ";
	}
}
if ($paymentType != "J" && $harced_OPT ['sec_03'] == "Y") {
	$JPmtError = RetValue ( "(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "','J','$paymentID') ", "ARPYER", "Count(*)" );
	if ($JPmtError > 0) {
		print "\n <ul id=\"reqtab\"> ";
	}
	print "\n <li><a href=\"{$homeURL}{$phpPath}{$J_CPPMTP}{$scriptVarBase}&amp;tag=REPORT\" title=\"Enter " . trim ( $J_CPDESC ) . "\">$J_CPDESC</a></li> ";
	if ($JPmtError > 0) {
		print "\n </ul> ";
	}
}
if ($paymentType != "Y" && $harced_OPT ['sec_04'] == "Y") {
	$YPmtError = RetValue ( "(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "','Y','$paymentID') ", "ARPYER", "Count(*)" );
	if ($YPmtError > 0) {
		print "\n <ul id=\"reqtab\"> ";
	}
	print "\n <li><a href=\"{$homeURL}{$phpPath}{$Y_CPPMTP}{$scriptVarBase}&amp;tag=REPORT\" title=\"Enter " . trim ( $Y_CPDESC ) . "\">$Y_CPDESC</a></li> ";
	if ($YPmtError > 0) {
		print "\n </ul> ";
	}
}
if ($paymentType != "D" && $harced_OPT ['sec_06'] == "Y" && $CRDPYC != "") {
	$DPmtError = RetValue ( "(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "','D','$paymentID') ", "ARPYER", "Count(*)" );
	if ($DPmtError > 0) {
		print "\n <ul id=\"reqtab\"> ";
	}
	print "\n <li><a href=\"{$homeURL}{$phpPath}{$D_CPPMTP}{$scriptVarBase}&amp;tag=REPORT\" title=\"Enter " . trim ( $D_CPDESC ) . "\">$D_CPDESC</a></li> ";
	if ($DPmtError > 0) {
		print "\n </ul> ";
	}
}
if ($paymentType != "U" && $harced_OPT ['sec_02'] == "Y" && ($CRBBAL != "Y" || $BMBCHT == "D")) {
	$UPmtError = RetValue ( "(PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "','U','$paymentID') ", "ARPYER", "Count(*)" );
	if ($UPmtError > 0) {
		print "\n <ul id=\"reqtab\"> ";
	}
	print "\n <li><a href=\"{$homeURL}{$phpPath}{$U_CPPMTP}{$scriptVarBase}&amp;tag=REPORT\" title=\"Enter " . trim ( $U_CPDESC ) . "\">$U_CPDESC</a></li> ";
	if ($UPmtError > 0) {
		print "\n </ul> ";
	}
}

if ($tabID == "REVERSE") {
	print "\n <li><span>Reverse</span></li> ";
} else {
	if ($rvsError == "Y") {
		print "\n <span id=\"reqtab\"> ";
	}
	print "\n <li><a href=\"{$homeURL}{$phpPath}ApplCashPaymentReverse.php{$scriptVarBase}&amp;tag=REPORT\" title=\"Reverse Payments\">Reverse</a></li> ";
	if ($rvsError == "Y") {
		print "\n </span> ";
	}
}

if ($tabID == "REVIEW") {
	print "\n <li><span>Review</span></li> ";
} else {
	print "\n <li><a href=\"{$homeURL}{$phpPath}ApplCashPaymentReview.php{$scriptVarBase}&amp;tag=REPORT\" title=\"Click here to review the payments\">Review</a></li> ";
}

print "\n </ul> ";
print "\n </div> ";

print "\n <div id=\"main\"> ";
print "\n <div id=\"contents\"> ";
?>
