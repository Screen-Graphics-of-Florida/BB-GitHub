<?php
// require_once 'ACACacheToXMLWriter.php';

/**
 * Class reads the Source Data (array) provided and executes methods in the provided Report (object)
 * to create the two xml files, Manifest and Form Data, for ACA reporting.
 * 
 * Use:
 *  1) instantiate the object
 *  2) ::setReport
 *  3) ::setSourceData
 *  4) ::createFile
 *
 */
class ACACacheToXMLDriver {
	/**
	 * @var array
	 */
	protected $sourceData = array ();
	
	/**
	 * @var ACACacheToXMLWriter
	*/
	protected $report;
	
	/**
	 * @var resource i5_connect
	 */
	protected $i5Connect;

	/**
	 * Class constructor
	 *
	 */
	function __construct($connect) {
		$this->i5Connect = $connect;
	}

	/**
	 * Create the tax report file
	 *
	 */
	public function createFile() {
		$report = $this->getReport ();
		
		$report->setSubmitter ( $this->getSourceData ( 'TaxReportSubmitter' ) );
		
		$employers = $this->getSourceData ( 'TaxReportEmployer' );
		foreach ( $employers as $employer ) {
			
			try {
				$report->nextEmployer ( $employer );
			} catch ( Exception $e ) {
				// throw exception up the stack
				throw $e;
			}
			
			if (isset ( $employer ['TaxCacheEmployee'] )) {
				$employees = $employer ['TaxCacheEmployee'];
				foreach ( $employees as $employee ) {
					
					try {
						$report->nextEmployee ( $employee );
					} catch ( Exception $e ) {
						// throw exception up the stack
						throw $e;
					}
					
					if (isset ( $employee ['TaxCacheDep'] )) {
						$dependents = $employee ['TaxCacheDep'];
						foreach ( $dependents as $dependent ) {
							try {
								$report->nextDependent ( $dependent );
							} catch ( Exception $e ) {
								// throw exception up the stack
								throw $e;
							}
						}
					}
				}
			}
		}
		
		// Generate the Unique Transmission ID
		$utid = $this->generateUTID ();
		// Update the report data
		$reportData = $report->getReport ();
		$reportData ['UNIQUE_TRANSMISSION_ID'] = $utid;
		$report->setReport ( $reportData );
		
		try {
			$report->complete ();
		} catch ( Exception $e ) {
			// throw exception up the stack
			throw $e;
		}
		
		// On successful creation of the xml files,
		// Update the ACA 1094C and 1095C Cache
		foreach ( $employers as $employer ) {
			$stmt1094C = 'Update HRACC4 Set C4TRANID=\'' . $utid . '\'';
			$stmt1094C .= ', C4SUBMID =' . $employer ['SUBMISSION_ID'];
			$stmt1094C .= ', C4ATCNT =' . $employer ['FORM_1095_ATTACHED_COUNT'];
			$stmt1094C .= ' Where C4CACHID=' . $employer ['C4CACHID'];
			$sqlResult = db2_exec ( $this->i5Connect->getConnection (), $stmt1094C );
			
			if (isset ( $employer ['TaxCacheEmployee'] )) {
				$employees = $employer ['TaxCacheEmployee'];
				foreach ( $employees as $employee ) {
					$stmt1095C = 'Update HRACC5 Set C5RECID=' . $employee ['RECORD_ID'];
					$stmt1095C .= ' Where C5CACHID=' . $employee ['C5CACHID'];
					$sqlResult = db2_exec ( $this->i5Connect->getConnection (), $stmt1095C );
				}
			}
		}
	}

	/**
	 * Get the Transmitter Control Code
	 *
	 * @return string
	 */
	protected function getTCC() {
		$submitter = $this->getSourceData ( 'TaxReportSubmitter' );
		return trim ( $submitter ['TRANSMITTER_CONTROL_CODE'] );
	}

	/**
	 * Generate the Unique Transmission Identifier
	 *
	 * @return string
	 */
	protected function generateUTID() {
		$uuid = $this->generateUUID ();
		$tcc = $this->getTCC ();
		
		$utid = $uuid . ':SYS12:' . $tcc . '::T';
		return $utid;
	}

	/**
	 * Generate a Universally Unique Identifier
	 *
	 * Generates 128 bits of random data using openssl_random_pseudo_bytes(),
	 * makes the permutations on the octets and
	 * then uses bin2hex() and vsprintf() to do the final formatting.
	 * (taken from Jack's answer at the following link:
	 * http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid)
	 *
	 * @return string
	 */
	protected function generateUUID() {
		$data = openssl_random_pseudo_bytes ( 16 );
		
		$data [6] = chr ( ord ( $data [6] ) & 0x0f | 0x40 ); // set version to 0100
		
		$data [8] = chr ( ord ( $data [8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10
		
		return vsprintf ( '%s%s-%s-%s-%s-%s%s%s', str_split ( bin2hex ( $data ), 4 ) );
	}

	/**
	 * Get the source data using the supplied key
	 *
	 * @param string $key
	 * @return array
	 */
	protected function getSourceData($key) {
		if (! isset ( $this->sourceData [$key] )) {
			return array ();
		} else {
			return $this->sourceData [$key];
		}
	}

	/**
	 * Set the source data array
	 *
	 * @param array $sourceData
	 */
	public function setSourceData($sourceData) {
		$this->sourceData = $sourceData;
	}

	/**
	 * Get the tax report object
	 *
	 * @return ACACacheToXMLWriter
	 */
	protected function getReport() {
		return $this->report;
	}

	/**
	 * Set the tax report object
	 * 
	 * @param ACACacheToXMLWriter $report
	 */
	public function setReport($report) {
		$this->report = $report;
	}
}
?>
