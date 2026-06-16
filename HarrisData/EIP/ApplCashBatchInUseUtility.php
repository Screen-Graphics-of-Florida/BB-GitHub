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

$page_title    = "Clear Batch In Use";
$scriptName    = "ApplCashBatchInUseUtility.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("BUBCHN","A","Batch"));
$programName   = "HARCBH";
$medIcon       = "Y";

$harcbh_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);  // Program Option Security
if ($harcbh_OPT['sec_03']!="Y") {
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
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.$formName.frBatch, 4, 0) ";
	print "\n    && editNum(document.$formName.toBatch, 4, 0) ";
	print "\n    && editFromToOper(document.$formName.frBatch, document.$formName.toBatch, document.$formName.operBatch, 4) ";
	print "\n    && editdate(document.$formName.frDate) ";
	print "\n    && editdate(document.$formName.toDate) ";
	print "\n    && editFromToOper(document.$formName.frDate, document.$formName.toDate, document.$formName.operDate, 'D') ";
	print "\n    && editNum(document.$formName.frDeposit, 13, 2) ";
	print "\n    && editNum(document.$formName.toDeposit, 13, 2) ";
	print "\n    && editFromToOper(document.$formName.frDeposit, document.$formName.toDeposit, document.$formName.operDeposit, 17) ";
	print "\n    && editNum(document.$formName.frOther, 13, 2) ";
	print "\n    && editNum(document.$formName.toOther, 13, 2) ";
	print "\n    && editFromToOper(document.$formName.frOther, document.$formName.toOther, document.$formName.operOther, 17) ";
	print "\n   ) return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHBATCHSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Batch","frBatch","toBatch","operBatch","opersel_num2_short","N","4","4");
	Build_AdvSrch_Entry("Batch Date","frDate","toDate","operDate","opersel_num2_short","D","6","6");
	Build_AdvSrch_Entry("Bank Name","srchBank","","operBank","opersel_alph_short","A","20","30");
	Build_AdvSrch_Entry("User Name","srchUser","","operUser","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Deposit Total","frDeposit","toDeposit","operDeposit","opersel_num2_short","N","20","17");
	Build_AdvSrch_Entry("Other Total","frOther","toOther","operOther","opersel_num2_short","N","20","17");
	Build_AdvSrch_Entry("Type Description","srchType","","operType","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Payment Entry Description","srchEntry","","operEntry","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Status Description","srchStatus","","operStatus","opersel_alph_short","A","20","50");
	Build_AdvSrch_Entry("Pending Payment Description","srchPend","","operPend","opersel_alph_short","A","20","50");

	$focusField = "frBatch";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Batch")     {$orby = array(array("BUBCHN","A","Batch"));}
	elseif ($sequence == "Date")      {$orby = array(array("BUBCHD","A","Batch Date"),array("BUBCHN","A","Batch"));}
	elseif ($sequence == "Bank")      {$orby = array(array("BKBKNMU","A","Bank"),array("BUBCHN","A","Batch"));}
	elseif ($sequence == "User")      {$orby = array(array("USDESCU","A","User"),array("BUBCHN","A","Batch"));}
	elseif ($sequence == "Deposit")   {$orby = array(array("BMDEPA","A","Deposit Total"),array("BUBCHN","A","Batch"));}
	elseif ($sequence == "Other")     {$orby = array(array("BMADJT","A","Other Total"),array("BUBCHN","A","Batch"));}
	elseif ($sequence == "Type")      {$orby = array(array("FLDESC_BMBCHTU","A","Type"),array("BUBCHN","A","Batch"));}
	elseif ($sequence == "Entry")     {$orby = array(array("FLDESC_BMPMTEU","A","Entry"),array("BUBCHN","A","Batch"));}
	elseif ($sequence == "Status")    {$orby = array(array("FLDESC_BMBCHSU","A","Status"),array("BUBCHN","A","Batch"));}
	elseif ($sequence == "Pending")   {$orby = array(array("FLDESC_BMPPMTU","A","Pending Payment"),array("BUBCHN","A","Batch"));}
	elseif ($sequence == "Errors")    {$orby = array(array("ARPYENERROR","A","Number of Errors"),array("BUBCHN","A","Batch"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Range_WildCard("BUBCHN", "Batch", $_POST['frBatch'], $_POST['toBatch'], "", $_POST['operBatch'], "N");
	$returnValue=Range_WildCard("BUBCHD", "Batch Date", $_POST['frDate'], $_POST['toDate'], "", $_POST['operDate'], "D");
	$returnValue=Build_WildCard("Upper(BKBKNM)", "Bank Name", $_POST['srchBank'], "U", $_POST['operBank'], "A");
	$returnValue=Build_WildCard("USDESCU", "User Name", $_POST['srchUser'], "U", $_POST['operUser'], "A");
	$returnValue=Range_WildCard("BMDEPA", "Deposit Total", $_POST['frDeposit'], $_POST['toDeposit'], "", $_POST['operDeposit'], "N");
	$returnValue=Range_WildCard("BMADJT", "Other Total", $_POST['frOther'], $_POST['toOther'], "", $_POST['operOther'], "N");
	$returnValue=Build_WildCard("Upper(a.FLDESC)", "Type Description", $_POST['srchType'], "U", $_POST['operType'], "A");
	$returnValue=Build_WildCard("Upper(b.FLDESC)", "Payment Entry Description", $_POST['srchEntry'], "U", $_POST['operEntry'], "A");
	$returnValue=Build_WildCard("Upper(c.FLDESC)", "Status Description", $_POST['srchStatus'], "U", $_POST['operStatus'], "A");
	$returnValue=Build_WildCard("Upper(e.FLDESC)", "Pending Payment Description", $_POST['srchPend'], "U", $_POST['operPend'], "A");
	require_once 'WildCardUpdate.php';
}

if ($tag != "Edit_Data") {
	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);
	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n var optionWin;";
	require_once 'AJAXRequest.js';
	require_once 'CheckSel.js';
	require_once 'Menu.js';

	$formName = "Search";  // Need to Calendar Include
	require_once 'CalendarInclude.php';
	$formName = "Chg";
	require_once 'CheckEnterSearch.php';
	require_once 'DateEdit.php';
	require_once 'NoFormValidate.php';
	require_once 'NumEdit.php';
	require_once 'ShowHideSelCriteria.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "APPLCASHBATCHINUSE";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
	print "\n <tr><td><h1>$page_title</h1></td>";

	if ($formatToPrint != "Y"){
		print "\n <td class=\"toolbar\">";
		print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
		require_once 'FormatToprint.php';
		require_once 'HelpPage.php';
		print "</td>";
	}
	print "\n </tr></table>";
	require_once 'ConfMessageDisplay.php';
	print $hrTagAttr;

	$uv_BankName ="BUBCHB";
	require 'UserView.php';

	require 'stmtSQLClear.php';
	$stmtSQL .= " Select BUBCHN,BUBCHD,BUBCHB,BUUSER, ";
	$stmtSQL .= " Coalesce(BMBCHT,' ') as BMBCHT,Coalesce(BMPMTE,' ') as BMPMTE,Coalesce(BMPPMT,' ') as BMPPMT, ";
	$stmtSQL .= " Coalesce(BMDEPA,0) as BMDEPA,Coalesce(BMADJT,0) as BMADJT,Coalesce(BMBCHS,' ') as BMBCHS,";
	$stmtSQL .= " Coalesce(USDESC,' ') as USDESC, Coalesce(USDESCU,' ') as USDESCU, ";
	$stmtSQL .= " Coalesce(BKBKNM,' ') as BKBKNM, Coalesce(Upper(BKBKNM),' ') as BKBKNMU, ";
	$stmtSQL .= " Coalesce(a.FLDESC,' ') as FLDESC_BMBCHT, Coalesce(Upper(a.FLDESC),' ') as FLDESC_BMBCHTU,";
	$stmtSQL .= " Coalesce(b.FLDESC,' ') as FLDESC_BMPMTE, Coalesce(Upper(b.FLDESC),' ') as FLDESC_BMPMTEU,";
	$stmtSQL .= " Coalesce(c.FLDESC,' ') as FLDESC_BMBCHS, Coalesce(Upper(c.FLDESC),' ') as FLDESC_BMBCHSU,";
	$stmtSQL .= " Coalesce(e.FLDESC,' ') as FLDESC_BMPPMT, Coalesce(Upper(e.FLDESC),' ') as FLDESC_BMPPMTU,";
	$stmtSQL .= " (Select Count(*) from ARYPTD y Where (YPBCH,YPBDAT,YPBANK,YPRPSQ)=(z.BUBCHN,z.BUBCHD,z.BUBCHB,0) and (YPPYCD<>'8' or YPIAMT<>0) and not exists (Select * from ARRVHL Where (RHISEQ,RHPSEQ)=(y.YPISEQ,y.YPPSEQ))) as ARYPTDOPEN, ";
	$stmtSQL .= " Coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB)=(z.BUBCHN,z.BUBCHD,z.BUBCHB)),0) + Coalesce((Select Count(*) from ARDCER Where (CRBCHN,CRBCHD,CRBCHB)=(z.BUBCHN,z.BUBCHD,z.BUBCHB)),0) as ARPYENERROR ";
	$fileSQL .= " ARPBCU z ";
	$fileSQL .= " left join ARPBCH   on (BMBCHN,BMBCHD,BMBCHB)=(BUBCHN,BUBCHD,BUBCHB) ";
	$fileSQL .= " left join SYUSER   on USUSER=BUUSER ";
	$fileSQL .= " left join HDBANK   on BKBANK=BUBCHB ";
	$fileSQL .= " left join SYFLAG a on (a.FLTYPE,a.FLVALU)=('ARBCHTYPE',BMBCHT) ";
	$fileSQL .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTENTR',BMPMTE) ";
	$fileSQL .= " left join SYFLAG c on (c.FLTYPE,c.FLVALU)=('ARBCHSTAT',BMBCHS) ";
	$fileSQL .= " left join SYFLAG e on (e.FLTYPE,e.FLVALU)=('BY',BMPPMT)  ";
	if ($wildCardSearch != "" || $uv_Sql!="") {$selectSQL  = "BUBCHN=BUBCHN " ;}
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By $orderBy ";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($formatToPrint == ""){
		$qsOpt = "";
		$qsOpt .= "\n <option value=\"BUBCHN|null|Batch|N|\" title=\"Batch\" SELECTED>Batch";
		$qsOpt .= "\n <option value=\"BUBCHD|DATE|Batch Date|D|\" title=\"Batch Date\">Batch Date";
		$qsOpt .= "\n <option value=\"Upper(BKBKNM)|null|Bank Name|A|U\" title=\"Bank Name\">Bank Name";
		$qsOpt .= "\n <option value=\"USDESCU|null|User Name|A|U\" title=\"User Name\">User Name";
		$qsOpt .= "\n <option value=\"BMDEPA|null|Deposit Total|N|\" title=\"Deposit Total\">Deposit Total";
		$qsOpt .= "\n <option value=\"BMADJT|null|Other Total|N|\" title=\"Other Total\">Other Total";
		$qsOpt .= "\n <option value=\"Upper(a.FLDESC)|null|Type Description|A|U\" title=\"Type Description\">Type Description";
		$qsOpt .= "\n <option value=\"Upper(b.FLDESC)|null|Payment Entry Description|A|U\" title=\"Payment Entry Description\">Payment Entry Description";
		$qsOpt .= "\n <option value=\"Upper(c.FLDESC)|null|Status Description|A|U\" title=\"Status Description\">Status Description";
		$qsOpt .= "\n <option value=\"Upper(e.FLDESC)|null|Pending Payment Description|A|U\" title=\"Pending Payment Description\">Pending Payment Description";
		require 'QuickSearchOption.php';
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=U&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\" onSubmit=\"return false;\">";
	print "\n <table $contentTable> <tr>";
	if ($formatToPrint == ""){
		print "<th class=\"colhdr\">Remove</th>";
	}
	$returnValue=OrderBy_Sort("BUBCHN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Batch\"      title=\"Sequence By Batch\">{$sortPoint}Batch</a></th>";
	$returnValue=OrderBy_Sort("BUBCHD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Date\"       title=\"Sequence By Batch Date, Batch\">{$sortPoint}Batch Date</a></th>";
	$returnValue=OrderBy_Sort("BKBKNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Bank\"       title=\"Sequence By Bank, Batch\">{$sortPoint}Bank</a></th>";
	$returnValue=OrderBy_Sort("USDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\"       title=\"Sequence By User, Batch\">{$sortPoint}User</a></th>";
	$returnValue=OrderBy_Sort("BMDEPA"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Deposit\"    title=\"Sequence By Deposit Total, Batch\">{$sortPoint}Deposit Total</a></th>";
	$returnValue=OrderBy_Sort("BMADJT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Other\" title=\"Sequence By Other Total, Batch\">{$sortPoint}Other Total</a></th>";
	$returnValue=OrderBy_Sort("FLDESC_BMBCHTU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Type\"       title=\"Sequence By Type, Batch\">{$sortPoint}Type</a></th>";
	$returnValue=OrderBy_Sort("FLDESC_BMPMTEU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Entry\"      title=\"Sequence By Payment Entry, Batch\">{$sortPoint}Payment Entry</a></th> ";
	$returnValue=OrderBy_Sort("FLDESC_BMBCHSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Status\"     title=\"Sequence By Status, Batch\">{$sortPoint}Status</a></th>";
	$returnValue=OrderBy_Sort("FLDESC_BMPPMTU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Pending\"    title=\"Sequence By Pending Payment, Batch\">{$sortPoint}Pending Payment</a></th>";
	$returnValue=OrderBy_Sort("ARPYENERROR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Errors\"    title=\"Sequence By Number of Errors, Batch\">{$sortPoint}Number of Errors</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		$maintainVar = "{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['BUBCHN'])) . "&amp;fromBatchDate=" . urlencode(trim($row['BUBCHD'])) . "&amp;fromBatchBank=" . urlencode(trim($row['BUBCHB'])) . "&amp;fromScript=" . urlencode(trim($scriptName));
		$F_BUBCHD=Format_Date($row['BUBCHD'], "D");
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\"> ";
		if ($formatToPrint == ""){
			print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"selc{$rowCount}\" id=\"selc{$rowCount}\" value='D' title=\"Remove User from Batch\"></td> ";
		}
		print "\n     <td class=\"colnmbr\"><input type=\"hidden\" name=\"bchn{$rowCount}\" id=\"bchn{$rowCount}\" value=\"" . rtrim($row['BUBCHN']) . "\">$row[BUBCHN]</td>";
		print "\n     <td class=\"colnmbr\"><input type=\"hidden\" name=\"bchd{$rowCount}\" id=\"bchd{$rowCount}\" value=\"" . rtrim($row['BUBCHD']) . "\">$F_BUBCHD</td>";
		print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[BUBCHB]\"><input type=\"hidden\" name=\"bchb{$rowCount}\" id=\"bchb{$rowCount}\" value=\"" . rtrim($row['BUBCHB']) . "\">$row[BKBKNM]</span></td>";
		print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[BUUSER]\"><input type=\"hidden\" name=\"user{$rowCount}\" id=\"user{$rowCount}\" value=\"" . rtrim($row['BUUSER']) . "\">$row[USDESC]</span></td>";
		$ARDEPDRecCount=RetValue("(BDBCHN,BDBCHD,BDBCHB)=($row[BUBCHN],$row[BUBCHD],$row[BUBCHB])", "ARDEPD", "Count(*)");
		if ($row['BMBCHT']=="D" && $ARDEPDRecCount) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ARDepositEntryInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Deposit Entry\">" . Format_Nbr ( $row['BMDEPA'], '2', $amtEditCode, 'Y', '', '') . "</a></td>";}
		else                                        {print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['BMDEPA'], '2', $amtEditCode, 'Y', '', '') . "</td>";}
		print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['BMADJT'], '2', $amtEditCode, 'Y', '', '') . "</td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMBCHT]\">$row[FLDESC_BMBCHT]</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMPMTE]\">$row[FLDESC_BMPMTE]</span></td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BMBCHS]\">$row[FLDESC_BMBCHS]</span></td>";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[BMPPMT]\">$row[FLDESC_BMPPMT]</span></td>";
		if ($row['ARPYENERROR']==0) {print "\n <td class=\"colnmbr\">$row[ARPYENERROR]</td>";}
		else                        {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashErrorInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Error\">$row[ARPYENERROR]</a></td>";}
		print "\n </tr>";
		$rowCount ++;
		$startRow ++;
	}

	print "\n   <tr><td><input type=\"hidden\" name=\"batchCount\" value=\"" . rtrim($rowCount) . "\"></td></tr>";
	if ($rowCount == 0){require 'NoRecordsFound.php';}

	print "</table> ";
	print "\n </form>";
    require_once 'PageBottom.php';
	print "$hrTagAttr";
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "\n </body> \n </html>";

}

if ($tag == "Edit_Data") {
	$ux = 0;
	$batchCount=$_POST['batchCount'];
	while ($ux < $batchCount) {
		if ($_POST["selc" . $ux]=="D") {
			$bchn=$_POST["bchn" . $ux];
			$bchd=$_POST["bchd" . $ux];
			$bchb=$_POST["bchb" . $ux];
			$user=$_POST["user" . $ux];
			$stmtSQL = "";
			$stmtSQL .= " Delete From ARPBCU ";
			$stmtSQL .= " Where (BUBCHN,BUBCHD,BUBCHB,BUUSER)=($bchn,$bchd,$bchb,'$user') ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

			$stmtSQL = "";
			$stmtSQL .= " Delete From HDCUSI ";
			$stmtSQL .= " Where (CIIBCH,CIIDTE,CIIBNK,CIIUSR)=($bchn,$bchd,$bchb,'$user') ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

			$stmtSQL = "";
			$stmtSQL .= "  Update ARPBCH Set BMPMTE=' ' Where (BMBCHN,BMBCHD,BMBCHB)=($bchn,$bchd,$bchb) ";
			$stmtSQL .= "  and not exists (Select * from ARPBCU Where (BUBCHN,BUBCHD,BUBCHB)=($bchn,$bchd,$bchb)) ";
			$stmtSQL .= "  and not exists (Select * from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB)=($bchn,$bchd,$bchb)) ";
			$stmtSQL .= "  and not exists (Select * from ARYPTD Where (YPBCH,YPBDAT,YPBANK)=($bchn,$bchd,$bchb)) ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		}
		$ux ++;
	}
	print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
}

?>
