<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$forCustomer  = (isset($_GET['forCustomer']))  ? $_GET['forCustomer'] : 0;
$moreInfo = $_GET['moreInfo'];
$moreCustomer = $_GET['moreCustomer'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Customer Search";
$scriptName     = "CustomerSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;forCustomer=" . urlencode(trim($forCustomer));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("CMCNA1U","A","Name"),array("CMCUST","A","Number"));

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
	print "\n if (editNum(document.Search.srchNumber, 7, 0) && ";
	print "\n     editNum(document.Search.srchPhone, 11, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Number","srchNumber","","operNumber","opersel_num_short","N","7","7");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","26","26");
	Build_AdvSrch_Entry("Address","srchAddress","","operAddress","opersel_alph_short","A","26","26");
	Build_AdvSrch_Entry("City","srchCity","","operCity","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("State","srchState","","operState","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Zip","srchZip","","operZip","opersel_alph_short","A","13","13");
	Build_AdvSrch_Entry("Phone Number","srchPhone","","operPhone","opersel_num_short","P","11","11");

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Number")  {$orby = array(array("CMCUST","A","Number"));}
	elseif ($sequence == "Name")    {$orby = array(array("CMCNA1U","A","Name"),array("CMCUST","A","Number"));}
	elseif ($sequence == "Address") {$orby = array(array("CMCNA2U","A","Address"));}
	elseif ($sequence == "City")    {$orby = array(array("CMCCTYU","A","City"),array("CMCNA1U","A","Name"));}
	elseif ($sequence == "State")   {$orby = array(array("CMST","A","State"),array("CMCNA1U","A","Name"));}
	elseif ($sequence == "Zip")     {$orby = array(array("CMZIP","A","Zip"),array("CMCNA1U","A","Name"));}
	elseif ($sequence == "Phone")   {$orby = array(array("CMPHON","A","Phone"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("CMCUST", "Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("CMCNA1U", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("CMCNA2U", "Address", $_POST['srchAddress'], "U", $_POST['operAddress'], "A");
	$returnValue=Build_WildCard("CMCCTYU", "City", $_POST['srchCity'], "U", $_POST['operCity'], "A");
	$returnValue=Build_WildCard("CMST", "State", $_POST['srchState'], "U", $_POST['operState'], "A");
	$returnValue=Build_WildCard("CMZIP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	$returnValue=Build_WildCard("CMPHON", "Phone", $_POST['srchPhone'], "U", $_POST['operPhone'], "P");
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

print "\n function selectCustomer(number,name){ ";
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

$uv_CustomerName="CMCUST";
$uv_CustomerClassName ="CMCCLS";
$uv_RegionName ="CMCRGN";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " HDCUST ";
$selectSQL="CMCUST>0 ";
if ($forCustomer>0) {$selectSQL .= " and (CMCUST=$forCustomer or CMBLTO=$forCustomer)";}
if ($moreInfo=="Y") {$selectSQL .= " and CMCUST=$moreCustomer ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"CMCUST|null|Customer Number|N|\" title=\"Customer Number\">Customer Number";
	$qsOpt .= "\n <option value=\"CMCNA1U|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"CMCNA2U|null|Address|A|U\" title=\"Address\">Address";
	$qsOpt .= "\n <option value=\"CMCCTYU|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"CMST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"CMZIP|null|Zip|A|U\" title=\"Zip\">Zip";
	$qsOpt .= "\n <option value=\"CMPHON|null|Phone Number|P|\" title=\"Phone Number\">Phone Number";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";

	$returnValue=OrderBy_Sort("CMCUST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number\">{$sortPoint}Number</a></th>";
	$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("CMCNA2U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Address\" title=\"Sequence By Address\">{$sortPoint}Address</a></th>";
	$returnValue=OrderBy_Sort("CMCCTYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\" title=\"Sequence By City, Name\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("CMST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"      title=\"Sequence By State, Name\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("CMZIP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Zip\"      title=\"Sequence By Zip, Name\">{$sortPoint}Zip</a></th>";
	$returnValue=OrderBy_Sort("CMPHON"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Phone\" title=\"Sequence By Phone\">{$sortPoint}Phone</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$phone=EditPhoneNumber($row['CMPHON']);
		$F_Name=Format_Quote($row['CMCNA1']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[CMCUST]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectCustomer('" . trim($row['CMCUST']) . "','" . trim($F_Name) . "')\" title=\"Select Name\">$F_Name</a></td> ";
		print "\n     <td class=\"colalph\">$row[CMCNA2]</td>";
		print "\n     <td class=\"colalph\">$row[CMCCTY]</td>";
		print "\n     <td class=\"colalph\">$row[CMST]</td>";
		print "\n     <td class=\"colalph\">$row[CMZIP]</td>";
		print "\n     <td class=\"colnmbr\">$phone</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCustomer=" . urlencode(trim($row['CMCUST'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Name=Format_Quote($row['CMCNA1']);
	$moreInfoSelect = "href=\"javascript:selectCustomer('" . trim($row['CMCUST']) . "','" . trim($F_Name) . "')\" title=\"Select Name\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Customer Number",$row[CMCUST],"","N");
	Build_DspFld("Customer Name",$row[CMCNA1],"","A");
	Build_DspFld("Address",$row[CMCNA2],"","A");
	if (trim($row[CMCNA3])!= "") {Build_DspFld("&nbsp;",$row[CMCNA3],"","A");}
	if (trim($row[CMCNA4])!= "") {Build_DspFld("&nbsp;",$row[CMCNA4],"","A");}
	Build_DspFld("City",$row[CMCCTY],"","A");
	$fieldDesc=RetValue("STID='$row[CMST]'", "HDSTID", "STDESC");
	Build_DspFld("State",$fieldDesc,"","A");
	Build_DspFld("Zip",$row[CMZIP],"","A");
	$phone=EditPhoneNumber($row['CMPHON']);
	Build_DspFld("Phone Number",$phone,"","N");
	$fieldDesc=RetValue("CNCTCD='$row[CMCTRY]'", "HDCTRY", "CNCDES");
	Build_DspFld("Country",$fieldDesc,"","A");
	if (trim($CRSCR1) != "") {Build_DspFld("$CRSCR1",$row[CMUDF1],"","A");}
	if (trim($CRSCR2) != "") {Build_DspFld("$CRSCR2",$row[CMUDF2],"","A");}
	if (trim($CRSCR3) != "") {Build_DspFld("$CRSCR3",$row[CMUDF3],"","A");}
	if (trim($CRSCR4) != "") {Build_DspFld("$CRSCR4",$row[CMUDF4],"","A");}
	if (trim($CRSCR5) != "") {Build_DspFld("$CRSCR5",$row[CMUDF5],"","A");}

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
