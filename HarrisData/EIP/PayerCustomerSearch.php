<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName            = $_GET['docName'];
$fldName            = $_GET['fldName'];
$fldDesc            = $_GET['fldDesc'];
$moreInfo           = $_GET['moreInfo'];
$forceChange        = $_GET['forceChange'];
$specificPayer      = $_GET['specificPayer'];
$customerNumber     = $_GET['customerNumber'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Payer Customer Search";
$scriptName     = "PayerCustomerSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;forceChange=" . urlencode(trim($forceChange)) . "&amp;specificPayer=" . urlencode(trim($specificPayer)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("PCCUST","A","Customer"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterSearch.php';
	require_once 'NumEdit.php';
	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.Search.srchCust, 7, 0) && ";
	print "\n       editNum(document.Search.srchPhone, 11, 0)) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","20","26");
	Build_AdvSrch_Entry("Customer","srchCust","","operCust","opersel_num_short","N","7","7");

	print "\n <tr><td class=\"dsphdr\">Address</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchAddress\" size=\"20\" maxlength=\"26\"></td>";
	print "\n </tr>";

	Build_AdvSrch_Entry("State","srchState","","operSt","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Zip","srchZip","","operZip","opersel_alph_short","A","13","13");

	print "\n <tr><td class=\"dsphdr\">Phone</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputnmbr\"> <input type=\"text\" name=\"srchPhone\" size=\"11\" maxlength=\"11\"></td>";
	print "\n </tr>";

	$focusField = "srchName";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Name")      {$orby = array(array("CMCNA1U","A","Name"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "Customer")  {$orby = array(array("CMCUST","A","Customer"));}
	elseif ($sequence == "City")      {$orby = array(array("CMCCTYU","A","City"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "State")     {$orby = array(array("CMST","A","State"),array("CMCUST","A","Customer"));}
	elseif ($sequence == "Phone")     {$orby = array(array("CMPHON","A","Phone"),array("CMCUST","A","Customer"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard ("CMCNA1U", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard ("CMCUST", "Customer", $_POST['srchCust'], "", $_POST['operCust'], "N");
	if (trim($_POST['srchAddress']) != ""){
		$returnValue=Build_WildCard("CMCNA2U", "Address", $_POST['srchAddress'], "U", "LIKE", "V");
		$_POST['srchAddress'] = Build_SelData($_POST['srchAddress'],"U","LIKE","A");
		$wildCardTemp .= " (trim(CMCNA2U)  LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(CMCNA3U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(CMCNA4U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(CMCCTYU) LIKE '$_POST[srchAddress]')";
	}
	$returnValue=Build_WildCard ("CMST", "State", $_POST['srchState'], "U", $_POST['operSt'], "A");
	$returnValue=Build_WildCard ("CMZIP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	$returnValue=Build_WildCard ("CMPHON", "Phone", $_POST['srchPhone'], "", "", "P");
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
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectCustomer(customerNumber, customerDesc) { ";
print "\n   if (window.opener.document.getElementById('PopUpWindow')===true) {window.opener.document.getElementById('PopUpWindow').innerHTML = self.name;} ";
print "\n   window.opener.document.$docName.$fldName.value = customerNumber; ";
print "\n   if      (window.opener.document.$docName.$fldDesc)          {window.opener.document.$docName.$fldDesc.value = customerDesc;} ";
print "\n   else if (window.opener.document.getElementById('$fldDesc')) {window.opener.document.getElementById('$fldDesc').innerHTML = customerDesc;} ";
print "\n   window.opener.document.$docName.$fldName.focus(); ";
if ($forceChange=="Y") {
	print "\n window.opener.document.getElementById('$fldName').onchange(); ";
	print "\n setTimeout('self.close()',3000); ";
} else {
	print "\n window.close(); ";
}
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

print "\n <table $contentTable> ";
$PYPYNM=RetValue("PYPAYR=$specificPayer", "ARPYRH", "PYPYNM");
Format_Header("Payer", $PYPYNM, $specificPayer);
print "\n </table> ";

print $searchhrTagAttr;

$uv_CustomerName ="PCCUST";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select PCCUST,CMCNA1,CMCNA2,CMCNA3,CMCNA4,CMCCTY,CMST,CMZIP,CMPHON,CMCNA1U,CMCCTYU ";
$fileSQL .= " ARPYRC ";
$fileSQL .= " inner join HDCUST on CMCUST=PCCUST ";
$selectSQL .=" PCPAYR=$specificPayer";

require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"CMCNA1U|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"CMCUST|null|Customer|N|\" title=\"Customer\">Customer";
	$qsOpt .= "\n <option value=\"CMCCTYU|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"CMST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"CMPHON|null|Phone|P|\" title=\"Phone\">Phone";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\"      title=\"Sequence By Name\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("CMCUST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Customer\"  title=\"Sequence By Customer\">{$sortPoint}Customer</a></th>";
	$returnValue=OrderBy_Sort("CMCCTYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\"      title=\"Sequence By City\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("CMST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"     title=\"Sequence By State\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("CMPHON"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Phone\"     title=\"Sequence By Phone\">{$sortPoint}Phone</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';

		$F_PCCUST=Format_Quote($row['PCCUST']);
		$F_CMCNA1=Format_Quote($row['CMCNA1']);
		$F_CMPHON=EditPhoneNumber($row['CMPHON']);
		$address=trim($row['CMCNA2']);
		if (trim($row['CMCNA3'])!="") {$address .= ", " . trim($row['CMCNA3']);}
		if (trim($row['CMCNA4'])!="") {$address .= ", " . trim($row['CMCNA4']);}
		$address .= ", " . trim($row['CMCCTY']) . " " . $row[CMST] . " " . $row[CMZIP];

		print "\n <tr class=\"$rowClass\">";
		print "\n <td class=\"colalph\"><a href=\"javascript:selectCustomer('" . trim($F_PCCUST) . "','" . trim($F_CMCNA1) . "')\" title=\"Select Customer\">$row[CMCNA1]</a></td> ";
		print "\n <td class=\"colalph\">$row[PCCUST]</td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$address\">$row[CMCCTY]</span></td>";
		print "\n <td class=\"colalph\">$row[CMST]</td>";
		print "\n <td class=\"colnmbr\">$F_CMPHON</td>";
		print "\n <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;customerNumber=" . urlencode(trim($row['PCCUST'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";

} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_PCCUST=Format_Quote($row['PCCUST']);
	$F_CMCNA1=Format_Quote($row['CMCNA1']);
	$moreInfoSelect = "href=\"javascript:selectCustomer('" . trim($F_PCCUST) . "','" . trim($F_CMCNA1) . "')\" title=\"Select Customer\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	print "\n     <tr><td class=\"dsphdr\">Customer</td> ";
	print "\n         <td class=\"dspalph\">$row[PCCUST]</td> ";
	print "\n     </tr> ";

	print "\n     <tr><td class=\"dsphdr\">Name</td> ";
	print "\n         <td class=\"dspalph\">$row[CMCNA1]</td> ";
	print "\n     </tr> ";


	print "\n     <tr><td class=\"dsphdr\">Address</td> ";
	print "\n         <td class=\"dspalph\">$row[CMCNA2]</td> ";
	print "\n     </tr> ";

	if (trim($row['CMCNA3'])!="") {
		print "\n <tr><td class=\"dsphdr\">&nbsp;</td> ";
		print "\n     <td class=\"dspalph\">$row[CMCNA3]</td> ";
		print "\n </tr> ";
	}

	if (trim($row['CMCNA4'])!="") {
		print "\n <tr><td class=\"dsphdr\">&nbsp;</td> ";
		print "\n     <td class=\"dspalph\">$row[CMCNA4]</td> ";
		print "\n </tr> ";
	}

	print "\n     <tr><td class=\"dsphdr\">&nbsp;</td> ";
	print "\n         <td class=\"dspalph\">$row[CMCITY] $row[CMST] $row[CMZIP]</td> ";
	print "\n     </tr> ";

	if ($row['CMCTRY']!=$HDCTCD) {
		$fieldDesc=RetValue("CNCTCD='$row[CMCTRY]'", "HDCTRY", "CNCDES");
		$F_CMCTRY=Format_Code($row['CMCTRY']);
		print "\n     <tr><td class=\"dsphdr\">Country</td> ";
		print "\n         <td class=\"dspalph\">$fieldDesc $F_CMCTRY</td> ";
		print "\n     </tr> ";
	}

	$F_CMPHON=EditPhoneNumber($row['CMPHON']);
	print "\n     <tr><td class=\"dsphdr\">Phone</td> ";
	print "\n         <td class=\"dspnmbr\">$F_CMPHON</td> ";
	print "\n     </tr> ";

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


