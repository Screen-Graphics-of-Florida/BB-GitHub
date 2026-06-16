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

$page_title            = "A/R Paid History Report";
$scriptName            = "ARPaidHistoryReport.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CARPHY";
$submitEnvProgram      = "HARPHY";
$submitEnvPrinter      = "HARPHYPF";
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
	print "\n       document.Chg.deduction.checked == false && ";
	print "\n       document.Chg.serviceCharge.checked == false && ";
	print "\n       document.Chg.nsf.checked == false && ";
	print "\n       document.Chg.unappliedCash.checked == false && ";
	print "\n       document.Chg.miscCash.checked == false) ";
	print "\n       {alert(\"Must make at least one Invoice Code selection\"); return false;} ";

	print "\n   if (editNum(document.Chg.frBank, 2, 0) ";
	print "\n    && editNum(document.Chg.toBank, 2, 0) ";
	print "\n    && editFromToOper(document.Chg.frBank,        document.Chg.toBank,      document.Chg.operBank, 2) ";
	print "\n    && editNum(document.Chg.frCustomer, 7, 0) ";
	print "\n    && editNum(document.Chg.toCustomer, 7, 0) ";
	print "\n    && editFromToOper(document.Chg.frCustomer,    document.Chg.toCustomer,  document.Chg.operCustomer, 7) ";
	print "\n    && editFromToOper(document.Chg.frCustName,    document.Chg.toCustName,  document.Chg.operCustName, 'A') ";
	print "\n    && editFromToOper(document.Chg.frAlphaSeq,    document.Chg.toAlphaSeq,  document.Chg.operAlphaSeq, 'A') ";
	print "\n    && editFromToOper(document.Chg.frClass,       document.Chg.toClass,     document.Chg.operClass, 'A') ";
	print "\n    && editFromToOper(document.Chg.frRegion,      document.Chg.toRegion,    document.Chg.operRegion, 'A') ";
	print "\n    && editdate(document.Chg.frPaidDate) ";
	print "\n    && editdate(document.Chg.toPaidDate) ";
	print "\n    && editFromToOper(document.Chg.frPaidDate,    document.Chg.toPaidDate,  document.Chg.operPaidDate, 'D') ";
	print "\n    && editNum(document.Chg.frInvoice, 7, 0) ";
	print "\n    && editNum(document.Chg.toInvoice, 7, 0) ";
	print "\n    && editFromToOper(document.Chg.frInvoice,     document.Chg.toInvoice,   document.Chg.operInvoice, 7) ";
	print "\n    && editNum(document.Chg.frLocation, 3, 0) ";
	print "\n    && editNum(document.Chg.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frLocation,    document.Chg.toLocation,  document.Chg.operLocation, 3) ";
	print "\n    && editFromToOper(document.Chg.frDocument,    document.Chg.toDocument,  document.Chg.operDocument, 'A') ";
	print "\n    && editNum(document.Chg.frPayer, 7, 0) ";
	print "\n    && editNum(document.Chg.toPayer, 7, 0) ";
	print "\n    && editFromToOper(document.Chg.frPayer,       document.Chg.toPayer,     document.Chg.operPayer, 7) ";
	print "\n    && editFromToOper(document.Chg.frPmtCode,     document.Chg.toPmtCode,   document.Chg.operPmtCode, 'A') ";
	print "\n    && editFromToOper(document.Chg.frSubCode,     document.Chg.toSubCode,   document.Chg.operSubCode, 'A') ";
	print "\n    && editFromToOper(document.Chg.frEntryUser,   document.Chg.toEntryUser, document.Chg.operEntryUser, 'A') ";
	print "\n    && editNum(document.Chg.frSalesman, 3, 0) ";
	print "\n    && editNum(document.Chg.toSalesman, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frSalesman,    document.Chg.toSalesman,  document.Chg.operSalesman, 3) ";
	print "\n    && editNum(document.Chg.frCashAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.toCashAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.frCashSub, 4, 0) ";
	print "\n    && editNum(document.Chg.toCashSub, 4, 0) ";
	print "\n    && editFromToOper2(document.Chg.frCashAcct,   document.Chg.frCashSub,   document.Chg.toCashAcct, document.Chg.toCashSub, document.Chg.operCashAcct, 4, 4) ";
	print "\n    && editNum(document.Chg.frOffsetAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.toOffsetAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.frOffsetSub, 4, 0) ";
	print "\n    && editNum(document.Chg.toOffsetSub, 4, 0) ";
	print "\n    && editFromToOper2(document.Chg.frOffsetAcct, document.Chg.frOffsetSub, document.Chg.toOffsetAcct, document.Chg.toOffsetSub, document.Chg.operOffsetAcct, 4, 4) ";
	if ($HDMCRL>0 && $CRPRMC=="Y") {print "\n    && editFromToOper(document.Chg.frCurType,   document.Chg.toCurType,   document.Chg.operCurType, 'A') ";}
	print "\n    ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARPAIDHISTORYREPORT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="PrintComment";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			$Err_CMNT=DecatErr_Field("@@cmnt", "PrintComment");

			$Err_ONA=DecatErr_Field("@@ona@", "onAccount");
			$Err_DED=DecatErr_Field("@@ded@", "deduction");
			$Err_SVG=DecatErr_Field("@@svg@", "serviceCharge");
			$Err_NSF=DecatErr_Field("@@nsf@", "nsf");
			$Err_UNA=DecatErr_Field("@@una@", "unappliedCash");
			$Err_MSC=DecatErr_Field("@@msc@", "miscCash");

			if ($HDMCRL>0 && $CRPRMC=="Y") {
				$Err_IDCR=DecatErr_Field("@@idcr", "currencyID");
				$Err_CURT=DecatErr_Field("@@curt", "currencyType");
			}

			$Err_SBNK=DecatErr_Field("@@sbnk", "sortBank");
			$Err_SCUS=DecatErr_Field("@@scus", "sortCustomer");
			$Err_SCNM=DecatErr_Field("@@scnm", "sortCustName");
			$Err_SALP=DecatErr_Field("@@salp", "sortAlpha");
			$Err_SCLS=DecatErr_Field("@@scls", "sortClass");
			$Err_SRGN=DecatErr_Field("@@srgn", "sortRegion");
			$Err_SPDT=DecatErr_Field("@@spdt", "sortPaidDate");
			$Err_SINV=DecatErr_Field("@@sinv", "sortInvoice");
			$Err_SLOC=DecatErr_Field("@@sloc", "sortLocation");
			$Err_SDOC=DecatErr_Field("@@sdoc", "sortDocument");
			$Err_SPYR=DecatErr_Field("@@spyr", "sortPayer");
			$Err_SPCD=DecatErr_Field("@@spcd", "sortPmtCode");
			$Err_SSUB=DecatErr_Field("@@ssub", "sortSubCode");
			$Err_SUSR=DecatErr_Field("@@susr", "sortEntryUser");
			$Err_SSLM=DecatErr_Field("@@sslm", "sortSalesman");
			$Err_SCSA=DecatErr_Field("@@scsa", "sortCashAcct");
			$Err_SOFA=DecatErr_Field("@@sofa", "sortOffsetAcct");
			$Err_SCUR=DecatErr_Field("@@scur", "sortCurrency");

			$Err_TBNK=DecatErr_Field("@@tbnk", "totalBank");
			$Err_TCUS=DecatErr_Field("@@tcus", "totalCustomer");
			$Err_TCNM=DecatErr_Field("@@tcnm", "totalCustName");
			$Err_TALP=DecatErr_Field("@@talp", "totalAlpha");
			$Err_TCLS=DecatErr_Field("@@tcls", "totalClass");
			$Err_TRGN=DecatErr_Field("@@trgn", "totalRegion");
			$Err_TPDT=DecatErr_Field("@@tpdt", "totalPaidDate");
			$Err_TINV=DecatErr_Field("@@tinv", "totalInvoice");
			$Err_TLOC=DecatErr_Field("@@tloc", "totalLocation");
			$Err_TDOC=DecatErr_Field("@@tdoc", "totalDocument");
			$Err_TPYR=DecatErr_Field("@@tpyr", "totalPayer");
			$Err_TPCD=DecatErr_Field("@@tpcd", "totalPmtCode");
			$Err_TSUB=DecatErr_Field("@@tsub", "totalSubCode");
			$Err_TUSR=DecatErr_Field("@@tusr", "totalEntryUser");
			$Err_TSLM=DecatErr_Field("@@tslm", "totalSalesman");
			$Err_TCSA=DecatErr_Field("@@tcsa", "totalCashAcct");
			$Err_TOFA=DecatErr_Field("@@tofa", "totalOffsetAcct");
			$Err_TCUR=DecatErr_Field("@@tcur", "totalCurrency");

			$Err_PBNK=DecatErr_Field("@@pbnk", "pageBank");
			$Err_PCUS=DecatErr_Field("@@pcus", "pageCustomer");
			$Err_PCNM=DecatErr_Field("@@pcnm", "pageCustName");
			$Err_PALP=DecatErr_Field("@@palp", "pageAlpha");
			$Err_PCLS=DecatErr_Field("@@pcls", "pageClass");
			$Err_PRGN=DecatErr_Field("@@prgn", "pageRegion");
			$Err_PPDT=DecatErr_Field("@@ppdt", "pagePaidDate");
			$Err_PINV=DecatErr_Field("@@pinv", "pageInvoice");
			$Err_PLOC=DecatErr_Field("@@ploc", "pageLocation");
			$Err_PDOC=DecatErr_Field("@@pdoc", "pageDocument");
			$Err_PPYR=DecatErr_Field("@@ppyr", "pagePayer");
			$Err_PPCD=DecatErr_Field("@@ppcd", "pagePmtCode");
			$Err_PSUB=DecatErr_Field("@@psub", "pageSubCode");
			$Err_PUSR=DecatErr_Field("@@pusr", "pageEntryUser");
			$Err_PSLM=DecatErr_Field("@@pslm", "pageSalesman");
			$Err_PCSA=DecatErr_Field("@@pcsa", "pageCashAcct");
			$Err_POFA=DecatErr_Field("@@pofa", "pageOffsetAcct");
			$Err_PCUR=DecatErr_Field("@@pcur", "pageCurrency");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$CMNT=Decat_Field("@@cmnt", $edtVar);

		$ONA=Decat_Field("@@ona@", $edtVar);
		$DED=Decat_Field("@@ded@", $edtVar);
		$SVG=Decat_Field("@@svg@", $edtVar);
		$NSF=Decat_Field("@@nsf@", $edtVar);
		$UNA=Decat_Field("@@una@", $edtVar);
		$MSC=Decat_Field("@@msc@", $edtVar);

		if ($HDMCRL>0 && $CRPRMC=="Y") {
			$IDCR=Decat_Field("@@idcr", $edtVar);
			$CURT=Decat_Field("@@curt", $edtVar);
		}

		$SBNK=Decat_Field("@@sbnk", $edtVar);
		$SCUS=Decat_Field("@@scus", $edtVar);
		$SCNM=Decat_Field("@@scnm", $edtVar);
		$SALP=Decat_Field("@@salp", $edtVar);
		$SCLS=Decat_Field("@@scls", $edtVar);
		$SRGN=Decat_Field("@@srgn", $edtVar);
		$SPDT=Decat_Field("@@spdt", $edtVar);
		$SINV=Decat_Field("@@sinv", $edtVar);
		$SLOC=Decat_Field("@@sloc", $edtVar);
		$SDOC=Decat_Field("@@sdoc", $edtVar);
		$SPYR=Decat_Field("@@spyr", $edtVar);
		$SPCD=Decat_Field("@@spcd", $edtVar);
		$SSUB=Decat_Field("@@ssub", $edtVar);
		$SUSR=Decat_Field("@@susr", $edtVar);
		$SSLM=Decat_Field("@@sslm", $edtVar);
		$SCSA=Decat_Field("@@scsa", $edtVar);
		$SOFA=Decat_Field("@@sofa", $edtVar);
		$SCUR=Decat_Field("@@scur", $edtVar);

		$TBNK=Decat_Field("@@tbnk", $edtVar);
		$TCUS=Decat_Field("@@tcus", $edtVar);
		$TCNM=Decat_Field("@@tcnm", $edtVar);
		$TALP=Decat_Field("@@talp", $edtVar);
		$TCLS=Decat_Field("@@tcls", $edtVar);
		$TRGN=Decat_Field("@@trgn", $edtVar);
		$TPDT=Decat_Field("@@tpdt", $edtVar);
		$TINV=Decat_Field("@@tinv", $edtVar);
		$TLOC=Decat_Field("@@tloc", $edtVar);
		$TDOC=Decat_Field("@@tdoc", $edtVar);
		$TPYR=Decat_Field("@@tpyr", $edtVar);
		$TPCD=Decat_Field("@@tpcd", $edtVar);
		$TSUB=Decat_Field("@@tsub", $edtVar);
		$TUSR=Decat_Field("@@tusr", $edtVar);
		$TSLM=Decat_Field("@@tslm", $edtVar);
		$TCSA=Decat_Field("@@tcsa", $edtVar);
		$TOFA=Decat_Field("@@tofa", $edtVar);
		$TCUR=Decat_Field("@@tcur", $edtVar);

		$PBNK=Decat_Field("@@pbnk", $edtVar);
		$PCUS=Decat_Field("@@pcus", $edtVar);
		$PCNM=Decat_Field("@@pcnm", $edtVar);
		$PALP=Decat_Field("@@palp", $edtVar);
		$PCLS=Decat_Field("@@pcls", $edtVar);
		$PRGN=Decat_Field("@@prgn", $edtVar);
		$PPDT=Decat_Field("@@ppdt", $edtVar);
		$PINV=Decat_Field("@@pinv", $edtVar);
		$PLOC=Decat_Field("@@ploc", $edtVar);
		$PDOC=Decat_Field("@@pdoc", $edtVar);
		$PPYR=Decat_Field("@@ppyr", $edtVar);
		$PPCD=Decat_Field("@@ppcd", $edtVar);
		$PSUB=Decat_Field("@@psub", $edtVar);
		$PUSR=Decat_Field("@@pusr", $edtVar);
		$PSLM=Decat_Field("@@pslm", $edtVar);
		$PCSA=Decat_Field("@@pcsa", $edtVar);
		$POFA=Decat_Field("@@pofa", $edtVar);
		$PCUR=Decat_Field("@@pcur", $edtVar);

		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$CMNT="Y";

		$IDCR="I";
		$CURT="";

		$ONA="Y";
		$DED="Y";
		$SVG="Y";
		$NSF="Y";
		$UNA="Y";
		$MSC="Y";

		$TBNK="N";
		$TCUS="N";
		$TCNM="N";
		$TALP="N";
		$TCLS="N";
		$TRGN="N";
		$TPDT="N";
		$TINV="N";
		$TLOC="N";
		$TDOC="N";
		$TPYR="N";
		$TPCD="N";
		$TSUB="N";
		$TUSR="N";
		$TSLM="N";
		$TCSA="N";
		$TOFA="N";
		$TCUR="N";

		$PBNK="N";
		$PCUS="N";
		$PCNM="N";
		$PALP="N";
		$PCLS="N";
		$PRGN="N";
		$PPDT="N";
		$PINV="N";
		$PLOC="N";
		$PDOC="N";
		$PPYR="N";
		$PPCD="N";
		$PSUB="N";
		$PUSR="N";
		$PSLM="N";
		$PCSA="N";
		$POFA="N";
		$PCUR="N";
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#PrintOption\">Print Option</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#InvoiceCode\">Invoice Code</a></td> ";
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

	print "\n <a name=\"PrintOption\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Print Option</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Print Comments","PrintComment","inputalph","YORN","CMNT",$CMNT,$Err_CMNT,"1","1","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"InvoiceCode\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Invoice Code</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("On Account","onAccount","inputalph","YORN","ONA",$ONA,$Err_ONA,"1","1","","","");
	Build_Fld_Entry("Deduction","deduction","inputalph","YORN","DED",$DED,$Err_DED,"1","1","","","");
	Build_Fld_Entry("Service Charge","serviceCharge","inputalph","YORN","SVG",$SVG,$Err_SVG,"1","1","","","");
	Build_Fld_Entry("NSF","nsf","inputalph","YORN","NSF",$NSF,$Err_NSF,"1","1","","","");
	Build_Fld_Entry("Unapplied Cash","unappliedCash","inputalph","YORN","UNA",$UNA,$Err_UNA,"1","1","","","");
	Build_Fld_Entry("Miscellaneous Cash","miscCash","inputalph","YORN","MSC",$MSC,$Err_MSC,"1","1","","","");
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

	$operNbr = "operBank";
	print "\n <tr><td class=\"dsphdr\">Bank</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frBank\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}BankSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frBank&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toBank\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}BankSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toBank&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

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

	$operNbr = "operClass";
	print "\n <tr><td class=\"dsphdr\">Customer Class</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frClass\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerClassSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frClass&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toClass\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerClassSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toClass&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operRegion";
	print "\n <tr><td class=\"dsphdr\">Customer Region</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frRegion\" size=\"5\" maxlength=\"5\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}RegionSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frRegion&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toRegion\" size=\"5\" maxlength=\"5\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}RegionSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toRegion&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Date Paid","frPaidDate","toPaidDate","operPaidDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Invoice","frInvoice","toInvoice","operInvoice","opersel_num2_short","N","7","7");

	$operNbr = "operLocation";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Document","frDocument","toDocument","operDocument","OperSel_Alph2_Short","A","15","15");

	$operNbr = "operPayer";
	print "\n <tr><td class=\"dsphdr\">Payer</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frPayer\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PayerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frPayer&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toPayer\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PayerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toPayer&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operPmtCode";
	print "\n <tr><td class=\"dsphdr\">Payment Code</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frPmtCode\" size=\"1\" maxlength=\"1\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ARPmtCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frPmtCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toPmtCode\" size=\"1\" maxlength=\"1\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ARPmtCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toPmtCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operSubCode";
	print "\n <tr><td class=\"dsphdr\">Payment Sub Code</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frSubCode\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frSubCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toSubCode\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toSubCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operEntryUser";
	print "\n <tr><td class=\"dsphdr\">Initial Entry By User</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frEntryUser\" size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;docName=Chg&amp;userFld=frEntryUser&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toEntryUser\" size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;docName=Chg&amp;userFld=toEntryUser&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operSalesman";
	print "\n <tr><td class=\"dsphdr\">Salesman</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frSalesman&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toSalesman&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operCashAcct";
	print "\n <tr><td class=\"dsphdr\">Cash Account</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frCashAcct\" size=\"4\" maxlength=\"4\">";
	print "\n                           - <input type=\"text\" name=\"frCashSub\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=frCashAcct&amp;subFld=frCashSub&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toCashAcct\" size=\"4\" maxlength=\"4\">";
	print "\n                           - <input type=\"text\" name=\"toCashSub\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=toCashAcct&amp;subFld=toCashSub&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	$operNbr = "operOffsetAcct";
	print "\n <tr><td class=\"dsphdr\">Offset Account</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frOffsetAcct\" size=\"4\" maxlength=\"4\">";
	print "\n                           - <input type=\"text\" name=\"frOffsetSub\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=frOffsetAcct&amp;subFld=frOffsetSub&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toOffsetAcct\" size=\"4\" maxlength=\"4\">";
	print "\n                           - <input type=\"text\" name=\"toOffsetSub\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=toOffsetAcct&amp;subFld=toOffsetSub&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
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

	if ($HDMCRL>0 && $CRPRMC=="Y") {$maxSequence=18;}
	else                           {$maxSequence=17;}

	$textOvr=SetTextOvr($Err_SBNK);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Bank</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortBank",$SBNK);
	Build_Fld_Entry("","totalBank","inputalph","YORN","TBNK",$TBNK,$Err_TBNK,"1","1","","","Y");
	Build_Fld_Entry("","pageBank","inputalph","YORN","PBNK",$PBNK,$Err_PBNK,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SBNK);

	$textOvr=SetTextOvr($Err_SCUS);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortCustomer",$SCUS);
	Build_Fld_Entry("","totalCustomer","inputalph","YORN","TCUS",$TCUS,$Err_TCUS,"1","1","","","Y");
	Build_Fld_Entry("","pageCustomer","inputalph","YORN","PCUS",$PCUS,$Err_PCUS,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SCUS);

	$textOvr=SetTextOvr($Err_SCNM);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Name</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortCustName",$SCNM);
	Build_Fld_Entry("","totalCustName","inputalph","YORN","TCNM",$TCNM,$Err_TCNM,"1","1","","","Y");
	Build_Fld_Entry("","pageCustName","inputalph","YORN","PCNM",$PCNM,$Err_PCNM,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SCNM);

	$textOvr=SetTextOvr($Err_SALP);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Alpha Sequence</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortAlpha",$SALP);
	Build_Fld_Entry("","totalAlpha","inputalph","YORN","TALP",$TALP,$Err_TALP,"1","1","","","Y");
	Build_Fld_Entry("","pageAlpha","inputalph","YORN","PALP",$PALP,$Err_PALP,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SALP);

	$textOvr=SetTextOvr($Err_SCLS);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Class</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortClass",$SCLS);
	Build_Fld_Entry("","totalClass","inputalph","YORN","TCLS",$TCLS,$Err_TCLS,"1","1","","","Y");
	Build_Fld_Entry("","pageClass","inputalph","YORN","PCLS",$PCLS,$Err_PCLS,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SCLS);

	$textOvr=SetTextOvr($Err_SRGN);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Region</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortRegion",$SRGN);
	Build_Fld_Entry("","totalRegion","inputalph","YORN","TRGN",$TRGN,$Err_TRGN,"1","1","","","Y");
	Build_Fld_Entry("","pageRegion","inputalph","YORN","PRGN",$PRGN,$Err_PRGN,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SRGN);

	$textOvr=SetTextOvr($Err_SPDT);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Date Paid</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortPaidDate",$SPDT);
	Build_Fld_Entry("","totalPaidDate","inputalph","YORN","TPDT",$TPDT,$Err_TPDT,"1","1","","","Y");
	Build_Fld_Entry("","pagePaidDate","inputalph","YORN","PPDT",$PPDT,$Err_PPDT,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SPDT);

	$textOvr=SetTextOvr($Err_SINV);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Invoice</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortInvoice",$SINV);
	Build_Fld_Entry("","totalInvoice","inputalph","YORN","TINV",$TINV,$Err_TINV,"1","1","","","Y");
	Build_Fld_Entry("","pageInvoice","inputalph","YORN","PINV",$PINV,$Err_PINV,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SINV);

	$textOvr=SetTextOvr($Err_SLOC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Location</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortLocation",$SLOC);
	Build_Fld_Entry("","totalLocation","inputalph","YORN","TLOC",$TLOC,$Err_TLOC,"1","1","","","Y");
	Build_Fld_Entry("","pageLocation","inputalph","YORN","PLOC",$PLOC,$Err_PLOC,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SLOC);

	$textOvr=SetTextOvr($Err_SDOC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Document</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortDocument",$SDOC);
	Build_Fld_Entry("","totalDocument","inputalph","YORN","TDOC",$TDOC,$Err_TDOC,"1","1","","","Y");
	Build_Fld_Entry("","pageDocument","inputalph","YORN","PDOC",$PDOC,$Err_PDOC,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SDOC);

	$textOvr=SetTextOvr($Err_SPYR);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Payer</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortPayer",$SPYR);
	Build_Fld_Entry("","totalPayer","inputalph","YORN","TPYR",$TPYR,$Err_TPYR,"1","1","","","Y");
	Build_Fld_Entry("","pagePayer","inputalph","YORN","PPYR",$PPYR,$Err_PPYR,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SPYR);

	$textOvr=SetTextOvr($Err_SPCD);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Payment Code</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortPmtCode",$SPCD);
	Build_Fld_Entry("","totalPmtCode","inputalph","YORN","TPCD",$TPCD,$Err_TPCD,"1","1","","","Y");
	Build_Fld_Entry("","pagePmtCode","inputalph","YORN","PPCD",$PPCD,$Err_PPCD,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SPCD);

	$textOvr=SetTextOvr($Err_SSUB);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Payment Sub Code</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortSubCode",$SSUB);
	Build_Fld_Entry("","totalSubCode","inputalph","YORN","TSUB",$TSUB,$Err_TSUB,"1","1","","","Y");
	Build_Fld_Entry("","pageSubCode","inputalph","YORN","PSUB",$PSUB,$Err_PSUB,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SSUB);

	$textOvr=SetTextOvr($Err_SUSR);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Initial Entry By User</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortEntryUser",$SUSR);
	Build_Fld_Entry("","totalEntryUser","inputalph","YORN","TUSR",$TUSR,$Err_TUSR,"1","1","","","Y");
	Build_Fld_Entry("","pageEntryUser","inputalph","YORN","PUSR",$PUSR,$Err_PUSR,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SUSR);

	$textOvr=SetTextOvr($Err_SSLM);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Salesman</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortSalesman",$SSLM);
	Build_Fld_Entry("","totalSalesman","inputalph","YORN","TSLM",$TSLM,$Err_TSLM,"1","1","","","Y");
	Build_Fld_Entry("","pageSalesman","inputalph","YORN","PSLM",$PSLM,$Err_PSLM,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SSLM);

	$textOvr=SetTextOvr($Err_SCSA);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Cash Account</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortCashAcct",$SCSA);
	Build_Fld_Entry("","totalCashAcct","inputalph","YORN","TCSA",$TCSA,$Err_TCSA,"1","1","","","Y");
	Build_Fld_Entry("","pageCashAcct","inputalph","YORN","PCSA",$PCSA,$Err_PCSA,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SCSA);

	$textOvr=SetTextOvr($Err_SOFA);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Offset Account</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortOffsetAcct",$SOFA);
	Build_Fld_Entry("","totalOffsetAcct","inputalph","YORN","TOFA",$TOFA,$Err_TOFA,"1","1","","","Y");
	Build_Fld_Entry("","pageOffsetAcct","inputalph","YORN","POFA",$POFA,$Err_POFA,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SOFA);

	if ($HDMCRL>0 && $CRPRMC=="Y") {
		$textOvr=SetTextOvr($Err_SCUR);
		print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Currency Type</span></td>";
		$sortSeqMax=$maxSequence; Build_Sort_Select("sortCurrency",$SCUR);
		Build_Fld_Entry("","totalCurrency","inputalph","YORN","TCUR",$TCUR,$Err_TCUR,"1","1","","","Y");
		Build_Fld_Entry("","pageCurrency","inputalph","YORN","PCUR",$PCUR,$Err_PCUR,"1","1","","","Y");
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

	$returnValue=Range_WildCard("YPBANK", "Bank", $_POST['frBank'], $_POST['toBank'], "", $_POST['operBank'], "N");
	$returnValue=Range_WildCard("IVBLTO", "Customer", $_POST['frCustomer'], $_POST['toCustomer'], "", $_POST['operCustomer'], "N");
	$returnValue=Range_WildCard("CMCNA1U", "Customer Name", $_POST['frCustName'], $_POST['toCustName'], "U", $_POST['operCustName'], "A");
	$returnValue=Range_WildCard("CMALPH", "Alpha Sequence", $_POST['frAlphaSeq'], $_POST['toAlphaSeq'], "U", $_POST['operAlphaSeq'], "A");
	$returnValue=Range_WildCard("CMCCLS", "Customer Class", $_POST['frClass'], $_POST['toClass'], "U", $_POST['operClass'], "A");
	$returnValue=Range_WildCard("CMCRGN", "Customer Region", $_POST['frRegion'], $_POST['toRegion'], "U", $_POST['operRegion'], "A");
	$returnValue=Range_WildCard("YPBDAT", "Date Paid", $_POST['frPaidDate'], $_POST['toPaidDate'], "", $_POST['operPaidDate'], "D");
	$returnValue=Range_WildCard("IVAINV", "Invoice", $_POST['frInvoice'], $_POST['toInvoice'], "", $_POST['operInvoice'], "N");
	$returnValue=Range_WildCard("IVLOC", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Range_WildCard("UPPER(YPCHK)", "Document", $_POST['frDocument'], $_POST['toDocument'], "U", $_POST['operDocument'], "A");
	$returnValue=Range_WildCard("YPPAYR", "Payer", $_POST['frPayer'], $_POST['toPayer'], "", $_POST['operPayer'], "N");
	$returnValue=Range_WildCard("YPPYCD", "Payment Code", $_POST['frPmtCode'], $_POST['toPmtCode'], "U", $_POST['operPmtCode'], "A");
	$returnValue=Range_WildCard("YPSBCD", "Payment Sub Code", $_POST['frSubCode'], $_POST['toSubCode'], "U", $_POST['operSubCode'], "A");
	$returnValue=Range_WildCard("YPAUDU", "Initial Entry By User", $_POST['frEntryUser'], $_POST['toEntryUser'], "U", $_POST['operEntryUser'], "A");
	$returnValue=Range_WildCard("IVSLSM", "Salesman", $_POST['frSalesman'], $_POST['toSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Range_WildCard_Acct("YPCSAC", "YPCSSB", "Cash Account", $_POST['frCashAcct'], $_POST['frCashSub'], $_POST['toCashAcct'], $_POST['toCashSub'], $_POST['operCashAcct']);
	$returnValue=Range_WildCard_Acct("YPACCT", "YPSUB", "Offset Account", $_POST['frOffsetAcct'], $_POST['frOffsetSub'], $_POST['toOffsetAcct'], $_POST['toOffsetSub'], $_POST['operOffsetAcct']);
	$returnValue=Range_WildCard("IVCURT", "Currency Type", $_POST['frCurType'], $_POST['toCurType'], "U", $_POST['operCurType'], "A");
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);
	if (!isset($_POST['PrintComment'])) {$_POST['PrintComment']="N";}  Concat_Field("@@cmnt", $_POST['PrintComment']);

	if (!isset($_POST['onAccount'])) {$_POST['onAccount']="N";}  Concat_Field("@@ona@", $_POST['onAccount']);
	if (!isset($_POST['deduction'])) {$_POST['deduction']="N";}  Concat_Field("@@ded@", $_POST['deduction']);
	if (!isset($_POST['serviceCharge'])) {$_POST['serviceCharge']="N";}  Concat_Field("@@svg@", $_POST['serviceCharge']);
	if (!isset($_POST['nsf'])) {$_POST['nsf']="N";}  Concat_Field("@@nsf@", $_POST['nsf']);
	if (!isset($_POST['unappliedCash'])) {$_POST['unappliedCash']="N";}  Concat_Field("@@una@", $_POST['unappliedCash']);
	if (!isset($_POST['miscCash'])) {$_POST['miscCash']="N";}  Concat_Field("@@msc@", $_POST['miscCash']);

	Concat_Field("@@idcr", strtoupper($_POST['currencyID']));
	Concat_Field("@@curt", strtoupper($_POST['currencyType']));

	Concat_Field("@@sbnk", $_POST['sortBank']);
	if (!isset($_POST['totalBank'])) {$_POST['totalBank']="N";}  Concat_Field("@@tbnk", $_POST['totalBank']);
	if (!isset($_POST['pageBank'])) {$_POST['pageBank']="N";}  Concat_Field("@@pbnk", $_POST['pageBank']);
	Concat_Field("@@scus", $_POST['sortCustomer']);
	if (!isset($_POST['totalCustomer'])) {$_POST['totalCustomer']="N";}  Concat_Field("@@tcus", $_POST['totalCustomer']);
	if (!isset($_POST['pageCustomer'])) {$_POST['pageCustomer']="N";}  Concat_Field("@@pcus", $_POST['pageCustomer']);
	Concat_Field("@@scnm", $_POST['sortCustName']);
	if (!isset($_POST['totalCustName'])) {$_POST['totalCustName']="N";}  Concat_Field("@@tcnm", $_POST['totalCustName']);
	if (!isset($_POST['pageCustName'])) {$_POST['pageCustName']="N";}  Concat_Field("@@pcnm", $_POST['pageCustName']);
	Concat_Field("@@salp", $_POST['sortAlpha']);
	if (!isset($_POST['totalAlpha'])) {$_POST['totalAlpha']="N";}  Concat_Field("@@talp", $_POST['totalAlpha']);
	if (!isset($_POST['pageAlpha'])) {$_POST['pageAlpha']="N";}  Concat_Field("@@palp", $_POST['pageAlpha']);
	Concat_Field("@@scls", $_POST['sortClass']);
	if (!isset($_POST['totalClass'])) {$_POST['totalClass']="N";}  Concat_Field("@@tcls", $_POST['totalClass']);
	if (!isset($_POST['pageClass'])) {$_POST['pageClass']="N";}  Concat_Field("@@pcls", $_POST['pageClass']);
	Concat_Field("@@srgn", $_POST['sortRegion']);
	if (!isset($_POST['totalRegion'])) {$_POST['totalRegion']="N";}  Concat_Field("@@trgn", $_POST['totalRegion']);
	if (!isset($_POST['pageRegion'])) {$_POST['pageRegion']="N";}  Concat_Field("@@prgn", $_POST['pageRegion']);
	Concat_Field("@@spdt", $_POST['sortPaidDate']);
	if (!isset($_POST['totalPaidDate'])) {$_POST['totalPaidDate']="N";}  Concat_Field("@@tpdt", $_POST['totalPaidDate']);
	if (!isset($_POST['pagePaidDate'])) {$_POST['pagePaidDate']="N";}  Concat_Field("@@ppdt", $_POST['pagePaidDate']);
	Concat_Field("@@sinv", $_POST['sortInvoice']);
	if (!isset($_POST['totalInvoice'])) {$_POST['totalInvoice']="N";}  Concat_Field("@@tinv", $_POST['totalInvoice']);
	if (!isset($_POST['pageInvoice'])) {$_POST['pageInvoice']="N";}  Concat_Field("@@pinv", $_POST['pageInvoice']);
	Concat_Field("@@sloc", $_POST['sortLocation']);
	if (!isset($_POST['totalLocation'])) {$_POST['totalLocation']="N";}  Concat_Field("@@tloc", $_POST['totalLocation']);
	if (!isset($_POST['pageLocation'])) {$_POST['pageLocation']="N";}  Concat_Field("@@ploc", $_POST['pageLocation']);
	Concat_Field("@@sdoc", $_POST['sortDocument']);
	if (!isset($_POST['totalDocument'])) {$_POST['totalDocument']="N";}  Concat_Field("@@tdoc", $_POST['totalDocument']);
	if (!isset($_POST['pageDocument'])) {$_POST['pageDocument']="N";}  Concat_Field("@@pdoc", $_POST['pageDocument']);
	Concat_Field("@@spyr", $_POST['sortPayer']);
	if (!isset($_POST['totalPayer'])) {$_POST['totalPayer']="N";}  Concat_Field("@@tpyr", $_POST['totalPayer']);
	if (!isset($_POST['pagePayer'])) {$_POST['pagePayer']="N";}  Concat_Field("@@ppyr", $_POST['pagePayer']);
	Concat_Field("@@spcd", $_POST['sortPmtCode']);
	if (!isset($_POST['totalPmtCode'])) {$_POST['totalPmtCode']="N";}  Concat_Field("@@tpcd", $_POST['totalPmtCode']);
	if (!isset($_POST['pagePmtCode'])) {$_POST['pagePmtCode']="N";}  Concat_Field("@@ppcd", $_POST['pagePmtCode']);
	Concat_Field("@@ssub", $_POST['sortSubCode']);
	if (!isset($_POST['totalSubCode'])) {$_POST['totalSubCode']="N";}  Concat_Field("@@tsub", $_POST['totalSubCode']);
	if (!isset($_POST['pageSubCode'])) {$_POST['pageSubCode']="N";}  Concat_Field("@@psub", $_POST['pageSubCode']);
	Concat_Field("@@susr", $_POST['sortEntryUser']);
	if (!isset($_POST['totalEntryUser'])) {$_POST['totalEntryUser']="N";}  Concat_Field("@@tusr", $_POST['totalEntryUser']);
	if (!isset($_POST['pageEntryUser'])) {$_POST['pageEntryUser']="N";}  Concat_Field("@@pusr", $_POST['pageEntryUser']);
	Concat_Field("@@sslm", $_POST['sortSalesman']);
	if (!isset($_POST['totalSalesman'])) {$_POST['totalSalesman']="N";}  Concat_Field("@@tslm", $_POST['totalSalesman']);
	if (!isset($_POST['pageSalesman'])) {$_POST['pageSalesman']="N";}  Concat_Field("@@pslm", $_POST['pageSalesman']);
	Concat_Field("@@scsa", $_POST['sortCashAcct']);
	if (!isset($_POST['totalCashAcct'])) {$_POST['totalCashAcct']="N";}  Concat_Field("@@tcsa", $_POST['totalCashAcct']);
	if (!isset($_POST['pageCashAcct'])) {$_POST['pageCashAcct']="N";}  Concat_Field("@@pcsa", $_POST['pageCashAcct']);
	Concat_Field("@@sofa", $_POST['sortOffsetAcct']);
	if (!isset($_POST['totalOffsetAcct'])) {$_POST['totalOffsetAcct']="N";}  Concat_Field("@@tofa", $_POST['totalOffsetAcct']);
	if (!isset($_POST['pageOffsetAcct'])) {$_POST['pageOffsetAcct']="N";}  Concat_Field("@@pofa", $_POST['pageOffsetAcct']);
	Concat_Field("@@scur", $_POST['sortCurrency']);
	if (!isset($_POST['totalCurrency'])) {$_POST['totalCurrency']="N";}  Concat_Field("@@tcur", $_POST['totalCurrency']);
	if (!isset($_POST['pageCurrency'])) {$_POST['pageCurrency']="N";}  Concat_Field("@@pcur", $_POST['pageCurrency']);

	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HARSPH_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
