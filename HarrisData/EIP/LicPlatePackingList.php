<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$packingList = (isset ( $_GET ['packingList'] )) ? $_GET ['packingList'] : 0;
$turnaround  = (isset ( $_GET ['turnaround'] ))  ? $_GET ['turnaround'] : 0;
$shipTo      = (isset ( $_GET ['shipTo'] ))      ? $_GET ['shipTo'] : NULL;
$shipToName  = (isset ( $_GET ['shipToName'] ))  ? $_GET ['shipToName'] : NULL;
$errFound = $_GET ['errFound'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$scriptName = "LicPlatePackingList.php";
$scriptVarBase = "{$genericVarBase}&amp;turnaround=" . urlencode(trim($turnaround));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$page_title = "License Plate Packing List";

// Create Packing List
if ($tag == "ADD") {
	$edtVar= "";
	Concat_Field("@@turn", $turnaround);
	Concat_Field("@@pkln", $packingList);
	Concat_Field("@@lpop", $licPlateOnPackingList);
	Concat_Field("@@qdec", $qtyNbrDec);
	Concat_Field("@@qedt", $qtyEditCode);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HOEPKA_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	
	if ($errFound == '') {
		$turnaround=Decat_Field("@@turn", $edtVar);
		if ($packingList > 0) {
			$confMessage = "Turnaround {$turnaround} added to Packing List {$packingList} ";
		} else {
			$packingList=Decat_Field("@@pkln", $edtVar);
			$confMessage = "Packing List {$packingList} created for Turnaround {$turnaround}";
		}
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=475&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	}

// Print Packing List
} elseif ($packingList) {
	$programName = RetValue ( "DODOCT='PKL' and DOAPID='OE'", "HDDOCT", "coalesce(DOPGID,'')" );
	if ($programName != '') {
		Print_Document ( $programName, "OE", $userProfile, "BROWSER", $packingList );
		$stmtSQL = " Update OEPKLH set SDPKLP = 'Y' Where SDPKL#={$packingList} ";
		$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		$confMessage = "Confirm Print of Packing List " . $packingList;
	} else {
		$confMessage = "Packing List pring program not found in Document Type ";
	}
	print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=475&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
	exit ();
} else {
	$plCnt = RetValue ( "SDSTS='' and SDSHTO=$shipTo", "OEPKLH", "coalesce(count(*),0)" );
	/* if ($plCnt == 0) {
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$baseURL}&amp;tag=ADD\"> ";
		exit ();
	} */
}

require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

print "\n <table $contentTable>";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y") {
	print "\n <td> ";
	print "\n <a href=\"{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=475\" title=\"Back Home\">$portalHome</a> ";
	print "\n <a href=\"{$baseURL}&amp;tag=ADD\">$addImageLrg</a> ";
	print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";

print "\n <p><table $contentTable> ";
Format_Header("Ship-To", $shipToName, $shipTo);
Format_Header("Turnaround", $turnaround);
print "\n </table> ";

if ($plCnt > 0) {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select SDPKL# as SDPKL,SDSVDS,SDPKLP ";
	$fileSQL .= " OEPKLH ";
	$selectSQL = "SDSTS='' and SDSHTO=$shipTo ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	print "\n <p><table $contentTable> ";
	print "\n <tr><th class=\"colhdr\">Packing<br>List</th>
		          <th class=\"colhdr\">Ship Via</th>
		          <th class=\"colhdr\">Printed</th><tr>";
	$rowCount = 0;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		require 'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colnmbr\"><a href=\"{$baseURL}&amp;tag=ADD&amp;packingList={$row[SDPKL]}\" title=\"Add Turnaround {$turnaround} to Packing List {$row[SDPKL]}\">$row[SDPKL]</a></td>";
		print "\n     <td class=\"colnmbr\">$row[SDSVDS]</td>";
		print "\n     <td class=\"colcode\">$row[SDPKLP]</td>";
		print "\n </tr>";
		
		$startRow ++;
		$rowCount ++;
	}
	print "\n </table>";
	
	print "\n </td> </tr> </table>";
}
print "</body> </html>";

// Print Document
function Print_Document($programName, $apid, $user, $ws, $packingList) {
	global $pgmLibrary, $i5Connect;
	if (! $i5Connect)
		die ( "<br>Print Document Connection Failed. Error number =" . i5_errno () . " msg=" . i5_errormsg () );
	
	$pgmCall = array (array ("Name" => "packingList","IO" => I5_IN,"Type" => I5_TYPE_CHAR,"Length" => "8" ),array ("Name" => "userProfile","IO" => I5_IN,"Type" => I5_TYPE_CHAR,"Length" => "10" ),array ("Name" => "ws","IO" => I5_INOUT,"Type" => I5_TYPE_CHAR,"Length" => "10" ),array ("Name" => "apid","IO" => I5_IN,"Type" => I5_TYPE_CHAR,"Length" => "2" ) );
	
	$pgm = i5_program_prepare ( $programName, $pgmCall );
	if (! $pgm) {
		die ( "<br>Print Document prepare errno=" . i5_errno () . " msg=" . i5_errormsg () );
	}
	
	$parmIn = array ("packingList" => $packingList,"userProfile" => $user,"ws" => $ws,"apid" => $apid );
	
	$parmOut = array ();
	
	$ret = i5_program_call ( $pgm, $parmIn, $parmOut );
	if (function_exists ( 'i5_output' ))
		extract ( i5_output () );
	if (! $ret) {
		die ( "<br> Print Document call errno=" . i5_errno () . " msg=" . i5_errormsg () . "Program:$pgm In:$parmIn" );
	}
	
	return;
}

?>