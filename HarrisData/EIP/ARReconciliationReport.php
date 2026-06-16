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

$page_title            = "A/R Reconciliation Report";
$scriptName            = "ARReconciliationReport.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CARRRT";
$submitEnvProgram      = "CARRRT";
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
	print "\n   if (editNum(document.Chg.DistributionPeriod, 4, 0) ";
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
	$pageID = "ARRECONCILIATIONREPORT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="DistributionPeriod";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			$Err_DPER=DecatErr_Field("@@dper", "DistributionPeriod");
			$Err_SUMF=DecatErr_Field("@@sumf", "SummaryFormat");

			$Err_SLOC=DecatErr_Field("@@sloc", "sortLocation");
			$Err_SCOF=DecatErr_Field("@@scof", "sortCompany");
			$Err_SGLA=DecatErr_Field("@@sgla", "sortAccount");
			$Err_SPER=DecatErr_Field("@@sper", "sortDistPeriod");
			$Err_SCUS=DecatErr_Field("@@scus", "sortCustomer");
			$Err_SCNM=DecatErr_Field("@@scnm", "sortCustName");
			$Err_SINV=DecatErr_Field("@@sinv", "sortInvoice");
			$Err_SDTE=DecatErr_Field("@@sdte", "sortDate");
			$Err_SCUR=DecatErr_Field("@@scur", "sortCurrency");

			$Err_TLOC=DecatErr_Field("@@tloc", "totalLocation");
			$Err_TCOF=DecatErr_Field("@@tcof", "totalCompany");
			$Err_TGLA=DecatErr_Field("@@tgla", "totalAccount");
			$Err_TPER=DecatErr_Field("@@tper", "totalDistPeriod");
			$Err_TCUS=DecatErr_Field("@@tcus", "totalCustomer");
			$Err_TCNM=DecatErr_Field("@@tcnm", "totalCustName");
			$Err_TINV=DecatErr_Field("@@tinv", "totalInvoice");
			$Err_TDTE=DecatErr_Field("@@tdte", "totalDate");
			$Err_TCUR=DecatErr_Field("@@tcur", "totalCurrency");

			$Err_PLOC=DecatErr_Field("@@ploc", "pageLocation");
			$Err_PCOF=DecatErr_Field("@@pcof", "pageCompany");
			$Err_PGLA=DecatErr_Field("@@pgla", "pageAccount");
			$Err_PPER=DecatErr_Field("@@pper", "pageDistPeriod");
			$Err_PCUS=DecatErr_Field("@@pcus", "pageCustomer");
			$Err_PCNM=DecatErr_Field("@@pcnm", "pageCustName");
			$Err_PINV=DecatErr_Field("@@pinv", "pageInvoice");
			$Err_PDTE=DecatErr_Field("@@pdte", "pageDate");
			$Err_PCUR=DecatErr_Field("@@pcur", "pageCurrency");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);

		$DPER=Decat_Field("@@dper", $edtVar);
		$SUMF=Decat_Field("@@sumf", $edtVar);

		$SLOC=Decat_Field("@@sloc", $edtVar);
		$SCOF=Decat_Field("@@scof", $edtVar);
		$SGLA=Decat_Field("@@sgla", $edtVar);
		$SPER=Decat_Field("@@sper", $edtVar);
		$SCUS=Decat_Field("@@scus", $edtVar);
		$SCNM=Decat_Field("@@scnm", $edtVar);
		$SINV=Decat_Field("@@sinv", $edtVar);
		$SDTE=Decat_Field("@@sdte", $edtVar);
		$SCUR=Decat_Field("@@scur", $edtVar);

		$TLOC=Decat_Field("@@tloc", $edtVar);
		$TCOF=Decat_Field("@@tcof", $edtVar);
		$TGLA=Decat_Field("@@tgla", $edtVar);
		$TPER=Decat_Field("@@tper", $edtVar);
		$TCUS=Decat_Field("@@tcus", $edtVar);
		$TCNM=Decat_Field("@@tcnm", $edtVar);
		$TINV=Decat_Field("@@tinv", $edtVar);
		$TDTE=Decat_Field("@@tdte", $edtVar);
		$TCUR=Decat_Field("@@tcur", $edtVar);

		$PLOC=Decat_Field("@@ploc", $edtVar);
		$PCOF=Decat_Field("@@pcof", $edtVar);
		$PGLA=Decat_Field("@@pgla", $edtVar);
		$PPER=Decat_Field("@@pper", $edtVar);
		$PCUS=Decat_Field("@@pcus", $edtVar);
		$PCNM=Decat_Field("@@pcnm", $edtVar);
		$PINV=Decat_Field("@@pinv", $edtVar);
		$PDTE=Decat_Field("@@pdte", $edtVar);
		$PCUR=Decat_Field("@@pcur", $edtVar);
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$SUMF="N";

		$TLOC="N";
		$TCOF="N";
		$TGLA="N";
		$TPER="N";
		$TCUS="N";
		$TCNM="N";
		$TINV="N";
		$TDTE="N";
		$TCUR="N";

		$PLOC="N";
		$PCOF="N";
		$PGLA="N";
		$PPER="N";
		$PCUS="N";
		$PCNM="N";
		$PINV="N";
		$PDTE="N";
		$PCUR="N";
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#PrintOption\">Print Option</a></td> ";
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
	$textOvr=SetTextOvr($Err_DPER);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Distribution Period To Base Report On</span></td> ";
	print "\n              <td class=\"inputnmbr\"><input type=\"text\" name=\"DistributionPeriod\" value=\"$DPER\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                      <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;docName=Chg&amp;periodFld=DistributionPeriod\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_DPER);

	Build_Fld_Entry("Print Summary Format","SummaryFormat","inputalph","YORN","SUMF",$SUMF,$Err_SUMF,"1","1","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	require_once 'AdvSearchTopReport.php';

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

	if ($HDMCRL>0 && $CRPRMC=="Y") {$maxSequence=9;}
	else                           {$maxSequence=8;}

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

	$returnValue=Range_WildCard_CoFac("LOCO#", "LOFAC#", "Company/Facility", $_POST['frCompany'], $_POST['frFacility'], $_POST['toCompany'], $_POST['toFacility'], $_POST['operCompany']);
	$returnValue=Range_WildCard_Acct("IVARAC", "IVARSB", "G/L Account", $_POST['frAccount'], $_POST['frSubaccount'], $_POST['toAccount'], $_POST['toSubaccount'], $_POST['operAccount']);
	$returnValue=Range_WildCard("IVCURD", "Currency Type", $_POST['frCurType'], $_POST['toCurType'], "U", $_POST['operCurType'], "A");
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);
	Concat_Field("@@dper", $_POST['DistributionPeriod']);
	if (!isset($_POST['SummaryFormat'])) {$_POST['SummaryFormat']="N";}  Concat_Field("@@sumf", $_POST['SummaryFormat']);

	Concat_Field("@@sloc", $_POST['sortLocation']);
	if (!isset($_POST['totalLocation'])) {$_POST['totalLocation']="N";}  Concat_Field("@@tloc", $_POST['totalLocation']);
	if (!isset($_POST['pageLocation'])) {$_POST['pageLocation']="N";}  Concat_Field("@@ploc", $_POST['pageLocation']);

	Concat_Field("@@scof", $_POST['sortCompany']);
	if (!isset($_POST['totalCompany'])) {$_POST['totalCompany']="N";}  Concat_Field("@@tcof", $_POST['totalCompany']);
	if (!isset($_POST['pageCompany'])) {$_POST['pageCompany']="N";}  Concat_Field("@@pcof", $_POST['pageCompany']);

	Concat_Field("@@sgla", $_POST['sortAccount']);
	if (!isset($_POST['totalAccount'])) {$_POST['totalAccount']="N";}  Concat_Field("@@tgla", $_POST['totalAccount']);
	if (!isset($_POST['pageAccount'])) {$_POST['pageAccount']="N";}  Concat_Field("@@pgla", $_POST['pageAccount']);

	Concat_Field("@@sper", $_POST['sortDistPeriod']);
	if (!isset($_POST['totalDistPeriod'])) {$_POST['totalDistPeriod']="N";}  Concat_Field("@@tper", $_POST['totalDistPeriod']);
	if (!isset($_POST['pageDistPeriod'])) {$_POST['pageDistPeriod']="N";}  Concat_Field("@@pper", $_POST['pageDistPeriod']);

	Concat_Field("@@scus", $_POST['sortCustomer']);
	if (!isset($_POST['totalCustomer'])) {$_POST['totalCustomer']="N";}  Concat_Field("@@tcus", $_POST['totalCustomer']);
	if (!isset($_POST['pageCustomer'])) {$_POST['pageCustomer']="N";}  Concat_Field("@@pcus", $_POST['pageCustomer']);

	Concat_Field("@@scnm", $_POST['sortCustName']);
	if (!isset($_POST['totalCustName'])) {$_POST['totalCustName']="N";}  Concat_Field("@@tcnm", $_POST['totalCustName']);
	if (!isset($_POST['pageCustName'])) {$_POST['pageCustName']="N";}  Concat_Field("@@pcnm", $_POST['pageCustName']);

	Concat_Field("@@sinv", $_POST['sortInvoice']);
	if (!isset($_POST['totalInvoice'])) {$_POST['totalInvoice']="N";}  Concat_Field("@@tinv", $_POST['totalInvoice']);
	if (!isset($_POST['pageInvoice'])) {$_POST['pageInvoice']="N";}  Concat_Field("@@pinv", $_POST['pageInvoice']);

	Concat_Field("@@sdte", $_POST['sortDate']);
	if (!isset($_POST['totalDate'])) {$_POST['totalDate']="N";}  Concat_Field("@@tdte", $_POST['totalDate']);
	if (!isset($_POST['pageDate'])) {$_POST['pageDate']="N";}  Concat_Field("@@pdte", $_POST['pageDate']);

	Concat_Field("@@scur", $_POST['sortCurrency']);
	if (!isset($_POST['totalCurrency'])) {$_POST['totalCurrency']="N";}  Concat_Field("@@tcur", $_POST['totalCurrency']);
	if (!isset($_POST['pageCurrency'])) {$_POST['pageCurrency']="N";}  Concat_Field("@@pcur", $_POST['pageCurrency']);

	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HARRRS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>
