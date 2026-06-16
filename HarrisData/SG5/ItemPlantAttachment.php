<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$itemNumber =	(isset($_GET['itemNumber']))	?	$_GET['itemNumber']	:	"";
$plantNumber=	(isset($_GET['plantNumber']))	?	$_GET['plantNumber']	:	"";
$touchScreen=	(isset($_GET['touchScreen']))	?	$_GET['touchScreen']	:	"";

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Item Plant Attachments";
$scriptName    = "ItemPlantAttachment.php";
$scriptVarBase = "{$genericVarBase}&amp;itemNumber=" . urlencode(trim($itemNumber)) . "&amp;plantNumber=" . urlencode(trim($plantNumber)) . "&amp;touchScreen=" . urlencode(trim($touchScreen));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL    = "{$baseURL}&amp;tag=INPUT&amp;startRow=" . urlencode($startRow);
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $prtMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("ATDESCU","A","Description"),array("ATATNSU","A","Attachment Name"));
$advanceSearch = "N";
$popUpWin      = "Y";
$formatToPrint = "";
$attachItemKey = trim($itemNumber);
$attachItemPlantKey = str_pad(trim($plantNumber), 3, '0', STR_PAD_LEFT) . $attachItemKey;

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$maxRows = $dspMaxRows;

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

print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($inquiryBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
if ($touchScreen == "Y") {
	print "\n <div class=\"quickLinksTop\">";
	print "\n   <ul class=\"toolbarTS\">";
	print "\n     <li class=\"optionTS\"><a href=\"javascript:window.close()\">&nbsp;<br>Close</a></li>";
	print "\n   </ul>";
	print "\n </div>";
}

$plantName=RetValue("PLPLNT=$plantNumber", "HDPLNT", "PLNAME");
$itemDesc=RetValue("IMITEM='$itemNumber'", "HDIMST", "IMIMDS");
print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td>";
print "\n </tr></table>";
print "\n <table $contentTable>";
Format_Header('Plant', $plantName, $plantNumber);
Format_Header('Item', $itemDesc, $itemNumber);
print "\n </table>";
print "$inquiryhrTagAttr";

require 'stmtSQLClear.php';
$stmtSQL    =  " Select SYD2WA.*, Coalesce(USDESC,' ') as USDESC ";
$fileSQL    =  "  SYD2WA ";
$fileSQL   .= " left join SYUSER on ATUSER=USUSER ";
$selectSQL  = " ATFOLD<>' '";
$selectSQL .= " and (ATFOLD='ITEM' and ATVKEY='{$attachItemKey}'";
$selectSQL .= "      or ATFOLD='ITEMPLANT' and ATVKEY='{$attachItemPlantKey}')";
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
print "\n </tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require  'SetRowClass.php';
	$attDate = TimeStamp_CYMD($row[ATTSTP]);
	$attDate = Format_Date($attDate, "D");
	$attTime = TimeStamp_TIME($row[ATTSTP]);
	$attTime = EditHrsMinSec($attTime);

	print "\n <tr class=\"$rowClass\">";
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
	print "\n </tr>";

	$startRow ++;
	$rowCount ++;
}

if ($rowCount == 0){require 'NoRecordsFound.php';}

print "</table>";
require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$inquiryhrTagAttr";
require_once 'Copyright.php';

print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "\n </body> \n </html>";

?>