<?php

require_once 'ApplCashPaymentEditSubCodeJava.php';
require_once 'ApplCashPaymentIconsJava.php';

if ($entryType=="") {
	// Quick Entry
	print "\n function ARQuickEntry() { ";
	print "\n   if (document.getElementById('addInvoiceNumber').value !=\"\" || ";
	print "\n       document.getElementById('addAmount').value !=\"\" || ";
	print "\n       document.getElementById('addPmtCode').value !=\"\" || ";
	print "\n       document.getElementById('addCompany').value !=\"\" || ";
	print "\n       document.getElementById('addFacility').value !=\"\" || ";
	print "\n       document.getElementById('addAccount').value !=\"\" || ";
	print "\n       document.getElementById('addSubaccount').value !=\"\" || ";
	print "\n       document.getElementById('addMemo').value !=\"\") { ";
	print "\n     if (editNum(document.getElementById('addInvoiceNumber').name, 7, 0) && ";
	print "\n         editNum(document.getElementById('addAmount').name, 11, 2) && ";
	print "\n         editNum(document.getElementById('addCompany').name, 2, 0) && ";
	print "\n         editNum(document.getElementById('addFacility').name, 4, 0) && ";
	print "\n         editNum(document.getElementById('addAccount').name, 4, 0) && ";
	print "\n         editNum(document.getElementById('addSubaccount').name, 4, 0)) { ";
	require 'ApplCashPaymentJavaEdtVarInclude.php';
	print "\n           edtVar += \"}{@@mncd\" + \"Q\"; ";
	print "\n           edtVar += \"}{@@sseq\" + nextPESSEQ.toString() ; ";
	print "\n           edtVar += \"}{@@sinv\" + document.getElementById('addInvoiceNumber').value ; ";
	print "\n           edtVar += \"}{@@amt@\" + document.getElementById('addAmount').value ; ";
	print "\n           edtVar += \"}{@@sbcd\" + document.getElementById('addPmtCode').value ; ";
	print "\n           edtVar += \"}{@@memo\" + document.getElementById('addMemo').value ; ";
	print "\n           edtVar += \"}{@@ofco\" + document.getElementById('addCompany').value ; ";
	print "\n           edtVar += \"}{@@offc\" + document.getElementById('addFacility').value ; ";
	print "\n           edtVar += \"}{@@ofac\" + document.getElementById('addAccount').value ; ";
	print "\n           edtVar += \"}{@@ofsb\" + document.getElementById('addSubaccount').value ; ";
	print "\n           edtVar += \"}{\"; ";
	print "\n       var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdARPYEN.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
	print "\n       var ajaxRequest = new ajaxObject(url,ARQuickEntryResponse); ";
	print "\n       ajaxRequest.update();  ";
	require 'ApplCashPaymentJavaUpdatePendingInclude.php';
	print "\n       document.getElementById('addInvoiceNumber').value=''; ";
	print "\n       document.getElementById('addAmount').value=''; ";
	print "\n       document.getElementById('addPmtCode').value=''; ";
	print "\n       document.getElementById('addMemo').value=''; ";
	print "\n       document.getElementById('addCompany').value=''; ";
	print "\n       document.getElementById('addFacility').value=''; ";
	print "\n       document.getElementById('addAccount').value=''; ";
	print "\n       document.getElementById('addSubaccount').value=''; ";
	print "\n       setTimeout('document.getElementById(\"addInvoiceNumber\").focus()',1); ";
	print "\n     } ";
	print "\n   } ";
	print "\n   return false; ";
	print "\n } ";

	// Quick Entry Response
	print "\n function ARQuickEntryResponse(responseText, responseStatus) {  ";
	print "\n   if (responseStatus==200) { ";
	print "\n     var response= responseText.split(\"|\"); ";
	print "\n     document.getElementById('otherEntry').innerHTML = response[1]; ";
	print "\n     document.getElementById('otherBalance').innerHTML = response[2]; ";
	print "\n     document.getElementById('OTHERCNT').innerHTML = response[3]; ";
	print "\n     document.getElementById('OTHERAMT').innerHTML = response[4]; ";
	print "\n     document.getElementById('OTHERVAR').innerHTML = response[5]; ";
	print "\n     document.getElementById('CEJICN').innerHTML = response[6]; ";
	print "\n     document.getElementById('CEJSAM').innerHTML = response[7]; ";
	print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
	require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
	print "\n } ";

	// Insert All Invoices based on Selection
	print "\n function AddFilterARPayment(returnUrl) { ";
	print "\n   var stmt = \" Insert Into ARPYEN \"; ";
	print "\n   stmt += \" (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,PECHK,PEPTYP,PEISEQ,PEENID,PEEDIT,PECRTB,PESPMT) \"; ";
	print "\n   stmt += \" Select {$fromBatchNumber},{$fromBatchDate},{$fromBatchBank},'{$fromType}',{$fromID},'{$fromDocument}','{$paymentType}',IVISEQ,1,'E','I','Y'\"; ";
	print "\n   stmt += \" From {$sv_fileSQL}\"; ";
	print "\n   stmt += \" Where {$sv_selectSQL}\"; ";
	print "\n   stmt += \" and not exists (select * from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID,PEISEQ)=({$fromBatchNumber},{$fromBatchDate},{$fromBatchBank},'{$fromType}',{$fromID},'" . trim($fromDocument) . "','{$paymentType}','{$paymentID}',IVISEQ)) \"; ";
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

	// Add payment line (+ icon taken)
	print "\n function insertARPYENLine(ISEQ,ENID,MNID) { ";
	print "\n   var MnCd='I'; ";
	print "\n   var PRCOLM=''; ";
	print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n } ";

	// Selection Check Box changed
	print "\n function editPESPMT(ISEQ,ENID,MNID,IVAINV) { ";
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";

	print "\n   if (document.getElementById(PESPMTfld).checked) { ";
	print "\n     document.getElementById(PEAMTfld).value=calcPEAMTAdjustment(ISEQ,ENID,MNID); ";
	print "\n     var MnCd='C'; ";
	print "\n     var PRCOLM='PEAMT'; ";
	print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n   } else { ";
	print "\n     var deleteMsg='Adjustment for invoice ' + IVAINV; ";
	if ($applCashPaymentDeletePrompt=="Y") {
		print "\n     if (confirmDelete(deleteMsg)) { ";
	}
	print "\n       var MnCd='D'; ";
	print "\n       var PRCOLM=''; ";
	print "\n       updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n       document.getElementById(PEAMTfld).value=''; ";
	print "\n       var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; document.getElementById(PESBCDfld).value=''; ";
	print "\n       var PEMEMOfld=\"memo\"+ISEQ+\"_\"+ENID; document.getElementById(PEMEMOfld).value=''; ";
	print "\n       var PEOFCOfld=\"ofco\"+ISEQ+\"_\"+ENID; document.getElementById(PEOFCOfld).value=''; ";
	print "\n       var PEOFFCfld=\"offc\"+ISEQ+\"_\"+ENID; document.getElementById(PEOFFCfld).value=''; ";
	print "\n       var PEOFACfld=\"ofac\"+ISEQ+\"_\"+ENID; document.getElementById(PEOFACfld).value=''; ";
	print "\n       var PEOFSBfld=\"ofsb\"+ISEQ+\"_\"+ENID; document.getElementById(PEOFSBfld).value=''; ";
	print "\n       var oldPECMNTfld=\"oldcmt\"+ISEQ+\"_\"+ENID; document.getElementById(oldPECMNTfld).innerHTML=''; ";
	print "\n       var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+ENID; document.getElementById(newPECMNTfld).value=''; ";
	print "\n       var cmtIconfld=\"cmt\"+ISEQ+\"_\"+ENID;      document.getElementById(cmtIconfld).innerHTML= \"{$commentImageURL}\" ; ";
	if ($applCashPaymentDeletePrompt=="Y") {
		print "\n     } else { ";
		print "\n       document.getElementById(PESPMTfld).checked=true; ";
		print "\n     } ";
	}
	print "\n   } ";
	print "\n } ";

	// Payment Amount changed
	print "\n function editPEAMTAdjustment(ISEQ,ENID,MNID) { ";
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).checked===false) { ";
	print "\n       document.getElementById(PESPMTfld).checked=true; ";
	print "\n   } ";
	print "\n   var MnCd='C'; ";
	print "\n   var PRCOLM='PEAMT'; ";
	print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n } ";

	// Calculate Payment Amount
	print "\n function calcPEAMTAdjustment(ISEQ,ENID,MNID) { ";
	print "\n   var IVBALNvalue=getHiddenJavaArrayValue(\"BALN\",ISEQ,MNID) ; ";
	print "\n   if (IVBALNvalue) { ";
	print "\n     var PENETB=IVBALNvalue; ";
	print "\n     if (PENETB.toFixed) {PENETB=PENETB.toFixed(2);} ";
	print "\n     else                {PENETB=(Math.round(100*PENETB)/100);} ";
	print "\n     return(PENETB); ";
	print "\n   } else { ";
	print "\n     return(0); ";
	print "\n   } ";
	print "\n } ";
}

// Delete Payment (selected Delete icon)
print "\n function delARPYENLine(ISEQ,ENID,MNID,IECRTB) { ";
print "\n   var MnCd='D'; ";
print "\n   var PRCOLM=''; ";
print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
if ($entryType=="") {
	print "\n   if (ENID != MNID) { ";
	print "\n     var Rowfld=\"row\"+ISEQ+\"_\"+ENID; ";
	print "\n     document.getElementById('paymentTable').deleteRow(document.getElementById(Rowfld).rowIndex); ";
	print "\n   } else { ";
	print "\n     var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID   ; document.getElementById(PEAMTfld).value=''; ";
	print "\n     var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; document.getElementById(PESBCDfld).value=''; ";
	print "\n     var PEMEMOfld=\"memo\"+ISEQ+\"_\"+ENID; document.getElementById(PEMEMOfld).value=''; ";
	print "\n     var PEOFCOfld=\"ofco\"+ISEQ+\"_\"+ENID; document.getElementById(PEOFCOfld).value=''; ";
	print "\n     var PEOFFCfld=\"offc\"+ISEQ+\"_\"+ENID; document.getElementById(PEOFFCfld).value=''; ";
	print "\n     var PEOFACfld=\"ofac\"+ISEQ+\"_\"+ENID; document.getElementById(PEOFACfld).value=''; ";
	print "\n     var PEOFSBfld=\"ofsb\"+ISEQ+\"_\"+ENID; document.getElementById(PEOFSBfld).value=''; ";
	print "\n   } ";
} else {
	print "\n   var Rowfld=\"row\"+ISEQ+\"_\"+ENID; ";
	print "\n   document.getElementById('paymentTable').deleteRow(document.getElementById(Rowfld).rowIndex); ";
}
print "\n } ";

// Update Payment in ARPYEN
print "\n function updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM) { ";
print "\n   var IVISEQvalue=getHiddenJavaArrayValue(\"ISEQ\",ISEQ,ENID); ";
print "\n   var PEENIDvalue=getHiddenJavaArrayValue(\"ENID\",ISEQ,ENID); ";
print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
print "\n   var PESINVfld=\"sinv\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID   ; ";
print "\n   var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEMEMOfld=\"memo\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEOFCOfld=\"ofco\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEOFFCfld=\"offc\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEOFACfld=\"ofac\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEOFSBfld=\"ofsb\"+ISEQ+\"_\"+ENID; ";
if ($entryType=="") {
	print "\n   if (editNum(document.getElementById(PEAMTfld).name, 11, 2) && ";
} else {
	print "\n   if (editNum(document.getElementById(PESINVfld).name, 7, 0) && ";
	print "\n       editNum(document.getElementById(PEAMTfld).name, 11, 2) && ";
}
print "\n       editNum(document.getElementById(PEOFCOfld).name, 2, 0) && ";
print "\n       editNum(document.getElementById(PEOFFCfld).name, 4, 0) && ";
print "\n       editNum(document.getElementById(PEOFACfld).name, 4, 0) && ";
print "\n       editNum(document.getElementById(PEOFSBfld).name, 4, 0)) { ";

print "\n     disableEntryFld(PESINVfld,\"Y\"); ";
print "\n     disableEntryFld(PESPMTfld,\"Y\"); ";
print "\n     if (PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEAMTfld,setDisable); ";
print "\n     if (PRCOLM==\"PESBCD\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PESBCDfld,setDisable); ";
print "\n     if (PRCOLM==\"PEMEMO\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEMEMOfld,setDisable); ";
print "\n     if (PRCOLM==\"PEOFCO\" || PRCOLM==\"PEOFFC\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEOFCOfld,setDisable); disableEntryFld(PEOFFCfld,setDisable); ";
print "\n     if (PRCOLM==\"PEOFAC\" || PRCOLM==\"PEOFSB\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEOFACfld,setDisable); disableEntryFld(PEOFSBfld,setDisable); ";

print "\n     if (MnCd!='D') { ";
print "\n       if (document.getElementById(PESBCDfld)) { ";
print "\n         if (trim(document.getElementById(PESBCDfld).value)==\"\") {document.getElementById(PESBCDfld).value=document.getElementById('subCode').value;} ";
print "\n         document.getElementById(PESBCDfld).value=document.getElementById(PESBCDfld).value.toUpperCase(); ";
print "\n       } ";
print "\n       if (document.getElementById(PEMEMOfld)) {document.getElementById(PEMEMOfld).value=document.getElementById(PEMEMOfld).value.toUpperCase();} ";
print "\n     } ";

if ($entryType=="") {
	print "\n       if (ENID==MNID && MnCd==\"D\") {var addLinefld=\"addLine\"+ISEQ+\"_\"+ENID; document.getElementById(addLinefld).innerHTML='';} ";
}

require 'ApplCashPaymentJavaEdtVarInclude.php';
print "\n         edtVar += \"}{@@mncd\" + MnCd; ";
print "\n         edtVar += \"}{@@_isq\" + ISEQ ; ";
print "\n         edtVar += \"}{@@_eid\" + ENID; ";
print "\n         edtVar += \"}{@@mnid\" + MNID ; ";
print "\n         edtVar += \"}{@@sseq\" + nextPESSEQ.toString() ; ";
print "\n         edtVar += \"}{@@iseq\" + IVISEQvalue.toString() ; ";
print "\n         edtVar += \"}{@@enid\" + PEENIDvalue.toString() ; ";
print "\n         edtVar += \"}{@@colm\" + PRCOLM ; ";
print "\n         edtVar += \"}{@@sbcd\" + document.getElementById(PESBCDfld).value ; ";
print "\n         if (PRCOLM==\"PESINV\" && document.getElementById(PESINVfld)) {edtVar += \"}{@@sinv\" + document.getElementById(PESINVfld).value ;} ";
print "\n         if (PRCOLM==\"PEAMT\" && document.getElementById(PEAMTfld))  {edtVar += \"}{@@amt@\" + document.getElementById(PEAMTfld).value ;} ";
print "\n         if (PRCOLM==\"PEMEMO\" && document.getElementById(PEMEMOfld)) {edtVar += \"}{@@memo\" + document.getElementById(PEMEMOfld).value ;} ";
print "\n         if ((PRCOLM==\"PEOFCO\" || PRCOLM==\"PEOFFC\") && document.getElementById(PEOFCOfld))  {edtVar += \"}{@@ofco\" + document.getElementById(PEOFCOfld).value ;} ";
print "\n         if ((PRCOLM==\"PEOFCO\" || PRCOLM==\"PEOFFC\") && document.getElementById(PEOFFCfld))  {edtVar += \"}{@@offc\" + document.getElementById(PEOFFCfld).value ;} ";
print "\n         if ((PRCOLM==\"PEOFAC\" || PRCOLM==\"PEOFSB\") && document.getElementById(PEOFACfld))  {edtVar += \"}{@@ofac\" + document.getElementById(PEOFACfld).value ;} ";
print "\n         if ((PRCOLM==\"PEOFAC\" || PRCOLM==\"PEOFSB\") && document.getElementById(PEOFSBfld))  {edtVar += \"}{@@ofsb\" + document.getElementById(PEOFSBfld).value ;} ";
print "\n         edtVar += \"}{\"; ";
print "\n     var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdARPYEN.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n     var ajaxRequest = new ajaxObject(url,updARPYENResponse); ";
print "\n     ajaxRequest.update();  ";
require 'ApplCashPaymentJavaUpdatePendingInclude.php';
print "\n   } ";
print "\n } ";

// Update Payment in ARPYEN Response
print "\n function updARPYENResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
print "\n     document.getElementById('otherEntry').innerHTML = response[1]; ";
print "\n     document.getElementById('otherBalance').innerHTML = response[2]; ";
print "\n     document.getElementById('OTHERCNT').innerHTML = response[3]; ";
print "\n     document.getElementById('OTHERAMT').innerHTML = response[4]; ";
print "\n     document.getElementById('OTHERVAR').innerHTML = response[5]; ";
print "\n     document.getElementById('CEJICN').innerHTML = response[6]; ";
print "\n     document.getElementById('CEJSAM').innerHTML = response[7]; ";
print "\n     var MnCd=response[8]; ";
print "\n     var PRCOLM=response[9]; ";
print "\n     var ISEQ=response[10]; ";
print "\n     var ENID=response[11]; ";
print "\n     var MNID=response[12]; ";
print "\n     var PENETBfld=\"netb\"+ISEQ+\"_\"+MNID ; if (document.getElementById(PENETBfld)) {document.getElementById(PENETBfld).innerHTML = response[15];} ";
print "\n     setHiddenJavaArrayValue(\"BALN\",ISEQ,MNID,response[16]); ";
print "\n     var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";
print "\n     if (document.getElementById(PEAMTfld)) {";
print "\n       var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
if ($entryType=="") {
	print "\n       setHiddenJavaArrayValue(\"ISEQ\",ISEQ,ENID,response[13]); ";
	print "\n       setHiddenJavaArrayValue(\"ENID\",ISEQ,ENID,response[14]); ";
	print "\n       if (ENID==MNID && MnCd==\"C\") {var addLinefld=\"addLine\"+ISEQ+\"_\"+ENID; document.getElementById(addLinefld).innerHTML='<a onClick=\"insertARPYENLine(\''+ISEQ+'\',\''+ENID+'\',\''+MNID+'\')\">$applCashAddPaymentLine<\/a>';} ";
	print "\n       if (ENID==MNID && MnCd==\"I\") {var addLinefld=\"addLine\"+ISEQ+\"_\"+ENID; document.getElementById(addLinefld).style.backgroundColor = 'red';} ";
	print "\n       var ICONfld=\"icon\"+ISEQ+\"_\"+ENID; ";
	print "\n       if (document.getElementById(ICONfld)) { ";
	print "\n         if (ENID!=MNID || document.getElementById(PESPMTfld).checked) {showSel(ICONfld);} ";
	print "\n         else                                                          {hideSel(ICONfld);} ";
	print "\n       }; ";
}
print "\n       enableEntryFld(PESPMTfld); ";
print "\n       var PESINVfld=\"sinv\"+ISEQ+\"_\"+ENID; enableEntryFld(PESINVfld); ";
print "\n       if (PRCOLM==\"PEAMT\") {enableEntryFld(PEAMTfld);} ";
print "\n       if (PRCOLM==\"PESBCD\" || PRCOLM==\"PEAMT\") {var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; if (document.getElementById(PESBCDfld)) {document.getElementById(PESBCDfld).value = response[17]; enableEntryFld(PESBCDfld);}} ";
print "\n       if (PRCOLM==\"PEMEMO\" || PRCOLM==\"PEAMT\") {var PEMEMOfld=\"memo\"+ISEQ+\"_\"+ENID; enableEntryFld(PEMEMOfld);} ";
print "\n       if (PRCOLM==\"PEOFCO\" || PRCOLM==\"PEOFFC\" || PRCOLM==\"PEAMT\") { ";
print "\n         var PEOFCOfld=\"ofco\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFCOfld)) {document.getElementById(PEOFCOfld).value = response[18]; enableEntryFld(PEOFCOfld);} ";
print "\n         var PEOFFCfld=\"offc\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFFCfld)) {document.getElementById(PEOFFCfld).value = response[19]; enableEntryFld(PEOFFCfld);} ";
print "\n         if (document.getElementById(PEOFCOfld).getAttribute(\"type\")==\"hidden\") { ";
print "\n           var PEOFCOHiddenfld=\"ofcoHidden\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFCOHiddenfld)) {document.getElementById(PEOFCOHiddenfld).innerHTML = response[18];} ";
print "\n           var PEOFFCHiddenfld=\"offcHidden\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFFCHiddenfld)) {document.getElementById(PEOFFCHiddenfld).innerHTML = response[19];} ";
print "\n         } ";
print "\n       } ";
print "\n       if (PRCOLM==\"PEOFAC\" || PRCOLM==\"PEOFSB\" || PRCOLM==\"PEAMT\") { ";
print "\n         var PEOFACfld=\"ofac\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFACfld)) {document.getElementById(PEOFACfld).value = response[20]; enableEntryFld(PEOFACfld);} ";
print "\n         var PEOFSBfld=\"ofsb\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFSBfld)) {document.getElementById(PEOFSBfld).value = response[21]; enableEntryFld(PEOFSBfld);} ";
print "\n         if (document.getElementById(PEOFACfld).getAttribute(\"type\")==\"hidden\") { ";
print "\n           var PEOFACHiddenfld=\"ofacHidden\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFACHiddenfld)) {document.getElementById(PEOFACHiddenfld).innerHTML = response[20];} ";
print "\n           var PEOFSBHiddenfld=\"ofsbHidden\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFSBHiddenfld)) {document.getElementById(PEOFSBHiddenfld).innerHTML = response[21];} ";
print "\n         } ";
print "\n       } ";

print "\n       if (MnCd!=\"D\") {";
print "\n         var ERRS=response[22]; ";
print "\n         var r=23; ";
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

// Add Default Icon (icon taken)
print "\n function defaultARPaymentInfo(ISEQ,ENID,MNID) { ";
print "\n   var IVISEQvalue=getHiddenJavaArrayValue(\"ISEQ\",ISEQ,ENID) ; ";
print "\n   var PEENIDvalue=getHiddenJavaArrayValue(\"ENID\",ISEQ,ENID) ; ";

print "\n   var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; disableEntryFld(PESBCDfld,\"Y\"); ";
print "\n   var PEOFCOfld=\"ofco\"+ISEQ+\"_\"+ENID; disableEntryFld(PEOFCOfld,\"Y\"); ";
print "\n   var PEOFFCfld=\"offc\"+ISEQ+\"_\"+ENID; disableEntryFld(PEOFFCfld,\"Y\"); ";
print "\n   var PEOFACfld=\"ofac\"+ISEQ+\"_\"+ENID; disableEntryFld(PEOFACfld,\"Y\"); ";
print "\n   var PEOFSBfld=\"ofsb\"+ISEQ+\"_\"+ENID; disableEntryFld(PEOFSBfld,\"Y\"); ";

require 'ApplCashPaymentJavaEdtVarInclude.php';
print "\n       edtVar += \"}{@@mncd\" + \"V\"; ";
print "\n       edtVar += \"}{@@_isq\" + ISEQ ; ";
print "\n       edtVar += \"}{@@_eid\" + ENID; ";
print "\n       edtVar += \"}{@@mnid\" + MNID ; ";
print "\n       edtVar += \"}{@@sseq\" + nextPESSEQ.toString() ; ";
print "\n       edtVar += \"}{@@iseq\" + IVISEQvalue.toString() ; ";
print "\n       edtVar += \"}{@@enid\" + PEENIDvalue.toString() ; ";
print "\n       edtVar += \"}{\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdARPYEN.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   var ajaxRequest = new ajaxObject(url,defaultARPaymentInfoResponse); ";
print "\n   ajaxRequest.update();  ";
require 'ApplCashPaymentJavaUpdatePendingInclude.php';
print "\n } ";

// Add Default Icon Response (after Add Default icon)
print "\n function defaultARPaymentInfoResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
print "\n     var ISEQ=response[1]; ";
print "\n     var ENID=response[2]; ";
print "\n     var MNID=response[3]; ";
print "\n     var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; if (document.getElementById(PESBCDfld)) {document.getElementById(PESBCDfld).value = response[4]; enableEntryFld(PESBCDfld);} ";

print "\n     var PEOFCOfld=\"ofco\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFCOfld)) {document.getElementById(PEOFCOfld).value = response[5]; enableEntryFld(PEOFCOfld);} ";
print "\n     var PEOFFCfld=\"offc\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFFCfld)) {document.getElementById(PEOFFCfld).value = response[6]; enableEntryFld(PEOFFCfld);} ";
print "\n     if (document.getElementById(PEOFCOfld).getAttribute(\"type\")==\"hidden\") { ";
print "\n       var PEOFCOHiddenfld=\"ofcoHidden\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFCOHiddenfld)) {document.getElementById(PEOFCOHiddenfld).innerHTML = response[5];} ";
print "\n       var PEOFFCHiddenfld=\"offcHidden\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFFCHiddenfld)) {document.getElementById(PEOFFCHiddenfld).innerHTML = response[6];} ";
print "\n     } ";

print "\n     var PEOFACfld=\"ofac\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFACfld)) {document.getElementById(PEOFACfld).value = response[7]; enableEntryFld(PEOFACfld);} ";
print "\n     var PEOFSBfld=\"ofsb\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFSBfld)) {document.getElementById(PEOFSBfld).value = response[8]; enableEntryFld(PEOFSBfld);} ";
print "\n     if (document.getElementById(PEOFACfld).getAttribute(\"type\")==\"hidden\") { ";
print "\n       var PEOFACHiddenfld=\"ofacHidden\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFACHiddenfld)) {document.getElementById(PEOFACHiddenfld).innerHTML = response[7];} ";
print "\n       var PEOFSBHiddenfld=\"ofsbHidden\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEOFSBHiddenfld)) {document.getElementById(PEOFSBHiddenfld).innerHTML = response[8];} ";
print "\n     } ";

print "\n     var ERRS=response[9]; ";
print "\n     var r=10; ";
print "\n     if (ERRS > 0) { ";
print "\n       for (var i=0; i<ERRS; i++) { ";
print "\n         var PRCOLM=response[r]; ";
print "\n         var ERRD=response[r+1]; ";
print "\n         setColumnError(PRCOLM,ERRD,ISEQ,ENID); ";
print "\n         r=r+2 ; ";
print "\n       }; ";
print "\n     }; ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
print "\n } ";

?>