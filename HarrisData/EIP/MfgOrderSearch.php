<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';


$forPlant	=	(isset($_GET['forPlant']))		?	$_GET['forPlant']		:	0;
$docName	=	(isset($_GET['docName']))		?	$_GET['docName']		:	null;
$fldorder	=	(isset($_GET['fldorder']))		?	$_GET['fldorder']		:	"";
$moreInfo	=	(isset($_GET['moreInfo']))		?	$_GET['moreInfo']		:	"";
$morePlt	=	(isset($_GET['morePlt']))		?	$_GET['morePlt']		:	0;
$moreOrder	=	(isset($_GET['moreOrder']))		?	$_GET['moreOrder']		:	"";


require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Manufacturing Order Search";
$scriptName     = "MfgOrderSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;forPlant=" . urlencode(trim($forPlant)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldorder=" . urlencode(trim($fldorder)) . "&amp;touchScreen=" . urlencode(trim($touchScreen)) . "&amp;dispatch=" . urlencode(trim($dispatch));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("OHORD","A","MfgOrder"),array("OHPN","A","Item"),array("OHRORD","A","Reference"));
$plant          = 0;


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
	require_once 'NoFormValidate.php';
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	if ($forPlant == 0) {
		Build_AdvSrch_Entry("Plant","srchPlant","","operPlant","opersel_num_short","N","3","3");
	}
	Build_AdvSrch_Entry("Mfg Order","srchOrder","","operOrder","opersel_alph_short","A","9","9");
	Build_AdvSrch_Entry("Item","srchItem","","operItem","opersel_alph_short","A","15","15");
	Build_AdvSrch_Entry("Reference","srchReference","","operReference","opersel_alph_short","A","20","20");

	$focusField = "srchDept";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "plant")  {$orby = array(array("OHPLT","N","Plant"));}
	elseif ($sequence == "order")  {$orby = array(array("OHORD","A","Mfg Order"));}
	elseif ($sequence == "item")  {$orby = array(array("OHPN","A","Item"),array("OHORD","A","Mfg Order"));}
	elseif ($sequence == "reference")  {$orby = array(array("OHRORD","A","Reference"),array("OHORD","A","Mfg Order"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("OHPLT", "Plant", $_POST['srchPlant'], "", $_POST['operPlant'], "N");
	$returnValue=Build_WildCard("OHORD", "MfgOrder", $_POST['srchOrder'], "U", $_POST['operOrder'], "A");
	$returnValue=Build_WildCard("OHPN", "Item", $_POST['srchItem'], "U", $_POST['operItem'], "A");
	$returnValue=Build_WildCard("OHRORD", "Reference", $_POST['srchReference'], "U", $_POST['operReference'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
	print "\n function selectorder(order){ ";
	print "\n window.opener.document.$docName.$fldorder.value = order; ";
	
	if ($touchScreen != 'Y') {print "\n    window.opener.document.$docName.$fldorder.focus;  ";}
	if ($dispatch == 'Y')    {print "\n    window.opener.document.$docName.submit(); ";}
	print "\n window.close(); ";
print "\n } ";

require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
if ($touchScreen == "Y") {require_once 'KeyboardFunctionsTS.js';}
function validate($searchForm) {
	if ($forPlant == 0)  {
		if (editNum(document.Search.srchPlant, 3, 0))  {
			return true;
		}  else   {
			return true;
		}
	}
}
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
if ($touchScreen == "Y") {$displayCloseIcon = "Y";}
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

$uv_PlantName = "OHPLT";
require_once 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select OHSTC, OHPLT, OHORD, OHPN, OHOTYP, OHCSDT, OHCDDT, OHCQTY, OHDWGR, OHRORD, OHPAPS, OHMGTP, OHBAC, coalesce(O2UNIT,'0') as O2UNIT, OHPRIC, OHORD# as OHORDN, OHORL# as OHORLN, OHBLN# as OHBLNN ";
$fileSQL .= " HDMOHM, Left Join HDMOHME on OHORD=O2ORD and OHPLT=O2PLT and OHSEQN=O2SEQN ";
if ($moreInfo == "Y") {$selectSQL="OHPLT = $morePlt and OHORD = '$moreOrder' ";}
elseif ($forPlant!=0) {$selectSQL="OHPLT = $forPlant and OHSTC <> 'C'";}
elseif ($wildCardSearch!="" || $uv_sql!="") {$selectSQL="OHPLT<>0 and OHSTC <> 'C'";}
else  {$selectSQL="OHSTC <> 'C'";} 
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"OHPLT|null|Plant|N|\" title=\"Plant\">plant";
	$qsOpt  = "\n <option value=\"OHORD|null|Mfg Order|A|U\" title=\"Mfg Order\" SELECTED>order";
	$qsOpt .= "\n <option value=\"OHPN|null|Item Number|A|U\" title=\"Item Number\">item";
	$qsOpt .= "\n <option value=\"OHRORD|null|Reference Number|A|U\" title=\"Reference Number\">reference";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("OHPLT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=plant\" title=\"Sequence By Plant\">{$sortPoint}Plant</a></th>";
	$returnValue=OrderBy_Sort("OHORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=order\" title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th>";
	$returnValue=OrderBy_Sort("OHPN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=item\" title=\"Sequence By Item, Mfg Order\">{$sortPoint}Item</a></th>";
	$returnValue=OrderBy_Sort("OHRORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=reference\" title=\"Sequence By Reference\">{$sortPoint}Reference</a></th>";
	print "\n <th class=\"colhdr\">Order Quantity</th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_OHCQTY = Format_Nbr($row[OHCQTY], $qtyNbrDec, $qtyEditCode, "", "", "");
		$fldPltName=RetValue("PLPLNT=$row[OHPLT]", "HDPLNT", "PLNAME");
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">$row[OHPLT]</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectorder('" . trim($row[OHORD]) . "')\" title=\"Select Mfg Order\">$row[OHORD]</a></td> ";
		print "\n     <td class=\"colalph\">$row[OHPN]</td>";
		print "\n     <td class=\"colalph\">$row[OHRORD]</td>";	
		print "\n     <td class=\"colnmbr\">$F_OHCQTY</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;morePlt=" . urlencode(trim($row['OHPLT'])) . "&amp;moreOrder=" . urlencode(trim($row['OHORD'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td>";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['WCDESC']);
	
	$moreInfoSelect = "href=\"javascript:selectorder('" . trim($row[OHORD]) . "')\" title=\"Select Mfg Order\">";
	
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("Mfg Order",$row['OHORD'],"","A");
	$fldPltName=RetValue("PLPLNT=$row[OHPLT]", "HDPLNT", "PLNAME");
	Build_DspFld("Plant",$row['OHPLT'],"$fldPltName","N");
	$descPN=RetValue("IMITEM='$row[OHPN]'", "HDIMST", "IMIMDS");
	Build_DspFld("Item",$row['OHPN'],"$descPN","A");
	Build_DspFld("Order Type",$row['OHOTYP'],"","A");
	$F_OHCDDT = Format_Date_ISO($row[OHCDDT],"H");
	Build_DspFld("Due Date",$F_OHCDDT,"","D");
	$F_OHCSDT = Format_Date_ISO($row[OHCSDT],"H");
	Build_DspFld("Start Date",$F_OHCSDT,"","D");
	$F_OHCQTY = Format_Nbr($row[OHCQTY], $qtyNbrDec, $qtyEditCode, "", "", "");
	Build_DspFld("Order Quantity",$F_OHCQTY,"","N");
	$F_OHPRIC = Format_Nbr($row[OHPRIC], $prcNbrDec, $amtEditCode, "", "", "");
	Build_DspFld("Unit Price",$F_OHPRIC,"","N");
	$F_O2UNIT = Format_Nbr($row[O2UNIT], $cstNbrDec, $amtEditCode, "", "", "");
	Build_DspFld("Unit Cost",$F_O2UNIT,"","A");
	Build_DspFld("Reference",$row['OHRORD'],"","A");
	Build_DspFld("Drawing Revision Number",$row['OHDWGR'],"","N");
	$F_OHMGPT = Format_Nbr($row[OHMGPT], 1, 1, "", "", "");
	Build_DspFld("Management Priority",$F_OHMGPT,"","N");
	$F_OHBAC = Format_Nbr($row[OHBAC], 0, "", "", "", "");
	Build_DspFld("Buyer Analyst",$F_OHBAC,"","A");
	Build_DspFld("Paper Switch",$row['OHPAPS'],"","A");
	$F_OHORDN = Format_Nbr($row[OHORDN], 0, "", "", "", "");
	Build_DspFld("Sales Order Number",$F_OHORDN,"","N");
	$F_OHORLN = Format_Nbr($row[OHORLN], 0, "", "", "", "");
	Build_DspFld("Sales Line Number",$F_OHORLN,"","N");
	$F_OHBLNN = Format_Nbr($row[OHBLNN], 0, "", "", "", "");
	Build_DspFld("Sales Release Number",$F_OHBLNN,"","N");
	

	print "\n </table> ";
	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardPrint.php';
}

print "$searchhrTagAttr";
require_once 'Copyright.php';
if ($touchScreen == "Y") {require_once 'KeyboardTS.htm';}
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
