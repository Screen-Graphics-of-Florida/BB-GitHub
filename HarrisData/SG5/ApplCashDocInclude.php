<?php
print "\n <td> ";
print "\n <div>";
print "\n <table $contentTable>";
print "\n <tr><th class=\"colhdr\">Status</th>";
print "\n     <th class=\"colhdr\">Document</th>";
print "\n     <th class=\"colhdr\">Count</th>";
print "\n     <th class=\"colhdr\">Payment</th>";
print "\n     <th class=\"colhdr\">Discount</th>";
print "\n     <th class=\"colhdr\">Variance</th>";
if ($tabID!="ERRORS" && $paymentType!="D" && $paymentType!="U" && $paymentType!="R" && $paymentType!="V") {
	print "\n     <th class=\"colhdr\">Select</th>";
	print "\n     <th class=\"colhdr\">Clear</th>";
}
print "\n </tr> ";
if ($tabID!="ERRORS" && $tabID != "REVIEW" && $tabID != "REVERSE") {
    $fromBatchDate_ISO = date ( 'Y-m-d', strtotime ( Date_CYMD_ISO ( $fromBatchDate ) . " - " . $CRDSPD . " days " ) );
	$InvBalSQLDoc  = "IVIVAM-IVNPOS-IVPPOS-(-Coalesce(PEAMT-(-PEDAMT),0))";
	$DscBalSQLDoc  = "IVDSCT-IVDSTK-Coalesce(OTH_PEDAMT,0) ";
	$DscAmtSQLDoc  = "date('$fromBatchDate_ISO') >IVDSCD Then 0 When Sign(IVIVAM)<>Sign(IVIVAM-IVNPOS-IVPPOS-(-Coalesce(PEAMT-(-PEDAMT),0))) Then 0 When Sign(IVIVAM)<>Sign($DscBalSQLDoc) Then 0 ";
	
	$colmRequests = array();
	$colmRequests[] = "count(*)";
	$colmRequests[] = "Sum(Coalesce(Case When $DscAmtSQLDoc When ABS($InvBalSQLDoc) < ABS($DscBalSQLDoc) Then $InvBalSQLDoc Else $DscBalSQLDoc End,0))";
	$colmRequests[] = "Sum(Coalesce(IVIVAM-IVNPOS-IVPPOS-(-Coalesce(PEAMT-(-PEDAMT),0))-Case When $DscAmtSQLDoc When ABS($InvBalSQLDoc) < ABS($DscBalSQLDoc) Then $InvBalSQLDoc Else $DscBalSQLDoc  End,0))";
	$sqlValues=RetValueMult($sv_selectSQL, $sv_fileSQL, $sv_withSQL, $colmRequests);
	$countDisplayed=$sqlValues[0];
	$disctDisplayed=$sqlValues[1];
	$totalDisplayed=$sqlValues[2];
	
	print "\n <tr><td class=\"dsphdr\">Displayed</td> ";
	print "\n     <td class=\"colnmbr\">&nbsp;</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($countDisplayed,0) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($totalDisplayed,2) . "</td> ";
	print "\n     <td class=\"colnmbr\">" . number_format($disctDisplayed,2) . "</td> ";
	print "\n     <td class=\"colnmbr\">&nbsp;</td> ";
	$removeVar = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
	if ($paymentType=="C")                          {print "\n     <td class=\"colcode\"><input type=\"radio\" name=\"spmtAll\" onClick=\"AddFilterARPayment('$removeVar','$sclcDefault','$sdscDefault') \" title=\"Select All Displayed\"></td> ";}
	elseif ($paymentType!="D" && $paymentType!="U") {print "\n     <td class=\"colcode\"><input type=\"radio\" name=\"spmtAll\" onClick=\"AddFilterARPayment('$removeVar') \" title=\"Select All Displayed\"></td> ";}
	if ($paymentType!="D" && $paymentType!="U") {print "\n     <td class=\"colcode\"><input type=\"radio\" name=\"rlseAll\" id=\"rlseAll\" onClick=\"DelFilterARPayment('$removeVar') \" title=\"Clear All Displayed\"></td> ";}
	print "\n </tr> ";
}
print "\n <tr><td class=\"dsphdr\">Payment</td> ";
print "\n     <td class=\"colnmbr\" >" . number_format($CECAMT,2) . "</td> ";
print "\n     <td class=\"colnmbr\"><a href=\"javascript:void+0\" onMouseOver=\"showSel('paymentSelection')\" onMouseOut=\"hideSel('paymentSelection')\"><span id=\"CASHCNT\">" . number_format($CASHCNT,0) . "</span></a></td> ";
print "\n     <td class=\"colnmbr\"><a href=\"javascript:void+0\" onMouseOver=\"showSel('paymentSelection')\" onMouseOut=\"hideSel('paymentSelection')\"><span id=\"CASHAMT\">" . number_format($CASHAMT,2) . "</span></a></td> ";
print "\n     <td class=\"colnmbr\"><a href=\"javascript:void+0\" onMouseOver=\"showSel('paymentSelection')\" onMouseOut=\"hideSel('paymentSelection')\"><span id=\"CASHDSC\">" . number_format($CASHDSC,2) . "</span></a></td> ";
print "\n     <td class=\"colnmbr\" id=\"CASHVAR\">" . number_format($CECAMT-$CASHAMT,2) . "</td> ";
print "\n </tr> ";
print "\n <tr><td class=\"dsphdr\">Other</td> ";
print "\n     <td class=\"colnmbr\" >" . number_format($CEJAMT,2) . "</td> ";
print "\n     <td class=\"colnmbr\"><a href=\"javascript:void+0\" onMouseOver=\"showSel('otherSelection')\" onMouseOut=\"hideSel('otherSelection')\"><span id=\"OTHERCNT\">" . number_format($OTHERCNT,0) . "</span></a></td> ";
print "\n     <td class=\"colnmbr\"><a href=\"javascript:void+0\" onMouseOver=\"showSel('otherSelection')\" onMouseOut=\"hideSel('otherSelection')\"><span id=\"OTHERAMT\">" . number_format($OTHERAMT,2) . "</span></a></td> ";
print "\n     <td class=\"colnmbr\"><a href=\"javascript:void+0\" onMouseOver=\"showSel('otherSelection')\" onMouseOut=\"hideSel('otherSelection')\"><span id=\"OTHERDSC\">" . number_format($OTHERDSC,2) . "</span></a></td> ";
print "\n     <td class=\"colnmbr\" id=\"OTHERVAR\">" . number_format($CEJAMT-$OTHERAMT-$OTHERDSC-$CASHDSC,2) . "</td> ";
print "\n </tr> ";
print "\n </table>";

print "\n </div>";
print "\n <div id=\"paymentSelection\" class=\"moreInfo\">{$depositInfo}</div>";
print "\n <div id=\"otherSelection\" class=\"moreInfo\">{$otherInfo}</div>";

print "\n </td> ";

?>
