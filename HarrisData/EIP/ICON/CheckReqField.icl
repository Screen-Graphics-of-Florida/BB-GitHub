%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Check Required Field For Value                              *
*********************************************************************
%}
function checkReqField(reqfield) {
var ok = "no";
for (var i=0; i<reqfield.value.length; i++) {
     var temp = reqfield.value.charAt(i);
     if (temp != ' ') ok = "OK";}
if (ok != "OK") {
   alert ("Field requires a valid entry.  Please try again.");
   reqfield.focus();
   reqfield.select();
   return false;}
else {
   return true;}
}