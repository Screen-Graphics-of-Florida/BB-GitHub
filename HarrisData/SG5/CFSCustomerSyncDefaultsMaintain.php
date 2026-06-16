<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$backHome           = $_GET['backHome'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "CFS Customer Sync Defaults Maintenance";
$scriptName     = "CFSCustomerSyncDefaultsMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HHDCSD_E";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "MAINTAIN") {
	$RecCount=RetValue("RRN(HDCFSD)=1", "HDCFSD", "Char(Count(*))");
	$maintenanceCode="C";

	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'CheckReqFieldJava.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'SaveCurrentURL.php';
	require_once 'UpperCase.php';

	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.country.value ==\"\" ";
	print "\n  || document.Chg.classCode.value ==\"\" ";
	print "\n  || document.Chg.location.value ==\"\" ";
	print "\n  || document.Chg.termsCode.value ==\"\" ";
	print "\n  || document.Chg.region.value ==\"\" ";
	print "\n  || document.Chg.warehouseNumber.value ==\"\" ";
	print "\n  || document.Chg.shipVia.value ==\"\" ";
	print "\n  || document.Chg.fobCode.value ==\"\" ";
	print "\n  || document.Chg.taxCode.value ==\"\"  ";
	print "\n  || document.Chg.billingCode.value ==\"\"  ";
	print "\n  || document.Chg.invoiceMethod.value ==\"\"  ";
	print "\n  || document.Chg.printDetailOrSummary.value ==\"\"  ";
	print "\n ) {alert(\"$reqFieldError\"); return false;} ";

	print "\n if (editNum(document.Chg.location, 3, 0) && ";
	print "\n     editNum(document.Chg.warehouseNumber, 3, 0) ";
	print "\n ) return true; ";
	print "\n } ";
	print "\n  function confirmDelete() {return confirm(\"$delRecordConf\")} ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARCONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($RecCount == 0) {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From HDCFSD ";
		$stmtSQL .= " Where RRN(HDCFSD)=1 ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$HHDCSD_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$HHDCSD_OPT['sec_01'];
	$sec_02=$HHDCSD_OPT['sec_02'];
	$sec_03="N";
	$sec_04="N";
	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField= "ReclaimResourceLevel";
	if ($errFound != "" || $RecCount==0) {
		if ($errFound == "" && $RecCount==0) {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_CDCTRY=DecatErr_Field("@@ctry", "country");
            $Err_CDCCLS=DecatErr_Field("@@ccls", "classCode");
            $Err_CDLOC =DecatErr_Field("@@loc@", "location");
            $Err_CDCTRM=DecatErr_Field("@@ctrm", "termsCode");
            $Err_CDCSTC=DecatErr_Field("@@cstc", "printStatement");
            $Err_CDCSCC=DecatErr_Field("@@cscc", "serviceCharge");
            $Err_CDALSM=DecatErr_Field("@@alsm", "alternateSalesman");
            $Err_CDCRGN=DecatErr_Field("@@crgn", "region");
            $Err_CDIBPK=DecatErr_Field("@@ibpk", "invByPackList");
            $Err_CDSTRQ=DecatErr_Field("@@strq", "storeRequired");
            $Err_CDORTY=DecatErr_Field("@@orty", "dftOrderType");
            $Err_CDWH  =DecatErr_Field("@@whs@", "warehouseNumber");
            $Err_CDBOAL=DecatErr_Field("@@boal", "allowBackorders");
            $Err_CDSV  =DecatErr_Field("@@sv@@", "shipVia");
            $Err_CDFOBC=DecatErr_Field("@@fobc", "fobCode");
            $Err_CDCTXC=DecatErr_Field("@@ctxc", "taxCode");
            $Err_CDBCDE=DecatErr_Field("@@bcde", "billingCode");
            $Err_CDIMTH=DecatErr_Field("@@imth", "invoiceMethod");
            $Err_CDIDOS=DecatErr_Field("@@idos", "printDetailOrSummary");
            $Err_CDRREF=DecatErr_Field("@@rref", "requireReferenceNumber");
            $Err_CDACCI=DecatErr_Field("@@acci", "autoCreateCustomerItem");
            $Err_CDAOVS=DecatErr_Field("@@aovs", "allowOverShipments");
			$Err_CDTSTP=DecatErr_Field("@@tstp", "originalTimeStamp");
		}
        $row['CDCTRY']=Decat_Field("@@ctry", $edtVar);
        $row['CDCCLS']=Decat_Field("@@ccls", $edtVar);
        $row['CDLOC'] =Decat_Field("@@loc@", $edtVar);
        $row['CDCTRM']=Decat_Field("@@ctrm", $edtVar);
        $row['CDCSTC']=Decat_Field("@@cstc", $edtVar);
        $row['CDCSCC']=Decat_Field("@@cscc", $edtVar);
        $row['CDALSM']=Decat_Field("@@alsm", $edtVar);
        $row['CDCRGN']=Decat_Field("@@crgn", $edtVar);
        $row['CDIBPK']=Decat_Field("@@ibpk", $edtVar);
        $row['CDSTRQ']=Decat_Field("@@strq", $edtVar);
        $row['CDORTY']=Decat_Field("@@orty", $edtVar);
        $row['CDWH']  =Decat_Field("@@whs@", $edtVar);
        $row['CDBOAL']=Decat_Field("@@boal", $edtVar);
        $row['CDSV']  =Decat_Field("@@sv@@", $edtVar);
        $row['CDFOBC']=Decat_Field("@@fobc", $edtVar);
        $row['CDCTXC']=Decat_Field("@@ctxc", $edtVar);
        $row['CDBCDE']=Decat_Field("@@bcde", $edtVar);
        $row['CDIMTH']=Decat_Field("@@imth", $edtVar);
        $row['CDIDOS']=Decat_Field("@@idos", $edtVar);
        $row['CDRREF']=Decat_Field("@@rref", $edtVar);
        $row['CDACCI']=Decat_Field("@@acci", $edtVar);
        $row['CDAOVS']=Decat_Field("@@aovs", $edtVar);
		$row['CDTSTP']=Decat_Field("@@tstp", $edtVar);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_CDTSTP);
	DspErrMsg($Err_CDTSTP);
	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['CDTSTP']) . "\"></td></tr> ";
	
	$fieldDesc=RetValue("CNCTCD='$row[CDCTRY]'", "HDCTRY", "CNCDES");
	$textOvr=SetTextOvr($Err_CDCTRY);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Country</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"country\" value=\"" . rtrim($row['CDCTRY']) . "\" size=\"7\" maxlength=\"3\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CountrySearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=country&amp;fldDesc=countryDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"countryDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDCTRY);

	$fieldDesc=RetValue("CCCCLS='$row[CDCCLS]'", "HDCCLS", "CCCCDS");
	$textOvr=SetTextOvr($Err_CDCCLS);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Class Code</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"classCode\" value=\"" . rtrim($row['CDCCLS']) . "\" size=\"7\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CustomerClassSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=classCode&amp;fldDesc=classCodeDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"classCodeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDCCLS);

	$row['CDLOC']=Default_Zero($row['CDLOC']);
	$fieldDesc=RetValue("LOLOC#=$row[CDLOC]", "HDLCTN", "LOLNA1");
	$textOvr=SetTextOvr($Err_CDLOC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Location</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"location\" value=\"" . rtrim($row['CDLOC']) . "\" size=\"7\" maxlength=\"3\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=location&amp;fldDesc=locationDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"locationDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDLOC);
	
	$fieldDesc=RetValue("TMCTRM='$row[CDCTRM]'", "HDTRMS", "TMCTDS");
	$textOvr=SetTextOvr($Err_CDCTRM);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Terms Code</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"termsCode\" value=\"" . rtrim($row['CDCTRM']) . "\" size=\"7\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=termsCode&amp;fldDesc=termsCodeDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"termsCodeDesc\">$fieldDesc</span></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CDCTRM);


	$row['CDALSM']=Default_Zero($row['CDALSM']);
	$fieldDesc=RetValue("SMSLSM=$row[CDALSM]", "HDSLSM", "SMSNA1");
	$textOvr=SetTextOvr($Err_CDALSM);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Alternate Salesman</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"alternateSalesman\" value=\"" . rtrim($row['CDALSM']) . "\" size=\"7\" maxlength=\"3\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=alternateSalesman&amp;fldDesc=alternateSalesmanName\" onclick=\"$searchWinVar\">&nbsp;  $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"alternateSalesmanName\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDALSM);
	
	$fieldDesc=RetValue("RGCRGN='$row[CDCRGN]'", "HDCRGN", "RGCRDS");
	$textOvr=SetTextOvr($Err_CDCRGN);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Region</span></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"text\" name=\"region\" value=\"" . rtrim($row['CDCRGN']) . "\" size=\"7\" maxlength=\"5\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}RegionSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=region&amp;fldDesc=regionDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"regionDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDCRGN);

	$row['CDWHS']=Default_Zero($row['CDWH']);
	$fieldDesc=RetValue("WHWHS=$row[CDWH]", "HDWHSM", "WHWHNM");
	$textOvr=SetTextOvr($Err_CDWH);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Warehouse</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"warehouseNumber\" value=\"" . rtrim($row['CDWH']) . "\" size=\"7\" maxlength=\"3\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=warehouseNumber&amp;fldDesc=warehouseName\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"warehouseName\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDWH);
	
	$fieldDesc=RetValue("OTAPID='OE' and OTOTCD='$row[CDORTY]'", "HDOTYP", "OTDESC");
	$textOvr=SetTextOvr($Err_CDORTY);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Default Order Type</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"dftOrderType\" value=\"" . rtrim($row['CDORTY']) . "\" size=\"7\" maxlength=\"1\"> ";
	print "\n                                     <a href=\"{$homeURL}{$cGIPath}OrderTypeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=dftOrderType&amp;fldDesc=dftOrderTypeDesc&amp;appID=OE\" onclick=\"$searchWinVar\">&nbsp;  $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"dftOrderTypeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDORTY);
	
	
	$fieldDesc=RetValue("SVSVSV='$row[CDSV]'", "HDSHPV", "SVSVDS");
	$textOvr=SetTextOvr($Err_CDSV);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Ship Via</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"shipVia\" value=\"" . rtrim($row['CDSV']) . "\" size=\"7\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}ShipViaSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=shipVia&amp;fldDesc=shipViaDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"shipViaDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDSV);

	$fieldDesc=RetValue("FBFBCD='$row[CDFOBC]'", "HDFOBM", "FBFBDS");
	$textOvr=SetTextOvr($Err_CDFOBC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>FOB Code</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"fobCode\" value=\"" . rtrim($row['CDFOBC']) . "\" size=\"7\" maxlength=\"2\"> ";
	print "\n                                     <a href=\"{$homeURL}{$cGIPath}FOBCodeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=fobCode&amp;fldDesc=fobCodeDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"fobCodeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDFOBC);

	$fieldDesc=RetValue("TCCODE='$row[CDCTXC]'", "HDTAXC", "TCDESC");
	$textOvr=SetTextOvr($Err_CDCTXC);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Tax Code</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"taxCode\" value=\"" . rtrim($row['CDCTXC']) . "\" size=\"7\" maxlength=\"1\"> ";
	print "\n                                     <a href=\"{$homeURL}{$cGIPath}TaxCodeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=taxCode&amp;fldDesc=taxCodeDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"taxCodeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDCTXC);

	$fieldDesc=RetValue("BCBCDE='$row[CDBCDE]'", "OEBCDE", "BCDESC");
	$textOvr=SetTextOvr($Err_CDBCDE);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Billing Code</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"billingCode\" value=\"" . rtrim($row['CDBCDE']) . "\" size=\"7\" maxlength=\"4\"> ";
	print "\n                                     <a href=\"{$homeURL}{$cGIPath}BillingCodeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=billingCode&amp;fldDesc=billingCodeDesc\" onclick=\"$searchWinVar\">$reqFieldChar $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"billingCodeDesc\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CDBCDE);
	
	Build_Fld_Entry("Invoice Method","invoiceMethod","inputalph","INVMETHOD","CDIMTH",$row[CDIMTH],$Err_CDIMTH,"1","1","Y","","");
	Build_Fld_Entry("Allow Backorders","allowBackorders","inputalph","BYN","CDBOAL",$row[CDBOAL],$Err_CDBOAL,"1","1","","","");
	Build_Fld_Entry("Print Detail or Summary","printDetailOrSummary","inputalph","DORS","CDIDOS",$row[CDIDOS],$Err_CDIDOS,"1","1","Y","","");
	Build_Fld_Entry("Statement","printStatement","inputalph","YORN","CRCSTC",$row[CDCSTC],$Err_CDCSTC,"1","1","Y","","");
	Build_Fld_Entry("Service Charge","serviceCharge","inputalph","YORN","CRCSCC",$row[CDCSCC],$Err_CDCSCC,"1","1","Y","","");
	Build_Fld_Entry("Store Required On Orders","storeRequired","inputalph","YORN","CDSTRQ",$row[CDSTRQ],$Err_CDSTRQ,"1","1","Y","","");
	Build_Fld_Entry("Invoice By Packing List","invByPackList","inputalph","YORN","CDIBPK",$row[CDIBPK],$Err_CDIBPK,"1","1","Y","","");
	Build_Fld_Entry("Require Reference Number","requireReferenceNumber","inputalph","YORN","CDRREF",$row[CDRREF],$Err_CDRREF,"1","1","Y","","");
	Build_Fld_Entry("Auto Create Customer/Item","autoCreateCustomerItem","inputalph","YORN","CDACCI",$row[CDACCI],$Err_CDACCI,"1","1","Y","","");
	Build_Fld_Entry("Allow Over Shipments","allowOverShipments","inputalph","YORN","CDAOVS",$row[CDAOVS],$Err_CDAOVS,"1","1","Y","","");
	
	print "\n     </table> ";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.$focusField.focus();";
	print "\n </script>";
	print "\n </form>";
	require_once 'MaintainBottom.php';
	print $hrTagAttr;
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	if (!isset($_POST['printStatement']))         {$_POST['printStatement']="N";}
	if (!isset($_POST['serviceCharge']))          {$_POST['serviceCharge']="N";}
	if (!isset($_POST['storeRequired']))          {$_POST['storeRequired']="N";}
	if (!isset($_POST['invByPackList']))          {$_POST['invByPackList']="N";}
	if (!isset($_POST['requireReferenceNumber'])) {$_POST['requireReferenceNumber']="N";}
	if (!isset($_POST['autoCreateCustomerItem'])) {$_POST['autoCreateCustomerItem']="N";}
	if (!isset($_POST['allowOverShipments']))     {$_POST['allowOverShipments']="N";}
	
	
	$edtVar= "";
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	Concat_Field("@@ctry", $_POST['country']);
    Concat_Field("@@ccls", $_POST['classCode']);
    Concat_Field("@@loc@", $_POST['location']);
    Concat_Field("@@ctrm", $_POST['termsCode']);
    Concat_Field("@@cstc", $_POST['printStatement']);
    Concat_Field("@@cscc", $_POST['serviceCharge']);
    Concat_Field("@@alsm", $_POST['alternateSalesman']);
    Concat_Field("@@crgn", $_POST['region']);
    Concat_Field("@@ibpk", $_POST['invByPackList']);
    Concat_Field("@@strq", $_POST['storeRequired']);
    Concat_Field("@@orty", $_POST['dftOrderType']);
    Concat_Field("@@whs@", $_POST['warehouseNumber']);
    Concat_Field("@@boal", $_POST['allowBackorders']);
    Concat_Field("@@sv@@", $_POST['shipVia']);
    Concat_Field("@@fobc", $_POST['fobCode']);
    Concat_Field("@@ctxc", $_POST['taxCode']);
    Concat_Field("@@bcde", $_POST['billingCode']);
    Concat_Field("@@imth", $_POST['invoiceMethod']);
    Concat_Field("@@idos", $_POST['printDetailOrSummary']);
    Concat_Field("@@rref", $_POST['requireReferenceNumber']);
    Concat_Field("@@acci", $_POST['autoCreateCustomerItem']);
    Concat_Field("@@aovs", $_POST['allowOverShipments']);
	$edtVar .= "}{";

	$returnValue=Validate_Data($userProfile, $errFound, $edtVar, $errVar);
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "CFS Customer Sync Defaults", "", "", "", "", "");
		$fileName="CFSCustomerSyncDefaults{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HHDCFS_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}

}

function Validate_Data($userProfile,$errFound,$edtVar,$errVar) {
	global $pgmLibrary, $i5Connect;
	if (is_null($errFound ))   $errFound="";
	if (is_null($edtVar ))     $edtVar="";
	if (is_null($errVar ))     $errVar="";

	$pgmCall = array(
	array("Name"=>"userProfile",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"errFound",        "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HHDCSD_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HHDCSD_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"userProfile"    =>$userProfile,
	"errFound"       =>$errFound,
	"edtVar"         =>$edtVar,
	"errVar"         =>$errVar);

	$parmOut = array(
	"userProfile"    =>"userProfile",
	"errFound"       =>"errFound",
	"edtVar"         =>"edtVar",
	"errVar"         =>"errVar");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data (HHDCSD_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['userProfile']    =$userProfile;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	return $returnValue;
}

?>