<?php

if ($row['PEENID']==0) {$disabled="style=\"visibility: hidden;\" " ;} else {$disabled="";}
$PEENID=$row['PEENID']; if ($PEENID==0) {$PEENID=1;}
print "\n <td class=\"inputalph\" id=\"icon{$row[PEISEQ]}_{$row[PEENID]}\" $disabled> ";

// Default icon
if ($paymentType=="U" || $paymentType=="D" || $paymentType=="J" || $paymentType=="M" ) {
	print "\n <a onClick=\"defaultARPaymentInfo('$row[IVISEQ]','$row[PEENID]','$row[MINPEENID]')\">$applCashDefaultInfoImage</a> ";
}

// Comment icon
print "\n <a onclick=\"showSel('commententry{$row[PEISEQ]}_{$row[PEENID]}')\" onMouseOver=\"showSel('commentshow{$row[PEISEQ]}_{$row[PEENID]}')\" onMouseOut=\"hideSel('commentshow{$row[PEISEQ]}_{$row[PEENID]}')\"><span  id=\"cmt{$row[PEISEQ]}_{$row[PEENID]}\"> ";
if (trim($row['PECMNT'])!="") {print "\n $commentExistImageNoTitle ";}
else                          {print "\n $commentImage ";}
print "\n </span></a> ";
print "\n <div id=\"commentshow{$row[PEISEQ]}_{$row[PEENID]}\" class=\"moreInfo\">";
print "\n     <table $contentTable> ";
print "\n         <tr><td class=\"dspalph\" id=\"oldcmt{$row['PEISEQ']}_{$row['PEENID']}\">" . trim($row[PECMNT]) . "</td></tr> ";
print "\n     </table> ";
print "\n </div>";
print "\n <div id=\"commententry{$row[PEISEQ]}_{$row[PEENID]}\" class=\"moreInfo\"> ";
print "\n     <table $contentTable> ";
print "\n         <tr><td><input type=\"text\" name=\"newcmt{$row['PEISEQ']}_{$row['PEENID']}\" id=\"newcmt{$row['PEISEQ']}_{$row['PEENID']}\" value=\"" . trim($row['PECMNT']) . "\" size=\"75\" maxlength=\"69\"></td></tr> ";
print "\n     <tr><td> ";
print "\n         <a onClick=\"AcceptCmtEntry('$row[PEISEQ]','$row[PEENID]');\">$commentAcceptImage</a> ";
print "\n         <a onClick=\"ResetCmtEntry('$row[PEISEQ]','$row[PEENID]');\">$commentResetImage</a> ";
print "\n         <a onClick=\"ClearCmtEntry('$row[PEISEQ]','$row[PEENID]');\">$commentClearImage</a> ";
print "\n         <a onClick=\"CloseCmtEntry('$row[PEISEQ]','$row[PEENID]');\">$closeImageMed</a> ";
print "\n     </td></tr></table> ";
print "\n </div>";
print "\n </td> ";

?>
