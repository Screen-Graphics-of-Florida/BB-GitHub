<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$displayMenu        = $_GET['displayMenu'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Deposit Entry";
$scriptName     = "ARDepositEntryInquiry.php";
$scriptVarBase  = "{$genericVarBase}&amp;displayMenu=" . urlencode(trim($displayMenu)) . "&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("BDSEQ","A","Sequence"));
$medIcon        = "Y";

$viewCheckBoxURL = "window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgBox={checkBoxNumber}'";
$viewCheckBoxDef = array(array("View:", "Has Balance", $viewCheckBoxURL, "1", "1", "BDAMT-Coalesce((Select Sum(YPAMT) from ARYPTD Where (YPBCH,YPBDAT,YPBANK,YPCHK)=(a.BDBCHN,a.BDBCHD,a.BDBCHB,a.BDSRCN) and YPPYCD in (Select PYPYCD from ARPYCD Where PYTYPE not in ('J','Y'))),0)-Coalesce((Select Sum(PEAMT) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB)=(a.BDBCHN,a.BDBCHD,a.BDBCHB) and (PECHK=a.BDSRCN or PEMCHK=a.BDSRCN) and PESBCD in (Select PSSBCD from ARPYCD inner join ARPYSB on PSPYCD=PYPYCD Where PYTYPE not in ('J','Y'))),0)<>0"),
array("", "No Balance", $viewCheckBoxURL, "2", "1", "BDAMT-Coalesce((Select Sum(YPAMT) from ARYPTD Where (YPBCH,YPBDAT,YPBANK,YPCHK)=(a.BDBCHN,a.BDBCHD,a.BDBCHB,a.BDSRCN) and YPPYCD in (Select PYPYCD from ARPYCD Where PYTYPE not in ('J','Y'))),0)-Coalesce((Select Sum(PEAMT) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB)=(a.BDBCHN,a.BDBCHD,a.BDBCHB) and (PECHK=a.BDSRCN or PEMCHK=a.BDSRCN) and PESBCD in (Select PSSBCD from ARPYCD inner join ARPYSB on PSPYCD=PYPYCD Where PYTYPE not in ('J','Y'))),0)=0"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH"){
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchAmount, 13, 2) && ";
	print "\n     editdate(document.Search.srchDate)) ";
	print "\n     return true;";
	print "\n    }";
	print "\n </script>";

	$scriptType = "I";    // L=List, S=Search, I=Inquiry
	if ($displayMenu=="Y") {$pageID = "ARDEPOSITENTRYSEARCH";}
	else                   {$pageID = "";}
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Amount","srchAmount","","operAmount","opersel_num_short","N","15","17");
	Build_AdvSrch_Entry("Source Code Description","srchCode","","operCode","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Source Number","srchSource","","operSource","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Date","srchDate","","operDate","opersel_num_short","D","6","6");

	$focusField = "srchAmount";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Code")   {$orby = array(array("BDSRCC","A","Source Code"),array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Source") {$orby = array(array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Date")   {$orby = array(array("BDDTE","A","Date"),array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Amount") {$orby = array(array("BDAMT","A","Amount"),array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Posted") {$orby = array(array("YPAMT","A","Posted Amount Paid"),array("BDSRCN","A","Source Number"));}
	elseif ($sequence == "Pending") {$orby = array(array("PEAMT","A","Pending Payment Amount"),array("BDSRCN","A","Source Number"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("BDAMT", "Amount", $_POST['srchAmount'], "", $_POST['operAmount'], "N");
	$returnValue=Build_WildCard("Upper(BSDESC)", "Source Code Description", $_POST['srchCode'], "U", $_POST['operCode'], "A");
	$returnValue=Build_WildCard("trim(BDSRCN)", "Source Number", $_POST['srchSource'], "U", $_POST['operSource'], "A");
	$returnValue=Build_WildCard("BDDTE", "Date", $_POST['srchDate'], "", $_POST['operDate'], "D");
	require_once 'WildCardUpdate.php';
}

if (isset($_GET['chgBox'])) {require "ViewCheckBoxUpdate.php";}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';
require_once 'Menu.js';

$formName = "Search";  // Need to Calendar Include
require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
if ($displayMenu=="Y") {require_once 'Banner.php';}
else                   {require $inquiryBanner;}
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
if ($displayMenu=="Y") {
	$pageID = "ARDEPOSITENTRYINQ";
	require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";

$uv_BankName ="BDBCHB";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select BDSEQ,BDAMT,BDSRCC,BDSRCN,BDDTE ,";
$stmtSQL .= " Coalesce((Select Sum(YPAMT) from ARYPTD Where (YPBCH,YPBDAT,YPBANK,YPCHK)=(a.BDBCHN,a.BDBCHD,a.BDBCHB,a.BDSRCN) and YPPYCD in (Select PYPYCD from ARPYCD Where PYTYPE not in ('J','Y'))),0) as YPAMT, ";
$stmtSQL .= " Coalesce((Select Sum(PEAMT) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB)=(a.BDBCHN,a.BDBCHD,a.BDBCHB) and (PECHK=a.BDSRCN or PEMCHK=a.BDSRCN) and PESBCD in (Select PSSBCD from ARPYCD inner join ARPYSB on PSPYCD=PYPYCD Where PYTYPE not in ('J','Y'))),0) as PEAMT, ";
$stmtSQL .= " Coalesce(BSDESC,' ') as BSDESC, Coalesce(Upper(BSDESC),' ') as BSDESCU ";
$fileSQL .= " ARDEPD a ";
$fileSQL .= " left join ARDSRC   on BSSRCC=BDSRCC ";
$selectSQL .= " BDBCHN=$fromBatchNumber and (BDBCHD,BDBCHB)=($fromBatchDate,$fromBatchBank) ";
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

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y"){
	print "\n <td class=\"toolbar\">";
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	if ($displayMenu!="Y") {require 'CloseWindow.php';}
	print "\n </td>";
}
print "\n </tr></table>";

print "\n <table $contentTable> ";
$F_BDBCHD=Format_Date($fromBatchDate, "D");
Format_Header("Batch", $fromBatchNumber, "$F_BDBCHD");
$V_BKBKNM=RetValue("BKBANK=$fromBatchBank", "HDBANK", "BKBKNM");
Format_Header("Bank", $V_BKBKNM, $fromBatchBank);
print "\n </table> ";

if ($displayMenu=="Y") {print $hrTagAttr;}
else                   {print $inquiryhrTagAttr;}

if ($formatToPrint == ""){
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"Coalesce(Upper(BSDESC),' ')|null|Source Code Description|A|U\" title=\"Source Code Description\">Source Code Description";
	$qsOpt .= "\n <option value=\"trim(BDSRCN)|null|Source Number|A|U\" title=\"Source Number\" SELECTED>Source Number";
	$qsOpt .= "\n <option value=\"BDDTE|DATE|Date|D|\" title=\"Date\">Date";
	$qsOpt .= "\n <option value=\"BDAMT|null|Amount|N|\" title=\"Amount\">Amount";
	require 'QuickSearchOption.php';
}

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("BSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Code\"   title=\"Sequence By Source Code\">{$sortPoint}Source Code</a></th>";
$returnValue=OrderBy_Sort("BDSRCN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Source\" title=\"Sequence By Source Number\">{$sortPoint}Source Number</a></th>";
$returnValue=OrderBy_Sort("BDDTE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Date\"   title=\"Sequence By Date\">{$sortPoint}Date</a></th>";
$returnValue=OrderBy_Sort("BDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount\">{$sortPoint}Amount</a></th>";
$returnValue=OrderBy_Sort("YPAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Posted\" title=\"Sequence By Posted Amount Paid\">{$sortPoint}Posted Amount Paid</a></th>";
$returnValue=OrderBy_Sort("PEAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Pending\" title=\"Sequence By Pending Payment Amount\">{$sortPoint}Pending Payment Amount</a></th>";
print "\n <th class=\"colhdr\">Remaining Balance</th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	$F_BDDTE=Format_Date($row['BDDTE'], "D");
	$F_RemainAmt=$row['BDAMT']-$row['YPAMT']-$row['PEAMT'];
	require  'SetRowClass.php';

	print "\n <tr class=\"$rowClass\">";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BDSRCC]\">$row[BSDESC]</span></td>";
	print "\n <td class=\"colalph\">$row[BDSRCN]</td>";
	print "\n <td class=\"colnmbr\">$F_BDDTE</td>";
	print "\n <td class=\"colnmbr\">" . number_format($row['BDAMT'],2) . "</td>";
	print "\n <td class=\"colnmbr\">" . number_format($row['YPAMT'],2) . "</td>";
	print "\n <td class=\"colnmbr\">" . number_format($row['PEAMT'],2) . "</td>";
	print "\n <td class=\"colnmbr\">" . number_format($F_RemainAmt,2) . "</td>";
	print "\n</tr>";

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "\n </table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
if ($displayMenu=="Y") {print $hrTagAttr;}
else  {
	print $inquiryhrTagAttr;
	if ($formatToPrint != "Y") {require 'CloseWindow.php';}
}
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
if ($displayMenu=="Y") {require_once 'Trailer.php';}
else                   {require $inquiryTrailer;}
print "\n </body> \n </html>";
?>
