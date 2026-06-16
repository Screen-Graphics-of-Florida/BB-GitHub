<?php
if ($columnDisplay['IVFRT']=="Y") {
	if ($row['IVFRT']<>0)  {print "\n <td class=\"colnmbr\">" . number_format($row['IVFRT'],2) . "</td> ";}
	else                   {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
}
if ($columnDisplay['IVSTAX']=="Y") {
	if ($row['IVSTAX']<>0) {print "\n <td class=\"colnmbr\">" . number_format($row['IVSTAX'],2) . "</td> ";}
	else                   {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
}
if ($columnDisplay['IVSPC']=="Y") {
	if ($row['IVSPC']<>0) {print "\n <td class=\"colnmbr\">" . number_format($row['IVSPC'],2) . "</td> ";}
	else                  {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
}
if ($columnDisplay['IVBLTO']=="Y" && $fromType=="P")      {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}CustomerInquiry.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "\" onclick=\"{$inquiryWinVar}\" title=\"View " . trim($row['BLTO_CMCNA1']) . " [$row[IVBLTO]] Quickview\">$row[IVBLTO]</a></td>";}
if ($columnDisplay['BLTO_CMCNA1']=="Y" && $fromType=="P") {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerInquiry.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "\" onclick=\"{$inquiryWinVar}\" title=\"View " . trim($row['BLTO_CMCNA1']) . " [$row[IVBLTO]] Quickview\">$row[BLTO_CMCNA1]</a></td>";}
if ($columnDisplay['IVTRMS']=="Y")       {print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[TMCTDS]\">$row[IVTRMS]</span></td>";}
if ($columnDisplay['TMCTDS']=="Y")       {print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[IVTRMS]\">$row[TMCTDS]</span></td>";}
if ($columnDisplay['IVDUED']=="Y")       {print "\n <td class=\"colnmbr\">$F_IVDUED</td> ";}
if ($columnDisplay['IVIVDT']=="Y") {
	if ($row['IVISEQ']==0) {print "\n <td class=\"coldate\">$F_IVIVDT</td> ";}
	else                   {print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['PEISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_IVIVDT</a></td> ";}
}
if ($columnDisplay['IVARPO']=="Y")       {print "\n <td class=\"colalph\">$row[IVARPO]</td> ";}
if ($columnDisplay['IVORD']=="Y")        {
	if     ($row['OESELECT']>0)  {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['IVORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[IVORD]</a></td> ";}
	elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['IVBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['IVAINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[IVORD]</a></td> ";}
	elseif ($row['IVORD']<>0)    {print "\n <td class=\"colnmbr\">$row[IVORD]</td> ";}
	else                         {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
}
if ($columnDisplay['IVORDT']=="Y") {
	$F_IVORDT=Format_Date($row['IVORDT'], "D");
	print "\n <td class=\"colnmbr\">$F_IVORDT</td> ";
}
if ($columnDisplay['IVORLN']=="Y")      {print "\n <td class=\"colnmbr\">$row[IVORLN]</td> ";}
if ($columnDisplay['IVPLT']=="Y")       {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[PLNAME]\">$row[IVPLT]</span></td>";}
if ($columnDisplay['PLNAME']=="Y")      {print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[IVPLT]\">$row[PLNAME]</span></td>";}
if ($columnDisplay['IVMORD']=="Y")      {print "\n <td class=\"colalph\">$row[IVMORD]</td> ";}
if ($columnDisplay['IVIVAM']=="Y")      {print "\n <td class=\"colnmbr\">" . number_format($row['IVIVAM'],2) . "</td> ";}
if ($columnDisplay['IVLOC']=="Y")       {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['IVLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location $row[LOLNA1] ($row[LOCO]/$row[LOFAC])\">$row[IVLOC]</a></td> ";}
if ($columnDisplay['LOLNA1']=="Y")      {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['IVLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location $row[IVLOC] ($row[LOCO]/$row[LOFAC])\">$row[LOLNA1]</a></td> ";}
if ($columnDisplay['IVSLSM']=="Y")      {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[SMSNA1]\">$row[IVSLSM]</span></td>";}
if ($columnDisplay['SMSNA1']=="Y")      {print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[IVSLSM]\">$row[SMSNA1]</span></td>";}
if ($columnDisplay['IVCUST']=="Y")      {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}CustomerInquiry.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVCUST'])) . "\" onclick=\"{$inquiryWinVar}\" title=\"View " . trim($row['SHTO_CMCNA1']) . " [$row[IVCUST]] Quickview\">$row[IVCUST]</a></td>";}
if ($columnDisplay['SHTO_CMCNA1']=="Y") {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerInquiry.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVCUST'])) . "\" onclick=\"{$inquiryWinVar}\" title=\"View " . trim($row['SHTO_CMCNA1']) . " [$row[IVCUST]] Quickview\">$row[SHTO_CMCNA1]</a></td>";}
if ($columnDisplay['IVPSDT']=="Y")      {print "\n <td class=\"colnmbr\">$F_IVPSDT</td> ";}
if ($columnDisplay['IVSBCD']=="Y")      {print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PSDESC]\">$row[IVSBCD]</span></td>";}
if ($columnDisplay['PSDESC']=="Y")      {print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[IVSBCD]\">$row[PSDESC]</span></td>";}

?>
