<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

  $edtVar     =  "";
  Concat_Field("@@fxlv", $_POST['DEFN']);
  Concat_Field("@@pmlv", $pricingLevel);
  $edtVar .= "}{";

  Rtv_Pricing_Definition($profileHandle, $edtVar);

  $row[$contract]=Decat_Field("@@cn@@", $edtVar);
  $row[$dollarAmt]=Decat_Field("@@dl@@", $edtVar);
  $row[$usePercent]=Decat_Field("@@up@@", $edtVar);
  $row[$bracketAmt]=Decat_Field("@@bp@@", $edtVar);
  $mdcol = "";

if (!$recCount || $reload) {Rtv_Pricing_Categories(profileHandle, edtVar, mdCol);}
?>