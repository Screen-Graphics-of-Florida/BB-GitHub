%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Edit Numeric Value                                          *
*********************************************************************
%}
function editNum(field, intMax, decMax) {
    testfield = field.value;
    var valid1 = "0123456789-. "
    var valid2 = "0123456789- "
    if (decMax == "0") {var valid = valid2;}
    else {var valid = valid1;}
    var ok = "yes";
    var temp;
    var max;
    var dec=0;
    for (var i=0; i<field.value.length; i++) {
        temp = "" + field.value.substring(i, i+1);
        if (temp == ".") dec++;
        if (valid.indexOf(temp) == "-1") ok = "no";}

        if (ok == "no" || dec > 1) {
           alert ("Invalid entry in numeric column.  Please try again.");
           field.focus();
           field.select();
           return false;}

        if (ok == "yes") {
           negSign = testfield.substring(0, testfield.indexOf('-'));
           if (negSign.length > 0){
               alert ("Negative sign must be entered in front of the number.  Please try again.");
               ok = "no";
               field.focus();
               field.select();
               return false;}
        }

        if (ok == "yes") {
           negSign = "Y";
           if (testfield.indexOf('.') == -1) testfield += ".";
           inttext = testfield.substring(0, testfield.indexOf('.'));
           if (testfield.indexOf('-') == -1)  negSign = "N";
           if (negSign == "Y") {inttext = testfield.substring(1, testfield.indexOf('.'));}
           dectext = testfield.substring(testfield.indexOf('.')+1, testfield.length);
           if (inttext.length > intMax || dectext.length > decMax){
              max = "";
              for(i = 0; i < intMax; i++)
              if (i > 8){max += i-9;}
              else {max += i+1;}
              max += ".";
              for(i = 0; i < decMax; i++){max += i+1;}
              alert ("Invalid entry.  Maximum column size is " + max +". Please try again.");
              field.focus();
              field.select();
              return false;}
           else {
              return true;
        }
    }
}



function editNumPos(field, intMax, decMax) {
    testfield = field.value;
    var valid1 = "0123456789. "
    var valid2 = "0123456789 "
    if (decMax == "0") {var valid = valid2;}
    else {var valid = valid1;}
    var ok = "yes";
    var temp;
    var max;
    var dec=0;
    for (var i=0; i<field.value.length; i++) {
        temp = "" + field.value.substring(i, i+1);
        if (temp == ".") dec++;
        if (valid.indexOf(temp) == "-1") ok = "no";}

        if (ok == "no" || dec > 1) {
           alert ("Invalid entry in numeric column.  Please try again.");
           field.focus();
           field.select();
           return false;}
        
        if (ok == "yes") {
           negSign = "Y";
           if (testfield.indexOf('.') == -1) testfield += ".";
           inttext = testfield.substring(0, testfield.indexOf('.'));
           if (testfield.indexOf('-') == -1)  negSign = "N";
           if (negSign == "Y") {inttext = testfield.substring(1, testfield.indexOf('.'));}
           dectext = testfield.substring(testfield.indexOf('.')+1, testfield.length);
           if (inttext.length > intMax || dectext.length > decMax){
              max = "";
              for(i = 0; i < intMax; i++)
              if (i > 8){max += i-9;}
              else {max += i+1;}
              max += ".";
              for(i = 0; i < decMax; i++){max += i+1;}
              alert ("Invalid entry.  Maximum column size is " + max +". Please try again.");
              field.focus();
              field.select();
              return false;}
           else {
              return true;
        }
    }
}