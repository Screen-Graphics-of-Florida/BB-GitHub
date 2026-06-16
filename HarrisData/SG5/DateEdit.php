
function editdate(objName, desc) {
    if (desc==null) {fldDesc="";} else {fldDesc=desc + "\n \n";}
<?php if ("$sysDateFormat" == "YMD") {print ("var dateFormat = \"YYMMDD\";");
} elseif ("$sysDateFormat" == "DMY") {print ("var dateFormat = \"DDMMYY\";");
} else {                              print ("var dateFormat = \"MMDDYY\";");}
?>
  var datefield = objName;
  if (chkdate(objName) == false) {
    datefield.select();
    alert(fldDesc + "Date is invalid. Date must be entered as  " + dateFormat);
    datefield.focus();
    return false;}
  else {return true;}
}

function chkdate(objName) {
  var valid = "0123456789"
  var ok = "yes";
  var temp;
  for (var i=0; i<objName.value.length; i++) {
    temp = "" + objName.value.substring(i, i+1);
    if (valid.indexOf(temp) == "-1") ok = "no";}
    if (ok == "no") {return false;}
    if (ok == "yes") {
      var strDate;
      var intday;
      var intMonth;
      var intYear;
      var datefield = objName;
      strDate = datefield.value;
      if (strDate.length < 1) {return true;}
      if (strDate.length > 1 && strDate.length < 6) {return false;}
      if (strDate.length>5) {
         <?php if ("$sysDateFormat" == "YMD") {print ("intMonth = strDate.substr(2, 2);");
                                               print ("intday = strDate.substr(4, 2);");
                                               print ("intYear = strDate.substr(4, 2)");
         } elseif ("$sysDateFormat" == "DMY") {print ("intMonth = strDate.substr(2, 2);");
                                               print ("intday = strDate.substr(0, 2);");
                                               print ("intYear = strDate.substr(4, 2);");
         } else {                              print ("intMonth = strDate.substr(0, 2);");
                                               print ("intday = strDate.substr(2, 2);");
                                               print ("intYear = strDate.substr(4);");
         }
         ?>
      
        if (intMonth>12 || intMonth<1) {return false;}
        if ((intMonth == 1 || intMonth == 3 || intMonth == 5 || intMonth == 7 || intMonth == 8 || intMonth == 10 || intMonth == 12) && (intday > 31 || intday < 1)) {
        return false;
      }
      if ((intMonth == 4 || intMonth == 6 || intMonth == 9 || intMonth == 11) && (intday > 30 || intday < 1)) {return false;}

      if (intMonth == 2) {
        if (intday < 1) {return false;}
        if (LeapYear(intYear) == true) {
          if (intday > 29) {return false;}
        } else {if (intday > 28) {return false;}
        }
      }
      return true;
    }
  }
}

function LeapYear(intYear) {
  if (intYear % 100 == 0) {
    if (intYear % 400 == 0) { return true; }
  } else {
    if ((intYear % 4) == 0) { return true; }
  }
  return false;
}
