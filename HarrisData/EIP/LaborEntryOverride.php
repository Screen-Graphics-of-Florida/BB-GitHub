<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

// $errFound = (isset ( $_GET ['errFound'] )) ? $GET ['errFound'] : "";
$touchScreen = (isset ( $_GET ['touchScreen'] )) ? $_GET ['touchScreen'] : "";

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once "ETControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Override Labor Entry";
$scriptName = "LaborEntryOverride.php";
$scriptVarBase = "{$genericVarBase}&amp;touchScreen=Y";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL = "{$scriptName}{$scriptVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$d2wVarBase = "{$altVarBase}&amp;touchScreen=Y";
$programName = "HETLBE";
// $maxRows = "4";
// $dspMaxRows = "4";
$rowIndexCurr = $startRow;
$formatToPrint = "";
$groupNbr = "0";
$plantNumber = "";
$focusField = "";

$returnURL = "ShopFloorDispatch.d2w/BUILD{$d2wVarBase}";

if ($tag == "NOUPDATE") {
	$confMessage = "Confirm No Update";
	print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$returnURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
	exit ();
}

// Program Option Security
$hsi214_OPT = pgmOptSecurity ( $profileHandle, $dataBaseID, 'HSI214' );

if ($tag == "Edit_Data") {
	$edtVar = "";
	
	Concat_Field ( "@@subr", "SrUpdateQO" );
	// if ($_POST ['reportOffOn'] == 'Y') {
	// Concat_Field ( "@@subr", "SrReportQ" );
	// } elseif ($_POST ['clockOff'] == 'Y') {
	// Concat_Field ( "@@subr", "SrClockOff" );
	// }
	Concat_Field ( "@@rows", $_POST ['displayedRows'] );
	Concat_Field ( "@@cmnt", $_POST ['comment60'] );
	Concat_Field ( "@@hfmt", $_POST ['hrsFmt'] );
	Concat_Field ( "@@ofon", $_POST ['reportOffOn'] );
	Concat_Field ( "@@off@", $_POST ['clockOff'] );
	for($i = 1; $i <= $_POST ['displayedRows']; $i ++) {
		$x = str_pad ( $i, 4, '0', STR_PAD_LEFT );
		$OR = "OR$x";
		$OS = "OS$x";
		$IT = "IT$x";
		$HW = "HW$x";
		$LT = "LT$x";
		$QC = "QC$x";
		$QS = "QS$x";
		$SR = "SR$x";
		$RQ = "RQ$x";
		$RR = "RR$x";
		$LC = "LC$x";
		if ($hsi214_OPT ['sec_01'] == 'Y') {
			$OC = "OC$x";
		}
		Concat_Field ( "$OR", $_POST [$OR] );
		Concat_Field ( "$OS", $_POST [$OS] );
		Concat_Field ( "$IT", $_POST [$IT] );
		Concat_Field ( "$HW", $_POST [$HW] );
		Concat_Field ( "$LT", $_POST [$LT] );
		$_POST [$QC] = str_replace(',', '', $_POST [$QC]);
		Concat_Field ( "$QC", $_POST [$QC] );
		$_POST [$QS] = str_replace(',', '', $_POST [$QS]);
		Concat_Field ( "$QS", $_POST [$QS] );
		Concat_Field ( "$SR", $_POST [$SR] );
		$_POST [$RQ] = str_replace(',', '', $_POST [$RQ]);
		Concat_Field ( "$RQ", $_POST [$RQ] );
		Concat_Field ( "$RR", $_POST [$RR] );
		Concat_Field ( "$LC", $_POST [$LC] );
		if ($hsi214_OPT ['sec_01'] == 'Y') {
			Concat_Field ( "$OC", $_POST [$OC] );
		}
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
		if ($_POST ['reportOffOn'] == 'Y') {
			$confMessage = "Confirm Override Labor Reported";
		} else {
			$confMessage = "Confirm Override Labor Clocked Off";
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$returnURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	} elseif ($errFound == "C") {
		$confMessage = DecatErr_Field ( "@@cmsg", "" );
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$returnURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
		exit ();
	} elseif ($errFound == "R") {
		$startRow = $_POST ['rowIndexCurr'];
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL=$baseURL&amp;startRow=$startRow&amp;timeStamp=" . urlencode ( trim ( $_SERVER ['REQUEST_TIME'] ) ) . "\">";
		exit ();
	} elseif ($errFound == "X") {
	    print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}EmployeeSignOn.d2w/REPORT{$d2wVarBase}\"> ";
	    exit ();
		// } else {
		// EdtVarErr ( $profileHandle, $edtVar );
		// ErrVarErr ( $profileHandle, $errVar );
		// print "\n <meta http-equiv=\"refresh\" content=\"0; URL=$baseURL&amp;startRow={$_POST['rowIndexCurr']}&amp;errFound=" . urlencode ( trim ( $errFound ) ) . "&amp;timeStamp=" . urlencode ( trim ( $_SERVER ['REQUEST_TIME'] ) ) . "\"> ";
		// exit ();
	}
}

if ($tag == "BUILD") {
	$edtVar = "";
	Concat_Field ( "@@subr", "SrFillWkO" );
	$edtVar .= "}{";
	
	$returnValue = Validate_Data ( "HETLBE_W", $profileHandle, $dataBaseID, $errFound, $edtVar, $errVar );
    if ($errFound == "X") {
        print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}EmployeeSignOn.d2w/REPORT{$d2wVarBase}\"> ";
    } else {
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$baseURL}\">";
    }
	exit ();
}

$stmtSQL = " Select * From SIDCTW Where WDXHND='$profileHandle'";
$stmtSQL .= " Fetch First 1 Row Only With NC";
$sqlResultDC = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$rowDC = db2_fetch_assoc ( $sqlResultDC );

$plantNumber = $rowDC ['WDPLT'];
$plantHrsFmt = RetValue ( "PLPLNT={$plantNumber}", "HDPLNT", "PLHWKF" );
$hrsFmt = (trim ( $plantHrsFmt ) == '') ? $TAHWKF : $plantHrsFmt;
if ($hrsFmt == 'T' && $TADSSF == "Y") {
	$hoursFormat = "(HHHMMSS)";
	$hoursMax = "8";
} elseif ($hrsFmt == 'T') {
	$hoursFormat = "(HHHMM)";
	$hoursMax = "6";
} else {
	$hoursFormat = "(Decimal)";
	$hoursMax = "10";
}
$dspRework = RetValue ( "PLPLNT={$plantNumber}", "HDPLNT", "PLDPRW" );

require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckEnterChg.php';
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
	var rows = (chgForm.displayedRows.value ? chgForm.displayedRows.value : 0);
    var ok = true;
    for (var rx = 1; rx <= rows; rx++) {
     	var suffix = rx.toString();
        while (suffix.length < 4) {
          	suffix = "0" + suffix;
        }
        var elemName = "LT" + suffix;
        if (chgForm[elemName].value == "") {
            <?php print "\n alert(\"$reqFieldError\");"; ?>
            ok = false;
            break;
        }
        
        elemName = "HW" + suffix;
		<?php if ($hrsFmt=='T' && $TADSSF=="Y") { ?>
           	ok = (editNum(chgForm[elemName], 7, 0));
		<?php } elseif ($hrsFmt=='T') { ?>
           	ok = (editNum(chgForm[elemName], 5, 0));
		<?php } else { ?>
           	ok = (editNum(chgForm[elemName], 3, 6));
		<?php } ?>
        if (!ok) {break;}
                      
        elemName = "QC" + suffix;
        chgForm[elemName].value = numberWithoutCommas(chgForm[elemName].value);
        ok = (editNum(chgForm[elemName], 9, 4));
        chgForm[elemName].value = numberWithCommas(chgForm[elemName].value);
        if (!ok) {break;}
                      
        elemName = "QS" + suffix;
        chgForm[elemName].value = numberWithoutCommas(chgForm[elemName].value);
        ok = (editNum(chgForm[elemName], 9, 4));
        chgForm[elemName].value = numberWithCommas(chgForm[elemName].value);
        if (!ok) {break;}
                      
		<?php if ($dspRework=="Y") { ?>
          	elemName = "RQ" + suffix;
            chgForm[elemName].value = numberWithoutCommas(chgForm[elemName].value);
            ok = (editNum(chgForm[elemName], 9, 4));
            chgForm[elemName].value = numberWithCommas(chgForm[elemName].value);
            if (!ok) {break;}
        <?php } ?>
    }
    return ok;
}

<!-- function nextPage(chgForm,startRow) { -->
<!-- 	if (validate(chgForm)) { -->
<!-- 	   	chgForm.rowIndexNext.value=startRow; -->
<!--     	chgForm.submit(); -->
<!--     } -->
<!-- } -->
<!-- function prevPage(chgForm,startRow) { -->
<!-- 	if (validate(chgForm)) { -->
<!-- 	  	chgForm.rowIndexPrev.value=startRow; -->
<!--     	chgForm.submit(); -->
<!--     } -->
<!-- } -->

<?php
print "\n </script>";
require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

// %{ Labor Entry Header %}
// BEGIN require_once 'MaintainTop.php';
print "\n <table $contentTable>";
print "\n <tr><td><h1>$page_title</h1></td></tr>";
print "\n </table>";
// END MaintainTop.php

print "\n <table $contentTable>";
Format_Header ( "Plant", $rowDC ['WDPNAM'], $rowDC ['WDPLT'] );
$f_DeptWc = "{$rowDC['WDDEPT']} / {$rowDC['WDWC']}";
Format_Header ( "Department/Work Center:", $rowDC ['WDWDSC'], $f_DeptWc );
if ($rowDC ['WDGRP'] == "0") {
	$groupNbr = "0";
	Format_Header ( "Employee", $rowDC ['WDENAM'], "" );
} else {
	$groupNbr = $rowDC ['WDGRP'];
	Format_Header ( "Group", $rowDC ['WDGDSC'], "" );
}
print "\n </table>";

// %INCLUDE "MoreInfo.icl"

print $hrTagAttr;
require_once 'ErrorDisplay.php';
if ($errFound != "") {
	$Err_WHRS = DecatErr_Field ( "@@whrs", "" );
	print "\n <span class=\"error\" $fldTextErrOvr>$Err_WHRS</span>";
}

// %{ Quantity Query %}
require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " SILOPW ";
$selectSQL .= " WOXHND='$profileHandle' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By WOMORD";
require 'stmtSQLEnd.php';
$pageSelectList = "Y";
require 'stmtSQLTotalRows.php';

print "\n \n <form class=\"formClass\" METHOD=\"post\" NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";

// BEGIN require 'QuickSearchOption.php';
// $pageSelectList = "N";
// $allowSaveFilter = "N";
// $advanceSearch = "N";

// print "<table $contentTable> \n <tr>";
// print "<td class=\"page\" nowrap>";
// if ($sql_Record_Count > $maxRows) { // Assign Paging Values
// $totalPages = ($sql_Record_Count / $maxRows);
// $totalPages = ceil ( $totalPages );
// } else {
// $totalPages = 1;
// }

// $page = round ( (($startRow - 1) / ($maxRows) + 1) );
// $rowIndexNext = $startRow + $maxRows;
// $rowIndexPrev = $startRow - $maxRows;
// print "\n Page: $page of $totalPages";

// // Icon section
// if (($nextPrevPos != 2) && ($nextPrevVar != "")) {
// if ($startRow > $maxRows) {
// print "\n <a href=\"javascript:prevPage(document.Chg,$rowIndexPrev)\">{$previousImage}</a>";
// } elseif ($sql_Record_Count > $maxRows) {
// print "\n {$nextPrevBlank}";
// }
// if ($sql_Record_Count >= $rowIndexNext) {
// print "\n <a href=\"javascript:nextPage(document.Chg,$rowIndexNext)\">{$nextImage}</a>";
// } elseif ($sql_Record_Count > $maxRows) {
// print "\n {$nextPrevBlank}";
// }
// }
// print "\n </td>";
// print "\n </tr>";
// print "\n </table>";
// END QuickSearchOption.php

print "\n <table $contentTable>";

print "\n <tr><td>";
$moreInfo = "";
print "\n <div id=\"selCmmt\" class=\"moreInfoPos\">";
print "\n   <table $quickSearchTable>";
print "\n     <tr>";
print "\n       <td>&nbsp;</td>";
print "\n       <td>&nbsp;</td>";
print "\n       <td>&nbsp;</td>";
print "\n       <td rowspan=\"20\">";
print "\n         <ul class=\"toolbarTS\">";
print "\n           <li class=\"optionTS2\"><a href=\"javascript:kbInactive()\" onClick=\"hideSel('selCmmt')\">&nbsp;<br>Back</a></li>";
print "\n           <li class=\"optionTS2\"><a href=\"javascript:kbInactive()\" onClick=\"hideSel('selCmmt')\">&nbsp;<br>Accept</a></li>";
print "\n         </ul>";
print "\n       </td>";
print "\n     </tr>";
print "\n     <tr><td class=\"dsphdr\">Comment</td>";
print "\n         <td class=\"input\"><input class=\"input\" id=\"comment60\" onfocus=\"kbActive(this, 'keyboard');\" name=\"comment60\" type=\"text\" value=\"$V_comment60\" size=\"40\" maxlength=\"40\"></td>";
print "\n     </tr>";
print "\n   </table>";
print "\n </div>";
print "\n </td></tr>";

print "\n <tr><th class=\"colhdr\">Sel</th>";
print "\n     <th class=\"colhdr\">Order</th>";
print "\n     <th class=\"colhdr\">Seq</th>";
print "\n     <th class=\"colhdr\">Item Number</th>";
print "\n     <th class=\"colhdr\">Start Time</th>";
print "\n     <th class=\"colhdr\">Stop Time</th>";
print "\n     <th class=\"colhdr\">Hours $hoursFormat</th>";
print "\n     <th class=\"colhdr\">Labor<br>Type</th>";
print "\n     <th class=\"colhdr\">Quantity<br>Complete</th>";
print "\n     <th class=\"colhdr\">Quantity<br>Scrapped /<br>Reason Cd</th>";
if ($dspRework == "Y") {
	print "\n     <th class=\"colhdr\">Rework<br>Quantity /<br>Reason Cd</th>";
}
if ($hsi214_OPT ['sec_01'] == 'Y') {
	print "\n     <th class=\"colhdr\">Labor Cd /<br>Oper Cmpl</th>";
} else {
	print "\n     <th class=\"colhdr\">Labor Cd</th>";
}
print "\n </tr>";

$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );

$rowCount = 0;
while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
	// if ($rowCount >= $dspMaxRows) {
	// break;
	// }
	$rowCount ++;
	$startRow ++;
	
	require 'SetRowClass.php';
	
	if ($row ['WOQTYC'] == 0) {
		$row ['WOQTYC'] = "";
	}
	if ($row ['WOQTYS'] == 0) {
		$row ['WOQTYS'] = "";
	}
	if ($row ['WOREWK'] == 0) {
		$row ['WOREWK'] = "";
	}
	
	$recNbr = $rowCount;
	$recNbr = str_pad ( $recNbr, 4, '0', STR_PAD_LEFT );
	
	if ($focusField == "") {
		$focusField = "HW$recNbr";
	}
	
	// First row
	print "\n <tr class=\"$rowClass\">";
	$orderChecked = Field_Checked ( $row ['WOOSEL'], 'Y' );
	$mfgOrder = trim ( $row ['WOMORD'] );
	$seqNumber = trim ( $row ['WOSEQN'] );
	print "\n <td align=\"center\">";
	print "\n <input id=\"selRec$rowCount\" name=\"selRec$rowCount\" class=\"bigcheck\" type=\"checkbox\"  value=\"Y\" $orderChecked onClick=\"selUpdate('$mfgOrder','$seqNumber','$rowCount')\">";
	print "\n </td>";
	print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"OR$recNbr\" value=\"{$row['WOMORD']}\"><a href=\"javascript:void+0\" onclick=\"showSel('selData{$recNbr}');\">{$row['WOMORD']} &nbsp; </a>";
	if ($sfdExitLabor != "") {
		$workURL = setURL ( $sfdExitLabor );
		$workURL = str_replace ( "@@mfgOrder", urlencode ( $row ['WOMORD'] ), $workURL );
		$workURL = str_replace ( "@@seqNumber", urlencode ( $row ['WOSEQN'] ), $workURL );
		print "<a href=\"$workURL&amp;touchScreen=Y\" onclick=\"$laborEntryWinVar\"><img border=\"$imageBorder\" src=\"{$homeURL}{$imagePath}newEventSml.gif\" title=\"Click here to execute exit point\" alt=\"Execute Exit Point\"></a>";
	}
	print "\n </td>";
	print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"OS$recNbr\" value=\"{$row['WOSEQN']}\">{$row['WOSEQN']}</td>";
	print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"IT$recNbr\" value=\"{$row['WOITEM']}\">{$row['WOITEM']}</td>";
	if ($TADSSF == "Y") {
		$F_STRT = EditHrsMinSec ( $row ['WOSTRT'] );
		$F_STOP = EditHrsMinSec ( $row ['WOSTOP'] );
	} else {
		$F_STRT = EditHrsMin ( substr ( $row ['WOSTRT'], 0, (strlen ( $row ['WOSTRT'] ) - 2) ) );
		$F_STOP = EditHrsMin ( substr ( $row ['WOSTOP'], 0, (strlen ( $row ['WOSTOP'] ) - 2) ) );
	}
	print "\n <td class=\"colalph\" >$F_STRT</td>";
	print "\n <td class=\"colalph\" >$F_STOP</td>";
	if ($hrsFmt == 'T') {
		$row ['WOWHRS'] = HoursInputFromHMS ( $row ['WOWHRST'], $TADSSF );
	}
	print "\n <td class=\"input\"><input class=\"input\" id=\"HW$recNbr\" onfocus=\"kbActive(this, 'numericKeyboard');\" name=\"HW$recNbr\" type=\"text\" value=\"{$row['WOWHRS']}\" size=\"10\" maxlength=\"$hoursMax\"></td>";
	print "\n <td class=\"input\"><input class=\"input\" id=\"LT$recNbr\" onfocus=\"kbActive(this, 'keyboard');\" name=\"LT$recNbr\" type=\"text\" value=\"{$row['WOLBTY']}\" size=\"1\" maxlength=\"1\">";
	print "\n   <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=LABORTYPE&amp;flagSrchHdr=" . urlencode ( 'Labor Type' ) . "&amp;fldName=LT$recNbr&amp;fldDesc=LT{$recNbr}Desc\" onclick=\"$searchWinVar\"> $searchDesc</a>";
	print "\n </td>";
	
	// Quantity Inputs
	$creditCodeOvr = 'N';
	$formattedQC = Format_Nbr ( $row ['WOQTYC'], 4, 'K', '', '', '' );
	if (strpos ( $formattedQC, '-' ) !== false) {
		$formattedQC = '-' . str_replace ( '-', '', $formattedQC );
	}
	print "\n <td class=\"input\"><input class=\"input\" id=\"QC$recNbr\" onfocus=\"kbActive(this, 'numericKeyboard', true);\" onkeyup=\"formatInput(this.id);\" name=\"QC$recNbr\" type=\"text\" value=\"{$formattedQC}\" size=\"12\" maxlength=\"17\"></td>";

	$formattedQS = Format_Nbr ( $row ['WOQTYS'], 4, 'K', '', '', '' );
	if (strpos ( $formattedQS, '-' ) !== false) {
		$formattedQS = '-' . str_replace ( '-', '', $formattedQS );
	}
	print "\n <td class=\"input\"><input class=\"input\" id=\"QS$recNbr\" onfocus=\"kbActive(this, 'numericKeyboard', true);\" onkeyup=\"formatInput(this.id);\" name=\"QS$recNbr\" type=\"text\" value=\"{$formattedQS}\" size=\"12\" maxlength=\"17\"></td>";

	if ($dspRework == "Y") {
		$formattedRQ = Format_Nbr ( $row ['WOREWK'], 4, 'K', '', '', '' );
		if (strpos ( $formattedRQ, '-' ) !== false) {
			$formattedRQ = '-' . str_replace ( '-', '', $formattedRQ );
		}
		print "\n <td class=\"input\"><input class=\"input\" id=\"RQ$recNbr\" onfocus=\"kbActive(this, 'numericKeyboard', true);\" onkeyup=\"formatInput(this.id);\" name=\"RQ$recNbr\" type=\"text\" value=\"{$formattedRQ}\" size=\"12\" maxlength=\"17\"></td>";
	}
	print "\n <td class=\"input\"><input class=\"input\" id=\"LC$recNbr\" onfocus=\"kbActive(this, 'keyboard');\" name=\"LC$recNbr\" type=\"text\" value=\"{$row['WOLCOD']}\" size=\"1\" maxlength=\"2\">";
	print "\n   <a href=\"{$homeURL}{$phpPath}LaborCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=LC$recNbr&amp;fldDesc=LC{$recNbr}Desc&amp;touchScreen=Y\" onclick=\"$searchWinVar\"> $searchDesc</a> ";
	print "\n </td>";
	print "</tr>";
	
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
			print "\n           <li class=\"optionTS2\"><a onClick=\"saveCurrentURL();\" href=\"{$homeURL}{$cGIPath}MfgOrderReceipt.d2w/REPORT{$d2wVarBase}&amp;turnaroundNumber={$row['WOORTN']}\">Finished<br>Goods<br>Receipt</a></li>";
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
	// if ($hsi214_OPT ['sec_01'] == 'Y') {
	// print "\n <li class=\"optionTS2\"><a href=\"{$baseURL}&amp;tag=OPERCOMP&amp;plantNumber={$row['WOPLT']}&amp;mfgOrder={$row['WOMORD']}&amp;sequenceNumber={$row['WOSEQN']}\">&nbsp;<br>Operation Complete</a></li>";
	// }
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
	Format_Header ( 'Description', trim ( $row ['WOIMDS'] ), '' );
	Format_Header ( 'Shortage', $row ['WOCPNS'], '' );
	Format_Header ( 'Order Number', trim ( $row ['WOMORD'] ), '' );
	Format_Header ( 'Sequence', $row ['WOSEQN'], '' );
	Format_Header ( 'Start Date', Format_Date ( $row ['WOCSDT'], 'H' ), '' );
	Format_Header ( 'Due Date', Format_Date ( $row ['WOCDDT'], 'H' ), '' );
	Format_Header ( 'Quantity', Format_Nbr ( $row ['WOQTYR'], $qtyNbrDec, $qtyEditCode, '', '', '' ), '' );
	Format_Header ( 'Hours', Format_Nbr ( $row ['WOHRSR'], $hrsNbrDec, $hrsEditCode, '', '', '' ), '' );
	if ($row ['WOORD#']) {
		Format_Header ( 'Sales Order', $row ['WOORD#'], '' );
	}
	if ($row ['WOSLTO']) {
		$customerName = RetValue ( "CMCUST={$row['WOSLTO']} ", "HDCUST", "CMCNA1" );
		Format_Header ( 'Customer', trim ( $customerName ), $row ['WOSLTO'] );
	}
	if ($row ['WOMGTP'] != '.0') {
		Format_Header ( 'Priority', $row ['WOMGTP'], '' );
	}
	if (trim ( $row ['WONXWC'] ) != '') {
		Format_Header ( 'Next W/C', trim ( $row ['WONXWC'] ), '' );
	}
	if (trim ( $row ['WOLSWC'] ) != '') {
		Format_Header ( 'Last W/C', trim ( $row ['WOLSWC'] ), '' );
	}
	if ($row ['WOEMGR']) {
		Format_Header ( 'Employee/Group', $row ['WOEMGR'], '' );
	}
	print "\n    </table>";
	print "\n   </div>";
	print "\n </td></tr>";
	
	// Second row
	print "<tr>";
	print "\n <td> </td>";
	print "\n <td> </td>";
	print "\n <td> </td>";
	print "\n <td class=\"hdrdata\" colspan=\"6\">{$row['WOIMDS']}</td>";
	print "\n <td class=\"input\"><input class=\"input\" id=\"SR$recNbr\" onfocus=\"kbActive(this, 'keyboard');\" name=\"SR$recNbr\" type=\"text\" value=\"{$row['WOSCRC']}\" size=\"1\" maxlength=\"2\">";
	print "\n   <a href=\"{$homeURL}{$phpPath}TransactionTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=SR$recNbr&amp;fldDesc=SR{$recNbr}Desc&amp;touchScreen=Y\" onclick=\"$searchWinVar\"> $searchDesc</a>";
	print "\n </td>";
	if ($dspRework == "Y") {
		print "\n <td class=\"input\"><input class=\"input\" id=\"RR$recNbr\" onfocus=\"kbActive(this, 'keyboard');\" name=\"RR$recNbr\" type=\"text\" value=\"{$row['WORWRC']}\" size=\"1\" maxlength=\"2\">";
		print "\n   <a href=\"{$homeURL}{$phpPath}TransactionTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=RR$recNbr&amp;fldDesc=RR{$recNbr}Desc&amp;touchScreen=Y\" onclick=\"$searchWinVar\"> $searchDesc</a>";
		print "\n </td>";
	}
	if ($hsi214_OPT ['sec_01'] == 'Y') {
		$checked = Field_Checked ( $row ['WOOPRC'], 'Y' );
		print "\n <td class=\"colcode\"><input class=\"bigcheck\" type=\"checkbox\" id=\"{OC$recNbr}\" name=\"OC$recNbr\" value=\"Y\" $checked></td>";
	}
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
	
	print "\n <tr><td><input type=\"hidden\" name=\"LT{$recNbr}Desc\" ></td></tr>";
	print "\n <tr><td><input type=\"hidden\" name=\"SR{$recNbr}Desc\" ></td></tr>";
	print "\n <tr><td><input type=\"hidden\" name=\"RR{$recNbr}Desc\" ></td></tr>";
	print "\n <tr><td><input type=\"hidden\" name=\"LC{$recNbr}Desc\" ></td></tr>";
}

print "\n <tr><td><input type=\"hidden\" name=\"displayedRows\" value=\"$rowCount\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"rowIndexCurr\" value=\"$rowIndexCurr\"></td></tr>";
// print "\n <tr><td><input type=\"hidden\" name=\"rowIndexNext\" value=\"\"></td></tr>";
// print "\n <tr><td><input type=\"hidden\" name=\"rowIndexPrev\" value=\"\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"hrsFmt\" value=\"$hrsFmt\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"reportOffOn\" value=\"\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"clockOff\" value=\"\"></td></tr>";

if ($rowCount == 0) {
	require 'NoRecordsFound.php';
}
print "\n </table>";
require_once 'KeyboardTS.htm';
print "\n <script TYPE=\"text/javascript\">document.Chg.$focusField.focus();</script>";
print "\n </form>";

print $hrTagAttr;
require_once 'Copyright.php';

print "\n </td>";

// Tool Bar
print "\n <td>";
print "\n   <ul class=\"toolbarTS\">";
print "\n     <li class=\"optionTS\"><a href=\"{$baseURL}&amp;tag=NOUPDATE\">&nbsp;<br>Back</a></li>";
if ($groupNbr > "0") {
	print "\n <li class=\"optionTS\"><a href=\"{$homeURL}{$cGIPath}LaborEntryEmployees.d2w/REPORT{$d2wVarBase}&amp;ovrLabor=Y\">&nbsp;<br>Maintain Employees</a></li>";
}
print "\n     <li class=\"optionTS\"><a href=\"{$homeURL}{$phpPath}LaborEntrySelectOrders.php{$scriptVarBase}&amp;tag=BUILD\">&nbsp;<br>Add Orders</a></li>";
if ($rowCount > 0) {
	print "\n     <li class=\"optionTS\"><a href=\"javascript:kbInactive()\" onClick=\"showSel('selCmmt');\"> &nbsp;<br>Comments</a></li>";
	print "\n     <li class=\"optionTS\"><a href=\"javascript:document.Chg.reportOffOn.value='Y'; check(document.Chg)\">&nbsp;<br>Report</a></li>";
	print "\n     <li class=\"optionTS\"><a href=\"javascript:document.Chg.clockOff.value='Y'; check(document.Chg)\">&nbsp;<br>Clock Off</a></li>";
	print "\n     <li class=\"optionTS\"><a href=\"{$baseURL}\">&nbsp;<br>Refresh</a></li>";
}
print "\n   </ul>";
print "\n </td>";

print "\n </tr>";
print "\n </table>";
// require_once 'Trailer.php';
print "</body> </html>";

function setURL($workURL) {
	global $plantNumber, $profileHandle, $userProfile, $homeURL, $cGIPath, $phpPath, $helpPath, $imagePath, $baseVar, $eID, $newsLink, $HDMERL;
	
	$poshomeURL = strpos ( $workURL, "@@homeURL" );
	$newsLinkPos = strpos ( $workURL, "@@newsLink" );
	if ($newsLinkPos >= 0) {
		$workURL = str_replace ( "@@newsLink", $newsLink, $workURL );
	}
	
	$phpPos = strpos ( strtoupper ( $workURL ), ".PHP" );
	if ($phpPos > 0) {
		$baseVarWrk = $baseVar;
		$workURL = str_replace ( "@@phpPath", $phpPath, $workURL );
	} else {
		$phpPos = strpos ( strtoupper ( $baseVar ), ".PHP" );
		$baseVarWrk = substr ( $baseVar, 0, $phpPos );
		$baseVarWrk .= ".icl";
		$workURL = str_replace ( "@@cGIPath", $cGIPath, $workURL );
	}
	$workURL = str_replace ( "@@homeURL", $homeURL, $workURL );
	$workURL = str_replace ( "@@helpPath", $helpPath, $workURL );
	$profileHandleURL = urlencode ( $profileHandle );
	$workURL = str_replace ( "@@prfh", $profileHandleURL, $workURL );
	$userProfile = urlencode ( $_SERVER ['PHP_AUTH_USER'] );
	$workURL = str_replace ( "@@userProfile", $userProfile, $workURL );
	if (strpos ( $workURL, "@@meapid" ) !== false) {
		if ($HDMERL > "0") {
			$meapid = "ME";
		} else {
			$meapid = "ET";
		}
		$workURL = str_replace ( "@@meapid", $meapid, $workURL );
	}
	if (strpos ( $workURL, "?" ) !== false) {
		$workAmp = "&amp;";
	} else {
		$workAmp = "?";
	}
	
	if ($poshomeURL >= 0) {
		$pos = strpos ( $workURL, "@@baseVar" );
		if ($pos === false) {
			$workURL = trim ( $workURL );
			$workURL .= "{$workAmp}baseVar=" . urlencode ( $baseVarWrk ) . "&amp;eID=" . urlencode ( $eID );
			$workAmp = "&amp;";
		} else {
			$baseVarURL = urlencode ( $baseVarWrk );
			$workURL = str_replace ( "@@baseVar", $baseVarURL, $workURL );
		}
		// $pos= strpos($workURL, "@@browser");
		// if ($pos){$workURL = str_replace("@@browser", $browser, $workURL);}
	}
	
	$workURL = str_replace ( "@@timeStamp", urlencode ( $_SERVER ['REQUEST_TIME'] ), $workURL );
	$workURL = str_replace ( "@@plantNumber", urlencode ( $plantNumber ), $workURL );
	
	return $workURL;
}

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

?>