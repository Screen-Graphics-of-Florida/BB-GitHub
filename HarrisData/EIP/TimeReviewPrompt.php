<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';


$errFound			=	(isset($_GET['errFound']))			 ?	$_GET['errFound']			:	null;
$wrnVar				=	(isset($_GET['wrnVar']))			 ?	$_GET['wrnVar']				:	null;
$resetSelectionFlag	=	(isset($_GET['resetSelectionFlag'])) ?	$_GET['resetSelectionFlag']	:	null;
$rtvSelection		=	(isset($_GET['rtvSelection']))		 ?	$_GET['rtvSelection']		:	null;
$saveSelection		=	(isset($_GET['saveSelection']))		 ?	$_GET['saveSelection']		:	null;
$scheduleJobSwitch  =	(isset($_GET['scheduleJobSwitch']))	 ?	$_GET['scheduleJobSwitch']	:	null;
$reportSelType      =	(isset($_GET['reportSelType']))	     ?	$_GET['reportSelType']	    :	null;
$fromEmid   	    =	(isset($_GET['fromEmid']))	 	     ?	$_GET['fromEmid']		    :	null;
$pdwk			    =	(isset($_GET['pdwk']))			     ?	$_GET['pdwk']			    :	null;
$inqOnly            =	(isset($_GET['inqOnly']))	         ?	$_GET['inqOnly']	        :	"";
$useSession         =	(isset($_GET['useSession']))	     ?	$_GET['useSession']	        :	null;
$forEmployee        =	(isset($_GET['forEmployee']))	     ?	$_GET['forEmployee']        :	null;

if (!is_null($forEmployee)) {
    $tag = 'Edit_Data';
    $_POST = [
        'sbdo' => 'A',
        'hfmt' => 'D',
        'updateSearch' => 'Y',
        'osch' => 'BETWEEN',
        'fsch' => '',
        'tsch' => '',
        'otdt' => '=',
        'ftdt' => '',
        'ocmp' => 'BETWEEN',
        'fcmp' => '',
        'ffac' => '',
        'tcmp' => '',
        'tfac' => '',
        'ohdp' => 'BETWEEN',
        'fhdp' => '',
        'thdp' => '',
        'oemp' => '=',
        'femp' => $forEmployee,
        'temp' => '',
        'oord' => 'BETWEEN',
        'ford' => '',
        'tord' => '',
        'ogrp' => 'BETWEEN',
        'fgrp' => '',
        'tgrp' => '',
        'oplt' => 'BETWEEN',
        'fplt' => '',
        'tplt' => '',
        'frPltName' => '',
        'toPltName' => '',
        'omdp' => 'BETWEEN',
        'fmdp' => '',
        'tmdp' => '',
        'owc' => 'BETWEEN',
        'fwc' => '',
        'twc' => '',
        'noex' => 'Y',
        'exne' => 'Y',
        'A@,' => 'Y',
        'AR,' => 'Y',
        'ER,' => 'Y',
        'NS,' => 'Y',
        'OT,' => 'Y',
        'PB,' => 'Y',
        'SA,' => 'Y',
        'UP,' => 'Y',
        '10,' => 'Y',
        '20,' => 'Y',
        'submitSchedule' => '',
        'saveSelection' => 'N',
        'rtvSelection' => '',
        'selScheduleJob' => ''];
}

if ($useSession == "Y") {
	$hfmt  =	(isset($_SESSION['hrsFmt']))	 ?	$_SESSION['hrsFmt']	:	$TAHWKF;

	if (is_null($reportSelType)) {
		$reportSelType  =	(isset($_SESSION['reportSelType']))	 ?	$_SESSION['reportSelType']	:	null;
	}
}

require_once 'SetLibraryList.php';
require_once "ETControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'WildCardAcctInclude.php';

$page_title            = "Time Review";
$scriptName            = "TimeReviewPrompt.php";
$scriptVarBase         = "{$genericVarBase}&amp;reportSelType=" . urlencode(trim($reportSelType));
$baseURL               = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection     = "";
$filterURL    		   = "{$scriptName}{$scriptVarBase}";
$submitCallProgram     = "HETBLI";
$submitEnvProgram      = "HETBLI";
$submitEnvPrinter      = "HETBLIPF";
$submitScheduleScript  = "";
$applicationID         = "ET";
$sylMaxSeq      	   = 3;


$mdCol = Rtv_Error_Levels();

foreach ($mdCol as $mdFld)  {
	$curdType = trim($mdFld['EVTYPE']);
	$curName = trim($mdFld['EVCODE']);
	$curText = trim($mdFld['EVDESC']);
}


if (is_null($tag) && $reportSelType) {$tag = "REPORT" ;}

if ($reportSelType) {
	$_SESSION['reportSelType']=$reportSelType;

	if ($reportSelType == 'E') {
		$tblID = 1;
	} else{
		$tblID = 2;
	}
}



if ($tag == "REPORT") {


	require 'FilterInit.php';
	require 'FilterDefault.php';

	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'DateEdit.php';
	require_once 'DisplayHideSelCriteria.php';
	require_once 'NumEdit.php';
	require_once 'AJAXRequest.js';
	require_once 'SaveCurrentURL.php';

	print "\n function validate(chgForm) { ";
	print "\n   if (document.Chg.sbdo.value == \"\",  ";
	print "\n       document.Chg.hfmt.value == \"\"  ";
	print "\n      ) {alert(\"Must make a selection\"); return false;} ";

	print "\n   if (editNum(document.Chg.fsch, 3, 0) ";
	print "\n    && editNum(document.Chg.tsch, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.fsch,        document.Chg.tsch,      document.Chg.osch, 3) ";
	print "\n    && editdate(document.Chg.ftdt) ";
	print "\n    && editNum(document.Chg.fcmp, 2, 0) ";
	print "\n    && editNum(document.Chg.tcmp, 2, 0) ";
	print "\n    && editNum(document.Chg.ffac, 4, 0) ";
	print "\n    && editNum(document.Chg.tfac, 4, 0) ";
	print "\n    && editFromToOper2(document.Chg.fcmp,    document.Chg.ffac, document.Chg.tcmp,    document.Chg.tfac,  document.Chg.ocmp, 2, 4) ";
	print "\n    && editFromToOper(document.Chg.fhdp,    document.Chg.thdp,  document.Chg.ohdp, 'A') ";
	print "\n    && editNum(document.Chg.femp, 5, 0) ";
	print "\n    && editNum(document.Chg.temp, 5, 0) ";
	print "\n    && editFromToOper(document.Chg.femp,    document.Chg.temp,  document.Chg.oemp, 5) ";
	print "\n    && editFromToOper(document.Chg.ford,    document.Chg.tord,  document.Chg.oord, 9) ";
	print "\n    && editNum(document.Chg.fgrp, 5, 0) ";
	print "\n    && editNum(document.Chg.tgrp, 5, 0) ";
	print "\n    && editFromToOper(document.Chg.fgrp,    document.Chg.tgrp,  document.Chg.ogrp, 5) ";
	if ($HDMERL > 0)  {print "\n    && editNum(document.Chg.fplt, 3, 0) ";
	print "\n    && editNum(document.Chg.tplt, 3, 0) ";
	print "\n    && editFromToOper(document.Chg.fplt,    document.Chg.tplt,  document.Chg.oplt, 3) ";
	print "\n    && editFromToOper(document.Chg.fmdp,    document.Chg.tmdp,  document.Chg.omdp, 'A') ";
	print "\n    && editFromToOper(document.Chg.fwc,    document.Chg.twc,  document.Chg.owc, 'A') ";}
	print "\n    ) {return true;} ";
	print "\n } ";

?>

 
  // Select Format
  function OpenSelectFormat() {showSel('showSelectFormat');}  
  function CloseSelectFormat() {hideSel('showSelectFormat');} 
  function editSF() { 
    if (document.getElementById('reportSelType').value =="E" || document.getElementById('reportSelType').value =="G") {OpenSelectFormat();}
    else {CloseSelectFormat();}
  } 

  // Supervisor Approval
  function OpenSupervisorApproval() {showSel('showSupervisorApproval');}  
  function CloseSupervisorApproval() {hideSel('showSupervisorApproval');} 
  function editSA() { 
    if (document.getElementById('reportSelType').value =="E" && $TASUPA != "") {OpenSupervisorApproval();}
    else {CloseSupervisorApproval();}
  } 
  
  // Refine Filter Criteria
  function OpenRefineFilterCriteria() {showSel('showRefineFilterCriteria');}  
  function CloseRefineFilterCriteria() {hideSel('showRefineFilterCriteria');} 
  function editRF() { 
    if (document.getElementById('reportSelType').value == 'E' && document.getElementById('inqOnly').value != 'Y'  || document.getElementById('reportSelType').value == 'G' && document.getElementById('inqOnly').value != 'Y') {OpenRefineFilterCriteria();}
    else {CloseRefineFilterCriteria();}
  } 
  
  // Error Level
  function OpenErrorLevel() {showSel('showErrorLevel');}  
  function CloseErrorLevel() {hideSel('showErrorLevel');} 
  function editEL() { 
    if (document.getElementById('reportSelType').value == 'E' || document.getElementById('reportSelType').value == 'G') {OpenErrorLevel();}
    else {CloseErrorLevel();}
  } 

	
<?php	
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
$popUpWin = "";
if ($inqOnly == "Y") {
	$popUpWin = "Y";
}
require_once 'Banner.php';
if ($inqOnly == "Y") {
	print "\n <table $contentTable>";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>Current $page_title Selection</h1></td>";
	print "\n     <td class=\"toolbar\">";
	require 'CloseWindow.php';
	print "\n </td></tr></table>";
}
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "TIMEREVIEWPROMPT";
if ($inqOnly != "Y") {
	require_once 'MenuDisplay.php';
}

print "\n <td class=\"content\">";
if ($inqOnly != "Y") {
	$allowScheduleJob = "N";
	require 'FilterTop.php';


}
require 'ConfMessageDisplayNoTable.php';
print $hrTagAttr;

// Get Errors

if ($errFound != "" || $scheduleJobSwitch == "Y") {
	$scheduleJobSwitch = "";
	$focusField="";
	$edtVar=EdtVarErr($profileHandle, $edtVar);
	if ($errFound != "") {
		$errVar=ErrVarErr($profileHandle, $errVar);
	}

	$reportSelType=Decat_Field("@@sfmt", $edtVar);
	$sbdo=Decat_Field("@@sbdo", $edtVar);
	$hfmt=Decat_Field("@@hfmt", $edtVar);
	$uapt=Decat_Field("@@uapt", $edtVar);
	$SF=Decat_Field("@@sf@@", $edtVar);
	$SA=Decat_Field("@@sa@@", $edtVar);
	$RF=Decat_Field("@@rf@@", $edtVar);
	$EL=Decat_Field("@@el@@", $edtVar);
	$noex=Decat_Field("@@noex", $edtVar);
	$exne=Decat_Field("@@exne", $edtVar);
	foreach ($mdCol as $mdFld)  {
		$curdType = trim($mdFld['EVTYPE']);
		$curName = trim($mdFld['EVCODE']);
		$curText = trim($mdFld['EVDESC']);
		$wrkLvlChk = "";
		$wrkLvlChk = ('er' . $curName);
		$dwrkLvlChk = ('@@' . $wrkLvlChk);
		$dwrkLvlChk = str_pad($dwrkLvlChk, 6, '@');
		$$wrkLvlChk=Decat_Field($dwrkLvlChk, $edtVar);
	}
	unset($mdFld);

	$pos = strpos($viewCheckBoxString,'@@,');
	if ($pos === false) {
	} else {
		$noex="Y";
	}
	$pos = strpos($viewCheckBoxString,'@X,');
	if ($pos === false) {
	} else {
		$exne="Y";
	}
	foreach ($mdCol as $mdFld)  {
		$curdType = trim($mdFld['EVTYPE']);
		$curName = trim($mdFld['EVCODE']);
		$curText = trim($mdFld['EVDESC']);
		$wrkLvlChk = "";
		$wrkLvlChk = str_pad($curName, 2, '@');
		$wrkLvlChk = ($wrkLvlChk  . ',');
		$pos = strpos($viewCheckBoxString, $wrkLvlChk);
		if ($pos === false) {
		} else {
			${$wrkLvlChk} = "Y";
		}
	}
	unset($mdFld);


	$pos = strpos($viewCheckBoxString,'@A,');
	if ($pos === false) {
	} else {
		$sbdo="A";
	}


	$pos = strpos($viewCheckBoxString,'@E,');
	if ($pos === false) {
	} else {
		$sbdo="E";
	}


	$pos = strpos($viewCheckBoxString,'@U,');
	if ($pos === false) {
	} else {
		$uapt="Y";
	}

} else {

	$SF="N";
	$SA="N";
	$RF="N";
	$EL="N";
	if ($reportSelType == "E" || $reportSelType == "G") {
		$SF="Y";
	}
	if ($reportSelType == "E" && $TASUPA != "") {
		$SA="Y";
	}
	if ($reportSelType == "E" || $reportSelType == "G") {
		$RF="Y";
		$EL="Y";
	}
	if ($sbdo == "" ) {
		$sbdo="E";
	}
	if ($hfmt == "" ) {
		$hfmt="D";
	}

	//if ($ftdt == 0 ) {
	//$ftdt=DateInputFromCYMD(DateTodayCYMD());
	//}

	//if ($HDMERL > 0 && $_SESSION['gotoFilter']=="Y") {
	//if ($HDMERL > 0 ) {
	//if (is_null($fplt) && is_null($tplt)) {
	//$returnValue=RtvDftPlant();
	//$forPlant = $returnValue['dftPltNumber'];
	//$fplt = $forPlant;
	//$tplt = $forPlant;
	//}
	//}
	if ($viewCheckBoxString == "") {

		$noex="Y";
		$exne="Y";
		foreach ($mdCol as $mdFld)  {
			$curdType = trim($mdFld['EVTYPE']);
			$curName = trim($mdFld['EVCODE']);
			$curText = trim($mdFld['EVDESC']);
			$wrkLvlChkx = "";
			$wrkLvlChkx = str_pad($curName, 2, '@');
			$wrkLvlChkx = ($wrkLvlChkx  . ',');
			$viewCheckBoxString = ($viewCheckBoxString . $wrkLvlChkx);
			$wrkLvlChk = "";
			$wrkLvlChk = ('er' . $curName);
			${$wrkLvlChk} = "Y";
		}
		unset($mdFld);
	} else {

		$pos = strpos($viewCheckBoxString,'@A,');
		if ($pos === false) {
		} else {
			$sbdo="A";
		}


		$pos = strpos($viewCheckBoxString,'@E,');
		if ($pos === false) {
		} else {
			$sbdo="E";
		}


		$pos = strpos($viewCheckBoxString,'@U,');
		if ($pos === false) {
		} else {
			$uapt="Y";
		}


		$pos = strpos($viewCheckBoxString,'@@,');
		if ($pos === false) {
		} else {
			$noex="Y";
		}
		$pos = strpos($viewCheckBoxString,'@X,');
		if ($pos === false) {
		} else {
			$exne="Y";
		}
		foreach ($mdCol as $mdFld)  {
			$curdType = trim($mdFld['EVTYPE']);
			$curName = trim($mdFld['EVCODE']);
			$curText = trim($mdFld['EVDESC']);
			$wrkLvlChk = "";
			$wrkLvlChk = str_pad($curName, 2, '@');
			$wrkLvlChk = ($wrkLvlChk  . ',');
			$pos = strpos($viewCheckBoxString, $wrkLvlChk);
			if ($pos === false) {
			} else {
				${$wrkLvlChk} = "Y";
			}
		}
		unset($mdFld);
	}
}


print "\n <table $quickLinkTable> ";
print "\n   <tr> ";
print "\n       <td class=\"quickLinkTabs\"><a href=\"#SelectFormat\">Select</a></td> ";
if ($TASUPA != "" && $reportSelType == "E") {print "\n       <td class=\"quickLinkTabs\"><a href=\"#SupervisorApproval\">Supervisor Approval</a></td> ";}
if ($wildCardDisplay != "")  {
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#CurrentFilterCriteria\">Current Filter Criteria</a></td> ";
}
if ($inqOnly != "Y")  {
	print "\n       <td class=\"quickLinkTabs\"><a href=\"#RefineFilterCriteria\">Refine Filter Criteria</a></td> ";
}
print "\n   </tr> ";
print "\n   <tr> ";
print "\n       <td class=\"quickLinkTabs\"><a href=\"#ErrorLevel\">Error Level</a></td> ";
print "\n   <tr><td></td></tr> ";
print "\n </table> ";
print $hrTagAttr;

print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

print "\n <a name=\"SelectFormat\"></a> ";
print "\n <div id=\"showSelectFormat\">";
print "\n <fieldset class=\"legendBody\"> ";
print "\n     <legend class=\"legendTitle\">Select</legend> ";
require 'TopOfForm.php';
print "\n     <table $contentTable> ";
print "\n     <tr><td class=\"dsphdr\">Display Format</td>";
if ($reportSelType == "E") {
	print "\n     <td class=\"dspalph\">Employee</td>";
} else {
	print "\n     <td class=\"dspalph\">Group</td>";
}
print "\n     </tr> ";

print "\n     <tr><td class=\"dsphdr\">Base Selection Criteria On</td>";
if ($inqOnly == "") {
	if ($sbdo == "E") {
		print "\n     <td class=\"inputcode\"><input name=\"sbdo\" type=\"radio\" VALUE='E' CHECKED >Employee &nbsp; <input  name=\"sbdo\" type=\"radio\" VALUE='A' >Actual Worked</td> " ;
	} elseif ($sbdo == "A") {
		print "\n     <td class=\"inputcode\"><input name=\"sbdo\" type=\"radio\" VALUE='E' >Employee &nbsp; <input  name=\"sbdo\" type=\"radio\" VALUE='A' CHECKED >Actual Worked </td> " ;
	}
}else {
	if ($sbdo == "E") {
		print "\n     <td class=\"dspalph\">Employee</td>";
	} else {
		print "\n     <td class=\"dspalph\">Actual Worked</td>";
	}
}
print "\n   </tr> " ;

print "\n     <tr><td class=\"dsphdr\">Display Hours Format</td>";
if ($inqOnly == "") {
	if ($hfmt == "D") {
		print "\n     <td class=\"inputcode\"><input name=\"hfmt\" type=\"radio\" VALUE='D' CHECKED >Decimal &nbsp; <input  name=\"hfmt\" type=\"radio\" VALUE='T' >Time</td> " ;
	} elseif ($hfmt == "T") {
		print "\n     <td class=\"inputcode\"><input name=\"hfmt\" type=\"radio\" VALUE='D' >Decimal &nbsp; <input  name=\"hfmt\" type=\"radio\" VALUE='T' CHECKED >Time </td> " ;
	}
}else {
	if ($hfmt == "D") {
		print "\n     <td class=\"dspalph\">Decimal</td>";
	} else {
		print "\n     <td class=\"dspalph\">Time</td>";
	}
}
print "\n   </tr> " ;

print "\n     </table> ";
print "\n <script TYPE=\"text/javascript\">";
if (trim($SF)=="Y") {print "\n OpenSelectFormat()";}
else                  {print "\n CloseSelectFormat()";}
print "\n </script>";
print "\n     </fieldset> ";
print "\n     </div>";

if ($TASUPA != ' ' && $reportSelType == "E")  {
	// Supervisor Approval (hidden DIV)
	print "\n <a name=\"SupervisorApproval\"></a> ";
	print "\n <div id=\"showSupervisorApproval\">";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Supervisor Approval</legend>";
	require 'TopOfForm.php';
	print "\n <table $contentTable> ";
	$fldChecked=Field_Checked($uapt,"Y");
	print "\n <tr><td class=\"dsphdr\">Unapproved Time Only</td>";
	if ($inqOnly != "Y") {
		print "\n     <td class=\"inputalph\"><input type=\"checkbox\" name=\"uapt\" id=\"uapt\" value=\"Y\" $fldChecked>";
	} else {
		if ($fldChecked){
			print "\n                <td>$selectedImageSml</td>";
		}
	}
	print "\n </tr>";
	print "\n </table> ";
	print "\n <script TYPE=\"text/javascript\">";
	if (trim($SA)=="Y") {print "\n OpenSupervisorApproval()";}
	else                  {print "\n CloseSupervisorApproval()";}
	print "\n </script>";
	print "\n </fieldset> ";
	print "\n </div>";
}


// Copy of AdvSearchTopReport.php
print "\n <a name=\"CurrentFilterCriteria\"></a> ";
if ($wildCardDisplay != "") {
	print "\n <fieldset class=\"legendBody\">";
	print "\n     <legend class=\"legendTitle\">Current Filter Criteria</legend>";
	require 'TopOfForm.php';
	print "\n     <table $contentTable>";
	print "\n         <colgroup><col width=\"99%\"><col width=\"1%\">";
	if ($inqOnly !="Y") {
		print "\n         <tr><td class=\"toolbar\"><td>&nbsp;</td><td><a href=\"javascript:document.Chg.updateSearch.value='C'; check(document.Chg){$wildCardResetURL}\">$wildClearLrg</a></td></tr>";
	}
	print "\n         <tr><td class=\"searchcriteria\">$wildCardDisplay</td></tr>";
	print "\n     </table>";
	print "\n </fieldset>";
}

if ($inqOnly != "Y"){
	print "\n <a name=\"RefineFilterCriteria\"></a> ";
	print "\n <div id=\"showRefineFilterCriteria\">";
	print "\n <fieldset class=\"legendBody\">";
	print "\n     <legend  class=\"legendTitle\">Refine Filter Criteria</legend>";
	require 'TopOfForm.php';
	print "\n     <table $contentTable>";
	print "\n         <colgroup> <col width=\"89%\"> <col width=\"1%\">";
	print "\n        <tr><td class=\"searchCriteria\">";
	print "\n            <input type=\"hidden\" name=\"updateSearch\" value=\"\">";
	if ($wildCardDisplay != "" ){
		print "\n        Add To Filter:";
		print "\n        <input type=\"radio\" name=\"andOr\" value=\"and\" CHECKED> And";
		print "          <input type=\"radio\" name=\"andOr\" value=\"or\">Or &nbsp;";
	}
	print "\n            </td>";
	print "\n            <td class=\"toolbar\">";
	print "\n                <a href=\"javascript:document.Chg.updateSearch.value='Y'; check(document.Chg)\">$addToImage</a>";
	print "\n            </td> </tr> </table>";
	print "\n     <table $contentTable>";

	print "\n         <tr>";
	print "\n             <th class=\"dsphdr\">&nbsp;</th>";
	print "\n             <th class=\"dsphdr\">Operand</th>";
	if ($fromToSearch == "Y"){
		print "\n         <th class=\"dsphdr\">From</th>";
		print "\n         <th class=\"dsphdr\">To</th>";
	}else{
		print "\n         <th class=\"dsphdr\">Filter Data</th>";
	}
	print "\n         </tr>";
	// End of Copy of AdvSearchTopReport.php

	if ($reportSelType != "" && $inqOnly != "Y")  {
		// Refine Filter Criteria (hidden DIV)


		$operNbr = "osch";
		print "\n <tr><td class=\"dsphdr\">Schedule</td>";
		print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fsch\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}ScheduleSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=fsch&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"tsch\" size=\"3\" maxlength=\"3\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}ScheduleSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=tsch&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n </tr>";

		$operNbr = "otdt";
		print "\n <tr><td class=\"dsphdr\">Transaction Date</td>";
		print "\n     <td>"; require "OperSel_Num_Short.php"; print "</td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"ftdt\" id=\"ftdt\" value=\"$ftdt\" size=\"6\" maxlength=\"6\">";
		print "\n                  <a href=\"javascript:calWindow('ftdt');\">$calendarImage</a></td>";
		print "\n </tr>";

		$operNbr = "ocmp";
		print "\n <tr><td class=\"dsphdr\">Company/Facility</td>";
		print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fcmp\" size=\"2\" maxlength=\"2\">";
		print "\n                           / <input type=\"text\" name=\"ffac\" size=\"4\" maxlength=\"4\">";
		if ($HDPERL>0 || $HDPRRL>0) {
			print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=fcmp&amp;fldFac=ffac&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		} else {
			print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=fcmp&amp;fldFac=ffac&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		}
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"tcmp\" size=\"2\" maxlength=\"2\">";
		print "\n                           / <input type=\"text\" name=\"tfac\" size=\"4\" maxlength=\"4\">";
		if ($HDPERL>0 || $HDPRRL>0) {
			print "\n                             <a href=\"{$homeURL}{$phpPath}HRCoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=tcmp&amp;fldFac=tfac&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		} else {
			print "\n                             <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=tcmp&amp;fldFac=tfac&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		}
		print "\n </tr>";

		$operNbr = "ohdp";
		print "\n <tr><td class=\"dsphdr\">Home Department</td>";
		print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
		if ($HDPRRL > 0 or $HDPERL > 0) {
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fhdp\" size=\"5\" maxlength=\"5\"> ";
			print "\n                             <a href=\"{$homeURL}{$phpPath}DepartmentSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=fhdp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"thdp\" size=\"5\" maxlength=\"5\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}DepartmentSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=thdp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		}  else  {
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fhdp\" size=\"5\" maxlength=\"5\"> ";
			print "\n                                </td> ";
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"thdp\" size=\"5\" maxlength=\"5\"> ";
			print "\n                                </td> ";
		}
		print "\n </tr>";

		$operNbr = "oemp";
		print "\n <tr><td class=\"dsphdr\">Employee Number</td>";
		print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"femp\" size=\"5\" maxlength=\"5\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldEmpl=femp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"temp\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}EmployeeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldEmpl=temp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n </tr>";

		$operNbr = "oord";
		print "\n <tr><td class=\"dsphdr\">Order Number</td>";
		print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"ford\" size=\"9\" maxlength=\"9\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}MfgOrderSearch.php{$genericVarBase}&amp;forPlant=0&amp;docName=Chg&amp;fldorder=ford\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"tord\" size=\"9\" maxlength=\"9\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}MfgOrderSearch.php{$genericVarBase}&amp;forPlant=0&amp;docName=Chg&amp;fldorder=tord\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n </tr>";

		$operNbr = "ogrp";
		print "\n <tr><td class=\"dsphdr\">Group Number</td>";
		print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fgrp\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}GroupNumberSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=fgrp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"tgrp\" size=\"5\" maxlength=\"5\">";
		print "\n                             <a href=\"{$homeURL}{$phpPath}GroupNumberSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=tgrp&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
		print "\n </tr>";

		if ($HDMERL > 0) {

			$operNbr = "oplt";
			print "\n <tr><td class=\"dsphdr\">Plant Number</td>";
			print "\n     <td>"; require "OperSel_Num2_Short.php"; print "</td>";
			print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"fplt\" value=\"$fplt\" size=\"3\" maxlength=\"3\"> ";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=fplt&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
			print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"tplt\" value=\"$tplt\" size=\"3\" maxlength=\"3\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=tplt&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
			print "\n     <td><INPUT TYPE=\"hidden\" ID=\"frPltName\" NAME=\"frPltName\"></td> ";
			print "\n     <td><INPUT TYPE=\"hidden\" ID=\"toPltName\" NAME=\"toPltName\"></td> ";
			print "\n </tr>";

			$forPlant = $fplt;

			$operNbr = "omdp";
			print "\n <tr><td class=\"dsphdr\">Manufacturing Department</td>";
			print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fmdp\" size=\"5\" maxlength=\"5\"> ";
			print "\n                             <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;tag=REPORT&amp;forPlant=$forPlant&amp;docName=Chg&amp;fldPlant=fplt&amp;fldPltName=frPltName&amp;flddept=fmdp&amp;fldWC=fwc&amp;fldDesc=none \" onclick=\"$searchWinVar\">$searchImage </a></td> ";
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"tmdp\" size=\"5\" maxlength=\"5\">";
			print "\n                             <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;tag=REPORT&amp;forPlant=forPlant&amp;docName=Chg&amp;fldPlant=tplt&amp;fldPltName=toPltName&amp;flddept=tmdp&amp;fldWC=twc&amp;fldDesc=none \" onclick=\"$searchWinVar\">$searchImage </a></td> ";
			print "\n </tr>";

			$operNbr = "owc";
			print "\n <tr><td class=\"dsphdr\">Work Center</td>";
			print "\n     <td>"; require "OperSel_Alph2_Short.php"; print "</td>";
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fwc\" size=\"5\" maxlength=\"5\"> ";
			print "\n                            </td> ";
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"twc\" size=\"5\" maxlength=\"5\">";
			print "\n                            </td> ";
			print "\n </tr>";
		}

		print "\n </table> ";
		print "\n <script TYPE=\"text/javascript\">";
		if (trim($RF)=="Y") {print "\n OpenRefineFilterCriteria()";}
		else                  {print "\n CloseRefineFilterCriteria()";}
		print "\n </script>";
		print "\n </fieldset> ";
		print "\n </div>";
	}
}


if ($reportSelType == "E" || $reportSelType == "G")  {
	// Error Level (hidden DIV)
	print "\n <a name=\"ErrorLevel\"></a> ";
	print "\n <div id=\"showErrorLevel\">";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Error Level ";
	Print "\n </legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable> ";

	print "\n <tr><td class=\"colhdr\">Description</td><td class=\"colhdr\">Code</td>";
	print "\n </tr>";
	$fldChecked=Field_Checked($noex,"Y");
	print "\n <tr><td class=\"dsphdr\">blank; No exceptions</td><td class=\"dsphdr\"></td>";
	if ($inqOnly == "") {
		print "\n     <td class=\"inputalph\"><input type=\"checkbox\" name=\"noex\" id=\"noex\" value=\"Y\" $fldChecked>";
	} else {
		if ($fldChecked){
			print "\n                <td>$selectedImageSml</td>";
		}
	}
	print "\n </tr>";
	$fldChecked=Field_Checked($exne,"Y");
	print "\n <tr><td class=\"dsphdr\">Exceptions, No Err Level</td><td class=\"dsphdr\">X</td>";
	if ($inqOnly == "") {
		print "\n     <td class=\"inputalph\"><input type=\"checkbox\" name=\"exne\" id=\"exne\" value=\"Y\" $fldChecked>";
	} else {
		if ($fldChecked){
			print "\n                <td>$selectedImageSml</td>";
		}
	}
	print "\n </tr>";
	foreach ($mdCol as $mdFld)  {
		$curdType = trim($mdFld['EVTYPE']);
		$curName = trim($mdFld['EVCODE']);
		$curText = trim($mdFld['EVDESC']);
		$wrkLvlChk = "";
		$wrkLvlChk = str_pad($curName, 2, '@');
		$wrkLvlChk = ($wrkLvlChk  . ',');
		$pos = strpos($viewCheckBoxString, $wrkLvlChk);
		if ($pos === false) {
		} else {
			${$wrkLvlChk} = "Y";
		}
		$fldChecked=Field_Checked(${$wrkLvlChk},"Y");
		print "\n <tr><td class=\"dsphdr\">$curText</td><td class=\"dsphdr\">$curName</td>";
		if ($inqOnly == "") {
			print "\n     <td class=\"inputalph\"><input type=\"checkbox\" name=\"$wrkLvlChk\" id=\"$wrkLvlChk\" value=\"Y\" $fldChecked>";
		} else {
			if ($fldChecked){
				print "\n                <td>$selectedImageSml</td>";
			}
		}
		print "\n </tr>";
	}
	unset($mdFld);
	print "\n </table> ";
	print "\n <script TYPE=\"text/javascript\">";
	if (trim($EL)=="Y") {print "\n OpenErrorLevel()";}
	else                  {print "\n CloseErrorLevel()";}
	print "\n </script>";
	print "\n </fieldset> ";
	print "\n </div>";
}

$envProgram = $submitEnvProgram;
$envPrinter = $submitEnvPrinter;
require 'ScheduleJob.php';

if ($inqOnly != "Y") {
	$allowScheduleJob = "N";
	require 'FilterBottom.php';
}
print "\n $hrTagAttr ";

if ($focusField !="") {
	print "\n <script TYPE=\"text/javascript\"> ";
	print "\n document.Chg.$focusField.focus(); ";
	print "\n </script> ";
}
print "\n </form>";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
if ($_SESSION['gotoFilter']=="Y") {
	$_SESSION['gotoFilter']="";
	print "\n <script TYPE=\"text/javascript\"> ";
	print "\n window.location.hash='CurrentFilterCriteria'; ";
	print "\n </script> ";
}
require_once 'Trailer.php';
print "</body> </html>";
}


if (is_null($reportSelType)) {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'CheckEnterChg.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "TIMEREVIEWPROMPT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";

	print "\n <table $contentTable>";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>$page_title</h1></td>";
	print "\n     <td class=\"toolbar\">";
	$medIcon= "Y";
	require 'HelpPage.php';
	print "\n </td></tr></table>";
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;
	$focusField="reportSelType";
	require_once 'ErrorDisplay.php';

	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\">";

	print "\n     <table $contentTable> ";
	print "\n     <tr><td class=\"dsphdr\">Select Display Format</td>";
	print "\n     <td class=\"inputcode\"><input name=\"reportSelType\" type=\"radio\" VALUE='E' onClick= \"window.location.href='{$homeURL}{$phpPath}{$scriptName}{$genericVarBase}&amp;reportSelType=E&amp;tag=REPORT'\">Employee   &nbsp; <input  name=\"reportSelType\" type=\"radio\" VALUE='G' onClick= \"window.location.href='{$homeURL}{$phpPath}{$scriptName}{$genericVarBase}&amp;reportSelType=G&amp;tag=REPORT'\">Group</td> " ;
	print "\n     </tr>";
	print "\n     </table> ";
	print "\n $hrTagAttr ";

	print "\n </form>";
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";


	if ($reportSelType) {
		if ($reportSelType == 'E') {
			$tblID = 1;
		} else {
			$tblID = 2;
		}
	}

	$resetSelectionFlag = "";

	if ($_SESSION['gotoFilter']=="Y") {
		$_SESSION['gotoFilter']="";
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n window.location.hash='CurrentFilterCriteria'; ";
		print "\n </script> ";

	}
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}



if ($tag == "WILDCARD" || $tag == "Edit_Data") {
	$andOr = $_POST['andOr'];

	if ($reportSelType) {
		$_SESSION['reportSelType']=$reportSelType;


	}

	require 'FilterInit.php';
	require 'FilterDefault.php';
	$wildCardSearch0  	  = $wildCardSearch;
	$wildCardDisplay0     = $wildCardDisplay;
	$wildCardTemp0  	  = $wildCardTemp;
	$wildDisplayTemp0 	  = $wildDisplayTemp;
	$$viewCheckBoxString  = $viewCheckBoxString;
	require 'WildCardClear.php';

	if ($_POST['updateSearch'] == "Y") {$_SESSION['gotoFilter']="Y";}

	//  start the build of edtVar
	$edtVar = "";
	if (!is_null($forEmployee)) {
        Concat_Field("@@updt", '');
	} else {
        Concat_Field("@@updt", $_POST['updateSearch']);
    }
	Concat_Field("@@updt", $_POST['updateSearch']);
	Concat_Field("@@sfmt", $_GET['reportSelType']);
	Concat_Field("@@sbdo", $_POST['sbdo']);
	Concat_Field("@@hfmt", $_POST['hfmt']);
	Concat_Field("@@uapt", $_POST['uapt']);

	if ($_GET['reportSelType'] == "E" || $_GET['reportSelType'] == "G") {
		$SF="Y";
	}
	if ($_GET['reportSelType'] == "E" && $TASUPA != "") {
		$SA="Y";
	}
	if ($_GET['reportSelType'] == "E" || $_GET['reportSelType'] == "G") {
		$RF="Y";
		$EL="Y";
	}
	Concat_Field("@@sf@@", $SF);
	Concat_Field("@@sa@@", $SA);
	Concat_Field("@@rf@@", $RF);
	Concat_Field("@@el@@", $EL);

	$wrkChkBox = "";
	Concat_field("@@elvl", "" );
	if ($_POST['noex'] == "Y") {
		$edtVar = ($edtVar . "  " . ",") ;
		$wrkChkBox = ($wrkChkBox . "@@" . ",");
	}
	if ($_POST['exne'] == "Y") {
		$edtVar = ($edtVar . " X" . ",") ;
		$wrkChkBox = ($wrkChkBox . "@X" . ",");
	}
	foreach ($mdCol as $mdFld)  {
		$curdType = trim($mdFld['EVTYPE']);
		$curName = trim($mdFld['EVCODE']);
		$curText = trim($mdFld['EVDESC']);
		$wrkLvlChk = "";
		$wrkLvlChk1 = "";
		$wrkLvlChk1 = str_pad($curName, 2, '@');
		$wrkLvlChk = ($wrkLvlChk1 . ',');
		if ($_POST[$wrkLvlChk] == "Y") {
			$edtVar = ($edtVar . $curName . ",") ;
			$wrkChkBox = ($wrkChkBox . $wrkLvlChk);
		}
	}
	unset($mdFld);
	Concat_Field("@@noex", $_POST['noex']);
	Concat_Field("@@exne", $_POST['exne']);
	foreach ($mdCol as $mdFld)  {
		$curdType = trim($mdFld['EVTYPE']);
		$curName = trim($mdFld['EVCODE']);
		$curText = trim($mdFld['EVDESC']);
		$wrkLvlChk = "";
		$wrkLvlChk = ('er' . $curName);
		$dwrkLvlChk = ('@@' . $wrkLvlChk);
		$dwrkLvlChk = str_pad($dwrkLvlChk, 6, '@');
		Concat_Field("$dwrkLvlChk", $_POST[$wrkLvlChk]);
	}
	unset($mdFld);

	if ($_POST['uapt'] == "Y") {
		$edtVar = ($edtVar . "  " . ",") ;
		$wrkChkBox = ($wrkChkBox . "@U" . ",");
	}
	if ($_POST['sbdo'] == "A") {
		$edtVar = ($edtVar . "  " . ",") ;
		$wrkChkBox = ($wrkChkBox . "@A" . ",");
	}
	if ($_POST['sbdo'] == "E") {
		$edtVar = ($edtVar . "  " . ",") ;
		$wrkChkBox = ($wrkChkBox . "@E" . ",");
	}

	require 'WildCardClear.php';

	//  build @@fil0   --  from HREMPL, send if $@@uapt <> Y
	if ($_POST['uapt'] != "Y" && $reportSelType =="E") {

		$wildCardSearch  = $wildCardSearch2;
		$wildCardTemp  = $wildCardTemp2;
		$wildCardDisplay = $wildCardDisplay2;
		$wildDisplayTemp = $wildDisplayTemp2;
		$sylflwSQL = "LWXHND='$profileHandle' and LWSCRNU='$filterScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=2";

        if (!is_null($forEmployee)) {
            $wildCardSearch = '';
            $wildCardDisplay = '';
        }

		$returnValue=Range_WildCard("EMSCHD", "Schedule", $_POST['fsch'], $_POST['tsch'], "", $_POST['osch'], "N");
		$returnValue=Range_WildCard_CoFac("EMCOMP", "EMFACL", "Company/Facility", $_POST['fcmp'], $_POST['ffac'], $_POST['tcmp'], $_POST['tfac'], $_POST['ocmp']);
		$returnValue=Range_WildCard("EMDEPT", "Home Department", $_POST['fhdp'], $_POST['thdp'], "", $_POST['ohdp'], "A");
		$returnValue=Range_WildCard("EMEMPL", "Employee", $_POST['femp'], $_POST['temp'], "", $_POST['oemp'], "N");
		$returnValue=Range_WildCard("EMHGRP", "Group Number", $_POST['fgrp'], $_POST['tgrp'], "", $_POST['ogrp'], "N");

		require 'WildCardUpdateReport.php';

		$wildCardSearch2  = $wildCardSearch;
		$wildCardDisplay2  = $wildCardDisplay;
		$wildCardTemp2  = $wildCardTemp;
		$wildDisplayTemp2  = $wildDisplayTemp;


		if ($wildCardSearch2 != "") {
			if (strtoupper(substr(trim($wildCardSearch2),0,3))=="AND") {$wildCardSearch2=substr(trim($wildCardSearch2),3);} // remove "and"
			Concat_Field("@@fil0", $wildCardSearch2);

		}
	}


	//  build @@filb   --  from ETJLF06, always send

	$wildCardSearch  = $wildCardSearch1;
	$wildCardTemp  = $wildCardTemp1;
	$wildCardDisplay = $wildCardDisplay1;
	$wildDisplayTemp = $wildDisplayTemp1;
	$sylflwSQL = "LWXHND='$profileHandle' and LWSCRNU='$filterScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=1";

    if (!is_null($forEmployee)) {
        $wildCardSearch = '';
        $wildCardDisplay = '';
    }

	if (is_null($forEmployee) && $_POST['updateSearch'] == "Y") {$_SESSION['gotoFilter']="Y";}
	$returnValue=Range_WildCard("EMSCHD", "Schedule", $_POST['fsch'], $_POST['tsch'], "", $_POST['osch'], "N");
	$returnValue=Build_WildCard("EHDATE", "Transaction Date", $_POST['ftdt'], "", $_POST['otdt'], "I");
	$returnValue=Range_WildCard_CoFac("EMCOMP", "EMFACL", "Company/Facility", $_POST['fcmp'], $_POST['ffac'], $_POST['tcmp'], $_POST['tfac'], $_POST['ocmp']);
	if ($_POST['sbdo'] == "A") {
		$returnValue=Range_WildCard("EHDEPT", "Home Department", $_POST['fhdp'], $_POST['thdp'], "", $_POST['ohdp'], "A");
	} else {
		$returnValue=Range_WildCard("EMDEPT", "Home Department", $_POST['fhdp'], $_POST['thdp'], "", $_POST['ohdp'], "A");
	}
	$returnValue=Range_WildCard("EMEMPL", "Employee", $_POST['femp'], $_POST['temp'], "", $_POST['oemp'], "N");
	$returnValue=Range_WildCard("EHORD", "Order", $_POST['ford'], $_POST['tord'], "", $_POST['oord'], "N");
	if ($_POST['sbdo'] == "A") {
		$returnValue=Range_WildCard("EHGRP", "Group Number", $_POST['fgrp'], $_POST['tgrp'], "", $_POST['ogrp'], "N");
		$returnValue=Range_WildCard("EHPLNT", "Plant Number", $_POST['fplt'], $_POST['tplt'], "", $_POST['oplt'], "N");
		$returnValue=Range_WildCard("EHDEPT", "Mfg Department", $_POST['fmdp'], $_POST['tmdp'], "", $_POST['odpt'], "A");
		$returnValue=Range_WildCard("EHWC", "Work Center", $_POST['fwc'], $_POST['twc'], "", $_POST['owc'], "N");
	} else {
		$returnValue=Range_WildCard("EMHGRP", "Group Number", $_POST['fgrp'], $_POST['tgrp'], "", $_POST['ogrp'], "N");
		$returnValue=Range_WildCard("EMPLNT", "Plant Number", $_POST['fplt'], $_POST['tplt'], "", $_POST['oplt'], "N");
		$returnValue=Range_WildCard("EMMDPT", "Mfg Department", $_POST['fmdp'], $_POST['tmdp'], "", $_POST['odpt'], "A");
		$returnValue=Range_WildCard("EMWC", "Work Center", $_POST['fwc'], $_POST['twc'], "", $_POST['owc'], "N");

	}
	if ($TASUPA != ""  && $_POST['uapt'] == "Y" && $_POST['updateSearch'] != "Y" && $_POST['saveSelection'] != "Y") {
		$returnValue=Range_WildCard("EHREEC", "Supervisor Approval Code", $TASUPA, $TASUPA, "", "=", "A");
	}

	require 'WildCardUpdateReport.php';

	$wildCardSearch1  = $wildCardSearch;
	$wildCardDisplay1  = $wildCardDisplay;
	$wildCardTemp1  = $wildCardTemp;
	$wildDisplayTemp1  = $wildDisplayTemp;


	if (strtoupper(substr(trim($wildCardSearch1),0,3))=="AND") {$wildCardSearch1=substr(trim($wildCardSearch1),3);} // remove "and"
	Concat_Field("@@filb", $wildCardSearch1);
	$wildCardDisplay=str_ireplace('&nbsp;',' ',$wildCardDisplay);

	//  build @@fila   --  from SIJLF02, always send
	$wildCardSearch  = $wildCardSearch0;
	$wildCardTemp    = $wildCardTemp0;
	$wildCardDisplay = $wildCardDisplay0;
	$wildDisplayTemp = $wildDisplayTemp0;
	if ($wrkChkBox != "") {
		$viewCheckBoxString = $wrkChkBox;
	}
	$sylflwSQL = "LWXHND='$profileHandle' and LWSCRNU='$filterScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=0";

	if (!is_null($forEmployee)) {
        $wildCardSearch = '';
        $wildCardDisplay = '';

        require 'stmtSQLClear.php';
        $stmtSQL = " Delete From ETBLWK Where BWXHND='$profileHandle'";
        $status = db2_exec($i5Connect->getConnection (), $stmtSQL);
    }

	if ($_POST['sbdo'] == "A") {
		$returnValue=Range_WildCard("LBSCHD", "Schedule", $_POST['fsch'], $_POST['tsch'], "", $_POST['osch'], "N");
	} else {
		$returnValue=Range_WildCard("EMSCHD", "Schedule", $_POST['fsch'], $_POST['tsch'], "", $_POST['osch'], "N");
	}
	$returnValue=Build_WildCard("LBDATE", "Transaction Date", $_POST['ftdt'], "", $_POST['otdt'], "I");
	$returnValue=Range_WildCard_CoFac("EMCOMP", "EMFACL", "Company/Facility", $_POST['fcmp'], $_POST['ffac'], $_POST['tcmp'], $_POST['tfac'], $_POST['ocmp']);
	if ($_POST['sbdo'] == "A") {
		$returnValue=Range_WildCard("LBDEPT", "Home Department", $_POST['fhdp'], $_POST['thdp'], "", $_POST['ohdp'], "A");
	} else {
		$returnValue=Range_WildCard("EMDEPT", "Home Department", $_POST['fhdp'], $_POST['thdp'], "", $_POST['ohdp'], "A");
	}
	$returnValue=Range_WildCard("EMEMPL", "Employee", $_POST['femp'], $_POST['temp'], "", $_POST['oemp'], "N");
	$returnValue=Range_WildCard("LBORD", "Order", $_POST['ford'], $_POST['tord'], "", $_POST['oord'], "N");
	if ($_POST['sbdo'] == "A") {
		$returnValue=Range_WildCard("LBGRP", "Group Number", $_POST['fgrp'], $_POST['tgrp'], "", $_POST['ogrp'], "N");
		$returnValue=Range_WildCard("LBPLT", "Plant Number", $_POST['fplt'], $_POST['tplt'], "", $_POST['oplt'], "N");
		$returnValue=Range_WildCard("LBDEPT", "Mfg Department", $_POST['fmdp'], $_POST['tmdp'], "", $_POST['odpt'], "A");
		$returnValue=Range_WildCard("LBWC", "Work Center", $_POST['fwc'], $_POST['twc'], "", $_POST['owc'], "N");
	} else {
		$returnValue=Range_WildCard("EMHGRP", "Group Number", $_POST['fgrp'], $_POST['tgrp'], "", $_POST['ogrp'], "N");
		$returnValue=Range_WildCard("EMPLNT", "Plant Number", $_POST['fplt'], $_POST['tplt'], "", $_POST['oplt'], "N");
		$returnValue=Range_WildCard("EMMDPT", "Mfg Department", $_POST['fmdp'], $_POST['tmdp'], "", $_POST['odpt'], "A");
		$returnValue=Range_WildCard("EMWC", "Work Center", $_POST['fwc'], $_POST['twc'], "", $_POST['owc'], "N");
	}
	if ($TASUPA != ""  && $_POST['uapt'] == "Y" && $_POST['updateSearch'] != "Y" && $_POST['saveSelection'] != "Y") {
		$returnValue=Range_WildCard("LBREEC", "Supervisor Approval Code", $TASUPA, $TASUPA, "", "=", "A");
	}

	require 'WildCardUpdateReport.php';

	$wildCardSearch0  	  = $wildCardSearch;
	$wildCardDisplay0     = $wildCardDisplay;
	$wildCardTemp0  	  = $wildCardTemp;
	$wildDisplayTemp0 	  = $wildDisplayTemp;
	$$viewCheckBoxString0 = $$viewCheckBoxString;

	require 'stmtSQLClear.php';
	$stmtSQL = " Update SYLFLW Set LWCVAR='$viewCheckBoxString' Where $sylflwSQL With NC";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	if ($_POST['updateSearch'] != "Y" && $_POST['saveSelection'] != "Y") {
		if (strtoupper(substr(trim($wildCardSearch0),0,3))=="AND") {$wildCardSearch0=substr(trim($wildCardSearch0),3);} // remove "and"
		Concat_Field("@@fila", $wildCardSearch0); Concat_Field("@@fild", $wildCardDisplay0);
	} else {
		if (strtoupper(substr(trim($wildCardSearch0),0,3))=="AND") {$wildCardSearch0=substr(trim($wildCardSearch0),3);} // remove "and"
		Concat_Field("@@fila", $wildCardSearch0); Concat_Field("@@fild", $wildCardDisplay0);
	}
	$edtVar .= "}{";
	$$viewCheckBoxString  	   =   $$viewCheckBoxString0;

	require_once 'ScheduleJobConcat.php';   // Schedule Entries Values

	if (!is_null($forEmployee) || ($_POST['updateSearch'] != "Y" && $_POST['saveSelection'] != "Y")) {
		$returnValue=Selection_Edit_Handle("HETBLI_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
		$submitSchedule=$returnValue['submitSchedule'];
		$errFound      =$returnValue['errFound'];
		$edtVar        =$returnValue['edtVar'];
		$errVar        =$returnValue['errVar'];
		$wrnVar        =$returnValue['wrnVar'];

	}

	// Copy of SubmitScheduleUpdate.php
	if (($errFound == "" && $saveSelection == "Y") || $rtvSelection == "Y") {
		$errFound = "Y";
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $typeReset);
		$_SESSION['saveSel']="Y";
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;errFound=" . urlencode($errFound) . "&amp;wrnVar=" . urlencode($wrnVar) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . "\"> ";
		print "\n <script TYPE=\"text/javascript\"> ";
		require_once 'NewWindowOpen.php';
		$wrkVar  = str_replace("&amp;", "&", $genericVarBase);
		if ($reportSelType == 'E') {
			$tblID = 1;
		} else{
			$tblID = 2;
		}

		$sylMaxSeq = 3;
		print "\n NewWindow('{$homeURL}{$phpPath}FilterSelection.php{$wrkVar}&tag=REPORT&fromTblID=" . urlencode($tblID) . "&fromPagID=" . urlencode($pagID) . "&fromScript=" . urlencode($scriptName) . "&pageHeading1=" . urlencode($page_title) . "&sylMaxSeq=" . urlencode($sylMaxSeq) . "&maintenanceCode=C','search_win','$searchWinPctH','$searchWinPctW','$searchWinSB','$searchWinRZ','$searchWinTB','$searchWinMB','$searchWinST' ); ";
		print "\n </script> ";

	} elseif ($errFound == "" && $saveSelection != "S") {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $typeReset);
		if ($submitSchedule != "S") {$submitSchedule = "M";}

		require 'SubmitScheduleMessage.php';
		$_SESSION['hrsFmt']=$_POST['hfmt'];
		if (!is_null($forEmployee) || ($_POST['updateSearch'] != "Y" && $_POST['updateSearch'] != "C" && $_POST['saveSelection'] != "Y")) {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}hdlist.php{$scriptVarBase}&amp;tblID=229&amp;fKey1=BWXHND&amp;fVal1=@@xhnd \" > ";
		} else {
			print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;useSession=Y&amp;errFound=" . urlencode($errFound) . "&amp;wrnVar=" . urlencode($wrnVar) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . "\"> ";
		}

	} else {
		if ($saveSelection == "S" && $errFound == "") {ErrVarErr($profileHandle, $typeReset);}

		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL=${$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;errFound=" . urlencode($errFound) . "&amp;wrnVar=" . urlencode($wrnVar) . "&amp;scheduleJobSwitch=Y&amp;timeStamp=" .  urlencode($_SERVER['REQUEST_TIME']) . "\"> ";
	}
	// End Of Copy of SubmitScheduleUpdate.php
	exit;
}


if ($tag == "REFRESH") {

	if ($reportSelType) {
		$_SESSION['reportSelType']=$reportSelType;
	}
	require 'FilterInit.php';
	require 'FilterDefault.php';
	$wildCardSearch0  	  = $wildCardSearch;
	$wildCardDisplay0     = $wildCardDisplay;
	$wildCardTemp0  	  = $wildCardTemp;
	$wildDisplayTemp0 	  = $wildDisplayTemp;
	$$viewCheckBoxString  = $viewCheckBoxString;
	require 'WildCardClear.php';

	if ($_POST['updateSearch'] == "Y") {$_SESSION['gotoFilter']="Y";}

	//  start the build of edtVar
	$edtVar = "";
	Concat_Field("@@updt", " ");
	Concat_Field("@@sfmt", $_SESSION['reportSelType']);
	Concat_Field("@@hfmt", $hfmt);
	$pos = strpos($viewCheckBoxString,'@A,');
	if ($pos === false) {
	} else {
		$sbdo="A";
	}
	$pos = strpos($viewCheckBoxString,'@E,');
	if ($pos === false) {
	} else {
		$sbdo="E";
	}
	Concat_Field("@@sbdo", $sbdo);
	$pos = strpos($viewCheckBoxString,'@U,');
	if ($pos === false) {
	} else {
		$uapt="Y";
	}
	Concat_Field("@@uapt", $uapt);

	Concat_Field("@@sf@@", 'Y');
	Concat_Field("@@sa@@", 'Y');
	Concat_Field("@@rf@@", 'Y');
	Concat_Field("@@el@@", 'Y');

	Concat_field("@@elvl", "" );
	$pos = strpos($viewCheckBoxString,'@@,');
	if ($pos === false) {
	} else {
		$edtVar = ($edtVar . "  " . ",") ;
	}
	$pos = strpos($viewCheckBoxString,'@X,');
	if ($pos === false) {
	} else {
		$edtVar = ($edtVar . " X" . ",") ;
	}

	$mdCol = Rtv_Error_Levels();

	foreach ($mdCol as $mdFld)  {
		$curdType = trim($mdFld['EVTYPE']);
		$curName = trim($mdFld['EVCODE']);
		$curText = trim($mdFld['EVDESC']);
		$wrkLvlChk = "";
		$wrkLvlChk1 = "";
		$wrkLvlChk1 = str_pad($curName, 2, '@');
		$wrkLvlChk = ($wrkLvlChk1 . ',');
		$pos = strpos($viewCheckBoxString,$wrkLvlChk);
		if ($pos === false) {
		} else {
			$edtVar = ($edtVar . $curName . ",") ;
		}
	}
	unset($mdFld);
	require 'WildCardClear.php';
	//  build @@fil0   --  from HREMPL, send if $@@uapt <> Y
	if ($uapt != "Y" && $reportSelType =="E") {

		$wildCardSearch  = $wildCardSearch2;
		$wildCardTemp  = $wildCardTemp2;
		$wildCardDisplay = $wildCardDisplay2;
		$wildDisplayTemp = $wildDisplayTemp2;
		$sylflwSQL = "LWXHND='$profileHandle' and LWSCRNU='$filterScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=2";
		if ($wildCardSearch2 != "") {
			if (strtoupper(substr(trim($wildCardSearch2),0,3))=="AND") {$wildCardSearch2=substr(trim($wildCardSearch2),3);} // remove "and"
			Concat_Field("@@fil0", $wildCardSearch2);

		}
	}

	//  build @@filb   --  from ETJLF06, always send
	$wildCardSearch  = $wildCardSearch1;
	$wildCardTemp  = $wildCardTemp1;
	$wildCardDisplay = $wildCardDisplay1;
	$wildDisplayTemp = $wildDisplayTemp1;
	$sylflwSQL = "LWXHND='$profileHandle' and LWSCRNU='$filterScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=1";
	if (strtoupper(substr(trim($wildCardSearch1),0,3))=="AND") {$wildCardSearch1=substr(trim($wildCardSearch1),3);} // remove "and"
	Concat_Field("@@filb", $wildCardSearch1);

	//  build @@fila   --  from SIJLF02, always send
	$wildCardSearch  = $wildCardSearch0;
	$wildCardTemp    = $wildCardTemp0;
	$wildCardDisplay = $wildCardDisplay0;
	$wildDisplayTemp = $wildDisplayTemp0;
	if ($wrkChkBox != "") {
		$viewCheckBoxString = $wrkChkBox;
	}
	$sylflwSQL = "LWXHND='$profileHandle' and LWSCRNU='$filterScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=0";
	if (strtoupper(substr(trim($wildCardSearch0),0,3))=="AND") {$wildCardSearch0=substr(trim($wildCardSearch0),3);} // remove "and"
	Concat_Field("@@fila", $wildCardSearch0); Concat_Field("@@fild", $wildCardDisplay0);


	$edtVar .= "}{";
	$$viewCheckBoxString  	   =   $$viewCheckBoxString0;

	require_once 'ScheduleJobConcat.php';   // Schedule Entries Values


	$submitSchedule = '0';

	$returnValue=Selection_Edit_Handle("HETBLI_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar);
	$submitSchedule=$returnValue['submitSchedule'];
	$errFound      =$returnValue['errFound'];
	$edtVar        =$returnValue['edtVar'];
	$errVar        =$returnValue['errVar'];
	$wrnVar        =$returnValue['wrnVar'];


	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}TimeReviewInqEmp.php{$scriptVarBase}&amp;fromEmid=" . urlencode(trim($fromEmid)) . "&amp;pdwk=" . urlencode(trim($pdwk)) . "\" > ";

	exit;
}




function Rtv_Error_Levels() {
	global $i5Connect;
	$stmtSQL = " Select * From HDEVNT Where EVTYPE='L' Order By EVCODE ";
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	while ($row = db2_fetch_assoc($sqlResult)){$mdCol[] = $row;}
	return $mdCol;
}

?>	
