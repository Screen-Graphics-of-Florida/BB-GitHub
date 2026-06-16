<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound        = $_GET['errFound'];
$tableName       = (isset($_GET['tableName'])) ? $_GET['tableName'] : "";
$tableDesc       = (isset($_GET['tableDesc'])) ? $_GET['tableDesc'] : "";

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';
require_once 'XMLValidateInclude.php';

$page_title     = "Table Import XML";
$scriptName     = "TableImportXML.php";
$scriptVarBase  = "{$genericVarBase}&amp;tblID=" . urlencode($tblID) . "&amp;tableName=" . urlencode(trim($tableName)) . "&amp;tableDesc=" . urlencode(trim($tableDesc));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$programName    = "HSYXXX";
$columnError    = "";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}Table.php{$scriptVarBase}&amp;tag=REPORT";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}
if ($tag == "Edit_Data") {
	try {
		$xml = $_POST['xml'];
		$xml = str_replace('<br>', '&lt;br&gt;', $xml);
		$xml = str_replace('&amp;', '&', $xml);
		$xml = str_replace('&', '&amp;', $xml);
		$xmlTableDoc = new SimpleXMLElement($xml);
	} catch (Exception $e) {
		$tag = "MAINTAIN";
		$errFound='Y';
	}
	if ($errFound!="Y") {require_once 'TableImportXMLValidate.php';}
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	require_once 'Menu.js';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.xml.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n return true;";
	print "\n }";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "TABLEXML";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	if ($errFound == "Y" || $columnError!="") {
		$clob = $_POST['xml'];
	} else {
		$stmtSQL = "Select DSXML From SYDCST Where DSTBID=$tblID";
		$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
		if (is_resource ( $sqlResult )) {
			$row = db2_fetch_array ( $sqlResult );
			$clob=xml_format($row[0]);
		}
	}

	// Program Option Security
	$sec_01="N";
	$sec_02="Y";
	$sec_03="N";
	$sec_04="N";
	print "\n <a NAME=\"top\"></a> ";
	require_once 'MaintainTop.php';
	print "<table $contentTable>";
	Format_Header_URL("Table", $tableDesc, $tableName, "");
	print "\n </table>";

	print $hrTagAttr;

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	if ($columnError != "") {print "\n $columnError";}
	print "<table $contentTable>";
	if ($errFound == "Y") {print "\n <tr><td class=\"error\">This is not a well formed XML document</td></tr> ";}
	print "\n <tr> ";
	print "\n <td class=\"inputalph\"><textarea name=\"xml\" ROWS=25 COLS=90 WRAP=OFF>" . trim($clob) . "</textarea></td>";
	print "\n </tr> ";
	print "\n </table>";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.xml.focus();";
	print "\n </script>";
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
	$stmtSQL = "Delete From SYDCST Where DSTBID=$tblID";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	// Page Header
	$xmlStr = $xmlTableDoc->asXML();
	//$xmlStr = str_replace('\'', '"', $xmlStr);

	$stmtSQL = "Insert into SYDCST (DSTBID, DSTBLN, DSTBLD, DSROLE, DSUSER, DSRSVD, DSCRTB, DSXML)
                            VALUES (?,?,?,?,?,?,?,?)";
	
	$sqlResult = db2_prepare($i5Connect->getConnection (), $stmtSQL);
	if ($sqlResult) {
		$DSUSER = "HDS";
		$DSROLE = "";
		$DSRSVD = "";
		$DSCRTB = "Sys_Gen";
		$var = array($tblID, $tableName, $tableDesc, $DSROLE, $DSUSER, $DSRSVD, $DSCRTB, $xmlStr);
		$ret = db2_execute($sqlResult, $var);
	}
	
	$confMessage=Format_ConfMsg_Desc($maintenanceCode, $tableDesc, $tableName, "", "", "", "");
	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
}

function xml_format($xml, $html_output=false) {
	$xml_obj = new SimpleXMLElement($xml);
	$level = 4;
	$indent = 0; // current indentation level
	$pretty = array();

	// get an array containing each XML element
	$xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

	// shift off opening XML tag if present
	if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
		$pretty[] = array_shift($xml);
	}

	foreach ($xml as $el) {
		if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
			// opening tag, increase indent
			$pretty[] = str_repeat(' ', $indent) . $el;
			$indent += $level;
		} else {
			if (preg_match('/^<\/.+>$/', $el)) {
				$indent -= $level;  // closing tag, decrease indent
			}
			if ($indent < 0) {
				$indent += $level;
			}
			$pretty[] = str_repeat(' ', $indent) . $el;
		}
	}
	$xml = implode("\n", $pretty);
	return ($html_output) ? htmlentities($xml) : $xml;
}

?>