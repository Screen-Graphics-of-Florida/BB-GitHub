<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromScript         = $_GET['fromScript'];
$fromRwSelection    = $_GET['fromRwSelection'];
$inquiryOnly        = $_GET['inquiryOnly'];
$cmtSelection       = $_GET['cmtSelection'];

require_once 'SetLibraryList.php';

require_once "GLControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'UserDefined_Number_Include.php';
require_once 'VarBase.php';

$page_title     = "G/L Report Writer Selection Comments";
$scriptName     = "GLReportWriterSelectionComment.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromRwSelection=" . urlencode(trim($fromRwSelection)) . "&amp;inquiryOnly=" . urlencode(trim($inquiryOnly)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HGLRWS_E";

if (is_null($tag)) {$tag="MAINTAIN";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once 'HeadCmtInclude.php';
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	require_once 'NoFormValidate.php';
	print "\n   function checkLimit(opt_countedTextBox, opt_charCnt) { ";
	print "\n     var countedTextBox = opt_countedTextBox ? opt_countedTextBox : \"cmtFld\"; ";
	print "\n     var charCnt = opt_charCnt ? opt_charCnt : \"charCnt\"; ";
	print "\n     var maxSize = 32000; ";
	print "\n     var field = document.getElementById(countedTextBox); ";
	print "\n     if (field && field.value.length >= maxSize) {field.value = field.value.substring(0, maxSize);} ";
	print "\n     var txtField = document.getElementById(charCnt); ";
	print "\n     if (txtField) {txtField.innerHTML = maxSize - field.value.length;} ";
	print "\n   } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onload=\"checkLimit('cmtFld', 'charCnt')\"> ";
	require_once ($cmtBanner);
	print "\n <table $contentTable>";
	print "\n     <tr valign=\"top\">";
	print "\n         <td width=30>&nbsp;</td> ";
	print "\n         <td> ";
	$comments="";
	if ($cmtSelection != "C") {$comment=GetGLRptWtrComment($fromRwSelection);}
	else                      {$comment="";}


	print "\n <table $contentTable> ";
	print "\n     <colgroup><col width=\"70%\"><col width=\"30%\"> ";
	print "\n     <tr><td><h1>$page_title</h1></td> ";
	print "\n         <td class=\"toolbar\"> ";
	if ($inquiryOnly == "Y") {require 'CloseWindow.php';}
	else {
		print "\n <a href=\"javascript:check(document.Chg)\">$commentAcceptImage</a> ";
		print "\n <a href=\"javascript:document.Chg.cmtSelection.value='R'; check(document.Chg)\">$commentResetImage</a> ";
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;cmtSelection=C\">$commentClearImage</a> ";
		$medIcon="Y";
		require 'HelpPage.php';
		print "\n <a href=\"javascript:window.close()\">$closeImageMed</a> ";
	}
	print "\n         </td> ";
	print "\n     </tr> ";
	print "\n </table> ";

	print "\n <table $contentTable> ";
	$GIGRSD=RetValue("GIGRSN='$fromRwSelection' ", "GLWRSM", "GIGRSD");
	Format_Header("Selection Name", $GIGRSD, $fromRwSelection);
	print "\n </table> ";

	print "\n <form class=\"formClass\" METHOD=POST NAME=Chg ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data\"> ";
	print "\n     <table $contentTable> ";
	print "\n         <tr><td><input type=\"hidden\" name=\"cmtSelection\"></td> ";
	print "\n             <td> ";
	if ($comment != "") {print "\n <textarea id=\"cmtFld\" name=\"comments\" rows=\"20\" COLS=60 WRAP=\"hard\" onKeyUp=\"checkLimit(\'cmtFld\', \'charCnt\');\" onKeyPress=\"checkLimit(\'cmtFld\', \'charCnt\');\">$comment</textarea> ";}
	else                {print "\n <textarea id=\"cmtFld\" name=\"comments\" rows=\"20\" COLS=60 WRAP=\"hard\" onKeyUp=\"checkLimit(\'cmtFld\', \'charCnt\');\" onKeyPress=\"checkLimit(\'cmtFld\', \'charCnt\');\"></textarea> ";}
	print "\n             </td> ";
	print "\n         </tr> ";
	print "\n     </table> ";
	print "\n     <table $contentTable> ";
	print "\n         <colgroup><col width=\"70%\"><col width=\"30%\"> ";
	print "\n         <tr><td valign=\"top\"> ";
	if ($inquiryOnly == "Y") {require 'CloseWindow.php';}
	else {
		print "\n <a href=\"javascript:check(document.Chg)\">$commentAcceptImage</a> ";
		print "\n <a href=\"javascript:document.Chg.cmtSelection.value='R'; check(document.Chg)\">$commentResetImage</a> ";
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;cmtSelection=C\">$commentClearImage</a> ";
		$medIcon="Y";
		require 'HelpPage.php';
		print "\n <a href=\"javascript:window.close()\">$closeImageMed</a> ";
	}
	print "\n             </td> ";
	print "\n             <td nowrap><b><span id=\"charCnt\">0</span></b> Characters Left</td> ";
	print "\n         </tr> ";
	print "\n    </table> ";
	print "\n </form> ";
	print "\n </td></tr></table> ";
	require $cmtTrailer;
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	if ($_POST['cmtSelection'] == "R") {print "\n  <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN\"> ";}
	else {
		$edtVar="";
		Concat_Field("@@grsn", $fromRwSelection);
		$edtVar .= "}{";
		$returnValue=UpdGLRptWtrComment($profileHandle, $edtVar, $_POST['comments']);
		$comments=$returnValue['comments'];

		$confMessage="Confirm Update Of Comment";
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n   opener.location.href=opener.location.href; ";
		print "\n   opener.focus(); ";
		print "\n   window.close(); ";
		print "\n </script> ";
	}
}

// Get Comment %}
function GetGLRptWtrComment ($fromRwSelection,$comment) {
	if (is_null($comment))       {$comment="";}

	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>GetGLRptWtrComment Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"fromRwSelection"  , "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"comment"          , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000")
	);

	$pgm = i5_program_prepare("HGLRWS_P", $pgmCall);
	if (!$pgm) {die("<br>GetGLRptWtrComment Program (HGLRWS_P) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"fromRwSelection"  =>$fromRwSelection,
	"comment"          =>$comment
	);

	$parmOut = array(
	"comment"          =>"comment"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>GetGLRptWtrComment Program (HGLRWS_P) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['comment']=$comment;
	return $comment;
}

// Update Comment %}
function UpdGLRptWtrComment ($profileHandle,$edtVar,$comment) {
	if (is_null($comment))       {$comment="";}

	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>UpdGLRptWtrComment Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$pgmCall = array(
	array("Name"=>"profileHandle"    , "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"edtVar"           , "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"comment"          , "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000")
	);

	$pgm = i5_program_prepare("HGLRWS_WC", $pgmCall);
	if (!$pgm) {die("<br>UpdGLRptWtrComment Program (HGLRWS_WC) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"    =>$profileHandle,
	"edtVar"           =>$edtVar ,
	"comment"          =>$comment
	);

	$parmOut = array(
	"comment"          =>"comment"
	);

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>UpdGLRptWtrComment Program (HGLRWS_WC) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['comment']=$comment;
	return $returnValue;
}


?>
