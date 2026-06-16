<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once "MCControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Customer Pricing Level";
$scriptName    = "PricingLevel.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL    = "{$baseURL}&amp;startRow=" . urlencode($startRow);
$filterURL     = "{$scriptName}{$scriptVarBase}";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("PVPMLV","A","Level"));
$programName    = "HOEPLM_E";

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
	print "\n if (editNum(document.Search.srchLevel, 3, 0) &&";
	print "\n     editNum(document.Search.srchWhs, 2, 0) &&";
	print "\n     editNum(document.Search.srchItem, 2, 0) &&";
	print "\n     editNum(document.Search.srchCust, 2, 0) &&";
	print "\n     editNum(document.Search.srchClass, 2, 0) &&";
	print "\n     editNum(document.Search.srchProdCls, 2, 0) &&";
	print "\n     editNum(document.Search.srchRegion, 2, 0) &&";
	print "\n     editNum(document.Search.srchProdGrp, 2, 0)";
	if ($MUPMCD == "Y") {
		print "\n     && editNum(document.Search.srchCurr, 2, 0)";
	}
	print "\n    )";
	print "\n     return true; ";
	print "\n    }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "PRICINGLEVELSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Level","srchLevel","","operLevel","opersel_num_short","N","3","3");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	print "\n </table>";

	print "\n <fieldset class=\"legendBody\">";
	if ($MUPMCD == "Y") {
		$catLegendText = "Category Sequence (0-8)";
	} else {
		$catLegendText = "Category Sequence (0-7)";
	}
	print "\n <legend class=\"legendTitle\">$catLegendText</legend>";
	print "\n <table $contentTable>";
	print "\n <colgroup>";
	Build_AdvSrch_Entry("Warehouse","srchWhs","","operWhs","opersel_num_short","N","1","1");
	Build_AdvSrch_Entry("Item Number","srchItem","","operItem","opersel_num_short","N","1","1");
	Build_AdvSrch_Entry("Customer Number","srchCust","","operCust","opersel_num_short","N","1","1");
	Build_AdvSrch_Entry("Customer Class","srchClass","","operClass","opersel_num_short","N","1","1");
	Build_AdvSrch_Entry("Product Class","srchProdCls","","operProdCls","opersel_num_short","N","1","1");
	Build_AdvSrch_Entry("Region","srchRegion","","operRegion","opersel_num_short","N","1","1");
	Build_AdvSrch_Entry("Product Group","srchProdGrp","","operProdGrp","opersel_num_short","N","1","1");
	if ($MUPMCD == "Y") {
		Build_AdvSrch_Entry("Currency","srchCurr","","operCurr","opersel_num_short","N","1","1");
	}
	print "\n </table>";
	print "\n </fieldset>";

	print "\n <fieldset class=\"legendBody\">";
	print "\n <legend class=\"legendTitle\">Structure Definition (Y,N)</legend>";
	print "\n <table $contentTable>";
	print "\n <colgroup>";
	Build_AdvSrch_Entry("Contract","srchContract","","operContract","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("List Less Amount","srchListLess","","operListLess","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Cost Plus Amount","srchCostPlus","","operCostPlus","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Amount","srchAmount","","operAmount","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Use Percentages","srchUsePct","","operUsePct","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Bracket By Quantity","srchBrkQty","","operBrkQty","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Bracket By Amount","srchBrkAmt","","operBrkAmt","opersel_alph_short","A","1","1");
	Build_AdvSrch_Entry("Commissionable","srchComm","","operComm","opersel_alph_short","A","1","1");
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

if ($tag == "ORDERBY") {
	if     ($sequence == "PricingLevel") {$orby = array(array("PVPMLV","A","Level"));}
	elseif ($sequence == "Description")  {$orby = array(array("PVLVDSU","A","Description"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "WhSequence")   {$orby = array(array("PVWHSQ","A","Warehouse"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "ItSequence")   {$orby = array(array("PVITSQ","A","Item Number"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "CsSequence")   {$orby = array(array("PVCSSQ","A","Customer Number"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "CcSequence")   {$orby = array(array("PVCCSQ","A","Customer Class"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "PcSequence")   {$orby = array(array("PVPCSQ","A","Product Class"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "RgSequence")   {$orby = array(array("PVRGSQ","A","Region"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "PgSequence")   {$orby = array(array("PVPGSQ","A","Product Group"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "CuSequence")   {$orby = array(array("PVCUSQ","A","Currency"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "Contract")     {$orby = array(array("PVCN","A","Contract"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "ListLess")     {$orby = array(array("PVLL","A","List Less Amount"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "CostPlus")     {$orby = array(array("PVCP","A","Cost Plus Amount"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "DollarAmt")    {$orby = array(array("PVDL","A","Amount"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "UsePercent")   {$orby = array(array("PVUP","A","Use Percentages"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "BracketQty")   {$orby = array(array("PVBP","A","Bracket By Quantity"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "BracketAmt")   {$orby = array(array("PVBA","A","Bracket By Amount"),array("PVPMLV","A","Level"));}
	elseif ($sequence == "Commission")   {$orby = array(array("PVCOMM","A","Commissionable"),array("PVPMLV","A","Level"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {require_once 'QuickSearch.php';}

if ($tag == "WILDCARD") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard("PVPMLV", "Level",              $_POST['srchLevel'],    "", $_POST['operLevel'],   "N");
	$returnValue=Build_WildCard("upper(PVLVDS)", "Description", $_POST['srchDesc'],    "U", $_POST['operDesc'],    "A");
	$returnValue=Build_WildCard("PVWHSQ", "Warehouse",          $_POST['srchWhs'],      "", $_POST['operWhs'],     "N");
	$returnValue=Build_WildCard("PVITSQ", "Item Number",        $_POST['srchItem'],     "", $_POST['operItem'],    "N");
	$returnValue=Build_WildCard("PVCSSQ", "Customer Number",    $_POST['srchCust'],     "", $_POST['operCust'],    "N");
	$returnValue=Build_WildCard("PVCCSQ", "Customer Class",     $_POST['srchClass'],    "", $_POST['operClass'],   "N");
	$returnValue=Build_WildCard("PVPCSQ", "Product Class",      $_POST['srchProdCls'],  "", $_POST['operProdCls'], "N");
	$returnValue=Build_WildCard("PVRGSQ", "Region",             $_POST['srchRegion'],   "", $_POST['operRegion'],  "N");
	$returnValue=Build_WildCard("PVPGSQ", "Product Group",      $_POST['srchProdGrp'],  "", $_POST['operProdGrp'], "N");
	if ($MUPMCD == "Y") {$returnValue=Build_WildCard("PVCUSQ", "Currency", $_POST['srchCurr'], "", $_POST['operCurr'], "N");}
	$returnValue=Build_WildCard("PVCN",   "Contract",           $_POST['srchContract'],"U", $_POST['operContract'],"A");
	$returnValue=Build_WildCard("PVLL",   "List Less Amount",   $_POST['srchListLess'],"U", $_POST['operListLess'],"A");
	$returnValue=Build_WildCard("PVCP",   "Cost Plus Amount",   $_POST['srchCostPlus'],"U", $_POST['operCostPlus'],"A");
	$returnValue=Build_WildCard("PVDL",   "Amount",             $_POST['srchAmount'],  "U", $_POST['operAmount'],  "A");
	$returnValue=Build_WildCard("PVUP",   "Use Percentages",    $_POST['srchUsePct'],  "U", $_POST['operUsePct'],  "A");
	$returnValue=Build_WildCard("PVBP",   "Bracket By Quantity",$_POST['srchBrkQty'],  "U", $_POST['operBrkQty'],  "A");
	$returnValue=Build_WildCard("PVBA",   "Bracket By Amount",  $_POST['srchBrkAmt'],  "U", $_POST['operBrkAmt'],  "A");
	$returnValue=Build_WildCard("PVCOMM", "Commissionable",     $_POST['srchComm'],    "U", $_POST['operComm'],    "A");
	require_once 'WildCardUpdate.php';
}

if ($tag != "EXPORT") {
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
	$pageID = "PRICINGLEVEL";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
	print "  <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		// Program Option Security
		$hpoplm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
		print "\n <td class=\"toolbar\">";
		if ($hpoplm_OPT['sec_01'] == "Y"){print "\n <a href=\"{$homeURL}{$phpPath}PricingLevelMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a>";}
		require_once 'XMLFormat.php';
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td>";
	}
	print "  </tr></table>";
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

require 'stmtSQLClear.php';
$stmtSQL .=  " Select HDPRLC.*, upper(PVLVDS) as PVLVDSU ";
$fileSQL .= " HDPRLC ";
if  ($wildCardSearch != "") {$selectSQL .= " PVPMLV>=0 ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	print "<table $contentTable>";
	if ($formatToPrint == "") {
		print "\n <tr><th class=\"dspalph\" colspan=\"3\">";
		$qsOpt  = "\n <option value=\"upper(PVLVDS)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
		$qsOpt .= "\n <option value=\"PVPMLV|null|Level|N|\" title=\"Level\">Level";
		require 'QuickSearchOption.php';
		print "\n </th>";
	} else {
		print "\n <tr><th class=\"dspalph\" colspan=\"2\">";
		print "\n </th>";
	}
	$catSeqSpan = ($MUPMCD == "Y") ? '8' : '7';
	print "\n <th class=\" grphdr\" colspan=\"$catSeqSpan\">Category Sequence</th>";
	print "\n <th class=\" grphdr\" colspan=\"8\">Structure Definition</th></tr>";

	print "\n <tr>";
	if ($formatToPrint != "Y" && ($hpoplm_OPT['sec_02'] == "Y" || $hpoplm_OPT['sec_03'] == "Y" || $hpoplm_OPT['sec_04'] == "Y")){
		print "<th class=\"colhdr\">$optionHeading</th>";
	}
	$returnValue=OrderBy_Sort("PVPMLV");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PricingLevel\"  title=\"Sequence By Pricing Level\">{$sortPoint}Level</a></th>";
	$returnValue=OrderBy_Sort("PVLVDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\"    title=\"Sequence By Description, Pricing Level\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("PVWHSQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=WhSequence\" title=\"Sequence By Warehouse, Pricing Level\">{$sortPoint}Whs</a></th>";
	$returnValue=OrderBy_Sort("PVITSQ"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ItSequence\"    title=\"Sequence By Item Number, Pricing Level\">{$sortPoint}Item<br>Number</a></th>";
	$returnValue=OrderBy_Sort("PVCSSQ");    $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CsSequence\"   title=\"Sequence By Customer Number, Pricing Level\">{$sortPoint}Customer<br>Number</a></th>";
	$returnValue=OrderBy_Sort("PVCCSQ");    $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CcSequence\"   title=\"Sequence By Customer Class, Pricing Level\">{$sortPoint}Customer<br>Class</a></th>";
	$returnValue=OrderBy_Sort("PVPCSQ");    $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PcSequence\"   title=\"Sequence By Product Class, Pricing Level\">{$sortPoint}Prod<br>Class</a></th>";
	$returnValue=OrderBy_Sort("PVRGSQ");    $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=RgSequence\"   title=\"Sequence By Region, Pricing Level\">{$sortPoint}Region</a></th>";
	$returnValue=OrderBy_Sort("PVPGSQ");    $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PgSequence\"   title=\"Sequence By Product Group, Pricing Level\">{$sortPoint}Prod<br>Group</a></th>";
	if ($MUPMCD == "Y") {
		$returnValue=OrderBy_Sort("PVCUSQ");    $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CuSequence\"   title=\"Sequence By Currency, Pricing Level\">{$sortPoint}Currency</a></th>";
	}
	$returnValue=OrderBy_Sort("PVCN");   $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Contract\"     title=\"Sequence By Contract, Pricing Leve\">{$sortPoint}Contract</a></th>";
	$returnValue=OrderBy_Sort("PVLL");   $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ListLess\"     title=\"Sequence By List Less Amount, Pricing Leve\">{$sortPoint}List<br>Less<br>Amount</a></th>";
	$returnValue=OrderBy_Sort("PVCP");   $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CostPlus\"     title=\"Sequence By ost Plus Amount, Pricing Leve\">{$sortPoint}Cost<br>Plus<br>Amount</a></th>";
	$returnValue=OrderBy_Sort("PVDL");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DollarAmt\"   title=\"Sequence By Amount, Pricing Level\">{$sortPoint}Amount</a></th>";
	$returnValue=OrderBy_Sort("PVUP");   $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=UsePercent\"     title=\"Sequence By Use Percentages, Pricing Level\">{$sortPoint}Use<br>Pct</a></th>";
	$returnValue=OrderBy_Sort("PVBP");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BracketQty\"   title=\"Sequence By Bracket By Quantity, Pricing Level\">{$sortPoint}Bracket<br>By Qty</a></th>";
	$returnValue=OrderBy_Sort("PVBA");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BracketAmt\"   title=\"Sequence By Bracket By Amount, Pricing Level\">{$sortPoint}Bracket<br>By Amt</a></th>";
	$returnValue=OrderBy_Sort("PVCOMM");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Commission\"   title=\"Sequence By Commissionable, Pricing Level\">{$sortPoint}Commissionable</a></th>";
	print "\n </tr>";
}

if ($tag == "EXPORT"){$xmlListName = "PricingLevelList"; require_once 'XMLInit.php';}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	if ($tag == "EXPORT"){
		$xmlID  = $xmlDoc->createElement(PriceLevel); $xmlRoot->appendChild($xmlID);
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Level"));          	$xmlTag->appendChild($xmlDoc->createTextNode($row['PVPMLV']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Description"));       $xmlTag->appendChild($xmlDoc->createTextNode($row['PVLVDS']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("WarehouseSeq"));	    $xmlTag->appendChild($xmlDoc->createTextNode($row['PVWHSQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ItemNumberSeq"));		$xmlTag->appendChild($xmlDoc->createTextNode($row['PVITSQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CustomerNumberSeq"));	$xmlTag->appendChild($xmlDoc->createTextNode($row['PVCSSQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CustomerClassSeq"));	$xmlTag->appendChild($xmlDoc->createTextNode($row['PVCCSQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ProductClassSeq"));	$xmlTag->appendChild($xmlDoc->createTextNode($row['PVPCSQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("RegionSeq"));	        $xmlTag->appendChild($xmlDoc->createTextNode($row['PVRGSQ']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ProductGroupSeq"));	$xmlTag->appendChild($xmlDoc->createTextNode($row['PVPGSQ']));
		if ($MUPMCD == "Y") {
			$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CurrencySeq"));	$xmlTag->appendChild($xmlDoc->createTextNode($row['PVCUSQ']));
		}
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ContractPricing"));   $xmlTag->appendChild($xmlDoc->createTextNode($row['PVCN']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ListLessAmount"));    $xmlTag->appendChild($xmlDoc->createTextNode($row['PVLL']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CostPlusAmount"));    $xmlTag->appendChild($xmlDoc->createTextNode($row['PVCP']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Amount"));  	        $xmlTag->appendChild($xmlDoc->createTextNode($row['PVDL']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("UsePercentages")); 	$xmlTag->appendChild($xmlDoc->createTextNode($row['PVUP']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("BracketByQty")); 	    $xmlTag->appendChild($xmlDoc->createTextNode($row['PVBP']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("BracketByAmt")); 	    $xmlTag->appendChild($xmlDoc->createTextNode($row['PVBA']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Commissionable")); 	$xmlTag->appendChild($xmlDoc->createTextNode($row['PVCOMM']));

	} else {
		$maintainVar = "{$scriptVarBase}&amp;pricingLevel=" . urlencode($row['PVPMLV']) . "&amp;levelDesc=" . urlencode($row['PVLVDS']) . "&amp;fromScript=" . urlencode($scriptName);
		$confirmDesc = Format_Confirm_Desc($row['PVLVDS'], $row['PVPMLV'], "", "", "", "");
		$dtlCount    = RetValue("PMPMLV=$row[PVPMLV]", "HDPRCD", "count(*)");
		$pvcnImage = "&nbsp;";
		$pvllImage = "&nbsp;";
		$pvcpImage = "&nbsp;";
		$pvdlImage = "&nbsp;";
		$pvupImage = "&nbsp;";
		$pvbpImage = "&nbsp;";
		$pvbaImage = "&nbsp;";
		if ($row['PVCN'] == "Y") {$pvcnImage = $selectedImageSml; }
		if ($row['PVLL'] == "Y") {$pvllImage = $selectedImageSml; }
		if ($row['PVCP'] == "Y") {$pvcpImage = $selectedImageSml; }
		if ($row['PVDL'] == "Y") {$pvdlImage = $selectedImageSml; }
		if ($row['PVUP'] == "Y") {$pvupImage = $selectedImageSml; }
		if ($row['PVBP'] == "Y") {$pvbpImage = $selectedImageSml; }
		if ($row['PVBA'] == "Y") {$pvbaImage = $selectedImageSml; }
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		if ($formatToPrint != "Y" && ($hpoplm_OPT['sec_02'] == "Y" || $hpoplm_OPT['sec_03'] == "Y" || $hpoplm_OPT['sec_04'] == "Y")) {
			print "\n <td class=\"opticon\">";
			if ($hpoplm_OPT['sec_02'] == "Y" || $hpoplm_OPT['sec_03'] == "Y") {
				print "\n <a href=\"{$homeURL}{$phpPath}PricingLevelMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;fromLevel=" . urlencode($row['PVPMLV']) . "&amp;maintenanceCode=C\">$changeImageSml</a>";
			}
			if ($hpoplm_OPT['sec_01'] == "Y" && $hpoplm_OPT['sec_04'] == "Y") {
				print "\n <a href=\"{$homeURL}{$phpPath}PricingLevelMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;fromLevel=" . urlencode($row['PVPMLV']) . "&amp;maintenanceCode=Z\">$copyImageSml</a>";
			}
			if ($hpoplm_OPT['sec_03'] == "Y" && $dtlCount == '0') {
				print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}PricingLevelMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;fromLevel=" . urlencode($row['PVPMLV']) . "&amp;maintenanceCode=D\">$deleteImageSml</a>";
			}
			print "\n </td>";
		}
		$F_PVWHSQ=Format_Nbr($row['PVWHSQ'],  "0", "Z", "Y", "", "");
		$F_PVITSQ=Format_Nbr($row['PVITSQ'],  "0", "Z", "Y", "", "");
		$F_PVCSSQ=Format_Nbr($row['PVCSSQ'],  "0", "Z", "Y", "", "");
		$F_PVCCSQ=Format_Nbr($row['PVCCSQ'],  "0", "Z", "Y", "", "");
		$F_PVPCSQ=Format_Nbr($row['PVPCSQ'],  "0", "Z", "Y", "", "");
		$F_PVRGSQ=Format_Nbr($row['PVRGSQ'],  "0", "Z", "Y", "", "");
		$F_PVPGSQ=Format_Nbr($row['PVPGSQ'],  "0", "Z", "Y", "", "");
		print "\n <td class=\"colnmbr\">{$row['PVPMLV']}</td>";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}PricingDetail.php{$maintainVar}&amp;tag=LOAD\" title=\"View Pricing Detail\">{$row['PVLVDS']}</a></td>";
		print "\n <td class=\"colcode\">$F_PVWHSQ</td>";
		print "\n <td class=\"colcode\">$F_PVITSQ</td>";
		print "\n <td class=\"colcode\">$F_PVCSSQ</td>";
		print "\n <td class=\"colcode\">$F_PVCCSQ</td>";
		print "\n <td class=\"colcode\">$F_PVPCSQ</td>";
		print "\n <td class=\"colcode\">$F_PVRGSQ</td>";
		print "\n <td class=\"colcode\">$F_PVPGSQ</td>";
		if ($MUPMCD == "Y") {
			$F_PVCUSQ=Format_Nbr($row['PVCUSQ'],  "0", "Z", "Y", "", "");
			print "\n <td class=\"colcode\">$F_PVCUSQ</td>";
		}
		print "\n <td class=\"colcode\">$pvcnImage</td>";
		print "\n <td class=\"colcode\">$pvllImage</td>";
		print "\n <td class=\"colcode\">$pvcpImage</td>";
		print "\n <td class=\"colcode\">$pvdlImage</td>";
		print "\n <td class=\"colcode\">$pvupImage</td>";
		print "\n <td class=\"colcode\">$pvbpImage</td>";
		print "\n <td class=\"colcode\">$pvbaImage</td>";
		print "\n <td class=\"colcode\">{$row['PVCOMM']}</td>";
	}
	$startRow ++;
	$rowCount ++;
}

require_once 'XMLExport.php';

if ($rowCount == 0) {require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>
