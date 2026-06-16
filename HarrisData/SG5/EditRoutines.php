	<?php
	require_once 'MonthDesc.php';

	function EditPhoneNumber($phone) {
		Global $phoneNbrFormat;
		$returnPhone= "";
		$phone = trim($phone);
		$lenPhone = strlen($phone);

		if ($lenPhone > 1){
			if ($phoneNbrFormat == "3"){$phoneSep = ".";}
			else                       {$phoneSep = "-";}

			if ($lenPhone == 11 && $phoneNbrFormat == "1"){
				$sCode = substr($phone, 0,1);
				$sArea = substr($phone,1,3);
				$sPrefix = substr($phone,4,3);
				$sNumber = substr($phone,7,4);
				$phone = "{$sCode} ({$sArea}) {$sPrefix}-{$sNumber}";
			} elseif ($lenPhone == 11){
				$sCode = substr($phone, 0,1);
				$sArea = substr($phone,1,3);
				$sPrefix = substr($phone,4,3);
				$sNumber = substr($phone,7,4);
				$phone = "{$sCode}{$phoneSep}{$sArea}{$phoneSep}{$sPrefix}-{$sNumber}";
			} elseif ($lenPhone == 10 && $phoneNbrFormat == "1"){
				$sArea = substr($phone,0,3);
				$sPrefix = substr($phone,3,3);
				$sNumber = substr($phone,6,4);
				$phone = "({$sArea}) {$sPrefix}-{$sNumber}";
			} elseif ($lenPhone == 10){
				$sArea = substr($phone,0,3);
				$sPrefix = substr($phone,3,3);
				$sNumber = substr($phone,6,4);
				$phone = "{$sArea}{$phoneSep}{$sPrefix}-{$sNumber}";
			} elseif ($lenPhone == 7){
				$sPrefix = substr($phone,0,3);
				$sNumber = substr($phone,3,4);
				$phone = "{$sPrefix}{$phoneSep}{$sNumber}";
			}
		}
		return $phone;
	}

	function Format_Domestic_Hover_Info ($FCUR, $TCUR, $DSTP, $OPER, $CRTE){
		$hoverText="";

		if ($DSTP>0) {
			$F_DSTP=Format_Date($DSTP, "D");
			$hoverText .= $F_DSTP;
		}
		if ($FCUR !=""){$hoverText .= " From $FCUR";}
		$stmtSQL .= "       ,Case When YPOPER='M' Then '*' When YPOPER='D' Then '/' Else ' ' End as YPOPER_SYMBOL ";
		if     ($OPER=="M") {$hoverText .= " *";}
		elseif ($OPER=="D") {$hoverText .= " /";}
		if ($CRTE !=0){$hoverText .= "$CRTE";}
		if ($TCUR !=""){$hoverText .= " To $TCUR";}

		return $hoverText;
	}

	function Format_Header ($titleIn, $descIn, $dataIn){
		if ($dataIn != ""){$F_dataIn = Format_Code($dataIn);}
		else              {$F_dataIn = "";}

		$hdrOut = "<tr>";
		if ($titleIn != ""){$hdrOut .= "<td class='hdrtitl'>$titleIn:</td>";}

		$hdrOut .=  "<td class='hdrdata'>$descIn &nbsp; $F_dataIn</td></tr>";
		print "\n $hdrOut";
	}

	function Format_Header_Hover ($titleIn, $descIn, $dataIn, $divName){
		if ($dataIn != ""){$F_dataIn = Format_Code($dataIn);}
		else              {$F_dataIn = "";}

		print "\n <tr>";
		if ($titleIn != ""){print "\n <td class='hdrtitl'>$titleIn:</td>";}

		print "\n <td class='hdrdata'><a href=\"javascript:void+0\" onMouseOver=\"showSel('$divName')\" onMouseOut=\"hideSel('$divName')\">$descIn &nbsp; $F_dataIn</a></td></tr>";
	}

	function Format_Detail_Hover ($titleIn, $descIn, $dataIn, $divName){
		if ($dataIn != ""){$F_dataIn = Format_Code($dataIn);}
		else              {$F_dataIn = "";}

		print "\n <tr>";
		if ($titleIn != ""){print "\n <td class='dsphdr'>$titleIn:</td>";}

		print "\n <td class='dspalph'><a href=\"javascript:void+0\" onMouseOver=\"showSel('$divName')\" onMouseOut=\"hideSel('$divName')\">$descIn &nbsp; $F_dataIn</a></td></tr>";
	}


	function Format_Header_URL ($titleIn = '', $descIn = '', $dataIn = '', $urlIn = ''){
		if ($dataIn != ""){$F_dataIn = Format_Code($dataIn);}
		else              {$F_dataIn = "";}

		$hdrOut = "<tr>";
		if ($titleIn != ""){$hdrOut .= "<td class='hdrtitl'>$titleIn:</td>" ;}

		if ($urlIn != "") {$hdrOut .= "<td class='hdrdata'><a href=\"{$urlIn}\" title=\"View $titleIn\">$descIn &nbsp; $F_dataIn</a></td></tr>";}
		else              {$hdrOut .= "<td class='hdrdata'>$descIn &nbsp; $F_dataIn</td></tr>";}
		print "\n $hdrOut";
	}

	function Format_Code ($CodeIn){
		Global $codeDisplay;
		Global $codeDspLeft;
		Global $codeDspRight;
		$CodeIn = trim($CodeIn);
		$CodeOut = "";
		if ($codeDisplay == "Y" && $CodeIn != ""){$CodeOut .= "{$codeDspLeft}{$CodeIn}{$codeDspRight}";}
		return $CodeOut;
	}

	function Format_Quote ($fieldIn){
		$fieldOut= $fieldIn;
		$fieldOut= str_replace("'", "&acute;", $fieldOut );
		$fieldOut= str_replace('"', "&quot;", $fieldOut);
		return $fieldOut;
	}

	function Format_Confirm_Desc ($confirmDesc1 = '', $confirmData1 = '', $confirmDesc2 = '', $confirmData2 = '', $confirmDesc3 = '',  $confirmData3 = ''){
		$F_confirmData1 = Format_Code(trim($confirmData1));
		$F_confirmDesc = "$confirmDesc1 $F_confirmData1";
		if ($confirmDesc2 != ""){
			$F_confirmData2 = Format_Code(trim($confirmData2));
			$F_confirmDesc .= "\\n$confirmDesc2 $F_confirmData2";
		}
		if ($confirmDesc3 != ""){
			$F_confirmData3 = Format_Code(trim($confirmData3));
			$F_confirmDesc .= "\\n$confirmDesc3 $F_confirmData3";
		}
		$F_confirmDesc = str_replace("'", "&acute;", $F_confirmDesc);
		$F_confirmDesc = str_replace('"', "&quot;", $F_confirmDesc);
		return $F_confirmDesc;
	}

	function Format_ConfMsg_Desc ($maintenanceCode = '', $confirmDesc1 = '', $confirmData1 = '', $confirmDesc2 = '', $confirmData2 = '', $confirmDesc3 = '', $confirmData3 = ''){
		if (    $maintenanceCode == "D") {$F_confMsgDesc = "Confirm Delete Of ";}
		elseif ($maintenanceCode == "C") {$F_confMsgDesc = "Confirm Update Of ";}
		elseif ($maintenanceCode == "M") {$F_confMsgDesc = "Confirm Move To ";}
		elseif ($maintenanceCode == "A") {$F_confMsgDesc = "Confirm Add Of ";}
		elseif ($maintenanceCode == "R") {$F_confMsgDesc = "Confirm Release Of ";}
		elseif ($maintenanceCode == "Re"){$F_confMsgDesc = "Confirm Reactivate Of ";}
		elseif ($maintenanceCode == "T") {$F_confMsgDesc = "Confirm Transfer Of ";}
		elseif ($maintenanceCode == "X") {$F_confMsgDesc = "Confirm Cancel Of ";}
		elseif ($maintenanceCode == "Y") {$F_confMsgDesc = "Confirm Deactivate Of ";}
		elseif ($maintenanceCode == "Z") {$F_confMsgDesc = "Confirm Copy Of ";}
		elseif ($maintenanceCode == "E") {$F_confMsgDesc = "Errors Found During Update Of ";}
		else                             {$F_confMsgDesc = "";}

		$F_confirmData1 = Format_Code(trim($confirmData1));
		$F_confMsgDesc .=  "$confirmDesc1 $F_confirmData1" ;
		if ($confirmDesc2 != ""){
			$F_confirmData2 = Format_Code(trim($confirmData2));
			$F_confMsgDesc .= " $confirmDesc2 $F_confirmData2";
		}
		if ($confirmDesc3 != ""){
			$F_confirmData3 = Format_Code(trim($confirmData3));
			$F_confMsgDesc .= " $confirmDesc3 $F_confirmData3";
		}

		$F_confMsgDesc= str_replace("'", "&acute", $F_confMsgDesc );
		$F_confMsgDesc= str_replace('"', "&quot", $F_confMsgDesc);
		return $F_confMsgDesc;
	}

	function Format_Acct ($account, $subAccount, $getDesc){
		$returnFldDesc = "";
		$F_acctSub = "";
		if ($account > "0"){
			while (strlen($subAccount)<4) {$subAccount =  "0{$subAccount}" ;}
			$F_acctSub = "{$account}-{$subAccount}";

			if ($getDesc == "Y"){
				$acctDesc = RetValue("CHACCT=$account and CHSUB=$subAccount ", "HDCHRT", "CHCHDS");
				$F_acctSub .= "&nbsp; $acctDesc";
			}elseif  ($getDesc == "F"){
				$returnFldDesc = RetValue("CHACCT=$account and CHSUB=$subAccount ", "HDCHRT", "CHCHDS");
				$F_acctSub=Format_Code($F_acctSub);
			}
		}
		return $F_acctSub;
	}

	function Format_CoFac ($company, $facility, $getDesc){
		$returnFldDesc = "";
		$F_coFac = "";
		if ($company > "0"){
			while(strlen($facility)<4) {$facility = "0{$facility}";}
			if (strlen($company) != "2" && $getDesc != "F"){$F_coFac = "{$company}/{$facility}";}
			else                                           {$F_coFac = "{$company}/{$facility}";}

			if ($getDesc == "Y"){
				$coFacName = RetValue("CFCO#=$company and CFFAC#=$facility ", "HDCFAC", "CFCFNM");
				$F_coFac .= "&nbsp; $coFacName";
			} elseif ($getDesc == "F"){
				$returnFldDesc = RetValue("CFCO#=$company and CFFAC#=$facility ", "HDCFAC", "CFCFNM");
				$F_coFac = Format_Code($F_coFac);
			}
		}
		return $F_coFac;
	}

	function Format_HRCoFac ($company,$facility,$getDesc) {
		global $returnFldDesc; $returnFldDesc= "";
		$F_coFac= "";
		if ($company > "0") {
			while(strlen($facility)<4) {$facility="0{$facility}";}
			$F_coFac= "$company/$facility";

			if ($getDesc == "Y") {
				$coFacName=RetValue("CFCOMP=$company and CFFACL=$facility ", "HRCOFC", "CFNAME");
				$F_coFac .="&nbsp; $coFacName";
			} elseif ($getDesc == "F") {
				$returnFldDesc=RetValue("CFCOMP=$company and CFFACL=$facility ", "HRCOFC", "CFNAME");
				$F_coFac=Format_Code($F_coFac);
			}
		}
		return $F_coFac;
	}

	function Ret_Format_EmplName($PRComp,$PRFACL,$PREmpl,$HRCo,$HREmpl,$termHD) {
		$F_EmpName= "";
		$returnValue=RetEmpNam($PRComp, $PRFACL, $PREmpl, $HRCo, $HREmpl);

		$lastName=$returnValue['lastName'];
		$firstName=$returnValue['firstName'];
		$midInit=$returnValue['midInit'];
		$reportName=$returnValue['reportName'];
		$termCode=$returnValue['termCode'];

		if ($firstName != "" || $lastName != "") {
			$F_EmpName=@Format_EmplName($firstName,$lastName,$midInit,$reportName,$termCode,$termHD);
		}
		return $F_EmpName;
	}

	function Format_EmplName ($firstName,$lastName,$middleInitial,$reportName,$termCode,$termHD) {
		Global $HRNameFormat, $termEmplHeader, $termEmplDetail;
		$F_EmpName= "";
		if ($firstName != "" || $lastName != "") {
			if     ($HRNameFormat == "0" && $reportName != "") {$F_EmpName= $reportName;}
			elseif ($HRNameFormat == "0")                      {$F_EmpName= "$firstName $middleInitial $lastName";}
			elseif ($HRNameFormat == "1")                      {$F_EmpName= "$lastName substr($firstName,0,1)";}
			elseif ($HRNameFormat == "2")                      {$F_EmpName= "$lastName, $firstName $middleInitial";}
			elseif ($HRNameFormat == "3")                      {$F_EmpName= "$lastName $firstName";}
			elseif ($HRNameFormat == "4")                      {$F_EmpName= "$lastName substr($firstName,0,1) $middleInitial";}
			elseif ($HRNameFormat == "5")                      {$F_EmpName= "$firstName $middleInitial $lastName";}
			elseif ($HRNameFormat == "6")                      {$F_EmpName= "substr($firstName,0,1) $middleInitial $lastName";}

			if     ($termCode != "" && $termHD == "H") {$F_EmpName .= " $termEmplHeader";}
			elseif ($termCode != "" && $termHD == "D") {$F_EmpName .= " $termEmplDetail";}
		}
		return $F_EmpName;
	}

	function Rtv_Error_Desc ($errorNumber) {
		$errorDesc= "";
		$errorDesc=RetValue("ERER#='$errorNumber'", "SYCERR", "ERERDS");
		if ($errorDesc == "") {$errorDesc=RetValue("ERER#='$errorNumber'", "HDERROR", "ERERDS");}
		return $errorDesc;
	}

	function Format_Date ($dateIn, $dateInFormat){
		$F_dateIn="";
		$dateIn=trim($dateIn);
		Global $dateFormatHdr;
		Global $dateFormatDtl;
		Global $dateEdit;

		if ($dateIn >0){
			if     ($dateInFormat=="H"){$dateFormat=$dateFormatHdr;}
			elseif ($dateInFormat=="D"){$dateFormat=$dateFormatDtl;}
			else                       {$dateFormat=$dateInFormat;}

			while(strlen($dateIn)<8) {$dateIn = "0{$dateIn}";}

			$fromFormat="*CYMD";
			$toFormat="*MDYY";
			$dateIn=Reformat_Date_4($dateIn, $fromFormat, $toFormat);
			$mm  =substr($dateIn,0,2);
			$dd  =substr($dateIn,2,2);
			$yy  =substr($dateIn,6,2);
			$yyyy=substr($dateIn,4,4);

			if     ($dateFormat == "1") {$F_dateIn="{$mm}{$dateEdit}{$dd}{$dateEdit}{$yy}";}
			elseif ($dateFormat == "2") {$F_dateIn="{$mm}{$dateEdit}{$dd}{$dateEdit}{$yyyy}";}
			elseif ($dateFormat == "3") {$F_dateIn="{$yy}{$dateEdit}{$mm}{$dateEdit}{$dd}";}
			elseif ($dateFormat == "4") {$F_dateIn="{$yyyy}{$dateEdit}{$mm}{$dateEdit}{$dd}";}
			elseif ($dateFormat == "5") {$F_dateIn="{$dd}{$dateEdit}{$mm}{$dateEdit}{$yy}";}
			elseif ($dateFormat == "6") {$F_dateIn="{$dd}{$dateEdit}{$mm}{$dateEdit}{$yyyy}";}
			elseif ($dateFormat == "7") {$mmDesc=Get_Month_Desc($mm)     ; $F_dateIn="$mmDesc $dd, $yyyy";}
			elseif ($dateFormat == "8") {$mmDesc=Get_Month_Desc($mm)     ; $F_dateIn="$dd $mmDesc $yyyy";}
			elseif ($dateFormat == "9") {$mmDesc=Get_Month_Full_Desc($mm); $F_dateIn="$mmDesc $dd, $yyyy";}
			elseif ($dateFormat == "10"){$mmDesc=Get_Month_Full_Desc($mm); $F_dateIn="$dd $mmDesc $yyyy";}
		}
		return $F_dateIn;
	}

	function Format_Date_ISO ($dateIn,$dateInFormat) {
		$F_dateIn= "";
		$dateIn=trim($dateIn);
		if ($dateIn != "0001-01-01" && $dateIn != "") {
			$dateIn=Date_FromISO_ToCYMD($dateIn);
			$F_dateIn=Format_Date($dateIn, $dateInFormat);
		}
		return $F_dateIn;
	}

	function Format_SSN ($SSN) {
		while(strlen($SSN)<9) {$SSN="0{$SSN}";}
		if ($SSN != "000000000") {
			$first3 = substr($SSN, 0,3);
			$second2 = substr($SSN,3,2);
			$third4 = substr($SSN,5,4);
			$SSN = "{$first3}-{$second2}-{$third4}";
		} else {$SSN="";}
		return $SSN;
	}

	function Field_Checked ($fieldValue,$checkValue) {
		if ($fieldValue == $checkValue) {$fieldCheck= "CHECKED";}
		else                            {$fieldCheck= "";}
		return $fieldCheck;
	}

	function Build_Date_Range ($yearYY,$startCMD,$endCMD) {
		global $startCmd;
		global $endCmd;
		if     (strlen($yearYY) == "0") {$yearYY= "00";}
		elseif (strlen($yearYY) == "1") {$yearYY="0{$yearYY}";}

		$startCMD="0101{$yearYY}";
		$startCMD=DateMDYCYMD($startCMD);
		$endCMD="1231{$yearYY}";
		$endCMD=DateMDYCYMD($endCMD);
	}

	function DateBirthFromCYMD ($date) {
		if ($date > "0") {
			while(strlen($date)<7) {$date="0{$date}";}
			$c= substr($date,0,1);
			$yy= substr($date,1,2);
			if ($c == "0" && $yy <= "39") {$date= "1" . substr($date,1,6);}
			$fromFormat= "*CYMD";
			$toFormat  = "*SYSVAL";
			$date=Reformat_Date($date,$fromFormat,$toFormat);
		}
		return $date;
	}

	function DateInputFromCYMD ($date) {
		if ($date > "0") {
			while (strlen($date)<7) {$date="0{$date}";}
			$fromFormat= "*CYMD";
			$toFormat  = "*SYSVAL";
			$date=Reformat_Date($date, $fromFormat, $toFormat);
		}
		return $date;
	}

	function DateTodayCYMD () {
		$date = date("mdy");
		if ($date > "0") {
			while(strlen($date)<7) {$date="0{$date}";}
			$fromFormat= "*MDY";
			$toFormat  = "*CYMD";
			$date=Reformat_Date($date,$fromFormat,$toFormat);
		}
		return $date;
	}

	function DateFromCYMD ($date) {
		Global $dateEdit;
		if ($date > "0") {
			while(strlen($date)<7) {$date="0{$date}";}
			$fromFormat= "*CYMD";
			$toFormat  = "*SYSVAL";
			$date=Reformat_Date($date,$fromFormat,$toFormat);
			$date=substr($date,0,2) . $dateEdit . substr($date,2,2) . $dateEdit . substr($date,4,2);
		}
		return $date;
	}

	function DateFromCYM ($period) {
		if ($period > "0") {
			while(strlen($period)<5) {$period="0{$period}";}
			$fromFormat= "*YYPP";
			$period=Reformat_Period($period, $fromFormat);
			$period=substr($period,1,2) . substr($period,3,2);
		}
		return $period;
	}

	function DateToCYMD ($date) {
		if ($date > "0") {
			while(strlen($date)<7) {$date="0{$date}";}
			$fromFormat = "*SYSVAL";
			$toFormat   = "*CYMD";
			$date=Reformat_Date($date,$fromFormat,$toFormat);
		}
		return $date;
	}


	function DateMDYCYMD ($date) {
		if ($date > "0") {
			while(strlen($date)<7) {$date="0{$date}";}
			$fromFormat= "*MDY";
			$toFormat  = "*CYMD";
			$date=Reformat_Date($date,$fromFormat,$toFormat);
		}
		return $date;
	}

	function Date_No_Slash ($dateIn) {
		if ($strpos($dateIn, "/") !== false) {$dateOut= substr($dateIn,0,2) . substr($dateIn,3,2) . substr($dateIn,5,2);}
		else                                 {$dateOut= "";}
		return $dateOut;
	}

	function DateFromISO  ($dateIn) {
		Global $dateEdit;
		if ($dateIn != "" && $dateIn != "0001-01-01") {
			if ($sysDateFormat == "YMD")      {$dateOut=substr($dateIn,2,2) . $dateEdit . substr($dateIn,5,2) . $dateEdit . substr($dateIn,8,2);}
			else if ($sysDateFormat == "DMY") {$dateOut=substr($dateIn,8,2) . $dateEdit . substr($dateIn,5,2) . $dateEdit . substr($dateIn,2,2);}
			else                              {$dateOut=substr($dateIn,5,2) . $dateEdit . substr($dateIn,8,2) . $dateEdit . substr($dateIn,2,2);}
		} else {$dateOut= "";}
		return $dateOut;
	}

	function DateInputFromISO  ($dateIn) {
		if ($dateIn != "" && $dateIn != "0001-01-01") {
			if ($sysDateFormat == "YMD")      {$dateOut=substr($dateIn,2,2) . substr($dateIn,5,2) . substr($dateIn,8,2);}
			else if ($sysDateFormat == "DMY") {$dateOut=substr($dateIn,8,2) . substr($dateIn,5,2) . substr($dateIn,2,2);}
			else                              {$dateOut=substr($dateIn,5,2) . substr($dateIn,8,2) . substr($dateIn,2,2);}
		} else {$dateOut= "";}
		return $dateOut;
	}
	function Date_FromISO_ToCYMD ($dateIn) {
		$dateOut= "";
		if ($dateIn > "0001-01-01") {
			$mm= substr($dateIn,5,2);
			$dd= substr($dateIn,8,2);
			$yy= substr($dateIn,2,2);

			if (substr($dateIn,0,1) == "1") {$c= "0";}
			else                            {$c= "1";}

			$dateOut= "{$c}{$yy}{$mm}{$dd}";
		}
		return $dateOut;
	}

	function Date_CYMD_ISO ($dateIn) {
		$dateOut= "";
		while(strlen($dateIn)<7) {$dateIn="0{$dateIn}";}
		$workDate  = $dateIn;
		$fromFormat= "*CYMD";
		$toFormat  = "*MDY";
		$workDate=Reformat_Date($workDate,$fromFormat,$toFormat);
		$dateOut=Date_MDY_ISO($workDate);
		return $dateOut;
	}

	function Date_ISO_MDY ($dateIn) {
		if ($dateIn != "0001-01-01" && $dateIn != "") {$dateOut= substr($dateIn,5,2) . substr($dateIn,8,2) . substr($dateIn,2,2);}
		else                                          {$dateOut= "";}
		return $dateOut;
	}

	function Date_MDY_ISO ($dateIn) {
		if ($dateIn == "0") {
			$dateOut= "0001-01-01";
		} else {
			while(strlen($dateIn)<7) {$dateIn="0{$dateIn}";}
			$CYMDDate  = $dateIn;
			$fromFormat= "*MDY";
			$toFormat  = "*CYMD";
			$CYMDDate=Reformat_Date($CYMDDate,$fromFormat,$toFormat);

			if (substr($CYMDDate,0,1) == "1") {$year= "20" . substr($CYMDDate,1,2);}
			else                              {$year= "19" . substr($CYMDDate,1,2);}

			$dateOut= $year . "-" . substr($CYMDDate,3,2) . "-" . substr($CYMDDate,5,2);
		}
		return $dateOut;
	}

	function Date_To_ISO ($dateIn) {
		if ($dateIn == "0") {
			$dateOut= "0001-01-01";
		} else {
			while(strlen($dateIn)<7) {$dateIn="0{$dateIn}";}
			$CYMDDate  = $dateIn;
			$fromFormat= "*SYSVAL";
			$toFormat  = "*CYMD";
			$CYMDDate=Reformat_Date($CYMDDate,$fromFormat,$toFormat);

			if (substr($CYMDDate,0,1) == "1") {$year= "20" . substr($CYMDDate,1,2);}
			else                              {$year= "19" . substr($CYMDDate,1,2);}

			$dateOut= $year . "-" . substr($CYMDDate,3,2) . "-" . substr($CYMDDate,5,2);
		}
		return $dateOut;
	}

	function PeriodInputFromCYP ($period) {
		if ($period > "0") {
			while(strlen($period)<5) {$period="0{$period}";}
			$fromFormat= "*YYPP";
			$period=Reformat_Period($period,$fromFormat);
			$period=substr($period,1,4);
		}
		return $period;
	}


	function PeriodFromCYP ($period) {
		Global $dateEdit;
		if ($period > "0") {
			while(strlen($period)<5) {$period="0{$period}";}
			$fromFormat= "*YYPP";
			$period=Reformat_Period($period, $fromFormat);
			$period=substr($period,1,2) . $dateEdit . substr($period,3,2);
		}
		return $period;
	}

	function PeriodToCYP ($period) {
		if ($period > "0") {
			while(strlen($period)<5) {$period="0{$period}";}
			$fromFormat= "*PPYY";
			$period=Reformat_Period($period, $fromFormat);
		}
		return $period;
	}

	function YearFromCYY ($year) {
		if ($year > "0") {
			while(strlen($year)<3) {$year="0{$year}";}
			$year += 1900;
		}
		return $year;
	}

	function EditHrsMin ($time) {
		$outTime = "";
		$length = strlen($time);
		$negPos = strpos($time, "-");

		if ($negPos !== false) {$outTime = "-";}
		else                   {$outTime = "&nbsp;";}

		$ssStart  = ($negPos === false)? 0 : $negPos + 1;
		$ssLength = ($length - $ssStart);
		$time = substr($time, $ssStart, $ssLength);

		while(strlen($time)<4) {$time="0{$time}";}
		$length = strlen($time);
		$outTime = substr($time,0,($length-2)) . ":" . substr($time,($length-2),2) . $outTime;

		return $outTime;
	}

	function EditHrsMinSec ($time) {
		$outTime = "";
		$length = strlen($time);
		$negPos = strpos($time, "-");

		if ($negPos !== false) {$outTime = "-";}
		else                   {$outTime = "&nbsp;";}

		$ssStart  = ($negPos === false)? 0 : $negPos + 1;
		$ssLength = ($length - $ssStart);
		$time = substr($time, $ssStart, $ssLength);

		while(strlen($time)<6) {$time="0{$time}";}
		$length = strlen($time);
		$outTime = substr($time,0,($length-4)) . ":" . substr($time,($length-4),2) . ":" . substr($time,($length-2),2) . $outTime;

		return $outTime;
	}

	function EditHrsMinNoSec ($time) {
		if ($time != "" && $time != "&nbsp;") {
			$time=trim($time);
			while(strlen($time)<6) {$time="0{$time}";}
			$time=substr($time,0,2) . ":" . substr($time,2,2);
		}
		return $time;
	}

	function Edit7HrsMinSec ($time7, $TADSSF) {
		$F_time= '';
		while(strlen($time7)<7) {$time7="0{$time7}";}
		if ($TADSSF == "Y") {
			$F_time=substr($time7,0,3) . ":" . substr($time7,3,2) . ":" . substr($time7,5,2);
		} else {
			$F_time=substr($time7,0,3) . ":" . substr($time7,3,2);
		}
		if (substr($F_time,0,1) == '0') {
			$F_time=(substr($F_time,1,(strlen($F_time)-1)));
		}
		if (substr($F_time,0,1) == '0') {
			$F_time=(substr($F_time,1,(strlen($F_time)-1)));
		}
		return $F_time;
	}

	function Edit9HrsMinSec ($time9, $TADSSF) {
		$F_time= '';
		while(strlen($time9)<9) {$time9="0{$time9}";}
		if ($TADSSF == "Y") {
			$F_time=substr($time9,0,5) . ":" . substr($time9,5,2) . ":" . substr($time9,7,2);
		} else {
			$F_time=substr($time9,0,5) . ":" . substr($time9,5,2);
		}

		if (substr($F_time,0,1) == '0') {
			$F_time=(substr($F_time,1,(strlen($F_time)-1)));
		}
		if (substr($F_time,0,1) == '0') {
			$F_time=(substr($F_time,1,(strlen($F_time)-1)));
		}
		if (substr($F_time,0,1) == '0') {
			$F_time=(substr($F_time,1,(strlen($F_time)-1)));
		}
		if (substr($F_time,0,1) == '0') {
			$F_time=(substr($F_time,1,(strlen($F_time)-1)));
		}
		return $F_time;
	}

	function TimeInputFromDec ($timein) {
		$timeout = '';
		if ($timein == '0') {return $timeout;}
		list($timeh, $timed) = explode('.', $timein);
		$timed = str_pad($timed, 2, '0');
		$timem = stdround(($timed * .6), 0);
		$timem = str_pad($timem, 2, '0', STR_PAD_LEFT);
		$timeout = $timeh . $timem;
		return $timeout;
	}

	function TimeInputFromHMS ($timeIn, $displaySS) {
		$timeOut = '';
		$timeOut = (string) $timeIn;
		$timeOut = str_pad($timeOut, 6, '0', STR_PAD_LEFT);
		if ($displaySS == 'Y') {return $timeOut;}
		$timeOut = substr($timeOut,0,4);
		return $timeOut;
	}

	function HoursInputFromHMS ($timeIn, $displaySS) {
		$timeOut = '';
		if ($timeIn == '0') {return $timeOut;}
		$timeOut = (string) $timeIn;
		if ($displaySS == 'Y') {return $timeOut;}
		$timeOut = substr($timeOut,0,strlen($timeOut)-2);
		return $timeOut;
	}

	function TimeStamp_CYMD ($timestampIn) {
		if ($timestampIn == "0001-01-01-00.00.00.000000" || $timestampIn == "9999-12-31-24.00.00.000000") {
			$dateOut= "";
		} else {
			$CYMDDate= "0" . substr($timestampIn,5,2) . substr($timestampIn,8,2) . substr($timestampIn,2,2);
			$fromFormat= "*MDY";
			$toFormat  = "*CYMD";
			$CYMDDate=Reformat_Date($CYMDDate,$fromFormat,$toFormat);
			$dateOut = $CYMDDate;
		}
		return $dateOut;
	}

	function TimeStamp_TIME ($timestampIn) {
		if ($timestampIn == "0001-01-01-00.00.00.000000" || $timestampIn == "9999-12-31-24.00.00.000000") {
			$timeOut= "";
		} else {
			$timeOut= substr($timestampIn,11,8);
			$timeOut= str_replace( ".", "",$timeOut);
		}
		return $timeOut;
	}

	function TimeStamp_TIME_HM ($timestampIn) {
		if ($timestampIn == "0001-01-01-00.00.00.000000" || $timestampIn == "9999-12-31-24.00.00.000000") {
			$timeOut= "";
		} else {
			$timeOut= substr($timestampIn,11,5);
			$timeOut= str_replace(".", "",$timeOut);
			if ($timeOut == "0000") {$timeOut= "";}
		}
		return $timeOut;
	}

	function stdround($num, $d=0) {
		$fuzz = 0.000001;
		if ($num > "0")     {$num = round($num + $fuzz / pow(10, $d), $d);}
		elseif ($num < "0") {$num = round($num - $fuzz / pow(10, $d), $d);}
		else                {$num = round($num, $d);}
		settype($num, "string");
		return $num;
	}

	/**
 * Formats a number for display
 *
 * @param mixed $inNumber
 * @param string $decimals
 * @param string $editcode
 * @param string $roundNbr
 * @param string $beforeChar
 * @param string $afterChar
 * @return string
 */
	function Format_Nbr ($inNumber = '', $decimals = '', $editcode = '', $roundNbr = '', $beforeChar = '', $afterChar = '') {
		global $decimalChar, $creditCodeOvr, $thousandChar;

        $dashPos = strpos($inNumber, "-");
        if ($dashPos > 1) {
            str_replace('-', '', $inNumber);
            $inNumber = '-' . $inNumber;
        }

        $valEdCd = "1234ABCDJKLMZ";
		if (strpos($valEdCd, $editcode)===false || is_numeric($decimals)==false) {return $inNumber;}

		$outNumber = "";
		$commas  = "12ABJK";
		$zeroBal = "13ACJL";
		$negSign = "JKLM";
		$crSign  = "ABCD";
		$noSign  = "1234Z";
		$dec = ($decimals == "0" || $editcode == "Z")? "" : $decimalChar;
		$thc = (strpos($commas, $editcode) === false)? "" : $thousandChar;
		$decPos = strpos($inNumber, ".");
		$length = strlen($inNumber);
		$scale  = ($decPos === false)? 0 : $length - $decPos - 1;

		if ($roundNbr == "Y")                            {$inNumber = stdround($inNumber, $decimals);}
		elseif ($decPos !== false && $decimals < $scale) {$inNumber = substr($inNumber, 0, ($decPos + $decimals + 1));}
		$inNumber = number_format($inNumber, $decimals, $dec, $thc);

		if ($inNumber == "0" && strpos($zeroBal, $editcode) === false) {return $outNumber;}
        if (intval($inNumber) == 0 && strpos($zeroBal, $editcode) !== false && $decimals == "0") {return '0';}

		$negPos = strpos($inNumber, "-");
		$length = strlen($inNumber);

		if ($afterChar != "") {$outNumber = $afterChar . $outNumber;}

		if (strpos($negSign, $editcode) !== false) {
			if ($negPos !== false && $creditCodeOvr == "Y") {$outNumber = ")" . $outNumber;}
			elseif ($negPos !== false)                      {$outNumber = "-" . $outNumber;}
			else                                            {$outNumber = "&nbsp;" . $outNumber;}
		}

		if (strpos($crSign, $editcode) !== false) {
			if ($negPos !== false && $creditCodeOvr == "Y") {$outNumber = ")" . $outNumber;}
			elseif ($negPos !== false)                      {$outNumber = "CR" . $outNumber;}
			elseif ($creditCodeOvr == "Y")                  {$outNumber = "&nbsp;" . $outNumber;}
			else                                            {$outNumber = "&nbsp; &nbsp; &nbsp;" . $outNumber;}
		}

		$ssStart  = ($negPos === false)? 0 : $negPos + 1;
		$ssLength = ($length - $ssStart);
		$outNumber = substr($inNumber, $ssStart, $ssLength) . $outNumber;

		if (strPos($noSign, $editcode) === false && $negPos !== false && $creditCodeOvr == "Y") {$outNumber = "(" . $outNumber;}

		if ($beforeChar != "") {$outNumber = $beforeChar . $outNumber;}

		return $outNumber;
	}

	function Build_DspFld($fldDesc,$fldVal1,$fldVal2 = null,$fldType = null) {
		$fldVal1=trim($fldVal1);
		print "\n <tr><td class=\"dsphdr\">$fldDesc</td> ";
		$inputType = "dspalph";
		if ($fldType == "D" || $fldType == "N") {$inputType = "dspnmbr";}
		print "\n <td class=\"$inputType\">$fldVal1</td>";
		if ($fldVal2){
			$fldVal2=trim($fldVal2);
			print "\n <td class=\"$inputType\">$fldVal2</td>";
		}
		print "\n </tr>";
	}

	function Build_Sort_Select($sortName,$sortValue) {
		global $sortSeqMax;
		print "\n <td align=\"center\">";
		print "\n   <select name=\"$sortName\">";
		print "\n   <option value=\"0\">&nbsp;";
		for ($i = 1; $i <= $sortSeqMax; $i++) {
			if ($i == $sortValue) {print "\n <option value=\"$i\" SELECTED>$i";}
			else                  {print "\n <option value=\"$i\">$i";}
		}
		print "\n   </select>";
		print "\n </td>";
	}

?>