<?php
require_once 'GetURLParm.php';
require_once 'SetLibraryList.php';

$reportSelType	=	(isset($_GET['reportSelType']))	?	$_GET['reportSelType']	: null;
$fromEmid		=	(isset($_GET['fromEmid']))		?	$_GET['fromEmid']		: 0;

/* Script Variables	*/
$scriptName		= "SupervisorApproval.php";
$scriptVarBase	= "{$genericVarBase}&amp;reportSelType=" . urlencode(trim($reportSelType)) . "&amp;fromEmid=" . urlencode(trim($fromEmid));
$baseURL		= "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

$backURL		= $_SESSION[$fromURL];
if ($backURL == "") {$backURL = "{$homeURL}{$phpPath}TimeReviewInqEmp.php{$scriptVarBase}";}

db2_autocommit($i5Connect, DB2_AUTOCOMMIT_OFF);
require 'stmtSQLClear.php';
$stmtSQL	.= "Update SIMLBP a Set a.LBREEC=' ', a.LBRELV=' ',a.LBRPYC=' ' ";
$stmtSQL	.= "Where (a.LBDATE,a.LBEMID,a.LBSHFT,a.LBRECS) in ";
$stmtSQL	.= "(Select BWDATE,BWEMID,BWSHFT,BWRECS ";
$stmtSQL	.= " From ETBLWK inner join SIMLBP b on BWDATE=b.LBDATE and BWEMID=b.LBEMID and BWSHFT=b.LBSHFT and BWRECS=b.LBRECS inner join ETCTRL on RRN(ETCTRL)=1 left join HDPLNT on b.LBPLT=PLPLNT ";
$stmtSQL	.= " Where BWXHND='$profileHandle' and BWEMID=$fromEmid and BWTYPE>='1' and BWTYPE<='4' and BWAORB='A' and b.LBREEC= Case When PLSUPA=' ' or PLSUPA is Null Then TASUPA Else PLSUPA End) ";
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
if (!$sqlResult) {
	db2_rollback($i5Connect);
	$confMessage = "Unable To Approve Employee Time";
} else {
	require 'stmtSQLClear.php';
	$stmtSQL	.= "Update ETBLWK a Set a.BWREEC=' ', a.BWRELV=' ',a.BWRPYC=' ' ";
	$stmtSQL	.= "Where a.BWXHND='$profileHandle' and a.BWEMID=$fromEmid and a.BWTYPE>='1' and a.BWTYPE<='4' and a.BWAORB='A' and a.BWREEC= ";
	$stmtSQL	.= "(Select coalesce(nullif(PLSUPA,' '),TASUPA) ";
	$stmtSQL	.= " From ETCTRL left join HDPLNT on a.BWPLT=PLPLNT ";
	$stmtSQL	.= " Where RRN(ETCTRL)=1) ";
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	if (!$sqlResult) {
		db2_rollback($i5Connect);
		$confMessage = "Unable To Approve Employee Time";
	} else{
		db2_commit($i5Connect);
		$confMessage = "Employee Time Has Been Approved";
	}
}

db2_autocommit($i5Connect, DB2_AUTOCOMMIT_ON);

print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . "\"> ";

?>