<?php
print "\n var responseActiveCount=0; ";
require_once 'RetValueAjax.php';

// Quantity Posted Check Box changed
print "\n function checkQtyPosted(ITEM,WHS,TLOT,LOT) { ";
print "\n   var qtypfld=\"qtyp\"+ITEM+\"_\"+WHS+\"_\"+TLOT; ";
print "\n   var sqtyfld=\"sqty\"+ITEM+\"_\"+WHS+\"_\"+TLOT; ";
print "\n   var avalfld=\"aval\"+ITEM+\"_\"+WHS+\"_\"+TLOT; ";
print "\n   var confirmQty=true";
print "\n   if (confirmQty==true) { ";
print "\n     if (document.getElementById(sqtyfld).checked) { ";
print "\n         document.getElementById(qtypfld).value=document.getElementById(avalfld).getAttribute('title'); ";
print "\n     } else { ";
print "\n         document.getElementById(qtypfld).value=''; ";
print "\n     } ";
print "\n     updIVLPAW(ITEM,WHS,TLOT,LOT); ";
print "\n   } else {document.getElementById(sqtyfld).checked=false;} ";
print "\n } ";

// Quantity Posted changed
print "\n function chgQtyPosted(ITEM,WHS,TLOT,LOT) { ";
print "\n   var qtypfld=\"qtyp\"+ITEM+\"_\"+WHS+\"_\"+TLOT; ";
print "\n   var ftypfld=\"ftyp\"+ITEM+\"_\"+WHS+\"_\"+TLOT; ";
print "\n   var sqtyfld=\"sqty\"+ITEM+\"_\"+WHS+\"_\"+TLOT; ";
print "\n   var avalfld=\"aval\"+ITEM+\"_\"+WHS+\"_\"+TLOT; ";
print "\n   var confirmQty=true";
print "\n   if (confirmQty==true) { ";
print "\n     if (document.getElementById(qtypfld).value==0) { ";
print "\n         document.getElementById(sqtyfld).checked=false; ";
print "\n     } else { ";
print "\n         document.getElementById(sqtyfld).checked=true; ";
print "\n     } ";
print "\n     updIVLPAW(ITEM,WHS,TLOT,LOT); ";
print "\n   } else {document.getElementById(qtypfld).value='';  ";
print "\n           document.getElementById(sqtyfld).checked=false;} ";
print "\n } ";


// Update Quantity Posted in IVLPAW
print "\n function updIVLPAW(ITEM,WHS,TLOT,LOT) { ";
print "\n   var qtypfld=\"qtyp\"+ITEM+\"_\"+WHS+\"_\"+TLOT   ; ";
print "\n   if (editNum(document.getElementById(qtypfld).name, 13, 5)) { ";
print "\n     var edtVar = \"@@xhnd" . $eID . "}{@@sid@" . $sid . "}{@@ndec" . $qtyNbrDec . "}{@@covr" . $creditCodeOvr . "\"; ";
print "\n         edtVar += \"}{@@item\" + ITEM ; ";
print "\n         edtVar += \"}{@@whs@\" + WHS ; ";
print "\n         edtVar += \"}{@@_lot\" + LOT; ";
print "\n         edtVar += \"}{@@qtyp\" + document.getElementById(qtypfld).value ; ";
print "\n         edtVar += \"}{\"; ";
// print "\n alert('edtVar'+edtVar);";
print "\n     var url = \"" . $homeURL . $phpPath . "LicPlateAddUpd.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n     var ajaxRequest = new ajaxObject(url,updIVLPAWResponse); ";
print "\n     ajaxRequest.update();  ";

print "\n     responseActiveCount++; // Add 1 ";
print "\n     document.getElementById('quickEntryMessage').innerHTML= responseActiveCount +' Updates Pending... '; ";
print "\n     document.getElementById('quickEntryMessage').style.color = '" . $errorBackground . "'; ";

print "\n   } ";
print "\n } ";

// Update Quantity Posted in OESLWK Response
print "\n function updIVLPAWResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";        // print "\n alert('response'+response);";
print "\n     var ITEM=response[2]; ";
print "\n     var WHS=response[3]; ";
print "\n     var LOT=response[4]; ";
print "\n     var avalfld=\"aval\"+ITEM+\"_\"+WHS+\"_\"+LOT; ";
print "\n     var sqtyfld=\"sqty\"+ITEM+\"_\"+WHS+\"_\"+LOT; ";
print "\n     var qtypfld=\"qtyp\"+ITEM+\"_\"+WHS+\"_\"+LOT; ";
print "\n     var ftypfld=\"ftyp\"+ITEM+\"_\"+WHS+\"_\"+LOT; ";
print "\n     document.getElementById(avalfld).value = response[1]; ";
print "\n     document.getElementById(avalfld).title = response[1]; ";
print "\n     document.getElementById(avalfld).innerHTML = response[1]+'&nbsp;'; ";
print "\n     if (response[5] == 'Y') { ";
print "\n       document.getElementById(qtypfld).value = ''; ";
print "\n       document.getElementById(qtypfld).title = ''; ";
print "\n       document.getElementById(qtypfld).innerHTML = ''; ";
print "\n       document.getElementById(sqtyfld).checked = false; ";
print "\n       alert('Quantity To Assign greater than Quantity Available');";
print "\n     } ";
print "\n   } ";

print "\n   responseActiveCount--; // subtract 1 ";
print "\n   if (responseActiveCount==0) { ";
print "\n     document.getElementById(\"quickEntryMessage\").innerHTML='Update Complete.'; ";
print "\n     document.getElementById(\"quickEntryMessage\").style.color = 'black'; ";
print "\n   } else {";
print "\n       document.getElementById(\"quickEntryMessage\").innerHTML=responseActiveCount+' Updates Pending... '; ";
print "\n   } ";
print "\n } ";

?>