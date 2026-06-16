<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromType     = $_GET['fromType'];     if (is_null($fromType)) {$fromType="C";}
$fromID       = $_GET['fromID'];
$fromCurrency = $_GET['fromCurrency']; if (is_null($fromCurrency)) {$fromCurrency="";}
$fromCategory = $_GET['fromCategory']; if (is_null($fromCategory)) {$fromCategory="";}

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "A/R Balance";
$scriptName     = "ARBalanceInquiry.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromCurrency=" . urlencode(trim($fromCurrency)) . "&amp;fromCategory=" . urlencode(trim($fromCategory));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$medIcon        = "Y";

if     ($fromCategory=="") {$dftOrderBy = array(array("FLDESCU","A","Category"));}
elseif ($fromType=="P")    {$dftOrderBy = array(array("CMCNA1U","A","Customer"));}
elseif ($HDMCRL>0 && $CRPRMC=="Y" && $fromCurrency=="") {$dftOrderBy = array(array("CYDESCU","A","Currency"));}
else {$dftOrderBy     = array(array("ARCARB","A","A/R Balance"));}

if ($HDMCRL<=0 || $CRPRMC!="Y") {$fromCategory="I";}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Category") {$orby = array(array("FLDESCU","A","Category"));}
	elseif ($sequence == "Customer") {$orby = array(array("CMCNA1U","A","User"));}
	elseif ($sequence == "Currency") {$orby = array(array("CYDESCU","A","Currency"));}
	elseif ($sequence == "Balance")  {$orby = array(array("ARCARB","A","A/R Balance"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require $inquiryBanner;
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";
if ($formatToPrint != "Y") {
	print "\n <td class=\"toolbar\"> ";
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	require 'CloseWindow.php';
	print "\n </td> ";
}
print "\n </tr> </table> ";

require 'ApplCashCustomerRetInfoInclude.php';
print "\n <div>";
print "\n <table $contentTable> ";
Format_Header_Hover($idText, $idName, $fromID,"payerSelection");
if ($fromCurrency!="") {
	$currencyDesc=RetValue("CYTYPE='$fromCurrency'", "HDCTYP", "CYDESC");
	Format_Header("Currency", $currencyDesc, $fromCurrency);
}
if ($fromCategory!="" && $HDMCRL>0 && $CRPRMC=="Y") {
	$categoryDesc=RetValue("(FLTYPE,FLVALU)=('ARBALCAT','$fromCategory')", "SYFLAG", "FLDESC");
	Format_Header("Category", $categoryDesc, $fromCategory);
}
print "\n </table> ";
print "\n </div>";
print "\n <div id=\"payerSelection\" class=\"moreInfo\">{$payerInfo}</div>";

print $inquiryhrTagAttr;
$uv_CustomerName ="CMCUST";
$uv_CustomerClassName ="CMCCLS";
$uv_RegionName ="CMCRGN";
if ($fromType=="P")  {$uv_PayerName="PCPAYR";}
require 'UserView.php';

require 'stmtSQLClear.php';
if ($fromType=="P")  {$stmtSQL .= " Select PCCUST as CMCUST,coalesce(CMCNA1,' ') as CMCNA1, coalesce(CMCNA1U,' ') as CMCNA1U,";}
else                {$stmtSQL .= " Select CMCUST,";}
$stmtSQL .= " coalesce(ARCTYP,'$fromCategory') as ARCTYP,coalesce(ARCURT,'$fromCurrency') as ARCURT,coalesce(ARCARB,0) as ARCARB ";
if ($fromCategory=="") {$stmtSQL .= " ,coalesce(FLDESC,' ') as FLDESC, coalesce(Upper(FLDESC),' ') as FLDESCU";}
if ($HDMCRL>0 && $CRPRMC=="Y" && $fromCurrency=="") {$stmtSQL .= " ,coalesce(CYDESC,' ') as CYDESC, coalesce(Upper(CYDESC),' ') as CYDESCU ";}
if ($fromType=="P")  {$fileSQL .= " ARPYRC left join HDCUST on CMCUST=PCCUST ";}
else                {$fileSQL .= " HDCUST ";}

$fileSQL .= " left join HDCARB on ARCUST=CMCUST ";
if ($fromCategory!="") {$fileSQL .= " and ARCTYP='$fromCategory' ";}
if ($fromCurrency!="") {$fileSQL .= " and ARCURT='$fromCurrency' ";}

if ($fromCategory=="") {$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('ARBALCAT',ARCTYP) ";}
if ($HDMCRL>0 && $CRPRMC=="Y") {$fileSQL .= " left join HDCTYP on CYTYPE=ARCURT ";}

if ($fromType=="P") {$selectSQL .= " PCPAYR=$fromID ";}
else                {$selectSQL .= " CMCUST=$fromID ";}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint != "Y" && ($fromCategory=="" || $HDMCRL>0 && $CRPRMC=="Y" && $fromCurrency=="" || $fromType=="P")) {
    $advanceSearch = "N";
	$qsOpt = "";
	if ($fromCategory=="") {$qsOpt .= "\n <option value=\"Upper(FLDESC)|null|Category Description|A|U\" title=\"Category Description\">Category Description";}
	if ($fromType=="P") {$qsOpt .= "\n <option value=\"CMCNA1U|null|Customer Name|A|U\" title=\"Customer Name\">Customer Name";}
	if ($HDMCRL>0 && $CRPRMC=="Y" && $fromCurrency=="") {$qsOpt .= "\n <option value=\"Upper(CYDESC)|null|Currency Description|A|U\" title=\"Currency Description\">Currency Description";}
	$qsOpt .= "\n <option value=\"ARCARB|null|A/R Balance|N|\" title=\"A/R Balance\" SELECTED>A/R Balance";
	require 'QuickSearchOption.php';
}

print "<table $contentTable> <tr>";
if ($fromCategory=="") {
	$returnValue=OrderBy_Sort("FLDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Category\"      title=\"Sequence By Category\">{$sortPoint}Category</a></th>";
}
if ($fromType=="P")    {
	$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Customer\"      title=\"Sequence By Customer\">{$sortPoint}Customer</a></th>";
}
if ($HDMCRL>0 && $CRPRMC=="Y" && $fromCurrency=="") {
	$returnValue=OrderBy_Sort("CYDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Currency\"      title=\"Sequence By Currency\">{$sortPoint}Currency</a></th>";
}
$returnValue=OrderBy_Sort("ARCARB"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Balance\"   title=\"Sequence By A/R Balance\">{$sortPoint}A/R Balance</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';

	print "\n <tr class=\"$rowClass\">";
	if ($fromCategory=="")                              {print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[ARCTYP]\">$row[FLDESC]</span></td>";}
	if ($fromType=="P")                                 {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['CMCUST'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[CMCUST]]\">$row[CMCNA1]</a></td> ";}
	if ($HDMCRL>0 && $CRPRMC=="Y" && $fromCurrency=="") {print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[ARCURT]\">$row[CYDESC]</span></td>";}
	print "\n     <td class=\"colnmbr\">" . number_format($row['ARCARB'],2) . "</td>";
	print "\n</tr>";

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print $inquiryhrTagAttr;
if ($formatToPrint != "Y") {require 'CloseWindow.php';}
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require $inquiryTrailer;
print "\n </body> \n </html>";
?>	
