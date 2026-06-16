<?php

// assign defaults values
print "\n var responseActiveCount=0; ";
// activate event to check before window is unloaded
print "\n window.onbeforeunload = testResponseActive; ";

// test to see if there are any active requests
print "\n function testResponseActive() { ";
print "\n   var testReponseActiveMsg=\"\"; ";
print "\n   if (responseActiveCount>0) { ";
print "\n     var testReponseActiveMsg = \"There are \"+ responseActiveCount + \" updates pending which may NOT complete if OK is taken.\"; ";
print "\n     return testReponseActiveMsg ; ";
print "\n   } ";
print "\n } ";

// Quick Entry
print "\n function QuickEntry() { ";
print "\n   if (document.getElementById('addCustomer').value !=\"\") { ";
print "\n     if (editNum(document.getElementById('addCustomer').name, 7, 0)) { ";
print "\n       var edtVar  = \"@@payr" . $fromPayer . "\"; ";
print "\n           edtVar += \"}{@@mncd\" + \"Q\"; ";
print "\n           edtVar += \"}{@@cust\" + document.getElementById('addCustomer').value ; ";
print "\n           edtVar += \"}{\"; ";
print "\n       var url = \"" . $homeURL . $phpPath . "PayerCustomerUpdARPYRC.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n       var ajaxRequest = new ajaxObject(url,QuickEntryResponse); ";
print "\n       ajaxRequest.update();  ";

print "\n       responseActiveCount++; // Add 1 ";
print "\n       document.getElementById('quickEntryMessage').innerHTML = responseActiveCount +' Updates Pending... '; ";
print "\n       document.getElementById('quickEntryMessage').style.color = '" . $errorBackground . "'; ";

print "\n       document.getElementById('addCustomer').value=''; ";
print "\n       setTimeout('document.getElementById(\"addCustomer\").focus()',1); ";
print "\n     } ";
print "\n   } ";
print "\n   return false; ";
print "\n } ";

// Quick Entry Response
print "\n function QuickEntryResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
print "\n     var MnCd=response[1]; ";
print "\n     var RowAdd=response[2]; ";
print "\n     var PCCUST=response[3]; var Rowfld=\"row\"+PCCUST; ";
print "\n     if (RowAdd=='E') { ";
print "\n       var ERERDS=response[4];  ";
print "\n       document.getElementById('addCustomer').value=PCCUST; ";
print "\n       document.getElementById('addCustomer').focus(); ";
print "\n       document.getElementById('addCustomerError').innerHTML=ERERDS; ";
print "\n     } else if (RowAdd=='Y') {  ";
print "\n       document.getElementById('addCustomerError').innerHTML=''; ";
print "\n       var CMCNA1=response[5];  ";
print "\n       var CMCNA2=response[6];  ";
print "\n       var CMCCTY=response[7];  ";
print "\n       var CMST  =response[8];  ";
print "\n       var CMZIP =response[9];  ";
print "\n       var CMPHON=response[10];  ";
print "\n       addRow('selTable',Rowfld,PCCUST,CMCNA1,CMCNA2,CMCCTY,CMST,CMZIP,CMPHON,'{$homeURL}{$imagePath}smDelete.gif') ";
print "\n     } ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";

print "\n   responseActiveCount--; // subtract 1 ";
print "\n   if (responseActiveCount==0) { ";
print "\n     document.getElementById(\"quickEntryMessage\").innerHTML='Update Complete.'; ";
print "\n     document.getElementById(\"quickEntryMessage\").style.color = 'black'; ";
print "\n   } else {";
print "\n     document.getElementById(\"quickEntryMessage\").innerHTML = responseActiveCount+' Updates Pending... '; ";
print "\n   } ";
print "\n } ";

// Delete Row (selected Delete icon)
print "\n function delARPYRCLine(PCCUST) { ";
print "\n   var MnCd='D'; ";
print "\n   var edtVar = \"@@payr" . $fromPayer . "\"; ";
print "\n       edtVar += \"}{@@mncd\" + MnCd; ";
print "\n       edtVar += \"}{@@cust\" + PCCUST ; ";
print "\n       edtVar += \"}{\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "PayerCustomerUpdARPYRC.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   var ajaxRequest = new ajaxObject(url,delARPYRCLineResponse); ";
print "\n   ajaxRequest.update();  ";

print "\n   responseActiveCount++; // Add 1 ";
print "\n   document.getElementById('quickEntryMessage').innerHTML = responseActiveCount +' Updates Pending... '; ";
print "\n   document.getElementById('quickEntryMessage').style.color = '" . $errorBackground . "'; ";
print "\n } ";

// Delete Row Response
print "\n function delARPYRCLineResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
print "\n     var MnCd=response[1]; ";
print "\n     var RowAdd=response[2]; ";
print "\n     if (RowAdd!='E') { ";
print "\n       var PCCUST=response[3]; var Rowfld=\"row\"+PCCUST; ";
print "\n       document.getElementById('selTable').deleteRow(document.getElementById(Rowfld).rowIndex); ";
print "\n     } ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";

print "\n   responseActiveCount--; // subtract 1 ";
print "\n   if (responseActiveCount==0) { ";
print "\n     document.getElementById(\"quickEntryMessage\").innerHTML='Update Complete.'; ";
print "\n     document.getElementById(\"quickEntryMessage\").style.color = 'black'; ";
print "\n   } else {";
print "\n     document.getElementById(\"quickEntryMessage\").innerHTML=responseActiveCount+' Updates Pending... '; ";
print "\n   } ";
print "\n } ";

?>

var count = "1";
function addRow(tblName,Rowfld,PCCUST,CMCNA1,CMCNA2,CMCCTY,CMST,CMZIP,CMPHON,delimg) {
  if (document.getElementById(Rowfld)) {alert(CNA1 + ' already selected'); return;}
  
  var tbody = document.getElementById(tblName).getElementsByTagName("TBODY")[0];
  // create row
  var row = document.createElement("TR");
  row.setAttribute('id',Rowfld);

  // create table cell 0 (Delete icon)
  var td0 = document.createElement("TD")
  td0.setAttribute('class','colopt');
  td0.setAttribute('className','colopt'); // For IE
  var img = document.createElement('IMG');
  img.setAttribute('src', delimg);
  img.setAttribute('title', 'Remove row');
  img.onclick = function(){if(confirmDelete(CMCNA1)) {delARPYRCLine(PCCUST)};}
  td0.appendChild(img);

  // create table cell 1 (Customer)
  var td1 = document.createElement("TD")
  td1.setAttribute('class','colnmbr');
  td1.setAttribute('className','colnmbr'); // For IE
  var strHtml1 = PCCUST ;
  td1.innerHTML = strHtml1.replace(/!count!/g,count);

  // create table cell 2 (Name)
  var td2 = document.createElement("TD")
  td2.setAttribute('class','colalph');
  td2.setAttribute('className','colalph'); // For IE
  var strHtml2 = CMCNA1 ;
  td2.innerHTML = strHtml2.replace(/!count!/g,count);

  // create table cell 3 (adress)
  var td3 = document.createElement("TD")
  td3.setAttribute('class','colalph');
  td3.setAttribute('className','colalph'); // For IE
  var strHtml3 = CMCNA2 ;
  td3.innerHTML = strHtml3.replace(/!count!/g,count);

  // create table cell 4 (city)
  var td4 = document.createElement("TD")
  td4.setAttribute('class','colalph');
  td4.setAttribute('className','colalph'); // For IE
  var strHtml4 = CMCCTY ;
  td4.innerHTML = strHtml4.replace(/!count!/g,count);

  // create table cell 5 (state)
  var td5 = document.createElement("TD")
  td5.setAttribute('class','colcode');
  td5.setAttribute('className','colcode'); // For IE
  var strHtml5 = CMST ;
  td5.innerHTML = strHtml5.replace(/!count!/g,count);

  // create table cell 6 (zip)
  var td6 = document.createElement("TD")
  td6.setAttribute('class','colalph');
  td6.setAttribute('className','colalph'); // For IE
  var strHtml6 = CMZIP ;
  td6.innerHTML = strHtml6.replace(/!count!/g,count);

  // create table cell 7 (phone)
  var td7 = document.createElement("TD")
  td7.setAttribute('class','colalph');
  td7.setAttribute('className','colalph'); // For IE
  var strHtml7 = CMPHON ;
  td7.innerHTML = strHtml7.replace(/!count!/g,count);

  // append data to row
  row.appendChild(td0);
  row.appendChild(td1);
  row.appendChild(td2);
  row.appendChild(td3);
  row.appendChild(td4);
  row.appendChild(td5);
  row.appendChild(td6);
  row.appendChild(td7);
  // add to count variable
  count = parseInt(count) + 1;
  // append row to table
  tbody.appendChild(row);
  return true;
} 
