<?php
require_once 'ApplCashPaymentEditRvsReasonJava.php';

$commentImageURL=str_replace('"','\"',$commentImage); $commentImageURL=str_replace("'","''",$commentImageURL);
$commentExistImageNoTitleURL=str_replace('"','\"',$commentExistImageNoTitle); $commentExistImageNoTitleURL=str_replace("'","''",$commentExistImageNoTitleURL);

// Edit Column
// Selection Check Box changed
print "\n function editPESPMT(ISEQ,PSEQ,ENID,IVAINV) { ";
print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; ";
print "\n   if (document.getElementById(PESPMTfld).checked) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PESPMT' ; ";
print "\n     updARPYEN(ISEQ,PSEQ,ENID,MnCd,PRCOLM); ";
print "\n   } else { ";
print "\n     var deleteMsg='Reverse of payment for invoice ' + IVAINV; ";
if ($applCashPaymentDeletePrompt=="Y") {
	print "\n     if (confirmDelete(deleteMsg)) { ";
}
print "\n       var RHRSCDfld=\"rscd\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; ";
print "\n       document.getElementById(RHRSCDfld).value=''; ";
print "\n       var oldPECMNTfld=\"oldcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; document.getElementById(oldPECMNTfld).innerHTML=''; ";
print "\n       var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; document.getElementById(newPECMNTfld).value=''; ";
print "\n       var cmtIconfld=\"cmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID;      document.getElementById(cmtIconfld).innerHTML= \"{$commentImageURL}\" ; ";
print "\n       var MnCd='D'; ";
print "\n       var PRCOLM='PESPMT' ; ";
print "\n       updARPYEN(ISEQ,PSEQ,ENID,MnCd,PRCOLM); ";
if ($applCashPaymentDeletePrompt=="Y") {
	print "\n     } else { ";
	print "\n       document.getElementById(PESPMTfld).checked=true; ";
	print "\n     } ";
}
print "\n   } ";
print "\n } ";

// Edit and Update Reversal Reason
print "\n function editRHRSCD(ISEQ,PSEQ,ENID) { ";
print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; ";
print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
print "\n     var RHRSCDfld=\"rscd\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n     document.getElementById(RHRSCDfld).value=document.getElementById(RHRSCDfld).value.toUpperCase() ; ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='RHRSCD'; ";
print "\n     updARPYEN(ISEQ,PSEQ,ENID,MnCd,PRCOLM); ";
print "\n   }";
print "\n } ";

// Update Payment in ARPYEN
print "\n function updARPYEN(ISEQ,PSEQ,ENID,MnCd,PRCOLM) { ";
print "\n   var IVISEQvalue=getHiddenJavaArrayValueReverse(\"ISEQ\",ISEQ,PSEQ,ENID); ";
print "\n   var YPPSEQvalue=getHiddenJavaArrayValueReverse(\"PSEQ\",ISEQ,PSEQ,ENID); ";
print "\n   var PEENIDvalue=getHiddenJavaArrayValueReverse(\"ENID\",ISEQ,PSEQ,ENID); ";
print "\n   var PEPMIDvalue=getHiddenJavaArrayValueReverse(\"PMID\",ISEQ,PSEQ,ENID); ";
print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; ";
print "\n   var RHRSCDfld=\"rscd\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n   if (document.getElementById(PESPMTfld).checked) {var PESPMT='Y';} ";
print "\n   else                                            {var PESPMT=' ';} ";
print "\n   if (MnCd==\"D\") {document.getElementById(RHRSCDfld).value='';} ";
print "\n   if (MnCd!=\"D\" && trim(document.getElementById(RHRSCDfld).value)==\"\") {document.getElementById(RHRSCDfld).value=document.getElementById('rvsReason').value;} ";
print "\n   document.getElementById(RHRSCDfld).value=document.getElementById(RHRSCDfld).value.toUpperCase() ; ";
print "\n   disableEntryFld(PESPMTfld,\"Y\"); ";
print "\n   disableEntryFld(RHRSCDfld,\"Y\"); ";

print "\n   var edtVar = \"@@bchn" . $fromBatchNumber . "}{@@bchd" . $fromBatchDate . "}{@@bchb" . $fromBatchBank . "}{@@type" . $fromType . "}{@@id@@" . $fromID . "}{@@chk@" . $fromDocument . "}{@@ptyp" . $paymentType . "\"; ";
print "\n       edtVar += \"}{@@mncd\" + MnCd; ";
print "\n       edtVar += \"}{@@_isq\" + ISEQ ; ";
print "\n       edtVar += \"}{@@_psq\" + PSEQ ; ";
print "\n       edtVar += \"}{@@_eid\" + ENID; ";
print "\n       edtVar += \"}{@@sseq\" + nextPESSEQ.toString() ; ";
print "\n       edtVar += \"}{@@iseq\" + IVISEQvalue.toString() ; ";
print "\n       edtVar += \"}{@@pseq\" + YPPSEQvalue.toString() ; ";
print "\n       edtVar += \"}{@@enid\" + PEENIDvalue.toString() ; ";
print "\n       edtVar += \"}{@@pmid\" + PEPMIDvalue.toString() ; ";
print "\n       edtVar += \"}{@@colm\" + PRCOLM ; ";
print "\n       edtVar += \"}{@@crtb\" + \"I\" ; ";
print "\n       edtVar += \"}{@@spmt\" + PESPMT ; ";
print "\n       edtVar += \"}{@@rscd\" + document.getElementById(RHRSCDfld).value ; ";
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
print "\n     var PSEQ=response[30]; ";
print "\n     var ENID=response[31]; ";
print "\n     var PESPMTfld=\"spmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; ";
print "\n     var RHRSCDfld=\"rscd\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; ";
print "\n     var ICONfld=\"icon\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; ";
print "\n     var oldPECMNTfld=\"oldcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; document.getElementById(oldPECMNTfld).innerHTML=response[36]; ";
print "\n     var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; document.getElementById(newPECMNTfld).value=response[36]; ";
print "\n     var cmtIconfld=\"cmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID; ";
print "\n     if (response[36]==\"\") {document.getElementById(cmtIconfld).innerHTML= \"{$commentImageURL}\" ;} ";
print "\n     else                    {document.getElementById(cmtIconfld).innerHTML= \"{$commentExistImageNoTitleURL}\" ;} ";
print "\n     setHiddenJavaArrayValueReverse(\"ISEQ\",ISEQ,PSEQ,ENID,response[32]); ";
print "\n     setHiddenJavaArrayValueReverse(\"PSEQ\",ISEQ,PSEQ,ENID,response[33]); ";
print "\n     setHiddenJavaArrayValueReverse(\"ENID\",ISEQ,PSEQ,ENID,response[34]); ";
print "\n     setHiddenJavaArrayValueReverse(\"PMID\",ISEQ,PSEQ,ENID,response[35]); ";
print "\n     enableEntryFld(PESPMTfld); ";
print "\n     enableEntryFld(RHRSCDfld); ";
print "\n     if (document.getElementById(ICONfld)) { ";
print "\n       if (document.getElementById(PESPMTfld).checked) {showSel(ICONfld);} ";
print "\n       else                                            {hideSel(ICONfld);} ";
print "\n     }; ";
print "\n     if (MnCd!=\"D\") {";
print "\n       var ERRS=response[37]; ";
print "\n       var r=38; ";
print "\n       if (ERRS > 0) { ";
print "\n         for (var i=0; i<ERRS; i++) { ";
print "\n           var PRCOLM=response[r]; ";
print "\n           var ERRD=response[r+1]; ";
print "\n           setColumnErrorReverse(PRCOLM,ERRD,ISEQ,PSEQ,ENID); ";
print "\n           r=r+2 ; ";
print "\n         }; ";
print "\n       }; ";
print "\n     }; ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  } ";
require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
print "\n } ";

// Set Column Error
print "\n function setColumnErrorReverse(PRCOLM,ERRD,ISEQ,PSEQ,ENID) {  ";
print "\n   var field=\"\"; ";
print "\n   if (PRCOLM==\"RHRSCD\") {field=\"rscd\";} ";
print "\n   var entryfld=field+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n   if (document.getElementById(entryfld)) { ";
print "\n     document.getElementById(entryfld).style.backgroundColor = '" . $errorBackground . "'; ";
print "\n     document.getElementById(entryfld).setAttribute(\"title\", ERRD) ; ";
print "\n   } ";
print "\n } ";


// Get Hidden Value
print "\n function getHiddenJavaArrayValueReverse(PRCOLM,ISEQ,PSEQ,ENID) {  ";
print "\n   for (var i=0; hiddenJavaArray[i][0] != \"QUIT\" && i < hiddenJavaArray.length; i++) {";
print "\n     var hiddenfld=ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n     if (hiddenJavaArray[i][0]!=hiddenfld) {continue} ";
print "\n     var hiddenval=0 ; ";
print "\n     if      (PRCOLM==\"ISEQ\") {hiddenval=hiddenJavaArray[i][1];} ";
print "\n     else if (PRCOLM==\"PSEQ\") {hiddenval=hiddenJavaArray[i][2];} ";
print "\n     else if (PRCOLM==\"ENID\") {hiddenval=hiddenJavaArray[i][3];} ";
print "\n     else if (PRCOLM==\"PMID\") {hiddenval=hiddenJavaArray[i][4];} ";
print "\n     break; ";
print "\n   }";
print "\n   return hiddenval; ";
print "\n } ";

// Set Hidden Value
print "\n function setHiddenJavaArrayValueReverse(PRCOLM,ISEQ,PSEQ,ENID,hiddenval) {  ";
print "\n   for (var i=0; hiddenJavaArray[i][0] != \"QUIT\" && i < hiddenJavaArray.length; i++) {";
print "\n     var hiddenfld=ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n     if (hiddenJavaArray[i][0]!=hiddenfld) {continue} ";
print "\n     if      (PRCOLM==\"ISEQ\") {hiddenJavaArray[i][1]=hiddenval;} ";
print "\n     else if (PRCOLM==\"PSEQ\") {hiddenJavaArray[i][2]=hiddenval;} ";
print "\n     else if (PRCOLM==\"ENID\") {hiddenJavaArray[i][3]=hiddenval;} ";
print "\n     else if (PRCOLM==\"PMID\") {hiddenJavaArray[i][4]=hiddenval;} ";
print "\n     break; ";
print "\n   }";
print "\n   return hiddenval; ";
print "\n } ";

// Accept Comment Entry
print "\n function AcceptCmtEntryReverse(ISEQ,PSEQ,ENID) { ";
print "\n   var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n   var commententryfld=\"commententry\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n   var responseFlds=ISEQ+\"|\"+PSEQ+\"|\"+ENID ; ";
print "\n   var IVISEQvalue=getHiddenJavaArrayValueReverse(\"ISEQ\",ISEQ,PSEQ,ENID); ";
print "\n   var YPPSEQvalue=getHiddenJavaArrayValueReverse(\"PSEQ\",ISEQ,PSEQ,ENID); ";
print "\n   var PEENIDvalue=getHiddenJavaArrayValueReverse(\"ENID\",ISEQ,PSEQ,ENID); ";
print "\n   var PEPMIDvalue=getHiddenJavaArrayValueReverse(\"PMID\",ISEQ,PSEQ,ENID); ";
print "\n   var cmnt = document.getElementById(newPECMNTfld).value ";
print "\n   var cmnt = cmnt.replace(/'/g, \"''\"); ";
print "\n   var stmt = \" Update ARPYEN \"; ";
print "\n       stmt += \" Set PECMNT='\" + cmnt +\"'\"; ";
print "\n       stmt += \" Where (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPMID,PEISEQ,PEENID)=(" . $fromBatchNumber . "," . $fromBatchDate . "," . $fromBatchBank . ",'" . $fromType . "'," . $fromID . ",'" . $fromDocument . "','\" + PEPMIDvalue.toString() + \"',\" + IVISEQvalue.toString() + \",\" + PEENIDvalue.toString()+\")\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "RunSQLUpdateWithResponse.php" . $scriptVarBase . "&amp;updateStmt=\" + escape(stmt) + \"&amp;responseFlds=\" + escape(responseFlds) + \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   var ajaxRequest = new ajaxObject(url,AcceptCmtEntryReverseResponse); ";
print "\n   ajaxRequest.update();  ";
require 'ApplCashPaymentJavaUpdatePendingInclude.php';
print "\n   hideSel(commententryfld); ";
print "\n } ";

// Accept Comment Entry Response
print "\n function AcceptCmtEntryReverseResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
print "\n     var ISEQ=response[1]; ";
print "\n     var PSEQ=response[2]; ";
print "\n     var ENID=response[3]; ";
print "\n     var oldPECMNTfld=\"oldcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n     var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n     var cmtIconfld=\"cmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n     if (document.getElementById(oldPECMNTfld).innerHTML!=\"\" && document.getElementById(newPECMNTfld).value==\"\") { ";
print "\n       document.getElementById(cmtIconfld).innerHTML= \"{$commentImageURL}\" ; } ";
print "\n     else if (document.getElementById(oldPECMNTfld).innerHTML==\"\" && document.getElementById(newPECMNTfld).value!=\"\") { ";
print "\n       document.getElementById(cmtIconfld).innerHTML= \"{$commentExistImageNoTitleURL}\" ; } ";
print "\n     document.getElementById(oldPECMNTfld).innerHTML = document.getElementById(newPECMNTfld).value; ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\");  }";
require 'ApplCashPaymentJavaUpdateCompleteInclude.php';
print "\n } ";

// Clear Comment Entry
print "\n function ClearCmtEntryReverse(ISEQ,PSEQ,ENID) { ";
print "\n   var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(newPECMNTfld).value = \"\"; ";
print "\n   document.getElementById(newPECMNTfld).focus() ; ";
print "\n } ";

// Close Comment Entry
print "\n function CloseCmtEntryReverse(ISEQ,PSEQ,ENID) { ";
print "\n   ResetCmtEntryReverse(ISEQ,PSEQ,ENID); ";
print "\n   var commententryfld=\"commententry\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n   hideSel(commententryfld); ";
print "\n } ";

// Reset Comment Entry
print "\n function ResetCmtEntryReverse(ISEQ,PSEQ,ENID) { ";
print "\n   var oldPECMNTfld=\"oldcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n   var newPECMNTfld=\"newcmt\"+ISEQ+\"_\"+PSEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(newPECMNTfld).value = document.getElementById(oldPECMNTfld).innerHTML; ";
print "\n   document.getElementById(newPECMNTfld).focus() ; ";
print "\n } ";

?>