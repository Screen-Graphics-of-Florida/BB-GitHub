<?php
print "\n function RetValueAjax(selWhere,selTable, selColumn, retVal) { ";
print "\n   var url = \"" . $homeURL . $phpPath . "RetValueAjaxCall.php" . $scriptVarBase . "&amp;selWhere=\" + escape(selWhere) + \"&amp;selTable=\" + escape(selTable) + \"&amp;selColumn=\" + escape(selColumn) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
print "\n   var response= request.responseText.split(\"|\"); ";

print "\n   var retVal=response[1]; ";
print "\n   return retVal; ";
print "\n } ";
?>