<?php
print "\n function saveCurrentURL() {";
print "\n   var url = \"" . $homeURL . $phpPath . "SaveCurrentURLUpdate.php?baseVar=" . urlencode($baseVar) . "&eID=" . urlencode($eID) . "&currentURL=" . urlencode($currentURL) . "\";";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false);";
print "\n   request.send(null);";
print "\n }";

print "\n function saveFilterURL() {";
print "\n   var url = \"" . $homeURL . $phpPath . "SaveCurrentURLUpdate.php?baseVar=" . urlencode($baseVar) . "&eID=" . urlencode($eID) . "&currentURL=" . urlencode($filterURL) . "\";";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false);";
print "\n   request.send(null);";
print "\n }";

?>