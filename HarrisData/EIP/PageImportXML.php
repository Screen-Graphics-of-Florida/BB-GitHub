<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$pagID        = (isset($_GET['pagID']))     ? $_GET['pagID']     : 0;
$pageDesc     = (isset($_GET['pageDesc']))  ? $_GET['pageDesc']  : "";
$role         = (isset($_GET['role']))      ? $_GET['role']      : "";
$user         = (isset($_GET['user']))      ? $_GET['user']      : "";
$tableName    = (isset($_GET['tableName'])) ? $_GET['tableName'] : "";
$tableDesc    = (isset($_GET['tableDesc'])) ? $_GET['tableDesc'] : "";

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Page Import XML";
$scriptName     = "PageImportXML.php";
$scriptVarBase  = "{$genericVarBase}&amp;tblID=" . urlencode($tblID) . "&amp;tableName=" . urlencode(trim($tableName)) . "&amp;tableDesc=" . urlencode(trim($tableDesc)) . "&amp;pagID=" . urlencode($pagID) . "&amp;role=" . urlencode(trim($role)) . "&amp;user=" . urlencode(trim($user)) . "&amp;pageDesc=" . urlencode(trim($pageDesc));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$programName    = "HSYXXX";
$backURL="{$homeURL}{$phpPath}Page.php{$scriptVarBase}&amp;tag=REPORT";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}
if ($tag == "Edit_Data") {
	try {
		$xml = $_POST['xml'];
		$xml = str_replace('<br>', '&lt;br&gt;', $xml);
		$xmlTableDoc = new SimpleXMLElement($xml);
	} catch (Exception $e) {
		$tag = "MAINTAIN";
		$errFound='Y';
	}
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
	print "\n   if (document.Chg.xml.value ==\"\")";
	print "\n   {alert(\"$reqFieldError\"); return false;} ";
	print "\n   return true;";
	print "\n }";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "PAGEMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	if ($errFound == "Y") {
		$clob = $_POST['xml'];
	} else {
		$stmtSQL="Select * From SYDSGN Where PDTBID=$tblID and PDPGID=$pagID and PDTYPE='L'";
		$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
		if (is_resource($sqlResult)){
			$row = db2_fetch_array ( $sqlResult );
			$clob=xml_format($row[9]);
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
	Format_Header_URL("Page", $pageDesc, "", "");
	print "\n </table>";

	print $hrTagAttr;
	$sqlResult = db2_fetch_array($i5Connect, $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "<table $contentTable>";
	if ($errFound == "Y") {print "\n <tr><td class=\"error\">This is not a well formed XML document</td></tr> ";}
	print "\n <tr> ";
	print "\n <td class=\"inputalph\"><textarea name=\"xml\" ROWS=25 COLS=90 WRAP=\"hard\">$clob</textarea></td>";
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
	$stmtSQL = "Delete From SYDSGN Where PDTBID=$tblID and PDPGID=$pagID";
	$status = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
	// Page Header
	$xmlStr = $xmlTableDoc->asXML();
	$xmlStr = str_replace('\'', '"', $xmlStr);
	$stmtSQL = "Insert into SYDSGN (PDTBID, PDPGID, PDTYPE, PDDESC, PDROLE, PDUSER, PDCRTB, PDXML)
                            VALUES (?,?,?,?,?,?,?,?)";
	$sqlResult = db2_prepare($i5Connect->getConnection (), $stmtSQL);
	if ($sqlResult) {
		$var = array($tblID, $pagID, "L", $pageDesc, $role, $user, $userProfile, $xmlStr);
		$ret = db2_execute($sqlResult, $var);
	}
	$confMessage=Format_ConfMsg_Desc($maintenanceCode, "Page", $pageDesc, "", "", "", "");
	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}Page.php{$scriptVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
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