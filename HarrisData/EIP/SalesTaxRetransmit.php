<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$orderControlNumber = (isset($_GET['orderControlNumber'])) ? $_GET['orderControlNumber'] : null;
$maintenanceCode    = (isset($_GET['maintenanceCode']))    ? $_GET['maintenanceCode'] : null;
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$page_title = "Sales Tax Retransmit";
$scriptName = "SalesTaxRetransmit.php";
$scriptVarBase = "{$genericVarBase}";
$backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=532";
$programName    = "AVATAX";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
    require_once 'ProgSecurityError.php';
    exit;
}
if ($tag == "Edit_Data") {
    $edtVar = "";
    Concat_Field("@@actl", $orderControlNumber);
    $edtVar .= "}{";
    $returnValue = Maintain_Edit("HOETXRT_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar);
    $errFound = $returnValue['errFound'];
    if ($errFound != "") {
        $maintenanceCode = "E";
    }
    if ($maintenanceCode == 'C') {
        $confMessage = Format_ConfMsg_Desc($maintenanceCode, $orderControlNumber, "", "in AvaTax", "", "", "");
    }
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
    exit();
}

?>
