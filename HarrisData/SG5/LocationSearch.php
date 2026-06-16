<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$moreLocation = $_GET['moreLocation'];

require_once 'SetLibraryList.php';

require_once "APControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Location Search";
$scriptName     = "LocationSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("LOLNA1U","A","Name"),array("LOLOC","A","Number"));

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
	if     ($sequence == "Number")  {$orby = array(array("LOLOC","A","Number"));}
	elseif ($sequence == "Name")    {$orby = array(array("LOLNA1U","A","Name"),array("LOLOC","A","Number"));}
	elseif ($sequence == "Address") {$orby = array(array("LOLNA1","A","Address"));}
	elseif ($sequence == "City")    {$orby = array(array("LOLCTYU","A","City"),array("LOLNA1U","A","Name"));}
	elseif ($sequence == "State")   {$orby = array(array("LOST","A","State"),array("LOLNA1U","A","Name"));}
	elseif ($sequence == "Zip")     {$orby = array(array("LOZIP","A","Zip"),array("LOLNA1U","A","Name"));}
	elseif ($sequence == "Phone")   {$orby = array(array("LOPHON","A","Phone"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("LOLOC", "Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("upper(LOLNA1)", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("LOLNA1", "Address", $_POST['srchAddress'], "U", $_POST['operAddress'], "A");
	$returnValue=Build_WildCard("upper(LOLCTY)", "City", $_POST['srchCity'], "U", $_POST['operCity'], "A");
	$returnValue=Build_WildCard("LOST", "State", $_POST['srchState'], "U", $_POST['operState'], "A");
	$returnValue=Build_WildCard("LOZIP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	$returnValue=Build_WildCard("LOPHON", "Phone", $_POST['srchPhone'], "U", $_POST['operPhone'], "P");
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

print "\n function selectLocation(number,name){ ";
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

$uv_BillingLocationName ="LOLOC#";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDLCTN.*, LOLOC# as LOLOC, upper(LOLNA1) as LOLNA1U, ";
$stmtSQL .= " upper(LOLNA2) as LOLNA2U, upper(LOLCTY) as LOLCTYU, ";
$stmtSQL .= " LOCO# as LOCO, LOFAC# as LOFAC";
$fileSQL .= " HDLCTN ";
if ($moreInfo=="Y")          {$selectSQL .= " LOLOC#=$moreLocation ";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL="LOLOC#<>0 ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"LOLOC#|null|Location Number|N|\" title=\"Location Number\">Location Number";
	$qsOpt .= "\n <option value=\"upper(LOLNA1)|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"upper(LOLNA2)|null|Address|A|U\" title=\"Address\">Address";
	$qsOpt .= "\n <option value=\"upper(LOLCTY)|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"LOST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"LOZIP|null|Zip|A|U\" title=\"Zip\">Zip";
	$qsOpt .= "\n <option value=\"LOPHON|null|Phone Number|P|\" title=\"Phone Number\">Phone Number";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";

	$returnValue=OrderBy_Sort("LOLOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number\">{$sortPoint}Number</a></th>";
	$returnValue=OrderBy_Sort("LOLNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("LOLNA1"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Address\" title=\"Sequence By Address\">{$sortPoint}Address</a></th>";
	$returnValue=OrderBy_Sort("LOLCTYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\" title=\"Sequence By City, Name\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("LOST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"      title=\"Sequence By State, Name\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("LOZIP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Zip\"      title=\"Sequence By Zip, Name\">{$sortPoint}Zip</a></th>";
	$returnValue=OrderBy_Sort("LOPHON"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Phone\" title=\"Sequence By Phone\">{$sortPoint}Phone</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$phone=EditPhoneNumber($row['LOPHON']);
		$F_Name=Format_Quote($row['LOLNA1']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colcode\">$row[LOLOC]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectLocation('" . trim($row['LOLOC']) . "','" . trim($F_Name) . "')\" title=\"Select Location\">$F_Name</a></td> ";
		print "\n     <td class=\"colalph\">$row[LOLNA2]</td>";
		print "\n     <td class=\"colalph\">$row[LOLCTY]</td>";
		print "\n     <td class=\"colalph\">$row[LOST]</td>";
		print "\n     <td class=\"colalph\">$row[LOZIP]</td>";
		print "\n     <td class=\"colnmbr\">$phone</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreLocation=" . urlencode(trim($row['LOLOC'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Name=Format_Quote($row['LOLNA1']);
	$moreInfoSelect = "href=\"javascript:selectLocation('" . trim($row['LOLOC']) . "','" . trim($F_Name) . "')\" title=\"Select Location\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Location Number",$row[LOLOC],"","N");
	Build_DspFld("Location Name",$row[LOLNA1],"","A");
	Build_DspFld("Address",$row[LOLNA2],"","A");
	if (trim($row[LOLNA3])!="") {Build_DspFld("&nbsp;",$row[LOLNA3],"","A");}
	if (trim($row[LOLNA4])!="") {Build_DspFld("&nbsp;",$row[LOLNA4],"","A");}
	Build_DspFld("City",$row[LOLCTY],"","A");
	$fieldDesc=RetValue("STID='$row[LOST]'", "HDSTID", "STDESC");
	Build_DspFld("State",$fieldDesc,"","A");
	Build_DspFld("Zip",$row[LOZIP],"","A");
	$phone=EditPhoneNumber($row['LOPHON']);
	Build_DspFld("Phone Number",$phone,"","N");
	$fieldDesc=RetValue("CNCTCD='$row[LOCTRY]'", "HDCTRY", "CNCDES");
	Build_DspFld("Country",$fieldDesc,"","A");
	$fieldDesc=RetValue("CFCO#=$row[LOCO] and CFFAC#=$row[LOFAC]", "HDCFAC", "CFCFNM");
	$F_cofac = Format_Code("$row[LOCO]/$row[LOFAC]");
	Build_DspFld("Company/Facility",$fieldDesc,"$F_cofac","A");
	$F_LOPLOD = RtvYNDesc($row[LOPLOD]);
	Build_DspFld("Print Location On Documents",$F_LOPLOD,"","A");
	Build_DspFld("Statement Message",$row[LOMSG3],"","A");
	$F_LOFACT = RtvYNDesc($row[LOFACT]);
	Build_DspFld("Factoring Location",$F_LOFACT,"","A");

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
