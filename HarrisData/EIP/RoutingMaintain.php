<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET ['maintenanceCode'];
$errMsg = $_GET ['errMsg'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$maintDesc = 'Add';
if ($maintenanceCode == 'C') {
	$maintDesc = 'Change';
} elseif ($maintenanceCode == 'D') {
	$maintDesc = 'Delete';
}
$page_title = "Routing Mass " . $maintDesc;
$scriptName = "RoutingMaintain.php";
$scriptVarBase = $genericVarBase;
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName = "HPD020";

$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=493";

// ** Rewritten for Mass Maintain
//require_once 'ProgSecurityTestInclude.php';
$program_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
if (($program_OPT['sec_06']=="N" && $maintenanceCode=="A") || ($program_OPT['sec_07']=="N" && $maintenanceCode=="C") || ($program_OPT['sec_08']=="N" && $maintenanceCode=="D")) {
    require_once 'ProgSecurityError.php';
    exit ();
}
// ** End Rewrite of ProgSecurityTestInclude.php

$selectedSQL = $_SESSION [$eid];
$pos = strpos ( strtoupper ( $selectedSQL ), 'FETCH FIRST' );
if ($pos > 0) {
	$selectedSQL = substr ( $selectedSQL, 0, $pos - 1 );
}

$routingResult = db2_exec ( $i5Connect->getConnection (), $selectedSQL, array ('cursor' => DB2_SCROLLABLE ) );

$rows = array ();
while ( $row = db2_fetch_assoc ( $routingResult, $startRow ) ) {
	$startRow ++;
	$rows [] = $row;
}

if ($tag == "Edit_Data") {
	$errMsg = NULL;
	if ($maintenanceCode == "Z") {
		$maintenanceCode = "A";
	}
	
	if ($maintenanceCode == "A") {
		$rowCount = 0;
		foreach ( $rows as $row ) {
			$rowCount ++;
			
			$existingRow = RetValue ( "RTPLT=$row[RTPLT] and RTPN='$row[RTPN]' and RTSEQN=$_POST[sequenceNumber]", "HDMRTM", "count(*)" );
			if ($existingRow > 0) {
				$errMsg = 'Routing Sequence already exists in one or more of the Selected Routings';
				break;
			}
		}
		
		if ($rowCount == 0 && ! $errMsg) {
			$errMsg = 'No Selected Routings';
		}
	}
	
	if (is_null ( $errMsg )) {
		$updCnt = 0;
		$errCnt = 0;
		foreach ( $rows as $row ) {
			$edtVar = "";
			Concat_Field ( "@@plt@", $row['RTPLT'] );
			Concat_Field ( "@@pn@@", trim ($row['RTPN'] ) );
			if ($maintenanceCode == "A") {
				Concat_Field ( "@@seqn", $_POST ['sequenceNumber'] );
			} else {
				Concat_Field ( "@@seqn", $row ['RTSEQN'] );
			}
			
			if ($maintenanceCode != 'D') {
				if ($maintenanceCode == 'C') {
					if (trim ( $_POST ['department'] ) != '') {
						Concat_Field ( "@@dept", strtoupper ( $_POST ['department'] ) );
						Concat_Field ( "@@wc@@", strtoupper ( $_POST ['workcenter'] ) );
					}
					if (trim ( $_POST ['operationNumber'] ) != '') {
						Concat_Field ( "@@son@", strtoupper ( $_POST ['operationNumber'] ) );
					}
					if (trim ( $_POST ['routingtype'] ) != '') {
						Concat_Field ( "@@rtyp", strtoupper ( $_POST ['routingtype'] ) );
					}
					if (trim ( $_POST ['alternateoperation'] ) != '') {
						Concat_Field ( "@@altc", strtoupper ( $_POST ['alternateoperation'] ) );
					}
					if ($_POST ['piecesperhour'] > 0 || trim ( $_POST ['measuredhours'] ) != '' || trim ( $_POST ['measuredhoursref'] ) != '') {
						Concat_Field ( "@@pphr", $_POST ['piecesperhour'] );
						Concat_Field ( "@@mhrs", $_POST ['measuredhours'] );
						Concat_Field ( "@@mhrc", strtoupper ( $_POST ['measuredhoursref'] ) );
					}
					if (trim ( $_POST ['crewsize'] ) != '') {
						Concat_Field ( "@@mpon", $_POST ['crewsize'] );
					}
					if (trim ( $_POST ['setuphours'] ) != '') {
						Concat_Field ( "@@suhr", $_POST ['setuphours'] );
					}
					if (trim ( $_POST ['setuphoursref'] ) != '') {
						Concat_Field ( "@@surc", strtoupper ( $_POST ['setuphoursref'] ) );
					}
					if (trim ( $_POST ['perunitrate'] ) != '') {
						Concat_Field ( "@@psuc", $_POST ['perunitrate'] );
					}
					if (trim ( $_POST ['transitdays'] ) != '') {
						Concat_Field ( "@@mtd@", $_POST ['transitdays'] );
					}
					if (trim ( $_POST ['laborgrade'] ) != '') {
						Concat_Field ( "@@lgrd", strtoupper ( $_POST ['laborgrade'] ) );
					}
					if (trim ( $_POST ['machinenumber'] ) != '') {
						Concat_Field ( "@@mach", strtoupper ( $_POST ['machinenumber'] ) );
					}
					if ($_POST ['machhoursonly'] == ' ' || strtoupper ( $_POST ['machhoursonly'] ) == 'Y') {
						Concat_Field ( "@@maho", strtoupper ( $_POST ['machhoursonly'] ) );
					}
                    if (trim ( $_POST ['ecnnumber'] ) != '') {
                        Concat_Field ( "@@ecn@", strtoupper ( $_POST ['ecnnumber'] ) );
                    }
                } else {
					Concat_Field ( "@@dept", strtoupper ( $_POST ['department'] ) );
					Concat_Field ( "@@wc@@", strtoupper ( $_POST ['workcenter'] ) );
					Concat_Field ( "@@son@", strtoupper ( $_POST ['operationNumber'] ) );
					Concat_Field ( "@@rtyp", strtoupper ( $_POST ['routingtype'] ) );
					Concat_Field ( "@@altc", strtoupper ( $_POST ['alternateoperation'] ) );
					Concat_Field ( "@@pphr", $_POST ['piecesperhour'] );
					Concat_Field ( "@@mhrs", $_POST ['measuredhours'] );
					Concat_Field ( "@@mhrc", strtoupper ( $_POST ['measuredhoursref'] ) );
					Concat_Field ( "@@mpon", $_POST ['crewsize'] );
					Concat_Field ( "@@suhr", $_POST ['setuphours'] );
					Concat_Field ( "@@surc", strtoupper ( $_POST ['setuphoursref'] ) );
					Concat_Field ( "@@psuc", $_POST ['perunitrate'] );
					Concat_Field ( "@@mtd@", $_POST ['transitdays'] );
					Concat_Field ( "@@lgrd", strtoupper ( $_POST ['laborgrade'] ) );
					Concat_Field ( "@@mach", strtoupper ( $_POST ['machinenumber'] ) );
					Concat_Field ( "@@maho", $_POST ['machhoursonly'] );
                    Concat_Field ( "@@ecn@", $_POST ['ecnnumber'] );
				}
			}
			$edtVar .= "}{";
			
			$returnValue = Routing_Maintain_Edit ( $maintenanceCode, $edtVar, $_POST ['piecesperhour'], $_POST ['measuredhours'], strtoupper ( $_POST ['measuredhoursref'] ) );
			$errFound = $returnValue ['errFound'];
			$errVar = $returnValue ['errVar'];
			
			if ($updCnt == 0 && $errFound == "Y" && $maintenanceCode != "D") {
				$_POST ['measuredhoursref'] = $returnValue ['mhrc'];
				$_POST ['measuredhours'] = $returnValue ['mhrs'];
				$_POST ['piecesperhour'] = $returnValue ['pphrs'];
				
				$errMsg = 'Please correct all errors';
				break;
			} elseif ($errFound == "Y") {
				$errCnt ++;
			} else {
				$updCnt ++;
			}
			
			// print_r ( $row );
			// echo "<p> edtVar is:", $edtVar, "</p><p> errFound is:", $errFound, "</p><p> errVar is:", $errVar, "</p><p> errCnt is:", $errCnt, "</p><p> updCnt is:", $updCnt, "</p>";
		}
		// exit ( 0 );
	}
	if (is_null ( $errMsg )) {
		if ($maintenanceCode == "A") {
			$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $updCnt . " rows for Rtg Seq " . $_POST ['sequenceNumber'], "", "", "", "" );
		} elseif ($maintenanceCode == "C") {
			$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $updCnt . " rows. ", "", "", "", "" );
		} elseif ($maintenanceCode == "D") {
			$errorMsg = ($errCnt > 0) ? $errCnt . ' rows could not be deleted' : '';
			$confMessage = Format_ConfMsg_Desc ( $maintenanceCode, $updCnt . " rows. ", "", $errorMsg, "", "" );
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
	}
}

if (is_null ( $tag ) || $tag == "MAINTAIN" || $errMsg) {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'CheckEnterChg.php';
	require_once 'NumEdit.php';
	
	print "\n function validate(chgForm) {";
	if ($maintenanceCode == "A") {
		print "\n if (document.Chg.sequenceNumber.value ==\"\" || ";
		print "\n     document.Chg.department.value ==\"\" || ";
		print "\n     document.Chg.workcenter.value ==\"\" ";
		print "\n ) {alert(\"$reqFieldError\"); return false;} ";
	}
	if ($maintenanceCode != "D") {
        if ($requireEcnValueOnMassUpdate =="Y") {
            print "\n if (document.Chg.ecnnumber.value ==\"\" ";
            print "\n ) {alert(\"$reqFieldError\"); return false;} ";
        }
        print "\n if (editNum(document.Chg.sequenceNumber, 3, 0)) ";
        print "\n if (confirmUpdate()) ";
	}
	print "\n  return true; }";

    print "\n function confirmUpdate(text) {return confirm(\"Are you sure you wish to update?\")}";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";
	
	require_once ($genericHead);
	print "\n </head>";
	
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ROUTINGMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = "";
	require_once 'AddRecordSQL.php';
	require 'stmtSQLEnd.php';

	print "\n <table $contentTable>";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>$page_title</h1></td>";
	print "\n     <td class=\"toolbar\">";
	if (($program_OPT['sec_06'] != "N" && $maintenanceCode == "A") || ($program_OPT['sec_07'] != "N" && $maintenanceCode == "C")) {
		print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
	}
	if ($wfInstance > "0") {
		print "\n <a onClick=\"return confirmCancelWF()\" href=\"$cancelWFURL\">$cancelImageMed</a>";
	} elseif ($backURL != "") {
		print "\n <a href=\"$backURL\">$cancelImageMed</a>";
	} else {
		print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";
	}
	
	if ($program_OPT['sec_08'] != "N" && $maintenanceCode == "D") {
		print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";
	}
	
	$medIcon = "Y";
	require 'HelpPage.php';
	print "\n </td></tr></table>";
	
	if ($maintenanceCode != "D") {
		print $hrTagAttr;
		require_once 'RequiredField.php';
	}
	
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc ( $sqlResult );
	
	if ($errMsg) {
		$row['RTSEQN'] = $_POST ['sequenceNumber'];
		$row['RTDEPT'] = strtoupper ( $_POST ['department'] );
		$row['RTWC'] = strtoupper ( $_POST ['workcenter'] );
		$row['RTOPER'] = strtoupper ( $_POST ['operationNumber'] );
		$row['RTRTYP'] = strtoupper ( $_POST ['routingtype'] );
		$row['RTALTC'] = strtoupper ( $_POST ['alternateoperation'] );
		$row['PPHR'] = $_POST ['piecesperhour'];
		$row['RTMHRS'] = $_POST ['measuredhours'];
		$row['RTMHRC'] = strtoupper ( $_POST ['measuredhoursref'] );
		$row['RTMPON'] = $_POST ['crewsize'];
		$row['RTSUHR'] = $_POST ['setuphours'];
		$row['RTSURC'] = strtoupper ( $_POST ['setuphoursref'] );
		$row['RTPSUC'] = $_POST ['perunitrate'];
		$row['RTMTD'] = $_POST ['transitdays'];
		$row['RTLGRD'] = strtoupper ( $_POST ['laborgrade'] );
		$row['RTMACH'] = strtoupper ( $_POST ['machinenumber'] );
		$row['RTMAHO'] = $_POST ['machhoursonly'];
                $row['RTECN'] = $_POST ['ecnnumber'];
		
		$Err_RTSEQN = DecatErr_Field ( "@@seqn", "sequenceNumber" );
		$Err_RTDEPT = DecatErr_Field ( "@@dept", "department" );
		$Err_RTWC = DecatErr_Field ( "@@wc@@", "workcenter" );
		$Err_RTOPER = DecatErr_Field ( "@@son@", "operationNumber" );
		$Err_RTRTYP = DecatErr_Field ( "@@rtyp", "routingtype" );
		$Err_RTALTC = DecatErr_Field ( "@@altc", "alternateoperation" );
		$Err_PPHRS = DecatErr_Field ( "@@pphr", "piecesperhour" );
		$Err_RTMHRS = DecatErr_Field ( "@@mhrs", "measuredhours" );
		$Err_RTMHRC = DecatErr_Field ( "@@mhrc", "measuredhoursref" );
		$Err_RTMPON = DecatErr_Field ( "@@mpon", "crewsize" );
		$Err_RTSUHR = DecatErr_Field ( "@@suhr", "setuphours" );
		$Err_RTSURC = DecatErr_Field ( "@@surc", "setuphoursref" );
		$Err_RTPSUC = DecatErr_Field ( "@@psuc", "perunitrate" );
		$Err_RTMTD = DecatErr_Field ( "@@mtd@", "transitdays" );
		$Err_RTLGRD = DecatErr_Field ( "@@lgrd", "laborgrade" );
		$Err_RTMACH = DecatErr_Field ( "@@mach", "machinenumber" );
		$Err_RTMAHO = DecatErr_Field ( "@@maho", "machhoursonly" );
        $Err_RTECN = DecatErr_Field ( "@@ecn@", "ecnnumber" );
		
		$focusField = "sequenceNumber";
	} else {
		$focusField = "sequenceNumber";
	}
	
	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode ( trim ( $maintenanceCode ) ) . "\">";
	print "\n <table $contentTable>";
	if ($errMsg != '') {
		print "\n <tr><td>&nbsp;</td><td class=\"error\">$errMsg</td></tr> ";
	}
	if ($maintenanceCode != "D") {
		if ($maintenanceCode == "A") {
			Build_Fld_Entry ( "Routing Sequence", "sequenceNumber", "inputnmbr", "", "RTSEQN", $row['RTSEQN'], $Err_RTSEQN, "3", "3", "Y", "", "" );
		} else {
			print "\n  <tr><td><input type=\"hidden\" name=\"sequenceNumber\" value=\"$row[RTSEQN]\"></td></tr>";
		}
		
		$req = ($maintenanceCode == "A") ? $reqFieldChar : '';
		$fieldDesc = RetValue ( "WCDEPT='$row[RTDEPT]' and WCWC='$row[RTWC]'", "HDMWCM", "WCDESC" );
		$textOvr = SetTextOvr ( $Err_WCDEPT );
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Department/Work Center</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"department\" value=\"" . rtrim ( $row ['RTDEPT'] ) . "\" size=\"5\" maxlength=\"5\"> / <input type=\"text\"   name=\"workcenter\" value=\"" . rtrim ( $row ['RTWC'] ) . "\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;fldPlant=plantNumber&amp;fldPltName=plantName&amp;docName=Chg&amp;flddept=department&amp;fldWC=workcenter&amp;fldDesc=WCDesc\" onclick=\"$searchWinVar\">$req $searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"WCDesc\">$fieldDesc</span></td>";
		print "\n     <td><input type=\"hidden\" name=\"plantNumber\" value=\"\"></td><td><input type=\"hidden\" name=\"plantName\" value=\"\"></td>";
		print "\n </tr> ";
		DspErrMsg ( $Err_WCDEPT );
		
		$fieldDesc = RetValue ( "SDSON='$row[RTOPER]'", "HDMSDM", "SDDESC" );
		$textOvr = SetTextOvr ( $Err_RTOPER );
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Operation Number</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"operationNumber\" value=\"" . rtrim ( $row ['RTOPER'] ) . "\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$cGIPath}OperationSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldoper=operationNumber&amp;fldDesc=SDDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
		print "\n     <input type=\"text\" name=\"SDDesc\" value=\"" . $fieldDesc . "\" disabled></td>";
		print "\n </tr> ";
		DspErrMsg ( $Err_RTOPER );
		
		Build_Fld_Entry ( "Routing Type", "routingtype", "inputalph", "RTGTYPE", "RTRTYP", $row['RTRTYP'], $Err_RTRTYP, "1", "1", "", "", "" );
		Build_Fld_Entry ( "Alternate Operation", "alternateoperation", "inputalph", "ALTOPER", "RTALTC", $row['RTALTC'], $Err_RTALTC, "1", "1", "", "", "" );
		$pphrs = round ( $pphrs, 4 );
		Build_Fld_Entry ( "Pieces Per Hour", "piecesperhour", "inputnmbr", "", "PPHRS", $row['PPHR'], $Err_PPHRS, "14", "14", "", "", "" );
		Build_Fld_Entry ( "Measured Hours", "measuredhours", "inputnmbr", "", "RTMHRS", $row['RTMHRS'], $Err_RTMHRS, "8", "8", "", "", "" );
		Build_Fld_Entry ( "Measured Hours Ref Code", "measuredhoursref", "inputalph", "RTMHRC", "RTMHRC", $row['RTMHRC'], $Err_RTMHRC, "1", "1", "", "", "" );
		Build_Fld_Entry ( "Standard Setup Hours", "setuphours", "inputnmbr", "", "RTSUHR", $row['RTSUHR'], $Err_RTSUHR, "8", "8", "", "", "" );
		Build_Fld_Entry ( "Setup Hours Ref Code", "setuphoursref", "inputalph", "RTSURC", "RTSURC", $row['RTSURC'], $Err_RTSURC, "1", "1", "", "", "" );
		Build_Fld_Entry ( "Crew Size", "crewsize", "inputnmbr", "", "RTMPON", $row['RTMPON'], $Err_RTMPON, "6", "6", "", "", "" );
		Build_Fld_Entry ( "Per Unit Rate", "perunitrate", "inputnmbr", "", "RTPSUC", $row['RTPSUC'], $Err_RTPSUC, "14", "14", "", "", "" );
		Build_Fld_Entry ( "Labor Grade", "laborgrade", "inputalph", "", "RTLGRD", $row['RTLGRD'], $Err_RTLGRD, "2", "2", "", "", "" );
		Build_Fld_Entry ( "Machine Number", "machinenumber", "inputalph", "", "RTMACH", $row['RTMACH'], $Err_RTMACH, "5", "5", "", "", "" );
		Build_Fld_Entry ( "Transit Days", "transitdays", "inputnmbr", "", "RTMTD", $row['RTMTD'], $Err_RTMTD, "5", "5", "", "", "" );
		
		$fieldDesc = RetValue( "FLTYPE='BY' and FLVALU='$row[RTMAHO]'", "SYFLAG", "FLDESC" );
		$textOvr = SetTextOvr ( $Err_RTMAHO );
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Machine Hours Only</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"machhoursonly\" value=\"" . $row ['RTMAHO'] . "\" size=\"1\" maxlength=\"1\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}}&amp;docName=Chg&amp;fldName=machhoursonly&amp;fldDesc=machhoursonlydesc&amp;flagType=BY&amp;flagSrchHdr=Machine Hours Only\" onclick=\"$searchWinVar\">$searchImage</a> ";
		print "\n     <span class=\"dspdesc\" id=\"machhoursonlydesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg ( $Err_RTMAHO );

        if ($requireEcnValueOnMassUpdate == "Y") {
            Build_Fld_Entry("ECN Number", "ecnnumber", "inputalph", "", "RTECN", $row['RTECN'], $Err_RTECN, "5", "5", "Y", "", "");
        } else {
            Build_Fld_Entry("ECN Number", "ecnnumber", "inputalph", "", "RTECN", $row['RTECN'], $Err_RTECN, "5", "5", "", "", "");
        }
		
		print "\n <script TYPE=\"text/javascript\">";
		print "\n document.Chg.$focusField.focus();";
		print "\n </script>";
	}
	print "\n </form>";

    if ($maintenanceCode != "D") {
        print "\n <table $contentTable>";
        print "\n <tr>";
        print "\n     <td class=\"toolbar\">";
        if (($program_OPT['sec_06'] != "N" && $maintenanceCode == "A") || ($program_OPT['sec_07'] != "N" && $maintenanceCode == "C")) {
            print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
        }
        if ($wfInstance > "0") {
            print "\n <a onClick=\"return confirmCancelWF()\" href=\"$cancelWFURL\">$cancelImageMed</a>";
        } elseif ($backURL != "") {
            print "\n <a href=\"$backURL\">$cancelImageMed</a>";
        } else {
            print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";
        }

        if ($program_OPT['sec_08'] != "N" && $maintenanceCode == "D") {
            print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";
        }

        $medIcon = "Y";
        require 'HelpPage.php';
        print "\n </td></tr></table>";
    }

	print $hrTagAttr;
	
	print "<table $contentTable>";
	print "\n <tr class=\"error\"><td colspan=\"10\">Selected Routings:</td></tr>";
	print "\n <tr><th class=\"colhdr\">Plant</th>";
	print "\n <th class=\"colhdr\">Item<br>Number</th>";
	print "\n <th class=\"colhdr\">Rtg<br>Seq</th>";
	print "\n <th class=\"colhdr\">Dept</th>";
	print "\n <th class=\"colhdr\">Work<br>Center</th>";
	print "\n <th class=\"colhdr\">Description</th>";
	print "\n <th class=\"colhdr\">Rtg<br>Type</th>";
	print "\n <th class=\"colhdr\">Alt<br>Oper</th>";
	print "\n <th class=\"colhdr\">Pieces<br>Per Hour</th>";
	print "\n <th class=\"colhdr\" colspan=\"2\">Measured<br>Hours</th>";
	print "\n <th class=\"colhdr\">Crew<br>Size</th>";
	print "\n <th class=\"colhdr\" colspan=\"2\">Setup<br>Hours</th>";
	print "\n <th class=\"colhdr\">Machine<br>Number</th>";
	print "\n <th class=\"colhdr\">Oper<br>Number</th>";
	print "\n <th class=\"colhdr\">Per Unit<br>Rate</th>";
	print "\n <th class=\"colhdr\">Labor<br>Grade</th>";
	print "\n <th class=\"colhdr\">Transit<br>Days</th>";
	print "\n <th class=\"colhdr\">Machine<br>Hours Only</th>";
    print "\n <th class=\"colhdr\">ECN<br>Number</th>";
	print "\n </tr>";
	
	$rowCount = 0;
	foreach ( $rows as $row ) {
		require 'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[RTPLT]</td>";
		print "\n     <td class=\"colalph\">$row[RTPN]</td> ";
		print "\n     <td class=\"colnmbr\">$row[RTSEQN]</td>";
		print "\n     <td class=\"colalph\">$row[RTDEPT]</td>";
		print "\n     <td class=\"colalph\">$row[RTWC]</td>";
		print "\n     <td class=\"colalph\">$row[WCDESC]</td>";
		print "\n     <td class=\"colcode\">$row[RTRTYP]</td>";
		print "\n     <td class=\"colcode\">$row[RTALTC]</td>";
		$F_PPHR = Format_Nbr($row['PPHR'], "5", $rteEditCode, "Y", "", "" );
		print "\n     <td class=\"colnmbr\">$F_PPHR</td>";
		$F_RTMHRS = Format_Nbr ($row['RTMHRS'], "4", $qtyEditCode, "Y", "", "" );
		print "\n     <td class=\"colnmbr\">$F_RTMHRS</td>";
		print "\n     <td class=\"colcode\">$row[RTMHRC]</td>";
		print "\n     <td class=\"colalph\">$row[RTMPON]</td>";
		$F_RTSUHR = Format_Nbr($row['RTSUHR'], $hrsNbrDec, $hrsEditCode, "Y", "", "" );
		print "\n     <td class=\"colnmbr\">$F_RTSUHR</td>";
		print "\n     <td class=\"colcode\">$row[RTSURC]</td>";
		print "\n     <td class=\"colalph\">$row[RTMACH]</td>";
		print "\n     <td class=\"colalph\">$row[RTSON]</td>";
		$F_RTPSUC = Format_Nbr($row['RTPSUC'], $rteNbrDec, $rteEditCode, "Y", "", "" );
		print "\n     <td class=\"colnmbr\">$F_RTPSUC</td>";
		print "\n     <td class=\"colcode\">$row[RTLGRD]</td>";
		print "\n     <td class=\"colnmbr\">$row[RTMTD]</td>";
		print "\n     <td class=\"colcode\">$row[RTMAHO]</td>";
        print "\n     <td class=\"colalph\">$row[RTECN]</td>";
		print "\n </tr>";
		$rowCount ++;
	}
	if ($rowCount == 0) {
		require 'NoRecordsFound.php';
	}
	print "</table>";
	
	print "$hrTagAttr";
	
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit ();
}

// Maintenance Edit
function Routing_Maintain_Edit($maintenanceCode, $edtVar, $pphrs, $mhrs, $mhrc) {
	global $profileHandle, $dataBaseID, $pgmLibrary, $i5Connect, $userProfile;
	$pgmName = 'HPD020_W';
	$errVar = "";
	$errFound = "";
	$subr = 'SRMASS';
	
	$pgmCall = array (array ("Name" => "profileHandle", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "64" ), array ("Name" => "dataBaseID", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "2" ), array ("Name" => "maintenanceCode", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1" ), array ("Name" => "errFound", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1" ), array ("Name" => "edtVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000" ), array ("Name" => "errVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000" ), array ("Name" => "mhrc", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1" ), array ("Name" => "mhrs", "IO" => I5_INOUT, "Type" => I5_TYPE_PACKED, "Length" => "7.4" ), array ("Name" => "pphrs", "IO" => I5_INOUT, "Type" => I5_TYPE_PACKED, "Length" => "13.5" ), array ("Name" => "subr", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "10" ) );
	
	$pgm = i5_program_prepare ( "$pgmName", $pgmCall );
	if (! $pgm) {
		die ( "<br>Validate_Data ($pgmName) prepare error. Error Number=" . i5_errno () . " msg=" . i5_errormsg () );
	}
	
	$parmIn = array ("profileHandle" => $profileHandle, "dataBaseID" => $dataBaseID, "maintenanceCode" => $maintenanceCode, "errFound" => $errFound, "edtVar" => $edtVar, "errVar" => $errVar, "mhrc" => $mhrc, "mhrs" => $mhrs, "pphrs" => $pphrs, "subr" => $subr );
	
	$parmOut = array ("errFound" => "errFound", "errVar" => "errVar", "mhrc" => "mhrc", "mhrs" => "mhrs", "pphrs" => "pphrs" );
	
	$ret = i5_program_call ( $pgm, $parmIn, $parmOut );
	if (function_exists ( 'i5_output' ))
		extract ( i5_output () );
	if (! $ret) {
		die ( "<br>Validate_Data ($pgmName) call errno=" . i5_errno () . " msg=" . i5_errormsg () );
	}
	
	$returnValue ['errFound'] = $errFound;
	$returnValue ['errVar'] = $errVar;
	$returnValue ['mhrc'] = $mhrc;
	$returnValue ['mhrs'] = $mhrs;
	$returnValue ['pphrs'] = $pphrs;
	return $returnValue;
}

?>