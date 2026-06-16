<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$fKey1 = $_GET ['fKey1'];
$fVal1 = $_GET ['fVal1'];
$fDsc1 = RetValue ( "EIEINID={$fVal1}", "HRACAE", "coalesce(EIEIN,'')" );
$corrACA1094CID = RetValue ( "C4CACHID={$fVal1}", "HRACC4", "coalesce(C4CORCID,0)" );

$page_title = "ACA 1095C Cache Search";
$scriptName = "ACA1095CSearch.php";
$scriptVarBase = "{$genericVarBase}&amp;fKey1=" . urlencode ( trim ( $fKey1 ) ) . "&amp;fVal1=" . urlencode ( trim ( $fVal1 ) ) . "&amp;fDsc1=" . urlencode ( trim ( $fDsc1 ) );
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;
$dftOrderBy = array (array ("C5ELNAM", "A", "Last Name" ), array ("C5EFNAM", "A", "First Name" ) );

require_once 'FilterInit.php';
require_once 'FilterDefault.php';

if ($tag == "MASTERSEARCH") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Search";
	require_once ($headInclude);
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'NumEdit.php';
	require_once 'CheckEnterSearch.php';
	require_once 'NoFormValidate.php';
	print "\n </script>";
	
	$scriptType = "S"; // L=List, S=Search, I=Inquiry
	$pageID = "";
	require_once 'AdvSearchTop.php';
	
	Build_AdvSrch_Entry ( "Last Name", "srchLast", "", "operLast", "opersel_alph_short", "A", "18", "18" );
	Build_AdvSrch_Entry ( "First Name", "srchFirst", "", "operFirst", "opersel_alph_short", "A", "18", "18" );
	
	$focusField = "srchLast";
	require_once 'AdvSearchBottom.php';
}

$maxRows = $dspMaxRows;

if ($tag == "ORDERBY") {
	if ($sequence == "LastName") {
		$orby = array (array ("EMLNAMU", "A", "Last Name" ), array ("EMCOMP", "A", "Company Number" ), array ("EMFACL", "A", "" ), array ("EMEMPL", "A", "Employee Number" ) );
	} elseif ($sequence == "FirstName") {
		$orby = array (array ("EMFNAMU", "A", "First Name" ), array ("EMLNAMU", "A", "Last Name" ) );
	} elseif ($sequence == "CompanyNumber") {
		$orby = array (array ("EMCOMP", "A", "Company Number" ), array ("EMFACL", "A", "" ), array ("EMEMPL", "A", "Employee Number" ) );
	} elseif ($sequence == "EmployeeNumber") {
		$orby = array (array ("EMEMPL", "A", "Employee Number" ), array ("EMCOMP", "A", "Company Number" ), array ("EMFACL", "A", "" ) );
	} elseif ($sequence == "HRCompany") {
		$orby = array (array ("EMPECP", "A", "HR Company" ), array ("EMPEMP", "A", "HR Employee" ) );
	} elseif ($sequence == "HREmployee") {
		$orby = array (array ("EMPEMP", "A", "HR Employee" ), array ("EMPECP", "A", "HR Company" ) );
	} elseif ($sequence == "Location") {
		$orby = array (array ("EMLOC", "A", "Location" ), array ("EMLNAMU", "A", "Last Name" ), array ("EMFNAMU", "A", "First Name" ), array ("EMCOMP", "A", "Company Number" ), array ("EMFACL", "A", "" ), array ("EMEMPL", "A", "Employee Number" ) );
	} elseif ($sequence == "Department") {
		$orby = array (array ("EMDEPT", "A", "Department" ), array ("EMLNAMU", "A", "Last Name" ), array ("EMFNAMU", "A", "First Name" ), array ("EMCOMP", "A", "Company Number" ), array ("EMFACL", "A", "" ), array ("EMEMPL", "A", "Employee Number" ) );
	}
	require_once 'OrderByUpdate.php';
}

if ($tag == "QSEARCH") {
	require_once 'QuickSearch.php';
}

if ($tag == "WILDCARD") {
	$andOr = $_POST ['andOr'];
	require_once 'WildCardClear.php';
	$returnValue = Build_WildCard ( "C5ELNAM", "Last Name", $_POST ['srchLast'], "", $_POST ['operLast'], "A" );
	$returnValue = Build_WildCard ( "C5EFNAM", "First Name", $_POST ['srchFirst'], "", $_POST ['operFirst'], "A" );
	require_once 'WildCardUpdate.php';
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Search";

if ($tag == "SELECT") {
	$stmtSQL = " Select *  From HRACC4 Where C4CACHID=$fVal1 ";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	$row4 = db2_fetch_assoc ( $sqlResult );
	$c5RecID = trim($row4['C4CSUBID']) . "|";
	
	$id = $_GET ['id'];
	$stmtSQL = "Select * from new table (
	             Insert Into HRACC5
					    (C5CAID94,C5CORCID,C5RECID,C5CURID,C5EFNAM,C5EMNAM,C5ELNAM,
					     C5TINRT,C5ESSOC,C5ADDR1,C5ADDR2,C5CITY,C5STATE,C5PSTCD,C5EEAGE,C5PSMO,
					     C5CVCDA,C5CVCD01,C5CVCD02,C5CVCD03,C5CVCD04,C5CVCD05,C5CVCD06,
					     C5CVCD07,C5CVCD08,C5CVCD09,C5CVCD10,C5CVCD11,C5CVCD12,
					     C5EMSHA,C5EMSH01,C5EMSH02,C5EMSH03,C5EMSH04,C5EMSH05,C5EMSH06,
					     C5EMSH07,C5EMSH08,C5EMSH09,C5EMSH10,C5EMSH11,C5EMSH12,
					     C5SHBRA,C5SHBR01,C5SHBR02,C5SHBR03,C5SHBR04,C5SHBR05,C5SHBR06,
					     C5SHBR07,C5SHBR08,C5SHBR09,C5SHBR10,C5SHBR11,C5SHBR12,
					     C5ZIPA,C5ZIP01,C5ZIP02,C5ZIP03,C5ZIP04,C5ZIP05,C5ZIP06,
					     C5ZIP07,C5ZIP08,C5ZIP09,C5ZIP10,C5ZIP11,C5ZIP12,C5CVIND) 
			     Select  
			             {$fVal1},C5CACHID,C5RECID,'{$c5RecID}' || C5RECID,C5EFNAM,C5EMNAM,C5ELNAM,
                         C5TINRT,C5ESSOC,C5ADDR1,C5ADDR2,C5CITY,C5STATE,C5PSTCD,C5EEAGE,C5PSMO,
                         C5CVCDA,C5CVCD01,C5CVCD02,C5CVCD03,C5CVCD04,C5CVCD05,C5CVCD06,
                         C5CVCD07,C5CVCD08,C5CVCD09,C5CVCD10,C5CVCD11,C5CVCD12,
                         C5EMSHA,C5EMSH01,C5EMSH02,C5EMSH03,C5EMSH04,C5EMSH05,C5EMSH06,
                         C5EMSH07,C5EMSH08,C5EMSH09,C5EMSH10,C5EMSH11,C5EMSH12,
                         C5SHBRA,C5SHBR01,C5SHBR02,C5SHBR03,C5SHBR04,C5SHBR05,C5SHBR06,
                         C5SHBR07,C5SHBR08,C5SHBR09,C5SHBR10,C5SHBR11,C5SHBR12,
					     C5ZIPA,C5ZIP01,C5ZIP02,C5ZIP03,C5ZIP04,C5ZIP05,C5ZIP06,
					     C5ZIP07,C5ZIP08,C5ZIP09,C5ZIP10,C5ZIP11,C5ZIP12,C5CVIND
			     From HRACC5 a Where a.C5CACHID={$id})";
	
	$sqlResult5 = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	
	// If row not added, set identity column and try again
	if (! $sqlResult5) {
		$sqlResult5 = Check_Identity ('HRACC5','C5CACHID',$stmtSQL);
	}
	$row5 = db2_fetch_assoc ( $sqlResult5 );
	
	$stmtSQL = " Insert Into HRACC2
					    (C2CAID5,C2DFNAM,C2DMNAM,C2DLNAM,C2DSUFF,C2TINRT,C2DSSOC,C2DDOB,C2CVCDA,
					     C2CVCD01,C2CVCD02,C2CVCD03,C2CVCD04,C2CVCD05,C2CVCD06,C2CVCD07,
					     C2CVCD08,C2CVCD09,C2CVCD10,C2CVCD11,C2CVCD12) 
                 Select {$row5 ['C5CACHID']},C2DFNAM,C2DMNAM,C2DLNAM,C2DSUFF,C2TINRT,C2DSSOC,C2DDOB,C2CVCDA,
					     C2CVCD01,C2CVCD02,C2CVCD03,C2CVCD04,C2CVCD05,C2CVCD06,C2CVCD07,
					     C2CVCD08,C2CVCD09,C2CVCD10,C2CVCD11,C2CVCD12
			     From HRACC2 a Where a.C2CAID5={$id}";
	
	$sqlResult2 = db2_exec ( $i5Connect->getConnection (), $stmtSQL );

	// If row not added, set identity column and try again
	if (! $sqlResult2) {
		$sqlResult2 = Check_Identity ('HRACC2','C2DCID5',$stmtSQL);
	}
		
	print "\n \n <script TYPE=\"text/javascript\">";
	print "\n opener.location.href=opener.location.href; ";
	print "\n window.close(); ";
	print "\n </script> \n";
}

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n function confirmAll(text) {return confirm(text);} \n";
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
print "\n <table $contentTable> ";
print "\n     <colgroup> <col width=\"80%\"><col width=\"15%\"> ";
print "\n     <tr><td><h1>$page_title</h1></td> ";
print "\n         <td class=\"toolbar\"> ";
require_once 'HelpPage.php';
print "\n &nbsp;<a href=\"javascript:window.close()\">$closeImageMed</a> ";
print "\n </td></tr></table> ";
print $searchhrTagAttr;

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " HRACC5 a";
$selectSQL = "C5CAID94={$corrACA1094CID} and 
              not exists (Select * from HRACC5 b Where b.C5CAID94={$fVal1} and a.C5EFNAM=b.C5EFNAM and a.C5ELNAM=b.C5ELNAM and a.C5ESSOC=b.C5ESSOC) ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By $orderBy ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );

$qsOpt = "\n <option value=\"C5ELNAM|null|Last Name|A|\" title=\"Last Name\" SELECTED>Last Name";
$qsOpt .= "\n <option value=\"C5EFNAM|null|First Name|A|\" title=\"First Name\">First Name";
require 'QuickSearchOption.php';

print "<table $contentTable> <tr>";
$returnValue = OrderBy_Sort ( "C5ELNAM" );
$sortVar = $returnValue ['sortedBy'];
$sortPoint = $returnValue ['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LastName\" title=\"Sequence By Last Name, First Name\">{$sortPoint}Last Name</a></th>";
$returnValue = OrderBy_Sort ( "C5EFNAM" );
$sortVar = $returnValue ['sortedBy'];
$sortPoint = $returnValue ['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=FirstName\" title=\"Sequence By First Name, Last Name\">{$sortPoint}First Name</a></th>";
$returnValue = OrderBy_Sort ( "C5ADDR1" );
$sortVar = $returnValue ['sortedBy'];
$sortPoint = $returnValue ['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=FirstName\" title=\"Sequence By First Name, Last Name\">{$sortPoint}Address</a></th>";
$returnValue = OrderBy_Sort ( "C5CITY" );
$sortVar = $returnValue ['sortedBy'];
$sortPoint = $returnValue ['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=FirstName\" title=\"Sequence By First Name, Last Name\">{$sortPoint}City</a></th>";
$returnValue = OrderBy_Sort ( "C5STATE" );
$sortVar = $returnValue ['sortedBy'];
$sortPoint = $returnValue ['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=FirstName\" title=\"Sequence By First Name, Last Name\">{$sortPoint}State</a></th>";
$returnValue = OrderBy_Sort ( "C5PSTCD" );
$sortVar = $returnValue ['sortedBy'];
$sortPoint = $returnValue ['sortPoint'];
print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=FirstName\" title=\"Sequence By First Name, Last Name\">{$sortPoint}Zip</a></th>";
print "\n </tr>";

$rowCount = 0;
while ( $row = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
	if ($rowCount >= $dspMaxRows) {
		break;
	}
	require 'SetRowClass.php';
	print "\n <tr class=\"$rowClass\">";
	print "\n     <td class=\"colalph\"><a href=\"{$baseURL}&amp;tag=SELECT&amp;id={$row[C5CACHID]}\" title=\"Select Employee\">$row[C5ELNAM]</a></td> ";
	print "\n     <td class=\"colalph\">$row[C5EFNAM]</td>";
	print "\n     <td class=\"colalph\">$row[C5ADDR1]</td>";
	print "\n     <td class=\"colalph\">$row[C5CITY]</td>";
	print "\n     <td class=\"colalph\">$row[C5STATE]</td>";
	print "\n     <td class=\"colalph\">$row[C5PSTCD]</td>";
	print "\n </tr>";
	$startRow ++;
	$rowCount ++;
}
if ($rowCount == 0) {
	require 'NoRecordsFound.php';
}
print "</table>";

require_once 'PageBottom.php';
require_once 'WildCardPrint.php';
print "$searchhrTagAttr";
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";


function Check_Identity ($table,$column,$stmtSQL) {
	global $pgmLibrary, $i5Connect;
	if (!$i5Connect) die("<br>Check Identity Column Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

	$maxSQL = "Select max({$column}) + 1 as MAXID From {$table}";
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $maxSQL );
	$row = db2_fetch_assoc ( $sqlResult );
	if (array_key_exists ( 'MAXID', $row )) {
		$maxSQL = "ALTER TABLE {$table} ALTER COLUMN {$column} RESTART WITH {$row['MAXID']}";
		$status = db2_exec ( $i5Connect->getConnection (), $maxSQL );
		if ($status) {
			$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		}
	}
	return $status;
}
?>	
