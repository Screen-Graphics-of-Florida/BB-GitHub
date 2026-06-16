<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$plateID            = (isset($_GET['plateID']))           ? $_GET['plateID']           : "";

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once "InventoryControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

if ($maintenanceCode == "T") {$pageHdr = "Transfer";}
elseif ($maintenanceCode == "F") {$pageHdr = "Finalize";}
elseif ($maintenanceCode == "R") {$pageHdr = "Replace";}
elseif ($maintenanceCode == "D") {$pageHdr = "Delete";}
$page_title     = "$pageHdr";
$scriptName     = "LicPlateMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;maintenanceCode=" . urlencode(trim($maintenanceCode));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=434";}

if ($tag == "Edit_Data") {
	$edtVar= "";
	Concat_Field("@@plid", $_POST['plateID']);
	Concat_Field("@@nwid", $_POST['newID']);
	$_POST['stkLoc'] = strtoupper($_POST['stkLoc']);
	Concat_Field("@@sloc", $_POST['stkLoc']);
	Concat_Field("@@whs@", $_POST['whsNumber']);
	Concat_Field("@@stkl", $CISTKL);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HIVLPM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "") {
		$confMessage="Confirm {$pageHdr} of $_POST[plateID]";
		if ($maintenanceCode == "F" && $licPlateFinalizeReturn == "Y") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
			exit;
		}
		if ($maintenanceCode != "F" && $maintenanceCode != "T") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}LicPlateMenu.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
			exit;
		}
	}
}

require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
require_once 'NumEdit.php';

print "\n function validate(chgForm) {";
print "\n   if (document.Chg.plateID.value ==\"\" ";
if ($maintenanceCode == "R") {print "\n  || document.Chg.newID.value ==\"\" "; }
if ($CISTKL == "Y" && $maintenanceCode != "R" && $maintenanceCode != "D") {
	print "\n  || document.Chg.stkLoc.value ==\"\" ";
}
print "\n   ) {alert(\"$reqFieldError\"); return false;} ";

if ($maintenanceCode != "R" && $maintenanceCode != "D") {
	print "\n if (editNum(document.Chg.whsNumber, 3, 0) ) ";
}
print "\n return true;";
print "\n }";

print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

if ($errFound != "") {
	$focusField= "";
	$Err_LHWHS=DecatErr_Field("@@whs@", "whsNumber");
	$Err_LHID=DecatErr_Field("@@plid", "plateID");
	$Err_NEWID=DecatErr_Field("@@nwid", "newID");
	$Err_LHSLID=DecatErr_Field("@@sloc", "stkLoc");
	$errFound= "";

	$whsNumber=Decat_Field("@@whs@", $edtVar);
	$plateID=Decat_Field("@@plid", $edtVar);
	$newID=Decat_Field("@@nwid", $edtVar);
	$stkLoc=Decat_Field("@@sloc", $edtVar);
} else {
	$focusField= "plateID";
}
print "\n <h1>$page_title</h1> ";
require_once 'RequiredField.php';
require_once 'ConfMessageDisplay.php';

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
print "\n <table $contentTable>";
if ($maintenanceCode == "R") {$licTitle = "Old License Plate";} else {$licTitle = "License Plate";}
Build_Fld_Entry("$licTitle","plateID","inputalph","","LHID",$plateID,$Err_LHID,"70","100","Y","","");
if ($maintenanceCode == "R") {
	Build_Fld_Entry("New License Plate","newID","inputalph","","LHID",$newID,$Err_NEWID,"70","100","Y","","");
} elseif ($maintenanceCode != "D") {
	if ($CISTKL == "Y") {Build_Fld_Entry("Location","stkLoc","inputalph","","LHSLID",$stkLoc,$Err_LHSLID,"20","15","Y","","");}
	Build_Fld_Entry("Warehouse","whsNumber","inputnmbr","","LTWH",$whsNumber,$Err_LHWHS,"3","3","","","");
}
print "\n </table> ";
print "\n <div>
                <ul class=\"toolbarLP\">
                  <li class=\"optionLP\"><a href=\"javascript:check(document.Chg)\">Accept</a></li>
                  <li class=\"optionLP\"><a href=\"{$homeURL}{$phpPath}LicPlateMenu.php{$scriptVarBase}\">Back</a></li>
		        </ul>
		      </div>";

print "\n <div style=\"clear:both\"></div>";
print "\n </form>";
print "\n <script TYPE=\"text/javascript\">";
print "\n document.Chg.$focusField.focus();";
print "\n </script>";
print "\n </td> </tr> </table>";
print "</body> </html>";
?>