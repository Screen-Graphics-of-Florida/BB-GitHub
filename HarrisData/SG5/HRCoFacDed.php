<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$tblID    = 0;
$pagID    = 0;
$fromCo   = $_GET['fromCo'];
$fromFac  = $_GET['fromFac'];

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "H/R Company/Facility Deductions";
$scriptName     = "HRCoFacDed.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromCo=" . urlencode(trim($fromCo)) . "&amp;fromFac=" . urlencode(trim($fromFac));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL     = "{$baseURL}&amp;startRow=" . urlencode($startRow);
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("CDDDNO","N","Number"));
$programName   = "HHRCDM";
$_SESSION[$fromURL]=$currentURL;

require_once 'FilterInit.php';
require_once 'FilterDefault.php';


if ($tag != "EXPORT") {
	if ($tag == "MASTERSEARCH") {
		require_once ($docType);
		print "\n <html> <head>";
		$formName = "Search";
		require_once ($headInclude);
		print "\n <script TYPE=\"text/javascript\">";
		require_once 'Menu.js';
		require_once 'NumEdit.php';
		require_once 'CheckEnterSearch.php';
		print "\n function validate(searchForm) {";
		print "\n if (editNum(document.Search.srchNumber, 3, 0) && ";
		print "\n     editNum(document.Search.srchCycle, 1, 0) && ";
		print "\n     editNum(document.Search.srchAcct, 4, 0) && ";
		print "\n     editNum(document.Search.srchSub, 4, 0) ) ";
		print "\n     return true;";
		print "\n }";
		print "\n </script>";

		$scriptType = "L";    // L=List, S=Search, I=Inquiry
		$pageID = "HRCOFACDEDSEARCH";
		require_once 'AdvSearchTop.php';
		Build_AdvSrch_Entry("Number","srchNumber","","operNumber","opersel_num_short","N","3","3");
		Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","10","10");
		Build_AdvSrch_Entry("Cycle","srchCycle","","operCycle","opersel_num_short","N","1","1");
		Build_AdvSrch_Entry("G/L Account","srchAcct","","operAcct","opersel_num_short","N","4","4");
		Build_AdvSrch_Entry("Sub","srchSub","","operSub","opersel_num_short","N","4","4");
		Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","30","30");

		$focusField = "srchNumber";
		require_once 'AdvSearchBottom.php';
	}

	$maxRows = $dspMaxRows;

	if ($tag == "ORDERBY"){
		if     ($sequence == "Number")      {$orby = array(array("CDDDNO","N","Number"),array("HVDDNM","A","Name"));}
		elseif ($sequence == "Name")        {$orby = array(array("upper(HVDDNM)","A","Name"),array("CDDDNO","N","Number"));}
		elseif ($sequence == "Cycle")       {$orby = array(array("CDDCYC","N","Cycle"));}
		elseif ($sequence == "Account")     {$orby = array(array("CDGLAC","A","Account"),array("CDGLSB","A",""));}
		elseif ($sequence == "Description") {$orby = array(array("upper(CHCHDS)","A","Description"));}
		require_once 'OrderByUpdate.php';
	}

	if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

	if ($tag == "WILDCARD"){
		$andOr = $_POST['andOr'];
		require_once 'WildCardClear.php';
		$returnValue=Build_WildCard("CDDDNO", "Number", $_POST['srchNumber'], "", $_POST['operNumber'], "N");
		$returnValue=Build_WildCard("upper(HVDDNM)", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
		$returnValue=Build_WildCard("CDDCYC", "Cycle", $_POST['srchCycle'], "", $_POST['operCycle'], "N");
		$returnValue=Build_WildCard("CDGLAC", "Account", $_POST['srchAcct'], "", $_POST['operAcct'], "N");
		$returnValue=Build_WildCard("CDGLSB", "Sub", $_POST['srchSub'], "", $_POST['operSub'], "N");
		$returnValue=Build_WildCard("upper(CHCHDS)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
		require_once 'WildCardUpdate.php';
	}


	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);

	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
	//print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";

	require_once 'AJAXRequest.js';
	require_once 'CheckEnterSearch.php';
	require_once 'Menu.js';
	require_once 'NoFormValidate.php';
	require_once 'SaveCurrentURL.php';
	require_once 'ShowHideSelCriteria.php';
	require_once 'CheckSel.js';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "HRCOFACDED";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
	print "\n <tr><td><h1>$page_title</h1></td>";

	// Program Option Security
	$hhrcdm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);

	print "\n <td class=\"toolbar\">";
	print "\n <a href=\"{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=52\">$portalHome</a>";
	if ($hhrcdm_OPT['sec_01'] == "Y"){print "\n <a href=\"{$homeURL}{$phpPath}HRCoFacDedMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a>";}
	require_once 'XMLFormat.php';
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	print "</td>";
	print "\n </table>";

	$coFacDesc=RetValue("CFCOMP=$fromCo and CFFACL=$fromFac", "HRCOFC", "CFNAME");
	$coFac=trim($fromCo) . "/" . trim($fromFac);
	print "\n <table $contentTable>";
	Format_Header("Company/Facility", $coFacDesc, "$coFac");
	print "\n </table>";

	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " HRCDED ";
$fileSQL .= " inner join HRDEDM on CDDDNO=HVDDNO ";
$fileSQL .= " inner join HDCHRT on CDGLAC=CHACCT and CDGLSB=CHSUB ";
$selectSQL=" CDCOMP=$fromCo and CDFACL=$fromFac ";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );


if ($tag != "EXPORT") {
	$qsOpt  = "\n <option value=\"CDDDNO|null|Number|N|\" title=\"Deduction Number\">Number";
	$qsOpt .= "\n <option value=\"upper(HVDDNM)|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"CDGLAC|null|Cycle|N|\" title=\"Cycle\">Cycle";
	$qsOpt .= "\n <option value=\"CDGLSB|null|Account Number|N|\" title=\"Account Number\">Account Number";
	$qsOpt .= "\n <option value=\"CDGLSB|null|Subaccount Number|N|\" title=\"Subaccount Number\">Subaccount Number";
	$qsOpt .= "\n <option value=\"upper(CHCHDS)|null|Description|A|U\" title=\"Description\">Description";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	if ($hhrcdm_OPT['sec_02'] == "Y"  || $hhrcdm_OPT['sec_03'] == "Y" || $hhrcdm_OPT['sec_04'] == "Y"){
		print "<th class=\"colhdr\">$optionHeading</th>";
	}
	$returnValue=OrderBy_Sort("CDDDNO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Number\" title=\"Sequence By Number, Name\">{$sortPoint}Number</a></th>";
	$returnValue=OrderBy_Sort("upper(HVDDNM)"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\" title=\"Sequence By Name, Number\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("CDDCYC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Cycle\" title=\"Sequence By Cycle\">{$sortPoint}Cycle</a></th>";
	$returnValue=OrderBy_Sort("CDGLAC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Account\" title=\"Sequence By Account\">{$sortPoint}G/L Account</a></th>";
	$returnValue=OrderBy_Sort("upper(CHCHDS)"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description\">{$sortPoint}Description</a></th>";
	print "\n </tr>";
}

if ($tag == "EXPORT"){$xmlListName = "ExportPayrollACHList"; require_once 'XMLInit.php';}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	require  'SetRowClass.php';

	$F_Name=Format_Quote($row['HVDDNM']);
	$F_AcctSub=Format_Acct($row['CDGLAC'],$row['CDGLSB'],"N");
	$F_Desc=Format_Quote($row['CHCHDS']);


	if ($tag == "EXPORT") {
		$xmlID  = $xmlDoc->createElement(HRCoFacDed); $xmlRoot->appendChild($xmlID);
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Number"));                            $xmlTag->appendChild($xmlDoc->createTextNode($row[CDDDNO]));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("DedName"));                          	$xmlTag->appendChild($xmlDoc->createTextNode($F_Name));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Cycle"));                         	$xmlTag->appendChild($xmlDoc->createTextNode($row[CDDCYC]));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("AcctSub"));                   		$xmlTag->appendChild($xmlDoc->createTextNode($F_AcctSub));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("DedDesc"));                   		$xmlTag->appendChild($xmlDoc->createTextNode($F_Desc));

	} else {

		$confirmDesc = Format_Confirm_Desc("$row[CDDDNO] $F_Name", "", "", "", "", "");
		print "\n <tr class=\"$rowClass\">";
		print "\n <td class=\"opticon\">";
		$maintainVar =  $scriptVarBase . "&amp;tag=MAINTAIN&amp;fromDed=" . urlencode(trim($row['CDDDNO']));
		if ($hhrcdm_OPT['sec_02'] == "Y") {
			print "\n <a href=\"{$homeURL}{$phpPath}HRCoFacDedMaintain.php{$maintainVar}&amp;maintenanceCode=C\">$changeImageSml</a>";
		}
		if ($hhrcdm_OPT['sec_04']=="Y") {
			print "\n <a href=\"{$homeURL}{$phpPath}HRCoFacDedMaintain.php{$maintainVar}&amp;maintenanceCode=Z\">$copyImageSml</a>";
		}
		if ($hhrcdm_OPT['sec_03'] == "Y") {
			print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}HRCoFacDedMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
		}
		print "\n </td>";

		print "\n     <td class=\"colnmbr\">$row[CDDDNO]</td>";
		print "\n     <td class=\"colalph\">$F_Name</td>";
		print "\n     <td class=\"colcode\">$row[CDDCYC]</td>";
		print "\n     <td class=\"colnmbr\">$F_AcctSub</td>";
		print "\n     <td class=\"colalph\">$F_Desc</td>";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
}

if ($tag != "EXPORT") {
	if ($rowCount == 0){require 'NoRecordsFound.php';}
}

require_once 'XMLExport.php';

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
//require 'EndTabInclude.php';
//print "\n </table>";
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>	
