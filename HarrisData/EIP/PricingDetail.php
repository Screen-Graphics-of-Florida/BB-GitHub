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
require_once "MCControl$dataBaseID.php";
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'PricingDetailFunctions.php';

$page_title    = "Customer Pricing Detail";
$scriptName    = "PricingDetail.php";
$scriptVarBase = "{$genericVarBase}&amp;pricingLevel=" . urlencode(trim($pricingLevel)) . "&amp;levelDesc=" . urlencode(trim($levelDesc));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$currentURL    = "{$baseURL}&amp;startRow=" . urlencode($startRow);
$filterURL     = "{$scriptName}{$scriptVarBase}";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$programName    = "HOEPLM_E";
$advanceSearch    = "N";
$allowSaveFilter  = "N";
$_SESSION[$fromURL]=$currentURL;


if ($tag == "LOAD") {
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
	$curName = trim($mdFld['PWCOLN']);
	$curText = trim($mdFld['PWCTXT']);
	if  ($dftOrderBy == "")  {
		$dftOrderBy = array(array("$curName","A","$curText"));
	}
}

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($formatToPrint != "") {$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if ($sequence == "dateStart") {
		$orby = array(array("PMSTDT","A","Contract Start Date"));
	} elseif ($sequence == "dateExpire") {
		$orby = array(array("PMEXDT","A","Contract Expiration Date"));
	} else {
		foreach ($mdCol as $mdFld) {
			$curName = trim($mdFld['PWCOLN']);
			$curText = trim($mdFld['PWCTXT']);
			if ($sequence == $curName) {$orby = array(array($curName,"A",$curText));}
		}
	}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {require_once 'QuickSearch.php';}

if ($tag == "WILDCARD") {
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	foreach ($mdCol as $mdFld) {
		$curName  = trim($mdFld['PWCOLN']);
		$curText  = trim($mdFld['PWCTXT']);
		$curdType = trim($mdFld['PWDTYP']);
		$curfType = trim($mdFld['PWFTYP']);
		if ($curfType == "PHONE") {
			$returnValue = Build_WildCard($curName, $curText, $_POST["srch{$curName}"], "", "", "P");
		} elseif ($curfType == "ISO") {
			$returnValue = Build_WildCard($curName, $curText, $_POST["srch{$curName}"], "", $_POST["oper{$curName}"], "I");
		} elseif ($curfType == "CYMD") {
			$returnValue = Build_WildCard($curName, $curText, $_POST["srch{$curName}"], "", $_POST["oper{$curName}"], "D");
		} elseif ($curdType == "NUMERIC" || $curdType == "DECIMAL") {
			$returnValue = Build_WildCard($curName, $curText, $_POST["srch{$curName}"], "", $_POST["oper{$curName}"], "N");
		} else {
			$returnValue = Build_WildCard($curName, $curText, $_POST["srch{$curName}"], "U", $_POST["oper{$curName}"], "A");
		}
	}
	require_once 'WildCardUpdate.php';
}

if ($tag != "EXPORT") {

	$edtVar = "";
	Concat_Field("@@fx@@", "DEFN");
	Concat_Field("@@pmlv", $pricingLevel);
	$edtVar .= "}{";
	$defVar=Rtv_Pricing_Definition($profileHandle, $edtVar);
	$contract   = Decat_Field("@@cn@@", $defVar);
	$listLess   = Decat_Field("@@ll@@", $defVar);
	$costPlus   = Decat_Field("@@cp@@", $defVar);
	$dollarAmt  = Decat_Field("@@dl@@", $defVar);
	$usePercent = Decat_Field("@@up@@", $defVar);
	$bracketQty = Decat_Field("@@bp@@", $defVar);
	$bracketAmt = Decat_Field("@@ba@@", $defVar);
	$commission = Decat_Field("@@comm", $defVar);

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
	$pageID = "PRICINGDETAIL";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		$hoeplm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
		print "\n <td class=\"toolbar\">";
		print "\n <a href=\"{$homeURL}{$phpPath}PricingLevel.php{$genericVarBase}\" title=\"Back Home\">$portalHome</a>";
		if ($hoeplm_OPT['sec_01'] == "Y") {print "\n <a href=\"{$homeURL}{$phpPath}PricingDetailMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a>";}
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td>";
	}

	print "</tr></table>";

	print "\n <table $contentTable>";
	Format_Header_URL("Pricing Level", $levelDesc, $pricingLevel, "");
	if ($listLess == "Y")   {$structureDefn = "List Less";}
	if ($costPlus == "Y")   {$structureDefn = "Cost Plus";}
	if ($dollarAmt == "Y")  {$structureDefn = "Amount";}
	if ($dollarAmt != "Y") { if ($usePercent == "Y") {$structureDefn .= " Percentage";} else {$structureDefn .= " Amount";}}
	print "\n <tr><td class=\"hdrtitl\">Definition:</td>";
	if ($contract == "Y")  {
		print "\n   <td class=\"hdrdata\">Contract</td></tr> ";
		print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">$structureDefn</td></tr>";
	} else {
		print "\n   <td class=\"hdrdata\">$structureDefn </td></tr> ";
	}
	if  ($bracketQty == "Y")  {
		print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">Bracket By Quantity</td></tr> ";
	} elseif ($bracketAmt == "Y") {
		print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">Bracket By Amount</td></tr> ";
	}
	if ($commission != "") {
		print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">";
		if ($commission == "Y") {
			print "Commissionable";
		} elseif ($commission == "N") {
			print "Non-Commissionable";
		} elseif ($commission == "L") {
			print "Limited Commission";
		}
		print "\n   </td></tr>";
	}
	print "\n </table> ";
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

require 'stmtSQLClear.php';
$stmtSQL .=  " Select * ";
$fileSQL .= " HDPRCD ";
$selectSQL = " PMPMLV = $pricingLevel ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	if ($formatToPrint == "") {
		$qsOpt  = "";
		foreach ($mdCol as $mdFld)  {
			$curHeading = trim($mdFld['PWCHDG']);
			$curText    = trim($mdFld['PWCTXT']);
			$curdType = trim($mdFld['PWDTYP']);
			$curName = trim($mdFld['PWCOLN']);
			if ($curdType == "NUMERIC" || $curdType == "DECIMAL" || $curdType == "DATE") {
				$qsOpt .= "\n <option value=\"$curName|null|$curText|N|\" title=\"$curText\">$curText";
			} else {
				$qsOpt .= "\n <option value=\"$curName|null|$curText|A|U\" title=\"$curText\">$curText";
			}
		}
		require 'QuickSearchOption.php';
	}

	print "<table $contentTable>";
	print "\n <tr>";
	if ($formatToPrint != "Y" && ($hoeplm_OPT['sec_02'] == "Y" || $hoeplm_OPT['sec_03'] == "Y" || $hoeplm_OPT['sec_04'] == "Y")){
		print "<th class=\"colhdr\">$optionHeading</th>";
	}

	$saveItem = false;
	$saveWhs  = false;
	foreach ($mdCol as $mdFld)  {
		$curHeading = trim($mdFld['PWCHDG']);
		$curText    = trim($mdFld['PWCTXT']);
		$curName    = trim($mdFld['PWCOLN']);
		if ($curName == "PMITEM") {$saveItem = true;}
		if ($curName == "PMWHS")  {$saveWhs  = true;}
		$returnValue=OrderBy_Sort($curName,$sortVar); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=$curName\" title=\"Sequence By $curText\">{$sortPoint}$curHeading</a></th>";
		if ($curName == "PMCUST" || $curName == "PMWHS") {
			print "\n <th class=\"colhdr\">Name</th> ";
		} else {
			print "\n <th class=\"colhdr\">Description</th> ";
		}
	}

	if ($saveItem && $saveWhs && ($listLess == "Y" || $costPlus == "Y")) {
		if ($listLess == "Y") {
			print "\n <th class=\"colhdr\">List Price</th> ";
		} else {
			print "\n <th class=\"colhdr\">Cost</th> ";
		}
	}

	if ($bracketQty != "Y" && $bracketAmt != "Y") {
		print "\n <th class=\"colhdr\">$structureDefn</th> ";
		if ($saveItem && $saveWhs && ($listLess == "Y" || $costPlus == "Y")) {
			print "\n <th class=\"colhdr\">Selling Price</th> ";
		}
		if ($commission == "Y" || $commission == "L") {
			print "\n <th class=\"colhdr\">Commission<br>Percent</th> ";
		}
	}

	if ($contract == "Y") {
		$returnValue=OrderBy_Sort("PMSTDT",$sortVar); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=dateStart\" title=\"Sequence By Contract Start Date\">{$sortPoint}Contract<br>Start Date</a></th>";
		$returnValue=OrderBy_Sort("PMEXDT",$sortVar); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=dateExpire\" title=\"Sequence By ontract Expiration Date\">{$sortPoint}Contract<br>Expiration<br>Date</a></th>";
	}

	print "\n </tr>";
}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	$maintainVar = "{$scriptVarBase}&amp;pricingKey=" . urlencode($row['PMPMKY']) . "&amp;contractStart=" . urlencode($row['PMSTDT']);
	$confirmDesc = Format_Confirm_Desc($row['PMPMLV'], $row['PMPMKY'], "", "", "", "");

	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	if ($formatToPrint != "Y" && ($hoeplm_OPT['sec_02'] == "Y" || $hoeplm_OPT['sec_03'] == "Y" || $hoeplm_OPT['sec_01'] == "Y" && $hoeplm_OPT['sec_04'] == "Y")) {
		print "\n <td class=\"opticon\">";
		if ($hoeplm_OPT['sec_02'] == "Y" || $hoeplm_OPT['sec_03'] == "Y") {
			print "\n <a href=\"{$homeURL}{$phpPath}PricingDetailMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageSml</a>";
		}
		if ($hoeplm_OPT['sec_01'] == "Y" && $hoeplm_OPT['sec_04'] == "Y") {
			print "\n <a href=\"{$homeURL}{$phpPath}PricingDetailMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=Z\">$copyImageSml</a>";
		}
		if ($hoeplm_OPT['sec_03'] == "Y") {
			print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}PricingDetailMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
		}
		print "\n </td>";
	}

	$saveItemValue = "";
	$saveWhseValue = "";
	$saveCurtValue = "";
	foreach ($mdCol as $mdFld)  {
		$curName    = trim($mdFld['PWCOLN']);
		$curSize    = trim($mdFld['PWFLEN']);
		$curdType   = trim($mdFld['PWDTYP']);
		$curfType   = trim($mdFld['PWFTYP']);

		$curHeading = trim($mdFld['PWCHDG']);
		$curDecm    = trim($mdFld['PWFDEC']);
		$curDKey    = trim($mdFld['PWDKEY']);
		$curDTbl    = trim($mdFld['PWDTBL']);
		$curDCol    = trim($mdFld['PWDCOL']);

		$curData    = trim($row[$curName]);
		$fmtValue   = $curData ;

		if ($curName == "PMITEM") {
			$saveItemValue = $curData;
		} elseif ($curName == "PMWHS") {
			$saveWhseValue = $curData;
		} elseif ($curName == "PMCURT") {
			$saveCurtValue = $curData;
		}

		if ($curdType == "CHAR") {
			$fmtValue = htmlentities($curData, ENT_QUOTES);
			if ($curfType == "CODE") {
				$cssClass =  "colcode";
			} else {
				$cssClass =  "colalph";
			}
		} elseif ($curdType == "NUMERIC" || $curdType == "DECIMAL" || $curdType == "DATE") {
			$cssClass = "colnmbr";
			if ($curfType == "ISO") {
				$fmtValue = Format_Date_ISO($curData, "D") ;
				$cssClass = "coldate";
			} elseif ($curfType == "CYMD")  {
				$fmtValue = Format_Date($curData, "D") ;
				$cssClass =  "coldate";
			} elseif ($curfType == "QUANTITY")  {
				$fmtValue = Format_Nbr($curData, $qtyNbrDec, $qtyEditCode, $qtyRoundNbr, $qtyBeforeChar, $qtyAfterChar);
			} elseif ($curfType == "AMOUNT")  {
				$fmtValue = Format_Nbr($curData, $prcNbrDec, $amtEditCode, $amtRoundNbr, $amtBeforeChar, $amtAfterChar);
			} elseif  ($curfType == "PRICE")  {
				$fmtValue = Format_Nbr($curData, $prcNbrDec, $prcEditCode, $prcRoundNbr, $prcBeforeChar, $prcAfterChar);
			} elseif  ($curfType == "COST")  {
				$fmtValue = Format_Nbr($curData, $cstNbrDec, $cstEditCode, $cstRoundNbr, $cstBeforeChar, $cstAfterChar);
			} elseif  ($curfType == "ZEROS") {
				$fmtValue = str_pad($curData, $curSize, "0", STR_PAD_LEFT);
			}
		}
		if ($fmtValue == "") {$fmtValue == "&nbsp;";}

		$curNameDesc = "";
		if ($curdType == "NUMERIC" || $curdType == "DECIMAL" || $curdType == "DATE")  {
			$curNameDesc = RetValue("$curDKey = $row[$curName] ", "$curDTbl", "$curDCol");
		}  else  {
			$curNameDesc = RetValue("$curDKey = '$row[$curName]' ", "$curDTbl", "$curDCol") ;
		}

		print "\n <td class=\"$cssClass\"> " ;
		if ($curName == "PMCUST") {
			$uecCustomer = urlencode($row['PMCUST']);
			print "\n <a href=\"{$homeURL}{$cGIPath}CustomerInquiry.d2w/DISPLAY{$altVarBase}&amp;customerNumber={$uecCustomer}\" onclick=\"{$inquiryWinVar}\" title=\"Customer Quickview\">{$fmtValue}</a>";
		} elseif  ($curName == "PMITEM") {
			$uecItem = urlencode($row['PMITEM']);
			print "\n <a href=\"{$homeURL}{$cGIPath}ItemInquiry.d2w/DISPLAY{$altVarBase}&amp;itemNumber={$uecItem}\" onclick=\"{$inquiryWinVar}\" title=\"Item Quickview\">{$fmtValue}</a>";
		} else {
			print "\n $fmtValue";
		}
		print "\n </td>";
		print "\n <td class=\"colalph\">$curNameDesc</td>";
	}

	if ($saveItemValue != "" && $saveWhseValue != "" && ($listLess == "Y" || $costPlus == "Y"))  {
		if ($listLess == "Y") {
			$listCost = RetValue("IWITEM='$saveItemValue' and IWWHS=$saveWhseValue", "HDIWHS", "IWLIST");
			if ($MUPMCD == "Y" && $saveCurtValue != "") {$listCost = Rtv_CurListPrice($saveItemValue, $saveWhseValue, $saveCurtValue, $MUDCRT, $listCost);}
			$F_listCost = Format_Nbr($listCost, $prcNbrDec, $amtEditCode, $amtRoundNbr, $amtBeforeChar, $amtAfterChar);
		} else {
			$returnValue = Rtv_Unit_Cost($saveItemValue, $saveWhseValue);
			$listCost = $returnValue['cost'];
			if ($MUPMCD == "Y" && $saveCurtValue != "") {$listCost = Rtv_CurCostAmount($saveWhseValue, $saveCurtValue, $MUDCRT, $listCost);}
			$F_listCost = Format_Nbr($listCost, $cstNbrDec, $cstEditCode, $cstRoundNbr, $cstBeforeChar, $cstAfterChar);
		}
		print "\n <td class=\"colnmbr\">$F_listCost</td>";
	}

	if ($bracketQty != "Y" && $bracketAmt != "Y") {
		if ($usePercent == "Y") {
			$F_PMLCPC = Format_Nbr(($row['PMLCPC']*100), $pctNbrDec, $pctEditCode, $pctRoundNbr, $pctBeforeChar, $pctAfterChar);
			print "\n <td class=\"colnmbr\">$F_PMLCPC</td>";
		} else {
			$F_PMLCAM = Format_Nbr($row['PMLCAM'], $prcNbrDec, $amtEditCode, $amtRoundNbr, $amtBeforeChar, $amtAfterChar);
			print "\n <td class=\"colnmbr\">$F_PMLCAM</td>";
		}
		if ($saveItemValue != "" && $saveWhseValue != "" && ($listLess == "Y" || $costPlus == "Y"))  {
			if ($listLess == "Y") {
				if ($usePercent == "Y") {
					$listCost = $listCost - ($listCost * $row['PMLCPC']);
				} else {
					$listCost = $listCost - $row['PMLCAM'];
				}
			} elseif ($costPlus == "Y") {
				if ($usePercent == "Y") {
					$addCost = ($listCost * $row['PMLCPC']);
					$listCost = $listCost + ($listCost * $row['PMLCPC']);
				} else {
					$listCost = $listCost + $row['PMLCAM'];
				}
			}
			$F_listCost = Format_Nbr($listCost, $prcNbrDec, $amtEditCode, $amtRoundNbr, $amtBeforeChar, $amtAfterChar);
			print "\n <td class=\"colnmbr\">$F_listCost</td>";
		}
		if ($commission == "Y" || $commission == "L") {
			$F_PMCMPC = Format_Nbr(($row['PMCMPC']*100), $pctNbrDec, $pctEditCode, $pctRoundNbr, $pctBeforeChar, $pctAfterChar);
			print "\n <td class=\"colnmbr\">$F_PMCMPC</td>";
		}
	}

	if ($contract == "Y") {
		$contractStart = Format_Date($row['PMSTDT'], "D");
		print "\n <td class=\"coldate\">$contractStart</td>";
		$contractEndDate = Format_Date($row['PMEXDT'], "D");
		print "\n <td class=\"coldate\">$contractEndDate</td>";
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