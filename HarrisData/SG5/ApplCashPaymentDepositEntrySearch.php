<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$fromType           = $_GET['fromType'];
$fromID             = $_GET['fromID'];

$docName            = $_GET['docName'];
$fldDocNumber       = $_GET['fldDocNumber'];
$fldDocAmount       = $_GET['fldDocAmount'];
$forceChange        = $_GET['forceChange'];

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

$page_title     = "Deposit Entry Search";
$scriptName     = "ApplCashPaymentDepositEntrySearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;forceChange=" . urlencode(trim($forceChange)) . "&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldDocNumber=" . urlencode(trim($fldDocNumber)) . "&amp;fldDocAmount=" . urlencode(trim($fldDocAmount));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("BDSRCN","A","Source Number"));

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("View:", "Has Balance", $viewCheckBoxURL, "1", "1", "BDAMT-Coalesce((Select Sum(YPAMT) from ARYPTD Where (YPBCH,YPBDAT,YPBANK,YPCHK)=(a.BDBCHN,a.BDBCHD,a.BDBCHB,a.BDSRCN) and YPPYCD in (Select PYPYCD from ARPYCD Where PYTYPE not in ('J','Y'))),0)-Coalesce((Select Sum(PEAMT) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB)=(a.BDBCHN,a.BDBCHD,a.BDBCHB) and (PECHK=a.BDSRCN or PEMCHK=a.BDSRCN) and PESBCD in (Select PSSBCD from ARPYCD inner join ARPYSB on PSPYCD=PYPYCD Where PYTYPE not in ('J','Y'))),0)<>0"),
array("", "No Balance", $viewCheckBoxURL, "2", "1", "BDAMT-Coalesce((Select Sum(YPAMT) from ARYPTD Where (YPBCH,YPBDAT,YPBANK,YPCHK)=(a.BDBCHN,a.BDBCHD,a.BDBCHB,a.BDSRCN) and YPPYCD in (Select PYPYCD from ARPYCD Where PYTYPE not in ('J','Y'))),0)-Coalesce((Select Sum(PEAMT) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB)=(a.BDBCHN,a.BDBCHD,a.BDBCHB) and (PECHK=a.BDSRCN or PEMCHK=a.BDSRCN) and PESBCD in (Select PSSBCD from ARPYCD inner join ARPYSB on PSPYCD=PYPYCD Where PYTYPE not in ('J','Y'))),0)=0"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CalendarInclude.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchPayment, 13, 2) && ";
	print "\n     editdate(document.Search.srchDate)  ) ";
	print "\n     return true;";
	print "\n    }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Source Number","srchDocument","","operDocument","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Source Code Description","srchCode","","operCode","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Amount","srchPayment","","operPayment","opersel_num_short","N","15","17");
	Build_AdvSrch_Entry("Date","srchDate","","operDate","opersel_num_short","D","6","6");

	$focusField = "srchDocument";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Document")   {$orby = array(array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Payment")    {$orby = array(array("BDAMT","A","Amount"),array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Date")       {$orby = array(array("BDDTE","A","Date"),array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "SourceCode") {$orby = array(array("BSDESCU","A","Source Code"),array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Order")      {$orby = array(array("BDSEQ","A","Sequence Number"),array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Posted")     {$orby = array(array("YPAMT","A","Posted Amount Paid"),array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Pending")     {$orby = array(array("PEAMT","A","Pending Payment Amount"),array("BDSRCN","A","Source Number"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("trim(BDSRCN) ", "Source Number", $_POST['srchDocument'], "U", $_POST['operDocument'], "A");
	$returnValue=Build_WildCard("upper(BSDESC)", "Source Code Description", $_POST['srchCode'], "U", $_POST['operCode'], "A");
	$returnValue=Build_WildCard("BDAMT ", "Amount", $_POST['srchPayment'], "", $_POST['operPayment'], "N");
	$returnValue=Build_WildCard("BDDTE", "Date", $_POST['srchDate'], "", $_POST['operDate'], "D");
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
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectDocument(docNumber, docAmount){ ";
print "\n   window.opener.document.$docName.$fldDocNumber.value = docNumber; ";
print "\n   window.opener.document.$docName.$fldDocAmount.value = docAmount; ";
if ($forceChange=="Y") {print "\n   window.opener.document.getElementById('$fldDocNumber').onchange(); ";}
print "\n   window.opener.document.$docName.$fldDocNumber.focus(); ";
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

require 'ApplCashBatchRetInfoInclude.php';

print "\n <table $contentTable><tr> ";
print "\n <td> ";
print "\n <div>";
print "\n     <table $contentTable> ";
Format_Header_Hover("Batch", $fromBatchNumber, $F_fromBatchDate,"batchSelection");
Format_Header("Bank", $bankName, $fromBatchBank);
print "\n     </table> ";
print "\n </div>";
print "\n <div id=\"batchSelection\" class=\"moreInfo\">{$batchInfo}</div>";
print "\n </td> ";
print "\n </tr></table> ";

$uv_BankName ="BDBCHB";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select BDSRCN,BDSRCC,BDAMT,BDSEQ,BDDTE,";
$stmtSQL .= " Coalesce((Select Sum(YPAMT) from ARYPTD Where (YPBCH,YPBDAT,YPBANK,YPCHK)=(a.BDBCHN,a.BDBCHD,a.BDBCHB,a.BDSRCN) and (YPGDED='G' or YPPYCD in (Select PYPYCD from ARPYCD Where PYTYPE not in ('J','Y','D')))),0) as YPAMT, ";
$stmtSQL .= " Coalesce((Select Sum(PEAMT) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB)=(a.BDBCHN,a.BDBCHD,a.BDBCHB) and a.BDSRCN=(Case When PEID=$CRMSCC Then PEMCHK Else PECHK End) and (PEGDED='G' or PESBCD in (Select PSSBCD from ARPYCD inner join ARPYSB on PSPYCD=PYPYCD Where PYTYPE not in ('J','Y','D')))),0) as PEAMT, ";
$stmtSQL .= " Coalesce(BSDESC,' ') as BSDESC, Coalesce(upper(BSDESC),' ') as BSDESCU ";
$fileSQL .= " ARDEPD a";
$fileSQL .= " left join ARDSRC   on BSSRCC=BDSRCC ";
$selectSQL .= " (BDBCHN,BDBCHD,BDBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
if (!$viewCheckBox[0] || !$viewCheckBox[1]) {
	if (!$viewCheckBox[0] && !$viewCheckBox[1]) {$selectSQL .= " and BDSRCC<>BDSRCC ";
	} else {
		$viewCheckSQL = Build_CheckBoxSQL($viewCheckBoxDef, $viewCheckBox);
		if ($selectSQL == "") {$selectSQL  = $viewCheckSQL;
		} else                {$selectSQL .= " and $viewCheckSQL ";}
	}
}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt = "";
$qsOpt .= "\n <option value=\"trim(BDSRCN)|null|Source Number|A|U\" title=\"Source Number\" SELECTED>Source Number";
$qsOpt .= "\n <option value=\"BDAMT|null|Amount|N|\" title=\"Amount\">Amount";
$qsOpt .= "\n <option value=\"BDDTE|DATE|Date|D|\" title=\"Date\">Date";
$qsOpt .= "\n <option value=\"upper(BSDESC)|null|Source Code Description|A|U\" title=\"Source Code Description\">Source Code Description";
$qsOpt .= "\n <option value=\"BDSEQ|null|Sequence Number|N|\" title=\"Sequence Number\">Sequence Number";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("BDSRCN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Document\"      title=\"Sequence By Source Number\">{$sortPoint}Source Number</a></th>";
$returnValue=OrderBy_Sort("BDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Payment\"       title=\"Sequence By Amount, Source Number\">{$sortPoint}Amount</a></th>";
$returnValue=OrderBy_Sort("BDDTE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Date\"    title=\"Sequence By Date, Source Number\">{$sortPoint}Date</a></th>";
$returnValue=OrderBy_Sort("BSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SourceCode\"       title=\"Sequence By Source Code, Source Number\">{$sortPoint}Source Code</a></th>";
$returnValue=OrderBy_Sort("BDSEQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Order\"      title=\"Sequence By Sequence Number, Source Number\">{$sortPoint}Sequence Number</a></th>";
$returnValue=OrderBy_Sort("YPAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Posted\"     title=\"Sequence By Posted Amount Paid, Source Number\">{$sortPoint}Posted Amount Paid</a></th>";
$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Pending\"    title=\"Sequence By Pending Payment Amount, Source Number\">{$sortPoint}Pending Payment Amount</a></th>";
print "\n <th class=\"colhdr\">Remaining Balance</th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	$F_BDDTE=Format_Date($row['BDDTE'], "D");
	$F_RemainAmt=$row['BDAMT']-$row['YPAMT']-$row['PEAMT'];

	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	print "\n <td class=\"colalph\"><a href=\"javascript:selectDocument('$row[BDSRCN]',$row[BDAMT])\" title=\"Select Source Number\">$row[BDSRCN]</a></td> ";
	print "\n <td class=\"colnmbr\">" . number_format($row['BDAMT'],2) . "</td>";
	print "\n <td class=\"colnmbr\">$F_BDDTE</td>";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BDSRCC]\">$row[BSDESC]</span></td>";
	print "\n <td class=\"colnmbr\">$row[BDSEQ]</td>";
	print "\n <td class=\"colnmbr\">" . number_format($row['YPAMT'],2) . "</td>";
	print "\n <td class=\"colnmbr\">" . number_format($row['PEAMT'],2) . "</td>";
	print "\n <td class=\"colnmbr\">" . number_format($F_RemainAmt,2) . "</td>";
	print "\n </tr>";

	$startRow ++;
	$rowCount ++;
}
if ($rowCount == 0){require 'NoRecordsFound.php';}
print "</table>";

require_once 'PageBottom.php';
require_once 'WildCardPrint.php';

print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
