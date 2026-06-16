<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$itemNumber = trim ( $_GET ['itemNumber'] );
$warehouseNumber = $_GET ['warehouseNumber'];
$stockLocId = $_GET ['stockLocId'];
$lotItem = $_GET ['lotItem'];
$qtyRequired = $_GET ['qtyRequired'];
$fromTo = $_GET ['fromTo'];
$fromToDesc = ($fromTo == 'F') ? 'From' : 'To';
$firstTime = (isset ( $_GET ['firstTime'] )) ? $_GET ['firstTime'] : "";
$useRepl = (isset ( $_GET ['useRepl'] )) ? $_GET ['useRepl'] : "N";

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

$page_title = "Transfer {$fromToDesc} Item";
$scriptName = "TransferItemAlloc.php";
$scriptVarBase = "{$genericVarBase}&amp;itemNumber=" . urlencode ( trim ( $itemNumber ) ) . "&amp;itemDesc=" . urlencode ( trim ( $itemDesc ) ) . "&amp;warehouseNumber=" . urlencode ( trim ( $warehouseNumber ) ) . "&amp;stockLocId=" . urlencode ( trim ( $stockLocId ) ) . "&amp;fromTo=" . urlencode ( trim ( $fromTo ) ) . "&amp;useRepl=" . urlencode ( trim ( $useRepl ) ) . "&amp;qtyRequired=" . urlencode ( trim ( $qtyRequired ) );
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $prtMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$popUpWin = "Y";
$allowSaveFilter = "N";
$pageSelectList = "N";

$stmtSQL = " Select * From HDITSLV02 Where ISITEM='$itemNumber' and ISSID#=$stockLocId Limit 1";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$itslRow = db2_fetch_assoc ( $sqlResult );

$backURL = $_SESSION [$fromURL];

if ($backURL == "") {
	$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=488";
}
$_SESSION[$fromURL]=$backURL;

if ($tag == "Edit_Data") {
	$item = $_GET ['item'];
	$whs = $_GET ['whs'];
	$fid = $_GET ['fid'];
	$tid = $_GET ['tid'];
	$lot = $_GET ['lot'];
	$qty = $_GET ['qty'];
	
	$stmtSQL = " Select * From HDITSLV01 Where ISITEM='$item' and ISSID#=$fid Limit 1";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$fromRow = db2_fetch_assoc ( $sqlResult );
	
	$stmtSQL = " Select * From HDITSLV01 Where ISITEM='$item' and ISSID#=$tid Limit 1";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$toRow = db2_fetch_assoc ( $sqlResult );
	
	$edtVar = "";
	Concat_Field ( "@@item", $fromRow ['ISITEM'] );
	Concat_Field ( "@@whs@", $fromRow ['ISWHS'] );
	Concat_Field ( "@@lot@", $lot );
	Concat_Field ( "@@qty@", $qty );
	Concat_Field ( "@@stkl", $CISTKL );
	Concat_Field ( "@@floc", $fromRow ['ISSTKR'] . $fromRow ['ISAILE'] . $fromRow ['ISSLOC'] );
	Concat_Field ( "@@fsid", $fid );
	Concat_Field ( "@@sloc", $toRow ['ISSTKR'] . $toRow ['ISAILE'] . $toRow ['ISSLOC'] );
	Concat_Field ( "@@twhs", $whs );
	$edtVar .= "}{";
	
	$maintenanceCode = 'X'; // Transfer without License Plate
	$errFound = "";
	$errVar = "";
	$wrnVar = "";
	$returnValue = Maintain_Edit ( "HIVLPM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar );
	$maintenanceCode = $returnValue ['maintenanceCode'];
	$errFound = $returnValue ['errFound'];
	$edtVar = $returnValue ['edtVar'];
	$errVar = $returnValue ['errVar'];
	
	if ($errFound == "") {
		if ($lot != '') {
			$stmtSQL = " Select max(ISRATE) as ISRATE,max(ISMXOH) as ISMXOH,max(ISRPQT) as ISRPQT,max(ISSGRP) as ISSGRP,max(ISECQT) as ISECQT 
			             from HDITSL where ISITEM='{$toRow ['ISITEM']}' and ISWHS={$toRow ['ISWHS']} and ISSID#={$tid};";
			$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
			$lotRow = db2_fetch_assoc ( $sqlResult );
			
			$stmtSQL = " Update HDITSL set ISRATE={$lotRow['ISRATE']},ISMXOH={$lotRow['ISMXOH']},ISRPQT={$lotRow['ISRPQT']},ISSGRP={$lotRow['ISSGRP']},ISECQT={$lotRow['ISECQT']} where ISITEM='{$toRow ['ISITEM']}' and ISWHS={$toRow ['ISWHS']} and ISSID#={$tid};";
			$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		}
		
		$confMessage = "Confirm Transfer of " . $qty . " to " . trim($toRow ['ISITEM']) . " / " . trim($toRow ['ISWHS']) . " / " . $toRow ['ISSTKR'] . $toRow ['ISAILE'] . $toRow ['ISSLOC'];
		print "\n \n <script TYPE=\"text/javascript\">";
		$backURL .= "&amp;confMessage=" . urlencode ( trim ( $confMessage ) );
		$backURL = str_replace ( "&amp;", "&", $backURL );
		print "\n   opener.location.href=\"{$backURL}\"";
		print "\n   window.close();";
		print "\n </script> \n";
		
		exit ();
	}
}

$itemDesc = RetValue ( "IMITEM='{$itemNumber}'", "HDIMST", "IMIMDS" );
$stkLoc = RetValue ( "IWITEM='{$itemNumber}' and IWWHS={$warehouseNumber}", "HDIWHS", "coalesce(IWSTKL,'N')" );
$lot = RetValue ( "IMITEM='{$itemNumber}'", "HDIMST", "coalesce(IMLOT,'N')" );
$dftOrderBy = array (array ("ISRATE", "A", "Priority" ), array ("ISLOC", "A", "Location" ) );

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != "") {
	$dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if ($sequence == "StockLoc") {
		$orby = array (array ("ISLOC", "A", "Location" ), array ("ISWHS", "A", "" ) );
	} elseif ($sequence == "Whs") {
		$orby = array (array ("ISWHS", "A", "Warehouse" ), array ("ISLOC", "A", "Location" ) );
	} elseif ($sequence == "LotNumber") {
		$orby = array (array ("ISLOT", "A", "Lot Number" ) );
	} elseif ($sequence == "Priority") {
		$orby = array (array ("ISRATE", "A", "Priority" ), array ("ISLOC", "A", "Location" ), array ("ISWHS", "A", "" ) );
	} elseif ($sequence == "ReplPriority") {
		$orby = array (array ("RSPRTY", "A", "Priority" ), array ("ISLOC", "A", "Location" ), array ("ISWHS", "A", "" ) );
	} elseif ($sequence == "QtyOnHand") {
		$orby = array (array ("ISQOH", "A", "Quantity On Hand" ), array ("ISLOC", "A", "Location" ), array ("ISWHS", "A", "" ) );
	} elseif ($sequence == "QtyHeld") {
		$orby = array (array ("ISQHSR", "A", "Quantity Held" ), array ("ISLOC", "A", "Location" ), array ("ISWHS", "A", "" ) );
	} elseif ($sequence == "QtyReserved") {
		$orby = array (array ("ISQRES", "A", "Quantity Reserved" ), array ("ISLOC", "A", "Location" ), array ("ISWHS", "A", "" ) );
	} elseif ($sequence == "QtyAvail") {
		$orby = array (array ("ISAVQT", "A", "Quantity Available" ), array ("ISLOC", "A", "Location" ), array ("ISWHS", "A", "" ) );
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

if ($useRepl == 'Y') {
	$stmtSQL = "Select ISSID# as ISSID from HDITSL where ISITEM='$itemNumber' and ISSID#<>$stockLocId
                and exists (Select * from IVRSFW where RSTWHS=$warehouseNumber and        
                           (RSTSKR='{$itslRow ['ISSTKR']}' or RSTSKR='') and (RSFWHS=ISWHS and (RSFSKR=ISSTKR or RSFSKR='')))";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$replIds = [];
	while ( $row = db2_fetch_assoc ( $sqlResult ) ) {
		$replIds[] = $row[ISSID];
	}
	$ids = implode($replIds,',');
	$stmtSQL = "Select ISSID#,ISWHS,ISLOT,ISSTKR,ISAILE,ISSLOC,ISLOC,ISQOH,ISQHSR,ISQRES,ISAVQT,
	            (Select RSPRTY from IVRSFW where RSTWHS=$warehouseNumber and        
                           (RSTSKR='{$itslRow ['ISSTKR']}' or RSTSKR='') and (RSFWHS=ISWHS and (RSFSKR=ISSTKR or RSFSKR=''))
		         order by RSTSKR desc,RSFSKR desc
				 fetch first row only) as ISRATE";
	$fileSQL = "  HDITSLV01 ";                           
	$selectSQL = " ISITEM='$itemNumber' and ISSID# in ($ids) and ISAVQT > 0 ";
} else {
	$stmtSQL = "Select a.*, coalesce(IMIMDS,'') as IMIMDS ";
	$fileSQL = "  HDITSLV01 a left join HDIMST on IMITEM=ISITEM";
	$selectSQL = " ISITEM='$itemNumber' and ISSID#<>$stockLocId and ISAVQT > 0 ";
}

require 'stmtSQLSelect.php';
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
require_once 'CheckSel.js';
require_once 'CheckEnterSearch.php';
require_once 'Format_Nbr.js';
require_once 'Menu.js';
require_once 'DateEdit.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'ShowHideSelCriteria.php';

print "\n function transferQty(msg, item, whs, tid, fid, lot, aqty, qty, genbase) {";
print "\n if (qty > aqty) {";
print "\n   alert ('Transfer Quantity (' + qty + ') is greater than Quantity Available (' + aqty + ')')";
print "\n   window.location.href = window.location.href";
print "\n   return false";
print "\n }";
print "\n var r = confirm (msg);";
print "\n if (r == true) {";
print "\n   var encItem = encodeURIComponent(item);";
print "\n   var newURL = genbase + '&tag=Edit_Data&item=' + encItem + '&whs=' + whs + '&fid=' + fid + '&tid=' + tid + '&lot=' + lot + '&qty=' + qty;";
print "\n   window.location.href = newURL ";
print "\n } else {";
print "\n   window.location.href = window.location.href";
print "\n }";
print "\n }";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

if ($formatToPrint != "Y") {
	print "\n <table $contentTable> ";
	print "\n     <colgroup> <col width=\"80%\"><col width=\"15%\"> ";
	print "\n     <tr><td><h1>$page_title</h1></td> ";
	print "\n         <td class=\"toolbar\"> ";
	require_once 'HelpPage.php';
	print "\n &nbsp;<a href=\"javascript:window.close()\">$closeImageMed</a> ";
	print "\n </td></tr></table> ";
	print "$hrTagAttr";
	
	print "\n <table $contentTable id=\"paymentTable\"> <tr>";
	print "\n <th class=\"colhdr\">Item<br>Number</th>";
	print "\n <th class=\"colhdr\">Description</th>";
	print "\n <th class=\"colhdr\">Whs</th>";
	print "\n <th class=\"colhdr\">Location</th>";
	print "\n <th class=\"colhdr\">Quantity<br>On Hand</th>";
	print "\n <th class=\"colhdr\">Suggested<br>Replenishment<br>Point</th>";
	print "\n <th class=\"colhdr\">Replenishment<br>Quantity</th>";
	print "\n <th class=\"colhdr\">Maximum<br>On Hand<br>Quantity</th>";
	print "\n </tr>";
	
	print "\n <tr class=\"evenrow\"> ";
	print "\n <td class=\"colalph\">$itemNumber</td> ";
	print "\n <td class=\"colalph\">$itemDesc</td> ";
	print "\n <td class=\"colnmbr\">$warehouseNumber</td> ";
	print "\n <td class=\"colalph\">{$itslRow ['ISSTKR']} {$itslRow ['ISAILE']} {$itslRow ['ISSLOC']}</td> ";
	$F_qtyOnHand = Format_Nbr ( $itslRow ['ISQOH'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"colnmbr\">$F_qtyOnHand</td> ";
	$F_replPoint = Format_Nbr ( $itslRow ['ISSGRP'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"colnmbr\">$F_replPoint</td> ";
	$F_replQty = Format_Nbr ( $itslRow ['ISRPQT'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"colnmbr\">$F_replQty</td> ";
	$F_maxQty = Format_Nbr ( $itslRow ['ISMXOH'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"colnmbr\">$F_maxQty</td> ";
	print "\n </tr>";
	print "\n </table>";
	
	// Define Quick Search columns
	$qsOpt = "";
	$qsOpt .= "\n <option value=\"ISWHS|null|Whs|N|\" title=\"Warehouse\">Whs";
	$qsOpt .= "\n <option value=\"ISLOC|null|Location|A|U\" title=\"Location\" SELECTED>Location";
	if ($lot != "N") {
		$qsOpt .= "\n <option value=\"ISLOT|null|Lot|A|U\" title=\"Lot Number\" SELECTED>Lot Number";
	}
	require 'QuickSearchOption.php';
	
	print "\n <table $contentTable> <tr>";
	$returnValue = OrderBy_Sort ( "ISWHS" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Whs\"                title=\"Sequence By Whs, Stock Location\">{$sortPoint}Whs</a></th>";
	$returnValue = OrderBy_Sort ( "ISLOC" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StockLoc\"  title=\"Sequence By Location\">{$sortPoint}Location</a></th>";
	if ($lot != "N") {
		$returnValue = OrderBy_Sort ( "ISLOT" );
		$sortVar = $returnValue ['sortedBy'];
		$sortPoint = $returnValue ['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LotNumber\" title=\"Sequence By Lot Number\">{$sortPoint}Lot<br>Number</a></th>";
	}
	$returnValue = OrderBy_Sort ( "ISRATE" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Priority\"                title=\"Sequence By Priority\">{$sortPoint}Priority</a></th>";
	$returnValue = OrderBy_Sort ( "ISQOH" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyOnHand\"                title=\"Sequence By Quantity On Hand\">{$sortPoint}Quantity<br>On Hand</a></th>";
	$returnValue = OrderBy_Sort ( "ISQHSR" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyHeld\"                title=\"Sequence By Quantity Held\">{$sortPoint}Quantity<br>Held</a></th>";
	$returnValue = OrderBy_Sort ( "ISQRES" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyReserved\"                title=\"Sequence By Quantity Reserved\">{$sortPoint}Quantity<br>Reserved</a></th>";
	$returnValue = OrderBy_Sort ( "ISAVQT" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyAvail\"                title=\"Sequence By Quantity Available\">{$sortPoint}Quantity<br>Available</a></th>";
	$returnValue = OrderBy_Sort ( "QSQTY" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyPicked\"               title=\"Sequence By Transfer Quantity\">{$sortPoint}Transfer<br>Quantity</a></th>";
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
		$trimLot = trim ( $row ['ISLOT'] );
		require 'SetRowClass.php';
		
		print "\n <tr class=\"$rowClass\" id=\"row{$row['ISSID']}_{$trimLot}\"> ";
		print "\n <td class=\"colnmbr\">$row[ISWHS]</td> ";
		
		// Select Checkbox
		print "\n <td class=\"colalph\">$row[ISLOC]</td> ";
		if ($lot != "N") {
			print "\n <td class=\"colalph\">$row[ISLOT]</td> ";
		}
		print "\n <td class=\"colcode\">$row[ISRATE]</td> ";
		$F_ISQOH = Format_Nbr ( $row ['ISQOH'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
		print "\n <td class=\"colnmbr\" title=\"$row[ISQOH]\">$F_ISQOH</td> ";
		
		$F_ISQHSR = Format_Nbr ( $row ['ISQHSR'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
		print "\n <td class=\"colnmbr\"\">$F_ISQHSR</td> ";
		
		$F_ISQRES = Format_Nbr ( $row ['ISQRES'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
		print "\n <td class=\"colnmbr\"\">$F_ISQRES</td> ";
		
		$F_ISAVQT = Format_Nbr ( $row ['ISAVQT'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
		print "\n <td class=\"colnmbr\" id=\"aval\" title=\"$row[ISAVQT]\">$F_ISAVQT</td> ";
		
		$rowSID = $row ['ISSID#'];
		// Entry
		$loc = $row [ISSTKR] . $row [ISAILE] . $row [ISSLOC];
		$confirm = 'Confirm Transfer: \n   Quantity:  ' . $F_ISAVQT . '\n   Location:  ' . $loc;
		$confirmLot = ($lot != "N") ? '\n   Lot Number:  ' . trim ( $row [ISLOT] ) : '';
		$confirm .= $confirmLot;
		print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"qty{$rowCount}\" value=\"\" size=\"8\" maxlength=\"15\" onChange=\"transferQty('Confirm Transfer:\\n  Quantity ' + this.value + '\\n  Location: {$loc}{$confirmLot}','{$itemNumber}',$warehouseNumber,$stockLocId,'$rowSID','{$trimLot}',{$row ['ISAVQT']},this.value,'{$genericVarBase}')\">  ";
		print "\n                                <input type=\"checkbox\" name=\"sqty{$rowCount}\" id=\"sqty{$rowCount}\" value=\"true\" onClick=\"transferQty('{$confirm}','{$itemNumber}',$warehouseNumber,$stockLocId,'$rowSID','{$trimLot}',{$row ['ISAVQT']},{$row ['ISAVQT']},'{$genericVarBase}')\"></td>  ";
		
		print "\n </tr>";
		
		$startRow ++;
		$rowCount ++;
	}
	
	if ($rowCount == 0) {
		require 'NoRecordsFound.php';
	}
	
	print "\n </table> ";
}
require_once 'PageBottom.php';
print "$hrTagAttr";
print "\n </table>";
require_once 'Copyright.php';

require_once 'Trailer.php';
print "\n </body> \n </html>";
?>
