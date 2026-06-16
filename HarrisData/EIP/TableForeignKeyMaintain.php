<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$errFound        = (isset($_GET['errFound'])) ? $_GET['errFound']    : "";

$fromTblID    = (isset($_GET['fromTblID']))     ? $_GET['fromTblID']     : 0;
$tableName    = (isset($_GET['tableName']))     ? $_GET['tableName']     : "";
$tableDesc    = (isset($_GET['tableDesc']))     ? $_GET['tableDesc']     : "";

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Table Foreign Key Maintenance";
$scriptName     = "TableForeignKeyMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromTblID=" . urlencode($fromTblID) . "&amp;tableName=" . urlencode($tableName) . "&amp;tableDesc=" . urlencode($tableDesc);
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$programName    = "HSYTBN_E";

$customTable = '';
if ($fromTblID >= 5000) {
  $customTable = 'Custom';
}

$backURL="{$homeURL}{$cGIPath}{$customTable}Table.d2w/REPORT{$altVarBase}";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Chg";
	require_once ($headInclude);

	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	require_once 'Menu.js';
	require_once 'NoFormValidate.php';
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "FOREIGNKEYMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	$stmtSQL .= " Select a.TCRFTB,TNDESC,TKCOLN,b.TCCTXT,coalesce(TFFKCO,' ') as TFFKCO,coalesce(TFFKVL,' ') as TFFKVL,coalesce(c.TCCTXT,' ') as TFFKCT,TKKYSQ,a.TCCOLN,TNTBID ";
	$stmtSQL .= " From SYTBLC a join SYTBLN on TNTBLN=a.TCRFTB ";
	$stmtSQL .= " join SYTBLK on TKTBID=TNTBID ";
	$stmtSQL .= " join SYTBLC b on b.TCTBLN=a.TCRFTB and b.TCCOLN=TKCOLN ";
	$stmtSQL .= " left outer join ";
	$stmtSQL .= " SYTBLF left join SYTBLC c on c.TCTBID=$fromTblID and c.TCCOLN=TFFKCO ";
	$stmtSQL .= " on TFTBID=$fromTblID and TFCOLN=a.TCCOLN and TFRTID=TNTBID and TFKYSQ=TKKYSQ ";
	$stmtSQL .= " Where a.TCTBID=$fromTblID and a.TCRFTB<>' ' order by a.TCDPOS,TKKYSQ ";
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hsyxxx_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
	$sec_01=$hsyxxx_OPT['sec_01'];
	$sec_02=$hsyxxx_OPT['sec_02'];
	$sec_03=$hsyxxx_OPT['sec_03'];
	$sec_04=$hsyxxx_OPT['sec_04'];
	require_once 'MaintainTop.php';

	print "<table $contentTable>";
	Format_Header_URL("Table", $tableDesc, $tableName, "{$homeURL}{$cGIPath}{$customTable}Table.d2w/REPORT{$altVarBase}&intHD=Y&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']));
	print "\n </table>";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\"  onSubmit=\"return validate(document.Chg)\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
	print "\n <table $contentTable>";
	print "\n <tr><th class=\"colhdr\">Reference Table</th>";
	print "\n     <th class=\"colhdr\">Description</th>";
	print "\n     <th class=\"colhdr\">Column</th>";
	print "\n     <th class=\"colhdr\">Description</th>";
	print "\n     <th class=\"colhdr\">Foreign Key Column</th>";
	print "\n     <th class=\"colhdr\">Foreign Key Value</th>";
	print "\n </tr>";

	$focusField = "";
	$saveColNm = "";
	$newColumn = false;

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){

		$columnName = trim($row['TCCOLN']);
		if ($columnName != $saveColNm) {
			require  'SetRowClass.php';
			$saveColNm = $columnName;
			$newColumn = true;
		}

		print "\n <tr class=\"$rowClass\" valign=\"top\">";

		if ($newColumn) {
			if (trim($row['TFFKCO']) == "" && trim($row['TFFKVL']) == "") {$row['TFFKCO'] =$saveColNm;}
			print "\n     <td class=\"colalph\">" . trim($row['TCRFTB']) . "</td>";
			print "\n     <td class=\"colalph\" $helpCursor><span title=\"" . trim($row['TCRFTB']) . "\">" . trim($row['TNDESC']) . "</span></td>";
		} else {
			print "\n     <td>&nbsp;</td>";
			print "\n     <td>&nbsp;</td>";
		}

		print "\n     <td class=\"colalph\">" . trim($row['TKCOLN']) . "</td>";
		print "\n     <td class=\"colalph\">" . trim($row['TCCTXT']) . "</td>";

		$recNbr = str_pad($startRow, 4, "0", STR_PAD_LEFT);
		//	$textOvr=SetTextOvr($Err_TFFKCO);
		$fieldDesc = trim($row['TFFKCT']);
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fk{$recNbr}\" value=\"" . trim($row['TFFKCO']) . "\" size=\"10\" maxlength=\"130\"><a href=\"{$homeURL}{$cGIPath}TableColumnSearch.d2w/REPORT{$altVarBase}&amp;docName=Chg&amp;fldName=fk{$recNbr}&amp;fldDesc=fk{$recNbr}Desc&amp;tableName=" . urlencode($tableName) . "&amp;tableDesc=" . urlencode($tableDesc) . "\" onclick=\"$searchWinVar\">$searchImage</a><span class=\"dspdesc\" id=\"fk{$recNbr}Desc\">$fieldDesc &nbsp;</span></td>";
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"fv{$recNbr}\" value=\"" . trim($row['TFFKVL']) . "\" size=\"10\" maxlength=\"130\"></td>";
		//	DspErrMsg($Err_TFFKCO);
		if ($focusField == "") {
			$focusField = "fk{$recNbr}";
		}

		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"co{$recNbr}\" value=\"{$columnName}\"></td>";
		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"rn{$recNbr}\" value=\"" . trim($row['TCRFTB']) . "\"></td>";
		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"ri{$recNbr}\" value=\"" . trim($row['TNTBID']) . "\"></td>";
		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"ks{$recNbr}\" value=\"" . trim($row['TKKYSQ']) . "\"></td>";
		print "\n     <td class=\"inputalph\"><input type=\"hidden\" name=\"kc{$recNbr}\" value=\"" . trim($row['TKCOLN']) . "\"></td>";
		print "\n </tr>";

		$newColumn = false;
		$startRow ++;
	}

	print "\n </table> ";

	if ($focusField != "") {
		print "\n <script TYPE=\"text/javascript\">";
		print "\n document.Chg.$focusField.focus();";
		print "\n </script>";
	}
	print "\n </form>";
	require_once 'MaintainBottom.php';
	print $hrTagAttr;
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {

	require 'stmtSQLClear.php';
	$stmtSQL .= " Delete From SYTBLF Where TFTBID=$fromTblID ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	$recCount = RetValue("TCTBID=$fromTblID and TCRFTB<>' '", "SYTBLC join SYTBLN on TNTBLN=TCRFTB join SYTBLK on TKTBID=TNTBID", "count(*)");
	for ($i=1; $i<=$recCount; $i++) {
		$recNbr = str_pad($i, 4, "0", STR_PAD_LEFT);
		if ($_POST['fk'.$recNbr] != "" || $_POST['fv'.$recNbr] != "") {
			require 'stmtSQLClear.php';
			$stmtSQL .= " Insert Into SYTBLF ";
			$stmtSQL .= " Values ('$tableName',$fromTblID,'{$_POST['co'.$recNbr]}','{$_POST['rn'.$recNbr]}',{$_POST['ri'.$recNbr]},{$_POST['ks'.$recNbr]},'{$_POST['kc'.$recNbr]}','" . strtoupper($_POST['fk'.$recNbr]) . "','" . strtoupper($_POST['fv'.$recNbr]) . "',Current_Timestamp,'$userProfile','Y') ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		}
	}

	$confMessage=Format_ConfMsg_Desc("C", $tableDesc, $tableName, "", "", "", "");
	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}{$customTable}Table.d2w/REPORT{$altVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\">";
}
?>