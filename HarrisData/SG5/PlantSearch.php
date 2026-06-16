<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$morePlant = $_GET['morePlant'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Plant Search";
$scriptName     = "PlantSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("PLNAMEU","A","Name"),array("PLPLNT","A","Number"));

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
	print "\n if (editNum(document.Search.srchNumber, 7, 0)) ";
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

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if     ($sequence == "Number")  {$orby = array(array("PLPLNT","A","Number"));}
	elseif ($sequence == "Name")    {$orby = array(array("PLNAMEU","A","Name"),array("PLPLNT","A","Number"));}
	elseif ($sequence == "Address") {$orby = array(array("PLADR1U","A","Address"));}
	elseif ($sequence == "City")    {$orby = array(array("PLCITYU","A","City"),array("PLNAMEU","A","Name"));}
	elseif ($sequence == "State")   {$orby = array(array("PLST","A","State"),array("PLNAMEU","A","Name"));}
	elseif ($sequence == "Zip")     {$orby = array(array("PLZIP","A","Zip"),array("PLNAMEU","A","Name"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("PLPLNT", "Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "N");
	$returnValue=Build_WildCard("PLNAMEU", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard("upper(PLADR1)", "Address", $_POST['srchAddress'], "U", $_POST['operAddress'], "A");
	$returnValue=Build_WildCard("upper(PLCITY)", "City", $_POST['srchCity'], "U", $_POST['operCity'], "A");
	$returnValue=Build_WildCard("PLST", "State", $_POST['srchState'], "U", $_POST['operState'], "A");
	$returnValue=Build_WildCard("PLZIP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectPlant(number,name){ ";
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

$uv_PlantName ="PLPLNT";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select HDPLNT.*, HDPLTE.*, PLCO# as PLCO, PLFAC# as PLFAC, ";
$stmtSQL .= " upper(PLNAME) as PLNAMEU, upper(PLADR1) as PLADR1U, upper(PLCITY) as PLCITYU, ";
$stmtSQL .= " PL#WWD as PLWWD, PL#WDD as PLWDD ";
$fileSQL .= " HDPLNT left join HDPLTE on PLPLNT=PEPLT ";
if ($moreInfo=="Y")          {$selectSQL .= " PLPLNT=$morePlant ";}
elseif ($wildCardSearch!="" || $uv_Sql != "") {$selectSQL="PLPLNT<>0 ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"PLPLNT|null|Plant Number|N|\" title=\"Plant Number\">Plant Number";
	$qsOpt .= "\n <option value=\"upper(PLNAME)|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"upper(PLADR1)|null|Address|A|U\" title=\"Address\">Address";
	$qsOpt .= "\n <option value=\"upper(PLCITY)|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"PLST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"PLZIP|null|Zip|A|U\" title=\"Zip\">Zip";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("PLPLNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number\">{$sortPoint}Number</a></th>";
	$returnValue=OrderBy_Sort("PLNAMEU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("PLADR1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Address\" title=\"Sequence By Address\">{$sortPoint}Address</a></th>";
	$returnValue=OrderBy_Sort("PLCITYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\" title=\"Sequence By City, Name\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("PLST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"      title=\"Sequence By State, Name\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("PLZIP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Zip\"      title=\"Sequence By Zip, Name\">{$sortPoint}Zip</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Name=Format_Quote($row['PLNAME']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[PLPLNT]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectPlant('" . trim($row['PLPLNT']) . "','" . trim($F_Name) . "')\" title=\"Select Plant\">$F_Name</a></td> ";
		print "\n     <td class=\"colalph\">$row[PLADR1]</td>";
		print "\n     <td class=\"colalph\">$row[PLCITY]</td>";
		print "\n     <td class=\"colalph\">$row[PLST]</td>";
		print "\n     <td class=\"colalph\">$row[PLZIP]</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;morePlant=" . urlencode(trim($row['PLPLNT'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Name=Format_Quote($row['PLNAME']);
	$moreInfoSelect = "href=\"javascript:selectPlant('" . trim($row['PLPLNT']) . "','" . trim($F_Name) . "')\" title=\"Select Plant\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#generalData\">General Data</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#productDataBase\">Product Data Base</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#defaultRouting\">Default Routing</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#costManagement\">Cost Management</a></td> ";
	if ($HDMPRL>0) {print "\n       <td class=\"quickLinkTabs\"><a href=\"#MPSMRP\">MPS/MRP</a></td> ";}
	print "\n   </tr> ";
	print "\n   <tr> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#shopFloor\">Shop Floor Control</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#cycleCount\">Cycle Count</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#accountNumbers\">Accounts</a></td> ";
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#userDefined\">User-Defined</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";

	print "\n <a name=\"general\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">General</legend> ";
	require 'TopOfForm.php'; 
	print "\n <table $contentTable> ";
	Build_DspFld("Plant Number",$row[PLPLNT],"","N");
	Build_DspFld("Plant Name",$row[PLNAME],"","A");
	$fieldDesc=RetValue("CFCO#=$row[PLCO] and CFFAC#=$row[PLFAC]", "HDCFAC", "CFCFNM");
	Build_DspFld("Company/Facility",$fieldDesc,"","A");
	if (trim($row[PLADR1])!="") {
		Build_DspFld("Address 1",$row[PLADR1],"","A");
	}
	if (trim($row[PLADR2])!="") {
		Build_DspFld("Address 2",$row[PLADR2],"","A");
	}
	if (trim($row[PLCITY])!="") {
		Build_DspFld("City",$row[PLCITY],"","A");
	}
	if (trim($row[PLST])!="") {
		$fieldDesc=RetValue("STID='$row[PLST]'", "HDSTID", "STDESC");
		Build_DspFld("State",$fieldDesc,"","A");
	}
	if (trim($row[PLZIP])!="") {
		Build_DspFld("Zip",$row[PLZIP],"","A");
	}
	if (trim($row[PLCTRY])!="") {
		$fieldDesc=RetValue("CNCTCD='$row[PLCTRY]'", "HDCTRY", "CNCDES");
		Build_DspFld("Country",$fieldDesc,"","A");
	}
	if (trim($row[PLSCHD])!="") {
		$fieldDesc=RetValue("SMSCHD='$row[PLSCHD]'", "HDSCHM", "SMDESC");
		Build_DspFld("Schedule",$fieldDesc,"","A");
	}
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"productDataBase\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Product Data Base</legend> ";
	require 'TopOfForm.php'; 
	print "\n <table $contentTable> ";
	Build_DspFld("Default Product Structure Routing Sequence",$row[PLRSEQ],"","N");
	$F_PLRSF = RtvYNDesc($row[PLRSF]);
	Build_DspFld("Product Structure Interfaces With Routing",$F_PLRSF,"","A");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"defaultRouting\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Default Routing</legend> ";
	require 'TopOfForm.php'; 
	print "\n <table $contentTable> ";
	$fieldDesc=RetValue("WCPLT=$row[PEPLT] and WCDEPT='$row[PEDDPT]' and WCWC='$row[PEDWC]'", "HDMWCM", "WCDESC");
	Build_DspFld("Default Department",$row[PEDDPT],$fieldDesc,"A");
	Build_DspFld("Default Work Center",$row[PEDWC],"","A");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"costManagement\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Cost Management</legend> ";
	require 'TopOfForm.php'; 
	print "\n <table $contentTable> ";
	print "\n     <tr><td> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Cost Category</legend> ";
	print "\n <table $contentTable> ";
	if (trim($row[PLCC1D])!="" || trim($row[PLC1LD])!="" || trim($row[PLCC2D])!="" || trim($row[PLC2LD])!="" || trim($row[PLCC3D])!="" || trim($row[PLC3LD])!="" || trim($row[PLCC4D])!="" || trim($row[PLC4LD])!="" || trim($row[PLCC5D])!="" || trim($row[PLC5LD])!="") {
		print "\n     <tr><td>&nbsp;</td> ";
		print "\n         <td class=\"colhdr\">Short Description</td> ";
		print "\n         <td class=\"colhdr\">Long Description</td></tr> ";
	}

	if (trim($row[PLCC1D])!="" || trim($row[PLC1LD])!="")	{
		print "\n     <tr><td class=\"dsphdr\">One</td> ";
		print "\n         <td class=\"dspalph\">$row[PLCC1D]</td> ";
		print "\n         <td class=\"dspalph\">$row[PLC1LD]</td></tr> ";
	}

	if (trim($row[PLCC2D])!="" || trim($row[PLC2LD])!="")	{
		print "\n     <tr><td class=\"dsphdr\">Two</td> ";
		print "\n         <td class=\"dspalph\">$row[PLCC2D]</td> ";
		print "\n         <td class=\"dspalph\">$row[PLC2LD]</td></tr> ";
	}

	if (trim($row[PLCC3D])!="" || trim($row[PLC3LD])!="")	{
		print "\n     <tr><td class=\"dsphdr\">Three</td> ";
		print "\n         <td class=\"dspalph\">$row[PLCC3D]</td> ";
		print "\n         <td class=\"dspalph\">$row[PLC3LD]</td></tr> ";
	}

	if (trim($row[PLCC4D])!="" || trim($row[PLC4LD])!="")	{
		print "\n     <tr><td class=\"dsphdr\">Four</td> ";
		print "\n         <td class=\"dspalph\">$row[PLCC4D]</td> ";
		print "\n         <td class=\"dspalph\">$row[PLC4LD]</td></tr> ";
	}

	if (trim($row[PLCC5D])!="" || trim($row[PLC5LD])!="")	{
		print "\n     <tr><td class=\"dsphdr\">Five</td> ";
		print "\n         <td class=\"dspalph\">$row[PLCC5D]</td> ";
		print "\n         <td class=\"dspalph\">$row[PLC5LD]</td></tr> ";
	}
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Cost Simulation Variables</legend> ";
	print "\n <table $contentTable> ";
	if (trim($row[PLDOL1])!="" || trim($row[PLDOL2])!="" || trim($row[PLPCT1])!="" || $row[PLPCA1]!=".00" || trim($row[PLPCT2])!="" || $row[PLPCA2]!=".00" || trim($row[PLPCT3])!="" || $row[PLPCA3]!=".00" || trim($row[PLPCT4])!="" || $row[PLPCA4]!=".00" || $row[PLSPCT]!=".00" || $row[PLGPCT]!=".00") {
		print "\n     <tr><td>&nbsp;</td> ";
		print "\n         <td>&nbsp;</td> ";
		print "\n         <td class=\"colhdr\">% Value</td></tr> ";
	}

	if (trim($row[PLDOL1])!="")	{Build_DspFld("Dollar One",$row[PLDOL1],"","A");}
	if (trim($row[PLDOL2])!="")	{Build_DspFld("Dollar Two",$row[PLDOL2],"","A");}

	if (trim($row[PLPCT1])!="" || $row[PLPCA1]!=".00") {
		$F_PLPCA1=Format_Nbr($row[PLPCA1], "2", ($pctEditCode), "", "", "");
		print "\n     <tr><td class=\"dsphdr\">Percent One</td> ";
		print "\n         <td class=\"dspalph\">$row[PLPCT1]</td> ";
		print "\n         <td class=\"dspnmbr\">$F_PLPCA1</td></tr> ";
	}

	if (trim($row[PLPCT2])!="" || $row[PLPCA2]!=".00") {
		$F_PLPCA2=Format_Nbr($row[PLPCA2], "2", ($pctEditCode), "", "", "");
		print "\n     <tr><td class=\"dsphdr\">Percent Two</td> ";
		print "\n         <td class=\"dspalph\">$row[PLPCT2]</td> ";
		print "\n         <td class=\"dspnmbr\">$F_PLPCA2</td></tr> ";
	}

	if (trim($row[PLPCT3])!="" || $row[PLPCA3]!=".00")	{
		$F_PLPCA3=Format_Nbr($row[PLPCA3], "2", ($pctEditCode), "", "", "");
		print "\n     <tr><td class=\"dsphdr\">Percent Three</td> ";
		print "\n         <td class=\"dspalph\">$row[PLPCT3]</td> ";
		print "\n         <td class=\"dspnmbr\">$F_PLPCA3</td></tr> ";
	}

	if (trim($row[PLPCT4])!="" || $row[PLPCA4]!=".00")	{
		$F_PLPCA4=Format_Nbr($row[PLPCA4], "2", ($pctEditCode), "", "", "");
		print "\n     <tr><td class=\"dsphdr\">Percent Four</td> ";
		print "\n         <td class=\"dspalph\">$row[PLPCT4]</td> ";
		print "\n         <td class=\"dspnmbr\">$F_PLPCA4</td></tr> ";
	}

	if ($row[PLSPCT]!=".00")	{
		$F_PLSPCT=Format_Nbr($row[PLSPCT], "2", ($pctEditCode), "", "", "");
		print "\n     <tr><td class=\"dsphdr\">S&M % Mfg Cost</td> ";
		print "\n         <td class=\"dspalph\"></td> ";
		print "\n         <td class=\"dspnmbr\">$F_PLSPCT</td></tr> ";
	}

	if ($row[PLGPCT]!=".00")	{
		$F_PLGPCT=Format_Nbr($row[PLGPCT], "2", ($pctEditCode), "", "", "");
		print "\n     <tr><td class=\"dsphdr\">G&A % Mfg Cost</td> ";
		print "\n         <td class=\"dspalph\"></td> ";
		print "\n         <td class=\"dspnmbr\">$F_PLGPCT</td></tr> ";
	}

	if (trim($row[PLFXEL])!="")	{
		$fieldDesc=RetValue("FLTYPE='FIXOVERHD' and FLVALU='$row[PLFXEL]'", "SYFLAG", "FLDESC");
		Build_DspFld("Fixed Overhead Category",$fieldDesc,"","A");
	}
	print "\n </table> ";
	print "\n </fieldset> ";
	print "\n </td></tr> ";
	print "\n </table> ";
	print "\n </fieldset> ";

	if ($HDMPRL>0) {
		print "\n <a name=\"MPSMRP\"></a> ";
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n     <legend class=\"legendTitle\">MPS/MRP</legend> ";
		require 'TopOfForm.php'; 
		print "\n <table $contentTable> ";
		$F_PLDCP=Date_FromISO_ToCYMD($row['PLDCP']);
		$F_PLDCP=Format_Date($F_PLDCP,"H");
		Build_DspFld("Planning Horizon Date",$F_PLDCP,"","N");
		if ($row[PLFCM] > "0") {
			$F_PLFCM=PeriodFromCYP($row[PLFCM]);
			Build_DspFld("Current Fiscal Period",$F_PLFCM,"","A");
		}

		if ($row[PLWDD]!="0")   {Build_DspFld("# Weeks Of Daily Periods",$row[PLWDD],"","A");}
		if ($row[PLWWD]!="0")   {Build_DspFld("# Weeks Of Weekly Periods",$row[PLWWD],"","A");}
		if ($row[PLSHR]!=".00") {Build_DspFld("Standard Hours/Day",$row[PLSHR],"","A");}
		if ($row[PLPL]!="0")	{Build_DspFld("Period Length In Days",$row[PLPL],"","A");}
		if ($row[PLNTP]!="0")	{Build_DspFld("Fixed Time Periods Weeks",$row[PLNTP],"","A");}

		if (trim($row[PLSSS])!="") {
			$fieldDesc = RtvYNDesc($row[PLSSS]);
			Build_DspFld("Include Safety Stock",$fieldDesc,"","A");
		}

		if (trim($row[PLSCS])!="") {
			$fieldDesc = RtvYNDesc($row[PLSCS]);
			Build_DspFld("Include Scrap Percent",$fieldDesc,"","A");
		}

		if (trim($row[PLSPS])!="") {
			$fieldDesc = RtvYNDesc($row[PLSPS]);
			Build_DspFld("Increase Order Quantities By Shrinkage Percentage",$fieldDesc,"","A");
		}

		if (trim($row[PLPEGR])!="") {
			$fieldDesc = RtvYNDesc($row[PLPEGR]);
			Build_DspFld("Create Pegged Requirements For Non MRP Items",$fieldDesc,"","A");
		}


		if ($row[PLMOA]!="0" || $row[PLPOA]!="0" || $row[PLMDD]!="0" || $row[PLPDD]!="0")	{
			print "\n <tr><td class=\"dsphdr\">&nbsp;</td> ";
			print "\n     <td class=\"colhdr\">Mfg</td> ";
			print "\n     <td class=\"colhdr\">Purchasing</td></tr> ";
		}

		if ($row[PLMOA]!="0" || $row[PLPOA]!="0")	{
			print "\n <tr><td class=\"dsphdr\">Order Action Periods</td> ";
			print "\n     <td class=\"dspnmbr\">$row[PLMOA]</td> ";
			print "\n     <td class=\"dspnmbr\">$row[PLPOA]</td></tr> ";
		}

		if ($row[PLMDD]!="0" || $row[PLPDD]!="0")	{
			print "\n <tr><td class=\"dsphdr\">Defer Damper Periods</td> ";
			print "\n     <td class=\"dspnmbr\">$row[PLMDD]</td> ";
			print "\n     <td class=\"dspnmbr\">$row[PLPDD]</td></tr> ";
		}


		if (trim($row[PLMRPF])!="") {
			$fieldDesc = RtvYNDesc($row[PLMRPF]);
			Build_DspFld("Use Prod Plan As MRP Input",$fieldDesc,"","A");
		}

		if (trim($row[PLFDTF])!="") {
			$fieldDesc=RetValue("FLTYPE='FORECASTDF' and FLVALU='$row[PLFDTF]'", "SYFLAG", "FLDESC");
			Build_DspFld("Forecast Date Format",$fieldDesc,"","A");
		}

		if (trim($row[PLFSPR])!="") {
			$fieldDesc=RetValue("FLTYPE='FORECASTSP' and FLVALU='$row[PLFSPR]'", "SYFLAG", "FLDESC");
			Build_DspFld("Forecast Spreaders",$fieldDesc,"","A");
		}

		if (trim($row[PLTFND])!="") {
			$fieldDesc=RetValue("TFDEF='$row[PLTFND]'", "HDMTFM", "TFDESC");
			Build_DspFld("Time Fence Definition",$fieldDesc,"","A");
		}

		if (trim($row[PLPFCT])!="") {Build_DspFld("Periods To Retain Forecast",$row[PLPFCT],"","A");}

		if (trim($row[PLCFCT])!="") {
			Build_DspFld("Periods To Consume Forecast",$row[PLCFCT],"","A");
		}
		print "\n </table> ";
		print "\n </fieldset> ";
	}

	print "\n <a name=\"shopFloor\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Shop Floor Control</legend> ";
	require 'TopOfForm.php'; 
	print "\n <table $contentTable> ";
	if (trim($row[PLBKOP])!="")	{
		$fieldDesc = RtvYNDesc($row[PLBKOP]);
		Build_DspFld("Backflush Components By Operation",$fieldDesc,"","A");
	}

	if ($row[PLLESQ]!="0") {Build_DspFld("Next Labor Sequence",$row[PLLESQ],"","A");}
	if ($row[PLNMO]!="0")  {Build_DspFld("Last Mfg Order Number",$row[PLNMO],"","A");}

	$fieldDesc=RetValue("FLTYPE='DATANCHDFT' and FLVALU='$row[PLDFTA]'", "SYFLAG", "FLDESC");
	Build_DspFld("Default Date Anchor",$fieldDesc,"","A");

	if (trim($row[PLUMFG])!="") {
		$fieldDesc=RetValue("FLTYPE='BY' and FLVALU='$row[PLUMFG]'", "SYFLAG", "FLDESC");
		Build_DspFld("Update Mfg Order Dates",$fieldDesc,"","A");
	}

	if (trim($row[PLORCL])!="")	{
		$fieldDesc = RtvYNDesc($row[PLORCL]);
		Build_DspFld("Close Orders At Receipt Entry (OR)",$fieldDesc,"","A");
	}

	if (trim($row[PLDPRW])!="") {
		$fieldDesc=RetValue("FLTYPE='PIECEREWRK' and FLVALU='$row[PLDPRW]'", "SYFLAG", "FLDESC");
		Build_DspFld("Display Pieces To Rework Field",$fieldDesc,"","A");
	}

	if (trim($row[PLHWKF])!="") {
		$fieldDesc=RetValue("FLTYPE='HOURWRKFMT' and FLVALU='$row[PLHWKF]'", "SYFLAG", "FLDESC");
		Build_DspFld("Hours Worked Format",$fieldDesc,"","A");
	}

	if (trim($row[PLCPDF])!="") {
		$fieldDesc=RetValue("FLTYPE='COMPLPIECE' and FLVALU='$row[PLCPDF]'", "SYFLAG", "FLDESC");
		Build_DspFld("Completed Pieces Default",$fieldDesc,"","A");
	}

	if (trim($row[PLRCPP])!="")	 {
		$fieldDesc = RtvYNDesc($row[PLRCPP]);
		Build_DspFld("Recalculate Completed Pieces",$fieldDesc,"","A");
	}

	if (trim($row[PLBCDC])!="")	 {
		$fieldDesc = RtvYNDesc($row[PLBCDC]);
		Build_DspFld("Bar Code/Data Collection",$fieldDesc,"","A");
	}

	if (trim($row[PLPRCD])!="")	 {
		$fieldDesc = RtvYNDesc($row[PLPRCD]);
		Build_DspFld("Add Order Process Day",$fieldDesc,"","A");
	}

	if (trim($row[PLACPO])!="") {
		$fieldDesc=RetValue("FLTYPE='AUTOEXPLPO' and FLVALU='$row[PLACPO]'", "SYFLAG", "FLDESC");
		Build_DspFld("Auto Explode Purchase Orders",$fieldDesc,"","A");
	}

	if (trim($row[PLAELV])!="") {
		$fieldDesc = RtvYNDesc($row[PLAELV]);
		Build_DspFld("Auto Explode All Levels",$fieldDesc,"","A");
	}

	if (trim($row[PLAENT])!="") {
		$fieldDesc = RtvYNDesc($row[PLAENT]);
		Build_DspFld("Activate Auto Explode Netting",$fieldDesc,"","A");
	}

	if (trim($row[PLKPOC])!="") {
		$fieldDesc=RetValue("FLTYPE='KANBANPO' and FLVALU='$row[PLKPOC]'", "SYFLAG", "FLDESC");
		Build_DspFld("Kanban Purchase Order Code",$fieldDesc,"","A");
	}

	if (trim($row[PLKBDD])!="")	 {
		$fieldDesc=RetValue("FLTYPE='KANBANDOC' and FLVALU='$row[PLKBDD]'", "SYFLAG", "FLDESC");
		Build_DspFld("Kanban Document Definition",$fieldDesc,"","A");
	}

	if (trim($row[PLSFLG])!="")	 {
		$fieldDesc = RtvYNDesc($row[PLSFLG]);
		Build_DspFld("Place Order With Shortage On Hold",$fieldDesc,"","A");
	}

	if (trim($row[PLHRSF])!="") {
		$fieldDesc=RetValue("FLTYPE='STDMILITRY' and FLVALU='$row[PLHRSF]'", "SYFLAG", "FLDESC");
		Build_DspFld("Standard Or Military Hours",$fieldDesc,"","A");
	}

	if (trim($row[PLTEF])!="") {
		$fieldDesc=RetValue("FLTYPE='TIMEENTFMT' and FLVALU='$row[PLTEF]'", "SYFLAG", "FLDESC");
		Build_DspFld("Time Entry Format",$fieldDesc,"","A");
	}

	if (trim($row[PLJCC])!="")	 {
		$fieldDesc=RetValue("FLTYPE='LBRRATEUSG' and FLVALU='$row[PLJCC]'", "SYFLAG", "FLDESC");
		Build_DspFld("WIP Labor Rate Usage Code",$fieldDesc,"","A");
	}

	if (trim($row[PLCNVS])!="")	 {
		$fieldDesc = RtvYNDesc($row[PLCNVS]);
		Build_DspFld("Convert Labor Entry Shift",$fieldDesc,"","A");
	}
	print "\n </table> ";

	print "\n <table $contentTable> ";
	if (trim($row[PLRSO1])!="" || trim($row[PLPS01])!="" || trim($row[PLRSO2])!="" || trim($row[PLPS02])!="" || trim($row[PLRSO3])!="" || trim($row[PLPS03])!="")	{
		print "\n <tr><td class=\"dsphdr\">Labor Entry Shift Override</td> ";
		print "\n     <td class=\"colhdr\">Regular</td> ";
		print "\n     <td class=\"colhdr\">Piece Rate</td></tr> ";
	}

	if (trim($row[PLRSO1])!="" || trim($row[PLPS01])!="")	 {
		print "\n <tr><td class=\"dsphdr\">Shift Code 1</td> ";
		print "\n     <td class=\"dspalph\">$row[PLRSO1]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPSO1]</td></tr> ";
	}

	if (trim($row[PLRSO2])!="" || trim($row[PLPS02])!="")	 {
		print "\n <tr><td class=\"dsphdr\">Shift Code 2</td> ";
		print "\n     <td class=\"dspalph\">$row[PLRSO2]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPSO2]</td></tr> ";
	}

	if (trim($row[PLRSO3])!="" || trim($row[PLPS03])!="")	 {
		print "\n <tr><td class=\"dsphdr\">Shift Code 3</td> ";
		print "\n     <td class=\"dspalph\">$row[PLRSO3]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPSO3]</td></tr> ";
	}
	print "\n </table> ";

	print "\n <table $contentTable> ";
	if (trim($row[PLPRPC])!="") {
		$fieldDesc=RetValue("C2CODE='$row[PLPRPC]'", "PRCODE", "C2DESC");
		Build_DspFld("Piece Rate Pay Code Default",$fieldDesc,"","A");
	}

	if (trim($row[PLPRHC])!="")	 {
		$fieldDesc=RetValue("C2CODE='$row[PLPRHC]'", "PRCODE", "C2DESC");
		Build_DspFld("Piece Rate Hours Pay Code",$fieldDesc,"","A");
	}

	if (trim($row[PLRLCD])!="")	 {
		$fieldDesc = RtvYNDesc($row[PLRLCD]);
		Build_DspFld("Restrict Labor Code Usage",$fieldDesc,"","A");
	}

	if (trim($row[PLHLDH])!="")	 {
		$fieldDesc=RetValue("FLTYPE='HALTERROR' and FLVALU='$row[PLHLDH]'", "SYFLAG", "FLDESC");
		Build_DspFld("Place Halt Errors On Hold",$fieldDesc,"","A");
	}

	if (trim($row[PLSUPA])!="")	 {
		$fieldDesc=RetValue("EVTYPE='X' and EVCODE='$row[PLSUPA]'", "HDEVNT", "EVDESC");
		Build_DspFld("Supervisor Approval Code",$fieldDesc,"","A");
	}
	print "\n </table> ";
	print "\n </fieldset> ";


	print "\n <a name=\"cycleCount\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Cycle Count</legend> ";
	require 'TopOfForm.php'; 
	print "\n <table $contentTable> ";
	if (trim($row[PLABCI])!="")	{
		$fieldDesc = RtvYNDesc($row[PLABCI]);
		print "\n <tr><td class=\"dsphdr\">ABC Analysis By Inventory Type</td> ";
		print "\n     <td class=\"dspalph\">$fieldDesc</td></tr> ";
	}
	print "\n </table> ";

	print "\n <table $contentTable> ";
	if ($row[PLVLA]!=".000" || $row[PLVLB]!=".000" || $row[PLVLC]!=".000" || $row[PLCAW]!="0" || $row[PLCBW]!="0" || $row[PLCCW]!="0" || $row[PLPCA]!="0" || $row[PLPCB]!="0") {
		print "\n <tr><td class=\"dsphdr\"></td> ";
		print "\n     <td class=\"colhdr\">Class<br>\"A\"</td> ";
		print "\n     <td class=\"colhdr\">Class<br>\"B\"</td> ";
		print "\n     <td class=\"colhdr\">Class<br>\"C\"</td></tr> ";
	}

	if ($row[PLVLA]!=".000" || $row[PLVLB]!=".000" || $row[PLVLC]!=".000") {
		$F_PLVLA=Format_Nbr($row[PLVLA]*100, "1", ($pctEditCode), "Y", "", "");
		$F_PLVLB=Format_Nbr($row[PLVLB]*100, "1", ($pctEditCode), "Y", "", "");
		$F_PLVLC=Format_Nbr($row[PLVLC]*100, "1", ($pctEditCode), "Y", "", "");
		print "\n <tr><td class=\"dsphdr\">Acceptance Limit Percentage</td> ";
		print "\n     <td class=\"dspnmbr\">$F_PLVLA</td> ";
		print "\n     <td class=\"dspnmbr\">$F_PLVLB</td> ";
		print "\n     <td class=\"dspnmbr\">$F_PLVLC</td></tr> ";
	}

	if ($row[PLCAW]!="0" || $row[PLCBW]!="0" || $row[PLCCW]!="0") {
		print "\n <tr><td class=\"dsphdr\">Cycle Days</td> ";
		print "\n     <td class=\"dspnmbr\">$row[PLCAW]</td> ";
		print "\n     <td class=\"dspnmbr\">$row[PLCBW]</td> ";
		print "\n     <td class=\"dspnmbr\">$row[PLCCW]</td></tr> ";
	}

	if ($row[PLPCA]!="0" || $row[PLPCB]!="0")	{
		print "\n <tr><td class=\"dsphdr\">Percentage For</td> ";
		print "\n     <td class=\"dspnmbr\">$row[PLPCA]</td> ";
		print "\n     <td class=\"dspnmbr\">$row[PLPCB]</td></tr> ";
	}
	print "\n </table> ";
	print "\n </fieldset> ";


	print "\n <a name=\"accountNumbers\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Accounts</legend> ";
	require 'TopOfForm.php'; 
	print "\n <table $contentTable> ";
	if ($row[PLFSIA]!="0")	{
		$F_AcctSub=Format_Acct($row['PLFSIA'],$row['PLFSIS'],"N");
		Build_DspFld("Floorstock Inventory",$F_AcctSub,"","N");
	}

	if ($row[PLUVRA]!="0")	{
		$F_AcctSub=Format_Acct($row['PLUVRA'],$row['PLUVRS'],"N");
		Build_DspFld("Unvouchered Receipts",$F_AcctSub,"","N");
	}

	if ($row[PLVSVA]!="0")	{
		$F_AcctSub=Format_Acct($row['PLVSVA'],$row['PLVSVS'],"N");
		Build_DspFld("Vendor Services Variance",$F_AcctSub,"","N");
	}
	print "\n </table> ";

	print "\n <table $contentTable> ";
	if ($row[PLWP1A]!="0" || $row[PLWV1A]!="0" || $row[PLMA1A]!="0" || $row[PLPA1A]!="0" || $row[PLWP2A]!="0" || $row[PLWV2A]!="0" || $row[PLMA2A]!="0" || $row[PLPA2A]!="0" || $row[PLWP3A]!="0" || $row[PLWV3A]!="0" || $row[PLMA3A]!="0" || $row[PLPA3A]!="0" || $row[PLWP4A]!="0" || $row[PLWV4A]!="0" || $row[PLMA4A]!="0" || $row[PLPA4A]!="0" || $row[PLWP5A]!="0" || $row[PLWV5A]!="0" || $row[PLMA5A]!="0" || $row[PLPA5A]!="0")	{
		print "\n <tr><td class=\"dsphdr\"></td> ";
		print "\n     <td class=\"colhdr\">WIP</td> ";
		print "\n     <td class=\"colhdr\">WIP Variance</td> ";
		print "\n     <td class=\"colhdr\">Manufactured<br>Cost Absorption</td> ";
		print "\n     <td class=\"colhdr\">Purchased<br>Cost Absorption</td></tr> ";
	}

	if ($row[PLWP1A]!="0" || $row[PLWV1A]!="0" || $row[PLMA1A]!="0" || $row[PLPA1A]!="0")	{
		print "\n <tr><td class=\"dsphdr\">Cost Category 1</td> ";
		$F_AcctSub=Format_Acct($row['PLWP1A'],$row['PLWP1S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLWV1A'],$row['PLWV1S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLMA1A'],$row['PLMA1S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLPA1A'],$row['PLPA1S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		print "\n </tr> ";
	}

	if ($row[PLWP2A]!="0" || $row[PLWV2A]!="0" || $row[PLMA2A]!="0" || $row[PLPA2A]!="0")	{
		print "\n <tr><td class=\"dsphdr\">Cost Category 2</td> ";
		$F_AcctSub=Format_Acct($row['PLWP2A'],$row['PLWP2S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLWV2A'],$row['PLWV2S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLMA2A'],$row['PLMA2S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLPA2A'],$row['PLPA2S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		print "\n </tr> ";
	}

	if ($row[PLWP3A]!="0" || $row[PLWV3A]!="0" || $row[PLMA3A]!="0" || $row[PLPA3A]!="0")	{
		print "\n <tr><td class=\"dsphdr\">Cost Category 3</td> ";
		$F_AcctSub=Format_Acct($row['PLWP3A'],$row['PLWP3S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLWV3A'],$row['PLWV3S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLMA3A'],$row['PLMA3S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLPA3A'],$row['PLPA3S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		print "\n </tr> ";
	}

	if ($row[PLWP4A]!="0" || $row[PLWV4A]!="0" || $row[PLMA4A]!="0" || $row[PLPA4A]!="0")	{
		print "\n <tr><td class=\"dsphdr\">Cost Category 4</td> ";
		$F_AcctSub=Format_Acct($row['PLWP4A'],$row['PLWP4S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLWV4A'],$row['PLWV4S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLMA4A'],$row['PLMA4S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLPA4A'],$row['PLPA4S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		print "\n </tr> ";
	}

	if ($row[PLWP5A]!="0" || $row[PLWV5A]!="0" || $row[PLMA5A]!="0" || $row[PLPA5A]!="0")	{
		print "\n <tr><td class=\"dsphdr\">Cost Category 5</td> ";
		$F_AcctSub=Format_Acct($row['PLWP5A'],$row['PLWP5S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLWV5A'],$row['PLWV5S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLMA5A'],$row['PLMA5S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		$F_AcctSub=Format_Acct($row['PLPA5A'],$row['PLPA5S'],"N");
		print "\n     <td class=\"colcode\">$F_AcctSub</td> ";
		print "\n </tr> ";
	}
	print "\n </table> ";
	print "\n </fieldset> ";


	print "\n <a name=\"userDefined\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">User-Defined</legend> ";
	require 'TopOfForm.php'; 
	print "\n <table $contentTable> ";
	if (trim($row[PLPAS1])!="" || trim($row[PLPA11])!="" || trim($row[PLPA12])!="" || trim($row[PLPAS2])!="" || trim($row[PLPA21])!="" || trim($row[PLPA22])!="" || trim($row[PLPAS3])!="" || trim($row[PLPA31])!="" || trim($row[PLPA32])!="" || trim($row[PLPNS1])!="" || trim($row[PLPN11])!="" || trim($row[PLPN12])!="" || trim($row[PLPNS2])!="" || trim($row[PLPN21])!="" || trim($row[PLPN22])!="" || trim($row[PLPNS3])!="" || trim($row[PLPN31])!="" || trim($row[PLPN32])!="")	{
		print "\n <tr><td class=\"dsphdr\"></td> ";
		print "\n     <td class=\"colhdr\">Description</td> ";
		print "\n     <td class=\"colhdr\" colspan=\"2\">Report Heading</td></tr> ";
	}

	if (trim($row[PLPAS1])!="" || trim($row[PLPA11])!="" || trim($row[PLPA12])!="")	{
		print "\n <tr><td class=\"dsphdr\">Plant Alpha 1</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPAS1]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPA11]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPA12]</td></tr> ";
	}

	if (trim($row[PLPAS2])!="" || trim($row[PLPA21])!="" || trim($row[PLPA22])!="") {
		print "\n <tr><td class=\"dsphdr\">Plant Alpha 2</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPAS2]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPA21]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPA22]</td></tr> ";
	}

	if (trim($row[PLPAS3])!="" || trim($row[PLPA31])!="" || trim($row[PLPA32])!="") {
		print "\n <tr><td class=\"dsphdr\">Plant Alpha 3</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPAS3]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPA31]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPA32]</td></tr> ";
	}

	if (trim($row[PLPNS1])!="" || trim($row[PLPN11])!="" || trim($row[PLPN12])!="")	{
		print "\n <tr><td class=\"dsphdr\">Plant Numeric 1</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPNS1]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPN11]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPN12]</td></tr> ";
	}

	if (trim($row[PLPNS2])!="" || trim($row[PLPN21])!="" || trim($row[PLPN22])!="") {
		print "\n <tr><td class=\"dsphdr\">Plant Numeric 2</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPNS2]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPN21]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPN22]</td></tr> ";
	}

	if (trim($row[PLPNS3])!="" || trim($row[PLPN31])!="" || trim($row[PLPN32])!="") {
		print "\n <tr><td class=\"dsphdr\">Plant Numeric 3</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPNS3]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPN31]</td> ";
		print "\n     <td class=\"dspalph\">$row[PLPN32]</td></tr> ";
	}
	print "\n </table> ";
	print "\n </fieldset> ";

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
