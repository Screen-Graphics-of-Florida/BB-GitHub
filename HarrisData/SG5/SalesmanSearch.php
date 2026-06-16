<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$moreSalesman = $_GET['moreSalesman'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Salesman Search";
$scriptName     = "SalesmanSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("SMSNA1U","A","Name"),array("SMSLSM","A","Number"));

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
	if     ($sequence == "Number")  {$orby = array(array("SMSLSM","A","Number"));}
	elseif ($sequence == "Name")    {$orby = array(array("SMSNA1U","A","Name"),array("SMSLSM","A","Number"));}
	elseif ($sequence == "Address") {$orby = array(array("SMSNA2U","A","Address"));}
	elseif ($sequence == "City")    {$orby = array(array("SMSCTYU","A","City"),array("SMSNA1U","A","Name"));}
	elseif ($sequence == "State")   {$orby = array(array("SMST","A","State"),array("SMSNA1U","A","Name"));}
	elseif ($sequence == "Zip")     {$orby = array(array("SMZIP","A","Zip"),array("SMSNA1U","A","Name"));}
	elseif ($sequence == "Phone")   {$orby = array(array("SMPHON","A","Phone"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("SMSLSM", "Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("upper(SMSNA1)", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("upper(SMSNA2)", "Address", $_POST['srchAddress'], "U", $_POST['operAddress'], "A");
	$returnValue=Build_WildCard("upper(SMSCTY)", "City", $_POST['srchCity'], "U", $_POST['operCity'], "A");
	$returnValue=Build_WildCard("SMST", "State", $_POST['srchState'], "U", $_POST['operState'], "A");
	$returnValue=Build_WildCard("SMZIP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	$returnValue=Build_WildCard("SMPHON", "Phone", $_POST['srchPhone'], "U", $_POST['operPhone'], "P");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectSalesman(number,name){ ";
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
$formName = "Search";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

$uv_SalesmanName ="SMSLSM";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDSLSM.*, upper(SMSNA1) as SMSNA1U, upper(SMSNA2) as SMSNA2U, upper(SMSCTY) as SMSCTYU ";
$fileSQL .= " HDSLSM ";
$selectSQL= " SMSLSM>0 ";
if ($moreInfo=="Y")          {$selectSQL .= " and SMSLSM=$moreSalesman ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"SMSLSM|null|Salesman Number|N|\" title=\"Salesman Number\">Salesman Number";
	$qsOpt .= "\n <option value=\"upper(SMSNA1)|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"upper(SMSNA2)|null|Address|A|U\" title=\"Address\">Address";
	$qsOpt .= "\n <option value=\"upper(SMSCTY)|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"SMST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"SMZIP|null|Zip|A|U\" title=\"Zip\">Zip";
	$qsOpt .= "\n <option value=\"SMPHON|null|Phone Number|P|\" title=\"Phone Number\">Phone Number";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("SMSLSM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number\">{$sortPoint}Number</a></th>";
	$returnValue=OrderBy_Sort("SMSNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("SMSNA2U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Address\" title=\"Sequence By Address\">{$sortPoint}Address</a></th>";
	$returnValue=OrderBy_Sort("SMSCTYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\" title=\"Sequence By City, Name\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("SMST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"      title=\"Sequence By State, Name\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("SMZIP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Zip\"      title=\"Sequence By Zip, Name\">{$sortPoint}Zip</a></th>";
	$returnValue=OrderBy_Sort("SMPHON"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Phone\" title=\"Sequence By Phone\">{$sortPoint}Phone</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$phone=EditPhoneNumber($row['SMPHON']);
		$F_Name=Format_Quote($row['SMSNA1']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[SMSLSM]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectSalesman('" . trim($row['SMSLSM']) . "','" . trim($F_Name) . "')\" title=\"Select Name\">$F_Name</a></td> ";
		print "\n     <td class=\"colalph\">$row[SMSNA2]</td>";
		print "\n     <td class=\"colalph\">$row[SMSCTY]</td>";
		print "\n     <td class=\"colalph\">$row[SMST]</td>";
		print "\n     <td class=\"colalph\">$row[SMZIP]</td>";
		print "\n     <td class=\"colnmbr\">$phone</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreSalesman=" . urlencode(trim($row['SMSLSM'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Name=Format_Quote($row['SMSNA1']);
	$moreInfoSelect = "href=\"javascript:selectSalesman('" . trim($row['SMSLSM']) . "','" . trim($F_Name) . "')\" title=\"Select Name\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Salesman Number",$row[SMSLSM],"","N");
	Build_DspFld("Salesman Name",$row[SMSNA1],"","A");
	Build_DspFld("Address",$row[SMSNA2],"","A");
	if (trim($row[SMSNA3])!="") {Build_DspFld("&nbsp;",$row[SMSNA3],"","A");}
	if (trim($row[SMSNA4])!="") {Build_DspFld("&nbsp;",$row[SMSNA4],"","A");}
	Build_DspFld("City",$row[SMSCTY],"","A");
	$fieldDesc=RetValue("STID='$row[SMST]'", "HDSTID", "STDESC");
	Build_DspFld("State",$fieldDesc,"","A");
	Build_DspFld("Zip",$row[SMZIP],"","A");
	$phone=EditPhoneNumber($row['SMPHON']);
	Build_DspFld("Phone Number",$phone,"","N");
	$fieldDesc=RetValue("RGCRGN='$row[SMREGN]'", "HDCRGN", "RGCRDS");
	Build_DspFld("Region",$fieldDesc,"","A");
	$fieldDesc=RetValue("CNCTCD='$row[SMCTRY]'", "HDCTRY", "CNCDES");
	Build_DspFld("Country",$fieldDesc,"","A");
	$fieldDesc=RetValue("FLTYPE='COMMTYPE' and FLVALU='$row[SMSCPT]'", "SYFLAG", "FLDESC");
	Build_DspFld("Commision Type",$fieldDesc,"","A");
	if ($row[SMSCPT]=="S") {
		$fieldDesc = Format_Nbr(($row['SMSCPC'] * 100), "4", "4", "Y", "", "");
		Build_DspFld("Commision Percent",$fieldDesc,"","A");
	}

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
