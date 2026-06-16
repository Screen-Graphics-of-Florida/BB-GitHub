<?php
require 'stmtSQLClear.php';
$appendUserView="N";  // Do not append user view security
$appendWildCard="N";  // Do not append wildCardSearch
$stmtSQL   .= " Select QDQLNKU, QDDESC, QDNROW, QDIMGN, QSSEQN, QDURLID, QDCLAS ";
$fileSQL   .= " SYQLNS a ";
$fileSQL   .= " left join SYQLND b on QDD2WN=QSD2WN and QDQLNK=QSQLNK ";
$selectSQL .= " QSROLE='$activeRole' and QDD2WNU=Upper('$scriptName') and QSSEQN<>0 ";
require 'stmtSQLSelect.php';
$stmtSQL   .= " Order By QSSEQN";
require 'stmtSQLEnd.php';

$dspMaxRows  = "99";
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
$row = db2_fetch_assoc($sqlResult);

$quicklinkSeqTable=array();
$startRow=1;
$quicklinkCount=0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	$quicklinkSeqTable[]=array("QDQLNKU"=>$row['QDQLNKU'], "QDDESC"=>$row['QDDESC'], "QDNROW"=>$row['QDNROW'], "QDIMGN"=>$row['QDIMGN'], "QSSEQN"=>$row['QSSEQN'], "QDURLID"=>$row['QDURLID'], "QDCLAS"=>$row['QDCLAS']);
	$startRow ++;
	$quicklinkCount ++;
}

?>
