<?php

if (trim($tabID)=="") {$tabID="CUSTOMER";}

require_once 'ApplCashBatchRetInfoInclude.php';

// Batch Information Section
print "\n <table $contentTable style=\"float:left;\"> ";
Format_Header_Hover("Batch", $fromBatchNumber, $F_fromBatchDate,"batchSelection");
Format_Header("Bank", $bankName, $fromBatchBank);
print "\n </table> ";

// Page Title
print "\n <h1 style=\"float:left; margin-left:5ex; margin-right:5ex;\">$page_title</h1>";

if ($formatToPrint == ""){
	// Page Icon Section
	print "\n <div style=\"float:left;\"> ";
	print "\n <a href=\"{$homeURL}{$phpPath}ApplCashBatch.php{$scriptVarBase}\" title=\"Back Home\">$portalHomeMed</a> ";
	print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT\">$reloadImage</a> ";
	$removeVar = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;startRow=" . urlencode(trim($startRow));
	$medIcon = "Y";
	if ($tabID != "MISCELLANEOUS") {require_once 'FormatToprint.php';}
	require_once 'HelpPage.php';
	print "\n </div> ";
}

// Reset float
print "\n <br style=\"clear:both;\"> ";

// Hidden Divisions for Batch and Payer
print "\n <div id=\"batchSelection\" class=\"moreInfo\">{$batchInfo}</div>";

// Tab Section
print "\n <table $contentTable><tr> ";
print "\n <td> ";

print "\n <div id=\"header\"> ";
print "\n <ul id=\"primary\"> ";

if ($tabID == "CUSTOMER")  {print "\n <li><span>Customer</span></li> ";}
else                       {print "\n <li><a href=\"{$homeURL}{$phpPath}ApplCashCustomer.php{$scriptVarBase}&amp;tag=REPORT\" title=\"Display Customer\">Customer</a></li> ";}

if ($tabID == "PAYER") {print "\n <li><span>Payer</span></li> ";}
else                   {print "\n <li><a href=\"{$homeURL}{$phpPath}ApplCashPayer.php{$scriptVarBase}&amp;tag=REPORT\" title=\"Display Payer\">Payer</a></li> ";}

if ($tabID == "INVOICE") {print "\n <li><span>Invoice</span></li> ";}
else                     {print "\n <li><a href=\"{$homeURL}{$phpPath}ApplCashInvoice.php{$scriptVarBase}&amp;tag=REPORT\" title=\"Display Invoice\">Invoice</a></li> ";}

if ($harced_OPT['sec_01']=="Y" && ($CRBBAL=="N" || $BMBCHT=="D" && CustomerUserView($profileHandle, $dataBaseID, $CRMSCC, "N")!="N")) {
	if ($tabID == "MISCELLANEOUS") {print "\n <li><span>Miscellaneous Cash</span></li> ";}
	else                           {print "\n <li><a href=\"{$homeURL}{$phpPath}ApplCashPaymentMisc.php{$scriptVarBase}&amp;tag=REPORT\" title=\"Enter Miscellaneous Cash\">Miscellaneous Cash</a></li> ";}
}

print "\n </ul> ";
print "\n </div> ";

print "\n <div id=\"main\"> ";
print "\n <div id=\"contents\"> ";

?>
