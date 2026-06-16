<?php

print "\n function BTCH_CUST(BchN, BchD, BchB) { ";
print "\n   var stmt = \"  Insert Into ARPBCU (BUBCHN,BUBCHD,BUBCHB,BUUSER,BUTSTP,BUTSUS,BUTSPT) \"; ";
print "\n       stmt += \" Select \" + BchN + \",\" + BchD + \",\" + BchB + \",'{$userProfile}',Current_Timestamp,'{$userProfile}','Y' \"; ";
print "\n       stmt += \" From SYUSER Where USUSER='{$userProfile}' \"; ";
print "\n       stmt += \" and not exists (Select * from ARPBCU Where (BUBCHN,BUBCHD,BUBCHB,BUUSER)=(\" + BchN + \",\" + BchD + \",\" + BchB + \",'{$userProfile}')) \"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";

print "\n   var stmt = \"  Update ARPBCH Set BMPMTE='A' Where (BMBCHN,BMBCHD,BMBCHB)=(\" + BchN + \",\" + BchD + \",\" + BchB + \") \"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
print "\n } ";

print "\n function BTCH_RLSE(BchN, BchD, BchB) { ";
print "\n   var stmt = \" Delete from ARPBCU \"; ";
print "\n       stmt += \" Where (BUBCHN,BUBCHD,BUBCHB,BUUSER)=(\" + BchN + \",\" + BchD + \",\" + BchB + \",'{$userProfile}') \"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";

print "\n   var stmt = \" Delete from HDCUSI \"; ";
print "\n       stmt += \" Where CIIBCH=\" + BchN + \" and (CIIDTE,CIIBNK,CIIUSR)=(\" + BchD + \",\" + BchB + \",'{$userProfile}') \"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";

print "\n   var stmt = \" Update ARPBCH a Set BMPMTE=' ' Where (BMBCHN,BMBCHD,BMBCHB)=(\" + BchN + \",\" + BchD + \",\" + BchB+ \")\" ; ";
print "\n       stmt += \" and not exists (Select * from ARPBCU Where (BUBCHN,BUBCHD,BUBCHB)=(\" + BchN + \",\" + BchD + \",\" + BchB + \")) \"; ";
print "\n       stmt += \" and not exists (Select * from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB)=(\" + BchN + \",\" + BchD + \",\" + BchB + \")) \"; ";
print "\n       stmt += \" and not exists (Select * from ARYPTD Where (YPBCH,YPBDAT,YPBANK) =(\" + BchN + \",\" + BchD + \",\" + BchB + \")) \"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
print "\n } ";

?>