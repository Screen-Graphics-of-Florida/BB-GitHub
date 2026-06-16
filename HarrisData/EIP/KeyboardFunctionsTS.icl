%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Keyboard Functions - Touch Screen                           *
*********************************************************************
%}

var crlf = String.fromCharCode(10,13);  // New Line in Unicode
var nbsp = " ";
var qwerty = "QWERTYUIOPASDFGHJKLZXCVBNM123456789.0-";
var number1 = "!@#$%^&*()[]{}<>/\\?'\";:+=,";
var number2 = "~`_+={}[]\\|;:<>/          ";
var kbUpper = new Array (26);
var kbLower = new Array (26);
var kbNum1 = new Array (26);
var kbNum2 = new Array (26);
var kbShiftable = 26;
var nkbShiftable = 12;
for (i=0;i<26;i++) {
   kbUpper[i] = qwerty.substring(i,i+1).toUpperCase();
   kbLower[i] = qwerty.substring(i,i+1).toLowerCase();
   kbNum1[i] = number1.substring(i,i+1);
   kbNum2[i] = number2.substring(i,i+1);
}

var kbQty = false;
var kbID = "i1";
var kbObj = {};
var kbData = "";
var maxLen = "";
var nkbData = "";
var kbShiftOn = true;
var nkbShiftOn = true;
var kbSwitchNum = false;
var clrLite = "#FFFFAA";
var clrAltLite  = "#FFFFEE";
var clrNorm = "#FFFFFF";
var clrAlt  = "transparent";

var dateCurrent = new Date;
var dateToday = new Date;
var monName = new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
var dowAbbr = new Array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
var imgToday= "$(homeURL)$(imagePath)lgSqSun.gif";
var imgLeft = "$(homeURL)$(imagePath)lgSqLeft.gif";
var imgLeft2 = "$(homeURL)$(imagePath)lgSqLeft2.gif";
var imgRight = "$(homeURL)$(imagePath)lgSqRight.gif";
var imgRight2 = "$(homeURL)$(imagePath)lgSqRight2.gif";
var imgCancel = "$(homeURL)$(imagePath)lgSqNoChange.gif";

function kbActive(inpObj, keyboardID, qtyObj) {
    kbInactive();
    if (qtyObj !== undefined) {
    	kbQty = true;
    }
    kbID = inpObj.id;
    kbObj = document.getElementById(keyboardID);
    inpPos = getPos(inpObj);
    maxLen=inpObj.maxLength;
    inpObj.style.background='#FFFFE0';
    kbObj.style.visibility = "visible";
    kbObj.style.zIndex = 100;
    kbObj.style.top = (inpPos[1]+inpObj.offsetHeight)+'px';
    kbObj.style.left = inpPos[0]+'px';
    switch (keyboardID) {
       case "dtpDiv":
           dtpHelper(inpObj, "MDY");
       case "numericKeyboard":
           document.getElementById('nkbData').innerHTML=inpObj.value;
           document.getElementById('nkbData').setAttribute('maxLength',maxLen.value);
       default:
           document.getElementById('kbData').innerHTML=inpObj.value;
           document.getElementById('kbData').setAttribute('maxLength',maxLen.value);
	   }
    inpObj.style.background='transparent';
}

function kbInactive() {
	if (kbObj.id != undefined) {
    kbObj.style.zIndex = 0;
    kbObj.style.visibility = "hidden";
    kbObj.style.top = "0px";
    kbObj.style.left = "0px";
	}
}

function kbLite(obj) {
	switch (obj.id) {
    case "kbShift":
    case "kbSwitch":
    case "kbBack":
    case "nkbBack":
    case "kbCancel":
    case "nkbCancel":
    case "kbClear":
    case "nkbClear":
    case "kbReturn":
    case "nkbReturn":
        obj.style.backgroundColor = clrAltLite;
        obj.style.color = clrAlt;
        break
    default:
        obj.style.backgroundColor = clrLite;
	}
}

function kbDark(obj) {
	switch (obj.id) {
    case "kbShift":
    case "kbSwitch":
    case "kbBack":
    case "nkbBack":
    case "kbCancel":
    case "nkbCancel":
    case "kbClear":
    case "nkbClear":
    case "kbReturn":
    case "nkbReturn":
        obj.style.backgroundColor = clrAlt;
        obj.style.color = clrAltLite;
        break
    default:
        obj.style.backgroundColor = clrNorm;
	}
}

function kbPress(obj) {
	inpObj = document.getElementById(kbID);
	switch (obj.id) {
    case "kbShift":
        kbShift(obj.id);
        txtLetter = null;
        break
    case "kbSwitch":
        kbSwitch();
        txtLetter = null;
        break
    case "kbBack":
    case "nkbBack":
        backspaceAtCursor();
        txtLetter = null;
        break
    case "kbCancel":
        kbInactive();
        document.getElementById('kbData').innerHTML = "";
        txtLetter = null;
        break
    case "nkbCancel":
        kbInactive();
        document.getElementById('nkbData').innerHTML = "";
        txtLetter = null;
        break
    case "kbClear":
        document.getElementById('kbData').innerHTML = "";
        txtLetter = null;
        break
    case "nkbClear":
        document.getElementById('nkbData').innerHTML = "";
        txtLetter = null;
        break
    case "kbSpace":
        txtLetter = nbsp;
        break
    case "kbReturn":
        txtLetter = document.getElementById('kbData').innerHTML;
        txtLetter = txtLetter.replace("&lt;", "<");
        txtLetter = txtLetter.replace("&gt;", ">");
        txtLetter = txtLetter.replace("&amp;", "&");
        inpObj.value = txtLetter;
        document.getElementById('kbData').innerHTML = "";
        kbInactive();
        if (document.getElementById('submitChg')) {document.Chg.submit();}
        if (document.getElementById('submitSearch')) {document.Search.submit();}
        break
    case "nkbReturn":
        inpObj.value = document.getElementById('nkbData').innerHTML;
        document.getElementById('nkbData').innerHTML = "";
        kbInactive();
        if (document.getElementById('submitChg')) {document.Chg.submit();}
        if (document.getElementById('submitSearch')) {document.Search.submit();}
        break
    default:
        txtLetter = obj.innerText;
        if (txtLetter == undefined) {
            txtLetter = obj.innerHTML.replace(/<[^>]+>/g,"");
        }
        }

    if (txtLetter != null) {insertAtCursorTextarea(txtLetter);}
}

function backspaceAtCursor() {
 if (document.getElementById('kbData')) {
     document.getElementById('kbData').innerHTML=document.getElementById('kbData').innerHTML.substr(0,(document.getElementById('kbData').innerHTML.length-1));
 }
 if (document.getElementById('nkbData')) {
     document.getElementById('nkbData').innerHTML=document.getElementById('nkbData').innerHTML.substr(0,(document.getElementById('nkbData').innerHTML.length-1));
     if (kbQty) {
         document.getElementById('nkbData').innerHTML = numberWithCommas(document.getElementById('nkbData').innerHTML);
     }
 }
}

function insertAtCursorTextarea(txtLetter) {
 if (document.selection) {txtLetter = txtLetter.replace(/\&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");}

 if (document.getElementById('kbData')) {
   if (document.getElementById('kbData').innerHTML.length < maxLen) {
       document.getElementById('kbData').innerHTML += txtLetter;
   }
 }

 if (document.getElementById('nkbData')) {
   if (document.getElementById('nkbData').innerHTML.length < maxLen) {
       document.getElementById('nkbData').innerHTML += txtLetter;
       if (kbQty) {
           document.getElementById('nkbData').innerHTML = numberWithCommas(document.getElementById('nkbData').innerHTML);
       }
   }
 }
}

// Populating keys during shift does NOT handle entities (eg &amp;)!!!
function kbShift(id) {
	switch (id) {
    case "kbShift":
        if (kbSwitchNum) {return};
        imax = kbShiftable;
        elem = "kbShift";
        elemPrefix = "kb";
        useKeyPrimary = kbSwitchNum;
        shift = kbShiftOn;
        kbShiftOn = !kbShiftOn;
        break
    case "nkbShift":
        imax = nkbShiftable;
        elem = "nkbShift";
        elemPrefix = "nkb";
        useKeyPrimary = true;
        shift = nkbShiftOn;
        nkbShiftOn = !nkbShiftOn;
        break
    default:
        imax = 0;
	}
	if (shift) {
    document.getElementById(elem).innerHTML = "<span class=\"kbText\">aA<\/span>";
    if (useKeyPrimary) {
        for (i=0; i<imax; i++) {document.getElementById(elemPrefix+i).innerHTML = kbNum2[i];}
    } else {
        for (i=0; i<imax; i++) {document.getElementById(elemPrefix+i).innerHTML = kbLower[i];}
    }
	} else {
    document.getElementById(elem).innerHTML = "<span class=\"kbText\">Aa<\/span>";
    if (useKeyPrimary) {
        for (i=0; i<imax; i++) {document.getElementById(elemPrefix+i).innerHTML = kbNum1[i];}
    } else {
        for (i=0; i<imax; i++) {document.getElementById(elemPrefix+i).innerHTML = kbUpper[i];}
    }
	}
}

function kbSwitch() {
	if (kbSwitchNum) {
    kbSwitchNum = false;
    document.getElementById("kbSwitch").innerHTML = "<span class=\"kbText\">Special<\/span>";
    for (i=0; i<26; i++) {
        document.getElementById("kb"+i).innerHTML = kbUpper[i];
    }
    document.getElementById("kb35").innerHTML = ".";
    document.getElementById("kb37").innerHTML = "-";
	} else {
    kbSwitchNum = true;
    document.getElementById("kbSwitch").innerHTML = "<span class=\"kbText\">Letters<\/span>";
    for (i=0; i<26; i++) {
        document.getElementById("kb"+i).innerHTML = kbNum1[i];
    }
    document.getElementById("kb6").innerHTML = "&amp;";
    document.getElementById("kb14").innerHTML = "&lt;";
    document.getElementById("kb35").innerHTML = "_";
    document.getElementById("kb37").innerHTML = "|";
	}
}

function getPos(obj) {
	h = window.screen.availHeight/2;
 w = window.screen.availWidth/2;
	var curleft = curtop = 0;
	if (obj.offsetParent) {
    curleft = obj.offsetLeft
    curtop = obj.offsetTop
    while (obj = obj.offsetParent) {
        curleft += obj.offsetLeft
        curtop += obj.offsetTop
    }
	}
	if (curtop > h) {curtop=curtop-265}
	if (curleft > w) {curleft=curleft-395}
	return [curleft,curtop];
}

function dtpHelper(objInput, dtpFormat) {
 // Get default value, if loaded in input box
	objInputID = objInput.id;
 if (objInput.value != undefined && objInput.value != '') {  // Assumes YYYY-MM-DD HH:MM:SS format
    dateCurrent = dtpString2Date(objInput.value,dtpFormat);
 }
 if (dateCurrent == undefined) {dateCurrent = dateToday;}
 dtpObjID = 'dtpDiv';
 dtp = document.getElementById(dtpObjID);
 dtp.style.visibility = 'visible';
 dtp.style.zIndex = 100;
 dtp.style.left = (getPosX(objInput))+'px';
 h = window.screen.availHeight/2;
 curtop=getPosY(objInput);
 if (curtop > h) {curtop=curtop-300}
 dtp.style.top = (curtop+25)+'px';
 loadCalM(objInputID, dateCurrent.getFullYear(), dateCurrent.getMonth(), dtpFormat);
}
	
function getPosX(obj) {
 var posX = 0;
 if (obj.offsetParent) {
     while (obj.offsetParent) {
         posX += obj.offsetLeft
         obj = obj.offsetParent;
     }
 } else if (obj.x)
     posX += obj.x;
     return posX;
}

function getPosY(obj) {
 var posY = 0;
 if (obj.offsetParent) {
     while (obj.offsetParent) {
         posY += obj.offsetTop;
         obj = obj.offsetParent;
     }
 } else if (obj.y)
     posY += obj.y;
     return posY;
}
	
function loadCalM(objInputID, calYY, calMM, dtpFormat) {
 dateCurrent = dateToday;
 curYY = dateCurrent.getFullYear();
 curMM = dateCurrent.getMonth();
 prvYY = calYY - 1;
 nxtYY = calYY + 1;
 calPM = calMM - 1;
 calPY = calYY;
 if (calPM < 0) {calPM = 11; calPY = calYY - 1;}
 calNM = calMM + 1;
 calNY = calYY;
 if (calNM > 11) {calNM = 0; calNY = calYY + 1;}
 // Establish control bar
 funToday = "loadCalM(" + dtpParm(objInputID) + "," + curYY + "," + curMM + "," + dtpParm(dtpFormat) + ");";
 funHTMLNY = "loadCalM(" + dtpParm(objInputID) + "," + nxtYY + "," + calMM + "," + dtpParm(dtpFormat) + ");";
 funHTMLN = "loadCalM(" + dtpParm(objInputID) + "," + calNY + "," + calNM + "," + dtpParm(dtpFormat) + ");";
 funHTMLP = "loadCalM(" + dtpParm(objInputID) + "," + calPY + "," + calPM + "," + dtpParm(dtpFormat) + ");";
 funHTMLPY = "loadCalM(" + dtpParm(objInputID) + "," + prvYY + "," + calMM + "," + dtpParm(dtpFormat) + ");";
 funHTMLCL = "dtpClose(" + dtpParm(objInputID) + ");";
 conHTML = '<div id="dtpControl">';
 conHTML += '<img src="' + imgToday + '" alt="Today" title="' + monName[curMM] + ' ' + curYY + '" onClick="' + funToday + '" class="dtpControlImgL">';
 conHTML += '<img src="' + imgLeft2 + '" alt="Prior" title="' + monName[calMM] + ' ' + prvYY + '" onClick="' + funHTMLPY + '" class="dtpControlImgL">';
 conHTML += '<img src="' + imgLeft + '" alt="Prior" title="' + monName[calPM] + ' ' + calPY + '" onClick="' + funHTMLP + '" class="dtpControlImgL">';
 conHTML += '<img src="' + imgCancel + '" alt="X" title="Cancel" onClick="' + funHTMLCL + '" class="dtpControlImgR">';
 conHTML += '<img src="' + imgRight2 + '" alt="Next" title="' + monName[calMM] + ' ' + nxtYY +  '" onClick="' + funHTMLNY + '" class="dtpControlImgR">';
 conHTML += '<img src="' + imgRight + '" alt="Next" title="' + monName[calNM] + ' ' + calNY +  '" onClick="' + funHTMLN + '" class="dtpControlImgR">';
 conHTML += monName[calMM] + ' ' + calYY;
 conHTML += '<\/div>';
 srcHTML = conHTML;
 // Populate month with days
 srcHTML += '<div id="dtpMonth">';
 w = 0;              // plot six weeks per month
 wd = 0;             // plot seven days per week
 while (wd < 7) {
     srcHTML += '<span class="dtpDayHdr">' + dowAbbr[wd] + '<\/span>';
     wd++;
 }
 srcHTML += '<br>';
		
 dateWork = new Date;
 dateWork.setFullYear(calYY, calMM, '1');
 dow = dateWork.getDay();
 i = dtpMonLength(calPM, calPY) - dow + 1;
 wd = 0;
 while (i <= dtpMonLength(calPM, calPY)) {
     srcHTML += makeSpan(calPY, calPM, i, 'dtpDayOld', objInputID, dtpFormat);
     i++;
     wd++;
 }

 i = 1;
 while (i <= dtpMonLength(calMM, calYY)) {
     while (wd < 7 && i <= dtpMonLength(calMM, calYY)) {
         dateWork.setFullYear(calYY, calMM, i)
         if (dateWork.getFullYear() == dateToday.getFullYear()
             && dateWork.getMonth() == dateToday.getMonth()
             && dateWork.getDate() == dateToday.getDate())
             {dtpClass = "dtpDayNow";}
         else {
             if (dateWork.getFullYear() == dateCurrent.getFullYear()
             && dateWork.getMonth() == dateCurrent.getMonth()
             && dateWork.getDate() == dateCurrent.getDate())
             {dtpClass = "dtpDayCur";}
         else
             {dtpClass = "dtpDay";}
     }
     srcHTML += makeSpan(calYY, calMM, i, dtpClass, objInputID, dtpFormat);
     wd++;
     i++;
     }
     if (wd > 6) {
         srcHTML += '<br>'
         wd = 0;
         w++;
     }
 }

 i = 1;
 while (w < 6) {
     while (wd < 7) {
         srcHTML += makeSpan(calNY, calNM, i, 'dtpDayOld', objInputID, dtpFormat);
         i++;
         wd++;
     }
     srcHTML += '<br>';
     wd = 0;
     w++
 }		
 srcHTML += '<\/div>';
 dtp.innerHTML = srcHTML;
}

function makeSpan(calYY, calMM, calDD, calClass, objInputID, dtpFormat) {
 calMM = calMM + 1; // adjust for javascript month 0-11 issue
 calSp = "";
 id = calYY + '-';
 if (calMM < 10) {id += '0';}
 id += calMM + '-';
 if (calDD < 10) {id += '0'; calSp = '&nbsp;';}
 id += calDD;
 spanHTML = '<span class="' + calClass + '" id="' + id
            + '" onClick="dtpDaySel(' + dtpParm(objInputID) + ',' + dtpParm(id) + ',' + dtpParm(dtpFormat) + ');" '
            + '" onMouseOver="dtpDayOn(' + dtpParm(id) + ');" onMouseOut="dtpDayOff(' + dtpParm(id) + ');"'
            + '>' + calSp + calDD + '<\/span>';
 return spanHTML;
}

function dtpParm(x) {
 dtpParmX = "'" + x + "'";
 return(dtpParmX);
}
		
function dtpMonLength(m,y) {
 var monLength = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
 monLen = monLength[m];
 if (m == 1 && isLeapYear(y)) {monLen += 1;}
 return(monLen);
}

function isLeapYear(y) {  // Check for leap year (divisible by 4, not by 100, unless by 400)
 if ((y % 4) == 0) {
     if ((y % 100) == 0) {
         if ((y % 400) == 0) {return true;}
         else {return false;}
     } else {return true;}
 } else {return false;}
}

function dtpString2Date(str, dtpFormat) {
 HH = 0;
 MN = 0;
 SS = 0;
 switch(dtpFormat) {
     case 'MDY': // MMDDYY
         YYYY = '20'+str.substr(4,2);
         MM = str.substr(0,2)-1;
         DD = str.substr(2,2);
         break
     case 'D1': // YYYY-MM-DD
         YYYY = str.substr(0,4);
         MM = str.substr(5,2)-1;
         DD = str.substr(8,2);
         break
     case 'D1T1': // YYYY-MM-DD HH:MM:SS
         YYYY = str.substr(0,4);
         MM = str.substr(5,2)-1;
         DD = str.substr(8,2);
         HH = str.substr(11,2);
         MN = str.substr(14,2);
         SS = str.substr(17,2);
         break
     default: // YYYY-MM-DD
         YYYY = str.substr(0,4);
         MM = str.substr(5,2)-1;
         DD = str.substr(8,2);
 }
 var dateWork = new Date();
 dateWork.setFullYear(YYYY,MM,DD);
 dateWork.setHours(HH,MN,SS);
 return dateWork;	
}

function dtpDate2String(dateWork, dtpFormat) {
 YYYY = dateWork.getFullYear();
 MM = dateWork.getMonth() + 1;
 DD = dateWork.getDate();
 HH = dateWork.getHours();
 MN = dateWork.getMinutes();
 SS = dateWork.getSeconds();
 switch(dtpFormat) {
     case 'MDY': // YYYY-MM-DD
         str = padZero(MM)+padZero(DD)+YYYY;
         str = str.substr(0,4)+str.substr(6,2);
         break
     case 'D1': // YYYY-MM-DD
         str = YYYY+'-'+padZero(MM)+'-'+padZero(DD)
         break
     case 'D1T1': // YYYY-MM-DD HH:MM:SS
         str = YYYY+'-'+padZero(MM)+'-'+padZero(DD)+' '+padZero(HH)+':'+padZero(MN)+':'+padZero(SS);
         break
     default: // YYYY-MM-DD
         str = YYYY+'-'+padZero(MM)+'-'+padZero(DD)
 }
 return str;	
}

function padZero(num) {
 if (num < 10) {str = '0' + num;}
 else {str = String(num);}
 return str;
}

function dtpDaySel(inputID, id, dtpFormat) {
 var dateWork = new Date();
 var dateWork2 = new Date();
 obj = document.getElementById(inputID)
 dateWork = dtpString2Date(id, 'D1');
 // Since only selecting date, move current time into new selection
 dateWork2 = dtpString2Date(obj.value, dtpFormat);
 dateWork.setHours(dateWork2.getHours(), dateWork2.getMinutes(), dateWork2.getSeconds());
 obj.value = dtpDate2String(dateWork, dtpFormat);

 dtpClose(inputID);
}

function dtpClose(inputID) {
 obj = document.getElementById(inputID)
 obj.style.background='transparent';
	dtp.style.visibility = 'hidden';
	dtp.style.zIndex = 0;
}

var dtpSaveColor = '';
function dtpDayOn(id) {
 obj = document.getElementById(id)
 dtpSaveColor = obj.style.color;
 obj.style.backgroundColor = "#FFCC00";
}

function dtpDayOff(id) {
 obj = document.getElementById(id)
 obj.style.backgroundColor = dtpSaveColor;
}

function numberWithCommas(input) {
	var x = numberWithoutCommas(input);
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

function numberWithoutCommas(x) {
	return x.replace(/[^\d\.\-]/g,'');
}

function formatInput(id) {
	var x = document.getElementById(id).value;
	x = numberWithCommas(x);
	document.getElementById(id).value = x;
}