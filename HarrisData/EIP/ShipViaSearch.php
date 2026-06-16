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

$page_title     = "Ship Via Search";
$scriptName     = "ShipViaSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("SVSVDSU","A","Description"),array("SVSVSV","A","Ship Via"));

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

	Build_AdvSrch_Entry("Ship Via","srchCode","","operCode","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Carrier Ship Via","srchCASV","","operCASV","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("SCAC","srchSCAC","","operSCAC","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("BOL Required","srchBOLR","","operBOLR","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Account Required","srchACRQ","","operACRQ","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Track By","srchTRBY","","operTRBY","opersel_alph_short","A","1","1");

	$focusField = "srchCode";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Code")        {$orby = array(array("SVSVSV","A","Ship Via"),array("SVSVDSU","A","Description"));}
	elseif ($sequence == "Carrier")     {$orby = array(array("TVCASV","A","Carrier Ship Via"),array("SVSVSV","A","Ship Via"));}
	elseif ($sequence == "Description") {$orby = array(array("SVSVDSU","A","Description"),array("SVSVSV","A","Ship Via"));}
	elseif ($sequence == "SCAC")        {$orby = array(array("SVSCAC","A","SCAC"),array("SVSVSV","A","Ship Via"));}
	elseif ($sequence == "BOLR")        {$orby = array(array("SVBOLR","A","BOL Required"),array("SVSVSV","A","Ship Via"));}
	elseif ($sequence == "AcctReq")     {$orby = array(array("TVACRQ","A","Account Required"),array("SVSVSV","A","Ship Via"));}
	elseif ($sequence == "TrackBy")     {$orby = array(array("TVTRBY","A","Track By"),array("SVSVSV","A","Ship Via"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("SVSVSV", "Ship Via", $_POST['srchCode'], "U", $_POST['operCode'], "A");
	$returnValue=Build_WildCard("TVCASV", "Carrier Ship Via", $_POST['srchCASV'], "U", $_POST['operCASV'], "A");
	$returnValue=Build_WildCard("upper(SVSVDS)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("SVSCAC", "SCAC", $_POST['srchSCAC'], "U", $_POST['operSCAC'], "A");
	$returnValue=Build_WildCard("SVBOLR", "BOL Required", $_POST['srchBOLR'], "U", $_POST['operBOLR'], "A");
	$returnValue=Build_WildCard("TVACRQ", "Account Required", $_POST['srchACRQ'], "U", $_POST['operACRQ'], "A");
	$returnValue=Build_WildCard("TVTRBY", "Track By", $_POST['srchTRBY'], "U", $_POST['operTRBY'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectType(type,desc){ ";
print "\n window.opener.document.$docName.$fldName.value = type; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = desc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = desc;}";
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
$stmtSQL .= " Select HDSHPV02.*, upper(SVSVDS) as SVSVDSU ";
$fileSQL .= " HDSHPV02 ";
if ($wildCardSearch!="") {$selectSQL="SVSVSV>' '";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt  = "\n <option value=\"upper(SVSVDS)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
$qsOpt .= "\n <option value=\"SVSVSV|null|Ship Via|A|U\" title=\"Ship Via\">Ship Via";
$qsOpt .= "\n <option value=\"TVCASV|null|Carrier Ship Via|A|U\" title=\"Carrier Ship Via\">Carrier Ship Via";
$qsOpt .= "\n <option value=\"SVSCAC|null|SCAC|A|U\" title=\"SCAC\">SCAC";
$qsOpt .= "\n <option value=\"SVBOLR|YORN|BOL Required|A|U\" title=\"BOL Required\">BOL Required";
$qsOpt .= "\n <option value=\"TVACRQ|YORN|Account Required|A|U\" title=\"Account Required\">Account Required";
$qsOpt .= "\n <option value=\"TVTRBY|YORN|Track By|A|U\" title=\"Track By\">Track By";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue=OrderBy_Sort("SVSVDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Ship Via\">{$sortPoint}Description</a></th>";
$returnValue=OrderBy_Sort("SVSVSV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Code\" title=\"Sequence By Ship Via, Description\">{$sortPoint}Ship<br>Via</a></th>";
$returnValue=OrderBy_Sort("TVCASV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Carrier\" title=\"Sequence By Carrier Ship Via, Description\">{$sortPoint}Carrier<br>Ship Via</a></th>";
$returnValue=OrderBy_Sort("SVSCAC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SCAC\" title=\"Sequence By SCAC, Ship Via\">{$sortPoint}SCAC</a></th>";
$returnValue=OrderBy_Sort("SVBOLR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BOLR\" title=\"Sequence By BOL Required, Ship Via\">{$sortPoint}BOL<br>Required</a></th>";
$returnValue=OrderBy_Sort("TVACRQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=AcctReq\" title=\"Sequence By Account Required, Ship Via\">{$sortPoint}Account<br>Required</a></th>";
$returnValue=OrderBy_Sort("TVTRBY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TrackBy\" title=\"Sequence By Track By, Ship Via\">{$sortPoint}Track<br>By</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	$F_Desc=Format_Quote($row['SVSVDS']);
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectType('" . trim($row['SVSVSV']) . "','" . trim($F_Desc) . "')\" title=\"Select Ship Via\">$F_Desc</a></td> ";
	print "\n     <td class=\"colcode\">$row[SVSVSV]</td>";
	print "\n     <td class=\"colcode\">$row[TVCASV]</td>";
	print "\n     <td class=\"colcode\">$row[SVSCAC]</td>";
	print "\n     <td class=\"colcode\">$row[SVBOLR]</td>";
	print "\n     <td class=\"colcode\">$row[TVACRQ]</td>";
	print "\n     <td class=\"colcode\">$row[TVTRBY]</td>";
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
