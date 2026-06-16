<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$fldName  = $_GET['fldName'];
$fldDesc  = $_GET['fldDesc'];
$moreInfo = $_GET['moreInfo'];
$moreItem = $_GET['moreItem'];

require_once 'SetLibraryList.php';
require_once "InventoryControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Item Search";
$scriptName     = "ItemSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("IMIMDSU","A","Desc"),array("IMITEM","A","Number"));

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
	print "\n if (editNum(document.Search.srchUpc, 14, 0) ";

	if (trim($CIINS1)!="")  {print "\n  && editNum(document.Search.srchNumeric1, 8, 5) ";}
	if (trim($CIINS2)!="")  {print "\n  && editNum(document.Search.srchNumeric2, 8, 5) ";}
	if (trim($CIINS3)!="")  {print "\n  && editNum(document.Search.srchNumeric3, 8, 5) ";}
	if (trim($CIINS4)!="")  {print "\n  && editNum(document.Search.srchNumeric4, 8, 5) ";}
	if (trim($CIINS5)!="")  {print "\n  && editNum(document.Search.srchNumeric5, 8, 5) ";}

	print "\n     )";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Item Number","srchNumber","","operNumber","opersel_alph_short","A","14","14");
	Build_AdvSrch_Entry("Item Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");
	Build_AdvSrch_Entry("Product Class","srchProdClass","","operProdClass","opersel_alph_short","A","4","4");
	Build_AdvSrch_Entry("Kit Item / Featured Item / Cfg","srchKfc","","operKfc","opersel_alph_short","A","1","1");
	if ($CILTUS == "Y") {Build_AdvSrch_Entry("Lot Controlled","srchLot","","operLot","opersel_alph_short","A","1","1");}
	if ($HDMPRL>0) {Build_AdvSrch_Entry("Part Type","srchPartType","","operPartType","opersel_alph_short","A","1","1");}
	Build_AdvSrch_Entry("Catalog Number","srchCatalog","","operCatalog","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("U.P.C. Number","srchUpc","","operUpc","opersel_num_short","N","14","14");
	if (trim($CIIAS1)!="") {Build_AdvSrch_Entry("$CIIAS1","alpha01","","operAlpha1","opersel_alph_short","A","15","15");}
	if (trim($CIIAS2)!="") {Build_AdvSrch_Entry("$CIIAS2","alpha02","","operAlpha2","opersel_alph_short","A","15","15");}
	if (trim($CIIAS3)!="") {Build_AdvSrch_Entry("$CIIAS3","alpha03","","operAlpha3","opersel_alph_short","A","15","15");}
	if (trim($CIIAS4)!="") {Build_AdvSrch_Entry("$CIIAS4","alpha04","","operAlpha4","opersel_alph_short","A","15","15");}
	if (trim($CIIAS5)!="") {Build_AdvSrch_Entry("$CIIAS5","alpha05","","operAlpha5","opersel_alph_short","A","15","15");}
	if (trim($CIINS1)!="") {Build_AdvSrch_Entry("$CIINS1","srchNumeric1","","operNumeric1","opersel_num_short","N","15","15");}
	if (trim($CIINS2)!="") {Build_AdvSrch_Entry("$CIINS2","srchNumeric2","","operNumeric2","opersel_num_short","N","15","15");}
	if (trim($CIINS3)!="") {Build_AdvSrch_Entry("$CIINS3","srchNumeric3","","operNumeric3","opersel_num_short","N","15","15");}
	if (trim($CIINS4)!="") {Build_AdvSrch_Entry("$CIINS4","srchNumeric4","","operNumeric4","opersel_num_short","N","15","15");}
	if (trim($CIINS5)!="") {Build_AdvSrch_Entry("$CIINS5","srchNumeric5","","operNumeric5","opersel_num_short","N","15","15");}

	$focusField = "srchNumber";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Number")    {$orby = array(array("IMITEM","A","Number"));}
	elseif ($sequence == "Desc")      {$orby = array(array("IMIMDSU","A","Desc"),array("IMITEM","A","Number"));}
	elseif ($sequence == "ProdClass") {$orby = array(array("IMPCLS","A","Product Class"),array("IMITEM","A","Number"));}
	elseif ($sequence == "Kfc")       {$orby = array(array("IMKIT","A","Kfc"),array("IMITEM","A","Number"));}
	elseif ($sequence == "Lot")       {$orby = array(array("IMLOT","A","Name"),array("IMITEM","A","Number"));}
	elseif ($sequence == "PartType")  {$orby = array(array("IMPTYP","A","Name"),array("IMITEM","A","Number"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("IMITEM", "Number", $_POST['srchNumber'], "U", $_POST['operNumber'], "A");
	$returnValue=Build_WildCard("(IMIMDSU)", "Desc", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("IMPCLS", "Product Class", $_POST['srchProdClass'], "U", $_POST['operProdClass'], "A");
	$returnValue=Build_WildCard("IMKIT", "Kfc", $_POST['srchKfc'], "U", $_POST['operKfc'], "A");
	$returnValue=Build_WildCard("IMLOT", "Lot", $_POST['srchLot'], "U", $_POST['operLot'], "A");
	$returnValue=Build_WildCard("IMPTYP", "Part Type", $_POST['srchPartType'], "U", $_POST['operPartType'], "A");
	$returnValue=Build_WildCard("IMCATN", "Catalog Number", $_POST['srchCatalog'], "U", $_POST['operCatalog'], "A");
	$returnValue=Build_WildCard("IMUPC", "U.P.C. Number", $_POST['srchUpc'], "", $_POST['operUpc'], "N");
	$returnValue=Build_WildCard("IMUDA1", "$CIIAS1", $_POST['alpha01'], "U", $_POST['operAlpha1'], "A");
	$returnValue=Build_WildCard("IMUDA2", "$CIIAS2", $_POST['alpha02'], "U", $_POST['operAlpha2'], "A");
	$returnValue=Build_WildCard("IMUDA3", "$CIIAS3", $_POST['alpha03'], "U", $_POST['operAlpha3'], "A");
	$returnValue=Build_WildCard("IMUDA4", "$CIIAS4", $_POST['alpha04'], "U", $_POST['operAlpha4'], "A");
	$returnValue=Build_WildCard("IMUDA5", "$CIIAS5", $_POST['alpha05'], "U", $_POST['operAlpha5'], "A");
	$returnValue=Build_WildCard("IMUDN1", "$CIINS1", $_POST['srchNumeric1'], "", $_POST['operNumeric1'], "N");
	$returnValue=Build_WildCard("IMUDN2", "$CIINS2", $_POST['srchNumeric2'], "", $_POST['operNumeric2'], "N");
	$returnValue=Build_WildCard("IMUDN3", "$CIINS3", $_POST['srchNumeric3'], "", $_POST['operNumeric3'], "N");
	$returnValue=Build_WildCard("IMUDN4", "$CIINS4", $_POST['srchNumeric4'], "", $_POST['operNumeric4'], "N");
	$returnValue=Build_WildCard("IMUDN5", "$CIINS5", $_POST['srchNumeric5'], "", $_POST['operNumeric5'], "N");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectItem(number,desc){ ";
print "\n window.opener.document.$docName.$fldName.value = number; ";
print "\n if (window.opener.document.$docName.$fldDesc) ";
print "\n    {window.opener.document.$docName.$fldDesc.value = desc;} ";
print "\n else if (window.opener.document.getElementById('$fldDesc'))";
print "\n         {window.opener.document.getElementById('$fldDesc').innerHTML = desc;}";
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

$uv_ProductClassName ="IMPCLS";
$uv_ProductInventoryTypeName ="IMITC";
$uv_ProductPartTypeName ="IMPTYP";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select IMITEM, IMPCLS, IMKIT, IMLOT, IMPTYP, IMALPH, IMUOMS, IMHAZM,";
$stmtSQL .= " IMDSHP, IMUPC, IMUPCT, IMCATN, IMITC, IMIMWG, IMUOMW, IMCSZS, IMCUUM, IMQUAL,";
$stmtSQL .= " IMUDA1, IMUDA2, IMUDA3, IMUDA4, IMUDA5,IMUDN1, IMUDN2, IMUDN3, IMUDN4, IMUDN5,";
if ($CIUSMP == "Y") {
	$stmtSQL .= " coalesce(SMPART,IMIMDS) as IMIMDS, coalesce(SMPART,IMIMDSU) as IMIMDSU ";
} else {
	$stmtSQL .= " IMIMDS, IMIMDSU ";
}
$fileSQL .= " HDIMST ";
if ($CIUSMP == "Y") {$fileSQL .= " left join HDSPRT on IMITEM=SMCITM ";}

if ($moreInfo=="Y")         {$selectSQL .= " IMITEM='$moreItem' ";}
elseif ($wildCardSearch!="") {$selectSQL="IMITEM<>' ' ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"IMITEM|null|Item Number|A|U\" title=\"Item Number\">Item Number";
	$qsOpt .= "\n <option value=\"IMIMDSU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"IMPCLS|null|Product Class|A|U\" title=\"Product Class\">Product Class";
	$qsOpt .= "\n <option value=\"IMKIT|null|KFC|A|U\" title=\"KFC\">KFC";
	if ($CILTUS == "Y") {$qsOpt .= "\n <option value=\"IMLOT|null|Lot|A|U\" title=\"Lot\">Lot";}
	if ($HDMPRL>0)      {$qsOpt .= "\n <option value=\"IMPTYP|null|Part Type|A|U\" title=\"Part Type\">Part Type";}
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("IMITEM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number\">{$sortPoint}Item Number</a></th>";
	$returnValue=OrderBy_Sort("IMIMDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\" title=\"Sequence By Description, Number\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("IMPCLS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ProdClass\" title=\"Sequence By Product Class, Number\">{$sortPoint}Product<br>Class</a></th>";
	$returnValue=OrderBy_Sort("IMKIT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Kfc\" title=\"Sequence By KFC, Number\">{$sortPoint}KFC</a></th>";
	if ($CILTUS == "Y") {
		$returnValue=OrderBy_Sort("IMLOT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Lot\" title=\"Sequence By Lot, Number\">{$sortPoint}Lot</a></th>";
	}
	if ($HDMPRL>0) {
		$returnValue=OrderBy_Sort("IMPTYP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PartType\" title=\"Sequence By Part Type, Number\">{$sortPoint}Part<br>Type</a></th>";
	}
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Desc=Format_Quote($row['IMIMDS']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\">$row[IMITEM]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectItem('" . trim($row['IMITEM']) . "','" . trim($F_Desc) . "')\" title=\"Select Desc\">$F_Desc</a></td> ";
		print "\n     <td class=\"colalph\">$row[IMPCLS]</td>";
		print "\n     <td class=\"colalph\">$row[IMKIT]</td>";
		if ($CILTUS == "Y") {print "\n <td class=\"colalph\">$row[IMLOT]</td>";}
		if ($HDMPRL>0)      {print "\n <td class=\"colalph\">$row[IMPTYP]</td>";}
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreItem=" . urlencode(trim($row['IMITEM'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a>";
		$itemImage="{$homePath}Images/Item/" . trim($row[IMITEM]) . $itemImageExt;
		if (file_exists($itemImage)) {
			print "\n <a href=\"{$homeURL}{$cGIPath}ImageDisplay.d2w/DISPLAY{$altVarBase}&amp;imageDisplayPath=" . urlencode(trim($homeURL)) . urlencode(trim($itemImage)) . "&amp;imageDesc=" . urlencode(trim($row['IMIMDS'])) . "\" onclick=\"$itemImageWinVar\">$foundImage</a>";
		}
		$commentsExist=Check_Item_Comments ($profileHandle,$dataBaseID,trim($row['IMITEM']));
		if ($commentsExist == "Y") {
			print "\n <a href=\"{$homeURL}{$cGIPath}ItemComments.d2w/REPORT{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['IMITEM'])) . "&amp;itemDesc=" . urlencode(trim($row['IMIMDS'])) . "\" onclick=\"$commentWinVar\">$commentExistImage</a>";
		}
		print "\n </td>";
		print "\n </tr>";
		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);

	$F_Desc=Format_Quote($row['IMIMDS']);
	$moreInfoSelect = "href=\"javascript:selectItem('" . trim($row['IMITEM']) . "','" . trim($F_Desc) . "')\" title=\"Select Number\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	Build_DspFld("Item Number",$row[IMITEM],"","A");
	Build_DspFld("Item Description",$row[IMIMDS],"","A");
	$fieldDesc=RetValue("PCPCLS='$row[IMPCLS]'", "HDPCLS", "PCPCDS");
	Build_DspFld("Product Class",$fieldDesc,"","A");
	$fieldDesc=RetValue("UMUOM='$row[IMUOMS]'", "HDUOM", "UMUMLD");
	Build_DspFld("Stocking Unit Of Measure",$fieldDesc,"","A");
	if (trim($row[IMKIT]) != "") {
		$fieldDesc=RetValue("FLTYPE='KIT' and FLVALU='$row[IMKIT]'", "SYFLAG", "FLDESC");
		Build_DspFld("Kit Item / Featured Item / Cfg",$fieldDesc,"","A");
	}
	$fieldDesc=RtvYNDesc($row[IMHAZM]);
	Build_DspFld("Hazardous Material",$fieldDesc,"","A");
	$fieldDesc=RtvYNDesc($row[IMDSHP]);
	Build_DspFld("Drop Ship Item",$fieldDesc,"","A");
	if ($CILTUS == "Y") {
		$fieldDesc=RetValue("FLTYPE='LOT' and FLVALU='$row[IMLOT]'", "SYFLAG", "FLDESC");
		Build_DspFld("Lot Controlled",$fieldDesc,"","A");
	}
	if ($row[IMUPC]>0) {
		$fieldDesc=RetValue("FLTYPE='UPCBAR' and FLVALU='$row[IMUPCT]'", "SYFLAG", "FLDESC");
		Build_DspFld("U.P.C. Bar Code Type",$fieldDesc,"","A");
		Build_DspFld("U.P.C Number",$row[IMUPC],"","N");
	}
	if (trim($row[IMCATN]) != "") {Build_DspFld("Catalog Number",$row[IMCATN],"","A");}
	if (trim($row[IMITC]) != "") {
		$fieldDesc=RetValue("ITITC='$row[IMITC]'", "HDITYP", "ITDESC");
		Build_DspFld("Inventory Type",$fieldDesc,"","A");
	}
	if ($row[IMIMWG] != .000) {Build_DspFld("Weight",$row[IMIMWG],"","N");}
	if (trim($row[IMUOMW]) != "") {
		$fieldDesc=RetValue("UMUOM='$row[IMUOMW]'", "HDUOM", "UMUMLD");
		Build_DspFld("Weight Unit Of Measure",$fieldDesc,"","A");
	}
	if ($row[IMCSZS] != .000) {Build_DspFld("Cubic Size",$row[IMCSZS],"","N");}
	if (trim($row[IMCUUM]) != "") {
		$fieldDesc=RetValue("UMUOM='$row[IMCUUM]'", "HDUOM", "UMUMLD");
		Build_DspFld("Cubic Unit Of Measure",$fieldDesc,"","A");
	}
	if (trim($row[IMQUAL]) != "") {
		$fieldDesc=RetValue("FLTYPE='QUALCONTRL' and FLVALU='$row[IMQUAL]'", "SYFLAG", "FLDESC");
		Build_DspFld("Quality Control Item",$fieldDesc,"","A");
	}
	if ($HDMPRL>0) {
		$fieldDesc=RetValue("FLTYPE='PARTTYPE' and FLVALU='$row[IMPTYP]'", "SYFLAG", "FLDESC");
		Build_DspFld("Part Type",$fieldDesc,"","A");
	}
	if (trim($CIIAS1) != "" && trim($row[IMUDA1]) != "") {Build_DspFld("$CIIAS1",$row[IMUDA1],"","A");}
	if (trim($CIIAS2) != "" && trim($row[IMUDA2]) != "") {Build_DspFld("$CIIAS2",$row[IMUDA2],"","A");}
	if (trim($CIIAS3) != "" && trim($row[IMUDA3]) != "") {Build_DspFld("$CIIAS3",$row[IMUDA3],"","A");}
	if (trim($CIIAS4) != "" && trim($row[IMUDA4]) != "") {Build_DspFld("$CIIAS4",$row[IMUDA4],"","A");}
	if (trim($CIIAS5) != "" && trim($row[IMUDA5]) != "") {Build_DspFld("$CIIAS5",$row[IMUDA5],"","A");}
	if (trim($CIINS1) != "" && $row[IMUDN1] != .00000)   {Build_DspFld("$CIINS1",$row[IMUDN1],"","N");}
	if (trim($CIINS2) != "" && $row[IMUDN2] != .00000)   {Build_DspFld("$CIINS2",$row[IMUDN2],"","N");}
	if (trim($CIINS3) != "" && $row[IMUDN3] != .00000)   {Build_DspFld("$CIINS3",$row[IMUDN3],"","N");}
	if (trim($CIINS4) != "" && $row[IMUDN4] != .00000)   {Build_DspFld("$CIINS4",$row[IMUDN4],"","N");}
	if (trim($CIINS5) != "" && $row[IMUDN5] != .00000)   {Build_DspFld("$CIINS5",$row[IMUDN5],"","N");}

	print "\n </table> ";
	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardprint.php';
}

print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
