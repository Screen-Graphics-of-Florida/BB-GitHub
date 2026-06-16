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

$page_title            = "Dunning Letter";
$scriptName            = "ARDunningLetter.php";
$scriptVarBase         = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$submitCallProgram     = "CARDUN";
$submitEnvProgram      = "CARDUN";
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
	print "\n   if (document.Chg.dunLetter.value ==\"\")";
	print "\n     {alert(\"$reqFieldError\"); return false;} ";
	print "\n   if (editNum(document.Chg.dunLetter, 4, 0) ";
	print "\n    && editNumPos(document.Chg.daysPastDue, 5, 0) ";
	print "\n    && editNumPos(document.Chg.frDayPast, 5, 0) ";
	print "\n    && editNumPos(document.Chg.toDayPast, 5, 0) ";
	print "\n    && editNum(document.Chg.frCustomer, 7, 0) ";
	print "\n    && editNum(document.Chg.toCustomer, 7, 0) ";
	print "\n    && editFromToOper(document.Chg.frCustomer,   document.Chg.toCustomer,   document.Chg.operCustomer, 7) ";
	print "\n    && editFromToOper(document.Chg.frCustName,   document.Chg.toCustName,   document.Chg.operCustName, 'A') ";
	print "\n    && editFromToOper(document.Chg.frAlphaSeq,   document.Chg.toAlphaSeq,   document.Chg.operAlphaSeq, 'A') ";
	print "\n    && editFromToOper(document.Chg.frClass,      document.Chg.toClass,      document.Chg.operClass, 'A') ";
	print "\n    && editFromToOper(document.Chg.frRegion,     document.Chg.toRegion,     document.Chg.operRegion, 'A') ";
	print "\n    && editFromToOper(document.Chg.frCollector,  document.Chg.toCollector,  document.Chg.operCollector, 'A') ";
	print "\n    && editNum(document.Chg.frLocation, 3, 0) ";
	print "\n    && editNum(document.Chg.toLocation, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frLocation,   document.Chg.toLocation,   document.Chg.operLocation, 3) ";
	print "\n    && editNum(document.Chg.frSalesman, 3, 0) ";
	print "\n    && editNum(document.Chg.toSalesman, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.frSalesman,   document.Chg.toSalesman,   document.Chg.operSalesman, 3) ";
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
	$pageID = "ARDUNNINGLETTER";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;

	$focusField="dunLetter";
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField="";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		if ($errFound != "") {
			$errVar=ErrVarErr($profileHandle, $errVar);

			$Err_LETR=DecatErr_Field("@@letr", "dunLetter");
			$Err_DAYD=DecatErr_Field("@@dayd", "daysPastDue");
			$Err_FDAY=DecatErr_Field("@@fday", "frDayPast");
			$Err_TDAY=DecatErr_Field("@@tday", "toDayPast");
			$Err_INVD=DecatErr_Field("@@invd", "invoiceDetail");
			$Err_CMTH=DecatErr_Field("@@cmth", "commentHeader");
			$Err_CMTT=DecatErr_Field("@@cmtt", "commentTrailer");

			$Err_BrkCus=DecatErr_Field("@@bcus", "breakCustomer");
			$Err_BrkNam=DecatErr_Field("@@bnam", "breakName");
			$Err_BrkAlp=DecatErr_Field("@@balp", "breakAlpha");
			$Err_BrkCls=DecatErr_Field("@@bcls", "breakClass");
			$Err_BrkRgn=DecatErr_Field("@@brgn", "breakRegion");
			$Err_BrkClt=DecatErr_Field("@@bclt", "breakCollector");
			$Err_BrkLoc=DecatErr_Field("@@bloc", "breakLocation");
			$Err_BrkSlm=DecatErr_Field("@@bslm", "breakSalesman");

			$Err_SrtInv=DecatErr_Field("@@sinv", "sortInvoice");
			$Err_SrtIvD=DecatErr_Field("@@sivd", "sortInvoiceDate");
			$Err_SrtDuD=DecatErr_Field("@@sdud", "sortDueDate");
			$Err_SrtRef=DecatErr_Field("@@sref", "sortReference");

			require 'ScheduleJobErr.php';    // Schedule Entries Errors
		}
		$submitSchedule=Decat_Field("@@sbjb", $edtVar);

		$LETR=Decat_Field("@@letr", $edtVar);
		$DAYD=Decat_Field("@@dayd", $edtVar);
		$FDAY=Decat_Field("@@fday", $edtVar);
		$TDAY=Decat_Field("@@tday", $edtVar);
		$INVD=Decat_Field("@@invd", $edtVar);
		$CMTH=Decat_Field("@@cmth", $edtVar);
		$CMTT=Decat_Field("@@cmtt", $edtVar);

		$BrkCus=Decat_Field("@@bcus", $edtVar);
		$BrkAlp=Decat_Field("@@balp", $edtVar);
		$BrkNam=Decat_Field("@@bnam", $edtVar);
		$BrkCls=Decat_Field("@@bcls", $edtVar);
		$BrkRgn=Decat_Field("@@brgn", $edtVar);
		$BrkClt=Decat_Field("@@bclt", $edtVar);
		$BrkSlm=Decat_Field("@@bslm", $edtVar);
		$BrkLoc=Decat_Field("@@bloc", $edtVar);

		$SrtInv=Decat_Field("@@sinv", $edtVar);
		$SrtIvD=Decat_Field("@@sivd", $edtVar);
		$SrtDuD=Decat_Field("@@sdud", $edtVar);
		$SrtRef=Decat_Field("@@sref", $edtVar);

		require 'ScheduleJobValue.php';  // Schedule Entries Values
	} else {
		$INVD="N";
		$CMTH="Y";
		$CMTT="Y";
		$BrkCus=1;
		if ($CRLPRI=="I") {$BrkLoc=2;}
		if ($CRBAON=="D") {$SrtDuD=1;}
		else              {$SrtIvD=1;}
		$SrtInv=2;
	}

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#PrintOption\">Print Option</a></td> ";
	if ($wildCardDisplay != "") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#CurrentFilterCriteria\">Current Filter Criteria</a></td> ";}
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#RefineFilterCriteria\">Refine Filter Criteria</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#Documentbreak\">Document Break</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#Detailsort\">Detail Sort</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	print $hrTagAttr;

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

	print "\n <a name=\"PrintOption\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Print Option</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	// Letter
	$fieldDesc=RetValue("DHDLLT=$LETR", "ARLETH", "DHDESC");
	$textOvr=SetTextOvr($Err_LETR);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Letter Number</span></td>";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"dunLetter\" value=\"" . rtrim($LETR) . "\" size=\"4\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}ARDunningLetterSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=dunLetter&amp;fldDesc=dunLetterDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"dunLetterDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_LETR);

	Build_Fld_Entry("Days Past Due","daysPastDue","inputnmbr","","DayDue",$DAYD,$Err_DAYD,"5","5","","","");

	print "\n         <tr><td class=\"dsphdr\">Or</td></tr>";

	$textOvr=SetTextOvr($Err_FDAY);
	if ($textOvr == "") {$textOvr=SetTextOvr($Err_TDAY); }
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Range Of Days</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frDayPast\" value=\"$FDAY\" size=\"5\" maxlength=\"5\"> &nbsp; ";
	print "\n                                     <input type=\"text\" name=\"toDayPast\" value=\"$TDAY\" size=\"5\" maxlength=\"5\"></td> ";
	DspErrMsg($Err_FDAY);
	DspErrMsg($Err_TDAY);

	Build_Fld_Entry("Print Invoice Detail","invoiceDetail","inputalph","YORN","INVD",$INVD,$Err_INVD,"1","1","","","");
	Build_Fld_Entry("Print Header Comment","commentHeader","inputalph","YORN","CMTH",$CMTH,$Err_CMTH,"1","1","","","");
	Build_Fld_Entry("Print Trailer Comment","commentTrailer","inputalph","YORN","CMTT",$CMTT,$Err_CMTT,"1","1","","","");
	print "\n     </table> ";
	print "\n </fieldset> ";


	require_once 'AdvSearchTopReport.php';

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

	print "\n <a name=\"Documentbreak\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Document Break</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";

	$maxSequence=8;

	$textOvr=SetTextOvr($Err_BrkCus);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("breakCustomer",$BrkCus);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkCus);

	$textOvr=SetTextOvr($Err_BrkNam);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Name</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("breakName",$BrkNam);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkNam);

	$textOvr=SetTextOvr($Err_BrkAlp);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Alpha Sequence</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("breakAlpha",$BrkAlp);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkAlp);

	$textOvr=SetTextOvr($Err_BrkCls);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Class</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("breakClass",$BrkCls);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkCls);

	$textOvr=SetTextOvr($Err_BrkRgn);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Customer Region</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("breakRegion",$BrkRgn);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkRgn);

	$textOvr=SetTextOvr($Err_BrkClt);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Collector</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("breakCollector",$BrkClt);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkClt);

	$textOvr=SetTextOvr($Err_BrkLoc);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Location</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("breakLocation",$BrkLoc);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkLoc);

	$textOvr=SetTextOvr($Err_BrkSlm);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Salesman</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("breakSalesman",$BrkSlm);
	print "\n         </tr> ";
	DspErrMsg($Err_BrkSlm);

	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"Detailsort\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Detail Sort</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";

	$maxSequence=4;

	$textOvr=SetTextOvr($Err_SrtInv);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Invoice</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("sortInvoice",$SrtInv);
	print "\n         </tr> ";
	DspErrMsg($Err_SrtInv);

	$textOvr=SetTextOvr($Err_SrtIvD);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Invoice Date</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("sortInvoiceDate",$SrtIvD);
	print "\n         </tr> ";
	DspErrMsg($Err_SrtIvD);

	$textOvr=SetTextOvr($Err_SrtDuD);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Due Date</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("sortDueDate",$SrtDuD);
	print "\n         </tr> ";
	DspErrMsg($Err_SrtDuD);

	$textOvr=SetTextOvr($Err_SrtRef);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Reference Number</span></td>";
	$sortSeqMax=$maxSequence; Build_sort_Select("sortReference",$SrtRef);
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

	$returnValue=Range_WildCard("CMCUST", "Customer", $_POST['frCustomer'], $_POST['toCustomer'], "", $_POST['operCustomer'], "N");
	$returnValue=Range_WildCard("CMCNA1U", "Customer Name", $_POST['frCustName'], $_POST['toCustName'], "U", $_POST['operCustName'], "A");
	$returnValue=Range_WildCard("CMALPH", "Alpha Sequence", $_POST['frAlphaSeq'], $_POST['toAlphaSeq'], "U", $_POST['operAlphaSeq'], "A");
	$returnValue=Range_WildCard("CMCCLS", "Customer Class", $_POST['frClass'], $_POST['toClass'], "U", $_POST['operClass'], "A");
	$returnValue=Range_WildCard("CMCRGN", "Customer Region", $_POST['frRegion'], $_POST['toRegion'], "U", $_POST['operRegion'], "A");
	$returnValue=Range_WildCard("CMCLCT", "Collector", $_POST['frCollector'], $_POST['toCollector'], "U", $_POST['operCollector'], "A");
	$returnValue=Range_WildCard("IVLOC", "Location", $_POST['frLocation'], $_POST['toLocation'], "", $_POST['operLocation'], "N");
	$returnValue=Range_WildCard("IVSLSM", "Salesman", $_POST['frSalesman'], $_POST['toSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Range_WildCard("IVCURT", "Currency Type", $_POST['frCurType'], $_POST['toCurType'], "U", $_POST['operCurType'], "A");
	require_once 'WildCardUpdateReport.php';

	$edtVar= "";
	Concat_Field("@@updt", $_POST['updateSearch']);
	Concat_Field("@@letr", $_POST['dunLetter']);
	Concat_Field("@@dayd", $_POST['daysPastDue']);
	Concat_Field("@@fday", $_POST['frDayPast']);
	Concat_Field("@@tday", $_POST['toDayPast']);
	if (!isset($_POST['invoiceDetail'])) {$_POST['invoiceDetail']="N";}  Concat_Field("@@invd", $_POST['invoiceDetail']);
	if (!isset($_POST['commentHeader'])) {$_POST['commentHeader']="N";}  Concat_Field("@@cmth", $_POST['commentHeader']);
	if (!isset($_POST['commentTrailer'])) {$_POST['commentTrailer']="N";}  Concat_Field("@@cmtt", $_POST['commentTrailer']);

	Concat_Field("@@bcus", $_POST['breakCustomer']);
	Concat_Field("@@bnam", $_POST['breakName']);
	Concat_Field("@@balp", $_POST['breakAlpha']);
	Concat_Field("@@bcls", $_POST['breakClass']);
	Concat_Field("@@brgn", $_POST['breakRegion']);
	Concat_Field("@@bclt", $_POST['breakCollector']);
	Concat_Field("@@bloc", $_POST['breakLocation']);
	Concat_Field("@@bslm", $_POST['breakSalesman']);

	Concat_Field("@@sinv", $_POST['sortInvoice']);
	Concat_Field("@@sivd", $_POST['sortInvoiceDate']);
	Concat_Field("@@sdud", $_POST['sortDueDate']);
	Concat_Field("@@sref", $_POST['sortReference']);

	if (strtoupper(substr(trim($wildCardSearch),0,3))=="AND") {$wildCardSearch=substr(trim($wildCardSearch),3);} // remove "and"
	Concat_Field("@@filv", $wildCardSearch);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay); Concat_Field("@@fild", $wildCardDisplay);

	require 'ScheduleJobConcat.php';   // Schedule Entries Values
	$edtVar .= "}{";

	$returnValue=Selection_Edit_Handle("HARSDN_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];

	require 'SubmitScheduleUpdate.php';
	exit;
}
