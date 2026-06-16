<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$fromComp           = $_GET['fromComp'];
$fromFacl           = $_GET['fromFacl'];
$fromEmpl           = $_GET['fromEmpl'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Employee ACH Maintenance";
$scriptName     = "EmplACHMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;fromComp=" . urlencode(trim($fromComp)) . "&amp;fromFacl=" . urlencode(trim($fromFacl)) . "&amp;fromEmpl=" . urlencode(trim($fromEmpl));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPRACM";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=25";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if (editNum(document.Chg.coNum, 2, 0) && ";
	print "\n     editNum(document.Chg.faclNum, 4, 0) && ";
	print "\n     editNum(document.Chg.emplNum, 5, 0) && ";
	print "\n     editNum(document.Chg.amt1, 7, 2) && ";
	print "\n     editNum(document.Chg.amt2, 7, 2) && ";
	print "\n     editNum(document.Chg.amt3, 7, 2) && ";
	print "\n     editNum(document.Chg.amt4, 7, 2) && ";
	print "\n     editNum(document.Chg.amt5, 7, 2) && ";
	print "\n     editNum(document.Chg.amt6, 7, 2) && ";
	print "\n     editNum(document.Chg.amt7, 7, 2) && ";
	print "\n     editNum(document.Chg.pct1, 2, 1) && ";
	print "\n     editNum(document.Chg.pct2, 2, 1) && ";
	print "\n     editNum(document.Chg.pct3, 2, 1) && ";
	print "\n     editNum(document.Chg.pct4, 2, 1) && ";
	print "\n     editNum(document.Chg.pct5, 2, 1) && ";
	print "\n     editNum(document.Chg.pct6, 2, 1) && ";
	print "\n     editNum(document.Chg.pct7, 2, 1)) ";
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
	$pageID = "EMPLOYEEACHMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From PREACH02 ";
		$stmtSQL .= " Where ACCOMP='$fromComp' and ACFACL='$fromFacl' and ACEMPL='$fromEmpl' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hpracm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hpracm_OPT['sec_01'];
	$sec_02=$hpracm_OPT['sec_02'];
	$sec_03=$hpracm_OPT['sec_03'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "coNum";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_ACCOMP=DecatErr_Field("@@comp", "coNum");
			$Err_ACFACL=DecatErr_Field("@@facl", "faclNum");
			$Err_ACEMPL=DecatErr_Field("@@empl", "emplNum");
			$Err_ACTYP1=DecatErr_Field("@@typ1", "typ1");
			$Err_ACACT1=DecatErr_Field("@@act1", "act1");
			$Err_ACTRN1=DecatErr_Field("@@trn1", "trn1");
			$Err_ACPRE1=DecatErr_Field("@@pre1", "pre1");
			$Err_ACAMT1=DecatErr_Field("@@amt1", "amt1");
			$Err_ACPCT1=DecatErr_Field("@@pct1", "pct1");
			$Err_ACTYP2=DecatErr_Field("@@typ2", "typ2");
			$Err_ACACT2=DecatErr_Field("@@act2", "act2");
			$Err_ACTRN2=DecatErr_Field("@@trn2", "trn2");
			$Err_ACPRE2=DecatErr_Field("@@pre2", "pre2");
			$Err_ACAMT2=DecatErr_Field("@@amt2", "amt2");
			$Err_ACPCT2=DecatErr_Field("@@pct2", "pct2");
			$Err_ACTYP3=DecatErr_Field("@@typ3", "typ3");
			$Err_ACACT3=DecatErr_Field("@@act3", "act3");
			$Err_ACTRN3=DecatErr_Field("@@trn3", "trn3");
			$Err_ACPRE3=DecatErr_Field("@@pre3", "pre3");
			$Err_ACAMT3=DecatErr_Field("@@amt3", "amt3");
			$Err_ACPCT3=DecatErr_Field("@@pct3", "pct3");
			$Err_ACTYP4=DecatErr_Field("@@typ4", "typ4");
			$Err_ACACT4=DecatErr_Field("@@act4", "act4");
			$Err_ACTRN4=DecatErr_Field("@@trn4", "trn4");
			$Err_ACPRE4=DecatErr_Field("@@pre4", "pre4");
			$Err_ACAMT4=DecatErr_Field("@@amt4", "amt4");
			$Err_ACPCT4=DecatErr_Field("@@pct4", "pct4");
			$Err_ACTYP5=DecatErr_Field("@@typ5", "typ5");
			$Err_ACACT5=DecatErr_Field("@@act5", "act5");
			$Err_ACTRN5=DecatErr_Field("@@trn5", "trn5");
			$Err_ACPRE5=DecatErr_Field("@@pre5", "pre5");
			$Err_ACAMT5=DecatErr_Field("@@amt5", "amt5");
			$Err_ACPCT5=DecatErr_Field("@@pct5", "pct5");
			$Err_ACTYP6=DecatErr_Field("@@typ6", "typ6");
			$Err_ACACT6=DecatErr_Field("@@act6", "act6");
			$Err_ACTRN6=DecatErr_Field("@@trn6", "trn6");
			$Err_ACPRE6=DecatErr_Field("@@pre6", "pre6");
			$Err_ACAMT6=DecatErr_Field("@@amt6", "amt6");
			$Err_ACPCT6=DecatErr_Field("@@pct6", "pct6");
			$Err_ACTYP7=DecatErr_Field("@@typ7", "typ7");
			$Err_ACACT7=DecatErr_Field("@@act7", "act7");
			$Err_ACTRN7=DecatErr_Field("@@trn7", "trn7");
			$Err_ACPRE7=DecatErr_Field("@@pre7", "pre7");
			$Err_ACAMT7=DecatErr_Field("@@amt7", "amt7");
			$Err_ACPCT7=DecatErr_Field("@@pct7", "pct7");
			$Err_ACNET=DecatErr_Field("@@net@", "net");
			$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");
			$errFound= "";
		}

		$row['ACCOMP']=Decat_Field("@@comp", $edtVar);
		$row['ACFACL']=Decat_Field("@@facl", $edtVar);
		$row['ACEMPL']=Decat_Field("@@empl", $edtVar);
		$row['ACTYP1']=Decat_Field("@@typ1", $edtVar);
		$row['ACACT1']=Decat_Field("@@act1", $edtVar);
		$row['ACTRN1']=Decat_Field("@@trn1", $edtVar);
		$row['ACPRE1']=Decat_Field("@@pre1", $edtVar);
		$row['ACAMT1']=Decat_Field("@@amt1", $edtVar);
		$row['ACPCT1']=Decat_Field("@@pct1", $edtVar);
		$row['ACTYP2']=Decat_Field("@@typ2", $edtVar);
		$row['ACACT2']=Decat_Field("@@act2", $edtVar);
		$row['ACTRN2']=Decat_Field("@@trn2", $edtVar);
		$row['ACPRE2']=Decat_Field("@@pre2", $edtVar);
		$row['ACAMT2']=Decat_Field("@@amt2", $edtVar);
		$row['ACPCT2']=Decat_Field("@@pct2", $edtVar);
		$row['ACTYP3']=Decat_Field("@@typ3", $edtVar);
		$row['ACACT3']=Decat_Field("@@act3", $edtVar);
		$row['ACTRN3']=Decat_Field("@@trn3", $edtVar);
		$row['ACPRE3']=Decat_Field("@@pre3", $edtVar);
		$row['ACAMT3']=Decat_Field("@@amt3", $edtVar);
		$row['ACPCT3']=Decat_Field("@@pct3", $edtVar);
		$row['ACTYP4']=Decat_Field("@@typ4", $edtVar);
		$row['ACACT4']=Decat_Field("@@act4", $edtVar);
		$row['ACTRN4']=Decat_Field("@@trn4", $edtVar);
		$row['ACPRE4']=Decat_Field("@@pre4", $edtVar);
		$row['ACAMT4']=Decat_Field("@@amt4", $edtVar);
		$row['ACPCT4']=Decat_Field("@@pct4", $edtVar);
		$row['ACTYP5']=Decat_Field("@@typ5", $edtVar);
		$row['ACACT5']=Decat_Field("@@act5", $edtVar);
		$row['ACTRN5']=Decat_Field("@@trn5", $edtVar);
		$row['ACPRE5']=Decat_Field("@@pre5", $edtVar);
		$row['ACAMT5']=Decat_Field("@@amt5", $edtVar);
		$row['ACPCT5']=Decat_Field("@@pct5", $edtVar);
		$row['ACTYP6']=Decat_Field("@@typ6", $edtVar);
		$row['ACACT6']=Decat_Field("@@act6", $edtVar);
		$row['ACTRN6']=Decat_Field("@@trn6", $edtVar);
		$row['ACPRE6']=Decat_Field("@@pre6", $edtVar);
		$row['ACAMT6']=Decat_Field("@@amt6", $edtVar);
		$row['ACPCT6']=Decat_Field("@@pct6", $edtVar);
		$row['ACTYP7']=Decat_Field("@@typ7", $edtVar);
		$row['ACACT7']=Decat_Field("@@act7", $edtVar);
		$row['ACTRN7']=Decat_Field("@@trn7", $edtVar);
		$row['ACPRE7']=Decat_Field("@@pre7", $edtVar);
		$row['ACAMT7']=Decat_Field("@@amt7", $edtVar);
		$row['ACPCT7']=Decat_Field("@@pct7", $edtVar);
		$row['ACTSTP']=Decat_Field("@@tstp", $edtVar);
		$row['ACNET']=Decat_Field("@@net@", $edtVar);

	}	elseif ($maintenanceCode=="Z") {
		$focusField= "coNum";
		$row[ACPCT1]=100*($row[ACPCT1]);
		$row[ACPCT2]=100*($row[ACPCT2]);
		$row[ACPCT3]=100*($row[ACPCT3]);
		$row[ACPCT4]=100*($row[ACPCT4]);
		$row[ACPCT5]=100*($row[ACPCT5]);
		$row[ACPCT6]=100*($row[ACPCT6]);
		$row[ACPCT7]=100*($row[ACPCT7]);

	} else {
		$focusField= "typ1";
		$row[ACPCT1]=100*($row[ACPCT1]);
		$row[ACPCT2]=100*($row[ACPCT2]);
		$row[ACPCT3]=100*($row[ACPCT3]);
		$row[ACPCT4]=100*($row[ACPCT4]);
		$row[ACPCT5]=100*($row[ACPCT5]);
		$row[ACPCT6]=100*($row[ACPCT6]);
		$row[ACPCT7]=100*($row[ACPCT7]);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_TSTP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>.</span></td>";
	print "\n     <td><input type=\"hidden\" name=\"timeStamp\" value=\"" . rtrim($row['ACTSTP']) . "\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_TSTP);
	if (is_null($row['ACCOMP']) || trim($row['ACCOMP'])=="") {$row['ACCOMP']=0;}
	if (is_null($row['ACFACL']) || trim($row['ACFACL'])=="")  {$row['ACFACL']=0;}
	$coFacDesc=RetValue("CFCOMP=$row[ACCOMP] and CFFACL=$row[ACFACL]", "HRCOFC", "CFNAME");
	$textOvr=SetTextOvr($Err_ACCOMP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company/Facility</span></td> ";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"coNum\" value=\"" . rtrim($row['ACCOMP']) . "\" size=\"1\" maxlength=\"2\"> / <input type=\"text\"   name=\"faclNum\" value=\"" . rtrim($row['ACFACL']) . "\" size=\"1\" maxlength=\"4\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldEmpl=emplNum&amp;fldEmplName=emplDesc&amp;fldCo=coNum&amp;fldFacl=faclNum&amp;fldCoName=coNumDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"coNumDesc\">$coFacDesc</span></td>";
	} else {
		print "\n     <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"coNum\" value=\"" . rtrim($row['ACCOMP']) . "\">$row[ACCOMP] / <input type=\"hidden\"   name=\"faclNum\" value=\"" . rtrim($row['ACFACL']) . "\">$row[ACFACL]";
		print "\n     <span class=\"dspdesc\">$coFacDesc</span></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_ACCOMP);


	$F_Name=Format_EmplName($row[EMFNAM],$row[EMLNAM],$row[EMMIDI],$row[EMRNAM],$row[EMTRCD],"D");
	$textOvr=SetTextOvr($Err_ACCOMP);
	$textOvr=SetTextOvr($Err_ACEMPL);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Employee</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"emplNum\" value=\"" . rtrim($row['ACEMPL']) . "\" size=\"3\" maxlength=\"5\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldEmpl=emplNum&amp;fldEmplName=emplDesc&amp;fldCo=coNum&amp;fldFacl=faclNum&amp;fldCoName=coNumDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"emplDesc\">$F_Name</span></td>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"emplNum\" value=\"" . rtrim($row['ACEMPL']) . "\">$row[ACEMPL]";
		print "\n     <span class=\"dspdesc\">$F_Name</span></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_ACCOMP);
	DspErrMsg($Err_ACEMPL);

	print "\n <tr><td>&nbsp;</td></tr>";

	print "\n </table> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Deposit Entry</legend> ";
	print "\n <table $contentTable>";

	print "\n <tr><td class=\"colhdr\">Deposit</td><td class=\"colhdr\">Type</td><td class=\"colhdr\">Acct Number</td><td class=\"colhdr\">Transit<br>Routing#</td><td class=\"colhdr\">Pre-Note</td><td class=\"colhdr\">Amount</td><td class=\"colhdr\">Percent</td><td class=\"colhdr\">Net Deposit</td></tr> ";

	print "\n <tr><td class=\"colcode\">1</td>";
	$textOvr=SetTextOvr($Err_ACTYP1);
	$textOvr=SetTextOvr($Err_ACACT1);
	$textOvr=SetTextOvr($Err_ACTRN1);
	$textOvr=SetTextOvr($Err_ACPRE1);
	$textOvr=SetTextOvr($Err_ACAMT1);
	$textOvr=SetTextOvr($Err_ACPCT1);
	$fieldDesc=RetValue("FLTYPE='ACCTTYPE' and FLVALU='$row[ACTYP1]'", "SYFLAG", "FLDESC");
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"typ1\" value=\"" . rtrim($row['ACTYP1']) . "\" size=\"1\" maxlength=\"1\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=ACCTTYPE&amp;flagSrchHdr=" . urlencode("Account Type") . "&amp;docName=Chg&amp;fldName=typ1&amp;fldDesc=typ1Desc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n                             <span class=\"dspdesc\" id=\"typ1Desc\">$fieldDesc</span>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"act1\" value=\"" . rtrim($row['ACACT1']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"trn1\" value=\"" . rtrim($row['ACTRN1']) . "\" size=\"10\" maxlength=\"9\"></td> ";
	$fldChecked=Field_Checked($row['ACPRE1'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"pre1\" value=\"Y\" $fldChecked></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"amt1\" value=\"" . rtrim($row['ACAMT1']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"pct1\" value=\"" . rtrim($row['ACPCT1']) . "\" size=\"10\" maxlength=\"4\"></td></tr> ";
	DspErrMsg($Err_ACTYP1);
	DspErrMsg($Err_ACACT1);
	DspErrMsg($Err_ACTRN1);
	DspErrMsg($Err_ACPRE1);
	DspErrMsg($Err_ACAMT1);
	DspErrMsg($Err_ACPCT1);

	print "\n <tr><td class=\"colcode\">2</td>";
	$textOvr=SetTextOvr($Err_ACTYP2);
	$textOvr=SetTextOvr($Err_ACACT2);
	$textOvr=SetTextOvr($Err_ACTRN2);
	$textOvr=SetTextOvr($Err_ACPRE2);
	$textOvr=SetTextOvr($Err_ACAMT2);
	$textOvr=SetTextOvr($Err_ACPCT2);
	$fieldDesc=RetValue("FLTYPE='ACCTTYPE' and FLVALU='$row[ACTYP2]'", "SYFLAG", "FLDESC");
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"typ2\" value=\"" . rtrim($row['ACTYP2']) . "\" size=\"1\" maxlength=\"1\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=ACCTTYPE&amp;flagSrchHdr=" . urlencode("Account Type") . "&amp;docName=Chg&amp;fldName=typ2&amp;fldDesc=typ2Desc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n                             <span class=\"dspdesc\" id=\"typ2Desc\">$fieldDesc</span>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"act2\" value=\"" . rtrim($row['ACACT2']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"trn2\" value=\"" . rtrim($row['ACTRN2']) . "\" size=\"10\" maxlength=\"9\"></td> ";
	$fldChecked=Field_Checked($row['ACPRE2'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"pre2\" value=\"Y\" $fldChecked></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"amt2\" value=\"" . rtrim($row['ACAMT2']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"pct2\" value=\"" . rtrim($row['ACPCT2']) . "\" size=\"10\" maxlength=\"4\"></td></tr> ";
	DspErrMsg($Err_ACTYP2);
	DspErrMsg($Err_ACACT2);
	DspErrMsg($Err_ACTRN2);
	DspErrMsg($Err_ACPRE2);
	DspErrMsg($Err_ACAMT2);
	DspErrMsg($Err_ACPCT2);

	print "\n <tr><td class=\"colcode\">3</td>";
	$textOvr=SetTextOvr($Err_ACTYP3);
	$textOvr=SetTextOvr($Err_ACACT3);
	$textOvr=SetTextOvr($Err_ACTRN3);
	$textOvr=SetTextOvr($Err_ACPRE3);
	$textOvr=SetTextOvr($Err_ACAMT3);
	$textOvr=SetTextOvr($Err_ACPCT3);
	$fieldDesc=RetValue("FLTYPE='ACCTTYPE' and FLVALU='$row[ACTYP3]'", "SYFLAG", "FLDESC");
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"typ3\" value=\"" . rtrim($row['ACTYP3']) . "\" size=\"1\" maxlength=\"1\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=ACCTTYPE&amp;flagSrchHdr=" . urlencode("Account Type") . "&amp;docName=Chg&amp;fldName=typ3&amp;fldDesc=typ3Desc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n                             <span class=\"dspdesc\" id=\"typ3Desc\">$fieldDesc</span>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"act3\" value=\"" . rtrim($row['ACACT3']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"trn3\" value=\"" . rtrim($row['ACTRN3']) . "\" size=\"10\" maxlength=\"9\"></td> ";
	$fldChecked=Field_Checked($row['ACPRE3'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"pre3\" value=\"Y\" $fldChecked></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"amt3\" value=\"" . rtrim($row['ACAMT3']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"pct3\" value=\"" . rtrim($row['ACPCT3']) . "\" size=\"10\" maxlength=\"4\"></td></tr> ";
	DspErrMsg($Err_ACTYP3);
	DspErrMsg($Err_ACACT3);
	DspErrMsg($Err_ACTRN3);
	DspErrMsg($Err_ACPRE3);
	DspErrMsg($Err_ACAMT3);
	DspErrMsg($Err_ACPCT3);

	print "\n <tr><td class=\"colcode\">4</td>";
	$textOvr=SetTextOvr($Err_ACTYP4);
	$textOvr=SetTextOvr($Err_ACACT4);
	$textOvr=SetTextOvr($Err_ACTRN4);
	$textOvr=SetTextOvr($Err_ACPRE4);
	$textOvr=SetTextOvr($Err_ACAMT4);
	$textOvr=SetTextOvr($Err_ACPCT4);
	$fieldDesc=RetValue("FLTYPE='ACCTTYPE' and FLVALU='$row[ACTYP4]'", "SYFLAG", "FLDESC");
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"typ4\" value=\"" . rtrim($row['ACTYP4']) . "\" size=\"1\" maxlength=\"1\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=ACCTTYPE&amp;flagSrchHdr=" . urlencode("Account Type") . "&amp;docName=Chg&amp;fldName=typ4&amp;fldDesc=typ4Desc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n                             <span class=\"dspdesc\" id=\"typ4Desc\">$fieldDesc</span>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"act4\" value=\"" . rtrim($row['ACACT4']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"trn4\" value=\"" . rtrim($row['ACTRN4']) . "\" size=\"10\" maxlength=\"9\"></td> ";
	$fldChecked=Field_Checked($row['ACPRE4'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"pre4\" value=\"Y\" $fldChecked></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"amt4\" value=\"" . rtrim($row['ACAMT4']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"pct4\" value=\"" . rtrim($row['ACPCT4']) . "\" size=\"10\" maxlength=\"4\"></td></tr> ";
	DspErrMsg($Err_ACTYP4);
	DspErrMsg($Err_ACACT4);
	DspErrMsg($Err_ACTRN4);
	DspErrMsg($Err_ACPRE4);
	DspErrMsg($Err_ACAMT4);
	DspErrMsg($Err_ACPCT4);

	print "\n <tr><td class=\"colcode\">5</td>";
	$textOvr=SetTextOvr($Err_ACTYP5);
	$textOvr=SetTextOvr($Err_ACACT5);
	$textOvr=SetTextOvr($Err_ACTRN5);
	$textOvr=SetTextOvr($Err_ACPRE5);
	$textOvr=SetTextOvr($Err_ACAMT5);
	$textOvr=SetTextOvr($Err_ACPCT5);
	$fieldDesc=RetValue("FLTYPE='ACCTTYPE' and FLVALU='$row[ACTYP5]'", "SYFLAG", "FLDESC");
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"typ5\" value=\"" . rtrim($row['ACTYP5']) . "\" size=\"1\" maxlength=\"1\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=ACCTTYPE&amp;flagSrchHdr=" . urlencode("Account Type") . "&amp;docName=Chg&amp;fldName=typ5&amp;fldDesc=typ5Desc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n                             <span class=\"dspdesc\" id=\"typ5Desc\">$fieldDesc</span>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"act5\" value=\"" . rtrim($row['ACACT5']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"trn5\" value=\"" . rtrim($row['ACTRN5']) . "\" size=\"10\" maxlength=\"9\"></td> ";
	$fldChecked=Field_Checked($row['ACPRE5'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"pre5\" value=\"Y\" $fldChecked></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"amt5\" value=\"" . rtrim($row['ACAMT5']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"pct5\" value=\"" . rtrim($row['ACPCT5']) . "\" size=\"10\" maxlength=\"4\"></td></tr> ";
	DspErrMsg($Err_ACTYP5);
	DspErrMsg($Err_ACACT5);
	DspErrMsg($Err_ACTRN5);
	DspErrMsg($Err_ACPRE5);
	DspErrMsg($Err_ACAMT5);
	DspErrMsg($Err_ACPCT5);

	print "\n <tr><td class=\"colcode\">6</td>";
	$textOvr=SetTextOvr($Err_ACTYP6);
	$textOvr=SetTextOvr($Err_ACACT6);
	$textOvr=SetTextOvr($Err_ACTRN6);
	$textOvr=SetTextOvr($Err_ACPRE6);
	$textOvr=SetTextOvr($Err_ACAMT6);
	$textOvr=SetTextOvr($Err_ACPCT6);
	$fieldDesc=RetValue("FLTYPE='ACCTTYPE' and FLVALU='$row[ACTYP6]'", "SYFLAG", "FLDESC");
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"typ6\" value=\"" . rtrim($row['ACTYP6']) . "\" size=\"1\" maxlength=\"1\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=ACCTTYPE&amp;flagSrchHdr=" . urlencode("Account Type") . "&amp;docName=Chg&amp;fldName=typ6&amp;fldDesc=typ6Desc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n                             <span class=\"dspdesc\" id=\"typ6Desc\">$fieldDesc</span>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"act6\" value=\"" . rtrim($row['ACACT6']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"trn6\" value=\"" . rtrim($row['ACTRN6']) . "\" size=\"10\" maxlength=\"9\"></td> ";
	$fldChecked=Field_Checked($row['ACPRE6'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"pre6\" value=\"Y\" $fldChecked></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"amt6\" value=\"" . rtrim($row['ACAMT6']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"pct6\" value=\"" . rtrim($row['ACPCT6']) . "\" size=\"10\" maxlength=\"4\"></td></tr> ";
	DspErrMsg($Err_ACTYP6);
	DspErrMsg($Err_ACACT6);
	DspErrMsg($Err_ACTRN6);
	DspErrMsg($Err_ACPRE6);
	DspErrMsg($Err_ACAMT6);
	DspErrMsg($Err_ACPCT6);

	print "\n <tr><td class=\"colcode\">7</td>";
	$textOvr=SetTextOvr($Err_ACTYP7);
	$textOvr=SetTextOvr($Err_ACACT7);
	$textOvr=SetTextOvr($Err_ACTRN7);
	$textOvr=SetTextOvr($Err_ACPRE7);
	$textOvr=SetTextOvr($Err_ACAMT7);
	$textOvr=SetTextOvr($Err_ACPCT7);
	$textOvr=SetTextOvr($Err_ACNET);
	$fieldDesc=RetValue("FLTYPE='ACCTTYPE' and FLVALU='$row[ACTYP7]'", "SYFLAG", "FLDESC");
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"typ7\" value=\"" . rtrim($row['ACTYP7']) . "\" size=\"1\" maxlength=\"1\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=ACCTTYPE&amp;flagSrchHdr=" . urlencode("Account Type") . "&amp;docName=Chg&amp;fldName=typ7&amp;fldDesc=typ7Desc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n                             <span class=\"dspdesc\" id=\"typ7Desc\">$fieldDesc</span>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"act7\" value=\"" . rtrim($row['ACACT7']) . "\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"trn7\" value=\"" . rtrim($row['ACTRN7']) . "\" size=\"10\" maxlength=\"9\"></td> ";
	$fldChecked=Field_Checked($row['ACPRE7'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"pre7\" value=\"Y\" $fldChecked></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"amt7\" value=\"" . rtrim($row['ACAMT7']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"pct7\" value=\"" . rtrim($row['ACPCT7']) . "\" size=\"10\" maxlength=\"4\"></td> ";
	$fldChecked=Field_Checked($row['ACNET'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"net\" value=\"Y\" $fldChecked></td></tr> ";
	DspErrMsg($Err_ACTYP7);
	DspErrMsg($Err_ACACT7);
	DspErrMsg($Err_ACTRN7);
	DspErrMsg($Err_ACPRE7);
	DspErrMsg($Err_ACAMT7);
	DspErrMsg($Err_ACPCT7);
	DspErrMsg($Err_ACNET);

	print "\n </table> ";
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

if ($tag == "Edit_Data") {
	if ($maintenanceCode=="D" && is_null($_POST['coNum'])) {
		$_POST['coNum']        =$fromComp;
		$_POST['faclNum']      =$fromFacl;
		$_POST['emplNum']      =$fromEmpl;
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@comp", $_POST['coNum']);
	Concat_Field("@@facl", $_POST['faclNum']);
	Concat_Field("@@empl", $_POST['emplNum']);

	$_POST['typ1']=strtoupper($_POST['typ1']);  Concat_Field("@@typ1", $_POST['typ1']);
	$_POST['act1']=strtoupper($_POST['act1']);  Concat_Field("@@act1", $_POST['act1']);
	$_POST['trn1']=strtoupper($_POST['trn1']);  Concat_Field("@@trn1", $_POST['trn1']);
	if (!isset($_POST['pre1'])) {$_POST['pre1']="N";}
	Concat_Field("@@pre1", $_POST['pre1']);
	Concat_Field("@@amt1", $_POST['amt1']);
	Concat_Field("@@pct1", $_POST['pct1']);

	$_POST['typ2']=strtoupper($_POST['typ2']);  Concat_Field("@@typ2", $_POST['typ2']);
	$_POST['act2']=strtoupper($_POST['act2']);  Concat_Field("@@act2", $_POST['act2']);
	$_POST['trn2']=strtoupper($_POST['trn2']);  Concat_Field("@@trn2", $_POST['trn2']);
	if (!isset($_POST['pre2'])) {$_POST['pre2']="N";}
	Concat_Field("@@pre2", $_POST['pre2']);
	Concat_Field("@@amt2", $_POST['amt2']);
	Concat_Field("@@pct2", $_POST['pct2']);

	$_POST['typ3']=strtoupper($_POST['typ3']);  Concat_Field("@@typ3", $_POST['typ3']);
	$_POST['act3']=strtoupper($_POST['act3']);  Concat_Field("@@act3", $_POST['act3']);
	$_POST['trn3']=strtoupper($_POST['trn3']);  Concat_Field("@@trn3", $_POST['trn3']);
	if (!isset($_POST['pre3'])) {$_POST['pre3']="N";}
	Concat_Field("@@pre3", $_POST['pre3']);
	Concat_Field("@@amt3", $_POST['amt3']);
	Concat_Field("@@pct3", $_POST['pct3']);

	$_POST['typ4']=strtoupper($_POST['typ4']);  Concat_Field("@@typ4", $_POST['typ4']);
	$_POST['act4']=strtoupper($_POST['act4']);  Concat_Field("@@act4", $_POST['act4']);
	$_POST['trn4']=strtoupper($_POST['trn4']);  Concat_Field("@@trn4", $_POST['trn4']);
	if (!isset($_POST['pre4'])) {$_POST['pre4']="N";}
	Concat_Field("@@pre4", $_POST['pre4']);
	Concat_Field("@@amt4", $_POST['amt4']);
	Concat_Field("@@pct4", $_POST['pct4']);

	$_POST['typ5']=strtoupper($_POST['typ5']);  Concat_Field("@@typ5", $_POST['typ5']);
	$_POST['act5']=strtoupper($_POST['act5']);  Concat_Field("@@act5", $_POST['act5']);
	$_POST['trn5']=strtoupper($_POST['trn5']);  Concat_Field("@@trn5", $_POST['trn5']);
	if (!isset($_POST['pre5'])) {$_POST['pre5']="N";}
	Concat_Field("@@pre5", $_POST['pre5']);
	Concat_Field("@@amt5", $_POST['amt5']);
	Concat_Field("@@pct5", $_POST['pct5']);

	$_POST['typ6']=strtoupper($_POST['typ6']);  Concat_Field("@@typ6", $_POST['typ6']);
	$_POST['act6']=strtoupper($_POST['act6']);  Concat_Field("@@act6", $_POST['act6']);
	$_POST['trn6']=strtoupper($_POST['trn6']);  Concat_Field("@@trn6", $_POST['trn6']);
	if (!isset($_POST['pre6'])) {$_POST['pre6']="N";}
	Concat_Field("@@pre6", $_POST['pre6']);
	Concat_Field("@@amt6", $_POST['amt6']);
	Concat_Field("@@pct6", $_POST['pct6']);

	$_POST['typ7']=strtoupper($_POST['typ7']);  Concat_Field("@@typ7", $_POST['typ7']);
	$_POST['act7']=strtoupper($_POST['act7']);  Concat_Field("@@act7", $_POST['act7']);
	$_POST['trn7']=strtoupper($_POST['trn7']);  Concat_Field("@@trn7", $_POST['trn7']);
	if (!isset($_POST['pre7'])) {$_POST['pre7']="N";}
	Concat_Field("@@pre7", $_POST['pre7']);
	Concat_Field("@@amt7", $_POST['amt7']);
	Concat_Field("@@pct7", $_POST['pct7']);
	Concat_Field("@@tstp", $_POST['timeStamp']);
	if (!isset($_POST['net'])) {$_POST['net']="N";}
	Concat_Field("@@net@", $_POST['net']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPRACM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$Err_ACCOMP=DecatErr_Field("@@comp", "coNum");

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, " Co/Fac: [$_POST[coNum] / $_POST[faclNum]] Empl: [$_POST[emplNum]]", "", "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif (($maintenanceCode == "D" || $maintenanceCode == "C") && $errFound != "" && $Err_ACCOMP != "") {
		$Err_ACCOMP=DecatErr_Field("@@comp", "coNum");
		$Err_ACFACL=DecatErr_Field("@@facl", "faclNum");
		$Err_ACEMPL=DecatErr_Field("@@empl", "emplNum");
		$confMessage=Format_ConfMsg_Desc("E", " Co/Fac: [$_POST[coNum] / $_POST[faclNum]] Empl: [$_POST[emplNum]]", "", "<br>$Err_ACCOMP", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;coNum=" . urlencode(trim($_POST['coNum'])) . "&amp;faclNum=" . urlencode(trim($_POST['faclNum'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>