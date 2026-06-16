<?php

require_once 'RetValueAjax.php';


print "\n function CustomerPAYMENT(C) { ";
print "\n   var edtVar = \"@@bchn" . $fromBatchNumber . "}{@@bchd" . $fromBatchDate . "}{@@bchb" . $fromBatchBank . "}{@@type" . $fromType . "}{@@id@@\" + C + \"}{\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "ApplCashCustomerExit.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
print "\n   var response= request.responseText.split(\"|\"); ";

print "\n   var retVal=response[1]; ";
print "\n   if (retVal != \"\") {alert(retVal);return false;} ";
print "\n   else { ";

print "\n     var selWhere =\"CICUST=\" + C +\" and CIIUSR<>' ' and (CIIBCH<>" . $fromBatchNumber . " or CIIDTE<>" . $fromBatchDate . " or CIIBNK<>" . $fromBatchBank  . " or CIIUSR<>'" . trim($userProfile)  . "' or CITYPE<>'" . $fromType  . "' or CIID<>\" + C  +\" ) \"; ";
print "\n     var selTable =\"HDCUSI\"; ";
print "\n     var selColumn=\"Char(Count(*))\"; ";
print "\n     var retVal=\"\"; ";
print "\n     var customerInUse=RetValueAjax(selWhere,selTable, selColumn, retVal); ";
print "\n     if (customerInUse > 0) {alert(\"Customer Already In Use. Refresh page for more information.\");return false;} ";
print "\n     else                   {CustomerAddHDCUSI(C);return true;} ";
print "\n   } ";
print "\n } ";

print "\n function CustomerRELEASE(C) { ";
print "\n   var edtVar = \"@@bchn" . $fromBatchNumber . "}{@@bchd" . $fromBatchDate . "}{@@bchb" . $fromBatchBank . "}{@@typeC}{@@id@@\" + C; ";
print "\n       edtVar += \"}{@@skipY\"; ";
print "\n       edtVar += \"}{\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "ApplCashPaymentReleaseEntry.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
print "\n } ";

print "\n function PayerPAYMENT(C) { ";
print "\n   var edtVar = \"@@bchn" . $fromBatchNumber . "}{@@bchd" . $fromBatchDate . "}{@@bchb" . $fromBatchBank . "}{@@type" . $fromType . "}{@@id@@\" + C + \"}{\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "ApplCashCustomerExit.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
print "\n   var response= request.responseText.split(\"|\"); ";

print "\n   var retVal=response[1]; ";
print "\n   if (retVal != \"\") {alert(retVal);return false;} ";
print "\n   else { ";

print "\n     var selWhere =\"PYPAYR=\" + C; ";
print "\n     var selTable =\"ARPYRH inner join ARPYRC on PCPAYR=PYPAYR left exception join HDCUSI on CICUST=PCCUST\"; ";
print "\n     var selColumn=\"Char(Count(*))\"; ";
print "\n     var retVal=\"\"; ";
print "\n     var OpenPayerCust=RetValueAjax(selWhere,selTable, selColumn, retVal); ";

print "\n     var selWhere =\"PYPAYR=\" + C; ";
print "\n     var selTable =\"ARPYRH inner join HDCUSI on (CIIBCH,CIIDTE,CIIBNK,CIIUSR,CITYPE,CIID)=(" . $fromBatchNumber. "," . $fromBatchDate. "," . $fromBatchBank. ",'" . $userProfile. "','" . $fromType. "',PYPAYR)\"; ";
print "\n     var selColumn=\"Char(Count(*))\"; ";
print "\n     var retVal=\"\"; ";
print "\n     var InUseThisCust=RetValueAjax(selWhere,selTable, selColumn, retVal); ";

print "\n     if (OpenPayerCust > 0 || InUseThisCust >0) {PayerAddHDCUSI(C);return true;} ";
print "\n     else                                       {alert(\"Payer Already In Use. Refresh page for more information.\");return false;} ";
print "\n   } ";
print "\n } ";

print "\n function PayerRELEASE(C) { ";
print "\n   var edtVar = \"@@bchn" . $fromBatchNumber . "}{@@bchd" . $fromBatchDate . "}{@@bchb" . $fromBatchBank . "}{@@typeP}{@@id@@\" + C; ";
print "\n       edtVar += \"}{@@skipY\"; ";
print "\n       edtVar += \"}{\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "ApplCashPaymentReleaseEntry.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
print "\n } ";

print "\n function CustomerAddHDCUSI(C) { ";
print "\n   var stmt = \" Insert Into HDCUSI (CICUST,CIIBCH,CIIDTE,CIIBNK,CIIUSR,CITYPE,CIID) \"; ";
print "\n   stmt += \" Select \" + C + \",{$fromBatchNumber},{$fromBatchDate},{$fromBatchBank},'{$userProfile}','C',\" + C; ";
print "\n   stmt += \" From SYUSER Where USUSER='{$userProfile}' \"; ";
print "\n   stmt += \" and not exists (Select * from HDCUSI Where CICUST=\" + C + \") \"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
print "\n } ";

print "\n function PayerAddHDCUSI(C) { ";
print "\n   var stmt = \" Insert Into HDCUSI (CICUST,CIIBCH,CIIDTE,CIIBNK,CIIUSR,CITYPE,CIID) \"; ";
print "\n   stmt += \" Select PCCUST,{$fromBatchNumber},{$fromBatchDate},{$fromBatchBank},'{$userProfile}','P',\" + C; ";
print "\n   stmt += \" from ARPYRC a \"; ";
print "\n   stmt += \" Where PCPAYR=\" + C ; ";
print "\n   stmt += \" and not exists (Select * from HDCUSI Where CICUST=a.PCCUST) \"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   request = new getXMLHTTPRequest(); ";
print "\n   request.open(\"GET\", url, false); ";
print "\n   request.send(null); ";
print "\n } ";

?>