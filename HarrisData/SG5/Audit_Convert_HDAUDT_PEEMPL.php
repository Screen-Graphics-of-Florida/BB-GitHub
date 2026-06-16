<?php ini_set('display_errors', 0); ini_set('log_errors',0); ini_set('report_memleaks',0);

require_once 'GetURLParm.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';
require_once 'XMLValidateInclude.php';
require_once ($baseExportFile);

// Bank level
$auditFile='PEEMPL';

$xmlstr = <<<XML
<?xml version="1.0" encoding="ISO-8859-1"?>
<?xml-stylesheet type="text/xsl" href="{$homeURL}{$homePath}AuditPEEMPLHtml.xsl"?>
<audit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="{$homeURL}{$homePath}Audit.xsd">
</audit>
XML;

$xmlAuditDoc = new SimpleXMLElement($xmlstr);
$saveRow =' ';

$xmlAuditDoc=hd_addChild($xmlAuditDoc,'html_stylesheet', trim($homeURL) . trim($homePath) . trim($casStyleSheet));


// Create sql to form xml
$stmtSQL = "
Select AUCODE,AUGRP,AUFILE,AUUSER,AUTSTP,AUDATA
      ,AUCO# as AUCO,AUFAC# as AUFAC,AUPECO,AUPELC,AUDEPT,AUPAYT,AUPREM,AUPEEM                
      ,(Select AUDATA From HDAUDT b Where (b.AUTSTP,b.AUUSER)=(a.AUTSTP,a.AUUSER) and AUSEQ in (0,1)) as FROM_AUDATA                              
From HDAUDT a
Where AUFILE='$auditFile' and AUSEQ=(Select MAX(AUSEQ) From HDAUDT b Where (b.AUTSTP,b.AUUSER)=(a.AUTSTP,a.AUUSER))
Order By AUTSTP,AUSEQ ";

$TransSeq=0;

$sqlResult = i5_query($stmtSQL);
i5_data_seek($sqlResult, 1);
while ($row = i5_fetch_assoc($sqlResult)){
	// Transaction ID
	$TransSeq+=1;

	$xmlTransDoc=$xmlAuditDoc->addChild('transaction', '');
	$xmlTransDoc->addAttribute('id', trim($TransSeq));
	$xmlTransDoc=hd_addChild($xmlTransDoc,'activity_code', trim($row['AUCODE']));
	$xmlTransDoc=hd_addChild($xmlTransDoc,'grouping', trim($row['AUGRP']));
	$xmlTransDoc=hd_addChild($xmlTransDoc,'table_name', trim($row['AUFILE']));
	$xmlTransDoc=hd_addChild($xmlTransDoc,'user_profile', trim($row['AUUSER']));
	$xmlTransDoc=hd_addChild($xmlTransDoc,'timestamp', trim($row['AUTSTP']));

	$xmlDataDoc = $xmlTransDoc->addChild('audit_data');

	// Data Detail
	$toData=$row['AUDATA'];
	$frData=$row['FROM_AUDATA'];

	$strPos=strpos($toData,'@@');
	while ($strPos > 0) {
		$parm=substr($toData,$strPos,6);
		$toValue=Decat_Field($parm, $toData);
		$frValue=Decat_Field($parm, $frData);
		$strPos=strpos($toData,'@@',$strPos+1);
		$toData=substr($toData,$endPos);
		if ($parm=="")
		if     ($parm=="@@act@") {$column="EPACT";}
		elseif ($parm=="@@levl") {$column="EPLEVL";}
		elseif ($parm=="@@oseq") {$column="EPOSEQ";}
		elseif ($parm=="@@titl") {$column="EPTITL";}
		elseif ($parm=="@@ethn") {$column="EPETHN";}
		elseif ($parm=="@@otid") {$column="EPOTID";}
		elseif ($parm=="@@hire") {$column="EPHIRE";}
		elseif ($parm=="@@fasc") {$column="EPFASC";}
		elseif ($parm=="@@sdep") {$column="EPSDEP";}
		elseif ($parm=="@@stat") {$column="EPSTAT";}
		elseif ($parm=="@@pens") {$column="EPPENS";}
		elseif ($parm=="@@labc") {$column="EPLABC";}
		elseif ($parm=="@@mail") {$column="EPMAIL";}
		elseif ($parm=="@@milt") {$column="EPMILT";}
		elseif ($parm=="@@wcmp") {$column="EPWCMP";}
		elseif ($parm=="@@phon") {$column="EPPHON";}
		elseif ($parm=="@@extn") {$column="EPEXTN";}
		elseif ($parm=="@@clas") {$column="EPCLAS";}
		elseif ($parm=="@@lvre") {$column="EPLVRE";}
		elseif ($parm=="@@lvdt") {$column="EPLVDT";}
		elseif ($parm=="@@lvrt") {$column="EPLVRT";}
		elseif ($parm=="@@rehr") {$column="EPREHR";}
		elseif ($parm=="@@hseq") {$column="EPHSEQ";}
		elseif ($parm=="@@rtem") {$column="EPRTEM";}
		elseif ($parm=="@@rvem") {$column="EPRVEM";}
		elseif ($parm=="@@temp") {$column="EPTEMP";}
		elseif ($parm=="@@sbhr") {$column="EPSBHR";}
		elseif ($parm=="@@sccl") {$column="EPSCCL";}
		elseif ($parm=="@@scid") {$column="EPSCID";}
		elseif ($parm=="@@scdt") {$column="EPSCDT";}
		elseif ($parm=="@@scex") {$column="EPSCEX";}
		elseif ($parm=="@@park") {$column="EPPARK";}
		elseif ($parm=="@@lcst") {$column="EPLCST";}
		elseif ($parm=="@@lcpl") {$column="EPLCPL";}
		elseif ($parm=="@@hndc") {$column="EPHNDC";}
		elseif ($parm=="@@phys") {$column="EPPHYS";}
		elseif ($parm=="@@pphn") {$column="EPPPHN";}
		elseif ($parm=="@@blod") {$column="EPBLOD";}
		elseif ($parm=="@@lbd@") {$column="EPLBD";}
		elseif ($parm=="@@cnme") {$column="EPCNME";}
		elseif ($parm=="@@cadr") {$column="EPCADR";}
		elseif ($parm=="@@cad2") {$column="EPCAD2";}
		elseif ($parm=="@@c1ct") {$column="EPC1CT";}
		elseif ($parm=="@@c1st") {$column="EPC1ST";}
		elseif ($parm=="@@c1zp") {$column="EPC1ZP";}
		elseif ($parm=="@@cphn") {$column="EPCPHN";}
		elseif ($parm=="@@c2nm") {$column="EPC2NM";}
		elseif ($parm=="@@c2a1") {$column="EPC2A1";}
		elseif ($parm=="@@c2a2") {$column="EPC2A2";}
		elseif ($parm=="@@c2ct") {$column="EPC2CT";}
		elseif ($parm=="@@c2st") {$column="EPC2ST";}
		elseif ($parm=="@@c2zp") {$column="EPC2ZP";}
		elseif ($parm=="@@c2ph") {$column="EPC2PH";}
		elseif ($parm=="@@grad") {$column="EPGRAD";}
		elseif ($parm=="@@dgre") {$column="EPDGRE";}
		elseif ($parm=="@@subj") {$column="EPSUBJ";}
		elseif ($parm=="@@schl") {$column="EPSCHL";}
		elseif ($parm=="@@pdpt") {$column="EPPDPT";}
		elseif ($parm=="@@dpcd") {$column="EPDPCD";}
		elseif ($parm=="@@pjob") {$column="EPPJOB";}
		elseif ($parm=="@@jbcd") {$column="EPJBCD";}
		elseif ($parm=="@@psts") {$column="EPPSTS";}
		elseif ($parm=="@@stcd") {$column="EPSTCD";}
		elseif ($parm=="@@actv") {$column="ERACTV";}
		elseif ($parm=="@@acct") {$column="ERACCT";}
		elseif ($parm=="@@sact") {$column="ERSACT";}
		elseif ($parm=="@@xfit") {$column="ERXFIT";}
		elseif ($parm=="@@xfic") {$column="ERXFIC";}
		elseif ($parm=="@@exsu") {$column="EREXSU";}
		elseif ($parm=="@@fwhs") {$column="ERFWHS";}
		elseif ($parm=="@@dfdp") {$column="ERDFDP";}
		elseif ($parm=="@@fdap") {$column="ERFDAP";}
		elseif ($parm=="@@fdaa") {$column="ERFDAA";}
		elseif ($parm=="@@cftp") {$column="ERCFTP";}
		elseif ($parm=="@@bftp") {$column="ERBFTP";}
		elseif ($parm=="@@eic@") {$column="EREIC";}
		elseif ($parm=="@@gwgs") {$column="ERGWGS";}
		elseif ($parm=="@@gpct") {$column="ERGPCT";}
		elseif ($parm=="@@exst") {$column="EREXST";}
		elseif ($parm=="@@stid") {$column="ERSTID";}
		elseif ($parm=="@@sdc@") {$column="ERSDC";}
		elseif ($parm=="@@stdp") {$column="ERSTDP";}
		elseif ($parm=="@@pec@") {$column="ERPEC";}
		elseif ($parm=="@@aec@") {$column="ERAEC";}
		elseif ($parm=="@@exam") {$column="EREXAM";}
		elseif ($parm=="@@taxc") {$column="ERTAXC";}
		elseif ($parm=="@@crc@") {$column="ERCRC";}
		elseif ($parm=="@@adc@") {$column="ERADC";}
		elseif ($parm=="@@stap") {$column="ERSTAP";}
		elseif ($parm=="@@staa") {$column="ERSTAA";}
		elseif ($parm=="@@cstp") {$column="ERCSTP";}
		elseif ($parm=="@@bstp") {$column="ERBSTP";}
		elseif ($parm=="@@suid") {$column="ERSUID";}
		elseif ($parm=="@@exwc") {$column="EREXWC";}
		elseif ($parm=="@@swid") {$column="ERSWID";}
		elseif ($parm=="@@jbcl") {$column="ERJBCL";}
		elseif ($parm=="@@sdid") {$column="ERSDID";}
		elseif ($parm=="@@loc1") {$column="ERLOC1";}
		elseif ($parm=="@@ldp1") {$column="ERLDP1";}
		elseif ($parm=="@@lpe1") {$column="ERLPE1";}
		elseif ($parm=="@@loc2") {$column="ERLOC2";}
		elseif ($parm=="@@ldp2") {$column="ERLDP2";}
		elseif ($parm=="@@lpe2") {$column="ERLPE2";}
		elseif ($parm=="@@pen@") {$column="ERPEN";}
		elseif ($parm=="@@type") {$column="ERTYPE";}
		elseif ($parm=="@@inds") {$column="ERINDS";}
		elseif ($parm=="@@opcd") {$column="EROPCD";}
		elseif ($parm=="@@nqpc") {$column="ERNQPC";}
		elseif ($parm=="@@stem") {$column="ERSTEM";}
		elseif ($parm=="@@lgrp") {$column="ERLGRP";}
		elseif ($parm=="@@pbcd") {$column="ERPBCD";}
		elseif ($parm=="@@pbdt") {$column="ERPBDT";}
		elseif ($parm=="@@cnty") {$column="ERCNTY";}
		elseif ($parm=="@@ehlt") {$column="EREHLT";}
		elseif ($parm=="@@rhlt") {$column="ERRHLT";}
		elseif ($parm=="@@wgpc") {$column="ERWGPC";}
		elseif ($parm=="@@seas") {$column="ERSEAS";}
		elseif ($parm=="@@unit") {$column="ERUNIT";}
		elseif ($parm=="@@dcdt") {$column="ERDCDT";}

		$xmlPayeeDetailDoc = $xmlDataDoc->addChild('audit_column', '');
		$xmlPayeeDetailDoc->addAttribute('id', trim($column));

		$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'from_value', trim($frValue));
		$xmlPayeeDetailDoc = hd_addChild($xmlPayeeDetailDoc,'to_value', trim($toValue));
	}
}


$xmlStr = $xmlACHDoc->asXML();
$domTableDoc = new DOMDocument("1.0");
$domTableDoc->loadXML($xmlStr);

$exportPath = "{$homePath}{$exportDirectory}";
if (!file_exists("$exportPath")) {exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$exportPath\")'");}
$dbPath = "{$exportPath}{$dataBaseID}/";
if (!file_exists("$dbPath")) {exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$dbPath\")'");}
$prACHPath = "{$dbPath}{$prACHDirectory}";
if (!file_exists("$prACHPath"))   {exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$prACHPath\")'");}

print $domTableDoc->save("$prACHPath{$auditFile}.xml");


function hd_addChild($obj, $element, $val){
	$obj->addChild($element, trim($val));
	return $obj;
}

?>