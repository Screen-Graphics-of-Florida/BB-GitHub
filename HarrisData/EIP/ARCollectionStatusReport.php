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

$page_title            = "A/R Collection Status Report";
$scriptName            = "ARCollectionStatusReport.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CARCSR";
$submitEnvProgram      = "HARCSR";
$submitEnvPrinter      = "HARCSRPF";
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
	print "\n   if (editFromToOper(document.Chg.frStatusCode,  document.Chg.toStatusCode,  document.Chg.operStatusCode, 'A') ";
	print "\n    && editNum(document.Chg.frCustomer, 7, 0) ";
	print "\n    && editNum(document.Chg.toCustomer, 7, 0) ";
	print "\n    && editFromToOper(document.Chg.frCustomer,    document.Chg.toCustomer,    document.Chg.operCustomer, 7) ";
	print "\n    && editFromToOper(document.Chg.frCustName,    document.Chg.toCustName,    document.Chg.operCustName, 'A') ";
	print "\n    && editFromToOper(document.Chg.frAlphaSeq,    document.Chg.toAlphaSeq,    document.Chg.operAlphaSeq, 'A') ";
	print "\n    && editFromToOper(document.Chg.frClass,       document.Chg.toClass,       document.Chg.operClass, 'A') ";
	print "\n    && editFromToOper(document.Chg.frRegion,      document.Chg.toRegion,      document.Chg.operRegion, 'A') ";
	print "\n    && editFromToOper(document.Chg.frCollector,   document.Chg.toCollector,   document.Chg.operCollector, 'A') ";
	print "\n    && editdate(document.Chg.frResponseDate) ";
	print "\n    && editdate(document.Chg.toResponseDate) ";
	print "\n    && editFromToOper(document.Chg.frResponseDate,document.Chg.toResponseDate,document.Chg.operResponseDate, 'D') ";
	print "\n    && editdate(document.Chg.frCallDate) ";
	print "\n    && editdate(document.Chg.toCallDate) ";
	print "\n    && editFromToOper(document.Chg.frCallDate,    document.Chg.toCallDate,    document.Chg.operCallDate, 'D') ";
	print "\n    && editFromToOper(document.Chg.frUserID,      document.Chg.toUserID,      document.Chg.operUserID, 'A') ";
	print "\n    && editNum(document.Chg.frLocation, 3, 0) ";
	print "\n    && editNum(document.Chg.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frLocation,    document.Chg.toLocation,    document.Chg.operLocation, 3) ";
	print "\n    && editNum(document.Chg.frSalesman, 3, 0) ";
	print "\n    && editNum(document.Chg.toSalesman, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frSalesman,    document.Chg.toSalesman,    document.Chg.operSalesman, 3) ";
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
	$pageID = "ARCOLLECTIONSTATUSREPORT";
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
			$Err_FULL=DecatErr_Field("@@full", "PaidInFull");
			$Err_FULL=DecatErr_Field("@@pinv", "paidInvoice");
			$Err_FULL=DecatErr_Field("@@pmtd", "pmtDetail");

			if ($HDMCRL>0 && $CRPRMC=="Y") {
				$Err_IDCR=DecatErr_Field("@@idcr", "currencyID");
				$Err_CURT=DecatErr_Field("@@curt", "currencyType");
			}

			$Err_SSTC=DecatErr_Field("@@sstc", "sortStatusCode");
			$Err_SCUS=DecatErr_Field("@@scus", "sortCustomer");
			$Err_SCNM=DecatErr_Field("@@scnm", "sortCustName");
			$Err_SALP=DecatErr_Field("@@salp", "sortAlpha");
			$Err_SCLS=DecatErr_Field("@@scls", "sortClass");
			$Err_SRGN=DecatErr_Field("@@srgn", "sortRegion");
			$Err_SCLT=DecatErr_Field("@@sclt", "sortCollector");
			$Err_SDTR=DecatErr_Field("@@sdtr", "sortResponseDate");
			$Err_SDTE=DecatErr_Field("@@sdte", "sortCallDate");
			$Err_SUSR=DecatErr_Field("@@susr", "sortUserID");
			$Err_SLOC=DecatErr_Field("@@sloc", "sortLocation");
			$Err_SSLM=DecatErr_Field("@@sslm", "sortSalesman");
			$Err_SCUR=DecatErr_Field("@@scur", "sortCurrency");

			$Err_TSTC=DecatErr_Field("@@tstc", "totalStatusCode");
			$Err_TCUS=DecatErr_Field("@@tcus", "totalCustomer");
			$Err_TCNM=DecatErr_Field("@@tcnm", "totalCustName");
			$Err_TALP=DecatErr_Field("@@talp", "totalAlpha");
			$Err_TCLS=DecatErr_Field("@@tcls", "totalClass");
			$Err_TRGN=DecatErr_Field("@@trgn", "totalRegion");
			$Err_TCLT=DecatErr_Field("@@tclt", "totalCollector");
			$Err_TDTR=DecatErr_Field("@@tdtr", "totalResponseDate");
			$Err_TDTE=DecatErr_Field("@@tdte", "totalCallDate");
			$Err_TUSR=DecatErr_Field("@@tusr", "totalUserID");
			$Err_TLOC=DecatErr_Field("@@tloc", "totalLocation");
			$Err_TSLM=DecatErr_Field("@@tslm", "totalSalesman");
			$Err_TCUR=DecatErr_Field("@@tcur", "totalCurrency");

			$Err_PSTC=DecatErr_Field("@@pstc", "pageStatusCode");
			$Err_PCUS=DecatErr_Field("@@pcus", "pageCustomer");
			$Err_PCNM=DecatErr_Field("@@pcnm", "pageCustName");
			$Err_PALP=DecatErr_Field("@@palp", "pageAlpha");
			$Err_PCLS=DecatErr_Field("@@pcls", "pageClass");
			$Err_PRGN=DecatErr_Field("@@prgn", "pageRegion");
			$Err_PCLT=DecatErr_Field("@@pclt", "pageCollector");
			$Err_PDTR=DecatErr_Field("@@pdtr", "pageResponseDate");
			$Err_PDTE=DecatErr_Field("@@pdte", "pageCallDate");
			$Err_PUSR=DecatErr_Field("@@pusr", "pageUserID");
			$Err_PLOC=DecatErr_Field("@@ploc", "pageLocation");
			$Err_PSLM=DecatErr_Field("@@pslm", "pageSalesman");
			$Err_PCUR=DecatErr_Field("@@pcur", "pageCurrency");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$CMNT=Decat_Field("@@cmnt", $edtVar);
		$FULL=Decat_Field("@@full", $edtVar);
		$PINV=Decat_Field("@@pinv", $edtVar);
		$PMTD=Decat_Field("@@pmtd", $edtVar);

		if ($HDMCRL>0 && $CRPRMC=="Y") {
			$IDCR=Decat_Field("@@idcr", $edtVar);
			$CURT=Decat_Field("@@curt", $edtVar);
		}

		$SSTC=Decat_Field("@@sstc", $edtVar);
		$SCUS=Decat_Field("@@scus", $edtVar);
		$SCNM=Decat_Field("@@scnm", $edtVar);
		$SALP=Decat_Field("@@salp", $edtVar);
		$SCLS=Decat_Field("@@scls", $edtVar);
		$SRGN=Decat_Field("@@srgn", $edtVar);
		$SCLT=Decat_Field("@@sclt", $edtVar);
		$SDTR=Decat_Field("@@sdtr", $edtVar);
		$SDTE=Decat_Field("@@sdte", $edtVar);
		$SUSR=Decat_Field("@@susr", $edtVar);
		$SLOC=Decat_Field("@@sloc", $edtVar);
		$SSLM=Decat_Field("@@sslm", $edtVar);
		$SCUR=Decat_Field("@@scur", $edtVar);

		$TSTC=Decat_Field("@@tstc", $edtVar);
		$TCUS=Decat_Field("@@tcus", $edtVar);
		$TCNM=Decat_Field("@@tcnm", $edtVar);
		$TALP=Decat_Field("@@talp", $edtVar);
		$TCLS=Decat_Field("@@tcls", $edtVar);
		$TRGN=Decat_Field("@@trgn", $edtVar);
		$TCLT=Decat_Field("@@tclt", $edtVar);
		$TDTR=Decat_Field("@@tdtr", $edtVar);
		$TDTE=Decat_Field("@@tdte", $edtVar);
		$TUSR=Decat_Field("@@tusr", $edtVar);
		$TLOC=Decat_Field("@@tloc", $edtVar);
		$TSLM=Decat_Field("@@tslm", $edtVar);
		$TCUR=Decat_Field("@@tcur", $edtVar);

		$PSTC=Decat_Field("@@pstc", $edtVar);
		$PCUS=Decat_Field("@@pcus", $edtVar);
		$PCNM=Decat_Field("@@pcnm", $edtVar);
		$PALP=Decat_Field("@@palp", $edtVar);
		$PCLS=Decat_Field("@@pcls", $edtVar);
		$PRGN=Decat_Field("@@prgn", $edtVar);
		$PCLT=Decat_Field("@@pclt", $edtVar);
		$PDTR=Decat_Field("@@pdtr", $edtVar);
		$PDTE=Decat_Field("@@pdte", $edtVar);
		$PUSR=Decat_Field("@@pusr", $edtVar);
		$PLOC=Decat_Field("@@ploc", $edtVar);
		$PSLM=Decat_Field("@@pslm", $edtVar);
		$PCUR=Decat_Field("@@pcur", $edtVar);
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$CMNT="Y";
		$FULL="N";
		$PINV="N";
		$PMTD="Y";

		$IDCR="I";
		$CURT="";

		$TSTC="N";
		$TCUS="N";
		$TCNM="N";
		$TALP="N";
		$TCLS="N";
		$TRGN="N";
		$TCLT="N";
		$TDTR="N";
		$TDTE="N";
		$TUSR="N";
		$TLOC="N";
		$TSLM="N";
		$TCUR="N";

		$PSTC="N";
		$PCUS="N";
		$PCNM="N";
		$PALP="N";
		$PCLS="N";
		$PRGN="N";
		$PCLT="N";
		$PDTR="N";
		$PDTE="N";
		$PUSR="N";
		$PLOC="N";
		$PSLM="N";
		$PCUR="N";
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#PrintOption\">Print Option</a></td> ";
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
	Build_Fld_Entry("Include Calls Paid In Full","PaidInFull","inputalph","YORN","FULL",$FULL,$Err_FULL,"1","1","","","");
	Build_Fld_Entry("Include Invoices Paid In Full","paidInvoice","inputalph","YORN","PINV",$PINV,$Err_PINV,"1","1","","","");
	Build_Fld_Entry("Include Payment Detail","pmtDetail","inputalph","YORN","PMTD",$PMTD,$Err_PMTD,"1","1","","","");
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

	$operNbr = "operStatusCode";
	print "\n <tr><td class=\"dsphdr\">Collection Status</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frStatusCode\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ARCollectionStatusSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frStatusCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toStatusCode\" size=\"2\" maxlength=\"2\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}ARCollectionStatusSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toStatusCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
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

	$operNbr = "operCollector";
	print "\n <tr><td class=\"dsphdr\">Collector</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frCollector\" size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=frCollector&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toCollector\" size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=toCollector&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Response Date","frResponseDate","toResponseDate","operResponseDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Call Date","frCallDate","toCallDate","operCallDate","opersel_num2_short","D","6","6");

	$operNbr = "operUserID";
	print "\n <tr><td class=\"dsphdr\">User ID</td>";
	print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"frUserID\" size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=frUserID&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"toUserID\" size=\"10\" maxlength=\"10\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=toUserID&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n </tr>";

	$operNbr = "operLocation";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toLocation&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	$operNbr = "operSalesman";
	print "\n <tr><td class=\"dsphdr\">Salesman</td>";
	print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=frSalesman&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=toSalesman&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
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

	if ($HDMCRL>0 && $CRPRMC=="Y") {$maxSequence=13;}
	else                           {$maxSequence=12;}

	$textOvr=SetTextOvr($Err_SSTC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Collection Status</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortStatusCode",$SSTC);
	Build_Fld_Entry("","totalStatusCode","inputcode","YORN","TSTC",$TSTC,$Err_TSTC,"1","1","","","Y");
	Build_Fld_Entry("","pageStatusCode","inputcode","YORN","PSTC",$PSTC,$Err_PSTC,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SSTC);

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

	$textOvr=SetTextOvr($Err_SCLS);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Class</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortClass",$SCLS);
	Build_Fld_Entry("","totalClass","inputcode","YORN","TCLS",$TCLS,$Err_TCLS,"1","1","","","Y");
	Build_Fld_Entry("","pageClass","inputcode","YORN","PCLS",$PCLS,$Err_PCLS,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SCLS);

	$textOvr=SetTextOvr($Err_SRGN);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Region</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortRegion",$SRGN);
	Build_Fld_Entry("","totalRegion","inputcode","YORN","TRGN",$TRGN,$Err_TRGN,"1","1","","","Y");
	Build_Fld_Entry("","pageRegion","inputcode","YORN","PRGN",$PRGN,$Err_PRGN,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SRGN);

	$textOvr=SetTextOvr($Err_SCLT);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Collector</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortCollector",$SCLT);
	Build_Fld_Entry("","totalCollector","inputcode","YORN","TCLT",$TCLT,$Err_TCLT,"1","1","","","Y");
	Build_Fld_Entry("","pageCollector","inputcode","YORN","PCLT",$PCLT,$Err_PCLT,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SCLT);

	$textOvr=SetTextOvr($Err_SDTR);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Response Date</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortResponseDate",$SDTR);
	Build_Fld_Entry("","totalResponseDate","inputcode","YORN","TDTR",$TDTR,$Err_TDTR,"1","1","","","Y");
	Build_Fld_Entry("","pageResponseDate","inputcode","YORN","PDTR",$PDTR,$Err_PDTR,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SDTR);

	$textOvr=SetTextOvr($Err_SDTE);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Call Date</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortCallDate",$SDTE);
	Build_Fld_Entry("","totalCallDate","inputcode","YORN","TDTE",$TDTE,$Err_TDTE,"1","1","","","Y");
	Build_Fld_Entry("","pageCallDate","inputcode","YORN","PDTE",$PDTE,$Err_PDTE,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SDTE);

	$textOvr=SetTextOvr($Err_SUSR);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>User ID</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortUserID",$SUSR);
	Build_Fld_Entry("","totalUserID","inputcode","YORN","TUSR",$TUSR,$Err_TUSR,"1","1","","","Y");
	Build_Fld_Entry("","pageUserID","inputcode","YORN","PUSR",$PUSR,$Err_PUSR,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SUSR);

	$textOvr=SetTextOvr($Err_SLOC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Location</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortLocation",$SLOC);
	Build_Fld_Entry("","totalLocation","inputcode","YORN","TLOC",$TLOC,$Err_TLOC,"1","1","","","Y");
	Build_Fld_Entry("","pageLocation","inputcode","YORN","PLOC",$PLOC,$Err_PLOC,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SLOC);

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

	$returnValue=Range_WildCard("CLSTCD", "Collection Status", $_POST['frStatusCode'], $_POST['toStatusCode'], "U", $_POST['operStatusCode'], "A");
	$returnValue=Range_WildCard("CLCUST", "Customer", $_POST['frCustomer'], $_POST['toCustomer'], "", $_POST['operCustomer'], "N");
	$returnValue=Range_WildCard("CMCNA1U", "Customer Name", $_POST['frCustName'], $_POST['toCustName'], "U", $_POST['operCustName'], "A");
	$returnValue=Range_WildCard("CMALPH", "Alpha Sequence", $_POST['frAlphaSeq'], $_POST['toAlphaSeq'], "U", $_POST['operAlphaSeq'], "A");
	$returnValue=Range_WildCard("CMCCLS", "Customer Class", $_POST['frClass'], $_POST['toClass'], "U", $_POST['operClass'], "A");
	$returnValue=Range_WildCard("CMCRGN", "Customer Region", $_POST['frRegion'], $_POST['toRegion'], "U", $_POST['operRegion'], "A");
	$returnValue=Range_WildCard("CMCLCT", "Collector", $_POST['frCollector'], $_POST['toCollector'], "U", $_POST['operCollector'], "A");
	$returnValue=Range_WildCard("CLDTAR", "Response Date", $_POST['frResponseDate'], $_POST['toResponseDate'], "", $_POST['operResponseDate'], "D");
	$returnValue=Range_WildCard("CLDTEN", "Call Date", $_POST['frCallDate'], $_POST['toCallDate'], "", $_POST['operCallDate'], "D");
	$returnValue=Range_WildCard("CLUSER", "User ID", $_POST['frUserID'], $_POST['toUserID'], "U", $_POST['operUserID'], "A");
	$returnValue=Range_WildCard("Coalesce(IVLOC,0)", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Range_WildCard("Coalesce(IVSLSM,0)", "Salesman", $_POST['frSalesman'], $_POST['toSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Range_WildCard("Coalesce(IVCURT,' ')", "Currency Type", $_POST['frCurType'], $_POST['toCurType'], "U", $_POST['operCurType'], "A");
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);
	if (!isset($_POST['PrintComment'])) {$_POST['PrintComment']="N";}  Concat_Field("@@cmnt", $_POST['PrintComment']);
	if (!isset($_POST['PaidInFull'])) {$_POST['PaidInFull']="N";}  Concat_Field("@@full", $_POST['PaidInFull']);
	if (!isset($_POST['paidInvoice'])) {$_POST['paidInvoice']="N";}  Concat_Field("@@pinv", $_POST['paidInvoice']);
	if (!isset($_POST['pmtDetail'])) {$_POST['pmtDetail']="N";}  Concat_Field("@@pmtd", $_POST['pmtDetail']);

	Concat_Field("@@idcr", strtoupper($_POST['currencyID']));
	Concat_Field("@@curt", strtoupper($_POST['currencyType']));

	Concat_Field("@@sstc", $_POST['sortStatusCode']);
	if (!isset($_POST['totalStatusCode'])) {$_POST['totalStatusCode']="N";}  Concat_Field("@@tstc", $_POST['totalStatusCode']);
	if (!isset($_POST['pageStatusCode'])) {$_POST['pageStatusCode']="N";}  Concat_Field("@@pstc", $_POST['pageStatusCode']);

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

	Concat_Field("@@sclt", $_POST['sortCollector']);
	if (!isset($_POST['totalCollector'])) {$_POST['totalCollector']="N";}  Concat_Field("@@tclt", $_POST['totalCollector']);
	if (!isset($_POST['pageCollector'])) {$_POST['pageCollector']="N";}  Concat_Field("@@pclt", $_POST['pageCollector']);

	Concat_Field("@@sdtr", $_POST['sortResponseDate']);
	if (!isset($_POST['totalResponseDate'])) {$_POST['totalResponseDate']="N";}  Concat_Field("@@tdtr", $_POST['totalResponseDate']);
	if (!isset($_POST['pageResponseDate'])) {$_POST['pageResponseDate']="N";}  Concat_Field("@@pdtr", $_POST['pageResponseDate']);

	Concat_Field("@@sdte", $_POST['sortCallDate']);
	if (!isset($_POST['totalCallDate'])) {$_POST['totalCallDate']="N";}  Concat_Field("@@tdte", $_POST['totalCallDate']);
	if (!isset($_POST['pageCallDate'])) {$_POST['pageCallDate']="N";}  Concat_Field("@@pdte", $_POST['pageCallDate']);

	Concat_Field("@@susr", $_POST['sortUserID']);
	if (!isset($_POST['totalUserID'])) {$_POST['totalUserID']="N";}  Concat_Field("@@tusr", $_POST['totalUserID']);
	if (!isset($_POST['pageUserID'])) {$_POST['pageUserID']="N";}  Concat_Field("@@pusr", $_POST['pageUserID']);

	Concat_Field("@@sloc", $_POST['sortLocation']);
	if (!isset($_POST['totalLocation'])) {$_POST['totalLocation']="N";}  Concat_Field("@@tloc", $_POST['totalLocation']);
	if (!isset($_POST['pageLocation'])) {$_POST['pageLocation']="N";}  Concat_Field("@@ploc", $_POST['pageLocation']);

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

	$returnValue=Selection_Edit_Handle("HARCRS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
  