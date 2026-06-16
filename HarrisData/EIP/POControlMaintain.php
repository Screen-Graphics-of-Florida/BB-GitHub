<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];
$backHome           = $_GET['backHome'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "P/O Control Maintenance";
$scriptName     = "POControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HPOCTU";

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
	require_once 'CheckEnterChg.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'UpperCase.php';
	print "\n function validate(chgForm) {";
	print "\n     if (document.Chg.poDistPeriod.value ==\"\" || ";
	print "\n         document.Chg.reclaimResourceLevel.value ==\"\" || ";
	print "\n         document.Chg.glFeedDetailOrSummary.value ==\"\" || ";
	print "\n         document.Chg.freightMiscChargeAllocation.value ==\"\") ";
	print "\n         {alert(\"$reqFieldError\"); return false;} ";

	print "\n     if (editNum(document.Chg.poDistPeriod, 4, 0) && ";
	print "\n         editNum(document.Chg.lastPONumberUsed, 8, 0) && ";
	if ($HDMPRL == 0) {print "\n  editNum(document.Chg.dftNbrPerAvgQtyShip, 2, 0) && ";}
	print "\n         editNum(document.Chg.lastRecTicketNbrAssigned, 8, 0)) ";
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
	$pageID = "POCONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = " Select POCTRL.*, COLPO# as COLPO From POCTRL Where RRN(POCTRL)=1";
	require 'stmtSQLEnd.php';

	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#general\">General</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#autoAssign\">Auto Assign</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#accounting\">Accounting</a></td> ";
	print "\n   </tr> ";
	print "\n   <tr> ";
	if ($HDMPRL == 0) {print "\n     <td class=\"quickLinkTabs\"><a href=\"#sugPO\">Suggested P/O</a></td> ";}
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#miscellaneous\">Miscellaneous</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#container\">Container Receipts</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField= "reclaimResourceLevel";
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_COTSTP=DecatErr_Field("@@tstp", "origTimestamp");
			$Err_COCPER=DecatErr_Field("@@cper", "poDistPeriod");
			$Err_COASSN=DecatErr_Field("@@assn", "poNumberSystemAssigned");
			$Err_COLPO =DecatErr_Field("@@lpo@", "lastPONumberUsed");
			$Err_COARAM=DecatErr_Field("@@aram", "recTicketNbrAssignment");
			$Err_COLRCV=DecatErr_Field("@@lrcv", "lastRecTicketNbrAssigned");
			$Err_COAUTO=DecatErr_Field("@@auto", "restorePOFeedIfErrorsExist");
			$Err_COCPER=DecatErr_Field("@@cper", "poDistPeriod");
			$Err_COSMC1=DecatErr_Field("@@smc1", "miscChargesScreenDesc1");
			$Err_COSMC2=DecatErr_Field("@@smc2", "miscChargesScreenDesc2");
			$Err_COSMC3=DecatErr_Field("@@smc3", "miscChargesScreenDesc3");
			$Err_COSMC4=DecatErr_Field("@@smc4", "miscChargesScreenDesc4");
			$Err_COSMC5=DecatErr_Field("@@smc5", "miscChargesScreenDesc5");
			$Err_COSMC6=DecatErr_Field("@@smc6", "miscChargesScreenDesc6");
			$Err_CORM11=DecatErr_Field("@@rm11", "miscChargesHeading11");
			$Err_CORM12=DecatErr_Field("@@rm12", "miscChargesHeading12");
			$Err_CORM21=DecatErr_Field("@@rm21", "miscChargesHeading21");
			$Err_CORM22=DecatErr_Field("@@rm22", "miscChargesHeading22");
			$Err_CORM31=DecatErr_Field("@@rm31", "miscChargesHeading31");
			$Err_CORM32=DecatErr_Field("@@rm32", "miscChargesHeading32");
			$Err_CORM41=DecatErr_Field("@@rm41", "miscChargesHeading41");
			$Err_CORM42=DecatErr_Field("@@rm42", "miscChargesHeading42");
			$Err_CORM51=DecatErr_Field("@@rm51", "miscChargesHeading51");
			$Err_CORM52=DecatErr_Field("@@rm52", "miscChargesHeading52");
			$Err_CORM61=DecatErr_Field("@@rm61", "miscChargesHeading61");
			$Err_CORM62=DecatErr_Field("@@rm62", "miscChargesHeading62");
			$Err_COAIWD=DecatErr_Field("@@aiwd", "autoItemWhsCreation");
			$Err_COAUTR=DecatErr_Field("@@autr", "printAuditTrail");
			$Err_COBBAL=DecatErr_Field("@@bbal", "batchBalancing");
			$Err_COLNDC=DecatErr_Field("@@lndc", "landedCosting");
			$Err_COALLC=DecatErr_Field("@@allc", "methodOfLandedCostAllocation");
			$Err_COFEDS=DecatErr_Field("@@feds", "glFeedDetailOrSummary");
			$Err_CODFTO=DecatErr_Field("@@dfto", "defaultOpenQtyToReceivedQty");
			$Err_CODFTC=DecatErr_Field("@@dftc", "defaultLastPOCostAtPOEntry");
			$Err_COUVGL=DecatErr_Field("@@uvgl", "feedUnvoucheredReceiptsToGL");
			$Err_COPOAP=DecatErr_Field("@@poap", "feedPurchaseOrdersToAP");
			$Err_COADAL=DecatErr_Field("@@adal", "freightMiscChargeAllocation");
			$Err_COSITM=DecatErr_Field("@@sitm", "defaultSeasonalCode");
			$Err_CONPUS=DecatErr_Field("@@npus", "dftNbrPerAvgQtyShip");
			$Err_COIQOB=DecatErr_Field("@@iqob", "inclBOQtyInSugOrdQty");
			$Err_COIQOR=DecatErr_Field("@@iqor", "inclResvQtyInSugOrdQty");
			$Err_COIQDS=DecatErr_Field("@@iqds", "inclDropShipQtyInSugOrdQty");
			$Err_COIIQC=DecatErr_Field("@@iiqc", "inclIssuesInCalcOfAvgQtyPerDay");
			$Err_COFRTC=DecatErr_Field("@@frtc", "freight");
			$Err_COSLTC=DecatErr_Field("@@sltc", "salesTax");
			$Err_COSCGC=DecatErr_Field("@@scgc", "specialCharges");
			$Err_COMCC1=DecatErr_Field("@@mcc1", "miscCharge1");
			$Err_COMCC2=DecatErr_Field("@@mcc2", "miscCharge2");
			$Err_COMCC3=DecatErr_Field("@@mcc3", "miscCharge3");
			$Err_COMCC4=DecatErr_Field("@@mcc4", "miscCharge4");
			$Err_COMCC5=DecatErr_Field("@@mcc5", "miscCharge5");
			$Err_COMCC6=DecatErr_Field("@@mcc6", "miscCharge6");
			$Err_COMDTY=DecatErr_Field("@@mdty", "miscChargeDuty");
			$Err_COCPYN=DecatErr_Field("@@cpyn", "processContainerReceipts");
			$Err_CORCLR=DecatErr_Field("@@rclr", "reclaimResourceLevel");
			$Err_COUCC =DecatErr_Field("@@ucc@", "updateCurrentCost");
		}
		$row['COTSTP']=Decat_Field("@@tstp", $edtVar);
		$row['COCPER']=Decat_Field("@@cper", $edtVar);
		$row['COASSN']=Decat_Field("@@assn", $edtVar);
		$row['COLPO'] =Decat_Field("@@lpo@", $edtVar);
		$row['COARAM']=Decat_Field("@@aram", $edtVar);
		$row['COLRCV']=Decat_Field("@@lrcv", $edtVar);
		$row['COAUTO']=Decat_Field("@@auto", $edtVar);
		$row['COCPER']=Decat_Field("@@cper", $edtVar);
		$row['COSMC1']=Decat_Field("@@smc1", $edtVar);
		$row['COSMC2']=Decat_Field("@@smc2", $edtVar);
		$row['COSMC3']=Decat_Field("@@smc3", $edtVar);
		$row['COSMC4']=Decat_Field("@@smc4", $edtVar);
		$row['COSMC5']=Decat_Field("@@smc5", $edtVar);
		$row['COSMC6']=Decat_Field("@@smc6", $edtVar);
		$row['CORM11']=Decat_Field("@@rm11", $edtVar);
		$row['CORM12']=Decat_Field("@@rm12", $edtVar);
		$row['CORM21']=Decat_Field("@@rm21", $edtVar);
		$row['CORM22']=Decat_Field("@@rm22", $edtVar);
		$row['CORM31']=Decat_Field("@@rm31", $edtVar);
		$row['CORM32']=Decat_Field("@@rm32", $edtVar);
		$row['CORM41']=Decat_Field("@@rm41", $edtVar);
		$row['CORM42']=Decat_Field("@@rm42", $edtVar);
		$row['CORM51']=Decat_Field("@@rm51", $edtVar);
		$row['CORM52']=Decat_Field("@@rm52", $edtVar);
		$row['CORM61']=Decat_Field("@@rm61", $edtVar);
		$row['CORM62']=Decat_Field("@@rm62", $edtVar);
		$row['COAIWD']=Decat_Field("@@aiwd", $edtVar);
		$row['COAUTR']=Decat_Field("@@autr", $edtVar);
		$row['COBBAL']=Decat_Field("@@bbal", $edtVar);
		$row['COLNDC']=Decat_Field("@@lndc", $edtVar);
		$row['COALLC']=Decat_Field("@@allc", $edtVar);
		$row['COFEDS']=Decat_Field("@@feds", $edtVar);
		$row['CODFTO']=Decat_Field("@@dfto", $edtVar);
		$row['CODFTC']=Decat_Field("@@dftc", $edtVar);
		$row['COUVGL']=Decat_Field("@@uvgl", $edtVar);
		$row['COPOAP']=Decat_Field("@@poap", $edtVar);
		$row['COADAL']=Decat_Field("@@adal", $edtVar);
		$row['COSITM']=Decat_Field("@@sitm", $edtVar);
		$row['CONPUS']=Decat_Field("@@npus", $edtVar);
		$row['COIQOB']=Decat_Field("@@iqob", $edtVar);
		$row['COIQOR']=Decat_Field("@@iqor", $edtVar);
		$row['COIQDS']=Decat_Field("@@iqds", $edtVar);
		$row['COIIQC']=Decat_Field("@@iiqc", $edtVar);
		$row['COFRTC']=Decat_Field("@@frtc", $edtVar);
		$row['COSLTC']=Decat_Field("@@sltc", $edtVar);
		$row['COSCGC']=Decat_Field("@@scgc", $edtVar);
		$row['COMCC1']=Decat_Field("@@mcc1", $edtVar);
		$row['COMCC2']=Decat_Field("@@mcc2", $edtVar);
		$row['COMCC3']=Decat_Field("@@mcc3", $edtVar);
		$row['COMCC4']=Decat_Field("@@mcc4", $edtVar);
		$row['COMCC5']=Decat_Field("@@mcc5", $edtVar);
		$row['COMCC6']=Decat_Field("@@mcc6", $edtVar);
		$row['COMDTY']=Decat_Field("@@mdty", $edtVar);
		$row['COCPYN']=Decat_Field("@@cpyn", $edtVar);
		$row['CORCLR']=Decat_Field("@@rclr", $edtVar);
		$row['COUCC'] =Decat_Field("@@ucc@", $edtVar);
	} else {
		$row['COCPER']=PeriodInputFromCYP($row['COCPER']);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_COTSTP);
	Build_DspFld("P/O Release Version",$HDPORL,"","A");
	DspErrMsg($Err_COTSTP);
	Build_DspFld("P/O Library Level",$HDPOLL,"","A");
	print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['COTSTP']) . "\"></td></tr> ";
	print "\n </table>";

	print "\n <a name=\"general\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">General</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Reclaim Resource Level","reclaimResourceLevel","inputalph","RECLAIMLVL","CORCLR",$row[CORCLR],$Err_CORCLR,"1","1","Y","","");
	Build_Fld_Entry("Automatic Item/Warehouse Creation","autoItemWhsCreation","inputalph","YORN","COAIWD",$row[COAIWD],$Err_COAIWD,"1","1","Y","","");
	Build_Fld_Entry("Print Audit Trail","printAuditTrail","inputalph","YORN","COAUTR",$row[COAUTR],$Err_COAUTR,"1","1","Y","","");
	Build_Fld_Entry("Batch Balancing","batchBalancing","inputalph","YORN","COBBAL",$row[COBBAL],$Err_COBBAL,"1","1","Y","","");
	Build_Fld_Entry("Landed Costing","landedCosting","inputalph","YORN","COLNDC",$row[COLNDC],$Err_COLNDC,"1","1","Y","","");
	Build_Fld_Entry("Method Of Landed Cost Allocation","methodOfLandedCostAllocation","inputalph","LCALLOCATN","COALLC",$row[COALLC],$Err_COALLC,"1","1","","","");
	Build_Fld_Entry("Default Open Qty To Received Qty","defaultOpenQtyToReceivedQty","inputalph","YORN","CODFTO",$row[CODFTO],$Err_CODFTO,"1","1","Y","","");
	Build_Fld_Entry("Default Last P/O Cost At P/O Entry","defaultLastPOCostAtPOEntry","inputalph","YORN","CODFTC",$row[CODFTC],$Err_CODFTC,"1","1","Y","","");
	Build_Fld_Entry("Freight/Misc Charge Allocation","freightMiscChargeAllocation","inputalph","FMALLOCATN","COADAL",$row[COADAL],$Err_COADAL,"1","1","Y","","");
	Build_Fld_Entry("Default Seasonal Code","defaultSeasonalCode","inputalph","YORN","COSITM",$row[COSITM],$Err_COSITM,"1","1","Y","","");
	if ($HDPDRL>0) {Build_Fld_Entry("Update Current Cost","updateCurrentCost","inputalph","YORN","COUCC",$row[COUCC],$Err_COUCC,"1","1","Y","","");}
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"autoAssign\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Auto Assign</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">Auto Assign</td> ";
	print "\n             <td class=\"colhdr\">Last Used</td> ";
	print "\n         </tr> ";

	$textOvr=SetTextOvr($Err_COLPO);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Purchase Order</span></td> ";
	Build_Fld_Entry("P/O Number","poNumberSystemAssigned","inputalph","YORN","COASSN",$row[COASSN],$Err_COASSN,"1","1","","","Y");
	Build_Fld_Entry("Last P/O Number","lastPONumberUsed","inputnmbr","","COLPO",$row[COLPO],$Err_COLPO,"8","8","","","Y");
	DspErrMsg($Err_COLPO);
	print "\n         </tr> ";

	if ($row[COARAM]=="A") {$row[COARAM]="Y";}
	$textOvr=SetTextOvr($Err_COLRCV);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Receiving Ticket</span></td> ";
	Build_Fld_Entry("Receiving Ticket Number System Assigned","recTicketNbrAssignment","inputalph","YORN","COARAM",$row[COARAM],$Err_COARAM,"1","1","","","Y");
	Build_Fld_Entry("Last Receiving Ticket Number","lastRecTicketNbrAssigned","inputnmbr","","COLRCV",$row[COLRCV],$Err_COLRCV,"8","8","","","Y");
	DspErrMsg($Err_COLRCV);
	print "\n         </tr> ";
	print "\n     </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"accounting\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Accounting</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_COCPER);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>P/O Distribution Period</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"poDistPeriod\" value=\"" . rtrim($row['COCPER']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;periodFld=poDistPeriod\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n </tr> ";
	DspErrMsg($Err_COCPER);

	Build_Fld_Entry("Feed Unvouchered Receipts To G/L","feedUnvoucheredReceiptsToGL","inputalph","YORN","COUVGL",$row[COUVGL],$Err_COUVGL,"1","1","Y","","");
	Build_Fld_Entry("Restore P/O Feed If Errors Exist","restorePOFeedIfErrorsExist","inputalph","YORN","COAUTO",$row[COAUTO],$Err_COAUTO,"1","1","Y","","");
	Build_Fld_Entry("Feed Purchase Orders To A/P","feedPurchaseOrdersToAP","inputalph","YORN","COPOAP",$row[COPOAP],$Err_COPOAP,"1","1","Y","","");
	Build_Fld_Entry("G/L Distribution Feed","glFeedDetailOrSummary","inputalph","DORS","COFEDS",$row[COFEDS],$Err_COFEDS,"1","1","Y","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	if ($HDMPRL == 0) {
		print "\n <a name=\"sugPO\"></a> ";
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n <legend class=\"legendTitle\">Suggested P/O</legend> ";
		require 'TopOfForm.php';
		print "\n <table $contentTable>";
		Build_Fld_Entry("Default Number Of Periods For Avg Qty Shipped","dftNbrPerAvgQtyShip","inputnmbr","","CONPUS",$row[CONPUS],$Err_CONPUS,"2","2","","","");
		Build_Fld_Entry("Include Backorder Qty In Suggested Order Qty","inclBOQtyInSugOrdQty","inputalph","YORN","COIQOB",$row[COIQOB],$Err_COIQOB,"1","1","Y","","");
		Build_Fld_Entry("Include Reserve Qty In Suggested Order Qty","inclResvQtyInSugOrdQty","inputalph","YORN","COIQOR",$row[COIQOR],$Err_COIQOR,"1","1","Y","","");
		Build_Fld_Entry("Include Drop Ship Qty In Suggested Order Qty","inclDropShipQtyInSugOrdQty","inputalph","YORN","COIQDS",$row[COIQDS],$Err_COIQDS,"1","1","Y","","");
		Build_Fld_Entry("Include Issues In Calc Of Avg Qty Per Day","inclIssuesInCalcOfAvgQtyPerDay","inputalph","YORN","COIIQC",$row[COIIQC],$Err_COIIQC,"1","1","Y","","");
		print "\n </table> ";
		print "\n </fieldset> ";
	}

	print "\n <a name=\"miscellaneous\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Miscellaneous</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	print "\n     <tr><td>&nbsp;</td> ";
	print "\n         <td class=\"colhdr\">Description</td> ";
	print "\n         <td class=\"colhdr\">Report Heading</td> ";
	print "\n     </tr> ";

	$textOvr=SetTextOvr($Err_COSMC1);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Miscellaneous Charges 1</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesScreenDesc1\" value=\"" . rtrim($row['COSMC1']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesHeading11\" value=\"" . rtrim($row['CORM11']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"miscChargesHeading12\" value=\"" . rtrim($row['CORM12']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_COSMC1);

	$textOvr=SetTextOvr($Err_COSMC2);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Miscellaneous Charges 2</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesScreenDesc2\" value=\"" . rtrim($row['COSMC2']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesHeading21\" value=\"" . rtrim($row['CORM21']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"miscChargesHeading22\" value=\"" . rtrim($row['CORM22']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_COSMC2);

	$textOvr=SetTextOvr($Err_COSMC3);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Miscellaneous Charges 3</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesScreenDesc3\" value=\"" . rtrim($row['COSMC3']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesHeading31\" value=\"" . rtrim($row['CORM31']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"miscChargesHeading32\" value=\"" . rtrim($row['CORM32']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_COSMC3);

	$textOvr=SetTextOvr($Err_COSMC4);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Miscellaneous Charges 4</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesScreenDesc4\" value=\"" . rtrim($row['COSMC4']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesHeading41\" value=\"" . rtrim($row['CORM41']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"miscChargesHeading42\" value=\"" . rtrim($row['CORM42']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_COSMC4);

	$textOvr=SetTextOvr($Err_COSMC5);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Miscellaneous Charges 5</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesScreenDesc5\" value=\"" . rtrim($row['COSMC5']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesHeading51\" value=\"" . rtrim($row['CORM51']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"miscChargesHeading52\" value=\"" . rtrim($row['CORM52']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_COSMC5);

	$textOvr=SetTextOvr($Err_COSMC6);
	print "\n     <tr><td class=\"dsphdr\"><span $textOvr>Miscellaneous Charges 6</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesScreenDesc6\" value=\"" . rtrim($row['COSMC6']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"miscChargesHeading61\" value=\"" . rtrim($row['CORM61']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"miscChargesHeading62\" value=\"" . rtrim($row['CORM62']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n     </tr> ";
	DspErrMsg($Err_COSMC6);

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"container\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Container Receipts</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Process Container Receipts","processContainerReceipts","inputalph","YORN","COCPYN",$row[COCPYN],$Err_COCPYN,"1","1","Y","","");

	print "\n <tr><td>&nbsp;</td> ";
	print "\n     <td class=\"colhdr\">Cost Allocation Methods</td> ";
	print "\n </tr> ";

	Build_Fld_Entry("Freight","freight","inputalph","CRALLOCATN","COFRTC",$row[COFRTC],$Err_COFRTC,"1","1","","","");
	Build_Fld_Entry("Sales Tax","salesTax","inputalph","CRALLOCATN","COSLTC",$row[COSLTC],$Err_COSLTC,"1","1","","","");
	Build_Fld_Entry("Special Charges","specialCharges","inputalph","CRALLOCATN","COSCGC",$row[COSCGC],$Err_COSCGC,"1","1","","","");
	Build_Fld_Entry("Misc Charge 1","miscCharge1","inputalph","CRALLOCATN","COMCC1",$row[COMCC1],$Err_COMCC1,"1","1","","","");
	Build_Fld_Entry("Misc Charge 2","miscCharge2","inputalph","CRALLOCATN","COMCC2",$row[COMCC2],$Err_COMCC2,"1","1","","","");
	Build_Fld_Entry("Misc Charge 3","miscCharge3","inputalph","CRALLOCATN","COMCC3",$row[COMCC3],$Err_COMCC3,"1","1","","","");
	Build_Fld_Entry("Misc Charge 4","miscCharge4","inputalph","CRALLOCATN","COMCC4",$row[COMCC4],$Err_COMCC4,"1","1","","","");
	Build_Fld_Entry("Misc Charge 5","miscCharge5","inputalph","CRALLOCATN","COMCC5",$row[COMCC5],$Err_COMCC5,"1","1","","","");
	Build_Fld_Entry("Misc Charge 6","miscCharge6","inputalph","CRALLOCATN","COMCC6",$row[COMCC6],$Err_COMCC6,"1","1","","","");
	Build_Fld_Entry("Misc Charge Used For Duty","miscChargeDuty","inputalph","123456","COMDTY",$row[COMDTY],$Err_COMDTY,"1","1","","","");

	print "\n </table> ";
	print "\n </fieldset> ";

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
	$edtVar= "";

	Concat_Field("@@cper", $_POST['poDistPeriod']);
	if (!isset($_POST['poNumberSystemAssigned'])) {$_POST['poNumberSystemAssigned']="N";} Concat_Field("@@assn", $_POST['poNumberSystemAssigned']);
	Concat_Field("@@lpo@", $_POST['lastPONumberUsed']);
	if (!isset($_POST['recTicketNbrAssignment'])) {$_POST['recTicketNbrAssignment']="M";} else {$_POST['recTicketNbrAssignment']="A";}
	Concat_Field("@@aram", $_POST['recTicketNbrAssignment']);
	if (!isset($_POST['recTicketNbrAssignment'])) {$_POST['recTicketNbrAssignment']="N";} Concat_Field("@@aram", $_POST['recTicketNbrAssignment']);
	Concat_Field("@@lrcv", $_POST['lastRecTicketNbrAssigned']);
	if (!isset($_POST['restorePOFeedIfErrorsExist'])) {$_POST['restorePOFeedIfErrorsExist']="N";} Concat_Field("@@auto", $_POST['restorePOFeedIfErrorsExist']);
	Concat_Field("@@cper", $_POST['poDistPeriod']);
	Concat_Field("@@smc1", $_POST['miscChargesScreenDesc1']);
	Concat_Field("@@smc2", $_POST['miscChargesScreenDesc2']);
	Concat_Field("@@smc3", $_POST['miscChargesScreenDesc3']);
	Concat_Field("@@smc4", $_POST['miscChargesScreenDesc4']);
	Concat_Field("@@smc5", $_POST['miscChargesScreenDesc5']);
	Concat_Field("@@smc6", $_POST['miscChargesScreenDesc6']);
	Concat_Field("@@rm11", $_POST['miscChargesHeading11']);
	Concat_Field("@@rm12", $_POST['miscChargesHeading12']);
	Concat_Field("@@rm21", $_POST['miscChargesHeading21']);
	Concat_Field("@@rm22", $_POST['miscChargesHeading22']);
	Concat_Field("@@rm31", $_POST['miscChargesHeading31']);
	Concat_Field("@@rm32", $_POST['miscChargesHeading32']);
	Concat_Field("@@rm41", $_POST['miscChargesHeading41']);
	Concat_Field("@@rm42", $_POST['miscChargesHeading42']);
	Concat_Field("@@rm51", $_POST['miscChargesHeading51']);
	Concat_Field("@@rm52", $_POST['miscChargesHeading52']);
	Concat_Field("@@rm61", $_POST['miscChargesHeading61']);
	Concat_Field("@@rm62", $_POST['miscChargesHeading62']);
	if (!isset($_POST['autoItemWhsCreation'])) {$_POST['autoItemWhsCreation']="N";} Concat_Field("@@aiwd", $_POST['autoItemWhsCreation']);
	if (!isset($_POST['printAuditTrail'])) {$_POST['printAuditTrail']="N";} Concat_Field("@@autr", $_POST['printAuditTrail']);
	if (!isset($_POST['batchBalancing'])) {$_POST['batchBalancing']="N";} Concat_Field("@@bbal", $_POST['batchBalancing']);
	if (!isset($_POST['landedCosting'])) {$_POST['landedCosting']="N";} Concat_Field("@@lndc", $_POST['landedCosting']);
	Concat_Field("@@allc", $_POST['methodOfLandedCostAllocation']=strtoupper($_POST['methodOfLandedCostAllocation']));
	Concat_Field("@@feds", $_POST['glFeedDetailOrSummary']=strtoupper($_POST['glFeedDetailOrSummary']));
	if (!isset($_POST['defaultOpenQtyToReceivedQty'])) {$_POST['defaultOpenQtyToReceivedQty']="N";} Concat_Field("@@dfto", $_POST['defaultOpenQtyToReceivedQty']);
	if (!isset($_POST['defaultLastPOCostAtPOEntry'])) {$_POST['defaultLastPOCostAtPOEntry']="N";} Concat_Field("@@dftc", $_POST['defaultLastPOCostAtPOEntry']);
	if (!isset($_POST['feedUnvoucheredReceiptsToGL'])) {$_POST['feedUnvoucheredReceiptsToGL']="N";} Concat_Field("@@uvgl", $_POST['feedUnvoucheredReceiptsToGL']);
	if (!isset($_POST['feedPurchaseOrdersToAP'])) {$_POST['feedPurchaseOrdersToAP']="N";} Concat_Field("@@poap", $_POST['feedPurchaseOrdersToAP']);
	Concat_Field("@@adal", $_POST['freightMiscChargeAllocation']=strtoupper($_POST['freightMiscChargeAllocation']));
	if (!isset($_POST['defaultSeasonalCode'])) {$_POST['defaultSeasonalCode']="N";} Concat_Field("@@sitm", $_POST['defaultSeasonalCode']);
	if (!isset($_POST['dftNbrPerAvgQtyShip'])) {$_POST['dftNbrPerAvgQtyShip']="N";} Concat_Field("@@npus", $_POST['dftNbrPerAvgQtyShip']);
	if (!isset($_POST['inclBOQtyInSugOrdQty'])) {$_POST['inclBOQtyInSugOrdQty']="N";} Concat_Field("@@iqob", $_POST['inclBOQtyInSugOrdQty']);
	if (!isset($_POST['inclResvQtyInSugOrdQty'])) {$_POST['inclResvQtyInSugOrdQty']="N";} Concat_Field("@@iqor", $_POST['inclResvQtyInSugOrdQty']);
	if (!isset($_POST['inclDropShipQtyInSugOrdQty'])) {$_POST['inclDropShipQtyInSugOrdQty']="N";} Concat_Field("@@iqds", $_POST['inclDropShipQtyInSugOrdQty']);
	if (!isset($_POST['inclIssuesInCalcOfAvgQtyPerDay'])) {$_POST['inclIssuesInCalcOfAvgQtyPerDay']="N";} Concat_Field("@@iiqc", $_POST['inclIssuesInCalcOfAvgQtyPerDay']);
	Concat_Field("@@frtc", $_POST['freight']=strtoupper($_POST['freight']));
	Concat_Field("@@sltc", $_POST['salesTax']=strtoupper($_POST['salesTax']));
	Concat_Field("@@scgc", $_POST['specialCharges']=strtoupper($_POST['specialCharges']));
	Concat_Field("@@mcc1", $_POST['miscCharge1']=strtoupper($_POST['miscCharge1']));
	Concat_Field("@@mcc2", $_POST['miscCharge2']=strtoupper($_POST['miscCharge2']));
	Concat_Field("@@mcc3", $_POST['miscCharge3']=strtoupper($_POST['miscCharge3']));
	Concat_Field("@@mcc4", $_POST['miscCharge4']=strtoupper($_POST['miscCharge3']));
	Concat_Field("@@mcc5", $_POST['miscCharge5']=strtoupper($_POST['miscCharge5']));
	Concat_Field("@@mcc6", $_POST['miscCharge6']=strtoupper($_POST['miscCharge6']));
	Concat_Field("@@mdty", $_POST['miscChargeDuty']=strtoupper($_POST['miscChargeDuty']));
	if (!isset($_POST['processContainerReceipts'])) {$_POST['processContainerReceipts']="N";} Concat_Field("@@cpyn", $_POST['processContainerReceipts']);
	Concat_Field("@@rclr", $_POST['reclaimResourceLevel']=strtoupper($_POST['reclaimResourceLevel']));
	if (!isset($_POST['updateCurrentCost'])) {$_POST['updateCurrentCost']="N";} Concat_Field("@@ucc@", $_POST['updateCurrentCost']);
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	$edtVar .= "}{";

	$returnValue=Validate_Data($userProfile, $errFound, $edtVar, $errVar);
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc("C", "P/O Control", "", "", "", "", "");
		$includeName= "{$homePath}POControl{$dataBaseID}.php";
		$fileName="POControl{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HPOCTL_I");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$backHome}{$altVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
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

	$pgm = i5_program_prepare("HPOCTU_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HPOCTU_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

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
	if (!$ret) {die("<br>Validate_Data (HPOCTU_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['userProfile']    =$userProfile;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	return $returnValue;
}

?>