<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName            = $_GET['docName'];
$fldName            = $_GET['fldName'];
$fldDesc            = $_GET['fldDesc'];
$moreInfo           = $_GET['moreInfo'];
$paymentCode        = $_GET['paymentCode'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Payment Code Search";
$scriptName     = "ARPmtCodeSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("PYPYDSU","A","Description"));
$advanceSearch  = "N";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Description") {$orby = array(array("PYPYDSU","A","Description"),array("PYPYCD","A","Payment Code"));}
	elseif ($sequence == "Code")        {$orby = array(array("PYPYCD","A","Payment Code"));}
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

print "\n function selectPaymentCode(paymentCode, desc){ ";
print "\n   window.opener.document.$docName.$fldName.value = paymentCode; ";
print "\n   if      (window.opener.document.$docName.$fldDesc)          {window.opener.document.$docName.$fldDesc.value = desc;} ";
print "\n   else if (window.opener.document.getElementById('$fldDesc')) {window.opener.document.getElementById('$fldDesc').innerHTML = desc;} ";
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
$stmtSQL .= " Select PYPYCD, PYACCT, PYSUB, PYSTDS, PYPYDS, PYPYDSU ";
$fileSQL .= " ARPYCD ";
if     ($moreInfo=="Y")      {$selectSQL=" PYPYCD='$paymentCode' ";}
elseif ($wildCardSearch!="") {$selectSQL=" PYPYCD=PYPYCD ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"PYPYDSU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"PYPYCD|null|Code|A|U\" title=\"Code\">Code";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("PYPYDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Payment Code\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("PYPYCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Code\" title=\"Sequence By Payment Code \">{$sortPoint}Code</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Desc=Format_Quote($row['PYPYDS']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectPaymentCode('" . trim($row['PYPYCD']) . "','" . trim($F_Desc) . "')\" title=\"Select Payment Code\">$row[PYPYDS]</a></td> ";
		print "\n     <td class=\"colcode\">$row[PYPYCD]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;paymentCode=" . urlencode(trim($row['PYPYCD'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['PYPYDS']);
	$moreInfoSelect = "href=\"javascript:selectPaymentCode('" . trim($row['PYPYCD']) . "','" . trim($F_Desc) . "')\" title=\"Select Payment Code\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	print "\n     <tr><td class=\"dsphdr\">Payment Code</td> ";
	print "\n         <td class=\"dspalph\">$row[PYPYCD]</td></tr> ";

	print "\n     <tr><td class=\"dsphdr\">Description</td> ";
	print "\n         <td class=\"dspalph\">$row[PYPYDS]</td></tr> ";

	print "\n     <tr><td class=\"dsphdr\">Statement Description</td> ";
	print "\n         <td class=\"dspalph\">$row[PYSTDS]</td></tr> ";

	$F_AcctSub=Format_Acct("$row[PYACCT]", "$row[PYSUB]", "Y");
	print "\n     <tr><td class=\"dsphdr\">Account</td> ";
	print "\n         <td class=\"dspalph\">$F_AcctSub</td></tr> ";

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
