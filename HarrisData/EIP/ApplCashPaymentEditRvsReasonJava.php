<?php
$CERSCD=RetValue("(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ", "ARDCEN", "CERSCD");
print "\n var curRvsReason=\"{$CERSCD}\"; ";

// Change Reverse Reason
print "\n function editRvsReason() { ";
print "\n   document.getElementById('rvsReason').value=document.getElementById('rvsReason').value.toUpperCase(); ";
print "\n   var toRvsReason=document.getElementById('rvsReason').value; ";
print "\n   if (toRvsReason != curRvsReason) { ";
print "\n     var edtVar = \"@@bchn" . $fromBatchNumber . "}{@@bchd" . $fromBatchDate . "}{@@bchb" . $fromBatchBank . "}{@@type" . $fromType . "}{@@id@@" . $fromID . "}{@@chk@" . $fromDocument . "}{@@rscd\" + toRvsReason.replace(/\\s/g,\"\")+\"}{\"; ";
print "\n     var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdRvsReason.php" . $scriptVarBase . "&amp;fromProfileHandle=" . $profileHandle . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n     var ajaxRequest = new ajaxObject(url,editRvsReasonResponse); ";
print "\n     ajaxRequest.update();  ";
print "\n     curRvsReason=toRvsReason;  ";
require 'ApplCashPaymentJavaUpdatePendingInclude.php';
print "\n   } ";
print "\n } ";

// Change Reverse Reason Response
print "\n function editRvsReasonResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
print "\n     var ERRD=response[1]; ";
print "\n     if (ERRD !=\"\") { ";
print "\n       document.getElementById('rvsReason').style.backgroundColor = '" . $errorBackground . "'; ";
print "\n       document.getElementById('rvsReason').setAttribute(\"title\", ERRD) ; ";
print "\n     } else { ; ";
print "\n       if (document.getElementById('rvsReason').getAttribute(\"title\")) {document.getElementById('rvsReason').removeAttribute(\"title\") ;} ";
print "\n       document.getElementById('rvsReason').style.backgroundColor = 'transparent'; ";
print "\n       document.getElementById('rvsReasonDesc').innerHTML=response[2]; ";
print "\n     }; ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
print "\n } ";

?>