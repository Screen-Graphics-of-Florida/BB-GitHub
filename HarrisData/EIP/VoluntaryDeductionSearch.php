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

$page_title     = "Voluntary Deduction Search";
$scriptName     = "VoluntaryDeductionSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("HVDDNO","N","Number"),array("HVDDNMU","A","Name"));

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
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchNumber, 3, 0) && ";
	print "\n     editNum(document.Search.srchPrty, 3, 0) && ";
	print "\n     editNum(document.Search.srchCyc, 1, 0) && ";
	print "\n     editNum(document.Search.srchMeth, 2, 0) && ";
	print "\n     editNum(document.Search.srchAcct, 4, 0) && ";
	print "\n     editNum(document.Search.srchSub, 4, 0) ) ";
	print "\n     return true;";
	print "\n }";print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Number","srchNumber","","operNumber","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Priority","srchPrty","","operPrty","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("Cycle","srchCyc","","operCyc","opersel_num_short","N","1","1");
	Build_AdvSrch_Entry("Accumulate","srchAccum","","operAccum","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Full Amount Only","srchFull","","operFull","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Take On Separate Check","srchSepChk","","operSepChk","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Calculation Method","srchMeth","","operMeth","opersel_num_short","N","2","2");

	$operNbr = "operAcct";
	print "\n <tr><td class=\"dsphdr\">Account</td>";
	print "\n     <td>"; require 'opersel_num_short.php'; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchAcct\" size=\"4\" maxlength=\"4\">&nbsp;-&nbsp;<input name=\"srchSub\" type=\"text\" size=\"4\" maxlength=\"4\"></td>";
	print "\n </tr>";

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Number")     {$orby = array(array("HVDDNO","N","Number"),array("HVDDNMU","A","Name"));}
	elseif ($sequence == "Name")       {$orby = array(array("HVDDNMU","A","Name"),array("HVDDNO","N","Number"));}
	elseif ($sequence == "Prty")       {$orby = array(array("HVPRTY","N","Priority"));}
	elseif ($sequence == "Cyc")        {$orby = array(array("HVCYCL","N","Cycle"));}
	elseif ($sequence == "Accum")      {$orby = array(array("HVACUM","A","Accumulate"));}
	elseif ($sequence == "Full")       {$orby = array(array("HVFAO","A","Full Amount Only"));}
	elseif ($sequence == "SepChk")     {$orby = array(array("HVTOSC","A","Take On Separate Check"));}
	elseif ($sequence == "Meth")       {$orby = array(array("HVMTHD","N","Calculation Method"));}
	elseif ($sequence == "Account")    {$orby = array(array("HVGLAC","A","G/L Account"),array("HVGLSB","A",""));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("HVDDNO", "Number", $_POST['srchNumber'], "", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("upper(HVDDNM)", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("HVPRTY", "Priority", $_POST['srchPrty'], "", $_POST['operPrty'], "N");
	$returnValue=Build_WildCard("HVCYCL", "Cycle", $_POST['srchCyc'], "", $_POST['operCyc'], "N");
	$returnValue=Build_WildCard("HVACUM", "Accumulate", $_POST['srchAccum'], "U", $_POST['operAccum'], "A");
	$returnValue=Build_WildCard("HVFAO", "Full Amount Only", $_POST['srchFull'], "U", $_POST['operFull'], "A");
	$returnValue=Build_WildCard("HVTOSC", "Take On Separate Check", $_POST['srchSepChk'], "U", $_POST['operSepChk'], "A");
	$returnValue=Build_WildCard("HVMTHD", "Calculation Method", $_POST['srchMeth'], "", $_POST['operMeth'], "N");
	$returnValue=Build_WildCard("HVGLAC ", "G/L Account", $_POST['srchAcct'], "", $_POST['operAcct'], "N");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectDed(number,name){ ";
print "\n window.opener.document.$docName.$fldName.value = number; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = name;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = name;}";
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

require 'stmtSQLClear.php';
$stmtSQL .= " Select HRDEDM.*, upper(HVDDNM) as HVDDNMU ";
$fileSQL .= " HRDEDM ";
if ($wildCardSearch!="") {$selectSQL="HVDDNO<>0 ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"HVDDNO|null|Deduction Number|N|\" title=\"Deduction Number\">Deduction Number";
$qsOpt .= "\n <option value=\"upper(HVDDNM)|null|Name|A|U\" title=\"Name\" SELECTED>Name";
$qsOpt .= "\n <option value=\"HVPRTY|null|Priority|N|\" title=\"Priority\">Priority";
$qsOpt .= "\n <option value=\"HVCYCL|null|Cycle|N|\" title=\"Cycle\">Cycle";
$qsOpt .= "\n <option value=\"HVACUM|null|Accumulate|A|U\" title=\"Accumulate\">Accumulate";
$qsOpt .= "\n <option value=\"HVFAO|null|Full Amount Only|A|U\" title=\"Full Amount Only\">Full Amount Only";
$qsOpt .= "\n <option value=\"HVTOSC|null|Separate Check|A|U\" title=\"Separate Check\">Separate Check";
$qsOpt .= "\n <option value=\"HVMTHD|null|Calculation Method|N|\" title=\"Calculation Method\">Calculation Method";
$qsOpt .= "\n <option value=\"HVGLAC|null|Account Number|N|\" title=\"Account Number\">Account Number";
$qsOpt .= "\n <option value=\"HVGLSB|null|Subaccount Number|N|\" title=\"Subaccount Number\">Subaccount Number";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("HVDDNO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number, Name\">{$sortPoint}Number</a></th>";
$returnValue=OrderBy_Sort("HVDDNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Name</a></th>";
$returnValue=OrderBy_Sort("HVPRTY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Prty\" title=\"Sequence By Priority\">{$sortPoint}Priority</a></th>";
$returnValue=OrderBy_Sort("HVCYCL"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Cyc\" title=\"Sequence By Cycle\">{$sortPoint}Cycle</a></th>";
$returnValue=OrderBy_Sort("HVACUM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Accum\" title=\"Sequence By Accumulate\">{$sortPoint}Accum</a></th>";
$returnValue=OrderBy_Sort("HVFAO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Full\" title=\"Sequence By Full Amount Only\">{$sortPoint}Full<br>Amount Only</a></th>";
$returnValue=OrderBy_Sort("HVTOSC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SepChk\" title=\"Sequence By Separate Check\">{$sortPoint}Separate<br>Check</a></th>";
$returnValue=OrderBy_Sort("HVMTHD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Meth\" title=\"Sequence By Calculation Method\">{$sortPoint}Calc<br>Method</a></th>";
$returnValue=OrderBy_Sort("HVGLAC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Account\" title=\"Sequence By G/L Account\">{$sortPoint}G/L<br>Account</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Desc=Format_Quote($row['HVDDNM']);
	$F_AcctSub=Format_Acct($row['HVGLAC'],$row['HVGLSB'],"N");
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colnmbr\">$row[HVDDNO]</td>";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectDed('" . trim($row['HVDDNO']) . "','" . trim($F_Desc) . "')\" title=\"Select Name\">$F_Desc</a></td> ";
	print "\n     <td class=\"colnmbr\">$row[HVPRTY]</td>";
	print "\n     <td class=\"colcode\">$row[HVCYCL]</td>";
	print "\n     <td class=\"colcode\">$row[HVACUM]</td>";
	print "\n     <td class=\"colcode\">$row[HVFAO]</td>";
	print "\n     <td class=\"colcode\">$row[HVTOSC]</td>";
	print "\n     <td class=\"colnmbr\">$row[HVMTHD]</td>";
	print "\n     <td class=\"colnmbr\">$F_AcctSub</td>";
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
