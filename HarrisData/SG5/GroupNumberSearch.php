<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName	=	(isset($_GET['docName']))	?	$_GET['docName']	:	"";
$fldName	= 	(isset($_GET['fldName']))	?	$_GET['fldName']	:	"";
$fldDesc	=	(isset($_GET['fldDesc']))	?	$_GET['fldDesc']	:	"";

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Group Number Search";
$scriptName     = "GroupNumberSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("BBPLNT","A","Plant"),array("BBDESCU","A","Group Description"));

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
	print "\n if (editNum(document.Search.srchPlant, 3, 0) && ";
	print "\n     editNum(document.Search.srchGroup, 5, 0) && ";
	print "\n     editNum(document.Search.srchSchd, 3, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Plant Number","srchPlant","","operPlant","opersel_num_short","N","5","3");
	Build_AdvSrch_Entry("Group Number","srchGroup","","operGroup","opersel_num_short","N","5","5");
	Build_AdvSrch_Entry("Group Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Department","srchDept","","operDept","opersel_alph_short","A","5","5");
	Build_AdvSrch_Entry("Work Center","srchWc","","operWc","opersel_alph_short","A","5","5");
	Build_AdvSrch_Entry("Schedule","srchSchd","","operSchd","opersel_num_short","N","5","3");

	$focusField = "srchPlant";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if     ($sequence == "Plant") 		{$orby = array(array("BBPLNT","A","Plant"),array("BBDESCU","A","Group Description"));}
	elseif ($sequence == "Group")		{$orby = array(array("BBGRP#","A","Group"));}
	elseif ($sequence == "Description")	{$orby = array(array("BBDESCU","A","Group Description"));}
	elseif ($sequence == "Department")	{$orby = array(array("BBDEPT","A","Department"),array("BBWC","A","Work Center"));}
	elseif ($sequence == "WorkCenter")	{$orby = array(array("BBWC","A","Work Center"));}
	elseif ($sequence == "Schedule")	{$orby = array(array("BBSCHD","A","Schedule"),array("BBDEPT","A","Department"),array("BBWC","A","Work Center"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {require_once 'QuickSearch.php';}

if ($tag == "WILDCARD") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("BBPLNT", "Plnt Number", $_POST['srchPlant'], "", $_POST['operPlant'], "N");
	$returnValue=Build_WildCard("BBGRP#", "Group Number", $_POST['srchGroup'], "", $_POST['operGroup'], "N");
	$returnValue=Build_WildCard("upper(BBDESC)", "Group Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("BBDEPT", "Department", $_POST['srchDept'], "U", $_POST['operDept'], "A");
	$returnValue=Build_WildCard("BBWC", "Work Center", $_POST['srchWc'], "U", $_POST['operWc'], "A");
	$returnValue=Build_WildCard("BBSCHD", "Schedule", $_POST['srchSchd'], "", $_POST['operSchd'], "N");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectGroup(groupNumber,groupName){ ";
print "\n window.opener.document.$docName.$fldName.value = groupNumber; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = groupName;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = groupName;}";
print "\n window.opener.document.$docName.$fldName.focus(); ";
print "\n window.close(); ";
print "\n } ";

require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
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

$uv_PlantName ="BBPLNT";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select BBGRP#, BBDESC, BBPLNT, BBDEPT, BBWC, BBSCHD, ";
$stmtSQL .= " upper(BBDESC) as BBDESCU ";
$fileSQL .= " HDGRPM ";
if ($wildCardSearch!="") {$selectSQL="BBGRP#<>0 ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"BBGRP#|null|Group|N|\" title=\"Group\">Group";
$qsOpt .= "\n <option value=\"upper(BBDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
$qsOpt .= "\n <option value=\"BBPLNT|null|Plant|N|\" title=\"Plant\">Plant";
$qsOpt .= "\n <option value=\"BBDEPT|null|Department|A|U\" title=\"Department\">Department";
$qsOpt .= "\n <option value=\"BBWC|null|WorkCenter|A|U\" title=\"Work Center\">Work Center";
$qsOpt .= "\n <option value=\"BBSCHD|null|Schedule|N|\" title=\"Schedule\">Schedule";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("BBPLNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Plant\" title=\"Sequence By Plant Number\">{$sortPoint}Plant Number</a></th>";
$returnValue=OrderBy_Sort("BBGRP#"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Group\" title=\"Sequence By Group Number\">{$sortPoint}Group Number</a></th>";
$returnValue=OrderBy_Sort("BBDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Group Description\">{$sortPoint}Description</a></th>";
$returnValue=OrderBy_Sort("BBDEPT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Department\" title=\"Sequence By Department\">{$sortPoint}Department</a></th>";
$returnValue=OrderBy_Sort("BBWC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=WorkCenter\" title=\"Sequence By Work Center\">{$sortPoint}Work Center</a></th>";
$returnValue=OrderBy_Sort("BBSCHD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Schedule\" title=\"Sequence By Schedule\">{$sortPoint}Schedule</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Description=Format_Quote($row['BBDESC']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colnmbr\">{$row['BBPLNT']}</td>";
	print "\n     <td class=\"colnmbr\">{$row['BBGRP#']}</td>";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectGroup('" . trim($row['BBGRP#']) . "','" . trim($F_Description) . "')\" title=\"Select Name\">$F_Description</a></td> ";
	print "\n     <td class=\"colalph\">{$row['BBDEPT']}</td>";
	print "\n     <td class=\"colalph\">{$row['BBWC']}</td>";
	print "\n     <td class=\"colnmbr\">{$row['BBSCHD']}</td>";
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
