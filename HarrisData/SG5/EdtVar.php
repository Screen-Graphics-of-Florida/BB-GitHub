<?php

$typeReset               = "{Reset}";

// $operandValue            = "";
// $userEdtVar              = "";
// $errorColor              = "";
// $errorSpan               = "";
// $textOvr                 = "";
// $typeUserDef             = "U";
// $typeParmDef             = "W";


function Concat_Error ($fieldName,$fieldValue) {
	global $errVar;
	if ($errVar == "") {$errVar="{$fieldName}{$fieldValue}";}
	else               {$errVar.="}{{$fieldName}{$fieldValue}";}
}

function Concat_Field ($fieldName, $fieldValue) {
	global $edtVar;
	if ($edtVar == "") {$edtVar="{$fieldName}{$fieldValue}";}
	else               {$edtVar.="}{{$fieldName}{$fieldValue}";}
}

function Decat_Field ($fieldName, $srchField) {
	$fieldValue = "";
	$pos = strpos($srchField, $fieldName);
	if ($pos !== false){
		$posNext = strpos($srchField, "}{", $pos += 6);
		$fieldValue = substr($srchField, $pos, $posNext - $pos);
	}
	return $fieldValue;
}

/*
function Decat_Negative ($fieldName) {
$fieldValue= "";
$pos=strpos($edtVar,$fieldName);
if ($pos !== false) {
$posNext=strpos($edtVar,"}{", $pos+1);
$fieldValue=substr($edtVar, $pos+6, $posNext-($pos+6));
}
@dtw_lastpos("-", fieldValue, pos)
if ($pos !== false) {
@dtw_concat("-", @dtw_rsubstr(fieldValue, "1", @dtw_rsubtract(pos, "1")), fieldValue)
}
return $fieldValue;
}

function Decat_WFParm_Field ($fieldName) {
$fieldValue= "";
$operandValue= "";
$pos=strpos($parmEdtVar,$fieldName);
if ($pos !== false) {
$posNext=strpos($parmEdtVar,"}{",$pos+1);
@dtw_substr(parmEdtVar, @dtw_radd(pos, "6"), $posNext-($pos+6), operandValue)

$pos= $posNext;
$posNext=strpos($parmEdtVar,"}{",$pos+2);
@dtw_substr(parmEdtVar, @dtw_radd(pos, "2"), @dtw_rsubtract(posNext, @dtw_radd(pos, "2")), fieldValue)
}
}
*/

function DecatErr_Field ($fieldName, $inputField) {
	global $errVar;
	global $focusField;
	$fieldValue= "";
	$pos = strpos($errVar, $fieldName);

	if ($pos !== false){
		$posNext = strpos($errVar, "}{", $pos += 6);
		$fieldValue = substr($errVar, $pos,  $posNext - $pos);
		if ($focusField == "") {$focusField=$inputField;}
	}
	return $fieldValue;
}

function DspErrMsg ($inputField) {
	if ($inputField != "") { print "\n<tr><td>&nbsp;</td><td class=\"error\" colspan=\"10\">$inputField</td></tr>";}
}

// EditVar Error Set/Retrieve
function EdtVarErr ($profileHandle,$edtVar) {
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>EdtVarErr Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"typeValue"    , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar"       , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HSYEER_W", $pgmCall);
	if (!$pgm) {die("<br>EdtVarErr Program (HSYEER_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$typeValue="V";
	if (is_null($edtVar ))$edtVar="";
	$parmIn = array(
	"profileHandle"=>$profileHandle,
	"typeValue"    =>$typeValue,
	"edtVar"       =>$edtVar
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"typeValue"    =>"typeValue",
	"edtVar"       =>"edtVar"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>EdtVarErr Program (HSYEER_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $edtVar;
}

// EditVar Error Set/Retrieve
function ErrVarErr ($profileHandle,$errVar) {
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>ErrVarErr Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"typeError"    , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"errVar"       , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HSYEER_W", $pgmCall);
	if (!$pgm) {die("<br>ErrVarErr Program (HSYEER_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$typeError="E";
	if (is_null($errVar ))$errVar="";
	$parmIn = array(
	"profileHandle"=>$profileHandle,
	"typeError"    =>$typeError,
	"errVar"       =>$errVar
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"typeError"    =>"typeError",
	"errVar"       =>"errVar"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>ErrVarErr Program (HSYEER_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $errVar;
}

/*
%{ ParmEdtVar Error Set/Retrieve %}
%FUNCTION(dtw_directcall) ParmVarErr (IN    CHAR(64)    profileHandle,
CHAR(1)     typeParmDef,
INOUT CHAR(32000) parmEdtVar)
{%EXEC {HSYEER_W.PGM %}
%}

function ReqTextOvr ($inputField) {
if ($inputField == "") {$textOvr= $fldTextErrOvr;}
return $inputField;
}

function SetErrorSpan ($inputField) {
if ($inputField != "") {
$errorSpan = "<span title=\"$inputField\">";
$errorColor= "style=background-color:red;";
} else {
$errorSpan = "<span>";
$errorColor= "";
}
}
*/

function  SetTextOvr ($inputField) {
	global $textOvr, $fldTextErrOvr;

	if ($inputField != "") {$textOvr=$fldTextErrOvr;}
	else                   {$textOvr="";}
	return $textOvr;
}


/**
 * Build Flag Entry
 *
 * @param string $fldDesc
 * @param string $fldName
 * @param string $fldType
 * @param string $fldID
 * @param string $fldValue
 * @param string $fldErr
 * @param string $fldSize
 * @param string $fldMax
 * @param string $fldReq
 * @param string $fldDspOnly
 * @param string $dspTDOnly
 */
function Build_Flag_Entry($fldDesc = '',$fldName = '',$fldType = '',$fldID = '',$fldValue = '',$fldErr = '',$fldSize = '',$fldMax = '',$fldReq = '',$fldDspOnly = '',$dspTDOnly = '') {
	global $genericVarBase, $searchWinVar, $searchImage, $reqFieldChar, $calendarImage;

	if (!isset($dspTDOnly) || $dspTDOnly != "Y") {$dspTDOnly = "";}
	$fldReqDesc = "";
	if ($fldReq == "Y") {$fldReqDesc = $reqFieldChar;}

	if ($dspTDOnly != "Y") {
		$textOvr=SetTextOvr($fldErr);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>$fldDesc</span></td> ";
	}
	if ($fldType == "YORN" || $fldType == "BY") {
		$fldDisabled = "";
		if ($fldDspOnly == "Y") {$fldDisabled = "DISABLED";}
		$fldChecked=Field_Checked($fldValue,"Y");
		print "\n     <td><input type=\"checkbox\" style=\"overflow:hidden;width:16px;height:22px;padding:0:1:1;margin:0;\" name=\"$fldName\" id=\"$fldName\" value=\"Y\" $fldDisabled $fldChecked></td>";

	} elseif ($fldType == "BN") {
		$fldDisabled = "";
		if ($fldDspOnly == "Y") {$fldDisabled = "DISABLED";}
		$fldChecked=Field_Checked(trim($fldValue),"");
		print "\n     <td><input type=\"checkbox\" style=\"overflow:hidden;width:16px;height:22px;padding:0:1:1;margin:0;\" name=\"$fldName\" id=\"$fldName\" value=\"\" $fldDisabled $fldChecked></td>";

	} elseif (trim($fldType) == "Date") {
		if ($fldDspOnly == "Y") {
			$fldValue=Format_Date(DateToCYMD($fldValue),"D");
			print "\n <td class=\"inputdate\"><input type=\"hidden\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\" size=\"6\" maxlength=\"6\">$fldValue</td>";
		} else {
			print "\n <td class=\"inputdate\"><input type=\"text\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\" size=\"6\" maxlength=\"6\">";
			print "\n                         <a href=\"javascript:calWindow('$fldName');\"> $fldReqDesc $calendarImage</a></td>";
		}

	} elseif ($fldDspOnly == "Y") {
		$fieldDesc=RetValue("FLTYPE='$fldType' and FLVALU='$fldValue'", "SYFLAG", "FLDESC");
		print "\n <td class=\"dspalph\"><input type=\"hidden\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\">$fieldDesc</td> ";

	} elseif (trim($fldType) != "") {
		$fieldDesc=RetValue("FLTYPE='$fldType' and FLVALU='$fldValue'", "SYFLAG", "FLDESC");
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\" size=\"$fldSize\" maxlength=\"$fldMax\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=$fldType&amp;flagSrchHdr=". urlencode($fldDesc) . "&amp;fldName=$fldName&amp;fldDesc={$fldName}Desc\" onclick=\"$searchWinVar\"> $fldReqDesc $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"{$fldName}Desc\">" .trim($fieldDesc) . "</span></td>";

	} else {
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\" size=\"$fldSize\" maxlength=\"$fldMax\"> $fldReqDesc </td>";
	}
	if ($dspTDOnly != "Y") {
		print "\n </tr> ";
		DspErrMsg($fldErr);
	}
}

/**
 * Build Fld Entry
 *
 * @param string $fldDesc
 * @param string $fldName
 * @param string $fldClass
 * @param string $fldType
 * @param string $fldID
 * @param string $fldValue
 * @param string $fldErr
 * @param string $fldSize
 * @param string $fldMax
 * @param string $fldReq
 * @param string $fldDspOnly
 * @param string $dspTDOnly
 */
function Build_Fld_Entry($fldDesc = '',$fldName = '',$fldClass = '',$fldType = '',$fldID = '',$fldValue = '',$fldErr = '',$fldSize = '',$fldMax = '',$fldReq = '',$fldDspOnly = '',$dspTDOnly = '') {
	global $genericVarBase, $searchWinVar, $searchImage, $reqFieldChar, $calendarImage;
	
	if (!isset($dspTDOnly) || $dspTDOnly != "Y") {$dspTDOnly = "";}
	if ($fldReq == "Y") {$fldReqDesc = $reqFieldChar;} else {$fldReqDesc = "";}

	if ($dspTDOnly != "Y") {
		$textOvr=SetTextOvr($fldErr);
		if ($fldDesc == "") {$fldDesc="&nbsp;";}
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>$fldDesc</span></td> ";
	}
	if ($fldType == "YORN" || $fldType == "BY") {
		$fldDisabled = "";
		if ($fldDspOnly == "Y") {$fldDisabled = "DISABLED";}
		if ($dspTDOnly == "Y")  {$fldClass = "checkBoxCol";}
		$fldChecked=Field_Checked($fldValue,"Y");
		print "\n     <td class=\"$fldClass\"><input type=\"checkbox\" class=\"checkbox\" name=\"$fldName\" id=\"$fldName\" value=\"Y\" $fldDisabled $fldChecked></td>";

	} elseif ($fldType == "BN") {
		$fldDisabled = "";
		if ($fldDspOnly == "Y") {$fldDisabled = "DISABLED";}
		$fldChecked=Field_Checked(trim($fldValue),"");
		print "\n     <td class=\"$fldClass\"><input type=\"checkbox\" class=\"checkbox\" name=\"$fldName\" id=\"$fldName\" value=\"\" $fldDisabled $fldChecked></td>";

	} elseif (trim($fldType) == "Date") {
		if ($fldDspOnly == "Y") {
			$fldValue=Format_Date(DateToCYMD($fldValue),"D");
			print "\n <td class=\"$fldClass\"><input type=\"hidden\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\" size=\"6\" maxlength=\"6\">$fldValue</td>";
		} else {
			print "\n <td class=\"$fldClass\"><input type=\"text\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\" size=\"6\" maxlength=\"6\">";
			print "\n                         <a href=\"javascript:calWindow('$fldName');\"> $fldReqDesc $calendarImage</a></td>";
		}

	} elseif ($fldDspOnly == "Y") {
		$fieldDesc=RetValue("FLTYPE='$fldType' and FLVALU='$fldValue'", "SYFLAG", "FLDESC");
		print "\n <td class=\"$fldClass\"><input type=\"hidden\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\">$fieldDesc</td> ";

	} elseif (trim($fldType) != "") {
		$fieldDesc=RetValue("FLTYPE='$fldType' and FLVALU='$fldValue'", "SYFLAG", "FLDESC");
		print "\n     <td class=\"$fldClass\"><input type=\"text\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\" size=\"$fldSize\" maxlength=\"$fldMax\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=$fldType&amp;flagSrchHdr=". urlencode($fldDesc) . "&amp;fldName=$fldName&amp;fldDesc={$fldName}Desc\" onclick=\"$searchWinVar\"> $fldReqDesc $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"{$fldName}Desc\">" .trim($fieldDesc) . "</span></td>";

	} else {
		//$decPos = 3;
		//$fldValue = sprintf("%.{$decPos}f\n",$fldValue);
		print "\n     <td class=\"$fldClass\"><input type=\"text\" name=\"$fldName\" id=\"$fldName\" value=\"" . rtrim($fldValue) . "\" size=\"$fldSize\" maxlength=\"$fldMax\"> $fldReqDesc </td>";
	}
	if ($dspTDOnly != "Y") {
		print "\n </tr> ";
		DspErrMsg($fldErr);
	}
}

/**
 * Build Advanced Search Entry
 *
 * @param string $fldDesc
 * @param string $fldFrom
 * @param string $fldTo
 * @param string $fldOper
 * @param string $fldSel
 * @param string $fldType
 * @param string $fldSize
 * @param string $fldMax
 */
function Build_AdvSrch_Entry($fldDesc = '',$fldFrom = '',$fldTo = '',$fldOper = '',$fldSel = '',$fldType = '',$fldSize = '',$fldMax = '') {
	global $calendarImage;

	$operNbr = "$fldOper";
	print "\n <tr><td class=\"dsphdr\">$fldDesc</td> ";
	print "\n     <td>";
	if ($fldType && $fldType != "P") {require "$fldSel.php";} else {print "\n &nbsp;";}
	print "</td>";
	$inputType = "inputAlph";
	if ($fldType == "D" || $fldType == "N") {$inputType = "inputNmbr";}


	if ($fldType == "D") {
		print "\n     <td class=\"$inputType\"><input type=\"text\" name=\"$fldFrom\" id=\"$fldFrom\" size=\"6\" maxlength=\"6\">";
		print "\n                             <a href=\"javascript:calWindow('$fldFrom');\">$calendarImage</a></td>";
	} else {
		print "\n     <td class=\"$inputType\"><input type=\"text\" name=\"$fldFrom\" id=\"$fldFrom\" size=\"$fldSize\" maxlength=\"$fldMax\"></td>";
	}
	if ($fldTo) {
		if ($fldType == "D") {
			print "\n     <td class=\"$inputType\"><input type=\"text\" name=\"$fldTo\" id=\"$fldTo\" size=\"6\" maxlength=\"6\">";
			print "\n                             <a href=\"javascript:calWindow('$fldTo');\">$calendarImage</a></td>";
		} else {
			print "\n     <td class=\"$inputType\"><input type=\"text\" name=\"$fldTo\" id=\"$fldTo\" size=\"$fldSize\" maxlength=\"$fldMax\"></td>";
		}
	}
	print "\n </tr>";
}

// UserEdtVar Error Set/Retrieve
function UsrVarErr ($profileHandle,$userEdtVar) {
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>UsrVarErr Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"typeValue"    , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"userEdtVar"   , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HSYEER_W", $pgmCall);
	if (!$pgm) {die("<br>UsrVarErr Program (HSYEER_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$typeValue="U";
	if (is_null($userEdtVar ))$userEdtVar="";
	$parmIn = array(
	"profileHandle"=>$profileHandle,
	"typeValue"    =>$typeValue,
	"userEdtVar"   =>$userEdtVar
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"typeValue"    =>"typeValue",
	"userEdtVar"   =>"userEdtVar"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>UsrVarErr Program (HSYEER_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $userEdtVar;
}

// WrnVar Error Set/Retrieve
function WrnVarErr ($profileHandle,$wrnVar) {
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>WrnVarErr Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"typeError"    , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"wrnVar"       , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HSYEER_W", $pgmCall);
	if (!$pgm) {die("<br>WrnVarErr Program (HSYEER_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$typeError="W";
	if (is_null($wrnVar ))$wrnVar="";
	$parmIn = array(
	"profileHandle"=>$profileHandle,
	"typeError"    =>$typeError,
	"wrnVar"       =>$wrnVar
	);

	$parmOut = array(
	"profileHandle"=>"profileHandle",
	"typeError"    =>"typeError",
	"wrnVar"       =>"wrnVar"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>WrnVarErr Program (HSYEER_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	return $wrnVar;
}

/**
 * simple method to encrypt or decrypt a plain text string
 * initialization vector(IV) has to be the same when encrypting and decrypting
 *
 * @param string $action: can be 'encrypt' or 'decrypt'
 * @param string $string: string to encrypt or decrypt
 *
 * @return string
 */
function encrypt_decrypt($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = '2ffofwero9fiuj24rt24j3rij';
    $secret_iv = 'zxvojoifjrweiofjifrj6j67';
    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}
?>