<?php
$commentImageURL=str_replace('"','\"',$commentImage); $commentImageURL=str_replace("'","''",$commentImageURL);
$commentExistImageNoTitleURL=str_replace('"','\"',$commentExistImageNoTitle); $commentExistImageNoTitleURL=str_replace("'","''",$commentExistImageNoTitleURL);

// Accept Comment Entry
print "\n function AcceptCmtEntry(ISEQ,ENID) { ";
print "\n   var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+ENID ; ";
print "\n   var commententryfld=\"commententry\"+ISEQ+\"_\"+ENID ; ";
print "\n   var responseFlds=ISEQ+\"|\"+ENID ; ";
print "\n   var PEENIDvalue=getHiddenJavaArrayValue(\"ENID\",ISEQ,ENID) ; ";
print "\n   var IVISEQvalue=getHiddenJavaArrayValue(\"ISEQ\",ISEQ,ENID) ; ";
print "\n   var cmnt = document.getElementById(newPECMNTfld).value ";
print "\n   var cmnt = cmnt.replace(/'/g, \"''\"); ";
print "\n   var stmt = \" Update ARPYEN \"; ";
print "\n       stmt += \" Set PECMNT='\" + cmnt +\"'\"; ";
print "\n       stmt += \" Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID,PEISEQ,PEENID)=(" . $fromBatchNumber . "," . $fromBatchDate . "," . $fromBatchBank . ",'" . $fromType . "'," . $fromID . ",'" . $fromDocument . "','" . $paymentType . "','" . $paymentID . "',\" + IVISEQvalue.toString() + \",\" + PEENIDvalue.toString()+\")\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdateWithResponse.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;responseFlds=\" + escape(responseFlds) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   var ajaxRequest = new ajaxObject(url,AcceptCmtEntryResponse); ";
print "\n   ajaxRequest.update();  ";
require 'ApplCashPaymentJavaUpdatePendingInclude.php';
print "\n   hideSel(commententryfld); ";
print "\n } ";

// Accept Comment Entry Response
print "\n function AcceptCmtEntryResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
print "\n     var ISEQ=response[1]; ";
print "\n     var ENID=response[2]; ";
print "\n     var oldPECMNTfld=\"oldcmt\"+ISEQ+\"_\"+ENID ; ";
print "\n     var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+ENID ; ";
print "\n     var cmtIconfld=\"cmt\"+ISEQ+\"_\"+ENID ; ";
print "\n     if (document.getElementById(oldPECMNTfld).innerHTML!=\"\" && document.getElementById(newPECMNTfld).value==\"\") { ";
print "\n       document.getElementById(cmtIconfld).innerHTML= \"{$commentImageURL}\" ; } ";
print "\n     else if (document.getElementById(oldPECMNTfld).innerHTML==\"\" && document.getElementById(newPECMNTfld).value!=\"\") { ";
print "\n       document.getElementById(cmtIconfld).innerHTML= \"{$commentExistImageNoTitleURL}\" ; } ";
print "\n     document.getElementById(oldPECMNTfld).innerHTML = document.getElementById(newPECMNTfld).value; ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  }";
require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
print "\n } ";

// Clear Comment Entry
print "\n function ClearCmtEntry(ISEQ,ENID) { ";
print "\n   var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(newPECMNTfld).value = \"\"; ";
print "\n   document.getElementById(newPECMNTfld).focus() ; ";
print "\n } ";

// Close Comment Entry
print "\n function CloseCmtEntry(ISEQ,ENID) { ";
print "\n   ResetCmtEntry(ISEQ,ENID); ";
print "\n   var commententryfld=\"commententry\"+ISEQ+\"_\"+ENID ; ";
print "\n   hideSel(commententryfld); ";
print "\n } ";

// Reset Comment Entry
print "\n function ResetCmtEntry(ISEQ,ENID) { ";
print "\n   var oldPECMNTfld=\"oldcmt\"+ISEQ+\"_\"+ENID ; ";
print "\n   var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(newPECMNTfld).value = document.getElementById(oldPECMNTfld).innerHTML; ";
print "\n   document.getElementById(newPECMNTfld).focus() ; ";
print "\n } ";

?>