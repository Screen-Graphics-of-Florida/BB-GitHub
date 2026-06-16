<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$licPlate = $_GET ['licPlate'];
$itemNumber = $_GET ['itemNumber'];
$whsNumber = $_GET ['whsNumber'];
$fLoc = $_GET ['fLoc'];
$stkLoc = (trim ( $fLoc ) != '') ? 'Y' : '';
$firstTime = (isset ( $_GET ['firstTime'] )) ? $_GET ['firstTime'] : "";

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "InventoryControl$dataBaseID.php";
require_once 'VarBase.php';
require_once 'WildCard.php';

$lotItem = RetValue ( "IMITEM='$itemNumber'", "HDIMST", "IMLOT" );
$itemDesc = RetValue ( "IMITEM='$itemNumber'", "HDIMST", "IMIMDS" );
$sid = RetValue ( "LHID='$licPlate'", "IVLPHD", "LHSLID" );

$backURL = $_SESSION [$fromURL];
if ($backURL == "") {
	$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=444";
}

$page_title = "Add to License Plate";
$scriptName = "LicPlateAdd.php";
$scriptVarBase = "{$genericVarBase}&amp;licPlate=" . urlencode ( trim ( $licPlate ) ) . "&amp;orderControlNumber=" . urlencode ( trim ( $orderControlNumber ) ) . "&amp;orderNumber=" . urlencode ( trim ( $orderNumber ) ) . "&amp;lineNumber=" . urlencode ( trim ( $lineNumber ) ) . "&amp;relNumber=" . urlencode ( trim ( $relNumber ) ) . "&amp;itemNumber=" . urlencode ( trim ( $itemNumber ) ) . "&amp;whsNumber=" . urlencode ( trim ( $whsNumber ) ) . "&amp;fLoc=" . urlencode ( trim ( $fLoc ) ) . "&amp;stkLoc=" . urlencode ( trim ( $stkLoc ) ) . "&amp;lotItem=" . urlencode ( trim ( $lotItem ) );
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
if ($itemNumber == '') {
	$dftOrderBy = array (array ("ISITEM", "A", "Item Number" ), array ("ISWHS", "A", "Warehouse" ) );
} else {
	$dftOrderBy = array (array ("ISLOT", "A", "Lot Number" ) );
}
$popUpWin = "Y";
$allowSaveFilter = "N";
$pageSelectList = "N";

if ($tag == "Edit_Data") {
	$maintenanceCode = 'I';
	$stmtSQL = "Select * From IVLPAW inner join IVLPHDV02 
	            on LWXHND='$eID' and LHID='' and ISSID=$sid and LWITEM=ISITEM and LWWHS=ISWHS and LWLOTN=ISLOT
	            Where LWXHND='$eID' and LWWQTY>0 Order By LWITEM";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$startRow = 1;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		$startRow ++;
		$edtVar = "";
		Concat_Field ( "@@item", $row ['LWITEM'] );
		Concat_Field ( "@@whs@", $row ['LWWHS'] );
		Concat_Field ( "@@lot@", $row ['LWLOTN'] );
		Concat_Field ( "@@qty@", $row ['LWWQTY'] );
		Concat_Field ( "@@plid", $licPlate );
		Concat_Field ( "@@sloc", $fLoc );
		Concat_Field ( "@@stkl", $CISTKL );
		Concat_Field ( "@@fsid", $sid );
		Concat_Field ( "@@fqty", $row ['QTY'] );
		$edtVar .= "}{";
		
		$returnValue = Maintain_Edit ( "HIVLPM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar );
		$maintenanceCode = $returnValue ['maintenanceCode'];
		$errFound = $returnValue ['errFound'];
		$edtVar = $returnValue ['edtVar'];
		$errVar = $returnValue ['errVar'];
	}
	if ($errFound == "") {
		if ($maintenanceCode == "I") {
			$confMessage = "Confirm Add to License Plate {$licPlate}";
		}
		$stmtSQL = " Delete From IVLPAW Where LWXHND='$eID'";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	}
}

// New Item/Whs then reset search criteria and order by.
if ($firstTime == "Y") {
	$chgSrch = "D";
	
	$stmtSQL = " Delete From IVLPAW Where LWXHND='$eID'";
	$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != "") {
	$dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if ($sequence == "Item") {
		$orby = array (array ("ISITEM", "A", "Item Number" ), array ("ISWHS", "A", "" ) );
	} elseif ($sequence == "Whs") {
		$orby = array (array ("ISWHS", "A", "Warehouse" ), array ("ISITEM", "A", "Item Number" ) );
	} elseif ($sequence == "ItemDesc") {
		$orby = array (array ("IMIMDS", "A", "Item Description" ), array ("ISITEM", "A", "Item Number" ) );
	} elseif ($sequence == "LotNumber") {
		$orby = array (array ("ISLOT", "A", "Lot Number" ) );
	} elseif ($sequence == "QtyAvail") {
		$orby = array (array ("QTY", "A", "Quantity Available" ), array ("ISITEM", "A", "Item Number" ) );
	} elseif ($sequence == "QtyAssigned") {
		$orby = array (array ("LWWQTY", "D", "Quantity To Assign" ), array ("ISITEM", "A", "Item Number" ) );
	}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
	require_once 'QuickSearch.php';
}

$uv_StockroomName = "ISSTKR";
$uv_AileName = "ISAILE";
$uv_StockLocationName = "ISSLOC";
require 'UserView.php';
require 'stmtSQLClear.php';
$stmtSQL .= " Select ISITEM, ISWHS, IMIMDS, ISSID, ISSTKR, ISAILE, ISSLOC, ISLOT, QTY - coalesce(LWWQTY, 0) as ISAVAL";
$stmtSQL .= "       ,coalesce(LWWQTY,0) as LWWQTY ,Case When coalesce(LWWQTY,0)<>0 Then 'CHECKED' ELSE ' ' End as CHECKPOSTED ";

$fileSQL .= "IVLPHDV02 inner join HDIMST on ISITEM=IMITEM ";
$fileSQL .= "       left join IVLPAW on LWXHND='$eID' and LWITEM=ISITEM and LWWHS=ISWHS and LWLOTN=ISLOT";
if ($itemNumber != '') {
	$selectSQL .= " ISITEM='$itemNumber' and ";
}
$selectSQL .= "ISSID=$sid and (coalesce(LWWQTY,0)<>0 or (QTY - coalesce(LWWQTY, 0)) > 0) and LHID='' ";

require 'stmtSQLSelect.php';
$sql_Record_Count = RetValue ( $selectSQL, $fileSQL, "count(*)" );
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
// print $stmtSQL;
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
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
require_once 'LicPlateAddJava.php';
print "\n function qtyWarning() {return confirm(\"Quantity Available is less than Quantity To Assign\");} ";
print "\n function validate(chgForm) {return true;}";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

if ($formatToPrint != "Y") {
	print "\n <table $contentTable>";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>$page_title</h1></td>";
	print "\n     <td class=\"toolbar\">";
	print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
	print "\n <a href=\"$backURL\">$cancelImageMed</a>";
	
	$medIcon = "Y";
	require 'HelpPage.php';
	print "\n </td></tr></table>";
	
	print "\n <table $contentTable <tr>";
	print "\n <tr><td class=\"hdrtitl\">License Plate:</td><td class=\"hdrdata\">$licPlate</td></tr>";
	if ($itemNumber != '') {
		print "\n <tr><td class=\"hdrtitl\">Item Number:</td><td class=\"hdrdata\">$itemNumber &nbsp; $itemDesc</td></tr>";
	}
	print "\n <tr><td class=\"hdrtitl\">Location:</td><td class=\"hdrdata\">$fLoc</td></tr>";
	print "\n </tr>";
	print "\n </table>";
	print "$hrTagAttr";
	
	// Define Quick Search columns
	$qsOpt = "";
	if ($itemNumber == '') {
		$qsOpt .= "\n <option value=\"ISITEM|null|Item|A|U\" title=\"Item Number\" SELECTED>Item Number";
		$qsOpt .= "\n <option value=\"ISWHS|null|Whs|N\" title=\"Whs\">Warehouse";
		$qsOpt .= "\n <option value=\"IMIMDS|null|Desc|A|U\" title=\"Item Description\">Item Description";
		if ($CILTUS == "Y") {
			$qsOpt .= "\n <option value=\"ISLOT|null|Lot Number|A|U\" title=\"Lot Number\">Lot Number";
		}
	} elseif ($CILTUS == "Y" && ($itemNumber == '' || $lotItem != 'N' && $itemNumber != '')) {
		$qsOpt .= "\n <option value=\"ISLOT|null|Lot Number|A|U\" title=\"Lot Number\" SELECTED>Lot Number";
	}
	require 'QuickSearchOption.php';
	
	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\">";
	print "\n <table $contentTable> <tr>";
	$returnValue = OrderBy_Sort ( "LHID" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	if ($itemNumber == '') {
		$returnValue = OrderBy_Sort ( "ISITEM" );
		$sortVar = $returnValue ['sortedBy'];
		$sortPoint = $returnValue ['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Item\"  title=\"Sequence By Item Number\">{$sortPoint}Item<br>Number</a></th>";
		
		$returnValue = OrderBy_Sort ( "ISWHS" );
		$sortVar = $returnValue ['sortedBy'];
		$sortPoint = $returnValue ['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Whs\"  title=\"Sequence By Whs\">{$sortPoint}Whs</a></th>";
		
		$returnValue = OrderBy_Sort ( "IMIMDS" );
		$sortVar = $returnValue ['sortedBy'];
		$sortPoint = $returnValue ['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ItemDesc\"  title=\"Sequence By Item Description\">{$sortPoint}Item<br>Description</a></th>";
	}
	if ($CILTUS == "Y" && ($itemNumber == '' || $lotItem != 'N' && $itemNumber != '')) {
		$returnValue = OrderBy_Sort ( "ISLOT" );
		$sortVar = $returnValue ['sortedBy'];
		$sortPoint = $returnValue ['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LotNumber\" title=\"Sequence By Lot Number\">{$sortPoint}Lot<br>Number</a></th>";
	}
	$returnValue = OrderBy_Sort ( "ISAVAL" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyAvail\"                title=\"Sequence By Quantity Available\">{$sortPoint}Quantity<br>Available</a></th>";
	$returnValue = OrderBy_Sort ( "LWWQTY" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyAssigned\"               title=\"Sequence By Quantity To Assign\">{$sortPoint}Quantity<br>To Assign</a></th>";
	print "\n </tr>";
	
	// Add hidden field needed for Active Responses
	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colalph\"></td> ";
	print "\n     <td class=\"colalph\" colspan=\"50\"><span id=\"quickEntryMessage\"></span></td> ";
	print "\n </tr>";
	
	// Stock/Lot rows
	$rowCount = 0;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		if ($rowCount >= $dspMaxRows) {
			break;
		}
		$trimITEM = trim ( $row ['ISITEM'] );
		$trimLot = trim ( $row ['ISLOT'] );
		$whs = $row ['ISWHS'];
		$rowSID = $row ['ISSID'];
		$rowID = "{$trimITEM}_{$whs}_{$trimLot}";
		require 'SetRowClass.php';
		
		print "\n <tr class=\"$rowClass\" id=\"row{$row['ISSID']}_{$trimLot}\"> ";
		if ($itemNumber == '') {
			print "\n <td class=\"colalph\">$row[ISITEM]</td> ";
			print "\n <td class=\"colnmbr\">$row[ISWHS]</td> ";
			print "\n <td class=\"colalph\">$row[IMIMDS]</td> ";
		}
		if ($CILTUS == "Y" && ($itemNumber == '' || $lotItem != 'N' && $itemNumber != '')) {
			print "\n <td class=\"colalph\">$row[ISLOT]</td> ";
		}
		$F_ISAVAL = Format_Nbr ( $row ['ISAVAL'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
		print "\n <td class=\"colnmbr\" id=\"aval{$rowID}\" title=\"$row[ISAVAL]\">$F_ISAVAL</td> ";
		
		// Entry
		if ($row ['LWWQTY'] != 0) {
			print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"qtyp$rowID\" id=\"qtyp$rowID\" value=\"" . number_format ( $row ['LWWQTY'], $qtyNbrDec, '.', '' ) . "\" size=\"8\" maxlength=\"15\" onChange=\"chgQtyPosted('$trimITEM','$whs','$trimLot','{$row['ISLOT']}') \">  ";
		} else {
			print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"qtyp$rowID\" id=\"qtyp$rowID\" value=\"\" size=\"8\" maxlength=\"15\" onChange=\"chgQtyPosted('$trimITEM','$whs','$trimLot','{$row['ISLOT']}') \">  ";
		}
		print "\n                                                        <input type=\"checkbox\" name=\"sqty$rowID\" id=\"sqty$rowID\" value=\"S\" $row[CHECKPOSTED] onClick=\"checkQtyPosted('$trimITEM','$whs','$trimLot','{$row['ISLOT']}')\" title=\"Update posted with variance\"></td>  ";
		
		print "\n <td><input type=\"hidden\" name=\"fqtyp$rowID\" id=\"ftyp$rowID\" value=\"" . number_format ( $row ['LWWQTY'], $qtyNbrDec, '.', '' ) . "\"></td>  ";
		print "\n </tr>";
		
		$startRow ++;
		$rowCount ++;
	}
	
	if ($rowCount == 0) {
		require 'NoRecordsFound.php';
	}
	
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
