<?php

// Delete Payment (selected Delete icon)
print "\n function delARPYENLine(ISEQ,ENID,PMID,PTYP,CRTB) { ";
print "\n   var MnCd='D'; ";
print "\n   var PRCOLM=''; ";
print "\n   updARPYEN(ISEQ,ENID,PMID,PTYP,CRTB,MnCd,PRCOLM); ";
print "\n   var Rowfld=\"row\"+ISEQ+\"_\"+ENID; ";
print "\n   document.getElementById('paymentTable').deleteRow(document.getElementById(Rowfld).rowIndex); ";
print "\n } ";

// Update Payment in ARPYEN
print "\n function updARPYEN(ISEQ,ENID,PMID,PTYP,CRTB,MnCd,PRCOLM) { ";
print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
print "\n   disableEntryFld(PESPMTfld,\"Y\"); ";

print "\n   var edtVar = \"@@bchn" . $fromBatchNumber . "}{@@bchd" . $fromBatchDate . "}{@@bchb" . $fromBatchBank . "}{@@type" . $fromType . "}{@@id@@" . $fromID . "}{@@chk@" . $fromDocument . "\"; ";
print "\n       edtVar += \"}{@@mncd\" + MnCd; ";
print "\n       edtVar += \"}{@@ptyp\" + PTYP; ";
print "\n       edtVar += \"}{@@pmid\" + PMID; ";
print "\n       edtVar += \"}{@@iseq\" + ISEQ; ";
print "\n       edtVar += \"}{@@enid\" + ENID; ";
print "\n       edtVar += \"}{@@crtb\" + CRTB ; ";
print "\n       edtVar += \"}{@@colm\" + PRCOLM ; ";
print "\n       edtVar += \"}{\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdARPYEN.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   var ajaxRequest = new ajaxObject(url,updARPYENResponse); ";
print "\n   ajaxRequest.update();  ";
require 'ApplCashPaymentJavaUpdatePendingInclude.php';
print "\n } ";

// Update Payment in ARPYEN Response
print "\n function updARPYENResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
print "\n     document.getElementById('depositEntry').innerHTML = response[1]; ";
print "\n     document.getElementById('otherEntry').innerHTML = response[2]; ";
print "\n     document.getElementById('depositBalance').innerHTML = response[3]; ";
print "\n     document.getElementById('otherBalance').innerHTML = response[4]; ";
print "\n     document.getElementById('CASHCNT').innerHTML = response[5]; ";
print "\n     document.getElementById('CASHAMT').innerHTML = response[6]; ";
print "\n     document.getElementById('CASHDSC').innerHTML = response[7]; ";
print "\n     document.getElementById('CASHVAR').innerHTML = response[8]; ";
print "\n     document.getElementById('OTHERCNT').innerHTML = response[9]; ";
print "\n     document.getElementById('OTHERAMT').innerHTML = response[10]; ";
print "\n     document.getElementById('OTHERVAR').innerHTML = response[11]; ";
print "\n     document.getElementById('CECICN').innerHTML = response[12]; ";
print "\n     document.getElementById('CECSAM').innerHTML = response[13]; ";
print "\n     document.getElementById('CECDAM').innerHTML = response[14]; ";
print "\n     document.getElementById('CEJICN').innerHTML = response[15]; ";
print "\n     document.getElementById('CEJSAM').innerHTML = response[16]; ";
print "\n     document.getElementById('CEDGIC').innerHTML = response[17]; ";
print "\n     document.getElementById('CEDGAM').innerHTML = response[18]; ";
print "\n     document.getElementById('CEDICN').innerHTML = response[19]; ";
print "\n     document.getElementById('CEDSAM').innerHTML = response[20]; ";
print "\n     document.getElementById('CEUICN').innerHTML = response[21]; ";
print "\n     document.getElementById('CEUSAM').innerHTML = response[22]; ";
print "\n     document.getElementById('CEUDAM').innerHTML = response[23]; ";
print "\n     document.getElementById('CEYICN').innerHTML = response[24]; ";
print "\n     document.getElementById('CEYSAM').innerHTML = response[25]; ";
print "\n     document.getElementById('CEYDAM').innerHTML = response[26]; ";
print "\n     var MnCd=response[27]; ";
print "\n     var PRCOLM=response[28]; ";
print "\n     var ISEQ=response[29]; ";
print "\n     var ENID=response[30]; ";
print "\n     if (MnCd!=\"D\") {";
print "\n       var ERRS=response[31]; ";
print "\n       var r=32; ";
print "\n       if (ERRS > 0) { ";
print "\n         for (var i=0; i<ERRS; i++) { ";
print "\n           var PRCOLM=response[r]; ";
print "\n           var ERRD=response[r+1]; ";
print "\n           setColumnError(PRCOLM,ERRD,ISEQ,ENID); ";
print "\n           r=r+2 ; ";
print "\n         }; ";
print "\n       }; ";
print "\n     }; ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
print "\n } ";

?>