<?php

// assign defaults values
$nextPESSEQ=RetValue("(PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK))=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim($fromDocument) . "') ", "ARPYEN", "Coalesce(Max(PESSEQ),0)+1");
print "\n var nextPESSEQ={$nextPESSEQ}; ";
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

// Save Skip Value
print "\n function SaveSkipValue() { ";
print "\n } ";

// Accept Entry
print "\n function ARReleaseEntry() { ";
print "\n   if (document.getElementById('SKIP').checked) { ";
print "\n     var skip = \"Y\"; ";
print "\n   } else { "; ;
print "\n     var skip = \"N\"; ";
print "\n   } ";
print "\n   var edtVar = \"@@bchn" . $fromBatchNumber . "}{@@bchd" . $fromBatchDate . "}{@@bchb" . $fromBatchBank . "}{@@type" . $fromType . "}{@@id@@" . $fromID . "}{@@chk@" . $fromDocument . "\"; ";
print "\n       edtVar += \"}{@@skip\" + skip; ";
print "\n       edtVar += \"}{\"; ";
print "\n   var url = \"" . $homeURL . $phpPath . "ApplCashPaymentReleaseEntry.php" . $scriptVarBase . "&amp;edtVar=\" + escape(edtVar)+ \"&amp;dummy=\" + new Date().getTime(); ";
print "\n   var ajaxRequest = new ajaxObject(url,ARReleaseEntryResponse); ";
print "\n   ajaxRequest.update();  ";
require 'ApplCashPaymentJavaReleasePendingInclude.php';
print "\n } ";

// Accept Entry Response
print "\n function ARReleaseEntryResponse(responseText, responseStatus) {  ";
print "\n   if (responseStatus==200) { ";
print "\n     var response= responseText.split(\"|\"); ";
require 'ApplCashPaymentJavaReleaseCompleteInclude.php';
print "\n     if (responseActiveCount==0) { ";
$noampVarBase  = str_replace("amp;", "", $scriptVarBase);
if ($fromType=="C") {print "\n       var returnUrl=\"{$homeURL}{$phpPath}ApplCashCustomer.php{$noampVarBase}&amp;tag=REPORT\" ";}
else                {print "\n       var returnUrl=\"{$homeURL}{$phpPath}ApplCashPayer.php{$noampVarBase}&amp;tag=REPORT\" ";}
print "\n       window.location.href=returnUrl; ";
print "\n     } ";
print "\n   } else  { alert(responseStatus + \" -- Error Processing Request\"); ";
require 'ApplCashPaymentJavaReleaseCompleteInclude.php';
print "\n   } ";
print "\n } ";

// Disable Entry Field
print "\n function disableEntryFld(field,setDisable) { ";
print "\n   if (document.getElementById(field)) { ";
print "\n     if (document.getElementById(field).getAttribute(\"title\")) {document.getElementById(field).removeAttribute(\"title\") ;} ";
print "\n     document.getElementById(field).style.backgroundColor='transparent'; ";
print "\n     if (setDisable==\"Y\") {document.getElementById(field).disabled=true;} ";
print "\n   } ";
print "\n } ";

// Enable Entry Field
print "\n function enableEntryFld(field) { ";
print "\n   if (document.getElementById(field)) {document.getElementById(field).disabled=false;} ";
print "\n } ";

// Edit and Update Amount
print "\n function editPEAMT(ISEQ,ENID,MNID) { ";
print "\n   var PEAMTfld=\"amt\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEAMTfld).name, 11, 2)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEAMT'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
print "\n } ";

// Edit and Update Account
print "\n function editPEARAC(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEARACfld=\"arac\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEARACfld).name, 4, 0)) { ";
print "\n     var PEARSBfld=\"arsb\"+ISEQ+\"_\"+ENID ; ";
print "\n     document.getElementById(PEARSBfld).select() ; ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Reference
print "\n function editPEARPO(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEARPOfld=\"arpo\"+ISEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(PEARPOfld).value=document.getElementById(PEARPOfld).value.toUpperCase() ; ";
print "\n   var MnCd='C'; ";
print "\n   var PRCOLM='PEARPO'; ";
print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Subaccount
print "\n function editPEARSB(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEARSBfld=\"arsb\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEARSBfld).name, 4, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEARSB'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Bill-To
print "\n function editPEBLTO(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEBLTOfld=\"blto\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEBLTOfld).name, 7, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEBLTO'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Amount
print "\n function editPEDAMT(ISEQ,ENID,MNID) { ";
print "\n   var PEDAMTfld=\"damt\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEDAMTfld).name, 11, 2)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEDAMT'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
print "\n } ";

// Edit and Update General Deduction
print "\n function editPEGDED(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEGDEDfld=\"gded\"+ISEQ+\"_\"+ENID ; ";
print "\n   var MnCd='C'; ";
print "\n   var PRCOLM='PEGDED'; ";
print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";

print "\n   if (document.getElementById(PEGDEDfld).checked==false) { ";
print "\n     var Rowfld=\"row\"+ISEQ+\"_\"+ENID ; ";
print "\n     document.getElementById('paymentTable').deleteRow(document.getElementById(Rowfld).rowIndex); ";
print "\n   } ";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Location
print "\n function editPELOC(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PELOCfld=\"loc\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PELOCfld).name, 3, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PELOC'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Miscellaneous Document
print "\n function editPEMCHK(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEMCHKfld=\"mchk\"+ISEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(PEMCHKfld).value=document.getElementById(PEMCHKfld).value.toUpperCase() ; ";
print "\n   var MnCd='C'; ";
print "\n   var PRCOLM='PEMCHK'; ";
print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Memo
print "\n function editPEMEMO(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEMEMOfld=\"memo\"+ISEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(PEMEMOfld).value=document.getElementById(PEMEMOfld).value.toUpperCase() ; ";
print "\n   var MnCd='C'; ";
print "\n   var PRCOLM='PEMEMO'; ";
print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Mfg Order
print "\n function editPEMORD(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEMORDfld=\"mord\"+ISEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(PEMORDfld).value=document.getElementById(PEMORDfld).value.toUpperCase() ; ";
print "\n   var MnCd='C'; ";
print "\n   var PRCOLM='PEMORD'; ";
print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Deduction Invoice
print "\n function editPENINV(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PENINVfld=\"ninv\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PENINVfld).name, 7, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PENINV'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   } ";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Account
print "\n function editPEOFAC(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEOFACfld=\"ofac\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEOFACfld).name, 4, 0)) { ";
print "\n     var PEOFSBfld=\"ofsb\"+ISEQ+\"_\"+ENID ; ";
print "\n     document.getElementById(PEOFSBfld).select() ; ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Company
print "\n function editPEOFCO(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEOFCOfld=\"ofco\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEOFCOfld).name, 2, 0)) { ";
print "\n     var PEOFFCfld=\"offc\"+ISEQ+\"_\"+ENID ; ";
print "\n     document.getElementById(PEOFFCfld).select() ; ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Facility
print "\n function editPEOFFC(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEOFFCfld=\"offc\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEOFFCfld).name, 4, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEOFFC'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Subaccount
print "\n function editPEOFSB(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEOFSBfld=\"ofsb\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEOFSBfld).name, 4, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEOFSB'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Order
print "\n function editPEORD(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEORDfld=\"ord\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEORDfld).name, 8, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEORD'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Order Date
print "\n function editPEORDT(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEORDTfld=\"ordt\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editdate(document.getElementById(PEORDTfld))) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEORDT'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Order Line
print "\n function editPEORLN(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEORLNfld=\"orln\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEORLNfld).name, 3, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEORLN'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Plant
print "\n function editPEPLT(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEPLTfld=\"plt\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PEPLTfld).name, 3, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PEPLT'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Pay Off Payment Code
print "\n function editPEPOSB(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PEPOSBfld=\"posb\"+ISEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(PEPOSBfld).value=document.getElementById(PEPOSBfld).value.toUpperCase() ; ";
print "\n   var MnCd='C'; ";
print "\n   var PRCOLM='PEPOSB'; ";
print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Payment Code
print "\n function editPESBCD(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PESBCDfld=\"sbcd\"+ISEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(PESBCDfld).value=document.getElementById(PESBCDfld).value.toUpperCase() ; ";
print "\n   var MnCd='C'; ";
print "\n   var PRCOLM='PESBCD'; ";
print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Ship-To
print "\n function editPESHTO(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PESHTOfld=\"shto\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PESHTOfld).name, 7, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PESHTO'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Invoice
print "\n function editPESINV(ISEQ,ENID,MNID) { ";
print "\n   var PESINVfld=\"sinv\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PESINVfld).name, 7, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PESINV'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
if ($paymentType!="M" && $paymentType!="U") {
	print "\n     var PEGDEDfld=\"gded\"+ISEQ+\"_\"+ENID ; ";
	print "\n     if (!document.getElementById(PEGDEDfld) || document.getElementById(PEGDEDfld).checked==false) { ";
	print "\n       var Rowfld=\"row\"+ISEQ+\"_\"+ENID ; ";
	print "\n       document.getElementById('paymentTable').deleteRow(document.getElementById(Rowfld).rowIndex); ";
	print "\n     } ";
}
print "\n   } ";
print "\n } ";

// Edit and Update Salesman
print "\n function editPESLSM(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PESLSMfld=\"slsm\"+ISEQ+\"_\"+ENID ; ";
print "\n   if (editNum(document.getElementById(PESLSMfld).name, 3, 0)) { ";
print "\n     var MnCd='C'; ";
print "\n     var PRCOLM='PESLSM'; ";
print "\n     updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
print "\n   }";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Edit and Update Terms
print "\n function editPETRMS(ISEQ,ENID,MNID) { ";
if ($entryType=="") {
	print "\n   var PESPMTfld=\"spmt\"+ISEQ+\"_\"+ENID; ";
	print "\n   if (document.getElementById(PESPMTfld).getAttribute(\"type\")!=\"checkbox\" || document.getElementById(PESPMTfld).checked) { ";
}
print "\n   var PETRMSfld=\"trms\"+ISEQ+\"_\"+ENID ; ";
print "\n   document.getElementById(PETRMSfld).value=document.getElementById(PETRMSfld).value.toUpperCase() ; ";
print "\n   var MnCd='C'; ";
print "\n   var PRCOLM='PETRMS'; ";
print "\n   updARPYEN(ISEQ,ENID,MNID,MnCd,PRCOLM); ";
if ($entryType=="") {print "\n   }";}
print "\n } ";

// Set Column Error
print "\n function setColumnError(PRCOLM, ERRD, ISEQ, ENID) {  ";
print "\n   var field=\"\"; ";
print "\n   if      (PRCOLM==\"PEAMT\")  {field=\"amt\";} ";
print "\n   else if (PRCOLM==\"PEARAC\") {field=\"arac\";} ";
print "\n   else if (PRCOLM==\"PEARPO\") {field=\"arpo\";} ";
print "\n   else if (PRCOLM==\"PEARSB\") {field=\"arsb\";} ";
print "\n   else if (PRCOLM==\"PEBLTO\") {field=\"blto\";} ";
print "\n   else if (PRCOLM==\"PECMNT\") {field=\"cmnt\";} ";
print "\n   else if (PRCOLM==\"PEDAMT\") {field=\"damt\";} ";
print "\n   else if (PRCOLM==\"PEGDED\") {field=\"gded\";} ";
print "\n   else if (PRCOLM==\"PELOC\")  {field=\"loc\";} ";
print "\n   else if (PRCOLM==\"PEMCHK\") {field=\"mchk\";} ";
print "\n   else if (PRCOLM==\"PEMEMO\") {field=\"memo\";} ";
print "\n   else if (PRCOLM==\"PEMORD\") {field=\"mord\";} ";
print "\n   else if (PRCOLM==\"PENINV\") {field=\"ninv\";} ";
print "\n   else if (PRCOLM==\"PEOFAC\") {field=\"ofac\";} ";
print "\n   else if (PRCOLM==\"PEOFCO\") {field=\"ofco\";} ";
print "\n   else if (PRCOLM==\"PEOFFC\") {field=\"offc\";} ";
print "\n   else if (PRCOLM==\"PEOFSB\") {field=\"ofsb\";} ";
print "\n   else if (PRCOLM==\"PEORD\")  {field=\"ord\";} ";
print "\n   else if (PRCOLM==\"PEORDT\") {field=\"ordt\";} ";
print "\n   else if (PRCOLM==\"PEORLN\") {field=\"orln\";} ";
print "\n   else if (PRCOLM==\"PEPLT\")  {field=\"plt\";} ";
print "\n   else if (PRCOLM==\"PEPOSB\") {field=\"posb\";} ";
print "\n   else if (PRCOLM==\"RHRSCD\") {field=\"rscd\";} ";
print "\n   else if (PRCOLM==\"PESBCD\") {field=\"sbcd\";} ";
print "\n   else if (PRCOLM==\"PESHTO\") {field=\"shto\";} ";
print "\n   else if (PRCOLM==\"PESINV\") {field=\"sinv\";} ";
print "\n   else if (PRCOLM==\"PESLSM\") {field=\"slsm\";} ";
print "\n   else if (PRCOLM==\"PETRMS\") {field=\"trms\";} ";
if ($paymentType=="C" || $paymentType=="Y") {
	print "\n   if (PRCOLM==\"PESBCD\") {var entryfld=\"subCode\";} ";
	print "\n   else                    {var entryfld=field+ISEQ+\"_\"+ENID ;} ";
} else {
	print "\n   var entryfld=field+ISEQ+\"_\"+ENID ; ";
}
print "\n   if (document.getElementById(entryfld)) { ";
print "\n     document.getElementById(entryfld).style.backgroundColor = '" . $errorBackground . "'; ";
print "\n     document.getElementById(entryfld).setAttribute(\"title\", ERRD) ; ";
print "\n   } ";
print "\n } ";

// Get Hidden Value
print "\n function getHiddenJavaArrayValue(PRCOLM,ISEQ,ENID) {  ";
print "\n   for (var i=0; hiddenJavaArray[i][0] != \"QUIT\" && i < hiddenJavaArray.length; i++) {";
print "\n     var hiddenfld=ISEQ+\"_\"+ENID ; ";
print "\n     if (hiddenJavaArray[i][0]!=hiddenfld) {continue} ";
print "\n     var hiddenval=0 ; ";
print "\n     if      (PRCOLM==\"MNID\") {hiddenval=hiddenJavaArray[i][1];} ";
print "\n     else if (PRCOLM==\"ISEQ\") {hiddenval=hiddenJavaArray[i][2];} ";
print "\n     else if (PRCOLM==\"ENID\") {hiddenval=hiddenJavaArray[i][3];} ";
print "\n     else if (PRCOLM==\"CRTB\") {hiddenval=hiddenJavaArray[i][4];} ";
print "\n     else if (PRCOLM==\"BALN\") {hiddenval=hiddenJavaArray[i][5];} ";
print "\n     else if (PRCOLM==\"DSCT\") {hiddenval=hiddenJavaArray[i][6];} ";
print "\n     break; ";
print "\n   }";
print "\n   return hiddenval; ";
print "\n } ";

// Set Hidden Value
print "\n function setHiddenJavaArrayValue(PRCOLM,ISEQ,ENID,hiddenval) {  ";
print "\n   for (var i=0; hiddenJavaArray[i][0] != \"QUIT\" && i < hiddenJavaArray.length; i++) {";
print "\n     var hiddenfld=ISEQ+\"_\"+ENID ; ";
print "\n     if (hiddenJavaArray[i][0]!=hiddenfld) {continue} ";
print "\n     if      (PRCOLM==\"MNID\") {hiddenJavaArray[i][1]=hiddenval;} ";
print "\n     else if (PRCOLM==\"ISEQ\") {hiddenJavaArray[i][2]=hiddenval;} ";
print "\n     else if (PRCOLM==\"ENID\") {hiddenJavaArray[i][3]=hiddenval;} ";
print "\n     else if (PRCOLM==\"CRTB\") {hiddenJavaArray[i][4]=hiddenval;} ";
print "\n     else if (PRCOLM==\"BALN\") {hiddenJavaArray[i][5]=hiddenval;} ";
print "\n     else if (PRCOLM==\"DSCT\") {hiddenJavaArray[i][6]=hiddenval;} ";
print "\n     break; ";
print "\n   }";
print "\n   return hiddenval; ";
print "\n } ";

?>
