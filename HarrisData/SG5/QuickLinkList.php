<?php

$linkCount=$quicklinkCount;
if ($linkCount > "0") {
	$linksPerRow=RetValue("QDD2WNU='" . strtoupper($scriptName) . "' and QDQLNKU='LINKSPERROW'", "SYQLND", "QDNROW");
	if (!$linksPerRow) {$linksPerRow= $quickLinksPerRow;}

	$remainder=$linkCount % $linksPerRow;
	$cellCount=gmp_div_q($linkCount, $linksPerRow);
	$cellCount = $remainder + $cellCount;

	print "\n <table $quickLinkTable> ";
	print "\n <tr> ";
	print "\n <td class=\"qllist\"> ";
	print "\n <ul class=\"qllist\"> ";

	$x      = "1";
	$tdCount= "1";
	while ($x <= $quicklinkCount) {
		$quickRow = db2_fetch_assoc($quicklinkSeqTable, $x);
		$quicklinkRef  =strtolower(trim($quickRow['DQLNKU']));
		$quicklinkTitle=trim($quickRow['QDDESC']);
		$quicklinkImage=trim($quickRow['QDIMGN']);
		$quicklinkURLID=trim($quickRow['QDURLID']);
		$quicklinkClass=trim($quickRow['QDCLAS']);
		$imageExists= "";
		if ($quicklinkImage != "" && $quicklinkDspImage == "Y") {
			$quickLinkImagePath= $quicklinkImage;
			if (file_exists($quickLinkImagePath)) {$imageExists="Y"; }
			else                                  {$imageExists="N"; }
		}
		$workURL = "";
		if ($quicklinkURLID != "") {
			$workURL=RetValue("FUID='$quicklinkURLID'", "SYURLM", "FUURL");
			if ($workURL != "") {
				$V_FUTRGT=RetValue("FUID='$quicklinkURLID'", "SYURLM", "FUTRGT");
				if      ($V_FUTRGT == "")                    {$tgt = "";}
				elseif  (strtoupper($V_FUTRGT) == "COMMENT") {$tgt= " onclick=\"$commentWinVar\" ";}
				elseif  (strtoupper($V_FUTRGT) == "INQUIRY") {$tgt= " onclick=\"$inquiryWinVar\" ";}
				elseif  ($V_FUTRGT != "")                    {$tgt= " Target=\"$V_FUTRGT\" ";}

				$poshomeURL=strpos($workURL,"@@homeURL");
				$workURL   =str_replace("@@homeURL",$homeURL,$workURL);
				if (strpos($workURL, "@@phpPath") !== false) {
					$baseVarWrk = $baseVar;
					$workURL = str_replace("@@phpPath", $phpPath, $workURL);
				}else{
					$baseVarWrk = str_replace(".php", ".icl", $baseVar);
					$workURL = str_replace("@@cGIPath", $cGIPath, $workURL);
				}
				$workURL   =str_replace("@@helpPath",$helpPath,$workURL);
				$workURL   =str_replace("@@userProfile",$userProfile,$workURL);
				$workURL   =str_replace("@@timeStamp",$_SERVER['REQUEST_TIME'],$workURL);

				if (strpos($workURL,"@@meapid") !== false) {
					if ($HDMERL>"0") {$meapid= "ME";}
					else             {$meapid= "ET";}
					$workURL=str_replace("@@meapid",$meapid,$workURL);
				}

				if (strpos($workURL,"?") !== false) {$workAmp= "&amp;";}
				else                                {$workAmp= "?";}

				if ($poshomeURL != "0") {
					if (strpos($workURL,"@@baseVar") === false) {$workURL .= "{$workAmp}baseVar=" . urlencode($baseVar) . "&amp;eID=" . urlencode($eID); }
					else                                        {$workURL  =str_replace("@@baseVar",urlencode($baseVar),$workURL);}
					if (strpos($workURL,"@@portal") === false)  {$workURL .= "{$workAmp}portal=" . urlencode($portal);}
					else                                        {$workURL  =str_replace("@@portal",urlencode($portal),$workURL);}
				}
				$workURL=Set_URL($workURL);
			}
		}
		if ($quicklinkClass != "") {$classOverride= $quicklinkClass;}
		else                       {$classOverride= "qllist";}

		if     ($workURL != "" && $imageExists == "Y") {print "\n <li class=\"$classOverride\"><a href=\"{$workURL}\"$tgt title=\"Click here to go to $quicklinkTitle\"><img border=\"$imageBorder\" src=\"$quickLinkImagePath\" alt=\"$quicklinkTitle\"></a></li>"; }
		elseif ($workURL != "")                        {print "\n <li class=\"$classOverride\"><a href=\"{$workURL}\"$tgt title=\"Click here to go to $quicklinkTitle\">$quicklinkTitle</a></li>";}

		$x ++;
		$tdCount ++;
		if ($tdCount > $cellCount) {
			print "\n </ul></td> ";
			print "\n <td>&nbsp;</td> ";
			print "\n <td class=\"qllist\"><ul class=\"qllist\"> ";
			$tdCount= "1";
		}
	}
	print "\n </ul> </td> </tr> </table> ";
	print $hrTagAttr;
}
?>