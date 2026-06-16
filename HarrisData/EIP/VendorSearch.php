<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$orderControlNumber  = $_GET['orderControlNumber'];
$lineNumber = $_GET['lineNumber'];
$moreInfo = $_GET['moreInfo'];
$moreVendor = $_GET['moreVendor'];

require_once 'SetLibraryList.php';
require_once "APControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Vendor Search";
$scriptName     = "VendorSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc)) . "&amp;orderControlNumber=" . urlencode(trim($orderControlNumber)) . "&amp;lineNumber=" . urlencode(trim($lineNumber));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("VMVNA1U","A","Name"),array("VMVEND","A","Number"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "SELECT") {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Update OEDTWK Set O1VEN1=$moreVendor Where O1OCTL=$orderControlNumber and O1ORL#=$lineNumber";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	print "\n <script TYPE=\"text/javascript\">";
	print "\n opener.location.href=opener.location.href";
	print "\n opener.focus();";
	print "\n window.close();";
	print "\n </script>";
	exit();
}

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
	if     ($sequence == "Number")  {$orby = array(array("VMVEND","A","Number"));}
	elseif ($sequence == "Name")    {$orby = array(array("VMVNA1U","A","Name"),array("VMVEND","A","Number"));}
	elseif ($sequence == "Address") {$orby = array(array("VMVNA2U","A","Address"));}
	elseif ($sequence == "City")    {$orby = array(array("VMVCTYU","A","City"),array("VMVNA1U","A","Name"));}
	elseif ($sequence == "State")   {$orby = array(array("VMST","A","State"),array("VMVNA1U","A","Name"));}
	elseif ($sequence == "Zip")     {$orby = array(array("VMZIP","A","Zip"),array("VMVNA1U","A","Name"));}
	elseif ($sequence == "Phone")   {$orby = array(array("VMPHON","A","Phone"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("VMVEND", "Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("VMVNA1U", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("VMVNA2U", "Address", $_POST['srchAddress'], "U", $_POST['operAddress'], "A");
	$returnValue=Build_WildCard("VMVCTYU", "City", $_POST['srchCity'], "U", $_POST['operCity'], "A");
	$returnValue=Build_WildCard("VMST", "State", $_POST['srchState'], "U", $_POST['operState'], "A");
	$returnValue=Build_WildCard("VMZIP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	$returnValue=Build_WildCard("VMPHON", "Phone", $_POST['srchPhone'], "U", $_POST['operPhone'], "P");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectVendor(number,name){ ";
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

$uv_VendorName ="VMVEND";
$uv_VendorTypeName ="VMVTYP";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " HDVEND ";
if ($moreInfo=="Y")          {$selectSQL .= " VMVEND=$moreVendor ";}
elseif ($wildCardSearch!="" || $uv_Sql != "") {$selectSQL="VMVEND<>0 ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"VMVEND|null|Vendor Number|N|\" title=\"Vendor Number\">Vendor Number";
	$qsOpt .= "\n <option value=\"VMVNA1U|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"VMVNA2U|null|Address|A|U\" title=\"Address\">Address";
	$qsOpt .= "\n <option value=\"VMVCTYU|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"VMST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"VMZIP|null|Zip|A|U\" title=\"Zip\">Zip";
	$qsOpt .= "\n <option value=\"VMPHON|null|Phone Number|P|\" title=\"Phone Number\">Phone Number";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("VMVEND"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number\">{$sortPoint}Number</a></th>";
	$returnValue=OrderBy_Sort("VMVNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("VMVNA2U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Address\" title=\"Sequence By Address\">{$sortPoint}Address</a></th>";
	$returnValue=OrderBy_Sort("VMVCTYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\" title=\"Sequence By City, Name\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("VMST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"      title=\"Sequence By State, Name\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("VMZIP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Zip\"      title=\"Sequence By Zip, Name\">{$sortPoint}Zip</a></th>";
	$returnValue=OrderBy_Sort("VMPHON"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Phone\" title=\"Sequence By Phone\">{$sortPoint}Phone</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$phone=EditPhoneNumber($row['VMPHON']);
		$F_Name=Format_Quote($row['VMVNA1']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[VMVEND]</td>";
		if ($orderControlNumber>0) {print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreVendor=" . urlencode(trim($row['VMVEND'])) . "&amp;tag=SELECT\" title=\"Select Name\">$F_Name</a></td> ";}
		else                       {print "\n     <td class=\"colalph\"><a href=\"javascript:selectVendor('" . trim($row['VMVEND']) . "','" . trim($F_Name) . "')\" title=\"Select Name\">$F_Name</a></td> ";}
		print "\n     <td class=\"colalph\">$row[VMVNA2]</td>";
		print "\n     <td class=\"colalph\">$row[VMVCTY]</td>";
		print "\n     <td class=\"colalph\">$row[VMST]</td>";
		print "\n     <td class=\"colalph\">$row[VMZIP]</td>";
		print "\n     <td class=\"colnmbr\">$phone</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreVendor=" . urlencode(trim($row['VMVEND'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Name=Format_Quote($row['VMVNA1']);
	$moreInfoSelect = "href=\"javascript:selectVendor('" . trim($row['VMVEND']) . "','" . trim($F_Name) . "')\" title=\"Select Name\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Vendor Number",$row[VMVEND],"","N");
	Build_DspFld("Vendor Name",$row[VMVNA1],"","A");
	Build_DspFld("Address",$row[VMVNA2],"","A");
	if ($row[VMVNA3]!="") {Build_DspFld("&nbsp;",$row[VMVNA3],"","A");}
	if ($row[VMVNA4]!="") {Build_DspFld("&nbsp;",$row[VMVNA4],"","A");}
	Build_DspFld("City",$row[VMVCTY],"","A");
	$fieldDesc=RetValue("STID='$row[VMST]'", "HDSTID", "STDESC");
	Build_DspFld("State",$fieldDesc,"","A");
	Build_DspFld("Zip",$row[VMZIP],"","A");
	$phone=EditPhoneNumber($row['VMPHON']);
	Build_DspFld("Phone Number",$phone,"","N");
	$fieldDesc=RetValue("CNCTCD='$row[VMCTRY]'", "HDCTRY", "CNCDES");
	Build_DspFld("Country",$fieldDesc,"","A");
	if (trim($CPSCR1) != "") {Build_DspFld("$CPSCR1",$row[VMUDF1],"","A");}
	if (trim($CPSCR2) != "") {Build_DspFld("$CPSCR2",$row[VMUDF2],"","A");}
	if (trim($CPSCR3) != "") {Build_DspFld("$CPSCR3",$row[VMUDF3],"","A");}
	if (trim($CPSCR4) != "") {Build_DspFld("$CPSCR4",$row[VMUDF4],"","A");}
	if (trim($CPSCR5) != "") {Build_DspFld("$CPSCR5",$row[VMUDF5],"","A");}

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
