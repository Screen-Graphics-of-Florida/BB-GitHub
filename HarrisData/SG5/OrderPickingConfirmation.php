<?php
$addPackList = $_GET ['addPackList'];

require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "Order Picking Confirmation";
$scriptName = "OrderPickingConfirmation.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;

// Create Packing List
if ($addPackList == 'Y') {
	Create_Packing_List ();
	print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=485&amp;confMessage=Packing List has been created\"> ";
	exit ();
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Chg";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
print "\n function confirmAccept(text) {return confirm(\"Confirm Create of Packing List\")}";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
print "\n <table $contentTable> ";
print "\n     <colgroup> <col width=\"60%\"><col width=\"35%\"> ";
print "\n     <tr><td><h1>$page_title</h1></td> ";
print "\n         <td class=\"toolbar\"> ";
print "\n <a href=\"{$homeURL}{$phpPath}OrderPickingSummary.php{$genericVarBase}&amp;viewAll=Y\">$previousImage</a>&nbsp;";
print "\n <a onClick=\"return confirmAccept()\" href=\"{$homeURL}{$phpPath}OrderPickingConfirmation.php{$genericVarBase}&addPackList=Y\">$mbOrderAccept</a>";
print "\n </td></tr></table> ";
print $hrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .= " Select OEOPIS.*, coalesce(QSSTKR,'') as QSSTKR, coalesce(QSAILE,'') as QSAILE, coalesce(QSSLOC,'') as QSSLOC, 
		             coalesce(QSLOT,'') as QSLOT, coalesce(QSQTY,0) as QSQTY";
$fileSQL .= " OEOPIS left join OEOPQS on SIUSER=QSUSER and SIITEM=QSITEM and SIWHS=QSWHS ";
$selectSQL = "SIUSER='$userProfile' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By SIITEM,SIWHS,QSSTKR,QSAILE,QSSLOC,QSLOT";
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );

print "<table $contentTable><tr>";
print "\n <th class=\"colhdr\">Item Number</th>";
print "\n <th class=\"colhdr\">Description</th>";
print "\n <th class=\"colhdr\">Whs</th>";
print "\n <th class=\"colhdr\">Quantity<br>Required</th>";
print "\n <th class=\"colhdr\" colspan=\"3\">Stock Location</th>";
print "\n <th class=\"colhdr\">Lot</th>";
print "\n <th class=\"colhdr\">Quantity<br>Picked</th>";
print "\n </tr>";

$rowCount = 0;
$saveItem = NULL;
$saveWhs = NULL;
while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
	if (! $saveItem || ($saveItem != $row [SIITEM] || $saveWhs != $row [SIWHS])) {
		$saveItem = $row [SIITEM];
		$saveWhs = $row [SIWHS];
		require 'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\">$row[SIITEM]</td>";
		print "\n     <td class=\"colalph\">$row[SIIMDS]</td> ";
		print "\n     <td class=\"colnmbr\">$row[SIWHS]</td>";
		if ($row [SIQTYP] < $row [SIQTYR]) {
			$qtyClass = 'oepriceover';
		} else {
			$qtyClass = 'colnmbr';
		}
		$F_QTYR = Format_Nbr ( $row [SIQTYR], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
		print "\n <td class=\"$qtyClass\">$F_QTYR</td> ";
	} else {
		print "\n     <td class=\"colalph\" colspan=\"4\">&nbsp;</td>";
	}
	print "\n     <td class=\"colalph\">$row[QSSTKR]</td>";
	print "\n     <td class=\"colalph\">$row[QSAILE]</td>";
	print "\n     <td class=\"colalph\">$row[QSSLOC]</td>";
	print "\n     <td class=\"colalph\">$row[QSLOT]</td>";
	$F_QSQTY = Format_Nbr ( $row [QSQTY], $qtyNbrDec, $qtyEditCode, "Y", "", "" );
	print "\n <td class=\"colnmbr\">$F_QSQTY</td> ";
	print "\n </tr>";
	$startRow ++;
	$rowCount ++;
}
if ($rowCount == 0) {
	require 'NoRecordsFound.php';
}
print "</table>";

print "$hrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
print "\n </body> \n </html>";


// Create Packing List
function Create_Packing_List (){
	global $pgmLibrary, $i5Connect, $userProfile;

	$pgmCall = array(
			array("Name"=>"userProfile", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"));

	$pgm = i5_program_prepare("HOEOPP_W", $pgmCall);
	if (!$pgm) {die("<br>Create Packing List Program Prepare Failed errno=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
			"userProfile"=>$userProfile
	);

	$parmOut = array(
			"userProfile"=>"userProfile"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br> Create Packing List Program call errno=".i5_errno()." msg=".i5_errormsg() . "");}
}

?>	
