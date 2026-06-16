<?php

$uv_BankName= "BMBCHB";
require 'userview.php';

require 'stmtSQLClear.php';
$appendWildCard="N";  // Do not append wildCardSearch
$stmtSQL .= " Select  BMBCHN, BMBCHD, BMBCHB, BMBCHT,";
$stmtSQL .= " BMDEPA, BMDEPE, BMDEPP, BMDEPD,";
$stmtSQL .= " BMADJT, BMADJE, BMADJP, ";
$stmtSQL .= " coalesce(BKBKNM,' ') as BKBKNM,  ";
$stmtSQL .= " coalesce(FLDESC,' ') as FLDESC";
if ($HDMCRL>0 && $CRPRMC=="Y") {
	$stmtSQL   .= " ,coalesce(BKCURT,' ') as BKCURT ";
	$stmtSQL   .= " ,coalesce(CFCURT,' ') as CFCURT ";
	$stmtSQL   .= " ,coalesce(a.CYDESC,' ') as BKCURT_CYDESC ";
	$stmtSQL   .= " ,coalesce(b.CYDESC,' ') as CFCURT_CYDESC ";
}
$fileSQL .= " ARPBCH ";
$fileSQL .= " left join HDBANK on BKBANK=BMBCHB ";
$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('ARBCHTYPE',BMBCHT) ";
if ($HDMCRL>0 && $CRPRMC=="Y") {
	$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(BKCO,BKFAC) ";
	$fileSQL .= " left join HDCTYP a on a.CYTYPE=BKCURT ";
	$fileSQL .= " left join HDCTYP b on b.CYTYPE=CFCURT ";
}
$selectSQL .= " (BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

$row = db2_fetch_assoc($sqlResult);
if (! $row) {require 'UserViewErrorInclude.php'; Exit;}

$F_fromBatchDate=Format_Date($fromBatchDate, "D");
$bankName=$row['BKBKNM'];
$BMBCHT=$row['BMBCHT'];

if ($HDMCRL>0 && $CRPRMC=="Y") {
	$BKCURT  =$row['BKCURT'];
	$CFCURT  =$row['CFCURT'];
} else {
	$BKCURT  ="";
	$CFCURT  ="";
}

$batchInfo = "";
$batchInfo .= "<table $quickSearchTable>";
$batchInfo .= "<tr><td class=\"dsphdr\">Batch</td> ";
$batchInfo .= "    <td class=\"dspnmbr\">$row[BMBCHN]</td> ";
$batchInfo .= "    <td class=\"dsphdr\">&nbsp;</td> ";
$batchInfo .= "    <td class=\"colhdr\">Total</td> ";
$batchInfo .= "    <td class=\"colhdr\">Posted</td> ";
$batchInfo .= "     <td class=\"colhdr\">Pending</td> ";
$batchInfo .= "     <td class=\"colhdr\">Variance</td> ";
$batchInfo .= " </tr> ";

$F_BMBCHD=Format_Date($row['BMBCHD'], "D");
$result=$row['BMDEPA'] - ($row['BMDEPP'] + $row['BMDEPE']);
$batchInfo .= " <tr> ";
$batchInfo .= "     <td class=\"dsphdr\">Batch Date</td> ";
$batchInfo .= "     <td class=\"dspnmbr\">$F_BMBCHD</td> ";
$batchInfo .= "     <td class=\"dsphdr\">Deposit</td> ";
$batchInfo .= "     <td class=\"colnmbr\">" . number_format($row['BMDEPA'],2) . "</td> ";
$batchInfo .= "     <td class=\"colnmbr\">" . number_format($row['BMDEPP'],2) . "</td> ";
$batchInfo .= "     <td class=\"colnmbr\" id=\"depositEntry\">" . number_format($row['BMDEPE'],2) . "</td> ";
$batchInfo .= "     <td class=\"colnmbr\" id=\"depositBalance\">" . number_format($result,2) . "</td> ";
$batchInfo .= " </tr> ";

$F_BMBCHB=Format_Code($row['BMBCHB']);
$result=$row['BMADJT'] - ($row['BMADJP'] + $row['BMADJE']);
$batchInfo .= " <tr> ";
$batchInfo .= "     <td class=\"dsphdr\">Bank</td> ";
$batchInfo .= "     <td class=\"dspnmbr\">$row[BKBKNM] $F_BMBCHB</td> ";
$batchInfo .= "     <td class=\"dsphdr\">Other</td> ";
$batchInfo .= "     <td class=\"colnmbr\">" . number_format($row['BMADJT'],2) . "</td> ";
$batchInfo .= "     <td class=\"colnmbr\">" . number_format($row['BMADJP'],2) . "</td> ";
$batchInfo .= "     <td class=\"colnmbr\" id=\"otherEntry\">" . number_format($row['BMADJE'],2) . "</td> ";
$batchInfo .= "     <td class=\"colnmbr\" id=\"otherBalance\">" . number_format($result,2) . "</td> ";
$batchInfo .= " </tr> ";

$F_BMBCHT=Format_Code($row['BMBCHT']);
$batchInfo .= " <tr> ";
$batchInfo .= "     <td class=\"dsphdr\">Type</td> ";
$batchInfo .= "     <td class=\"dspalph\">$row[FLDESC] $F_BMBCHT</td> ";
$batchInfo .= " </tr> ";

if ($HDMCRL>0 && $CRPRMC=="Y") {
	$F_BKCURT=Format_Code($row['BKCURT']);
	$batchInfo .= " <tr> ";
	$batchInfo .= "     <td class=\"dsphdr\">Foreign Currency</td> ";
	$batchInfo .= "     <td class=\"dspalph\">$row[BKCURT_CYDESC] $F_BKCURT</td> ";
	$batchInfo .= " </tr> ";

	$F_CFCURT=Format_Code($row['CFCURT']);
	$batchInfo .= " <tr> ";
	$batchInfo .= "     <td class=\"dsphdr\">Domestic Currency</td> ";
	$batchInfo .= "     <td class=\"dspalph\">$row[CFCURT_CYDESC] $F_CFCURT</td> ";
	$batchInfo .= " </tr> ";
}
$batchInfo .= " </table> ";
?>
