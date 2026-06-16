%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Date Edit                                                   *
*********************************************************************
%}

function editTimeHM(objName) {
var timefield = objName;
if (chkTimeHM(objName) == false) {
  timefield.select();
  alert("Time is invalid");
  timefield.focus();
  return false;}
else {
  return true;}
}

function chkTimeHM(objName) {
  var valid = "0123456789"
  var ok = "yes";
  var temp;
  for (var i=0; i<objName.value.length; i++) {
    temp = "" + objName.value.substring(i, i+1);
    if (valid.indexOf(temp) == "-1") ok = "no";}
  if (ok == "no") {return false;}
  if (ok == "yes") {
    var strTime;
    var intHour;
    var intMinute;
    var intSecond;
    var timefield = objName;
    strTime = timefield.value;
    if (strTime.length < 1) {return true;}
    if (strTime.length > 1 && strTime.length < 3) {return false;}
    if (strTime.length>2) {
      if (strTime.length>3) {
        intHour = strTime.substr(0, 2);
        intMinute = strTime.substr(2, 2);}
      else {
        intHour = strTime.substr(0, 1);
        intMinute = strTime.substr(1, 2);}
      if (intHour>24 || intHour<0) {return false;}
      if (intHour==24 && intMinute>0) {return false;}
      if (intMinute>59 || intMinute<0) {return false;}
      return true;
    }
  }
}

function editTimeHMS(objName) {
var timefield = objName;
if (chkTimeHMS(objName) == false) {
timefield.select();
alert("Time is invalid");
timefield.focus();
return false;}
else {
return true;}
}

function chkTimeHMS(objName) {
  var valid = "0123456789"
  var ok = "yes";
  var temp;
  for (var i=0; i<objName.value.length; i++) {
    temp = "" + objName.value.substring(i, i+1);
    if (valid.indexOf(temp) == "-1") ok = "no";}
  if (ok == "no") {return false;}
  if (ok == "yes") {
    var strTime;
    var intHour;
    var intMinute;
    var intSecond;
    var timefield = objName;
    strTime = timefield.value;
    if (strTime.length < 1) {return true;}
    if (strTime.length > 1 && strTime.length < 5) {return false;}
    if (strTime.length>4) {
      if (strTime.length>5) {
        intHour = strTime.substr(0, 2);
        intMinute = strTime.substr(2, 2);
        intSecond = strTime.substr(4);}
      else {
        intHour = strTime.substr(0, 1);
        intMinute = strTime.substr(1, 2);
        intSecond = strTime.substr(3);}
      if (intHour>24 || intHour<0) {return false;}
      if (intHour==24 && (intMinute>0 || intSecond>0)) {return false;}
      if (intMinute>59 || intMinute<0) {return false;}
      if (intSecond>59 || intMinute<0) {return false;}
      return true;
    }
  }
}
