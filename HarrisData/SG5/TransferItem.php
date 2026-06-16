<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound = $_GET['errFound'];
$fLot     = $_GET['fLot'];
$fItem    = $_GET['fItem'];
$fWhs     = $_GET['fWhs'];
$fLoc     = $_GET['fLoc'];
$fSid     = $_GET['fSid'];
$fQty     = $_GET['fQty'];
$maintenanceCode = 'X';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once "InventoryControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Transfer Item";
$scriptName     = "TransferItem.php";
$scriptVarBase  = "{$genericVarBase}&amp;fLot=" . urlencode(trim($fLot)) . "&amp;fItem=" . urlencode(trim($fItem)) . "&amp;fWhs=" . urlencode(trim($fWhs)) . "&amp;fLoc=" . urlencode(trim($fLoc)) . "&amp;fSid=" . urlencode(trim($fSid)) . "&amp;fQty=" . urlencode(trim($fQty));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=444";}

if ($tag == "Edit_Data") {
	$edtVar= "";
	Concat_Field("@@item", $fItem);
	Concat_Field("@@whs@", $fWhs);
	Concat_Field("@@lot@", $fLot);
	Concat_Field("@@qty@", $_POST['addQty']);
	$_POST['stkLoc'] = strtoupper($_POST['stkLoc']);
	Concat_Field("@@sloc", $_POST['stkLoc']);
	Concat_Field("@@stkl", $CISTKL);
	Concat_Field("@@floc", $fLoc);
	Concat_Field("@@fsid", $fSid);
	Concat_Field("@@fqty", $fQty);
	Concat_Field("@@twhs", $_POST['toWhs']);
	$edtVar .= "}{";
	
	$returnValue=Maintain_Edit("HIVLPM_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "") {
		$confMessage = "Confirm Transfer";
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		exit;
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
print "\n   if (document.Chg.addQty.value ==\"\" ";
if ($CISTKL == "Y") {print "\n  || document.Chg.stkLoc.value ==\"\" "; }
print "\n   ) {alert(\"$reqFieldError\"); return false;} ";

print "\n   if (document.Chg.addQty.value > $fQty)";
print "\n      {alert(\"Quantity cannot be greater than Quantity Available\"); return false;} ";
print "\n   if (editZero(document.Chg.addQty, 9, 4) ) ";
print "\n return true;";
print "\n }";

print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
$itemDesc=RetValue("IMITEM='$fItem'", "HDIMST", "IMIMDS");

if ($errFound != "") {
	$focusField= "";
	$edtVar=EdtVarErr($profileHandle, $edtVar);
	$errVar=ErrVarErr($profileHandle, $errVar);

	$Err_ADDQTY=DecatErr_Field("@@qty@", "addQty");
	$Err_TOSID=DecatErr_Field("@@sloc", "stkLoc");
	$Err_TOSID=DecatErr_Field("@@sloc", "stkLoc");
	$errFound= "";

	$addQty=Decat_Field("@@qty@", $edtVar);
	$stkLoc=Decat_Field("@@sloc", $edtVar);
	$toWhs=Decat_Field("@@twhs", $edtVar);
	
} else {
	$focusField= "addQty";
}
print "\n <h1>$page_title</h1> ";
require_once 'RequiredField.php';

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\" onSubmit=\"return false;\">";
print "\n <table $contentTable>";
print "\n <tr><td class=\"dsphdr\">Item Number</td><td class=\"dspalph\">$fItem &nbsp; $itemDesc</td><td class=\"dsphdr\">&nbsp; Whs<td class=\"dspalph\">$fWhs</td>";
print "\n     <td><input type=\"hidden\" name=\"moreItems\" value=\"\"></td></tr>";
if ($CILTUS == "Y" && $fLot != "") {print "\n <tr><td class=\"dsphdr\">Lot Number</td><td class=\"dspalph\">$fLot</td></tr>";}
print "\n <tr><td class=\"dsphdr\">Location</td><td class=\"dspalph\">$fLoc</td></tr>";

print "\n <tr><td class=\"dsphdr\"><span $textOvr>Quantity</span></td> ";
Build_Fld_Entry("Quantity","addQty","inputnmbr","","ADDQTY",$addQty,$Err_ADDQTY,"15","15","Y","","Y");
$f_fQty=Format_Nbr($fQty, ($qtyNbrDec), ($qtyEditCode), "", "", "");
print "\n <td class=\"dsphdr\">&nbsp; Quantity Available<td class=\"dspalph\">$f_fQty</td></tr>";

if ($CISTKL == "Y") {Build_Fld_Entry("To Location","stkLoc","inputalph","","TOSID","$stkLoc",$Err_TOSID,"20","15","Y","","");}
Build_Fld_Entry("To Whs","toWhs","inputalph","","TOWHS",$toWhs,$Err_TOWHS,"5","3","","","");

print "\n </table> ";
print "\n <div>
            <ul class=\"toolbarLP\">
              <li class=\"optionLP\"><a href=\"javascript:check(document.Chg)\">Accept</a></li>
              <li class=\"optionLP\"><a href=\"$backURL\">Back</a></li>";
print "\n </ul> </div>";

print "\n <div style=\"clear:both\"></div>";

print "\n <script TYPE=\"text/javascript\">";
print "\n document.Chg.$focusField.focus();";
print "\n </script>";
print "\n </form>";
print "\n </td> </tr> </table>";
print "</body> </html>";
?>