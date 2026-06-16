<?php
require_once 'GetURLParm.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

$tblID    = (isset($_GET['tblID']))     ? $_GET['tblID']     : 0;
$tblDesc  = (isset($_GET['tblDesc']))   ? $_GET['tblDesc']   : "";
$dft_Srch = "";

// Create Table / Column Level information
$xmlTableDoc = new SimpleXMLElement("<hdDocSet></hdDocSet>");

// clear doc set table
$stmtSQL = "Delete From SYDCST Where DSTBID=$tblID ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

// Create sql to form xml
$stmtSQL = "
Select 'a_table' as group, TNTBID as tableID, ' ' as columnName, '0' as column_condition,
SYTBLN.*, SYTBLK.*, SYTBLC.*, SYTBCC.*, SYTBCR.*, SYTBLL.*, SYTBLP.*, SYTBLF.*, SYURLM.*  
from SYTBLN 

left join SYTBLK on TKTBID = 0
left join SYTBLC on TCTBID = 0 
left join SYTBCC on CCCNID = ' ' 
left join SYTBCR on CRTBID = 0 
left join SYTBLL on TLTBID = 0 
left join SYTBLP on TPTBID = 0 
left join SYTBLF on TFTBID = 0
left join SYURLM on FUID   = ' '  

Where TNTBID=$tblID
union
Select 'b_key' as group, TKTBID as tableID, char(TKKYSQ) as columnName, char(TKKYSQ) as column_condition,
SYTBLN.*, SYTBLK.*, SYTBLC.*, SYTBCC.*, SYTBCR.*, SYTBLL.*, SYTBLP.*, SYTBLF.*, SYURLM.*  
from SYTBLN 

inner join SYTBLK on TNTBID = TKTBID
left join SYTBLC on TCTBID = 0
left join SYTBCC on CCCNID = ' ' 
left join SYTBCR on CRTBID = 0 
left join SYTBLL on TLTBID = 0 
left join SYTBLP on TPTBID = 0 
left join SYTBLF on TFTBID = 0 
left join SYURLM on FUID   = ' '  

Where TNTBID=$tblID
union
Select  'c_column' as group, TCTBID as tableID, TCCOLN as columnName, '1' as column_condition,
SYTBLN.*, SYTBLK.*, SYTBLC.*, SYTBCC.*, SYTBCR.*, SYTBLL.*, SYTBLP.*, SYTBLF.*, SYURLM.*  
from SYTBLN 

inner join SYTBLC on TCTBID = TNTBID  
left join SYTBLK on TKTBID = 0
left join SYTBCC on CCCNID = ' ' 
left join SYTBCR on CRTBID = TNTBID and (TCCOLN=CRCOLN or TCCOLN=CRRLC1 or TCCOLN=CRRLC2 or TCCOLN=CRRLC3 or TCCOLN=CRRLC4 or TCCOLN=CRRLC5)
left join SYTBLL on TLTBID = 0 
left join SYTBLP on TPTBID = 0 
left join SYTBLF on TFTBID = 0 
left join SYURLM on FUID   = ' '  

Where TNTBID=$tblID
union
Select  'c_column' as group, TCTBID as tableID, TCCOLN as columnName, '2' as column_condition,
SYTBLN.*, SYTBLK.*, SYTBLC.*, SYTBCC.*, SYTBCR.*, SYTBLL.*, SYTBLP.*, SYTBLF.*, SYURLM.*  
from SYTBLN 

inner join SYTBLC on TNTBID = TCTBID
inner join SYTBCC on TCCNID = CCCNID
left join SYTBLK on TKTBID = 0
left join SYTBCR on CRTBID = 0 
left join SYTBLL on TLTBID = 0 
left join SYTBLP on TPTBID = 0 
left join SYTBLF on TFTBID = 0 
left join SYURLM on FUID   = ' '  

Where TNTBID=$tblID 
union
Select  'b_keya' as group, TFTBID as tableID, TFCOLN as columnName, '10001' as column_condition,
SYTBLN.*, SYTBLK.*, SYTBLC.*, SYTBCC.*, SYTBCR.*, SYTBLL.*, SYTBLP.*, SYTBLF.*, SYURLM.*  
from SYTBLN 

inner join SYTBLF on TNTBID = TFTBID
inner join SYTBLC on TCTBID = TNTBID and TCCOLN = TFCOLN
left join SYTBLK on TKTBID = 0
left join SYTBCR on CRTBID = 0 
left join SYTBLL on TLTBID = 0 
left join SYTBLP on TPTBID = 0
left join SYTBCC on CCCNID = ' ' 
left join SYURLM on FUID   = ' '  

Where TNTBID=$tblID
union
Select  'c_column' as group, CRTBID as tableID, CRCOLN as columnName, '3' as column_condition,
SYTBLN.*, SYTBLK.*, SYTBLC.*, SYTBCC.*, SYTBCR.*, SYTBLL.*, SYTBLP.*, SYTBLF.*, SYURLM.* 
from SYTBLN 

inner join SYTBCR on TNTBID = CRTBID
inner join SYTBLC on TCTBID = TNTBID and TCCOLN = CRCOLN
left join SYTBLK on TKTBID = 0
left join SYTBLF on TFTBID = 0 
left join SYTBLL on TLTBID = 0 
left join SYTBLP on TPTBID = 0
left join SYTBCC on CCCNID = ' ' 
left join SYURLM on FUID   = ' '  

Where TNTBID=$tblID 
union
Select  tltype as group, TLTBID as tableID, tlcoln as columnName, '0' as column_condition,
SYTBLN.*, SYTBLK.*, SYTBLC.*, SYTBCC.*, SYTBCR.*, SYTBLL.*, SYTBLP.*, SYTBLF.*, SYURLM.*

from SYTBLN 

inner join SYTBLL on TNTBID = TLTBID
left join SYURLM on TLURLID=FUID  
left join SYTBLK on TKTBID = 0
left join SYTBLF on TFTBID = 0 
left join SYTBLP on TPTBID = 0
left join SYTBCC on CCCNID = TLCNID 
left join SYTBLC on TCTBID = 0 
left join SYTBCR on CRTBID = 0 

Where TNTBID=$tblID
union
Select  'g_link' as group, TPTBID as tableID, tpcoln as columnName, '0' as column_condition,
SYTBLN.*, SYTBLK.*, SYTBLC.*, SYTBCC.*, SYTBCR.*, SYTBLL.*, SYTBLP.*, SYTBLF.*, SYURLM.*  
from SYTBLN 

inner join SYTBLL on TNTBID = TLTBID
inner join SYTBLP on TLTBID = TPTBID
left join SYTBLF on TFTBID = 0
left join SYTBLK on TKTBID = 0
left join SYTBCC on CCCNID = ' ' 
left join SYTBLC on TCTBID = 0 
left join SYTBCR on CRTBID = 0 
left join SYURLM on FUID   = ' '  

Where TNTBID=$tblID

Order By TNTBID, group, tableID, columnName, column_condition";

$saveLink = "";
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
while ($row = db2_fetch_assoc($sqlResult)){

	// Insert Doc Set with change of table
	if ($saveTableName != "" && trim($row['TNTBLN']) != $saveTableName){
		$xmlStr = $xmlTableDoc->asXML();
		$domTableDoc = new DOMDocument("1.0");
		$domTableDoc->loadXML($xmlStr);
		$xmlStr = str_replace('\'', '"', $xmlStr);
		$stmtSQL = "Insert into SYDCST (DSTBID, DSTBLN, DSTBLD, DSROLE, DSUSER, DSRSVD, DSCRTB, DSXML)
                                VALUES (?,?,?,?,?,?,?,?)";
		$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

		if ($sqlResult) {
			$DSTBID = $saveTableID;
			$DSTBLN = "$saveTableName";
			$DSTBLD = "$saveTableDesc";
			$DSUSER = "HDS";
			$DSROLE = "";
			$DSRSVD = "";
			$DSCRTB = "Sys_Gen";
			$var = array($DSTBID, $DSTBLN, $DSTBLD, $DSROLE, $DSUSER, $DSRSVD, $DSCRTB, $xmlStr);
			$ret = db2_execute($sqlResult, $var);
		}
		$saveKey = '';
		$saveLink = '';
		$saveLink = '';
		$saveCheck = ' ';
		$saveRelation = '';
	}
	// Table level
	if ($saveTableName == "" || trim($row['TNTBLN']) != $saveTableName){
		$saveTableName = trim($row['TNTBLN']);
		$saveTableDesc = trim($row['TNDESC']);
		$saveTableID = $row['TNTBID'];
		$xmlTableDoc = new SimpleXMLElement("<hdDocSet></hdDocSet>");
		$saveRow =' ';

		$tableID = $xmlTableDoc->addChild('table', '');
		$tableID=hd_addChild($tableID,'name', trim($row['TNTBLN']));
		$tableID=hd_addChild($tableID,'desc', trim($row['TNDESC']));
		$tableID=hd_addChild($tableID,'maint_script_name', trim($row['TND2WN']));
		$tableID=hd_addChild($tableID,'prog_opt_sec_prog', trim($row['TNPGOS']));
		$tableID=hd_addChild($tableID,'doc_script_name', trim($row['TNDOCS']));
		$tableID=hd_addChild($tableID,'XML_container', trim($row['TNXMLC']));
		$tableID=hd_addChild($tableID,'search_col_name', trim($row['TNCOLN']));
		$tableID=hd_addChild($tableID,'search_type', trim($row['TNSTYP']));
		$tableID=hd_addChild($tableID,'subprocedure', trim($row['TNSUBP']));
		$tableID=hd_addChild($tableID,'audit', trim($row['TNAUDT']));

		// Load Filter Checkboxes
		require 'stmtSQLClear.php';
		$stmtSQL   .= " Select * ";
		$fileSQL   .= " SYLFCB ";
		$selectSQL .= " CBTBID=$tblID and CBPGID=0 ";
		require 'stmtSQLSelect.php';
		require 'stmtSQLEnd.php';
		require 'stmtSQLTotalRows.php';
		$filterResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
		$rowCount = 0;
		if ($sql_Record_Count>0) {
			while ($filterRow = db2_fetch_assoc($filterResult)){
				if ($rowCount == 0) {
					$rowCount++;
					$rows = $xmlTableDoc->addChild('filter', '');
				}
				$filterID = $rows->addChild('checkbox');
				$filterID->addAttribute('id', trim($filterRow['CBCBID']));
				$filterID = hd_addChild($filterID, 'label', trim($filterRow['CBLABL']));
				$filterID = hd_addChild($filterID, 'default', trim($filterRow['CBDFTV']));
				$filterID = hd_addChild($filterID, 'sql', urlencode(trim($filterRow['CBFILV'])));
				$filterID = hd_addChild($filterID, 'desc', urlencode(trim($filterRow['CBFILD'])));
			}
		}
	}

	// Key
	if (trim($row['GROUP']) == 'b_key' && trim($row['COLUMN_CONDITION']) < '10000'){
		$keyArray[$keyIndex] = trim($row['TKCOLN']);
		$keyIndex++;
	}

	// Foreign Key... used for target file relation
	if (trim($row['GROUP']) == 'b_keya' && trim($row['COLUMN_CONDITION']) == '10001'){
		$keyArraya = array(trim($row['TCCOLN']) => array(trim($row['TFRFKC'])));
		$trgKeyArray[$trgKeyIndex] = trim($row['TFRFKC']);
		$trgKeyIndex++;
		$trgTable = trim($row['TFRFTB']);
	}

	// Link
	if ((trim($row['GROUP']) == 'B' || trim($row['GROUP']) == 'C' || trim($row['GROUP']) == 'D' || trim($row['GROUP']) == 'T') && (trim($row['TLLKID']) != '')){
		if ($saveLink == ''){
			$linkNameDoc = $xmlTableDoc->addChild('link', '');
			$saveLink = trim($row['TLLKID']);
		}
		$linkNameID = $linkNameDoc->addChild('linkid');
		$linkNameID->addAttribute('id', trim($row['TLLKID']));
		$linkNameID = hd_addChild($linkNameID, 'type', trim($row['TLTYPE']));
		$linkNameID = hd_addChild($linkNameID, 'display_sequence', trim($row['TLDSEQ']));
		$linkNameID = hd_addChild($linkNameID, 'column_name', trim($row['TLCOLN']));
		if (trim($row['TLCNID']) != ' ' && trim($row['CCCNID']) == trim($row['TLCNID'])){
			$linkNameID = hd_addChild($linkNameID, 'condition_id', trim($row['CCCNID']));
			$linkNameID = hd_addChild($linkNameID, 'condition_desc', trim($row['CCDESC']));
			$linkNameID = hd_addChild($linkNameID, 'condition_table', trim($row['CCFILN']));
			$linkNameID = hd_addChild($linkNameID, 'condition_criteria', urlencode(trim($row['CCCOND'])));
		}
		$linkNameID = hd_addChild($linkNameID, 'condition_sequence', trim($row['TLCSEQ']));
		$linkNameID = hd_addChild($linkNameID, 'urlid', trim($row['TLURLID']));
		$linkNameID = hd_addChild($linkNameID, 'image', trim($row['TLIMG']));
		if (trim($row['TLD2WN']) != "") {$linkNameID = hd_addChild($linkNameID, 'script_name', trim($row['TLD2WN']));}
		else {$linkNameID = hd_addChild($linkNameID, 'script_name', trim($row['TND2WN']));}
		$linkNameID = hd_addChild($linkNameID, 'pgm_opt_program_override', trim($row['TLPGOV']));
		$linkNameID = hd_addChild($linkNameID, 'pgm_opt_sequence', trim($row['TLPOPT']));
		$linkNameID = hd_addChild($linkNameID, 'imagepath', trim($row['TLIPTH']));
		$linkNameID = hd_addChild($linkNameID, 'link_table', trim($row['TLFILN']));
		$linkNameID = hd_addChild($linkNameID, 'link_criteria', trim($row['TLSELC']));
		if (trim($row['FUTITL']) == "" && strpos($row['TLD2WN'], "@@parm[TVTURL]") !== false) {$row['FUTITL'] = "Track Shipment";}
		$linkNameID = hd_addChild($linkNameID, 'link_title', trim($row['FUTITL']));
		$linkNameID = hd_addChild($linkNameID, 'link_target', trim($row['FUTRGT']));
		$linkNameID = hd_addChild($linkNameID, 'link_URL', trim($row['FUURL']));
		$linkNameID = hd_addChild($linkNameID, 'link_image', trim($row['FUIMG']));

		// Load Link Parameters
		$linkID=trim($row['TLLKID']);
		require 'stmtSQLClear.php';
		$stmtSQL   .= " Select *  ";
		$fileSQL   .= " SYTBLP ";
		$selectSQL .= " TPTBID=$tblID ";
		if (trim($row['GROUP']) == 'C' || trim($row['GROUP']) == 'D') {
			$selectSQL .= " and (TPLKID=$linkID or TPLKID=0) ";}
			else {$selectSQL .= " and TPLKID=$linkID ";}
			require 'stmtSQLSelect.php';
			require 'stmtSQLEnd.php';
			require 'stmtSQLTotalRows.php';
			$parmResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
			$linkParms = "";
			if ($sql_Record_Count>0) {
				while ($parmRow = db2_fetch_assoc($parmResult)){
					$linkParms.='&amp;'.trim($parmRow['TPPARM']);
					if (trim($parmRow['TPPARM']) != "") {
						if (trim($parmRow['TPCOLN']) != "") {
							$linkParms.='=@@parm'.trim($parmRow['TPCOLN']).'}{';
						}
						elseif (trim($parmRow['TPVALU']) != "") {
							$linkParms.='='.trim($parmRow['TPVALU']);
						}
					}
				}
			}
			$linkNameID = hd_addChild($linkNameID, 'link_parm', $linkParms);

			// Load Column Condition
			$condID=trim($row['TCCNID']);
			if ($condID != "") {
				require 'stmtSQLClear.php';
				$stmtSQL   .= " Select *  ";
				$fileSQL   .= " SYTBCC ";
				$selectSQL .= " CCCNID=$condID ";
				require 'stmtSQLSelect.php';
				require 'stmtSQLEnd.php';
				require 'stmtSQLTotalRows.php';
				$condResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
				$condRow = db2_fetch_assoc($condResult);
				if ($row) {
					$linkNameID = hd_addChild($linkNameID, 'condition_id', $condID);
					$linkNameID = hd_addChild($linkNameID, 'condition_table', trim($condRow['CCFILN']));
					$linkNameID = hd_addChild($linkNameID, 'condition_criteria', urlencode(trim($condRow['CCCOND'])));
				}
			}
	}

	// Column Definition
	if (trim($row['GROUP']) == 'c_column' && trim($row['COLUMN_CONDITION']) == '1'){

		// Add Primary Key Section
		if ($keyIndex > 0) {
			$tableKey = $xmlTableDoc->addChild('keys', '');
			//$tableKeySeq = $tableKey->addChild('key');
			//$tableKeySeq->addAttribute('id', '0');

			foreach($keyArray as $value){
				$tableKeySeqColumn = $tableKey->addChild('col');
				$tableKeySeqColumn->addAttribute('id', trim($value));
			}

			unset($keyArray);
			$keyIndex = 0;
		}

		$saveColumn = trim($row['TCCOLN']);

		if ($saveRow == ' '){
			$rows = $xmlTableDoc->addChild('row', '');
			$saveRow='x';
		}

		$columnName = $rows->addChild('col');
		$colName=trim($row['TCCOLN']);
		$columnName->addAttribute('id', $colName);
		$columnName = hd_addChild($columnName, 'label', trim($row['TCCTXT']));
		$colHdg = trim($row['TCCHDG']);
		while (strpos($colHdg, '  ')>0) {$colHdg = str_replace('  ', ' ', $colHdg);}
		$columnName = hd_addChild($columnName, 'colheading', $colHdg);
		if (trim($row['TCDTYP']) == "DECIMAL"){$row['TCDTYP'] = "NUMERIC";}
		if (trim($row['TCDTYP']) == "VARCHAR"){$row['TCDTYP'] = "CHAR";}
		$columnName = hd_addChild($columnName, 'data_type', trim(strtolower($row['TCDTYP'])));
		addFormat($columnName, trim($row['TCFTYP']), trim($row['TCDTYP']));
		$columnName = hd_addChild($columnName, 'flag_type', trim($row['TCFLGT']));
		$columnName = hd_addChild($columnName, 'length', trim($row['TCLENG']));
		$colDftSrch=trim($row['TCSDFT']);
		if (!$dft_Srch && $colDftSrch == "Y") {$dft_Srch = $colName;}

		if (trim($row['TCDTYP']) == "NUMERIC" || trim($row['TCDTYP']) == "DECIMAL"){
			if (trim($row['TCDFTV']) != 0) {
				$columnName = hd_addChild($columnName, 'default_value', trim($row['TCDFTV']));
			}
		} elseif (trim($row['TCDFTV']) != "") {
			$columnName = hd_addChild($columnName, 'default_value', trim($row['TCDFTV']));
		}
		$saveRefTable = trim($row['TCRFTB']);
		if (trim($row['TCREQF']) == "Y") {$columnName = hd_addEmpty($columnName, 'required');}
		if (trim($row['TCUPCS']) == "Y") {$columnName = hd_addEmpty($columnName, 'uppercase_only');}
		if (trim($row['CRCOLN']) == $colName || trim($row['CRRLC1']) == $colName || trim($row['CRRLC2']) == $colName || trim($row['CRRLC3']) == $colName || trim($row['CRRLC4']) == $colName || trim($row['CRRLC5']) == $colName) {
			hd_addChild($columnName, 'related_column_ID', trim($row['CRFTYP']) . "_" . trim($row['CRRLID']));
		}

		if (trim($row['TCRFTB']) != "") {
			$refTable=trim($row['TCRFTB']);
			require 'stmtSQLClear.php';
			$stmtSQL   .= " Select SYTBLF.*,TNCOLN,TCDTYP as keyType ";
			$fileSQL   .= " SYTBLF inner join SYTBLN on TFRFTB=TNTBLN ";
			$fileSQL   .= "        inner join SYTBLC on TFRFTB=TCTBLN and TFRFKC=TCCOLN";
			$selectSQL .= " TFTBID=$tblID and TFCOLN='$colName' and TFRFTB='$refTable'";
			require 'stmtSQLSelect.php';
			$stmtSQL .= " Order By TFTBID,TFCOLN,TFRTID,TFKYSQ ";
			require 'stmtSQLEnd.php';
			require 'stmtSQLTotalRows.php';
			$keysResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
			$rowCount = 0;
			$refCriteria = "";
			if ($sql_Record_Count>0) {
				while ($relRow = db2_fetch_assoc($keysResult)){
					if ($rowCount == 0) {
						$rowCount++;
						$columnName = hd_addChild($columnName, 'ref_table', $refTable);
						$columnName = hd_addChild($columnName, 'ref_column', trim($relRow['TNCOLN']));
					}
					$coldType = trim($relRow['KEYTYPE']);
					$qte="";
					if ($coldType == "CHAR") {$qte = "'";}
					if ($refCriteria != "") {$refCriteria .= " and ";}
					if (trim($relRow['TFFKCO']) != "") {$refCriteria .= trim($relRow['TFRFKC']) . "={$qte}@@parm[" . trim($relRow['TFFKCO']) . "]{$qte}";}
					elseif (trim($relRow['TFFKVL']) == "NULL") {$refCriteria .= trim($relRow['TFRFKC']) . " is null";}
					else {$refCriteria .= trim($relRow['TFRFKC']) . "={$qte}" . trim($relRow['TFFKVL']) . "{$qte}";}
				}
				$columnName = hd_addChild($columnName, 'ref_criteria', $refCriteria);
			}
		}

		if (trim($row['TCALWN']) == "Y") {$columnName = hd_addEmpty($columnName, 'allow_null');}
		if (trim($row['TCASWU']) == "Y") {$columnName = hd_addEmpty($columnName, 'allow_update');}
		if (trim($row['TCDTYP']) != "CHAR" && trim($row['TCSCAL']) != 0) {
			$columnName = hd_addChild($columnName, 'decimal', trim($row['TCSCAL']));
		}
		$columnName = hd_addChild($columnName, 'search_url', trim($row['TCSURL']));
		$columnName = hd_addChild($columnName, 'alt_sort', trim($row['TCSRTN']));
		$columnName = hd_addChild($columnName, 'user_view_col', trim($row['TCUVFN']));
		$columnName = hd_addChild($columnName, 'ccsid', trim($row['TCCCSID']));

	}

	// Column Condition - within column

	if (trim($row['GROUP']) == 'c_column' && trim($row['COLUMN_CONDITION']) == '2'){
		$columnName = hd_addChild($columnName, 'condition_id', trim($row['CCCNID']));
		$columnName = hd_addChild($columnName, 'condition_desc', trim($row['CCDESC']));
		$columnName = hd_addChild($columnName, 'condition_table', trim($row['CCFILN']));
		$columnName = hd_addChild($columnName, 'condition_criteria', urlencode(trim($row['CCCOND'])));
	}

	// Reference... used for source file relation
	if (trim($row['GROUP']) == 'c_column' && trim($row['COLUMN_CONDITION']) == '3'){

		$relColumn = (trim($row['CRCOLN']));
		$columnName = $rows->addChild('col');
		$columnName->addAttribute('id', trim($row['CRFTYP']) . "_" . trim($row['CRRLID']));
		$columnName = hd_addChild($columnName, 'label', trim($row['CRDESC']));
		$columnName = hd_addChild($columnName, 'colheading', trim($row['CRDESC']));
		$columnName = hd_addChild($columnName, 'format', trim(strtolower($row['CRFTYP'])));

		require 'stmtSQLClear.php';
		$stmtSQL   .= " Select SYTBLF.*,TNCOLN ";
		$fileSQL   .= " SYTBLF inner join sytbln on TFRFTB=TNTBLN ";
		$selectSQL .= " TFTBID=$tblID and TFCOLN='$relColumn' ";
		require 'stmtSQLSelect.php';
		$stmtSQL .= " Order By TFTBID,TFCOLN,TFRTID,TFKYSQ ";
		require 'stmtSQLEnd.php';
		require 'stmtSQLTotalRows.php';
		$keysResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
		$rowCount = 0;
		$refCriteria = "";
		if ($sql_Record_Count>0) {
			while ($relRow = db2_fetch_assoc($keysResult)){
				if ($rowCount == 0) {
					$rowCount++;
					$columnName = hd_addChild($columnName, 'related_column_1', trim($row['CRCOLN']));
					$columnName = hd_addChild($columnName, 'related_column_2', trim($row['CRRLC1']));
					$columnName = hd_addChild($columnName, 'related_column_3', trim($row['CRRLC2']));
					$columnName = hd_addChild($columnName, 'related_column_4', trim($row['CRRLC3']));
					$columnName = hd_addChild($columnName, 'related_column_5', trim($row['CRRLC4']));
					$columnName = hd_addChild($columnName, 'related_column_6', trim($row['CRRLC5']));
					$columnName = hd_addChild($columnName, 'ref_table', trim($relRow['TFRFTB']));
					$columnName = hd_addChild($columnName, 'ref_column', trim($relRow['TNCOLN']));
				}
				if ($refCriteria != "") {$refCriteria .= " and ";}
				if (trim($relRow['TFFKCO']) != "") {$refCriteria .= trim($relRow['TFRFKC']) . "={$qte}@@parm[" . trim($relRow['TFFKCO']) . "]{$qte}";}
				elseif (trim($relRow['TFFKVL']) == "NULL") {$refCriteria .= trim($relRow['TFRFKC']) . " is null";}
				else {$refCriteria .= trim($relRow['TFRFKC']) . "={$qte}" . trim($relRow['TFFKVL']) . "{$qte}";}
			}
			$columnName = hd_addChild($columnName, 'ref_criteria', $refCriteria);
		}
		else {
			$columnName = hd_addChild($columnName, 'related_column_1', trim($row['CRCOLN']));
			$columnName = hd_addChild($columnName, 'related_column_2', trim($row['CRRLC1']));
			$columnName = hd_addChild($columnName, 'related_column_3', trim($row['CRRLC2']));
			$columnName = hd_addChild($columnName, 'related_column_4', trim($row['CRRLC3']));
			$columnName = hd_addChild($columnName, 'related_column_5', trim($row['CRRLC4']));
			$columnName = hd_addChild($columnName, 'related_column_6', trim($row['CRRLC5']));
		}
	}

}

// Load Filter Checkboxes
require 'stmtSQLClear.php';
$stmtSQL   .= " Select *  ";
$fileSQL   .= " SYTBFC ";
$selectSQL .= " TVTBID=$tblID and TVPGID=0 ";
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
		if (trim($row['TVGRPN']) != "") {$filterID = hd_addChild($filterID, 'group', urlencode(trim(strtoupper($row['TVGRPN']))));}
		if (trim($row['TVDFTV']) == 1) {$filterID = hd_addEmpty($filterID, 'default');}
		$filterID = hd_addChild($filterID, 'sql', urlencode(trim($row['TVFILV'])));
		$filterID = hd_addChild($filterID, 'desc', urlencode(trim($row['TVFILD'])));
	}
}

$xmlStr = $xmlTableDoc->asXML();
$domTableDoc = new DOMDocument("1.0");
$domTableDoc->loadXML($xmlStr);
//$xmlStr = str_replace('\'', '"', $xmlStr);
$stmtSQL = "Insert into SYDCST (DSTBID, DSTBLN, DSTBLD, DSROLE, DSUSER, DSRSVD, DSCRTB, DSXML)
                                VALUES (?,?,?,?,?,?,?,?)";

$sqlResult = db2_prepare($i5Connect->getConnection (), $stmtSQL);
if ($sqlResult) {
	$DSTBID = $saveTableID;
	$DSTBLN = "$saveTableName";
	$DSTBLD = "$saveTableDesc";
	$DSUSER = "HDS";
	$DSROLE = "";
	$DSRSVD = "";
	$DSCRTB = "Sys_Gen";
	$var = array($DSTBID, $DSTBLN, $DSTBLD, $DSROLE, $DSUSER, $DSRSVD, $DSCRTB, $xmlStr);
	$ret = db2_execute($sqlResult, $var);
}

// Load Default Page Design
$pagID = ($tblID >= 5000) ? 100 : 0;
$rcdCnt = RetValue("PDTBID=$tblID and PDPGID=$pagID", "SYDSGN", "count(*)");

// Create Default Page Design if it doesn't exist.
if ($rcdCnt == 0) {
	$stmtSQL = "Delete From SYDSGN Where PDTBID=$tblID and PDPGID=$pagID";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	$xmlTableDoc = new SimpleXMLElement("<hdList></hdList>");
	$pageDesc = $xmlTableDoc->addChild('table', '');
	$desc = ($tblID < 5000) ? "HDS $saveTableDesc" : $saveTableDesc;
	$pageDesc=hd_addChild($pageDesc,'desc', $desc);
	$pageDesc=hd_addChild($pageDesc,'heading1', $saveTableDesc);
	if ($dft_Srch) {$pageDesc=hd_addChild($pageDesc,'dft_Search', $dft_Srch);}
	$pageDesc = hd_addEmpty($pageDesc, 'export');
	$pageControl = $xmlTableDoc->addChild('paging', '');
	$pageControl = hd_addEmpty($pageControl, 'select');

	// Load Columns Based On Default Sequence
	require 'stmtSQLClear.php';
	$stmtSQL   .= " Select *  ";
	$fileSQL   .= " SYTBLC ";
	$selectSQL .= " TCTBID=$tblID and TCDSEQ>0";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By TCDSEQ ";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$dftColResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	$rowCount = 0;
	if ($sql_Record_Count>0) {
		while ($row = db2_fetch_assoc($dftColResult)){
			if ($rowCount == 0) {
				$rowCount++;
				$rows = $xmlTableDoc->addChild('row', '');
			}
			$columnName = $rows->addChild('col');
			$columnName->addAttribute('id', trim($row['TCCOLN']));
		}
	}

	// Load Links
	require 'stmtSQLClear.php';
	$stmtSQL   .= " Select *  ";
	$fileSQL   .= " SYTBLL ";
	$selectSQL .= " TLTBID=$tblID";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By TLTBID,TLLKID ";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$linkResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	$rowCount = 0;
	if ($sql_Record_Count>0) {
		while ($row = db2_fetch_assoc($linkResult)){
			if ($rowCount == 0) {
				$rowCount++;
				$links = $xmlTableDoc->addChild('link', '');
			}
			$linkID = $links->addChild('linkid');
			$linkID->addAttribute('id', trim($row['TLLKID']));
		}
	}

	// Load Filter Checkboxes
	require 'stmtSQLClear.php';
	$stmtSQL   .= " Select *  ";
	$fileSQL   .= " SYTBFC ";
	$selectSQL .= " TVTBID=$tblID and TVPGID=0 ";
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
			if (trim($row['TVDFTV']) == 1) {$filterID = hd_addEmpty($filterID, 'default');}
		}
	}

	$xmlStr = $xmlTableDoc->asXML();
	$domTableDoc = new DOMDocument("1.0");
	$domTableDoc->loadXML($xmlStr);
	$xmlStr = str_replace('\'', '"', $xmlStr);
	$stmtSQL = "Insert into SYDSGN (PDTBID, PDPGID, PDTYPE, PDDESC, PDROLE, PDUSER, PDDFLT, PDCRTB, PDXML)
                                VALUES (?,?,?,?,?,?,?,?,?)";
	$sqlResult = db2_prepare($i5Connect->getConnection (), $stmtSQL);
	if ($sqlResult) {
        $PDDESC = ($tblID < 5000) ? "HDS trim($saveTableDesc)" : trim($saveTableDesc);
		$empty = "";
		$PDCRTB = "Sys_Gen";
		$var = array($tblID, $pagID, "L", $PDDESC, $empty, $empty, $empty, $PDCRTB, $xmlStr);
		$ret = db2_execute($sqlResult, $var);
	}
}
$confMessage=Format_ConfMsg_Desc("C", $tblDesc, $tblID, "", "", "", "");
$fromURL = RetValue("ERXHND='$profileHandle' and ERTYPE='U'", "SYEERR", "EREERR");
if (!$fromURL) {
	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}Table.d2w/REPORT{$altVarBase}&intHD=Y&amp;confMessage=" . urlencode(trim($confMessage)) . "\">";
} else {
	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$fromURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\">";
}

function hd_addChild($obj, $element, $val){
	if ($val !=''){$obj->addChild($element, trim($val));}
	return $obj;
}
function hd_addEmpty($obj, $element){
	$obj->addChild($element, null);
	return $obj;
}

function addFormat($columnName, $fType, $dType){
	switch ($fType){
		case "AMT":
			$columnName = hd_addChild($columnName, 'format', 'amount');
			break;

		case "EMPID":
			$columnName = hd_addChild($columnName, 'format', 'empid');
			break;

		case "PHONE":
			$columnName = hd_addChild($columnName, 'format', 'phone');
			break;

		case "CYMD":
			$columnName = hd_addChild($columnName, 'format', 'cymd');
			break;

		case "CYR":
			$columnName = hd_addChild($columnName, 'format', 'cyr');
			break;
					
		case "PERIOD":
			$columnName = hd_addChild($columnName, 'format', 'period');
			break;

		case "CODE":
			$columnName = hd_addChild($columnName, 'format', 'code');
			break;

		case "CCN":
			$columnName = hd_addChild($columnName, 'format', 'creditcard');
			break;

		case "CST":
			$columnName = hd_addChild($columnName, 'format', 'cost');
			break;

		case "HRS":
			$columnName = hd_addChild($columnName, 'format', 'hours');
			break;

		case "ISO":
			$columnName = hd_addChild($columnName, 'format', 'isodate');
			break;

		case "NOZERO":
			$columnName = hd_addChild($columnName, 'format', 'nozero');
			break;

		case "QTY":
			$columnName = hd_addChild($columnName, 'format', 'quantity');
			break;

		case "PCT":
			$columnName = hd_addChild($columnName, 'format', 'percent');
			break;

		case "PRC":
			$columnName = hd_addChild($columnName, 'format', 'price');
			break;

		case "RTE":
			$columnName = hd_addChild($columnName, 'format', 'rate');
			break;

		case "SSN":
			$columnName = hd_addChild($columnName, 'format', 'ssn');
			break;

		case "TIMEHHDD":
			$columnName = hd_addChild($columnName, 'format', 'timehhdd');
			break;

		case "TIMEHHMM":
			$columnName = hd_addChild($columnName, 'format', 'timehhmm');
			break;

		case "TIMEHHMMSS":
			$columnName = hd_addChild($columnName, 'format', 'timehhmmss');
			break;

		case "ZEROS":
			$columnName = hd_addChild($columnName, 'format', 'zeros');
			break;

		case "";
		switch ($dType){

			/*			case "CHAR":
			$columnName = hd_addChild($columnName, 'format', 'char');
			break;

			case "NUMERIC":
			$columnName = hd_addChild($columnName, 'format', 'numeric');
			break;
			*/
			case "TIMESTMP":
				$columnName = hd_addChild($columnName, 'format', 'timestamp');
				break;
				/*			case "INTEGER":
				$columnName = hd_addChild($columnName, 'format', 'integer');
				break;
				*/
			case "CLOB":
				$columnName = hd_addChild($columnName, 'format', 'textbox');
				break;

			case "VARCHAR":
				$columnName = hd_addChild($columnName, 'format', 'textbox');
				break;

			case "DEC":
				$columnName = hd_addChild($columnName, 'format', 'numeric');
				break;
		}
	}
}
?>