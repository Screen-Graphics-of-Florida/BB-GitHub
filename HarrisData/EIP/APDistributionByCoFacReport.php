<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$backHome           = $_GET['backHome'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$reportSelType      = $_GET['reportSelType'];
$jobSbmSched        = $_GET['jobSbmSched'];
$resetSelectionFlag = $_GET['resetSelectionFlag'];
$rtvSelection       = $_GET['rtvSelection'];
$saveSelection      = $_GET['saveSelection'];
$scheduleJobSwitch  = $_GET['scheduleJobSwitch'];
$selScheduleJob     = $_GET['selScheduleJob'];
$submitSchedule     = $_GET['submitSchedule'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once "APControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title            = "A/P Distribution By Company/Facility Report";
$scriptName            = "APDistributionByCoFacReport.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CAPDBF";
$submitEnvProgram      = "HAPDBF";
$submitEnvPrinter      = "QSYSPRT";
$submitScheduleScript  = "";
$applicationID         = "AP";

if (is_null($tag)) {$tag="REPORT";}

if ($tag == "REPORT") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(chgForm) { ";
	print "\n   if (editNum(document.Chg.frDistPeriod, 4, 0) ";
	print "\n    && editNum(document.Chg.toDistPeriod, 4, 0) ";
	print "\n    && editNum(document.Chg.frCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.toCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.frFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.toFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.frAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.toAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.frSubaccount, 4, 0) ";
	print "\n    && editNum(document.Chg.toSubaccount, 4, 0) ";
	print "\n    && editNum(document.Chg.frVendor, 7, 0) ";
	print "\n    && editNum(document.Chg.toVendor, 7, 0) ";

	print "\n    && editNum(document.Chg.spCo01, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc01, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo02, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc02, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo03, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc03, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo04, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc04, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo05, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc05, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo06, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc06, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo07, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc07, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo08, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc08, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo09, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc09, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo10, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc10, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo11, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc11, 4, 0) ";

	print "\n    && editNum(document.Chg.spCo12, 2, 0) ";
	print "\n    && editNum(document.Chg.spFc12, 4, 0) ";

	print "\n    && editFromToAll(document.Chg.frDistPeriod, document.Chg.toDistPeriod, document.Chg.allDistPeriod, 'DP') ";
	print "\n    && editFromToAll(document.Chg.frCompany, document.Chg.toCompany, document.Chg.allCompany, 2) ";
	print "\n    && editFromToAll(document.Chg.frFacility, document.Chg.toFacility, document.Chg.allFacility, 4) ";
	print "\n    && editFromToAll(document.Chg.frAccount, document.Chg.toAccount, document.Chg.allAccount, 4) ";
	print "\n    && editFromToAll(document.Chg.frSubaccount, document.Chg.toSubaccount, document.Chg.allSubaccount, 4) ";
	print "\n    && editFromToAll(document.Chg.frVendor, document.Chg.toVendor, document.Chg.allVendor, 7) ";
	print "\n    ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "APDISTCOFACREPORT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="longDescription";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			$Err_LDSC=DecatErr_Field("@@ldsc", "longDescription");

			if ($HDMCRL>0 && $CPPRMC=="Y") {
				$Err_IDCR=DecatErr_Field("@@idcr", "currencyID");
				$Err_CURT=DecatErr_Field("@@curt", "currencyType");
			}

			$Err_FPER=DecatErr_Field("@@fper", "frDistPeriod");
			$Err_TPER=DecatErr_Field("@@tper", "toDistPeriod");
			$Err_APER=DecatErr_Field("@@aper", "allDistPeriod");

			$Err_FCO=DecatErr_Field("@@fco@", "frCompany");
			$Err_TCO=DecatErr_Field("@@tco@", "toCompany");
			$Err_ACO=DecatErr_Field("@@aco@", "allCompany");

			$Err_FFAC=DecatErr_Field("@@ffac", "frFacility");
			$Err_TFAC=DecatErr_Field("@@tfac", "toFacility");
			$Err_AFAC=DecatErr_Field("@@afac", "allFacility");

			$Err_FACC=DecatErr_Field("@@facc", "frAccount");
			$Err_TACC=DecatErr_Field("@@tacc", "toAccount");
			$Err_AACC=DecatErr_Field("@@aacc", "allAccount");

			$Err_FSUB=DecatErr_Field("@@fsub", "frSubaccount");
			$Err_TSUB=DecatErr_Field("@@tsub", "toSubaccount");
			$Err_ASUB=DecatErr_Field("@@asub", "allSubaccount");

			$Err_FVND=DecatErr_Field("@@fvnd", "frVendor");
			$Err_TVND=DecatErr_Field("@@tvnd", "toVendor");
			$Err_AVND=DecatErr_Field("@@avnd", "allVendor");

			$Err_CO01=DecatErr_Field("@@co01", "spCo01");
			$Err_FC01=DecatErr_Field("@@fc01", "spFc01");

			$Err_CO02=DecatErr_Field("@@co02", "spCo02");
			$Err_FC02=DecatErr_Field("@@fc02", "spFc02");

			$Err_CO03=DecatErr_Field("@@co03", "spCo03");
			$Err_FC03=DecatErr_Field("@@fc03", "spFc03");

			$Err_CO04=DecatErr_Field("@@co04", "spCo04");
			$Err_FC04=DecatErr_Field("@@fc04", "spFc04");

			$Err_CO05=DecatErr_Field("@@co05", "spCo05");
			$Err_FC05=DecatErr_Field("@@fc05", "spFc05");

			$Err_CO06=DecatErr_Field("@@co06", "spCo06");
			$Err_FC06=DecatErr_Field("@@fc06", "spFc06");

			$Err_CO07=DecatErr_Field("@@co07", "spCo07");
			$Err_FC07=DecatErr_Field("@@fc07", "spFc07");

			$Err_CO08=DecatErr_Field("@@co08", "spCo08");
			$Err_FC08=DecatErr_Field("@@fc08", "spFc08");

			$Err_CO09=DecatErr_Field("@@co09", "spCo09");
			$Err_FC09=DecatErr_Field("@@fc09", "spFc09");

			$Err_CO10=DecatErr_Field("@@co10", "spCo10");
			$Err_FC10=DecatErr_Field("@@fc10", "spFc10");

			$Err_CO11=DecatErr_Field("@@co11", "spCo11");
			$Err_FC11=DecatErr_Field("@@fc11", "spFc11");

			$Err_CO12=DecatErr_Field("@@co12", "spCo12");
			$Err_FC12=DecatErr_Field("@@fc12", "spFc12");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);

		$LDSC=Decat_Field("@@ldsc", $edtVar);

		if ($HDMCRL>0 && $CPPRMC=="Y") {
			$IDCR=Decat_Field("@@idcr", $edtVar);
			$CURT=Decat_Field("@@curt", $edtVar);
		}

		$FPER=Decat_Field("@@fper", $edtVar);
		$TPER=Decat_Field("@@tper", $edtVar);
		$APER=Decat_Field("@@aper", $edtVar);;

		$FCO=Decat_Field("@@fco@", $edtVar);
		$TCO=Decat_Field("@@tco@", $edtVar);
		$ACO=Decat_Field("@@aco@", $edtVar);

		$FFAC=Decat_Field("@@ffac", $edtVar);
		$TFAC=Decat_Field("@@tfac", $edtVar);
		$AFAC=Decat_Field("@@afac", $edtVar);

		$FACC=Decat_Field("@@facc", $edtVar);
		$TACC=Decat_Field("@@tacc", $edtVar);
		$AACC=Decat_Field("@@aacc", $edtVar);

		$FSUB=Decat_Field("@@fsub", $edtVar);
		$TSUB=Decat_Field("@@tsub", $edtVar);
		$ASUB=Decat_Field("@@asub", $edtVar);

		$FVND=Decat_Field("@@fvnd", $edtVar);
		$TVND=Decat_Field("@@tvnd", $edtVar);
		$AVND=Decat_Field("@@avnd", $edtVar);

		$CO01=Decat_Field("@@co01", $edtVar);
		$FC01=Decat_Field("@@fc01", $edtVar);

		$CO02=Decat_Field("@@co02", $edtVar);
		$FC02=Decat_Field("@@fc02", $edtVar);

		$CO03=Decat_Field("@@co03", $edtVar);
		$FC03=Decat_Field("@@fc03", $edtVar);

		$CO04=Decat_Field("@@co04", $edtVar);
		$FC04=Decat_Field("@@fc04", $edtVar);

		$CO05=Decat_Field("@@co05", $edtVar);
		$FC05=Decat_Field("@@fc05", $edtVar);

		$CO06=Decat_Field("@@co06", $edtVar);
		$FC06=Decat_Field("@@fc06", $edtVar);

		$CO07=Decat_Field("@@co07", $edtVar);
		$FC07=Decat_Field("@@fc07", $edtVar);

		$CO08=Decat_Field("@@co08", $edtVar);
		$FC08=Decat_Field("@@fc08", $edtVar);

		$CO09=Decat_Field("@@co09", $edtVar);
		$FC09=Decat_Field("@@fc09", $edtVar);

		$CO10=Decat_Field("@@co10", $edtVar);
		$FC10=Decat_Field("@@fc10", $edtVar);

		$CO11=Decat_Field("@@co11", $edtVar);
		$FC11=Decat_Field("@@fc11", $edtVar);

		$CO12=Decat_Field("@@co12", $edtVar);
		$FC12=Decat_Field("@@fc12", $edtVar);

		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$LDSC="N";

		$IDCR="D";
		$CURT="";

		$APER = "ALL";
		$ACO = "ALL";
		$AFAC = "ALL";
		$AACC = "ALL";
		$ASUB = "ALL";
		$AVND = "ALL";
	}

	if ($APER == "ALL") {$checked_APER="CHECKED";} else {$checked_APER="";}
	if ($ACO == "ALL") {$checked_ACO="CHECKED";} else {$checked_AACO="";}
	if ($AFAC == "ALL") {$checked_AFAC="CHECKED";} else {$checked_AFAC="";}
	if ($AACC == "ALL") {$checked_AACC="CHECKED";} else {$checked_AACC="";}
	if ($ASUB == "ALL") {$checked_ASUB="CHECKED";} else {$checked_ASUB="";}
	if ($AVND == "ALL") {$checked_AVND="CHECKED";} else {$checked_AVND="";}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#PrintOption\">Print Option</a></td> ";
	if ($HDMCRL>0 && $CPPRMC=="Y") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#Currency\">Currency</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#FromToAll\">From/To/All</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#Specific\">Specific Company/Facility Entry</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	print $hrTagAttr;

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

	print "\n <a name=\"PrintOption\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Print Option</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Print Long Descriptions","longDescription","inputalph","YORN","LDSC",$LDSC,$Err_LDSC,"1","1","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	if ($HDMCRL>0 && $CPPRMC=="Y") {
		print "\n <a name=\"Currency\"></a> ";
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n     <legend class=\"legendTitle\">Currency</legend> ";
		require 'TopOfForm.php';
		print "\n     <table $contentTable> ";
		Build_Fld_Entry("Invoice/Domestic","currencyID","inputalph","INVDOM","IDCR",$IDCR,$Err_IDCR,"1","1","","","");

		print "\n         <tr><td class=\"dsphdr\">--OR--</td></tr>";

		$fieldDesc=RetValue("CYTYPE='$CURT'", "HDCTYP", "CYDESC");
		$textOvr=SetTextOvr($Err_CURT);
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Convert To Currency Type</span></td> ";
		print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"currencyType\" value=\"" . rtrim($CURT) . "\" size=\"3\" maxlength=\"3\"> ";
		print "\n                                     <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=currencyType&amp;fldDesc=currencyTypeDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
		print "\n                                     <span class=\"dspdesc\" id=\"currencyTypeDesc\">$fieldDesc</span></td>";
		print "\n         </tr> ";
		DspErrMsg($Err_CURT);
		print "\n     </table> ";
		print "\n </fieldset> ";
	}

	print "\n <a name=\"FromToAll\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">From/To/All</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";

	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">From</td> ";
	print "\n             <td class=\"colhdr\">To</td> ";
	print "\n             <td class=\"colhdr\">All</td> ";
	print "\n         </tr> ";

	$textOvr=SetTextOvr($Err_FPER);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TPER); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_APER); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Distribution Period</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frDistPeriod\" value=\"$FPER\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toDistPeriod\" value=\"$TPER\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allDistPeriod\" value='ALL' $checked_APER onClick=\"if (this.checked) this.form.frDistPeriod.value='', this.form.toDistPeriod.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FPER);
	DspErrMsg($Err_TPER);
	DspErrMsg($Err_APER);

	$textOvr=SetTextOvr($Err_FCO);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TCO); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_ACO); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Company</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frCompany\" value=\"$FCO\" size=\"2\" maxlength=\"2\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frCompany&amp;fldFac=frFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toCompany\" value=\"$TCO\" size=\"2\" maxlength=\"2\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFac=toFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allCompany\" value=\"ALL\" $checked_ACO onClick=\"if (this.checked) this.form.frCompany.value='', this.form.toCompany.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FCO);
	DspErrMsg($Err_TCO);
	DspErrMsg($Err_ACO);

	$textOvr=SetTextOvr($Err_FFAC);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TFAC); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_AFAC); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Facility</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frFacility\" value=\"$FFAC\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toFacility\" value=\"$TFAC\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allFacility\" value=\"ALL\" $checked_AFAC onClick=\"if (this.checked) this.form.frFacility.value='', this.form.toFacility.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FFAC);
	DspErrMsg($Err_TFAC);
	DspErrMsg($Err_AFAC);

	$textOvr=SetTextOvr($Err_FACC);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TACC); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_AACC); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Account Number</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frAccount\" value=\"$FACC\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=frAccount&amp;subFld=frSubaccount&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toAccount\" value=\"$TACC\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=toAccount&amp;subFld=toSubaccount&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allAccount\" value=\"ALL\" $checked_AACC onClick=\"if (this.checked) this.form.frAccount.value='', this.form.toAccount.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FACC);
	DspErrMsg($Err_TACC);
	DspErrMsg($Err_AACC);

	$textOvr=SetTextOvr($Err_FSUB);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TSUB); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_ASUB); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Subaccount Number</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frSubaccount\" value=\"$FSUB\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toSubaccount\" value=\"$TSUB\" size=\"4\" maxlength=\"4\"></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allSubaccount\" value=\"ALL\" $checked_ASUB onClick=\"if (this.checked) this.form.frSubaccount.value='', this.form.toSubaccount.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FSUB);
	DspErrMsg($Err_TSUB);
	DspErrMsg($Err_ASUB);

	$textOvr=SetTextOvr($Err_FVND);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TVND); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_AVND); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Vendor</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frVendor\" value=\"$FVND\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frVendor&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toVendor\" value=\"$TVND\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toVendor&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allVendor\" value=\"ALL\" $checked_AVND onClick=\"if (this.checked) this.form.frVendor.value='', this.form.toVendor.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FVND);
	DspErrMsg($Err_TVND);
	DspErrMsg($Err_AVND);

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"Specific\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Specific Company/Facility Entry</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";

	print "\n         <tr><td class=\"colhdr\">Co/Fac</td> ";
	print "\n             <td class=\"colhdr\">Co/Fac</td> ";
	print "\n             <td class=\"colhdr\">Co/Fac</td> ";
	print "\n         </tr> ";

	$textOvr=SetTextOvr($Err_CO01);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_CO02); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_CO03); }
	print "\n         <tr><td class=\"inputnmbr\"><input type=\"text\" name=\"spCo01\" value=\"$CO01\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc01\" value=\"$FC01\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo01&amp;fldFac=spFc01&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spCo02\" value=\"$CO02\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc02\" value=\"$FC02\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo02&amp;fldFac=spFc02&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spCo03\" value=\"$CO03\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc03\" value=\"$FC03\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo03&amp;fldFac=spFc03&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CO01);
	DspErrMsg($Err_CO02);
	DspErrMsg($Err_CO03);

	$textOvr=SetTextOvr($Err_CO04);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_CO05); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_CO06); }
	print "\n         <tr><td class=\"inputnmbr\"><input type=\"text\" name=\"spCo04\" value=\"$CO04\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc04\" value=\"$FC04\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo04&amp;fldFac=spFc04&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spCo05\" value=\"$CO05\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc05\" value=\"$FC05\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo05&amp;fldFac=spFc05&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spCo06\" value=\"$CO06\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc06\" value=\"$FC06\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo06&amp;fldFac=spFc06&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CO04);
	DspErrMsg($Err_CO05);
	DspErrMsg($Err_CO06);

	$textOvr=SetTextOvr($Err_CO07);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_CO08); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_CO09); }
	print "\n         <tr><td class=\"inputnmbr\"><input type=\"text\" name=\"spCo07\" value=\"$CO07\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc07\" value=\"$FC07\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo07&amp;fldFac=spFc07&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spCo08\" value=\"$CO08\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc08\" value=\"$FC08\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo08&amp;fldFac=spFc08&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spCo09\" value=\"$CO09\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc09\" value=\"$FC09\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo09&amp;fldFac=spFc09&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CO07);
	DspErrMsg($Err_CO08);
	DspErrMsg($Err_CO09);

	$textOvr=SetTextOvr($Err_CO10);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_CO11); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_CO12); }
	print "\n         <tr><td class=\"inputnmbr\"><input type=\"text\" name=\"spCo10\" value=\"$CO10\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc10\" value=\"$FC10\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo10&amp;fldFac=spFc10&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spCo11\" value=\"$CO11\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc11\" value=\"$FC11\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo11&amp;fldFac=spFc11&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spCo12\" value=\"$CO12\" size=\"2\" maxlength=\"2\">";
	print "\n                                   / <input type=\"text\" name=\"spFc12\" value=\"$FC12\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=spCo12&amp;fldFac=spFc12&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CO10);
	DspErrMsg($Err_CO11);
	DspErrMsg($Err_CO12);

	print "\n     </table> ";
	print "\n </fieldset> ";

	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	require 'SubmitScheduleBottom.php';
	print "\n $hrTagAttr ";

	if ($focusField !="") {
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n document.Chg.$focusField.focus(); ";
		print "\n </script> ";
	}
	print "\n </form>";
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	$edtVar= "";
	if (!isset($_POST['longDescription'])) {$_POST['longDescription']="N";}  Concat_Field("@@ldsc", $_POST['longDescription']);

	Concat_Field("@@idcr", strtoupper($_POST['currencyID']));
	Concat_Field("@@curt", strtoupper($_POST['currencyType']));

	Concat_Field("@@fper", $_POST['frDistPeriod']);
	Concat_Field("@@tper", $_POST['toDistPeriod']);
	Concat_Field("@@aper", $_POST['allDistPeriod']);

	Concat_Field("@@fco@", $_POST['frCompany']);
	Concat_Field("@@tco@", $_POST['toCompany']);
	Concat_Field("@@aco@", $_POST['allCompany']);

	Concat_Field("@@ffac", $_POST['frFacility']);
	Concat_Field("@@tfac", $_POST['toFacility']);
	Concat_Field("@@afac", $_POST['allFacility']);

	Concat_Field("@@facc", $_POST['frAccount']);
	Concat_Field("@@tacc", $_POST['toAccount']);
	Concat_Field("@@aacc", $_POST['allAccount']);

	Concat_Field("@@fsub", $_POST['frSubaccount']);
	Concat_Field("@@tsub", $_POST['toSubaccount']);
	Concat_Field("@@asub", $_POST['allSubaccount']);

	Concat_Field("@@fvnd", $_POST['frVendor']);
	Concat_Field("@@tvnd", $_POST['toVendor']);
	Concat_Field("@@avnd", $_POST['allVendor']);

	Concat_Field("@@co01", $_POST['spCo01']);
	Concat_Field("@@fc01", $_POST['spFc01']);

	Concat_Field("@@co02", $_POST['spCo02']);
	Concat_Field("@@fc02", $_POST['spFc02']);

	Concat_Field("@@co03", $_POST['spCo03']);
	Concat_Field("@@fc03", $_POST['spFc03']);

	Concat_Field("@@co04", $_POST['spCo04']);
	Concat_Field("@@fc04", $_POST['spFc04']);

	Concat_Field("@@co05", $_POST['spCo05']);
	Concat_Field("@@fc05", $_POST['spFc05']);

	Concat_Field("@@co06", $_POST['spCo06']);
	Concat_Field("@@fc06", $_POST['spFc06']);

	Concat_Field("@@co07", $_POST['spCo07']);
	Concat_Field("@@fc07", $_POST['spFc07']);

	Concat_Field("@@co08", $_POST['spCo08']);
	Concat_Field("@@fc08", $_POST['spFc08']);

	Concat_Field("@@co09", $_POST['spCo09']);
	Concat_Field("@@fc09", $_POST['spFc09']);

	Concat_Field("@@co10", $_POST['spCo10']);
	Concat_Field("@@fc10", $_POST['spFc10']);

	Concat_Field("@@co11", $_POST['spCo11']);
	Concat_Field("@@fc11", $_POST['spFc11']);

	Concat_Field("@@co12", $_POST['spCo12']);
	Concat_Field("@@fc12", $_POST['spFc12']);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HAPDRS_WC", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}
?>	
