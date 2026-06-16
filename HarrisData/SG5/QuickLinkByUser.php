<?php

if ($quicklinkSelected == "useDefault") {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Delete From SYQLBW Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$quicklinkLoaded= "";
} else {
	$quicklinkLoaded=trim(RetValue("QWXHND='$profileHandle' and QWD2WN='$scriptName'", "SYQLBW", "QWQSEL"));
}

require 'stmtSQLClear.php';
if ($quicklinkLoaded == "") {
	$userLinkLoaded=RetValue("QUUSER='$userProfile' and QUD2WN='$scriptName'", "SYQLBU", "QUQSEL");

	if ($userLinkLoaded == "") {
		if ($quickLinkViewAllDft == "Y") {$quicklinkSelected="viewAll";}
		else                             {$quicklinkLoaded  ="hideAll";}
		$stmtSQL .= " Insert Into SYQLBW (QWXHND,QWD2WN,QWQSEL) ";
		$stmtSQL .= " Values ('$profileHandle','$scriptName','$quicklinkLoaded') ";
	} else {
		$quicklinkLoaded = $userLinkLoaded;
		$stmtSQL .= " Insert Into SYQLBW (QWXHND,QWD2WN,QWQSEL) ";
		$stmtSQL .= " Select '$profileHandle',QUD2WN,QUQSEL";
		$stmtSQL .= " From SYQLBU ";
		$stmtSQL .= " Where QUUSER='$userProfile' and QUD2WN='$scriptName' ";
	}

} elseif ($quicklinkRemove != "") {
	$quicklinkLoaded=str_replace($quicklinkRemove,"",$quicklinkLoaded);
	$stmtSQL .= " Update SYQLBW Set QWQSEL='$quicklinkLoaded'";
	$stmtSQL .= " Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";

} elseif ($quicklinkSelected == "useDefault") {
	$userLinkLoaded=RetValue("QUUSER='$userProfile' and QUD2WN='$scriptName'", "SYQLBU", "QUQSEL");
	$stmtSQL .= " Delete From SYQLBW Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	require 'stmtSQLClear.php';
	$quicklinkLoaded= $userLinkLoaded;
	$stmtSQL .=  " Insert Into SYQLBW (QWXHND,QWD2WN,QWQSEL) ";
	$stmtSQL .=  " Select '$profileHandle',QUD2WN,QUQSEL ";
	$stmtSQL .=  " From SYQLBU ";
	$stmtSQL .=  " Where QUUSER='$userProfile' and QUD2WN='$scriptName' ";

} elseif ($quicklinkSelected == "saveDefault") {
	$stmtSQL .= " Delete From SYQLBU Where QUUSER='$userProfile' and QUD2WN='$scriptName' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	require 'stmtSQLClear.php';
	$stmtSQL .= " Insert Into SYQLBU (QUUSER,QUD2WN,QUQSEL) ";
	$stmtSQL .= " Select '$userProfile',QWD2WN,QWQSEL";
	$stmtSQL .= " From SYQLBW ";
	$stmtSQL .= " Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";

} elseif ($quicklinkSelected == "viewAll") {
	$quicklinkLoaded= "";
	$stmtSQL .= " Update SYQLBW Set QWQSEL='' ";
	$stmtSQL .= " Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";

} elseif ($quicklinkSelected == "hideAll") {
	$quicklinkLoaded= "hideAll";
	$stmtSQL .= " Update SYQLBW Set QWQSEL='hideAll' ";
	$stmtSQL .= " Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";

} elseif ($quicklinkSelected == "allRows") {
	if (strpos($quicklinkLoaded,"allRows") === false) {
		$quicklinkLoaded .= " allRows";
		$stmtSQL .= " Update SYQLBW Set QWQSEL='$quicklinkLoaded' ";
		$stmtSQL .= " Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";
	}

} elseif ($quicklinkSelected == "defaultRows") {
	$quicklinkLoaded=str_replace(" allRows","", $quicklinkLoaded);
	$stmtSQL .= " Update SYQLBW Set QWQSEL='$quicklinkLoaded' ";
	$stmtSQL .= " Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";

} elseif ($quicklinkSelected != "") {
	$quicklinkLoaded=str_replace($quicklinkSelected,"",$quicklinkLoaded);
	$quicklinkLoaded = trim($quicklinkSelected) . trim($quicklinkLoaded);
	$stmtSQL .= " Update SYQLBW Set QWQSEL='$quicklinkLoaded' ";
	$stmtSQL .= " Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";
}

if ($stmtSQL != "") {$status = db2_exec($i5Connect->getConnection (), $stmtSQL);}

?>