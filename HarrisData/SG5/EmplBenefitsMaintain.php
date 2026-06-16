<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$prCompany          = $_GET['prCompany'];
$prFacility         = $_GET['prFacility'];
$prEmployee         = $_GET['prEmployee'];
$hrCompany          = $_GET['hrCompany'];
$hrEmployee         = $_GET['hrEmployee'];
$dependentNbr       = $_GET['dependentNbr'];
$benefitCode        = $_GET['benefitCode'];
$planCode           = $_GET['planCode'];
$fromD2w            = $_GET['fromD2w'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Benefit Maintenance";
$scriptName     = "EmplBenefitsMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$employeeVarBase}&amp;dependentNbr=" . urlencode(trim($dependentNbr)) . "&amp;benefitCode=" . urlencode(trim($benefitCode)) . "&amp;planCode=" . urlencode(trim($planCode)) . "&amp;fromD2w=" . urlencode(trim($fromD2w));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPEECU_E";
$applicationID  = "PE";
$classcode      = "";
$classification = "";
$cobraBenefit   = "";
$CVDESC         = "";
$fsaMaint       = "";
$updateError    = "";
$updsd          = "";
$BenGrp         = "";
$dataType       = "SSNO";
$modeD          = "D";
$modeI          = "I";
if ($fromD2w != "")	{$backURL="{$homeURL}{$cGIPath}{$fromD2w}/REPORT{$altVarBase}{$employeeVarBase}&amp;dependentNbr=" . urlencode(trim($dependentNbr)) . "&amp;benefitCode=" . urlencode(trim($benefitCode)) . "&amp;planCode=" . urlencode(trim($planCode)) . "\"";}
else                {$backURL="{$homeURL}{$cGIPath}EmplBenefits.d2w/REPORT{$altVarBase}{$employeeVarBase}";}
require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}
$userPass=EmployeeUserView($profileHandle,$dataBaseID,$applicationID,$userPass,$prCompany,$prFacility,$prEmployee,$hrCompany,$hrEmployee);
if ($userPass == "N") {
	require_once 'UserViewErrorInclude.php';
	exit;
}
if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'CalendarInclude.php';
	require_once 'DateEdit.php';
	require_once 'UpperCase.php';

	print "\n function chkNumber(fld) {";
	print "\n if (fld.value != '' && fld.value != parseInt(fld.value)) {";
	print "\n alert('Field must be numeric ' + fld.value) ";
	print "\n }";
	print "\n }";

	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.dateEffective.value ==\"\" || ";
	print "\n     document.Chg.planHolderSSN.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editdate(document.Chg.dateEffective) && ";
	print "\n     editdate(document.Chg.enrollmentDate) && ";
	print "\n     editdate(document.Chg.vestingDate) && ";
	print "\n     editNum(document.Chg.amount, 7, 0) && ";
	print "\n     editNum(document.Chg.numberOfUnits, 5, 0) && ";
	if ($dependentNbr == 0000000) {
		print "\n     editNum(document.Chg.deductionAmount, 5, 2) && ";
		print "\n     editNum(document.Chg.deductionPercent, 2, 3) && ";
	}
	print "\n     editNum(document.Chg.planHolderSSN, 9, 0) && ";
	print "\n     editdate(document.Chg.certificateRecvDate) && ";
	print "\n     editNum(document.Chg.phoneNumber, 10, 0) && ";
	print "\n     editNum(document.Chg.monthsOfCoverage, 3, 0) && ";
	print "\n     editdate(document.Chg.preexistingCondDate) && ";
	print "\n     editdate(document.Chg.coverageTermDate) && ";
	print "\n     editdate(document.Chg.certificateRequestedDate) && ";
	print "\n     editdate(document.Chg.certificateIssuedDate) && ";
	print "\n     editdate(document.Chg.electronicDocDate) && ";
	print "\n     editNum(document.Chg.premium, 5, 2)) ";
	print "\n return true;";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
?>
  var count = "1";
  var nextSEQ = "10";
   
    function addRow(tblName,benname,benssn,benpct,errorDesc) 
  {
    nextSEQ++; // add 1
    var tbody = document.getElementById(tblName).getElementsByTagName("TBODY")[0];
    // create row
    var row = document.createElement("TR");
    // ??????  row.setAttribute('id',colname);  removed by lwm - halted otherwise
               
    var td0 = document.createElement("TD")
    td0.setAttribute('class','inputalph');
    td0.setAttribute('className','inputalph');  // For IE
    var strHtml0 = "<INPUT TYPE=\"text\" NAME=\"nm" + nextSEQ + "\" VALUE=\"" + benname + "\" SIZE=\"20\" MAXLENGTH=\"20\">";	
    td0.innerHTML = strHtml0.replace(/!count!/g,count);
   
    var td1 = document.createElement("TD")
    td1.setAttribute('class','inputnmbr');
    td1.setAttribute('className','inputnmbr');  // For IE
    var strHtml1 = "<INPUT TYPE=\"text\" onkeyup=\"chkNumber(this)\" NAME=\"ss" + nextSEQ + "\" VALUE=\"" + benssn + "\" SIZE=\"9\" MAXLENGTH=\"9\">";
    td1.innerHTML = strHtml1.replace(/!count!/g,count);
    
    var td2 = document.createElement("TD")
    td2.setAttribute('class','inputnmbr');
    td2.setAttribute('className','inputnmbr');  // For IE
    var strHtml2 = "<INPUT TYPE=\"text\" onkeyup=\"chkNumber(this)\" NAME=\"pc" + nextSEQ + "\" VALUE=\"" + benpct + "\" SIZE=\"3\" MAXLENGTH=\"3\">";
    td2.innerHTML = strHtml2.replace(/!count!/g,count);
    
	var td3 = document.createElement("TD")
    td3.setAttribute('class','error');
    td3.setAttribute('className','error');  // For IE
    if (errorDesc) {var strHtml3 = errorDesc;}
    else {var strHtml3 = "&nbsp;"}
    td3.innerHTML = strHtml3.replace(/!count!/g,count);
       
    
    // append data to row
    row.appendChild(td0);
    row.appendChild(td1);
    row.appendChild(td2);
    row.appendChild(td3);   
    // add to count variable
    count = parseInt(count) + 1;
    // append row to table
    tbody.appendChild(row);
  }
<?php

print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "BENEFITSMAINTAIN";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
$stmtSQL= "";
$beneficiaries="";

if ($maintenanceCode == "A") {
	require_once 'AddRecordSQL.php';
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select * ";
	$stmtSQL .= " From CBCOVR ";
	$stmtSQL .= " Where CVCOMP=$prCompany and CVFACL=$prFacility and CVCOVR='$benefitCode' and CVPLAN='$planCode' ";
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);
	$beneficiaries=trim($row['CVEBEN']);
} else {
	if ($errFound == "") {
		require 'stmtSQLClear.php';
		$stmtSQL .= " Delete From HRBNWK Where BNXHND='$profileHandle' ";
		$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

		require 'stmtSQLClear.php';
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From HREMCV left outer join HREBBN on ECCOMP=BNCOMP and ECFACL=BNFACL and ECEMPL=BNEMPL and ECSPDP=BNSPDP and ECCOVR=BNCOVR and ECPLAN=BNPLAN";
		$stmtSQL .= " Where ECCOMP=$prCompany and ECFACL=$prFacility and ECEMPL=$hrEmployee and ECSPDP=$dependentNbr and ECCOVR='$benefitCode' and ECPLAN='$planCode' ";
		require 'stmtSQLEnd.php';
		$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
		$row = db2_fetch_assoc($sqlResult);

		require 'stmtSQLClear.php';
		while ($row = db2_fetch_assoc($sqlResult, $startRow)){
			if ($stmtSQL == "") {$stmtSQL .= " Insert Into HRBNWK (BNBNAM, BNBSSN, BNBPCT, BNCOMP, BNFACL, BNEMPL, BNSPDP, BNCOVR, BNPLAN, BNXHND, BN5RRN) Values ";}
			else
			{$stmtSQL .= ",";}
			$stmtSQL .= " ('". trim($row[BNBNAM]) . "','" . trim($row[BNBSSN]) . "','" . trim($row[BNBPCT]) . "',$prCompany,$prFacility,$hrEmployee,$dependentNbr,'$benefitCode','$planCode','$profileHandle',$startRow) ";
			$startRow ++;
		}
		if ($stmtSQL) {$status = db2_exec($i5Connect->getConnection (), $stmtSQL);}
		require 'stmtSQLClear.php';
	}
	$stmtSQL .= " Select * ";
	$stmtSQL .= " From HREMCV inner join CBCOVR on ECCOMP=CVCOMP and ECFACL=CVFACL and ECCOVR=CVCOVR and ECPLAN=CVPLAN";
	$stmtSQL .= "             inner join CBCODE on CVPLAN=CCCODE";
	$stmtSQL .= " Where ECCOMP=$prCompany and ECFACL=$prFacility and ECEMPL=$hrEmployee and ECSPDP=$dependentNbr and ECCOVR='$benefitCode' and ECPLAN='$planCode' ";
	require 'stmtSQLEnd.php';
}

// Program Option Security
$sec_01="Y";
$sec_02="Y";
$sec_03="Y";
$sec_04="N";
print "\n <a NAME=\"top\"></a> ";
require_once 'MaintainTop.php';

if ($dependentNbr == 0000000) {
	$termHD="H";
	$F_Name=Ret_Format_EmplName($prCompany,$prFacility,$prEmployee,$hrCompany,$hrEmployee,$termHD);
} else {
	$F_Name=RetDepNam($profileHandle, $dataBaseID, $dependentNbr);
}
print "\n <div><h2>$F_Name</h2></div>";

$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

if ($maintenanceCode == "C") {
	$beneficiaries=$row['CVEBEN'];
}

print $hrTagAttr;
print "\n <table $quickLinkTable> ";
print "\n   <tr> ";
print "\n     <td class=\"quickLinkTabs\"><a href=\"#general\">General</a></td> ";
print "\n     <td class=\"quickLinkTabs\"><a href=\"#certificate\">Certificate</a></td> ";
print "\n     <td class=\"quickLinkTabs\"><a href=\"#terminate\">Terminate</a></td> ";
if ($beneficiaries=="Y") {
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#beneficiaries\">Beneficiaries</a></td> ";
}
print "\n   </tr> ";
print "\n </table> ";

print $hrTagAttr;
require_once 'RequiredField.php';
require_once 'ErrorDisplay.php';

if ($errFound != "" || $maintenanceCode=="A") {
	if ($errFound == "" && $maintenanceCode=="A") {
		$edtVar= "";
		$focusField= "eligibilityDate";
	} else {
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$Err_ECCOVR=DecatErr_Field("@@covr", "benefitCode");
		$Err_ECPLAN=DecatErr_Field("@@plan", "planCode");
		$Err_ECELIG=DecatErr_Field("@@elig", "eligibilityDate");
		$Err_ECELEC=DecatErr_Field("@@elec", "enrollmentDate");
		$Err_ECDCE= DecatErr_Field("@@dce@", "dateEffective");
		$Err_ECVEST=DecatErr_Field("@@vest", "vestingDate");
		$Err_ECAMT= DecatErr_Field("@@amt@", "amount");
		$Err_ECUNIT=DecatErr_Field("@@unit", "numberOfUnits");
		$Err_ECDEDA=DecatErr_Field("@@deda", "deductionAmount");
		$Err_ECDEDP=DecatErr_Field("@@dedp", "deductionPercent");
		$Err_ECPHC= DecatErr_Field("@@phc@", "planHolder");
		$Err_ECPHSS=DecatErr_Field("@@phss", "planHolderSSN");
		$Err_ECID=  DecatErr_Field("@@id@@", "identificationNbr");
		$Err_EDOC=  DecatErr_Field("@@edoc", "electronicDocDate");
		$Err_ECCERT=DecatErr_Field("@@cert", "certificateRecvDate");
		$Err_ECCISU=DecatErr_Field("@@cisu", "certificateIssuer");
		$Err_ECPHON=DecatErr_Field("@@phon", "phoneNumber");
		$Err_ECMNTH=DecatErr_Field("@@mnth", "monthsOfCoverage");
		$Err_ECPREC=DecatErr_Field("@@prec", "preexistingCondDate");
		$Err_ECDCT= DecatErr_Field("@@dct@", "coverageTermDate");
		$Err_ECTRCR=DecatErr_Field("@@trcr", "certificateRequestedDate");
		$Err_ECTRCI=DecatErr_Field("@@trci", "certificateIssuedDate");
		$Err_ECACKN=DecatErr_Field("@@ackn", "employeeReceipt");
		$Err_ECCARR=DecatErr_Field("@@carr", "carrierNotified");
		$Err_ECPREM=DecatErr_Field("@@prem", "premium");
		$Err_AUTD  =DecatErr_Field("@@autd", "updateDependents");
		$Err_EDED  =DecatErr_Field("@@eded", "updateDeduction");
		$totalPct  =DecatErr_Field("@@tpct", "totalPct");
		$Err_TSTP=DecatErr_Field("@@tstp", "timeStamp");
	}
	$row['ECCOVR']=Decat_Field("@@covr", $edtVar);
	$row['ECPLAN']=Decat_Field("@@plan", $edtVar);
	$row['ECELIG']=Decat_Field("@@elig", $edtVar);
	$row['ECELEC']=Decat_Field("@@elec", $edtVar);
	$row['ECDCE']=Decat_Field("@@dce@", $edtVar);
	$row['ECVEST']=Decat_Field("@@vest", $edtVar);
	$row['ECAMT']=Decat_Field("@@amt@", $edtVar);
	$row['ECUNIT']=Decat_Field("@@unit", $edtVar);
	$row['ECDEDA']=Decat_Field("@@deda", $edtVar);
	$row['ECDEDP']=Decat_Field("@@dedp", $edtVar);
	$row['ECPHC']=Decat_Field("@@phc@", $edtVar);
	$row['ECPHSS']=Decat_Field("@@phss", $edtVar);
	$row['ECPHSS']=RetColValue("$profileHandle", "$dataBaseID", "ECCOMP=$prCompany and ECFACL=$prFacility and ECEMPL=$hrEmployee and ECSPDP=$dependentNbr and ECCOVR='$benefitCode' and ECPLAN='$planCode'", "HREMCV", "ECPHSS", "D");
	$row['ECID']=Decat_Field("@@id@@", $edtVar);
	$row['ECEDOC']=Decat_Field("@@edoc", $edtVar);
	$row['ECCERT']=Decat_Field("@@cert", $edtVar);
	$row['ECCISU']=Decat_Field("@@cisu", $edtVar);
	$row['ECPHON']=Decat_Field("@@phon", $edtVar);
	$row['ECMNTH']=Decat_Field("@@mnth", $edtVar);
	$row['ECPREC']=Decat_Field("@@prec", $edtVar);
	$row['ECDCT']=Decat_Field("@@dct@", $edtVar);
	$row['ECTRCR']=Decat_Field("@@trcr", $edtVar);
	$row['ECTRCI']=Decat_Field("@@trci", $edtVar);
	$row['ECACKN']=Decat_Field("@@ackn", $edtVar);
	$row['ECCARR']=Decat_Field("@@carr", $edtVar);
	$row['ECPREM']=Decat_Field("@@prem", $edtVar);
	$updateDependents=Decat_Field("@@autd", $edtVar);
	$updateDeduction=Decat_Field("@@eded", $edtVar);

	$errFound= "";
	if ($updateDependents == "") {$updateDependents="N";}
	if ($updateDeduction == "") {$updateDeduction="N";}
	$row['ECTSTP']=Decat_Field("@@tstp", $edtVar);

	if ($errFound == "" && $maintenanceCode=="A") {
		$row['ECCOVR']=$benefitCode;
		$row['ECPLAN']=$planCode;
		$planHolderSSN=RetColValue("$profileHandle", "$dataBaseID", "ECCOMP=$prCompany and ECFACL=$prFacility and ECMEMPL=$hrEmployee", "HREMCV", "ECPHSS", "D");
		$row['ECPHSS']=$planHolderSSN;
	}
} else {
	$row[ECDEDP]=100*($row[ECDEDP]);
	$focusField= "eligibilityDate";
	$row[ECELIG]=DateInputFromCYMD($row[ECELIG]);
	$row[ECELEC]=DateInputFromCYMD($row[ECELEC]);
	$row[ECDCE]=DateInputFromCYMD($row[ECDCE]);
	$row[ECVEST]=DateInputFromCYMD($row[ECVEST]);
	$row[ECCERT]=DateInputFromCYMD($row[ECCERT]);
	$row[ECPREC]=DateInputFromCYMD($row[ECPREC]);
	$row[ECDCT]=DateInputFromCYMD($row[ECDCT]);
	$row[ECTRCR]=DateInputFromCYMD($row[ECTRCR]);
	$row[ECTRCI]=DateInputFromCYMD($row[ECTRCI]);
	$row[ECEDOC]=DateInputFromCYMD($row[ECEDOC]);
	$row[ECPHSS]=RetColValue("$profileHandle", "$dataBaseID", "ECCOMP=$prCompany and ECFACL=$prFacility and ECEMPL=$hrEmployee and ECSPDP=$dependentNbr and ECCOVR='$benefitCode' and ECPLAN='$planCode'", "HREMCV", "ECPHSS", "D");
}

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
print "\n <a name=\"general\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">General</legend> ";
require 'TopOfForm.php';
print "\n <table $contentTable>";
$textOvr=SetTextOvr($Err_TSTP);
print "\n <tr><td class=\"dsphdr\"><span $textOvr>.</span></td>";
print "\n     <td><input type=\"hidden\" name=\"timeStamp\" value=\"" . rtrim($row['ECTSTP']) . "\"></td> ";
print "\n </tr> ";
DspErrMsg($Err_TSTP);
$textOvr=SetTextOvr($Err_ECCOVR);
$cvdesc=RetValue("CVCOMP='$prCompany' and CVFACL='$prFacility' and CVCOVR='$benefitCode' and CVPLAN='$planCode'", "CBCOVR", "CVDESC");
Build_DspFld("Benefit",$cvdesc,"","A");
DspErrMsg($Err_ECCOVR);

$ccdesc=RetValue("CCCODE='$planCode'", "CBCODE", "CCDESC");
Build_DspFld("Plan",$ccdesc,"","A");


Build_Fld_Entry("Eligibility Date","eligibilityDate","inputalph","Date","ECELIG",$row[ECELIG],$Err_ECELIG,"6","6","","","");
Build_Fld_Entry("Enrollment Date","enrollmentDate","inputalph","Date","ECELEC",$row[ECELEC],$Err_ECELEC,"6","6","","","");
Build_Fld_Entry("Date Effective","dateEffective","inputalph","Date","ECDCE",$row[ECDCE],$Err_ECDCE,"6","6","Y","","");
Build_Fld_Entry("Vesting Date","vestingDate","inputalph","Date","ECVEST",$row[ECVEST],$Err_ECVEST,"6","6","","","");
Build_Fld_Entry("Amount","amount","inputnmbr","","ECAMT",$row[ECAMT],$Err_ECAMT,"7","7","","","");

if ($dependentNbr == 0000000) {
	Build_Fld_Entry("Employee Deduction Amount","deductionAmount","inputnmbr","","ECDEDA",$row[ECDEDA],$Err_ECDEDA,"8","8","","","");
	Build_Fld_Entry("Employee Deduction Percent","deductionPercent","inputnmbr","","ECDEDP",$row[ECDEDP],$Err_ECDEDP,"6","6","","","");
}
if ($row['CVUNIT']>0) {
	Build_Fld_Entry("Number of Units","numberOfUnits","inputnmbr","","ECUNIT",$row['ECUNIT'],$Err_ECUNIT,"7","5","","","");
} else {
	print "\n <tr><td class=\"inputalph\"><input type=\"hidden\" name=\"numberOfUnits\" value=\"00000\" size=\"7\" maxlength=\"5\"></td></tr>";
}
Build_Fld_Entry("Plan Holder SS#","planHolderSSN","inputnmbr","","ECPHSS",$row[ECPHSS],$Err_ECPHSS,"9","9","Y","","");

$fieldDesc=RetValue("FLTYPE='PLANHOLDER' and FLVALU='{$row['ECPHC']}'", "SYFLAG", "FLDESC");
$textOvr=SetTextOvr($Err_ECPHC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Plan Holder</span></td>";
	print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"planHolder\" value=\"{$row['ECPHC']}\" size=\"1\" maxlength=\"1\">";
	print "\n     <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=PLANHOLDER&amp;flagSrchHdr=". urlencode('Plan Holder') . "&amp;fldName=planHolder&amp;fldDesc=planHolderDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"planHolderDesc\">$fieldDesc</span>";		
	print "\n </td>";
	print "\n </tr> ";
DspErrMsg($Err_ECPHC);

Build_Fld_Entry("Identification Number","identificationNbr","inputnmbr","","ECID",$row[ECID],$Err_ECID,"12","10","","","");
Build_Fld_Entry("Electronic Document Distribution Authorization","electronicDocDate","inputnmbr","Date","ECEDOC",$row[ECEDOC],$Err_ECEDOC,"6","6","","","");

if ($dependentNbr > 0000000 || $row['CVAUTD'] == "N") {
	print "\n <tr><td class=\"inputalph\"><input type=\"hidden\" name=\"updateDependents\" value=\"N\" size=\"1\" maxlength=\"1\"></td></tr>";
} else {
	if ($row['CVTYPE'] <> "FSA ") {
		$updateDependents="Y";
	}
	// Not sure about the use of CVAUTD as fldId below ??
	Build_Fld_Entry("Update Dependent Rows","updateDependents","inputalph","YORN","CVAUTD",$updateDependents,$Err_AUTD,"1","1","","","");
}
// Not sure about the use of CVDEDN as fldId below ??
if ($row['CVDEDN'] > 0 && $dependentNbr == 0000000) {
	Build_Fld_Entry("Update Employee Deduction","updateDeduction","inputalph","YORN","CVDEDN",$updateDeduction,$Err_EDED,"1","1","","","");
} else {
	print "\n <tr><td class=\"inputalph\"><input type=\"hidden\" name=\"updateDeduction\" value=\"N\" size=\"1\" maxlength=\"1\"></td></tr>";
}

print "\n </table> ";
print "\n </fieldset> ";

print "\n <a name=\"certificate\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Certificate Receipt</legend> ";
require 'TopOfForm.php';
print "\n <table $contentTable>";
Build_Fld_Entry("Date Received","certificateRecvDate","inputalph","Date","ECCERT",$row[ECCERT],$Err_ECCERT,"6","6","","","");
Build_Fld_Entry("Issuer","certificateIssuer","inputalph","","ECCISU",$row[ECCISU],$Err_ECCISU,"15","30","","","");
Build_Fld_Entry("Phone Number Of Issuer","phoneNumber","inputnmbr","","ECPHON",$row[ECPHON],$Err_ECPHON,"10","10","","","");
Build_Fld_Entry("Previous Months Of Coverage","monthsOfCoverage","inputnmbr","","ECMNTH",$row[ECMNTH],$Err_ECMNTH,"3","3","","","");
Build_Fld_Entry("Preexisting Condition Expiration Date","preexistingCondDate","inputalph","Date","ECPREC",$row[ECPREC],$Err_ECECPREC,"6","6","","","");
print "\n </table> ";
print "\n </fieldset> ";

print "\n <a name=\"terminate\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Terminate Benefit</legend> ";
require 'TopOfForm.php';
print "\n <table $contentTable>";
if ($row['ECDCT'] == 0) {$row[ECDCT]=" ";}

Build_Fld_Entry("Coverage Termination Date","coverageTermDate","inputalph","Date","ECDCT",$row[ECDCT],$Err_ECDCT,"6","6","","","");

Build_Fld_Entry("Date Certificate Requested","certificateRequestedDate","inputalph","Date","ECTRCR",$row[ECTRCR],$Err_ECTRCR,"6","6","","","");
Build_Fld_Entry("Date Certificate Issued","certificateIssuedDate","inputalph","Date","ECTRCI",$row[ECTRCI],$Err_ECTRCI,"6","6","","","");
Build_Fld_Entry("Employee Receipt","employeeReceipt","inputalph","YORN","ECACKN",$row[ECACKN],$Err_ECACKN,"1","1","","","");
Build_Fld_Entry("Provider Notified","carrierNotified","inputalph","YORN","ECCARR",$row[ECCARR],$Err_ECCARR,"1","1","","","");
Build_Fld_Entry("COBRA Premium Override","premium","inputnmbr","","ECPREM",$row[ECPREM],$Err_ECPREM,"7","7","","","");
$classcode=$row[ECCLAS];

print "\n </table> ";
print "\n </fieldset> ";

if ($beneficiaries=="Y") {
	print "\n <a name=\"beneficiaries\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Benefit Beneficiaries</legend> ";
	print "\n <div class=\"quickLinksTop\"><a href=\"javascript:addRow('selTable','','','')\">$addMoreImage</a><a href=\"#top\">$topOfFormImage</a></div> ";
	print "\n <table id=\"selTable\" $contentTable>";
	print "<tr>";
	print "\n <th class=\"colhdr\">Name</th>";
	print "\n <th class=\"colhdr\">Soc Sec No</th>";
	print "\n <th class=\"colhdr\">Percent</th>";
	print "\n </tr>";

	require 'stmtSQLClear.php';
	$stmtSQL .= " Select * ";
	$stmtSQL .= " From HRBNWK";
	$stmtSQL .= " Where BNXHND='$profileHandle'";
	require 'stmtSQLEnd.php';

	$startRow = 1;

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	$row = db2_fetch_assoc($sqlResult);

	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($row) {
			$errDesc = "";
			if ($row[BNERR] <> "") {
				$errDesc=RetValue("ERER#='$row[BNERR]'", "HDERROR", "ERERDS");
			} else {
				$errDesc = " ";
			}

			print "\n <script TYPE=\"text/javascript\">";
			$row[BNBSSN]=RetColValue("$profileHandle", "$dataBaseID", "BNCOMP=$prCompany and BNFACL=$prFacility and BNEMPL=$hrEmployee and BNSPDP=$dependentNbr and BNCOVR='$benefitCode' and BNPLAN='$planCode' and BNXHND='$profileHandle' and BN5RRN=$startRow", "HRBNWK", "BNBSSN", "D");
			print "\n addRow('selTable','". trim($row[BNBNAM]) . "','" . trim($row[BNBSSN]) . "','" . trim($row[BNBPCT]) . "','" . trim($errDesc) . "')";
			print "\n </script>";
		}
		$startRow ++;
	}


	if ($totalPct<>"") {
		print "<tr><td class=\"dsphdr\"><span $textOvr></span></td>";
		print "\n <td class=\"error\"><input type=\"hidden\" name=\"totalPct\" value=\"" . rtrim($totalPct) . "\" size=\"40\" maxlength=\"40\">$totalPct</td>";
		print "\n </tr> ";}

		//print "<tr><td><INPUT TYPE=\"hidden\" ID=\"srtCol\" NAME=\"srtCol\"></td></tr>"; do i need this ??

		print "\n </table> ";
		print "\n </fieldset> ";
}

if ($focusField == "benefitCode" || $focusField == "planCode") {$focusField = "eligibilityDate";}

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

	$edtVar= "";
	for ($i=11; $i<=99; $i++) {
		Concat_Field("@@nm$i", $_POST['nm'.$i]);
		Concat_Field("@@ss$i", $_POST['ss'.$i]);
		Concat_Field("@@pc$i", $_POST['pc'.$i]);
	}

	$edtVar .= "}{";

	$wrnVar=$edtVar;

	//%INCLUDE "setLibraryList.icl"  // needed ???
	if ($dependentNbr == 0000000) {
		$socSecNo=RetColValue($profileHandle, $dataBaseID, "EMCOMP='$prCompany' and EMFACL='$prFacility' and EMEMPL='$prEmployee'", "HREMPL", "EMSSNO", "D");
	} else {
		$socSecNo=RetColValue($profileHandle, $dataBaseID, "SDCOMP='$prCompany' and SDFACL='$prFacility' and SDEMPL='$hrEmployee' and SDSPDP='$dependentNbr'", "HRSPDP", "SDSSN", "D");
	}

	if ($maintenanceCode=="D" && is_null($_POST['benefitCode'])) {
		$_POST['benefitCode']=$benefitCode;
		$_POST['planCode']=$planCode;
	}

	if ($maintenanceCode=="D"  && !isset($_POST['updateDependents'])) {
		require 'stmtSQLClear.php';
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From CBCOVR ";
		$stmtSQL .= " Where CVCOMP=$prCompany and CVFACL=$prFacility and CVCOVR='$benefitCode' and CVPLAN='$planCode' ";
		require 'stmtSQLEnd.php';
		$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
		$row = db2_fetch_assoc($sqlResult);
		if ($dependentNbr == 0000000 && $row['CVAUTD'] == "Y")
		{$_POST['updateDependents']="Y";}
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}
	$editCovrPlan= "N";
	$edtVar= "";
	Concat_Field("@@comp", $prCompany);
	Concat_Field("@@facl", $prFacility);
	Concat_Field("@@pecp", $hrCompany);
	Concat_Field("@@empl", $hrEmployee);
	Concat_Field("@@spdp", $dependentNbr);
	Concat_Field("@@tstp", $_POST['timeStamp']);
	Concat_Field("@@ssn@", $socSecNo);
	Concat_Field("@@covr", $benefitCode);
	Concat_Field("@@plan", $planCode);
	Concat_Field("@@clas", $classcode);
	Concat_Field("@@elig", $_POST['eligibilityDate']);
	Concat_Field("@@elec", $_POST['enrollmentDate']);
	Concat_Field("@@dce@", $_POST['dateEffective']);
	Concat_Field("@@vest", $_POST['vestingDate']);
	Concat_Field("@@amt@", $_POST['amount']);
	Concat_Field("@@unit", $_POST['numberOfUnits']);
	if (!isset($_POST['deductionAmount'])) {$_POST['deductionAmount']=0;}
	Concat_Field("@@deda", $_POST['deductionAmount']);
	if (!isset($_POST['deductionPercent'])) {$_POST['deductionPercent']=0;}
	Concat_Field("@@dedp", $_POST['deductionPercent']);
	Concat_Field("@@phc@", $_POST['planHolder']=strtoupper($_POST['planHolder']));
	Concat_Field("@@phss", $_POST['planHolderSSN']);
	Concat_Field("@@id@@", $_POST['identificationNbr']);
	Concat_Field("@@edoc", $_POST['electronicDocDate']);
	Concat_Field("@@cert", $_POST['certificateRecvDate']);
	Concat_Field("@@cisu", $_POST['certificateIssuer']);
	Concat_Field("@@phon", $_POST['phoneNumber']);
	Concat_Field("@@mnth", $_POST['monthsOfCoverage']);
	Concat_Field("@@prec", $_POST['preexistingCondDate']);
	Concat_Field("@@dct@", $_POST['coverageTermDate']);
	Concat_Field("@@trcr", $_POST['certificateRequestedDate']);
	Concat_Field("@@trci", $_POST['certificateIssuedDate']);
	if (!isset($_POST['employeeReceipt'])) {$_POST['employeeReceipt']="N";}
	Concat_Field("@@ackn", $_POST['employeeReceipt']=strtoupper($_POST['employeeReceipt']));
	if (!isset($_POST['carrierNotified'])) {$_POST['carrierNotified']="N";}
	Concat_Field("@@carr", $_POST['carrierNotified']=strtoupper($_POST['carrierNotified']));
	Concat_Field("@@prem", $_POST['premium']);
	if (!isset($_POST['updateDependents'])) {$_POST['updateDependents']="N";}
	Concat_Field("@@autd", $_POST['updateDependents']=strtoupper($_POST['updateDependents']));
	if (!isset($_POST['updateDeduction'])) {$_POST['updateDeduction']="N";}
	Concat_Field("@@eded", $_POST['updateDeduction']=strtoupper($_POST['updateDeduction']));

	Concat_Field("@@edcp", $editCovrPlan);

	$edtVar .= "}{";

	$returnValue=Maintain_Edit_Handle("HPEECU_W", $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$profileHandle  =$returnValue['profileHandle'];
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	$cvdesc=RetValue("CVCOMP='$prCompany' and CVFACL='$prFacility' and CVCOVR='$benefitCode' and CVPLAN='$planCode'", "CBCOVR", "CVDESC");
	$ccdesc=RetValue("CCCODE='$planCode'", "CBCODE", "CCDESC");
	$Err_ECCOVR=DecatErr_Field("@@covr", "benefitCode");
	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, $cvdesc, $benefitCode, "$ccdesc", "$planCode", "", "");
		if ($fromD2w != "" && $fromD2w != "EmplBenefitSelect.d2w") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$fromD2w}/REPORT{$altVarBase}{$employeeVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		} else {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}emplBenefits.d2w/REPORT{$altVarBase}{$employeeVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		}
	} elseif (($maintenanceCode == "D" || $maintenanceCode == "C") && $errFound != "" && $Err_ECCOVR != "") {
		$confMessage=Format_ConfMsg_Desc("E", $cvdesc, $benefitCode, "$ccdesc", "$planCode", "<br>$Err_ECCOVR", "");
		if ($fromD2w != "" && $fromD2w != "EmplBenefitSelect.d2w") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$fromD2w}/REPORT{$altVarBase}{$employeeVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		} else {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}emplBenefits.d2w/REPORT{$altVarBase}{$employeeVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		}
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;dependentNbr=" . urlencode(trim($dependentNbr)) . "&amp;benefitCode=" . urlencode(trim($benefitCode)) . "&amp;planCode=" . urlencode(trim($planCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}
?>