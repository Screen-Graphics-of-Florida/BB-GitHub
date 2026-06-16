<?php
print "\n var responseActiveCount=0; ";
require_once 'RetValueAjax.php';

// Quantity Posted Check Box changed
print "\n function checkQtyPosted(LHID,ISID,TLOT,LOT) { ";
print "\n   var qtypfld=\"qtyp\"+LHID+\"_\"+ISID+\"_\"+TLOT; ";
print "\n   var sqtyfld=\"sqty\"+LHID+\"_\"+ISID+\"_\"+TLOT; ";
print "\n   var avalfld=\"aval\"+LHID+\"_\"+ISID+\"_\"+TLOT; ";
print "\n   var confirmQty=true";
print "\n   if ((parseFloat(document.getElementById('maxQty').getAttribute('title')) < parseFloat(document.getElementById(avalfld).getAttribute('title')) + parseFloat(document.getElementById('LWWQTYsum').getAttribute('title'))) && document.getElementById(sqtyfld).checked) { ";
print "\n       confirm('Quantity Allocated (' + parseFloat(document.getElementById('LWWQTYsum').getAttribute('title')) +  ') + Selected Quantity (' + parseFloat(document.getElementById(avalfld).getAttribute('title')) + ') is greater than ' + document.getElementById('maxDesc').getAttribute('title') + ' (' + parseFloat(document.getElementById('maxQty').getAttribute('title')) + ')');  var confirmQty = false;  ";
print "\n   } ";
print "\n   if (confirmQty==true) { ";
print "\n     if (document.getElementById(sqtyfld).checked) { ";
print "\n         document.getElementById(qtypfld).value=document.getElementById(avalfld).getAttribute('title'); ";
print "\n     } else { ";
print "\n         document.getElementById(qtypfld).value=''; ";
print "\n     } ";
print "\n     updOELPWK(LHID,ISID,TLOT,LOT); ";
print "\n   } else {document.getElementById(sqtyfld).checked=false;} ";
print "\n } ";

// Quantity Posted changed
print "\n function chgQtyPosted(LHID,ISID,TLOT,LOT) { ";
print "\n   var qtypfld=\"qtyp\"+LHID+\"_\"+ISID+\"_\"+TLOT; ";
print "\n   var ftypfld=\"ftyp\"+LHID+\"_\"+ISID+\"_\"+TLOT; ";
print "\n   var sqtyfld=\"sqty\"+LHID+\"_\"+ISID+\"_\"+TLOT; ";
print "\n   var avalfld=\"aval\"+LHID+\"_\"+ISID+\"_\"+TLOT; ";
print "\n   var confirmQty=true";
print "\n   if ((parseFloat(document.getElementById('maxQty').getAttribute('title')) < parseFloat(document.getElementById(qtypfld).value) - parseFloat(document.getElementById(ftypfld).value) + parseFloat(document.getElementById('LWWQTYsum').getAttribute('title')))) { ";
print "\n       confirm('Quantity Allocated (' + parseFloat(document.getElementById('LWWQTYsum').getAttribute('title')) +  ') + Selected Quantity (' + parseFloat(document.getElementById(qtypfld).value) + ') is greater than ' + document.getElementById('maxDesc').getAttribute('title') + ' (' + parseFloat(document.getElementById('maxQty').getAttribute('title')) + ')');  var confirmQty = false;  ";
print "\n   } ";
print "\n   if (confirmQty==true) { ";
print "\n     if (document.getElementById(qtypfld).value==0) { ";
print "\n         document.getElementById(sqtyfld).checked=false; ";
print "\n     } else { ";
print "\n         document.getElementById(sqtyfld).checked=true; ";
print "\n     } ";
print "\n     updOELPWK(LHID,ISID,TLOT,LOT); ";
print "\n   } else {document.getElementById(qtypfld).value='';  ";
print "\n           document.getElementById(sqtyfld).checked=false;} ";
print "\n } ";


// Update Quantity Posted in OESLWK
print "\n function updOELPWK(LHID,ISID,TLOT,LOT) { ";
print "\n   var qtypfld=\"qtyp\"+LHID+\"_\"+ISID+\"_\"+TLOT   ; ";
print "\n   if (editNum(document.getElementById(qtypfld).name, 13, 5)) { ";
print "\n     var edtVar = \"@@octl" . $orderControlNumber . "}{@@orl#" . $lineNumber . "}{@@bln#" . $relNumber . "}{@@item" . $itemNumber . "}{@@whs@" . $whsNumber . "}{@@ndec" . $qtyNbrDec . "}{@@covr" . $creditCodeOvr . "\"; ";
print "\n         edtVar += \"}{@@lpid\" + LHID ; ";
print "\n         edtVar += \"}{@@_sid\" + ISID ; ";
print "\n         edtVar += \"}{@@_lot\" + LOT; ";
print "\n         edtVar += \"}{@@qtyp\" + document.getElementById(qtypfld).value ; ";
print "\n         edtVar += \"}{\"; ";
print "\n     var url = \"" . $homeURL . $phpPath . "LicPlateAllocUpd.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n     var ajaxRequest = new ajaxObject(url,updOELPWKResponse); ";
print "\n     ajaxRequest.update();  ";

print "\n     responseActiveCount++; // Add 1 ";
print "\n     document.getElementById('quickEntryMessage').innerHTML= responseActiveCount +' Updates Pending... '; ";
print "\n     document.getElementById('quickEntryMessage').style.color = '" . $errorBackground . "'; ";

print "\n   } ";
print "\n } ";

// Update Quantity Posted in OESLWK Response
print "\n function updOELPWKResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";        // print "\n alert('response'+response);";
print "\n     var LHID=response[6]; ";
print "\n     var ISID=response[7]; ";
print "\n     var LOT=response[8]; ";
print "\n     var qtypfld=\"qtyp\"+LHID+\"_\"+ISID+\"_\"+LOT; ";
print "\n     var ftypfld=\"ftyp\"+LHID+\"_\"+ISID+\"_\"+LOT; ";
print "\n     var avalfld=\"aval\"+LHID+\"_\"+ISID+\"_\"+LOT; ";
print "\n     var sqtyfld=\"sqty\"+LHID+\"_\"+ISID+\"_\"+LOT; ";
print "\n     document.getElementById('LWWQTYsum').innerHTML = response[1]; ";
print "\n     document.getElementById('LWWQTYsum').title = response[1]; ";
print "\n     document.getElementById('LWWQTYvar').innerHTML = response[2]; ";
print "\n     document.getElementById('LWWQTYvar').title = response[3]; ";
print "\n     document.getElementById(qtypfld).value = response[4]; ";
print "\n     document.getElementById(ftypfld).value = response[4]; ";
print "\n     document.getElementById(avalfld).value = response[5]; ";
print "\n     document.getElementById(avalfld).title = response[5]; ";
print "\n     document.getElementById(avalfld).innerHTML = response[5]+'&nbsp;'; ";
print "\n     if (response[4] == '.00') { ";
print "\n       document.getElementById(qtypfld).value = ''; ";
print "\n       document.getElementById(sqtyfld).checked = false;} ";
print "\n     if (response[9] == 'Y') { ";
print "\n       alert('Quantity no longer available');} ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";

print "\n   responseActiveCount--; // subtract 1 ";
print "\n   if (responseActiveCount==0) { ";
print "\n     document.getElementById(\"quickEntryMessage\").innerHTML='Update Complete.'; ";
print "\n     document.getElementById(\"quickEntryMessage\").style.color = 'black'; ";
print "\n   } else {";
print "\n       document.getElementById(\"quickEntryMessage\").innerHTML=responseActiveCount+' Updates Pending... '; ";
print "\n   } ";
print "\n } ";

?>