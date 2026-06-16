<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$plateID = (isset($_GET['plateID']))           ? $_GET['plateID']           : "";

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once "InventoryControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "&nbsp; License Plate";
$scriptName     = "LicPlateMenu.php";
$scriptVarBase  = "{$genericVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$_SESSION[$retURL] = $baseURL;
$maintainVar    = "{$genericVarBase}&amp;tag=MAINTAIN";
$maintenanceCode= "";
$programName    = "LICPLATE";

if ($tag == "Edit_Data") {
	$edtVar= "";
	Concat_Field("@@plid", $_POST['plateID']);
	$_POST['stkLoc'] = strtoupper($_POST['stkLoc']);
	Concat_Field("@@sloc", $_POST['stkLoc']);
	Concat_Field("@@stkl", $CISTKL);
	$_POST['itemNumber'] = strtoupper($_POST['itemNumber']);
	Concat_Field("@@item", $_POST['itemNumber']);
	Concat_Field("@@optn", $_POST['option']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HIVLPM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "") {
		if ($_POST['option'] == "V") {
			if ($_POST['plateID'] != "") {
				print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=435&amp;nMenu=Y&amp;fKey1=LHID&amp;fVal1=" . urlencode(trim($_POST['plateID'])) . "&amp;maintenanceCode=V\"> ";
			} elseif ($_POST['stkLoc'] != "") {
				print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=487&amp;nMenu=Y&amp;fKey1=ISLOC&amp;fVal1=" . urlencode(trim($_POST['stkLoc'])) . "&amp;maintenanceCode=V\"> ";
			} elseif ($_POST['itemNumber'] != "") {
				print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=444&amp;nMenu=Y&amp;fKey1=ISITEM&amp;fVal1=" . urlencode(trim($_POST['itemNumber'])) . "&amp;maintenanceCode=V\"> ";
			}
		} else {
				print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}LicPlateMaintain.php{$maintainVar}&amp;plateID=" . urlencode(trim($_POST['plateID'])) . "&amp;maintenanceCode="  . urlencode(trim($_POST['option'])) . "\"> ";
		}
		exit;
	}
}

require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
print "\n function validate(chgForm) {";
print "\n   if (document.Chg.option.value ==\"V\" && ";
print "\n      (document.Chg.plateID.value ==\"\" ";
print "\n       && document.Chg.stkLoc.value ==\"\" ";
print "\n       && document.Chg.itemNumber.value ==\"\") ";
print "\n   ) {alert(\"Enter License Plate, Location or Item Number\"); return false;} ";
print "\n   return true;";
print "\n }";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";

print "\n <h1>{$page_title}</h1>";
require_once 'ConfMessageDisplay.php';
$licPlate_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
print "\n <div>
            <ul class=\"toolbarLP\">";
           	  if ($licPlate_OPT['sec_01']=="Y") {print "\n <li class=\"optionLP\"><a href=\"{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=434&amp;nMenu=Y\">Receiving</a></li>";}
              if ($licPlate_OPT['sec_02']=="Y") {print "\n <li class=\"optionLP\"><a onClick=\"javascript:document.getElementById('option').value='T';\" href=\"javascript:check(document.Chg)\">Transfer</a></li>";}
              if ($licPlate_OPT['sec_03']=="Y") {print "\n <li class=\"optionLP\"><a onClick=\"javascript:document.getElementById('option').value='F';\" href=\"javascript:check(document.Chg)\">Finalize</a></li>";}
              if ($licPlate_OPT['sec_04']=="Y") {print "\n <li class=\"optionLP\"><a onClick=\"javascript:document.getElementById('option').value='R';\" href=\"javascript:check(document.Chg)\">Replace</a></li>";}
              if ($licPlate_OPT['sec_05']=="Y") {print "\n <li class=\"optionLP\"><a onClick=\"javascript:document.getElementById('option').value='D';\" href=\"javascript:check(document.Chg)\">Delete</a></li>";}
print "\n   </ul>
	      </div>";
print "\n <div style=\"clear:both\"></div><br>";
print "\n <table $contentTable><tr><td>";
if ($errFound != ""){
	$focusField= "";
	$Err_LHID=DecatErr_Field("@@plid", "plateID");
	$Err_LHSLID=DecatErr_Field("@@sloc", "stkLoc");
	$Err_LHITEM=DecatErr_Field("@@item", "itemNumber");
	$errFound= "";

	$plateID=Decat_Field("@@plid", $edtVar);
	$stkLoc=Decat_Field("@@sloc", $edtVar);
	$itemNumber=Decat_Field("@@item", $edtVar);
} 
if ($focusField == ""){$focusField= "plateID";}
 
$textOvr=SetTextOvr($Err_LHID);
print "\n <tr><td class=\"dsphdr\"><span $textOvr>&nbsp; License Plate</span></td>";
print "\n  <td class=\"inputnmbr\"><input type=\"text\" name=\"plateID\" value=\"$plateID\" size=\"70\" maxlength=\"100\"></td></tr>";
DspErrMsg($Err_LHID);

$textOvr=SetTextOvr($Err_LHSLID);
print "\n <tr><td class=\"dsphdr\"><span $textOvr>&nbsp; Location</span></td>";
print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"stkLoc\" value=\"$stkLoc\" size=\"70\" maxlength=\"100\"></td>
		      <td><div><ul class=\"toolbarLP\"><li class=\"optionLP\"><a onClick=\"javascript:document.getElementById('option').value='V';\" href=\"javascript:check(document.Chg)\">View</a></li></ul></div></td></tr>";
DspErrMsg($Err_LHSLID);
	
$textOvr=SetTextOvr($Err_LHITEM);
print "\n <tr><td class=\"dsphdr\"><span $textOvr>&nbsp; Item Number</span></td>";
print "\n  <td class=\"inputnmbr\"><input type=\"text\" name=\"itemNumber\" value=\"$itemNumber\" size=\"70\" maxlength=\"15\"></td></tr>";
DspErrMsg($Err_LHITEM);

print "\n  <tr><td><input type=\"hidden\" id=\"option\" name=\"option\" value=\"\"></td></tr>";

print "\n </table></form>";
print "\n <script TYPE=\"text/javascript\">";
print "\n document.Chg.$focusField.focus();";
print "\n </script>";
print "</body> </html>";
?>
