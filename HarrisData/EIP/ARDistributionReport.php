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

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'WildCardAcctInclude.php';

$page_title            = "A/R Distribution Report";
$scriptName            = "ARDistributionReport.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CARDRP";
$submitEnvProgram      = "HARDRP";
$submitEnvPrinter      = "HARDRPPF";
$submitScheduleScript  = "";
$applicationID         = "AR";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

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
	print "\n   if (document.Chg.onAccount.checked == false && ";
	print "\n       document.Chg.serviceCharge.checked == false && ";
	print "\n       document.Chg.PurgeInvoices.checked == false) ";
	print "\n       {alert(\"Must make at least one Invoice selection\"); return false;} ";

	print "\n   if (editNum(document.Chg.frLocation, 3, 0) ";
	print "\n    && editNum(document.Chg.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frLocation,   document.Chg.toLocation,   document.Chg.operLocation, 3) ";
	print "\n    && editNum(document.Chg.frCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.toCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.frFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.toFacility, 4, 0) ";
	print "\n    && editFromToOper2(document.Chg.frCompany,   document.Chg.frFacility,   document.Chg.toCompany, document.Chg.toFacility, document.Chg.operCompany, 2, 4) ";
	print "\n    && editNum(document.Chg.frAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.toAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.frSubaccount, 4, 0) ";
	print "\n    && editNum(document.Chg.toSubaccount, 4, 0) ";
	print "\n    && editFromToOper2(document.Chg.frAccount,   document.Chg.frSubaccount, document.Chg.toAccount, document.Chg.toSubaccount, document.Chg.operAccount, 4, 4) ";
	print "\n    && editNum(document.Chg.frInvoice, 7, 0) ";
	print "\n    && editNum(document.Chg.toInvoice, 7, 0) ";
	print "\n    && editFromToOper(document.Chg.frInvoice,    document.Chg.toInvoice,    document.Chg.operInvoice, 7) ";
	print "\n    && editdate(document.Chg.frInvDate) ";
	print "\n    && editdate(document.Chg.toInvDate) ";
	print "\n    && editFromToOper(document.Chg.frInvDate,    document.Chg.toInvDate,    document.Chg.operInvDate, 'D') ";
	print "\n    && editNum(document.Chg.frDistPeriod, 4, 0) ";
	print "\n    && editNum(document.Chg.toDistPeriod, 4, 0) ";
	print "\n    && editFromToOper(document.Chg.frDistPeriod, document.Chg.toDistPeriod, document.Chg.operDistPeriod, 'DP') ";
	print "\n    && editNum(document.Chg.frCustomer, 7, 0) ";
	print "\n    && editNum(document.Chg.toCustomer, 7, 0) ";
	print "\n    && editFromToOper(document.Chg.frCustomer,   document.Chg.toCustomer,   document.Chg.operCustomer, 7) ";
	print "\n    && editFromToOper(document.Chg.frCustName,   document.Chg.toCustName,   document.Chg.operCustName, 'A') ";
	print "\n    && editFromToOper(document.Chg.frAlphaSeq,   document.Chg.toAlphaSeq,   document.Chg.operAlphaSeq, 'A') ";
	print "\n    && editFromToOper(document.Chg.frTranType,   document.Chg.toTranType,   document.Chg.operTranType, 'A') ";
	print "\n    && editNum(document.Chg.frSalesman, 3, 0) ";
	print "\n    && editNum(document.Chg.toSalesman, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frSalesman,   document.Chg.toSalesman,   document.Chg.operSalesman, 3) ";
	print "\n    && editFromToOper(document.Chg.frFedGL,      document.Chg.toFedGL,      document.Chg.operFedGL, 'A') ";
	if ($HDMCRL>0 && $CRPRMC=="Y") {print "\n    && editFromToOper(document.Chg.frCurType, document.Chg.toCurType, document.Chg.operCurType, 'A') ";}
	print "\n    ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARDISTRIBUTIONREPORT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="PurgeInvoices";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			$Err_ONA=DecatErr_Field("@@ona@", "onAccount");
			$Err_SVG=DecatErr_Field("@@svg@", "serviceCharge");
			$Err_IPIV=DecatErr_Field("@@ipiv", "PurgeInvoices");

			if ($HDMCRL>0 && $CRPRMC=="Y") {
				$Err_IDCR=DecatErr_Field("@@idcr", "currencyID");
				$Err_CURT=DecatErr_Field("@@curt", "currencyType");
			}

			$Err_SLOC=DecatErr_Field("@@sloc", "sortLocation");
			$Err_SCOF=DecatErr_Field("@@scof", "sortCompany");
			$Err_SGLA=DecatErr_Field("@@sgla", "sortAccount");
			$Err_SINV=DecatErr_Field("@@sinv", "sortInvoice");
			$Err_SDTE=DecatErr_Field("@@sdte", "sortDate");
			$Err_SPER=DecatErr_Field("@@sper", "sortDistPeriod");
			$Err_SCUS=DecatErr_Field("@@scus", "sortCustomer");
			$Err_SCNM=DecatErr_Field("@@scnm", "sortCustName");
			$Err_SALP=DecatErr_Field("@@salp", "sortAlpha");
			$Err_STRN=DecatErr_Field("@@strn", "sortTranType");
			$Err_SSLM=DecatErr_Field("@@sslm", "sortSalesman");
			$Err_SCUR=DecatErr_Field("@@scur", "sortCurrency");

			$Err_TLOC=DecatErr_Field("@@tloc", "totalLocation");
			$Err_TCOF=DecatErr_Field("@@tcof", "totalCompany");
			$Err_TGLA=DecatErr_Field("@@tgla", "totalAccount");
			$Err_TINV=DecatErr_Field("@@tinv", "totalInvoice");
			$Err_TDTE=DecatErr_Field("@@tdte", "totalDate");
			$Err_TPER=DecatErr_Field("@@tper", "totalDistPeriod");
			$Err_TCUS=DecatErr_Field("@@tcus", "totalCustomer");
			$Err_TCNM=DecatErr_Field("@@tcnm", "totalCustName");
			$Err_TALP=DecatErr_Field("@@talp", "totalAlpha");
			$Err_TTRN=DecatErr_Field("@@ttrn", "totalTranType");
			$Err_TSLM=DecatErr_Field("@@tslm", "totalSalesman");
			$Err_TCUR=DecatErr_Field("@@tcur", "totalCurrency");

			$Err_PLOC=DecatErr_Field("@@ploc", "pageLocation");
			$Err_PCOF=DecatErr_Field("@@pcof", "pageCompany");
			$Err_PGLA=DecatErr_Field("@@pgla", "pageAccount");
			$Err_PINV=DecatErr_Field("@@pinv", "pageInvoice");
			$Err_PDTE=DecatErr_Field("@@pdte", "pageDate");
			$Err_PPER=DecatErr_Field("@@pper", "pageDistPeriod");
			$Err_PCUS=DecatErr_Field("@@pcus", "pageCustomer");
			$Err_PCNM=DecatErr_Field("@@pcnm", "pageCustName");
			$Err_PALP=DecatErr_Field("@@palp", "pageAlpha");
			$Err_PTRN=DecatErr_Field("@@ptrn", "totalTranType");
			$Err_PSLM=DecatErr_Field("@@pslm", "pageSalesman");
			$Err_PCUR=DecatErr_Field("@@pcur", "pageCurrency");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);

		$ONA=Decat_Field("@@ona@", $edtVar);
		$SVG=Decat_Field("@@svg@", $edtVar);
		$IPIV=Decat_Field("@@ipiv", $edtVar);

		if ($HDMCRL>0 && $CRPRMC=="Y") {
			$IDCR=Decat_Field("@@idcr", $edtVar);
			$CURT=Decat_Field("@@curt", $edtVar);
		}

		$SLOC=Decat_Field("@@sloc", $edtVar);
		$SCOF=Decat_Field("@@scof", $edtVar);
		$SGLA=Decat_Field("@@sgla", $edtVar);
		$SINV=Decat_Field("@@sinv", $edtVar);
		$SDTE=Decat_Field("@@sdte", $edtVar);
		$SPER=Decat_Field("@@sper", $edtVar);
		$SCUS=Decat_Field("@@scus", $edtVar);
		$SCNM=Decat_Field("@@scnm", $edtVar);
		$SALP=Decat_Field("@@salp", $edtVar);
		$STRN=Decat_Field("@@strn", $edtVar);
		$SSLM=Decat_Field("@@sslm", $edtVar);
		$SCUR=Decat_Field("@@scur", $edtVar);

		$TLOC=Decat_Field("@@tloc", $edtVar);
		$TCOF=Decat_Field("@@tcof", $edtVar);
		$TGLA=Decat_Field("@@tgla", $edtVar);
		$TINV=Decat_Field("@@tinv", $edtVar);
		$TDTE=Decat_Field("@@tdte", $edtVar);
		$TPER=Decat_Field("@@tper", $edtVar);
		$TCUS=Decat_Field("@@tcus", $edtVar);
		$TCNM=Decat_Field("@@tcnm", $edtVar);
		$TALP=Decat_Field("@@talp", $edtVar);
		$TTRN=Decat_Field("@@ttrn", $edtVar);
		$TSLM=Decat_Field("@@tslm", $edtVar);
		$TCUR=Decat_Field("@@tcur", $edtVar);

		$PLOC=Decat_Field("@@ploc", $edtVar);
		$PCOF=Decat_Field("@@pcof", $edtVar);
		$PGLA=Decat_Field("@@pgla", $edtVar);
		$PINV=Decat_Field("@@pinv", $edtVar);
		$PDTE=Decat_Field("@@pdte", $edtVar);
		$PPER=Decat_Field("@@pper", $edtVar);
		$PCUS=Decat_Field("@@pcus", $edtVar);
		$PCNM=Decat_Field("@@pcnm", $edtVar);
		$PALP=Decat_Field("@@palp", $edtVar);
		$PTRN=Decat_Field("@@ptrn", $edtVar);
		$PSLM=Decat_Field("@@pslm", $edtVar);
		$PCUR=Decat_Field("@@pcur", $edtVar);
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$ONA="Y";
		$SVG="Y";
		$IPIV="Y";

		$IDCR="D";
		$CURT="";

		$TLOC="N";
		$TCOF="N";
		$TGLA="N";
		$TINV="N";
		$TDTE="N";
		$TPER="N";
		$TCUS="N";
		$TCNM="N";
		$TALP="N";
		$TTRN="N";
		$TSLM="N";
		$TCUR="N";

		$PLOC="N";
		$PCOF="N";
		$PGLA="N";
		$PINV="N";
		$PDTE="N";
		$PPER="N";
		$PCUS="N";
		$PCNM="N";
		$PALP="N";
		$PTRN="N";
		$PSLM="N";
		$PCUR="N";
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#IncludeInvoice\">Include Invoices</a></td> ";
	if ($HDMCRL>0 && $CRPRMC=="Y") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#Currency\">Currency</a></td> ";}
	print "\n   </tr> ";
	print "\n   <tr> ";
	if ($wildCardDisplay != "") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#CurrentFilterCriteria\">Current Filter Criteria</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#RefineFilterCriteria\">Refine Filter Criteria</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#SortPrintCriteria\">Sort/Print Criteria</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	print $hrTagAttr;

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

	print "\n <a name=\"IncludeInvoice\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Include Invoices</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("On Account","onAccount","inputalph","YORN","ONA",$ONA,$Err_ONA,"1","1","","","");
	Build_Fld_Entry("Service Charge","serviceCharge","inputalph","YORN","SVG",$SVG,$Err_SVG,"1","1","","","");
	Build_Fld_Entry("Purged","PurgeInvoices","inputalph","YORN","IPIV",$IPIV,$Err_IPIV,"1","1","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	if ($HDMCRL>0 && $CRPRMC=="Y") {
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

	require_once 'AdvSearchTopReport.php';

	$operNbr = "operLocation";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	$operNbr = "operCompany";
	print "\n <tr><td class=\"dsphdr\">Company/Facility</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frCompany\" size=\"2\" maxlength=\"2\">";
	print "\n                           / <input type=\"text\" name=\"frFacility\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frCompany&amp;fldFac=frFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toCompany\" size=\"2\" maxlength=\"2\">";
	print "\n                           / <input type=\"text\" name=\"toFacility\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFac=toFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	$operNbr = "operAccount";
	print "\n <tr><td class=\"dsphdr\">G/L Account</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frAccount\" size=\"4\" maxlength=\"4\">";
	print "\n                           - <input type=\"text\" name=\"frSubaccount\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=frAccount&amp;subFld=frSubaccount&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toAccount\" size=\"4\" maxlength=\"4\">";
	print "\n                           - <input type=\"text\" name=\"toSubaccount\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=toAccount&amp;subFld=toSubaccount&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Invoice","frInvoice","toInvoice","operInvoice","opersel_num2_short","N","7","7");
	Build_AdvSrch_Entry("Invoice Date","frInvDate","toInvDate","operInvDate","opersel_num2_short","D","6","6");

	$operNbr = "operDistPeriod";
	print "\n <tr><td class=\"dsphdr\">Distribution Period</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frDistPeriod\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;docName=Chg&amp;periodFld=frDistPeriod\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toDistPeriod\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;docName=Chg&amp;periodFld=toDistPeriod\" onclick=\"$searchWinVar\">$searchImage</a></td> ";

	$operNbr = "operCustomer";
	print "\n <tr><td class=\"dsphdr\">Customer</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frCustomer\" size=\"7\" maxlength=\"7\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frCustomer&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toCustomer\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toCustomer&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Customer Name","frCustName","toCustName","operCustName","OperSel_Alph2_Short","A","20","30");
	Build_AdvSrch_Entry("Alpha Sequence","frAlphaSeq","toAlphaSeq","operAlphaSeq","OperSel_Alph2_Short","A","4","4");

	$operNbr = "operTranType";
	print "\n <tr><td class=\"dsphdr\">A/R Transaction Type</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frTranType\" size=\"1\" maxlength=\"1\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=ARDISTTYPE&amp;flagSrchHdr=" . urlencode("A/R Transaction Type") . "&amp;docName=Chg&amp;fldName=frTranType&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toTranType\" size=\"1\" maxlength=\"1\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=ARDISTTYPE&amp;flagSrchHdr=" . urlencode("A/R Transaction Type") . "&amp;docName=Chg&amp;fldName=toTranType&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operSalesman";
	print "\n <tr><td class=\"dsphdr\">Salesman</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frSalesman&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toSalesman&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operFedGL";
	print "\n <tr><td class=\"dsphdr\">G/L Transfer Code</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frFedGL\" size=\"1\" maxlength=\"1\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=GLTRFCODE&amp;flagSrchHdr=" . urlencode("G/L Transfer Code") . "&amp;docName=Chg&amp;fldName=frFedGL&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toFedGL\" size=\"1\" maxlength=\"1\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=GLTRFCODE&amp;flagSrchHdr=" . urlencode("G/L Transfer Code") . "&amp;docName=Chg&amp;fldName=toFedGL&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	if ($HDMCRL>0 && $CRPRMC=="Y") {
		$operNbr = "operCurType";
		print "\n <tr><td class=\"dsphdr\">Currency Type</td>";
		print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frCurType\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frCurType&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toCurType\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}CurrencyTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toCurType&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
		print "\n </tr>";
	}

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"SortPrintCriteria\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Sort/Print Criteria</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">Sort<br>Sequence</td> ";
	print "\n             <td class=\"colhdr\">Print<br>Total</td> ";
	print "\n             <td class=\"colhdr\">Page<br>Break</td> ";
	print "\n         </tr> ";

	if ($HDMCRL>0 && $CRPRMC=="Y") {$maxSequence=12;}
	else                           {$maxSequence=11;}

	$textOvr=SetTextOvr($Err_SLOC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Location</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortLocation",$SLOC);
	Build_Fld_Entry("","totalLocation","inputcode","YORN","TLOC",$TLOC,$Err_TLOC,"1","1","","","Y");
	Build_Fld_Entry("","pageLocation","inputcode","YORN","PLOC",$PLOC,$Err_PLOC,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SLOC);

	$textOvr=SetTextOvr($Err_SCOF);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Company/Facility</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortCompany",$SCOF);
	Build_Fld_Entry("","totalCompany","inputcode","YORN","TCOF",$TCOF,$Err_TCOF,"1","1","","","Y");
	Build_Fld_Entry("","pageCompany","inputcode","YORN","PCOF",$PCOF,$Err_PCOF,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SCOF);

	$textOvr=SetTextOvr($Err_SGLA);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>G/L Account</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortAccount",$SGLA);
	Build_Fld_Entry("","totalAccount","inputcode","YORN","TGLA",$TGLA,$Err_TGLA,"1","1","","","Y");
	Build_Fld_Entry("","pageAccount","inputcode","YORN","PGLA",$PGLA,$Err_PGLA,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SGLA);

	$textOvr=SetTextOvr($Err_SINV);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Invoice</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortInvoice",$SINV);
	Build_Fld_Entry("","totalInvoice","inputcode","YORN","TINV",$TINV,$Err_TINV,"1","1","","","Y");
	Build_Fld_Entry("","pageInvoice","inputcode","YORN","PINV",$PINV,$Err_PINV,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SINV);

	$textOvr=SetTextOvr($Err_SDTE);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Invoice Date</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortDate",$SDTE);
	Build_Fld_Entry("","totalDate","inputcode","YORN","TDTE",$TDTE,$Err_TDTE,"1","1","","","Y");
	Build_Fld_Entry("","pageDate","inputcode","YORN","PDTE",$PDTE,$Err_PDTE,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SDTE);

	$textOvr=SetTextOvr($Err_SPER);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Distribution Period</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortDistPeriod",$SPER);
	Build_Fld_Entry("","totalDistPeriod","inputcode","YORN","TPER",$TPER,$Err_TPER,"1","1","","","Y");
	Build_Fld_Entry("","pageDistPeriod","inputcode","YORN","PPER",$PPER,$Err_PPER,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SPER);

	$textOvr=SetTextOvr($Err_SCUS);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortCustomer",$SCUS);
	Build_Fld_Entry("","totalCustomer","inputcode","YORN","TCUS",$TCUS,$Err_TCUS,"1","1","","","Y");
	Build_Fld_Entry("","pageCustomer","inputcode","YORN","PCUS",$PCUS,$Err_PCUS,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SCUS);

	$textOvr=SetTextOvr($Err_SCNM);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Name</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortCustName",$SCNM);
	Build_Fld_Entry("","totalCustName","inputcode","YORN","TCNM",$TCNM,$Err_TCNM,"1","1","","","Y");
	Build_Fld_Entry("","pageCustName","inputcode","YORN","PCNM",$PCNM,$Err_PCNM,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SCNM);

	$textOvr=SetTextOvr($Err_SALP);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Alpha Sequence</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortAlpha",$SALP);
	Build_Fld_Entry("","totalAlpha","inputcode","YORN","TALP",$TALP,$Err_TALP,"1","1","","","Y");
	Build_Fld_Entry("","pageAlpha","inputcode","YORN","PALP",$PALP,$Err_PALP,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SALP);

	$textOvr=SetTextOvr($Err_STRN);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>A/R Transaction Type</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortTranType",$STRN);
	Build_Fld_Entry("","totalTranType","inputcode","YORN","TTRN",$TTRN,$Err_TTRN,"1","1","","","Y");
	Build_Fld_Entry("","pageTranType","inputcode","YORN","PTRN",$PTRN,$Err_PTRN,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_STRN);

	$textOvr=SetTextOvr($Err_SSLM);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Salesman</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortSalesman",$SSLM);
	Build_Fld_Entry("","totalSalesman","inputcode","YORN","TSLM",$TSLM,$Err_TSLM,"1","1","","","Y");
	Build_Fld_Entry("","pageSalesman","inputcode","YORN","PSLM",$PSLM,$Err_PSLM,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SSLM);

	if ($HDMCRL>0 && $CRPRMC=="Y") {
		$textOvr=SetTextOvr($Err_SCUR);
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Currency Type</span></td>";
		$sortSeqMax=$maxSequence; Build_Sort_Select("sortCurrency",$SCUR);
		Build_Fld_Entry("","totalCurrency","inputcode","YORN","TCUR",$TCUR,$Err_TCUR,"1","1","","","Y");
		Build_Fld_Entry("","pageCurrency","inputcode","YORN","PCUR",$PCUR,$Err_PCUR,"1","1","","","Y");
		print "\n         </tr> ";
		DspErrMsg($Err_SCUR);
	}

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
	if ($_SESSION['gotoFilter']=="Y") {
		$_SESSION['gotoFilter']="";
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n window.location.hash='CurrentFilterCriteria'; ";
		print "\n </script> ";
	}
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "WILDCARD" || $tag == "Edit_Data") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	if ($_POST['updateSearch'] == "Y") {$_SESSION['gotoFilter']="Y";}

	$returnValue=Range_WildCard("IDLOC", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Range_WildCard_CoFac("IDCO", "IDFAC", "Company/Facility", $_POST['frCompany'], $_POST['frFacility'], $_POST['toCompany'], $_POST['toFacility'], $_POST['operCompany']);
	$returnValue=Range_WildCard_Acct("IDACCT", "IDSUB", "G/L Account", $_POST['frAccount'], $_POST['frSubaccount'], $_POST['toAccount'], $_POST['toSubaccount'], $_POST['operAccount']);
	$returnValue=Range_WildCard("IDAINV", "Invoice", $_POST['frInvoice'], $_POST['toInvoice'], "", $_POST['operInvoice'], "N");
	$returnValue=Range_WildCard("IDIVDT", "Invoice Date", $_POST['frInvDate'], $_POST['toInvDate'], "", $_POST['operInvDate'], "D");
	$returnValue=Range_WildCard("IDPER", "Distribution Period", $_POST['frDistPeriod'], $_POST['toDistPeriod'], "", $_POST['operDistPeriod'], "DP");
	$returnValue=Range_WildCard("IDBLTO", "Customer", $_POST['frCustomer'], $_POST['toCustomer'], "", $_POST['operCustomer'], "N");
	$returnValue=Range_WildCard("CMCNA1U", "Customer Name", $_POST['frCustName'], $_POST['toCustName'], "U", $_POST['operCustName'], "A");
	$returnValue=Range_WildCard("CMALPH", "Alpha Sequence", $_POST['frAlphaSeq'], $_POST['toAlphaSeq'], "U", $_POST['operAlphaSeq'], "A");
	$returnValue=Range_WildCard("IDARTY", "A/R Transaction Type", $_POST['frTranType'], $_POST['toTranType'], "U", $_POST['operTranType'], "A");
	$returnValue=Range_WildCard("Coalesce(IVSLSM,0)", "Salesman", $_POST['frSalesman'], $_POST['toSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Range_WildCard("IDGLTC", "G/L Transfer Code", $_POST['frFedGL'], $_POST['toFedGL'], "U", $_POST['operFedGL'], "A");
	$returnValue=Range_WildCard("IDCURT", "Currency Type", $_POST['frCurType'], $_POST['toCurType'], "U", $_POST['operCurType'], "A");
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);

	if (!isset($_POST['onAccount'])) {$_POST['onAccount']="N";}  Concat_Field("@@ona@", $_POST['onAccount']);
	if (!isset($_POST['serviceCharge'])) {$_POST['serviceCharge']="N";}  Concat_Field("@@svg@", $_POST['serviceCharge']);
	if (!isset($_POST['PurgeInvoices'])) {$_POST['PurgeInvoices']="N";}  Concat_Field("@@ipiv", $_POST['PurgeInvoices']);

	Concat_Field("@@idcr", strtoupper($_POST['currencyID']));
	Concat_Field("@@curt", strtoupper($_POST['currencyType']));

	Concat_Field("@@sloc", $_POST['sortLocation']);
	if (!isset($_POST['totalLocation'])) {$_POST['totalLocation']="N";}  Concat_Field("@@tloc", $_POST['totalLocation']);
	if (!isset($_POST['pageLocation'])) {$_POST['pageLocation']="N";}  Concat_Field("@@ploc", $_POST['pageLocation']);

	Concat_Field("@@scof", $_POST['sortCompany']);
	if (!isset($_POST['totalCompany'])) {$_POST['totalCompany']="N";}  Concat_Field("@@tcof", $_POST['totalCompany']);
	if (!isset($_POST['pageCompany'])) {$_POST['pageCompany']="N";}  Concat_Field("@@pcof", $_POST['pageCompany']);

	Concat_Field("@@sgla", $_POST['sortAccount']);
	if (!isset($_POST['totalAccount'])) {$_POST['totalAccount']="N";}  Concat_Field("@@tgla", $_POST['totalAccount']);
	if (!isset($_POST['pageAccount'])) {$_POST['pageAccount']="N";}  Concat_Field("@@pgla", $_POST['pageAccount']);

	Concat_Field("@@sinv", $_POST['sortInvoice']);
	if (!isset($_POST['totalInvoice'])) {$_POST['totalInvoice']="N";}  Concat_Field("@@tinv", $_POST['totalInvoice']);
	if (!isset($_POST['pageInvoice'])) {$_POST['pageInvoice']="N";}  Concat_Field("@@pinv", $_POST['pageInvoice']);

	Concat_Field("@@sdte", $_POST['sortDate']);
	if (!isset($_POST['totalDate'])) {$_POST['totalDate']="N";}  Concat_Field("@@tdte", $_POST['totalDate']);
	if (!isset($_POST['pageDate'])) {$_POST['pageDate']="N";}  Concat_Field("@@pdte", $_POST['pageDate']);

	Concat_Field("@@sper", $_POST['sortDistPeriod']);
	if (!isset($_POST['totalDistPeriod'])) {$_POST['totalDistPeriod']="N";}  Concat_Field("@@tper", $_POST['totalDistPeriod']);
	if (!isset($_POST['pageDistPeriod'])) {$_POST['pageDistPeriod']="N";}  Concat_Field("@@pper", $_POST['pageDistPeriod']);

	Concat_Field("@@scus", $_POST['sortCustomer']);
	if (!isset($_POST['totalCustomer'])) {$_POST['totalCustomer']="N";}  Concat_Field("@@tcus", $_POST['totalCustomer']);
	if (!isset($_POST['pageCustomer'])) {$_POST['pageCustomer']="N";}  Concat_Field("@@pcus", $_POST['pageCustomer']);

	Concat_Field("@@scnm", $_POST['sortCustName']);
	if (!isset($_POST['totalCustName'])) {$_POST['totalCustName']="N";}  Concat_Field("@@tcnm", $_POST['totalCustName']);
	if (!isset($_POST['pageCustName'])) {$_POST['pageCustName']="N";}  Concat_Field("@@pcnm", $_POST['pageCustName']);

	Concat_Field("@@salp", $_POST['sortAlpha']);
	if (!isset($_POST['totalAlpha'])) {$_POST['totalAlpha']="N";}  Concat_Field("@@talp", $_POST['totalAlpha']);
	if (!isset($_POST['pageAlpha'])) {$_POST['pageAlpha']="N";}  Concat_Field("@@palp", $_POST['pageAlpha']);

	Concat_Field("@@strn", $_POST['sortTranType']);
	if (!isset($_POST['totalTranType'])) {$_POST['totalTranType']="N";}  Concat_Field("@@ttrn", $_POST['totalTranType']);
	if (!isset($_POST['pageTranType'])) {$_POST['pageTranType']="N";}  Concat_Field("@@ptrn", $_POST['pageTranType']);

	Concat_Field("@@sslm", $_POST['sortSalesman']);
	if (!isset($_POST['totalSalesman'])) {$_POST['totalSalesman']="N";}  Concat_Field("@@tslm", $_POST['totalSalesman']);
	if (!isset($_POST['pageSalesman'])) {$_POST['pageSalesman']="N";}  Concat_Field("@@pslm", $_POST['pageSalesman']);

	Concat_Field("@@scur", $_POST['sortCurrency']);
	if (!isset($_POST['totalCurrency'])) {$_POST['totalCurrency']="N";}  Concat_Field("@@tcur", $_POST['totalCurrency']);
	if (!isset($_POST['pageCurrency'])) {$_POST['pageCurrency']="N";}  Concat_Field("@@pcur", $_POST['pageCurrency']);

	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HARDRS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
