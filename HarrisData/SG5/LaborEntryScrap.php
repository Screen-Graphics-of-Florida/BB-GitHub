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

$page_title = "Labor Entry - Scrap Quantities";
$scriptName = "LaborEntryScrap.php";
$scriptVarBase = "{$genericVarBase}&amp;touchScreen=Y";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL = "{$scriptName}{$scriptVarBase}";
$nextPrevVar = "{$scriptVarBase}";
$d2wVarBase = "{$altVarBase}&amp;touchScreen=Y";
$programName = "HETLBE";
//$maxRows = "4";
//$dspMaxRows = "4";
$rowIndexCurr = $startRow;
$formatToPrint = "";
$groupNbr = "0";
$plantNumber = "";
$focusField = "";

$returnURL = "ShopFloorDispatch.d2w/BUILD{$d2wVarBase}";

if ($tag == "NOUPDATE") {
	print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$returnURL}\"> ";
	exit ();
}

// Program Option Security
 $hsi214_OPT = pgmOptSecurity ( $profileHandle, $dataBaseID, 'HSI214' );

if ($tag == "Edit_Data") {
	$edtVar = "";
	
	Concat_Field ( "@@subr", "SrAcceptS" );
	Concat_Field ( "@@rows", $_POST ['displayedRows'] );
	Concat_Field ( "@@cmnt", $_POST ['comment60'] );
	for($i = 1; $i <= $_POST ['displayedRows']; $i ++) {
		$x = str_pad ( $i, 4, '0', STR_PAD_LEFT );
		$OR = "OR$x";
		$OS = "OS$x";
		$IT = "IT$x";
		$QS = "QS$x";
		$SR = "SR$x";
		$RQ = "RQ$x";
		$RR = "RR$x";
		if ($hsi214_OPT ['sec_01'] == 'Y') {
			$OC = "OC$x";
		}
		Concat_Field ( "$OR", $_POST [$OR] );
		Concat_Field ( "$OS", $_POST [$OS] );
		Concat_Field ( "$IT", $_POST [$IT] );
		$_POST [$QS] = str_replace(',', '', $_POST [$QS]);
		Concat_Field ( "$QS", $_POST [$QS] );
		Concat_Field ( "$SR", $_POST [$SR] );
		$_POST [$RQ] = str_replace(',', '', $_POST [$RQ]);
		Concat_Field ( "$RQ", $_POST [$RQ] );
		Concat_Field ( "$RR", $_POST [$RR] );
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
	
	if ($errFound == "") {
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL=$baseURL&amp;timeStamp=" . urlencode ( trim ( $_SERVER ['REQUEST_TIME'] ) ) . "\">";
		exit ();
	} elseif ($errFound == "X") {
	    print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}EmployeeSignOn.d2w/REPORT{$d2wVarBase}\"> ";
	    exit ();
	}
}

$stmtSQL = " Select * From SIDCTW Where WDXHND='$profileHandle'";
$stmtSQL .= " Fetch First 1 Row Only With NC";
$sqlResultDC = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$rowDC = db2_fetch_assoc ( $sqlResultDC );

$plantNumber = $rowDC ['WDPLT'];

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

function validate(chgForm) {
	var rows = (chgForm.displayedRows.value ? chgForm.displayedRows.value : 0);
    var ok = true;
    for (var rx = 1; rx <= rows; rx++) {
     	var suffix = rx.toString();
        while (suffix.length < 4) {
          	suffix = "0" + suffix;
        }

        elemName = "QS" + suffix;
        chgForm[elemName].value = numberWithoutCommas(chgForm[elemName].value);
        ok = (editNum(chgForm[elemName], 9, 4));
        chgForm[elemName].value = numberWithCommas(chgForm[elemName].value);
        if (!ok) {break;}
    }
    return ok;
}

<?php
print "\n </script>";
require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

// Labor Entry Header
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
$codeDescription = RetValue ( "DFCODE={$rowDC['WDDTCL']}", "ETDCDF", "DFDESC" );
Format_Header ( "Data Collection Code:", $codeDescription, $rowDC['WDDTCL'] );
print "\n </table>";

// %INCLUDE "MoreInfo.icl"
print $hrTagAttr;
require_once 'ErrorDisplay.php';

// Scrap Query
require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " SILOPW ";
$selectSQL .= " WOXHND='$profileHandle' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By WOMORD";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';

print "\n \n <form class=\"formClass\" method=\"post\" name=\"Chg\" action=\"{$baseURL}&amp;tag=Edit_Data\">";

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

print "\n <tr><th class=\"colhdr\">Order</th>";
print "\n     <th class=\"colhdr\">Seq</th>";
print "\n     <th class=\"colhdr\">Item Number</th>";
print "\n     <th class=\"colhdr\">Quantity<br>Scrapped</th>";
print "\n     <th class=\"colhdr\">Scrapped<br>Reason Cd</th>";
if ($hsi214_OPT ['sec_01'] == 'Y') {
	print "\n     <th class=\"colhdr\">Oper Cmpl</th>";
}
print "\n     <th class=\"colhdr\">Running Total<br>Qty Scrapped</th>";
print "\n     <th class=\"colhdr\">Last Scrap<br>Reason Cd</th>";
print "\n </tr>";

$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );

$rowCount = 0;
while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
	$rowCount ++;
	$startRow ++;
	
	require 'SetRowClass.php';
	
	if ($row ['WOQTYS'] == 0) {
		$row ['WOQTYS'] = "";
	}

	$recNbr = $rowCount;
	$recNbr = str_pad ( $recNbr, 4, '0', STR_PAD_LEFT );
	
	if ($focusField == "") {
		$focusField = "QS$recNbr";
	}
	
	// First row
	print "\n <tr class=\"$rowClass\">";
	$mfgOrder = trim ( $row ['WOMORD'] );
	$seqNumber = trim ( $row ['WOSEQN'] );
	print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"OR$recNbr\" value=\"{$row['WOMORD']}\">{$row['WOMORD']}</td>";
	print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"OS$recNbr\" value=\"{$row['WOSEQN']}\">{$row['WOSEQN']}</td>";
	print "\n <td class=\"colalph\"><input type=\"hidden\" name=\"IT$recNbr\" value=\"{$row['WOITEM']}\">{$row['WOITEM']}</td>";
	// Quantity Inputs
	$formattedQS = Format_Nbr ( $row ['WOQTYS'], 4, 'K', '', '', '' );
	if (strpos ( $formattedQS, '-' ) !== false) {
		$formattedQS = '-' . str_replace ( '-', '', $formattedQS );
	}
	print "\n <td class=\"input\"><input class=\"input\" id=\"QS$recNbr\" onfocus=\"kbActive(this, 'numericKeyboard', true);\" onkeyup=\"formatInput(this.id);\" name=\"QS$recNbr\" type=\"text\" value=\"{$formattedQS}\" size=\"12\" maxlength=\"17\"></td>";
    // Scrapped Reason Code Input
    $fSCRC = trim($row['WOSCRC']);
    print "\n <td class=\"input\"><input class=\"input\" id=\"SR$recNbr\" onfocus=\"kbActive(this, 'keyboard');\" name=\"SR$recNbr\" type=\"text\" value=\"$fSCRC\" size=\"1\" maxlength=\"2\">";
    print "\n   <a href=\"{$homeURL}{$phpPath}TransactionTypeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=SR$recNbr&amp;fldDesc=SR{$recNbr}Desc&amp;touchScreen=Y\" onclick=\"$searchWinVar\"> $searchDesc</a>";
    print "\n </td>";
    if ($hsi214_OPT ['sec_01'] == 'Y') {
        $checked = Field_Checked ( $row ['WOOPRC'], 'Y' );
        print "\n <td class=\"colcode\"><input class=\"bigcheck\" type=\"checkbox\" id=\"{OC$recNbr}\" name=\"OC$recNbr\" value=\"Y\" $checked></td>";
    }
    // Running Scrap Total and Last Reason Code
    $formattedQSCT = Format_Nbr ( $row ['WOQSCT'], 4, 'K', '', '', '' );
    if (strpos ( $formattedQSCT, '-' ) !== false) {
        $formattedQSCT = '-' . str_replace ( '-', '', $formattedQSCT );
    }
    print "\n <td class=\"dspalph\">{$formattedQSCT}</td>";
    print "\n <td class=\"dspalph\">{$row ['WOSCCL']}</td>";

    print "</tr>";
	// Second row
	print "<tr>";
	print "\n <td> </td>";
	print "\n <td> </td>";
	print "\n <td class=\"hdrdata\" colspan=\"6\">{$row['WOIMDS']}</td>";
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
	
	print "\n <tr><td><input type=\"hidden\" name=\"SR{$recNbr}Desc\" ></td></tr>";
    print "\n <tr><td><input type=\"hidden\" name=\"RR{$recNbr}Desc\" ></td></tr>";
}

print "\n <tr><td><input type=\"hidden\" name=\"displayedRows\" value=\"$rowCount\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"rowIndexCurr\" value=\"$rowIndexCurr\"></td></tr>";
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
print "\n     <li class=\"optionTS\"><a href=\"{$baseURL}&amp;tag=NOUPDATE\">&nbsp;<br>Done</a></li>";
if ($rowCount > 0) {
	print "\n     <li class=\"optionTS\"><a href=\"javascript:kbInactive()\" onClick=\"showSel('selCmmt');\"> &nbsp;<br>Comments</a></li>";
	print "\n     <li class=\"optionTS\"><a href=\"javascript:check(document.Chg)\">&nbsp;<br>Accept</a></li>";
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