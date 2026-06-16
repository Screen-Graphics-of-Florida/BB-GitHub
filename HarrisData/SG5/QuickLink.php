<?php

// This function builds the sequence of "quicklink" display.
// Any page using dynamic quicklink sequencing should be calling this function.

//  The quicklinkSeqTable includes the following columns:
//      1   quicklinkReference (e.g. "demographics" identifies "#demographics")
//      2   quicklinkTitle     (e.g. "Demographics")
//      3   quicklinkMaxRows   Assigned to maxRows
//      4   quicklinkImage     link image
//      5   quicklinkSeqNbr    Used by RPG program to sort quicklinks.
//      6   quicklinkURLID     URL ID.
//      7   quicklinkClass     Override CSS Class

//   NOTES:
//      The quicklinkSeqTable must be sorted in ascending order by
//      quicklinkSeqNbr.

//      If a quicklink does not appear in the table, the link and the link-to info
//      will not display.

//      If there are no rows (empty table), NO quicklinks will appear.

$quickLinksPerRow      = "5";
$noInfoFoundMsg        = "No XXX Information Available.";
$quicklinkSelected     = $_GET['quicklinkSelected'];
$quicklinkRemove       = $_GET['quicklinkRemove'];
$quicklinkLoaded       = $_GET['quicklinkLoaded'];
$quicklinkSelSeq       = $_GET['quicklinkSelSeq'];

function Format_Column_Header ($columnName,$columnDesc) {
	$returnValue=OrderBy_Sort($columnName); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	$columnHdr=$columnDesc;
	require 'SelectPageColumnHdr.php';
}

/*

function Remove_Quick_Link ($linkName) {
	$linkName=strtoupper($linkName);
	$z= 0;
	while((@dtw_tb_rgetv(quicklinkSeqTable, z, "1") != "$linkName") && (z < $quicklinkCount)) {
		$z ++;
		if (@dtw_tb_rgetv(quicklinkSeqTable, z, "1") == "$linkName") {
			@dtw_tb_deleterow(quicklinkSeqTable, z, "1")
			$quicklinkCount --;
		}
	}

	function Remove_Quick_Link_URL ($linkName) {
		$z= "1";
		while((@dtw_tb_rgetv(quicklinkSeqTable, z, "1") != "$linkName") && (z < $quicklinkCount)) {
			$z ++; }
			if (@dtw_tb_rgetv(quicklinkSeqTable, z, "1") == "$linkName") {
				@dtw_tb_setv(quicklinkSeqTable, "", "z", "6")
			}
	}

*/	
?>