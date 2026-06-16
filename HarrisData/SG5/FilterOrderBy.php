<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'SetLibraryList.php';
require_once 'hdListInclude.php';

$fromScript = (isset($_GET['fromScript']))   ? strtoupper($_GET['fromScript']) : "";
$clrSel     = (isset($_GET['clrSel']))  ? $_GET['clrSel']  : null;

/* Script Variables	*/
$page_title    = "Sequence By Selection";
$scriptName    = "FilterOrderBy.php";
$scriptVarBase = "{$genericVarBase}&amp;tblID=" . urlencode($tblID) . "&amp;pagID=" . urlencode($pagID) . "&amp;fromScript=" . urlencode($fromScript);
if ($nMenu)  {$scriptVarBase .= "&amp;nMenu=" . urlencode($nMenu);}
if ($fRel)   {$scriptVarBase .= "&amp;fRel=" . urlencode($fRel);}
if ($fKey1)  {$scriptVarBase .= "&amp;fKey1=" . urlencode($fKey1) . "&amp;fVal1=" . urlencode($fVal1);}
if ($fKey2)  {$scriptVarBase .= "&amp;fKey2=" . urlencode($fKey2) . "&amp;fVal2=" . urlencode($fVal2);}
if ($fKey3)  {$scriptVarBase .= "&amp;fKey3=" . urlencode($fKey3) . "&amp;fVal3=" . urlencode($fVal3);}
if ($fKey4)  {$scriptVarBase .= "&amp;fKey4=" . urlencode($fKey4) . "&amp;fVal4=" . urlencode($fVal4);}
if ($fKey5)  {$scriptVarBase .= "&amp;fKey5=" . urlencode($fKey5) . "&amp;fVal5=" . urlencode($fVal5);}
if ($fDsc1)  {$scriptVarBase .= "&amp;fDsc1=" . urlencode($fDsc1);}
if ($fDsc2)  {$scriptVarBase .= "&amp;fDsc2=" . urlencode($fDsc2);}
if ($fDsc3)  {$scriptVarBase .= "&amp;fDsc3=" . urlencode($fDsc3);}
if ($fDsc4)  {$scriptVarBase .= "&amp;fDsc4=" . urlencode($fDsc4);}
if ($fDsc5)  {$scriptVarBase .= "&amp;fDsc5=" . urlencode($fDsc5);}
$baseURL       = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$popUpWin        = "Y";
$browser = getenv("HTTP_USER_AGENT");
$iePos = strpos($browser, "MSIE");
$medIcon = "Y";

if ($tag == "SELECT") {
	global $edtVar;
	$sortSel = "";
	$edtVar  = "";
	for ($x=1; $x<=9; $x++) {
		foreach ($srchCol as $colName) {
			if ($_POST["sort$colName"] == $x) {
				$sortSel = "Y";
				foreach ($hdDocsetRow->xpath("col[@id='" . trim($colName) . "']") as $col) {
					$colHeading = (string) $col->label;
					while (strpos($colHeading, "@@parm[") !== false) {
						$parmName = Decat_Parm($colHeading);
						$colHeading = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colHeading);
					}
					$colAltSort = (string) $col->alt_sort;
					if ($colAltSort == "UPPER") {$sortFld = "upper($colName)";}
					else                        {$sortFld = ($colAltSort)? $colAltSort : $colName;}
					$sortFld = "({$sortFld})";
					$adSeq      = ($_POST["dseq$colName"])? "D" : "A";
					Concat_Field("@@ob{$x}f", $sortFld);
					Concat_Field("@@ob{$x}s", $adSeq);
					Concat_Field("@@ob{$x}d", $colHeading);
				}
			}
		}

	}
	$edtVar .= "}{";

	if ($sortSel) {
		require 'stmtSQLClear.php';
		$stmtSQL = " Update SYLFLW Set LWOVAR='$edtVar' Where LWXHND='$profileHandle' and LWSCRNU='$fromScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=0";
		$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

		if (!$sqlResult) {
			require 'stmtSQLClear.php';
			$stmtSQL .= " Insert Into SYLFLW (LWXHND,LWSCRNU,LWTBID,LWPGID,LWFLID,LWSEQ,LWNAME,LWFVAR,LWOVAR,LWCVAR) ";
			$stmtSQL .= " Values ('$profileHandle','$fromScript',$tblID,$pagID,0,0,' ',' ','$edtVar',' ')  ";
			$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
		}
	}

	$fromURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;timeStamp=" . urlencode(time());
	$fromURL  = str_replace("amp;", "", $fromURL);
	print "\n <script TYPE=\"text/javascript\">";
	print "\n opener.location.href='$fromURL'";
	print "\n opener.focus();";
	print "\n window.close();";
	print "\n </script>";
	exit();
}

require_once ($docType);
print "\n <html> 	<head>";
$formName = "Chg";
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
print "\n <tr><td><h1>$pageHeading1</h1></td>";
print "\n <td class=\"toolbar\">";
print "\n <a href=\"javascript:check(document.Chg)\">$selectAcceptImage</a>";
print "\n <a href=\"$baseURL&amp;clrSel=Y\">$selectClearImage</a>";
if ($iePos === false) {
	print "\n <a href=\"javascript:self.close()\">$closeImageMed</a>";
} else {
	print "\n <a href=\"javascript:window.close()\">$closeImageMed</a>";
}
require 'HelpPage.php';
print "</td>";
print "\n </tr>";
print "\n </table>";
print $hrTagAttr;

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$baseURL}&amp;tag=SELECT\">";
print "\n <table $quickSearchTable>";
print "\n <tr>";
print "\n     <th class=\"colhdr\">Column</th>";
print "\n     <th class=\"colhdr\">Sequence</th>";
print "\n     <th class=\"colhdr\">Descending</th>";
print "\n </tr>";

$ordByVar = ($clrSel !== null) ? "" : RetValue("LWXHND='$profileHandle' and LWSCRNU='$fromScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=0", "SYLFLW", "LWOVAR");

foreach ($srchCol as $colName) {
	foreach ($hdDocsetRow->xpath("col[@id='" . trim($colName) . "']") as $col) {
		$colHeading = (string) $col->label;
		$colAltSort = (string) $col->alt_sort;
		if ($colAltSort == "UPPER") {$sortFld = "upper($colName)";}
		else                        {$sortFld = ($colAltSort)? $colAltSort : $colName;}
		$sortFld = "({$sortFld})";
		while (strpos($colHeading, "@@parm[") !== false) {
			$parmName = Decat_Parm($colHeading);
			$colHeading = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colHeading);
		}
		$colHeading = str_replace("<br>", " ", $colHeading);
		print "\n <tr><td class=\"colalph\">$colHeading</td>";
		$dChecked = "";
		$sortValue = 0;
		for ($x=1; $x<=9; $x++) {
			$orderByFld = Decat_Field("@@ob{$x}f", $ordByVar);
			if ($orderByFld == $sortFld) {
				$orderBySeq = Decat_Field("@@ob{$x}s", $ordByVar);
				if ($orderBySeq == "D") {$dChecked = "CHECKED";}
				$sortValue = $x;
				break;
			}
		}
		print "\n <td align=\"center\">";
		print "\n <select name=\"sort$colName\">";
		print "\n <option value=\"0\">";
		for ($x=1; $x<=9; $x++) {
			if ($x == $sortValue) {
				print "\n <option value=\"" . rtrim($x) . "\" SELECTED>$x</option>";
			} else {
				print "\n <option value=\"" . rtrim($x) . "\">$x</option>";
			}
		}
		print "\n </select>";
		print "\n </td>";
		print "\n <td align=\"center\"><input name=\"dseq$colName\" type=\"checkbox\" $dChecked value=\"Y\"></td>";
		print "\n </tr>";
	}
}

print "\n </table>";
print "\n </form>";
print "\n <a href=\"javascript:check(document.Chg)\">$selectAcceptImage</a>";
print "\n <a href=\"$baseURL&amp;clrSel=Y\">$selectClearImage</a>";
if ($ffPos !== false) {
	print "\n <a href=\"javascript:self.close()\">$closeImageMed</a>";
} else {
	print "\n <a href=\"javascript:window.close()\">$closeImageMed</a>";
}
require 'HelpPage.php';
print "$hrTagAttr";
require_once 'Copyright.php';
print "</td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";

?>