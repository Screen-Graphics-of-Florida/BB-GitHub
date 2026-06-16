<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$backHome           = $_GET['backHome'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "A/R Control Maintenance";
$scriptName     = "ARControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HARCTU_E";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "MAINTAIN") {
	$RecCount=RetValue("RRN(ARCTRL)=1", "ARCTRL", "Char(Count(*))");
	$maintenanceCode="C";

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
	print "\n if (document.Chg.ReclaimResourceLevel.value ==\"\" ";
	print "\n  || document.Chg.LocationPrimary.value ==\"\" ";
	print "\n  || document.Chg.MiscellaneousCashCustomer.value ==\"\" ";
	print "\n  || document.Chg.MiscellaneousCashInvoice.value ==\"\" ";
	print "\n  || document.Chg.ARDistributionPeriod.value ==\"\" ";
	if ($HDVLCH=="Y") {
		print "\n  || (document.Chg.ARAccount.value ==\"\" && document.Chg.ARSubaccount.value ==\"\") ";
		print "\n  || (document.Chg.SalesTaxAccount.value ==\"\" && document.Chg.SalesTaxSubaccount.value ==\"\") ";
		print "\n  || (document.Chg.FreightAccount.value ==\"\" && document.Chg.FreightSubaccount.value ==\"\") ";
		print "\n  || (document.Chg.SpecialChargesAccount.value ==\"\" && document.Chg.SpecialChargesSubaccount.value ==\"\") ";
	}
	print "\n  || document.Chg.AgeCreditInvoicesBasedOn.value ==\"\" ";
	print "\n  || document.Chg.BaseAgingOnCode.value ==\"\" ";
	print "\n  || document.Chg.AgingBucket1NumberOfDays.value ==\"\" ";
	print "\n  || document.Chg.AgingBucket2NumberOfDays.value ==\"\" ";
	print "\n  || document.Chg.AgingBucket3NumberOfDays.value ==\"\" ";
	print "\n  || document.Chg.AgingBucket4NumberOfDays.value ==\"\"  ";
	print "\n  || document.Chg.AssignUnappliedCash.value ==\"\"  ";
	print "\n  || document.Chg.AssignGeneralDeduction.value ==\"\"  ";
	print "\n  || document.Chg.AssignSpecificDeduction.value ==\"\"  ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";

	print "\n if (editNum(document.Chg.MiscellaneousCashCustomer, 7, 0) && ";
	print "\n     editNum(document.Chg.MiscellaneousCashInvoice, 7, 0) && ";
	print "\n     editNum(document.Chg.LastCustomerUsed, 7, 0) && ";
	print "\n     editNum(document.Chg.LastPayerUsed, 7, 0) && ";
	print "\n     editNum(document.Chg.LastCashPostingBatchUsed, 4, 0) && ";
	print "\n     editNum(document.Chg.OEBatch, 4, 0) && ";
	print "\n     editNum(document.Chg.NSFBatch, 4, 0) && ";
	print "\n     editNum(document.Chg.InvoiceEntryBatch, 4, 0) && ";
	print "\n     editNum(document.Chg.ARDistributionPeriod, 4, 0) && ";
	print "\n     editNum(document.Chg.ARAccount, 4, 0) && ";
	print "\n     editNum(document.Chg.ARSubaccount, 4, 0) && ";
	print "\n     editNum(document.Chg.SalesTaxAccount, 4, 0) && ";
	print "\n     editNum(document.Chg.SalesTaxSubaccount, 4, 0) && ";
	print "\n     editNum(document.Chg.FreightAccount, 4, 0) && ";
	print "\n     editNum(document.Chg.FreightSubaccount, 4, 0) && ";
	print "\n     editNum(document.Chg.SpecialChargesAccount, 4, 0) && ";
	print "\n     editNum(document.Chg.SpecialChargesSubaccount, 4, 0) && ";
	print "\n     editNum(document.Chg.AgingBucket1NumberOfDays, 3, 0) && ";
	print "\n     editNum(document.Chg.AgingBucket2NumberOfDays, 3, 0) && ";
	print "\n     editNum(document.Chg.AgingBucket3NumberOfDays, 3, 0) && ";
	print "\n     editNum(document.Chg.AgingBucket4NumberOfDays, 3, 0) && ";
	print "\n     editNum(document.Chg.NewCustomerDefaultsCreditLimit, 7, 0) && ";
	print "\n     editNum(document.Chg.NewCustomerDefaultsLocation, 3, 0) && ";
	print "\n     editNum(document.Chg.NewCustomerDefaultsSalesman, 3, 0) && ";
	print "\n     editNum(document.Chg.NewCustomerDefaultsWarehouse, 3, 0) && ";
	print "\n     editNum(document.Chg.FactorBank, 2, 0) && ";
	print "\n     editNum(document.Chg.FactoringARAccount, 4, 0) && ";
	print "\n     editNum(document.Chg.FactoringARSubaccount, 4, 0) && ";
	print "\n     editNum(document.Chg.FactoringLiabilityAccount, 4, 0) && ";
	print "\n     editNum(document.Chg.FactoringLiabilitySubaccount, 4, 0) &&  ";
	print "\n     editNum(document.Chg.GraceDaysGivenForDiscount, 4, 0) &&  ";
	print "\n     editNum(document.Chg.UnappliedCashInvoice, 7, 0) &&  ";
	print "\n     editNum(document.Chg.GeneralDeductionInvoice, 7, 0) &&  ";
	print "\n     editNum(document.Chg.SpecificDeductionInvoice, 7, 0) ";
	print "\n ) return true; ";
	print "\n } ";
	print "\n  function confirmDelete() {return confirm(\"$delRecordConf\")} ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARCONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($RecCount == 0) {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From ARCTRL ";
		$stmtSQL .= " Where RRN(ARCTRL)=1 ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$harctu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$harctu_OPT['sec_01'];
	$sec_02=$harctu_OPT['sec_02'];
	$sec_03="N";
	$sec_04="N";
	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";

	print "\n       <td class=\"quickLinkTabs\"><a href=\"#general\">General</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#autoAssign\">Auto Assign</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#accounting\">Accounting</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#aging\">Aging</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#newCustomerDefaults\">New Customer Defaults</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#factoring\">Factoring</a></td> ";
	print "\n   </tr> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#deduction\">Deduction</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#NSF\">NSF</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#userDefined\">User Defined</a></td> ";
	if ($HDMCRL>0) {print "\n       <td class=\"quickLinkTabs\"><a href=\"#multiCurrency\">Multi-Currency</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#businessRules\">Application Of Cash Business Rules</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField= "ReclaimResourceLevel";
	if ($errFound != "" || $RecCount==0) {
		if ($errFound == "" && $RecCount==0) {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_CRRCLR=DecatErr_Field("@@rclr", "ReclaimResourceLevel");
			$Err_CRLPRI=DecatErr_Field("@@lpri", "LocationPrimary");
			$Err_CRBBAL=DecatErr_Field("@@bbal", "DepositBalancingRequired");
			$Err_CRMSCC=DecatErr_Field("@@mscc", "MiscellaneousCashCustomer");
			$Err_CRMSCI=DecatErr_Field("@@msci", "MiscellaneousCashInvoice");
			$Err_CRAACN=DecatErr_Field("@@aacn", "AutoAssignCustomer");
			$Err_CRLCNU=DecatErr_Field("@@lcnu", "LastCustomerUsed");
			$Err_CRAPYR=DecatErr_Field("@@apyr", "AutoAssignPayer");
			$Err_CRLPYR=DecatErr_Field("@@lpyr", "LastPayerUsed");
			$Err_CRABCH=DecatErr_Field("@@abch", "AutoAssignCashPostingBatch");
			$Err_CRPBCH=DecatErr_Field("@@pbch", "LastCashPostingBatchUsed");
			$Err_CROEBH=DecatErr_Field("@@oebh", "OEBatch");
			$Err_CRNSBH=DecatErr_Field("@@nsbh", "NSFBatch");
			$Err_CRIEBH=DecatErr_Field("@@iebh", "InvoiceEntryBatch");
			$Err_CRDPER=DecatErr_Field("@@dper", "ARDistributionPeriod");
			$Err_CRARGL=DecatErr_Field("@@argl", "FeedARToGL");
			$Err_CRFEDS=DecatErr_Field("@@feds", "GLDistributionFeed");
			$Err_CRFPDS=DecatErr_Field("@@fpds", "GLPaymentFeed");
			$Err_CRAUTO=DecatErr_Field("@@auto", "RestoreARFeedIfErrorsExist");
			$Err_CRINTR=DecatErr_Field("@@intr", "GenerateInterCompanyTransactions");
			$Err_CRARAC=DecatErr_Field("@@arac", "ARAccount");
			$Err_CRARSB=DecatErr_Field("@@arsb", "ARSubaccount");
			$Err_CRTXAC=DecatErr_Field("@@txac", "SalesTaxAccount");
			$Err_CRTXSB=DecatErr_Field("@@txsb", "SalesTaxSubaccount");
			$Err_CRFRAC=DecatErr_Field("@@frac", "FreightAccount");
			$Err_CRFRSB=DecatErr_Field("@@frsb", "FreightSubaccount");
			$Err_CRSCAC=DecatErr_Field("@@scac", "SpecialChargesAccount");
			$Err_CRSCSB=DecatErr_Field("@@scsb", "SpecialChargesSubaccount");
			$Err_CRCIAO=DecatErr_Field("@@ciao", "AgeCreditInvoicesBasedOn");
			$Err_CRBAON=DecatErr_Field("@@baon", "BaseAgingOnCode");
			$Err_CRAGE1=DecatErr_Field("@@age1", "AgingBucket1NumberOfDays");
			$Err_CRAGE2=DecatErr_Field("@@age2", "AgingBucket2NumberOfDays");
			$Err_CRAGE3=DecatErr_Field("@@age3", "AgingBucket3NumberOfDays");
			$Err_CRAGE4=DecatErr_Field("@@age4", "AgingBucket4NumberOfDays");
			$Err_CRCCRL=DecatErr_Field("@@ccrl", "NewCustomerDefaultsCreditLimit");
			$Err_CRCTRM=DecatErr_Field("@@ctrm", "NewCustomerDefaultsTermsCode");
			$Err_CRCSCC=DecatErr_Field("@@cscc", "NewCustomerDefaultsServiceCharge");
			$Err_CRCSTC=DecatErr_Field("@@cstc", "NewCustomerDefaultsStatement");
			$Err_CRCRGN=DecatErr_Field("@@crgn", "NewCustomerDefaultsCustomerRegion");
			$Err_CRCTRY=DecatErr_Field("@@ctry", "NewCustomerDefaultsCustomerCountry");
			$Err_CRLOC =DecatErr_Field("@@loc@", "NewCustomerDefaultsLocation");
			$Err_CRCCLS=DecatErr_Field("@@ccls", "NewCustomerDefaultsClassCode");
			$Err_CRSLSM=DecatErr_Field("@@slsm", "NewCustomerDefaultsSalesman");
			$Err_CRSVIA=DecatErr_Field("@@svia", "NewCustomerDefaultsShipVia");
			$Err_CRWHS =DecatErr_Field("@@whs@", "NewCustomerDefaultsWarehouse");
			$Err_CRPARF=DecatErr_Field("@@parf", "ProcessARFactoring");
			$Err_CRPYCD=DecatErr_Field("@@pycd", "FactoringPaymentCode");
			$Err_CRFBNK=DecatErr_Field("@@fbnk", "FactorBank");
			$Err_CRFARA=DecatErr_Field("@@fara", "FactoringARAccount");
			$Err_CRFARS=DecatErr_Field("@@fars", "FactoringARSubaccount");
			$Err_CRFLIA=DecatErr_Field("@@flia", "FactoringLiabilityAccount");
			$Err_CRFLIS=DecatErr_Field("@@flis", "FactoringLiabilitySubaccount");
			$Err_CRDPYC=DecatErr_Field("@@dpyc", "DeductionPaymentCode");
			$Err_CRDTRM=DecatErr_Field("@@dtrm", "DeductionTermsCode");
			$Err_CRDDTE=DecatErr_Field("@@ddte", "DeductionDate");
			$Err_CRNPYC=DecatErr_Field("@@npyc", "NSFPaymentCode");
			$Err_CRNCOL=DecatErr_Field("@@ncol", "NSFCollectionStatus");
			$Err_CRSCR1=DecatErr_Field("@@scr1", "GenericDescription1");
			$Err_CRSCR2=DecatErr_Field("@@scr2", "GenericDescription2");
			$Err_CRSCR3=DecatErr_Field("@@scr3", "GenericDescription3");
			$Err_CRSCR4=DecatErr_Field("@@scr4", "GenericDescription4");
			$Err_CRSCR5=DecatErr_Field("@@scr5", "GenericDescription5");
			$Err_CRRP11=DecatErr_Field("@@rp11", "GenericReportHeading11");
			$Err_CRRP12=DecatErr_Field("@@rp12", "GenericReportHeading12");
			$Err_CRRP21=DecatErr_Field("@@rp21", "GenericReportHeading21");
			$Err_CRRP22=DecatErr_Field("@@rp22", "GenericReportHeading22");
			$Err_CRRP31=DecatErr_Field("@@rp31", "GenericReportHeading31");
			$Err_CRRP32=DecatErr_Field("@@rp32", "GenericReportHeading32");
			$Err_CRRP41=DecatErr_Field("@@rp41", "GenericReportHeading41");
			$Err_CRRP42=DecatErr_Field("@@rp42", "GenericReportHeading42");
			$Err_CRRP51=DecatErr_Field("@@rp51", "GenericReportHeading51");
			$Err_CRRP52=DecatErr_Field("@@rp52", "GenericReportHeading52");
			if ($HDMCRL>0)  {
				$Err_CRPRMC=DecatErr_Field("@@prmc", "ProcessMultiCurrency");
				$Err_CRMCRT=DecatErr_Field("@@mcrt", "ARCurrencyRateType");
			}
			$Err_CRDSPD=DecatErr_Field("@@dspd", "GraceDaysGivenForDiscount");
			$Err_CRUINV=DecatErr_Field("@@uinv", "UnappliedCashInvoice");
			$Err_CRUAUI=DecatErr_Field("@@uaui", "AssignUnappliedCash");
			$Err_CRGINV=DecatErr_Field("@@ginv", "GeneralDeductionInvoice");
			$Err_CRGAUI=DecatErr_Field("@@gaui", "AssignGeneralDeduction");
			$Err_CRIINV=DecatErr_Field("@@iinv", "SpecificDeductionInvoice");
			$Err_CRIAUI=DecatErr_Field("@@iaui", "AssignSpecificDeduction");
			$Err_CRTSTP=DecatErr_Field("@@tstp", "originalTimeStamp");
		}
		$row['CRRCLR']=Decat_Field("@@rclr", $edtVar);
		$row['CRLPRI']=Decat_Field("@@lpri", $edtVar);
		$row['CRBBAL']=Decat_Field("@@bbal", $edtVar);
		$row['CRMSCC']=Decat_Field("@@mscc", $edtVar);
		$row['CRMSCI']=Decat_Field("@@msci", $edtVar);
		$row['CRAACN']=Decat_Field("@@aacn", $edtVar);
		$row['CRLCNU']=Decat_Field("@@lcnu", $edtVar);
		$row['CRAPYR']=Decat_Field("@@apyr", $edtVar);
		$row['CRLPYR']=Decat_Field("@@lpyr", $edtVar);
		$row['CRABCH']=Decat_Field("@@abch", $edtVar);
		$row['CRPBCH']=Decat_Field("@@pbch", $edtVar);
		$row['CROEBH']=Decat_Field("@@oebh", $edtVar);
		$row['CRNSBH']=Decat_Field("@@nsbh", $edtVar);
		$row['CRIEBH']=Decat_Field("@@iebh", $edtVar);
		$row['CRDPER']=Decat_Field("@@dper", $edtVar);
		$row['CRARGL']=Decat_Field("@@argl", $edtVar);
		$row['CRFEDS']=Decat_Field("@@feds", $edtVar);
		$row['CRFPDS']=Decat_Field("@@fpds", $edtVar);
		$row['CRAUTO']=Decat_Field("@@auto", $edtVar);
		$row['CRINTR']=Decat_Field("@@intr", $edtVar);
		$row['CRARAC']=Decat_Field("@@arac", $edtVar);
		$row['CRARSB']=Decat_Field("@@arsb", $edtVar);
		$row['CRTXAC']=Decat_Field("@@txac", $edtVar);
		$row['CRTXSB']=Decat_Field("@@txsb", $edtVar);
		$row['CRFRAC']=Decat_Field("@@frac", $edtVar);
		$row['CRFRSB']=Decat_Field("@@frsb", $edtVar);
		$row['CRSCAC']=Decat_Field("@@scac", $edtVar);
		$row['CRSCSB']=Decat_Field("@@scsb", $edtVar);
		$row['CRCIAO']=Decat_Field("@@ciao", $edtVar);
		$row['CRBAON']=Decat_Field("@@baon", $edtVar);
		$row['CRAGE1']=Decat_Field("@@age1", $edtVar);
		$row['CRAGE2']=Decat_Field("@@age2", $edtVar);
		$row['CRAGE3']=Decat_Field("@@age3", $edtVar);
		$row['CRAGE4']=Decat_Field("@@age4", $edtVar);
		$row['CRCCRL']=Decat_Field("@@ccrl", $edtVar);
		$row['CRCTRM']=Decat_Field("@@ctrm", $edtVar);
		$row['CRCSCC']=Decat_Field("@@cscc", $edtVar);
		$row['CRCSTC']=Decat_Field("@@cstc", $edtVar);
		$row['CRCRGN']=Decat_Field("@@crgn", $edtVar);
		$row['CRCTRY']=Decat_Field("@@ctry", $edtVar);
		$row['CRLOC']=Decat_Field("@@loc@", $edtVar);
		$row['CRCCLS']=Decat_Field("@@ccls", $edtVar);
		$row['CRSLSM']=Decat_Field("@@slsm", $edtVar);
		$row['CRSVIA']=Decat_Field("@@svia", $edtVar);
		$row['CRWHS'] =Decat_Field("@@whs@", $edtVar);
		$row['CRPARF']=Decat_Field("@@parf", $edtVar);
		$row['CRPYCD']=Decat_Field("@@pycd", $edtVar);
		$row['CRFBNK']=Decat_Field("@@fbnk", $edtVar);
		$row['CRFARA']=Decat_Field("@@fara", $edtVar);
		$row['CRFARS']=Decat_Field("@@fars", $edtVar);
		$row['CRFLIA']=Decat_Field("@@flia", $edtVar);
		$row['CRFLIS']=Decat_Field("@@flis", $edtVar);
		$row['CRDPYC']=Decat_Field("@@dpyc", $edtVar);
		$row['CRDTRM']=Decat_Field("@@dtrm", $edtVar);
		$row['CRDDTE']=Decat_Field("@@ddte", $edtVar);
		$row['CRNPYC']=Decat_Field("@@npyc", $edtVar);
		$row['CRNCOL']=Decat_Field("@@ncol", $edtVar);
		$row['CRSCR1']=Decat_Field("@@scr1", $edtVar);
		$row['CRSCR2']=Decat_Field("@@scr2", $edtVar);
		$row['CRSCR3']=Decat_Field("@@scr3", $edtVar);
		$row['CRSCR4']=Decat_Field("@@scr4", $edtVar);
		$row['CRSCR5']=Decat_Field("@@scr5", $edtVar);
		$row['CRRP11']=Decat_Field("@@rp11", $edtVar);
		$row['CRRP12']=Decat_Field("@@rp12", $edtVar);
		$row['CRRP21']=Decat_Field("@@rp21", $edtVar);
		$row['CRRP22']=Decat_Field("@@rp22", $edtVar);
		$row['CRRP31']=Decat_Field("@@rp31", $edtVar);
		$row['CRRP32']=Decat_Field("@@rp32", $edtVar);
		$row['CRRP41']=Decat_Field("@@rp41", $edtVar);
		$row['CRRP42']=Decat_Field("@@rp42", $edtVar);
		$row['CRRP51']=Decat_Field("@@rp51", $edtVar);
		$row['CRRP52']=Decat_Field("@@rp52", $edtVar);
		if ($HDMCRL>0)  {
			$row['CRPRMC']=Decat_Field("@@prmc", $edtVar);
			$row['CRMCRT']=Decat_Field("@@mcrt", $edtVar);
		}
		$row['CRDSPD']=Decat_Field("@@dspd", $edtVar);
		$row['CRUINV']=Decat_Field("@@uinv", $edtVar);
		$row['CRUAUI']=Decat_Field("@@uaui", $edtVar);
		$row['CRGINV']=Decat_Field("@@ginv", $edtVar);
		$row['CRGAUI']=Decat_Field("@@gaui", $edtVar);
		$row['CRIINV']=Decat_Field("@@iinv", $edtVar);
		$row['CRIAUI']=Decat_Field("@@iaui", $edtVar);
		$row['CRTSTP']=Decat_Field("@@tstp", $edtVar);

		if ($errFound=="" && $RecCount==0) {
			$row['CRRCLR']="O";
			$row['CRINTR']="N";
			$row['CRARGL']="N";
			$row['CRFEDS']="S";
			$row['CRFPDS']="S";
			$row['CRAUTO']="N";
			$row['CRCSCC']="N";
			$row['CRCSTC']="Y";
			$row['CRCIAO']="O";
			$row['CRBAON']="D";
			$row['CRAACN']="N";
			$row['CRAPYR']="N";
			$row['CRLPRI']="I";
			$row['CRPARF']="N";
			$row['CRPRMC']="N";
			$row['CRBBAL']="N";
			$row['CRABCH']="N";
			$row['CRDDTE']="P";
			$row['CRUAUI']="E";
			$row['CRGAUI']="E";
			$row['CRIAUI']="E";
		}
	} else {
		$row['CRDPER']=PeriodInputFromCYP($row['CRDPER']);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_CRTSTP);
	Build_DspFld("A/R Release Version",$HDARRL,"","A");
	DspErrMsg($Err_CRTSTP);
	Build_DspFld("A/R Library Level",$HDARLL,"","A");
	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['CRTSTP']) . "\"></td></tr> ";
	print "\n </table> ";

	print "\n <a name=\"general\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">General</legend> ";
	require 'TopOfForm.php';
	print "\n         <table $contentTable>";
	Build_Fld_Entry("Reclaim Resource Level","ReclaimResourceLevel","inputalph","RECLAIMLVL","CRRCLR",$row[CRRCLR],$Err_CRRCLR,"1","1","Y","","");
	Build_Fld_Entry("Location Primary","LocationPrimary","inputalph","LOCPRIM","CRLPRI",$row[CRLPRI],$Err_CRLPRI,"1","1","Y","","");
	Build_Fld_Entry("Deposit Balancing Required","DepositBalancingRequired","inputalph","YORN","CRBBAL",$row[CRBBAL],$Err_CRBBAL,"1","1","Y","","");
	print "\n         </table> ";

	print "\n         <fieldset class=\"legendBody\"> ";
	print "\n             <legend class=\"legendTitle\">Miscellaneous Cash</legend> ";
	print "\n             <table $contentTable> ";
	$fieldDesc=RetValue("CMCUST=$row[CRMSCC]", "HDCUST", "CMCNA1");
	$textOvr=SetTextOvr($Err_CRMSCC);
	print "\n                 <tr><td class=\"dsphdr\"><span $textOvr>Miscellaneous Cash Customer</span></td> ";
	print "\n                     <td class=\"inputnmbr\"><input type=\"text\" name=\"MiscellaneousCashCustomer\" value=\"" . rtrim($row['CRMSCC']) . "\" size=\"7\" maxlength=\"7\"> ";
	print "\n                                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=MiscellaneousCashCustomer&amp;fldDesc=MiscellaneousCashCustomerName\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n                                             <span class=\"dspdesc\" id=\"MiscellaneousCashCustomerName\">$fieldDesc</span></td>";
	print "\n                 </tr> ";
	DspErrMsg($Err_CRMSCC);

	Build_Fld_Entry("Miscellaneous Cash Invoice Number","MiscellaneousCashInvoice","inputalph","","CRMSCI",$row[CRMSCI],$Err_CRMSCI,"7","7","Y","","");
	print "\n             </table> ";
	print "\n         </fieldset> ";
	print "\n </fieldset> ";

	print "\n <a name=\"autoAssign\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Auto Assign</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">Auto Assign</td> ";
	print "\n             <td class=\"colhdr\">Last Used</td> ";
	print "\n         </tr> ";

	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer</span></td> ";
	Build_Fld_Entry("Customer","AutoAssignCustomer","inputalph","YORN","CRAACN",$row[CRAACN],$Err_CRAACN,"1","1","","","Y");
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"LastCustomerUsed\" value=\"" . rtrim($row['CRLCNU']) . "\" size=\"7\" maxlength=\"7\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRAACN);

	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Payer</span></td> ";
	Build_Fld_Entry("Payer","AutoAssignPayer","inputalph","YORN","CRAPYR",$row[CRAPYR],$Err_CRAPYR,"1","1","","","Y");
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"LastPayerUsed\" value=\"" . rtrim($row['CRLPYR']) . "\" size=\"7\" maxlength=\"7\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRAPYR);

	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>A/R Payment Batch</span></td> ";
	Build_Fld_Entry("A/R Payment Batch","AutoAssignCashPostingBatch","inputalph","YORN","CRABCH",$row[CRABCH],$Err_CRABCH,"1","1","","","Y");
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"LastCashPostingBatchUsed\" value=\"" . rtrim($row['CRPBCH']) . "\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRABCH);

	Build_Fld_Entry("Order Entry Batch","OEBatch","inputnmbr","","CROEBH",$row[CROEBH],$Err_CROEBH,"4","4","","","");
	Build_Fld_Entry("NSF Batch","NSFBatch","inputnmbr","","CRNSBH",$row[CRNSBH],$Err_CRNSBH,"4","4","","","");
	Build_Fld_Entry("Invoice Entry Batch","InvoiceEntryBatch","inputnmbr","","CRIEBH",$row[CRIEBH],$Err_CRIEBH,"4","4","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"accounting\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Accounting</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	$textOvr=SetTextOvr($Err_CRDPER);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>A/R Distribution Period</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"ARDistributionPeriod\" value=\"" . rtrim($row['CRDPER']) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;docName=Chg&amp;periodFld=ARDistributionPeriod&amp;beginDateFld=begindate&amp;endDateFld=enddate\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n                                     <input type=\"hidden\" name=\"begindate\"><input type=\"hidden\" name=\"enddate\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRDPER);

	Build_Fld_Entry("Feed A/R To G/L","FeedARToGL","inputalph","YORN","CRARGL",$row[CRARGL],$Err_CRARGL,"1","1","Y","","");
	Build_Fld_Entry("G/L Distribution Feed","GLDistributionFeed","inputalph","DORS","CRFEDS",$row[CRFEDS],$Err_CRFEDS,"1","1","","","");
	Build_Fld_Entry("G/L Payment Feed","GLPaymentFeed","inputalph","DORS","CRFPDS",$row[CRFPDS],$Err_CRFPDS,"1","1","","","");
	Build_Fld_Entry("Restore A/R Feed If Errors Exist","RestoreARFeedIfErrorsExist","inputalph","YORN","CRAUTO",$row[CRAUTO],$Err_CRAUTO,"1","1","Y","","");
	Build_Fld_Entry("Generate Inter-Company Transactions","GenerateInterCompanyTransactions","inputalph","INTRCO","CRINTR",$row[CRINTR],$Err_CRINTR,"1","1","","","");
	print "\n     </table> ";

	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">Account</td> ";
	print "\n         </tr> ";

	$row['CRARAC']=Default_Zero($row['CRARAC']);  $row['CRARSB']=Default_Zero($row['CRARSB']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CRARAC],$row[CRARSB])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CRARAC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>A/R</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"ARAccount\" value=\"" . rtrim($row['CRARAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                                     <input type=\"text\" name=\"ARSubaccount\" value=\"" . rtrim($row['CRARSB']) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=ARAccount&amp;subFld=ARSubaccount&amp;descFld=ARAccountDesc\" onclick=\"$searchWinVar\"> "; if ($HDVLCH=="Y") {print $reqFieldChar; } print " $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"ARAccountDesc\">$fieldDesc</span></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRARAC);

	$row['CRTXAC']=Default_Zero($row['CRTXAC']);  $row['CRTXSB']=Default_Zero($row['CRTXSB']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CRTXAC],$row[CRTXSB])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CRTXAC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Sales Tax</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"SalesTaxAccount\" value=\"" . rtrim($row['CRTXAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                                     <input type=\"text\" name=\"SalesTaxSubaccount\" value=\"" . rtrim($row['CRTXSB']) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=SalesTaxAccount&amp;subFld=SalesTaxSubaccount&amp;descFld=SalesTaxAccountDesc\" onclick=\"$searchWinVar\"> "; if ($HDVLCH=="Y") {print $reqFieldChar; } print " $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"SalesTaxAccountDesc\">$fieldDesc</span></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRTXAC);

	$row['CRFRAC']=Default_Zero($row['CRFRAC']);  $row['CRFRSB']=Default_Zero($row['CRFRSB']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CRFRAC],$row[CRFRSB])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CRFRAC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Freight</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"FreightAccount\" value=\"" . rtrim($row['CRFRAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                                     <input type=\"text\" name=\"FreightSubaccount\" value=\"" . rtrim($row['CRFRSB']) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=FreightAccount&amp;subFld=FreightSubaccount&amp;descFld=FreightAccountDesc\" onclick=\"$searchWinVar\"> "; if ($HDVLCH=="Y") {print $reqFieldChar; } print " $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"FreightAccountDesc\">$fieldDesc</span></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRFRAC);

	$row['CRSCAC']=Default_Zero($row['CRSCAC']);  $row['CRSCSB']=Default_Zero($row['CRSCSB']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CRSCAC],$row[CRSCSB])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CRSCAC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Special Charges</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"SpecialChargesAccount\" value=\"" . rtrim($row['CRSCAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                                     <input type=\"text\" name=\"SpecialChargesSubaccount\" value=\"" . rtrim($row['CRSCSB']) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=SpecialChargesAccount&amp;subFld=SpecialChargesSubaccount&amp;descFld=SpecialChargesAccountDesc\" onclick=\"$searchWinVar\"> "; if ($HDVLCH=="Y") {print $reqFieldChar; } print " $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"SpecialChargesAccountDesc\">$fieldDesc</span></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRSCAC);
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"aging\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Aging</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Age Credit Invoices Based On","AgeCreditInvoicesBasedOn","inputalph","AGEARCRDT","CRCIAO",$row[CRCIAO],$Err_CRCIAO,"1","1","Y","","");
	Build_Fld_Entry("Base Aging On Code","BaseAgingOnCode","inputalph","AGEDATE","CRBAON",$row[CRBAON],$Err_CRBAON,"1","1","Y","","");
	print "\n     </table> ";

	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">Aging Number Of Days</td> ";
	print "\n         </tr> ";
	Build_Fld_Entry("Bucket 1","AgingBucket1NumberOfDays","inputnmbr","","CRAGE1",$row[CRAGE1],$Err_CRAGE1,"3","3","Y","","");
	Build_Fld_Entry("Bucket 2","AgingBucket2NumberOfDays","inputnmbr","","CRAGE2",$row[CRAGE2],$Err_CRAGE2,"3","3","Y","","");
	Build_Fld_Entry("Bucket 3","AgingBucket3NumberOfDays","inputnmbr","","CRAGE3",$row[CRAGE3],$Err_CRAGE3,"3","3","Y","","");
	Build_Fld_Entry("Bucket 4","AgingBucket4NumberOfDays","inputnmbr","","CRAGE4",$row[CRAGE4],$Err_CRAGE4,"3","3","Y","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"newCustomerDefaults\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">New Customer Defaults</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Credit Limit","NewCustomerDefaultsCreditLimit","inputnmbr","","CRCCRL",$row[CRCCRL],$Err_CRCCRL,"7","7","","","");

	$fieldDesc=RetValue("TMCTRM='$row[CRCTRM]'", "HDTRMS", "TMCTDS");
	$textOvr=SetTextOvr($Err_CRCTRM);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Terms Code</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"NewCustomerDefaultsTermsCode\" value=\"" . rtrim($row['CRCTRM']) . "\" size=\"7\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NewCustomerDefaultsTermsCode&amp;fldDesc=NewCustomerDefaultsTermsCodeDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"NewCustomerDefaultsTermsCodeDesc\">$fieldDesc</span></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRCTRM);

	Build_Fld_Entry("Service Charge","NewCustomerDefaultsServiceCharge","inputalph","YORN","CRCSCC",$row[CRCSCC],$Err_CRCSCC,"1","1","Y","","");
	Build_Fld_Entry("Statement","NewCustomerDefaultsStatement","inputalph","YORN","CRCSTC",$row[CRCSTC],$Err_CRCSTC,"1","1","Y","","");

	$fieldDesc=RetValue("RGCRGN='$row[CRCRGN]'", "HDCRGN", "RGCRDS");
	$textOvr=SetTextOvr($Err_CRCRGN);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Region</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"NewCustomerDefaultsCustomerRegion\" value=\"" . rtrim($row['CRCRGN']) . "\" size=\"7\" maxlength=\"5\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}RegionSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NewCustomerDefaultsCustomerRegion&amp;fldDesc=NewCustomerDefaultsCustomerRegionDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"NewCustomerDefaultsCustomerRegionDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRCRGN);

	$fieldDesc=RetValue("CNCTCD='$row[CRCTRY]'", "HDCTRY", "CNCDES");
	$textOvr=SetTextOvr($Err_CRCTRY);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Country</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"NewCustomerDefaultsCustomerCountry\" value=\"" . rtrim($row['CRCTRY']) . "\" size=\"7\" maxlength=\"3\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CountrySearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NewCustomerDefaultsCustomerCountry&amp;fldDesc=NewCustomerDefaultsCustomerCountryDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"NewCustomerDefaultsCustomerCountryDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRCTRY);

	$row['CRLOC']=Default_Zero($row['CRLOC']);
	$fieldDesc=RetValue("LOLOC#=$row[CRLOC]", "HDLCTN", "LOLNA1");
	$textOvr=SetTextOvr($Err_CRLOC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Location</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"NewCustomerDefaultsLocation\" value=\"" . rtrim($row['CRLOC']) . "\" size=\"7\" maxlength=\"3\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NewCustomerDefaultsLocation&amp;fldDesc=NewCustomerDefaultsLocationDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"NewCustomerDefaultsLocationDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRLOC);

	$fieldDesc=RetValue("CCCCLS='$row[CRCCLS]'", "HDCCLS", "CCCCDS");
	$textOvr=SetTextOvr($Err_CRCCLS);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Class Code</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"NewCustomerDefaultsClassCode\" value=\"" . rtrim($row['CRCCLS']) . "\" size=\"7\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CustomerClassSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NewCustomerDefaultsClassCode&amp;fldDesc=NewCustomerDefaultsClassCodeDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"NewCustomerDefaultsClassCodeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRCCLS);

	$row['CRSLSM']=Default_Zero($row['CRSLSM']);
	$fieldDesc=RetValue("SMSLSM=$row[CRSLSM]", "HDSLSM", "SMSNA1");
	$textOvr=SetTextOvr($Err_CRSLSM);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Salesman</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"NewCustomerDefaultsSalesman\" value=\"" . rtrim($row['CRSLSM']) . "\" size=\"7\" maxlength=\"3\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NewCustomerDefaultsSalesman&amp;fldDesc=NewCustomerDefaultsSalesmanName\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"NewCustomerDefaultsSalesmanName\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRSLSM);

	$fieldDesc=RetValue("SVSVSV='$row[CRSVIA]'", "HDSHPV", "SVSVDS");
	$textOvr=SetTextOvr($Err_CRSVIA);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Ship Via</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"NewCustomerDefaultsShipVia\" value=\"" . rtrim($row['CRSVIA']) . "\" size=\"7\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}ShipViaSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NewCustomerDefaultsShipVia&amp;fldDesc=NewCustomerDefaultsShipViaDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"NewCustomerDefaultsShipViaDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRSVIA);

	$row['CRWHS']=Default_Zero($row['CRWHS']);
	$fieldDesc=RetValue("WHWHS=$row[CRWHS]", "HDWHSM", "WHWHNM");
	$textOvr=SetTextOvr($Err_CRWHS);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Warehouse</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"NewCustomerDefaultsWarehouse\" value=\"" . rtrim($row['CRWHS']) . "\" size=\"7\" maxlength=\"3\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NewCustomerDefaultsWarehouse&amp;fldDesc=NewCustomerDefaultsWarehouseName\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"NewCustomerDefaultsWarehouseName\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRWHS);
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"factoring\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Factoring</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Process A/R Factoring","ProcessARFactoring","inputalph","YORN","CRPARF",$row[CRPARF],$Err_CRPARF,"1","1","Y","","");

	$fieldDesc=RetValue("PYPYCD='$row[CRPYCD]'", "ARPYCD", "PYPYDS");
	$textOvr=SetTextOvr($Err_CRPYCD);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Factoring Payment Code</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"FactoringPaymentCode\" value=\"" . rtrim($row['CRPYCD']) . "\" size=\"2\" maxlength=\"1\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}ARPmtCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=FactoringPaymentCode&amp;fldDesc=FactoringPaymentCodeDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"FactoringPaymentCodeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRPYCD);

	$fieldDesc=RetValue("BKBANK=$row[CRFBNK]", "HDBANK", "BKBKNM");
	$textOvr=SetTextOvr($Err_CRFBNK);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Factor Bank</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"FactorBank\" value=\"" . rtrim($row['CRFBNK']) . "\" size=\"2\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}BankSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=FactorBank&amp;fldDesc=FactorBankDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"FactorBankDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRFBNK);
	print "\n     </table> ";

	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">Account</td> ";
	print "\n         </tr> ";

	$row['CRFARA']=Default_Zero($row['CRFARA']);  $row['CRFARS']=Default_Zero($row['CRFARS']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CRFARA],$row[CRFARS])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CRFARA);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Factoring A/R</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"FactoringARAccount\" value=\"" . rtrim($row['CRFARA']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                                     <input type=\"text\" name=\"FactoringARSubaccount\" value=\"" . rtrim($row['CRFARS']) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=FactoringARAccount&amp;subFld=FactoringARSubaccount&amp;descFld=FactoringARAccountDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"FactoringARAccountDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRFARA);

	$row['CRFLIA']=Default_Zero($row['CRFLIA']);  $row['CRFLIS']=Default_Zero($row['CRFLIS']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CRFLIA],$row[CRFLIS])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CRFLIA);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Factoring Liability</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"FactoringLiabilityAccount\" value=\"" . rtrim($row['CRFLIA']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                                     <input type=\"text\" name=\"FactoringLiabilitySubaccount\" value=\"" . rtrim($row['CRFLIS']) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=FactoringLiabilityAccount&amp;subFld=FactoringLiabilitySubaccount&amp;descFld=FactoringLiabilityAccountDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"FactoringLiabilityAccountDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRFLIA);
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"deduction\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Deduction</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	$fieldDesc=RetValue("PYPYCD='$row[CRDPYC]'", "ARPYCD", "PYPYDS");
	$textOvr=SetTextOvr($Err_CRDPYC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Deduction Payment Code</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"DeductionPaymentCode\" value=\"" . rtrim($row['CRDPYC']) . "\" size=\"2\" maxlength=\"1\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}ARPmtCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=DeductionPaymentCode&amp;fldDesc=DeductionPaymentCodeDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"DeductionPaymentCodeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRDPYC);

	$fieldDesc=RetValue("TMCTRM='$row[CRDTRM]'", "HDTRMS", "TMCTDS");
	$textOvr=SetTextOvr($Err_CRDTRM);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Deduction Terms Code</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"DeductionTermsCode\" value=\"" . rtrim($row['CRDTRM']) . "\" size=\"2\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=DeductionTermsCode&amp;fldDesc=DeductionTermsCodeDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"DeductionTermsCodeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRDTRM);

	Build_Fld_Entry("Deduction Date","DeductionDate","inputalph","DEDDATE","CRDDTE",$row[CRDDTE],$Err_CRDDTE,"2","1","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"NSF\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">NSF</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	$fieldDesc=RetValue("PYPYCD='$row[CRNPYC]'", "ARPYCD", "PYPYDS");
	$textOvr=SetTextOvr($Err_CRNPYC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>NSF Payment Code</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"NSFPaymentCode\" value=\"" . rtrim($row['CRNPYC']) . "\" size=\"2\" maxlength=\"1\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}ARPmtCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NSFPaymentCode&amp;fldDesc=NSFPaymentCodeDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"NSFPaymentCodeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRNPYC);

	$fieldDesc=RetValue("CSSTCD='$row[CRNCOL]'", "ARCSTM", "CSDESC");
	$textOvr=SetTextOvr($Err_CRNCOL);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>NSF Collection Status</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"NSFCollectionStatus\" value=\"" . rtrim($row['CRNCOL']) . "\" size=\"2\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}ARCollectionStatusSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=NSFCollectionStatus&amp;fldDesc=NSFCollectionStatusDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"NSFCollectionStatusDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CRNCOL);
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"userDefined\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">User Defined</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n         <td class=\"colhdr\">Description</td> ";
	print "\n         <td class=\"colhdr\">Report Heading</td> ";
	print "\n         </tr> ";

	$textOvr=SetTextOvr($Err_CRSCR1);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Generic 1</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericDescription1\" value=\"" . rtrim($row['CRSCR1']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericReportHeading11\" value=\"" . rtrim($row['CRRP11']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"GenericReportHeading12\" value=\"" . rtrim($row['CRRP12']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRSCR1);

	$textOvr=SetTextOvr($Err_CRSCR2);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Generic 2</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericDescription2\" value=\"" . rtrim($row['CRSCR2']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericReportHeading21\" value=\"" . rtrim($row['CRRP21']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                     <input type=\"text\" name=\"GenericReportHeading22\" value=\"" . rtrim($row['CRRP22']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRSCR2);

	$textOvr=SetTextOvr($Err_CRSCR3);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Generic 3</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericDescription3\" value=\"" . rtrim($row['CRSCR3']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericReportHeading31\" value=\"" . rtrim($row['CRRP31']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                     <input type=\"text\" name=\"GenericReportHeading32\" value=\"" . rtrim($row['CRRP32']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRSCR3);

	$textOvr=SetTextOvr($Err_CRSCR4);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Generic 4</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericDescription4\" value=\"" . rtrim($row['CRSCR4']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericReportHeading41\" value=\"" . rtrim($row['CRRP41']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                     <input type=\"text\" name=\"GenericReportHeading42\" value=\"" . rtrim($row['CRRP42']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRSCR4);

	$textOvr=SetTextOvr($Err_CRSCR5);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Generic 5</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericDescription5\" value=\"" . rtrim($row['CRSCR5']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"GenericReportHeading51\" value=\"" . rtrim($row['CRRP51']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                     <input type=\"text\" name=\"GenericReportHeading52\" value=\"" . rtrim($row['CRRP52']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CRSCR5);
	print "\n     </table> ";
	print "\n </fieldset> ";

	if ($HDMCRL>0) {
		print "\n <a name=\"multiCurrency\"></a> ";
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n     <legend class=\"legendTitle\">Multi-Currency</legend> ";
		require 'TopOfForm.php';
		print "\n     <table $contentTable> ";
		Build_Fld_Entry("Process Multi-Currency","ProcessMultiCurrency","inputalph","YORN","CRPRMC",$row[CRPRMC],$Err_CRPRMC,"1","1","Y","","");

		$fieldDesc=RetValue("RYTYPE='$row[CRMCRT]'", "HDRTYP", "RYDESC");
		$textOvr=SetTextOvr($Err_CRMCRT);
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>A/R Currency Rate Type</span></td> ";
		print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"ARCurrencyRateType\" value=\"" . rtrim($row['CRMCRT']) . "\" size=\"10\" maxlength=\"10\"> ";
		print "\n                                     <a href=\"{$homeURL}{$phpPath}CurrencyRateTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=ARCurrencyRateType&amp;fldDesc=ARCurrencyRateTypeDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
		print "\n                                     <span class=\"dspdesc\" id=\"ARCurrencyRateTypeDesc\">$fieldDesc</span></td>";
		print "\n         </tr> ";
		DspErrMsg($Err_CRMCRT);
		print "\n     </table> ";
		print "\n </fieldset> ";
	}

	print "\n <a name=\"businessRules\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Application Of Cash Business Rules</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Grace Days Given For Discount","GraceDaysGivenForDiscount","inputnmbr","","CRDSPD",$row[CRDSPD],$Err_CRDSPD,"4","4","","","");
	print "\n     </table> ";

	print "\n     <fieldset class=\"legendBody\"> ";
	print "\n         <legend class=\"legendTitle\">Invoice Numbering</legend> ";
	print "\n         <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">Invoice</td> ";
	print "\n             <td class=\"colhdr\">Rule</td> ";
	print "\n         </tr> ";

	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Unapplied Cash</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"UnappliedCashInvoice\" value=\"" . rtrim($row['CRUINV']) . "\" size=\"7\" maxlength=\"7\"></td> ";
	Build_Fld_Entry("Unapplied Cash","AssignUnappliedCash","inputalph","ARINVASSGN","CRUAUI",$row[CRUAUI],$Err_CRUAUI,"1","1","Y","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_CRUAUI);

	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>General Deduction</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"GeneralDeductionInvoice\" value=\"" . rtrim($row['CRGINV']) . "\" size=\"7\" maxlength=\"7\"></td> ";
	Build_Fld_Entry("Unapplied Cash","AssignGeneralDeduction","inputalph","ARINVASSGN","CRGAUI",$row[CRGAUI],$Err_CRGAUI,"1","1","Y","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_CRGAUI);

	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Specific Deduction</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"SpecificDeductionInvoice\" value=\"" . rtrim($row['CRIINV']) . "\" size=\"7\" maxlength=\"7\"></td> ";
	Build_Fld_Entry("Unapplied Cash","AssignSpecificDeduction","inputalph","ARINVASSGN","CRIAUI",$row[CRIAUI],$Err_CRIAUI,"1","1","Y","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_CRIAUI);

	print "\n         </table> ";
	print "\n     </fieldset> ";
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
	$edtVar= "";
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	Concat_Field("@@dper", $_POST['ARDistributionPeriod']);
	Concat_Field("@@rclr", strtoupper($_POST['ReclaimResourceLevel']));
	Concat_Field("@@lpri", strtoupper($_POST['LocationPrimary']));
	if (!isset($_POST['DepositBalancingRequired'])) {$_POST['DepositBalancingRequired']="N";} Concat_Field("@@bbal", $_POST['DepositBalancingRequired']);
	Concat_Field("@@mscc", $_POST['MiscellaneousCashCustomer']);
	Concat_Field("@@msci", $_POST['MiscellaneousCashInvoice']);
	if (!isset($_POST['AutoAssignCustomer'])) {$_POST['AutoAssignCustomer']="N";} Concat_Field("@@aacn", $_POST['AutoAssignCustomer']);strtoupper($_POST['']);
	Concat_Field("@@lcnu", $_POST['LastCustomerUsed']);
	if (!isset($_POST['AutoAssignPayer'])) {$_POST['AutoAssignPayer']="N";} Concat_Field("@@apyr", $_POST['AutoAssignPayer']);
	Concat_Field("@@lpyr", $_POST['LastPayerUsed']);
	if (!isset($_POST['AutoAssignCashPostingBatch'])) {$_POST['AutoAssignCashPostingBatch']="N";} Concat_Field("@@abch", $_POST['AutoAssignCashPostingBatch']);
	Concat_Field("@@pbch", $_POST['LastCashPostingBatchUsed']);
	Concat_Field("@@oebh", $_POST['OEBatch']);
	Concat_Field("@@nsbh", $_POST['NSFBatch']);
	Concat_Field("@@iebh", $_POST['InvoiceEntryBatch']);
	if (!isset($_POST['FeedARToGL'])) {$_POST['FeedARToGL']="N";} Concat_Field("@@argl", $_POST['FeedARToGL']);
	Concat_Field("@@feds", strtoupper($_POST['GLDistributionFeed']));
	Concat_Field("@@fpds", strtoupper($_POST['GLPaymentFeed']));
	if (!isset($_POST['RestoreARFeedIfErrorsExist'])) {$_POST['RestoreARFeedIfErrorsExist']="N";} Concat_Field("@@auto", $_POST['RestoreARFeedIfErrorsExist']);
	Concat_Field("@@intr", strtoupper($_POST['GenerateInterCompanyTransactions']));
	Concat_Field("@@arac", $_POST['ARAccount']);
	Concat_Field("@@arsb", $_POST['ARSubaccount']);
	Concat_Field("@@txac", $_POST['SalesTaxAccount']);
	Concat_Field("@@txsb", $_POST['SalesTaxSubaccount']);
	Concat_Field("@@frac", $_POST['FreightAccount']);
	Concat_Field("@@frsb", $_POST['FreightSubaccount']);
	Concat_Field("@@scac", $_POST['SpecialChargesAccount']);
	Concat_Field("@@scsb", $_POST['SpecialChargesSubaccount']);
	Concat_Field("@@ciao", strtoupper($_POST['AgeCreditInvoicesBasedOn']));
	Concat_Field("@@baon", strtoupper($_POST['BaseAgingOnCode']));
	Concat_Field("@@age1", $_POST['AgingBucket1NumberOfDays']);
	Concat_Field("@@age2", $_POST['AgingBucket2NumberOfDays']);
	Concat_Field("@@age3", $_POST['AgingBucket3NumberOfDays']);
	Concat_Field("@@age4", $_POST['AgingBucket4NumberOfDays']);
	Concat_Field("@@ccrl", $_POST['NewCustomerDefaultsCreditLimit']);
	Concat_Field("@@ctrm", strtoupper($_POST['NewCustomerDefaultsTermsCode']));
	if (!isset($_POST['NewCustomerDefaultsServiceCharge'])) {$_POST['NewCustomerDefaultsServiceCharge']="N";} Concat_Field("@@cscc", $_POST['NewCustomerDefaultsServiceCharge']);
	if (!isset($_POST['NewCustomerDefaultsStatement'])) {$_POST['NewCustomerDefaultsStatement']="N";} Concat_Field("@@cstc", $_POST['NewCustomerDefaultsStatement']);
	Concat_Field("@@crgn", strtoupper($_POST['NewCustomerDefaultsCustomerRegion']));
	Concat_Field("@@ctry", strtoupper($_POST['NewCustomerDefaultsCustomerCountry']));
	Concat_Field("@@loc@", $_POST['NewCustomerDefaultsLocation']);
	Concat_Field("@@ccls", strtoupper($_POST['NewCustomerDefaultsClassCode']));
	Concat_Field("@@slsm", $_POST['NewCustomerDefaultsSalesman']);
	Concat_Field("@@svia", strtoupper($_POST['NewCustomerDefaultsShipVia']));
	Concat_Field("@@whs@", $_POST['NewCustomerDefaultsWarehouse']);
	if (!isset($_POST['ProcessARFactoring'])) {$_POST['ProcessARFactoring']="N";} Concat_Field("@@parf", $_POST['ProcessARFactoring']);
	Concat_Field("@@pycd", strtoupper($_POST['FactoringPaymentCode']));
	Concat_Field("@@fbnk", $_POST['FactorBank']);
	Concat_Field("@@fara", $_POST['FactoringARAccount']);
	Concat_Field("@@fars", $_POST['FactoringARSubaccount']);
	Concat_Field("@@flia", $_POST['FactoringLiabilityAccount']);
	Concat_Field("@@flis", $_POST['FactoringLiabilitySubaccount']);
	Concat_Field("@@dpyc", strtoupper($_POST['DeductionPaymentCode']));
	Concat_Field("@@dtrm", strtoupper($_POST['DeductionTermsCode']));
	Concat_Field("@@ddte", strtoupper($_POST['DeductionDate']));
	Concat_Field("@@npyc", strtoupper($_POST['NSFPaymentCode']));
	Concat_Field("@@ncol", strtoupper($_POST['NSFCollectionStatus']));
	Concat_Field("@@scr1", $_POST['GenericDescription1']);
	Concat_Field("@@scr2", $_POST['GenericDescription2']);
	Concat_Field("@@scr3", $_POST['GenericDescription3']);
	Concat_Field("@@scr4", $_POST['GenericDescription4']);
	Concat_Field("@@scr5", $_POST['GenericDescription5']);
	Concat_Field("@@rp11", $_POST['GenericReportHeading11']);
	Concat_Field("@@rp12", $_POST['GenericReportHeading12']);
	Concat_Field("@@rp21", $_POST['GenericReportHeading21']);
	Concat_Field("@@rp22", $_POST['GenericReportHeading22']);
	Concat_Field("@@rp31", $_POST['GenericReportHeading31']);
	Concat_Field("@@rp32", $_POST['GenericReportHeading32']);
	Concat_Field("@@rp41", $_POST['GenericReportHeading41']);
	Concat_Field("@@rp42", $_POST['GenericReportHeading42']);
	Concat_Field("@@rp51", $_POST['GenericReportHeading51']);
	Concat_Field("@@rp52", $_POST['GenericReportHeading52']);
	if ($HDMCRL>0)  {
		if (!isset($_POST['ProcessMultiCurrency'])) {$_POST['ProcessMultiCurrency']="N";} Concat_Field("@@prmc", $_POST['ProcessMultiCurrency']);
		Concat_Field("@@mcrt", strtoupper($_POST['ARCurrencyRateType']));
	}
	Concat_Field("@@dspd", $_POST['GraceDaysGivenForDiscount']);
	Concat_Field("@@uinv", $_POST['UnappliedCashInvoice']);
	Concat_Field("@@uaui", strtoupper($_POST['AssignUnappliedCash']));
	Concat_Field("@@ginv", $_POST['GeneralDeductionInvoice']);
	Concat_Field("@@gaui", strtoupper($_POST['AssignGeneralDeduction']));
	Concat_Field("@@iinv", $_POST['SpecificDeductionInvoice']);
	Concat_Field("@@iaui", strtoupper($_POST['AssignSpecificDeduction']));
	$edtVar .= "}{";

	$returnValue=Validate_Data($userProfile, $errFound, $edtVar, $errVar);
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "A/R Control", "", "", "", "", "");
		$includeName= "{$homePath}ARControl{$dataBaseID}.php";
		$fileName="ARControl{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HARCTL_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}

}

function Validate_Data($userProfile,$errFound,$edtVar,$errVar) {
	global $pgmLibrary, $i5Connect;
	if (is_null($errFound ))   $errFound="";
	if (is_null($edtVar ))     $edtVar="";
	if (is_null($errVar ))     $errVar="";

	$pgmCall = array(
	array("Name"=>"userProfile",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"errFound",        "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HARCTU_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HARCTU_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"userProfile"    =>$userProfile,
	"errFound"       =>$errFound,
	"edtVar"         =>$edtVar,
	"errVar"         =>$errVar);

	$parmOut = array(
	"userProfile"    =>"userProfile",
	"errFound"       =>"errFound",
	"edtVar"         =>"edtVar",
	"errVar"         =>"errVar");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data (HARCTU_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['userProfile']    =$userProfile;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	return $returnValue;
}

?>