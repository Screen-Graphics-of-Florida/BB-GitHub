<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$pricingLevel = $_GET['pricingLevel'];
$levelDesc    = $_GET['levelDesc'];
$reload       = (isset($_GET['reload'])) ? $_GET['reload'] : null;

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'VendorPricingDetailFunctions.php';

$page_title    = "Vendor Pricing Detail";
$scriptName    = "VendorPricingDetail.php";
$scriptVarBase = "{$genericVarBase}&amp;pricingLevel=" . urlencode(trim($pricingLevel)) . "&amp;levelDesc=" . urlencode(trim($levelDesc)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL    = "{$baseURL}&amp;tag=INPUT&amp;startRow=" . urlencode($startRow);
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$programName    = "HPOPLM_E";
$advanceSearch    = "N";
$allowSaveFilter  = "N";

if ($tag == "LOAD"){
	$edtVar = "";
	Concat_Field("@@fx@@", "LOAD");
	Concat_Field("@@pmlv", $pricingLevel);
	$edtVar .= "}{";
	$defVar=Rtv_Pricing_Definition($profileHandle, $edtVar);
	require_once 'WildCardClear.php';
	require_once 'WildCardUpdate.php';
	$chgSrch = "D";
}

$mdCol = Rtv_Pricing_Categories($profileHandle, $pricingLevel);

foreach ($mdCol as $mdFld)  {
	$curdType = trim($mdFld['PWDTYP']);
	$curName = trim($mdFld['PWCOLN']);
	$curText = trim($mdFld['PWCTXT']);
	$curSize = trim($mdFld['PWFLEN']);
	$curDecm = trim($mdFld['PWFDEC']);
	if  ($dftOrderBy == "")  {
		$dftOrderBy = array(array("$curName","A","$curText"));
	}
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if ($sequence == "VDWHS")        {$orby = array(array("VDWHS","A","Warehouse"));}
	elseif ($sequence == "VDVEND")   {$orby = array(array("VDVEND","A","Vendor Number"));}
	elseif ($sequence == "VDITEM")   {$orby = array(array("VDITEM","A","Item Number"));}
	elseif ($sequence == $curName)   {$orby = array(array($curName,"A",$curHeading));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("VDWHS",  "Warehouse", $_POST['srchVDWHS'], "", $_POST['operVDWHS'], "N");
	$returnValue=Build_WildCard("VDVEND", "Vendor Number", $_POST['srchVDVEND'], "", $_POST['operVDVEND'], "N");
	$returnValue=Build_WildCard("VDITEM", "Item Number", $_POST['srchVDITEM'], "U", $_POST['operVDITEM'], "A");
	require_once 'WildCardUpdate.php';
}

if ($tag != "EXPORT"){

	$edtVar = "";
	Concat_Field("@@fx@@", "DEFN");
	Concat_Field("@@pmlv", $pricingLevel);
	$edtVar .= "}{";
	$defVar=Rtv_Pricing_Definition($profileHandle, $edtVar);
	$dollarAmt  = Decat_Field("@@dl@@", $defVar);
	$usePercent = Decat_Field("@@up@@", $defVar);
	$contract   = Decat_Field("@@cn@@", $defVar);
	$bracketAmt = Decat_Field("@@bp@@", $defVar);
	$maintainVar = "{$scriptVarBase}&amp;dollarAmt=" . urlencode(trim($dollarAmt)) . "&amp;usePercent=" . urlencode(trim($usePercent)) . "&amp;contract=" . urlencode(trim($contract)) . "&amp;bracketAmt=" . urlencode(trim($bracketAmt));

	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);
	$formName = "Search";

	print "\n \n <script TYPE=\"text/javascript\">";
	require_once 'AJAXRequest.js';
	require_once 'CheckEnterSearch.php';
	require_once 'CheckSel.js';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'SaveCurrentURL.php';
	require_once 'ShowHideSelCriteria.php';
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
	require_once 'NoFormValidate.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "Vendor Pricing Level Detail";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		$hpoplm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
		print "\n <td class=\"toolbar\">";
		{print "\n <a href=\"{$homeURL}{$phpPath}VendorPricingLevel.php{$maintainVar}&amp;tag=REPORT&amp;title=Back Home\">$portalHome</a>";}
		if ($hpoplm_OPT['sec_01'] == "Y"){print "\n <a href=\"{$homeURL}{$phpPath}VendorPricingDetailMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a>";}
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td></tr></table>";
	}

	print "\n <table $contentTable>";

	Format_Header_URL("Pricing Level", $levelDesc, $pricingLevel, "");

	if ($dollarAmt == "Y")  {$structureDefn = "Dollar";}
	if ($usePercent == "Y") {$structureDefn = "Percentage";} else {$structureDefn .= " Amount";}

	print "\n <tr><td class=\"hdrtitl\">Definition:</td>";
	if ($contract == "Y")  {
		print "\n   <td class=\"hdrdata\">Contract</td></tr> ";
		print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">$structureDefn</td></tr>";
	} else {
		print "\n   <td class=\"hdrdata\">$structureDefn </td></tr> ";
	}

	if  ($bracketAmt == "Y")  {
		print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">Bracket By Quantity</td></tr> ";
	}
	print "\n </table> ";
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

$mdCol = Rtv_Pricing_Categories($profileHandle, $pricingLevel);

require 'stmtSQLClear.php';
$stmtSQL .=  " Select * ";
$fileSQL .= " POVPDT ";
$selectSQL = " VDPMLV = $pricingLevel ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	if ($formatToPrint == ""){
		$qsOpt  = "";
		$srchName = "";
		foreach ($mdCol as $mdFld)  {
			$curHeading = trim($mdFld['PWCHDG']);
			$curdType = trim($mdFld['PWDTYP']);
			$curName = trim($mdFld['PWCOLN']);
			$curSize = trim($mdFld['PWFLEN']);
			$curDecm = trim($mdFld['PWFDEC']);
			$curDPos = trim($mdFld['PWDPOS']);
			if (strlen($curHeading)>$curSize) {$fldSize = strlen($curHeading);} else {$fldSize = $curSize;}
			if ($curdType == "NUMERIC" || $curdType == "DECIMAL" || $curdType == "DATE") {
				$qsOpt .= "\n <option value=\"$curName|null|$curHeading|N|\" title=\"$curHeading\">$curHeading";
			} else {
				$qsOpt .= "\n <option value=\"$curName|null|$curHeading|A|U\" title=\"$curHeading\">$curHeading";
			}
		}
		require 'QuickSearchOption.php';
	}

	print "<table $contentTable>";
	print "\n <tr>";
	if ($formatToPrint != "Y" && ($hpoplm_OPT['sec_02'] == "Y" || $hpoplm_OPT['sec_03'] == "Y" || $hpoplm_OPT['sec_04'] == "Y")){
		print "<th class=\"colhdr\">$optionHeading</th>";
	}

	$orderByVar = "$d2wVarBase, $searchVarBase";
	$saveItem = "";
	$saveWhs  = "";

	foreach ($mdCol as $mdFld)  {
		$curHeading = trim($mdFld['PWCHDG']);
		$curText    = trim($mdFld['PWCTXT']);
		$curdType   = trim($mdFld['PWDTYP']);
		$curName    = trim($mdFld['PWCOLN']);
		$curSize    = trim($mdFld['PWFLEN']);
		$curDecm    = trim($mdFld['PWFDEC']);

		if ($curdType = "NUMERIC" || "DECIMAL" || "DATE") {
			$returnValue=OrderBy_Sort($curName,$sortVar); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=$curName\" title=\"Sequence By $curHeading\">{$sortPoint}$curHeading</a></th>";
		}  else  {
			$returnValue=OrderBy_Sort($curName,$sortVar); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=$curName\" title=\"Sequence By $curHeading\">{$sortPoint}$curHeading</a></th>";
		}

		if ($curName == "VDVEND" || $curName == "VDWHS") {
			print "\n <th class=\"colhdr\">Name</th> ";
		} else {
			print "\n <th class=\"colhdr\">Description</th> ";
		}

		if ($curName == "VDITEM") {
			$saveItem = "Y";
		}  elseif ($curName == "VDWHS") {
			$saveWhs = "Y";
		}
	}

	if ($contract == "Y") {
		print "\n <th class=\"colhdr\">Contract<br>Start Date</th> ";
		print "\n <th class=\"colhdr\">Contract<br>Expiration<br>Date</th> ";
	} 
	if ($dollarAmt == "Y" && ($usePercent != "Y") && $bracketAmt != "Y" )  {
		print "\n <th class=\"colhdr\">Dollar Amount</th> ";
	}
	print "\n </tr>";
}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	$maintainVarD2w = "{$altVarBase}";
	$confirmDesc = Format_Confirm_Desc($row['VDPMLV'], $row['VDPMKY'], "", "", "", "");

	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	if ($formatToPrint != "Y" && ($hpoplm_OPT['sec_02'] == "Y" || $hpoplm_OPT['sec_03'] == "Y" || $hpoplm_OPT['sec_04'] == "Y")){
		print "\n <td class=\"opticon\">";
		if ($hpoplm_OPT['sec_02'] == "Y" || $hpoplm_OPT['sec_03'] == "Y"){
			print "\n <a href=\"{$homeURL}{$phpPath}VendorPricingDetailMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;pricingKey=" . urlencode($row['VDPMKY']) . "&amp;contractStartDate=" . urlencode($row['VDSTDT']) . "&amp;maintenanceCode=C\">$changeImageSml</a>";
		}
		if ($hpoplm_OPT['sec_01'] == "Y" && $hpoplm_OPT['sec_04'] == "Y"){
			print "\n <a href=\"{$homeURL}{$phpPath}VendorPricingDetailMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;pricingKey=" . urlencode($row['VDPMKY']) . "&amp;contractStartDate=" . urlencode($row['VDSTDT']) . "&amp;maintenanceCode=Z\">$copyImageSml</a>";
		}
		if ($hpoplm_OPT['sec_03'] == "Y") {
			print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}VendorPricingDetailMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;pricingKey=" . urlencode($row['VDPMKY']) . "&amp;contractStartDate=" . urlencode($row['VDSTDT']) . "&amp;maintenanceCode=D\">$deleteImageSml</a>";
		}
		print "\n </td>";
	}

	foreach ($mdCol as $mdFld)  {
		$curHeading = trim($mdFld['PWCHDG']);
		$curdType   = trim($mdFld['PWDTYP']);
		$curfType   = trim($mdFld['PWFTYP']);
		$curName    = trim($mdFld['PWCOLN']);
		$curSize    = trim($mdFld['PWFLEN']);
		$curDecm    = trim($mdFld['PWFDEC']);
		$curDKey    = trim($mdFld['PWDKEY']);
		$curDTbl    = trim($mdFld['PWDTBL']);
		$curDCol    = trim($mdFld['PWDCOL']);
		$curData    = $row[$curName];
		$fmtValue   = $curData ;

		if ($curName == "VDITEM") {$saveItem = $curData;}
		elseif ($curName == "VDWHS") {$saveWhs = $curData;}

		if ($curdType == "CHAR")  {
			if ($curfType == "CODE")  {
				$cssClass =  "\"colcode\"";
			} else {
				$cssClass =  "\"colalph\"";
			}

		}  elseif  ($curdType == "NUMERIC" || $curdType == "DECIMAL" || $curdType == "DATE")  {
			$cssClass = "\"colnmbr\"";
			if ($curfType == "ISO") {
				Format_Date_ISO($curData, "D", $fmtValue) ;
				$cssClass = "\"coldate\"";
			}   elseif ($curfType == "CYMD")  {
				Format_Date($curData, "D", $fmtValue) ;
				$cssClass =  "\"coldate\"";
			}   elseif ($curfType == "AMOUNT")  {
				Format_Nbr($fmtValue, $fmtValue, "2", ($amtEditCode), "", "", "");
			}  elseif  ($curfType == "PRICE")  {
				Format_Nbr($fmtValue, $fmtValue,($prcNbrDec), ($amtEditCode), "", "", "");
			}  elseif  ($curfType == "ZEROS") {
				while(@dtw_rlength($fmtValue) != $curLength) {@dtw_insert("0", $fmtValue, $fmtValue) ; }
			}
		}
		if ($fmtValue == "") { $fmtValue == "&nbsp;" ; }

		$curNameDesc = "";
		if ($curdType == "NUMERIC" || $curdType == "DECIMAL" || $curdType == "DATE")  {
			$curNameDesc = RetValue("$curDKey = $row[$curName] ", "$curDTbl", "$curDCol");
		}  else  {
			$curNameDesc = RetValue("$curDKey = '$row[$curName]' ", "$curDTbl", "$curDCol") ;
		}

		print "\n <td class=$cssClass> " ;
		if  ($curName == "VDVEND") {print "\n <a href=\"{$homeURL}{$cGIPath}VendorInquiry.d2w/DISPLAY{$altVarBase}&amp;vendorNumber=" . urlencode($row['VDVEND']) .  "&amp;fromScript=\" . urlencode($scriptName)\" onclick=\"{$inquiryWinVar}\" title=\"Vendor Quickview\">{$fmtValue}</a>";}
		elseif  ($curName == "VDITEM") {print "\n <a href=\"{$homeURL}{$cGIPath}ItemInquiry.d2w/DISPLAY{$altVarBase}&amp;itemNumber=" . urlencode($row['VDITEM']) .  "&amp;fromScript=\" . urlencode($scriptName)\" onclick=\"{$inquiryWinVar}\" title=\"Item Quickview\">{$fmtValue}</a>";}
		else {print "\n $curData"; }
		print "\n </td>";
		print "\n <td class=\"colalph\">$curNameDesc</td>";
	}

	if ($contract == "Y") {
		$contractStartDate=$row['VDSTDT'];
		$row['VDSTDT']=Format_Date(($row['VDSTDT']), "D");
		print "\n <td class=\"coldate\">$row[VDSTDT] </td>";
		$row['VDEXDT']=Format_Date(($row['VDEXDT']), "D");
		print "\n <td class=\"coldate\">$row[VDEXDT] </td>";
	}

	if ($dollarAmt == "Y" && $usePercent != "Y" && $bracketAmt != "Y" )  {
		print "\n <td class=\"colcode\">$row[VDAMNT]</td> ";
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


function Rtv_Pricing_Categories($profileHandle, $pricingLevel) {
	global $i5Connect;
	$stmtSQL = " Select * From HDPRCW Where PWXHND='$profileHandle' and PWPMLV=$pricingLevel Order By PWDPOS ";
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	while ($row = db2_fetch_assoc($sqlResult)){$mdCol[] = $row;}
	return $mdCol;
}

?>