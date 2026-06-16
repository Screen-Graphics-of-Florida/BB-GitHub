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

$page_title     = "I/V Control Maintenance";
$scriptName     = "IVControlMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HIVCTU";

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
	print "\n     if (document.Chg.ivDistPeriod.value ==\"\" || ";
	print "\n         document.Chg.reclaimResourceLevel.value ==\"\" || ";
	print "\n         document.Chg.glDistributionFeed.value ==\"\") ";
	print "\n         {alert(\"$reqFieldError\"); return false;} ";

	print "\n     if (editNum(document.Chg.ivDistPeriod, 4, 0) && ";
	print "\n         editNum(document.Chg.assetAcctNbr, 4, 0) && ";
	print "\n         editNum(document.Chg.assetSubAcctNbr, 4, 0) && ";
	print "\n         editNum(document.Chg.lastCfgItemNumber, 15, 0) && ";
	print "\n         editNum(document.Chg.lastNonCfgItemNumber, 15, 0) && ";
	print "\n         editNum(document.Chg.corporateWhs, 3, 0) && ";
	print "\n         editNum(document.Chg.lastTransferNumber, 8, 0)) ";
	print "\n return true;";
	if ($HDVLCH=="Y") {
		print "\n     if (document.Chg.assetAcctNbr.value ==\"\" && ";
		print "\n         document.Chg.assetSubAcctNbr.value ==\"\") ";
		print "\n         {alert(\"$reqFieldError\"); ";
		print "\n         document.Chg.assetAcctNbr.focus(); ";
		print "\n         document.Chg.assetAcctNbr(); ";
		print "\n         return false;} ";
	}
	print "\n }";

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "OECONTROLMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL = " Select IVCTRL.*, CIPER# as CIPER, CISLT# as CISLT From IVCTRL Where RRN(IVCTRL)=1 ";
	require 'stmtSQLEnd.php';

	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';

	print $hrTagAttr;
	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#general\">General</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#accounting\">Accounting</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#lotControl\">Lot Control</a></td> ";
	print "\n   </tr> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#smartPart\">Smart Part</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#transfers\">Transfers</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#userDefined\">User Defined</a></td> ";
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
			$Err_CIRIOA=DecatErr_Field("@@rioa", "refreshInvOffsetAcct");
			$Err_CIIVGL=DecatErr_Field("@@ivgl", "feedIVToGL");
			$Err_CIINTC=DecatErr_Field("@@intc", "genInterCoTrans");
			$Err_CIAUTO=DecatErr_Field("@@auto", "restoreIVFeedIfErrors");
			$Err_CIPER =DecatErr_Field("@@per@", "ivDistPeriod");
			$Err_CIIVAA=DecatErr_Field("@@ivaa", "assetAcctNbr");
			$Err_CIIVAS=DecatErr_Field("@@ivas", "subassetAcctNbr");
			$Err_CILTUS=DecatErr_Field("@@ltus", "lotControlActive");
			$Err_CILCSE=DecatErr_Field("@@lcse", "lotCostValToBeActual");
			$Err_CISLT =DecatErr_Field("@@slt@", "startingLotNumber");
			$Err_CILTRL=DecatErr_Field("@@ltrl", "lotRelief");
			$Err_CILRLF=DecatErr_Field("@@lrlf", "autoLotRelief");
			$Err_CIALMI=DecatErr_Field("@@almi", "allowMulItemsPerLot");
			$Err_CILUS1=DecatErr_Field("@@lus1", "lotUserDefScrDesc1");
			$Err_CILUS2=DecatErr_Field("@@lus2", "lotUserDefScrDesc2");
			$Err_CILUS3=DecatErr_Field("@@lus3", "lotUserDefScrDesc3");
			$Err_CILU11=DecatErr_Field("@@lu11", "lotUserDefinedHeading11");
			$Err_CILU12=DecatErr_Field("@@lu12", "lotUserDefinedHeading12");
			$Err_CILU21=DecatErr_Field("@@lu21", "lotUserDefinedHeading21");
			$Err_CILU22=DecatErr_Field("@@lu22", "lotUserDefinedHeading22");
			$Err_CILU31=DecatErr_Field("@@lu31", "lotUserDefinedHeading31");
			$Err_CILU32=DecatErr_Field("@@lu32", "lotUserDefinedHeading32");
			$Err_CIFEDS=DecatErr_Field("@@feds", "glDistributionFeed");
			$Err_CISTKL=DecatErr_Field("@@stkl", "stockLocatorActive");
			$Err_CITAGS=DecatErr_Field("@@tags", "allowTagsToBeReused");
			$Err_CIOOIA=DecatErr_Field("@@ooia", "includeOnOrderQty");
			$Err_CIIAS1=DecatErr_Field("@@ias1", "itemUserDefAlphScrDesc1");
			$Err_CIIAS2=DecatErr_Field("@@ias2", "itemUserDefAlphScrDesc2");
			$Err_CIIAS3=DecatErr_Field("@@ias3", "itemUserDefAlphScrDesc3");
			$Err_CIIAS4=DecatErr_Field("@@ias4", "itemUserDefAlphScrDesc4");
			$Err_CIIAS5=DecatErr_Field("@@ias5", "itemUserDefAlphScrDesc5");
			$Err_CIIA11=DecatErr_Field("@@ia11", "itemUserDefAlphHdg11");
			$Err_CIIA12=DecatErr_Field("@@ia12", "itemUserDefAlphHdg12");
			$Err_CIIA21=DecatErr_Field("@@ia21", "itemUserDefAlphHdg21");
			$Err_CIIA22=DecatErr_Field("@@ia22", "itemUserDefAlphHdg22");
			$Err_CIIA31=DecatErr_Field("@@ia31", "itemUserDefAlphHdg31");
			$Err_CIIA32=DecatErr_Field("@@ia32", "itemUserDefAlphHdg32");
			$Err_CIIA41=DecatErr_Field("@@ia41", "itemUserDefAlphHdg41");
			$Err_CIIA42=DecatErr_Field("@@ia42", "itemUserDefAlphHdg42");
			$Err_CIIA51=DecatErr_Field("@@ia51", "itemUserDefAlphHdg51");
			$Err_CIIA52=DecatErr_Field("@@ia52", "itemUserDefAlphHdg52");
			$Err_CIINS1=DecatErr_Field("@@ins1", "itemUserDefNumScrDesc1");
			$Err_CIINS2=DecatErr_Field("@@ins2", "itemUserDefNumScrDesc2");
			$Err_CIINS3=DecatErr_Field("@@ins3", "itemUserDefNumScrDesc3");
			$Err_CIINS4=DecatErr_Field("@@ins4", "itemUserDefNumScrDesc4");
			$Err_CIINS5=DecatErr_Field("@@ins5", "itemUserDefNumScrDesc5");
			$Err_CIIN12=DecatErr_Field("@@in11", "itemUserDefNumHdg11");
			$Err_CIIN12=DecatErr_Field("@@in12", "itemUserDefNumHdg12");
			$Err_CIIN21=DecatErr_Field("@@in21", "itemUserDefNumHdg21");
			$Err_CIIN22=DecatErr_Field("@@in22", "itemUserDefNumHdg22");
			$Err_CIIN31=DecatErr_Field("@@in31", "itemUserDefNumHdg31");
			$Err_CIIN32=DecatErr_Field("@@in32", "itemUserDefNumHdg32");
			$Err_CIIN41=DecatErr_Field("@@in41", "itemUserDefNumHdg41");
			$Err_CIIN42=DecatErr_Field("@@in42", "itemUserDefNumHdg42");
			$Err_CIIN51=DecatErr_Field("@@in51", "itemUserDefNumHdg51");
			$Err_CIIN52=DecatErr_Field("@@in52", "itemUserDefNumHdg52");
			$Err_CIWAS1=DecatErr_Field("@@was1", "whsUserDefAlphScrDesc1");
			$Err_CIWAS2=DecatErr_Field("@@was2", "whsUserDefAlphScrDesc2");
			$Err_CIWAS3=DecatErr_Field("@@was3", "whsUserDefAlphScrDesc3");
			$Err_CIWA11=DecatErr_Field("@@wa11", "whsUserDefAlphHdg11");
			$Err_CIWA12=DecatErr_Field("@@wa12", "whsUserDefAlphHdg12");
			$Err_CIWA21=DecatErr_Field("@@wa21", "whsUserDefAlphHdg21");
			$Err_CIWA22=DecatErr_Field("@@wa22", "whsUserDefAlphHdg22");
			$Err_CIWA31=DecatErr_Field("@@wa31", "whsUserDefAlphHdg31");
			$Err_CIWA32=DecatErr_Field("@@wa32", "whsUserDefAlphHdg32");
			$Err_CIWN11=DecatErr_Field("@@wn11", "whsUserDefNumHdg11");
			$Err_CIWN12=DecatErr_Field("@@wn12", "whsUserDefNumHdg12");
			$Err_CIWN21=DecatErr_Field("@@wn21", "whsUserDefNumHdg21");
			$Err_CIWN12=DecatErr_Field("@@wn22", "whsUserDefNumHdg22");
			$Err_CIWN31=DecatErr_Field("@@wn31", "whsUserDefNumHdg31");
			$Err_CIWN32=DecatErr_Field("@@wn32", "whsUserDefNumHdg32");
			$Err_CIRCLR=DecatErr_Field("@@rclr", "reclaimResourceLevel");
			$Err_CIITM1=DecatErr_Field("@@itm1", "lastCfgItemNumber");
			$Err_CIITM2=DecatErr_Field("@@itm2", "lastNonCfgItemNumber");
			$Err_CIARMO=DecatErr_Field("@@armo", "autoRcvMfgOrder");
			$Err_CIUSMP=DecatErr_Field("@@usmp", "useSmartPart");
			$Err_CIUVCT=DecatErr_Field("@@uvct", "useCatalog");
			$Err_CIASUB=DecatErr_Field("@@asub", "allowSubstitutes");
			$Err_CITRSR=DecatErr_Field("@@trsr", "transferStockroom");
			$Err_CICWHS=DecatErr_Field("@@cwhs", "corporateWhs");
			$Err_CILTRT=DecatErr_Field("@@ltrt", "lastTransferNumber");
			$Err_CITSTP=DecatErr_Field("@@tstp", "timeStamp");
		}
		$row['CERCLR']=Decat_Field("@@rclr", $edtVar);
		$row['CIRIOA']=Decat_Field("@@rioa", $edtVar);
		$row['CIIVGL']=Decat_Field("@@ivgl", $edtVar);
		$row['CIINTC']=Decat_Field("@@intc", $edtVar);
		$row['CIAUTO']=Decat_Field("@@auto", $edtVar);
		$row['CIPER'] =Decat_Field("@@per@", $edtVar);
		$row['CIIVAA']=Decat_Field("@@ivaa", $edtVar);
		$row['CIIVAS']=Decat_Field("@@ivas", $edtVar);
		$row['CILTUS']=Decat_Field("@@ltus", $edtVar);
		$row['CILCSE']=Decat_Field("@@lcse", $edtVar);
		$row['CISLT'] =Decat_Field("@@slt@", $edtVar);
		$row['CILTRL']=Decat_Field("@@ltrl", $edtVar);
		$row['CILRLF']=Decat_Field("@@lrlf", $edtVar);
		$row['CIALMI']=Decat_Field("@@almi", $edtVar);
		$row['CILUS1']=Decat_Field("@@lus1", $edtVar);
		$row['CILUS2']=Decat_Field("@@lus2", $edtVar);
		$row['CILUS3']=Decat_Field("@@lus3", $edtVar);
		$row['CILU11']=Decat_Field("@@lu11", $edtVar);
		$row['CILU12']=Decat_Field("@@lu12", $edtVar);
		$row['CILU21']=Decat_Field("@@lu21", $edtVar);
		$row['CILU22']=Decat_Field("@@lu22", $edtVar);
		$row['CILU31']=Decat_Field("@@lu31", $edtVar);
		$row['CILU32']=Decat_Field("@@lu32", $edtVar);
		$row['CIFEDS']=Decat_Field("@@feds", $edtVar);
		$row['CISTKL']=Decat_Field("@@stkl", $edtVar);
		$row['CITAGS']=Decat_Field("@@tags", $edtVar);
		$row['CIOOIA']=Decat_Field("@@ooia", $edtVar);
		$row['CIIAS1']=Decat_Field("@@ias1", $edtVar);
		$row['CIIAS2']=Decat_Field("@@ias2", $edtVar);
		$row['CIIAS3']=Decat_Field("@@ias3", $edtVar);
		$row['CIIAS4']=Decat_Field("@@ias4", $edtVar);
		$row['CIIAS5']=Decat_Field("@@ias5", $edtVar);
		$row['CIIA11']=Decat_Field("@@ia11", $edtVar);
		$row['CIIA12']=Decat_Field("@@ia12", $edtVar);
		$row['CIIA21']=Decat_Field("@@ia21", $edtVar);
		$row['CIIA22']=Decat_Field("@@ia22", $edtVar);
		$row['CIIA31']=Decat_Field("@@ia31", $edtVar);
		$row['CIIA32']=Decat_Field("@@ia32", $edtVar);
		$row['CIIA41']=Decat_Field("@@ia41", $edtVar);
		$row['CIIA42']=Decat_Field("@@ia42", $edtVar);
		$row['CIIA51']=Decat_Field("@@ia51", $edtVar);
		$row['CIIA52']=Decat_Field("@@ia52", $edtVar);
		$row['CIINS1']=Decat_Field("@@ins1", $edtVar);
		$row['CIINS2']=Decat_Field("@@ins2", $edtVar);
		$row['CIINS3']=Decat_Field("@@ins3", $edtVar);
		$row['CIINS4']=Decat_Field("@@ins4", $edtVar);
		$row['CIINS5']=Decat_Field("@@ins5", $edtVar);
		$row['CIIN11']=Decat_Field("@@in11", $edtVar);
		$row['CIIN12']=Decat_Field("@@in12", $edtVar);
		$row['CIIN21']=Decat_Field("@@in21", $edtVar);
		$row['CIIN22']=Decat_Field("@@in22", $edtVar);
		$row['CIIN31']=Decat_Field("@@in31", $edtVar);
		$row['CIIN32']=Decat_Field("@@in32", $edtVar);
		$row['CIIN41']=Decat_Field("@@in41", $edtVar);
		$row['CIIN42']=Decat_Field("@@in42", $edtVar);
		$row['CIIN51']=Decat_Field("@@in51", $edtVar);
		$row['CIIN52']=Decat_Field("@@in52", $edtVar);
		$row['CIWAS1']=Decat_Field("@@was1", $edtVar);
		$row['CIWAS2']=Decat_Field("@@was2", $edtVar);
		$row['CIWAS3']=Decat_Field("@@was3", $edtVar);
		$row['CIWA11']=Decat_Field("@@wa11", $edtVar);
		$row['CIWA12']=Decat_Field("@@wa12", $edtVar);
		$row['CIWA21']=Decat_Field("@@wa21", $edtVar);
		$row['CIWA22']=Decat_Field("@@wa22", $edtVar);
		$row['CIWA31']=Decat_Field("@@wa31", $edtVar);
		$row['CIWA32']=Decat_Field("@@wa32", $edtVar);
		$row['CIWN11']=Decat_Field("@@wn11", $edtVar);
		$row['CIWN12']=Decat_Field("@@wn12", $edtVar);
		$row['CIWN21']=Decat_Field("@@wn21", $edtVar);
		$row['CIWN12']=Decat_Field("@@wn22", $edtVar);
		$row['CIWN31']=Decat_Field("@@wn31", $edtVar);
		$row['CIWN32']=Decat_Field("@@wn32", $edtVar);
		$row['CIRCLR']=Decat_Field("@@rclr", $edtVar);
		$row['CIITM1']=Decat_Field("@@itm1", $edtVar);
		$row['CIITM2']=Decat_Field("@@itm2", $edtVar);
		$row['CIARMO']=Decat_Field("@@armo", $edtVar);
		$row['CIUSMP']=Decat_Field("@@usmp", $edtVar);
		$row['CIUVCT']=Decat_Field("@@uvct", $edtVar);
		$row['CIASUB']=Decat_Field("@@asub", $edtVar);
		$row['CITRSR']=Decat_Field("@@trsr", $edtVar);
		$row['CITRAI']=Decat_Field("@@trai", $edtVar);
		$row['CITRLC']=Decat_Field("@@trlc", $edtVar);
		$row['CICWHS']=Decat_Field("@@cwhs", $edtVar);
		$row['CILTRT']=Decat_Field("@@ltrt", $edtVar);
		$row['CITSTP']=Decat_Field("@@tstp", $edtVar);
	} else {
		$row['CIPER']=PeriodInputFromCYP($row['CIPER']);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";
	$textOvr=SetTextOvr($Err_CITSTP);
	Build_DspFld("I/V Release Version",$HDINRL,"","A");
	DspErrMsg($Err_CITSTP);
	Build_DspFld("I/V Library Level",$HDINLL,"","A");
	print "\n </table>";

	print "\n <a name=\"general\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">General</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	Build_Fld_Entry("Reclaim Resource Level","reclaimResourceLevel","inputalph","RECLAIMLVL","CIRCLR",$row[CIRCLR],$Err_CIRCLR,"3","1","Y","","");
	Build_Fld_Entry("Include On Order Qty In Availability","includeOnOrderQty","inputalph","YORN","CIOOIA",$row[CIOOIA],$Err_CIOOIA,"1","1","Y","","");
	Build_Fld_Entry("Allow Tags To Be Reused","allowTagsToBeReused","inputalph","YORN","CITAGS",$row[CITAGS],$Err_CITAGS,"1","1","Y","","");
	Build_Fld_Entry("Stock Locator Active","stockLocatorActive","inputalph","YORN","CISTKL",$row[CISTKL],$Err_CISTKL,"1","1","Y","","");
	Build_Fld_Entry("Use Vendor Catalog","useCatalog","inputalph","YORN","CIUVCT",$row[CIUVCT],$Err_CIUVCT,"1","1","Y","","");
	Build_Fld_Entry("Allow Substitutes","allowSubstitutes","inputalph","YORN","CIASUB",$row[CIASUB],$Err_CIASUB,"1","1","Y","","");
	Build_Fld_Entry("Auto Receive Mfg Order","autoRcvMfgOrder","inputalph","YORN","CIARMO",$row[CIARMO],$Err_CIARMO,"1","1","Y","","");

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"accounting\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Accounting</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_CIPER);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>I/V Distribution Period</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"ivDistPeriod\" value=\"" . rtrim($row['CIPER']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}PeriodSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;periodFld=ivDistPeriod\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n </tr> ";
	DspErrMsg($Err_CIPER);

	$row['CIIVAA']=Default_Zero($row['CIIVAA']);
	$row['CIIVAS']=Default_Zero($row['CIIVAS']);
	$fieldDesc=RetValue("CHACCT=$row[CIIVAA] and CHSUB=$row[CIIVAS]", "HDCHRT", "CHCHDS");
	$textOvr=SetTextOvr($Err_CIIVAA);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Inventory Asset Account</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\"   name=\"assetAcctNbr\" value=\"" . rtrim($row['CIIVAA']) . "\" size=\"4\" maxlength=\"4\"> - ";
	print "\n                             <input type=\"text\"   name=\"assetSubAcctNbr\" value=\"" . rtrim($row['CIIVAS']) . "\" size=\"4\" maxlength=\"4\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;acctFld=assetAcctNbr&amp;subFld=assetSubAcctNbr&amp;descFld=assetAcctDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n     <span class=\"dspdesc\" id=\"assetAcctDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_CIIVAA);

	Build_Fld_Entry("Generate Inter-Company Transactions","genInterCoTrans","inputalph","YORN","CIINTC",$row[CIINTC],$Err_CIINTC,"1","1","Y","","");
	Build_Fld_Entry("Refresh Inventory Offset Account","refreshInvOffsetAcct","inputalph","YORN","CIRIOA",$row[CIRIOA],$Err_CIRIOA,"1","1","Y","","");
	Build_Fld_Entry("Feed I/V To G/L","feedIVToGL","inputalph","YORN","CIIVGL",$row[CIIVGL],$Err_CIIVGL,"1","1","Y","","");
	Build_Fld_Entry("Restore I/V Feed If Errors Exist","restoreIVFeedIfErrors","inputalph","YORN","CIAUTO",$row[CIAUTO],$Err_CIAUTO,"1","1","Y","","");
	Build_Fld_Entry("G/L Distribution Feed","glDistributionFeed","inputalph","DORS","CIFEDS",$row[CIFEDS],$Err_CIFEDS,"1","1","Y","","");

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"lotControl\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Lot Control</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	Build_Fld_Entry("Lot Control Active","lotControlActive","inputalph","YORN","CILTUS",$row[CILTUS],$Err_CILTUS,"1","1","Y","","");
	Build_Fld_Entry("Lot Cost Valuation To Be Actual","lotCostValToBeActual","inputalph","YORN","CILCSE",$row[CILCSE],$Err_CILCSE,"1","1","Y","","");
	Build_Fld_Entry("Lot Relief","lotRelief","inputalph","LOTRELIEF","CILTRL",$row[CILTRL],$Err_CILTRL,"1","1","Y","","");
	Build_Fld_Entry("Automatic Lot Relief","autoLotRelief","inputalph","AUTOLOTREL","CILRLF",$row[CILRLF],$Err_CILRLF,"1","1","Y","","");
	Build_Fld_Entry("Allow Multiple Items Per Lot","allowMulItemsPerLot","inputalph","YORN","CIALMI",$row[CIALMI],$Err_CIALMI,"1","1","Y","","");
	$row[CISLT] = trim($row[CISLT]);
	Build_Fld_Entry("Starting Lot Number","startingLotNumber","inputnmbr","","CISLT",$row[CISLT],$Err_CISLT,"15","15","","","");
	print "\n </table> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">User Defined</legend> ";

	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n         <td class=\"colhdr\">Description</td> ";
	print "\n         <td class=\"colhdr\">Report Heading</td> ";
	print "\n         </tr> ";

	$textOvr=SetTextOvr($Err_CILUS1);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Lot User Defined 1</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"lotUserDefScrDesc1\" value=\"" . rtrim($row['CILUS1']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"lotUserDefinedHeading11\" value=\"" . rtrim($row['CILU11']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"lotUserDefinedHeading12\" value=\"" . rtrim($row['CILU12']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CILUS1);

	$textOvr=SetTextOvr($Err_CILUS2);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Lot User Defined 2</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"lotUserDefScrDesc2\" value=\"" . rtrim($row['CILUS2']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"lotUserDefinedHeading21\" value=\"" . rtrim($row['CILU21']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"lotUserDefinedHeading22\" value=\"" . rtrim($row['CILU22']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CILUS2);

	$textOvr=SetTextOvr($Err_CILUS3);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Lot User Defined 3</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"lotUserDefScrDesc3\" value=\"" . rtrim($row['CILUS3']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"lotUserDefinedHeading31\" value=\"" . rtrim($row['CILU31']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"lotUserDefinedHeading32\" value=\"" . rtrim($row['CILU32']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CILUS3);

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n </fieldset> ";

	print "\n  <a name=\"smartPart\"></a> ";
	print "\n  <fieldset class=\"legendBody\"> ";
	print "\n  <legend class=\"legendTitle\">Smart Part</legend> ";
	require 'TopOfForm.php';

	print "\n <table $contentTable>";
	Build_Fld_Entry("Use Smart Part","useSmartPart","inputalph","YORN","CIUSMP",$row[CIUSMP],$Err_CIUSMP,"1","1","Y","","");
	Build_Fld_Entry("Last Configured Item Number","lastCfgItemNumber","inputnmbr","","CIITM1",$row[CIITM1],$Err_CIITM1,"15","15","","","");
	Build_Fld_Entry("Last Non-Configured Item Number","lastNonCfgItemNumber","inputnmbr","","CIITM2",$row[CIITM2],$Err_CIITM2,"15","15","","","");
	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n  <a name=\"transfers\"></a> ";
	print "\n  <fieldset class=\"legendBody\"> ";
	print "\n  <legend class=\"legendTitle\">Transfers</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";

	$row['CICWHS']=Default_Zero($row['CICWHS']);
	$fieldDesc=RetValue("WHWHS=$row[CICWHS]", "HDWHSM", "WHWHNM");
	$textOvr=SetTextOvr($Err_CICWHS);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Corporate Warehouse</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"corporateWhs\" value=\"" . rtrim($row['CICWHS']) . "\" size=\"3\" maxlength=\"3\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}WarehouseSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=corporateWhs&amp;fldDesc=corporateWhsName\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                                     <span class=\"dspdesc\" id=\"corporateWhsName\">$fieldDesc</span></td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CICWHS);

	$textOvr=SetTextOvr($Err_CITRSR);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Transfer Stock Location</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"transferStockroom\" value=\"" . rtrim($row['CITRSR']) . "\" size=\"3\" maxlength=\"3\"> ";
	print "\n                                     <input type=\"text\" name=\"transferAisle\" value=\"" . rtrim($row['CITRAI']) . "\" size=\"4\" maxlength=\"8\"> ";
	print "\n                                     <input type=\"text\" name=\"transferLoc\" value=\"" . rtrim($row['CITRLC']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                     <input type=\"hidden\" name=\"stockLocId\"> ";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}StockLocSearch.php{$genericVarBase}&amp;docName=Chg&amp;forWhs=&amp;fldStkr=transferStockroom&amp;fldAisle=transferAisle&amp;fldLoc=transferLoc&amp;fldStkID=stockLocId\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n             </td>";
	print "\n         </tr> ";
	DspErrMsg($Err_CITRSR);

	Build_Fld_Entry("Last Transfer Ticket Number","lastTransferNumber","inputnmbr","","CILTRT",$row[CILTRT],$Err_CILTRT,"8","8","","","");

	if ($itemExtCat1 != "" || $itemExtCat2 != "" || $itemExtCat3 != "" || $itemExtCat4 != "" || $itemExtCat5 != "") {
		$noneChecked=Field_Checked($row['CITRUD'],"0");
		$oneChecked=Field_Checked($row['CITRUD'],"1");
		$twoChecked=Field_Checked($row['CITRUD'],"2");
		$threeChecked=Field_Checked($row['CITRUD'],"3");
		$fourChecked=Field_Checked($row['CITRUD'],"4");
		$fiveChecked=Field_Checked($row['CITRUD'],"5");
		print "\n  <tr><td class=\"dsphdr\" valign=\"top\"><span $textOvr>Transfer User-Defined Category</span></td> ";
		print "\n      <td class=\"dspalph\"> ";
		print "\n          <input type=\"radio\" name=\"transCat\" $noneChecked value=\"0\">None <br> ";
		if ($itemExtCat1 != "") {print "\n <input type=\"radio\" name=\"transCat\" $oneChecked   value=\"1\">$itemExtCat1 <br> "; }
		if ($itemExtCat2 != "") {print "\n <input type=\"radio\" name=\"transCat\" $twoChecked   value=\"2\">$itemExtCat2 <br> "; }
		if ($itemExtCat3 != "") {print "\n <input type=\"radio\" name=\"transCat\" $threeChecked value=\"3\">$itemExtCat3 <br> "; }
		if ($itemExtCat4 != "") {print "\n <input type=\"radio\" name=\"transCat\" $fourChecked  value=\"4\">$itemExtCat4 <br> "; }
		if ($itemExtCat5 != "") {print "\n <input type=\"radio\" name=\"transCat\" $fiveChecked  value=\"5\">$itemExtCat5 <br> "; }
		print "\n      </td> ";
		print "\n  </tr> ";
	}

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n <a name=\"userDefined\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">User Defined</legend> ";
	require 'TopOfForm.php';

	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n         <td class=\"colhdr\">Description</td> ";
	print "\n         <td class=\"colhdr\">Report Heading</td> ";
	print "\n         </tr> ";

	$textOvr=SetTextOvr($Err_CIIAS1);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Alpha 1</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphScrDesc1\" value=\"" . rtrim($row['CIIAS1']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphHdg11\" value=\"" . rtrim($row['CIIA11']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefAlphHdg12\" value=\"" . rtrim($row['CIIA12']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIIAS1);

	$textOvr=SetTextOvr($Err_CIIAS2);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Alpha 2</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphScrDesc2\" value=\"" . rtrim($row['CIIAS2']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphHdg21\" value=\"" . rtrim($row['CIIA21']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefAlphHdg22\" value=\"" . rtrim($row['CIIA22']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIIAS2);

	$textOvr=SetTextOvr($Err_CIIAS3);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Alpha 3</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphScrDesc3\" value=\"" . rtrim($row['CIIAS3']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphHdg31\" value=\"" . rtrim($row['CIIA31']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefAlphHdg32\" value=\"" . rtrim($row['CIIA32']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIIAS3);

	$textOvr=SetTextOvr($Err_CIIAS4);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Alpha 4</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphScrDesc4\" value=\"" . rtrim($row['CIIAS4']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphHdg41\" value=\"" . rtrim($row['CIIA41']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefAlphHdg42\" value=\"" . rtrim($row['CIIA42']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIIAS4);

	$textOvr=SetTextOvr($Err_CIIAS5);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Alpha 5</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphScrDesc5\" value=\"" . rtrim($row['CIIAS5']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefAlphHdg51\" value=\"" . rtrim($row['CIIA51']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefAlphHdg52\" value=\"" . rtrim($row['CIIA52']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIIAS5);

	$textOvr=SetTextOvr($Err_CIINS1);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Numeric 1</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumScrDesc1\" value=\"" . rtrim($row['CIINS1']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumHdg11\" value=\"" . rtrim($row['CIIN11']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefNumHdg12\" value=\"" . rtrim($row['CIIN12']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIINS1);

	$textOvr=SetTextOvr($Err_CIINS2);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Numeric 2</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumScrDesc2\" value=\"" . rtrim($row['CIINS2']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumHdg21\" value=\"" . rtrim($row['CIIN21']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefNumHdg22\" value=\"" . rtrim($row['CIIN22']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIINS2);

	$textOvr=SetTextOvr($Err_CIINS3);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Numeric 3</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumScrDesc3\" value=\"" . rtrim($row['CIINS3']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumHdg31\" value=\"" . rtrim($row['CIIN31']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefNumHdg32\" value=\"" . rtrim($row['CIIN32']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIINS3);

	$textOvr=SetTextOvr($Err_CIINS4);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Numeric 4</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumScrDesc4\" value=\"" . rtrim($row['CIINS4']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumHdg41\" value=\"" . rtrim($row['CIIN41']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefNumHdg42\" value=\"" . rtrim($row['CIIN42']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIINS4);

	$textOvr=SetTextOvr($Err_CIINS5);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Item Numeric 5</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumScrDesc5\" value=\"" . rtrim($row['CIINS5']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"itemUserDefNumHdg51\" value=\"" . rtrim($row['CIIN51']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"itemUserDefNumHdg52\" value=\"" . rtrim($row['CIIN52']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIINS5);

	$textOvr=SetTextOvr($Err_CIWAS1);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Alpha 1</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefAlphScrDesc1\" value=\"" . rtrim($row['CIWAS1']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefAlphHdg11\" value=\"" . rtrim($row['CIWA11']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"whsUserDefAlphHdg12\" value=\"" . rtrim($row['CIWA12']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIWAS1);

	$textOvr=SetTextOvr($Err_CIWAS2);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Alpha 2</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefAlphScrDesc2\" value=\"" . rtrim($row['CIWAS2']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefAlphHdg21\" value=\"" . rtrim($row['CIWA21']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"whsUserDefAlphHdg22\" value=\"" . rtrim($row['CIWA22']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIWAS2);

	$textOvr=SetTextOvr($Err_CIWAS3);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Alpha 3</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefAlphScrDesc3\" value=\"" . rtrim($row['CIWAS3']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefAlphHdg31\" value=\"" . rtrim($row['CIWA31']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"whsUserDefAlphHdg32\" value=\"" . rtrim($row['CIWA32']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIWAS3);

	$textOvr=SetTextOvr($Err_CIWNS1);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Numeric 1</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefNumScrDesc1\" value=\"" . rtrim($row['CIWNS1']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefNumHdg11\" value=\"" . rtrim($row['CIWN11']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"whsUserDefNumHdg12\" value=\"" . rtrim($row['CIWN12']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIWNS1);

	$textOvr=SetTextOvr($Err_CIWNS2);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Numeric 2</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefNumScrDesc2\" value=\"" . rtrim($row['CIWNS2']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefNumHdg21\" value=\"" . rtrim($row['CIWN21']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"whsUserDefNumHdg22\" value=\"" . rtrim($row['CIWN22']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIWNS2);


	$textOvr=SetTextOvr($Err_CIWNS3);
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Warehouse Numeric 3</span></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefNumScrDesc3\" value=\"" . rtrim($row['CIWNS3']) . "\" size=\"23\" maxlength=\"23\"></td> ";
	print "\n         <td class=\"inputnmbr\"><input type=\"text\" name=\"whsUserDefNumHdg31\" value=\"" . rtrim($row['CIWN31']) . "\" size=\"8\" maxlength=\"8\"> ";
	print "\n                                 <input type=\"text\" name=\"whsUserDefNumHdg32\" value=\"" . rtrim($row['CIWN32']) . "\" size=\"8\" maxlength=\"8\"></td> ";
	print "\n         </tr> ";
	DspErrMsg($Err_CIWNS3);


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

	if (!isset($_POST['refreshInvOffsetAcct'])) {$_POST['refreshInvOffsetAcct']="N";} Concat_Field("@@rioa", $_POST['refreshInvOffsetAcct']);
	if (!isset($_POST['feedIVToGL'])) {$_POST['feedIVToGL']="N";} Concat_Field("@@ivgl", $_POST['feedIVToGL']);
	if (!isset($_POST['genInterCoTrans'])) {$_POST['genInterCoTrans']="N";} Concat_Field("@@intc", $_POST['genInterCoTrans']);
	if (!isset($_POST['restoreIVFeedIfErrors'])) {$_POST['restoreIVFeedIfErrors']="N";} Concat_Field("@@auto", $_POST['restoreIVFeedIfErrors']);
	Concat_Field("@@per@", $_POST['ivDistPeriod']);
	Concat_Field("@@ivaa", $_POST['assetAcctNbr']);
	Concat_Field("@@ivas", $_POST['subassetAcctNbr']);
	if (!isset($_POST['lotControlActive'])) {$_POST['lotControlActive']="N";} Concat_Field("@@ltus", $_POST['lotControlActive']);
	if (!isset($_POST['lotCostValToBeActual'])) {$_POST['lotCostValToBeActual']="N";} Concat_Field("@@lcse", $_POST['lotCostValToBeActual']);
	if (trim($_POST['startingLotNumber']) == "") {$_POST['startingLotNumber'] = "0";}
	Concat_Field("@@slt@", $_POST['startingLotNumber']);
	Concat_Field("@@ltrl", $_POST['lotRelief']=strtoupper($_POST['lotRelief']));
	Concat_Field("@@lrlf", $_POST['autoLotRelief']=strtoupper($_POST['autoLotRelief']));
	if (!isset($_POST['allowMulItemsPerLot'])) {$_POST['allowMulItemsPerLot']="N";} Concat_Field("@@almi", $_POST['allowMulItemsPerLot']);
	Concat_Field("@@lus1", $_POST['lotUserDefScrDesc1']);
	Concat_Field("@@lus2", $_POST['lotUserDefScrDesc2']);
	Concat_Field("@@lus3", $_POST['lotUserDefScrDesc3']);
	Concat_Field("@@lu11", $_POST['lotUserDefinedHeading11']);
	Concat_Field("@@lu12", $_POST['lotUserDefinedHeading12']);
	Concat_Field("@@lu21", $_POST['lotUserDefinedHeading21']);
	Concat_Field("@@lu22", $_POST['lotUserDefinedHeading22']);
	Concat_Field("@@lu31", $_POST['lotUserDefinedHeading31']);
	Concat_Field("@@lu32", $_POST['lotUserDefinedHeading32']);
	Concat_Field("@@feds", $_POST['glDistributionFeed']=strtoupper($_POST['glDistributionFeed']));
	if (!isset($_POST['stockLocatorActive'])) {$_POST['stockLocatorActive']="N";} Concat_Field("@@stkl", $_POST['stockLocatorActive']);
	if (!isset($_POST['allowTagsToBeReused'])) {$_POST['allowTagsToBeReused']="N";} Concat_Field("@@tags", $_POST['allowTagsToBeReused']);
	if (!isset($_POST['includeOnOrderQty'])) {$_POST['includeOnOrderQty']="N";} Concat_Field("@@ooia", $_POST['includeOnOrderQty']);
	Concat_Field("@@ias1", $_POST['itemUserDefAlphScrDesc1']);
	Concat_Field("@@ias2", $_POST['itemUserDefAlphScrDesc2']);
	Concat_Field("@@ias3", $_POST['itemUserDefAlphScrDesc3']);
	Concat_Field("@@ias4", $_POST['itemUserDefAlphScrDesc4']);
	Concat_Field("@@ias5", $_POST['itemUserDefAlphScrDesc5']);
	Concat_Field("@@ia11", $_POST['itemUserDefAlphHdg11']);
	Concat_Field("@@ia12", $_POST['itemUserDefAlphHdg12']);
	Concat_Field("@@ia21", $_POST['itemUserDefAlphHdg21']);
	Concat_Field("@@ia22", $_POST['itemUserDefAlphHdg22']);
	Concat_Field("@@ia31", $_POST['itemUserDefAlphHdg31']);
	Concat_Field("@@ia32", $_POST['itemUserDefAlphHdg32']);
	Concat_Field("@@ia41", $_POST['itemUserDefAlphHdg41']);
	Concat_Field("@@ia42", $_POST['itemUserDefAlphHdg42']);
	Concat_Field("@@ia51", $_POST['itemUserDefAlphHdg51']);
	Concat_Field("@@ia52", $_POST['itemUserDefAlphHdg52']);
	Concat_Field("@@ins1", $_POST['itemUserDefNumScrDesc1']);
	Concat_Field("@@ins2", $_POST['itemUserDefNumScrDesc2']);
	Concat_Field("@@ins3", $_POST['itemUserDefNumScrDesc3']);
	Concat_Field("@@ins4", $_POST['itemUserDefNumScrDesc4']);
	Concat_Field("@@ins5", $_POST['itemUserDefNumScrDesc5']);
	Concat_Field("@@in11", $_POST['itemUserDefNumHdg11']);
	Concat_Field("@@in12", $_POST['itemUserDefNumHdg12']);
	Concat_Field("@@in21", $_POST['itemUserDefNumHdg21']);
	Concat_Field("@@in22", $_POST['itemUserDefNumHdg22']);
	Concat_Field("@@in31", $_POST['itemUserDefNumHdg31']);
	Concat_Field("@@in32", $_POST['itemUserDefNumHdg32']);
	Concat_Field("@@in41", $_POST['itemUserDefNumHdg41']);
	Concat_Field("@@in42", $_POST['itemUserDefNumHdg42']);
	Concat_Field("@@in51", $_POST['itemUserDefNumHdg51']);
	Concat_Field("@@in52", $_POST['itemUserDefNumHdg52']);
	Concat_Field("@@was1", $_POST['whsUserDefAlphScrDesc1']);
	Concat_Field("@@was2", $_POST['whsUserDefAlphScrDesc2']);
	Concat_Field("@@was3", $_POST['whsUserDefAlphScrDesc3']);
	Concat_Field("@@wa11", $_POST['whsUserDefAlphHdg11']);
	Concat_Field("@@wa12", $_POST['whsUserDefAlphHdg12']);
	Concat_Field("@@wa21", $_POST['whsUserDefAlphHdg21']);
	Concat_Field("@@wa22", $_POST['whsUserDefAlphHdg22']);
	Concat_Field("@@wa31", $_POST['whsUserDefAlphHdg31']);
	Concat_Field("@@wa32", $_POST['whsUserDefAlphHdg32']);
	Concat_Field("@@wns1", $_POST['whsUserDefNumScrDesc1']);
	Concat_Field("@@wns2", $_POST['whsUserDefNumScrDesc2']);
	Concat_Field("@@wns3", $_POST['whsUserDefNumScrDesc3']);
	Concat_Field("@@wn11", $_POST['whsUserDefNumHdg11']);
	Concat_Field("@@wn12", $_POST['whsUserDefNumHdg12']);
	Concat_Field("@@wn21", $_POST['whsUserDefNumHdg21']);
	Concat_Field("@@wn22", $_POST['whsUserDefNumHdg22']);
	Concat_Field("@@wn31", $_POST['whsUserDefNumHdg31']);
	Concat_Field("@@wn32", $_POST['whsUserDefNumHdg32']);
	Concat_Field("@@rclr", $_POST['reclaimResourceLevel']=strtoupper($_POST['reclaimResourceLevel']));
	Concat_Field("@@itm1", $_POST['lastCfgItemNumber']);
	Concat_Field("@@itm2", $_POST['lastNonCfgItemNumber']);
	if (!isset($_POST['autoRcvMfgOrder'])) {$_POST['autoRcvMfgOrder']=" ";} Concat_Field("@@armo", $_POST['autoRcvMfgOrder']);
	if (!isset($_POST['useSmartPart'])) {$_POST['useSmartPart']=" ";} Concat_Field("@@usmp", $_POST['useSmartPart']);
	if (!isset($_POST['useCatalog'])) {$_POST['useCatalog']=" ";} Concat_Field("@@uvct", $_POST['useCatalog']);
	if (!isset($_POST['allowSubstitutes'])) {$_POST['allowSubstitutes']=" ";} Concat_Field("@@asub", $_POST['allowSubstitutes']);
	Concat_Field("@@trsr", $_POST['transferStockroom']=strtoupper($_POST['transferStockroom']));
	Concat_Field("@@trai", $_POST['transferAisle']=strtoupper($_POST['transferAisle']));
	Concat_Field("@@trlc", $_POST['transferLoc']=strtoupper($_POST['transferLoc']));
	Concat_Field("@@cwhs", $_POST['corporateWhs']);
	Concat_Field("@@ltrt", $_POST['lastTransferNumber']);
	Concat_Field("@@tstp", $_POST['timeStamp']);
	if ($itemExtCat1 != "" || $itemExtCat2 != "" || $itemExtCat3 != "" || $itemExtCat4 != "" || $itemExtCat5 != "") {
		Concat_Field("@@trud", $_POST['transCat']);
	}
	$edtVar .= "}{";

	$returnValue=Validate_Data($userProfile, $errFound, $edtVar, $errVar);
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc("C", "I/V Control", "", "", "", "", "");
		$includeName= "{$homePath}InventoryControl{$dataBaseID}.php";
		$fileName="InventoryControl{$dataBaseID}.php";
		Write_Control_File($homePath, $fileName, "HIVCTL_I");
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

	$pgm = i5_program_prepare("HIVCTU_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HIVCTU_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

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
	if (!$ret) {die("<br>Validate_Data (HIVCTU_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['userProfile']    =$userProfile;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	return $returnValue;
}

?>