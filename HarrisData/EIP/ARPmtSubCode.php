<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'ARPmtTypeInclude.php';

$fromPmtCode        = $_GET['fromPmtCode'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Payment Sub Code";
$scriptName    = "ARPmtSubCode.php";
$scriptVarBase = "{$genericVarBase}&amp;fromPmtCode=" . urlencode(trim($fromPmtCode));
$altScriptVarBase = "{$altVarBase}&amp;fromPmtCode=" . urlencode(trim($fromPmtCode));
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("PSSBCD","A","Payment Sub Code"));
$programName   = "HARPSM_E";
$useScriptName = "Y";

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

$PYTYPE=RetValue("PYPYCD='$fromPmtCode'", "ARPYCD", "PYTYPE");
if     ($PYTYPE=="C") {$CPCFOV=$C_CPCFOV; $CPACOV=$C_CPACOV; $CPCSAC=$C_CPCSAC; $CPARAC=$C_CPARAC; $CPOFAC=$C_CPOFAC;}
elseif ($PYTYPE=="D") {$CPCFOV=$D_CPCFOV; $CPACOV=$D_CPACOV; $CPCSAC=$D_CPCSAC; $CPARAC=$D_CPARAC; $CPOFAC=$D_CPOFAC;}
elseif ($PYTYPE=="J") {$CPCFOV=$J_CPCFOV; $CPACOV=$J_CPACOV; $CPCSAC=$J_CPCSAC; $CPARAC=$J_CPARAC; $CPOFAC=$J_CPOFAC;}
elseif ($PYTYPE=="M") {$CPCFOV=$M_CPCFOV; $CPACOV=$M_CPACOV; $CPCSAC=$M_CPCSAC; $CPARAC=$M_CPARAC; $CPOFAC=$M_CPOFAC;}
elseif ($PYTYPE=="U") {$CPCFOV=$U_CPCFOV; $CPACOV=$U_CPACOV; $CPCSAC=$U_CPCSAC; $CPARAC=$U_CPARAC; $CPOFAC=$U_CPOFAC;}
elseif ($PYTYPE=="Y") {$CPCFOV=$Y_CPCFOV; $CPACOV=$Y_CPACOV; $CPCSAC=$Y_CPCSAC; $CPARAC=$Y_CPARAC; $CPOFAC=$Y_CPOFAC;}
else                  {$CPCFOV="N"; $CPACOV="N"; $CPCSAC="N"; $CPARAC="N"; $CPOFAC="N";}

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	$fromToSearch = "";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editdate(document.Search.srchDeactDate) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHBATCHSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","20","30");
	Build_AdvSrch_Entry("Payment Sub Code","srchSubCode","","operSubCode","opersel_alph_short","A","4","4");
	if ($CPCFOV=="Y") {Build_AdvSrch_Entry("Allow Company/Facility Override Description","srchCoFacOvr","","operCoFacOvr","opersel_alph_short","A","4","3");}
	if ($CPACOV=="Y") {Build_AdvSrch_Entry("Allow Account Override Description","srchAcctOvr","","operAcctOvr","opersel_alph_short","A","4","3");}
	if ($CPCSAC=="Y") {Build_AdvSrch_Entry("Cash Account Description","srchCashAcct","","operCashAcct","opersel_alph_short","A","30","30");}
	if ($CPARAC=="Y") {Build_AdvSrch_Entry("A/R Account Description","srchARAcct","","operARAcct","opersel_alph_short","A","30","30");}
	if ($CPOFAC=="Y") {
		Build_AdvSrch_Entry("Offset Co/Fac Name","srchOffCoFac","","operOffCoFac","opersel_alph_short","A","30","30");
		Build_AdvSrch_Entry("Offset Account Description","srchOffAcct","","operOffAcct","opersel_alph_short","A","30","30");
	}
	Build_AdvSrch_Entry("Statement Description","srchStmtDesc","","operStmtDesc","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Date Deactivated","srchDeactDate","","operDeactDate","opersel_num_short","D","6","6");

	$focusField = "srchDesc";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "SubCode")     {$orby = array(array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "Description") {$orby = array(array("PSDESCU","A","Description"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "CashAcct")    {$orby = array(array("CSH_CHCHDSU","A","Cash Account"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "ARAcct") {$orby = array(array("AR_CHCHDSU","A","A/R Account"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "OffsetCoFac") {$orby = array(array("CFCFNMU","A","Offset Co/Fac"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "OffsetAccount") {$orby = array(array("OFF_CHCHDSU","A","Offset Account"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "StmtDescr") {$orby = array(array("PSSTDSU","A","Statement Description"),array("PSSBCD","A","Payment Sub Code"));}
	elseif ($sequence == "Deactivated") {$orby = array(array("PSDTDE","A","Date Deactivated"),array("PSSBCD","A","Payment Sub Code"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard ("PSDESCU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard ("PSSBCD", "Payment Sub Code", $_POST['srchSubCode'], "U", $_POST['operSubCode'], "A");
	$returnValue=Build_WildCard ("upper(d.FLDESC)", "Allow Company/Facility Override Description", $_POST['srchCoFacOvr'], "U", $_POST['operCoFacOvr'], "A");
	$returnValue=Build_WildCard ("upper(e.FLDESC)", "Allow Account Override Description", $_POST['srchAcctOvr'], "U", $_POST['operAcctOvr'], "A");
	$returnValue=Build_WildCard ("a.CHCHDSU", "Cash Account Description", $_POST['srchCashAcct'], "U", $_POST['operCashAcct'], "A");
	$returnValue=Build_WildCard ("b.CHCHDSU", "A/R Account Description", $_POST['srchARAcct'], "U", $_POST['operARAcct'], "A");
	$returnValue=Build_WildCard ("CFCFNMU", "Offset Co/Fac Name", $_POST['srchOffCoFac'], "U", $_POST['operOffCoFac'], "A");
	$returnValue=Build_WildCard ("c.CHCHDSU", "Offset Account Description", $_POST['srchOffAcct'], "U", $_POST['operOffAcct'], "A");
	$returnValue=Build_WildCard ("PSSTDSU", "Statement Description", $_POST['srchStmtDesc'], "U", $_POST['operStmtDesc'], "A");
	$returnValue=Build_WildCard ("Coalesce(PSDTDE,'0001-01-01')", "Date Deactivated", $_POST['srchDeactDate'], "", $_POST['operDeactDate'], "I");
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

	$formName = "Search";  // Need to Calendar Include
	require_once 'CalendarInclude.php';
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
	$pageID = "ARPMTSUBCODE";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
	print "\n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		$harpsm_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);  // Program Option Security

		print "\n <td class=\"toolbar\">";
		print "\n <a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=5\" title=\"Back Home\">$portalHome</a> ";
		if ($harpsm_OPT['sec_01'] == "Y"){print "\n <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a>";}
		require_once 'XMLFormat.php';
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td>";
	}

	print "\n </tr></table>";

	print "\n <table $contentTable>";
	$PYPYDS=RetValue("PYPYCD='$fromPmtCode'", "ARPYCD", "PYPYDS");
	Format_Header("Payment Code", $PYPYDS, $fromPmtCode);
	print "\n </table>";

	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;
}

require 'stmtSQLClear.php';
$stmtSQL .= " Select PSSBCD,PSDESC,PSPYCD,PSCFOV,PSACOV,PSSTDS,PSCSAC,PSCSSB,PSARAC,PSARSB,PSOFCO,PSOFFC,PSOFAC,PSOFSB,PSDTDE,   ";
$stmtSQL .= " coalesce(PYPYDS,' ') as PYPYDS, upper(coalesce(PYPYDS,' ')) as PYPYDSU,   ";
$stmtSQL .= " coalesce(d.FLDESC,' ') as COFAC_FLDESC,   ";
$stmtSQL .= " coalesce(e.FLDESC,' ') as ACCT_FLDESC,   ";
$stmtSQL .= " coalesce(a.CHCHDS,' ') as CSH_CHCHDS, coalesce(a.CHCHDSU,' ') as CSH_CHCHDSU,   ";
$stmtSQL .= " coalesce(b.CHCHDS,' ') as AR_CHCHDS, coalesce(b.CHCHDSU,' ') as AR_CHCHDSU,   ";
$stmtSQL .= " coalesce(CFCFNM,' ') as CFCFNM, coalesce(CFCFNMU,' ') as CFCFNMU,   ";
$stmtSQL .= " coalesce(c.CHCHDS,' ') as OFF_CHCHDS, coalesce(c.CHCHDSU,' ') as OFF_CHCHDSU   ";
$fileSQL .= " ARPYSB ";
$fileSQL .= " left join ARPYCD on PYPYCD=PSPYCD ";
$fileSQL .= " left join SYFLAG d on (d.FLTYPE,d.FLVALU)=('YORN',PSCFOV) ";
$fileSQL .= " left join SYFLAG e on (e.FLTYPE,e.FLVALU)=('YORN',PSACOV) ";
$fileSQL .= " left join HDCHRT a on (a.CHACCT,a.CHSUB)=(PSCSAC,PSCSSB) ";
$fileSQL .= " left join HDCHRT b on (b.CHACCT,b.CHSUB)=(PSARAC,PSARSB) ";
$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(PSOFCO,PSOFFC) ";
$fileSQL .= " left join HDCHRT c on (c.CHACCT,c.CHSUB)=(PSOFAC,PSOFSB) ";
$selectSQL .= " PSPYCD='$fromPmtCode' ";

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($tag != "EXPORT") {
	if ($formatToPrint == ""){
		$qsOpt = "";
		$qsOpt .= "\n <option value=\"PSDESCU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
		$qsOpt .= "\n <option value=\"PSSBCD|null|Payment Sub Code|A|U\" title=\"Payment Sub Code\">Payment Sub Code";
		if ($CPCFOV=="Y") {$qsOpt .= "\n <option value=\"upper(d.FLDESC)|null|Allow Company/Facility Override Description|A|U\" title=\"Allow Company/Facility Override Description\">Allow Company/Facility Override Description";}
		if ($CPACOV=="Y") {$qsOpt .= "\n <option value=\"upper(e.FLDESC)|null|Allow Account Override Description|A|U\" title=\"Allow Account Override Description\">Allow Account Override Description";}
		if ($CPCSAC=="Y") {$qsOpt .= "\n <option value=\"a.CHCHDSU|null|Cash Account Description|A|U\" title=\"Cash Account Description\">Cash Account Description";}
		if ($CPARAC=="Y") {$qsOpt .= "\n <option value=\"b.CHCHDSU|null|A/R Account Description|A|U\" title=\"A/R Account Description\">A/R Account Description";}
		if ($CPOFAC=="Y") {
			$qsOpt .= "\n <option value=\"CFCFNMU|null|Offset Co/Fac Name|A|U\" title=\"Offset Co/Fac Name\">Offset Co/Fac Name";
			$qsOpt .= "\n <option value=\"c.CHCHDSU|null|Offset Account Description|A|U\" title=\"Offset Account Description\">Offset Account Description";
		}
		$qsOpt .= "\n <option value=\"PSSTDSU|null|Statement Description|A|U\" title=\"Statement Description\">Statement Description";
		$qsOpt .= "\n <option value=\"Coalesce(PSDTDE,'0001-01-01')|DATE|Date Deactivated|I|\" title=\"Date Deactivated\">Date Deactivated";
		require 'QuickSearchOption.php';
	}

	print "<table $contentTable> <tr>";
	if ($formatToPrint != "Y" && ($harpsm_OPT['sec_02']=="Y" || $harpsm_OPT['sec_03']=="Y" || $harpsm_OPT['sec_01']=="Y" && $harpsm_OPT['sec_04']=="Y")) {
		print "<th class=\"colhdr\">$optionHeading</th>";
	}
	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Payment Sub Code\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("PSSBCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SubCode\" title=\"Sequence By Payment Sub Code\">{$sortPoint}Payment Sub Code</a></th>";
	if ($CPCSAC=="Y") {
		$returnValue=OrderBy_Sort("CSH_CHCHDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=CashAcct\" title=\"Sequence By Cash Account, Payment Sub Code\">{$sortPoint}Cash Account</a></th>";
	}
	if ($CPARAC=="Y") {
		$returnValue=OrderBy_Sort("AR_CHCHDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ARAcct\" title=\"Sequence By A/R Account, Payment Sub Code\">{$sortPoint}A/R Account</a></th>";
	}
	if ($CPOFAC=="Y") {
		$returnValue=OrderBy_Sort("CFCFNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OffsetCoFac\" title=\"Sequence By Offset Company/Facility, Payment Sub Code\">{$sortPoint}Offset Co/Fac</a></th>";
		$returnValue=OrderBy_Sort("OFF_CHCHDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OffsetAccount\" title=\"Sequence By Offset Account, Payment Sub Code\">{$sortPoint}Offset Account</a></th>";
	}
	$returnValue=OrderBy_Sort("PSSTDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=StmtDescr\" title=\"Sequence By Statement Description, Payment Sub Code\">{$sortPoint}Statement Description</a></th>";
	$returnValue=OrderBy_Sort("PSDTDE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Deactivated\" title=\"Sequence By Date Deactivated, Payment Sub Code\">{$sortPoint}Date Deactivated</a></th>";
	print "\n </tr>";
}

if ($tag == "EXPORT"){$xmlListName = "PaymentSubCodeList"; require_once 'XMLInit.php';}

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}
	$F_PSDTDE=Format_Date_ISO($row['PSDTDE'], "D");

	if ($tag == "EXPORT"){
		$xmlID  = $xmlDoc->createElement(PaymentSubCode); $xmlRoot->appendChild($xmlID);
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Batch"));             $xmlTag->appendChild($xmlDoc->createTextNode($row['BMBCHN']));

		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("SubCode"));              $xmlTag->appendChild($xmlDoc->createTextNode($row['PSSBCD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("Description"));          $xmlTag->appendChild($xmlDoc->createTextNode($row['PSDESC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("PaymentCode"));          $xmlTag->appendChild($xmlDoc->createTextNode($row['PSPYCD']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("AllowCoFacOverride"));   $xmlTag->appendChild($xmlDoc->createTextNode($row['PSCFOV']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("AllowAccountOverride")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PSACOV']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("StatementDescription")); $xmlTag->appendChild($xmlDoc->createTextNode($row['PSSTDS']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CashAccount"));          $xmlTag->appendChild($xmlDoc->createTextNode($row['PSCSAC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("CashSubAccount"));       $xmlTag->appendChild($xmlDoc->createTextNode($row['PSCSSB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ARAccount"));            $xmlTag->appendChild($xmlDoc->createTextNode($row['PSARAC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("ARSubAccount"));         $xmlTag->appendChild($xmlDoc->createTextNode($row['PSARSB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OffsetCompany"));        $xmlTag->appendChild($xmlDoc->createTextNode($row['PSOFCO']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OffsetFacility"));       $xmlTag->appendChild($xmlDoc->createTextNode($row['PSOFFC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OffsetAccount"));        $xmlTag->appendChild($xmlDoc->createTextNode($row['PSOFAC']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("OffsetSubAccount"));     $xmlTag->appendChild($xmlDoc->createTextNode($row['PSOFSB']));
		$xmlTag = $xmlID->appendChild($xmlDoc->createElement("DateDeactivated"));      $xmlTag->appendChild($xmlDoc->createTextNode($F_PSDTDE));

	} else {
		if (trim($row['PSSBCD'])==trim($fromPmtCode)) {$sec_03="N";} else {$sec_03=$harpsm_OPT['sec_03'];}  // Cannot delete

		$maintainVar = "{$scriptVarBase}&amp;pmtSubCode=" . urlencode(trim($row['PSSBCD'])) . "&amp;pmtSubCodeDesc=" . urlencode(trim($row['PSDESC'])) . "&amp;fromScript=" . urlencode(trim($scriptName));

		require 'SetRowClass.php';
		$confirmDesc = Format_Confirm_Desc("$row[PSDESC]", "$row[PSSBCD]", "", "", "", "");
		print "\n <tr class=\"$rowClass\">";

		if ($formatToPrint!="Y" && ($harpsm_OPT['sec_02']=="Y" || $sec_03=="Y" || $harpsm_OPT['sec_01']=="Y" && $harpsm_OPT['sec_04']=="Y")) {
			print "\n <td class=\"opticon\"> ";
			if ($harpsm_OPT['sec_02']=="Y" || $sec_03=="Y") {
				print "\n <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageSml</a>";
			}
			if ($harpsm_OPT['sec_01']=="Y" && $harpsm_OPT['sec_04']=="Y") {
				print "\n <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=Z\">$copyImageSml</a>";
			}
			if ($sec_03=="Y" && $row['PSSBCD']!=$row['PSPYCD']) {
				print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}ARPmtSubCodeMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageSml</a>";
			}
			print "\n </td> ";
		}
		print "\n <td class=\"colalph\">$row[PSDESC]</td> ";
		print "\n <td class=\"colalph\">$row[PSSBCD]</td> ";
		if ($CPCSAC=="Y") {
			$F_AcctSub=Format_Acct($row['PSCSAC'],$row['PSCSSB'],"N");
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_AcctSub\">$row[CSH_CHCHDS]</span></td>";
		}
		if ($CPARAC=="Y") {
			$F_AcctSub=Format_Acct($row['PSARAC'],$row['PSARSB'],"N");
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_AcctSub\">$row[AR_CHCHDS]</span></td>";
		}
		if ($CPOFAC=="Y") {
			$F_CoFac=Format_CoFac($row['PSOFCO'], $row['PSOFFC'],"N");
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_CoFac\">$row[CFCFNM]</span></td>";
			$F_AcctSub=Format_Acct($row['PSOFAC'],$row['PSOFSB'],"N");
			print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_AcctSub\">$row[OFF_CHCHDS]</span></td>";
		}
		print "\n <td class=\"colalph\">$row[PSSTDS]</td>";
		print "\n <td class=\"colalph\">$F_PSDTDE</td>";
		print "\n </tr> ";
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
