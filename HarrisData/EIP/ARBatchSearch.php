<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName            = $_GET['docName'];
$specificBank       = $_GET['specificBank'];
$fldBatch           = $_GET['fldBatch'];
$fldDate            = $_GET['fldDate'];
$fldBank            = $_GET['fldBank'];

$moreInfo           = $_GET['moreInfo'];
$batchNumber        = $_GET['batchNumber'];
$batchDate          = $_GET['batchDate'];
$batchBank          = $_GET['batchBank'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$CRPERN=RetValue("RRN(ARCTRL)=1", "ARCTRL", "CRDPER");
$ARPdBegDate=RetValue("PDPER#=$CRPERN", "HDPBED", "PDBDAT");

$page_title     = "A/R Payment Batch";
$scriptName     = "ARBatchSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldBatch=" . urlencode(trim($fldBatch)) . "&amp;fldDate=" . urlencode(trim($fldDate)) . "&amp;fldBank=" . urlencode(trim($fldBank)) . "&amp;specificBank=" . urlencode(trim($specificBank));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy    = array(array("BMBCHN","A","Batch"));

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("View:", "Closed", $viewCheckBoxURL, "1", "0", "BMBCHS='C' and BMPPMT<>'Y' and BMBCHD>=$ARPdBegDate"),
array("", "Entry", $viewCheckBoxURL, "2", "1", "(BMBCHS in ('O','R') or BMPPMT='Y')"),
array("", "Workflow", $viewCheckBoxURL, "3", "0", "BMBCHS='W'"),
array("", "Prior Period", $viewCheckBoxURL, "4", "0", "BMBCHD<$ARPdBegDate"),
array("", "With Variance", $viewCheckBoxURL, "5", "0", "BMDEPA-BMDEPP-BMDEPE<>0"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	
	require_once 'CalendarInclude.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	
	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.$formName.frBatch, 4, 0) ";
	print "\n    && editNum(document.$formName.toBatch, 4, 0) ";
	print "\n    && editFromToOper(document.$formName.frBatch, document.$formName.toBatch, document.$formName.operBatch, 4) ";
	print "\n    && editdate(document.$formName.frDate) ";
	print "\n    && editdate(document.$formName.toDate) ";
	print "\n    && editFromToOper(document.$formName.frDate, document.$formName.toDate, document.$formName.operDate, 'D') ";
	print "\n    && editNum(document.$formName.frBank, 2, 0) ";
	print "\n    && editNum(document.$formName.toBank, 2, 0) ";
	print "\n    && editFromToOper(document.$formName.frBank, document.$formName.toBank, document.$formName.operBank, 2) ";
	print "\n    && editNum(document.$formName.frDeposit, 13, 2) ";
	print "\n    && editNum(document.$formName.toDeposit, 13, 2) ";
	print "\n    && editFromToOper(document.$formName.frDeposit, document.$formName.toDeposit, document.$formName.operDeposit, 17) ";
	print "\n    && editNum(document.$formName.frOther, 13, 2) ";
	print "\n    && editNum(document.$formName.toOther, 13, 2) ";
	print "\n    && editFromToOper(document.$formName.frOther, document.$formName.toOther, document.$formName.operOther, 17) ";
	print "\n   ) return true; ";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Batch","frBatch","toBatch","operBatch","opersel_num2_short","N","4","4");
	Build_AdvSrch_Entry("Batch Date","frDate","toDate","operDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Bank","frBank","toBank","operBank","opersel_num2_short","N","2","2");
	Build_AdvSrch_Entry("Deposit Total","frDeposit","toDeposit","operDeposit","opersel_num2_short","N","20","17");
	Build_AdvSrch_Entry("Other Total","frOther","toOther","operOther","opersel_num2_short","N","20","17");
	Build_AdvSrch_Entry("Type Description","srchType","","operType","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Payment Entry Description","srchEntry","","operEntry","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Status Description","srchStatus","","operStatus","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Pending Payment Description","srchPend","","operPend","opersel_alph_short","A","20","50");

	$focusField = "frBatch";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Batch")     {$orby = array(array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Date")      {$orby = array(array("BMBCHD","A","Batch Date"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Bank")      {$orby = array(array("BMBCHB","A","Bank"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Deposit")   {$orby = array(array("BMDEPA","A","Deposit Total"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Other")     {$orby = array(array("BMADJT","A","Other Total"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Type")      {$orby = array(array("FLDESC_BMBCHTU","A","Type"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Entry")     {$orby = array(array("FLDESC_BMPMTEU","A","Entry"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Status")    {$orby = array(array("FLDESC_BMBCHSU","A","Status"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "InUse")     {$orby = array(array("FLDESC_BUUSERU","A","In Use"),array("BMBCHN","A","Batch"));}
	elseif ($sequence == "Pending")   {$orby = array(array("FLDESC_BMPPMTU","A","Pending Payment"),array("BMBCHN","A","Batch"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Range_WildCard("BMBCHN", "Batch", $_POST['frBatch'], $_POST['toBatch'], "", $_POST['operBatch'], "N");
	$returnValue=Range_WildCard("BMBCHD", "Batch Date", $_POST['frDate'], $_POST['toDate'], "", $_POST['operDate'], "D");
	$returnValue=Range_WildCard("BMBCHB", "Bank", $_POST['frBank'], $_POST['toBank'], "", $_POST['operBank'], "N");
	$returnValue=Range_WildCard("BMDEPA", "Deposit Total", $_POST['frDeposit'], $_POST['toDeposit'], "", $_POST['operDeposit'], "N");
	$returnValue=Range_WildCard("BMADJT", "Other Total", $_POST['frOther'], $_POST['toOther'], "", $_POST['operOther'], "N");
	$returnValue=Build_WildCard("Upper(a.FLDESC)", "Type Description", $_POST['srchType'], "U", $_POST['operType'], "A");
	$returnValue=Build_WildCard("Upper(b.FLDESC)", "Payment Entry Description", $_POST['srchEntry'], "U", $_POST['operEntry'], "A");
	$returnValue=Build_WildCard("Upper(c.FLDESC)", "Status Description", $_POST['srchStatus'], "U", $_POST['operStatus'], "A");
	$returnValue=Build_WildCard("Upper(e.FLDESC)", "Pending Payment Description", $_POST['srchPend'], "U", $_POST['operPend'], "A");
	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';

$formName = "Search";  // Need to Calendar Include
require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectBatch(batchNumber, batchDate, batchBank){ ";
print "\n   window.opener.document.$docName.$fldBatch.value = batchNumber; ";
print "\n   window.opener.document.$docName.$fldDate.value = batchDate; ";
if ($specificBank=="") {print "\n   window.opener.document.$docName.$fldBank.value = batchBank; ";}
print "\n   window.opener.document.$docName.$fldBatch.focus(); ";
print "\n   window.close(); ";
print "\n } ";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

$uv_BankName ="BMBCHB";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select BMBCHN,BMBCHD,BMBCHB,BMBCHT,BMPMTE,BMPPMT,BMINST,BMDEPA,BMADJT,BMTRNS,BMBCHS,";
$stmtSQL .= " Coalesce(BKBKNM,' ') as BKBKNM, Coalesce(Upper(BKBKNM),' ') as BKBKNMU, ";
$stmtSQL .= " Coalesce(a.FLDESC,' ') as FLDESC_BMBCHT, Coalesce(Upper(a.FLDESC),' ') as FLDESC_BMBCHTU,";
$stmtSQL .= " Coalesce(b.FLDESC,' ') as FLDESC_BMPMTE, Coalesce(Upper(b.FLDESC),' ') as FLDESC_BMPMTEU,";
$stmtSQL .= " Coalesce(c.FLDESC,' ') as FLDESC_BMBCHS, Coalesce(Upper(c.FLDESC),' ') as FLDESC_BMBCHSU,";
$stmtSQL .= " Coalesce(d.FLDESC,' ') as FLDESC_BUUSER, Coalesce(Upper(d.FLDESC),' ') as FLDESC_BUUSERU,";
$stmtSQL .= " Coalesce(e.FLDESC,' ') as FLDESC_BMPPMT, Coalesce(Upper(e.FLDESC),' ') as FLDESC_BMPPMTU";
$fileSQL .= " ARPBCH z ";
$fileSQL .= " left join HDBANK   on BKBANK=BMBCHB ";
$fileSQL .= " left join SYFLAG a on (a.FLTYPE,a.FLVALU)=('ARBCHTYPE',BMBCHT) ";
$fileSQL .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTENTR',BMPMTE) ";
$fileSQL .= " left join SYFLAG c on (c.FLTYPE,c.FLVALU)=('ARBCHSTAT',BMBCHS) ";
$fileSQL .= " left join SYFLAG d on (d.FLTYPE,d.FLVALU)=('BY',Case When (Select Count(*) from ARPBCU Where (BUBCHN,BUBCHD,BUBCHB)=(z.BMBCHN,z.BMBCHD,z.BMBCHB))>0 Then 'Y' Else ' ' End)";
$fileSQL .= " left join SYFLAG e on (e.FLTYPE,e.FLVALU)=('BY',BMPPMT)  ";
if ($moreInfo=="Y") {$selectSQL .= " (BMBCHN,BMBCHD,BMBCHB)=($batchNumber,$batchDate,$batchBank) ";
} elseif (!$viewCheckBox[0] && !$viewCheckBox[1] && !$viewCheckBox[2] && !$viewCheckBox[3] && !$viewCheckBox[4]) {$selectSQL .= " BMBCHS<>BMBCHS";
} else {
	if ($specificBank!="") {$selectSQL .= " BMBCHB=$specificBank ";}
	if ($CRBBAL == "Y" && $selectSQL == "") {$selectSQL .= " (BMBCHT='C' or BMBCHT='D' and BMBCHS='R') ";}
	elseif ($CRBBAL == "Y")                 {$selectSQL .= " and (BMBCHT='C' or BMBCHT='D' and BMBCHS='R') ";}
	$viewCheckSQL = Build_CheckBoxSQL($viewCheckBoxDef, $viewCheckBox);
	if ($selectSQL == "") {$selectSQL  = $viewCheckSQL;
	} else                {$selectSQL .= " and $viewCheckSQL ";}
}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
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

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("BMBCHN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Batch\"      title=\"Sequence By Batch\">{$sortPoint}Batch</a></th>";
	$returnValue=OrderBy_Sort("BMBCHD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Date\"       title=\"Sequence By Batch Date, Batch\">{$sortPoint}Batch Date</a></th>";
	$returnValue=OrderBy_Sort("BMBCHB"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Bank\"       title=\"Sequence By Bank, Batch\">{$sortPoint}Bank</a></th>";
	$returnValue=OrderBy_Sort("BMDEPA"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Deposit\"    title=\"Sequence By Deposit Total, Batch\">{$sortPoint}Deposit Total</a></th>";
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
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}

		$F_BMBCHD=Format_Date($row['BMBCHD'], "D");
		$wrkDate = Date_CYMD_ISO($row['BMBCHD']);
		$hover_BMBCHD = date('l F dS Y', strtotime($wrkDate));
		$inputBMBCHD=DateInputFromCYMD($row['BMBCHD']);

		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		print "\n <td class=\"colnmbr\"><a href=\"javascript:selectBatch($row[BMBCHN],'$inputBMBCHD',$row[BMBCHB])\" title=\"Select Batch\">$row[BMBCHN]</a></td> ";
		print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$hover_BMBCHD\">$F_BMBCHD</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMBCHB]\">$row[BKBKNM]</span></td>";
		$ARDEPDRecCount=RetValue("(BDBCHN,BDBCHD,BDBCHB)=($row[BMBCHN],$row[BMBCHD],$row[BMBCHB])", "ARDEPD", "Count(*)");
		if ($row['BMBCHT']=="D" && $ARDEPDRecCount) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ARDepositEntryInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Deposit Entry\">" . number_format($row['BMDEPA'],2) . "</a></td>";}
		else                                        {print "\n <td class=\"colnmbr\">" . number_format($row['BMDEPA'],2) . "</td>";}
		print "\n <td class=\"colnmbr\">" . number_format($row['BMADJT'],2) . "</td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMBCHT]\">$row[FLDESC_BMBCHT]</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMPMTE]\">$row[FLDESC_BMPMTE]</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMBCHS]\">$row[FLDESC_BMBCHS]</span></td>";
		if (trim($row['FLDESC_BUUSER'])=="Yes") {print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$phpPath}ApplCashBatchUserInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View user assigned to batch\">$row[FLDESC_BUUSER]</a></td>";}
		else                                    {print "\n <td class=\"colcode\">$row[FLDESC_BUUSER]</td>";}
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[BMPPMT]\">$row[FLDESC_BMPPMT]</span></td>";
		print "\n <td class=\"colicon\"><a href=\"{$homeURL}{$cGIPath}{$scriptName}{$scriptVarBase}&amp;batchNumber=" . urlencode(trim($row['BMBCHN'])) . "&amp;batchDate=" . urlencode(trim($row['BMBCHD'])) . "&amp;batchBank=" . urlencode(trim($row['BMBCHB'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_BMBCHD=Format_Date($row['BMBCHD'], "D");
	$inputBMBCHD=DateInputFromCYMD($row['BMBCHD']);

	$moreInfoSelect = "href=\"javascript:selectBatch($row[BMBCHN],$inputBMBCHD,$row[BMBCHB])\" title=\"Select Batch\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	$F_BMBCHD=Format_Code(Format_Date($row['BMBCHD'], "D"));
	print "\n <tr> ";
	print "\n <td class=\"dsphdr\">Batch</td> ";
	print "\n <td class=\"dspnmbr\">$row[BMBCHN] $F_BMBCHD</td> ";
	print "\n </tr> ";

	$F_BMBCHB=Format_Code($row['BMBCHB']);
	print "\n <tr> ";
	print "\n <td class=\"dsphdr\">Bank</td> ";
	print "\n <td class=\"dspalph\">$row[BKBKNM] $F_BMBCHB</td> ";
	print "\n </tr> ";

	$F_BMBCHT=Format_Code($row['BMBCHT']);
	print "\n <tr><td class=\"dsphdr\">Batch Type</td> ";
	print "\n     <td class=\"dspalph\">$row[FLDESC_BMBCHT]</td> ";
	print "\n     <td class=\"dspalph\">$F_BMBCHT</td> ";
	print "\n </tr> ";

	$F_BMPMTE=Format_Code($row['BMPMTE']);
	print "\n <tr><td class=\"dsphdr\">Payment Entry</td> ";
	print "\n     <td class=\"dspalph\">$row[FLDESC_BMPMTE]</td> ";
	print "\n     <td class=\"dspalph\">$F_BMPMTE</td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\">Instance</td> ";
	print "\n     <td class=\"dspalph\">$row[BMINST]</td> ";
	print "\n </tr> ";

	$F_BMINDT=Format_Date($row['BMINDT'], "D");
	print "\n <tr><td class=\"dsphdr\">Instance Date</td> ";
	print "\n     <td class=\"dspalph\">$F_BMINDT</td> ";
	print "\n </tr> ";

	$F_BMWFPR=Format_Code($row['BMWFPR']);
	print "\n <tr><td class=\"dsphdr\">Workflow Process</td> ";
	print "\n     <td class=\"dspalph\">$row[PRDESC]</td> ";
	print "\n     <td class=\"dspalph\">$F_BMWFPR</td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\">Transaction Total</td> ";
	print "\n     <td class=\"dspnmbr\">" . number_format($row['BMCHKT'],2) . "</td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\">Number Of Transactions</td> ";
	print "\n     <td class=\"dspnmbr\">" . number_format($row['BMTRNS'],0) . "</td> ";
	print "\n </tr> ";

	$F_BMBCHS=Format_Code($row['BMBCHS']);
	print "\n <tr><td class=\"dsphdr\">Batch Status</td> ";
	print "\n     <td class=\"dspalph\">$row[FLDESC_BMBCHS]</td> ";
	print "\n     <td class=\"dspalph\">$F_BMBCHS</td> ";
	print "\n </tr> ";

	print "\n <tr><td class=\"dsphdr\">Initial Entry Timestamp</td> ";
	print "\n     <td class=\"dspalph\">$row[BMAUDT]</td> ";
	print "\n </tr> ";

	$F_BMAUDU=Format_Code($row['BMAUDU']);
	print "\n <tr><td class=\"dsphdr\">Initial Entry By User Profile</td> ";
	print "\n     <td class=\"dspalph\">$row[USDESC_BMAUDU]</td> ";
	print "\n     <td class=\"dspalph\">$F_BMAUDU</td> ";
	print "\n </tr> ";

	print "\n </table> ";

	print "\n <table $contentTable>";
	print "\n <tr><td class=\"dsphdr\">&nbsp;</td> ";
	print "\n     <td class=\"colhdr\">Total</td> ";
	print "\n     <td class=\"colhdr\">Posted</td> ";
	print "\n     <td class=\"colhdr\">Pending</td> ";
	print "\n     <td class=\"colhdr\">Variance</td> ";
	print "\n </tr> ";

	$result=$row['BMDEPA'] - ($row['BMDEPP'] + $row['BMDEPE']);
	print "\n <tr><td class=\"dsphdr\">Deposit</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMDEPA'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMDEPP'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMDEPE'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($result,2) . "</td> ";
	print "\n </tr> ";

	$result=$row['BMADJT'] - ($row['BMADJP'] + $row['BMADJE']);
	print "\n <tr><td class=\"dsphdr\">Other</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMADJT'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMADJP'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($row['BMADJE'],2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($result,2) . "</td> ";
	print "\n </tr> ";

	print "\n </table> ";
	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardPrint.php';
}
print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
