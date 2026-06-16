<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound = (isset ( $_GET ['errFound'] )) ? $GET ['errFound'] : "";
$touchScreen = (isset ( $_GET ['touchScreen'] )) ? $_GET ['touchScreen'] : "";

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once "ETControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "Labor Entry Select Orders";
$scriptName = "LaborEntrySelectOrders.php";
$scriptVarBase = "{$genericVarBase}&amp;touchScreen=Y";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL = "{$scriptName}{$scriptVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$d2wVarBase = "{$altVarBase}&amp;touchScreen=Y";
$programName = "HETLBE";
$maxRows = "8";
$dspMaxRows = 8;
$rowIndexCurr = $startRow;
$groupNbr = "0";
$plantNumber = "";
$focusField = "";
$filterURL = "{$scriptName}{$scriptVarBase}";

$dftOrderBy = array (array ("WOCSDT", "A", "Start Date" ), array ("WOMORD", "A", "Order" ), array ("WOSEQN", "A", "Rtg Seq" ) );

$returnURL = "LaborEntryOverride.php{$scriptVarBase}&amp;tag=BUILD";

if ($tag == "NOUPDATE") {
	$confMessage = "Confirm No Update";
	print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$returnURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
	exit ();
}

$stmtSQL = " Select * From SIDCTW Where WDXHND='$profileHandle'";
$stmtSQL .= " Fetch First 1 Row Only With NC";
$sqlResultDC = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$rowDC = db2_fetch_assoc ( $sqlResultDC );
$plantNumber = $rowDC ['WDPLT'];

if ($tag == "BUILD") {
	$lastLaborType = getLastLaborType ( $i5Connect, $rowDC );
	
	$stmtSQL = " Delete From SILOPW Where WOXHND='$profileHandle'";
	$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	
	require 'stmtSQLClear.php';
	$fileName = ' HDMLPM  Inner Join HDMOHM on LPPLT=OHPLT and LPORD=OHORD';
	$selectRecord = " LPREAS<>'D' and LPRTYP<>'V' and LPALTC='P' and LPRELC='R' and LPOPRC<>'Y' and OHSTC not in ('C','P')";
	$selectRecord .= " and Not Exists (Select WOXHND From SILOPW Where WOXHND='{$profileHandle}' and WOMORD=LPORD and WOSEQN=LPSEQN)";
	$selectRecord .= " and Not Exists (Select EHSCTL From HDMECH Where EHPLNT=LPPLT and EHORD=LPORD and EHSEQN=LPSEQN and Substr(EHTRAN,2,1)='0'";
	if ($rowDC ['WDGRP'] == "0") {
		$selectRecord .= " and EHCO={$rowDC['WDCOMP']} and EHFAC={$rowDC['WDFACL']} and EHEMP={$rowDC['WDEMPL']})";
	} else {
		$selectRecord .= " and EHGRP={$rowDC['WDGRP']})";
	}
	$selectRecord .= " and LPPLT={$plantNumber} and LPDEPT='{$rowDC['WDDEPT']}' and LPWC='{$rowDC['WDWC']}' ";
	
	$stmtSQL = " Insert Into SILOPW
	(WOXHND,WOMORD,WOSEQN,WOPLT,WODEPT,WOWC,WOCSDT,WOCDDT,WOITEM,WOCPNS,WOOSEL,WOPGM,WOQTYR,WOHRSR,WOMGTP,WOORD#,WOLBTY)
	Select
	'{$profileHandle}',LPORD,LPSEQN,LPPLT,LPDEPT,LPWC,LPCSDT,LPCDDT,OHPN,OHCPNS,'{$orderSelected}','WEBOLE',
	case when (ohcqty-lpqtyc-lprscr)>0 then (ohcqty-lpqtyc-lprscr)
	else 0
	end as QTYR,
	case when lpmhrc='M' and (ohcqty-lpqtyc-lprscr)>0 then ((ohcqty-lpqtyc-lprscr)*lpmhrs)/1000+lpsuhr
	when lpmhrc='H' and (ohcqty-lpqtyc-lprscr)>0 then ((ohcqty-lpqtyc-lprscr)*lpmhrs)/100+lpsuhr
	when (ohcqty-lpqtyc-lprscr)>0 then ((ohcqty-lpqtyc-lprscr)*lpmhrs)+lpsuhr
	else 0
	end as HRSR,
	LPMGTP,
	OHORD#,
	'{$lastLaborType}'
	from $fileName
	where $selectRecord";
	// begin debugging code
	// echo $stmtSQL;
	// exit;
	// end debugging code
	$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
}

if ($tag == "SELECT") {
	$errFound = '';
	
	require 'stmtSQLClear.php';
	$fieldName = ' count(*) ';
	$fileName = ' HDMLPM  Inner Join HDMOHM on LPPLT=OHPLT and LPORD=OHORD';
	$selectRecord = " LPREAS<>'D' and LPRTYP<>'V' and LPALTC='P' and LPRELC='R' and LPOPRC<>'Y' and OHSTC not in ('C','P')";
	$selectRecord .= " and Not Exists (Select WOXHND From SILOPW Where WOXHND='{$profileHandle}' and WOMORD=LPORD and WOSEQN=LPSEQN)";
	$selectRecord .= " and Not Exists (Select EHSCTL From HDMECH Where EHPLNT=LPPLT and EHORD=LPORD and EHSEQN=LPSEQN and Substr(EHTRAN,2,1)='0'";
	if ($rowDC ['WDGRP'] == "0") {
		$selectRecord .= " and EHCO={$rowDC['WDCOMP']} and EHFAC={$rowDC['WDFACL']} and EHEMP={$rowDC['WDEMPL']})";
	} else {
		$selectRecord .= " and EHGRP={$rowDC['WDGRP']})";
	}
	$orderSelected = '';
	
	if (trim ( $_POST ['turn'] ) != '') {
		$orderSelected = 'Y';
		$selectRecord .= " and LPLBTN={$_POST['turn']} ";
		$turnFound = RetValue ( $selectRecord, $fileName, $fieldName );
		if ($turnFound == '0') {
			$Err_turn = 'Invalid Turnaround Number';
			$focusField = 'turn';
			$errFound = 'Y';
		}
	} elseif (trim ( $_POST ['order'] ) != '') {
		$orderSelected = 'Y';
		$_POST ['order'] = strtoupper ( $_POST ['order'] );
		$selectRecord .= " and LPPLT={$plantNumber} and LPORD='{$_POST['order']}' and LPSEQN={$_POST['seqn']} ";
		$orderFound = RetValue ( $selectRecord, $fileName, $fieldName );
		if ($orderFound == '0') {
			$Err_order = 'Invalid Order Number / Rtg Seq';
			$focusField = 'order';
			$errFound = 'Y';
		}
	} elseif (trim ( $_POST ['dept'] ) != '') {
		$_POST ['dept'] = strtoupper ( $_POST ['dept'] );
		$selectRecord .= " and LPPLT={$plantNumber} and LPDEPT='{$_POST['dept']}' ";
		if (trim ( $_POST ['wc'] ) != '') {
			$_POST ['wc'] = strtoupper ( $_POST ['wc'] );
			$selectRecord .= " and LPWC='{$_POST['wc']}' ";
		}
		$deptFound = RetValue ( $selectRecord, $fileName, $fieldName );
		if ($deptFound == '0') {
			$Err_dept = 'No Operations Found for Department / Work Center';
			$focusField = 'dept';
			$errFound = 'Y';
		}
	}
	
	if ($errFound != 'Y') {
		$lastLaborType = getLastLaborType ( $i5Connect, $rowDC );
		
		$stmtSQL = " Insert Into SILOPW
		(WOXHND,WOMORD,WOSEQN,WOPLT,WODEPT,WOWC,WOCSDT,WOCDDT,WOITEM,WOCPNS,WOOSEL,WOPGM,WOQTYR,WOHRSR,WOMGTP,WOORD#,WOLBTY)
		Select
		'{$profileHandle}',LPORD,LPSEQN,LPPLT,LPDEPT,LPWC,LPCSDT,LPCDDT,OHPN,OHCPNS,'{$orderSelected}','WEBOLE',
		case when (ohcqty-lpqtyc-lprscr)>0 then (ohcqty-lpqtyc-lprscr)
		else 0
		end as QTYR,
		case when lpmhrc='M' and (ohcqty-lpqtyc-lprscr)>0 then ((ohcqty-lpqtyc-lprscr)*lpmhrs)/1000+lpsuhr
		when lpmhrc='H' and (ohcqty-lpqtyc-lprscr)>0 then ((ohcqty-lpqtyc-lprscr)*lpmhrs)/100+lpsuhr
		when (ohcqty-lpqtyc-lprscr)>0 then ((ohcqty-lpqtyc-lprscr)*lpmhrs)+lpsuhr
		else 0
		end as HRSR,
		LPMGTP,
		OHORD#,
		'{$lastLaborType}'
		from HDMLPM inner join HDMOHM on lpplt=ohplt and lpord=ohord
		where $selectRecord";
		// begin debugging code
		// echo $stmtSQL;
		// exit;
		// end debugging code
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	}
}

if ($tag == "Edit_Data") {
	
	for($i = 1; $i <= $_POST ['displayedRows']; $i ++) {
		$x = str_pad ( $i, 4, '0', STR_PAD_LEFT );
		$OR = "OR$x";
		$OS = "OS$x";
		$LT = "LT$x";
		$stmtSQL = " Update SILOPW Set WOLBTY=Upper('{$_POST[$LT]}') Where WOXHND='{$profileHandle}' and WOMORD='{$_POST[$OR]}' and WOSEQN={$_POST[$OS]}";
		$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	}
	
	if ($_POST ['rowIndexNext'] != '' || $_POST ['rowIndexPrev'] != '') {
		$startRow = ($_POST ['rowIndexNext'] != '') ? $_POST ['rowIndexNext'] : $_POST ['rowIndexPrev'];
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL=$baseURL&amp;startRow=$startRow&amp;timeStamp=" . urlencode ( trim ( $_SERVER ['REQUEST_TIME'] ) ) . "\">";
		exit ();
	}
	
	$edtVar = "";
	
	Concat_Field ( "@@subr", "SrEditS" );
	Concat_Field ( "@@rows", $_POST ['displayedRows'] );
	Concat_Field ( "@@date", $_POST ['transDate'] );
	Concat_Field ( "@@time", $_POST ['transTime'] );
	for($i = 1; $i <= $_POST ['displayedRows']; $i ++) {
		$x = str_pad ( $i, 4, '0', STR_PAD_LEFT );
		$OR = "OR$x";
		$OS = "OS$x";
		$LT = "LT$x";
		Concat_Field ( "$OR", $_POST [$OR] );
		Concat_Field ( "$OS", $_POST [$OS] );
		Concat_Field ( "$LT", $_POST [$LT] );
	}
	
	$edtVar .= "}{";
	
	// echo '<br>Value of $profileHandle=',$profileHandle;
	// echo '<br>Value of $dataBaseID=',$dataBaseID;
	// echo '<br>Value of $errFound=',$errFound;
	// echo '<br>Value of $edtVar=',$edtVar;
	// echo '<br>Value of $errVar=',$errVar;
	// exit;
	
	$returnValue = Validate_Data ( "HETLBE_W", $profileHandle, $dataBaseID, $errFound, $edtVar, $errVar );
	$errFound = $returnValue ['errFound'];
	$edtVar = $returnValue ['edtVar'];
	$errVar = $returnValue ['errVar'];
	
	// echo 'Value of $edtVar=',$edtVar,'<br> Value of $errFound=',$errFound,'<br> Value of $errVar=',$errVar,'<br> Value of $_POST[\'rowIndexNext\']=',$_POST['rowIndexNext'],'<br> Value of $_POST[\'rowIndexPrev\']=',$_POST['rowIndexPrev'],'<br> Value of $_POST[\'rowIndexCurr\']=',$_POST['rowIndexCurr'] ;
	// exit;
	
	if ($errFound == "") {
		if ($_POST ['reportOffOn'] == 'on') {
			$confMessage = "Confirm Override Labor Clocked On";
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}ShopFloorDispatch.d2w/BUILD{$altVarBase}&amp;touchScreen=Y&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		} else {
			$confMessage = "Confirm Override Labor Orders Selected";
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$returnURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		}
		exit ();
	} elseif ($errFound == "C") {
		$confMessage = DecatErr_Field ( "@@cmsg", "" );
		if ($_POST ['reportOffOn'] == 'on') {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}ShopFloorDispatch.d2w/BUILD{$altVarBase}&amp;touchScreen=Y&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		} else {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$returnURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		}
		exit ();
	} elseif ($errFound == "X") {
	    print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}EmployeeSignOn.d2w/REPORT{$d2wVarBase}\"> ";
	    exit ();
	}
	// } else {
	// EdtVarErr($profileHandle, $edtVar);
	// ErrVarErr($profileHandle, $errVar);
	// print "\n <meta http-equiv=\"refresh\" content=\"0; URL=$baseURL&amp;startRow={$_POST['rowIndexCurr']}&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . "\"> ";
	// exit;
	// }
}

// Dispatch Program Option Security
$hsi214_OPT = pgmOptSecurity ( $profileHandle, $dataBaseID, 'HSI214' );

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if ($sequence == "ITEM") {
		$orby = array (array ("WOITEM", "A", "Item Number" ), array ("WOCSDT", "A", "Start Date" ), array ("WOMORD", "A", "Order" ), array ("WOSEQN", "A", "Rtg Seq" ) );
	} elseif ($sequence == "CPNS") {
		$orby = array (array ("WOCPNS", "A", "Shortage" ), array ("WOCSDT", "A", "Start Date" ), array ("WOMORD", "A", "Order" ), array ("WOSEQN", "A", "Rtg Seq" ) );
	} elseif ($sequence == "ORD") {
		$orby = array (array ("WOMORD", "A", "Order" ), array ("WOSEQN", "A", "Rtg Seq" ) );
	} elseif ($sequence == "SEQN") {
		$orby = array (array ("WOSEQN", "A", "Rtg Seq" ), array ("WOMORD", "A", "Order" ) );
	} elseif ($sequence == "CSDT") {
		$orby = array (array ("WOCSDT", "A", "Start Date" ), array ("WOMORD", "A", "Order" ), array ("WOSEQN", "A", "Rtg Seq" ) );
	} elseif ($sequence == "CDDT") {
		$orby = array (array ("WOCDDT", "A", "Due Date" ), array ("WOMORD", "A", "Order" ), array ("WOSEQN", "A", "Rtg Seq" ) );
	} elseif ($sequence == "QTYR") {
		$orby = array (array ("WOQTYR", "A", "Quantity" ), array ("WOMORD", "A", "Order" ), array ("WOSEQN", "A", "Rtg Seq" ) );
	} elseif ($sequence == "HRSR") {
		$orby = array (array ("WOHRSR", "A", "Hours" ), array ("WOMORD", "A", "Order" ), array ("WOSEQN", "A", "Rtg Seq" ) );
	}
	require_once 'OrderByUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Chg";
// Program Option Security
$prog_OPT = pgmOptSecurity ( $profileHandle, $dataBaseID, $programName );
$sec_01 = $prog_OPT ['sec_01'];

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';
require_once 'CheckEnterChg.php';
require_once 'DateEdit.php';
require_once 'KeyboardFunctionsTS.js';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
?>
function selUpdate(ord,seq,row) {
	var selFld="selRec"+row;
	<?php print "\n var url =\"{$homeURL}{$phpPath}LaborEntryOverrideSelUpdate.php?baseVar=" . urlencode($baseVar) . "&eID=" . urlencode($eID) . "\"; \n"; ?>
    	url += "&mfgOrder=" + escape(ord);
    	url += "&seqNumber=" + escape(seq);
	var sel='N';
		if (document.getElementById(selFld).checked) {sel='Y';}
    	url += "&selFlag=" + escape(sel);
    	url += "&dummy=" + new Date().getTime();
	var ajaxRequest = new ajaxObject(url,selUpdateResponse);
		ajaxRequest.update();
}

function selUpdateResponse(responseText, responseStatus) {
	if (responseStatus==200) {
    } else  { alert(responseStatus + " -- Error Processing Request");}
}

function validate(chgForm) {
	if (editNum(chgForm.turn, 9, 0) && 
	    editNum(chgForm.seqn, 3, 0)) 
	return true;
}

function checkSel(selForm) {
  if (validateSel(selForm))
    selForm.submit();
}

function validateSel(selForm) {
	<?php if ($sec_01 == 'Y' && $TAALRT == 'Y') : ?>
    if (selForm.transDate.value =="" ||
        selForm.transTime.value =="")
       {alert("<?php echo $reqFieldError; ?>"); return false;}
	if (editNum(selForm.transDate, 6, 0) &&
        editdate(selForm.transDate) &&
        <?php if ($TADSSF == "Y") : ?>
        editNum(selForm.transTime, 6, 0)
        <?php else :?>
        editNum(selForm.transTime, 4, 0)
        <?php endif; ?>
       )
	<?php endif; ?>
	return true;
}
             
function nextPage(selForm,startRow) {
	if (validateSel(selForm)) {
	   	selForm.rowIndexNext.value=startRow;
    	selForm.submit();
    }
}
function prevPage(selForm,startRow) {
	if (validateSel(selForm)) {
	  	selForm.rowIndexPrev.value=startRow;
    	selForm.submit();
    }
}
<?php
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";
print "\n   <table $baseTable>";
print "\n     <tr valign=\"top\">";
print "\n       <td class=\"content\">";

print "\n         <table $contentTable>";
print "\n           <tr><td><h1>$page_title</h1></td></tr>";
print "\n         </table>";

print "\n         <table $contentTable>";
Format_Header ( "Plant", $rowDC ['WDPNAM'], $rowDC ['WDPLT'] );
if ($rowDC ['WDGRP'] == "0") {
	$groupNbr = "0";
	Format_Header ( "Employee", $rowDC ['WDENAM'], "" );
} else {
	$groupNbr = $rowDC ['WDGRP'];
	Format_Header ( "Group", $rowDC ['WDGDSC'], "" );
}
print "\n         </table>";

// require_once 'ConfMessageDisplay.php';
print $hrTagAttr;
require_once 'RequiredField.php';
require_once 'ErrorDisplay.php';

if ($errFound != "") {
	// $edtVar=EdtVarErr($profileHandle, $edtVar);
	// $errVar=ErrVarErr($profileHandle, $errVar);
	// $Err_TRAN = DecatErr_Field ( "@@dtcl", "currTrans" );
	$Err_transDate = DecatErr_Field ( "@@date", "transDate" );
	$Err_transTime = DecatErr_Field ( "@@time", "transTime" );
	
	$rowDC ['WDDATE'] = Decat_Field ( "@@date", $edtVar );
	$rowDC ['WDTIME'] = Decat_Field ( "@@time", $edtVar );
	
	$errFound = "";
} else {
	$_POST ['turn'] = '';
	$_POST ['order'] = '';
	$_POST ['seqn'] = '';
	$_POST ['dept'] = '';
	$_POST ['wc'] = '';
	$rowDC ['WDDATE'] = DateInputFromCYMD ( $rowDC ['WDDATE'] );
	$rowDC ['WDTIME'] = TimeInputFromHMS ( $rowDC ['WDTIME'], $TADSSF );
	$focusField = 'turn';
}

// ///////////////////
// Select Operations
print "\n <form class=\"formClass\" method=\"post\" name=\"Chg\" onSubmit=\"return Validate(document.Chg)\" action=\"{$baseURL}&amp;tag=SELECT\">";
print "\n <table $contentTable>";

$textOvr = SetTextOvr ( $Err_turn );
print "\n <tr><td class=\"dsphdr\"><span $textOvr>Turnaround Number</span></td>";
print "\n <td class=\"input\"><input type=\"text\" name=\"turn\" id=\"turn\" onfocus=\"kbActive(this, 'numericKeyboard');\" value=\"{$_POST['turn']}\" size=\"10\" maxlength=\"9\"></td>";
print "\n <td class=\"error\">$Err_turn</td>";
print "\n </tr> ";

$textOvr = SetTextOvr ( $Err_order );
print "\n <tr><td class=\"dsphdr\"><span $textOvr>Order Number / Rtg Seq</span></td>";
print "\n <td class=\"inputalph\"><input type=\"text\" name=\"order\" onfocus=\"kbActive(this, 'keyboard');\" value=\"" . rtrim ( $_POST ['order'] ) . "\" size=\"10\" maxlength=\"9\">";
print "\n                       / <input type=\"text\" name=\"seqn\" onfocus=\"kbActive(this, 'numericKeyboard');\" value=\"{$_POST['seqn']}\" size=\"5\" maxlength=\"3\">";
print "\n                         <a href=\"{$homeURL}{$phpPath}LaborInProcessSearch.php{$genericVarBase}&amp;forPlant=" . urlencode ( $rowDC ['WDPLT'] ) . "&amp;docName=Chg&amp;fldorder=order&amp;fldseqn=seqn\" onclick=\"$searchWinVar\">$searchImage</a> ";
print "\n </td>";
print "\n <td class=\"error\">$Err_order</td>";
print "\n </tr> ";

$textOvr = SetTextOvr ( $Err_dept );
print "\n <tr><td class=\"dsphdr\"><span $textOvr>Department / Work Center</span></td>";
print "\n <td class=\"inputalph\"><input type=\"text\" name=\"dept\" onfocus=\"kbActive(this, 'keyboard');\" value=\"" . rtrim ( $_POST ['dept'] ) . "\" size=\"5\" maxlength=\"5\">";
print "\n                       / <input type=\"text\" name=\"wc\" onfocus=\"kbActive(this, 'keyboard');\" value=\"" . rtrim ( $_POST ['wc'] ) . "\" size=\"5\" maxlength=\"5\">";
print "\n                         <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;forPlant=" . urlencode ( $rowDC ['WDPLT'] ) . "&amp;docName=Chg&amp;flddept=dept&amp;fldWC=wc&amp;fldDesc=wcDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
print "\n                         <input type=\"hidden\" id=\"wcDesc\"></td>";
print "\n <td class=\"error\">$Err_dept</td>";
print "\n </tr> ";

print "\n <tr><td>&nbsp;</td>";
print "\n     <td><ul class=\"toolbarLTS\">";
print "\n           <li class=\"optionNTS\"><a href=\"javascript:check(document.Chg)\">&nbsp;<br>Select</a></li>";
print "\n     </ul></td>";
print "\n </tr>";

print "\n </table>";
print "\n </form>";

// ///////////////////
// Clock On/Off

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " SILOPW ";
$selectSQL .= " WOXHND='$profileHandle' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By WOMORD";
require 'stmtSQLEnd.php';
$pageSelectList = "Y";
require 'stmtSQLTotalRows.php';

print "\n <form class=\"formClass\" method=\"post\" name=\"Sel\" action=\"{$baseURL}&amp;tag=Edit_Data\">";

print "\n <table $contentTable>";
$textOvr = SetTextOvr ( $Err_transDate );
print "\n <tr><td class=\"dsphdr\"><span $textOvr>Date</span></td>";
if ($sec_01 == 'Y' && $TAALRT == 'Y') {
	print "\n <td class=\"input\"><input type=\"text\" name=\"transDate\" id=\"transDate\" onfocus=\"kbActive(this, 'dtpDiv');\" value=\"{$rowDC['WDDATE']}\" size=\"10\" maxlength=\"6\"> $reqFieldChar </td>";
} else {
	$F_DATE = Format_Date ( DateToCYMD ( $rowDC ['WDDATE'] ), "D" );
	print "\n <td class=\"inputdate\"><input type=\"hidden\" name=\"transDate\" value=\"{$rowDC['WDDATE']}\">$F_DATE</td>";
}
print "\n <td>&nbsp;</td>";
$textOvr = SetTextOvr ( $Err_transTime );
print "\n <td class=\"dsphdr\"><span $textOvr>Time</span></td>";
if ($sec_01 == 'Y' && $TAALRT == 'Y') {
	$transTimeMax = '6';
	if ($TADSSF != 'Y') {
		$transTimeMax = '4';
	}
	print "\n <td class=\"input\"><input type=\"text\" name=\"transTime\" id=\"transTime\" onfocus=\"kbActive(this, 'numericKeyboard');\" value=\"{$rowDC['WDTIME']}\" size=\"10\" maxlength=\"{$transTimeMax}\"> $reqFieldChar </td>";
} else {
	$F_TIME = ($TADSSF == 'Y') ? EditHrsMinSec ( $rowDC ['WDTIME'] ) : EditHrsMinNoSec ( $rowDC ['WDTIME'] );
	print "\n <td class=\"inputnmbr\"><input type=\"hidden\" name=\"transTime\" value=\"{$rowDC['WDTIME']}\">$F_TIME</td>";
}
print "\n </tr> ";
DspErrMsg ( $Err_transDate );
DspErrMsg ( $Err_transTime );
// DspErrMsg ( $Err_TRAN);
print "\n </table>";

// BEGIN require 'QuickSearchOption.php';
$pageSelectList = "N";
$allowSaveFilter = "N";
$advanceSearch = "N";

print "<table $contentTable> \n <tr>";
print "<td class=\"page\" nowrap>";
if ($sql_Record_Count > $maxRows) { // Assign Paging Values
	$totalPages = ($sql_Record_Count / $maxRows);
	$totalPages = ceil ( $totalPages );
} else {
	$totalPages = 1;
}

$page = round ( (($startRow - 1) / ($maxRows) + 1) );
$rowIndexNext = $startRow + $maxRows;
$rowIndexPrev = $startRow - $maxRows;
print "\n Page: $page of $totalPages";

// Icon section
if (($nextPrevPos != 2) && ($nextPrevVar != "")) {
	if ($startRow > $maxRows) {
		print "\n <a href=\"javascript:prevPage(document.Sel,$rowIndexPrev)\">{$previousImage}</a>";
	} elseif ($sql_Record_Count > $maxRows) {
		print "\n {$nextPrevBlank}";
	}
	if ($sql_Record_Count >= $rowIndexNext) {
		print "\n <a href=\"javascript:nextPage(document.Sel,$rowIndexNext)\">{$nextImage}</a>";
	} elseif ($sql_Record_Count > $maxRows) {
		print "\n {$nextPrevBlank}";
	}
}
print "\n </td>";
print "\n </tr>";
print "\n </table>";
// END QuickSearchOption.php

// Operations
print "\n <table $contentTable>";

print "\n <tr><th class=\"colhdr\">Sel</th>";
print "\n     <th class=\"colhdr\">Item Number</th>";
print "\n     <th class=\"colhdr\">Sh</th>";
print "\n     <th class=\"colhdr\">Order</th>";
print "\n     <th class=\"colhdr\">Seq</th>";
print "\n     <th class=\"colhdr\">Start Date</th>";
print "\n     <th class=\"colhdr\">Due Date</th>";
print "\n     <th class=\"colhdr\">Quantity</th>";
print "\n     <th class=\"colhdr\">Hours</th>";
print "\n     <th class=\"colhdr\">Labor<br>Type</th>";
print "\n </tr>";

$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );

$rowCount = 0;
while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
	if ($rowCount >= $dspMaxRows) {
		break;
	}
	$rowCount ++;
	$startRow ++;
	
	require 'SetRowClass.php';
	$f_WOCSDT = Format_Date ( $row ['WOCSDT'], 'D' );
	$f_WOCDDT = Format_Date ( $row ['WOCDDT'], 'D' );
	$f_WOQTYR = Format_Nbr ( $row ['WOQTYR'], $qtyNbrDec, $qtyEditCode, $qtyRoundNbr, $qtyBeforeChar, $qtyAfterChar );
	$f_WOHRSR = Format_Nbr ( $row ['WOHRSR'], $hrsNbrDec, $hrsEditCode, $hrsRoundNbr, $hrsBeforeChar, $hrsAfterChar );
	$row ['WOLBTY'] = trim ( $row ['WOLBTY'] );
	
	$recNbr = $rowCount;
	$recNbr = str_pad ( $recNbr, 4, '0', STR_PAD_LEFT );
	
	// First row
	print "\n <tr class=\"$rowClass\">";
	$orderChecked = Field_Checked ( $row ['WOOSEL'], 'Y' );
	$mfgOrder = trim ( $row ['WOMORD'] );
	$seqNumber = trim ( $row ['WOSEQN'] );
	print "\n <td align=\"center\">";
	print "\n <input id=\"selRec$rowCount\" name=\"selRec$rowCount\" class=\"bigcheck\" type=\"checkbox\"  value=\"Y\" $orderChecked onClick=\"selUpdate('$mfgOrder','$seqNumber','$rowCount')\">";
	print "\n </td>";
	print "\n <td class=\"colalph\" onclick=\"showSel('selData{$recNbr}');\">{$row['WOITEM']}</td>";
	print "\n <td class=\"colcode\" onclick=\"showSel('selData{$recNbr}');\">{$row['WOCPNS']}</td>";
	print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"OR$recNbr\" value=\"{$row['WOMORD']}\"><a href=\"javascript:void+0\" onclick=\"showSel('selData{$recNbr}');\">{$row['WOMORD']} &nbsp; </a></td>";
	print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"OS$recNbr\" value=\"{$row['WOSEQN']}\"><a href=\"javascript:void+0\" onclick=\"showSel('selData{$recNbr}');\">{$row['WOSEQN']}</a></td>";
	print "\n <td class=\"colnmbr\" onclick=\"showSel('selData{$recNbr}');\">{$f_WOCSDT}</td>";
	print "\n <td class=\"colnmbr\" onclick=\"showSel('selData{$recNbr}');\">{$f_WOCDDT}</td>";
	print "\n <td class=\"colnmbr\" onclick=\"showSel('selData{$recNbr}');\">{$f_WOQTYR}</td>";
	print "\n <td class=\"colnmbr\">{$f_WOHRSR}</td>";
	print "\n <td class=\"input\"><input class=\"input\" id=\"LT$recNbr\" onfocus=\"kbActive(this, 'keyboard');\" name=\"LT$recNbr\" type=\"text\" value=\"{$row['WOLBTY']}\" size=\"1\" maxlength=\"1\">";
	print "\n   <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Sel&amp;flagType=LABORTYPE&amp;flagSrchHdr=" . urlencode ( 'Labor Type' ) . "&amp;fldName=LT$recNbr&amp;fldDesc=LT{$recNbr}Desc\" onclick=\"$searchWinVar\"> $searchDesc</a>";
	print "\n </td>";
	print "</tr>";
	
	if ($row ['WOMSGN'] != '') {
		$errorMessage = Rtv_Error_Desc ( $row ['WOMSGN'] );
		if ($errorMessage != '') {
			print "\n <tr class=\"$rowClass\">";
			print "\n    <td>&nbsp;</td>";
			print "\n    <td class=\"error\" colspan=\"5\">$errorMessage</td>";
			print "\n </tr>";
		}
	}
	
	// Hidden Division
	print "\n <tr><td>";
	print "\n   <div id=\"selData{$recNbr}\" class=\"moreInfoPos\">";
	print "\n    <table $quickSearchTable>";
	print "\n      <tr>";
	print "\n       <td>&nbsp;</td>";
	print "\n       <td>&nbsp;</td>";
	print "\n       <td rowspan=\"20\">";
	print "\n         <ul class=\"toolbarTS\">";
	print "\n           <li class=\"optionTS2\"><a href=\"javascript:void+0\" onClick=\"hideSel('selData{$recNbr}')\">&nbsp;<br>Exit</a></li>";
	if ($hsi214_OPT ['sec_04'] == 'Y') {
		$cannotIssueComp = RetValue ( "MHPLT={$row['WOPLT']} and MHORD='{$row['WOMORD']}' and HCISSC='N' ", "HDOHLD Inner Join HDHLCD On MHHLCD=HCHLCD and 'M'=HCTYPE", "Count(*)" );
		if (! $cannotIssueComp) {
			print "\n           <li class=\"optionTS2\"><a onClick=\"saveCurrentURL();\"  href=\"{$homeURL}{$cGIPath}ComponentMaterialRequisition.d2w/REPORT{$d2wVarBase}&amp;plantNumber={$row['WOPLT']}&amp;mfgOrder={$row['WOMORD']}&amp;parentItem={$row['WOITEM']}\">Component<br>Material<br>Requisition</a></li>";
		}
	}
	if ($hsi214_OPT ['sec_05'] == 'Y') {
		$cannotOrderReceipt = RetValue ( "MHPLT={$row['WOPLT']} and MHORD='{$row['WOMORD']}' and HCORDR='N' ", "HDOHLD Inner Join HDHLCD On MHHLCD=HCHLCD and 'M'=HCTYPE", "Count(*)" );
		if (! $cannotOrderReceipt) {
			$orderRecTurnaround = RetValue ( "OHPLT={$row['WOPLT']} and OHORD='{$row['WOMORD']}' ", "HDMOHM", "OHORTN" );
			print "\n           <li class=\"optionTS2\"><a onClick=\"saveCurrentURL();\" href=\"{$homeURL}{$cGIPath}MfgOrderReceipt.d2w/REPORT{$d2wVarBase}&amp;turnaroundNumber={$orderRecTurnaround}\">Finished<br>Goods<br>Receipt</a></li>";
		}
	}
	if ($hsi214_OPT ['sec_06'] == 'Y') {
		$finalTagTurnaround = RetValue ( "OHPLT={$row['WOPLT']} and OHORD='{$row['WOMORD']}' ", "HDMOHM", "OHFTTN" );
		print "\n           <li class=\"optionTS2\"><a onClick=\"saveCurrentURL();\" href=\"{$homeURL}{$cGIPath}MfgOrderFinalTag.d2w/REPORT{$d2wVarBase}&amp;turnaroundNumber={$finalTagTurnaround}\">&nbsp;<br>Final Tag</a></li>";
	}
	print "\n         </ul>";
	print "\n       </td>";
	print "\n       <td rowspan=\"20\">";
	print "\n         <ul class=\"toolbarTS\">";
	if ($hsi214_OPT ['sec_10'] == 'Y') {
		print "\n           <li class=\"optionTS2\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$d2wVarBase}&amp;plantNumber={$row['WOPLT']}&amp;mfgOrder={$row['WOMORD']}\" onclick=\"{$inquiryWinVar}\">&nbsp;<br>Order Inquiry</a></li>";
	}
	if ($hsi214_OPT ['sec_11'] == 'Y') {
		print "\n           <li class=\"optionTS2\"><a href=\"{$homeURL}{$cGIPath}ProductStructureIndentedStockStatusInquiry.d2w/DISPLAY{$d2wVarBase}&amp;plantNumber={$row['WOPLT']}&amp;itemNumber={$row['WOITEM']}&amp;quantityRequired={$row['WOQTYR']}\" onclick=\"{$inquiryWinVar}\">&nbsp;<br>Stock Status</a></li>";
	}
	if ($row ['WONTCD'] == 'Y') {
		print "\n           <li class=\"optionTS2\"><a href=\"{$homeURL}{$cGIPath}LaborInProcessNotesInquiry.d2w/REPORT{$d2wVarBase}&amp;plantNumber={$row['WOPLT']}&amp;mfgOrder={$row['WOMORD']}&amp;sequenceNumber={$row['WOSEQN']}&amp;documentType=RTG\" onclick=\"{$commentWinVar}\">&nbsp;<br>Routing Notes</a></li>";
	}
	if ($hsi214_OPT ['sec_13'] == 'Y') {
		$attachItemPlantKey = str_pad ( $row ['WOPLT'], 3, '0', STR_PAD_LEFT ) . $row ['WOITEM'];
		$attachments = RetValue ( "ATFOLD<>' ' and (ATFOLD='ITEM' and ATVKEY='{$row['WOITEM']}' or ATFOLD='ITEMPLANT' and ATVKEY='{$attachItemPlantKey}') and (ATUSER='{$userProfile}' or ATPRIV=' ' or '{$admin}' ='Y')", "SYD2WA", "char(coalesce(count(*),0))" );
		if ($attachments > "0") {
			print "\n           <li class=\"optionTS2\"><a href=\"{$homeURL}{$phpPath}ItemPlantAttachment.php{$scriptVarBase}&amp;plantNumber={$row['WOPLT']}&amp;itemNumber={$row['WOITEM']}\" onclick=\"{$inquiryWinVar}\">&nbsp;<br>Attachments</a></li>";
		}
	}
	print "\n         </ul>";
	print "\n       </td>";
	print "\n      </tr>";
	Format_Header ( 'Item Number', trim ( $row ['WOITEM'] ), '' );
	$itemDescription = RetValue ( "IMITEM='{$row['WOITEM']}' ", "HDIMST", "IMIMDS" );
	Format_Header ( 'Description', trim ( $itemDescription ), '' );
	Format_Header ( 'Shortage', $row ['WOCPNS'], '' );
	Format_Header ( 'Order Number', trim ( $row ['WOMORD'] ), '' );
	Format_Header ( 'Sequence', $row ['WOSEQN'], '' );
	Format_Header ( 'Start Date', Format_Date ( $row ['WOCSDT'], 'H' ), '' );
	Format_Header ( 'Due Date', Format_Date ( $row ['WOCDDT'], 'H' ), '' );
	Format_Header ( 'Quantity', Format_Nbr ( $row ['WOQTYR'], $qtyNbrDec, $qtyEditCode, '', '', '' ), '' );
	Format_Header ( 'Hours', Format_Nbr ( $row ['WOHRSR'], $hrsNbrDec, $hrsEditCode, '', '', '' ), '' );
	if ($row ['WOORD#']) {
		Format_Header ( 'Sales Order', $row ['WOORD#'], '' );
		$customerNumber = RetValue ( "OEORD#={$row['WOORD#']} ", "OEORHD ", "OEBLTO" );
		$customerName = RetValue ( "CMCUST#={$customerNumber} ", "HDCUST ", "CMCNA1" );
		Format_Header ( 'Customer', trim ( $customerName ), $customerNumber );
	}
	if ($row ['WOMGTP'] != '.0') {
		Format_Header ( 'Priority', $row ['WOMGTP'], '' );
	}
	// if (trim ( $row ['WONXWC'] ) != '') {
	// Format_Header ( 'Next W/C', trim ( $row ['WONXWC'] ), '' );
	// }
	// if (trim ( $row ['WOLSWC'] ) != '') {
	// Format_Header ( 'Last W/C', trim ( $row ['WOLSWC'] ), '' );
	// }
	// if ($row ['WOEMGR']) {
	// Format_Header ( 'Employee/Group', $row ['WOEMGR'], '' );
	// }
	print "\n    </table>";
	print "\n   </div>";
	print "\n </td></tr>";
	
	print "\n <tr><td><input type=\"hidden\" name=\"LT{$recNbr}Desc\" ></td></tr>";
}

print "\n <tr><td><input type=\"hidden\" name=\"displayedRows\" value=\"$rowCount\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"rowIndexCurr\" value=\"$rowIndexCurr\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"rowIndexNext\" value=\"\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"rowIndexPrev\" value=\"\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"reportOffOn\" value=\"\"></td></tr>";

print "\n </table>";

print "\n </form>";
require_once 'KeyboardTS.htm';
print "\n <script TYPE=\"text/javascript\">document.Chg.$focusField.focus();</script>";

print $hrTagAttr;
require_once 'Copyright.php';

print "\n       </td>";

// Tool Bar
print "\n       <td>";
print "\n         <ul class=\"toolbarTS\">";
print "\n           <li class=\"optionTS\"><a href=\"{$baseURL}&amp;tag=NOUPDATE\">&nbsp;<br>Exit</a></li>";
print "\n           <li class=\"optionTS\"><a href=\"javascript:document.Sel.reportOffOn.value='on'; checkSel(document.Sel)\">&nbsp;<br>Clock On</a></li>";
print "\n           <li class=\"optionTS\"><a href=\"javascript:document.Sel.reportOffOn.value='off'; checkSel(document.Sel)\">&nbsp;<br>Clock Off</a></li>";
print "\n           <li class=\"optionTS\"><a href=\"{$baseURL}\">&nbsp;<br>Refresh</a></li>";
print "\n         </ul>";
print "\n       </td>";

print "\n     </tr> ";
print "\n   </table>";
// require_once 'Trailer.php';
print "\n </body> \n </html>";

// Maintenance Edit (passing Hex Handle)
function Validate_Data($pgmName, $profileHandle, $dataBaseID, $errFound, $edtVar, $errVar) {
	global $pgmLibrary, $i5Connect;
	if (is_null ( $errFound ))
		$errFound = "";
	if (is_null ( $edtVar ))
		$edtVar = "";
	if (is_null ( $errVar ))
		$errVar = "";
	
	$pgmCall = array (array ("Name" => "profileHandle", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "64" ), array ("Name" => "dataBaseID", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "2" ), array ("Name" => "errFound", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1" ), array ("Name" => "edtVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000" ), array ("Name" => "errVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000" ) );
	
	$pgm = i5_program_prepare ( "$pgmName", $pgmCall );
	if (! $pgm) {
		die ( "<br>Validate_Data ($pgmName) prepare error. Error Number=" . i5_errno () . " msg=" . i5_errormsg () );
	}
	
	$parmIn = array ("profileHandle" => $profileHandle, "dataBaseID" => $dataBaseID, "errFound" => $errFound, "edtVar" => $edtVar, "errVar" => $errVar );
	
	$parmOut = array ("profileHandle" => "profileHandle", "dataBaseID" => "dataBaseID", "errFound" => "errFound", "edtVar" => "edtVar", "errVar" => "errVar" );
	
	$ret = i5_program_call ( $pgm, $parmIn, $parmOut );
	if (function_exists ( 'i5_output' ))
		extract ( i5_output () );
	if (! $ret) {
		die ( "<br>Validate_Data ($pgmName) call errno=" . i5_errno () . " msg=" . i5_errormsg () );
	}
	
	$returnValue ['profileHandle'] = $profileHandle;
	$returnValue ['dataBaseID'] = $dataBaseID;
	$returnValue ['errFound'] = $errFound;
	$returnValue ['edtVar'] = $edtVar;
	$returnValue ['errVar'] = $errVar;
	return $returnValue;
}

function getLastLaborType($i5Connect, $rowDC) {
	$lastLaborType = 'D';
	
	$stmtSQL = " Select DFLBTY as LBTY, EHCALD as CALD, EHSTRT as STRT ";
	$stmtSQL .= " From HDMECH inner join ETDCDF on EHTRAN=DFCODE ";
	$stmtSQL .= " Where DFLBTY<>' ' ";
	if ($rowDC ['WDGRP'] == "0") {
		$stmtSQL .= " and EHCO={$rowDC['WDCOMP']} and EHFAC={$rowDC['WDFACL']} and EHEMP={$rowDC['WDEMPL']}";
	} else {
		$stmtSQL .= " and EHGRP={$rowDC['WDGRP']}";
	}
	$stmtSQL .= " Union ";
	$stmtSQL .= " Select LBLBTY as LBTY, LBCALD as CALD, LBSTRT as STRT ";
	$stmtSQL .= " From SIMLBP ";
	$stmtSQL .= " Where LBLBTY<>' ' and LBDTCL<>' ' ";
	if ($rowDC ['WDGRP'] == "0") {
		$stmtSQL .= " and LBCO={$rowDC['WDCOMP']} and LBFAC={$rowDC['WDFACL']} and LBEMP={$rowDC['WDEMPL']}";
	} else {
		$stmtSQL .= " and LBGRP={$rowDC['WDGRP']}";
	}
	$stmtSQL .= " Union ";
	$stmtSQL .= " Select LZLBTY as LBTY, LZCALD as CALD, LZSTRT as STRT ";
	$stmtSQL .= " From SIMLBZ ";
	$stmtSQL .= " Where LZLBTY<>' ' and LZDTCL<>' ' ";
	if ($rowDC ['WDGRP'] == "0") {
		$stmtSQL .= " and LZCO={$rowDC['WDCOMP']} and LZFAC={$rowDC['WDFACL']} and LZEMP={$rowDC['WDEMPL']}";
	} else {
		$stmtSQL .= " and LZGRP={$rowDC['WDGRP']}";
	}
	$stmtSQL .= " Order by 2 desc, 3 desc ";
	$stmtSQL .= " Fetch First 1 Row Only With NC";
	
	$stmtResourceLBTY = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	if ($stmtResourceLBTY) {
		$rowLBTY = db2_fetch_assoc ( $stmtResourceLBTY );
		if ($rowLBTY) {
			$lastLaborType = $rowLBTY ['LBTY'];
		}
	}
	return $lastLaborType;
}

?>	
