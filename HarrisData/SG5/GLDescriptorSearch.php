<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$moreInfo = $_GET['moreInfo'];

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "G/L Descriptor Search";
$scriptName     = "GLDescriptorSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("DSDS","A","Descriptor Table"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Descriptor")   {$orby = array(array("DSDS","A","Descriptor"));}
	elseif ($sequence == "BegAccount")   {$orby = array(array("DSBACC","A","Begin Account"),array("DSDS","A","Descriptor"));}
	elseif ($sequence == "EndAccount")   {$orby = array(array("DSEACC","A","End Account"),array("DSDS","A","Descriptor"));}
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

print "\n function selectDescriptor(descriptorNumber){ ";
print "\n   window.opener.document.$docName.$fldName.value = descriptorNumber; ";
print "\n   window.opener.document.$docName.$fldName.focus(); ";
print "\n   window.close(); ";
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
$withSQL .= " With GLDSCPWITH ";
$withSQL .= "(DSDS,DSBACC,DSEACC) ";
$withSQL .= " as ( ";
$withSQL .= " Select DSDS# as DSDS ";
$withSQL .= "       ,Min(DIGITS(DSAACT)||'-'||DIGITS(DSSUB)) as DSBACC ";
$withSQL .= "       ,Max(DIGITS(DSAACT)||'-'||DIGITS(DSSUB)) as DSEACC ";
$withSQL .= " From GLDSCP";
$withSQL .= " Group By DSDS# ";
$withSQL .= " ) ";
$stmtSQL .= " Select DSDS,DSBACC,DSEACC ";
$fileSQL .= " GLDSCPWITH ";
if ($wildCardSearch!="") {$selectSQL="DSDS=DSDS ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$qsOpt = "";
$qsOpt .= "\n <option value=\"DSDS|null|Descriptor Number|A|U\" title=\"Descriptor Number\" SELECTED>Descriptor Number";
$qsOpt .= "\n <option value=\"DSBACC|null|Begin Account|A|U\" title=\"Begin Account\">Begin Account";
$qsOpt .= "\n <option value=\"DSEACC|null|End Account|A|U\" title=\"End Account\">End Account";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";

$returnValue=OrderBy_Sort("DSDS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Descriptor\">{$sortPoint}Descriptor</a></th>";
$returnValue=OrderBy_Sort("DSBACC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BegAccount\" title=\"Sequence By Begin Account\">{$sortPoint}Begin Account</a></th>";
$returnValue=OrderBy_Sort("DSEACC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=EndAccount\" title=\"Sequence By End Account\">{$sortPoint}End Account</a></th>";
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colnmbr\"><a href=\"javascript:selectDescriptor('" . trim($row['DSDS']) . "')\" title=\"Select Descriptor\">$row[DSDS]</a></td> ";
	print "\n     <td class=\"colnmbr\">$row[DSBACC]</td>";
	print "\n     <td class=\"colnmbr\">$row[DSEACC]</td>";
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
