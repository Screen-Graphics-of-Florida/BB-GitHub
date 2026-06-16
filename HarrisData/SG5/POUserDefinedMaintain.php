<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$orderControl = (isset($_GET['orderControl'])) ? $_GET['orderControl'] : 0;
$fromPO       = (isset($_GET['fromPO'])) ? $_GET['fromPO'] : 0;
$fromLine     = (isset($_GET['fromLine'])) ? $_GET['fromLine'] : 0;
$fromItem     = (isset($_GET['fromItem'])) ? $_GET['fromItem'] : null;
$fromItemDesc = (isset($_GET['fromItemDesc'])) ? $_GET['fromItemDesc'] : '';
$UFFILN       = (isset($_GET['udTable'])) ? $_GET['udTable'] : "POOUMS";
$popUpWin = "Y";

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'POUserDefinedInclude.php';
require_once 'UserDefined_Number_Include.php';
require_once 'VarBase.php';

$page_title     = "PO User-Defined Maintenance";
$scriptName     = "POUserDefinedMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;fromLine=" . urlencode(trim($fromLine)) . "&amp;fromPO=" . urlencode(trim($fromPO)) . "&amp;udTable=" . urlencode(trim($UFFILN));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPOUHU_W";
$editVariables  = "";
$requVariables  = "";

if ($fromPO > 0) {
	$stmtSQL = " Select * From POPOMS Where POPO=$fromPO";
} else {
	$stmtSQL = " Select * From POHDRW Where H1OCTL=$orderControl";
}
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

if ($fromLine > 0) {
	if ($fromPO > 0) {
		$stmtSQL = " Select * From POPOMD Where PDPO=$fromPO and PDPOL#=$fromLine";
	} else {
		$stmtSQL = " Select * From PODTLW Where D1OCTL=$orderControl and D1LINE=$fromLine";
	}
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$dtlRow = db2_fetch_assoc($sqlResult);
}

if ($fromPO > 0) {
	$vendorNumber = $row['POVEND'];
	$type = $row['POTYPE'];
	$pcls = $dtlRow['PDPCLS'];
} else {
	$vendorNumber = $row['H1VEND'];
	$type = $row['H1TYPE'];
	$pcls = $dtlRow['D1PCLS'];
}

$udCol = Rtv_PO_UserDefined_Columns($UFFILN, $vendorNumber, $type, $pcls);
if ($maintenanceCode != "A") {$poCol = Rtv_UserDefined_Values($orderControl, $fromPO, $fromLine);}

if ($tag == "MAINTAIN") {
	foreach ($udCol as $udFld)  {
		$UFFLDN = trim($udFld['UFFLDN']);
		$UFTYPE = trim($udFld['UFTYPE']);
		$UFSIZE = trim($udFld['UFSIZE']);
		$UFDECM = trim($udFld['UFDECM']);
		$UFREQF = trim($udFld['UFREQF']);

		if ($UFTYPE == "N") {
			$editVariables .= ($editVariables != '') ? ' && ' : '';
			$editVariables .=   " editNum(document.Chg." . $UFFLDN . "," . $UFSIZE . "," . $UFDECM . ") ";
		} else if ($UFTYPE == "D") {
			$editVariables .= ($editVariables != '') ? ' && ' : '';
			$editVariables .=   " editdate(document.Chg." . $UFFLDN . ") ";
		}

		if ($UFREQF == "Y") {
			$requVariables .= ($requVariables != '') ? ' || ' : '';
			$requVariables .=   " document.Chg." . $UFFLDN . ".value == \"\" ";
		}
	}

	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'CheckReqFieldJava.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'SaveCurrentURL.php';
	require_once 'UpperCase.php';

	if ($requVariables == '' && $editVariables == '') {
		require_once 'NoFormValidate.php';
	} else {
		print "\n function validate(chgForm) {";
		if ($requVariables != "") {
			print "\n if ( ";
			print "\n $requVariables";
			print "\n ) {alert(\"$reqFieldError\"); return false;} ";
		}
		if ($editVariables != "") {
			print "\n if ( ";
			print "\n $editVariables";
			print "\n ) ";
		}
		print "\n   {return true;} ";
		print "\n  } ";
	}

	print "\n  function confirmDelete() {return confirm(\"$delRecordConf\")} ";

	print "\n  function checkCmtLength(textField) { ";
	print "\n  	 if (textField.length > 1800) { ";
	print "\n      alert('Maximum column size has been reached.'); ";
	print "\n      return false; ";
	print "\n    } ";
	print "\n    return true; ";
	print "\n  } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	print "\n <td class=\"content\">";

	print "\n <table $contentTable>";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>$page_title</h1></td>";
	print "\n     <td class=\"toolbar\">";
	if (($sec_01 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "Z")) || ($sec_02 != "N" && $maintenanceCode == "C") || ($maintenanceCode != "A" && $maintenanceCode != "C" && $maintenanceCode != "D" && $maintenanceCode != "Z")) {
		print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
	}
	print "\n <a href=\"javascript:window.close()\">$closeImageMed</a> ";

	$medIcon= "Y";
	require 'HelpPage.php';
	print "\n </td></tr></table>";
	print "\n <table $contentTable>";
	$vendorName = RetValue("VMVEND={$vendorNumber}", "HDVEND", "VMVNA1");
	if ($fromPO > 0) {
		Format_Header("Order", $fromPO, "");
	}
	Format_Header("Vendor", $vendorName, $vendorNumber);
	if (!is_null($fromItem)) {
		Format_Header("Item", $fromItemDesc, $fromItem);
	}
	print "\n </table>";
	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
	print "\n     <table $contentTable> ";

	foreach ($udCol as $udFld)  {
		$UFFLDN = trim($udFld['UFFLDN']);
		$UFDESC = trim($udFld['UFDESC']);
		$UFTYPE = trim($udFld['UFTYPE']);
		$UFSIZE = trim($udFld['UFSIZE']);
		$UFDECM = trim($udFld['UFDECM']);
		$UFVALU = trim($udFld['UFVALU']);
		$UFBOXS = trim($udFld['UFBOXS']);
		$UFREQF = trim($udFld['UFREQF']);
		$UFVLDV = trim($udFld['UFVLDV']);
		$OUFLDD="";
		$OUFLDR="";
		$OUFLDV="";

		if ($errFound != "") {
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$catFld=$UFFLDN; while(strlen($catFld)<6) {$catFld.="@";}
			$errFldName=DecatErr_Field($catFld, $UFFLDN);
			$fieldValue=Decat_Field($catFld,$edtVar);
			if      ($UFTYPE == "D") {$OUFLDD=$fieldValue;}
			else if ($UFTYPE == "N") {$OUFLDR=$fieldValue;}
			else                     {$OUFLDV=$fieldValue;}
		} else {
			foreach ($poCol as $poFld)  {
				if ($UFFLDN==trim($poFld['WUFLDN'])) {
					$OUFLDD = trim($poFld['WUFLDD']);
					$OUFLDR = trim($poFld['WUFLDR']);
					$OUFLDV = trim($poFld['WUFLDV']);
				}
			}

			if      ($UFTYPE == "N") {$OUFLDR=number_format($OUFLDR,$UFDECM,'.','');}
			else if ($UFTYPE == "D") {$OUFLDD=DateInputFromISO($OUFLDD); }
		}

		if ($UFREQF == "Y") {$fldReqDesc = $reqFieldChar;}
		else                {$fldReqDesc = "";}
		$textOvr=SetTextOvr($errFldName);
		print "\n     <tr><td class=\"dsphdr\"><span $textOvr>$UFDESC</span></td> ";
		if ($UFTYPE == "A" && $UFVALU != "Y" && $UFVLDV != "Y") {
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"$UFFLDN\" id=\"$UFFLDN\" value=\"" . rtrim($OUFLDV) . "\" size=\"$UFSIZE\" maxlength=\"$UFSIZE\"> $fldReqDesc ";
		} else if ($UFTYPE == "C") {
			print "\n     <td class=\"inputalph\"> ";
			if ($OUFLDV != "") {print "\n     <textarea name=\"$UFFLDN\" id=\"$UFFLDN\" ROWS=$UFBOXS COLS=60 onkeyup=\"checkCmtLength(this.value)\" onkeypress=\"checkCmtLength(this.value)\">" . rtrim($OUFLDV) . "</textarea> $fldReqDesc ";}
			else               {print "\n     <textarea name=\"$UFFLDN\" id=\"$UFFLDN\" ROWS=$UFBOXS COLS=60 onkeyup=\"checkCmtLength(this.value)\" onkeypress=\"checkCmtLength(this.value)\"></textarea> $fldReqDesc ";}
		} else if ($UFTYPE == "N") {
			$UFSIZE+=$UFDECM;
			if ($UFDECM>0) {$UFSIZE+=2;}
			print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"$UFFLDN\" id=\"$UFFLDN\" value=\"" . rtrim($OUFLDR) . "\" size=\"$UFSIZE\" maxlength=\"$UFSIZE\"> $fldReqDesc ";
		} else if ($UFTYPE == "D") {
			print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"$UFFLDN\" id=\"$UFFLDN\" value=\"" . rtrim($OUFLDD) . "\" size=\"6\" maxlength=\"6\"> $fldReqDesc ";
			if ($UFVLDV != "Y") {print "\n <a href=\"javascript:calWindow('" . $UFFLDN . "');\">$calendarImage</a> ";}
		}
		if ($UFVALU == "Y" || $UFVLDV == "Y") {
			require 'stmtSQLClear.php';
			$stmtSQL .= " Select UVFLDV ";
			$fileSQL .= " SYUDFV ";
			$selectSQL .= " UVFILN='" . trim($UFFILN) . "' and UVFLDN='" . trim($UFFLDN) . "' and UVEVNT=' ' ";
			require 'stmtSQLSelect.php';
			$stmtSQL .= " Order By UVSEQ# ";
			$stmtSQL .= " For Fetch Only with NC ";
			$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
			print "\n     <td class=\"inputalph\">";
			print "\n <select class=\"inputalph\" name=\"$UFFLDN\">";
			print "\n <option value=\"\">";
			$rowCount = 0;
			while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
				$selected = (trim($row[UVFLDV]) == trim($OUFLDV)) ? 'SELECTED' : '';
				print "\n <option VALUE=\"$row[UVFLDV]\" $selected>$row[UVFLDV]";
				$startRow ++;
				$rowCount ++;
			}
			print "\n </select>$fldReqDesc";
			//	print "\n     <a href=\"{$homeURL}{$phpPath}userdefinedsearch.php{$genericVarBase}&amp;docName=Chg&amp;fileName=" . trim($UFFILN) . "&amp;fldName=" . trim($UFFLDN) . "&amp;fldType=" . trim($UFTYPE) . "&amp;fldDesc=". urlencode(trim($UFDESC)) . "\" onclick=\"$searchWinVar\"> $searchImage </a> ";
		}

		print "\n     </td></tr> ";
		DspErrMsg($errFldName);
	}
	$errFound= "";
	print "\n     </table> ";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n </script>";
	print "\n </form>";

	print "\n <table $contentTable>";
	print "\n     <tr>";
	print "\n         <td class=\"toolbar\">";
	if (($sec_01 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "Z")) || ($sec_02 != "N" && $maintenanceCode == "C") || ($maintenanceCode != "A" && $maintenanceCode != "C" && $maintenanceCode != "D" && $maintenanceCode != "Z")) {
		print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
	}
	print "\n <a href=\"javascript:window.close()\">$closeImageMed</a> ";
	$medIcon= "Y";
	require "HelpPage.php";
	print "\n         </td>";
	print "\n     </tr>";
	print "\n </table>";

	print $hrTagAttr;
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	$edtVar="";
	Concat_Field("@@file", $UFFILN);
	Concat_Field("@@octl", $orderControl);
	Concat_Field("@@popo", $fromPO);
	Concat_Field("@@line", $fromLine);
	$edtVar .= "}{";

	foreach ($udCol as $udFld)  {
		$UFFLDN = trim($udFld['UFFLDN']);
		$UFTYPE = trim($udFld['UFTYPE']);
		$UFSIZE = trim($udFld['UFSIZE']);
		$UFDECM = trim($udFld['UFDECM']);
		$UFUPPR = trim($udFld['UFUPPR']);

		$outField=$_POST[$UFFLDN];
		if ($UFUPPR == "Y") {$outField=strtoupper($outField);}
		if ($UFTYPE == "N") {$outField=Build_User_Number($outField, $UFSIZE, $UFDECM);}

		while (strlen($UFFLDN)<6) {$UFFLDN .="@";}
		$edtVar .= rtrim($UFFLDN) . rtrim($outField) . "}{";
	}

    //echo $edtVar;
	//exit();

	$returnValue=Maintain_UserDefined("HPOUHU_W", $errFound, $edtVar, $errVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "") {
		print "\n <script TYPE=\"text/javascript\">opener.location.reload(); window.close()</script> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>
