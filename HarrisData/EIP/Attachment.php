<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$attachFolder  = $_GET['attachFolder'];
$attachFolderU = strtoupper($attachFolder);
$attachForDesc = $_GET['attachForDesc'];
$attachVarKey  = $_GET['attachVarKey'];
$attachPrg1    = $_GET['attachPrg1'];
$attachPrg2    = $_GET['attachPrg2'];
$attachPrg3    = $_GET['attachPrg3'];
$attachPrg4    = $_GET['attachPrg4'];
$attachPrg5    = $_GET['attachPrg5'];

$noRefresh    = $_GET['noRefresh'];
if ($noRefresh == 'Y') {
    $_SESSION['refreshOpener'] = null;
} else {
    $_SESSION['refreshOpener'] = true;
}

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Attachments";
$scriptName    = "Attachment.php";
$scriptVarBase = "{$genericVarBase}&amp;attachFolder=" . urlencode($attachFolder) . "&amp;attachForDesc=" . urlencode($attachForDesc) . "&amp;attachVarKey=" . urlencode($attachVarKey) . "&amp;userProfile=" . urlencode($userProfile) . "&amp;attachPrg1=" . urlencode($attachPrg1) . "&amp;attachPrg2=" . urlencode($attachPrg2) . "&amp;attachPrg3=" . urlencode($attachPrg3) . "&amp;attachPrg4=" . urlencode($attachPrg4) . "&amp;attachPrg5=" . urlencode($attachPrg5) . "&amp;noRefresh=" . urlencode($noRefresh);
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL    = "{$baseURL}&amp;tag=INPUT&amp;startRow=" . urlencode($startRow);
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("ATDESCU","A","Description"),array("ATATNSU","A","Attachment Name"));
$advanceSearch = "N";
$popUpWin      = "Y";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "DELETE") {
	$attachFolderU = $_GET['attachFolderU'];
	$attachLongName = $_GET['attachLongName'];
	$attachShortName = $_GET['attachShortName'];
	$attachDesc = $_GET['attachDesc'];
		unlink($attachLongName);
		$attachPath = "{$homePath}{$uploadDirectory}{$dataBaseID}/{$attachFolder}/{$attachVarKey}/";
		rmdir($attachPath);
	require 'stmtSQLClear.php';
	$stmtSQL .= " Delete From SYD2WA Where ATFOLD='$attachFolderU' and ATVKEY='$attachVarKey' and ATATNS='$attachShortName' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$maintenanceCode = "D";
	$confMessage=Format_ConfMsg_Desc($maintenanceCode, $attachDesc, $attachShortName, "", "", "", "");
}

if ($tag == "ORDERBY"){
	if     ($sequence == "Desc")       {$orby = array(array("ATDESCU","A","Description"),array("ATATNSU","A","Attachment Name"));}
	elseif ($sequence == "Attachment") {$orby = array(array("ATATNSU","A","Attachment Name"),array("ATDESCU","A","Description"));}
	elseif ($sequence == "User")       {$orby = array(array("ATUSER","A","User"),array("ATDESCU","A","Description"));}
	elseif ($sequence == "Date")       {$orby = array(array("date(ATTSTP)","D","Date"),array("time(ATTSTP) DESC","N","Time"),array("ATDESCU","A","Description"));}
	elseif ($sequence == "Time")       {$orby = array(array("time(ATTSTP)","N","Time"),array("ATDESCU","A","Description"));}
	elseif ($sequence == "Body")       {$orby = array(array("ATBODY","A","Body File"),array("ATDESCU","A","Description"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag != "EXPORT"){
	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);
	$formName = "Search";

	print "\n \n <script TYPE=\"text/javascript\">";
	require_once 'AJAXRequest.js';
	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'CheckSel.js';
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
	print "\n <td class=\"content\">";
	print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		// Program Option Security
		$hsyatm_w_OPT=pgmOptSecurity($profileHandle, $dataBaseID, "HSYATM_W");

		print "\n <td class=\"toolbar\">";
		if ($hsyatm_w_OPT['sec_01'] == "Y"){print "\n <a href=\"{$homeURL}{$phpPath}AttachmentMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">{$addImageLrg}</a>";}

		require_once 'HelpPage.php';
		print "</td>";
	}
	print "\n </tr>";
	print "\n <tr><td><h2>{$attachForDesc}</h2></td></tr>";
	print "\n </table>";
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

require 'stmtSQLClear.php';
$stmtSQL    =  " Select SYD2WA.*, Coalesce(USDESC,' ') as USDESC ";
$fileSQL    =  "  SYD2WA ";
$fileSQL   .= " left join SYUSER on ATUSER=USUSER ";
$selectSQL  = " ATFOLD<>' ' and ATFOLD='{$attachFolderU}' and ATVKEY='{$attachVarKey}'";
$selectSQL .= "  and (ATUSER='{$userProfile}' or ATPRIV=' ' or '$admin' ='Y')";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($formatToPrint == ""){
	$qsOpt  = "\n <option value=\"ATDESCU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"ATATNSU|null|Attachment Name|A|U\" title=\"Attachment Name\">Attachment Name";
	$qsOpt .= "\n <option value=\"ATTSTP|DATE|Statement Date|TSD|\" title=\"Date\">Date";
	require 'QuickSearchOption.php';
}

print "<table $contentTable><tr>";
if ($formatToPrint != "Y" && ($hsyatm_w_OPT['sec_02'] == "Y" || $hsyatm_w_OPT['sec_03'] == "Y" || $hsyatm_w_OPT['sec_04'] == "Y" || $hsyatm_w_OPT['sec_05'] == "Y" || $hoeoem_OPT['sec_01'] == "Y")){
	print "<th class=\"colhdr\">$optionHeading</th>";
}
$returnValue=OrderBy_Sort("ATDESCU");  $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Desc\" title=\"Sequence By Description, Attachment Name\">{$sortPoint}Description</a></th>";
$returnValue=OrderBy_Sort("ATATNSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Attachment\" title=\"Sequence By Attachment Name, Description\">{$sortPoint}Attachment Name</a></th>";
$returnValue=OrderBy_Sort("ATUSER"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\" title=\"Sequence By User, Description\">{$sortPoint}User</a></th>";
$returnValue=OrderBy_Sort("date(ATTSTP)"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Date\" title=\"Sequence By Date, Time, Description\">{$sortPoint}Date</a></th>";
$returnValue=OrderBy_Sort("time(ATTSTP)"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Time\" title=\"Sequence By Time, Description\">{$sortPoint}Time</a></th>";
$returnValue=OrderBy_Sort("ATDIRL"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
if ($attachFolderU == "DOCUMENT") {
	$returnValue=OrderBy_Sort("ATBODY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Body\" title=\"Sequence By Body File, Description\">{$sortPoint}E-mail<br>Body File</a></th>";
}
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require  'SetRowClass.php';
	$maintainVar = "{$scriptVarBase}&amp;tag=MAINTAIN&amp;attachFolderU=" . urlencode(trim($row['ATFOLD'])) . "&amp;attachShortName=" . urlencode(trim($row['ATATNS'])) . "&amp;attachLongName=" . urlencode(trim($row['ATATNL'])) . "&amp;attachDesc=" . urlencode(trim($row['ATDESC'])) . "&amp;bodyFile=" . urlencode($row['ATBODY']) . "&amp;attachPrivate=" . urlencode($row['ATPRIV']) . "&amp;directLink=" . urlencode($row['ATDIRL']) . "&amp;attachUser=" . urlencode(trim($row['ATUSER']));
    $path = str_replace('\\', '\\\\', trim($row['ATATNS']));
	$confirmDesc = Format_Confirm_Desc(trim($row['ATDESC']), $path, "", "", "", "");
	$attDate = TimeStamp_CYMD($row[ATTSTP]);
	$attDate = Format_Date($attDate, "D");
	$attTime = TimeStamp_TIME($row[ATTSTP]);
	$attTime = EditHrsMinSec($attTime);

	print "\n <tr class=\"$rowClass\">";
	if ($formatToPrint != "Y" && ($hsyatm_w_OPT['sec_02'] == "Y" || $hsyatm_w_OPT['sec_03'] == "Y" || $hsyatm_w_OPT['sec_04'] == "Y" || $hsyatm_w_OPT['sec_05'] == "Y" || $hoeoem_OPT['sec_01'] == "Y")){
		print "\n <td class=\"opticon\">";
		if ($admin == "Y" || (trim($row['ATUSER']) == $userProfile)) {
			if ($hsyatm_w_OPT['sec_02'] == "Y" || $hsyatm_w_OPT['sec_03'] == "Y"){
				print "\n <a href=\"{$homeURL}{$phpPath}AttachmentMaintain.php{$maintainVar}&amp;maintenanceCode=C\">$changeImageSml</a>";
			}
			if ($hsyatm_w_OPT['sec_03'] == "Y"){
				print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}{$scriptName}{$maintainVar}&amp;tag=DELETE\">$deleteImageSml</a>";
			}
		} else {print "\n &nbsp;";}
		print "</td>";
	}
	print "\n <td class=\"colalph\">$row[ATDESC]</td>";
	$longName = trim($row['ATATNL']);
	if (trim($row['ATDIRL']) != "Y") {
	   $longName = "{$homePath}{$longName}";
	   $fileFound = file_exists($longName);
	} else {
	    $fileFound = "Y";
	}
	if ($fileFound) {
		print "\n <td class=\"colalph\"><a href=\"{$longName}\" target=_blank title=\"Click here to view attachment\">$row[ATATNS]</a></td>";
	} else {print "\n <td class=\"colalph\">$row[ATATNS]</td>";}
	print "\n <td class=\"colalph\">$row[USDESC]</td>";
	print "\n <td class=\"colalph\">$attDate</td>";
	print "\n <td class=\"colalph\">$attTime</td>";
	if ($attachFolderU == "DOCUMENT") {
		if ($row[ATBODY] == "Y") {$bodyImage=$checkImage;} else {$bodyImage="&nbsp;";}
		print "\n <td class=\"colcode\">$bodyImage</td>";
	}
	print "\n </tr>";

	$startRow ++;
	$rowCount ++;
}

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