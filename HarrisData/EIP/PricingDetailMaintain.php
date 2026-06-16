<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound        = $_GET['errFound'];
$wrnVar          = $_GET['wrnVar'];
$pricingLevel    = $_GET['pricingLevel'];
$levelDesc       = $_GET['levelDesc'];
$pricingKey      = $_GET['pricingKey'];
$contractStart   = $_GET['contractStart'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';
require_once 'PricingDetailFunctions.php';

$page_title     = "Customer Pricing Detail Maintenance";
$scriptName     = "PricingDetailMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;pricingLevel=" . urlencode(trim($pricingLevel)) . "&amp;levelDesc=" . urlencode(trim($levelDesc));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;pricingKey=" . urlencode(trim($pricingKey)) . "&amp;contractStart=" . urlencode(trim($contractStart)) . "&amp;maintenanceCode=D";
$programName    = "HOEPLM_E";
$backURL        = $_SESSION[$fromURL];
$dspMaxRows     = "999";
$useItem        = "";

if ($backURL == "") {$backURL="{$homeURL}{$phpPath}PricingDetail.php{$scriptVarBase}";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

$edtVar = "";
Concat_Field("@@fx@@", "DEFN");
Concat_Field("@@pmlv", $pricingLevel);
$edtVar .= "}{";
$defVar = Rtv_Pricing_Definition($profileHandle, $edtVar);
$contract   = Decat_Field("@@cn@@", $defVar);
$listLess   = Decat_Field("@@ll@@", $defVar);
$costPlus   = Decat_Field("@@cp@@", $defVar);
$dollarAmt  = Decat_Field("@@dl@@", $defVar);
$usePercent = Decat_Field("@@up@@", $defVar);
$bracketQty = Decat_Field("@@bp@@", $defVar);
$bracketAmt = Decat_Field("@@ba@@", $defVar);
$commission = Decat_Field("@@comm", $defVar);
$edtVar = "";

$mdCol = Rtv_Pricing_Categories($profileHandle, $pricingLevel);

if ($tag == "Edit_Data") {
	if ($maintenanceCode=="D" ) {
		$_POST['pricingLevel']      = $pricingLevel;
		$_POST['pricingKey']        = $pricingKey;
		$_POST['contractStart'] = DateInputFromCYMD($contractStart);
	}

	if ($maintenanceCode == "Z") {$maintenanceCode = "A";}

	$edtVar= "";
	strtoupper($pricingKey);
	Concat_Field("@@pmlv", $pricingLevel);
	if ($maintenanceCode == "C" || $maintenanceCode == "D")  {
		Concat_Field("@@pmky", $pricingKey);
	}  else  {
		Concat_Field("@@pmky", "");
	}
	$i = 1;
	foreach ($mdCol as $mdFld)  {
		$curName 	= trim($mdFld['PWCOLN']);
		$curdType  	= trim($mdFld['PWDTYP']);
		$curSize	= trim($mdFld['PWFLEN']);
		$curDKey = trim($mdFld['PWDKEY']);
		if ($curDKey == "IMITEM") {$useItem = "Y";}
		$chgCurName = "chg$curName";
		Concat_Field("@@nam$i", $curName);
		Concat_Field("@@len$i", $curSize);
		$curShort = "@@";
		$curShort .= strtolower(substr($curName, 2));
		$curShort = str_pad($curShort,6,"@");

		if ($curdType == "NUMERIC" || $curdType == "DECIMAL")  {
			$curData = $_POST[$chgCurName] ;
			$curShort .= $curData;
		}  else  {
			$curData = $_POST[$chgCurName] ;
			$curShort .= strtoupper($curData);
		}

		Concat_Field($curShort, "");
		$i = $i + 1;
	}
	if ($contract == "Y")  {
		Concat_Field("@@stdt", $_POST['contractStart']);
		Concat_Field("@@exdt", $_POST['contractExpire']);
	}
	if ($bracketQty == "Y" || $bracketAmt == "Y") {
		for ($i = 1; $i <= 99; $i++) {
			if ($i < 10) {$i = "0" . $i;}
			$lmt = "lm" . $i;
			$amt = "am" . $i;
			$cmp = "cm" . $i;
			$mrp = "mr" . $i;
			if ($_POST[$lmt]>0) {
				Concat_Field("@@$lmt", $_POST[$lmt]);
				Concat_Field("@@$amt", $_POST[$amt]);
				Concat_Field("@@$cmp", $_POST[$cmp]);
				Concat_Field("@@$mrp", $_POST[$mrp]);
			}
		}
	} else {
		if ($usePercent == "Y") {
			Concat_Field("@@lcpc", $_POST['llCpPercent']);
		} else {
			Concat_Field("@@lcam", $_POST['llCpAmount']);
		}
		if ($commission == "Y" || $commission == "L") {
			Concat_Field("@@cmpc", $_POST['commPercent']);
		}
	}
	if ($useItem == "Y")  {
		Concat_Field("@@msrp", $_POST['msrp']);
	}
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HOEPRM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	$tag = "MAINTAIN";
	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, $levelDesc, $pricingLevel, "" , "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}PricingDetail.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		exit;
	} elseif ($maintenanceCode == "D") {
		$Err_Message=DecatErr_Field("@@pmlv", "pricingLevel");
		$confMessage=Format_ConfMsg_Desc("", $levelDesc, $pricingLevel, "<br>$Err_Message", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}PricingDetail.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		exit;
	}
}

if ($tag == "MAINTAIN") {
	$requiredFields = "";
	$editVariables =  "";

	foreach ($mdCol as $mdFld) {
		$curdType = trim($mdFld['PWDTYP']);
		$curfType = trim($mdFld['PWFTYP']);
		$curName = trim($mdFld['PWCOLN']);
		$curSize = trim($mdFld['PWFLEN']);
		$curDecm = trim($mdFld['PWFDEC']);
		$curDKey = trim($mdFld['PWDKEY']);
		if ($curDKey == "IMITEM") {$useItem = "Y";}
		$chgCurName = "chg$curName";

		if ($requiredFields != "") {$requiredFields .= "||" ;}
		$requiredFields .= " document.Chg.$chgCurName.value == \"\" ";

		if ($curdType == "NUMERIC" || $curdType == "DECIMAL") {
			if ($editVariables != "") {$editVariables .= " && " ;}
			if ($curfType == "CYMD" || $curfType == "ISO") {
				$editVariables .= " editdate(document.Chg.$chgCurName) ";
			} else {
				$editVariables .= " editNum(document.Chg." ;
				$editVariables .= "$chgCurName,$curSize,$curDecm)";
			}
		}
		if  ($curNameHld == "") {$curNameHld = $chgCurName;}
	}

	if ($contract == "Y") {
		if ($requiredFields != "") {$requiredFields .= " ||";}
		$requiredFields .= " document.Chg.contractStart.value == \"\" ";
		$requiredFields .= " || document.Chg.contractExpire.value == \"\" ";
		if ($editVariables != "") {$editVariables .= " &&";}
		$editVariables .= " editdate(document.Chg.contractStart)";
		$editVariables .= " && editdate(document.Chg.contractExpire)";
	}

	if ($useItem == "Y") {
		if ($editVariables != "") {$editVariables .= " && ";}
		$editVariables .= " editNum(document.Chg.msrp,8,5)";
	}

	if ($bracketQty == "Y" || $bracketAmt == "Y") {
		if ($editVariables != "") {$editVariables .= " && ";}
		$editVariables .= " editNum(document.Chg.limit,9,4)";
		$editVariables .= " && editNum(document.Chg.msrd,8,5)";
		
		if ($usePercent == "Y") {
			$editVariables .= " && editNum(document.Chg.amount,3,4)";
		} else  {
			$editVariables .= " && editNum(document.Chg.amount,8,5)";
		}
		if ($commission == "Y" || $commission == "L") {
			$editVariables .= " && editNum(document.Chg.compct,3,4)";
		}
	} else {
		if ($editVariables != "") {$editVariables .= " && ";}
		if ($usePercent == "Y") {
			$editVariables .= " editNum(document.Chg.llCpPercent,3,4)";
		} else {
			$editVariables .= " editNum(document.Chg.llCpAmount,8,5)";
		}
		if ($commission == "Y" || $commission == "L") {
			if ($editVariables != "") {$editVariables .= " && ";}
			$editVariables .= " editNum(document.Chg.commPercent,3,4)";
		}
	}

	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	require_once 'DateEdit.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	require_once 'CalendarInclude.php';
	print "\n function validate(chgForm) {";
	print "\n if ($requiredFields)";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if ($editVariables) ";
	print "\n return true;";
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
?>
	function checkAdd(comm,delimg) {
	  document.onkeyup = function(e){
	   if (!e) {
	     var k = (e) ? e.which:event.keyCode;
	     var t = (e) ? e.which:event.srcElement.type;
	   } else {
	     var node = e.target;
	     while(node.nodeType != node.ELEMENT_NODE)
		        node = node.parentNode;
	     var t = node.type;
	     var k = e.which;
	   }
	   if (k == 13 && t != 'textarea') 
         if (validate(document.Chg)) {
	       addIt(document.Chg,comm,delimg);
       }
	  }
	}
	
	function addIt(chgForm,comm,delimg) {
	  var limit = document.getElementById('limit').value;
	  var amount = document.getElementById('amount').value;
	  var msrd = document.getElementById('msrd').value;
	  if (document.getElementById('compct')) {
	    var compct = document.getElementById('compct').value;
	  } else {var compct = '';}
      if (validate(document.Chg)) {
	    addRow('dataTable',limit,amount,compct,msrd,comm,delimg);
      }
	}
	
	function deleteElement(id){ 
	  var el = document.getElementById(id);
	  el.parentNode.removeChild(el);
	  return false;
	}

    var count = "1";
    function addRow(tableID,limit,amount,compct,msrd,comm,delimg) {
        if (limit == "")  {limit  = 0;}
	if (amount == "") {amount = 0;}
	if (compct == "") {compct = 0;}
	if (msrd == "")   {msrd = 0;}
	var tbody = document.getElementById(tableID).getElementsByTagName("TBODY")[0];
	var table = document.getElementById(tableID);
      
	var rows=document.getElementById(tableID).getElementsByTagName('tr');
	var rowCount=rows.length;
        rowCount = parseInt(rowCount) - 1;
        if (rowCount > 98) {alert ('You have reached the maximum of 99 entries'); return;}
        var row = document.createElement("TR");
        var tableName = 'row' + rowCount;
        row.setAttribute('id',tableName);
    
        var td1 = document.createElement("TD")
        td1.setAttribute('class','inputnmbr');
        td1.setAttribute('className','inputnmbr');  // For IE
        if (rowCount < 10) {td1.innerHTML = "<input type=text id=lm0"+rowCount+" name=lm0"+rowCount+" value="+limit+" onkeyup=\"return editNum(document.Chg.lm0"+rowCount+",9,4)\">";}
        else               {td1.innerHTML = "<input type=text id=lm"+rowCount+" name=lm"+rowCount+" value="+limit+" onkeyup=\"return editNum(document.Chg.lm"+rowCount+",9,4)\">";}
    
        var td2 = document.createElement("TD")
        td2.setAttribute('class','inputnmbr');
        td2.setAttribute('className','inputnmbr');  // For IE
        if (rowCount < 10) {td2.innerHTML = "<input type=text id=am0"+rowCount+" name=am0"+rowCount+" value="+amount+" onkeyup=\"return editNum(document.Chg.am0"+rowCount+",8,5)\">";}
        else               {td2.innerHTML = "<input type=text id=am"+rowCount+" name=am"+rowCount+" value="+amount+" onkeyup=\"return editNum(document.Chg.am"+rowCount+",8,5)\">";}
    
        if (comm == 'Y' || comm == 'L') {
	    var td3 = document.createElement("TD")
	    td3.setAttribute('class','inputnmbr');
	    td3.setAttribute('className','inputnmbr');  // For IE
	    if (rowCount < 10) {td3.innerHTML = "<input type=text id=cm0"+rowCount+" name=cm0"+rowCount+" value="+compct+" onkeyup=\"return editNum(document.Chg.cm0"+rowCount+",3,4)\">";}
	    else               {td3.innerHTML = "<input type=text id=cm"+rowCount+" name=cm"+rowCount+" value="+compct+" onkeyup=\"return editNum(document.Chg.cm"+rowCount+",3,4)\">";}
        }
    
        var td4 = document.createElement("TD")
        td4.setAttribute('class','inputnmbr');
        td4.setAttribute('className','inputnmbr');  // For IE
        if (rowCount < 10) {td4.innerHTML = "<input type=text id=mr0"+rowCount+" name=mr0"+rowCount+" value="+msrd+" onkeyup=\"return editNum(document.Chg.mr0"+rowCount+",8,5)\">";}
        else               {td4.innerHTML = "<input type=text id=mr"+rowCount+" name=mr"+rowCount+" value="+msrd+" onkeyup=\"return editNum(document.Chg.mr"+rowCount+",8,5)\">";}
       
        var td5 = document.createElement("TD")
        td5.setAttribute('class','colicon');
        td5.setAttribute('className','colicon');  // For IE
        var img = document.createElement('IMG');
        img.setAttribute('src', delimg);
        img.setAttribute('title', 'Remove row');
        img.onclick = function(){delRow(row);}
        td5.appendChild(img);
   
        // append data to row
        row.appendChild(td1);
        row.appendChild(td2);
        if (comm == 'Y' || comm == 'L') {
            row.appendChild(td3);
        }
        // append row to table
        row.appendChild(td4);
        row.appendChild(td5);
        tbody.appendChild(row);
     
        document.Chg.limit.value="";
        document.Chg.amount.value="";
        if (comm == 'Y' || comm == 'L') {
            document.Chg.compct.value="";
        }
        document.Chg.msrd.value="";
        setTimeout(document.Chg.limit.focus(), 50);
    }
  
function delRow(row){row.parentNode.removeChild(row);}


</script>
<?php
require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr onKeyPress=\"checkAdd('$commission','{$homeURL}{$imagePath}smDelete.gif')\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "PRICINGDETAILMAINTAIN";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
$stmtSQL= "";
if ($maintenanceCode == "A") {
	require_once 'AddRecordSQL.php';
} else {
	$stmtSQL .= " Select * ";
	$stmtSQL .= " From HDPRCD ";
	$stmtSQL .= " Where PMPMLV=$pricingLevel and PMPMKY='$pricingKey' and PMSTDT=$contractStart ";
}
require 'stmtSQLEnd.php';

// Program Option Security
$hoeplm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01=$hoeplm_OPT['sec_01'];
$sec_02=$hoeplm_OPT['sec_02'];
$sec_03=$hoeplm_OPT['sec_03'];
$sec_04=$hoeplm_OPT['sec_04'];

require_once 'MaintainTop.php';

print "\n <table $contentTable>";
Format_Header_URL("Pricing Level", $levelDesc, $pricingLevel, "");
if ($listLess == "Y")   {$structureDefn = "List Less";}
if ($costPlus == "Y")   {$structureDefn = "Cost Plus";}
if ($dollarAmt == "Y")  {$structureDefn = "Amount";}
if ($dollarAmt != "Y")  {if ($usePercent == "Y") {$structureDefn .= " Percentage";} else {$structureDefn .= " Amount";}}
print "\n <tr><td class=\"hdrtitl\">Definition:</td>";
if ($contract == "Y")  {
	print "\n   <td class=\"hdrdata\">Contract</td></tr>";
	print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">$structureDefn</td></tr>";
} else {
	print "\n   <td class=\"hdrdata\">$structureDefn</td></tr>";
}
if  ($bracketQty == "Y")  {
	print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">Bracket By Quantity</td></tr> ";
} elseif ($bracketAmt == "Y") {
	print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">Bracket By Amount</td></tr> ";
}
if ($commission != "") {
	print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">";
	if ($commission == "Y")     {print "Commissionable";}
	elseif ($commission == "N") {print "Non-Commissionable";}
	elseif ($commission == "L") {print "Limited Commission";}
	print "\n   </td></tr>";
}
print "\n </table>";
print $hrTagAttr;
require_once 'RequiredField.php';
require_once 'ErrorDisplay.php';

$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

if ($errFound != "" || $maintenanceCode=="A") {
	if ($errFound == "" && $maintenanceCode=="A") {
		$edtVar= "";
	} else {
		$focusField= "";
		$curName = trim($mdCol[0]['PWCOLN']);
		$Err_General=DecatErr_Field("@@gerr", "chg$curName");
		$Err_PMSTDT =DecatErr_Field("@@stdt", "contractStart");
		$Err_PMEXDT =DecatErr_Field("@@exdt", "contractExpire");
		$Err_PMLCAM =DecatErr_Field("@@lcam", "llCpAmount");
		$Err_PMLCPC =DecatErr_Field("@@lcpc", "llCpPercent");
		$Err_PMCMPC =DecatErr_Field("@@cmpc", "commPercent");
	}

	$row['PMPMKY']=Decat_Field("@@pmky", $edtVar);
	$row['PMSTDT']=Decat_Field("@@stdt", $edtVar);
	$row['PMEXDT']=Decat_Field("@@exdt", $edtVar);
	$row['PMLCAM']=Decat_Field("@@lcam", $edtVar);
	$row['PMLCPC']=Decat_Field("@@lcpc", $edtVar);
	$row['PMCMPC']=Decat_Field("@@cmpc", $edtVar);
	$row['PMMSRP']=Decat_Field("@@msrp", $edtVar);

	if ($errFound == "" && $maintenanceCode == "A") {
		$focusField = $curNameHld ;
	}

} elseif ($maintenanceCode == "Z") {
	$focusField = $curNameHld ;
	$F_PMSTDT=Format_Date($row['PMSTDT'], "D");
	$row['PMSTDT']=DateInputFromCYMD($row['PMSTDT']);
	$F_PMEXDT=Format_Date($row['PMEXDT'], "D");
	$row['PMEXDT']=DateInputFromCYMD($row['PMEXDT']);
	$row['PMLCPC'] = Format_Nbr(($row['PMLCPC'] * 100), "4", "4", "Y", "", "");
	$row['PMCMPC'] = Format_Nbr(($row['PMCMPC'] * 100), "4", "4", "Y", "", "");
} else {
	if ($contract == "Y")		{$focusField = "contractExpire";}
	elseif ($bracketQty == "Y" || $bracketAmt == "Y") {$focusField = "limit";}
	elseif ($usePercent == "Y") {$focusField = "llCpPercent";}
	else                        {$focusField = "llCpAmount";}

	$row['PMLCPC'] = Format_Nbr(($row['PMLCPC'] * 100), "4", "4", "Y", "", "");
	$row['PMCMPC'] = Format_Nbr(($row['PMCMPC'] * 100), "4", "4", "Y", "", "");
	$F_PMSTDT=Format_Date($row['PMSTDT'], "D");
	$row['PMSTDT']=DateInputFromCYMD($row['PMSTDT']);
	$F_PMEXDT=Format_Date($row['PMEXDT'], "D");
	$row['PMEXDT']=DateInputFromCYMD($row['PMEXDT']);
}

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
print "\n <table $contentTable>";
if ($Err_General != "")  {
	print "\n  <tr><td>&nbsp;</td><td class=\"error\" colspan=\"10\">$Err_General</td></tr>";
}

foreach ($mdCol as $mdFld)  {
	$curText    = trim($mdFld['PWCTXT']);
	$curdType   = trim($mdFld['PWDTYP']);
	$curName    = trim($mdFld['PWCOLN']);
	$curSize    = trim($mdFld['PWFLEN']);
	$curDKey    = trim($mdFld['PWDKEY']);
	$curDTbl    = trim($mdFld['PWDTBL']);
	$curDCol    = trim($mdFld['PWDCOL']);
	$curSearch  = trim($mdFld['PWSD2W']);
	$curData    = "";
	if ($maintenanceCode != "Z") {$curData = rtrim($row[$curName]);}
	$chgCurName = "chg$curName";
	if ($errFound != "") {
		$curShort = "@@";
		$curShort .= strtolower(substr($curName, 2));
		$curShort = str_pad($curShort,6,"@");
		$Err_Message = DecatErr_Field ($curShort, $chgCurName);
		$curData = Decat_Field($curShort, $edtVar) ;
	}  else  {
		$Err_Message = "";
	}

	$curNameDesc = "";
	if ($curdType =="NUMERIC" || $curdType =="DECIMAL") {
		$cssClass = "inputnmbr";
		if ($errFound == "" && ($maintenanceCode=="A" || $maintenanceCode=="Z")) {$curData="";}
		$curNameDesc = RetValue("$curDKey = $curData ", "$curDTbl", "$curDCol");
	}  else  {
		$cssClass = "inputalph";
		$curNameDesc = RetValue("$curDKey ='$curData' ", "$curDTbl", "$curDCol");
	}
	if ($curData == "") {$curNameDesc = "";}

	$textOvr=SetTextOvr($Err_Message);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>$curText</span></td>";
	if ($maintenanceCode == "A" || $maintenanceCode == "Z") {
		$srch = "{$phpPath}{$curSearch}.php{$genericVarBase}";
		print "\n  <td class=\"$cssClass\"><input type=\"text\"  name=\"$chgCurName\" value=\"$curData\" size=\"22\" maxlength=\"$curSize\">";
		print "\n  <a href=\"{$homeURL}{$srch}&amp;docName=Chg&amp;fldName={$chgCurName}&amp;fldDesc={$chgCurName}Desc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage </a><span class=\"dspdesc\" id=\"{$chgCurName}Desc\">$curNameDesc</span></td>";
	} else {
		$F_curData = Format_Code($curData);
		print "\n <td class=\"$cssClass\"><input type=\"hidden\" name=\"$chgCurName\" value=\"$curData\">$curNameDesc &nbsp; $F_curData</td>";
	}
	print "\n </tr>";
	DspErrMsg($Err_Message);
}

if ($contract == "Y") {
	$textOvr=SetTextOvr($Err_PMSTDT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Contract Start Date </span></td>";
	if ($maintenanceCode == "A" || $maintenanceCode == "Z") {
		print "\n  <td class=\"inputnmbr\"><input type=\"text\" name=\"contractStart\" value=\"" . rtrim($row['PMSTDT']) . "\"  size=\"7\" maxlength=\"6\">";
		print "\n  <a href=\"javascript:calWindow('contractStart');\">$reqFieldChar $calendarImage</a></td>";
	} else {
		print "\n  <td class=\"dspnmbr\"><input type=\"hidden\" name=\"contractStart\" value=\"" . rtrim($row['PMSTDT']) . "\">$F_PMSTDT</td>";
	}
	print "\n </tr>";
	DspErrMsg($Err_PMSTDT);

	$textOvr=SetTextOvr($Err_PMEXDT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Contract Expiration Date </span></td>";
	if ($maintenanceCode == "A" || $maintenanceCode == "C" || $maintenanceCode == "Z") {
		print "\n  <td class=\"inputnmbr\"><input type=\"text\"  name=\"contractExpire\" value=\"" . rtrim($row['PMEXDT']) . "\"  size=\"7\" maxlength=\"6\">";
		print "\n     <a href=\"javascript:calWindow('contractExpire');\">$reqFieldChar $calendarImage</a></td>";
	} else {
		print "\n  <td class=\"dspnmbr\"><input type=\"hidden\"  name=\"contractExpire\" value=\"" . rtrim($row['PMEXDT']) . "\">$F_PMEXDT</td>";
	}
	print "\n </tr>";
	DspErrMsg($Err_PMEXDT);
}


if ($bracketQty != "Y" && $bracketAmt != "Y")  {
	if ($usePercent == "Y") {
		$textOvr=SetTextOvr($Err_PMLCPC);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>$structureDefn</span></td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"  name=\"llCpPercent\" value=\"" . rtrim($row['PMLCPC']) . "\" size=\"20\" maxlength=\"8\"></td>";
		print "\n </tr>";
		DspErrMsg($Err_PMLCPC);
	} else {
		$textOvr=SetTextOvr($Err_PMLCAM);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>$structureDefn</span></td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"  name=\"llCpAmount\" value=\"" . rtrim($row['PMLCAM']) . "\" size=\"20\" maxlength=\"14\"></td>";
		print "\n </tr>";
		DspErrMsg($Err_PMLCAM);
	}
	if ($commission == "Y" || $commission == "L") {
		$textOvr=SetTextOvr($Err_PMCMPC);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Commission Percent</span></td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"  name=\"commPercent\" value=\"" . rtrim($row['PMCMPC']) . "\" size=\"20\" maxlength=\"8\"></td>";
		print "\n </tr>";
		DspErrMsg($Err_PMCMPC);
	}
}

if ($useItem == "Y")  {
	$textOvr=SetTextOvr($Err_PMMSRP);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>MSRP</span></td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"  name=\"msrp\" value=\"" . rtrim($row['PMMSRP']) . "\" size=\"20\" maxlength=\"8\"></td>";
	print "\n </tr>";
	DspErrMsg($Err_PMMSRP);
}
print "\n <tr>";
print "\n <td>&nbsp;</td>";

if ($bracketQty == "Y" || $bracketAmt == "Y")  {
	print "\n <td><table $contentTable id=\"dataTable\"><tr>";

	if ($bracketQty == "Y") {$brkDesc = "Quantity";} else {$brkDesc = "Amount";}
	print "\n   <th class=\"colhdr\">$brkDesc Limit</th>";
	print "\n   <th class=\"colhdr\">$structureDefn</th>";
	if ($commission == "Y" || $commission == "L") {
		print "\n <th class=\"colhdr\">Commission<br>Percent</th>";
	}
	print "\n   <th class=\"colhdr\">MSRP</th>";
	print "\n </tr>";

	print "\n  <tr><td class=\"inputnmbr\"><input type=\"text\" name=\"limit\" id=\"limit\" value=\"\" size=\"20\" maxlength=\"14\"></td>";
	print "\n      <td class=\"inputnmbr\"><input type=\"text\" name=\"amount\" id=\"amount\" value=\"\" size=\"20\" maxlength=\"14\"></td>";
	if ($commission == "Y" || $commission == "L") {
		print "\n <td class=\"inputnmbr\"><input type=\"text\"  name=\"compct\" id=\"compct\" value=\"\" size=\"20\" maxlength=\"8\"></td>";
	}
	print "\n      <td class=\"inputnmbr\"><input type=\"text\" name=\"msrd\" id=\"msrd\" value=\"\" size=\"20\" maxlength=\"14\"></td>";
	print "\n <td><a href=\"javascript:addIt(document.Chg,'$commission','{$homeURL}{$imagePath}smDelete.gif')\">&nbsp; $acceptImageMed</a></td>";
	print "\n </tr>";

	if ($errFound == "") {
		$stmtSQL  = " Select * ";
		$stmtSQL .= " From HDPRCB ";
		$stmtSQL .= " Where PBPMLV=$pricingLevel and PBPMKY='$pricingKey' and PBSTDT=$contractStart ";
		require 'stmtSQLEnd.php';
		$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
		while ($row = db2_fetch_assoc($sqlResult, $startRow)){
			if ($usePercent == "Y")  {$amtPct = $row[PBPCT] * 100;} else {$amtPct = $row[PBPRC];}
			$compct = $row[PBCMP] * 100;
			print "\n <script TYPE=\"text/javascript\">";
			print "\n addRow('dataTable','$row[PBLMT]','$amtPct','$compct','$row[PBMSRP]','$commission','{$homeURL}{$imagePath}smDelete.gif')";
			print "\n </script>";
			$startRow++;
		}
	} else {
		for ($i = 1; $i <= 99; $i++) {
			if ($i < 10) {$i = "0" . $i;}
			$lmt = "lm" . $i;
			$amt = "am" . $i;
			$cmp = "cm" . $i;
			$mrp = "mr" . $i;
			if (isset($_POST[$lmt])) {
				print "\n <script TYPE=\"text/javascript\">";
				print "\n addRow('dataTable','$_POST[$lmt]','$_POST[$amt]','$_POST[$cmp]','$_POST[$mrp]','$commission','{$homeURL}{$imagePath}smDelete.gif')";
				print "\n </script>";
			}
		}
	}
	print "\n </table>";
}

print "\n </tr>";
print "\n </table>";
print "\n <script TYPE=\"text/javascript\">document.Chg.$focusField.focus();</script>";
print "\n </form>";

require_once 'MaintainBottom.php';
print $hrTagAttr ;
require_once 'Copyright.php';
print "\n </td></tr>";
print "\n </table>";
require_once 'Trailer.php';
print "</body> </html>";
}


?>