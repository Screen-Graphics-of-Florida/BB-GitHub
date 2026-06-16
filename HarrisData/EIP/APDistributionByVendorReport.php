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

require_once "APControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title            = "A/P Distribution By Vendor Report";
$scriptName            = "APDistributionByVendorReport.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CAPDVV";
$submitEnvProgram      = "HAPDVV";
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
	print "\n    && editNum(document.Chg.spVendor01, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor02, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor03, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor04, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor05, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor06, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor07, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor08, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor09, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor10, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor11, 7, 0) ";
	print "\n    && editNum(document.Chg.spVendor12, 7, 0) ";
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
	$pageID = "APDISTVENDORREPORT";
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

			$Err_VN01=DecatErr_Field("@@vn01", "spVendor01");
			$Err_VN02=DecatErr_Field("@@vn02", "spvendor02");
			$Err_VN03=DecatErr_Field("@@vn03", "spVendor03");
			$Err_VN04=DecatErr_Field("@@vn04", "spVendor04");
			$Err_VN05=DecatErr_Field("@@vn05", "spVendor05");
			$Err_VN06=DecatErr_Field("@@vn06", "spVendor06");
			$Err_VN07=DecatErr_Field("@@vn07", "spVendor07");
			$Err_VN08=DecatErr_Field("@@vn08", "spVendor08");
			$Err_VN09=DecatErr_Field("@@vn09", "spVendor09");
			$Err_VN10=DecatErr_Field("@@vn10", "spVendor10");
			$Err_VN11=DecatErr_Field("@@vn11", "spVendor11");
			$Err_VN12=DecatErr_Field("@@vn12", "spVendor12");

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

		$VN01=Decat_Field("@@vn01", $edtVar);
		$VN02=Decat_Field("@@vn02", $edtVar);
		$VN03=Decat_Field("@@vn03", $edtVar);
		$VN04=Decat_Field("@@vn04", $edtVar);
		$VN05=Decat_Field("@@vn05", $edtVar);
		$VN06=Decat_Field("@@vn06", $edtVar);
		$VN07=Decat_Field("@@vn07", $edtVar);
		$VN08=Decat_Field("@@vn08", $edtVar);
		$VN09=Decat_Field("@@vn09", $edtVar);
		$VN10=Decat_Field("@@vn10", $edtVar);
		$VN11=Decat_Field("@@vn11", $edtVar);
		$VN12=Decat_Field("@@vn12", $edtVar);
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
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#Specific\">Specific Vendor Entry</a></td> ";
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
	print "\n     <legend class=\"legendTitle\">Specific Vendor Entry</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";

	print "\n         <tr><td class=\"colhdr\">Vendor</td> ";
	print "\n             <td class=\"colhdr\">Vendor</td> ";
	print "\n             <td class=\"colhdr\">Vendor</td> ";
	print "\n             <td class=\"colhdr\">Vendor</td> ";
	print "\n         </tr> ";

	$textOvr=SetTextOvr($Err_VN01);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_VN02); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_VN03); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_VN04); }
	print "\n         <tr><td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor01\" value=\"$VN01\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor01&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor02\" value=\"$VN02\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor02&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor03\" value=\"$VN03\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor03&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor04\" value=\"$VN04\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor04&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_VN01);
	DspErrMsg($Err_VN02);
	DspErrMsg($Err_VN03);
	DspErrMsg($Err_VN04);

	$textOvr=SetTextOvr($Err_VN05);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_VN06); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_VN07); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_VN08); }
	print "\n         <tr><td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor05\" value=\"$VN05\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor05&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor06\" value=\"$VN06\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor06&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor07\" value=\"$VN07\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor07&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor08\" value=\"$VN08\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor08&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_VN05);
	DspErrMsg($Err_VN06);
	DspErrMsg($Err_VN07);
	DspErrMsg($Err_VN08);

	$textOvr=SetTextOvr($Err_VN09);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_VN10); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_VN11); }
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_VN12); }
	print "\n         <tr><td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor09\" value=\"$VN09\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor09&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor10\" value=\"$VN10\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor10&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor11\" value=\"$VN11\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor11&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"spVendor12\" value=\"$VN12\" size=\"7\" maxlength=\"7\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}VendorSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=spVendor12&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_VN09);
	DspErrMsg($Err_VN10);
	DspErrMsg($Err_VN11);
	DspErrMsg($Err_VN12);

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

	Concat_Field("@@vn01", $_POST['spVendor01']);
	Concat_Field("@@vn02", $_POST['spVendor02']);
	Concat_Field("@@vn03", $_POST['spVendor03']);
	Concat_Field("@@vn04", $_POST['spVendor04']);
	Concat_Field("@@vn05", $_POST['spVendor05']);
	Concat_Field("@@vn06", $_POST['spVendor06']);
	Concat_Field("@@vn07", $_POST['spVendor07']);
	Concat_Field("@@vn08", $_POST['spVendor08']);
	Concat_Field("@@vn09", $_POST['spVendor09']);
	Concat_Field("@@vn10", $_POST['spVendor10']);
	Concat_Field("@@vn11", $_POST['spVendor11']);
	Concat_Field("@@vn12", $_POST['spVendor12']);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HAPDRS_WV", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}
?>	
