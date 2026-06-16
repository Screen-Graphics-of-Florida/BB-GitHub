<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$WFReview           = $_GET['WFReview'];
$wfInstance         = $_GET['wfInstance'];
$wfInstanceDate     = $_GET['wfInstanceDate'];
$wfWorkItem         = $_GET['wfWorkItem'];
$wfWorkItemSequence = $_GET['wfWorkItemSequence'];
$wfParticipantId    = $_GET['wfParticipantId'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$CRPERN=RetValue("RRN(ARCTRL)=1", "ARCTRL", "CRDPER");
$ARPdBegDate=RetValue("PDPER#=$CRPERN", "HDPBED", "PDBDAT");

$page_title    = "Application of Cash: Batch";
$scriptName    = "ApplCashBatch.php";
$scriptVarBase = "{$genericVarBase}&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$altScriptVarBase = "{$altVarBase}&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("BMBCHN","A","Batch"));
$programName   = "HARABH_E";
$attachFolder  = "ApplCashBatch";

if ($wfInstance == 0) {
	$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
	$viewCheckBoxDef = array(array("View:", "Closed", $viewCheckBoxURL, "1", "0", "BMBCHS='C' and BMPPMT<>'Y' and BMBCHD>=$ARPdBegDate"),
	array("", "Entry", $viewCheckBoxURL, "2", "1", "(BMBCHS in ('O','R') or BMPPMT='Y')"),
	array("", "Prior Period", $viewCheckBoxURL, "4", "0", "BMBCHD<$ARPdBegDate"),
	array("", "With Variance", $viewCheckBoxURL, "5", "0", "BMDEPA-BMDEPP-BMDEPE<>0"));
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	$fromToSearch = "Y";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.$formName.frBatch, 4, 0) ";
	print "\n    && editNum(document.$formName.toBatch, 4, 0) ";
	print "\n    && editFromToOper(document.$formName.frBatch, document.$formName.toBatch, document.$formName.operBatch, 4) ";
	print "\n    && editdate(document.$formName.frDate) ";
	print "\n    && editdate(document.$formName.toDate) ";
	print "\n    && editFromToOper(document.$formName.frDate, document.$formName.toDate, document.$formName.operDate, 'D') ";
	print "\n    && editNum(document.$formName.frDeposit, 13, 2) ";
	print "\n    && editNum(document.$formName.toDeposit, 13, 2) ";
	print "\n    && editFromToOper(document.$formName.frDeposit, document.$formName.toDeposit, document.$formName.operDeposit, 17) ";
	print "\n    && editNum(document.$formName.frOther, 13, 2) ";
	print "\n    && editNum(document.$formName.toOther, 13, 2) ";
	print "\n    && editFromToOper(document.$formName.frOther, document.$formName.toOther, document.$formName.operOther, 17) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHBATCHSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Batch","frBatch","toBatch","operBatch","opersel_num2_short","N","4","4");
	Build_AdvSrch_Entry("Batch Date","frDate","toDate","operDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Bank Name","frBank","toBank","operBank","opersel_alph2_short","A","20","30");
	Build_AdvSrch_Entry("Deposit Total","frDeposit","toDeposit","operDeposit","opersel_num2_short","N","20","17");
	Build_AdvSrch_Entry("Other Total","frOther","toOther","operOther","opersel_num2_short","N","20","17");
	Build_AdvSrch_Entry("Type Description","srchType","","operType","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Payment Entry Description","srchEntry","","operEntry","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Status Description","srchStatus","","operStatus","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Pending Payment Description","srchPend","","operPend","opersel_alph_short","A","20","50");

	$focusField = "frBatch";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Batch")     {$orby = array(array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Date")      {$orby = array(array("BMBCHD","A","Batch Date"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Bank")      {$orby = array(array("BKBKNMU","A","Bank"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Deposit")   {$orby = array(array("BMDEPA","A","Deposit Total"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Variance")  {$orby = array(array("BMBALC","A","Deposit Variance"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Other")     {$orby = array(array("BMADJT","A","Other Total"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Type")      {$orby = array(array("FLDESC_BMBCHTU","A","Type"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Entry")     {$orby = array(array("FLDESC_BMPMTEU","A","Entry"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Status")    {$orby = array(array("FLDESC_BMBCHSU","A","Status"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "InUse")     {$orby = array(array("FLDESC_BUUSERU","A","In Use"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Pending")   {$orby = array(array("FLDESC_BMPPMTU","A","Pending Payment"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Errors")    {$orby = array(array("ARPYENERROR","A","Number of Errors"),array("BMBCHN","A","Batch"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Range_WildCard("BMBCHN", "Batch", $_POST['frBatch'], $_POST['toBatch'], "", $_POST['operBatch'], "N");
	$returnValue=Range_WildCard("BMBCHD", "Batch Date", $_POST['frDate'], $_POST['toDate'], "", $_POST['operDate'], "D");
	$returnValue=Range_WildCard("Upper(BKBKNM)", "Bank Name", $_POST['frBank'], $_POST['toBank'], "U", $_POST['operBank'], "A");
	$returnValue=Range_WildCard("BMDEPA", "Deposit Total", $_POST['frDeposit'], $_POST['toDeposit'], "", $_POST['operDeposit'], "N");
	$returnValue=Range_WildCard("BMADJT", "Other Total", $_POST['frOther'], $_POST['toOther'], "", $_POST['operOther'], "N");
	$returnValue=Build_WildCard("Upper(a.FLDESC)", "Type Description", $_POST['srchType'], "U", $_POST['operType'], "A");
	$returnValue=Build_WildCard("Upper(b.FLDESC)", "Payment Entry Description", $_POST['srchEntry'], "U", $_POST['operEntry'], "A");
	$returnValue=Build_WildCard("Upper(c.FLDESC)", "Status Description", $_POST['srchStatus'], "U", $_POST['operStatus'], "A");
	$returnValue=Build_WildCard("Upper(e.FLDESC)", "Pending Payment Description", $_POST['srchPend'], "U", $_POST['operPend'], "A");
	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

if ($tag != "EXPORT"){
	require_once ($docType);
	print "\n <html> \n	<head>";
	$formName = "Search";
	require_once ($headInclude);

	print "\n \n <script TYPE=\"text/javascript\">";
	require_once 'AJAXRequest.js';
	require_once 'CheckSel.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NoFormValidate.php';
	require_once 'NumEdit.php';
	require_once 'SaveCurrentURL.php';
	require_once 'ShowHideSelCriteria.php';

	require 'ApplCashBatchJava.php';

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "APPLCASHBATCH";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
	print "\n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		$harabh_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);  // Program Option Security
		$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, "HARCED");

		print "\n <td class=\"toolbar\">";
		if ($harabh_OPT['sec_01'] == "Y"){print "\n <a href=\"{$homeURL}{$phpPath}ApplCashBatchMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;fromScript=" . urlencode(trim($scriptName)) . "&amp;maintenanceCode=A\">$addImageLrg</a>";}
		require_once 'XMLFormat.php';
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td>";
	}

	print "\n </tr></table>";
	if ($wfInstance>0){
		print "\n <table $contentTable>";
		Format_Header_URL("Work Item", $wfInstance, $wfInstanceDate, "{$homeURL}{$cGIPath}WFHistorySelect.d2w/REPORT{$altScriptVarBase}&amp;fromScript=" . urlencode(trim($scriptName)) . "&amp;displayWFIcons=Y");
		print "\n </table>";
	}
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

$uv_BankName ="BMBCHB";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select BMBCHN,BMBCHD,BMBCHB,BMBCHS,BMBCHT,BMPMTE,BMPMTS,BMPPMT,BMINST,BMINDT,BMDEPA,BMADJT,BMTRNS,";
$stmtSQL .= " BMDEPA-BMDEPP-BMDEPE as BMBALC, ";
$stmtSQL .= " Coalesce(BKBKNM,' ') as BKBKNM, Coalesce(Upper(BKBKNM),' ') as BKBKNMU, ";
$stmtSQL .= " Coalesce(a.FLDESC,' ') as FLDESC_BMBCHT, Coalesce(Upper(a.FLDESC),' ') as FLDESC_BMBCHTU,";
$stmtSQL .= " Coalesce(b.FLDESC,' ') as FLDESC_BMPMTE, Coalesce(Upper(b.FLDESC),' ') as FLDESC_BMPMTEU,";
$stmtSQL .= " Coalesce(c.FLDESC,' ') as FLDESC_BMBCHS, Coalesce(Upper(c.FLDESC),' ') as FLDESC_BMBCHSU,";
$stmtSQL .= " Coalesce(d.FLDESC,' ') as FLDESC_BUUSER, Coalesce(Upper(d.FLDESC),' ') as FLDESC_BUUSERU,";
$stmtSQL .= " Coalesce(e.FLDESC,' ') as FLDESC_BMPPMT, Coalesce(Upper(e.FLDESC),' ') as FLDESC_BMPPMTU,";
$stmtSQL .= " (Select Count(*) from ARYPTD y Where (YPBCH,YPBDAT,YPBANK,YPRPSQ)=(z.BMBCHN,z.BMBCHD,z.BMBCHB,0) and YPPYCD<>'$CRNPYC' and YPDPTP<>'N' and not exists (Select * from ARRVHL Where (RHISEQ,RHPSEQ)=(y.YPISEQ,y.YPPSEQ))) as ARYPTDOPEN, ";
$stmtSQL .= " Coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB)=(z.BMBCHN,z.BMBCHD,z.BMBCHB)),0) + Coalesce((Select Count(*) from ARDCER Where (CRBCHN,CRBCHD,CRBCHB)=(z.BMBCHN,z.BMBCHD,z.BMBCHB)),0) as ARPYENERROR ";
$fileSQL .= " ARPBCH z ";
$fileSQL .= " left join HDBANK   on BKBANK=BMBCHB ";
$fileSQL .= " left join SYFLAG a on (a.FLTYPE,a.FLVALU)=('ARBCHTYPE',BMBCHT) ";
$fileSQL .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTENTR',BMPMTE) ";
$fileSQL .= " left join SYFLAG c on (c.FLTYPE,c.FLVALU)=('ARBCHSTAT',BMBCHS) ";
$fileSQL .= " left join SYFLAG d on (d.FLTYPE,d.FLVALU)=('BY',Case When (Select Count(*) from ARPBCU Where (BUBCHN,BUBCHD,BUBCHB)=(z.BMBCHN,z.BMBCHD,z.BMBCHB))>0 Then 'Y' Else ' ' End) ";
$fileSQL .= " left join SYFLAG e on (e.FLTYPE,e.FLVALU)=('BY',BMPPMT) ";
if ($wfInstance>0) {$selectSQL .= " (BMINST,BMINDT)=($wfInstance,$wfInstanceDate) ";
} elseif (!$viewCheckBox[0] && !$viewCheckBox[1] && !$viewCheckBox[2] && !$viewCheckBox[3]) {$selectSQL .= " BMBCHS<>BMBCHS";
} else {
	if ($CRBBAL == "Y") {$selectSQL .= " (BMBCHT='C' or BMBCHT='D' and BMBCHS<>'O') ";}
	$viewCheckSQL = Build_CheckBoxSQL($viewCheckBoxDef, $viewCheckBox);
	if ($selectSQL == "") {$selectSQL  = $viewCheckSQL;
	} else                {$selectSQL .= " and $viewCheckSQL ";}
}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	if ($formatToPrint == ""){
		$qsOpt = "";
		$qsOpt .= "\n <option value=\"BMBCHN|null|Batch|N|\" title=\"Batch\" SELECTED>Batch";
		$qsOpt .= "\n <option value=\"BMBCHD|DATE|Batch Date|D|\" title=\"Batch Date\">Batch Date";
		$qsOpt .= "\n <option value=\"Upper(BKBKNM)|null|Bank Name|A|U\" title=\"Bank Name\">Bank Name";
		$qsOpt .= "\n <option value=\"BMDEPA|null|Deposit Total|N|\" title=\"Deposit Total\">Deposit Total";
		$qsOpt .= "\n <option value=\"BMADJT|null|Other Total|N|\" title=\"Other Total\">Other Total";
		$qsOpt .= "\n <option value=\"Upper(a.FLDESC)|null|Type Description|A|U\" title=\"Type Description\">Type Description";
		$qsOpt .= "\n <option value=\"Upper(b.FLDESC)|null|Payment Entry Description|A|U\" title=\"Payment Entry Description\">Payment Entry Description";
		$qsOpt .= "\n <option value=\"Upper(c.FLDESC)|null|Status Description|A|U\" title=\"Status Description\">Status Description";
		$qsOpt .= "\n <option value=\"Upper(e.FLDESC)|null|Pending Payment Description|A|U\" title=\"Pending Payment Description\">Pending Payment Description";
		require 'QuickSearchOption.php';
	}
	print "<table $contentTable> <tr>";
	if ($formatToPrint != "Y"  &&  ($harabh_OPT['sec_02']=="Y"  || $harabh_OPT['sec_03']=="Y")or $harabh_OPT['sec_05']=="Y" || $harabh_OPT['sec_06']=="Y" || $harced_OPT['sec_01']=="Y" || $harced_OPT['sec_02']=="Y" || $harced_OPT['sec_03']=="Y" || $harced_OPT['sec_04']=="Y" || $harced_OPT['sec_05']=="Y" || $harced_OPT['sec_06']=="Y" && $CRDPYC!=""){
		print "<th class=\"colhdr\">$optionHeading</th>";
	}
	$returnValue=OrderBy_Sort("BMBCHN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Batch\"      title=\"Sequence By Batch\">{$sortPoint}Batch</a></th>";
	$returnValue=OrderBy_Sort("BMBCHD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Date\"       title=\"Sequence By Batch Date, Batch\">{$sortPoint}Batch Date</a></th>";
	$returnValue=OrderBy_Sort("BKBKNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Bank\"       title=\"Sequence By Bank, Batch\">{$sortPoint}Bank</a></th>";
	$returnValue=OrderBy_Sort("BMDEPA"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Deposit\"    title=\"Sequence By Deposit Total, Batch\">{$sortPoint}Deposit Total</a></th>";
	$returnValue=OrderBy_Sort("BMBALC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Variance\"    title=\"Sequence By Deposit Variance, Batch\">{$sortPoint}Deposit Variance</a></th>";
	$returnValue=OrderBy_Sort("BMADJT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Other\" title=\"Sequence By Other Total, Batch\">{$sortPoint}Other Total</a></th>";
	$returnValue=OrderBy_Sort("FLDESC_BMBCHTU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Type\"       title=\"Sequence By Type, Batch\">{$sortPoint}Type</a></th>";
	$returnValue=OrderBy_Sort("FLDESC_BMPMTEU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Entry\"      title=\"Sequence By Payment Entry, Batch\">{$sortPoint}Payment Entry</a></th> ";
	$returnValue=OrderBy_Sort("FLDESC_BMBCHSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Status\"     title=\"Sequence By Status, Batch\">{$sortPoint}Status</a></th>";
	$returnValue=OrderBy_Sort("FLDESC_BUUSERU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InUse\"      title=\"Sequence By In Use, Batch\">{$sortPoint}In Use</a></th>";
	$returnValue=OrderBy_Sort("FLDESC_BMPPMTU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Pending\"    title=\"Sequence By Pending Payment, Batch\">{$sortPoint}Pending Payment</a></th>";
	$returnValue=OrderBy_Sort("ARPYENERROR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Errors\"    title=\"Sequence By Number of Errors, Batch\">{$sortPoint}Number of Errors</a></th>";
	print "\n </tr>";
}

if ($tag == "EXPORT"){$xmlListName = "ApplCashBatchList"; require_once 'XMLInit.php';}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	if ($tag == "EXPORT"){
		$xmlID  = $xmlDoc->createElement(ApplCashBatch); $xmlRoot->appendChild($xmlID);
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Batch"));                              $xmlTag->appendChild($xmlDoc->createTextNode($row['BMBCHN']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("BatchDate"));                          $xmlTag->appendChild($xmlDoc->createTextNode($row['BMBCHD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Bank"));                               $xmlTag->appendChild($xmlDoc->createTextNode($row['BMBCHB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("BatchStatus"));                        $xmlTag->appendChild($xmlDoc->createTextNode($row['BMBCHS']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("BatchType"));                          $xmlTag->appendChild($xmlDoc->createTextNode($row['BMBCHT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PaymentEntry"));                       $xmlTag->appendChild($xmlDoc->createTextNode($row['BMPMTE']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PaymentStatus"));                      $xmlTag->appendChild($xmlDoc->createTextNode($row['BMPMTS']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PendingPayment"));                     $xmlTag->appendChild($xmlDoc->createTextNode($row['BMPPMT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Instance"));                           $xmlTag->appendChild($xmlDoc->createTextNode($row['BMINST']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("InstanceDate"));                       $xmlTag->appendChild($xmlDoc->createTextNode($row['BMINDT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("WorkflowProcess"));                    $xmlTag->appendChild($xmlDoc->createTextNode($row['BMWFPR']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("DepositTotal"));                       $xmlTag->appendChild($xmlDoc->createTextNode($row['BMDEPA']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("DepositPendingPayment"));              $xmlTag->appendChild($xmlDoc->createTextNode($row['BMDEPE']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("DepositPosted"));                      $xmlTag->appendChild($xmlDoc->createTextNode($row['BMDEPP']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("DepositDeniedInWorkflow"));            $xmlTag->appendChild($xmlDoc->createTextNode($row['BMDEPD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OtherTotal"));                         $xmlTag->appendChild($xmlDoc->createTextNode($row['BMADJT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OtherPendingPayment"));                $xmlTag->appendChild($xmlDoc->createTextNode($row['BMADJE']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OtherPosted"));                        $xmlTag->appendChild($xmlDoc->createTextNode($row['BMADJP']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OtherDeniedInWorkflow"));              $xmlTag->appendChild($xmlDoc->createTextNode($row['BMADJD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("TransactionTotal"));                   $xmlTag->appendChild($xmlDoc->createTextNode($row['BMCHKT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("NumberOfTransactions"));               $xmlTag->appendChild($xmlDoc->createTextNode($row['BMTRNS']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("EntryTimestamp"));                     $xmlTag->appendChild($xmlDoc->createTextNode($row['BMAUDT']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("EntryUserProfile"));                   $xmlTag->appendChild($xmlDoc->createTextNode($row['BMAUDU']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Timestamp"));                          $xmlTag->appendChild($xmlDoc->createTextNode($row['BMTSTP']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("TimestampUserProfile"));               $xmlTag->appendChild($xmlDoc->createTextNode($row['BMTSUS']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ProcessTimestamp"));                   $xmlTag->appendChild($xmlDoc->createTextNode($row['BMTSPT']));

	} else {
		$maintainVar = "{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['BMBCHN'])) . "&amp;fromBatchDate=" . urlencode(trim($row['BMBCHD'])) . "&amp;fromBatchBank=" . urlencode(trim($row['BMBCHB'])) . "&amp;fromScript=" . urlencode(trim($scriptName));

		$Allow_Release=RetValue("(BUBCHN,BUBCHD,BUBCHB,BUUSER)=($row[BMBCHN],$row[BMBCHD],$row[BMBCHB],'$userProfile')", "ARPBCU", "Count(*)");
		$ARYPTDRecCnt =RetValue("(YPBCH ,YPBDAT,YPBANK)=($row[BMBCHN],$row[BMBCHD],$row[BMBCHB])", "ARYPTD", "Count(*)");
		$Batch_InUse  =RetValue("(BUBCHN,BUBCHD,BUBCHB)=($row[BMBCHN],$row[BMBCHD],$row[BMBCHB])", "ARPBCU", "Count(*)");
		$F_BMBCHD=Format_Date($row['BMBCHD'], "D");
		$wrkDate = Date_CYMD_ISO($row['BMBCHD']);
		$hover_BMBCHD = date('l F dS Y', strtotime($wrkDate));

		require 'SetRowClass.php';
		$confirmDesc = Format_Confirm_Desc("Batch", "$row[BMBCHN]", "Batch Date", "$F_BMBCHD", "Bank", "$row[BKBKNM]");
		print "\n <tr class=\"$rowClass\">";
		if ($formatToPrint != "Y" && ($harabh_OPT['sec_02']=="Y" || $harabh_OPT['sec_03']=="Y" || $harabh_OPT['sec_05']=="Y" || $harabh_OPT['sec_06']=="Y" || $harced_OPT['sec_01']=="Y" || $harced_OPT['sec_02']=="Y" || $harced_OPT['sec_03']=="Y" || $harced_OPT['sec_04']=="Y" || $harced_OPT['sec_05']=="Y" || $harced_OPT['sec_06']=="Y" && $CRDPYC!="")){
			print "\n <td class=\"opticon\">";
			// Change icon
			if (($harabh_OPT['sec_02']=="Y" || $harabh_OPT['sec_03']=="Y") && trim($row['BMPMTS'])=="" && $row['BMBCHD']>=$ARPdBegDate && ($row['BMINST']==0 || $row['BMINST']==$wfInstance && $row['BMINDT']==$wfInstanceDate)){
				print "\n <a href=\"{$homeURL}{$phpPath}ApplCashBatchMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageSml</a>";
			}
			// Delete icon
			if ($harabh_OPT['sec_03']=="Y" && trim($row['BMPMTS'])=="" && !$ARYPTDRecCnt && (trim($row['BMPMTE']) == "" || $row['BMPMTE'] == "A") && $row['BMBCHT']!="D" && $row['BMINST']==0) {
				print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}ApplCashBatchMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
			}
			// Payment icon
			if ($row['BMBCHD']>=$ARPdBegDate && trim($row['BMPMTS'])=="" && (trim($row['BMPMTE']) == "" || $row['BMPMTE']=="A") && ($row['BMINST']==0 || $row['BMINST']==$wfInstance && $row['BMINDT']==$wfInstanceDate) && (($CRBBAL=="Y" && $row['BMBCHT']!="D" && ($harced_OPT['sec_03']=="Y" || $harced_OPT['sec_04']=="Y" || $harced_OPT['sec_06']=="Y") && $CRDPYC!="") || (($CRBBAL!="Y" || $row['BMBCHT']=="D") && ($harced_OPT['sec_01']=="Y" || $harced_OPT['sec_02']=="Y" || $harced_OPT['sec_03']=="Y" || $harced_OPT['sec_04']=="Y" || $harced_OPT['sec_05']=="Y" || $harced_OPT['sec_06']=="Y" && $CRDPYC!="")))) {
				print "\n <a onClick=\"BTCH_CUST('$row[BMBCHN]', '$row[BMBCHD]', '$row[BMBCHB]');\" href=\"{$homeURL}{$phpPath}ApplCashCustomer.php{$maintainVar}&amp;tag=REPORT\">$arCashPmtImageSml</a>";
			}
			// Attachment icon
			if ($harabh_OPT['sec_01']=="Y" || $harabh_OPT['sec_02']=="Y" || $harabh_OPT['sec_03']=="Y"){
				$attachVarKey=trim($row['BMBCHN']) . "_" . trim($row['BMBCHD']) . "_" . trim($row['BMBCHB']);
				$attachForDesc="$row[BKBKNM] - Batch $row[BMBCHN] on $F_BMBCHD";
				$attachPrg1= "ARPBCH Where (PEBCHN,PEBCHD,PEBCHB)=($row[BMBCHN],$row[BMBCHD],$row[BMBCHB]) ";
				print "\n <a href=\"{$homeURL}{$phpPath}Attachment.php{$scriptVarBase}&amp;attachFolder=" . urlencode($attachFolder) . "&amp;attachForDesc=" . urlencode($attachForDesc) . "&amp;attachVarKey=" . urlencode($attachVarKey) . "&amp;userProfile=" . urlencode($userProfile) . "&amp;attachPrg1=" . urlencode($attachPrg1) . "&amp;attachPrg2=" . urlencode($attachPrg2) . "&amp;attachPrg3=" . urlencode($attachPrg3) . "&amp;attachPrg4=" . urlencode($attachPrg4) . "&amp;attachPrg5=" . urlencode($attachPrg5) . "\" onclick=\"$selectionWinVar\">$attachImageSml</a> ";
			}
			// Release icon
			if ($Allow_Release && ($row['BMINST']==0 || $row['BMINST']==$wfInstance && $row['BMINDT']==$wfInstanceDate)) {
				print "\n <a onClick=\"BTCH_RLSE('$row[BMBCHN]', '$row[BMBCHD]', '$row[BMBCHB]');\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT\">$releaseBatchImage</a>";
			}
			// Transfer to History icon
			if (trim($harabh_OPT['sec_05'])=="Y" && $Batch_InUse==0 && trim($row['BMPMTS'])=="" && $row['BMPMTE']=="A" && $row['BMPPMT']=="Y" && $row['ARPYENERROR']==0) {
				print "\n <a href=\"{$homeURL}{$phpPath}ApplCashBatchToHistory.php{$maintainVar}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($scriptName)) . "\">$workflowBatchImageSml</a>";
			}
			// Report icon
			if ($ARYPTDRecCnt && ($harabh_OPT['sec_01']=="Y" || $harabh_OPT['sec_02']=="Y" || $harabh_OPT['sec_03']=="Y" || (($CRBBAL=="Y" && $row['BMBCHT']!="D" && ($harced_OPT['sec_03']=="Y" || $harced_OPT['sec_04']=="Y" || $harced_OPT['sec_06']=="Y" && $CRDPYC!="")) || (($CRBBAL!="Y" || $row['BMBCHT']=="D") && ($harced_OPT['sec_01']=="Y" || $harced_OPT['sec_02']=="Y" || $harced_OPT['sec_03']=="Y" || $harced_OPT['sec_04']=="Y" || $harced_OPT['sec_05']=="Y" || $harced_OPT['sec_06']=="Y" && $CRDPYC!=""))))) {
				print "\n <a href=\"{$homeURL}{$phpPath}ApplCashBatchReport.php{$maintainVar}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($scriptName)) . "\">$reportBatchImageSml</a>";
			}
			// Reverse icon
			if ($row['BMPMTE']!="N" && $harabh_OPT['sec_06']=="Y" && trim($row['BMPMTS'])=="" && trim($row['ARYPTDOPEN'])>0 && $Batch_InUse==0 && $row['BMBCHS'] !="W" && $wfInstance<=0) {
				print "\n <a href=\"{$homeURL}{$phpPath}ApplCashBatchReverse.php{$maintainVar}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($scriptName)) . "\">$reverseBatchImageSml</a>";
			}
			print "\n </td>";
		}
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashBatchSelect.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($scriptName)) . "\" title=\"View Batch\">$row[BMBCHN]</a></td>";
		print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$hover_BMBCHD\">$F_BMBCHD</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMBCHB]\">$row[BKBKNM]</span></td>";
		$ARDEPDRecCount=RetValue("(BDBCHN,BDBCHD,BDBCHB)=($row[BMBCHN],$row[BMBCHD],$row[BMBCHB])", "ARDEPD", "Count(*)");
		if ($row['BMBCHT']=="D" && $ARDEPDRecCount) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ARDepositEntryInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Deposit Entry\">" . Format_Nbr ( $row['BMDEPA'], '2', $amtEditCode, 'Y', '', '') . "</a></td>";}
		else                                        {print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['BMDEPA'], '2', $amtEditCode, 'Y', '', '') . "</td>";}
		print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['BMBALC'], '2', $amtEditCode, 'Y', '', '') . "</td>";
		print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['BMADJT'], '2', $amtEditCode, 'Y', '', '') . "</td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMBCHT]\">$row[FLDESC_BMBCHT]</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMPMTE]\">$row[FLDESC_BMPMTE]</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMBCHS]\">$row[FLDESC_BMBCHS]</span></td>";
		if (trim($row['FLDESC_BUUSER'])=="Yes") {print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}ApplCashBatchUserInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View user assigned to batch\">$row[FLDESC_BUUSER]</a></td>";}
		else                                    {print "\n <td class=\"colcode\">$row[FLDESC_BUUSER]</td>";}
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[BMPPMT]\">$row[FLDESC_BMPPMT]</span></td>";
		if ($row['ARPYENERROR']==0) {print "\n <td class=\"colnmbr\">$row[ARPYENERROR]</td>";}
		else                        {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashErrorInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Error\">$row[ARPYENERROR]</a></td>";}
		print "\n </tr>";
	}
	$startRow ++;
	$rowCount ++;
}

require_once 'XMLExport.php';

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "\n </table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>