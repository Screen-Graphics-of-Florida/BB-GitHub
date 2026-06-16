<?php
ini_set ( 'display_errors', 0 );
ini_set ( 'log_errors', 0 );
ini_set ( 'report_memleaks', 0 );

if (PHP_SAPI === 'cli') {
	$Arg = explode ( "::", $argv [1] );
	$_GET ['baseVar'] = $Arg [0];
	$fromACAEINID = trim ( $Arg [1] );
} else {
	$fromACAEINID = $_GET ['fromACAEINID'];
}
require_once 'GetURLParm.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$stmtSQL = " Select * From HRACAE Where EIEINID=$fromACAEINID ";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$einRow = db2_fetch_assoc ( $sqlResult );

// Set Global Variables
$taxYear = $einRow ['EITXYR'];
$taxYear2 = substr ( $einRow ['EITXYR'], 2, 2 );
$taxYearStart = '1' . $taxYear2 . '0101';
$taxYearEnd = '1' . $taxYear2 . '1231';
$nextTaxYear = '1' . $taxYear2 + 1 . '0101';
$acaEmployed = FALSE;
$acaAssessment = FALSE;
$acaPartTime = FALSE;
$acaIneligible = FALSE;
$coveredIndividual = FALSE;
$planStartMonth = '';
$employmentStatus = array ('01' => '', '02' => '', '03' => '', '04' => '', '05' => '', '06' => '', '07' => '', '08' => '', '09' => '', '10' => '', '11' => '', '12' => '' );
$enrollmentStatus = array ('01' => '', '02' => '', '03' => '', '04' => '', '05' => '', '06' => '', '07' => '', '08' => '', '09' => '', '10' => '', '11' => '', '12' => '' );
$coverageStatus = array ('01' => '', '02' => '', '03' => '', '04' => '', '05' => '', '06' => '', '07' => '', '08' => '', '09' => '', '10' => '', '11' => '', '12' => '' );
$offerStatus = array ('01' => '', '02' => '', '03' => '', '04' => '', '05' => '', '06' => '', '07' => '', '08' => '', '09' => '', '10' => '', '11' => '', '12' => '' );
$employeeShareLowestCost = array ('01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0, '11' => 0, '12' => 0 );

$cnt_1095C_Attached = 0;
$cnt_Full_Time_ALE = array ('01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0, '11' => 0, '12' => 0 );
$cnt_Employee_ALE = array ('01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0, '11' => 0, '12' => 0 );

$backURL = "{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=481";

// Delete Cache prior to load
delete1094CCache ( $einRow );

// Get Federal Tax Defaults
$stmtSQL = " Select * From PRYDFL Where YDCOMP={$einRow['EICO']} and (YDFACL={$einRow['EIFAC']} or YDFACL=0)
	         Order By YDFACL desc
	         Fetch First Row Only";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$coFacRow = db2_fetch_assoc ( $sqlResult );

// Don't load Address Line 2 if Employer and Destination Addresses are the same
$adr2 = (trim ( $coFacRow ['YDADDR'] ) == trim ( $coFacRow ['YDADR2'] )) ? '' : trim ( $coFacRow ['YDADR2'] );

// Set Section 4980H Trans Relief - All 12 Months
$C44980TA = $einRow ['EI498001'];
$elementName = 'EI4980';
for($mm = 1; $mm <= 12; $mm ++) {
	$mm2 = substr ( sprintf ( "%'.02d\n", $mm ), 0, 2 );
	$elementMonth = $elementName . $mm2;
	if ($C44980TA != $einRow [$elementMonth]) {
		$C44980TA = '';
		break;
	}
}

// Load Affordable Care Act 1094C Cache
$stmtSQL = " Insert Into HRACC4 (C4TXYR,C4BNAM1,C4PYTIN,C4TINRT,C4ADDR1,C4ADDR2,C4CITY,C4STATE,C4PSTCD,C4ATRAN,C4AAGRP,C4QOFFM,
		                         C4QOFFTR,C44980TR,C4OFFM98,C4MC12M,C4QCOV01,C4QCOV02,C4QCOV03,C4QCOV04,C4QCOV05,C4QCOV06,C4QCOV07,
		                         C4QCOV08,C4QCOV09,C4QCOV10,C4QCOV11,C4QCOV12,C44980TA,C44980T01,C44980T02,C44980T03,C44980T04,
		                         C44980T05,C44980T06,C44980T07,C44980T08,C44980T09,C44980T10,C44980T11,C44980T12) ";
$stmtSQL .= " Values ({$einRow ['EITXYR']},'{$coFacRow ['YDNAME']}','{$einRow ['EIEIN']}','BUSINESS_TIN','{$coFacRow ['YDADDR']}','{$adr2}',
                     '{$coFacRow ['YDCITY']}','{$coFacRow ['YDSTAT']}','{$coFacRow ['YDZIP']}',{$einRow ['EIATRAN']},{$einRow ['EIAAGRP']},{$einRow ['EIQOFFM']},
                      {$einRow ['EIQOFFTR']},{$einRow ['EI4980TR']},{$einRow ['EIOFFM98']},{$einRow ['EIQOFFA']},{$einRow ['EIQOFF01']},{$einRow ['EIQOFF02']},
                      {$einRow ['EIQOFF03']},{$einRow ['EIQOFF04']},{$einRow ['EIQOFF05']},{$einRow ['EIQOFF06']},{$einRow ['EIQOFF07']},{$einRow ['EIQOFF08']},
                      {$einRow ['EIQOFF09']},{$einRow ['EIQOFF10']},{$einRow ['EIQOFF11']},{$einRow ['EIQOFF12']},
                     '{$C44980TA}','{$einRow ['EI498001']}','{$einRow ['EI498002']}','{$einRow ['EI498003']}','{$einRow ['EI498004']}','{$einRow ['EI498005']}',
                     '{$einRow ['EI498006']}','{$einRow ['EI498007']}','{$einRow ['EI498008']}','{$einRow ['EI498009']}','{$einRow ['EI498010']}','{$einRow ['EI498011']}','{$einRow ['EI498012']}') ";
$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );

// If row not added, set identity column and try again
if (! $status) {
	Check_Identity_Column ( 'HRACC4', 'C4CACHID', $stmtSQL );
}

// Get ID of Affordable Care Act 1094C Cache
$stmtSQL = " Select C4CACHID From HRACC4 Where C4TXYR={$einRow ['EITXYR']} and C4PYTIN='{$einRow['EIEIN']}' ";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$row = db2_fetch_assoc ( $sqlResult );
$aca1094CCacheId = $row ['C4CACHID'];


// Load 1095C Cache
load1095CCache ( $aca1094CCacheId, $einRow );

// Update 1094C Cache Counts
upd1094CCacheCounts ( $aca1094CCacheId );

/**
 * Load Affordable Care Act 1095 Cache
 *
 * @param integer $aca1094CCacheId Id of Affordable Care Act 1094C Cache
 * @param array $einRow Array of Affordable Care Act EIN
 */
function load1095CCache($aca1094CCacheId, array $einRow) {
	global $i5Connect, $coveredIndividual, $employmentStatus, $enrollmentStatus, $taxYearStart, $taxYearEnd, $planStartMonth;
	
	$data = array ();
	$stmtSQL = "Select * From HREMPL a
	            Where (EMACT='' and EMPRAC='' and EMPEAC='' or EMTRDT>" . $taxYearStart . ")
	            and {$einRow ['EIEIN']} = (Select YDEIN From PRYDFL Where YDCOMP=a.EMCOMP and (YDFACL=a.EMFACL or YDFACL=0)
	            Order By YDFACL desc
	            Fetch First Row Only)";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$startRow = 1;
	while ( $emp = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		$startRow ++;
		
		// If Hire Date > Tax Year End and no prior transactions, skip newly hired employees
		if ($emp ['EMHIRE'] > $taxYearEnd) {
			$stmtSQL = " Select count(*) as HISTCNT
			             From PEHIST inner join HREMPL on HICOMP=EMPECP and HIEMPL=EMPEMP
			             Where HICOMP={$emp ['EMPECP']} and HIEMPL={$emp ['EMPEMP']} and HITRDT<={$taxYearEnd} and HITRCD in ('HI','TE','RH')";
			$result = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
			$row = db2_fetch_assoc ( $result );
			if ($row ['HISTCNT'] == 0) {
				continue;
			}
		}
		
		$coveredIndividual = FALSE;
		fillEmploymentStatus ( $emp );
		fillEnrollmentStatus ( $emp );
		fillOfferStatus ( $emp, $einRow );
		
		$data ['C5CAID94'] = $aca1094CCacheId;
		$data ['C5CVIND'] = 0;
		$data ['C5EFNAM'] = checkValue ( $emp ['EMFNAM'] );
		$data ['C5EMNAM'] = $emp ['EMMIDI'];
		$data ['C5ELNAM'] = checkValue ( $emp ['EMLNAM'] );
		$data ['C5ESSOC'] = RetColValue ( "$profileHandle", "$dataBaseID", "EMEMID={$emp ['EMEMID']}", "HREMPL", "EMSSNO", "D" );
		$data ['C5ADDR1'] = $emp ['EMADR1'];
		$data ['C5ADDR2'] = $emp ['EMADR2'];
		$data ['C5CITY'] = $emp ['EMCITY'];
		$data ['C5STATE'] = $emp ['EMSTAT'];
		$data ['C5PSTCD'] = $emp ['EMZIP'];
		$data ['C5PSMO'] = (integer) $planStartMonth;
		while ( strlen ( $emp ['EMDOB'] ) < 7 ) {
			$emp ['EMDOB'] = "0{$emp ['EMDOB']}";
		}
		$einRow ['EMDOB'] = $emp ['EMDOB'];
		
		add1095CCache ( $aca1094CCacheId, $einRow, $emp, $data );
	}
}

/**
 * Determine Employee Employment Status by month for the Tax Year
 *
 * @param array $emp Array of Employee
 */
function fillEmploymentStatus(array $emp) {
	global $i5Connect, $taxYearStart, $taxYearEnd, $nextTaxYear, $employmentStatus, $acaEmployed, $acaAssessment, $acaPartTime, $acaIneligible;
	
	$hasTrans = FALSE;
	$acaEmployed = FALSE;
	$acaAssessment = FALSE;
	$acaPartTime = FALSE;
	$acaIneligible = FALSE;
	
	// Initialize Employment array
	foreach ( $employmentStatus as $month => $value ) {
		$employmentStatus [$month] = '';
	}
	
	$stmtSQL = " Select HITRDT,HITRCD,HISTAT 
		         From PEHIST inner join HREMPL on HICOMP=EMPECP and HIEMPL=EMPEMP
		         Where HICOMP={$emp ['EMPECP']} and HIEMPL={$emp ['EMPEMP']} and HITRDT<={$taxYearEnd} and 
		         (HITRCD in ('HI','TE','RH') or
		         HITRCD = 'ST' and HISTAT in ('FR','FRAE','FRAI','PR','PRAE','PRAI','LNPB','LNPE'))
		         Order By HITRDT,HITRCD ";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$startRow = 1;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		$hasTrans = TRUE;
		$startRow ++;
		switch ($row ['HITRCD']) {
			case 'HI' : // Hire
				fillBuckets ( $row ['HITRDT'], $fillCurrentMonth = FALSE, $hire = TRUE );
				$acaEmployed = TRUE;
				$acaPartTime = FALSE;
				$acaAssessment = FALSE;
				$acaIneligible = FALSE;
				break;
			case 'RH' : // Rehire
				fillBuckets ( $row ['HITRDT'], $fillCurrentMonth = FALSE, $hire = TRUE );
				$acaEmployed = TRUE;
				$acaPartTime = FALSE;
				$acaAssessment = FALSE;
				$acaIneligible = FALSE;
				break;
			case 'TE' : // Termination
				if ($row ['HITRDT'] >= $taxYearStart) {
					$date = date_create ( Date_CYMD_ISO ( $row ['HITRDT'] ) );
					$lastDay = $date->format ( 't' );
					$day = $date->format ( 'd' ); // Determine day of $date
					$fillCurrentMonth = ($day == $lastDay) ? TRUE : FALSE;
					fillBuckets ( $row ['HITRDT'], $fillCurrentMonth );
					if ($acaEmployed && ! $fillCurrentMonth) {
						$month = $date->format ( 'm' ); // Determine month of $date
						$employmentStatus [$month] = 'TE';
					}
				}
				$acaEmployed = FALSE;
				$acaPartTime = FALSE;
				$acaAssessment = FALSE;
				$acaIneligible = FALSE;
				break;
			case 'ST' : // Status Change
				$status = trim ( $row ['HISTAT'] );
				if ($status == 'LNPB') { // Limited Non-assessment Period Begins
					if ($row ['HITRDT'] >= $taxYearStart) {
						$date = date_create ( Date_CYMD_ISO ( $row ['HITRDT'] ) );
						$month = $date->format ( 'm' ); // Determine month of $date
						$employmentStatus [$month] = '';
					}
					fillBuckets ( $row ['HITRDT'] );
					$acaEmployed = TRUE;
					$acaAssessment = TRUE;
				} elseif ($status == 'FRAI') { // Full Time Ineligible
					fillBuckets ( $row ['HITRDT'], $fillCurrentMonth = FALSE );
					$acaIneligible = TRUE;
					fillBuckets ( $row ['HITRDT'], $fillCurrentMonth = TRUE );
				} elseif ($status == 'FRAE' && $acaIneligible) { // Full Time Eligible
					fillBuckets ( $row ['HITRDT'], $fillCurrentMonth = FALSE );
					$acaEmployed = TRUE;
					$acaAssessment = FALSE;
					$acaIneligible = FALSE;
				} elseif ($status == 'LNPE') { // Limited Non-assessment Period Ends
					fillBuckets ( $row ['HITRDT'], $fillCurrentMonth = TRUE );
					$acaEmployed = TRUE;
					$acaAssessment = FALSE;
				} elseif (! $acaPartTime && ($status == 'PR' || $status == 'PRAI' or $status == 'FRAI')) {
					fillBuckets ( $row ['HITRDT'], $fillCurrentMonth = FALSE );
					$acaEmployed = TRUE;
					$acaPartTime = TRUE;
					$acaAssessment = FALSE;
				} elseif ($acaPartTime && ($status == 'FR' || $status == 'FRAE' or $status == 'PRAE')) {
					fillBuckets ( $row ['HITRDT'] );
					$acaEmployed = TRUE;
					$acaPartTime = FALSE;
					$acaAssessment = FALSE;
				}
				break;
		}
	}
	
	// If no transactions, assume Hired Full Time [ACA Eligible] prior to Tax Year Start, still employed on Tax Year End
	if (! $hasTrans) {
		$acaEmployed = TRUE;
		$acaPartTime = FALSE;
		$acaAssessment = FALSE;
	}
	fillBuckets ( $nextTaxYear ); // Fill remaining empty months
}

/**
 * Fill buckets
 *
 * @param string $date Date to fill
 * @param integer $fillCurrentMonth Fill Current Month
 */
function fillBuckets($date, $fillCurrentMonth = FALSE, $hire = FALSE) {
	global $i5Connect, $employmentStatus, $taxYearStart, $taxYearEnd, $acaEmployed, $acaAssessment, $acaPartTime, $acaIneligible;
	
	if ($date < $taxYearStart) { // Date in prior year, fill no buckets
		return;
	}
	if ($date > $taxYearEnd) { // Date in next year, fill all buckets
		$m = 13;
	} else {
		$m = substr ( $date, 3, 2 ); // Determine month of $date
	}
	$day = substr ( $date, 5, 2 ); // Determine day of $date
	
	if (! $acaEmployed) {
		$code = 'NO';
	} elseif ($acaIneligible) {
		$code = 'IN';
	} elseif ($acaAssessment) {
		$code = 'AS';
	} elseif ($acaPartTime) {
		$code = 'PT';
	} else {
		$code = 'FT';
	}
	
	foreach ( $employmentStatus as $month => $value ) {
		if ($value == '') {
			if (! $fillCurrentMonth && $month == $m && $hire && $day != '01') {
				$employmentStatus [$month] = 'NH';
			} elseif (($fillCurrentMonth && $month <= $m) || (! $fillCurrentMonth && $month < $m)) {
				$employmentStatus [$month] = $code;
			}
		}
	}
}

/**
 * Determine Employee Benefit Enrollment Status by month for the Tax Year
 *
 * @param array $emp Array of Employee
 */
function fillEnrollmentStatus(array $emp) {
	global $i5Connect, $coveredIndividual, $employmentStatus, $enrollmentStatus, $coverageStatus, $taxYear, $taxYearStart, $taxYearEnd, $planStartMonth;
	$covOn1st = array ();
	// Initialize
	foreach ( $enrollmentStatus as $month => $value ) {
		$enrollmentStatus [$month] = '';
		$coverageStatus [$month] = '';
	}
	
	$stmtSQL = "Select ECDCE,ECDCT,CBCOVE.*
                ,Case When Coalesce(CVEFFD,0)=0 Then CEECEF Else CVEFFD End As STARTDATE 
                From CBCOVE inner join HREMCV on CECOMP=ECCOMP and CEFACL=ECFACL and CECOVR=ECCOVR and CEPLAN=ECPLAN
                            left join CBCOVR on CVCOMP=CECOMP AND CVFACL=CEFACL AND CVCOVR=CECOVR AND CVPLAN=CEPLAN
				Where CEPLFU<>'' and ECCOMP=" . $emp ['EMCOMP'] . " and ECFACL=" . $emp ['EMFACL'] . " and ECEMPL=" . $emp ['EMPEMP'] . " and ECSPDP=0 
				and (ECDCT=0 or  ECDCT>=" . $taxYearStart . " and ECDCT<=" . $taxYearEnd . ") and CEECEF<=" . $taxYearEnd . " and CEECEX>=" . $taxYearStart . "
				Order By ECDCE ";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$startRow = 1;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		$startRow ++;
		$row ['ECDCE'] = str_pad ( $row ['ECDCE'], 7, "0", STR_PAD_LEFT );
		$row ['ECDCT'] = str_pad ( $row ['ECDCT'], 7, "0", STR_PAD_LEFT );
		$row ['STARTDATE'] = str_pad ( $row ['STARTDATE'], 7, "0", STR_PAD_LEFT );
		if ($planStartMonth == '') {
		    $planStartMonth = substr ( $row ['STARTDATE'], 3, 2 ); // Determine Plan Start Month
		}
		foreach ( $enrollmentStatus as $month => $value ) {
			// Bypass months that already have a value
			if ($value != '') {
				continue;
			}
			$firstDayOfMonth = $taxYear . '-' . $month . '-01';
			$d = new DateTime ( $firstDayOfMonth );
			$lastDayOfMonth = $d->format ( 'Y-m-t' );
			$firstDayOfMonth = Date_FromISO_ToCYMD ( $firstDayOfMonth );
			$lastDayOfMonth = Date_FromISO_ToCYMD ( $lastDayOfMonth );
			
			if ($row ['ECDCE'] >= $firstDayOfMonth && $row ['ECDCE'] <= $lastDayOfMonth || $row ['ECDCT'] >= $firstDayOfMonth && $row ['ECDCT'] <= $lastDayOfMonth) {
				if ($row ['CEPLFU'] == '0') { // Self Funded
					$coveredIndividual = TRUE;
				}
				$coverageStatus [$month] = 'Y';
			}
			if ($covOn1st [$month] && $covOn1st [$month] == $row ['ECDCE']) {
				$enrollmentStatus [$month] = trim ( $row ['CECOVR'] ) . ':' . trim ( $row ['CEPLAN'] ) . ':' . $row ['CESEQU'] . ':' . $row ['CEPLFU'];
			}
			if ($row ['ECDCE'] > $firstDayOfMonth || ($row ['ECDCT'] > 0 && $row ['ECDCT'] < $firstDayOfMonth)) {
				continue;
			}
			// Employee Terminated prior to the last day of the month, but Benefit terminates at the end of the month so employee is covered
			if ($employmentStatus [$month] == 'TE' && $row ['ECDCT'] == $lastDayOfMonth) {
				$employmentStatus [$month] = 'FT';
				$enrollmentStatus [$month] = trim ( $row ['CECOVR'] ) . ':' . trim ( $row ['CEPLAN'] ) . ':' . $row ['CESEQU'] . ':' . $row ['CEPLFU'];
			}
			if ($lastDayOfMonth <= $row ['ECDCT'] || $row ['ECDCT'] == 0) {
				$enrollmentStatus [$month] = trim ( $row ['CECOVR'] ) . ':' . trim ( $row ['CEPLAN'] ) . ':' . $row ['CESEQU'] . ':' . $row ['CEPLFU'];
			} elseif ($firstDayOfMonth <= $row ['ECDCT']) {
				$date = date_create ( Date_CYMD_ISO ( $row ['ECDCT'] ) );
				date_add ( $date, date_interval_create_from_date_string ( '1 day' ) );
				$covOn1st [$month] = Date_FromISO_ToCYMD ( date_format ( $date, 'Y-m-d' ) );
			}
		}
	}
}

/**
 * Determine Employee Benefit Offer Status by month for the Tax Year
 *
 * @param array $emp Array of Employee
 * @param array $einRow Array of ACA EIN
 */
function fillOfferStatus(array $emp, array $einRow) {
	global $i5Connect, $employmentStatus, $enrollmentStatus, $offerStatus, $employeeShareLowestCost, $taxYear, $taxYearStart, $taxYearEnd;
	
	$fullTime = (in_array ( 'FT', $employmentStatus )) ? TRUE : FALSE;
	$covOn1st = array ();
	$mecBenefit = FALSE;
	$offerEE = array ();
	$offerFam = array ();
	$offerSpouse = array ();
	$offerSpouseCond = array ();
	$offerDep = array ();
	$offerDepCond = array ();
	$status1H = array ('NO' => '', 'NH' => '', 'AS' => '', 'TE' => '', 'IN' => '' );
	
	foreach ( $offerStatus as $month => $value ) {
		// If Employee Status is 'NO' Not an Employee, 'NH' Employee hired after 1st on month, 'AS' Limited Assessment Period, 'PT' Part Time Ineligible
		// Then default Offer Status to '1H'
		if (array_key_exists ( $employmentStatus [$month], $status1H ) || $employmentStatus [$month] == 'PT' && $fullTime) {
			$offerStatus [$month] = '1H';
		} else {
			$offerStatus [$month] = '';
		}
		// Initialize
		$offerEE [$month] = FALSE;
		$offerFam [$month] = FALSE;
		$offerSpouse [$month] = FALSE;
		$offerSpouseCond [$month] = FALSE;
		$offerDep [$month] = FALSE;
	    $offerDepCond [$month] = FALSE;
		$employeeShareLowestCost [$month] = 0;
	}
	
	// If Employee assigned to a Group, check Benefit Groups for available plans
	if (trim ( $emp ['EMBGRP'] ) != '') {
		$stmtSQL = "Select CBCOVE.* From CBCOVE inner join HRBGRD on CECOMP=BDCOMP and CEFACL=BDFACL and CECOVR=BDCOVR and CEPLAN=BDPLAN
				Where CEPLFU<>'' and BDCOMP=" . $emp ['EMCOMP'] . " and BDFACL=" . $emp ['EMFACL'] . " and BDBGRP='" . $emp ['EMBGRP'] . "'
				and CEECEF<=" . $taxYearEnd . " and CEECEX>=" . $taxYearStart;
	} else {
		$stmtSQL = "Select CBCOVE.* From CBCOVE inner join CBCOVR on CECOMP=CVCOMP and CEFACL=CVFACL and CECOVR=CVCOVR and CEPLAN=CVPLAN
				Where CEPLFU<>'' and CVCOMP=" . $emp ['EMCOMP'] . " and CVFACL=" . $emp ['EMFACL'] . "
				and CEECEF<=" . $taxYearEnd . " and CEECEX>=" . $taxYearStart;
	}
	$stmtSQL .= "   Order By CEECEF ";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$startRow = 1;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		$startRow ++;
		$row ['CEECEF'] = str_pad ( $row ['CEECEF'], 7, "0", STR_PAD_LEFT );
		$row ['CEECEX'] = str_pad ( $row ['CEECEX'], 7, "0", STR_PAD_LEFT );
		foreach ( $offerStatus as $month => $value ) {
			// Bypass months that already have a value
			if ($value != '') {
				continue;
			}
			$firstDayOfMonth = $taxYear . '-' . $month . '-01';
			$d = new DateTime ( $firstDayOfMonth );
			$lastDayOfMonth = $d->format ( 'Y-m-t' );
			$firstDayOfMonth = Date_FromISO_ToCYMD ( $firstDayOfMonth );
			$lastDayOfMonth = Date_FromISO_ToCYMD ( $lastDayOfMonth );
			
			// Coverage Expired prior to First Day of Month
			if ($row ['CEECEX'] < $firstDayOfMonth) {
				break;
			}
			
			if ($covOn1st [$month]) {
				if ($covOn1st [$month] == $row ['CEECEF'] && $lastDayOfMonth <= $row ['CEECEX']) {
					continue;
				}
			} elseif ($lastDayOfMonth <= $row ['CEECEX']) {
				// continue;
			} elseif ($firstDayOfMonth <= $row ['CEECEX']) {
				$date = date_create ( Date_CYMD_ISO ( $row ['CEECEX'] ) );
				date_add ( $date, date_interval_create_from_date_string ( '1 day' ) );
				$covOn1st [$month] = Date_FromISO_ToCYMD ( date_format ( $date, 'Y-m-d' ) );
				break;
			}
			// Individual Plan with Employee Share Lowest Cost < current value
			if ($row ['CECOVT'] == '0' && $row ['CELCEC'] > 0 && ($employeeShareLowestCost [$month] == 0 || floatval ( $row ['CELCEC'] ) < floatval ( $employeeShareLowestCost [$month] ))) {
				$employeeShareLowestCost [$month] = $row ['CELCEC'];
			}
			// Minimum Essential Coverage
			if ($row ['CEAMEC']) {
				$mecBenefit = TRUE;
				// Minimum Value and Coverage Type 0 (Individual)
				if ($row ['CEMVAL'] && $row ['CECOVT'] == '0') {
					$offerEE [$month] = TRUE;
				} elseif ($row ['CECOVT'] == '1') { // Coverage Type 1 (Spouse)
					$offerSpouse [$month] = TRUE;
				} elseif ($row ['CECOVT'] == '2') { // Coverage Type 2 (Dependent)
					$offerDep [$month] = TRUE;
				} elseif ($row ['CECOVT'] == '3') { // Coverage Type 3 (Family)
					$offerFam [$month] = TRUE;
				} elseif ($row ['CECOVT'] == '4') { // Coverage Type 4 (Spouse Conditional)
					$offerSpouseCond [$month] = TRUE;
				} elseif ($row ['CECOVT'] == '5') { // Coverage Type 5 (Dependent Conditional)
					$offerDepCond [$month] = TRUE;
				}
			}
		}
	}
	foreach ( $offerStatus as $month => $value ) {
		// Bypass months that already have a value
		if ($value != '') {
			continue;
		}
		// Employee Offer
		if ($offerEE [$month]) {
			if ($employmentStatus [$month] == 'PT') {
				if ($enrollmentStatus [$month] != '') {
					$offerStatus [$month] = '1G';
				} else {
					$offerStatus [$month] = '1H';
				}
			} elseif ($offerFam [$month] && array_key_exists ( $month, $employeeShareLowestCost ) && floatval ( $employeeShareLowestCost [$month] ) <= $einRow ['EIFPLAA']) {
				$offerStatus [$month] = '1A'; // Employee and Family Offer and Employee Share Lowest Cost <= 9.5% of Federal Poverty Line (92.39 -> 1108.65/12 for 2015 per form)
			} elseif ($offerFam [$month]) {
				$offerStatus [$month] = '1E'; // Employee and Family Offer
			} elseif ($offerSpouse [$month]) {
				$offerStatus [$month] = '1D'; // Employee and Spouse Offer
			} elseif ($offerSpouseCond [$month]) {
				$offerStatus [$month] = '1J'; // Employee and Spouse (Conditional) Offer
			} elseif ($offerDep [$month]) {
				$offerStatus [$month] = '1C'; // Employee and Dependent Offer
			} elseif ($offerDepCond [$month]) {
				$offerStatus [$month] = '1K'; // Employee and Spouse (Conditional) and Dependent Offer
			} else {
				$offerStatus [$month] = '1B'; // Employee Offer
			}
		} elseif ($mecBenefit) {
			$offerStatus [$month] = '1F'; // At least one Minimum Essential Coverage Offer
		} else {
			$offerStatus [$month] = '1H'; // Default value
		}
	}
}

/**
 * Add Affordable Care Act 1095 Cache row
 *
 * @param integer $aca1094CCacheId Id of Affordable Care Act 1094C Cache
 * @param array $einRow Array of Affordable Care Act EIN
 * @param array $emp Array of Employee
 * @param array $data Array of Affordable Care Act 1095C Cache to be added
 */
function add1095CCache($aca1094CCacheId, array $einRow, array $emp, array $data) {
	global $i5Connect, $coveredIndividual, $taxYearStart, $taxYearEnd, $employmentStatus, $enrollmentStatus, $offerStatus, $employeeShareLowestCost, $cnt_1095C_Attached, $cnt_Full_Time_ALE, $cnt_Employee_ALE;
	
	// Default Safe Harbor Type from EIN
	$safeHarborType = $einRow ['EIAFFTP'];
	
	// Check for Safe Harbor Type Override Transaction
	$stmtSQL = " Select HISTAT From PEHIST Where HICOMP={$emp['EMPECP']} and HIEMPL={$emp['EMPEMP']} and 
	             HITRDT>={$taxYearStart} and HITRDT<={$taxYearEnd} and HITRCD='ST' and HISTAT in ('SH2F','SH2G','SH2H')
	             Order By HITRDT desc 
	             Fetch First Row Only";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc ( $sqlResult );
	$safeHarborTrans = (array_key_exists ( 'HISTAT', $row )) ? $row ['HISTAT'] : FALSE;
	
	// Safe Harbor Type Override
	if ($safeHarborTrans) {
		if ($safeHarborTrans == 'SH2G') {
			$safeHarborType = '1'; // Federal Poverty Line
		} elseif ($safeHarborTrans == 'SH2F') {
			$safeHarborType = '3'; // Form W2 Wages
		} elseif ($safeHarborTrans == 'SH2H') {
			$safeHarborType = '2'; // Employee Rate of Pay
		}
	}
	
	$employeeShareCodes = array ('1B', '1C', '1D', '1E' );
	$fullTime = (in_array ( 'FT', $employmentStatus )) ? TRUE : FALSE;
	$partTime = (in_array ( 'PT', $employmentStatus ) && $coveredIndividual) ? TRUE : FALSE;
	$assessment = (in_array ( 'AS', $employmentStatus )) ? TRUE : FALSE;
	$terminated = (in_array ( 'TE', $employmentStatus )) ? TRUE : FALSE;
	
	if (! $fullTime && ! $partTime && $terminated && ! $assessment && ! $coveredIndividual) {
		return;
	}
	
	// If not Full Time, Part Time, In Assessment Period or Terminated for any month and not enrolled in benefit, skip employee
	if (! $fullTime && ! $partTime && ! $assessment && ! $terminated) {
		$enrolled = FALSE;
		foreach ( $enrollmentStatus as $month => $value ) {
			if ($value != '') {
				$enrolled = TRUE;
				break;
			}
		}
		if (! $enrolled) {
			return;
		}
	}
	$elementName = 'C5CVCD'; // Coverage Code
	$elementName1 = 'C5EMSH'; // Employee Share
	$elementName2 = 'C5SHBR'; // Section 4980H Safe Harbor
	$loadLines1516 = TRUE;
	$allMonths1G = TRUE;
	
	// Line 14 Coverage Code
	foreach ( $employmentStatus as $month => $value ) {
		if ($value == 'FT') { // Full Time
			$cnt_Full_Time_ALE [$month] ++; // Increment Full Time Count by month
		}
		if ($value != 'NO') { // Full Time, Part Time or Limited Assement Period
			$cnt_Employee_ALE [$month] ++; // Increment Employee Count by month
		}
		
		$employeeShareColumn = $elementName1 . $month;
		$data [$employeeShareColumn] = 0; // Initialize Line 15 Employee Share
		$safeHarborColumn = $elementName2 . $month;
		$data [$safeHarborColumn] = ''; // Initialize Line 16 Section 4980H Safe Harbor
		$coverageCodeColumn = $elementName . $month;
		$plan = explode ( ":", $enrollmentStatus [$month] ); // [0]=Benefit Code [1]=Plan Code [3] Sequence [3]=ACA Plan Funding
		                                                     // Self Funded
		if ($plan [3] == '0' || $coveredIndividual) {
			$data ['C5CVIND'] = 1; // Covered Individuals indicator
		}
		if (! $fullTime && $plan [3] == '0') {
			$data [$coverageCodeColumn] = '1G'; // Part time Employee Enrolled in Self-Insured Plan
				                                    // $loadLines1516 = FALSE;
		} else {
			$data [$coverageCodeColumn] = $offerStatus [$month];
			$allMonths1G = FALSE;
		}
	}
	
	// Load lines 15 & 16
	if ($loadLines1516) {
		if ($safeHarborType == '2') { // Employee Rate of Pay
			$affLimitByMonth = affLimitRateOfPay ( $emp );
		} elseif ($safeHarborType == '3') { // Form W-2 Wages
			$affLimit = affLimitW2Wages ( $emp );
		}
		
		foreach ( $employmentStatus as $month => $value ) {
			$coverageCodeColumn = $elementName . $month;
			$employeeShareColumn = $elementName1 . $month;
			$safeHarborColumn = $elementName2 . $month;
			
			// Line 15 Employee Share Lowest Cost of Coverage
			if (in_array ( $data [$coverageCodeColumn], $employeeShareCodes )) {
				$data [$employeeShareColumn] = $employeeShareLowestCost [$month];
			}
			
			// Line 16 Section 4980H Safe Harbor
			if ($enrollmentStatus [$month] != '' && $employmentStatus [$month] == 'NO' || $employmentStatus [$month] == 'TE') { // Terminated during the month
				$data [$safeHarborColumn] = '2B';
			} elseif ($enrollmentStatus [$month] != '' && ! $allMonths1G) {
				$data [$safeHarborColumn] = '2C';
			} elseif ($employmentStatus [$month] == 'NO' || $employmentStatus [$month] == 'NH') { // Not an employee or New Hire/Rehire
				$data [$safeHarborColumn] = '2A';
			} elseif ($employmentStatus [$month] == 'AS') { // Limited Assessment Period
				$data [$safeHarborColumn] = '2D';
			} elseif ($employmentStatus [$month] == 'PT' && $enrollmentStatus [$month] == '') { // Part time ACA Ineligible and not enrolled
				$data [$safeHarborColumn] = '2B';
			} elseif ($employmentStatus [$month] == 'FT' && $enrollmentStatus [$month] == '' && $data [$coverageCodeColumn] != '1A' && $data [$coverageCodeColumn] != '1H') {
				// Determine if insurance was affordable based on the Affordability Safe Harbor Type
				switch ($safeHarborType) {
					case '1' : // Federal Poverty Line
						if (floatval ( $employeeShareLowestCost [$month] ) <= floatval ( $einRow ['EIFPLAA'] )) {
							$data [$safeHarborColumn] = '2G';
						}
						break;
					case '2' : // Employee Rate of Pay
						if (floatval ( $employeeShareLowestCost [$month] ) <= floatval ( $affLimitByMonth [$month] )) {
							$data [$safeHarborColumn] = '2H';
						}
						break;
					case '3' : // Form W-2 Wages
						if (floatval ( $employeeShareLowestCost [$month] ) <= floatval ( $affLimit )) {
							$data [$safeHarborColumn] = '2F';
						}
						break;
				}
			} elseif ($enrollmentStatus [$month] == '' && $employmentStatus [$month] == 'IN') {
				$data [$safeHarborColumn] = '2B';
			}
		}
	}
	
	// Set the ALL 12 Month values
	$data ['C5CVCDA'] = $data ['C5CVCD01']; // Coverage Code All 12 Months
	$data ['C5SHBRA'] = $data ['C5SHBR01']; // Section 4980H Safe Harbor All 12 Months
	$data ['C5EMSHA'] = $data ['C5EMSH01']; // Employee Share All 12 Months
	$C5EMSH01 = $data ['C5EMSH01'];
	foreach ( $employmentStatus as $month => $value ) {
		$coverageCodeColumn = $elementName . $month;
		$employeeShareColumn = $elementName1 . $month;
		$safeHarborColumn = $elementName2 . $month;
		// If Coverage Code for month different than All 12 Months, clear All 12 Months column
		if ($data [$coverageCodeColumn] != $data ['C5CVCDA']) {
			$data ['C5CVCDA'] = '';
		}
		// If Safe Harbor for month different than All 12 Months, clear All 12 Months column
		if ($data [$safeHarborColumn] != $data ['C5SHBRA']) {
			$data ['C5SHBRA'] = '';
		}
		// If Employee Share for month different than January, set All 12 Months column to zero
		if ($data [$employeeShareColumn] != $C5EMSH01) {
			$data ['C5EMSHA'] = 0;
		}
	}
	
	$cnt_1095C_Attached ++; // Increment Form 1095C Count
	                        
	// Load Affordable Care Act 1095C Cache
	$stmtSQL = " Insert Into HRACC5 
			    (C5CAID94,C5EFNAM,C5EMNAM,C5ELNAM,C5TINRT,C5ESSOC,
			     C5ADDR1,C5ADDR2,C5CITY,C5STATE,C5PSTCD,C5CVCDA,C5PSMO,
			     C5CVCD01,C5CVCD02,C5CVCD03,C5CVCD04,C5CVCD05,C5CVCD06,
			     C5CVCD07,C5CVCD08,C5CVCD09,C5CVCD10,C5CVCD11,C5CVCD12,
			     C5EMSHA,C5EMSH01,C5EMSH02,C5EMSH03,C5EMSH04,C5EMSH05,
			     C5EMSH06,C5EMSH07,C5EMSH08,C5EMSH09,C5EMSH10,C5EMSH11,
			     C5EMSH12,C5SHBRA,C5SHBR01,C5SHBR02,C5SHBR03,C5SHBR04,C5SHBR05,
	             C5SHBR06,C5SHBR07,C5SHBR08,C5SHBR09,C5SHBR10,C5SHBR11,C5SHBR12,C5CVIND) ";
	
	$stmtSQL .= " Values 
	            ({$data ['C5CAID94']},'{$data ['C5EFNAM']}','{$data ['C5EMNAM']}','{$data ['C5ELNAM']}','INDIVIDUAL_TIN','{$data ['C5ESSOC']}',
	            '{$data ['C5ADDR1']}','{$data ['C5ADDR2']}','{$data ['C5CITY']}','{$data ['C5STATE']}','{$data ['C5PSTCD']}','{$data ['C5CVCDA']}',{$data['C5PSMO']},
	            '{$data ['C5CVCD01']}','{$data ['C5CVCD02']}','{$data ['C5CVCD03']}','{$data ['C5CVCD04']}','{$data ['C5CVCD05']}','{$data ['C5CVCD06']}',
	            '{$data ['C5CVCD07']}','{$data ['C5CVCD08']}','{$data ['C5CVCD09']}','{$data ['C5CVCD10']}','{$data ['C5CVCD11']}','{$data ['C5CVCD12']}',
	             {$data ['C5EMSHA']},{$data ['C5EMSH01']},{$data ['C5EMSH02']},{$data ['C5EMSH03']},{$data ['C5EMSH04']},{$data ['C5EMSH05']},
	             {$data ['C5EMSH06']},{$data ['C5EMSH07']},{$data ['C5EMSH08']},{$data ['C5EMSH09']},{$data ['C5EMSH10']},{$data ['C5EMSH11']},
	             {$data ['C5EMSH12']},'{$data ['C5SHBRA']}','{$data ['C5SHBR01']}','{$data ['C5SHBR02']}','{$data ['C5SHBR03']}','{$data ['C5SHBR04']}','{$data ['C5SHBR05']}',
	            '{$data ['C5SHBR06']}','{$data ['C5SHBR07']}','{$data ['C5SHBR08']}','{$data ['C5SHBR09']}','{$data ['C5SHBR10']}','{$data ['C5SHBR11']}','{$data ['C5SHBR12']}',{$data ['C5CVIND']}) ";
	$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	
	// If row not added, set identity column and try again
	if (! $status) {
		Check_Identity_Column ( 'HRACC5', 'C5CACHID', $stmtSQL );
	}
	
	// Covered Individuals
	if ($data ['C5CVIND'] == 1) {
		// Get ID of Affordable Care Act 1095C Cache
		$stmtSQL = " Select * From HRACC5 Where C5CAID94={$data ['C5CAID94']} and C5ESSOC='{$data ['C5ESSOC']}' ";
		$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		$aca1095CCacheRow = db2_fetch_assoc ( $sqlResult );
		
		// Load 1095C Dependent Cache
		load1095CCacheDep ( $einRow, $aca1095CCacheRow, $emp ['EMEMID'] );
	}
}

/**
 * Load Affordable Care Act 1095 Cache Dependents
 *
 * @param array   $einRow Array of Affordable Care Act EIN
 * @param array   $aca1095CCacheRow Array of Affordable Care Act 1095C Cache
 * @param integer $employeeId Id of Employee
 */
function load1095CCacheDep($einRow, $aca1095CCacheRow, $employeeId) {
	global $i5Connect, $enrollmentStatus, $coverageStatus, $taxYearStart, $taxYearEnd, $taxYear2;
	
	// Load Employee into Affordable Care Act 1095 Cache Dependent
	$data = array ();
	$data ['C2CAID5'] = $aca1095CCacheRow ['C5CACHID'];
	$data ['C2DFNAM'] = checkValue ( $aca1095CCacheRow ['C5EFNAM'] );
	$data ['C2DMNAM'] = $aca1095CCacheRow ['C5EMNAM'];
	$data ['C2DLNAM'] = checkValue ( $aca1095CCacheRow ['C5ELNAM'] );
	$data ['C2DSSOC'] = $aca1095CCacheRow ['C5ESSOC'];
	$data ['C2DDOB'] = CYMD_to_ISO ( $einRow ['EMDOB'] );
	$elementName = 'C2CVCD';
	$elementName2 = 'C5CVCD';
	$hasCoverage = FALSE;
	for($mm = 1; $mm <= 12; $mm ++) {
		$mm2 = substr ( sprintf ( "%'.02d\n", $mm ), 0, 2 );
		$elementMonth = $elementName . $mm2;
		$elementMonth2 = $elementName2 . $mm2;
		$coverage = (trim ( $enrollmentStatus [$mm2] ) != '' || $coverageStatus [$mm2] == 'Y') ? 1 : 0;
		if ($coverage) {
			$hasCoverage = TRUE;
		}
		$data [$elementMonth] = $coverage;
	}
	if ($hasCoverage) {
		add1095CCacheDep ( $data );
	}
	
	$data = init1095CCacheDep ();
	$saveEmployeeDepId = NULL;
	
	// Get Dependent Benefits
	$stmtSQL = "Select HREMCV.*,CBCOVE.*,HRSPDP.* From CBCOVE inner join HREMCV on CECOMP=ECCOMP and CEFACL=ECFACL and CECOVR=ECCOVR and CEPLAN=ECPLAN
			                                         inner join HREMPL on ECCOMP=EMCOMP and ECFACL=EMFACL and ECEMPL=EMPEMP
			                                         inner join HRSPDP on ECCOMP=SDCOMP and ECFACL=SDFACL and ECEMPL=SDEMPL and ECSPDP=SDSPDP 
				Where CEPLFU<>'' and EMEMID=" . $employeeId . " and ECSPDP>0 and
					 (ECDCT=0 or  ECDCT>=" . $taxYearStart . " and ECDCT<=" . $taxYearEnd . ") 
					  and CEECEF<=" . $taxYearEnd . " and CEECEX>=" . $taxYearStart . "
				Order by ECSPDP,CEECEF";
	
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$startRow = 1;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		$startRow ++;
		$row ['ECDCE'] = str_pad ( $row ['ECDCE'], 7, "0", STR_PAD_LEFT );
		$row ['ECDCT'] = str_pad ( $row ['ECDCT'], 7, "0", STR_PAD_LEFT );
		
		// Employee Dependent changed - Add row
		if (! is_null ( $saveEmployeeDepId ) && $saveEmployeeDepId !== $row ['SDSPDP']) {
			add1095CCacheDep ( $data );
			$data = init1095CCacheDep ();
		}
		if (! array_key_exists ( 'C2CAID5', $data )) {
			$saveEmployeeDepId = $row ['SDSPDP'];
			$data ['C2CAID5'] = $aca1095CCacheRow ['C5CACHID'];
			$data ['C2DFNAM'] = checkValue ( $row ['SDFNAM'] );
			$data ['C2DMNAM'] = $row ['SDMIDI'];
			$data ['C2DLNAM'] = checkValue ( $row ['SDLNAM'] );
			$data ['C2DSSOC'] = RetColValue ( "$profileHandle", "$dataBaseID", "SDCOMP={$row ['SDCOMP']} and SDFACL={$row ['SDFACL']} and SDEMPL={$row ['SDEMPL']} and SDSPDP={$row ['SDSPDP']}", "HRSPDP", "SDSSN", "D" );
			while ( strlen ( $row ['SDDOB'] ) < 7 ) {
				$row ['SDDOB'] = "0{$row ['SDDOB']}";
			}
			$data ['C2DDOB'] = CYMD_to_ISO ( $row ['SDDOB'] );
		}
		$elementName = 'C2CVCD';
		$elementName2 = 'C5CVCD';
		for($mm = 1; $mm <= 12; $mm ++) {
			$mm2 = substr ( sprintf ( "%'.02d\n", $mm ), 0, 2 );
			$coverageMonth = '1' . $taxYear2 . $mm2 . '01';
			$lastDayMonth = '1' . $taxYear2 . $mm2 . '31';
			// Dependent has eligibility for this Coverage Month
			if (($coverageMonth >= $row ['ECDCE'] || $row ['ECDCE'] <= $lastDayMonth) && ($row ['ECDCT'] == 0 || $row ['ECDCT'] > $lastDayMonth || $row ['ECDCT'] >= $coverageMonth && $row ['ECDCT'] <= $lastDayMonth)) {
				$elementMonth = $elementName . $mm2;
				$data [$elementMonth] = TRUE;
			}
		}
	}
	if (! is_null ( $saveEmployeeDepId )) {
		add1095CCacheDep ( $data );
	}
}

/**
 * Add Affordable Care Act 1095 Cache Dependent row
 *
 * @param array $data Array of Affordable Care Act 1095C Cache Dependent to be added
 */
function add1095CCacheDep(array $data) {
	global $i5Connect, $employmentStatus;
	
	// Set the ALL 12 Month values
	$elementName = 'C2CVCD'; // Coverage Code
	$data ['C2CVCDA'] = $data ['C2CVCD01']; // Coverage Code All 12 Months
	foreach ( $employmentStatus as $month => $value ) {
		$coverageCodeColumn = $elementName . $month;
		// If Coverage Code for month different than All 12 Months, clear All 12 Months column
		if ($data [$coverageCodeColumn] != $data ['C2CVCDA']) {
			$data ['C2CVCDA'] = 0;
		}
	}
	
	$C2TINRT = (trim($data ['C2DSSOC']) == '') ? '' : 'INDIVIDUAL_TIN';
	// Load Affordable Care Act 1095C Dependent Cache
	$stmtSQL = " Insert Into HRACC2 (C2CAID5,C2DFNAM,C2DMNAM,C2DLNAM,C2DSUFF,C2TINRT,
			                         C2DSSOC,C2DDOB,C2CVCDA,C2CVCD01,C2CVCD02,C2CVCD03,
			                         C2CVCD04,C2CVCD05,C2CVCD06,C2CVCD07,C2CVCD08,C2CVCD09,
			                         C2CVCD10,C2CVCD11,C2CVCD12) ";
	$stmtSQL .= " Values ({$data ['C2CAID5']},'{$data ['C2DFNAM']}','{$data ['C2DMNAM']}','{$data ['C2DLNAM']}','{$data ['C2DSUFF']}','{$C2TINRT}',
                         '{$data ['C2DSSOC']}','{$data ['C2DDOB']}',{$data ['C2CVCDA']},{$data ['C2CVCD01']},{$data ['C2CVCD02']},{$data ['C2CVCD03']},
                         {$data ['C2CVCD04']},{$data ['C2CVCD05']},{$data ['C2CVCD06']},{$data ['C2CVCD07']},{$data ['C2CVCD08']},{$data ['C2CVCD09']},
                         {$data ['C2CVCD10']},{$data ['C2CVCD11']},{$data ['C2CVCD12']}) ";
	$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	
	// If row not added, set identity column and try again
	if (! $status) {
		Check_Identity_Column ( 'HRACC2', 'C2DCID5', $stmtSQL );
	}
}

/**
 * Init Affordable Care Act 1095 Cache Dependent row
 *
 */
function init1095CCacheDep() {
	$data = array ();
	$data ['C2CVCDA'] = '';
	$elementName = 'C2CVCD';
	for($mm = 1; $mm <= 12; $mm ++) {
		$mm2 = substr ( sprintf ( "%'.02d\n", $mm ), 0, 2 );
		$elementMonth = $elementName . $mm2;
		$data [$elementMonth] = 0;
	}
	return $data;
}

/**
 * Update Affordable Care Act 1094 Cache Counts
 *
 * @param integer $aca1094CCacheId Id of Affordable Care Act 1094C Cache
 */
function upd1094CCacheCounts($aca1094CCacheId) {
	global $i5Connect, $cnt_1095C_Attached, $cnt_Full_Time_ALE, $cnt_Employee_ALE;
	
	$C4EACTA = $cnt_Employee_ALE ['01'];
	foreach ( $cnt_Employee_ALE as $month => $value ) {
		if ($C4EACTA != $value) {
			$C4EACTA = 0;
			break;
		}
	}
	
	$stmtSQL = " Update HRACC4 set C4ATCNT={$cnt_1095C_Attached},C4MBCNT={$cnt_1095C_Attached},
	                               C4FACT01={$cnt_Full_Time_ALE['01']},C4FACT02={$cnt_Full_Time_ALE['02']},C4FACT03={$cnt_Full_Time_ALE['03']},
	                               C4FACT04={$cnt_Full_Time_ALE['04']},C4FACT05={$cnt_Full_Time_ALE['05']},C4FACT06={$cnt_Full_Time_ALE['06']},
	                               C4FACT07={$cnt_Full_Time_ALE['07']},C4FACT08={$cnt_Full_Time_ALE['08']},C4FACT09={$cnt_Full_Time_ALE['09']},
	                               C4FACT10={$cnt_Full_Time_ALE['10']},C4FACT11={$cnt_Full_Time_ALE['11']},C4FACT12={$cnt_Full_Time_ALE['12']},
	                               C4EACTA={$C4EACTA},
	                               C4EACT01={$cnt_Employee_ALE['01']},C4EACT02={$cnt_Employee_ALE['02']},C4EACT03={$cnt_Employee_ALE['03']},
	                               C4EACT04={$cnt_Employee_ALE['04']},C4EACT05={$cnt_Employee_ALE['05']},C4EACT06={$cnt_Employee_ALE['06']},
	                               C4EACT07={$cnt_Employee_ALE['07']},C4EACT08={$cnt_Employee_ALE['08']},C4EACT09={$cnt_Employee_ALE['09']},
	                               C4EACT10={$cnt_Employee_ALE['10']},C4EACT11={$cnt_Employee_ALE['11']},C4EACT12={$cnt_Employee_ALE['12']}
	             Where C4CACHID={$aca1094CCacheId} ";
	$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
}

/**
 * Calculate Affordable Limit based on Employee W2 Wages
 *
 * @param array $emp Array of Employee
 */
function affLimitW2Wages(array $emp = array()) {
	global $i5Connect, $employmentStatus, $offerStatus, $taxYear;
	$affLimit = 0;
	$stmtSQL = " Select sum(QEQWTC) as WAGE From PRQEMP 
	             Where QECOMP={$emp['EMCOMP']} and QEFACL={$emp['EMFACL']} and QEEMPL={$emp['EMEMPL']} and QEYEAR={$taxYear}";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row = db2_fetch_assoc ( $sqlResult );
	
	$cnt = 0;
	foreach ( $employmentStatus as $month => $value ) {
		if ($value != 'NO') {
			$cnt ++;
		}
	}
	
	$affLimit = bcmul ( bcdiv ( floatval ( $row ['WAGE'] ), $cnt, 2 ), '.095', 2 );
	
	return $affLimit;
}

/**
 * Calculate Affordable Limit based on Employee Rate of Pay
 *
 * @param array $emp Array of Employee
 */
function affLimitRateOfPay(array $emp = array()) {
	global $i5Connect, $taxYearStart, $taxYearEnd;
	$affLimitByMonth = array ('01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0, '11' => 0, '12' => 0 );
	
	// If Employee Pay Type is Commission or Other, return 0 for all months
	if ($emp ['EMTYPE'] == 'C' || $emp ['EMTYPE'] == 'O') {
		return $affLimitByMonth;
	}
	
	// Read All Salary Change Transaction thru the Tax Year End for the Employee
	$stmtSQL = " Select HITRDT,HIRTCD,HIPAYT,HIPAYR 
		         From PEHIST inner join HREMPL on HICOMP=EMPECP and HIEMPL=EMPEMP
		         Where HICOMP={$emp ['EMPECP']} and HIEMPL={$emp ['EMPEMP']} and HITRDT<={$taxYearEnd} and 
		         HITRCD = 'SC' and HIPAYT in ('H','S')
		         Order By HITRDT,HITSTP ";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$startRow = 1;
	while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		$startRow ++;
		$rateOfPay = 0;
		if ($row ['HIPAYT'] == 'H') {
			$rateOfPay = bcmul ( floatval ( $row ['HIPAYR'] ), 130, 2 );
		} else {
			switch ($row ['HIRTCD']) {
				case 1 : // Weekly (HIPAYR*52/12)
					$rateOfPay = bcdiv ( bcmul ( floatval ( $row ['HIPAYR'] ), 52, 2 ), 12, 2 );
					break;
				case 2 : // Bi-weekly (HIPAYR*26/12)
					$rateOfPay = bcdiv ( bcmul ( floatval ( $row ['HIPAYR'] ), 26, 2 ), 12, 2 );
					break;
				case 3 : // Semi-monthly (HIPAYR*24/12)
					$rateOfPay = bcdiv ( bcmul ( floatval ( $row ['HIPAYR'] ), 24, 2 ), 12, 2 );
					break;
				case 4 : // Monthly
					$rateOfPay = $row ['HIPAYR'];
					break;
				case 5 : // Annually (HIPAYR/12)
					$rateOfPay = bcdiv ( floatval ( $row ['HIPAYR'] ), 12, 2 );
					break;
			}
		}
		if ($rateOfPay > 0) {
			if ($row ['HITRDT'] < $taxYearStart) {
				$curPay = $rateOfPay;
			} else {
				$dd = substr ( $row ['HITRDT'], 5, 2 ); // Determine day of Transaction Date
				$mm = substr ( $row ['HITRDT'], 3, 2 ); // Determine month of Transaction Date
				foreach ( $affLimitByMonth as $month => $value ) {
					if ($month >= $mm) {
						break;
					}
					if ($value == 0) {
						$affLimitByMonth [$month] = $curPay;
					}
				}
				if ($dd == '01' || $rateOfPay < $curPay) {
					$affLimitByMonth [$mm] = $rateOfPay;
				} else {
					$affLimitByMonth [$mm] = $curPay;
				}
				$curPay = $rateOfPay;
			}
		}
	}
	
	// Multiply Monthly Limit by 9.5%
	foreach ( $affLimitByMonth as $month => $value ) {
		if ($month > $mm && $value == 0) {
			$value = $curPay;
		}
		if ($value > 0) {
			$affLimitByMonth [$month] = bcmul ( floatval ( $value ), '.095', 2 );
		}
	}
	
	return $affLimitByMonth;
}

/**
 * Delete Cache prior to load
 *
 * @param array $data Array of Affordable Care Act EIN row to be loaded to cache
 */
function delete1094CCache(array $data = array()) {
	if (array_key_exists ( 'EITXYR', $data ) && array_key_exists ( 'EIEIN', $data )) {
		global $i5Connect;
		$stmtSQL = " Delete From HRACC4 Where C4TXYR=" . $data ['EITXYR'] . " and C4PYTIN ='" . trim ( $data ['EIEIN'] ) . "'";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$stmtSQL = " Delete From HRACC5 a Where not exists (Select * from HRACC4 Where a.C5CAID94=C4CACHID)";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		
		$stmtSQL = " Delete From HRACC2 a Where not exists (Select * from HRACC5 Where a.C2CAID5=C5CACHID)";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	}
}

/**
 * Check value for special characters
 *
 * @param string $value Value to check
 */
function checkValue($value) {
	$value = str_replace ( "'", "''", $value );
	return $value;
}

/**
 * Format Date from CYMD to ISO
 *
 * @param array $data Array of Affordable Care Act EIN row to be loaded to cache
 */
function CYMD_to_ISO($date) {
	$dateISO = '';
	if (substr ( $date, 0, 1 ) == '0') {
		$year = '19';
	} else {
		$year = '20';
	}
	$dateISO = $year . substr ( $date, 1, 2 ) . '-' . substr ( $date, 3, 2 ) . '-' . substr ( $date, 5, 2 );
	return $dateISO;
}

?>	
