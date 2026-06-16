<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName     = $_GET['docName'];
$periodFld   = $_GET['periodFld'];
$begDateFld  = $_GET['begDateFld'];
$endDateFld  = $_GET['endDateFld'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Period Begin And End Dates Search";
$scriptName     = "PeriodSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;periodFld=" . urlencode(trim($periodFld)). "&amp;begDateFld=" . urlencode(trim($begDateFld)) . "&amp;endDateFld=" . urlencode(trim($endDateFld));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("PDPER#","D","Period"));
$advanceSearch  = "N";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Period")   {$orby = array(array("PDPER#","A","Period"));}
	elseif ($sequence == "BegDate")  {$orby = array(array("PDBDAT","A","Begin Date"));}
	elseif ($sequence == "EndDate")  {$orby = array(array("PDEDAT","A","End Date"));}
	elseif ($sequence == "Days")     {$orby = array(array("PDWKDY","A","Working Days"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";

require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'DateEdit.php';
require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectPeriod(period,begDate,endDate){ ";
if ($periodFld)  {print "\n window.opener.document.$docName.$periodFld.value = period; ";}
if ($begDateFld) {print "\n window.opener.document.$docName.$begDateFld.value = begDate; ";}
if ($endDateFld) {print "\n window.opener.document.$docName.$endDateFld.value = endDate; ";}
print "\n window.opener.document.$docName.$periodFld.focus(); ";
print "\n window.close(); ";
print "\n } ";
print "\n </script>";

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
$stmtSQL .= " Select PDPER#, PDBDAT, PDEDAT, PDWKDY ";
$fileSQL .= " HDPBED ";
if ($wildCardSearch!="") {$selectSQL="PDPER#<>0 ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"PDPER#|null|Period Number|DP|\" title=\"Period Number\" SELECTED>Period Number";
$qsOpt .= "\n <option value=\"PDBDAT|DATE|Begin Date|D|\" title=\"Begin Date\">Begin Date";
$qsOpt .= "\n <option value=\"PDEDAT|DATE|End Date|D|\" title=\"End Date\">End Date";
$qsOpt .= "\n <option value=\"PDWKDY|null|Working Days|N|\" title=\"Working Days\">Working Days";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("PDPER#"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Period\"      title=\"Sequence By Period\">{$sortPoint}Period</a></th>";
$returnValue=OrderBy_Sort("PDBDAT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BegDate\"  title=\"Sequence By Begin Date\">{$sortPoint}Begin Date</a></th>";
$returnValue=OrderBy_Sort("PDEDAT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EndDate\"  title=\"Sequence By End Date\">{$sortPoint}End Date</a></th>";
$returnValue=OrderBy_Sort("PDWKDY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Days\"  title=\"Sequence By Working Days\">{$sortPoint}Working<br>Days</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$R_PDPER=PeriodInputFromCYP($row['PDPER#']);
	$R_PDBDAT=DateInputFromCYMD($row['PDBDAT']);
	$R_PDEDAT=DateInputFromCYMD($row['PDEDAT']);
	$F_PDPER=PeriodFromCYP($row['PDPER#']);
	$F_PDBDAT=Format_Date($row['PDBDAT'],"D");
	$F_PDEDAT=Format_Date($row['PDEDAT'],"D");
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectPeriod('" . trim($R_PDPER) . "','" . trim($R_PDBDAT) . "','" . trim($R_PDEDAT) . "')\" title=\"Select Period\">$F_PDPER</a></td> ";
	print "\n     <td class=\"coldate\">$F_PDBDAT</td>";
	print "\n     <td class=\"coldate\">$F_PDEDAT</td>";
	print "\n     <td class=\"colcode\">{$row['PDWKDY']}</td>";
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
