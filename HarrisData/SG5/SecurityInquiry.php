<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'hdListInclude.php';

/* Script Variables	*/
$page_title    = "Security Inquiry";
$scriptName    = "SecurityInqury.php";
$scriptVarBase = "{$genericVarBase}&amp;tblID=" . urlencode($tblID) . "&amp;pagID=" . urlencode($pagID) . "&amp;fromScript=" . urlencode($fromScript);
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$popUpWin      = "Y";
$browser = getenv("HTTP_USER_AGENT");
$iePos = strpos($browser, "MSIE");
$medIcon = "Y";

$link_count = count($hdDocsetLink->xpath("linkid[type='D']"));
if ($link_count === 0) {
    $link_count = count($hdDocsetLink->xpath("linkid[type='B']"));
}
if ($link_count === 0) {
    $link_count = count($hdDocsetLink->xpath("linkid[type='C']"));
}
$uv_count   = count($hdDocsetRow->xpath("col[user_view_col]"));

require_once ($docType);
print "\n <html> 	<head>";
require_once ($headInclude);

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>$pageHeading1 $page_title</h1></td>";
print "\n <td class=\"toolbar\">";
if ($iePos === false) {print "\n <a href=\"javascript:self.close()\">$closeImageMed</a>";}
else                  {print "\n <a href=\"javascript:window.close()\">$closeImageMed</a>";}
print "</td>";
print "\n </tr>";
print "\n </table>";
print "<table $contentTable>";
Format_Header("User", $profileName, $userProfile);
print "\n </table>";
print $hrTagAttr;

if ($link_count>0) {
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Links</legend> ";
	print "\n <table $contentTable>";
	print "<tr>";
	print "\n <th class=\"colhdr\">Program</th>";
	print "\n <th class=\"colhdr\">Description</th>";
	print "\n <th class=\"colhdr\">Opt</th>";
	print "\n <th class=\"colhdr\">Icon</th>";
	print "\n <th class=\"colhdr\">Title</th>";
	print "\n <th class=\"colhdr\">Auth</th>";
	print "\n <th class=\"colhdr\">Sel</th>";
	print "\n </tr>";

	foreach ($hdDocsetLink->linkid as $col) {
		$linkType = (string) $col->type;
		$optPGOV = (string) $col[0]->pgm_opt_program_override;
		$optPOPT = (string) $col[0]->pgm_opt_sequence;
		if ($linkType == "C" && $optPOPT == 0) {continue;}
		$colID   = (string) $col['id'];
		$colCond = (string) trim($col[0]->condition_criteria);
		if ($colCond) {
			$colCond = urldecode($colCond);
			while (strpos($colCond, "@@parm") !== false) {
				$colCond = str_replace("\"", "'", $colCond);
				$parmName = Decat_Parm($colCond);
				if ($parmName == "") {break;}
				$colCond = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colCond);
			}
			eval("\$testCond = " . trim($colCond).";");
			if ((int)$testCond==false) {continue;}
		}
		if ($linkType == "C") {
			$colName = (string) $col[0]->column_name;
			$row  = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
			$linkImage   = (string) trim($row[0]->colheading);
		} else {
			$linkImage   = (string) trim($col[0]->link_image);
			if (!$linkImage) {$linkImage   = (string) trim($col[0]->image);}
			$linkImage  = "{$$linkImage}";
		}
		$colHeading   = "";
		$colDesc = (string) urldecode($col->link_title);
		$linkAuth = "";
		if ($optPOPT>0) {$savPOPT = $optPOPT;} else {$savPOPT="";}
		$optPOPT = str_pad($optPOPT, 2, "0", STR_PAD_LEFT);
		if ($optPGOV) {$ovp_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $optPGOV);}
		if ($optPOPT == "00" || (!$optPGOV && $hdList_OPT["sec_$optPOPT"] == "Y") || ($optPGOV && $ovp_OPT["sec_$optPOPT"] == "Y")) {
			$linkAuth = $checkImage;
		}
		if ($optPGOV == "" && $optPOPT>0) $optPGOV=$programName;
		if ($optPOPT==00) $optPOPT="";
		$linkSel = $checkImage;
		if ($hdListLink) {
			$colID = (string) $col[0]['id'];
			$linkID  = $hdListLink->xpath("linkid[@id='" . $colID . "']");
			if (!$linkID[0]) {$linkSel = "";}
		}
		$progDesc = RetValue("PTPGID='$optPGOV'", "SYILET", "PTDESC");
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\"> ";
		print "\n     <td class=\"colalph\">$optPGOV</td>";
		print "\n     <td class=\"colalph\">$progDesc</td>";
		print "\n     <td class=\"colcode\">$savPOPT</td>";
		print "\n     <td class=\"colcode\"><span $textOvr>$linkImage</span></td> ";
		print "\n     <td class=\"colalph\">$colDesc</td>";
		if ($linkAuth) {print "\n     <td class=\"colcode\">$linkAuth</td>";}
		else           {print "\n     <td class=\"colcode\">$notselectedImage</td>";}
		if ($linkSel)  {print "\n     <td class=\"colcode\">$linkSel</td>";}
		else           {print "\n     <td class=\"colcode\">$notselectedImage</td>";}
		print "\n </tr> ";
	}
	print "\n </table> ";
	print "\n </fieldset> ";
}

// User View
if ($uv_count>0) {
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">User View Columns</legend> ";
	print "\n <table $contentTable>";
	print "<tr>";
	print "\n <th class=\"colhdr\">Description</th>";
	print "\n <th class=\"colhdr\">Column</th>";
	print "\n </tr>";

	foreach ($hdDocsetRow->col as $col) {
		$colUvsn = trim($col->user_view_col);
		if ($colUvsn) {
		$colCond = (string) trim($col[0]->condition_criteria);
		$colCond = urldecode($colCond);
		if ($colCond) {
			while (strpos($colCond, "@@parm") !== false) {
				$colCond = str_replace("\"", "'", $colCond);
				$parmName = Decat_Parm($colCond);
				if ($parmName == "") {break;}
				$colCond = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colCond);
			}
			eval("\$testCond = " . trim($colCond).";");
		}

		if (!$colCond || (int)$testCond==true) {
		$colName  = trim(strtoupper($col['id']));
			$uvName   = RetValue("UFFLDN='{$colUvsn}'", "SYUFLD", "UFSCRN");
			$uvName   = str_replace("#", "_", $uvName);
			$uvVarNm  = 'uv_' . $uvName;
			$$uvVarNm = $colName;
			require 'UserView.php';
			if ($uv_Sql) {
				if ($sqlWhere) {$sqlWhere .= "<br> and $uv_Sql";}
				else           {$sqlWhere = " $uv_Sql";}
			}
			require  'SetRowClass.php';
			$colDesc = RetValue("UFFLDN='{$colUvsn}'", "SYUFLD", "UFDESC");
			print "\n <tr class=\"$rowClass\"> ";
			print "\n     <td class=\"colalph\">$colDesc</td>";
			print "\n     <td class=\"colalph\">$colName</td>";
			print "\n </tr> ";
		}
		}
	}

	print "\n </table> ";
	print "\n </fieldset> ";

	if ($sqlWhere != "") {
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n <legend class=\"legendTitle\">Current User View</legend> ";
		print "\n <div>$sqlWhere</div>";
		print "\n </fieldset> ";
	}
}

if ($iePos === false) {print "\n <a href=\"javascript:self.close()\">$closeImageMed</a>";}
else                  {print "\n <a href=\"javascript:window.close()\">$closeImageMed</a>";}
print "$hrTagAttr";
require_once 'Copyright.php';
print "</td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";
?>