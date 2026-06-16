<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName  = $_GET['docName'];
$userFld  = $_GET['userFld'];
$descFld  = $_GET['descFld'];
$moreInfo = $_GET['moreInfo'];
$moreUser = $_GET['moreUser'];

require_once 'SetLibraryList.php';

require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "User Search";
$scriptName     = "UserSearch.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;userFld=" . urlencode(trim($userFld)) . "&amp;descFld=" . urlencode(trim($descFld));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftOrderBy     = array(array("USDESCU","A","Description"),array("USUSER","A","User"));

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
	require_once 'NoFormValidate.php';
	print "\n </script>";

	$scriptType = "S";    // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';

	Build_AdvSrch_Entry("User","srchUser","","operUser","opersel_alph_short","A","10","10");
	Build_AdvSrch_Entry("Description","srchDesc","","operDesc","opersel_alph_short","A","10","30");
	Build_AdvSrch_Entry("Role","srchRole","","operRole","opersel_alph_short","A","10","10");

	$focusField = "srchUser";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY"){
	if     ($sequence == "User")         {$orby = array(array("USUSER","A","User"),array("CHSUB","A",""));}
	elseif ($sequence == "Description")  {$orby = array(array("USDESCU","A","Description"),array("USUSER","A","User"));}
	elseif ($sequence == "Role")         {$orby = array(array("USROLE","A","Role"),array("USUSER","A","User"));}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH"){require_once 'QuickSearch.php';}

if ($tag == "WILDCARD"){
	$andOr = $_POST['andOr'];
	require_once 'WildCardClear.php';
	$returnValue=Build_WildCard("USUSER ", "User", $_POST['srchUser'], "U", $_POST['operUser'], "A");
	$returnValue=Build_WildCard("USDESCU", "Description", $_POST['srchDesc'], "U", $_POST['operDesc'], "A");
	$returnValue=Build_WildCard("USROLE", "Role", $_POST['srchRole'], "U", $_POST['operRole'], "A");
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
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';

print "\n function selectUser(user,desc){ ";
print "\n window.opener.document.$docName.$userFld.value = user; ";
print "\n if (window.opener.document.$docName.$descFld) ";
print "\n    {window.opener.document.$docName.$descFld.value = desc;} ";
print "\n else if (window.opener.document.getElementById('$descFld'))";
print "\n         {window.opener.document.getElementById('$descFld').innerHTML = desc;}";
print "\n window.opener.document.$docName.$userFld.focus(); ";
print "\n window.close(); ";
print "\n } ";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " SYUSER ";
if ($moreInfo=="Y")          {$selectSQL .= " USUSER='$moreUser' ";}
elseif ($wildCardSearch!="") {$selectSQL="USUSER<>' ' ";}
require 'stmtSQLSelect.php';
if ($moreInfo!="Y") {$stmtSQL .= " Order By $orderBy ";}
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

if ($moreInfo != "Y") {
	$qsOpt  = "\n <option value=\"USDESCU|null|Description|A|U\" title=\"Description\" SELECTED>Description";
	$qsOpt .= "\n <option value=\"USUSER|null|User|A|U\" title=\"User\">User";
	$qsOpt .= "\n <option value=\"USROLE|null|Role|A|U\" title=\"Role\">Role";
	require 'QuickSearchOption.php';

	print "<table $contentTable> <tr>";
	$returnValue=OrderBy_Sort("USDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Description\"  title=\"Sequence By Description, User\">{$sortPoint}Description</a></th>";
	$returnValue=OrderBy_Sort("USUSER"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=User\" title=\"Sequence By User\">{$sortPoint}User</a></th>";
	$returnValue=OrderBy_Sort("USROLE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Role\" title=\"Sequence By Role, User\">{$sortPoint}Role</a></th>";
	print "\n </tr>";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}
		require  'SetRowClass.php';
		$F_Desc=Format_Quote($row['USDESC']);
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\"><a href=\"javascript:selectUser('" . trim($row['USUSER']) . "','" . trim($F_Desc) . "')\" title=\"Select User\">$F_Desc</a></td> ";
		print "\n     <td class=\"colalph\">{$row['USUSER']}</td>";
		print "\n     <td class=\"colalph\">{$row['USROLE']}</td>";
		print "\n     <td class=\"colicon\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;moreUser=" . urlencode(trim($row['USUSER'])) . "&amp;moreInfo=Y\">$smMoreInfoImage</a></td> ";
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount == 0){require 'NoRecordsFound.php';}
	print "</table>";
} else {
	$row = db2_fetch_assoc($sqlResult);
	$F_Desc=Format_Quote($row['USDESC']);
	$moreInfoSelect = "href=\"javascript:selectUser('" . trim($row['USUSER']) . "','" . trim($F_Desc) . "')\" title=\"Select User\">";
	require_once 'SearchMoreInfoTop.php';

	print "\n <table $contentTable> ";

	Build_DspFld("User",$row[USUSER],"","A");
	Build_DspFld("Description",$row[USDESC],"","A");
	$fieldDesc=RetValue("RMROLE='$row[USROLE]'", "SYROLM", "RMDESC");
	$F_USROLE=Format_Code($row[USROLE]);
	Build_DspFld("Role",$fieldDesc,"$F_USROLE","A");
	$fieldDesc=RetValue("CMCUST=$row[USCUST]", "HDCUST", "CMCNA1");
	$F_USCUST=Format_Code($row[USCUST]);
	Build_DspFld("Customer Number",$fieldDesc,"$F_USCUST","A");
	$fieldDesc=RetValue("VMVEND=$row[USVEND]", "HDVEND", "VMVNA1");
	$F_USVEND=Format_Code($row[USVEND]);
	Build_DspFld("Vendor Number",$fieldDesc,"$F_USVEND","A");
	$fieldDesc=RetValue("SMSLSM=$row[USSLSM]", "HDSLSM", "SMSNA1");
	$F_USSLSM=Format_Code($row[USSLSM]);
	Build_DspFld("Salesman Number",$fieldDesc,"$F_USSLSM","A");
	Build_DspFld("Badge Number",$row[USCLCK],"","A");
	$fieldDesc=RetValue("FLTYPE='BYN' and FLVALU='$row[USADMN]'", "SYFLAG", "FLDESC");
	Build_DspFld("Administrator",$fieldDesc,"","A");
	$fieldDesc=RetValue("FLTYPE='BYN' and FLVALU='$row[USAASI]'", "SYFLAG", "FLDESC");
	Build_DspFld("Allow Access To Security Inquiry",$fieldDesc,"","A");
	$fieldDesc=RetValue("FLTYPE='BYN' and FLVALU='$row[USADOC]'", "SYFLAG", "FLDESC");
	Build_DspFld("Allow Access To Documentation",$fieldDesc,"","A");
	$fieldDesc=RetValue("CNCATN='$row[USCATN]'", "OECATN", "CNDESC");
	$F_USCATN=Format_Code($row[USCATN]);
	Build_DspFld("Catalog",$fieldDesc,"$F_USCATN","A");
	$fieldDesc=RetValue("PLPLNT=$row[USDPLT]", "HDPLNT", "PLNAME");
	$F_USDPLT=Format_Code($row[USDPLT]);
	Build_DspFld("Default Plant Number",$fieldDesc,"$F_USDPLT","A");
	Build_DspFld("Fax Number",$row[USFAX],"","A");
	Build_DspFld("E-Mail Address",$row[USEMAL],"","A");
	$fieldDesc=RetValue("MDMEDM='$row[USMEDM]'", "WFMEDM", "MDDESC");
	$F_USMEDM=Format_Code($row[USMEDM]);
	Build_DspFld("Medium",$fieldDesc,"$F_USMEDM","A");

	print "\n </table> ";
	require_once 'SearchMoreInfoBottom.php';
}

if ($moreInfo != "Y") {
	require_once 'PageBottom.php';
	require_once 'WildCardPrint.php';
}
print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
?>	
