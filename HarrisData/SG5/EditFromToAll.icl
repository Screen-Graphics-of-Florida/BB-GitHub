%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Edit From/To/All Range                                      *
*********************************************************************
%}

function editFromToAll(fromField, toField, allField, fieldType)
  {
   if (fieldType == "D") {
       fromValue = fromField.value;
       toValue = toField.value;
       fcc = 0;
       fyy = fromValue.substring(4,6);
       fmd = fromValue.substring(0,4);
       tcc = 0;
       tyy = toValue.substring(4,6);
       tmd = toValue.substring(0,4);
       if (fyy>=0 && fyy<=39) {fcc=1;}
       if (tyy>=0 && tyy<=39) {tcc=1;}
       fromValue = fcc + fyy + fmd;
       toValue   = tcc + tyy + tmd;
   }
       else if (fieldType == "A") {
           fromValue = fromField.value.toUpperCase();
           toValue = toField.value.toUpperCase();
       }
           else if (fieldType == "P") {
               fromValue = fromField.value;
               toValue = toField.value;
               fcc = 0; fyy = 0; fpp = 0;
               if (fromValue > "0") {
                   fyy = fromValue.substring(2,4);
                   fpp = fromValue.substring(0,2);
                   if (fyy>=0 && fyy<=39) {fcc=1;}}
               tcc = 0; tyy = 0; tpp = 0;
               if (toValue > "0") {
                   tyy = toValue.substring(2,4);
                   tpp = toValue.substring(0,2);
                   if (tyy>=0 && tyy<=39) {tcc=1;}}
               fromValue = fcc + fyy + fpp;
               toValue   = tcc + tyy + tpp;
           }
               else if (fieldType > "0") {
                   fromValue = fromField.value;
                   toValue = toField.value;
                   for (var i=fromValue.length; i<fieldType; i++) {fromValue = "0" + fromValue;}
                   for (var i=toValue.length; i<fieldType; i++)   {toValue = "0" + toValue;}
               }

   if (allField.checked && (fromField.value > "" || toField.value > ""))
           {alert("From and to values are not valid with all selection");
           fromField.focus();
           fromField.select();
           return false;}
       else {if (fromValue > toValue)
                {alert("From value greater than to value");
                fromField.focus();
                fromField.select();
                return false;}
             else {if (fromField.value == "" && toField.value == "" && allField.checked == false)
                       {alert("From to and all selections are all blank");
                       fromField.focus();
                       fromField.select();
                       return false;}
                   else {return true;}
             }
       }
   }

function editFromTo(fromField, toField, fieldType)
  {
   if (fieldType == "D") {
       fromValue = fromField.value;
       toValue = toField.value;
       fcc = 0;
       fyy = fromValue.substring(4,6);
       fmd = fromValue.substring(0,4);
       tcc = 0;
       tyy = toValue.substring(4,6);
       tmd = toValue.substring(0,4);
       if (fyy>=0 && fyy<=39) {fcc=1;}
       if (tyy>=0 && tyy<=39) {tcc=1;}
       fromValue = fcc + fyy + fmd;
       toValue   = tcc + tyy + tmd;
   }
       else if (fieldType == "A") {
           fromValue = fromField.value.toUpperCase();
           toValue = toField.value.toUpperCase();
       }
           else if (fieldType > "0") {
               fromValue = fromField.value;
               toValue = toField.value;
               for (var i=fromValue.length; i<fieldType; i++) {fromValue = "0" + fromValue;}
               for (var i=toValue.length; i<fieldType; i++)   {toValue = "0" + toValue;}
           }

   if (fromValue > toValue) {
        alert("From value greater than to value");
        fromField.focus();
        fromField.select();
        return false;}
   else {return true;}
  }