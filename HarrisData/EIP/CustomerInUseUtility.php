<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title    = "Clear Customer In Use";
$scriptName    = "CustomerInUseUtility.php";
$scriptVarBase = "{$genericVarBase}";
$nextPrevVar   = "{$scriptVarBase}";
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL     = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows    = $dspMaxRowsDft;
$prtMaxRows    = $prtMaxRowsDft;
$dftOrderBy    = array(array("CICUST","A","Customer"));
$programName   = "HARZZU";
$medIcon       = "Y";

$harzzu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);  // Program Option Security
if ($harzzu_OPT['sec_03']!="Y") {
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
	require_once 'Menu.js';

	require_once 'CheckEnterSearch.php';
	require_once 'NumEdit.php';

	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.Search.srchCust, 7, 0) && ";
	print "\n     editNum(document.Search.srchPhone, 11, 0) && ";
	print "\n     editNum(document.Search.srchLoc, 3, 0) && ";
	print "\n     editNum(document.Search.srchSalesman, 3, 0) && ";
	print "\n     editNum(document.Search.srchPriority, 3, 1)) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "L";    // L=List, S=Search, I=Inquiry
	$pageID = "APPLCASHCUSTOMERSEARCH";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","20","26");
	Build_AdvSrch_Entry("Customer","srchCust","","operCust","opersel_num_short","N","7","7");

	print "\n <tr><td class=\"dsphdr\">Address</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchAddress\" size=\"20\" maxlength=\"26\"></td>";
	print "\n </tr>";

	$operNbr = "operSt";
	print "\n <tr><td class=\"dsphdr\">State</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchState\" size=\"2\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}StateSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchState&amp;fldDesc=stateDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"stateDesc\"></span></td>";
	print "\n </tr>";

	Build_AdvSrch_Entry("Zip","srchZip","","operZip","opersel_alph_short","A","20","14");
	Build_AdvSrch_Entry("Credit Contact","srchContact","","operContact","opersel_alph_short","A","16","16");
	Build_AdvSrch_Entry("Phone","srchPhone","","","","P","20","11");

	$operNbr = "operTerms";
	print "\n <tr><td class=\"dsphdr\">Terms Code</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchTerms\" size=\"2\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchTerms&amp;fldDesc=termsCodeDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"termsCodeDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operClass";
	print "\n <tr><td class=\"dsphdr\">Class Code</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchClass\" size=\"2\" maxlength=\"2\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerClassSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchClass&amp;fldDesc=classCodeDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"classCodeDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operRegion";
	print "\n <tr><td class=\"dsphdr\">Customer Region</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchRegion\" size=\"5\" maxlength=\"5\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}RegionSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchRegion&amp;fldDesc=regionDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"regionDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operLoc";
	print "\n <tr><td class=\"dsphdr\">Location</td>";
	print "\n     <td>"; require "opersel_num_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"srchLoc\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchLoc&amp;fldDesc=srchLocDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"srchLocDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operSalesman";
	print "\n <tr><td class=\"dsphdr\">Salesman</td>";
	print "\n     <td>"; require "opersel_num_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"srchSalesman\" size=\"3\" maxlength=\"3\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchSalesman&amp;fldDesc=salesmanDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"salesmanDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operCountry";
	print "\n <tr><td class=\"dsphdr\">Country</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchCountry\" size=\"3\" maxlength=\"3\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CountrySearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchCountry&amp;fldDesc=countryDesc\" onclick=\"$searchWinVar\">$searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"countryDesc\"></span></td>";
	print "\n </tr>";

	$operNbr = "operHold";
	print "\n <tr><td class=\"dsphdr\">Hold Code</td>";
	print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
	print "\n     <td class=\"inputalph\"><input name=\"srchHold\" type=\"text\" size=\"4\" maxlength=\"4\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}HoldCodeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Search&amp;fldType=O&amp;fldName=srchHold&amp;fldDesc=srchHoldDesc\" onclick=\"$searchWinVar\"> $searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"srchHoldDesc\">$fieldDesc</span></td>";
	print "\n </tr>";

	if ($CRSCR1 != "") {Build_AdvSrch_Entry("$CRSCR1","srchUDF1","","operUDF1","opersel_alph_short","A","15","15");}
	if ($CRSCR2 != "") {Build_AdvSrch_Entry("$CRSCR2","srchUDF2","","operUDF2","opersel_alph_short","A","15","15");}
	if ($CRSCR3 != "") {Build_AdvSrch_Entry("$CRSCR3","srchUDF3","","operUDF3","opersel_alph_short","A","15","15");}
	if ($CRSCR4 != "") {Build_AdvSrch_Entry("$CRSCR4","srchUDF4","","operUDF4","opersel_alph_short","A","15","15");}
	if ($CRSCR5 != "") {Build_AdvSrch_Entry("$CRSCR5","srchUDF5","","operUDF5","opersel_alph_short","A","15","15");}
	Build_AdvSrch_Entry("Management Priority","srchPriority","","operMgmt","opersel_num_short","N","5","5");

	$focusField = "srchName";
	require_once 'AdvSearchBottom.php';
}

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Name")      {$orby = array(array("CMCNA1U","A","Name"),array("CICUST","A","Customer"));}
	elseif ($sequence == "Customer")  {$orby = array(array("CICUST","A","Customer"));}
	elseif ($sequence == "User")      {$orby = array(array("USDESCU","A","User"),array("CICUST","A","Customer"));}
	elseif ($sequence == "PartPayer") {$orby = array(array("FLDESCU","A","Part Of Payer"),array("CICUST","A","Customer"));}
	elseif ($sequence == "City")      {$orby = array(array("CMCCTYU","A","City"),array("CICUST","A","Customer"));}
	elseif ($sequence == "State")     {$orby = array(array("CMST","A","State"),array("CICUST","A","Customer"));}
	elseif ($sequence == "Method")    {$orby = array(array("PSDESCU","A","Preferred Payment Method"),array("CICUST","A","Customer"));}
	elseif ($sequence == "OpenAR")    {$orby = array(array("ARCARB","A","Open A/R"),array("CICUST","A","Customer"));}
	elseif ($sequence == "InUse")     {$orby = array(array("CIIBCH","A","In Use By Batch"),array("CICUST","A","Customer"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$_POST['srchAddress'] = trim($_POST['srchAddress']);
	if ($_POST['srchAddress'] != ""){
		$returnValue=Build_WildCard("CMCNA2U", "Address", $_POST['srchAddress'], "U", "LIKE", "V");
		$_POST['srchAddress'] = Build_SelData($_POST['srchAddress'],"U","LIKE","A");
		$wildCardTemp .= " (trim(CMCNA2U)  LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(CMCNA3U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(CMCNA4U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(CMCCTYU) LIKE '$_POST[srchAddress]')";
	}
	$returnValue=Build_WildCard ("CMCNA1U", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard ("CICUST", "Customer", $_POST['srchCust'], "", $_POST['operCust'], "N");
	$returnValue=Build_WildCard ("USDESCU", "User Name", $_POST['srchUser'], "U", $_POST['operUser'], "A");
	$returnValue=Build_WildCard ("CMST", "State", $_POST['srchState'], "U", $_POST['operSt'], "A");
	$returnValue=Build_WildCard ("CMZIP", "Zip", $_POST['srchZip'], "", $_POST['operZip'], "A");
	$returnValue=Build_WildCard ("upper(CMCRCT)", "Credit Contact", $_POST['srchContact'], "U", $_POST['operContact'], "A");
	$returnValue=Build_WildCard ("CMPHON", "Phone", $_POST['srchPhone'], "", "", "P");
	$returnValue=Build_WildCard ("CMCTRM", "Terms Code", $_POST['srchTerms'], "U", $_POST['operTerms'], "A");
	$returnValue=Build_WildCard ("CMCCLS", "Class Code", $_POST['srchClass'], "U", $_POST['operClass'], "A");
	$returnValue=Build_WildCard ("CMCRGN", "Customer Region", $_POST['srchRegion'], "U", $_POST['operRegion'], "A");
	$returnValue=Build_WildCard ("CMLOC#", "Location", $_POST['srchLoc'], "", $_POST['operLoc'], "N");
	$returnValue=Build_WildCard ("CMSLSM", "Salesman", $_POST['srchSalesman'], "", $_POST['operSalesman'], "N");
	$returnValue=Build_WildCard ("CMCTRY", "Country", $_POST['srchCountry'], "U", $_POST['operCountry'], "A");
	$returnValue=Build_WildCard ("CMCHLD", "Hold Code", $_POST['srchHold'], "U", $_POST['operHold'], "A");
	$returnValue=Build_WildCard ("upper(CMUDF1)", $CRSCR1, $_POST['srchUDF1'], "U", $_POST['operUDF1'], "A");
	$returnValue=Build_WildCard ("upper(CMUDF2)", $CRSCR2, $_POST['srchUDF2'], "U", $_POST['operUDF2'], "A");
	$returnValue=Build_WildCard ("upper(CMUDF3)", $CRSCR3, $_POST['srchUDF3'], "U", $_POST['operUDF3'], "A");
	$returnValue=Build_WildCard ("upper(CMUDF4)", $CRSCR4, $_POST['srchUDF4'], "U", $_POST['operUDF4'], "A");
	$returnValue=Build_WildCard ("upper(CMUDF5)", $CRSCR5, $_POST['srchUDF5'], "U", $_POST['operUDF5'], "A");
	$returnValue=Build_WildCard ("CMMPTY", "Management Priority", $_POST['srchPriority'], "", $_POST['operMgmt'], "N");
	require_once 'WildCardUpdate.php';
}

if ($tag != "Edit_Data") {
	require_once ($docType);
	print "\n <html> \n	<head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n var optionWin;";
	require_once 'AJAXRequest.js';
	require_once 'CheckSel.js';
	require_once 'Menu.js';

	require_once 'CheckEnterSearch.php';
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
	$pageID = "CUSTOMERINUSE";
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

	$uv_CustomerName ="CMCUST";
	$uv_CustomerClassName ="CMCCLS";
	$uv_RegionName ="CMCRGN";
	require 'UserView.php';

	require 'stmtSQLClear.php';
	$stmtSQL .= " Select CICUST,CIIUSR,CIIBCH,CIIDTE,CIIBNK,CITYPE,CIID,";
	$stmtSQL .= " Coalesce(CMCNA1,' ') as CMCNA1, Coalesce(CMCNA1U,' ') as CMCNA1U,";
	$stmtSQL .= " Coalesce(CMCNA2,' ') as CMCNA2, Coalesce(CMCNA3,' ') as CMCNA3,";
	$stmtSQL .= " Coalesce(CMCNA4,' ') as CMCNA4,";
	$stmtSQL .= " Coalesce(CMCCTY,' ') as CMCCTY, Coalesce(CMCCTYU,' ') as CMCCTYU,";
	$stmtSQL .= " Coalesce(CMST,' ')   as CMST  , Coalesce(CMZIP,' ') as CMZIP,";
	$stmtSQL .= " Coalesce(CMSBCD,' ') as CMSBCD,";
	$stmtSQL .= " Coalesce(ARCARB,0) as ARCARB,";
	$stmtSQL .= " Coalesce(FLDESC,' ') as FLDESC, Coalesce(Upper(FLDESC),' ') as FLDESCU,";
	$stmtSQL .= " Coalesce(PSDESC,' ') as PSDESC, Coalesce(PSDESCU,' ') as PSDESCU,";
	$stmtSQL .= " Coalesce(USDESC,' ') as USDESC, Coalesce(USDESCU,' ') as USDESCU ";
	$fileSQL .= " HDCUSI z ";
	$fileSQL .= " left join HDCUST on CMCUST=CICUST";
	if ($HDMCRL>0 && $CRPRMC=="Y") {$fileSQL .= " left join HDCARB on (ARCUST,ARCTYP,ARCURT)=(CICUST,'I',(Select BKCURT from HDBANK Where BKBANK=z.CIIBNK))"; }
	else                           {$fileSQL .= " left join HDCARB on (ARCUST,ARCTYP,ARCURT)=(CICUST,'I',' ') ";}
	$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('BY',Case When (Select Count(*) from ARPYRC Where PCCUST=z.CICUST)>0 Then 'Y' Else ' ' End)";
	$fileSQL .= " left join ARPYSB on PSSBCD=CMSBCD ";
	$fileSQL .= " left join SYUSER on USUSER=CIIUSR ";
	if ($wildCardSearch != "" || $uv_Sql!="") {$selectSQL  = "(CICUST=CICUST)" ;}
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By $orderBy ";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($formatToPrint == ""){
		$qsOpt = "";
		$qsOpt .= "\n <option value=\"CMCNA1U|null|Name|A|U\" title=\"Name\" SELECTED>Name";
		$qsOpt .= "\n <option value=\"CICUST|null|Customer|N|\" title=\"Customer\">Customer";
		$qsOpt .= "\n <option value=\"Coalesce(USDESCU,' ')|null|In Use By User Name|A|U\" title=\"In Use By User Name\">In Use By User Name";
		$qsOpt .= "\n <option value=\"Coalesce(Upper(FLDESC),' ')|null|Part of Payer Description|A|U\" title=\"Part of Payer Description\">Part of Payer Description";
		$qsOpt .= "\n <option value=\"CMCCTYU|null|City|A|U\" title=\"City\">City";
		$qsOpt .= "\n <option value=\"CMST|null|State|A|U\" title=\"State\">State";
		$qsOpt .= "\n <option value=\"Coalesce(PSDESCU,' ')|null|Preferred Payment Method Description|A|U\" title=\"Preferred Payment Method Description\">Preferred Payment Method Description";
		$qsOpt .= "\n <option value=\"Coalesce(ARCARB,0)|null|Open A/R|N|\" title=\"Open A/R\">Open A/R";
		$qsOpt .= "\n <option value=\"Coalesce(CIIBCH,0)|null|In Use By Batch|N|\" title=\"In Use By Batch\">In Use By Batch";
		require 'QuickSearchOption.php';
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=U&amp;wrnVar=" . urlencode(trim($wrnVar)) . "\" onSubmit=\"return false;\">";
	print "\n <table $contentTable> <tr>";
	if ($formatToPrint == ""){
		print "<th class=\"colhdr\">Remove</th>";
	}
	$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\"      title=\"Sequence By Name\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("CICUST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Customer\"  title=\"Sequence By Customer\">{$sortPoint}Customer</a></th>";
	$returnValue=OrderBy_Sort("USDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\"      title=\"Sequence By In Use By User\">{$sortPoint}In Use By User</a></th>";
	$returnValue=OrderBy_Sort("FLDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PartPayer\" title=\"Sequence By Part of Payer\">{$sortPoint}Part of Payer</a></th>";
	$returnValue=OrderBy_Sort("CMCCTYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\"      title=\"Sequence By City\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("CMST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"     title=\"Sequence By State\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Method\"    title=\"Sequence By Preferred Payment Method\">{$sortPoint}Preferred Payment Method</a></th>";
	$returnValue=OrderBy_Sort("ARCARB"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OpenAR\"    title=\"Sequence By Open A/R\">{$sortPoint}Open A/R</a></th> ";
	$returnValue=OrderBy_Sort("CIIBCH"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InUse\"     title=\"Sequence By In Use By Batch\">{$sortPoint}In Use By Batch</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		$maintainVar = "{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['CIIBCH'])) . "&amp;fromBatchDate=" . urlencode(trim($row['CIIDTE'])) . "&amp;fromBatchBank=" . urlencode(trim($row['CIIBNK'])) . "&amp;fromType=" . urlencode(trim($row['CITYPE'])) . "&amp;fromID=" . urlencode(trim($row['CIID'])) . "&amp;fromScript=" . urlencode(trim($scriptName));
		$maintainVarD2w = "{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['CICUST']));
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\"> ";
		if ($formatToPrint == ""){
			print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"selc{$rowCount}\" id=\"selc{$rowCount}\" value='D' title=\"Remove User from Customer\"></td> ";
		}
		$address=trim($row['CMCNA2']);
		if (trim($row['CMCNA3'])!="") {$address .= ", " . trim($row['CMCNA3']);}
		if (trim($row['CMCNA4'])!="") {$address .= ", " . trim($row['CMCNA4']);}
		$address .= ", " . trim($row['CMCCTY']) . " " . $row[CMST] . " " . $row[CMZIP];

		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$maintainVarD2w}&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer\">$row[CMCNA1]</a></td> ";
		print "\n <td class=\"colnmbr\"><input type=\"hidden\" name=\"cust{$rowCount}\" id=\"cust{$rowCount}\" value=\"" . rtrim($row['CICUST']) . "\">$row[CICUST]</td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[CIIUSR]\">$row[USDESC]</span></td>";
		if (trim($row['FLDESC'])=="Yes") {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}PayerInquiry.php{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Payer\">$row[FLDESC]</a></td>";}
		else                             {print "\n <td class=\"colalph\">$row[FLDESC]</td>";}
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$address\">$row[CMCCTY]</span></td>";
		print "\n <td class=\"colalph\">$row[CMST]</td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[CMSBCD]\">$row[PSDESC]</span></td>";
		print "\n <td class=\"colnmbr\">" . number_format($row['ARCARB'],2) . "</td>";
		print "\n <td class=\"colnmbr\">$row[CIIBCH]</td>";
		print "\n </tr>";
		$rowCount ++;
		$startRow ++;
	}

	print "\n   <tr><td><input type=\"hidden\" name=\"CustomerCount\" value=\"" . rtrim($rowCount) . "\"></td></tr>";
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
	$CustomerCount=$_POST['CustomerCount'];
	while ($ux < $CustomerCount) {
		if ($_POST["selc" . $ux]=="D") {
			$cust=$_POST["cust" . $ux];
			$stmtSQL = "";
			$stmtSQL .= " Delete From HDCUSI ";
			$stmtSQL .= " Where CICUST=$cust ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		}
		$ux ++;
	}
	print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
}

?>
