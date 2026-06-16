<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';


$forPlant	=	(isset($_GET['forPlant']))		?	$_GET['forPlant']		:	0;
$docName	=	(isset($_GET['docName']))		?	$_GET['docName']		:	null;
$fldorder	=	(isset($_GET['fldorder']))		?	$_GET['fldorder']		:	"";
$fldseqn	=	(isset($_GET['fldseqn']))		?	$_GET['fldseqn']		:	0;
$moreInfo	=	(isset($_GET['moreInfo']))		?	$_GET['moreInfo']		:	"";
$morePlt	=	(isset($_GET['morePlt']))		?	$_GET['morePlt']		:	0;
$moreOrder	=	(isset($_GET['moreOrder']))		?	$_GET['moreOrder']		:	"";
$moreSeqn	=	(isset($_GET['moreSeqn']))		?	$_GET['moreSeqn']		:	0;
$moreDept	=	(isset($_GET['moreDept']))		?	$_GET['moreDept']		:	"";
$moreWc		=	(isset($_GET['moreWc']))		?	$_GET['moreWc']			:	"";


require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Labor In Process Search";
$scriptName     = "LaborInProcessSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;forPlant=" . urlencode(trim($forPlant)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldorder=" . urlencode(trim($fldorder)) . "&amp;fldseqn=" . urlencode(trim($fldseqn)) . "&amp;touchScreen=" . urlencode(trim($touchScreen)) . "&amp;dispatch=" . urlencode(trim($dispatch));
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
	Build_AdvSrch_Entry("Sequence","srchSeqn","","operSeqn","opersel_alph_short","N","3","3");
	Build_AdvSrch_Entry("Department","srchDept","","operDept","opersel_alph_short","A","5","5");
	Build_AdvSrch_Entry("Workcenter","srchWc","","operWc","opersel_alph_short","A","5","5");
	Build_AdvSrch_Entry("Item","srchItem","","operItem","opersel_alph_short","A","15","15");
	

	$focusField = "srchDept";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "plant")  {$orby = array(array("LPPLT","N","Plant"));}
	elseif ($sequence == "order")  {$orby = array(array("LPORD","A","Mfg Order"));}
	elseif ($sequence == "seqn")  {$orby = array(array("LPSEQN","N","Sequence"));}
	elseif ($sequence == "dept")  {$orby = array(array("LPDEPT","A","Department"));}
	elseif ($sequence == "wc")  {$orby = array(array("LPWC","A","Workcenter"));}
	elseif ($sequence == "item")  {$orby = array(array("OHPN","A","Item"),array("LPORD","A","Mfg Order"));}

	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("LPPLT", "Plant", $_POST['srchPlant'], "", $_POST['operPlant'], "N");
	$returnValue=Build_WildCard("LPORD", "MfgOrder", $_POST['srchOrder'], "U", $_POST['operOrder'], "A");
	$returnValue=Build_WildCard("LPSEQN", "Sequence", $_POST['srchSeqn'], "", $_POST['operSeqn'], "N");
	$returnValue=Build_WildCard("LPDEPT", "Department", $_POST['srchDept'], "U", $_POST['operDept'], "A");
	$returnValue=Build_WildCard("LPWC", "Workcenter", $_POST['srchWc'], "U", $_POST['operWc'], "A");
	$returnValue=Build_WildCard("OHPN", "Item", $_POST['srchItem'], "U", $_POST['operItem'], "A");
	
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
	print "\n function selectorder(order,seqn){ ";
	print "\n window.opener.document.$docName.$fldorder.value = order; ";
	print "\n window.opener.document.$docName.$fldseqn.value = seqn; ";
	
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
$stmtSQL .= " Select LPPLT, LPORD, LPSEQN, LPDEPT, LPWC, LPRTYP, OHCQTY - LPQTYC - LPRSCR as qtyremaining, OHPN ";
$fileSQL .= " HDMLPM inner join HDMOHM on LPPLT=OHPLT and LPORD=OHORD ";
if ($moreInfo == "Y") {$selectSQL="LPPLT = $morePlt and LPORD = '$moreOrder' and LPSEQN = '$moreSeqn'  ";;}
elseif ($forPlant!=0) {$selectSQL="LPPLT = $forPlant and OHSTC not in ('C','P') and LPREAS<>'D' and LPRTYP<>'V' and LPALTC='P' and LPRELC='R' and LPOPRC<>'Y'";}
elseif ($wildCardSearch!="" || $uv_sql!="") {$selectSQL="LPPLT<>0 and OHSTC not in ('C','P') and LPREAS<>'D' and LPRTYP<>'V' and LPALTC='P' and LPRELC='R' and LPOPRC<>'Y'";}
else  {$selectSQL="OHSTC not in ('C','P') and LPREAS<>'D' and LPRTYP<>'V' and LPALTC='P' and LPRELC='R' and LPOPRC<>'Y'";} 
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"LPPLT|null|Plant|N|\" title=\"Plant\">plant";
	$qsOpt  = "\n <option value=\"LPORD|null|Mfg Order|A|U\" title=\"Mfg Order\" SELECTED>order";
	$qsOpt .= "\n <option value=\"LPSEQN|null|Sequence|N|\" title=\"Sequence\">seqn";
	$qsOpt .= "\n <option value=\"LPDEPT|null|Department|A|U\" title=\"Department\">dept";
	$qsOpt .= "\n <option value=\"LPWC|null|Workcenter|A|U\" title=\"Workcenter\">wc";
	$qsOpt .= "\n <option value=\"OHPN|null|Item Number|A|U\" title=\"Item Number\">item";
	
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("LPPLT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=plant\" title=\"Sequence By Plant\">{$sortPoint}Plant</a></th>";
	$returnValue=OrderBy_Sort("LPORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=order\" title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th>";
	$returnValue=OrderBy_Sort("LPSEQN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=seqn\" title=\"Sequence By Sequence, Mfg Order\">{$sortPoint}Sequence</a></th>";
	$returnValue=OrderBy_Sort("LPDEPT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=dept\" title=\"Sequence By Department\">{$sortPoint}Department</a></th>";
	$returnValue=OrderBy_Sort("LPWC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=wc\" title=\"Sequence By Workcenter\">{$sortPoint}Workcenter</a></th>";
	print "\n <th class=\"colhdr\">Quantity Remaining</th>";
	$returnValue=OrderBy_Sort("OHPN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=item\" title=\"Sequence By Item, Mfg Order\">{$sortPoint}Item</a></th>";
	
	
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_qtyremaining = Format_Nbr($row['QTYREMAINING'], $qtyNbrDec, $qtyEditCode, "", "", "");
		$fldPltName=RetValue("PLPLNT={$row['LPPLT']}", "HDPLNT", "PLNAME");
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\">{$row['LPPLT']}</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectorder('" . trim($row['LPORD']) . "','" . trim($row['LPSEQN']) . "')\" title=\"Select Mfg Order / Sequence\">{$row['LPORD']}</a></td> ";
		print "\n     <td class=\"colalph\">{$row['LPSEQN']}</td>";
		print "\n     <td class=\"colalph\">{$row['LPDEPT']}</td>";
		print "\n     <td class=\"colalph\">{$row['LPWC']}</td>";
		print "\n     <td class=\"colnmbr\">$F_qtyremaining</td>";
		print "\n     <td class=\"colalph\">{$row['OHPN']}</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;morePlt=" . urlencode(trim($row['LPPLT'])) . "&amp;moreOrder=" . urlencode(trim($row['LPORD'])) . "&amp;moreSeqn=" . urlencode(trim($row['LPSEQN'])) . "&amp;moreDept=" . urlencode(trim($row['LPDEPT'])) . "&amp;moreWc=" . urlencode(trim($row['LPWC'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td>";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	//$F_Desc=Format_Quote($row['WCDESC']);
	
	$moreInfoSelect = "href=\"javascript:selectorder('" . trim($row[LPORD]) . "','" . trim($row[LPSEQN]) . "')\" title=\"Select Mfg Order/Sequence\">";
	
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	$fieldDesc=RetValue("WCPLT={$row['LPPLT']} and WCDEPT='{$row['LPDEPT']}' and WCWC='{$row['LPWC']}'", "HDMWCM ", "WCDESC");
	$fldPltName=RetValue("PLPLNT=$row[LPPLT]", "HDPLNT", "PLNAME");
	Build_DspFld("Plant",$row['LPPLT'],"$fldPltName","N");
	Build_DspFld("Mfg Order",$row['LPORD'],"","A");
	Build_DspFld("Sequence",$row[LPSEQN],"","A");
	print "\n <tr><td class=\"dsphdr\">Department/Work Center</td>";
	print "\n     <td class=\"dspalph\">$row[LPDEPT] / $row[LPWC]</td>";
	print "\n     <td class=\"dspalph\">$fieldDesc</td></tr>";
	Build_DspFld("Routing Type",$row['LPRTYP'],"","A");
	$descPN=RetValue("IMITEM='$row[OHPN]'", "HDIMST", "IMIMDS");
	Build_DspFld("Item",$row['OHPN'],"$descPN","A");
	
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
