<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldComp  = (isset($_GET['fldComp']))           ? $_GET['fldComp']           : '';
$fldType  = $_GET['fldType'];
$fldDesc  = $_GET['fldDesc'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "HR Codes Search";
$scriptName     = "HRCodesSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldComp=" . urlencode(trim($fldComp)) . "&amp;fldType=" . urlencode(trim($fldType)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("ODCODE","A","Code"),array("ODDESCU","A","Description"));

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

	if ($fldComp=="") {Build_AdvSrch_Entry("Company","srchComp","","operComp","opersel_num_short","N","2","2");}
	Build_AdvSrch_Entry("Code","srchCode","","operCode","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","20","20");

	if ($fldComp=="") {$focusField = "srchComp";} else {$focusField = "srchCode";}
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Comp")         {$orby = array(array("ODCOMP","A","Company"),array("ODCODE","A","Code"));}
	elseif ($sequence == "Code")         {$orby = array(array("ODCODE","A","Code"));}
	elseif ($sequence == "Description")  {$orby = array(array("ODDESCU","A","Description"),array("ODCODE","A","Code"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("ODCOMP", "Company", $_POST['srchComp'], "", $_POST['operComp'], "N");
	$returnValue=Build_WildCard("ODCODE", "Code", $_POST['srchCode'], "U", $_POST['operCode'], "A");
	$returnValue=Build_WildCard("upper(ODDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectCode(Code,Desc){ ";
print "\n window.opener.document.$docName.$fldName.value = Code; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = Desc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = Desc;}";
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
print "<table $contentTable>";
if ($fldComp!="") {
	$fieldDesc=RetValue("CFCOMP='$fldComp'", "HRCOFC", "CFNAME");
	Format_Header("Company", $fieldDesc, "$fldComp");
}
$fieldDesc=RetValue("FLTYPE='HRCODETYPE' and FLVALU='$fldType'", "SYFLAG", "FLDESC");
Format_Header("Type Of Code", $fieldDesc, "$fldType");
print "\n </table>";
print $searchhrTagAttr;

if ($fldComp=="")  {
	$uv_HRCompanyName ="ODCOMP";
	require 'UserView.php';
}
require 'stmtSQLClear.php';
$stmtSQL .= " Select ODCOMP, ODTYPE, ODCODE, ODDESC, upper(ODDESC) as ODDESCU ";
$fileSQL .= " PECODE ";
if ($fldComp!="")  {$selectSQL.="ODCOMP='$fldComp' and ";}
$selectSQL.="ODTYPE='$fldType' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt = "";
if ($fldComp=="")  {$qsOpt  .= "\n <option value=\"ODCOMP|null|Company Number|N|\" title=\"Company Number\">Company Number";}
$qsOpt .= "\n <option value=\"ODCODE|null|Code|A|U\" title=\"Code\">Code";
$qsOpt .= "\n <option value=\"upper(ODDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
if ($fldComp=="")  {
	$returnValue=OrderBy_Sort("ODCOMP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Comp\" title=\"Sequence By Company, Code\">{$sortPoint}Company</a></th>";
}
$returnValue=OrderBy_Sort("ODCODE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Code\" title=\"Sequence By Code\">{$sortPoint}Code</a></th>";
$returnValue=OrderBy_Sort("ODDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Code\">{$sortPoint}Description</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Desc=Format_Quote($row['ODDESC']);
	print "\n <tr class=\"$rowClass\">";
	if ($fldComp=="") {print "\n     <td class=\"colnmbr\">$row[ODCOMP]</td>";}
	print "\n     <td class=\"colcode\">$row[ODCODE]</td>";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectCode('" . trim($row['ODCODE']) . "','" . trim($F_Desc) . "')\" title=\"Select Code\">$F_Desc</a></td> ";
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
