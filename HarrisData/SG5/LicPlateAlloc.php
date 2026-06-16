<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$orderControlNumber = $_GET['orderControlNumber'];
$orderNumber        = $_GET['orderNumber'];
$lineNumber         = $_GET['lineNumber'];
$relNumber          = $_GET['relNumber'];
$itemNumber         = $_GET['itemNumber'];
$itemDesc           = $_GET['itemDesc'];
$whsNumber          = $_GET['whsNumber'];
$stkLoc             = $_GET['stkLoc'];
$lotItem            = $_GET['lotItem'];
$qtyOrdered         = $_GET['qtyOrdered'];
$firstTime	        = (isset($_GET['firstTime'])) ? $_GET['firstTime'] : "";

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "OEControl$dataBaseID.php";
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "License Plate Allocation";
$scriptName    = "LicPlateAlloc.php";
$scriptVarBase = "{$genericVarBase}&amp;orderControlNumber=" . urlencode(trim($orderControlNumber)) . "&amp;orderNumber=" . urlencode(trim($orderNumber)) . "&amp;lineNumber=" . urlencode(trim($lineNumber)) . "&amp;relNumber=" . urlencode(trim($relNumber)) . "&amp;itemNumber=" . urlencode(trim($itemNumber)) . "&amp;itemDesc=" . urlencode(trim($itemDesc)) . "&amp;compItem=" . urlencode(trim($compItem)) . "&amp;optionItem=" . urlencode(trim($optionItem)) . "&amp;cmpOptDesc=" . urlencode(trim($cmpOptDesc)) . "&amp;whsNumber=" . urlencode(trim($whsNumber)) . "&amp;stkLoc=" . urlencode(trim($stkLoc)) . "&amp;lotItem=" . urlencode(trim($lotItem)) . "&amp;qtyOrdered=" . urlencode(trim($qtyOrdered));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
if ($stkLoc == "Y") {$dftOrderBy    = array(array("ISRATE","A","Priority"),array("ISSTKR","A","Stockroom"),array("ISAILE","A","Aisle"),array("ISSLOC","A","Location"));}
else                {$dftOrderBy    = array(array("ISLOT","A","Lot Number"));}
$popUpWin      = "Y";
$allowSaveFilter    = "N";
$pageSelectList = "N";
		
// New Item/Whs then reset search criteria and order by.
if ($firstTime == "Y") {$chgSrch = "D";}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "LicPlate")      {$orby = array(array("LHID" ,"A","License Plate"));}
	elseif ($sequence == "StockLoc")      {$orby = array(array("ISSTKR" ,"A","Stock Location"),array("ISAILE" ,"A",""),array("ISSLOC" ,"A",""));}
	elseif ($sequence == "LotNumber" && $stkLoc == "Y")   {$orby = array(array("ISLOT" ,"A","Lot Number"),array("ISSTKR" ,"A","Stock Location"),array("ISAILE" ,"A",""),array("ISSLOC" ,"A",""));}
	elseif ($sequence == "LotNumber")     {$orby = array(array("ISLOT" ,"A","Lot Number"));}
	elseif ($sequence == "Priority")      {$orby = array(array("ISRATE" ,"A","Priority"),array("ISSTKR" ,"A","Stock Location"),array("ISAILE" ,"A",""),array("ISSLOC" ,"A",""));}
	elseif ($sequence == "QtyAvail" && $stkLoc == "Y")    {$orby = array(array("ISAVAL" ,"A","Quantity Available"),array("ISSTKR" ,"A","Stock Location"),array("ISAILE" ,"A",""),array("ISSLOC" ,"A",""));}
	elseif ($sequence == "QtyAvail")      {$orby = array(array("ISAVAL" ,"A","Quantity Available"),array("ISLOT" ,"A","Lot Number"));}
	elseif ($sequence == "QtyAlloc" && $stkLoc == "Y")   {$orby = array(array("LWWQTY" ,"D","Quantity Allocated"),array("ISSTKR" ,"A","Stock Location"),array("ISAILE" ,"A",""),array("ISSLOC" ,"A",""));}
	elseif ($sequence == "QtyAlloc")     {$orby = array(array("LWWQTY" ,"D","Quantity Allocated"),array("ISLOT" ,"A","Lot Number"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

$uv_StockroomName ="ISSTKR";
$uv_AileName ="ISAILE";
$uv_StockLocationName ="ISSLOC";
require 'UserView.php';
require 'stmtSQLClear.php';
$stmtSQL .= " Select LHID, coalesce(ISSID#,LHSLID) as ISSID#, coalesce(ISSTKR,'') as ISSTKR, coalesce(ISAILE,'') as ISAILE, coalesce(ISSLOC,'') as ISSLOC"; 
$stmtSQL .= "             ,coalesce(ISLOT,LDLOT) as ISLOT, coalesce(ISRATE,0) as ISRATE, LDQTY - coalesce(WQTY, 0) - coalesce(AQTY, 0) as ISAVAL";
$stmtSQL .= "             ,coalesce(LWWQTY,0) as LWWQTY ";
$stmtSQL .= "             ,Case When coalesce(LWWQTY,0)<>0 Then 'CHECKED' ELSE ' ' End as CHECKPOSTED ";

$dspLot = "";
if ($lotItem == "A" || $lotItem == "M") {$dspLot = "Y";}
$fileSQL .= "IVLPHD inner join IVLPDT on LHID=LDID"; 
$fileSQL .= "             left join HDITSL on LHSLID=ISSID# and LDITEM=ISITEM and LHWHS=ISWHS and LDLOT=ISLOT"; 
$fileSQL .= "             left join OELPWK on LWOCTL=$orderControlNumber and LWLINE=$lineNumber and LWBLN=$relNumber and LWITEM='$itemNumber' and LWLPID=LHID and LWSID=coalesce(ISSID#,LHSLID) and LWLOTN=LDLOT";
$fileSQL .= "             left join table (Select LWLPID as LPID,LWITEM as ITEM,LWSID as SID,LWLOTN as LOTN,sum(LWWQTY) as WQTY from OELPWK GROUP BY LWLPID,LWITEM,LWSID,LWLOTN 
                                          ) as LPWK on (LPID,ITEM,SID,LOTN)=(LHID,LDITEM,coalesce(ISSID#,LHSLID),coalesce(ISLOT,LDLOT))";

$fileSQL .= "             left join table (Select LAID,LAITEM,LAWHS,LASID,LALOTN,sum(LAQTY) as AQTY from IVLPAL ";
$fileSQL .= "                              Where LAORD<>$orderNumber and not exists (Select * from OEHDWK Where H1ORD#=LAORD)";
$fileSQL .= "                              GROUP BY LAID,LAITEM,LAWHS,LASID,LALOTN";
$fileSQL .= "                             ) as LPAL on (LAID,LAITEM,LAWHS,LASID,LALOTN)=(LHID,'$itemNumber',$whsNumber,coalesce(ISSID#,LHSLID),coalesce(ISLOT,LDLOT))";
$selectSQL .= " (LDITEM,LHWHS)=('$itemNumber',$whsNumber) and (coalesce(LWWQTY,0)<>0 or (LDQTY - coalesce(WQTY, 0) - coalesce(AQTY, 0)) > 0) ";
if ($dspLot == "Y" && $stkLoc == "Y") {$selectSQL .= " and LDLOT<>' ' ";}

require 'stmtSQLSelect.php';
$sql_Record_Count=RetValue($selectSQL, $fileSQL, "count(*)");
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
//print $stmtSQL;
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n var optionWin;";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';
require_once 'Format_Nbr.js';
require_once 'Menu.js';
require_once 'CheckEnterAjax.php';
require_once 'CheckEnterSearch.php';
require_once 'CalendarInclude.php';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
require_once 'StringTrimJavaScript.php';
require_once 'LicPlateAllocJava.php';
print "\n function qtyWarning() {return confirm(\"Quantity Available is less than Quantity Posted\");} ";
print "\n function validate(chgForm) {return true;}";

print "\n function acceptStkLot() {";
print "\n opener.location.href=opener.location.href;";
print "\n window.close();";
print "\n }";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

if ($formatToPrint != "Y"){
	print "\n <table $contentTable>";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>$page_title</h1></td>";
	print "\n     <td class=\"toolbar\">";
	print "\n <a href=\"javascript:acceptStkLot();\">$acceptImageMed</a>";

	$medIcon= "Y";
	require 'HelpPage.php';
	print "\n </td></tr></table>";
	print "$hrTagAttr";

	print "\n <table $contentTable id=\"paymentTable\"> <tr>";
	print "\n <th class=\"colhdr\">Order<br>Number</th>";
	print "\n <th class=\"colhdr\">Ln</th>";
	if ($relNumber>0) {print "\n <th class=\"colhdr\">Rel</th>";}
	print "\n <th class=\"colhdr\">Item<br>Number</th>";
	print "\n <th class=\"colhdr\">Whs</th>";
	print "\n <th class=\"colhdr\">Description</th>";
	print "\n <th class=\"colhdr\">Quantity<br>Ordered</th>";
	print "\n <th class=\"colhdr\">Quantity<br>Allocated</th>";
	print "\n <th class=\"colhdr\">Quantity<br>Variance</th>";
	$overShipPct = 0;
	if ($CEAOVS == 'Y') {
		$overShipPct=RetValue("H1OCTL=$orderControlNumber and CMAOVS='Y'", "OEHDWK inner join HDCUST on H1SHTO=CMCUST", "coalesce(CMOVSH*100,0)");
		$F_overShipPct=Format_Nbr($overShipPct,$pctNbrDec, $pctEditCode, "Y", "", "");
		$maxQty = $qtyOrdered + ($qtyOrdered * ($overShipPct/100));
		$F_maxQty=Format_Nbr($maxQty,$qtyNbrDec, $qtyEditCode, "Y", "", "");
	}
	if ($overShipPct > 0) {
		print "\n <th class=\"colhdr\">Over Shipment<br>Percent</th>";
		print "\n <th class=\"colhdr\">Maximum<br>Quantity</th>";
		print "\n <td id=\"maxDesc\" title=\"Maximum Quantity\"></td> ";
	} else {
		print "\n <td id=\"maxDesc\" title=\"Quantity Ordered\"></td> ";
	}
	print "\n </tr>";

	print "\n <tr class=\"evenrow\"> ";
	if ($orderNumber>0) {$F_orderNumber = $orderNumber;} else {$F_orderNumber = "New Order";}
	print "\n <td class=\"colnmbr\">$F_orderNumber</td> ";
	print "\n <td class=\"colnmbr\">$lineNumber</td> ";
	if ($relNumber>0) {print "\n <th class=\"colnmbr\">$relNumber</th>";}
	print "\n <td class=\"colalph\">$itemNumber</td> ";
	print "\n <td class=\"colnmbr\">$whsNumber</td> ";
	print "\n <td class=\"colalph\">$itemDesc</td> ";
	$F_qtyOrdered=Format_Nbr($qtyOrdered,$qtyNbrDec, $qtyEditCode, "Y", "", "");
	print "\n <td class=\"colnmbr\">$F_qtyOrdered</td> ";
	$qtyPosted=RetValue("LWOCTL=$orderControlNumber and LWLINE=$lineNumber and LWBLN=$relNumber and LWITEM='$itemNumber'", "OELPWK", "coalesce(sum(LWWQTY),0)");
	$F_qtyPosted=Format_Nbr($qtyPosted,$qtyNbrDec, $qtyEditCode, "Y", "", "");
	print "\n <td class=\"colnmbr\" id=\"LWWQTYsum\" title=\"$qtyPosted\">$F_qtyPosted</td> ";
	$qtyVar=$qtyOrdered-$qtyPosted;
	$F_qtyVar=Format_Nbr($qtyVar,$qtyNbrDec, $qtyEditCode, "Y", "", "");
	print "\n <td class=\"oepriceover\" id=\"LWWQTYvar\" title=\"$qtyVar\">$F_qtyVar</td> ";
	if ($overShipPct > 0) {
		print "\n <td class=\"colnmbr\">$F_overShipPct</td> ";
		print "\n <td class=\"colnmbr\">$F_maxQty</td> ";
		print "\n <td id=\"maxQty\" title=\"$maxQty\"></td> ";
	} else {
		print "\n <td id=\"maxQty\" title=\"$qtyOrdered\"></td> ";
	}
	print "\n </tr>";
	print "\n </table>";

	// Define Quick Search columns
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"LHID|null|License Plate|A|U\" title=\"License Plate\" SELECTED>License Plate";
	if ($stkLoc == "Y") {
		$qsOpt .= "\n <option value=\"ISSTKR|null|Stockroom|A|U\" title=\"Stockroom\" SELECTED>Stockroom";
		$qsOpt .= "\n <option value=\"ISAILE|null|Aisle|A|U\" title=\"Aisle\">Aisle";
		$qsOpt .= "\n <option value=\"ISSLOC|null|Location|A|U\" title=\"Location\">Location";
	}
	if ($dspLot == "Y") {
		$qsOpt .= "\n <option value=\"ISLOT|null|Lot Number|A|U\" title=\"Lot Number\" SELECTED>Lot Number";
	}
	if ($stkLoc == "Y") {$qsOpt .= "\n <option value=\"ISRATE|null|Priority|N|\" title=\"Priority\">Priority";}
	require 'QuickSearchOption.php';

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\">";
	print "\n <table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("LHID"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LicPlate\"  title=\"Sequence By License Plate\">{$sortPoint}License<br>Plate</a></th>";
	if ($stkLoc == "Y") {
		$returnValue=OrderBy_Sort("ISSTKR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\" colspan=\"3\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StockLoc\"  title=\"Sequence By Stock Location\">{$sortPoint}Stock<br>Location</a></th>";
	}
	if ($dspLot == "Y") {
		$returnValue=OrderBy_Sort("ISLOT");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LotNumber\" title=\"Sequence By Lot Number\">{$sortPoint}Lot<br>Number</a></th>";
	}
	if ($stkLoc == "Y") {
		$returnValue=OrderBy_Sort("ISRATE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Priority\"                title=\"Sequence By Priority\">{$sortPoint}Priority</a></th>";
	}
	$returnValue=OrderBy_Sort("ISAVAL"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyAvail\"                title=\"Sequence By Quantity Available\">{$sortPoint}Quantity<br>Available</a></th>";
	$returnValue=OrderBy_Sort("LWWQTY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyAlloc\"               title=\"Sequence By Quantity Posted\">{$sortPoint}Quantity<br>Allocated</a></th>";
	print "\n </tr>";

	// Add hidden field needed for Active Responses
	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colalph\"></td> ";
	print "\n     <td class=\"colalph\" colspan=\"50\"><span id=\"quickEntryMessage\"></span></td> ";
	print "\n </tr>";

	// Stock/Lot rows
	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		$trimLHID=trim($row['LHID']);
		$trimLot=trim($row['ISLOT']);
		require  'SetRowClass.php';

		print "\n <tr class=\"$rowClass\" id=\"row{$row['ISSID']}_{$trimLot}\"> ";
		print "\n <td class=\"colalph\">$trimLHID</td> ";
		
		// Select Checkbox
		if ($stkLoc == "Y") {
			print "\n <td class=\"colalph\">$row[ISSTKR]</td> ";
			print "\n <td class=\"colalph\">$row[ISAILE]</td> ";
			print "\n <td class=\"colalph\">$row[ISSLOC]</td> ";
		}
		if ($dspLot == "Y") {
			print "\n <td class=\"colalph\">$row[ISLOT]</td> ";
		}
		if ($stkLoc == "Y") {print "\n <td class=\"colcode\">$row[ISRATE]</td> ";}
		$F_ISAVAL=Format_Nbr($row['ISAVAL'],$qtyNbrDec, $qtyEditCode, "Y", "", "");
		print "\n <td class=\"colnmbr\" id=\"aval{$trimLHID}_{$row['ISSID#']}_{$trimLot}\" title=\"$row[ISAVAL]\">$F_ISAVAL</td> ";
		
		$rowSID = $row['ISSID#'];
		$rowID = "{$trimLHID}_{$rowSID}_{$trimLot}";
		// Entry - Discount
		if ($row['LWWQTY']!=0) {print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"qtyp$rowID\" id=\"qtyp$rowID\" value=\"" . number_format($row['LWWQTY'],$qtyNbrDec, '.', '') . "\" size=\"8\" maxlength=\"15\" onChange=\"chgQtyPosted('$trimLHID','$rowSID','$trimLot','{$row['ISLOT']}') \">  ";}
		else                   {print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"qtyp$rowID\" id=\"qtyp$rowID\" value=\"\" size=\"8\" maxlength=\"15\" onChange=\"chgQtyPosted('$trimLHID','$rowSID','$trimLot','{$row['ISLOT']}') \">  ";}
		print "\n                                                        <input type=\"checkbox\" name=\"sqty$rowID}\" id=\"sqty$rowID\" value=\"S\" $row[CHECKPOSTED] onClick=\"checkQtyPosted('$trimLHID','$rowSID','$trimLot','{$row['ISLOT']}')\" title=\"Update posted with variance\"></td>  ";

		                        print "\n <td><input type=\"hidden\" name=\"fqtyp$rowID\" id=\"ftyp$rowID\" value=\"" . number_format($row['LWWQTY'],$qtyNbrDec, '.', '') . "\"></td>  ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}

	if ($rowCount == 0){require 'NoRecordsFound.php';}

	print "\n </table></form> ";
}
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
print "\n </table>";
require_once 'Copyright.php';

require_once 'Trailer.php';
print "\n <script TYPE=\"text/javascript\">";
print "\n    var hiddenJavaArray = new Array({$hiddenJavaArray}new Array(\"QUIT\",\"\",\"\",\"\",\"\",\"\",\"\")); ";
print "\n </script>";
print "\n </body> \n </html>";
?>
