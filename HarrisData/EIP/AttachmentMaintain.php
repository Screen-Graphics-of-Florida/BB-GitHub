<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound        = $_GET['errFound'];
$attachDesc      = $_GET['attachDesc'];
$attachLongName  = $_GET['attachLongName'];
$attachShortName = $_GET['attachShortName'];
$attachFolder    = $_GET['attachFolder'];
$attachFolderU   = strtoupper($attachFolder);
$attachForDesc   = $_GET['attachForDesc'];
$attachVarKey    = $_GET['attachVarKey'];
$attachPrg1      = $_GET['attachPrg1'];
$attachPrg2      = $_GET['attachPrg2'];
$attachPrg3      = $_GET['attachPrg3'];
$attachPrg4      = $_GET['attachPrg4'];
$attachPrg5      = $_GET['attachPrg5'];
$bodyFile        = $_GET['bodyFile'];
$directLink      = $_GET['directLink'];
$attachPrivate   = $_GET['attachPrivate'];
$attachUser      = $_GET['attachUser'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Attachment Maintain";
$scriptName    = "AttachmentMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;attachFolder=" . urlencode($attachFolder) . "&amp;attachForDesc=" . urlencode($attachForDesc) . "&amp;attachVarKey=" . urlencode($attachVarKey) . "&amp;attachUser=" . urlencode($attachUser) . "&amp;attachPrg1=" . urlencode($attachPrg1) . "&amp;attachPrg2=" . urlencode($attachPrg2) . "&amp;attachPrg3=" . urlencode($attachPrg3) . "&amp;attachPrg4=" . urlencode($attachPrg4) . "&amp;attachPrg5=" . urlencode($attachPrg5);
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL     = "{$homeURL}{$phpPath}Attachment.php{$genericVarBase}&amp;attachFolder=" . urlencode($attachFolder) . "&amp;attachFolderU=" . urlencode($attachFolderU) . "&amp;attachForDesc=" . urlencode($attachForDesc) . "&amp;attachVarKey=" . urlencode($attachVarKey) . "&amp;attachShortName=" . urlencode($attachShortName) . "&amp;attachLongName=" . urlencode($attachLongName) . "&amp;attachDesc=" . urlencode($attachDesc) . "&amp;tag=DELETE&amp;maintenanceCode=D";
$programName   = "HSYATM_W";
$popUpWin      = "Y";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n function chgToFile(from) {";
	print "\n wrkFld1 = from.substring(from.lastIndexOf('\\\\')+1,from.length);";
	print "\n wrkFld3 = from.substring(from.lastIndexOf('\\\\')+1,from.lastIndexOf('.'));";
	print "\n wrkFld2 = from.substring(0,from.length);";
	print "\n if (document.Chg.attachmentName.value ==\"\") {document.Chg.attachmentName.value = wrkFld1;};";
	print "\n if (document.Chg.attachDesc.value ==\"\") {document.Chg.attachDesc.value = wrkFld3;};";
	print "\n document.Chg.fileSave.value = wrkFld2;}";
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
?>
	var iChars = "!@#$%^&*()+=[]\\\'\;\,\/{}\|\"\:<>\?\~"; 
<?php
	if ($maintenanceCode == "A" && document.Chg.directLink.value =="") {
		print "\n for (var i = 0; i < document.Chg.attachmentName.value.length; i++) { ";
		print "\n if (iChars.indexOf(document.Chg.attachmentName.value.charAt(i)) != -1) ";
		print "\n {alert(\"Special characters are not valid for Attachment Name\"); return false;}} ";
		print "\n if (document.Chg.fileName.value ==\"\" ||";
		print "\n     document.Chg.attachmentName.value ==\"\")";
	} else {print "\n if (document.Chg.attachDesc.value ==\"\")";}
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n return true;";
	print "\n }";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr >";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	print "\n <td class=\"content\">";
	print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td><td>";
	require 'MaintainTopNoTable.php';
	print "\n </td></tr>";
	print "\n <tr><td><h2>{$attachForDesc}</h2></td></tr>";
	print "\n </table>";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
			$focusField= "fileName";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_ATDESC=DecatErr_Field("@@desc", "attachDesc");
			$Err_ATATNL=DecatErr_Field("@@atnl", "fileName");
			$Err_ATATNS=DecatErr_Field("@@atns", "attachmentName");
			$Err_ATDIRL=DecatErr_Field("@@dirl", "directLink");
			$Err_ATPRIV=DecatErr_Field("@@priv", "attachPrivate");
			$Err_ATPRIV=DecatErr_Field("@@body", "bodyFile");
		}

		$row['ATDESC']=Decat_Field("@@desc", $edtVar);
		$row['ATATNL']=Decat_Field("@@flsv", $edtVar);
		$row['ATATNS']=Decat_Field("@@atns", $edtVar);
		$row['ATDIRL']=Decat_Field("@@dirl", $edtVar);
		$row['ATPRIV']=Decat_Field("@@priv", $edtVar);
		$row['ATREPL']=Decat_Field("@@repl", $edtVar);
		$row['ATBODY']=Decat_Field("@@body", $edtVar);

	} else {
		$focusField= "attachDesc";
		$row['ATDESC']=$attachDesc;
		$row['ATATNL']=$attachLongName;
		$row['ATATNS']=$attachShortName;
		$row['ATDIRL']=$directLink;
		$row['ATPRIV']=$attachPrivate;
		$row['ATBODY']=$bodyFile;
		$row['ATREPL']="Y";
	}

	if ($maintenanceCode == "A") {
		print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ENCTYPE=\"multipart/form-data\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode($maintenanceCode) . "\">";
	} else {
		print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode($maintenanceCode) . "\">";
	}
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_ATATNL);
	print "\n <tr> ";
	print "\n <td class=\"dsphdr\">File</td> ";
	if ($maintenanceCode == "A") {
		print "\n <td><input type=\"FILE\" name=\"fileName\" value=\"{$row['ATATNL']}\" size=\"50\" maxlength=\"256\" onChange=\"javascript:chgToFile(this.form.fileName.value);\" >{$reqFieldChar}</td>";
		print "\n <td><input type=\"hidden\" name=\"fileSave\"><td>";
	} else {
		print "\n <td class=\"inputalph\">{$row['ATATNL']}</td>";
	}
	print "\n </tr>";
	DspErrMsg($Err_ATATNL);

	$textOvr=SetTextOvr($Err_ATATNS);
	print "\n <tr>";
	print "\n <td class=\"dsphdr\">Attachment Name</td>";
	if ($maintenanceCode == "A") {
		print "\n <td class=\"inputalph\"><input name=\"attachmentName\" type=\"text\" value=\"{$row['ATATNS']}\" size=\"50\" maxlength=\"100\">{$reqFieldChar}</td>";
	} else {
		print "\n <td class=\"inputalph\"><input name=\"attachmentName\" type=\"hidden\" value=\"{$row['ATATNS']}\">{$row['ATATNS']}</td>";
	}
	print "\n </tr>";
	DspErrMsg($Err_ATATNS);

	$textOvr=SetTextOvr($Err_ATDESC);
	print "\n <tr>";
	print "\n <td class=\"dsphdr\">Description</td>";
	print "\n <td class=\"inputalph\"><input name=\"attachDesc\" type=\"text\" value=\"{$row['ATDESC']}\" size=\"50\" maxlength=\"100\"></td>";
	print "\n </tr>";
	DspErrMsg($Err_ATDESC);

	if ($attachFolderU == "DOCUMENT") {
		$bodyChecked=Field_Checked($row['ATBODY'], "Y");
		$textOvr=SetTextOvr($Err_ATBODY);
		print "\n <tr>";
		print "\n <td class=\"dsphdr\">E-mail Body File</td>";
		print "\n <td class=\"inputalph\"><input name=\"bodyFile\" type=\"checkbox\" value=\"Y\" {$bodyChecked}></td>";
		print "\n </tr>";
		DspErrMsg($Err_ATBODY);
	}

	if ($attachFolderU != "DOCUMENT" && ($maintenanceCode == "A" || $userProfile == $attachUser || $admin == 'Y')) {
		$directChecked=Field_Checked($row['ATDIRL'], "Y");
		$textOvr=SetTextOvr($Err_ATDIRL);
		print "\n <tr>";
		print "\n <td class=\"dsphdr\">Direct Link</td>";
		if ($maintenanceCode == "A") {
		    print "\n <td class=\"inputalph\"><input name=\"directLink\" type=\"checkbox\" value=\"Y\" {$directChecked}></td>";}
		else {
			print "\n <td class=\"inputalph\"><input name=\"directLinkdisabled\" type=\"checkbox\" {$directChecked} disabled></td>";
		    print "\n <tr><td><input name=\"directLink\" type=\"hidden\" value=\"{$row['ATDIRL']}\"></td></tr>";
		}
		print "\n </tr>";
		DspErrMsg($Err_ATDIRL);
	    
		$pubChecked=Field_Checked($row['ATPRIV'], "Y");
		$textOvr=SetTextOvr($Err_ATPRIV);
		print "\n <tr>";
		print "\n <td class=\"dsphdr\">Private</td>";
		print "\n <td class=\"inputalph\"><input name=\"attachPrivate\" type=\"checkbox\" value=\"Y\" {$pubChecked}></td>";
		print "\n </tr>";
		DspErrMsg($Err_ATPRIV);
	} else {
		print "\n <tr><td><input name=\"directLink\" type=\"hidden\" value=\"{$row['ATDIRL']}\"><input name=\"attachPrivate\" type=\"hidden\" value=\"{$row['ATPRIV']}\"></td></tr>";
	}

	if ($maintenanceCode == "A") {
		$replaceChecked=Field_Checked($row['ATREPL'], "Y");
		print "\n <tr>";
		print "\n <td class=\"dsphdr\">Replace Existing File</td>";
		print "\n <td class=\"inputalph\"><input name=\"replaceFile\" type=\"checkbox\" value=\"Y\" {$replaceChecked}></td>";
		print "\n </tr>";
	}
	print "\n </table>";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.$focusField.focus();";
	print "\n </script>";
	print "\n </form>";
	require_once 'MaintainBottom.php';
	print $hrTagAttr;

	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require ($searchTrailer);
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	$fileWrite = "";
	$fileError = "N";
	if (isset($_POST['bodyFile']))   {$bodyFile   = $_POST['bodyFile'];} else {$bodyFile = "";}
	$attachFolderU = trim(strtoupper($attachFolder));
	$attachShortName = trim($_POST['attachmentName']);

	if ($maintenanceCode == "A" && $_POST['directLink'] != "Y") {
		if($_FILES["fileName"]["size"]  == 0) {$fileError = "S";}

		if((!empty($_FILES["fileName"])) && ($_FILES['fileName']['error'] == 0) && $fileError == "N") {
			$returnUser = RetValue("ATFOLD='$attachFolderU' and ATVKEY='$attachVarKey' and ATATNS='$attachShortName'", "SYD2WA", "ATUSER");
			if ($returnUser == "" || ($_POST['replaceFile'] == "Y" && ($returnUser == $userProfile || $admin == "Y"))) {
				$fileName = basename($_FILES['fileName']['name']);
				$ext = substr($fileName, strrpos($fileName, '.') + 1);
					$attachPath = "{$homePath}{$uploadDirectory}{$dataBaseID}/";
					if (!file_exists("$attachPath")) {exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$attachPath\")'");}
					$acaPath = "{$attachPath}{$attachFolder}/";
					if (!file_exists("$acaPath"))   {exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$acaPath\")'");}
					$sub2Path = "{$acaPath}{$attachVarKey}/";
					if (!file_exists("$sub2Path"))   {exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$sub2Path\")'");}
					$longName = "{$uploadDirectory}{$dataBaseID}/{$attachFolder}/{$attachVarKey}/{$_POST['attachmentName']}";
					$newname = $sub2Path . $_POST['attachmentName'];
					// Check if the table with the same name is already exists on the server
					if (file_exists($newname) && $_POST['replaceFile'] == "Y") {unlink($newname);}
					// Attempt to move the uploaded table to it's new place
					if (!file_exists($newname) && (move_uploaded_file($_FILES['fileName']['tmp_name'],$newname))) {$fileWrite = "Y";}
			}
		}
	}
	if ($maintenanceCode == "A" && $_POST['directLink'] == "Y") {
	    $longName = $attachShortName;
	    $attachShortName = preg_replace('/^.+[\\\\\\/]/', '', $longName);
	    $returnUser = RetValue("ATFOLD='$attachFolderU' and ATVKEY='$attachVarKey' and ATATNS='$attachShortName'", "SYD2WA", "ATUSER");
	    if ($returnUser != "") {
	        $attachShortName = $longName;
	        $fileError = "Y";
	    }
	}
	$edtVar= "";
	Concat_Field("@@fold", $attachFolderU);
	Concat_Field("@@vkey", $attachVarKey);
	Concat_Field("@@desc", $_POST['attachDesc']);
	Concat_Field("@@atnl", $longName);
	Concat_Field("@@atns", $attachShortName);
	Concat_Field("@@user", $userProfile);
	Concat_Field("@@body", $bodyFile);
	Concat_Field("@@dirl", $_POST['directLink']);
	Concat_Field("@@priv", $_POST['attachPrivate']);
	Concat_Field("@@repl", $_POST['replaceFile']);
	Concat_Field("@@prg1", $attachPrg1);
	Concat_Field("@@prg2", $attachPrg2);
	Concat_Field("@@prg3", $attachPrg3);
	Concat_Field("@@prg4", $attachPrg4);
	Concat_Field("@@prg5", $attachPrg5);
	$edtVar .= "}{";

	if ($maintenanceCode == "C" || ($fileWrite) && $fileError == "N" || $_POST['directLink'] == "Y" && $fileError != "Y") {
		$returnValue=Maintain_Edit("HSYATM_W", $profileHandle, $dataBaseID, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
		$maintenanceCode=$returnValue['maintenanceCode'];
		$errFound       =$returnValue['errFound'];
		$edtVar         =$returnValue['edtVar'];
		$errVar         =$returnValue['errVar'];
	} else {
		$errFound = "Y";
		if ($fileError == "S") {
			Concat_Error("@@atnl", "File too large");
		} elseif ($fileError == "P") {
			Concat_Error("@@atnl", "File not found ");
		} else {
			Concat_Error("@@atnl", "{$_POST['fileSave']}");
			if ($returnUser == "" || trim($returnUser) == trim($userProfile)) {
				Concat_Error("@@atns", "Attachment Name already exists");
			} else {
				Concat_Error("@@atns", "Attachment Name already added by another user");
			}
		}
	}
	$errVar .= "}{";

	if ($errFound == "Y") {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=" . urlencode($maintenanceCode) . "&amp;errFound=" . urlencode($errFound) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . " \"> ";
	} else {
		print "\n <script TYPE=\"text/javascript\">";
		if ($_SESSION['refreshOpener']) {
            print "\n   opener.location.href=opener.location.href;";
        }
		print "\n   window.close();";
		print "\n </script>";
	}
}
?>