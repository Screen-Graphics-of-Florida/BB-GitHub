<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Vendor Pricing Level";
$scriptName    = "VendorPricingLevel.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL    = "{$baseURL}&amp;tag=INPUT&amp;startRow=" . urlencode($startRow);
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("VLPMLV","A","Level"));
$programName    = "HPOPLM_E";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n function validate(searchForm) {";
	print "\n if (editNum(document.Search.srchLevel, 2, 0) &&";
	print "\n     editNum(document.Search.srchWhse, 2, 0) &&";
	print "\n     editNum(document.Search.srchItem, 2, 0) &&";
	print "\n     editNum(document.Search.srchVend, 2, 0))";
	print "\n     return true; ";
	print "\n    }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "VENDORPRICINGLEVELSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Level","srchLevel","","operLevel","opersel_num_short","N","2","2");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	print "\n </table>";

	print "\n <fieldset class=\"legendBody\">";
	print "\n <legend class=\"legendTitle\">Category Sequence (0-3)</legend>";
	print "\n <table $contentTable>";
	print "\n <colgroup>";
	Build_AdvSrch_Entry("Warehouse","srchWhse","","operWhse","opersel_num_short","N","1","1");
	Build_AdvSrch_Entry("Item Number","srchItem","","operItem","opersel_num_short","N","1","1");
	Build_AdvSrch_Entry("Vendor Number","srchVend","","operVend","opersel_num_short","N","1","1");
	print "\n </table>";
	print "\n </fieldset>";

	print "\n <fieldset class=\"legendBody\">";
	print "\n <legend class=\"legendTitle\">Structure Definition (Y,N)</legend>";
	print "\n <table $contentTable>";
	print "\n <colgroup>";
	Build_AdvSrch_Entry("Contract","srchVlcn","","operVlcn","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Dollar Amount","srchVldl","","operVldl","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Use Percentages","srchVlup","","operVlup","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Bracket By Amount","srchVlbp","","operVlbp","opersel_alph_short","A","1","1");
	print "\n </table>";
	print "\n </fieldset>";
	$focusField = "srchLevel";
	print "\n <table $contentTable>";
	require_once 'AdvSearchBottom.php';
	print "\n </td></tr>";

	exit;
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Level")         {$orby = array(array("VLPMLV","A","Level"));}
	elseif ($sequence == "Description")   {$orby = array(array("VLLVDS","A","Description"));}
	elseif ($sequence == "Whs")           {$orby = array(array("VLWHSQ","A","Whs"));}
	elseif ($sequence == "Item Number")   {$orby = array(array("VLITSQ","A","Item Number"));}
	elseif ($sequence == "Vendor Number") {$orby = array(array("VLVNSQ","A","Vendor Number"));}
	elseif ($sequence == "Contract")      {$orby = array(array("VLCN","A","Contract"));}
	elseif ($sequence == "Dollar Amount") {$orby = array(array("VLDL","A","Dollar Amount"));}
	elseif ($sequence == "Use Pct")       {$orby = array(array("VLUP","A","Use Pct"));}
	elseif ($sequence == "Bracket By Amt"){$orby = array(array("VLBP","A","Bracket By Amt"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard("VLPMLV", "Pricing Level", $_POST['srchLevel'], "", $_POST['operLevel'], "N");
	$returnValue=Build_WildCard("VLLVDS", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("VLWHSQ",  "Warehouse", $_POST['srchWhse'], "", $_POST['operWhse'], "N");
	$returnValue=Build_WildCard("VLITSQ", "Item Number", $_POST['srchItem'], "U", $_POST['operItem'], "A");
	$returnValue=Build_WildCard("VLVNSQ", "Vendor Number", $_POST['srchVend'], "", $_POST['operVend'], "N");
	$returnValue=Build_WildCard("VLCN",   "Contract", $_POST['srchVlcn'], "U", $_POST['operVlcn'], "A");
	$returnValue=Build_WildCard("VLDL",   "Dollar Amount", $_POST['srchVldl'], "U", $_POST['operVldl'], "A");
	$returnValue=Build_WildCard("VLUP",   "Use Percentage", $_POST['srchVlup'], "U", $_POST['operVlup'], "A");
	$returnValue=Build_WildCard("VLBP",   "Bracket By Amount", $_POST['srchVlbp'], "U", $_POST['operVlbp'], "A");
	require_once 'WildCardUpdate.php';
}

if ($tag != "EXPORT"){
	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);
	$formName = "Search";

	print "\n \n <script TYPE=\"text/javascript\">";
	require_once 'AJAXRequest.js';
	require_once 'CheckEnterSearch.php';
	require_once 'CheckSel.js';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'SaveCurrentURL.php';
	require_once 'ShowHideSelCriteria.php';
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
	require_once 'NoFormValidate.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "Vendor Pricing Level";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		// Program Option Security
		$hpoplm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
		print "\n <td class=\"toolbar\">";
		if ($hpoplm_OPT['sec_01'] == "Y"){print "\n <a href=\"{$homeURL}{$phpPath}VendorPricingLevelMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a>";}
		require_once 'XMLFormat.php';
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td></tr></table>";
	}
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

$uv_Level ='VLPMLV';
$uv_Description = 'VLLVDS';
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .=  " Select * ";
$fileSQL .= " POVPLV ";
if  ($wildCardSearch != "" || $uv_Sql != ""){$selectSQL .= " VLPMLV>=0 ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	if ($formatToPrint == ""){
	}

	print "<table $contentTable>";
	if ($formatToPrint == "") {
		print "\n <tr><th class=\"dspalph\" colspan=\"3\">";
		$qsOpt  = "\n <option value=\"upper(VLLVDS)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
		$qsOpt .= "\n <option value=\"VLPMLV|null|Level|N|\" title=\"Level\">Level";
		require 'QuickSearchOption.php';
		print "\n </th>";
	} else {print "\n <tr><th class=\"dspalph\" colspan=\"2\">"; print "\n </th>";	}
	print "\n     <th class=\" grphdr\" colspan=\"3\">Category Sequence</th>";
	print "\n     <th class=\" grphdr\" colspan=\"4\">Structure Definition</th></tr>";

	print "\n <tr>";
	if ($formatToPrint != "Y" && ($hpoplm_OPT['sec_02'] == "Y" || $hpoplm_OPT['sec_03'] == "Y" || $hpoplm_OPT['sec_04'] == "Y")){
		print "<th class=\"colhdr\">$optionHeading</th>";
	}
	$returnValue=OrderBy_Sort("VLPMLV");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Level\"  title=\"Sequence By Pricing Level\">{$sortPoint}Level</a></th>";
	$returnValue=OrderBy_Sort("VLLVDS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\"    title=\"Sequence By Description\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("VLWHSQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Whs\" title=\"Sequence By Warehouse\">{$sortPoint}Whs</a></th>";
	$returnValue=OrderBy_Sort("VLITSQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Item Number\"    title=\"Sequence By Item Number\">{$sortPoint}Item<br>Number</a></th>";
	$returnValue=OrderBy_Sort("VLVNSQ");    $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Vendor Number\"   title=\"Sequence By Vendor Number\">{$sortPoint}Vendor<br>Number</a></th>";
	$returnValue=OrderBy_Sort("VLCN");   $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Contract\"     title=\"Sequence By Contract\">{$sortPoint}Contract</a></th>";
	$returnValue=OrderBy_Sort("VLDL");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Dollar Amount\"   title=\"Sequence By Dollar Amount\">{$sortPoint}Dollar<br>Amount</a></th>";
	$returnValue=OrderBy_Sort("VLUP");   $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Use Pct\"     title=\"Sequence By Use Percentage\">{$sortPoint}Use<br>Pct</a></th>";
	$returnValue=OrderBy_Sort("VLBP");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Bracket By Amt\"   title=\"Sequence By Bracket By Quantity\">{$sortPoint}Bracket<br>By Quantity</a></th>";
	print "\n </tr>";
}

if ($tag == "EXPORT"){$xmlListName = "VendorPricingLevelList"; require_once 'XMLInit.php';}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	if ($tag == "EXPORT"){
		$xmlID  = $xmlDoc->createElement(VendorPriceLevel); $xmlRoot->appendChild($xmlID);
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Level"));          $xmlTag->appendChild($xmlDoc->createTextNode($row['VLPMLV']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Desc"));           $xmlTag->appendChild($xmlDoc->createTextNode($row['VLLVDS']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("WarehouseSequence"));      $xmlTag->appendChild($xmlDoc->createTextNode($row['VLWHSQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ItemSequence"));    $xmlTag->appendChild($xmlDoc->createTextNode($row['VLITSQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("VendorSequence"));  $xmlTag->appendChild($xmlDoc->createTextNode($row['VLVNSQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Contract"));       $xmlTag->appendChild($xmlDoc->createTextNode($row['VLCN']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("DollarAmount"));  $xmlTag->appendChild($xmlDoc->createTextNode($row['VLDL']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("UsePercentage")); $xmlTag->appendChild($xmlDoc->createTextNode($row['VLUP']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("BracketAmount")); $xmlTag->appendChild($xmlDoc->createTextNode($row['VLBP']));

	} else {
		$maintainVar = "{$scriptVarBase}&amp;pricingLevel=" . urlencode($row['VLPMLV']) . "&amp;levelDesc=" . urlencode($row['VLLVDS']) . "&amp;fromScript=" . urlencode($scriptName);
		$maintainVarD2w = "{$altVarBase}&amp;pricingLevel=" . urlencode($row['VLPMLV']) . "&amp;levelDesc=" . urlencode($row['VLLVDS']) . "&amp;fromScript=" . urlencode($scriptName);
		$confirmDesc = Format_Confirm_Desc($row['VLLVDS'], $row['VLPMLV'], "", "", "", "");
		$dtlCount    = RetValue("VDPMLV=$row[VLPMLV]", "POVPDT", "count(*)");
		$vlcnImage = "&nbsp;";
		$vldlImage = "&nbsp;";
		$vlupImage = "&nbsp;";
		$vlbpImage = "&nbsp;";
		if ($row[VLCN] == "Y"){$vlcnImage = $selectedImageSml; }
		if ($row[VLDL] == "Y"){$vldlImage = $selectedImageSml; }
		if ($row[VLUP] == "Y"){$vlupImage = $selectedImageSml; }
		if ($row[VLBP] == "Y"){$vlbpImage = $selectedImageSml; }
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		if ($formatToPrint != "Y" && ($hpoplm_OPT['sec_02'] == "Y" || $hpoplm_OPT['sec_03'] == "Y" || $hpoplm_OPT['sec_04'] == "Y")){
			print "\n <td class=\"opticon\">";
			if ($hpoplm_OPT['sec_02'] == "Y" || $hpoplm_OPT['sec_03'] == "Y"){
				print "\n <a href=\"{$homeURL}{$phpPath}VendorPricingLevelMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;fromLevel=" . urlencode($row['VLPMLV']) . "&amp;maintenanceCode=C\">$changeImageSml</a>";
			}
			if ($hpoplm_OPT['sec_01'] == "Y" && $hpoplm_OPT['sec_04'] == "Y"){
				print "\n <a href=\"{$homeURL}{$phpPath}VendorPricingLevelMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;fromLevel=" . urlencode($row['VLPMLV']) . "&amp;maintenanceCode=Z\">$copyImageSml</a>";
			}
			if ($hpoplm_OPT['sec_03'] == "Y" && $dtlCount == '0') {
				print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}VendorPricingLevelMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;fromLevel=" . urlencode($row['VLPMLV']) . "&amp;maintenanceCode=D\">$deleteImageSml</a>";
			}

			print "\n </td>";
		}
		$F_VLWHSQ=Format_Nbr($row['VLWHSQ'],  "0", "Z", "Y", "", "");
		$F_VLITSQ=Format_Nbr($row['VLITSQ'],  "0", "Z", "Y", "", "");
		$F_VLVNSQ=Format_Nbr($row['VLVNSQ'],  "0", "Z", "Y", "", "");
		print "\n <td class=\"colnmbr\">$row[VLPMLV]</td>";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}VendorPricingDetail.php{$maintainVar}&amp;tag=LOAD\" title=\"View Vendor Pricing Detail\">$row[VLLVDS]</a></td>";
		print "\n <td class=\"colcode\">$F_VLWHSQ</td>";
		print "\n <td class=\"colcode\">$F_VLITSQ</td>";
		print "\n <td class=\"colcode\">$F_VLVNSQ</td>";
		print "\n <td class=\"colcode\">$vlcnImage</td>";
		print "\n <td class=\"colcode\">$vldlImage</td>";
		print "\n <td class=\"colcode\">$vlupImage</td>";
		print "\n <td class=\"colcode\">$vlbpImage</td>";
	}
	$startRow ++;
	$rowCount ++;
}

require_once 'XMLExport.php';

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>
