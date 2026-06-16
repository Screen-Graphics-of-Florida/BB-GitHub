<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$moreTermsCode = $_GET['moreTermsCode'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Terms Code Search";
$scriptName     = "TermsCodeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("TMCTDSU","A","Description"),array("TMCTRM","A","Terms Code"));

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
	print "\n if (editNum(document.Search.srchDaysDue, 3, 0) && ";
	print "\n     editNum(document.Search.srchDaysDisc, 3, 0) && ";
	print "\n     editNum(document.Search.srchDisc, 3, 1) && ";
	print "\n     editdate(document.Search.srchDueDate) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Terms Code","srchCode","","operCode","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Days Till Due","srchDaysDue","","operDaysDue","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("Days For Discount","srchDaysDisc","","operDaysDisc","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("Discount Percent","srchDisc","","operDisc","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("Due Date","srchDueDate","","operDueDate","opersel_num_short","D","6","6");

	$focusField = "srchCode";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Code")    {$orby = array(array("TMCTRM","A","Terms Code"));}
	elseif ($sequence == "Desc")    {$orby = array(array("TMCTDSU","A","Description"),array("TMCTRM","A","Terms Code"));}
	elseif ($sequence == "DaysDue") {$orby = array(array("TMCTDT","A","Days Till Due"));}
	elseif ($sequence == "DaysDsc") {$orby = array(array("TMCTDD","A","Days For Discount"),array("TMCTRM","A","Terms Code"));}
	elseif ($sequence == "DscPct")  {$orby = array(array("TMCTDC","A","Discount Percent"),array("TMCTRM","A","Terms Code"));}
	elseif ($sequence == "DueDate") {$orby = array(array("TMDUED","A","Due Date"),array("TMCTRM","A","Terms Code"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("TMCTRM", "Terms Code Number", $_POST['srchCode'], "U", $_POST['operCode'], "A");
	$returnValue=Build_WildCard("upper(TMCTDS)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("TMCTDT", "Days Till Due", $_POST['srchDaysDue'], "", $_POST['operDaysDue'], "N");
	$returnValue=Build_WildCard("TMCTDD", "Days For Discount", $_POST['srchDaysDisc'], "", $_POST['operDaysDisc'], "N");
	$returnValue=Build_WildCard("TMCTDS", "Discount Percent", $_POST['srchDisc'], "", $_POST['operDisc'], "N");
	$returnValue=Build_WildCard("TMDUED", "Due Date", $_POST['srchDueDate'], "", $_POST['operDueDate'], "D");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CalendarInclude.php';
require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectTermsCode(number,name){ ";
print "\n window.opener.document.$docName.$fldName.value = number; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = name;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = name;}";
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
$stmtSQL .= " Select HDTRMS.*, upper(TMCTDS) as TMCTDSU ";
$fileSQL .= " HDTRMS ";
if     ($moreInfo=="Y")      {$selectSQL="TMCTRM='$moreTermsCode' ";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL="TMCTRM=TMCTRM ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"TMCTRM|null|Terms Code|A|U\" title=\"Terms Code\">Terms Code";
	$qsOpt .= "\n <option value=\"upper(TMCTDS)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"TMCTDT|null|Days Till Due|N|\" title=\"Days Till Due\">Days Till Due";
	$qsOpt .= "\n <option value=\"TMCTDD|null|Days For Discount|N|\" title=\"Days For Discount\">Days For Discount";
	$qsOpt .= "\n <option value=\"TMCTDC|null|Discount Percent|N|\" title=\"Discount Percent\">Discount Percent";
	$qsOpt .= "\n <option value=\"TMDUED|DATE|Date Deactivated|D|\" title=\"Date Deactivated\">Date Deactivated";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("TMCTRM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Code\" title=\"Sequence By Terms Code\">{$sortPoint}Terms<br>Code</a></th>";
	$returnValue=OrderBy_Sort("TMCTDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Description, Terms Code\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("TMCTDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DaysDue\" title=\"Sequence By Days Till Due, Terms Code\">{$sortPoint}Days<br>Till<br>Due</a></th>";
	$returnValue=OrderBy_Sort("TMCTDD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DaysDsc\" title=\"Sequence By Days For Discount, Terms Code\">{$sortPoint}Days<br>For<br>Discount</a></th>";
	$returnValue=OrderBy_Sort("TMCTDC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DscPct\"      title=\"Sequence By Discount Percent, Terms Code\">{$sortPoint}Discount<br>Percent</a></th>";
	$returnValue=OrderBy_Sort("TMDUED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DueDate\" title=\"Sequence By Statement Due Date, Terms Code\">{$sortPoint}Due<br>Date</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_TMDUED=Format_Date($row['TMDUED'],"D");
		$F_TMCTDC = Format_Nbr($row['TMCTDC'], "1", "4", "Y", "", "");
		$F_Name=Format_Quote($row['TMCTDS']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colcode\">$row[TMCTRM]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectTermsCode('" . $row['TMCTRM'] . "','" . trim($F_Name) . "')\" title=\"Select Name\">$row[TMCTDS]</a></td> ";
		print "\n     <td class=\"colnmbr\">$row[TMCTDT]</td>";
		print "\n     <td class=\"colnmbr\">$row[TMCTDD]</td>";
		print "\n     <td class=\"colnmbr\">$F_TMCTDC</td>";
		print "\n     <td class=\"coldate\">$F_TMDUED</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreTermsCode=" . urlencode($row['TMCTRM']) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);

	$moreInfoSelect = "href=\"javascript:selectTermsCode('" . trim($row['TMCTRM']) . "','" . trim($row['BKBKNM']) . "')\" title=\"Select Terms Code\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	Build_DspFld("Terms Code",$row[TMCTRM],"","A");
	Build_DspFld("Description",$row[TMCTDS],"","A");
	Build_DspFld("Days Till Due",$row[TMCTDT],"","N");
	Build_DspFld("Days For Discount",$row[TMCTDD],"","N");
	$F_TMCTDC = Format_Nbr($row['TMCTDC'], "1", "4", "Y", "", "");
	Build_DspFld("Discount Percent",$F_TMCTDC,"","N");
	$F_TMDUED=Format_Date($row['TMDUED'],"H");
	Build_DspFld("Due Date",$F_TMDUED,"","D");
	print "\n </table> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Day Of The Month</legend> ";
	print "\n <table $contentTable> ";
	print "\n     <tr> ";
	print "\n         <td class=\"colhdr\">From<br>Day</td> ";
	print "\n         <td class=\"colhdr\">Through<br>Day</td> ";
	print "\n         <td class=\"colhdr\">Day<br>Due</td> ";
	print "\n         <td class=\"colhdr\">Month<br>Increment</td> ";
	print "\n     </tr> ";
	print "\n     <tr> ";
	print "\n         <td class=\"colnmbr\">$row[TMFDY1]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMTDY1]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMDUE1]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMINC1]</td> ";
	print "\n     </tr> ";
	print "\n     <tr> ";
	print "\n         <td class=\"colnmbr\">$row[TMFDY2]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMTDY2]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMDUE2]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMINC2]</td> ";
	print "\n     </tr> ";
	print "\n     <tr> ";
	print "\n         <td class=\"colnmbr\">$row[TMFDY3]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMTDY3]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMDUE3]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMINC3]</td> ";
	print "\n     </tr> ";
	print "\n     <tr> ";
	print "\n         <td class=\"colnmbr\">$row[TMFDY4]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMTDY4]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMDUE4]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMINC4]</td> ";
	print "\n     </tr> ";
	print "\n     <tr> ";
	print "\n         <td class=\"colnmbr\">$row[TMFDY5]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMTDY5]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMDUE5]</td> ";
	print "\n         <td class=\"colnmbr\">$row[TMINC5]</td> ";
	print "\n     </tr> ";
	print "\n </table> ";
	print "\n </fieldset> ";
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
