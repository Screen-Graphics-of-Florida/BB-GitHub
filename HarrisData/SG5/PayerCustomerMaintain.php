<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromPayer          = $_GET['fromPayer'];

$WFReview           = $_GET['WFReview'];
$wfInstance         = $_GET['wfInstance'];
$wfInstanceDate     = $_GET['wfInstanceDate'];
$wfWorkItem         = $_GET['wfWorkItem'];
$wfWorkItemSequence = $_GET['wfWorkItemSequence'];
$wfParticipantId    = $_GET['wfParticipantId'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'QuickLink.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Payer Maintenance: Customer";
$scriptName    = "PayerCustomerMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromPayer=" . urlencode(trim($fromPayer)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("CMCNA1","A","Name"),array("PCCUST","A","Customer"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$dspMaxRows = $prtMaxRows;
$maxRows = $dspMaxRows;

require 'stmtSQLClear.php';
$stmtSQL .= " Select PCCUST, ";
$stmtSQL .= " Coalesce(CMCNA1,' ') as CMCNA1, ";
$stmtSQL .= " Coalesce(CMCNA2,' ') as CMCNA2, ";
$stmtSQL .= " Coalesce(CMCCTY,' ') as CMCCTY, ";
$stmtSQL .= " Coalesce(CMST,' ') as CMST, ";
$stmtSQL .= " Coalesce(CMZIP,' ') as CMZIP, ";
$stmtSQL .= " Coalesce(CMPHON,' ') as CMPHON  ";
$fileSQL .= " ARPYRC ";
$fileSQL .= " left join HDCUST on CMCUST=PCCUST ";
$selectSQL .= "PCPAYR=$fromPayer ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Chg";

print "\n <link rel=stylesheet type=\"text/css\" href=\"{$ARApplCashStyleSheet}\"> ";
print "\n \n <script TYPE=\"text/javascript\">";
print "\n var optionWin;";
require_once 'AJAXRequest.js';
require_once 'Menu.js';

require_once 'CheckEnterAjax.php';
require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
require_once 'StringTrimJavaScript.php';

require_once 'PayerCustomerMaintainJava.php';

print "\n function confirmDelete(deleteMsg) {return confirm(\"{$delRecordConf} \" + \"\\n\" + \"\\n\" + deleteMsg);} ";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterAjax(QuickEntry)\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "PAYERCUSTOMERMAINTAIN";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

$maintenanceCode="C";

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n     <td class=\"toolbar\">";
print "\n <a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=100\" title=\"Back Home\">$portalHomeMed</a> ";
$medIcon= "Y";
require 'HelpPage.php';
print "\n </td></tr></table>";

print "\n <table $contentTable> ";
$payerName=RetValue("PYPAYR=$fromPayer ", "ARPYRH", "PYPYNM");
Format_Header("Payer", $payerName, $fromPayer);
print "\n </table> ";

if ($formatToPrint != "Y"){

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}\" onSubmit=\"return false;\">";
	print "\n <table $contentTable id=\"selTable\"> <tr>";
	Format_Column_Header(" ", "Opt") ;
	Format_Column_Header("PCCUST ", "Customer") ;
	Format_Column_Header("CMCNA1U", "Name") ;
	Format_Column_Header("CMCNA2U", "Address") ;
	Format_Column_Header("CMCCTYU", "City") ;
	Format_Column_Header("CMST   ", "State") ;
	Format_Column_Header("CMZIP  ", "Zip") ;
	Format_Column_Header("CMPHON ", "Phone") ;
	print "\n </tr>";

	// Quick Entry Row
	require  'SetRowClass.php';

	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"entry\"><a onClick=\"QuickEntry();\">$entryAcceptImage</a></td>";
	print "\n     <td class=\"entry\" nowrap><input type=\"text\" name=\"addCustomer\" id=\"addCustomer\" size=\"7\" maxlength=\"7\"> ";
	print "\n                                <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=addCustomer&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td>  ";
	print "\n     <td class=\"colalph\" colspan=\"50\"><span id=\"addCustomerError\"></span></td> ";
	print "\n </tr> ";

	// add hidden field needed for Active Responses
	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colalph\"></td> ";
	print "\n     <td class=\"colalph\" colspan=\"50\"><span id=\"quickEntryMessage\"></span></td> ";
	print "\n </tr>";

	// Customer rows
	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		$F_CMPHON=EditPhoneNumber($row['CMPHON']);
		require  'SetRowClass.php';

		print "\n <tr class=\"$rowClass\" id=\"row{$row['PCCUST']}\"> ";
		print "\n     <td class=\"inputcode\"><a onClick=\"if(confirmDelete('$row[CMCNA1]')) {delARPYRCLine('$row[PCCUST]');} \">$deleteImageSml</a></td>";
		print "\n     <td class=\"colnmbr\">$row[PCCUST]</td> ";
		print "\n     <td class=\"colalph\">$row[CMCNA1]</td> ";
		print "\n     <td class=\"colalph\">$row[CMCNA2]</td> ";
		print "\n     <td class=\"colalph\">$row[CMCCTY]</td> ";
		print "\n     <td class=\"colcode\">$row[CMST]</td> ";
		print "\n     <td class=\"colalph\">$row[CMZIP]</td> ";
		print "\n     <td class=\"colalph\">$F_CMPHON</td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	print "\n </table></form> ";

	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.addCustomer.focus();";
	print "\n \n </script>";
}
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";

?>
