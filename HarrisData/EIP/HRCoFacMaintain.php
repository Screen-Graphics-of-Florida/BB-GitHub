<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$fromCo             = $_GET['fromCo'];
$fromFac            = $_GET['fromFac'];
$copyCo             = $_GET['fromCo'];
$copyFac            = $_GET['fromFac'];
$hrCo               = (isset($_GET['hrCo']))   ? $_GET['hrCo']   : null;

if (($maintenanceCode == "C" || $maintenanceCode == "Z") && ($fromFac==0)) {$hrCo='Y';}

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "H/R Company/Facility Maintenance";
$scriptName     = "HRCoFacMaintain.php";
$scriptVarBase  = "{$genericVarBase}{$hdListVarBase}&amp;fromCo=" . urlencode(trim($fromCo)) . "&amp;fromFac=" . urlencode(trim($fromFac));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HHRCOU_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=52";}

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

	if ($hrCo=='Y') {
		print "\n if (document.Chg.coName.value ==\"\" || ";
		print "\n     document.Chg.address1.value ==\"\" || ";
		print "\n     document.Chg.city.value ==\"\" || ";
		print "\n     document.Chg.state.value ==\"\" || ";
		print "\n     document.Chg.zipCode.value ==\"\")";
		print "\n {alert(\"$reqFieldError\"); return false;} ";
		print "\n if (editZero(document.Chg.coNum, 2, 0) && ";
		print "\n     editNum(document.Chg.facNum, 4, 0) && ";
		print "\n     editNum(document.Chg.phone, 10, 0) && ";
		print "\n     editNum(document.Chg.weeklyStdBudgetHrs, 2, 0)) ";
		print "\n return true;";
		print "\n }";

	} else {

		print "\n if (document.Chg.coName.value ==\"\" ";

		print "\n  || document.Chg.address1.value ==\"\" ";
		print "\n  || document.Chg.city.value ==\"\" ";
		print "\n  || document.Chg.state.value ==\"\" ";
		print "\n  || document.Chg.zipCode.value ==\"\" ";
		print "\n  || document.Chg.stmtCode.value ==\"\" ";

		print "\n  || document.Chg.timeEntryFormFormat.value ==\"\" ";
		print "\n  || document.Chg.prtOverFlowAdv.value ==\"\" ";
		print "\n  || document.Chg.prtCoFacNameAndAdrOnChk.value ==\"\" ";
		print "\n  || document.Chg.vacaValHrs.value ==\"\" ";
		print "\n  || document.Chg.vacaValEarnings.value ==\"\" ";
		print "\n  || document.Chg.vacaExpAsEarned.value ==\"\" ";
		print "\n  || document.Chg.vacaPrtAvailOnChks.value ==\"\" ";
		print "\n  || document.Chg.sickValHrs.value ==\"\" ";
		print "\n  || document.Chg.sickValEarnings.value ==\"\" ";
		print "\n  || document.Chg.sickExpAsEarned.value ==\"\" ";
		print "\n  || document.Chg.sickPrtAvailOnChks.value ==\"\" ";
		print "\n  || document.Chg.employeesNotPaidRpt.value ==\"\" ";
		print "\n  || document.Chg.grossHrsPayByDeptWorked.value ==\"\" ";
		print "\n  || document.Chg.grossHrsPayByHomeDept.value ==\"\" ";
		print "\n  || document.Chg.payDtlRpt.value ==\"\" ";
		print "\n  || document.Chg.expDistrByAcct.value ==\"\" ";
		print "\n  || document.Chg.expDistrByDept.value ==\"\" ";
		print "\n  || document.Chg.preTaxEdit.value ==\"\" ";
		print "\n  || document.Chg.taxComputationEdit.value ==\"\" ";
		print "\n  || document.Chg.postTaxDeductEdit.value ==\"\" ";
		print "\n  || document.Chg.four01kRpt.value ==\"\" ";
		print "\n  || document.Chg.vacaaccrualRpt.value ==\"\" ";
		print "\n  || document.Chg.sickaccrualRpt.value ==\"\" ";
		print "\n  || document.Chg.FSAContribAndPayments.value ==\"\" ";
		print "\n  || document.Chg.certifiedPayrollEdit.value ==\"\" ";
		print "\n  || document.Chg.dirDepositEdit.value ==\"\" ";
		print "\n  || document.Chg.payrollBalRpt.value ==\"\" ";
		print "\n  || document.Chg.payrollRegister.value ==\"\" ";
		print "\n  || document.Chg.hourlyDetOrSumHrsEntry.value ==\"\" ";
		print "\n  || document.Chg.hourlyElapsedHrsOrStartStop.value ==\"\" ";
		print "\n  || document.Chg.hourlySuppressDateWorked.value ==\"\" ";
		print "\n  || document.Chg.hourlyDateDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyPayCode.value ==\"\" ";
		print "\n  || document.Chg.hourlyShiftWorked.value ==\"\" ";
		print "\n  || document.Chg.hourlyDeptWorked.value ==\"\" ";
		print "\n  || document.Chg.hourlyJobNumb.value ==\"\" ";
		print "\n  || document.Chg.hourlyJobClass.value ==\"\" ";
		print "\n  || document.Chg.hourlyOverridePayRate.value ==\"\" ";
		print "\n  || document.Chg.hourlyOverrideGLAcct.value ==\"\" ";
		print "\n  || document.Chg.salaryDetOrSumHrsEntry.value ==\"\" ";
		print "\n  || document.Chg.salaryElapsedHrsOrStartStop.value ==\"\" ";
		print "\n  || document.Chg.salarySuppressDateWorked.value ==\"\" ";
		print "\n  || document.Chg.salaryDateDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryPayCode.value ==\"\" ";
		print "\n  || document.Chg.salaryShiftWorked.value ==\"\" ";
		print "\n  || document.Chg.salaryDeptWorked.value ==\"\" ";
		print "\n  || document.Chg.salaryJobNumb.value ==\"\" ";
		print "\n  || document.Chg.salaryJobClass.value ==\"\" ";
		print "\n  || document.Chg.salaryOverridePayRate.value ==\"\" ";
		print "\n  || document.Chg.salaryOverrideGLAcct.value ==\"\" ";
		print "\n  || document.Chg.hourlyRegPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyOvertimePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyDBLtimePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyVacationPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyHolidayPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlySickPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyOtherTaxablePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyNonTaxablePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyCommissionPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyBonusPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyFringePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.hourlyMinWageMakeupPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryRegPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryOvertimePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryDBLtimePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryVacationPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryHolidayPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salarySickPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryOtherTaxablePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryNonTaxablePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryCommissionPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryBonusPayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.salaryFringePayCodeDefault.value ==\"\" ";
		print "\n  || document.Chg.city.value ==\"\" ";
		print "\n  || document.Chg.stmtCode.value ==\"\" ";
		if (isset($_POST['tipPayroll']))	{
			print "\n  || document.Chg.hourlyCashTipsPayCodeDefault.value ==\"\" ";
			print "\n  || document.Chg.hourlyChargeTipsPayCodeDefault.value ==\"\" ";
			print "\n  || document.Chg.hourlyGrossReceiptsPayCodeDefault.value ==\"\" ";
			print "\n  || document.Chg.hourlyMealAllowPayCodeDefault.value ==\"\" ";
		}
		print "\n ) {alert(\"$reqFieldError\"); return false;} ";

		print "\n if (editZero(document.Chg.coNum, 2, 0) && ";
		print "\n     editZero(document.Chg.facNum, 4, 0) && ";
		print "\n     editNum(document.Chg.coHRNum, 2, 0) && ";
		print "\n     editNum(document.Chg.premPeriodStrDay, 2, 0) && ";
		print "\n     editNum(document.Chg.bank, 2, 0) && ";
		print "\n     editNum(document.Chg.phone, 10, 0) && ";
		print "\n     editNum(document.Chg.gracePeriodDays, 3, 0) && ";
		print "\n     editNum(document.Chg.ovrBegBatchNumb, 7, 0) && ";
		print "\n     editNum(document.Chg.tipCredPct, 2, 3) && ";
		print "\n     editNum(document.Chg.minWage, 3, 2) && ";
		print "\n     editNum(document.Chg.opportWage, 3, 2) && ";
		print "\n     editNum(document.Chg.emplrFICAOasdiTaxPct, 2, 3) && ";
		print "\n     editNum(document.Chg.emplrFICAHiTaxPct, 2, 3) && ";
		print "\n     editNum(document.Chg.empleeFICAOasdiTaxPct, 2, 3) && ";
		print "\n     editNum(document.Chg.empleeFICAHiTaxPct, 2, 3) && ";
		print "\n     editNum(document.Chg.FICAOasdiMaxTaxInc, 7, 2) && ";
		print "\n     editNum(document.Chg.FICAHiMaxTaxInc, 7, 2) && ";
		print "\n     editNum(document.Chg.FUICompPct, 2, 1) && ";
		print "\n     editNum(document.Chg.FUICompMaxWage, 5, 2) && ";
		print "\n     editNum(document.Chg.ovrTimeMult, 1, 2) && ";
		print "\n     editNum(document.Chg.dblTimeMult, 1, 2) && ";
		print "\n     editNum(document.Chg.interCoWageAcctsDueFromAcct, 4, 0) && ";
		print "\n     editNum(document.Chg.interCoWageAcctsDueFromSubAcct, 4, 0) && ";
		print "\n     editNum(document.Chg.interCoWageAcctsDueToAcct, 4, 0) && ";
		print "\n     editNum(document.Chg.interCoWageAcctsDueToSubAcct, 4, 0) && ";
		print "\n     editNum(document.Chg.interCoTaxAcctsDueFromAcct, 4, 0) && ";
		print "\n     editNum(document.Chg.interCoTaxAcctsDueFromSubAcct, 4, 0) && ";
		print "\n     editNum(document.Chg.interCoTaxAcctsDueToAcct, 4, 0) && ";
		print "\n     editNum(document.Chg.interCoTaxAcctsDueToSubAcct, 4, 0) && ";
		print "\n     editNum(document.Chg.overtimeAftNumbHrsPerDay, 3, 2) && ";
		print "\n     editNum(document.Chg.overtimeAftNumbHrsPerWeek, 3, 2) && ";
		print "\n     editNum(document.Chg.maxHourlyRate, 5, 2) && ";
		print "\n     editNum(document.Chg.maxGrossAmt, 7, 2) && ";
		print "\n     editNum(document.Chg.daysUntilDue, 3, 0)) ";
		print "\n return true;";
		print "\n }";
	}

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "HRCOFACMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL = " Select * From HRCOFC inner join HRCOFX on CFCOMP=CXCOMP and CFFACL=CXFACL Where CFCOMP='$fromCo' and CFFACL='$fromFac' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hhrcou_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hhrcou_OPT['sec_01'];
	$sec_02=$hhrcou_OPT['sec_02'];
	$sec_03=$hhrcou_OPT['sec_03'];
	$sec_04=$hhrcou_OPT['sec_04'];
	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	if ($hrCo!='Y') {
		print $hrTagAttr;
		print "\n <table $quickLinkTable> ";
		print "\n   <tr> ";
		print "\n     <td class=\"quickLinkTabs\"><a href=\"#common\">Common Data</a></td> ";
		print "\n     <td class=\"quickLinkTabs\"><a href=\"#cobra\">COBRA</a></td> ";
		print "\n     <td class=\"quickLinkTabs\"><a href=\"#payrollGeneral\">Payroll General</a></td> ";
		print "\n     <td class=\"quickLinkTabs\"><a href=\"#payrollIntCoAccts\"> Inter-Company Accounts</a></td> ";
		print "\n     <td class=\"quickLinkTabs\"><a href=\"#payrollVacationSick\">Vacation-Sick</a></td> ";
		print "\n   </tr> ";
		print "\n   <tr> ";
		print "\n     <td class=\"quickLinkTabs\"><a href=\"#payrollOvertime\">Overtime</a></td> ";
		print "\n     <td class=\"quickLinkTabs\"><a href=\"#payrollProcessingReportsPrintOptions\">Processing Reports<br>Print Options</a></td> ";
		print "\n     <td class=\"quickLinkTabs\"><a href=\"#payrollPayCodeDefaults\">Pay Code Defaults</a></td> ";
		print "\n     <td class=\"quickLinkTabs\"><a href=\"#payrollPayTransactionEntryDefaults\">Pay Transaction<br>Entry Defaults</a></td> ";
		print "\n   </tr> ";
		print "\n </table> ";}

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

				if ($hrCo=='Y') {
					$Err_CFCOMP=DecatErr_Field("@@comp", "coNum");
					$Err_CFFACL=DecatErr_Field("@@facl", "facNum");
					$Err_CFNAME=DecatErr_Field("@@name", "coName");
					$Err_CFADR1=DecatErr_Field("@@adr1", "address1");
					$Err_CFADR2=DecatErr_Field("@@adr2", "address2");
					$Err_CFCITY=DecatErr_Field("@@city", "city");
					$Err_CFSTID=DecatErr_Field("@@stid", "state");
					$Err_CFZIP =DecatErr_Field("@@zip@", "zipCode");
					$Err_CFPHON=DecatErr_Field("@@phon", "phone");
					$Err_CFSBH =DecatErr_Field("@@sbh@", "weeklyStdBudgetHrs");

				} else {

					$Err_CFCOMP=DecatErr_Field("@@comp", "coNum");
					$Err_CFFACL=DecatErr_Field("@@facl", "facNum");
					$Err_CFNAME=DecatErr_Field("@@name", "coName");
					$Err_CFADR1=DecatErr_Field("@@adr1", "address1");
					$Err_CFADR2=DecatErr_Field("@@adr2", "address2");
					$Err_CFCITY=DecatErr_Field("@@city", "city");
					$Err_CFSTID=DecatErr_Field("@@stid", "state");
					$Err_CFZIP =DecatErr_Field("@@zip@", "zipCode");
					$Err_CFPHON=DecatErr_Field("@@phon", "phone");
					$Err_CFHRXR=DecatErr_Field("@@hrxr", "coHRNum");

					$Err_CFSTCD=DecatErr_Field("@@stcd", "stmtCode");
					$Err_CFDAY =DecatErr_Field("@@day@", "premPeriodStrDay");
					$Err_CFCUT =DecatErr_Field("@@cut@", "cutDays");
					$Err_CFDUE =DecatErr_Field("@@due@", "daysUntilDue");
					$Err_CFGRAC=DecatErr_Field("@@grac", "gracePeriodDays");
					$Err_CFRDTH=DecatErr_Field("@@rdth", "retainDetTransHist");
					$Err_CFMGN =DecatErr_Field("@@mgn@", "manualGenNoticeDefault");
					$Err_CFMCN =DecatErr_Field("@@mcn@", "manualContNoticeDefault");
					$Err_CFMVN =DecatErr_Field("@@mvn@", "manualConvNoticeDefault");

					$Err_CFBKNO=DecatErr_Field("@@bkno", "bank");
					$Err_CFACCT=DecatErr_Field("@@acct", "ovrBankAcctNumb");
					$Err_CFBEGB=DecatErr_Field("@@begb", "ovrBegBatchNumb");
					$Err_CFCKRC=DecatErr_Field("@@ckrc", "chkRecon");
					$Err_CFPRTR=DecatErr_Field("@@prtr", "suppPayTransEntryRates");
					$Err_CFPRTS=DecatErr_Field("@@prts", "timeEntryFormFormat");
					$Err_CFHTCL=DecatErr_Field("@@htcl", "prtTimeCardLabels");
					$Err_CFFGL =DecatErr_Field("@@fgl@", "actvGLFeed");
					$Err_CFVGL =DecatErr_Field("@@vgl@", "valGLAccts");
					$Err_CFPF11=DecatErr_Field("@@pf11", "prtOverFlowAdv");
					$Err_CFP401=DecatErr_Field("@@p401", "prtEmplr401kOnChks");
					$Err_CFPF12=DecatErr_Field("@@pf12", "runResetAccrEachPer");
					$Err_CFCPR =DecatErr_Field("@@cpr@", "certPayrollHist");
					$Err_PCTIP =DecatErr_Field("@@tip@", "tipPayroll");
					$Err_CFTCP =DecatErr_Field("@@tcp@", "tipCredPct");
					$Err_CXMWR =DecatErr_Field("@@mwr@", "minWage");
					$Err_CXOPWG=DecatErr_Field("@@opwg", "opportWage");
					$Err_CFEIN =DecatErr_Field("@@ein@", "estIDNumb");

					$Err_CFRFCR=DecatErr_Field("@@rfcr", "emplrFICAOasdiTaxPct");
					$Err_CFRHIR=DecatErr_Field("@@rhir", "emplrFICAHiTaxPct");
					$Err_CFEFCR=DecatErr_Field("@@efcr", "empleeFICAOasdiTaxPct");
					$Err_CFEHIR=DecatErr_Field("@@ehir", "empleeFICAHiTaxPct");
					$Err_CFFICB=DecatErr_Field("@@ficb", "FICAOasdiMaxTaxInc");
					$Err_CFHIB =DecatErr_Field("@@hib@", "FICAHiMaxTaxInc");
					$Err_CFFUIR=DecatErr_Field("@@fuir", "FUICompPct");
					$Err_CFFUIB=DecatErr_Field("@@fuib", "FUICompMaxWage");
					$Err_CFOTM =DecatErr_Field("@@otm@", "ovrTimeMult");
					$Err_CFDTM =DecatErr_Field("@@dtm@", "dblTimeMult");
					$Err_CFCPGM=DecatErr_Field("@@cpgm", "chkPrtPgmName");
					$Err_CFPCNA=DecatErr_Field("@@pcna", "prtCoFacNameAndAdrOnChk");
					$Err_CFWGDA=DecatErr_Field("@@wgda", "interCoWageAcctsDueFromAcct");
					$Err_CFWGDS=DecatErr_Field("@@wgds", "interCoWageAcctsDueFromSubAcct");
					$Err_CFWGCA=DecatErr_Field("@@wgca", "interCoWageAcctsDueToAcct");
					$Err_CFWGCS=DecatErr_Field("@@wgcs", "interCoWageAcctsDueToSubAcct");
					$Err_CFTXDA=DecatErr_Field("@@txda", "interCoTaxAcctsDueFromAcct");
					$Err_CFTXDS=DecatErr_Field("@@txds", "interCoTaxAcctsDueFromSubAcct");
					$Err_CFTXCA=DecatErr_Field("@@txca", "interCoTaxAcctsDueToAcct");
					$Err_CFTXCS=DecatErr_Field("@@txcs", "interCoTaxAcctsDueToSubAcct");

					$Err_CFVACH=DecatErr_Field("@@vach", "vacaValHrs");
					$Err_CFSICH=DecatErr_Field("@@sich", "sickValHrs");
					$Err_CFVACD=DecatErr_Field("@@vacd", "vacaValEarnings");
					$Err_CFSICD=DecatErr_Field("@@sicd", "sickValEarnings");
					$Err_CFEXVC=DecatErr_Field("@@exvc", "vacaExpAsEarned");
					$Err_CFEXSC=DecatErr_Field("@@exsc", "sickExpAsEarned");
					$Err_CFPRVC=DecatErr_Field("@@prvc", "vacaPrtAvailOnChks");
					$Err_CFPRSC=DecatErr_Field("@@prsc", "sickPrtAvailOnChks");
					$Err_CFOTHD=DecatErr_Field("@@othd", "overtimeAftNumbHrsPerDay");
					$Err_CFOTHW=DecatErr_Field("@@othw", "overtimeAftNumbHrsPerWeek");
					$Err_CFMXHR=DecatErr_Field("@@mxhr", "maxHourlyRate");
					$Err_CFMXGA=DecatErr_Field("@@mxga", "maxGrossAmt");

					$Err_CFPF13=DecatErr_Field("@@pf13", "employeesNotPaidRpt");
					$Err_CFGHPW=DecatErr_Field("@@ghpw", "grossHrsPayByDeptWorked");
					$Err_CFGHPH=DecatErr_Field("@@ghph", "grossHrsPayByHomeDept");
					$Err_CFPF1 =DecatErr_Field("@@pf1@", "payDtlRpt");
					$Err_CFPF2 =DecatErr_Field("@@pf2@", "expDistrByAcct");
					$Err_CFPF3 =DecatErr_Field("@@pf3@", "expDistrByDept");
					$Err_CFPRDD=DecatErr_Field("@@prdd", "preTaxEdit");
					$Err_CFPTCD=DecatErr_Field("@@ptcd", "taxComputationEdit");
					$Err_CFPODD=DecatErr_Field("@@podd", "postTaxDeductEdit");
					$Err_CFPF5 =DecatErr_Field("@@pf5@", "four01kRpt");
					$Err_CFPF6 =DecatErr_Field("@@pf6@", "vacaaccrualRpt");
					$Err_CFPF7 =DecatErr_Field("@@pf7@", "sickaccrualRpt");
					$Err_CFPF8 =DecatErr_Field("@@pf8@", "FSAContribAndPayments");
					$Err_CFPF9 =DecatErr_Field("@@pf9@", "certifiedPayrollEdit");
					$Err_CFPF4 =DecatErr_Field("@@pf4@", "dirDepositEdit");
					$Err_CFPF10=DecatErr_Field("@@pf10", "payrollBalRpt");
					$Err_CFPPRD=DecatErr_Field("@@pprd", "payrollRegister");

					$Err_CFHD01=DecatErr_Field("@@hd01", "hourlyDetOrSumHrsEntry");
					$Err_CFHD02=DecatErr_Field("@@hd02", "hourlyElapsedHrsOrStartStop");
					$Err_CFHD03=DecatErr_Field("@@hd03", "hourlySuppressDateWorked");
					$Err_CFHD04=DecatErr_Field("@@hd04", "hourlyDateDefault");
					$Err_CFHD05=DecatErr_Field("@@hd05", "hourlyPayCode");
					$Err_CFHD06=DecatErr_Field("@@hd06", "hourlyShiftWorked");
					$Err_CFHD07=DecatErr_Field("@@hd07", "hourlyDeptWorked");
					$Err_CFHD08=DecatErr_Field("@@hd08", "hourlyJobNumb");
					$Err_CFHD09=DecatErr_Field("@@hd09", "hourlyJobClass");
					$Err_CFHD10=DecatErr_Field("@@hd10", "hourlyOverridePayRate");
					$Err_CFHD11=DecatErr_Field("@@hd11", "hourlyOverrideGLAcct");
					$Err_CFSD01=DecatErr_Field("@@sd01", "salaryDetOrSumHrsEntry");
					$Err_CFSD02=DecatErr_Field("@@sd02", "salaryElapsedHrsOrStartStop");
					$Err_CFSD03=DecatErr_Field("@@sd03", "salarySuppressDateWorked");
					$Err_CFSD04=DecatErr_Field("@@sd04", "salaryDateDefault");
					$Err_CFSD05=DecatErr_Field("@@sd05", "salaryPayCode");
					$Err_CFSD06=DecatErr_Field("@@sd06", "salaryShiftWorked");
					$Err_CFSD07=DecatErr_Field("@@sd07", "salaryDeptWorked");
					$Err_CFSD08=DecatErr_Field("@@sd08", "salaryJobNumb");
					$Err_CFSD09=DecatErr_Field("@@sd09", "salaryJobClass");
					$Err_CFSD10=DecatErr_Field("@@sd10", "salaryOverridePayRate");
					$Err_CFSD11=DecatErr_Field("@@sd11", "salaryOverrideGLAcct");

					$Err_ACPC  =DecatErr_Field("@@acpc", "paycodecheckbox");

					$Err_CFHRGC=DecatErr_Field("@@hrgc", "hourlyRegPayCodeDefault");
					$Err_CFHOVC=DecatErr_Field("@@hovc", "hourlyOvertimePayCodeDefault");
					$Err_CFHDTC=DecatErr_Field("@@hdtc", "hourlyDBLtimePayCodeDefault");
					$Err_CFHVCC=DecatErr_Field("@@hvcc", "hourlyVacationPayCodeDefault");
					$Err_CFHHLC=DecatErr_Field("@@hhlc", "hourlyHolidayPayCodeDefault");
					$Err_CFHSCC=DecatErr_Field("@@hscc", "hourlySickPayCodeDefault");
					$Err_CFHOTC=DecatErr_Field("@@hotc", "hourlyOtherTaxablePayCodeDefault");
					$Err_CFHNTC=DecatErr_Field("@@hntc", "hourlyNonTaxablePayCodeDefault");
					$Err_CFHCMC=DecatErr_Field("@@hcmc", "hourlyCommissionPayCodeDefault");
					$Err_CFHBNC=DecatErr_Field("@@hbnc", "hourlyBonusPayCodeDefault");
					$Err_CFHFGC=DecatErr_Field("@@hfgc", "hourlyFringePayCodeDefault");
					$Err_CFHMWM=DecatErr_Field("@@hmwm", "hourlyMinWageMakeupPayCodeDefault");
					$Err_CFHCTC=DecatErr_Field("@@hctc", "hourlyCashTipsPayCodeDefault");
					$Err_CFHGTC=DecatErr_Field("@@hgtc", "hourlyChargeTipsPayCodeDefault");
					$Err_CFHGRC=DecatErr_Field("@@hgrc", "hourlyGrossReceiptsPayCodeDefault");
					$Err_CFHMLC=DecatErr_Field("@@hmlc", "hourlyMealAllowPayCodeDefault");
					$Err_CFSRGC=DecatErr_Field("@@srgc", "salaryRegPayCodeDefault");
					$Err_CFSOVC=DecatErr_Field("@@sovc", "salaryOvertimePayCodeDefault");
					$Err_CFSDTC=DecatErr_Field("@@sdtc", "salaryDBLtimePayCodeDefault");
					$Err_CFSVCC=DecatErr_Field("@@svcc", "salaryVacationPayCodeDefault");
					$Err_CFSHLC=DecatErr_Field("@@shlc", "salaryHolidayPayCodeDefault");
					$Err_CFSSCC=DecatErr_Field("@@sscc", "salarySickPayCodeDefault");
					$Err_CFSOTC=DecatErr_Field("@@sotc", "salaryOtherTaxablePayCodeDefault");
					$Err_CFSNTC=DecatErr_Field("@@sntc", "salaryNonTaxablePayCodeDefault");
					$Err_CFSCMC=DecatErr_Field("@@scmc", "salaryCommissionPayCodeDefault");
					$Err_CFSBNC=DecatErr_Field("@@sbnc", "salaryBonusPayCodeDefault");
					$Err_CFSFGC=DecatErr_Field("@@sfgc", "salaryFringePayCodeDefault");
				}
				$errFound= "";
			}
			$row['CFTSTP']=Decat_Field("@@tstp", $edtVar);
			if ($hrCo=='Y') {
				$row['CFCOMP']=Decat_Field("@@comp", $edtVar);
				$row['CFFACL']=Decat_Field("@@facl", $edtVar);
				$row['CFNAME']=Decat_Field("@@name", $edtVar);
				$row['CFADR1']=Decat_Field("@@adr1", $edtVar);
				$row['CFADR2']=Decat_Field("@@adr2", $edtVar);
				$row['CFCITY']=Decat_Field("@@city", $edtVar);
				$row['CFSTID']=Decat_Field("@@stid", $edtVar);
				$row['CFZIP'] =Decat_Field("@@zip@", $edtVar);
				$row['CFPHON']=Decat_Field("@@phon", $edtVar);
				$row['CFSBH'] =Decat_Field("@@sbh@", $edtVar);
			} else {
				$row['CFCOMP']=Decat_Field("@@comp", $edtVar);
				$row['CFFACL']=Decat_Field("@@facl", $edtVar);
				$row['CFNAME']=Decat_Field("@@name", $edtVar);
				$row['CFADR1']=Decat_Field("@@adr1", $edtVar);
				$row['CFADR2']=Decat_Field("@@adr2", $edtVar);
				$row['CFCITY']=Decat_Field("@@city", $edtVar);
				$row['CFSTID']=Decat_Field("@@stid", $edtVar);
				$row['CFZIP'] =Decat_Field("@@zip@", $edtVar);
				$row['CFPHON']=Decat_Field("@@phon", $edtVar);
				$row['CFHRXR']=Decat_Field("@@hrxr", $edtVar);

				$row['CFSTCD']=Decat_Field("@@stcd", $edtVar);
				$row['CFDAY'] =Decat_Field("@@day@", $edtVar);
				$row['CFCUT'] =Decat_Field("@@cut@", $edtVar);
				$row['CFDUE'] =Decat_Field("@@due@", $edtVar);
				$row['CFGRAC']=Decat_Field("@@grac", $edtVar);
				$row['CFRDTH']=Decat_Field("@@rdth", $edtVar);
				$row['CFMGN'] =Decat_Field("@@mgn@", $edtVar);
				$row['CFMCN'] =Decat_Field("@@mcn@", $edtVar);
				$row['CFMVN'] =Decat_Field("@@mvn@", $edtVar);

				$row['CFBKNO']=Decat_Field("@@bkno", $edtVar);
				$row['CFACCT']=Decat_Field("@@acct", $edtVar);
				$row['CFBEGB']=Decat_Field("@@begb", $edtVar);
				$row['CFCKRC']=Decat_Field("@@ckrc", $edtVar);
				$row['CFPRTR']=Decat_Field("@@prtr", $edtVar);
				$row['CFPRTS']=Decat_Field("@@prts", $edtVar);
				$row['CFHTCL']=Decat_Field("@@htcl", $edtVar);
				$row['CFFGL'] =Decat_Field("@@fgl@", $edtVar);
				$row['CFVGL'] =Decat_Field("@@vgl@", $edtVar);
				$row['CFPF11']=Decat_Field("@@pf11", $edtVar);
				$row['CFP401']=Decat_Field("@@p401", $edtVar);
				$row['CFPF12']=Decat_Field("@@pf12", $edtVar);
				$row['CFCPR'] =Decat_Field("@@cpr@", $edtVar);
				$row['PCTIP'] =Decat_Field("@@tip@", $edtVar);
				$row['CFTCP'] =Decat_Field("@@tcp@", $edtVar);
				$row['CXMWR'] =Decat_Field("@@mwr@", $edtVar);
				$row['CXOPWG']=Decat_Field("@@opwg", $edtVar);
				$row['CFEIN'] =Decat_Field("@@ein@", $edtVar);

				$row['CFRFCR']=Decat_Field("@@rfcr", $edtVar);
				$row['CFRHIR']=Decat_Field("@@rhir", $edtVar);
				$row['CFEFCR']=Decat_Field("@@efcr", $edtVar);
				$row['CFEHIR']=Decat_Field("@@ehir", $edtVar);
				$row['CFFICB']=Decat_Field("@@ficb", $edtVar);
				$row['CFHIB'] =Decat_Field("@@hib@", $edtVar);
				$row['CFFUIR']=Decat_Field("@@fuir", $edtVar);
				$row['CFFUIB']=Decat_Field("@@fuib", $edtVar);
				$row['CFOTM'] =Decat_Field("@@otm@", $edtVar);
				$row['CFDTM'] =Decat_Field("@@dtm@", $edtVar);
				$row['CFCPGM']=Decat_Field("@@cpgm", $edtVar);
				$row['CFPCNA']=Decat_Field("@@pcna", $edtVar);
				$row['CFWGDA']=Decat_Field("@@wgda", $edtVar);
				$row['CFWGDS']=Decat_Field("@@wgds", $edtVar);
				$row['CFWGCA']=Decat_Field("@@wgca", $edtVar);
				$row['CFWGCS']=Decat_Field("@@wgcs", $edtVar);
				$row['CFTXDA']=Decat_Field("@@txda", $edtVar);
				$row['CFTXDS']=Decat_Field("@@txds", $edtVar);
				$row['CFTXCA']=Decat_Field("@@txca", $edtVar);
				$row['CFTXCS']=Decat_Field("@@txcs", $edtVar);

				$row['CFVACH']=Decat_Field("@@vach", $edtVar);
				$row['CFSICH']=Decat_Field("@@sich", $edtVar);
				$row['CFVACD']=Decat_Field("@@vacd", $edtVar);
				$row['CFSICD']=Decat_Field("@@sicd", $edtVar);
				$row['CFEXVC']=Decat_Field("@@exvc", $edtVar);
				$row['CFEXSC']=Decat_Field("@@exsc", $edtVar);
				$row['CFPRVC']=Decat_Field("@@prvc", $edtVar);
				$row['CFPRSC']=Decat_Field("@@prsc", $edtVar);
				$row['CFOTHD']=Decat_Field("@@othd", $edtVar);
				$row['CFOTHW']=Decat_Field("@@othw", $edtVar);
				$row['CFMXHR']=Decat_Field("@@mxhr", $edtVar);
				$row['CFMXGA']=Decat_Field("@@mxga", $edtVar);

				$row['CFPF13']=Decat_Field("@@pf13", $edtVar);
				$row['CFGHPW']=Decat_Field("@@ghpw", $edtVar);
				$row['CFGHPH']=Decat_Field("@@ghph", $edtVar);
				$row['CFPF1'] =Decat_Field("@@pf1@", $edtVar);
				$row['CFPF2'] =Decat_Field("@@pf2@", $edtVar);
				$row['CFPF3'] =Decat_Field("@@pf3@", $edtVar);
				$row['CFPRDD']=Decat_Field("@@prdd", $edtVar);
				$row['CFPTCD']=Decat_Field("@@ptcd", $edtVar);
				$row['CFPODD']=Decat_Field("@@podd", $edtVar);
				$row['CFPF5'] =Decat_Field("@@pf5@", $edtVar);
				$row['CFPF6'] =Decat_Field("@@pf6@", $edtVar);
				$row['CFPF7'] =Decat_Field("@@pf7@", $edtVar);
				$row['CFPF8'] =Decat_Field("@@pf8@", $edtVar);
				$row['CFPF9'] =Decat_Field("@@pf9@", $edtVar);
				$row['CFPF4'] =Decat_Field("@@pf4@", $edtVar);
				$row['CFPF10']=Decat_Field("@@pf10", $edtVar);
				$row['CFPPRD']=Decat_Field("@@pprd", $edtVar);

				$row['CFHD01']=Decat_Field("@@hd01", $edtVar);
				$row['CFHD02']=Decat_Field("@@hd02", $edtVar);
				$row['CFHD03']=Decat_Field("@@hd03", $edtVar);
				$row['CFHD04']=Decat_Field("@@hd04", $edtVar);
				$row['CFHD05']=Decat_Field("@@hd05", $edtVar);
				$row['CFHD06']=Decat_Field("@@hd06", $edtVar);
				$row['CFHD07']=Decat_Field("@@hd07", $edtVar);
				$row['CFHD08']=Decat_Field("@@hd08", $edtVar);
				$row['CFHD09']=Decat_Field("@@hd09", $edtVar);
				$row['CFHD10']=Decat_Field("@@hd10", $edtVar);
				$row['CFHD11']=Decat_Field("@@hd11", $edtVar);
				$row['CFSD01']=Decat_Field("@@sd01", $edtVar);
				$row['CFSD02']=Decat_Field("@@sd02", $edtVar);
				$row['CFSD03']=Decat_Field("@@sd03", $edtVar);
				$row['CFSD04']=Decat_Field("@@sd04", $edtVar);
				$row['CFSD05']=Decat_Field("@@sd05", $edtVar);
				$row['CFSD06']=Decat_Field("@@sd06", $edtVar);
				$row['CFSD07']=Decat_Field("@@sd07", $edtVar);
				$row['CFSD08']=Decat_Field("@@sd08", $edtVar);
				$row['CFSD09']=Decat_Field("@@sd09", $edtVar);
				$row['CFSD10']=Decat_Field("@@sd10", $edtVar);
				$row['CFSD11']=Decat_Field("@@sd11", $edtVar);

				$ACPC=Decat_Field("@@acpc", $edtVar);

				$row['CFHRGC']=Decat_Field("@@hrgc", $edtVar);
				$row['CFHOVC']=Decat_Field("@@hovc", $edtVar);
				$row['CFHDTC']=Decat_Field("@@hdtc", $edtVar);
				$row['CFHVCC']=Decat_Field("@@hvcc", $edtVar);
				$row['CFHHLC']=Decat_Field("@@hhlc", $edtVar);
				$row['CFHSCC']=Decat_Field("@@hscc", $edtVar);
				$row['CFHOTC']=Decat_Field("@@hotc", $edtVar);
				$row['CFHNTC']=Decat_Field("@@hntc", $edtVar);
				$row['CFHCMC']=Decat_Field("@@hcmc", $edtVar);
				$row['CFHBNC']=Decat_Field("@@hbnc", $edtVar);
				$row['CFHFGC']=Decat_Field("@@hfgc", $edtVar);
				$row['CFHMWM']=Decat_Field("@@hmwm", $edtVar);
				$row['CFHCTC']=Decat_Field("@@hctc", $edtVar);
				$row['CFHGTC']=Decat_Field("@@hgtc", $edtVar);
				$row['CFHGRC']=Decat_Field("@@hgrc", $edtVar);
				$row['CFHMLC']=Decat_Field("@@hmlc", $edtVar);
				$row['CFSRGC']=Decat_Field("@@srgc", $edtVar);
				$row['CFSOVC']=Decat_Field("@@sovc", $edtVar);
				$row['CFSDTC']=Decat_Field("@@sdtc", $edtVar);
				$row['CFSVCC']=Decat_Field("@@svcc", $edtVar);
				$row['CFSHLC']=Decat_Field("@@shlc", $edtVar);
				$row['CFSSCC']=Decat_Field("@@sscc", $edtVar);
				$row['CFSOTC']=Decat_Field("@@sotc", $edtVar);
				$row['CFSNTC']=Decat_Field("@@sntc", $edtVar);
				$row['CFSCMC']=Decat_Field("@@scmc", $edtVar);
				$row['CFSBNC']=Decat_Field("@@sbnc", $edtVar);
				$row['CFSFGC']=Decat_Field("@@sfgc", $edtVar);
			}
		}	elseif ($maintenanceCode=="Z") {
			$focusField= "coNum";
			if ($hrCo!='Y') {
				$row[CFRFCR]=100*($row[CFRFCR]);
				$row[CFRHIR]=100*($row[CFRHIR]);
				$row[CFEFCR]=100*($row[CFEFCR]);
				$row[CFEHIR]=100*($row[CFEHIR]);
				$row[CFFUIR]=100*($row[CFFUIR]);
			}
		} else {
			$focusField= "coName";
			if ($hrCo!='Y') {
				$row[CFRFCR]=100*($row[CFRFCR]);
				$row[CFRHIR]=100*($row[CFRHIR]);
				$row[CFEFCR]=100*($row[CFEFCR]);
				$row[CFEHIR]=100*($row[CFEHIR]);
				$row[CFFUIR]=100*($row[CFFUIR]);
			}
		}
		if ($row['CFPRAC'] == "D" and $row['CFPEAC'] == "D" and $row['CFCBAC'] == "D"){
			$maintenanceCode= "R";
			$ReactivateDesc="Click Update to Reactivate";
		}

		if ($hrCo=='Y') {

			// Screen 1: Facl = 0 - Human Resources Co
			print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;hrCo=" . urlencode(trim($hrCo)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
			print "\n <table $contentTable>";
			print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['CFTSTP']) . "\"></td></tr> ";
			$textOvr=SetTextOvr($Err_CFCOMP);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company Number</span></td>";
			if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
				print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"coNum\" value=\"" . rtrim($row['CFCOMP']) . "\" size=\"2\" maxlength=\"2\">$reqFieldChar</td>";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"coNum\" value=\"" . rtrim($row['CFCOMP']) . "\">$row[CFCOMP]";
				if ($maintenanceCode=="R") {print "\n  <span class=\"dspdesc\">$ReactivateDesc</span>";}
				print "\n </td>";
			}
			print "\n </tr> ";
			DspErrMsg($Err_CFCOMP);

			$textOvr=SetTextOvr($Err_CFFACL);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Facility Number</span></td>";
			if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
				$row[CFFACL]=0;
			}
			print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"facNum\" value=\"" . rtrim($row['CFFACL']) . "\">$row[CFFACL]</td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFFACL);

			Build_Fld_Entry("Company/Facility Name","coName","inputalph","","CFNAME",$row[CFNAME],$Err_CFNAME,"50","30","Y","","");
			Build_Fld_Entry("Address Line 1","address1","inputalph","","CFADR1",$row[CFADR1],$Err_CFADR1,"50","30","Y","","");
			Build_Fld_Entry("Address Line 2","address2","inputalph","","CFADR2",$row[CFADR2],$Err_CFADR2,"50","30","","","");
			Build_Fld_Entry("City","city","inputalph","","CFCITY",$row[CFCITY],$Err_CFCITY,"25","16","Y","","");
			$textOvr=SetTextOvr($Err_CFSTID);
			$fieldDesc=RetValue("STID='$row[CFSTID]'", "HDSTID", "STDESC");
			print "\n <tr><td class=\"dsphdr\"><span $textOvr></span>State</td> ";
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"state\" value=\"" . rtrim($row['CFSTID']) . "\" size=\"1\" maxlength=\"2\"> ";
			print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=state&amp;fldDesc=stateDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"stateDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFSTID);
			Build_Fld_Entry("Zip Code","zipCode","inputalph","","CFZIP",$row[CFZIP],$Err_CFZIP,"10","15","Y","","");
			$textOvr=SetTextOvr($Err_CFPHON);
			if ($row[CFPHON] > "0")
			while (strlen($row[CFPHON])<10) {$row[CFPHON] =  "0{$row[CFPHON]}" ;}
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Phone Number</span></td>";
			print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"phone\" value=\"" . rtrim($row['CFPHON']) . "\" size=\"15\" maxlength=\"10\">$reqFieldChar</td></tr>";
			DspErrMsg($Err_CFPHON);
			Build_Fld_Entry("Weekly Std. Budget Hrs","weeklyStdBudgetHrs","inputnmbr","","CFSBH",$row[CFSBH],$Err_CFSBH,"2","2","Y","","");
			print "\n </table>";

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

		} else {

			print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;hrCo=" . urlencode(trim($hrCo)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";

			// Screen 1: Facl <> 0 - Co/Fac
			print "\n <a name=\"common\"></a> ";
			print "\n <fieldset class=\"legendBody\"> ";
			print "\n <legend class=\"legendTitle\">Common Data</legend> ";
			require 'TopOfForm.php';
			print "\n <table $contentTable>";
			print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['CFTSTP']) . "\"></td></tr> ";
			$textOvr=SetTextOvr($Err_CFCOMP);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Company Number</span></td>";

			if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
				print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"coNum\" value=\"" . rtrim($row['CFCOMP']) . "\" size=\"2\" maxlength=\"2\">$reqFieldChar</td>";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"coNum\" value=\"" . rtrim($row['CFCOMP']) . "\">$row[CFCOMP]";
				if ($maintenanceCode=="R") {print "\n  <span class=\"dspdesc\">$ReactivateDesc</span>";}
				print "\n </td>";
			}
			print "\n </tr> ";

			DspErrMsg($Err_CFCOMP);
			$textOvr=SetTextOvr($Err_CFFACL);
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Facility Number</span></td>";
			if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
				print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"facNum\" value=\"" . rtrim($row[CFFACL]) . "\" size=\"4\" maxlength=\"4\">$reqFieldChar</td>";
			} else {
				print "\n <td class=\"inputnmbr\"><input type=\"hidden\"   name=\"facNum\" value=\"" . rtrim($row[CFFACL]) . "\">$row[CFFACL]</td>";
			}
			print "\n </tr> ";
			DspErrMsg($Err_CFFACL);

			Build_Fld_Entry("Company/Facility Name","coName","inputalph","","CFNAME",$row[CFNAME],$Err_CFNAME,"50","30","Y","","");
			Build_Fld_Entry("Address Line 1","address1","inputalph","","CFADR1",$row[CFADR1],$Err_CFADR1,"50","30","Y","","");
			Build_Fld_Entry("Address Line 2","address2","inputalph","","CFADR2",$row[CFADR2],$Err_CFADR2,"50","30","","","");
			Build_Fld_Entry("City","city","inputalph","","CFCITY",$row[CFCITY],$Err_CFCITY,"25","16","Y","","");

			$textOvr=SetTextOvr($Err_CFSTID);
			$fieldDesc=RetValue("STID='$row[CFSTID]'", "HDSTID", "STDESC");
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>State</span></td> ";
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"state\" value=\"" . rtrim($row[CFSTID]) . "\" size=\"1\" maxlength=\"2\"> ";
			print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=state&amp;fldDesc=stateDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"stateDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFSTID);

			Build_Fld_Entry("Zip Code","zipCode","inputalph","","CFZIP",$row[CFZIP],$Err_CFZIP,"10","15","Y","","");

			$textOvr=SetTextOvr($Err_CFPHON);
			if ($row[CFPHON] > "0")
			while (strlen($row[CFPHON])<10) {$row[CFPHON] =  "0{$row[CFPHON]}" ;}
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Phone Number</span></td>";
			print "\n  <td class=\"inputalph\"><input type=\"text\"  name=\"phone\" value=\"" . rtrim($row[CFPHON]) . "\" size=\"15\" maxlength=\"10\">$reqFieldChar</td></tr>";
			DspErrMsg($Err_CFPHON);
			Build_Fld_Entry("H/R Company Number","coHRNum","inputnmbr","","CFHRXR",$row[CFHRXR],$Err_CFHRXR,"2","2","Y","","");
			print "\n </table> ";
			print "\n </fieldset> ";

			// Screen 2
			print "\n <a name=\"cobra\"></a> ";
			print "\n <fieldset class=\"legendBody\"> ";
			print "\n <legend class=\"legendTitle\">COBRA</legend> ";
			require 'TopOfForm.php';
			print "\n <table $contentTable>";
			Build_Fld_Entry("Statement Code","stmtCode","inputalph","COBRASTCO","CFSTCD",$row[CFSTCD],$Err_CFSTCD,"1","1","Y","","");
			Build_Fld_Entry("Premium Period Start Day","premPeriodStrDay","inputnmbr","","CFDAY",$row[CFDAY],$Err_CFDAY,"2","2","Y","","");
			Build_Fld_Entry("Print Invoices NNN Days Before Premium Period","cutDays","inputnmbr","","CFCUT",$row[CFCUT],$Err_CFCUT,"3","3","","","");
			Build_Fld_Entry("Days Until Due","daysUntilDue","inputnmbr","","CFDUE",$row[CFDUE],$Err_CFDUE,"3","3","Y","","");
			Build_Fld_Entry("Grace Period (Days)","gracePeriodDays","inputnmbr","","CFGRAC",$row[CFGRAC],$Err_CFGRAC,"3","3","","","");
			Build_Fld_Entry("Retain Detail Transaction History","retainDetTransHist","inputalph","YORN","CFRDTH",$row[CFRDTH],$Err_CFRDTH,"1","1","Y","","");
			Build_Fld_Entry("Manual General Notice","manualGenNoticeDefault","inputalph","YORN","CFMGN",$row[CFMGN],$Err_CFMGN,"1","1","Y","","");
			Build_Fld_Entry("Manual Continuation Notice","manualContNoticeDefault","inputalph","YORN","CFMCN",$row[CFMCN],$Err_CFMCN,"1","1","Y","","");
			Build_Fld_Entry("Manual Conversion Notice","manualConvNoticeDefault","inputalph","YORN","CFMVN",$row[CFMVN],$Err_CFMVN,"1","1","Y","","");
			print "\n </table> ";
			print "\n </fieldset> ";

			// Screen 3
			print "\n <a name=\"payrollGeneral\"></a> ";
			print "\n <fieldset class=\"legendBody\"> ";
			print "\n <legend class=\"legendTitle\">Payroll General</legend> ";
			require 'TopOfForm.php';
			print "\n <table $contentTable>";

			$textOvr=SetTextOvr($Err_CFBKNO);
			$fieldDesc=RetValue("BKBANK='$row[CFBKNO]'", "HDBANK", "BKBKNM");
			print "\n <tr><td class=\"dsphdr\"><span $textOvr>Bank</span></td> ";
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"bank\" value=\"" . rtrim($row[CFBKNO]) . "\" size=\"2\" maxlength=\"2\"> ";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayrollBankSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=bank&amp;fldDesc=bankDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"bankDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFBKNO);

			Build_Fld_Entry("Override Bank Account Number","ovrBankAcctNumb","inputalph","","CFACCT",$row[CFACCT],$Err_CFACCT,"22","17","","","");
			Build_Fld_Entry("Override Beginning Batch Number","ovrBegBatchNumb","inputnmbr","","CFBEGB",$row[CFBEGB],$Err_CFBEGB,"10","7","","","");
			Build_Fld_Entry("Check Reconciliation","chkRecon","inputalph","YORN","CFCKRC",$row[CFCKRC],$Err_CFCKRC,"1","1","Y","","");
			Build_Fld_Entry("Suppress Pay Trans. Entry Rates","suppPayTransEntryRates","inputalph","YORN","CFPRTR",$row[CFPRTR],$Err_CFPRTR,"1","1","Y","","");
			Build_Fld_Entry("Time Entry Form Format","timeEntryFormFormat","inputalph","ENTRYFORM","CFPRTS",$row[CFPRTS],$Err_CFPRTS,"1","1","Y","","");
			Build_Fld_Entry("Print Time Card Labels","prtTimeCardLabels","inputalph","YORN","CFHTCL",$row[CFHTCL],$Err_CFHTCL,"1","1","Y","","");
			Build_Fld_Entry("Activate G/L Feed","actvGLFeed","inputalph","YORN","CFFGL",$row[CFFGL],$Err_CFFGL,"1","1","Y","","");
			Build_Fld_Entry("Validate G/L Accounts","valGLAccts","inputalph","YORN","CFVGL",$row[CFVGL],$Err_CFVGL,"1","1","Y","","");
			Build_Fld_Entry("Print Overflow Advice","prtOverFlowAdv","inputalph","OVERFLOWAD","CFPF11",$row[CFPF11],$Err_CFPF11,"1","1","Y","","");
			Build_Fld_Entry("Print Employer 401(k) on Checks","prtEmplr401kOnChks","inputalph","YORN","CFP401",$row[CFP401],$Err_CFP401,"1","1","Y","","");
			Build_Fld_Entry("Run Reset Accruals Each Period","runResetAccrEachPer","inputalph","YORN","CFPF12",$row[CFPF12],$Err_CFPF12,"1","1","Y","","");
			Build_Fld_Entry("Certified Payroll History","certPayrollHist","inputalph","YORN","CFCPR",$row[CFCPR],$Err_CFCPR,"1","1","Y","","");
			Build_Fld_Entry("Tip Payroll","tipPayroll","inputalph","YORN","PCTIP",$row[PCTIP],$Err_PCTIP,"1","1","Y","","");
			Build_Fld_Entry("Tip Credit Percent","tipCredPct","inputnmbr","","CFTCP",$row[CFTCP],$Err_CFTCP,"8","6","","","");
			Build_Fld_Entry("Minimum Wage","minWage","inputnmbr","","CXMWR",$row[CXMWR],$Err_CXMWR,"7","6","","","");
			Build_Fld_Entry("Opportunity Wage","opportWage","inputnmbr","","CXOPWG",$row[CXOPWG],$Err_CXOPWG,"7","6","","","");
			Build_Fld_Entry("Establishment ID Number","estIDNumb","inputalph","","CFEIN",$row[CFEIN],$Err_CFEIN,"21","16","","","");

			// Screen 4
			Build_Fld_Entry("Employer FICA OASDI Tax Percent","emplrFICAOasdiTaxPct","inputnmbr","","CFRFCR",$row[CFRFCR],$Err_CFRFCR,"7","6","Y","","");
			Build_Fld_Entry("Employer FICA HI Tax Percent","emplrFICAHiTaxPct","inputnmbr","","CFRHIR",$row[CFRHIR],$Err_CFRHIR,"7","6","Y","","");
			Build_Fld_Entry("Employee FICA OASDI Tax Percent","empleeFICAOasdiTaxPct","inputnmbr","","CFEFCR",$row[CFEFCR],$Err_CFEFCR,"7","6","Y","","");
			Build_Fld_Entry("Employee FICA HI Tax Percent","empleeFICAHiTaxPct","inputnmbr","","CFEHIR",$row[CFEHIR],$Err_CFEHIR,"7","6","Y","","");
			Build_Fld_Entry("FICA OASDI Maximum Taxable Income","FICAOasdiMaxTaxInc","inputnmbr","","CFFICB",$row[CFFICB],$Err_CFFICB,"11","10","Y","","");
			Build_Fld_Entry("FICA HI Maximum Taxable Income","FICAHiMaxTaxInc","inputnmbr","","CFHIB",$row[CFHIB],$Err_CFHIB,"11","10","Y","","");
			Build_Fld_Entry("FUI Compensation Percent","FUICompPct","inputnmbr","","CFFUIR",$row[CFFUIR],$Err_CFFUIR,"5","4","","","");
			Build_Fld_Entry("FUI Compensation Maximum Wage","FUICompMaxWage","inputnmbr","","CFFUIB",$row[CFFUIB],$Err_CFFUIB,"9","8","","","");
			Build_Fld_Entry("Overtime Multiplier","ovrTimeMult","inputnmbr","","CFOTM",$row[CFOTM],$Err_CFOTM,"5","4","Y","","");
			Build_Fld_Entry("Double Time Multiplier","dblTimeMult","inputnmbr","","CFDTM",$row[CFDTM],$Err_CFDTM,"5","4","Y","","");
			Build_Fld_Entry("Check Printing Program Name","chkPrtPgmName","inputalph","","CFCPGM",$row[CFCPGM],$Err_CFCPGM,"15","10","","","");
			Build_Fld_Entry("Print Co/Fac Name And Address On Check","prtCoFacNameAndAdrOnChk","inputalph","YORN","CFPCNA",$row[CFPCNA],$Err_CFPCNA,"1","1","Y","","");
			print "\n </table> ";
			print "\n </fieldset> ";
			print "\n <a name=\"payrollIntCoAccts\"></a> ";
			print "\n <fieldset class=\"legendBody\"> ";
			print "\n <legend class=\"legendTitle\">Inter-Company Accounts</legend> ";
			require 'TopOfForm.php';
			print "\n <table $contentTable>";

			print "\n <tr><td>&nbsp;</td><td class=\"colhdr\">Due From</td><td class=\"colhdr\">Due To</td></tr> ";
			if (is_null($row['CFWGDA']) || trim($row['CFWGDA'])=="") {$row['CFWGDA']=0;}
			if (is_null($row['CFWGDS']) || trim($row['CFWGDS'])=="") {$row['CFWGDS']=0;}
			if (is_null($row['CFWGCA']) || trim($row['CFWGCA'])=="") {$row['CFWGCA']=0;}
			if (is_null($row['CFWGCS']) || trim($row['CFWGCS'])=="") {$row['CFWGCS']=0;}
			$textOvr=SetTextOvr($Err_CFWGDA);
			$textOvr=SetTextOvr($Err_CFWGCA);
			$fieldDesc=RetValue("CHACCT=$row[CFWGDA] and CHSUB=$row[CFWGDS]", "HDCHRT", "CHCHDS");
			print "\n <tr><td class=\"dsphdr\">Wage</td>";
			print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"interCoWageAcctsDueFromAcct\" value=\"" . rtrim($row[CFWGDA]) . "\" size=\"1\" maxlength=\"4\"> - <input type=\"text\"   name=\"interCoWageAcctsDueFromSubAcct\" value=\"" . rtrim($row[CFWGDS]) . "\" size=\"1\" maxlength=\"4\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=interCoWageAcctsDueFromAcct&amp;subFld=interCoWageAcctsDueFromSubAcct&amp;descFld=interCoWageAcctsDueFromAcctDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"interCoWageAcctsDueFromAcctDesc\">$fieldDesc</span>&nbsp;</td>";
			$fieldDesc=RetValue("CHACCT=$row[CFWGCA] and CHSUB=$row[CFWGCS]", "HDCHRT", "CHCHDS");
			print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"interCoWageAcctsDueToAcct\" value=\"" . rtrim($row[CFWGCA]) . "\" size=\"1\" maxlength=\"4\"> - <input type=\"text\"   name=\"interCoWageAcctsDueToSubAcct\" value=\"" . rtrim($row[CFWGCS]) . "\" size=\"1\" maxlength=\"4\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=interCoWageAcctsDueToAcct&amp;subFld=interCoWageAcctsDueToSubAcct&amp;descFld=interCoWageAcctsDueToAcctDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"interCoWageAcctsDueToAcctDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFWGDA);
			DspErrMsg($Err_CFWGCA);

			if (is_null($row['CFTXDA']) || trim($row['CFTXDA'])=="") {$row['CFTXDA']=0;}
			if (is_null($row['CFTXDS']) || trim($row['CFTXDS'])=="") {$row['CFTXDS']=0;}
			if (is_null($row['CFTXCA']) || trim($row['CFTXCA'])=="") {$row['CFTXCA']=0;}
			if (is_null($row['CFTXCS']) || trim($row['CFTXCS'])=="") {$row['CFTXCS']=0;}
			$textOvr=SetTextOvr($Err_CFTXDA);
			$textOvr=SetTextOvr($Err_CFTXCA);
			$fieldDesc=RetValue("CHACCT=$row[CFTXDA] and CHSUB=$row[CFTXDS]", "HDCHRT", "CHCHDS");
			print "\n <tr><td class=\"dsphdr\">Tax</td>";
			print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"interCoTaxAcctsDueFromAcct\" value=\"" . rtrim($row[CFTXDA]) . "\" size=\"1\" maxlength=\"4\"> - <input type=\"text\"   name=\"interCoTaxAcctsDueFromSubAcct\" value=\"" . rtrim($row[CFTXDS]) . "\" size=\"1\" maxlength=\"4\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=interCoTaxAcctsDueFromAcct&amp;subFld=interCoTaxAcctsDueFromSubAcct&amp;descFld=interCoTaxAcctsDueFromAcctDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"interCoTaxAcctsDueFromAcctDesc\">$fieldDesc</span>&nbsp;</td>";
			$fieldDesc=RetValue("CHACCT=$row[CFTXCA] and CHSUB=$row[CFTXCS]", "HDCHRT", "CHCHDS");
			print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"interCoTaxAcctsDueToAcct\" value=\"" . rtrim($row[CFTXCA]) . "\" size=\"1\" maxlength=\"4\"> - <input type=\"text\"   name=\"interCoTaxAcctsDueToSubAcct\" value=\"" . rtrim($row[CFTXCS]) . "\" size=\"1\" maxlength=\"4\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=interCoTaxAcctsDueToAcct&amp;subFld=interCoTaxAcctsDueToSubAcct&amp;descFld=interCoTaxAcctsDueToAcctDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"interCoTaxAcctsDueToAcctDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFTXDA);
			DspErrMsg($Err_CFTXCA);
			print "\n </table> ";
			print "\n </fieldset> ";

			// Screen 5
			print "\n <a name=\"payrollVacationSick\"></a> ";
			print "\n <fieldset class=\"legendBody\"> ";
			print "\n <legend class=\"legendTitle\">Vacation-Sick</legend> ";
			require 'TopOfForm.php';
			print "\n <table $contentTable>";

			print "\n <tr><td>&nbsp;</td><td class=\"colhdr\">Vacation</td><td class=\"colhdr\">Sick</td></tr> ";
			print "\n <tr><td class=\"dsphdr\">Validate Hours</td>";
			$textOvr=SetTextOvr($Err_CFVACH);
			$textOvr=SetTextOvr($Err_CFSICH);
			Build_Fld_Entry("","vacaValHrs","inputalph","YORN","CFVACH",$row[CFVACH],$Err_CFVACH,"1","1","Y","","Y");
			Build_Fld_Entry("","sickValHrs","inputalph","YORN","CFSICH",$row[CFSICH],$Err_CFSICH,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFVACH);
			DspErrMsg($Err_CFSICH);

			print "\n <tr><td class=\"dsphdr\">Validate Earnings</td>";
			$textOvr=SetTextOvr($Err_CFVACD);
			$textOvr=SetTextOvr($Err_CFSICD);
			Build_Fld_Entry("","vacaValEarnings","inputalph","YORN","CFVACD",$row[CFVACD],$Err_CFVACD,"1","1","Y","","Y");
			Build_Fld_Entry("","sickValEarnings","inputalph","YORN","CFSICD",$row[CFSICD],$Err_CFSICD,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFVACD);
			DspErrMsg($Err_CFSICD);

			print "\n <tr><td class=\"dsphdr\">Expense As Earned</td>";
			$textOvr=SetTextOvr($Err_CFEXVC);
			$textOvr=SetTextOvr($Err_CFEXSC);
			Build_Fld_Entry("","vacaExpAsEarned","inputalph","YORN","CFEXVC",$row[CFEXVC],$Err_CFEXVC,"1","1","Y","","Y");
			Build_Fld_Entry("","sickExpAsEarned","inputalph","YORN","CFEXSC",$row[CFEXSC],$Err_CFEXSC,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFEXVC);
			DspErrMsg($Err_CFEXSC);

			print "\n <tr><td class=\"dsphdr\">Print Available On Checks</td>";
			$textOvr=SetTextOvr($Err_CFPRVC);
			$textOvr=SetTextOvr($Err_CFPRSC);
			Build_Fld_Entry("","vacaPrtAvailOnChks","inputalph","YORN","CFPRVC",$row[CFPRVC],$Err_CFPRVC,"1","1","Y","","Y");
			Build_Fld_Entry("","sickPrtAvailOnChks","inputalph","YORN","CFPRSC",$row[CFPRSC],$Err_CFPRSC,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFPRVC);
			DspErrMsg($Err_CFPRSC);
			print "\n </table> ";
			print "\n </fieldset> ";
			print "\n <a name=\"payrollOvertime\"></a> ";
			print "\n <fieldset class=\"legendBody\"> ";
			print "\n <legend class=\"legendTitle\">Overtime</legend> ";
			require 'TopOfForm.php';
			print "\n <table $contentTable>";
			Build_Fld_Entry("Overtime After # Hours Per Day","overtimeAftNumbHrsPerDay","inputnmbr","","CFOTHD",$row[CFOTHD],$Err_CFOTHD,"7","6","","","");
			Build_Fld_Entry("Overtime After # Hours Per Week","overtimeAftNumbHrsPerWeek","inputnmbr","","CFOTHW",$row[CFOTHW],$Err_CFOTHW,"7","6","","","");
			Build_Fld_Entry("Maximum Hourly Rate","maxHourlyRate","inputnmbr","","CFMXHR",$row[CFMXHR],$Err_CFMXHR,"9","8","","","");
			Build_Fld_Entry("Maximum Gross Amount","maxGrossAmt","inputnmbr","","CFMXGA",$row[CFMXGA],$Err_CFMXGA,"11","10","","","");
			print "\n </table> ";
			print "\n </fieldset> ";

			// Screen 6
			print "\n <a name=\"payrollProcessingReportsPrintOptions\"></a> ";
			print "\n <fieldset class=\"legendBody\"> ";
			print "\n <legend class=\"legendTitle\">Processing Reports Print Options</legend> ";
			require 'TopOfForm.php';
			print "\n <table $contentTable>";
			Build_Fld_Entry("Employees Not Paid Report","employeesNotPaidRpt","inputalph","PRREPORT","CFPF13",$row[CFPF13],$Err_CFPF13,"1","1","Y","","");
			Build_Fld_Entry("Gross Hrs/Pay By Dept Worked","grossHrsPayByDeptWorked","inputalph","PRREPORT","CFGHPW",$row[CFGHPW],$Err_CFGHPW,"1","1","Y","","");
			Build_Fld_Entry("Gross Hrs/Pay By Home Dept","grossHrsPayByHomeDept","inputalph","PRREPORT","CFGHPH",$row[CFGHPH],$Err_CFGHPH,"1","1","Y","","");
			Build_Fld_Entry("Pay Detail Report","payDtlRpt","inputalph","PRREPORT","CFPF1",$row[CFPF1],$Err_CFPF1,"1","1","Y","","");
			Build_Fld_Entry("Expense Distribution By Account","expDistrByAcct","inputalph","PRREPORT","CFPF2",$row[CFPF2],$Err_CFPF2,"1","1","Y","","");
			Build_Fld_Entry("Expense Distribution By Dept","expDistrByDept","inputalph","PRREPORT","CFPF3",$row[CFPF3],$Err_CFPF3,"1","1","Y","","");
			Build_Fld_Entry("Pre-Tax Edit","preTaxEdit","inputalph","PRREPORT","CFPRDD",$row[CFPRDD],$Err_CFPRDD,"1","1","Y","","");
			Build_Fld_Entry("Tax Computation Edit","taxComputationEdit","inputalph","PRREPORT","CFPTCD",$row[CFPTCD],$Err_CFPTCD,"1","1","Y","","");
			Build_Fld_Entry("Post Tax Deduction Edit","postTaxDeductEdit","inputalph","PRREPORT","CFPODD",$row[CFPODD],$Err_CFPODD,"1","1","Y","","");
			Build_Fld_Entry("401(k) Report","four01kRpt","inputalph","PRREPORT","CFPF5",$row[CFPF5],$Err_CFPF5,"1","1","Y","","");
			Build_Fld_Entry("Vacation Accrual Report","vacaaccrualRpt","inputalph","PRREPORT","CFPF6",$row[CFPF6],$Err_CFPF6,"1","1","Y","","");
			Build_Fld_Entry("Sick Accrual Report","sickaccrualRpt","inputalph","PRREPORT","CFPF7",$row[CFPF7],$Err_CFPF7,"1","1","Y","","");
			Build_Fld_Entry("FSA Contributions And Payments","FSAContribAndPayments","inputalph","PRREPORT","CFPF8",$row[CFPF8],$Err_CFPF8,"1","1","Y","","");
			Build_Fld_Entry("Certified Payroll Edit","certifiedPayrollEdit","inputalph","PRREPORT","CFPF9",$row[CFPF9],$Err_CFPF9,"1","1","Y","","");
			Build_Fld_Entry("Direct Deposit Edit","dirDepositEdit","inputalph","PRREPORT","CFPF4",$row[CFPF4],$Err_CFPF4,"1","1","Y","","");
			Build_Fld_Entry("Payroll Balancing Report","payrollBalRpt","inputalph","","CFPF10",$row[CFPF10],$Err_CFPF10,"1","1","Y","","");
			Build_Fld_Entry("Payroll Register","payrollRegister","inputalph","PRREPORT","CFPPRD",$row[CFPPRD],$Err_CFPPRD,"1","1","Y","","");
			print "\n </table> ";
			print "\n </fieldset> ";

			// Screen 7
			print "\n <a name=\"payrollPayCodeDefaults\"></a> ";
			print "\n <fieldset class=\"legendBody\"> ";
			print "\n <legend class=\"legendTitle\">Pay Code Defaults</legend> ";
			require 'TopOfForm.php';
			print "\n <table $contentTable>";
			Build_Fld_Entry("Create Code Rows","paycodecheckbox","inputalph","YORN","ACPC",$ACPC,$Err_ACPC,"1","1","Y","","");

			print "\n <tr><td>&nbsp;</td><td class=\"colhdr\">Hourly</td><td class=\"colhdr\">Salaried</td></tr> ";
			print "\n <tr><td class=\"dsphdr\">Regular</td>";
			$textOvr=SetTextOvr($Err_CFHRGC);
			$textOvr=SetTextOvr($Err_CFSRGC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHRGC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyRegPayCodeDefault\" value=\"" . rtrim($row['CFHRGC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyRegPayCodeDefault&amp;fldDesc=hourlyRegPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyRegPayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSRGC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryRegPayCodeDefault\" value=\"" . rtrim($row['CFSRGC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryRegPayCodeDefault&amp;fldDesc=salaryRegPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryRegPayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHRGC);
			DspErrMsg($Err_CFSRGC);

			print "\n <tr><td class=\"dsphdr\">Overtime</td>";
			$textOvr=SetTextOvr($Err_CFHOVC);
			$textOvr=SetTextOvr($Err_CFSOVC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHOVC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyOvertimePayCodeDefault\" value=\"" . rtrim($row['CFHOVC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyOvertimePayCodeDefault&amp;fldDesc=hourlyOvertimePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyOvertimePayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSOVC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryOvertimePayCodeDefault\" value=\"" . rtrim($row['CFSOVC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryOvertimePayCodeDefault&amp;fldDesc=salaryOvertimePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryOvertimePayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHOVC);
			DspErrMsg($Err_CFSOVC);

			print "\n <tr><td class=\"dsphdr\">Double Time</td>";
			$textOvr=SetTextOvr($Err_CFHDTC);
			$textOvr=SetTextOvr($Err_CFSDTC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHDTC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyDBLtimePayCodeDefault\" value=\"" . rtrim($row['CFHDTC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyDBLtimePayCodeDefault&amp;fldDesc=hourlyDBLtimePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyDBLtimePayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSDTC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryDBLtimePayCodeDefault\" value=\"" . rtrim($row['CFSDTC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryDBLtimePayCodeDefault&amp;fldDesc=salaryDBLtimePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryDBLtimePayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHDTC);
			DspErrMsg($Err_CFSDTC);

			print "\n <tr><td class=\"dsphdr\">Vacation</td>";
			$textOvr=SetTextOvr($Err_CFHVCC);
			$textOvr=SetTextOvr($Err_CFSVCC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHVCC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyVacationPayCodeDefault\" value=\"" . rtrim($row['CFHVCC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyVacationPayCodeDefault&amp;fldDesc=hourlyVacationPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyVacationPayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSVCC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryVacationPayCodeDefault\" value=\"" . rtrim($row['CFSVCC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryVacationPayCodeDefault&amp;fldDesc=salaryVacationPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryVacationPayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHVCC);
			DspErrMsg($Err_CFSVCC);

			print "\n <tr><td class=\"dsphdr\">Holiday</td>";
			$textOvr=SetTextOvr($Err_CFHHLC);
			$textOvr=SetTextOvr($Err_CFSHLC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHHLC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyHolidayPayCodeDefault\" value=\"" . rtrim($row['CFHHLC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyHolidayPayCodeDefault&amp;fldDesc=hourlyHolidayPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyHolidayPayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSHLC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryHolidayPayCodeDefault\" value=\"" . rtrim($row['CFSHLC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryHolidayPayCodeDefault&amp;fldDesc=salaryHolidayPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryHolidayPayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHHLC);
			DspErrMsg($Err_CFSHLC);

			print "\n <tr><td class=\"dsphdr\">Sick</td>";
			$textOvr=SetTextOvr($Err_CFHSCC);
			$textOvr=SetTextOvr($Err_CFSSCC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHSCC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlySickPayCodeDefault\" value=\"" . rtrim($row['CFHSCC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlySickPayCodeDefault&amp;fldDesc=hourlySickPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlySickPayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSSCC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salarySickPayCodeDefault\" value=\"" . rtrim($row['CFSSCC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salarySickPayCodeDefault&amp;fldDesc=salarySickPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salarySickPayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHSCC);
			DspErrMsg($Err_CFSSCC);

			print "\n <tr><td class=\"dsphdr\">Other Taxable</td>";
			$textOvr=SetTextOvr($Err_CFHOTC);
			$textOvr=SetTextOvr($Err_CFSOTC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHOTC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyOtherTaxablePayCodeDefault\" value=\"" . rtrim($row['CFHOTC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyOtherTaxablePayCodeDefault&amp;fldDesc=hourlyOtherTaxablePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyOtherTaxablePayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSOTC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryOtherTaxablePayCodeDefault\" value=\"" . rtrim($row['CFSOTC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryOtherTaxablePayCodeDefault&amp;fldDesc=salaryOtherTaxablePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryOtherTaxablePayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHOTC);
			DspErrMsg($Err_CFSOTC);

			print "\n <tr><td class=\"dsphdr\">Nontaxable</td>";
			$textOvr=SetTextOvr($Err_CFHNTC);
			$textOvr=SetTextOvr($Err_CFSNTC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHNTC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyNonTaxablePayCodeDefault\" value=\"" . rtrim($row['CFHNTC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyNonTaxablePayCodeDefault&amp;fldDesc=hourlyNonTaxablePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyNonTaxablePayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSNTC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryNonTaxablePayCodeDefault\" value=\"" . rtrim($row['CFSNTC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryNonTaxablePayCodeDefault&amp;fldDesc=salaryNonTaxablePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryNonTaxablePayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHNTC);
			DspErrMsg($Err_CFSNTC);

			print "\n <tr><td class=\"dsphdr\">Commission</td>";
			$textOvr=SetTextOvr($Err_CFHCMC);
			$textOvr=SetTextOvr($Err_CFSCMC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHCMC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyCommissionPayCodeDefault\" value=\"" . rtrim($row['CFHCMC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyCommissionPayCodeDefault&amp;fldDesc=hourlyCommissionPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyCommissionPayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSCMC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryCommissionPayCodeDefault\" value=\"" . rtrim($row['CFSCMC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryCommissionPayCodeDefault&amp;fldDesc=salaryCommissionPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryCommissionPayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHCMC);
			DspErrMsg($Err_CFSCMC);

			print "\n <tr><td class=\"dsphdr\">Bonus</td>";
			$textOvr=SetTextOvr($Err_CFHBNC);
			$textOvr=SetTextOvr($Err_CFSBNC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHBNC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyBonusPayCodeDefault\" value=\"" . rtrim($row['CFHBNC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyBonusPayCodeDefault&amp;fldDesc=hourlyBonusPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyBonusPayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSBNC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryBonusPayCodeDefault\" value=\"" . rtrim($row['CFSBNC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryBonusPayCodeDefault&amp;fldDesc=salaryBonusPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryBonusPayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHBNC);
			DspErrMsg($Err_CFSBNC);

			print "\n <tr><td class=\"dsphdr\">Fringe</td>";
			$textOvr=SetTextOvr($Err_CFHFGC);
			$textOvr=SetTextOvr($Err_CFSFGC);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHFGC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyFringePayCodeDefault\" value=\"" . rtrim($row['CFHFGC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyFringePayCodeDefault&amp;fldDesc=hourlyFringePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyFringePayCodeDefaultDesc\">$fieldDesc</span></td>";
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFSFGC]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"salaryFringePayCodeDefault\" value=\"" . rtrim($row['CFSFGC']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=salaryFringePayCodeDefault&amp;fldDesc=salaryFringePayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"salaryFringePayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHFGC);
			DspErrMsg($Err_CFSFGC);

			print "\n <tr><td class=\"dsphdr\">Min. Wage Makeup</td>";
			$textOvr=SetTextOvr($Err_CFHMWM);
			$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHMWM]'", "PRCODE", "C2DESC");
			print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyMinWageMakeupPayCodeDefault\" value=\"" . rtrim($row['CFHMWM']) . "\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyMinWageMakeupPayCodeDefault&amp;fldDesc=hourlyMinWageMakeupPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"hourlyMinWageMakeupPayCodeDefaultDesc\">$fieldDesc</span></td>";
			print "\n </tr> ";
			DspErrMsg($Err_CFHMWM);

			if ($row['PCTIP']="Y") {
				print "\n <tr><td class=\"dsphdr\">Cash Tips</td>";
				$textOvr=SetTextOvr($Err_CFHCTC);
				$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHCTC]'", "PRCODE", "C2DESC");
				print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyCashTipsPayCodeDefault\" value=\"" . rtrim($row['CFHCTC']) . "\" size=\"3\" maxlength=\"3\">";
				print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyCashTipsPayCodeDefault&amp;fldDesc=hourlyCashTipsPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
				print "\n     <span class=\"dspdesc\" id=\"hourlyCashTipsPayCodeDefaultDesc\">$fieldDesc</span></td>";
				print "\n </tr> ";
				DspErrMsg($Err_CFHCTC);

				print "\n <tr><td class=\"dsphdr\">Charge Tips</td>";
				$textOvr=SetTextOvr($Err_CFHGTC);
				$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHGTC]'", "PRCODE", "C2DESC");
				print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyChargeTipsPayCodeDefault\" value=\"" . rtrim($row['CFHGTC']) . "\" size=\"3\" maxlength=\"3\">";
				print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyChargeTipsPayCodeDefault&amp;fldDesc=hourlyChargeTipsPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
				print "\n     <span class=\"dspdesc\" id=\"hourlyChargeTipsPayCodeDefaultDesc\">$fieldDesc</span></td>";
				print "\n </tr> ";
				DspErrMsg($Err_CFHGTC);

				print "\n <tr><td class=\"dsphdr\">Gross Receipts</td>";
				$textOvr=SetTextOvr($Err_CFHGRC);
				$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHGRC]'", "PRCODE", "C2DESC");
				print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyGrossReceiptsPayCodeDefault\" value=\"" . rtrim($row['CFHGRC']) . "\" size=\"3\" maxlength=\"3\">";
				print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyGrossReceiptsPayCodeDefault&amp;fldDesc=hourlyGrossReceiptsPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
				print "\n     <span class=\"dspdesc\" id=\"hourlyGrossReceiptsPayCodeDefaultDesc\">$fieldDesc</span></td>";
				print "\n </tr> ";
				DspErrMsg($Err_CFHGRC);

				print "\n <tr><td class=\"dsphdr\">Meal Allowance</td>";
				$textOvr=SetTextOvr($Err_CFHMLC);
				$fieldDesc=RetValue("C2COMP=$fromCo and C2FACL=$fromFac and C2CODE='$row[CFHMLC]'", "PRCODE", "C2DESC");
				print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"hourlyMealAllowPayCodeDefault\" value=\"" . rtrim($row['CFHMLC']) . "\" size=\"3\" maxlength=\"3\">";
				print "\n                             <a href=\"{$homeURL}{$phpPath}PayCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=hourlyMealAllowPayCodeDefault&amp;fldDesc=hourlyMealAllowPayCodeDefaultDesc&amp;forHRCo=$fromCo&amp;forHRFac=$fromFac\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a>";
				print "\n     <span class=\"dspdesc\" id=\"hourlyMealAllowPayCodeDefaultDesc\">$fieldDesc</span></td>";
				print "\n </tr> ";
				DspErrMsg($Err_CFHMLC);

			} else {

				$row['CFHCTC']="";
				$row['CFHGTC']="";
				$row['CFHGRC']="";
				$row['CFHMLC']="";
			}
			print "\n </table> ";
			print "\n </fieldset> ";

			// Screen 8
			print "\n <a name=\"payrollPayTransactionEntryDefaults\"></a> ";
			print "\n <fieldset class=\"legendBody\"> ";
			print "\n <legend class=\"legendTitle\">Pay Transaction Entry Defaults</legend> ";
			require 'TopOfForm.php';
			print "\n <table $contentTable>";
			print "\n <tr><td>&nbsp;</td><td class=\"colhdr\">Hourly</td><td class=\"colhdr\">Salaried</td></tr> ";

			print "\n <tr><td class=\"dsphdr\">Detail Or Summary Hours Entry</td>";
			$textOvr=SetTextOvr($Err_CFHD01);
			$textOvr=SetTextOvr($Err_CFSD01);
			Build_Fld_Entry("","hourlyDetOrSumHrsEntry","inputalph","DORS","CFHD01",$row[CFHD01],$Err_CFHD01,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryDetOrSumHrsEntry","inputalph","DORS","CFSD01",$row[CFSD01],$Err_CFSD01,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD01);
			DspErrMsg($Err_CFSD01);

			print "\n <tr><td class=\"dsphdr\">Elapsed Hours Or Start/Stop</td>";
			$textOvr=SetTextOvr($Err_CFHD02);
			$textOvr=SetTextOvr($Err_CFSD02);
			Build_Fld_Entry("","hourlyElapsedHrsOrStartStop","inputalph","HOURSFMT","CFHD02",$row[CFHD02],$Err_CFHD02,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryElapsedHrsOrStartStop","inputalph","HOURSFMT","CFSD02",$row[CFSD02],$Err_CFSD02,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD02);
			DspErrMsg($Err_CFSD02);

			print "\n <tr><td class=\"dsphdr\">Suppress Date Worked</td>";
			$textOvr=SetTextOvr($Err_CFHD03);
			$textOvr=SetTextOvr($Err_CFSD03);
			Build_Fld_Entry("","hourlySuppressDateWorked","inputalph","YORN","CFHD03",$row[CFHD03],$Err_CFHD03,"1","1","Y","","Y");
			Build_Fld_Entry("","salarySuppressDateWorked","inputalph","YORN","CFSD03",$row[CFSD03],$Err_CFSD03,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD03);
			DspErrMsg($Err_CFSD03);

			print "\n <tr><td class=\"dsphdr\">Date Default</td>";
			$textOvr=SetTextOvr($Err_CFHD04);
			$textOvr=SetTextOvr($Err_CFSD04);
			Build_Fld_Entry("","hourlyDateDefault","inputalph","DATEDFLT","CFHD04",$row[CFHD04],$Err_CFHD04,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryDateDefault","inputalph","DATEDFLT","CFSD04",$row[CFSD04],$Err_CFSD04,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD04);
			DspErrMsg($Err_CFSD04);

			print "\n <tr><td class=\"dsphdr\">Pay Code</td>";
			$textOvr=SetTextOvr($Err_CFHD05);
			$textOvr=SetTextOvr($Err_CFSD05);
			Build_Fld_Entry("","hourlyPayCode","inputalph","CODEDFLT","CFHD05",$row[CFHD05],$Err_CFHD05,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryPayCode","inputalph","CODEDFLT","CFSD05",$row[CFSD05],$Err_CFSD05,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD05);
			DspErrMsg($Err_CFSD05);

			print "\n <tr><td class=\"dsphdr\">Shift Worked</td>";
			$textOvr=SetTextOvr($Err_CFHD06);
			$textOvr=SetTextOvr($Err_CFSD06);
			Build_Fld_Entry("","hourlyShiftWorked","inputalph","SHIFTDFLT","CFHD06",$row[CFHD06],$Err_CFHD06,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryShiftWorked","inputalph","SHIFTDFLT","CFSD06",$row[CFSD06],$Err_CFSD06,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD06);
			DspErrMsg($Err_CFSD06);

			print "\n <tr><td class=\"dsphdr\">Department Worked</td>";
			$textOvr=SetTextOvr($Err_CFHD07);
			$textOvr=SetTextOvr($Err_CFSD07);
			Build_Fld_Entry("","hourlyDeptWorked","inputalph","DEPTDFLT","CFHD07",$row[CFHD07],$Err_CFHD07,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryDeptWorked","inputalph","DEPTDFLT","CFSD07",$row[CFSD07],$Err_CFSD07,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD07);
			DspErrMsg($Err_CFSD07);

			print "\n <tr><td class=\"dsphdr\">Job Number</td>";
			$textOvr=SetTextOvr($Err_CFHD08);
			$textOvr=SetTextOvr($Err_CFSD08);
			Build_Fld_Entry("","hourlyJobNumb","inputalph","JOBDFLT","CFHD08",$row[CFHD08],$Err_CFHD08,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryJobNumb","inputalph","JOBDFLT","CFSD08",$row[CFSD08],$Err_CFSD08,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD08);
			DspErrMsg($Err_CFSD08);

			print "\n <tr><td class=\"dsphdr\">Job Class</td>";
			$textOvr=SetTextOvr($Err_CFHD09);
			$textOvr=SetTextOvr($Err_CFSD09);
			Build_Fld_Entry("","hourlyJobClass","inputalph","CLASSDFLT","CFHD09",$row[CFHD09],$Err_CFHD09,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryJobClass","inputalph","CLASSDFLT","CFSD09",$row[CFSD09],$Err_CFSD09,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD09);
			DspErrMsg($Err_CFSD09);

			print "\n <tr><td class=\"dsphdr\">Override Pay Rate</td>";
			$textOvr=SetTextOvr($Err_CFHD10);
			$textOvr=SetTextOvr($Err_CFSD10);
			Build_Fld_Entry("","hourlyOverridePayRate","inputalph","RATEDFLT","CFHD10",$row[CFHD10],$Err_CFHD10,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryOverridePayRate","inputalph","RATEDFLT","CFSD10",$row[CFSD10],$Err_CFSD10,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD10);
			DspErrMsg($Err_CFSD10);

			print "\n <tr><td class=\"dsphdr\">Override G/L Account</td>";
			$textOvr=SetTextOvr($Err_CFHD11);
			$textOvr=SetTextOvr($Err_CFSD11);
			Build_Fld_Entry("","hourlyOverrideGLAcct","inputalph","ACCTDFLT","CFHD11",$row[CFHD11],$Err_CFHD11,"1","1","Y","","Y");
			Build_Fld_Entry("","salaryOverrideGLAcct","inputalph","ACCTDFLT","CFSD11",$row[CFSD11],$Err_CFSD11,"1","1","Y","","Y");
			print "\n </tr> ";
			DspErrMsg($Err_CFHD11);
			DspErrMsg($Err_CFSD11);
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
}

if ($tag == "Edit_Data") {
	if ($maintenanceCode=="D" && is_null($_POST['coNum'])) {
		$_POST['coNum']  = $fromCo;
		$_POST['facNum'] = $fromFac;
		$_POST['coName'] = $_GET['fromName'];
	}

	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";

	if ($hrCo=='Y') {

		//Screen 1 - Human Resources Co
		Concat_Field("@@tstp", $_POST['originalTimeStamp']);
		Concat_Field("@@comp", $_POST['coNum']);
		Concat_Field("@@facl", $_POST['facNum']);
		Concat_Field("@@name", $_POST['coName']);
		Concat_Field("@@adr1", $_POST['address1']);
		Concat_Field("@@adr2", $_POST['address2']);
		Concat_Field("@@city", $_POST['city']);
		$_POST['state']=strtoupper($_POST['state']);  Concat_Field("@@stid", $_POST['state']);
		Concat_Field("@@zip@", $_POST['zipCode']);
		Concat_Field("@@phon", $_POST['phone']);
		Concat_Field("@@sbh@", $_POST['weeklyStdBudgetHrs']);

	} else {

		//Screen 1 - Co/Fac
		if ($maintenanceCode == "A") {
			Concat_Field("@@cco@", $copyCo);
			Concat_Field("@@cfac", $copyFac);
		}
		Concat_Field("@@tstp", $_POST['originalTimeStamp']);
		Concat_Field("@@comp", $_POST['coNum']);
		Concat_Field("@@facl", $_POST['facNum']);
		Concat_Field("@@name", $_POST['coName']);
		Concat_Field("@@adr1", $_POST['address1']);
		Concat_Field("@@adr2", $_POST['address2']);
		Concat_Field("@@city", $_POST['city']);
		$_POST['state']=strtoupper($_POST['state']);  Concat_Field("@@stid", $_POST['state']);
		Concat_Field("@@zip@", $_POST['zipCode']);
		Concat_Field("@@phon", $_POST['phone']);
		Concat_Field("@@hrxr", $_POST['coHRNum']);

		//Screen 2
		$_POST['stmtCode']=strtoupper($_POST['stmtCode']);  Concat_Field("@@stcd", $_POST['stmtCode']);
		Concat_Field("@@day@", $_POST['premPeriodStrDay']);
		Concat_Field("@@cut@", $_POST['cutDays']);
		Concat_Field("@@due@", $_POST['daysUntilDue']);
		Concat_Field("@@grac", $_POST['gracePeriodDays']);
		if (!isset($_POST['retainDetTransHist'])) {$_POST['retainDetTransHist']="N";}
		Concat_Field("@@rdth", $_POST['retainDetTransHist']=strtoupper($_POST['retainDetTransHist']));
		if (!isset($_POST['manualGenNoticeDefault'])) {$_POST['manualGenNoticeDefault']="N";}
		Concat_Field("@@mgn@", $_POST['manualGenNoticeDefault']=strtoupper($_POST['manualGenNoticeDefault']));
		if (!isset($_POST['manualContNoticeDefault'])) {$_POST['manualContNoticeDefault']="N";}
		Concat_Field("@@mcn@", $_POST['manualContNoticeDefault']=strtoupper($_POST['manualContNoticeDefault']));
		if (!isset($_POST['manualConvNoticeDefault'])) {$_POST['manualConvNoticeDefault']="N";}
		Concat_Field("@@mvn@", $_POST['manualConvNoticeDefault']=strtoupper($_POST['manualConvNoticeDefault']));

		//Screen 3
		Concat_Field("@@bkno", $_POST['bank']);
		$_POST['ovrBankAcctNumb']=strtoupper($_POST['ovrBankAcctNumb']);  Concat_Field("@@acct", $_POST['ovrBankAcctNumb']);
		Concat_Field("@@begb", $_POST['ovrBegBatchNumb']);
		if (!isset($_POST['chkRecon'])) {$_POST['chkRecon']="N";}
		Concat_Field("@@ckrc", $_POST['chkRecon']=strtoupper($_POST['chkRecon']));
		if (!isset($_POST['suppPayTransEntryRates'])) {$_POST['suppPayTransEntryRates']="N";}
		Concat_Field("@@prtr", $_POST['suppPayTransEntryRates']=strtoupper($_POST['suppPayTransEntryRates']));
		$_POST['timeEntryFormFormat']=strtoupper($_POST['timeEntryFormFormat']);  Concat_Field("@@prts", $_POST['timeEntryFormFormat']);
		if (!isset($_POST['prtTimeCardLabels'])) {$_POST['prtTimeCardLabels']="N";}
		Concat_Field("@@htcl", $_POST['prtTimeCardLabels']=strtoupper($_POST['prtTimeCardLabels']));
		if (!isset($_POST['actvGLFeed'])) {$_POST['actvGLFeed']="N";}
		Concat_Field("@@fgl@", $_POST['actvGLFeed']=strtoupper($_POST['actvGLFeed']));
		if (!isset($_POST['valGLAccts'])) {$_POST['valGLAccts']="N";}
		Concat_Field("@@vgl@", $_POST['valGLAccts']=strtoupper($_POST['valGLAccts']));
		$_POST['prtOverFlowAdv']=strtoupper($_POST['prtOverFlowAdv']);  Concat_Field("@@pf11", $_POST['prtOverFlowAdv']);
		if (!isset($_POST['prtEmplr401kOnChks'])) {$_POST['prtEmplr401kOnChks']="N";}
		Concat_Field("@@p401", $_POST['prtEmplr401kOnChks']=strtoupper($_POST['prtEmplr401kOnChks']));
		if (!isset($_POST['runResetAccrEachPer'])) {$_POST['runResetAccrEachPer']="N";}
		Concat_Field("@@pf12", $_POST['runResetAccrEachPer']=strtoupper($_POST['runResetAccrEachPer']));
		if (!isset($_POST['certPayrollHist'])) {$_POST['certPayrollHist']="N";}
		Concat_Field("@@cpr@", $_POST['certPayrollHist']=strtoupper($_POST['certPayrollHist']));
		if (!isset($_POST['tipPayroll'])) {$_POST['tipPayroll']="N";}
		Concat_Field("@@tip@", $_POST['tipPayroll']=strtoupper($_POST['tipPayroll']));
		Concat_Field("@@tcp@", $_POST['tipCredPct']);
		Concat_Field("@@mwr@", $_POST['minWage']);
		Concat_Field("@@opwg", $_POST['opportWage']);
		$_POST['estIDNumb']=strtoupper($_POST['estIDNumb']);  Concat_Field("@@ein@", $_POST['estIDNumb']);

		//Screen 4
		Concat_Field("@@rfcr", $_POST['emplrFICAOasdiTaxPct']);
		Concat_Field("@@rhir", $_POST['emplrFICAHiTaxPct']);
		Concat_Field("@@efcr", $_POST['empleeFICAOasdiTaxPct']);
		Concat_Field("@@ehir", $_POST['empleeFICAHiTaxPct']);
		Concat_Field("@@ficb", $_POST['FICAOasdiMaxTaxInc']);
		Concat_Field("@@hib@", $_POST['FICAHiMaxTaxInc']);
		Concat_Field("@@fuir", $_POST['FUICompPct']);
		Concat_Field("@@fuib", $_POST['FUICompMaxWage']);
		Concat_Field("@@otm@", $_POST['ovrTimeMult']);
		Concat_Field("@@dtm@", $_POST['dblTimeMult']);
		$_POST['chkPrtPgmName']=strtoupper($_POST['chkPrtPgmName']);  Concat_Field("@@cpgm", $_POST['chkPrtPgmName']);
		if (!isset($_POST['prtCoFacNameAndAdrOnChk'])) {$_POST['prtCoFacNameAndAdrOnChk']="N";}
		Concat_Field("@@pcna", $_POST['prtCoFacNameAndAdrOnChk']=strtoupper($_POST['prtCoFacNameAndAdrOnChk']));
		Concat_Field("@@wgda", $_POST['interCoWageAcctsDueFromAcct']);
		Concat_Field("@@wgds", $_POST['interCoWageAcctsDueFromSubAcct']);
		Concat_Field("@@wgca", $_POST['interCoWageAcctsDueToAcct']);
		Concat_Field("@@wgcs", $_POST['interCoWageAcctsDueToSubAcct']);
		Concat_Field("@@txda", $_POST['interCoTaxAcctsDueFromAcct']);
		Concat_Field("@@txds", $_POST['interCoTaxAcctsDueFromSubAcct']);
		Concat_Field("@@txca", $_POST['interCoTaxAcctsDueToAcct']);
		Concat_Field("@@txcs", $_POST['interCoTaxAcctsDueToSubAcct']);

		//Screen 5
		if (!isset($_POST['vacaValHrs'])) {$_POST['vacaValHrs']="N";}
		Concat_Field("@@vach", $_POST['vacaValHrs']=strtoupper($_POST['vacaValHrs']));
		if (!isset($_POST['sickValHrs'])) {$_POST['sickValHrs']="N";}
		Concat_Field("@@sich", $_POST['sickValHrs']=strtoupper($_POST['sickValHrs']));
		if (!isset($_POST['vacaValEarnings'])) {$_POST['vacaValEarnings']="N";}
		Concat_Field("@@vacd", $_POST['vacaValEarnings']=strtoupper($_POST['vacaValEarnings']));
		if (!isset($_POST['sickValEarnings'])) {$_POST['sickValEarnings']="N";}
		Concat_Field("@@sicd", $_POST['sickValEarnings']=strtoupper($_POST['sickValEarnings']));
		if (!isset($_POST['vacaExpAsEarned'])) {$_POST['vacaExpAsEarned']="N";}
		Concat_Field("@@exvc", $_POST['vacaExpAsEarned']=strtoupper($_POST['vacaExpAsEarned']));
		if (!isset($_POST['sickExpAsEarned'])) {$_POST['sickExpAsEarned']="N";}
		Concat_Field("@@exsc", $_POST['sickExpAsEarned']=strtoupper($_POST['sickExpAsEarned']));
		if (!isset($_POST['vacaPrtAvailOnChks'])) {$_POST['vacaPrtAvailOnChks']="N";}
		Concat_Field("@@prvc", $_POST['vacaPrtAvailOnChks']=strtoupper($_POST['vacaPrtAvailOnChks']));
		if (!isset($_POST['sickPrtAvailOnChks'])) {$_POST['sickPrtAvailOnChks']="N";}
		Concat_Field("@@prsc", $_POST['sickPrtAvailOnChks']=strtoupper($_POST['sickPrtAvailOnChks']));
		Concat_Field("@@othd", $_POST['overtimeAftNumbHrsPerDay']);
		Concat_Field("@@othw", $_POST['overtimeAftNumbHrsPerWeek']);
		Concat_Field("@@mxhr", $_POST['maxHourlyRate']);
		Concat_Field("@@mxga", $_POST['maxGrossAmt']);

		//Screen 6
		$_POST['employeesNotPaidRpt']=strtoupper($_POST['employeesNotPaidRpt']);  Concat_Field("@@pf13", $_POST['employeesNotPaidRpt']);
		$_POST['grossHrsPayByDeptWorked']=strtoupper($_POST['grossHrsPayByDeptWorked']);  Concat_Field("@@ghpw", $_POST['grossHrsPayByDeptWorked']);
		$_POST['grossHrsPayByHomeDept']=strtoupper($_POST['grossHrsPayByHomeDept']);  Concat_Field("@@ghph", $_POST['grossHrsPayByHomeDept']);
		$_POST['payDtlRpt']=strtoupper($_POST['payDtlRpt']);  Concat_Field("@@pf1@", $_POST['payDtlRpt']);
		$_POST['expDistrByAcct']=strtoupper($_POST['expDistrByAcct']);  Concat_Field("@@pf2@", $_POST['expDistrByAcct']);
		$_POST['expDistrByDept']=strtoupper($_POST['expDistrByDept']);  Concat_Field("@@pf3@", $_POST['expDistrByDept']);
		$_POST['preTaxEdit']=strtoupper($_POST['preTaxEdit']);  Concat_Field("@@prdd", $_POST['preTaxEdit']);
		$_POST['taxComputationEdit']=strtoupper($_POST['taxComputationEdit']);  Concat_Field("@@ptcd", $_POST['taxComputationEdit']);
		$_POST['postTaxDeductEdit']=strtoupper($_POST['postTaxDeductEdit']);  Concat_Field("@@podd", $_POST['postTaxDeductEdit']);
		$_POST['four01kRpt']=strtoupper($_POST['four01kRpt']);  Concat_Field("@@pf5@", $_POST['four01kRpt']);
		$_POST['vacaaccrualRpt']=strtoupper($_POST['vacaaccrualRpt']);  Concat_Field("@@pf6@", $_POST['vacaaccrualRpt']);
		$_POST['sickaccrualRpt']=strtoupper($_POST['sickaccrualRpt']);  Concat_Field("@@pf7@", $_POST['sickaccrualRpt']);
		$_POST['FSAContribAndPayments']=strtoupper($_POST['FSAContribAndPayments']);  Concat_Field("@@pf8@", $_POST['FSAContribAndPayments']);
		$_POST['certifiedPayrollEdit']=strtoupper($_POST['certifiedPayrollEdit']);  Concat_Field("@@pf9@", $_POST['certifiedPayrollEdit']);
		$_POST['dirDepositEdit']=strtoupper($_POST['dirDepositEdit']);  Concat_Field("@@pf4@", $_POST['dirDepositEdit']);
		$_POST['payrollBalRpt']=strtoupper($_POST['payrollBalRpt']);  Concat_Field("@@pf10", $_POST['payrollBalRpt']);
		$_POST['payrollRegister']=strtoupper($_POST['payrollRegister']);  Concat_Field("@@pprd", $_POST['payrollRegister']);

		//Screen 7
		if (!isset($_POST['paycodecheckbox'])) {$_POST['paycodecheckbox']="N";}
		Concat_Field("@@acpc", $_POST['paycodecheckbox']=strtoupper($_POST['paycodecheckbox']));
		$_POST['hourlyRegPayCodeDefault']=strtoupper($_POST['hourlyRegPayCodeDefault']);  Concat_Field("@@hrgc", $_POST['hourlyRegPayCodeDefault']);
		$_POST['hourlyOvertimePayCodeDefault']=strtoupper($_POST['hourlyOvertimePayCodeDefault']);  Concat_Field("@@hovc", $_POST['hourlyOvertimePayCodeDefault']);
		$_POST['hourlyDBLtimePayCodeDefault']=strtoupper($_POST['hourlyDBLtimePayCodeDefault']);  Concat_Field("@@hdtc", $_POST['hourlyDBLtimePayCodeDefault']);
		$_POST['hourlyVacationPayCodeDefault']=strtoupper($_POST['hourlyVacationPayCodeDefault']);  Concat_Field("@@hvcc", $_POST['hourlyVacationPayCodeDefault']);
		$_POST['hourlyHolidayPayCodeDefault']=strtoupper($_POST['hourlyHolidayPayCodeDefault']);  Concat_Field("@@hhlc", $_POST['hourlyHolidayPayCodeDefault']);
		$_POST['hourlySickPayCodeDefault']=strtoupper($_POST['hourlySickPayCodeDefault']);  Concat_Field("@@hscc", $_POST['hourlySickPayCodeDefault']);
		$_POST['hourlyOtherTaxablePayCodeDefault']=strtoupper($_POST['hourlyOtherTaxablePayCodeDefault']);  Concat_Field("@@hotc", $_POST['hourlyOtherTaxablePayCodeDefault']);
		$_POST['hourlyNonTaxablePayCodeDefault']=strtoupper($_POST['hourlyNonTaxablePayCodeDefault']);  Concat_Field("@@hntc", $_POST['hourlyNonTaxablePayCodeDefault']);
		$_POST['hourlyCommissionPayCodeDefault']=strtoupper($_POST['hourlyCommissionPayCodeDefault']);  Concat_Field("@@hcmc", $_POST['hourlyCommissionPayCodeDefault']);
		$_POST['hourlyBonusPayCodeDefault']=strtoupper($_POST['hourlyBonusPayCodeDefault']);  Concat_Field("@@hbnc", $_POST['hourlyBonusPayCodeDefault']);
		$_POST['hourlyFringePayCodeDefault']=strtoupper($_POST['hourlyFringePayCodeDefault']);  Concat_Field("@@hfgc", $_POST['hourlyFringePayCodeDefault']);
		$_POST['hourlyMinWageMakeupPayCodeDefault']=strtoupper($_POST['hourlyMinWageMakeupPayCodeDefault']);  Concat_Field("@@hmwm", $_POST['hourlyMinWageMakeupPayCodeDefault']);
		$_POST['hourlyCashTipsPayCodeDefault']=strtoupper($_POST['hourlyCashTipsPayCodeDefault']);  Concat_Field("@@hctc", $_POST['hourlyCashTipsPayCodeDefault']);
		$_POST['hourlyChargeTipsPayCodeDefault']=strtoupper($_POST['hourlyChargeTipsPayCodeDefault']);  Concat_Field("@@hgtc", $_POST['hourlyChargeTipsPayCodeDefault']);
		$_POST['hourlyGrossReceiptsPayCodeDefault']=strtoupper($_POST['hourlyGrossReceiptsPayCodeDefault']);  Concat_Field("@@hgrc", $_POST['hourlyGrossReceiptsPayCodeDefault']);
		$_POST['hourlyMealAllowPayCodeDefault']=strtoupper($_POST['hourlyMealAllowPayCodeDefault']);  Concat_Field("@@hmlc", $_POST['hourlyMealAllowPayCodeDefault']);
		$_POST['salaryRegPayCodeDefault']=strtoupper($_POST['salaryRegPayCodeDefault']);  Concat_Field("@@srgc", $_POST['salaryRegPayCodeDefault']);
		$_POST['salaryOvertimePayCodeDefault']=strtoupper($_POST['salaryOvertimePayCodeDefault']);  Concat_Field("@@sovc", $_POST['salaryOvertimePayCodeDefault']);
		$_POST['salaryDBLtimePayCodeDefault']=strtoupper($_POST['salaryDBLtimePayCodeDefault']);  Concat_Field("@@sdtc", $_POST['salaryDBLtimePayCodeDefault']);
		$_POST['salaryVacationPayCodeDefault']=strtoupper($_POST['salaryVacationPayCodeDefault']);  Concat_Field("@@svcc", $_POST['salaryVacationPayCodeDefault']);
		$_POST['salaryHolidayPayCodeDefault']=strtoupper($_POST['salaryHolidayPayCodeDefault']);  Concat_Field("@@shlc", $_POST['salaryHolidayPayCodeDefault']);
		$_POST['salarySickPayCodeDefault']=strtoupper($_POST['salarySickPayCodeDefault']);  Concat_Field("@@sscc", $_POST['salarySickPayCodeDefault']);
		$_POST['salaryOtherTaxablePayCodeDefault']=strtoupper($_POST['salaryOtherTaxablePayCodeDefault']);  Concat_Field("@@sotc", $_POST['salaryOtherTaxablePayCodeDefault']);
		$_POST['salaryNonTaxablePayCodeDefault']=strtoupper($_POST['salaryNonTaxablePayCodeDefault']);  Concat_Field("@@sntc", $_POST['salaryNonTaxablePayCodeDefault']);
		$_POST['salaryCommissionPayCodeDefault']=strtoupper($_POST['salaryCommissionPayCodeDefault']);  Concat_Field("@@scmc", $_POST['salaryCommissionPayCodeDefault']);
		$_POST['salaryBonusPayCodeDefault']=strtoupper($_POST['salaryBonusPayCodeDefault']);  Concat_Field("@@sbnc", $_POST['salaryBonusPayCodeDefault']);
		$_POST['salaryFringePayCodeDefault']=strtoupper($_POST['salaryFringePayCodeDefault']);  Concat_Field("@@sfgc", $_POST['salaryFringePayCodeDefault']);

		//Screen 8
		$_POST['hourlyDetOrSumHrsEntry']=strtoupper($_POST['hourlyDetOrSumHrsEntry']);  Concat_Field("@@hd01", $_POST['hourlyDetOrSumHrsEntry']);
		$_POST['hourlyElapsedHrsOrStartStop']=strtoupper($_POST['hourlyElapsedHrsOrStartStop']);  Concat_Field("@@hd02", $_POST['hourlyElapsedHrsOrStartStop']);
		if (!isset($_POST['hourlySuppressDateWorked'])) {$_POST['hourlySuppressDateWorked']="N";}
		Concat_Field("@@hd03", $_POST['hourlySuppressDateWorked']=strtoupper($_POST['hourlySuppressDateWorked']));
		$_POST['hourlyDateDefault']=strtoupper($_POST['hourlyDateDefault']);  Concat_Field("@@hd04", $_POST['hourlyDateDefault']);
		$_POST['hourlyPayCode']=strtoupper($_POST['hourlyPayCode']);  Concat_Field("@@hd05", $_POST['hourlyPayCode']);
		$_POST['hourlyShiftWorked']=strtoupper($_POST['hourlyShiftWorked']);  Concat_Field("@@hd06", $_POST['hourlyShiftWorked']);
		$_POST['hourlyDeptWorked']=strtoupper($_POST['hourlyDeptWorked']);  Concat_Field("@@hd07", $_POST['hourlyDeptWorked']);
		$_POST['hourlyJobNumb']=strtoupper($_POST['hourlyJobNumb']);  Concat_Field("@@hd08", $_POST['hourlyJobNumb']);
		$_POST['hourlyJobClass']=strtoupper($_POST['hourlyJobClass']);  Concat_Field("@@hd09", $_POST['hourlyJobClass']);
		$_POST['hourlyOverridePayRate']=strtoupper($_POST['hourlyOverridePayRate']);  Concat_Field("@@hd10", $_POST['hourlyOverridePayRate']);
		$_POST['hourlyOverrideGLAcct']=strtoupper($_POST['hourlyOverrideGLAcct']);  Concat_Field("@@hd11", $_POST['hourlyOverrideGLAcct']);
		$_POST['salaryDetOrSumHrsEntry']=strtoupper($_POST['salaryDetOrSumHrsEntry']);  Concat_Field("@@sd01", $_POST['salaryDetOrSumHrsEntry']);
		$_POST['salaryElapsedHrsOrStartStop']=strtoupper($_POST['salaryElapsedHrsOrStartStop']);  Concat_Field("@@sd02", $_POST['salaryElapsedHrsOrStartStop']);
		if (!isset($_POST['salarySuppressDateWorked'])) {$_POST['salarySuppressDateWorked']="N";}
		Concat_Field("@@sd03", $_POST['salarySuppressDateWorked']=strtoupper($_POST['salarySuppressDateWorked']));
		$_POST['salaryDateDefault']=strtoupper($_POST['salaryDateDefault']);  Concat_Field("@@sd04", $_POST['salaryDateDefault']);
		$_POST['salaryPayCode']=strtoupper($_POST['salaryPayCode']);  Concat_Field("@@sd05", $_POST['salaryPayCode']);
		$_POST['salaryShiftWorked']=strtoupper($_POST['salaryShiftWorked']);  Concat_Field("@@sd06", $_POST['salaryShiftWorked']);
		$_POST['salaryDeptWorked']=strtoupper($_POST['salaryDeptWorked']);  Concat_Field("@@sd07", $_POST['salaryDeptWorked']);
		$_POST['salaryJobNumb']=strtoupper($_POST['salaryJobNumb']);  Concat_Field("@@sd08", $_POST['salaryJobNumb']);
		$_POST['salaryJobClass']=strtoupper($_POST['salaryJobClass']);  Concat_Field("@@sd09", $_POST['salaryJobClass']);
		$_POST['salaryOverridePayRate']=strtoupper($_POST['salaryOverridePayRate']);  Concat_Field("@@sd10", $_POST['salaryOverridePayRate']);
		$_POST['salaryOverrideGLAcct']=strtoupper($_POST['salaryOverrideGLAcct']);  Concat_Field("@@sd11", $_POST['salaryOverrideGLAcct']);
	}
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HHRCOU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($maintenanceCode == "R") {$maintenanceCode= "Re";}
		if ($maintenanceCode == "Re") {$maintenanceCode= "R";}
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, "$_POST[coName]", "$_POST[coNum] / $_POST[facNum]", "", "", "", "");
		} else {
			$Err_CFCOMP=DecatErr_Field("@@comp", "coNum");
			$Err_CFFACL=DecatErr_Field("@@facl", "facNum");
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, "$_POST[coName]", "$_POST[coNum] / $_POST[facNum]", "", "", "", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;coNum=" . urlencode(trim($_POST['coNum'])) . "&amp;facNum=" . urlencode(trim($_POST['facNum'])) . "&amp;hrCo=" . urlencode(trim($hrCo)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

if ($tag == "CHANGEALL") {
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

	print "\n if (editZero(document.Chg.FICAOasdiMaxTaxInc, 7, 2) && ";
	print "\n     editZero(document.Chg.FICAHiMaxTaxInc, 7, 2) && ";
	print "\n     editZero(document.Chg.FUICompMaxWage, 5, 2)) ";
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
	$pageID = "HRCOFACMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";

	$stmtSQL .= " Select CFFICB, CFHIB, CFFUIB  ";
	$stmtSQL .= " From HRCOFC ";
	$stmtSQL .= " Where CFCOMP>0 and CFFACL>0 ";
	$stmtSQL .= " Fetch First 1 Rows Only";

	// Program Option Security
	$hhrcou_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01="N";
	$sec_02=$hhrcou_OPT['sec_02'];
	$sec_03="N";
	$sec_04="N";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "") {
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$Err_CFFICB=DecatErr_Field("@@ficb", "FICAOasdiMaxTaxInc");
		$Err_CFHIB =DecatErr_Field("@@hib@", "FICAHiMaxTaxInc");
		$Err_CFFUIB=DecatErr_Field("@@fuib", "FUICompMaxWage");
		$errFound= "";

		$row['CFFICB']=Decat_Field("@@ficb", $edtVar);
		$row['CFHIB'] =Decat_Field("@@hib@", $edtVar);
		$row['CFFUIB']=Decat_Field("@@fuib", $edtVar);
	}

	$focusField= "FICAOasdiMaxTaxInc";

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data_All&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	Build_Fld_Entry("FICA OASDI Maximum Taxable Income","FICAOasdiMaxTaxInc","inputnmbr","","CFFICB",$row[CFFICB],$Err_CFFICB,"11","11","","","");
	Build_Fld_Entry("FICA HI Maximum Taxable Income","FICAHiMaxTaxInc","inputnmbr","","CFHIB",$row[CFHIB],$Err_CFHIB,"11","11","","","");
	Build_Fld_Entry("FUI Compensation Maximum Wage","FUICompMaxWage","inputnmbr","","CFFUIB",$row[CFFUIB],$Err_CFFUIB,"9","8","","","");
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

if ($tag == "Edit_Data_All") {

	$edtVar= "";
	Concat_Field("@@ficb", $_POST['FICAOasdiMaxTaxInc']);
	Concat_Field("@@hib@", $_POST['FICAHiMaxTaxInc']);
	Concat_Field("@@fuib", $_POST['FUICompMaxWage']);
	$edtVar .= "}{";

	require 'stmtSQLClear.php';
	$stmtSQL .= " Update HRCOFC Set CFFICB=$_POST[FICAOasdiMaxTaxInc], CFHIB=$_POST[FICAHiMaxTaxInc], CFFUIB=$_POST[FUICompMaxWage] Where CFFACL <>0 ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	$confMessage=Format_ConfMsg_Desc("", "All Data Updated", "", "", "", "", "");
	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
}

if ($tag == "ADD") {

	$hrCo="";

	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	require_once 'Menu.js';
	require_once 'NoFormValidate.php';
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "HRCOFACMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";

	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';
	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Add&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	print "\n <tr><td class=\"dsphdr\">Selection</td> ";
	print "\n     <td class=\"inputalph\"><input type=\"radio\" name=\"hrCo\" VALUE='N' CHECKED>Company/Facility</td></tr> ";
	print "\n <tr><td></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"radio\" name=\"hrCo\" VALUE='Y'>Human Resources Company</td></tr> ";
	print "\n </table>";
	print "\n </form>";
	require_once 'MaintainBottom.php';
	print $hrTagAttr;
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Add") {
	$hrCo = $_POST['hrCo'];
	print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;hrCo=" . urlencode(trim($hrCo)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
}
?>