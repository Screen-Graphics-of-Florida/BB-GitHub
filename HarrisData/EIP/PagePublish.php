<?php
require_once 'GetURLParm.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$tblID     = $_GET['tblID'];
$tableName = $_GET['tableName'];
$tableDesc = $_GET['tableDesc'];
$role      = $_GET['role'];
$user      = $_GET['user'];
$pagID     = $_GET['pagID'];
$page      = $_GET['page'];

$scriptVarBase  = "{$altVarBase}&amp;tableName=" . urlencode(trim($tableName)) . "&amp;tableDesc=" . urlencode(trim($tableDesc));

// Create Table / Column Level information
$xmlTableDoc = new SimpleXMLElement("<hdList></hdList>");

// Delete Page Design if it exists
$stmtSQL = "Delete From SYDSGN Where PDTBID=$tblID and PDPGID=$pagID and PDROLE='$role' and PDUSER='$user'";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

// Create XML
$stmtSQL = "
Select SYPAGH.*, SYPAGC.* From SYPAGH 
inner join SYPAGC on PHTBLN=PCTBLN and PHPAGE=PCPAGE and PHROLE=PCROLE and PHUSER=PCUSER
Where PHTBLN='$tableName' and PHPAGE='$page' and PHROLE='$role' and PHUSER='$user' 
      and (PCDPOS>0 or PCQSCH='Y' or PCFILT='Y')
Order By PCCOLN";

$savePage = "";
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
while ($row = db2_fetch_assoc($sqlResult)){

	// Page Header
	if ($savePage == ""){
		$savePage = trim($row['PHTBLN']);
		$savePageDesc = trim($row['PHPAGE']);
		$xmlTableDoc = new SimpleXMLElement("<hdList></hdList>");

		$pageDesc = $xmlTableDoc->addChild('table', '');
		$pageDesc=hd_addChild($pageDesc,'name', trim($row['PHTBLN']));
		$pageDesc=hd_addChild($pageDesc,'desc', trim($row['PHPAGE']));
		$pageDesc=hd_addChild($pageDesc,'heading1', trim($row['PHHDG1']));
		$pageDesc=hd_addChild($pageDesc,'heading1_upper', trim($row['PHHDG1U']));
		$pageDesc=hd_addChild($pageDesc,'heading2', trim($row['PHHDG2']));
		$pageDesc=hd_addChild($pageDesc,'heading2_upper', trim($row['PHHDG2U']));

		$pageControl = $xmlTableDoc->addChild('paging', '');
		$pageControl=hd_addChild($pageControl,'page_total', trim($row['PHPALW']));
		$pageControl=hd_addChild($pageControl,'page_rows', trim($row['PHPROW']));
		$pageControl=hd_addChild($pageControl,'page_select', trim($row['PHPLST']));

		$rows = $xmlTableDoc->addChild('row', '');
		$PDPGID = trim($row['PHPGID']);
		$PDHDG1 = trim($row['PHHDG1']);
		$PDHDG2 = trim($row['PHHDG2']);
	}

	// Column Definition
	$columnName = $rows->addChild('col');
	$columnName->addAttribute('id', trim($row['PCCOLN']));
	$columnName = hd_addChild($columnName, 'length', trim($row['PCDLEN']));
}

// Load Filter Checkboxes
require 'stmtSQLClear.php';
$stmtSQL   .= " Select *  ";
$fileSQL   .= " SYTBFC ";
$selectSQL .= " TVTBID=$tblID and TVPGID=$pagID ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By TVDSEQ ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$filterResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
$rowCount = 0;
if ($sql_Record_Count>0) {
	while ($row = db2_fetch_assoc($filterResult)){
		if ($rowCount == 0) {
			$rowCount++;
			$rows = $xmlTableDoc->addChild('filter', '');
		}
		$filterID = $rows->addChild('checkbox');
		$filterID->addAttribute('id', trim($row['TVCBID']));
		$filterID = hd_addChild($filterID, 'label', trim($row['TVLABL']));
		$filterID = hd_addChild($filterID, 'default', trim($row['TVDFTV']));
		$filterID = hd_addChild($filterID, 'sql', urlencode(trim($row['TVFILV'])));
		$filterID = hd_addChild($filterID, 'desc', urlencode(trim($row['TVFILD'])));
	}
}

$xmlStr = $xmlTableDoc->asXML();
$domTableDoc = new DOMDocument("1.0");
$domTableDoc->loadXML($xmlStr);
$xmlStr = str_replace('\'', '"', $xmlStr);
$stmtSQL = "Insert into SYDSGN (PDTBID, PDTBLN, PDTYPE, PDPGID, PDPAGE, PDHDG1, PDHDG2, PDROLE, PDUSER, PDDFLT, PDCRTB, PDXML)
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

$sqlResult = db2_prepare($i5Connect->getConnection (), $stmtSQL);
if ($sqlResult) {
	$PDDFLT = "";
	$PDCRTB = "Sys_Gen";
	$var = array($tblID, $tableName, "L", $PDPGID, $PDHDG1, $PDHDG2, $PDHDG1, $role, $user, $PDDFLT, $PDCRTB, $xmlStr);
	$ret = db2_execute($sqlResult, $var);
}

$confMessage=Format_ConfMsg_Desc("C", $page, "", "", "", "", "");
print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}Page.d2w/REPORT{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\">";

function hd_addChild($obj, $element, $val){
	if ($val !=''){
		$obj->addChild($element, trim($val));
	}
	return $obj;
}
?>