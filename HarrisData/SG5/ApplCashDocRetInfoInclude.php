<?php

require 'stmtSQLClear.php';
$appendUserView="N";  // Do not append user view security
$appendWildCard="N";  // Do not append wildCardSearch
$stmtSQL .= " Select CECAMT,CEJAMT, ";
$stmtSQL .= "        CECICN+CEUICN+CEDGIC as CASHCNT, ";
$stmtSQL .= "        CECSAM+CEUSAM-CEDGAM as CASHAMT, ";
$stmtSQL .= "        CECDAM+CEUDAM as CASHDSC, ";
$stmtSQL .= "        CEJICN+CEYICN+CEDICN as OTHERCNT, ";
$stmtSQL .= "        CEJSAM+CEYSAM as OTHERAMT, ";
$stmtSQL .= "        CEYDAM as OTHERDSC, ";
$stmtSQL .= "        CECSAM,CECDAM,CECICN, ";
$stmtSQL .= "        CEJSAM,CEJICN, ";
$stmtSQL .= "        CEUSAM,CEUDAM,CEUICN, ";
$stmtSQL .= "        CEDGAM,CEDGIC,CEDICN,CEDSAM, ";
$stmtSQL .= "        CEYSAM,CEYDAM,CEYICN ";
$fileSQL .= " ARDCEN ";
$selectSQL .= " CEBCHN=$fromBatchNumber and CEBCHD=$fromBatchDate and CEBCHB=$fromBatchBank and CETYPE='$fromType' and CEID=$fromID and trim(CECHK)='" . trim($fromDocument) . "' ";
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

$CECAMT=$row['CECAMT'];
$CEJAMT=$row['CEJAMT'];
$CASHCNT=$row['CASHCNT'];
$CASHAMT=$row['CASHAMT'];
$CASHDSC=$row['CASHDSC'];
$OTHERCNT=$row['OTHERCNT'];
$OTHERAMT=$row['OTHERAMT'];
$OTHERDSC=$row['OTHERDSC'];
$CEDICN=$row['CEDICN'];
$CEDSAM=$row['CEDSAM'];

// Deposit
$depositInfo = "";
$depositInfo .= "<table $quickSearchTable>";
$depositInfo .= "<tr><td class=\"colhdr\">Payment Type</td> ";
$depositInfo .= "    <td class=\"colhdr\">Count</td> ";
$depositInfo .= "    <td class=\"colhdr\">Payment</td> ";
$depositInfo .= "    <td class=\"colhdr\">Discount</td> ";
$depositInfo .= " </tr> ";

$depositInfo .= " <tr><td class=\"dsphdr\">Cash</td> ";
$depositInfo .= "     <td class=\"colnmbr\" id=\"CECICN\">" . number_format($row['CECICN'],0) . "</td> ";
$depositInfo .= "     <td class=\"colnmbr\" id=\"CECSAM\">" . number_format($row['CECSAM'],2) . "</td> ";
$depositInfo .= "     <td class=\"colnmbr\" id=\"CECDAM\">" . number_format($row['CECDAM'],2) . "</td> ";
$depositInfo .= " </tr> ";

$depositInfo .= " <tr><td class=\"dsphdr\">Unapplied Cash</td> ";
$depositInfo .= "     <td class=\"colnmbr\" id=\"CEUICN\">" . number_format($row['CEUICN'],0) . "</td> ";
$depositInfo .= "     <td class=\"colnmbr\" id=\"CEUSAM\">" . number_format($row['CEUSAM'],2) . "</td> ";
$depositInfo .= "     <td class=\"colnmbr\" id=\"CEUDAM\">" . number_format($row['CEUDAM'],2) . "</td> ";
$depositInfo .= " </tr> ";

$depositInfo .= " <tr><td class=\"dsphdr\">Less General Deduction</td> ";
$depositInfo .= "     <td class=\"colnmbr\" id=\"CEDGIC\">" . number_format($row['CEDGIC'],0) . "</td> ";
$depositInfo .= "     <td class=\"colnmbr\" id=\"CEDGAM\">" . number_format($row['CEDGAM'],2) . "</td> ";
$depositInfo .= "     <td class=\"colnmbr\">&nbsp;</td> ";
$depositInfo .= " </tr> ";

$depositInfo .= " </table> ";

$otherInfo = "";
$otherInfo .= "<table $quickSearchTable>";
$otherInfo .= "<tr><td class=\"colhdr\">Payment Type</td> ";
$otherInfo .= "    <td class=\"colhdr\">Count</td> ";
$otherInfo .= "    <td class=\"colhdr\">Payment</td> ";
$otherInfo .= "    <td class=\"colhdr\">Discount</td> ";
$otherInfo .= " </tr> ";

$otherInfo .= " <tr> ";
$otherInfo .= "     <td class=\"dsphdr\">Adjustment</td> ";
$otherInfo .= "     <td class=\"colnmbr\" id=\"CEJICN\">" . number_format($row['CEJICN'],0) . "</td> ";
$otherInfo .= "     <td class=\"colnmbr\" id=\"CEJSAM\">" . number_format($row['CEJSAM'],2) . "</td> ";
$otherInfo .= " </tr> ";

$otherInfo .= " <tr> ";
$otherInfo .= "     <td class=\"dsphdr\">Apply Credit</td> ";
$otherInfo .= "     <td class=\"colnmbr\" id=\"CEYICN\">" . number_format($row['CEYICN'],0) . "</td> ";
$otherInfo .= "     <td class=\"colnmbr\" id=\"CEYSAM\">" . number_format($row['CEYSAM'],2) . "</td> ";
$otherInfo .= "     <td class=\"colnmbr\" id=\"CEYDAM\">" . number_format($row['CEYDAM'],2) . "</td> ";
$otherInfo .= " </tr> ";

$otherInfo .= " <tr><td class=\"dsphdr\" colspan=\"5\"><hr/></td></tr> ";

$otherInfo .= " <tr> ";
$otherInfo .= "     <td class=\"dsphdr\">Specific Deduction</td> ";
$otherInfo .= "     <td class=\"colnmbr\" id=\"CEDICN\">" . number_format($row['CEDICN'],0) . "</td> ";
$otherInfo .= "     <td class=\"colnmbr\" id=\"CEDSAM\">" . number_format($row['CEDSAM'],2) . "</td> ";
$otherInfo .= " </tr> ";

$otherInfo .= " </table> ";

?>
