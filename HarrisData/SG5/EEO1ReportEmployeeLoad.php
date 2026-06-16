<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errMsg = $_GET ['errMsg'];
$fromRPID = $_GET ['fromRPID'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$page_title = "EEO-1 Report Maintenance";
$scriptName = "EEO1ReportMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;fromRPID=" . urlencode(trim($fromRPID));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

// Delete any existing EEO-1 Report Employee rows
$stmtSQL = " Delete From PEEORE Where RERPID={$fromRPID} ";
$status = db2_exec($i5Connect->getConnection(), $stmtSQL);

// Get EEO-1 Report and Establishment
$stmtSQL = " Select * From PEEORP inner join PEESTB on RPESID=ESESID Where RPRPID=$fromRPID ";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);
$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=515&fKey1=RERPID&fVal1={$row[RPRPID]}&fDsc1={trim($row[RPDESC])}";

$year = substr($row[RPFRDT], 0,4);
$fromCYMD = Date_FromISO_ToCYMD($row[RPFRDT]);
$fromBeg = substr($fromCYMD, 0, 3) . '0101';
$toCYMD = Date_FromISO_ToCYMD($row[RPTODT]);
$toEnd = substr($fromCYMD, 0, 3) . '1231';
$today = date("Y-m-d");

$stmtSQL = "Select distinct(EMEMID),EMCOMP,EMFACL,EMEMPL,EMEEO,EMLNAM,EMFNAM,EMGNDR,EPETHN, EMOVTM 
            From PRCKHS 
            inner join HREMPL on EMCOMP=EHCOMP and EMFACL=EHFACL and EMEMPL=EHEMPL 
            inner join PEEMPL on EPEMID=EMEMID
            inner join PEESLC on ELESID={$row[RPESID]} and ELCO=EMPECP and ELCODE=EMLOC
            Where EHRCCD in ('H', 'M') and EHPPDT >= {$fromCYMD} and EHPPDT <= {$toCYMD}
            Order By EMEMID";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));
$startRow = 1;
while ($data = db2_fetch_assoc($sqlResult, $startRow)) {
    $startRow++;
	
	if ($data[EMOVTM] == '6') {
		
    $stmtSQL = "Select coalesce(sum(SHHOUR),0) as THOURS 
                From PRCKHS 
                inner join HREMPL on EMCOMP=EHCOMP and EMFACL=EHFACL and EMEMPL=EHEMPL  
                inner join PRDTHS on SHPREF=EHPREF and SHHRUN='H' and SHSUMT not in ('V', 'S', 'H', 'O', 'D') 
                Where EMEMID={$data[EMEMID]} and EHCKDT >= {$fromBeg} and EHCKDT <= {$toEnd} ";
				
			} else {
					
	$stmtSQL = "Select coalesce(sum(SHHOUR),0) as THOURS 
                From PRCKHS 
                inner join HREMPL on EMCOMP=EHCOMP and EMFACL=EHFACL and EMEMPL=EHEMPL  
                inner join PRDTHS on SHPREF=EHPREF and SHHRUN='H' and SHSUMT not in ('V', 'S', 'H') 
                Where EMEMID={$data[EMEMID]} and EHCKDT >= {$fromBeg} and EHCKDT <= {$toEnd} ";
	
	}		
    
	$sqlResult2 = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $hoursRow = db2_fetch_assoc($sqlResult2);

    $stmtSQL = "Select coalesce(sum(QEQWTC),0) as TEARN 
                From PRQEMP
                inner join HREMPL on EMCOMP=QECOMP and EMFACL=QEFACL and EMEMPL=QEEMPL  
                Where EMEMID={$data[EMEMID]} and QEYEAR = {$year} ";
    $sqlResult2 = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $wagesRow = db2_fetch_assoc($sqlResult2);

    // $pbcd = RetValue("({$wagesRow[TEARN]} >= PBMINW and {$wagesRow[TEARN]} <= PBMAXW or {$wagesRow[TEARN]} > PBMINW and PBMAXW=0) and '{$today}' >= coalesce(PBEFDT,'{$today}') and '{$today}' <=coalesce(PBEXDT,'{$today}')", "PEPYBD", "PBCD");
    $pbcd = 0;

    $inclInEEO = ($wagesRow[TEARN] > 0 && $hoursRow[THOURS] > 0) ? 'Y' : 'N';
    $stmtSQL = " Insert Into PEEORE (RERPID,REJBCT,RECOMP,REFACL,REEMPL,REEMID,RELNAM,REFNAM,REGNDR,REETCT,REINCT,REPBID,REW2B1,REHRSW)";
    $stmtSQL .= " Values ({$fromRPID}, {$data[EMEEO]},{$data[EMCOMP]},{$data[EMFACL]},{$data[EMEMPL]},{$data[EMEMID]},'{$data[EMLNAM]}','{$data[EMFNAM]}','{$data[EMGNDR]}',{$data[EPETHN]}, '{$inclInEEO}',{$pbcd},{$wagesRow[TEARN]},{$hoursRow[THOURS]}) ";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
}

print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
?>