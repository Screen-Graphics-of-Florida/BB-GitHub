<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$itemNumber = $_GET ['itemNumber'];
$itemDesc = $_GET ['itemDesc'];
$whsNumber = $_GET ['whsNumber'];
$stkLoc = $_GET ['stkLoc'];
$lotItem = $_GET ['lotItem'];
$qtyRequired = $_GET ['qtyRequired'];
$firstTime = (isset ( $_GET ['firstTime'] )) ? $_GET ['firstTime'] : "";

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

$page_title = "Order Picking Item";
$scriptName = "OrderPickingAlloc.php";
$scriptVarBase = "{$genericVarBase}&amp;itemNumber=" . urlencode ( trim ( $itemNumber ) ) . "&amp;itemDesc=" . urlencode ( trim ( $itemDesc ) ) . "&amp;whsNumber=" . urlencode ( trim ( $whsNumber ) ) . "&amp;stkLoc=" . urlencode ( trim ( $stkLoc ) ) . "&amp;lotItem=" . urlencode ( trim ( $lotItem ) ) . "&amp;qtyRequired=" . urlencode ( trim ( $qtyRequired ) );
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $prtMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
if ($stkLoc == "Y") {
	$dftOrderBy = array (array ("ISRATE", "A", "Priority" ), array ("ISSTKR", "A", "Stockroom" ), array ("ISAILE", "A", "Aisle" ), array ("ISSLOC", "A", "Location" ) );
} else {
	$dftOrderBy = array (array ("ISLOT", "A", "Lot Number" ) );
}
$popUpWin = "Y";
$allowSaveFilter = "N";
$pageSelectList = "N";

// Find the next Item/Whs to pick
$stmtSQL = "Select * From OEOPIS Where SIUSER='{$userProfile}' Order By SISTKR,SIAILE,SISLOC,SIITEM,SIWHS";
$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
$nextURL = NULL;
$current = FALSE;
while ( $row = db2_fetch_assoc ( $result, $nextRow ) ) {
	if (trim($row ['SIITEM']) == $itemNumber && trim($row ['SIWHS']) == $whsNumber) {
		$current = TRUE;
	} elseif ($current) {
		$IWSTKL = RetValue ( "IWITEM='{$row[SIITEM]}' and IWWHS=$row[SIWHS]", "HDIWHS", "coalesce(IWSTKL,'N')" );
		$IMLOT = RetValue ( "IMITEM='{$row[SIITEM]}'", "HDIMST", "coalesce(IMLOT,'N')" );
		$nextURL = "&amp;itemNumber=" . urlencode ( trim ( $row[SIITEM] ) ) . "&amp;itemDesc=" . urlencode ( trim ( $row[SIIMDS] ) ) . "&amp;whsNumber=" . urlencode ( trim ( $row[SIWHS] ) ) . "&amp;qtyRequired=" . urlencode ( trim ( $row[SIQTYR] ) ) . "&amp;stkLoc=" . urlencode ( trim ( $IWSTKL ) ) . "&amp;lotItem=" . urlencode ( trim ( $IMLOT ) ) . "&amp;firstTime=Y";
		break;
	}
	$nextRow ++;
}

// New Item/Whs then reset search criteria and order by.
if ($firstTime == "Y") {
	$chgSrch = "D";
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != "") {
	$dspMaxRows = $prtMaxRows;
}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if ($sequence == "StockLoc") {
		$orby = array (array ("ISSTKR", "A", "Stock Location" ), array ("ISAILE", "A", "" ), array ("ISSLOC", "A", "" ) );
	} elseif ($sequence == "LotNumber" && $stkLoc == "Y") {
		$orby = array (array ("ISLOT", "A", "Lot Number" ), array ("ISSTKR", "A", "Stock Location" ), array ("ISAILE", "A", "" ), array ("ISSLOC", "A", "" ) );
	} elseif ($sequence == "LotNumber") {
		$orby = array (array (" // ", "A", "Lot Number" ) );
	} elseif ($sequence == "Priority") {
		$orby = array (array ("ISRATE", "A", "Priority" ), array ("ISSTKR", "A", "Stock Location" ), array ("ISAILE", "A", "" ), array ("ISSLOC", "A", "" ) );
	} elseif ($sequence == "QtyAvail" && $stkLoc == "Y") {
		$orby = array (array ("ISAVAL", "A", "Quantity Available" ), array ("ISSTKR", "A", "Stock Location" ), array ("ISAILE", "A", "" ), array ("ISSLOC", "A", "" ) );
	} elseif ($sequence == "QtyAvail") {
		$orby = array (array ("ISAVAL", "A", "Quantity Available" ), array ("ISLOT", "A", "Lot Number" ) );
	} elseif ($sequence == "QtyPicked" && $stkLoc == "Y") {
		$orby = array (array ("QSQTY", "D", "Quantity Picked" ), array ("ISSTKR", "A", "Stock Location" ), array ("ISAILE", "A", "" ), array ("ISSLOC", "A", "" ) );
	} elseif ($sequence == "QtyPicked") {
		$orby = array (array ("QSQTY", "D", "Quantity Picked" ), array ("ISLOT", "A", "Lot Number" ) );
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

$dspLot = "";
if ($lotItem == "A" || $lotItem == "M") {
	$dspLot = "Y";
}
if ($dspLot == "Y" && $stkLoc == "Y") {
	$lotSQL = " and ISLOT<>' ' ";
} else {
	$lotSQL = '';
}

if ($stkLoc == "Y") {
	$stmtSQL = "Select coalesce(ISSID#,0) as ISSID#, coalesce(ISSTKR,'') as ISSTKR, coalesce(ISAILE,'') as ISAILE, coalesce(ISSLOC,'') as ISSLOC
	              ,coalesce(ISSTKR,'') || coalesce(ISAILE,'') || coalesce(ISSLOC,'') as ISSTKL
                  ,coalesce(ISLOT,QSLOT) as ISLOT, coalesce(ISRATE,0) as ISRATE, ISQOH - ISQRES - ISQHSR - coalesce(WQTY, 0) as ISAVAL
                  ,coalesce(QSQTY,0) as QSQTY,Case When coalesce(QSQTY,0)<>0 Then 'CHECKED' ELSE ' ' End as CHECKPOSTED ";
	$fileSQL = "  OEOPIS left join HDITSL on  SIITEM=ISITEM and SIWHS=ISWHS
                         left join OEOPQS on QSUSER='$userProfile' and QSITEM='$itemNumber' and QSWHS=$whsNumber and QSSID=ISSID# and QSLOT=ISLOT
                         left join table (Select QSSID as SID,QSLOT as LOTN,sum(QSQTY) as WQTY from OEOPQS Where QSITEM='$itemNumber'
                                         GROUP BY QSSID,QSLOT) as LPWK on (SID,LOTN)=(coalesce(ISSID#,0),coalesce(ISLOT,QSLOT)) ";
	$selectSQL = " (SIUSER,SIITEM,SIWHS)=('$userProfile','$itemNumber',$whsNumber) and (ISQOH - ISQRES - ISQHSR > 0 or QSQTY>0) $lotSQL ";
	$orderBy = " ISRATE,ISSTKR,ISAILE,ISSLOC,ISLOT";
} elseif ($dspLot == "Y") {
	$stmtSQL = "Select 0 as ISSID#, '' as ISSTKR, '' as ISAILE, '' as ISSLOC, '' as ISSTKL
	              ,coalesce(LTLT#,QSLOT) as ISLOT, 0 as ISRATE, LTQOH - LTQAL - LTLQHR - coalesce(WQTY, 0) as ISAVAL
	              ,coalesce(QSQTY,0) as QSQTY,Case When coalesce(QSQTY,0)<>0 Then 'CHECKED' ELSE ' ' End as CHECKPOSTED ";
	$fileSQL = " OEOPIS left join HDLOT on SIITEM=LTITEM and SIWHS=LTWH
	                    left join OEOPQS on QSUSER='$userProfile' and QSITEM='$itemNumber' and QSWHS=$whsNumber and QSSID=0 and QSLOT=LTLT#
	                    left join table (Select QSSID as SID,QSLOT as LOTN,sum(QSQTY) as WQTY from OEOPQS Where QSITEM='$itemNumber'
	                                     GROUP BY QSSID,QSLOT) as LPWK on (SID,LOTN)=(0,coalesce(LTLT#,QSLOT)) ";
	$selectSQL = " (SIUSER,SIITEM,SIWHS)=('$userProfile','$itemNumber',$whsNumber) and (LTQOH - LTQAL - LTLQHR > 0 or QSQTY>0) ";
	$orderBy = " ISLOT";
} else {
	$stmtSQL = "Select 0 as ISSID#, '' as ISSTKR, '' as ISAILE, '' as ISSLOC, '' as ISSTKL
	              ,'' as ISLOT, 0 as ISRATE, IWOHQT-IWQHSR - coalesce(QSQTY, 0) as ISAVAL
	              ,coalesce(QSQTY,0) as QSQTY,Case When coalesce(QSQTY,0)<>0 Then 'CHECKED' ELSE ' ' End as CHECKPOSTED ";
	$fileSQL = " OEOPIS left join HDIWHS on SIITEM=IWITEM and SIWHS=IWWHS
	                    left join OEOPQS on QSUSER='$userProfile' and QSITEM='$itemNumber' and QSWHS=$whsNumber ";
	$selectSQL = " (SIUSER,SIITEM,SIWHS)=('$userProfile','$itemNumber',$whsNumber) ";
	$orderBy = " ISLOT";
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
require_once 'OrderPickingAllocJava.php';
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

if ($formatToPrint != "Y") {
	print "\n <table $contentTable>";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>$page_title</h1></td>";
	print "\n     <td class=\"toolbar\">";
	print "\n <a href=\"{$homeURL}{$phpPath}OrderPickingSummary.php{$genericVarBase}\">$previousImage</a>&nbsp;";
	if ($nextURL) {
		print "\n <a href=\"{$homeURL}{$phpPath}OrderPickingAlloc.php{$scriptVarBase}{$nextURL}\">$nextImage</a>";
	} else {
		print "\n <a href=\"{$homeURL}{$phpPath}OrderPickingSummary.php{$genericVarBase}&amp;viewAll=Y\">$nextImage</a>&nbsp;";
	}
	
	$medIcon = "Y";
	require 'HelpPage.php';
	print "\n </td></tr></table>";
	print "$hrTagAttr";
	
$stmtSQL = "Select IXDESC From HDIMXD Where IXITEM = '{$itemNumber}' and IXDOCT = 'PIC'";
$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
$nextRow = 1;
while ( $row = db2_fetch_assoc ( $result, $nextRow ) ) {
	if ($nextRow >1) 	print "\n <br>";
	print "\n <span class=\"colalph\">$row[IXDESC]</span>";
	$nextRow ++;
}

    $stkUOM = RetValue ( "IMITEM='{$itemNumber}'", "HDIMST", "coalesce(IMUOMS,'')" );
    $stkUOMDesc = RetValue ( "UMUOM='{$stkUOM}'", "HDUOM", "coalesce(UMUMSD,'')" );
	print "\n <table $contentTable id=\"paymentTable\"> <tr>";
	print "\n <th class=\"colhdr\">Item<br>Number</th>";
	print "\n <th class=\"colhdr\">Whs</th>";
	print "\n <th class=\"colhdr\">Description</th>";
	print "\n <th class=\"colhdr\">UOM</th>";
	print "\n <th class=\"colhdr\">Quantity<br>Required</th>";
	print "\n <th class=\"colhdr\">Quantity<br>Picked</th>";
	print "\n <th class=\"colhdr\">Quantity<br>Variance</th>";
	print "\n </tr>";
	
	print "\n <tr class=\"evenrow\"> ";
	print "\n <td class=\"colalph\">$itemNumber</td> ";
	print "\n <td class=\"colnmbr\">$whsNumber</td> ";
	print "\n <td class=\"colalph\">$itemDesc</td> ";
	print "\n <td class=\"colalph\">$stkUOMDesc</td> ";
	$F_qtyRequired = Format_Nbr ( $qtyRequired, $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"colnmbr\" id=\"QSQTYreq\" title=\"$qtyRequired\">$F_qtyRequired</td> ";
	$qtyPicked = RetValue ( "QSUSER='{$userProfile}' and QSITEM='$itemNumber' and QSWHS=$whsNumber", "OEOPQS", "coalesce(sum(QSQTY),0)" );
	$F_qtyPicked = Format_Nbr ( $qtyPicked, $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"colnmbr\" id=\"QSQTYsum\" title=\"$qtyPicked\">$F_qtyPicked</td> ";
	$qtyVar = $qtyRequired - $qtyPicked;
	$F_qtyVar = Format_Nbr ( $qtyVar, $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"oepriceover\" id=\"QSQTYvar\" title=\"$qtyVar\">$F_qtyVar</td> ";
	print "\n </tr>";
	print "\n </table>";

	$stmtSQL = "Select OCORD# as ORDER,OCORL# as LINE,OCCMNT as NOTE 
	            From OEOPOR inner join OEORDP on ORTURN=IDTURN 
	                        inner join OEORDT on ODORD#=IDORD# and ODORL#=IDORL# and ODBLN#=IDBLN# and ODITEM='{$itemNumber}' and ODWH={$whsNumber} 
	                        inner join OEOCMT on OCORD#=IDORD# and (OCORL#=0 or OCORL#=999 or OCORL#=IDORL# and OCBLN#=IDBLN#)  
	            Where ORUSER='{$userProfile}' and ODITEM='{$itemNumber}' and OCDOCT='PIC' 
	            Order By OCORD#,OCORL#,OCCSEQ";
	$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$nextRow = 1;
	$saveOrder = 0;
	$saveLine = 0;
	while ( $row = db2_fetch_assoc ( $result, $nextRow ) ) {
		if ($nextRow == 1) {
			print "\n <table $contentTable> <tr>";
			print "\n <th class=\"colhdr\">Order</th>";
			print "\n <th class=\"colhdr\">Line</th>";
			print "\n <th class=\"colhdr\">Notes</th>";
			print "\n </tr>";
		}
		if ($saveOrder == $row[ORDER]) {
		    if ($saveLine == $row[LINE]) {
		        $saveLine = $row[LINE];
		        $row[LINE] = '';
		    }
		    $saveOrder = $row[ORDER];
		    $row[ORDER] = '';
		} else {
		    $saveOrder = $row[ORDER];
		    $saveLine = $row[LINE];
		    require 'SetRowClass.php';
		}
		print "\n <tr class=\"$rowClass\"> ";
		print "\n <td class=\"colnmbr\">$row[ORDER]</td> ";
		if ($row[LINE] == 000 || $row[LINE] == 999) {
    		print "\n <td class=\"colalph\"></td> ";
		} else {
    		print "\n <td class=\"colnmbr\">$row[LINE]</td> ";
		}
		print "\n <td class=\"colalph\">$row[NOTE]</td> ";
		print "\n </tr>";
		$nextRow ++;
	}
	if ($nextRow > 1) {
		print "\n </table>";
	}
	
	
	// Define Quick Search columns
	$qsOpt = "";
	if ($stkLoc == "Y") {
		$qsOpt .= "\n <option value=\"concat(ISSTKR, concat(ISAILE,ISSLOC))|null|Stock Location|A|U\" title=\"StockLoc\" SELECTED>Stock Location";
	}
	if ($stkLoc == "Y" && $dspLot == "Y") {
		$qsOpt .= "\n <option value=\"ISLOT|null|Lot Number|A|U\" title=\"Lot Number\" SELECTED>Lot Number";
	} elseif ($dspLot == "Y") {
		$qsOpt .= "\n <option value=\"LTLT#|null|Lot Number|A|U\" title=\"Lot Number\" SELECTED>Lot Number";
	}
	if ($stkLoc == "Y") {
		$qsOpt .= "\n <option value=\"ISRATE|null|Priority|N|\" title=\"Priority\">Priority";
	}
	require 'QuickSearchOption.php';
	
	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\">";
	print "\n <table $contentTable> <tr>";
	if ($stkLoc == "Y") {
		$returnValue = OrderBy_Sort ( "ISSTKR" );
		$sortVar = $returnValue ['sortedBy'];
		$sortPoint = $returnValue ['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\" colspan=\"3\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StockLoc\"  title=\"Sequence By Stock Location\">{$sortPoint}Stock<br>Location</a></th>";
	}
	if ($dspLot == "Y") {
		$returnValue = OrderBy_Sort ( "ISLOT" );
		$sortVar = $returnValue ['sortedBy'];
		$sortPoint = $returnValue ['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LotNumber\" title=\"Sequence By Lot Number\">{$sortPoint}Lot<br>Number</a></th>";
	}
	if ($stkLoc == "Y") {
		$returnValue = OrderBy_Sort ( "ISRATE" );
		$sortVar = $returnValue ['sortedBy'];
		$sortPoint = $returnValue ['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Priority\"                title=\"Sequence By Priority\">{$sortPoint}Priority</a></th>";
	}
	$returnValue = OrderBy_Sort ( "ISAVAL" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyAvail\"                title=\"Sequence By Quantity Available\">{$sortPoint}Quantity<br>Available</a></th>";
	$returnValue = OrderBy_Sort ( "QSQTY" );
	$sortVar = $returnValue ['sortedBy'];
	$sortPoint = $returnValue ['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=QtyPicked\"               title=\"Sequence By Quantity Posted\">{$sortPoint}Quantity<br>Picked</a></th>";
	print "\n </tr>";
	
	// Add hidden field needed for Active Responses
	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colalph\"></td> ";
	print "\n     <td class=\"colalph\" colspan=\"50\"><span id=\"quickEntryMessage\"></span></td> ";
	print "\n </tr>";
	
	// Stock/Lot rows
	$posRow = null;
	$rowCount = 0;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		if ($rowCount >= $dspMaxRows) {
			break;
		}
		$trimLot = trim ( $row ['ISLOT'] );
		require 'SetRowClass.php';
		
		print "\n <tr class=\"$rowClass\" id=\"row{$row['ISSID']}_{$trimLot}\"> ";
		
		// Select Checkbox
		if ($stkLoc == "Y") {
			print "\n <td class=\"colalph\">$row[ISSTKR]</td> ";
			print "\n <td class=\"colalph\">$row[ISAILE]</td> ";
			print "\n <td class=\"colalph\">$row[ISSLOC]</td> ";
		}
		if ($dspLot == "Y") {
			print "\n <td class=\"colalph\">$row[ISLOT]</td> ";
		}
		if ($stkLoc == "Y") {
			print "\n <td class=\"colcode\">$row[ISRATE]</td> ";
		}
		$F_ISAVAL = Format_Nbr ( $row ['ISAVAL'], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
		print "\n <td class=\"colnmbr\" id=\"aval_{$row['ISSID#']}_{$trimLot}\" title=\"$row[ISAVAL]\">$F_ISAVAL</td> ";
		
		$rowSID = $row ['ISSID#'];
		$rowID = "{$trimLHID}_{$rowSID}_{$trimLot}";
		if (is_null($posRow) && $wildCardDisplay != "") {
		    $posRow =  'qtyp' . $rowID;
		}
		// Entry
		if ($row ['QSQTY'] != 0) {
			print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"qtyp$rowID\" id=\"qtyp$rowID\" value=\"" . number_format ( $row ['QSQTY'], $qtyNbrDec, '.', '' ) . "\" size=\"8\" maxlength=\"15\" onChange=\"chgQtyPosted('$rowSID','$trimLot','{$row['ISLOT']}') \">  ";
		} else {
			print "\n <td class=\"inputnmbr\" nowrap><input type=\"text\" name=\"qtyp$rowID\" id=\"qtyp$rowID\" value=\"\" size=\"8\" maxlength=\"15\" onChange=\"chgQtyPosted('$rowSID','$trimLot','{$row['ISLOT']}') \">  ";
		}
		if ($orderPickingQtyCheckbox != 'N') {
		    print "\n <input type=\"checkbox\" name=\"sqty$rowID\" id=\"sqty$rowID\" value=\"S\" $row[CHECKPOSTED] onClick=\"checkQtyPosted('$rowSID','$trimLot','{$row['ISLOT']}')\" title=\"Update posted with variance\"></td>  ";
		}
		
		print "\n <td><input type=\"hidden\" name=\"fqtyp$rowID\" id=\"ftyp$rowID\" value=\"" . number_format ( $row ['QSQTY'], $qtyNbrDec, '.', '' ) . "\"></td>  ";
		print "\n </tr>";
		
		$startRow ++;
		$rowCount ++;
	}
	
	if ($rowCount == 0) {
		require 'NoRecordsFound.php';
	}
	if ($posRow) {
	    print "\n <script TYPE=\"text/javascript\">";
	    print "\n document.Chg.{$posRow}.focus();";
	    print "\n </script>";
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
