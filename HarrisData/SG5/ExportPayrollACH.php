<?php ini_set('display_errors',0); ini_set('log_errors',0); ini_set('report_memleaks',0);
chdir(dirname($_SERVER['argv'][0]));

$Arg = explode("::",$argv[1]);
$_GET['baseVar'] = $Arg[0];
$_SERVER['PHP_AUTH_PW']=$Arg[1];
$_SERVER['PHP_AUTH_USER']="HDS";

require_once 'GetURLParm.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';
require_once 'XMLValidateInclude.php';
require_once ($baseExportFile);

// Create sql to form xml
$stmtSQL = "
Select DDBKNO,DDCOMP,DDFACL,DDEMPL,DDSEQ ,DDACTY 
      ,DDACCT,DDPRE ,DDAMT ,DDNAM ,DDORGN,DDERAC,DIGITS(DDFDID) as DDFDID
      ,substr(DDTRN,1,4) as DDTRN_T
      ,substr(DDTRN,5,4) as DDTRN_A
      ,substr(DDTRN,9,1) as DDTRN_C
      ,substr(DDTRN,1,8) as DDTRN_HASH
      ,BABKNM,BABEGO,BAFIDM,BAFIDO,BAACHT,BAXSLT,BAIOV,BAMETH,BANMOP
      ,BAFILL,BAACH
      ,substr(BAIMD#,2,4) as BAIMD_T
      ,substr(BAIMD#,6,4) as BAIMD_A
      ,substr(BAIMD#,10,1) as BAIMD_C
      ,substr(BAIMD#,2,8) as BAIMD_H
      ,CFNAME,CFNAMEU,CFPHON,F_MAKEDATE(CFCKDT) as CFCKDT,F_MAKEDATE(CFPDTE) as CFPDTE
      ,(Select Min(YDNAME) from PRYDFL Where YDEIN = a.DDFDID) as YDNAME 
From PRDDWK a 
inner join PREACH  on (ACCOMP,ACFACL,ACEMPL) = (DDCOMP,DDFACL,DDEMPL) 
left join PRBANK  on BABKNO = DDBKNO 
left join HRCOFC  on (CFCOMP,CFFACL) = (DDCOMP,DDFACL) 
Order By DDBKNO,DDORGN,DDERAC,DDFDID,DDCOMP,DDFACL,DDEMPL,DDSEQ ";

$i=1;
$saveBatch = "";
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
while ($row = db2_fetch_assoc($sqlResult)){

	// Insert Doc Set with change of bank
	if ($saveBankNumber != 0 && $row['DDBKNO'] != $saveBankNumber){
		require "ExportPayrollACHInclude.php";


	}

	// Bank level
	if ($saveBankNumber == 0 || $row['DDBKNO'] != $saveBankNumber){
		$today_time=time();
		$saveBankNumber = $row['DDBKNO'];
		$saveBankName = trim($row['BABKNM']);
		$saveBAXSLT = trim($row['BAXSLT']);
		$bankFile=trim($row['BAACHT']) . "_" . trim(date("YmdHis",$today_time));


		$xmlstr = <<<XML
<?xml version="1.0" encoding="ISO-8859-1"?>
<?xml-stylesheet type="text/xsl" href="{$homeURL}{$homePath}ExportPayrollACHHtml.xsl"?>
<payroll_ach xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="{$homeURL}{$homePath}ExportPayrollACH.xsd">
</payroll_ach>
XML;

		$xmlACHDoc = new SimpleXMLElement($xmlstr);
		$saveRow =' ';

		
		$xmlACHDoc=hd_addChild($xmlACHDoc,'html_stylesheet', trim($homeURL) . trim($homePath) . trim($casStyleSheet));

		$xmlBankDoc = $xmlACHDoc->addChild('bank', '');
		$xmlBankDoc=hd_addChild($xmlBankDoc,'bank_number', trim($row['DDBKNO']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'bank_name', trim($row['BABKNM']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'bank_routing_transit', trim($row['BAIMD_T']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'bank_routing_aba', trim($row['BAIMD_A']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'bank_routing_check', trim($row['BAIMD_C']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'bank_routing_hash', trim($row['BAIMD_H']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'ach_method', trim($row['BAMETH']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'output_filler', trim($row['BAFILL']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'immed_origin_value', trim($row['BAIOV']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'file_modifier', trim($row['BAFIDM']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'file_modifier_override', trim($row['BAFIDO']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'employer_name_option', trim($row['BANMOP']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'prenote_needed', trim($row['BAACH']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'ach_transmission_file', trim($bankFile));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'ach_XSLT_file', trim($row['BAXSLT']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'fed_ein', trim($row['DDFDID']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'cofac_name', trim($row['CFNAME']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'cofac_name_upper', trim($row['CFNAMEU']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'tax_default_employer_name', trim($row['YDNAME']));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'process_date', trim(date("Y-m-d",$today_time)));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'process_time', trim(date("H:i:s",$today_time)));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'process_timestamp', trim(date("Y-m-d-H:i:s",$today_time)));
		$xmlBankDoc=hd_addChild($xmlBankDoc,'process_user_profile', trim($userProfile));
		$saveBatch = '';
	}

	// Batch (within Bank)
	if ($saveBatch == '' || trim($row['DDORGN']) != $saveOrigin || trim($row['DDERAC']) != $saveAccount || trim($row['DDFDID']) != $saveFID){
		if ($saveBatch == ''){
			$BABEGO=$row['BABEGO'];
			$saveBatch = "Y";
		} else {$BABEGO+=1;
				$saveACHMethod = trim($row['BAMETH']);
				if ($saveACHMethod == 'D'){$i++;}
		}

		$xmlBatchDoc = $xmlBankDoc->addChild('batch', '');
		$xmlBatchDoc->addAttribute('id', trim($row['DDORGN']) . "_" . trim($row['DDERAC']) . "_" . trim($row['DDFDID']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'fed_ein', trim($row['DDFDID']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'employer_bank_account', trim($row['DDERAC']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'company', trim($row['DDCOMP']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'facility', trim($row['DDFACL']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'cofac_name', trim($row['CFNAME']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'cofac_name_upper', trim($row['CFNAMEU']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'cofac_phone', trim($row['CFPHON']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'tax_default_employer_name', trim($row['YDNAME']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'check_date', trim($row['CFCKDT']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'pay_period_end_date', trim($row['CFPDTE']));
		$xmlBatchDoc = hd_addChild($xmlBatchDoc, 'batch_number', trim($BABEGO));

		$xmlPayeeDoc = $xmlBatchDoc->addChild('payee');

		$saveOrigin = trim($row['DDORGN']);
		$saveAccount = trim($row['DDERAC']);
		$saveFID = trim($row['DDFDID']);
		$saveEmployee = '';
		$saveRelation = '';
	}

	// Employee Detail
	$xmlPayeeDetailDoc = $xmlPayeeDoc->addChild('payee_detail', '');
	$xmlPayeeDetailDoc->addAttribute('id', trim($row['DDCOMP']) . "_" . trim($row['DDFACL']) . "_" . trim($row['DDEMPL']) . "_" . trim($row['DDSEQ']));
	// $xmlPayeeDetailDoc->addAttribute('seq', $i);
	// $i++;

	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_number', trim($row['DDEMPL']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_name', trim($row['DDNAM']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_account_type', trim($row['DDACTY']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_routing_transit', trim($row['DDTRN_T']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_routing_aba', trim($row['DDTRN_A']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_routing_check', trim($row['DDTRN_C']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_routing_hash', trim($row['DDTRN_HASH']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_account', trim($row['DDACCT']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_prenote', trim($row['DDPRE']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'payee_amount', trim($row['DDAMT']));
	$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'seq', $i);
	$i++;
}



require "ExportPayrollACHInclude.php";


function hd_addChild($obj, $element, $val){
	$obj->addChild($element, trim($val));
	return $obj;
}


?>