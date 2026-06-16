<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$plantNumber     = $_GET['plantNumber'];
$plantName       = $_GET['plantName'];

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Warehouse Supply Maintenance";
$scriptName     = "WarehouseSupplyMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;plantNumber=" . urlencode(trim($plantNumber)) . "&amp;plantName=" . urlencode(trim($plantName));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "";
$pageID         = "WHSSUPPLYMAINTAIN";
$maintenanceCode= "C";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=14";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);

	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';
	require_once 'CheckEnterChg.php';
	require_once 'NoFormValidate.php';
	require_once 'DragDrop.js';
	print "\n
	   function validate(chgForm) {
         var item = [];
         var x = 1000;
         for (var i = 0; i < selected.childNodes.length; i++) {
		   if (selected.childNodes[i].nodeName == 'LI') {
		     itm = selected.childNodes.item(i)
             whs = itm.getAttribute('id').substring(1,4)
             item += '@@' + x + whs + '}{'
             x++;
           } 
         }	
         document.Chg.data.value = item;
         return true; 
	   }";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";

	require_once 'MaintainTop.php';
	print "\n <table $contentTable>";
	Format_Header("Plant", $plantName, $plantNumber);
	print "\n </table> ";
	print $hrTagAttr;
	require_once 'ErrorDisplay.php';
	print "\n <div class=\"copr\" style=\"border: 1px black solid; margin: 5px;\">To move a warehouse between Available and Selected, click on the warehouse to move and while holding the button down,<br>
                                  move the mouse to where you wish to place it and release the button. The order of the Selected records defines the priority.</div>";
	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
	print "\n <table $contentTable>";
	print "\n <tr>";

	print "\n <td valign=\"top\">&nbsp; &nbsp; <span style=\"font-weight:bold;\">Available:</span> ";
	require 'stmtSQLClear.php';
	$stmtSQL =  " Select WHWHS,WHWHNM  ";
	$fileSQL =  " HDWHSM left exception join HDWSPM on WSTYPE='P' and WSWH=WHWHS ";
	$selectSQL =  " WHWHS>0";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By upper(WHWHNM)";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	print "\n <ul id=\"available\" class=\"sortable boxy\" style=\"margin-left: 1em;\">Whs &nbsp; Description";
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		while (strlen($row[WHWHS])<3) {$row[WHWHS] =  "0{$row[WHWHS]}" ;}
		print "\n <li id=\"W$row[WHWHS]\">$row[WHWHS] &nbsp; $row[WHWHNM]</li>";
		$startRow++;
	}
	print "\n </ul></td> ";

	print "\n <td valign=\"top\">&nbsp; &nbsp; <span style=\"font-weight:bold;\">Selected:</span>";
	require 'stmtSQLClear.php';
	$stmtSQL .=  " Select HDWSPM.*,WHWHNM  ";
	$fileSQL .=  " HDWSPM inner join HDWHSM on WSWH=WHWHS";
	$selectSQL =  " WSTYPE='P' and WSPLWH=$plantNumber and WSSORD='S'";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By WSSEQ#";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
	print "\n <ul id=\"selected\" class=\"sortable boxy\" style=\"margin-left: 1em;\">Whs &nbsp; Description";
	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		while (strlen($row[WSWH])<3) {$row[WSWH] =  "0{$row[WSWH]}" ;}
		print "\n <li id=\"W$row[WSWH]\">$row[WSWH] &nbsp; $row[WHWHNM]</li>";
		$startRow++;
	}

	print "\n </ul></td> ";
	print "\n <td><input type=\"hidden\" name=\"data\"></td> ";
	print "\n </tr> ";
	print "\n </table> ";

	print "\n </form><br>";
	require_once 'MaintainBottom.php';
	print $hrTagAttr;
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	require_once 'DragDropLoad.js';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Delete From HDWSPM Where WSTYPE='P' and WSPLWH=$plantNumber and WSSORD='S' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	if ($maintenanceCode!="D") {
		require 'stmtSQLClear.php';
		$stmtSQL .= " Insert Into HDWSPM values ";
		$s = 1;
		$i = 1000;
		$edtVar = $_POST['data'];
		while($i<=2000) {
			$fld = "@@" . $i;
			$whsNbr=Decat_Field($fld, $edtVar);
			if ($whsNbr == "") {break;}
			if ($s>1) {$stmtSQL .= ",";}
			$stmtSQL .= " ('P',$plantNumber,'S',$s,$whsNbr) ";
			$s++;
			$i++;
		}
		$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	}
	$confMessage=Format_ConfMsg_Desc($maintenanceCode, "$plantName", "$plantNumber" , "", "", "", "");
	print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
}

?>