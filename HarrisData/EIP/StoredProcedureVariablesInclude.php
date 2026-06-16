<?php

function Rtv_ARPYRUTable_Columns($payer) {
	global $i5Connect;
	$stmtSQL  = "Select * from ARPYRU  ";
	$stmtSQL .= "Where PUPAYR=$payer ";
	$stmtSQL .= "Order By PUFLDN For Fetch Only with NC ";
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	while ($row = db2_fetch_assoc($sqlResult)){$arCol[] = $row;}
	return $arCol;
}

function Rtv_UserDefined_Columns($fileName,$eventCode) {
	global $i5Connect;
	$stmtSQL  = "Select * from SYUDFM  ";
	$stmtSQL .= "Where UFFILN='$fileName' and (UFEVNT='$eventCode' or UFEVNT='          ') ";
	$stmtSQL .= "Order By UFFSEQ For Fetch Only with NC ";
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	while ($row = db2_fetch_assoc($sqlResult)){$udCol[] = $row;}
	return $udCol;
}


?>