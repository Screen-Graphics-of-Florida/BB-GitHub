<?php

if ($fromType=="C") {
	require 'stmtSQLClear.php';
	$appendUserView="N";  // Do not append user view security
	$appendWildCard="N";  // Do not append wildCardSearch
	$stmtSQL .= " Select 'Customer' as HEADING, CMCNA1,CMCNA2,CMCNA3,CMCNA4,CMCCTY,CMST ,CMZIP ,CMCTRY, CMPHON, ";
	$stmtSQL .= " coalesce(CNCDES,' ') as CNCDES ";
	$fileSQL .= " HDCUST ";
	$fileSQL .= " left join HDCTRY on CNCTCD=CMCTRY ";
	$selectSQL .= " CMCUST=$fromID ";

} elseif ($fromType=="P") {
	require 'stmtSQLClear.php';
	$appendUserView="N";  // Do not append user view security
	$appendWildCard="N";  // Do not append wildCardSearch
	$stmtSQL .= " Select 'Payer' as HEADING, PYPYNM as CMCNA1,PYADR1 as CMCNA2,PYADR2 as CMCNA3,PYADR3 as CMCNA4, ";
	$stmtSQL .= " PYCITY as CMCCTY,PYST as CMST ,PYZIP as CMZIP ,PYCTRY as CMCTRY, PYPHON as CMPHON, ";
	$stmtSQL .= " coalesce(CNCDES,' ') as CNCDES ";
	$fileSQL .= " ARPYRH ";
	$fileSQL .= " left join HDCTRY on CNCTCD=PYCTRY ";
	$selectSQL .= " PYPAYR=$fromID ";
}
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

$idText=$row['HEADING'];
$idName=$row['CMCNA1'];

$payerInfo = "";
$payerInfo .= "<table $quickSearchTable>";

$F_fromID=Format_Code($fromID);
$payerInfo .= "<tr><td class=\"dsphdr\">$row[HEADING]:</td> ";
$payerInfo .= "    <td class=\"dspalph\">$row[CMCNA1] $F_fromID</td></tr> ";

$payerInfo .= "<tr><td class=\"dsphdr\">&nbsp;</td> ";
$payerInfo .= "    <td class=\"dspalph\">$row[CMCNA2]</td></tr> ";

if (trim($row['CMCNA3'])!="") {
	$payerInfo .= "<tr><td class=\"dsphdr\">&nbsp;</td> ";
	$payerInfo .= "    <td class=\"dspalph\">$row[CMCNA3]</td></tr> ";
}

if (trim($row['CMCNA4'])!="") {
	$payerInfo .= "<tr><td class=\"dsphdr\">&nbsp;</td> ";
	$payerInfo .= "    <td class=\"dspalph\">$row[CMCNA4]</td></tr> ";
}

if (trim($row['CMCCTY'])!="" || trim($row['CMST'])!="" || trim($row['CMZIP'])!="") {
	$payerInfo .= "<tr><td class=\"dsphdr\">&nbsp;</td> ";
	$payerInfo .= "    <td class=\"dspalph\">$row[CMCCTY] $row[CMST] $row[CMZIP]</td></tr> ";
}

if (trim($row['CMCTRY']) != trim($HDCTCD) && trim($row['CNCDES'])!="") {
	$payerInfo .= "<tr><td class=\"dsphdr\">&nbsp;</td> ";
	$payerInfo .= "    <td class=\"dspalph\">$row[CNCDES]</td></tr> ";
}

if ($row['CMPHON']>0) {
	$F_CMPHON=EditPhoneNumber($row['CMPHON']);
	$payerInfo .= "<tr><td class=\"dsphdr\">&nbsp;</td> ";
	$payerInfo .= "    <td class=\"dspalph\">$F_CMPHON</td></tr> ";
}

$payerInfo .= "</table> ";

?>
