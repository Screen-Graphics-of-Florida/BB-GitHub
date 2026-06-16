<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldCo    = $_GET['fldCo'];
$fldFac   = $_GET['fldFac'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$moreCo   = $_GET['moreCo'];
$moreFac  = $_GET['moreFac'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Company/Facility Search";
$scriptName     = "HRCoFacSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldCo=" . urlencode(trim($fldCo)). "&amp;fldFac=" . urlencode(trim($fldFac)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("CFNAMEU","A","Name"),array("CFCOMP","A","Company"),array("CFFACL","A",""));

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
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchCo, 2, 0) && ";
	print "\n     editNum(document.Search.srchFac, 4, 0) && ";
	print "\n     editNum(document.Search.srchHRCo, 2, 0) ) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Company","srchCo","","operCo","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Facility","srchFac","","operFac","opersel_num_short","N","4","4");
	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Address","srchAddr","","operAddr","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("City","srchCity","","operCity","opersel_alph_short","A","16","16");
	Build_AdvSrch_Entry("State","srchState","","operState","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Zip Code","srchZip","","operZip","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Phone","srchPhone","","","","P","10","10");

	$focusField = "srchCo";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Company")  {$orby = array(array("CFCOMP","A","Company"),array("CFFACL","A",""));}
	elseif ($sequence == "Name")     {$orby = array(array("CFNAMEU","A","Name"),array("CFCOMP","A","Co/Fac"),array("CFFACL","A",""));}
	elseif ($sequence == "Address")  {$orby = array(array("CFADR1","A","Address"));}
	elseif ($sequence == "City")     {$orby = array(array("upper(CFCITY)","A","City"));}
	elseif ($sequence == "State")    {$orby = array(array("CFSTID","A","State"));}
	elseif ($sequence == "ZipCode")  {$orby = array(array("CFZIP","A","Zip Code"));}
	elseif ($sequence == "Phone")    {$orby = array(array("CFPHON","A","Phone"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("CFCOMP", "Company", $_POST['srchCo'], "", $_POST['operCo'], "N");
	$returnValue=Build_WildCard("CFFACL", "Facility", $_POST['srchFac'], "", $_POST['operFac'], "N");
	$returnValue=Build_WildCard("CFNAMEU", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("CFADR1", "Address", $_POST['srchAddr'], "U", $_POST['operAddr'], "A");
	$returnValue=Build_WildCard("upper(CFCITY)", "City", $_POST['srchCity'], "U", $_POST['operCity'], "A");
	$returnValue=Build_WildCard("CFSTID", "State", $_POST['srchState'], "U", $_POST['operState'], "A");
	$returnValue=Build_WildCard("CFZIP", "Zip Code", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	$returnValue=Build_WildCard("CFPHON", "Phone", $_POST['srchPhone'], "", $_POST['operPhone'], "PA");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectCoFac(coNum,facNum,coNumDesc){ ";
print "\n window.opener.document.$docName.$fldCo.value = coNum; ";
print "\n window.opener.document.$docName.$fldFac.value = facNum; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = coNumDesc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = coNumDesc;}";
print "\n window.opener.document.$docName.$fldCo.focus(); ";
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

$uv_CompanyName ="CFCOMP";
$uv_FacilityName ="CFFACL";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HRCOFC.*,  upper(CFCITY) as CFCITYU ";
$fileSQL .= " HRCOFC ";
$selectSQL="CFFACL<>0 and CFPRAC=' ' ";
if ($moreInfo=="Y")          {$selectSQL .= " and CFCOMP=$moreCo and CFFACL=$moreFac";}
if ($wildCardSearch!="") {$selectSQL.=" and CFCOMP<>0 ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"CFCOMP|null|Company Number|N|\" title=\"Company Number\">Company Number";
	$qsOpt .= "\n <option value=\"CFFACL|null|Facility Number|N|\" title=\"Facility Number\">Facility Number";
	$qsOpt .= "\n <option value=\"CFNAMEU|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"CFADR1|null|Address|A|U\" title=\"Address\">Address";
	$qsOpt .= "\n <option value=\"CFCITYU|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"CFSTID|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"CFZIP|null|Zip|A|U\" title=\"Zip\">Zip";
	$qsOpt .= "\n <option value=\"CFPHON|null|Phone Number|P|\" title=\"Phone Number\">Phone Number";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("CFCOMP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Company\"      title=\"Sequence By Co/Fac\">{$sortPoint}Co/Fac</a></th>";
	$returnValue=OrderBy_Sort("CFNAMEU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\"  title=\"Sequence By Name, Co/Fac\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("CFADR1"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Address\" title=\"Sequence By Address\">{$sortPoint}Address</a></th>";
	$returnValue=OrderBy_Sort("CFCITYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\" title=\"Sequence By City\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("CFSTID"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\" title=\"Sequence By State\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("CFZIP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ZipCode\" title=\"Sequence By Zip Code\">{$sortPoint}Zip Code</a></th>";
	$returnValue=OrderBy_Sort("CFPHON"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Phone\" title=\"Sequence By Phone\">{$sortPoint}Phone</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_CoFac=Format_CoFac($row['CFCOMP'],$row['CFFACL'],"N");
		$phone=EditPhoneNumber($row['CFPHON']);
		$F_Name=Format_Quote($row['CFNAME']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$F_CoFac</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectCoFac('" . trim($row['CFCOMP']) . "','" . trim($row['CFFACL']) . "','" . trim($F_Name) . "')\" title=\"Select Company/Facility\">$F_Name</a></td> ";
		print "\n     <td class=\"colalph\">$row[CFADR1]</td>";
		print "\n     <td class=\"colalph\">$row[CFCITY]</td>";
		print "\n     <td class=\"colalph\">$row[CFSTID]</td>";
		print "\n     <td class=\"colalph\">$row[CFZIP]</td>";
		print "\n     <td class=\"colnmbr\">$phone</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreCo=" . urlencode(trim($row['CFCOMP'])) . "&amp;moreFac=" . urlencode(trim($row['CFFACL'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Name=Format_Quote($row['CFNAME']);
	$F_CoFac=Format_CoFac($row['CFCOMP'],$row['CFFACL'],"N");
	$moreInfoSelect = "href=\"javascript:selectCoFac('" . trim($row['CFCOMP']) . "','" . trim($row['CFFACL']) . "','" . trim($F_Name) . "')\" title=\"Select Company/Facility\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Company/Facility",$F_CoFac,"","N");
	Build_DspFld("Name",$row[CFNAME],"","A");
	Build_DspFld("Address",$row[CFADR1],"","A");
	if ($row[CFADR2]!="") {Build_DspFld("&nbsp;",$row[CFADR2],"","A");}
	Build_DspFld("City",$row[CFCITY],"","A");
	$fieldDesc=RetValue("STID='$row[CFSTID]'", "HDSTID", "STDESC");
	Build_DspFld("State",$fieldDesc,"","A");
	Build_DspFld("Zip",$row[CFZIP],"","A");
	$phone=EditPhoneNumber($row['CFPHON']);
	Build_DspFld("Phone Number",$phone,"","N");

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
