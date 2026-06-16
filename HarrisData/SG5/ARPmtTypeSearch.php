<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName            = $_GET['docName'];
$fldName            = $_GET['fldName'];
$fldDesc            = $_GET['fldDesc'];
$displayAuto        = $_GET['displayAuto'];
$pmtType            = $_GET['pmtType'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Payment Type Search";
$scriptName     = "ARPmtTypeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;displayAuto=" . urlencode(trim($displayAuto)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("CPTYPE","A","Payment Type"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srcPgmOptSeq, 2, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Payment Type","srchPmtType","","operPmtType","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Program Option Security Sequence","srcPgmOptSeq","","operPgmOptSeq","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Transaction Type","srchTransType","","operTransType","opersel_alph_short","A","1","1");

	$focusField = "srchPmtType";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "PmtType")      {$orby = array(array("CPTYPE","A","Payment Type"));}
	elseif ($sequence == "Description")  {$orby = array(array("CPDESCU","A","Description"),array("CPTYPE","A","Payment Type"));}
	elseif ($sequence == "PgmOpt")       {$orby = array(array("CPPOPT","A","Program Option Security Sequence"),array("CPTYPE","A","Payment Type"));}
	elseif ($sequence == "TransType")    {$orby = array(array("CPTRNT","A","Transaction Type"),array("CPTYPE","A","Payment Type"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("CPTYPE ", "Payment Type", $_POST['srchPmtType'], "U", $_POST['operPmtType'], "A");
	$returnValue=Build_WildCard("CPDESCU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("CPPOPT ", "Program Option Security Sequence", $_POST['srcPgmOptSeq'], "", $_POST['operPgmOptSeq'], "N");
	$returnValue=Build_WildCard("CPTRNT", "Transaction Type", $_POST['srchTransType'], "U", $_POST['operTransType'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectPmtType(pmtType,pmtTypeDesc){ ";
print "\n   window.opener.document.$docName.$fldName.value = pmtType; ";
print "\n   if      (window.opener.document.$docName.$fldDesc)          {window.opener.document.$docName.$fldDesc.value = pmtTypeDesc;} ";
print "\n   else if (window.opener.document.getElementById('$fldDesc')) {window.opener.document.getElementById('$fldDesc').innerHTML = pmtTypeDesc;} ";
print "\n   window.opener.document.$docName.$fldName.focus(); ";
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

require 'stmtSQLClear.php';
$stmtSQL .= " Select CPTYPE,CPDESC,CPPOPT,CPTRNT,CPDESCU ";
$fileSQL .= " ARPAYT ";
if     ($displayAuto!="Y")   {$selectSQL .= " CPTYPE<>'A' ";}
elseif ($wildCardSearch!="") {$selectSQL .= " CPTYPE=CPTYPE ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt = "";
$qsOpt .= "\n <option value=\"CPTYPE|null|Payment Type|A|U\" title=\"Payment Type\">Payment Type";
$qsOpt .= "\n <option value=\"CPDESCU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
$qsOpt .= "\n <option value=\"CPPOPT|null|Program Option Security Sequence|N|\" title=\"Program Option Security Sequence\">Program Option Security Sequence";
$qsOpt .= "\n <option value=\"CPTRNT|null|Transaction Type|A|U\" title=\"Transaction Type\">Transaction Type";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("CPTYPE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PmtType\"      title=\"Sequence By Payment Type\">{$sortPoint}Payment Type</a></th>";
$returnValue=OrderBy_Sort("CPDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\"       title=\"Sequence By Description, Payment Type\">{$sortPoint}Description</a></th>";
$returnValue=OrderBy_Sort("CPPOPT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PgmOpt\"      title=\"Sequence By Program Option Security Sequence, Payment Type\">{$sortPoint}Program Option Security Sequence</a></th>";
$returnValue=OrderBy_Sort("CPTRNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TransType\"      title=\"Sequence By Transaction Type, Payment Type\">{$sortPoint}Transaction Type</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Desc=Format_Quote($row['CPDESC']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colcode\"><a href=\"javascript:selectPmtType('" . trim($row['CPTYPE']) . "','" . trim($F_Desc) . "')\" title=\"Select Payment Type\">$row[CPTYPE]</a></td> ";
	print "\n     <td class=\"colalph\">$row[CPDESC]</td>";
	print "\n     <td class=\"colcode\">$row[CPPOPT]</td>";
	print "\n     <td class=\"colcode\">$row[CPTRNT]</td>";
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
