<?php

require_once 'ApplCashPaymentEditSubCodeJava.php';
require_once 'ApplCashPaymentIconsJava.php';

if ($entryType=="") {
	// Quick Entry
	print "\n function ARQuickEntry() { ";
	print "\n   if (document.getElementById('addInvoiceNumber').value !=\"\" || ";
	print "\n       document.getElementById('addAmount').value !=\"\" || ";
	print "\n       document.getElementById('addDiscount').value !=\"\" || ";
	print "\n       document.getElementById('addPayOffSubCode').value !=\"\" ) { ";
	print "\n     if (editNum(document.getElementById('addInvoiceNumber').name, 7, 0) && ";
	print "\n         editNum(document.getElementById('addAmount').name, 11, 2) && ";
	print "\n         editNum(document.getElementById('addDiscount').name, 11, 2)) { ";
	print "\n       document.getElementById('addPayOffSubCode').value=document.getElementById('addPayOffSubCode').value.toUpperCase() ; ";
	require 'ApplCashPaymentJavaEdtVarInclude.php';
	print "\n           edtVar += \"}{@@mncd\" + \"Q\"; ";
	print "\n           edtVar += \"}{@@sseq\" + nextPESSEQ.toString(); ; ";
	print "\n           edtVar += \"}{@@crtb\" + \"I\" ; ";
	print "\n           edtVar += \"}{@@sinv\" + document.getElementById('addInvoiceNumber').value ; ";
	print "\n           edtVar += \"}{@@amt@\" + document.getElementById('addAmount').value ; ";
	print "\n           edtVar += \"}{@@damt\" + document.getElementById('addDiscount').value ; ";
	print "\n           edtVar += \"}{@@posb\" + document.getElementById('addPayOffSubCode').value ; ";
	print "\n           edtVar += \"}{\"; ";
	print "\n       var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdARPYEN.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
	print "\n       var ajaxRequest = new ajaxObject(url,ARQuickEntryResponse); ";
	print "\n       ajaxRequest.update();  ";
	require 'ApplCashPaymentJavaUpdatePendingInclude.php';
	print "\n       document.getElementById('addInvoiceNumber').value=''; ";
	print "\n       document.getElementById('addAmount').value=''; ";
	print "\n       document.getElementById('addDiscount').value=''; ";
	print "\n       setTimeout('document.getElementById(\"addInvoiceNumber\").focus()',1); ";
	print "\n     } ";
	print "\n   } ";
	print "\n   return false; ";
	print "\n } ";

	// Quick Entry Response
	print "\n function ARQuickEntryResponse(responseText, responseStatus) {  ";
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
	print "\n     document.getElementById('CEDICN').innerHTML = response[17]; ";
	print "\n     document.getElementById('CEDSAM').innerHTML = response[18]; ";
	print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
	require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
	print "\n } ";

	// Insert All Invoices based on Selection
	print "\n function AddFilterARPayment(returnUrl, sclcDefault, sdscDefault) { ";
	print "\n   var stmt = \" Insert Into ARPYEN \"; ";
	print "\n   stmt += \" (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,PECHK,PEPTYP,PEISEQ,PEENID,PEEDIT,PECRTB,PESPMT,PESCLC,PEAMT,PESDSC,PEDAMT,PESSEQ,PESBCD) \"; ";
	print "\n   stmt += \" Select {$fromBatchNumber},{$fromBatchDate},{$fromBatchBank},'{$fromType}',{$fromID},'{$fromDocument}','{$paymentType}',IVISEQ,1,'E','I','Y',\"; ";
	print "\n   if (sclcDefault==1) {stmt += \" 'Y',\";} ";
	print "\n   else                {stmt += \" ' ',\";} ";
	print "\n   if (sdscDefault==1 || (sdscDefault!=1 && sclcDefault!=1)) {stmt += \" IVIVAM-IVNPOS-IVPPOS-(Case When {$DscAmtSQL} When ABS({$InvBalSQL}) < ABS({$DscBalSQL}) Then {$InvBalSQL} Else {$DscBalSQL} End),\";} ";
	print "\n   else                                                      {stmt += \" IVIVAM-IVNPOS-IVPPOS,\";} ";
	print "\n   if (sdscDefault==1) {stmt += \" 'Y',Case When {$DscAmtSQL} When ABS({$InvBalSQL}) < ABS({$DscBalSQL}) Then {$InvBalSQL} Else {$DscBalSQL} End\";} ";
	print "\n   else                {stmt += \" ' ',0 \";} ";
	print "\n   stmt += \" ,(Select Coalesce(Max(PESSEQ),0)-(-1) From ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK))=({$fromBatchNumber},{$fromBatchDate},{$fromBatchBank},'{$fromType}',{$fromID},'" . trim($fromDocument) . "')) \"; ";
	print "\n   stmt += \" ,(Select CECSBC From ARDCEN Where (CEBCHN,CEBCHD,CEBCHB,CETYPE,CEID,trim(CECHK))=({$fromBatchNumber},{$fromBatchDate},{$fromBatchBank},'{$fromType}',{$fromID},'" . trim($fromDocument) . "')) \"; ";
	print "\n   stmt += \" From {$sv_fileSQL}\"; ";
	print "\n   stmt += \" Where {$sv_selectSQL}\"; ";
	print "\n   stmt += \" and not exists (select * from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID,PEISEQ,PEENID)=({$fromBatchNumber},{$fromBatchDate},{$fromBatchBank},'{$fromType}',{$fromID},'" . trim($fromDocument) . "','{$paymentType}','{$paymentID}',IVISEQ,1)) \"; ";
	print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
	print "\n   request = new getXMLHTTPRequest(); ";
	print "\n   request.open(\"GET\", url, false); ";
	print "\n   request.send(null); ";
	require 'ApplCashPaymentJavaEdtVarInclude.php';
	print "\n       edtVar += \"}{@@mncd\" + \"E\"; ";
	print "\n       edtVar += \"}{\"; ";
	print "\n   var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdARPYEN.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
	print "\n   request = new getXMLHTTPRequest(); ";
	print "\n   request.open(\"GET\", url, false); ";
	print "\n   request.send(null); ";
	print "\n   window.location.href=returnUrl; ";
	print "\n } ";

	// Delete All Payments based on Selection
	print "\n function DelFilterARPayment(returnUrl) { ";
	print "\n   if (confirmDelete('Payments displayed.')) { ";
	print "\n     var stmt = \" Update ARPYEN w \"; ";
	print "\n     stmt += \" Set PEEDIT='R' \"; ";
	print "\n     stmt += \" Where exists (Select * from {$sv_fileSQL} Where {$sv_selectSQL} and (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,PECHK,PEPTYP,PEPMID,PEISEQ,PEENID)=(w.PEBCHN,w.PEBCHD,w.PEBCHB,w.PETYPE,w.PEID,w.PECHK,w.PEPTYP,w.PEPMID,w.PEISEQ,w.PEENID)) \"; ";
	print "\n     var url = \"" . $homeURL . $phpPath . "RunSQLUpdate.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;dummy=\" + new Date().getTime(); ";
	print "\n     request = new getXMLHTTPRequest(); ";
	print "\n     request.open(\"GET\", url, false); ";
	print "\n     request.send(null); ";
	require 'ApplCashPaymentJavaEdtVarInclude.php';
	print "\n         edtVar += \"}{@@mncd\" + \"R\"; ";
	print "\n         edtVar += \"}{\"; ";
	print "\n     var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdARPYEN.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
	print "\n     request = new getXMLHTTPRequest(); ";
	print "\n     request.open(\"GET\", url, false); ";
	print "\n     request.send(null); ";
	print "\n     window.location.href=returnUrl; ";
	print "\n   } else { ";
	print "\n     document.getElementById('rlseAll').checked=false; ";
	print "\n   } ";
	print "\n } ";

	// Selection Check Box changed
	print "\n function editPESPMT(ISEQ,ENID,MNID,IVAINV,sclcDefault,sdscDefault) { ";
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESCLCfld=\"sclc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESDSCfld=\"sdsc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID; ";

	print "\n   if (document.getElementById(PESPMTfld).checked) { ";
	print "\n     document.getElementById(PESCLCfld).checked=sclcDefault; ";
	print "\n     document.getElementById(PESDSCfld).checked=sdscDefault; ";
	print "\n     if (document.getElementById(PESCLCfld).checked || (document.getElementById(PESDSCfld).checked===false && document.getElementById(PESCLCfld).checked===false)) { ";
	print "\n         document.getElementById(PEAMTfld).value=calcPEAMTCash(ISEQ,ENID,MNID); ";
	print "\n     } ";
	print "\n     if (document.getElementById(PESDSCfld).checked===false) { ";
	print "\n         document.getElementById(PEDAMTfld).value=''; ";
	print "\n     } ";
	print "\n     var MnCd='C'; ";
	print "\n     var PRCOLM='PESPMT' ; ";
	print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n   } else { ";
	print "\n     var IVDSCTvalue=getHiddenJavaArrayValue(\"DSCT\",ISEQ,ENID); ";
	print "\n     var PEPOSBfld=\"posb\"+ISEQ+\"_\"+ENID; ";
	print "\n     if (trim(document.getElementById(PEPOSBfld).value)!=\"\") {var deleteMsg='Payment and Payoff for invoice ' + IVAINV;} ";
	print "\n     else                                                      {var deleteMsg='Payment for invoice ' + IVAINV;} ";
	if ($applCashPaymentDeletePrompt=="Y") {
 		print "\n     if (confirmDelete(deleteMsg)) { ";
	}
	print "\n       document.getElementById(PESCLCfld).checked=false; ";
	print "\n       document.getElementById(PESDSCfld).checked=false; ";
	print "\n       document.getElementById(PEAMTfld).value=''; ";
	print "\n       document.getElementById(PEDAMTfld).value=IVDSCTvalue; ";
	print "\n       var oldPECMNTfld=\"oldcmt\"+ISEQ+\"_\"+ENID; document.getElementById(oldPECMNTfld).innerHTML=''; ";
	print "\n       var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+ENID; document.getElementById(newPECMNTfld).value=''; ";
	print "\n       var cmtIconfld=\"cmt\"+ISEQ+\"_\"+ENID;      document.getElementById(cmtIconfld).innerHTML= \"{$commentImageURL}\" ; ";
	print "\n       var MnCd='D'; ";
	print "\n       var PRCOLM='PESPMT' ; ";
	print "\n       updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	if ($applCashPaymentDeletePrompt=="Y") {
		print "\n     } else { ";
		print "\n       document.getElementById(PESPMTfld).checked=true; ";
		print "\n     } ";
	}
	print "\n   } ";
	print "\n } ";

	// Calculate Check Box changed
	print "\n function editPESCLCCash(ISEQ,ENID,MNID,sdscDefault) { ";
	print "\n   var PESCLCfld=\"sclc\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESCLCfld).checked) { ";
	print "\n     var IVBALNvalue=getHiddenJavaArrayValue(\"BALN\",ISEQ,ENID); ";
	print "\n     var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n     var PESDSCfld=\"sdsc\"+ISEQ+\"_\"+ENID; ";
	print "\n     var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";
	print "\n     var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID; ";
	print "\n     if (document.getElementById(PESPMTfld).checked===false) { ";
	print "\n         document.getElementById(PESDSCfld).checked=sdscDefault; ";
	print "\n         document.getElementById(PESPMTfld).checked=true; ";
	print "\n         if (document.getElementById(PESDSCfld).checked===false) { ";
	print "\n             document.getElementById(PEDAMTfld).value=''; ";
	print "\n         } ";
	print "\n     } ";
	print "\n     var PEAMTvalue=IVBALNvalue-document.getElementById(PEDAMTfld).value; ";
	print "\n     if (PEAMTvalue.toFixed) {PEAMTvalue=PEAMTvalue.toFixed(2);} ";
	print "\n     else                    {PEAMTvalue=(Math.round(100*PEAMTvalue)/100);} ";
	print "\n     document.getElementById(PEAMTfld).value=PEAMTvalue; ";
	print "\n     var MnCd='C'; ";
	print "\n     var PRCOLM='PESPMT' ; ";
	print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n   } ";
	print "\n } ";

	// Discount Check Box changed
	print "\n function editPESDSCCash(ISEQ,ENID,MNID,sclcDefault) { ";
	print "\n   var IVBALNvalue=getHiddenJavaArrayValue(\"BALN\",ISEQ,ENID); ";
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESCLCfld=\"sclc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESDSCfld=\"sdsc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESDSCfld).checked) { ";
	print "\n       if (document.getElementById(PESPMTfld).checked===false) { ";
	print "\n           document.getElementById(PESCLCfld).checked=sclcDefault; ";
	print "\n           document.getElementById(PESPMTfld).checked=true; ";
	print "\n       } ";
	print "\n   } else { ";
	print "\n       document.getElementById(PEDAMTfld).value=''; ";
	print "\n   } ";
	print "\n   if (document.getElementById(PESCLCfld).checked) { ";
	print "\n       var PEAMTvalue=IVBALNvalue-document.getElementById(PEDAMTfld).value; ";
	print "\n       if (PEAMTvalue.toFixed) {PEAMTvalue=PEAMTvalue.toFixed(2);} ";
	print "\n       else                    {PEAMTvalue=(Math.round(100*PEAMTvalue)/100);} ";
	print "\n       document.getElementById(PEAMTfld).value=PEAMTvalue; ";
	print "\n   } ";
	print "\n   var MnCd='C'; ";
	print "\n   var PRCOLM='PESPMT' ; ";
	print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n } ";

	// Payment Amount changed
	print "\n function editPEAMTCash(ISEQ,ENID,MNID,sdscDefault) { ";
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESCLCfld=\"sclc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESDSCfld=\"sdsc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID; ";
	print "\n   document.getElementById(PESCLCfld).checked=false; ";
	print "\n   if (document.getElementById(PESPMTfld).checked===false) { ";
	print "\n       document.getElementById(PESDSCfld).checked=sdscDefault; ";
	print "\n       document.getElementById(PESPMTfld).checked=true; ";
	print "\n       if (document.getElementById(PESDSCfld).checked===false) { ";
	print "\n           document.getElementById(PEDAMTfld).value=''; ";
	print "\n       } ";
	print "\n   } ";
	print "\n   var MnCd='C'; ";
	print "\n   var PRCOLM='PESPMT' ; ";
	print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n } ";

	// Discount changed
	print "\n function editPEDAMTCash(ISEQ,ENID,MNID,sclcDefault) { ";
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESCLCfld=\"sclc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESDSCfld=\"sdsc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID; ";
	print "\n   document.getElementById(PESDSCfld).checked=true; ";
	print "\n   if (document.getElementById(PESPMTfld).checked===false) { ";
	print "\n       document.getElementById(PESCLCfld).checked=sclcDefault; ";
	print "\n       document.getElementById(PESPMTfld).checked=true; ";
	print "\n   } ";
	print "\n   if (document.getElementById(PESCLCfld).checked) { ";
	print "\n       document.getElementById(PEAMTfld).value=calcPEAMTCash(ISEQ,ENID,MNID); ";
	print "\n   } ";
	print "\n   var MnCd='C'; ";
	print "\n   var PRCOLM='PESPMT' ; ";
	print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n } ";

	// Pay Off Payment Code changed
	print "\n function editPEPOSBCash(ISEQ,ENID,MNID,sclcDefault,sdscDefault) { ";
	print "\n   var PEPOSBfld=\"posb\"+ISEQ+\"_\"+ENID; ";
	print "\n   document.getElementById(PEPOSBfld).value=document.getElementById(PEPOSBfld).value.toUpperCase() ; ";
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESCLCfld=\"sclc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PESDSCfld=\"sdsc\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID; ";

	print "\n   if (document.getElementById(PESPMTfld).checked===false) { ";
	print "\n     document.getElementById(PESPMTfld).checked=true; ";
	print "\n     document.getElementById(PESCLCfld).checked=sclcDefault; ";
	print "\n     document.getElementById(PESDSCfld).checked=sdscDefault; ";
	print "\n     if (document.getElementById(PESCLCfld).checked || (document.getElementById(PESDSCfld).checked===false && document.getElementById(PESCLCfld).checked===false)) { ";
	print "\n         document.getElementById(PEAMTfld).value=calcPEAMTCash(ISEQ,ENID,MNID); ";
	print "\n     } ";
	print "\n     if (document.getElementById(PESDSCfld).checked===false) { ";
	print "\n         document.getElementById(PEDAMTfld).value=''; ";
	print "\n     } ";
	print "\n   } ";
	print "\n   var MnCd='C'; ";
	print "\n   var PRCOLM='PESPMT' ; ";
	print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n } ";

	// Calculate Payment Amount
	print "\n function calcPEAMTCash(ISEQ,ENID,MNID) { ";
	print "\n   var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (editNum(document.getElementById(PEDAMTfld).name, 11, 2)) { ";
	print "\n     var IVBALNvalue=getHiddenJavaArrayValue(\"BALN\",ISEQ,ENID); ";
	print "\n     var PESCLCfld=\"sclc\"+ISEQ+\"_\"+ENID; ";
	print "\n     var PESDSCfld=\"sdsc\"+ISEQ+\"_\"+ENID; ";
	print "\n     if (document.getElementById(PESDSCfld).checked || (document.getElementById(PESDSCfld).checked===false && document.getElementById(PESCLCfld).checked===false)) { ";
	print "\n         var PENETB=IVBALNvalue-document.getElementById(PEDAMTfld).value; ";
	print "\n     } else { ";
	print "\n         var PENETB=IVBALNvalue; ";
	print "\n     } ";
	print "\n     if (PENETB.toFixed) {PENETB=PENETB.toFixed(2);} ";
	print "\n     else                {PENETB=(Math.round(100*PENETB)/100);} ";
	print "\n     return(PENETB); ";
	print "\n   } else { ";
	print "\n       return(0); ";
	print "\n   } ";
	print "\n } ";
}

if ($entryType=="E") {
	// Delete Payment (selected Delete icon)
	print "\n function delARPYENLine(ISEQ,ENID,MNID,IECRTB) { ";
	print "\n   var MnCd='D'; ";
	print "\n   var PRCOLM='PESPMT'; ";
	print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n   var Rowfld=\"row\"+ISEQ+\"_\"+ENID; ";
	print "\n   document.getElementById('paymentTable').deleteRow(document.getElementById(Rowfld).rowIndex); ";
	print "\n } ";
}

// Update Payment in ARPYEN
print "\n function updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM) { ";
print "\n   var IVISEQvalue=getHiddenJavaArrayValue(\"ISEQ\",ISEQ,ENID); ";
print "\n   var PEENIDvalue=getHiddenJavaArrayValue(\"ENID\",ISEQ,ENID); ";
print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
print "\n   var PESCLCfld=\"sclc\"+ISEQ+\"_\"+ENID;  ";
print "\n   var PESDSCfld=\"sdsc\"+ISEQ+\"_\"+ENID;  ";
print "\n   var PESINVfld=\"sinv\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEAMTfld =\"amt\"+ISEQ+\"_\"+ENID   ; ";
print "\n   var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID   ; ";
print "\n   var PEPOSBfld=\"posb\"+ISEQ+\"_\"+ENID; ";

if ($entryType=="") {
	print "\n   if (editNum(document.getElementById(PEAMTfld).name, 11, 2) && ";
	print "\n       editNum(document.getElementById(PEDAMTfld).name, 11, 2)) { ";
	print "\n     if (document.getElementById(PESPMTfld).checked) {var PESPMT='Y';} ";
	print "\n     else                                            {var PESPMT=' ';} ";
	print "\n     if (document.getElementById(PESCLCfld).checked) {var PESCLC='Y';} ";
	print "\n     else                                            {var PESCLC=' ';} ";
	print "\n     if (document.getElementById(PESDSCfld).checked) {var PESDSC='Y';} ";
	print "\n     else                                            {var PESDSC=' ';} ";
} else {
	print "\n   if (editNum(document.getElementById(PESINVfld).name, 7, 0) && ";
	print "\n       editNum(document.getElementById(PEAMTfld).name, 11, 2) && ";
	print "\n       editNum(document.getElementById(PEDAMTfld).name, 11, 2)) { ";
}
print "\n     disableEntryFld(PESINVfld,\"Y\"); ";
print "\n     disableEntryFld(PESPMTfld,\"Y\"); ";
print "\n     disableEntryFld(PESCLCfld,\"Y\"); ";
print "\n     disableEntryFld(PESDSCfld,\"Y\"); ";
print "\n     disableEntryFld(PEAMTfld,\"Y\"); ";
print "\n     disableEntryFld(PEDAMTfld,\"Y\"); ";
print "\n     disableEntryFld(PEPOSBfld,\"Y\"); ";

require 'ApplCashPaymentJavaEdtVarInclude.php';
print "\n         edtVar += \"}{@@mncd\" + MnCd; ";
print "\n         edtVar += \"}{@@_isq\" + ISEQ ; ";
print "\n         edtVar += \"}{@@_eid\" + ENID; ";
print "\n         edtVar += \"}{@@sseq\" + nextPESSEQ.toString() ; ";
print "\n         edtVar += \"}{@@iseq\" + IVISEQvalue.toString() ; ";
print "\n         edtVar += \"}{@@enid\" + PEENIDvalue.toString() ; ";
print "\n         edtVar += \"}{@@colm\" + PRCOLM ; ";
print "\n         edtVar += \"}{@@crtb\" + \"I\" ; ";
if ($entryType=="") {
	print "\n         edtVar += \"}{@@spmt\" + PESPMT ; ";
	print "\n         edtVar += \"}{@@sclc\" + PESCLC ; ";
	print "\n         edtVar += \"}{@@sdsc\" + PESDSC ; ";
	print "\n         if (PRCOLM==\"PESPMT\") { ";
	print "\n           edtVar += \"}{@@amt@\" + document.getElementById(PEAMTfld).value ; ";
	print "\n           edtVar += \"}{@@damt\" + document.getElementById(PEDAMTfld).value ; ";
	print "\n           edtVar += \"}{@@posb\" + document.getElementById(PEPOSBfld).value ; ";
	print "\n         } ";
} else {
	print "\n         if (PRCOLM==\"PESINV\") {edtVar += \"}{@@sinv\" + document.getElementById(PESINVfld).value ;} ";
}
print "\n         if (PRCOLM==\"PEAMT\")  {edtVar += \"}{@@amt@\" + document.getElementById(PEAMTfld).value ;} ";
print "\n         if (PRCOLM==\"PEDAMT\") {edtVar += \"}{@@damt\" + document.getElementById(PEDAMTfld).value ;} ";
print "\n         if (PRCOLM==\"PEPOSB\") {edtVar += \"}{@@posb\" + document.getElementById(PEPOSBfld).value ;} ";
print "\n         edtVar += \"}{\"; ";
print "\n     var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdARPYEN.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n     var ajaxRequest = new ajaxObject(url,updARPYENResponse); ";
print "\n     ajaxRequest.update();  ";
require 'ApplCashPaymentJavaUpdatePendingInclude.php';
if ($entryType=="") {
	print "\n     if (MnCd == 'D') {setHiddenJavaArrayValue(\"ENID\",ISEQ,ENID,0);} ";
	print "\n     else             {setHiddenJavaArrayValue(\"ENID\",ISEQ,ENID,1);} ";
}
print "\n   } ";
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
print "\n     document.getElementById('CEDICN').innerHTML = response[17]; ";
print "\n     document.getElementById('CEDSAM').innerHTML = response[18]; ";
print "\n     var MnCd=response[19]; ";
print "\n     var PRCOLM=response[20]; ";
print "\n     var ISEQ=response[21]; ";
print "\n     var ENID=response[22]; ";
print "\n     var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";
print "\n     if (document.getElementById(PEAMTfld)) {";
print "\n       var PENETBfld=\"netb\"+ISEQ+\"_\"+ENID; if (document.getElementById(PENETBfld)) {document.getElementById(PENETBfld).innerHTML = response[25]; } ";
print "\n       var PEPOAMfld=\"poam\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEPOAMfld)) {document.getElementById(PEPOAMfld).innerHTML = response[26]; } ";
print "\n       var PEPOSBfld=\"posb\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEPOSBfld)) {document.getElementById(PEPOSBfld).value = response[27]; enableEntryFld(PEPOSBfld); } ";
print "\n       var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
if ($entryType=="") {
	print "\n       setHiddenJavaArrayValue(\"ISEQ\",ISEQ,ENID,response[23]); ";
	print "\n       setHiddenJavaArrayValue(\"ENID\",ISEQ,ENID,response[24]); ";
	print "\n       var ICONfld=\"icon\"+ISEQ+\"_\"+ENID; ";
	print "\n       if (document.getElementById(ICONfld)) { ";
	print "\n         if (document.getElementById(PESPMTfld).checked) {showSel(ICONfld);} ";
	print "\n         else                                            {hideSel(ICONfld);} ";
	print "\n       }; ";
}
print "\n       enableEntryFld(PESPMTfld); ";
print "\n       enableEntryFld(PEAMTfld); ";
print "\n       var PESCLCfld=\"sclc\"+ISEQ+\"_\"+ENID; enableEntryFld(PESCLCfld); ";
print "\n       var PESDSCfld=\"sdsc\"+ISEQ+\"_\"+ENID; enableEntryFld(PESDSCfld); ";
print "\n       var PESINVfld=\"sinv\"+ISEQ+\"_\"+ENID; enableEntryFld(PESINVfld); ";
print "\n       var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID; enableEntryFld(PEDAMTfld); ";

print "\n       if (MnCd!=\"D\") {";
print "\n         var ERRS=response[28]; ";
print "\n         var r=29; ";
print "\n         if (ERRS > 0) { ";
print "\n           for (var i=0; i<ERRS; i++) { ";
print "\n             var PRCOLM=response[r]; ";
print "\n             var ERRD=response[r+1]; ";
print "\n             setColumnError(PRCOLM,ERRD,ISEQ,ENID); ";
print "\n             r=r+2 ; ";
print "\n           }; ";
print "\n         }; ";
print "\n       }; ";
print "\n     }; ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
print "\n } ";

?>