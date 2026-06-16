<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$table        = (isset($_GET['table']))        ? $_GET['table']       : "";
$column       = (isset($_GET['column']))       ? $_GET['column']      : "";
$colDesc      = (isset($_GET['colDesc']))      ? $_GET['colDesc']     : "";

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title      = "User-Defined Document Comment";
$scriptName      = "UserDefinedDocCmt.php";
$scriptVarBase   = "{$genericVarBase}&amp;table=" . urlencode($table) . "&amp;column=" . urlencode($column);
$baseURL         = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$nextPrevVar     = "{$scriptVarBase}";
$dspMaxRows      = 9999;
$prtMaxRows      = $prtMaxRowsDft;
$popUpWin        = "Y";

if ($tag == "Edit_Data") {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Delete From SYUDFC Where DCFILN='$table' and DCFLDN='$column'";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	
	$values = null;
	foreach ($_POST as $key => $value) {
		if (trim($value) != "") {
			print 'here';
			if (isset($values)) {$values.=", ";}
			$values.="('" . $table . "', '" . $column . "', '" . $key . "', '" . $value . "')";
		}
	}

	require 'stmtSQLClear.php';
	$stmtSQL .= " Insert Into SYUDFC (DCFILN,DCFLDN,DCDOCT,DCCMNT) Values $values ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	print "\n <script TYPE=\"text/javascript\">";
	print "\n opener.location.href=opener.location.href";
	print "\n opener.focus();";
	print "\n window.close();";
	print "\n </script>";
	exit();
}

require_once ($docType);
print "\n <html> 	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

require 'stmtSQLClear.php';
$stmtSQL .=  " Select DODOCT,DODESC,coalesce(DCCMNT,' ') as DCCMNT ";
$fileSQL .=  " HDDOCT left join SYUDFC on DCFILN='$table' and DCFLDN='$column' and DCDOCT=DODOCT ";
$selectSQL =  " DOAPID='OE' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By DODOCT";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n     <td class=\"toolbar\">";
print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
print "\n <a href=\"javascript:window.close()\">$cancelImageMed</a>";

$medIcon= "Y";
require 'HelpPage.php';
print "\n </td></tr></table>";

print "\n <table $contentTable> ";
Format_Header("Table", "Customer Order", $table);
Format_Header("Column", $colDesc, $column);
print "\n </table> ";

print $hrTagAttr;

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
print "<table $contentTable><tr>";
print "\n <tr><th class=\"colhdr\">Document</th>";
print "\n     <th class=\"colhdr\">Description</th>";
print "\n     <th class=\"colhdr\">Comment</th></tr>";

$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\" valign=\"top\">";
	print "\n <td class=\"colalph\">{$row['DODOCT']}</td>";
	print "\n <td class=\"colalph\">{$row['DODESC']}</td>";
	$cmt = trim($row[DCCMNT]);
	print "\n <td class=\"inputalph\"><input type=\"text\" name=\"$row[DODOCT]\" value=\"$cmt\" size=\"80\" maxlength=\"60\"> ";
	print "\n </tr>";

	$startRow ++;
	$rowCount ++;
}

print "\n </form>";

print "\n <table $contentTable><tr><td class=\"toolbar\">";
print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
print "\n <a href=\"javascript:window.close()\">$cancelImageMed</a>";
$medIcon= "Y";
require "HelpPage.php";
print "\n </td></tr></table>";

print "$hrTagAttr";
require_once 'Copyright.php';
print "</td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";

?>