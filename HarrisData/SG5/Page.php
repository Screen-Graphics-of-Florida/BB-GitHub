<?php
$saveImageTitle = "Import XML";
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

//$tableName  = (isset($_GET['tableName'])) ? $_GET['tableName'] : "";
//$tableDesc  = (isset($_GET['tableDesc'])) ? $_GET['tableDesc'] : "";
$tableName  = RetValue("TNTBID={$tblID}", "SYTBLN", "TNTBLN");
$tableDesc  = RetValue("TNTBID={$tblID}", "SYTBLN", "TNDESC");

$intHD      = (isset($_GET['intHD']))     ? $_GET['intHD']     : "";

$page_title    = "Page";
$scriptName    = "Page.php";
$scriptVarBase  = "{$genericVarBase}&amp;tblID=" . urlencode($tblID) . "&amp;tableName=" . urlencode(trim($tableName)) . "&amp;tableDesc=" . urlencode(trim($tableDesc)) . "&amp;intHD=" . urlencode(trim($intHD));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("PDDESC","A","Description"),array("PDROLE","A","Role"),array("PDUSER","A","User Profile"));
$programName   = "CUSTOMTBL";

require_once 'ProgSecurityTestInclude.php';
if ($admin!="Y") {
	require_once 'ProgSecurityError.php';
	exit;
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterSearch.php';
	require_once 'Menu.js';
	require_once 'NoFormValidate.php';
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "PAGE";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","10","50");
	Build_AdvSrch_Entry("Role","srchRole","","operRole","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("User Profile","srchUser","","operUser","opersel_alph_short","A","10","10");

	$focusField = "srchDesc";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Desc")      {$orby = array(array("upper(PDDESC)","A","Description"),array("PDROLE","A","Role"),array("PDUSER","A","User Profile"));}
	elseif ($sequence == "Role")      {$orby = array(array("PDROLE","A","Role"),array("PDDESC","A","Description"));}
	elseif ($sequence == "User")      {$orby = array(array("PDUSER","A","User Profile"),array("PDDESC","A","Description"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("upper(PDDESC)", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("PDROLE", "Role", $_POST['srchRole'], "U", $_POST['operRole'], "A");
	$returnValue=Build_WildCard("PDUSER", "User Profile", $_POST['srchUser'], "U", $_POST['operUser'], "A");
	require_once 'WildCardUpdate.php';
}

if ($tag != "EXPORT"){
	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);
	$formName = "Search";

	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
	require_once 'AJAXRequest.js';
	require_once 'CheckEnterSearch.php';
	require_once 'CheckSel.js';
	require_once 'SaveCurrentURL.php';
	require_once 'ShowHideSelCriteria.php';
	require_once 'Menu.js';
	require_once 'NoFormValidate.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "PAGE";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
	print "\n <tr><td><h1>$page_title</h1></td>";


	if ($formatToPrint != "Y"){
		print "\n <td class=\"toolbar\">";
		print "\n <a href=\"{$homeURL}{$phpPath}PageMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a>";
		require_once 'XMLFormat.php';
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td>";
	}

	print "\n </tr></table>";
	print "<table $contentTable>";
	$customTable = '';
	if ($tblID >= 5000) {
	  $customTable = 'Custom';
	}
	if ($intHD == "Y" || $sec_01 == 'Y') {Format_Header_URL("Table", $tableDesc, $tableName, "{$homeURL}{$cGIPath}/{$customTable}Table.d2w/REPORT{$altVarBase}&amp;tblID=" . urlencode(trim($fTblID)) . "&amp;intHD=" . urlencode(trim($intHD)));}
	else                                 {Format_Header_URL("Table", $tableDesc, $tableName, "");}
	print "\n </table>";
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

require 'stmtSQLClear.php';
$stmtSQL .= " Select PDPGID, PDTYPE, PDDESC, PDROLE, PDUSER, PDDFLT, PDCRTB, PDTSTP";
$fileSQL  = " SYDSGN";
$selectSQL .= " PDTBID=$tblID ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
//print $stmtSQL;
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	if ($formatToPrint == ""){
		$qsOpt  = "\n <option value=\"upper(PDDESC)|null|Description|A|U\" title=\"Description\" SELECTED>Description";
		$qsOpt .= "\n <option value=\"PDROLE|null|Role|A|U\" title=\"Role\">Role";
		$qsOpt .= "\n <option value=\"PDUSER|null|User Profile|A|U\" title=\"User Profile\">User Profile";
		require 'QuickSearchOption.php';
	}

	print "<table $contentTable> <tr>";
	if ($formatToPrint != "Y"){
		print "<th class=\"colhdr\">$optionHeading</th>";
	}
	$returnValue=OrderBy_Sort("upper(PDDESC)"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\"      title=\"Sequence By Description, Role, User Profile\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("PDROLE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Role\"       title=\"Sequence By Role, Description\">{$sortPoint}Role</a></th>";
	$returnValue=OrderBy_Sort("PDUSER"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\"       title=\"Sequence By User Profile, Description\">{$sortPoint}User Profile</a></th>";
	print "\n </tr>";
}

if ($tag == "EXPORT"){$xmlListName = "PageList"; require_once 'XMLInit.php';}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	if ($tag == "EXPORT"){
		$xmlID  = $xmlDoc->createElement(Page); $xmlRoot->appendChild($xmlID);
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Description"));           $xmlTag->appendChild($xmlDoc->createTextNode($row['PDDESC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Role"));           $xmlTag->appendChild($xmlDoc->createTextNode($row['PDROLE']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("UserProfile"));    $xmlTag->appendChild($xmlDoc->createTextNode($row['PDUSER']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CreatedBy"));      $xmlTag->appendChild($xmlDoc->createTextNode($row['PDCRTB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Timestamp"));      $xmlTag->appendChild($xmlDoc->createTextNode($row['PDTSTP']));

	} else {
		$maintainVar  = "{$scriptVarBase}&amp;pagID=" . urlencode($row[PDPGID]);
		require 'SetRowClass.php';
		$confirmDesc = Format_Confirm_Desc("$row[PDDESC]", "", "", "", "", "");
		print "\n <tr class=\"$rowClass\">";
		if ($formatToPrint != "Y"){
			print "\n <td class=\"opticon\">";
            if (($intHD == "Y" && $row[PDPGID]<100) || ($tblID < 5000 && $row[PDPGID]>99) || ($tblID > 5000 && (trim($row[PDROLE]) != '' || trim($row[PDUSER]) != '' || $sec_01 == 'Y'))) {
                print "\n <a href=\"{$homeURL}{$phpPath}PageMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageSml</a>";
                print "\n <a href=\"{$homeURL}{$phpPath}PageMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=Z\">$copyImageSml</a>";
                print "\n <a href=\"{$homeURL}{$phpPath}PageImportXML.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C&amp;pageDesc=" . urlencode(trim($row[PDDESC])) . "&amp;role=" . urlencode(trim($row[PDROLE])) . "&amp;user=" . urlencode(trim($row[PDUSER])) . "\">$xmlImport</a>";
                print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}PageMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
            } else {
                print "\n <a href=\"{$homeURL}{$phpPath}PageMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=Z\">$copyImageSml</a>";
			}
			print "\n </td>";
		}
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}hdList.php{$genericVarBase}&amp;tblID=" . urlencode($tblID) . "&amp;pagID=" . urlencode($row[PDPGID]) . "\" title=\"View Page\">$row[PDDESC]</a></td>";
		print "\n <td class=\"colalph\">$row[PDROLE]</td>";
		print "\n <td class=\"colalph\">$row[PDUSER]</td>";
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