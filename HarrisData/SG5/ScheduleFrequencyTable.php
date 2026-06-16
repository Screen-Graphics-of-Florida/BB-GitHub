<?php
$stmtSQL   = " Select FLVALU,FLDESC From SYFLAG Where FLTYPE='SCHFREQPHP' Order By FLVALU For Fetch Only with NC ";
$dspMaxRows  = "99";
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

$schFreqTable=array();
$startRow=1;
$Count=0;
while ($row = db2_fetch_assoc($sqlResult)){
	$schFreqTable[]=array("FLVALU"=>$row['FLVALU'], "FLDESC"=>$row['FLDESC']);
	$startRow ++;
	$Count ++;
}

?>
