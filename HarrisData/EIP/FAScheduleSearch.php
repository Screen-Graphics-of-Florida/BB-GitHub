<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$moreSchedule = $_GET['moreSchedule'];

require_once 'SetLibraryList.php';

require_once "FAControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "F/A Schedule Search";
$scriptName     = "FAScheduleSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("SMDESCU","A","Description"),array("SMSCHD","A","Schedule"));

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
	print "\n
	   function validate(searchForm) {
	      if ( editNum(document.Search.srchSchedule, 2, 0) )
	         return true;
	      }
	      ";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Schedule","srchSchedule","","operSchedule","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Description","srchDescription","","operDescription","opersel_alph_short","A","30","30");

	$focusField = "srchSchedule";
	require_once 'AdvSearchBottom.php';
   }

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if ($sequence == "Schedule") {
	   $orby=array(array("SMSCHD","A","Schedule"));
	   }
	elseif ($sequence=="Description") {
	   $orby=array(array("SMDESCU","A","Description"),array("SMSCHD","A","Schedule"));
	   }
	require_once 'OrderByUpdate.php';
   }

if ($tag == "QSEARCH"){
   require_once 'QuickSearch.php';
   }

if ($tag=="WILDCARD"){
	$andOr=$_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("SMSCHD", "Schedule", $_POST['srchSchedule'], "U", $_POST['operSchedule'], "N");
	$returnValue=Build_WildCard("upper(SMDESC)", "Description", $_POST['srchDescription'], "U", $_POST['operDescription'], "A");
	require_once 'WildCardUpdate.php';
   }

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectSchedule(schedule,description){ ";
print "\n window.opener.document.$docName.$fldName.value = schedule; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = description;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = description;}";
print "\n window.opener.document.$docName.$fldName.focus(); ";
print "\n window.close(); ";
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
$stmtSQL .= " Select FASCHD.*, SMSCHD, upper(SMDESC) as SMDESCU, SMALPH, SML179, SMLSPC  ";
$fileSQL .= " FASCHD ";
if ($moreInfo=="Y") {
   $selectSQL.=" SMSCHD=$moreSchedule ";
   }
elseif ($wildCardSearch!="" || $uv_Sql!="") {
   $selectSQL="SMSCHD<>0 ";
   }
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {
   $stmtSQL.=" Order By $orderBy ";
   }
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"SMSCHD|null|Schedule|N|\" title=\"Schedule\">Schedule";
	$qsOpt .= "\n <option value=\"upper(SMDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";

	$returnValue=OrderBy_Sort("SMSCHD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Schedule\" title=\"Sequence By Schedule\">{$sortPoint}Schedule</a></th>";
	$returnValue=OrderBy_Sort("SMDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Schedule\">{$sortPoint}Description</a></th>";
	print "\n </tr>";

	$rowcount=0;
	$startrow=1;
	while ($row = db2_fetch_assoc($sqlResult, $startRow) and ($rowcount<$dspMaxRows)){
		require  'SetRowClass.php';
		$F_Description=Format_Quote($row['SMDESC']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colcode\">$row[SMSCHD]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectSchedule('" . trim($row['SMSCHD']) . "','" . trim($F_Description) . "')\" title=\"Select Schedule\">$F_Description</a></td> ";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreSchedule=" . urlencode(trim($row['SMSCHD'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";
		$startrow++;
		$rowcount++;
      }
	if ($rowcount==0) {
	   require 'NoRecordsFound.php';
	   }
	print "</table>";
   }
else {
	$row=db2_fetch_assoc($sqlResult);
	$F_Description=Format_Quote($row['SMDESC']);
	$moreInfoSelect = "href=\"javascript:selectSchedule('" . trim($row['SMSCHD']) . "','" . trim($F_Description) . "')\" title=\"Select Schedule\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Schedule",$row[SMSCHD],"","N");
	Build_DspFld("Description",$row[SMDESC],"","A");
	Build_DspFld("Alpha Sequence",$row[SMALPH],"","A");
	Build_DspFld("Section 179 Expense Limit",$row[SML179],"","N");
	Build_DspFld("Special Allowance Limit",$row[SMLSPC],"","N");

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
