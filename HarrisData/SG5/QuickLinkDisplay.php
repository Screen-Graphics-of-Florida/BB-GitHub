<?php

$linksPerRow=RetValue("QDD2WNU='" . strtoupper($scriptName) . "' and QDQLNKU='LINKSPERROW'", "SYQLND", "QDNROW");
if (!$linksPerRow) {$linksPerRow = $quickLinksPerRow;}

if ($quicklinkLoaded == "" && $quickLinkByUser == "Y") {
	$quicklinkLoaded= "hideAll";
	$initialLoad    = "Y";
} else {
	$initialLoad= "";
}

if ($quicklinkCount == 1 && $quickLinkByUser == "Y") {
	$initialLoad= "Y";
	$quicklinkRef=strtolower($quicklinkSeqTable[0]['QDQLNKU']);
	$quicklinkLoaded = $quicklinkRef;
}

if (($formatToPrint == "" || $formatToPrint == "N") && $quicklinkCount > 0) {
	if ($quickLinkByUser == "Y") {
		print "\n <div class=\"quickLinksTop\"> ";
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;quicklinkSelected=useDefault\">$qlinkDftLrg</a> ";
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;quicklinkSelected=saveDefault\">$qlinkSetLrg</a> ";
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;quicklinkSelected=viewAll\">$qlinkAllLrg</a> ";
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;quicklinkSelected=hideAll\">$qlinkClearLrg</a> ";
		if ($quickLinkAllRows == "Y") {
			if (strpos($quicklinkLoaded, "allRows") !== false) {print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;quicklinkSelected=defaultRows\">$qlinkDftRowLrg</a> ";}
			else                                               {print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;quicklinkSelected=allRows\">$qlinkMaxRowLrg</a> ";}
		}
		print "\n </div> ";
	}
	print "\n <table $quickLinkTable> ";
	$x= 1;
	$tdCount= 1;
	foreach ($quicklinkSeqTable as $quickRow) {
		$quicklinkRef  =strtolower(trim($quickRow['QDQLNKU']));
		$quicklinkTitle=trim($quickRow['QDDESC']);
		$quicklinkImage=trim($quickRow['QDIMGN']);
		$quicklinkURLID=trim($quickRow['QDURLID']);
		$quicklinkClass=trim($quickRow['QDCLAS']);

		if ($tdCount == 1) {print "\n <tr> ";}

		$imageExists= "";
		if ($quicklinkImage!= "" && $quicklinkDspImage == "Y") {
			$quickLinkImagePath=$quicklinkImage;
			if (file_exists($quickLinkImagePath)) {$imageExists="Y";}
			else                                  {$imageExists="N";}
		}
		$workURL= "";
		if ($quicklinkURLID != "") {
			$workURL=RetValue("FUID='$quicklinkURLID'", "SYURLM", "FUURL");
			if ($workURL != "") {
				$V_FUTRGT=RetValue("FUID='$quicklinkURLID'", "SYURLM", "FUTRGT");
				if     ($V_FUTRGT == "")                    {$tgt= "";}
				elseif (strtoupper($V_FUTRGT) == "COMMENT") {$tgt= " onclick=\"$commentWinVar\" ";}
				elseif (strtoupper($V_FUTRGT) == "INQUIRY") {$tgt= " onclick=\"$inquiryWinVar\" ";}
				elseif ($V_FUTRGT != "")                    {$tgt= " Target=\"$V_FUTRGT\" ";}

				$poshomeURL=strpos($workURL, "@@homeURL");
				$workURL   =str_replace("@@homeURL",$homeURL,$workURL);
				$workURL   =str_replace("@@cGIPath",$cGIPath,$workURL);
				$workURL   =str_replace("@@helpPath",$helpPath,$workURL);
				$workURL   =str_replace("@@timeStamp",urlencode($_SERVER['REQUEST_TIME']),$workURL);

				if (strpos($workURL,"@@meapid") !== false) {
					if ($HDMERL>"0") {$meapid= "ME";}
					else             {$meapid= "ET";}
					$workURL=str_replace("@@meapid",$meapid,$workURL);
				}

				if (strpos($workURL,"?") !== false) {$workAmp= "&amp;" ; }
				else                                {$workAmp= "?" ;}

				if ($poshomeURL !== false) {
					if (strpos($workURL,"@@baseVar") === false) {$workURL .= "{$workAmp}baseVar=" . urlencode($baseVar) . "&amp;eID=" . urlencode($eID);}
					else                                        {$workURL=str_replace("@@baseVar",urlencode($baseVar),$workURL);}
					if (strpos($workURL,"@@portal") === false)  {$workURL .= "{$workAmp}portal=" . urlencode($portal);}
					else                                        {$workURL=str_replace("@@portal", urlencode($portal),$workURL);}
				}
				$workURL=Set_URL($workURL);
			}
		}

		if ($initialLoad == "Y") {$quicklinkLoaded .= $quicklinkRef;}

		if ($quickLinkByUser == "Y") {$qLinkPos=strpos($quicklinkLoaded,$quicklinkRef);}

		if     ($quicklinkClass != "")                          {$classOverride=$quicklinkClass;}
		elseif ($qLinkPos !== false && $quickLinkByUser == "Y") {$classOverride="quickLinkLoaded";}
		else                                                    {$classOverride="quickLinkTabs";}

		if ($workURL != "" && $imageExists == "Y") {
			print "\n <td><a href=\"{$workURL}\"$tgt title=\"Click here to go to $quicklinkTitle\"><img border=\"$imageBorder\" src=\"$quickLinkImagePath\" alt=\"$quicklinkTitle\"></a></td> ";
		} elseif  ($workURL != "") {
			print "\n <td class=\"$classOverride\"><a href=\"{$workURL}\"$tgt title=\"Click here to go to $quicklinkTitle\">$quicklinkTitle</a></td> ";
		} elseif  ($imageExists == "Y") {
			print "\n <td><a href=\"#$quicklinkRef\" title=\"Click here to go to $quicklinkTitle\"><img border=\"$imageBorder\" src=\"$quickLinkImagePath\" alt=\"$quicklinkTitle\"></a></td> ";
		} else {
			if ($qLinkPos !== false || $initialLoad == "Y") {
				print "\n <td class=\"$classOverride\"><a href=\"#$quicklinkRef\" title=\"Click here to go to $quicklinkTitle\">$quicklinkTitle</a></td> ";
			} else {
				print "\n <td class=\"$classOverride\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;quicklinkSelected=" . urlencode($quicklinkRef) . "&amp;quicklinkSelSeq=" . urlencode($x) . "\" title=\"Click here to display $quicklinkTitle\">$quicklinkTitle</a></td> ";
			}
		}
		$x ++;
		$tdCount ++;
		if ($tdCount > $linksPerRow) {
			print "\n </tr> ";
			$tdCount= 1;
		}
	}
	if ($tdCount > 1) {print "\n </tr> ";}

	print "\n </table> ";
}

if ($initialLoad == "Y") {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Update SYQLBW Set QWQSEL='$quicklinkLoaded' ";
	$stmtSQL .= " Where QWXHND='$profileHandle' and QWD2WN='$scriptName' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
}

?>