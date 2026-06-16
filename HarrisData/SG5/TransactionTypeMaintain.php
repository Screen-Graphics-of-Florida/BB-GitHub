<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromScript         = $_GET['fromScript'];
$fromTransType      = $_GET['fromTransType'];
$transType          = $_GET['transType'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once "InventoryControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Transaction Type Maintenance";
$scriptName     = "TransactionTypeMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromTransType=" . urlencode(trim($fromTransType)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HIVTTU_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=19";}

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
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.transTypeDesc.value ==\"\" || ";
	if ($HDPDRL>0) {print "\n     document.Chg.affectPltOrWhs.value ==\"\" || ";}
	print "\n     document.Chg.qtyDesc.value ==\"\" || ";
	print "\n     document.Chg.docDesc.value ==\"\" || ";
	print "\n     document.Chg.dteDesc.value ==\"\" || ";
	print "\n     document.Chg.updateFeedToGL.value ==\"\" || ";
	print "\n     document.Chg.updateTransHist.value ==\"\" || ";
	print "\n     document.Chg.includeListPrice.value ==\"\" || ";
	print "\n     document.Chg.includeARInvoice.value ==\"\" || ";
	print "\n     document.Chg.includeVendor.value ==\"\" || ";
	print "\n     document.Chg.includeCustomer.value ==\"\" || ";
	print "\n     document.Chg.includeReasonCode.value ==\"\" || ";
	print "\n     document.Chg.includeReasonDesc.value ==\"\" || ";
	print "\n     document.Chg.updateAvgCost.value ==\"\" || ";
	print "\n     document.Chg.updateLastPOCost.value ==\"\" || ";
	print "\n     document.Chg.updateLastPurchDate.value ==\"\" || ";
	print "\n     document.Chg.updateLastRecvDate.value ==\"\" || ";
	print "\n     document.Chg.defaultUnitOfMeasure.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNum(document.Chg.purchInvOffsetAcctNumber, 4, 0) && ";
	print "\n     editNum(document.Chg.purchInvOffsetSubNumber, 4, 0) ";
	if ($HDPDRL>0) {
		print "\n  && editNum(document.Chg.manufInvOffsetAcctNumber, 4, 0) ";
		print "\n  && editNum(document.Chg.manufInvOffsetSubNumber, 4, 0) ";
	}
	print "\n    ) ";
	print "\n return true;";
	print "\n }";


	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "IVTRANSACTIONTYPEMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select HDTTYP.*, TTLPO# as TTLPO ";
		$stmtSQL .= " From HDTTYP ";
		$stmtSQL .= " Where TTTYPE='$fromTransType' ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hivttu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hivttu_OPT['sec_01'];
	$sec_02=$hivttu_OPT['sec_02'];
	$sec_03=$hivttu_OPT['sec_03'];
	$sec_04=$hivttu_OPT['sec_04'];

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);
	if ($row[TTRESV] == "Y") {$sec_03="N";}
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$focusField= "transType";
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_TTTYPE=DecatErr_Field("@@type", "transType");
			$Err_TTDESC=DecatErr_Field("@@desc", "transTypeDesc");
			$Err_TTSHDS=DecatErr_Field("@@shds", "shortDesc");
			$Err_TTLOCN=DecatErr_Field("@@locn", "affectPltOrWhs");
			$Err_TTOHFG=DecatErr_Field("@@ohfg", "affectToOnHandQty");
			$Err_TTRCFG=DecatErr_Field("@@rcfg", "affectToReceivedQty");
			$Err_TTADFG=DecatErr_Field("@@adfg", "affectToAdjustedQty");
			$Err_TTISFG=DecatErr_Field("@@isfg", "affectToIssuedQty");
			$Err_TTRSFG=DecatErr_Field("@@rsfg", "affectToReservedQty");
			$Err_TTRVFG=DecatErr_Field("@@rvfg", "affectToRetToVendor");
			$Err_TTORFG=DecatErr_Field("@@orfg", "affectToOnHandInReceiving");
			$Err_TTHRFG=DecatErr_Field("@@hrfg", "affectToHeldInReceiving");
			$Err_TTBORL=DecatErr_Field("@@borl", "affectToBackorderRelease");
			$Err_TTWPFG=DecatErr_Field("@@wpfg", "affectToWIP");
			$Err_TTYUFG=DecatErr_Field("@@yufg", "affectToYTDUsage");
			$Err_TTOOFG=DecatErr_Field("@@oofg", "affectToOnOrderQty");
			$Err_TTSLFG=DecatErr_Field("@@slfg", "affectToSoldQty");
			$Err_TTTRFG=DecatErr_Field("@@trfg", "affectToTransferredQty");
			$Err_TTKIFG=DecatErr_Field("@@kifg", "affectToKitIssuedQty");
			$Err_TTITFG=DecatErr_Field("@@itfg", "affectToInTransit");
			$Err_TTHDFG=DecatErr_Field("@@hdfg", "affectToHeldInStockQty");
			$Err_TTWSFG=DecatErr_Field("@@wsfg", "affectToWarehouseScrap");
			$Err_TTFLFG=DecatErr_Field("@@flfg", "affectToFloorstock");
			$Err_TTYSFG=DecatErr_Field("@@ysfg", "affectToOrderScrapFromWIP");
			$Err_TTFSFG=DecatErr_Field("@@fsfg", "affectToFloorstockScrap");
			$Err_TTQTYD=DecatErr_Field("@@qtyd", "qtyDesc");
			$Err_TTDOCD=DecatErr_Field("@@docd", "docDesc");
			$Err_TTDTED=DecatErr_Field("@@dted", "dteDesc");
			$Err_TTGLTR=DecatErr_Field("@@gltr", "updateFeedToGL");
			$Err_TTUDTN=DecatErr_Field("@@udtn", "updateTransHist");
			$Err_TTPRIC=DecatErr_Field("@@pric", "includeListPrice");
			$Err_TTAPIV=DecatErr_Field("@@apiv", "includeARInvoice");
			$Err_TTVEND=DecatErr_Field("@@vend", "includeVendor");
			$Err_TTCUST=DecatErr_Field("@@cust", "includeCustomer");
			$Err_TTRESC=DecatErr_Field("@@resc", "includeReasonCode");
			$Err_TTRESD=DecatErr_Field("@@resd", "includeReasonDesc");
			$Err_TTCOST=DecatErr_Field("@@cost", "updateCost");
			$Err_TTAVGC=DecatErr_Field("@@avgc", "updateAvgCost");
			$Err_TTLPO =DecatErr_Field("@@lpo@", "updateLastPOCost");
			$Err_TTLPDT=DecatErr_Field("@@lpdt", "updateLastPurchDate");
			$Err_TTDTER=DecatErr_Field("@@dter", "updateLastRecvDate");
			$Err_TTDUOM=DecatErr_Field("@@duom", "defaultUnitOfMeasure");
			$Err_TTKITM=DecatErr_Field("@@kitm", "kitItemAllowIfRelievedBy");
			$Err_TTTYP2=DecatErr_Field("@@typ2", "secondTransType");
			$Err_TTWH2 =DecatErr_Field("@@wh2@", "secondWarehouseDesc");
			$Err_TTPOFM=DecatErr_Field("@@pofm", "purchInvOffsetAcctNumber");
			$Err_TTPOFS=DecatErr_Field("@@pofs", "purchInvOffsetSubNumber");
			$Err_TTACCD=DecatErr_Field("@@accd", "purchInvOffsetDesc");
			$Err_TTMOFM=DecatErr_Field("@@mofm", "manufInvOffsetAcctNumber");
			$Err_TTMOFS=DecatErr_Field("@@mofs", "manufInvOffsetSubNumber");
			$Err_TTMDES=DecatErr_Field("@@mdes", "manufInvOffsetDesc");
			$Err_TTRESV=DecatErr_Field("@@resv", "resvTransType");
			if ($CISTKL=="Y")  {
				$Err_TTSTKM=DecatErr_Field("@@stkm", "affectStockLocations");
			}
			if ($CILTUS=="Y")  {
				$Err_TTLOTM=DecatErr_Field("@@lotm", "lotRowMaint");
				$Err_TTMWLM=DecatErr_Field("@@mwlm", "updateLotWhereUsed");
			}
		}

		$row['TTTYPE']=Decat_Field("@@type", $edtVar);
		$row['TTDESC']=Decat_Field("@@desc", $edtVar);
		$row['TTSHDS']=Decat_Field("@@shds", $edtVar);
		$row['TTLOCN']=Decat_Field("@@locn", $edtVar);
		$row['TTOHFG']=Decat_Field("@@ohfg", $edtVar);
		$row['TTRCFG']=Decat_Field("@@rcfg", $edtVar);
		$row['TTADFG']=Decat_Field("@@adfg", $edtVar);
		$row['TTISFG']=Decat_Field("@@isfg", $edtVar);
		$row['TTRSFG']=Decat_Field("@@rsfg", $edtVar);
		$row['TTRVFG']=Decat_Field("@@rvfg", $edtVar);
		$row['TTORFG']=Decat_Field("@@orfg", $edtVar);
		$row['TTHRFG']=Decat_Field("@@hrfg", $edtVar);
		$row['TTBORL']=Decat_Field("@@borl", $edtVar);
		$row['TTWPFG']=Decat_Field("@@wpfg", $edtVar);
		$row['TTYUFG']=Decat_Field("@@yufg", $edtVar);
		$row['TTOOFG']=Decat_Field("@@oofg", $edtVar);
		$row['TTSLFG']=Decat_Field("@@slfg", $edtVar);
		$row['TTTRFG']=Decat_Field("@@trfg", $edtVar);
		$row['TTKIFG']=Decat_Field("@@kifg", $edtVar);
		$row['TTITFG']=Decat_Field("@@itfg", $edtVar);
		$row['TTHDFG']=Decat_Field("@@hdfg", $edtVar);
		$row['TTWSFG']=Decat_Field("@@wsfg", $edtVar);
		$row['TTFLFG']=Decat_Field("@@flfg", $edtVar);
		$row['TTYSFG']=Decat_Field("@@ysfg", $edtVar);
		$row['TTFSFG']=Decat_Field("@@fsfg", $edtVar);
		$row['TTQTYD']=Decat_Field("@@qtyd", $edtVar);
		$row['TTDOCD']=Decat_Field("@@docd", $edtVar);
		$row['TTDTED']=Decat_Field("@@dted", $edtVar);
		$row['TTGLTR']=Decat_Field("@@gltr", $edtVar);
		$row['TTUDTN']=Decat_Field("@@udtn", $edtVar);
		$row['TTPRIC']=Decat_Field("@@pric", $edtVar);
		$row['TTAPIV']=Decat_Field("@@apiv", $edtVar);
		$row['TTVEND']=Decat_Field("@@vend", $edtVar);
		$row['TTCUST']=Decat_Field("@@cust", $edtVar);
		$row['TTRESC']=Decat_Field("@@resc", $edtVar);
		$row['TTRESD']=Decat_Field("@@resd", $edtVar);
		$row['TTCOST']=Decat_Field("@@cost", $edtVar);
		$row['TTAVGC']=Decat_Field("@@avgc", $edtVar);
		$row['TTLPO ']=Decat_Field("@@lpo@", $edtVar);
		$row['TTLPDT']=Decat_Field("@@lpdt", $edtVar);
		$row['TTDTER']=Decat_Field("@@dter", $edtVar);
		$row['TTDUOM']=Decat_Field("@@duom", $edtVar);
		$row['TTKITM']=Decat_Field("@@kitm", $edtVar);
		$row['TTTYP2']=Decat_Field("@@typ2", $edtVar);
		$row['TTWH2']=Decat_Field("@@wh2@", $edtVar);
		$row['TTPOFM']=Decat_Field("@@pofm", $edtVar);
		$row['TTPOFS']=Decat_Field("@@pofs", $edtVar);
		$row['TTACCD']=Decat_Field("@@accd", $edtVar);
		$row['TTMOFM']=Decat_Field("@@mofm", $edtVar);
		$row['TTMOFS']=Decat_Field("@@mofs", $edtVar);
		$row['TTMDES']=Decat_Field("@@mdes", $edtVar);
		$row['TTRESV']=Decat_Field("@@resv", $edtVar);
		if ($CISTKL=="Y")  {
			$row['TTSTKM']=Decat_Field("@@stkm", $edtVar);
		}
		if ($CILTUS=="Y")  {
			$row['TTLOTM']=Decat_Field("@@lotm", $edtVar);
			$row['TTMWLM']=Decat_Field("@@mwlm", $edtVar);
		}
		if ($errFound == "" && $maintenanceCode == "A") {
			$row['TTRESV']="N";
		}
		$errFound= "";

	}	elseif ($maintenanceCode=="Z") {
		$row['TTTYPE']="";
		$row['TTRESV']="N";
		$focusField= "transType";

	} else {
		$focusField= "transTypeDesc";
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_TTTYPE);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Transaction Type</span></td>";
	if ($maintenanceCode=="A" || $maintenanceCode=="Z") {
		print "\n <td class=\"inputalph\"><input type=\"text\"   name=\"transType\" value=\"" . rtrim($row['TTTYPE']) . "\" size=\"4\" maxlength=\"4\">$reqFieldChar</td>";
	} else {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"transType\" value=\"" . rtrim($row['TTTYPE']) . "\">$row[TTTYPE]</td>";
	}
	print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"resvTransType\" value=\"" . rtrim($row['TTRESV']) . "\"></td>";
	print "\n </tr> ";
	DspErrMsg($Err_TTTYPE);

	Build_Fld_Entry("Description","transTypeDesc","inputalph","","TTDESC",$row[TTDESC],$Err_TTDESC,"35","30","Y","","");
	Build_Fld_Entry("Short Description","shortDesc","inputalph","","TTDESC",$row[TTSHDS],$Err_TTSHDS,"15","10","","","");
	if ($HDPDRL>0) {Build_Fld_Entry("Affect Plant Or Warehouse","affectPltOrWhs","inputalph","PLTWHS","TTLOCN",$row[TTLOCN],$Err_TTLOCN,"1","1","Y",$row[TTRESV],"");}

	print "\n <tr><td colspan=\"4\"><fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Affect On Following Quantities</legend> <table>";

	Build_Fld_Entry("On Hand","affectToOnHandQty","inputalph","QTYAFFECT","TTOHFG",$row[TTOHFG],$Err_TTOHFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Received","affectToReceivedQty","inputalph","QTYAFFECT","TTRCFG",$row[TTRCFG],$Err_TTRCFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Adjusted","affectToAdjustedQty","inputalph","QTYAFFECT","TTADFG",$row[TTADFG],$Err_TTADFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Issued","affectToIssuedQty","inputalph","QTYAFFECT","TTISFG",$row[TTISFG],$Err_TTISFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Reserved","affectToReservedQty","inputalph","QTYAFFECT","TTRSFG",$row[TTRSFG],$Err_TTRSFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Return To Vendor","affectToRetToVendor","inputalph","QTYAFFECT","TTRVFG",$row[TTRVFG],$Err_TTRVFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("On Hand In Receiving","affectToOnHandInReceiving","inputalph","QTYAFFECT","TTORFG",$row[TTORFG],$Err_TTORFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Held In Receiving","affectToHeldInReceiving","inputalph","QTYAFFECT","TTHRFG",$row[TTHRFG],$Err_TTHRFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Backorder Release","affectToBackorderRelease","inputalph","QTYAFFECT","TTBORL",$row[TTBORL],$Err_TTBORL,"1","1","","","");
	if ($HDPDRL>0) {Build_Fld_Entry("Work-In-Process","affectToWIP","inputalph","QTYAFFECT","TTWPFG",$row[TTWPFG],$Err_TTWPFG,"1","1","",$row[TTRESV],"");}
	if ($HDPDRL>0) {Build_Fld_Entry("YTD Usage","affectToYTDUsage","inputalph","QTYAFFECT","TTYUFG",$row[TTYUFG],$Err_TTYUFG,"1","1","",$row[TTRESV],"");}
	Build_Fld_Entry("On Order","affectToOnOrderQty","inputalph","QTYAFFECT","TTOOFG",$row[TTOOFG],$Err_TTOOFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Sold","affectToSoldQty","inputalph","QTYAFFECT","TTSLFG",$row[TTSLFG],$Err_TTSLFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Transferred","affectToTransferredQty","inputalph","QTYAFFECT","TTTRFG",$row[TTTRFG],$Err_TTTRFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Kit Issued","affectToKitIssuedQty","inputalph","QTYAFFECT","TTKIFG",$row[TTKIFG],$Err_TTKIFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("In Transit","affectToInTransit","inputalph","QTYAFFECT","TTITFG",$row[TTITFG],$Err_TTITFG,"1","1","",$row[TTRESV],"");
	Build_Fld_Entry("Held In Stock","affectToHeldInStockQty","inputalph","QTYAFFECT","TTHDFG",$row[TTHDFG],$Err_TTHDFG,"1","1","",$row[TTRESV],"");
	if ($HDPDRL>0) {Build_Fld_Entry("Warehouse Scrap","affectToWarehouseScrap","inputalph","QTYAFFECT","TTWSFG",$row[TTWSFG],$Err_TTWSFG,"1","1","",$row[TTRESV],"");}
	if ($HDPDRL>0) {Build_Fld_Entry("Floorstock","affectToFloorstock","inputalph","QTYAFFECT","TTFLFG",$row[TTFLFG],$Err_TTFLFG,"1","1","",$row[TTRESV],"");}
	if ($HDPDRL>0) {Build_Fld_Entry("Order Scrap From WIP","affectToOrderScrapFromWIP","inputalph","QTYAFFECT","TTYSFG",$row[TTYSFG],$Err_TTYSFG,"1","1","",$row[TTRESV],"");}
	if ($HDPDRL>0) {Build_Fld_Entry("Floorstock Scrap","affectToFloorstockScrap","inputalph","QTYAFFECT","TTFSFG",$row[TTFSFG],$Err_TTFSFG,"1","1","",$row[TTRESV],"");}
	print "\n </table></fieldset> </td></tr>";
	print "\n <tr><td>&nbsp;</td></tr>";

	Build_Fld_Entry("Quantity Description","qtyDesc","inputalph","","TTQTYD",$row[TTQTYD],$Err_TTQTYD,"25","25","Y","","");
	Build_Fld_Entry("Document Description","docDesc","inputalph","","TTDOCD",$row[TTDOCD],$Err_TTDOCD,"25","25","Y","","");
	Build_Fld_Entry("Date Description","dteDesc","inputalph","","TTDTED",$row[TTDTED],$Err_TTDTED,"25","25","Y","","");
	Build_Fld_Entry("Feed To G/L","updateFeedToGL","inputalph","YORN","TTGLTR",$row[TTGLTR],$Err_TTGLTR,"1","1","Y","","");
	Build_Fld_Entry("Update Transaction History","updateTransHist","inputalph","YORN","TTUDTN",$row[TTUDTN],$Err_TTUDTN,"1","1","Y",$row[TTRESV],"");
	if ($HDPDRL==0) {Build_Fld_Entry("Override Cost","overrideCost","inputalph","YORN","TTCOST",$row[TTCOST],$Err_TTCOST,"1","1","Y",$row[TTRESV],"");}
	Build_Fld_Entry("Include List Price","includeListPrice","inputalph","YORN","TTPRIC",$row[TTPRIC],$Err_TTPRIC,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Include A/R Invoice Number","includeARInvoice","inputalph","YORN","TTAPIV",$row[TTAPIV],$Err_TTAPIV,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Include Vendor Number","includeVendor","inputalph","YORN","TTVEND",$row[TTVEND],$Err_TTVEND,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Include Customer Number","includeCustomer","inputalph","YORN","TTCUST",$row[TTCUST],$Err_TTCUST,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Include Reason Code","includeReasonCode","inputalph","YORN","TTRESC",$row[TTRESC],$Err_TTRESC,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Include Reason Description","includeReasonDesc","inputalph","YORN","TTRESD",$row[TTRESD],$Err_TTRESD,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Update Average Cost","updateAvgCost","inputalph","YORN","TTAVGC",$row[TTAVGC],$Err_TTAVGC,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Update Last P/O Number/Cost","updateLastPOCost","inputalph","YORN","TTLPO",$row[TTLPO],$Err_TTLPO,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Update Last Purchase Date","updateLastPurchDate","inputalph","YORN","TTLPDT",$row[TTLPDT],$Err_TTLPDT,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Update Last Received Date","updateLastRecvDate","inputalph","YORN","TTDTER",$row[TTDTER],$Err_TTDTER,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Default Unit Of Measure","defaultUnitOfMeasure","inputalph","UOM","TTDUOM",$row[TTDUOM],$Err_TTDUOM,"1","1","Y",$row[TTRESV],"");
	Build_Fld_Entry("Kit Item Allowed If Relieved By","kitItemAllowIfRelievedBy","inputalph","KITALLOWED","TTKITM",$row[TTKITM],$Err_TTKITM,"1","1","",$row[TTRESV],"");

	$textOvr=SetTextOvr($Err_TTTYP2);
	$fieldDesc=RetValue("TTTYPE='$row[TTTYP2]'", "HDTTYP", "TTDESC");
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Second Transaction Type</span></td> ";
	if ($row[TTRESV]=="Y") {
		print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"secondTransType\" value=\"" . rtrim($row['TTTYP2']) . "\">$row[TTTYP2]";
		print "\n  <span class=\"dspdesc\">$fieldDesc</span></td>";
	} else {
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"secondTransType\" value=\"" . rtrim($row['TTTYP2']) . "\" size=\"1\" maxlength=\"4\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}TransactionTypeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=secondTransType&amp;fldDesc=secondTransTypeDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"secondTransTypeDesc\">$fieldDesc</span></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_TTTYP2);

	Build_Fld_Entry("Second Warehouse Description","secondWarehouseDesc","inputalph","","TTWH2",$row[TTWH2],$Err_TTWH2,"25","25","","","");

	if (is_null($row['TTPOFM']) || trim($row['TTPOFM'])=="") {$row['TTPOFM']=0;}
	if (is_null($row['TTPOFS']) || trim($row['TTPOFS'])=="") {$row['TTPOFS']=0;}
	$textOvr=SetTextOvr($Err_TTPOFM);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Purchased Inventory Offset</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"purchInvOffsetAcctNumber\" value=\"" . rtrim($row['TTPOFM']) . "\" size=\"1\" maxlength=\"4\"> - <input type=\"text\"   name=\"purchInvOffsetSubNumber\" value=\"" . rtrim($row['TTPOFS']) . "\" size=\"1\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=purchInvOffsetAcctNumber&amp;subFld=purchInvOffsetSubNumber&amp;descFld=purchInvOffsetDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n </tr> ";
	DspErrMsg($Err_TTPOFM);

	Build_Fld_Entry("Offset Account Description","purchInvOffsetDesc","inputalph","","TTACCD",$row[TTACCD],$Err_TTACCD,"25","25","","","");

	if ($HDPDRL>0) {
		if (is_null($row['TTMOFM']) || trim($row['TTMOFM'])=="") {$row['TTMOFM']=0;}
		if (is_null($row['TTMOFS']) || trim($row['TTMOFS'])=="")  {$row['TTMOFS']=0;}
		$textOvr=SetTextOvr($Err_TTMOFM);
		print "\n <tr><td class=\"dsphdr\"><span $textOvr>Manufactured Inventory Offset</span></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"manufInvOffsetAcctNumber\" value=\"" . rtrim($row['TTMOFM']) . "\" size=\"1\" maxlength=\"4\"> - <input type=\"text\"   name=\"manufInvOffsetSubNumber\" value=\"" . rtrim($row['TTMOFS']) . "\" size=\"1\" maxlength=\"4\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=manufInvOffsetAcctNumber&amp;subFld=manufInvOffsetSubNumber&amp;descFld=manufInvOffsetDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
		print "\n </tr> ";
		DspErrMsg($Err_TTMOFM);

		Build_Fld_Entry("Offset Account Description","manufInvOffsetDesc","inputalph","","TTMDES",$row[TTMDES],$Err_TTMDES,"25","25","","","");
	}

	// Affect Stock Locations
	if ($CISTKL=="Y")  {
		Build_Fld_Entry("Affect Stock Locations","affectStockLocations","inputalph","AFFSTKLOC","TTSTKM",$row[TTSTKM],$Err_TTSTKM,"1","1","",$row[TTRESV],"");
	}

	// Lot Control Active
	if ($CILTUS=="Y")  {
		Build_Fld_Entry("Lot Row Maintenance","lotRowMaint","inputalph","AFFECTLOT","TTLOTM",$row[TTLOTM],$Err_TTLOTM,"1","1","",$row[TTRESV],"");
		Build_Fld_Entry("Update Lot Where Used","updateLotWhereUsed","inputalph","BYN","TTMWLM",$row[TTMWLM],$Err_TTMWLM,"1","1","",$row[TTRESV],"");
	}

	print "\n </table> ";


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
	if ($maintenanceCode=="D" && is_null($_POST['transType'])) {
		$_POST['transType']    =$fromTransType;
		$_POST['transTypeDesc']=RetValue("TTTYPE='$_POST[transType]'", "HDTTYP", "TTDESC");
	}
	if ($maintenanceCode == "Z") {$maintenanceCode= "A";}

	$edtVar= "";
	Concat_Field("@@frm1", $fromTransType);
	Concat_Field("@@type", $_POST['transType']=strtoupper($_POST['transType']));
	Concat_Field("@@desc", $_POST['transTypeDesc']);
	Concat_Field("@@shds", $_POST['shortDesc']);
	Concat_Field("@@ohfg", $_POST['affectToOnHandQty']=strtoupper($_POST['affectToOnHandQty']));
	Concat_Field("@@rcfg", $_POST['affectToReceivedQty']=strtoupper($_POST['affectToReceivedQty']));
	Concat_Field("@@adfg", $_POST['affectToAdjustedQty']=strtoupper($_POST['affectToAdjustedQty']));
	Concat_Field("@@isfg", $_POST['affectToIssuedQty']=strtoupper($_POST['affectToIssuedQty']));
	Concat_Field("@@rsfg", $_POST['affectToReservedQty']=strtoupper($_POST['affectToReservedQty']));
	Concat_Field("@@rvfg", $_POST['affectToRetToVendor']=strtoupper($_POST['affectToRetToVendor']));
	Concat_Field("@@orfg", $_POST['affectToOnHandInReceiving']=strtoupper($_POST['affectToOnHandInReceiving']));
	Concat_Field("@@hrfg", $_POST['affectToHeldInReceiving']=strtoupper($_POST['affectToHeldInReceiving']));
	Concat_Field("@@borl", $_POST['affectToBackorderRelease']=strtoupper($_POST['affectToBackorderRelease']));
	Concat_Field("@@wpfg", $_POST['affectToWIP']=strtoupper($_POST['affectToWIP']));
	Concat_Field("@@yufg", $_POST['affectToYTDUsage']=strtoupper($_POST['affectToYTDUsage']));
	Concat_Field("@@oofg", $_POST['affectToOnOrderQty']=strtoupper($_POST['affectToOnOrderQty']));
	Concat_Field("@@slfg", $_POST['affectToSoldQty']=strtoupper($_POST['affectToSoldQty']));
	Concat_Field("@@trfg", $_POST['affectToTransferredQty']=strtoupper($_POST['affectToTransferredQty']));
	Concat_Field("@@kifg", $_POST['affectToKitIssuedQty']=strtoupper($_POST['affectToKitIssuedQty']));
	Concat_Field("@@itfg", $_POST['affectToInTransit']=strtoupper($_POST['affectToInTransit']));
	Concat_Field("@@hdfg", $_POST['affectToHeldInStockQty']=strtoupper($_POST['affectToHeldInStockQty']));
	Concat_Field("@@wsfg", $_POST['affectToWarehouseScrap']=strtoupper($_POST['affectToWarehouseScrap']));
	Concat_Field("@@flfg", $_POST['affectToFloorstock']=strtoupper($_POST['affectToFloorstock']));
	Concat_Field("@@ysfg", $_POST['affectToOrderScrapFromWIP']=strtoupper($_POST['affectToOrderScrapFromWIP']));
	Concat_Field("@@fsfg", $_POST['affectToFloorstockScrap']=strtoupper($_POST['affectToFloorstockScrap']));
	Concat_Field("@@qtyd", $_POST['qtyDesc']);
	Concat_Field("@@docd", $_POST['docDesc']);
	Concat_Field("@@dted", $_POST['dteDesc']);
    if (! isset($_POST['updateFeedToGL'])) {
        $_POST['updateFeedToGL'] = "N";
    }
	Concat_Field("@@gltr", $_POST['updateFeedToGL'] = strtoupper($_POST['updateFeedToGL']));
    if ($_POST['resvTransType'] != 'Y') {
        if (! isset($_POST['affectPltOrWhs'])) {
            $_POST['affectPltOrWhs'] = "W";
        }
        Concat_Field("@@locn", $_POST['affectPltOrWhs'] = strtoupper($_POST['affectPltOrWhs']));
        if (! isset($_POST['updateTransHist'])) {
            $_POST['updateTransHist'] = "N";
        }
        Concat_Field("@@udtn", $_POST['updateTransHist'] = strtoupper($_POST['updateTransHist']));
        if (! isset($_POST['includeListPrice'])) {
            $_POST['includeListPrice'] = "N";
        }
        Concat_Field("@@pric", $_POST['includeListPrice'] = strtoupper($_POST['includeListPrice']));
        if (! isset($_POST['includeARInvoice'])) {
            $_POST['includeARInvoice'] = "N";
        }
        Concat_Field("@@apiv", $_POST['includeARInvoice'] = strtoupper($_POST['includeARInvoice']));
        if (! isset($_POST['includeVendor'])) {
            $_POST['includeVendor'] = "N";
        }
        Concat_Field("@@vend", $_POST['includeVendor'] = strtoupper($_POST['includeVendor']));
        if (! isset($_POST['includeCustomer'])) {
            $_POST['includeCustomer'] = "N";
        }
        Concat_Field("@@cust", $_POST['includeCustomer'] = strtoupper($_POST['includeCustomer']));
        if (! isset($_POST['includeReasonCode'])) {
            $_POST['includeReasonCode'] = "N";
        }
        Concat_Field("@@resc", $_POST['includeReasonCode'] = strtoupper($_POST['includeReasonCode']));
        if (! isset($_POST['includeReasonDesc'])) {
            $_POST['includeReasonDesc'] = "N";
        }
        Concat_Field("@@resd", $_POST['includeReasonDesc'] = strtoupper($_POST['includeReasonDesc']));
        if (! isset($_POST['overrideCost'])) {
            $_POST['overrideCost'] = "N";
        }
        Concat_Field("@@cost", $_POST['overrideCost'] = strtoupper($_POST['overrideCost']));
        if (! isset($_POST['updateAvgCost'])) {
            $_POST['updateAvgCost'] = "N";
        }
        Concat_Field("@@avgc", $_POST['updateAvgCost'] = strtoupper($_POST['updateAvgCost']));
        if (! isset($_POST['updateLastPurchDate'])) {
            $_POST['updateLastPurchDate'] = "N";
        }
        Concat_Field("@@lpdt", $_POST['updateLastPurchDate'] = strtoupper($_POST['updateLastPurchDate']));
        if (! isset($_POST['updateLastPOCost'])) {
            $_POST['updateLastPOCost'] = "N";
        }
        Concat_Field("@@lpo@", $_POST['updateLastPOCost'] = strtoupper($_POST['updateLastPOCost']));
        if (! isset($_POST['updateLastRecvDate'])) {
            $_POST['updateLastRecvDate'] = "N";
        }
        Concat_Field("@@dter", $_POST['updateLastRecvDate'] = strtoupper($_POST['updateLastRecvDate']));
    }
	Concat_Field("@@duom", $_POST['defaultUnitOfMeasure']=strtoupper($_POST['defaultUnitOfMeasure']));
	Concat_Field("@@kitm", $_POST['kitItemAllowIfRelievedBy']=strtoupper($_POST['kitItemAllowIfRelievedBy']));
	Concat_Field("@@typ2", $_POST['secondTransType']=strtoupper($_POST['secondTransType']));
	Concat_Field("@@wh2@", $_POST['secondWarehouseDesc']);
	Concat_Field("@@pofm", $_POST['purchInvOffsetAcctNumber']);
	Concat_Field("@@pofs", $_POST['purchInvOffsetSubNumber']);
	Concat_Field("@@accd", $_POST['purchInvOffsetDesc']);
	Concat_Field("@@mofm", $_POST['manufInvOffsetAcctNumber']);
	Concat_Field("@@mofs", $_POST['manufInvOffsetSubNumber']);
	Concat_Field("@@mdes", $_POST['manufInvOffsetDesc']);
	Concat_Field("@@resv", $_POST['resvTransType']=strtoupper($_POST['resvTransType']));
	if ($CISTKL=="Y")  {
		Concat_Field("@@stkm", $_POST['affectStockLocations']=strtoupper($_POST['affectStockLocations']));
	}
	if ($CILTUS=="Y")  {
		Concat_Field("@@lotm", $_POST['lotRowMaint']=strtoupper($_POST['lotRowMaint']));
		Concat_Field("@@mwlm", $_POST['updateLotWhereUsed']=strtoupper($_POST['updateLotWhereUsed']));
	}
	Concat_Field("@@tsus", $userProfile);
	Concat_Field("@@tsws", "BROWSER");
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HIVTTU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "" || $maintenanceCode == "D") {
		if ($errFound == "") {
			$confMessage=Format_ConfMsg_Desc($maintenanceCode, "$_POST[transType] $_POST[transTypeDesc]", "" , "", "", "", "");
		} else {
			$Err_TTTYPE=DecatErr_Field("@@type", "transType");
			$confMessage=Format_ConfMsg_Desc("", "$_POST[transType] $_POST[transTypeDesc]", "", "<br>$Err_TTTYPE", "", "", "");
		}
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;transType=" . urlencode(trim($_POST['transType'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>