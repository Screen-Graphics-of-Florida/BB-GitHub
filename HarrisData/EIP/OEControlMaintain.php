<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$backHome           = $_GET['backHome'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "O/E Control Maintenance";
$scriptName     = "OEControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HOECTU";
$sortSeqMax     = 7;

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
	require_once 'CheckEnterChg.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	print "\n function validate(chgForm) {";
	print "\n     if (document.Chg.oeDistPeriod.value ==\"\" || ";
	print "\n         document.Chg.reclaimResourceLevel.value ==\"\" || ";
	print "\n         document.Chg.numberOfDaysPastDue.value ==\"\" || ";
	print "\n         document.Chg.lastInvoiceNumberUsed.value ==\"\" || ";
	print "\n         document.Chg.dueDateUsed.value ==\"\" || ";
	print "\n         document.Chg.oeToARAudit.value ==\"\" || ";
	print "\n         document.Chg.nbrDataQueueProcJobs.value ==\"\" || ";
	print "\n         document.Chg.defaultBank.value ==\"\" || ";
	print "\n        (document.Chg.arAcctNumber.value ==\"\" && ";
	print "\n         document.Chg.arSubAcctNumber.value ==\"\") || ";
	print "\n        (document.Chg.freightAcctNumber.value ==\"\" && ";
	print "\n         document.Chg.freightSubAcctNumber.value ==\"\") || ";
	print "\n        (document.Chg.salesTaxAcctNumber.value ==\"\" && ";
	print "\n         document.Chg.salesTaxSubAcctNumber.value ==\"\") || ";
	print "\n        (document.Chg.specialChgAcctNumber.value ==\"\" && ";
	print "\n         document.Chg.specialChgSubAcctNumber.value ==\"\") || ";
	print "\n         document.Chg.onlineCreditCardAuthorization.value ==\"\" || ";
	print "\n         document.Chg.defaultBank.value ==\"\") ";
	print "\n         {alert(\"$reqFieldError\"); return false;} ";

	print "\n     if (editNum(document.Chg.oeDistPeriod, 4, 0) && ";
	print "\n         editNum(document.Chg.lastOrderNumberUsed, 8, 0) && ";
	print "\n         editNum(document.Chg.pastDueDollarAmount, 7, 0) && ";
	print "\n         editNum(document.Chg.numberOfDaysPastDue, 5, 0) && ";
	print "\n         editNum(document.Chg.lastInvoiceNumberUsed, 7, 0) && ";
	print "\n         editNum(document.Chg.dueDateUsed, 1, 0) && ";
	print "\n         editNum(document.Chg.merchantCode, 5, 0) && ";
	print "\n         editNum(document.Chg.nbrDataQueueProcJobs, 2, 0) && ";
	print "\n         editNum(document.Chg.defaultBank, 2, 0) && ";
	print "\n         editNum(document.Chg.arAcctNumber, 4, 0) && ";
	print "\n         editNum(document.Chg.arSubAcctNumber, 4, 0) && ";
	print "\n         editNum(document.Chg.freightAcctNumber, 4, 0) && ";
	print "\n         editNum(document.Chg.freightSubAcctNumber, 4, 0) && ";
	print "\n         editNum(document.Chg.salesTaxAcctNumber, 4, 0) && ";
	print "\n         editNum(document.Chg.salesTaxSubAcctNumber, 4, 0) && ";
	print "\n         editNum(document.Chg.specialChgAcctNumber, 4, 0) && ";
	print "\n         editNum(document.Chg.specialChgSubAcctNumber, 4, 0) && ";
	print "\n         editNum(document.Chg.lastPackingListNumber, 8, 0) && ";
	print "\n         editNum(document.Chg.lastBillOfLadingNumber, 8, 0)) ";
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
	$pageID = "OECONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = " Select OECTRL.*, CEPER# as CEPER, CEPD$ as CEPD, CELPL# as CELPL, CELBL# as CELBL, OEIVCT.* ";
	$stmtSQL .= " From OECTRL, OEIVCT ";
	require 'stmtSQLEnd.php';

	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#general\">General</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#autoAssign\">Auto Assign</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#accounting\">Accounting</a></td> ";
	print "\n   </tr> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#userDefined\">User Defined</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#billOfLading\">Bill Of Lading</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#invoicing\">Invoicing</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField= "reclaimResourceLevel";
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_CEPER =DecatErr_Field("@@per@", "oeDistPeriod");
			$Err_CERCLR=DecatErr_Field("@@rclr", "reclaimResourceLevel");
			$Err_CELORD=DecatErr_Field("@@lord", "lastOrderNumberUsed");
			$Err_CEASSN=DecatErr_Field("@@assn", "automaticOrderNumberAssignment");
			$Err_CEOAWC=DecatErr_Field("@@oawc", "orderAvailabilityWarning");
			$Err_CEPD  =DecatErr_Field("@@pd@@", "pastDueDollarAmount");
			$Err_CEPDDC=DecatErr_Field("@@pddc", "numberOfDaysPastDue");
			$Err_CELINV=DecatErr_Field("@@linv", "lastInvoiceNumberUsed");
			$Err_CEDUDT=DecatErr_Field("@@dudt", "dueDateUsed");
			$Err_CEAPGO=DecatErr_Field("@@apgo", "autoPurgeOrderWhenClosed");
			$Err_CESHPO=DecatErr_Field("@@shpo", "defaultShipQuantity");
			$Err_CEAOVS=DecatErr_Field("@@aovs", "allowOverShipments");
			$Err_CEOEAR=DecatErr_Field("@@oear", "oeToARAudit");
			$Err_CENMDQ=DecatErr_Field("@@nmdq", "nbrDataQueueProcJobs");
			$Err_CEMHLD=DecatErr_Field("@@mhld", "mfgHoldCode");
			$Err_CEDBNK=DecatErr_Field("@@dbnk", "defaultBank");
			$Err_CEAIWD=DecatErr_Field("@@aiwd", "automaticItemWarehouseCreation");
			$Err_CEBPBL=DecatErr_Field("@@bpbl", "backupPriorToBillingUpdate");
			$Err_CEARAC=DecatErr_Field("@@arac", "arAcctNumber");
			$Err_CEARSB=DecatErr_Field("@@arsb", "arSubAcctNumber");
			$Err_CEFRAC=DecatErr_Field("@@frac", "freightAcctNumber");
			$Err_CEFRSB=DecatErr_Field("@@frsb", "freightSubAcctNumber");
			$Err_CETXAC=DecatErr_Field("@@txac", "salesTaxAcctNumber");
			$Err_CETXSB=DecatErr_Field("@@txsb", "salesTaxSubAcctNumber");
			$Err_CESCAC=DecatErr_Field("@@scac", "specialChgAcctNumber");
			$Err_CESCSB=DecatErr_Field("@@scsb", "specialChgSubAcctNumber");
			$Err_CECCP= DecatErr_Field("@@ccp@", "onlineCreditCardAuthorization");
			$Err_CEMRCH=DecatErr_Field("@@mrch", "merchantCode");
			$Err_CEUPGP=DecatErr_Field("@@upgp", "useProductGroupPricing");
			$Err_CEOEPO=DecatErr_Field("@@oepo", "allowPOGeneration");
			$Err_CEUCD1=DecatErr_Field("@@ucd1", "userDefined1");
			$Err_CEUCD2=DecatErr_Field("@@ucd2", "userDefined2");
			$Err_CEUCD2=DecatErr_Field("@@ucd3", "userDefined3");
			$Err_CEUCD4=DecatErr_Field("@@ucd4", "userDefined4");
			$Err_CEUCD5=DecatErr_Field("@@ucd5", "userDefined5");
			$Err_CESUD1=DecatErr_Field("@@sud1", "storeDefined1");
			$Err_CESUD2=DecatErr_Field("@@sud2", "storeDefined2");
			$Err_CESUD3=DecatErr_Field("@@sud3", "storeDefined3");
			$Err_CESUD4=DecatErr_Field("@@sud4", "storeDefined4");
			$Err_CESUD5=DecatErr_Field("@@sud5", "storeDefined5");
			$Err_CELPL =DecatErr_Field("@@lpl@", "lastPackingListNumber");
			$Err_CELBL =DecatErr_Field("@@lbl@", "lastBillOfLadingNumber");
			$Err_CEBOLC=DecatErr_Field("@@bolc", "printCommentsOnlyNoDetail");
			$Err_CEHF01=DecatErr_Field("@@hf01", "bolHdrFoot01");
			$Err_CECM01=DecatErr_Field("@@cm01", "bolComment01");
			$Err_CEHF02=DecatErr_Field("@@hf02", "bolHdrFoot02");
			$Err_CECM02=DecatErr_Field("@@cm02", "bolComment02");
			$Err_CEHF03=DecatErr_Field("@@hf03", "bolHdrFoot03");
			$Err_CECM03=DecatErr_Field("@@cm03", "bolComment03");
			$Err_CEHF04=DecatErr_Field("@@hf04", "bolHdrFoot04");
			$Err_CECM04=DecatErr_Field("@@cm04", "bolComment04");
			$Err_CEHF05=DecatErr_Field("@@hf05", "bolHdrFoot05");
			$Err_CECM05=DecatErr_Field("@@cm05", "bolComment05");
			$Err_CEHF06=DecatErr_Field("@@hf06", "bolHdrFoot06");
			$Err_CECM06=DecatErr_Field("@@cm06", "bolComment06");
			$Err_CEHF07=DecatErr_Field("@@hf07", "bolHdrFoot07");
			$Err_CECM07=DecatErr_Field("@@cm07", "bolComment07");
			$Err_CEHF08=DecatErr_Field("@@hf08", "bolHdrFoot08");
			$Err_CECM08=DecatErr_Field("@@cm08", "bolComment08");
			$Err_OISLOC=DecatErr_Field("@@sloc", "sortLocationNumber");
			$Err_OISORD=DecatErr_Field("@@sord", "sortOrderNumber");
			$Err_OISBLT=DecatErr_Field("@@sblt", "sortBillToNumber");
			$Err_OISBNM=DecatErr_Field("@@sbnm", "sortBillToName");
			$Err_OISZIP=DecatErr_Field("@@szip", "sortBillToZipCode");
			$Err_OISSLS=DecatErr_Field("@@ssls", "sortSalesmanNumber");
			$Err_OISINV=DecatErr_Field("@@sinv", "sortInvoiceNumber");
		}
		$row['CERCLR']=Decat_Field("@@rclr", $edtVar);
		$row['CEPER'] =Decat_Field("@@per@", $edtVar);
		$row['CELORD']=Decat_Field("@@lord", $edtVar);
		$row['CEASSN']=Decat_Field("@@assn", $edtVar);
		$row['CEOAWC']=Decat_Field("@@oawc", $edtVar);
		$row['CEPD']  =Decat_Field("@@pd@@", $edtVar);
		$row['CEPDDC']=Decat_Field("@@pddc", $edtVar);
		$row['CELINV']=Decat_Field("@@linv", $edtVar);
		$row['CEABOR']=Decat_Field("@@abor", $edtVar);
		$row['CEDUDT']=Decat_Field("@@dudt", $edtVar);
		$row['CEAPGO']=Decat_Field("@@apgo", $edtVar);
		$row['CESHPO']=Decat_Field("@@shpo", $edtVar);
		$row['CEAOVS']=Decat_Field("@@aovs", $edtVar);
		$row['CEOEAR']=Decat_Field("@@oear", $edtVar);
		$row['CENMDQ']=Decat_Field("@@nmdq", $edtVar);
		$row['CEMHLD']=Decat_Field("@@mhld", $edtVar);
		$row['CEDBNK']=Decat_Field("@@dbnk", $edtVar);
		$row['CEAIWD']=Decat_Field("@@aiwd", $edtVar);
		$row['CEBPBL']=Decat_Field("@@bpbl", $edtVar);
		$row['CEARAC']=Decat_Field("@@arac", $edtVar);
		$row['CEARSB']=Decat_Field("@@arsb", $edtVar);
		$row['CEFRAC']=Decat_Field("@@frac", $edtVar);
		$row['CEFRSB']=Decat_Field("@@frsb", $edtVar);
		$row['CETXAC']=Decat_Field("@@txac", $edtVar);
		$row['CETXSB']=Decat_Field("@@txsb", $edtVar);
		$row['CESCAC']=Decat_Field("@@scac", $edtVar);
		$row['CESCSB']=Decat_Field("@@scsb", $edtVar);
		$row['CECCP'] =Decat_Field("@@ccp@", $edtVar);
		$row['CEMRCH']=Decat_Field("@@mrch", $edtVar);
		$row['CEUPGP']=Decat_Field("@@upgp", $edtVar);
		$row['CEOEPO']=Decat_Field("@@oepo", $edtVar);
		$row['CEUCD1']=Decat_Field("@@ucd1", $edtVar);
		$row['CEUCD2']=Decat_Field("@@ucd2", $edtVar);
		$row['CEUCD2']=Decat_Field("@@ucd3", $edtVar);
		$row['CEUCD4']=Decat_Field("@@ucd4", $edtVar);
		$row['CEUCD5']=Decat_Field("@@ucd5", $edtVar);
		$row['CESUD1']=Decat_Field("@@sud1", $edtVar);
		$row['CESUD2']=Decat_Field("@@sud2", $edtVar);
		$row['CESUD3']=Decat_Field("@@sud3", $edtVar);
		$row['CESUD4']=Decat_Field("@@sud4", $edtVar);
		$row['CESUD5']=Decat_Field("@@sud5", $edtVar);
		$row['CELPL'] =Decat_Field("@@lpl@", $edtVar);
		$row['CELBL'] =Decat_Field("@@lbl@", $edtVar);
		$row['CEBOLC']=Decat_Field("@@bolc", $edtVar);
		$row['CEHF01']=Decat_Field("@@hf01", $edtVar);
		$row['CECM01']=Decat_Field("@@cm01", $edtVar);
		$row['CEHF02']=Decat_Field("@@hf02", $edtVar);
		$row['CECM02']=Decat_Field("@@cm02", $edtVar);
		$row['CEHF03']=Decat_Field("@@hf03", $edtVar);
		$row['CECM03']=Decat_Field("@@cm03", $edtVar);
		$row['CEHF04']=Decat_Field("@@hf04", $edtVar);
		$row['CECM04']=Decat_Field("@@cm04", $edtVar);
		$row['CEHF05']=Decat_Field("@@hf05", $edtVar);
		$row['CECM05']=Decat_Field("@@cm05", $edtVar);
		$row['CEHF06']=Decat_Field("@@hf06", $edtVar);
		$row['CECM06']=Decat_Field("@@cm06", $edtVar);
		$row['CEHF07']=Decat_Field("@@hf07", $edtVar);
		$row['CECM07']=Decat_Field("@@cm07", $edtVar);
		$row['CEHF08']=Decat_Field("@@hf08", $edtVar);
		$row['CECM08']=Decat_Field("@@cm08", $edtVar);
		$row['OISLOC']=Decat_Field("@@sloc", $edtVar);
		$row['OISORD']=Decat_Field("@@sord", $edtVar);
		$row['OISBLT']=Decat_Field("@@sblt", $edtVar);
		$row['OISBNM']=Decat_Field("@@sbnm", $edtVar);
		$row['OISZIP']=Decat_Field("@@szip", $edtVar);
		$row['OISSLS']=Decat_Field("@@ssls", $edtVar);
		$row['OISINV']=Decat_Field("@@sinv", $edtVar);
	} else {
		$row['CEPER']=PeriodInputFromCYP($row['CEPER']);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_CRTSTP);
	Build_DspFld("O/E Release Version",$HDOERL,"","A");
	DspErrMsg($Err_CRTSTP);
	Build_DspFld("O/E Library Level",$HDOELL,"","A");
	print "\n </table>";

	print "\n <a name=\"general\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">General</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	Build_Fld_Entry("Reclaim Resource Level","reclaimResourceLevel","inputalph","RECLAIMLVL","CERCLR",$row[CERCLR],$Err_CERCLR,"3","1","Y","","");
	Build_Fld_Entry("Order Availability Warning","orderAvailabilityWarning","inputalph","YORN","CEOAWC",$row[CEOAWC],$Err_CEOAWC,"1","1","Y","","");
	Build_Fld_Entry("Past Due Dollar Amount","pastDueDollarAmount","inputnmbr","","CEPD",$row[CEPD],$Err_CEPD,"3","7","Y","","");
	Build_Fld_Entry("Number Of Days Past Due","numberOfDaysPastDue","inputnmbr","","CEPDDC",$row[CEPDDC],$Err_CEPDDC,"3","5","","","");
	Build_Fld_Entry("Due Date Used","dueDateUsed","inputnmbr","DUEDATEUSD","CEDUDT",$row[CEDUDT],$Err_CEDUDT,"3","1","Y","","");
	Build_Fld_Entry("Automatically Purge Order When Closed","autoPurgeOrderWhenClosed","inputalph","YORN","CEAPGO",$row[CEAPGO],$Err_CEAPGO,"1","1","Y","","");
	Build_Fld_Entry("Default Ship Quantity","defaultShipQuantity","inputalph","YORN","CESHPO",$row[CESHPO],$Err_CESHPO,"1","1","Y","","");
	Build_Fld_Entry("Allow Over Shipments","allowOverShipments","inputalph","YORN","CEAOVS",$row[CEAOVS],$Err_CEAOVS,"1","1","Y","","");
	Build_Fld_Entry("O/E To A/R Audit","oeToARAudit","inputalph","DORS","CEOEAR",$row[CEOEAR],$Err_CEOEAR,"3","1","Y","","");
	Build_Fld_Entry("Number Of Data Queue Processing Jobs","nbrDataQueueProcJobs","inputnmbr","","CENMDQ",$row[CENMDQ],$Err_CENMDQ,"3","2","Y","","");
	Build_Fld_Entry("Online Credit Card Authorization","onlineCreditCardAuthorization","inputalph","DYN","CECCP",$row[CECCP],$Err_CECCP,"3","1","Y","","");
	Build_Fld_Entry("Default Merchant Code","merchantCode","inputnmbr","","CEMRCH",$row[CEMRCH],$Err_CEMRCH,"3","5","","","");
	Build_Fld_Entry("Use Product Group Pricing","useProductGroupPricing","inputalph","YORN","CEUPGP",$row[CEUPGP],$Err_CEUPGP,"1","1","Y","","");
	Build_Fld_Entry("Allow P/O Generation","allowPOGeneration","inputalph","ALLOWPOGEN","CEOEPO",$row[CEOEPO],$Err_CEOEPO,"3","1","Y","","");

	if ($HDPDRL>0) {
		$fieldDesc=RetValue("HCHLCD='$row[CEMHLD]' and HCTYPE='M'", "HDHLCD", "HCDESC");
		$textOvr=SetTextOvr($Err_CEMHLD);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Mfg Hold Code For Credit Hold</span></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"mfgHoldCode\" value=\"" . rtrim($row['CEMHLD']) . "\" size=\"3\" maxlength=\"4\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}HoldCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldType=O&amp;fldName=mfgHoldCode&amp;fldDesc=mfgHoldCodeDesc\" onclick=\"$searchWinVar\">&nbsp;  $searchImage</a> ";
		print "\n                             <span class=\"dspdesc\" id=\"mfgHoldCodeDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_CEMHLD);
	}

	$row['CEDBNK']=Default_Zero($row['CEDBNK']);
	$fieldDesc=RetValue("BKBANK=$row[CEDBNK]", "HDBANK", "BKBKNM");
	$textOvr=SetTextOvr($Err_CEDBNK);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Default Bank</span></td> ";
	print "\n <td class=\"inputnmbr\"><input type=\"text\"   name=\"defaultBank\" value=\"" . rtrim($row['CEDBNK']) . "\" size=\"3\" maxlength=\"2\"> ";
	print "\n                         <a href=\"{$homeURL}{$phpPath}BankSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=defaultBank&amp;fldDesc=defaultBankDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n                         <span class=\"dspdesc\" id=\"defaultBankDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CEDBNK);

	Build_Fld_Entry("Automatic Item/Warehouse Creation","automaticItemWarehouseCreation","inputalph","YORN","CEAIWD",$row[CEAIWD],$Err_CEAIWD,"1","1","Y","","");
	Build_Fld_Entry("Backup Prior To Billing Update","backupPriorToBillingUpdate","inputalph","YORN","CEBPBL",$row[CEBPBL],$Err_CEBPBL,"1","1","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"autoAssign\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Auto Assign</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">Last Used</td> ";
	print "\n             <td class=\"colhdr\">Auto Assign</td> ";
	print "\n         </tr> ";

	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Order</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"lastOrderNumberUsed\" value=\"" . rtrim($row['CELORD']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	Build_Fld_Entry("","automaticOrderNumberAssignment","colcode","YORN","CEASSN",$row[CEASSN],$Err_CEASSN,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_CELORD);

	Build_Fld_Entry("Invoice","lastInvoiceNumberUsed","inputnmbr","","CELINV",$row[CELINV],$Err_CELINV,"8","7","Y","","");
	Build_Fld_Entry("Packing List","lastPackingListNumber","inputnmbr","","CELPL",$row[CELPL],$Err_CELPL,"8","8","","","");
	Build_Fld_Entry("Bill Of Lading","lastBillOfLadingNumber","inputnmbr","","CELBL",$row[CELBL],$Err_CELBL,"8","8","","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"accounting\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Accounting</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_CEPER);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>O/E Distribution Period</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"oeDistPeriod\" value=\"" . rtrim($row['CEPER']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;periodFld=oeDistPeriod\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n </tr> ";
	DspErrMsg($Err_CEPER);

	print "\n </table> ";

	print "\n <table $contentTable> ";
	print "\n <tr><td>&nbsp;</td> ";
	print "\n <td class=\"colhdr\">Account</td> ";
	print "\n </tr> ";

	$row['CEARAC']=Default_Zero($row['CEARAC']);
	$row['CEARSB']=Default_Zero($row['CEARSB']);
	$fieldDesc=RetValue("CHACCT=$row[CEARAC] and CHSUB=$row[CEARSB]", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CEARAC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Accounts Receivable</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"arAcctNumber\" value=\"" . rtrim($row['CEARAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"arSubAcctNumber\" value=\"" . rtrim($row['CEARSB']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=arAcctNumber&amp;subFld=arSubAcctNumber&amp;descFld=arAcctNumberDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"arAcctNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CEARAC);

	$row['CEFRAC']=Default_Zero($row['CEFRAC']);
	$row['CEFRSB']=Default_Zero($row['CEFRSB']);
	$fieldDesc=RetValue("CHACCT=$row[CEFRAC] and CHSUB=$row[CEFRSB]", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CEFRAC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Freight</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"freightAcctNumber\" value=\"" . rtrim($row['CEFRAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"freightSubAcctNumber\" value=\"" . rtrim($row['CEFRSB']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=freightAcctNumber&amp;subFld=freightSubAcctNumber&amp;descFld=freightAcctNumberDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"freightAcctNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CEFRAC);


	$row['CETXAC']=Default_Zero($row['CETXAC']);
	$row['CETXSB']=Default_Zero($row['CETXSB']);
	$fieldDesc=RetValue("CHACCT=$row[CETXAC] and CHSUB=$row[CETXSB]", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CETXAC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Sales Tax</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"salesTaxAcctNumber\" value=\"" . rtrim($row['CETXAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"salesTaxSubAcctNumber\" value=\"" . rtrim($row['CETXSB']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=salesTaxAcctNumber&amp;subFld=salesTaxSubAcctNumber&amp;descFld=salesTaxAcctNumberDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"salesTaxAcctNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CETXAC);

	$row['CESCAC']=Default_Zero($row['CESCAC']);
	$row['CESCSB']=Default_Zero($row['CESCSB']);
	$fieldDesc=RetValue("CHACCT=$row[CESCAC] and CHSUB=$row[CESCSB]", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CESCAC);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Special Charges</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"specialChgAcctNumber\" value=\"" . rtrim($row['CESCAC']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"specialChgSubAcctNumber\" value=\"" . rtrim($row['CESCSB']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=specialChgAcctNumber&amp;subFld=specialChgSubAcctNumber&amp;descFld=specialChgAcctNumberDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"specialChgAcctNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CESCAC);
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"userDefined\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">User Defined</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	print "\n <tr><td>&nbsp;</td> ";
	print "\n     <td class=\"colhdr\">Description</td> ";
	print "\n </tr> ";

	Build_Fld_Entry("User-Defined Alpha 1","userDefined1","inputalph","","CEUCD1",$row[CEUCD1],$Err_CEUCD1,"35","30","","","");
	Build_Fld_Entry("User-Defined Alpha 2","userDefined2","inputalph","","CEUCD2",$row[CEUCD2],$Err_CEUCD2,"35","30","","","");
	Build_Fld_Entry("User-Defined Alpha 3","userDefined3","inputalph","","CEUCD3",$row[CEUCD3],$Err_CEUCD3,"35","30","","","");
	Build_Fld_Entry("User-Defined Alpha 4","userDefined4","inputalph","","CEUCD4",$row[CEUCD4],$Err_CEUCD4,"35","30","","","");
	Build_Fld_Entry("User-Defined Alpha 5","userDefined5","inputalph","","CEUCD5",$row[CEUCD5],$Err_CEUCD5,"35","30","","","");
	Build_Fld_Entry("Store User-Defined Alpha 1","storeDefined1","inputalph","","CESUD1",$row[CESUD1],$Err_CESUD1,"35","30","","","");
	Build_Fld_Entry("Store User-Defined Alpha 2","storeDefined2","inputalph","","CESUD2",$row[CESUD2],$Err_CESUD2,"35","30","","","");
	Build_Fld_Entry("Store User-Defined Alpha 3","storeDefined3","inputalph","","CESUD3",$row[CESUD3],$Err_CESUD3,"35","30","","","");
	Build_Fld_Entry("Store User-Defined Alpha 4","storeDefined4","inputalph","","CESUD4",$row[CESUD4],$Err_CESUD4,"35","30","","","");
	Build_Fld_Entry("Store User-Defined Alpha 5","storeDefined5","inputalph","","CESUD5",$row[CESUD5],$Err_CESUD5,"35","30","","","");

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n  <a name=\"billOfLading\"></a> ";
	print "\n  <fieldset class=\"legendBody\"> ";
	print "\n  <legend class=\"legendTitle\">Bill of Lading</legend> ";
	require 'TopOfForm.php';

	print "\n <table $contentTable>";
	Build_Fld_Entry("Print Comments Only - No Detail","printCommentsOnlyNoDetail","inputalph","YORN","CEBOLC",$row[CEBOLC],$Err_CEBOLC,"1","1","Y","","");
	print "\n </table> ";

	print "\n <table $contentTable>";
	$head01=Field_Checked($row['CEHF01'], "H");
	$foot01=Field_Checked($row['CEHF01'], "F");
	print "\n <tr><td class=\"colhdr\">Header</td><td class=\"colhdr\">Footer</td><td class=\"colhdr\">Bill Of Lading Comments</td></tr> ";
	print "\n <tr><td class=\"colCode\"><input name=\"bolHdrFoot01\" type=\"radio\" VALUE='H' $head01></td> ";
	print "\n     <td class=\"colCode\"><input name=\"bolHdrFoot01\" type=\"radio\" VALUE='F' $foot01></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"bolComment01\" value=\"" . rtrim($row['CECM01']) . "\" size=\"40\" maxlength=\"30\">";
	print "\n </tr> ";
	DspErrMsg($Err_CEHF01);
	DspErrMsg($Err_CECM01);

	$head02=Field_Checked($row['CEHF02'], "H");
	$foot02=Field_Checked($row['CEHF02'], "F");
	print "\n <tr><td class=\"colCode\"><input name=\"bolHdrFoot02\" type=\"radio\" VALUE='H' $head02></td> ";
	print "\n     <td class=\"colCode\"><input name=\"bolHdrFoot02\" type=\"radio\" VALUE='F' $foot02></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"bolComment02\" value=\"" . rtrim($row['CECM02']) . "\" size=\"40\" maxlength=\"30\">";
	print "\n </tr> ";
	DspErrMsg($Err_CEHF02);
	DspErrMsg($Err_CECM02);


	$head03=Field_Checked($row['CEHF03'], "H");
	$foot03=Field_Checked($row['CEHF03'], "F");
	print "\n <tr><td class=\"colCode\"><input name=\"bolHdrFoot03\" type=\"radio\" VALUE='H' $head03></td> ";
	print "\n     <td class=\"colCode\"><input name=\"bolHdrFoot03\" type=\"radio\" VALUE='F' $foot03></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"bolComment03\" value=\"" . rtrim($row['CECM03']) . "\" size=\"40\" maxlength=\"30\">";
	print "\n </tr> ";
	DspErrMsg($Err_CEHF03);
	DspErrMsg($Err_CECM03);

	$head04=Field_Checked($row['CEHF04'], "H");
	$foot04=Field_Checked($row['CEHF04'], "F");
	print "\n <tr><td class=\"colCode\"><input name=\"bolHdrFoot04\" type=\"radio\" VALUE='H' $head04></td> ";
	print "\n     <td class=\"colCode\"><input name=\"bolHdrFoot04\" type=\"radio\" VALUE='F' $foot04></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"bolComment04\" value=\"" . rtrim($row['CECM04']) . "\" size=\"40\" maxlength=\"30\">";
	print "\n </tr> ";
	DspErrMsg($Err_CEHF04);
	DspErrMsg($Err_CECM04);

	$head05=Field_Checked($row['CEHF05'], "H");
	$foot05=Field_Checked($row['CEHF05'], "F");
	print "\n <tr><td class=\"colCode\"><input name=\"bolHdrFoot05\" type=\"radio\" VALUE='H' $head05></td> ";
	print "\n     <td class=\"colCode\"><input name=\"bolHdrFoot05\" type=\"radio\" VALUE='F' $foot05></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"bolComment05\" value=\"" . rtrim($row['CECM05']) . "\" size=\"40\" maxlength=\"30\">";
	print "\n </tr> ";
	DspErrMsg($Err_CEHF05);
	DspErrMsg($Err_CECM05);

	$head06=Field_Checked($row['CEHF06'], "H");
	$foot06=Field_Checked($row['CEHF06'], "F");
	print "\n <tr><td class=\"colCode\"><input name=\"bolHdrFoot06\" type=\"radio\" VALUE='H' $head06></td> ";
	print "\n     <td class=\"colCode\"><input name=\"bolHdrFoot06\" type=\"radio\" VALUE='F' $foot06></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"bolComment06\" value=\"" . rtrim($row['CECM06']) . "\" size=\"40\" maxlength=\"30\">";
	print "\n </tr> ";
	DspErrMsg($Err_CEHF06);
	DspErrMsg($Err_CECM06);

	$head07=Field_Checked($row['CEHF07'], "H");
	$foot07=Field_Checked($row['CEHF07'], "F");
	print "\n <tr><td class=\"colCode\"><input name=\"bolHdrFoot07\" type=\"radio\" VALUE='H' $head07></td> ";
	print "\n     <td class=\"colCode\"><input name=\"bolHdrFoot07\" type=\"radio\" VALUE='F' $foot07></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"bolComment07\" value=\"" . rtrim($row['CECM07']) . "\" size=\"40\" maxlength=\"30\">";
	print "\n </tr> ";
	DspErrMsg($Err_CEHF07);
	DspErrMsg($Err_CECM07);

	$head08=Field_Checked($row['CEHF08'], "H");
	$foot08=Field_Checked($row['CEHF08'], "F");
	print "\n <tr><td class=\"colCode\"><input name=\"bolHdrFoot08\" type=\"radio\" VALUE='H' $head08></td> ";
	print "\n     <td class=\"colCode\"><input name=\"bolHdrFoot08\" type=\"radio\" VALUE='F' $foot08></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"bolComment08\" value=\"" . rtrim($row['CECM08']) . "\" size=\"40\" maxlength=\"30\">";
	print "\n </tr> ";
	DspErrMsg($Err_CEHF08);
	DspErrMsg($Err_CECM08);

	print "\n </table> ";
	print "\n </fieldset> ";


	print "\n  <a name=\"invoicing\"></a> ";
	print "\n  <fieldset class=\"legendBody\"> ";
	print "\n  <legend class=\"legendTitle\">Invoicing</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	print "\n     <tr><td>&nbsp;</td> ";
	print "\n         <td class=\"colhdr\">Sequence</td> ";
	print "\n         <td class=\"colhdr\">Print<br>Total</td> ";
	print "\n         <td class=\"colhdr\">Page<br>Break</td> ";
	print "\n     </tr> ";

	$chkTotLoc=Field_Checked($row['OITLOC'], "Y");
	$chkBrkLoc=Field_Checked($row['OIBLOC'], "Y");
	$textOvr=SetTextOvr($Err_OISLOC);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Location Number</span></td> ";
	Build_Sort_Select("sortLocationNumber", $row['OISLOC']);
	print "\n    <td class=\"colcode\"><input name=\"totLoc\" type=\"checkbox\" VALUE='Y' $chkTotLoc></td> ";
	print "\n    <td class=\"colcode\"><input name=\"brkLoc\" type=\"checkbox\" VALUE='Y' $chkBrkLoc></td> ";
	print "\n    </tr> ";
	DspErrMsg($Err_OISLOC);

	$chkTotOrd=Field_Checked($row['OITORD'], "Y");
	$chkBrkOrd=Field_Checked($row['OIBORD'], "Y");
	$textOvr=SetTextOvr($Err_OISORD);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Order Number</span></td> ";
	Build_Sort_Select("sortOrderNumber", $row['OISORD']);
	print "\n    <td class=\"colcode\"><input name=\"totOrd\" type=\"checkbox\" VALUE='Y' $chkTotOrd></td> ";
	print "\n    <td class=\"colcode\"><input name=\"brkOrd\" type=\"checkbox\" VALUE='Y' $chkBrkOrd></td> ";
	print "\n    </tr> ";
	DspErrMsg($Err_OISORD);

	$chkTotBlt=Field_Checked($row['OITBLT'], "Y");
	$chkBrkBlt=Field_Checked($row['OIBBLT'], "Y");
	$textOvr=SetTextOvr($Err_OISBLT);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Bill-To Number</span></td> ";
	Build_Sort_Select("sortBillToNumber", $row['OISBLT']);
	print "\n    <td class=\"colcode\"><input name=\"totBlt\" type=\"checkbox\" VALUE='Y' $chkTotBlt></td> ";
	print "\n    <td class=\"colcode\"><input name=\"brkBlt\" type=\"checkbox\" VALUE='Y' $chkBrkBlt></td> ";
	print "\n    </tr> ";
	DspErrMsg($Err_OISBLT);

	$chkTotBnm=Field_Checked($row['OITBNM'], "Y");
	$chkBrkBnm=Field_Checked($row['OIBBNM'], "Y");
	$textOvr=SetTextOvr($Err_OISBNM);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Bill-To Name</span></td> ";
	Build_Sort_Select("sortBillToName", $row['OISBNM']);
	print "\n    <td class=\"colcode\"><input name=\"totBnm\" type=\"checkbox\" VALUE='Y' $chkTotBnm></td> ";
	print "\n    <td class=\"colcode\"><input name=\"brkBnm\" type=\"checkbox\" VALUE='Y' $chkBrkBnm></td> ";
	print "\n    </tr> ";
	DspErrMsg($Err_OISBNM);

	$chkTotZip=Field_Checked($row['OITZIP'], "Y");
	$chkBrkZip=Field_Checked($row['OIBZIP'], "Y");
	$textOvr=SetTextOvr($Err_OISZip);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Bill-To Zip Code</span></td> ";
	Build_Sort_Select("sortBillToZipCode", $row['OISZIP']);
	print "\n    <td class=\"colcode\"><input name=\"totZip\" type=\"checkbox\" VALUE='Y' $chkTotZip></td> ";
	print "\n    <td class=\"colcode\"><input name=\"brkZip\" type=\"checkbox\" VALUE='Y' $chkBrkZip></td> ";
	print "\n    </tr> ";
	DspErrMsg($Err_OISZIP);

	$chkTotSls=Field_Checked($row['OITSLS'], "Y");
	$chkBrkSls=Field_Checked($row['OIBSLS'], "Y");
	$textOvr=SetTextOvr($Err_OISZIP);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Salesman Number</span></td> ";
	Build_Sort_Select("sortSalesmanNumber", $row['OISSLS']);
	print "\n    <td class=\"colcode\"><input name=\"totSls\" type=\"checkbox\" VALUE='Y' $chkTotSls></td> ";
	print "\n    <td class=\"colcode\"><input name=\"brkSls\" type=\"checkbox\" VALUE='Y' $chkBrkSls></td> ";
	print "\n    </tr> ";
	DspErrMsg($Err_OISZIP);

	$textOvr=SetTextOvr($Err_OISINV);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Invoice Number</span></td> ";
	Build_Sort_Select("sortInvoiceNumber", $row['OISINV']);
	print "\n    </tr> ";
	DspErrMsg($Err_OISINV);

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
	$edtVar= "";

	Concat_Field("@@rclr", $_POST['reclaimResourceLevel']=strtoupper($_POST['reclaimResourceLevel']));
	if (!isset($_POST['orderAvailabilityWarning'])) {$_POST['orderAvailabilityWarning']="N";} Concat_Field("@@oawc", $_POST['orderAvailabilityWarning']);
	Concat_Field("@@pd@@", $_POST['pastDueDollarAmount']);
	Concat_Field("@@pddc", $_POST['numberOfDaysPastDue']);
	Concat_Field("@@dudt", $_POST['dueDateUsed']=strtoupper($_POST['dueDateUsed']));
	if (!isset($_POST['autoPurgeOrderWhenClosed'])) {$_POST['autoPurgeOrderWhenClosed']="N";} Concat_Field("@@apgo", $_POST['autoPurgeOrderWhenClosed']);
	if (!isset($_POST['defaultShipQuantity'])) {$_POST['defaultShipQuantity']="N";} Concat_Field("@@shpo", $_POST['defaultShipQuantity']);
	if (!isset($_POST['allowOverShipments'])) {$_POST['allowOverShipments']="N";} Concat_Field("@@aovs", $_POST['allowOverShipments']);
	Concat_Field("@@oear", $_POST['oeToARAudit']=strtoupper($_POST['oeToARAudit']));
	Concat_Field("@@nmdq", $_POST['nbrDataQueueProcJobs']);
	Concat_Field("@@ccp@", $_POST['onlineCreditCardAuthorization']=strtoupper($_POST['onlineCreditCardAuthorization']));
	Concat_Field("@@mrch", $_POST['merchantCode']);
	if (!isset($_POST['useProductGroupPricing'])) {$_POST['useProductGroupPricing']="N";} Concat_Field("@@upgp", $_POST['useProductGroupPricing']);
	Concat_Field("@@oepo", $_POST['allowPOGeneration']=strtoupper($_POST['allowPOGeneration']));
	Concat_Field("@@mhld", $_POST['mfgHoldCode']=strtoupper($_POST['mfgHoldCode']));
	Concat_Field("@@dbnk", $_POST['defaultBank']);
	if (!isset($_POST['automaticItemWarehouseCreation'])) {$_POST['automaticItemWarehouseCreation']="N";} Concat_Field("@@aiwd", $_POST['automaticItemWarehouseCreation']);
	if (!isset($_POST['backupPriorToBillingUpdate'])) {$_POST['backupPriorToBillingUpdate']="N";} Concat_Field("@@bpbl", $_POST['backupPriorToBillingUpdate']);
	if (!isset($_POST['automaticOrderNumberAssignment'])) {$_POST['automaticOrderNumberAssignment']="N";} Concat_Field("@@assn", $_POST['automaticOrderNumberAssignment']);
	Concat_Field("@@lord", $_POST['lastOrderNumberUsed']);
	Concat_Field("@@linv", $_POST['lastInvoiceNumberUsed']);
	Concat_Field("@@lpl@", $_POST['lastPackingListNumber']);
	Concat_Field("@@lbl@", $_POST['lastBillOfLadingNumber']);
	Concat_Field("@@per@", $_POST['oeDistPeriod']);
	Concat_Field("@@arac", $_POST['arAcctNumber']);
	Concat_Field("@@arsb", $_POST['arSubAcctNumber']);
	Concat_Field("@@frac", $_POST['freightAcctNumber']);
	Concat_Field("@@frsb", $_POST['freightSubAcctNumber']);
	Concat_Field("@@txac", $_POST['salesTaxAcctNumber']);
	Concat_Field("@@txsb", $_POST['salesTaxSubAcctNumber']);
	Concat_Field("@@scac", $_POST['specialChgAcctNumber']);
	Concat_Field("@@scsb", $_POST['specialChgSubAcctNumber']);
	Concat_Field("@@ucd1", $_POST['userDefined1']);
	Concat_Field("@@ucd2", $_POST['userDefined2']);
	Concat_Field("@@ucd3", $_POST['userDefined3']);
	Concat_Field("@@ucd4", $_POST['userDefined4']);
	Concat_Field("@@ucd5", $_POST['userDefined5']);
	Concat_Field("@@sud1", $_POST['storeDefined1']);
	Concat_Field("@@sud2", $_POST['storeDefined2']);
	Concat_Field("@@sud3", $_POST['storeDefined3']);
	Concat_Field("@@sud4", $_POST['storeDefined4']);
	Concat_Field("@@sud5", $_POST['storeDefined5']);
	if (!isset($_POST['printCommentsOnlyNoDetail'])) {$_POST['printCommentsOnlyNoDetail']="N";} Concat_Field("@@bolc", $_POST['printCommentsOnlyNoDetail']);
	if (!isset($_POST['bolHdrFoot01']) || rtrim($_POST['bolComment01']) == "") {$_POST['bolHdrFoot01']=" ";} Concat_Field("@@hf01", $_POST['bolHdrFoot01']);
	Concat_Field("@@cm01", $_POST['bolComment01']);
	if (!isset($_POST['bolHdrFoot02']) || rtrim($_POST['bolComment02']) == "") {$_POST['bolHdrFoot02']=" ";} Concat_Field("@@hf02", $_POST['bolHdrFoot02']);
	Concat_Field("@@cm02", $_POST['bolComment02']);
	if (!isset($_POST['bolHdrFoot03']) || rtrim($_POST['bolComment03']) == "") {$_POST['bolHdrFoot03']=" ";} Concat_Field("@@hf03", $_POST['bolHdrFoot03']);
	Concat_Field("@@cm03", $_POST['bolComment03']);
	if (!isset($_POST['bolHdrFoot04']) || rtrim($_POST['bolComment04']) == "") {$_POST['bolHdrFoot04']=" ";} Concat_Field("@@hf04", $_POST['bolHdrFoot04']);
	Concat_Field("@@cm04", $_POST['bolComment04']);
	if (!isset($_POST['bolHdrFoot05']) || rtrim($_POST['bolComment05']) == "") {$_POST['bolHdrFoot05']=" ";} Concat_Field("@@hf05", $_POST['bolHdrFoot05']);
	Concat_Field("@@cm05", $_POST['bolComment05']);
	if (!isset($_POST['bolHdrFoot06']) || rtrim($_POST['bolComment06']) == "") {$_POST['bolHdrFoot06']=" ";} Concat_Field("@@hf06", $_POST['bolHdrFoot06']);
	Concat_Field("@@cm06", $_POST['bolComment06']);
	if (!isset($_POST['bolHdrFoot07']) || rtrim($_POST['bolComment07']) == "") {$_POST['bolHdrFoot07']=" ";} Concat_Field("@@hf07", $_POST['bolHdrFoot07']);
	Concat_Field("@@cm07", $_POST['bolComment07']);
	if (!isset($_POST['bolHdrFoot08']) || rtrim($_POST['bolComment08']) == "") {$_POST['bolHdrFoot08']=" ";} Concat_Field("@@hf08", $_POST['bolHdrFoot08']);
	Concat_Field("@@cm08", $_POST['bolComment08']);
	Concat_Field("@@sloc", $_POST['sortLocationNumber']);
	Concat_Field("@@sord", $_POST['sortOrderNumber']);
	Concat_Field("@@sblt", $_POST['sortBillToNumber']);
	Concat_Field("@@sbnm", $_POST['sortBillToName']);
	Concat_Field("@@szip", $_POST['sortBillToZipCode']);
	Concat_Field("@@ssls", $_POST['sortSalesmanNumber']);
	Concat_Field("@@sinv", $_POST['sortInvoiceNumber']);

	if (!isset($_POST['brkLoc'])) {$_POST['brkLoc']="N";} Concat_Field("@@bloc", $_POST['brkLoc']);
	if (!isset($_POST['brkOrd'])) {$_POST['brkOrd']="N";} Concat_Field("@@bord", $_POST['brkOrd']);
	if (!isset($_POST['brkBlt'])) {$_POST['brkBlt']="N";} Concat_Field("@@bblt", $_POST['brkBlt']);
	if (!isset($_POST['brkBnm'])) {$_POST['brkBnm']="N";} Concat_Field("@@bbnm", $_POST['brkBnm']);
	if (!isset($_POST['brkZip'])) {$_POST['brkZip']="N";} Concat_Field("@@bzip", $_POST['brkZip']);
	if (!isset($_POST['brkSls'])) {$_POST['brkSls']="N";} Concat_Field("@@bsls", $_POST['brkSls']);

	if (!isset($_POST['totLoc'])) {$_POST['totLoc']="N";} Concat_Field("@@tloc", $_POST['totLoc']);
	if (!isset($_POST['totOrd'])) {$_POST['totOrd']="N";} Concat_Field("@@tord", $_POST['totOrd']);
	if (!isset($_POST['totBlt'])) {$_POST['totBlt']="N";} Concat_Field("@@tblt", $_POST['totBlt']);
	if (!isset($_POST['totBnm'])) {$_POST['totBnm']="N";} Concat_Field("@@tbnm", $_POST['totBnm']);
	if (!isset($_POST['totZip'])) {$_POST['totZip']="N";} Concat_Field("@@tzip", $_POST['totZip']);
	if (!isset($_POST['totSls'])) {$_POST['totSls']="N";} Concat_Field("@@tsls", $_POST['totSls']);
	$edtVar .= "}{";

	$returnValue=Validate_Data($userProfile, $errFound, $edtVar, $errVar);
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc("C", "O/E Control", "", "", "", "", "");
		$includeName= "{$homePath}OEControl{$dataBaseID}.php";
		$fileName="OEControl{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HOECTL_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
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

	$pgm = i5_program_prepare("HOECTU_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HOECTU_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

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
	if (!$ret) {die("<br>Validate_Data (HOECTU_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['userProfile']    =$userProfile;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	return $returnValue;
}

?>