<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$backHome           = $_GET['backHome'];
$errFound           = $_GET['errFound'];
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

$page_title            = "A/R Statement";
$scriptName            = "ARStatement.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CARSTM";
$submitEnvProgram      = "CARSTM";
$submitEnvPrinter      = "";
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
	print "\n   if (document.Chg.CutoffDate.value ==\"\")";
	print "\n     {alert(\"$reqFieldError\"); return false;} ";
	print "\n   if (editdate(document.Chg.CutoffDate) ";
	print "\n    && editdate(document.Chg.FullyPaidDate) ";
	print "\n    && editdate(document.Chg.BalanceForwardDate) ";
	print "\n    && editNumPos(document.Chg.DaysPastDue, 5, 0) ";
	print "\n    && editNumPos(document.Chg.CurPastDue, 13, 0) ";
	print "\n    && editNum(document.Chg.frLocation, 3, 0) ";
	print "\n    && editNum(document.Chg.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frLocation,  document.Chg.toLocation,  document.Chg.operLocation, 3) ";
	print "\n    && editNum(document.Chg.frCustomer, 7, 0) ";
	print "\n    && editNum(document.Chg.toCustomer, 7, 0) ";
	print "\n    && editFromToOper(document.Chg.frCustomer,  document.Chg.toCustomer,  document.Chg.operCustomer, 7) ";
	print "\n    && editFromToOper(document.Chg.frCustName,  document.Chg.toCustName,  document.Chg.operCustName, 'A') ";
	print "\n    && editFromToOper(document.Chg.frAlphaSeq,  document.Chg.toAlphaSeq,  document.Chg.operAlphaSeq, 'A') ";
	print "\n    && editNum(document.Chg.frSalesman, 3, 0) ";
	print "\n    && editNum(document.Chg.toSalesman, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frSalesman,  document.Chg.toSalesman,  document.Chg.operSalesman, 'A') ";
	print "\n    && editFromToOper(document.Chg.frClass,     document.Chg.toClass,     document.Chg.operClass, 'A') ";
	print "\n    && editFromToOper(document.Chg.frRegion,    document.Chg.toRegion,    document.Chg.operRegion, 'A') ";
	print "\n    && editFromToOper(document.Chg.frCollector, document.Chg.toCollector, document.Chg.operCollector, 'A') ";
	if ($HDMCRL>0 && $CRPRMC=="Y") {print "\n    && editFromToOper(document.Chg.frCurType, document.Chg.toCurType, document.Chg.operCurType, 'A') ";}
	print "\n       ) {return true;} ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARSTATEMENT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="CutoffDate";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_Cutoff=DecatErr_Field("@@cuto", "CutoffDate");
			$Err_FullPd=DecatErr_Field("@@full", "FullyPaidDate");
			$Err_BalFwd=DecatErr_Field("@@balf", "BalanceForwardDate");
			$Err_PmtDtl=DecatErr_Field("@@pmtd", "PrintPmtDtl");
			$Err_PrtCmt=DecatErr_Field("@@prtc", "PrintComment");
			$Err_DayDue=DecatErr_Field("@@dayd", "DaysPastDue");
			$Err_CurDue=DecatErr_Field("@@curd", "CurPastDue");
			$Err_StmYN=DecatErr_Field("@@stmy", "CustStmtYN");
			$Err_NegVal=DecatErr_Field("@@negv", "StmNegValue");

			$Err_BrkLoc=DecatErr_Field("@@bloc", "BreakLocation");
			$Err_BrkCus=DecatErr_Field("@@bcus", "BreakCustomer");
			$Err_BrkNam=DecatErr_Field("@@bnam", "BreakName");
			$Err_BrkCls=DecatErr_Field("@@bcls", "BreakClass");
			$Err_BrkRgn=DecatErr_Field("@@brgn", "BreakRegion");
			$Err_BrkSlm=DecatErr_Field("@@bslm", "BreakSalesman");

			$Err_SrtInv=DecatErr_Field("@@sinv", "SortInvoice");
			$Err_SrtIvD=DecatErr_Field("@@sivd", "SortInvoiceDate");
			$Err_SrtDuD=DecatErr_Field("@@sdud", "SortDueDate");
			$Err_SrtRef=DecatErr_Field("@@sref", "SortReference");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$Cutoff=Decat_Field("@@cuto", $edtVar);
		$FullPd=Decat_Field("@@full", $edtVar);
		$BalFwd=Decat_Field("@@balf", $edtVar);
		$PmtDtl=Decat_Field("@@pmtd", $edtVar);
		$PrtCmt=Decat_Field("@@prtc", $edtVar);
		$DayDue=Decat_Field("@@dayd", $edtVar);
		$CurDue=Decat_Field("@@curd", $edtVar);
		$StmYN=Decat_Field("@@stmy", $edtVar);
		$NegVal=Decat_Field("@@negv", $edtVar);

		$BrkLoc=Decat_Field("@@bloc", $edtVar);
		$BrkCus=Decat_Field("@@bcus", $edtVar);
		$BrkNam=Decat_Field("@@bnam", $edtVar);
		$BrkCls=Decat_Field("@@bcls", $edtVar);
		$BrkRgn=Decat_Field("@@brgn", $edtVar);
		$BrkSlm=Decat_Field("@@bslm", $edtVar);

		$SrtInv=Decat_Field("@@sinv", $edtVar);
		$SrtIvD=Decat_Field("@@sivd", $edtVar);
		$SrtDuD=Decat_Field("@@sdud", $edtVar);
		$SrtRef=Decat_Field("@@sref", $edtVar);
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$CRDPER=RetValue("RRN(ARCTRL)=1", "ARCTRL", "CRDPER");
		$ARPdEndDate=RetValue("PDPER#=$CRDPER", "HDPBED", "PDEDAT");
		$Cutoff=DateInputFromCYMD($ARPdEndDate);
		$StmYN="Y";
		$BrkCus=1;
		if ($CRLPRI=="I") {$BrkLoc=2;}
		if ($CRBAON=="D") {$SrtDuD=1;}
		else              {$SrtIvD=1;}
		$SrtInv=2;
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#Date\">Date</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#PrintOption\">Print Option</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#CustomerCriteria\">Customer Criteria</a></td> ";
	print "\n   </tr> ";
	print "\n   <tr> ";
	if ($wildCardDisplay != "") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#CurrentFilterCriteria\">Current Filter Criteria</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#RefineFilterCriteria\">Refine Filter Criteria</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#StatementBreak\">Statement Break</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#DetailSort\">Detail Sort</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	print $hrTagAttr;
	
	require_once 'ErrorDisplay.php';

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\">";

	print "\n <a name=\"Date\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Date</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	// Invoice/Payment Cutoff Date
	$textOvr=SetTextOvr($Err_Cutoff);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Invoice/Payment Cutoff</span></td>";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"CutoffDate\" value=\"" . rtrim($Cutoff) . "\" size=\"6\" maxlength=\"6\"> ";
	print "\n                                     <a href=\"javascript:calWindow('CutoffDate');\"> $reqFieldChar $calendarImage</a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_Cutoff);

	// Fully Paid Date
	$textOvr=SetTextOvr($Err_FullPd);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Include Fully Paid Invoices After</span></td>";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"FullyPaidDate\" value=\"" . rtrim($FullPd) . "\" size=\"6\" maxlength=\"6\"> ";
	print "\n                                     <a href=\"javascript:calWindow('FullyPaidDate');\"> &nbsp; $calendarImage</a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_FullPd);

	// Balance Forward
	$textOvr=SetTextOvr($Err_BalFwd);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Balance Forward</span></td>";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"BalanceForwardDate\" value=\"" . rtrim($BalFwd) . "\" size=\"6\" maxlength=\"6\"> ";
	print "\n                                     <a href=\"javascript:calWindow('BalanceForwardDate');\"> &nbsp; $calendarImage</a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_BalFwd);
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"PrintOption\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Print Option</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Print Payment Detail","PrintPmtDtl","inputalph","YORN","PmtDtl",$PmtDtl,$Err_PmtDtl,"1","1","","","");
	Build_Fld_Entry("Print Comments","PrintComment","inputalph","YORN","PrtCmt",$PrtCmt,$Err_PrtCmt,"1","1","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"CustomerCriteria\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Customer Criteria</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Days Past Due","DaysPastDue","inputnmbr","","DayDue",$DayDue,$Err_DayDue,"5","5","","","");
	Build_Fld_Entry("Currency Past Due","CurPastDue","inputnmbr","","CurDue",$CurDue,$Err_CurDue,"13","13","","","");
	Build_Fld_Entry("Limit To Customers Assigned To Receive Statement","CustStmtYN","inputalph","YORN","StmYN",$StmYN,$Err_StmYN,"1","1","","","");
	Build_Fld_Entry("Produce Statements With Negative Value","StmNegValue","inputalph","YORN","NegVal",$NegVal,$Err_NegVal,"1","1","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	require_once 'AdvSearchTopReport.php';

	$operNbr = "operLocation";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frLocation&amp;fldDesc=frLocationDesc\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toLocation&amp;fldDesc=toLocationDesc\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	$operNbr = "operCustomer";
	print "\n <tr><td class=\"dsphdr\">Customer</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frCustomer\" size=\"7\" maxlength=\"7\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frCustomer&amp;fldDesc=frCustomerName\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toCustomer\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toCustomer&amp;fldDesc=toCustomerName\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Customer Name","frCustName","toCustName","operCustName","OperSel_Alph2_Short","A","20","30");
	Build_AdvSrch_Entry("Alpha Sequence","frAlphaSeq","toAlphaSeq","operAlphaSeq","OperSel_Alph2_Short","A","4","4");

	$operNbr = "operSalesman";
	print "\n <tr><td class=\"dsphdr\">Salesman</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frSalesman&amp;fldDesc=frSalesmanName\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toSalesman&amp;fldDesc=toSalesmanName\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

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

	$operNbr = "operCollector";
	print "\n <tr><td class=\"dsphdr\">Collector</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frCollector\" size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=frCollector&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toCollector\" size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=toCollector&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
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

	print "\n <a name=\"StatementBreak\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Statement Break</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";

	$maxSequence=6;

	$textOvr=SetTextOvr($Err_BrkLoc);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Location</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("BreakLocation",$BrkLoc);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkLoc);

	$textOvr=SetTextOvr($Err_BrkCus);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("BreakCustomer",$BrkCus);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkCus);

	$textOvr=SetTextOvr($Err_BrkNam);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Name</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("BreakName",$BrkNam);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkNam);

	$textOvr=SetTextOvr($Err_BrkCls);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Class</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("BreakClass",$BrkCls);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkCls);

	$textOvr=SetTextOvr($Err_BrkRgn);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Region</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("BreakRegion",$BrkRgn);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkRgn);

	$textOvr=SetTextOvr($Err_BrkSlm);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Salesman</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("BreakSalesman",$BrkSlm);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkSlm);

	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"DetailSort\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Detail Sort</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";

	$maxSequence=4;

	$textOvr=SetTextOvr($Err_SrtInv);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Invoice</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("SortInvoice",$SrtInv);
	print "\n         </tr> ";
	DspErrMsg($Err_SrtInv);

	$textOvr=SetTextOvr($Err_SrtIvD);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Invoice Date</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("SortInvoiceDate",$SrtIvD);
	print "\n         </tr> ";
	DspErrMsg($Err_SrtIvD);

	$textOvr=SetTextOvr($Err_SrtDuD);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Due Date</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("SortDueDate",$SrtDuD);
	print "\n         </tr> ";
	DspErrMsg($Err_SrtDuD);

	$textOvr=SetTextOvr($Err_SrtRef);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Reference Number</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("SortReference",$SrtRef);
	print "\n         </tr> ";
	DspErrMsg($Err_SrtRef);

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

	$returnValue=Range_WildCard("IVLOC", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Range_WildCard("IVBLTO", "Customer", $_POST['frCustomer'], $_POST['toCustomer'], "", $_POST['operCustomer'], "N");
	$returnValue=Range_WildCard("CMCNA1U", "Customer Name", $_POST['frCustName'], $_POST['toCustName'], "U", $_POST['operCustName'], "A");
	$returnValue=Range_WildCard("CMALPH", "Alpha Sequence", $_POST['frAlphaSeq'], $_POST['toAlphaSeq'], "U", $_POST['operAlphaSeq'], "A");
	$returnValue=Range_WildCard("IVSLSM", "Salesman", $_POST['frSalesman'], $_POST['toSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Range_WildCard("CMCCLS", "Customer Class", $_POST['frClass'], $_POST['toClass'], "U", $_POST['operClass'], "A");
	$returnValue=Range_WildCard("CMCRGN", "Customer Region", $_POST['frRegion'], $_POST['toRegion'], "U", $_POST['operRegion'], "A");
	$returnValue=Range_WildCard("CMCLCT", "Collector", $_POST['frCollector'], $_POST['toCollector'], "U", $_POST['operCollector'], "A");
	$returnValue=Range_WildCard("IVCURT", "Currency Type", $_POST['frCurType'], $_POST['toCurType'], "U", $_POST['operCurType'], "A");
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);
	Concat_Field("@@cuto", $_POST['CutoffDate']);
	Concat_Field("@@full", $_POST['FullyPaidDate']);
	Concat_Field("@@balf", $_POST['BalanceForwardDate']);
	if (!isset($_POST['PrintPmtDtl'])) {$_POST['PrintPmtDtl']="N";}  Concat_Field("@@pmtd", $_POST['PrintPmtDtl']);
	if (!isset($_POST['PrintComment'])) {$_POST['PrintComment']="N";}  Concat_Field("@@prtc", $_POST['PrintComment']);
	Concat_Field("@@dayd", $_POST['DaysPastDue']);
	Concat_Field("@@curd", $_POST['CurPastDue']);
	if (!isset($_POST['CustStmtYN'])) {$_POST['CustStmtYN']="B";}  Concat_Field("@@stmy", $_POST['CustStmtYN']);
	if (!isset($_POST['StmNegValue'])) {$_POST['StmNegValue']="N";}  Concat_Field("@@negv", $_POST['StmNegValue']);

	Concat_Field("@@bloc", $_POST['BreakLocation']);
	Concat_Field("@@bcus", $_POST['BreakCustomer']);
	Concat_Field("@@bnam", $_POST['BreakName']);
	Concat_Field("@@bcls", $_POST['BreakClass']);
	Concat_Field("@@brgn", $_POST['BreakRegion']);
	Concat_Field("@@bslm", $_POST['BreakSalesman']);

	Concat_Field("@@sinv", $_POST['SortInvoice']);
	Concat_Field("@@sivd", $_POST['SortInvoiceDate']);
	Concat_Field("@@sdud", $_POST['SortDueDate']);
	Concat_Field("@@sref", $_POST['SortReference']);

	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HARSTS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
