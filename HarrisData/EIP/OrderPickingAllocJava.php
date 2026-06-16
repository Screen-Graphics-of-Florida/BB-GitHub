<?php
print "\n var responseActiveCount=0; ";
require_once 'RetValueAjax.php';

// Quantity Picked Check Box changed
print "\n function checkQtyPosted(ISID,TLOT,LOT) { ";
print "\n   var qtypfld=\"qtyp_\"+ISID+\"_\"+TLOT; ";
print "\n   var sqtyfld=\"sqty_\"+ISID+\"_\"+TLOT; ";
print "\n   var avalfld=\"aval_\"+ISID+\"_\"+TLOT; ";
print "\n   var confirmQty=true";
//print "\n   if ((parseFloat(document.getElementById('maxQty').getAttribute('title')) < parseFloat(document.getElementById(avalfld).getAttribute('title')) + parseFloat(document.getElementById('QSQTYsum').getAttribute('title'))) && document.getElementById(sqtyfld).checked) { ";
//print "\n       confirm('Quantity Picked (' + parseFloat(document.getElementById('QSQTYsum').getAttribute('title')) +  ') + Selected Quantity (' + parseFloat(document.getElementById(avalfld).getAttribute('title')) + ') is greater than ' + document.getElementById('maxDesc').getAttribute('title') + ' (' + parseFloat(document.getElementById('maxQty').getAttribute('title')) + ')');  var confirmQty = false;  ";
//print "\n   } ";
print "\n   if (confirmQty==true) { ";
print "\n     if (document.getElementById(sqtyfld).checked) { ";
print "\n       if (parseFloat(document.getElementById('QSQTYvar').getAttribute('title')) < parseFloat(document.getElementById(avalfld).getAttribute('title'))) { ";
print "\n         document.getElementById(qtypfld).value=document.getElementById('QSQTYvar').getAttribute('title'); ";
print "\n       } else { ";
print "\n         document.getElementById(qtypfld).value=document.getElementById(avalfld).getAttribute('title'); ";
print "\n       } ";
print "\n     } else { ";
print "\n         document.getElementById(qtypfld).value=''; ";
print "\n     } ";
print "\n     updOEOPQS(ISID,TLOT,LOT); ";
print "\n   } else {document.getElementById(sqtyfld).checked=false;} ";
print "\n } ";

// Quantity Picked changed
print "\n function chgQtyPosted(ISID,TLOT,LOT) { ";
print "\n   var qtypfld=\"qtyp_\"+ISID+\"_\"+TLOT; ";
print "\n   var ftypfld=\"ftyp_\"+ISID+\"_\"+TLOT; ";
print "\n   var sqtyfld=\"sqty_\"+ISID+\"_\"+TLOT; ";
print "\n   var avalfld=\"aval_\"+ISID+\"_\"+TLOT; ";
print "\n   var confirmQty=true";
//print "\n alert('lot='+TLOT);";
//print "\n alert('avail='+parseFloat(document.getElementById(avalfld).getAttribute('title')));";

print "\n   if (TLOT != '') { ";
print "\n     if (parseFloat(document.getElementById(qtypfld).value) > parseFloat(document.getElementById(avalfld).getAttribute('title'))) { ";
print "\n       confirm('Quantity Picked (' + +parseFloat(document.getElementById(qtypfld).value) + ') is greater than Available ' + document.getElementById(avalfld).getAttribute('title'));  var confirmQty = false;  ";
print "\n     } ";
//print "\n     if ((parseFloat(document.getElementById('maxQty').getAttribute('title')) < parseFloat(document.getElementById(qtypfld).value) - parseFloat(document.getElementById(ftypfld).value) + parseFloat(document.getElementById('LWWQTYsum').getAttribute('title')))) { ";
//print "\n       confirm('Quantity Allocated (' + parseFloat(document.getElementById('LWWQTYsum').getAttribute('title')) +  ') + Selected Quantity (' + parseFloat(document.getElementById(qtypfld).value) + ') is greater than ' + document.getElementById('maxDesc').getAttribute('title') + ' (' + parseFloat(document.getElementById('maxQty').getAttribute('title')) + ')');  var confirmQty = false;  ";
//print "\n     } ";
print "\n   } ";
print "\n   if (confirmQty==true) { ";
print "\n   if ('{$orderPickingQtyCheckbox}' != 'N') {";
print "\n     if (document.getElementById(qtypfld).value==0) { ";
print "\n         document.getElementById(sqtyfld).checked=false; ";
print "\n     } else { ";
print "\n         document.getElementById(sqtyfld).checked=true; ";
print "\n     } ";
print "\n   } ";
print "\n     updOEOPQS(ISID,TLOT,LOT); ";
print "\n   } else {document.getElementById(qtypfld).value='' && '{$orderPickingQtyCheckbox}' != 'N';  ";
print "\n           document.getElementById(sqtyfld).checked=false;} ";
print "\n } ";


// Update Quantity Picked in OEOPQS
print "\n function updOEOPQS(ISID,TLOT,LOT) { ";
print "\n   var qtypfld=\"qtyp_\"+ISID+\"_\"+TLOT   ; ";
print "\n   if (editNum(document.getElementById(qtypfld).name, 13, 5)) { ";
print "\n     var edtVar = \"@@item" . $itemNumber . "}{@@whs@" . $whsNumber . "}{@@ndec" . $qtyNbrDec . "}{@@covr" . $creditCodeOvr . "\"; ";
print "\n         edtVar += \"}{@@_sid\" + ISID ; ";
print "\n         edtVar += \"}{@@_lot\" + LOT; ";
print "\n         edtVar += \"}{@@qtyp\" + document.getElementById(qtypfld).value ; ";
print "\n         edtVar += \"}{\"; ";
print "\n     var url = \"" . $homeURL . $phpPath . "OrderPickingAllocUpd.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n     var ajaxRequest = new ajaxObject(url,updOEOPQSResponse); ";
print "\n     ajaxRequest.update();  ";

print "\n     responseActiveCount++; // Add 1 ";
print "\n     document.getElementById('quickEntryMessage').innerHTML= responseActiveCount +' Updates Pending... '; ";
print "\n     document.getElementById('quickEntryMessage').style.color = '" . $errorBackground . "'; ";

print "\n   } ";
print "\n } ";

// Update Quantity Posted in OEOPQS Response
print "\n function updOEOPQSResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";        // print "\n alert('response'+response);";
print "\n     var ISID=response[6]; ";
print "\n     var LOT=response[7]; ";
print "\n     var qtypfld=\"qtyp_\"+ISID+\"_\"+LOT; ";
print "\n     var ftypfld=\"ftyp_\"+ISID+\"_\"+LOT; ";
print "\n     var avalfld=\"aval_\"+ISID+\"_\"+LOT; ";
print "\n     var sqtyfld=\"sqty_\"+ISID+\"_\"+LOT; ";
print "\n     document.getElementById('QSQTYsum').innerHTML = response[1]; ";
print "\n     document.getElementById('QSQTYsum').title = response[1]; ";
print "\n     document.getElementById('QSQTYvar').innerHTML = response[2]; ";
print "\n     document.getElementById('QSQTYvar').title = response[3]; ";
print "\n     document.getElementById(qtypfld).value = response[4]; ";
print "\n     document.getElementById(ftypfld).value = response[4]; ";
print "\n     document.getElementById(avalfld).value = response[5]; ";
print "\n     document.getElementById(avalfld).title = response[5]; ";
print "\n     document.getElementById(avalfld).innerHTML = response[5]+'&nbsp;'; ";
print "\n     if (response[4] == '.00') { ";
print "\n       document.getElementById(qtypfld).value = ''; ";
print "\n       if ('{$orderPickingQtyCheckbox}' != 'N') {";
print "\n           document.getElementById(sqtyfld).checked = false;}} ";
print "\n     if (response[8] == 'Y') { ";
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