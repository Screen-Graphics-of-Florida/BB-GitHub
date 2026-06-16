<?php
$updStkLoc = $_GET ['updStkLoc'];
$viewAll = $_GET ['viewAll'];

require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once "InventoryControl$dataBaseID.php";
require_once 'WildCard.php';

$page_title = "Order Picking Summary";
$scriptName = "OrderPickingSummary.php";
$scriptVarBase = "{$genericVarBase}&amp;viewAll=" . urlencode ( trim ( $viewAll ) );
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$viewAllURL = "{$homeURL}{$phpPath}{$scriptName}{$genericVarBase}&amp;viewAll=Y";
$viewOpenURL = "{$homeURL}{$phpPath}{$scriptName}{$genericVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$dftOrderBy = array (array ("SISTKR", "A", "Desc" ), array ("SIAILE", "A", "Number" ) );
$maxRows = $dspMaxRows;

$backURL = $_SESSION [$fromURL];

if ($updStkLoc == 'Y') {
	updateStockLoc ();
}

$errMsg = '';
if ($tag == "Edit_Data" && $orderPickingScanItem == "Y") {
    $scanItem = strtoupper(trim ( $_POST ['scanItem'] ));
    $stmtSQL .= " Select * ";
    $fileSQL .= " OEOPIS ";
    $selectSQL = "SIUSER='$userProfile' and SIITEM='$scanItem'";
    require 'stmtSQLSelect.php';
    $stmtSQL .= " Limit 1 ";
    require 'stmtSQLTotalRows.php';
    $sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
    $row = db2_fetch_assoc ( $sqlResult );
    if (trim($row[SIITEM]) != $scanItem) {
		$errMsg = $scanItem . ' not found';
	} else {
    	$stkLoc=RetValue("IWITEM='{$row[SIITEM]}' and IWWHS=$row[SIWHS]", "HDIWHS", "coalesce(IWSTKL,'N')");
    	$lotItem=RetValue("IMITEM='{$row[SIITEM]}'", "HDIMST", "coalesce(IMLOT,'N')");
	    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}OrderPickingAlloc.php{$scriptVarBase}&amp;itemNumber=" . urlencode ( $scanItem ) . "&amp;itemDesc=" . urlencode ( trim ( $row[SIIMDS] ) ) . "&amp;whsNumber=" . urlencode ( trim ( $row[SIWHS] ) ) . "&amp;qtyRequired=" . urlencode ( trim ( $row[SIQTYR] ) ) . "&amp;stkLoc=" . urlencode ( trim ( $stkLoc ) ) . "&amp;lotItem=" . urlencode ( trim ( $lotItem ) ) . "&amp;firstTime=Y\"> ";
		exit ();
	}
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Chg";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
print "\n function confirmCancel(text) {return confirm(\"Confirm Cancel of Picked Quantities\")}";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
print "\n <table $contentTable> ";
print "\n     <colgroup> <col width=\"60%\"><col width=\"35%\"> ";
print "\n     <tr><td><h1>$page_title</h1></td> ";
print "\n         <td class=\"toolbar\"> ";
if ($viewAll == "Y") {
	print "\n <a href=\"$viewOpenURL\">$qlinkDftRowLrg</a>&nbsp;";
} else {
	print "\n <a href=\"$viewAllURL\">$qlinkMaxRowLrg</a>&nbsp;";
}

print "\n <a href=\"$backURL\">$previousImage</a>&nbsp;";
print "\n <a href=\"{$homeURL}{$phpPath}OrderPickingConfirmation.php{$genericVarBase}\">$nextImage</a>&nbsp;";
print "\n </td></tr></table> ";
print $hrTagAttr;

if ($orderPickingScanItem == "Y") {
    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
    print "\n <table $contentTable>";
    print "\n <tr class=\"oddrow\"> ";
    print "\n <td class=\"dsphdr\" nowrap>Item Number <input type=\"text\" name=\"scanItem\" value=\"\" size=\"15\" maxlength=\"15\" onChange=\"this.form.submit() \"><span class=\"error\">$errMsg</span>";
    print "\n </tr> ";
    print "\n <script TYPE=\"text/javascript\">";
    print "\n document.Chg.scanItem.focus();";
    print "\n </script>";
    print "\n </form>";
}

$uv_ProductClassName = "IMPCLS";
$uv_ProductInventoryTypeName = "IMITC";
$uv_ProductPartTypeName = "IMPTYP";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " OEOPIS ";
$selectSQL = "SIUSER='$userProfile' ";
if ($viewAll != "Y") {
	$selectSQL .= " and SIQTYR<>SIQTYP ";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By SISTKR,SIAILE,SISLOC,SIITEM,SIWHS ";
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );

print "<table $contentTable><tr>";
print "\n <th class=\"colhdr\">Item Number</th>";
print "\n <th class=\"colhdr\">Description</th>";
print "\n <th class=\"colhdr\">Whs</th>";
print "\n <th class=\"colhdr\">Quantity<br>Required</th>";
print "\n <th class=\"colhdr\">Quantity<br>Picked</th>";
print "\n <th class=\"colhdr\" colspan=\"3\">Stock Location</th>";
print "\n <th class=\"colhdr\">Quantity<br>Available</th>";
print "\n </tr>";

$rowCount = 0;
while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
	if ($rowCount >= $dspMaxRows) {
		break;
	}
	$stkLoc=RetValue("IWITEM='{$row[SIITEM]}' and IWWHS=$row[SIWHS]", "HDIWHS", "coalesce(IWSTKL,'N')");
	$lotItem=RetValue("IMITEM='{$row[SIITEM]}'", "HDIMST", "coalesce(IMLOT,'N')");
	
	$cmtCnt=RetValue("IXITEM='{$row[SIITEM]}' and IXDOCT = 'PIC'", "HDIMXD", "count(*)");
	if ($cmtCnt == 0) {
	   $cmtCnt=RetValue("ORUSER='{$userProfile}' and ODITEM='{$row[SIITEM]}' and OCDOCT = 'PIC'", "OEOPOR inner join OEORDP on ORTURN=IDTURN inner join OEORDT on ODORD#=IDORD# and ODORL#=IDORL# and ODBLN#=IDBLN# and ODITEM='{$row[SIITEM]}' and ODWH={$row[SIWHS]} inner join OEOCMT on OCORD#=IDORD# and (OCORL#=IDORL# and OCBLN#=IDBLN# or OCORL#=000 or OCORL#=999)", "count(*)");
	}
	            
	require 'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}OrderPickingAlloc.php{$scriptVarBase}&amp;itemNumber=". urlencode(trim($row[SIITEM])) . "&amp;itemDesc=" . urlencode(trim($row[SIIMDS])) . "&amp;whsNumber=" . urlencode(trim($row[SIWHS])) . "&amp;qtyRequired=" . urlencode(trim($row[SIQTYR])) . "&amp;stkLoc=" . urlencode(trim($stkLoc)) . "&amp;lotItem=" . urlencode(trim($lotItem)) . "&amp;firstTime=Y\">$row[SIITEM]</a></td>";
	$descClass =  ($cmtCnt > 0) ? 'colvcat' : 'colalph';
	print "\n     <td class=\"$descClass\">$row[SIIMDS]</td> ";
	print "\n     <td class=\"colnmbr\">$row[SIWHS]</td>";
	$qtyClass =  ($row [SIQTYP] < $row [SIQTYR]) ? 'oepriceover' : 'colnmbr';
	$F_QTYR = Format_Nbr ( $row [SIQTYR], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"$qtyClass\">$F_QTYR</td> ";
	$qtyClass =  ($row [SIQTYP] > $row [SIQTYR]) ? 'oepriceover' : 'colnmbr';
	$F_QTYP = Format_Nbr ( $row [SIQTYP], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"$qtyClass\">$F_QTYP</td> ";
	print "\n     <td class=\"colalph\">$row[SISTKR]</td>";
	print "\n     <td class=\"colalph\">$row[SIAILE]</td>";
	print "\n     <td class=\"colalph\">$row[SISLOC]</td>";
	$F_QTYA = Format_Nbr ( $row [SIQTYA], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"colnmbr\">$F_QTYA</td> ";
	print "\n </tr>";
	$startRow ++;
	$rowCount ++;
}
if ($rowCount == 0) {
	require 'NoRecordsFound.php';
}
print "</table>";

print "$hrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
print "\n </body> \n </html>";

/**
 * Update Order Picking Summary Quantity Available
 *
 */
function updateStockLoc() {
	global $i5Connect, $userProfile, $CILTUS, $CISTKL;
	
	$stmtSQL = " Update OEOPIS a Set SIQTYP=(Select coalesce(sum(QSQTY),0) From OEOPQS Where QSUSER=a.SIUSER and QSITEM=a.SIITEM and QSWHS=a.SIWHS)
	Where  SIUSER='{$userProfile}'";
	$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	
	// Non Stock Locator or Lot Items
	$stmtSQL = "Select * From OEOPIS inner join HDIMST on SIITEM=IMITEM
	inner join HDIWHS on SIITEM=IWITEM and SIWHS=IWWHS
	Where SIUSER='$userProfile' and IMLOT='N' and IWSTKL='N'
	Order By SIITEM,SIWHS ";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$startRow = 0;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		$startRow ++;
		$stmtSQL = "Select IWOHQT-IWQHSR as AVAIL From HDIWHS Where (IWWHS,IWITEM)=({$row['SIWHS']},'{$row['SIITEM']}')";
		$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		$itemWhsRow = db2_fetch_assoc ( $result );
		$stmtSQL = " Update OEOPIS set SIQTYA={$itemWhsRow['AVAIL']}
			 		 Where SIUSER='$userProfile' and SIITEM='{$row['SIITEM']}' and SIWHS={$row['SIWHS']} ";
		$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	}
	
	// Stock Locator Items			
	if ($CISTKL == 'Y') {
		$stmtSQL = "Select * From OEOPIS inner join HDIWHS on SIITEM=IWITEM and SIWHS=IWWHS
		            Where SIUSER='$userProfile' and IWSTKL='Y'
		            Order By SIITEM,SIWHS ";
		$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
		$startRow = 0;
		while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
			$startRow ++;
			$stmtSQL = "Select ISSID#,ISSTKR,ISAILE,ISSLOC,ISQOH-ISQHSR-ISQRES-coalesce(ALLOC,0) as AVAIL,
			            ISSTKR || ISAILE || ISSLOC as ISSTKL
                        From HDSTLC Inner Join HDITSL on SLSTID=ISSID#  
                        left join (Select QSITEM,QSWHS,QSSID,QSLOT,coalesce(sum(QSQTY),0) as ALLOC 
                        From OEOPQS Where QSUSER<>'{$userProfile}' GROUP BY QSITEM,QSWHS,QSSID,QSLOT) as OEOPQS 
                        on (QSITEM,QSWHS,QSSID,QSLOT)=(ISITEM,ISWHS,ISSID#,ISLOT)      
	                    Where (ISWHS,ISITEM)=({$row['SIWHS']},'{$row['SIITEM']}') and ISQOH-ISQHSR-ISQRES-coalesce(ALLOC,0)>0
	                    Order By ISRATE,ISSTKR,ISAILE,ISSLOC
	                    Fetch First Row Only";
			$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
			$stkLoc = db2_fetch_assoc ( $result );
			if ($stkLoc ['AVAIL'] > 0) {
				$stmtSQL = " Update OEOPIS set SISID={$stkLoc['ISSID#']},SISTKR='{$stkLoc['ISSTKR']}',SIAILE='{$stkLoc['ISAILE']}',
				                               SISLOC='{$stkLoc['ISSLOC']}',SISTKL='{$stkLoc['ISSTKL']}',SIQTYA={$stkLoc['AVAIL']} 
				             Where SIUSER='$userProfile' and SIITEM='{$row['SIITEM']}' and SIWHS={$row['SIWHS']} ";
				$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
			}
		}
	}
	
	// Lot Controlled Only Items
	if ($CILTUS == 'Y') {
		$stmtSQL = "Select * From OEOPIS inner join HDIMST on SIITEM=IMITEM
		                                 inner join HDIWHS on SIITEM=IWITEM and SIWHS=IWWHS
		            Where SIUSER='$userProfile' and IMLOT<>'N' and IWSTKL='N'
		            Order By SIITEM,SIWHS ";
		$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
		$startRow = 0;
		while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
			$startRow ++;
			$stmtSQL = "Select  LTQOH - LTQAL - LTLQHR as AVAIL 
			            From HDLOT       
	                    Where (LTWH,LTITEM)=({$row['SIWHS']},'{$row['SIITEM']}') and LTQOH - LTQAL - LTLQHR>0
	                    Order By LTLT#
	                    Fetch First Row Only";
			$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
			$stkLoc = db2_fetch_assoc ( $result );
			if ($stkLoc ['AVAIL'] > 0) {
				$stmtSQL = " Update OEOPIS set SIQTYA={$stkLoc['AVAIL']} 
				             Where SIUSER='$userProfile' and SIITEM='{$row['SIITEM']}' and SIWHS={$row['SIWHS']} ";
				$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
			}
		}
	}
}
?>	
