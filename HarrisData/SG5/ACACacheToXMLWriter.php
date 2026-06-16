<?php

/**
 * Class formats ACA Information Returns as two xml files, Manifest and Form Data.
 *
 */
class ACACacheToXMLWriter
{

    /**
     * String for zero
     */
    const ZERO = '0';

    /**
     * Allow control of report driver
     */
    const DRIVER = 'ACACacheToXMLDriver';

    // Software Vendor Information
    /**
     * Current Tax Year
     */
    const CURRENT_YEAR = '2023';

    /**
     * HD Software Identification Tax Year 2023
     */
    const SOFTWARE_ID_23 = '23A0017836';

    /**
     * HD Software Identification Tax Year 2022
     */
    const SOFTWARE_ID_22 = '22A0015024';

    /**
     * HD Software Identification Tax Year 2021
     */
    const SOFTWARE_ID_21 = '21A0013300';

    /**
     * HD Software Identification Tax Year 2020
     */
    const SOFTWARE_ID_20 = '20A0011098';

    /**
     * HD Software Identification Tax Year 2019
     */
    const SOFTWARE_ID_19 = '19A0009243';

    /**
     * HD Software Identification Tax Year 2018
     */
    const SOFTWARE_ID_18 = '18A0006822';

    /**
     * HD Software Identification Tax Year 2017
     */
    const SOFTWARE_ID_17 = '17A0005822';

    /**
     * HD Software Identification Tax Year 2016
     */
    const SOFTWARE_ID_16 = '16A0003277';

    /**
     * HD Software Identification Tax Year 2015
     */
    const SOFTWARE_ID_15 = '15A0000961';

    /**
     * HD Contact First Name
     * @var string
     */
    protected $vendorContactNameFirst = 'Wesley';

    /**
     * HD Contact Middle Name
     * @var string
     */
    protected $vendorContactNameMiddle = '';

    /**
     * HD Contact Last Name
     * @var string
     */
    protected $vendorContactNameLast = 'Pals';

    /**
     * HD Contact Name Suffix
     * @var string
     */
    protected $vendorContactNameSuffix = '';

    /**
     * HD Contact Phone Number
     * @var string
     */
    protected $vendorContactPhone = '2627849099';

    // Specialized text formatting
    /**
     * US Currency (2 digits past decimal)
     */
    const FMT_CUR = 1;

    /**
     * Number (INTEGERS ONLY)
     */
    const FMT_NBR = 2;

    /**
     * Flag/Indicator (1|0)
     */
    const FMT_FLG = 3;

    /**
     * Date (uses DateTime object and format accepted by date())
     */
    const FMT_DTE = 4;

    /**
     * String
     */
    const FMT_STR = 5;

    /**
     * Phone number
     */
    const FMT_PHN = 6;

    /**
     * Zip Code Extension
     */
    const FMT_ZXT = 7;

    /**
     * Zip Code
     */
    const FMT_ZIP = 8;

    /**
     * Taxpayer Identification Number (EIN, SSN, etc)
     */
    const FMT_TIN = 9;

    /**
     * ACA xsd BusinessNameControlType pattern
     */
    const FMT_BUSNAMCTRL = 10;

    /**
     * ACA xsd PersonFirstName(et al)Type pattern
     */
    const FMT_NAME = 11;

    /**
     * ACA xsd CityType pattern
     */
    const FMT_CITY = 12;

    /**
     * ACA xsd BusinessNameLine1 Type pattern
     */
    const FMT_BUSNAM = 13;

    /**
     * ACA xsd BusinessNameLine2 Type pattern
     */
    const FMT_BUSNAM2 = 14;

    /**
     * ACA xsd JuratSignaturePINType pattern
     */
    const FMT_JSPIN = 15;

    /**
     * ACA xsd StreetAddressType pattern
     */
    const FMT_ADDR = 16;

    /**
     * Flag variation (1=Yes/True|2=No/False)
     */
    const FMT_YN = 17;

    /**
     * Default date format used in data models
     * @var string
     */
    protected $defaultDateFormat = 'Y-m-d';

    //
    /**
     * Tax Report data
     * @var array
     */
    protected $reportData = array();

    /**
     * Tax Submitter data
     * @var array
     */
    protected $submitterData = array();

    /**
     * current Tax Report Employer
     * @var array
     */
    protected $employerData = array();

    /**
     * current Tax Cache Employee
     * @var array
     */
    protected $employeeData = array();

    /**
     * current Tax Cache Employee/Dependent
     * @var array
     */
    protected $dependentData = array();

    /**
     * TRUE if submitter data set
     * @var bool
     */
    protected $flagSubmitter = false;

    /**
     * TRUE if employer data set
     * @var bool
     */
    protected $flagEmployer = false;

    /**
     * TRUE if employee data set
     * @var bool
     */
    protected $flagEmployee = false;

    /**
     * Working Directory
     * @var
     */
    protected $workDir;

    // ACA requires 2 XML files - Manifest and Form Data
    /**
     * Keys to the file resources and names arrays
     * @var array
     */
    protected $fileKeys = array(
        'Manifest',
        'FormData'
    );

    /**
     * SimpleXMLElements
     * @var array
     */
    protected $fileResources = array();

    /**
     * File names include the path
     * @var array
     */
    protected $fileNames = array();

    /**
     * @var integer Count Employers
     */
    protected $cntER = 0;

    /**
     * @var integer Count Employees by Employer
     */
    protected $cntEEER = 0;

    /**
     * @var integer Count Employees
     */
    protected $cntEE = 0;

    /**
     * @var array Offer of Coverage Codes Indicating Individual Coverage HRA
     */
    protected $icHRA = [
        '1L',
        '1M',
        '1N',
        '1O',
        '1P',
        '1Q',
        '1T',
        '1U'
    ];

    /**
     * Class constructor
     *
     * @param array $data
     *            The tax report data - requires at least the Tax Year (YYYY)
     * @param string $workDir
     *            The working directory for the xml files
     * @throws Exception If the xml resources cannot be initialized
     */
    public function __construct(array $data, $workDir)
    {
        $this->setReport($data);
        $this->setWorkDir($workDir);
        try {
            $this->fileInit();
        } catch (Exception $e) {
            throw $e;
        }
        return;
    }

    // ************************************** Collect data
    
    /**
     * Create next employer group
     *
     * Form1094CUpstreamDetail
     *
     * @param array $data
     *            employer-specific data
     * @throws Exception submitter data is not provided first
     */
    public function nextEmployer(array $data)
    {
        // Must have submission started
        if (! $this->flagSubmitter) {
            throw new Exception('Must provide submitter data before employer data');
        }
        
        // New Employer
        $this->cntEEER = 0;
        
        $this->setEmployer($data);
        
        // Employer
        $this->cntER ++;
        $this->createEmployer();
        
        return;
    }

    /**
     * Create next employee
     *
     * Form1095CUpstreamDetail
     *
     * @param array $data
     *            employee-specific data
     * @throws Exception employer data is not provided first
     */
    public function nextEmployee(array $data)
    {
        // If no employer yet, problem
        if (! $this->flagEmployer) {
            throw new Exception('Must provide employer data before employee data');
        }
        
        // New Employee
        $this->setEmployee($data);
        
        // Employee
        $this->cntEE ++;
        $this->cntEEER ++;
        $this->createEmployee();
        
        return;
    }

    /**
     * Create next dependent
     *
     * @param array $data
     *            employee/dependent-specific data
     * @throws Exception employee data is not provided first
     */
    public function nextDependent(array $data)
    {
        // If no employee yet, problem
        if (! $this->flagEmployee) {
            throw new Exception('Must provide employee data before dependent data');
        }
        
        // New Dependent
        $this->setDependent($data);
        
        // Dependent
        $this->createDependent();
        
        return;
    }

    /**
     * Wrap up and Save the file resources as XML files
     */
    public function complete()
    {
        $errors = $this->fileWrite('FormData');
        if (! empty($errors)) {
            throw new Exception('Error saving the Form Data file');
        }
        
        $this->createManifest();
        
        $errors = $this->fileWrite('Manifest');
        if (! empty($errors)) {
            throw new Exception('Error saving the Manifest file');
        }
        
        return;
    }

    /**
     * Retrieve the Driver class name
     *
     * @return string
     */
    public function getDriverClass()
    {
        return self::DRIVER;
    }

    /**
     * Set report data for use in file creation
     *
     * @param array $data
     *            the report data
     */
    public function setReport(array $data)
    {
        $this->reportData = $data;
    }

    /**
     * Get report data array for use in file creation
     *
     * @return array
     */
    public function getReport()
    {
        return $this->reportData;
    }

    /**
     * Set submitter data for use in file creation
     *
     * @param array $data
     *            the submitter data
     */
    public function setSubmitter(array $data)
    {
        $this->submitterData = $data;
        $this->flagSubmitter = TRUE;
        return;
    }

    /**
     * Get submitter data for use in file creation
     *
     * @return array
     */
    protected function getSubmitter()
    {
        return $this->submitterData;
    }

    /**
     * Set employer data for use in file creation
     *
     * @param array $data
     *            employer data
     */
    protected function setEmployer(array $data)
    {
        $this->employerData = $data;
        $this->flagEmployer = TRUE;
        return;
    }

    /**
     * Get employer data for use in file creation
     *
     * @return array employer data
     */
    protected function getEmployer()
    {
        return $this->employerData;
    }

    /**
     * Set employee data for use in file creation
     *
     * @param array $data
     *            employee data
     */
    protected function setEmployee(array $data)
    {
        $this->employeeData = $data;
        $this->flagEmployee = TRUE;
        return;
    }

    /**
     * Get employee data for use in file creation
     *
     * @return array employee data
     */
    protected function getEmployee()
    {
        return $this->employeeData;
    }

    /**
     * Set employee/dependent data for use in file creation
     *
     * @param array $data
     *            dependent data
     */
    protected function setDependent(array $data)
    {
        $this->dependentData = $data;
        return;
    }

    /**
     * Get employee/dependent data for use in file creation
     *
     * @return array dependent data
     */
    protected function getDependent()
    {
        return $this->dependentData;
    }

    /**
     * Get Offer of Coverage Codes Indicating Individual Coverage HRA
     *
     * @return array
     */
    protected function getIcHRA()
    {
        return $this->icHRA;
    }

    // ************************************** Create record types
    
    /**
     * Create Employer - Form 1094C
     *
     * Based on Tax Report and 1094C Cache data
     */
    protected function createEmployer()
    {
        $report = $this->getReport();
        
        $data = $this->getEmployer();
        $data['test_scenario_id'] = ''; // NOT SUPPORTED
        
        $xmlObj = $this->getFileResource('FormData');
        
        // namespaces
        $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty23';
        if ($report['TAX_YEAR'] == '2022') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty22';
	    } elseif ($report['TAX_YEAR'] == '2021') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty21';
        } elseif ($report['TAX_YEAR'] == '2020') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty20';
        } elseif ($report['TAX_YEAR'] == '2019') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty19';
        } elseif ($report['TAX_YEAR'] == '2018') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty18';
        } elseif ($report['TAX_YEAR'] == '2017') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty17';
        } elseif ($report['TAX_YEAR'] == '2016') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty16';
        } elseif ($report['TAX_YEAR'] == '2015') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:7.0';
        }
        $nsIrs = 'urn:us:gov:treasury:irs:common';
        
        // ******************************************************************
        //
        // Form 1094C
        //
        // ******************************************************************
        $xml1094C = $xmlObj->addChild('Form1094CUpstreamDetail', '', $nsNone);
        
        $tag = 'recordType';
        $val = '';
        $xml1094C->addAttribute($tag, $val);
        
        $tag = 'lineNum';
        $val = '0';
        $xml1094C->addAttribute($tag, $val);
        
        $tag = 'SubmissionId';
        $val = (string) $data['SUBMISSION_ID'];
        $xml1094C->addChild($tag, $val);
        
        if (isset($data['CORRECTED']) && $data['CORRECTED'] === 'R') {
            $tag = 'OriginalUniqueSubmissionId';
            $val = $this->format(self::FMT_STR, 64, $data, 'CORRECTED_SUBMISSION_ID');
            if (! empty($val)) {
                $xml1094C->addChild($tag, $val);
            }
        }
        
        if (isset($report['testfilecode']) && $report['testfilecode'] === 'T') {
            $tag = 'TestScenarioId';
            $val = $this->format(self::FMT_STR, 6, $data, 'test_scenario_id');
            if (! empty($val)) {
                $xml1094C->addChild($tag, $val);
            }
        }
        
        $tag = 'TaxYr';
        $val = $this->format(self::FMT_NBR, 4, $report, 'TAX_YEAR');
        if ($report['TAX_YEAR'] == '2015') {
            $xml1094C->addChild($tag, $val, $nsIrs);
        } else {
            $xml1094C->addChild($tag, $val);
        }
        
        $tag = 'CorrectedInd';
        $val = '0';
        if (isset($data['CORRECTED']) && $data['CORRECTED'] === '4') {
            $val = '1';
        }
        $xml1094C->addChild($tag, $val);
        
        if (isset($data['CORRECTED']) && $data['CORRECTED'] === '4') {
            $xmlCorrectedSubmissionInfoGrp = $xml1094C->addChild('CorrectedSubmissionInfoGrp');
            
            $tag = 'CorrectedUniqueSubmissionId';
            $val = $this->format(self::FMT_STR, 80, $data, 'CORRECTED_SUBMISSION_ID');
            $xmlCorrectedSubmissionInfoGrp->addChild($tag, $val);
            
            $xmlCorrectedSubmissionPayerName = $xmlCorrectedSubmissionInfoGrp->addChild('CorrectedSubmissionPayerName');
            
            $tag = 'BusinessNameLine1Txt';
            $val = $this->format(self::FMT_STR, 75, $data, 'CORR__BUSINESS_NAME_1', self::FMT_BUSNAM);
            $xmlCorrectedSubmissionPayerName->addChild($tag, $val);
            
            $tag = 'BusinessNameLine2Txt';
            $val = $this->format(self::FMT_STR, 75, $data, 'CORR__BUSINESS_NAME_2', self::FMT_BUSNAM2);
            if (! empty($val)) {
                $xmlCorrectedSubmissionPayerName->addChild($tag, $val);
            }
            
            $tag = 'CorrectedSubmissionPayerTIN';
            $val = $this->format(self::FMT_TIN, 9, $data, 'CORR__PAYER_TIN');
            if (! empty($val) || ($report['TAX_YEAR'] != '2015' && $report['TAX_YEAR'] != '2016')) {
                $xmlCorrectedSubmissionInfoGrp->addChild($tag, $val);
            }
        }
        
        // ******************************************************************
        //
        // Form 1094C Part I Applicable Large Employer Member (ALE Member)
        //
        // ******************************************************************
        $xml1094CPartI = $xml1094C->addChild('EmployerInformationGrp');
        
        $xmlBusinessName = $xml1094CPartI->addChild('BusinessName');
        
        // 1094C Part I Box/Line 1
        $tag = 'BusinessNameLine1Txt';
        $val = $this->format(self::FMT_STR, 75, $data, 'BUSINESS_NAME_1', self::FMT_BUSNAM);
        $xmlBusinessName->addChild($tag, $val);
        
        $tag = 'BusinessNameLine2Txt';
        $val = $this->format(self::FMT_STR, 75, $data, 'BUSINESS_NAME_2', self::FMT_BUSNAM2);
        if (! empty($val)) {
            $xmlBusinessName->addChild($tag, $val);
        }
        
        $tag = 'BusinessNameControlTxt';
        $val = $this->format(self::FMT_STR, 4, $data, 'BUSINESS_NAME_CONTROL', self::FMT_BUSNAMCTRL);
        if (! empty($val)) {
            $xml1094CPartI->addChild($tag, $val);
        }
        
        $tag = 'TINRequestTypeCd';
        $val = $this->format(self::FMT_STR, 15, $data, 'TIN_REQUEST_TYPE');
        if (! empty($val)) {
            $xml1094CPartI->addChild($tag, $val, $nsIrs);
        }
        
        // 1094C Part I Box/Line 2
        $tag = 'EmployerEIN';
        $val = $this->format(self::FMT_TIN, 9, $data, 'PAYER_TIN');
        $xml1094CPartI->addChild($tag, $val, $nsIrs);
        
        $xmlMailingAddressGrp = $xml1094CPartI->addChild('MailingAddressGrp');
        
        $xmlUSAddressGrp = $xmlMailingAddressGrp->addChild('USAddressGrp');
        
        // 1094C Part I Box/Line 3
        $tag = 'AddressLine1Txt';
        $val = $this->format(self::FMT_STR, 35, $data, 'ADDRESS_LINE_1', self::FMT_ADDR);
        $xmlUSAddressGrp->addChild($tag, $val);
        
        $tag = 'AddressLine2Txt';
        $val = $this->format(self::FMT_STR, 35, $data, 'ADDRESS_LINE_2', self::FMT_ADDR);
        if (! empty($val)) {
            $xmlUSAddressGrp->addChild($tag, $val);
        }
        
        // 1094C Part I Box/Line 4
        $tag = 'CityNm';
        $val = $this->format(self::FMT_STR, 22, $data, 'CITY', self::FMT_CITY);
        $xmlUSAddressGrp->addChild($tag, $val, $nsIrs);
        
        // 1094C Part I Box/Line 5
        $tag = 'USStateCd';
        $val = $this->format(self::FMT_STR, 2, $data, 'STATE');
        $xmlUSAddressGrp->addChild($tag, $val);
        
        // 1094C Part I Box/Line 6
        $tag = 'USZIPCd';
        $val = $this->format(self::FMT_ZIP, 5, $data, 'POSTAL_CODE');
        $xmlUSAddressGrp->addChild($tag, $val, $nsIrs);
        
        $tag = 'USZIPExtensionCd';
        $val = $this->format(self::FMT_ZXT, 4, $data, 'POSTAL_CODE');
        if (! empty($val)) {
            $xmlUSAddressGrp->addChild($tag, $val, $nsIrs);
        }
        
        // 1094C Part I Box/Line 7
        $xmlContactNameGrp = $xml1094CPartI->addChild('ContactNameGrp');
        
        $tag = 'PersonFirstNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'CONTACT_FIRST_NAME', self::FMT_NAME);
        $xmlContactNameGrp->addChild($tag, $val);
        
        $tag = 'PersonMiddleNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'CONTACT_MIDDLE_INITIAL', self::FMT_NAME);
        if (! empty($val)) {
            $xmlContactNameGrp->addChild($tag, $val);
        }
        
        $tag = 'PersonLastNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'CONTACT_LAST_NAME', self::FMT_NAME);
        $xmlContactNameGrp->addChild($tag, $val);
        
        $tag = 'SuffixNm';
        $val = $this->format(self::FMT_STR, 10, $data, 'CONTACT_SUFFIX', self::FMT_NAME);
        if (! empty($val)) {
            $xmlContactNameGrp->addChild($tag, $val);
        }
        
        // 1094C Part I Box/Line 8
        $tag = 'ContactPhoneNum';
        $len = 30;
        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
            $len = 15;
        }
        $val = $this->format(self::FMT_PHN, $len, $data, 'CONTACT_PHONE');
        if (! empty($val)) {
            $xml1094CPartI->addChild($tag, $val);
        }
        
        // NOT SUPPORTED $xml1094C->addChild ( 'GovtEntityEmployerInfoGrp' );
        
        // 1094C Part I Line 18
        $tag = 'Form1095CAttachedCnt';
        $val = $this->format(self::FMT_NBR, 10, $data, 'FORM_1095_ATTACHED_COUNT');
        $xml1094C->addChild($tag, $val);
        
        // ******************************************************************
        //
        // Form 1094C Part II ALE Member Information
        //
        // ******************************************************************
        // 1094C Part II Line 19
        $tag = 'AuthoritativeTransmittalInd';
        $val = $this->format(self::FMT_FLG, 1, $data, 'AUTHORITATIVE_TRANSMITTER');
        $xml1094C->addChild($tag, $val);
        
        // Complete lines 20-22 only for the Authoritative Transmittal
        if (isset($data['AUTHORITATIVE_TRANSMITTER']) && $data['AUTHORITATIVE_TRANSMITTER']) {
            
            // 1094C Part II Line 20
            $tag = 'TotalForm1095CALEMemberCnt';
            $val = $this->format(self::FMT_NBR, 10, $data, 'FORM_1095C_ALE_MEMBER_COUNT');
            $xml1094C->addChild($tag, $val);
            
            // 1094C Part II Line 21
            $tag = 'AggregatedGroupMemberCd';
            $val = $this->format(self::FMT_YN, 1, $data, 'AGGREGATED_ALE_GROUP');
            $xml1094C->addChild($tag, $val);
            
            // 1094C Part II Line 22 A
            $tag = 'QualifyingOfferMethodInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'QUALIFYING_OFFER_METHOD');
            $xml1094C->addChild($tag, $val);
            
            if ($report['TAX_YEAR'] == '2015') {
                // 1094C Part II Line 22 B
                $tag = 'QlfyOfferMethodTrnstReliefInd';
                $val = $this->format(self::FMT_FLG, 1, $data, 'QUALIFYING_OFFER_TRANS_RELIEF');
                $xml1094C->addChild($tag, $val);
            }
            
            if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                // 1094C Part II Line 22 C
                $tag = 'Section4980HReliefInd';
                $val = $this->format(self::FMT_FLG, 1, $data, 'SECTION_4980H_TRANS_RELIEF');
                $xml1094C->addChild($tag, $val);
            }
            
            // 1094C Part II Line 22 D
            $tag = 'NinetyEightPctOfferMethodInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'OFFER_METHOD_98_PERCENT');
            $xml1094C->addChild($tag, $val);
        }
        
        $tag = 'JuratSignaturePIN';
        $val = $this->format(self::FMT_JSPIN, 10, $data, 'JURAT_SIGNATURE_PIN');
        if (! empty($val)) {
            $xml1094C->addChild($tag, $val);
        }
        
        $tag = 'PersonTitleTxt';
        $val = $this->format(self::FMT_STR, 20, $data, 'CONTACT_PERSON_TITLE', self::FMT_NAME);
        if (! empty($val)) {
            $xml1094C->addChild($tag, $val);
        }
        
        $tag = 'SignatureDt';
        $val = $this->format(self::FMT_DTE, 10, $data, 'SIGNATURE_DATE', 'Y-m-d');
        if (! empty($val)) {
            if ($report['TAX_YEAR'] == '2015') {
                $xml1094C->addChild($tag, $val, $nsIrs);
            } else {
                $xml1094C->addChild($tag, $val);
            }
        }
        
        // Complete the remainder of the form only for the Authoritative Transmittal
        if (isset($data['AUTHORITATIVE_TRANSMITTER']) && $data['AUTHORITATIVE_TRANSMITTER']) {
            
            // ******************************************************************
            //
            // Form 1094C Part III ALE Member Information - Monthly
            //
            // ******************************************************************
            $xmlALEMemberInformationGrp = $xml1094C->addChild('ALEMemberInformationGrp');
            
            $yearlyMinEssCov = false;
            $yearlyFTECnt = false;
            $yearlyTotEmpCnt = false;
            $yearlyAggGrpInd = false;
            $yearlySect4980H = false;
            if (isset($data['MIN_COVERAGE_12_MONTHS']) && $data['MIN_COVERAGE_12_MONTHS']) {
                $yearlyMinEssCov = true;
            } else {
                $yearlyMinEssCov = $this->sameAll12Months($data, 'QUALIFYING_COVERAGE_', 'MIN_COVERAGE_');
            }
            if (isset($data['FULL_TIME_ALE_COUNT_12_MONTHS']) && $data['FULL_TIME_ALE_COUNT_12_MONTHS'] > 0) {
                $yearlyFTECnt = true;
            } else {
                $yearlyFTECnt = $this->sameAll12Months($data, 'FULL_TIME_ALE_COUNT_');
            }
            if (isset($data['EMPLOYEE_ALE_COUNT_12_MONTHS']) && $data['EMPLOYEE_ALE_COUNT_12_MONTHS'] > 0) {
                $yearlyTotEmpCnt = true;
            } else {
                $yearlyTotEmpCnt = $this->sameAll12Months($data, 'EMPLOYEE_ALE_COUNT_');
            }
            if (isset($data['AGGREGATED_ALE_GROUP']) && $data['AGGREGATED_ALE_GROUP']) {
                if (isset($data['AGGREGATED_GROUP_12_MONTHS']) && $data['AGGREGATED_GROUP_12_MONTHS']) {
                    $yearlyAggGrpInd = true;
                } else {
                    $yearlyAggGrpInd = $this->sameAll12Months($data, 'AGGREGATED_GROUP_');
                }
            }
            if (isset($data['SECTION_4980H_TRANS_RELIEF']) && $data['SECTION_4980H_TRANS_RELIEF']) {
                $yearlySect4980H = false;
                if ($report['TAX_YEAR'] == '2015') {
                    if (isset($data['SECTION_4980H_TRANS_12_MONTHS']) && trim($data['SECTION_4980H_TRANS_12_MONTHS']) != '') {
                        $yearlySect4980H = true;
                    } else {
                        $yearlySect4980H = $this->sameAll12Months($data, 'SECTION_4980H_TRANS_');
                    }
                }
            }
            if ($yearlyMinEssCov || $yearlyFTECnt || $yearlyTotEmpCnt || $yearlyAggGrpInd || $yearlySect4980H) {
                $xmlYearlyALEMemberDetail = $xmlALEMemberInformationGrp->addChild('YearlyALEMemberDetail');
            }
            
            $tag = 'MinEssentialCvrOffrCd';
            if ($yearlyMinEssCov) {
                $val = $this->format(self::FMT_FLG, 1, $data, 'MIN_COVERAGE_12_MONTHS');
                $xmlYearlyALEMemberDetail->addChild($tag, $val);
            } else {
                $xmlJanALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JanALEMonthlyInfoGrp');
                $xmlFebALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('FebALEMonthlyInfoGrp');
                $xmlMarALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MarALEMonthlyInfoGrp');
                $xmlAprALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AprALEMonthlyInfoGrp');
                $xmlMayALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MayALEMonthlyInfoGrp');
                $xmlJunALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JunALEMonthlyInfoGrp');
                $xmlJulALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JulALEMonthlyInfoGrp');
                $xmlAugALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AugALEMonthlyInfoGrp');
                $xmlSeptALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('SeptALEMonthlyInfoGrp');
                $xmlOctALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('OctALEMonthlyInfoGrp');
                $xmlNovALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('NovALEMonthlyInfoGrp');
                $xmlDecALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('DecALEMonthlyInfoGrp');
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_1');
                $xmlJanALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_2');
                $xmlFebALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_3');
                $xmlMarALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_4');
                $xmlAprALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_5');
                $xmlMayALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_6');
                $xmlJunALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_7');
                $xmlJulALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_8');
                $xmlAugALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_9');
                $xmlSeptALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_10');
                $xmlOctALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_11');
                $xmlNovALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_YN, 1, $data, 'QUALIFYING_COVERAGE_12');
                $xmlDecALEMonthlyInfoGrp->addChild($tag, $val);
            }
            
            if (! isset($data['OFFER_METHOD_98_PERCENT']) || ! $data['OFFER_METHOD_98_PERCENT']) {
                $tag = 'ALEMemberFTECnt';
                if ($yearlyFTECnt) {
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_12_MONTHS');
                    $xmlYearlyALEMemberDetail->addChild($tag, $val);
                } else {
                    if (! isset($xmlJanALEMonthlyInfoGrp)) {
                        $xmlJanALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JanALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlFebALEMonthlyInfoGrp)) {
                        $xmlFebALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('FebALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlMarALEMonthlyInfoGrp)) {
                        $xmlMarALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MarALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlAprALEMonthlyInfoGrp)) {
                        $xmlAprALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AprALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlMayALEMonthlyInfoGrp)) {
                        $xmlMayALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MayALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlJunALEMonthlyInfoGrp)) {
                        $xmlJunALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JunALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlJulALEMonthlyInfoGrp)) {
                        $xmlJulALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JulALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlAugALEMonthlyInfoGrp)) {
                        $xmlAugALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AugALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlSeptALEMonthlyInfoGrp)) {
                        $xmlSeptALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('SeptALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlOctALEMonthlyInfoGrp)) {
                        $xmlOctALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('OctALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlNovALEMonthlyInfoGrp)) {
                        $xmlNovALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('NovALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlDecALEMonthlyInfoGrp)) {
                        $xmlDecALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('DecALEMonthlyInfoGrp');
                    }
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_1');
                    $xmlJanALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_2');
                    $xmlFebALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_3');
                    $xmlMarALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_4');
                    $xmlAprALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_5');
                    $xmlMayALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_6');
                    $xmlJunALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_7');
                    $xmlJulALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_8');
                    $xmlAugALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_9');
                    $xmlSeptALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_10');
                    $xmlOctALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_11');
                    $xmlNovALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_NBR, 10, $data, 'FULL_TIME_ALE_COUNT_12');
                    $xmlDecALEMonthlyInfoGrp->addChild($tag, $val);
                }
            }
            
            $tag = 'TotalEmployeeCnt';
            if ($yearlyTotEmpCnt) {
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_12_MONTHS');
                $xmlYearlyALEMemberDetail->addChild($tag, $val);
            } else {
                if (! isset($xmlJanALEMonthlyInfoGrp)) {
                    $xmlJanALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JanALEMonthlyInfoGrp');
                }
                if (! isset($xmlFebALEMonthlyInfoGrp)) {
                    $xmlFebALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('FebALEMonthlyInfoGrp');
                }
                if (! isset($xmlMarALEMonthlyInfoGrp)) {
                    $xmlMarALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MarALEMonthlyInfoGrp');
                }
                if (! isset($xmlAprALEMonthlyInfoGrp)) {
                    $xmlAprALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AprALEMonthlyInfoGrp');
                }
                if (! isset($xmlMayALEMonthlyInfoGrp)) {
                    $xmlMayALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MayALEMonthlyInfoGrp');
                }
                if (! isset($xmlJunALEMonthlyInfoGrp)) {
                    $xmlJunALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JunALEMonthlyInfoGrp');
                }
                if (! isset($xmlJulALEMonthlyInfoGrp)) {
                    $xmlJulALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JulALEMonthlyInfoGrp');
                }
                if (! isset($xmlAugALEMonthlyInfoGrp)) {
                    $xmlAugALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AugALEMonthlyInfoGrp');
                }
                if (! isset($xmlSeptALEMonthlyInfoGrp)) {
                    $xmlSeptALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('SeptALEMonthlyInfoGrp');
                }
                if (! isset($xmlOctALEMonthlyInfoGrp)) {
                    $xmlOctALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('OctALEMonthlyInfoGrp');
                }
                if (! isset($xmlNovALEMonthlyInfoGrp)) {
                    $xmlNovALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('NovALEMonthlyInfoGrp');
                }
                if (! isset($xmlDecALEMonthlyInfoGrp)) {
                    $xmlDecALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('DecALEMonthlyInfoGrp');
                }
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_1');
                $xmlJanALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_2');
                $xmlFebALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_3');
                $xmlMarALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_4');
                $xmlAprALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_5');
                $xmlMayALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_6');
                $xmlJunALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_7');
                $xmlJulALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_8');
                $xmlAugALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_9');
                $xmlSeptALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_10');
                $xmlOctALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_11');
                $xmlNovALEMonthlyInfoGrp->addChild($tag, $val);
                
                $val = $this->format(self::FMT_NBR, 10, $data, 'EMPLOYEE_ALE_COUNT_12');
                $xmlDecALEMonthlyInfoGrp->addChild($tag, $val);
            }
            
            if (isset($data['AGGREGATED_ALE_GROUP']) && $data['AGGREGATED_ALE_GROUP']) {
                $tag = 'AggregatedGroupInd';
                if ($yearlyAggGrpInd) {
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_12_MONTHS');
                    $xmlYearlyALEMemberDetail->addChild($tag, $val);
                } else {
                    if (! isset($xmlJanALEMonthlyInfoGrp)) {
                        $xmlJanALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JanALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlFebALEMonthlyInfoGrp)) {
                        $xmlFebALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('FebALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlMarALEMonthlyInfoGrp)) {
                        $xmlMarALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MarALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlAprALEMonthlyInfoGrp)) {
                        $xmlAprALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AprALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlMayALEMonthlyInfoGrp)) {
                        $xmlMayALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MayALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlJunALEMonthlyInfoGrp)) {
                        $xmlJunALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JunALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlJulALEMonthlyInfoGrp)) {
                        $xmlJulALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JulALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlAugALEMonthlyInfoGrp)) {
                        $xmlAugALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AugALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlSeptALEMonthlyInfoGrp)) {
                        $xmlSeptALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('SeptALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlOctALEMonthlyInfoGrp)) {
                        $xmlOctALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('OctALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlNovALEMonthlyInfoGrp)) {
                        $xmlNovALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('NovALEMonthlyInfoGrp');
                    }
                    if (! isset($xmlDecALEMonthlyInfoGrp)) {
                        $xmlDecALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('DecALEMonthlyInfoGrp');
                    }
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_1');
                    $xmlJanALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_2');
                    $xmlFebALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_3');
                    $xmlMarALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_4');
                    $xmlAprALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_5');
                    $xmlMayALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_6');
                    $xmlJunALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_7');
                    $xmlJulALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_8');
                    $xmlAugALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_9');
                    $xmlSeptALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_10');
                    $xmlOctALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_11');
                    $xmlNovALEMonthlyInfoGrp->addChild($tag, $val);
                    
                    $val = $this->format(self::FMT_FLG, 1, $data, 'AGGREGATED_GROUP_12');
                    $xmlDecALEMonthlyInfoGrp->addChild($tag, $val);
                }
            }
            
            if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                if (isset($data['SECTION_4980H_TRANS_RELIEF']) && $data['SECTION_4980H_TRANS_RELIEF']) {
                    $tag = 'ALESect4980HTrnstReliefCd';
                    if ($yearlySect4980H) {
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_12_MONTHS');
                        $xmlYearlyALEMemberDetail->addChild($tag, $val);
                    } else {
                        if (! isset($xmlJanALEMonthlyInfoGrp)) {
                            $xmlJanALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JanALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlFebALEMonthlyInfoGrp)) {
                            $xmlFebALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('FebALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlMarALEMonthlyInfoGrp)) {
                            $xmlMarALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MarALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlAprALEMonthlyInfoGrp)) {
                            $xmlAprALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AprALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlMayALEMonthlyInfoGrp)) {
                            $xmlMayALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('MayALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlJunALEMonthlyInfoGrp)) {
                            $xmlJunALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JunALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlJulALEMonthlyInfoGrp)) {
                            $xmlJulALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('JulALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlAugALEMonthlyInfoGrp)) {
                            $xmlAugALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('AugALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlSeptALEMonthlyInfoGrp)) {
                            $xmlSeptALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('SeptALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlOctALEMonthlyInfoGrp)) {
                            $xmlOctALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('OctALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlNovALEMonthlyInfoGrp)) {
                            $xmlNovALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('NovALEMonthlyInfoGrp');
                        }
                        if (! isset($xmlDecALEMonthlyInfoGrp)) {
                            $xmlDecALEMonthlyInfoGrp = $xmlALEMemberInformationGrp->addChild('DecALEMonthlyInfoGrp');
                        }
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_1');
                        $xmlJanALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_2');
                        $xmlFebALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_3');
                        $xmlMarALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_4');
                        $xmlAprALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_5');
                        $xmlMayALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_6');
                        $xmlJunALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_7');
                        $xmlJulALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_8');
                        $xmlAugALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_9');
                        $xmlSeptALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_10');
                        $xmlOctALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_11');
                        $xmlNovALEMonthlyInfoGrp->addChild($tag, $val);
                        
                        $val = $this->format(self::FMT_STR, 1, $data, 'SECTION_4980H_TRANS_12');
                        $xmlDecALEMonthlyInfoGrp->addChild($tag, $val);
                    }
                }
            }
            
            // ******************************************************************
            //
            // Form 1094C Part IV Other ALE Members of Aggregated ALE Group
            //
            // ******************************************************************
            if (isset($data['AGGREGATED_ALE_GROUP']) && $data['AGGREGATED_ALE_GROUP'] && isset($data['OtherALEMembers'])) {
                foreach ($data['OtherALEMembers'] as $member) {
                    $xmlOtherALEMembersGrp = $xml1094C->addChild('OtherALEMembersGrp');
                    
                    $xmlBusinessNameOth = $xmlOtherALEMembersGrp->addChild('BusinessName');
                    
                    $tag = 'BusinessNameLine1Txt';
                    $val = $this->format(self::FMT_STR, 75, $member, 'BUSINESS_NAME_1', self::FMT_BUSNAM);
                    $xmlBusinessNameOth->addChild($tag, $val);
                    
                    $tag = 'BusinessNameLine2Txt';
                    $val = $this->format(self::FMT_STR, 75, $member, 'BUSINESS_NAME_2', self::FMT_BUSNAM2);
                    if (! empty($val)) {
                        $xmlBusinessNameOth->addChild($tag, $val);
                    }
                    
                    $tag = 'BusinessNameControlTxt';
                    $val = $this->format(self::FMT_STR, 4, $member, 'BUSINESS_NAME_CONTROL', self::FMT_BUSNAMCTRL);
                    if (! empty($val)) {
                        $xmlOtherALEMembersGrp->addChild($tag, $val);
                    }
                    
                    $tag = 'TINRequestTypeCd';
                    $val = $this->format(self::FMT_STR, 15, $member, 'TIN_REQUEST_TYPE');
                    if (! empty($val)) {
                        $xmlOtherALEMembersGrp->addChild($tag, $val, $nsIrs);
                    }
                    
                    $tag = 'EIN';
                    $val = $this->format(self::FMT_TIN, 9, $member, 'PAYER_TIN');
                    $xmlOtherALEMembersGrp->addChild($tag, $val, $nsIrs);
                }
            }
        }
        
        $this->setFileResource('FormData', $xmlObj);
    }

    /**
     * Create Employee - Form 1095C Parts I and II
     *
     * Based on Employee data
     */
    protected function createEmployee()
    {
        $report = $this->getReport();
        
        $employer = $this->getEmployer();
        
        $data = $this->getEmployee();
        $data['test_scenario_id'] = ''; // NOT SUPPORTED
        
        $xmlObj = $this->getFileResource('FormData');
        $rx = $this->cntER - 1;
        $xmlNode = $xmlObj->Form1094CUpstreamDetail[$rx];
        
        // namespaces
        $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty21';
        if ($report['TAX_YEAR'] == '2020') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty20';
        } elseif ($report['TAX_YEAR'] == '2019') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty19';
        } elseif ($report['TAX_YEAR'] == '2018') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty18';
        } elseif ($report['TAX_YEAR'] == '2017') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty17';
        } elseif ($report['TAX_YEAR'] == '2016') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty16';
        } elseif ($report['TAX_YEAR'] == '2015') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:7.0';
        }
        $nsIrs = 'urn:us:gov:treasury:irs:common';
        
        // ******************************************************************
        //
        // Form 1095C
        //
        // ******************************************************************
        $xml1095C = $xmlNode->addChild('Form1095CUpstreamDetail');
        
        $tag = 'recordType';
        $val = '';
        $xml1095C->addAttribute($tag, $val);
        
        $tag = 'lineNum';
        $val = '0';
        $xml1095C->addAttribute($tag, $val);
        
        $tag = 'RecordId';
        $val = (string) $data['RECORD_ID'];
        $xml1095C->addChild($tag, $val);
        
        $tag = 'TestScenarioId';
        $val = $this->format(self::FMT_STR, 5, $data, 'test_scenario_id');
        if (! empty($val)) {
            $xml1095C->addChild($tag, $val);
        }
        
        $tag = 'CorrectedInd';
        $val = '0';
        if (isset($employer['CORRECTED']) && $employer['CORRECTED'] === '5') {
            $val = '1';
        }
        $xml1095C->addChild($tag, $val);
        
        if (isset($employer['CORRECTED']) && $employer['CORRECTED'] === '5') {
            if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                $xmlCorrectedRecordInfoGrp = $xml1095C->addChild('CorrectedRecordInfoGrp');
            } else {
                $xmlCorrectedRecordInfoGrp = $xml1095C->addChild('CorrectedRecordRecipientGrp');
            }
            
            $tag = 'CorrectedUniqueRecordId';
            $val = $this->format(self::FMT_STR, 80, $data, 'CORRECTED_UNIQUE_RECORD_ID');
            $xmlCorrectedRecordInfoGrp->addChild($tag, $val);
            
            if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                $xmlCorrectedRecordPayeeName = $xmlCorrectedRecordInfoGrp->addChild('CorrectedRecordPayeeName');
            } else {
                $xmlCorrectedRecordPayeeName = $xmlCorrectedRecordInfoGrp->addChild('CorrectedRecRecipientPrsnName');
            }
            
            $tag = 'PersonFirstNm';
            $val = $this->format(self::FMT_STR, 20, $data, 'CORR__FIRST_NAME', self::FMT_NAME);
            $xmlCorrectedRecordPayeeName->addChild($tag, $val);
            
            $tag = 'PersonMiddleNm';
            $val = $this->format(self::FMT_STR, 20, $data, 'CORR__MIDDLE_NAME', self::FMT_NAME);
            if (! empty($val)) {
                $xmlCorrectedRecordPayeeName->addChild($tag, $val);
            }
            
            $tag = 'PersonLastNm';
            $val = $this->format(self::FMT_STR, 20, $data, 'CORR__LAST_NAME', self::FMT_NAME);
            $xmlCorrectedRecordPayeeName->addChild($tag, $val);
            
            // NOT SUPPORTED by any source data. OPTIONAL per schema definition.
            // $tag = 'SuffixNm';
            // $val = $this->format ( self::FMT_STR, 10, $data, 'corr__suffix', self::FMT_NAME );
            // if (! empty ( $val )) {
            // $xmlCorrectedRecordPayeeName->addChild ( $tag, $val );
            // }
            
            $tag = 'CorrectedRecRecipientTIN';
            if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                $tag = 'CorrectedRecordPayeeTIN';
            }
            $val = $this->format(self::FMT_TIN, 9, $data, 'CORR__SOCIAL_SECURITY_NUMBER');
            if (! empty($val)) {
                $xmlCorrectedRecordInfoGrp->addChild($tag, $val);
            }
        }
        
        $tag = 'TaxYr';
        $val = $this->format(self::FMT_NBR, 4, $report, 'TAX_YEAR');
        if (! empty($val)) {
            if ($report['TAX_YEAR'] == '2015') {
                $xml1095C->addChild($tag, $val, $nsIrs);
            } else {
                $xml1095C->addChild($tag, $val);
            }
        }
        
        // ******************************************************************
        //
        // Form 1095C Part I Employee
        //
        // ******************************************************************
        $xmlEmployeeInfoGrp = $xml1095C->addChild('EmployeeInfoGrp');
        
        $xmlOtherCompletePersonName = $xmlEmployeeInfoGrp->addChild('OtherCompletePersonName');
        
        $tag = 'PersonFirstNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'FIRST_NAME', self::FMT_NAME);
        $xmlOtherCompletePersonName->addChild($tag, $val);
        
        $tag = 'PersonMiddleNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'MIDDLE_NAME', self::FMT_NAME);
        if (! empty($val)) {
            $xmlOtherCompletePersonName->addChild($tag, $val);
        }
        
        $tag = 'PersonLastNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'LAST_NAME', self::FMT_NAME);
        $xmlOtherCompletePersonName->addChild($tag, $val);
        
        // NOT SUPPORTED by any source data. OPTIONAL per schema definition.
        // $tag = 'SuffixNm';
        // $val = $this->format ( self::FMT_STR, 10, $data, 'suffix', self::FMT_NAME );
        // if (! empty ( $val )) {
        // $xmlOtherCompletePersonName->addChild ( $tag, $val );
        // }
        
        // NOT SUPPORTED by any source data. OPTIONAL per schema definition.
        // $tag = 'PersonNameControlTxt';
        // $val = $this->format ( self::FMT_STR, 4, $data, 'name_control' );
        // if (! empty ( $val )) {
        // $xmlEmployeeInfoGrp->addChild ( $tag, $val );
        // }
        
        $ssn = $this->format(self::FMT_TIN, 9, $data, 'SOCIAL_SECURITY_NUMBER');
        if (! empty($ssn)) {
            $tag = 'TINRequestTypeCd';
            $val = $this->format(self::FMT_STR, 15, $data, 'TIN_REQUEST_TYPE');
            if (! empty($val)) {
                $xmlEmployeeInfoGrp->addChild($tag, $val, $nsIrs);
            }
            $tag = 'SSN';
            $xmlEmployeeInfoGrp->addChild($tag, $ssn, $nsIrs);
        }
        
        $xmlMailingAddressGrp = $xmlEmployeeInfoGrp->addChild('MailingAddressGrp');
        
        $xmlUSAddressGrp = $xmlMailingAddressGrp->addChild('USAddressGrp');
        
        $tag = 'AddressLine1Txt';
        $val = $this->format(self::FMT_STR, 35, $data, 'ADDRESS_LINE_1', self::FMT_ADDR);
        $xmlUSAddressGrp->addChild($tag, $val);
        
        $tag = 'AddressLine2Txt';
        $val = $this->format(self::FMT_STR, 35, $data, 'ADDRESS_LINE_2', self::FMT_ADDR);
        if (! empty($val)) {
            $xmlUSAddressGrp->addChild($tag, $val);
        }
        
        $tag = 'CityNm';
        $val = $this->format(self::FMT_STR, 22, $data, 'CITY', self::FMT_CITY);
        $xmlUSAddressGrp->addChild($tag, $val, $nsIrs);
        
        $tag = 'USStateCd';
        $val = $this->format(self::FMT_STR, 2, $data, 'STATE');
        $xmlUSAddressGrp->addChild($tag, $val);
        
        $tag = 'USZIPCd';
        $val = $this->format(self::FMT_ZIP, 5, $data, 'POSTAL_CODE');
        $xmlUSAddressGrp->addChild($tag, $val, $nsIrs);
        
        $tag = 'USZIPExtensionCd';
        $val = $this->format(self::FMT_ZXT, 4, $data, 'POSTAL_CODE');
        if (! empty($val)) {
            $xmlUSAddressGrp->addChild($tag, $val, $nsIrs);
        }
        
        $tag = 'ALEContactPhoneNum';
        $len = 30;
        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
            $len = 15;
        }
        $val = $this->format(self::FMT_PHN, $len, $employer, 'CONTACT_PHONE');
        if (! empty($val)) {
            $xml1095C->addChild($tag, $val);
        }
        
        // ******************************************************************
        //
        // Form 1095C Part II Employee Offer and Coverage
        //
        // ******************************************************************
        $tag = 'StartMonthNumberCd';
        // include leading zero
        $val = $this->format(self::FMT_NBR, 2, $data, 'PLAN_START_MONTH', '%02u');
        $xml1095C->addChild($tag, $val);

        // If the employee was offered an individual coverage HRA,
        // this is the employee’s age on January 1 of the tax year being reported.
        if ($this->isICHRA()) {
            $tag = 'AgeNum';
            $val = (string) $data['EMPLOYEE_AGE'];
            $xml1095C->addChild($tag, $val);
        }
        
        $xmlEmployeeOfferAndCoverageGrp = $xml1095C->addChild('EmployeeOfferAndCoverageGrp');
        
        // 1095C Part II Line 14
        if (isset($data['COVERAGE_CODE_12_MONTHS']) && trim($data['COVERAGE_CODE_12_MONTHS']) != '') {
            $tag = 'AnnualOfferOfCoverageCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_12_MONTHS');
            $xmlEmployeeOfferAndCoverageGrp->addChild($tag, $val);
        } else {
            
            $tag = 'JanOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_1');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'FebOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_2');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'MarOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_3');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'AprOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_4');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'MayOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_5');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'JunOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_6');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'JulOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_7');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'AugOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_8');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'SepOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_9');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'OctOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_10');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'NovOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_11');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
            
            $tag = 'DecOfferCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'COVERAGE_CODE_12');
            if (! empty($val)) {
                if (! isset($xmlMonthlyOfferCoverageGrp)) {
                    $xmlMonthlyOfferCoverageGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyOfferCoverageGrp');
                }
                $xmlMonthlyOfferCoverageGrp->addChild($tag, $val);
            }
        }
        
        // 1095C Part II Line 15
        if ($report['TAX_YEAR'] == '2020') {
            $completeLine15 = array(
                '1B',
                '1C',
                '1D',
                '1E',
                '1J',
                '1K',
                '1L',
                '1M',
                '1N',
                '1O',
                '1P',
                '1Q',
                '1T',
                '1U'
            );
        } elseif ($report['TAX_YEAR'] == '2015') {
            $completeLine15 = array(
                '1B',
                '1C',
                '1D',
                '1E'
            );
        } else {
            $completeLine15 = array(
                '1B',
                '1C',
                '1D',
                '1E',
                '1J',
                '1K'
            );
        }
        if (isset($data['EMPLOYEE_SHARE_12_MONTHS']) && (float) $data['EMPLOYEE_SHARE_12_MONTHS']) {
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15)) {
                $tag = 'AnnlEmployeeRequiredContriAmt ';
                if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                    $tag = 'AnnlShrLowestCostMthlyPremAmt';
                }
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_12_MONTHS');
                $xmlEmployeeOfferAndCoverageGrp->addChild($tag, $val);
            }
        } else {
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_1'], $completeLine15)) {
                $tag = 'JanuaryAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_1');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_2'], $completeLine15)) {
                $tag = 'FebruaryAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_2');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_3'], $completeLine15)) {
                $tag = 'MarchAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_3');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_4'], $completeLine15)) {
                $tag = 'AprilAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_4');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_5'], $completeLine15)) {
                $tag = 'MayAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_5');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_6'], $completeLine15)) {
                $tag = 'JuneAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_6');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_7'], $completeLine15)) {
                $tag = 'JulyAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_7');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_8'], $completeLine15)) {
                $tag = 'AugustAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_8');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_9'], $completeLine15)) {
                $tag = 'SeptemberAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_9');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_10'], $completeLine15)) {
                $tag = 'OctoberAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_10');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_11'], $completeLine15)) {
                $tag = 'NovemberAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_11');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
            
            if (in_array($data['COVERAGE_CODE_12_MONTHS'], $completeLine15) || in_array($data['COVERAGE_CODE_12'], $completeLine15)) {
                $tag = 'DecemberAmt';
                $val = $this->format(self::FMT_CUR, 19, $data, 'EMPLOYEE_SHARE_12');
                if (! empty($val)) {
                    if (! isset($xmlMonthlyShareOfLowestCostMonthlyPremGrp)) {
                        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyShareOfLowestCostMonthlyPremGrp');
                        } else {
                            $xmlMonthlyShareOfLowestCostMonthlyPremGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyEmployeeRequiredContriGrp');
                        }
                    }
                    $xmlMonthlyShareOfLowestCostMonthlyPremGrp->addChild($tag, $val);
                }
            }
        }
        
        // 1095C Part II Line 16
        if (isset($data['SECTION_4980H_SAFE_12_MONTHS']) && trim($data['SECTION_4980H_SAFE_12_MONTHS']) != '') {
            $tag = 'AnnualSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_12_MONTHS');
            if (! empty($val)) {
                $xmlEmployeeOfferAndCoverageGrp->addChild($tag, $val);
            }
        } else {
            
            $tag = 'JanSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_1');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'FebSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_2');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'MarSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_3');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'AprSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_4');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'MaySafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_5');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'JunSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_6');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'JulSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_7');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'AugSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_8');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'SepSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_9');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'OctSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_10');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'NovSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_11');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
            
            $tag = 'DecSafeHarborCd';
            $val = $this->format(self::FMT_STR, 2, $data, 'SECTION_4980H_SAFE_HARBOR_12');
            if (! empty($val)) {
                if (! isset($xmlMonthlySafeHarborGrp)) {
                    $xmlMonthlySafeHarborGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlySafeHarborGrp');
                }
                $xmlMonthlySafeHarborGrp->addChild($tag, $val);
            }
        }
        
        // 1095C Part II Line 17
        // If the employee was offered an individual coverage HRA,
        // enter the appropriate ZIP code used to identify the lowest cost silver plan
        // used to calculate the Employee Required Contribution in line 15
        if ($this->isICHRA()) {
            if (isset($data['ZIP_CODE_12_MONTHS']) && trim($data['ZIP_CODE_12_MONTHS']) != '') {
                    $tag = 'AnnualICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_12_MONTHS');
                    $xmlEmployeeOfferAndCoverageGrp->addChild($tag, $val);
            } else {
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_1'], $this->getIcHRA())) {
                    $tag = 'JanICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_1');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_2'], $this->getIcHRA())) {
                    $tag = 'FebICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_2');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_3'], $this->getIcHRA())) {
                    $tag = 'MarICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_3');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_4'], $this->getIcHRA())) {
                    $tag = 'AprICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_4');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_5'], $this->getIcHRA())) {
                    $tag = 'MayICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_5');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_6'], $this->getIcHRA())) {
                    $tag = 'JunICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_6');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_7'], $this->getIcHRA())) {
                    $tag = 'JulICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_7');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_8'], $this->getIcHRA())) {
                    $tag = 'AugICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_8');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_9'], $this->getIcHRA())) {
                    $tag = 'SepICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_9');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_10'], $this->getIcHRA())) {
                    $tag = 'OctICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_10');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_11'], $this->getIcHRA())) {
                    $tag = 'NovICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_11');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
                
                if (in_array($data['COVERAGE_CODE_12_MONTHS'], $this->getIcHRA()) || in_array($data['COVERAGE_CODE_12'], $this->getIcHRA())) {
                    $tag = 'DecICHRAZipCd';
                    $val = $this->format(self::FMT_ZIP, 5, $data, 'ZIP_CODE_12');
                    if (! empty($val)) {
                        if (! isset($xmlMonthlyZipCodeGrp)) {
                            $xmlMonthlyZipCodeGrp = $xmlEmployeeOfferAndCoverageGrp->addChild('MonthlyICHRAZipCdGrp');
                        }
                        $xmlMonthlyZipCodeGrp->addChild($tag, $val);
                    }
                }
            }
        }
        
        // ******************************************************************
        //
        // Form 1095C Part III Covered Individuals
        //
        // ******************************************************************
        $tag = 'CoveredIndividualInd';
        $val = $this->format(self::FMT_FLG, 1, $data, 'COVERED_INDIVIDUAL');
        $xml1095C->addChild($tag, $val);
        
        $this->setFileResource('FormData', $xmlObj);
    }

    /**
     * Create Dependent - Form 1095C Part III
     *
     * Based on Dependent data
     */
    protected function createDependent()
    {
        $report = $this->getReport();
        
        $data = $this->getDependent();
        
        $xmlObj = $this->getFileResource('FormData');
        $rx = $this->cntER - 1;
        $ex = $this->cntEEER - 1;
        $xmlNode = $xmlObj->Form1094CUpstreamDetail[$rx]->Form1095CUpstreamDetail[$ex];
        
        // namespaces
        $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty23';
        if ($report['TAX_YEAR'] == '2022') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty22';
	            } elseif ($report['TAX_YEAR'] == '2021') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty21';
        } elseif ($report['TAX_YEAR'] == '2020') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty20';
        } elseif ($report['TAX_YEAR'] == '2019') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty19';
        } elseif ($report['TAX_YEAR'] == '2018') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty18';
        } elseif ($report['TAX_YEAR'] == '2017') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty17';
        } elseif ($report['TAX_YEAR'] == '2016') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty16';
        } elseif ($report['TAX_YEAR'] == '2015') {
            $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:7.0';
        }
        $nsIrs = 'urn:us:gov:treasury:irs:common';
        
        // ******************************************************************
        //
        // Form 1095C Part III Covered Individuals
        //
        // ******************************************************************
        $xmlCoveredIndividualGrp = $xmlNode->addChild('CoveredIndividualGrp');
        
        $xmlCoveredIndividualName = $xmlCoveredIndividualGrp->addChild('CoveredIndividualName');
        
        $tag = 'PersonFirstNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'FIRST_NAME', self::FMT_NAME);
        $xmlCoveredIndividualName->addChild($tag, $val);
        
        $tag = 'PersonMiddleNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'MIDDLE_NAME', self::FMT_NAME);
        if (! empty($val)) {
            $xmlCoveredIndividualName->addChild($tag, $val);
        }
        
        $tag = 'PersonLastNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'LAST_NAME', self::FMT_NAME);
        $xmlCoveredIndividualName->addChild($tag, $val);
        
        $tag = 'SuffixNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'SUFFIX', self::FMT_NAME);
        if (! empty($val)) {
            $xmlCoveredIndividualName->addChild($tag, $val);
        }
        
        // NOT SUPPORTED by any source data. OPTIONAL per schema definition.
        // $tag = 'PersonNameControlTxt';
        // $val = $this->format ( self::FMT_STR, 4, $data, 'name_control' );
        // if (! empty ( $val )) {
        // $xmlCoveredIndividualGrp->addChild ( $tag, $val );
        // }
        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016' || $report['TAX_YEAR'] == '2017') {
            $ssn = $this->format(self::FMT_TIN, 9, $data, 'SOCIAL_SECURITY_NUMBER');
            if (! empty($ssn)) {
                $tag = 'TINRequestTypeCd';
                $val = $this->format(self::FMT_STR, 15, $data, 'TIN_REQUEST_TYPE');
                if (! empty($val)) {
                    $xmlCoveredIndividualGrp->addChild($tag, $val, $nsIrs);
                }
                $tag = 'SSN';
                $xmlCoveredIndividualGrp->addChild($tag, $ssn, $nsIrs);
            }
            $tag = 'BirthDt';
            $val = $this->format(self::FMT_DTE, 10, $data, 'DATE_OF_BIRTH', 'Y-m-d');
            if (! empty($val)) {
                if ($report['TAX_YEAR'] == '2015') {
                    $xmlCoveredIndividualGrp->addChild($tag, $val, $nsIrs);
                } else {
                    $xmlCoveredIndividualGrp->addChild($tag, $val);
                }
            }
        } else {
            $ssn = $this->format(self::FMT_TIN, 9, $data, 'SOCIAL_SECURITY_NUMBER');
            if (! empty($ssn)) {
                $tag = 'TINRequestTypeCd';
                $val = $this->format(self::FMT_STR, 15, $data, 'TIN_REQUEST_TYPE');
                if (! empty($val)) {
                    $xmlCoveredIndividualGrp->addChild($tag, $val, $nsIrs);
                }
                $tag = 'SSN';
                $xmlCoveredIndividualGrp->addChild($tag, $ssn, $nsIrs);
            } else {
                $tag = 'BirthDt';
                $val = $this->format(self::FMT_DTE, 10, $data, 'DATE_OF_BIRTH', 'Y-m-d');
                if (! empty($val)) {
                    if ($report['TAX_YEAR'] == '2015') {
                        $xmlCoveredIndividualGrp->addChild($tag, $val, $nsIrs);
                    } else {
                        $xmlCoveredIndividualGrp->addChild($tag, $val);
                    }
                }
            }
        }
        
        if (isset($data['COVERAGE_CODE_12_MONTHS']) && $data['COVERAGE_CODE_12_MONTHS']) {
            $tag = 'CoveredIndividualAnnualInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_12_MONTHS');
            $xmlCoveredIndividualGrp->addChild($tag, $val);
        } else {
            $xmlCoveredIndividualMonthlyIndGrp = $xmlCoveredIndividualGrp->addChild('CoveredIndividualMonthlyIndGrp');
            
            $tag = 'JanuaryInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_1');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'FebruaryInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_2');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'MarchInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_3');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'AprilInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_4');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'MayInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_5');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'JuneInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_6');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'JulyInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_7');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'AugustInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_8');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'SeptemberInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_9');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'OctoberInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_10');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'NovemberInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_11');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
            
            $tag = 'DecemberInd';
            $val = $this->format(self::FMT_FLG, 1, $data, 'COVERAGE_CODE_12');
            $xmlCoveredIndividualMonthlyIndGrp->addChild($tag, $val);
        }
        
        $this->setFileResource('FormData', $xmlObj);
    }

    /**
     * Create Manifest file
     *
     * Based on Submitter
     */
    protected function createManifest()
    {
        $report = $this->getReport();
        $report['prior_year'] = '0';
        if ($report['TAX_YEAR'] != self::CURRENT_YEAR) {
            $report['prior_year'] = '1';
        }
        
        $data = $this->getSubmitter();
        if (isset($data['CONTACT_NAME'])) {
            if (strpos($data['CONTACT_NAME'], ',') !== false) {
                list ($data['CONTACT_FAMILY_NAME'], $data['CONTACT_GIVEN_NAME']) = explode(',', $data['CONTACT_NAME']);
            } else {
                list ($data['CONTACT_GIVEN_NAME'], $data['CONTACT_FAMILY_NAME']) = explode(' ', $data['CONTACT_NAME']);
            }
        }
        $data['vendorContactNameFirst'] = $this->vendorContactNameFirst;
        $data['vendorContactNameMiddle'] = $this->vendorContactNameMiddle;
        $data['vendorContactNameLast'] = $this->vendorContactNameLast;
        $data['vendorContactNameSuffix'] = $this->vendorContactNameSuffix;
        $data['countER'] = $this->cntER;
        $data['countEE'] = $this->cntEE;
        $data['timestamp'] = $this->getTimestamp();
        $fileName = $this->getFilename('FormData');
        $data['hash'] = hash_file('sha256', $fileName);
        $data['size'] = filesize($fileName);
        $data['name'] = substr(strrchr($fileName, "/"), 1);
        
        $xmlObj = $this->getFileResource('Manifest');
        
        // namespaces

        $nsNone = 'urn:us:gov:treasury:irs:ext:aca:air:ty23';
        $nsIrs = 'urn:us:gov:treasury:irs:common';
        $nsAcabushdr = 'urn:us:gov:treasury:irs:msg:acabusinessheader';
        
        // ACATransmitterBusinessHeaderRequest*******************************
        $xmlHeader = $xmlObj->addChild('ACABusinessHeader', '', $nsAcabushdr);
        
        $tag = 'UniqueTransmissionId';
        $val = trim($report['UNIQUE_TRANSMISSION_ID']);
        $xmlHeader->addChild($tag, $val, $nsNone);
        
        $tag = 'Timestamp';
        $val = trim($data['timestamp']);
        $xmlHeader->addChild($tag, $val, $nsIrs);
        
        // ACATransmitterManifestReqDtl**************************************
        $xmlDetail = $xmlObj->addChild('ACATransmitterManifestReqDtl', '', $nsNone);
        
        $tag = 'PaymentYr';
        $val = $this->format(self::FMT_NBR, 4, $report, 'TAX_YEAR');
        $xmlDetail->addChild($tag, $val);
        
        $tag = 'PriorYearDataInd';
        $val = $this->format(self::FMT_FLG, 1, $report, 'prior_year');
        $xmlDetail->addChild($tag, $val);
        
        $tag = 'EIN';
        $val = $this->format(self::FMT_TIN, 9, $data, 'SUBMITTER_EIN');
        $xmlDetail->addChild($tag, $val, $nsIrs);
        
        $tag = 'TransmissionTypeCd';
        $val = $this->format(self::FMT_STR, 1, $report, 'ACA_TRANSMISSION_TYPE');
        $xmlDetail->addChild($tag, $val);
        
        $tag = 'TestFileCd';
        $val = $this->format(self::FMT_STR, 1, $report, 'testfilecode');
        $xmlDetail->addChild($tag, $val);
        
        if (isset($report['ACA_TRANSMISSION_TYPE']) && $report['ACA_TRANSMISSION_TYPE'] == 'R') {
            $tag = 'OriginalReceiptId';
            $val = $this->format(self::FMT_STR, 80, $report, 'ORIGINAL_RECEIPT_ID');
            if (! empty($val)) {
                $xmlDetail->addChild($tag, $val);
            }
        }
        
        // Do we support a foreign transmitter? NO
        // $tag = 'TransmitterForeignEntityInd';
        // $val = '?';
        // $xmlDetail->addChild ( $tag, $val );
        
        // TransmitterNameGrp************************************************
        $xmlTransmitterNameGrp = $xmlDetail->addChild('TransmitterNameGrp');
        
        $tag = 'BusinessNameLine1Txt';
        $val = $this->format(self::FMT_STR, 75, $data, 'SUBMITTER_NAME', self::FMT_BUSNAM);
        $xmlTransmitterNameGrp->addChild($tag, $val);
        
        // Not supported by any source data. Optional per schema definition.
        // $tag = 'BusinessNameLine2Txt';
        // $val = '?';
        // $xmlTransmitterNameGrp->addChild ( $tag, $val );
        
        // CompanyInformationGrp*********************************************
        $xmlCompanyInformationGrp = $xmlDetail->addChild('CompanyInformationGrp');
        
        $tag = 'CompanyNm';
        $val = $this->format(self::FMT_STR, 75, $data, 'COMPANY_NAME', self::FMT_BUSNAM);
        $xmlCompanyInformationGrp->addChild($tag, $val);
        
        // MailingAddressGrp*************************************************
        $xmlMailingAddressGrp = $xmlCompanyInformationGrp->addChild('MailingAddressGrp');
        
        // USAddressGrp******************************************************
        $xmlUSAddressGrp = $xmlMailingAddressGrp->addChild('USAddressGrp');
        
        $tag = 'AddressLine1Txt';
        $val = $this->format(self::FMT_STR, 35, $data, 'SUBMITTER_ADDRESS_ID__ADDRESS_LINE_1', self::FMT_ADDR);
        $xmlUSAddressGrp->addChild($tag, $val);
        
        $tag = 'AddressLine2Txt';
        $val = $this->format(self::FMT_STR, 35, $data, 'SUBMITTER_ADDRESS_ID__ADDRESS_LINE_2', self::FMT_ADDR);
        if (! empty($val)) { // Optional per schema definition.
            $xmlUSAddressGrp->addChild($tag, $val);
        }
        
        $tag = 'CityNm';
        $val = $this->format(self::FMT_STR, 22, $data, 'SUBMITTER_ADDRESS_ID__CITY', self::FMT_CITY);
        $xmlUSAddressGrp->addChild($tag, $val, $nsIrs);
        
        $tag = 'USStateCd';
        $val = $this->format(self::FMT_STR, 2, $data, 'SUBMITTER_ADDRESS_ID__SUBDIVISION_CODE');
        $xmlUSAddressGrp->addChild($tag, $val);
        
        $tag = 'USZIPCd';
        $val = $this->format(self::FMT_ZIP, 5, $data, 'SUBMITTER_ADDRESS_ID__POSTAL_CODE');
        $xmlUSAddressGrp->addChild($tag, $val, $nsIrs);
        
        $tag = 'USZIPExtensionCd';
        $val = $this->format(self::FMT_ZXT, 4, $data, 'SUBMITTER_ADDRESS_ID__POSTAL_CODE');
        if (! empty($val)) { // Optional per schema definition.
            $xmlUSAddressGrp->addChild($tag, $val, $nsIrs);
        }
        
        // ContactNameGrp****************************************************
        $xmlContactNameGrp = $xmlCompanyInformationGrp->addChild('ContactNameGrp');
        
        $tag = 'PersonFirstNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'CONTACT_GIVEN_NAME', self::FMT_NAME);
        $xmlContactNameGrp->addChild($tag, $val);
        
        // NOT SUPPORTED by any source data. OPTIONAL per schema definition.
        // $tag = 'PersonMiddleNm';
        // $val = $this->format ( self::FMT_STR, 30, $data, 'CONTACT_MIDDLE_NAME', self::FMT_NAME );
        // if (! empty ( $val )) { // Optional per schema definition.
        // $xmlContactNameGrp->addChild ( $tag, $val );
        // }
        
        $tag = 'PersonLastNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'CONTACT_FAMILY_NAME', self::FMT_NAME);
        $xmlContactNameGrp->addChild($tag, $val);
        
        // NOT SUPPORTED by any source data. OPTIONAL per schema definition.
        // $tag = 'SuffixNm';
        // $val = $this->format ( self::FMT_STR, 20, $data, 'CONTACT_NAME_QUALIFICATION', self::FMT_NAME );
        // if (! empty ( $val )) { // Optional per schema definition.
        // $xmlContactNameGrp->addChild ( $tag, $val );
        // }
        
        $data['contact_phone'] = trim($data['CONTACT_PHONE_NUMBER']);
        $data['contact_phone'] .= trim($data['CONTACT_PHONE_EXTENSION']);
        $tag = 'ContactPhoneNum';
        $len = 30;
        if ($report['TAX_YEAR'] == '2015' || $report['TAX_YEAR'] == '2016') {
            $len = 15;
        }
        $val = $this->format(self::FMT_PHN, $len, $data, 'contact_phone');
        $xmlCompanyInformationGrp->addChild($tag, $val);
        
        // VendorInformationGrp**********************************************
        $xmlVendorInformationGrp = $xmlDetail->addChild('VendorInformationGrp');
        
        $tag = 'VendorCd';
        $val = 'V';
        $xmlVendorInformationGrp->addChild($tag, $val);
        
        // VendorContactNameGrp**********************************************
        $xmlVendorContactNameGrp = $xmlVendorInformationGrp->addChild('ContactNameGrp');
        
        $tag = 'PersonFirstNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'vendorContactNameFirst', self::FMT_NAME);
        $xmlVendorContactNameGrp->addChild($tag, $val);
        
        $tag = 'PersonMiddleNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'vendorContactNameMiddle', self::FMT_NAME);
        if (! empty($val)) { // Optional per schema definition.
            $xmlVendorContactNameGrp->addChild($tag, $val);
        }
        
        $tag = 'PersonLastNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'vendorContactNameLast', self::FMT_NAME);
        $xmlVendorContactNameGrp->addChild($tag, $val);
        
        $tag = 'SuffixNm';
        $val = $this->format(self::FMT_STR, 20, $data, 'vendorContactNameSuffix', self::FMT_NAME);
        if (! empty($val)) { // Optional per schema definition.
            $xmlVendorContactNameGrp->addChild($tag, $val);
        }
        
        $tag = 'ContactPhoneNum';
        $val = $this->vendorContactPhone;
        $xmlVendorInformationGrp->addChild($tag, $val);
        
        // ******************************************************************
        $tag = 'TotalPayeeRecordCnt';
        $val = $this->format(self::FMT_NBR, 10, $data, 'countEE');
        $xmlDetail->addChild($tag, $val);
        
        $tag = 'TotalPayerRecordCnt';
        $val = $this->format(self::FMT_NBR, 10, $data, 'countER');
        $xmlDetail->addChild($tag, $val);
        
        $tag = 'SoftwareId';
        $val = self::SOFTWARE_ID_23;
        if ($report['TAX_YEAR'] == '2022') {
            $val = self::SOFTWARE_ID_22;
        }
        if ($report['TAX_YEAR'] == '2021') {
            $val = self::SOFTWARE_ID_21;
        }
        if ($report['TAX_YEAR'] == '2020') {
            $val = self::SOFTWARE_ID_20;
        }
        if ($report['TAX_YEAR'] == '2019') {
            $val = self::SOFTWARE_ID_19;
        }
        if ($report['TAX_YEAR'] == '2018') {
            $val = self::SOFTWARE_ID_18;
        }
        if ($report['TAX_YEAR'] == '2017') {
            $val = self::SOFTWARE_ID_17;
        }
        if ($report['TAX_YEAR'] == '2016') {
            $val = self::SOFTWARE_ID_16;
        }
        if ($report['TAX_YEAR'] == '2015') {
            $val = self::SOFTWARE_ID_15;
        }
        $xmlDetail->addChild($tag, $val);
        
        $tag = 'FormTypeCd';
        $val = '1094/1095C';
        $xmlDetail->addChild($tag, $val);
        
        $tag = 'BinaryFormatCd';
        $val = 'application/xml';
        $xmlDetail->addChild($tag, $val, $nsIrs);
        
        $tag = 'ChecksumAugmentationNum';
        $val = $this->format(self::FMT_STR, 64, $data, 'hash');
        $xmlDetail->addChild($tag, $val, $nsIrs);
        
        $tag = 'AttachmentByteSizeNum';
        $val = $this->format(self::FMT_NBR, 10, $data, 'size');
        $xmlDetail->addChild($tag, $val);
        
        $tag = 'DocumentSystemFileNm';
        $val = trim($data['name']);
        $xmlDetail->addChild($tag, $val);
        
        $this->setFileResource('Manifest', $xmlObj);
    }

    /**
     * Formats data based on supplied data type
     * Enforces standards from ACA specification for formatting various data types
     * Defaults an empty string or zero if index pointing to value in array that is not set
     *
     * @param integer $type
     *            format type - use FMT_ constants defined in object
     * @param integer $len
     *            length (maximum) of the formatted value
     * @param array $arr
     *            The array containing the data elements
     * @param string $idx
     *            The key for the specific value in the array
     * @param mixed $fmt
     *            The formatting instruction (e.g. format string)
     * @return string containing value formatted per instructions
     */
    protected function format($type, $len, array $arr = array(), $idx = '', $fmt = '')
    {
        $ret = '';
        switch ($type) {
            case self::FMT_CUR:
                if (isset($arr[$idx])) {
                    $ret = $this->formatMoney($arr[$idx], $len);
                } else {
                    $ret = '0.00';
                }
                break;
            
            case self::FMT_NBR:
                if (isset($arr[$idx])) {
                    $ret = $arr[$idx];
                    if ($fmt == '') {
                        // trim off leading zeros
                        $ret = ltrim($ret, '0');
                        // empty string defaults to zero
                        $ret = (empty($ret)) ? (string) self::ZERO : $ret;
                    }
                    // Truncate strings that are too long (high order digits)
                    if (strlen($ret) > $len) {
                        $ret = substr($ret, strlen($ret) - $len, $len);
                    }
                    if ($fmt != '') {
                        $ret = sprintf($fmt, $ret);
                    }
                } else {
                    if ($fmt == '') {
                        $ret = self::ZERO;
                    } else {
                        $ret = sprintf($fmt, $ret);
                    }
                }
                break;
            
            case self::FMT_FLG:
                if (isset($arr[$idx])) {
                    $ret = $this->formatIndicator($arr[$idx]);
                } else {
                    $ret = $this->formatIndicator(FALSE);
                }
                break;
            
            case self::FMT_YN:
                if (isset($arr[$idx])) {
                    $ret = $this->formatDigitCodeType($arr[$idx]);
                } else {
                    $ret = $this->formatDigitCodeType(FALSE);
                }
                break;
            
            case self::FMT_TIN:
                if (isset($arr[$idx])) {
                    $ret = $this->formatDigitsOnly($arr[$idx], $len, 9);
                }
                break;
            
            case self::FMT_ZIP:
                if (isset($arr[$idx])) {
                    $ret = $this->formatDigitsOnly($arr[$idx], $len, 5);
                }
                break;
            
            case self::FMT_PHN:
                if (isset($arr[$idx])) {
                    $ret = $this->formatDigitsOnly($arr[$idx], $len, 10);
                }
                break;
            
            case self::FMT_ZXT:
                if (isset($arr[$idx])) {
                    $ret = $this->formatZipExt(trim($arr[$idx]), $len);
                }
                break;
            
            case self::FMT_JSPIN:
                if (isset($arr[$idx])) {
                    $ret = $this->formatDigitsOnly($arr[$idx], $len, 1);
                }
                break;
            
            case self::FMT_DTE:
                $ret = $this->formatDate($arr[$idx], $fmt);
                break;
            
            case self::FMT_STR:
                if (isset($arr[$idx])) {
                    $ret = $this->formatString($arr[$idx], $len, $fmt);
                } elseif ($fmt === 0) {
                    $ret = self::ZERO;
                }
                break;
        }
        return $ret;
    }

    /**
     * Format Digits Only -
     * Assume inbound data item is alpha-numeric, with or without formatting (i.e.
     * dashes, spaces, parens, etc)
     * Remove all formatting to give just numbers
     * Truncate to maximum length if specified
     * If minimum length specified and it is not satisfied by length of resulting field, return an empty string
     *
     * @param str $item
     *            Data item
     * @param int $max
     *            [optional] Maximum length of resulting field
     * @param int $min
     *            [optional] Minimum length of resulting field
     * @return string Return value
     */
    protected function formatDigitsOnly($item, $max = 0, $min = 0)
    {
        // Removes anything non-numeric
        $ret = preg_replace('/[^0-9]/', '', $item);
        // If maximum length specified, truncate strings that are too long
        if ($max > 0 && strlen($ret) > $max) {
            $ret = substr($ret, 0, $max);
        }
        // If minimum length specified and string is not long enough, return empty string
        if ($min > 0 && $min <= $max && strlen($ret) < $min) {
            $ret = '';
        }
        
        return $ret;
    }

    /**
     * Format Zip Code Extension
     * Assume inbound zip code has spaces, dashes, etc.
     * Remove all to give just numbers
     * If zip code is 5 digits or less, return an empty string
     * If zip code is more than 5 digits, retrieve extension
     * If extension is less than length specified, return an empty string
     *
     * @param str $zip
     *            version of Zip Code
     * @param int $len
     *            of resulting field
     * @return string value
     */
    protected function formatZipExt($zip, $len)
    {
        // Removes anything non-numeric
        $ret = preg_replace('/[^0-9]/', '', $zip);
        // If zip code is more than 5 digits, retrieve extension
        if (strlen($ret) > 5) {
            $ret = substr($ret, 5);
            $ret = $this->formatDigitsOnly($ret, $len, $len);
        } else {
            $ret = '';
        }
        return $ret;
    }

    /**
     * Format Dates -
     * Inbound format is assumed - see $this->defaultDateFormat.
     * Return an empty string if invalid date or format.
     *
     * @param str $date
     *            String version of Date
     * @param str $fmt
     *            Format string of resulting date
     * @return string value
     */
    protected function formatDate($date, $fmt)
    {
        $dt = DateTime::createFromFormat($this->defaultDateFormat, $date);
        if ($dt === FALSE) {
            $ret = '';
        } else {
            $ret = $dt->format($fmt);
        }
        return $ret;
    }

    /**
     * Set indicator based on boolean TRUE|FALSE
     * 1 - TRUE
     * 0 - FALSE
     *
     * @param boolean $bool
     *            Boolean indicator
     * @return string value
     */
    protected function formatIndicator($bool)
    {
        if ($bool) {
            $ret = "1";
        } else {
            $ret = "0";
        }
        return $ret;
    }

    /**
     * Set ACA xsd DigitCodeType based on boolean TRUE|FALSE
     * 1 - TRUE
     * 2 - FALSE
     *
     * @param boolean $bool
     *            Boolean indicator
     * @return string value
     */
    protected function formatDigitCodeType($bool)
    {
        if ($bool) {
            $ret = "1";
        } else {
            $ret = "2";
        }
        return $ret;
    }

    /**
     * Enforce string handling standards
     * - Standard strings cannot contain a hash character (#) and must have multiple dashes (--) reduced to a single dash (-),
     * multiple embedded spaces reduced to a single space, and any ampersand (&), apostrophe (') or less than (<) converted
     * to its appropriate html entity.
     * Standard strings are also truncated to the maximum length and any leading and trailing spaces are removed.
     *
     * @param string $str
     *            The string value to format
     * @param integer $len
     *            The maximum length of the string
     * @param string $fmt
     *            [optional] Additional formatting based on the ACA xsd element type pattern
     * @return string $ret Standardized string
     */
    protected function formatString($str, $len, $fmt = '')
    {
        
        // Remove hash char
        $pattern = array(
            '/#/'
        );
        $replacement = array(
            ''
        );
        $str = preg_replace($pattern, $replacement, $str);
        
        // Additional formatting
        switch ($fmt) {
            case self::FMT_BUSNAMCTRL:
                $str = strtoupper($str);
                $str = preg_replace('/[^A-Z0-9\-&]/', '', $str);
                break;
            
            case self::FMT_NAME:
                $str = preg_replace('/[^A-Za-z\- ]/', '', $str);
                break;
            
            case self::FMT_CITY:
                $str = preg_replace('/[^A-Za-z ]/', '', $str);
                break;
            
            case self::FMT_BUSNAM:
                $str = preg_replace('/[^A-Za-z0-9\-()&\' ]/', '', $str);
                break;
            
            case self::FMT_BUSNAM2:
                $str = preg_replace('/[^A-Za-z0-9\/%\-()&\' ]/', '', $str);
                break;
            
            case self::FMT_ADDR:
                $str = preg_replace('/[^A-Za-z0-9\-\/ ]/', '', $str);
                break;
        }
        
        // Convert ampersand, apostrophe or less than and reduce multiple dashes or spaces to single dash or space
        $pattern = array(
            '/&/',
            "/'/",
            '/</',
            '/-+/',
            '/\s+/'
        );
        $replacement = array(
            '&amp;',
            '&apos;',
            '&lt;',
            '-',
            ' '
        );
        $str = preg_replace($pattern, $replacement, $str);
        
        // Truncate strings that are too long
        if (strlen($str) > $len) {
            $str = substr($str, 0, $len);
            // throw new Exception ("String <<$str>> more than $len chars");
        }
        // Remove leading and trailing spaces
        $str = trim($str);
        return $str;
    }

    /**
     * Enforce money handling standards
     * - Money fields contain only digits and a decimal point - no separators or signs
     * - Include dollars and cents with decimal
     * - Do NOT round to nearest dollar!
     * - Truncate high order digits for numbers that are too long
     *
     * @param float $num
     *            The numeric value
     * @param integer $len
     *            The target length of the number field
     *            - include 2 digits past decimal
     *            - e.g. 1,234.40 is length 7 (1234.40)
     * @return string $ret Standardized money
     *        
     */
    protected function formatMoney($num, $len)
    {
        $num = (float) $num;
        // 2 decimals, remove commas
        $ret = number_format($num, 2, '.', '');
        
        // Truncate strings that are too long
        if (strlen($ret) > $len) {
            $ret = substr($ret, strlen($ret) - $len, $len);
        }
        return $ret;
    }

    /**
     * Get current GMT
     *
     * Format: YYYYMMDDTHHMMSSsssZ
     *
     * @return string
     */
    protected function getGMT()
    {
        list ($microseconds, $unix_time) = explode(' ', microtime());
        return date('Ymd\THis', $unix_time) . substr($microseconds, 2, 3) . 'Z';
    }

    /**
     * @return string
     */
    protected function getTimestamp()
    {
        $dateTime = new DateTime();
        $timeStamp = $dateTime->format(DATE_W3C);
        return $timeStamp;
    }

    /**
     * Is the employee's offer of coverage an individual coverage HRA
     * @return bool
     */
    protected function isICHRA()
    {
        $data = $this->getEmployee();
        $periods = [
            '12_MONTHS',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12'
        ];
        foreach ($periods as $pd) {
            if (in_array($data['COVERAGE_CODE_' . $pd], $this->getIcHRA())) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Is the data element the same for all 12 months?
     *
     * @param
     *            array The array containing the data elements to test
     * @param
     *            string The month index prefix to the values in the array
     * @param
     *            string [optional] The all 12 months index prefix to the value in the array
     * @return boolean Returns true when each month's value is the same as the all 12 months value.
     *         Otherwise, returns false.
     */
    protected function sameAll12Months(array $data, $monthPrefix, $allPrefix = null)
    {
        $same = true;
        
        if ($allPrefix === null) {
            $allPrefix = $monthPrefix;
        }
        $iAll = $allPrefix . '12_MONTHS';
        for ($m = 1; $m <= 12; $m ++) {
            $iMonth = $monthPrefix . $m;
            if ($data[$iMonth] != $data[$iAll]) {
                $same = false;
            }
        }
        
        return $same;
    }

    // ************************************** File/Resource Handling
    /**
     * Get the file name
     *
     * @param
     *            string [optional] The key indicating which file name. If not specified, returns array of file names.
     * @return string|array The file name including path or an array of file names.
     */
    public function getFilename($key = null)
    {
        if (empty($this->fileNames)) {
            $this->fileNames = $this->setFilename();
        }
        if ($key === null) {
            return $this->fileNames;
        } else {
            return $this->fileNames[$key];
        }
    }

    /**
     * Set file names
     *
     * File names created using
     * - FORM '1094C'
     * - TYPE either 'Manifest' or 'Request'
     * - TCC the Submitter's Transmitter Control Code
     * - TIMESTAMP the date and time
     *
     * Format: FORM_TYPE_TCC_TIMESTAMP.xml
     *
     * Example: 1094C_Request_BY02G_20150101T010102000Z.xml
     *
     * @return array The file names including path
     */
    public function setFilename()
    {
        $names = array();
        
        $workDir = $this->getWorkDir();
        
        $form = '1094C';
        $type = array(
            'Manifest',
            'Request'
        );
        $submitterData = $this->getSubmitter();
        $tcc = trim($submitterData['TRANSMITTER_CONTROL_CODE']);
        $gmt = $this->getGMT();
        
        foreach ($this->fileKeys as $key => $fileKey) {
            $names[$fileKey] = $workDir . $form . '_' . $type[$key] . '_' . $tcc . '_' . $gmt . '.xml';
        }
        
        return $names;
    }

    /**
     * Define working directory for files created
     *
     * @return string The working directory
     */
    public function getWorkDir()
    {
        return $this->workDir;
    }

    /**
     * Define working directory for files created
     *
     * Pre-pended to file name
     *
     * @param
     *            string The working directory
     */
    public function setWorkDir($workDir = "/")
    {
        $this->workDir = $workDir;
        return;
    }

    /**
     * Get the file resource
     *
     * @param
     *            string [optional] The key indicating which resource. If not specified, returns array of resources.
     * @return SimpleXMLElement array|boolean The resource (SimpleXMLElement) or an array of resources. False if no resources have been defined.
     */
    public function getFileResource($key = null)
    {
        if (empty($this->fileResources)) {
            return false;
        }
        if ($key === null) {
            return $this->fileResources;
        } else {
            return $this->fileResources[$key];
        }
    }

    /**
     * @param $key
     * @param SimpleXMLElement $resource
     */
    public function setFileResource($key, SimpleXMLElement $resource)
    {
        $this->fileResources[$key] = $resource;
    }

    /**
     * Initialize SimpleXML objects
     *
     * @throws Exception If the XML data could not be parsed
     */
    protected function fileInit()
    {
        $taxYear = $this->reportData['TAX_YEAR'];


        $xmlManifest2023 = <<<MANIFEST
<?xml version="1.0" encoding="UTF-8"?>
<n1:ACAUIBusinessHeader
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23"
xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:acabushdr="urn:us:gov:treasury:irs:msg:acabusinessheader"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:acauibusinessheader"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:acauibusinessheader IRS-ACAUserInterfaceHeaderMessage.xsd">
</n1:ACAUIBusinessHeader>
MANIFEST;
        $xmlFormData2023 = <<<FORMDATA
<?xml version="1.0" encoding="UTF-8"?>
<n1:Form109495CTransmittalUpstream
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23" xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage IRS-Form1094-1095CTransmitterUpstreamMessage.xsd">
</n1:Form109495CTransmittalUpstream>
FORMDATA;

        $xmlManifest2022 = <<<MANIFEST
<?xml version="1.0" encoding="UTF-8"?>
<n1:ACAUIBusinessHeader
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23"
xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:acabushdr="urn:us:gov:treasury:irs:msg:acabusinessheader"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:acauibusinessheader"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:acauibusinessheader IRS-ACAUserInterfaceHeaderMessage.xsd">
</n1:ACAUIBusinessHeader>
MANIFEST;
        $xmlFormData2022 = <<<FORMDATA
<?xml version="1.0" encoding="UTF-8"?>
<n1:Form109495CTransmittalUpstream
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty22" xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage IRS-Form1094-1095CTransmitterUpstreamMessage.xsd">
</n1:Form109495CTransmittalUpstream>
FORMDATA;

        $xmlManifest2021 = <<<MANIFEST
<?xml version="1.0" encoding="UTF-8"?>
<n1:ACAUIBusinessHeader
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23"
xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:acabushdr="urn:us:gov:treasury:irs:msg:acabusinessheader"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:acauibusinessheader"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:acauibusinessheader IRS-ACAUserInterfaceHeaderMessage.xsd">
</n1:ACAUIBusinessHeader>
MANIFEST;
        $xmlFormData2021 = <<<FORMDATA
<?xml version="1.0" encoding="UTF-8"?>
<n1:Form109495CTransmittalUpstream
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty21" xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage IRS-Form1094-1095CTransmitterUpstreamMessage.xsd">
</n1:Form109495CTransmittalUpstream>
FORMDATA;


        $xmlManifest2020 = <<<MANIFEST
<?xml version="1.0" encoding="UTF-8"?>
<n1:ACAUIBusinessHeader
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23"
xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:acabushdr="urn:us:gov:treasury:irs:msg:acabusinessheader"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:acauibusinessheader"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:acauibusinessheader IRS-ACAUserInterfaceHeaderMessage.xsd">
</n1:ACAUIBusinessHeader>
MANIFEST;
        $xmlFormData2020 = <<<FORMDATA
<?xml version="1.0" encoding="UTF-8"?>
<n1:Form109495CTransmittalUpstream
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty20" xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage IRS-Form1094-1095CTransmitterUpstreamMessage.xsd">
</n1:Form109495CTransmittalUpstream>
FORMDATA;
        
        $xmlManifest2019 = <<<MANIFEST19
<?xml version="1.0" encoding="UTF-8"?>
<n1:ACAUIBusinessHeader
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23"
xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:acabushdr="urn:us:gov:treasury:irs:msg:acabusinessheader"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:acauibusinessheader"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:acauibusinessheader IRS-ACAUserInterfaceHeaderMessage.xsd">
</n1:ACAUIBusinessHeader>
MANIFEST19;
        $xmlFormData2019 = <<<FORMDATA19
<?xml version="1.0" encoding="UTF-8"?>
<n1:Form109495CTransmittalUpstream
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty19" xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage IRS-Form1094-1095CTransmitterUpstreamMessage.xsd">
</n1:Form109495CTransmittalUpstream>
FORMDATA19;
        
        $xmlManifest2018 = <<<MANIFEST18
<?xml version="1.0" encoding="UTF-8"?>
<n1:ACAUIBusinessHeader
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23"
xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:acabushdr="urn:us:gov:treasury:irs:msg:acabusinessheader"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:acauibusinessheader"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:acauibusinessheader IRS-ACAUserInterfaceHeaderMessage.xsd">
</n1:ACAUIBusinessHeader>
MANIFEST18;
        $xmlFormData2018 = <<<FORMDATA18
<?xml version="1.0" encoding="UTF-8"?>
<n1:Form109495CTransmittalUpstream
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty18" xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage IRS-Form1094-1095CTransmitterUpstreamMessage.xsd">
</n1:Form109495CTransmittalUpstream>
FORMDATA18;
        
        $xmlManifest2017 = <<<MANIFEST17
<?xml version="1.0" encoding="UTF-8"?>
<n1:ACAUIBusinessHeader
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23"
xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:acabushdr="urn:us:gov:treasury:irs:msg:acabusinessheader"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:acauibusinessheader"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:acauibusinessheader IRS-ACAUserInterfaceHeaderMessage.xsd">
</n1:ACAUIBusinessHeader>
MANIFEST17;
        $xmlFormData2017 = <<<FORMDATA17
<?xml version="1.0" encoding="UTF-8"?>
<n1:Form109495CTransmittalUpstream
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty17" xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage IRS-Form1094-1095CTransmitterUpstreamMessage.xsd">
</n1:Form109495CTransmittalUpstream>
FORMDATA17;
        
        $xmlManifest2016 = <<<MANIFEST16
<?xml version="1.0" encoding="UTF-8"?>
<n1:ACAUIBusinessHeader
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23"
xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:acabushdr="urn:us:gov:treasury:irs:msg:acabusinessheader"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:acauibusinessheader"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:acauibusinessheader IRS-ACAUserInterfaceHeaderMessage.xsd">
</n1:ACAUIBusinessHeader>
MANIFEST16;
        $xmlFormData2016 = <<<FORMDATA16
<?xml version="1.0" encoding="UTF-8"?>
<n1:Form109495CTransmittalUpstream
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty16" xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage IRS-Form1094-1095CTransmitterUpstreamMessage.xsd">
</n1:Form109495CTransmittalUpstream>
FORMDATA16;
        
        $xmlManifest2015 = <<<MANIFEST15
<?xml version="1.0" encoding="UTF-8"?>
<n1:ACAUIBusinessHeader
xmlns="urn:us:gov:treasury:irs:ext:aca:air:ty23"
xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:acabushdr="urn:us:gov:treasury:irs:msg:acabusinessheader"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:acauibusinessheader"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:acauibusinessheader IRS-ACAUserInterfaceHeaderMessage.xsd">
</n1:ACAUIBusinessHeader>
MANIFEST15;
        $xmlFormData2015 = <<<FORMDATA15
<?xml version="1.0" encoding="UTF-8"?>
<n1:Form109495CTransmittalUpstream
xmlns="urn:us:gov:treasury:irs:ext:aca:air:7.0" xmlns:irs="urn:us:gov:treasury:irs:common"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:n1="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage"
xsi:schemaLocation="urn:us:gov:treasury:irs:msg:form1094-1095Ctransmitterupstreammessage IRS-Form1094-1095CTransmitterUpstreamMessage.xsd">
</n1:Form109495CTransmittalUpstream>
FORMDATA15;
        
        libxml_use_internal_errors(true);
        
        foreach ($this->fileKeys as $fileKey) {
            try {
                $xmlObj = new SimpleXMLElement(${'xml' . $fileKey . $taxYear});
            } catch (Exception $e) {
                throw $e;
            }
            $this->setFileResource($fileKey, $xmlObj);
        }
        
        libxml_clear_errors();
    }

    /**
     * Save a file resource
     *
     * @param
     *            string The key indicating which resource.
     * @return array An empty array when the save is successful, otherwise an array of errors.
     */
    protected function fileWrite($fileKey)
    {
        $messages = array();
        
        libxml_use_internal_errors(true);
        
        $xmlObj = $this->getFileResource($fileKey);
        if (! ($xmlObj->asXML($this->getFilename($fileKey)))) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $message = 'Warning ';
                        break;
                    case LIBXML_ERR_ERROR:
                        $message = 'Error ';
                        break;
                    case LIBXML_ERR_FATAL:
                        $message = 'Fatal Error ';
                        break;
                }
                $message .= $error->code . ': ' . trim($error->message);
                if ($error->file) {
                    $message .= " in file $error->file";
                }
                $message .= " on line $error->line";
                $messages[] = $message;
            }
            libxml_clear_errors();
        }
        
        return $messages;
    }
}
