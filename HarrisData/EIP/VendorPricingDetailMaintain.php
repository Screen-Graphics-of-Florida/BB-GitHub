<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode   = $_GET['maintenanceCode'];
$errFound          = $_GET['errFound'];
$wrnVar            = $_GET['wrnVar'];
$pricingKey        = $_GET['pricingKey'];
$fromScript        = $_GET['fromScript'];
$levelDesc         = $_GET['levelDesc'];
$pricingLevel      = $_GET['pricingLevel'];
$dollarAmt         = $_GET['dollarAmt'];
$usePercent        = $_GET['usePercent'];
$contract          = $_GET['contract'];
$bracketAmt        = $_GET['bracketAmt'];
$contractStartDate = $_GET['contractStartDate'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Vendor Pricing Detail Maintenance";
$scriptName     = "VendorPricingDetailMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;pricingLevel=" . urlencode(trim($pricingLevel)) . "&amp;levelDesc=" . urlencode(trim($levelDesc)) . "&amp;pricingKey=" . urlencode(trim($pricingKey)) . "&amp;dollarAmt=" . urlencode(trim($dollarAmt)) . "&amp;usePercent=" . urlencode(trim($usePercent)) . "&amp;contract=" . urlencode(trim($contract)) . "&amp;bracketAmt=" . urlencode(trim($bracketAmt)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;contractStartDate=" . urlencode(trim($contractStartDate)) . "&amp;maintenanceCode=D";
$programName    = "HPOPLM_E";
$backURL="{$homeURL}{$phpPath}VendorPricingDetail.php{$scriptVarBase}&amp;tblID=7";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

$mdCol = Rtv_Pricing_Categories($profileHandle, $pricingLevel);
$requiredFields = "";
$editVariables =  "";

foreach ($mdCol as $mdFld)  {
	$curdType = trim($mdFld['PWDTYP']);
	$curfType = trim($mdFld['PWFTYP']);
	$curText = trim($mdFld['PWTEXT']);
	$curName = trim($mdFld['PWCOLN']);
	$curSize = trim($mdFld['PWFLEN']);
	$curDecm = trim($mdFld['PWFDEC']);
	$chgCurName = "chg$curName";

	if ($requiredFields != "") {$requiredFields .= "||" ;}
	$requiredFields .= " document.Chg.$chgCurName.value == \"\" ";

	if ($curdType == "NUMERIC" || $curdType == "DECIMAL") {
		if ($editVariables != "") {$editVariables .= " && " ;}
		if ($curfType == "CYMD" || $curfType == "ISO"){
			$editVariables .= " editdate(document.Chg.$chgCurName) ";
		} else {
			$editVariables .= " editNum(document.Chg." ;
			$editVariables .= "$chgCurName,$curSize,$curDecm)";
		}
	}
	if  ($curNameHld == "") {$curNameHld = $chgCurName;}
	if  ($dftOrderBy == "") {$dftOrderBy = array(array("$chgCurName","A","$curText"));}
}

if ($contract == "Y" && $errFound != "") {$contractStartDate =DateToCYMD($contractStartDate);}

if ($contract == "Y") {
	if ($requiredFields != "") {$requiredFields .= " ||";}
	$requiredFields .= " document.Chg.contractStartDate.value == \"\" ";
	$requiredFields .= " || document.Chg.contractExpirationDate.value == \"\" ";
	if ($editVariables != "") {$editVariables .= " &&";}
	$editVariables .= " editdate(document.Chg.contractStartDate)";
	$editVariables .= " && editdate(document.Chg.contractExpirationDate)";
}

if ($bracketAmt == "Y") {
	if ($editVariables != "") {$editVariables .= " && ";}
	$editVariables .= " editNum(document.Chg.bracketLimit1,9,4)";
	$editVariables .= " && editNum(document.Chg.bracketLimit2,9,4)";
	$editVariables .= " && editNum(document.Chg.bracketLimit3,9,4)";
	$editVariables .= " && editNum(document.Chg.bracketLimit4,9,4)";
	$editVariables .= " && editNum(document.Chg.bracketLimit5,9,4)";
	$editVariables .= " && editNum(document.Chg.bracketLimit6,9,4)";
	$editVariables .= " && editNum(document.Chg.bracketLimit7,9,4)";
	$editVariables .= " && editNum(document.Chg.bracketLimit8,9,4)";

	if ($usePercent == "Y") {
		$editVariables .= " && editNum(document.Chg.bracketPercentage1,7,6)";
		$editVariables .= " && editNum(document.Chg.bracketPercentage2,7,6)";
		$editVariables .= " && editNum(document.Chg.bracketPercentage3,7,6)";
		$editVariables .= " && editNum(document.Chg.bracketPercentage4,7,6)";
		$editVariables .= " && editNum(document.Chg.bracketPercentage5,7,6)";
		$editVariables .= " && editNum(document.Chg.bracketPercentage6,7,6)";
		$editVariables .= " && editNum(document.Chg.bracketPercentage7,7,6)";
		$editVariables .= " && editNum(document.Chg.bracketPercentage8,7,6)";
	} else  {
		$editVariables .= " && editNum(document.Chg.bracketAmount1,8,5)";
		$editVariables .= " && editNum(document.Chg.bracketAmount2,8,5)";
		$editVariables .= " && editNum(document.Chg.bracketAmount3,8,5)";
		$editVariables .= " && editNum(document.Chg.bracketAmount4,8,5)";
		$editVariables .= " && editNum(document.Chg.bracketAmount5,8,5)";
		$editVariables .= " && editNum(document.Chg.bracketAmount6,8,5)";
		$editVariables .= " && editNum(document.Chg.bracketAmount7,8,5)";
		$editVariables .= " && editNum(document.Chg.bracketAmount8,8,5)";
	}
}
if (($dollarAmt == "Y" && $bracketAmt != "Y" && $usePercent != "Y") || ($bracketAmt == "Y" && $usePercent == "Y")) {
	if ($editVariables != "") {$editVariables .= " &&";}
	$editVariables .= " editZero(document.Chg.dolAmount,8,5)";
}
if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'DateEdit.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if ($requiredFields)";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if ($editVariables) ";
	print "\n return true;";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "VENDORPRICINGDETAILMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From POVPDT ";
		$stmtSQL .= " Where VDPMKY='$pricingKey' and VDPMLV='$pricingLevel' and VDSTDT='$contractStartDate' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hpoprm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hpoprm_OPT['sec_01'];
	$sec_02=$hpoprm_OPT['sec_02'];
	$sec_03=$hpoprm_OPT['sec_03'];
	$sec_04=$hpoprm_OPT['sec_04'];

	require_once 'MaintainTop.php';

	print "\n <table $contentTable>";

	Format_Header_URL("Pricing Level", $levelDesc, $pricingLevel, "");

	if ($dollarAmt == "Y")  {$structureDefn = "Dollar";}
	if ($usePercent == "Y") {$structureDefn = "Percentage";} else {$structureDefn .= " Amount";}

	print "\n <tr><td class=\"hdrtitl\">Definition:</td>";
	if ($contract == "Y")  {
		print "\n   <td class=\"hdrdata\">Contract</td></tr>";
		print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">$structureDefn</td></tr>";
	} else {
		print "\n   <td class=\"hdrdata\">$structureDefn</td></tr>";
	}

	if  ($bracketAmt == "Y")  {
		print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">Bracket By Quantity</td></tr>";
	}

	print "\n </table>";

	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
		} else {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_VDPMLV =DecatErr_Field("@@pmlv", "pricingLevel");
			$Err_VDPMKY =DecatErr_Field("@@pmky", "pricingKey");
			$Err_General=DecatErr_Field("@@gerr", "chg$curName");
			$Err_VDSTDT =DecatErr_Field("@@stdt", "contractStartDate");
			$Err_VDEXDT =DecatErr_Field("@@exdt", "contractExpirationDate");
			$Err_VDQOTE =DecatErr_Field("@@qote", "quote");
			$Err_VDAMNT =DecatErr_Field("@@amnt", "dolAmount");
			$Err_VDBKL1 =DecatErr_Field("@@bkl1", "bracketLimit1");
			$Err_VDBKP1 =DecatErr_Field("@@bkp1", "bracketAmount1");
			$Err_VDBKC1 =DecatErr_Field("@@bkc1", "bracketPercentage1");
			$Err_VDBKL2 =DecatErr_Field("@@bkl2", "bracketLimit2");
			$Err_VDBKP2 =DecatErr_Field("@@bkp2", "bracketAmount2");
			$Err_VDBKC2 =DecatErr_Field("@@bkc2", "bracketPercentage2");
			$Err_VDBKL3 =DecatErr_Field("@@bkl3", "bracketLimit3");
			$Err_VDBKP3 =DecatErr_Field("@@bkp3", "bracketAmount3");
			$Err_VDBKC3 =DecatErr_Field("@@bkc3", "bracketPercentage3");
			$Err_VDBKL4 =DecatErr_Field("@@bkl4", "bracketLimit4");
			$Err_VDBKP4 =DecatErr_Field("@@bkp4", "bracketAmount4");
			$Err_VDBKC4 =DecatErr_Field("@@bkc4", "bracketPercentage4");
			$Err_VDBKL5 =DecatErr_Field("@@bkl5", "bracketLimit5");
			$Err_VDBKP5 =DecatErr_Field("@@bkp5", "bracketAmount5");
			$Err_VDBKC5 =DecatErr_Field("@@bkc5", "bracketPercentage5");
			$Err_VDBKL6 =DecatErr_Field("@@bkl6", "bracketLimit6");
			$Err_VDBKP6 =DecatErr_Field("@@bkp6", "bracketAmount6");
			$Err_VDBKC6 =DecatErr_Field("@@bkc6", "bracketPercentage6");
			$Err_VDBKL7 =DecatErr_Field("@@bkl7", "bracketLimit7");
			$Err_VDBKP7 =DecatErr_Field("@@bkp7", "bracketAmount7");
			$Err_VDBKC7 =DecatErr_Field("@@bkc7", "bracketPercentage7");
			$Err_VDBKL8 =DecatErr_Field("@@bkl8", "bracketLimit8");
			$Err_VDBKP8 =DecatErr_Field("@@bkp8", "bracketAmount8");
			$Err_VDBKC8 =DecatErr_Field("@@bkc8", "bracketPercentage8");
		}

		$row['VDPMLV']=Decat_Field("@@pmlv", $edtVar);
		$row['VDWHS'] =Decat_Field("@@whs@", $edtVar);
		$row['VDITEM']=Decat_Field("@@item", $edtVar);
		$row['VDVEND']=Decat_Field("@@vend", $edtVar);
		$row['VDPMKY']=Decat_Field("@@pmky", $edtVar);
		if ($contract == "Y")  {
			$row['VDSTDT']=Decat_Field("@@stdt", $edtVar);
			$F_VDSTDT=Format_Date(DateToCYMD($row['VDSTDT']), "D");
			$row['VDEXDT']=Decat_Field("@@exdt", $edtVar);
			$F_VDEXDT=Format_Date(DateToCYMD($row['VDEXDT']), "D");
		}
		$row['VDAMNT']=Decat_Field("@@amnt", $edtVar);
		$row['VDQOTE']=Decat_Field("@@qote", $edtVar);
		if ($bracketAmt == "Y")  {
			$row['VDBKL1']=Decat_Field("@@bkl1", $edtVar);
			$row['VDBKL2']=Decat_Field("@@bkl2", $edtVar);
			$row['VDBKL3']=Decat_Field("@@bkl3", $edtVar);
			$row['VDBKL4']=Decat_Field("@@bkl4", $edtVar);
			$row['VDBKL5']=Decat_Field("@@bkl5", $edtVar);
			$row['VDBKL6']=Decat_Field("@@bkl6", $edtVar);
			$row['VDBKL7']=Decat_Field("@@bkl7", $edtVar);
			$row['VDBKL8']=Decat_Field("@@bkl8", $edtVar);
			if ($usePercent == "Y")  {
				$row['VDBKC1']=Decat_Field("@@bkc1", $edtVar);
				$row['VDBKC2']=Decat_Field("@@bkc2", $edtVar);
				$row['VDBKC3']=Decat_Field("@@bkc3", $edtVar);
				$row['VDBKC4']=Decat_Field("@@bkc4", $edtVar);
				$row['VDBKC5']=Decat_Field("@@bkc5", $edtVar);
				$row['VDBKC6']=Decat_Field("@@bkc6", $edtVar);
				$row['VDBKC7']=Decat_Field("@@bkc7", $edtVar);
				$row['VDBKC8']=Decat_Field("@@bkc8", $edtVar);
			}  else  {
				$row['VDBKP1']=Decat_Field("@@bkp1", $edtVar);
				$row['VDBKP2']=Decat_Field("@@bkp2", $edtVar);
				$row['VDBKP3']=Decat_Field("@@bkp3", $edtVar);
				$row['VDBKP4']=Decat_Field("@@bkp4", $edtVar);
				$row['VDBKP5']=Decat_Field("@@bkp5", $edtVar);
				$row['VDBKP6']=Decat_Field("@@bkp6", $edtVar);
				$row['VDBKP7']=Decat_Field("@@bkp7", $edtVar);
				$row['VDBKP8']=Decat_Field("@@bkp8", $edtVar);
			}
		}

		if ($errFound == "" && $maintenanceCode == "A") {
			$focusField = $curNameHld ;
			$F_VDSTDT=Format_Date($row['VDSTDT'], "D");
			$row['VDSTDT']=DateInputFromCYMD($row['VDSTDT']);
			$F_VDEXDT=Format_Date($row['VDEXDT'], "D");
			$row['VDEXDT']=DateInputFromCYMD($row['VDEXDT']);
		}

	} elseif ($maintenanceCode == "Z") {
		$focusField = $curNameHld ;
		$F_VDSTDT=Format_Date($row['VDSTDT'], "D");
		$row['VDSTDT']=DateInputFromCYMD($row['VDSTDT']);
		$F_VDEXDT=Format_Date($row['VDEXDT'], "D");
		$row['VDEXDT']=DateInputFromCYMD($row['VDEXDT']);
		$row['VDBKC1'] = Format_Nbr(($row['VDBKC1'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC2'] = Format_Nbr(($row['VDBKC2'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC3'] = Format_Nbr(($row['VDBKC3'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC4'] = Format_Nbr(($row['VDBKC4'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC5'] = Format_Nbr(($row['VDBKC5'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC6'] = Format_Nbr(($row['VDBKC6'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC7'] = Format_Nbr(($row['VDBKC7'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC8'] = Format_Nbr(($row['VDBKC8'] * 100), "6", "4", "Y", "", "");
	} else   {
		$focusField= $chgCurName;
		if ($contract == "Y")       {$focusField = "contractExpirationDate";}
		elseif ($bracketAmt == "Y") {$focusField = "bracketLimit1";}
		elseif ($usePercent == "Y") {$focusField = "bracketPercentage1";}
		else                        {$focusField = "dolAmount";}

		$row['VDBKC1'] = Format_Nbr(($row['VDBKC1'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC2'] = Format_Nbr(($row['VDBKC2'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC3'] = Format_Nbr(($row['VDBKC3'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC4'] = Format_Nbr(($row['VDBKC4'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC5'] = Format_Nbr(($row['VDBKC5'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC6'] = Format_Nbr(($row['VDBKC6'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC7'] = Format_Nbr(($row['VDBKC7'] * 100), "6", "4", "Y", "", "");
		$row['VDBKC8'] = Format_Nbr(($row['VDBKC8'] * 100), "6", "4", "Y", "", "");
		$F_VDSTDT=Format_Date($row['VDSTDT'], "D");
		$row['VDSTDT']=DateInputFromCYMD($row['VDSTDT']);
		$F_VDEXDT=Format_Date($row['VDEXDT'], "D");
		$row['VDEXDT']=DateInputFromCYMD($row['VDEXDT']);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	if ($formatToPrint == ""){
		print "\n <table $contentTable>";
		if ($Err_General != "")  {
			print "\n  <tr><td>&nbsp;</td><td class=\"error\" colspan=\"10\">$Err_General</td></tr>";
		}

		$srchName = "";
		foreach ($mdCol as $mdFld)  {
			$curHeading = trim($mdFld['PWCHDG']);
			$curfType   = trim($mdFld['PWFTYP']);
			$curdType   = trim($mdFld['PWDTYP']);
			$curName    = trim($mdFld['PWCOLN']);
			$curSize    = trim($mdFld['PWFLEN']);
			$curDecm    = trim($mdFld['PWFDEC']);
			$curDPos    = trim($mdFld['PWDPOS']);
			$curDKey    = trim($mdFld['PWDKEY']);
			$curDTbl    = trim($mdFld['PWDTBL']);
			$curDCol    = trim($mdFld['PWDCOL']);
			$curData    = "";
			if ($maintenanceCode != "Z") {$curData = $row[$curName];}
			$chgCurName = "chg$curName";
			if ($errFound != "") {
				$curShort = "@@";
				$curShort .= strtolower(substr($curName, 2));
				$curShort = str_pad($curShort,6,"@");
				$fieldValue = DecatErr_Field ($curShort, $chgCurName);
				$Err_Message = $fieldValue;
				$fieldValue = Decat_Field($curShort, "") ;
			}  else  {
				$Err_Message = "";
				$fieldValue = $curData;
			}

			$curNameDesc = "";
			if ($curdType =="NUMERIC" || $curdType =="DECIMAL") {
				$cssClass = "inputnmbr";
				if ($errFound == "" && ($maintenanceCode=="A" || $maintenanceCode=="Z")) {$curData="";}
				$curNameDesc = RetValue("$curDKey = $curData ", "$curDTbl", "$curDCol");
			}  else  {
				$cssClass = "inputalph";
				$curNameDesc = RetValue("$curDKey ='$curData' ", "$curDTbl", "$curDCol");
			}

			$textOvr=SetTextOvr($Err_Message);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>$curHeading</span></td>";
			if ($maintenanceCode == "A" || $maintenanceCode == "Z") {
				print "\n  <td class=\"$cssClass\"><input type=\"text\"  name=\"$chgCurName\" value=\"" . rtrim($curData) . "\" size=\"22\" maxlength=\"$curSize\">";
				if ($chgCurName == "chgVDWHS") {
					print "\n  <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=$chgCurName&amp;fldDesc={$chgCurName}Desc\" onclick=\"$searchWinVar\">$reqFieldChar  $searchImage   </a><span class=\"dspdesc\" id=\"{$chgCurName}Desc\">$fieldDesc</span></td>";
				}
				if ($chgCurName == "chgVDVEND") {
					print "\n  <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=$chgCurName&amp;fldDesc={$chgCurName}Desc\" onclick=\"$searchWinVar\">$reqFieldChar  $searchImage   </a><span class=\"dspdesc\" id=\"{$chgCurName}Desc\">$fieldDesc</span></td>";
				}
				if ($chgCurName == "chgVDITEM") {
					print "\n  <a href=\"{$homeURL}{$phpPath}ItemSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=$chgCurName&amp;fldDesc={$chgCurName}Desc\" onclick=\"$searchWinVar\">$reqFieldChar  $searchImage   </a><span class=\"dspdesc\" id=\"{$chgCurName}Desc\">$fieldDesc</span></td>";
				}
				if ($curNameDesc == "")  {$curNameDesc = $fldDesc;}
			} else {
				$F_curData=Format_Code($curData);
				print "\n <td class=\"$cssClass\"><input type=\"hidden\" name=\"$chgCurName\" value=\"" . rtrim($curData) . "\" size=\"35\" maxlength=\"35\">$curNameDesc &nbsp; $F_curData</td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_Message);
		}

		if ($contract == "Y") {
			$textOvr=SetTextOvr($Err_VDSTDT);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Contract Start Date </span></td>";
			if ($maintenanceCode == "A" || $maintenanceCode == "Z") {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"contractStartDate\" value=\"" . rtrim($row['VDSTDT']) . "\"  size=\"7\" maxlength=\"6\">";
				print "\n  <a href=\"javascript:calWindow('contractStartDate');\">$reqFieldChar $calendarImage</a></td>";
			} else {
				print "\n  <td class=\"dspnmbr\"><input type=\"hidden\"   name=\"contractStartDate\" value=\"" . rtrim($row['VDSTDT']) . "\">$F_VDSTDT</td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDSTDT);

			$textOvr=SetTextOvr($Err_VDEXDT);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Contract Expiration Date </span></td>";
			if ($maintenanceCode == "A" || $maintenanceCode == "C" || $maintenanceCode == "Z") {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"contractExpirationDate\" value=\"" . rtrim($row['VDEXDT']) . "\"  size=\"7\" maxlength=\6\">";
				print "\n     <a href=\"javascript:calWindow('contractExpirationDate');\">$reqFieldChar $calendarImage</a></td>";
			} else {
				print "\n  <td class=\"dspnmbr\"><input type=\"hidden\"  name=\"contractExpirationDate\" value=\"" . rtrim($row['VDEXDT']) . "\">$F_VDEXDT</td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDEXDT);
		}

		$textOvr=SetTextOvr($Err_VDQOTE);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Quote Number </span></td>";
		if ($maintenanceCode != "D") {
			print "\n  <td class=\"inputalph\"><input type=\"text\"  name=\"quote\" value=\"" . rtrim($row['VDQOTE']) . "\" size=\"22\" maxlength=\"22\"></td>";
		} else {
			print "\n  <td class=\"dspalph\"><input type=\"hidden\" name=\"quote\" value=\"" . rtrim($row['VDQOTE']) . "\">$VDQOTE";
			print "\n     <a href=\"javascript:check(document.Chg)\"></a></td>";
		}
		print "\n </tr>";
		DspErrMsg($Err_VDQOTE);


		if ($bracketAmt == "Y" && $usePercent != "Y") {$dollarAmt = "Y";}

		if ($dollarAmt == "Y" && $bracketAmt != "Y" && $usePercent != "Y") {
			$textOvr=SetTextOvr($Err_VDAMNT);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Dollar Amount</span></td>";
		} elseif  ($bracketAmt == "Y" && $usePercent == "Y") {
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Dollar Amount</span></td>";
		}
		if (($dollarAmt == "Y" && $bracketAmt != "Y" && $usePercent != "Y") || ($bracketAmt == "Y" && $usePercent == "Y")) {

			if ($maintenanceCode == "A" || $maintenanceCode == "C" || $maintenanceCode == "Z") {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"dolAmount\" value=\"" . rtrim($row['VDAMNT']) . "\" size=\"22\" maxlength=\"14\">";
				print "\n     <a href=\"javascript:check(document.Chg)\">$reqFieldChar</a></td>";
			} else {
				print "\n  <td class=\"dspnmbr\"><input type=\"hidden\"  name=\"dolAmount\" value=\"" . rtrim($row['VDAMNT']) . "\">$VDAMNT";
				print "\n     <a href=\"javascript:check(document.Chg)\">$reqFieldChar</a></td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDAMNT);
		}
		print "\n <tr><td>&nbsp;</td></tr>";

		print "\n <tr>";
		print "\n <td>&nbsp;</td>";

		print "\n <td><table $contentTable><tr>";

		if ($bracketAmt == "Y")  {
			print "\n   <th class=\"colhdr\" colspan=\"1\">Bracket Quantity<br>Limit</th>";
			if ($dollarAmt == "Y")  {
				print "\n   <th class=\"colhdr\" colspan=\"1\">Bracket<br>Dollar Amount</th>";
			} elseif ($dollarAmt != "Y" && $usePercent == "Y")  {
				print "\n   <th class=\"colhdr\" colspan=\"1\">Bracket Dollar Amount<br>Less Percentage</th>";
			} elseif ($dollarAmt != "Y" && $usePercent != "Y")  {
				print "\n   <th class=\"colhdr\" colspan=\"1\">Bracket Dollar Amount<br>Less Amount</th>";
			}
		}
		print "\n </tr>";

		if ($bracketAmt == "Y")  {
			print "\n  <tr><td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketLimit1\" value=\"" . rtrim($row['VDBKL1']) . "\" size=\"20\" maxlength=\"14\"></td>";
			if ($usePercent == "Y")  {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketPercentage1\" value=\"" . rtrim($row['VDBKC1']) . "\" size=\"20\" maxlength=\"8\"></td>";
			}   else  {print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketAmount1\" value=\"" . rtrim($row['VDBKP1']) . "\" size=\"20\" maxlength=\"14\"></td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDBKL1);
			DspErrMsg($Err_VDBKC1);
			DspErrMsg($Err_VDBKP1);

			print "\n  <tr><td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketLimit2\" value=\"" . rtrim($row['VDBKL2']) . "\" size=\"20\" maxlength=\"14\"></td>";
			if ($usePercent == "Y")  {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketPercentage2\" value=\"" . rtrim($row['VDBKC2']) . "\" size=\"20\" maxlength=\"8\"></td>";
			}   else  {print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketAmount2\" value=\"" . rtrim($row['VDBKP2']) . "\" size=\"20\" maxlength=\"14\"></td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDBKL2);
			DspErrMsg($Err_VDBKC2);
			DspErrMsg($Err_VDBKP2);

			print "\n  <tr><td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketLimit3\" value=\"" . rtrim($row['VDBKL3']) . "\" size=\"20\" maxlength=\"14\"></td>";
			if ($usePercent == "Y")  {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketPercentage3\" value=\"" . rtrim($row['VDBKC3']) . "\" size=\"20\" maxlength=\"8\"></td>";
			}   else  {print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketAmount3\" value=\"" . rtrim($row['VDBKP3']) . "\" size=\"20\" maxlength=\"14\"></td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDBKL3);
			DspErrMsg($Err_VDBKC3);
			DspErrMsg($Err_VDBKP3);

			print "\n  <tr><td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketLimit4\" value=\"" . rtrim($row['VDBKL4']) . "\" size=\"20\" maxlength=\"14\"></td>";
			if ($usePercent == "Y")  {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketPercentage4\" value=\"" . rtrim($row['VDBKC4']) . "\" size=\"20\" maxlength=\"8\"></td>";
			}   else  {print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketAmount4\" value=\"" . rtrim($row['VDBKP4']) . "\" size=\"20\" maxlength=\"14\"></td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDBKL4);
			DspErrMsg($Err_VDBKC4);
			DspErrMsg($Err_VDBKP4);

			print "\n  <tr><td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketLimit5\" value=\"" . rtrim($row['VDBKL5']) . "\" size=\"20\" maxlength=\"14\"></td>";
			if ($usePercent == "Y")  {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketPercentage5\" value=\"" . rtrim($row['VDBKC5']) . "\" size=\"20\" maxlength=\"8\"></td>";
			}   else  {print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketAmount5\" value=\"" . rtrim($row['VDBKP5']) . "\" size=\"20\" maxlength=\"14\"></td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDBKL5);
			DspErrMsg($Err_VDBKC5);
			DspErrMsg($Err_VDBKP5);

			print "\n  <tr><td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketLimit6\" value=\"" . rtrim($row['VDBKL6']) . "\" size=\"20\" maxlength=\"14\"></td>";
			if ($usePercent == "Y")  {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketPercentage6\" value=\"" . rtrim($row['VDBKC6']) . "\" size=\"20\" maxlength=\"8\"></td>";
			}   else  {print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketAmount6\" value=\"" . rtrim($row['VDBKP6']) . "\" size=\"20\" maxlength=\"14\"></td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDBKL6);
			DspErrMsg($Err_VDBKC6);
			DspErrMsg($Err_VDBKP6);

			print "\n  <tr><td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketLimit7\" value=\"" . rtrim($row['VDBKL7']) . "\" size=\"20\" maxlength=\"14\"></td>";
			if ($usePercent == "Y")  {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketPercentage7\" value=\"" . rtrim($row['VDBKC7']) . "\" size=\"20\" maxlength=\"8\"></td>";
			}   else  {print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketAmount7\" value=\"" . rtrim($row['VDBKP7']) . "\" size=\"20\" maxlength=\"14\"></td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDBKL7);
			DspErrMsg($Err_VDBKC7);
			DspErrMsg($Err_VDBKP7);

			print "\n  <tr><td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketLimit8\" value=\"" . rtrim($row['VDBKL8']) . "\" size=\"20\" maxlength=\"14\"></td>";
			if ($usePercent == "Y")  {
				print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketPercentage8\" value=\"" . rtrim($row['VDBKC8']) . "\" size=\"20\" maxlength=\"8\"></td>";
			}   else  {print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"bracketAmount8\" value=\"" . rtrim($row['VDBKP8']) . "\" size=\"20\" maxlength=\"14\"></td>";
			}
			print "\n </tr>";
			DspErrMsg($Err_VDBKL8);
			DspErrMsg($Err_VDBKC8);
			DspErrMsg($Err_VDBKP8);
		}

		print "\n </table>";
		print "\n </td></tr>";
		print "\n </table>";
		print "\n <script TYPE=\"text/javascript\">document.Chg.$focusField.focus();</script>";
	}
	print "\n </form>";

	require_once 'MaintainBottom.php';
	print $hrTagAttr ;
	require_once 'Copyright.php';
	print "\n </td></tr>";
	print "\n </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
}

if ($tag == "Edit_Data") {
	if ($maintenanceCode=="D" ) {
		$_POST['pricingLevel']      =$pricingLevel;
		$_POST['pricingKey']        =$pricingKey;
		$_POST['contractStartDate'] = $_GET['contractStartDate'];
		$_POST['contractStartDate']=DateInputFromCYMD($_POST['contractStartDate']);
	}

	if ($maintenanceCode == "Z") {$maintenanceCode = "A";}

	if (is_null($_POST['pricingLevel'])) {$_POST['pricingLevel'] = $pricingLevel;}
	if (is_null($_POST['pricingKey']))   {$_POST['pricingKey'] = $pricingKey;}
	if ($contract == "Y")  {
		if (is_null($_POST['contractStartDate'])) {$_POST['contractStartDate'] = $contractStartDate;}
		if (is_null($_POST['contractExpirationDate'])) {$_POST['contractExpirationDate'] = $contractExpirationDate;}
	}

	$edtVar= "";
	strtoupper($pricingKey);
	Concat_Field("@@pmlv", $_POST['pricingLevel']);
	if ($maintenanceCode == "C" || $maintenanceCode == "D")  {
		Concat_Field("@@pmky", $pricingKey);
	}  else  {
		Concat_Field("@@pmky", "");
	}
	$i = 1;
	foreach ($mdCol as $mdFld)  {
		$curName 	= trim($mdFld['PWCOLN']);
		$curdType  	= trim($mdFld['PWDTYP']);
		$curSize		= trim($mdFld['PWFLEN']);
		$chgCurName = "chg$curName";
		Concat_Field("@@nam$i", $curName);
		Concat_Field("@@len$i", $curSize);
		$curShort = "@@";
		$curShort .= strtolower(substr($curName, 2));
		$curShort = str_pad($curShort,6,"@");

		if ($curdType == "NUMERIC" || $curdType == "DECIMAL")  {
			$curData = $_POST[$chgCurName] ;
			$curShort .= $curData;
		}  else  {
			$curData = $_POST[$chgCurName] ;
			$curShort .= strtoupper($curData);
		}

		Concat_Field($curShort, "");
		$i = $i + 1;
	}
	if ($contract == "Y")  {
		Concat_Field("@@stdt", $_POST['contractStartDate']);
		Concat_Field("@@exdt", $_POST['contractExpirationDate']);
	}
	Concat_Field("@@qote", $_POST['quote']);
	Concat_Field("@@amnt", $_POST['dolAmount']);
	Concat_Field("@@bkl1", $_POST['bracketLimit1']);
	Concat_Field("@@bkl2", $_POST['bracketLimit2']);
	Concat_Field("@@bkl3", $_POST['bracketLimit3']);
	Concat_Field("@@bkl4", $_POST['bracketLimit4']);
	Concat_Field("@@bkl5", $_POST['bracketLimit5']);
	Concat_Field("@@bkl6", $_POST['bracketLimit6']);
	Concat_Field("@@bkl7", $_POST['bracketLimit7']);
	Concat_Field("@@bkl8", $_POST['bracketLimit8']);
	Concat_Field("@@bkc1", $_POST['bracketPercentage1']);
	Concat_Field("@@bkc2", $_POST['bracketPercentage2']);
	Concat_Field("@@bkc3", $_POST['bracketPercentage3']);
	Concat_Field("@@bkc4", $_POST['bracketPercentage4']);
	Concat_Field("@@bkc5", $_POST['bracketPercentage5']);
	Concat_Field("@@bkc6", $_POST['bracketPercentage6']);
	Concat_Field("@@bkc7", $_POST['bracketPercentage7']);
	Concat_Field("@@bkc8", $_POST['bracketPercentage8']);
	Concat_Field("@@bkp1", $_POST['bracketAmount1']);
	Concat_Field("@@bkp2", $_POST['bracketAmount2']);
	Concat_Field("@@bkp3", $_POST['bracketAmount3']);
	Concat_Field("@@bkp4", $_POST['bracketAmount4']);
	Concat_Field("@@bkp5", $_POST['bracketAmount5']);
	Concat_Field("@@bkp6", $_POST['bracketAmount6']);
	Concat_Field("@@bkp7", $_POST['bracketAmount7']);
	Concat_Field("@@bkp8", $_POST['bracketAmount8']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPOPRM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	$F_VDEXDT=Format_Date(DateToCYMD($_POST['contractExpirationDate']), "D");
	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, $levelDesc, $pricingLevel, "" , "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}VendorPricingDetail.php{$scriptVarBase}&amp;tblID=7&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif ($maintenanceCode == "D") {
		DecatErr_Field("@@pmlv", "pricingLevel");
		$confMessage=Format_ConfMsg_Desc("", $levelDesc, $pricingLevel, "<br>$fieldValue", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}VendorPricingDetail.php{$scriptVarBase}&amp;tblID=7&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;contractStartDate=" . urlencode(trim($_POST['contractStartDate'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

function Rtv_Pricing_Categories($profileHandle, $pricingLevel) {
	global $i5Connect;
	$stmtSQL = " Select * From HDPRCW Where PWXHND='$profileHandle' and PWPMLV=$pricingLevel Order By PWDPOS ";
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	while ($row = db2_fetch_assoc($sqlResult)){$mdCol[] = $row;}
	return $mdCol;
}

?>