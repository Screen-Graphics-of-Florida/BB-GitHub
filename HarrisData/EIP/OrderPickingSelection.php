<?php
require_once 'GetURLParm.php';
$turnaround = $_GET ['turnaround'];
$orderNumber = $_GET ['orderNumber'];
$shipToNumber = $_GET ['shipToNumber'];
$dropShipNumber = $_GET ['dropShipNumber'];
$shipToName = $_GET ['shipToName'];
$maintenanceCode = $_GET ['maintenanceCode'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$backURL = $_SESSION [$fromURL];

// Select Order
if ($maintenanceCode == 'S') {

	$stmtSQL = " Select count(*) as CNT From OEOPOR 
                 Where ORTURN={$turnaround} and ORUSER<>'$userProfile'";
	$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc ( $result );
	if ($row ['CNT'] > 0) {
		$confMessage = "Order {$orderNumber} not added, no longer available.";
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	}
	
	if ($mobilePickMultShipTo == "N") {
		$stmtSQL = " Select count(*) as CNT From OEOPOR Where ORSHTO<>{$shipToNumber} and ORUSER='$userProfile' ";
		$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		$row = db2_fetch_assoc ( $result );
		if ($row ['CNT'] > 0) {
			$confMessage = "Order {$orderNumber} not added, the Ship-To-Number must match those already selected.";
			print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
			exit ();
		}
	}

	$stmtSQL = " Select count(*) as CNT From OEORDP inner join OEORDT on IDORD#=ODORD# and IDORL#=ODORL# and IDBLN#=ODBLN#
                                 left join HDKIT on KTKTIT=ODITEM
                 Where IDTURN={$turnaround} and KTKTRL in ('C','O')";
	$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc ( $result );
	if ($row ['CNT'] > 0) {
		$confMessage = "Order {$orderNumber} not added, it contains a Kit Relieved by Component or Option.";
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	}

	$stmtSQL = " Select count(*) as CNT From OEOPOR inner join OEORHP on ORTURN=IHTURN
	             Where IHORD#={$orderNumber} and IHTURN<>{$turnaround} and ORUSER='{$userProfile}'";
	$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc ( $result );
	if ($row ['CNT'] > 0) {
		$numberOfRowsAffected = $row ['CNT'];
	} else {
		$stmtSQL = " Update OEORHD set OEBUSY='B' Where OEORD#={$orderNumber} and OEBUSY='' ";
		$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		$numberOfRowsAffected = db2_num_rows ( $result );
	}
	if ($numberOfRowsAffected > 0) {
		// Update Order Picking Order
		$stmtSQL = " Insert Into OEOPOR (ORTURN,ORSHTO,ORDSHP,ORUSER,ORTSTP) ";
		$stmtSQL .= " Values ({$turnaround},{$shipToNumber},{$dropShipNumber},'{$userProfile}',CURRENT_TIMESTAMP) ";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		$confMessage = "Order {$orderNumber} selected";
	} else {
		$confMessage = "Order {$orderNumber} is busy";
	}
	
} elseif ($maintenanceCode == 'D') {
	$stmtSQL = " Select count(*) as CNT From OEOPOR inner join OEORHP on ORTURN=IHTURN
	             Where IHORD#={$orderNumber} and IHTURN<>{$turnaround} and ORUSER='{$userProfile}'";
	$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc ( $result );
	if ($row ['CNT'] > 0) {
		$numberOfRowsAffected = $row ['CNT'];
	} else {
		$stmtSQL = " Update OEORHD set OEBUSY=' ' Where OEORD#={$orderNumber} and OEBUSY='B' ";
		$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		$numberOfRowsAffected = db2_num_rows ( $result );
	}
	
	if ($numberOfRowsAffected > 0) {
		// Update Order Picking Order
		$stmtSQL = " Delete From OEOPOR Where ORTURN={$turnaround} ";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		$confMessage = "Order {$orderNumber} removed";
	}
	
} elseif ($maintenanceCode == 'X') {
	// Reset Order Busy flags
	$stmtSQL = "Update OEORHD Set OEBUSY='' Where OEORD# in (Select IHORD# From OEOPOR inner join OEORHP on ORTURN=IHTURN and ORUSER='{$userProfile}')";
	$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	
	// Delete Order Picking Orders
	$stmtSQL = " Delete From OEOPOR Where ORUSER='{$userProfile}' ";
	$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );

	$confMessage = "All orders released";
}

// Delete Order Picking Item Summary
$stmtSQL = " Delete From OEOPIS Where SIUSER='{$userProfile}'";
$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );

$selOrderCount = 0;
if ($maintenanceCode != 'X') {
	// Create Order Picking Item Summary
	$stmtSQL = " Insert Into OEOPIS (SIUSER,SIITEM,SIWHS,SIIMDS,SIQTYR) ";
	$stmtSQL .= " Select '{$userProfile}',ODITEM,ODWH,max(ODIMDS), sum(IDQOPK) as QTYREQ 
		      From OEORDP inner join OEORDT on IDORD#=ODORD# and IDORL#=ODORL# and IDBLN#=ODBLN#
              Where exists (Select * From OEOPOR Where ORTURN=IDTURN and ORUSER='{$userProfile}')
              Group By ODITEM,ODWH ";
	$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$selOrderCount = db2_num_rows ( $result );
}

// Delete Order Picking Quantities where the Item no longer exists in Order Picking Item Summary
$stmtSQL = " Delete From OEOPQS Where QSUSER='{$userProfile}' and not exists 
             (Select * From OEOPIS Where SIUSER='{$userProfile}' and SIITEM=QSITEM and SIWHS=QSWHS)";
$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );

// Update Quantity Picked
$stmtSQL = " Update OEOPIS a Set SIQTYP=(Select coalesce(sum(QSQTY),0) From OEOPQS Where QSUSER=a.SIUSER and QSITEM=a.SIITEM and QSWHS=a.SIWHS)
		     Where  SIUSER='{$userProfile}'";
$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );

if ($selOrderCount > 0 && $mobilePickMultShipTo == "N" && strpos ( $backURL, "fKey1" ) === false) {
	$backURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=485&fKey1=IHSHTO&fVal1={$shipToNumber}&fDsc1={$shipToName}";
} elseif ($selOrderCount == 0) {
	$backURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=485";
}

print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";

?>
