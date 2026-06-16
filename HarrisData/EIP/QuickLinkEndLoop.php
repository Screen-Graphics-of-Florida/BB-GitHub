<?php
if ($quicklinkSelected != "" && $quicklinkSelected != "viewAll" && $quicklinkSelected != "allRows" && $quicklinkSelected != "defaultRows") {
	if ($quicklinkSelected != "useDefault" && $quicklinkSelected != "saveDefault") {$x="0";}
	print "\n <script TYPE=\"text/javascript\">";
	print "\n window.location.hash='$quicklinkSelected'";
	print "\n </script>";
	$quicklinkSelected= "";
	$quicklinkSavSeq= $quicklinkSelSeq;
	$quicklinkSelSeq= "";
}
$x ++;

?>