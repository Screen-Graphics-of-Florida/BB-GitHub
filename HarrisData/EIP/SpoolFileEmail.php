<?php

require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'GenericDirectCallVariables.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'VarBase.php';

$jobName	     = $_GET["jobName"];
$userName	     = $_GET["userName"];
$jobNbr 	     = $_GET["jobNbr"];
$spoolFileName	 = $_GET["spoolFileName"];
$spoolFileNumber = $_GET["spoolFileNumber"];
$userData	     = $_GET["userData"];

$page_title    = "E-mail Spool File";
$scriptName    = "SpoolFileEmail.php";
$scriptVarBase = "{$genericVarBase}&amp;spoolFileName=" . urlencode(trim($spoolFileName)) . "&amp;jobName=" . urlencode(trim($jobName)) . "&amp;userName=" . urlencode(trim($userName)) . "&amp;jobNbr=" . urlencode(trim($jobNbr)) . "&amp;spoolFileNumber=" . urlencode(trim($spoolFileNumber)) . "&amp;userData=" . urlencode(trim($userData));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$popUpWin      = "Y";

$i5Authority = i5_adopt_authority($userProfile, $_SERVER['PHP_AUTH_PW'], $i5Connect);
if (!$i5Authority) die("User Profile failed. Error number =".i5_errno()." msg=".i5_errormsg());
$i5Connect->setToolkitServiceParams(array('plugSize'=>'5M')); // bigger size to handle large spool file
$stringData = i5_spool_get_data($spoolFileName,$jobName,$userName,$jobNbr,$spoolFileNumber);
$i5Connect->setToolkitServiceParams(array('plugSize'=>'512K')); // reset plug size to default
if ($stringData === false) {
    print "\n \n <script TYPE=\"text/javascript\">";
    print "\n alert (\"Spool file cannot be emailed\"); \n";
    print "\n window.close() \n";
    print "\n </script> \n";
    exit();
}

if ($tag == "REPORT") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";

	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.emailFrom.value ==\"\" ||";
	print "\n     document.Chg.emailTo.value ==\"\" ||";
	print "\n     document.Chg.emailSubj.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n return true;";
	print "\n }";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr >";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	print "\n <td class=\"content\">";
	print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td><td>";
	print "\n <a href=\"javascript:check(document.Chg)\">$commentAcceptImage</a>";
	print "\n <a href=\"javascript:window.close()\">$cancelImageMed</a>";
	require_once 'HelpPage.php';
	print "\n </td></tr>";
	print "\n </table>";

	print "<table $contentTable>";
	Format_Header("File Name", $spoolFileName, "");
	Format_Header("Job", $jobName, "");
	Format_Header("User Data", $userData, "");
	print "\n </table>";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	if ($errFound != "") {
		if ($errFound == "") {
			$edtVar= "";
			$focusField= "fileName";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_ATDESC=DecatErr_Field("@@desc", "attachDesc");
			$Err_ATATNL=DecatErr_Field("@@atnl", "fileName");
			$Err_ATATNS=DecatErr_Field("@@atns", "attachmentName");
			$Err_ATPRIV=DecatErr_Field("@@priv", "attachPrivate");
		}

		$row['ATDESC']=Decat_Field("@@desc", $edtVar);
		$row['ATATNL']=Decat_Field("@@flsv", $edtVar);
		$row['ATATNS']=Decat_Field("@@atns", $edtVar);
		$row['ATDIRL']=Decat_Field("@@dirl", $edtVar);
		$row['ATPRIV']=Decat_Field("@@priv", $edtVar);
		$row['ATREPL']=Decat_Field("@@repl", $edtVar);

	} else {
		$focusField = "emailTo";
		$to         = $userEmail;
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_EMFROM);
	print "\n <tr>";
	print "\n <td class=\"dsphdr\">From:</td>";
	print "\n <td class=\"inputalph\"><input name=\"emailFrom\" type=\"text\" value=\"{$to}\" size=\"80\" maxlength=\"256\">{$reqFieldChar}</td>";
	print "\n </tr>";
	DspErrMsg($Err_EMFROM);

	$textOvr=SetTextOvr($Err_EMTO);
	print "\n <tr>";
	print "\n <td class=\"dsphdr\">To:</td>";
	print "\n <td class=\"inputalph\"><input name=\"emailTo\" type=\"text\" value=\"{$row['EMTO']}\" size=\"80\" maxlength=\"256\">{$reqFieldChar}</td>";
	print "\n </tr>";
	DspErrMsg($Err_EMTO);

	$textOvr=SetTextOvr($Err_EMCC);
	print "\n <tr>";
	print "\n <td class=\"dsphdr\">cc:</td>";
	print "\n <td class=\"inputalph\"><input name=\"emailCC\" type=\"text\" value=\"{$row['EMCC']}\" size=\"80\" maxlength=\"256\"></td>";
	print "\n </tr>";
	DspErrMsg($Err_EMCC);

	$textOvr=SetTextOvr($Err_EMBCC);
	print "\n <tr>";
	print "\n <td class=\"dsphdr\">bcc:</td>";
	print "\n <td class=\"inputalph\"><input name=\"emailBCC\" type=\"text\" value=\"{$row['EMBCC']}\" size=\"80\" maxlength=\"256\"></td>";
	print "\n </tr>";
	DspErrMsg($Err_EMBCC);

	$textOvr=SetTextOvr($Err_EMSUBJ);
	print "\n <tr>";
	print "\n <td class=\"dsphdr\">Subject:</td>";
	print "\n <td class=\"inputalph\"><input name=\"emailSubj\" type=\"text\" value=\"{$row['EMSUBJ']}\" size=\"80\" maxlength=\"256\">{$reqFieldChar}</td>";
	print "\n </tr>";
	DspErrMsg($Err_EMSUBJ);
	print "\n </table>";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.$focusField.focus();";
	print "\n </script>";
	print "\n </form>";
	print "\n <a href=\"javascript:check(document.Chg)\">$commentAcceptImage</a>";
	print "\n <a href=\"javascript:window.close()\">$cancelImageMed</a>";
	print $hrTagAttr;

	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require ($searchTrailer);
	print "</body> </html>";
	exit;

} else {

	$from    = $_POST['emailFrom'];
	$to      = $_POST['emailTo'];
	$cc      = $_POST['emailCC'];
	$bcc     = $_POST['emailBCC'];
	$subject = $_POST['emailSubj'];
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion();
	if ($cc)  {$headers .= "\r\n" .'Cc: ' . $cc;}
	if ($bcc) {$headers .= "\r\n" . 'Bcc: ' . $bcc;}

	$pageBreak = '____________________________________________________________________________________________________________________________________' . chr(13) . chr(10);

	$newstr = str_replace(chr(12), $pageBreak, $stringData);
	$message = "
<html>
<head>
  <title>HTML E-mail</title>
</head>
<body>
  <table border=\"1\" width=\"100%\"><tr><td>
  <pre><code><font SIZE=1>{$newstr}</font></code></pre>
  </td></tr>
</body>
</html>
";
	mail($to, $subject, $message, $headers);
	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n window.close();";
	print "\n </script> \n";
	exit();}
?>