<?php
/**
 * Include this file in the 'submitted' script. 
 * It populates a global variable, $sourceData, which is an array containing the data needed
 * to produce the two xml files, Manifest and Form Data, for ACA reporting. 
 * This script assumes the 'includer' has:
 *   1) already included SetLibraryList.php
 *   2) set the $transCtrlCd variable to the appropriate Transmitter Control Code 
 *   3) set the $fromACA1094CID variable to either the SINGLE Affordable Care Act 1094C Cache ID to transform
 *      or an empty string when transforming MULTIPLE EINs
 *   4) set the $taxYear variable to either the appropriate Tax Year to transform MULTIPLE EINs
 *      or an empty string when transforming a SINGLE
 *   5) set the $corr variable to either a O(riginal), C(orrected) or R(eplacement) to transform MULTIPLE EINs
 *      or an empty string when transforming a SINGLE
 */
$sourceData = array ();

// Submitter Data
$submitterData = gatherTaxReportSubmitter ();
if ($submitterData) {
	$submitterData ['TRANSMITTER_CONTROL_CODE'] = $transCtrlCd;
	$sourceData ['TaxReportSubmitter'] = $submitterData;
	
	// Employer Data
	$employerData = gatherTaxReportEmployer ( $fromACA1094CID, $taxYear, $corr );
	if ($employerData) {
		$sourceData ['TaxReportEmployer'] = $employerData;
	}
}

/**
 * Get the Tax Report Submitter info
 * 
 * @return array Tax Transmitter data 
 */
function gatherTaxReportSubmitter() {
	global $i5Connect;
	
	$submitterData = array ();
	
	$stmtSQL = 'Select QMFDID as SUBMITTER_EIN, QMNAME as SUBMITTER_NAME';
	$stmtSQL .= ', QMADDR as SUBMITTER_ADDRESS_ID__ADDRESS_LINE_1';
	$stmtSQL .= ', \' \' as SUBMITTER_ADDRESS_ID__ADDRESS_LINE_2';
	$stmtSQL .= ', QMCITY as SUBMITTER_ADDRESS_ID__CITY';
	$stmtSQL .= ', QMSTAT as SUBMITTER_ADDRESS_ID__SUBDIVISION_CODE';
	$stmtSQL .= ', Concat(QMZIP, QMZIPX) as SUBMITTER_ADDRESS_ID__POSTAL_CODE';
	$stmtSQL .= ', QMCNAM as CONTACT_NAME';
	$stmtSQL .= ', QMCPHN as CONTACT_PHONE_NUMBER';
	$stmtSQL .= ', QMCPHX as CONTACT_PHONE_EXTENSION';
	$stmtSQL .= ', QMNAME as COMPANY_NAME';
	$stmtSQL .= ' From PRQTRM ';
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	if ($sqlResult) {
		$submitterData = db2_fetch_assoc ( $sqlResult );
	}
	
	return $submitterData;
}

/**
 * Get the Tax Report Employer info
 * 
 * @param integer $cacheId Affordable Care Act 1094C Cache ID
 * @param number $year Tax Year
 * @param string $corr Correction
 * @return multidimensional array Employer (1094C) and Employees (1095C) and Covered Individuals (1095C Part III) 
 */
function gatherTaxReportEmployer($cacheId, $year, $corr) {
	global $i5Connect;
	
	$employerData = array ();
	
	$stmtSQL = 'Select ROW_NUMBER () OVER () as NUMBER, a.C4CACHID, a.C4CORCID';
	$stmtSQL .= ', a.C4TXYR as TAX_YEAR, a.C4RCPTID as RECEIPT_ID, a.C4SUBMID as SUBMISSION_ID';
	$stmtSQL .= ', a.C4CORR as CORRECTED, a.C4CSUBID as CORRECTED_SUBMISSION_ID, a.C4TRANID as UNIQUE_TRANSMISSION_ID';
	$stmtSQL .= ', a.C4BNAM1 as BUSINESS_NAME_1, a.C4BNAM2 as BUSINESS_NAME_2';
	$stmtSQL .= ', a.C4PYTIN as PAYER_TIN, a.C4BNAMC as BUSINESS_NAME_CONTROL, a.C4TINRT as TIN_REQUEST_TYPE';
	$stmtSQL .= ', a.C4ADDR1 as ADDRESS_LINE_1, a.C4ADDR2 as ADDRESS_LINE_2, a.C4CITY as CITY';
	$stmtSQL .= ', a.C4STATE as STATE, a.C4PSTCD as POSTAL_CODE, a.C4CFNAM as CONTACT_FIRST_NAME';
	$stmtSQL .= ', a.C4CMNAM as CONTACT_MIDDLE_INITIAL, a.C4CLNAM as CONTACT_LAST_NAME, a.C4CSUFF as CONTACT_SUFFIX';
	$stmtSQL .= ', a.C4CPHON as CONTACT_PHONE, a.C4ATCNT as FORM_1095_ATTACHED_COUNT, a.C4ATRAN as AUTHORITATIVE_TRANSMITTER';
	$stmtSQL .= ', a.C4MBCNT as FORM_1095C_ALE_MEMBER_COUNT, a.C4AAGRP as AGGREGATED_ALE_GROUP, a.C4QOFFM as QUALIFYING_OFFER_METHOD';
	$stmtSQL .= ', a.C4QOFFTR as QUALIFYING_OFFER_TRANS_RELIEF, a.C44980TR as SECTION_4980H_TRANS_RELIEF, a.C4OFFM98 as OFFER_METHOD_98_PERCENT';
	$stmtSQL .= ', a.C4JSPIN as JURAT_SIGNATURE_PIN, a.C4CTITL as CONTACT_PERSON_TITLE, a.C4SIGDT as SIGNATURE_DATE';
	$stmtSQL .= ', a.C4MC12M as MIN_COVERAGE_12_MONTHS, a.C4QCOV01 as QUALIFYING_COVERAGE_1, a.C4QCOV02 as QUALIFYING_COVERAGE_2';
	$stmtSQL .= ', a.C4QCOV03 as QUALIFYING_COVERAGE_3, a.C4QCOV04 as QUALIFYING_COVERAGE_4, a.C4QCOV05 as QUALIFYING_COVERAGE_5';
	$stmtSQL .= ', a.C4QCOV06 as QUALIFYING_COVERAGE_6, a.C4QCOV07 as QUALIFYING_COVERAGE_7, a.C4QCOV08 as QUALIFYING_COVERAGE_8';
	$stmtSQL .= ', a.C4QCOV09 as QUALIFYING_COVERAGE_9, a.C4QCOV10 as QUALIFYING_COVERAGE_10, a.C4QCOV11 as QUALIFYING_COVERAGE_11';
	$stmtSQL .= ', a.C4QCOV12 as QUALIFYING_COVERAGE_12, a.C4FACT01 as FULL_TIME_ALE_COUNT_1, a.C4FACT02 as FULL_TIME_ALE_COUNT_2';
	$stmtSQL .= ', a.C4FACT03 as FULL_TIME_ALE_COUNT_3, a.C4FACT04 as FULL_TIME_ALE_COUNT_4, a.C4FACT05 as FULL_TIME_ALE_COUNT_5';
	$stmtSQL .= ', a.C4FACT06 as FULL_TIME_ALE_COUNT_6, a.C4FACT07 as FULL_TIME_ALE_COUNT_7, a.C4FACT08 as FULL_TIME_ALE_COUNT_8';
	$stmtSQL .= ', a.C4FACT09 as FULL_TIME_ALE_COUNT_9, a.C4FACT10 as FULL_TIME_ALE_COUNT_10, a.C4FACT11 as FULL_TIME_ALE_COUNT_11';
	$stmtSQL .= ', a.C4FACT12 as FULL_TIME_ALE_COUNT_12, a.C4EACTA as EMPLOYEE_ALE_COUNT_12_MONTHS, a.C4EACT01 as EMPLOYEE_ALE_COUNT_1';
	$stmtSQL .= ', a.C4EACT02 as EMPLOYEE_ALE_COUNT_2, a.C4EACT03 as EMPLOYEE_ALE_COUNT_3, a.C4EACT04 as EMPLOYEE_ALE_COUNT_4';
	$stmtSQL .= ', a.C4EACT05 as EMPLOYEE_ALE_COUNT_5, a.C4EACT06 as EMPLOYEE_ALE_COUNT_6, a.C4EACT07 as EMPLOYEE_ALE_COUNT_7';
	$stmtSQL .= ', a.C4EACT08 as EMPLOYEE_ALE_COUNT_8, a.C4EACT09 as EMPLOYEE_ALE_COUNT_9, a.C4EACT10 as EMPLOYEE_ALE_COUNT_10';
	$stmtSQL .= ', a.C4EACT11 as EMPLOYEE_ALE_COUNT_11, a.C4EACT12 as EMPLOYEE_ALE_COUNT_12, a.C4AGRPA as AGGREGATED_GROUP_12_MONTHS';
	$stmtSQL .= ', a.C4AGRP01 as AGGREGATED_GROUP_1, a.C4AGRP02 as AGGREGATED_GROUP_2, a.C4AGRP03 as AGGREGATED_GROUP_3';
	$stmtSQL .= ', a.C4AGRP04 as AGGREGATED_GROUP_4, a.C4AGRP05 as AGGREGATED_GROUP_5, a.C4AGRP06 as AGGREGATED_GROUP_6';
	$stmtSQL .= ', a.C4AGRP07 as AGGREGATED_GROUP_7, a.C4AGRP08 as AGGREGATED_GROUP_8, a.C4AGRP09 as AGGREGATED_GROUP_9';
	$stmtSQL .= ', a.C4AGRP10 as AGGREGATED_GROUP_10, a.C4AGRP11 as AGGREGATED_GROUP_11, a.C4AGRP12 as AGGREGATED_GROUP_12';
	$stmtSQL .= ', a.C44980TA as SECTION_4980H_TRANS_12_MONTHS, a.C44980T01 as SECTION_4980H_TRANS_1, a.C44980T02 as SECTION_4980H_TRANS_2';
	$stmtSQL .= ', a.C44980T03 as SECTION_4980H_TRANS_3, a.C44980T04 as SECTION_4980H_TRANS_4, a.C44980T05 as SECTION_4980H_TRANS_5';
	$stmtSQL .= ', a.C44980T06 as SECTION_4980H_TRANS_6, a.C44980T07 as SECTION_4980H_TRANS_7, a.C44980T08 as SECTION_4980H_TRANS_8';
	$stmtSQL .= ', a.C44980T09 as SECTION_4980H_TRANS_9, a.C44980T10 as SECTION_4980H_TRANS_10, a.C44980T11 as SECTION_4980H_TRANS_11';
	$stmtSQL .= ', a.C44980T12 as SECTION_4980H_TRANS_12';
	$stmtSQL .= ', b.C4BNAM1 as CORR__BUSINESS_NAME_1, b.C4BNAM2 as CORR__BUSINESS_NAME_2, b.C4PYTIN as CORR__PAYER_TIN';
	$stmtSQL .= ' From HRACC4 a';
	$stmtSQL .= ' Left join HRACC4 b on a.C4CORCID=b.C4CACHID';
	if (! empty ( $cacheId )) {
		// SINGLE
		$stmtSQL .= ' Where a.C4CACHID=' . $cacheId . ' and a.C4RCPTID=\' \'';
	} elseif ($corr == 'O' || $corr == 'R') {
		// MULTIPLE Original or Replacement
		$stmtSQL .= ' Where a.C4TXYR=' . $year . ' and a.C4CORR=\'' . $corr . '\' and a.C4RCPTID=\' \'';
	} else {
		// MULTIPLE Corrected
		$stmtSQL .= ' Where a.C4TXYR=' . $year . ' and a.C4CORR in (\'4\',\'5\') and a.C4RCPTID=\' \'';
	}
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	
	if ($sqlResult) {
		$startRow = 1;
		while ( $taxReportEmployerRow = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
			$taxReportEmployerRow ['SUBMISSION_ID'] = $taxReportEmployerRow ['NUMBER'];
			// Gather Other ALE Members for Employer
			$memberData = gatherTaxReportOtherALEMembers ( $taxReportEmployerRow ['C4CACHID'] );
			if ($memberData) {
				$taxReportEmployerRow ['OtherALEMembers'] = $memberData;
			}
			// Gather Employee Data for Employer
			$employeeData = gatherTaxReportEmployee ( $taxReportEmployerRow ['C4CACHID'] );
			if ($employeeData) {
				$taxReportEmployerRow ['FORM_1095_ATTACHED_COUNT'] = count ( $employeeData );
				$taxReportEmployerRow ['TaxCacheEmployee'] = $employeeData;
			}
			
			// Add to other Employers
			$employerData [] = $taxReportEmployerRow;
			
			$startRow ++;
		}
	}
	
	return $employerData;
}

/**
 * Get the Tax Report Other ALE Members info
 *
 * @param integer $cacheId Affordable Care Act 1094C Cache ID
 * @return array Other ALE Members (1094C Part IV)
 */
function gatherTaxReportOtherALEMembers($cacheId) {
	global $i5Connect;
	
	$memberData = array ();
	
	$stmtSQL = 'Select C3CACHID';
	$stmtSQL .= ', C3CAID94 as ACA_1094C_CACHE_ID';
	$stmtSQL .= ', C3BNAM1 as BUSINESS_NAME_1';
	$stmtSQL .= ', C3BNAM2 as BUSINESS_NAME_2';
	$stmtSQL .= ', C3PYTIN as PAYER_TIN';
	$stmtSQL .= ', C3BNAMC as BUSINESS_NAME_CONTROL';
	$stmtSQL .= ', C3TINRT as TIN_REQUEST_TYPE';
	$stmtSQL .= ', C3LUPD as LAST_UPDATED';
	$stmtSQL .= ' From HRACC3';
	$stmtSQL .= ' Where C3CAID94=' . $cacheId;
	$stmtSQL .= ' Order by C3PYTIN';
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	
	if ($sqlResult) {
		$startRow = 1;
		while ( $memberRow = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
			// Add to other Members
			$memberData [] = $memberRow;
			
			$startRow ++;
		}
	}
	
	return $memberData;
}

/**
 * Get the Tax Report Employee info
 * 
 * @param integer $cacheId Affordable Care Act 1094C Cache ID
 * @return multidimensional array Employees (1095C) and Covered Individuals (1095C Part III)  
 */
function gatherTaxReportEmployee($cacheId) {
	global $i5Connect;
	
	$employeeData = array ();
	
	$stmtSQL = 'Select ROW_NUMBER () OVER () as NUMBER, a.C5CACHID, a.C5CORCID';
	$stmtSQL .= ', a.C5RECID as RECORD_ID, a.C5CURID as CORRECTED_UNIQUE_RECORD_ID';
	$stmtSQL .= ', a.C5EFNAM as FIRST_NAME, a.C5EMNAM as MIDDLE_NAME';
	$stmtSQL .= ', a.C5ELNAM as LAST_NAME, a.C5ESSOC as SOCIAL_SECURITY_NUMBER';
	$stmtSQL .= ', a.C5TINRT as TIN_REQUEST_TYPE, a.C5ADDR1 as ADDRESS_LINE_1';
	$stmtSQL .= ', a.C5ADDR2 as ADDRESS_LINE_2, a.C5CITY as CITY, a.C5STATE as STATE, a.C5PSTCD as POSTAL_CODE';
	$stmtSQL .= ', a.C5EEAGE as EMPLOYEE_AGE, a.C5PSMO as PLAN_START_MONTH';
	$stmtSQL .= ', a.C5CVCDA as COVERAGE_CODE_12_MONTHS, a.C5CVCD01 as COVERAGE_CODE_1';
	$stmtSQL .= ', a.C5CVCD02 as COVERAGE_CODE_2, a.C5CVCD03 as COVERAGE_CODE_3, a.C5CVCD04 as COVERAGE_CODE_4';
	$stmtSQL .= ', a.C5CVCD05 as COVERAGE_CODE_5, a.C5CVCD06 as COVERAGE_CODE_6, a.C5CVCD07 as COVERAGE_CODE_7';
	$stmtSQL .= ', a.C5CVCD08 as COVERAGE_CODE_8, a.C5CVCD09 as COVERAGE_CODE_9, a.C5CVCD10 as COVERAGE_CODE_10';
	$stmtSQL .= ', a.C5CVCD11 as COVERAGE_CODE_11, a.C5CVCD12 as COVERAGE_CODE_12, a.C5EMSHA as EMPLOYEE_SHARE_12_MONTHS';
	$stmtSQL .= ', a.C5EMSH01 as EMPLOYEE_SHARE_1, a.C5EMSH02 as EMPLOYEE_SHARE_2, a.C5EMSH03 as EMPLOYEE_SHARE_3';
	$stmtSQL .= ', a.C5EMSH04 as EMPLOYEE_SHARE_4, a.C5EMSH05 as EMPLOYEE_SHARE_5, a.C5EMSH06 as EMPLOYEE_SHARE_6';
	$stmtSQL .= ', a.C5EMSH07 as EMPLOYEE_SHARE_7, a.C5EMSH08 as EMPLOYEE_SHARE_8, a.C5EMSH09 as EMPLOYEE_SHARE_9';
	$stmtSQL .= ', a.C5EMSH10 as EMPLOYEE_SHARE_10, a.C5EMSH11 as EMPLOYEE_SHARE_11, a.C5EMSH12 as EMPLOYEE_SHARE_12';
	$stmtSQL .= ', a.C5SHBRA as SECTION_4980H_SAFE_12_MONTHS, a.C5SHBR01 as SECTION_4980H_SAFE_HARBOR_1, a.C5SHBR02 as SECTION_4980H_SAFE_HARBOR_2';
	$stmtSQL .= ', a.C5SHBR03 as SECTION_4980H_SAFE_HARBOR_3, a.C5SHBR04 as SECTION_4980H_SAFE_HARBOR_4, a.C5SHBR05 as SECTION_4980H_SAFE_HARBOR_5';
	$stmtSQL .= ', a.C5SHBR06 as SECTION_4980H_SAFE_HARBOR_6, a.C5SHBR07 as SECTION_4980H_SAFE_HARBOR_7, a.C5SHBR08 as SECTION_4980H_SAFE_HARBOR_8';
	$stmtSQL .= ', a.C5SHBR09 as SECTION_4980H_SAFE_HARBOR_9, a.C5SHBR10 as SECTION_4980H_SAFE_HARBOR_10, a.C5SHBR11 as SECTION_4980H_SAFE_HARBOR_11';
	$stmtSQL .= ', a.C5SHBR12 as SECTION_4980H_SAFE_HARBOR_12, a.C5CVIND as COVERED_INDIVIDUAL';
	$stmtSQL .= ', a.C5ZIPA as ZIP_CODE_12_MONTHS, a.C5ZIP01 as ZIP_CODE_1';
	$stmtSQL .= ', a.C5ZIP02 as ZIP_CODE_2, a.C5ZIP03 as ZIP_CODE_3, a.C5ZIP04 as ZIP_CODE_4';
	$stmtSQL .= ', a.C5ZIP05 as ZIP_CODE_5, a.C5ZIP06 as ZIP_CODE_6, a.C5ZIP07 as ZIP_CODE_7';
	$stmtSQL .= ', a.C5ZIP08 as ZIP_CODE_8, a.C5ZIP09 as ZIP_CODE_9, a.C5ZIP10 as ZIP_CODE_10';
	$stmtSQL .= ', a.C5ZIP11 as ZIP_CODE_11, a.C5ZIP12 as ZIP_CODE_12';
	$stmtSQL .= ', b.C5EFNAM as CORR__FIRST_NAME, b.C5EMNAM as CORR__MIDDLE_NAME, b.C5ELNAM as CORR__LAST_NAME, b.C5ESSOC as CORR__SOCIAL_SECURITY_NUMBER';
	$stmtSQL .= ' From HRACC5 a';
	$stmtSQL .= ' Left join HRACC5 b on a.C5CORCID=b.C5CACHID';
	$stmtSQL .= ' Where a.C5CAID94=' . $cacheId;
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	
	if ($sqlResult) {
		$startRow = 1;
		while ( $taxCacheEmployeeRow = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
			$taxCacheEmployeeRow ['RECORD_ID'] = $taxCacheEmployeeRow ['NUMBER'];
			// Gather Dependent Data for Employee
			$dependentData = gatherTaxReportDep ( $taxCacheEmployeeRow ['C5CACHID'] );
			if ($dependentData) {
				$taxCacheEmployeeRow ['TaxCacheDep'] = $dependentData;
			}
			
			// Add to other Employees
			$employeeData [] = $taxCacheEmployeeRow;
			
			$startRow ++;
		}
	}
	
	return $employeeData;
}

/**
 * Get the Tax Report Dependent info
 * 
 * @param integer $cacheId Affordable Care Act 1095C Cache ID
 * @return array Covered Individuals (1095C Part III) 
 */
function gatherTaxReportDep($cacheId) {
	global $i5Connect;
	
	$dependentData = array ();
	
	$stmtSQL = 'Select C2DFNAM as FIRST_NAME, C2DMNAM as MIDDLE_NAME, C2DLNAM as LAST_NAME';
	$stmtSQL .= ', C2DSUFF as SUFFIX, C2DSSOC as SOCIAL_SECURITY_NUMBER';
	$stmtSQL .= ', C2TINRT as TIN_REQUEST_TYPE, C2DDOB as DATE_OF_BIRTH';
	$stmtSQL .= ', C2CVCDA as COVERAGE_CODE_12_MONTHS, C2CVCD01 as COVERAGE_CODE_1, C2CVCD02 as COVERAGE_CODE_2';
	$stmtSQL .= ', C2CVCD03 as COVERAGE_CODE_3, C2CVCD04 as COVERAGE_CODE_4, C2CVCD05 as COVERAGE_CODE_5';
	$stmtSQL .= ', C2CVCD06 as COVERAGE_CODE_6, C2CVCD07 as COVERAGE_CODE_7, C2CVCD08 as COVERAGE_CODE_8';
	$stmtSQL .= ', C2CVCD09 as COVERAGE_CODE_9, C2CVCD10 as COVERAGE_CODE_10, C2CVCD11 as COVERAGE_CODE_11';
	$stmtSQL .= ', C2CVCD12 as COVERAGE_CODE_12';
	$stmtSQL .= ' From HRACC2';
	$stmtSQL .= ' Where C2CAID5=' . $cacheId;
	$stmtSQL .= ' Order by 1';
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	
	if ($sqlResult) {
		$startRow = 1;
		while ( $taxCacheDep = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
			$startRow ++;
			
			// Add to other Dependents
			$dependentData [] = $taxCacheDep;
		}
	}
	
	return $dependentData;
}

?>
