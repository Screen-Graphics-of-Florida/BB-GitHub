<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$backHome           = $_GET['backHome'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "A/P Control Maintenance";
$scriptName     = "APControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HAPCTU_E";

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

	require_once 'CheckEnterChg.php';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';

	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.reclaimResourceLev.value ==\"\" || ";
	print "\n     document.Chg.wrnOnDatesInVouchEntry.value ==\"\" || ";
	print "\n     document.Chg.remittAdviceOpt.value ==\"\" || ";
	print "\n     document.Chg.apDistPeriod.value ==\"\" || ";
	print "\n     document.Chg.GlDistFeed.value ==\"\" || ";
	print "\n     document.Chg.GlPaymentFeed.value ==\"\" || ";
	print "\n     document.Chg.apAcctNumber.value ==\"\" || ";
	print "\n     document.Chg.apDiscAcctNumber.value ==\"\" || ";
	print "\n     document.Chg.ageCreditInvoicesBasedOn.value ==\"\" || ";
	print "\n     document.Chg.baseAgingOnCode.value ==\"\" ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";

	print "\n if (editNum(document.Chg.remittAdviceOpt, 1, 0) && ";
	print "\n     editNum(document.Chg.lastVouchNumberUsed, 9, 0) && ";
	print "\n     editNum(document.Chg.lastRecurringVouchNumberUsed, 9, 0) && ";
	print "\n     editNum(document.Chg.lastTempVendNumberUsed, 7, 0) && ";
	print "\n     editNum(document.Chg.apDistPeriod, 4, 0) && ";
	print "\n     editNum(document.Chg.apAcctNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.apSubNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.apDiscAcctNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.apDiscSubNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.corpapCompNumber, 2, 0) && ";
	print "\n     editNum(document.Chg.corpapFacNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.corpDiscCompNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.corpDiscFacNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.agingNumberOfDays1, 3, 0) && ";
	print "\n     editNum(document.Chg.agingNumberOfDays2, 3, 0) && ";
	print "\n     editNum(document.Chg.agingNumberOfDays3, 3, 0) && ";
	print "\n     editNum(document.Chg.agingNumberOfDays4, 3, 0) && ";
	print "\n     editNum(document.Chg.agingNumberOfDays5, 3, 0) && ";
	print "\n     editNum(document.Chg.expAcctNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.expSubNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.discPercent, 3, 2)) ";
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
	$pageID = "APCONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From APCTRL ";
		$stmtSQL .= " Where RRN(APCTRL)=1 ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hapctu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hapctu_OPT['sec_01'];
	$sec_02=$hapctu_OPT['sec_02'];
	$sec_03="N";
	$sec_04="N";
	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#general\">General</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#autoAssign\">Auto Assign</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#accounting\">Accounting</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#aging\">Aging</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#newVendorDefaults\">New Vendor Defaults</a></td> ";
	print "\n   </tr> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#userDefined\">User Defined</a></td> ";
	if ($HDMCRL>"0") {print "\n <td class=\"quickLinkTabs\"><a href=\"#multiCurrency\">Multi-Currency</a></td> ";}
	print "\n   </tr> ";
	print "\n </table> ";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField= "reclaimResourceLev";
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_CPRCLR=DecatErr_Field("@@rclr", "reclaimResourceLev");
			$Err_CPWDUP=DecatErr_Field("@@wdup", "reqF14ForDupInvoices");
			$Err_CPWIDT=DecatErr_Field("@@widt", "wrnOnDatesInVouchEntry");
			$Err_CPREMT=DecatErr_Field("@@remt", "remittAdviceOpt");
			$Err_CPVOUC=DecatErr_Field("@@vouc", "lastVouchNumberUsed");
			$Err_CPRVOU=DecatErr_Field("@@rvou", "lastRecurringVouchNumberUsed");
			$Err_CPTEMP=DecatErr_Field("@@temp", "lastTempVendNumberUsed");
			$Err_CPDPER=DecatErr_Field("@@dper", "apDistPeriod");
			$Err_CPAPGL=DecatErr_Field("@@apgl", "feedApToGl");
			$Err_CPFEDS=DecatErr_Field("@@feds", "GlDistFeed");
			$Err_CPFEPY=DecatErr_Field("@@fepy", "GlPaymentFeed");
			$Err_CPAUTO=DecatErr_Field("@@auto", "restoreApFeedIfErr");
			$Err_CPINTR=DecatErr_Field("@@intr", "genInterCompTrans");
			$Err_CPAPA =DecatErr_Field("@@apa@", "apAcctNumber");
			$Err_CPAPS =DecatErr_Field("@@aps@", "apSubNumber");
			$Err_CPDSA =DecatErr_Field("@@dsa@", "apDiscAcctNumber");
			$Err_CPDSS =DecatErr_Field("@@dss@", "apDiscSubNumber");
			$Err_CPCORP=DecatErr_Field("@@corp", "procApAtCorpLvl");
			$Err_CPAPCO=DecatErr_Field("@@apco", "corpapCompNumber");
			$Err_CPAPFA=DecatErr_Field("@@apfa", "corpapFacNumber");
			$Err_CPDSCO=DecatErr_Field("@@dsco", "corpDiscCompNumber");
			$Err_CPDSFA=DecatErr_Field("@@dsfa", "corpDiscFacNumber");
			$Err_CPCIAO=DecatErr_Field("@@ciao", "ageCreditInvoicesBasedOn");
			$Err_CPBAON=DecatErr_Field("@@baon", "baseAgingOnCode");
			$Err_CPAGE1=DecatErr_Field("@@age1", "agingNumberOfDays1");
			$Err_CPAGE2=DecatErr_Field("@@age2", "agingNumberOfDays2");
			$Err_CPAGE3=DecatErr_Field("@@age3", "agingNumberOfDays3");
			$Err_CPAGE4=DecatErr_Field("@@age4", "agingNumberOfDays4");
			$Err_CPAGE5=DecatErr_Field("@@age5", "agingNumberOfDays5");
			$Err_CPTRMS=DecatErr_Field("@@trms", "termsCode");
			$Err_CPVTYP=DecatErr_Field("@@vtyp", "vendorType");
			$Err_CP1099=DecatErr_Field("@@1099", "ten99Code");
			$Err_CPEXPA=DecatErr_Field("@@expa", "expAcctNumber");
			$Err_CPEXPS=DecatErr_Field("@@exps", "expSubNumber");
			$Err_CPDSPT=DecatErr_Field("@@dspt", "discPercent");
			$Err_CPDSC1=DecatErr_Field("@@dsc1", "distAlpha1");
			$Err_CPDSC2=DecatErr_Field("@@dsc2", "distAlpha2");
			$Err_CPSCR1=DecatErr_Field("@@scr1", "vendAlpha1");
			$Err_CPRP11=DecatErr_Field("@@rp11", "vendAlpha11");
			$Err_CPRP12=DecatErr_Field("@@rp12", "vendAlpha12");
			$Err_CPSCR2=DecatErr_Field("@@scr2", "vendAlpha2");
			$Err_CPRP21=DecatErr_Field("@@rp21", "vendAlpha21");
			$Err_CPRP22=DecatErr_Field("@@rp22", "vendAlpha22");
			$Err_CPSCR3=DecatErr_Field("@@scr3", "vendAlpha3");
			$Err_CPRP31=DecatErr_Field("@@rp31", "vendAlpha31");
			$Err_CPRP32=DecatErr_Field("@@rp32", "vendAlpha32");
			$Err_CPSCR4=DecatErr_Field("@@scr4", "vendAlpha4");
			$Err_CPRP41=DecatErr_Field("@@rp41", "vendAlpha41");
			$Err_CPRP42=DecatErr_Field("@@rp42", "vendAlpha42");
			$Err_CPSCR5=DecatErr_Field("@@scr5", "vendAlpha5");
			$Err_CPRP51=DecatErr_Field("@@rp51", "vendAlpha51");
			$Err_CPRP52=DecatErr_Field("@@rp52", "vendAlpha52");
			if ($HDMCRL>0)  {
				$Err_CPPRMC=DecatErr_Field("@@prmc", "procMultiCurr");
				$Err_CPMCRT=DecatErr_Field("@@mcrt", "apCurRateType");
			}
			$Err_CRTSTP=DecatErr_Field("@@tstp", "originalTimeStamp");
		}
		$row['CPRCLR']=Decat_Field("@@rclr", $edtVar);
		$row['CPWDUP']=Decat_Field("@@wdup", $edtVar);
		$row['CPWIDT']=Decat_Field("@@widt", $edtVar);
		$row['CPREMT']=Decat_Field("@@remt", $edtVar);
		$row['CPVOUC']=Decat_Field("@@vouc", $edtVar);
		$row['CPRVOU']=Decat_Field("@@rvou", $edtVar);
		$row['CPTEMP']=Decat_Field("@@temp", $edtVar);
		$row['CPDPER']=Decat_Field("@@dper", $edtVar);
		$row['CPAPGL']=Decat_Field("@@apgl", $edtVar);
		$row['CPFEDS']=Decat_Field("@@feds", $edtVar);
		$row['CPFEPY']=Decat_Field("@@fepy", $edtVar);
		$row['CPAUTO']=Decat_Field("@@auto", $edtVar);
		$row['CPINTR']=Decat_Field("@@intr", $edtVar);
		$row['CPAPA']=Decat_Field("@@apa@", $edtVar);
		$row['CPTAPS']=Decat_Field("@@aps@", $edtVar);
		$row['CPDSA']=Decat_Field("@@dsa@", $edtVar);
		$row['CPDSS']=Decat_Field("@@dss@", $edtVar);
		$row['CPCORP']=Decat_Field("@@corp", $edtVar);
		$row['CPAPCO']=Decat_Field("@@apco", $edtVar);
		$row['CPAPFA']=Decat_Field("@@apfa", $edtVar);
		$row['CPDSCO']=Decat_Field("@@dsco", $edtVar);
		$row['CPDSFA']=Decat_Field("@@dsfa", $edtVar);
		$row['CPCIAO']=Decat_Field("@@ciao", $edtVar);
		$row['CPBAON']=Decat_Field("@@baon", $edtVar);
		$row['CPAGE1']=Decat_Field("@@age1", $edtVar);
		$row['CPAGE2']=Decat_Field("@@age2", $edtVar);
		$row['CPAGE3']=Decat_Field("@@age3", $edtVar);
		$row['CPAGE4']=Decat_Field("@@age4", $edtVar);
		$row['CPAGE5']=Decat_Field("@@age5", $edtVar);
		$row['CPTRMS']=Decat_Field("@@trms", $edtVar);
		$row['CPVTYP']=Decat_Field("@@vtyp", $edtVar);
		$row['CP1099']=Decat_Field("@@1099", $edtVar);
		$row['CPEXPA']=Decat_Field("@@expa", $edtVar);
		$row['CPEXPS']=Decat_Field("@@exps", $edtVar);
		$row['CPDSPT']=Decat_Field("@@dspt", $edtVar);
		$row['CPDSC1']=Decat_Field("@@dsc1", $edtVar);
		$row['CPDSC2']=Decat_Field("@@dsc2", $edtVar);
		$row['CPSCR1']=Decat_Field("@@scr1", $edtVar);
		$row['CPRP11']=Decat_Field("@@rp11", $edtVar);
		$row['CPRP12']=Decat_Field("@@rp12", $edtVar);
		$row['CPSCR2']=Decat_Field("@@scr2", $edtVar);
		$row['CPRP21']=Decat_Field("@@rp21", $edtVar);
		$row['CPRP22']=Decat_Field("@@rp22", $edtVar);
		$row['CPSCR3']=Decat_Field("@@scr3", $edtVar);
		$row['CPRP31']=Decat_Field("@@rp31", $edtVar);
		$row['CPRP32']=Decat_Field("@@rp32", $edtVar);
		$row['CPSCR4']=Decat_Field("@@scr4", $edtVar);
		$row['CPRP41']=Decat_Field("@@rp41", $edtVar);
		$row['CPRP42']=Decat_Field("@@rp42", $edtVar);
		$row['CPSCR5']=Decat_Field("@@scr5", $edtVar);
		$row['CPRP51']=Decat_Field("@@rp51", $edtVar);
		$row['CPRP52']=Decat_Field("@@rp52", $edtVar);
		if ($HDMCRL>0)  {
			$row['CPPRMC']=Decat_Field("@@prmc", $edtVar);
			$row['CPMCRT']=Decat_Field("@@mcrt", $edtVar);
		}
		$row['CRTSTP']=Decat_Field("@@tstp", $edtVar);
	} else {
		$row['CPDPER']=PeriodInputFromCYP($row['CPDPER']);
		$row[CPDSPT]=100*($row[CPDSPT]);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_CRTSTP);
	Build_DspFld("A/P Release Version",$HDAPRL,"","A");
	DspErrMsg($Err_CRTSTP);
	Build_DspFld("A/P Library Level",$HDAPLL,"","A");
	print "\n <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['CPTSTP']) . "\"></td></tr> ";
	print "\n </table> ";

	print "\n <a name=\"general\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">General</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	Build_Fld_Entry("Reclaim Resource Level","reclaimResourceLev","inputalph","RECLAIMLVL","CPRCLR",$row[CPRCLR],$Err_CPRCLR,"1","1","Y","","");
	Build_Fld_Entry("Require F14 For Duplicate Invoices","reqF14ForDupInvoices","inputalph","YORN","CPWDUP",$row[CPWDUP],$Err_CPWDUP,"1","1","Y","","");
	Build_Fld_Entry("Warning On Dates In Voucher Entry","wrnOnDatesInVouchEntry","inputalph","WRNDTEVCHE","CPWIDT",$row[CPWIDT],$Err_CPWIDT,"1","1","Y","","");
	Build_Fld_Entry("Remittance Advice Options","remittAdviceOpt","inputalph","REMITADVIC","CPREMT",$row[CPREMT],$Err_CPREMT,"1","1","Y","","");

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"autoAssign\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Auto Assign</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Last Voucher Number Used","lastVouchNumberUsed","inputnmbr","","CPVOUC",$row[CPVOUC],$Err_CPVOUC,"9","9","","","");
	Build_Fld_Entry("Last Recurring Voucher Number Used","lastRecurringVouchNumberUsed","inputnmbr","","CPRVOU",$row[CPRVOU],$Err_CPRVOU,"9","9","","","");
	Build_Fld_Entry("Last Temporary Vendor Number Used","lastTempVendNumberUsed","inputnmbr","","CPTEMP",$row[CPTEMP],$Err_CPTEMP,"9","7","","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"accounting\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Accounting</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_CPDPER);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>A/P Distribution Period</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"apDistPeriod\" value=\"" . rtrim($row['CPDPER']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;periodFld=apDistPeriod\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n </tr> ";
	DspErrMsg($Err_CPDPER);

	Build_Fld_Entry("Feed A/P To G/L","feedApToGl","inputalph","YORN","CPAPGL",$row[CPAPGL],$Err_CPAPGL,"1","1","Y","","");
	Build_Fld_Entry("G/L Distribution Feed","GlDistFeed","inputalph","DORS","CPFEDS",$row[CPFEDS],$Err_CPFEDS,"1","1","Y","","");
	Build_Fld_Entry("G/L Payment Feed","GlPaymentFeed","inputalph","DORS","CPFEPY",$row[CPFEPY],$Err_CPFEPY,"1","1","Y","","");
	Build_Fld_Entry("Restore A/P Feed If Errors Exist","restoreApFeedIfErr","inputalph","YORN","CPAUTO",$row[CPAUTO],$Err_CPAUTO,"1","1","Y","","");
	Build_Fld_Entry("Generate Inter-Company Transactions","genInterCompTrans","inputalph","YORN","CPINTR",$row[CPINTR],$Err_CPINTR,"1","1","Y","","");
	print "\n </table> ";

	print "\n <table $contentTable> ";
	print "\n <tr><td>&nbsp;</td> ";
	print "\n <td class=\"colhdr\">Account</td> ";
	print "\n </tr> ";

	$row['CPAPA']=Default_Zero($row['CPAPA']);
	$row['CPAPS']=Default_Zero($row['CPAPS']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CPAPA],$row[CPAPS])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CPAPA);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>A/P</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"apAcctNumber\" value=\"" . rtrim($row['CPAPA']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"apSubNumber\" value=\"" . rtrim($row['CPAPS']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=apAcctNumber&amp;subFld=apSubNumber&amp;descFld=apAcctNumberDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"apAcctNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CPAPA);

	$row['CPDSA']=Default_Zero($row['CPDSA']);
	$row['CPDSS']=Default_Zero($row['CPDSS']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CPDSA],$row[CPDSS])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CPDSA);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>A/P Discount</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"apDiscAcctNumber\" value=\"" . rtrim($row['CPDSA']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"apDiscSubNumber\" value=\"" . rtrim($row['CPDSS']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=apDiscAcctNumber&amp;subFld=apDiscSubNumber&amp;descFld=apDiscAcctNumberDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"apDiscAcctNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CPDSA);

	print "\n </table> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Corporate</legend> ";
	print "\n <table $contentTable> ";
	Build_Fld_Entry("Process A/P At Corporate Level","procApAtCorpLvl","inputalph","YORN","CPCORP",$row[CPCORP],$Err_CPCORP,"1","1","Y","","");
	print "\n </table> ";

	print "\n <table $contentTable> ";
	print "\n <tr><td>&nbsp;</td> ";
	print "\n <td class=\"colhdr\">Company/Facility</td> ";
	print "\n </tr> ";

	$row['CPAPCO']=Default_Zero($row['CPAPCO']);
	$row['CPAPFA']=Default_Zero($row['CPAPFA']);
	$fieldDesc=RetValue("(CFCO#,CFFAC#)=($row[CPAPCO],$row[CPAPFA])", "HDCFAC", "CFCFNM");
	$textOvr=SetTextOvr($Err_CPAPCO);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Corporate A/P</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"corpapCompNumber\" value=\"" . rtrim($row['CPAPCO']) . "\" size=\"2\" maxlength=\"2\"> - ";
	print "\n                             <input type=\"text\"   name=\"corpapFacNumber\" value=\"" . rtrim($row['CPAPFA']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=corpapCompNumber&amp;fldFac=corpapFacNumber&amp;fldDesc=corpapCompNumberDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"corpapCompNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CPAPCO);

	$row['CPDSCO']=Default_Zero($row['CPDSCO']);
	$row['CPDSFA']=Default_Zero($row['CPDSFA']);
	$fieldDesc=RetValue("(CFCO#,CFFAC#)=($row[CPDSCO],$row[CPDSFA])", "HDCFAC", "CFCFNM");
	$textOvr=SetTextOvr($Err_CPDSCO);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Corporate Discount</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"corpDiscCompNumber\" value=\"" . rtrim($row['CPDSCO']) . "\" size=\"2\" maxlength=\"2\"> - ";
	print "\n                             <input type=\"text\"   name=\"corpDiscFacNumber\" value=\"" . rtrim($row['CPDSFA']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldCo=corpDiscCompNumber&amp;fldFac=corpDiscFacNumber&amp;fldDesc=corpDiscCompNumberDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"corpDiscCompNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CPDSCO);

	print "\n </table> ";
	print "\n </fieldset> ";
	print "\n </fieldset> ";

	print "\n <a name=\"aging\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Aging</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Age Credit Invoices Based On","ageCreditInvoicesBasedOn","inputalph","AGEARCRDT","CPCIAO",$row[CPCIAO],$Err_CPCIAO,"1","1","Y","","");
	Build_Fld_Entry("Base Aging On Code","baseAgingOnCode","inputalph","AGEDATE","CPBAON",$row[CPBAON],$Err_CPBAON,"1","1","Y","","");
	print "\n </table> ";

	print "\n <table $contentTable> ";
	print "\n <tr><td>&nbsp;</td> ";
	print "\n <td class=\"colhdr\">Aging Number Of Days:</td> ";
	print "\n </tr> ";
	Build_Fld_Entry("Bucket 1","agingNumberOfDays1","inputnmbr","","CPAGE1",$row[CPAGE1],$Err_CPAGE1,"3","3","","","");
	Build_Fld_Entry("Bucket 2","agingNumberOfDays2","inputnmbr","","CPAGE2",$row[CPAGE2],$Err_CPAGE2,"3","3","","","");
	Build_Fld_Entry("Bucket 3","agingNumberOfDays3","inputnmbr","","CPAGE3",$row[CPAGE3],$Err_CPAGE3,"3","3","","","");
	Build_Fld_Entry("Bucket 4","agingNumberOfDays4","inputnmbr","","CPAGE4",$row[CPAGE4],$Err_CPAGE4,"3","3","","","");
	Build_Fld_Entry("Bucket 5","agingNumberOfDays5","inputnmbr","","CPAGE5",$row[CPAGE5],$Err_CPAGE5,"3","3","","","");

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"newVendorDefaults\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">New Vendor Defaults</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	$fieldDesc=RetValue("VTRMS='$row[CPTRMS]'", "APVTRM", "VTVTDS");
	$textOvr=SetTextOvr($Err_CPTRMS);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Terms Code</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"termsCode\" value=\"" . rtrim($row['CPTRMS']) . "\" size=\"2\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}VendorTermsCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=termsCode&amp;fldDesc=termsCodeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"termsCodeDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CPTRMS);

	$fieldDesc=RetValue("VTVTYP='$row[CPVTYP]'", "HDVTYP", "VTDESC");
	$textOvr=SetTextOvr($Err_CPVTYP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Vendor Type</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"vendorType\" value=\"" . rtrim($row['CPVTYP']) . "\" size=\"2\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}VendorTypeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=vendorType&amp;fldDesc=vendorTypeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"vendorTypeDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CPVTYP);

	$fieldDesc=RetValue("PTPTCD='$row[CP1099]'", "APP109", "PTTPDS");
	$textOvr=SetTextOvr($Err_CP1099);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>1099 Code</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"ten99Code\" value=\"" . rtrim($row['CP1099']) . "\" size=\"2\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}Vendor1099Search.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=ten99Code&amp;fldDesc=ten99CodeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"ten99CodeDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CP1099);

	$row['CPEXPA']=Default_Zero($row['CPEXPA']);
	$row['CPEXPS']=Default_Zero($row['CPEXPS']);
	$fieldDesc=RetValue("(CHACCT,CHSUB)=($row[CPEXPA],$row[CPEXPS])", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CPEXPA);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Expense Account</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"expAcctNumber\" value=\"" . rtrim($row['CPEXPA']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"expSubNumber\" value=\"" . rtrim($row['CPEXPS']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=expAcctNumber&amp;subFld=expSubNumber&amp;descFld=expAcctNumberDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"expAcctNumberDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CPEXPA);

	Build_Fld_Entry("Discount Percent","discPercent","inputnmbr","","CPDSPT",$row[CPDSPT],$Err_CPDSPT,"7","7","","","");

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"userDefined\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">User Defined</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	print "\n <tr><td>&nbsp;</td> ";
	print "\n     <td class=\"colhdr\">Description</td> ";
	print "\n     <td class=\"colhdr\" colspan=\"2\">Report Heading</td> ";
	print "\n </tr> ";

	Build_Fld_Entry("Distribution Alpha 1","distAlpha1","inputalph","","CPDSC1",$row[CPDSC1],$Err_CPDSC1,"23","16","","","");
	Build_Fld_Entry("Distribution Alpha 2","distAlpha2","inputalph","","CPDSC2",$row[CPDSC2],$Err_CPDSC2,"23","16","","","");

	$textOvr=SetTextOvr($Err_CPSCR1);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Vendor Alpha 1</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha1\" value=\"" . rtrim($row['CPSCR1']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha11\" value=\"" . rtrim($row['CPRP11']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha12\" value=\"" . rtrim($row['CPRP12']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_CPSCR1);

	$textOvr=SetTextOvr($Err_CPSCR2);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Vendor Alpha 2</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha2\" value=\"" . rtrim($row['CPSCR2']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha21\" value=\"" . rtrim($row['CPRP21']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha22\" value=\"" . rtrim($row['CPRP22']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_CPSCR2);

	$textOvr=SetTextOvr($Err_CPSCR3);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Vendor Alpha 3</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha3\" value=\"" . rtrim($row['CPSCR3']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha31\" value=\"" . rtrim($row['CPRP31']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha32\" value=\"" . rtrim($row['CPRP32']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_CPSCR3);

	$textOvr=SetTextOvr($Err_CPSCR4);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Vendor Alpha 4</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha4\" value=\"" . rtrim($row['CPSCR4']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha41\" value=\"" . rtrim($row['CPRP41']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha42\" value=\"" . rtrim($row['CPRP42']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_CPSCR4);

	$textOvr=SetTextOvr($Err_CPSCR5);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Vendor Alpha 5</span></td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha5\" value=\"" . rtrim($row['CPSCR5']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha51\" value=\"" . rtrim($row['CPRP51']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\"   name=\"vendAlpha52\" value=\"" . rtrim($row['CPRP52']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_CPSCR5);
	print "\n </table> ";
	print "\n </fieldset> ";

	// Multi-Currency Processing
	if ($HDMCRL>0)  {
		print "\n  <a name=\"multiCurrency\"></a> ";
		print "\n  <fieldset class=\"legendBody\"> ";
		print "\n  <legend class=\"legendTitle\">Multi-Currency</legend> ";
		require 'TopOfForm.php';
		print "\n <table $contentTable>";
		Build_Fld_Entry("Process Multi-Currency","procMultiCurr","inputalph","YORN","CPPRMC",$row[CPPRMC],$Err_CPPRMC,"1","1","Y","","");

		$fieldDesc=RetValue("RYTYPE='$row[CPMCRT]'", "HDRTYP", "RYDESC");
		$textOvr=SetTextOvr($Err_CPMCRT);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>A/P Currency Rate Type</span></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"apCurRateType\" value=\"" . rtrim($row['CPMCRT']) . "\" size=\"10\" maxlength=\"10\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyRateTypeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=apCurRateType&amp;fldDesc=apCurRateTypeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"apCurRateTypeDesc\">$fieldDesc</span></td>";
		print "\n </tr> ";
		DspErrMsg($Err_CPMCRT);
		print "\n </table> ";
		print "\n </fieldset> ";
	}

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
	Concat_Field("@@rclr", $_POST['reclaimResourceLev']=strtoupper($_POST['reclaimResourceLev']));
	if (!isset($_POST['reqF14ForDupInvoices'])) {$_POST['reqF14ForDupInvoices']="N";} Concat_Field("@@wdup", $_POST['reqF14ForDupInvoices']);
	Concat_Field("@@widt", $_POST['wrnOnDatesInVouchEntry']=strtoupper($_POST['wrnOnDatesInVouchEntry']));
	Concat_Field("@@remt", $_POST['remittAdviceOpt']);
	Concat_Field("@@vouc", $_POST['lastVouchNumberUsed']);
	Concat_Field("@@rvou", $_POST['lastRecurringVouchNumberUsed']);
	Concat_Field("@@temp", $_POST['lastTempVendNumberUsed']);
	Concat_Field("@@dper", $_POST['apDistPeriod']);
	if (!isset($_POST['feedApToGl'])) {$_POST['feedApToGl']="N";} Concat_Field("@@apgl", $_POST['feedApToGl']);
	Concat_Field("@@feds", $_POST['GlDistFeed']=strtoupper($_POST['GlDistFeed']));
	Concat_Field("@@fepy", $_POST['GlPaymentFeed']=strtoupper($_POST['GlPaymentFeed']));
	if (!isset($_POST['restoreApFeedIfErr'])) {$_POST['restoreApFeedIfErr']="N";} Concat_Field("@@auto", $_POST['restoreApFeedIfErr']);
	if (!isset($_POST['genInterCompTrans'])) {$_POST['genInterCompTrans']="N";} Concat_Field("@@intr", $_POST['genInterCompTrans']);
	Concat_Field("@@apa@", $_POST['apAcctNumber']);
	Concat_Field("@@aps@", $_POST['apSubNumber']);
	Concat_Field("@@dsa@", $_POST['apDiscAcctNumber']);
	Concat_Field("@@dss@", $_POST['apDiscSubNumber']);
	if (!isset($_POST['procApAtCorpLvl'])) {$_POST['procApAtCorpLvl']="N";} Concat_Field("@@corp", $_POST['procApAtCorpLvl']);
	Concat_Field("@@apco", $_POST['corpapCompNumber']);
	Concat_Field("@@apfa", $_POST['corpapFacNumber']);
	Concat_Field("@@dsco", $_POST['corpDiscCompNumber']);
	Concat_Field("@@dsfa", $_POST['corpDiscFacNumber']);
	Concat_Field("@@ciao", $_POST['ageCreditInvoicesBasedOn']=strtoupper($_POST['ageCreditInvoicesBasedOn']));
	Concat_Field("@@baon", $_POST['baseAgingOnCode']=strtoupper($_POST['baseAgingOnCode']));
	Concat_Field("@@age1", $_POST['agingNumberOfDays1']);
	Concat_Field("@@age2", $_POST['agingNumberOfDays2']);
	Concat_Field("@@age3", $_POST['agingNumberOfDays3']);
	Concat_Field("@@age4", $_POST['agingNumberOfDays4']);
	Concat_Field("@@age5", $_POST['agingNumberOfDays5']);
	Concat_Field("@@trms", $_POST['termsCode']=strtoupper($_POST['termsCode']));
	Concat_Field("@@vtyp", $_POST['vendorType']=strtoupper($_POST['vendorType']));
	Concat_Field("@@1099", $_POST['ten99Code']=strtoupper($_POST['ten99Code']));
	Concat_Field("@@expa", $_POST['expAcctNumber']);
	Concat_Field("@@exps", $_POST['expSubNumber']);
	Concat_Field("@@dspt", $_POST['discPercent']);
	Concat_Field("@@dsc1", $_POST['distAlpha1']);
	Concat_Field("@@dsc2", $_POST['distAlpha2']);
	Concat_Field("@@scr1", $_POST['vendAlpha1']);
	Concat_Field("@@rp11", $_POST['vendAlpha11']);
	Concat_Field("@@rp12", $_POST['vendAlpha12']);
	Concat_Field("@@scr2", $_POST['vendAlpha2']);
	Concat_Field("@@rp21", $_POST['vendAlpha21']);
	Concat_Field("@@rp22", $_POST['vendAlpha22']);
	Concat_Field("@@scr3", $_POST['vendAlpha3']);
	Concat_Field("@@rp31", $_POST['vendAlpha31']);
	Concat_Field("@@rp32", $_POST['vendAlpha32']);
	Concat_Field("@@scr4", $_POST['vendAlpha4']);
	Concat_Field("@@rp41", $_POST['vendAlpha41']);
	Concat_Field("@@rp42", $_POST['vendAlpha42']);
	Concat_Field("@@scr5", $_POST['vendAlpha5']);
	Concat_Field("@@rp51", $_POST['vendAlpha51']);
	Concat_Field("@@rp52", $_POST['vendAlpha52']);
	if ($HDMCRL>0)  {
		if (!isset($_POST['procMultiCurr'])) {$_POST['procMultiCurr']="N";} Concat_Field("@@prmc", $_POST['procMultiCurr']);
		Concat_Field("@@mcrt", $_POST['apCurRateType']=strtoupper($_POST['apCurRateType']));
	}
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HAPCTU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "A/P Control", "", "", "", "", "");
		$includeName= "{$homePath}APControl{$dataBaseID}.php";
		$fileName="APControl{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HAPCTL_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>