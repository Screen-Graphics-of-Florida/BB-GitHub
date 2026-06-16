<?php

// Retrieve Payment Column By User
function RtvColArray ($profileHandle,$paymentType,$userProfile) {
	global $i5Connect, $dataBaseID, $pgmLibrary;
	$recCount=RetValue("(PXXHND,PXTYPE)=('$profileHandle','$paymentType')", "ARPYCX", "count(*)");
	if ($recCount<=0) {
		$recCount=RetValue("(PUUSER,PUTYPE)=('$userProfile','$paymentType')", "ARPYCU", "count(*)");
		if ($recCount>0) {
			$stmtSQL  = " Insert Into ARPYCX ";
			$stmtSQL .= " (PXXHND,PXTYPE,PXCOLM,PXDSPL) ";
			$stmtSQL .= " Select '$profileHandle',PUTYPE,PUCOLM,PUDSPL ";
			$stmtSQL .= " From ARPYCU ";
			$stmtSQL .= " Where (PUUSER,PUTYPE)=('$userProfile','$paymentType') ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		} else {
			$stmtSQL  = " Insert Into ARPYCX ";
			$stmtSQL .= " (PXXHND,PXTYPE,PXCOLM,PXDSPL) ";
			$stmtSQL .= " Select '$profileHandle',PUTYPE,PUCOLM,PUDSPL ";
			$stmtSQL .= " From ARPYCU ";
			$stmtSQL .= " Where (PUUSER,PUTYPE)=('HDS','$paymentType') ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		}
	}

	require 'stmtSQLClear.php';
	$stmtSQL .= " Select PLCOLM,coalesce(PXDSPL,' ') as PXDSPL";
	$fileSQL .= " ARPYCL ";
	$fileSQL .= " left join ARPYCX on (PXTYPE,PXCOLM,PXXHND)=(PLTYPE,PLCOLM,'$profileHandle') ";
	$selectSQL .= " PLTYPE='$paymentType' ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	while ($row = db2_fetch_assoc($sqlResult)){
		$columnDisplay[trim($row['PLCOLM'])] = trim($row['PXDSPL']);
	}

	return $columnDisplay;
}


?>