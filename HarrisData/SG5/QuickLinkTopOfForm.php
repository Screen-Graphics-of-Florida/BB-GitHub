<?php
print "\n <fieldset class=\"legendBody\"> ";

$displayTitle="{$quicklinkTitle}";

if ($pageSelectList == "Y") {
	if      (!$sql_Record_Count)                    {$displayMaxRowsMsg="N";}
	elseif  ($sql_Record_Count < $quicklinkMaxRows) {$displayMaxRows= $sql_Record_Count;}
	else                                            {$displayMaxRows= $quicklinkMaxRows;}

	if ($displayMaxRowsMsg != "N") {$displayTitle .= "<span class=\"legendTitleData\">($displayMaxRows Of $sql_Record_Count Rows)</span>"; }
}

$displayMaxRowsMsg="";
print "\n <legend class=\"legendTitle\">$displayTitle</legend> ";

if (($formatToPrint == "" || $formatToPrint == "N") && ($quicklinkCount > 1 || $moreURL != "")) {
	print "\n <div class=\"quickLinksTop\"> ";
	if     ($quicklinkCount>1 && $moreURL != "" && $moreWinVar != "") {print "\n <a href=\"{$moreURL}\" onclick=\"$moreWinVar\">$moreInfoImage</a> <a href=\"#top\">$topOfFormImage</a> "; }
	elseif ($moreURL != "" && $moreWinVar != "")                      {print "\n <a href=\"{$moreURL}\" onclick=\"$moreWinVar\">$moreInfoImage</a> ";}
	elseif ($quicklinkCount>1 && $moreURL != "")                      {print "\n <a href=\"{$moreURL}\">$moreInfoImage</a> <a href=\"#top\">$topOfFormImage</a> ";}
	elseif ($moreURL != "")                                           {print "\n <a href=\"{$moreURL}\">$moreInfoImage</a> ";}
	elseif ($quicklinkCount>1)                                        {print "\n <a href=\"#top\">$topOfFormImage</a> ";}

	if ($quickLinkByUser == "Y" && $quicklinkLoaded != "" && $quicklinkCount>1) {
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=REPORT&amp;quicklinkRemove=" . urlencode($quicklinkRef) . "&amp;quicklinkLoaded=" . urlencode($quicklinkLoaded) . "\" title=\"Click here to display $quicklinkTitle\">$closeImageMed</a> ";
	}

	print "\n </div> ";
	if ($pageSelectList == "N" || $sql_Record_Count) {print "\n <br> ";}
	$moreWinVar= "";
}

?>