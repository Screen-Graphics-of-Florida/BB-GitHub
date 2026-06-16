<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Payment Column By User";
$scriptName    = "ARPmtColumnUser.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("USDESCU","A","User"));
$programName   = "HARPCU_E";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NoFormValidate.php';
	require_once 'NumEdit.php';
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "ARPMTCOLUMNUSERSRCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("User Name","srchUser","","operUser","opersel_alph_short","A","15","30");
	Build_AdvSrch_Entry("Payment Type Description","srchPmtType","","operPmtType","opersel_alph_short","A","15","15");

	$focusField = "srchUser";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "User")        {$orby = array(array("USDESCU","A","User"));}
	elseif ($sequence == "PaymentType") {$orby = array(array("CPDESCU","A","Payment Type"),array("USDESCU","A","User"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("Coalesce(USDESCU,' ')", "User Name", $_POST['srchUser'], "U", $_POST['operUser'], "A");
	$returnValue=Build_WildCard("Coalesce(CPDESCU,' ')", "Payment Type Description", $_POST['srchPmtType'], "U", $_POST['operPmtType'], "A");
	require_once 'WildCardUpdate.php';
}

if ($tag != "EXPORT"){
	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);

	print "\n \n <script TYPE=\"text/javascript\">";
	require_once 'AJAXRequest.js';
	require_once 'CheckSel.js';
	require_once 'Menu.js';

	require_once 'CheckEnterSearch.php';
	require_once 'NoFormValidate.php';
	require_once 'SaveCurrentURL.php';
	require_once 'ShowHideSelCriteria.php';

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARPMTCOLUMNUSER";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
	print "\n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		// Program Option Security
		$harpcu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);

		print "\n <td class=\"toolbar\">";
		if ($harpcu_OPT['sec_01'] == "Y"){print "\n <a href=\"{$homeURL}{$phpPath}ARPmtColumnUserMaintain.php{$scriptVarBase}&amp;tag=ADD&amp;fromUser=&amp;fromPmtType=&amp;fromScript=" . urlencode(trim($scriptName)) . "&amp;maintenanceCode=A\">$addImageLrg</a>";}

		require_once 'XMLFormat.php';
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td>";
	}

	print "\n </tr></table>";
	if ($wfInstance>0){
		print "\n <table $contentTable>";
		Format_Header_URL("Work Item", $wfInstance, $wfInstanceDate, "{$homeURL}{$cGIPath}WFHistorySelect.d2w/REPORT{$altVarBase}&amp;fromScript=" . urlencode(trim($scriptName)) . "&amp;displayWFIcons=Y");
		print "\n </table>";
	}
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

require 'stmtSQLClear.php';
$distinctSQL= "PUUSER||PUTYPE ";
$stmtSQL .= " Select distinct PUUSER,PUTYPE,";
$stmtSQL .= " Coalesce(USDESC,' ') as USDESC, Coalesce(USDESCU,' ') as USDESCU, ";
$stmtSQL .= " Coalesce(CPDESC,' ') as CPDESC, Coalesce(CPDESCU,' ') as CPDESCU";
$fileSQL .= " ARPYCU ";
$fileSQL .= " left join SYUSER on USUSER=PUUSER ";
$fileSQL .= " left join ARPAYT on CPTYPE=PUTYPE ";
if ($wildCardSearch!="") {$selectSQL="PUUSER>' ' ";}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	if ($formatToPrint == ""){
		$qsOpt = "";
		$qsOpt .= "\n <option value=\"Coalesce(USDESCU,' ')|null|User Name|A|U\" title=\"User Name\" SELECTED>User Name";
		$qsOpt .= "\n <option value=\"Coalesce(CPDESCU,' ')|null|Payment Type Description|A|U\" title=\"Payment Type Description\">Payment Type Description";
		require 'QuickSearchOption.php';
	}

	print "<table $contentTable> <tr>";
	if ($formatToPrint != "Y"  &&  ($harpcu_OPT['sec_02'] == "Y"  || $harpcu_OPT['sec_03'] == "Y" || $harpcu_OPT['sec_04'] == "Y")or $harpcu_OPT['sec_05'] == "Y"){
		print "<th class=\"colhdr\">$optionHeading</th>";
	}
	$returnValue=OrderBy_Sort("USDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\"        title=\"Sequence By User\">{$sortPoint}User</a></th>";
	$returnValue=OrderBy_Sort("CPDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PaymentType\" title=\"Sequence By Payment Type, User\">{$sortPoint}Payment Type</a></th>";
	print "\n </tr>";
}

if ($tag == "EXPORT"){$xmlListName = "PaymentColumnUserList"; require_once 'XMLInit.php';}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	if ($tag == "EXPORT"){
		$xmlID  = $xmlDoc->createElement(PaymentColumnUser); $xmlRoot->appendChild($xmlID);
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("User"));         $xmlTag->appendChild($xmlDoc->createTextNode($row['USDESC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PaymentType"));  $xmlTag->appendChild($xmlDoc->createTextNode($row['CPDESC']));

	} else {
		$maintainVar = "{$scriptVarBase}&amp;fromUser=" . urlencode(trim($row['PUUSER'])) . "&amp;fromPmtType=" . urlencode(trim($row['PUTYPE'])) . "&amp;fromScript=" . urlencode(trim($scriptName));

		require  'SetRowClass.php';
		$confirmDesc = Format_Confirm_Desc("$row[USDESC] $row[CPDESC]", "", "", "", "", "");
		print "\n <tr class=\"$rowClass\">";
		if ($formatToPrint != "Y" && ($harpcu_OPT['sec_02'] == "Y" || $harpcu_OPT['sec_03'] == "Y" || $harpcu_OPT['sec_04'] == "Y")){
			print "\n <td class=\"opticon\">";
			if ($harpcu_OPT['sec_02'] == "Y" || $harpcu_OPT['sec_03'] == "Y" && trim($row['PUUSER'])!="HDS") {
				print "\n <a href=\"{$homeURL}{$phpPath}ARPmtColumnUserMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageSml</a>";
			}
			if ($harpcu_OPT['sec_01']=="Y" && $harpcu_OPT['sec_04']=="Y") {
				print "\n <a href=\"{$homeURL}{$phpPath}ARPmtColumnUserMaintain.php{$maintainVar}&amp;tag=ADD&amp;maintenanceCode=Z\">$copyImageSml</a>";
			}
			if (trim($row['PUUSER'])!="HDS" && $harpcu_OPT['sec_03'] == "Y") {
				print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}ARPmtColumnUserMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
			}
			print "\n </td>";
		}
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PUUSER]\">$row[USDESC]</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PUTYPE]\">$row[CPDESC]</span></td>";
		print "\n </tr>";
	}
	$startRow ++;
	$rowCount ++;
}

require_once 'XMLExport.php';

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$hrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";
?>