<?php
if     ($paymentType=="C") {$CESBCD=RetValue("(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ", "ARDCEN", "CECSBC");}
elseif ($paymentType=="D") {$CESBCD=RetValue("(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ", "ARDCEN", "CEDSBC");}
elseif ($paymentType=="J") {$CESBCD=RetValue("(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ", "ARDCEN", "CEJSBC");}
elseif ($paymentType=="U") {$CESBCD=RetValue("(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ", "ARDCEN", "CEUSBC");}
elseif ($paymentType=="Y") {$CESBCD=RetValue("(CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ", "ARDCEN", "CEYSBC");}
print "\n var curSubCode=\"{$CESBCD}\"; ";

// Change Payment Code
print "\n function editSubCode() { ";
print "\n   document.getElementById('subCode').value=document.getElementById('subCode').value.toUpperCase(); ";
print "\n   var toSubCode=document.getElementById('subCode').value; ";
print "\n   if (toSubCode != curSubCode) { ";
if ($paymentType=="C") {print "\n     if (confirm(\"All pending Cash Payments will be changed to use the new payment code \" + toSubCode))  { ";}
print "\n       var edtVar = \"@@bchn" . $fromBatchNumber . "}{@@bchd" . $fromBatchDate . "}{@@bchb" . $fromBatchBank . "}{@@type" . $fromType . "}{@@id@@" . $fromID . "}{@@chk@" . $fromDocument . "}{@@ptyp" . $paymentType . "}{@@sbcd\" + toSubCode.replace(/\\s/g,\"\")+\"}{\"; ";
print "\n       var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdSubCode.php" . $scriptVarBase . "&amp;fromProfileHandle=" . $profileHandle . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n       var ajaxRequest = new ajaxObject(url,editSubCodeResponse); ";
print "\n       ajaxRequest.update();  ";
print "\n       curSubCode=toSubCode;  ";
require 'ApplCashPaymentJavaUpdatePendingInclude.php';
if ($paymentType=="C") {print "\n     } ";}
print "\n   } ";
print "\n } ";

// Change Payment Code Response
print "\n function editSubCodeResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
print "\n     var ERRD=response[1]; ";
print "\n     if (ERRD !=\"\") { ";
print "\n       document.getElementById('subCode').style.backgroundColor = '" . $errorBackground . "'; ";
print "\n       document.getElementById('subCode').setAttribute(\"title\", ERRD) ; ";
print "\n     } else { ; ";
print "\n       if (document.getElementById('subCode').getAttribute(\"title\")) {document.getElementById('subCode').removeAttribute(\"title\") ;} ";
print "\n       document.getElementById('subCode').style.backgroundColor = 'transparent'; ";
print "\n       document.getElementById('subCodeDesc').innerHTML=response[2]; ";
print "\n     }; ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
print "\n } ";

?>