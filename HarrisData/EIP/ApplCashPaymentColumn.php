<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$fromID             = $_GET['fromID'];
$fromType           = $_GET['fromType'];
$fromDocument       = $_GET['fromDocument'];
$paymentType        = $_GET['paymentType'];
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
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'ApplCashInclude.php';

$page_title    = "Application of Cash";
$scriptName    = "ApplCashPaymentColumn.php";
$scriptVarBase = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;fromType=" . urlencode(trim($fromType)) . "&amp;fromID=" . urlencode(trim($fromID)) . "&amp;fromDocument=" . urlencode(trim($fromDocument)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId)) . "&amp;paymentType=" . urlencode(trim($paymentType));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("PLSEQ","A","Display Sequence"));
$tabID         = "COLUMN";
$programName   = "HARCED";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "DEFAULT") {$columnDisplay = RtvColArray($profileHandle,$paymentType,$userProfile);}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Chg";
print "\n \n <script TYPE=\"text/javascript\">";
print "\n var optionWin;";
require_once 'AJAXRequest.js';
require_once 'Menu.js';

require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
require_once 'ShowHideSelCriteria.php';

require_once 'ApplCashPaymentEditSubCodeJava.php';
require_once 'ApplCashPaymentJava.php';

// Edit Column
print "\n function editColumn(column) { ";
print "\n   var selcfld=\"selc\"+column ; ";
print "\n   var stmt = \" Update ARPYCX \"; ";
print "\n   if (document.getElementById(selcfld).checked) { ";
print "\n     stmt += \" Set PXDSPL='Y'\"; ";
print "\n   } else { ";
print "\n     stmt += \" Set PXDSPL='N'\"; ";
print "\n   } ";
print "\n   stmt += \" Where (PXXHND,PXTYPE,PXCOLM)=('$profileHandle','$paymentType','\"+column+\"')\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   var ajaxRequest = new ajaxObject(url,editColumnResponse); ";
print "\n   ajaxRequest.update();  ";
require 'ApplCashPaymentJavaUpdatePendingInclude.php';
print "\n } ";

// Edit Column Response
print "\n function editColumnResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
print "\n } ";

// Default icon (Delete so default is used)
print "\n function dfltColumn() { ";
print "\n   var stmt = \" Delete from ARPYCX \"; ";
print "\n   stmt += \" Where (PXXHND,PXTYPE)=('$profileHandle','$paymentType')\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
//print "\n   var ajaxRequest = new ajaxObject(url); ";
//print "\n   ajaxRequest.update();  ";
print "\n } ";

// Save this as default
print "\n function saveColumn() { ";
print "\n   var stmt = \" Delete from ARPYCU \"; ";
print "\n   stmt += \" Where (PUUSER,PUTYPE)=('$userProfile','$paymentType')\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
//print "\n   var ajaxRequest = new ajaxObject(url); ";
//print "\n   ajaxRequest.update();  ";
print "\n   var stmt = \" Insert Into ARPYCU \"; ";
print "\n   stmt += \" (PUUSER,PUTYPE,PUCOLM,PUDSPL,PUTSTP,PUTSUS,PUTSPT)\"; ";
print "\n   stmt += \" Select '$userProfile',PXTYPE,PXCOLM,PXDSPL,Current_Timestamp,'$userProfile','Y'\"; ";
print "\n   stmt += \" From ARPYCX \"; ";
print "\n   stmt += \" Where (PXXHND,PXTYPE)=('$profileHandle','$paymentType') \"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
//print "\n   var ajaxRequest = new ajaxObject(url); ";
//print "\n   ajaxRequest.update();  ";
print "\n } ";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "APPLCASHPAYMENT";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

// Program Option Security
$harced_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
require 'ApplCashPaymentTabInclude.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select PLCOLM,PLDESC,Case When Coalesce(PXDSPL,' ')='Y' Then 'CHECKED' ELSE ' ' END as ROWCHECKED";
$fileSQL .= " ARPYCL ";
$fileSQL .= " left join ARPYCX on (PXXHND,PXTYPE,PXCOLM)=('$profileHandle',PLTYPE,PLCOLM) ";
$selectSQL .= "PLTYPE='$paymentType' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By PLSEQ ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=U&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\" onSubmit=\"return false;\">";
print "\n <table $contentTable> ";
print "\n   <tr>";
print "\n     <th class=\"colhdr\">Display</th>";
print "\n     <th class=\"colhdr\">Column</th>";
print "\n   </tr>";

require_once 'ApplCashPaymentJavaUpdateHiddenInclude.php';

$rowCount=0;
while ($row = db2_fetch_assoc($sqlResult)){
	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"selc{$row[PLCOLM]}\" id=\"selc{$row[PLCOLM]}\" value='S' $row[ROWCHECKED] onClick=\"editColumn('$row[PLCOLM]') \" title=\"Select Column\"></td> ";
	print "\n     <td class=\"colalph\">$row[PLDESC]</td> ";
	print "\n </tr>";
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}
print "\n </table></form>";

require 'EndTabInclude.php';
print "\n </table>";
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";

?>
