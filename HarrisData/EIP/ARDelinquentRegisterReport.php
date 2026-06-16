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

$page_title            = "A/R Delinquent Register Report";
$scriptName            = "ARDelinquentRegisterReport.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CARDEQ";
$submitEnvProgram      = "HARDEQ";
$submitEnvPrinter      = "HARDEQPF";
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
	print "\n   if (document.Chg.onInvoice.checked == false && ";
	print "\n       document.Chg.onCredit.checked == false && ";
	print "\n       document.Chg.deduction.checked == false && ";
	print "\n       document.Chg.serviceCharge.checked == false && ";
	print "\n       document.Chg.nsf.checked == false && ";
	print "\n       document.Chg.unappliedCash.checked == false) ";
	print "\n       {alert(\"Must make at least one Invoice Code selection\"); return false;} ";

	print "\n   if (editNumPos(document.Chg.CurrencyDue, 13, 2) ";
	print "\n    && editNumPos(document.Chg.DaysPastDue, 5, 0) ";
	print "\n    && editNum(document.Chg.frLocation, 3, 0) ";
	print "\n    && editNum(document.Chg.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frLocation,  document.Chg.toLocation,  document.Chg.operLocation, 3) ";
	print "\n    && editNum(document.Chg.frCustomer, 7, 0) ";
	print "\n    && editNum(document.Chg.toCustomer, 7, 0) ";
	print "\n    && editFromToOper(document.Chg.frCustomer,  document.Chg.toCustomer,  document.Chg.operCustomer, 7) ";
	print "\n    && editFromToOper(document.Chg.frCustName,  document.Chg.toCustName,  document.Chg.operCustName, 'A') ";
	print "\n    && editFromToOper(document.Chg.frAlphaSeq,  document.Chg.toAlphaSeq,  document.Chg.operAlphaSeq, 'A') ";
	print "\n    && editFromToOper(document.Chg.frClass,     document.Chg.toClass,     document.Chg.operClass, 'A') ";
	print "\n    && editFromToOper(document.Chg.frRegion,    document.Chg.toRegion,    document.Chg.operRegion, 'A') ";
	print "\n    && editFromToOper(document.Chg.frCollector, document.Chg.toCollector, document.Chg.operCollector, 'A') ";
	print "\n    && editNum(document.Chg.frSalesman, 3, 0) ";
	print "\n    && editNum(document.Chg.toSalesman, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frSalesman,  document.Chg.toSalesman,  document.Chg.operSalesman, 3) ";
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
	$pageID = "ARDELINQUENTREGISTERREPORT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="DelinquentInvoice";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			$Err_DELQ=DecatErr_Field("@@delq", "DelinquentInvoice");
			$Err_CRLM=DecatErr_Field("@@crlm", "CreditLimit");
			$Err_DDUE=DecatErr_Field("@@ddue", "DaysPastDue");
			$Err_CDUE=DecatErr_Field("@@cdue", "CurrencyDue");

			$Err_ONI=DecatErr_Field("@@oni@", "onInvoice");
			$Err_ONR=DecatErr_Field("@@onr@", "onCredit");
			$Err_DED=DecatErr_Field("@@ded@", "deduction");
			$Err_SVG=DecatErr_Field("@@svg@", "serviceCharge");
			$Err_NSF=DecatErr_Field("@@nsf@", "nsf");
			$Err_UNA=DecatErr_Field("@@una@", "unappliedCash");

			if ($HDMCRL>0 && $CRPRMC=="Y") {
				$Err_IDCR=DecatErr_Field("@@idcr", "currencyID");
				$Err_CURT=DecatErr_Field("@@curt", "currencyType");
			}

			$Err_SLOC=DecatErr_Field("@@sloc", "sortLocation");
			$Err_SCUS=DecatErr_Field("@@scus", "sortCustomer");
			$Err_SCNM=DecatErr_Field("@@scnm", "sortCustName");
			$Err_SALP=DecatErr_Field("@@salp", "sortAlpha");
			$Err_SCLS=DecatErr_Field("@@scls", "sortClass");
			$Err_SRGN=DecatErr_Field("@@srgn", "sortRegion");
			$Err_SCLT=DecatErr_Field("@@sclt", "sortCollector");
			$Err_SINV=DecatErr_Field("@@sinv", "sortInvoice");
			$Err_SDTE=DecatErr_Field("@@sdte", "sortDate");
			$Err_SSLM=DecatErr_Field("@@sslm", "sortSalesman");
			$Err_SCUR=DecatErr_Field("@@scur", "sortCurrency");

			$Err_TLOC=DecatErr_Field("@@tloc", "totalLocation");
			$Err_TCUS=DecatErr_Field("@@tcus", "totalCustomer");
			$Err_TCNM=DecatErr_Field("@@tcnm", "totalCustName");
			$Err_TALP=DecatErr_Field("@@talp", "totalAlpha");
			$Err_TCLS=DecatErr_Field("@@tcls", "totalClass");
			$Err_TRGN=DecatErr_Field("@@trgn", "totalRegion");
			$Err_TCLT=DecatErr_Field("@@tclt", "totalCollector");
			$Err_TINV=DecatErr_Field("@@tinv", "totalInvoice");
			$Err_TDTE=DecatErr_Field("@@tdte", "totalDate");
			$Err_TSLM=DecatErr_Field("@@tslm", "totalSalesman");
			$Err_TCUR=DecatErr_Field("@@tcur", "totalCurrency");

			$Err_PLOC=DecatErr_Field("@@ploc", "pageLocation");
			$Err_PCUS=DecatErr_Field("@@pcus", "pageCustomer");
			$Err_PCNM=DecatErr_Field("@@pcnm", "pageCustName");
			$Err_PALP=DecatErr_Field("@@palp", "pageAlpha");
			$Err_PCLS=DecatErr_Field("@@pcls", "pageClass");
			$Err_PRGN=DecatErr_Field("@@prgn", "pageRegion");
			$Err_PCLT=DecatErr_Field("@@pclt", "pageCollector");
			$Err_PINV=DecatErr_Field("@@pinv", "pageInvoice");
			$Err_PDTE=DecatErr_Field("@@pdte", "pageDate");
			$Err_PSLM=DecatErr_Field("@@pslm", "pageSalesman");
			$Err_PCUR=DecatErr_Field("@@pcur", "pageCurrency");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);
		$DELQ=Decat_Field("@@delq", $edtVar);
		$CRLM=Decat_Field("@@crlm", $edtVar);
		$DDUE=Decat_Field("@@ddue", $edtVar);
		$CDUE=Decat_Field("@@cdue", $edtVar);

		$ONI=Decat_Field("@@oni@", $edtVar);
		$ONR=Decat_Field("@@onr@", $edtVar);
		$DED=Decat_Field("@@ded@", $edtVar);
		$SVG=Decat_Field("@@svg@", $edtVar);
		$NSF=Decat_Field("@@nsf@", $edtVar);
		$UNA=Decat_Field("@@una@", $edtVar);

		if ($HDMCRL>0 && $CRPRMC=="Y") {
			$IDCR=Decat_Field("@@idcr", $edtVar);
			$CURT=Decat_Field("@@curt", $edtVar);
		}

		$SLOC=Decat_Field("@@sloc", $edtVar);
		$SCUS=Decat_Field("@@scus", $edtVar);
		$SCNM=Decat_Field("@@scnm", $edtVar);
		$SALP=Decat_Field("@@salp", $edtVar);
		$SCLS=Decat_Field("@@scls", $edtVar);
		$SRGN=Decat_Field("@@srgn", $edtVar);
		$SCLT=Decat_Field("@@sclt", $edtVar);
		$SINV=Decat_Field("@@sinv", $edtVar);
		$SDTE=Decat_Field("@@sdte", $edtVar);
		$SSLM=Decat_Field("@@sslm", $edtVar);
		$SCUR=Decat_Field("@@scur", $edtVar);

		$TLOC=Decat_Field("@@tloc", $edtVar);
		$TCUS=Decat_Field("@@tcus", $edtVar);
		$TCNM=Decat_Field("@@tcnm", $edtVar);
		$TALP=Decat_Field("@@talp", $edtVar);
		$TCLS=Decat_Field("@@tcls", $edtVar);
		$TRGN=Decat_Field("@@trgn", $edtVar);
		$TCLT=Decat_Field("@@tclt", $edtVar);
		$TINV=Decat_Field("@@tinv", $edtVar);
		$TDTE=Decat_Field("@@tdte", $edtVar);
		$TSLM=Decat_Field("@@tslm", $edtVar);
		$TCUR=Decat_Field("@@tcur", $edtVar);

		$PLOC=Decat_Field("@@ploc", $edtVar);
		$PCUS=Decat_Field("@@pcus", $edtVar);
		$PCNM=Decat_Field("@@pcnm", $edtVar);
		$PALP=Decat_Field("@@palp", $edtVar);
		$PCLS=Decat_Field("@@pcls", $edtVar);
		$PRGN=Decat_Field("@@prgn", $edtVar);
		$PCLT=Decat_Field("@@pclt", $edtVar);
		$PINV=Decat_Field("@@pinv", $edtVar);
		$PDTE=Decat_Field("@@pdte", $edtVar);
		$PSLM=Decat_Field("@@pslm", $edtVar);
		$PCUR=Decat_Field("@@pcur", $edtVar);
		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$IORD=$CRBAON;
		$DELQ="N";
		$CRLM="N";
		$DDUE="";
		$CDUE="";

		$IDCR="I";
		$CURT="";

		$ONI="Y";
		$ONR="Y";
		$DED="Y";
		$SVG="Y";
		$NSF="Y";
		$UNA="Y";

		$TLOC="N";
		$TCUS="N";
		$TCNM="N";
		$TALP="N";
		$TCLS="N";
		$TRGN="N";
		$TCLT="N";
		$TINV="N";
		$TDTE="N";
		$TSLM="N";
		$TCUR="N";

		$PLOC="N";
		$PCUS="N";
		$PCNM="N";
		$PALP="N";
		$PCLS="N";
		$PRGN="N";
		$PCLT="N";
		$PINV="N";
		$PDTE="N";
		$PSLM="N";
		$PCUR="N";
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#PrintOption\">Print Option</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#CustomerCriteria\">Customer Criteria</a></td> ";
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
	Build_Fld_Entry("Include Only Delinquent Invoices","DelinquentInvoice","inputalph","YORN","DELQ",$DELQ,$Err_DELQ,"1","1","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"CustomerCriteria\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Customer Criteria</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Over Credit Limit","CreditLimit","inputalph","YORN","CRLM",$CRLM,$Err_CRLM,"1","1","","","");

	$textOvr=SetTextOvr($Err_CDUE);
	print "\n     	<tr><td class=\"dsphdr\"><span $textOvr>Currency Due</span></td> ";
	print "\n           <td class=\"inputnmbr\"><input type=\"text\" name=\"CurrencyDue\" value=\"$CDUE\" size=\"17\" maxlength=\"17\"></td> ";
	print "\n     	</tr> ";
	DspErrMsg($Err_CDUE);

	$textOvr=SetTextOvr($Err_DDUE);
	print "\n     	<tr><td class=\"dsphdr\"><span $textOvr>Days Past Due</span></td> ";
	print "\n           <td class=\"inputnmbr\"><input type=\"text\" name=\"DaysPastDue\" value=\"$DDUE\" size=\"5\" maxlength=\"5\"></td> ";
	print "\n     	</tr> ";
	DspErrMsg($Err_DDUE);

	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"InvoiceCode\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Invoice Code</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	Build_Fld_Entry("Invoice","onInvoice","inputalph","YORN","ONI",$ONI,$Err_ONI,"1","1","","","");
	Build_Fld_Entry("Credit Memo","onCredit","inputalph","YORN","ONR",$ONR,$Err_ONR,"1","1","","","");
	Build_Fld_Entry("Deduction","deduction","inputalph","YORN","DED",$DED,$Err_DED,"1","1","","","");
	Build_Fld_Entry("Service Charge","serviceCharge","inputalph","YORN","SVG",$SVG,$Err_SVG,"1","1","","","");
	Build_Fld_Entry("NSF","nsf","inputalph","YORN","NSF",$NSF,$Err_NSF,"1","1","","","");
	Build_Fld_Entry("Unapplied Cash","unappliedCash","inputalph","YORN","UNA",$UNA,$Err_UNA,"1","1","","","");
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

	if ($HDMCRL>0 && $CRPRMC=="Y") {$maxSequence=11;}
	else                           {$maxSequence=10;}

	$textOvr=SetTextOvr($Err_SLOC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Location</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortLocation",$SLOC);
	Build_Fld_Entry("","totalLocation","inputcode","YORN","TLOC",$TLOC,$Err_TLOC,"1","1","","","Y");
	Build_Fld_Entry("","pageLocation","inputcode","YORN","PLOC",$PLOC,$Err_PLOC,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SLOC);

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

	$textOvr=SetTextOvr($Err_SINV);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Invoice</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortInvoice",$SINV);
	Build_Fld_Entry("","totalInvoice","inputcode","YORN","TINV",$TINV,$Err_TINV,"1","1","","","Y");
	Build_Fld_Entry("","pageInvoice","inputcode","YORN","PINV",$PINV,$Err_PINV,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SINV);

	$textOvr=SetTextOvr($Err_SDTE);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Due Date</span></td>";
	$sortSeqMax=$maxSequence; Build_Sort_Select("sortDate",$SDTE);
	Build_Fld_Entry("","totalDate","inputcode","YORN","TDTE",$TDTE,$Err_TDTE,"1","1","","","Y");
	Build_Fld_Entry("","pageDate","inputcode","YORN","PDTE",$PDTE,$Err_PDTE,"1","1","","","Y");
	print "\n         </tr> ";
	DspErrMsg($Err_SDTE);

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

	$returnValue=Range_WildCard("IVLOC", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Range_WildCard("IVBLTO", "Customer", $_POST['frCustomer'], $_POST['toCustomer'], "", $_POST['operCustomer'], "N");
	$returnValue=Range_WildCard("CMCNA1U", "Customer Name", $_POST['frCustName'], $_POST['toCustName'], "U", $_POST['operCustName'], "A");
	$returnValue=Range_WildCard("CMALPH", "Alpha Sequence", $_POST['frAlphaSeq'], $_POST['toAlphaSeq'], "U", $_POST['operAlphaSeq'], "A");
	$returnValue=Range_WildCard("CMCCLS", "Customer Class", $_POST['frClass'], $_POST['toClass'], "U", $_POST['operClass'], "A");
	$returnValue=Range_WildCard("CMCRGN", "Customer Region", $_POST['frRegion'], $_POST['toRegion'], "U", $_POST['operRegion'], "A");
	$returnValue=Range_WildCard("CMCLCT", "Collector", $_POST['frCollector'], $_POST['toCollector'], "U", $_POST['operCollector'], "A");
	$returnValue=Range_WildCard("IVSLSM", "Salesman", $_POST['frSalesman'], $_POST['toSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Range_WildCard("IVCURT", "Currency Type", $_POST['frCurType'], $_POST['toCurType'], "U", $_POST['operCurType'], "A");
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);
	if (!isset($_POST['DelinquentInvoice'])) {$_POST['DelinquentInvoice']="N";}  Concat_Field("@@delq", $_POST['DelinquentInvoice']);

	if (!isset($_POST['CreditLimit'])) {$_POST['CreditLimit']="N";}  Concat_Field("@@crlm", $_POST['CreditLimit']);
	Concat_Field("@@ddue", $_POST['DaysPastDue']);
	Concat_Field("@@cdue", $_POST['CurrencyDue']);

	if (!isset($_POST['onInvoice'])) {$_POST['onInvoice']="N";}  Concat_Field("@@oni@", $_POST['onInvoice']);
	if (!isset($_POST['onCredit'])) {$_POST['onCredit']="N";}  Concat_Field("@@onr@", $_POST['onCredit']);
	if (!isset($_POST['deduction'])) {$_POST['deduction']="N";}  Concat_Field("@@ded@", $_POST['deduction']);
	if (!isset($_POST['serviceCharge'])) {$_POST['serviceCharge']="N";}  Concat_Field("@@svg@", $_POST['serviceCharge']);
	if (!isset($_POST['nsf'])) {$_POST['nsf']="N";}  Concat_Field("@@nsf@", $_POST['nsf']);
	if (!isset($_POST['unappliedCash'])) {$_POST['unappliedCash']="N";}  Concat_Field("@@una@", $_POST['unappliedCash']);

	Concat_Field("@@idcr", strtoupper($_POST['currencyID']));
	Concat_Field("@@curt", strtoupper($_POST['currencyType']));

	Concat_Field("@@sloc", $_POST['sortLocation']);
	if (!isset($_POST['totalLocation'])) {$_POST['totalLocation']="N";}  Concat_Field("@@tloc", $_POST['totalLocation']);
	if (!isset($_POST['pageLocation'])) {$_POST['pageLocation']="N";}  Concat_Field("@@ploc", $_POST['pageLocation']);

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

	Concat_Field("@@sinv", $_POST['sortInvoice']);
	if (!isset($_POST['totalInvoice'])) {$_POST['totalInvoice']="N";}  Concat_Field("@@tinv", $_POST['totalInvoice']);
	if (!isset($_POST['pageInvoice'])) {$_POST['pageInvoice']="N";}  Concat_Field("@@pinv", $_POST['pageInvoice']);

	Concat_Field("@@sdte", $_POST['sortDate']);
	if (!isset($_POST['totalDate'])) {$_POST['totalDate']="N";}  Concat_Field("@@tdte", $_POST['totalDate']);
	if (!isset($_POST['pageDate'])) {$_POST['pageDate']="N";}  Concat_Field("@@pdte", $_POST['pageDate']);

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

	$returnValue=Selection_Edit_Handle("HARDQE_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}

?>	
