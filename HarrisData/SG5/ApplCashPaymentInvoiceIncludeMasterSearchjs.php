<?php
print "\n && editNum(document.$formName.frFreight, 11, 2) ";
print "\n && editNum(document.$formName.toFreight, 11, 2) ";
print "\n && editFromToOper(document.$formName.frFreight, document.$formName.toFreight, document.$formName.operFreight, 15) ";

print "\n && editNum(document.$formName.frTax, 11, 2) ";
print "\n && editNum(document.$formName.toTax, 11, 2) ";
print "\n && editFromToOper(document.$formName.frTax, document.$formName.toTax, document.$formName.operTax, 15) ";

print "\n && editNum(document.$formName.frSpecCharge, 11, 2) ";
print "\n && editNum(document.$formName.toSpecCharge, 11, 2) ";
print "\n && editFromToOper(document.$formName.frSpecCharge, document.$formName.toSpecCharge, document.$formName.operSpecCharge, 15) ";

if ($fromType=="P") {
	print "\n && editNum(document.$formName.frBillTo, 7, 0) ";
	print "\n && editNum(document.$formName.toBillTo, 7, 0) ";
	print "\n && editFromToOper(document.$formName.frBillTo, document.$formName.toBillTo, document.$formName.operBillTo, 7) ";
}

print "\n && editdate(document.$formName.frDueDate) ";
print "\n && editdate(document.$formName.toDueDate) ";
print "\n && editFromToOper(document.$formName.frDueDate, document.$formName.toDueDate, document.$formName.operDueDate, 'D') ";

print "\n && editdate(document.$formName.frInvoiceDate) ";
print "\n && editdate(document.$formName.toInvoiceDate) ";
print "\n && editFromToOper(document.$formName.frInvoiceDate, document.$formName.toInvoiceDate, document.$formName.operInvoiceDate, 'D') ";

print "\n && editNum(document.$formName.frOEOrder, 8, 0) ";
print "\n && editNum(document.$formName.toOEOrder, 8, 0) ";
print "\n && editFromToOper(document.$formName.frOEOrder, document.$formName.toOEOrder, document.$formName.operOEOrder, 8) ";

print "\n && editdate(document.$formName.frOEDate) ";
print "\n && editdate(document.$formName.toOEDate) ";
print "\n && editFromToOper(document.$formName.frOEDate, document.$formName.toOEDate, document.$formName.operOEDate, 'D') ";

print "\n && editNum(document.$formName.frOELine, 3, 0) ";
print "\n && editNum(document.$formName.toOELine, 3, 0) ";
print "\n && editFromToOper(document.$formName.frOELine, document.$formName.toOELine, document.$formName.operOELine, 3) ";

print "\n && editNum(document.$formName.frPlant, 3, 0) ";
print "\n && editNum(document.$formName.toPlant, 3, 0) ";
print "\n && editFromToOper(document.$formName.frPlant, document.$formName.toPlant, document.$formName.operPlant, 3) ";

print "\n && editNum(document.$formName.frInvoiceAmount, 11, 2) ";
print "\n && editNum(document.$formName.toInvoiceAmount, 11, 2) ";
print "\n && editFromToOper(document.$formName.frInvoiceAmount, document.$formName.toInvoiceAmount, document.$formName.operInvoiceAmount, 15) ";

print "\n && editNum(document.$formName.frLocation, 3, 0) ";
print "\n && editNum(document.$formName.toLocation, 3, 0) ";
print "\n && editFromToOper(document.$formName.frLocation, document.$formName.toLocation, document.$formName.operLocation, 3) ";

print "\n && editNum(document.$formName.frSalesman, 3, 0) ";
print "\n && editNum(document.$formName.toSalesman, 3, 0) ";
print "\n && editFromToOper(document.$formName.frSalesman, document.$formName.toSalesman, document.$formName.operSalesman, 3) ";

print "\n && editNum(document.$formName.frShipTo, 7, 0) ";
print "\n && editNum(document.$formName.toShipTo, 7, 0) ";
print "\n && editFromToOper(document.$formName.frShipTo, document.$formName.toShipTo, document.$formName.operShipTo, 7) ";

print "\n && editdate(document.$formName.frLastPostedDate) ";
print "\n && editdate(document.$formName.toLastPostedDate) ";
print "\n && editFromToOper(document.$formName.frLastPostedDate, document.$formName.toLastPostedDate, document.$formName.operLastPostedDate, 'D') ";
?>
