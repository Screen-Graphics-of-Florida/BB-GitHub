<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromScript         = $_GET['fromScript'];
$fromPayer          = $_GET['fromPayer'];

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
require_once 'StoredProcedureVariablesInclude.php';
require_once 'UserDefined_Number_Include.php';
require_once 'VarBase.php';

$page_title     = "Payer Maintenance";
$scriptName     = "PayerMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromPayer=" . urlencode(trim($fromPayer)) . "&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$cancelWFURL    = "{$baseURL}&amp;tag=CancelWF";
$programName    = "HARPYM_E";
$vldPgmName     = "HARPYM_E";
$UFFILN         = "ARPYRU";
$editVariables  = "";
$requVariables  = "";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=100";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

$userPass=PayerUserView($profileHandle, $fromPayer, "Y");
if ($userPass == "N") {
	require_once 'UserViewErrorInclude.php';
	exit;
}

$udCol = Rtv_UserDefined_Columns($UFFILN, "");
if ($maintenanceCode != "A") {$arCol = Rtv_ARPYRUTable_Columns($fromPayer);}

if ($tag == "WFREVIEW") {
	$setFlag="Y";
	$edtVar="";
	Concat_Field("@@inst", $wfInstance);
	Concat_Field("@@indt", $wfInstanceDate);
	Concat_Field("@@witm", $wfWorkItem);
	Concat_Field("@@wisq", $wfWorkItemSequence);
	Concat_Field("@@ptid", $wfParticipantId);
	$edtVar .= "}{";
	SetApprove_Data($profileHandle, $dataBaseID, $setFlag, $edtVar);

	$edtVar=RetValue("(ADINST,ADINDT)=($wfInstance,$wfInstanceDate)", "WFDTAA", "ADVAR");
	$WFReview="Y";
	$maintenanceCode=Decat_Field("@@mncd", $edtVar);
	$fromPayer      =Decat_Field("@@frm1", $edtVar);
}

if ($tag == "MAINTAIN" || $tag == "WFREVIEW") {
	$vldExcptExist=RtvWFExcptExist($profileHandle, $programName, $vldPgmName, "E");

	foreach ($udCol as $udFld)  {
		$UFFLDN = trim($udFld['UFFLDN']);
		$UFTYPE = trim($udFld['UFTYPE']);
		$UFSIZE = trim($udFld['UFSIZE']);
		$UFDECM = trim($udFld['UFDECM']);
		$UFREQF = trim($udFld['UFREQF']);

		if      ($UFTYPE == "N") {$editVariables .=   " editNum(document.Chg." . $UFFLDN . "," . $UFSIZE . "," . $UFDECM . ") && ";}
		else if ($UFTYPE == "D") {$editVariables .=   " editdate(document.Chg." . $UFFLDN . ") && ";}

		if ($UFREQF == "Y") {$requVariables .=   "  && checkReqField(document.Chg." . $UFFLDN . ") ";}
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

	print "\n function validate(chgForm) {";
	if ($vldExcptExist == "N") {
		print "\n if (document.Chg.payerName.value ==\"\" ";
		if ($editVariables!="") {print "\n $requVariables"; }
		print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	}

	print "\n if ( $editVariables ";
	print "\n     editNum(document.Chg.payer, 7, 0) && ";
	print "\n     editNum(document.Chg.phoneNumber, 11, 0) && ";
	print "\n     editNum(document.Chg.faxNumber, 11, 0) && ";
	print "\n     editNum(document.Chg.contactPhone, 11, 0)  ";
	print "\n ) return true; ";
	print "\n } ";

	print "\n  function confirmDelete() {return confirm(\"$delRecordConf\")} ";
	print "\n  function confirmCancelWF() {return confirm(\"Cancel Processing of Work Item\")} ";

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
	$pageID = "PAYERMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A" || $WFReview=="Y") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From ARPYRH ";
		$stmtSQL .= " Where PYPAYR=$fromPayer ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$harpym_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harpym_OPT['sec_01'];
	$sec_02=$harpym_OPT['sec_02'];
	$sec_03=$harpym_OPT['sec_03'];;
	$sec_04=$harpym_OPT['sec_04'];;
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($CRAPYR == "Y") {$focusField="payerName";}
	else                {$focusField="payer";}

	if ($errFound != "" || $WFReview=="Y" || $maintenanceCode=="A") {
		if ($errFound == "" && $WFReview=="" && $maintenanceCode=="A") {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_PYPAYR=DecatErr_Field("@@payr", "payer");
			$Err_PYPYNM=DecatErr_Field("@@pynm", "payerName");
			$Err_PYADR1=DecatErr_Field("@@adr1", "addressLineOne");
			$Err_PYADR2=DecatErr_Field("@@adr2", "addressLineTwo");
			$Err_PYADR3=DecatErr_Field("@@adr3", "addressLineThree");
			$Err_PYCITY=DecatErr_Field("@@city", "city");
			$Err_PYST  =DecatErr_Field("@@st@@", "state");
			$Err_PYZIP =DecatErr_Field("@@zip@", "zip");
			$Err_PYCTRY=DecatErr_Field("@@ctry", "country");
			$Err_PYPHON=DecatErr_Field("@@phon", "phoneNumber");
			$Err_PYFAX =DecatErr_Field("@@fax@", "faxNumber");
			$Err_PYCTOR=DecatErr_Field("@@ctor", "collector");
			$Err_PYCONT=DecatErr_Field("@@cont", "contact");
			$Err_PYPHON=DecatErr_Field("@@cphn", "contactPhone");
			$Err_PYEMAL=DecatErr_Field("@@emal", "contactEmail");
			$Err_PYSBCD=DecatErr_Field("@@sbcd", "paymentMethod");
			// $errFound= "";  // Needed for UserDefined Fields
		}
		$row['PYPAYR']=Decat_Field("@@payr", $edtVar);
		$row['PYPYNM']=Decat_Field("@@pynm", $edtVar);
		$row['PYADR1']=Decat_Field("@@adr1", $edtVar);
		$row['PYADR2']=Decat_Field("@@adr2", $edtVar);
		$row['PYADR3']=Decat_Field("@@adr3", $edtVar);
		$row['PYCITY']=Decat_Field("@@city", $edtVar);
		$row['PYST']  =Decat_Field("@@st@@", $edtVar);
		$row['PYZIP'] =Decat_Field("@@zip@", $edtVar);
		$row['PYCTRY']=Decat_Field("@@ctry", $edtVar);
		$row['PYPHON']=Decat_Field("@@phon", $edtVar);
		$row['PYFAX'] =Decat_Field("@@fax@", $edtVar);
		$row['PYCTOR']=Decat_Field("@@ctor", $edtVar);
		$row['PYCONT']=Decat_Field("@@cont", $edtVar);
		$row['PYCPHN']=Decat_Field("@@cphn", $edtVar);
		$row['PYEMAL']=Decat_Field("@@emal", $edtVar);
		$row['PYSBCD']=Decat_Field("@@sbcd", $edtVar);
		$row['PYTSTP']=Decat_Field("@@tstp", $edtVar);

	} else {
		$focusField="payerName";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['PYTSTP']) . "\"></td></tr> ";

	$textOvr=SetTextOvr($Err_PYPAYR);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Payer</span></td> ";
	if (($maintenanceCode == "A" || $maintenanceCode == "Z") && $CRAPYR == "Y" && $WFReview=="") {
		print "\n  <td class=\"inputnmbr\"><input type=\"hidden\" name=\"payer\" value=\"0\"></td> ";
	} elseif ($maintenanceCode == "A" || $maintenanceCode == "Z") {
		print "\n  <td class=\"inputnmbr\"><input type=\"text\" name=\"payer\" value=\"" . trim($row['PYPAYR']) . "\" size=\"7\" maxlength=\"7\"> $reqFieldChar</td> ";
	} else {
		print "\n  <td class=\"inputnmbr\"><input type=\"hidden\" name=\"payer\" value=\"" . trim($row['PYPAYR']) . "\">" . trim($row['PYPAYR']) . "</td> ";
	}
	print "\n  </tr> ";
	DspErrMsg($Err_PYPAYR);

	Build_Fld_Entry("Name","payerName","inputalph","","PYPYNM",$row[PYPYNM],$Err_PYPYNM,"26","26","Y","","");
	Build_Fld_Entry("Address Line One","addressLineOne","inputalph","","PYADR1",$row[PYADR1],$Err_PYADR1,"26","26","","","");
	Build_Fld_Entry("Address Line Two","addressLineTwo","inputalph","","PYADR2",$row[PYADR2],$Err_PYADR2,"26","26","","","");
	Build_Fld_Entry("Address Line Three","addressLineThree","inputalph","","PYADR3",$row[PYADR3],$Err_PYADR3,"19","19","","","");
	$textOvr=SetTextOvr($Err_PYCITY);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>City</span></td> ";
	print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"city\" value=\"" . trim($row['PYCITY']) . "\" size=\"26\" maxlength=\"26\"></td> ";
	if ($Err_PYCITY != '') {
	    print "\n <td class=\"dsphdr\">  Accept Address<input type=\"checkbox\" class=\"checkbox\" name=\"acceptAddress\" id=\"acceptAddress\" value=\"Y\"></td>";
	}
	print "\n  </tr> ";
	DspErrMsg($Err_PYCITY);

	$fieldDesc=RetValue("STID='$row[PYST]'", "HDSTID", "STDESC");
	$textOvr=SetTextOvr($Err_PYST);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>State</span></td> ";
	print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"state\" onChange=\"chkUpper(this)\" value=\"" . trim($row['PYST']) . "\" size=\"5\" maxlength=\"2\"> ";
	print "\n                              <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=state&amp;fldDesc=stateDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                              <span class=\"dspdesc\" id=\"stateDesc\">$fieldDesc</span></td>";
	print "\n  </tr> ";
	DspErrMsg($Err_PYST);

	Build_Fld_Entry("Zip","zip","inputalph","","PYZIP",$row[PYZIP],$Err_PYZIP,"13","13","","","");

	$fieldDesc=RetValue("CNCTCD='$row[PYCTRY]'", "HDCTRY", "CNCDES");
	$textOvr=SetTextOvr($Err_PYCTRY);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Country</span></td> ";
	print "\n      <td class=\"inputalph\"><input name=\"country\" type=\"text\" value=\"" . trim($row['PYCTRY']) . "\" size=\"5\" maxlength=\"3\"> ";
	print "\n                              <a href=\"{$homeURL}{$phpPath}CountrySearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=country&amp;fldDesc=countryDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                              <span class=\"dspdesc\" id=\"countryDesc\">$fieldDesc</span></td>";
	print "\n  </tr> ";
	DspErrMsg($Err_PYCTRY);

	Build_Fld_Entry("Phone","phoneNumber","inputnmbr","","PYPHON",$row[PYPHON],$Err_PYPHON,"11","11","","","");
	Build_Fld_Entry("Fax","faxNumber","inputnmbr","","PYFAX",$row[PYFAX],$Err_PYFAX,"11","11","","","");

	$fieldDesc=RetValue("PSSBCD='$row[PYSBCD]'", "ARPYSB", "PSDESC");
	$textOvr=SetTextOvr($Err_PYSBCD);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Preferred Payment Method</span></td> ";
	print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"paymentMethod\" value=\"" . trim($row['PYSBCD']) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                              <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=paymentMethod&amp;fldDesc=paymentMethodDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                              <span class=\"dspdesc\" id=\"paymentMethodDesc\">$fieldDesc</span></td>";
	print "\n  </tr> ";
	DspErrMsg($Err_PYSBCD);

	$fieldDesc=RetValue("USUSER='$row[PYCTOR]'", "SYUSER", "USDESC");
	$textOvr=SetTextOvr($Err_PYCTOR);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Collector</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"collector\" value=\"" . rtrim($row['PYCTOR']) . "\" size=\"10\" maxlength=\"10\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=collector&amp;descFld=collectorName\" onclick=\"$searchWinVar\"> $searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"collectorName\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_PYCTOR);

	print "\n </table> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Contact</legend> ";
	print "\n     <table $contentTable> ";

	Build_Fld_Entry("Name","contact","inputalph","","PYCONT",$row[PYCONT],$Err_PYCONT,"16","16","","","");
	Build_Fld_Entry("Phone","contactPhone","inputnmbr","","PYCPHN",$row[PYCPHN],$Err_PYCPHN,"11","11","","","");
	Build_Fld_Entry("E-mail","contactEmail","inputalph","","PYEMAL",$row[PYEMAL],$Err_PYEMAL,"50","256","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">User-Defined</legend> ";
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
		$PUFLDD="";
		$PUFLDR="";
		$PUFLDV="";

		if ($errFound != "" || $maintenanceCode == "A") {
			$catFld=$UFFLDN; while(strlen($catFld)<6) {$catFld.="@";}
			$errFldName=DecatErr_Field($catFld, $UFFLDN);
			$fieldValue=Decat_Field($catFld,$edtVar);
			if      ($UFTYPE == "D") {$PUFLDD=$fieldValue;}
			else if ($UFTYPE == "N") {$PUFLDR=$fieldValue;}
			else                     {$PUFLDV=$fieldValue;}
		} else {
			foreach ($arCol as $arFld)  {
				if ($UFFLDN==trim($arFld['PUFLDN'])) {
					$PUFLDD = trim($arFld['PUFLDD']);
					$PUFLDR = trim($arFld['PUFLDR']);
					$PUFLDV = trim($arFld['PUFLDV']);
				}
			}

			if      ($UFTYPE == "N") {$PUFLDR=number_format($PUFLDR,$UFDECM,'.','');}
			else if ($UFTYPE == "D") {$PUFLDD=DateInputFromISO($PUFLDD); }
		}

		if ($UFREQF == "Y") {$fldReqDesc = $reqFieldChar;}
		else                {$fldReqDesc = "";}
		$textOvr=SetTextOvr($errFldName);
		print "\n     <tr><td class=\"dsphdr\"><span $textOvr>$UFDESC</span></td> ";
		if ($UFTYPE == "A") {
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"$UFFLDN\" id=\"$UFFLDN\" value=\"" . rtrim($PUFLDV) . "\" size=\"$UFSIZE\" maxlength=\"$UFSIZE\"> $fldReqDesc ";
		} else if ($UFTYPE == "C") {
			print "\n     <td class=\"inputalph\"> ";
			if ($PUFLDV != "") {print "\n     <textarea name=\"$UFFLDN\" id=\"$UFFLDN\" ROWS=$UFBOXS COLS=60 onkeyup=\"checkCmtLength(this.value)\" onkeypress=\"checkCmtLength(this.value)\">" . rtrim($PUFLDV) . "</textarea> $fldReqDesc ";}
			else               {print "\n     <textarea name=\"$UFFLDN\" id=\"$UFFLDN\" ROWS=$UFBOXS COLS=60 onkeyup=\"checkCmtLength(this.value)\" onkeypress=\"checkCmtLength(this.value)\"></textarea> $fldReqDesc ";}
		} else if ($UFTYPE == "N") {
			$UFSIZE+=$UFDECM;
			if ($UFDECM>0) {$UFSIZE+=2;}
			print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"$UFFLDN\" id=\"$UFFLDN\" value=\"" . rtrim($PUFLDR) . "\" size=\"$UFSIZE\" maxlength=\"$UFSIZE\"> $fldReqDesc ";
		} else if ($UFTYPE == "D") {
			print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"$UFFLDN\" id=\"$UFFLDN\" value=\"" . rtrim($PUFLDD) . "\" size=\"6\" maxlength=\"6\"> $fldReqDesc ";
			if ($UFVLDV != "Y") {print "\n <a href=\"javascript:calWindow('" . $UFFLDN . "');\">$calendarImage</a> ";}
		}
		if ($UFVALU == "Y" || $UFVLDV == "Y") {print "\n     <a href=\"{$homeURL}{$phpPath}userdefinedsearch.php{$genericVarBase}&amp;docName=Chg&amp;fileName=" . trim($UFFILN) . "&amp;fldName=" . trim($UFFLDN) . "&amp;fldType=" . trim($UFTYPE) . "&amp;fldDesc=". urlencode(trim($UFDESC)) . "\" onclick=\"$searchWinVar\"> $searchImage </a> ";}

		print "\n     </td></tr> ";
		DspErrMsg($errFldName);
	}
	$errFound= "";
	print "\n     </table> ";
	print "\n </fieldset> ";

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

if ($tag == "CancelWF") {
	$RecCount=RetValue("(WIINST,WIINDT,WIWITM,WIWISQ)=($wfInstance,$wfInstanceDate,$wfWorkItem,$wfWorkItemSequence)", "WFWITM", "Char(Count(*))");
	if ($RecCount >0) {
		$setFlag="";
		$edtVar="";
		Concat_Field("@@inst", $wfInstance);
		Concat_Field("@@indt", $wfInstanceDate);
		Concat_Field("@@witm", $wfWorkItem);
		Concat_Field("@@wisq", $wfWorkItemSequence);
		Concat_Field("@@ptid", $wfParticipantId);
		$edtVar .= "}{";
		SetApprove_Data($profileHandle, $dataBaseID, $setFlag, $edtVar);
	}
	print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}WFWorkList.d2w/REPORT{$genericVarBase}\"> ";
}

if ($tag == "Edit_Data") {
	if ($maintenanceCode=="D" && is_null($_POST['payer'])) {
		$_POST['payer']    =$fromPayer;
		$_POST['payerName']=RetValue("PYPAYR=$_POST[payer]", "ARPYRH", "PYPYNM");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode="A";}

	$edtVar="";
	Concat_Field("@@frm1", $fromPayer);
	Concat_Field("@@wfid", $wfInstance);
	Concat_Field("@@wfdt", $wfInstanceDate);
	Concat_Field("@@wfcn", $wfWorkItem);
	Concat_Field("@@wfsq", $wfWorkItemSequence);
	Concat_Field("@@wfpt", $wfParticipantId);
	Concat_Field("@@frm1", $fromPayer);
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	Concat_Field("@@payr", $_POST['payer']);
	Concat_Field("@@pynm", $_POST['payerName']);
	Concat_Field("@@adr1", $_POST['addressLineOne']);
	Concat_Field("@@adr2", $_POST['addressLineTwo']);
	Concat_Field("@@adr3", $_POST['addressLineThree']);
	Concat_Field("@@city", $_POST['city']);
	Concat_Field("@@st@@", strtoupper($_POST['state']));
	Concat_Field("@@zip@", strtoupper($_POST['zip']));
	Concat_Field("@@ctry", strtoupper($_POST['country']));
	Concat_Field("@@phon", $_POST['phoneNumber']);
	Concat_Field("@@fax@", $_POST['faxNumber']);
	Concat_Field("@@ctor", strtoupper($_POST['collector']));
	Concat_Field("@@cont", $_POST['contact']);
	Concat_Field("@@cphn", $_POST['contactPhone']);
	Concat_Field("@@emal", $_POST['contactEmail']);
	Concat_Field("@@sbcd", strtoupper($_POST['paymentMethod']));
	if ($_POST['acceptAddress'] == 'Y') {
	   Concat_Field("@@acad", 'Y');
	}
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
	$edtVar .= "}{";

	$returnValue=Maintain_Edit_Handle("HARPYM_W", $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		if ($maintenanceCode == "A" && $_POST['payer'] == 0) {$_POST['payer']="";}
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST['payerName'], $_POST['payer'], "", "", "", "");
		if ($WFReview=="Y") {
			print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}WFWorkList.d2w/REPORT{$genericVarBase}\"> ";
		} elseif ($maintenanceCode == "D" || $fromScript == "") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		} else {
			print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$fromScript}{$genericVarBase}&amp;tag=REPORT&amp;fromPayer=" . urlencode(trim($_POST['payer'])) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		}
	} elseif ($maintenanceCode == "D") {
		$Err_PYPAYR=DecatErr_Field("@@payr", "payer");
		$confMessage=Format_ConfMsg_Desc("", $_POST['payerName'], $_POST['payer'], "", "", "<br>$Err_PYPAYR", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=100&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;payer=" . urlencode(trim($_POST['payer'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

function SetApprove_Data($profileHandle,$dataBaseID,$approveCode,$edtVar) {
	global $pgmLibrary, $i5Connect;
	if (is_null($approveCode)) $approveCode="";
	if (is_null($edtVar))      $edtVar="";

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
