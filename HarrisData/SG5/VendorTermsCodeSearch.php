<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Vendor Terms Code Search";
$scriptName     = "VendorTermsCodeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("VTVTDSU","A","Desc"),array("VTRMS","A","Terms Code"));

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
	require_once 'NoFormValidate.php';
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Terms Code","srchTermsCode","","operTermsCode","opersel_alph_short","A","10","2");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Days Till Due","srchDaysTillDue","","operDaysTillDue","opersel_num_short","N","10","3");
	Build_AdvSrch_Entry("Days For Disc","srchDaysForDisc","","operDaysForDisc","opersel_num_short","N","10","3");
	Build_AdvSrch_Entry("Discount Percent","srchDiscPerc","","operDiscPerc","opersel_num_short","N","10","4");

	$focusField = "srchTermsCode";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "TermsCode")   {$orby = array(array("VTRMS","A","Terms Code"));}
	elseif ($sequence == "Desc")        {$orby = array(array("VTVTDSU","A","Desc"),array("VTRMS","A","Terms Code"));}
	elseif ($sequence == "DaysTillDue") {$orby = array(array("VTDAYS","A","Days Till Due"),array("upper(VTVTDS)","A","Desc"),array("VTRMS","A","Terms Code"));}
	elseif ($sequence == "DaysForDisc") {$orby = array(array("VTDISD","A","Days For Disc"),array("upper(VTVTDS)","A","Desc"),array("VTRMS","A","Terms Code"));}
	elseif ($sequence == "DiscPerc")    {$orby = array(array("VTDISP","A","Discount Percent"),array("upper(VTVTDS)","A","Desc"),array("VTRMS","A","Terms Code"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("VTRMS", "Terms Code", $_POST['srchTermsCode'], "U", $_POST['operTermsCode'], "A");
	$returnValue=Build_WildCard("upper(VTVTDS)", "Desc", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("VTDAYS", "Days Till Due", $_POST['srchDaysTillDue'], "U", $_POST['operDaysTillDue'], "A");
	$returnValue=Build_WildCard("VTDISD", "Days For Disc", $_POST['srchDaysForDisc'], "U", $_POST['operDaysForDisc'], "A");
	$returnValue=Build_WildCard("VTDISP", "Discount Percent", $_POST['srchDiscPerc'], "U", $_POST['operDiscPerc'], "A");
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

print "\n function selectTermsCode(TermsCode,Desc){ ";
print "\n window.opener.document.$docName.$fldName.value = TermsCode; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = Desc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = Desc;}";
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
$stmtSQL .= " Select VTRMS, VTVTDS, VTDAYS, VTDISD, Decimal(VTDISP*100,3,1) as VTDISP, upper(VTVTDS) as VTVTDSU ";
$fileSQL .= " APVTRM ";
if ($wildCardSearch!="") {$selectSQL="VTRMS<>' ' ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"VTRMS|null|Terms Code|A|U\" title=\"Terms Code\">Terms Code";
$qsOpt .= "\n <option value=\"uperp(VTVTDS)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
$qsOpt .= "\n <option value=\"VTDAYS|null|Days Till Due|N|\" title=\"Days Till Due\">Days Till Due";
$qsOpt .= "\n <option value=\"VTDISD|null|Days For Discount|N|\" title=\"Days For Discount\">Days For Discount";
$qsOpt .= "\n <option value=\"Decimal(VTDISP*100,3,1)|null|Discount Percent|N|\" title=\"Discount Percent\">Discount Percent";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("VTRMS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TermsCode\" title=\"Sequence By Terms Code\">{$sortPoint}Terms Code</a></th>";
$returnValue=OrderBy_Sort("VTVTDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\" title=\"Sequence By Desc, Terms Code\">{$sortPoint}Description</a></th>";
$returnValue=OrderBy_Sort("VTDAYS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DaysTillDue\" title=\"Sequence By Days Till Due, Desc, Terms Code\">{$sortPoint}Days Till Due</a></th>";
$returnValue=OrderBy_Sort("VTDISD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DaysForDisc\" title=\"Sequence By Days For Disc, Desc, Terms Code\">{$sortPoint}Days For Disc</a></th>";
$returnValue=OrderBy_Sort("VTDISP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DiscPerc\" title=\"Sequence By Discount Percent, Desc, Terms Code\">{$sortPoint}Discount Percent</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	$F_Desc=Format_Quote($row['VTVTDS']);
	print "\n     <td class=\"colcode\">$row[VTRMS]</td>";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectTermsCode('" . trim($row['VTRMS']) . "','" . trim($F_Desc) . "')\" title=\"Select Terms Code\">$F_Desc</a></td> ";
	print "\n     <td class=\"colnmbr\">$row[VTDAYS]</td>";
	print "\n     <td class=\"colnmbr\">$row[VTDISD]</td>";
	print "\n     <td class=\"colnmbr\">$row[VTDISP]</td>";
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
