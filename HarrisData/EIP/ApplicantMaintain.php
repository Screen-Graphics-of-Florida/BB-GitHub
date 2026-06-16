<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromCo             = $_GET['fromCo'];
$fromDate           = $_GET['fromDate'];
$fromSeq            = $_GET['fromSeq'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Applicant Maintenance";
$scriptName     = "ApplicantMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;fromCo=" . urlencode(trim($fromCo)) . "&amp;fromDate=" . urlencode(trim($fromDate)) . "&amp;fromSeq=" . urlencode(trim($fromSeq)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPEAPU_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=71";}

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
	require_once 'DateEdit.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	require_once 'CheckEnterChg.php';
	require_once 'CalendarInclude.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.lastName.value ==\"\" || ";
	print "\n     document.Chg.firstName.value ==\"\" || ";
	print "\n     document.Chg.reportName.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.applComp, 2, 0) && ";
	print "\n     editdate(document.Chg.applDate) && ";
	print "\n     editNum(document.Chg.phone, 10, 0) && ";
	print "\n     editNum(document.Chg.ethnicID, 1, 0) && ";
	print "\n     editNum(document.Chg.jobCateg, 2, 3) && ";
	print "\n     editNum(document.Chg.ssn, 9, 0) && ";
	print "\n     editNum(document.Chg.emplHRNum, 9, 0) && ";
	print "\n     editNum(document.Chg.yearGrad, 4, 0) && ";
	print "\n     editdate(document.Chg.intvwDate) && ";
	print "\n     editdate(document.Chg.nextIntvwDate) && ";
	print "\n     editdate(document.Chg.availDate) && ";
	print "\n     editNum(document.Chg.salaryMin, 7, 2) && ";
	print "\n     editNum(document.Chg.salaryMax, 7, 2) && ";
	print "\n     editNum(document.Chg.prvPosStrtDate1, 4, 0) && ";
	print "\n     editNum(document.Chg.prvPosEndDate1, 4, 0) && ";
	print "\n     editNum(document.Chg.prvPosStrtDate2, 4, 0) && ";
	print "\n     editNum(document.Chg.prvPosEndDate2, 4, 0) && ";
	print "\n     editNum(document.Chg.prvPosStrtDate3, 4, 0) && ";
	print "\n     editNum(document.Chg.prvPosEndDate3, 4, 0) && ";
	print "\n     editNum(document.Chg.refPhone1, 10, 0) && ";
	print "\n     editNum(document.Chg.refPhone2, 10, 0) && ";
	print "\n     editNum(document.Chg.refPhone3, 10, 0) && ";
	print "\n     editdate(document.Chg.drugTestDate) && ";
	print "\n     editdate(document.Chg.emplTestDate) && ";
	print "\n     editdate(document.Chg.drvLicExpDate)) ";
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
	$pageID = "APPLICANTMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From PEAPPL ";
		$stmtSQL .= " Where APCOMP=$fromCo and APAPDT=$fromDate and APAPSQ=$fromSeq ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hpeapu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hpeapu_OPT['sec_01'];
	$sec_02=$hpeapu_OPT['sec_02'];
	$sec_03=$hpeapu_OPT['sec_03'];
	$sec_04=$hpeapu_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "applComp";
			$edtVar= "";

		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_APCOMP=DecatErr_Field("@@comp", "applComp");
			$Err_APAPDT=DecatErr_Field("@@apdt", "applDate");
			$Err_APLNAM=DecatErr_Field("@@lnam", "lastName");
			$Err_APFNAM=DecatErr_Field("@@fnam", "firstName");
			$Err_APMIDI=DecatErr_Field("@@midi", "midInit");
			$Err_APRNAM=DecatErr_Field("@@rnam", "reportName");
			$Err_APADR1=DecatErr_Field("@@adr1", "adrLine1");
			$Err_APADR2=DecatErr_Field("@@adr2", "adrLine2");
			$Err_APCITY=DecatErr_Field("@@city", "city");
			$Err_APST  =DecatErr_Field("@@st@@", "state");
			$Err_APZIP =DecatErr_Field("@@zip@", "zip");
			$Err_APPHON=DecatErr_Field("@@phon", "phone");
			$Err_APSSNO=DecatErr_Field("@@ssno", "ssn");
			$Err_APETID=DecatErr_Field("@@etid", "ethnicID");
			$Err_APGNDR=DecatErr_Field("@@gndr", "gender");
			$Err_APJBCD=DecatErr_Field("@@jbcd", "jobCode");
			$Err_APPSTN=DecatErr_Field("@@pstn", "position");
			$Err_APEEO =DecatErr_Field("@@eeo@", "jobCateg");
			$Err_APEMPL=DecatErr_Field("@@empl", "emplHRNum");
			$Err_APLCTN=DecatErr_Field("@@lctn", "location");
			$Err_APSTTS=DecatErr_Field("@@stts", "stsCode");
			$Err_APSKIL=DecatErr_Field("@@skil", "skills");
			$Err_APCMNT=DecatErr_Field("@@cmnt", "comments");
			$Err_APCER1=DecatErr_Field("@@cer1", "cert1");
			$Err_APCER2=DecatErr_Field("@@cer2", "cert2");
			$Err_APCER3=DecatErr_Field("@@cer3", "cert3");
			$Err_APBOND=DecatErr_Field("@@bond", "bonded");
			$Err_APGRAD=DecatErr_Field("@@grad", "yearGrad");
			$Err_APDGRE=DecatErr_Field("@@dgre", "degree");
			$Err_APSUBJ=DecatErr_Field("@@subj", "subjMajor");
			$Err_APSCHL=DecatErr_Field("@@schl", "school");
			$Err_APSLMN=DecatErr_Field("@@slmn", "salaryMin");
			$Err_APSLMX=DecatErr_Field("@@slmx", "salaryMax");
			$Err_APIVDT=DecatErr_Field("@@ivdt", "intvwDate");
			$Err_APIVR1=DecatErr_Field("@@ivr1", "intvwRemarks1");
			$Err_APIVR2=DecatErr_Field("@@ivr2", "intvwRemarks2");
			$Err_APNIVD=DecatErr_Field("@@nivd", "nextIntvwDate");
			$Err_APAVLD=DecatErr_Field("@@avld", "availDate");
			$Err_APHIRE=DecatErr_Field("@@hire", "hireSource");
			$Err_APAONF=DecatErr_Field("@@aonf", "apOnFile");
			$Err_APP1CO=DecatErr_Field("@@p1co", "prvPosCoName1");
			$Err_APP1PN=DecatErr_Field("@@p1pn", "prvPosPosition1");
			$Err_APP1SD=DecatErr_Field("@@p1sd", "prvPosStrtDate1");
			$Err_APP1ED=DecatErr_Field("@@p1ed", "prvPosEndDate1");
			$Err_APP2CO=DecatErr_Field("@@p2co", "prvPosCoName2");
			$Err_APP2PN=DecatErr_Field("@@p2pn", "prvPosPosition2");
			$Err_APP2SD=DecatErr_Field("@@p2sd", "prvPosStrtDate2");
			$Err_APP2ED=DecatErr_Field("@@p2ed", "prvPosEndDate2");
			$Err_APP3CO=DecatErr_Field("@@p3co", "prvPosCoName3");
			$Err_APP3PN=DecatErr_Field("@@p3pn", "prvPosPosition3");
			$Err_APP3SD=DecatErr_Field("@@p3sd", "prvPosStrtDate3");
			$Err_APP3ED=DecatErr_Field("@@p3ed", "prvPosEndDate3");
			$Err_APR1NM=DecatErr_Field("@@r1nm", "refName1");
			$Err_APR1PH=DecatErr_Field("@@r1ph", "refPhone1");
			$Err_APR1CK=DecatErr_Field("@@r1ck", "refCheck1");
			$Err_APR2NM=DecatErr_Field("@@r2nm", "refName2");
			$Err_APR2PH=DecatErr_Field("@@r2ph", "refPhone2");
			$Err_APR2CK=DecatErr_Field("@@r2ck", "refCheck2");
			$Err_APR3NM=DecatErr_Field("@@r3nm", "refName3");
			$Err_APR3PH=DecatErr_Field("@@r3ph", "refPhone3");
			$Err_APR3CK=DecatErr_Field("@@r3ck", "refCheck3");
			$Err_APDTRQ=DecatErr_Field("@@dtrq", "drugTestReqd");
			$Err_APDTDT=DecatErr_Field("@@dtdt", "drugTestDate");
			$Err_APDTPA=DecatErr_Field("@@dtpa", "drugTestPassd");
			$Err_APETRQ=DecatErr_Field("@@etrq", "emplTestReqd");
			$Err_APETDT=DecatErr_Field("@@etdt", "emplTestDate");
			$Err_APETPA=DecatErr_Field("@@etpa", "emplTestPassd");
			$Err_APMILT=DecatErr_Field("@@milt", "miltSts");
			$Err_APDLNO=DecatErr_Field("@@dlno", "drvLicNum");
			$Err_APDLCL=DecatErr_Field("@@dlcl", "drvLicCdlClass");
			$Err_APDLST=DecatErr_Field("@@dlst", "drvLicState");
			$Err_APDLED=DecatErr_Field("@@dled", "drvLicExpDate");
			$Err_APCFEL=DecatErr_Field("@@cfel", "everConvOfFelony");
			$Err_APUN18=DecatErr_Field("@@un18", "under18");
			$Err_APLEFE=DecatErr_Field("@@lefe", "legalEligForEmpInUS");
			$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");
		}

		$row['APCOMP']=Decat_Field("@@comp", $edtVar);
		$row['APAPDT']=Decat_Field("@@apdt", $edtVar);
		$row['APAPSQ']=Decat_Field("@@apsq", $edtVar);
		$row['APLNAM']=Decat_Field("@@lnam", $edtVar);
		$row['APFNAM']=Decat_Field("@@fnam", $edtVar);
		$row['APMIDI']=Decat_Field("@@midi", $edtVar);
		$row['APRNAM']=Decat_Field("@@rnam", $edtVar);
		$row['APADR1']=Decat_Field("@@adr1", $edtVar);
		$row['APADR2']=Decat_Field("@@adr2", $edtVar);
		$row['APCITY']=Decat_Field("@@city", $edtVar);
		$row['APST']=Decat_Field("@@st@@",   $edtVar);
		$row['APZIP']=Decat_Field("@@zip@",  $edtVar);
		$row['APPHON']=Decat_Field("@@phon", $edtVar);
		$row['APSSNO']=Decat_Field("@@ssno", $edtVar);
		$row['APSSNO']=RetColValue("$profileHandle", "$dataBaseID", "APCOMP=$fromCo and APAPDT=$fromDate and APAPSQ=$fromSeq", "PEAPPL", "APSSNO", "D");
		$row['APETID']=Decat_Field("@@etid", $edtVar);
		$row['APGNDR']=Decat_Field("@@gndr", $edtVar);
		$row['APJBCD']=Decat_Field("@@jbcd", $edtVar);
		$row['APPSTN']=Decat_Field("@@pstn", $edtVar);
		$row['APEEO']=Decat_Field("@@eeo@",  $edtVar);
		$row['APEMPL']=Decat_Field("@@empl", $edtVar);
		$row['APLCTN']=Decat_Field("@@lctn", $edtVar);
		$row['APSTTS']=Decat_Field("@@stts", $edtVar);
		$row['APSKIL']=Decat_Field("@@skil", $edtVar);
		$row['APCMNT']=Decat_Field("@@cmnt", $edtVar);
		$row['APCER1']=Decat_Field("@@cer1", $edtVar);
		$row['APCER2']=Decat_Field("@@cer2", $edtVar);
		$row['APCER3']=Decat_Field("@@cer3", $edtVar);
		$row['APBOND']=Decat_Field("@@bond", $edtVar);
		$row['APGRAD']=Decat_Field("@@grad", $edtVar);
		$row['APDGRE']=Decat_Field("@@dgre", $edtVar);
		$row['APSUBJ']=Decat_Field("@@subj", $edtVar);
		$row['APSCHL']=Decat_Field("@@schl", $edtVar);
		$row['APSLMN']=Decat_Field("@@slmn", $edtVar);
		$row['APSLMX']=Decat_Field("@@slmx", $edtVar);
		$row['APIVDT']=Decat_Field("@@ivdt", $edtVar);
		$row['APIVR1']=Decat_Field("@@ivr1", $edtVar);
		$row['APIVR2']=Decat_Field("@@ivr2", $edtVar);
		$row['APNIVD']=Decat_Field("@@nivd", $edtVar);
		$row['APAVLD']=Decat_Field("@@avld", $edtVar);
		$row['APHIRE']=Decat_Field("@@hire", $edtVar);
		$row['APAONF']=Decat_Field("@@aonf", $edtVar);
		$row['APP1CO']=Decat_Field("@@p1co", $edtVar);
		$row['APP1PN']=Decat_Field("@@p1pn", $edtVar);
		$row['APP1SD']=Decat_Field("@@p1sd", $edtVar);
		$row['APP1ED']=Decat_Field("@@p1ed", $edtVar);
		$row['APP2CO']=Decat_Field("@@p2co", $edtVar);
		$row['APP2PN']=Decat_Field("@@p2pn", $edtVar);
		$row['APP2SD']=Decat_Field("@@p2sd", $edtVar);
		$row['APP2ED']=Decat_Field("@@p2ed", $edtVar);
		$row['APP3CO']=Decat_Field("@@p3co", $edtVar);
		$row['APP3PN']=Decat_Field("@@p3pn", $edtVar);
		$row['APP3SD']=Decat_Field("@@p3sd", $edtVar);
		$row['APP3ED']=Decat_Field("@@p3ed", $edtVar);
		$row['APR1NM']=Decat_Field("@@r1nm", $edtVar);
		$row['APR1PH']=Decat_Field("@@r1ph", $edtVar);
		$row['APR1CK']=Decat_Field("@@r1ck", $edtVar);
		$row['APR2NM']=Decat_Field("@@r2nm", $edtVar);
		$row['APR2PH']=Decat_Field("@@r2ph", $edtVar);
		$row['APR2CK']=Decat_Field("@@r2ck", $edtVar);
		$row['APR3NM']=Decat_Field("@@r3nm", $edtVar);
		$row['APR3PH']=Decat_Field("@@r3ph", $edtVar);
		$row['APR3CK']=Decat_Field("@@r3ck", $edtVar);
		$row['APDTRQ']=Decat_Field("@@dtrq", $edtVar);
		$row['APDTDT']=Decat_Field("@@dtdt", $edtVar);
		$row['APDTPA']=Decat_Field("@@dtpa", $edtVar);
		$row['APETRQ']=Decat_Field("@@etrq", $edtVar);
		$row['APETDT']=Decat_Field("@@etdt", $edtVar);
		$row['APETPA']=Decat_Field("@@etpa", $edtVar);
		$row['APMILT']=Decat_Field("@@milt", $edtVar);
		$row['APDLNO']=Decat_Field("@@dlno", $edtVar);
		$row['APDLCL']=Decat_Field("@@dlcl", $edtVar);
		$row['APDLST']=Decat_Field("@@dlst", $edtVar);
		$row['APDLED']=Decat_Field("@@dled", $edtVar);
		$row['APCFEL']=Decat_Field("@@cfel", $edtVar);
		$row['APUN18']=Decat_Field("@@un18", $edtVar);
		$row['APLEFE']=Decat_Field("@@lefe", $edtVar);
		$row['APTSTP']=Decat_Field("@@tstp", $edtVar);

		if ($errFound == "" && $maintenanceCode == "A") {
			$row[APETID]=7;
		}

		$errFound= "";

	} else {
		if ($maintenanceCode=="Z") {$focusField= "applComp";} else {$focusField= "lastName";}
		$row[APAPDT]=DateInputFromCYMD($row[APAPDT]);
		$row[APIVDT]=DateInputFromCYMD($row[APIVDT]);
		$row[APNIVD]=DateInputFromCYMD($row[APNIVD]);
		$row[APAVLD]=DateInputFromCYMD($row[APAVLD]);
		$row[APDTDT]=DateInputFromCYMD($row[APDTDT]);
		$row[APETDT]=DateInputFromCYMD($row[APETDT]);
		$row[APDLED]=DateInputFromCYMD($row[APDLED]);
		$row[APSSNO]=RetColValue("$profileHandle", "$dataBaseID", "APCOMP=$fromCo and APAPDT=$fromDate and APAPSQ=$fromSeq", "PEAPPL", "APSSNO", "D");
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_TSTP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>.</span></td>";
	print "\n     <td><input type=\"hidden\" name=\"timeStamp\" value=\"" . rtrim($row['APTSTP']) . "\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_TSTP);
	
	$fieldDesc=RetValue("CFCOMP='$row[APCOMP]' and CFFACL=0", "HRCOFC", "CFNAME");
	$textOvr=SetTextOvr($Err_APCOMP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company Number</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"applComp\" value=\"" . rtrim($row['APCOMP']) . "\" size=\"2\" maxlength=\"2\">$reqFieldChar";
		print "\n                         <a href=\"{$homeURL}{$phpPath}HRCompanySearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=applComp&amp;fldDesc=coDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	} else {
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"applComp\" value=\"" . rtrim($row['APCOMP']) . "\">$row[APCOMP]";
	}
	print "\n <span class=\"dspdesc\" id=\"coDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APCOMP);

	$textOvr=SetTextOvr($Err_APAPDT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Application Date</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"applDate\" value=\"" . rtrim($row['APAPDT']) . "\"  size=\"6\" maxlength=\"6\">";
		print "\n  <a href=\"javascript:calWindow('applDate');\">$reqFieldChar $calendarImage</a></td>";
	} else {
		$F_APAPDT=Format_Date(DateToCYMD($row['APAPDT']), "D");
		print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"applDate\" value=\"" . rtrim($row['APAPDT']) . "\">$F_APAPDT</td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_APAPDT);

	if ($maintenanceCode=="C") {
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Sequence Number</span></td>";
		print "\n <td class=\"inputnmbr\">$row[APAPSQ]</td>";
		print "\n </tr> ";
	}

	Build_Fld_Entry("Last Name","lastName","inputalph","","APLNAM",$row[APLNAM],$Err_APLNAM,"18","18","Y","","","");
	Build_Fld_Entry("First Name","firstName","inputalph","","APFNAM",$row[APFNAM],$Err_APFNAM,"18","18","Y","","","");
	Build_Fld_Entry("Middle Initial","midInit","inputalph","","APMIDI",$row[APMIDI],$Err_APMIDI,"1","1","","","");
	Build_Fld_Entry("Report Name","reportName","inputalph","","APRNAM",$row[APRNAM],$Err_APRNAM,"23","23","Y","","");
	Build_Fld_Entry("Address Line 1","adrLine1","inputalph","","APADR1",$row[APADR1],$Err_APADR1,"30","30","","","");
	Build_Fld_Entry("Address Line 2","adrLine2","inputalph","","APADR2",$row[APADR2],$Err_APADR2,"30","30","","","");
	Build_Fld_Entry("City","city","inputalph","","APCITY",$row[APCITY],$Err_APCITY,"16","16","","","");

	$textOvr=SetTextOvr($Err_APST);
	$fieldDesc=RetValue("STID='$row[APST]'", "PRSTID", "STDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>State</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"state\" value=\"" . rtrim($row['APST']) . "\" size=\"1\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearchHR.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=state&amp;fldDesc=stateDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"stateDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APST);

	Build_Fld_Entry("Zip Code","zip","inputalph","","APZIP",$row[APZIP],$Err_APZIP,"10","10","","","");
	Build_Fld_Entry("Phone Number","phone","inputnmbr","","APPHON",$row[APPHON],$Err_APPHON,"10","10","","","");
	Build_Fld_Entry("Social Security Number","ssn","inputnmbr","","APSSNO",$row[APSSNO],$Err_APSSNO,"10","9","","","");
	Build_Fld_Entry("Ethnic ID","ethnicID","inputnmbr","ETHNICID","APETID",$row[APETID],$Err_APETID,"2","1","","","");
	Build_Fld_Entry("Gender","gender","inputalph","GENDER","APGNDR",$row[APGNDR],$Err_APGNDR,"2","1","","","");

	$textOvr=SetTextOvr($Err_APJBCD);
	$fieldDesc=RetValue("ODCOMP=$fromCo and ODTYPE='J' and ODCODE='$row[APJBCD]'", "PECODE", "ODDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Job Code Applied For</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"jobCode\" value=\"" . rtrim($row['APJBCD']) . "\" size=\"2\" maxlength=\"4\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=jobCode&amp;fldComp=$fromCo&amp;fldType=J&amp;fldDesc=jobCodeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"jobCodeDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APJBCD);

	Build_Fld_Entry("Position Applied For","position","inputalph","","APPSTN",$row[APPSTN],$Err_APPSTN,"20","20","","","");

	$textOvr=SetTextOvr($Err_APEEO);
	$fieldDesc=RetValue("ODCOMP=$fromCo and ODTYPE='Y' and ODCODE=$row[APEEO]", "PECODE", "ODDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>EEO-1 Job Category</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"jobCateg\" value=\"" . rtrim($row['APEEO']) . "\" size=\"2\" maxlength=\"5\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=jobCateg&amp;fldComp=$fromCo&amp;fldType=Y&amp;fldDesc=jobCategDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"jobCategDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APEEO);

	$textOvr=SetTextOvr($Err_APLCTN);
	$fieldDesc=RetValue("ODCOMP=$fromCo and ODTYPE='O' and ODCODE='$row[APLCTN]'", "PECODE", "ODDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Location Code</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"location\" value=\"" . rtrim($row['APLCTN']) . "\" size=\"2\" maxlength=\"4\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=location&amp;fldComp=$fromCo&amp;fldType=O&amp;fldDesc=locationDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"locationDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APLCTN);


	$firstName=RetValue("EMPEMP='$row[APEMPL]'", "HREMPL", "EMFNAM ");
	$lastName=RetValue("EMPEMP='$row[APEMPL]'", "HREMPL", "EMLNAM ");
	$name=Format_EmplName(trim($firstName),trim($lastName),"","","","");
	$F_Name=Format_Quote($name);
	$textOvr=SetTextOvr($Err_APCOMP);
	$textOvr=SetTextOvr($Err_APEMPL);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Employee</span></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"emplHRNum\" value=\"" . rtrim($row['APEMPL']) . "\" size=\"9\" maxlength=\"9\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;forHRCo=$fromCo&amp;fldHREmpl=emplHRNum&amp;fldEmplName=emplDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"emplDesc\">$F_Name</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APCOMP);
	DspErrMsg($Err_APEMPL);

	$textOvr=SetTextOvr($Err_APSTTS);
	$fieldDesc=RetValue("ODCOMP=$fromCo and ODTYPE='A' and ODCODE='$row[APSTTS]'", "PECODE", "ODDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Status Code</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"stsCode\" value=\"" . rtrim($row['APSTTS']) . "\" size=\"2\" maxlength=\"4\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=stsCode&amp;fldComp=$fromCo&amp;fldType=A&amp;fldDesc=stsCodeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"stsCodeDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APSTTS);

	Build_Fld_Entry("Skills","skills","inputalph","","APSKIL",$row[APSKIL],$Err_APSKIL,"20","15","","","");
	Build_Fld_Entry("Comments","comments","inputalph","","APCMNT",$row[APCMNT],$Err_APCMNT,"35","30","","","");
	Build_Fld_Entry("Certification 1","cert1","inputalph","","APCER1",$row[APCER1],$Err_APCER1,"20","20","","","");
	Build_Fld_Entry("Certification 2","cert2","inputalph","","APCER2",$row[APCER2],$Err_APCER2,"20","20","","","");
	Build_Fld_Entry("Certification 3","cert3","inputalph","","APCER3",$row[APCER3],$Err_APCER3,"20","20","","","");
	Build_Fld_Entry("Bonded","bonded","inputalph","YORN","APBOND",$row[APBOND],$Err_APBOND,"1","1","","","");
	Build_Fld_Entry("Year Graduated (Highest)","yearGrad","inputnmbr","","APGRAD",$row[APGRAD],$Err_APGRAD,"3","4","","","");
	Build_Fld_Entry("Degree (Highest)","degree","inputalph","","APDGRE",$row[APDGRE],$Err_APDGRE,"3","3","","","");
	Build_Fld_Entry("Subject/Major","subjMajor","inputalph","","APSUBJ",$row[APSUBJ],$Err_APSUBJ,"20","15","","","");
	Build_Fld_Entry("School (Latest)","school","inputalph","","APSCHL",$row[APSCHL],$Err_APSCHL,"20","15","","","");

	print "\n </table> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Salary Requirements</legend> ";
	print "\n <table $contentTable>";
	print "\n <tr><td>&nbsp;</td><td class=\"colhdr\">Minimum  -  Maximum</td></tr> ";

	$textOvr=SetTextOvr($Err_APSLMN);
	$textOvr=SetTextOvr($Err_APSLMX);
	print "\n <tr><td class=\"dsphdr\">&nbsp;</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"salaryMin\" value=\"" . rtrim($row['APSLMN']) . "\" size=\"7\" maxlength=\"10\"> <input type=\"text\"   name=\"salaryMax\" value=\"" . rtrim($row['APSLMX']) . "\" size=\"7\" maxlength=\"10\"> ";
	print "\n </tr> ";
	DspErrMsg($Err_APSLMN);
	DspErrMsg($Err_APSLMX);

	print "\n </table> ";
	print "\n </fieldset> ";


	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Interview</legend> ";
	print "\n <table $contentTable>";
	Build_Fld_Entry("Interview Date","intvwDate","inputdate","Date",APIVDT,$row[APIVDT],$Err_APIVDT,"","","","","");
	Build_Fld_Entry("Interview Remarks","intvwRemarks1","inputalph","","APIVR1",$row[APIVR1],$Err_APIVR1,"50","50","","","");
	Build_Fld_Entry("","intvwRemarks2","inputalph","","APIVR2",$row[APIVR2],$Err_APIVR2,"50","50","","","");
	Build_Fld_Entry("Next Interview Date","nextIntvwDate","inputdate","Date",APNIVD,$row[APNIVD],$Err_APNIVD,"","","","","");
	Build_Fld_Entry("Available Date","availDate","inputdate","Date",APAVLD,$row[APAVLD],$Err_APAVLD,"","","","","");

	$textOvr=SetTextOvr($Err_APHIRE);
	$fieldDesc=RetValue("ODCOMP=$fromCo and ODTYPE='W' and ODCODE='$row[APHIRE]'", "PECODE", "ODDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Hire Source</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"hireSource\" value=\"" . rtrim($row['APHIRE']) . "\" size=\"2\" maxlength=\"4\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hireSource&amp;fldComp=$fromCo&amp;fldType=W&amp;fldDesc=hireSourceDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"hireSourceDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APHIRE);

	Build_Fld_Entry("Application On File","apOnFile","inputalph","YORN","APAONF",$row[APAONF],$Err_APAONF,"1","1","","","");

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Previous Positions</legend> ";
	print "\n <table $contentTable>";
	print "\n <tr><td class=\"colhdr\">Previous:</td><td class=\"colhdr\">Company Name</td><td class=\"colhdr\">Position</td><td class=\"colhdr\">Dates Employed</td></tr> ";
	print "\n <tr><td class=\"dsphdr\">Position 1</td>";
	$row['APP1SD']=DateFromCYM($row['APP1SD']);
	$row['APP1ED']=DateFromCYM($row['APP1ED']);
	$textOvr=SetTextOvr($Err_APP1CO);
	$textOvr=SetTextOvr($Err_APP1PN);
	$textOvr=SetTextOvr($Err_APP1SD);
	$textOvr=SetTextOvr($Err_APP1ED);
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"prvPosCoName1\" value=\"" . rtrim($row['APP1CO']) . "\" size=\"20\" maxlength=\"20\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"prvPosPosition1\" value=\"" . rtrim($row['APP1PN']) . "\" size=\"20\" maxlength=\"20\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"prvPosStrtDate1\" value=\"" . rtrim($row['APP1SD']) . "\" size=\"4\" maxlength=\"4\"> - <input type=\"text\"   name=\"prvPosEndDate1\" value=\"" . rtrim($row['APP1ED']) . "\" size=\"4\" maxlength=\"4\"></tr> ";
	DspErrMsg($Err_APP1CO);
	DspErrMsg($Err_APP1PN);
	DspErrMsg($Err_APP1SD);
	DspErrMsg($Err_APP1ED);

	print "\n <tr><td class=\"dsphdr\">Position 2</td>";
	$row['APP2SD']=DateFromCYM($row['APP2SD']);
	$row['APP2ED']=DateFromCYM($row['APP2ED']);
	$textOvr=SetTextOvr($Err_APP2CO);
	$textOvr=SetTextOvr($Err_APP2PN);
	$textOvr=SetTextOvr($Err_APP2SD);
	$textOvr=SetTextOvr($Err_APP2ED);
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"prvPosCoName2\" value=\"" . rtrim($row['APP2CO']) . "\" size=\"20\" maxlength=\"20\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"prvPosPosition2\" value=\"" . rtrim($row['APP2PN']) . "\" size=\"20\" maxlength=\"20\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"prvPosStrtDate2\" value=\"" . rtrim($row['APP2SD']) . "\" size=\"4\" maxlength=\"4\"> - <input type=\"text\"   name=\"prvPosEndDate2\" value=\"" . rtrim($row['APP2ED']) . "\" size=\"4\" maxlength=\"4\"></tr> ";
	DspErrMsg($Err_APP2CO);
	DspErrMsg($Err_APP2PN);
	DspErrMsg($Err_APP2SD);
	DspErrMsg($Err_APP2ED);

	print "\n <tr><td class=\"dsphdr\">Position 3</td>";
	$row['APP3SD']=DateFromCYM($row['APP3SD']);
	$row['APP3ED']=DateFromCYM($row['APP3ED']);
	$textOvr=SetTextOvr($Err_APP3CO);
	$textOvr=SetTextOvr($Err_APP3PN);
	$textOvr=SetTextOvr($Err_APP3SD);
	$textOvr=SetTextOvr($Err_APP3ED);
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"prvPosCoName3\" value=\"" . rtrim($row['APP3CO']) . "\" size=\"20\" maxlength=\"20\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"prvPosPosition3\" value=\"" . rtrim($row['APP3PN']) . "\" size=\"20\" maxlength=\"20\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"prvPosStrtDate3\" value=\"" . rtrim($row['APP3SD']) . "\" size=\"4\" maxlength=\"4\"> - <input type=\"text\"   name=\"prvPosEndDate3\" value=\"" . rtrim($row['APP3ED']) . "\" size=\"4\" maxlength=\"4\"></tr> ";
	DspErrMsg($Err_APP3CO);
	DspErrMsg($Err_APP3PN);
	DspErrMsg($Err_APP3SD);
	DspErrMsg($Err_APP3ED);
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">References</legend> ";
	print "\n <table $contentTable>";
	print "\n <tr><td class=\"colhdr\">Name</td><td class=\"colhdr\">Phone Number</td><td class=\"colhdr\">Checked</td></tr> ";
	$textOvr=SetTextOvr($Err_APR1NM);
	$textOvr=SetTextOvr($Err_APR1PH);
	$textOvr=SetTextOvr($Err_APR1CK);
	print "\n <tr><td class=\"inputalph\"><input type=\"text\"   name=\"refName1\" value=\"" . rtrim($row['APR1NM']) . "\" size=\"20\" maxlength=\"20\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"refPhone1\" value=\"" . rtrim($row['APR1PH']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	$fldChecked=Field_Checked($row['APR1CK'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"refCheck1\" value=\"Y\" $fldChecked></td>";DspErrMsg($Err_APR1CK);
	DspErrMsg($Err_APR1NM);
	DspErrMsg($Err_APR1PH);
	DspErrMsg($Err_APR1CK);
	print "\n </tr> ";

	$textOvr=SetTextOvr($Err_APR2NM);
	$textOvr=SetTextOvr($Err_APR2PH);
	$textOvr=SetTextOvr($Err_APR2CK);
	print "\n <tr><td class=\"inputalph\"><input type=\"text\"   name=\"refName2\" value=\"" . rtrim($row['APR2NM']) . "\" size=\"20\" maxlength=\"20\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"refPhone2\" value=\"" . rtrim($row['APR2PH']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	$fldChecked=Field_Checked($row['APR2CK'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"refCheck2\" value=\"Y\" $fldChecked></td>";DspErrMsg($Err_APR2CK);
	DspErrMsg($Err_APR2NM);
	DspErrMsg($Err_APR2PH);
	DspErrMsg($Err_APR2CK);
	print "\n </tr> ";

	$textOvr=SetTextOvr($Err_APR3NM);
	$textOvr=SetTextOvr($Err_APR3PH);
	$textOvr=SetTextOvr($Err_APR3CK);
	print "\n <tr><td class=\"inputalph\"><input type=\"text\"   name=\"refName3\" value=\"" . rtrim($row['APR3NM']) . "\" size=\"20\" maxlength=\"20\"></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"refPhone3\" value=\"" . rtrim($row['APR3PH']) . "\" size=\"10\" maxlength=\"10\"></td> ";
	$fldChecked=Field_Checked($row['APR3CK'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"refCheck3\" value=\"Y\" $fldChecked></td>";
	DspErrMsg($Err_APR3CK);
	DspErrMsg($Err_APR3PH);
	DspErrMsg($Err_APR3CK);
	print "\n </tr> ";

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Testing</legend> ";
	print "\n <table $contentTable>";
	print "\n <tr><td>&nbsp;</td><td class=\"colhdr\">Required</td><td class=\"colhdr\">Date</td><td class=\"colhdr\">Passed</td></tr> ";
	print "\n <tr><td class=\"dsphdr\">Drug Test</td>";
	$textOvr=SetTextOvr($Err_APDTRQ);
	$textOvr=SetTextOvr($Err_APDTDT);
	$textOvr=SetTextOvr($Err_APDTPA);
	$fldChecked=Field_Checked($row['APDTRQ'],"Y");
	print "\n  <td class=\"colcode\"><input type=\"checkbox\" name=\"drugTestReqd\" value=\"Y\" $fldChecked></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"drugTestDate\" value=\"" . rtrim($row['APDTDT']) . "\"  size=\"6\" maxlength=\"6\">";
	print "\n  <a href=\"javascript:calWindow('drugTestDate');\">$calendarImage</a></td>";
	$fldChecked=Field_Checked($row['APDTPA'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"drugTestPassd\" value=\"Y\" $fldChecked></td>";
	DspErrMsg($Err_APDTRQ);
	DspErrMsg($Err_APDTDT);
	DspErrMsg($Err_APDTPA);
	print "\n <tr><td class=\"dsphdr\">Employment Test</td>";
	$textOvr=SetTextOvr($Err_APETRQ);
	$textOvr=SetTextOvr($Err_APETDT);
	$textOvr=SetTextOvr($Err_APETPA);
	$fldChecked=Field_Checked($row['APETRQ'],"Y");
	print "\n  <td class=\"colcode\"><input type=\"checkbox\" name=\"emplTestReqd\" value=\"Y\" $fldChecked></td>";
	print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"emplTestDate\" value=\"" . rtrim($row['APETDT']) . "\"  size=\"6\" maxlength=\"6\">";
	print "\n  <a href=\"javascript:calWindow('emplTestDate');\">$calendarImage</a></td>";
	$fldChecked=Field_Checked($row['APETPA'],"Y");
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"emplTestPassd\" value=\"Y\" $fldChecked></td>";
	DspErrMsg($Err_APETRQ);
	DspErrMsg($Err_APETDT);
	DspErrMsg($Err_APETPA);
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Military</legend> ";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_APMILT);
	$fieldDesc=RetValue("ODCOMP=$fromCo and ODTYPE='V' and ODCODE='$row[APMILT]'", "PECODE", "ODDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Military Status</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"miltSts\" value=\"" . rtrim($row['APMILT']) . "\" size=\"2\" maxlength=\"4\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HRCodesSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=miltSts&amp;fldComp=$fromCo&amp;fldType=V&amp;fldDesc=miltStsDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"miltStsDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APMILT);
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Drivers License</legend> ";
	print "\n <table $contentTable>";
	Build_Fld_Entry("Number","drvLicNum","inputalph","","APDLNO",$row[APDLNO],$Err_APDLNO,"20","20","","","");
	Build_Fld_Entry("CDL Class/Endorsements","drvLicCdlClass","inputalph","","APDLCL",$row[APDLCL],$Err_APDLCL,"20","20","","","");
	$textOvr=SetTextOvr($Err_APDLST);
	$fieldDesc=RetValue("STID='$row[APDLST]'", "PRSTID", "STDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>State</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"drvLicState\" value=\"" . rtrim($row['APDLST']) . "\" size=\"1\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearchHR.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=drvLicState&amp;fldDesc=drvLicStateDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"drvLicStateDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_APDLST);

	Build_Fld_Entry("Expiration Date","drvLicExpDate","inputdate","Date",APDLED,$row[APDLED],$Err_APDLED,"","","","","");
	print "\n </table> ";
	print "\n </fieldset> ";


	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Eligibility</legend> ";
	print "\n <table $contentTable>";
	Build_Fld_Entry("Ever Convicted Of A Felony","everConvOfFelony","inputalph","YORN","APCFEL",$row[APCFEL],$Err_APCFEL,"1","1","","","");
	Build_Fld_Entry("If Under 18, Proof Of Eligibility","under18","inputalph","YORN","APUN18",$row[APUN18],$Err_APUN18,"1","1","","","");
	Build_Fld_Entry("Legally Eligible For Employment In The U.S.","legalEligForEmpInUS","inputalph","YORN","APLEFE",$row[APLEFE],$Err_APLEFE,"1","1","","","");
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
	if ($maintenanceCode=="D" && is_null($_POST['applComp'])) {
		$_POST['applComp']    =$fromCo;
		$_POST['applDate']    =$fromDate;
	}

	if ($maintenanceCode == "C" || $maintenanceCode == "D") {
		$_POST['applSeq']     =$fromSeq;
	} else {
		$_POST['applSeq']     =0;
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@comp", $_POST['applComp']);
	Concat_Field("@@apdt", $_POST['applDate']);
	Concat_Field("@@apsq", $_POST['applSeq']);

	Concat_Field("@@frco", $fromCo);
	Concat_Field("@@frdt", $fromDate);
	Concat_Field("@@frsq", $fromSeq);

	Concat_Field("@@tstp", $_POST['timeStamp']);
	Concat_Field("@@lnam", $_POST['lastName']);
	Concat_Field("@@fnam", $_POST['firstName']);
	Concat_Field("@@midi", $_POST['midInit']);
	Concat_Field("@@rnam", $_POST['reportName']);
	Concat_Field("@@adr1", $_POST['adrLine1']);
	Concat_Field("@@adr2", $_POST['adrLine2']);
	Concat_Field("@@city", $_POST['city']);
	$_POST['state']=strtoupper($_POST['state']);  Concat_Field("@@st@@", $_POST['state']);
	Concat_Field("@@zip@", $_POST['zip']);
	Concat_Field("@@phon", $_POST['phone']);
	Concat_Field("@@ssno", $_POST['ssn']);
	Concat_Field("@@etid", $_POST['ethnicID']);
	$_POST['gender']=strtoupper($_POST['gender']);  Concat_Field("@@gndr", $_POST['gender']);
	Concat_Field("@@jbcd", $_POST['jobCode']);
	Concat_Field("@@pstn", $_POST['position']);
	Concat_Field("@@eeo@", $_POST['jobCateg']);
	Concat_Field("@@empl", $_POST['emplHRNum']);
	$_POST['location']=strtoupper($_POST['location']);  Concat_Field("@@lctn", $_POST['location']);
	$_POST['stsCode']=strtoupper($_POST['stsCode']);  Concat_Field("@@stts", $_POST['stsCode']);
	Concat_Field("@@skil", $_POST['skills']);
	Concat_Field("@@cmnt", $_POST['comments']);
	Concat_Field("@@cer1", $_POST['cert1']);
	Concat_Field("@@cer2", $_POST['cert2']);
	Concat_Field("@@cer3", $_POST['cert3']);
	if (!isset($_POST['bonded'])) {$_POST['bonded']="N";}
	Concat_Field("@@bond", $_POST['bonded']=strtoupper($_POST['bonded']));
	Concat_Field("@@grad", $_POST['yearGrad']);
	$_POST['degree']=strtoupper($_POST['degree']);  Concat_Field("@@dgre", $_POST['degree']);
	Concat_Field("@@subj", $_POST['subjMajor']);
	Concat_Field("@@schl", $_POST['school']);
	Concat_Field("@@slmn", $_POST['salaryMin']);
	Concat_Field("@@slmx", $_POST['salaryMax']);
	Concat_Field("@@ivdt", $_POST['intvwDate']);
	Concat_Field("@@ivr1", $_POST['intvwRemarks1']);
	Concat_Field("@@ivr2", $_POST['intvwRemarks2']);
	Concat_Field("@@nivd", $_POST['nextIntvwDate']);
	Concat_Field("@@avld", $_POST['availDate']);
	$_POST['hireSource']=strtoupper($_POST['hireSource']);  Concat_Field("@@hire", $_POST['hireSource']);
	if (!isset($_POST['apOnFile'])) {$_POST['apOnFile']="N";}
	Concat_Field("@@aonf", $_POST['apOnFile']=strtoupper($_POST['apOnFile']));
	Concat_Field("@@p1co", $_POST['prvPosCoName1']);
	Concat_Field("@@p1pn", $_POST['prvPosPosition1']);
	Concat_Field("@@p1sd", $_POST['prvPosStrtDate1']);
	Concat_Field("@@p1ed", $_POST['prvPosEndDate1']);
	Concat_Field("@@p2co", $_POST['prvPosCoName2']);
	Concat_Field("@@p2pn", $_POST['prvPosPosition2']);
	Concat_Field("@@p2sd", $_POST['prvPosStrtDate2']);
	Concat_Field("@@p2ed", $_POST['prvPosEndDate2']);
	Concat_Field("@@p3co", $_POST['prvPosCoName3']);
	Concat_Field("@@p3pn", $_POST['prvPosPosition3']);
	Concat_Field("@@p3sd", $_POST['prvPosStrtDate3']);
	Concat_Field("@@p3ed", $_POST['prvPosEndDate3']);
	Concat_Field("@@r1nm", $_POST['refName1']);
	Concat_Field("@@r1ph", $_POST['refPhone1']);
	if (!isset($_POST['refCheck1'])) {$_POST['refCheck1']="N";}
	Concat_Field("@@r1ck", $_POST['refCheck1']=strtoupper($_POST['refCheck1']));
	Concat_Field("@@r2nm", $_POST['refName2']);
	Concat_Field("@@r2ph", $_POST['refPhone2']);
	if (!isset($_POST['refCheck2'])) {$_POST['refCheck2']="N";}
	Concat_Field("@@r2ck", $_POST['refCheck2']=strtoupper($_POST['refCheck2']));
	Concat_Field("@@r3nm", $_POST['refName3']);
	Concat_Field("@@r3ph", $_POST['refPhone3']);
	if (!isset($_POST['refCheck3'])) {$_POST['refCheck3']="N";}
	Concat_Field("@@r3ck", $_POST['refCheck3']=strtoupper($_POST['refCheck3']));
	if (!isset($_POST['drugTestReqd'])) {$_POST['drugTestReqd']="N";}
	Concat_Field("@@dtrq", $_POST['drugTestReqd']=strtoupper($_POST['drugTestReqd']));
	Concat_Field("@@dtdt", $_POST['drugTestDate']);
	if (!isset($_POST['drugTestPassd'])) {$_POST['drugTestPassd']="N";}
	Concat_Field("@@dtpa", $_POST['drugTestPassd']=strtoupper($_POST['drugTestPassd']));
	if (!isset($_POST['emplTestReqd'])) {$_POST['emplTestReqd']="N";}
	Concat_Field("@@etrq", $_POST['emplTestReqd']=strtoupper($_POST['emplTestReqd']));
	Concat_Field("@@etdt", $_POST['emplTestDate']);
	if (!isset($_POST['emplTestPassd'])) {$_POST['emplTestPassd']="N";}
	Concat_Field("@@etpa", $_POST['emplTestPassd']=strtoupper($_POST['emplTestPassd']));
	$_POST['miltSts']=strtoupper($_POST['miltSts']);  Concat_Field("@@milt", $_POST['miltSts']);
	Concat_Field("@@dlno", $_POST['drvLicNum']);
	Concat_Field("@@dlcl", $_POST['drvLicCdlClass']);
	$_POST['drvLicState']=strtoupper($_POST['drvLicState']);  Concat_Field("@@dlst", $_POST['drvLicState']);
	Concat_Field("@@dled", $_POST['drvLicExpDate']);
	if (!isset($_POST['everConvOfFelony'])) {$_POST['everConvOfFelony']="N";}
	Concat_Field("@@cfel", $_POST['everConvOfFelony']=strtoupper($_POST['everConvOfFelony']));
	if (!isset($_POST['under18'])) {$_POST['under18']="N";}
	Concat_Field("@@un18", $_POST['under18']=strtoupper($_POST['under18']));
	if (!isset($_POST['legalEligForEmpInUS'])) {$_POST['legalEligForEmpInUS']="N";}
	Concat_Field("@@lefe", $_POST['legalEligForEmpInUS']=strtoupper($_POST['legalEligForEmpInUS']));
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HPEAPU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];
	$Err_APCOMP=DecatErr_Field("@@comp", "applComp");
	
	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "Co: [$_POST[applComp]] Date: [$_POST[applDate]] Seq: [$_POST[applSeq]]", "", "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif (($maintenanceCode == "D" || $maintenanceCode == "C") && $errFound != "" && $Err_APCOMP != "") {
		$confMessage=Format_ConfMsg_Desc("E", "Co: [$_POST[applComp]] Date: [$_POST[applDate]] Seq: [$_POST[applSeq]]<br>$Err_APCOMP", "", "", "", "", ""); 
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;applComp=" . urlencode(trim($_POST['applComp'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

