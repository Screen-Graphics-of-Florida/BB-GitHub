%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Edit Numeric Value For Zero                                 *
*********************************************************************
%}
function editZero(field, intMax, decMax) {
testfield = field.value;
var valid1 = "0123456789-. "
var valid2 = "0123456789- "
var valid3 = "123456789"
if (decMax == "0") {var valid = valid2;}
else {var valid = valid1;}
var ok = "yes";
var temp;
var max;
for (var i=0; i<field.value.length; i++) {
   temp = "" + field.value.substring(i, i+1);
   if (valid.indexOf(temp) == "-1") ok = "no";}
   if (ok == "no") {
      alert ("Invalid entry in numeric field.  Please try again.");
      field.focus();
      field.select();
      return false;}
if (ok == "yes") {
   if (testfield.indexOf('.') == -1) testfield += ".";
   inttext = testfield.substring(0, testfield.indexOf('.'));
   dectext = testfield.substring(testfield.indexOf('.')+1, testfield.length);
   if (inttext.length > intMax || dectext.length > decMax){
      max = "";
      for(i = 0; i < intMax; i++)
      if (i > 8){max += i-9;}
      else {max += i+1;}
      max += ".";
      for(i = 0; i < decMax; i++){max += i+1;}
      alert ("Invalid entry.  Maximum field size is " + max + ". Please try again.");
      field.focus();
      field.select();
      return false;}
}
if (ok == "yes") {
   for (var i=0; i<field.value.length; i++) {
   temp = "" + field.value.substring(i, i+1);
   if (valid3.indexOf(temp) >= 0) ok = "OK";}
   if (ok != "OK") {
      alert ("Field requires a valid entry.  Please try again.");
      field.focus();
      field.select();
      return false;}
   else {
      return true;}
}
}