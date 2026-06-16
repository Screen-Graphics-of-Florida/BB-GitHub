<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName	=	(isset($_GET['docName']))	?	$_GET['docName']	:	"";
$fldName	= 	(isset($_GET['fldName']))	?	$_GET['fldName']	:	"";
$fldDesc	=	(isset($_GET['fldDesc']))	?	$_GET['fldDesc']	:	"";
$moreInfo	=	(isset($_GET['moreInfo']))	?	$_GET['moreInfo']	:	"";
$moreSched	=	(isset($_GET['moreSched']))	?	$_GET['moreSched']	:	0;
$moreStart	=	(isset($_GET['moreStart']))	?	$_GET['moreStart']	:	null;

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Schedule Search";
$scriptName     = "ScheduleSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("SMDESCU","A","Description"),array("SMSCHD","A","Number"));

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
	print "\n if (editNum(document.Search.srchSched, 3, 0) && ";
	print "\n     editdate(document.Search.srchStart) && ";
	print "\n     editdate(document.Search.srchEnd) && ";
	print "\n     editNum(document.Search.srchShift, 1, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Schedule","srchSched","","operSched","opersel_num_short","N","5","3");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","20","20");
	Build_AdvSrch_Entry("Effectivity Start Date","srchStart","","operStart","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("Effectivity End Date","srchEnd","","operEnd","opersel_num_short","D","6","6");
	Build_AdvSrch_Entry("Shift","srchShift","","operShift","opersel_num_short","N","5","1");

	$focusField = "srchSched";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if     ($sequence == "Schedule")	{$orby = array(array("SMSCHD","A","Schedule"));}
	elseif ($sequence == "Description")	{$orby = array(array("SMDESCU","A","Description"),array("SMSCHD","A","Schedule"));}
	elseif ($sequence == "StartDate")	{$orby = array(array("SMEFFS","A","StartDate"),array("SMSCHD","A","Schedule"));}
	elseif ($sequence == "EndDate")		{$orby = array(array("SMEFFE","A","EndDate"),array("SMSCHD","A","Schedule"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("SMSCHD", "Schedule", $_POST['srchSched'], "", $_POST['operSched'], "N");
	$returnValue=Build_WildCard("upper(SMDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("SMEFFS", "Start Date", $_POST['srchStart'], "", $_POST['operStart'], "IN");
	$returnValue=Build_WildCard("SMEFFE", "End Date", $_POST['srchEnd'], "", $_POST['operEnd'], "IN");
	$returnValue=Build_WildCard("SMSHFT", "Shift", $_POST['srchShift'], "", $_POST['operShift'], "N");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectSchedule(scheduleNumber,scheduleDescription){ ";
print "\n window.opener.document.$docName.$fldName.value = scheduleNumber; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = scheduleDescription;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = scheduleDescription;}";
print "\n window.opener.document.$docName.$fldName.focus(); ";
print "\n window.close(); ";
print "\n } ";

require_once 'AJAXRequest.js';
require_once 'DateEdit.php';
require_once 'CalendarInclude.php';
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

$uv_ScheduleName ="SMSCHD";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDSCHM.*, upper(SMDESC) as SMDESCU ";
$fileSQL .= " HDSCHM ";
if ($moreInfo=="Y" && $moreStart=="") {$selectSQL .= " SMSCHD=$moreSched  and SMEFFS is null ";}
elseif ($moreInfo=="Y")               {$selectSQL .= " SMSCHD=$moreSched  and SMEFFS='$moreStart' ";}
elseif ($wildCardSearch!="" || $uv_Sql != "") {$selectSQL="SMSCHD<>0 ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"SMSCHD|null|Schedule|N|\" title=\"Schedule\">Schedule";
	$qsOpt .= "\n <option value=\"upper(SMDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"SMEFFS|DATE|Start Date|I|\" title=\"Start Date\">Start Date";
	$qsOpt .= "\n <option value=\"SMEFFE|DATE|End Date|I|\" title=\"End Date\">End Date";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("SMSCHD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Schedule\" title=\"Sequence By Schedule\">{$sortPoint}Schedule</a></th>";
	$returnValue=OrderBy_Sort("SMDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Schedule\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("SMEFFS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StartDate\" title=\"Sequence By Start Date, Schedule\">{$sortPoint}Start<br>Date</a></th>";
	$returnValue=OrderBy_Sort("SMEFFE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EndDate\" title=\"Sequence By End Date, Schedule\">{$sortPoint}End<br>Date</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Description=Format_Quote($row['SMDESC']);
		$F_EFFS=Format_Date_ISO($row['SMEFFS'], "D") ;
		$F_EFFE=Format_Date_ISO($row['SMEFFE'], "D") ;
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">{$row['SMSCHD']}</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectSchedule('" . trim($row['SMSCHD']) . "','" . trim($F_Description) . "')\" title=\"Select Name\">$F_Description</a></td> ";
		print "\n     <td class=\"coldate\">$F_EFFS</td>";
		print "\n     <td class=\"coldate\">$F_EFFE</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreSched=" . urlencode(trim($row['SMSCHD'])) . "&amp;moreStart=" . urlencode(trim($row['SMEFFS'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Description=Format_Quote($row['SMDESC']);
	$moreInfoSelect = "href=\"javascript:selectSchedule('" . trim($row['SMSCHD']) . "','" . trim($F_Description) . "')\" title=\"Select Schedule\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	Build_DspFld("Schedule",$row['SMSCHD'],"","N");
	$F_EFFS=Format_Date_ISO($row['SMEFFS'], "D") ;
	Build_DspFld("Effectivity Start Date",$F_EFFS,"","D");
	$F_EFFE=Format_Date_ISO($row['SMEFFE'], "D") ;
	Build_DspFld("Effectivity End Date",$F_EFFE,"","D");
	Build_DspFld("Description",$row['SMDESC'],"","");
	Build_DspFld("Shift",$row['SMSHFT'],"","N");
	Build_DspFld("Work Day Based On",$row['SMWKDB'],"","");
	Build_DspFld("Round Run Time To Shift",$row['SMRRTS'],"","");
	Build_DspFld("Round Lunch Time To Schedule",$row['SMRLTS'],"","");
	print "\n </table> ";

	print "\n <table $contentTable> ";
	print "\n   <tr><th></th>";
	print "\n       <th class=\"grphdr\" colspan=\"2\">Shift Start</th>";
	print "\n       <th class=\"grphdr\" colspan=\"2\">Shift End</th>";
	print "\n   </tr>";

	print "\n   <tr><th></th>";
	print "\n       <th class=\"colhdr\">   Early  </th>";
	print "\n       <th class=\"colhdr\">   Late   </th>";
	print "\n       <th class=\"colhdr\">   Early  </th>";
	print "\n       <th class=\"colhdr\">   Late   </th>";
	print "\n   </tr>";

	print "\n   <tr><td class=\"dsphdr\">Grace Period (Minutes)</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMGPBS']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMGPAS']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMGPBE']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMGPAE']}</td>";
	print "\n   </tr>";
	
	print "\n   <tr><td class=\"dsphdr\">Round To Schedule</td>";
	print "\n       <td class=\"colcode\">{$row['SMRTBS']}</td>";
	print "\n       <td class=\"colcode\">{$row['SMRTAS']}</td>";
	print "\n       <td class=\"colcode\">{$row['SMRTBE']}</td>";
	print "\n       <td class=\"colcode\">{$row['SMRTAE']}</td>";
	print "\n   </tr>";
	
	print "\n   <tr><td class=\"dsphdr\">Beyond Grace Period Exception</td>";
	print "\n       <td class=\"colcode\">{$row['SMPEBS']}</td>";
	print "\n       <td class=\"colcode\">{$row['SMPEAS']}</td>";
	print "\n       <td class=\"colcode\">{$row['SMPEBE']}</td>";
	print "\n       <td class=\"colcode\">{$row['SMPEAE']}</td>";
	print "\n   </tr>";
	
	print "\n   <tr><td class=\"dsphdr\">Round To Value</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMRVBS']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMRVAS']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMRVBE']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMRVAE']}</td>";
	print "\n   </tr>";
	
	print "\n   <tr><td class=\"dsphdr\">Rounding Split (Minutes)</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMRSBS']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMRSAS']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMRSBE']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMRSAE']}</td>";
	print "\n   </tr>";
	
	print "\n   <tr><td class=\"dsphdr\">Lunch Grace Period (Minutes)</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMLGBS']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMLGAS']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMLGBE']}</td>";
	print "\n       <td class=\"colnmbr\">{$row['SMLGAE']}</td>";
	print "\n   </tr>";

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
