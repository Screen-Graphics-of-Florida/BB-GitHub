<?php
if ($CRBBAL=="Y") {$BMBCHT=RetValue("(BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank)", "ARPBCH", "BMBCHT");}
else              {$BMBCHT="";}

require_once 'ApplCashPaymentEditSubCodeJava.php';
require_once 'ApplCashPaymentIconsJava.php';

if ($entryType=="") {
	// Quick Entry
	print "\n function ARQuickEntry() { ";
	print "\n   if (document.getElementById('addInvoiceNumber').value !=\"\" || ";
	print "\n       document.getElementById('addAmount').value !=\"\" || ";
	print "\n       document.getElementById('addDedInvoice').value !=\"\" || ";
	print "\n       document.getElementById('addPmtCode').value !=\"\" || ";
	print "\n       document.getElementById('addReference').value !=\"\" || ";
	print "\n       document.getElementById('addMemo').value !=\"\" || ";
	print "\n       document.getElementById('addLocation').value !=\"\" || ";
	print "\n       document.getElementById('addBillTo').value !=\"\" || ";
	print "\n       document.getElementById('addAccount').value !=\"\" || ";
	print "\n       document.getElementById('addSubaccount').value !=\"\" || ";
	print "\n       document.getElementById('addOEOrder').value !=\"\" || ";
	print "\n       document.getElementById('addOrderDate').value !=\"\" || ";
	print "\n       document.getElementById('addOrderLine').value !=\"\" || ";
	print "\n       document.getElementById('addPlant').value !=\"\" || ";
	print "\n       document.getElementById('addMfgOrder').value !=\"\" || ";
	print "\n       document.getElementById('addSalesman').value !=\"\" || ";
	print "\n       document.getElementById('addTermsCode').value !=\"\" ) { ";
	print "\n     if (editNum(document.getElementById('addInvoiceNumber').name, 7, 0) && ";
	print "\n         editNum(document.getElementById('addAmount').name, 11, 2) && ";
	print "\n         editNum(document.getElementById('addDedInvoice').name, 7, 0) && ";
	print "\n         editNum(document.getElementById('addLocation').name, 3, 0) && ";
	print "\n         editNum(document.getElementById('addBillTo').name, 7, 0) && ";
	print "\n         editNum(document.getElementById('addAccount').name, 4, 0) && ";
	print "\n         editNum(document.getElementById('addSubaccount').name, 4, 0) && ";
	print "\n         editNum(document.getElementById('addOEOrder').name, 8, 0) && ";
	print "\n         editdate(document.getElementById('addOrderDate')) && ";
	print "\n         editNum(document.getElementById('addOrderLine').name, 3, 0) && ";
	print "\n         editNum(document.getElementById('addPlant').name, 3, 0) && ";
	print "\n         editNum(document.getElementById('addSalesman').name, 3, 0)) { ";
	require 'ApplCashPaymentJavaEdtVarInclude.php';
	print "\n           edtVar += \"}{@@mncd\" + \"Q\"; ";
	print "\n           edtVar += \"}{@@sseq\" + nextPESSEQ.toString() ; ";
	if ($columnDisplay['PEGDED']=="Y" && ($HDMCRL==0 || $CRPRMC!="Y" || $BKCURT==$CFCURT) && ($CRBBAL!="Y" || $BMBCHT=="D")) {
		print "\n           if (document.getElementById('addGenDed').checked) {edtVar += \"}{@@gdedG\";} ";
		print "\n           else                                              {edtVar += \"}{@@gded\";} ";
	}
	print "\n           edtVar += \"}{@@sinv\" + document.getElementById('addInvoiceNumber').value; ";
	print "\n           edtVar += \"}{@@amt@\" + document.getElementById('addAmount').value; ";
	print "\n           edtVar += \"}{@@ninv\" + document.getElementById('addDedInvoice').value; ";
	print "\n           edtVar += \"}{@@sbcd\" + document.getElementById('addPmtCode').value; ";
	print "\n           edtVar += \"}{@@arpo\" + document.getElementById('addReference').value; ";
	print "\n           edtVar += \"}{@@memo\" + document.getElementById('addMemo').value; ";
	print "\n           edtVar += \"}{@@loc@\" + document.getElementById('addLocation').value; ";
	print "\n           edtVar += \"}{@@blto\" + document.getElementById('addBillTo').value; ";
	print "\n           edtVar += \"}{@@arac\" + document.getElementById('addAccount').value; ";
	print "\n           edtVar += \"}{@@arsb\" + document.getElementById('addSubaccount').value; ";
	print "\n           edtVar += \"}{@@ord@\" + document.getElementById('addOEOrder').value; ";
	print "\n           edtVar += \"}{@@ordt\" + document.getElementById('addOrderDate').value; ";
	print "\n           edtVar += \"}{@@orln\" + document.getElementById('addOrderLine').value; ";
	print "\n           edtVar += \"}{@@plt@\" + document.getElementById('addPlant').value; ";
	print "\n           edtVar += \"}{@@mord\" + document.getElementById('addMfgOrder').value; ";
	print "\n           edtVar += \"}{@@slsm\" + document.getElementById('addSalesman').value; ";
	print "\n           edtVar += \"}{@@trms\" + document.getElementById('addTermsCode').value; ";
	print "\n           edtVar += \"}{\"; ";
	print "\n       var url = \"" . $homeURL . $phpPath . "ApplCashPaymentUpdARPYEN.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
	print "\n       var ajaxRequest = new ajaxObject(url,ARQuickEntryResponse); ";
	print "\n       ajaxRequest.update();  ";
	require 'ApplCashPaymentJavaUpdatePendingInclude.php';
	print "\n       document.getElementById('addInvoiceNumber').value=''; ";
	print "\n       document.getElementById('addAmount').value=''; ";
	print "\n       document.getElementById('addDedInvoice').value=''; ";
	print "\n       document.getElementById('addPmtCode').value=''; ";
	print "\n       document.getElementById('addReference').value=''; ";
	print "\n       document.getElementById('addMemo').value=''; ";
	print "\n       document.getElementById('addLocation').value=''; ";
	print "\n       document.getElementById('addBillTo').value=''; ";
	print "\n       document.getElementById('addAccount').value=''; ";
	print "\n       document.getElementById('addSubaccount').value=''; ";
	print "\n       document.getElementById('addOEOrder').value=''; ";
	print "\n       document.getElementById('addOrderDate').value=''; ";
	print "\n       document.getElementById('addOrderLine').value=''; ";
	print "\n       document.getElementById('addPlant').value=''; ";
	print "\n       document.getElementById('addMfgOrder').value=''; ";
	print "\n       document.getElementById('addSalesman').value=''; ";
	print "\n       document.getElementById('addTermsCode').value=''; ";
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
	print "\n     document.getElementById('depositBalance').innerHTML = response[2]; ";
	print "\n     document.getElementById('CASHCNT').innerHTML = response[3]; ";
	print "\n     document.getElementById('CASHAMT').innerHTML = response[4]; ";
	print "\n     document.getElementById('CASHVAR').innerHTML = response[5]; ";
	print "\n     document.getElementById('OTHERCNT').innerHTML = response[6]; ";
	print "\n     document.getElementById('CEDGIC').innerHTML = response[7]; ";
	print "\n     document.getElementById('CEDGAM').innerHTML = response[8]; ";
	print "\n     document.getElementById('CEDICN').innerHTML = response[9]; ";
	print "\n     document.getElementById('CEDSAM').innerHTML = response[10]; ";
	print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
	require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
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
	print "\n     document.getElementById(PEAMTfld).value=calcPEAMTDeduct(ISEQ,ENID,MNID); ";
	print "\n     var MnCd='C'; ";
	print "\n     var PRCOLM='PEAMT'; ";
	print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n   } else { ";
	print "\n     var deleteMsg='Deduction for invoice ' + IVAINV; ";
	if ($applCashPaymentDeletePrompt=="Y") {
		print "\n     if (confirmDelete(deleteMsg)) { ";
	}
	print "\n       var MnCd='D'; ";
	print "\n       var PRCOLM=''; ";
	print "\n       updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n       document.getElementById(PEAMTfld).value=''; ";
	print "\n       var PENINVfld=\"ninv\"+ISEQ+\"_\"+ENID; document.getElementById(PENINVfld).value=''; ";
	print "\n       var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; document.getElementById(PESBCDfld).value=''; ";
	print "\n       var PEMEMOfld=\"memo\"+ISEQ+\"_\"+ENID; document.getElementById(PEMEMOfld).value=''; ";
	print "\n       var PEARPOfld=\"arpo\"+ISEQ+\"_\"+ENID; document.getElementById(PEARPOfld).value=''; ";
	print "\n       var PELOCfld=\"loc\"+ISEQ+\"_\"+ENID; document.getElementById(PELOCfld).value=''; ";
	print "\n       var PEARACfld=\"arac\"+ISEQ+\"_\"+ENID; document.getElementById(PEARACfld).value=''; ";
	print "\n       var PEARSBfld=\"arsb\"+ISEQ+\"_\"+ENID; document.getElementById(PEARSBfld).value=''; ";
	print "\n       var PEBLTOfld=\"blto\"+ISEQ+\"_\"+ENID; document.getElementById(PEBLTOfld).value=''; ";
	print "\n       var PEORDfld=\"ord\"+ISEQ+\"_\"+ENID; document.getElementById(PEORDfld).value=''; ";
	print "\n       var PEORDTfld=\"ordt\"+ISEQ+\"_\"+ENID; document.getElementById(PEORDTfld).value=''; ";
	print "\n       var PEORLNfld=\"orln\"+ISEQ+\"_\"+ENID; document.getElementById(PEORLNfld).value=''; ";
	print "\n       var PEPLTfld=\"plt\"+ISEQ+\"_\"+ENID; document.getElementById(PEPLTfld).value=''; ";
	print "\n       var PEMORDfld=\"mord\"+ISEQ+\"_\"+ENID; document.getElementById(PEMORDfld).value=''; ";
	print "\n       var PESLSMfld=\"slsm\"+ISEQ+\"_\"+ENID; document.getElementById(PESLSMfld).value=''; ";
	print "\n       var PETRMSfld=\"trms\"+ISEQ+\"_\"+ENID; document.getElementById(PETRMSfld).value=''; ";
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
	print "\n function editPEAMTDeduct(ISEQ,ENID,MNID) { ";
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).checked===false) { ";
	print "\n       document.getElementById(PESPMTfld).checked=true; ";
	print "\n   } ";
	print "\n   var MnCd='C'; ";
	print "\n   var PRCOLM='PEAMT'; ";
	print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
	print "\n } ";

	// Calculate Payment Amount
	print "\n function calcPEAMTDeduct(ISEQ,ENID,MNID) { ";
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
	print "\n   if (IECRTB==\"A\" || ENID != MNID) { ";
	print "\n     var Rowfld=\"row\"+ISEQ+\"_\"+ENID; ";
	print "\n     document.getElementById('paymentTable').deleteRow(document.getElementById(Rowfld).rowIndex); ";
	print "\n   } else { ";
	print "\n     var PENINVfld=\"ninv\"+ISEQ+\"_\"+ENID; document.getElementById(PENINVfld).value=''; ";
	print "\n     var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; document.getElementById(PESBCDfld).value=''; ";
	print "\n     var PEMEMOfld=\"memo\"+ISEQ+\"_\"+ENID; document.getElementById(PEMEMOfld).value=''; ";
	print "\n     var PEARPOfld=\"arpo\"+ISEQ+\"_\"+ENID; document.getElementById(PEARPOfld).value=''; ";
	print "\n     var PELOCfld=\"loc\"+ISEQ+\"_\"+ENID; document.getElementById(PELOCfld).value=''; ";
	print "\n     var PEBLTOfld=\"blto\"+ISEQ+\"_\"+ENID; document.getElementById(PEBLTOfld).value=''; ";
	print "\n     var PEARACfld=\"arac\"+ISEQ+\"_\"+ENID; document.getElementById(PEARACfld).value=''; ";
	print "\n     var PEARSBfld=\"arsb\"+ISEQ+\"_\"+ENID; document.getElementById(PEARSBfld).value=''; ";
	print "\n     var PEORDfld=\"ord\"+ISEQ+\"_\"+ENID; document.getElementById(PEORDfld).value=''; ";
	print "\n     var PEORDTfld=\"ordt\"+ISEQ+\"_\"+ENID; document.getElementById(PEORDTfld).value=''; ";
	print "\n     var PEORLNfld=\"orln\"+ISEQ+\"_\"+ENID; document.getElementById(PEORLNfld).value=''; ";
	print "\n     var PEPLTfld=\"plt\"+ISEQ+\"_\"+ENID; document.getElementById(PEPLTfld).value=''; ";
	print "\n     var PEMORDfld=\"mord\"+ISEQ+\"_\"+ENID; document.getElementById(PEMORDfld).value=''; ";
	print "\n     var PESLSMfld=\"slsm\"+ISEQ+\"_\"+ENID; document.getElementById(PESLSMfld).value=''; ";
	print "\n     var PETRMSfld=\"trms\"+ISEQ+\"_\"+ENID; document.getElementById(PETRMSfld).value=''; ";
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
print "\n   var PECRTBvalue=getHiddenJavaArrayValue(\"CRTB\",ISEQ,ENID); ";
print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
print "\n   var PESINVfld=\"sinv\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEGDEDfld=\"gded\"+ISEQ+\"_\"+ENID; ";
print "\n   var PENINVfld=\"ninv\"+ISEQ+\"_\"+ENID; ";
print "\n   var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEMEMOfld=\"memo\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEARPOfld=\"arpo\"+ISEQ+\"_\"+ENID; ";
print "\n   var PELOCfld=\"loc\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEBLTOfld=\"blto\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEARACfld=\"arac\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEARSBfld=\"arsb\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEORDfld=\"ord\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEORDTfld=\"ordt\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEORLNfld=\"orln\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEPLTfld=\"plt\"+ISEQ+\"_\"+ENID; ";
print "\n   var PEMORDfld=\"mord\"+ISEQ+\"_\"+ENID; ";
print "\n   var PESLSMfld=\"slsm\"+ISEQ+\"_\"+ENID; ";
print "\n   var PETRMSfld=\"trms\"+ISEQ+\"_\"+ENID; ";

if ($entryType=="") {
	print "\n   if (editNum(document.getElementById(PEAMTfld).name, 11, 2) && ";
} else {
	print "\n   if (editNum(document.getElementById(PESINVfld).name, 7, 0) && ";
	print "\n       editNum(document.getElementById(PEAMTfld).name, 11, 2) && ";
}
print "\n       editNum(document.getElementById(PENINVfld).name, 7, 0) && ";
print "\n       editNum(document.getElementById(PELOCfld).name, 3, 0) && ";
print "\n       editNum(document.getElementById(PEBLTOfld).name, 7, 0) && ";
print "\n       editNum(document.getElementById(PEARACfld).name, 4, 0) && ";
print "\n       editNum(document.getElementById(PEARSBfld).name, 4, 0) && ";
print "\n       editNum(document.getElementById(PEORDfld).name, 8, 0) && ";
print "\n       editdate(document.getElementById(PEORDTfld)) && ";
print "\n       editNum(document.getElementById(PEORLNfld).name, 3, 0) && ";
print "\n       editNum(document.getElementById(PEPLTfld).name, 3, 0) && ";
print "\n       editNum(document.getElementById(PESLSMfld).name, 3, 0)) { ";

print "\n     disableEntryFld(PESINVfld,\"Y\"); ";
print "\n     disableEntryFld(PESPMTfld,\"Y\"); ";
print "\n     if (PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEAMTfld,setDisable); disableEntryFld(PEGDEDfld,setDisable);";
print "\n     if (PRCOLM==\"PENINV\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PENINVfld,setDisable); ";
print "\n     if (PRCOLM==\"PESBCD\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PESBCDfld,setDisable); ";
print "\n     if (PRCOLM==\"PEMEMO\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEMEMOfld,setDisable); ";
print "\n     if (PRCOLM==\"PEARPO\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEARPOfld,setDisable); ";
print "\n     if (PRCOLM==\"PELOC\"  || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PELOCfld,setDisable); ";
print "\n     if (PRCOLM==\"PEBLTO\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEBLTOfld,setDisable); ";
print "\n     if (PRCOLM==\"PEARAC\" || PRCOLM==\"PEARSB\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEARACfld,setDisable); disableEntryFld(PEARSBfld,setDisable); ";
print "\n     if (PRCOLM==\"PEORD\"  || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEORDfld,setDisable); ";
print "\n     if (PRCOLM==\"PEORDT\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEORDTfld,setDisable); ";
print "\n     if (PRCOLM==\"PEORLN\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEORLNfld,setDisable); ";
print "\n     if (PRCOLM==\"PEPLT\"  || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEPLTfld,setDisable); ";
print "\n     if (PRCOLM==\"PEMORD\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PEMORDfld,setDisable); ";
print "\n     if (PRCOLM==\"PESLSM\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PESLSMfld,setDisable); ";
print "\n     if (PRCOLM==\"PETRMS\" || PRCOLM==\"PEAMT\") {var setDisable=\"Y\"} else {setDisable=\"\";} disableEntryFld(PETRMSfld,setDisable); ";

print "\n     if (MnCd!='D') { ";
print "\n       if (document.getElementById(PESBCDfld)) { ";
print "\n         if (trim(document.getElementById(PESBCDfld).value)==\"\") {document.getElementById(PESBCDfld).value=document.getElementById('subCode').value;} ";
print "\n         document.getElementById(PESBCDfld).value=document.getElementById(PESBCDfld).value.toUpperCase(); ";
print "\n       } ";
print "\n       if (document.getElementById(PEMEMOfld)) {document.getElementById(PEMEMOfld).value=document.getElementById(PEMEMOfld).value.toUpperCase();} ";
print "\n       if (document.getElementById(PEARPOfld)) {document.getElementById(PEARPOfld).value=document.getElementById(PEARPOfld).value.toUpperCase();} ";
print "\n       if (document.getElementById(PEMORDfld)) {document.getElementById(PEMORDfld).value=document.getElementById(PEMORDfld).value.toUpperCase();} ";
print "\n       if (document.getElementById(PETRMSfld)) {document.getElementById(PETRMSfld).value=document.getElementById(PETRMSfld).value.toUpperCase();} ";
print "\n     } ";

if ($entryType=="") {
	print "\n       if (ENID==MNID && MnCd==\"D\" && PECRTBvalue==\"I\") {var addLinefld=\"addLine\"+ISEQ+\"_\"+ENID; if (document.getElementById(addLinefld)) {document.getElementById(addLinefld).innerHTML='';}} ";
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
print "\n         edtVar += \"}{@@crtb\" + PECRTBvalue ; ";
print "\n         if (document.getElementById(PEGDEDfld) && document.getElementById(PEGDEDfld).checked) {var PEGDED='G';} ";
print "\n         else                                                                                  {var PEGDED=' ';} ";
print "\n         edtVar += \"}{@@gded\" + PEGDED ; ";
print "\n         if (document.getElementById(PENINVfld)) {edtVar += \"}{@@ninv\" + document.getElementById(PENINVfld).value;} ";
print "\n         if (document.getElementById(PESBCDfld)) {edtVar += \"}{@@sbcd\" + document.getElementById(PESBCDfld).value;} ";
print "\n         if (PRCOLM==\"PESINV\" && document.getElementById(PESINVfld)) {edtVar += \"}{@@sinv\" + document.getElementById(PESINVfld).value;} ";
print "\n         if (PRCOLM==\"PEAMT\"  && document.getElementById(PEAMTfld))   {edtVar += \"}{@@amt@\" + document.getElementById(PEAMTfld).value;} ";
print "\n         if (PRCOLM==\"PEMEMO\" && document.getElementById(PEMEMOfld))  {edtVar += \"}{@@memo\" + document.getElementById(PEMEMOfld).value;} ";
print "\n         if (PRCOLM==\"PEARPO\" && document.getElementById(PEARPOfld))  {edtVar += \"}{@@arpo\" + document.getElementById(PEARPOfld).value;} ";
print "\n         if (PRCOLM==\"PELOC\"  && document.getElementById(PELOCfld))   {edtVar += \"}{@@loc@\" + document.getElementById(PELOCfld).value;} ";
print "\n         if (PRCOLM==\"PEBLTO\" && document.getElementById(PEBLTOfld))  {edtVar += \"}{@@blto\" + document.getElementById(PEBLTOfld).value;} ";
print "\n         if (PRCOLM==\"PEARAC\" || PRCOLM==\"PEARSB\" && document.getElementById(PEARACfld))  {edtVar += \"}{@@arac\" + document.getElementById(PEARACfld).value;} ";
print "\n         if (PRCOLM==\"PEARAC\" || PRCOLM==\"PEARSB\" && document.getElementById(PEARSBfld))  {edtVar += \"}{@@arsb\" + document.getElementById(PEARSBfld).value;} ";
print "\n         if (PRCOLM==\"PEORD\"  && document.getElementById(PEORDfld))   {edtVar += \"}{@@ord@\" + document.getElementById(PEORDfld).value;} ";
print "\n         if (PRCOLM==\"PEORDT\" && document.getElementById(PEORDTfld))  {edtVar += \"}{@@ordt\" + document.getElementById(PEORDTfld).value;} ";
print "\n         if (PRCOLM==\"PEORLN\" && document.getElementById(PEORLNfld))  {edtVar += \"}{@@orln\" + document.getElementById(PEORLNfld).value;} ";
print "\n         if (PRCOLM==\"PEPLT\"  && document.getElementById(PEPLTfld))   {edtVar += \"}{@@plt@\" + document.getElementById(PEPLTfld).value;} ";
print "\n         if (PRCOLM==\"PEMORD\" && document.getElementById(PEMORDfld))  {edtVar += \"}{@@mord\" + document.getElementById(PEMORDfld).value;} ";
print "\n         if (PRCOLM==\"PESLSM\" && document.getElementById(PESLSMfld))  {edtVar += \"}{@@slsm\" + document.getElementById(PESLSMfld).value;} ";
print "\n         if (PRCOLM==\"PETRMS\" && document.getElementById(PETRMSfld))  {edtVar += \"}{@@trms\" + document.getElementById(PETRMSfld).value;} ";
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
print "\n     document.getElementById('depositEntry').innerHTML = response[1]; ";
print "\n     document.getElementById('depositBalance').innerHTML = response[2]; ";
print "\n     document.getElementById('CASHCNT').innerHTML = response[3]; ";
print "\n     document.getElementById('CASHAMT').innerHTML = response[4]; ";
print "\n     document.getElementById('CASHVAR').innerHTML = response[5]; ";
print "\n     document.getElementById('OTHERCNT').innerHTML = response[6]; ";
print "\n     document.getElementById('CEDGIC').innerHTML = response[7]; ";
print "\n     document.getElementById('CEDGAM').innerHTML = response[8]; ";
print "\n     document.getElementById('CEDICN').innerHTML = response[9]; ";
print "\n     document.getElementById('CEDSAM').innerHTML = response[10]; ";
print "\n     var MnCd=response[11]; ";
print "\n     var PRCOLM=response[12]; ";
print "\n     var ISEQ=response[13]; ";
print "\n     var ENID=response[14]; ";
print "\n     var MNID=response[15]; ";
print "\n     setHiddenJavaArrayValue(\"ISEQ\",ISEQ,ENID,response[16]); ";
print "\n     setHiddenJavaArrayValue(\"ENID\",ISEQ,ENID,response[17]); ";
print "\n     var PECRTBvalue=getHiddenJavaArrayValue(\"CRTB\",ISEQ,ENID); ";
print "\n     var PENETBfld=\"netb\"+ISEQ+\"_\"+MNID ; if (document.getElementById(PENETBfld)) {document.getElementById(PENETBfld).innerHTML = response[18]; } ";
print "\n     setHiddenJavaArrayValue(\"BALN\",ISEQ,MNID,response[19]); ";
print "\n     var Rowfld=\"row\"+ISEQ+\"_\"+ENID; ";
print "\n     var PEGDEDfld=\"gded\"+ISEQ+\"_\"+ENID; ";
print "\n     if (MnCd==\"D\" && document.getElementById(Rowfld) && document.getElementById(PEGDEDfld) && document.getElementById(PEGDEDfld).checked) {";
print "\n       document.getElementById('paymentTable').deleteRow(document.getElementById(Rowfld).rowIndex); ";
print "\n     } ";
print "\n     var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID; ";
print "\n     if (document.getElementById(PEAMTfld)) {";
print "\n       var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
if ($entryType=="") {
	print "\n       if (ENID==MNID && MnCd==\"C\" && PECRTBvalue==\"I\") {var addLinefld=\"addLine\"+ISEQ+\"_\"+ENID; if (document.getElementById(addLinefld)) {document.getElementById(addLinefld).innerHTML='<a onClick=\"insertARPYENLine(\''+ISEQ+'\',\''+ENID+'\',\''+MNID+'\')\">$applCashAddPaymentLine<\/a>';}} ";
	print "\n       if (ENID==MNID && MnCd==\"I\")                       {var addLinefld=\"addLine\"+ISEQ+\"_\"+ENID; if (document.getElementById(addLinefld)) {document.getElementById(addLinefld).style.backgroundColor = 'red';}} ";
	print "\n       var ICONfld=\"icon\"+ISEQ+\"_\"+ENID; ";
	print "\n       if (document.getElementById(ICONfld)) { ";
	print "\n         if (ENID!=MNID || document.getElementById(PESPMTfld).checked) {showSel(ICONfld);} ";
	print "\n         else                                                          {hideSel(ICONfld);} ";
	print "\n       }; ";
}
print "\n       enableEntryFld(PESPMTfld); ";
print "\n       var PESINVfld=\"sinv\"+ISEQ+\"_\"+ENID; enableEntryFld(PESINVfld); ";
print "\n       if (PRCOLM==\"PEAMT\") {enableEntryFld(PEAMTfld); enableEntryFld(PEGDEDfld);} ";
print "\n       if (PRCOLM==\"PENINV\" || PRCOLM==\"PEAMT\") {var PENINVfld=\"ninv\"+ISEQ+\"_\"+ENID; if (document.getElementById(PENINVfld)) {document.getElementById(PENINVfld).value = response[20]; enableEntryFld(PENINVfld);}} ";
print "\n       if (PRCOLM==\"PESBCD\" || PRCOLM==\"PEAMT\") {var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; if (document.getElementById(PESBCDfld)) {document.getElementById(PESBCDfld).value = response[21]; enableEntryFld(PESBCDfld);}} ";
print "\n       if (PRCOLM==\"PEMEMO\" || PRCOLM==\"PEAMT\") {var PEMEMOfld=\"memo\"+ISEQ+\"_\"+ENID; enableEntryFld(PEMEMOfld);} ";
print "\n       if (PRCOLM==\"PEARPO\" || PRCOLM==\"PEAMT\") {var PEARPOfld=\"arpo\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEARPOfld)) {document.getElementById(PEARPOfld).value = response[22]; enableEntryFld(PEARPOfld);}} ";
print "\n       if (PRCOLM==\"PELOC\"  || PRCOLM==\"PEAMT\") {var PELOCfld=\"loc\"+ISEQ+\"_\"+ENID;   if (document.getElementById(PELOCfld)) {document.getElementById(PELOCfld).value = response[23]; enableEntryFld(PELOCfld);}} ";
print "\n       if (PRCOLM==\"PEARAC\" || PRCOLM==\"PEARSB\" || PRCOLM==\"PEAMT\") {var PEARACfld=\"arac\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEARACfld)) {document.getElementById(PEARACfld).value = response[24]; enableEntryFld(PEARACfld);}} ";
print "\n       if (PRCOLM==\"PEARAC\" || PRCOLM==\"PEARSB\" || PRCOLM==\"PEAMT\") {var PEARSBfld=\"arsb\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEARSBfld)) {document.getElementById(PEARSBfld).value = response[25]; enableEntryFld(PEARSBfld);}} ";
print "\n       if (PRCOLM==\"PEBLTO\" || PRCOLM==\"PEAMT\") {var PEBLTOfld=\"blto\"+ISEQ+\"_\"+ENID; enableEntryFld(PEBLTOfld);} ";
print "\n       if (PRCOLM==\"PEORD\"  || PRCOLM==\"PEAMT\") {var PEORDfld=\"ord\"+ISEQ+\"_\"+ENID;   if (document.getElementById(PEORDfld)) {document.getElementById(PEORDfld).value = response[26]; enableEntryFld(PEORDfld);}} ";
print "\n       if (PRCOLM==\"PEORDT\" || PRCOLM==\"PEAMT\") {var PEORDTfld=\"ordt\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEORDTfld)) {document.getElementById(PEORDTfld).value = response[27]; enableEntryFld(PEORDTfld);}} ";
print "\n       if (PRCOLM==\"PEORLN\" || PRCOLM==\"PEAMT\") {var PEORLNfld=\"orln\"+ISEQ+\"_\"+ENID; enableEntryFld(PEORLNfld);} ";
print "\n       if (PRCOLM==\"PEPLT\"  || PRCOLM==\"PEAMT\") {var PEPLTfld=\"plt\"+ISEQ+\"_\"+ENID; enableEntryFld(PEPLTfld);} ";
print "\n       if (PRCOLM==\"PEMORD\" || PRCOLM==\"PEAMT\") {var PEMORDfld=\"mord\"+ISEQ+\"_\"+ENID; enableEntryFld(PEMORDfld);} ";
print "\n       if (PRCOLM==\"PESLSM\" || PRCOLM==\"PEAMT\") {var PESLSMfld=\"slsm\"+ISEQ+\"_\"+ENID; if (document.getElementById(PESLSMfld)) {document.getElementById(PESLSMfld).value = response[28]; enableEntryFld(PESLSMfld);}} ";
print "\n       if (PRCOLM==\"PETRMS\" || PRCOLM==\"PEAMT\") {var PETRMSfld=\"trms\"+ISEQ+\"_\"+ENID; if (document.getElementById(PETRMSfld)) {document.getElementById(PETRMSfld).value = response[29]; enableEntryFld(PETRMSfld);}} ";

print "\n       if (MnCd!=\"D\") {";
print "\n         var ERRS=response[30]; ";
print "\n         var r=31; ";
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
print "\n   var IVISEQvalue=getHiddenJavaArrayValue(\"ISEQ\",ISEQ,ENID); ";
print "\n   var PEENIDvalue=getHiddenJavaArrayValue(\"ENID\",ISEQ,ENID); ";
print "\n   var PECRTBvalue=getHiddenJavaArrayValue(\"CRTB\",ISEQ,ENID); ";

print "\n   var PENINVfld=\"ninv\"+ISEQ+\"_\"+ENID; disableEntryFld(PENINVfld,\"Y\"); ";
print "\n   var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; disableEntryFld(PESBCDfld,\"Y\"); ";
print "\n   var PEARPOfld=\"arpo\"+ISEQ+\"_\"+ENID; disableEntryFld(PEARPOfld,\"Y\"); ";
print "\n   var PELOCfld =\"loc\"+ISEQ+\"_\"+ENID;  disableEntryFld(PELOCfld,\"Y\"); ";
print "\n   var PEARACfld=\"arac\"+ISEQ+\"_\"+ENID; disableEntryFld(PEARACfld,\"Y\"); ";
print "\n   var PEARSBfld=\"arsb\"+ISEQ+\"_\"+ENID; disableEntryFld(PEARSBfld,\"Y\"); ";
print "\n   var PEORDfld =\"ord\"+ISEQ+\"_\"+ENID;  disableEntryFld(PEORDfld,\"Y\"); ";
print "\n   var PEORDTfld=\"ordt\"+ISEQ+\"_\"+ENID; disableEntryFld(PEORDTfld,\"Y\"); ";
print "\n   var PESLSMfld=\"slsm\"+ISEQ+\"_\"+ENID; disableEntryFld(PESLSMfld,\"Y\"); ";
print "\n   var PETRMSfld=\"trms\"+ISEQ+\"_\"+ENID; disableEntryFld(PETRMSfld,\"Y\"); ";

require 'ApplCashPaymentJavaEdtVarInclude.php';
print "\n       edtVar += \"}{@@mncd\" + \"V\"; ";
print "\n       edtVar += \"}{@@_isq\" + ISEQ ; ";
print "\n       edtVar += \"}{@@_eid\" + ENID; ";
print "\n       edtVar += \"}{@@mnid\" + MNID ; ";
print "\n       edtVar += \"}{@@sseq\" + nextPESSEQ.toString() ; ";
print "\n       edtVar += \"}{@@iseq\" + IVISEQvalue.toString() ; ";
print "\n       edtVar += \"}{@@enid\" + PEENIDvalue.toString() ; ";
print "\n       edtVar += \"}{@@crtb\" + PECRTBvalue ; ";
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
print "\n     var PENINVfld=\"ninv\"+ISEQ+\"_\"+ENID; if (document.getElementById(PENINVfld)) {document.getElementById(PENINVfld).value = response[4]; enableEntryFld(PENINVfld);} ";
print "\n     var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID; if (document.getElementById(PESBCDfld)) {document.getElementById(PESBCDfld).value = response[5]; enableEntryFld(PESBCDfld);} ";
print "\n     var PEARPOfld=\"arpo\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEARPOfld)) {document.getElementById(PEARPOfld).value = response[6]; enableEntryFld(PEARPOfld);} ";
print "\n     var PELOCfld =\"loc\"+ISEQ+\"_\"+ENID ; if (document.getElementById(PELOCfld))  {document.getElementById(PELOCfld).value = response[7];  enableEntryFld(PELOCfld);} ";
print "\n     var PEARACfld=\"arac\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEARACfld)) {document.getElementById(PEARACfld).value = response[8]; enableEntryFld(PEARACfld)} ";
print "\n     var PEARSBfld=\"arsb\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEARSBfld)) {document.getElementById(PEARSBfld).value = response[9]; enableEntryFld(PEARSBfld)} ";
print "\n     var PEORDfld =\"ord\"+ISEQ+\"_\"+ENID ; if (document.getElementById(PEORDfld))  {document.getElementById(PEORDfld).value = response[10]; enableEntryFld(PEORDfld)} ";
print "\n     var PEORDTfld=\"ordt\"+ISEQ+\"_\"+ENID; if (document.getElementById(PEORDTfld)) {document.getElementById(PEORDTfld).value = response[11]; enableEntryFld(PEORDTfld)} ";
print "\n     var PESLSMfld=\"slsm\"+ISEQ+\"_\"+ENID; if (document.getElementById(PESLSMfld)) {document.getElementById(PESLSMfld).value = response[12]; enableEntryFld(PESLSMfld)} ";
print "\n     var PETRMSfld=\"trms\"+ISEQ+\"_\"+ENID; if (document.getElementById(PETRMSfld)) {document.getElementById(PETRMSfld).value = response[13]; enableEntryFld(PETRMSfld)} ";

print "\n     var ERRS=response[14]; ";
print "\n     var r=15; ";
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