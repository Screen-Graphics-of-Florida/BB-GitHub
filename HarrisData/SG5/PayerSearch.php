<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'ARPmtTypeInclude.php';

$docName            = $_GET['docName'];
$fldName            = $_GET['fldName'];
$fldDesc            = $_GET['fldDesc'];
$moreInfo           = $_GET['moreInfo'];
$Payer              = $_GET['Payer'];
$forCustomer        = $_GET['forCustomer'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Payer Search";
$scriptName     = "PayerSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;forCustomer=" . urlencode(trim($forCustomer)) . "&amp;docName=" . urlencode(trim($docName)) . "&amp;fldName=" . urlencode(trim($fldName)) . "&amp;fldDesc=" . urlencode(trim($fldDesc));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("PYPYNMU","A","Name"));

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CalendarInclude.php';
	require_once 'DateEdit.php';
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	print "\n function validate(searchForm) {";
	print "\n   if (editNum(document.Search.srchPayer, 7, 0) && ";
	print "\n       editNum(document.Search.srchPhone, 11, 0) && ";
	print "\n       editNum(document.Search.srchFax, 11, 0) && ";
	print "\n       editNum(document.Search.srchContactPhone, 11, 0)) ";
	print "\n     return true;";
	print "\n }";
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("Name","srchName","","operName","opersel_alph_short","A","20","26");
	Build_AdvSrch_Entry("Payer","srchPayer","","operPayer","opersel_num_short","N","7","7");

	print "\n <tr><td class=\"dsphdr\">Address</td>";
	print "\n     <td>&nbsp;</td>";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchAddress\" size=\"20\" maxlength=\"26\"></td>";
	print "\n </tr>";

	Build_AdvSrch_Entry("State","srchState","","operSt","opersel_alph_short","A","2","2");
	Build_AdvSrch_Entry("Zip","srchZip","","operZip","opersel_alph_short","A","13","13");
	Build_AdvSrch_Entry("Country","srchCountry","","operCountry","opersel_alph_short","A","3","3");
	Build_AdvSrch_Entry("Phone","srchPhone","","","opersel_alph_short","P","11","11");
	Build_AdvSrch_Entry("Fax","srchFax","","","opersel_alph_short","P","11","11");
	Build_AdvSrch_Entry("Collector","srchCollector","","operCollector","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Contact","srchContact","","operContact","opersel_alph_short","A","16","16");
	Build_AdvSrch_Entry("Contact Phone","srchContactPhone","","","opersel_alph_short","P","11","11");
	Build_AdvSrch_Entry("Preferred Payment Method Description","srchPmtCode","","operPmtCode","opersel_alph_short","A","10","30");
	Build_AdvSrch_Entry("Retain Credit Card","srchUpdCard","","operUpdCard","opersel_alph_short","A","1","1");

	$focusField = "srchName";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "Name")      {$orby = array(array("PYPYNMU","A","Name"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "Payer")     {$orby = array(array("PYPAYR","A","Payer"));}
	elseif ($sequence == "City")      {$orby = array(array("PYCITYU","A","City"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "State")     {$orby = array(array("PYST","A","State"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "Zip")       {$orby = array(array("PYZIP","A","Zip"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "Phone")     {$orby = array(array("PYPHON","A","Phone"),array("PYPAYR","A","Payer"));}
	elseif ($sequence == "Method")    {$orby = array(array("PSDESCU","A","Preferred Payment Method"),array("PYPAYR","A","Payer"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';

	$returnValue=Build_WildCard ("PYPYNMU", "Name", $_POST['srchName'], "U", $_POST['operName'], "A");
	$returnValue=Build_WildCard ("PYPAYR", "Payer", $_POST['srchPayer'], "", $_POST['operPayer'], "N");
	if (trim($_POST['srchAddress']) != ""){
		$returnValue=Build_WildCard("PYADR1U", "Address", $_POST['srchAddress'], "U", "LIKE", "V");
		$_POST['srchAddress'] = Build_SelData($_POST['srchAddress'],"U","LIKE","A");
		$wildCardTemp .= " (trim(PYADR1U)  LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(PYADR2U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(PYADR3U) LIKE '$_POST[srchAddress]'";
		$wildCardTemp .= " OR   trim(PYCITYU) LIKE '$_POST[srchAddress]')";
	}
	$returnValue=Build_WildCard ("PYST", "State", $_POST['srchState'], "U", $_POST['operSt'], "A");
	$returnValue=Build_WildCard ("PYZIP", "Zip", $_POST['srchZip'], "U", $_POST['operZip'], "A");
	$returnValue=Build_WildCard ("PYCTRY", "Country", $_POST['srchCountry'], "U", $_POST['operCountry'], "A");
	$returnValue=Build_WildCard ("PYPHON", "Phone", $_POST['srchPhone'], "", "", "P");
	$returnValue=Build_WildCard ("PYFAX", "Fax", $_POST['srchFax'], "", "", "P");
	$returnValue=Build_WildCard ("PYCTOR", "Collector", $_POST['srchCollector'], "U", $_POST['operCollector'], "A");
	$returnValue=Build_WildCard ("PYCONTU", "Contact", $_POST['srchContact'], "U", $_POST['operContact'], "A");
	$returnValue=Build_WildCard ("PYCPHN", "Contact Phone", $_POST['srchContactPhone'], "", "", "P");
	$returnValue=Build_WildCard ("PSDESCU", "Preferred Payment Method Description", $_POST['srchPmtCode'], "U", $_POST['operPmtCode'], "A");
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckSel.js';

require_once 'CheckEnterSearch.php';
require_once 'NoFormValidate.php';
require_once 'NumEdit.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectPayer(Payer, PayerName) { ";
print "\n   if (window.opener.document.getElementById('PopUpWindow')===true) {window.opener.document.getElementById('PopUpWindow').innerHTML = self.name;} ";
print "\n   window.opener.document.$docName.$fldName.value = Payer; ";
print "\n   if      (window.opener.document.$docName.$fldDesc)          {window.opener.document.$docName.$fldDesc.value = PayerName;} ";
print "\n   else if (window.opener.document.getElementById('$fldDesc')) {window.opener.document.getElementById('$fldDesc').innerHTML = PayerName;} ";
print "\n   window.opener.document.$docName.$fldName.focus(); ";
print "\n   window.close(); ";
print "\n } ";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
if ($moreInfo=="Y") {print "\n <body $bodyTagAttr>";}
else                {print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";}
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
require_once 'PageTitleInclude.php';
if ($forCustomer!="") {
	print "\n <table $contentTable> ";
	$CMCNA1=RetValue("CMCUST='$forCustomer'", "HDCUST", "CMCNA1");
	Format_Header("For Customer", $CMCNA1, $forCustomer);
	print "\n </table> ";
}
print $searchhrTagAttr;

$uv_PayerName ="PYPAYR";
require 'UserView.php';

require 'stmtSQLClear.php';
$stmtSQL .= " Select PYPAYR,PYPYNM,PYADR1,PYADR2,PYADR3,PYCITY,PYST,  PYZIP, ";
$stmtSQL .= "        PYCTRY,PYPHON,PYFAX ,PYCTOR,PYCONT,PYCPHN,PYEMAL,PYSBCD, ";
$stmtSQL .= "        PYPYNMU,PYCITYU,";
$stmtSQL .= " Coalesce(PSDESC,' ') as PSDESC, Coalesce(PSDESCU,' ') as PSDESCU ";
$fileSQL .= " ARPYRH ";
$fileSQL .= " left join ARPYSB on PSSBCD=PYSBCD ";
if ($forCustomer!="") {$fileSQL .= " inner join ARPYRC on PCPAYR=PYPAYR ";}
if     ($moreInfo=="Y")                     {$selectSQL .=" PYPAYR=$Payer ";}
elseif ($forCustomer!="")                   {$selectSQL .=" PCCUST=$forCustomer ";}
elseif ($wildCardSearch!="" || $uv_Sql!="") {$selectSQL .=" PYPAYR=PYPAYR ";}

require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"PYPYNMU|null|Name|A|U\" title=\"Name\" SELECTED>Name";
	$qsOpt .= "\n <option value=\"PYPAYR|null|Payer|N|\" title=\"Payer\">Payer";
	$qsOpt .= "\n <option value=\"PYCITYU|null|City|A|U\" title=\"City\">City";
	$qsOpt .= "\n <option value=\"PYST|null|State|A|U\" title=\"State\">State";
	$qsOpt .= "\n <option value=\"PYZIP|null|Zip|A|U\" title=\"Zip\">Zip";
	$qsOpt .= "\n <option value=\"PYPHON|null|Phone|P|\" title=\"Phone\">Phone";
	$qsOpt .= "\n <option value=\"PSDESCU|null|Preferred Payment Method Description|A|U\" title=\"Preferred Payment Method Description\">Preferred Payment Method Description";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("PYPYNMU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Name\"      title=\"Sequence By Name\">{$sortPoint}Name</a></th>";
	$returnValue=OrderBy_Sort("PYPAYR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Payer\"  title=\"Sequence By Payer\">{$sortPoint}Payer</a></th>";
	$returnValue=OrderBy_Sort("PYCITYU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=City\"      title=\"Sequence By City\">{$sortPoint}City</a></th>";
	$returnValue=OrderBy_Sort("PYST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=State\"     title=\"Sequence By State\">{$sortPoint}State</a></th>";
	$returnValue=OrderBy_Sort("PYZIP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Zip\"     title=\"Sequence By Zip\">{$sortPoint}Zip</a></th>";
	$returnValue=OrderBy_Sort("PYPHON"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Phone\"     title=\"Sequence By Phone\">{$sortPoint}Phone</a></th>";
	$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Method\"    title=\"Sequence By Preferred Payment Method\">{$sortPoint}Preferred Payment Method</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';

		$F_PYPHON=EditPhoneNumber($row['PYPHON']);
		$F_PYPYNM=Format_Quote($row['PYPYNM']);
		$address=trim($row['PYADR1']);
		if (trim($row['PYADR2'])!="") {$address .= ", " . trim($row['PYADR2']);}
		if (trim($row['PYADR3'])!="") {$address .= ", " . trim($row['PYADR3']);}
		$address .= ", " . trim($row['PYCITY']) . " " . $row[PYST] . " " . $row[PYZIP];

		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectPayer($row[PYPAYR],'" . trim($F_PYPYNM) . "')\" title=\"Select Payer\">$row[PYPYNM]</a></td> ";
		print "\n <td class=\"colnmbr\">$row[PYPAYR]</td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$address\">$row[PYCITY]</span></td>";
		print "\n <td class=\"colalph\">$row[PYST]</td>";
		print "\n <td class=\"colalph\">$row[PYZIP]</td>";
		print "\n <td class=\"colalph\">$F_PYPHON</td>";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PYSBCD]\">$row[PSDESC]</span></td>";
		print "\n <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;Payer=" . urlencode(trim($row['PYPAYR'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";

} else {

	$row = db2_fetch_assoc($sqlResult);

	$F_PYPYNM=Format_Quote($row['PYPYNM']);
	$moreInfoSelect = "href=\"javascript:selectPayer($row[PYPAYR],'" . trim($F_PYPYNM) . "')\" title=\"Select Payer\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";
	Format_Header("Payer", $row[PYPYNM], $row[PYPAYR]);
	print "\n </table> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Demographics</legend> ";
	print "\n <table $contentTable> ";

	Build_DspFld("Address",$row['PYADR1'],"","A");
	if (trim($row['PYADR2']) != "") {Build_DspFld("",$row['PYADR2'],"","A");}
	if (trim($row['PYADR3']) != "") {Build_DspFld("",$row['PYADR3'],"","A");}
	if (trim($row['PYCITY']) != "" || trim($row['PYST']) != "" || trim($row['PYZIP']) != "") {
		Build_DspFld("","$row[PYCITY], $row[PYST] $row[PYZIP]","","A");
	}

	if (trim($row['PYCTRY']) != "" && $row['PYCTRY'] != $HDCTCD) {
		$fieldDesc=RetValue("CNCTCD='$row[PYCTRY]'", "HDCTRY", "CNCDES");
		$F_PYCTRY=Format_Code($row['PYCTRY']);
		Build_DspFld("Country",$fieldDesc,$F_PYCTRY,"A");
	}

	$F_PYPHON=EditPhoneNumber($row['PYPHON']);
	Build_DspFld("Phone",$F_PYPHON,"","A");

	$F_PYFAX=EditPhoneNumber($row['PYFAX']);
	Build_DspFld("Fax",$F_PYFAX,"","A");

	$F_PYSBCD=Format_Code($row['PYSBCD']);
	Build_DspFld("Preferred Payment Method",$row['PSDESC'],$F_PYSBCD,"A");

	$fieldDesc=RetValue("USUSER='$row[PYCTOR]'", "SYUSER", "USDESC");
	$F_PYCTOR=Format_Code($row['PYCTOR']);
	Build_DspFld("Collector",$fieldDesc,$F_PYCTOR,"A");
	print "\n </table> ";

	// *****************************************************************************
	// Demographics - Contact
	// *****************************************************************************
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Contact</legend> ";
	print "\n <table $contentTable> ";
	Build_DspFld("Name",$row['PYCONT'],"","A");

	$F_PYCPHN=EditPhoneNumber($row['PYCPHN']);
	Build_DspFld("Phone",$F_PYCPHN,"","A");

	Build_DspFld("E-mail",$row['PYEMAL'],"","A");
	print "\n </table> ";
	print "\n </fieldset> ";

	// *****************************************************************************
	// Demographics - User Defined
	// *****************************************************************************
	$appendUserView="N";  // Do not append user view security
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select UFFLDN,UFDESC,UFTYPE,UFSIZE,UFDECM,UFVALU,UFBOXS,UFREQF,UFVLDV,UFFFMT, ";
	$stmtSQL .= " PUFLDD,Coalesce(PUFLDR,0) as PUFLDR,Coalesce(PUFLDV,' ') as PUFLDV ";
	$fileSQL .= " SYUDFM ";
	$fileSQL .= " left join ARPYRU on (PUFLDN,PUPAYR)=(UFFLDN,$Payer) ";
	$selectSQL .= " (UFFILN,UFEVNT)=('ARPYRU',' ') ";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By UFFSEQ ";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">User-Defined</legend> ";
	print "\n <table $contentTable> ";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}

		require  'SetRowClass.php';

		print "\n <tr><td class=\"dsphdr\">$row[UFDESC]</td> ";
		if ($row['UFTYPE'] == "A") {
			if       ($row['UFFFMT'] == "U") {print "\n <td class=\"dspalph\"><a href=\"$row[PUFLDV]\" target=_blank>$row[PUFLDV]</a></td> ";
			} elseif ($row['UFFFMT'] == "E") {print "\n <td class=\"dspalph\"><a href=\"mailto:$row[PUFLDV]\" target=_blank title=\"Click here to send e-mail\">$row[PUFLDV]</a></td> ";
			} else                           {print "\n <td class=\"dspalph\">$row[PUFLDV] ";}
		} elseif ($row['UFTYPE'] == "C") {
			$printRows=substr_count(bin2hex($row['PUFLDV']),"0d0a");
			print "\n <td><textarea name=\"$row[UFFLDN]\" readonly ROWS=\"$printRows\" COLS=\"60\" WRAP=\"hard\">$row[PUFLDV]</textarea> ";
		} elseif ($row['UFTYPE'] == "N") {
			$row['PUFLDR']=number_format($row['PUFLDR'],$row['UFDECM'],".","");
			if   ($row['UFFFMT'] == "P") {$row['PUFLDR']=EditPhoneNumber($row['PUFLDR']);}
			print "\n <td class=\"dspnmbr\">$row[PUFLDR] ";

		} elseif ($row['UFTYPE'] == "D") {
			$row['PUFLDD']=Format_Date(Date_FromISO_ToCYMD($row['PUFLDD']), "H");
			print "\n <td class=\"dspnmbr\">$row[PUFLDD] ";
		}
		print "\n </td></tr> ";

		$startRow ++;
		$rowCount ++;
	}

	print "\n </table> ";
	print "\n </fieldset> ";

	print "\n </fieldset> ";

	// *****************************************************************************
	// Show Customers of Payer
	// *****************************************************************************
	$appendUserView="N";  // Do not append user view security
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select PCCUST, ";
	$stmtSQL .= " Coalesce(CMCNA1,' ') as CMCNA1, Coalesce(CMCNA1U,' ') as CMCNA1U, ";
	$stmtSQL .= " Coalesce(CMCNA2,' ') as CMCNA2, ";
	$stmtSQL .= " Coalesce(CMCCTY,' ') as CMCCTY, Coalesce(CMST  ,' ') as CMST  , ";
	$stmtSQL .= " Coalesce(CMZIP ,' ') as CMZIP , Coalesce(CMPHON,0) as CMPHON ";
	$fileSQL .= " ARPYRC ";
	$fileSQL .= " inner join HDCUST on CMCUST=PCCUST ";
	$selectSQL .= " PCPAYR = $Payer ";

	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By CMCNA1U";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Customer</legend> ";
	print "\n <table $contentTable> ";
	print "\n <tr> ";
	print "\n <th class=\"colhdr\">Customer</th> ";
	print "\n <th class=\"colhdr\">Name</th> ";
	print "\n <th class=\"colhdr\">Address</th> ";
	print "\n <th class=\"colhdr\">City</th> ";
	print "\n <th class=\"colhdr\">State</th> ";
	print "\n <th class=\"colhdr\">Zip</th> ";
	print "\n <th class=\"colhdr\">Phone</th> ";
	print "\n </tr> ";

	$rowCount = 0;
	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}

		require  'SetRowClass.php';
		$row['CMPHON']=EditPhoneNumber($row['CMPHON']);
		print "\n <tr class=\"$rowClass)\"> ";
		print "\n     <td class=\"colnmbr\">$row[PCCUST]</td> ";
		print "\n     <td class=\"colalph\">$row[CMCNA1]</td> ";
		print "\n     <td class=\"colalph\">$row[CMCNA2]</td> ";
		print "\n     <td class=\"colalph\">$row[CMCCTY]</td> ";
		print "\n     <td class=\"colcode\">$row[CMST]</td> ";
		print "\n     <td class=\"colalph\">$row[CMZIP]</td> ";
		print "\n     <td class=\"colalph\">$row[CMPHON]</td> ";
		print "\n </tr> ";

		$startRow ++;
		$rowCount ++;
	}
	print "\n </table> ";
	print "\n </fieldset> ";

	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
}
print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>			
