<?php
if ($quicklinkSelSeq>0) {$x= $quicklinkSelSeq;}

$quicklinkRef    =strtolower(trim($quickRow['QDQLNKU']));
$quicklinkTitle  =trim($quickRow['QDDESC']);
$quicklinkMaxRows=trim($quickRow['QDNROW']);

if     (strpos($quicklinkLoaded, "allRows") !== false)       {$quicklinkMaxRows="9999";}
elseif ($quicklinkMaxRows == "0" || $quicklinkMaxRows == "") {$quicklinkMaxRows=$dspMaxRowsDft;}

$maxRows= $quicklinkMaxRows;
$dspMaxRows  = $quicklinkMaxRows;

$qLinkPos=strpos($quicklinkLoaded, trim($quicklinkRef));
?>